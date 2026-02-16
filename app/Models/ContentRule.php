<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ContentRule extends Model
{
    use SoftDeletes;

    protected $table = 'content_rules';

    protected $fillable = [
        'pattern',
        'match_type',
        'action',
        'category',
        'name',
        'description',
        'priority',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'priority' => 'integer',
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

    public function scopeByPriority($query)
    {
        return $query->orderBy('priority', 'asc');
    }

    public function toEnforcementArray(): array
    {
        return [
            'id' => $this->uuid,
            'name' => $this->name,
            'pattern' => $this->pattern,
            'matchType' => $this->match_type,
            'action' => $this->action,
        ];
    }
}
