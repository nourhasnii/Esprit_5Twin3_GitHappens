<?php

namespace App\Services;

use App\Models\Product;

class DecisionEngineService
{
    public function decide(array $scores, array $visionResult, ?Product $product = null): array
    {
        $qualityScore = (float) ($scores['quality_score'] ?? 50);
        $freshness    = (float) ($scores['freshness_score'] ?? 50);
        $defects      = $visionResult['defects'] ?? [];
        $condition    = $visionResult['condition'] ?? 'average';
        $confidence   = (float) ($visionResult['confidence'] ?? 0.7);

        $factors = [];

        // === Règles de décision ===

        // 1. Cas critique → WITHDRAW
        if ($qualityScore < 40 || $condition === 'poor' || $this->hasCriticalDefect($defects)) {
            $decision = 'withdraw';
            $factors[] = "Score qualité bas ({$qualityScore}/100)";
            if ($condition === 'poor') $factors[] = "État du produit : mauvais";
            if ($this->hasCriticalDefect($defects)) $factors[] = "Défaut critique détecté : " . implode(', ', $defects);
        }
        // 2. Score moyen → DONATE
        elseif ($qualityScore < 65) {
            $decision = 'donate';
            $factors[] = "Score qualité moyen ({$qualityScore}/100)";
            $factors[] = "Produit encore consommable mais non optimal pour la vente";
        }
        // 3. Confiance Vision trop faible → INSPECT
        elseif ($confidence < 0.55) {
            $decision = 'inspect';
            $factors[] = "Confiance de l'analyse Vision faible (" . round($confidence * 100) . "%)";
            $factors[] = "Un contrôle manuel est recommandé";
        }
        // 4. Bon score → SELL
        else {
            $decision = 'sell';
            $factors[] = "Score qualité élevé ({$qualityScore}/100)";
            $factors[] = "Aucun défaut critique détecté";
            if (!empty($defects)) {
                $factors[] = "Défauts mineurs : " . implode(', ', $defects);
            }
        }

        // Ajuster la confiance finale
        $finalConfidence = round(min(99, max(40, $confidence * 100 + ($qualityScore - 50) * 0.2)), 1);

        // Générer l'explication
        $explanation = $this->buildExplanation($decision, $finalConfidence, $factors, $qualityScore);

        return [
            'decision'    => $decision,
            'confidence'  => $finalConfidence,
            'explanation' => $explanation,
            'factors'     => $factors,
        ];
    }

    private function hasCriticalDefect(array $defects): bool
    {
        $criticalKeywords = ['mold', 'moisissure', 'rotten', 'pourri', 'spoil', 'avarié', 'fungus', 'bacteria'];

        foreach ($defects as $defect) {
            $defectLower = strtolower($defect);
            foreach ($criticalKeywords as $keyword) {
                if (str_contains($defectLower, $keyword)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function buildExplanation(string $decision, float $confidence, array $factors, float $score): string
    {
        $labels = [
            'sell'     => 'VENTE',
            'donate'   => 'DON',
            'withdraw' => 'RETRAIT',
            'inspect'  => 'CONTRÔLE MANUEL',
        ];

        $label = $labels[$decision] ?? strtoupper($decision);

        $text = "Recommandation : {$label} (confiance {$confidence}%). ";
        $text .= "Score de qualité : {$score}/100. ";
        $text .= "Raisons : " . implode(' · ', $factors) . ".";

        return $text;
    }
}