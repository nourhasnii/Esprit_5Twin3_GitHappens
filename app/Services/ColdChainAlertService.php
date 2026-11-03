<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\Batch;
use App\Models\TransportCondition;
use App\Models\User;
use App\Notifications\CriticalAlertNotification;

class ColdChainAlertService
{
    public function simulate(float $temperature, int $duration): array
    {
        $classification = $this->classify($temperature, $duration);

        if ($classification === null) {
            return [
                'severity' => 'info',
                'risk_score' => 0,
                'temperature_points' => 0,
                'duration_points' => 0,
                'explanation' => sprintf(
                    'Temperature %.2f C remains in the safe range below 6 C. No alert would be created.',
                    $temperature
                ),
            ];
        }

        return [
            'severity' => $classification['severity'],
            'risk_score' => $classification['risk_score'],
            'temperature_points' => $classification['temperature_points'],
            'duration_points' => $classification['duration_points'],
            'explanation' => $classification['message'],
        ];
    }

    public function analyzeBatch(Batch $batch): array
    {
        $conditions = TransportCondition::query()
            ->where('batch_id', $batch->id)
            ->orderBy('recorded_at')
            ->get();

        $alerts = Alert::query()
            ->where('batch_id', $batch->id)
            ->latest()
            ->get();

        $temperatures = $conditions->map(
            fn (TransportCondition $condition): float => (float) $condition->temperature
        );

        $outOfRange = $conditions->filter(
            fn (TransportCondition $condition): bool =>
                $this->isAnalysisOutOfRange((float) $condition->temperature)
        );

        $maxDeviation = $conditions->max(
            function (TransportCondition $condition): float {
                $temperature = (float) $condition->temperature;

                return max(0, -$temperature, $temperature - 8);
            }
        ) ?? 0;

        $riskScore = min(100, (float) ($alerts->max('risk_score') ?? 0));

        $level = $riskScore < 30
            ? 'low'
            : ($riskScore <= 60 ? 'medium' : 'high');

        $severityCounts = $alerts->groupBy('severity')->map->count()->all();

        $averageTemperature = $temperatures->isNotEmpty()
            ? number_format($temperatures->avg(), 2)
            : 'N/A';

        $minimumTemperature = $temperatures->isNotEmpty()
            ? number_format($temperatures->min(), 2)
            : 'N/A';

        $maximumTemperature = $temperatures->isNotEmpty()
            ? number_format($temperatures->max(), 2)
            : 'N/A';

        $reasons = [
            sprintf(
                'Average temperature: %s C across %d measurement(s).',
                $averageTemperature,
                $conditions->count()
            ),
            sprintf(
                'Observed range: %s C to %s C.',
                $minimumTemperature,
                $maximumTemperature
            ),
            sprintf(
                '%d measurement(s) exceeded the 0 C to 8 C range; maximum deviation: %s C.',
                $outOfRange->count(),
                number_format($maxDeviation, 2)
            ),
            sprintf(
                'Total recorded time out of range: %d minute(s).',
                $outOfRange->sum(
                    fn (TransportCondition $condition): int =>
                        (int) ($condition->duration_minutes ?? 0)
                )
            ),
            sprintf(
                'Alert severity totals: %d critical, %d warning, %d info.',
                $severityCounts['critical'] ?? 0,
                $severityCounts['warning'] ?? 0,
                $severityCounts['info'] ?? 0
            ),
            sprintf(
                'Batch risk score: %s/100 (%s).',
                number_format($riskScore, 2),
                $level
            ),
        ];

        return [
            'average_temperature' => $temperatures->isNotEmpty()
                ? (float) $temperatures->avg()
                : null,

            'min_temperature' => $temperatures->isNotEmpty()
                ? (float) $temperatures->min()
                : null,

            'max_temperature' => $temperatures->isNotEmpty()
                ? (float) $temperatures->max()
                : null,

            'max_deviation' => (float) $maxDeviation,

            'total_minutes_out_of_range' => (int) $outOfRange->sum(
                fn (TransportCondition $condition): int =>
                    (int) ($condition->duration_minutes ?? 0)
            ),

            'out_of_range_measurements' => $outOfRange->count(),

            'alerts_by_severity' => [
                'critical' => $severityCounts['critical'] ?? 0,
                'warning' => $severityCounts['warning'] ?? 0,
                'info' => $severityCounts['info'] ?? 0,
            ],

            'risk_score' => $riskScore,
            'risk_level' => $level,
            'reasons' => $reasons,
            'conditions' => $conditions,
            'alerts' => $alerts,
        ];
    }

