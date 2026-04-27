<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Partner (Reseller / White-label tenant root)
 *
 * SIDE: Cross-zone — owned by RED (admin-managed) but referenced by GREEN
 *       partner-portal queries via app.current_partner_id RLS context.
 *
 * LIFECYCLE: status ('active' | 'suspended' | 'closed') is the single source
 *            of truth for whether a partner is operational. There is no
 *            soft-delete — child accounts.partner_id always points at a real
 *            row that callers can resolve. Use transitionTo('closed') as the
 *            equivalent of "deleting" a partner.
 *
 * SECURITY NOTE: 'status' is excluded from $fillable. Status changes go
 *                through transitionTo() to enforce the allowed-transition
 *                map and to provide a single audit-friendly entry point
 *                (mirrors Account::transitionTo()).
 */
class Partner extends Model
{
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

    const STATUS_TRANSITIONS = [
        self::STATUS_ACTIVE => [self::STATUS_SUSPENDED, self::STATUS_CLOSED],
        self::STATUS_SUSPENDED => [self::STATUS_ACTIVE, self::STATUS_CLOSED],
        self::STATUS_CLOSED => [], // Terminal — partners cannot be reopened
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

    /**
     * Child accounts owned by this partner.
     *
     * NOTE: Until the partner-scope RLS predicate ships (Phase 0, PR-3), this
     * relation returns [] for portal_rw sessions because the accounts RLS
     * policy still keys on app.current_tenant_id only. svc_red queries
     * (admin) work today because that role bypasses RLS.
     */
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

    public function isClosed(): bool
    {
        return $this->status === self::STATUS_CLOSED;
    }

    public function canTransitionTo(string $newStatus): bool
    {
        $allowed = self::STATUS_TRANSITIONS[$this->status] ?? [];
        return in_array($newStatus, $allowed);
    }

    /**
     * Transition partner to a new status with validation and pessimistic
     * locking. Mirrors Account::transitionTo() — see that method for the
     * lock-ordering contract.
     *
     * @throws \InvalidArgumentException if the transition is not allowed
     */
    public function transitionTo(string $newStatus): self
    {
        return DB::transaction(function () use ($newStatus) {
            $locked = static::lockForUpdate()->findOrFail($this->id);

            if (! $locked->canTransitionTo($newStatus)) {
                throw new \InvalidArgumentException(
                    "Cannot transition partner from '{$locked->status}' to '{$newStatus}'"
                );
            }

            $oldStatus = $locked->status;
            $locked->update(['status' => $newStatus]);

            $this->status = $newStatus;

            Log::info('Partner status transitioned', [
                'partner_id' => $this->id,
                'from' => $oldStatus,
                'to' => $newStatus,
            ]);

            return $this;
        });
    }
}
