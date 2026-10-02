<?php

namespace App\Http\Controllers;

use App\Enums\RecommendationStatus;
use App\Http\Requests\OptimizationRequest;
use App\Http\Requests\RecommendationDecisionRequest;
use App\Models\Batch;
use App\Models\OptimizationRecommendation;
use App\Models\Site;
use App\Models\Stock;
use App\Services\Optimization\AntiWastePlanner;
use App\Services\Optimization\OptimizationEngine;
use App\Support\BatchAttributes;
use App\Support\Fmt;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OptimizationController extends Controller
{
    public function __construct(
        private readonly OptimizationEngine $engine,
        private readonly AntiWastePlanner $planner,
    ) {}

    public function index(): View
    {
        $recommendations = OptimizationRecommendation::query()
            ->with(['batch', 'product', 'sourceSite', 'recommendedSite', 'chosenSite'])
            ->latest()
            ->paginate(15);

        return view('optimization.index', compact('recommendations'));
    }

    public function create(Request $request): View
    {
        $batches = Batch::query()->with('product')->latest()->get()
            ->reject(fn (Batch $b) => BatchAttributes::isBlocked($b) || BatchAttributes::isExpired($b))
            ->values();

        // Où se trouve chaque lot actuellement (pour pré-remplir origine et quantité)
        $locations = Stock::query()
            ->whereNotNull('batch_id')
            ->where('quantity', '>', 0)
            ->orderByDesc('quantity')
            ->get(['batch_id', 'site_id', 'quantity'])
            ->groupBy('batch_id')
            ->map(fn ($rows) => $rows->map(fn (Stock $r) => ['site_id' => $r->site_id, 'quantity' => $r->quantity])->values());

        return view('optimization.create', [
            'batches' => $batches,
            'sites' => Site::query()->active()->logistics()->orderBy('name')->get(),
            'locations' => $locations,
            'selectedBatch' => $request->integer('batch_id') ?: null,
            'selectedSource' => $request->integer('source_site_id') ?: null,
            'selectedQuantity' => $request->float('quantity') ?: null,
            'weights' => config('stock.optimization.weights'),
        ]);
    }

    public function store(OptimizationRequest $request): RedirectResponse
    {
        $recommendation = $this->engine->recommend(
            Batch::query()->with('product')->findOrFail($request->validated('batch_id')),
            Site::query()->findOrFail($request->validated('source_site_id')),
            (float) $request->validated('quantity'),
            $request->user(),
        );

        return redirect()->route('optimization.show', $recommendation);
    }

    public function show(OptimizationRecommendation $recommendation): View
    {
        $recommendation->load(['batch.product', 'product', 'sourceSite', 'recommendedSite', 'chosenSite', 'decider', 'stockMovement', 'rescuer']);
        $lowScore = $this->lowScoreAdvice($recommendation);

        return view('optimization.show', [
            'recommendation' => $recommendation,
            'criteria' => OptimizationEngine::CRITERIA,
            'mapPoints' => $this->mapPoints($recommendation),
            'lowScore' => $lowScore,
            'rescue' => $this->rescuePlan($recommendation, $lowScore !== null),
        ]);
    }

    /** Applique le plan anti-gaspillage : transferts partiels, promotion et dons. */
    public function rescue(Request $request, OptimizationRecommendation $recommendation): RedirectResponse
    {
        $this->planner->apply($recommendation, $request->user());
        $impact = $recommendation->rescue_plan['impact'];

        return redirect()->route('optimization.show', $recommendation)->with('success', sprintf(
            'Plan anti-gaspillage appliqué : %s kg sauvés, dont %s kg donnés (environ %d repas).',
            Fmt::n($impact['saved_kg'], 1), Fmt::n($impact['donated_kg'], 1), $impact['meals']
        ));
    }

    /** Bon de don imprimable pour une association du plan appliqué. */
    public function donation(OptimizationRecommendation $recommendation, int $site): View
    {
        abort_unless($recommendation->hasAppliedRescue(), 404);

        $donation = collect($recommendation->rescue_plan['donations'] ?? [])->firstWhere('site_id', $site);
        abort_if($donation === null, 404);

        $recommendation->load(['batch.product', 'product', 'sourceSite', 'rescuer']);

        return view('optimization.donation', [
            'recommendation' => $recommendation,
            'donation' => $donation,
            'association' => Site::query()->find($site),
            'plan' => $recommendation->rescue_plan,
        ]);
    }

    public function decide(RecommendationDecisionRequest $request, OptimizationRecommendation $recommendation): RedirectResponse
    {
        $this->engine->applyDecision(
            $recommendation,
            $request->validated('action'),
            $request->validated('chosen_site_id') ? (int) $request->validated('chosen_site_id') : null,
            $request->validated('note'),
            $request->boolean('create_movement'),
            $request->user(),
        );

        return redirect()->route('optimization.show', $recommendation)
            ->with('success', 'Décision enregistrée : '.mb_strtolower($recommendation->status->label()).'.');
    }

    /**
     * Plan anti-gaspillage à afficher : celui qui a été appliqué, sinon une proposition
     * quand la meilleure destination est insuffisante et que le lot n'a pas encore été déplacé.
     */
    private function rescuePlan(OptimizationRecommendation $r, bool $atRisk): ?array
    {
        if ($r->hasAppliedRescue()) {
            return $r->rescue_plan + ['applied' => true];
        }

        $open = in_array($r->status, [RecommendationStatus::Pending, RecommendationStatus::Rejected], true);

        if (! $atRisk || ! $open || $r->days_to_expiry === null) {
            return null;
        }

        return $this->planner->planFor($r) + ['applied' => false];
    }

    /** Points de la carte : site d'origine et chaque site évalué, avec sa note. */
    private function mapPoints(OptimizationRecommendation $r): array
    {
        $candidates = collect($r->candidates);
        $ids = $candidates->pluck('site_id')->push($r->source_site_id)->filter()->unique();
        $sites = Site::query()->whereIn('id', $ids)->get(['id', 'name', 'city', 'latitude', 'longitude'])->keyBy('id');

        $points = [];
        $source = $sites->get($r->source_site_id);

        if ($source?->hasCoordinates()) {
            $points[] = [
                'role' => 'source', 'name' => $source->name, 'city' => $source->city,
                'lat' => $source->latitude, 'lng' => $source->longitude,
            ];
        }

        foreach ($candidates as $c) {
            $site = $sites->get($c['site_id']);

            if (! $site?->hasCoordinates()) {
                continue;
            }

            $points[] = [
                'role' => match (true) {
                    $c['site_id'] === $r->chosen_site_id && $r->chosen_site_id !== $r->recommended_site_id => 'chosen',
                    $c['site_id'] === $r->recommended_site_id => 'pick',
                    ! $c['feasible'] => 'excluded',
                    default => 'candidate',
                },
                'name' => $site->name,
                'city' => $site->city,
                'lat' => $site->latitude,
                'lng' => $site->longitude,
                'score' => $c['feasible'] ? $c['total'] : null,
                'distance' => $c['metrics']['distance_km'] ?? null,
                'hours' => $c['metrics']['transit_hours'] ?? null,
                'co2' => $c['metrics']['co2_kg'] ?? null,
                'blocking' => $c['blocking'] ?? [],
            ];
        }

        return $points;
    }

    /**
     * Avertissement si la meilleure destination reste peu satisfaisante,
     * avec le conseil adapté au critère le plus faible et la quantité que le réseau peut réellement écouler.
     */
    private function lowScoreAdvice(OptimizationRecommendation $r): ?array
    {
        $threshold = (float) config('stock.optimization.low_score_threshold', 60);
        $best = $r->recommendedCandidate();

        if ($best && $r->score >= $threshold) {
            return null;
        }

        $advice = [
            'expiry' => 'Le lot ne pourra probablement pas être vendu avant sa DLC : réduisez la quantité envoyée, prévoyez une promotion ou orientez le surplus vers un don.',
            'demand' => 'Aucun site n’a besoin de toute cette quantité : répartissez le lot sur plusieurs sites en relançant l’optimisation avec une quantité plus faible.',
            'distance' => 'Les sites intéressés sont éloignés : un transport groupé avec d’autres livraisons limiterait le coût et les émissions.',
            'co2' => 'Le transport vers les sites intéressés est émetteur : un transport groupé ou un site plus proche est préférable.',
            'capacity' => 'Les sites intéressés sont presque pleins : libérez de la place ou passez par un site de transit.',
        ];

        $weakest = $best ? collect($best['scores'])->sort()->keys()->first() : null;

        // Quantité que chaque site peut vendre avant la DLC, déduction faite de son stock actuel
        $absorbable = null;
        $bestAbsorbable = null;

        if ($r->days_to_expiry !== null) {
            $absorbable = 0.0;

            foreach (collect($r->candidates)->where('feasible', true) as $c) {
                $m = $c['metrics'];
                $remaining = max(0, $r->days_to_expiry - ($m['transit_hours'] ?? 0) / 24);
                $canSell = max(0, ($m['avg_daily_out'] ?? 0) * $remaining - ($m['current_stock'] ?? 0));
                $absorbable += $canSell;

                if ($c['site_id'] === $r->recommended_site_id) {
                    $bestAbsorbable = $canSell;
                }
            }

            $absorbable = min((float) $r->quantity, floor($absorbable));
            $bestAbsorbable = $bestAbsorbable !== null ? floor($bestAbsorbable) : null;
        }

        return [
            'threshold' => $threshold,
            'no_destination' => $best === null,
            'advice' => $best ? ($advice[$weakest] ?? null) : 'Aucun site ne respecte les contraintes : vérifiez les capacités, les coordonnées GPS et la DLC du lot.',
            'weakest' => $weakest ? OptimizationEngine::CRITERIA[$weakest] : null,
            'absorbable' => $absorbable,
            'best_absorbable' => $bestAbsorbable,
            'retry_url' => $bestAbsorbable !== null && $bestAbsorbable > 0 && $bestAbsorbable < (float) $r->quantity
                ? route('optimization.create', [
                    'batch_id' => $r->batch_id,
                    'source_site_id' => $r->source_site_id,
                    'quantity' => $bestAbsorbable,
                ])
                : null,
        ];
    }
}
