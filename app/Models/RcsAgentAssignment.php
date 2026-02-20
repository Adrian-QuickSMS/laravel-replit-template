<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * RCS Agent Assignment - polymorphic distribution to sub-accounts and users
 *
 * assignable_type: App\Models\SubAccount or App\Models\User
 * assignable_id: UUID of the assigned entity
 *
 * Follows the SenderIdAssignment pattern exactly.
 */
class RcsAgentAssignment extends Model
{
    protected $table = 'rcs_agent_assignments';

    protected $fillable = [
        'rcs_agent_id',
        'assignable_type',
        'assignable_id',
        'assigned_by',
    ];

    protected $casts = [
        'rcs_agent_id' => 'integer',
        'assignable_id' => 'string',
        'assigned_by' => 'string',
    ];

    // =====================================================
    // RELATIONSHIPS
    // =====================================================

    public function rcsAgent(): BelongsTo
    {
        return $this->belongsTo(RcsAgent::class, 'rcs_agent_id');
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
