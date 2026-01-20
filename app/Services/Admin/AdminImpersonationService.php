<?php

namespace App\Services\Admin;

class AdminImpersonationService
{
    const STATUS_NOT_STARTED = 'not_started';
    const STATUS_ACTIVE = 'active';
    const STATUS_ENDED = 'ended';
    const STATUS_EXPIRED = 'expired';
    
    public static function isEnabled(): bool
    {
        return config('admin.impersonation.enabled', true);
    }
    
    public static function isReadOnly(): bool
    {
        return config('admin.impersonation.read_only', true);
    }
    
    public static function getMaxDuration(): int
    {
        return config('admin.impersonation.max_duration', 300);
    }
    
    public static function getMinReasonLength(): int
    {
        return config('admin.impersonation.min_reason_length', 10);
    }
    
    public static function isActive(): bool
    {
        $session = session('admin_impersonation');
        if (!$session || !isset($session['active']) || $session['active'] !== true) {
            return false;
        }
        
        if (self::isSessionExpired($session)) {
            self::endSession('expired');
            return false;
        }
        
        return true;
    }
    
    public static function getCurrentSession(): ?array
    {
        if (!self::isActive()) {
            return null;
        }
        
        return session('admin_impersonation');
    }
    
    public static function getImpersonatedAccountId(): ?string
    {
        $session = self::getCurrentSession();
        return $session['account_id'] ?? null;
    }
    
    public static function getImpersonatedAccountName(): ?string
    {
        $session = self::getCurrentSession();
        return $session['account_name'] ?? null;
    }
    
    public static function startSession(string $accountId, string $accountName, string $reason): array
    {
        if (!self::isEnabled()) {
            return [
                'success' => false,
                'error' => 'Impersonation is disabled'
            ];
        }
        
        if (strlen($reason) < self::getMinReasonLength()) {
            return [
                'success' => false,
                'error' => 'Reason must be at least ' . self::getMinReasonLength() . ' characters'
            ];
        }
        
        $adminSession = session('admin_auth');
        if (!$adminSession) {
            return [
                'success' => false,
                'error' => 'Admin session not found'
            ];
        }
        
        $sessionId = 'IMP-' . uniqid() . '-' . time();
        $startTime = now();
        $maxDuration = self::getMaxDuration();
        $expiresAt = $startTime->copy()->addSeconds($maxDuration);
        
        $impersonationData = [
            'session_id' => $sessionId,
            'active' => true,
            'read_only' => self::isReadOnly(),
            'account_id' => $accountId,
            'account_name' => $accountName,
            'reason' => $reason,
            'admin_id' => $adminSession['admin_id'] ?? $adminSession['user_id'] ?? null,
            'admin_email' => $adminSession['email'] ?? 'unknown',
            'admin_name' => $adminSession['name'] ?? 'Unknown Admin',
            'admin_role' => $adminSession['role'] ?? 'unknown',
            'started_at' => $startTime->toIso8601String(),
            'expires_at' => $expiresAt->toIso8601String(),
            'max_duration' => $maxDuration,
            'ip_address' => request()->ip(),
            'actions_logged' => []
        ];
        
        session()->put('admin_impersonation', $impersonationData);
        
        AdminAuditService::log('IMPERSONATION_STARTED', [
            'session_id' => $sessionId,
            'account_id' => $accountId,
            'account_name' => $accountName,
            'reason' => $reason,
            'read_only' => self::isReadOnly(),
            'max_duration' => $maxDuration,
            'severity' => 'CRITICAL'
        ]);
        
        return [
            'success' => true,
            'session_id' => $sessionId,
            'expires_at' => $expiresAt->toIso8601String(),
            'read_only' => self::isReadOnly()
        ];
    }
    
    public static function endSession(string $endReason = 'manual'): array
    {
        $session = session('admin_impersonation');
        
        if (!$session) {
            return [
                'success' => false,
                'error' => 'No active impersonation session'
            ];
        }
        
        $sessionId = $session['session_id'] ?? 'unknown';
        $accountId = $session['account_id'] ?? 'unknown';
        $accountName = $session['account_name'] ?? 'unknown';
        $startedAt = $session['started_at'] ?? null;
        $actionsCount = count($session['actions_logged'] ?? []);
        
        $duration = 0;
        if ($startedAt) {
            $duration = now()->diffInSeconds(\Carbon\Carbon::parse($startedAt));
        }
        
        session()->forget('admin_impersonation');
        
        AdminAuditService::log('IMPERSONATION_ENDED', [
            'session_id' => $sessionId,
            'account_id' => $accountId,
            'account_name' => $accountName,
            'end_reason' => $endReason,
            'duration_seconds' => $duration,
            'actions_count' => $actionsCount,
            'severity' => 'HIGH'
        ]);
        
        return [
            'success' => true,
            'session_id' => $sessionId,
            'duration_seconds' => $duration,
            'actions_count' => $actionsCount
        ];
    }
    
    public static function logAction(string $action, array $details = []): void
    {
        if (!self::isActive()) {
            return;
        }
        
        if (!config('admin.impersonation.log_all_actions', true)) {
            return;
        }
        
        $session = session('admin_impersonation');
        $actionsLogged = $session['actions_logged'] ?? [];
        
        $actionEntry = [
            'action' => $action,
            'details' => $details,
            'timestamp' => now()->toIso8601String()
        ];
        
        $actionsLogged[] = $actionEntry;
        $session['actions_logged'] = $actionsLogged;
        session()->put('admin_impersonation', $session);
        
        AdminAuditService::log('IMPERSONATION_ACTION', [
            'session_id' => $session['session_id'] ?? 'unknown',
            'account_id' => $session['account_id'] ?? 'unknown',
            'action' => $action,
            'details' => $details,
            'severity' => 'MEDIUM'
        ]);
    }
    
    public static function getRemainingTime(): int
    {
        $session = self::getCurrentSession();
        if (!$session) {
            return 0;
        }
        
        $expiresAt = $session['expires_at'] ?? null;
        if (!$expiresAt) {
            return 0;
        }
        
        $remaining = \Carbon\Carbon::parse($expiresAt)->diffInSeconds(now(), false);
        return max(0, -$remaining);
    }
    
    protected static function isSessionExpired(array $session): bool
    {
        $expiresAt = $session['expires_at'] ?? null;
        if (!$expiresAt) {
            return true;
        }
        
        return \Carbon\Carbon::parse($expiresAt)->isPast();
    }
    
    public static function canAccessPii(): bool
    {
        return false;
    }
    
    public static function canMutateData(): bool
    {
        if (!self::isActive()) {
            return true;
        }
        
        return !self::isReadOnly();
    }
}
