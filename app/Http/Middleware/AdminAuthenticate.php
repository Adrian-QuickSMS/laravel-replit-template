<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\AdminUser;
use App\Services\Admin\AdminAuditService;

class AdminAuthenticate
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->isCustomerSession($request)) {
            return $this->handleCustomerAccessAttempt($request);
        }
        
        if (config('admin.ip_allowlist.enabled', false)) {
            if (!$this->isIpAllowed($request->ip())) {
                $this->logSecurityEvent('ADMIN_ACCESS_IP_BLOCKED', [
                    'ip' => $request->ip(),
                    'path' => $request->path(),
                    'user_agent' => $request->userAgent()
                ]);
                
                abort(403, 'Access denied from this IP address');
            }
        }
        
        if ((config('app.env') === 'local' || config('app.debug') === true)) {
            if (!session()->has('admin_auth') || session('admin_auth.authenticated') !== true) {
                $devAdmin = AdminUser::where('role', 'super_admin')
                    ->where('status', 'active')
                    ->first();

                if ($devAdmin) {
                    session()->put('admin_auth', [
                        'authenticated' => true,
                        'mfa_verified' => true,
                        'admin_id' => $devAdmin->id,
                        'email' => $devAdmin->email,
                        'name' => $devAdmin->full_name,
                        'role' => $devAdmin->role,
                        'last_activity' => now()->timestamp,
                        'ip_address' => $request->ip(),
                    ]);
                    session()->put('admin_user_email', $devAdmin->email);
                }
                // If no DB user exists, do NOT create a fake session — redirect to login instead
            }
        }
        
        $adminSession = session('admin_auth');
        
        if (!$adminSession || !isset($adminSession['authenticated']) || $adminSession['authenticated'] !== true) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => 'Admin authentication required'], 401);
            }
            return redirect()->route('admin.login');
        }
        
        if (!$this->isWhitelistedUser($adminSession['email'] ?? '')) {
            $this->logSecurityEvent('ADMIN_ACCESS_UNAUTHORIZED_USER', [
                'email' => $adminSession['email'] ?? 'unknown',
                'ip' => $request->ip(),
                'path' => $request->path(),
                'session_id' => session()->getId()
            ]);
            
            session()->forget('admin_auth');
            
            return $this->redirectToCustomerPortal($request, 'unauthorized_user');
        }
        
        if ($this->isSessionExpired($adminSession)) {
            $this->logSecurityEvent('ADMIN_SESSION_EXPIRED', [
                'email' => $adminSession['email'] ?? 'unknown',
                'last_activity' => $adminSession['last_activity'] ?? 0
            ]);
            
            session()->forget('admin_auth');
            return redirect()->route('admin.login')->with('error', 'Admin session expired. Please login again.');
        }
        
        if (!$this->isMfaVerified($adminSession)) {
            if ($this->isMfaSetupRequired($adminSession)) {
                return redirect()->route('admin.mfa.setup');
            }
            return redirect()->route('admin.mfa.verify');
        }

        // Revalidate admin user is still active on each request
        $adminUser = AdminUser::find($adminSession['admin_id'] ?? null);
        if (!$adminUser || $adminUser->status !== 'active' || $adminUser->isLocked()) {
            session()->forget('admin_auth');
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => 'Account suspended or locked'], 403);
            }
            return redirect()->route('admin.login')->with('error', 'Your account has been suspended or locked.');
        }

        session()->put('admin_auth.last_activity', now()->timestamp);
        session()->put('admin_auth.ip_address', $request->ip());

        return $next($request);
    }
    
    protected function isCustomerSession(Request $request): bool
    {
        $customerSession = session('customer_auth');
        if ($customerSession && isset($customerSession['authenticated']) && $customerSession['authenticated'] === true) {
            $adminSession = session('admin_auth');
            if (!$adminSession || !isset($adminSession['authenticated']) || $adminSession['authenticated'] !== true) {
                return true;
            }
        }
        
        return false;
    }
    
    protected function handleCustomerAccessAttempt(Request $request): Response
    {
        $customerEmail = session('customer_auth.email', 'unknown');
        
        $this->logSecurityEvent('CUSTOMER_ADMIN_ACCESS_ATTEMPT', [
            'customer_email' => $customerEmail,
            'ip' => $request->ip(),
            'path' => $request->path(),
            'user_agent' => $request->userAgent(),
            'referer' => $request->header('referer'),
            'session_id' => session()->getId()
        ]);
        
        return $this->redirectToCustomerPortal($request, 'customer_access_attempt');
    }
    
    protected function redirectToCustomerPortal(Request $request, string $reason): Response
    {
        $redirectPath = config('admin.security.customer_portal_redirect', '/dashboard');
        
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'error' => 'Access denied',
                'redirect' => $redirectPath
            ], 403);
        }
        
        return redirect($redirectPath)->with('error', 'You do not have permission to access that area.');
    }
    
    protected function isWhitelistedUser(string $email): bool
    {
        if (empty($email)) {
            return false;
        }

        try {
            // Primary auth source: admin_users database table
            $exists = AdminUser::where('email', strtolower($email))
                ->where('status', 'active')
                ->exists();

            if ($exists) {
                return true;
            }

            // Fallback: check whitelisted email domains
            $whitelistedDomains = config('admin.security.whitelisted_domains', []);
            $emailParts = explode('@', $email);
            if (count($emailParts) === 2) {
                $domain = strtolower($emailParts[1]);
                foreach ($whitelistedDomains as $whitelistedDomain) {
                    if ($domain === strtolower($whitelistedDomain)) {
                        return true;
                    }
                }
            }

            return false;
        } catch (\Exception $e) {
            \Log::error('[AdminAuth] DB lookup failed during whitelist check', ['error' => $e->getMessage()]);
            return false;
        }
    }
    
    protected function isIpAllowed(string $ip): bool
    {
        $allowedIps = config('admin.ip_allowlist.ips', []);
        $allowedCidrs = config('admin.ip_allowlist.cidrs', []);
        
        if (empty($allowedIps) && empty($allowedCidrs)) {
            return true;
        }
        
        if (in_array($ip, $allowedIps)) {
            return true;
        }
        
        foreach ($allowedCidrs as $cidr) {
            if ($this->ipInCidr($ip, $cidr)) {
                return true;
            }
        }
        
        return false;
    }
    
    protected function ipInCidr(string $ip, string $cidr): bool
    {
        if (!str_contains($cidr, '/')) {
            return $ip === $cidr;
        }

        list($subnet, $mask) = explode('/', $cidr);
        $mask = (int) $mask;
        
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $ip = ip2long($ip);
            $subnet = ip2long($subnet);
            $mask = -1 << (32 - $mask);
            $subnet &= $mask;
            return ($ip & $mask) == $subnet;
        }
        
        return false;
    }
    
    protected function isSessionExpired(array $adminSession): bool
    {
        $lastActivity = $adminSession['last_activity'] ?? 0;
        $timeout = config('admin.session_timeout', 3600);
        
        return (now()->timestamp - $lastActivity) > $timeout;
    }
    
    protected function isMfaVerified(array $adminSession): bool
    {
        return isset($adminSession['mfa_verified']) && $adminSession['mfa_verified'] === true;
    }
    
    protected function isMfaSetupRequired(array $adminSession): bool
    {
        return isset($adminSession['mfa_setup_required']) && $adminSession['mfa_setup_required'] === true;
    }
    
    protected function logSecurityEvent(string $eventCode, array $data): void
    {
        try {
            AdminAuditService::log($eventCode, array_merge($data, [
                'timestamp' => now()->toIso8601String(),
                'severity' => $this->getEventSeverity($eventCode)
            ]));
        } catch (\Exception $e) {
            \Log::error('[AdminAuth] Failed to log security event: ' . $e->getMessage(), [
                'event_code' => $eventCode,
                'data' => $data
            ]);
        }
    }
    
    protected function getEventSeverity(string $eventCode): string
    {
        $criticalEvents = ['CUSTOMER_ADMIN_ACCESS_ATTEMPT', 'ADMIN_ACCESS_UNAUTHORIZED_USER'];
        $highEvents = ['ADMIN_ACCESS_IP_BLOCKED'];
        
        if (in_array($eventCode, $criticalEvents)) {
            return 'CRITICAL';
        }
        if (in_array($eventCode, $highEvents)) {
            return 'HIGH';
        }
        
        return 'MEDIUM';
    }
}
