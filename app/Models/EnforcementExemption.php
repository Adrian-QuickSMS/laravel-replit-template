<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class EnforcementExemption extends Model
{
    use SoftDeletes;

    protected $table = 'enforcement_exemptions';

    protected $fillable = [
        'engine',
        'exemption_type',
        'scope',
        'value',
        'rule_id',
        'account_id',
        'reason',
        'is_active',
        'created_by',
        'expires_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForEngine($query, $engine)
    {
        return $query->where('engine', $engine);
    }

    public function scopeForScope($query, $scope)
    {
        return $query->where('scope', $scope);
    }

    public function appliesToRule($ruleId): bool
    {
        return $this->rule_id == $ruleId;
    }

    public function appliesToValue($val): bool
    {
        return $this->value == $val;
    }
}
