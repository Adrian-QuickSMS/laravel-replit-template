<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\Admin\GovernanceEnforcementService;
use Symfony\Component\HttpFoundation\Response;

class EnforceAdminLocks
{
    protected GovernanceEnforcementService $governanceService;

    public function __construct(GovernanceEnforcementService $governanceService)
    {
        $this->governanceService = $governanceService;
    }

    public function handle(Request $request, Closure $next, string $entityType = null): Response
    {
        if (!$entityType) {
            return $next($request);
        }

        $entityId = $request->route('id') ?? $request->input('entity_id');
        $accountId = $request->user()->account_id ?? $request->input('account_id');
        $subAccountId = $request->input('sub_account_id');
        
        if (!$entityId || !$accountId) {
            return $next($request);
        }

        $action = $this->determineAction($request);
        
        $isAdminRoute = str_starts_with($request->path(), 'admin/');
        
        $context = [
            'is_admin' => $isAdminRoute,
            'can_override_admin_lock' => $isAdminRoute && $this->canOverrideAdminLock($request),
            'source_ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ];

        $result = $this->governanceService->isActionAllowed(
            (int) $accountId,
            $subAccountId ? (int) $subAccountId : null,
            $entityType,
            (int) $entityId,
            $action,
            $context
        );

        if (!$result['allowed']) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => $result['reason'],
                    'lock_source' => $result['lock_source'],
                    'lock_reason' => $result['lock_reason'],
                ], 403);
            }

            return redirect()->back()->with('error', $result['reason']);
        }

        return $next($request);
    }

    private function determineAction(Request $request): string
    {
        $method = $request->method();
        
        return match($method) {
            'PUT', 'PATCH' => 'update',
            'DELETE' => 'delete',
            'POST' => $this->getPostAction($request),
            default => 'view',
        };
    }

    private function getPostAction(Request $request): string
    {
        $action = $request->input('action') ?? $request->route('action') ?? 'create';
        
        $actionMap = [
            'activate' => 'activate',
            'deactivate' => 'deactivate',
            'suspend' => 'suspend',
            'unsuspend' => 'unsuspend',
            'enable' => 'enable',
            'disable' => 'disable',
            'send' => 'send',
            'reactivate' => 'reactivate',
        ];

        return $actionMap[$action] ?? 'modify';
    }

    private function canOverrideAdminLock(Request $request): bool
    {
        $user = $request->user();
        
        if (!$user) {
            return false;
        }

        $adminRoles = ['super_admin', 'compliance_admin', 'security_admin'];
        $userRole = $user->admin_role ?? $user->role ?? null;
        
        return in_array($userRole, $adminRoles);
    }
}
