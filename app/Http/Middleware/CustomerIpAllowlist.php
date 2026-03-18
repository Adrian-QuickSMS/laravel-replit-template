<?php

namespace App\Http\Middleware;

use App\Models\AccountAuditLog;
use App\Services\IpAllowlistService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * CustomerIpAllowlist — checks every authenticated customer request against
 * the account's IP allowlist.
 *
 * Runs AFTER CustomerAuthenticate (requires session tenant_id).
 * Uses cached allowlist entries (60s TTL) to minimise DB load.
 */
class CustomerIpAllowlist
{
    public function __construct(
        private IpAllowlistService $ipAllowlistService,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $accountId = session('customer_tenant_id');

        if (!$accountId) {
            return $next($request);
        }

        if (!$this->ipAllowlistService->isEnabled($accountId)) {
            return $next($request);
        }

        $clientIp = $request->ip();

        if ($this->ipAllowlistService->isIpAllowed($accountId, $clientIp)) {
            return $next($request);
        }

        // IP not in allowlist — log and reject
        try {
            AccountAuditLog::record(
                accountId: $accountId,
                action: 'ip_allowlist_blocked',
                userId: session('customer_user_id'),
                details: "Login attempt blocked: IP {$clientIp} not in allowlist",
                metadata: [
                    'blocked_ip' => $clientIp,
                    'path' => $request->path(),
                    'user_agent' => $request->userAgent(),
                ],
            );
        } catch (\Throwable $e) {
            // Audit failure must not block the response
        }

        // Invalidate session (regenerates ID to prevent session fixation) and force re-login
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Access denied: your IP address is not in the allowed list for this account.',
            ], 403);
        }

        return redirect()->route('auth.login')
            ->with('error', 'Access denied: your IP address is not in the allowed list for this account.');
    }
}
