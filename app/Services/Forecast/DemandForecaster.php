<?php

namespace App\Services\Forecast;

use App\Models\Product;
use App\Models\Site;
use App\Models\StockMovement;
use App\Support\TunisianCalendar;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

/**
 * Prévision de la demande journalière d'un produit sur un site.
 *
 * Méthode explicable, en 5 étapes :
 *  1. Historique des ventes (sorties hors dons, casse, destruction) sur 8 semaines, jour par jour.
 *  2. Ventes exceptionnelles ramenées à un niveau plausible (médiane + k × écart absolu médian),
 *     pour qu'une erreur de saisie ou une commande isolée ne fausse pas la prévision.
 *  3. Effets des événements du calendrier tunisien retirés de l'historique (Ramadan, Aïd, rentrée…).
 *  4. Effet du jour de la semaine appris sur l'historique (prudent quand il y a peu de semaines).
 *  5. Lissage exponentiel de Holt : un niveau et une tendance amortie, puis
 *     prévision = (niveau + tendance) × effet du jour × effet des événements à venir.
 *
 * La précision est mesurée en rejouant les 7 derniers jours (backtest) et comparée à la moyenne simple.
 */
class DemandForecaster
{
    public const WEEKDAYS = [1 => 'Lun', 2 => 'Mar', 3 => 'Mer', 4 => 'Jeu', 5 => 'Ven', 6 => 'Sam', 7 => 'Dim'];

    /** @var array<string, array> modèles calculés pendant la requête */
    private array $models = [];

    /** Prévision détaillée jour par jour, à partir d'une date (aujourd'hui par défaut). */
    public function forecast(int $siteId, int $productId, ?CarbonInterface $from = null, ?int $days = null): array
    {
        $model = $this->model($siteId, $productId);
        $from = CarbonImmutable::parse($from ?? now())->startOfDay();
        $days = max(1, $days ?? (int) config('stock.forecast.horizon_days', 14));

        $points = [];
        for ($i = 0; $i < $days; $i++) {
            $points[] = $this->point($model, $from->addDays($i));
        }

        $total = array_sum(array_column($points, 'qty'));

        return $model + [
            'from' => $from->toDateString(),
            'days' => $points,
            'total' => round($total, 1),
            'average' => round($total / $days, 2),
        ];
    }

    /** Demande journalière moyenne prévue sur une période (utilisée par le moteur et le plan). */
    public function averageDaily(int $siteId, int $productId, CarbonInterface $from, float $days): float
    {
        $n = max(1, (int) ceil($days));
        $model = $this->model($siteId, $productId);

        if (! $model['has_history']) {
            return 0.0;
        }

        $start = CarbonImmutable::parse($from)->startOfDay();
        $sum = 0.0;
        for ($i = 0; $i < $n; $i++) {
            $sum += $this->point($model, $start->addDays($i))['qty'];
        }

        return $sum / $n;
    }

    /**
     * Date de rupture prévue : premier jour où les ventes cumulées dépassent le stock disponible.
     *
     * @return array{date: string, days: int}|null  null si aucune rupture dans l'horizon
     */
    public function stockout(int $siteId, int $productId, float $available, int $horizon = 30): ?array
    {
        $model = $this->model($siteId, $productId);

        if (! $model['has_history'] || $model['level'] <= 0) {
            return null;
        }

        $today = CarbonImmutable::now()->startOfDay();
        $cumulative = 0.0;

        for ($i = 0; $i < $horizon; $i++) {
            $cumulative += $this->point($model, $today->addDays($i))['qty'];

            if ($cumulative >= $available) {
                return ['date' => $today->addDays($i)->toDateString(), 'days' => $i];
            }
        }

        return null;
    }

    /** Modèle ajusté sur l'historique (mis en cache pour la requête). */
    public function model(int $siteId, int $productId): array
    {
        return $this->models[$siteId.'-'.$productId] ??= $this->build($siteId, $productId);
    }

