<?php

namespace App\Services;

use App\Models\AccountIpAllowlist;
use App\Models\AccountSettings;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * IpAllowlistService — manages account-level IP allowlist for portal login.
 *
 * Uses Redis cache (60s TTL) to avoid DB hits on every authenticated request.
 * Supports both exact IPs and CIDR notation.
 *
 * Limit: 50 entries per account.
 */
class IpAllowlistService
{
    private const MAX_ENTRIES = 50;
    private const CACHE_TTL = 60; // seconds
    private const CACHE_PREFIX = 'ip_allowlist:';

    /** @var array<string, bool> Request-scoped enabled/disabled cache */
    private array $enabledCache = [];

    /**
     * Check if an IP is allowed for a given account.
     * Returns true if: allowlist is disabled, OR IP matches an active entry.
     */
    public function isIpAllowed(string $accountId, string $clientIp): bool
    {
        try {
            if (!$this->isEnabled($accountId)) {
                return true;
            }

            $allowedEntries = $this->getActiveEntries($accountId);

            if (empty($allowedEntries)) {
                // Fail-closed: enabled but no IPs = block all
                return false;
            }

            foreach ($allowedEntries as $entry) {
                if ($this->ipMatchesEntry($clientIp, $entry)) {
                    return true;
                }
            }

            return false;

        } catch (\Throwable $e) {
            Log::error('[IpAllowlistService] Check failed, allowing access', [
                'error' => $e->getMessage(),
                'account_id' => $accountId,
            ]);
            // Fail-open on errors to avoid locking out all users
            return true;
        }
    }

    /**
     * Check if IP allowlist is enabled for an account.
     * Cached per-request (service is scoped) to avoid repeated DB queries
     * since this runs on every authenticated request via middleware.
     */
    public function isEnabled(string $accountId): bool
    {
        if (!isset($this->enabledCache[$accountId])) {
            $settings = AccountSettings::withoutGlobalScopes()->find($accountId);
            $this->enabledCache[$accountId] = $settings && $settings->ip_allowlist_enabled;
        }

        return $this->enabledCache[$accountId];
    }

