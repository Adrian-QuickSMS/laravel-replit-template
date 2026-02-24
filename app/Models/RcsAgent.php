<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\SubAccount;

class RcsAgent extends Model
{
    use HasFactory, SoftDeletes;

    const STATUS_DRAFT = 'draft';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_IN_REVIEW = 'in_review';
    const STATUS_PENDING_INFO = 'pending_info';
    const STATUS_INFO_PROVIDED = 'info_provided';
    const STATUS_SENT_TO_SUPPLIER = 'sent_to_supplier';
    const STATUS_SUPPLIER_APPROVED = 'supplier_approved';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_SUSPENDED = 'suspended';
    const STATUS_REVOKED = 'revoked';

    // =====================================================
    // STATE MACHINE: Allowed transitions
    // =====================================================

    const TRANSITIONS = [
        self::STATUS_DRAFT         => [self::STATUS_SUBMITTED],
        self::STATUS_SUBMITTED     => [self::STATUS_IN_REVIEW, self::STATUS_SENT_TO_SUPPLIER, self::STATUS_REJECTED, self::STATUS_PENDING_INFO],
        self::STATUS_IN_REVIEW     => [self::STATUS_APPROVED, self::STATUS_REJECTED, self::STATUS_PENDING_INFO, self::STATUS_SENT_TO_SUPPLIER],
        self::STATUS_PENDING_INFO  => [self::STATUS_SUBMITTED, self::STATUS_INFO_PROVIDED, self::STATUS_DRAFT],
        self::STATUS_INFO_PROVIDED => [self::STATUS_SENT_TO_SUPPLIER, self::STATUS_REJECTED, self::STATUS_PENDING_INFO, self::STATUS_IN_REVIEW],
        self::STATUS_SENT_TO_SUPPLIER => [self::STATUS_SUPPLIER_APPROVED],
        self::STATUS_SUPPLIER_APPROVED => [self::STATUS_APPROVED, self::STATUS_SUSPENDED],
        self::STATUS_APPROVED      => [self::STATUS_SUSPENDED],
        self::STATUS_REJECTED      => [self::STATUS_SUBMITTED, self::STATUS_DRAFT],
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
        self::STATUS_SENT_TO_SUPPLIER => 'admin',
        self::STATUS_SUPPLIER_APPROVED => 'admin',
        self::STATUS_SUSPENDED     => 'admin',
        self::STATUS_REVOKED       => 'admin',
        self::STATUS_DRAFT         => 'customer',
    ];

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
        'sector',
        'workflow_status',
        'additional_info',
        'created_by',
    ];

    /**
     * Admin-only fields excluded from $fillable (set only via transitionTo/forceFill):
     * rejection_reason, admin_notes, suspension_reason, revocation_reason,
     * submitted_at, reviewed_at, reviewed_by, full_payload, is_locked, version, version_history
     */

    protected $hidden = [
        'admin_notes', // RED side - never expose to customer portal
    ];

    protected $casts = [
        'logo_crop_metadata' => 'array',
        'hero_crop_metadata' => 'array',
        'test_numbers' => 'array',
        'full_payload' => 'array',
        'registered_address' => 'array',
        'version_history' => 'array',
        'show_phone' => 'boolean',
        'show_website' => 'boolean',
        'show_email' => 'boolean',
        'is_locked' => 'boolean',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });

        // Fail-closed tenant scope — matches Contact, ApiConnection, SenderId pattern
        static::addGlobalScope('tenant', function (Builder $builder) {
            $tenantId = auth()->check() && auth()->user()->tenant_id
                ? auth()->user()->tenant_id
                : session('customer_tenant_id');
            if ($tenantId) {
                $builder->where('rcs_agents.account_id', $tenantId);
            } else {
                $builder->whereRaw('1 = 0');
            }
        });
    }

    public function statusHistories()
    {
        return $this->hasMany(RcsAgentStatusHistory::class)->orderBy('created_at', 'desc');
    }

    public function toPortalArray(): array
    {
        return [
            'id' => $this->uuid,
            'name' => $this->name,
            'description' => $this->description,
            'status' => str_replace('_', '-', $this->workflow_status ?? 'draft'),
            'billing' => $this->billing_category,
            'useCase' => str_replace('_', '-', $this->use_case ?? ''),
            'created' => $this->created_at ? $this->created_at->toDateString() : null,
            'updated' => $this->updated_at ? $this->updated_at->toDateString() : null,
            'rejectionReason' => $this->rejection_reason,
            'brandColor' => $this->brand_color ?? '#886CC0',
            'logoUrl' => $this->logo_url,
            'heroUrl' => $this->hero_url,
            'supportPhone' => $this->support_phone,
            'showPhone' => (bool) $this->show_phone,
            'website' => $this->website,
            'supportEmail' => $this->support_email,
            'showEmail' => (bool) $this->show_email,
            'privacyUrl' => $this->privacy_url,
            'termsUrl' => $this->terms_url,
            'useCaseOverview' => $this->use_case_overview,
            'userConsent' => !empty($this->opt_in_description),
            'optOutAvailable' => !empty($this->opt_out_description),
            'monthlyVolume' => $this->monthly_volume,
            'testNumbers' => $this->test_numbers ?? [],
            'companyName' => $this->account ? $this->account->company_name : null,
            'companyNumber' => $this->company_number,
            'approverName' => $this->approver_name,
            'approverJobTitle' => $this->approver_job_title,
            'approverEmail' => $this->approver_email,
        ];
    }

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function statusHistory()
    {
        return $this->hasMany(RcsAgentStatusHistory::class)->orderBy('created_at', 'desc');
    }

    public function comments()
    {
        return $this->hasMany(RcsAgentComment::class)->orderBy('created_at', 'desc');
    }

    public function customerComments()
    {
        return $this->hasMany(RcsAgentComment::class)->customerVisible()->orderBy('created_at', 'desc');
    }

    public function assignments()
    {
        return $this->hasMany(RcsAgentAssignment::class)->orderBy('created_at', 'desc');
    }

    public function toAdminArray(): array
    {
        $createdByUser = $this->relationLoaded('createdBy') ? $this->createdBy : null;

        return [
            'id' => $this->uuid,
            'uuid' => $this->uuid,
            'account_id' => $this->account_id,
            'name' => $this->name,
            'description' => $this->description,
            'brand_color' => $this->brand_color ?? '#886CC0',
            'logo_url' => $this->logo_url,
            'logo_crop_metadata' => $this->logo_crop_metadata,
            'hero_url' => $this->hero_url,
            'hero_crop_metadata' => $this->hero_crop_metadata,
            'support_phone' => $this->support_phone,
            'support_email' => $this->support_email,
            'website' => $this->website,
            'privacy_url' => $this->privacy_url,
            'terms_url' => $this->terms_url,
            'show_phone' => (bool) $this->show_phone,
            'show_email' => (bool) $this->show_email,
            'show_website' => (bool) $this->show_website,
            'billing_category' => $this->billing_category,
            'use_case' => $this->use_case,
            'use_case_overview' => $this->use_case_overview,
            'campaign_frequency' => $this->campaign_frequency,
            'monthly_volume' => $this->monthly_volume,
            'opt_in_description' => $this->opt_in_description,
            'opt_out_description' => $this->opt_out_description,
            'test_numbers' => $this->test_numbers ?? [],
            'company_number' => $this->company_number,
            'company_website' => $this->company_website,
            'registered_address' => $this->registered_address,
            'approver_name' => $this->approver_name,
            'approver_job_title' => $this->approver_job_title,
            'approver_email' => $this->approver_email,
            'sector' => $this->sector,
            'workflow_status' => $this->workflow_status,
            'submitted_at' => $this->submitted_at,
            'reviewed_at' => $this->reviewed_at,
            'reviewed_by' => $this->reviewed_by,
            'rejection_reason' => $this->rejection_reason,
            'admin_notes' => $this->admin_notes,
            'suspension_reason' => $this->suspension_reason,
            'revocation_reason' => $this->revocation_reason,
            'additional_info' => $this->additional_info,
            'version' => $this->version,
            'full_payload' => $this->full_payload,
            'is_locked' => (bool) $this->is_locked,
            'created_by' => $this->created_by,
            'created_by_name' => $createdByUser ? trim(($createdByUser->first_name ?? '') . ' ' . ($createdByUser->last_name ?? '')) : null,
            'created_by_email' => $createdByUser->email ?? null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    public function canCustomerProvideInfo(): bool
    {
        return $this->workflow_status === self::STATUS_PENDING_INFO;
    }

    public function isEditable(): bool
    {
        return in_array($this->workflow_status, [self::STATUS_DRAFT, self::STATUS_REJECTED, self::STATUS_PENDING_INFO]);
    }

    public function isLocked(): bool
    {
        return in_array($this->workflow_status, [self::STATUS_SUBMITTED, self::STATUS_IN_REVIEW, self::STATUS_SENT_TO_SUPPLIER, self::STATUS_SUPPLIER_APPROVED, self::STATUS_APPROVED]);
    }

    public function transitionTo(string $newStatus, $userId, ?string $reason = null, ?string $notes = null, $actingUser = null): void
    {
        $oldStatus = $this->workflow_status;

        // Validate transition is allowed
        $allowedTransitions = self::TRANSITIONS[$oldStatus] ?? [];
        if (!in_array($newStatus, $allowedTransitions)) {
            throw new \InvalidArgumentException(
                "Invalid transition from '{$oldStatus}' to '{$newStatus}'. Allowed: " . implode(', ', $allowedTransitions)
            );
        }

        if ($newStatus === self::STATUS_SUBMITTED) {
            $this->full_payload = $this->toArray();
            $this->submitted_at = now();
            $this->is_locked = true;
        } elseif ($newStatus === self::STATUS_REJECTED) {
            $this->rejection_reason = $reason;
            $this->reviewed_at = now();
            $this->reviewed_by = $userId;
            $this->is_locked = false;
            $this->full_payload = array_merge($this->full_payload ?? [], [
                'rejection_reason' => $reason,
                'reviewed_at' => now()->toIso8601String(),
            ]);
        } elseif ($newStatus === self::STATUS_SENT_TO_SUPPLIER) {
            $this->reviewed_at = now();
            $this->reviewed_by = $userId;
            $this->is_locked = true;
        } elseif ($newStatus === self::STATUS_SUPPLIER_APPROVED) {
            $this->is_locked = true;
        } elseif ($newStatus === self::STATUS_APPROVED) {
            $this->reviewed_at = now();
            $this->reviewed_by = $userId;
            $this->is_locked = true;
        } elseif ($newStatus === self::STATUS_PENDING_INFO) {
            $this->is_locked = false;
        } elseif ($newStatus === self::STATUS_DRAFT) {
            $this->is_locked = false;
        } elseif ($newStatus === self::STATUS_SUSPENDED) {
            $this->suspension_reason = $reason;
            $this->is_locked = true;
        } elseif ($newStatus === self::STATUS_REVOKED) {
            $this->revocation_reason = $reason;
            $this->is_locked = true;
        }

        $this->workflow_status = $newStatus;
        $this->save();

        $this->recordStatusHistory($oldStatus, $newStatus, $this->getActionForTransition($oldStatus, $newStatus), $userId, $reason, $notes, $actingUser);
    }

    /**
     * Scope: approved RCS Agents usable by a specific user
     * Checks account-level (no assignments = available to all) OR assigned to user/sub-account
     */
    public function scopeUsableByUser($query, User $user)
    {
        return $query->where('workflow_status', self::STATUS_APPROVED)
            ->where('account_id', $user->tenant_id)
            ->where(function ($q) use ($user) {
                $q->whereDoesntHave('assignments')
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

    public function transitionStatus(string $newStatus, $userId, ?string $reason = null, ?string $notes = null, $actingUser = null): void
    {
        $this->transitionTo($newStatus, $userId, $reason, $notes, $actingUser);
    }

    public function recordStatusHistory(
        ?string $fromStatus,
        string $toStatus,
        string $action,
        $userId,
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
        }
        
        return $this->statusHistories()->create([
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'action' => $action,
            'reason' => $reason,
            'notes' => $notes,
            'payload_snapshot' => $this->full_payload,
            'user_id' => $userId,
            'user_name' => $actingUser ? $actingUser->name ?? null : null,
            'user_email' => $actingUser ? $actingUser->email ?? null : null,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);
    }

    protected function getActionForTransition(?string $from, string $to): string
    {
        $transitions = [
            'draft_submitted' => 'submitted',
            'submitted_in_review' => 'review_started',
            'in_review_approved' => 'approved',
            'in_review_rejected' => 'rejected',
            'in_review_pending_info' => 'returned_to_customer',
            'submitted_sent_to_supplier' => 'sent_to_supplier',
            'submitted_rejected' => 'rejected',
            'submitted_pending_info' => 'returned_to_customer',
            'pending_info_submitted' => 'resubmitted',
            'info_provided_sent_to_supplier' => 'sent_to_supplier',
            'info_provided_rejected' => 'rejected',
            'info_provided_pending_info' => 'returned_to_customer',
            'rejected_submitted' => 'resubmitted',
            'in_review_sent_to_supplier' => 'sent_to_supplier',
            'sent_to_supplier_supplier_approved' => 'supplier_approved',
            'supplier_approved_approved' => 'approved',
            'approved_suspended' => 'suspended',
            'supplier_approved_suspended' => 'suspended',
            'suspended_approved' => 'reactivated',
            'suspended_revoked' => 'revoked',
        ];

        $key = ($from ?? 'null') . '_' . $to;
        return $transitions[$key] ?? 'status_changed';
    }
}
