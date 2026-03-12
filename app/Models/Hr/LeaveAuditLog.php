<?php

namespace App\Models\Hr;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class LeaveAuditLog extends Model
{
    use HasUuids;

    protected $table = 'leave_audit_log';

    public $timestamps = false;

    protected $fillable = [
        'actor_id',
        'action',
        'target_employee_id',
        'leave_request_id',
        'old_value',
        'new_value',
        'note',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    const ACTION_REQUEST_SUBMITTED = 'request_submitted';
    const ACTION_REQUEST_APPROVED = 'request_approved';
    const ACTION_REQUEST_REJECTED = 'request_rejected';
    const ACTION_REQUEST_CANCELLED = 'request_cancelled';
    const ACTION_ENTITLEMENT_CHANGED = 'entitlement_changed';
    const ACTION_PROFILE_CREATED = 'profile_created';
    const ACTION_PROFILE_UPDATED = 'profile_updated';

    public static function record(
        string $actorId,
        string $action,
        string $targetEmployeeId,
        ?string $leaveRequestId = null,
        ?string $oldValue = null,
        ?string $newValue = null,
        ?string $note = null
    ): self {
        return self::create([
            'actor_id' => $actorId,
            'action' => $action,
            'target_employee_id' => $targetEmployeeId,
            'leave_request_id' => $leaveRequestId,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'note' => $note,
            'created_at' => now(),
        ]);
    }
}
