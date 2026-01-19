<?php

namespace App\Services\Admin;

class AdminRbacService
{
    const ROLE_SUPER_ADMIN = 'super_admin';
    const ROLE_SUPPORT = 'support';
    const ROLE_FINANCE = 'finance';
    const ROLE_COMPLIANCE = 'compliance';
    const ROLE_SALES = 'sales';
    
    const RESPONSIBILITY_OBSERVE = 'observe';
    const RESPONSIBILITY_CONTROL = 'control';
    const RESPONSIBILITY_INVESTIGATE = 'investigate';
    const RESPONSIBILITY_GOVERN = 'govern';
    
    protected static array $responsibilityDefinitions = [
        self::RESPONSIBILITY_OBSERVE => [
            'name' => 'Observe',
            'description' => 'View-only access to traffic, outcomes, routing, and financials',
            'icon' => 'fa-eye',
            'color' => '#4a90d9',
            'permissions' => [
                'observe.traffic',
                'observe.delivery_outcomes',
                'observe.supplier_routing',
                'observe.revenue',
                'observe.cost',
                'observe.margin',
                'observe.configuration'
            ]
        ],
        self::RESPONSIBILITY_CONTROL => [
            'name' => 'Control',
            'description' => 'Approve, block, suspend, and override system state',
            'icon' => 'fa-sliders-h',
            'color' => '#f59e0b',
            'permissions' => [
                'control.sender_id.approve',
                'control.sender_id.block',
                'control.rcs_agent.approve',
                'control.rcs_agent.block',
                'control.country.approve',
                'control.country.block',
                'control.account.set_state',
                'control.account.set_credit_limit',
                'control.account.set_test_allowance',
                'control.account.suspend',
                'control.account.reactivate',
                'control.api.suspend',
                'control.api.reactivate',
                'control.number.suspend',
                'control.number.reactivate',
                'control.email_to_sms.suspend',
                'control.email_to_sms.reactivate'
            ]
        ],
        self::RESPONSIBILITY_INVESTIGATE => [
            'name' => 'Investigate',
            'description' => 'Support access for troubleshooting and impersonation',
            'icon' => 'fa-search',
            'color' => '#10b981',
            'permissions' => [
                'investigate.view_config',
                'investigate.reproduce_issues',
                'investigate.impersonate',
                'investigate.reveal_data'
            ]
        ],
        self::RESPONSIBILITY_GOVERN => [
            'name' => 'Govern',
            'description' => 'Compliance enforcement and audit management',
            'icon' => 'fa-gavel',
            'color' => '#8b5cf6',
            'permissions' => [
                'govern.anti_spam',
                'govern.country_restrictions',
                'govern.third_party_approvals',
                'govern.audit_trails'
            ]
        ]
    ];
    
