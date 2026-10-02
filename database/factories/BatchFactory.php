<?php

namespace Database\Factories;

use App\Models\Batch;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Batch>
 */
class BatchFactory extends Factory
{
    protected $model = Batch::class;

    private static int $lotCounter = 1;

    public function definition(): array
    {
        $product = Product::inRandomOrder()->first() ?? Product::factory()->create();

        $shelfLifeWeeks = $this->faker->randomElement([2, 3, 4, 8, 12, 26, 52, 104]);
        $productionDate = $this->faker->dateTimeBetween('-10 months', '+1 month');
        $expirationDate = (clone $productionDate)->modify('+' . $shelfLifeWeeks . ' weeks');

        $status = 'active';
        if ($expirationDate < new \DateTime()) {
            $status = $this->faker->boolean(80) ? 'expired' : 'recalled';
        } elseif ($this->faker->boolean(5)) {
            $status = 'recalled';
        }

        $unit = $product->unit ?? $this->faker->randomElement(['kg', 'g', 'L', 'pièce', 'paquet']);

        return [
            'product_id' => $product->id,
            'lot_number' => 'LOT-' . date('Y') . '-' . str_pad(self::$lotCounter++, 6, '0', STR_PAD_LEFT)
                . '-' . strtoupper($this->faker->bothify('??')),
            'production_date' => $productionDate->format('Y-m-d'),
            'expiration_date' => $expirationDate->format('Y-m-d'),
            'quantity' => $this->faker->randomFloat(2, 10, 10000),
            'unit' => $unit,
            'carbon_footprint' => $this->faker->optional(0.6)->randomFloat(2, 1.0, 500.0),
            'status' => $status,
        ];
    }

    public function active(): static
    {
        return $this->state(function (array $attributes) {
            $productionDate = $this->faker->dateTimeBetween('-3 months', '-1 week');
            $expirationDate = (clone $productionDate)->modify('+3 months');

            return [
                'production_date' => $productionDate->format('Y-m-d'),
                'expiration_date' => $expirationDate->format('Y-m-d'),
                'status' => 'active',
            ];
        });
    }

    public function nearExpiration(): static
    {
        return $this->state(function (array $attributes) {
            $productionDate = $this->faker->dateTimeBetween('-6 months', '-5 months');
            $expirationDate = $this->faker->dateTimeBetween('+3 days', '+14 days');

            return [
                'production_date' => $productionDate->format('Y-m-d'),
                'expiration_date' => $expirationDate->format('Y-m-d'),
                'status' => 'active',
            ];
        });
    }

    public function expired(): static
    {
        return $this->state(function (array $attributes) {
            $productionDate = $this->faker->dateTimeBetween('-14 months', '-9 months');
            $expirationDate = $this->faker->dateTimeBetween('-5 months', '-1 month');

            return [
                'production_date' => $productionDate->format('Y-m-d'),
                'expiration_date' => $expirationDate->format('Y-m-d'),
                'status' => 'expired',
            ];
        });
    }

    public function recalled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'recalled',
        ]);
    }
}
