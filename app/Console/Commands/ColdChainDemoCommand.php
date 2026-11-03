<?php

namespace App\Console\Commands;

use App\Services\ColdChainAlertService;
use Illuminate\Console\Command;

class ColdChainDemoCommand extends Command
{
    protected $signature = 'coldchain:demo {temperature : Measured temperature in °C} {duration=0 : Duration in minutes}';

    protected $description = 'Simulate a cold-chain measurement without saving anything';

    public function handle(ColdChainAlertService $alertService): int
    {
        $temperature = (float) $this->argument('temperature');
        $duration = (int) $this->argument('duration');
        $simulation = $alertService->simulate($temperature, $duration);
        $temperatureConfig = config('coldchain.temperature');

        $this->line('Cold-chain simulation (no data saved)');
        $this->line(sprintf('Temperature received: %.2f °C', $temperature));
        $this->line(sprintf('Duration received: %d minute(s)', $duration));
        $this->line(sprintf(
            'Thresholds: safe 0 to below %s °C; critical below %s °C or above %s °C; duration critical over %d minutes.',
            $temperatureConfig['safe_max'],
            $temperatureConfig['critical_min'],
            $temperatureConfig['critical_max'],
            $temperatureConfig['duration_critical_minutes']
        ));
        $this->line('Severity: ' . $simulation['severity']);
        $this->line(sprintf('Risk score: %.2f / 100', $simulation['risk_score']));
        $this->line(sprintf(
            'Breakdown: temperature points %.2f + duration points %.2f',
            $simulation['temperature_points'],
            $simulation['duration_points']
        ));
        $this->line('Explanation: ' . $simulation['explanation']);

        return self::SUCCESS;
    }
}
