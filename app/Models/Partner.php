<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Partner (Reseller / White-label tenant root)
 *
 * SIDE: Cross-zone — owned by RED (admin-managed) but referenced by GREEN
 *       partner-portal queries via app.current_partner_id RLS context.
 *
 * No business logic in this PR — only data shape and the accounts relation.
 * Status transitions, suspension flow, branding link, etc. ship in later PRs.
 */
class Partner extends Model
{
    use SoftDeletes;

    protected $table = 'partners';

    protected $keyType = 'string';
    public $incrementing = false;

    const STATUS_ACTIVE = 'active';
    const STATUS_SUSPENDED = 'suspended';
    const STATUS_CLOSED = 'closed';

    const STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_SUSPENDED,
        self::STATUS_CLOSED,
    ];

    protected $fillable = [
        'legal_name',
        'trading_name',
        'contract_type',
        'currency',
        'owner_account_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'id' => 'string',
        'owner_account_id' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class, 'partner_id');
    }

    public function ownerAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'owner_account_id');
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }
}
