<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        $products = Product::with('producer')
            ->latest()
            ->paginate(12);

        return view('front.products.index', compact('products'));
    }

    public function show(Product $product): View
    {
        $product->load(['producer', 'batches', 'certifications']);

        return view('front.products.show', compact('product'));
    }
}
