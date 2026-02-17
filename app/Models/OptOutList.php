<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * GREEN SIDE: Opt-Out List (suppression lists)
 *
 * DATA CLASSIFICATION: Confidential - Compliance/Regulatory
 * SIDE: GREEN (customer portal accessible)
 * TENANT ISOLATION: MANDATORY account_id on every row + RLS
 */
class OptOutList extends Model
{
    protected $table = 'opt_out_lists';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'account_id',
        'name',
        'description',
        'count',
        // is_master is NOT fillable — only system can set this (partial unique index enforces one per account)
    ];

    protected $casts = [
        'id' => 'string',
        'account_id' => 'string',
        'is_master' => 'boolean',
        'count' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('tenant', function (Builder $builder) {
            $tenantId = auth()->check() && auth()->user()->tenant_id
                ? auth()->user()->tenant_id
                : session('customer_tenant_id');
            if ($tenantId) {
                $builder->where('opt_out_lists.account_id', $tenantId);
            }
        });
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function records(): HasMany
    {
        return $this->hasMany(OptOutRecord::class, 'opt_out_list_id');
    }

    public function refreshCount(): void
    {
        $this->update(['count' => $this->records()->count()]);
    }

    public function toPortalArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'is_master' => $this->is_master,
            'count' => $this->count,
            'created_at' => $this->created_at?->format('Y-m-d'),
            'updated_at' => $this->updated_at?->format('Y-m-d'),
        ];
    }
}
