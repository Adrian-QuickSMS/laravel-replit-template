<?php

namespace App\Services;

use App\Models\AccountSettings;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

/**
 * AntiFloodService — prevents duplicate messages to the same recipient within a time window.
 *
 * Architecture: 3-tier lookup for sub-1ms hot-path performance.
 *   L1: Redis SET with TTL (primary lookup — sub-ms)
 *   L2: PostgreSQL message_dedup_log (persistence + audit trail)
 *
 * The check path (isDuplicate) hits Redis ONLY.
 * The record path (recordSend) writes to both Redis and PostgreSQL.
 *
 * Modes:
 *   enforce  — block duplicate sends, return error
 *   monitor  — log duplicates but allow the send
 *   off      — no checking at all
 *
 * Independent of the admin-side spam_filter_mode on accounts table.
 */
class AntiFloodService
{
    private const REDIS_PREFIX = 'antiflood:';

    /**
     * Check if a message is a duplicate (same content + same recipient within window).
     *
     * @return AntiFloodResult
     */
    public function check(string $accountId, string $recipientNumber, string $messageContent, string $source = 'CAMPAIGNS'): AntiFloodResult
    {
        try {
            $settings = $this->getSettings($accountId);

            if (!$settings || $settings->anti_flood_mode === 'off' || !$settings->anti_flood_enabled) {
                return AntiFloodResult::allowed();
            }

            $contentHash = hash('sha256', $messageContent);
            $recipientHash = hash('sha256', $recipientNumber);
            $redisKey = self::REDIS_PREFIX . $accountId . ':' . $contentHash . ':' . $recipientHash;

            // L1: Redis lookup — sub-millisecond
            $exists = false;
            try {
                $exists = (bool) Redis::exists($redisKey);
            } catch (\Throwable $e) {
                // Redis down — fall through to DB check
                Log::warning('[AntiFloodService] Redis unavailable, falling back to DB', [
                    'error' => $e->getMessage(),
                ]);
                $exists = $this->checkDatabase($accountId, $contentHash, $recipientHash);
            }

            if (!$exists) {
                return AntiFloodResult::allowed();
            }

            // Duplicate detected
            $isEnforced = $settings->anti_flood_mode === 'enforce';

            Log::info('[AntiFloodService] Duplicate detected', [
                'account_id' => $accountId,
                'mode' => $settings->anti_flood_mode,
                'source' => $source,
                'enforced' => $isEnforced,
            ]);

            if ($isEnforced) {
                return AntiFloodResult::blocked(
                    "Duplicate message to this recipient blocked. Same message was sent within the last {$settings->anti_flood_window_hours} hour(s)."
                );
            }

            // Monitor mode — log but allow
            return AntiFloodResult::monitored();

        } catch (\Throwable $e) {
            Log::error('[AntiFloodService] Check failed, allowing message', [
                'error' => $e->getMessage(),
                'account_id' => $accountId,
            ]);
            // Fail-open: never block sends due to service errors
            return AntiFloodResult::allowed();
        }
    }

    /**
     * Record a sent message for future duplicate detection.
     * Called AFTER a message is successfully dispatched.
     */
    public function recordSend(
        string $accountId,
        string $recipientNumber,
        string $messageContent,
        string $source = 'CAMPAIGNS',
        ?string $senderId = null
    ): void {
        try {
            $settings = $this->getSettings($accountId);

            if (!$settings || $settings->anti_flood_mode === 'off' || !$settings->anti_flood_enabled) {
                return;
            }

            $contentHash = hash('sha256', $messageContent);
            $recipientHash = hash('sha256', $recipientNumber);
            $windowSeconds = $settings->anti_flood_window_hours * 3600;
            $redisKey = self::REDIS_PREFIX . $accountId . ':' . $contentHash . ':' . $recipientHash;
            $expiresAt = now()->addSeconds($windowSeconds);

            // L1: Redis — SET with TTL
            try {
                Redis::setex($redisKey, $windowSeconds, '1');
            } catch (\Throwable $e) {
                Log::warning('[AntiFloodService] Redis write failed', ['error' => $e->getMessage()]);
            }

            // L2: PostgreSQL — persistent audit trail
            try {
                DB::table('message_dedup_log')->insert([
                    'content_hash' => $contentHash,
                    'recipient_hash' => $recipientHash,
                    'sender_id_value' => $senderId ? substr($senderId, 0, 15) : null,
                    'account_id' => $accountId,
                    'message_source' => $source,
                    'normalisation_applied' => false,
                    'created_at' => now(),
                    'expires_at' => $expiresAt,
                ]);
            } catch (\Throwable $e) {
                Log::warning('[AntiFloodService] DB write failed', ['error' => $e->getMessage()]);
            }

        } catch (\Throwable $e) {
            Log::error('[AntiFloodService] recordSend failed', [
                'error' => $e->getMessage(),
                'account_id' => $accountId,
            ]);
            // Never block business logic
        }
    }

    /**
     * Fallback DB check when Redis is unavailable.
     */
    private function checkDatabase(string $accountId, string $contentHash, string $recipientHash): bool
    {
        return DB::table('message_dedup_log')
            ->where('account_id', $accountId)
            ->where('content_hash', $contentHash)
            ->where('recipient_hash', $recipientHash)
            ->where('expires_at', '>', now())
            ->exists();
    }

    /**
     * Get account settings with in-process caching.
     */
    private function getSettings(string $accountId): ?AccountSettings
    {
        static $cache = [];

        if (!isset($cache[$accountId])) {
            $cache[$accountId] = AccountSettings::withoutGlobalScopes()->find($accountId);
        }

        return $cache[$accountId];
    }

    /**
     * Clean up expired dedup log entries.
     * Called by scheduled command.
     */
    public function cleanupExpired(): int
    {
        return DB::table('message_dedup_log')
            ->where('expires_at', '<', now())
            ->delete();
    }
}

/**
 * Value object for anti-flood check results.
 */
class AntiFloodResult
{
    public function __construct(
        public readonly bool $allowed,
        public readonly bool $isDuplicate,
        public readonly bool $monitored,
        public readonly ?string $reason = null,
    ) {}

    public static function allowed(): self
    {
        return new self(allowed: true, isDuplicate: false, monitored: false);
    }

    public static function blocked(string $reason): self
    {
        return new self(allowed: false, isDuplicate: true, monitored: false, reason: $reason);
    }

    public static function monitored(): self
    {
        return new self(allowed: true, isDuplicate: true, monitored: true);
    }
}
