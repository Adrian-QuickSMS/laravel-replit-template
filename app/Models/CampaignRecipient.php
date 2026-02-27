<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Campaign Recipient — individual recipient record within a campaign.
 *
 * DATA CLASSIFICATION: Confidential - Customer PII
 * TENANT ISOLATION: Inherited from parent Campaign (campaign_id FK)
 *
 * Design notes:
 * - This table can grow to millions of rows per campaign.
 * - Indexed for batch processing: (campaign_id, status, batch_number).
 * - Unique constraint on (campaign_id, mobile_number) enforces dedup.
 * - Contact data is snapshotted at resolution time for merge field stability.
 * - Links to message_logs via message_log_id for canonical delivery records.
 */
class CampaignRecipient extends Model
{
    protected $table = 'campaign_recipients';

    protected $keyType = 'string';
    public $incrementing = false;

    // =====================================================
    // STATUS CONSTANTS
    // =====================================================

    const STATUS_PENDING = 'pending';
    const STATUS_QUEUED = 'queued';
    const STATUS_SENT = 'sent';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_FAILED = 'failed';
    const STATUS_UNDELIVERABLE = 'undeliverable';
    const STATUS_OPTED_OUT = 'opted_out';
    const STATUS_SKIPPED = 'skipped';

    const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_QUEUED,
        self::STATUS_SENT,
        self::STATUS_DELIVERED,
        self::STATUS_FAILED,
        self::STATUS_UNDELIVERABLE,
        self::STATUS_OPTED_OUT,
        self::STATUS_SKIPPED,
    ];

    // Terminal statuses — no further processing needed
    const TERMINAL_STATUSES = [
        self::STATUS_DELIVERED,
        self::STATUS_UNDELIVERABLE,
        self::STATUS_OPTED_OUT,
        self::STATUS_SKIPPED,
    ];

    // =====================================================
    // SOURCE CONSTANTS
    // =====================================================

    const SOURCE_LIST = 'list';
    const SOURCE_TAG = 'tag';
    const SOURCE_CSV = 'csv';
    const SOURCE_MANUAL = 'manual';
    const SOURCE_INDIVIDUAL = 'individual';

    const SOURCES = [
        self::SOURCE_LIST,
        self::SOURCE_TAG,
        self::SOURCE_CSV,
        self::SOURCE_MANUAL,
        self::SOURCE_INDIVIDUAL,
    ];

    // =====================================================
    // RETRY CONFIGURATION
    // =====================================================

    const MAX_RETRIES = 3;

    // =====================================================
    // MODEL CONFIGURATION
    // =====================================================

    protected $fillable = [
        'campaign_id',
        'contact_id',
        'mobile_number',
        'first_name',
        'last_name',
        'email',
        'custom_data',
        'source',
        'source_id',
        'status',
        'failure_reason',
        'failure_code',
        'resolved_content',
        'delivered_channel',
        'segments',
        'encoding',
        'cost',
        'currency',
        'country_iso',
        'message_log_id',
        'gateway_message_id',
        'gateway_id',
        'queued_at',
        'sent_at',
        'delivered_at',
        'failed_at',
        'batch_number',
        'retry_count',
        'next_retry_at',
        'metadata',
    ];

    protected $casts = [
        'id' => 'string',
        'campaign_id' => 'string',
        'contact_id' => 'string',
        'message_log_id' => 'string',
        'custom_data' => 'array',
        'metadata' => 'array',
        'segments' => 'integer',
        'cost' => 'decimal:6',
        'batch_number' => 'integer',
        'retry_count' => 'integer',
        'queued_at' => 'datetime',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'failed_at' => 'datetime',
        'next_retry_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $attributes = [
        'custom_data' => '{}',
        'metadata' => '{}',
    ];

    // No global tenant scope here — tenant isolation is inherited via the
    // Campaign relationship. Queries should always go through Campaign::recipients()
    // or include a campaign_id WHERE clause.

    // =====================================================
    // RELATIONSHIPS
    // =====================================================

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class, 'campaign_id');
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }

    // =====================================================
    // STATUS METHODS
    // =====================================================

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isQueued(): bool
    {
        return $this->status === self::STATUS_QUEUED;
    }

    public function isSent(): bool
    {
        return $this->status === self::STATUS_SENT;
    }

    public function isDelivered(): bool
    {
        return $this->status === self::STATUS_DELIVERED;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function isTerminal(): bool
    {
        return in_array($this->status, self::TERMINAL_STATUSES);
    }

    public function canRetry(): bool
    {
        return $this->status === self::STATUS_FAILED
            && $this->retry_count < self::MAX_RETRIES;
    }

    /**
     * Mark this recipient as queued for sending.
     */
    public function markQueued(): bool
    {
        $this->status = self::STATUS_QUEUED;
        $this->queued_at = now();
        return $this->save();
    }

    /**
     * Mark as sent (submitted to gateway, awaiting DLR).
     */
    public function markSent(string $gatewayMessageId, ?int $gatewayId = null): bool
    {
        $this->status = self::STATUS_SENT;
        $this->gateway_message_id = $gatewayMessageId;
        $this->gateway_id = $gatewayId;
        $this->sent_at = now();
        return $this->save();
    }

    /**
     * Mark as delivered (gateway confirmed delivery).
     */
    public function markDelivered(?string $deliveredChannel = null): bool
    {
        $this->status = self::STATUS_DELIVERED;
        $this->delivered_at = now();
        if ($deliveredChannel) {
            $this->delivered_channel = $deliveredChannel;
        }
        return $this->save();
    }

    /**
     * Mark as failed with reason.
     */
    public function markFailed(string $reason, ?string $code = null): bool
    {
        $this->status = self::STATUS_FAILED;
        $this->failure_reason = $reason;
        $this->failure_code = $code;
        $this->failed_at = now();
        return $this->save();
    }

    /**
     * Mark as opted out (skipped due to opt-out list).
     */
    public function markOptedOut(): bool
    {
        $this->status = self::STATUS_OPTED_OUT;
        return $this->save();
    }

    /**
     * Mark as skipped (invalid number, duplicate in another campaign, etc.).
     */
    public function markSkipped(string $reason): bool
    {
        $this->status = self::STATUS_SKIPPED;
        $this->failure_reason = $reason;
        return $this->save();
    }

    /**
     * Schedule a retry attempt.
     */
    public function scheduleRetry(): bool
    {
        if (!$this->canRetry()) {
            $this->status = self::STATUS_UNDELIVERABLE;
            return $this->save();
        }

        $this->retry_count += 1;
        $this->status = self::STATUS_PENDING;
        // Exponential backoff: 30s, 120s, 480s
        $delaySeconds = 30 * pow(4, $this->retry_count - 1);
        $this->next_retry_at = now()->addSeconds($delaySeconds);
        $this->failure_reason = null;
        $this->failure_code = null;
        return $this->save();
    }

    // =====================================================
    // MERGE FIELD RESOLUTION
    // =====================================================

    /**
     * Resolve merge fields in a message template against this recipient's data.
     *
     * Supports:
     * - Built-in fields: {{first_name}}, {{last_name}}, {{full_name}}, {{email}}, {{mobile_number}}
     * - camelCase aliases (backward compat): {{firstName}}, {{lastName}}, {{fullName}}, {{mobile}}
     * - Custom CSV fields (flat): {{Appointment Date}}, {{company}}, {{amount_due}}
     * - Prefixed custom fields (backward compat): {{custom_data.fieldname}}
     */
    public function resolveContent(string $template): string
    {
        $firstName = $this->first_name ?? '';
        $lastName = $this->last_name ?? '';
        $fullName = trim($firstName . ' ' . $lastName);

        $data = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'full_name' => $fullName,
            'email' => $this->email ?? '',
            'mobile_number' => $this->mobile_number ?? '',

            'firstName' => $firstName,
            'lastName' => $lastName,
            'fullName' => $fullName,
            'mobileNumber' => $this->mobile_number ?? '',
            'mobile' => $this->mobile_number ?? '',
        ];

        $customData = $this->custom_data ?? [];
        if (is_string($customData)) {
            $customData = json_decode($customData, true) ?? [];
        }
        if (!is_array($customData)) {
            $customData = [];
        }
        foreach ($customData as $key => $value) {
            $safeValue = $value ?? '';
            $data[$key] = $safeValue;
            $data["custom_data.{$key}"] = $safeValue;
        }

        return preg_replace_callback('/\{\{\s*([^}]+?)\s*\}\}/', function ($matches) use ($data) {
            $field = trim($matches[1]);
            return $data[$field] ?? '';
        }, $template);
    }

    // =====================================================
    // SCOPES
    // =====================================================

    public function scopeOfStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeQueued($query)
    {
        return $query->where('status', self::STATUS_QUEUED);
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', self::STATUS_DELIVERED);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    public function scopeRetryable($query)
    {
        return $query->where('status', self::STATUS_FAILED)
            ->where('retry_count', '<', self::MAX_RETRIES)
            ->whereNotNull('next_retry_at')
            ->where('next_retry_at', '<=', now());
    }

    public function scopeForBatch($query, int $batchNumber)
    {
        return $query->where('batch_number', $batchNumber);
    }

    // =====================================================
    // HELPERS
    // =====================================================

    public function getFullName(): string
    {
        return trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? '')) ?: 'Unknown';
    }

    public function toPortalArray(): array
    {
        return [
            'id' => $this->id,
            'mobile_number' => $this->getMaskedMobile(),
            'name' => $this->getFullName(),
            'source' => $this->source,
            'status' => $this->status,
            'delivered_channel' => $this->delivered_channel,
            'segments' => $this->segments,
            'cost' => $this->cost,
            'currency' => $this->currency,
            'country_iso' => $this->country_iso,
            'failure_reason' => $this->failure_reason,
            'sent_at' => $this->sent_at?->toIso8601String(),
            'delivered_at' => $this->delivered_at?->toIso8601String(),
            'retry_count' => $this->retry_count,
        ];
    }

    private function getMaskedMobile(): string
    {
        $number = $this->mobile_number;
        if (!$number || strlen($number) < 7) {
            return '***';
        }
        return substr($number, 0, 4) . ' **** ' . substr($number, -3);
    }
}
