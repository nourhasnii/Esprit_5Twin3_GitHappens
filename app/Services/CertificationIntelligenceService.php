<?php

namespace App\Services;

use App\Models\Certification;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class CertificationIntelligenceService
{
    public const COMPLIANCE_EXCELLENT = 90;
    public const COMPLIANCE_GOOD = 75;
    public const COMPLIANCE_ATTENTION = 55;

    public const RISK_LOW_THRESHOLD = 34;
    public const RISK_MEDIUM_THRESHOLD = 59;

    public function __construct(
        private readonly Carbon $now,
    ) {}

    public function generate(): array
    {
        $expiringWindowDays = 30;

        $allCertifications = Certification::with(['product'])->get();
        $validCerts = $allCertifications->filter(fn (Certification $c) => $c->effective_status === Certification::STATUS_VALID);
        $expiringCerts = $allCertifications->filter(fn (Certification $c) => $c->effective_status === Certification::STATUS_EXPIRING);
        $expiredCerts = $allCertifications->filter(fn (Certification $c) => $c->effective_status === Certification::STATUS_EXPIRED);
        $pendingCerts = $allCertifications->filter(fn (Certification $c) => $c->effective_status === Certification::STATUS_PENDING);

        $productCertCounts = $this->computeValidCertificationCountsByProduct($allCertifications);

        $allProducts = Product::count();
        $productsWithAtLeastOneValidCert = $productCertCounts->filter(fn (int $count) => $count >= 1)->count();
        $uncertifiedProducts = max(0, $allProducts - $productsWithAtLeastOneValidCert);

        $productsAffectedByExpiring = $expiringCerts->pluck('product_id')->unique()->count();
        $productsAffectedByExpired = $expiredCerts->pluck('product_id')->unique()->count();
        $productsAffected = $expiringCerts->merge($expiredCerts)->pluck('product_id')->unique()->count();

        [$complianceScore, $complianceLevel] = $this->computeCompliance(
            $allCertifications->count(),
            $validCerts->count(),
            $expiringCerts->count(),
            $expiredCerts->count(),
            $allProducts,
            $uncertifiedProducts,
        );

        [$riskScore, $riskLevel, $riskFactors] = $this->computeRisk(
            $expiredCerts->count(),
            $expiringCerts->count(),
            $uncertifiedProducts,
            $productsAffected,
            $pendingCerts->count(),
        );

        $expirationAnalysis = $this->computeExpirationAnalysis($allCertifications, $expiringWindowDays);
        $impactAnalysis = $this->computeImpactAnalysis($expiredCerts, $expiringCerts, $uncertifiedProducts, $allProducts);
        $priorityActions = $this->computePriorityActions($expiredCerts, $expiringCerts, $uncertifiedProducts, $productCertCounts);

        $verificationSnapshot = [
            'generated_at' => $this->now->toIso8601String(),
            'window_days' => $expiringWindowDays,
            'total_certifications' => $allCertifications->count(),
            'total_products' => $allProducts,
        ];

        return [
            'snapshot' => $verificationSnapshot,
            'counts' => [
                'valid_certifications' => $validCerts->count(),
                'expiring_soon' => $expiringCerts->count(),
                'expired_certifications' => $expiredCerts->count(),
                'pending_certifications' => $pendingCerts->count(),
                'total_certifications' => $allCertifications->count(),
                'certified_products' => $productsWithAtLeastOneValidCert,
                'uncertified_products' => $uncertifiedProducts,
                'affected_products' => $productsAffected,
                'products_affected_by_expiring' => $productsAffectedByExpiring,
                'products_affected_by_expired' => $productsAffectedByExpired,
            ],
            'compliance' => [
                'score' => $complianceScore,
                'level' => $complianceLevel,
            ],
            'risk' => [
                'score' => $riskScore,
                'level' => $riskLevel,
                'factors' => $riskFactors,
            ],
            'expiration_analysis' => $expirationAnalysis,
            'impact_analysis' => $impactAnalysis,
            'priority_actions' => $priorityActions,
        ];
    }

    /**
     * @return array{0: float, 1: string}
     */
    private function computeCompliance(
        int $totalCerts,
        int $validCerts,
        int $expiringCerts,
        int $expiredCerts,
        int $totalProducts,
        int $uncertifiedProducts,
    ): array {
        if ($totalCerts === 0 && $totalProducts === 0) {
            return [100.0, 'EXCELLENT'];
        }

        $certValidRatio = $totalCerts > 0 ? ($validCerts / $totalCerts) : 1.0;
        $expiredPenalty = $totalCerts > 0 ? ($expiredCerts / $totalCerts) * 30 : 0.0;
        $expiringPenalty = $totalCerts > 0 ? ($expiringCerts / $totalCerts) * 12 : 0.0;
        $productCoverageRatio = $totalProducts > 0 ? (($totalProducts - $uncertifiedProducts) / $totalProducts) : 1.0;

        $score = (
            ($certValidRatio * 0.55) +
            ($productCoverageRatio * 0.45)
        ) * 100 - $expiredPenalty - $expiringPenalty;

        $score = max(0.0, min(100.0, round($score, 1)));

        $level = match (true) {
            $score >= self::COMPLIANCE_EXCELLENT => 'EXCELLENT',
            $score >= self::COMPLIANCE_GOOD => 'GOOD',
            $score >= self::COMPLIANCE_ATTENTION => 'ATTENTION',
            default => 'CRITICAL',
        };

        return [$score, $level];
    }

    /**
     * @return array{0: float, 1: string, 2: list<string>}
     */
    private function computeRisk(
        int $expiredCount,
        int $expiringCount,
        int $uncertifiedProducts,
        int $affectedProducts,
        int $pendingCount,
    ): array {
        $factors = [];
        $score = 0;

        if ($expiredCount > 0) {
            $score += min(40, $expiredCount * 15);
            $factors[] = "{$expiredCount} certification" . ($expiredCount > 1 ? 's are' : ' is') . " expired";
        }

        if ($expiringCount > 0) {
            $score += min(25, $expiringCount * 7);
            $factors[] = "{$expiringCount} certification" . ($expiringCount > 1 ? 's' : '') . " expire within 30 days";
        }

        if ($uncertifiedProducts > 0) {
            $score += min(25, $uncertifiedProducts * 6);
            $factors[] = "{$uncertifiedProducts} product" . ($uncertifiedProducts > 1 ? 's' : '') . " have no valid certification";
        }

        if ($affectedProducts > 0) {
            $score += min(15, $affectedProducts * 2.5);
            $factors[] = "{$affectedProducts} product" . ($affectedProducts > 1 ? 's are' : ' is') . " potentially affected";
        }

        if ($pendingCount > 0) {
            $score += min(10, $pendingCount * 3);
            $factors[] = "{$pendingCount} certification" . ($pendingCount > 1 ? 's' : '') . " awaiting validation";
        }

        if ($score === 0) {
            $factors[] = 'No risk factor detected';
        }

        $score = max(0.0, min(100.0, round($score, 1)));

        $level = match (true) {
            $score <= self::RISK_LOW_THRESHOLD => 'LOW',
            $score <= self::RISK_MEDIUM_THRESHOLD => 'MEDIUM',
            default => 'HIGH',
        };

        return [$score, $level, $factors];
    }

    /**
     * @param Collection<int, Certification> $all
     * @return array<string, mixed>
     */
    private function computeExpirationAnalysis(Collection $all, int $windowDays): array
    {
        $threshold = $this->now->copy()->addDays($windowDays)->startOfDay();
        $today = $this->now->copy()->startOfDay();

        $groups = [
            '0_to_7_days' => 0,
            '8_to_30_days' => 0,
            '31_to_90_days' => 0,
            'over_90_days' => 0,
            'already_expired' => 0,
        ];

        $nearestExpirations = collect();
        $next7DaysTotal = 0;

        foreach ($all as $cert) {
            $expiresAt = $cert->expires_at instanceof Carbon
                ? $cert->expires_at->copy()->startOfDay()
                : Carbon::parse($cert->expires_at)->startOfDay();

            $diffDays = $today->diffInDays($expiresAt, false);

            if ($diffDays < 0) {
                $groups['already_expired']++;
            } elseif ($diffDays <= 7) {
                $groups['0_to_7_days']++;
                $next7DaysTotal++;
            } elseif ($diffDays <= 30) {
                $groups['8_to_30_days']++;
            } elseif ($diffDays <= 90) {
                $groups['31_to_90_days']++;
            } else {
                $groups['over_90_days']++;
            }

            if ($diffDays >= 0 && $expiresAt->lte($threshold)) {
                $nearestExpirations->push([
                    'certification_id' => $cert->id,
                    'name' => $cert->name,
                    'certificate_number' => $cert->certificate_number,
                    'product_id' => $cert->product_id,
                    'product_name' => $cert->product?->name,
                    'expires_at' => $expiresAt->toDateString(),
                    'days_left' => (int) $diffDays,
                ]);
            }
        }

        return [
            'window_days' => $windowDays,
            'groups' => $groups,
            'expiring_in_next_7_days' => $next7DaysTotal,
            'nearest' => $nearestExpirations
                ->sortBy('days_left')
                ->take(10)
                ->values()
                ->all(),
        ];
    }

    /**
     * @param Collection<int, Certification> $expiredCerts
     * @param Collection<int, Certification> $expiringCerts
     * @return array<string, mixed>
     */
    private function computeImpactAnalysis(
        Collection $expiredCerts,
        Collection $expiringCerts,
        int $uncertifiedProducts,
        int $totalProducts,
    ): array {
        $expiredByOrganization = $expiredCerts
            ->groupBy(fn (Certification $c) => $c->issuing_organization)
            ->map(fn (Collection $group) => $group->count())
            ->sortDesc()
            ->all();

        $expiringByOrganization = $expiringCerts
            ->groupBy(fn (Certification $c) => $c->issuing_organization)
            ->map(fn (Collection $group) => $group->count())
            ->sortDesc()
            ->all();

        $topAffectedProducts = $expiredCerts->merge($expiringCerts)
            ->groupBy(fn (Certification $c) => $c->product_id)
            ->map(function (Collection $group, ?int $productId) {
                $firstCert = $group->first();

                return [
                    'product_id' => $productId,
                    'product_name' => $firstCert->product?->name ?? 'Produit inconnu',
                    'certifications_count' => $group->count(),
                    'expired_count' => $group->filter(
                        fn (Certification $c) => $c->effective_status === Certification::STATUS_EXPIRED,
                    )->count(),
                    'expiring_count' => $group->filter(
                        fn (Certification $c) => $c->effective_status === Certification::STATUS_EXPIRING,
                    )->count(),
                ];
            })
            ->sortByDesc('certifications_count')
            ->take(10)
            ->values()
            ->all();

        $coverageRatio = $totalProducts > 0
            ? round((($totalProducts - $uncertifiedProducts) / $totalProducts) * 100, 1)
            : 100.0;

        return [
            'coverage_percent' => $coverageRatio,
            'expired_by_organization' => $expiredByOrganization,
            'expiring_by_organization' => $expiringByOrganization,
            'top_affected_products' => $topAffectedProducts,
        ];
    }

    /**
     * @return array<int, array{priority: string, action: string, reason: string}>
     */
    private function computePriorityActions(
        Collection $expiredCerts,
        Collection $expiringCerts,
        int $uncertifiedProducts,
        Collection $productValidCount,
    ): array {
        $actions = [];

        if ($expiredCerts->count() > 0) {
            $names = $expiredCerts->take(3)->pluck('name')->implode(', ');
            $more = max(0, $expiredCerts->count() - 3);

            $actions[] = [
                'priority' => 'HIGH',
                'action' => 'Renew or archive expired certifications immediately',
                'reason' => "{$expiredCerts->count()} certification(s) already expired: {$names}" . ($more > 0 ? " (+{$more} others)" : ''),
            ];
        }

        if ($expiringCerts->count() > 0) {
            $productsAffected = $expiringCerts->pluck('product_id')->unique()->count();

            $actions[] = [
                'priority' => 'HIGH',
                'action' => 'Launch renewal campaign for certifications expiring within 30 days',
                'reason' => "{$expiringCerts->count()} certification(s) will expire soon, affecting {$productsAffected} product(s)",
            ];
        }

        if ($uncertifiedProducts > 0) {
            $actions[] = [
                'priority' => 'MEDIUM',
                'action' => 'Certify the uncertified product catalogue',
                'reason' => "{$uncertifiedProducts} product(s) have no valid certification",
            ];
        }

        $weakestProducts = $productValidCount
            ->filter(fn (int $count) => $count === 1)
            ->take(5);

        if ($weakestProducts->isNotEmpty()) {
            $actions[] = [
                'priority' => 'MEDIUM',
                'action' => 'Diversify certifications for products depending on only one label',
                'reason' => "{$weakestProducts->count()} product(s) have a single active certification — a single expiration creates compliance risk",
            ];
        }

        if (count($actions) === 0) {
            $actions[] = [
                'priority' => 'LOW',
                'action' => 'Maintain current compliance program',
                'reason' => 'No critical or medium risk detected on this snapshot',
            ];
        }

        $priorityOrder = ['HIGH' => 0, 'MEDIUM' => 1, 'LOW' => 2];
        usort($actions, static fn (array $a, array $b) => $priorityOrder[$a['priority']] <=> $priorityOrder[$b['priority']]);

        return $actions;
    }

    /**
     * @param Collection<int, Certification> $allCerts
     * @return Collection<int, int> (keyed by product_id → valid cert count)
     */
    private function computeValidCertificationCountsByProduct(Collection $allCerts): Collection
    {
        return $allCerts
            ->filter(fn (Certification $c) => $c->effective_status === Certification::STATUS_VALID)
            ->groupBy('product_id')
            ->map(fn (Collection $group) => $group->count());
    }
}
