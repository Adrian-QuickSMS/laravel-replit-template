<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessageDedupLog extends Model
{
    public $timestamps = false;

    protected $table = 'message_dedup_log';

    protected $fillable = [
        'account_id',
        'message_hash',
        'normalised_hash',
        'recipient_hash',
        'first_seen_at',
        'expires_at',
    ];

    protected $casts = [
        'first_seen_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function scopeNotExpired($query)
    {
        return $query->where('expires_at', '>', now());
    }

    public function scopeForAccount($query, $accountId)
    {
        return $query->where('account_id', $accountId);
    }

    public static function isDuplicate($accountId, $messageHash): bool
    {
        return static::where('account_id', $accountId)
            ->where('message_hash', $messageHash)
            ->where('expires_at', '>', now())
            ->exists();
    }

    public static function record($accountId, $messageHash, $normalisedHash, $recipientHash, $windowMinutes)
    {
        return static::create([
            'account_id' => $accountId,
            'message_hash' => $messageHash,
            'normalised_hash' => $normalisedHash,
            'recipient_hash' => $recipientHash,
            'first_seen_at' => now(),
            'expires_at' => now()->addMinutes($windowMinutes),
        ]);
    }

    public static function purgeExpired()
    {
        return static::where('expires_at', '<', now())->delete();
    }
}
