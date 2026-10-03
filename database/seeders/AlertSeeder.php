<?php

namespace Database\Seeders;

use App\Models\Alert;
use App\Models\TransportCondition;
use Illuminate\Database\Seeder;

class AlertSeeder extends Seeder
{
    public function run(): void
    {
        $conditions = TransportCondition::with('batch')->get();

        if ($conditions->isEmpty()) {
            $this->command->warn('No transport conditions found. Run TransportConditionSeeder first.');

            return;
        }

        Alert::query()->delete();

        $outOfRange = $conditions->filter(function (TransportCondition $condition): bool {
            return (float) $condition->temperature < TransportCondition::TEMPERATURE_MIN
                || (float) $condition->temperature > TransportCondition::TEMPERATURE_MAX;
        });

        foreach ($outOfRange as $condition) {
            $temperature = (float) $condition->temperature;
            Alert::factory()->create([
                'batch_id' => $condition->batch_id,
                'transport_condition_id' => $condition->id,
                'type' => 'temperature',
                'severity' => abs($temperature - 3.5) >= 5 ? 'critical' : 'warning',
                'title' => 'Cold-chain temperature excursion',
                'message' => sprintf('Recorded temperature %.2f°C is outside the 2°C to 5°C range.', $temperature),
                'status' => 'open',
                'risk_score' => min(100, 50 + abs($temperature - 3.5) * 10),
            ]);
        }

        Alert::factory()->info()->create([
            'batch_id' => $conditions->random()->batch_id,
            'transport_condition_id' => null,
            'type' => 'other',
            'title' => 'Transport inspection note',
            'message' => 'Manual inspection note recorded for a cold-chain shipment.',
        ]);

        Alert::factory()->warning()->resolved()->create([
            'batch_id' => $conditions->random()->batch_id,
            'transport_condition_id' => null,
            'type' => 'duration',
            'title' => 'Extended transport duration',
            'message' => 'The shipment exceeded its expected transport duration.',
        ]);

        Alert::factory()->critical()->create([
            'batch_id' => $conditions->random()->batch_id,
            'transport_condition_id' => null,
            'type' => 'humidity',
            'title' => 'Humidity monitoring required',
            'message' => 'Manual review is required for humidity exposure during transport.',
        ]);

        $this->command->info('AlertSeeder: alerts generated for excursions and manual cases.');
    }
}
