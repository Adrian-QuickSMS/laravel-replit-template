<?php

namespace App\Validation;

use Illuminate\Support\Facades\Log;

/**
 * Validates webhook URLs to prevent SSRF attacks.
 * Blocks private/reserved IP ranges, metadata services, and non-HTTP schemes.
 */
class WebhookUrlValidator
{
    /**
     * Reserved/private CIDR ranges that must not be targeted.
     */
    private const BLOCKED_CIDRS = [
        '0.0.0.0/8',        // "This" network
        '10.0.0.0/8',       // Private
        '100.64.0.0/10',    // Shared address (CGNAT)
        '127.0.0.0/8',      // Loopback
        '169.254.0.0/16',   // Link-local (AWS metadata)
        '172.16.0.0/12',    // Private
        '192.0.0.0/24',     // IETF protocol assignments
        '192.0.2.0/24',     // Documentation
        '192.168.0.0/16',   // Private
        '198.18.0.0/15',    // Benchmarking
        '198.51.100.0/24',  // Documentation
        '203.0.113.0/24',   // Documentation
        '224.0.0.0/4',      // Multicast
        '240.0.0.0/4',      // Reserved
        '255.255.255.255/32', // Broadcast
    ];

    private const BLOCKED_IPV6_CIDRS = [
        '::1/128',          // Loopback
        'fc00::/7',         // Unique local
        'fe80::/10',        // Link-local
        '::ffff:0:0/96',    // IPv4-mapped
    ];

    /**
     * Validate a webhook URL is safe to call.
     *
     * @return array{valid: bool, error: ?string}
     */
    public static function validate(string $url): array
    {
        $parsed = parse_url($url);

        if (!$parsed || empty($parsed['host'])) {
            return ['valid' => false, 'error' => 'Invalid URL format.'];
        }

        // Only allow HTTPS (or HTTP for dev environments)
        $scheme = strtolower($parsed['scheme'] ?? '');
        if (!in_array($scheme, ['https', 'http'])) {
            return ['valid' => false, 'error' => 'Only HTTP/HTTPS schemes are allowed.'];
        }

        // Block common metadata hostnames
        $host = strtolower($parsed['host']);
        $blockedHosts = ['localhost', 'metadata.google.internal', 'instance-data'];
        if (in_array($host, $blockedHosts)) {
            return ['valid' => false, 'error' => 'This hostname is not allowed for webhooks.'];
        }

        // Resolve hostname to IP and check against blocked ranges
        $ips = gethostbynamel($host);
        if ($ips === false) {
            return ['valid' => false, 'error' => 'Could not resolve hostname.'];
        }

        foreach ($ips as $ip) {
            if (self::isPrivateOrReserved($ip)) {
                Log::warning('[WebhookUrlValidator] Blocked SSRF attempt', [
                    'url' => $url,
                    'resolved_ip' => $ip,
                ]);
                return ['valid' => false, 'error' => 'Webhook URL must not resolve to a private or reserved IP address.'];
            }
        }

        return ['valid' => true, 'error' => null];
    }

    /**
     * Check if an IP address falls within blocked CIDR ranges.
     */
    public static function isPrivateOrReserved(string $ip): bool
    {
        // Use PHP's built-in filter for quick check
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            return true;
        }

        // Additional check against our explicit CIDR list for completeness
        $cidrs = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)
            ? self::BLOCKED_IPV6_CIDRS
            : self::BLOCKED_CIDRS;

        foreach ($cidrs as $cidr) {
            if (self::ipInCidr($ip, $cidr)) {
                return true;
            }
        }

        return false;
    }

    private static function ipInCidr(string $ip, string $cidr): bool
    {
        [$subnet, $bits] = explode('/', $cidr);
        $ipLong = ip2long($ip);
        $subnetLong = ip2long($subnet);

        if ($ipLong === false || $subnetLong === false) {
            return false;
        }

        $mask = -1 << (32 - (int) $bits);
        return ($ipLong & $mask) === ($subnetLong & $mask);
    }
}
