<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TraceabilityEvent extends Model
{
    use HasFactory;
    public const TYPES = [
        'production',
        'processing',
        'transport',
        'storage',
        'distribution',
    ];

    protected $fillable = [
        'batch_id',
        'event_type',
        'event_date',
        'location',
        'latitude',
        'longitude',
        'actor_id',
        'description',
        'quantity',
        'temperature',
        'distance_km',
        'carbon_emission',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'datetime',
            'latitude' => 'decimal:6',
            'longitude' => 'decimal:6',
            'quantity' => 'decimal:2',
            'temperature' => 'decimal:2',
            'distance_km' => 'decimal:2',
            'carbon_emission' => 'decimal:2',
            'metadata' => 'array',
        ];
        }

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}