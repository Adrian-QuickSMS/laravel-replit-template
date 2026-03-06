<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class EmailToSmsAuditLog extends Model
{
    protected $table = 'email_to_sms_audit_log';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'account_id',
        'setup_id',
        'reporting_group_id',
        'action',
        'user_id',
        'user_name',
        'details',
        'metadata',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'id' => 'string',
        'account_id' => 'string',
        'setup_id' => 'string',
        'reporting_group_id' => 'string',
        'user_id' => 'string',
        'metadata' => 'array',
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
                $builder->where('email_to_sms_audit_log.account_id', $tenantId);
            } else {
                $builder->whereRaw('1 = 0');
            }
        });
    }

    public function setup(): BelongsTo
    {
        return $this->belongsTo(EmailToSmsSetup::class, 'setup_id');
    }

    public static function logAction(
        string $accountId,
        string $action,
        ?string $setupId = null,
        ?string $reportingGroupId = null,
        ?string $details = null,
        ?array $metadata = null
    ): self {
        return static::withoutGlobalScopes()->create([
            'account_id' => $accountId,
            'setup_id' => $setupId,
            'reporting_group_id' => $reportingGroupId,
            'action' => $action,
            'user_id' => session('customer_user_id'),
            'user_name' => session('customer_user_name'),
            'details' => $details,
            'metadata' => $metadata,
            'ip_address' => request()->ip(),
            'user_agent' => substr((string) request()->userAgent(), 0, 500),
        ]);
    }
}
