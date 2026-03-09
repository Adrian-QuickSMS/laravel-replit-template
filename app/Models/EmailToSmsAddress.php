<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class EmailToSmsAddress extends Model
{
    protected $table = 'email_to_sms_addresses';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'setup_id',
        'account_id',
        'email_address',
        'is_primary',
        'status',
    ];

    protected $casts = [
        'id' => 'string',
        'setup_id' => 'string',
        'account_id' => 'string',
        'is_primary' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });

        static::addGlobalScope('tenant', function (Builder $builder) {
            $tenantId = session('customer_tenant_id');
            if ($tenantId) {
                $builder->where('email_to_sms_addresses.account_id', $tenantId);
            } else {
                $builder->whereRaw('1 = 0');
            }
        });
    }

    public function setup(): BelongsTo
    {
        return $this->belongsTo(EmailToSmsSetup::class, 'setup_id');
    }
}
