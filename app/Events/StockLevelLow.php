<?php

namespace App\Events;

use App\Models\Product;
use App\Models\Site;
use Illuminate\Foundation\Events\Dispatchable;

/** Émis quand le stock disponible d'un produit passe sous son seuil sur un site. */
class StockLevelLow
{
    use Dispatchable;

    public function __construct(
        public Site $site,
        public Product $product,
        public float $available,
        public float $threshold,
    ) {}
}
