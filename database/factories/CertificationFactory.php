<?php

namespace Database\Factories;

use App\Models\Certification;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Certification>
 */
class CertificationFactory extends Factory
{
    protected $model = Certification::class;

    private static $certificationCatalog = [
        [
            'name' => 'Agriculture Biologique (AB)',
            'issuing_organization' => 'Agence BIO',
            'duration_years' => 2,
            'valid_ratio' => 0.6,
        ],
        [
            'name' => 'Label Rouge',
            'issuing_organization' => 'INAO',
            'duration_years' => 3,
            'valid_ratio' => 0.7,
        ],
        [
            'name' => 'Appellation d\'Origine Contrôlée (AOC)',
            'issuing_organization' => 'INAO',
            'duration_years' => 5,
            'valid_ratio' => 0.8,
        ],
        [
            'name' => 'Fairtrade / Max Havelaar',
            'issuing_organization' => 'Max Havelaar France',
            'duration_years' => 2,
            'valid_ratio' => 0.6,
        ],
        [
            'name' => 'MSC (Marine Stewardship Council)',
            'issuing_organization' => 'MSC International',
            'duration_years' => 3,
            'valid_ratio' => 0.7,
        ],
        [
            'name' => 'IGP (Indication Géographique Protégée)',
            'issuing_organization' => 'INAO',
            'duration_years' => 5,
            'valid_ratio' => 0.8,
        ],
        [
            'name' => 'Bleu-Blanc-Coeur',
            'issuing_organization' => 'Association Bleu-Blanc-Coeur',
            'duration_years' => 2,
            'valid_ratio' => 0.6,
        ],
        [
            'name' => 'UTZ Certified',
            'issuing_organization' => 'Rainforest Alliance',
            'duration_years' => 3,
            'valid_ratio' => 0.65,
        ],
    ];

    public function definition(): array
    {
        $catalog = $this->faker->randomElement(self::$certificationCatalog);
        $issuedAt = Carbon::instance($this->faker->dateTimeBetween('-4 years', '-6 months'));
        $expiresAt = $issuedAt->copy()->addYears($catalog['duration_years']);

        $isValid = $this->faker->boolean($catalog['valid_ratio'] * 100);
        if (! $isValid && $this->faker->boolean(50)) {
            $expiresAt = $issuedAt->copy()->subMonth();
        }

        $rawStatus = $isValid ? Certification::STATUS_VALID : Certification::STATUS_EXPIRED;
        $effectiveStatus = Certification::calculateStatus($expiresAt, $rawStatus);

        return [
            'product_id' => Product::inRandomOrder()->value('id') ?? Product::factory(),
            'name' => $catalog['name'],
            'certificate_number' => strtoupper(substr(preg_replace('/[^A-Z0-9]/', '', $catalog['name']), 0, 3))
                . '-' . $this->faker->unique()->regexify('[A-Z0-9]{2}-[0-9]{5}-[A-Z0-9]{2}'),
            'issuing_organization' => $catalog['issuing_organization'],
            'issued_at' => $issuedAt->toDateString(),
            'expires_at' => $expiresAt->toDateString(),
            'document_path' => null,
            'status' => $effectiveStatus,
            'notes' => $this->faker->optional(0.4)->realText(150),
        ];
    }

    public function valid(): static
    {
        return $this->state(function (array $attributes) {
            $issuedAt = Carbon::instance($this->faker->dateTimeBetween('-1 years', '-1 months'));
            $expiresAt = $issuedAt->copy()->addYears(2);

            return [
                'issued_at' => $issuedAt->toDateString(),
                'expires_at' => $expiresAt->toDateString(),
                'status' => Certification::calculateStatus($expiresAt, Certification::STATUS_VALID),
            ];
        });
    }

    public function expiring(): static
    {
        return $this->state(function (array $attributes) {
            $issuedAt = Carbon::instance($this->faker->dateTimeBetween('-3 years', '-2 years'));
            $expiresAt = Carbon::instance($this->faker->dateTimeBetween('+1 week', '+25 days'));

            return [
                'issued_at' => $issuedAt->toDateString(),
                'expires_at' => $expiresAt->toDateString(),
                'status' => Certification::calculateStatus($expiresAt, Certification::STATUS_VALID),
            ];
        });
    }

    public function expired(): static
    {
        return $this->state(function (array $attributes) {
            $issuedAt = Carbon::instance($this->faker->dateTimeBetween('-5 years', '-4 years'));
            $expiresAt = Carbon::instance($this->faker->dateTimeBetween('-11 months', '-1 week'));

            return [
                'issued_at' => $issuedAt->toDateString(),
                'expires_at' => $expiresAt->toDateString(),
                'status' => Certification::calculateStatus($expiresAt, Certification::STATUS_VALID),
            ];
        });
    }

    public function pending(): static
    {
        return $this->state(function (array $attributes) {
            $issuedAt = Carbon::instance($this->faker->dateTimeBetween('-2 months', '-1 week'));
            $expiresAt = $issuedAt->copy()->addYears(2);

            return [
                'issued_at' => $issuedAt->toDateString(),
                'expires_at' => $expiresAt->toDateString(),
                'status' => Certification::STATUS_PENDING,
            ];
        });
    }
}
