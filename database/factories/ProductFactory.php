<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Produits au format FoodLens (catégorie, origine, producteur, unité).
 * Utilisée par le module Stocks via ProductFactory::new() : le modèle Product de l'équipe n'est pas modifié.
 *
 * Exemples :
 *   ProductFactory::new()->dairy()->create(['name' => 'Lben 1 L']);
 *   ProductFactory::new()->count(10)->create();
 *
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    private const DAIRY = ['Lait frais 1 L', 'Yaourt nature 125 g', 'Lben 1 L', 'Raïb 1 L', 'Fromage frais 200 g', 'Beurre doux 250 g', 'Crème fraîche 20 cl'];

    private const GROCERY = ['Harissa 135 g', 'Huile d’olive 1 L', 'Dattes Deglet Nour 500 g', 'Couscous fin 1 kg', 'Tomates 1 kg', 'Oranges Maltaises 1 kg'];

    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(self::GROCERY),
            'description' => 'Produit généré pour les tests du module Stocks.',
            'category' => 'Épicerie',
            'origin_country' => 'Tunisie',
            'origin_region' => $this->faker->randomElement(array_keys(SiteFactory::CITIES)),
            // Producteur : un utilisateur existant, sinon un nouveau
            'producer_id' => fn () => User::query()->value('id') ?? UserFactory::new()->create()->getKey(),
            'unit' => $this->faker->randomElement(['kg', 'L', 'pièce']),
            'is_organic' => $this->faker->boolean(20),
            'verification_status' => 'verified',
        ];
    }

    /** Produit laitier : reconnu par la prévision (effet Ramadan, rentrée…) */
    public function dairy(): static
    {
        return $this->state(fn () => [
            'name' => $this->faker->randomElement(self::DAIRY),
            'category' => 'Produits laitiers',
            'unit' => 'L',
        ]);
    }
}
