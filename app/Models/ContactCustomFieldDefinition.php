<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * GREEN SIDE: Custom Field Definition (EAV schema layer)
 *
 * DATA CLASSIFICATION: Internal - Tenant Configuration
 * SIDE: GREEN (customer portal accessible)
 * TENANT ISOLATION: MANDATORY account_id on every row + RLS
 */
class ContactCustomFieldDefinition extends Model
{
    protected $table = 'contact_custom_field_definitions';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'account_id',
        'field_name',
        'field_label',
        'description',
        'enum_options',
        'is_required',
        'sort_order',
    ];

    protected $casts = [
        'id' => 'string',
        'account_id' => 'string',
        'enum_options' => 'array',
        'is_required' => 'boolean',
        'sort_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('tenant', function (Builder $builder) {
            if (auth()->check() && auth()->user()->tenant_id) {
                $builder->where('contact_custom_field_definitions.account_id', auth()->user()->tenant_id);
            }
        });
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }
}
