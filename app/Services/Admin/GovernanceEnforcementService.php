<?php

namespace App\Services\Admin;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GovernanceEnforcementService
{
    private const CACHE_TTL_SECONDS = 30;
    private const CACHE_PREFIX = 'governance_';

    private const ENTITY_TABLE_MAP = [
        'template' => 'templates',
        'sender_id' => 'sender_ids',
        'campaign' => 'campaigns',
        'api_connection' => 'api_connections',
        'number' => 'numbers',
        'rcs_agent' => 'rcs_agents',
        'email_to_sms_config' => 'email_to_sms_configs',
    ];

    private const LOCKABLE_ACTIONS = [
        'update', 'delete', 'activate', 'deactivate', 'send', 
        'reactivate', 'unsuspend', 'modify', 'enable', 'disable'
    ];

    public function isActionAllowed(
        int $accountId,
        ?int $subAccountId,
        string $entityType,
        int $entityId,
        string $action,
        array $context = []
    ): array {
        $cacheKey = self::CACHE_PREFIX . "action_{$entityType}_{$entityId}_{$action}";
        
        $lockInfo = $this->getEntityLockInfo($entityType, $entityId);
        
        if ($lockInfo === null) {
            return [
                'allowed' => true,
                'reason' => null,
                'lock_source' => 'NONE',
                'lock_reason' => null,
                'blocking_rule_id' => null,
            ];
        }

        if ($lockInfo['lock_source'] === 'NONE') {
            return [
                'allowed' => true,
                'reason' => null,
                'lock_source' => 'NONE',
                'lock_reason' => null,
                'blocking_rule_id' => null,
            ];
        }

        $isAdminContext = $context['is_admin'] ?? false;
        $canOverride = $context['can_override_admin_lock'] ?? false;

        if ($lockInfo['lock_source'] === 'ADMIN') {
            if (!$isAdminContext) {
                return [
                    'allowed' => false,
                    'reason' => 'This item has been suspended by QuickSMS Admin. Please contact support for assistance.',
                    'lock_source' => 'ADMIN',
                    'lock_reason' => $lockInfo['lock_reason'],
                    'blocking_rule_id' => null,
                    'locked_at' => $lockInfo['locked_at'],
                    'locked_by' => $lockInfo['locked_by'],
                ];
            }

            $unlockActions = ['unsuspend', 'reactivate', 'enable', 'unlock'];
            if (in_array($action, $unlockActions) && !$canOverride) {
                return [
                    'allowed' => false,
                    'reason' => 'Admin lock can only be removed by an authorized admin user.',
                    'lock_source' => 'ADMIN',
                    'lock_reason' => $lockInfo['lock_reason'],
                    'blocking_rule_id' => null,
                ];
            }

            return [
                'allowed' => true,
                'reason' => 'Admin user can perform actions on admin-locked entities.',
                'lock_source' => 'ADMIN',
                'lock_reason' => $lockInfo['lock_reason'],
                'blocking_rule_id' => null,
            ];
        }

        if ($lockInfo['lock_source'] === 'CUSTOMER') {
            if ($isAdminContext) {
                return [
                    'allowed' => true,
                    'reason' => 'Admin can override customer lock.',
                    'lock_source' => 'CUSTOMER',
                    'lock_reason' => $lockInfo['lock_reason'],
                    'blocking_rule_id' => null,
                ];
            }

            return [
                'allowed' => false,
                'reason' => 'This item is currently locked.',
                'lock_source' => 'CUSTOMER',
                'lock_reason' => $lockInfo['lock_reason'],
                'blocking_rule_id' => null,
            ];
        }

        return [
            'allowed' => false,
            'reason' => 'Entity is locked with unknown lock source.',
            'lock_source' => $lockInfo['lock_source'],
            'lock_reason' => $lockInfo['lock_reason'],
            'blocking_rule_id' => null,
        ];
    }

