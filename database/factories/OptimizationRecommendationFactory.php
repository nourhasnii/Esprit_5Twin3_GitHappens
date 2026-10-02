<?php

namespace Database\Factories;

use App\Enums\RecommendationStatus;
use App\Models\Batch;
use App\Models\OptimizationRecommendation;
use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Recommandations de l'Optimization Engine, au même format que celles calculées par le moteur
 * (candidats, scores, explication), pour tester les écrans et l'historique sans lancer le calcul.
 * Pour une vraie recommandation : OptimizationEngine::recommend().
 *
 * Exemples :
 *   OptimizationRecommendation::factory()->create();
 *   OptimizationRecommendation::factory()->lowScore()->rejected()->create();
 *
 * @extends Factory<OptimizationRecommendation>
 */
class OptimizationRecommendationFactory extends Factory
{
    protected $model = OptimizationRecommendation::class;

    public function definition(): array
    {
        return [
            'batch_id' => BatchFactory::new()->for(ProductFactory::new()->dairy(), 'product'),
            'product_id' => fn (array $a) => Batch::query()->find($a['batch_id'])?->product_id,
            'source_site_id' => SiteFactory::new()->warehouse(),
            'recommended_site_id' => SiteFactory::new()->retailStore(),
            'chosen_site_id' => null,
            'quantity' => $this->faker->numberBetween(50, 400),
            'score' => $this->faker->randomFloat(1, 65, 98),
            'distance_km' => $this->faker->randomFloat(1, 10, 300),
            'co2_kg' => fn (array $a) => round($a['distance_km'] * $a['quantity'] / 1000 * 0.115, 2),
            'days_to_expiry' => $this->faker->numberBetween(2, 20),
            'weights' => config('stock.optimization.weights'),
            'candidates' => fn (array $a) => [$this->candidate($a)],
            'explanation' => fn (array $a) => [
                'summary' => sprintf('Envoyer %d unité(s) vers %s.', $a['quantity'], Site::query()->find($a['recommended_site_id'])?->name),
                'points' => ['Recommandation générée par factory (données de test).'],
                'excluded' => [],
            ],
            'status' => RecommendationStatus::Pending,
        ];
    }

    public function accepted(): static
    {
        return $this->state([
            'status' => RecommendationStatus::Accepted,
            // Fonction évaluée après la création du site recommandé : le site choisi est le même
            'chosen_site_id' => fn (array $attributes) => $attributes['recommended_site_id'],
            'decided_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state([
            'status' => RecommendationStatus::Rejected,
            'decision_note' => 'Proposition rejetée (données de test).',
            'decided_at' => now(),
        ]);
    }

    /** Destination peu satisfaisante (déclenche l'avertissement et le plan anti-gaspillage) */
    public function lowScore(): static
    {
        return $this->state(fn () => ['score' => $this->faker->randomFloat(1, 30, 55), 'days_to_expiry' => 2]);
    }

    private function candidate(array $a): array
    {
        $site = Site::query()->find($a['recommended_site_id']);
        $total = (float) $a['score'];

        return [
            'site_id' => $site?->getKey(),
            'site_code' => $site?->code,
            'site_name' => $site?->name,
            'site_type' => $site?->type?->value,
            'city' => $site?->city,
            'feasible' => true,
            'blocking' => [],
            'scores' => ['demand' => $total, 'expiry' => $total, 'distance' => $total, 'capacity' => $total, 'co2' => $total],
            'reasons' => array_fill_keys(['demand', 'expiry', 'distance', 'capacity', 'co2'], 'Valeur de test.'),
            'metrics' => [
                'distance_km' => $a['distance_km'], 'transit_hours' => round($a['distance_km'] / 60, 1), 'co2_kg' => $a['co2_kg'] ?? 0,
                'capacity' => (float) $site?->capacity, 'used' => 0, 'free' => (float) $site?->capacity,
                'avg_daily_out' => 20, 'current_stock' => 0, 'threshold' => 0, 'need' => $a['quantity'], 'horizon_days' => 7,
            ],
            'total' => $total,
        ];
    }
}
