<?php

namespace App\Models\Hr;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveEntitlement extends Model
{
    use HasUuids;

    protected $table = 'leave_entitlements';

    protected $fillable = [
        'employee_id',
        'year',
        'total_entitlement_units',
        'carried_over_units',
        'adjustment_units',
        'purchased_units',
        'gifted_units',
        'is_prorated',
        'prorate_note',
    ];

    protected $casts = [
        'year' => 'integer',
        'total_entitlement_units' => 'integer',
        'carried_over_units' => 'integer',
        'adjustment_units' => 'integer',
        'purchased_units' => 'integer',
        'gifted_units' => 'integer',
        'is_prorated' => 'boolean',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(EmployeeHrProfile::class, 'employee_id');
    }

    public function getTotalAvailableUnitsAttribute(): int
    {
        return $this->total_entitlement_units
            + $this->carried_over_units
            + $this->adjustment_units
            + $this->purchased_units
            + $this->gifted_units;
    }

    public function getTotalAvailableDaysAttribute(): float
    {
        return $this->total_available_units / 4;
    }

    public function getEntitlementDaysAttribute(): float
    {
        return $this->total_entitlement_units / 4;
    }

    public function getAdditionalUnitsUsedAttribute(): int
    {
        return $this->carried_over_units + $this->purchased_units + $this->gifted_units;
    }
}
