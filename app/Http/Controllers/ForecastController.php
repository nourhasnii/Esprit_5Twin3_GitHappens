<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Site;
use App\Models\StockMovement;
use App\Services\Forecast\DemandForecaster;
use App\Services\Stock\StockService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Écran « Prévisions » : demande prévue d'un produit sur un site, expliquée étape par étape. */
class ForecastController extends Controller
{
    public function __construct(
        private readonly DemandForecaster $forecaster,
        private readonly StockService $stocks,
    ) {}

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'site_id' => ['nullable', 'integer'],
            'product_id' => ['nullable', 'integer'],
            'from' => ['nullable', 'date'],
            'days' => ['nullable', 'integer', 'in:7,14,30'],
        ]);

        $sites = Site::query()->active()->logistics()->orderBy('name')->get();
        $products = Product::query()->orderBy('name')->get();

        // Par défaut : le couple site/produit qui a le plus de ventes enregistrées
        $default = StockMovement::query()->sales()
            ->whereIn('source_site_id', $sites->modelKeys())
            ->selectRaw('source_site_id, product_id, count(*) as n')
            ->groupBy('source_site_id', 'product_id')
            ->orderByDesc('n')
            ->first();

        $siteId = (int) ($filters['site_id'] ?? $default?->source_site_id ?? $sites->first()?->getKey());
        $productId = (int) ($filters['product_id'] ?? $default?->product_id ?? $products->first()?->getKey());
        $today = CarbonImmutable::now()->startOfDay();
        $from = isset($filters['from']) ? CarbonImmutable::parse($filters['from'])->startOfDay() : $today;
        $days = (int) ($filters['days'] ?? config('stock.forecast.horizon_days', 14));

        $forecast = $siteId && $productId ? $this->forecaster->forecast($siteId, $productId, $from, $days) : null;
        $available = $siteId && $productId ? $this->stocks->availableAt($siteId, $productId) : 0.0;

        return view('forecast.index', [
            'sites' => $sites,
            'products' => $products,
            'site' => $sites->firstWhere('id', $siteId),
            'product' => $products->firstWhere('id', $productId),
            'forecast' => $forecast,
            'from' => $from,
            'days' => $days,
            'isSimulation' => $from->gt($today),
            'available' => $available,
            'stockout' => $forecast && ! $from->gt($today) ? $this->forecaster->stockout($siteId, $productId, $available) : null,
            'shortcuts' => $this->upcomingEvents($today),
        ]);
    }

    /** Prochaines périodes du calendrier, pour simuler la demande à ces dates en un clic. */
    private function upcomingEvents(CarbonImmutable $today): array
    {
        $shortcuts = [];

        foreach (config('stock.forecast.events', []) as $event) {
            $starts = [];

            foreach ($event['periods'] ?? [] as [$start]) {
                $starts[] = CarbonImmutable::parse($start)->startOfDay();
            }

            if (isset($event['recurring'])) {
                [$m, $d] = explode('-', $event['recurring'][0]);
                $starts[] = $today->setDate($today->year, (int) $m, (int) $d);
                $starts[] = $today->setDate($today->year + 1, (int) $m, (int) $d);
            }

            $next = collect($starts)->filter(fn ($s) => $s->gt($today))->sort()->first();

            if ($next) {
                $shortcuts[] = ['label' => $event['label'], 'from' => $next->toDateString(), 'date' => $next];
            }
        }

        return collect($shortcuts)->sortBy('from')->take(4)->values()->all();
    }
}
