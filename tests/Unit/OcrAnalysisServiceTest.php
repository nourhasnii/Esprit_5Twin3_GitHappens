<?php

use App\Models\Batch;
use App\Services\OcrAnalysisService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

uses(TestCase::class);

it('returns zero inconsistency when OCR matches declared batch values', function () {
    $batch = new Batch([
        'lot_number' => 'LOT-42',
        'production_date' => '2026-01-10',
        'expiration_date' => '2027-01-10',
        'quantity' => 12.5,
    ]);

    $result = app(OcrAnalysisService::class)->compareWithBatch([
        'lot_number' => 'lot-42',
        'production_date' => '10/01/2026',
        'expiration_date' => '2027-01-10',
        'quantity' => '12.50',
    ], $batch);

    expect($result['mismatches'])->toBe([])
        ->and($result['inconsistency_score'])->toBe(0.0);
});

it('weights a lot number mismatch more heavily than a quantity mismatch', function () {
    $batch = new Batch([
        'lot_number' => 'LOT-42',
        'production_date' => '2026-01-10',
        'expiration_date' => '2027-01-10',
        'quantity' => 12.5,
    ]);
    $service = app(OcrAnalysisService::class);

    $lotMismatch = $service->compareWithBatch(['lot_number' => 'LOT-99'], $batch);
    $quantityMismatch = $service->compareWithBatch(['quantity' => 10], $batch);
    $expirationMismatch = $service->compareWithBatch(['expiration_date' => '2026-12-01'], $batch);

    expect($lotMismatch['inconsistency_score'])->toBe(40.0)
        ->and($quantityMismatch['inconsistency_score'])->toBe(10.0)
        ->and($lotMismatch['mismatches'][0]['severity'])->toBe('critical')
        ->and($expirationMismatch['mismatches'][0]['declared_value'])->toBe('2027-01-10');
});

it('extracts strict JSON from the local Ollama response', function () {
    Storage::fake('public');
    Storage::disk('public')->put('ocr-labels/test.jpg', 'fake image bytes');
    Http::fake([
        'http://localhost:11434/api/generate' => Http::response([
            'response' => "```json\n{\"product_name\":\"Olive oil\",\"lot_number\":\"LOT-42\",\"production_date\":null,\"expiration_date\":\"2027-01-10\",\"quantity\":5,\"unit\":\"L\",\"allergens\":[],\"certifications\":[\"Organic\"],\"other_text\":\"Cold pressed\",\"confidence\":0.91}\n```",
        ]),
    ]);

    $result = app(OcrAnalysisService::class)->analyze('ocr-labels/test.jpg');

    expect($result['product_name'])->toBe('Olive oil')
        ->and($result['lot_number'])->toBe('LOT-42')
        ->and($result['certifications'])->toBe(['Organic'])
        ->and($result['confidence'])->toBe(0.91);

    Http::assertSent(fn (Request $request) => $request->url() === 'http://localhost:11434/api/generate'
        && $request['model'] === 'qwen2.5vl'
        && $request['stream'] === false
        && count($request['images']) === 1);
});
