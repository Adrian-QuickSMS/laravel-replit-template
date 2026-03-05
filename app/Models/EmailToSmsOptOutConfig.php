<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class EmailToSmsOptOutConfig extends Model
{
    protected $table = 'email_to_sms_opt_out_config';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'setup_id',
        'account_id',
        'opt_out_list_id',
        'opt_out_list_name',
    ];

    protected $casts = [
        'id' => 'string',
        'setup_id' => 'string',
        'account_id' => 'string',
        'opt_out_list_id' => 'string',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('tenant', function (Builder $builder) {
            $tenantId = session('customer_tenant_id');
            if ($tenantId) {
                $builder->where('email_to_sms_opt_out_config.account_id', $tenantId);
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
