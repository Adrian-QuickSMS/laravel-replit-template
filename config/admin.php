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
    
    'impersonation' => [
        'max_duration' => env('ADMIN_IMPERSONATION_MAX_DURATION', 300),
        'min_reason_length' => 10,
    ],
    
    'users' => [
        [
            'id' => 'admin-001',
            'email' => 'admin@quicksms.co.uk',
            'password_hash' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'name' => 'System Administrator',
            'role' => 'super_admin',
            'mfa_secret' => null,
            'mfa_enabled' => false,
            'status' => 'active'
        ],
        [
            'id' => 'admin-002',
            'email' => 'support@quicksms.co.uk',
            'password_hash' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
            'name' => 'Support Team',
            'role' => 'support',
            'mfa_secret' => null,
            'mfa_enabled' => false,
            'status' => 'active'
        ],
        [
            'id' => 'admin-003',
            'email' => 'finance@quicksms.co.uk',
            'password_hash' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
            'name' => 'Finance Team',
            'role' => 'finance',
            'mfa_secret' => null,
            'mfa_enabled' => false,
            'status' => 'active'
        ],
        [
            'id' => 'admin-004',
            'email' => 'compliance@quicksms.co.uk',
            'password_hash' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
            'name' => 'Compliance Team',
            'role' => 'compliance',
            'mfa_secret' => null,
            'mfa_enabled' => false,
            'status' => 'active'
        ]
    ],
    
    'audit' => [
        'retention_days' => 2555,
        'log_channel' => 'admin_audit'
    ]
];
