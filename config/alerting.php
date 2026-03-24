<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Alert Categories
    |--------------------------------------------------------------------------
    |
    | Defines the available alert categories and their display names.
    | Used for grouping alerts in the UI and for validation.
    |
    */

    'categories' => [
        'billing' => 'Account & Billing',
        'messaging' => 'Messaging Performance',
        'compliance' => 'Compliance & Registration',
        'security' => 'Security & Access',
        'system' => 'System & Integration',
        'campaign' => 'Campaign & Flows',
        'sub_account' => 'Sub-Account Caps & Limits',
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin Alert Categories
    |--------------------------------------------------------------------------
    */

    'admin_categories' => [
        'fraud' => 'Spam & Fraud',
        'platform_health' => 'Platform Health',
        'customer_risk' => 'Customer Risk',
        'commercial' => 'Commercial & Billing Risk',
        'compliance_legal' => 'Compliance & Legal',
        'supplier_monitoring' => 'Supplier Monitoring',
    ],

    /*
    |--------------------------------------------------------------------------
    | Available Channels
    |--------------------------------------------------------------------------
    */

    'channels' => ['email', 'in_app', 'webhook', 'sms', 'slack', 'teams'],

    /*
    |--------------------------------------------------------------------------
    | Frequency Options
    |--------------------------------------------------------------------------
    */

    'frequencies' => [
        'instant' => 'Instant',
        'batched_15m' => 'Every 15 minutes',
        'batched_1h' => 'Every hour',
        'daily_digest' => 'Daily summary',
        'once_per_breach' => 'Once per breach',
    ],

    /*
    |--------------------------------------------------------------------------
    | Trigger Types
    |--------------------------------------------------------------------------
    */

    'trigger_types' => ['threshold', 'percentage_change', 'absolute_change', 'event'],

    /*
    |--------------------------------------------------------------------------
    | Condition Operators
    |--------------------------------------------------------------------------
    */

    'condition_operators' => ['lt', 'gt', 'lte', 'gte', 'eq', 'drops_by', 'increases_by'],

    /*
    |--------------------------------------------------------------------------
    | Queue Configuration
    |--------------------------------------------------------------------------
    */

    'queue' => [
        'evaluation' => env('ALERT_QUEUE_EVALUATION', 'alerts'),
        'dispatch' => env('ALERT_QUEUE_DISPATCH', 'alerts'),
        'batch' => env('ALERT_QUEUE_BATCH', 'alerts-batch'),
        'digest' => env('ALERT_QUEUE_DIGEST', 'alerts-digest'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Cooldown (minutes)
    |--------------------------------------------------------------------------
    */

    'default_cooldown_minutes' => 60,

    /*
    |--------------------------------------------------------------------------
    | Webhook Configuration
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | Admin Channel Webhook URLs
    |--------------------------------------------------------------------------
    |
    | Used by AlertDispatcherService for admin-only alerts (no tenant context).
    |
    */

    'admin_slack_webhook_url' => env('ALERT_ADMIN_SLACK_WEBHOOK_URL'),
    'admin_teams_webhook_url' => env('ALERT_ADMIN_TEAMS_WEBHOOK_URL'),

    'webhook' => [
        'timeout_seconds' => 10,
        'max_retries' => 3,
        'retry_backoff' => [5, 30, 120], // seconds between retries
        'signature_header' => 'X-QuickSMS-Signature',
        'signature_algorithm' => 'sha256',
    ],

    /*
    |--------------------------------------------------------------------------
    | Batch/Digest Schedule
    |--------------------------------------------------------------------------
    */

    'batch' => [
        'batched_15m' => 15,  // minutes
        'batched_1h' => 60,
        'daily_digest_hour' => 8, // 8 AM UTC
    ],

    /*
    |--------------------------------------------------------------------------
    | System Default Alert Rules
    |--------------------------------------------------------------------------
    |
    | These are seeded as is_system_default = true.
    | Customers can override or disable them but not delete them.
    |
    */

    'defaults' => [

        // --- Billing ---
        [
            'category' => 'billing',
            'trigger_type' => 'threshold',
            'trigger_key' => 'credit_balance_percentage',
            'condition_operator' => 'gte',
            'condition_value' => 80, // 80% of credit used
            'channels' => ['in_app', 'email'],
            'frequency' => 'once_per_breach',
            'cooldown_minutes' => 360,
            'severity' => 'warning',
            'title' => 'Credit balance running low',
            'escalation_rules' => [
                ['condition_value' => 95, 'channels' => ['sms']],
            ],
        ],
        [
            'category' => 'billing',
            'trigger_type' => 'event',
            'trigger_key' => 'payment_failed',
            'condition_operator' => 'eq',
            'condition_value' => null,
            'channels' => ['in_app', 'email'],
            'frequency' => 'instant',
            'cooldown_minutes' => 60,
            'severity' => 'critical',
            'title' => 'Payment failed',
        ],
        [
            'category' => 'billing',
            'trigger_type' => 'event',
            'trigger_key' => 'invoice_generated',
            'condition_operator' => 'eq',
            'condition_value' => null,
            'channels' => ['in_app', 'email'],
            'frequency' => 'instant',
            'cooldown_minutes' => 0,
            'severity' => 'info',
            'title' => 'New invoice generated',
        ],
        [
            'category' => 'billing',
            'trigger_type' => 'percentage_change',
            'trigger_key' => 'spend_rate',
            'condition_operator' => 'increases_by',
            'condition_value' => 50, // 50% increase in spend rate
            'channels' => ['in_app', 'email'],
            'frequency' => 'once_per_breach',
            'cooldown_minutes' => 1440, // 24 hours
            'severity' => 'warning',
            'title' => 'Unusual spend spike detected',
        ],

        // --- Messaging ---
        [
            'category' => 'messaging',
            'trigger_type' => 'threshold',
            'trigger_key' => 'delivery_rate',
            'condition_operator' => 'lt',
            'condition_value' => 90, // delivery rate drops below 90%
            'channels' => ['in_app', 'email'],
            'frequency' => 'once_per_breach',
            'cooldown_minutes' => 360,
            'severity' => 'warning',
            'title' => 'Delivery rate below threshold',
        ],
        [
            'category' => 'messaging',
            'trigger_type' => 'threshold',
            'trigger_key' => 'failed_messages',
            'condition_operator' => 'gt',
            'condition_value' => 1000,
            'channels' => ['in_app', 'email'],
            'frequency' => 'once_per_breach',
            'cooldown_minutes' => 360,
            'severity' => 'warning',
            'title' => 'Failed messages above threshold',
        ],
        [
            'category' => 'messaging',
            'trigger_type' => 'percentage_change',
            'trigger_key' => 'delivery_rate_delta',
            'condition_operator' => 'drops_by',
            'condition_value' => 7,
            'channels' => ['in_app', 'email'],
            'frequency' => 'once_per_breach',
            'cooldown_minutes' => 360,
            'severity' => 'warning',
            'title' => 'Delivery rate baseline deviation',
        ],
        [
            'category' => 'messaging',
            'trigger_type' => 'percentage_change',
            'trigger_key' => 'network_delivery_delta',
            'condition_operator' => 'drops_by',
            'condition_value' => 8,
            'channels' => ['in_app', 'email'],
            'frequency' => 'once_per_breach',
            'cooldown_minutes' => 360,
            'severity' => 'warning',
            'title' => 'Network-level delivery deviation',
        ],
        [
            'category' => 'messaging',
            'trigger_type' => 'percentage_change',
            'trigger_key' => 'country_delivery_delta',
            'condition_operator' => 'drops_by',
            'condition_value' => 10,
            'channels' => ['in_app', 'email'],
            'frequency' => 'once_per_breach',
            'cooldown_minutes' => 360,
            'severity' => 'warning',
            'title' => 'Country-level delivery deviation',
        ],
        [
            'category' => 'messaging',
            'trigger_type' => 'threshold',
            'trigger_key' => 'pending_rate',
            'condition_operator' => 'gt',
            'condition_value' => 5,
            'channels' => ['in_app', 'email'],
            'frequency' => 'once_per_breach',
            'cooldown_minutes' => 60,
            'severity' => 'warning',
            'title' => 'Pending message rate above threshold',
        ],
        [
            'category' => 'messaging',
            'trigger_type' => 'threshold',
            'trigger_key' => 'pending_rate_critical',
            'condition_operator' => 'gt',
            'condition_value' => 10,
            'channels' => ['in_app', 'email', 'sms'],
            'frequency' => 'once_per_breach',
            'cooldown_minutes' => 30,
            'severity' => 'critical',
            'title' => 'Pending message rate critical',
        ],
        [
            'category' => 'messaging',
            'trigger_type' => 'threshold',
            'trigger_key' => 'missing_dlr_rate',
            'condition_operator' => 'gt',
            'condition_value' => 3,
            'channels' => ['in_app', 'email'],
            'frequency' => 'once_per_breach',
            'cooldown_minutes' => 60,
            'severity' => 'warning',
            'title' => 'Missing DLR percentage above threshold',
        ],
        [
            'category' => 'messaging',
            'trigger_type' => 'threshold',
            'trigger_key' => 'submission_rejection_rate',
            'condition_operator' => 'gt',
            'condition_value' => 1,
            'channels' => ['in_app', 'email'],
            'frequency' => 'once_per_breach',
            'cooldown_minutes' => 60,
            'severity' => 'warning',
            'title' => 'Submission rejection rate above threshold',
        ],
        [
            'category' => 'messaging',
            'trigger_type' => 'threshold',
            'trigger_key' => 'senderid_rejection_rate',
            'condition_operator' => 'gt',
            'condition_value' => 2,
            'channels' => ['in_app', 'email'],
            'frequency' => 'once_per_breach',
            'cooldown_minutes' => 60,
            'severity' => 'warning',
            'title' => 'Sender ID rejection rate above threshold',
        ],
        [
            'category' => 'messaging',
            'trigger_type' => 'threshold',
            'trigger_key' => 'rcs_fallback_rate',
            'condition_operator' => 'gt',
            'condition_value' => 20,
            'channels' => ['in_app', 'email'],
            'frequency' => 'once_per_breach',
            'cooldown_minutes' => 360,
            'severity' => 'warning',
            'title' => 'RCS fallback rate above threshold',
        ],

        // --- Compliance ---
        [
            'category' => 'compliance',
            'trigger_type' => 'event',
            'trigger_key' => 'sender_id_status_changed',
            'condition_operator' => 'eq',
            'condition_value' => null,
            'channels' => ['in_app', 'email'],
            'frequency' => 'instant',
            'cooldown_minutes' => 0,
            'severity' => 'info',
            'title' => 'Sender ID registration status update',
        ],
        [
            'category' => 'compliance',
            'trigger_type' => 'event',
            'trigger_key' => 'rcs_agent_status_changed',
            'condition_operator' => 'eq',
            'condition_value' => null,
            'channels' => ['in_app', 'email'],
            'frequency' => 'instant',
            'cooldown_minutes' => 0,
            'severity' => 'info',
            'title' => 'RCS agent status update',
        ],

        // --- Security ---
        [
            'category' => 'security',
            'trigger_type' => 'event',
            'trigger_key' => 'suspicious_login',
            'condition_operator' => 'eq',
            'condition_value' => null,
            'channels' => ['in_app', 'email', 'sms'],
            'frequency' => 'instant',
            'cooldown_minutes' => 0,
            'severity' => 'critical',
            'title' => 'Suspicious login detected',
        ],
        [
            'category' => 'security',
            'trigger_type' => 'event',
            'trigger_key' => 'api_key_lifecycle',
            'condition_operator' => 'eq',
            'condition_value' => null,
            'channels' => ['in_app', 'email'],
            'frequency' => 'instant',
            'cooldown_minutes' => 0,
            'severity' => 'warning',
            'title' => 'API key change detected',
        ],

        // --- System ---
        [
            'category' => 'system',
            'trigger_type' => 'threshold',
            'trigger_key' => 'api_error_rate',
            'condition_operator' => 'gt',
            'condition_value' => 5, // more than 5% error rate
            'channels' => ['in_app', 'email', 'webhook'],
            'frequency' => 'once_per_breach',
            'cooldown_minutes' => 60,
            'severity' => 'warning',
            'title' => 'API error rate above threshold',
        ],
        [
            'category' => 'system',
            'trigger_type' => 'event',
            'trigger_key' => 'webhook_delivery_failed',
            'condition_operator' => 'eq',
            'condition_value' => null,
            'channels' => ['in_app', 'email'],
            'frequency' => 'batched_1h',
            'cooldown_minutes' => 60,
            'severity' => 'warning',
            'title' => 'Webhook delivery failures detected',
        ],
        [
            'category' => 'system',
            'trigger_type' => 'threshold',
            'trigger_key' => 'platform_processing_time',
            'condition_operator' => 'gt',
            'condition_value' => 3,
            'channels' => ['in_app', 'email'],
            'frequency' => 'once_per_breach',
            'cooldown_minutes' => 60,
            'severity' => 'warning',
            'title' => 'Platform processing time above threshold',
        ],
        [
            'category' => 'system',
            'trigger_type' => 'threshold',
            'trigger_key' => 'platform_processing_time_critical',
            'condition_operator' => 'gt',
            'condition_value' => 8,
            'channels' => ['in_app', 'email', 'sms'],
            'frequency' => 'once_per_breach',
            'cooldown_minutes' => 30,
            'severity' => 'critical',
            'title' => 'Platform processing time critical',
        ],
        [
            'category' => 'system',
            'trigger_type' => 'threshold',
            'trigger_key' => 'queued_messages_outbound',
            'condition_operator' => 'gt',
            'condition_value' => 2000,
            'channels' => ['in_app', 'email'],
            'frequency' => 'once_per_breach',
            'cooldown_minutes' => 30,
            'severity' => 'warning',
            'title' => 'Outbound queue depth above threshold',
        ],
        [
            'category' => 'system',
            'trigger_type' => 'threshold',
            'trigger_key' => 'queue_growth_rate',
            'condition_operator' => 'gt',
            'condition_value' => 15,
            'channels' => ['in_app', 'email', 'sms'],
            'frequency' => 'once_per_breach',
            'cooldown_minutes' => 15,
            'severity' => 'critical',
            'title' => 'Queue growth rate spike',
        ],
        [
            'category' => 'system',
            'trigger_type' => 'threshold',
            'trigger_key' => 'oldest_queued_message_age',
            'condition_operator' => 'gt',
            'condition_value' => 30,
            'channels' => ['in_app', 'email', 'sms'],
            'frequency' => 'once_per_breach',
            'cooldown_minutes' => 15,
            'severity' => 'critical',
            'title' => 'Oldest queued message age critical',
        ],
        [
            'category' => 'system',
            'trigger_type' => 'threshold',
            'trigger_key' => 'queued_dlr_count',
            'condition_operator' => 'gt',
            'condition_value' => 1000,
            'channels' => ['in_app', 'email'],
            'frequency' => 'once_per_breach',
            'cooldown_minutes' => 60,
            'severity' => 'warning',
            'title' => 'Queued DLR count above threshold',
        ],
        [
            'category' => 'system',
            'trigger_type' => 'threshold',
            'trigger_key' => 'customer_api_error_rate',
            'condition_operator' => 'gt',
            'condition_value' => 2,
            'channels' => ['in_app', 'email'],
            'frequency' => 'once_per_breach',
            'cooldown_minutes' => 60,
            'severity' => 'warning',
            'title' => 'Customer API error rate above threshold',
        ],
        [
            'category' => 'system',
            'trigger_type' => 'threshold',
            'trigger_key' => 'customer_api_latency',
            'condition_operator' => 'gt',
            'condition_value' => 1200,
            'channels' => ['in_app', 'email'],
            'frequency' => 'once_per_breach',
            'cooldown_minutes' => 60,
            'severity' => 'warning',
            'title' => 'Customer API latency above threshold',
        ],
        [
            'category' => 'system',
            'trigger_type' => 'threshold',
            'trigger_key' => 'webhook_failure_rate',
            'condition_operator' => 'gt',
            'condition_value' => 5,
            'channels' => ['in_app', 'email'],
            'frequency' => 'once_per_breach',
            'cooldown_minutes' => 60,
            'severity' => 'warning',
            'title' => 'Webhook failure rate above threshold',
        ],
        [
            'category' => 'system',
            'trigger_type' => 'threshold',
            'trigger_key' => 'dlr_callback_latency',
            'condition_operator' => 'gt',
            'condition_value' => 15,
            'channels' => ['in_app', 'email'],
            'frequency' => 'once_per_breach',
            'cooldown_minutes' => 60,
            'severity' => 'warning',
            'title' => 'DLR callback latency above threshold',
        ],

        // --- Campaign ---
        [
            'category' => 'campaign',
            'trigger_type' => 'event',
            'trigger_key' => 'campaign_completed',
            'condition_operator' => 'eq',
            'condition_value' => null,
            'channels' => ['in_app'],
            'frequency' => 'instant',
            'cooldown_minutes' => 0,
            'severity' => 'info',
            'title' => 'Campaign completed',
        ],
        [
            'category' => 'campaign',
            'trigger_type' => 'threshold',
            'trigger_key' => 'campaign_delivery_rate',
            'condition_operator' => 'lt',
            'condition_value' => 70,
            'channels' => ['in_app', 'email'],
            'frequency' => 'once_per_breach',
            'cooldown_minutes' => 360,
            'severity' => 'warning',
            'title' => 'Campaign underperforming',
        ],
        [
            'category' => 'campaign',
            'trigger_type' => 'percentage_change',
            'trigger_key' => 'traffic_volume_spike',
            'condition_operator' => 'increases_by',
            'condition_value' => 200,
            'channels' => ['in_app', 'email'],
            'frequency' => 'once_per_breach',
            'cooldown_minutes' => 60,
            'severity' => 'warning',
            'title' => 'Traffic spike detected',
        ],
        [
            'category' => 'campaign',
            'trigger_type' => 'percentage_change',
            'trigger_key' => 'traffic_volume_drop',
            'condition_operator' => 'drops_by',
            'condition_value' => 50,
            'channels' => ['in_app', 'email'],
            'frequency' => 'once_per_breach',
            'cooldown_minutes' => 60,
            'severity' => 'warning',
            'title' => 'Traffic drop detected',
        ],

        // Security & Account Settings
        [
            'category' => 'security',
            'trigger_type' => 'event',
            'trigger_key' => 'security_setting_changed',
            'condition_operator' => 'eq',
            'condition_value' => null,
            'channels' => ['in_app', 'email'],
            'frequency' => 'instant',
            'cooldown_minutes' => 5,
            'severity' => 'warning',
            'title' => 'Security setting changed',
        ],
        [
            'category' => 'security',
            'trigger_type' => 'event',
            'trigger_key' => 'ip_allowlist_changed',
            'condition_operator' => 'eq',
            'condition_value' => null,
            'channels' => ['in_app', 'email'],
            'frequency' => 'instant',
            'cooldown_minutes' => 5,
            'severity' => 'warning',
            'title' => 'IP allowlist changed',
        ],
        [
            'category' => 'security',
            'trigger_type' => 'event',
            'trigger_key' => 'api_connection_state_changed',
            'condition_operator' => 'eq',
            'condition_value' => null,
            'channels' => ['in_app', 'email'],
            'frequency' => 'instant',
            'cooldown_minutes' => 5,
            'severity' => 'warning',
            'title' => 'API connection status changed',
        ],

        // Sub-Account Caps & Limits
        [
            'category' => 'sub_account',
            'trigger_type' => 'threshold',
            'trigger_key' => 'sub_account_spend_cap',
            'condition_operator' => 'gte',
            'condition_value' => 100,
            'channels' => ['in_app', 'email'],
            'frequency' => 'once_per_breach',
            'cooldown_minutes' => 0,
            'severity' => 'warning',
            'title' => 'Sub-account spend cap breached',
        ],
        [
            'category' => 'sub_account',
            'trigger_type' => 'threshold',
            'trigger_key' => 'sub_account_spend_cap_approaching',
            'condition_operator' => 'gte',
            'condition_value' => 80,
            'channels' => ['in_app'],
            'frequency' => 'once_per_breach',
            'cooldown_minutes' => 0,
            'severity' => 'info',
            'title' => 'Sub-account approaching spend cap',
        ],
        [
            'category' => 'sub_account',
            'trigger_type' => 'threshold',
            'trigger_key' => 'sub_account_volume_cap',
            'condition_operator' => 'gte',
            'condition_value' => 100,
            'channels' => ['in_app', 'email'],
            'frequency' => 'once_per_breach',
            'cooldown_minutes' => 0,
            'severity' => 'warning',
            'title' => 'Sub-account volume cap breached',
        ],
        [
            'category' => 'sub_account',
            'trigger_type' => 'threshold',
            'trigger_key' => 'sub_account_volume_cap_approaching',
            'condition_operator' => 'gte',
            'condition_value' => 80,
            'channels' => ['in_app'],
            'frequency' => 'once_per_breach',
            'cooldown_minutes' => 0,
            'severity' => 'info',
            'title' => 'Sub-account approaching volume cap',
        ],
        [
            'category' => 'sub_account',
            'trigger_type' => 'threshold',
            'trigger_key' => 'sub_account_daily_limit',
            'condition_operator' => 'gte',
            'condition_value' => 100,
            'channels' => ['in_app', 'email'],
            'frequency' => 'once_per_breach',
            'cooldown_minutes' => 0,
            'severity' => 'warning',
            'title' => 'Sub-account daily limit breached',
        ],
        [
            'category' => 'sub_account',
            'trigger_type' => 'threshold',
            'trigger_key' => 'sub_account_daily_limit_approaching',
            'condition_operator' => 'gte',
            'condition_value' => 80,
            'channels' => ['in_app'],
            'frequency' => 'once_per_breach',
            'cooldown_minutes' => 0,
            'severity' => 'info',
            'title' => 'Sub-account approaching daily limit',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin Default Alert Rules
    |--------------------------------------------------------------------------
    */

    'admin_defaults' => [
        [
            'category' => 'fraud',
            'trigger_type' => 'event',
            'trigger_key' => 'spam_filter_triggered',
            'condition_operator' => 'eq',
            'condition_value' => null,
            'channels' => ['in_app'],
            'frequency' => 'instant',
            'cooldown_minutes' => 0,
            'severity' => 'warning',
            'title' => 'Spam filter triggered',
        ],
        [
            'category' => 'fraud',
            'trigger_type' => 'event',
            'trigger_key' => 'high_risk_account',
            'condition_operator' => 'eq',
            'condition_value' => null,
            'channels' => ['in_app', 'email', 'slack'],
            'frequency' => 'instant',
            'cooldown_minutes' => 0,
            'severity' => 'critical',
            'title' => 'High-risk account behaviour detected',
        ],
        [
            'category' => 'platform_health',
            'trigger_type' => 'threshold',
            'trigger_key' => 'queue_backlog',
            'condition_operator' => 'gt',
            'condition_value' => 10000,
            'channels' => ['in_app', 'slack'],
            'frequency' => 'once_per_breach',
            'cooldown_minutes' => 30,
            'severity' => 'critical',
            'title' => 'Queue backlog building',
        ],
        [
            'category' => 'platform_health',
            'trigger_type' => 'threshold',
            'trigger_key' => 'dlr_latency_seconds',
            'condition_operator' => 'gt',
            'condition_value' => 300, // 5 minutes
            'channels' => ['in_app', 'slack'],
            'frequency' => 'once_per_breach',
            'cooldown_minutes' => 60,
            'severity' => 'warning',
            'title' => 'DLR latency above threshold',
        ],
        [
            'category' => 'commercial',
            'trigger_type' => 'event',
            'trigger_key' => 'negative_margin_route',
            'condition_operator' => 'eq',
            'condition_value' => null,
            'channels' => ['in_app', 'email'],
            'frequency' => 'daily_digest',
            'cooldown_minutes' => 1440,
            'severity' => 'warning',
            'title' => 'Negative margin route detected',
        ],
        [
            'category' => 'compliance_legal',
            'trigger_type' => 'event',
            'trigger_key' => 'unregistered_sender_attempt',
            'condition_operator' => 'eq',
            'condition_value' => null,
            'channels' => ['in_app'],
            'frequency' => 'batched_1h',
            'cooldown_minutes' => 60,
            'severity' => 'warning',
            'title' => 'Unregistered sender usage attempt',
        ],

        // Customer Risk — Account Management
        [
            'category' => 'customer_risk',
            'trigger_type' => 'event',
            'trigger_key' => 'account_status_override',
            'condition_operator' => 'eq',
            'condition_value' => null,
            'channels' => ['in_app', 'email', 'slack'],
            'frequency' => 'instant',
            'cooldown_minutes' => 0,
            'severity' => 'critical',
            'title' => 'Account status overridden by admin',
        ],
        [
            'category' => 'customer_risk',
            'trigger_type' => 'event',
            'trigger_key' => 'spam_filter_mode_changed',
            'condition_operator' => 'eq',
            'condition_value' => null,
            'channels' => ['in_app'],
            'frequency' => 'instant',
            'cooldown_minutes' => 0,
            'severity' => 'warning',
            'title' => 'Spam filter mode changed',
        ],

        // --- Supplier Monitoring: Tier 1 — Critical Supplier Health ---
        [
            'category' => 'supplier_monitoring',
            'trigger_type' => 'threshold',
            'trigger_key' => 'supplier_delivery_rate',
            'condition_operator' => 'lt',
            'condition_value' => 92,
            'channels' => ['in_app', 'email', 'slack'],
            'frequency' => 'once_per_breach',
            'cooldown_minutes' => 30,
            'severity' => 'critical',
            'title' => 'Delivery rate below threshold',
        ],
        [
            'category' => 'supplier_monitoring',
            'trigger_type' => 'percentage_change',
            'trigger_key' => 'supplier_delivery_rate_deviation',
            'condition_operator' => 'drops_by',
            'condition_value' => 5,
            'channels' => ['in_app', 'email', 'slack'],
            'frequency' => 'once_per_breach',
            'cooldown_minutes' => 30,
            'severity' => 'critical',
            'title' => 'Delivery rate baseline deviation',
        ],
        [
            'category' => 'supplier_monitoring',
            'trigger_type' => 'threshold',
            'trigger_key' => 'supplier_dlr_latency_median',
            'condition_operator' => 'gt',
            'condition_value' => 20,
            'channels' => ['in_app', 'email', 'slack'],
            'frequency' => 'once_per_breach',
            'cooldown_minutes' => 30,
            'severity' => 'critical',
            'title' => 'DLR latency above threshold',
        ],
        [
            'category' => 'supplier_monitoring',
            'trigger_type' => 'threshold',
            'trigger_key' => 'supplier_dlr_latency_p95',
            'condition_operator' => 'gt',
            'condition_value' => 2,
            'channels' => ['in_app', 'email', 'slack'],
            'frequency' => 'once_per_breach',
            'cooldown_minutes' => 60,
            'severity' => 'critical',
            'title' => 'DLR p95 latency anomaly',
        ],
        [
            'category' => 'supplier_monitoring',
            'trigger_type' => 'threshold',
            'trigger_key' => 'supplier_pending_messages',
            'condition_operator' => 'gt',
            'condition_value' => 5000,
            'channels' => ['in_app', 'slack'],
            'frequency' => 'once_per_breach',
            'cooldown_minutes' => 15,
            'severity' => 'warning',
            'title' => 'Pending messages above threshold',
        ],
        [
            'category' => 'supplier_monitoring',
            'trigger_type' => 'threshold',
            'trigger_key' => 'supplier_pending_growth_rate',
            'condition_operator' => 'gt',
            'condition_value' => 20,
            'channels' => ['in_app', 'email', 'slack'],
            'frequency' => 'once_per_breach',
            'cooldown_minutes' => 15,
            'severity' => 'critical',
            'title' => 'Pending message growth rate spike',
        ],
        [
            'category' => 'supplier_monitoring',
            'trigger_type' => 'threshold',
            'trigger_key' => 'supplier_submit_success_rate',
            'condition_operator' => 'lt',
            'condition_value' => 99.5,
            'channels' => ['in_app', 'email', 'slack'],
            'frequency' => 'once_per_breach',
            'cooldown_minutes' => 30,
            'severity' => 'critical',
            'title' => 'Submit success rate below threshold',
        ],
        [
            'category' => 'supplier_monitoring',
            'trigger_type' => 'threshold',
            'trigger_key' => 'supplier_api_availability',
            'condition_operator' => 'lt',
            'condition_value' => 99.9,
            'channels' => ['in_app', 'email', 'slack'],
            'frequency' => 'once_per_breach',
            'cooldown_minutes' => 30,
            'severity' => 'critical',
            'title' => 'Supplier API availability below threshold',
        ],
        [
            'category' => 'supplier_monitoring',
            'trigger_type' => 'threshold',
            'trigger_key' => 'supplier_api_latency',
            'condition_operator' => 'gt',
            'condition_value' => 800,
            'channels' => ['in_app', 'slack'],
            'frequency' => 'once_per_breach',
            'cooldown_minutes' => 15,
            'severity' => 'warning',
            'title' => 'Supplier API latency above threshold',
        ],
        [
            'category' => 'supplier_monitoring',
            'trigger_type' => 'threshold',
            'trigger_key' => 'supplier_api_timeout_rate',
            'condition_operator' => 'gt',
            'condition_value' => 1,
            'channels' => ['in_app', 'email', 'slack'],
            'frequency' => 'once_per_breach',
            'cooldown_minutes' => 30,
            'severity' => 'critical',
            'title' => 'Supplier API timeout rate above threshold',
        ],

        // --- Supplier Monitoring: Tier 2 — Carrier Behaviour ---
        [
            'category' => 'supplier_monitoring',
            'trigger_type' => 'percentage_change',
            'trigger_key' => 'supplier_network_delivery_delta',
            'condition_operator' => 'drops_by',
            'condition_value' => 6,
            'channels' => ['in_app', 'email'],
            'frequency' => 'once_per_breach',
            'cooldown_minutes' => 60,
            'severity' => 'warning',
            'title' => 'Network delivery degradation',
        ],
        [
            'category' => 'supplier_monitoring',
            'trigger_type' => 'threshold',
            'trigger_key' => 'supplier_senderid_rejection_rate',
            'condition_operator' => 'gt',
            'condition_value' => 2,
            'channels' => ['in_app', 'email'],
            'frequency' => 'once_per_breach',
            'cooldown_minutes' => 60,
            'severity' => 'warning',
            'title' => 'Sender ID rejection rate above threshold',
        ],
        [
            'category' => 'supplier_monitoring',
            'trigger_type' => 'percentage_change',
            'trigger_key' => 'supplier_country_delivery_delta',
            'condition_operator' => 'drops_by',
            'condition_value' => 7,
            'channels' => ['in_app', 'email'],
            'frequency' => 'once_per_breach',
            'cooldown_minutes' => 60,
            'severity' => 'warning',
            'title' => 'Country delivery degradation',
        ],
        [
            'category' => 'supplier_monitoring',
            'trigger_type' => 'threshold',
            'trigger_key' => 'supplier_missing_dlr_rate',
            'condition_operator' => 'gt',
            'condition_value' => 3,
            'channels' => ['in_app', 'email'],
            'frequency' => 'once_per_breach',
            'cooldown_minutes' => 60,
            'severity' => 'warning',
            'title' => 'Missing DLR receipt rate above threshold',
        ],
    ],
];
