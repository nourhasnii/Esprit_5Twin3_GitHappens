<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTransportConditionRequest;
use App\Http\Requests\Admin\UpdateTransportConditionRequest;
use App\Models\Batch;
use App\Models\TransportCondition;
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

    public function store(StoreTransportConditionRequest $request): RedirectResponse
    {
        TransportCondition::create([
            ...$request->validated(),
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('admin.transport-conditions.index')->with('success', 'Transport condition created successfully.');
    }

    public function show(TransportCondition $transportCondition): View
    {
        $transportCondition->load(['batch.product', 'traceabilityEvent', 'alerts']);

        return view('admin.transport-conditions.show', compact('transportCondition'));
    }

    public function edit(TransportCondition $transportCondition): View
    {
        return view('admin.transport-conditions.edit', [
            'transportCondition' => $transportCondition,
            'batches' => Batch::orderBy('lot_number')->get(['id', 'lot_number']),
        ]);
    }

    public function update(UpdateTransportConditionRequest $request, TransportCondition $transportCondition): RedirectResponse
    {
        $transportCondition->update($request->validated());

        return redirect()->route('admin.transport-conditions.index')->with('success', 'Transport condition updated successfully.');
    }

    public function destroy(TransportCondition $transportCondition): RedirectResponse
    {
        $transportCondition->delete();

        return redirect()->route('admin.transport-conditions.index')->with('success', 'Transport condition deleted successfully.');
    }
}
