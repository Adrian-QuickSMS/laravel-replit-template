<?php

namespace App\Traits;

use App\Services\Admin\GovernanceEnforcementService;
use Illuminate\Http\Request;

trait ChecksAdminLocks
{
    protected function checkAdminLock(
        int $accountId,
        ?int $subAccountId,
        string $entityType,
        int $entityId,
        string $action,
        Request $request = null
    ): array {
        $governanceService = app(GovernanceEnforcementService::class);

        $isAdminRoute = $request && str_starts_with($request->path(), 'admin/');
        
        $context = [
            'is_admin' => $isAdminRoute,
            'can_override_admin_lock' => $isAdminRoute && $this->userCanOverrideAdminLock($request),
            'source_ip' => $request ? $request->ip() : null,
            'user_agent' => $request ? $request->userAgent() : null,
            'admin_email' => $request && $request->user() ? $request->user()->email : null,
        ];

        return $governanceService->isActionAllowed(
            $accountId,
            $subAccountId,
            $entityType,
            $entityId,
            $action,
            $context
        );
    }

    protected function getEntityLockInfo(string $entityType, int $entityId): ?array
    {
        $governanceService = app(GovernanceEnforcementService::class);
        return $governanceService->getEntityLockInfo($entityType, $entityId);
    }

    protected function isEntityLockedByAdmin(string $entityType, int $entityId): bool
    {
        $lockInfo = $this->getEntityLockInfo($entityType, $entityId);
        return $lockInfo && $lockInfo['lock_source'] === 'ADMIN';
    }

    protected function applyAdminLock(
        string $entityType,
        int $entityId,
        string $reason,
        int $adminUserId,
        Request $request = null
    ): array {
        $governanceService = app(GovernanceEnforcementService::class);
        
        $context = [
            'source_ip' => $request ? $request->ip() : null,
            'user_agent' => $request ? $request->userAgent() : null,
            'admin_email' => $request && $request->user() ? $request->user()->email : null,
        ];

        return $governanceService->applyAdminLock(
            $entityType,
            $entityId,
            $reason,
            $adminUserId,
            $context
        );
    }

    protected function removeAdminLock(
        string $entityType,
        int $entityId,
        int $adminUserId,
        string $reason = null,
        Request $request = null
    ): array {
        $governanceService = app(GovernanceEnforcementService::class);
        
        $context = [
            'source_ip' => $request ? $request->ip() : null,
            'user_agent' => $request ? $request->userAgent() : null,
            'admin_email' => $request && $request->user() ? $request->user()->email : null,
        ];

        return $governanceService->removeAdminLock(
            $entityType,
            $entityId,
            $adminUserId,
            $reason,
            $context
        );
    }

    protected function getLockedEntitiesForAccount(int $accountId, ?string $entityType = null): array
    {
        $governanceService = app(GovernanceEnforcementService::class);
        return $governanceService->getLockedEntitiesForAccount($accountId, $entityType);
    }

    private function userCanOverrideAdminLock(?Request $request): bool
    {
        if (!$request || !$request->user()) {
            return false;
        }

        $adminRoles = ['super_admin', 'compliance_admin', 'security_admin'];
        $userRole = $request->user()->admin_role ?? $request->user()->role ?? null;
        
        return in_array($userRole, $adminRoles);
    }
}
