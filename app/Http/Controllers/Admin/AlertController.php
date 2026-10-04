<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAlertRequest;
use App\Http\Requests\Admin\UpdateAlertRequest;
use App\Models\Alert;
use App\Models\Batch;
use App\Models\TransportCondition;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AlertController extends Controller
{
    public function index(Request $request): View
    {
        $query = Alert::with(['batch', 'transportCondition']);
        $alerts = $query
            ->when($request->filled('severity'), fn ($query) => $query->where('severity', $request->string('severity')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('batch_id'), fn ($query) => $query->where('batch_id', $request->integer('batch_id')))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.alerts.index', [
            'alerts' => $alerts,
            'batches' => Batch::orderBy('lot_number')->get(['id', 'lot_number']),
            'openCount' => Alert::open()->count(),
            'criticalCount' => Alert::where('severity', 'critical')->whereNotIn('status', ['resolved', 'ignored'])->count(),
            'averageRiskScore' => Alert::whereNotNull('risk_score')->avg('risk_score'),
            'lastAlerts' => Alert::with('batch')->latest()->limit(5)->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.alerts.create', [
            'batches' => Batch::orderBy('lot_number')->get(['id', 'lot_number']),
            'conditions' => TransportCondition::with('batch')->latest('recorded_at')->get(),
        ]);
    }

    public function store(StoreAlertRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['created_by'] = $request->user()->id;
        $data = $this->syncBatchFromCondition($data);

        Alert::create($data);

        return redirect()->route('admin.alerts.index')->with('success', 'Alert created successfully.');
    }

    public function show(Alert $alert): View
    {
        $alert->load(['batch.product', 'transportCondition', 'creator', 'resolver']);

        return view('admin.alerts.show', compact('alert'));
    }

    public function edit(Alert $alert): View
    {
        return view('admin.alerts.edit', [
            'alert' => $alert,
            'batches' => Batch::orderBy('lot_number')->get(['id', 'lot_number']),
            'conditions' => TransportCondition::with('batch')->latest('recorded_at')->get(),
        ]);
    }

    public function update(UpdateAlertRequest $request, Alert $alert): RedirectResponse
    {
        $data = $this->syncBatchFromCondition($request->validated());
        if ($data['status'] !== 'resolved') {
            $data['resolved_at'] = null;
            $data['resolved_by'] = null;
        }
        $alert->update($data);

        return redirect()->route('admin.alerts.index')->with('success', 'Alert updated successfully.');
    }

    public function destroy(Alert $alert): RedirectResponse
    {
        $alert->delete();

        return redirect()->route('admin.alerts.index')->with('success', 'Alert deleted successfully.');
    }

    public function resolve(Request $request, Alert $alert): RedirectResponse
    {
        $alert->update(['status' => 'resolved', 'resolved_at' => now(), 'resolved_by' => $request->user()->id]);

        return back()->with('success', 'Alert resolved successfully.');
    }

    public function ignore(Alert $alert): RedirectResponse
    {
        $alert->update(['status' => 'ignored', 'resolved_at' => null, 'resolved_by' => null]);

        return back()->with('success', 'Alert ignored successfully.');
    }

    public function acknowledge(Alert $alert): RedirectResponse
    {
        $alert->update(['status' => 'acknowledged']);

        return back()->with('success', 'Alert acknowledged successfully.');
    }

    private function syncBatchFromCondition(array $data): array
    {
        if (! empty($data['transport_condition_id'])) {
            $condition = TransportCondition::findOrFail($data['transport_condition_id']);
            $data['batch_id'] = $condition->batch_id;
        }

        return $data;
    }
}
