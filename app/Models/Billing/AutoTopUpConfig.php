<?php

namespace App\Models\Billing;

use App\Models\Account;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class AutoTopUpConfig extends Model
{
    use HasUuids;

    protected $table = 'auto_topup_configs';

    protected $fillable = [
        'account_id',
        'enabled',
        'threshold_amount',
        'topup_amount',
        'stripe_customer_id',
        'stripe_payment_method_id',
        'card_brand',
        'card_last4',
        'card_exp_month',
        'card_exp_year',
        'max_topups_per_day',
        'daily_topup_cap',
        'min_minutes_between_topups',
        'notify_email_success',
        'notify_email_failure',
        'notify_inapp_success',
        'notify_inapp_failure',
        'notify_requires_action',
        'retry_attempts',
        'retry_delay_minutes',
        'disable_after_consecutive_failures',
        'consecutive_failure_count',
        'last_triggered_at',
        'last_successful_topup_at',
        'admin_locked',
        'admin_locked_reason',
        'admin_locked_at',
        'admin_locked_by',
        'updated_by_user_id',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'threshold_amount' => 'decimal:4',
        'topup_amount' => 'decimal:4',
        'daily_topup_cap' => 'decimal:4',
        'max_topups_per_day' => 'integer',
        'min_minutes_between_topups' => 'integer',
        'card_exp_month' => 'integer',
        'card_exp_year' => 'integer',
        'notify_email_success' => 'boolean',
        'notify_email_failure' => 'boolean',
        'notify_inapp_success' => 'boolean',
        'notify_inapp_failure' => 'boolean',
        'notify_requires_action' => 'boolean',
        'retry_attempts' => 'integer',
        'retry_delay_minutes' => 'integer',
        'disable_after_consecutive_failures' => 'integer',
        'consecutive_failure_count' => 'integer',
        'last_triggered_at' => 'datetime',
        'last_successful_topup_at' => 'datetime',
        'admin_locked' => 'boolean',
        'admin_locked_at' => 'datetime',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function events()
    {
        return $this->hasMany(AutoTopUpEvent::class, 'config_id');
    }

    // Scopes

    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('enabled', true);
    }

    public function scopeAdminLocked(Builder $query): Builder
    {
        return $query->where('admin_locked', true);
    }

    // Helpers

    public function isLocked(): bool
    {
        return $this->admin_locked;
    }

    public function hasValidPaymentMethod(): bool
    {
        return !empty($this->stripe_customer_id) && !empty($this->stripe_payment_method_id);
    }

    public function canTrigger(): bool
    {
        return $this->enabled
            && !$this->admin_locked
            && $this->hasValidPaymentMethod();
    }

    public function incrementFailureCount(): void
    {
        $this->increment('consecutive_failure_count');
    }

    public function resetFailureCount(): void
    {
        $this->update([
            'consecutive_failure_count' => 0,
            'last_successful_topup_at' => now(),
        ]);
    }

    public function shouldAutoDisable(): bool
    {
        return $this->consecutive_failure_count >= $this->disable_after_consecutive_failures;
    }

    /**
     * Return a safe representation for customer portal API responses.
     */
    public function toPortalArray(): array
    {
        return [
            'enabled' => $this->enabled,
            'threshold_amount' => $this->threshold_amount,
            'topup_amount' => $this->topup_amount,
            'max_topups_per_day' => $this->max_topups_per_day,
            'daily_topup_cap' => $this->daily_topup_cap,
            'min_minutes_between_topups' => $this->min_minutes_between_topups,
            'card_brand' => $this->card_brand,
            'card_last4' => $this->card_last4,
            'card_exp_month' => $this->card_exp_month,
            'card_exp_year' => $this->card_exp_year,
            'has_payment_method' => $this->hasValidPaymentMethod(),
            'notify_email_success' => $this->notify_email_success,
            'notify_email_failure' => $this->notify_email_failure,
            'notify_inapp_success' => $this->notify_inapp_success,
            'notify_inapp_failure' => $this->notify_inapp_failure,
            'notify_requires_action' => $this->notify_requires_action,
            'admin_locked' => $this->admin_locked,
            'admin_locked_reason' => $this->admin_locked ? $this->admin_locked_reason : null,
            'consecutive_failure_count' => $this->consecutive_failure_count,
            'last_successful_topup_at' => $this->last_successful_topup_at?->toIso8601String(),
            'last_triggered_at' => $this->last_triggered_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
