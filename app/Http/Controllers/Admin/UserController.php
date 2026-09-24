<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\BrevoMailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);
        $users = User::with('roles')->when($request->filled('search'), function ($query) use ($request) {
            $term = '%' . $request->string('search') . '%';
            $query->where(fn ($q) => $q->where('name', 'like', $term)->orWhere('email', 'like', $term)->orWhere('organization_name', 'like', $term));
        })->when($request->filled('role'), fn ($query) => $query->role($request->string('role')))->when($request->filled('account_status'), fn ($query) => $query->where('account_status', $request->string('account_status')))->latest()->paginate(15)->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'totalUsers' => User::count(),
            'pendingUsers' => User::whereIn('account_status', ['pending', 'information_required'])->count(),
            'activeUsers' => User::where('account_status', 'active')->count(),
            'inactiveUsers' => User::whereIn('account_status', ['rejected', 'suspended'])->count(),
        ]);
    }

    public function show(User $user): View
    {
        $this->authorize('view', $user);
        return view('admin.users.show', ['user' => $user->load(['roles', 'verificationRequests.documents', 'verificationRequests.reviewer'])]);
    }

    public function updateStatus(Request $request, User $user, AuditLogService $audit, BrevoMailService $mail): RedirectResponse
    {
        $this->authorize('updateStatus', $user);
        $validated = $request->validate([
            'account_status' => ['required', 'in:active,suspended'],
            'reason' => ['nullable', 'string', 'max:2000', 'required_if:account_status,suspended'],
        ]);
        $oldStatus = $user->account_status;
        $isSuspend = $validated['account_status'] === 'suspended';
        $reason = $request->string('reason')->toString() ?: null;

        $user->update(['account_status' => $validated['account_status']]);
        $audit->record($isSuspend ? 'ACCOUNT_SUSPENDED' : 'ACCOUNT_REACTIVATED', $user, ['account_status' => $oldStatus], ['account_status' => $user->account_status, 'reason' => $reason]);

        $emailFailed = false;
        try {
            if ($isSuspend) {
                $mail->sendSuspensionEmail($user, $reason);
            } else {
                $mail->sendReactivationEmail($user);
            }
        } catch (RuntimeException) {
            $emailFailed = true;
        }

        return back()->with($emailFailed ? 'error' : 'success', $emailFailed
            ? 'Account status updated, but the notification email could not be delivered. Check Brevo configuration and logs.'
            : 'Account status updated and the user was notified.');
    }
}