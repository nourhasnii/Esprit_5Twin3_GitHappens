<?php

use App\Models\Batch;
use App\Models\Alert;
use App\Models\TransportCondition;
use App\Notifications\CriticalAlertNotification;
use App\Services\ColdChainAlertService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

it('classifies cold-chain temperature conditions and creates the expected alerts', function () {
    Notification::fake();
    $this->seed();
    Notification::fake();
    $batch = Batch::query()->firstOrFail();
    $service = app(ColdChainAlertService::class);

    $criticalCondition = TransportCondition::factory()->for($batch)->create([
        'temperature' => 12,
        'duration_minutes' => 0,
    ]);
    $criticalAlert = $service->syncAlert($criticalCondition);

    expect($criticalAlert?->severity)->toBe('critical')
        ->and($criticalAlert?->batch_id)->toBe($batch->id)
        ->and($criticalAlert?->transport_condition_id)->toBe($criticalCondition->id)
        ->and($criticalAlert?->risk_score)->toBeGreaterThan(0);

    $service->syncAlert($criticalCondition);
    expect(Alert::where('transport_condition_id', $criticalCondition->id)->count())->toBe(1);
    \App\Models\User::role('admin')->get()->each(
        fn ($admin) => Notification::assertSentTo($admin, CriticalAlertNotification::class)
    );

    $warningCondition = TransportCondition::factory()->for($batch)->create([
        'temperature' => 7,
        'duration_minutes' => 0,
    ]);
    $warningAlert = $service->syncAlert($warningCondition);

    expect($warningAlert?->severity)->toBe('warning');

    $safeCondition = TransportCondition::factory()->for($batch)->create([
        'temperature' => 4,
        'duration_minutes' => 0,
    ]);

    expect($service->syncAlert($safeCondition))->toBeNull();
});

it('analyzes a batch with explainable cold-chain statistics', function () {
    Notification::fake();
    $this->seed();
    Notification::fake();
    $batch = Batch::query()->firstOrFail();
    $service = app(ColdChainAlertService::class);
    Alert::where('batch_id', $batch->id)->delete();
    TransportCondition::where('batch_id', $batch->id)->delete();

    foreach ([[4, 0], [-1, 20], [10, 40]] as [$temperature, $duration]) {
        $condition = TransportCondition::factory()->for($batch)->create([
            'temperature' => $temperature,
            'duration_minutes' => $duration,
        ]);
        $service->syncAlert($condition);
    }

    $analysis = $service->analyzeBatch($batch);

    expect($analysis['out_of_range_measurements'])->toBe(2)
        ->and($analysis['max_deviation'])->toBe(2.0)
        ->and($analysis['total_minutes_out_of_range'])->toBe(60)
        ->and($analysis['risk_score'])->toBeGreaterThan(60)
        ->and($analysis['risk_level'])->toBe('high')
        ->and($analysis['reasons'])->not->toBeEmpty();
});

it('simulates an alert without persisting anything', function () {
    Notification::fake();
    $this->seed();
    Notification::fake();
    $service = app(ColdChainAlertService::class);
    $alertCount = Alert::count();

    $simulation = $service->simulate(12, 40);

    expect($simulation['severity'])->toBe('critical')
        ->and($simulation['risk_score'])->toBe(90)
        ->and($simulation['temperature_points'])->toBe(70)
        ->and($simulation['duration_points'])->toBe(20)
        ->and($simulation['explanation'])->toContain('Score:');
    expect(Alert::count())->toBe($alertCount);
});

it('prints a cold-chain demo without saving data', function () {
    Notification::fake();
    $this->seed();
    Notification::fake();
    $alertCount = Alert::count();

    $this->artisan('coldchain:demo', [
        'temperature' => 12,
        'duration' => 45,
    ])
        ->expectsOutput('Temperature received: 12.00 °C')
        ->expectsOutput('Duration received: 45 minute(s)')
        ->expectsOutputToContain('Thresholds:')
        ->expectsOutput('Severity: critical')
        ->expectsOutput('Risk score: 92.50 / 100')
        ->expectsOutput('Breakdown: temperature points 70.00 + duration points 22.50')
        ->expectsOutputToContain('Explanation:')
        ->assertExitCode(0);

    expect(Alert::count())->toBe($alertCount);
});

it('explains a generated alert using the configured risk breakdown', function () {
    $batch = Batch::factory()->create();
    $condition = TransportCondition::factory()->for($batch)->create([
        'temperature' => 12,
        'duration_minutes' => 40,
    ]);
    $service = app(ColdChainAlertService::class);
    $alert = $service->syncAlert($condition);

    $explanation = $service->explainAlert($alert);

    expect($explanation['message'])->toBe($alert->message)
        ->and($explanation['temperature_points'])->toEqual(70)
        ->and($explanation['duration_points'])->toEqual(20)
        ->and($explanation['risk_score'])->toEqual(90);
});
