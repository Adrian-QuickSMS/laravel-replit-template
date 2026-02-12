<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Gateway extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'supplier_id',
        'gateway_code',
        'name',
        'currency',
        'billing_method',
        'fx_source',
        'active',
        'last_rate_update',
        'notes',
    ];

    protected $casts = [
        'active' => 'boolean',
        'last_rate_update' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relationships
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function rateCards()
    {
        return $this->hasMany(RateCard::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(RateCardAuditLog::class);
    }

    // Accessors
    public function getRateCountAttribute()
    {
        return $this->rateCards()->where('active', true)->count();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('active', false);
    }
}
