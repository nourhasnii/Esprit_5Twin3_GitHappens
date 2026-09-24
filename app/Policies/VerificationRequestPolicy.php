<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VerificationRequest;

class VerificationRequestPolicy
{
    public function viewAny(User $user): bool { return $user->can('review_verifications'); }
    public function view(User $user, VerificationRequest $request): bool { return $user->can('review_verifications'); }
    public function review(User $user, VerificationRequest $request): bool { return $user->can('review_verifications'); }
}