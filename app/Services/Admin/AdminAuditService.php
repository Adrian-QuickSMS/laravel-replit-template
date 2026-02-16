<?php

namespace App\Services\Admin;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AdminAuditService
{
    const CATEGORY_AUTH = 'authentication';
    const CATEGORY_ACCESS = 'access_control';
    const CATEGORY_DATA = 'data_access';
    const CATEGORY_IMPERSONATION = 'impersonation';
    const CATEGORY_CONFIG = 'configuration';
    const CATEGORY_SECURITY = 'security';
    const CATEGORY_ADMIN_USER = 'admin_user_management';
    
    const SEVERITY_LOW = 'low';
    const SEVERITY_MEDIUM = 'medium';
    const SEVERITY_HIGH = 'high';
    const SEVERITY_CRITICAL = 'critical';
    
    const EVENT_ADMIN_USER_INVITED = 'ADMIN_USER_INVITED';
    const EVENT_ADMIN_USER_INVITE_RESENT = 'ADMIN_USER_INVITE_RESENT';
    const EVENT_ADMIN_USER_ACTIVATED = 'ADMIN_USER_ACTIVATED';
    const EVENT_ADMIN_USER_SUSPENDED = 'ADMIN_USER_SUSPENDED';
    const EVENT_ADMIN_USER_REACTIVATED = 'ADMIN_USER_REACTIVATED';
    const EVENT_ADMIN_USER_ARCHIVED = 'ADMIN_USER_ARCHIVED';
    const EVENT_ADMIN_USER_PASSWORD_RESET = 'ADMIN_USER_PASSWORD_RESET';
    const EVENT_ADMIN_USER_MFA_RESET = 'ADMIN_USER_MFA_RESET';
    const EVENT_ADMIN_USER_MFA_UPDATED = 'ADMIN_USER_MFA_UPDATED';
    const EVENT_ADMIN_USER_EMAIL_UPDATED = 'ADMIN_USER_EMAIL_UPDATED';
    const EVENT_ADMIN_USER_SESSIONS_REVOKED = 'ADMIN_USER_SESSIONS_REVOKED';
    const EVENT_ADMIN_IMPERSONATION_STARTED = 'ADMIN_IMPERSONATION_STARTED';
    const EVENT_ADMIN_IMPERSONATION_ENDED = 'ADMIN_IMPERSONATION_ENDED';
    const EVENT_ADMIN_LOGIN_BLOCKED_BY_IP = 'ADMIN_LOGIN_BLOCKED_BY_IP';
    
