<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class EmailToSmsSetup extends Model
{
    protected $table = 'email_to_sms_setups';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'account_id',
        'sub_account_id',
        'type',
        'name',
        'description',
        'status',
        'reporting_group_id',
        'sender_id_template_id',
        'sender_id_label',
        'multiple_sms_enabled',
        'delivery_reports_enabled',
        'delivery_report_email',
        'daily_limit',
    ];

    protected $casts = [
        'id' => 'string',
        'account_id' => 'string',
        'sub_account_id' => 'string',
        'sender_id_template_id' => 'string',
        'reporting_group_id' => 'string',
        'multiple_sms_enabled' => 'boolean',
        'delivery_reports_enabled' => 'boolean',
        'daily_limit' => 'integer',
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
                $builder->where('email_to_sms_setups.account_id', $tenantId);
            } else {
                $builder->whereRaw('1 = 0');
            }
        });
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function subAccount(): BelongsTo
    {
        return $this->belongsTo(SubAccount::class, 'sub_account_id');
    }

    public function reportingGroup(): BelongsTo
    {
        return $this->belongsTo(EmailToSmsReportingGroup::class, 'reporting_group_id');
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(EmailToSmsAddress::class, 'setup_id');
    }

    public function allowedSenders(): HasMany
    {
        return $this->hasMany(EmailToSmsAllowedSender::class, 'setup_id');
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(EmailToSmsRecipient::class, 'setup_id');
    }

    public function optOutConfig(): HasMany
    {
        return $this->hasMany(EmailToSmsOptOutConfig::class, 'setup_id');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(EmailToSmsAuditLog::class, 'setup_id');
    }

    public function scopeForAccount($query, string $accountId)
    {
        return $query->withoutGlobalScope('tenant')->where('account_id', $accountId);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeStandard($query)
    {
        return $query->where('type', 'standard');
    }

    public function scopeContactList($query)
    {
        return $query->where('type', 'contact_list');
    }

    public function toPortalArray(): array
    {
        $this->loadMissing(['addresses', 'allowedSenders', 'recipients', 'optOutConfig', 'subAccount', 'reportingGroup']);

        $originatingEmails = $this->addresses->pluck('email_address')->values()->toArray();
        $allowedSenderEmails = $this->allowedSenders->pluck('email_pattern')->values()->toArray();

        $base = [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'type' => $this->type,
            'subaccountId' => $this->sub_account_id,
            'subaccountName' => $this->subAccount?->name ?? 'Main Account',
            'originatingEmails' => $originatingEmails,
            'senderIdTemplateId' => $this->sender_id_template_id,
            'senderId' => $this->sender_id_label,
            'multipleSmsEnabled' => $this->multiple_sms_enabled,
            'deliveryReportsEnabled' => $this->delivery_reports_enabled,
            'deliveryReportsEmail' => $this->delivery_report_email,
            'dailyLimit' => $this->daily_limit,
            'reportingGroupId' => $this->reporting_group_id,
            'reportingGroupName' => $this->reportingGroup?->name,
            'status' => ucfirst($this->status),
            'createdAt' => $this->created_at?->toIso8601String(),
            'updatedAt' => $this->updated_at?->toIso8601String(),
            'created' => $this->created_at?->format('Y-m-d'),
            'lastUpdated' => $this->updated_at?->format('Y-m-d'),
        ];

        if ($this->type === 'standard') {
            $base['allowedEmails'] = $allowedSenderEmails;
        } else {
            $base['allowedSenderEmails'] = $allowedSenderEmails;
            $base['contactBookListIds'] = $this->recipients->pluck('recipient_id')->values()->toArray();
            $base['contactBookListNames'] = $this->recipients->pluck('recipient_name')->values()->toArray();
            $optOutConfigs = $this->optOutConfig;
            $base['optOutMode'] = $optOutConfigs->isNotEmpty() ? 'SELECTED' : 'NONE';
            $base['optOutListIds'] = $optOutConfigs->pluck('opt_out_list_id')->values()->toArray();
            $base['optOutListNames'] = $optOutConfigs->pluck('opt_out_list_name')->values()->toArray();
        }

        return $base;
    }
}
