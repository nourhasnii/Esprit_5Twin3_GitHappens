<?php

namespace App\Models;

use App\Enums\RecommendationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OptimizationRecommendation extends Model
{
    protected $fillable = [
        'batch_id', 'product_id', 'source_site_id', 'recommended_site_id', 'chosen_site_id',
        'quantity', 'score', 'distance_km', 'co2_kg', 'days_to_expiry',
        'weights', 'candidates', 'explanation',
        'status', 'decision_note', 'requested_by', 'decided_by', 'decided_at', 'stock_movement_id',
        'rescue_plan', 'rescue_applied_at', 'rescue_applied_by',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'float',
            'score' => 'float',
            'distance_km' => 'float',
            'co2_kg' => 'float',
            'weights' => 'array',
            'candidates' => 'array',
            'explanation' => 'array',
            'status' => RecommendationStatus::class,
            'decided_at' => 'datetime',
            'rescue_plan' => 'array',
            'rescue_applied_at' => 'datetime',
        ];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function sourceSite(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'source_site_id');
    }

    public function recommendedSite(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'recommended_site_id');
    }

    public function chosenSite(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'chosen_site_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function decider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    public function stockMovement(): BelongsTo
    {
        return $this->belongsTo(StockMovement::class);
    }

    public function candidate(?int $siteId): ?array
    {
        return $siteId ? collect($this->candidates)->firstWhere('site_id', $siteId) : null;
    }

    public function recommendedCandidate(): ?array
    {
        return $this->candidate($this->recommended_site_id);
    }

    public function rescuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rescue_applied_by');
    }

    public function hasAppliedRescue(): bool
    {
        return $this->rescue_applied_at !== null;
    }

    /**
     * Impact cumulé des plans anti-gaspillage appliqués (bandeau d'impact).
     *
     * @return array{plans: int, saved_kg: float, donated_kg: float, meals: int, co2_avoided_kg: float}
     */
    public static function impactTotals(): array
    {
        $totals = ['plans' => 0, 'saved_kg' => 0.0, 'donated_kg' => 0.0, 'meals' => 0, 'co2_avoided_kg' => 0.0];

        static::query()->whereNotNull('rescue_applied_at')->get(['rescue_plan'])
            ->each(function (self $r) use (&$totals) {
                $impact = $r->rescue_plan['impact'] ?? [];
                $totals['plans']++;
                $totals['saved_kg'] += (float) ($impact['saved_kg'] ?? 0);
                $totals['donated_kg'] += (float) ($impact['donated_kg'] ?? 0);
                $totals['meals'] += (int) ($impact['meals'] ?? 0);
                $totals['co2_avoided_kg'] += (float) ($impact['co2_net_kg'] ?? 0);
            });

        return $totals;
    }
}
