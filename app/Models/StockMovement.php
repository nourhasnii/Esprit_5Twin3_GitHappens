<?php

namespace App\Models;

use App\Enums\StockMovementType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class StockMovement extends Model
{
    /** @use HasFactory<\Database\Factories\StockMovementFactory> */
    use HasFactory;

    protected $fillable = [
        'reference', 'type', 'product_id', 'batch_id', 'source_site_id', 'destination_site_id',
        'quantity', 'reason', 'notes', 'user_id', 'moved_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => StockMovementType::class,
            'quantity' => 'float',
            'moved_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (StockMovement $movement) {
            $movement->reference ??= 'MV-'.now()->format('ymd').'-'.Str::upper(Str::random(6));
            $movement->moved_at ??= now();
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function sourceSite(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'source_site_id');
    }

    public function destinationSite(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'destination_site_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Ventes : sorties dont le motif n'indique ni un don, ni une casse, ni une destruction. */
    public function scopeSales(Builder $query): void
    {
        $query->where('type', StockMovementType::Out->value);

        foreach (config('stock.forecast.excluded_reasons', []) as $word) {
            $query->where(fn (Builder $q) => $q->whereNull('reason')->orWhere('reason', 'not like', '%'.$word.'%'));
        }
    }

    public function scopeForBatch(Builder $query, ?int $batchId): void
    {
        $batchId ? $query->where('batch_id', $batchId) : $query->whereNull('batch_id');
    }

    public function scopeFilter(Builder $query, array $filters): void
    {
        $query
            ->when($filters['type'] ?? null, fn ($q, $type) => $q->where('type', $type))
            ->when($filters['site_id'] ?? null, fn ($q, $siteId) => $q->where(
                fn ($w) => $w->where('source_site_id', $siteId)->orWhere('destination_site_id', $siteId)
            ))
            ->when($filters['product_id'] ?? null, fn ($q, $id) => $q->where('product_id', $id))
            ->when($filters['batch_id'] ?? null, fn ($q, $id) => $q->where('batch_id', $id))
            ->when($filters['from'] ?? null, fn ($q, $d) => $q->where('moved_at', '>=', Carbon::parse($d)->startOfDay()))
            ->when($filters['to'] ?? null, fn ($q, $d) => $q->where('moved_at', '<=', Carbon::parse($d)->endOfDay()));
    }

    /** Quantité signée du point de vue d'un site (+ reçu, − sorti). */
    public function signedQuantityFor(int $siteId): float
    {
        return $this->destination_site_id === $siteId ? $this->quantity : -$this->quantity;
    }
}
