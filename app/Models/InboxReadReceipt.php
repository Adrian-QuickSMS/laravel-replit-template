<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InboxReadReceipt extends Model
{
    protected $table = 'inbox_read_receipts';

    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'account_id',
        'conversation_id',
        'user_id',
        'last_read_at',
    ];

    protected $casts = [
        'id' => 'string',
        'account_id' => 'string',
        'conversation_id' => 'string',
        'user_id' => 'string',
        'last_read_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('tenant', function (Builder $builder) {
            $tenantId = auth()->check() && auth()->user()->tenant_id
                ? auth()->user()->tenant_id
                : session('customer_tenant_id');

            if ($tenantId) {
                $builder->where('inbox_read_receipts.account_id', $tenantId);
            } else {
                $builder->whereRaw('1 = 0');
            }
        });
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(InboxConversation::class, 'conversation_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
