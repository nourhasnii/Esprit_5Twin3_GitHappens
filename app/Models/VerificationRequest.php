<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\VerificationDocument;

class VerificationRequest extends Model
{
    public const PENDING = 'pending';
    public const INFORMATION_REQUIRED = 'information_required';
    public const APPROVED = 'approved';
    public const REJECTED = 'rejected';

    protected $fillable = [
        'user_id', 'status', 'submitted_at', 'reviewed_at', 'reviewed_by',
        'decision', 'rejection_reason', 'information_request_reason',
    ];

    protected function casts(): array
    {
        return ['submitted_at' => 'datetime', 'reviewed_at' => 'datetime'];
    }

    public function user() { return $this->belongsTo(User::class); }
    public function reviewer() { return $this->belongsTo(User::class, 'reviewed_by'); }
    public function documents() { return $this->hasMany(VerificationDocument::class); }
}