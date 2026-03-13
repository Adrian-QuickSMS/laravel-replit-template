<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CountryRequest extends Model
{
    use SoftDeletes;

    protected $table = 'country_requests';

    protected $fillable = [
        'request_uuid',
        'account_id',
        'sub_account_id',
        'country_code',
        'country_name',
        'use_case_description',
        'estimated_monthly_volume',
        'supporting_documents',
        'workflow_status',
        'review_notes',
        'rejection_reason',
        'reviewed_by',
        'reviewed_at',
        'version',
        'version_history',
        'submitted_by',
    ];

    protected $casts = [
        'supporting_documents' => 'array',
        'version_history' => 'array',
        'reviewed_at' => 'datetime',
        'estimated_monthly_volume' => 'decimal:0',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }
}
