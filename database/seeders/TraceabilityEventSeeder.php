<?php

namespace Database\Seeders;

use App\Models\Batch;
use App\Models\TraceabilityEvent;
use Illuminate\Database\Seeder;

class TraceabilityEventSeeder extends Seeder
{
    public function run(): void
    {
        $batches = Batch::with('product')->get();
        if ($batches->isEmpty()) {
            $this->command->warn('Aucun lot trouvé. Exécutez d\'abord BatchSeeder.');

            return;
        }

        $totalCreated = 0;
        $chainsCreated = 0;

        foreach ($batches as $batch) {
            if ($batch->events()->exists()) {
                continue;
            }

            $productionDate = $batch->production_date instanceof \DateTimeInterface
                ? $batch->production_date
                : \Illuminate\Support\Carbon::parse($batch->production_date);

            $expirationDate = $batch->expiration_date instanceof \DateTimeInterface
                ? $batch->expiration_date
                : \Illuminate\Support\Carbon::parse($batch->expiration_date);

            $chain = [
                [
                    'event_type' => 'production',
                    'offset_days' => 0,
                    'location' => "Site de production - {$batch->product->origin_region}, {$batch->product->origin_country}",
                    'latitude' => $this->randomCoord(41, 51),
                    'longitude' => $this->randomCoord(-5, 8),
                    'description' => "Mise en production du lot {$batch->lot_number}. Quantité initiale : {$batch->quantity} {$batch->unit}.",
                    'quantity' => $batch->quantity,
                ],
            ];

            $processingDays = rand(0, 3);
            if ($processingDays > 0 || $this->flip(65)) {
                $chain[] = [
                    'event_type' => 'processing',
                    'offset_days' => $processingDays,
                    'location' => 'Atelier de transformation - Station de conditionnement',
                    'latitude' => $this->randomCoord(43, 50),
                    'longitude' => $this->randomCoord(-3, 7),
                    'description' => 'Conditionnement final, emballage sous vide et étiquetage avec code-barres traçabilité.',
                    'quantity' => round($batch->quantity * (mt_rand(960, 995) / 1000), 2),
                ];
            }

            if ($this->flip(80)) {
                $offset = $this->lastOffset($chain) + rand(1, 5);
                $chain[] = [
                    'event_type' => 'transport',
                    'offset_days' => $offset,
                    'location' => "Transport réfrigéré FR-{$this->plate()} - Trajet national",
                    'latitude' => $this->randomCoord(44, 50),
                    'longitude' => $this->randomCoord(-1, 6),
                    'description' => 'Transport sous chaîne du froid positive, température enregistrée en continu tout au long du trajet.',
                    'quantity' => end($chain)['quantity'] ?? $batch->quantity,
                    'temperature' => round(mt_rand(25, 85) / 10, 1),
                    'distance_km' => round(mt_rand(800, 8500) / 10, 1),
                    'carbon_emission' => round(mt_rand(300, 5500) / 10, 1),
                ];
            }

            if ($this->flip(75)) {
                $offset = $this->lastOffset($chain) + rand(0, 2);
                $chain[] = [
                    'event_type' => 'storage',
                    'offset_days' => $offset,
                    'location' => 'Entrepôt régional - Chambre froide contrôle hygrométrie',
                    'latitude' => $this->randomCoord(43, 50),
                    'longitude' => $this->randomCoord(-2, 6),
                    'description' => 'Stockage avant distribution. Vérification quotidienne de température et humidité relative.',
                    'quantity' => end($chain)['quantity'] ?? $batch->quantity,
                    'temperature' => round(mt_rand(0, 150) / 10, 1),
                ];
            }

            if ($this->flip(60)) {
                $offset = $this->lastOffset($chain) + rand(1, 4);
                if ($offset <= $expirationDate->diffInDays($productionDate)) {
                    $chain[] = [
                        'event_type' => 'distribution',
                        'offset_days' => $offset,
                        'location' => 'Plateforme de distribution - Centre régional',
                        'latitude' => $this->randomCoord(43, 50),
                        'longitude' => $this->randomCoord(-2, 7),
                        'description' => 'Réception et préparation de commandes pour points de vente. Contrôle qualité réception.',
                        'quantity' => round((end($chain)['quantity'] ?? $batch->quantity) * (mt_rand(970, 1000) / 1000), 2),
                    ];
                }
            }

            foreach ($chain as $step) {
                $eventDate = (clone $productionDate)->modify('+' . $step['offset_days'] . ' days');
                if ($eventDate > $expirationDate) {
                    continue;
                }

                $event = TraceabilityEvent::create([
                    'batch_id' => $batch->id,
                    'event_type' => $step['event_type'],
                    'event_date' => $eventDate->format('Y-m-d ') . str_pad((string) mt_rand(6, 20), 2, '0', STR_PAD_LEFT) . ':' . str_pad((string) mt_rand(0, 59), 2, '0', STR_PAD_LEFT) . ':00',
                    'location' => $step['location'],
                    'latitude' => $step['latitude'] ?? null,
                    'longitude' => $step['longitude'] ?? null,
                    'actor_id' => $this->flip(70) ? \App\Models\User::inRandomOrder()->value('id') : null,
                    'description' => $step['description'] ?? null,
                    'quantity' => $step['quantity'] ?? null,
                    'temperature' => $step['temperature'] ?? null,
                    'distance_km' => $step['distance_km'] ?? null,
                    'carbon_emission' => $step['carbon_emission'] ?? null,
                    'metadata' => $this->flip(50) ? [
                        'vehicle_registration' => $step['event_type'] === 'transport' ? strtoupper($this->plate()) : null,
                        'operator' => $this->flip(40) ? \App\Models\User::inRandomOrder()->value('name') : null,
                        'temperature_records' => isset($step['temperature']) ? [$step['temperature'], round($step['temperature'] + (mt_rand(-5, 5) / 10), 1)] : null,
                        'hygiene_check' => $this->flip(85),
                    ] : null,
                ]);
                $totalCreated++;
            }
            $chainsCreated++;
        }

        $extraScatter = TraceabilityEvent::factory()
            ->count(max(0, 8 - $totalCreated))
            ->create();
        $totalCreated += $extraScatter->count();

        $this->command->info("TraceabilityEventSeeder : {$chainsCreated} chaînes traçabilité générées, {$totalCreated} événements créés au total.");
    }

    private function randomCoord(float $min, float $max): float
    {
        return round($min + lcg_value() * abs($max - $min), 7);
    }

    private function flip(int $percent): bool
    {
        return mt_rand(1, 100) <= $percent;
    }

    /** @param array<int, array{offset_days?: int}> $chain */
    private function lastOffset(array $chain): int
    {
        $last = end($chain);

        return (int) ($last['offset_days'] ?? 0);
    }

    private function plate(): string
    {
        $letters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K', 'L', 'M'];

        return $letters[array_rand($letters)] . $letters[array_rand($letters)]
            . '-' . str_pad((string) mt_rand(100, 999), 3, '0', STR_PAD_LEFT)
            . '-' . $letters[array_rand($letters)] . $letters[array_rand($letters)];
    }
}
