<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Models\Category;
use App\Models\Product;

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

    public function store(StoreCategoryRequest $request)
    {
        $this->authorize('manage_products', Product::class);

        Category::create($request->validated());

        return redirect()->route('admin.categories.index')->with('success', 'Catégorie créée avec succès.');
    }

    public function edit(Category $category)
    {
        $this->authorize('manage_products', Product::class);

        return view('admin.categories.edit', compact('category'));
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $this->authorize('manage_products', Product::class);

        $category->update($request->validated());

        return redirect()->route('admin.categories.index')->with('success', 'Catégorie mise à jour avec succès.');
    }

    public function destroy(Category $category)
    {
        $this->authorize('manage_products', Product::class);

        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', 'Catégorie supprimée. Les produits associés conservent leur catégorie historique.');
    }
}
