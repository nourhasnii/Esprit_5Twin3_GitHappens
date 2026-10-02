<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        $this->authorize('manage_products', Product::class);

        $categories = Category::withCount('products')->orderBy('name')->paginate(15);

        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        $this->authorize('manage_products', Product::class);

        return view('admin.categories.create');
    }

    public function store(Request $request)
    {
        $this->authorize('manage_products', Product::class);

        Category::create($this->validatedData($request));

        return redirect()->route('admin.categories.index')->with('success', 'Catégorie créée avec succès.');
    }

    public function edit(Category $category)
    {
        $this->authorize('manage_products', Product::class);

        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $this->authorize('manage_products', Product::class);

        $category->update($this->validatedData($request, $category));

        return redirect()->route('admin.categories.index')->with('success', 'Catégorie mise à jour avec succès.');
    }

    public function destroy(Category $category)
    {
        $this->authorize('manage_products', Product::class);

        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', 'Catégorie supprimée. Les produits associés conservent leur catégorie historique.');
    }

    private function validatedData(Request $request, ?Category $category = null): array
    {
        $input = $request->all();
        $input['slug'] = Str::slug((string) ($input['name'] ?? ''));

        $validated = Validator::make($input, [
            'name' => ['required', 'string', 'max:255', Rule::unique('categories', 'name')->ignore($category?->id)],
            'slug' => ['required', 'string', 'max:255', Rule::unique('categories', 'slug')->ignore($category?->id)],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ])->validate();

        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }
}
