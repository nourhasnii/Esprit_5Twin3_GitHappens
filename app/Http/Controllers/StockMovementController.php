<?php

namespace App\Http\Controllers;

use App\Enums\StockMovementType;
use App\Http\Requests\StoreStockMovementRequest;
use App\Models\Batch;
use App\Models\Product;
use App\Models\Site;
use App\Models\StockMovement;
use App\Services\Stock\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StockMovementController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'type' => ['nullable', Rule::enum(StockMovementType::class)],
            'site_id' => ['nullable', 'integer'],
            'product_id' => ['nullable', 'integer'],
            'batch_id' => ['nullable', 'integer'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ]);

        $movements = StockMovement::query()
            ->with(['product', 'batch', 'sourceSite', 'destinationSite', 'user'])
            ->filter($filters)
            ->latest('moved_at')
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        $totals = StockMovement::query()
            ->filter($filters)
            ->toBase()
            ->selectRaw('type, COUNT(*) as movements, SUM(quantity) as quantity')
            ->groupBy('type')
            ->get()
            ->keyBy('type');

        return view('stock-movements.index', [
            'movements' => $movements,
            'totals' => $totals,
            'filters' => $filters,
            'sites' => Site::query()->logistics()->orderBy('name')->get(['id', 'name']),
            'products' => Product::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function create(Request $request): View
    {
        return view('stock-movements.create', [
            'sites' => Site::query()->active()->logistics()->orderBy('name')->get(),
            'products' => Product::query()->orderBy('name')->get(['id', 'name']),
            'batches' => Batch::query()->with('product')->latest()->get(),
            'defaultType' => StockMovementType::tryFrom((string) $request->query('type')) ?? StockMovementType::In,
        ]);
    }

    public function store(StoreStockMovementRequest $request, StockService $stocks): RedirectResponse
    {
        $movement = $stocks->record($request->validated(), $request->user());

        return redirect()->route('stock-movements.index')
            ->with('success', 'Mouvement '.$movement->reference.' enregistré ('.mb_strtolower($movement->type->label()).').');
    }
}
