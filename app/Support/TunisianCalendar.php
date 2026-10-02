<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Str;

/**
 * Calendrier commercial tunisien : périodes qui modifient la demande (Ramadan, Aïd, rentrée, été).
 * Les événements et leurs multiplicateurs sont définis dans config/stock.php (forecast.events).
 */
class TunisianCalendar
{
    /** Famille du produit d'après son nom (ex. « laitier » pour « Lait frais 1 L »), ou null. */
    public static function categoryOf(?string $productName): ?string
    {
        $name = Str::lower(Str::ascii((string) $productName));

        foreach (config('stock.forecast.categories', []) as $category => $keywords) {
            foreach ($keywords as $keyword) {
                if (preg_match('/\b'.preg_quote(Str::lower(Str::ascii($keyword)), '/').'/u', $name)) {
                    return $category;
                }
            }
        }

        return null;
    }

    public static function isCoastal(?string $city): bool
    {
        $city = Str::lower(Str::ascii((string) $city));

        return $city !== '' && in_array($city, array_map(fn ($c) => Str::lower(Str::ascii($c)), config('stock.forecast.coastal_cities', [])), true);
    }

    /**
     * Événements actifs à une date, avec le multiplicateur applicable à la famille du produit.
     *
     * @return list<array{label: string, multiplier: float}>
     */
    public static function effects(CarbonInterface $date, ?string $category, bool $coastal): array
    {
        $day = CarbonImmutable::parse($date)->startOfDay();
        $effects = [];

        foreach (config('stock.forecast.events', []) as $event) {
            if (($event['coastal_only'] ?? false) && ! $coastal) {
                continue;
            }

            if (! self::isActive($event, $day)) {
                continue;
            }

            $multipliers = $event['multipliers'] ?? [];
            $multiplier = (float) ($category !== null && isset($multipliers[$category]) ? $multipliers[$category] : ($multipliers['*'] ?? 1));

            if (abs($multiplier - 1) > 0.001) {
                $effects[] = ['label' => $event['label'], 'multiplier' => $multiplier];
            }
        }

        return $effects;
    }

    /** Multiplicateur combiné des événements, borné par forecast.max_multiplier. */
    public static function multiplier(array $effects): float
    {
        $product = array_reduce($effects, fn ($carry, $e) => $carry * $e['multiplier'], 1.0);
        $max = (float) config('stock.forecast.max_multiplier', 2.0);

        return max(1 / $max, min($max, $product));
    }

    private static function isActive(array $event, CarbonImmutable $day): bool
    {
        foreach ($event['periods'] ?? [] as [$start, $end]) {
            if ($day->betweenIncluded(CarbonImmutable::parse($start)->startOfDay(), CarbonImmutable::parse($end)->endOfDay())) {
                return true;
            }
        }

        if (isset($event['recurring'])) {
            [$start, $end] = $event['recurring'];
            $md = $day->format('m-d');

            return $start <= $end ? ($md >= $start && $md <= $end) : ($md >= $start || $md <= $end);
        }

        return false;
    }
}
