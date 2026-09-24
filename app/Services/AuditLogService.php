<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AuditLogService
{
    public function record(
        string $action,
        ?Model $entity = null,
        array $old = [],
        array $new = [],
        ?Request $request = null,
        ?User $actor = null,
    ): AuditLog {
        $request ??= request();
        $actor ??= auth()->user();

        return AuditLog::create([
            'actor_id' => $actor?->getKey(),
            'action' => $action,
            'entity_type' => $entity?->getMorphClass(),
            'entity_id' => $entity?->getKey(),
            'old_values' => $old ?: null,
            'new_values' => $new ?: null,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}