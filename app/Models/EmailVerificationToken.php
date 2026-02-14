<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * GREEN SIDE: Email Verification Tokens
 *
 * DATA CLASSIFICATION: Internal - Email Verification
 * SIDE: GREEN (used by customer email verification flow)
 * TENANT ISOLATION: Scoped via user_id → users.tenant_id
 *
 * SECURITY NOTES:
 * - Tokens expire after 24 hours
 * - One-time use only (deleted after verification)
 * - Can be regenerated (invalidates previous)
 * - Token stored as SHA-256 hash
 */
class EmailVerificationToken extends Model
{
    protected $table = 'email_verification_tokens';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'token',
        'email',
        'expires_at',
        'created_at',
    ];

    protected $casts = [
        'user_id' => 'string',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    // =====================================================
    // RELATIONSHIPS
    // =====================================================

    /**
     * The user this token belongs to
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // =====================================================
    // SCOPES
    // =====================================================

    /**
     * Scope: Only active (not expired) tokens
     */
    public function scopeActive($query)
    {
        return $query->where('expires_at', '>', now());
    }

    /**
     * Scope: Expired tokens
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    // =====================================================
    // HELPER METHODS
    // =====================================================

    /**
     * Check if token is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if token is active
     */
    public function isActive(): bool
    {
        return !$this->isExpired();
    }

    /**
     * Create new verification token for user
     */
    public static function createForUser(User $user): array
    {
        // Generate random token
        $plainToken = bin2hex(random_bytes(32)); // 64 char hex string
        $hashedToken = hash('sha256', $plainToken);

        // Delete any existing tokens for this user
        static::where('user_id', $user->id)->delete();

        // Create new token
        $token = static::create([
            'user_id' => $user->id,
            'token' => $hashedToken,
            'email' => $user->email,
            'expires_at' => now()->addHours(24),
            'created_at' => now(),
        ]);

        return [
            'model' => $token,
            'plain_token' => $plainToken, // Return plain token to send in email
        ];
    }

    /**
     * Find token by plain token value
     */
    public static function findByPlainToken(string $plainToken): ?self
    {
        $hashedToken = hash('sha256', $plainToken);

        return static::where('token', $hashedToken)
            ->where('expires_at', '>', now())
            ->first();
    }

    /**
     * Verify token without consuming it (for email verification step)
     */
    public static function verifyWithoutConsuming(string $plainToken): ?User
    {
        $token = static::findByPlainToken($plainToken);

        if (!$token) {
            return null;
        }

        $user = $token->user;

        if ($user && !$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        return $user;
    }

    /**
     * Verify and consume token (for security setup step - final use)
     */
    public static function verifyAndConsume(string $plainToken): ?User
    {
        $token = static::findByPlainToken($plainToken);

        if (!$token) {
            return null;
        }

        $user = $token->user;

        if ($user && !$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        $token->delete();

        return $user;
    }
}
