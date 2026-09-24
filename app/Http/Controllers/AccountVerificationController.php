<?php

namespace App\Http\Controllers;

use App\Services\AuditLogService;
use App\Services\BrevoMailService;
use RuntimeException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountVerificationController extends Controller
{
    public function edit(Request $request): View
    {
        return view('account.verification', ['user' => $request->user()->load('verificationRequests.documents')]);
    }

    public function update(Request $request, AuditLogService $audit, BrevoMailService $mail): RedirectResponse
    {
        $validated = $request->validate([
            'organization_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'country' => ['nullable', 'string', 'max:100'],
            'region' => ['nullable', 'string', 'max:150'],
            'address' => ['required', 'string', 'max:1000'],
            'professional_identifier' => ['nullable', 'string', 'max:255'],
            'document_type' => ['required', 'string', 'max:100'],
            'document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);
        $user = $request->user();
        $path = $request->file('document')->store('verification-documents');
        $user->update(collect($validated)->except(['document_type', 'document'])->all() + ['account_status' => 'pending']);
        $verification = $user->verificationRequests()->whereIn('status', ['information_required', 'pending'])->latest()->first();
        $oldStatus = $verification?->status;
        $verification ??= $user->verificationRequests()->create(['status' => 'pending']);
        $verification->update(['status' => 'pending', 'submitted_at' => now(), 'information_request_reason' => null]);
        $verification->documents()->create(['document_type' => $validated['document_type'], 'file_path' => $path, 'original_name' => $request->file('document')->getClientOriginalName(), 'mime_type' => $request->file('document')->getMimeType(), 'file_size' => $request->file('document')->getSize(), 'uploaded_by' => $user->id]);
        $audit->record($oldStatus === 'information_required' ? 'APPLICATION_RESUBMITTED' : 'VERIFICATION_SUBMITTED', $verification, ['status' => $oldStatus], ['status' => 'pending', 'document_type' => $validated['document_type']]);

        $emailFailed = false;
        try {
            $mail->sendAdminInformationSubmittedEmail($user, $verification);
        } catch (RuntimeException) {
            $emailFailed = true;
        }

        return redirect()->route('dashboard')->with($emailFailed ? 'error' : 'success', $emailFailed
            ? 'Your information was saved, but the administrator notification could not be delivered. Check Brevo configuration and logs.'
            : 'Your information has been resubmitted for review.');
    }
}