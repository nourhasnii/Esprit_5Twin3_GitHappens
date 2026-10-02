<?php

namespace Database\Seeders;

use App\Models\Batch;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BatchSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::all();
        if ($products->isEmpty()) {
            $this->command->warn('Aucun produit trouvé. Exécutez d\'abord ProductSeeder.');

            return;
        }

        $year = date('Y');
        $createdCount = 0;

        $seedBatches = [
            [
                'product_name' => "Huile d'Olive Extra Vierge de Provence AOC",
                'lot_number' => "LOT-{$year}-OLIV-001",
                'production_offset_weeks' => -8,
                'shelf_life_weeks' => 104,
                'quantity' => 2400,
                'unit' => 'L',
                'carbon_footprint' => 2.50,
                'status' => 'active',
            ],
            [
                'product_name' => "Huile d'Olive Extra Vierge de Provence AOC",
                'lot_number' => "LOT-{$year}-OLIV-002",
                'production_offset_weeks' => -52,
                'shelf_life_weeks' => 40,
                'quantity' => 1500,
                'unit' => 'L',
                'carbon_footprint' => 2.40,
                'status' => 'expired',
            ],
            [
                'product_name' => 'Miel de Lavande des Hautes-Alpes',
                'lot_number' => "LOT-{$year}-MIEL-010",
                'production_offset_weeks' => -4,
                'shelf_life_weeks' => 156,
                'quantity' => 800,
                'unit' => 'kg',
                'carbon_footprint' => 1.25,
                'status' => 'active',
            ],
            [
                'product_name' => 'Fromage de Chèvre AOP Crottin de Chavignol',
                'lot_number' => "LOT-{$year}-CHEV-050",
                'production_offset_weeks' => -2,
                'shelf_life_weeks' => 4,
                'quantity' => 500,
                'unit' => 'pièce',
                'carbon_footprint' => 5.90,
                'status' => 'active',
            ],
            [
                'product_name' => 'Fromage de Chèvre AOP Crottin de Chavignol',
                'lot_number' => "LOT-{$year}-CHEV-048",
                'production_offset_weeks' => -8,
                'shelf_life_weeks' => 4,
                'quantity' => 450,
                'unit' => 'pièce',
                'carbon_footprint' => 5.70,
                'status' => 'expired',
            ],
            [
                'product_name' => 'Tomates Cerises Heirloom Bio',
                'lot_number' => "LOT-{$year}-TOMC-120",
                'production_offset_weeks' => -1,
                'shelf_life_weeks' => 2,
                'quantity' => 1200,
                'unit' => 'kg',
                'carbon_footprint' => 0.90,
                'status' => 'active',
            ],
            [
                'product_name' => 'Tomates Cerises Heirloom Bio',
                'lot_number' => "LOT-{$year}-TOMC-119",
                'production_offset_weeks' => -2,
                'shelf_life_weeks' => 2,
                'quantity' => 980,
                'unit' => 'kg',
                'carbon_footprint' => 0.92,
                'status' => 'recalled',
            ],
            [
                'product_name' => 'Saumon Atlantique Élevé Durablement Label Rouge',
                'lot_number' => "LOT-{$year}-SAUM-078",
                'production_offset_weeks' => -1,
                'shelf_life_weeks' => 3,
                'quantity' => 3200,
                'unit' => 'kg',
                'carbon_footprint' => 6.45,
                'status' => 'active',
            ],
            [
                'product_name' => 'Poulet Fermier Label Rouge Plein Air',
                'lot_number' => "LOT-{$year}-POUL-033",
                'production_offset_weeks' => -3,
                'shelf_life_weeks' => 3,
                'quantity' => 150,
                'unit' => 'pièce',
                'carbon_footprint' => 4.15,
                'status' => 'active',
            ],
            [
                'product_name' => 'Poulet Fermier Label Rouge Plein Air',
                'lot_number' => "LOT-{$year}-POUL-030",
                'production_offset_weeks' => -12,
                'shelf_life_weeks' => 3,
                'quantity' => 145,
                'unit' => 'pièce',
                'carbon_footprint' => 4.05,
                'status' => 'expired',
            ],
            [
                'product_name' => 'Pommes Golden Delicious Bio du Limousin',
                'lot_number' => "LOT-{$year}-POMM-201",
                'production_offset_weeks' => -26,
                'shelf_life_weeks' => 30,
                'quantity' => 5200,
                'unit' => 'kg',
                'carbon_footprint' => 0.48,
                'status' => 'active',
            ],
            [
                'product_name' => 'Pain au Levain Traditionnel de Campagne',
                'lot_number' => "LOT-{$year}-PAIN-445",
                'production_offset_weeks' => -0,
                'shelf_life_weeks' => 1,
                'quantity' => 300,
                'unit' => 'pièce',
                'carbon_footprint' => 1.02,
                'status' => 'active',
            ],
            [
                'product_name' => 'Riz Basmati Bio du Punjab',
                'lot_number' => "LOT-{$year}-RIZB-015",
                'production_offset_weeks' => -20,
                'shelf_life_weeks' => 104,
                'quantity' => 8000,
                'unit' => 'kg',
                'carbon_footprint' => 3.25,
                'status' => 'active',
            ],
            [
                'product_name' => 'Lentilles Vertes du Puy AOC',
                'lot_number' => "LOT-{$year}-LENT-088",
                'production_offset_weeks' => -50,
                'shelf_life_weeks' => 52,
                'quantity' => 3400,
                'unit' => 'kg',
                'carbon_footprint' => 1.08,
                'status' => 'expired',
            ],
            [
                'product_name' => 'Chocolat Noir 72% Grand Cru Pérou',
                'lot_number' => "LOT-{$year}-CHOC-077",
                'production_offset_weeks' => -10,
                'shelf_life_weeks' => 78,
                'quantity' => 1800,
                'unit' => 'g',
                'carbon_footprint' => 8.80,
                'status' => 'active',
            ],
            [
                'product_name' => 'Café Arabica Colombien Supremo',
                'lot_number' => "LOT-{$year}-CAFE-042",
                'production_offset_weeks' => -6,
                'shelf_life_weeks' => 52,
                'quantity' => 2500,
                'unit' => 'kg',
                'carbon_footprint' => 5.70,
                'status' => 'active',
            ],
        ];

        foreach ($seedBatches as $batchData) {
            $product = Product::where('name', $batchData['product_name'])->first();
            if (! $product) {
                continue;
            }

            $productionDate = today()->addWeeks($batchData['production_offset_weeks']);
            $expirationDate = (clone $productionDate)->addWeeks($batchData['shelf_life_weeks']);

            $batch = Batch::updateOrCreate(
                ['lot_number' => $batchData['lot_number']],
                [
                    'product_id' => $product->id,
                    'production_date' => $productionDate->format('Y-m-d'),
                    'expiration_date' => $expirationDate->format('Y-m-d'),
                    'quantity' => $batchData['quantity'],
                    'unit' => $batchData['unit'],
                    'carbon_footprint' => $batchData['carbon_footprint'],
                    'status' => $batchData['status'],
                ]
            );

            if ($batch->wasRecentlyCreated) {
                $createdCount++;
            }
        }

        $nearExpirationBatches = Batch::factory()
            ->count(3)
            ->nearExpiration()
            ->create();
        $createdCount += $nearExpirationBatches->count();

        $activeBatches = Batch::factory()
            ->count(4)
            ->active()
            ->create();
        $createdCount += $activeBatches->count();

        $expiredBatches = Batch::factory()
            ->count(2)
            ->expired()
            ->create();
        $createdCount += $expiredBatches->count();

        $activeTotal = $nearExpirationBatches->count() + $activeBatches->count();
        $expiredTotal = $expiredBatches->count();

        $this->command->info("BatchSeeder : lots explicites traités, {$createdCount} nouveaux lots créés via updateOrCreate + factories.");
        $this->command->line("  Lots actifs : {$activeTotal} (proche expiration: {$nearExpirationBatches->count()})");
        $this->command->line("  Lots expirés / rappelés : {$expiredTotal}");
    }
}
