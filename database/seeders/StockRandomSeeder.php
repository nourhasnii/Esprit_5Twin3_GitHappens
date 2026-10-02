<?php

namespace Database\Seeders;

use App\Events\StockLevelLow;
use App\Models\Site;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\User;
use App\Services\Stock\StockService;
use Database\Factories\BatchFactory;
use Database\Factories\ProductFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Event;

/**
 * Réseau aléatoire généré par les factories : utile pour tester l'application avec plus de données.
 * Crée 4 magasins, 1 entrepôt et 2 associations, 3 produits avec leurs lots, les lignes de stock,
 * des réceptions et 30 jours de ventes par magasin, puis recalcule les stocks à partir des mouvements.
 *
 * Lancement (autant de fois que voulu) : php artisan db:seed --class=StockRandomSeeder
 */
class StockRandomSeeder extends Seeder
{
    public function run(StockService $stocks): void
    {
        $producerId = User::query()->value('id');

        if (! $producerId) {
            $this->command?->warn('Aucun utilisateur : lancez d’abord « php artisan db:seed ».');

            return;
        }

        // 1. Sites : factories avec états (type de site) et ->count()
        $warehouse = Site::factory()->warehouse()->create();
        $stores = Site::factory()->retailStore()->count(4)->create();
        Site::factory()->association()->count(2)->create();

        // 2. Produits et lots
        $products = ProductFactory::new()->dairy()->count(3)->create(['producer_id' => $producerId]);
        $batches = $products->map(fn ($product) => BatchFactory::new()->for($product, 'product')->create());

        // 3. Lignes de stock ouvertes à 0 (StockFactory) : le recalcul final les remplira
        foreach ($batches as $batch) {
            Stock::factory()->forBatch($batch)->create(['site_id' => $warehouse->getKey(), 'quantity' => 0, 'min_threshold' => 0]);

            foreach ($stores as $store) {
                Stock::factory()->create([
                    'site_id' => $store->getKey(),
                    'product_id' => $batch->product_id,
                    'quantity' => 0,
                    'min_threshold' => 150,
                ]);
            }
        }

        Event::fakeFor(function () use ($warehouse, $stores, $batches) {
            foreach ($batches as $batch) {
                // 4. Réception du lot à l'entrepôt
                StockMovement::factory()->into($warehouse)->create([
                    'product_id' => $batch->product_id,
                    'batch_id' => $batch->getKey(),
                    'quantity' => 1500,
                    'moved_at' => now()->subDays(31),
                ]);

                // 5. Chaque magasin reçoit du stock puis vend pendant 30 jours
                foreach ($stores as $store) {
                    StockMovement::factory()->into($store)->create([
                        'product_id' => $batch->product_id,
                        'quantity' => 900,
                        'moved_at' => now()->subDays(31),
                    ]);

                    StockMovement::factory()
                        ->count(30)
                        ->sale()
                        ->from($store)
                        ->sequence(fn ($sequence) => ['moved_at' => now()->subDays(30 - $sequence->index)->setTime(19, 0)])
                        ->create(['product_id' => $batch->product_id]);
                }
            }
        }, [StockLevelLow::class]);

        // 6. Les mouvements créés par factory ne passent pas par StockService : on recalcule les stocks
        $fixed = $stocks->recalculate(fix: true)->count();

        $this->command?->info(sprintf(
            'Réseau aléatoire créé : %d sites, %d produits, %d mouvements (%d ligne(s) de stock mises à jour).',
            7, $products->count(), 3 + 3 * 4 * 31, $fixed
        ));
    }
}
