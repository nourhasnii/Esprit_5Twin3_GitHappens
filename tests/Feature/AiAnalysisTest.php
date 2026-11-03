<?php

use App\Models\Batch;
use App\Models\TransportCondition;
use App\Models\User;
use App\Services\LlmAnalysisService;
use App\Services\MlPredictionService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

function aiContext(): array
{
    return [
        'batch_code' => 'LOT-TEST-001',
        'product' => 'Tomates',
        'temperature' => 12,
        'duration' => 45,
        'rule_based_score' => 92.5,
        'severity' => 'critical',
        'ml_probability' => 0.96,
        'average_temperature' => 8.5,
        'max_deviation' => 4,
        'minutes_out_of_range' => 45,
    ];
}

it('returns a parsed Gemini analysis and caches identical contexts', function () {
    config()->set([
        'ai.llm.enabled' => true,
        'ai.llm.provider' => 'gemini',
        'ai.llm.api_key' => 'test-key',
        'ai.llm.model' => 'gemini-test',
    ]);
    Cache::flush();
    Http::fake([
        'generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [[
                'content' => ['parts' => [['text' => '{"summary":"Température élevée. Risque important.","probable_cause":"Rupture du froid.","recommended_action":["Isoler le lot","Vérifier le transport"]}']]],
            ]],
        ]),
    ]);

    $service = app(LlmAnalysisService::class);
    $first = $service->analyze(aiContext());
    $second = $service->analyze(aiContext());

    expect($first['available'])->toBeTrue()
        ->and($first['summary'])->toBe('Température élevée. Risque important.')
        ->and($first['recommended_actions'])->toHaveCount(2)
        ->and($second)->toEqual($first);
    Http::assertSentCount(1);
    Http::assertSent(function ($request): bool {
        return $request->hasHeader('x-goog-api-key', 'test-key')
            && ! str_contains($request->url(), 'key=')
            && $request->data()['generationConfig']['responseMimeType'] === 'application/json';
    });

    config()->set('ai.llm.model', 'another-gemini-test');
    $service->analyze(aiContext());
    Http::assertSentCount(2);
});

it('falls back when the LLM response is invalid or unavailable', function () {
    config()->set([
        'ai.llm.enabled' => true,
        'ai.llm.provider' => 'gemini',
        'ai.llm.api_key' => 'test-key',
    ]);
    Http::fake([
        '*' => Http::response(['candidates' => []], 200),
    ]);

    $result = app(LlmAnalysisService::class)->analyze(aiContext());

    expect($result)->toBe([
        'available' => false,
        'summary' => null,
        'probable_cause' => null,
        'recommended_actions' => [],
    ]);
});

it('renders AI results and neutral fallbacks with a mocked ML service', function () {
    $this->seed();
    $user = User::query()->where('email', 'test@example.com')->firstOrFail();
    $batch = Batch::query()->firstOrFail();
    TransportCondition::factory()->for($batch)->create([
        'temperature' => 12,
        'duration_minutes' => 45,
    ]);

    config()->set([
        'ai.llm.enabled' => true,
        'ai.llm.provider' => 'gemini',
        'ai.llm.api_key' => 'test-key',
    ]);
    Http::fake([
        '*' => Http::response([
            'candidates' => [[
                'content' => ['parts' => [['text' => '{"summary":"La température est élevée. Le risque est important.","probable_cause":"Excursion thermique.","recommended_action":["Contrôler le véhicule"]}']]],
            ]],
        ]),
    ]);
    $this->mock(MlPredictionService::class, function ($mock): void {
        $mock->shouldReceive('predict')->andReturn([
            'available' => true,
            'probability' => 0.9611,
            'model' => 'GradientBoosting',
        ]);
    });

    $this->actingAs($user)
        ->get(route('admin.transport-conditions.batch', $batch))
        ->assertOk()
        ->assertSee('Analyse IA')
        ->assertSee('96.11%')
        ->assertSee('Excursion thermique.');

    $this->mock(MlPredictionService::class, function ($mock): void {
        $mock->shouldReceive('predict')->andReturn([
            'available' => false,
            'probability' => null,
            'model' => null,
        ]);
    });
    Http::fake([
        '*' => Http::response(['candidates' => []], 200),
    ]);

    $this->actingAs($user)
        ->get(route('admin.transport-conditions.batch', $batch))
        ->assertOk()
        ->assertSee('Analyse IA indisponible');
});
