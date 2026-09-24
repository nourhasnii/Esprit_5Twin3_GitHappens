<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool { return $user->can('manage_users'); }
    public function view(User $user, User $model): bool { return $user->can('manage_users'); }
    public function updateStatus(User $user, User $model): bool { return $user->can('manage_users') && $user->id !== $model->id; }
}