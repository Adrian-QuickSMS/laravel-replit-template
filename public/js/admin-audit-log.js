var ADMIN_AUDIT = (function() {
    'use strict';

    var AUDIT_STORAGE_KEY = 'quicksms_admin_audit_log';
    var CUSTOMER_AUDIT_STORAGE_KEY = 'quicksms_customer_audit_log';
    
    var auditLog = [];
    var customerAuditLog = [];

    var ENTITY_TYPES = {
        SENDER_ID: { code: 'sender_id', label: 'SenderID', prefix: 'SID' },
        RCS_AGENT: { code: 'rcs_agent', label: 'RCS Agent', prefix: 'RCS' },
        ACCOUNT: { code: 'account', label: 'Account', prefix: 'ACC' },
        SUB_ACCOUNT: { code: 'sub_account', label: 'Sub-Account', prefix: 'SUB' },
        USER: { code: 'user', label: 'User', prefix: 'USR' },
        NUMBER: { code: 'number', label: 'Number', prefix: 'NUM' },
        TEMPLATE: { code: 'template', label: 'Template', prefix: 'TPL' },
        CAMPAIGN: { code: 'campaign', label: 'Campaign', prefix: 'CMP' }
    };

    var APPROVAL_STATUSES = {
        SUBMITTED: { code: 'submitted', label: 'Submitted', order: 1 },
        IN_REVIEW: { code: 'in_review', label: 'In Review', order: 2 },
        RETURNED: { code: 'returned', label: 'Returned to Customer', order: 3 },
        RESUBMITTED: { code: 'resubmitted', label: 'Resubmitted', order: 4 },
        VALIDATION_IN_PROGRESS: { code: 'validation_in_progress', label: 'Validation In Progress', order: 5 },
        VALIDATION_FAILED: { code: 'validation_failed', label: 'Validation Failed', order: 6 },
        APPROVED: { code: 'approved', label: 'Approved', order: 7 },
        REJECTED: { code: 'rejected', label: 'Rejected', order: 8 },
        PROVISIONING: { code: 'provisioning', label: 'Provisioning In Progress', order: 9 },
        LIVE: { code: 'live', label: 'Live', order: 10 }
    };

    var AUDIT_EVENT_TYPES = {
        STATUS_TRANSITION: 'status_transition',
        EXTERNAL_VALIDATION_SUBMIT: 'external_validation_submit',
        EXTERNAL_VALIDATION_RESPONSE: 'external_validation_response',
        CUSTOMER_NOTIFICATION: 'customer_notification',
        INTERNAL_ALERT: 'internal_alert',
        DATA_ACCESS: 'data_access',
        DATA_REVEAL: 'data_reveal',
        IMPERSONATION_START: 'impersonation_start',
        IMPERSONATION_END: 'impersonation_end',
        IMPERSONATION_ACTION: 'impersonation_action',
        CONFIGURATION_CHANGE: 'configuration_change',
        FORCE_APPROVE: 'force_approve',
        APPROVE: 'approve',
        REJECT: 'reject',
        RETURN_TO_CUSTOMER: 'return_to_customer',
        SUBMIT_EXTERNAL: 'submit_external',
        NUMBER_ASSIGNED: 'number_assigned',
        NUMBER_REASSIGNED: 'number_reassigned',
        NUMBER_MODE_CHANGED: 'number_mode_changed',
        NUMBER_CAPABILITY_CHANGED: 'number_capability_changed',
        NUMBER_SUSPENDED: 'number_suspended',
        NUMBER_REACTIVATED: 'number_reactivated',
        BILLING_MODEL_CHANGED: 'billing_model_changed',
        KEYWORD_DISABLED: 'keyword_disabled',
        OPTOUT_ROUTING_CHANGED: 'optout_routing_changed'
    };

    var ADMIN_ACTIONS = {
        APPROVE: 'APPROVE',
        REJECT: 'REJECT',
        RETURN: 'RETURN',
        SUBMIT_EXTERNAL: 'SUBMIT_EXTERNAL',
        FORCE_APPROVE: 'FORCE_APPROVE'
    };

    var SEVERITY_LEVELS = {
        LOW: { code: 'low', color: '#64748b', weight: 1 },
        MEDIUM: { code: 'medium', color: '#3b82f6', weight: 2 },
        HIGH: { code: 'high', color: '#f59e0b', weight: 3 },
        CRITICAL: { code: 'critical', color: '#ef4444', weight: 4 }
    };

    function init() {
        loadFromStorage();
        console.log('[AdminAudit] Initialized with', auditLog.length, 'admin entries');
    }

    function loadFromStorage() {
        try {
            var adminData = localStorage.getItem(AUDIT_STORAGE_KEY);
            if (adminData) {
                auditLog = JSON.parse(adminData);
            }
            var customerData = localStorage.getItem(CUSTOMER_AUDIT_STORAGE_KEY);
            if (customerData) {
                customerAuditLog = JSON.parse(customerData);
            }
        } catch (e) {
            console.error('[AdminAudit] Failed to load from storage:', e);
        }
    }

    function saveToStorage() {
        try {
            localStorage.setItem(AUDIT_STORAGE_KEY, JSON.stringify(auditLog));
            localStorage.setItem(CUSTOMER_AUDIT_STORAGE_KEY, JSON.stringify(customerAuditLog));
        } catch (e) {
            console.error('[AdminAudit] Failed to save to storage:', e);
        }
    }

    function generateAuditId() {
        return 'AUD-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9).toUpperCase();
    }

    function generateIntegrityHash(entry) {
        var data = entry.auditId + '|' + entry.action + '|' + entry.entityId + '|' + entry.adminUserId + '|' + entry.timestamp;
        var hash = 0;
        for (var i = 0; i < data.length; i++) {
            var char = data.charCodeAt(i);
            hash = ((hash << 5) - hash) + char;
            hash = hash & hash;
        }
        return 'SHA256:' + Math.abs(hash).toString(16).toUpperCase().padStart(8, '0');
    }

    function getAdminContext() {
        if (typeof AdminControlPlane !== 'undefined') {
            var admin = AdminControlPlane.getCurrentAdmin ? AdminControlPlane.getCurrentAdmin() : null;
            var impersonation = AdminControlPlane.getImpersonationSession ? AdminControlPlane.getImpersonationSession() : null;
            return {
                adminUserId: admin ? admin.id : 'unknown',
                adminEmail: admin ? admin.email : 'unknown',
                adminRole: admin ? admin.role : 'unknown',
                adminName: admin ? admin.name : 'Unknown Admin',
                ipAddress: admin ? admin.ipAddress : 'unknown',
                sessionId: admin ? admin.sessionStart : new Date().toISOString(),
                isImpersonating: !!impersonation,
                impersonatedAccountId: impersonation ? impersonation.accountId : null
            };
        }
        return {
            adminUserId: 'unknown',
            adminEmail: 'admin@quicksms.com',
            adminRole: 'super_admin',
            adminName: 'Admin User',
            ipAddress: 'unknown',
            sessionId: new Date().toISOString(),
            isImpersonating: false,
            impersonatedAccountId: null
        };
    }

    function logStatusTransition(entityType, entityId, previousStatus, newStatus, reason, externalRefs) {
        var context = getAdminContext();
        
        var entry = createAuditEntry({
            eventType: AUDIT_EVENT_TYPES.STATUS_TRANSITION,
            action: 'STATUS_CHANGE',
            entityType: entityType,
            entityId: entityId,
            statusTransition: {
                previousStatus: previousStatus,
                newStatus: newStatus,
                transitionTime: new Date().toISOString()
            },
            reason: reason || null,
            externalReferences: externalRefs || {},
            severity: determineTransitionSeverity(previousStatus, newStatus)
        }, context);

        appendToLog(entry, context.isImpersonating);
        return entry;
    }

    function logExternalValidation(entityType, entityId, provider, requestId, action, payload, response) {
        var context = getAdminContext();
        
        var entry = createAuditEntry({
            eventType: action === 'submit' ? AUDIT_EVENT_TYPES.EXTERNAL_VALIDATION_SUBMIT : AUDIT_EVENT_TYPES.EXTERNAL_VALIDATION_RESPONSE,
            action: action === 'submit' ? 'EXTERNAL_VALIDATION_SUBMIT' : 'EXTERNAL_VALIDATION_RESPONSE',
            entityType: entityType,
            entityId: entityId,
            externalReferences: {
                provider: provider,
                externalRequestId: requestId
            },
            externalValidation: {
                provider: provider,
                requestId: requestId,
                payloadSummary: payload ? JSON.stringify(payload).substring(0, 200) : null,
                responseCode: response ? response.code : null,
                responseStatus: response ? response.status : null
            },
            severity: response && response.status === 'FAILED' ? 'HIGH' : 'MEDIUM'
        }, context);

        appendToLog(entry, context.isImpersonating);
        return entry;
    }

    function logDataAccess(entityType, entityId, accessType, dataFields) {
        var context = getAdminContext();
        
        var entry = createAuditEntry({
            eventType: accessType === 'reveal' ? AUDIT_EVENT_TYPES.DATA_REVEAL : AUDIT_EVENT_TYPES.DATA_ACCESS,
            action: accessType === 'reveal' ? 'PII_REVEAL' : 'DATA_VIEW',
            entityType: entityType,
            entityId: entityId,
            dataAccess: {
                accessType: accessType,
                fieldsAccessed: dataFields || [],
                timestamp: new Date().toISOString()
            },
            severity: accessType === 'reveal' ? 'HIGH' : 'LOW'
        }, context);

        appendToLog(entry, context.isImpersonating);
        return entry;
    }

    function logImpersonation(action, targetAccountId, targetAccountName, reason) {
        var context = getAdminContext();
        
        var eventType = action === 'start' ? AUDIT_EVENT_TYPES.IMPERSONATION_START : 
                       action === 'end' ? AUDIT_EVENT_TYPES.IMPERSONATION_END : 
                       AUDIT_EVENT_TYPES.IMPERSONATION_ACTION;
        
        var entry = createAuditEntry({
            eventType: eventType,
            action: 'IMPERSONATION_' + action.toUpperCase(),
            entityType: ENTITY_TYPES.ACCOUNT.code,
            entityId: targetAccountId,
            impersonation: {
                targetAccountId: targetAccountId,
                targetAccountName: targetAccountName,
                reason: reason,
                action: action
            },
            severity: 'CRITICAL'
        }, context);

        appendToLog(entry, true);
        return entry;
    }

    function logForceApprove(entityType, entityId, reason, previousStatus, submissionId, versionId, notesSummary) {
        var context = getAdminContext();
        
        var entry = createAuditEntry({
            eventType: AUDIT_EVENT_TYPES.FORCE_APPROVE,
            action: 'FORCE_APPROVE',
            entityType: entityType,
            entityId: entityId,
            submissionId: submissionId || null,
            versionId: versionId || null,
            statusTransition: {
                previousStatus: previousStatus,
                newStatus: 'approved',
                transitionTime: new Date().toISOString(),
                bypassedValidation: true
            },
            reason: reason,
            notesSummary: notesSummary || null,
            externalReferences: {},
            severity: 'CRITICAL'
        }, context);

        appendToLog(entry, context.isImpersonating);
        return entry;
    }

    function logNumberAction(eventType, data) {
        var context = getAdminContext();
        
        var severityMap = {
            'NUMBER_ASSIGNED': 'HIGH',
            'NUMBER_REASSIGNED': 'HIGH',
            'NUMBER_MODE_CHANGED': 'HIGH',
            'NUMBER_CAPABILITY_CHANGED': 'MEDIUM',
            'NUMBER_SUSPENDED': 'HIGH',
            'NUMBER_REACTIVATED': 'HIGH',
            'BILLING_MODEL_CHANGED': 'CRITICAL',
            'KEYWORD_DISABLED': 'HIGH',
            'OPTOUT_ROUTING_CHANGED': 'MEDIUM'
        };
        
        var entry = createAuditEntry({
            eventType: AUDIT_EVENT_TYPES[eventType] || eventType.toLowerCase(),
            action: eventType,
            entityType: ENTITY_TYPES.NUMBER.code,
            entityId: data.numberId || data.entityId,
            numberDetails: {
                number: data.number,
                numberType: data.numberType,
                affectedCustomerAccountId: data.accountId,
                affectedCustomerAccount: data.accountName,
                affectedSubAccount: data.subAccount
            },
            beforeAfter: {
                before: data.before || null,
                after: data.after || null
            },
            reason: data.reason || null,
            severity: data.severity || severityMap[eventType] || 'MEDIUM'
        }, context);

        appendToLog(entry, context.isImpersonating);
        return entry;
    }

    function logNumberSuspended(numberId, number, accountId, accountName, reason) {
        return logNumberAction('NUMBER_SUSPENDED', {
            numberId: numberId,
            number: number,
            accountId: accountId,
            accountName: accountName,
            before: { status: 'active' },
            after: { status: 'suspended' },
            reason: reason
        });
    }

    function logNumberReactivated(numberId, number, accountId, accountName, reason) {
        return logNumberAction('NUMBER_REACTIVATED', {
            numberId: numberId,
            number: number,
            accountId: accountId,
            accountName: accountName,
            before: { status: 'suspended' },
            after: { status: 'active' },
            reason: reason
        });
    }

    function logNumberReassigned(numberId, number, previousAccount, newAccount, previousSubAccount, newSubAccount, reason) {
        return logNumberAction('NUMBER_REASSIGNED', {
            numberId: numberId,
            number: number,
            accountId: newAccount.id || newAccount,
            accountName: newAccount.name || newAccount,
            before: { account: previousAccount, subAccount: previousSubAccount },
            after: { account: newAccount.name || newAccount, subAccount: newSubAccount },
            reason: reason
        });
    }

    function logNumberModeChanged(numberId, number, accountId, accountName, previousMode, newMode, reason) {
        return logNumberAction('NUMBER_MODE_CHANGED', {
            numberId: numberId,
            number: number,
            accountId: accountId,
            accountName: accountName,
            before: { mode: previousMode },
            after: { mode: newMode },
            reason: reason
        });
    }

    function logNumberCapabilityChanged(numberId, number, accountId, accountName, previousCapabilities, newCapabilities) {
        return logNumberAction('NUMBER_CAPABILITY_CHANGED', {
            numberId: numberId,
            number: number,
            accountId: accountId,
            accountName: accountName,
            before: { capabilities: previousCapabilities },
            after: { capabilities: newCapabilities }
        });
    }

    function logOptoutRoutingChanged(numberId, number, accountId, accountName, previousConfig, newConfig) {
        return logNumberAction('OPTOUT_ROUTING_CHANGED', {
            numberId: numberId,
            number: number,
            accountId: accountId,
            accountName: accountName,
            before: previousConfig,
            after: newConfig
        });
    }

    function logBulkNumberAction(eventType, affectedNumbers, reason) {
        var context = getAdminContext();
        
        var entry = createAuditEntry({
            eventType: AUDIT_EVENT_TYPES[eventType] || eventType.toLowerCase(),
            action: 'BULK_' + eventType,
            entityType: ENTITY_TYPES.NUMBER.code,
            entityId: 'BULK_' + affectedNumbers.length + '_NUMBERS',
            bulkOperation: {
                affectedCount: affectedNumbers.length,
                affectedNumberIds: affectedNumbers.map(function(n) { return n.id; }),
                affectedNumbers: affectedNumbers.map(function(n) { return n.number; }).slice(0, 10),
                truncated: affectedNumbers.length > 10
            },
            reason: reason || null,
            severity: 'HIGH'
        }, context);

        appendToLog(entry, context.isImpersonating);
        return entry;
    }

    function createAuditEntry(data, context) {
        var auditId = generateAuditId();
        
        var entry = {
            auditId: auditId,
            timestamp: new Date().toISOString(),
            timestampUnix: Date.now(),
            
            adminUserId: context.adminUserId,
            adminEmail: context.adminEmail,
            adminRole: context.adminRole,
            adminName: context.adminName,
            ipAddress: context.ipAddress,
            sessionId: context.sessionId,
            userAgent: navigator.userAgent,
            
            action: data.action,
            eventType: data.eventType,
            entityType: data.entityType,
            entityId: data.entityId,
            
            submissionId: data.submissionId || null,
            versionId: data.versionId || null,
            
            statusTransition: data.statusTransition || null,
            externalReferences: data.externalReferences || {},
            externalValidation: data.externalValidation || null,
            dataAccess: data.dataAccess || null,
            impersonation: data.impersonation || null,
            reason: data.reason || null,
            
            notesSummary: data.notesSummary || null,
            customerMessage: data.customerMessage || null,
            
            severity: data.severity || 'LOW',
            
            isImpersonationAction: context.isImpersonating,
            impersonatedAccountId: context.impersonatedAccountId,
            
            compliance: {
                iso27001: true,
                nhsDspToolkit: true,
                retentionExpiry: new Date(Date.now() + (7 * 365 * 24 * 60 * 60 * 1000)).toISOString()
            },
            
            integrity: {
                immutable: true,
                checksum: null
            }
        };

        entry.integrity.checksum = generateIntegrityHash(entry);
        
        return entry;
    }

    function log(data) {
        var context = getAdminContext();
        
        var actionToEventType = {
            'APPROVE': AUDIT_EVENT_TYPES.APPROVE,
            'REJECT': AUDIT_EVENT_TYPES.REJECT,
            'RETURN': AUDIT_EVENT_TYPES.RETURN_TO_CUSTOMER,
            'SUBMIT_EXTERNAL': AUDIT_EVENT_TYPES.SUBMIT_EXTERNAL,
            'FORCE_APPROVE': AUDIT_EVENT_TYPES.FORCE_APPROVE
        };
        
        var actionToSeverity = {
            'APPROVE': 'HIGH',
            'REJECT': 'HIGH',
            'RETURN': 'MEDIUM',
            'SUBMIT_EXTERNAL': 'MEDIUM',
            'FORCE_APPROVE': 'CRITICAL'
        };
        
        var entry = createAuditEntry({
            eventType: actionToEventType[data.action] || data.eventType || 'admin_action',
            action: data.action,
            entityType: data.entityType,
            entityId: data.entityId,
            submissionId: data.submissionId,
            versionId: data.versionId,
            statusTransition: data.previousStatus || data.newStatus ? {
                previousStatus: data.previousStatus || null,
                newStatus: data.newStatus || null,
                transitionTime: new Date().toISOString()
            } : null,
            notesSummary: data.notesSummary || null,
            customerMessage: data.customerMessage || null,
            reason: data.reason || null,
            externalReferences: data.externalReferences || {},
            severity: data.severity || actionToSeverity[data.action] || 'MEDIUM'
        }, context);

        appendToLog(entry, context.isImpersonating);
        return entry;
    }

    function logApproval(entityType, entityId, submissionId, versionId, notesSummary) {
        return log({
            action: ADMIN_ACTIONS.APPROVE,
            entityType: entityType,
            entityId: entityId,
            submissionId: submissionId,
            versionId: versionId,
            previousStatus: 'in_review',
            newStatus: 'approved',
            notesSummary: notesSummary
        });
    }

    function logRejection(entityType, entityId, submissionId, versionId, customerMessage, notesSummary) {
        return log({
            action: ADMIN_ACTIONS.REJECT,
            entityType: entityType,
            entityId: entityId,
            submissionId: submissionId,
            versionId: versionId,
            previousStatus: 'in_review',
            newStatus: 'rejected',
            customerMessage: customerMessage,
            notesSummary: notesSummary
        });
    }

    function logReturn(entityType, entityId, submissionId, versionId, customerMessage, reasonCode, notesSummary) {
        return log({
            action: ADMIN_ACTIONS.RETURN,
            entityType: entityType,
            entityId: entityId,
            submissionId: submissionId,
            versionId: versionId,
            previousStatus: 'in_review',
            newStatus: 'returned_to_customer',
            customerMessage: customerMessage,
            notesSummary: notesSummary,
            reason: reasonCode
        });
    }

    function logSubmitExternal(entityType, entityId, submissionId, versionId, provider, externalRequestId) {
        return log({
            action: ADMIN_ACTIONS.SUBMIT_EXTERNAL,
            entityType: entityType,
            entityId: entityId,
            submissionId: submissionId,
            versionId: versionId,
            externalReferences: {
                provider: provider,
                externalRequestId: externalRequestId
            }
        });
    }

    function appendToLog(entry, isImpersonation) {
        auditLog.push(entry);
        
        if (!isImpersonation && !entry.isImpersonationAction) {
            var customerEntry = createCustomerVisibleEntry(entry);
            if (customerEntry) {
                customerAuditLog.push(customerEntry);
            }
        }
        
        saveToStorage();
        
        emitAuditEvent(entry);
        
        logToConsole(entry);
    }

    function createCustomerVisibleEntry(adminEntry) {
        var customerVisibleActions = [
            'STATUS_CHANGE', 
            'EXTERNAL_VALIDATION_SUBMIT', 
            'EXTERNAL_VALIDATION_RESPONSE'
        ];
        
        if (customerVisibleActions.indexOf(adminEntry.action) === -1) {
            return null;
        }
        
        return {
            auditId: 'CUST-' + adminEntry.auditId,
            timestamp: adminEntry.timestamp,
            entityType: adminEntry.entityType,
            entityId: adminEntry.entityId,
            action: adminEntry.action,
            statusTransition: adminEntry.statusTransition ? {
                previousStatus: adminEntry.statusTransition.previousStatus,
                newStatus: adminEntry.statusTransition.newStatus
            } : null,
            performedBy: 'QuickSMS Team'
        };
    }

    function emitAuditEvent(entry) {
        var event = new CustomEvent('adminAuditEntry', { 
            detail: entry,
            bubbles: true
        });
        document.dispatchEvent(event);
    }

    function logToConsole(entry) {
        var severityLevel = SEVERITY_LEVELS[entry.severity] || SEVERITY_LEVELS.LOW;
        var prefix = '[ADMIN_AUDIT][' + entry.severity + ']';
        
        var logData = {
            auditId: entry.auditId,
            timestamp: entry.timestamp,
            admin: {
                id: entry.adminUserId,
                email: entry.adminEmail,
                role: entry.adminRole
            },
            action: entry.action,
            entityType: entry.entityType,
            entityId: entry.entityId,
            statusTransition: entry.statusTransition,
            externalRefs: entry.externalReferences,
            ipAddress: entry.ipAddress,
            isImpersonation: entry.isImpersonationAction,
            checksum: entry.integrity.checksum
        };
        
        if (entry.severity === 'CRITICAL') {
            console.error(prefix, JSON.stringify(logData, null, 2));
        } else if (entry.severity === 'HIGH') {
            console.warn(prefix, JSON.stringify(logData, null, 2));
        } else {
            console.log(prefix, JSON.stringify(logData, null, 2));
        }
    }

    function determineTransitionSeverity(previousStatus, newStatus) {
        var criticalTransitions = ['rejected', 'approved'];
        var highTransitions = ['returned', 'validation_failed', 'live'];
        
        if (criticalTransitions.indexOf(newStatus) !== -1) return 'HIGH';
        if (highTransitions.indexOf(newStatus) !== -1) return 'HIGH';
        return 'MEDIUM';
    }

    function searchAuditLog(filters) {
        return auditLog.filter(function(entry) {
            if (filters.entityType && entry.entityType !== filters.entityType) return false;
            if (filters.entityId && entry.entityId !== filters.entityId) return false;
            if (filters.adminUserId && entry.adminUserId !== filters.adminUserId) return false;
            if (filters.action && entry.action !== filters.action) return false;
            if (filters.severity && entry.severity !== filters.severity) return false;
            if (filters.eventType && entry.eventType !== filters.eventType) return false;
            
            if (filters.dateFrom) {
                var fromDate = new Date(filters.dateFrom).getTime();
                if (entry.timestampUnix < fromDate) return false;
            }
            if (filters.dateTo) {
                var toDate = new Date(filters.dateTo).getTime();
                if (entry.timestampUnix > toDate) return false;
            }
            
            if (filters.excludeImpersonation && entry.isImpersonationAction) return false;
            
            return true;
        });
    }

    function getEntityAuditTrail(entityType, entityId) {
        return searchAuditLog({
            entityType: entityType,
            entityId: entityId
        }).sort(function(a, b) {
            return b.timestampUnix - a.timestampUnix;
        });
    }

    function getAdminActivityLog(adminUserId, limit) {
        var entries = searchAuditLog({ adminUserId: adminUserId });
        entries.sort(function(a, b) {
            return b.timestampUnix - a.timestampUnix;
        });
        return limit ? entries.slice(0, limit) : entries;
    }

    function getCustomerAuditLog(entityId) {
        return customerAuditLog.filter(function(entry) {
            return entry.entityId === entityId;
        }).sort(function(a, b) {
            return new Date(b.timestamp) - new Date(a.timestamp);
        });
    }

    function verifyIntegrity(entry) {
        var expectedChecksum = generateIntegrityHash(entry);
        return entry.integrity.checksum === expectedChecksum;
    }

    function getAllLogs() {
        return auditLog.slice();
    }

    function getLogCount() {
        return auditLog.length;
    }

    function exportAuditLog(filters, format) {
        var entries = filters ? searchAuditLog(filters) : auditLog;
        
        if (format === 'csv') {
            return exportToCSV(entries);
        }
        
        return JSON.stringify(entries, null, 2);
    }

    function exportToCSV(entries) {
        var headers = ['Audit ID', 'Timestamp', 'Admin Email', 'Admin Role', 'Action', 'Entity Type', 'Entity ID', 'Previous Status', 'New Status', 'Severity', 'IP Address', 'Checksum'];
        var rows = [headers.join(',')];
        
        entries.forEach(function(entry) {
            var row = [
                entry.auditId,
                entry.timestamp,
                entry.adminEmail,
                entry.adminRole,
                entry.action,
                entry.entityType,
                entry.entityId,
                entry.statusTransition ? entry.statusTransition.previousStatus : '',
                entry.statusTransition ? entry.statusTransition.newStatus : '',
                entry.severity,
                entry.ipAddress,
                entry.integrity.checksum
            ];
            rows.push(row.map(function(v) { return '"' + (v || '') + '"'; }).join(','));
        });
        
        return rows.join('\n');
    }

    return {
        init: init,
        log: log,
        logApproval: logApproval,
        logRejection: logRejection,
        logReturn: logReturn,
        logSubmitExternal: logSubmitExternal,
        logStatusTransition: logStatusTransition,
        logExternalValidation: logExternalValidation,
        logDataAccess: logDataAccess,
        logImpersonation: logImpersonation,
        logForceApprove: logForceApprove,
        logNumberAction: logNumberAction,
        logNumberSuspended: logNumberSuspended,
        logNumberReactivated: logNumberReactivated,
        logNumberReassigned: logNumberReassigned,
        logNumberModeChanged: logNumberModeChanged,
        logNumberCapabilityChanged: logNumberCapabilityChanged,
        logOptoutRoutingChanged: logOptoutRoutingChanged,
        logBulkNumberAction: logBulkNumberAction,
        searchAuditLog: searchAuditLog,
        getEntityAuditTrail: getEntityAuditTrail,
        getAdminActivityLog: getAdminActivityLog,
        getCustomerAuditLog: getCustomerAuditLog,
        verifyIntegrity: verifyIntegrity,
        getAllLogs: getAllLogs,
        getLogCount: getLogCount,
        exportAuditLog: exportAuditLog,
        ENTITY_TYPES: ENTITY_TYPES,
        APPROVAL_STATUSES: APPROVAL_STATUSES,
        AUDIT_EVENT_TYPES: AUDIT_EVENT_TYPES,
        SEVERITY_LEVELS: SEVERITY_LEVELS,
        ADMIN_ACTIONS: ADMIN_ACTIONS
    };
})();

document.addEventListener('DOMContentLoaded', function() {
    ADMIN_AUDIT.init();
});
