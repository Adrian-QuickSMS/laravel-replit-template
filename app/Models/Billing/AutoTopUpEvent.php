<?php

namespace App\Models\Billing;

use App\Models\Account;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class AutoTopUpEvent extends Model
{
    use HasUuids;

    protected $table = 'auto_topup_events';
    public $timestamps = false;

    // Event types
    const TYPE_TRIGGERED = 'triggered';
    const TYPE_PAYMENT_INITIATED = 'payment_initiated';
    const TYPE_PAYMENT_SUCCEEDED = 'payment_succeeded';
    const TYPE_PAYMENT_FAILED = 'payment_failed';
    const TYPE_REQUIRES_ACTION = 'requires_action';
    const TYPE_ACTION_COMPLETED = 'action_completed';
    const TYPE_ACTION_EXPIRED = 'action_expired';
    const TYPE_RETRY_SCHEDULED = 'retry_scheduled';
    const TYPE_RETRY_ATTEMPTED = 'retry_attempted';
    const TYPE_AUTO_DISABLED = 'auto_disabled';
    const TYPE_ADMIN_DISABLED = 'admin_disabled';
    const TYPE_ADMIN_UNLOCKED = 'admin_unlocked';
    const TYPE_CONFIG_UPDATED = 'config_updated';
    const TYPE_PAYMENT_METHOD_ADDED = 'payment_method_added';
    const TYPE_PAYMENT_METHOD_REMOVED = 'payment_method_removed';

    // Statuses
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_SUCCEEDED = 'succeeded';
    const STATUS_FAILED = 'failed';
    const STATUS_REQUIRES_ACTION = 'requires_action';
    const STATUS_EXPIRED = 'expired';
    const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'account_id',
        'config_id',
        'event_type',
        'status',
        'trigger_balance',
        'trigger_threshold',
        'topup_amount',
        'vat_amount',
        'total_charge_amount',
        'daily_count_before',
        'daily_value_before',
        'stripe_payment_intent_id',
        'stripe_customer_id',
        'stripe_payment_method_id',
        'failure_code',
        'failure_message',
        'requires_action_url',
        'idempotency_key',
        'retry_of_event_id',
        'retry_count',
        'metadata',
        'created_at',
        'processed_at',
        'completed_at',
    ];

    protected $casts = [
        'trigger_balance' => 'decimal:4',
        'trigger_threshold' => 'decimal:4',
        'topup_amount' => 'decimal:4',
        'vat_amount' => 'decimal:4',
        'total_charge_amount' => 'decimal:4',
        'daily_count_before' => 'integer',
        'daily_value_before' => 'decimal:4',
        'retry_count' => 'integer',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'processed_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->created_at)) {
                $model->created_at = now();
            }
        });
    }

    // Relationships

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function config()
    {
        return $this->belongsTo(AutoTopUpConfig::class, 'config_id');
    }

    public function retryOf()
    {
        return $this->belongsTo(self::class, 'retry_of_event_id');
    }

    public function retries()
    {
        return $this->hasMany(self::class, 'retry_of_event_id');
    }

    // Scopes

    public function scopeForAccount(Builder $query, string $accountId): Builder
    {
        return $query->where('account_id', $accountId);
    }

    public function scopeForToday(Builder $query): Builder
    {
        return $query->where('created_at', '>=', now()->utc()->startOfDay());
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_PROCESSING]);
    }

    public function scopeSucceeded(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_SUCCEEDED);
    }

    public function scopePaymentEvents(Builder $query): Builder
    {
        return $query->whereIn('event_type', [
            self::TYPE_PAYMENT_SUCCEEDED,
            self::TYPE_PAYMENT_INITIATED,
        ]);
    }

    /**
     * Return a safe representation for customer portal API responses.
     */
    public function toPortalArray(): array
    {
        return [
            'id' => $this->id,
            'event_type' => $this->event_type,
            'status' => $this->status,
            'trigger_balance' => $this->trigger_balance,
            'trigger_threshold' => $this->trigger_threshold,
            'topup_amount' => $this->topup_amount,
            'vat_amount' => $this->vat_amount,
            'total_charge_amount' => $this->total_charge_amount,
            'failure_message' => $this->failure_message,
            'requires_action_url' => $this->requires_action_url,
            'retry_count' => $this->retry_count,
            'created_at' => $this->created_at?->toIso8601String(),
            'processed_at' => $this->processed_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
        ];
    }

    /**
     * Return full representation for admin console (includes Stripe refs).
     */
    public function toAdminArray(): array
    {
        return array_merge($this->toPortalArray(), [
            'account_id' => $this->account_id,
            'config_id' => $this->config_id,
            'daily_count_before' => $this->daily_count_before,
            'daily_value_before' => $this->daily_value_before,
            'stripe_payment_intent_id' => $this->stripe_payment_intent_id,
            'stripe_customer_id' => $this->stripe_customer_id,
            'stripe_payment_method_id' => $this->stripe_payment_method_id,
            'failure_code' => $this->failure_code,
            'idempotency_key' => $this->idempotency_key,
            'retry_of_event_id' => $this->retry_of_event_id,
            'metadata' => $this->metadata,
        ]);
    }
}
