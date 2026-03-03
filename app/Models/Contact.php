<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * GREEN SIDE: Contact (Contact Book core entity)
 *
 * DATA CLASSIFICATION: Confidential - Customer PII
 * SIDE: GREEN (customer portal accessible)
 * TENANT ISOLATION: MANDATORY account_id on every row + RLS
 */
class Contact extends Model
{
    use SoftDeletes;

    protected $table = 'contacts';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'account_id',
        'sub_account_id',
        'mobile_number',
        'first_name',
        'last_name',
        'email',
        'date_of_birth',
        'postcode',
        'city',
        'country',
        'custom_data',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'id' => 'string',
        'account_id' => 'string',
        'sub_account_id' => 'string',
        'custom_data' => 'array',
        'date_of_birth' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $attributes = [
        'custom_data' => '{}',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('tenant', function (Builder $builder) {
            $tenantId = auth()->check() && auth()->user()->tenant_id
                ? auth()->user()->tenant_id
                : session('customer_tenant_id');
            if ($tenantId) {
                $builder->where('contacts.account_id', $tenantId);
            } else {
                // Fail-closed: return zero rows when no tenant context
                $builder->whereRaw('1 = 0');
            }
        });
    }

    // =====================================================
    // RELATIONSHIPS
    // =====================================================

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function subAccount(): BelongsTo
    {
        return $this->belongsTo(SubAccount::class, 'sub_account_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'contact_tag')
            ->withPivot('created_at');
    }

    public function lists(): BelongsToMany
    {
        return $this->belongsToMany(ContactList::class, 'contact_list_member', 'contact_id', 'list_id')
            ->withPivot('created_at');
    }

    public function campaignRecipients(): HasMany
    {
        return $this->hasMany(CampaignRecipient::class, 'contact_id');
    }

    // =====================================================
    // SCOPES
    // =====================================================

    public function scopeActive($query)
    {
        return $query->whereRaw("status = 'active'");
    }

    public function scopeOptedOut($query)
    {
        return $query->whereRaw("status = 'opted_out'");
    }

    public function scopeForSubAccount($query, string $subAccountId)
    {
        return $query->where('sub_account_id', $subAccountId);
    }

    public function scopeByMobile($query, string $mobileNumber)
    {
        return $query->where('mobile_number', $mobileNumber);
    }

    public function scopeSearch($query, ?string $search)
    {
        if (!$search) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('first_name', 'ilike', "%{$search}%")
              ->orWhere('last_name', 'ilike', "%{$search}%")
              ->orWhere('email', 'ilike', "%{$search}%")
              ->orWhere('mobile_number', 'ilike', "%{$search}%");
        });
    }

    /**
     * Filter by date range on created_at.
     */
    public function scopeDateRange($query, ?string $from, ?string $to)
    {
        if ($from) {
            $query->where('contacts.created_at', '>=', $from);
        }
        if ($to) {
            $query->where('contacts.created_at', '<=', $to);
        }
        return $query;
    }

    /**
     * Filter by country ISO code.
     */
    public function scopeForCountry($query, string $country)
    {
        return $query->where('country', $country);
    }

    /**
     * Filter contacts that belong to a specific tag by ID.
     */
    public function scopeForTag($query, string $tagId)
    {
        return $query->whereHas('tags', fn($q) => $q->where('tags.id', $tagId));
    }

    /**
     * Filter contacts that belong to a specific list by ID.
     */
    public function scopeForList($query, string $listId)
    {
        return $query->whereHas('lists', fn($q) => $q->where('contact_lists.id', $listId));
    }

    // =====================================================
    // HELPERS
    // =====================================================

    public function getInitialsAttribute(): string
    {
        $first = $this->first_name ? strtoupper(substr($this->first_name, 0, 1)) : '';
        $last = $this->last_name ? strtoupper(substr($this->last_name, 0, 1)) : '';
        return $first . $last ?: '??';
    }

    public function getMobileMaskedAttribute(): string
    {
        $number = $this->mobile_number;
        if (!$number || strlen($number) < 7) {
            return '***';
        }
        return substr($number, 0, 4) . ' **** ' . substr($number, -3);
    }

    public function toPortalArray(): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'initials' => $this->initials,
            'email' => $this->email,
            'mobile_masked' => $this->mobile_masked,
            'tags' => $this->tags->pluck('name')->toArray(),
            'lists' => $this->lists->pluck('name')->toArray(),
            'status' => $this->getRawOriginal('status') ?? 'active',
            'source' => $this->getRawOriginal('source') ?? 'ui',
            'created_at' => $this->created_at?->format('Y-m-d'),
        ];
    }
}