    public function getEntityLockInfo(string $entityType, int $entityId): ?array
    {
        $tableName = self::ENTITY_TABLE_MAP[$entityType] ?? null;
        
        if ($tableName === null) {
            Log::warning("[GovernanceEnforcementService] Unknown entity type: {$entityType}");
            return null;
        }

        try {
            $entity = DB::table($tableName)
                ->select(['lock_source', 'lock_reason', 'locked_at', 'locked_by'])
                ->where('id', $entityId)
                ->first();

            if ($entity === null) {
                return null;
            }

            return [
                'lock_source' => $entity->lock_source ?? 'NONE',
                'lock_reason' => $entity->lock_reason,
                'locked_at' => $entity->locked_at,
                'locked_by' => $entity->locked_by,
            ];
        } catch (\Exception $e) {
            Log::error("[GovernanceEnforcementService] Error fetching lock info: " . $e->getMessage());
            return [
                'lock_source' => 'NONE',
                'lock_reason' => null,
                'locked_at' => null,
                'locked_by' => null,
            ];
        }
    }

    public function applyAdminLock(
        string $entityType,
        int $entityId,
        string $reason,
        int $adminUserId,
        array $context = []
    ): array {
        $tableName = self::ENTITY_TABLE_MAP[$entityType] ?? null;
        
        if ($tableName === null) {
            return [
                'success' => false,
                'error' => "Unknown entity type: {$entityType}",
            ];
        }

        try {
            $beforeState = DB::table($tableName)->where('id', $entityId)->first();
            
            if ($beforeState === null) {
                return [
                    'success' => false,
                    'error' => 'Entity not found.',
                ];
            }

            DB::table($tableName)
                ->where('id', $entityId)
                ->update([
                    'lock_source' => 'ADMIN',
                    'lock_reason' => $reason,
                    'locked_at' => now(),
                    'locked_by' => $adminUserId,
                    'updated_at' => now(),
                ]);

            $this->logGovernanceEvent(
                'ADMIN_LOCK_APPLIED',
                $entityType,
                $entityId,
                $adminUserId,
                'ADMIN',
                $context['admin_email'] ?? null,
                (array) $beforeState,
                [
                    'lock_source' => 'ADMIN',
                    'lock_reason' => $reason,
                    'locked_at' => now()->toIso8601String(),
                    'locked_by' => $adminUserId,
                ],
                $reason,
                $context['source_ip'] ?? null,
                $context['user_agent'] ?? null
            );

            $this->invalidateLockCache($entityType, $entityId);

            return [
                'success' => true,
                'message' => 'Admin lock applied successfully.',
            ];
        } catch (\Exception $e) {
            Log::error("[GovernanceEnforcementService] Error applying admin lock: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Failed to apply admin lock: ' . $e->getMessage(),
            ];
        }
    }

    public function removeAdminLock(
        string $entityType,
        int $entityId,
        int $adminUserId,
        string $reason = null,
        array $context = []
    ): array {
        $tableName = self::ENTITY_TABLE_MAP[$entityType] ?? null;
        
        if ($tableName === null) {
            return [
                'success' => false,
                'error' => "Unknown entity type: {$entityType}",
            ];
        }

        try {
            $beforeState = DB::table($tableName)->where('id', $entityId)->first();
            
            if ($beforeState === null) {
                return [
                    'success' => false,
                    'error' => 'Entity not found.',
                ];
            }

            if (($beforeState->lock_source ?? 'NONE') !== 'ADMIN') {
                return [
                    'success' => false,
                    'error' => 'Entity is not locked by admin.',
                ];
            }

            DB::table($tableName)
                ->where('id', $entityId)
                ->update([
                    'lock_source' => 'NONE',
                    'lock_reason' => null,
                    'locked_at' => null,
                    'locked_by' => null,
                    'updated_at' => now(),
                ]);

            $this->logGovernanceEvent(
                'ADMIN_LOCK_REMOVED',
                $entityType,
                $entityId,
                $adminUserId,
                'ADMIN',
                $context['admin_email'] ?? null,
                (array) $beforeState,
                [
                    'lock_source' => 'NONE',
                    'lock_reason' => null,
                    'locked_at' => null,
                    'locked_by' => null,
                ],
                $reason,
                $context['source_ip'] ?? null,
                $context['user_agent'] ?? null
            );

            $this->invalidateLockCache($entityType, $entityId);

            return [
                'success' => true,
                'message' => 'Admin lock removed successfully.',
            ];
        } catch (\Exception $e) {
            Log::error("[GovernanceEnforcementService] Error removing admin lock: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Failed to remove admin lock: ' . $e->getMessage(),
            ];
        }
    }

