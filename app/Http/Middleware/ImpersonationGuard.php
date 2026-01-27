<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\Admin\ImpersonationService;
use Illuminate\Support\Facades\Log;

class ImpersonationGuard
{
    protected ImpersonationService $impersonationService;

    public function __construct(ImpersonationService $impersonationService)
    {
        $this->impersonationService = $impersonationService;
    }

    public function handle(Request $request, Closure $next)
    {
        $session = $this->impersonationService->getCurrentSession();
        
        if ($session) {
            $request->merge([
                'impersonation_active' => true,
                'impersonation_session' => $session,
                'pii_masked' => true,
            ]);
            
            view()->share('impersonationActive', true);
            view()->share('impersonationSession', $session);
            view()->share('piiMasked', true);
        } else {
            $request->merge([
                'impersonation_active' => false,
            ]);
            view()->share('impersonationActive', false);
        }
        
        return $next($request);
    }
}
