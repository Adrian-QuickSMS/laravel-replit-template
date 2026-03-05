<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class EmailToSmsSetup extends Model
{
    use SoftDeletes;

    protected $table = 'email_to_sms_setups';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'account_id',
        'sub_account_id',
        'name',
        'description',
        'type',
        'originating_emails',
        'allowed_sender_emails',
        'sender_id_template_id',
        'sender_id',
        'subject_overrides_sender_id',
        'multiple_sms_enabled',
        'delivery_reports_enabled',
        'delivery_reports_email',
        'status',
        'reporting_group_id',
        'contact_book_list_ids',
        'opt_out_mode',
        'opt_out_list_ids',
    ];

    protected $casts = [
        'id' => 'string',
        'account_id' => 'string',
        'sub_account_id' => 'string',
        'sender_id_template_id' => 'string',
        'reporting_group_id' => 'string',
        'originating_emails' => 'array',
        'allowed_sender_emails' => 'array',
        'contact_book_list_ids' => 'array',
        'opt_out_list_ids' => 'array',
        'subject_overrides_sender_id' => 'boolean',
        'multiple_sms_enabled' => 'boolean',
        'delivery_reports_enabled' => 'boolean',
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

    public function subAccount(): BelongsTo
    {
        return $this->belongsTo(SubAccount::class, 'sub_account_id');
    }

    public function reportingGroup(): BelongsTo
    {
        return $this->belongsTo(EmailToSmsReportingGroup::class, 'reporting_group_id');
    }

    public function scopeForAccount($query, string $accountId)
    {
        return $query->where('account_id', $accountId);
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
}
