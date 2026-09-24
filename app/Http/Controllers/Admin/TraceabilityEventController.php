<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\TraceabilityEvent;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TraceabilityEventController extends Controller
{
    public function index(Request $request)
    {
        $events = TraceabilityEvent::with(['batch.product', 'actor'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = '%' . $request->string('search') . '%';
                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery->where('location', 'like', $search)
                        ->orWhere('description', 'like', $search)
                        ->orWhereHas('batch', fn ($batchQuery) => $batchQuery->where('lot_number', 'like', $search));
                });
            })
            ->when($request->filled('event_type'), fn ($query) => $query->where('event_type', (string) $request->string('event_type')))
            ->when($request->filled('batch_id'), fn ($query) => $query->where('batch_id', $request->integer('batch_id')))
            ->when($request->filled('event_date'), fn ($query) => $query->whereDate('event_date', $request->date('event_date')))
            ->latest('event_date')
            ->paginate(10)
            ->withQueryString();

        $batches = Batch::with('product')->orderByDesc('created_at')->get();
        $counts = collect(TraceabilityEvent::TYPES)->mapWithKeys(fn ($type) => [$type => TraceabilityEvent::where('event_type', $type)->count()]);

        return view('admin.events.index', compact('events', 'batches', 'counts'));
    }

    public function create(Request $request)
    {
        $batches = Batch::with('product')->latest()->get();
        $actors = User::orderBy('name')->get(['id', 'name']);
        $selectedBatch = $request->integer('batch_id') ?: null;

        return view('admin.events.create', compact('batches', 'actors', 'selectedBatch'));
    }

    public function store(Request $request)
    {
        TraceabilityEvent::create($this->validatedData($request));

        return $this->redirectAfterSave($request, 'Traceability event added successfully.');
    }

    public function show(TraceabilityEvent $event)
    {
        $event->load(['batch.product', 'actor']);

        return view('admin.events.show', compact('event'));
    }

    public function edit(TraceabilityEvent $event)
    {
        $batches = Batch::with('product')->latest()->get();
        $actors = User::orderBy('name')->get(['id', 'name']);

        return view('admin.events.edit', compact('event', 'batches', 'actors'));
    }

    public function update(Request $request, TraceabilityEvent $event)
    {
        $event->update($this->validatedData($request));

        return $this->redirectAfterSave($request, 'Traceability event updated successfully.');
    }

    public function destroy(TraceabilityEvent $event)
    {
        $event->delete();

        return redirect()->route('admin.events.index')->with('success', 'Traceability event deleted successfully.');
    }

    public function timeline(Batch $batch)
    {
        $batch->load([
            'product',
            'events' => fn ($query) => $query->with('actor')->orderBy('event_date'),
        ]);

        $events = $batch->events;
        $typesPresent = $events->pluck('event_type')->unique();
        $missingTypes = collect(TraceabilityEvent::TYPES)
            ->reject(fn ($type) => $typesPresent->contains($type))
            ->values();
        $firstEvent = $events->first();
        $latestEvent = $events->last();

        $summary = [
            'total' => $events->count(),
            'first' => $firstEvent?->event_date,
            'latest' => $latestEvent?->event_date,
            'distance' => $events->sum(fn ($event) => (float) ($event->distance_km ?? 0)),
            'carbon' => $events->sum(fn ($event) => (float) ($event->carbon_emission ?? 0)),
        ];

        return view('admin.events.timeline', compact('batch', 'events', 'summary', 'missingTypes'));
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'batch_id' => ['required', 'exists:batches,id'],
            'event_type' => ['required', Rule::in(TraceabilityEvent::TYPES)],
            'event_date' => ['required', 'date'],
            'location' => ['required', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'actor_id' => ['nullable', 'exists:users,id'],
            'description' => ['nullable', 'string'],
            'quantity' => ['nullable', 'numeric', 'min:0'],
            'temperature' => ['nullable', 'numeric'],
            'distance_km' => ['nullable', 'numeric', 'min:0'],
            'carbon_emission' => ['nullable', 'numeric', 'min:0'],
            'metadata' => ['nullable', 'json'],
        ]);
    }

    private function redirectAfterSave(Request $request, string $message)
    {
        if ($request->filled('batch_id')) {
            return redirect()->route('admin.batches.traceability', $request->integer('batch_id'))->with('success', $message);
        }

        return redirect()->route('admin.events.index')->with('success', $message);
    }
}
