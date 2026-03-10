var RoleNavigation = (function() {
    'use strict';
    
    var ROLE_DEFINITIONS = {
        'owner': {
            label: 'Account Owner',
            description: 'Full access to all features. One per Main Account.',
            isUnique: true,
            canBeAssigned: false,
            navigation: ['dashboard', 'messages', 'contacts', 'reporting', 'purchase', 'management', 'account', 'support'],
            accountScope: ['details', 'users', 'sub-accounts', 'audit-logs', 'security'],
            specialCapabilities: ['transfer_ownership', 'delete_account', 'manage_billing']
        },
        'admin': {
            label: 'Admin',
            description: 'Full access to all features within their scope.',
            isUnique: false,
            canBeAssigned: true,
            navigation: ['dashboard', 'messages', 'contacts', 'reporting', 'purchase', 'management', 'account', 'support'],
            accountScope: ['details', 'users', 'sub-accounts', 'audit-logs', 'security'],
            specialCapabilities: ['manage_users', 'manage_sub_accounts']
        },
        'messaging-manager': {
            label: 'Messaging Manager',
            description: 'Full access to messaging, contacts, and related reporting.',
            isUnique: false,
            canBeAssigned: true,
            navigation: ['dashboard', 'messages', 'contacts', 'reporting', 'management', 'support'],
            accountScope: [],
            managementScope: ['templates', 'numbers'],
            reportingScope: ['dashboard', 'message-log'],
            specialCapabilities: ['send_messages', 'manage_contacts', 'manage_templates']
        },
        'finance': {
            label: 'Finance / Billing',
            description: 'Access to billing, invoices, and financial reporting.',
            isUnique: false,
            canBeAssigned: true,
            navigation: ['dashboard', 'reporting', 'purchase', 'support'],
            accountScope: ['details'],
            reportingScope: ['finance-data', 'invoices'],
            specialCapabilities: ['view_billing', 'purchase_credits']
        },
        'developer': {
            label: 'Developer / API User',
            description: 'Access to API connections, documentation, and technical settings.',
            isUnique: false,
            canBeAssigned: true,
            navigation: ['dashboard', 'management', 'reporting', 'support'],
            managementScope: ['api-connections', 'email-to-sms'],
            reportingScope: ['message-log'],
            specialCapabilities: ['manage_api_keys', 'view_webhooks']
        },
        'auditor': {
            label: 'Read-Only / Auditor',
            description: 'View-only access to all sections for compliance review.',
            isUnique: false,
            canBeAssigned: true,
            navigation: ['dashboard', 'messages', 'contacts', 'reporting', 'management', 'account', 'support'],
            accountScope: ['details', 'audit-logs'],
            isReadOnly: true,
            specialCapabilities: ['view_all', 'export_reports']
        },
        'campaign-approver': {
            label: 'Campaign Approver',
            description: 'Can review and approve campaigns before sending.',
            isUnique: false,
            canBeAssigned: true,
            isOptional: true,
            navigation: ['dashboard', 'messages', 'reporting', 'support'],
            reportingScope: ['dashboard', 'message-log'],
            specialCapabilities: ['approve_campaigns', 'reject_campaigns']
        },
        'security-officer': {
            label: 'Security Officer',
            description: 'Manages security settings, MFA policies, and access reviews.',
            isUnique: false,
            canBeAssigned: true,
            isOptional: true,
            navigation: ['dashboard', 'account', 'reporting', 'support'],
            accountScope: ['users', 'audit-logs', 'security'],
            specialCapabilities: ['manage_mfa_policy', 'force_password_reset', 'review_access']
        }
    };

    var NAVIGATION_SECTIONS = {
        'dashboard': { selector: '[data-nav="dashboard"]', label: 'Dashboard' },
        'messages': { selector: '[data-nav="messages"]', label: 'Messages' },
        'contacts': { selector: '[data-nav="contacts"]', label: 'Contact Book' },
        'reporting': { selector: '[data-nav="reporting"]', label: 'Reporting' },
        'purchase': { selector: '[data-nav="purchase"]', label: 'Purchase' },
        'management': { selector: '[data-nav="management"]', label: 'Management' },
        'account': { selector: '[data-nav="account"]', label: 'Account' },
        'support': { selector: '[data-nav="support"]', label: 'Support' }
    };

    var currentUserRole = null;
    var auditCallbacks = [];

    function init(userRole) {
        currentUserRole = userRole || 'auditor';
        applyNavigationVisibility();
        console.log('[RoleNavigation] Initialized with role:', currentUserRole);
    }

    function applyNavigationVisibility() {
        var roleDef = ROLE_DEFINITIONS[currentUserRole];
        if (!roleDef) {
            console.warn('[RoleNavigation] Unknown role:', currentUserRole);
            return;
        }

        var allowedNav = roleDef.navigation || [];
        
        Object.keys(NAVIGATION_SECTIONS).forEach(function(navKey) {
            var section = NAVIGATION_SECTIONS[navKey];
            var element = document.querySelector(section.selector);
            
            if (element) {
                if (allowedNav.includes(navKey)) {
                    element.style.display = '';
                    element.removeAttribute('data-role-hidden');
                } else {
                    element.style.display = 'none';
                    element.setAttribute('data-role-hidden', 'true');
                }
            }
        });

        applySubNavigationVisibility(roleDef);
        applyReadOnlyMode(roleDef.isReadOnly === true);
    }

    function applySubNavigationVisibility(roleDef) {
        if (roleDef.accountScope) {
            document.querySelectorAll('[data-nav="account"] [data-subnav]').forEach(function(el) {
                var subnavKey = el.getAttribute('data-subnav');
                if (roleDef.accountScope.includes(subnavKey)) {
                    el.style.display = '';
                } else {
                    el.style.display = 'none';
                }
            });
        }

        if (roleDef.managementScope) {
            document.querySelectorAll('[data-nav="management"] [data-subnav]').forEach(function(el) {
                var subnavKey = el.getAttribute('data-subnav');
                if (roleDef.managementScope.includes(subnavKey)) {
                    el.style.display = '';
                } else {
                    el.style.display = 'none';
                }
            });
        }

        if (roleDef.reportingScope) {
            document.querySelectorAll('[data-nav="reporting"] [data-subnav]').forEach(function(el) {
                var subnavKey = el.getAttribute('data-subnav');
                if (roleDef.reportingScope.includes(subnavKey)) {
                    el.style.display = '';
                } else {
                    el.style.display = 'none';
                }
            });
        }
    }

    function applyReadOnlyMode(isReadOnly) {
        if (isReadOnly) {
            document.body.classList.add('role-read-only');
            document.querySelectorAll('[data-action-button], .btn-primary, .btn-success, .btn-warning').forEach(function(btn) {
                if (!btn.hasAttribute('data-allow-readonly')) {
                    btn.classList.add('role-disabled');
                    btn.setAttribute('data-original-disabled', btn.disabled);
                    btn.disabled = true;
                }
            });
        } else {
            document.body.classList.remove('role-read-only');
            document.querySelectorAll('.role-disabled').forEach(function(btn) {
                btn.classList.remove('role-disabled');
                var originalDisabled = btn.getAttribute('data-original-disabled');
                btn.disabled = originalDisabled === 'true';
                btn.removeAttribute('data-original-disabled');
            });
        }
    }

    function changeRole(userId, newRole, changedBy, reason) {
        var oldRole = currentUserRole;
        var roleDef = ROLE_DEFINITIONS[newRole];
        
        if (!roleDef) {
            console.error('[RoleNavigation] Invalid role:', newRole);
            return { success: false, error: 'Invalid role' };
        }

        if (!roleDef.canBeAssigned && newRole !== 'owner') {
            console.error('[RoleNavigation] Role cannot be assigned:', newRole);
            return { success: false, error: 'Role cannot be assigned' };
        }

        var auditEntry = {
            action: 'ROLE_CHANGED',
            userId: userId,
            previousRole: oldRole,
            newRole: newRole,
            changedBy: changedBy,
            reason: reason || null,
            timestamp: new Date().toISOString(),
            ipAddress: '192.168.1.100'
        };

        console.log('[AUDIT] Role change:', auditEntry);
        
        auditCallbacks.forEach(function(cb) {
            try { cb(auditEntry); } catch (e) { console.error(e); }
        });

        return { 
            success: true, 
            auditEntry: auditEntry,
            previousRole: oldRole,
            newRole: newRole
        };
    }

    function onRoleChange(callback) {
        if (typeof callback === 'function') {
            auditCallbacks.push(callback);
        }
    }

    function getRoleDefinition(role) {
        return ROLE_DEFINITIONS[role] || null;
    }

    function getAllRoles(includeOptional) {
        var roles = [];
        Object.keys(ROLE_DEFINITIONS).forEach(function(key) {
            var def = ROLE_DEFINITIONS[key];
            if (includeOptional || !def.isOptional) {
                roles.push({
                    id: key,
                    label: def.label,
                    description: def.description,
                    canBeAssigned: def.canBeAssigned,
                    isOptional: def.isOptional || false
                });
            }
        });
        return roles;
    }

    function getAssignableRoles(includeOptional) {
        return getAllRoles(includeOptional).filter(function(r) { return r.canBeAssigned; });
    }

    function canAccessSection(role, section) {
        var roleDef = ROLE_DEFINITIONS[role];
        if (!roleDef) return false;
        return (roleDef.navigation || []).includes(section);
    }

    function hasCapability(role, capability) {
        var roleDef = ROLE_DEFINITIONS[role];
        if (!roleDef) return false;
        return (roleDef.specialCapabilities || []).includes(capability);
    }

    function getCurrentRole() {
        return currentUserRole;
    }

    function isReadOnly() {
        var roleDef = ROLE_DEFINITIONS[currentUserRole];
        return roleDef ? roleDef.isReadOnly === true : false;
    }

    return {
        init: init,
        changeRole: changeRole,
        onRoleChange: onRoleChange,
        getRoleDefinition: getRoleDefinition,
        getAllRoles: getAllRoles,
        getAssignableRoles: getAssignableRoles,
        canAccessSection: canAccessSection,
        hasCapability: hasCapability,
        getCurrentRole: getCurrentRole,
        isReadOnly: isReadOnly,
        ROLES: ROLE_DEFINITIONS
    };
})();

if (typeof module !== 'undefined' && module.exports) {
    module.exports = RoleNavigation;
}
