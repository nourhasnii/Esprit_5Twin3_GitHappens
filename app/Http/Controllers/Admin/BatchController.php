<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Product;
use App\Services\BatchQrCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class BatchController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $batches = Batch::with('product')
            ->when($request->filled('search'), fn ($query) => $query->where('lot_number', 'like', '%' . $request->string('search') . '%'))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('product_id'), fn ($query) => $query->where('product_id', $request->integer('product_id')))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $products = Product::orderBy('name')->get(['id', 'name']);

        return view('admin.batches.index', compact('batches', 'products'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $products = Product::orderBy('name')->get(['id', 'name', 'category', 'unit']);

        return view('admin.batches.create', compact('products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Batch::create($this->validatedData($request));

        return redirect()->route('admin.batches.index')->with('success', 'Lot créé avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Batch $batch)
    {
        $batch->load('product');
        $qrCodeExists = $batch->qr_code_path && Storage::disk('public')->exists($batch->qr_code_path);

        return view('admin.batches.show', compact('batch', 'qrCodeExists'));
    }

    public function downloadQr(Batch $batch)
    {
        $disk = Storage::disk('public');

        if (! $batch->qr_code_path || ! $disk->exists($batch->qr_code_path)) {
            return redirect()->route('admin.batches.show', $batch)->with('error', 'QR code is not available.');
        }

        $extension = pathinfo($batch->qr_code_path, PATHINFO_EXTENSION);

        return response()->download($disk->path($batch->qr_code_path), 'batch-' . $batch->lot_number . '-qr.' . $extension);
    }

    public function regenerateQr(Batch $batch, BatchQrCodeService $qrCodeService)
    {
        $qrCodeService->generate($batch);

        return redirect()->route('admin.batches.show', $batch)->with('success', 'QR code regenerated.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Batch $batch)
    {
        $products = Product::orderBy('name')->get(['id', 'name', 'category', 'unit']);

        return view('admin.batches.edit', compact('batch', 'products'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Batch $batch)
    {
        $batch->update($this->validatedData($request, $batch));

        return redirect()->route('admin.batches.index')->with('success', 'Lot mis à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Batch $batch)
    {
        $batch->delete();

        return redirect()->route('admin.batches.index')->with('success', 'Lot supprimé avec succès.');
    }

    private function validatedData(Request $request, ?Batch $batch = null): array
    {
        return $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'lot_number' => ['required', 'string', 'max:255', Rule::unique('batches', 'lot_number')->ignore($batch?->id)],
            'production_date' => ['required', 'date'],
            'expiration_date' => ['required', 'date', 'after:production_date'],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'unit' => ['required', 'string', 'max:50'],
            'carbon_footprint' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(['active', 'expired', 'recalled'])],
        ]);
    }
}
