<?php

namespace App\Models;

use App\Models\Billing\CampaignReservation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * GREEN SIDE: Campaign (Send Message campaigns)
 *
 * DATA CLASSIFICATION: Internal - Messaging Operations
 * SIDE: GREEN (customer portal accessible)
 * TENANT ISOLATION: MANDATORY account_id on every row + RLS
 *
 * State machine governs campaign lifecycle from draft through completion.
 * Integrates with BalanceService for fund reservation, MessageLog for delivery tracking.
 */
class Campaign extends Model
{
    use SoftDeletes;

    protected $table = 'campaigns';

    protected $keyType = 'string';
    public $incrementing = false;

    // =====================================================
    // STATUS CONSTANTS
    // =====================================================

    const STATUS_DRAFT = 'draft';
    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_QUEUED = 'queued';
    const STATUS_SENDING = 'sending';
    const STATUS_PAUSED = 'paused';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_FAILED = 'failed';

    const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_SCHEDULED,
        self::STATUS_QUEUED,
        self::STATUS_SENDING,
        self::STATUS_PAUSED,
        self::STATUS_COMPLETED,
        self::STATUS_CANCELLED,
        self::STATUS_FAILED,
    ];

    // =====================================================
    // STATE MACHINE: Allowed transitions
    // =====================================================

    const TRANSITIONS = [
        self::STATUS_DRAFT     => [self::STATUS_SCHEDULED, self::STATUS_QUEUED],
        self::STATUS_SCHEDULED => [self::STATUS_QUEUED, self::STATUS_CANCELLED],
        self::STATUS_QUEUED    => [self::STATUS_SENDING, self::STATUS_FAILED, self::STATUS_CANCELLED],
        self::STATUS_SENDING   => [self::STATUS_PAUSED, self::STATUS_COMPLETED, self::STATUS_FAILED],
        self::STATUS_PAUSED    => [self::STATUS_SENDING, self::STATUS_CANCELLED],
        self::STATUS_COMPLETED => [], // terminal
        self::STATUS_CANCELLED => [], // terminal
        self::STATUS_FAILED    => [self::STATUS_DRAFT], // can retry via re-draft
    ];

    // =====================================================
    // TYPE CONSTANTS
    // =====================================================

    const TYPE_SMS = 'sms';
    const TYPE_RCS_BASIC = 'rcs_basic';
    const TYPE_RCS_SINGLE = 'rcs_single';

    const TYPES = [
        self::TYPE_SMS,
        self::TYPE_RCS_BASIC,
        self::TYPE_RCS_SINGLE,
    ];

    // =====================================================
    // DEFAULT SEND CONFIG
    // =====================================================

    const DEFAULT_BATCH_SIZE = 1000;
    const DEFAULT_SEND_RATE = 100; // messages per second

    // =====================================================
    // MODEL CONFIGURATION
    // =====================================================

    protected $fillable = [
        'account_id',
        'sub_account_id',
        'name',
        'description',
        'type',
        'status',
        'message_template_id',
        'message_content',
        'rcs_content',
        'encoding',
        'segment_count',
        'sender_id_id',
        'rcs_agent_id',
        'recipient_sources',
        'total_recipients',
        'total_unique_recipients',
        'total_opted_out',
        'total_invalid',
        'scheduled_at',
        'timezone',
        'send_rate',
        'batch_size',
        'estimated_cost',
        'actual_cost',
        'currency',
        'reservation_id',
        'content_resolved_at',
        'preparation_status',
        'preparation_progress',
        'preparation_error',
        'tags',
        'metadata',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'id' => 'string',
        'account_id' => 'string',
        'sub_account_id' => 'string',
        'message_template_id' => 'string',
        'reservation_id' => 'string',
        'rcs_content' => 'array',
        'recipient_sources' => 'array',
        'tags' => 'array',
        'metadata' => 'array',
        'total_recipients' => 'integer',
        'total_unique_recipients' => 'integer',
        'total_opted_out' => 'integer',
        'total_invalid' => 'integer',
        'segment_count' => 'integer',
        'send_rate' => 'integer',
        'batch_size' => 'integer',
        'sent_count' => 'integer',
        'delivered_count' => 'integer',
        'failed_count' => 'integer',
        'pending_count' => 'integer',
        'fallback_sms_count' => 'integer',
        'estimated_cost' => 'decimal:4',
        'actual_cost' => 'decimal:4',
        'content_resolved_at' => 'datetime',
        'preparation_progress' => 'integer',
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'paused_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'failed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $attributes = [
        'recipient_sources' => '[]',
        'tags' => '[]',
        'metadata' => '{}',
    ];

    // =====================================================
    // BOOT / TENANT SCOPE
    // =====================================================

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) \Illuminate\Support\Str::uuid();
            }
        });

        static::addGlobalScope('tenant', function (Builder $builder) {
            $tenantId = auth()->check() && auth()->user()->tenant_id
                ? auth()->user()->tenant_id
                : session('customer_tenant_id');
            if ($tenantId) {
                $builder->where('campaigns.account_id', $tenantId);
            } else {
                $builder->whereRaw('1 = 0');
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

    public function subAccount(): BelongsTo
    {
        return $this->belongsTo(SubAccount::class, 'sub_account_id');
    }

    public function messageTemplate(): BelongsTo
    {
        return $this->belongsTo(MessageTemplate::class, 'message_template_id');
    }

    public function senderId(): BelongsTo
    {
        return $this->belongsTo(SenderId::class, 'sender_id_id');
    }

    public function rcsAgent(): BelongsTo
    {
        return $this->belongsTo(RcsAgent::class, 'rcs_agent_id');
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(CampaignReservation::class, 'reservation_id');
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(CampaignRecipient::class, 'campaign_id');
    }

    // =====================================================
    // STATE MACHINE
    // =====================================================

    /**
     * Transition to a new status with validation.
     *
     * @throws \InvalidArgumentException if transition is not allowed
     */
    public function transitionTo(string $newStatus): bool
    {
        $oldStatus = $this->status;

        $allowedTransitions = self::TRANSITIONS[$oldStatus] ?? [];
        if (!in_array($newStatus, $allowedTransitions)) {
            throw new \InvalidArgumentException(
                "Invalid campaign transition from '{$oldStatus}' to '{$newStatus}'. "
                . "Allowed: " . implode(', ', $allowedTransitions)
            );
        }

        // Apply status-specific timestamps
        switch ($newStatus) {
            case self::STATUS_QUEUED:
                // No specific timestamp — queued_at is tracked per-recipient
                break;
            case self::STATUS_SENDING:
                if (!$this->started_at) {
                    $this->started_at = now();
                }
                $this->paused_at = null;
                break;
            case self::STATUS_PAUSED:
                $this->paused_at = now();
                break;
            case self::STATUS_COMPLETED:
                $this->completed_at = now();
                break;
            case self::STATUS_CANCELLED:
                $this->cancelled_at = now();
                break;
            case self::STATUS_FAILED:
                $this->failed_at = now();
                break;
            case self::STATUS_DRAFT:
                // Reset for retry
                $this->started_at = null;
                $this->completed_at = null;
                $this->failed_at = null;
                $this->paused_at = null;
                $this->cancelled_at = null;
                break;
        }

        $this->status = $newStatus;
        return $this->save();
    }

    /**
     * Check if a transition to the given status is allowed.
     */
    public function canTransitionTo(string $newStatus): bool
    {
        $allowed = self::TRANSITIONS[$this->status] ?? [];
        return in_array($newStatus, $allowed);
    }

    // =====================================================
    // STATUS CHECKS
    // =====================================================

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isScheduled(): bool
    {
        return $this->status === self::STATUS_SCHEDULED;
    }

    public function isSending(): bool
    {
        return $this->status === self::STATUS_SENDING;
    }

    public function isPaused(): bool
    {
        return $this->status === self::STATUS_PAUSED;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function isTerminal(): bool
    {
        return in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_CANCELLED]);
    }

    public function isActive(): bool
    {
        return in_array($this->status, [self::STATUS_QUEUED, self::STATUS_SENDING]);
    }

    public function isEditable(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function canPause(): bool
    {
        return $this->canTransitionTo(self::STATUS_PAUSED);
    }

    public function canResume(): bool
    {
        return $this->status === self::STATUS_PAUSED;
    }

    public function canCancel(): bool
    {
        return $this->canTransitionTo(self::STATUS_CANCELLED);
    }

    public function isSms(): bool
    {
        return $this->type === self::TYPE_SMS;
    }

    public function isRcs(): bool
    {
        return in_array($this->type, [self::TYPE_RCS_BASIC, self::TYPE_RCS_SINGLE]);
    }

    // =====================================================
    // PROGRESS HELPERS
    // =====================================================

    /**
     * Get delivery progress as a percentage (0-100).
     */
    public function getProgressPercentage(): float
    {
        if (empty($this->total_unique_recipients)) {
            return 0;
        }

        $processed = ($this->sent_count ?? 0) + ($this->delivered_count ?? 0) + ($this->failed_count ?? 0);
        return round(($processed / $this->total_unique_recipients) * 100, 1);
    }

    /**
     * Get delivery rate (delivered / total sent, as percentage).
     */
    public function getDeliveryRate(): float
    {
        $sent = $this->sent_count ?? 0;
        $delivered = $this->delivered_count ?? 0;
        $failed = $this->failed_count ?? 0;

        if ($sent === 0 && $delivered === 0) {
            return 0;
        }

        $totalAttempted = $sent + $delivered + $failed;
        if ($totalAttempted === 0) {
            return 0;
        }

        return round(($delivered / $totalAttempted) * 100, 1);
    }

    /**
     * Get the effective sender display name.
     */
    public function getSenderDisplayName(): ?string
    {
        if ($this->isSms()) {
            return $this->senderId?->sender_id_value;
        }
        return $this->rcsAgent?->name;
    }

    // =====================================================
    // SCOPES
    // =====================================================

    public function scopeOfStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeDrafts($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', self::STATUS_SCHEDULED);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [self::STATUS_QUEUED, self::STATUS_SENDING]);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Campaigns due for scheduled send.
     */
    public function scopeDueForSend($query)
    {
        return $query->where('status', self::STATUS_SCHEDULED)
            ->where('scheduled_at', '<=', now());
    }

    public function scopeSearch($query, ?string $search)
    {
        if (!$search) {
            return $query;
        }
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'ilike', "%{$search}%")
              ->orWhere('description', 'ilike', "%{$search}%");
        });
    }

    // =====================================================
    // PORTAL METHODS
    // =====================================================

    public function toPortalArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'type' => $this->type,
            'status' => $this->status,
            'message_content' => $this->message_content,
            'rcs_content' => $this->rcs_content,
            'encoding' => $this->encoding,
            'segment_count' => $this->segment_count,
            'sender' => $this->getSenderDisplayName(),
            'sender_id_id' => $this->sender_id_id,
            'rcs_agent_id' => $this->rcs_agent_id,
            'recipient_sources' => $this->recipient_sources,
            'total_recipients' => $this->total_recipients,
            'total_unique_recipients' => $this->total_unique_recipients,
            'total_opted_out' => $this->total_opted_out,
            'total_invalid' => $this->total_invalid,
            'scheduled_at' => $this->scheduled_at?->toIso8601String(),
            'timezone' => $this->timezone,
            'send_rate' => $this->send_rate,
            'started_at' => $this->started_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'paused_at' => $this->paused_at?->toIso8601String(),
            'sent_count' => $this->sent_count,
            'delivered_count' => $this->delivered_count,
            'failed_count' => $this->failed_count,
            'pending_count' => $this->pending_count,
            'fallback_sms_count' => $this->fallback_sms_count,
            'estimated_cost' => $this->estimated_cost,
            'actual_cost' => $this->actual_cost,
            'currency' => $this->currency,
            'content_resolved_at' => $this->content_resolved_at?->toIso8601String(),
            'preparation_status' => $this->preparation_status,
            'preparation_progress' => $this->preparation_progress,
            'progress_percentage' => $this->getProgressPercentage(),
            'delivery_rate' => $this->getDeliveryRate(),
            'tags' => $this->tags,
            'is_editable' => $this->isEditable(),
            'can_pause' => $this->canPause(),
            'can_resume' => $this->canResume(),
            'can_cancel' => $this->canCancel(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }

    /**
     * Summary array for campaign list views.
     */
    public function toListArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'status' => $this->status,
            'sender' => $this->getSenderDisplayName(),
            'total_unique_recipients' => $this->total_unique_recipients,
            'sent_count' => $this->sent_count,
            'delivered_count' => $this->delivered_count,
            'failed_count' => $this->failed_count,
            'progress_percentage' => $this->getProgressPercentage(),
            'delivery_rate' => $this->getDeliveryRate(),
            'estimated_cost' => $this->estimated_cost,
            'actual_cost' => $this->actual_cost,
            'currency' => $this->currency,
            'scheduled_at' => $this->scheduled_at?->toIso8601String(),
            'started_at' => $this->started_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
