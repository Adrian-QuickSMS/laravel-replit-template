var AuditLogger = (function() {
    'use strict';

    var ACTOR_TYPES = Object.freeze({
        USER: 'user',
        SYSTEM: 'system',
        API: 'api'
    });

    var MODULES = Object.freeze({
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
    });

    var CATEGORIES = Object.freeze({
        USER_MANAGEMENT: 'user_management',
        ACCESS_CONTROL: 'access_control',
        ACCOUNT: 'account',
        ENFORCEMENT: 'enforcement',
        SECURITY: 'security',
        AUTHENTICATION: 'authentication',
        MESSAGING: 'messaging',
        CONTACTS: 'contacts',
        DATA_ACCESS: 'data_access',
        FINANCIAL: 'financial',
        GDPR: 'gdpr',
        COMPLIANCE: 'compliance',
        API: 'api',
        SYSTEM: 'system'
    });

    var SEVERITIES = Object.freeze({
        LOW: 'low',
        MEDIUM: 'medium',
        HIGH: 'high',
        CRITICAL: 'critical'
    });

    var EVENT_CATALOGUE = Object.freeze({

        USER_CREATED: Object.freeze({
            code: 'USER_CREATED',
            module: MODULES.USERS,
            category: CATEGORIES.USER_MANAGEMENT,
            severity: SEVERITIES.HIGH,
            description: 'New user account created',
            requiredFields: ['targetUserId', 'assignedRole']
        }),
        USER_INVITED: Object.freeze({
            code: 'USER_INVITED',
            module: MODULES.USERS,
            category: CATEGORIES.USER_MANAGEMENT,
            severity: SEVERITIES.MEDIUM,
            description: 'User invitation sent',
            requiredFields: ['inviteeEmail', 'assignedRole']
        }),
        USER_SUSPENDED: Object.freeze({
            code: 'USER_SUSPENDED',
            module: MODULES.USERS,
            category: CATEGORIES.USER_MANAGEMENT,
            severity: SEVERITIES.HIGH,
            description: 'User account suspended',
            requiredFields: ['targetUserId']
        }),
        USER_REACTIVATED: Object.freeze({
            code: 'USER_REACTIVATED',
            module: MODULES.USERS,
            category: CATEGORIES.USER_MANAGEMENT,
            severity: SEVERITIES.HIGH,
            description: 'User account reactivated',
            requiredFields: ['targetUserId']
        }),
        USER_DELETED: Object.freeze({
            code: 'USER_DELETED',
            module: MODULES.USERS,
            category: CATEGORIES.USER_MANAGEMENT,
            severity: SEVERITIES.CRITICAL,
            description: 'User account deleted',
            requiredFields: ['targetUserId']
        }),
        USER_UPDATED: Object.freeze({
            code: 'USER_UPDATED',
            module: MODULES.USERS,
            category: CATEGORIES.USER_MANAGEMENT,
            severity: SEVERITIES.LOW,
            description: 'User details updated',
            requiredFields: ['targetUserId']
        }),

        ROLE_CHANGED: Object.freeze({
            code: 'ROLE_CHANGED',
            module: MODULES.PERMISSIONS,
            category: CATEGORIES.ACCESS_CONTROL,
            severity: SEVERITIES.HIGH,
            description: 'User role modified',
            requiredFields: ['targetUserId', 'previousRole', 'newRole']
        }),
        ROLE_ASSIGNED: Object.freeze({
            code: 'ROLE_ASSIGNED',
            module: MODULES.PERMISSIONS,
            category: CATEGORIES.ACCESS_CONTROL,
            severity: SEVERITIES.HIGH,
            description: 'Role assigned to user',
            requiredFields: ['targetUserId', 'assignedRole']
        }),
        PERMISSION_GRANTED: Object.freeze({
            code: 'PERMISSION_GRANTED',
            module: MODULES.PERMISSIONS,
            category: CATEGORIES.ACCESS_CONTROL,
            severity: SEVERITIES.MEDIUM,
            description: 'Permission granted',
            requiredFields: ['targetUserId', 'permission']
        }),
        PERMISSION_REVOKED: Object.freeze({
            code: 'PERMISSION_REVOKED',
            module: MODULES.PERMISSIONS,
            category: CATEGORIES.ACCESS_CONTROL,
            severity: SEVERITIES.MEDIUM,
            description: 'Permission revoked',
            requiredFields: ['targetUserId', 'permission']
        }),
        PERMISSION_OVERRIDE_SET: Object.freeze({
            code: 'PERMISSION_OVERRIDE_SET',
            module: MODULES.PERMISSIONS,
            category: CATEGORIES.ACCESS_CONTROL,
            severity: SEVERITIES.MEDIUM,
            description: 'Permission override configured',
            requiredFields: ['targetUserId']
        }),
        PERMISSION_OVERRIDE_REMOVED: Object.freeze({
            code: 'PERMISSION_OVERRIDE_REMOVED',
            module: MODULES.PERMISSIONS,
            category: CATEGORIES.ACCESS_CONTROL,
            severity: SEVERITIES.MEDIUM,
            description: 'Permission override removed',
            requiredFields: ['targetUserId']
        }),
        PERMISSIONS_RESET: Object.freeze({
            code: 'PERMISSIONS_RESET',
            module: MODULES.PERMISSIONS,
            category: CATEGORIES.ACCESS_CONTROL,
            severity: SEVERITIES.HIGH,
            description: 'Permissions reset to defaults',
            requiredFields: ['targetUserId']
        }),
        SENDER_CAPABILITY_CHANGED: Object.freeze({
            code: 'SENDER_CAPABILITY_CHANGED',
            module: MODULES.PERMISSIONS,
            category: CATEGORIES.ACCESS_CONTROL,
            severity: SEVERITIES.MEDIUM,
            description: 'Sender capability level modified',
            requiredFields: ['targetUserId', 'previousCapability', 'newCapability']
        }),

        SUB_ACCOUNT_CREATED: Object.freeze({
            code: 'SUB_ACCOUNT_CREATED',
            module: MODULES.SUB_ACCOUNTS,
            category: CATEGORIES.ACCOUNT,
            severity: SEVERITIES.HIGH,
            description: 'Sub-account created',
            requiredFields: ['subAccountId', 'subAccountName']
        }),
        SUB_ACCOUNT_UPDATED: Object.freeze({
            code: 'SUB_ACCOUNT_UPDATED',
            module: MODULES.SUB_ACCOUNTS,
            category: CATEGORIES.ACCOUNT,
            severity: SEVERITIES.MEDIUM,
            description: 'Sub-account details updated',
            requiredFields: ['subAccountId']
        }),
        SUB_ACCOUNT_SUSPENDED: Object.freeze({
            code: 'SUB_ACCOUNT_SUSPENDED',
            module: MODULES.SUB_ACCOUNTS,
            category: CATEGORIES.ACCOUNT,
            severity: SEVERITIES.HIGH,
            description: 'Sub-account suspended',
            requiredFields: ['subAccountId']
        }),
        SUB_ACCOUNT_REACTIVATED: Object.freeze({
            code: 'SUB_ACCOUNT_REACTIVATED',
            module: MODULES.SUB_ACCOUNTS,
            category: CATEGORIES.ACCOUNT,
            severity: SEVERITIES.HIGH,
            description: 'Sub-account reactivated',
            requiredFields: ['subAccountId']
        }),
        SUB_ACCOUNT_ARCHIVED: Object.freeze({
            code: 'SUB_ACCOUNT_ARCHIVED',
            module: MODULES.SUB_ACCOUNTS,
            category: CATEGORIES.ACCOUNT,
            severity: SEVERITIES.HIGH,
            description: 'Sub-account archived',
            requiredFields: ['subAccountId']
        }),

        ENFORCEMENT_OVERRIDE_REQUESTED: Object.freeze({
            code: 'ENFORCEMENT_OVERRIDE_REQUESTED',
            module: MODULES.SUB_ACCOUNTS,
            category: CATEGORIES.ENFORCEMENT,
            severity: SEVERITIES.HIGH,
            description: 'Enforcement override requested',
            requiredFields: ['subAccountId', 'ruleType']
        }),
        ENFORCEMENT_OVERRIDE_APPROVED: Object.freeze({
            code: 'ENFORCEMENT_OVERRIDE_APPROVED',
            module: MODULES.SUB_ACCOUNTS,
            category: CATEGORIES.ENFORCEMENT,
            severity: SEVERITIES.HIGH,
            description: 'Enforcement override approved',
            requiredFields: ['subAccountId', 'ruleType', 'approvedBy']
        }),
        ENFORCEMENT_OVERRIDE_DENIED: Object.freeze({
            code: 'ENFORCEMENT_OVERRIDE_DENIED',
            module: MODULES.SUB_ACCOUNTS,
            category: CATEGORIES.ENFORCEMENT,
            severity: SEVERITIES.MEDIUM,
            description: 'Enforcement override denied',
            requiredFields: ['subAccountId', 'ruleType']
        }),
        ENFORCEMENT_RULE_CHANGED: Object.freeze({
            code: 'ENFORCEMENT_RULE_CHANGED',
            module: MODULES.SUB_ACCOUNTS,
            category: CATEGORIES.ENFORCEMENT,
            severity: SEVERITIES.MEDIUM,
            description: 'Enforcement rule modified',
            requiredFields: ['subAccountId', 'ruleType']
        }),
        ENFORCEMENT_TRIGGERED: Object.freeze({
            code: 'ENFORCEMENT_TRIGGERED',
            module: MODULES.SUB_ACCOUNTS,
            category: CATEGORIES.ENFORCEMENT,
            severity: SEVERITIES.MEDIUM,
            description: 'Enforcement limit triggered',
            requiredFields: ['subAccountId', 'ruleType', 'triggerValue']
        }),

        MFA_ENABLED: Object.freeze({
            code: 'MFA_ENABLED',
            module: MODULES.SECURITY,
            category: CATEGORIES.SECURITY,
            severity: SEVERITIES.MEDIUM,
            description: 'Multi-factor authentication enabled',
            requiredFields: ['targetUserId', 'mfaMethod']
        }),
        MFA_DISABLED: Object.freeze({
            code: 'MFA_DISABLED',
            module: MODULES.SECURITY,
            category: CATEGORIES.SECURITY,
            severity: SEVERITIES.HIGH,
            description: 'Multi-factor authentication disabled',
            requiredFields: ['targetUserId']
        }),
        MFA_RESET: Object.freeze({
            code: 'MFA_RESET',
            module: MODULES.SECURITY,
            category: CATEGORIES.SECURITY,
            severity: SEVERITIES.HIGH,
            description: 'Multi-factor authentication reset',
            requiredFields: ['targetUserId']
        }),
        MFA_RECOVERY_USED: Object.freeze({
            code: 'MFA_RECOVERY_USED',
            module: MODULES.SECURITY,
            category: CATEGORIES.SECURITY,
            severity: SEVERITIES.HIGH,
            description: 'MFA recovery code used',
            requiredFields: ['targetUserId']
        }),
        IP_ALLOWLIST_UPDATED: Object.freeze({
            code: 'IP_ALLOWLIST_UPDATED',
            module: MODULES.SECURITY,
            category: CATEGORIES.SECURITY,
            severity: SEVERITIES.MEDIUM,
            description: 'IP allowlist configuration updated',
            requiredFields: ['changeType']
        }),
        PASSWORD_POLICY_CHANGED: Object.freeze({
            code: 'PASSWORD_POLICY_CHANGED',
            module: MODULES.SECURITY,
            category: CATEGORIES.SECURITY,
            severity: SEVERITIES.HIGH,
            description: 'Password policy modified',
            requiredFields: ['policyTier']
        }),

        LOGIN_SUCCESS: Object.freeze({
            code: 'LOGIN_SUCCESS',
            module: MODULES.AUTHENTICATION,
            category: CATEGORIES.AUTHENTICATION,
            severity: SEVERITIES.LOW,
            description: 'User logged in successfully',
            requiredFields: []
        }),
        LOGIN_FAILED: Object.freeze({
            code: 'LOGIN_FAILED',
            module: MODULES.AUTHENTICATION,
            category: CATEGORIES.AUTHENTICATION,
            severity: SEVERITIES.MEDIUM,
            description: 'Login attempt failed',
            requiredFields: ['failureReason']
        }),
        LOGIN_FAILED_MFA: Object.freeze({
            code: 'LOGIN_FAILED_MFA',
            module: MODULES.AUTHENTICATION,
            category: CATEGORIES.AUTHENTICATION,
            severity: SEVERITIES.MEDIUM,
            description: 'Login failed - MFA verification',
            requiredFields: []
        }),
        LOGIN_BLOCKED: Object.freeze({
            code: 'LOGIN_BLOCKED',
            module: MODULES.AUTHENTICATION,
            category: CATEGORIES.AUTHENTICATION,
            severity: SEVERITIES.HIGH,
            description: 'Login blocked due to security policy',
            requiredFields: ['blockReason']
        }),
        LOGOUT: Object.freeze({
            code: 'LOGOUT',
            module: MODULES.AUTHENTICATION,
            category: CATEGORIES.AUTHENTICATION,
            severity: SEVERITIES.LOW,
            description: 'User logged out',
            requiredFields: []
        }),
        SESSION_EXPIRED: Object.freeze({
            code: 'SESSION_EXPIRED',
            module: MODULES.AUTHENTICATION,
            category: CATEGORIES.AUTHENTICATION,
            severity: SEVERITIES.LOW,
            description: 'User session expired',
            requiredFields: []
        }),
        PASSWORD_CHANGED: Object.freeze({
            code: 'PASSWORD_CHANGED',
            module: MODULES.AUTHENTICATION,
            category: CATEGORIES.AUTHENTICATION,
            severity: SEVERITIES.MEDIUM,
            description: 'Password changed',
            requiredFields: ['targetUserId']
        }),
        PASSWORD_RESET_REQUESTED: Object.freeze({
            code: 'PASSWORD_RESET_REQUESTED',
            module: MODULES.AUTHENTICATION,
            category: CATEGORIES.AUTHENTICATION,
            severity: SEVERITIES.MEDIUM,
            description: 'Password reset requested',
            requiredFields: []
        }),
        PASSWORD_RESET_FORCED: Object.freeze({
            code: 'PASSWORD_RESET_FORCED',
            module: MODULES.AUTHENTICATION,
            category: CATEGORIES.AUTHENTICATION,
            severity: SEVERITIES.HIGH,
            description: 'Forced password reset initiated',
            requiredFields: ['targetUserId']
        }),

        CAMPAIGN_CREATED: Object.freeze({
            code: 'CAMPAIGN_CREATED',
            module: MODULES.CAMPAIGNS,
            category: CATEGORIES.MESSAGING,
            severity: SEVERITIES.LOW,
            description: 'Campaign created',
            requiredFields: ['campaignId']
        }),
        CAMPAIGN_SUBMITTED: Object.freeze({
            code: 'CAMPAIGN_SUBMITTED',
            module: MODULES.CAMPAIGNS,
            category: CATEGORIES.MESSAGING,
            severity: SEVERITIES.LOW,
            description: 'Campaign submitted for approval',
            requiredFields: ['campaignId']
        }),
        CAMPAIGN_APPROVED: Object.freeze({
            code: 'CAMPAIGN_APPROVED',
            module: MODULES.CAMPAIGNS,
            category: CATEGORIES.MESSAGING,
            severity: SEVERITIES.MEDIUM,
            description: 'Campaign approved',
            requiredFields: ['campaignId', 'approvedBy']
        }),
        CAMPAIGN_REJECTED: Object.freeze({
            code: 'CAMPAIGN_REJECTED',
            module: MODULES.CAMPAIGNS,
            category: CATEGORIES.MESSAGING,
            severity: SEVERITIES.MEDIUM,
            description: 'Campaign rejected',
            requiredFields: ['campaignId', 'rejectedBy', 'rejectionReason']
        }),
        CAMPAIGN_SENT: Object.freeze({
            code: 'CAMPAIGN_SENT',
            module: MODULES.CAMPAIGNS,
            category: CATEGORIES.MESSAGING,
            severity: SEVERITIES.LOW,
            description: 'Campaign dispatched',
            requiredFields: ['campaignId', 'recipientCount']
        }),
        CAMPAIGN_SCHEDULED: Object.freeze({
            code: 'CAMPAIGN_SCHEDULED',
            module: MODULES.CAMPAIGNS,
            category: CATEGORIES.MESSAGING,
            severity: SEVERITIES.LOW,
            description: 'Campaign scheduled',
            requiredFields: ['campaignId', 'scheduledTime']
        }),
        CAMPAIGN_CANCELLED: Object.freeze({
            code: 'CAMPAIGN_CANCELLED',
            module: MODULES.CAMPAIGNS,
            category: CATEGORIES.MESSAGING,
            severity: SEVERITIES.MEDIUM,
            description: 'Campaign cancelled',
            requiredFields: ['campaignId']
        }),

        TEMPLATE_CREATED: Object.freeze({
            code: 'TEMPLATE_CREATED',
            module: MODULES.MESSAGING,
            category: CATEGORIES.MESSAGING,
            severity: SEVERITIES.LOW,
            description: 'Message template created',
            requiredFields: ['templateId']
        }),
        TEMPLATE_UPDATED: Object.freeze({
            code: 'TEMPLATE_UPDATED',
            module: MODULES.MESSAGING,
            category: CATEGORIES.MESSAGING,
            severity: SEVERITIES.LOW,
            description: 'Message template updated',
            requiredFields: ['templateId']
        }),
        TEMPLATE_DELETED: Object.freeze({
            code: 'TEMPLATE_DELETED',
            module: MODULES.MESSAGING,
            category: CATEGORIES.MESSAGING,
            severity: SEVERITIES.MEDIUM,
            description: 'Message template deleted',
            requiredFields: ['templateId']
        }),
        TEMPLATE_APPROVED: Object.freeze({
            code: 'TEMPLATE_APPROVED',
            module: MODULES.MESSAGING,
            category: CATEGORIES.MESSAGING,
            severity: SEVERITIES.MEDIUM,
            description: 'Message template approved',
            requiredFields: ['templateId', 'approvedBy']
        }),
        TEMPLATE_REJECTED: Object.freeze({
            code: 'TEMPLATE_REJECTED',
            module: MODULES.MESSAGING,
            category: CATEGORIES.MESSAGING,
            severity: SEVERITIES.MEDIUM,
            description: 'Message template rejected',
            requiredFields: ['templateId', 'rejectedBy']
        }),

        CONTACT_CREATED: Object.freeze({
            code: 'CONTACT_CREATED',
            module: MODULES.CONTACTS,
            category: CATEGORIES.CONTACTS,
            severity: SEVERITIES.LOW,
            description: 'Contact created',
            requiredFields: []
        }),
        CONTACT_UPDATED: Object.freeze({
            code: 'CONTACT_UPDATED',
            module: MODULES.CONTACTS,
            category: CATEGORIES.CONTACTS,
            severity: SEVERITIES.LOW,
            description: 'Contact updated',
            requiredFields: []
        }),
        CONTACT_DELETED: Object.freeze({
            code: 'CONTACT_DELETED',
            module: MODULES.CONTACTS,
            category: CATEGORIES.CONTACTS,
            severity: SEVERITIES.MEDIUM,
            description: 'Contact deleted',
            requiredFields: []
        }),
        CONTACT_IMPORTED: Object.freeze({
            code: 'CONTACT_IMPORTED',
            module: MODULES.CONTACTS,
            category: CATEGORIES.CONTACTS,
            severity: SEVERITIES.LOW,
            description: 'Contacts imported',
            requiredFields: ['importCount']
        }),
        CONTACT_EXPORTED: Object.freeze({
            code: 'CONTACT_EXPORTED',
            module: MODULES.CONTACTS,
            category: CATEGORIES.CONTACTS,
            severity: SEVERITIES.MEDIUM,
            description: 'Contacts exported',
            requiredFields: ['exportCount']
        }),
        LIST_CREATED: Object.freeze({
            code: 'LIST_CREATED',
            module: MODULES.CONTACTS,
            category: CATEGORIES.CONTACTS,
            severity: SEVERITIES.LOW,
            description: 'Contact list created',
            requiredFields: ['listId']
        }),
        LIST_UPDATED: Object.freeze({
            code: 'LIST_UPDATED',
            module: MODULES.CONTACTS,
            category: CATEGORIES.CONTACTS,
            severity: SEVERITIES.LOW,
            description: 'Contact list updated',
            requiredFields: ['listId']
        }),
        LIST_DELETED: Object.freeze({
            code: 'LIST_DELETED',
            module: MODULES.CONTACTS,
            category: CATEGORIES.CONTACTS,
            severity: SEVERITIES.MEDIUM,
            description: 'Contact list deleted',
            requiredFields: ['listId']
        }),
        OPT_OUT_RECEIVED: Object.freeze({
            code: 'OPT_OUT_RECEIVED',
            module: MODULES.CONTACTS,
            category: CATEGORIES.CONTACTS,
            severity: SEVERITIES.MEDIUM,
            description: 'Opt-out request processed',
            requiredFields: []
        }),
        OPT_IN_RECEIVED: Object.freeze({
            code: 'OPT_IN_RECEIVED',
            module: MODULES.CONTACTS,
            category: CATEGORIES.CONTACTS,
            severity: SEVERITIES.LOW,
            description: 'Opt-in consent recorded',
            requiredFields: []
        }),

        DATA_VIEWED: Object.freeze({
            code: 'DATA_VIEWED',
            module: MODULES.REPORTING,
            category: CATEGORIES.DATA_ACCESS,
            severity: SEVERITIES.LOW,
            description: 'Data viewed',
            requiredFields: ['dataType']
        }),
        DATA_UNMASKED: Object.freeze({
            code: 'DATA_UNMASKED',
            module: MODULES.REPORTING,
            category: CATEGORIES.DATA_ACCESS,
            severity: SEVERITIES.HIGH,
            description: 'Sensitive data unmasked',
            requiredFields: ['dataType']
        }),
        DATA_EXPORTED: Object.freeze({
            code: 'DATA_EXPORTED',
            module: MODULES.REPORTING,
            category: CATEGORIES.DATA_ACCESS,
            severity: SEVERITIES.MEDIUM,
            description: 'Data exported',
            requiredFields: ['exportType', 'format']
        }),
        REPORT_GENERATED: Object.freeze({
            code: 'REPORT_GENERATED',
            module: MODULES.REPORTING,
            category: CATEGORIES.DATA_ACCESS,
            severity: SEVERITIES.LOW,
            description: 'Report generated',
            requiredFields: ['reportType']
        }),
        AUDIT_LOG_ACCESSED: Object.freeze({
            code: 'AUDIT_LOG_ACCESSED',
            module: MODULES.COMPLIANCE,
            category: CATEGORIES.DATA_ACCESS,
            severity: SEVERITIES.LOW,
            description: 'Audit logs accessed',
            requiredFields: []
        }),
        AUDIT_LOG_EXPORTED: Object.freeze({
            code: 'AUDIT_LOG_EXPORTED',
            module: MODULES.COMPLIANCE,
            category: CATEGORIES.DATA_ACCESS,
            severity: SEVERITIES.MEDIUM,
            description: 'Audit logs exported',
            requiredFields: ['format', 'recordCount']
        }),

        PURCHASE_INITIATED: Object.freeze({
            code: 'PURCHASE_INITIATED',
            module: MODULES.FINANCIAL,
            category: CATEGORIES.FINANCIAL,
            severity: SEVERITIES.LOW,
            description: 'Purchase initiated',
            requiredFields: ['purchaseType']
        }),
        PURCHASE_COMPLETED: Object.freeze({
            code: 'PURCHASE_COMPLETED',
            module: MODULES.FINANCIAL,
            category: CATEGORIES.FINANCIAL,
            severity: SEVERITIES.MEDIUM,
            description: 'Purchase completed',
            requiredFields: ['purchaseId', 'purchaseType']
        }),
        PURCHASE_FAILED: Object.freeze({
            code: 'PURCHASE_FAILED',
            module: MODULES.FINANCIAL,
            category: CATEGORIES.FINANCIAL,
            severity: SEVERITIES.MEDIUM,
            description: 'Purchase failed',
            requiredFields: ['failureReason']
        }),
        INVOICE_GENERATED: Object.freeze({
            code: 'INVOICE_GENERATED',
            module: MODULES.FINANCIAL,
            category: CATEGORIES.FINANCIAL,
            severity: SEVERITIES.LOW,
            description: 'Invoice generated',
            requiredFields: ['invoiceId']
        }),
        INVOICE_PAID: Object.freeze({
            code: 'INVOICE_PAID',
            module: MODULES.FINANCIAL,
            category: CATEGORIES.FINANCIAL,
            severity: SEVERITIES.MEDIUM,
            description: 'Invoice paid',
            requiredFields: ['invoiceId']
        }),
        PAYMENT_RECEIVED: Object.freeze({
            code: 'PAYMENT_RECEIVED',
            module: MODULES.FINANCIAL,
            category: CATEGORIES.FINANCIAL,
            severity: SEVERITIES.MEDIUM,
            description: 'Payment received',
            requiredFields: ['paymentId']
        }),
        CREDIT_APPLIED: Object.freeze({
            code: 'CREDIT_APPLIED',
            module: MODULES.FINANCIAL,
            category: CATEGORIES.FINANCIAL,
            severity: SEVERITIES.MEDIUM,
            description: 'Credit applied to account',
            requiredFields: ['creditType']
        }),
        REFUND_ISSUED: Object.freeze({
            code: 'REFUND_ISSUED',
            module: MODULES.FINANCIAL,
            category: CATEGORIES.FINANCIAL,
            severity: SEVERITIES.HIGH,
            description: 'Refund issued',
            requiredFields: ['refundId']
        }),

        ACCOUNT_CREATED: Object.freeze({
            code: 'ACCOUNT_CREATED',
            module: MODULES.ACCOUNT,
            category: CATEGORIES.ACCOUNT,
            severity: SEVERITIES.HIGH,
            description: 'Account created',
            requiredFields: []
        }),
        ACCOUNT_ACTIVATED: Object.freeze({
            code: 'ACCOUNT_ACTIVATED',
            module: MODULES.ACCOUNT,
            category: CATEGORIES.ACCOUNT,
            severity: SEVERITIES.HIGH,
            description: 'Account activated',
            requiredFields: []
        }),
        ACCOUNT_SUSPENDED: Object.freeze({
            code: 'ACCOUNT_SUSPENDED',
            module: MODULES.ACCOUNT,
            category: CATEGORIES.ACCOUNT,
            severity: SEVERITIES.CRITICAL,
            description: 'Account suspended',
            requiredFields: ['suspensionReason']
        }),
        ACCOUNT_REACTIVATED: Object.freeze({
            code: 'ACCOUNT_REACTIVATED',
            module: MODULES.ACCOUNT,
            category: CATEGORIES.ACCOUNT,
            severity: SEVERITIES.HIGH,
            description: 'Account reactivated',
            requiredFields: []
        }),
        ACCOUNT_DETAILS_UPDATED: Object.freeze({
            code: 'ACCOUNT_DETAILS_UPDATED',
            module: MODULES.ACCOUNT,
            category: CATEGORIES.ACCOUNT,
            severity: SEVERITIES.MEDIUM,
            description: 'Account details updated',
            requiredFields: ['fieldsUpdated']
        }),

        SAR_REQUEST: Object.freeze({
            code: 'SAR_REQUEST',
            module: MODULES.COMPLIANCE,
            category: CATEGORIES.GDPR,
            severity: SEVERITIES.HIGH,
            description: 'Subject access request received',
            requiredFields: ['requestId']
        }),
        SAR_COMPLETED: Object.freeze({
            code: 'SAR_COMPLETED',
            module: MODULES.COMPLIANCE,
            category: CATEGORIES.GDPR,
            severity: SEVERITIES.HIGH,
            description: 'Subject access request completed',
            requiredFields: ['requestId']
        }),
        DATA_DELETION_REQUESTED: Object.freeze({
            code: 'DATA_DELETION_REQUESTED',
            module: MODULES.COMPLIANCE,
            category: CATEGORIES.GDPR,
            severity: SEVERITIES.HIGH,
            description: 'Data deletion request received',
            requiredFields: ['requestId']
        }),
        DATA_DELETION_COMPLETED: Object.freeze({
            code: 'DATA_DELETION_COMPLETED',
            module: MODULES.COMPLIANCE,
            category: CATEGORIES.GDPR,
            severity: SEVERITIES.CRITICAL,
            description: 'Data deletion request processed',
            requiredFields: ['requestId']
        }),
        CONSENT_UPDATED: Object.freeze({
            code: 'CONSENT_UPDATED',
            module: MODULES.COMPLIANCE,
            category: CATEGORIES.GDPR,
            severity: SEVERITIES.MEDIUM,
            description: 'Consent preferences updated',
            requiredFields: ['consentType']
        }),
        PROCESSING_RECORD: Object.freeze({
            code: 'PROCESSING_RECORD',
            module: MODULES.COMPLIANCE,
            category: CATEGORIES.GDPR,
            severity: SEVERITIES.LOW,
            description: 'Processing activity recorded',
            requiredFields: ['processingType']
        }),

        SECURITY_INCIDENT: Object.freeze({
            code: 'SECURITY_INCIDENT',
            module: MODULES.COMPLIANCE,
            category: CATEGORIES.COMPLIANCE,
            severity: SEVERITIES.CRITICAL,
            description: 'Security incident reported',
            requiredFields: ['incidentType']
        }),
        POLICY_UPDATED: Object.freeze({
            code: 'POLICY_UPDATED',
            module: MODULES.COMPLIANCE,
            category: CATEGORIES.COMPLIANCE,
            severity: SEVERITIES.HIGH,
            description: 'Security policy updated',
            requiredFields: ['policyType']
        }),
        ACCESS_REVIEW: Object.freeze({
            code: 'ACCESS_REVIEW',
            module: MODULES.COMPLIANCE,
            category: CATEGORIES.COMPLIANCE,
            severity: SEVERITIES.MEDIUM,
            description: 'Access review completed',
            requiredFields: ['reviewScope']
        }),
        COMPLIANCE_CHECK: Object.freeze({
            code: 'COMPLIANCE_CHECK',
            module: MODULES.COMPLIANCE,
            category: CATEGORIES.COMPLIANCE,
            severity: SEVERITIES.LOW,
            description: 'Compliance check performed',
            requiredFields: ['checkType']
        }),

        API_KEY_CREATED: Object.freeze({
            code: 'API_KEY_CREATED',
            module: MODULES.API,
            category: CATEGORIES.API,
            severity: SEVERITIES.HIGH,
            description: 'API key created',
            requiredFields: ['keyId']
        }),
        API_KEY_REVOKED: Object.freeze({
            code: 'API_KEY_REVOKED',
            module: MODULES.API,
            category: CATEGORIES.API,
            severity: SEVERITIES.HIGH,
            description: 'API key revoked',
            requiredFields: ['keyId']
        }),
        API_KEY_ROTATED: Object.freeze({
            code: 'API_KEY_ROTATED',
            module: MODULES.API,
            category: CATEGORIES.API,
            severity: SEVERITIES.MEDIUM,
            description: 'API key rotated',
            requiredFields: ['keyId']
        }),
        WEBHOOK_CONFIGURED: Object.freeze({
            code: 'WEBHOOK_CONFIGURED',
            module: MODULES.API,
            category: CATEGORIES.API,
            severity: SEVERITIES.MEDIUM,
            description: 'Webhook endpoint configured',
            requiredFields: ['webhookId']
        }),
        WEBHOOK_DELETED: Object.freeze({
            code: 'WEBHOOK_DELETED',
            module: MODULES.API,
            category: CATEGORIES.API,
            severity: SEVERITIES.MEDIUM,
            description: 'Webhook endpoint deleted',
            requiredFields: ['webhookId']
        }),

        SYSTEM_STARTUP: Object.freeze({
            code: 'SYSTEM_STARTUP',
            module: MODULES.SYSTEM,
            category: CATEGORIES.SYSTEM,
            severity: SEVERITIES.LOW,
            description: 'System started',
            requiredFields: []
        }),
        SYSTEM_SHUTDOWN: Object.freeze({
            code: 'SYSTEM_SHUTDOWN',
            module: MODULES.SYSTEM,
            category: CATEGORIES.SYSTEM,
            severity: SEVERITIES.LOW,
            description: 'System shutdown',
            requiredFields: []
        }),
        SYSTEM_MAINTENANCE: Object.freeze({
            code: 'SYSTEM_MAINTENANCE',
            module: MODULES.SYSTEM,
            category: CATEGORIES.SYSTEM,
            severity: SEVERITIES.LOW,
            description: 'System maintenance performed',
            requiredFields: ['maintenanceType']
        }),
        CONFIG_CHANGED: Object.freeze({
            code: 'CONFIG_CHANGED',
            module: MODULES.SYSTEM,
            category: CATEGORIES.SYSTEM,
            severity: SEVERITIES.HIGH,
            description: 'System configuration changed',
            requiredFields: ['configKey']
        }),
        SCHEDULED_TASK_RUN: Object.freeze({
            code: 'SCHEDULED_TASK_RUN',
            module: MODULES.SYSTEM,
            category: CATEGORIES.SYSTEM,
            severity: SEVERITIES.LOW,
            description: 'Scheduled task executed',
            requiredFields: ['taskName']
        })
    });

    var APPROVED_EVENT_CODES = Object.keys(EVENT_CATALOGUE);

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

    var auditLog = [];
    var maxLogSize = 10000;
    var strictMode = true;

    function isValidEventType(eventType) {
        return APPROVED_EVENT_CODES.includes(eventType);
    }

    function getEventDefinition(eventType) {
        if (!isValidEventType(eventType)) {
            return null;
        }
        return EVENT_CATALOGUE[eventType];
    }

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
        return value.replace(PHONE_PATTERN, '[PHONE_REDACTED]');
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

        if (!isValidEventType(eventType)) {
            if (strictMode) {
                console.error('[AuditLogger] REJECTED: Event type "' + eventType + '" is not in the approved catalogue.');
                console.error('[AuditLogger] Approved event types:', APPROVED_EVENT_CODES.join(', '));
                return null;
            } else {
                console.warn('[AuditLogger] WARNING: Event type "' + eventType + '" is not in the approved catalogue.');
            }
        }

        var eventDef = getEventDefinition(eventType) || {
            code: eventType,
            module: MODULES.SYSTEM,
            category: CATEGORIES.SYSTEM,
            severity: SEVERITIES.MEDIUM,
            description: eventType.replace(/_/g, ' '),
            requiredFields: []
        };

        var actor = options.actor || getCurrentActor();
        var target = options.target || null;

        var entry = {
            eventId: generateEventId(),
            eventType: eventDef.code,
            eventTypeRef: 'CATALOGUE:' + eventDef.code,
            module: options.module || eventDef.module,
            actorType: actor.actorType || ACTOR_TYPES.USER,
            actorId: actor.actorId || actor.userId || 'unknown',
            subAccountId: actor.subAccountId || options.subAccountId || null,
            targetRef: target ? {
                entityType: target.resourceType || target.entityType || 'unknown',
                entityId: target.resourceId || target.entityId || target.userId || null
            } : null,
            description: options.description || eventDef.description,
            timestamp: getCurrentTimestampUTC(),
            ipAddress: options.ipAddress || getClientIP(),
            category: eventDef.category,
            severity: eventDef.severity,
            actorName: actor.actorName || actor.userName || null,
            actorRole: actor.actorRole || actor.role || null,
            metadata: sanitizeDetails(options.data || {}),
            sessionId: getSessionId(),
            requestId: options.requestId || generateRequestId(),
            result: options.result || 'success',
            reason: options.reason || null,
            catalogueVersion: '1.0.0'
        };

        auditLog.unshift(entry);

        if (auditLog.length > maxLogSize) {
            auditLog = auditLog.slice(0, maxLogSize);
        }

        console.log('[AUDIT]', formatLogEntry(entry));

        if (eventDef.severity === 'critical' || eventDef.severity === 'high') {
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
            ref: entry.eventTypeRef
        };
    }

    function notifySecurityTeam(entry) {
        console.log('[SECURITY ALERT]', entry.description, '-', entry.severity.toUpperCase());
    }

    function getCatalogue() {
        return Object.assign({}, EVENT_CATALOGUE);
    }

    function getApprovedEventTypes() {
        return APPROVED_EVENT_CODES.slice();
    }

    function getEventsByCategory(category) {
        return APPROVED_EVENT_CODES.filter(function(code) {
            return EVENT_CATALOGUE[code].category === category;
        });
    }

    function getEventsByModule(module) {
        return APPROVED_EVENT_CODES.filter(function(code) {
            return EVENT_CATALOGUE[code].module === module;
        });
    }

    function getEventsBySeverity(severity) {
        return APPROVED_EVENT_CODES.filter(function(code) {
            return EVENT_CATALOGUE[code].severity === severity;
        });
    }

    function setStrictMode(enabled) {
        strictMode = enabled;
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

    return {
        log: log,
        query: query,
        getRecentActivity: getRecentActivity,
        getSecurityAlerts: getSecurityAlerts,

        getCatalogue: getCatalogue,
        getApprovedEventTypes: getApprovedEventTypes,
        getEventDefinition: getEventDefinition,
        isValidEventType: isValidEventType,
        getEventsByCategory: getEventsByCategory,
        getEventsByModule: getEventsByModule,
        getEventsBySeverity: getEventsBySeverity,
        setStrictMode: setStrictMode,

        EVENT_CATALOGUE: EVENT_CATALOGUE,
        EVENT_TYPES: EVENT_CATALOGUE,
        ACTION_TYPES: EVENT_CATALOGUE,
        ACTOR_TYPES: ACTOR_TYPES,
        MODULES: MODULES,
        CATEGORIES: CATEGORIES,
        SEVERITIES: SEVERITIES,
        APPROVED_EVENT_CODES: APPROVED_EVENT_CODES
    };
})();

if (typeof module !== 'undefined' && module.exports) {
    module.exports = AuditLogger;
}
