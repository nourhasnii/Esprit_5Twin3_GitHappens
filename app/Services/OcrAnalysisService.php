<?php

namespace App\Services;

use App\Models\Batch;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Throwable;

class OcrAnalysisService
{
    private const COMPARISON_WEIGHTS = [
        'lot_number' => 40,
        'expiration_date' => 35,
        'production_date' => 15,
        'quantity' => 10,
    ];

    public function analyze(string $imagePath): array
    {
        $image = Storage::disk('public')->get($imagePath);
        $prompt = <<<'PROMPT'
Read the product or batch label in the image. Return STRICT JSON only, without markdown or any text before or after it, with exactly these keys:
{
  "product_name": string or null,
  "lot_number": string or null,
  "production_date": string or null,
  "expiration_date": string or null,
  "quantity": number or null,
  "unit": string or null,
  "allergens": array of strings,
  "certifications": array of strings,
  "other_text": string,
  "confidence": number between 0 and 1
}
Use null when a value is not legible or not present. Do not infer missing values.
PROMPT;

        $response = Http::timeout(max(180, (int) config('services.ollama.timeout', 300)))
            ->post(config('services.ollama.endpoint', 'http://localhost:11434/api/generate'), [
                'model' => config('services.ollama.model', 'qwen2.5vl'),
                'prompt' => $prompt,
                'images' => [base64_encode($image)],
                'stream' => false,
                'format' => 'json',
            ]);

        if (! $response->successful()) {
            throw new \RuntimeException('Ollama OCR request failed: '.$response->body());
        }

        $content = trim((string) $response->json('response', ''));
        $content = preg_replace('/^```(?:json)?\s*|\s*```$/i', '', $content) ?? $content;

        try {
            $result = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            $result = null;
        }

        if (! is_array($result)) {
            return [
                'product_name' => null,
                'lot_number' => null,
                'production_date' => null,
                'expiration_date' => null,
                'quantity' => null,
                'unit' => null,
                'allergens' => [],
                'certifications' => [],
                'other_text' => $content,
                'confidence' => 0,
            ];
        }

        return [
            'product_name' => $result['product_name'] ?? null,
            'lot_number' => $result['lot_number'] ?? null,
            'production_date' => $result['production_date'] ?? null,
            'expiration_date' => $result['expiration_date'] ?? null,
            'quantity' => $result['quantity'] ?? null,
            'unit' => $result['unit'] ?? null,
            'allergens' => is_array($result['allergens'] ?? null) ? $result['allergens'] : [],
            'certifications' => is_array($result['certifications'] ?? null) ? $result['certifications'] : [],
            'other_text' => is_string($result['other_text'] ?? null) ? $result['other_text'] : '',
            'confidence' => is_numeric($result['confidence'] ?? null)
                ? min(1, max(0, (float) $result['confidence']))
                : 0,
        ];
    }

    public function compareWithBatch(array $ocrResult, Batch $batch): array
    {
        $mismatches = [];
        $score = 0;
        $declared = $batch->getAttributes();

        foreach (self::COMPARISON_WEIGHTS as $field => $weight) {
            $ocrValue = $ocrResult[$field] ?? null;
            $declaredValue = $declared[$field] ?? null;

            if ($ocrValue === null || $ocrValue === '' || $declaredValue === null || $declaredValue === '') {
                continue;
            }

            if ($this->normalize($field, $ocrValue) === $this->normalize($field, $declaredValue)) {
                continue;
            }

            $score += $weight;
            $mismatches[] = [
                'field' => $field,
                'ocr_value' => $ocrValue,
                'declared_value' => $this->displayValue($field, $declaredValue),
                'severity' => in_array($field, ['lot_number', 'expiration_date'], true) ? 'critical' : ($field === 'production_date' ? 'moderate' : 'low'),
            ];
        }

        $explanation = $mismatches === []
            ? 'Les champs lisibles de l’étiquette correspondent aux données déclarées pour ce lot.'
            : count($mismatches).' incohérence(s) détectée(s), pour un score de '.$score.'/100. '.
                'Les écarts critiques concernent le numéro de lot ou la date d’expiration.';

        return [
            'mismatches' => $mismatches,
            'inconsistency_score' => (float) min(100, $score),
            'explanation' => $explanation,
        ];
    }

    private function normalize(string $field, mixed $value): string
    {
        if (in_array($field, ['production_date', 'expiration_date'], true)) {
            try {
                if ($value instanceof \DateTimeInterface) {
                    return CarbonImmutable::instance($value)->toDateString();
                }

                $value = trim((string) $value);
                if (preg_match('/^\d{4}-\d{2}-\d{2}(?:\s|$)/', $value)) {
                    return CarbonImmutable::createFromFormat('!Y-m-d', substr($value, 0, 10))->toDateString();
                }

                foreach (['Y-m-d', 'Y-m-d H:i:s', 'd/m/Y', 'd-m-Y', 'm/d/Y', 'Y/m/d'] as $format) {
                    if (! CarbonImmutable::hasFormat($value, $format)) {
                        continue;
                    }

                    $date = CarbonImmutable::createFromFormat('!'.$format, $value);
                    if ($date !== false) {
                        return $date->toDateString();
                    }
                }
            } catch (Throwable) {
                // Fall back to a normalized text comparison for unreadable dates.
            }
        }

        if ($field === 'quantity' && is_numeric($value)) {
            return (string) (float) $value;
        }

        return mb_strtolower(trim((string) $value));
    }

    private function displayValue(string $field, mixed $value): mixed
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        if (in_array($field, ['production_date', 'expiration_date'], true) && preg_match('/^\d{4}-\d{2}-\d{2}/', (string) $value)) {
            return substr((string) $value, 0, 10);
        }

        return $value;
    }
}
