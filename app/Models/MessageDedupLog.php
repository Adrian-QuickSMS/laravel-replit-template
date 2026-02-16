<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * RED SIDE: Anti-spam duplicate message detection log
 *
 * Records hash of (message content + recipient) for each sent message.
 * Used to detect and block duplicate messages within a configurable time window.
 *
 * DATA CLASSIFICATION: Internal - Message Metadata
 * SIDE: RED (system-level)
 * TENANT ISOLATION: account_id scoped via RLS
 */
class MessageDedupLog extends Model
{
    protected $table = 'message_dedup_log';

    public $timestamps = false;

    protected $fillable = [
        'content_hash',
        'recipient_hash',
        'sender_id_value',
        'account_id',
        'message_source',
        'normalisation_applied',
        'created_at',
        'expires_at',
    ];

    protected $casts = [
        'normalisation_applied' => 'boolean',
        'created_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    // =====================================================
    // SCOPES
    // =====================================================

    public function scopeNotExpired($query)
    {
        return $query->where('expires_at', '>', now());
    }

    public function scopeForAccount($query, string $accountId)
    {
        return $query->where('account_id', $accountId);
    }

    // =====================================================
    // STATIC HELPERS
    // =====================================================

    /**
     * Check if a duplicate message exists within the time window.
     */
    public static function isDuplicate(string $contentHash, string $recipientHash): bool
    {
        return static::where('content_hash', $contentHash)
            ->where('recipient_hash', $recipientHash)
            ->where('expires_at', '>', now())
            ->exists();
    }

    /**
     * Record a message for future duplicate detection.
     */
    public static function record(
        string $contentHash,
        string $recipientHash,
        string $accountId,
        string $messageSource,
        int $windowMinutes = 60,
        bool $normalisationApplied = false,
        ?string $senderIdValue = null
    ): self {
        return static::create([
            'content_hash' => $contentHash,
            'recipient_hash' => $recipientHash,
            'sender_id_value' => $senderIdValue,
            'account_id' => $accountId,
            'message_source' => $messageSource,
            'normalisation_applied' => $normalisationApplied,
            'created_at' => now(),
            'expires_at' => now()->addMinutes($windowMinutes),
        ]);
    }

    /**
     * Clean up expired entries.
     */
    public static function purgeExpired(): int
    {
        return static::where('expires_at', '<=', now())->delete();
    }
}
