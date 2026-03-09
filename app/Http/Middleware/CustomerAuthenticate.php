<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

        DB::select("SELECT set_config('app.current_tenant_id', ?, false)", [session('customer_tenant_id')]);

        // Bind the authenticated user to Laravel's auth guard so that
        // $request->user(), auth()->user(), and model global scopes work correctly
        $user = \App\Models\User::withoutGlobalScope('tenant')->find(session('customer_user_id'));
        if ($user) {
            Auth::setUser($user);
        }

        return $next($request);
    }
}
