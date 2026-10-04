<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\TransportCondition;
use App\Models\User;
use App\Notifications\CriticalAlertNotification;

class ColdChainAlertService
{
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
        $wasClosed = in_array($alert?->status, ['resolved', 'ignored'], true);
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

        if ($alert->severity === 'critical' && (! $wasCritical || $wasClosed || $alert->wasRecentlyCreated)) {
            User::role('admin')->get()->each->notify(new CriticalAlertNotification($alert));
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
                $riskConfig['critical_base'] + ($deviation * $riskConfig['critical_deviation_multiplier'])
            );
            $severity = 'critical';
        } else {
            $temperatureScore = $riskConfig['warning_base']
                + (($temperature - $temperatureConfig['safe_max']) * $riskConfig['warning_temperature_multiplier']);
            $severity = 'warning';
        }

        $durationScore = min(
            $riskConfig['duration_cap'],
            $duration / $riskConfig['duration_divisor']
        );

        return [
            'severity' => $severity,
            'risk_score' => min(100, $temperatureScore + $durationScore),
            'title' => $severity === 'critical'
                ? 'Critical cold-chain temperature excursion'
                : 'Cold-chain temperature warning',
            'message' => sprintf(
                'Recorded temperature %.2f°C is outside the safe 0°C to below 6°C range%s.',
                $temperature,
                $duration > 0 ? sprintf(' during a %d-minute period', $duration) : ''
            ),
        ];
    }
}
