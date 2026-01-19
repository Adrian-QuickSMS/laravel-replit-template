var AuditLogger = (function() {
    'use strict';

    var ACTOR_TYPES = {
        USER: 'user',
        SYSTEM: 'system',
        API: 'api'
    };

    var MODULES = {
        ACCOUNT: 'account',
        USERS: 'users',
        SUB_ACCOUNTS: 'sub_accounts',
        PERMISSIONS: 'permissions',
        SECURITY: 'security',
        AUTHENTICATION: 'authentication',
        MESSAGING: 'messaging',
        CAMPAIGNS: 'campaigns',
        CONTACTS: 'contacts',
        REPORTING: 'reporting',
        FINANCIAL: 'financial',
        COMPLIANCE: 'compliance',
        API: 'api',
        SYSTEM: 'system'
    };

    var EVENT_TYPES = {
        USER_CREATED: { module: MODULES.USERS, category: 'user_management', severity: 'high', description: 'New user account created' },
        USER_INVITED: { module: MODULES.USERS, category: 'user_management', severity: 'medium', description: 'User invitation sent' },
        USER_SUSPENDED: { module: MODULES.USERS, category: 'user_management', severity: 'high', description: 'User account suspended' },
        USER_REACTIVATED: { module: MODULES.USERS, category: 'user_management', severity: 'high', description: 'User account reactivated' },
        USER_DELETED: { module: MODULES.USERS, category: 'user_management', severity: 'critical', description: 'User account deleted' },
        USER_UPDATED: { module: MODULES.USERS, category: 'user_management', severity: 'low', description: 'User details updated' },

        ROLE_CHANGED: { module: MODULES.PERMISSIONS, category: 'access_control', severity: 'high', description: 'User role modified' },
        ROLE_ASSIGNED: { module: MODULES.PERMISSIONS, category: 'access_control', severity: 'high', description: 'Role assigned to user' },
        PERMISSION_GRANTED: { module: MODULES.PERMISSIONS, category: 'access_control', severity: 'medium', description: 'Permission granted' },
        PERMISSION_REVOKED: { module: MODULES.PERMISSIONS, category: 'access_control', severity: 'medium', description: 'Permission revoked' },
        PERMISSION_OVERRIDE_SET: { module: MODULES.PERMISSIONS, category: 'access_control', severity: 'medium', description: 'Permission override configured' },
        PERMISSION_OVERRIDE_REMOVED: { module: MODULES.PERMISSIONS, category: 'access_control', severity: 'medium', description: 'Permission override removed' },
        PERMISSIONS_RESET: { module: MODULES.PERMISSIONS, category: 'access_control', severity: 'high', description: 'Permissions reset to defaults' },
        SENDER_CAPABILITY_CHANGED: { module: MODULES.PERMISSIONS, category: 'access_control', severity: 'medium', description: 'Sender capability level modified' },

        SUB_ACCOUNT_CREATED: { module: MODULES.SUB_ACCOUNTS, category: 'account', severity: 'high', description: 'Sub-account created' },
        SUB_ACCOUNT_SUSPENDED: { module: MODULES.SUB_ACCOUNTS, category: 'account', severity: 'high', description: 'Sub-account suspended' },
        SUB_ACCOUNT_REACTIVATED: { module: MODULES.SUB_ACCOUNTS, category: 'account', severity: 'high', description: 'Sub-account reactivated' },
        SUB_ACCOUNT_ARCHIVED: { module: MODULES.SUB_ACCOUNTS, category: 'account', severity: 'high', description: 'Sub-account archived' },

        ENFORCEMENT_OVERRIDE_REQUESTED: { module: MODULES.SUB_ACCOUNTS, category: 'enforcement', severity: 'high', description: 'Enforcement override requested' },
        ENFORCEMENT_OVERRIDE_APPROVED: { module: MODULES.SUB_ACCOUNTS, category: 'enforcement', severity: 'high', description: 'Enforcement override approved' },
        ENFORCEMENT_OVERRIDE_DENIED: { module: MODULES.SUB_ACCOUNTS, category: 'enforcement', severity: 'medium', description: 'Enforcement override denied' },
        ENFORCEMENT_RULE_CHANGED: { module: MODULES.SUB_ACCOUNTS, category: 'enforcement', severity: 'medium', description: 'Enforcement rule modified' },
        ENFORCEMENT_TRIGGERED: { module: MODULES.SUB_ACCOUNTS, category: 'enforcement', severity: 'medium', description: 'Enforcement limit triggered' },

        MFA_ENABLED: { module: MODULES.SECURITY, category: 'security', severity: 'medium', description: 'Multi-factor authentication enabled' },
        MFA_DISABLED: { module: MODULES.SECURITY, category: 'security', severity: 'high', description: 'Multi-factor authentication disabled' },
        MFA_RESET: { module: MODULES.SECURITY, category: 'security', severity: 'high', description: 'Multi-factor authentication reset' },
        MFA_RECOVERY_USED: { module: MODULES.SECURITY, category: 'security', severity: 'high', description: 'MFA recovery code used' },
        IP_ALLOWLIST_UPDATED: { module: MODULES.SECURITY, category: 'security', severity: 'medium', description: 'IP allowlist configuration updated' },
        PASSWORD_POLICY_CHANGED: { module: MODULES.SECURITY, category: 'security', severity: 'high', description: 'Password policy modified' },

        LOGIN_SUCCESS: { module: MODULES.AUTHENTICATION, category: 'authentication', severity: 'low', description: 'User logged in successfully' },
        LOGIN_FAILED: { module: MODULES.AUTHENTICATION, category: 'authentication', severity: 'medium', description: 'Login attempt failed' },
        LOGIN_FAILED_MFA: { module: MODULES.AUTHENTICATION, category: 'authentication', severity: 'medium', description: 'Login failed - MFA verification' },
        LOGIN_BLOCKED: { module: MODULES.AUTHENTICATION, category: 'authentication', severity: 'high', description: 'Login blocked due to security policy' },
        LOGOUT: { module: MODULES.AUTHENTICATION, category: 'authentication', severity: 'low', description: 'User logged out' },
        SESSION_EXPIRED: { module: MODULES.AUTHENTICATION, category: 'authentication', severity: 'low', description: 'User session expired' },
        PASSWORD_CHANGED: { module: MODULES.AUTHENTICATION, category: 'authentication', severity: 'medium', description: 'Password changed' },
        PASSWORD_RESET_REQUESTED: { module: MODULES.AUTHENTICATION, category: 'authentication', severity: 'medium', description: 'Password reset requested' },
        PASSWORD_RESET_FORCED: { module: MODULES.AUTHENTICATION, category: 'authentication', severity: 'high', description: 'Forced password reset initiated' },

        CAMPAIGN_SUBMITTED: { module: MODULES.CAMPAIGNS, category: 'messaging', severity: 'low', description: 'Campaign submitted for approval' },
        CAMPAIGN_APPROVED: { module: MODULES.CAMPAIGNS, category: 'messaging', severity: 'medium', description: 'Campaign approved' },
        CAMPAIGN_REJECTED: { module: MODULES.CAMPAIGNS, category: 'messaging', severity: 'medium', description: 'Campaign rejected' },
        CAMPAIGN_SENT: { module: MODULES.CAMPAIGNS, category: 'messaging', severity: 'low', description: 'Campaign dispatched' },
        CAMPAIGN_SCHEDULED: { module: MODULES.CAMPAIGNS, category: 'messaging', severity: 'low', description: 'Campaign scheduled' },
        CAMPAIGN_CANCELLED: { module: MODULES.CAMPAIGNS, category: 'messaging', severity: 'medium', description: 'Campaign cancelled' },

        TEMPLATE_CREATED: { module: MODULES.MESSAGING, category: 'messaging', severity: 'low', description: 'Message template created' },
        TEMPLATE_UPDATED: { module: MODULES.MESSAGING, category: 'messaging', severity: 'low', description: 'Message template updated' },
        TEMPLATE_DELETED: { module: MODULES.MESSAGING, category: 'messaging', severity: 'medium', description: 'Message template deleted' },
        TEMPLATE_APPROVED: { module: MODULES.MESSAGING, category: 'messaging', severity: 'medium', description: 'Message template approved' },

        CONTACT_IMPORTED: { module: MODULES.CONTACTS, category: 'contacts', severity: 'low', description: 'Contacts imported' },
        CONTACT_EXPORTED: { module: MODULES.CONTACTS, category: 'contacts', severity: 'medium', description: 'Contacts exported' },
        LIST_CREATED: { module: MODULES.CONTACTS, category: 'contacts', severity: 'low', description: 'Contact list created' },
        OPT_OUT_RECEIVED: { module: MODULES.CONTACTS, category: 'contacts', severity: 'medium', description: 'Opt-out request processed' },
        OPT_IN_RECEIVED: { module: MODULES.CONTACTS, category: 'contacts', severity: 'low', description: 'Opt-in consent recorded' },

        DATA_UNMASKED: { module: MODULES.REPORTING, category: 'data_access', severity: 'high', description: 'Sensitive data unmasked' },
        DATA_EXPORTED: { module: MODULES.REPORTING, category: 'data_access', severity: 'medium', description: 'Data exported' },
        REPORT_GENERATED: { module: MODULES.REPORTING, category: 'data_access', severity: 'low', description: 'Report generated' },
        AUDIT_LOG_ACCESSED: { module: MODULES.COMPLIANCE, category: 'data_access', severity: 'low', description: 'Audit logs accessed' },

        PURCHASE_COMPLETED: { module: MODULES.FINANCIAL, category: 'financial', severity: 'medium', description: 'Purchase completed' },
        INVOICE_GENERATED: { module: MODULES.FINANCIAL, category: 'financial', severity: 'low', description: 'Invoice generated' },
        PAYMENT_RECEIVED: { module: MODULES.FINANCIAL, category: 'financial', severity: 'medium', description: 'Payment received' },
        CREDIT_APPLIED: { module: MODULES.FINANCIAL, category: 'financial', severity: 'medium', description: 'Credit applied to account' },
        REFUND_ISSUED: { module: MODULES.FINANCIAL, category: 'financial', severity: 'high', description: 'Refund issued' },

        ACCOUNT_ACTIVATED: { module: MODULES.ACCOUNT, category: 'account', severity: 'high', description: 'Account activated' },
        ACCOUNT_SUSPENDED: { module: MODULES.ACCOUNT, category: 'account', severity: 'critical', description: 'Account suspended' },
        ACCOUNT_DETAILS_UPDATED: { module: MODULES.ACCOUNT, category: 'account', severity: 'medium', description: 'Account details updated' },

        SAR_REQUEST: { module: MODULES.COMPLIANCE, category: 'gdpr', severity: 'high', description: 'Subject access request received' },
        DATA_DELETION: { module: MODULES.COMPLIANCE, category: 'gdpr', severity: 'critical', description: 'Data deletion request processed' },
        CONSENT_UPDATED: { module: MODULES.COMPLIANCE, category: 'gdpr', severity: 'medium', description: 'Consent preferences updated' },
        PROCESSING_RECORD: { module: MODULES.COMPLIANCE, category: 'gdpr', severity: 'low', description: 'Processing activity recorded' },
        SECURITY_INCIDENT: { module: MODULES.COMPLIANCE, category: 'compliance', severity: 'critical', description: 'Security incident reported' },
        POLICY_UPDATED: { module: MODULES.COMPLIANCE, category: 'compliance', severity: 'high', description: 'Security policy updated' },
        ACCESS_REVIEW: { module: MODULES.COMPLIANCE, category: 'compliance', severity: 'medium', description: 'Access review completed' },

        API_KEY_CREATED: { module: MODULES.API, category: 'api', severity: 'high', description: 'API key created' },
        API_KEY_REVOKED: { module: MODULES.API, category: 'api', severity: 'high', description: 'API key revoked' },
        API_REQUEST: { module: MODULES.API, category: 'api', severity: 'low', description: 'API request processed' },
        WEBHOOK_CONFIGURED: { module: MODULES.API, category: 'api', severity: 'medium', description: 'Webhook endpoint configured' },

        SYSTEM_MAINTENANCE: { module: MODULES.SYSTEM, category: 'system', severity: 'low', description: 'System maintenance performed' },
        CONFIG_CHANGED: { module: MODULES.SYSTEM, category: 'system', severity: 'high', description: 'System configuration changed' }
    };

    var SENSITIVE_PATTERNS = [
        { pattern: /password/i, replacement: '[CREDENTIAL_REDACTED]' },
        { pattern: /token/i, replacement: '[TOKEN_REDACTED]' },
        { pattern: /secret/i, replacement: '[SECRET_REDACTED]' },
        { pattern: /apiKey/i, replacement: '[API_KEY_REDACTED]' },
        { pattern: /api_key/i, replacement: '[API_KEY_REDACTED]' },
        { pattern: /creditCard/i, replacement: '[CARD_REDACTED]' },
        { pattern: /cvv/i, replacement: '[CVV_REDACTED]' },
        { pattern: /pin/i, replacement: '[PIN_REDACTED]' }
    ];

    var PHONE_PATTERN = /(\+?\d{1,4}[-.\s]?)?\(?\d{1,4}\)?[-.\s]?\d{1,4}[-.\s]?\d{1,9}/g;
    var EMAIL_PATTERN = /[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/g;

    var auditLog = [];
    var maxLogSize = 10000;

    function generateEventId() {
        var timestamp = Date.now().toString(36);
        var random = Math.random().toString(36).substr(2, 9);
        return 'EVT-' + timestamp.toUpperCase() + '-' + random.toUpperCase();
    }

    function generateRequestId() {
        return 'REQ-' + Math.random().toString(36).substr(2, 9).toUpperCase();
    }

    function getSessionId() {
        if (!window.QUICKSMS_SESSION_ID) {
            window.QUICKSMS_SESSION_ID = 'SESS-' + Math.random().toString(36).substr(2, 12).toUpperCase();
        }
        return window.QUICKSMS_SESSION_ID;
    }

    function getClientIP() {
        return window.QUICKSMS_CLIENT_IP || '0.0.0.0';
    }

    function getCurrentTimestampUTC() {
        return new Date().toISOString();
    }

    function getCurrentActor() {
        var user = window.QUICKSMS_USER || {};
        return {
            actorType: ACTOR_TYPES.USER,
            actorId: user.id || 'unknown',
            actorName: user.name || 'Unknown User',
            actorRole: user.role || 'unknown',
            subAccountId: user.subAccountId || null
        };
    }

    function sanitizeValue(value) {
        if (typeof value !== 'string') {
            return value;
        }

        var sanitized = value;

        sanitized = sanitized.replace(PHONE_PATTERN, '[PHONE_REDACTED]');
        sanitized = sanitized.replace(EMAIL_PATTERN, '[EMAIL_REDACTED]');

        return sanitized;
    }

    function sanitizeDetails(data) {
        if (!data || typeof data !== 'object') {
            return {};
        }

        var sanitized = {};

        for (var key in data) {
            if (!data.hasOwnProperty(key)) continue;

            var value = data[key];
            var isSensitiveKey = false;

            for (var i = 0; i < SENSITIVE_PATTERNS.length; i++) {
                if (SENSITIVE_PATTERNS[i].pattern.test(key)) {
                    sanitized[key] = SENSITIVE_PATTERNS[i].replacement;
                    isSensitiveKey = true;
                    break;
                }
            }

            if (isSensitiveKey) continue;

            var blockedFields = ['messageContent', 'message_content', 'smsBody', 'sms_body', 
                                 'messageBody', 'message_body', 'content', 'body', 'text',
                                 'phoneNumber', 'phone_number', 'mobile', 'msisdn', 'recipient',
                                 'recipients', 'phone', 'telephone', 'cell'];

            if (blockedFields.includes(key)) {
                sanitized[key] = '[CONTENT_NOT_LOGGED]';
                continue;
            }

            if (typeof value === 'object' && value !== null) {
                sanitized[key] = sanitizeDetails(value);
            } else if (typeof value === 'string') {
                sanitized[key] = sanitizeValue(value);
            } else {
                sanitized[key] = value;
            }
        }

        return sanitized;
    }

    function log(eventType, options) {
        options = options || {};

        var eventInfo = EVENT_TYPES[eventType];
        if (!eventInfo) {
            console.warn('[AuditLogger] Unknown event type:', eventType);
            eventInfo = { 
                module: MODULES.SYSTEM, 
                category: 'unknown', 
                severity: 'medium', 
                description: eventType.replace(/_/g, ' ') 
            };
        }

        var actor = options.actor || getCurrentActor();
        var target = options.target || null;

        var entry = {
            eventId: generateEventId(),

            eventType: eventType,

            module: options.module || eventInfo.module,

            actorType: actor.actorType || ACTOR_TYPES.USER,
            actorId: actor.actorId || actor.userId || 'unknown',

            subAccountId: actor.subAccountId || options.subAccountId || null,

            targetRef: target ? {
                entityType: target.resourceType || target.entityType || 'unknown',
                entityId: target.resourceId || target.entityId || target.userId || null
            } : null,

            description: options.description || eventInfo.description,

            timestamp: getCurrentTimestampUTC(),

            ipAddress: options.ipAddress || getClientIP(),

            category: eventInfo.category,
            severity: eventInfo.severity,

            actorName: actor.actorName || actor.userName || null,
            actorRole: actor.actorRole || actor.role || null,

            metadata: sanitizeDetails(options.data || {}),

            sessionId: getSessionId(),
            requestId: options.requestId || generateRequestId(),

            result: options.result || 'success',
            reason: options.reason || null
        };

        auditLog.unshift(entry);

        if (auditLog.length > maxLogSize) {
            auditLog = auditLog.slice(0, maxLogSize);
        }

        console.log('[AUDIT]', formatLogEntry(entry));

        if (eventInfo.severity === 'critical' || eventInfo.severity === 'high') {
            notifySecurityTeam(entry);
        }

        return entry;
    }

    function formatLogEntry(entry) {
        return {
            id: entry.eventId,
            time: entry.timestamp,
            action: entry.description,
            severity: entry.severity,
            actor: entry.actorName + ' (' + entry.actorRole + ')',
            target: entry.targetRef ? entry.targetRef.entityType + ':' + entry.targetRef.entityId : 'N/A',
            ip: entry.ipAddress
        };
    }

    function notifySecurityTeam(entry) {
        console.log('[SECURITY ALERT]', entry.description, '-', entry.severity.toUpperCase());
    }

    function logUserCreated(targetUser, options) {
        options = options || {};
        return log('USER_CREATED', {
            target: { entityType: 'user', entityId: targetUser.userId },
            data: {
                creationType: options.creationType || 'direct',
                requiresPasswordReset: options.requiresPasswordReset !== false,
                requiresMFA: options.requiresMFA !== false,
                assignedSubAccount: options.subAccountId,
                assignedRole: targetUser.role
            },
            reason: options.reason
        });
    }

    function logUserInvited(targetUser, options) {
        options = options || {};
        return log('USER_INVITED', {
            target: { entityType: 'user', entityId: targetUser.userId || 'pending' },
            data: {
                assignedRole: targetUser.role,
                expiresAt: options.expiresAt
            }
        });
    }

    function logRoleChanged(targetUser, previousRole, newRole, reason) {
        return log('ROLE_CHANGED', {
            target: { entityType: 'user', entityId: targetUser.userId },
            data: {
                previousRole: previousRole,
                newRole: newRole
            },
            reason: reason
        });
    }

    function logPermissionChanged(targetUser, permission, granted, isOverride) {
        var eventType = granted ? 'PERMISSION_GRANTED' : 'PERMISSION_REVOKED';
        if (isOverride) {
            eventType = 'PERMISSION_OVERRIDE_SET';
        }

        return log(eventType, {
            target: { entityType: 'user', entityId: targetUser.userId },
            data: {
                permission: permission,
                granted: granted,
                isOverride: isOverride
            }
        });
    }

    function logPermissionsUpdated(targetUser, changes) {
        return log('PERMISSION_OVERRIDE_SET', {
            target: { entityType: 'user', entityId: targetUser.userId },
            data: {
                changesCount: Object.keys(changes).length,
                permissionsModified: Object.keys(changes)
            }
        });
    }

    function logSenderCapabilityChanged(targetUser, previousLevel, newLevel, reason) {
        return log('SENDER_CAPABILITY_CHANGED', {
            target: { entityType: 'user', entityId: targetUser.userId },
            data: {
                previousCapability: previousLevel,
                newCapability: newLevel
            },
            reason: reason
        });
    }

    function logEnforcementOverride(subAccountId, ruleType, action, details) {
        var eventType = 'ENFORCEMENT_OVERRIDE_' + action.toUpperCase();
        return log(eventType, {
            target: { entityType: 'sub_account', entityId: subAccountId },
            subAccountId: subAccountId,
            data: {
                ruleType: ruleType,
                overrideType: action
            },
            reason: details.reason
        });
    }

    function logMFAChange(targetUser, action, details) {
        var eventType = 'MFA_' + action.toUpperCase();
        return log(eventType, {
            target: { entityType: 'user', entityId: targetUser.userId },
            data: details || {}
        });
    }

    function logLoginAttempt(userId, success, failureReason) {
        var eventType = success ? 'LOGIN_SUCCESS' : 'LOGIN_FAILED';

        return log(eventType, {
            actor: {
                actorType: ACTOR_TYPES.USER,
                actorId: userId,
                actorName: userId,
                actorRole: 'authenticating'
            },
            data: {
                success: success
            },
            result: success ? 'success' : 'failure',
            reason: failureReason
        });
    }

    function logLoginBlocked(userId, reason, attempts) {
        return log('LOGIN_BLOCKED', {
            actor: {
                actorType: ACTOR_TYPES.USER,
                actorId: userId,
                actorName: userId,
                actorRole: 'blocked'
            },
            data: {
                failedAttempts: attempts
            },
            result: 'blocked',
            reason: reason
        });
    }

    function logSystemEvent(eventType, details, options) {
        options = options || {};
        return log(eventType, {
            actor: {
                actorType: ACTOR_TYPES.SYSTEM,
                actorId: 'system',
                actorName: 'System',
                actorRole: 'system'
            },
            data: details,
            description: options.description
        });
    }

    function logAPIEvent(eventType, apiKeyId, details, options) {
        options = options || {};
        return log(eventType, {
            actor: {
                actorType: ACTOR_TYPES.API,
                actorId: apiKeyId,
                actorName: 'API Client',
                actorRole: 'api'
            },
            data: details,
            description: options.description
        });
    }

    function query(filters) {
        var results = auditLog;

        if (filters.eventType) {
            results = results.filter(function(e) { return e.eventType === filters.eventType; });
        }

        if (filters.module) {
            results = results.filter(function(e) { return e.module === filters.module; });
        }

        if (filters.category) {
            results = results.filter(function(e) { return e.category === filters.category; });
        }

        if (filters.severity) {
            results = results.filter(function(e) { return e.severity === filters.severity; });
        }

        if (filters.actorId) {
            results = results.filter(function(e) { return e.actorId === filters.actorId; });
        }

        if (filters.actorType) {
            results = results.filter(function(e) { return e.actorType === filters.actorType; });
        }

        if (filters.subAccountId) {
            results = results.filter(function(e) { return e.subAccountId === filters.subAccountId; });
        }

        if (filters.startDate) {
            var start = new Date(filters.startDate);
            results = results.filter(function(e) { return new Date(e.timestamp) >= start; });
        }

        if (filters.endDate) {
            var end = new Date(filters.endDate);
            results = results.filter(function(e) { return new Date(e.timestamp) <= end; });
        }

        return results;
    }

    function getRecentActivity(count) {
        return auditLog.slice(0, count || 50);
    }

    function getSecurityAlerts(hours) {
        var since = new Date(Date.now() - (hours || 24) * 60 * 60 * 1000);
        return auditLog.filter(function(e) {
            return (e.severity === 'high' || e.severity === 'critical') &&
                   new Date(e.timestamp) >= since;
        });
    }

    function exportAuditLog(filters, format) {
        var data = filters ? query(filters) : auditLog;

        log('DATA_EXPORTED', {
            data: {
                exportType: 'audit_log',
                format: format || 'json',
                recordCount: data.length
            }
        });

        if (format === 'csv') {
            return convertToCSV(data);
        }

        return JSON.stringify(data, null, 2);
    }

    function convertToCSV(data) {
        if (data.length === 0) return '';

        var headers = [
            'event_id', 'event_type', 'module', 'actor_type', 'actor_id', 
            'sub_account_id', 'target_ref', 'description', 'timestamp', 
            'ip_address', 'category', 'severity', 'result'
        ];

        var rows = data.map(function(e) {
            return [
                e.eventId,
                e.eventType,
                e.module,
                e.actorType,
                e.actorId,
                e.subAccountId || '',
                e.targetRef ? e.targetRef.entityType + ':' + e.targetRef.entityId : '',
                e.description,
                e.timestamp,
                e.ipAddress,
                e.category,
                e.severity,
                e.result
            ].map(function(v) { return '"' + String(v || '').replace(/"/g, '""') + '"'; }).join(',');
        });

        return headers.join(',') + '\n' + rows.join('\n');
    }

    function getSchema() {
        return {
            eventId: 'string (system-generated, format: EVT-XXXXXX-XXXXXXXXX)',
            eventType: 'string (normalised event type from EVENT_TYPES)',
            module: 'string (source module from MODULES)',
            actorType: 'string (User | System | API)',
            actorId: 'string (user ID, system, or API key ID)',
            subAccountId: 'string | null (sub-account context if applicable)',
            targetRef: 'object | null ({ entityType, entityId })',
            description: 'string (human-readable description)',
            timestamp: 'string (ISO 8601 UTC)',
            ipAddress: 'string (client IP where applicable)',
            category: 'string (event category)',
            severity: 'string (low | medium | high | critical)',
            metadata: 'object (sanitized additional data)',
            sessionId: 'string (session identifier)',
            requestId: 'string (request correlation ID)',
            result: 'string (success | failure | blocked)',
            reason: 'string | null (reason for action if applicable)'
        };
    }

    return {
        log: log,
        logUserCreated: logUserCreated,
        logUserInvited: logUserInvited,
        logRoleChanged: logRoleChanged,
        logPermissionChanged: logPermissionChanged,
        logPermissionsUpdated: logPermissionsUpdated,
        logSenderCapabilityChanged: logSenderCapabilityChanged,
        logEnforcementOverride: logEnforcementOverride,
        logMFAChange: logMFAChange,
        logLoginAttempt: logLoginAttempt,
        logLoginBlocked: logLoginBlocked,
        logSystemEvent: logSystemEvent,
        logAPIEvent: logAPIEvent,
        query: query,
        getRecentActivity: getRecentActivity,
        getSecurityAlerts: getSecurityAlerts,
        exportAuditLog: exportAuditLog,
        getSchema: getSchema,
        EVENT_TYPES: EVENT_TYPES,
        ACTION_TYPES: EVENT_TYPES,
        ACTOR_TYPES: ACTOR_TYPES,
        MODULES: MODULES
    };
})();

if (typeof module !== 'undefined' && module.exports) {
    module.exports = AuditLogger;
}
