<?php

namespace Database\Seeders;

use App\Enums\SiteType;
use App\Events\StockLevelLow;
use App\Models\Product;
use App\Models\Site;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\User;
use App\Services\Stock\StockService;
use App\Support\TunisianCalendar;
use Database\Factories\ProductFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Event;

/**
 * Historique réaliste de 8 semaines pour démontrer la prévision : un nouveau produit « Lben 1 L »
 * vendu dans les magasins, avec un rythme hebdomadaire (pic le week-end), une légère hausse,
 * l'effet de la rentrée scolaire et un peu de hasard. Les données existantes ne sont pas modifiées.
 *
 * Lancement (une seule fois) : php artisan db:seed --class=ForecastDemoSeeder
 */
class ForecastDemoSeeder extends Seeder
{
    /** Ventes moyennes par jour et par magasin */
    private const RATES = ['MAG-SOU' => 30, 'MAG-MON' => 16, 'MAG-NAB' => 22, 'MAG-GAB' => 10, 'MAG-KAI' => 12];

    /** Rythme de la semaine (1 = lundi … 7 = dimanche) : week-end plus fort */
    private const WEEK = [1 => 0.85, 2 => 0.9, 3 => 0.95, 4 => 1.0, 5 => 1.1, 6 => 1.3, 7 => 1.2];

    public function run(StockService $stocks): void
    {
        $producerId = User::query()->value('id');

        if (! $producerId) {
            $this->command?->warn('Aucun utilisateur : lancez d’abord « php artisan db:seed ».');

            return;
        }

        $product = Product::query()->firstWhere('name', 'Lben 1 L')
            ?? ProductFactory::new()->dairy()->create(['name' => 'Lben 1 L', 'unit' => 'L', 'producer_id' => $producerId]);

        if (StockMovement::query()->where('product_id', $product->getKey())->exists()) {
            $this->command?->warn('Le produit « Lben 1 L » a déjà un historique : rien à faire.');

            return;
        }

        mt_srand(2026); // hasard reproductible : la démo donne toujours les mêmes chiffres
        $days = 56;
        $category = TunisianCalendar::categoryOf($product->name);

        // Seules les alertes de rupture sont mises en sourdine pendant la génération
        Event::fakeFor(function () use ($stocks, $product, $days, $category) {
            foreach (self::RATES as $code => $rate) {
                $site = Site::query()->where('code', $code)->where('type', SiteType::Store->value)->first();

                if (! $site) {
                    continue;
                }

                $plan = [];
                for ($ago = $days; $ago >= 1; $ago--) {
                    $date = now()->subDays($ago)->setTime(19, 30);
                    $trend = 1 + 0.004 * ($days - $ago);                  // +0,4 % par jour
                    $events = TunisianCalendar::multiplier(TunisianCalendar::effects($date, $category, TunisianCalendar::isCoastal($site->city)));
                    $noise = 1 + (mt_rand(-100, 100) / 100) * 0.08;       // ± 8 %
                    $plan[] = [$date, max(0, round($rate * self::WEEK[$date->dayOfWeekIso] * $trend * $events * $noise))];
                }

                // Ligne de stock ouverte à 0 (StockFactory) : le recalcul final la remplira
                Stock::factory()->create([
                    'site_id' => $site->getKey(),
                    'product_id' => $product->getKey(),
                    'quantity' => 0,
                    'min_threshold' => 2 * $rate,
                ]);

                // Livraisons hebdomadaires (factory) : chaque lundi matin, les ventes de la semaine ;
                // la dernière livraison laisse en plus environ 5 jours de stock
                $weeks = array_chunk($plan, 7);
                foreach ($weeks as $i => $week) {
                    StockMovement::factory()->into($site)->on($week[0][0]->copy()->setTime(7, 0))->create([
                        'product_id' => $product->getKey(),
                        'quantity' => array_sum(array_column($week, 1)) + ($i === count($weeks) - 1 ? 5 * $rate : 0),
                        'reason' => 'Livraison hebdomadaire',
                    ]);
                }

                // Historique des ventes généré par la factory (une vente par jour)
                foreach ($plan as [$date, $quantity]) {
                    if ($quantity > 0) {
                        StockMovement::factory()->sale()->from($site)->on($date)->create([
                            'product_id' => $product->getKey(),
                            'quantity' => $quantity,
                        ]);
                    }
                }
            }
        }, [StockLevelLow::class]);

        // Les ventes créées par factory ne passent pas par StockService : on recalcule les stocks
        $stocks->recalculate(fix: true);

        $this->command?->info('Historique de 8 semaines créé pour « Lben 1 L » dans '.count(self::RATES).' magasins.');
    }
}
