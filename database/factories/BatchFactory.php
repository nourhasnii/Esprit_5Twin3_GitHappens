<?php

namespace Database\Factories;

use App\Models\Batch;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Lots au format FoodLens (lot_number, production_date, expiration_date, status).
 * Utilisée par le module Stocks via BatchFactory::new() : le modèle Batch de l'équipe n'est pas modifié.
 *
 * Exemples :
 *   BatchFactory::new()->expiringIn(2)->create();        // lot à 2 jours de sa DLC
 *   BatchFactory::new()->recalled()->create();           // lot rappelé (bloqué)
 *   BatchFactory::new()->for($product)->create();        // lot d'un produit donné
 *
 * @extends Factory<Batch>
 */
class BatchFactory extends Factory
{
    protected $model = Batch::class;

    public function definition(): array
    {
        return [
            'product_id' => ProductFactory::new(),
            'lot_number' => 'LOT-'.now()->format('Y').'-'.strtoupper($this->faker->unique()->bothify('####??')),
            'production_date' => now()->subDays($this->faker->numberBetween(1, 5))->toDateString(),
            'expiration_date' => now()->addDays($this->faker->numberBetween(10, 30))->toDateString(),
            'quantity' => $this->faker->numberBetween(200, 2000),
            'unit' => 'pièce',
            'status' => 'active',
        ];
    }

    public function expiringIn(int $days): static
    {
        return $this->state(['expiration_date' => now()->addDays($days)->toDateString()]);
    }

    /** DLC dépassée (statut toujours « active » : c'est la date qui le rend invendable) */
    public function pastExpiry(): static
    {
        return $this->state(['expiration_date' => now()->subDay()->toDateString()]);
    }

    public function expired(): static
    {
        return $this->state(['status' => 'expired']);
    }

    public function recalled(): static
    {
        return $this->state(['status' => 'recalled']);
    }
}
