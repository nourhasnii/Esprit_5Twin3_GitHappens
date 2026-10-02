<?php

namespace App\Http\Controllers;

use Carbon\CarbonImmutable;
use App\Http\Requests\StoreStockRequest;
use App\Http\Requests\UpdateStockRequest;
use App\Models\Batch;
use App\Models\Product;
use App\Models\Site;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Services\Forecast\DemandForecaster;
use App\Services\Stock\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockController extends Controller
{
    public function __construct(
        private readonly StockService $stocks,
        private readonly DemandForecaster $forecaster,
    ) {}

    /** Vue globale : stocks regroupés par site, alertes de rupture, indicateurs. */
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'site_id' => ['nullable', 'integer'],
            'product_id' => ['nullable', 'integer'],
            'state' => ['nullable', 'in:ok,low,expiring,expired,recalled'],
        ]);

        $sites = Site::query()
            ->active()
            ->logistics()
            ->withSum('stocks', 'quantity')
            ->when($filters['site_id'] ?? null, fn ($q, $id) => $q->whereKey($id))
            ->orderBy('name')
            ->get();

        $low = $this->stocks->lowStock();
        $lowKeys = $low->pluck('key')->flip();

        $lines = Stock::query()
            ->with(['site', 'product', 'batch'])
            ->whereIn('site_id', $sites->modelKeys())
            ->when($filters['product_id'] ?? null, fn ($q, $id) => $q->where('product_id', $id))
            ->orderBy('product_id')
            ->orderByDesc('quantity')
            ->get()
            ->each(fn (Stock $line) => $line->setAttribute(
                'display_state',
                $line->state($lowKeys->has($line->site_id.'-'.$line->product_id))
            ));

        // Ruptures prévues dans les 7 jours, d'après la prévision de la demande
        $predicted = collect();
        if (config('stock.forecast.enabled', true)) {
            $predicted = $lines
                ->unique(fn (Stock $line) => $line->site_id.'-'.$line->product_id)
                ->map(function (Stock $line) {
                    $available = $this->stocks->availableAt($line->site_id, $line->product_id);
                    $stockout = $available > 0 ? $this->forecaster->stockout($line->site_id, $line->product_id, $available, 7) : null;

                    return $stockout ? (object) [
                        'site' => $line->site,
                        'product' => $line->product,
                        'available' => $available,
                        'days' => $stockout['days'],
                        'date' => CarbonImmutable::parse($stockout['date']),
                        'daily' => $this->forecaster->averageDaily($line->site_id, $line->product_id, now(), 7),
                    ] : null;
                })
                ->filter()
                ->sortBy('days')
                ->values();
        }

        if ($state = $filters['state'] ?? null) {
            $lines = $lines->where('display_state', $state)->values();
        }

        $kpis = [
            'sites' => $sites->count(),
            'quantity' => $lines->sum('quantity'),
            'low' => $low->count(),
            'expiring' => $lines->whereIn('display_state', ['expiring', 'expired'])->count(),
        ];

        return view('stocks.index', [
            'sites' => $sites,
            'linesBySite' => $lines->groupBy('site_id'),
            'low' => $low,
            'predicted' => $predicted,
            'kpis' => $kpis,
            'filters' => $filters,
            'isFiltered' => array_filter($filters) !== [],
            'products' => Product::query()->orderBy('name')->get(['id', 'name']),
            'allSites' => Site::query()->active()->logistics()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function create(Request $request): View
    {
        return view('stocks.create', [
            'sites' => Site::query()->active()->logistics()->orderBy('name')->get(),
            'products' => Product::query()->orderBy('name')->get(['id', 'name']),
            'batches' => Batch::query()->with('product')->latest()->get(),
            'selectedSite' => $request->integer('site_id') ?: null,
        ]);
    }

    public function store(StoreStockRequest $request): RedirectResponse
    {
        $line = $this->stocks->openLine($request->validated(), $request->user());

        return redirect()->route('stocks.show', $line)->with('success', 'Ligne de stock créée.');
    }

    public function show(Stock $stock): View
    {
        $stock->load(['site', 'product', 'batch']);

        $movements = StockMovement::query()
            ->with(['sourceSite', 'destinationSite', 'user'])
            ->where('product_id', $stock->product_id)
            ->forBatch($stock->batch_id)
            ->where(fn ($q) => $q
                ->where('source_site_id', $stock->site_id)
                ->orWhere('destination_site_id', $stock->site_id))
            ->latest('moved_at')
            ->paginate(20);

        $window = (int) config('stock.optimization.demand_window_days', 30);

        return view('stocks.show', [
            'stock' => $stock,
            'movements' => $movements,
            'window' => $window,
            'avgDaily' => $this->stocks->averageDailyConsumption($stock->site_id, $stock->product_id, $window),
            'productAvailable' => $this->stocks->availableAt($stock->site_id, $stock->product_id),
        ]);
    }

    public function edit(Stock $stock): View
    {
        $stock->load(['site', 'product', 'batch']);

        return view('stocks.edit', [
            'stock' => $stock,
            'productAvailable' => $this->stocks->availableAt($stock->site_id, $stock->product_id),
        ]);
    }

    public function update(UpdateStockRequest $request, Stock $stock): RedirectResponse
    {
        $this->stocks->setThreshold($stock->site_id, $stock->product_id, (float) $request->validated('min_threshold'));

        return redirect()->route('stocks.index', ['site_id' => $stock->site_id])
            ->with('success', 'Seuil de '.$stock->product?->name.' mis à jour sur '.$stock->site?->name.'.');
    }

    public function destroy(Stock $stock): RedirectResponse
    {
        if ($stock->quantity > 0) {
            return back()->with('error', 'Cette ligne contient encore du stock : enregistrez une sortie ou un ajustement à zéro avant de la supprimer.');
        }

        $stock->delete();

        return redirect()->route('stocks.index')->with('success', 'Ligne de stock supprimée.');
    }
}
