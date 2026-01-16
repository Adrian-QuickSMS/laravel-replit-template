var PermissionEvaluator = (function() {
    'use strict';

    var ACCOUNT_SCOPE_RESTRICTIONS = {
        'TEST': {
            blockedPermissions: ['purchase_credits', 'manage_payment_methods'],
            maxRecipients: 10,
            requiresApprovedNumbers: true,
            note: 'TEST mode restricts purchasing and payment features'
        },
        'SUSPENDED': {
            blockedPermissions: ['send_sms', 'send_rcs', 'create_campaigns', 'schedule_messages', 'purchase_credits'],
            note: 'SUSPENDED accounts cannot send messages or make purchases'
        },
        'PENDING_ACTIVATION': {
            blockedPermissions: ['send_sms', 'send_rcs', 'create_campaigns', 'schedule_messages'],
            note: 'Account must be activated before messaging'
        }
    };

    var SENDER_CAPABILITY_BLOCKS = {
        'restricted': {
            blockedPermissions: [
                'send_sms',
                'send_rcs', 
                'create_templates',
                'upload_csv',
                'create_contacts',
                'edit_contacts',
                'delete_contacts'
            ],
            allowedPermissions: ['use_templates', 'view_contacts', 'manage_lists'],
            note: 'Restricted senders can only use templates and predefined lists'
        }
    };

    function evaluate(context) {
        var result = {
            permission: context.permission,
            allowed: false,
            decidedAt: null,
            decisionPath: [],
            finalReason: null
        };

        result.decisionPath.push({
            layer: 'START',
            permission: context.permission,
            timestamp: new Date().toISOString()
        });

        var accountScopeResult = evaluateAccountScope(context, result);
        if (accountScopeResult.decided) {
            return finalize(result, accountScopeResult);
        }

        var roleResult = evaluateRole(context, result);
        if (roleResult.decided) {
            return finalize(result, roleResult);
        }

        var capabilityResult = evaluateSenderCapability(context, result);
        if (capabilityResult.decided) {
            return finalize(result, capabilityResult);
        }

        var toggleResult = evaluatePermissionToggles(context, result);
        return finalize(result, toggleResult);
    }

    function evaluateAccountScope(context, result) {
        var layer = 'ACCOUNT_SCOPE';
        var accountState = context.accountState || 'ACTIVE';
        var restrictions = ACCOUNT_SCOPE_RESTRICTIONS[accountState];

        result.decisionPath.push({
            layer: layer,
            accountState: accountState,
            hasRestrictions: !!restrictions
        });

        if (restrictions && restrictions.blockedPermissions) {
            if (restrictions.blockedPermissions.includes(context.permission)) {
                return {
                    decided: true,
                    allowed: false,
                    layer: layer,
                    reason: 'Blocked by account state: ' + accountState + '. ' + restrictions.note
                };
            }
        }

        return { decided: false };
    }

    function evaluateRole(context, result) {
        var layer = 'ROLE';
        var role = context.role;
        var roleDefaults = getRoleDefaults(role);

        result.decisionPath.push({
            layer: layer,
            role: role,
            defaultValue: roleDefaults[context.permission]
        });

        if (roleDefaults[context.permission] === false) {
            return {
                decided: true,
                allowed: false,
                layer: layer,
                reason: 'Role "' + role + '" does not have this permission by default'
            };
        }

        return { decided: false };
    }

    function evaluateSenderCapability(context, result) {
        var layer = 'SENDER_CAPABILITY';
        var capability = context.senderCapability;
        
        if (!capability) {
            result.decisionPath.push({
                layer: layer,
                capability: 'none',
                skipped: true
            });
            return { decided: false };
        }

        var blocks = SENDER_CAPABILITY_BLOCKS[capability];
        
        result.decisionPath.push({
            layer: layer,
            capability: capability,
            hasBlocks: !!blocks
        });

        if (blocks && blocks.blockedPermissions) {
            if (blocks.blockedPermissions.includes(context.permission)) {
                return {
                    decided: true,
                    allowed: false,
                    layer: layer,
                    reason: 'Blocked by sender capability level: ' + capability + '. ' + blocks.note
                };
            }
        }

        return { decided: false };
    }

    function evaluatePermissionToggles(context, result) {
        var layer = 'PERMISSION_TOGGLES';
        var overrides = context.permissionOverrides || {};
        var hasOverride = overrides[context.permission] !== undefined;
        var overrideValue = overrides[context.permission];

        result.decisionPath.push({
            layer: layer,
            hasOverride: hasOverride,
            overrideValue: hasOverride ? overrideValue : 'N/A'
        });

        if (hasOverride) {
            return {
                decided: true,
                allowed: overrideValue === true,
                layer: layer,
                reason: overrideValue ? 'Explicitly allowed by permission override' : 'Explicitly denied by permission override'
            };
        }

        var roleDefaults = getRoleDefaults(context.role);
        var defaultValue = roleDefaults[context.permission] === true;

        return {
            decided: true,
            allowed: defaultValue,
            layer: 'ROLE_DEFAULT',
            reason: 'Inherited from role "' + context.role + '" default: ' + (defaultValue ? 'allowed' : 'denied')
        };
    }

    function finalize(result, decision) {
        result.allowed = decision.allowed;
        result.decidedAt = decision.layer;
        result.finalReason = decision.reason;
        
        result.decisionPath.push({
            layer: 'FINAL',
            allowed: decision.allowed,
            decidedAt: decision.layer,
            reason: decision.reason,
            timestamp: new Date().toISOString()
        });

        return result;
    }

    function getRoleDefaults(role) {
        if (typeof PermissionManager !== 'undefined' && PermissionManager.getRoleDefaults) {
            return PermissionManager.getRoleDefaults(role) || {};
        }
        
        var defaults = {
            'owner': { send_sms: true, send_rcs: true, create_templates: true, use_templates: true, view_contacts: true, create_contacts: true, edit_contacts: true, delete_contacts: true, manage_lists: true, upload_csv: true, export_contacts: true, create_campaigns: true, approve_campaigns: true, cancel_campaigns: true, view_campaign_reports: true, resend_failed: true, manage_sender_ids: true, manage_numbers: true, manage_api_keys: true, manage_webhooks: true, manage_email_to_sms: true, view_balance: true, purchase_credits: true, view_invoices: true, manage_payment_methods: true, view_spending_reports: true, view_audit_logs: true, manage_users: true, manage_roles: true, force_password_reset: true, manage_mfa_policy: true, access_security_settings: true },
            'admin': { send_sms: true, send_rcs: true, create_templates: true, use_templates: true, view_contacts: true, create_contacts: true, edit_contacts: true, delete_contacts: true, manage_lists: true, upload_csv: true, export_contacts: true, create_campaigns: true, approve_campaigns: true, cancel_campaigns: true, view_campaign_reports: true, resend_failed: true, manage_sender_ids: true, manage_numbers: true, manage_api_keys: true, manage_webhooks: true, manage_email_to_sms: true, view_balance: true, purchase_credits: true, view_invoices: true, manage_payment_methods: false, view_spending_reports: true, view_audit_logs: true, manage_users: true, manage_roles: true, force_password_reset: true, manage_mfa_policy: false, access_security_settings: true },
            'messaging-manager': { send_sms: true, send_rcs: true, create_templates: true, use_templates: true, view_contacts: true, create_contacts: true, edit_contacts: true, delete_contacts: false, manage_lists: true, upload_csv: true, export_contacts: true, create_campaigns: true, approve_campaigns: false, cancel_campaigns: true, view_campaign_reports: true, resend_failed: true, manage_sender_ids: false, manage_numbers: false, manage_api_keys: false, manage_webhooks: false, manage_email_to_sms: false, view_balance: true, purchase_credits: false, view_invoices: false, manage_payment_methods: false, view_spending_reports: false, view_audit_logs: false, manage_users: false, manage_roles: false, force_password_reset: false, manage_mfa_policy: false, access_security_settings: false },
            'finance': { send_sms: false, send_rcs: false, create_templates: false, use_templates: false, view_contacts: false, create_contacts: false, edit_contacts: false, delete_contacts: false, manage_lists: false, upload_csv: false, export_contacts: false, create_campaigns: false, approve_campaigns: false, cancel_campaigns: false, view_campaign_reports: true, resend_failed: false, manage_sender_ids: false, manage_numbers: false, manage_api_keys: false, manage_webhooks: false, manage_email_to_sms: false, view_balance: true, purchase_credits: true, view_invoices: true, manage_payment_methods: true, view_spending_reports: true, view_audit_logs: false, manage_users: false, manage_roles: false, force_password_reset: false, manage_mfa_policy: false, access_security_settings: false },
            'developer': { send_sms: false, send_rcs: false, create_templates: false, use_templates: false, view_contacts: false, create_contacts: false, edit_contacts: false, delete_contacts: false, manage_lists: false, upload_csv: false, export_contacts: false, create_campaigns: false, approve_campaigns: false, cancel_campaigns: false, view_campaign_reports: true, resend_failed: false, manage_sender_ids: false, manage_numbers: false, manage_api_keys: true, manage_webhooks: true, manage_email_to_sms: true, view_balance: false, purchase_credits: false, view_invoices: false, manage_payment_methods: false, view_spending_reports: false, view_audit_logs: true, manage_users: false, manage_roles: false, force_password_reset: false, manage_mfa_policy: false, access_security_settings: false },
            'auditor': { send_sms: false, send_rcs: false, create_templates: false, use_templates: false, view_contacts: true, create_contacts: false, edit_contacts: false, delete_contacts: false, manage_lists: false, upload_csv: false, export_contacts: true, create_campaigns: false, approve_campaigns: false, cancel_campaigns: false, view_campaign_reports: true, resend_failed: false, manage_sender_ids: false, manage_numbers: false, manage_api_keys: false, manage_webhooks: false, manage_email_to_sms: false, view_balance: true, purchase_credits: false, view_invoices: true, manage_payment_methods: false, view_spending_reports: true, view_audit_logs: true, manage_users: false, manage_roles: false, force_password_reset: false, manage_mfa_policy: false, access_security_settings: false }
        };
        return defaults[role] || {};
    }

    function can(context) {
        var result = evaluate(context);
        return result.allowed;
    }

    function canWithAudit(context) {
        var result = evaluate(context);
        
        console.log('[PermissionEvaluator] Decision:', {
            permission: result.permission,
            allowed: result.allowed,
            decidedAt: result.decidedAt,
            reason: result.finalReason,
            path: result.decisionPath.map(function(p) { return p.layer; }).join(' → ')
        });
        
        return result;
    }

    function getDecisionPath(context) {
        var result = evaluate(context);
        return result.decisionPath;
    }

    function explainDecision(context) {
        var result = evaluate(context);
        var explanation = [];
        
        explanation.push('Permission: ' + result.permission);
        explanation.push('Result: ' + (result.allowed ? 'ALLOWED' : 'DENIED'));
        explanation.push('Decided at layer: ' + result.decidedAt);
        explanation.push('Reason: ' + result.finalReason);
        explanation.push('');
        explanation.push('Evaluation path:');
        
        result.decisionPath.forEach(function(step, idx) {
            var prefix = idx === 0 ? '  ▶' : '  →';
            if (step.layer === 'FINAL') {
                prefix = '  ✓';
            }
            explanation.push(prefix + ' ' + step.layer);
        });
        
        return explanation.join('\n');
    }

    function evaluateBatch(contexts) {
        return contexts.map(function(ctx) {
            return evaluate(ctx);
        });
    }

    return {
        evaluate: evaluate,
        can: can,
        canWithAudit: canWithAudit,
        getDecisionPath: getDecisionPath,
        explainDecision: explainDecision,
        evaluateBatch: evaluateBatch,
        ACCOUNT_SCOPE_RESTRICTIONS: ACCOUNT_SCOPE_RESTRICTIONS,
        SENDER_CAPABILITY_BLOCKS: SENDER_CAPABILITY_BLOCKS
    };
})();

if (typeof module !== 'undefined' && module.exports) {
    module.exports = PermissionEvaluator;
}
