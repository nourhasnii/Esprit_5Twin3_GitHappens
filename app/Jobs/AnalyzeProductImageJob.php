<?php

namespace App\Jobs;

use App\Models\QualityCheck;
use App\Services\VisionAnalysisService;
use App\Services\QualityScoringService;
use App\Services\DecisionEngineService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AnalyzeProductImageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 180; // 3 minutes (Ollama peut être lent)

    public function __construct(
        public QualityCheck $qualityCheck
    ) {}

    public function handle(
        VisionAnalysisService $visionService,
        QualityScoringService $scoringService,
        DecisionEngineService $decisionService
    ): void {
        try {
            // 1. Mettre le statut en pending
            $this->qualityCheck->update(['status' => 'pending']);

            // 2. Analyse Vision (Ollama)
            $visionResult = $visionService->analyze($this->qualityCheck->image_path);

            // 3. Quality Scoring
            $product = $this->qualityCheck->product;
            $scores = $scoringService->calculate($visionResult, $product);

            // 4. Decision Engine
            $decision = $decisionService->decide($scores, $visionResult, $product);

            // 5. Sauvegarder le résultat final
            $this->qualityCheck->update([
                'vision_result'    => $visionResult,
                'freshness_score'  => $scores['freshness_score'],
                'quality_score'    => $scores['quality_score'],
                'decision'         => $decision['decision'],
                'confidence'       => $decision['confidence'],
                'explanation'      => $decision['explanation'],
                'factors'          => $decision['factors'],
                'status'           => 'completed',
            ]);

        } catch (\Throwable $e) {
            Log::error('AnalyzeProductImageJob failed', [
                'quality_check_id' => $this->qualityCheck->id,
                'error' => $e->getMessage(),
            ]);

            $this->qualityCheck->update([
                'status' => 'failed',
                'explanation' => 'Analyse échouée : ' . $e->getMessage(),
            ]);
        }
    }
}