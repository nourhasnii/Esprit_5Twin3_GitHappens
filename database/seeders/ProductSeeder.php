<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $producerRole = Role::firstOrCreate(['name' => 'producteur', 'guard_name' => 'web']);

        $producer = User::role('producteur')->inRandomOrder()->first();
        if (! $producer) {
            $producer = User::factory()->create([
                'name' => 'Ferme des Vignes Dorées',
                'email' => 'ferme.vignes+producteur@example.com',
            ]);
            $producer->assignRole($producerRole);
        }

        $secondProducer = User::where('email', '!=', $producer->email)->role('producteur')->inRandomOrder()->first();
        if (! $secondProducer) {
            $secondProducer = User::factory()->create([
                'name' => 'Établissements Martin SARL',
                'email' => 'etablissements.martin+producteur@example.com',
            ]);
            $secondProducer->assignRole($producerRole);
        }

        $products = [
            [
                'name' => "Huile d'Olive Extra Vierge de Provence AOC",
                'description' => "Huile d'olive pressée à froid, issue d'oliviers centenaires de la région provençale. Notes fruitées vertes, amande fraîche et artichaut.",
                'category' => 'Epicerie',
                'origin_country' => 'France',
                'origin_region' => 'Provence',
                'producer_id' => $producer->id,
                'unit' => 'L',
                'is_organic' => true,
                'carbon_footprint' => 2.45,
                'verification_status' => 'verified',
            ],
            [
                'name' => 'Miel de Lavande des Hautes-Alpes',
                'description' => 'Miel monofloral récolté à plus de 1200m d\'altitude. Cristallisation naturelle, arômes de lavande et de thym.',
                'category' => 'Epicerie',
                'origin_country' => 'France',
                'origin_region' => 'Hautes-Alpes',
                'producer_id' => $producer->id,
                'unit' => 'kg',
                'is_organic' => true,
                'carbon_footprint' => 1.20,
                'verification_status' => 'verified',
            ],
            [
                'name' => 'Fromage de Chèvre AOP Crottin de Chavignol',
                'description' => 'Petit fromage de chèvre à pâte molle et croute naturelle. Affiné 2 à 3 semaines en cave de température contrôlée.',
                'category' => 'Produits Laitiers',
                'origin_country' => 'France',
                'origin_region' => 'Bourgogne',
                'producer_id' => $secondProducer->id,
                'unit' => 'pièce',
                'is_organic' => false,
                'carbon_footprint' => 5.80,
                'verification_status' => 'verified',
            ],
            [
                'name' => 'Tomates Cerises Heirloom Bio',
                'description' => 'Variété ancienne de tomates cerises multicolores (jaune, orange, rouge, pourpre). Cultivées sous abri non chauffé.',
                'category' => 'Fruits & Légumes',
                'origin_country' => 'France',
                'origin_region' => 'Rhône-Alpes',
                'producer_id' => $secondProducer->id,
                'unit' => 'kg',
                'is_organic' => true,
                'carbon_footprint' => 0.95,
                'verification_status' => 'verified',
            ],
            [
                'name' => 'Saumon Atlantique Élevé Durablement Label Rouge',
                'description' => 'Filets de saumon élevés en mer de Norvège, sans antibiotiques. Nourriture à base de farine et huile de poisson MSC.',
                'category' => 'Poissons & Fruits de Mer',
                'origin_country' => 'Norvège',
                'origin_region' => 'Troms',
                'producer_id' => $producer->id,
                'unit' => 'kg',
                'is_organic' => false,
                'carbon_footprint' => 6.40,
                'verification_status' => 'verified',
            ],
            [
                'name' => 'Poulet Fermier Label Rouge Plein Air',
                'description' => 'Poulets de la race Label Rouge, élevés en plein air minimum 81 jours. Alimentation à base de céréales sans OGM.',
                'category' => 'Viandes & Volailles',
                'origin_country' => 'France',
                'origin_region' => 'Bretagne',
                'producer_id' => $secondProducer->id,
                'unit' => 'pièce',
                'is_organic' => false,
                'carbon_footprint' => 4.10,
                'verification_status' => 'verified',
            ],
            [
                'name' => 'Riz Basmati Bio du Punjab',
                'description' => 'Riz basmati à grains longs, vieilli 12 mois. Culture en terrasses inondées, récolte manuelle, parure 5%.',
                'category' => 'Epicerie',
                'origin_country' => 'Inde',
                'origin_region' => 'Punjab',
                'producer_id' => $producer->id,
                'unit' => 'kg',
                'is_organic' => true,
                'carbon_footprint' => 3.20,
                'verification_status' => 'pending',
            ],
            [
                'name' => 'Chocolat Noir 72% Grand Cru Pérou',
                'description' => 'Tablette de chocolat noir, origine unique Piura Pérou. Fèves Chuncho sélectionnées, conchage 72h.',
                'category' => 'Confiserie',
                'origin_country' => 'Pérou',
                'origin_region' => 'Piura',
                'producer_id' => $secondProducer->id,
                'unit' => 'g',
                'is_organic' => true,
                'carbon_footprint' => 8.70,
                'verification_status' => 'verified',
            ],
            [
                'name' => 'Café Arabica Colombien Supremo',
                'description' => 'Grains de café Arabica de la région de Huila, altitude 1800m. Torréfaction artisanale medium, notes caramel et agrume.',
                'category' => 'Boissons',
                'origin_country' => 'Colombie',
                'origin_region' => 'Huila',
                'producer_id' => $producer->id,
                'unit' => 'kg',
                'is_organic' => false,
                'carbon_footprint' => 5.60,
                'verification_status' => 'pending',
            ],
            [
                'name' => 'Pommes Golden Delicious Bio du Limousin',
                'description' => 'Pommes Golden Delicious cueillies à maturité optimale. Conservation en atmosphère contrôlée, sans traitement post-récolte.',
                'category' => 'Fruits & Légumes',
                'origin_country' => 'France',
                'origin_region' => 'Limousin',
                'producer_id' => $secondProducer->id,
                'unit' => 'kg',
                'is_organic' => true,
                'carbon_footprint' => 0.45,
                'verification_status' => 'verified',
            ],
            [
                'name' => 'Vin Rouge Bordeaux Grand Cru Classé 2019',
                'description' => 'Assemblage Cabernet Sauvignon / Merlot. Millésime 2019, élevé 18 mois en fûts de chêne français neufs.',
                'category' => 'Boissons',
                'origin_country' => 'France',
                'origin_region' => 'Bordeaux',
                'producer_id' => $producer->id,
                'unit' => 'L',
                'is_organic' => false,
                'carbon_footprint' => 11.30,
                'verification_status' => 'verified',
            ],
            [
                'name' => 'Lentilles Vertes du Puy AOC',
                'description' => 'Lentilles vertes cultivées sur les cieux volcaniques du Puy. Cuisson 20-25 min, consistance ferme et goût marqué.',
                'category' => 'Epicerie',
                'origin_country' => 'France',
                'origin_region' => 'Auvergne',
                'producer_id' => $secondProducer->id,
                'unit' => 'kg',
                'is_organic' => true,
                'carbon_footprint' => 1.10,
                'verification_status' => 'rejected',
            ],
            [
                'name' => 'Amandes Complètes Biologiques de la Drôme',
                'description' => 'Amandes décortiquées, variété Ferragnès. Récolte mécanique, séchage naturel au soleil. Calibre 18/20.',
                'category' => 'Epicerie',
                'origin_country' => 'France',
                'origin_region' => 'Drôme',
                'producer_id' => $producer->id,
                'unit' => 'kg',
                'is_organic' => true,
                'carbon_footprint' => 2.10,
                'verification_status' => 'verified',
            ],
            [
                'name' => 'Pain au Levain Traditionnel de Campagne',
                'description' => 'Pain de campagne façonné à la main, farine T80 issue de blé français. Levain naturel, levure de boulanger, fermentation 24h.',
                'category' => 'Boulangerie',
                'origin_country' => 'France',
                'origin_region' => 'Normandie',
                'producer_id' => $secondProducer->id,
                'unit' => 'pièce',
                'is_organic' => false,
                'carbon_footprint' => 1.05,
                'verification_status' => 'verified',
            ],
        ];

        $createdProducts = collect();
        foreach ($products as $productData) {
            $product = Product::updateOrCreate(
                ['name' => $productData['name']],
                array_merge(
                    ['image' => null, 'barcode' => null],
                    $productData
                )
            );
            $createdProducts->push($product);
        }

        $remainingCount = max(0, 10 - $createdProducts->count());
        if ($remainingCount > 0) {
            Product::factory()
                ->count($remainingCount)
                ->verified()
                ->create(['producer_id' => $producer->id])
                ->each(fn ($p) => $createdProducts->push($p));
        }

        $this->command->info("ProductSeeder : {$createdProducts->count()} produits gérés (updateOrCreate + factory).");
    }
}
