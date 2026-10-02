<?php

namespace App\Services;

use App\Models\Product;

class QualityScoringService
{
    public function calculate(array $visionResult, ?Product $product = null): array
    {
        // 1. Score de fraîcheur venant de la Vision
        $freshness = (float) ($visionResult['freshness_estimate'] ?? 50);

        // 2. Pénalité selon les défauts détectés
        $defects = $visionResult['defects'] ?? [];
        $defectPenalty = min(count($defects) * 8, 30); // max -30 points

        // 3. Score DLC (si le produit a une date d'expiration)
        $dlcScore = 70; // valeur par défaut
        // Tu pourras améliorer ça plus tard avec la vraie DLC du Batch

        // 4. Bonus certifications
        $certBonus = 0;
        if ($product) {
            if ($product->is_organic) $certBonus += 5;
            if ($product->is_local) $certBonus += 3;
            if ($product->is_fair_trade) $certBonus += 2;
        }

        // 5. Pénalité CO₂ (légère)
        $co2Penalty = 0;
        if ($product && $product->carbon_footprint) {
            if ($product->carbon_footprint > 5) $co2Penalty = 5;
            elseif ($product->carbon_footprint > 2) $co2Penalty = 2;
        }

        // Formule finale
        $qualityScore = ($freshness * 0.55)
                      + ($dlcScore * 0.25)
                      + $certBonus
                      - $defectPenalty
                      - $co2Penalty;

        // Borner entre 0 et 100
        $qualityScore = max(0, min(100, round($qualityScore, 2)));

        return [
            'freshness_score' => round($freshness, 2),
            'quality_score'   => $qualityScore,
            'defect_penalty'  => $defectPenalty,
            'cert_bonus'      => $certBonus,
            'co2_penalty'     => $co2Penalty,
        ];
    }
}