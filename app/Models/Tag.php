<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * GREEN SIDE: Tag (colour-coded contact labels)
 *
 * DATA CLASSIFICATION: Internal - Tenant Organisation
 * SIDE: GREEN (customer portal accessible)
 * TENANT ISOLATION: MANDATORY account_id on every row + RLS
 */
class Tag extends Model
{
    protected $table = 'tags';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'account_id',
        'name',
        'color',
        'contact_count',
        'last_used',
    ];

    protected $casts = [
        'id' => 'string',
        'account_id' => 'string',
        'contact_count' => 'integer',
        'last_used' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('tenant', function (Builder $builder) {
            $tenantId = auth()->check() && auth()->user()->tenant_id
                ? auth()->user()->tenant_id
                : session('customer_tenant_id');
            if ($tenantId) {
                $builder->where('tags.account_id', $tenantId);
            }
        });
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function contacts(): BelongsToMany
    {
        return $this->belongsToMany(Contact::class, 'contact_tag')
            ->withPivot('created_at');
    }

    public function refreshContactCount(): void
    {
        $this->update(['contact_count' => $this->contacts()->count()]);
    }

    public function toPortalArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'color' => $this->color,
            'contact_count' => $this->contact_count,
            'created_at' => $this->created_at?->format('Y-m-d'),
            'last_used' => $this->last_used?->format('Y-m-d'),
            'source' => $this->getRawOriginal('source') ?? 'manual',
        ];
    }
}
