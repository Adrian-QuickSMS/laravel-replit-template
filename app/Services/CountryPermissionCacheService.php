<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Three-tier country permission cache: L1 (in-process array) → L2 (Redis) → L3 (PostgreSQL).
 *
 * L1: Per-request array, zero-cost repeated lookups within a single HTTP request.
 * L2: Redis with 5-minute TTL, shared across workers.
 * L3: PostgreSQL query (country_controls + country_control_overrides + sub_account_country_permissions).
 */
class CountryPermissionCacheService
{
    private const L2_TTL_SECONDS = 300; // 5 minutes
    private const L2_PREFIX = 'country_perm:';

    /** @var array<string, array> L1 in-process cache, keyed by "{accountId}:{subAccountId}" */
    private array $l1 = [];

    /**
     * Get the resolved permission for a country.
     *
     * Resolution order (most specific wins):
     *  1. Sub-account override (sub_account_country_permissions)
     *  2. Account override (country_control_overrides)
     *  3. Global default (country_controls.default_status)
     *
     * @return string 'allowed'|'blocked'|'restricted'
     */
    public function getPermission(string $accountId, ?string $subAccountId, string $countryIso): string
    {
        $permissions = $this->getPermissionsForEntity($accountId, $subAccountId);

        return $permissions[$countryIso] ?? 'blocked';
    }

    /**
     * Check if sending to a country is allowed.
     */
    public function isCountryAllowed(string $accountId, ?string $subAccountId, string $countryIso): bool
    {
        return $this->getPermission($accountId, $subAccountId, $countryIso) === 'allowed';
    }

    /**
     * Get all country permissions for an account (or sub-account).
     * Returns associative array: ['GB' => 'allowed', 'NG' => 'blocked', ...].
     */
    public function getPermissionsForEntity(string $accountId, ?string $subAccountId): array
    {
        $l1Key = $accountId . ':' . ($subAccountId ?? '_');

        // L1: in-process
        if (isset($this->l1[$l1Key])) {
            return $this->l1[$l1Key];
        }

        // L2: Redis (with fallback to L3 if Redis is unavailable)
        $l2Key = self::L2_PREFIX . $l1Key;
        try {
            $cached = Cache::store('redis')->get($l2Key);
            if ($cached !== null) {
                $this->l1[$l1Key] = $cached;
                return $cached;
            }
        } catch (\Exception $e) {
            Log::warning('[CountryPermissionCache] Redis read failed, falling through to DB: ' . $e->getMessage());
        }

        // L3: PostgreSQL
        $permissions = $this->resolveFromDatabase($accountId, $subAccountId);

        // Populate L2 (best-effort) + L1
        try {
            Cache::store('redis')->put($l2Key, $permissions, self::L2_TTL_SECONDS);
        } catch (\Exception $e) {
            Log::warning('[CountryPermissionCache] Redis write failed: ' . $e->getMessage());
        }
        $this->l1[$l1Key] = $permissions;

        return $permissions;
    }

    /**
     * Invalidate cache for an account (and optionally a specific sub-account).
     * Called when overrides change or global defaults change.
     */
    public function invalidateAccount(string $accountId, ?string $subAccountId = null): void
    {
        try {
            if ($subAccountId) {
                $l1Key = $accountId . ':' . $subAccountId;
                $l2Key = self::L2_PREFIX . $l1Key;
                unset($this->l1[$l1Key]);
                Cache::store('redis')->forget($l2Key);
            } else {
                // Invalidate the account-level cache
                $l1Key = $accountId . ':_';
                $l2Key = self::L2_PREFIX . $l1Key;
                unset($this->l1[$l1Key]);
                Cache::store('redis')->forget($l2Key);

                // Invalidate all sub-account caches for this account
                $this->invalidateAllSubAccounts($accountId);
            }
        } catch (\Exception $e) {
            Log::warning('[CountryPermissionCache] Redis invalidation failed: ' . $e->getMessage());
        }
    }

