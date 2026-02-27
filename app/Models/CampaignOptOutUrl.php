<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * CampaignOptOutUrl — unique opt-out URL per MSISDN per campaign.
 *
 * Each recipient in a campaign with URL opt-out enabled gets a unique
 * token that maps to https://qout.uk/{token}. When the subscriber
 * clicks the link, they see a landing page with an unsubscribe button.
 * The first confirmed click creates an OptOutRecord.
 *
 * Token: 8 chars base62 → 218 trillion combinations
 * URL:   https://qout.uk/Ab3Kf9xZ (25 chars fixed)
 * TTL:   30 days from campaign send
 */
class CampaignOptOutUrl extends Model
{
    protected $table = 'campaign_opt_out_urls';

    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    const BASE_URL = 'https://qout.uk/';
    const TOKEN_LENGTH = 8;
    const TOKEN_CHARS = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    const TTL_DAYS = 30;

    protected $fillable = [
        'account_id',
        'campaign_id',
        'mobile_number',
        'token',
        'clicked_at',
        'click_ip',
        'unsubscribed',
        'unsubscribed_at',
        'unsubscribe_ip',
        'expires_at',
    ];

    protected $casts = [
        'unsubscribed' => 'boolean',
        'clicked_at' => 'datetime',
        'unsubscribed_at' => 'datetime',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
            if (empty($model->token)) {
                $model->token = self::generateToken();
            }
            if (empty($model->expires_at)) {
                $model->expires_at = now()->addDays(self::TTL_DAYS);
            }
        });
    }

    // =====================================================
    // RELATIONSHIPS
    // =====================================================

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class, 'campaign_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    // =====================================================
    // TOKEN GENERATION
    // =====================================================

    /**
     * Generate a unique 8-character base62 token.
     */
    public static function generateToken(): string
    {
        do {
            $token = '';
            for ($i = 0; $i < self::TOKEN_LENGTH; $i++) {
                $token .= self::TOKEN_CHARS[random_int(0, strlen(self::TOKEN_CHARS) - 1)];
            }
        } while (static::where('token', $token)->exists());

        return $token;
    }

    /**
     * Get the full opt-out URL for this record.
     */
    public function getUrl(): string
    {
        return self::BASE_URL . $this->token;
    }

    /**
     * Get the fixed-length URL string for segment calculation.
     * Always 25 characters: https://qout.uk/ (18) + 8 char token
     */
    public static function getFixedLengthUrl(): string
    {
        return self::BASE_URL . str_repeat('X', self::TOKEN_LENGTH);
    }

    /**
     * Get the exact character count of the opt-out URL.
     */
    public static function getUrlCharCount(): int
    {
        return strlen(self::BASE_URL) + self::TOKEN_LENGTH;
    }

    // =====================================================
    // STATUS
    // =====================================================

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isAlreadyUnsubscribed(): bool
    {
        return $this->unsubscribed;
    }

    /**
     * Record the first click (landing page visit).
     */
    public function recordClick(string $ip): void
    {
        if ($this->clicked_at) {
            return; // First click only
        }

        $this->update([
            'clicked_at' => now(),
            'click_ip' => $ip,
        ]);
    }

    /**
     * Confirm the unsubscribe (button click on landing page).
     */
    public function confirmUnsubscribe(string $ip): void
    {
        $this->update([
            'unsubscribed' => true,
            'unsubscribed_at' => now(),
            'unsubscribe_ip' => $ip,
        ]);
    }
}
