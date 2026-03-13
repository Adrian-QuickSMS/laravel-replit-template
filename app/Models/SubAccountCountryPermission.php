<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Sub-account level country permission override.
 *
 * Hierarchy: country_controls (global) → country_control_overrides (account) → sub_account_country_permissions (sub-account).
 * Most specific wins.
 */
class SubAccountCountryPermission extends Model
{
    protected $table = 'sub_account_country_permissions';

    protected $fillable = [
        'sub_account_id',
        'country_control_id',
        'permission_status',
        'reason',
        'created_by',
    ];

    protected $casts = [
        'sub_account_id' => 'string',
        'created_by' => 'string',
    ];

    public function subAccount(): BelongsTo
    {
        return $this->belongsTo(SubAccount::class, 'sub_account_id');
    }

    public function countryControl(): BelongsTo
    {
        return $this->belongsTo(CountryControl::class, 'country_control_id');
    }
}