    protected static array $eventCatalogue = [
        'admin_login_success' => [
            'category' => self::CATEGORY_AUTH,
            'description' => 'Admin user logged in successfully',
            'severity' => self::SEVERITY_MEDIUM
        ],
        'admin_login_failed' => [
            'category' => self::CATEGORY_AUTH,
            'description' => 'Admin login attempt failed',
            'severity' => self::SEVERITY_HIGH
        ],
        'admin_logout' => [
            'category' => self::CATEGORY_AUTH,
            'description' => 'Admin user logged out',
            'severity' => self::SEVERITY_LOW
        ],
        'admin_mfa_verified' => [
            'category' => self::CATEGORY_AUTH,
            'description' => 'Admin MFA verification successful',
            'severity' => self::SEVERITY_MEDIUM
        ],
        'admin_mfa_failed' => [
            'category' => self::CATEGORY_AUTH,
            'description' => 'Admin MFA verification failed',
            'severity' => self::SEVERITY_HIGH
        ],
        'admin_session_expired' => [
            'category' => self::CATEGORY_AUTH,
            'description' => 'Admin session expired',
            'severity' => self::SEVERITY_LOW
        ],
        'admin_ip_blocked' => [
            'category' => self::CATEGORY_SECURITY,
            'description' => 'Access blocked due to IP not in allowlist',
            'severity' => self::SEVERITY_HIGH
        ],
        'admin_impersonation_start' => [
            'category' => self::CATEGORY_IMPERSONATION,
            'description' => 'Admin started impersonating customer account',
            'severity' => self::SEVERITY_HIGH
        ],
        'admin_impersonation_end' => [
            'category' => self::CATEGORY_IMPERSONATION,
            'description' => 'Admin ended impersonation session',
            'severity' => self::SEVERITY_MEDIUM
        ],
        'admin_impersonation_action' => [
            'category' => self::CATEGORY_IMPERSONATION,
            'description' => 'Action performed during impersonation',
            'severity' => self::SEVERITY_MEDIUM
        ],
        'admin_data_reveal' => [
            'category' => self::CATEGORY_DATA,
            'description' => 'Admin revealed masked PII data',
            'severity' => self::SEVERITY_HIGH
        ],
        'admin_data_export' => [
            'category' => self::CATEGORY_DATA,
            'description' => 'Admin exported data',
            'severity' => self::SEVERITY_HIGH
        ],
        'admin_account_status_change' => [
            'category' => self::CATEGORY_ACCESS,
            'description' => 'Admin changed account status',
            'severity' => self::SEVERITY_HIGH
        ],
        'admin_approval_decision' => [
            'category' => self::CATEGORY_ACCESS,
            'description' => 'Admin made approval decision',
            'severity' => self::SEVERITY_MEDIUM
        ],
        'admin_config_change' => [
            'category' => self::CATEGORY_CONFIG,
            'description' => 'Admin changed system configuration',
            'severity' => self::SEVERITY_HIGH
        ],
        'admin_permission_denied' => [
            'category' => self::CATEGORY_ACCESS,
            'description' => 'Admin action denied due to insufficient permissions',
            'severity' => self::SEVERITY_MEDIUM
        ],
        self::EVENT_ADMIN_USER_INVITED => [
            'category' => self::CATEGORY_ADMIN_USER,
            'description' => 'Admin user invitation sent',
            'severity' => self::SEVERITY_MEDIUM
        ],
        self::EVENT_ADMIN_USER_INVITE_RESENT => [
            'category' => self::CATEGORY_ADMIN_USER,
            'description' => 'Admin user invitation resent',
            'severity' => self::SEVERITY_LOW
        ],
        self::EVENT_ADMIN_USER_ACTIVATED => [
            'category' => self::CATEGORY_ADMIN_USER,
            'description' => 'Admin user account activated',
            'severity' => self::SEVERITY_MEDIUM
        ],
        self::EVENT_ADMIN_USER_SUSPENDED => [
            'category' => self::CATEGORY_ADMIN_USER,
            'description' => 'Admin user account suspended',
            'severity' => self::SEVERITY_HIGH
        ],
        self::EVENT_ADMIN_USER_REACTIVATED => [
            'category' => self::CATEGORY_ADMIN_USER,
            'description' => 'Admin user account reactivated',
            'severity' => self::SEVERITY_HIGH
        ],
        self::EVENT_ADMIN_USER_ARCHIVED => [
            'category' => self::CATEGORY_ADMIN_USER,
            'description' => 'Admin user account archived',
            'severity' => self::SEVERITY_CRITICAL
        ],
        self::EVENT_ADMIN_USER_PASSWORD_RESET => [
            'category' => self::CATEGORY_ADMIN_USER,
            'description' => 'Admin user password reset initiated',
            'severity' => self::SEVERITY_HIGH
        ],
        self::EVENT_ADMIN_USER_MFA_RESET => [
            'category' => self::CATEGORY_ADMIN_USER,
            'description' => 'Admin user MFA reset performed',
            'severity' => self::SEVERITY_HIGH
        ],
        self::EVENT_ADMIN_USER_MFA_UPDATED => [
            'category' => self::CATEGORY_ADMIN_USER,
            'description' => 'Admin user MFA method updated',
            'severity' => self::SEVERITY_HIGH
        ],
        self::EVENT_ADMIN_USER_EMAIL_UPDATED => [
            'category' => self::CATEGORY_ADMIN_USER,
            'description' => 'Admin user email address updated',
            'severity' => self::SEVERITY_HIGH
        ],
        self::EVENT_ADMIN_USER_SESSIONS_REVOKED => [
            'category' => self::CATEGORY_ADMIN_USER,
            'description' => 'Admin user sessions forcibly revoked',
            'severity' => self::SEVERITY_HIGH
        ],
        self::EVENT_ADMIN_IMPERSONATION_STARTED => [
            'category' => self::CATEGORY_IMPERSONATION,
            'description' => 'Admin impersonation session started',
            'severity' => self::SEVERITY_CRITICAL
        ],
        self::EVENT_ADMIN_IMPERSONATION_ENDED => [
            'category' => self::CATEGORY_IMPERSONATION,
            'description' => 'Admin impersonation session ended',
            'severity' => self::SEVERITY_HIGH
        ],
        self::EVENT_ADMIN_LOGIN_BLOCKED_BY_IP => [
            'category' => self::CATEGORY_SECURITY,
            'description' => 'Admin login blocked due to IP restriction',
            'severity' => self::SEVERITY_CRITICAL
        ],
        'admin_user_created' => [
            'category' => self::CATEGORY_ADMIN_USER,
            'description' => 'New admin user account created',
            'severity' => self::SEVERITY_HIGH
        ],
        'admin_user_updated' => [
            'category' => self::CATEGORY_ADMIN_USER,
            'description' => 'Admin user account updated',
            'severity' => self::SEVERITY_MEDIUM
        ],
        'admin_user_suspended' => [
            'category' => self::CATEGORY_ADMIN_USER,
            'description' => 'Admin user account suspended',
            'severity' => self::SEVERITY_HIGH
        ],
        'admin_user_activated' => [
            'category' => self::CATEGORY_ADMIN_USER,
            'description' => 'Admin user account activated',
            'severity' => self::SEVERITY_MEDIUM
        ],
        'admin_user_unlocked' => [
            'category' => self::CATEGORY_ADMIN_USER,
            'description' => 'Admin user account unlocked',
            'severity' => self::SEVERITY_MEDIUM
        ],
        'admin_user_deleted' => [
            'category' => self::CATEGORY_ADMIN_USER,
            'description' => 'Admin user account deleted',
            'severity' => self::SEVERITY_CRITICAL
        ],
        'admin_mfa_reset' => [
            'category' => self::CATEGORY_ADMIN_USER,
            'description' => 'Admin user MFA reset performed',
            'severity' => self::SEVERITY_HIGH
        ],
        'admin_invite_resent' => [
            'category' => self::CATEGORY_ADMIN_USER,
            'description' => 'Admin user invitation resent',
            'severity' => self::SEVERITY_LOW
        ],
        'admin_password_changed' => [
            'category' => self::CATEGORY_AUTH,
            'description' => 'Admin user password changed',
            'severity' => self::SEVERITY_HIGH
        ],
        'admin_mfa_skipped_dev' => [
            'category' => self::CATEGORY_AUTH,
            'description' => 'Admin MFA skipped in development mode',
            'severity' => self::SEVERITY_MEDIUM
        ],
    ];
    
