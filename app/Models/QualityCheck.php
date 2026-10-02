<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QualityCheck extends Model
{
    use HasFactory;
    protected $fillable = [
        'product_id',
        'batch_id',
        'image_path',
        'vision_result',
        'quality_score',
        'freshness_score',
        'decision',
        'confidence',
        'explanation',
        'factors',
        'status',
        'created_by',
    ];

    protected $casts = [
        'vision_result' => 'array',
        'factors' => 'array',
        'quality_score' => 'decimal:2',
        'freshness_score' => 'decimal:2',
        'confidence' => 'decimal:2',
    ];

    // Relations
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}