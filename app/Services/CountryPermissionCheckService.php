<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Real-time country permission enforcement for message submission.
 *
 * Provides the core check: "Can this account/sub-account send to this country?"
 * Uses CountryPermissionCacheService for fast lookups.
 */
class CountryPermissionCheckService
{
    private CountryPermissionCacheService $cache;

    /** @var array<string, string>|null Prefix→ISO map, loaded once per singleton lifetime */
    private ?array $prefixMap = null;

    /** @var array<string, string>|null ISO→name map, loaded once per singleton lifetime */
    private ?array $countryNameMap = null;

    public function __construct(CountryPermissionCacheService $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Check if sending to a destination number is allowed.
     *
     * @param string $accountId UUID of the account
     * @param string|null $subAccountId UUID of the sub-account (nullable)
     * @param string $destinationNumber E.164 phone number (e.g. +447700900000)
     * @return array{allowed: bool, country_iso: string|null, reason: string|null}
     */
    public function checkDestination(string $accountId, ?string $subAccountId, string $destinationNumber): array
    {
        $countryIso = $this->resolveCountryFromNumber($destinationNumber);

        if ($countryIso === null) {
            return [
                'allowed' => false,
                'country_iso' => null,
                'reason' => 'Unable to determine destination country from phone number.',
            ];
        }

        $permission = $this->cache->getPermission($accountId, $subAccountId, $countryIso);

        if ($permission === 'allowed') {
            return [
                'allowed' => true,
                'country_iso' => $countryIso,
                'reason' => null,
            ];
        }

        $countryName = $this->getCountryName($countryIso);

        if ($permission === 'restricted') {
            return [
                'allowed' => false,
                'country_iso' => $countryIso,
                'reason' => "Sending to {$countryName} ({$countryIso}) requires approval. Submit a country access request.",
            ];
        }

        return [
            'allowed' => false,
            'country_iso' => $countryIso,
            'reason' => "Sending to {$countryName} ({$countryIso}) is not permitted for your account.",
        ];
    }

    /**
     * Bulk-check multiple destination numbers. Returns per-number results.
     *
     * @param string $accountId
     * @param string|null $subAccountId
     * @param string[] $destinationNumbers
     * @return array<string, array{allowed: bool, country_iso: string|null, reason: string|null}>
     */
    public function checkDestinations(string $accountId, ?string $subAccountId, array $destinationNumbers): array
    {
        $results = [];
        $permissions = $this->cache->getPermissionsForEntity($accountId, $subAccountId);

        foreach ($destinationNumbers as $number) {
            $countryIso = $this->resolveCountryFromNumber($number);

            if ($countryIso === null) {
                $results[$number] = [
                    'allowed' => false,
                    'country_iso' => null,
                    'reason' => 'Unable to determine destination country.',
                ];
                continue;
            }

            $permission = $permissions[$countryIso] ?? 'blocked';

            if ($permission === 'allowed') {
                $results[$number] = [
                    'allowed' => true,
                    'country_iso' => $countryIso,
                    'reason' => null,
                ];
            } elseif ($permission === 'restricted') {
                $results[$number] = [
                    'allowed' => false,
                    'country_iso' => $countryIso,
                    'reason' => "Sending to {$countryIso} requires approval.",
                ];
            } else {
                $results[$number] = [
                    'allowed' => false,
                    'country_iso' => $countryIso,
                    'reason' => "Sending to {$countryIso} is not permitted.",
                ];
            }
        }

        return $results;
    }

    /**
     * Get a summary of allowed/blocked/restricted countries for display.
     */
    public function getCountrySummary(string $accountId, ?string $subAccountId): array
    {
        $permissions = $this->cache->getPermissionsForEntity($accountId, $subAccountId);

        $allowed = [];
        $blocked = [];
        $restricted = [];

        foreach ($permissions as $iso => $status) {
            match ($status) {
                'allowed' => $allowed[] = $iso,
                'blocked' => $blocked[] = $iso,
                'restricted' => $restricted[] = $iso,
                default => $blocked[] = $iso,
            };
        }

        return [
            'allowed_count' => count($allowed),
            'blocked_count' => count($blocked),
            'restricted_count' => count($restricted),
            'allowed' => $allowed,
            'blocked' => $blocked,
            'restricted' => $restricted,
        ];
    }

    /**
     * Resolve country ISO from E.164 phone number using in-memory prefix map.
     * Loads the prefix→ISO map from DB once, then all lookups are in-memory.
     */
    private function resolveCountryFromNumber(string $number): ?string
    {
        $digits = ltrim($number, '+');

        if (empty($digits)) {
            return null;
        }

        $map = $this->getPrefixMap();

        // Try progressively shorter prefixes (longest match first)
        for ($len = min(4, strlen($digits)); $len >= 1; $len--) {
            $prefix = substr($digits, 0, $len);
            if (isset($map[$prefix])) {
                return $map[$prefix];
            }
        }

        return null;
    }

    private function getCountryName(string $countryIso): string
    {
        $names = $this->getCountryNameMap();
        return $names[$countryIso] ?? $countryIso;
    }

    /**
     * Load prefix→ISO map from DB (once per singleton lifetime).
     */
    private function getPrefixMap(): array
    {
        if ($this->prefixMap === null) {
            $this->prefixMap = DB::table('country_controls')
                ->whereNotNull('country_prefix')
                ->pluck('country_iso', 'country_prefix')
                ->toArray();
        }
        return $this->prefixMap;
    }

    /**
     * Load ISO→name map from DB (once per singleton lifetime).
     */
    private function getCountryNameMap(): array
    {
        if ($this->countryNameMap === null) {
            $this->countryNameMap = DB::table('country_controls')
                ->pluck('country_name', 'country_iso')
                ->toArray();
        }
        return $this->countryNameMap;
    }
}
