<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'supplier_code',
        'name',
        'status',
        'default_currency',
        'default_billing_method',
        'notes',
        'contact_name',
        'contact_email',
        'contact_phone',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relationships
    public function gateways()
    {
        return $this->hasMany(Gateway::class);
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
    public function getGatewayCountAttribute()
    {
        return $this->gateways()->count();
    }

    public function getLastRateUpdateAttribute()
    {
        return $this->rateCards()
            ->latest('created_at')
            ->value('created_at');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeSuspended($query)
    {
        return $query->where('status', 'suspended');
    }
}
