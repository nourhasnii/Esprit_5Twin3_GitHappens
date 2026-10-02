<?php

namespace Database\Factories;

use App\Enums\StockMovementType;
use App\Models\Site;
use App\Models\StockMovement;
use Database\Factories\Concerns\ResolvesDemoProduct;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * Mouvements de stock (la référence MV-… est générée automatiquement par le modèle).
 *
 * Convention du module : la quantité est toujours positive, le sens vient des sites
 * (entrée = destination, sortie = source, transfert = les deux).
 * Après avoir créé des mouvements par factory, StockService::recalculate(true) met les stocks à jour.
 *
 * Exemples :
 *   StockMovement::factory()->sale()->from($magasin)->create(['product_id' => $p->id]);
 *   StockMovement::factory()->count(30)->sale()->from($magasin)->create();
 *   StockMovement::factory()->transfer($entrepot, $magasin)->create();
 *
 * @extends Factory<StockMovement>
 */
class StockMovementFactory extends Factory
{
    use ResolvesDemoProduct;

    protected $model = StockMovement::class;

    public function definition(): array
    {
        return [
            'type' => StockMovementType::In,
            'product_id' => fn () => $this->demoProductId(),
            'batch_id' => null,
            'source_site_id' => null,
            'destination_site_id' => SiteFactory::new()->warehouse(),
            'quantity' => $this->faker->numberBetween(50, 500),
            'reason' => 'Réception fournisseur',
            'moved_at' => now()->subDays($this->faker->numberBetween(1, 30))->setTime($this->faker->numberBetween(8, 19), 0),
        ];
    }

    /** Entrée vers un site */
    public function into(Site $site): static
    {
        return $this->state(['type' => StockMovementType::In, 'source_site_id' => null, 'destination_site_id' => $site->getKey()]);
    }

    /** Vente : sortie d'un magasin (comptée par la prévision de la demande) */
    public function sale(): static
    {
        return $this->state(fn () => [
            'type' => StockMovementType::Out,
            'source_site_id' => SiteFactory::new()->retailStore(),
            'destination_site_id' => null,
            'quantity' => $this->faker->numberBetween(5, 40),
            'reason' => 'Ventes',
        ]);
    }

    /** Site d'origine d'une sortie ou d'un transfert */
    public function from(Site $site): static
    {
        return $this->state(['source_site_id' => $site->getKey()]);
    }

    /** Don solidaire : sortie qui n'est PAS une vente (exclue de la prévision) */
    public function donation(): static
    {
        return $this->state(fn () => [
            'type' => StockMovementType::Out,
            'destination_site_id' => null,
            'reason' => 'Don solidaire · Association de test',
        ]);
    }

    public function transfer(Site $from, Site $to): static
    {
        return $this->state([
            'type' => StockMovementType::Transfer,
            'source_site_id' => $from->getKey(),
            'destination_site_id' => $to->getKey(),
            'reason' => 'Réapprovisionnement',
        ]);
    }

    public function on(Carbon|string $date): static
    {
        return $this->state(['moved_at' => Carbon::parse($date)]);
    }
}
