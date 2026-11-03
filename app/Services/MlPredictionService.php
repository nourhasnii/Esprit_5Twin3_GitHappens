<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use Throwable;

class MlPredictionService
{
    public function predict(float $temperature, int $durationMinutes): array
    {
        $unavailable = [
            'available' => false,
            'probability' => null,
            'model' => null,
        ];

        if (! config('ai.ml.enabled')) {
            return $unavailable;
        }

        try {
            $process = new Process([
                (string) config('ai.ml.python_bin', 'python'),
                base_path('ml/predict.py'),
                (string) $temperature,
                (string) $durationMinutes,
            ]);
            $process->setTimeout((int) config('ai.ml.timeout', 10));
            $process->run();

            if (! $process->isSuccessful()) {
                throw new \RuntimeException(trim($process->getErrorOutput()) ?: 'ML prediction process failed.');
            }

            $result = json_decode(trim($process->getOutput()), true, 512, JSON_THROW_ON_ERROR);
            if (! is_array($result) || ! isset($result['probability'], $result['model'])) {
                throw new \RuntimeException('ML prediction returned an invalid response.');
            }

            return [
                'available' => true,
                'probability' => (float) $result['probability'],
                'model' => (string) $result['model'],
            ];
        } catch (Throwable $exception) {
            Log::warning('ML cold-chain prediction unavailable.', [
                'temperature' => $temperature,
                'duration_minutes' => $durationMinutes,
                'error' => $exception->getMessage(),
            ]);

            return $unavailable;
        }
    }
}
