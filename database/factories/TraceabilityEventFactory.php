<?php

namespace Database\Factories;

use App\Models\Batch;
use App\Models\TraceabilityEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TraceabilityEvent>
 */
class TraceabilityEventFactory extends Factory
{
    protected $model = TraceabilityEvent::class;

    private static $eventData = [
        'production' => [
            'locations' => [
                'Atelier de production - Site de Lyon',
                'Usine agroalimentaire - Zone industrielle de Rungis',
                'Chai de production - Domaine viticole de Bordeaux',
                'Ligne de production - Usine alimentaire de Nantes',
            ],
            'lat' => [45.75, 48.74, 44.84, 47.22],
            'lng' => [4.85, 2.35, -0.58, -1.55],
            'temp_range' => [15.0, 25.0],
        ],
        'processing' => [
            'locations' => [
                'Station de triage et lavage - Centre de transformation',
                'Atelier de conditionnement - Plateforme logistique',
                'Unité de pasteurisation - Usine de transformation',
                'Salle de découpe - Atelier de transformation agréé',
            ],
            'lat' => [48.85, 43.60, 43.70, 50.63],
            'lng' => [2.35, 3.88, 7.26, 3.02],
            'temp_range' => [4.0, 18.0],
        ],
        'transport' => [
            'locations' => [
                'Camion réfrigéré FR-348-KJ - Trajet A7',
                'Convoi ferroviaire - Ligne Paris-Marseille',
                'Navire cargo MSC-98234 - Trajet Méditerranée',
                'Camion livraison locale - Région Île-de-France',
            ],
            'lat' => [46.23, 45.44, 43.30, 48.90],
            'lng' => [4.85, 4.67, 5.37, 2.35],
            'temp_range' => [2.0, 12.0],
        ],
        'storage' => [
            'locations' => [
                'Entrepôt frigorifique - Hub logistique de Dijon',
                'Chambre froide négative - Centre de stockage de Toulouse',
                'Hangar de stockage à température ambiante - Orléans',
                'Réserve contrôlée - Plateforme de distribution de Lille',
            ],
            'lat' => [47.32, 43.60, 47.90, 50.63],
            'lng' => [5.04, 1.44, 1.91, 3.02],
            'temp_range' => [-20.0, 20.0],
        ],
        'distribution' => [
            'locations' => [
                'Centre de distribution E.Leclerc - Villeneuve d\'Ascq',
                'Plateau logistique Intermarché - Montauban',
                'Plateforme Carrefour Supply Chain - Caen',
                'Dépôt de distribution - Zone commerciale de Strasbourg',
            ],
            'lat' => [50.63, 43.97, 49.18, 48.58],
            'lng' => [3.13, 1.35, -0.37, 7.75],
            'temp_range' => [0.0, 15.0],
        ],
    ];

    public function definition(): array
    {
        $batch = Batch::inRandomOrder()->first() ?? Batch::factory()->create();

        $eventType = $this->faker->randomElement(TraceabilityEvent::TYPES);
        $eventTemplate = self::$eventData[$eventType] ?? self::$eventData['production'];

        $locationIndex = $this->faker->numberBetween(0, min(count($eventTemplate['locations']) - 1, count($eventTemplate['lat']) - 1, count($eventTemplate['lng']) - 1));

        $eventDate = $this->faker->dateTimeBetween('-8 months', '+1 week');

        return [
            'batch_id' => $batch->id,
            'event_type' => $eventType,
            'event_date' => $eventDate->format('Y-m-d H:i:s'),
            'location' => $eventTemplate['locations'][$locationIndex],
            'latitude' => $this->faker->optional(0.85)->randomFloat(7, $eventTemplate['lat'][$locationIndex] - 0.2, $eventTemplate['lat'][$locationIndex] + 0.2),
            'longitude' => $this->faker->optional(0.85)->randomFloat(7, $eventTemplate['lng'][$locationIndex] - 0.2, $eventTemplate['lng'][$locationIndex] + 0.2),
            'actor_id' => $this->faker->boolean(75) ? (User::inRandomOrder()->value('id') ?? User::factory()) : null,
            'description' => $this->faker->optional(0.7)->realText(150),
            'quantity' => $this->faker->optional(0.6)->randomFloat(2, 1.0, $batch->quantity ?? 5000),
            'temperature' => in_array($eventType, ['transport', 'storage'], true)
                ? $this->faker->optional(0.85)->randomFloat(2, $eventTemplate['temp_range'][0], $eventTemplate['temp_range'][1])
                : null,
            'distance_km' => $eventType === 'transport' ? $this->faker->optional(0.8)->randomFloat(2, 10, 1500) : null,
            'carbon_emission' => $eventType === 'transport' ? $this->faker->optional(0.7)->randomFloat(2, 5, 800) : null,
            'metadata' => $this->faker->boolean(40) ? [
                'operator' => $this->faker->optional()->name(),
                'vehicle_ref' => $eventType === 'transport' ? $this->faker->bothify('??-###-??') : null,
                'equipment' => $this->faker->optional()->bothify('EQ-###-????'),
                'hygiene_check' => $this->faker->boolean(85),
            ] : null,
        ];
    }

    public function type(string $type): static
    {
        return $this->state(fn (array $attributes) => [
            'event_type' => in_array($type, TraceabilityEvent::TYPES, true) ? $type : 'production',
        ]);
    }
}
