<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $password = env('ADMIN_PASSWORD');

        if (blank($password)) {
            throw new \RuntimeException('ADMIN_PASSWORD must be set before running AdminSeeder.');
        }

        $adminRole = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);
        foreach (['manage_products', 'manage_users', 'review_verifications'] as $permissionName) {
            $adminRole->givePermissionTo(Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']));
        }

        $admin = User::firstOrCreate(
            ['email' => 'nour.hasni02@gmail.com'],
            [
                'name' => 'Nour Hasni',
                'password' => Hash::make(env('ADMIN_PASSWORD')),
            ],
        );

        $admin->assignRole('admin');
    }
}