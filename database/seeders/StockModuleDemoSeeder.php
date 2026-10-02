<?php

namespace Database\Seeders;

use App\Models\Batch;
use App\Models\Product;
use App\Models\User;
use Database\Factories\BatchFactory;
use Database\Factories\ProductFactory;
use Illuminate\Database\Seeder;

/**
 * Démo complète du module Stocks & Optimisation sur FoodLens :
 * 2 produits laitiers et 2 lots créés par ProductFactory et BatchFactory (si absents),
 * puis sites, associations, stocks et 30 jours de ventes.
 * Prérequis : au moins un utilisateur (php artisan db:seed crée l'admin et le Test User).
 * Lancement : php artisan db:seed --class=StockModuleDemoSeeder
 */
class StockModuleDemoSeeder extends Seeder
{
    public function run(): void
    {
        $producerId = User::query()->value('id');

        if (! $producerId) {
            $this->command?->warn('Aucun utilisateur : lancez d’abord « php artisan db:seed ».');

            return;
        }

        // Produits et lots créés par les factories, avec des valeurs fixées pour la démo
        $product = fn (string $name, string $unit) => Product::query()->firstWhere('name', $name)
            ?? ProductFactory::new()->dairy()->create(['name' => $name, 'unit' => $unit, 'producer_id' => $producerId]);

        $yaourt = $product('Yaourt nature 125 g', 'pot');
        $lait = $product('Lait frais 1 L', 'L');

        $batch = fn (Product $p, string $lot, int $days, string $unit) => Batch::query()->firstWhere('lot_number', $lot)
            ?? BatchFactory::new()->for($p, 'product')->expiringIn($days)->create(['lot_number' => $lot, 'quantity' => 1000, 'unit' => $unit]);

        // Le yaourt périme dans 12 jours, le lait dans 2 jours (pour la démo du plan anti-gaspillage)
        $batch($yaourt, 'LOT-2026-001', 12, 'pot');
        $batch($lait, 'LOT-2026-002', 2, 'L');

        $this->command?->info('Produits et lots de démonstration prêts.');

        $this->call(StockDemoSeeder::class);
    }
}