    protected static array $roleDefinitions = [
        self::ROLE_SUPER_ADMIN => [
            'name' => 'Super Admin',
            'description' => 'Full access to all admin functions',
            'responsibilities' => [
                self::RESPONSIBILITY_OBSERVE,
                self::RESPONSIBILITY_CONTROL,
                self::RESPONSIBILITY_INVESTIGATE,
                self::RESPONSIBILITY_GOVERN
            ],
            'permissions' => ['*'],
            'level' => 100
        ],
        self::ROLE_SUPPORT => [
            'name' => 'Support',
            'description' => 'Customer support and troubleshooting',
            'responsibilities' => [
                self::RESPONSIBILITY_OBSERVE,
                self::RESPONSIBILITY_INVESTIGATE
            ],
            'permissions' => [
                'observe.traffic',
                'observe.delivery_outcomes',
                'observe.configuration',
                'investigate.view_config',
                'investigate.reproduce_issues',
                'investigate.impersonate',
                'investigate.reveal_data',
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
            'responsibilities' => [
                self::RESPONSIBILITY_OBSERVE
            ],
            'permissions' => [
                'observe.revenue',
                'observe.cost',
                'observe.margin',
                'observe.supplier_routing',
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
            'responsibilities' => [
                self::RESPONSIBILITY_OBSERVE,
                self::RESPONSIBILITY_CONTROL,
                self::RESPONSIBILITY_GOVERN
            ],
            'permissions' => [
                'observe.traffic',
                'observe.configuration',
                'control.sender_id.approve',
                'control.sender_id.block',
                'control.rcs_agent.approve',
                'control.rcs_agent.block',
                'control.country.approve',
                'control.country.block',
                'govern.anti_spam',
                'govern.country_restrictions',
                'govern.third_party_approvals',
                'govern.audit_trails',
                'accounts.view',
                'campaigns.approvals',
                'campaigns.blocked',
                'assets.sender_ids',
                'assets.rcs_agents',
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
            'responsibilities' => [
                self::RESPONSIBILITY_OBSERVE
            ],
            'permissions' => [
                'observe.traffic',
                'observe.revenue',
                'accounts.view',
                'accounts.details',
                'reporting.client',
                'system.pricing'
            ],
            'level' => 40
        ]
    ];
    
    protected static array $permissionDefinitions = [
        'observe.traffic' => 'View all client message traffic',
        'observe.delivery_outcomes' => 'View delivery success/failure outcomes',
        'observe.supplier_routing' => 'View supplier routing decisions',
        'observe.revenue' => 'View revenue data',
        'observe.cost' => 'View cost data',
        'observe.margin' => 'View margin calculations',
        'observe.configuration' => 'View all configuration state (read-only)',
        
        'control.sender_id.approve' => 'Approve sender ID registrations',
        'control.sender_id.block' => 'Block sender IDs',
        'control.rcs_agent.approve' => 'Approve RCS agent registrations',
        'control.rcs_agent.block' => 'Block RCS agents',
        'control.country.approve' => 'Approve destination countries',
        'control.country.block' => 'Block destination countries',
        'control.account.set_state' => 'Override account states (Test/Live/Suspended)',
        'control.account.set_credit_limit' => 'Set account credit limits',
        'control.account.set_test_allowance' => 'Set test message allowances',
        'control.account.suspend' => 'Suspend accounts',
        'control.account.reactivate' => 'Reactivate suspended accounts',
        'control.api.suspend' => 'Suspend API connections',
        'control.api.reactivate' => 'Reactivate API connections',
        'control.number.suspend' => 'Suspend numbers',
        'control.number.reactivate' => 'Reactivate numbers',
        'control.email_to_sms.suspend' => 'Suspend Email-to-SMS configurations',
        'control.email_to_sms.reactivate' => 'Reactivate Email-to-SMS configurations',
        
        'investigate.view_config' => 'Safely view customer configuration',
        'investigate.reproduce_issues' => 'Reproduce issues without mutating state',
        'investigate.impersonate' => 'Impersonate customer UI (logged to admin audit only)',
        'investigate.reveal_data' => 'Reveal masked PII data with audit trail',
        
        'govern.anti_spam' => 'Enforce anti-spam rules',
        'govern.country_restrictions' => 'Enforce country restrictions',
        'govern.third_party_approvals' => 'Track third-party approvals',
        'govern.audit_trails' => 'Maintain immutable audit trails',
        
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
    
    public static function getResponsibilities(): array
    {
        return self::$responsibilityDefinitions;
    }
    
    public static function getResponsibility(string $responsibilityId): ?array
    {
        return self::$responsibilityDefinitions[$responsibilityId] ?? null;
    }
    
    public static function getRoleResponsibilities(?string $roleId): array
    {
        if (!$roleId) {
            return [];
        }
        
        $role = self::getRole($roleId);
        if (!$role) {
            return [];
        }
        
        return $role['responsibilities'] ?? [];
    }
    
    public static function hasResponsibility(?string $roleId, string $responsibility): bool
    {
        if (!self::isRbacEnabled()) {
            return true;
        }
        
        $responsibilities = self::getRoleResponsibilities($roleId);
        return in_array($responsibility, $responsibilities);
    }
    
    public static function canObserve(): bool
    {
        return self::hasResponsibility(self::getUserRole(), self::RESPONSIBILITY_OBSERVE);
    }
    
    public static function canControl(): bool
    {
        return self::hasResponsibility(self::getUserRole(), self::RESPONSIBILITY_CONTROL);
    }
    
    public static function canInvestigate(): bool
    {
        return self::hasResponsibility(self::getUserRole(), self::RESPONSIBILITY_INVESTIGATE);
    }
    
    public static function canGovern(): bool
    {
        return self::hasResponsibility(self::getUserRole(), self::RESPONSIBILITY_GOVERN);
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
    
    public static function getActiveResponsibilitiesForUser(): array
    {
        $role = self::getUserRole();
        $responsibilities = self::getRoleResponsibilities($role);
        
        $result = [];
        foreach ($responsibilities as $respId) {
            $resp = self::getResponsibility($respId);
            if ($resp) {
                $result[$respId] = $resp;
            }
        }
        
        return $result;
    }
}
