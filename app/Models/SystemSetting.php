<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * RED SIDE: System-wide configuration key-value store
 *
 * DATA CLASSIFICATION: Internal - System Configuration
 * SIDE: RED (admin-only)
 */
class SystemSetting extends Model
{
    protected $table = 'system_settings';

    protected $fillable = [
        'setting_key',
        'setting_value',
        'setting_group',
        'description',
    ];

    // =====================================================
    // STATIC HELPERS
    // =====================================================

    /**
     * Get a setting value by key, with optional default.
     */
    public static function getValue(string $key, $default = null)
    {
        $setting = Cache::remember(
            "system_setting:{$key}",
            60,
            fn () => static::where('setting_key', $key)->first()
        );

        return $setting ? $setting->setting_value : $default;
    }

    /**
     * Set a setting value by key.
     */
    public static function setValue(string $key, $value)
    {
        $result = static::updateOrCreate(
            ['setting_key' => $key],
            ['setting_value' => $value]
        );

        Cache::forget("system_setting:{$key}");

        return $result;
    }

    /**
     * Get all settings in a group.
     */
    public static function getGroup(string $group)
    {
        return static::where('setting_group', $group)->get();
    }

    // =====================================================
    // SCOPES
    // =====================================================

    public function scopeGroup($query, string $group)
    {
        return $query->where('setting_group', $group);
    }
}