    /**
     * Get all active allowlist entries for an account (cached).
     */
    public function getActiveEntries(string $accountId): array
    {
        $cacheKey = self::CACHE_PREFIX . $accountId;

        try {
            return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($accountId) {
                return AccountIpAllowlist::withoutGlobalScopes()
                    ->where('tenant_id', $accountId)
                    ->where('status', 'active')
                    ->pluck('ip_address')
                    ->toArray();
            });
        } catch (\Throwable $e) {
            // Cache miss — direct DB query
            return AccountIpAllowlist::withoutGlobalScopes()
                ->where('tenant_id', $accountId)
                ->where('status', 'active')
                ->pluck('ip_address')
                ->toArray();
        }
    }

    /**
     * Add an IP/CIDR to the allowlist.
     */
    public function addIp(string $accountId, string $ipAddress, ?string $label, string $createdBy): AccountIpAllowlist
    {
        // Check limit
        $currentCount = AccountIpAllowlist::withoutGlobalScopes()
            ->where('tenant_id', $accountId)
            ->count();

        if ($currentCount >= self::MAX_ENTRIES) {
            throw new \RuntimeException("IP allowlist limit reached ({$currentCount}/" . self::MAX_ENTRIES . ")");
        }

        // Validate IP/CIDR
        if (!$this->isValidIpOrCidr($ipAddress)) {
            throw new \InvalidArgumentException("Invalid IP address or CIDR range: {$ipAddress}");
        }

        // Check for duplicate
        $existing = AccountIpAllowlist::withoutGlobalScopes()
            ->where('tenant_id', $accountId)
            ->where('ip_address', $ipAddress)
            ->first();

        if ($existing) {
            if ($existing->status === 'disabled') {
                $existing->update(['status' => 'active']);
                $this->invalidateCache($accountId);
                return $existing->fresh();
            }
            throw new \RuntimeException("IP address already in allowlist: {$ipAddress}");
        }

        $entry = AccountIpAllowlist::withoutGlobalScopes()->create([
            'tenant_id' => $accountId,
            'ip_address' => $ipAddress,
            'label' => $label,
            'created_by' => $createdBy,
            'status' => 'active',
        ]);

        $this->invalidateCache($accountId);
        return $entry;
    }

    /**
     * Remove (disable) an IP from the allowlist.
     */
    public function removeIp(string $accountId, string $entryId): bool
    {
        $entry = AccountIpAllowlist::withoutGlobalScopes()
            ->where('tenant_id', $accountId)
            ->where('id', $entryId)
            ->first();

        if (!$entry) {
            return false;
        }

        $entry->update(['status' => 'disabled']);
        $this->invalidateCache($accountId);
        return true;
    }

    /**
     * Toggle the IP allowlist feature on/off.
     * When enabling, the caller's IP MUST be in the allowlist to prevent self-lockout.
     */
    public function toggleEnabled(string $accountId, bool $enabled, ?string $callerIp = null, ?string $userId = null): void
    {
        if ($enabled && $callerIp && $callerIp !== '') {
            // Safety: ensure caller's IP is in the allowlist before enabling
            $activeEntries = AccountIpAllowlist::withoutGlobalScopes()
                ->where('tenant_id', $accountId)
                ->where('status', 'active')
                ->pluck('ip_address')
                ->toArray();

            $hasCallerIp = false;
            foreach ($activeEntries as $entry) {
                if ($this->ipMatchesEntry($callerIp, $entry)) {
                    $hasCallerIp = true;
                    break;
                }
            }

            if (!$hasCallerIp) {
                // Check if IP exists as disabled — re-enable it
                $existing = AccountIpAllowlist::withoutGlobalScopes()
                    ->where('tenant_id', $accountId)
                    ->where('ip_address', $callerIp)
                    ->first();

                if ($existing && $existing->status === 'disabled') {
                    $existing->update(['status' => 'active']);
                    $this->invalidateCache($accountId);
                } elseif (!$existing) {
                    $this->addIp($accountId, $callerIp, 'Auto-added (enabling allowlist)', $userId ?? 'system');
                }
                // If it already exists as active, no action needed (already covered)
            }
        }

        AccountSettings::withoutGlobalScopes()
            ->where('account_id', $accountId)
            ->update(['ip_allowlist_enabled' => $enabled]);

        $this->invalidateCache($accountId);
    }

    /**
     * Validate an IP address or CIDR range.
     */
    public function isValidIpOrCidr(string $input): bool
    {
        // Exact IP
        if (filter_var($input, FILTER_VALIDATE_IP)) {
            return true;
        }

        // CIDR notation
        if (strpos($input, '/') !== false) {
            [$subnet, $mask] = explode('/', $input, 2);

            if (!filter_var($subnet, FILTER_VALIDATE_IP)) {
                return false;
            }

            $maskInt = (int) $mask;

            if (filter_var($subnet, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                return $maskInt >= 0 && $maskInt <= 32;
            }

            if (filter_var($subnet, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                return $maskInt >= 0 && $maskInt <= 128;
            }
        }

        return false;
    }

    /**
     * Check if a client IP matches an allowlist entry (exact or CIDR).
     */
    private function ipMatchesEntry(string $clientIp, string $entry): bool
    {
        if (strpos($entry, '/') === false) {
            return $clientIp === $entry;
        }

        return $this->ipInCidr($clientIp, $entry);
    }

    /**
     * Check if an IP falls within a CIDR range.
     */
    private function ipInCidr(string $ip, string $cidr): bool
    {
        [$subnet, $mask] = explode('/', $cidr, 2);

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $ipLong = ip2long($ip);
            $subnetLong = ip2long($subnet);

            if ($ipLong === false || $subnetLong === false) {
                return false;
            }

            $maskLong = -1 << (32 - (int)$mask);

            return ($ipLong & $maskLong) === ($subnetLong & $maskLong);
        }

        // IPv6 CIDR support
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $ipBin = inet_pton($ip);
            $subnetBin = inet_pton($subnet);
            $maskInt = (int) $mask;

            if ($ipBin === false || $subnetBin === false) {
                return false;
            }

            $fullBytes = intdiv($maskInt, 8);
            $remainBits = $maskInt % 8;

            for ($i = 0; $i < $fullBytes; $i++) {
                if ($ipBin[$i] !== $subnetBin[$i]) {
                    return false;
                }
            }

            if ($remainBits > 0 && $fullBytes < strlen($ipBin)) {
                $maskByte = 0xFF << (8 - $remainBits);
                if ((ord($ipBin[$fullBytes]) & $maskByte) !== (ord($subnetBin[$fullBytes]) & $maskByte)) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }

    /**
     * Invalidate the cached allowlist for an account.
     */
    public function invalidateCache(string $accountId): void
    {
        Cache::forget(self::CACHE_PREFIX . $accountId);
        unset($this->enabledCache[$accountId]);
    }
}
