<?php

namespace App\Models\Hr;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequest extends Model
{
    use HasUuids;

    protected $table = 'leave_requests';

    protected $fillable = [
        'employee_id',
        'leave_type',
        'status',
        'start_date',
        'end_date',
        'duration_units',
        'duration_days_display',
        'day_portion',
        'employee_note',
        'approver_id',
        'approval_comment',
        'submitted_at',
        'approved_at',
        'rejected_at',
        'cancelled_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'duration_units' => 'integer',
        'duration_days_display' => 'decimal:2',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    const TYPE_ANNUAL = 'annual_leave';
    const TYPE_SICKNESS = 'sickness';
    const TYPE_MEDICAL = 'medical';
    const TYPE_BIRTHDAY = 'birthday';

    const LEAVE_TYPES = [
        self::TYPE_ANNUAL => 'Annual Leave',
        self::TYPE_SICKNESS => 'Sickness',
        self::TYPE_MEDICAL => 'Medical',
        self::TYPE_BIRTHDAY => 'Birthday',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_CANCELLED = 'cancelled';

    const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_APPROVED,
        self::STATUS_REJECTED,
        self::STATUS_CANCELLED,
    ];

    const PORTION_FULL = 'full';
    const PORTION_HALF_AM = 'half_am';
    const PORTION_HALF_PM = 'half_pm';
    const PORTION_QUARTER = 'quarter';

    public function employee(): BelongsTo
    {
        return $this->belongsTo(EmployeeHrProfile::class, 'employee_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(EmployeeHrProfile::class, 'approver_id');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isAnnualLeave(): bool
    {
        return $this->leave_type === self::TYPE_ANNUAL;
    }

    public function getLeaveTypeLabelAttribute(): string
    {
        return self::LEAVE_TYPES[$this->leave_type] ?? ucfirst(str_replace('_', ' ', $this->leave_type));
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'badge-warning',
            self::STATUS_APPROVED => 'badge-success',
            self::STATUS_REJECTED => 'badge-danger',
            self::STATUS_CANCELLED => 'badge-secondary',
            default => 'badge-light',
        };
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeForYear(Builder $query, int $year): Builder
    {
        return $query->whereYear('start_date', $year);
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('leave_type', $type);
    }
}
