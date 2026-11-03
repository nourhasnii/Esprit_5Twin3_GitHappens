<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class LlmAnalysisService
{
    public function analyze(array $context): array
    {
        $unavailable = [
            'available' => false,
            'summary' => null,
            'probable_cause' => null,
            'recommended_actions' => [],
        ];

        if (! config('ai.llm.enabled') || ! config('ai.llm.api_key')) {
            Log::info('LLM cold-chain analysis unavailable: API key is missing or the service is disabled.', [
                'provider' => config('ai.llm.provider'),
            ]);

            return $unavailable;
        }

        $cacheKey = 'cold-chain-llm:'.hash('sha256', json_encode([
            'provider' => config('ai.llm.provider'),
            'model' => config('ai.llm.model'),
            'context' => $context,
        ], JSON_THROW_ON_ERROR));

        try {
            return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($context, $unavailable): array {
                $responseText = $this->requestAnalysis($context);
                $analysis = $this->parseResponse($responseText);

                if ($analysis === null) {
                    throw new \RuntimeException('LLM returned invalid analysis JSON.');
                }

                return [
                    'available' => true,
                    ...$analysis,
                ];
            });
        } catch (Throwable $exception) {
            Log::warning('LLM cold-chain analysis unavailable.', [
                'provider' => config('ai.llm.provider'),
                'exception' => $exception::class,
            ]);

            return $unavailable;
        }
    }

    private function requestAnalysis(array $context): string
    {
        $prompt = 'Réponds uniquement en JSON valide, en français, avec les clés summary (exactement 2 phrases), probable_cause et recommended_action (maximum 3 éléments courts). Base ta réponse UNIQUEMENT sur les données fournies, sans supposer de faits externes. Données: '.json_encode($context, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        $provider = config('ai.llm.provider');

        if ($provider === 'anthropic') {
            $response = Http::timeout((int) config('ai.llm.timeout', 15))
                ->withHeaders([
                    'x-api-key' => config('ai.llm.api_key'),
                    'anthropic-version' => '2023-06-01',
                ])
                ->post('https://api.anthropic.com/v1/messages', [
                    'model' => config('ai.llm.model'),
                    'max_tokens' => 500,
                    'messages' => [['role' => 'user', 'content' => $prompt]],
                ]);
            $response->throw();

            return (string) $response->json('content.0.text', '');
        }

        if ($provider !== 'gemini') {
            throw new \RuntimeException('Unsupported LLM provider.');
        }

        $response = Http::timeout((int) config('ai.llm.timeout', 15))
            ->withHeaders([
                'x-goog-api-key' => config('ai.llm.api_key'),
            ])
            ->post('https://generativelanguage.googleapis.com/v1beta/models/'.config('ai.llm.model').':generateContent', [
                'contents' => [['parts' => [['text' => $prompt]]]],
                'generationConfig' => [
                    'responseMimeType' => 'application/json',
                ],
            ]);
        $response->throw();

        return (string) $response->json('candidates.0.content.parts.0.text', '');
    }

    private function parseResponse(string $response): ?array
    {
        $cleaned = trim($response);
        $cleaned = preg_replace('/^```(?:json)?\s*|\s*```$/i', '', $cleaned) ?: $cleaned;
        $decoded = json_decode($cleaned, true);

        if (! is_array($decoded)
            || ! is_string($decoded['summary'] ?? null)
            || ! is_string($decoded['probable_cause'] ?? null)
            || ! is_array($decoded['recommended_action'] ?? null)
        ) {
            return null;
        }

        return [
            'summary' => $decoded['summary'],
            'probable_cause' => $decoded['probable_cause'],
            'recommended_actions' => array_values(array_filter(
                array_slice($decoded['recommended_action'], 0, 3),
                'is_string'
            )),
        ];
    }
}
