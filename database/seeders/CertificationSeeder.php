<?php

namespace Database\Seeders;

use App\Models\Certification;
use App\Models\Product;
use Illuminate\Database\Seeder;

class CertificationSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::all();
        if ($products->isEmpty()) {
            $this->command->warn('Aucun produit trouvé. Exécutez d\'abord ProductSeeder.');

            return;
        }

        $certificationTemplates = [
            [
                'name' => 'Agriculture Biologique (AB)',
                'issuing_organization' => 'Agence BIO',
                'duration_years' => 2,
                'status' => Certification::STATUS_VALID,
            ],
            [
                'name' => 'Label Rouge',
                'issuing_organization' => 'INAO',
                'duration_years' => 3,
                'status' => Certification::STATUS_VALID,
            ],
            [
                'name' => 'Appellation d\'Origine Protégée (AOP)',
                'issuing_organization' => 'INAO',
                'duration_years' => 5,
                'status' => Certification::STATUS_VALID,
            ],
            [
                'name' => 'Fairtrade / Max Havelaar',
                'issuing_organization' => 'Max Havelaar France',
                'duration_years' => 2,
                'status' => Certification::STATUS_VALID,
            ],
            [
                'name' => 'MSC (Marine Stewardship Council)',
                'issuing_organization' => 'MSC International',
                'duration_years' => 3,
                'status' => Certification::STATUS_VALID,
            ],
            [
                'name' => 'Indication Géographique Protégée (IGP)',
                'issuing_organization' => 'INAO',
                'duration_years' => 5,
                'status' => Certification::STATUS_VALID,
            ],
            [
                'name' => 'Bleu-Blanc-Coeur',
                'issuing_organization' => 'Association Bleu-Blanc-Coeur',
                'duration_years' => 2,
                'status' => Certification::STATUS_EXPIRING,
            ],
            [
                'name' => 'Rainforest Alliance / UTZ',
                'issuing_organization' => 'Rainforest Alliance',
                'duration_years' => 3,
                'status' => Certification::STATUS_VALID,
            ],
        ];

        $now = today();
        $createdCount = 0;
        $assignedCount = 0;

        foreach ($certificationTemplates as $index => $template) {
            $product = $products->get($index % $products->count());

            $issuedAt = (clone $now)->subMonths(rand(3, $template['duration_years'] * 12 - 4));
            if ($template['status'] === Certification::STATUS_EXPIRING) {
                $expiresAt = (clone $now)->addDays(rand(5, 25));
            } else {
                $expiresAt = (clone $issuedAt)->addYears($template['duration_years'])->subDays(rand(10, 60));
            }

            $certNumberBase = strtoupper(substr(preg_replace('/[^A-Z0-9]/', '', $template['name']), 0, 3));
            $productSuffix = str_pad((string) $product->id, 3, '0', STR_PAD_LEFT);

            $cert = Certification::updateOrCreate(
                [
                    'name' => $template['name'],
                    'product_id' => $product->id,
                ],
                [
                    'certificate_number' => "{$certNumberBase}-{$productSuffix}-{$index}",
                    'issuing_organization' => $template['issuing_organization'],
                    'issued_at' => $issuedAt->format('Y-m-d'),
                    'expires_at' => $expiresAt->format('Y-m-d'),
                    'status' => Certification::calculateStatus($expiresAt, $template['status']),
                    'document_path' => null,
                    'notes' => "Certification délivrée pour la gamme {$product->name}. Vérifiée et conforme aux référentiels en vigueur.",
                ]
            );

            if ($cert->wasRecentlyCreated) {
                $createdCount++;
            }
            $assignedCount++;
        }

        $additionalCount = 0;
        $productsWithOrganic = Product::where('is_organic', true)
            ->whereDoesntHave('certifications', fn ($q) => $q->where('name', 'like', '%Biologique%'))
            ->take(5)
            ->get();

        foreach ($productsWithOrganic as $product) {
            $issuedAt = (clone $now)->subMonths(rand(2, 18));
            $expiresAt = (clone $issuedAt)->addYears(2);
            $productSuffix = str_pad((string) $product->id, 3, '0', STR_PAD_LEFT);

            $cert = Certification::updateOrCreate(
                [
                    'name' => 'Agriculture Biologique (AB)',
                    'product_id' => $product->id,
                ],
                [
                    'certificate_number' => "AB-{$productSuffix}-SUPPL",
                    'issuing_organization' => 'Agence BIO',
                    'issued_at' => $issuedAt->format('Y-m-d'),
                    'expires_at' => $expiresAt->format('Y-m-d'),
                    'status' => Certification::calculateStatus($expiresAt, Certification::STATUS_VALID),
                    'document_path' => null,
                    'notes' => "Certification BIO associée automatiquement sur la base de l'attribut is_organic.",
                ]
            );

            if ($cert->wasRecentlyCreated) {
                $createdCount++;
            }
            $additionalCount++;
        }

        $extra = Certification::factory()
            ->count(3)
            ->expiring()
            ->create();
        $createdCount += $extra->count();

        $this->command->info("CertificationSeeder : {$assignedCount} templates assignés, {$additionalCount} certifications BIO ajoutés, {$createdCount} nouveaux enregistrements, {$extra->count()} certifications supplémentaires via factory.");
    }
}
