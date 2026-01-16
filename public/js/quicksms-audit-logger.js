var AuditLogger = (function() {
    'use strict';

    var ACTION_TYPES = {
        USER_CREATED: { category: 'user_management', severity: 'high', label: 'User Created' },
        USER_INVITED: { category: 'user_management', severity: 'medium', label: 'User Invited' },
        USER_SUSPENDED: { category: 'user_management', severity: 'high', label: 'User Suspended' },
        USER_REACTIVATED: { category: 'user_management', severity: 'high', label: 'User Reactivated' },
        USER_DELETED: { category: 'user_management', severity: 'critical', label: 'User Deleted' },
        
        ROLE_CHANGED: { category: 'access_control', severity: 'high', label: 'Role Changed' },
        ROLE_ASSIGNED: { category: 'access_control', severity: 'high', label: 'Role Assigned' },
        
        PERMISSION_GRANTED: { category: 'access_control', severity: 'medium', label: 'Permission Granted' },
        PERMISSION_REVOKED: { category: 'access_control', severity: 'medium', label: 'Permission Revoked' },
        PERMISSION_OVERRIDE_SET: { category: 'access_control', severity: 'medium', label: 'Permission Override Set' },
        PERMISSION_OVERRIDE_REMOVED: { category: 'access_control', severity: 'medium', label: 'Permission Override Removed' },
        PERMISSIONS_RESET: { category: 'access_control', severity: 'high', label: 'Permissions Reset to Default' },
        
        SENDER_CAPABILITY_CHANGED: { category: 'access_control', severity: 'medium', label: 'Sender Capability Changed' },
        
        ENFORCEMENT_OVERRIDE_REQUESTED: { category: 'enforcement', severity: 'high', label: 'Enforcement Override Requested' },
        ENFORCEMENT_OVERRIDE_APPROVED: { category: 'enforcement', severity: 'high', label: 'Enforcement Override Approved' },
        ENFORCEMENT_OVERRIDE_DENIED: { category: 'enforcement', severity: 'medium', label: 'Enforcement Override Denied' },
        ENFORCEMENT_RULE_CHANGED: { category: 'enforcement', severity: 'medium', label: 'Enforcement Rule Changed' },
        ENFORCEMENT_TRIGGERED: { category: 'enforcement', severity: 'medium', label: 'Enforcement Rule Triggered' },
        
        MFA_ENABLED: { category: 'security', severity: 'medium', label: 'MFA Enabled' },
        MFA_DISABLED: { category: 'security', severity: 'high', label: 'MFA Disabled' },
        MFA_RESET: { category: 'security', severity: 'high', label: 'MFA Reset' },
        MFA_RECOVERY_USED: { category: 'security', severity: 'high', label: 'MFA Recovery Code Used' },
        
        LOGIN_SUCCESS: { category: 'authentication', severity: 'low', label: 'Login Successful' },
        LOGIN_FAILED: { category: 'authentication', severity: 'medium', label: 'Login Failed' },
        LOGIN_FAILED_MFA: { category: 'authentication', severity: 'medium', label: 'Login Failed - MFA' },
        LOGIN_BLOCKED: { category: 'authentication', severity: 'high', label: 'Login Blocked' },
        LOGOUT: { category: 'authentication', severity: 'low', label: 'Logout' },
        PASSWORD_CHANGED: { category: 'authentication', severity: 'medium', label: 'Password Changed' },
        PASSWORD_RESET_REQUESTED: { category: 'authentication', severity: 'medium', label: 'Password Reset Requested' },
        PASSWORD_RESET_FORCED: { category: 'authentication', severity: 'high', label: 'Password Reset Forced' },
        
        DATA_UNMASKED: { category: 'data_access', severity: 'high', label: 'Sensitive Data Unmasked' },
        DATA_EXPORTED: { category: 'data_access', severity: 'medium', label: 'Data Exported' },
        
        ACCOUNT_ACTIVATED: { category: 'account', severity: 'high', label: 'Account Activated' },
        ACCOUNT_SUSPENDED: { category: 'account', severity: 'critical', label: 'Account Suspended' }
    };

    var auditLog = [];
    var maxLogSize = 10000;

    function getClientIP() {
        return '192.168.1.100';
    }

    function getCurrentUser() {
        return {
            userId: window.QUICKSMS_USER?.id || 'unknown',
            userName: window.QUICKSMS_USER?.name || 'Unknown User',
            role: window.QUICKSMS_USER?.role || 'unknown',
            senderCapability: window.QUICKSMS_USER?.senderCapability || null,
            subAccountId: window.QUICKSMS_USER?.subAccountId || null
        };
    }

    function log(actionType, details) {
        var actionInfo = ACTION_TYPES[actionType];
        if (!actionInfo) {
            console.warn('[AuditLogger] Unknown action type:', actionType);
            actionInfo = { category: 'unknown', severity: 'medium', label: actionType };
        }

        var actor = details.actor || getCurrentUser();
        var target = details.target || null;

        var entry = {
            id: generateAuditId(),
            timestamp: new Date().toISOString(),
            action: actionType,
            actionLabel: actionInfo.label,
            category: actionInfo.category,
            severity: actionInfo.severity,
            
            actor: {
                userId: actor.userId,
                userName: actor.userName,
                role: actor.role,
                senderCapability: actor.senderCapability,
                subAccountId: actor.subAccountId
            },
            
            target: target ? {
                userId: target.userId,
                userName: target.userName,
                role: target.role,
                senderCapability: target.senderCapability,
                subAccountId: target.subAccountId,
                resourceType: target.resourceType,
                resourceId: target.resourceId
            } : null,
            
            details: sanitizeDetails(details.data || {}),
            
            context: {
                ipAddress: details.ipAddress || getClientIP(),
                userAgent: navigator.userAgent,
                sessionId: getSessionId(),
                requestId: generateRequestId()
            },
            
            result: details.result || 'success',
            reason: details.reason || null
        };

        auditLog.unshift(entry);

        if (auditLog.length > maxLogSize) {
            auditLog = auditLog.slice(0, maxLogSize);
        }

        console.log('[AUDIT]', formatLogEntry(entry));

        if (actionInfo.severity === 'critical' || actionInfo.severity === 'high') {
            notifySecurityTeam(entry);
        }

        return entry;
    }

    function generateAuditId() {
        return 'audit-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9);
    }

    function generateRequestId() {
        return 'req-' + Math.random().toString(36).substr(2, 9);
    }

    function getSessionId() {
        if (!window.QUICKSMS_SESSION_ID) {
            window.QUICKSMS_SESSION_ID = 'sess-' + Math.random().toString(36).substr(2, 12);
        }
        return window.QUICKSMS_SESSION_ID;
    }

    function sanitizeDetails(data) {
        var sanitized = { ...data };
        
        var sensitiveKeys = ['password', 'token', 'secret', 'apiKey', 'creditCard'];
        sensitiveKeys.forEach(function(key) {
            if (sanitized[key]) {
                sanitized[key] = '[REDACTED]';
            }
        });

        return sanitized;
    }

    function formatLogEntry(entry) {
        return {
            id: entry.id,
            time: entry.timestamp,
            action: entry.actionLabel,
            severity: entry.severity,
            actor: entry.actor.userName + ' (' + entry.actor.role + ')',
            target: entry.target ? entry.target.userName || entry.target.resourceId : 'N/A',
            ip: entry.context.ipAddress
        };
    }

    function notifySecurityTeam(entry) {
        console.log('[SECURITY ALERT]', entry.actionLabel, '-', entry.severity.toUpperCase());
    }

    function logUserCreated(targetUser, options) {
        return log('USER_CREATED', {
            target: targetUser,
            data: {
                creationType: options.creationType || 'direct',
                requiresPasswordReset: options.requiresPasswordReset || true,
                requiresMFA: options.requiresMFA || true,
                assignedSubAccount: options.subAccountId
            },
            reason: options.reason
        });
    }

    function logUserInvited(targetUser, options) {
        return log('USER_INVITED', {
            target: targetUser,
            data: {
                inviteEmail: targetUser.email,
                assignedRole: targetUser.role,
                expiresAt: options.expiresAt
            }
        });
    }

    function logRoleChanged(targetUser, previousRole, newRole, reason) {
        return log('ROLE_CHANGED', {
            target: targetUser,
            data: {
                previousRole: previousRole,
                newRole: newRole
            },
            reason: reason
        });
    }

    function logPermissionChanged(targetUser, permission, granted, isOverride) {
        var actionType = granted ? 'PERMISSION_GRANTED' : 'PERMISSION_REVOKED';
        if (isOverride) {
            actionType = granted ? 'PERMISSION_OVERRIDE_SET' : 'PERMISSION_OVERRIDE_SET';
        }

        return log(actionType, {
            target: targetUser,
            data: {
                permission: permission,
                granted: granted,
                isOverride: isOverride
            }
        });
    }

    function logPermissionsUpdated(targetUser, changes) {
        return log('PERMISSION_OVERRIDE_SET', {
            target: targetUser,
            data: {
                changesCount: Object.keys(changes).length,
                changes: changes
            }
        });
    }

    function logSenderCapabilityChanged(targetUser, previousLevel, newLevel, reason) {
        return log('SENDER_CAPABILITY_CHANGED', {
            target: targetUser,
            data: {
                previousCapability: previousLevel,
                newCapability: newLevel
            },
            reason: reason
        });
    }

    function logEnforcementOverride(subAccountId, ruleType, action, details) {
        var actionType = 'ENFORCEMENT_OVERRIDE_' + action.toUpperCase();
        return log(actionType, {
            target: { resourceType: 'sub_account', resourceId: subAccountId },
            data: {
                ruleType: ruleType,
                ...details
            },
            reason: details.reason
        });
    }

    function logMFAChange(targetUser, action, details) {
        var actionType = 'MFA_' + action.toUpperCase();
        return log(actionType, {
            target: targetUser,
            data: details || {}
        });
    }

    function logLoginAttempt(userId, success, failureReason) {
        var actionType = success ? 'LOGIN_SUCCESS' : 'LOGIN_FAILED';
        
        return log(actionType, {
            actor: { userId: userId, userName: userId, role: 'unknown', senderCapability: null },
            data: {
                success: success,
                failureReason: failureReason
            },
            result: success ? 'success' : 'failure'
        });
    }

    function logLoginBlocked(userId, reason, attempts) {
        return log('LOGIN_BLOCKED', {
            actor: { userId: userId, userName: userId, role: 'unknown', senderCapability: null },
            data: {
                reason: reason,
                failedAttempts: attempts
            },
            result: 'blocked'
        });
    }

    function query(filters) {
        var results = auditLog;

        if (filters.actionType) {
            results = results.filter(function(e) { return e.action === filters.actionType; });
        }

        if (filters.category) {
            results = results.filter(function(e) { return e.category === filters.category; });
        }

        if (filters.severity) {
            results = results.filter(function(e) { return e.severity === filters.severity; });
        }

        if (filters.actorId) {
            results = results.filter(function(e) { return e.actor.userId === filters.actorId; });
        }

        if (filters.targetId) {
            results = results.filter(function(e) { return e.target && e.target.userId === filters.targetId; });
        }

        if (filters.startDate) {
            var start = new Date(filters.startDate);
            results = results.filter(function(e) { return new Date(e.timestamp) >= start; });
        }

        if (filters.endDate) {
            var end = new Date(filters.endDate);
            results = results.filter(function(e) { return new Date(e.timestamp) <= end; });
        }

        if (filters.subAccountId) {
            results = results.filter(function(e) {
                return (e.actor.subAccountId === filters.subAccountId) ||
                       (e.target && e.target.subAccountId === filters.subAccountId);
            });
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
                recordCount: data.length,
                filters: filters
            }
        });

        if (format === 'csv') {
            return convertToCSV(data);
        }

        return JSON.stringify(data, null, 2);
    }

    function convertToCSV(data) {
        if (data.length === 0) return '';

        var headers = ['id', 'timestamp', 'action', 'severity', 'actor_id', 'actor_name', 'actor_role', 'target_id', 'ip_address', 'result'];
        var rows = data.map(function(e) {
            return [
                e.id,
                e.timestamp,
                e.action,
                e.severity,
                e.actor.userId,
                e.actor.userName,
                e.actor.role,
                e.target ? e.target.userId || e.target.resourceId : '',
                e.context.ipAddress,
                e.result
            ].map(function(v) { return '"' + String(v || '').replace(/"/g, '""') + '"'; }).join(',');
        });

        return headers.join(',') + '\n' + rows.join('\n');
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
        query: query,
        getRecentActivity: getRecentActivity,
        getSecurityAlerts: getSecurityAlerts,
        exportAuditLog: exportAuditLog,
        ACTION_TYPES: ACTION_TYPES
    };
})();

if (typeof module !== 'undefined' && module.exports) {
    module.exports = AuditLogger;
}
