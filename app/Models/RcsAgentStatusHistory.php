<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RcsAgentStatusHistory extends Model
{
    use HasFactory;

    public $timestamps = false;

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
        'created_at',
    ];

    protected $casts = [
        'payload_snapshot' => 'array',
        'created_at' => 'datetime',
    ];

    public function rcsAgent()
    {
        return $this->belongsTo(RcsAgent::class);
    }

    public function getActionLabelAttribute(): string
    {
        $labels = [
            'created' => 'Created as Draft',
            'submitted' => 'Submitted for Review',
            'review_started' => 'Review Started',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'resubmitted' => 'Resubmitted for Review',
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
            'approved' => 'badge-pastel-success',
            'rejected' => 'badge-pastel-danger',
        ];

        return $classes[$this->to_status] ?? 'badge-pastel-secondary';
    }
}
