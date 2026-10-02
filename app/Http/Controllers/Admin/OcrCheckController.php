<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\AnalyzeLabelOcrJob;
use App\Models\Batch;
use App\Models\OcrCheck;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OcrCheckController extends Controller
{
    public function index()
    {
        $this->authorize('manage_products', Product::class);

        $checks = OcrCheck::with(['batch', 'product', 'creator'])
            ->latest()
            ->paginate(15);

        return view('admin.ocr-checks.index', compact('checks'));
    }

    public function create()
    {
        $this->authorize('manage_products', Product::class);

        $batches = Batch::with('product')->latest()->get();

        return view('admin.ocr-checks.create', compact('batches'));
    }

    public function store(Request $request)
    {
        $this->authorize('manage_products', Product::class);

        $validated = $request->validate([
            'batch_id' => ['required', 'exists:batches,id'],
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $batch = Batch::findOrFail($validated['batch_id']);
        $imagePath = $request->file('image')->store('ocr-labels', 'public');
        $check = OcrCheck::create([
            'batch_id' => $batch->id,
            'product_id' => $batch->product_id,
            'image_path' => $imagePath,
            'status' => 'pending',
            'created_by' => $request->user()->id,
        ]);

        AnalyzeLabelOcrJob::dispatch($check);

        return redirect()
            ->route('admin.ocr-checks.show', $check)
            ->with('success', 'Analyse lancée. Le résultat sera disponible après le traitement en arrière-plan.');
    }

    public function show(OcrCheck $ocrCheck)
    {
        $this->authorize('manage_products', Product::class);
        $ocrCheck->load(['batch', 'product', 'creator']);

        return view('admin.ocr-checks.show', compact('ocrCheck'));
    }

    public function destroy(OcrCheck $ocrCheck)
    {
        $this->authorize('manage_products', Product::class);

        if ($ocrCheck->image_path) {
            Storage::disk('public')->delete($ocrCheck->image_path);
        }

        $ocrCheck->delete();

        return redirect()
            ->route('admin.ocr-checks.index')
            ->with('success', 'Analyse OCR supprimée.');
    }
}
