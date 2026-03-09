<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to check user permission toggles.
 *
 * Usage in routes:
 *   ->middleware('permission:manage_users')
 *   ->middleware('permission:send_bulk,send_one_to_one')  // ANY of these
 *
 * Evaluates effective permissions (role defaults + user overrides).
 */
class CheckPermission
{
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthenticated',
            ], 401);
        }

        // Account owner bypasses all permission checks
        if ($user->isOwner()) {
            return $next($request);
        }

        // Check if user has ANY of the required permissions
        foreach ($permissions as $permission) {
            if ($user->hasPermission($permission)) {
                return $next($request);
            }
        }

        return response()->json([
            'status' => 'error',
            'message' => 'You do not have permission to perform this action',
        ], 403);
    }
}
