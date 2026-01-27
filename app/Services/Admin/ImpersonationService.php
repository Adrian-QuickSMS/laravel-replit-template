<?php

namespace App\Services\Admin;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use App\Services\Admin\AdminAuditService;

class ImpersonationService
{
    protected bool $mockMode = true;
    
    protected array $piiMaskedAreas = [
        'contacts',
        'message_content',
        'exports',
        'phone_numbers',
        'customer_emails',
        'billing_details',
    ];
    
    public function __construct()
    {
        $this->mockMode = config('admin.impersonation_mock_mode', true);
    }
    
    public function startSession(string $adminEmail, string $targetUserId, int $durationMinutes, string $reason): array
    {
        $this->validateSuperAdminRole($adminEmail);
        
        $sessionId = 'IMP-' . time() . '-' . substr(md5($adminEmail . $targetUserId), 0, 8);
        $startTime = now();
        $endTime = $startTime->copy()->addMinutes($durationMinutes);
        
        $sessionData = [
            'session_id' => $sessionId,
            'admin_email' => $adminEmail,
            'target_user_id' => $targetUserId,
            'start_time' => $startTime->toIso8601String(),
            'end_time' => $endTime->toIso8601String(),
            'duration_minutes' => $durationMinutes,
            'reason' => $reason,
            'pii_masked' => true,
            'read_only' => false,
        ];
        
        Session::put('impersonation_session', $sessionData);
        
        AdminAuditService::logImpersonationStarted(
            $adminEmail,
            $targetUserId,
            $durationMinutes,
            $sessionId,
            $reason
        );
        
        return [
            'success' => true,
            'session_id' => $sessionId,
            'end_time' => $endTime->toIso8601String(),
            'pii_masked_areas' => $this->piiMaskedAreas,
        ];
    }
    
    public function endSession(string $sessionId, string $endType = 'manual'): array
    {
        $sessionData = Session::get('impersonation_session');
        
        if (!$sessionData || $sessionData['session_id'] !== $sessionId) {
            return [
                'success' => false,
                'error' => 'Session not found or already ended',
            ];
        }
        
        $durationSeconds = now()->diffInSeconds($sessionData['start_time']);
        
        AdminAuditService::logImpersonationEnded(
            $sessionData['admin_email'],
            $sessionData['target_user_id'],
            $sessionId,
            $endType,
            $durationSeconds
        );
        
        Session::forget('impersonation_session');
        
        return [
            'success' => true,
            'session_id' => $sessionId,
            'end_type' => $endType,
        ];
    }
    
    public function isSessionActive(): bool
    {
        $sessionData = Session::get('impersonation_session');
        
        if (!$sessionData) {
            return false;
        }
        
        $endTime = \Carbon\Carbon::parse($sessionData['end_time']);
        
        if (now()->gt($endTime)) {
            $this->endSession($sessionData['session_id'], 'expired');
            return false;
        }
        
        return true;
    }
    
    public function getCurrentSession(): ?array
    {
        if (!$this->isSessionActive()) {
            return null;
        }
        
        return Session::get('impersonation_session');
    }
    
    public function getRemainingTime(): int
    {
        $sessionData = Session::get('impersonation_session');
        
        if (!$sessionData) {
            return 0;
        }
        
        $endTime = \Carbon\Carbon::parse($sessionData['end_time']);
        $remaining = now()->diffInSeconds($endTime, false);
        
        return max(0, $remaining);
    }
    
    public function isPiiMasked(): bool
    {
        $sessionData = Session::get('impersonation_session');
        return $sessionData ? ($sessionData['pii_masked'] ?? true) : false;
    }
    
    public function getMaskedAreas(): array
    {
        return $this->piiMaskedAreas;
    }
    
    public function shouldBlockWrite(string $action): bool
    {
        $sessionData = Session::get('impersonation_session');
        
        if (!$sessionData) {
            return false;
        }
        
        $blockedActions = [
            'delete_customer',
            'modify_billing',
            'export_data',
            'send_message',
        ];
        
        return in_array($action, $blockedActions);
    }
    
    protected function validateSuperAdminRole(string $adminEmail): void
    {
        $adminRole = session('admin_role', null);
        
        if ($adminRole !== 'super_admin') {
            Log::warning('[Impersonation] Unauthorized impersonation attempt', [
                'admin_email' => $adminEmail,
                'admin_role' => $adminRole,
                'timestamp' => now()->toIso8601String(),
            ]);
            
            throw new \Exception('Only Super Admins can impersonate users');
        }
    }
    
    public function canImpersonate(string $adminEmail): bool
    {
        $adminRole = session('admin_role', null);
        return $adminRole === 'super_admin';
    }
    
    protected function logAdminAuditEvent(string $eventType, array $data): void
    {
        $logEntry = array_merge([
            'event_type' => $eventType,
            'timestamp' => now()->toIso8601String(),
            'audit_type' => 'INTERNAL_ADMIN_ONLY',
            'customer_audit' => false,
        ], $data);
        
        Log::channel('admin_audit')->info('[Impersonation] ' . $eventType, $logEntry);
        
        Log::info('[Impersonation][Admin Audit] ' . $eventType, $logEntry);
    }
    
    public function enableMockMode(): void
    {
        $this->mockMode = true;
    }
    
    public function disableMockMode(): void
    {
        $this->mockMode = false;
    }
}