    public function getLockedEntitiesForAccount(int $accountId, ?string $entityType = null): array
    {
        $results = [];
        $tables = $entityType ? [$entityType => self::ENTITY_TABLE_MAP[$entityType]] : self::ENTITY_TABLE_MAP;

        foreach ($tables as $type => $tableName) {
            if ($tableName === null) continue;

            try {
                $locked = DB::table($tableName)
                    ->where('account_id', $accountId)
                    ->where('lock_source', 'ADMIN')
                    ->select(['id', 'lock_source', 'lock_reason', 'locked_at', 'locked_by'])
                    ->get();

                foreach ($locked as $entity) {
                    $results[] = [
                        'entity_type' => $type,
                        'entity_id' => $entity->id,
                        'lock_source' => $entity->lock_source,
                        'lock_reason' => $entity->lock_reason,
                        'locked_at' => $entity->locked_at,
                        'locked_by' => $entity->locked_by,
                    ];
                }
            } catch (\Exception $e) {
                Log::warning("[GovernanceEnforcementService] Could not fetch locked entities from {$tableName}: " . $e->getMessage());
            }
        }

        return $results;
    }

    public function getApprovalQueueCounts(): array
    {
        $counts = [
            'senderid_requests' => 0,
            'rcs_agent_requests' => 0,
            'country_requests' => 0,
            'total_pending' => 0,
        ];

        $pendingStatuses = ['SUBMITTED', 'IN_REVIEW', 'RESUBMITTED', 'VALIDATION_IN_PROGRESS'];

        foreach (['senderid_requests', 'rcs_agent_requests', 'country_requests'] as $table) {
            try {
                if (\Schema::hasTable($table)) {
                    $count = DB::table($table)
                        ->whereIn('workflow_status', $pendingStatuses)
                        ->count();
                    $counts[$table] = $count;
                    $counts['total_pending'] += $count;
                }
            } catch (\Exception $e) {
                Log::warning("[GovernanceEnforcementService] Could not count {$table}: " . $e->getMessage());
            }
        }

        return $counts;
    }

    private function logGovernanceEvent(
        string $eventType,
        string $entityType,
        int $entityId,
        int $actorId,
        string $actorType,
        ?string $actorEmail,
        ?array $beforeState,
        ?array $afterState,
        ?string $reason,
        ?string $sourceIp,
        ?string $userAgent
    ): void {
        try {
            $accountId = $beforeState['account_id'] ?? $afterState['account_id'] ?? null;
            $subAccountId = $beforeState['sub_account_id'] ?? $afterState['sub_account_id'] ?? null;

            DB::table('governance_audit_events')->insert([
                'event_uuid' => Str::uuid()->toString(),
                'event_type' => $eventType,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'account_id' => $accountId,
                'sub_account_id' => $subAccountId,
                'actor_id' => $actorId,
                'actor_type' => $actorType,
                'actor_email' => $actorEmail,
                'before_state' => $beforeState ? json_encode($this->sanitizeForAudit($beforeState)) : null,
                'after_state' => $afterState ? json_encode($this->sanitizeForAudit($afterState)) : null,
                'reason' => $reason,
                'source_ip' => $sourceIp,
                'user_agent' => $userAgent,
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error("[GovernanceEnforcementService] Failed to log governance event: " . $e->getMessage());
        }
    }

    private function sanitizeForAudit(array $data): array
    {
        $sensitiveKeys = ['password', 'token', 'secret', 'api_key', 'private_key'];
        
        foreach ($sensitiveKeys as $key) {
            if (isset($data[$key])) {
                $data[$key] = '[REDACTED]';
            }
        }

        return $data;
    }

    private function invalidateLockCache(string $entityType, int $entityId): void
    {
        $cachePattern = self::CACHE_PREFIX . "action_{$entityType}_{$entityId}_*";
        Cache::forget($cachePattern);
    }
}
