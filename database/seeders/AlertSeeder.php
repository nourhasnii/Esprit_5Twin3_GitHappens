<?php

namespace Database\Seeders;

use App\Models\Alert;
use App\Models\TransportCondition;
use App\Services\ColdChainAlertService;
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
            return $condition->outOfRangeTemperature();
        });

        foreach ($outOfRange as $condition) {
            app(ColdChainAlertService::class)->syncAlert($condition);
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
