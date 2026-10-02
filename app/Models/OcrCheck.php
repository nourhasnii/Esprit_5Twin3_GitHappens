<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OcrCheck extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_id',
        'product_id',
        'image_path',
        'ocr_result',
        'declared_snapshot',
        'mismatches',
        'inconsistency_score',
        'confidence',
        'status',
        'explanation',
        'error_message',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'ocr_result' => 'array',
            'declared_snapshot' => 'array',
            'mismatches' => 'array',
            'inconsistency_score' => 'decimal:2',
            'confidence' => 'decimal:2',
        ];
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
