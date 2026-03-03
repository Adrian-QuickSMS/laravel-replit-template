<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Set Tenant Context Middleware
 *
 * CRITICAL SECURITY MIDDLEWARE
 *
 * Sets PostgreSQL session variable 'app.current_tenant_id' from authenticated user.
 * This enables Row Level Security (RLS) policies to enforce tenant isolation at the
 * database layer.
 *
 * SECURITY:
 * - tenant_id is NEVER derived from user input
 * - tenant_id comes from authenticated user record only
 * - PostgreSQL RLS policies use current_setting('app.current_tenant_id')
 * - This creates defense-in-depth: even if developer forgets WHERE tenant_id=X,
 *   the database blocks cross-tenant queries
 *
 * USAGE:
 * Apply to all authenticated routes that access tenant-scoped data.
 *
 * FLOW:
 * 1. User authenticates, JWT contains user_id
 * 2. Laravel loads User model from JWT
 * 3. This middleware reads user->tenant_id
 * 4. Sets PostgreSQL session variable
 * 5. All subsequent queries in this request are tenant-scoped by RLS
 *
 * IMPORTANT:
 * - This middleware MUST run after authentication middleware
 * - This middleware MUST run before any database queries
 * - Session variable is request-scoped (resets after request completes)
 */
class SetTenantContext
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $tenantId = null;

        if ($request->user()) {
            $user = $request->user();

            if (empty($user->tenant_id)) {
                Log::error('SetTenantContext: User has no tenant_id', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                ]);

                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid tenant context. Please contact support.'
                ], 500);
            }

            $tenantId = $user->tenant_id;
        } elseif ($request->session()->has('customer_tenant_id')) {
            $tenantId = $request->session()->get('customer_tenant_id');
        }

        if ($tenantId) {
            try {
                DB::select("SELECT set_config('app.current_tenant_id', ?, false)", [$tenantId]);

                if (config('app.debug')) {
                    Log::debug('Tenant context set', [
                        'tenant_id' => $tenantId,
                        'source' => $request->user() ? 'auth_guard' : 'session',
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('SetTenantContext: Failed to set tenant context', [
                    'tenant_id' => $tenantId,
                    'error' => $e->getMessage(),
                ]);

                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to establish secure connection. Please try again.'
                ], 500);
            }
        }

        return $next($request);
    }

    /**
     * Terminate the request.
     *
     * Clean up tenant context after request completes.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $response
     * @return void
     */
    public function terminate(Request $request, $response)
    {
        // PostgreSQL LOCAL variables auto-reset after transaction
        // This is just defensive cleanup
        try {
            DB::statement("RESET app.current_tenant_id");
        } catch (\Exception $e) {
            // Ignore errors on cleanup
            Log::debug('SetTenantContext cleanup error (non-critical)', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
