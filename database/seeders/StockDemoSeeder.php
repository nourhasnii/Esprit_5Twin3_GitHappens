<?php

namespace Database\Seeders;

use App\Enums\StockMovementType;
use App\Events\StockLevelLow;
use App\Models\Batch;
use App\Models\Site;
use App\Models\StockMovement;
use App\Services\Stock\StockService;
use App\Support\BatchAttributes;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Event;

/**
 * Données de démonstration pour la soutenance.
 * Prérequis : des produits et des lots existent déjà (module Lots).
 * Lancement : php artisan db:seed --class=StockDemoSeeder
 */
class StockDemoSeeder extends Seeder
{
    /** Ventes journalières moyennes par magasin et stock restant visé à la fin de l'historique. */
    private const STORES = [
        'MAG-SOU' => ['rate' => 40, 'left' => 120],
        'MAG-NAB' => ['rate' => 25, 'left' => 400],
        'MAG-MON' => ['rate' => 15, 'left' => 150],
        'MAG-GAB' => ['rate' => 6, 'left' => 300],
        'MAG-KAI' => ['rate' => 0, 'left' => 250],
    ];

    /** Lots de démonstration créés par StockModuleDemoSeeder / DemoDataSeeder */
    private const DEMO_LOTS = ['LOT-2026-001', 'LOT-2026-002'];

    public function run(StockService $stocks): void
    {
        $this->call(SiteSeeder::class);

        $valid = Batch::query()->with('product')->get()
            ->reject(fn (Batch $b) => BatchAttributes::isBlocked($b) || BatchAttributes::isExpired($b));

        // Priorité aux lots de démonstration (yaourt puis lait) : la base peut déjà contenir
        // d'autres lots, par exemple ceux des autres modules de l'équipe
        $demo = $valid
            ->filter(fn (Batch $b) => in_array(BatchAttributes::code($b), self::DEMO_LOTS, true))
            ->sortBy(fn (Batch $b) => array_search(BatchAttributes::code($b), self::DEMO_LOTS, true));

        $batches = ($demo->isNotEmpty() ? $demo : $valid)
            ->unique('product_id')
            ->take(2)
            ->values();

        if ($batches->isEmpty()) {
            $this->command?->warn('Aucun lot valide trouvé : créez d’abord des produits et des lots.');

            return;
        }

        $warehouse = Site::where('code', 'ENT-SFX')->firstOrFail();

        // Déjà lancé : on ne double pas les stocks ni l'historique des ventes
        if (StockMovement::query()->where('destination_site_id', $warehouse->id)->where('reason', 'Réception production')->exists()) {
            $this->command?->warn('Les stocks de démonstration existent déjà : rien à faire.');

            return;
        }

        // Pas de notifications pendant la génération de l'historique
        Event::fakeFor(function () use ($stocks, $batches, $warehouse) {
            foreach ($batches as $index => $batch) {
                $factor = $index === 0 ? 1.0 : 0.6;
                $this->seedStoreHistory($stocks, (int) $batch->product_id, $factor);

                $stocks->record([
                    'type' => StockMovementType::In,
                    'destination_site_id' => $warehouse->id,
                    'batch_id' => $batch->id,
                    'quantity' => 800,
                    'reason' => 'Réception production',
                    'moved_at' => now()->subDay(),
                ]);
            }
        }, [StockLevelLow::class]);

        $this->command?->info('Stocks de démonstration créés pour '.$batches->count().' lot(s) à '.$warehouse->name.'.');
    }

    /** Stock initial il y a 31 jours puis ventes quotidiennes (±20 %, moyenne exacte). */
    private function seedStoreHistory(StockService $stocks, int $productId, float $factor): void
    {
        foreach (self::STORES as $code => $profile) {
            $site = Site::where('code', $code)->firstOrFail();
            $rate = $profile['rate'] * $factor;

            $stocks->record([
                'type' => StockMovementType::In,
                'destination_site_id' => $site->id,
                'product_id' => $productId,
                'quantity' => round($rate * 30 + $profile['left']),
                'reason' => 'Stock initial',
                'moved_at' => now()->subDays(31),
            ]);

            $stocks->setThreshold($site->id, $productId, 150);

            for ($day = 30; $day >= 1; $day--) {
                $quantity = round($rate * ($day % 2 === 0 ? 1.2 : 0.8));

                if ($quantity <= 0) {
                    continue;
                }

                $stocks->record([
                    'type' => StockMovementType::Out,
                    'source_site_id' => $site->id,
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'reason' => 'Ventes',
                    'moved_at' => now()->subDays($day)->setTime(19, 0),
                ]);
            }
        }
    }
}
