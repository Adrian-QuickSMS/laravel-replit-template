<?php

namespace App\Http\Middleware;

use App\Models\ApiConnection;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

/**
 * Authenticates inbound API requests using API Key or Basic Auth credentials.
 *
 * Flow:
 * 1. Extract credentials from Authorization header (Bearer or Basic)
 * 2. Look up the connection by hashed key or username
 * 3. Verify connection is active (not draft/suspended/archived)
 * 4. Enforce IP allowlist (if enabled)
 * 5. Enforce per-connection rate limit
 * 6. Set tenant context (PostgreSQL session variable + request attributes)
 * 7. Update last_used tracking
 */
class ApiKeyAuthenticate
{
    public function handle(Request $request, Closure $next): Response
    {
        $connection = $this->resolveConnection($request);

        if (!$connection) {
            return response()->json([
                'error' => 'unauthorized',
                'message' => 'Invalid API credentials.',
            ], 401);
        }

        // Verify connection is active
        if (!$connection->isActive()) {
            $status = $connection->getRawOriginal('status');
            return response()->json([
                'error' => 'forbidden',
                'message' => "API connection is {$status}.",
            ], 403);
        }

        // Enforce IP allowlist
        if ($connection->ip_allowlist_enabled) {
            $clientIp = $request->ip();
            if (!$this->isIpAllowed($clientIp, $connection->ip_allowlist ?? [])) {
                return response()->json([
                    'error' => 'forbidden',
                    'message' => 'Request IP not in allowlist.',
                ], 403);
            }
        }

        // Enforce per-connection rate limit
        $rateLimitKey = 'api_connection:' . $connection->id;
        if (RateLimiter::tooManyAttempts($rateLimitKey, $connection->rate_limit_per_minute)) {
            $retryAfter = RateLimiter::availableIn($rateLimitKey);
            return response()->json([
                'error' => 'rate_limited',
                'message' => 'Rate limit exceeded.',
                'retry_after' => $retryAfter,
            ], 429)->withHeaders([
                'Retry-After' => $retryAfter,
                'X-RateLimit-Limit' => $connection->rate_limit_per_minute,
                'X-RateLimit-Remaining' => 0,
            ]);
        }
        RateLimiter::hit($rateLimitKey, 60);

        // Set tenant context for PostgreSQL RLS
        DB::select("SELECT set_config('app.current_tenant_id', ?, false)", [$connection->account_id]);

        // Attach connection to request for downstream controllers
        $request->attributes->set('api_connection', $connection);
        $request->attributes->set('api_account_id', $connection->account_id);
        $request->attributes->set('api_sub_account_id', $connection->sub_account_id);

        // Update last_used tracking (non-blocking — won't fail the request)
        try {
            $connection->touchLastUsed($request->ip());
        } catch (\Throwable $e) {
            // Don't fail the request if usage tracking fails
        }

        $response = $next($request);

        // Add rate limit headers to response
        $remaining = RateLimiter::remaining($rateLimitKey, $connection->rate_limit_per_minute);
        $response->headers->set('X-RateLimit-Limit', $connection->rate_limit_per_minute);
        $response->headers->set('X-RateLimit-Remaining', max(0, $remaining));

        return $response;
    }

    /**
     * Resolve the ApiConnection from the request credentials.
     * Supports: Bearer token (API Key) and Basic Auth.
     */
    private function resolveConnection(Request $request): ?ApiConnection
    {
        $authHeader = $request->header('Authorization', '');

        // Try Bearer token (API Key)
        if (str_starts_with($authHeader, 'Bearer ')) {
            $apiKey = substr($authHeader, 7);
            return $this->resolveByApiKey($apiKey);
        }

        // Try X-API-Key header
        $xApiKey = $request->header('X-API-Key');
        if ($xApiKey) {
            return $this->resolveByApiKey($xApiKey);
        }

        // Try Basic Auth
        if (str_starts_with($authHeader, 'Basic ')) {
            $decoded = base64_decode(substr($authHeader, 6), true);
            if ($decoded && str_contains($decoded, ':')) {
                [$username, $password] = explode(':', $decoded, 2);
                return $this->resolveByBasicAuth($username, $password);
            }
        }

        return null;
    }

    /**
     * Look up connection by SHA-256 hash of the API key.
     * Uses withoutGlobalScopes because tenant context isn't set yet.
     */
    private function resolveByApiKey(string $apiKey): ?ApiConnection
    {
        $hash = hash('sha256', $apiKey);

        return ApiConnection::withoutGlobalScopes()
            ->where('api_key_hash', $hash)
            ->first();
    }

    /**
     * Look up connection by username, then verify password hash.
     * Uses withoutGlobalScopes because tenant context isn't set yet.
     */
    private function resolveByBasicAuth(string $username, string $password): ?ApiConnection
    {
        $connection = ApiConnection::withoutGlobalScopes()
            ->where('basic_auth_username', $username)
            ->first();

        if (!$connection) {
            return null;
        }

        if (!$connection->verifyPassword($password)) {
            return null;
        }

        return $connection;
    }

    /**
     * Check if the client IP is in the allowlist.
     * Supports exact match and CIDR notation.
     */
    private function isIpAllowed(string $clientIp, array $allowlist): bool
    {
        if (empty($allowlist)) {
            return true; // Empty list = allow all (shouldn't happen if enabled, but fail-open for usability)
        }

        foreach ($allowlist as $entry) {
            $entry = trim($entry);
            if (empty($entry)) {
                continue;
            }

            // Exact match
            if ($clientIp === $entry) {
                return true;
            }

            // CIDR match
            if (str_contains($entry, '/') && $this->ipInCidr($clientIp, $entry)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if an IP address falls within a CIDR range.
     */
    private function ipInCidr(string $ip, string $cidr): bool
    {
        [$subnet, $bits] = explode('/', $cidr, 2);
        $bits = (int) $bits;

        // IPv4
        $ipLong = ip2long($ip);
        $subnetLong = ip2long($subnet);

        if ($ipLong === false || $subnetLong === false) {
            return false; // Not valid IPv4 — could be IPv6
        }

        if ($bits < 0 || $bits > 32) {
            return false;
        }

        $mask = -1 << (32 - $bits);
        return ($ipLong & $mask) === ($subnetLong & $mask);
    }
}
