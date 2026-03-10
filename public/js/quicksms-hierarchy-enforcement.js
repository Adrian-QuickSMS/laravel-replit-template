var HierarchyEnforcement = (function() {
    'use strict';

    var ENFORCEMENT_RULES = {
        SINGLE_SUBACCOUNT: {
            code: 'SINGLE_SUBACCOUNT',
            message: 'Users can only belong to one Sub-Account',
            severity: 'critical'
        },
        NO_SHARED_CREDENTIALS: {
            code: 'NO_SHARED_CREDENTIALS',
            message: 'Credential sharing is prohibited',
            severity: 'critical'
        },
        AUDITED_PERMISSION_CHANGES: {
            code: 'AUDITED_PERMISSION_CHANGES',
            message: 'All permission changes must be logged',
            severity: 'high'
        },
        HIERARCHICAL_DISPLAY: {
            code: 'HIERARCHICAL_DISPLAY',
            message: 'Users must be displayed within hierarchy context',
            severity: 'medium'
        }
    };

    var userSubAccountMap = {};
    var credentialHashes = {};
    var activeSessionsPerUser = {};

    function validateUserSubAccount(userId, targetSubAccountId) {
        var currentSubAccount = userSubAccountMap[userId];

        if (currentSubAccount && currentSubAccount !== targetSubAccountId) {
            var violation = {
                rule: ENFORCEMENT_RULES.SINGLE_SUBACCOUNT,
                userId: userId,
                currentSubAccount: currentSubAccount,
                attemptedSubAccount: targetSubAccountId,
                timestamp: new Date().toISOString(),
                blocked: true
            };

            logViolation(violation);

            return {
                allowed: false,
                error: 'User already belongs to Sub-Account: ' + currentSubAccount,
                violation: violation
            };
        }

        return { allowed: true };
    }

    function assignUserToSubAccount(userId, subAccountId, assignedBy) {
        var validation = validateUserSubAccount(userId, subAccountId);
        
        if (!validation.allowed) {
            return validation;
        }

        var previousSubAccount = userSubAccountMap[userId];
        userSubAccountMap[userId] = subAccountId;

        if (typeof AuditLogger !== 'undefined') {
            AuditLogger.log('USER_ASSIGNED_TO_SUBACCOUNT', {
                target: { userId: userId, subAccountId: subAccountId },
                data: {
                    previousSubAccount: previousSubAccount,
                    newSubAccount: subAccountId
                }
            });
        }

        return {
            allowed: true,
            userId: userId,
            subAccountId: subAccountId,
            previousSubAccount: previousSubAccount
        };
    }

    function moveUserToSubAccount(userId, newSubAccountId, reason, movedBy) {
        var currentSubAccount = userSubAccountMap[userId];

        if (!currentSubAccount) {
            return assignUserToSubAccount(userId, newSubAccountId, movedBy);
        }

        if (!reason || reason.trim().length < 10) {
            return {
                allowed: false,
                error: 'A detailed reason (min 10 characters) is required when moving users between Sub-Accounts'
            };
        }

        var previousSubAccount = userSubAccountMap[userId];
        userSubAccountMap[userId] = newSubAccountId;

        if (typeof AuditLogger !== 'undefined') {
            AuditLogger.log('USER_MOVED_BETWEEN_SUBACCOUNTS', {
                target: { userId: userId },
                data: {
                    fromSubAccount: previousSubAccount,
                    toSubAccount: newSubAccountId,
                    reason: reason
                },
                reason: reason
            });
        }

        return {
            allowed: true,
            userId: userId,
            fromSubAccount: previousSubAccount,
            toSubAccount: newSubAccountId
        };
    }

    function getUserSubAccount(userId) {
        return userSubAccountMap[userId] || null;
    }

    function detectCredentialSharing(userId, credentialHash, sessionInfo) {
        if (!credentialHashes[credentialHash]) {
            credentialHashes[credentialHash] = [];
        }

        var existingUses = credentialHashes[credentialHash];
        var recentUses = existingUses.filter(function(use) {
            return new Date() - new Date(use.timestamp) < 24 * 60 * 60 * 1000;
        });

        var differentUsers = recentUses.filter(function(use) {
            return use.userId !== userId;
        });

        if (differentUsers.length > 0) {
            var violation = {
                rule: ENFORCEMENT_RULES.NO_SHARED_CREDENTIALS,
                userId: userId,
                sharedWith: differentUsers.map(function(u) { return u.userId; }),
                timestamp: new Date().toISOString(),
                blocked: true
            };

            logViolation(violation);

            return {
                detected: true,
                violation: violation,
                action: 'block_and_alert'
            };
        }

        credentialHashes[credentialHash].push({
            userId: userId,
            timestamp: new Date().toISOString(),
            ipAddress: sessionInfo.ipAddress,
            userAgent: sessionInfo.userAgent
        });

        return { detected: false };
    }

    function detectConcurrentSessions(userId, newSessionInfo) {
        if (!activeSessionsPerUser[userId]) {
            activeSessionsPerUser[userId] = [];
        }

        var sessions = activeSessionsPerUser[userId];
        
        var activeSessions = sessions.filter(function(s) {
            return new Date() - new Date(s.lastActivity) < 30 * 60 * 1000;
        });

        var differentLocations = activeSessions.filter(function(s) {
            return s.ipAddress !== newSessionInfo.ipAddress;
        });

        if (differentLocations.length > 0) {
            return {
                suspicious: true,
                reason: 'Concurrent sessions from different locations',
                locations: differentLocations.map(function(s) { return s.ipAddress; }),
                action: 'require_verification'
            };
        }

        activeSessionsPerUser[userId] = activeSessions;
        activeSessionsPerUser[userId].push({
            sessionId: newSessionInfo.sessionId,
            ipAddress: newSessionInfo.ipAddress,
            userAgent: newSessionInfo.userAgent,
            lastActivity: new Date().toISOString()
        });

        return { suspicious: false };
    }

    function enforceAuditedPermissionChange(changeRequest) {
        if (!changeRequest.reason || changeRequest.reason.trim().length === 0) {
            return {
                allowed: false,
                error: 'Permission changes require a reason for audit purposes',
                rule: ENFORCEMENT_RULES.AUDITED_PERMISSION_CHANGES
            };
        }

        if (!changeRequest.changedBy || !changeRequest.changedBy.userId) {
            return {
                allowed: false,
                error: 'Permission changes must identify the actor',
                rule: ENFORCEMENT_RULES.AUDITED_PERMISSION_CHANGES
            };
        }

        var auditRecord = {
            changeId: 'perm-' + Date.now(),
            targetUserId: changeRequest.targetUserId,
            permissionKey: changeRequest.permission,
            previousValue: changeRequest.previousValue,
            newValue: changeRequest.newValue,
            reason: changeRequest.reason,
            changedBy: changeRequest.changedBy,
            timestamp: new Date().toISOString(),
            notificationSent: true
        };

        if (typeof AuditLogger !== 'undefined') {
            AuditLogger.logPermissionChanged(
                { userId: changeRequest.targetUserId },
                changeRequest.permission,
                changeRequest.newValue,
                true
            );
        }

        notifyPermissionChange(auditRecord);

        return {
            allowed: true,
            auditRecord: auditRecord
        };
    }

    function notifyPermissionChange(auditRecord) {
        console.log('[NOTIFICATION] Permission change notification:', {
            to: auditRecord.targetUserId,
            change: auditRecord.permissionKey,
            newValue: auditRecord.newValue,
            changedBy: auditRecord.changedBy.userId,
            timestamp: auditRecord.timestamp
        });

        return {
            notified: true,
            recipients: [auditRecord.targetUserId],
            method: 'in_app_and_email'
        };
    }

    function validateHierarchicalContext(userListRequest) {
        if (userListRequest.format === 'flat' || !userListRequest.includeHierarchy) {
            var violation = {
                rule: ENFORCEMENT_RULES.HIERARCHICAL_DISPLAY,
                requestType: userListRequest.type,
                timestamp: new Date().toISOString(),
                corrected: true
            };

            console.log('[ENFORCEMENT] Flat user list requested, enforcing hierarchy:', violation);

            return {
                enforced: true,
                originalFormat: userListRequest.format,
                enforcedFormat: 'hierarchical',
                message: 'User lists must display hierarchical context'
            };
        }

        return { enforced: false, format: userListRequest.format };
    }

    function formatUsersWithHierarchy(users, subAccounts) {
        var hierarchy = {
            mainAccount: {
                users: [],
                subAccounts: {}
            }
        };

        subAccounts.forEach(function(sa) {
            hierarchy.mainAccount.subAccounts[sa.id] = {
                id: sa.id,
                name: sa.name,
                users: []
            };
        });

        users.forEach(function(user) {
            var subAccountId = userSubAccountMap[user.id];
            
            if (subAccountId && hierarchy.mainAccount.subAccounts[subAccountId]) {
                hierarchy.mainAccount.subAccounts[subAccountId].users.push({
                    ...user,
                    hierarchyPath: ['Main Account', hierarchy.mainAccount.subAccounts[subAccountId].name, user.name]
                });
            } else {
                hierarchy.mainAccount.users.push({
                    ...user,
                    hierarchyPath: ['Main Account', user.name]
                });
            }
        });

        return hierarchy;
    }

    function preventFlatUserList(users) {
        if (!Array.isArray(users)) {
            return users;
        }

        return users.map(function(user) {
            var subAccountId = userSubAccountMap[user.id];
            return {
                ...user,
                subAccountId: subAccountId,
                subAccountName: subAccountId ? getSubAccountName(subAccountId) : 'Main Account',
                hierarchyLevel: subAccountId ? 'sub_account_user' : 'main_account_user',
                displayContext: {
                    showInHierarchy: true,
                    parentContext: subAccountId || 'main',
                    flatDisplayProhibited: true
                }
            };
        });
    }

    function getSubAccountName(subAccountId) {
        return 'Sub-Account ' + subAccountId;
    }

    function logViolation(violation) {
        console.error('[SECURITY VIOLATION]', violation.rule.code, '-', violation.rule.message, violation);

        if (typeof AuditLogger !== 'undefined') {
            AuditLogger.log('SECURITY_VIOLATION', {
                data: {
                    ruleCode: violation.rule.code,
                    severity: violation.rule.severity,
                    details: violation
                }
            });
        }

        if (violation.rule.severity === 'critical') {
            alertSecurityTeam(violation);
        }
    }

    function alertSecurityTeam(violation) {
        console.log('[SECURITY ALERT] Critical violation detected:', violation.rule.code);
    }

    function getEnforcementStatus() {
        return {
            rules: Object.keys(ENFORCEMENT_RULES).map(function(key) {
                return {
                    code: ENFORCEMENT_RULES[key].code,
                    message: ENFORCEMENT_RULES[key].message,
                    severity: ENFORCEMENT_RULES[key].severity,
                    active: true
                };
            }),
            statistics: {
                usersTracked: Object.keys(userSubAccountMap).length,
                activeSessions: Object.keys(activeSessionsPerUser).reduce(function(sum, key) {
                    return sum + activeSessionsPerUser[key].length;
                }, 0)
            }
        };
    }

    return {
        validateUserSubAccount: validateUserSubAccount,
        assignUserToSubAccount: assignUserToSubAccount,
        moveUserToSubAccount: moveUserToSubAccount,
        getUserSubAccount: getUserSubAccount,
        detectCredentialSharing: detectCredentialSharing,
        detectConcurrentSessions: detectConcurrentSessions,
        enforceAuditedPermissionChange: enforceAuditedPermissionChange,
        validateHierarchicalContext: validateHierarchicalContext,
        formatUsersWithHierarchy: formatUsersWithHierarchy,
        preventFlatUserList: preventFlatUserList,
        getEnforcementStatus: getEnforcementStatus,
        ENFORCEMENT_RULES: ENFORCEMENT_RULES
    };
})();

if (typeof module !== 'undefined' && module.exports) {
    module.exports = HierarchyEnforcement;
}