    private function build(int $siteId, int $productId): array
    {
        $cfg = config('stock.forecast');
        $site = Site::query()->find($siteId);
        $product = Product::query()->find($productId);
        // Famille reconnue d'après le nom et la catégorie du produit (ex. « Produits laitiers »)
        $category = TunisianCalendar::categoryOf(trim($product?->name.' '.($product?->category ?? '')));
        $coastal = TunisianCalendar::isCoastal($site?->city);

        $today = CarbonImmutable::now()->startOfDay();
        $start = $today->subDays((int) $cfg['history_days']);

        $sales = [];
        StockMovement::query()->sales()
            ->where('source_site_id', $siteId)
            ->where('product_id', $productId)
            ->where('moved_at', '>=', $start)
            ->where('moved_at', '<', $today)
            ->get(['quantity', 'moved_at'])
            ->each(function (StockMovement $m) use (&$sales) {
                $key = CarbonImmutable::parse($m->moved_at)->toDateString();
                $sales[$key] = ($sales[$key] ?? 0) + (float) $m->quantity;
            });

        $base = [
            'site_id' => $siteId,
            'product_id' => $productId,
            'category' => $category,
            'coastal' => $coastal,
            'history' => [],
            'outliers' => [],
            'weekday_factors' => array_fill_keys(array_keys(self::WEEKDAYS), 1.0),
            'level' => 0.0,
            'trend' => 0.0,
            'sigma' => 0.0,
            'last_date' => $today->subDay()->toDateString(),
            'simple_average' => 0.0,
            'accuracy' => null,
        ];

        if ($sales === []) {
            return $base + ['has_history' => false, 'history_days' => 0, 'missing_days' => 0];
        }

        // 1. Série jour par jour, du premier jour de vente à hier.
        //    Un jour sans AUCUNE vente enregistrée sur le site (tous produits confondus) est une donnée
        //    manquante (magasin fermé, saisie non faite) : il est ignoré au lieu de compter pour 0.
        $openDays = StockMovement::query()->sales()
            ->where('source_site_id', $siteId)
            ->where('moved_at', '>=', $start)
            ->where('moved_at', '<', $today)
            ->pluck('moved_at')
            ->mapWithKeys(fn ($at) => [CarbonImmutable::parse($at)->toDateString() => true])
            ->all();

        $first = CarbonImmutable::parse(min(array_keys($sales)));
        $dates = [];
        $y = [];
        $missing = 0;
        for ($d = $first; $d->lt($today); $d = $d->addDay()) {
            if (! isset($openDays[$d->toDateString()])) {
                $missing++;
                continue;
            }
            $dates[] = $d;
            $y[] = $sales[$d->toDateString()] ?? 0.0;
        }

        // 2. Ventes exceptionnelles plafonnées
        [$y, $outliers] = $this->capOutliers($dates, $y, (float) $cfg['outlier_mad']);

        $history = [];
        foreach ($dates as $i => $d) {
            $effects = TunisianCalendar::effects($d, $category, $coastal);
            $history[] = [
                'date' => $d->toDateString(),
                'qty' => round($y[$i], 2),
                'raw' => round($sales[$d->toDateString()] ?? 0.0, 2),
                'events' => array_column($effects, 'label'),
                'multiplier' => TunisianCalendar::multiplier($effects),
            ];
        }

        $multipliers = array_column($history, 'multiplier');
        $accuracy = $this->calibrate($dates, $y, $multipliers, $cfg, $category, $coastal);
        $fit = $this->fit($dates, $y, $multipliers, $cfg, $accuracy['alpha'] ?? null, $accuracy['beta'] ?? null, $accuracy['weekly'] ?? true);

        return array_merge($base, $fit, [
            'has_history' => true,
            'history_days' => count($y),
            'missing_days' => $missing,
            'history' => $history,
            'outliers' => $outliers,
            'simple_average' => round(array_sum($y) / count($y), 2),
            'accuracy' => $accuracy,
        ]);
    }