    /**
     * Invalidate ALL country permission caches (e.g. when global defaults change).
     */
    public function invalidateAll(): void
    {
        $this->l1 = [];

        try {
            $store = Cache::store('redis')->getStore();
            $redis = $store->connection();
            // Use the store's actual prefix to match how Laravel internally keys entries
            $prefix = $store->getPrefix() . self::L2_PREFIX;
            $cursor = null;
            do {
                [$cursor, $keys] = $redis->scan($cursor ?: 0, ['match' => $prefix . '*', 'count' => 200]);
                if (!empty($keys)) {
                    $redis->del(...$keys);
                }
            } while ($cursor);
        } catch (\Exception $e) {
            Log::warning('[CountryPermissionCache] Failed to flush all keys: ' . $e->getMessage());
        }
    }

    /**
     * Warm the cache for a specific account.
     */
    public function warmAccount(string $accountId): void
    {
        // Warm account-level
        $permissions = $this->resolveFromDatabase($accountId, null);
        $l1Key = $accountId . ':_';
        Cache::store('redis')->put(self::L2_PREFIX . $l1Key, $permissions, self::L2_TTL_SECONDS);
        $this->l1[$l1Key] = $permissions;

        // Warm sub-accounts
        $subAccountIds = DB::table('sub_accounts')
            ->where('account_id', $accountId)
            ->where('is_active', true)
            ->pluck('id');

        foreach ($subAccountIds as $subAccountId) {
            $permissions = $this->resolveFromDatabase($accountId, $subAccountId);
            $key = $accountId . ':' . $subAccountId;
            Cache::store('redis')->put(self::L2_PREFIX . $key, $permissions, self::L2_TTL_SECONDS);
            $this->l1[$key] = $permissions;
        }
    }

    /**
     * Resolve permissions from PostgreSQL (L3).
     *
     * Builds a full country→status map by layering:
     *  1. Global defaults from country_controls
     *  2. Account overrides from country_control_overrides
     *  3. Sub-account overrides from sub_account_country_permissions (if sub-account)
     */
    private function resolveFromDatabase(string $accountId, ?string $subAccountId): array
    {
        $permissions = [];

        try {
            // Layer 1: Global defaults
            $globals = DB::table('country_controls')
                ->select('country_iso', 'default_status')
                ->get();

            foreach ($globals as $row) {
                $permissions[$row->country_iso] = $row->default_status;
            }

            // Layer 2: Account overrides
            $overrides = DB::table('country_control_overrides')
                ->join('country_controls', 'country_control_overrides.country_control_id', '=', 'country_controls.id')
                ->where('country_control_overrides.account_id', $accountId)
                ->select('country_controls.country_iso', 'country_control_overrides.override_status')
                ->get();

            foreach ($overrides as $row) {
                $permissions[$row->country_iso] = $row->override_status;
            }

            // Layer 3: Sub-account overrides (most specific)
            if ($subAccountId) {
                $subOverrides = DB::table('sub_account_country_permissions')
                    ->join('country_controls', 'sub_account_country_permissions.country_control_id', '=', 'country_controls.id')
                    ->where('sub_account_country_permissions.sub_account_id', $subAccountId)
                    ->select('country_controls.country_iso', 'sub_account_country_permissions.permission_status')
                    ->get();

                foreach ($subOverrides as $row) {
                    $permissions[$row->country_iso] = $row->permission_status;
                }
            }
        } catch (\Exception $e) {
            Log::error('[CountryPermissionCache] Database resolution failed: ' . $e->getMessage());
        }

        return $permissions;
    }

    /**
     * Invalidate all sub-account caches for a given account.
     */
    private function invalidateAllSubAccounts(string $accountId): void
    {
        try {
            $subAccountIds = DB::table('sub_accounts')
                ->where('account_id', $accountId)
                ->pluck('id');

            foreach ($subAccountIds as $subAccountId) {
                $l1Key = $accountId . ':' . $subAccountId;
                unset($this->l1[$l1Key]);
                Cache::store('redis')->forget(self::L2_PREFIX . $l1Key);
            }
        } catch (\Exception $e) {
            Log::warning('[CountryPermissionCache] Failed to invalidate sub-account caches: ' . $e->getMessage());
        }
    }
}
