<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * GREEN SIDE: Opt-Out Record (individual mobile number suppressions)
 *
 * DATA CLASSIFICATION: Confidential - Compliance/Regulatory
 * SIDE: GREEN (customer portal accessible)
 * TENANT ISOLATION: MANDATORY account_id on every row + RLS
 *
 * COMPLIANCE NOTE: Records keyed by mobile_number, not contact_id.
 * Persists even after contact deletion (regulatory requirement).
 */
class OptOutRecord extends Model
{
    protected $table = 'opt_out_records';

    protected $keyType = 'string';
    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'account_id',
        'opt_out_list_id',
        'mobile_number',
        'campaign_ref',
    ];

    protected $casts = [
        'id' => 'string',
        'account_id' => 'string',
        'opt_out_list_id' => 'string',
        'created_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('tenant', function (Builder $builder) {
            if (auth()->check() && auth()->user()->tenant_id) {
                $builder->where('opt_out_records.account_id', auth()->user()->tenant_id);
            }
        });
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function optOutList(): BelongsTo
    {
        return $this->belongsTo(OptOutList::class, 'opt_out_list_id');
    }

    public function toPortalArray(): array
    {
        return [
            'id' => $this->id,
            'mobile' => $this->mobile_number,
            'source' => $this->getRawOriginal('source') ?? 'manual',
            'timestamp' => $this->created_at?->format('Y-m-d H:i:s'),
            'campaign_ref' => $this->campaign_ref,
            'list_id' => $this->opt_out_list_id,
            'list_name' => $this->optOutList?->name,
        ];
    }
}
