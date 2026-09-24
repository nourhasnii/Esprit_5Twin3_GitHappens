<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VerificationDocument;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VerificationDocumentController extends Controller
{
    public function download(VerificationDocument $verificationDocument): StreamedResponse
    {
        abort_unless(request()->user()->can('review_verifications'), 403);

        return Storage::download($verificationDocument->file_path, $verificationDocument->original_name);
    }
}