    public static function log(string $eventCode, array $data = [], ?string $severityOverride = null): void
    {
        $eventDef = self::$eventCatalogue[$eventCode] ?? null;
        
        if (!$eventDef) {
            Log::warning("Unknown admin audit event: {$eventCode}");
            $eventDef = [
                'category' => 'unknown',
                'description' => $eventCode,
                'severity' => self::SEVERITY_MEDIUM
            ];
        }
        
        $adminSession = session('admin_auth', []);
        
        $logEntry = [
            'event_id' => Str::uuid()->toString(),
            'event_code' => $eventCode,
            'category' => $eventDef['category'],
            'description' => $eventDef['description'],
            'severity' => $severityOverride ?? $eventDef['severity'],
            'timestamp_utc' => gmdate('Y-m-d\TH:i:s\Z'),
            'actor' => [
                'admin_id' => $adminSession['admin_id'] ?? null,
                'email' => $adminSession['email'] ?? session('admin_email', 'unknown'),
                'role' => $adminSession['role'] ?? session('admin_role', 'unknown'),
                'name' => $adminSession['name'] ?? null
            ],
            'context' => [
                'source_ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'session_id' => session()->getId(),
                'request_path' => request()->path(),
                'request_method' => request()->method()
            ],
            'data' => self::sanitizeData($data),
            'immutable' => true,
            'retention_years' => 7
        ];
        
        Log::channel('single')->info('[AdminAudit] ' . json_encode($logEntry));
        
        // TODO: Store in admin_audit_logs database table when backend integration is complete
    }
    
