<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class VisionAnalysisService
{
    public function analyze(string $imagePath): array
    {
        $fullPath = Storage::disk('public')->path($imagePath);
        $imageData = base64_encode(file_get_contents($fullPath));

        $prompt = <<<PROMPT
You are a food quality expert. Analyze this product image and return ONLY a valid JSON with these exact keys:
- product_type (string)
- condition (good / average / poor)
- defects (array of strings)
- freshness_estimate (number between 0 and 100)
- confidence (number between 0 and 1)

Do not add any text outside the JSON.
PROMPT;

        $response = Http::timeout(120)->post('http://localhost:11434/api/generate', [
            'model' => 'qwen2.5vl',           
            'prompt' => $prompt,
            'images' => [$imageData],
            'stream' => false,
            'format' => 'json',
        ]);

        if (!$response->successful()) {
            throw new \Exception('Ollama Vision request failed: ' . $response->body());
        }

        $content = $response->json('response');

        // Clean possible markdown
        $content = preg_replace('/^```json\s*|\s*```$/', '', trim($content));

        $result = json_decode($content, true);

        if (!is_array($result)) {
            // Fallback if model doesn't respect format perfectly
            $result = [
                'product_type' => 'unknown',
                'condition' => 'average',
                'defects' => [],
                'freshness_estimate' => 50,
                'confidence' => 0.5,
                'raw' => $content,
            ];
        }

        return $result;
    }
}