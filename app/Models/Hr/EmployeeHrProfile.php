<?php

namespace App\Models\Hr;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class EmployeeHrProfile extends Model
{
    use HasUuids;

    protected $table = 'employee_hr_profiles';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'employee_number',
        'start_date',
        'end_date',
        'department',
        'job_title',
        'hr_role',
        'manager_id',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    const ROLE_EMPLOYEE = 'employee';
    const ROLE_MANAGER = 'manager';
    const ROLE_HR_ADMIN = 'hr_admin';

    const HR_ROLES = [
        self::ROLE_EMPLOYEE,
        self::ROLE_MANAGER,
        self::ROLE_HR_ADMIN,
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('tenant', function (Builder $builder) {
            if (auth()->check() && auth()->user()->tenant_id) {
                $builder->where('employee_hr_profiles.tenant_id', auth()->user()->tenant_id);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(self::class, 'manager_id');
    }

    public function directReports(): HasMany
    {
        return $this->hasMany(self::class, 'manager_id');
    }

    public function leaveEntitlements(): HasMany
    {
        return $this->hasMany(LeaveEntitlement::class, 'employee_id');
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class, 'employee_id');
    }

    public function entitlementForYear(int $year): ?LeaveEntitlement
    {
        return $this->leaveEntitlements()->where('year', $year)->first();
    }

    public function isManager(): bool
    {
        return in_array($this->hr_role, [self::ROLE_MANAGER, self::ROLE_HR_ADMIN]);
    }

    public function isHrAdmin(): bool
    {
        return $this->hr_role === self::ROLE_HR_ADMIN;
    }

    public function getFullNameAttribute(): string
    {
        $user = $this->user;
        return $user ? trim($user->first_name . ' ' . $user->last_name) : 'Unknown';
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForTenant(Builder $query, string $tenantId): Builder
    {
        return $query->withoutGlobalScope('tenant')->where('tenant_id', $tenantId);
    }
}
