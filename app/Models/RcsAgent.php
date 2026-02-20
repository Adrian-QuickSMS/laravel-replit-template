<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * GREEN SIDE: RCS Agent Registration & Approval Workflow
 *
 * DATA CLASSIFICATION: Internal - Messaging Asset
 * SIDE: GREEN (customer portal accessible for own account)
 * TENANT ISOLATION: account_id scoped via RLS + global scope
 *
 * State machine follows the SenderId approval pattern with extended states
 * for pending_info/info_provided and suspension/revocation.
 */
class RcsAgent extends Model
{
    use SoftDeletes;

    protected $table = 'rcs_agents';

    // =====================================================
    // STATUS CONSTANTS
    // =====================================================

    const STATUS_DRAFT = 'draft';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_IN_REVIEW = 'in_review';
    const STATUS_PENDING_INFO = 'pending_info';
    const STATUS_INFO_PROVIDED = 'info_provided';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_SUSPENDED = 'suspended';
    const STATUS_REVOKED = 'revoked';

    // =====================================================
    // BILLING CATEGORY CONSTANTS
    // =====================================================

    const BILLING_CONVERSATIONAL = 'conversational';
    const BILLING_NON_CONVERSATIONAL = 'non-conversational';

    // =====================================================
    // USE CASE CONSTANTS
    // =====================================================

    const USE_CASE_OTP = 'otp';
    const USE_CASE_TRANSACTIONAL = 'transactional';
    const USE_CASE_PROMOTIONAL = 'promotional';
    const USE_CASE_MULTI_USE = 'multi-use';

    // =====================================================
    // STATE MACHINE: Allowed transitions
    // =====================================================

    const TRANSITIONS = [
        self::STATUS_DRAFT         => [self::STATUS_SUBMITTED],
        self::STATUS_SUBMITTED     => [self::STATUS_IN_REVIEW],
        self::STATUS_IN_REVIEW     => [self::STATUS_APPROVED, self::STATUS_REJECTED, self::STATUS_PENDING_INFO],
        self::STATUS_PENDING_INFO  => [self::STATUS_INFO_PROVIDED],
        self::STATUS_INFO_PROVIDED => [self::STATUS_IN_REVIEW, self::STATUS_APPROVED, self::STATUS_REJECTED, self::STATUS_PENDING_INFO],
        self::STATUS_APPROVED      => [self::STATUS_SUSPENDED],
        self::STATUS_REJECTED      => [self::STATUS_DRAFT],
        self::STATUS_SUSPENDED     => [self::STATUS_APPROVED, self::STATUS_REVOKED],
        self::STATUS_REVOKED       => [], // terminal state
    ];

    // Who can trigger each transition target
    const TRANSITION_ACTORS = [
        self::STATUS_SUBMITTED     => 'customer',
        self::STATUS_IN_REVIEW     => 'admin',
        self::STATUS_APPROVED      => 'admin',
        self::STATUS_REJECTED      => 'admin',
        self::STATUS_PENDING_INFO  => 'admin',
        self::STATUS_INFO_PROVIDED => 'customer',
        self::STATUS_SUSPENDED     => 'admin',
        self::STATUS_REVOKED       => 'admin',
        self::STATUS_DRAFT         => 'customer', // re-edit after rejection
    ];

    // =====================================================
    // MODEL CONFIGURATION
    // =====================================================

    protected $fillable = [
        'uuid',
        'account_id',
        'name',
        'description',
        'brand_color',
        'logo_url',
        'logo_crop_metadata',
        'hero_url',
        'hero_crop_metadata',
        'support_phone',
        'website',
        'support_email',
        'privacy_url',
        'terms_url',
        'show_phone',
        'show_website',
        'show_email',
        'billing_category',
        'use_case',
        'campaign_frequency',
        'monthly_volume',
        'opt_in_description',
        'opt_out_description',
        'use_case_overview',
        'test_numbers',
        'company_number',
        'company_website',
        'registered_address',
        'approver_name',
        'approver_job_title',
        'approver_email',
        'workflow_status',
        'submitted_at',
        'reviewed_by',
        'reviewed_at',
        'rejection_reason',
        'admin_notes',
        'suspension_reason',
        'revocation_reason',
        'additional_info',
        'version',
        'version_history',
        'full_payload',
        'is_locked',
        'created_by',
    ];

