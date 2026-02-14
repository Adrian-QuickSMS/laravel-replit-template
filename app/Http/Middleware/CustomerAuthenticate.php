<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomerAuthenticate
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!session('customer_logged_in') || !session('customer_user_id') || !session('customer_tenant_id')) {
            if ($request->expectsJson()) {
                return response()->json(['status' => 'error', 'message' => 'Unauthenticated'], 401);
            }
            return redirect()->route('auth.login')->with('error', 'Please log in to continue.');
        }

        \Illuminate\Support\Facades\DB::select("SELECT set_config('app.current_tenant_id', ?, false)", [session('customer_tenant_id')]);

        return $next($request);
    }
}
