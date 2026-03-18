<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Events\Alerting\SubAccountSpendCapBreached;
use App\Events\Alerting\SubAccountSpendCapApproaching;
use App\Events\Alerting\SubAccountVolumeCapBreached;
use App\Events\Alerting\SubAccountVolumeCapApproaching;
use App\Events\Alerting\SubAccountDailyLimitBreached;
use App\Events\Alerting\SubAccountDailyLimitApproaching;

/**
 * GREEN SIDE: Sub-Account (Account Hierarchy: Account > Sub-Account > User)
 *
 * DATA CLASSIFICATION: Internal - Tenant Organisation
 * SIDE: GREEN (customer portal accessible)
 * TENANT ISOLATION: MANDATORY account_id on every row + RLS
 *
 * Hierarchy: Main Account → Sub-Account → User
 * Sub-account users can only see their own sub-account and below, never up or sideways.
 */
class SubAccount extends Model
{
    use SoftDeletes;

    protected $table = 'sub_accounts';

    // UUID primary key
    protected $keyType = 'string';
    public $incrementing = false;

    // Status constants
    const STATUS_LIVE = 'live';
    const STATUS_SUSPENDED = 'suspended';
    const STATUS_ARCHIVED = 'archived';

    // Enforcement type constants
    const ENFORCEMENT_WARN = 'warn';
    const ENFORCEMENT_BLOCK = 'block';
    const ENFORCEMENT_APPROVAL = 'approval';

    // Approaching-limit threshold (shared across all cap checks)
    const APPROACHING_THRESHOLD = 0.8;

    protected $fillable = [
        'account_id',
        'name',
        'description',
        'is_active',
        'created_by',
        // Limits & Enforcement
        'spending_limit',
        'monthly_spending_cap',
        'monthly_message_cap',
        'daily_send_limit',
        'enforcement_type',
        'hard_stop_enabled',
        'sub_account_status',
        // Audit
        'limits_updated_by',
        'limits_updated_at',
    ];