    protected $casts = [
        'account_id' => 'string',
        'created_by' => 'string',
        'reviewed_by' => 'string',
        'logo_crop_metadata' => 'array',
        'hero_crop_metadata' => 'array',
        'test_numbers' => 'array',
        'full_payload' => 'array',
        'version_history' => 'array',
        'show_phone' => 'boolean',
        'show_website' => 'boolean',
        'show_email' => 'boolean',
        'is_locked' => 'boolean',
        'version' => 'integer',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $hidden = [
        'admin_notes', // RED side - never expose to customer portal
    ];

    protected static function boot()
    {
        parent::boot();

        // Auto-generate UUID on create
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });

        // Auto-scope by tenant if authenticated
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (auth()->check() && auth()->user()->tenant_id) {
                $builder->where('rcs_agents.account_id', auth()->user()->tenant_id);
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

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(RcsAgentAssignment::class, 'rcs_agent_id');
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(RcsAgentStatusHistory::class, 'rcs_agent_id')
            ->orderBy('created_at', 'desc');
    }

    // Backward-compatible alias
    public function statusHistories(): HasMany
    {
        return $this->statusHistory();
    }

    public function comments(): HasMany
    {
        return $this->hasMany(RcsAgentComment::class, 'rcs_agent_id');
    }

    public function customerComments(): HasMany
    {
        return $this->comments()->customerVisible()->orderBy('created_at', 'desc');
    }

    public function internalComments(): HasMany
    {
        return $this->comments()->internal()->orderBy('created_at', 'desc');
    }

    // =====================================================
    // STATE MACHINE
    // =====================================================

    /**
     * Transition to a new workflow status with full validation and audit trail
     *
     * @param string $newStatus Target status
     * @param string|null $actorId User ID performing the transition
     * @param string|null $reason Reason for the transition
     * @param string|null $notes Additional notes
     * @param mixed $actingUser User model for audit (name, email)
     * @return bool
     * @throws \InvalidArgumentException if transition is not allowed
     */
    public function transitionTo(
        string $newStatus,
        ?string $actorId = null,
        ?string $reason = null,
        ?string $notes = null,
        $actingUser = null
    ): bool {
        $oldStatus = $this->workflow_status;

        // Validate transition is allowed
        $allowedTransitions = self::TRANSITIONS[$oldStatus] ?? [];
        if (!in_array($newStatus, $allowedTransitions)) {
            throw new \InvalidArgumentException(
                "Invalid transition from '{$oldStatus}' to '{$newStatus}'. Allowed: " . implode(', ', $allowedTransitions)
            );
        }

        // Apply status-specific logic
        switch ($newStatus) {
            case self::STATUS_SUBMITTED:
                $this->full_payload = $this->toArray();
                $this->submitted_at = now();
                $this->is_locked = true;
                break;

            case self::STATUS_IN_REVIEW:
                $this->is_locked = true;
                break;

            case self::STATUS_APPROVED:
                $this->reviewed_at = now();
                $this->reviewed_by = $actorId;
                $this->is_locked = true;
                $this->rejection_reason = null;
                break;

            case self::STATUS_REJECTED:
                $this->reviewed_at = now();
                $this->reviewed_by = $actorId;
                $this->rejection_reason = $reason;
                $this->is_locked = false;
                $this->full_payload = array_merge($this->full_payload ?? [], [
                    'rejection_reason' => $reason,
                    'reviewed_at' => now()->toIso8601String(),
                ]);
                break;

            case self::STATUS_PENDING_INFO:
                $this->admin_notes = $notes ?? $this->admin_notes;
                break;

            case self::STATUS_INFO_PROVIDED:
                $this->additional_info = $reason; // customer's response
                break;

            case self::STATUS_DRAFT:
                // Re-edit after rejection: bump version, archive previous state
                $this->is_locked = false;
                $this->version = ($this->version ?? 1) + 1;
                $history = $this->version_history ?? [];
                $history[] = [
                    'version' => $this->version - 1,
                    'payload' => $this->full_payload,
                    'rejected_at' => $this->reviewed_at?->toIso8601String(),
                    'rejection_reason' => $this->rejection_reason,
                ];
                $this->version_history = $history;
                $this->rejection_reason = null;
                $this->reviewed_at = null;
                $this->reviewed_by = null;
                break;

            case self::STATUS_SUSPENDED:
                $this->suspension_reason = $reason;
                $this->is_locked = true;
                break;

            case self::STATUS_REVOKED:
                $this->revocation_reason = $reason;
                $this->is_locked = true;
                break;
        }

        $this->workflow_status = $newStatus;
        $this->save();

        // Record status history
        $this->recordStatusHistory(
            $oldStatus,
            $newStatus,
            $this->getActionForTransition($oldStatus, $newStatus),
            $actorId,
            $reason,
            $notes,
            $actingUser
        );

        return true;
    }

