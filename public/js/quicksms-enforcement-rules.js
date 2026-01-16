var EnforcementRules = (function() {
    'use strict';

    var RULE_TYPES = {
        'daily_send_limit': {
            label: 'Daily Send Limit',
            description: 'Maximum messages per day',
            unit: 'messages',
            icon: 'fa-calendar-day',
            defaultThreshold: 1000,
            resetPeriod: 'daily'
        },
        'monthly_spend_cap': {
            label: 'Monthly Spend Cap',
            description: 'Maximum spend per month',
            unit: 'GBP',
            icon: 'fa-pound-sign',
            defaultThreshold: 500,
            resetPeriod: 'monthly'
        },
        'campaign_approval': {
            label: 'Campaign Approval Required',
            description: 'Campaigns require admin approval before sending',
            unit: null,
            icon: 'fa-check-double',
            defaultThreshold: null,
            resetPeriod: null
        },
        'recipient_limit': {
            label: 'Recipient Limit Per Campaign',
            description: 'Maximum recipients per single campaign',
            unit: 'recipients',
            icon: 'fa-users',
            defaultThreshold: 5000,
            resetPeriod: null
        }
    };

    var ENFORCEMENT_MODES = {
        'warn': {
            label: 'Warn Only',
            description: 'Alert admins but allow sending',
            blocking: false
        },
        'soft_stop': {
            label: 'Soft Stop',
            description: 'Block sending, admin can override',
            blocking: true,
            overridable: true
        },
        'hard_stop': {
            label: 'Hard Stop',
            description: 'Block sending, no override allowed',
            blocking: true,
            overridable: false
        }
    };

    var subAccountRules = {};
    var usageTracking = {};
    var pendingAlerts = [];

    function initSubAccount(subAccountId, rules) {
        subAccountRules[subAccountId] = {
            id: subAccountId,
            rules: rules || getDefaultRules(),
            createdAt: new Date().toISOString(),
            updatedAt: new Date().toISOString()
        };

        usageTracking[subAccountId] = {
            dailySends: 0,
            dailySendsDate: getTodayKey(),
            monthlySpend: 0,
            monthlySpendMonth: getMonthKey(),
            pendingApprovals: []
        };

        return subAccountRules[subAccountId];
    }

    function getDefaultRules() {
        return {
            daily_send_limit: {
                enabled: true,
                threshold: 1000,
                mode: 'soft_stop',
                warningAt: 80
            },
            monthly_spend_cap: {
                enabled: true,
                threshold: 500,
                mode: 'soft_stop',
                warningAt: 80
            },
            campaign_approval: {
                enabled: false,
                threshold: null,
                mode: 'soft_stop',
                approvers: []
            },
            recipient_limit: {
                enabled: true,
                threshold: 5000,
                mode: 'warn',
                warningAt: null
            }
        };
    }

    function getTodayKey() {
        return new Date().toISOString().split('T')[0];
    }

    function getMonthKey() {
        var d = new Date();
        return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0');
    }

    function getSubAccountRules(subAccountId) {
        return subAccountRules[subAccountId] || null;
    }

    function updateRule(subAccountId, ruleType, config) {
        if (!subAccountRules[subAccountId]) {
            initSubAccount(subAccountId);
        }

        var previousRule = { ...subAccountRules[subAccountId].rules[ruleType] };
        subAccountRules[subAccountId].rules[ruleType] = {
            ...subAccountRules[subAccountId].rules[ruleType],
            ...config
        };
        subAccountRules[subAccountId].updatedAt = new Date().toISOString();

        var auditEntry = {
            action: 'ENFORCEMENT_RULE_UPDATED',
            subAccountId: subAccountId,
            ruleType: ruleType,
            previousConfig: previousRule,
            newConfig: subAccountRules[subAccountId].rules[ruleType],
            timestamp: new Date().toISOString(),
            changedBy: { userId: 'current-user', role: 'admin' }
        };

        console.log('[AUDIT] Enforcement rule updated:', auditEntry);

        return subAccountRules[subAccountId].rules[ruleType];
    }

    function getUsage(subAccountId) {
        var usage = usageTracking[subAccountId];
        if (!usage) {
            return { dailySends: 0, monthlySpend: 0 };
        }

        if (usage.dailySendsDate !== getTodayKey()) {
            usage.dailySends = 0;
            usage.dailySendsDate = getTodayKey();
        }

        if (usage.monthlySpendMonth !== getMonthKey()) {
            usage.monthlySpend = 0;
            usage.monthlySpendMonth = getMonthKey();
        }

        return usage;
    }

    function checkEnforcement(subAccountId, action) {
        var result = {
            allowed: true,
            triggered: [],
            warnings: [],
            blocked: false,
            blockedBy: null,
            overridable: false,
            requiresApproval: false
        };

        var config = subAccountRules[subAccountId];
        if (!config) {
            return result;
        }

        var rules = config.rules;
        var usage = getUsage(subAccountId);

        if (rules.daily_send_limit && rules.daily_send_limit.enabled) {
            var dailyResult = checkDailyLimit(rules.daily_send_limit, usage, action);
            if (dailyResult.triggered) {
                result.triggered.push(dailyResult);
                if (dailyResult.blocking) {
                    result.blocked = true;
                    result.blockedBy = 'daily_send_limit';
                    result.overridable = dailyResult.overridable;
                }
            }
            if (dailyResult.warning) {
                result.warnings.push(dailyResult);
            }
        }

        if (rules.monthly_spend_cap && rules.monthly_spend_cap.enabled) {
            var spendResult = checkMonthlySpend(rules.monthly_spend_cap, usage, action);
            if (spendResult.triggered) {
                result.triggered.push(spendResult);
                if (spendResult.blocking && !result.blocked) {
                    result.blocked = true;
                    result.blockedBy = 'monthly_spend_cap';
                    result.overridable = spendResult.overridable;
                }
            }
            if (spendResult.warning) {
                result.warnings.push(spendResult);
            }
        }

        if (rules.campaign_approval && rules.campaign_approval.enabled && action.type === 'campaign') {
            result.requiresApproval = true;
            result.triggered.push({
                rule: 'campaign_approval',
                message: 'Campaign requires approval before sending'
            });
        }

        if (rules.recipient_limit && rules.recipient_limit.enabled && action.recipientCount) {
            var recipientResult = checkRecipientLimit(rules.recipient_limit, action);
            if (recipientResult.triggered) {
                result.triggered.push(recipientResult);
                if (recipientResult.blocking && !result.blocked) {
                    result.blocked = true;
                    result.blockedBy = 'recipient_limit';
                    result.overridable = recipientResult.overridable;
                }
            }
        }

        result.allowed = !result.blocked;

        if (result.triggered.length > 0 || result.warnings.length > 0) {
            logEnforcementEvent(subAccountId, action, result);
        }

        if (result.blocked || result.warnings.length > 0) {
            queueAlerts(subAccountId, result, action);
        }

        return result;
    }

    function checkDailyLimit(rule, usage, action) {
        var messageCount = action.messageCount || 1;
        var projectedTotal = usage.dailySends + messageCount;
        var threshold = rule.threshold;
        var mode = ENFORCEMENT_MODES[rule.mode];
        var percentUsed = (usage.dailySends / threshold) * 100;

        if (projectedTotal > threshold) {
            return {
                rule: 'daily_send_limit',
                triggered: true,
                blocking: mode.blocking,
                overridable: mode.overridable || false,
                current: usage.dailySends,
                limit: threshold,
                projected: projectedTotal,
                message: 'Daily send limit of ' + threshold + ' messages would be exceeded'
            };
        }

        if (rule.warningAt && percentUsed >= rule.warningAt) {
            return {
                rule: 'daily_send_limit',
                warning: true,
                current: usage.dailySends,
                limit: threshold,
                percentUsed: Math.round(percentUsed),
                message: 'Daily send limit is ' + Math.round(percentUsed) + '% used'
            };
        }

        return { triggered: false };
    }

    function checkMonthlySpend(rule, usage, action) {
        var estimatedCost = action.estimatedCost || 0;
        var projectedTotal = usage.monthlySpend + estimatedCost;
        var threshold = rule.threshold;
        var mode = ENFORCEMENT_MODES[rule.mode];
        var percentUsed = (usage.monthlySpend / threshold) * 100;

        if (projectedTotal > threshold) {
            return {
                rule: 'monthly_spend_cap',
                triggered: true,
                blocking: mode.blocking,
                overridable: mode.overridable || false,
                current: usage.monthlySpend,
                limit: threshold,
                projected: projectedTotal,
                message: 'Monthly spend cap of £' + threshold + ' would be exceeded'
            };
        }

        if (rule.warningAt && percentUsed >= rule.warningAt) {
            return {
                rule: 'monthly_spend_cap',
                warning: true,
                current: usage.monthlySpend,
                limit: threshold,
                percentUsed: Math.round(percentUsed),
                message: 'Monthly spend is ' + Math.round(percentUsed) + '% of cap'
            };
        }

        return { triggered: false };
    }

    function checkRecipientLimit(rule, action) {
        var recipientCount = action.recipientCount || 0;
        var threshold = rule.threshold;
        var mode = ENFORCEMENT_MODES[rule.mode];

        if (recipientCount > threshold) {
            return {
                rule: 'recipient_limit',
                triggered: true,
                blocking: mode.blocking,
                overridable: mode.overridable || false,
                count: recipientCount,
                limit: threshold,
                message: 'Campaign exceeds recipient limit of ' + threshold
            };
        }

        return { triggered: false };
    }

    function logEnforcementEvent(subAccountId, action, result) {
        var auditEntry = {
            action: 'ENFORCEMENT_TRIGGERED',
            subAccountId: subAccountId,
            requestedAction: action,
            result: {
                allowed: result.allowed,
                blocked: result.blocked,
                blockedBy: result.blockedBy,
                overridable: result.overridable,
                triggeredRules: result.triggered.map(function(t) { return t.rule; }),
                warnings: result.warnings.map(function(w) { return w.rule; })
            },
            timestamp: new Date().toISOString(),
            userId: action.userId || 'unknown'
        };

        console.log('[AUDIT] Enforcement event:', auditEntry);

        return auditEntry;
    }

    function queueAlerts(subAccountId, result, action) {
        var alert = {
            id: 'alert-' + Date.now(),
            subAccountId: subAccountId,
            type: result.blocked ? 'enforcement_blocked' : 'enforcement_warning',
            severity: result.blocked ? 'high' : 'medium',
            result: result,
            action: action,
            recipients: ['sub_account_admins', 'main_account_admins'],
            createdAt: new Date().toISOString(),
            sent: false
        };

        pendingAlerts.push(alert);

        console.log('[ALERT] Queued enforcement alert:', alert);

        return alert;
    }

    function sendAlerts() {
        var toSend = pendingAlerts.filter(function(a) { return !a.sent; });

        toSend.forEach(function(alert) {
            console.log('[ALERT] Sending:', alert.type, 'to', alert.recipients.join(', '));
            alert.sent = true;
            alert.sentAt = new Date().toISOString();
        });

        return toSend.length;
    }

    function requestOverride(subAccountId, ruleType, reason, requestedBy) {
        var config = subAccountRules[subAccountId];
        if (!config) {
            return { success: false, error: 'Sub-account not found' };
        }

        var rule = config.rules[ruleType];
        var mode = ENFORCEMENT_MODES[rule.mode];

        if (!mode.overridable) {
            return {
                success: false,
                error: 'Rule is set to Hard Stop and cannot be overridden'
            };
        }

        var override = {
            id: 'override-' + Date.now(),
            subAccountId: subAccountId,
            ruleType: ruleType,
            reason: reason,
            requestedBy: requestedBy,
            requestedAt: new Date().toISOString(),
            status: 'pending',
            expiresAt: new Date(Date.now() + 24 * 60 * 60 * 1000).toISOString()
        };

        var auditEntry = {
            action: 'OVERRIDE_REQUESTED',
            subAccountId: subAccountId,
            ruleType: ruleType,
            reason: reason,
            requestedBy: requestedBy,
            timestamp: new Date().toISOString()
        };

        console.log('[AUDIT] Override requested:', auditEntry);

        return { success: true, override: override };
    }

    function approveOverride(overrideId, approvedBy, notes) {
        var auditEntry = {
            action: 'OVERRIDE_APPROVED',
            overrideId: overrideId,
            approvedBy: approvedBy,
            notes: notes,
            timestamp: new Date().toISOString()
        };

        console.log('[AUDIT] Override approved:', auditEntry);

        return { success: true, status: 'approved' };
    }

    function recordUsage(subAccountId, messageCount, cost) {
        var usage = getUsage(subAccountId);

        usage.dailySends += messageCount;
        usage.monthlySpend += cost;

        console.log('[USAGE] Recorded:', {
            subAccountId: subAccountId,
            messageCount: messageCount,
            cost: cost,
            newDailyTotal: usage.dailySends,
            newMonthlyTotal: usage.monthlySpend
        });

        return usage;
    }

    function getEnforcementSummary(subAccountId) {
        var config = subAccountRules[subAccountId];
        var usage = getUsage(subAccountId);

        if (!config) {
            return null;
        }

        var rules = config.rules;
        var summary = {
            subAccountId: subAccountId,
            rules: [],
            usage: usage
        };

        Object.keys(rules).forEach(function(ruleType) {
            var rule = rules[ruleType];
            var ruleInfo = RULE_TYPES[ruleType];
            var usageValue = null;
            var percentUsed = null;

            if (ruleType === 'daily_send_limit') {
                usageValue = usage.dailySends;
                percentUsed = rule.threshold ? Math.round((usageValue / rule.threshold) * 100) : 0;
            } else if (ruleType === 'monthly_spend_cap') {
                usageValue = usage.monthlySpend;
                percentUsed = rule.threshold ? Math.round((usageValue / rule.threshold) * 100) : 0;
            }

            summary.rules.push({
                type: ruleType,
                label: ruleInfo.label,
                enabled: rule.enabled,
                threshold: rule.threshold,
                mode: rule.mode,
                modeLabel: ENFORCEMENT_MODES[rule.mode] ? ENFORCEMENT_MODES[rule.mode].label : 'Unknown',
                currentUsage: usageValue,
                percentUsed: percentUsed,
                unit: ruleInfo.unit
            });
        });

        return summary;
    }

    return {
        initSubAccount: initSubAccount,
        getSubAccountRules: getSubAccountRules,
        updateRule: updateRule,
        getUsage: getUsage,
        checkEnforcement: checkEnforcement,
        requestOverride: requestOverride,
        approveOverride: approveOverride,
        recordUsage: recordUsage,
        sendAlerts: sendAlerts,
        getEnforcementSummary: getEnforcementSummary,
        RULE_TYPES: RULE_TYPES,
        ENFORCEMENT_MODES: ENFORCEMENT_MODES
    };
})();

if (typeof module !== 'undefined' && module.exports) {
    module.exports = EnforcementRules;
}
