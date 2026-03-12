<?php

namespace App\Models\Hr;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class CompanyHrSettings extends Model
{
    use HasUuids;

    protected $table = 'company_hr_settings';

    protected $fillable = [
        'tenant_id',
        'default_annual_entitlement_units',
        'holiday_year_start_month',
        'holiday_year_start_day',
        'email_notifications_enabled',
        'ics_generation_enabled',
        'notification_email_from',
        'team_notification_email',
        'show_leave_type_in_notifications',
        'weekend_days',
    ];

    protected $casts = [
        'default_annual_entitlement_units' => 'integer',
        'holiday_year_start_month' => 'integer',
        'holiday_year_start_day' => 'integer',
        'email_notifications_enabled' => 'boolean',
        'ics_generation_enabled' => 'boolean',
        'show_leave_type_in_notifications' => 'boolean',
        'weekend_days' => 'array',
    ];

    /**
     * Get or create settings for a tenant.
     */
    public static function forTenant(string $tenantId): self
    {
        return self::firstOrCreate(
            ['tenant_id' => $tenantId],
            ['default_annual_entitlement_units' => 100] // 25 days default
        );
    }

    public function getDefaultEntitlementDaysAttribute(): float
    {
        return $this->default_annual_entitlement_units / 4;
    }

    public function getWeekendDayNumbers(): array
    {
        return $this->weekend_days ?? [6, 0]; // Saturday, Sunday
    }
}