    /**
     * Ajustement : effet du jour de la semaine, puis lissage de Holt avec tendance amortie.
     *
     * @param  list<CarbonImmutable>  $dates
     * @param  list<float>  $y
     * @param  list<float>  $eventMultipliers
     */
    private function fit(array $dates, array $y, array $eventMultipliers, array $cfg, ?float $alpha = null, ?float $beta = null, bool $weekly = true): array
    {
        $n = count($y);

        // 3. Retirer l'effet des événements passés
        $adjusted = [];
        foreach ($y as $i => $value) {
            $adjusted[] = $value / max(0.01, $eventMultipliers[$i]);
        }

        // 4. Effet du jour de la semaine, rapproché de 1 quand il y a peu de semaines d'historique
        $mean = array_sum($adjusted) / max(1, $n);
        $byDay = [];
        foreach ($adjusted as $i => $value) {
            $byDay[$dates[$i]->dayOfWeekIso][] = $value;
        }

        $factors = [];
        foreach (array_keys(self::WEEKDAYS) as $wd) {
            $values = $byDay[$wd] ?? [];
            $count = count($values);
            $raw = $mean > 0 && $count > 0 ? (array_sum($values) / $count) / $mean : 1.0;
            $factors[$wd] = 1 + ($raw - 1) * $count / ($count + 2);
        }
        $norm = array_sum($factors) / 7;
        foreach ($factors as $wd => $f) {
            $factors[$wd] = $weekly && $norm > 0 ? $f / $norm : 1.0;
        }

        // 5. Lissage de Holt sur la série corrigée
        $z = [];
        foreach ($adjusted as $i => $value) {
            $z[] = $value / max(0.05, $factors[$dates[$i]->dayOfWeekIso]);
        }

        $alpha ??= (float) $cfg['alpha'];
        $beta ??= (float) $cfg['beta'];
        $phi = (float) $cfg['damping'];

        $warmup = array_slice($z, 0, min(7, $n));
        $level = array_sum($warmup) / count($warmup);
        $trend = 0.0;
        $residuals = [];

        foreach ($z as $i => $value) {
            $predicted = $level + $phi * $trend;
            if ($i >= min(7, $n)) {
                $residuals[] = $value - $predicted;
            }
            $newLevel = $alpha * $value + (1 - $alpha) * $predicted;
            $trend = $beta * ($newLevel - $level) + (1 - $beta) * $phi * $trend;
            $level = $newLevel;
        }

        $sigma = 0.0;
        if (count($residuals) > 1) {
            $m = array_sum($residuals) / count($residuals);
            $sigma = sqrt(array_sum(array_map(fn ($r) => ($r - $m) ** 2, $residuals)) / (count($residuals) - 1));
        }

        return [
            'level' => round(max(0, $level), 3),
            'trend' => round($trend, 3),
            'sigma' => round($sigma, 3),
            'weekday_factors' => array_map(fn ($f) => round($f, 3), $factors),
            'last_date' => end($dates)->toDateString(),
            'alpha' => $alpha,
            'beta' => $beta,
            'weekly' => $weekly,
        ];
    }

    /** Prévision d'un jour : (niveau + tendance amortie) × effet du jour × effet des événements. */
    private function point(array $model, CarbonImmutable $date): array
    {
        $effects = TunisianCalendar::effects($date, $model['category'], $model['coastal']);
        $multiplier = TunisianCalendar::multiplier($effects);
        $weekday = $date->dayOfWeekIso;

        if (! $model['has_history']) {
            return ['date' => $date->toDateString(), 'weekday' => self::WEEKDAYS[$weekday], 'qty' => 0.0, 'low' => 0.0, 'high' => 0.0,
                'events' => array_column($effects, 'label'), 'multiplier' => $multiplier];
        }

        $phi = (float) config('stock.forecast.damping');
        $h = max(1, CarbonImmutable::parse($model['last_date'])->diffInDays($date, false));
        $trendSum = abs(1 - $phi) < 1e-9 ? $h : $phi * (1 - $phi ** $h) / (1 - $phi);
        $base = max(0, $model['level'] + $model['trend'] * $trendSum);
        $scale = $model['weekday_factors'][$weekday] * $multiplier;
        $qty = $base * $scale;

        // Intervalle à 80 % (± 1,28 écart-type), qui s'élargit légèrement avec l'horizon
        $band = 1.28 * $model['sigma'] * $scale * sqrt(1 + 0.05 * ($h - 1));

        return [
            'date' => $date->toDateString(),
            'weekday' => self::WEEKDAYS[$weekday],
            'qty' => round($qty, 2),
            'low' => round(max(0, $qty - $band), 2),
            'high' => round($qty + $band, 2),
            'events' => array_column($effects, 'label'),
            'multiplier' => round($multiplier, 3),
        ];
    }

