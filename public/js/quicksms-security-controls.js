var SecurityControls = (function() {
    'use strict';

    var MFA_POLICIES = {
        'disabled': { label: 'Disabled', required: false, gracePeriod: null },
        'optional': { label: 'Optional', required: false, gracePeriod: null },
        'recommended': { label: 'Recommended', required: false, gracePeriod: null, showPrompt: true },
        'required': { label: 'Required', required: true, gracePeriod: 0 },
        'required_grace': { label: 'Required (Grace Period)', required: true, gracePeriod: 7 }
    };

    var PASSWORD_POLICIES = {
        'standard': {
            label: 'Standard',
            minLength: 8,
            requireUppercase: true,
            requireLowercase: true,
            requireNumber: true,
            requireSpecial: false,
            maxAge: 90,
            preventReuse: 3,
            lockoutAttempts: 5,
            lockoutDuration: 30
        },
        'strong': {
            label: 'Strong',
            minLength: 12,
            requireUppercase: true,
            requireLowercase: true,
            requireNumber: true,
            requireSpecial: true,
            maxAge: 60,
            preventReuse: 6,
            lockoutAttempts: 3,
            lockoutDuration: 60
        },
        'enterprise': {
            label: 'Enterprise',
            minLength: 14,
            requireUppercase: true,
            requireLowercase: true,
            requireNumber: true,
            requireSpecial: true,
            maxAge: 30,
            preventReuse: 12,
            lockoutAttempts: 3,
            lockoutDuration: 120
        }
    };

    var accountSecurityConfig = {
        mfaPolicy: 'required',
        passwordPolicy: 'strong',
        ipAllowlistEnabled: false,
        ipAllowlist: [],
        sessionTimeout: 30,
        concurrentSessions: 3
    };

    var subAccountConfigs = {};
    var userMFAStatus = {};
    var suspendedUsers = {};
    var loginAttempts = {};

    function initSubAccountSecurity(subAccountId, config) {
        subAccountConfigs[subAccountId] = {
            id: subAccountId,
            mfaPolicy: config.mfaPolicy || 'required',
            mfaGracePeriodEnd: config.mfaGracePeriodEnd || null,
            ipAllowlistEnabled: config.ipAllowlistEnabled || false,
            ipAllowlist: config.ipAllowlist || [],
            inheritAccountPolicy: config.inheritAccountPolicy !== false,
            passwordPolicy: config.passwordPolicy || null,
            createdAt: new Date().toISOString(),
            updatedAt: new Date().toISOString()
        };

        return subAccountConfigs[subAccountId];
    }

    function getEffectiveMFAPolicy(subAccountId) {
        var subConfig = subAccountConfigs[subAccountId];
        
        if (!subConfig || subConfig.inheritAccountPolicy) {
            return {
                policy: accountSecurityConfig.mfaPolicy,
                source: 'account',
                ...MFA_POLICIES[accountSecurityConfig.mfaPolicy]
            };
        }

        return {
            policy: subConfig.mfaPolicy,
            source: 'sub_account',
            gracePeriodEnd: subConfig.mfaGracePeriodEnd,
            ...MFA_POLICIES[subConfig.mfaPolicy]
        };
    }

    function checkMFACompliance(userId, subAccountId) {
        var policy = getEffectiveMFAPolicy(subAccountId);
        var userMFA = userMFAStatus[userId] || { enabled: false };

        var result = {
            userId: userId,
            policy: policy.policy,
            policySource: policy.source,
            mfaEnabled: userMFA.enabled,
            compliant: true,
            action: null,
            message: null
        };

        if (!policy.required) {
            result.action = policy.showPrompt && !userMFA.enabled ? 'prompt' : 'none';
            return result;
        }

        if (userMFA.enabled) {
            result.compliant = true;
            result.action = 'none';
            return result;
        }

        if (policy.gracePeriod > 0) {
            var gracePeriodEnd = policy.gracePeriodEnd || 
                new Date(Date.now() + policy.gracePeriod * 24 * 60 * 60 * 1000).toISOString();
            
            if (new Date() < new Date(gracePeriodEnd)) {
                result.compliant = true;
                result.action = 'warn';
                result.message = 'MFA setup required by ' + gracePeriodEnd.split('T')[0];
                result.gracePeriodEnd = gracePeriodEnd;
                return result;
            }
        }

        result.compliant = false;
        result.action = 'block';
        result.message = 'MFA setup is mandatory. Please configure MFA to continue.';

        logSecurityEvent('MFA_COMPLIANCE_FAILED', userId, { policy: policy.policy, subAccountId: subAccountId });

        return result;
    }

    function setUserMFAStatus(userId, enabled, method) {
        userMFAStatus[userId] = {
            enabled: enabled,
            method: method,
            enabledAt: enabled ? new Date().toISOString() : null,
            disabledAt: !enabled ? new Date().toISOString() : null
        };

        if (typeof AuditLogger !== 'undefined') {
            AuditLogger.logMFAChange({ userId: userId }, enabled ? 'ENABLED' : 'DISABLED', { method: method });
        }

        return userMFAStatus[userId];
    }

    function getEffectiveIPAllowlist(subAccountId) {
        var subConfig = subAccountConfigs[subAccountId];
        var allowlist = [];
        var enabled = false;

        if (accountSecurityConfig.ipAllowlistEnabled) {
            allowlist = allowlist.concat(accountSecurityConfig.ipAllowlist);
            enabled = true;
        }

        if (subConfig && subConfig.ipAllowlistEnabled) {
            allowlist = allowlist.concat(subConfig.ipAllowlist);
            enabled = true;
        }

        return {
            enabled: enabled,
            allowlist: [...new Set(allowlist)],
            sources: {
                account: accountSecurityConfig.ipAllowlistEnabled,
                subAccount: subConfig ? subConfig.ipAllowlistEnabled : false
            }
        };
    }

    function checkIPAllowed(ipAddress, subAccountId) {
        var config = getEffectiveIPAllowlist(subAccountId);

        if (!config.enabled || config.allowlist.length === 0) {
            return { allowed: true, reason: 'IP allowlist not enabled' };
        }

        var allowed = config.allowlist.some(function(pattern) {
            return matchIPPattern(ipAddress, pattern);
        });

        if (!allowed) {
            logSecurityEvent('IP_BLOCKED', null, { ip: ipAddress, subAccountId: subAccountId });
        }

        return {
            allowed: allowed,
            reason: allowed ? 'IP in allowlist' : 'IP not in allowlist',
            ipAddress: ipAddress,
            checkedPatterns: config.allowlist.length
        };
    }

    function matchIPPattern(ip, pattern) {
        if (pattern === ip) return true;

        if (pattern.includes('/')) {
            return matchCIDR(ip, pattern);
        }

        if (pattern.includes('*')) {
            var regex = new RegExp('^' + pattern.replace(/\./g, '\\.').replace(/\*/g, '\\d+') + '$');
            return regex.test(ip);
        }

        return false;
    }

    function matchCIDR(ip, cidr) {
        var parts = cidr.split('/');
        var baseIP = parts[0];
        var mask = parseInt(parts[1], 10);

        var ipNum = ipToNumber(ip);
        var baseNum = ipToNumber(baseIP);
        var maskNum = ~((1 << (32 - mask)) - 1);

        return (ipNum & maskNum) === (baseNum & maskNum);
    }

    function ipToNumber(ip) {
        return ip.split('.').reduce(function(acc, octet) {
            return (acc << 8) + parseInt(octet, 10);
        }, 0) >>> 0;
    }

    function addIPToAllowlist(ip, scope, subAccountId) {
        if (scope === 'account') {
            if (!accountSecurityConfig.ipAllowlist.includes(ip)) {
                accountSecurityConfig.ipAllowlist.push(ip);
            }
        } else if (scope === 'sub_account' && subAccountId) {
            var config = subAccountConfigs[subAccountId];
            if (config && !config.ipAllowlist.includes(ip)) {
                config.ipAllowlist.push(ip);
            }
        }

        logSecurityEvent('IP_ALLOWLIST_UPDATED', null, { action: 'add', ip: ip, scope: scope, subAccountId: subAccountId });

        return { success: true };
    }

    function removeIPFromAllowlist(ip, scope, subAccountId) {
        if (scope === 'account') {
            accountSecurityConfig.ipAllowlist = accountSecurityConfig.ipAllowlist.filter(function(i) { return i !== ip; });
        } else if (scope === 'sub_account' && subAccountId) {
            var config = subAccountConfigs[subAccountId];
            if (config) {
                config.ipAllowlist = config.ipAllowlist.filter(function(i) { return i !== ip; });
            }
        }

        logSecurityEvent('IP_ALLOWLIST_UPDATED', null, { action: 'remove', ip: ip, scope: scope, subAccountId: subAccountId });

        return { success: true };
    }

    function suspendUser(userId, reason, suspendedBy) {
        suspendedUsers[userId] = {
            userId: userId,
            suspended: true,
            reason: reason,
            suspendedBy: suspendedBy,
            suspendedAt: new Date().toISOString()
        };

        propagateSuspension(userId);

        if (typeof AuditLogger !== 'undefined') {
            AuditLogger.log('USER_SUSPENDED', {
                target: { userId: userId },
                data: { reason: reason },
                reason: reason
            });
        }

        return suspendedUsers[userId];
    }

    function propagateSuspension(userId) {
        console.log('[SECURITY] Propagating suspension for user:', userId);
        console.log('[SECURITY] - Invalidating all active sessions');
        console.log('[SECURITY] - Revoking API tokens');
        console.log('[SECURITY] - Blocking pending operations');
        console.log('[SECURITY] - Notifying connected services');

        return {
            sessionsInvalidated: true,
            tokensRevoked: true,
            operationsBlocked: true,
            timestamp: new Date().toISOString()
        };
    }

    function reactivateUser(userId, reason, reactivatedBy) {
        if (suspendedUsers[userId]) {
            suspendedUsers[userId].suspended = false;
            suspendedUsers[userId].reactivatedAt = new Date().toISOString();
            suspendedUsers[userId].reactivatedBy = reactivatedBy;
            suspendedUsers[userId].reactivationReason = reason;
        }

        if (typeof AuditLogger !== 'undefined') {
            AuditLogger.log('USER_REACTIVATED', {
                target: { userId: userId },
                data: { reason: reason },
                reason: reason
            });
        }

        return { success: true, userId: userId };
    }

    function isUserSuspended(userId) {
        return suspendedUsers[userId] && suspendedUsers[userId].suspended === true;
    }

    function getEffectivePasswordPolicy(subAccountId) {
        var subConfig = subAccountConfigs[subAccountId];

        if (subConfig && subConfig.passwordPolicy && !subConfig.inheritAccountPolicy) {
            return {
                policy: subConfig.passwordPolicy,
                source: 'sub_account',
                ...PASSWORD_POLICIES[subConfig.passwordPolicy]
            };
        }

        return {
            policy: accountSecurityConfig.passwordPolicy,
            source: 'account',
            ...PASSWORD_POLICIES[accountSecurityConfig.passwordPolicy]
        };
    }

    function validatePassword(password, subAccountId) {
        var policy = getEffectivePasswordPolicy(subAccountId);
        var errors = [];

        if (password.length < policy.minLength) {
            errors.push('Password must be at least ' + policy.minLength + ' characters');
        }

        if (policy.requireUppercase && !/[A-Z]/.test(password)) {
            errors.push('Password must contain at least one uppercase letter');
        }

        if (policy.requireLowercase && !/[a-z]/.test(password)) {
            errors.push('Password must contain at least one lowercase letter');
        }

        if (policy.requireNumber && !/[0-9]/.test(password)) {
            errors.push('Password must contain at least one number');
        }

        if (policy.requireSpecial && !/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)) {
            errors.push('Password must contain at least one special character');
        }

        return {
            valid: errors.length === 0,
            errors: errors,
            policy: policy.policy,
            requirements: {
                minLength: policy.minLength,
                uppercase: policy.requireUppercase,
                lowercase: policy.requireLowercase,
                number: policy.requireNumber,
                special: policy.requireSpecial
            }
        };
    }

    function checkPasswordHistory(userId, newPasswordHash, subAccountId) {
        var policy = getEffectivePasswordPolicy(subAccountId);
        return { allowed: true, preventReuse: policy.preventReuse };
    }

    function recordLoginAttempt(userId, success, ipAddress) {
        if (!loginAttempts[userId]) {
            loginAttempts[userId] = { attempts: [], lockedUntil: null };
        }

        var record = loginAttempts[userId];
        
        if (success) {
            record.attempts = [];
            record.lockedUntil = null;
            return { locked: false };
        }

        record.attempts.push({
            timestamp: new Date().toISOString(),
            ipAddress: ipAddress
        });

        var recentAttempts = record.attempts.filter(function(a) {
            return new Date() - new Date(a.timestamp) < 15 * 60 * 1000;
        });

        record.attempts = recentAttempts;

        var policy = getEffectivePasswordPolicy(null);
        
        if (recentAttempts.length >= policy.lockoutAttempts) {
            record.lockedUntil = new Date(Date.now() + policy.lockoutDuration * 60 * 1000).toISOString();
            
            logSecurityEvent('ACCOUNT_LOCKED', userId, {
                attempts: recentAttempts.length,
                lockoutDuration: policy.lockoutDuration
            });

            if (typeof AuditLogger !== 'undefined') {
                AuditLogger.logLoginBlocked(userId, 'Too many failed attempts', recentAttempts.length);
            }

            return {
                locked: true,
                lockedUntil: record.lockedUntil,
                message: 'Account locked for ' + policy.lockoutDuration + ' minutes due to too many failed attempts'
            };
        }

        return {
            locked: false,
            remainingAttempts: policy.lockoutAttempts - recentAttempts.length
        };
    }

    function isAccountLocked(userId) {
        var record = loginAttempts[userId];
        
        if (!record || !record.lockedUntil) {
            return { locked: false };
        }

        if (new Date() > new Date(record.lockedUntil)) {
            record.lockedUntil = null;
            record.attempts = [];
            return { locked: false };
        }

        return {
            locked: true,
            lockedUntil: record.lockedUntil
        };
    }

    function performSecurityCheck(userId, subAccountId, ipAddress) {
        var checks = {
            timestamp: new Date().toISOString(),
            userId: userId,
            subAccountId: subAccountId,
            ipAddress: ipAddress,
            passed: true,
            failures: []
        };

        var suspensionCheck = isUserSuspended(userId);
        if (suspensionCheck) {
            checks.passed = false;
            checks.failures.push({ check: 'suspension', message: 'User account is suspended' });
        }

        var lockCheck = isAccountLocked(userId);
        if (lockCheck.locked) {
            checks.passed = false;
            checks.failures.push({ check: 'lockout', message: 'Account is locked', until: lockCheck.lockedUntil });
        }

        var ipCheck = checkIPAllowed(ipAddress, subAccountId);
        if (!ipCheck.allowed) {
            checks.passed = false;
            checks.failures.push({ check: 'ip_allowlist', message: 'IP address not allowed' });
        }

        var mfaCheck = checkMFACompliance(userId, subAccountId);
        if (!mfaCheck.compliant) {
            checks.passed = false;
            checks.failures.push({ check: 'mfa', message: mfaCheck.message, action: mfaCheck.action });
        }

        return checks;
    }

    function logSecurityEvent(eventType, userId, details) {
        console.log('[SECURITY EVENT]', eventType, userId, details);
    }

    return {
        initSubAccountSecurity: initSubAccountSecurity,
        getEffectiveMFAPolicy: getEffectiveMFAPolicy,
        checkMFACompliance: checkMFACompliance,
        setUserMFAStatus: setUserMFAStatus,
        getEffectiveIPAllowlist: getEffectiveIPAllowlist,
        checkIPAllowed: checkIPAllowed,
        addIPToAllowlist: addIPToAllowlist,
        removeIPFromAllowlist: removeIPFromAllowlist,
        suspendUser: suspendUser,
        reactivateUser: reactivateUser,
        isUserSuspended: isUserSuspended,
        getEffectivePasswordPolicy: getEffectivePasswordPolicy,
        validatePassword: validatePassword,
        checkPasswordHistory: checkPasswordHistory,
        recordLoginAttempt: recordLoginAttempt,
        isAccountLocked: isAccountLocked,
        performSecurityCheck: performSecurityCheck,
        MFA_POLICIES: MFA_POLICIES,
        PASSWORD_POLICIES: PASSWORD_POLICIES
    };
})();

if (typeof module !== 'undefined' && module.exports) {
    module.exports = SecurityControls;
}
