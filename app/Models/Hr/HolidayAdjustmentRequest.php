<?php

namespace App\Models\Hr;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HolidayAdjustmentRequest extends Model
{
    use HasUuids;

    protected $table = 'holiday_adjustment_requests';

    protected $fillable = [
        'employee_id',
        'type',
        'status',
        'units',
        'year',
        'requested_by',
        'approved_by',
        'reason',
        'admin_note',
        'approved_at',
        'rejected_at',
    ];

    protected $casts = [
        'units' => 'integer',
        'year' => 'integer',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    const TYPE_PURCHASE = 'purchase';
    const TYPE_TOIL = 'toil';
    const TYPE_GIFTED = 'gifted';
    const TYPE_CARRY_OVER = 'carry_over';

    const TYPES = [
        self::TYPE_PURCHASE => 'Purchased Holiday',
        self::TYPE_TOIL => 'TOIL',
        self::TYPE_GIFTED => 'Gifted',
        self::TYPE_CARRY_OVER => 'Carry Over',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    public function employee(): BelongsTo
    {
        return $this->belongsTo(EmployeeHrProfile::class, 'employee_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(EmployeeHrProfile::class, 'requested_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(EmployeeHrProfile::class, 'approved_by');
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? ucfirst(str_replace('_', ' ', $this->type));
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
        return $query->where('year', $year);
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }
}
