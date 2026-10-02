<?php

namespace App\Events;

use App\Models\OptimizationRecommendation;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Émis quand une destination de lot est validée (acceptée ou modifiée).
 * Point d'intégration : le module Traçabilité peut l'écouter pour créer un
 * TraceabilityEvent "transport" ou passer le lot en statut in_transit.
 */
class BatchDestinationValidated
{
    use Dispatchable;

    public function __construct(public OptimizationRecommendation $recommendation) {}
}
