<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $manageProducts = Permission::firstOrCreate(['name' => 'manage_products', 'guard_name' => 'web']);
        $manageUsers = Permission::firstOrCreate(['name' => 'manage_users', 'guard_name' => 'web']);
        $reviewVerifications = Permission::firstOrCreate(['name' => 'review_verifications', 'guard_name' => 'web']);

        $roleProducteur = Role::firstOrCreate(['name' => 'producteur', 'guard_name' => 'web']);
        $roleTransformateur = Role::firstOrCreate(['name' => 'transformateur', 'guard_name' => 'web']);
        $roleDistributeur = Role::firstOrCreate(['name' => 'distributeur', 'guard_name' => 'web']);
        $roleConsommateur = Role::firstOrCreate(['name' => 'consommateur', 'guard_name' => 'web']);

        $roleProducteur->givePermissionTo($manageProducts);
        $roleTransformateur->givePermissionTo($manageProducts);
        $roleDistributeur->givePermissionTo($manageProducts);

        $this->call(AdminSeeder::class);

        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        $user->assignRole($roleProducteur);
    }
}
