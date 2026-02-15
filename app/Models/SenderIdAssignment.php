<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * SenderID Assignment - polymorphic distribution to sub-accounts and users
 *
 * assignable_type: App\Models\SubAccount or App\Models\User
 * assignable_id: UUID of the assigned entity
 */
class SenderIdAssignment extends Model
{
    protected $table = 'sender_id_assignments';

    protected $fillable = [
        'sender_id_id',
        'assignable_type',
        'assignable_id',
        'assigned_by',
    ];

    protected $casts = [
        'sender_id_id' => 'integer',
        'assignable_id' => 'string',
        'assigned_by' => 'string',
    ];

    // =====================================================
    // RELATIONSHIPS
    // =====================================================

    public function senderId(): BelongsTo
    {
        return $this->belongsTo(SenderId::class, 'sender_id_id');
    }

    public function assignable(): MorphTo
    {
        return $this->morphTo();
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
