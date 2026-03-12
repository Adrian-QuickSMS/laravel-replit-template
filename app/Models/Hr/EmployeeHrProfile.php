<?php

namespace App\Models\Hr;

use App\Models\AdminUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeeHrProfile extends Model
{
    use HasUuids;

    protected $table = 'employee_hr_profiles';

    protected $fillable = [
        'admin_user_id',
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

    public function adminUser(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'admin_user_id');
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
        $user = $this->adminUser;
        return $user ? trim($user->first_name . ' ' . $user->last_name) : 'Unknown';
    }

    public function getEmailAttribute(): ?string
    {
        return $this->adminUser?->email;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
