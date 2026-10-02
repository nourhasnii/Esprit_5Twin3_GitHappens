<?php

namespace App\Jobs;

use App\Models\OcrCheck;
use App\Services\OcrAnalysisService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class AnalyzeLabelOcrJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;

    public function __construct(public OcrCheck $ocrCheck) {}

    public function handle(OcrAnalysisService $ocrService): void
    {
        $check = OcrCheck::findOrFail($this->ocrCheck->id);

        try {
            $check->update(['status' => 'pending', 'error_message' => null]);
            $check->load('batch.product.certifications');

            if (! $check->batch) {
                throw new \RuntimeException('The selected batch is no longer available.');
            }

            $batch = $check->batch;
            $product = $batch->product;
            $ocrResult = $ocrService->analyze($check->image_path);
            $comparison = $ocrService->compareWithBatch($ocrResult, $batch);

            $check->update([
                'product_id' => $product?->id,
                'ocr_result' => $ocrResult,
                'declared_snapshot' => [
                    'batch' => [
                        'lot_number' => $batch->lot_number,
                        'production_date' => $batch->production_date?->toDateString(),
                        'expiration_date' => $batch->expiration_date?->toDateString(),
                        'quantity' => $batch->quantity,
                        'unit' => $batch->unit,
                        'status' => $batch->status,
                    ],
                    'product' => $product ? [
                        'name' => $product->name,
                        'category' => $product->category,
                        'unit' => $product->unit,
                        'origin_country' => $product->origin_country,
                        'origin_region' => $product->origin_region,
                        'certifications' => $product->certifications->pluck('name')->all(),
                    ] : null,
                ],
                'mismatches' => $comparison['mismatches'],
                'inconsistency_score' => $comparison['inconsistency_score'],
                'confidence' => $ocrResult['confidence'] ?? null,
                'explanation' => $comparison['explanation'],
                'status' => 'completed',
                'error_message' => null,
            ]);
        } catch (Throwable $exception) {
            Log::error('AnalyzeLabelOcrJob failed', [
                'ocr_check_id' => $check->id,
                'error' => $exception->getMessage(),
                'exception' => $exception,
            ]);

            $check->update([
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
                'explanation' => 'L’analyse OCR a échoué. Vérifiez la connexion à Ollama puis relancez l’analyse.',
            ]);
        }
    }
}
