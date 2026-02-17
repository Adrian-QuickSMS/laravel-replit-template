<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * GREEN SIDE: Contact List (static + dynamic segments)
 *
 * DATA CLASSIFICATION: Internal - Tenant Organisation
 * SIDE: GREEN (customer portal accessible)
 * TENANT ISOLATION: MANDATORY account_id on every row + RLS
 */
class ContactList extends Model
{
    protected $table = 'contact_lists';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'account_id',
        'name',
        'description',
        'rules',
        'contact_count',
        'last_evaluated',
    ];

    protected $casts = [
        'id' => 'string',
        'account_id' => 'string',
        'rules' => 'array',
        'contact_count' => 'integer',
        'last_evaluated' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('tenant', function (Builder $builder) {
            if (auth()->check() && auth()->user()->tenant_id) {
                $builder->where('contact_lists.account_id', auth()->user()->tenant_id);
            }
        });
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function contacts(): BelongsToMany
    {
        return $this->belongsToMany(Contact::class, 'contact_list_member', 'list_id', 'contact_id')
            ->withPivot('created_at');
    }

    public function isStatic(): bool
    {
        return $this->getRawOriginal('type') === 'static';
    }

    public function isDynamic(): bool
    {
        return $this->getRawOriginal('type') === 'dynamic';
    }

    public function refreshContactCount(): void
    {
        if ($this->isStatic()) {
            $this->update(['contact_count' => $this->contacts()->count()]);
        }
    }

    public function toPortalArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'type' => $this->getRawOriginal('type') ?? 'static',
            'rules' => $this->rules,
            'contact_count' => $this->contact_count,
            'created_at' => $this->created_at?->format('Y-m-d'),
            'updated_at' => $this->updated_at?->format('Y-m-d'),
            'last_evaluated' => $this->last_evaluated?->format('Y-m-d'),
        ];
    }
}
