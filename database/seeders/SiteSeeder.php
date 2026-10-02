<?php

namespace Database\Seeders;

use App\Enums\SiteType;
use App\Models\Site;
use Illuminate\Database\Seeder;

class SiteSeeder extends Seeder
{
    public function run(): void
    {
        $sites = [
            ['PRD-BJA', 'Unité de production Béja', SiteType::Production, 'Béja', 36.7256, 9.1817, 10000],
            ['ENT-SFX', 'Entrepôt frigorifique Sfax', SiteType::Warehouse, 'Sfax', 34.7406, 10.7603, 20000],
            ['CDI-TUN', 'Plateforme Tunis Ben Arous', SiteType::DistributionCenter, 'Ben Arous', 36.7531, 10.2189, 30000],
            ['MAG-SOU', 'Magasin Sousse Centre', SiteType::Store, 'Sousse', 35.8256, 10.6084, 5000],
            ['MAG-MON', 'Magasin Monastir', SiteType::Store, 'Monastir', 35.7643, 10.8113, 3000],
            ['MAG-NAB', 'Magasin Nabeul', SiteType::Store, 'Nabeul', 36.4561, 10.7376, 3000],
            ['MAG-GAB', 'Magasin Gabès', SiteType::Store, 'Gabès', 33.8815, 10.0982, 3000],
            ['MAG-KAI', 'Magasin Kairouan', SiteType::Store, 'Kairouan', 35.6781, 10.0963, 2500],

            // Associations (noms fictifs) : reçoivent les dons du plan anti-gaspillage.
            // Pour une association, la capacité est la quantité maximale acceptée par don.
            ['ASSO-SFX', 'Association Assiette Solidaire Sfax', SiteType::Association, 'Sfax', 34.7521, 10.7298, 150],
            ['ASSO-SOU', 'Association Pain Partagé Sousse', SiteType::Association, 'Sousse', 35.8302, 10.6251, 120],
            ['ASSO-TUN', 'Association Table Solidaire Tunis', SiteType::Association, 'Tunis', 36.8065, 10.1815, 200],
        ];

        foreach ($sites as [$code, $name, $type, $city, $lat, $lng, $capacity]) {
            Site::updateOrCreate(['code' => $code], [
                'name' => $name,
                'type' => $type,
                'city' => $city,
                'latitude' => $lat,
                'longitude' => $lng,
                'capacity' => $capacity,
                'is_active' => true,
            ]);
        }
    }
}
