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

    // H3 FIX: status, reviewer_id, reviewed_at, reviewer_notes removed from $fillable.
    // These fields are only set through the release() and block() workflow methods.
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
        'expires_at',
    ];

    // H4 FIX: Prevent accidental serialization of sensitive message content.
    // Admin controllers should explicitly select fields they need.
    protected $hidden = [
        'message_body',
        'message_body_normalised',
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
            // Ensure new messages start as pending
            if (empty($model->status)) {
                $model->status = 'pending';
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
        return $query->where(function ($q) {
            $q->where('expires_at', '>', now())
              ->orWhereNull('expires_at');
        });
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now())
                     ->whereNotNull('expires_at');
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
     * M3 FIX: Validates reviewability before allowing state change.
     *
     * @throws \LogicException if message is not reviewable
     */
    public function release(string $reviewerId, string $notes): void
    {
        if (!$this->isReviewable()) {
            throw new \LogicException(
                "Quarantine message {$this->uuid} is not reviewable (status: {$this->status}, expired: " .
                ($this->isExpired() ? 'yes' : 'no') . ')'
            );
        }

        $this->forceFill([
            'status' => 'released',
            'reviewer_id' => $reviewerId,
            'reviewed_at' => now(),
            'reviewer_notes' => $notes,
        ])->save();
    }

    /**
     * Block a quarantined message permanently.
     * M3 FIX: Validates reviewability before allowing state change.
     *
     * @throws \LogicException if message is not reviewable
     */
    public function block(string $reviewerId, string $notes): void
    {
        if (!$this->isReviewable()) {
            throw new \LogicException(
                "Quarantine message {$this->uuid} is not reviewable (status: {$this->status}, expired: " .
                ($this->isExpired() ? 'yes' : 'no') . ')'
            );
        }

        $this->forceFill([
            'status' => 'blocked',
            'reviewer_id' => $reviewerId,
            'reviewed_at' => now(),
            'reviewer_notes' => $notes,
        ])->save();
    }

    /**
     * Check if message is still within review window.
     * L6 FIX: Handles null expires_at gracefully (no expiry = always reviewable if pending).
     */
    public function isReviewable(): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }

        if ($this->expires_at === null) {
            return true;
        }

        return $this->expires_at > now();
    }

    /**
     * Check if the message has expired.
     */
    public function isExpired(): bool
    {
        if ($this->expires_at === null) {
            return false;
        }

        return $this->expires_at <= now();
    }
}
