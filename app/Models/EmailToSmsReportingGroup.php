<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class EmailToSmsReportingGroup extends Model
{
    use SoftDeletes;

    protected $table = 'email_to_sms_reporting_groups';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'account_id',
        'name',
        'description',
        'status',
    ];

    protected $casts = [
        'id' => 'string',
        'account_id' => 'string',
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

    public function setups(): HasMany
    {
        return $this->hasMany(EmailToSmsSetup::class, 'reporting_group_id');
    }

    public function scopeForAccount($query, string $accountId)
    {
        return $query->where('account_id', $accountId);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
