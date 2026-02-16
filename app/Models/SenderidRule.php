<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * RED SIDE: SenderID enforcement rules
 *
 * DATA CLASSIFICATION: Internal - Enforcement Configuration
 * SIDE: RED (admin-only)
 */
class SenderidRule extends Model
{
    use SoftDeletes;

    protected $table = 'senderid_rules';

    protected $fillable = [
        'name',
        'pattern',
        'match_type',
        'action',
        'category',
        'use_normalisation',
        'is_active',
        'priority',
        'description',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'use_normalisation' => 'boolean',
        'is_active' => 'boolean',
        'priority' => 'integer',
    ];

    protected $hidden = [];

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

    public function scopeByPriority($query)
    {
        return $query->orderBy('priority', 'asc');
    }

    public function scopeCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    // =====================================================
    // HELPERS
    // =====================================================

    /**
     * Convert to the array format used by MessageEnforcementService.
     */
    public function toEnforcementArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'pattern' => $this->pattern,
            'matchType' => $this->match_type,
            'action' => $this->action,
            'useNormalisation' => $this->use_normalisation,
        ];
    }
}
