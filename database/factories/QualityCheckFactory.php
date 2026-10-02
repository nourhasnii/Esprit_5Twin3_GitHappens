<?php

namespace Database\Factories;

use App\Models\Batch;
use App\Models\Product;
use App\Models\QualityCheck;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QualityCheck>
 */
class QualityCheckFactory extends Factory
{
    protected $model = QualityCheck::class;

    private static $decisionExplanations = [
        'sell' => [
            'Aspect visuel excellent, couleurs vives et texture ferme. Aucun défaut détecté.',
            'Produit conforme aux spécifications qualité. Couleur, texture et taille dans les normes.',
            'Analyse visuelle positive : maturité optimale, absence de meurtrissures, taux de sucre idéal.',
        ],
        'donate' => [
            'Produit consommable mais présentant des défauts esthétiques mineurs. Convient pour la distribution alimentaire.',
            'Légère dépassement de la fenêtre de vente optimale mais encore propre à la consommation.',
            'Imperfections de forme ou de taille n\'affectant pas la qualité gustative ou sanitaire.',
        ],
        'withdraw' => [
            'Présence de moisissure visible et odeur suspecte. Retrait immédiat requis.',
            'Maturité excessive et signes évidents de détérioration. Ne peut être commercialisé.',
            'Couleur anormale et texture ramollie. Risque sanitaire potentiel.',
        ],
        'inspect' => [
            'Résultats ambigus : présence de taches dont l\'origine n\'est pas certaine. Nécessite une vérification manuelle.',
            'Score qualité à la limite du seuil. Recommandation : contre-expertise par un opérateur qualifié.',
            'Détection d\'anomalies mineures non catégorisables. Inspection physique recommandée.',
        ],
    ];

    public function definition(): array
    {
        $product = Product::inRandomOrder()->first() ?? Product::factory()->create();
        $batch = $this->faker->optional(0.6)->passthrough(
            Batch::where('product_id', $product->id)->inRandomOrder()->first()
            ?? Batch::factory()->create(['product_id' => $product->id])
        );

        $qualityScore = $this->faker->randomFloat(2, 25, 99);
        $freshnessScore = $this->faker->randomFloat(2, 20, 98);
        $avgScore = ($qualityScore + $freshnessScore) / 2;

        if ($avgScore >= 82) {
            $decision = 'sell';
        } elseif ($avgScore >= 60) {
            $decision = $this->faker->boolean(60) ? 'sell' : 'donate';
        } elseif ($avgScore >= 40) {
            $decision = $this->faker->boolean(50) ? 'donate' : 'inspect';
        } else {
            $decision = $this->faker->boolean(80) ? 'withdraw' : 'inspect';
        }

        $confidence = $this->faker->randomFloat(2, 65, 99.5);
        $status = $this->faker->randomElement(['completed', 'completed', 'completed', 'completed', 'pending', 'failed']);

        return [
            'product_id' => $product->id,
            'batch_id' => $batch?->id,
            'image_path' => null,
            'vision_result' => [
                'labels' => [
                    ['label' => $this->faker->randomElement(['fruit_frais', 'legume', 'emballage_intact', 'etiquette']), 'score' => $this->faker->randomFloat(2, 0.7, 0.99)],
                ],
                'objects_detected' => $this->faker->randomNumber(1),
                'resolution' => $this->faker->randomElement(['1920x1080', '2560x1440', '1280x720']),
            ],
            'quality_score' => $qualityScore,
            'freshness_score' => $freshnessScore,
            'decision' => $status === 'completed' ? $decision : null,
            'confidence' => $status === 'completed' ? $confidence : null,
            'explanation' => $status === 'completed'
                ? $this->faker->randomElement(self::$decisionExplanations[$decision])
                : null,
            'factors' => [
                'couleur' => $this->faker->randomFloat(2, 40, 95),
                'texture' => $this->faker->randomFloat(2, 35, 96),
                'forme' => $this->faker->randomFloat(2, 50, 95),
                'uniformite' => $this->faker->randomFloat(2, 30, 90),
            ],
            'status' => $status,
            'created_by' => User::inRandomOrder()->value('id') ?? User::factory(),
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
        ]);
    }

    public function sell(): static
    {
        return $this->state(fn (array $attributes) => [
            'decision' => 'sell',
            'quality_score' => $this->faker->randomFloat(2, 80, 99),
            'freshness_score' => $this->faker->randomFloat(2, 78, 99),
            'confidence' => $this->faker->randomFloat(2, 85, 99),
            'status' => 'completed',
            'explanation' => $this->faker->randomElement(self::$decisionExplanations['sell']),
        ]);
    }

    public function donate(): static
    {
        return $this->state(fn (array $attributes) => [
            'decision' => 'donate',
            'quality_score' => $this->faker->randomFloat(2, 55, 75),
            'freshness_score' => $this->faker->randomFloat(2, 50, 72),
            'confidence' => $this->faker->randomFloat(2, 70, 90),
            'status' => 'completed',
            'explanation' => $this->faker->randomElement(self::$decisionExplanations['donate']),
        ]);
    }

    public function withdraw(): static
    {
        return $this->state(fn (array $attributes) => [
            'decision' => 'withdraw',
            'quality_score' => $this->faker->randomFloat(2, 15, 45),
            'freshness_score' => $this->faker->randomFloat(2, 10, 40),
            'confidence' => $this->faker->randomFloat(2, 80, 98),
            'status' => 'completed',
            'explanation' => $this->faker->randomElement(self::$decisionExplanations['withdraw']),
        ]);
    }

    public function inspect(): static
    {
        return $this->state(fn (array $attributes) => [
            'decision' => 'inspect',
            'quality_score' => $this->faker->randomFloat(2, 35, 65),
            'freshness_score' => $this->faker->randomFloat(2, 30, 60),
            'confidence' => $this->faker->randomFloat(2, 55, 78),
            'status' => 'completed',
            'explanation' => $this->faker->randomElement(self::$decisionExplanations['inspect']),
        ]);
    }
}
