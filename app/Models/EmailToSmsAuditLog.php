<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class EmailToSmsAuditLog extends Model
{
    protected $table = 'email_to_sms_audit_log';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'account_id',
        'user_id',
        'setup_id',
        'reporting_group_id',
        'action',
        'entity_type',
        'changes',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'id' => 'string',
        'account_id' => 'string',
        'user_id' => 'string',
        'setup_id' => 'string',
        'reporting_group_id' => 'string',
        'changes' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public static function logAction(
        string $accountId,
        string $action,
        string $entityType,
        ?string $setupId = null,
        ?string $reportingGroupId = null,
        ?array $changes = null
    ): self {
        return static::create([
            'account_id' => $accountId,
            'user_id' => session('customer_user_id'),
            'setup_id' => $setupId,
            'reporting_group_id' => $reportingGroupId,
            'action' => $action,
            'entity_type' => $entityType,
            'changes' => $changes,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
