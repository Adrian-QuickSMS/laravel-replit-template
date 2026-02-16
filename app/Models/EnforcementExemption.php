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

    protected $fillable = [
        'engine',
        'exemption_type',
        'rule_id',
        'scope',
        'value',
        'account_id',
        'reason',
        'is_active',
        'created_by',
    ];

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
            $q->where('scope', 'global');

            if ($scopeType === 'account' && $scopeId) {
                $q->orWhere(function ($q2) use ($scopeId) {
                    $q2->where('scope', 'account')
                       ->where('account_id', $scopeId);
                });
            }

            if ($scopeType === 'sub_account' && $scopeId) {
                $q->orWhere(function ($q2) use ($scopeId) {
                    $q2->where('scope', 'sub_account')
                       ->where('account_id', $scopeId);
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
            return strtoupper($this->value) === strtoupper($value);
        }

        return false;
    }
}
