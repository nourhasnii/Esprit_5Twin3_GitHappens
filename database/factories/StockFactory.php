<?php

namespace Database\Factories;

use App\Models\Batch;
use App\Models\Stock;
use Database\Factories\Concerns\ResolvesDemoProduct;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Lignes de stock (site × produit × lot).
 *
 * Attention : dans l'application, la quantité d'une ligne est la somme de ses mouvements.
 * Pour des données cohérentes, créez des mouvements (StockMovementFactory ou StockService::record())
 * puis lancez StockService::recalculate(true). Cette factory sert surtout aux tests d'affichage et d'alertes.
 *
 * Exemples :
 *   Stock::factory()->belowThreshold()->create();
 *   Stock::factory()->forBatch($batch)->create(['site_id' => $site->id]);
 *
 * @extends Factory<Stock>
 */
class StockFactory extends Factory
{
    use ResolvesDemoProduct;

    protected $model = Stock::class;

    public function definition(): array
    {
        return [
            'site_id' => SiteFactory::new()->retailStore(),
            'product_id' => fn () => $this->demoProductId(),
            'batch_id' => null,
            'quantity' => $this->faker->numberBetween(150, 800),
            'min_threshold' => 100,
            'last_movement_at' => now()->subHours($this->faker->numberBetween(1, 72)),
        ];
    }

    /** Ligne d'un lot précis (le produit est celui du lot) */
    public function forBatch(Batch $batch): static
    {
        return $this->state(['batch_id' => $batch->getKey(), 'product_id' => $batch->product_id]);
    }

    /** Stock sous le seuil d'alerte */
    public function belowThreshold(): static
    {
        return $this->state(fn (array $a) => ['quantity' => max(0, ($a['min_threshold'] ?? 100) - $this->faker->numberBetween(10, 60))]);
    }

    public function empty(): static
    {
        return $this->state(['quantity' => 0]);
    }
}
