<?php

namespace Database\Factories;

use App\Models\Alert;
use App\Models\TransportCondition;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Alert>
 */
class AlertFactory extends Factory
{
    protected $model = Alert::class;

    public function definition(): array
    {
        $condition = TransportCondition::inRandomOrder()->first();

        return [
            'batch_id' => $condition?->batch_id,
            'transport_condition_id' => $condition?->id,
            'type' => fake()->randomElement(Alert::TYPES),
            'severity' => fake()->randomElement(Alert::SEVERITIES),
            'title' => fake()->sentence(4),
            'message' => fake()->sentence(12),
            'status' => 'open',
            'risk_score' => fake()->randomFloat(2, 10, 100),
            'resolved_at' => null,
            'resolved_by' => null,
            'created_by' => User::inRandomOrder()->value('id'),
        ];
    }

    public function info(): static
    {
        return $this->state(fn (array $attributes) => ['severity' => 'info']);
    }

    public function warning(): static
    {
        return $this->state(fn (array $attributes) => ['severity' => 'warning']);
    }

    public function critical(): static
    {
        return $this->state(fn (array $attributes) => ['severity' => 'critical']);
    }

    public function open(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'open',
            'resolved_at' => null,
            'resolved_by' => null,
        ]);
    }

    public function resolved(): static
    {
        return $this->state(function (array $attributes) {
            $resolverId = User::inRandomOrder()->value('id');

            return [
                'status' => 'resolved',
                'resolved_at' => now()->subHours(fake()->numberBetween(1, 72)),
                'resolved_by' => $resolverId,
            ];
        });
    }
}
