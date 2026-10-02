<?php

namespace App\Models\Concerns;

use App\Models\Stock;
use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * À ajouter dans App\Models\Product et App\Models\Batch :
 *     use \App\Models\Concerns\HasStock;
 */
trait HasStock
{
    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }
}
