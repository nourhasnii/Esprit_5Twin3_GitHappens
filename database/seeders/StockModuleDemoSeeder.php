<?php

namespace Database\Seeders;

use App\Models\Batch;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Démo complète du module Stocks & Optimisation sur FoodLens :
 * 2 produits laitiers et 2 lots (si absents), puis sites, associations, stocks et 30 jours de ventes.
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

        $product = fn (string $name, string $unit) => Product::query()->firstOrCreate(['name' => $name], [
            'description' => 'Produit de démonstration du module Stocks & Optimisation.',
            'category' => 'Produits laitiers',
            'origin_country' => 'Tunisie',
            'producer_id' => $producerId,
            'unit' => $unit,
            'verification_status' => 'verified',
        ]);

        $yaourt = $product('Yaourt nature 125 g', 'pot');
        $lait = $product('Lait frais 1 L', 'L');

        $batch = fn (Product $p, string $lot, int $days, float $qty, string $unit) => Batch::query()->firstOrCreate(['lot_number' => $lot], [
            'product_id' => $p->getKey(),
            'production_date' => now()->subDays(2)->toDateString(),
            'expiration_date' => now()->addDays($days)->toDateString(),
            'quantity' => $qty,
            'unit' => $unit,
            'status' => 'active',
        ]);

        // Le yaourt périme dans 12 jours, le lait dans 2 jours (pour la démo du plan anti-gaspillage)
        $batch($yaourt, 'LOT-2026-001', 12, 1000, 'pot');
        $batch($lait, 'LOT-2026-002', 2, 1000, 'L');

        $this->command?->info('Produits et lots de démonstration prêts.');

        $this->call(StockDemoSeeder::class);
    }
}
