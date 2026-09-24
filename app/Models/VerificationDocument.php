<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\VerificationRequest;

class VerificationDocument extends Model
{
    protected $fillable = ['verification_request_id', 'document_type', 'file_path', 'original_name', 'mime_type', 'file_size', 'uploaded_by'];

    public function verificationRequest() { return $this->belongsTo(VerificationRequest::class); }
    public function uploader() { return $this->belongsTo(User::class, 'uploaded_by'); }
}