    public static function logAdminUserEvent(
        string $eventType,
        string $actorAdminEmail,
        ?string $targetAdminEmail,
        ?array $beforeValues = null,
        ?array $afterValues = null,
        ?string $reason = null
    ): void {
        self::log($eventType, [
            'actor_admin' => $actorAdminEmail,
            'target_admin' => $targetAdminEmail,
            'before_values' => $beforeValues,
            'after_values' => $afterValues,
            'reason' => $reason
        ]);
    }
    
    public static function logUserInvited(
        string $actorAdmin,
        string $targetEmail,
        string $role
    ): void {
        self::logAdminUserEvent(
            self::EVENT_ADMIN_USER_INVITED,
            $actorAdmin,
            $targetEmail,
            null,
            ['status' => 'Invited', 'role' => $role]
        );
    }
    
    public static function logInviteResent(
        string $actorAdmin,
        string $targetEmail
    ): void {
        self::logAdminUserEvent(
            self::EVENT_ADMIN_USER_INVITE_RESENT,
            $actorAdmin,
            $targetEmail
        );
    }
    
    public static function logUserActivated(
        string $actorAdmin,
        string $targetEmail
    ): void {
        self::logAdminUserEvent(
            self::EVENT_ADMIN_USER_ACTIVATED,
            $actorAdmin,
            $targetEmail,
            ['status' => 'Invited'],
            ['status' => 'Active']
        );
    }
    
    public static function logUserSuspended(
        string $actorAdmin,
        string $targetEmail,
        string $previousStatus,
        string $reason
    ): void {
        self::logAdminUserEvent(
            self::EVENT_ADMIN_USER_SUSPENDED,
            $actorAdmin,
            $targetEmail,
            ['status' => $previousStatus],
            ['status' => 'Suspended'],
            $reason
        );
    }
    
    public static function logUserReactivated(
        string $actorAdmin,
        string $targetEmail,
        string $reason
    ): void {
        self::logAdminUserEvent(
            self::EVENT_ADMIN_USER_REACTIVATED,
            $actorAdmin,
            $targetEmail,
            ['status' => 'Suspended'],
            ['status' => 'Active'],
            $reason
        );
    }
    
    public static function logUserArchived(
        string $actorAdmin,
        string $targetEmail,
        string $previousStatus,
        string $reason
    ): void {
        self::logAdminUserEvent(
            self::EVENT_ADMIN_USER_ARCHIVED,
            $actorAdmin,
            $targetEmail,
            ['status' => $previousStatus],
            ['status' => 'Archived'],
            $reason
        );
    }
    
    public static function logPasswordReset(
        string $actorAdmin,
        string $targetEmail
    ): void {
        self::logAdminUserEvent(
            self::EVENT_ADMIN_USER_PASSWORD_RESET,
            $actorAdmin,
            $targetEmail,
            null,
            ['password_reset_sent' => true]
        );
    }
    
    public static function logMfaReset(
        string $actorAdmin,
        string $targetEmail,
        bool $temporaryDisable = false,
        ?int $disableHours = null
    ): void {
        $afterValues = ['mfa_reset' => true];
        if ($temporaryDisable) {
            $afterValues['mfa_temporarily_disabled'] = true;
            $afterValues['disable_hours'] = $disableHours;
        }
        
        self::logAdminUserEvent(
            self::EVENT_ADMIN_USER_MFA_RESET,
            $actorAdmin,
            $targetEmail,
            ['mfa_enrolled' => true],
            $afterValues
        );
    }
    
