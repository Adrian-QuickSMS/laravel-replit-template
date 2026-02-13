<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Customer Portal Authentication Middleware
 *
 * Ensures the session-based customer login is active before allowing
 * access to authenticated portal routes. Redirects to login if not.
 */
class CustomerAuthenticate
{
    public function handle(Request $request, Closure $next)
    {
        if (!session('customer_logged_in') || !session('customer_user_id') || !session('customer_tenant_id')) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['status' => 'error', 'message' => 'Not authenticated'], 401);
            }
            return redirect()->route('auth.login');
        }

        return $next($request);
    }
}
