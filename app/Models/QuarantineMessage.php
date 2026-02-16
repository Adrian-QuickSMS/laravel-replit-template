<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * RED SIDE: Quarantined messages awaiting admin review
 *
 * DATA CLASSIFICATION: Internal - Message Content (sensitive)
 * SIDE: RED (admin-only review)
 * TENANT ISOLATION: account_id scoped via RLS
 */
class QuarantineMessage extends Model
{
    protected $table = 'quarantine_messages';

    protected $fillable = [
        'account_id',
        'sub_account_id',
        'user_id',
        'source',
        'campaign_id',
        'sender_id_value',
        'message_body',
        'message_body_normalised',
        'urls_detected',
        'triggered_rules',
        'primary_engine',
        'recipient_count',
        'scheduled_send_at',
        'status',
        'reviewer_id',
        'reviewed_at',
        'reviewer_notes',
        'expires_at',
    ];

    protected $casts = [
        'urls_detected' => 'array',
        'triggered_rules' => 'array',
        'recipient_count' => 'integer',
        'scheduled_send_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    // =====================================================
    // LIFECYCLE
    // =====================================================

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    // =====================================================
    // RELATIONSHIPS
    // =====================================================

    public function recipients(): HasMany
    {
        return $this->hasMany(QuarantineRecipient::class, 'quarantine_message_id');
    }

    // =====================================================
    // SCOPES
    // =====================================================

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeNotExpired($query)
    {
        return $query->where('expires_at', '>', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    public function scopeForEngine($query, string $engine)
    {
        return $query->where('primary_engine', $engine);
    }

    public function scopeForAccount($query, string $accountId)
    {
        return $query->where('account_id', $accountId);
    }

    // =====================================================
    // ACTIONS
    // =====================================================

    /**
     * Release a quarantined message for sending.
     */
    public function release(string $reviewerId, string $notes): void
    {
        $this->update([
            'status' => 'released',
            'reviewer_id' => $reviewerId,
            'reviewed_at' => now(),
            'reviewer_notes' => $notes,
        ]);
    }

    /**
     * Block a quarantined message permanently.
     */
    public function block(string $reviewerId, string $notes): void
    {
        $this->update([
            'status' => 'blocked',
            'reviewer_id' => $reviewerId,
            'reviewed_at' => now(),
            'reviewer_notes' => $notes,
        ]);
    }

    /**
     * Check if message is still within review window.
     */
    public function isReviewable(): bool
    {
        return $this->status === 'pending' && $this->expires_at > now();
    }
}
