<?php

namespace App\Services;

use App\Models\CertificationAIAnalysis;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class CertificationAIService
{
    public const PROMPT_VERSION = '1.0';
    public const FALLBACK_SUMMARY = 'AI analysis is temporarily unavailable. Deterministic certification metrics are still available.';
    public const FALLBACK_RISK_EXPLANATION = 'The automated AI explanation could not be generated. Use the deterministic compliance and risk scores above as the primary decision basis.';
    public const FALLBACK_BUSINESS_IMPACT = 'No AI-generated business impact is available for this analysis cycle. Prioritize actions based on the deterministic priority actions list.';

    public function __construct(
        private readonly OllamaService $ollama,
    ) {}

    /**
     * Run an AI certification analysis based on pre-computed deterministic data.
     *
     * Never crashes — always returns a safe structure.
     *
     * @param array<string, mixed> $deterministicData Pre-computed data from CertificationIntelligenceService
     * @return array{
     *     analysis: CertificationAIAnalysis|null,
     *     success: bool,
     *     error_message: string|null,
     *     used_persisted_cache: bool,
     * }
     */
    public function analyze(
        array $deterministicData,
        bool $refresh = false,
        ?CertificationAIAnalysis $target = null,
    ): array
    {
        $sanitizedInput = $this->sanitizeDeterministicInput($deterministicData);

        if (! $refresh && ! $target) {
            $existing = CertificationAIAnalysis::latestSaved();
            if ($existing instanceof CertificationAIAnalysis) {
                return [
                    'analysis' => $existing,
                    'success' => true,
                    'error_message' => null,
                    'used_persisted_cache' => true,
                ];
            }
        }

        $startedAt = (int) (microtime(true) * 1000);

        try {
            $payload = $this->buildAiPayload($sanitizedInput);
            $systemPrompt = $this->systemPrompt();
            $userPrompt = $this->userPrompt($payload);

            $result = $this->ollama->generateJson(
                prompt: $userPrompt,
                systemPrompt: $systemPrompt,
                model: OllamaService::DEFAULT_MODEL,
            );

            $parsed = $this->parseAndValidateResponse($result['response'] ?? '');

            $analysisData = [
                'summary' => $parsed['summary'],
                'risk_explanation' => $parsed['risk_explanation'],
                'key_insights' => $parsed['key_insights'],
                'priority_actions' => $parsed['priority_actions'],
                'business_impact' => $parsed['business_impact'],
                'confidence' => $parsed['confidence'],
                'compliance_score' => $payload['compliance_score'] ?? null,
                'risk_score' => $payload['risk_score'] ?? null,
                'risk_level' => $payload['risk_level'] ?? null,
                'compliance_level' => $payload['compliance_level'] ?? null,
                'model' => $result['model'] ?? null,
                'prompt_version' => self::PROMPT_VERSION,
                'duration_ms' => (int) ((microtime(true) * 1000) - $startedAt),
                'status' => 'completed',
            ];

            if ($target) {
                $target->update($analysisData);
                $analysis = $target->refresh();
            } else {
                $analysis = CertificationAIAnalysis::query()->create($analysisData);
            }

            return [
                'analysis' => $analysis,
                'success' => true,
                'error_message' => null,
                'used_persisted_cache' => false,
            ];
        } catch (ConnectionException $e) {
            Log::warning('[CertificationAIService] Ollama unavailable', ['error' => $e->getMessage()]);

            return $this->fallbackResult($sanitizedInput, 'Ollama unreachable', $startedAt, $target, $e->getMessage());
        } catch (ValidationException $e) {
            Log::warning('[CertificationAIService] Invalid AI JSON', [
                'errors' => $e->errors(),
            ]);

            return $this->fallbackResult($sanitizedInput, 'Invalid AI response JSON', $startedAt, $target, $e->getMessage() . ' ' . json_encode($e->errors()));
        } catch (Throwable $e) {
            Log::warning('[CertificationAIService] AI analysis failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->fallbackResult($sanitizedInput, 'AI analysis failed', $startedAt, $target, $e->getMessage());
        }
    }

    /**
     * @param array<string, mixed> $deterministicData
     * @return array<string, mixed>
     */
    private function sanitizeDeterministicInput(array $deterministicData): array
    {
        $counts = $deterministicData['counts'] ?? [];
        $compliance = $deterministicData['compliance'] ?? [];
        $risk = $deterministicData['risk'] ?? [];
        $expirationAnalysis = $deterministicData['expiration_analysis'] ?? [];
        $impactAnalysis = $deterministicData['impact_analysis'] ?? [];
        $snapshot = $deterministicData['snapshot'] ?? [];
        $priorityActions = $deterministicData['priority_actions'] ?? [];

        $nearest = is_array($expirationAnalysis['nearest'] ?? null) ? array_values($expirationAnalysis['nearest']) : [];
        $topProducts = is_array($impactAnalysis['top_affected_products'] ?? null) ? array_values($impactAnalysis['top_affected_products']) : [];

        return [
            'snapshot' => $snapshot,
            'counts' => [
                'valid_certifications' => (int) ($counts['valid_certifications'] ?? 0),
                'expiring_soon' => (int) ($counts['expiring_soon'] ?? 0),
                'expired_certifications' => (int) ($counts['expired_certifications'] ?? 0),
                'pending_certifications' => (int) ($counts['pending_certifications'] ?? 0),
                'total_certifications' => (int) ($counts['total_certifications'] ?? 0),
                'certified_products' => (int) ($counts['certified_products'] ?? 0),
                'uncertified_products' => (int) ($counts['uncertified_products'] ?? 0),
                'affected_products' => (int) ($counts['affected_products'] ?? 0),
                'products_affected_by_expiring' => (int) ($counts['products_affected_by_expiring'] ?? 0),
                'products_affected_by_expired' => (int) ($counts['products_affected_by_expired'] ?? 0),
                'total_products' => (int) ($counts['total_products'] ?? ($snapshot['total_products'] ?? 0)),
            ],
            'compliance_score' => (int) round((float) ($compliance['score'] ?? 0)),
            'compliance_level' => (string) ($compliance['level'] ?? 'UNKNOWN'),
            'risk_score' => (int) round((float) ($risk['score'] ?? 0)),
            'risk_level' => (string) ($risk['level'] ?? 'UNKNOWN'),
            'risk_factors' => is_array($risk['factors'] ?? null) ? array_values($risk['factors']) : [],
            'expiration_groups' => is_array($expirationAnalysis['groups'] ?? null) ? $expirationAnalysis['groups'] : [],
            'expiring_in_next_7_days' => (int) ($expirationAnalysis['expiring_in_next_7_days'] ?? 0),
            'nearest_expirations' => array_slice($nearest, 0, 8),
            'top_affected_products' => array_slice($topProducts, 0, 8),
            'coverage_percent' => isset($impactAnalysis['coverage_percent']) ? (float) $impactAnalysis['coverage_percent'] : null,
            'deterministic_priority_actions' => is_array($priorityActions) ? array_values($priorityActions) : [],
        ];
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function buildAiPayload(array $data): array
    {
        return [
            'compliance_score' => $data['compliance_score'],
            'compliance_level' => $data['compliance_level'],
            'valid_certifications' => $data['counts']['valid_certifications'],
            'expiring_soon' => $data['counts']['expiring_soon'],
            'expired_certifications' => $data['counts']['expired_certifications'],
            'uncertified_products' => $data['counts']['uncertified_products'],
            'affected_products' => $data['counts']['affected_products'],
            'risk_score' => $data['risk_score'],
            'risk_level' => $data['risk_level'],
            'risk_factors' => $data['risk_factors'],
            'coverage_percent' => $data['coverage_percent'],
            'expiring_next_7' => $data['expiring_in_next_7_days'],
            'nearest_expirations' => $data['nearest_expirations'],
            'top_affected_products' => $data['top_affected_products'],
            'deterministic_priority_actions' => $data['deterministic_priority_actions'],
            'total_certifications' => $data['counts']['total_certifications'],
            'total_products' => $data['counts']['total_products'],
            'certified_products' => $data['counts']['certified_products'],
            'pending_certifications' => $data['counts']['pending_certifications'],
        ];
    }

    private function systemPrompt(): string
    {
        return <<<'SYSTEM'
You are a food certification compliance analyst for NutriTrace, a French food traceability SaaS platform.

You receive ONLY structured data already computed by the Laravel deterministic certification engine.
That deterministic engine is the SOURCE OF TRUTH.

RULES — STRICTLY ENFORCED:
1. Do NOT invent facts.
2. Do NOT modify any numerical values.
3. Do NOT invent certifications, products, dates, companies or quantities.
4. Do NOT claim a certification is expired/expiring unless the provided data says so.
5. Do NOT create scores, counts or levels. Use only values present in the provided data.
6. Analyse ONLY the data given to you in the user message.
7. All reasoning must be grounded in the numbers and risk factors provided.
8. If counts are zero, explicitly state that nothing is at risk.
9. Produce practical, concrete, priority-ordered actions that a compliance manager could execute today.

Return ONLY valid JSON with exactly these keys (no trailing commas):
{
  "summary": "Short paragraph (2-3 sentences) summarizing the compliance situation using ONLY the provided numbers.",
  "risk_explanation": "One paragraph explaining the risk level with specific factors.",
  "key_insights": [
    "Between 3 and 6 bullet insight strings describing the most important visible patterns or priorities."
  ],
  "priority_actions": [
    {
      "priority": "HIGH or MEDIUM or LOW",
      "action": "Short, concrete action sentence.",
      "reason": "Why this action must be taken, citing the numbers explicitly."
    }
  ],
  "business_impact": "One paragraph on potential commercial, audit or regulatory consequences.",
  "confidence": "Integer between 0 and 100 — self-assess how much of the answer is directly backed by the provided structured data. 100 = purely factual synthesis of provided data, lower if you needed to infer or extrapolate."
}

Do NOT output markdown, code fences, commentary, explanations, or ANY text outside the JSON object.
SYSTEM;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function userPrompt(array $payload): string
    {
        return <<<USER
Analyze the following NutriTrace certification compliance data produced by the deterministic engine.
Return ONLY valid JSON per your instructions.

DETERMINISTIC_DATA = {$this->jsonEncodeSafe($payload)}
USER;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function jsonEncodeSafe(array $payload): string
    {
        return (string) json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    /**
     * @return array{summary: string, risk_explanation: string, key_insights: list<string>, priority_actions: list<array{priority: string, action: string, reason: string}>, business_impact: string, confidence: int|null}
     *
     * @throws ValidationException if response is unparseable or violates shape
     */
    private function parseAndValidateResponse(string $raw): array
    {
        $cleaned = trim($raw);
        $cleaned = preg_replace('/^```json\s*|\s*```$/i', '', $cleaned) ?? $cleaned;
        $cleaned = preg_replace('/^```\s*|\s*```$/i', '', $cleaned) ?? $cleaned;
        $cleaned = trim($cleaned);

        if ($cleaned === '') {
            throw ValidationException::withMessages(['ai_response' => 'Empty AI response']);
        }

        /** @var mixed $decoded */
        $decoded = json_decode($cleaned, true);
        if (! is_array($decoded)) {
            throw ValidationException::withMessages(['ai_response' => 'Not a valid JSON object']);
        }

        $summary = $this->stringOrDefault($decoded['summary'] ?? null);
        $riskExplanation = $this->stringOrDefault($decoded['risk_explanation'] ?? null);
        $businessImpact = $this->stringOrDefault($decoded['business_impact'] ?? null);

        $keyInsightsRaw = is_array($decoded['key_insights'] ?? null) ? $decoded['key_insights'] : [];
        $keyInsights = array_values(array_filter(array_map(
            fn (mixed $item): ?string => is_string($item) ? $this->truncate(trim($item), 1000) : null,
            $keyInsightsRaw,
        )));

        if (count($keyInsights) === 0) {
            $keyInsights = [$this->truncate($summary, 500)];
        }

        $actionsRaw = is_array($decoded['priority_actions'] ?? null) ? $decoded['priority_actions'] : [];
        $priorityActions = [];
        foreach ($actionsRaw as $actionRaw) {
            if (! is_array($actionRaw)) {
                continue;
            }

            $priority = strtoupper((string) ($actionRaw['priority'] ?? 'MEDIUM'));
            if (! in_array($priority, ['HIGH', 'MEDIUM', 'LOW'], true)) {
                $priority = 'MEDIUM';
            }

            $action = $this->stringOrDefault($actionRaw['action'] ?? null);
            if ($action === '' || $action === '(non fourni)') {
                continue;
            }

            $reason = $this->stringOrDefault($actionRaw['reason'] ?? null, 'Aucune raison explicitée');

            $priorityActions[] = [
                'priority' => $priority,
                'action' => $this->truncate($action, 1000),
                'reason' => $this->truncate($reason, 1000),
            ];
        }

        if (count($priorityActions) === 0) {
            $priorityActions[] = [
                'priority' => 'LOW',
                'action' => 'Maintenir le niveau de conformité actuel',
                'reason' => "L'IA n'a pas généré d'actions priorisées. Référez-vous aux actions déterministes.",
            ];
        }

        $confidenceRaw = $decoded['confidence'] ?? null;
        $confidence = null;
        if (is_int($confidenceRaw) || is_float($confidenceRaw)) {
            $confidence = (int) round(max(0, min(100, (float) $confidenceRaw)));
        } elseif (is_string($confidenceRaw) && is_numeric($confidenceRaw)) {
            $confidence = (int) round(max(0, min(100, (float) $confidenceRaw)));
        }

        return [
            'summary' => $summary,
            'risk_explanation' => $riskExplanation,
            'key_insights' => $keyInsights,
            'priority_actions' => $priorityActions,
            'business_impact' => $businessImpact,
            'confidence' => $confidence,
        ];
    }

    private function stringOrDefault(mixed $value, string $default = '(non fourni)'): string
    {
        if ($value === null) {
            return $default;
        }

        $str = is_string($value) ? $value : (is_scalar($value) ? (string) $value : $default);

        return $this->truncate(trim($str), 5000);
    }

    private function truncate(string $value, int $maxChars): string
    {
        if (mb_strlen($value) <= $maxChars) {
            return $value;
        }

        return mb_substr($value, 0, $maxChars - 3) . '...';
    }

    /**
     * @param array<string, mixed> $sanitizedInput
     * @return array{
     *     analysis: CertificationAIAnalysis|null,
     *     success: bool,
     *     error_message: string,
     *     used_persisted_cache: bool,
     * }
     */
    private function fallbackResult(
        array $sanitizedInput,
        string $error,
        int $startedAt,
        ?CertificationAIAnalysis $target = null,
        ?string $recordedError = null,
    ): array
    {
        $deterministicFactors = $sanitizedInput['risk_factors'] ?? [];
        $deterministicActions = $sanitizedInput['deterministic_priority_actions'] ?? [];

        $fallbackKeyInsights = array_values(array_filter(array_merge(
            is_array($deterministicFactors) ? array_slice($deterministicFactors, 0, 4) : [],
            [
                'Analyse IA indisponible — les compteurs ci-dessus font foi.',
                'Relancez une analyse IA quand le service est de nouveau disponible.',
            ],
        )));

        $fallbackActions = is_array($deterministicActions) && count($deterministicActions) > 0
            ? array_slice($deterministicActions, 0, 5)
            : [
                ['priority' => 'MEDIUM', 'action' => 'Revalider la conformité manuellement', 'reason' => 'Service IA temporairement indisponible'],
            ];

        try {
            $analysisData = [
                'summary' => self::FALLBACK_SUMMARY,
                'risk_explanation' => self::FALLBACK_RISK_EXPLANATION,
                'key_insights' => $fallbackKeyInsights,
                'priority_actions' => $fallbackActions,
                'business_impact' => self::FALLBACK_BUSINESS_IMPACT,
                'confidence' => null,
                'compliance_score' => $sanitizedInput['compliance_score'] ?? null,
                'risk_score' => $sanitizedInput['risk_score'] ?? null,
                'risk_level' => $sanitizedInput['risk_level'] ?? null,
                'compliance_level' => $sanitizedInput['compliance_level'] ?? null,
                'model' => 'fallback_' . OllamaService::DEFAULT_MODEL,
                'prompt_version' => self::PROMPT_VERSION,
                'duration_ms' => (int) ((microtime(true) * 1000) - $startedAt),
                'status' => 'failed',
                'error_message' => mb_substr($recordedError ?? $error, 0, 10000),
            ];

            if ($target) {
                $target->update($analysisData);
                $analysis = $target->refresh();
            } else {
                $analysis = CertificationAIAnalysis::query()->create($analysisData);
            }
        } catch (Throwable $e) {
            Log::warning('[CertificationAIService] Persisting fallback failed', ['error' => $e->getMessage()]);
            $analysis = null;
        }

        return [
            'analysis' => $analysis,
            'success' => false,
            'error_message' => $error,
            'used_persisted_cache' => false,
        ];
    }
}
