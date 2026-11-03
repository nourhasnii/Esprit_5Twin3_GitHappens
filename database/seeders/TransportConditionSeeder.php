<?php

namespace Database\Seeders;

use App\Models\Batch;
use App\Models\TraceabilityEvent;
use App\Models\TransportCondition;
use Illuminate\Database\Seeder;

class TransportConditionSeeder extends Seeder
{
    public function run(): void
    {
        $batches = Batch::all();

        if ($batches->isEmpty()) {
            $this->command->warn('No batches found. Run BatchSeeder first.');

            return;
        }

        $eventsByBatch = TraceabilityEvent::where('event_type', 'transport')
            ->get()
            ->groupBy('batch_id');

        TransportCondition::query()->delete();

        $conditionCount = 0;

        foreach ($batches->take(3) as $batchIndex => $batch) {
            for ($measurement = 0; $measurement < 6; $measurement++) {
                $temperature = match ($measurement) {
                    1 => 7.0 + $batchIndex,
                    4 => 9.0 + $batchIndex,
                    default => fake()->randomFloat(2, 2.1, 5.2),
                };
                TransportCondition::factory()->for($batch)->state([
                    'temperature' => $temperature,
                    'duration_minutes' => $measurement === 4 ? 45 : fake()->numberBetween(10, 90),
                    'traceability_event_id' => $eventsByBatch->get($batch->id)?->random()?->id,
                ])->create();
                $conditionCount++;
            }
        }

        for ($index = $conditionCount; $index < 22; $index++) {
            $batch = $batches->random();
            $conditionFactory = TransportCondition::factory()->for($batch)->state([
                'traceability_event_id' => $eventsByBatch->get($batch->id)?->random()?->id,
            ]);

            if ($index % 5 === 0 || ($index >= 18 && $index % 2 === 0)) {
                $conditionFactory = $conditionFactory->outOfRange();
            }

            $conditionFactory->create();
        }

        $this->command->info('TransportConditionSeeder: 22 transport conditions created.');
    }
}
