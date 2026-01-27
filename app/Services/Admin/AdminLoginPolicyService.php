<?php

namespace App\Services\Admin;

use Illuminate\Support\Facades\Log;

class AdminLoginPolicyService
{
    protected bool $mockMode = true;
    
    protected array $mockIpAllowlist = [
        '127.0.0.1/32',
        '10.0.0.0/8',
        '172.16.0.0/12',
        '192.168.0.0/16',
        '::1/128',
    ];
    
    protected array $mockMfaPolicy = [
        'mfa_required' => true,
        'allowed_methods' => ['Authenticator', 'SMS', 'Both'],
        'default_method' => 'Authenticator',
        'grace_period_minutes' => 0,
    ];
    
    public function __construct()
    {
        $this->mockMode = config('admin.policy_mock_mode', true);
    }
    
    public function validateLoginPolicy(string $email, string $ipAddress): array
    {
        $result = [
            'allowed' => true,
            'ip_allowed' => true,
            'mfa_required' => true,
            'allowed_mfa_methods' => ['Authenticator', 'SMS', 'Both'],
            'errors' => [],
            'security_events' => [],
        ];
        
        $ipCheck = $this->checkIpAllowlist($ipAddress);
        if (!$ipCheck['allowed']) {
            $result['allowed'] = false;
            $result['ip_allowed'] = false;
            $result['errors'][] = 'Access denied';
            $result['security_events'][] = [
                'type' => 'IP_BLOCKED_LOGIN',
                'email' => $email,
                'ip' => $ipAddress,
                'timestamp' => now()->toIso8601String(),
                'details' => 'Login blocked: IP not in allowlist',
            ];
            
            $this->logSecurityEvent('IP_BLOCKED_LOGIN', [
                'email' => $email,
                'ip' => $ipAddress,
                'reason' => 'IP not in admin allowlist',
            ]);
            
            return $result;
        }
        
        $mfaPolicy = $this->getMfaPolicy($email);
        $result['mfa_required'] = $mfaPolicy['mfa_required'];
        $result['allowed_mfa_methods'] = $mfaPolicy['allowed_methods'];
        
        return $result;
    }
    
    public function checkIpAllowlist(string $ipAddress): array
    {
        $allowlist = $this->getIpAllowlist();
        
        foreach ($allowlist as $cidr) {
            if ($this->ipInCidr($ipAddress, $cidr)) {
                return [
                    'allowed' => true,
                    'matched_rule' => $cidr,
                ];
            }
        }
        
        return [
            'allowed' => false,
            'matched_rule' => null,
        ];
    }
    
    public function getMfaPolicy(string $email): array
    {
        if ($this->mockMode) {
            return $this->mockMfaPolicy;
        }
        
        return $this->mockMfaPolicy;
    }
    
    public function getIpAllowlist(): array
    {
        if ($this->mockMode) {
            return $this->mockIpAllowlist;
        }
        
        return $this->mockIpAllowlist;
    }
    
    public function validateMfaMethod(string $email, string $method): bool
    {
        $policy = $this->getMfaPolicy($email);
        $allowedMethods = $policy['allowed_methods'];
        
        if (in_array('Both', $allowedMethods)) {
            return in_array($method, ['Authenticator', 'SMS', 'Both']);
        }
        
        return in_array($method, $allowedMethods);
    }
    
    public function isMfaRequired(string $email): bool
    {
        $policy = $this->getMfaPolicy($email);
        return $policy['mfa_required'] ?? true;
    }
    
    public function getAllowedMfaMethods(string $email): array
    {
        $policy = $this->getMfaPolicy($email);
        return $policy['allowed_methods'] ?? ['Authenticator'];
    }
    
    protected function ipInCidr(string $ip, string $cidr): bool
    {
        if (strpos($cidr, '/') === false) {
            return $ip === $cidr;
        }
        
        list($subnet, $bits) = explode('/', $cidr);
        
        if ($bits == 0) {
            return true;
        }
        
        $ip = ip2long($ip);
        $subnet = ip2long($subnet);
        
        if ($ip === false || $subnet === false) {
            return false;
        }
        
        $mask = -1 << (32 - $bits);
        $subnet &= $mask;
        
        return ($ip & $mask) == $subnet;
    }
    
    protected function logSecurityEvent(string $eventType, array $data): void
    {
        $logEntry = array_merge([
            'event_type' => $eventType,
            'timestamp' => now()->toIso8601String(),
            'audit_type' => 'INTERNAL_ADMIN_SECURITY',
        ], $data);
        
        Log::channel('security')->warning('[AdminLoginPolicy] ' . $eventType, $logEntry);
        
        Log::info('[AdminLoginPolicy][Security Event] ' . $eventType, $logEntry);
    }
    
    public function setMockIpAllowlist(array $allowlist): void
    {
        $this->mockIpAllowlist = $allowlist;
    }
    
    public function setMockMfaPolicy(array $policy): void
    {
        $this->mockMfaPolicy = array_merge($this->mockMfaPolicy, $policy);
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
