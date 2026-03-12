<?php

namespace App\Models\Hr;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class HrSettings extends Model
{
    use HasUuids;

    protected $table = 'hr_settings';

    protected $fillable = [
        'default_annual_entitlement_units',
        'holiday_year_start_month',
        'holiday_year_start_day',
        'email_notifications_enabled',
        'birthday_leave_enabled',
        'team_notification_email',
        'show_leave_type_in_notifications',
        'weekend_days',
    ];

    protected $casts = [
        'default_annual_entitlement_units' => 'integer',
        'holiday_year_start_month' => 'integer',
        'holiday_year_start_day' => 'integer',
        'email_notifications_enabled' => 'boolean',
        'birthday_leave_enabled' => 'boolean',
        'show_leave_type_in_notifications' => 'boolean',
        'weekend_days' => 'array',
    ];

    public static function instance(): self
    {
        return self::first() ?? self::create([
            'default_annual_entitlement_units' => 120,
        ]);
    }

    public function getDefaultEntitlementDaysAttribute(): float
    {
        return $this->default_annual_entitlement_units / 4;
    }

    public function getWeekendDayNumbers(): array
    {
        return $this->weekend_days ?? [6, 0];
    }
}
