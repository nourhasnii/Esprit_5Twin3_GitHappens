<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Batch extends Model
{
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