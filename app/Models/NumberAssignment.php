<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

/**
 * Number Assignment — polymorphic distribution to sub-accounts and users.
 *
 * Follows the same pattern as SenderIdAssignment.
 * assignable_type: App\Models\SubAccount or App\Models\User
 * assignable_id: UUID of the assigned entity
 */
class NumberAssignment extends Model
{
    protected $table = 'number_assignments';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'purchased_number_id',
        'assignable_type',
        'assignable_id',
        'assigned_by',
    ];

    protected $casts = [
        'purchased_number_id' => 'string',
        'assignable_id' => 'string',
        'assigned_by' => 'string',
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

    // =====================================================
    // RELATIONSHIPS
    // =====================================================

    public function purchasedNumber(): BelongsTo
    {
        return $this->belongsTo(PurchasedNumber::class, 'purchased_number_id');
    }

    public function assignable(): MorphTo
    {
        return $this->morphTo();
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
