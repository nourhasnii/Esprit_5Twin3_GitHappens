<?php

namespace Database\Factories;

use App\Enums\SiteType;
use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Sites tunisiens réalistes (ville, coordonnées GPS, capacité selon le type).
 *
 * Exemples :
 *   Site::factory()->retailStore()->count(5)->create();
 *   Site::factory()->warehouse()->inCity('Sfax')->create();
 *   Site::factory()->association()->create();
 *
 * @extends Factory<Site>
 */
class SiteFactory extends Factory
{
    protected $model = Site::class;

    /** Villes tunisiennes avec leurs coordonnées (centre-ville) */
    public const CITIES = [
        'Tunis' => [36.8065, 10.1815], 'Sfax' => [34.7406, 10.7603], 'Sousse' => [35.8256, 10.6084],
        'Kairouan' => [35.6781, 10.0963], 'Bizerte' => [37.2744, 9.8739], 'Gabès' => [33.8815, 10.0982],
        'Ariana' => [36.8665, 10.1647], 'Gafsa' => [34.4250, 8.7842], 'Monastir' => [35.7643, 10.8113],
        'Ben Arous' => [36.7531, 10.2189], 'Kasserine' => [35.1676, 8.8365], 'Médenine' => [33.3549, 10.5055],
        'Nabeul' => [36.4561, 10.7376], 'Béja' => [36.7256, 9.1817], 'Mahdia' => [35.5047, 11.0622],
        'Jendouba' => [36.5011, 8.7802], 'Tozeur' => [33.9197, 8.1335], 'Le Kef' => [36.1822, 8.7147],
    ];

    private const PREFIX = [
        'production' => 'PRD', 'warehouse' => 'ENT', 'distribution_center' => 'CDI', 'store' => 'MAG', 'association' => 'ASSO',
    ];

    private const CAPACITY = [
        'production' => [5000, 15000], 'warehouse' => [10000, 30000], 'distribution_center' => [15000, 40000],
        'store' => [1500, 6000], 'association' => [80, 250],
    ];

    public function definition(): array
    {
        $city = $this->faker->randomElement(array_keys(self::CITIES));
        [$lat, $lng] = self::CITIES[$city];

        return [
            'type' => SiteType::Store,
            'city' => $city,
            // Léger décalage autour du centre-ville (environ ± 2 km)
            'latitude' => round($lat + $this->faker->randomFloat(4, -0.02, 0.02), 7),
            'longitude' => round($lng + $this->faker->randomFloat(4, -0.02, 0.02), 7),
            'address' => $this->faker->buildingNumber().' avenue '.$this->faker->randomElement(['Habib Bourguiba', 'de la République', 'Farhat Hached', 'de l’Indépendance', 'Hédi Chaker']),
            'code' => fn (array $a) => self::PREFIX[$this->typeOf($a)].'-'.strtoupper($this->faker->unique()->bothify('???##')),
            'name' => fn (array $a) => $this->nameFor($this->typeOf($a), $a['city']),
            'capacity' => fn (array $a) => $this->faker->numberBetween(...self::CAPACITY[$this->typeOf($a)]),
            'is_active' => true,
        ];
    }

    public function production(): static
    {
        return $this->state(['type' => SiteType::Production]);
    }

    public function warehouse(): static
    {
        return $this->state(['type' => SiteType::Warehouse]);
    }

    public function distributionCenter(): static
    {
        return $this->state(['type' => SiteType::DistributionCenter]);
    }

    public function retailStore(): static
    {
        return $this->state(['type' => SiteType::Store]);
    }

    /** Association qui reçoit les dons du plan anti-gaspillage (capacité = quantité max par don) */
    public function association(): static
    {
        return $this->state(['type' => SiteType::Association]);
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }

    /** Site sans GPS : il est exclu des recommandations */
    public function withoutCoordinates(): static
    {
        return $this->state(['latitude' => null, 'longitude' => null]);
    }

    public function inCity(string $city): static
    {
        [$lat, $lng] = self::CITIES[$city] ?? [null, null];

        return $this->state(['city' => $city, 'latitude' => $lat, 'longitude' => $lng]);
    }

    private function typeOf(array $attributes): string
    {
        $type = $attributes['type'] ?? SiteType::Store;

        return $type instanceof SiteType ? $type->value : (string) $type;
    }

    private function nameFor(string $type, string $city): string
    {
        return match ($type) {
            'production' => 'Unité de production '.$city,
            'warehouse' => 'Entrepôt frigorifique '.$city,
            'distribution_center' => 'Plateforme de distribution '.$city,
            'association' => 'Association '.$this->faker->randomElement(['Assiette Solidaire', 'Pain Partagé', 'Table Solidaire', 'Cœur Ouvert']).' '.$city,
            default => 'Magasin '.$city.' '.$this->faker->randomElement(['Centre', 'Nord', 'Sud', 'Médina', 'Lac']),
        };
    }
}