    /**
     * Auto-calibrage : plusieurs réglages de lissage sont essayés en rejouant les 7 derniers jours
     * (le modèle est ajusté sans eux, puis comparé aux ventes réelles). Le réglage qui se trompe
     * le moins est retenu. Son erreur (WAPE) est comparée à celle de la moyenne simple.
     */
    private function calibrate(array $dates, array $y, array $eventMultipliers, array $cfg, ?string $category, bool $coastal): ?array
    {
        $test = 7;
        $n = count($y);

        if ($n < 21 || array_sum(array_slice($y, -$test)) <= 0) {
            return null;
        }

        $trainDates = array_slice($dates, 0, $n - $test);
        $trainY = array_slice($y, 0, $n - $test);
        $trainMultipliers = array_slice($eventMultipliers, 0, $n - $test);
        $actual = array_sum(array_slice($y, -$test));

        $average = array_sum($trainY) / count($trainY);
        $baselineError = 0.0;
        for ($i = $n - $test; $i < $n; $i++) {
            $baselineError += abs($y[$i] - $average);
        }

        $best = null;
        $tried = 0;
        foreach ([false, true] as $weekly) {
            foreach ([0.05, 0.1, 0.2, 0.3, 0.5] as $alpha) {
                foreach ([0.0, 0.1] as $beta) {
                    $tried++;
                    $model = $this->fit($trainDates, $trainY, $trainMultipliers, $cfg, $alpha, $beta, $weekly)
                        + ['has_history' => true, 'category' => $category, 'coastal' => $coastal];

                    $error = 0.0;
                    for ($i = $n - $test; $i < $n; $i++) {
                        $error += abs($y[$i] - $this->point($model, $dates[$i])['qty']);
                    }

                    // À erreur égale, le réglage le plus simple (essayé en premier) est conservé
                    if ($best === null || $error < $best['error'] - 1e-9) {
                        $best = ['error' => $error, 'alpha' => $alpha, 'beta' => $beta, 'weekly' => $weekly];
                    }
                }
            }
        }

        return [
            'test_days' => $test,
            'error' => round(100 * $best['error'] / $actual, 1),
            'baseline_error' => round(100 * $baselineError / $actual, 1),
            'alpha' => $best['alpha'],
            'beta' => $best['beta'],
            'weekly' => $best['weekly'],
            'tried' => $tried,
        ];
    }

    /** @return array{0: list<float>, 1: list<array{date: string, original: float, capped: float}>} */
    private function capOutliers(array $dates, array $y, float $k): array
    {
        $positive = array_values(array_filter($y, fn ($v) => $v > 0));

        if (count($positive) < 5) {
            return [$y, []];
        }

        $median = $this->median($positive);
        $mad = $this->median(array_map(fn ($v) => abs($v - $median), $positive));
        $cap = $mad > 0 ? $median + $k * 1.4826 * $mad : $median * 3;

        $outliers = [];
        foreach ($y as $i => $value) {
            if ($value > $cap) {
                $outliers[] = ['date' => $dates[$i]->toDateString(), 'original' => round($value, 2), 'capped' => round($cap, 2)];
                $y[$i] = $cap;
            }
        }

        return [$y, $outliers];
    }

    private function median(array $values): float
    {
        sort($values);
        $n = count($values);
        $mid = intdiv($n, 2);

        return $n % 2 ? (float) $values[$mid] : ($values[$mid - 1] + $values[$mid]) / 2;
    }
}
