var ReportingAccess = (function() {
    'use strict';

    var REPORTING_LAYERS = {
        'kpi-dashboard': {
            level: 1,
            label: 'KPI Dashboard',
            description: 'Aggregated metrics and non-sensitive summaries',
            sensitivity: 'low',
            dataTypes: ['delivery_rate', 'volume_metrics', 'cost_summary', 'trend_data'],
            maskingRules: {
                phoneNumbers: false,
                messageContent: false,
                recipientNames: false
            }
        },
        'campaign-analytics': {
            level: 2,
            label: 'Campaign Analytics',
            description: 'Campaign performance and engagement metrics',
            sensitivity: 'medium',
            dataTypes: ['campaign_performance', 'engagement_metrics', 'segment_analysis', 'ab_test_results'],
            maskingRules: {
                phoneNumbers: true,
                messageContent: true,
                recipientNames: true
            }
        },
        'message-logs': {
            level: 3,
            label: 'Message Logs',
            description: 'Individual message records with full details',
            sensitivity: 'high',
            dataTypes: ['message_details', 'delivery_status', 'timestamps', 'sender_info', 'recipient_info', 'message_content'],
            maskingRules: {
                phoneNumbers: true,
                messageContent: true,
                recipientNames: true
            }
        }
    };

    var ROLE_LAYER_ACCESS = {
        'owner': {
            layers: ['kpi-dashboard', 'campaign-analytics', 'message-logs'],
            canUnmask: true,
            seeCostData: true,
            seeMessageContent: true
        },
        'admin': {
            layers: ['kpi-dashboard', 'campaign-analytics', 'message-logs'],
            canUnmask: true,
            seeCostData: true,
            seeMessageContent: true
        },
        'messaging-manager': {
            layers: ['kpi-dashboard', 'campaign-analytics', 'message-logs'],
            canUnmask: false,
            seeCostData: false,
            seeMessageContent: true
        },
        'finance': {
            layers: ['kpi-dashboard'],
            canUnmask: false,
            seeCostData: true,
            seeMessageContent: false
        },
        'developer': {
            layers: ['kpi-dashboard', 'campaign-analytics'],
            canUnmask: false,
            seeCostData: false,
            seeMessageContent: false
        },
        'auditor': {
            layers: ['kpi-dashboard', 'campaign-analytics', 'message-logs'],
            canUnmask: false,
            seeCostData: true,
            seeMessageContent: false
        },
        'campaign-approver': {
            layers: ['kpi-dashboard', 'campaign-analytics'],
            canUnmask: false,
            seeCostData: false,
            seeMessageContent: true
        },
        'security-officer': {
            layers: ['kpi-dashboard'],
            canUnmask: false,
            seeCostData: false,
            seeMessageContent: false
        }
    };

    var userOverrides = {};
    var unmaskSession = {};

    function canAccessLayer(role, layerKey, overrides) {
        var roleAccess = ROLE_LAYER_ACCESS[role];
        if (!roleAccess) {
            return { allowed: false, reason: 'Unknown role' };
        }

        if (overrides && overrides['layer_' + layerKey] !== undefined) {
            return {
                allowed: overrides['layer_' + layerKey],
                reason: overrides['layer_' + layerKey] ? 'Allowed by permission override' : 'Denied by permission override',
                source: 'override'
            };
        }

        var hasAccess = roleAccess.layers.includes(layerKey);
        return {
            allowed: hasAccess,
            reason: hasAccess ? 'Allowed by role "' + role + '"' : 'Role "' + role + '" does not have access to this layer',
            source: 'role'
        };
    }

    function getAccessibleLayers(role, overrides) {
        var roleAccess = ROLE_LAYER_ACCESS[role] || { layers: [] };
        var accessible = [];

        Object.keys(REPORTING_LAYERS).forEach(function(layerKey) {
            var result = canAccessLayer(role, layerKey, overrides);
            if (result.allowed) {
                accessible.push({
                    key: layerKey,
                    ...REPORTING_LAYERS[layerKey],
                    accessSource: result.source
                });
            }
        });

        return accessible;
    }

    function getMaskingRules(role, layerKey) {
        var roleAccess = ROLE_LAYER_ACCESS[role] || {};
        var layer = REPORTING_LAYERS[layerKey];
        
        if (!layer) {
            return { phoneNumbers: true, messageContent: true, recipientNames: true };
        }

        var rules = { ...layer.maskingRules };

        if (role === 'finance') {
            rules.messageContent = true;
            rules.phoneNumbers = true;
            rules.recipientNames = true;
        }

        if (!roleAccess.seeMessageContent) {
            rules.messageContent = true;
        }

        return rules;
    }

    function maskPhoneNumber(phone) {
        if (!phone || phone.length < 6) return '****';
        return phone.substring(0, 3) + '****' + phone.substring(phone.length - 2);
    }

    function maskMessageContent(content, showPreview) {
        if (!content) return '[No content]';
        if (showPreview && content.length > 20) {
            return content.substring(0, 20) + '... [masked]';
        }
        return '[Message content masked]';
    }

    function maskName(name) {
        if (!name) return '****';
        return name.charAt(0) + '***';
    }

    function applyMasking(data, role, layerKey) {
        var rules = getMaskingRules(role, layerKey);
        var masked = JSON.parse(JSON.stringify(data));

        if (Array.isArray(masked)) {
            return masked.map(function(item) {
                return maskRecord(item, rules);
            });
        }

        return maskRecord(masked, rules);
    }

    function maskRecord(record, rules) {
        if (rules.phoneNumbers) {
            if (record.phone) record.phone = maskPhoneNumber(record.phone);
            if (record.phoneNumber) record.phoneNumber = maskPhoneNumber(record.phoneNumber);
            if (record.recipient) record.recipient = maskPhoneNumber(record.recipient);
            if (record.sender) record.sender = maskPhoneNumber(record.sender);
        }

        if (rules.messageContent) {
            if (record.content) record.content = maskMessageContent(record.content);
            if (record.messageContent) record.messageContent = maskMessageContent(record.messageContent);
            if (record.body) record.body = maskMessageContent(record.body);
        }

        if (rules.recipientNames) {
            if (record.recipientName) record.recipientName = maskName(record.recipientName);
            if (record.contactName) record.contactName = maskName(record.contactName);
        }

        return record;
    }

    function canUnmask(role, userId) {
        var roleAccess = ROLE_LAYER_ACCESS[role];
        if (!roleAccess || !roleAccess.canUnmask) {
            return false;
        }
        return true;
    }

    function requestUnmask(userId, role, layerKey, recordId, reason) {
        if (!canUnmask(role, userId)) {
            return {
                success: false,
                error: 'Role does not have unmask privileges'
            };
        }

        var sessionKey = userId + '_' + layerKey + '_' + recordId;
        unmaskSession[sessionKey] = {
            userId: userId,
            role: role,
            layerKey: layerKey,
            recordId: recordId,
            reason: reason,
            grantedAt: new Date().toISOString(),
            expiresAt: new Date(Date.now() + 5 * 60 * 1000).toISOString()
        };

        var auditEntry = {
            action: 'DATA_UNMASKED',
            userId: userId,
            role: role,
            layer: layerKey,
            recordId: recordId,
            reason: reason,
            timestamp: new Date().toISOString(),
            expiresAt: unmaskSession[sessionKey].expiresAt,
            ipAddress: '192.168.1.100'
        };

        console.log('[AUDIT] Data unmasked:', auditEntry);

        return {
            success: true,
            sessionKey: sessionKey,
            expiresAt: unmaskSession[sessionKey].expiresAt
        };
    }

    function isUnmasked(userId, layerKey, recordId) {
        var sessionKey = userId + '_' + layerKey + '_' + recordId;
        var session = unmaskSession[sessionKey];
        
        if (!session) return false;
        
        if (new Date(session.expiresAt) < new Date()) {
            delete unmaskSession[sessionKey];
            return false;
        }
        
        return true;
    }

    function canSeeCostData(role) {
        var roleAccess = ROLE_LAYER_ACCESS[role];
        return roleAccess ? roleAccess.seeCostData : false;
    }

    function canSeeMessageContent(role) {
        var roleAccess = ROLE_LAYER_ACCESS[role];
        return roleAccess ? roleAccess.seeMessageContent : false;
    }

    function getDataVisibility(role, layerKey) {
        var roleAccess = ROLE_LAYER_ACCESS[role] || {};
        var layer = REPORTING_LAYERS[layerKey];
        var rules = getMaskingRules(role, layerKey);

        return {
            layer: layerKey,
            layerLabel: layer ? layer.label : 'Unknown',
            role: role,
            visibility: {
                phoneNumbers: !rules.phoneNumbers,
                messageContent: !rules.messageContent && roleAccess.seeMessageContent,
                recipientNames: !rules.recipientNames,
                costData: roleAccess.seeCostData,
                aggregatedMetrics: true
            },
            canUnmask: roleAccess.canUnmask || false
        };
    }

    function evaluateReportingAccess(context) {
        var result = {
            userId: context.userId,
            role: context.role,
            requestedLayer: context.layer,
            requestedData: context.dataType,
            allowed: false,
            visibility: {},
            decisionPath: []
        };

        result.decisionPath.push({ step: 'START', layer: context.layer });

        var layerAccess = canAccessLayer(context.role, context.layer, context.overrides);
        result.decisionPath.push({ 
            step: 'LAYER_CHECK', 
            layer: context.layer, 
            allowed: layerAccess.allowed,
            reason: layerAccess.reason 
        });

        if (!layerAccess.allowed) {
            result.allowed = false;
            result.reason = layerAccess.reason;
            return result;
        }

        result.allowed = true;
        result.visibility = getDataVisibility(context.role, context.layer);
        result.maskingRules = getMaskingRules(context.role, context.layer);
        result.decisionPath.push({ step: 'GRANTED', visibility: result.visibility });

        return result;
    }

    return {
        canAccessLayer: canAccessLayer,
        getAccessibleLayers: getAccessibleLayers,
        getMaskingRules: getMaskingRules,
        applyMasking: applyMasking,
        maskPhoneNumber: maskPhoneNumber,
        maskMessageContent: maskMessageContent,
        maskName: maskName,
        canUnmask: canUnmask,
        requestUnmask: requestUnmask,
        isUnmasked: isUnmasked,
        canSeeCostData: canSeeCostData,
        canSeeMessageContent: canSeeMessageContent,
        getDataVisibility: getDataVisibility,
        evaluateReportingAccess: evaluateReportingAccess,
        LAYERS: REPORTING_LAYERS,
        ROLE_ACCESS: ROLE_LAYER_ACCESS
    };
})();

if (typeof module !== 'undefined' && module.exports) {
    module.exports = ReportingAccess;
}
