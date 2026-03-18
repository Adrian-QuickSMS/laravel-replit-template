<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * GREEN SIDE: Held messages — messages blocked by out-of-hours restrictions.
 *
 * DATA CLASSIFICATION: Confidential - Message Content
 * TENANT ISOLATION: tenant_id + RLS
 */
class HeldMessage extends Model
{
    protected $table = 'held_messages';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'tenant_id',
        'recipient_number',
        'message_content',
        'sender_id',
        'message_type',
        'origin',
        'campaign_id',
        'campaign_recipient_id',
        'sub_account_id',
        'user_id',
        'held_reason',
        'release_after',
        'status',
        'released_at',
        'metadata',
    ];

    protected $casts = [
        'id' => 'string',
        'tenant_id' => 'string',
        'campaign_id' => 'string',
        'campaign_recipient_id' => 'string',
        'sub_account_id' => 'string',
        'user_id' => 'string',
        'release_after' => 'datetime',
        'released_at' => 'datetime',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('tenant', function (Builder $builder) {
            $tenantId = session('customer_tenant_id')
                ?? config('app.current_tenant_id');

            if ($tenantId) {
                $builder->where('held_messages.tenant_id', $tenantId);
            } else {
                $builder->whereRaw('1 = 0');
            }
        });
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'tenant_id');
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class, 'campaign_id');
    }

    public function scopeHeld(Builder $query): Builder
    {
        return $query->where('status', 'held');
    }

    public function scopeReleasable(Builder $query): Builder
    {
        return $query->where('status', 'held')
            ->where('release_after', '<=', now());
    }
}
