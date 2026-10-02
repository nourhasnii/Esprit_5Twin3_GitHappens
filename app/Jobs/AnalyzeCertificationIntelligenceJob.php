<?php

namespace App\Jobs;

use App\Models\CertificationAIAnalysis;
use App\Services\CertificationAIService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class AnalyzeCertificationIntelligenceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300;

    public $tries = 1;

    public $failOnTimeout = true;

    /** @param array<string, mixed> $deterministicData */
    public function __construct(
        public CertificationAIAnalysis $analysis,
        public array $deterministicData,
    ) {}

    public function handle(CertificationAIService $aiService): void
    {
        if ($this->analysis->fresh()?->status !== 'pending') {
            return;
        }

        try {
            $result = $aiService->analyze($this->deterministicData, true, $this->analysis);

            if (! $result['success']) {
                $this->markFailed($result['error_message'] ?? 'AI analysis failed without an error message.');
            }
        } catch (Throwable $exception) {
            Log::error('AnalyzeCertificationIntelligenceJob failed', [
                'analysis_id' => $this->analysis->id,
                'error' => $exception->getMessage(),
            ]);

            $this->markFailed($exception->getMessage());
        }
    }

    public function failed(?Throwable $exception): void
    {
        if ($exception) {
            Log::error('AnalyzeCertificationIntelligenceJob exhausted', [
                'analysis_id' => $this->analysis->id,
                'error' => $exception->getMessage(),
            ]);
        }

        $this->markFailed($exception?->getMessage() ?? 'The queued AI analysis failed.');
    }

    private function markFailed(string $errorMessage): void
    {
        CertificationAIAnalysis::query()
            ->whereKey($this->analysis->getKey())
            ->where('status', 'pending')
            ->update([
                'status' => 'failed',
                'error_message' => mb_substr($errorMessage, 0, 10000),
            ]);
    }
}