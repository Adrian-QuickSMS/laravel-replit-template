<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * GREEN SIDE: Account Settings (Preferences and Configuration)
 *
 * DATA CLASSIFICATION: Internal - Account Preferences
 * SIDE: GREEN (customer portal accessible)
 * TENANT ISOLATION: One-to-one with accounts table
 *
 * SECURITY NOTES:
 * - One row per account (account_id is primary key)
 * - Portal users can view/update own account settings only
 * - Webhook URLs validated before storage
 * - Notification preferences stored as JSON
 */
class AccountSettings extends Model
{
    protected $table = 'account_settings';

    // account_id is primary key (one-to-one with accounts)
    protected $primaryKey = 'account_id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'account_id',
        'notification_email_enabled',
        'notification_email_addresses',
        'notification_sms_enabled',
        'notification_sms_numbers',
        'webhook_url_delivery',
        'webhook_url_inbound',
        'webhook_secret',
        'timezone',
        'date_format',
        'time_format',
        'currency',
        'language',
        'session_timeout_minutes',
        'require_mfa',
        'allow_api_access',
        'api_rate_limit_override',
        'approved_test_numbers',
        'test_numbers_ever_used',
        // Security settings
        'message_retention_days',
        'data_masking_config',
        'owner_bypass_masking',
        'anti_flood_enabled',
        'anti_flood_window_hours',
        'anti_flood_mode',
        'out_of_hours_enabled',
        'out_of_hours_start',
        'out_of_hours_end',
        'out_of_hours_action',
        'ip_allowlist_enabled',
    ];

    protected $casts = [
        'account_id' => 'string',
        'notification_email_enabled' => 'boolean',
        'notification_email_addresses' => 'array',
        'notification_sms_enabled' => 'boolean',
        'notification_sms_numbers' => 'array',
        'require_mfa' => 'boolean',
        'allow_api_access' => 'boolean',
        'session_timeout_minutes' => 'integer',
        'api_rate_limit_override' => 'integer',
        'approved_test_numbers' => 'array',
        'test_numbers_ever_used' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        // Security settings
        'message_retention_days' => 'integer',
        'data_masking_config' => 'array',
        'owner_bypass_masking' => 'boolean',
        'anti_flood_enabled' => 'boolean',
        'anti_flood_window_hours' => 'integer',
        'out_of_hours_enabled' => 'boolean',
        'ip_allowlist_enabled' => 'boolean',
    ];

    protected $hidden = [
        'webhook_secret',
    ];

    // =====================================================
    // RELATIONSHIPS
    // =====================================================

    /**
     * The account these settings belong to
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    // =====================================================
    // HELPER METHODS
    // =====================================================

    /**
     * Check if email notifications are enabled
     */
    public function hasEmailNotifications(): bool
    {
        return $this->notification_email_enabled && !empty($this->notification_email_addresses);
    }

    /**
     * Check if SMS notifications are enabled
     */
    public function hasSmsNotifications(): bool
    {
        return $this->notification_sms_enabled && !empty($this->notification_sms_numbers);
    }

    /**
     * Check if delivery webhook is configured
     */
    public function hasDeliveryWebhook(): bool
    {
        return !empty($this->webhook_url_delivery);
    }

    /**
     * Check if inbound webhook is configured
     */
    public function hasInboundWebhook(): bool
    {
        return !empty($this->webhook_url_inbound);
    }

    /**
     * Get session timeout in minutes (default 60)
     */
    public function getSessionTimeout(): int
    {
        return $this->session_timeout_minutes ?? 60;
    }

    /**
     * Validate webhook URL format
     */
    public function validateWebhookUrl(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false
            && (str_starts_with($url, 'https://') || str_starts_with($url, 'http://'));
    }

    /**
     * Add email to notification list
     */
    public function addNotificationEmail(string $email): bool
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $emails = $this->notification_email_addresses ?? [];

        if (!in_array($email, $emails)) {
            $emails[] = $email;
            return $this->update(['notification_email_addresses' => $emails]);
        }

        return true;
    }

    /**
     * Remove email from notification list
     */
    public function removeNotificationEmail(string $email): bool
    {
        $emails = $this->notification_email_addresses ?? [];
        $emails = array_values(array_diff($emails, [$email]));

        return $this->update(['notification_email_addresses' => $emails]);
    }

    /**
     * Add SMS number to notification list
     */
    public function addNotificationSms(string $number): bool
    {
        $numbers = $this->notification_sms_numbers ?? [];

        if (!in_array($number, $numbers)) {
            $numbers[] = $number;
            return $this->update(['notification_sms_numbers' => $numbers]);
        }

        return true;
    }

    /**
     * Remove SMS number from notification list
     */
    public function removeNotificationSms(string $number): bool
    {
        $numbers = $this->notification_sms_numbers ?? [];
        $numbers = array_values(array_diff($numbers, [$number]));

        return $this->update(['notification_sms_numbers' => $numbers]);
    }

    /**
     * Format for portal display (hide webhook secret)
     */
    public function toPortalArray(): array
    {
        return [
            'account_id' => $this->account_id,
            'notifications' => [
                'email_enabled' => $this->notification_email_enabled,
                'email_addresses' => $this->notification_email_addresses ?? [],
                'sms_enabled' => $this->notification_sms_enabled,
                'sms_numbers' => $this->notification_sms_numbers ?? [],
            ],
            'webhooks' => [
                'delivery_url' => $this->webhook_url_delivery,
                'inbound_url' => $this->webhook_url_inbound,
                'has_secret' => !empty($this->webhook_secret),
            ],
            'locale' => [
                'timezone' => $this->timezone,
                'date_format' => $this->date_format,
                'time_format' => $this->time_format,
                'currency' => $this->currency,
                'language' => $this->language,
            ],
            'security' => [
                'session_timeout_minutes' => $this->session_timeout_minutes,
                'require_mfa' => $this->require_mfa,
                'allow_api_access' => $this->allow_api_access,
                'message_retention_days' => $this->message_retention_days ?? 180,
                'data_masking_config' => $this->data_masking_config ?? [],
                'owner_bypass_masking' => $this->owner_bypass_masking ?? true,
                'anti_flood_enabled' => $this->anti_flood_enabled ?? false,
                'anti_flood_mode' => $this->anti_flood_mode ?? 'off',
                'anti_flood_window_hours' => $this->anti_flood_window_hours ?? 2,
                'out_of_hours_enabled' => $this->out_of_hours_enabled ?? false,
                'out_of_hours_start' => $this->out_of_hours_start ?? '21:00',
                'out_of_hours_end' => $this->out_of_hours_end ?? '08:00',
                'out_of_hours_action' => $this->out_of_hours_action ?? 'reject',
                'ip_allowlist_enabled' => $this->ip_allowlist_enabled ?? false,
            ],
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
