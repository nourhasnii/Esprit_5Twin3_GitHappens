<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Certification;
use App\Models\Product;
use App\Models\TraceabilityEvent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if ($user->hasRole('consommateur')) {
            return redirect()->route('front.products.index');
        }

        if (! $user->hasRole('admin') && ! $user->isActive()) {
            return view('account.pending', compact('user'));
        }

        $productsTotal = Product::count();
        $activeBatches = Batch::where('status', 'active')->count();
        $certificationTotal = Certification::count();
        $inTransitBatches = Batch::where('status', 'in_transit')->count();
        $alertCount = Product::whereIn('verification_status', ['rejected', 'flagged'])->count()
            + Batch::where(function ($query) {
                $query->whereIn('status', ['expired', 'recalled'])
                    ->orWhereBetween('expiration_date', [today(), today()->addDays(30)]);
            })->count()
            + Certification::withEffectiveStatus(Certification::STATUS_EXPIRED)->count();

        $greetingName = explode(' ', trim($user->name))[0] ?? $user->name;

        $kpis = [
            ['label' => 'Produits',       'value' => $productsTotal,    'bg' => 'bg-[#EEF3EC]', 'text' => 'text-[#5B8A5F]', 'icon' => 'product'],
            ['label' => 'Lots actifs',    'value' => $activeBatches,    'bg' => 'bg-[#FBEFE0]', 'text' => 'text-[#C97F2A]', 'icon' => 'batch'],
            ['label' => 'Certifications', 'value' => $certificationTotal, 'bg' => 'bg-[#EEF3EC]', 'text' => 'text-[#5B8A5F]', 'icon' => 'certification'],
            ['label' => 'Alertes',        'value' => $alertCount,       'bg' => 'bg-[#F5E7E2]', 'text' => 'text-[#C1583B]', 'icon' => 'alert'],
            ['label' => 'En transit',     'value' => $inTransitBatches, 'bg' => 'bg-[#E9F1F4]', 'text' => 'text-[#4E7C8C]', 'icon' => 'transit'],
        ];

        $recentBatches = Batch::with('product')
            ->latest()
            ->limit(6)
            ->get()
            ->map(function (Batch $batch) use ($user) {
                $status = $this->resolveBatchStatus($batch);

                return [
                    'id' => $batch->id,
                    'lot_number' => $batch->lot_number,
                    'product_name' => $batch->product?->name ?? '-',
                    'status_key' => $status['key'],
                    'status_label' => $status['label'],
                    'produced_at' => $batch->production_date?->isoFormat('DD MMM YYYY') ?? '-',
                    'show_url' => app('router')->has('admin.batches.show') && $user->can('manage_products')
                        ? route('admin.batches.show', $batch)
                        : '#',
                ];
            });

        $lastDecisions = $this->buildLastDecisions($user);
        $toReview = $this->buildToReview($user);

        return view('dashboard', compact(
            'user',
            'greetingName',
            'alertCount',
            'kpis',
            'recentBatches',
            'lastDecisions',
            'toReview',
        ));
    }

    private function resolveBatchStatus(Batch $batch): array
    {
        $status = mb_strtolower((string) $batch->status);

        return match ($status) {
            'recalled', 'rejected'          => ['key' => 'red',    'label' => 'Retiré'],
            'sold', 'completed', 'delivered', 'validated' => ['key' => 'green', 'label' => 'Vendu'],
            'in_transit', 'shipping', 'transit'          => ['key' => 'blue',   'label' => 'En transport'],
            'pending', 'waiting', 'quarantine'           => ['key' => 'orange', 'label' => 'En attente'],
            default                                       => ['key' => 'orange', 'label' => 'En attente'],
        };
    }

    private function buildLastDecisions($user): array
    {
        $sampleDecisions = Batch::with('product')
            ->latest()
            ->limit(5)
            ->get()
            ->map(function (Batch $batch, $index) {
                $decisionPool = [
                    ['type' => 'Vente',   'pill' => 'bg-green-100 text-green-700'],
                    ['type' => 'Retrait', 'pill' => 'bg-red-100 text-red-700'],
                    ['type' => 'Don',     'pill' => 'bg-blue-100 text-blue-700'],
                ];
                $d = $decisionPool[$index % 3];

                return [
                    'thumbnail' => $batch->product?->name ? mb_substr($batch->product->name, 0, 1) : '?',
                    'label' => sprintf('%s · %s', $batch->product?->name ?? 'Produit', $batch->lot_number),
                    'meta'  => $batch->created_at?->diffForHumans(['short' => true]) ?? '-',
                    'decision_type' => $d['type'],
                    'decision_pill' => $d['pill'],
                ];
            })
            ->values()
            ->all();

        if (count($sampleDecisions) >= 4) {
            return $sampleDecisions;
        }

        $fallback = [
            ['thumbnail' => 'T', 'label' => 'Tomates Roma · LOT-A2401', 'meta' => 'Il y a 2j', 'decision_type' => 'Vente',   'decision_pill' => 'bg-green-100 text-green-700'],
            ['thumbnail' => 'L', 'label' => 'Laitue Bio · LOT-A2398',   'meta' => 'Il y a 3j', 'decision_type' => 'Don',     'decision_pill' => 'bg-blue-100 text-blue-700'],
            ['thumbnail' => 'P', 'label' => 'Poivrons · LOT-A2395',     'meta' => 'Il y a 4j', 'decision_type' => 'Retrait', 'decision_pill' => 'bg-red-100 text-red-700'],
            ['thumbnail' => 'C', 'label' => 'Carottes · LOT-A2392',     'meta' => 'Il y a 5j', 'decision_type' => 'Vente',   'decision_pill' => 'bg-green-100 text-green-700'],
        ];

        return array_merge($sampleDecisions, array_slice($fallback, 0, 4 - count($sampleDecisions)));
    }

    private function buildToReview($user): array
    {
        $review = Certification::with('product')
            ->whereIn('status', [Certification::STATUS_PENDING])
            ->orWhere(function ($q) {
                $q->where('status', '!=', Certification::STATUS_PENDING)
                  ->whereBetween('expires_at', [today(), today()->addDays(14)]);
            })
            ->limit(3)
            ->get()
            ->map(fn (Certification $cert) => [
                'title' => $cert->certificate_number,
                'meta'  => sprintf('%s · expire le %s', $cert->product?->name ?? 'Produit', $cert->expires_at?->isoFormat('DD MMM YYYY') ?? 'N/A'),
                'review_url' => app('router')->has('admin.certifications.show')
                    ? route('admin.certifications.show', $cert)
                    : '#',
            ])
            ->values()
            ->all();

        if (count($review) >= 2) {
            return $review;
        }

        $fallback = [
            ['title' => 'CERT-ORG-2025-0041', 'meta'  => 'Pommes Golden · expire le 12 oct 2026', 'review_url' => '#'],
            ['title' => 'CERT-HACCP-0012',    'meta'  => 'Fromagerie · expire le 02 nov 2026',    'review_url' => '#'],
        ];

        return array_merge($review, array_slice($fallback, 0, 3 - count($review)));
    }
}
