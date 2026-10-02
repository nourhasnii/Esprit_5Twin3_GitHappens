<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'description',
        'category_id',
        'category',
        'origin_country',
        'origin_region',
        'producer_id',
        'unit',
        'image',
        'barcode',
        'is_organic',
        'carbon_footprint',
        'verification_status',
    ];

    protected function casts(): array
    {
        return [
            'is_organic' => 'boolean',
            'carbon_footprint' => 'decimal:2',
        ];
    }

    public function producer()
    {
        return $this->belongsTo(User::class, 'producer_id');
    }

    public function categoryModel()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function batches()
    {
        return $this->hasMany(Batch::class);
    }

    public function certifications()
    {
        return $this->hasMany(Certification::class);
    }
    public function qualityChecks()
{
    return $this->hasMany(QualityCheck::class);
}
}