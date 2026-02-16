<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class QuarantineMessage extends Model
{
    protected $table = 'quarantine_messages';

    protected $fillable = [
        'account_id',
        'sender_id_value',
        'message_body',
        'primary_engine',
        'matched_rule_id',
        'matched_rule_name',
        'status',
        'reviewer_id',
        'reviewer_notes',
        'reviewed_at',
        'expires_at',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'reviewed_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(QuarantineRecipient::class, 'quarantine_message_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    public function scopeForEngine($query, $engine)
    {
        return $query->where('primary_engine', $engine);
    }

    public function scopeForAccount($query, $accountId)
    {
        return $query->where('account_id', $accountId);
    }

    public function release($reviewerId, $notes = null)
    {
        $this->status = 'released';
        $this->reviewer_id = $reviewerId;
        $this->reviewer_notes = $notes;
        $this->reviewed_at = now();
        $this->save();

        return $this;
    }

    public function block($reviewerId, $notes = null)
    {
        $this->status = 'blocked';
        $this->reviewer_id = $reviewerId;
        $this->reviewer_notes = $notes;
        $this->reviewed_at = now();
        $this->save();

        return $this;
    }

    public function isReviewable(): bool
    {
        return $this->status === 'pending';
    }
}
