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
            if (config('admin.dev_autologin', false) && config('app.env') === 'local') {
                $devAdmin = \App\Models\AdminUser::where('role', 'super_admin')
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
                        'hr_role' => $devAdmin->hr_role ?? 'none',
                        'last_activity' => now()->timestamp,
                        'ip_address' => $request->ip(),
                    ]);
                    session()->put('admin_user_email', $devAdmin->email);
                    return response(view('admin.landing'), 200);
                }
            }

            if ($request->expectsJson()) {
                return response()->json(['status' => 'error', 'message' => 'Unauthenticated'], 401);
            }
            return redirect()->route('auth.login')->with('error', 'Please log in to continue.');
        }

        DB::select("SELECT set_config('app.current_tenant_id', ?, false)", [session('customer_tenant_id')]);

        // Bind the authenticated user to Laravel's auth guard so that
        // $request->user(), auth()->user(), and model global scopes work correctly
        $user = \App\Models\User::withoutGlobalScope('tenant')->find(session('customer_user_id'));
        if (!$user || $user->status !== 'active') {
            session()->flush();
            if ($request->expectsJson()) {
                return response()->json(['status' => 'error', 'message' => 'Unauthenticated'], 401);
            }
            return redirect()->route('auth.login')->with('error', 'Your account is no longer active.');
        }

        Auth::setUser($user);

        return $next($request);
    }
}
