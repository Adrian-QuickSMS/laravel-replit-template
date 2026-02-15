<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * GREEN SIDE: Sub-Account (Account Hierarchy: Account > Sub-Account > User)
 *
 * DATA CLASSIFICATION: Internal - Tenant Organisation
 * SIDE: GREEN (customer portal accessible)
 * TENANT ISOLATION: MANDATORY account_id on every row + RLS
 */
class SubAccount extends Model
{
    use SoftDeletes;

    protected $table = 'sub_accounts';

    // UUID primary key
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'account_id',
        'name',
        'description',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'id' => 'string',
        'account_id' => 'string',
        'created_by' => 'string',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        // Auto-scope by tenant_id if authenticated
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (auth()->check() && auth()->user()->tenant_id) {
                $builder->where('sub_accounts.account_id', auth()->user()->tenant_id);
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

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'sub_account_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function senderIdAssignments(): MorphMany
    {
        return $this->morphMany(SenderIdAssignment::class, 'assignable');
    }

    // =====================================================
    // SCOPES
    // =====================================================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForAccount($query, string $accountId)
    {
        return $query->withoutGlobalScope('tenant')->where('account_id', $accountId);
    }

    // =====================================================
    // HELPER METHODS
    // =====================================================

    public function isActive(): bool
    {
        return $this->is_active === true;
    }

    /**
     * Get approved SenderIDs assigned to this sub-account
     */
    public function getAssignedSenderIds()
    {
        return SenderId::whereHas('assignments', function ($query) {
            $query->where('assignable_type', self::class)
                ->where('assignable_id', $this->id);
        })->where('workflow_status', SenderId::STATUS_APPROVED)->get();
    }

    public function toPortalArray(): array
    {
        return [
            'id' => $this->id,
            'account_id' => $this->account_id,
            'name' => $this->name,
            'description' => $this->description,
            'is_active' => $this->is_active,
            'user_count' => $this->users()->count(),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
