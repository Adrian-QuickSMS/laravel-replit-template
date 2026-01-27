<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\Admin\AdminLoginPolicyService;
use Illuminate\Support\Facades\Log;

class AdminLoginPolicy
{
    protected AdminLoginPolicyService $policyService;

    public function __construct(AdminLoginPolicyService $policyService)
    {
        $this->policyService = $policyService;
    }

    public function handle(Request $request, Closure $next)
    {
        $ipAddress = $request->ip();
        $email = $request->input('email', session('admin_email', 'unknown'));
        
        $policyResult = $this->policyService->validateLoginPolicy($email, $ipAddress);
        
        if (!$policyResult['ip_allowed']) {
            Log::warning('[AdminLoginPolicy] IP blocked login attempt', [
                'email' => $email,
                'ip' => $ipAddress,
                'timestamp' => now()->toIso8601String(),
            ]);
            
            return response()->json([
                'error' => 'Access denied',
            ], 403);
        }
        
        $request->merge([
            'mfa_required' => $policyResult['mfa_required'],
            'allowed_mfa_methods' => $policyResult['allowed_mfa_methods'],
        ]);
        
        return $next($request);
    }
}
