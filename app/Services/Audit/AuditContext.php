<?php

namespace App\Services\Audit;

/**
 * Extracts audit context (who, where) from the current request.
 * Used by controllers when recording audit events.
 */
class AuditContext
{
    /**
     * Get the current actor's ID and display name from the authenticated user.
     *
     * @return array{user_id: string|null, user_name: string|null, account_id: string|null}
     */
    public static function actor(): array
    {
        $user = auth()->user();

        if (!$user) {
            return [
                'user_id' => null,
                'user_name' => 'System',
                'account_id' => session('customer_tenant_id'),
            ];
        }

        $name = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
        if (empty($name)) {
            $name = $user->email ?? $user->name ?? 'Unknown';
        }

        return [
            'user_id' => $user->id,
            'user_name' => $name,
            'account_id' => $user->tenant_id ?? session('customer_tenant_id'),
        ];
    }

    /**
     * Get account_id for the current tenant context.
     */
    public static function accountId(): ?string
    {
        $user = auth()->user();
        return $user?->tenant_id ?? session('customer_tenant_id');
    }

    /**
     * Build a diff of changed fields between old and new values.
     * Only includes fields that actually changed.
     */
    public static function diff(array $oldValues, array $newValues): array
    {
        $changes = [];
        foreach ($newValues as $key => $newVal) {
            $oldVal = $oldValues[$key] ?? null;
            if ($oldVal !== $newVal) {
                $changes[$key] = [
                    'from' => $oldVal,
                    'to' => $newVal,
                ];
            }
        }
        return $changes;
    }

    /**
     * Sanitize metadata to redact sensitive fields before storing in audit log.
     */
    public static function sanitize(array $data): array
    {
        $sensitiveFields = [
            'password', 'password_hash', 'token', 'secret', 'api_key',
            'credit_card', 'credential', 'private_key', 'auth_token',
            'session_token', 'mfa_secret', 'otp_secret', 'recovery_codes',
        ];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = static::sanitize($value);
            } elseif (is_string($value)) {
                foreach ($sensitiveFields as $field) {
                    if (stripos($key, $field) !== false) {
                        $data[$key] = '[REDACTED]';
                        break;
                    }
                }
            }
        }

        return $data;
    }
}
