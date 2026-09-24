<?php

namespace App\Services;

use App\Models\AccountActivationToken;
use App\Models\User;
use App\Services\BrevoMailService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class AccountActivationService
{
    public function issue(User $user, BrevoMailService $mail): string
    {
        AccountActivationToken::where('user_id', $user->id)->whereNull('used_at')->delete();
        $plainToken = Str::random(64);
        $activation = AccountActivationToken::create([
            'user_id' => $user->id,
            'token_hash' => Hash::make($plainToken),
            'expires_at' => now()->addHours(24),
        ]);

        return $mail->sendApprovalEmail($user, route('account.activation.show', ['token' => $plainToken]));
    }

    public function activate(string $plainToken, string $password, ?BrevoMailService $mail = null): User
    {
        $activation = AccountActivationToken::whereNull('used_at')->where('expires_at', '>', now())->latest()->get()->first(fn ($item) => Hash::check($plainToken, $item->token_hash));

        abort_unless($activation, 404);

        $user = $activation->user;
        $user->forceFill(['password' => $password, 'account_status' => 'active', 'must_change_password' => false])->save();
        $activation->update(['used_at' => now()]);

        app(AuditLogService::class)->record('ACCOUNT_ACTIVATED', $user, [], [], null, null);

        try {
            ($mail ?? app(BrevoMailService::class))->sendAccountActivatedEmail($user);
        } catch (RuntimeException) {
            Log::warning('NutriTrace account activation email could not be delivered.', ['user_id' => $user->id]);
        }

        return $user;
    }
}