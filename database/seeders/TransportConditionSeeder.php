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

        for ($index = 0; $index < 22; $index++) {
            $batch = $batches->random();
            $condition = TransportCondition::factory()
                ->for($batch)
                ->state([
                    'traceability_event_id' => $eventsByBatch->get($batch->id)?->random()?->id,
                ])
                ->when($index % 5 === 0, fn ($factory) => $factory->outOfRange())
                ->create();
        }

        $this->command->info('TransportConditionSeeder: 22 transport conditions created.');
    }
}
