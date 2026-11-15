<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogService
{
    public function record(
        string $action,
        ?string $targetType = null,
        int|string|null $targetId = null,
        array $metadata = [],
        string $outcome = 'success',
        string $severity = 'info',
        ?Request $request = null,
    ): AuditLog {
        $request ??= request();
        $actor = $request->user();

        return AuditLog::query()->create([
            'occurred_at' => now(),
            'actor_type' => $actor?->role ?? 'system',
            'actor_user_id' => $actor?->getKey(),
            'action' => $action,
            'target_type' => $targetType,
            'target_id' => $targetId === null ? null : (string) $targetId,
            'outcome' => $outcome,
            'severity' => $severity,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
            'request_id' => $request->header('X-Request-ID'),
            'metadata' => $this->redact($metadata),
        ]);
    }

    private function redact(array $metadata): array
    {
        $sensitive = ['password', 'token', 'authorization', 'cookie', 'secret', 'two_factor_secret'];

        foreach ($metadata as $key => $value) {
            if (in_array(strtolower((string) $key), $sensitive, true)) {
                $metadata[$key] = '[REDACTED]';
            }
        }

        return $metadata;
    }
}
