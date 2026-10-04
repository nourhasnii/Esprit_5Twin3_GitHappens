<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransportCondition extends Model
{
    use HasFactory;

    public const TEMPERATURE_MIN = 2.0;
    public const TEMPERATURE_MAX = 5.0;

    protected $fillable = [
        'batch_id',
        'traceability_event_id',
        'recorded_at',
        'temperature',
        'humidity',
        'location',
        'duration_minutes',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'recorded_at' => 'datetime',
            'temperature' => 'decimal:2',
            'humidity' => 'decimal:2',
            'duration_minutes' => 'integer',
        ];
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    public function traceabilityEvent()
    {
        return $this->belongsTo(TraceabilityEvent::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function alerts()
    {
        return $this->hasMany(Alert::class);
    }

    public function scopeOutOfRange(Builder $query): Builder
    {
        return $query->where(function (Builder $query) {
            $query->where('temperature', '<', self::TEMPERATURE_MIN)
                ->orWhere('temperature', '>', self::TEMPERATURE_MAX);
        });
    }
}
