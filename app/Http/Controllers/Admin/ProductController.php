<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->query('q');
        $search = is_string($query) ? trim($query) : '';
        $categoryId = filter_var($request->query('category_id'), FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1],
        ]) ?: null;
        $certificationFilter = $request->query('certification');
        $certification = in_array($certificationFilter, ['with', 'without'], true)
            ? $certificationFilter
            : 'all';
        $organic = in_array($request->query('organic'), ['organic', 'non_organic'], true)
            ? $request->query('organic')
            : 'all';
        $verificationStatus = in_array($request->query('verification_status'), ['pending', 'verified', 'rejected'], true)
            ? $request->query('verification_status')
            : 'all';

        $products = Product::query()
            ->with(['producer', 'categoryModel', 'certifications'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%");
                });
            })
            ->when($categoryId, fn ($query) => $query->where('category_id', $categoryId))
            ->when($certification === 'with', fn ($query) => $query->whereHas('certifications'))
            ->when($certification === 'without', fn ($query) => $query->whereDoesntHave('certifications'))
            ->when($organic === 'organic', fn ($query) => $query->where('is_organic', true))
            ->when($organic === 'non_organic', fn ($query) => $query->where('is_organic', false))
            ->when($verificationStatus !== 'all', fn ($query) => $query->where('verification_status', $verificationStatus))
            ->latest()
            ->paginate(10)
            ->withQueryString();
        $categories = Category::orderBy('name')->get(['id', 'name']);

        return view('admin.products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $producers = User::orderBy('name')->get(['id', 'name']);
        $categories = Category::where('is_active', true)->orderBy('name')->get();

        return view('admin.products.create', compact('producers', 'categories'));
    }

    public function store(Request $request)
    {
        Product::create($this->validatedData($request));

        return redirect()->route('admin.products.index')
            ->with('success', 'Produit créé avec succès.');
    }

    public function show(Product $product)
    {
        $product->load('batches', 'producer', 'categoryModel');

        return view('admin.products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $producers = User::orderBy('name')->get(['id', 'name']);
        $categories = Category::query()
            ->where('is_active', true)
            ->when($product->category_id, fn ($query) => $query->orWhereKey($product->category_id))
            ->orderBy('name')
            ->get();

        return view('admin.products.edit', compact('product', 'producers', 'categories'));
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
            'category_id' => [
                'nullable',
                Rule::exists('categories', 'id')->where(function ($query) use ($product) {
                    $query->where('is_active', true);

                    if ($product?->category_id) {
                        $query->orWhere('id', $product->category_id);
                    }
                }),
            ],
            'category' => ['nullable', 'string', 'max:100'],
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

        if (! empty($validated['category_id'])) {
            $validated['category'] = Category::whereKey($validated['category_id'])->value('name');
        } else {
            $validated['category'] = $validated['category'] ?? $product?->category ?? 'Autres';
        }

        return $validated;
    }
}