    protected $casts = [
        'id' => 'string',
        'account_id' => 'string',
        'created_by' => 'string',
        'is_active' => 'boolean',
        'spending_limit' => 'decimal:4',
        'spending_used_current_period' => 'decimal:4',
        'monthly_spending_cap' => 'decimal:4',
        'monthly_message_cap' => 'integer',
        'daily_send_limit' => 'integer',
        'monthly_spend_used' => 'decimal:4',
        'monthly_messages_used' => 'integer',
        'daily_sends_used' => 'integer',
        'hard_stop_enabled' => 'boolean',
        'limits_updated_at' => 'datetime',
        'daily_sends_reset_date' => 'date',
        'monthly_usage_reset_date' => 'date',
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

    public function invitations(): HasMany
    {
        return $this->hasMany(UserInvitation::class, 'sub_account_id');
    }

    public function countryPermissions(): HasMany
    {
        return $this->hasMany(SubAccountCountryPermission::class, 'sub_account_id');
    }

    // =====================================================
    // SCOPES
    // =====================================================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLive($query)
    {
        return $query->where('sub_account_status', self::STATUS_LIVE);
    }

    public function scopeSuspended($query)
    {
        return $query->where('sub_account_status', self::STATUS_SUSPENDED);
    }

    public function scopeForAccount($query, string $accountId)
    {
        return $query->withoutGlobalScope('tenant')->where('account_id', $accountId);
    }

    // =====================================================
    // STATUS METHODS
    // =====================================================

    public function isLive(): bool
    {
        return $this->sub_account_status === self::STATUS_LIVE;
    }

    public function isSuspended(): bool
    {
        return $this->sub_account_status === self::STATUS_SUSPENDED;
    }

    public function isArchived(): bool
    {
        return $this->sub_account_status === self::STATUS_ARCHIVED;
    }

    public function isActive(): bool
    {
        return $this->is_active === true;
    }

    public function suspend(): void
    {
        $this->update([
            'sub_account_status' => self::STATUS_SUSPENDED,
            'is_active' => false,
        ]);
    }

    public function reactivate(): void
    {
        $this->update([
            'sub_account_status' => self::STATUS_LIVE,
            'is_active' => true,
        ]);
    }

    public function archive(): void
    {
        if ($this->sub_account_status !== self::STATUS_SUSPENDED) {
            throw new \InvalidArgumentException('Can only archive a suspended sub-account');
        }
        $this->update([
            'sub_account_status' => self::STATUS_ARCHIVED,
            'is_active' => false,
        ]);
    }

    // =====================================================
    // LIMITS & ENFORCEMENT
    // =====================================================

    /**
     * Update limits with validation. Changes are logged in audit.
     */
    public function updateLimits(array $limits, string $updatedBy): void
    {
        $data = array_intersect_key($limits, array_flip([
            'monthly_spending_cap', 'monthly_message_cap', 'daily_send_limit',
            'enforcement_type', 'hard_stop_enabled',
        ]));

        $data['limits_updated_by'] = $updatedBy;
        $data['limits_updated_at'] = now();

        $this->update($data);

        Log::info('Sub-account limits updated', [
            'sub_account_id' => $this->id,
            'account_id' => $this->account_id,
            'updated_by' => $updatedBy,
            'changes' => $data,
        ]);
    }

    /**
     * Get current enforcement state based on usage vs caps.
     */
    public function getEnforcementState(): string
    {
        if ($this->isSpendingCapExceeded() || $this->isMessageCapExceeded() || $this->isDailyLimitExceeded()) {
            return $this->hard_stop_enabled ? 'blocked' : $this->enforcement_type;
        }

        // Check if approaching limits (80% threshold)
        if ($this->isApproachingLimits()) {
            return 'warning';
        }

        return 'normal';
    }

    public function isSpendingCapExceeded(): bool
    {
        if ($this->monthly_spending_cap === null) return false;
        return (float)$this->monthly_spend_used >= (float)$this->monthly_spending_cap;
    }

    public function isMessageCapExceeded(): bool
    {
        if ($this->monthly_message_cap === null) return false;
        return $this->monthly_messages_used >= $this->monthly_message_cap;
    }

    public function isDailyLimitExceeded(): bool
    {
        if ($this->daily_send_limit === null) return false;
        return $this->daily_sends_used >= $this->daily_send_limit;
    }

    public function isApproachingLimits(): bool
    {
        if ($this->monthly_spending_cap !== null && (float)$this->monthly_spend_used >= (float)$this->monthly_spending_cap * self::APPROACHING_THRESHOLD) {
            return true;
        }
        if ($this->monthly_message_cap !== null && $this->monthly_messages_used >= $this->monthly_message_cap * self::APPROACHING_THRESHOLD) {
            return true;
        }
        if ($this->daily_send_limit !== null && $this->daily_sends_used >= $this->daily_send_limit * self::APPROACHING_THRESHOLD) {
            return true;
        }

        return false;
    }

    /**
     * Check if sending is allowed based on current enforcement state.
     */
    public function canSend(): bool
    {
        if (!$this->isLive()) return false;

        if ($this->hard_stop_enabled) {
            return !$this->isSpendingCapExceeded()
                && !$this->isMessageCapExceeded()
                && !$this->isDailyLimitExceeded();
        }

        if ($this->enforcement_type === self::ENFORCEMENT_BLOCK) {
            return !$this->isSpendingCapExceeded()
                && !$this->isMessageCapExceeded()
                && !$this->isDailyLimitExceeded();
        }

        // warn and approval modes allow sending (approval requires separate workflow)
        return true;
    }

    // =====================================================
    // USAGE TRACKING
    // =====================================================

    /**
     * Record a sent message and update usage counters atomically.
     * Fires sub-account alerting events when caps are approached or breached.
     *
     * @param int $messageParts Number of message parts/fragments sent
     * @param float $cost Cost of the message in account currency
     */
    public function recordMessageSent(int $messageParts, float $cost): void
    {
        // Snapshot pre-update state for alert transition detection.
        // Captures both breached and approaching states so we only fire
        // events on the exact transition, not on every subsequent message.
        $prevState = $this->snapshotAlertState();

        $today = today()->toDateString();
        $monthStart = today()->startOfMonth()->toDateString();

        // Atomic update: reset counters if date/month changed, then increment — all in one query
        DB::statement("
            UPDATE sub_accounts SET
                daily_sends_used = CASE
                    WHEN daily_sends_reset_date IS NULL OR daily_sends_reset_date < ?
                    THEN 1 ELSE daily_sends_used + 1 END,
                daily_sends_reset_date = ?,
                monthly_spend_used = CASE
                    WHEN monthly_usage_reset_date IS NULL OR monthly_usage_reset_date < ?
                    THEN ? ELSE monthly_spend_used + ? END,
                monthly_messages_used = CASE
                    WHEN monthly_usage_reset_date IS NULL OR monthly_usage_reset_date < ?
                    THEN ? ELSE monthly_messages_used + ? END,
                monthly_usage_reset_date = ?
            WHERE id = ?
        ", [$today, $today, $monthStart, $cost, $cost, $monthStart, $messageParts, $messageParts, $monthStart, $this->id]);

        // Refresh from primary connection to get accurate post-update values
        // (avoids stale reads from read replicas)
        $fresh = static::withoutGlobalScopes()
            ->on(DB::getDefaultConnection())
            ->find($this->id);

        if ($fresh) {
            $this->setRawAttributes($fresh->getAttributes());
            $this->syncOriginal();
        }

        // Fire alerting events for sub-account caps
        $this->evaluateSubAccountAlerts($prevState);
    }

    /**
     * Snapshot the current alert-relevant state for transition detection.
     */
    private function snapshotAlertState(): array
    {
        $t = self::APPROACHING_THRESHOLD;

        return [
            'spend_exceeded' => $this->isSpendingCapExceeded(),
            'spend_approaching' => $this->monthly_spending_cap !== null
                && !$this->isSpendingCapExceeded()
                && (float) $this->monthly_spending_cap > 0
                && (float) $this->monthly_spend_used >= (float) $this->monthly_spending_cap * $t,

            'message_exceeded' => $this->isMessageCapExceeded(),
            'message_approaching' => $this->monthly_message_cap !== null
                && !$this->isMessageCapExceeded()
                && (int) $this->monthly_message_cap > 0
                && (int) $this->monthly_messages_used >= (int) $this->monthly_message_cap * $t,

            'daily_exceeded' => $this->isDailyLimitExceeded(),
            'daily_approaching' => $this->daily_send_limit !== null
                && !$this->isDailyLimitExceeded()
                && (int) $this->daily_send_limit > 0
                && (int) $this->daily_sends_used >= (int) $this->daily_send_limit * $t,
        ];
    }

    /**
     * Evaluate and fire sub-account cap/limit alert events.
     * Only fires events on state transitions (not-approaching -> approaching,
     * not-breached -> breached) to prevent event storms.
     */
    private function evaluateSubAccountAlerts(array $prevState): void
    {
        $t = self::APPROACHING_THRESHOLD;

        // --- Monthly Spend Cap ---
        if ($this->monthly_spending_cap !== null) {
            $spendUsed = (float) $this->monthly_spend_used;
            $spendCap = (float) $this->monthly_spending_cap;

            if ($this->isSpendingCapExceeded() && !$prevState['spend_exceeded']) {
                Log::info('[SubAccountAlert] Spend cap breached', [
                    'sub_account_id' => $this->id,
                    'account_id' => $this->account_id,
                    'spend_used' => $spendUsed,
                    'spend_cap' => $spendCap,
                    'hard_stopped' => (bool) $this->hard_stop_enabled,
                ]);
                SubAccountSpendCapBreached::dispatch(
                    $this->account_id,
                    $this->id,
                    $this->name,
                    $spendUsed,
                    $spendCap,
                    (bool) $this->hard_stop_enabled,
                    $this->hard_stop_enabled ? 'critical' : 'warning',
                );
            } elseif (
                !$this->isSpendingCapExceeded()
                && $spendCap > 0
                && $spendUsed >= $spendCap * $t
                && !$prevState['spend_approaching']
            ) {
                Log::info('[SubAccountAlert] Approaching spend cap', [
                    'sub_account_id' => $this->id,
                    'account_id' => $this->account_id,
                    'spend_used' => $spendUsed,
                    'spend_cap' => $spendCap,
                    'percentage' => round(($spendUsed / $spendCap) * 100, 1),
                ]);
                SubAccountSpendCapApproaching::dispatch(
                    $this->account_id,
                    $this->id,
                    $this->name,
                    $spendUsed,
                    $spendCap,
                );
            }
        }

        // --- Monthly Message Cap ---
        if ($this->monthly_message_cap !== null) {
            $messagesUsed = (int) $this->monthly_messages_used;
            $messageCap = (int) $this->monthly_message_cap;

            if ($this->isMessageCapExceeded() && !$prevState['message_exceeded']) {
                Log::info('[SubAccountAlert] Message cap breached', [
                    'sub_account_id' => $this->id,
                    'account_id' => $this->account_id,
                    'messages_used' => $messagesUsed,
                    'message_cap' => $messageCap,
                    'hard_stopped' => (bool) $this->hard_stop_enabled,
                ]);
                SubAccountVolumeCapBreached::dispatch(
                    $this->account_id,
                    $this->id,
                    $this->name,
                    $messagesUsed,
                    $messageCap,
                    (bool) $this->hard_stop_enabled,
                    $this->hard_stop_enabled ? 'critical' : 'warning',
                );
            } elseif (
                !$this->isMessageCapExceeded()
                && $messageCap > 0
                && $messagesUsed >= $messageCap * $t
                && !$prevState['message_approaching']
            ) {
                Log::info('[SubAccountAlert] Approaching message cap', [
                    'sub_account_id' => $this->id,
                    'account_id' => $this->account_id,
                    'messages_used' => $messagesUsed,
                    'message_cap' => $messageCap,
                    'percentage' => round(($messagesUsed / $messageCap) * 100, 1),
                ]);
                SubAccountVolumeCapApproaching::dispatch(
                    $this->account_id,
                    $this->id,
                    $this->name,
                    $messagesUsed,
                    $messageCap,
                );
            }
        }

        // --- Daily Send Limit ---
        if ($this->daily_send_limit !== null) {
            $dailyUsed = (int) $this->daily_sends_used;
            $dailyLimit = (int) $this->daily_send_limit;

            if ($this->isDailyLimitExceeded() && !$prevState['daily_exceeded']) {
                Log::info('[SubAccountAlert] Daily limit breached', [
                    'sub_account_id' => $this->id,
                    'account_id' => $this->account_id,
                    'daily_used' => $dailyUsed,
                    'daily_limit' => $dailyLimit,
                    'hard_stopped' => (bool) $this->hard_stop_enabled,
                ]);
                SubAccountDailyLimitBreached::dispatch(
                    $this->account_id,
                    $this->id,
                    $this->name,
                    $dailyUsed,
                    $dailyLimit,
                    (bool) $this->hard_stop_enabled,
                );
            } elseif (
                !$this->isDailyLimitExceeded()
                && $dailyLimit > 0
                && $dailyUsed >= $dailyLimit * $t
                && !$prevState['daily_approaching']
            ) {
                Log::info('[SubAccountAlert] Approaching daily limit', [
                    'sub_account_id' => $this->id,
                    'account_id' => $this->account_id,
                    'daily_used' => $dailyUsed,
                    'daily_limit' => $dailyLimit,
                    'percentage' => round(($dailyUsed / $dailyLimit) * 100, 1),
                ]);
                SubAccountDailyLimitApproaching::dispatch(
                    $this->account_id,
                    $this->id,
                    $this->name,
                    $dailyUsed,
                    $dailyLimit,
                );
            }
        }
    }

    // =====================================================
    // PORTAL METHODS
    // =====================================================

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
            'status' => $this->sub_account_status ?? self::STATUS_LIVE,
            'user_count' => $this->users_count ?? $this->users()->count(),
            'limits' => [
                'monthly_spending_cap' => $this->monthly_spending_cap,
                'monthly_message_cap' => $this->monthly_message_cap,
                'daily_send_limit' => $this->daily_send_limit,
                'enforcement_type' => $this->enforcement_type ?? self::ENFORCEMENT_WARN,
                'hard_stop_enabled' => $this->hard_stop_enabled ?? false,
            ],
            'usage' => [
                'monthly_spend_used' => (float)($this->monthly_spend_used ?? 0),
                'monthly_messages_used' => $this->monthly_messages_used ?? 0,
                'daily_sends_used' => $this->daily_sends_used ?? 0,
            ],
            'enforcement_state' => $this->getEnforcementState(),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
