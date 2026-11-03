<?php

use App\Models\Batch;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Spatie\Permission\Models\Role;

/** Crée un utilisateur avec les permissions attribuées au rôle en production. */
function stockUser(string $role = 'distributeur'): User
{
    app(RolePermissionSeeder::class)->run();

    Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user->fresh();
}

/** Produit de test créé à la main (sans ProductFactory, qui échoue parfois). */
function stockProduct(string $name = 'Yaourt nature 125 g', ?User $producer = null): Product
{
    return Product::create([
        'name' => $name,
        'category' => 'Produits Laitiers',
        'origin_country' => 'Tunisie',
        'producer_id' => ($producer ?? User::factory()->create())->id,
        'unit' => 'pot',
        'verification_status' => 'verified',
    ]);
}

/** Lot actif dont la DLC est dans $days jours. */
function stockBatch(Product $product, int $days = 10, string $status = 'active'): Batch
{
    return Batch::factory()->create([
        'product_id' => $product->id,
        'production_date' => now()->subDays(2)->toDateString(),
        'expiration_date' => now()->addDays($days)->toDateString(),
        'quantity' => 1000,
        'unit' => $product->unit,
        'status' => $status,
    ]);
}