    public static function logMfaUpdated(
        string $actorAdmin,
        string $targetEmail,
        string $previousMethod,
        string $newMethod,
        ?string $newPhone = null
    ): void {
        $afterValues = ['mfa_method' => $newMethod];
        if ($newPhone) {
            $afterValues['mfa_phone'] = self::maskPhone($newPhone);
        }
        
        self::logAdminUserEvent(
            self::EVENT_ADMIN_USER_MFA_UPDATED,
            $actorAdmin,
            $targetEmail,
            ['mfa_method' => $previousMethod],
            $afterValues
        );
    }
    
    public static function logEmailUpdated(
        string $actorAdmin,
        string $targetEmail,
        string $previousEmail,
        string $newEmail,
        string $reason
    ): void {
        self::logAdminUserEvent(
            self::EVENT_ADMIN_USER_EMAIL_UPDATED,
            $actorAdmin,
            $targetEmail,
            ['email' => $previousEmail],
            ['email' => $newEmail],
            $reason
        );
    }
    
    public static function logSessionsRevoked(
        string $actorAdmin,
        string $targetEmail,
        int $sessionsRevoked
    ): void {
        self::logAdminUserEvent(
            self::EVENT_ADMIN_USER_SESSIONS_REVOKED,
            $actorAdmin,
            $targetEmail,
            null,
            ['sessions_revoked' => $sessionsRevoked]
        );
    }
    
    public static function logImpersonationStarted(
        string $actorAdmin,
        string $targetUser,
        int $durationMinutes,
        string $sessionId,
        string $reason
    ): void {
        self::logAdminUserEvent(
            self::EVENT_ADMIN_IMPERSONATION_STARTED,
            $actorAdmin,
            $targetUser,
            null,
            [
                'impersonation_active' => true,
                'duration_minutes' => $durationMinutes,
                'session_id' => $sessionId
            ],
            $reason
        );
    }
    
    public static function logImpersonationEnded(
        string $actorAdmin,
        string $targetUser,
        string $sessionId,
        string $endReason,
        int $actualDurationSeconds
    ): void {
        self::logAdminUserEvent(
            self::EVENT_ADMIN_IMPERSONATION_ENDED,
            $actorAdmin,
            $targetUser,
            ['impersonation_active' => true],
            [
                'impersonation_active' => false,
                'session_id' => $sessionId,
                'end_reason' => $endReason,
                'actual_duration_seconds' => $actualDurationSeconds
            ]
        );
    }
    
    public static function logLoginBlockedByIp(
        string $blockedEmail,
        string $blockedIp
    ): void {
        self::log(self::EVENT_ADMIN_LOGIN_BLOCKED_BY_IP, [
            'actor_admin' => $blockedEmail,
            'target_admin' => $blockedEmail,
            'blocked_ip' => $blockedIp,
            'before_values' => null,
            'after_values' => ['login_blocked' => true, 'block_reason' => 'ip_not_allowed']
        ], self::SEVERITY_CRITICAL);
    }
    
    protected static function sanitizeData(array $data): array
    {
        $sensitiveFields = [
            'password', 'password_hash', 'token', 'secret', 'api_key', 
            'credit_card', 'credential', 'private_key', 'auth_token',
            'session_token', 'mfa_secret', 'otp_secret', 'recovery_codes'
        ];
        
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = self::sanitizeData($value);
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
    
    protected static function maskPhone(string $phone): string
    {
        if (strlen($phone) < 6) {
            return '***';
        }
        return substr($phone, 0, 3) . str_repeat('*', strlen($phone) - 5) . substr($phone, -2);
    }
    
    public static function getEventCatalogue(): array
    {
        return self::$eventCatalogue;
    }
    
    public static function logImpersonationAction(string $action, array $data = []): void
    {
        $impersonation = session('admin_impersonation');
        
        if (!$impersonation) {
            return;
        }
        
        self::log('admin_impersonation_action', array_merge([
            'action' => $action,
            'impersonated_account' => $impersonation['account_id'] ?? null,
            'impersonation_reason' => $impersonation['reason'] ?? null,
            'impersonation_started' => $impersonation['started_at'] ?? null
        ], $data), self::SEVERITY_MEDIUM);
    }
    
    public static function isImpersonating(): bool
    {
        return session()->has('admin_impersonation');
    }
}
