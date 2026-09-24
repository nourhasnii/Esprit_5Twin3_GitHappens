<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\VerificationDecisionRequest;
use App\Models\VerificationRequest;
use App\Services\AccountActivationService;
use App\Services\AuditLogService;
use App\Services\BrevoMailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class VerificationRequestController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', VerificationRequest::class);
        $requests = VerificationRequest::with('user.roles')->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))->when($request->filled('search'), function ($query) use ($request) {
            $term = '%' . $request->string('search') . '%';
            $query->whereHas('user', fn ($q) => $q->where('name', 'like', $term)->orWhere('email', 'like', $term)->orWhere('organization_name', 'like', $term));
        })->latest('submitted_at')->paginate(15)->withQueryString();

        return view('admin.verification.index', compact('requests'));
    }

    public function show(VerificationRequest $verificationRequest): View
    {
        $this->authorize('view', $verificationRequest);
        return view('admin.verification.show', ['verificationRequest' => $verificationRequest->load(['user.roles', 'documents', 'reviewer'])]);
    }

    public function approve(VerificationRequest $verificationRequest, AccountActivationService $activation, AuditLogService $audit, BrevoMailService $mail): RedirectResponse
    {
        $this->authorize('review', $verificationRequest);
        $this->ensurePending($verificationRequest);
        $reviewerId = request()->user()->id;
        $verificationRequest->update(['status' => 'approved', 'decision' => 'approved', 'reviewed_at' => now(), 'reviewed_by' => $reviewerId]);
        $verificationRequest->user->update(['account_status' => 'pending', 'approved_at' => now(), 'approved_by' => $reviewerId, 'rejection_reason' => null]);
        $audit->record('ACCOUNT_APPROVED', $verificationRequest->user, [], ['verification_request_id' => $verificationRequest->id]);
        try {
            $messageId = $activation->issue($verificationRequest->user, $mail);
        } catch (RuntimeException) {
            return back()->with('error', 'The account was approved, but the activation email could not be delivered. Check Brevo configuration and logs.');
        }
        return back()->with('success', 'Account approved. Activation email accepted by Brevo (' . $messageId . ').');
    }

    public function requestInformation(VerificationDecisionRequest $request, VerificationRequest $verificationRequest, AuditLogService $audit, BrevoMailService $mail): RedirectResponse
    {
        $this->authorize('review', $verificationRequest);
        $this->ensurePending($verificationRequest);
        $reason = $request->string('reason')->toString();
        $reviewerId = request()->user()->id;
        $verificationRequest->update(['status' => 'information_required', 'decision' => 'information_required', 'information_request_reason' => $reason, 'reviewed_at' => now(), 'reviewed_by' => $reviewerId]);
        $verificationRequest->user->update(['account_status' => 'information_required', 'information_requested_at' => now(), 'information_requested_by' => $reviewerId]);
        $audit->record('INFORMATION_REQUESTED', $verificationRequest->user, [], ['reason' => $reason]);
        try {
            $mail->sendInformationRequestEmail($verificationRequest->user, $reason);
        } catch (RuntimeException) {
            return back()->with('error', 'The request was saved, but the information email could not be delivered. Check Brevo configuration and logs.');
        }
        return back()->with('success', 'The applicant has been asked for more information.');
    }

    public function reject(VerificationDecisionRequest $request, VerificationRequest $verificationRequest, AuditLogService $audit, BrevoMailService $mail): RedirectResponse
    {
        $this->authorize('review', $verificationRequest);
        $this->ensurePending($verificationRequest);
        $reason = $request->string('reason')->toString();
        $reviewerId = request()->user()->id;
        $verificationRequest->update(['status' => 'rejected', 'decision' => 'rejected', 'rejection_reason' => $reason, 'reviewed_at' => now(), 'reviewed_by' => $reviewerId]);
        $verificationRequest->user->update(['account_status' => 'rejected', 'rejected_at' => now(), 'rejected_by' => $reviewerId, 'rejection_reason' => $reason]);
        $audit->record('ACCOUNT_REJECTED', $verificationRequest->user, [], ['reason' => $reason]);
        try {
            $mail->sendRejectionEmail($verificationRequest->user, $reason);
        } catch (RuntimeException) {
            return back()->with('error', 'The rejection was saved, but the notification email could not be delivered. Check Brevo configuration and logs.');
        }
        return back()->with('success', 'The account was rejected and the applicant was notified.');
    }

    private function ensurePending(VerificationRequest $request): void
    {
        abort_unless(in_array($request->status, ['pending', 'information_required'], true), 422, 'This request has already been reviewed.');
    }
}