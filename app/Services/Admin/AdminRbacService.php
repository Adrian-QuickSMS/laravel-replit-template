<?php

namespace App\Services\Admin;

class AdminRbacService
{
    const ROLE_SUPER_ADMIN = 'super_admin';
    const ROLE_SUPPORT = 'support';
    const ROLE_FINANCE = 'finance';
    const ROLE_COMPLIANCE = 'compliance';
    const ROLE_SALES = 'sales';
    
    protected static array $roleDefinitions = [
        self::ROLE_SUPER_ADMIN => [
            'name' => 'Super Admin',
            'description' => 'Full access to all admin functions',
            'permissions' => ['*'],
            'level' => 100
        ],
        self::ROLE_SUPPORT => [
            'name' => 'Support',
            'description' => 'Customer support and troubleshooting',
            'permissions' => [
                'accounts.view',
                'accounts.impersonate',
                'reporting.message_log',
                'reporting.client',
                'campaigns.view',
                'assets.view',
                'audit_logs.view'
            ],
            'level' => 50
        ],
        self::ROLE_FINANCE => [
            'name' => 'Finance',
            'description' => 'Financial operations and billing',
            'permissions' => [
                'accounts.view',
                'accounts.balances',
                'reporting.finance',
                'reporting.supplier',
                'billing.invoices',
                'billing.payments',
                'billing.credits'
            ],
            'level' => 50
        ],
        self::ROLE_COMPLIANCE => [
            'name' => 'Compliance',
            'description' => 'Regulatory compliance and content review',
            'permissions' => [
                'accounts.view',
                'campaigns.approvals',
                'campaigns.blocked',
                'assets.sender_ids',
                'assets.templates',
                'security.audit_logs',
                'security.country_controls',
                'security.anti_spam'
            ],
            'level' => 60
        ],
        self::ROLE_SALES => [
            'name' => 'Sales',
            'description' => 'Sales and account management',
            'permissions' => [
                'accounts.view',
                'accounts.details',
                'reporting.client',
                'system.pricing'
            ],
            'level' => 40
        ]
    ];
    
    protected static array $permissionDefinitions = [
        'accounts.view' => 'View account list and basic details',
        'accounts.edit' => 'Edit account settings',
        'accounts.impersonate' => 'Impersonate customer accounts',
        'accounts.balances' => 'View and manage account balances',
        'accounts.details' => 'View detailed account information',
        'reporting.message_log' => 'Access global message log',
        'reporting.client' => 'Access client reporting',
        'reporting.supplier' => 'Access supplier reporting',
        'reporting.finance' => 'Access financial reports',
        'campaigns.view' => 'View campaigns',
        'campaigns.approvals' => 'Approve/reject campaigns',
        'campaigns.blocked' => 'Manage blocked campaigns',
        'assets.view' => 'View messaging assets',
        'assets.sender_ids' => 'Manage sender ID approvals',
        'assets.rcs_agents' => 'Manage RCS agent approvals',
        'assets.templates' => 'Manage template approvals',
        'assets.numbers' => 'Manage number allocations',
        'api.view' => 'View API connections',
        'api.manage' => 'Manage API settings',
        'billing.invoices' => 'View and manage invoices',
        'billing.payments' => 'View and process payments',
        'billing.credits' => 'Manage account credits',
        'security.audit_logs' => 'View admin audit logs',
        'security.country_controls' => 'Manage country controls',
        'security.anti_spam' => 'Manage anti-spam settings',
        'security.ip_allowlists' => 'Manage IP allowlists',
        'system.pricing' => 'Manage pricing configuration',
        'system.routing' => 'Manage message routing',
        'system.flags' => 'Manage feature flags',
        'audit_logs.view' => 'View audit logs',
        'data.reveal' => 'Reveal masked PII data'
    ];
    
    public static function isRbacEnabled(): bool
    {
        return config('admin.rbac.enabled', false);
    }
    
    public static function getRoles(): array
    {
        return self::$roleDefinitions;
    }
    
    public static function getRole(string $roleId): ?array
    {
        return self::$roleDefinitions[$roleId] ?? null;
    }
    
    public static function getPermissions(): array
    {
        return self::$permissionDefinitions;
    }
    
    public static function hasPermission(?string $userRole, string $permission): bool
    {
        if (!self::isRbacEnabled()) {
            return true;
        }
        
        if (!$userRole) {
            return false;
        }
        
        $role = self::getRole($userRole);
        if (!$role) {
            return false;
        }
        
        $permissions = $role['permissions'] ?? [];
        
        if (in_array('*', $permissions)) {
            return true;
        }
        
        if (in_array($permission, $permissions)) {
            return true;
        }
        
        $permissionParts = explode('.', $permission);
        if (count($permissionParts) > 1) {
            $wildcardPermission = $permissionParts[0] . '.*';
            if (in_array($wildcardPermission, $permissions)) {
                return true;
            }
        }
        
        return false;
    }
    
    public static function getUserRole(): ?string
    {
        $adminSession = session('admin_auth');
        return $adminSession['role'] ?? null;
    }
    
    public static function can(string $permission): bool
    {
        return self::hasPermission(self::getUserRole(), $permission);
    }
    
    public static function canAny(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (self::can($permission)) {
                return true;
            }
        }
        return false;
    }
    
    public static function canAll(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (!self::can($permission)) {
                return false;
            }
        }
        return true;
    }
}
