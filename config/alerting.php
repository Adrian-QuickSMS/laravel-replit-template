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
            'condition_value' => 85, // delivery rate drops below 85%
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
            'channels' => ['in_app'],
            'frequency' => 'instant',
            'cooldown_minutes' => 0,
            'severity' => 'warning',
            'title' => 'Spam filter mode changed',
        ],
    ],
];
