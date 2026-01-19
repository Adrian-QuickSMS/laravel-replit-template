<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminAuthenticate
{
    public function handle(Request $request, Closure $next): Response
    {
        $adminSession = session('admin_auth');
        
        if (!$adminSession || !isset($adminSession['authenticated']) || $adminSession['authenticated'] !== true) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => 'Admin authentication required'], 401);
            }
            return redirect()->route('admin.login');
        }
        
        if ($this->isSessionExpired($adminSession)) {
            session()->forget('admin_auth');
            return redirect()->route('admin.login')->with('error', 'Admin session expired. Please login again.');
        }
        
        if (!$this->isMfaVerified($adminSession)) {
            if ($this->isMfaSetupRequired($adminSession)) {
                return redirect()->route('admin.mfa.setup');
            }
            return redirect()->route('admin.mfa.verify');
        }
        
        session()->put('admin_auth.last_activity', now()->timestamp);
        
        return $next($request);
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
}
