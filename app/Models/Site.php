<?php

namespace App\Models;

use App\Enums\SiteType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Site extends Model
{
    protected $fillable = [
        'code', 'name', 'type', 'city', 'address',
        'latitude', 'longitude', 'capacity', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'type' => SiteType::class,
            'latitude' => 'float',
            'longitude' => 'float',
            'capacity' => 'float',
            'is_active' => 'boolean',
        ];
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    public function outgoingMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'source_site_id');
    }

    public function incomingMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'destination_site_id');
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /** Sites qui stockent et vendent (tous sauf les associations). */
    public function scopeLogistics(Builder $query): void
    {
        $query->where('type', '!=', SiteType::Association->value);
    }

    /** Associations qui reçoivent les dons. */
    public function scopeAssociations(Builder $query): void
    {
        $query->where('type', SiteType::Association->value);
    }

    public function hasCoordinates(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    /** Utilise withSum('stocks', 'quantity') s'il a été chargé, sinon interroge la base. */
    public function usedCapacity(): float
    {
        if (array_key_exists('stocks_sum_quantity', $this->attributes)) {
            return (float) $this->attributes['stocks_sum_quantity'];
        }

        return (float) $this->stocks()->sum('quantity');
    }

    public function freeCapacity(): float
    {
        return max(0, $this->capacity - $this->usedCapacity());
    }

    /** Taux d'occupation entre 0 et 1 (peut dépasser 1 après un ajustement). */
    public function occupancyRate(): float
    {
        return $this->capacity > 0 ? $this->usedCapacity() / $this->capacity : 0.0;
    }
}
