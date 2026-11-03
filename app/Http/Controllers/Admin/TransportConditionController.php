<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTransportConditionRequest;
use App\Http\Requests\Admin\UpdateTransportConditionRequest;
use App\Models\Batch;
use App\Models\TransportCondition;
use App\Services\ColdChainAlertService;
use App\Services\LlmAnalysisService;
use App\Services\MlPredictionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransportConditionController extends Controller
{
    public function index(Request $request): View
    {
        $conditions = TransportCondition::with('batch')
            ->when($request->filled('batch_id'), fn ($query) => $query->where('batch_id', $request->integer('batch_id')))
            ->when($request->filled('date'), fn ($query) => $query->whereDate('recorded_at', $request->string('date')))
            ->when($request->boolean('out_of_range'), fn ($query) => $query->outOfRange())
            ->latest('recorded_at')
            ->paginate(10)
            ->withQueryString();

        return view('admin.transport-conditions.index', [
            'conditions' => $conditions,
            'batches' => Batch::orderBy('lot_number')->get(['id', 'lot_number']),
        ]);
    }

    public function create(): View
    {
        return view('admin.transport-conditions.create', [
            'batches' => Batch::orderBy('lot_number')->get(['id', 'lot_number']),
        ]);
    }

    public function batch(Batch $batch, MlPredictionService $mlService, LlmAnalysisService $llmService): View
    {
        $analysis = app(ColdChainAlertService::class)->analyzeBatch($batch);

        return $this->batchView($batch, $analysis, null, [], $mlService, $llmService);
    }

    public function simulateBatch(Request $request, Batch $batch, ColdChainAlertService $alertService, MlPredictionService $mlService, LlmAnalysisService $llmService): View
    {
        $validated = $request->validate([
            'temperature' => ['required', 'numeric', 'between:-50,60'],
            'duration_minutes' => ['required', 'integer', 'min:0'],
        ]);

        $analysis = $alertService->analyzeBatch($batch);

        return $this->batchView(
            $batch,
            $analysis,
            $alertService->simulate((float) $validated['temperature'], (int) $validated['duration_minutes']),
            $validated,
            $mlService,
            $llmService
        );
    }

    private function batchView(
        Batch $batch,
        array $analysis,
        ?array $simulation = null,
        array $simulationInput = [],
        ?MlPredictionService $mlService = null,
        ?LlmAnalysisService $llmService = null
    ): View
    {
        $latestCondition = $analysis['conditions']->last();
        $temperature = $simulationInput !== []
            ? (float) $simulationInput['temperature']
            : ($latestCondition ? (float) $latestCondition->temperature : $analysis['average_temperature']);
        $duration = $simulationInput !== []
            ? (int) $simulationInput['duration_minutes']
            : ($latestCondition ? (int) ($latestCondition->duration_minutes ?? 0) : $analysis['total_minutes_out_of_range']);
        $ml = $temperature === null || $mlService === null
            ? ['available' => false, 'probability' => null, 'model' => null]
            : $mlService->predict($temperature, $duration);
        $llm = $llmService?->analyze([
            'batch_code' => $batch->lot_number,
            'product' => $batch->product?->name,
            'temperature' => $temperature,
            'duration' => $duration,
            'rule_based_score' => $simulation['risk_score'] ?? $analysis['risk_score'],
            'severity' => $simulation['severity'] ?? $analysis['risk_level'],
            'ml_probability' => $ml['probability'],
            'average_temperature' => $analysis['average_temperature'],
            'max_deviation' => $analysis['max_deviation'],
            'minutes_out_of_range' => $analysis['total_minutes_out_of_range'],
        ]) ?? ['available' => false, 'summary' => null, 'probable_cause' => null, 'recommended_actions' => []];

        return view('admin.transport-conditions.batch', [
            'batch' => $batch->load('product'),
            'analysis' => $analysis,
            'simulation' => $simulation,
            'simulationInput' => $simulationInput,
            'ml' => $ml,
            'llm' => $llm,
        ]);
    }

    public function store(StoreTransportConditionRequest $request, ColdChainAlertService $alertService): RedirectResponse
    {
        DB::transaction(function () use ($request, $alertService): void {
            $condition = TransportCondition::create([
                ...$request->validated(),
                'created_by' => $request->user()->id,
            ]);

            $alertService->syncAlert($condition);
        });

        return redirect()->route('admin.transport-conditions.index')->with('success', 'Transport condition created successfully.');
    }

    public function show(TransportCondition $transportCondition, MlPredictionService $mlService): View
    {
        $transportCondition->load(['batch.product', 'traceabilityEvent', 'alerts']);
        $ml = $mlService->predict(
            (float) $transportCondition->temperature,
            (int) ($transportCondition->duration_minutes ?? 0)
        );

        return view('admin.transport-conditions.show', compact('transportCondition', 'ml'));
    }

    public function edit(TransportCondition $transportCondition): View
    {
        return view('admin.transport-conditions.edit', [
            'transportCondition' => $transportCondition,
            'batches' => Batch::orderBy('lot_number')->get(['id', 'lot_number']),
        ]);
    }

    public function update(UpdateTransportConditionRequest $request, TransportCondition $transportCondition, ColdChainAlertService $alertService): RedirectResponse
    {
        DB::transaction(function () use ($request, $transportCondition, $alertService): void {
            $transportCondition->update($request->validated());
            $alertService->syncAlert($transportCondition->fresh());
        });

        return redirect()->route('admin.transport-conditions.index')->with('success', 'Transport condition updated successfully.');
    }

    public function destroy(TransportCondition $transportCondition): RedirectResponse
    {
        $transportCondition->delete();

        return redirect()->route('admin.transport-conditions.index')->with('success', 'Transport condition deleted successfully.');
    }
}
