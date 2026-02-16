<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * RED SIDE: Enforcement exemptions for all engines
 *
 * Supports three exemption types: rule, engine, value
 * Supports three scope levels: global, account, sub_account
 *
 * DATA CLASSIFICATION: Internal - Enforcement Configuration
 * SIDE: RED (admin-only)
 */
class EnforcementExemption extends Model
{
    use SoftDeletes;

    protected $table = 'enforcement_exemptions';

    // M1 FIX: Allowlist of valid table names for rule_table
    public const VALID_RULE_TABLES = ['senderid_rules', 'content_rules', 'url_rules'];
    public const VALID_ENGINES = ['senderid', 'content', 'url'];
    public const VALID_EXEMPTION_TYPES = ['rule', 'engine', 'value'];
    public const VALID_SCOPE_TYPES = ['global', 'account', 'sub_account'];

    protected $fillable = [
        'engine',
        'exemption_type',
        'rule_id',
        'rule_table',
        'exempted_value',
        'scope_type',
        'scope_id',
        'reason',
        'is_active',
        'created_by',
    ];

    // Hide internal audit fields from default serialization.
    protected $hidden = ['created_by'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // =====================================================
    // LIFECYCLE
    // =====================================================

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }

            // M1 FIX: Validate rule_table against allowlist before saving
            if ($model->rule_table !== null && !in_array($model->rule_table, self::VALID_RULE_TABLES, true)) {
                throw new \InvalidArgumentException(
                    "Invalid rule_table value: '{$model->rule_table}'. Must be one of: " .
                    implode(', ', self::VALID_RULE_TABLES)
                );
            }

            // Validate engine
            if (!in_array($model->engine, self::VALID_ENGINES, true)) {
                throw new \InvalidArgumentException(
                    "Invalid engine value: '{$model->engine}'. Must be one of: " .
                    implode(', ', self::VALID_ENGINES)
                );
            }
        });

        static::updating(function ($model) {
            // M1 FIX: Also validate on update
            if ($model->isDirty('rule_table') && $model->rule_table !== null
                && !in_array($model->rule_table, self::VALID_RULE_TABLES, true)) {
                throw new \InvalidArgumentException(
                    "Invalid rule_table value: '{$model->rule_table}'. Must be one of: " .
                    implode(', ', self::VALID_RULE_TABLES)
                );
            }
        });
    }

    // =====================================================
    // SCOPES
    // =====================================================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForEngine($query, string $engine)
    {
        return $query->where('engine', $engine);
    }

    public function scopeForScope($query, string $scopeType, ?string $scopeId = null)
    {
        $query->where(function ($q) use ($scopeType, $scopeId) {
            // Always include global exemptions
            $q->where('scope_type', 'global');

            if ($scopeType === 'account' && $scopeId) {
                $q->orWhere(function ($q2) use ($scopeId) {
                    $q2->where('scope_type', 'account')
                       ->where('scope_id', $scopeId);
                });
            }

            if ($scopeType === 'sub_account' && $scopeId) {
                $q->orWhere(function ($q2) use ($scopeId) {
                    $q2->where('scope_type', 'sub_account')
                       ->where('scope_id', $scopeId);
                });
            }
        });

        return $query;
    }

    // =====================================================
    // HELPERS
    // =====================================================

    /**
     * Check if this exemption applies to a specific rule.
     */
    public function appliesToRule(int $ruleId, string $ruleTable): bool
    {
        if ($this->exemption_type === 'engine') {
            return true; // Engine-wide exemption applies to all rules
        }

        if ($this->exemption_type === 'rule') {
            return $this->rule_id === $ruleId && $this->rule_table === $ruleTable;
        }

        return false;
    }

    /**
     * Check if this exemption applies to a specific value.
     */
    public function appliesToValue(string $value): bool
    {
        if ($this->exemption_type === 'engine') {
            return true;
        }

        if ($this->exemption_type === 'value') {
            return strtoupper($this->exempted_value) === strtoupper($value);
        }

        return false;
    }
}
