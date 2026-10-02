<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\AnalyzeProductImageJob;
use App\Models\Product;
use App\Models\QualityCheck;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class QualityCheckController extends Controller
{
    public function index()
    {
        $checks = QualityCheck::with(['product', 'creator'])
            ->latest()
            ->paginate(12);

        return view('admin.quality-checks.index', compact('checks'));
    }

    public function create()
    {
        $products = Product::orderBy('name')->get();
        return view('admin.quality-checks.create', compact('products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'image'      => 'required|image|max:5120', // max 5MB
        ]);

        // Stocker l'image
        $path = $request->file('image')->store('quality-checks', 'public');

        // Créer l'enregistrement
        $check = QualityCheck::create([
            'product_id'  => $request->product_id,
            'image_path'  => $path,
            'status'      => 'pending',
            'created_by'  => $request->user()->id,        ]);

        // Lancer l'analyse en arrière-plan
        AnalyzeProductImageJob::dispatch($check);

        return redirect()
            ->route('admin.quality-checks.show', $check)
            ->with('success', 'Analyse lancée. Le résultat arrivera dans quelques instants.');
    }

    public function show(QualityCheck $qualityCheck)
    {
        $qualityCheck->load(['product', 'creator']);
        return view('admin.quality-checks.show', compact('qualityCheck'));
    }

    public function destroy(QualityCheck $qualityCheck)
    {
        if ($qualityCheck->image_path) {
            Storage::disk('public')->delete($qualityCheck->image_path);
        }

        $qualityCheck->delete();

        return redirect()
            ->route('admin.quality-checks.index')
            ->with('success', 'Analyse supprimée.');
    }
}