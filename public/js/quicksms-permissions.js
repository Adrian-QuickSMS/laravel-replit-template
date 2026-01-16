var PermissionManager = (function() {
    'use strict';

    var PERMISSION_CATEGORIES = {
        'messaging-content': {
            label: 'Messaging & Content',
            icon: 'fa-envelope',
            permissions: {
                'send_sms': { label: 'Send SMS Messages', description: 'Compose and send SMS messages' },
                'send_rcs': { label: 'Send RCS Messages', description: 'Compose and send rich RCS messages' },
                'create_templates': { label: 'Create Templates', description: 'Create and edit message templates' },
                'use_templates': { label: 'Use Templates', description: 'Send messages using templates' },
                'schedule_messages': { label: 'Schedule Messages', description: 'Schedule messages for future delivery' },
                'use_ai_assist': { label: 'Use AI Assistant', description: 'Access AI-powered content suggestions' }
            }
        },
        'recipients-contacts': {
            label: 'Recipients & Contacts',
            icon: 'fa-address-book',
            permissions: {
                'view_contacts': { label: 'View Contacts', description: 'View contact records' },
                'create_contacts': { label: 'Create Contacts', description: 'Add new contacts' },
                'edit_contacts': { label: 'Edit Contacts', description: 'Modify existing contacts' },
                'delete_contacts': { label: 'Delete Contacts', description: 'Remove contacts' },
                'manage_lists': { label: 'Manage Lists', description: 'Create and edit contact lists' },
                'upload_csv': { label: 'Upload CSV', description: 'Import contacts via CSV' },
                'export_contacts': { label: 'Export Contacts', description: 'Export contact data' }
            }
        },
        'campaign-controls': {
            label: 'Campaign Controls',
            icon: 'fa-bullhorn',
            permissions: {
                'create_campaigns': { label: 'Create Campaigns', description: 'Create new messaging campaigns' },
                'approve_campaigns': { label: 'Approve Campaigns', description: 'Approve campaigns for sending' },
                'cancel_campaigns': { label: 'Cancel Campaigns', description: 'Cancel scheduled campaigns' },
                'view_campaign_reports': { label: 'View Campaign Reports', description: 'Access campaign analytics' },
                'resend_failed': { label: 'Resend Failed Messages', description: 'Retry failed message deliveries' }
            }
        },
        'configuration': {
            label: 'Configuration',
            icon: 'fa-cogs',
            permissions: {
                'manage_sender_ids': { label: 'Manage Sender IDs', description: 'Register and manage sender IDs' },
                'manage_numbers': { label: 'Manage Numbers', description: 'Configure virtual numbers' },
                'manage_api_keys': { label: 'Manage API Keys', description: 'Create and revoke API keys' },
                'manage_webhooks': { label: 'Manage Webhooks', description: 'Configure webhook endpoints' },
                'manage_email_to_sms': { label: 'Manage Email-to-SMS', description: 'Configure email triggers' }
            }
        },
        'financial-access': {
            label: 'Financial Access',
            icon: 'fa-credit-card',
            permissions: {
                'view_balance': { label: 'View Balance', description: 'View account credit balance' },
                'purchase_credits': { label: 'Purchase Credits', description: 'Buy message credits' },
                'view_invoices': { label: 'View Invoices', description: 'Access billing invoices' },
                'manage_payment_methods': { label: 'Manage Payment Methods', description: 'Add or remove payment cards' },
                'view_spending_reports': { label: 'View Spending Reports', description: 'Access financial reports' }
            }
        },
        'security-governance': {
            label: 'Security & Governance',
            icon: 'fa-shield-alt',
            permissions: {
                'view_audit_logs': { label: 'View Audit Logs', description: 'Access system audit logs' },
                'manage_users': { label: 'Manage Users', description: 'Add, edit, or remove users' },
                'manage_roles': { label: 'Manage Roles', description: 'Assign and modify user roles' },
                'force_password_reset': { label: 'Force Password Reset', description: 'Require users to reset passwords' },
                'manage_mfa_policy': { label: 'Manage MFA Policy', description: 'Configure MFA requirements' },
                'access_security_settings': { label: 'Access Security Settings', description: 'Modify security configuration' }
            }
        }
    };

    var ROLE_DEFAULTS = {
        'owner': {
            'send_sms': true, 'send_rcs': true, 'create_templates': true, 'use_templates': true, 'schedule_messages': true, 'use_ai_assist': true,
            'view_contacts': true, 'create_contacts': true, 'edit_contacts': true, 'delete_contacts': true, 'manage_lists': true, 'upload_csv': true, 'export_contacts': true,
            'create_campaigns': true, 'approve_campaigns': true, 'cancel_campaigns': true, 'view_campaign_reports': true, 'resend_failed': true,
            'manage_sender_ids': true, 'manage_numbers': true, 'manage_api_keys': true, 'manage_webhooks': true, 'manage_email_to_sms': true,
            'view_balance': true, 'purchase_credits': true, 'view_invoices': true, 'manage_payment_methods': true, 'view_spending_reports': true,
            'view_audit_logs': true, 'manage_users': true, 'manage_roles': true, 'force_password_reset': true, 'manage_mfa_policy': true, 'access_security_settings': true
        },
        'admin': {
            'send_sms': true, 'send_rcs': true, 'create_templates': true, 'use_templates': true, 'schedule_messages': true, 'use_ai_assist': true,
            'view_contacts': true, 'create_contacts': true, 'edit_contacts': true, 'delete_contacts': true, 'manage_lists': true, 'upload_csv': true, 'export_contacts': true,
            'create_campaigns': true, 'approve_campaigns': true, 'cancel_campaigns': true, 'view_campaign_reports': true, 'resend_failed': true,
            'manage_sender_ids': true, 'manage_numbers': true, 'manage_api_keys': true, 'manage_webhooks': true, 'manage_email_to_sms': true,
            'view_balance': true, 'purchase_credits': true, 'view_invoices': true, 'manage_payment_methods': false, 'view_spending_reports': true,
            'view_audit_logs': true, 'manage_users': true, 'manage_roles': true, 'force_password_reset': true, 'manage_mfa_policy': false, 'access_security_settings': true
        },
        'messaging-manager': {
            'send_sms': true, 'send_rcs': true, 'create_templates': true, 'use_templates': true, 'schedule_messages': true, 'use_ai_assist': true,
            'view_contacts': true, 'create_contacts': true, 'edit_contacts': true, 'delete_contacts': false, 'manage_lists': true, 'upload_csv': true, 'export_contacts': true,
            'create_campaigns': true, 'approve_campaigns': false, 'cancel_campaigns': true, 'view_campaign_reports': true, 'resend_failed': true,
            'manage_sender_ids': false, 'manage_numbers': false, 'manage_api_keys': false, 'manage_webhooks': false, 'manage_email_to_sms': false,
            'view_balance': true, 'purchase_credits': false, 'view_invoices': false, 'manage_payment_methods': false, 'view_spending_reports': false,
            'view_audit_logs': false, 'manage_users': false, 'manage_roles': false, 'force_password_reset': false, 'manage_mfa_policy': false, 'access_security_settings': false
        },
        'finance': {
            'send_sms': false, 'send_rcs': false, 'create_templates': false, 'use_templates': false, 'schedule_messages': false, 'use_ai_assist': false,
            'view_contacts': false, 'create_contacts': false, 'edit_contacts': false, 'delete_contacts': false, 'manage_lists': false, 'upload_csv': false, 'export_contacts': false,
            'create_campaigns': false, 'approve_campaigns': false, 'cancel_campaigns': false, 'view_campaign_reports': true, 'resend_failed': false,
            'manage_sender_ids': false, 'manage_numbers': false, 'manage_api_keys': false, 'manage_webhooks': false, 'manage_email_to_sms': false,
            'view_balance': true, 'purchase_credits': true, 'view_invoices': true, 'manage_payment_methods': true, 'view_spending_reports': true,
            'view_audit_logs': false, 'manage_users': false, 'manage_roles': false, 'force_password_reset': false, 'manage_mfa_policy': false, 'access_security_settings': false
        },
        'developer': {
            'send_sms': false, 'send_rcs': false, 'create_templates': false, 'use_templates': false, 'schedule_messages': false, 'use_ai_assist': false,
            'view_contacts': false, 'create_contacts': false, 'edit_contacts': false, 'delete_contacts': false, 'manage_lists': false, 'upload_csv': false, 'export_contacts': false,
            'create_campaigns': false, 'approve_campaigns': false, 'cancel_campaigns': false, 'view_campaign_reports': true, 'resend_failed': false,
            'manage_sender_ids': false, 'manage_numbers': false, 'manage_api_keys': true, 'manage_webhooks': true, 'manage_email_to_sms': true,
            'view_balance': false, 'purchase_credits': false, 'view_invoices': false, 'manage_payment_methods': false, 'view_spending_reports': false,
            'view_audit_logs': true, 'manage_users': false, 'manage_roles': false, 'force_password_reset': false, 'manage_mfa_policy': false, 'access_security_settings': false
        },
        'auditor': {
            'send_sms': false, 'send_rcs': false, 'create_templates': false, 'use_templates': false, 'schedule_messages': false, 'use_ai_assist': false,
            'view_contacts': true, 'create_contacts': false, 'edit_contacts': false, 'delete_contacts': false, 'manage_lists': false, 'upload_csv': false, 'export_contacts': true,
            'create_campaigns': false, 'approve_campaigns': false, 'cancel_campaigns': false, 'view_campaign_reports': true, 'resend_failed': false,
            'manage_sender_ids': false, 'manage_numbers': false, 'manage_api_keys': false, 'manage_webhooks': false, 'manage_email_to_sms': false,
            'view_balance': true, 'purchase_credits': false, 'view_invoices': true, 'manage_payment_methods': false, 'view_spending_reports': true,
            'view_audit_logs': true, 'manage_users': false, 'manage_roles': false, 'force_password_reset': false, 'manage_mfa_policy': false, 'access_security_settings': false
        },
        'campaign-approver': {
            'send_sms': false, 'send_rcs': false, 'create_templates': false, 'use_templates': false, 'schedule_messages': false, 'use_ai_assist': false,
            'view_contacts': true, 'create_contacts': false, 'edit_contacts': false, 'delete_contacts': false, 'manage_lists': false, 'upload_csv': false, 'export_contacts': false,
            'create_campaigns': false, 'approve_campaigns': true, 'cancel_campaigns': true, 'view_campaign_reports': true, 'resend_failed': false,
            'manage_sender_ids': false, 'manage_numbers': false, 'manage_api_keys': false, 'manage_webhooks': false, 'manage_email_to_sms': false,
            'view_balance': false, 'purchase_credits': false, 'view_invoices': false, 'manage_payment_methods': false, 'view_spending_reports': false,
            'view_audit_logs': false, 'manage_users': false, 'manage_roles': false, 'force_password_reset': false, 'manage_mfa_policy': false, 'access_security_settings': false
        },
        'security-officer': {
            'send_sms': false, 'send_rcs': false, 'create_templates': false, 'use_templates': false, 'schedule_messages': false, 'use_ai_assist': false,
            'view_contacts': false, 'create_contacts': false, 'edit_contacts': false, 'delete_contacts': false, 'manage_lists': false, 'upload_csv': false, 'export_contacts': false,
            'create_campaigns': false, 'approve_campaigns': false, 'cancel_campaigns': false, 'view_campaign_reports': false, 'resend_failed': false,
            'manage_sender_ids': false, 'manage_numbers': false, 'manage_api_keys': false, 'manage_webhooks': false, 'manage_email_to_sms': false,
            'view_balance': false, 'purchase_credits': false, 'view_invoices': false, 'manage_payment_methods': false, 'view_spending_reports': false,
            'view_audit_logs': true, 'manage_users': true, 'manage_roles': true, 'force_password_reset': true, 'manage_mfa_policy': true, 'access_security_settings': true
        }
    };

    var userOverrides = {};
    var auditCallbacks = [];

    function init() {
        console.log('[PermissionManager] Initialized');
    }

    function getCategories() {
        return PERMISSION_CATEGORIES;
    }

    function getRoleDefaults(role) {
        return ROLE_DEFAULTS[role] || {};
    }

    function getEffectivePermissions(userId, role, overrides) {
        var defaults = ROLE_DEFAULTS[role] || {};
        var effective = {};
        var sources = {};
        
        Object.keys(PERMISSION_CATEGORIES).forEach(function(catKey) {
            var cat = PERMISSION_CATEGORIES[catKey];
            Object.keys(cat.permissions).forEach(function(permKey) {
                var defaultValue = defaults[permKey] === true;
                var overrideValue = overrides && overrides[permKey] !== undefined ? overrides[permKey] : null;
                
                if (overrideValue !== null) {
                    effective[permKey] = overrideValue;
                    sources[permKey] = 'override';
                } else {
                    effective[permKey] = defaultValue;
                    sources[permKey] = 'inherited';
                }
            });
        });
        
        return { permissions: effective, sources: sources };
    }

    function setOverride(userId, permission, value, changedBy, reason) {
        if (!userOverrides[userId]) {
            userOverrides[userId] = {};
        }
        
        var previousValue = userOverrides[userId][permission];
        userOverrides[userId][permission] = value;
        
        var auditEntry = {
            action: 'PERMISSION_OVERRIDE_SET',
            userId: userId,
            permission: permission,
            previousValue: previousValue !== undefined ? previousValue : 'inherited',
            newValue: value,
            reason: reason || null,
            changedBy: changedBy,
            timestamp: new Date().toISOString(),
            ipAddress: '192.168.1.100'
        };
        
        console.log('[AUDIT] Permission override:', auditEntry);
        
        auditCallbacks.forEach(function(cb) {
            try { cb(auditEntry); } catch (e) { console.error(e); }
        });
        
        return { success: true, auditEntry: auditEntry };
    }

    function clearOverride(userId, permission, changedBy, reason) {
        if (!userOverrides[userId]) {
            return { success: false, error: 'No overrides exist for this user' };
        }
        
        var previousValue = userOverrides[userId][permission];
        delete userOverrides[userId][permission];
        
        var auditEntry = {
            action: 'PERMISSION_OVERRIDE_CLEARED',
            userId: userId,
            permission: permission,
            previousValue: previousValue,
            newValue: 'inherited',
            reason: reason || null,
            changedBy: changedBy,
            timestamp: new Date().toISOString(),
            ipAddress: '192.168.1.100'
        };
        
        console.log('[AUDIT] Permission override cleared:', auditEntry);
        
        auditCallbacks.forEach(function(cb) {
            try { cb(auditEntry); } catch (e) { console.error(e); }
        });
        
        return { success: true, auditEntry: auditEntry };
    }

    function getUserOverrides(userId) {
        return userOverrides[userId] || {};
    }

    function hasPermission(userId, role, permission, overrides) {
        var result = getEffectivePermissions(userId, role, overrides || userOverrides[userId]);
        return result.permissions[permission] === true;
    }

    function onPermissionChange(callback) {
        if (typeof callback === 'function') {
            auditCallbacks.push(callback);
        }
    }

    function getPermissionInfo(permission) {
        for (var catKey in PERMISSION_CATEGORIES) {
            var cat = PERMISSION_CATEGORIES[catKey];
            if (cat.permissions[permission]) {
                return {
                    category: catKey,
                    categoryLabel: cat.label,
                    ...cat.permissions[permission]
                };
            }
        }
        return null;
    }

    function countOverrides(userId) {
        var overrides = userOverrides[userId] || {};
        return Object.keys(overrides).length;
    }

    return {
        init: init,
        getCategories: getCategories,
        getRoleDefaults: getRoleDefaults,
        getEffectivePermissions: getEffectivePermissions,
        setOverride: setOverride,
        clearOverride: clearOverride,
        getUserOverrides: getUserOverrides,
        hasPermission: hasPermission,
        onPermissionChange: onPermissionChange,
        getPermissionInfo: getPermissionInfo,
        countOverrides: countOverrides,
        CATEGORIES: PERMISSION_CATEGORIES,
        ROLE_DEFAULTS: ROLE_DEFAULTS
    };
})();

if (typeof module !== 'undefined' && module.exports) {
    module.exports = PermissionManager;
}