    /**
     * Backward-compatible transition method (delegates to transitionTo)
     */
    public function transitionStatus(string $newStatus, $userId, ?string $reason = null, ?string $notes = null, $actingUser = null): void
    {
        $this->transitionTo($newStatus, (string) $userId, $reason, $notes, $actingUser);
    }

    /**
     * Record a status transition in the audit history
     */
    public function recordStatusHistory(
        ?string $fromStatus,
        string $toStatus,
        string $action,
        ?string $userId = null,
        ?string $reason = null,
        ?string $notes = null,
        $actingUser = null
    ): RcsAgentStatusHistory {
        $ipAddress = null;
        $userAgent = null;

        try {
            $ipAddress = request()->ip();
            $userAgent = request()->userAgent();
        } catch (\Exception $e) {
            // CLI or queue context
        }

        return $this->statusHistory()->create([
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'action' => $action,
            'reason' => $reason,
            'notes' => $notes,
            'payload_snapshot' => $this->full_payload,
            'user_id' => $userId,
            'user_name' => $actingUser ? ($actingUser->first_name . ' ' . $actingUser->last_name) : null,
            'user_email' => $actingUser ? $actingUser->email : null,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);
    }

    /**
     * Map transitions to human-readable action names
     */
    protected function getActionForTransition(?string $from, string $to): string
    {
        $transitions = [
            'draft_submitted' => 'submitted',
            'submitted_in_review' => 'review_started',
            'in_review_approved' => 'approved',
            'in_review_rejected' => 'rejected',
            'in_review_pending_info' => 'info_requested',
            'pending_info_info_provided' => 'info_provided',
            'info_provided_in_review' => 'review_resumed',
            'rejected_draft' => 'resubmission_started',
            'approved_suspended' => 'suspended',
            'suspended_approved' => 'reactivated',
            'suspended_revoked' => 'revoked',
        ];

        $key = ($from ?? 'null') . '_' . $to;
        return $transitions[$key] ?? 'status_changed';
    }

    // =====================================================
    // STATUS CHECKS
    // =====================================================

    public function isUsable(): bool
    {
        return $this->workflow_status === self::STATUS_APPROVED;
    }

    public function isEditable(): bool
    {
        return in_array($this->workflow_status, [self::STATUS_DRAFT, self::STATUS_REJECTED]);
    }

    public function isLocked(): bool
    {
        return in_array($this->workflow_status, [
            self::STATUS_SUBMITTED,
            self::STATUS_IN_REVIEW,
            self::STATUS_PENDING_INFO,
            self::STATUS_APPROVED,
            self::STATUS_SUSPENDED,
            self::STATUS_REVOKED,
        ]);
    }

    public function isPending(): bool
    {
        return in_array($this->workflow_status, [
            self::STATUS_SUBMITTED,
            self::STATUS_IN_REVIEW,
            self::STATUS_PENDING_INFO,
            self::STATUS_INFO_PROVIDED,
        ]);
    }

    public function canCustomerProvideInfo(): bool
    {
        return $this->workflow_status === self::STATUS_PENDING_INFO;
    }

    // =====================================================
    // SCOPES
    // =====================================================

    public function scopeUsable($query)
    {
        return $query->where('workflow_status', self::STATUS_APPROVED);
    }

    public function scopePending($query)
    {
        return $query->whereIn('workflow_status', [
            self::STATUS_SUBMITTED,
            self::STATUS_IN_REVIEW,
            self::STATUS_PENDING_INFO,
            self::STATUS_INFO_PROVIDED,
        ]);
    }

    public function scopeForAccount($query, string $accountId)
    {
        return $query->withoutGlobalScope('tenant')->where('account_id', $accountId);
    }

    /**
     * Get approved RCS Agents usable by a specific user
     * Checks: account-level (no assignments = available to all) OR assigned to user OR assigned to user's sub-account
     */
    public function scopeUsableByUser($query, User $user)
    {
        return $query->where('workflow_status', self::STATUS_APPROVED)
            ->where('account_id', $user->tenant_id)
            ->where(function ($q) use ($user) {
                // Agents with no assignments are available to all users on the account
                $q->whereDoesntHave('assignments')
                    // OR assigned directly to this user
                    ->orWhereHas('assignments', function ($assignQ) use ($user) {
                        $assignQ->where(function ($innerQ) use ($user) {
                            $innerQ->where('assignable_type', User::class)
                                ->where('assignable_id', $user->id);
                        });
                        if ($user->sub_account_id) {
                            $assignQ->orWhere(function ($innerQ) use ($user) {
                                $innerQ->where('assignable_type', SubAccount::class)
                                    ->where('assignable_id', $user->sub_account_id);
                            });
                        }
                    });
            });
    }

    // =====================================================
    // PORTAL METHODS
    // =====================================================

    /**
     * Format for customer portal display
     * NEVER expose admin_notes (RED side)
     */
    public function toPortalArray(): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'name' => $this->name,
            'description' => $this->description,
            'brand_color' => $this->brand_color,
            'logo_url' => $this->logo_url,
            'hero_url' => $this->hero_url,
            'billing_category' => $this->billing_category,
            'use_case' => $this->use_case,
            'support_phone' => $this->support_phone,
            'website' => $this->website,
            'support_email' => $this->support_email,
            'privacy_url' => $this->privacy_url,
            'terms_url' => $this->terms_url,
            'show_phone' => $this->show_phone,
            'show_website' => $this->show_website,
            'show_email' => $this->show_email,
            'campaign_frequency' => $this->campaign_frequency,
            'monthly_volume' => $this->monthly_volume,
            'opt_in_description' => $this->opt_in_description,
            'opt_out_description' => $this->opt_out_description,
            'use_case_overview' => $this->use_case_overview,
            'company_number' => $this->company_number,
            'company_website' => $this->company_website,
            'registered_address' => $this->registered_address,
            'approver_name' => $this->approver_name,
            'approver_job_title' => $this->approver_job_title,
            'approver_email' => $this->approver_email,
            'workflow_status' => $this->workflow_status,
            'submitted_at' => $this->submitted_at?->toIso8601String(),
            'reviewed_at' => $this->reviewed_at?->toIso8601String(),
            'rejection_reason' => $this->rejection_reason,
            'version' => $this->version,
            'is_locked' => $this->is_locked,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }

    /**
     * Format for admin display (includes RED side data)
     */
    public function toAdminArray(): array
    {
        return array_merge($this->toPortalArray(), [
            'admin_notes' => $this->admin_notes,
            'suspension_reason' => $this->suspension_reason,
            'revocation_reason' => $this->revocation_reason,
            'additional_info' => $this->additional_info,
            'full_payload' => $this->full_payload,
            'version_history' => $this->version_history,
            'logo_crop_metadata' => $this->logo_crop_metadata,
            'hero_crop_metadata' => $this->hero_crop_metadata,
            'test_numbers' => $this->test_numbers,
            'created_by' => $this->created_by,
            'reviewed_by' => $this->reviewed_by,
            'account_id' => $this->account_id,
        ]);
    }
}
