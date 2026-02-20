<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * RED SIDE: RCS Agent Status History - audit trail for workflow transitions
 *
 * Follows the SenderIdStatusHistory pattern.
 * Records every status change with actor details, IP, and payload snapshot.
 */
class RcsAgentStatusHistory extends Model
{
    protected $table = 'rcs_agent_status_histories';

    public $timestamps = false; // Only created_at, no updated_at

    protected $fillable = [
        'rcs_agent_id',
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
        'rcs_agent_id' => 'integer',
        'user_id' => 'string',
        'payload_snapshot' => 'array',
        'created_at' => 'datetime',
    ];

    // =====================================================
    // RELATIONSHIPS
    // =====================================================

    public function rcsAgent(): BelongsTo
    {
        return $this->belongsTo(RcsAgent::class, 'rcs_agent_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // =====================================================
    // ACCESSORS
    // =====================================================

    public function getActionLabelAttribute(): string
    {
        $labels = [
            'created' => 'Created as Draft',
            'submitted' => 'Submitted for Review',
            'review_started' => 'Review Started',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'info_requested' => 'Additional Info Requested',
            'info_provided' => 'Info Provided by Customer',
            'review_resumed' => 'Review Resumed',
            'resubmission_started' => 'Resubmission Started',
            'suspended' => 'Suspended',
            'reactivated' => 'Reactivated',
            'revoked' => 'Permanently Revoked',
            'edited' => 'Edited',
            'status_changed' => 'Status Changed',
        ];

        return $labels[$this->action] ?? ucfirst(str_replace('_', ' ', $this->action));
    }

    public function getStatusBadgeClassAttribute(): string
    {
        $classes = [
            'draft' => 'badge-pastel-secondary',
            'submitted' => 'badge-pastel-info',
            'in_review' => 'badge-pastel-warning',
            'pending_info' => 'badge-pastel-warning',
            'info_provided' => 'badge-pastel-info',
            'approved' => 'badge-pastel-success',
            'rejected' => 'badge-pastel-danger',
            'suspended' => 'badge-pastel-danger',
            'revoked' => 'badge-pastel-dark',
        ];

        return $classes[$this->to_status] ?? 'badge-pastel-secondary';
    }
}
