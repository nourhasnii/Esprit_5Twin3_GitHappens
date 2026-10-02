<?php

namespace Database\Factories\Concerns;

use App\Models\Product;
use App\Models\User;
use Database\Factories\UserFactory;

/**
 * Produit laitier utilisé par défaut dans les factories du module Stocks.
 * Réutilise un produit existant, sinon en crée un avec des valeurs fixes (catégorie, origine, producteur),
 * pour que les factories du module donnent toujours un résultat prévisible.
 */
trait ResolvesDemoProduct
{
    protected function demoProductId(): int
    {
        $existing = Product::query()->where('category', 'Produits Laitiers')->value('id');

        if ($existing) {
            return (int) $existing;
        }

        return (int) Product::query()->create([
            'name' => 'Lait frais 1 L',
            'description' => 'Produit créé par les factories du module Stocks.',
            'category' => 'Produits Laitiers',
            'origin_country' => 'Tunisie',
            'producer_id' => User::query()->value('id') ?? UserFactory::new()->create()->getKey(),
            'unit' => 'L',
            'verification_status' => 'verified',
        ])->getKey();
    }
}
