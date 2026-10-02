<?php

namespace App\Models;

use App\Services\BatchQrCodeService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Batch extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::created(fn (Batch $batch) => app(BatchQrCodeService::class)->generate($batch));
        static::updated(function (Batch $batch) {
            if ($batch->wasChanged('lot_number')) {
                app(BatchQrCodeService::class)->generate($batch);
            }
        });
    }

    protected $fillable = [
        'product_id',
        'lot_number',
        'production_date',
        'expiration_date',
        'quantity',
        'unit',
        'carbon_footprint',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'production_date' => 'date',
            'expiration_date' => 'date',
            'quantity' => 'decimal:2',
            'carbon_footprint' => 'decimal:2',
        ];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function events()
    {
        return $this->hasMany(TraceabilityEvent::class);
    }

}