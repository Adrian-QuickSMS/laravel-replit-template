<?php

namespace App\Models\Billing;

use App\Models\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class CustomerPrice extends Model
{
    use HasUuids;

    protected $table = 'customer_prices';

    protected $fillable = [
        'account_id', 'product_type', 'country_iso', 'unit_price',
        'currency', 'source', 'hubspot_deal_line_item_id',
        'set_by', 'set_at', 'valid_from', 'valid_to', 'active',
        'version', 'previous_version_id', 'change_reason', 'billing_type',
    ];

    protected $casts = [
        'unit_price' => 'decimal:6',
        'active' => 'boolean',
        'valid_from' => 'date',
        'valid_to' => 'date',
        'set_at' => 'datetime',
        'version' => 'integer',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function previousVersion()
    {
        return $this->belongsTo(self::class, 'previous_version_id');
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeValidAt($query, $date = null)
    {
        $date = $date ?? now()->toDateString();
        return $query->where('valid_from', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('valid_to')->orWhere('valid_to', '>=', $date);
            });
    }

    public function scopeForLookup($query, string $accountId, string $productType, ?string $countryIso)
    {
        return $query->where('account_id', $accountId)
            ->where('product_type', $productType)
            ->where('country_iso', $countryIso)
            ->active()
            ->validAt();
    }

    public function scopeAdminOverrides($query)
    {
        return $query->where('source', 'admin_override');
    }

    public function scopeHubspotPrices($query)
    {
        return $query->where('source', 'hubspot');
    }
}
