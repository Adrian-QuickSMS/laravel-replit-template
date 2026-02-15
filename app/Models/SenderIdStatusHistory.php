<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * RED SIDE: SenderID Status History - audit trail for workflow transitions
 *
 * Follows the RcsAgentStatusHistory pattern.
 * Records every status change with actor details, IP, and payload snapshot.
 */
class SenderIdStatusHistory extends Model
{
    protected $table = 'sender_id_status_history';

    public $timestamps = false; // Only created_at, no updated_at

    protected $fillable = [
        'sender_id_id',
        'from_status',
        'to_status',
        'action',
        'reason',
        'notes',
        'payload_snapshot',
        'user_id',
        'user_name',
        'user_email',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'sender_id_id' => 'integer',
        'user_id' => 'string',
        'payload_snapshot' => 'array',
        'created_at' => 'datetime',
    ];

    // =====================================================
    // RELATIONSHIPS
    // =====================================================

    public function senderId(): BelongsTo
    {
        return $this->belongsTo(SenderId::class, 'sender_id_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
