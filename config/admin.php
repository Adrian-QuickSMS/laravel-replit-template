<?php

return [
    'session_timeout' => env('ADMIN_SESSION_TIMEOUT', 3600),
    
    'mfa' => [
        'required' => env('ADMIN_MFA_REQUIRED', true),
        'issuer' => env('ADMIN_MFA_ISSUER', 'QuickSMS Admin'),
    ],
    
    'ip_allowlist' => [
        'enabled' => env('ADMIN_IP_ALLOWLIST_ENABLED', false),
        'ips' => array_filter(explode(',', env('ADMIN_IP_ALLOWLIST_IPS', ''))),
        'cidrs' => array_filter(explode(',', env('ADMIN_IP_ALLOWLIST_CIDRS', ''))),
    ],
    
    'rbac' => [
        'enabled' => env('ADMIN_RBAC_ENABLED', false),
    ],
    
    'security' => [
        'whitelisted_domains' => [
            'quicksms.co.uk',
            'quicksms.com',
        ],
        'max_login_attempts' => env('ADMIN_MAX_LOGIN_ATTEMPTS', 5),
        'lockout_duration' => env('ADMIN_LOCKOUT_DURATION', 900),
        'customer_portal_redirect' => '/dashboard',
    ],
    
    'impersonation' => [
        'enabled' => env('ADMIN_IMPERSONATION_ENABLED', true),
        'max_duration' => env('ADMIN_IMPERSONATION_MAX_DURATION', 300),
        'min_reason_length' => 10,
        'read_only' => true,
        'log_all_actions' => true,
    ],
    
    // DEPRECATED: Admin users now stored in admin_users DB table.
    // Config users array emptied — no hardcoded credentials in source.
    'users' => [],
    
    'audit' => [
        'retention_days' => 2555,
        'log_channel' => 'admin_audit'
    ]
];
