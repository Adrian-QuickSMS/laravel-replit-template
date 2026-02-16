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
        'key',
        'value',
        'group',
        'description',
        'updated_by',
    ];

    protected $casts = [
        'value' => 'json',
    ];

    // =====================================================
    // STATIC HELPERS
    // =====================================================

    /**
     * Get a setting value by key, with optional default.
     */
    public static function getValue(string $key, mixed $default = null): mixed
    {
        $setting = Cache::remember(
            "system_setting:{$key}",
            60,
            fn () => static::where('key', $key)->first()
        );

        return $setting ? $setting->value : $default;
    }

    /**
     * Set a setting value by key.
     */
    public static function setValue(string $key, mixed $value, ?string $updatedBy = null): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'updated_by' => $updatedBy]
        );

        Cache::forget("system_setting:{$key}");
    }

    /**
     * Get all settings in a group as key => value array.
     */
    public static function getGroup(string $group): array
    {
        return static::where('group', $group)
            ->pluck('value', 'key')
            ->toArray();
    }

    // =====================================================
    // SCOPES
    // =====================================================

    public function scopeGroup($query, string $group)
    {
        return $query->where('group', $group);
    }
}
