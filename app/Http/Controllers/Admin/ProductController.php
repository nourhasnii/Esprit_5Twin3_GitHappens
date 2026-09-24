<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('producer')->latest()->paginate(10);

        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        $producers = User::orderBy('name')->get(['id', 'name']);

        return view('admin.products.create', compact('producers'));
    }

    public function store(Request $request)
    {
        Product::create($this->validatedData($request));

        return redirect()->route('admin.products.index')
            ->with('success', 'Produit créé avec succès.');
    }

    public function show(Product $product)
    {
        $product->load('batches', 'producer');

        return view('admin.products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $producers = User::orderBy('name')->get(['id', 'name']);

        return view('admin.products.edit', compact('product', 'producers'));
    }

    public function update(Request $request, Product $product)
    {
        $product->update($this->validatedData($request, $product));

        return redirect()->route('admin.products.index')
            ->with('success', 'Produit mis à jour avec succès.');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', 'Produit supprimé avec succès.');
    }

    private function validatedData(Request $request, ?Product $product = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category' => ['required', 'string', 'max:100'],
            'origin_country' => ['required', 'string', 'max:100'],
            'origin_region' => ['nullable', 'string', 'max:150'],
            'producer_id' => ['required', 'exists:users,id'],
            'unit' => ['required', 'string', 'max:50'],
            'image' => ['nullable', 'string', 'max:2048'],
            'barcode' => ['nullable', 'string', 'max:255', 'unique:products,barcode,' . ($product?->id ?? 'NULL')],
            'is_organic' => ['nullable', 'boolean'],
            'carbon_footprint' => ['nullable', 'numeric', 'min:0'],
            'verification_status' => ['required', 'in:pending,verified,rejected'],
        ]);

        $validated['is_organic'] = $request->boolean('is_organic');

        return $validated;
    }
}
