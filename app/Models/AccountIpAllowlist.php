<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * GREEN SIDE: Account IP Allowlist entries.
 *
 * DATA CLASSIFICATION: Internal - Account Security
 * TENANT ISOLATION: tenant_id + RLS
 */
class AccountIpAllowlist extends Model
{
    protected $table = 'account_ip_allowlist';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'tenant_id',
        'ip_address',
        'label',
        'created_by',
        'status',
    ];

    protected $casts = [
        'id' => 'string',
        'tenant_id' => 'string',
        'created_by' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        // Tenant scope — fail-closed (zero rows if no tenant context)
        static::addGlobalScope('tenant', function (Builder $builder) {
            $tenantId = session('customer_tenant_id')
                ?? config('app.current_tenant_id');

            if ($tenantId) {
                $builder->where('account_ip_allowlist.tenant_id', $tenantId);
            } else {
                $builder->whereRaw('1 = 0'); // fail-closed
            }
        });
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'tenant_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function toPortalArray(): array
    {
        return [
            'id' => $this->id,
            'ip_address' => $this->ip_address,
            'label' => $this->label,
            'status' => $this->status,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
