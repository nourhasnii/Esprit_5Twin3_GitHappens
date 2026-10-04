<?php

namespace Database\Factories;

use App\Models\Batch;
use App\Models\TransportCondition;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TransportCondition>
 */
class TransportConditionFactory extends Factory
{
    protected $model = TransportCondition::class;

    public function definition(): array
    {
        $batch = Batch::inRandomOrder()->first() ?? Batch::factory()->create();

        return [
            'batch_id' => $batch->id,
            'traceability_event_id' => null,
            'recorded_at' => fake()->dateTimeBetween('-30 days', 'now'),
            'temperature' => fake()->randomFloat(2, 2.1, 4.9),
            'humidity' => fake()->randomFloat(2, 55, 80),
            'location' => fake()->randomElement([
                'Camion réfrigéré - A7',
                'Entrepôt frigorifique - Dijon',
                'Plateforme logistique - Rungis',
                'Centre de distribution - Lille',
            ]),
            'duration_minutes' => fake()->numberBetween(30, 720),
            'notes' => fake()->optional(0.35)->sentence(),
            'created_by' => User::inRandomOrder()->value('id'),
        ];
    }

    public function outOfRange(): static
    {
        return $this->state(fn (array $attributes) => [
            'temperature' => fake()->randomElement([
                fake()->randomFloat(2, -2, 1.99),
                fake()->randomFloat(2, 5.01, 12),
            ]),
        ]);
    }
}
