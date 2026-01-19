<?php

namespace App\Services\Admin;

use Illuminate\Support\Facades\Log;

class AdminAuditService
{
    const CATEGORY_AUTH = 'authentication';
    const CATEGORY_ACCESS = 'access_control';
    const CATEGORY_DATA = 'data_access';
    const CATEGORY_IMPERSONATION = 'impersonation';
    const CATEGORY_CONFIG = 'configuration';
    const CATEGORY_SECURITY = 'security';
    
    const SEVERITY_LOW = 'low';
    const SEVERITY_MEDIUM = 'medium';
    const SEVERITY_HIGH = 'high';
    const SEVERITY_CRITICAL = 'critical';
    
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
        ]
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
            'event_code' => $eventCode,
            'category' => $eventDef['category'],
            'description' => $eventDef['description'],
            'severity' => $severityOverride ?? $eventDef['severity'],
            'timestamp' => now()->toIso8601String(),
            'actor' => [
                'admin_id' => $adminSession['admin_id'] ?? null,
                'email' => $adminSession['email'] ?? null,
                'role' => $adminSession['role'] ?? null,
                'name' => $adminSession['name'] ?? null
            ],
            'context' => [
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'session_id' => session()->getId(),
                'request_path' => request()->path(),
                'request_method' => request()->method()
            ],
            'data' => self::sanitizeData($data)
        ];
        
        Log::channel('admin_audit')->info(json_encode($logEntry));
        
        // TODO: Store in admin_audit_logs database table when backend integration is complete
    }
    
    protected static function sanitizeData(array $data): array
    {
        $sensitiveFields = ['password', 'token', 'secret', 'api_key', 'credit_card'];
        
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