    public function explainAlert(Alert $alert): array
    {
        $condition = $alert->transportCondition;

        if (! $condition) {
            return [
                'message' => $alert->message,
                'temperature_points' => null,
                'duration_points' => null,
                'risk_score' => $alert->risk_score,
            ];
        }

        $classification = $this->classify(
            (float) $condition->temperature,
            (int) ($condition->duration_minutes ?? 0)
        );

        return [
            'message' => $alert->message,
            'temperature_points' => $classification['temperature_points'] ?? null,
            'duration_points' => $classification['duration_points'] ?? null,
            'risk_score' => $classification['risk_score'] ?? $alert->risk_score,
        ];
    }

    public function syncAlert(TransportCondition $condition): ?Alert
    {
        $temperature = (float) $condition->temperature;
        $duration = (int) ($condition->duration_minutes ?? 0);

        $classification = $this->classify($temperature, $duration);

        $alert = Alert::query()
            ->where('transport_condition_id', $condition->id)
            ->where('type', 'temperature')
            ->whereNull('created_by')
            ->latest('id')
            ->first();

        if ($classification === null) {
            if ($alert && $alert->status !== 'resolved') {
                $alert->update([
                    'status' => 'resolved',
                    'resolved_at' => now(),
                    'resolved_by' => null,
                ]);
            }

            return $alert;
        }

        $wasCritical = $alert?->severity === 'critical';

        $wasClosed = in_array(
            $alert?->status,
            ['resolved', 'ignored'],
            true
        );

        $attributes = [
            'batch_id' => $condition->batch_id,
            'transport_condition_id' => $condition->id,
            'type' => 'temperature',
            'severity' => $classification['severity'],
            'title' => $classification['title'],
            'message' => $classification['message'],
            'status' => 'open',
            'risk_score' => $classification['risk_score'],
            'resolved_at' => null,
            'resolved_by' => null,
            'created_by' => null,
        ];

        if ($alert) {
            $alert->update($attributes);
        } else {
            $alert = Alert::create($attributes);
        }

        if (
            $alert->severity === 'critical'
            && (! $wasCritical || $wasClosed || $alert->wasRecentlyCreated)
        ) {
            User::role('admin')
                ->get()
                ->each
                ->notify(new CriticalAlertNotification($alert));
        }

        return $alert;
    }

    public function classify(float $temperature, int $duration): ?array
    {
        $temperatureConfig = config('coldchain.temperature');
        $riskConfig = config('coldchain.risk');

        $outOfSafeRange = $temperature < $temperatureConfig['safe_min']
            || $temperature >= $temperatureConfig['safe_max'];

        if (! $outOfSafeRange) {
            return null;
        }

        $critical = $temperature < $temperatureConfig['critical_min']
            || $temperature > $temperatureConfig['critical_max']
            || $duration > $temperatureConfig['duration_critical_minutes'];

        if ($critical) {
            $deviation = $temperature < $temperatureConfig['critical_min']
                ? $temperatureConfig['critical_min'] - $temperature
                : max(0, $temperature - $temperatureConfig['critical_max']);

            $temperatureScore = min(
                $riskConfig['critical_temperature_cap'],
                $riskConfig['critical_base']
                    + ($deviation * $riskConfig['critical_deviation_multiplier'])
            );

            $severity = 'critical';
        } else {
            $temperatureScore = $riskConfig['warning_base']
                + (
                    ($temperature - $temperatureConfig['safe_max'])
                    * $riskConfig['warning_temperature_multiplier']
                );

            $severity = 'warning';
        }

        $durationScore = min(
            $riskConfig['duration_cap'],
            $duration / $riskConfig['duration_divisor']
        );

        $totalScore = min(100, $temperatureScore + $durationScore);

        return [
            'severity' => $severity,
            'risk_score' => $totalScore,
            'temperature_points' => $temperatureScore,
            'duration_points' => $durationScore,

            'title' => $severity === 'critical'
                ? 'Critical cold-chain temperature excursion'
                : 'Cold-chain temperature warning',

            'message' => sprintf(
                'Measured temperature %.2f C crossed the safe threshold; deviation is %.2f C, with %d minute(s) out of range. Score: %.2f temperature points + %.2f duration points = %.2f/100.',
                $temperature,
                max(
                    0,
                    $temperature < 0
                        ? -$temperature
                        : ($temperature > 8 ? $temperature - 8 : $temperature - 6)
                ),
                $duration,
                $temperatureScore,
                $durationScore,
                $totalScore
            ),
        ];
    }

    private function isAnalysisOutOfRange(float $temperature): bool
    {
        return $temperature < 0 || $temperature > 8;
    }
}