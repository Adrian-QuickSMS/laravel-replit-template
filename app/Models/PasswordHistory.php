<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * RED SIDE: Password History
 *
 * DATA CLASSIFICATION: Restricted - Security Control
 * SIDE: RED (password hashes are security-sensitive)
 * TENANT ISOLATION: Scoped via user_id → users.tenant_id
 *
 * SECURITY NOTES:
 * - Prevents password reuse (last 12 passwords)
 * - Password hashes never exposed anywhere
 * - Portal roles: NO ACCESS
 * - Only accessed by authentication service
 * - Retention: 2 years or last 12 passwords (whichever is longer)
 */
class PasswordHistory extends Model
{
    protected $table = 'password_history';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'password_hash',
        'set_at',
    ];

    protected $hidden = [
        'password_hash',
    ];

    protected $casts = [
        'user_id' => 'string',
        'set_at' => 'datetime',
    ];

    /**
     * Convert user_id binary to string
     */
    public function getUserIdAttribute($value)
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value) && strlen($value) === 36) {
            return $value;
        }

        $hex = bin2hex($value);
        return sprintf(
            '%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20)
        );
    }

    /**
     * Convert user_id string to binary
     */
    public function setUserIdAttribute($value)
    {
        if ($value === null) {
            return;
        }

        if (is_string($value) && strlen($value) === 16) {
            $this->attributes['user_id'] = $value;
            return;
        }

        $hex = str_replace('-', '', $value);
        $this->attributes['user_id'] = hex2bin($hex);
    }

    // =====================================================
    // RELATIONSHIPS
    // =====================================================

    /**
     * The user this password history belongs to
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // =====================================================
    // HELPER METHODS
    // =====================================================

    /**
     * Get last N password hashes for user
     */
    public static function getLastPasswordsForUser($userId, int $count = 12)
    {
        return static::where('user_id', $userId)
            ->orderBy('set_at', 'desc')
            ->limit($count)
            ->get();
    }

    /**
     * Clean up old password history (keep last 12 or 2 years, whichever is longer)
     */
    public static function cleanupForUser($userId): int
    {
        // Get the 12th most recent password
        $twelfthPassword = static::where('user_id', $userId)
            ->orderBy('set_at', 'desc')
            ->skip(11)
            ->first();

        if (!$twelfthPassword) {
            return 0; // Less than 12 passwords, don't delete anything
        }

        // Delete passwords older than 2 years AND older than the 12th password
        $twoYearsAgo = now()->subYears(2);
        $cutoffDate = $twelfthPassword->set_at->lt($twoYearsAgo)
            ? $twelfthPassword->set_at
            : $twoYearsAgo;

        return static::where('user_id', $userId)
            ->where('set_at', '<', $cutoffDate)
            ->delete();
    }
}
