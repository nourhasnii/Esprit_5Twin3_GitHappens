<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class OllamaService
{
    public const DEFAULT_MODEL = 'qwen2.5vl';
    public const DEFAULT_ENDPOINT = 'http://localhost:11434/api/generate';
    public const DEFAULT_TIMEOUT = 300;

    private string $endpoint;
    private string $defaultModel;
    private int $timeout;

    public function __construct(?string $endpoint = null, ?string $defaultModel = null, ?int $timeout = null)
    {
        $this->endpoint = $endpoint ?? (string) config('services.ollama.endpoint', self::DEFAULT_ENDPOINT);
        $this->defaultModel = $defaultModel ?? (string) config('services.ollama.model', self::DEFAULT_MODEL);
        $this->timeout = max(180, $timeout ?? (int) config('services.ollama.timeout', self::DEFAULT_TIMEOUT));
    }

    /**
     * Send a JSON-constrained completion to local Ollama.
     *
     * @param string $prompt           User prompt (structured data + task)
     * @param string|null $systemPrompt Optional system instructions
     * @param string|null $model        Override default model
     * @param array<string, mixed> $images Optional list of base64-encoded images for VLMs
     *
     * @return array{response: string, model: string, done: bool, raw: array<string, mixed>}
     *
     * @throws ConnectionException if Ollama cannot be reached
     * @throws RequestException if Ollama returns a 4xx/5xx status
     * @throws Throwable any other HTTP transport error
     */
    public function generateJson(
        string $prompt,
        ?string $systemPrompt = null,
        ?string $model = null,
        array $images = [],
    ): array {
        $resolvedModel = $model ?? $this->defaultModel;

        $payload = [
            'model' => $resolvedModel,
            'prompt' => $prompt,
            'stream' => false,
            'format' => 'json',
        ];

        if ($systemPrompt !== null && $systemPrompt !== '') {
            $payload['system'] = $systemPrompt;
        }

        if ($images !== []) {
            $payload['images'] = array_values($images);
        }

        try {
            $response = Http::timeout($this->timeout)->post($this->endpoint, $payload);
        } catch (ConnectionException $e) {
            Log::warning('[Ollama] Connection failed', [
                'endpoint' => $this->endpoint,
                'model' => $resolvedModel,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        } catch (Throwable $e) {
            Log::warning('[Ollama] Transport error', [
                'endpoint' => $this->endpoint,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }

        if (! $response->successful()) {
            Log::warning('[Ollama] Non-successful response', [
                'status' => $response->status(),
                'body' => $response->body(),
                'model' => $resolvedModel,
            ]);

            $response->throw();
        }

        /** @var array<string, mixed> $json */
        $json = $response->json() ?? [];
        $rawResponse = (string) ($json['response'] ?? '');

        return [
            'response' => $rawResponse,
            'model' => (string) ($json['model'] ?? $resolvedModel),
            'done' => (bool) ($json['done'] ?? true),
            'raw' => $json,
        ];
    }

    public function getEndpoint(): string
    {
        return $this->endpoint;
    }

    public function getDefaultModel(): string
    {
        return $this->defaultModel;
    }
}
