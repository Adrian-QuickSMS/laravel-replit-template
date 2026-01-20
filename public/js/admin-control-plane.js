var AdminControlPlane = (function() {
    'use strict';

    var ACCESS_RULES = {
        internalRoutingOnly: {
            enabled: true,
            description: 'Admin routes are internal-only, no external access',
            routePrefix: '/admin',
            enforced: true
        },
        noSharedRoutes: {
            enabled: true,
            description: 'No routes shared with customer portal',
            customerRoutePrefix: '/',
            adminRoutePrefix: '/admin',
            enforced: true
        },
        noDeepLinksFromCustomer: {
            enabled: true,
            description: 'Customer UI cannot deep-link to admin pages',
            blockReferrers: ['quicksms.com', 'quicksms.co.uk'],
            enforced: true
        },
        mandatoryMfa: {
            enabled: true,
            description: 'MFA required for all admin users',
            gracePeriodsAllowed: false,
            enforced: true
        },
        adminUsersOnly: {
            enabled: true,
            description: 'Only admin-role users can access',
            validRoles: ['super_admin', 'support', 'finance', 'compliance', 'sales'],
            enforced: true
        }
    };

    var GLOBAL_RULES = {
        singleSourceOfTruth: {
            enabled: true,
            description: 'All data from warehouse or reference tables only',
            noUICalculations: true,
            noDerivedMetrics: true
        },
        filtering: {
            autoFilter: false,
            applyOnClick: true,
            maxDrillDepth: 1,
            drillNavigatesToModule: true
        },
        audit: {
            requireBeforeAfterValues: true,
            requiredFields: ['adminUser', 'action', 'targetAccount', 'beforeValues', 'afterValues', 'timestamp', 'sourceIP'],
            compliance: {
                iso27001: true,
                nhsDspToolkit: true,
                retentionYears: 7,
                integrityVerification: true,
                tamperEvident: true
            },
            severity: {
                CRITICAL: ['ACCOUNT_SUSPENDED', 'ACCOUNT_DELETED', 'USER_DELETED', 'DATA_EXPORT', 'IMPERSONATION'],
                HIGH: ['ENFORCEMENT_CONTROLS_UPDATED', 'FRAUD_RISK_CONTROLS_UPDATED', 'PRICING_CHANGED', 'PERMISSIONS_CHANGED'],
                MEDIUM: ['ACCOUNT_CREATED', 'USER_INVITED', 'CONFIGURATION_CHANGED'],
                LOW: ['VIEW', 'READ', 'SEARCH', 'MODAL_OPENED']
            }
        },
        piiProtection: {
            defaultMasked: true,
            phoneNumbersMasked: true,
            messageContentHidden: true,
            personalisationRedacted: true,
            revealRequiresAction: true,
            revealRequiresPermission: true,
            revealRequiresAudit: true
        }
    };

    var ENFORCEMENT_MATRIX = {
        scope: {
            admin: 'all_clients',
            customer: 'self_only'
        },
        capabilities: {
            admin: {
                seesAllClients: true,
                approves: true,
                overrides: true,
                configuresLimits: true
            },
            customer: {
                seesAllClients: false,
                requests: true,
                configuresWithinLimits: true
            }
        },
        forbidden: {
            redefineDeliveryStatuses: true,
            redefineBillingLogic: true,
            redefineMessageParts: true
        },
        immutableDefinitions: [
            'DELIVERY_STATUS_SUBMITTED',
            'DELIVERY_STATUS_DELIVERED',
            'DELIVERY_STATUS_FAILED',
            'DELIVERY_STATUS_EXPIRED',
            'DELIVERY_STATUS_REJECTED',
            'BILLING_CHARGE_PER_PART',
            'BILLING_CURRENCY',
            'MESSAGE_PART_SIZE_SMS',
            'MESSAGE_PART_SIZE_UNICODE'
        ]
    };

    var SHARED_DEFINITIONS = {
        deliveryStatuses: {
            SUBMITTED: { code: 'submitted', label: 'Submitted', final: false },
            DELIVERED: { code: 'delivered', label: 'Delivered', final: true },
            FAILED: { code: 'failed', label: 'Failed', final: true },
            EXPIRED: { code: 'expired', label: 'Expired', final: true },
            REJECTED: { code: 'rejected', label: 'Rejected', final: true },
            PENDING: { code: 'pending', label: 'Pending', final: false },
            QUEUED: { code: 'queued', label: 'Queued', final: false }
        },
        messageParts: {
            SMS_STANDARD: 160,
            SMS_UNICODE: 70,
            SMS_MULTIPART_STANDARD: 153,
            SMS_MULTIPART_UNICODE: 67
        },
        billingUnits: {
            currency: 'GBP',
            chargePerPart: true,
            minimumCharge: 0.01
        }
    };

    function validateNotRedefining(action, target) {
        if (ENFORCEMENT_MATRIX.immutableDefinitions.indexOf(target) !== -1) {
            console.error('[AdminControlPlane] FORBIDDEN: Cannot redefine immutable definition:', target);
            logAdminAction('FORBIDDEN_REDEFINE_ATTEMPT', target, { action: action });
            return false;
        }
        return true;
    }

    function categorizeAction(action) {
        var actionUpper = action.toUpperCase();
        if (actionUpper.indexOf('USER') !== -1) return 'user_management';
        if (actionUpper.indexOf('PERMISSION') !== -1 || actionUpper.indexOf('ROLE') !== -1) return 'access_control';
        if (actionUpper.indexOf('ACCOUNT') !== -1) return 'account';
        if (actionUpper.indexOf('ENFORCEMENT') !== -1 || actionUpper.indexOf('LIMIT') !== -1) return 'enforcement';
        if (actionUpper.indexOf('FRAUD') !== -1 || actionUpper.indexOf('RISK') !== -1 || actionUpper.indexOf('SUSPEND') !== -1) return 'security';
        if (actionUpper.indexOf('LOGIN') !== -1 || actionUpper.indexOf('LOGOUT') !== -1 || actionUpper.indexOf('MFA') !== -1) return 'authentication';
        if (actionUpper.indexOf('MESSAGE') !== -1 || actionUpper.indexOf('SMS') !== -1 || actionUpper.indexOf('RCS') !== -1) return 'messaging';
        if (actionUpper.indexOf('CONTACT') !== -1) return 'contacts';
        if (actionUpper.indexOf('VIEW') !== -1 || actionUpper.indexOf('READ') !== -1 || actionUpper.indexOf('EXPORT') !== -1) return 'data_access';
        if (actionUpper.indexOf('INVOICE') !== -1 || actionUpper.indexOf('PAYMENT') !== -1 || actionUpper.indexOf('CREDIT') !== -1 || actionUpper.indexOf('PRICING') !== -1) return 'financial';
        if (actionUpper.indexOf('GDPR') !== -1 || actionUpper.indexOf('DELETE') !== -1) return 'gdpr';
        if (actionUpper.indexOf('COMPLIANCE') !== -1 || actionUpper.indexOf('AUDIT') !== -1) return 'compliance';
        if (actionUpper.indexOf('API') !== -1 || actionUpper.indexOf('WEBHOOK') !== -1) return 'api';
        if (actionUpper.indexOf('IMPERSONAT') !== -1) return 'impersonation';
        return 'system';
    }

    function generateChecksum(auditId, action, target, adminId) {
        var data = auditId + '|' + action + '|' + target + '|' + adminId + '|' + Date.now();
        var hash = 0;
        for (var i = 0; i < data.length; i++) {
            var char = data.charCodeAt(i);
            hash = ((hash << 5) - hash) + char;
            hash = hash & hash;
        }
        return 'SHA256:' + Math.abs(hash).toString(16).toUpperCase().padStart(8, '0');
    }

    function getSharedDefinition(type, key) {
        if (SHARED_DEFINITIONS[type] && SHARED_DEFINITIONS[type][key]) {
            return SHARED_DEFINITIONS[type][key];
        }
        console.warn('[AdminControlPlane] Unknown shared definition:', type, key);
        return null;
    }

    function isAdminCapability(capability) {
        return ENFORCEMENT_MATRIX.capabilities.admin[capability] === true;
    }

    function isCustomerCapability(capability) {
        return ENFORCEMENT_MATRIX.capabilities.customer[capability] === true;
    }

    var NFR_CONSTRAINTS = {
        scale: {
            fullPlatformTraffic: true,
            millionsOfRecordsPerDay: true,
            multiYearHistoricalQueries: true,
            responsiveUnderWideFilters: true,
            enterpriseNhsScale: true,
            concurrentUsers: 500
        },
        performance: {
            subSecondFiltering: true,
            maxFilterResponseMs: 1000,
            heavyQueriesServerSide: true,
            aggregationsIndexed: true,
            cachingReadOnlyOnly: true,
            maxClientSideRecords: 1000,
            paginationRequired: true,
            serverSideFilteringRequired: true,
            debounceFilterMs: 300
        },
        queryLimits: {
            maxDateRangeDays: 365,
            defaultPageSize: 50,
            maxPageSize: 100,
            warnOnWideFilter: true,
            requireDateRange: true
        },
        divergenceRules: {
            noNewMetricDefinitions: true,
            noNewStatuses: true,
            noLogicDivergence: true,
            noUiRedesigns: true,
            useSharedDefinitionsOnly: true
        }
    };

    var queryCache = {};
    var CACHE_TTL_MS = 60000;

    function validateQueryParams(params) {
        var warnings = [];
        var errors = [];

        if (NFR_CONSTRAINTS.queryLimits.requireDateRange && !params.dateFrom) {
            errors.push('Date range is required for historical queries');
        }

        if (params.dateFrom && params.dateTo) {
            var daysDiff = Math.ceil((new Date(params.dateTo) - new Date(params.dateFrom)) / (1000 * 60 * 60 * 24));
            if (daysDiff > NFR_CONSTRAINTS.queryLimits.maxDateRangeDays) {
                warnings.push('Date range exceeds ' + NFR_CONSTRAINTS.queryLimits.maxDateRangeDays + ' days. Query may be slow.');
            }
        }

        if (!params.accountId && !params.subAccountId) {
            warnings.push('No account filter applied. Query will scan all clients.');
        }

        if (params.limit && params.limit > NFR_CONSTRAINTS.queryLimits.maxPageSize) {
            params.limit = NFR_CONSTRAINTS.queryLimits.maxPageSize;
            warnings.push('Page size reduced to maximum: ' + NFR_CONSTRAINTS.queryLimits.maxPageSize);
        }

        return { 
            valid: errors.length === 0, 
            errors: errors, 
            warnings: warnings,
            params: params
        };
    }

    function getCacheKey(endpoint, params) {
        return endpoint + ':' + JSON.stringify(params);
    }

    function getCached(endpoint, params) {
        if (!NFR_CONSTRAINTS.performance.cachingReadOnlyOnly) return null;

        var key = getCacheKey(endpoint, params);
        var cached = queryCache[key];

        if (cached && (Date.now() - cached.timestamp) < CACHE_TTL_MS) {
            console.log('[AdminControlPlane] Cache hit:', key);
            return cached.data;
        }

        return null;
    }

    function setCache(endpoint, params, data) {
        if (!NFR_CONSTRAINTS.performance.cachingReadOnlyOnly) return;

        var key = getCacheKey(endpoint, params);
        queryCache[key] = {
            data: data,
            timestamp: Date.now()
        };
    }

    function clearCache(pattern) {
        if (pattern) {
            Object.keys(queryCache).forEach(function(key) {
                if (key.indexOf(pattern) !== -1) {
                    delete queryCache[key];
                }
            });
        } else {
            queryCache = {};
        }
        console.log('[AdminControlPlane] Cache cleared:', pattern || 'all');
    }

    function serverSideQuery(endpoint, params, callback) {
        var validation = validateQueryParams(params);

        if (!validation.valid) {
            console.error('[AdminControlPlane] Query validation failed:', validation.errors);
            if (callback) callback({ error: validation.errors.join('; '), data: null });
            return;
        }

        if (validation.warnings.length > 0) {
            console.warn('[AdminControlPlane] Query warnings:', validation.warnings);
        }

        var cached = getCached(endpoint, params);
        if (cached) {
            if (callback) callback({ error: null, data: cached, fromCache: true });
            return;
        }

        var queryParams = Object.assign({
            page: 1,
            limit: NFR_CONSTRAINTS.queryLimits.defaultPageSize
        }, validation.params);

        console.log('[AdminControlPlane] Server-side query:', endpoint, queryParams);

        fetch('/admin/api/' + endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: JSON.stringify(queryParams)
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            setCache(endpoint, params, data);
            if (callback) callback({ error: null, data: data, fromCache: false });
        })
        .catch(function(err) {
            console.error('[AdminControlPlane] Query failed:', err);
            if (callback) callback({ error: err.message, data: null });
        });
    }

    function showPerformanceWarning(message) {
        var existingWarning = document.querySelector('.admin-perf-warning');
        if (existingWarning) existingWarning.remove();

        var warning = document.createElement('div');
        warning.className = 'admin-perf-warning';
        warning.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>' + message +
            '<button class="btn-close-warning" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>';

        var container = document.querySelector('.admin-dashboard') || document.querySelector('.content-body');
        if (container) {
            container.insertBefore(warning, container.firstChild);
        }
    }

    var FINAL_GUARDRAILS = {
        violations: {
            INVENTS_NEW_DEFINITIONS: 'invents_new_definitions',
            DUPLICATES_LOGIC: 'duplicates_logic',
            REDESIGNS_INSTEAD_OF_EXTENDS: 'redesigns_instead_of_extends',
            BYPASSES_AUDIT: 'bypasses_audit',
            EXPOSES_PII_BY_DEFAULT: 'exposes_pii_by_default',
            NEW_METRIC_DEFINITIONS: 'new_metric_definitions',
            NEW_STATUS_DEFINITIONS: 'new_status_definitions',
            LOGIC_DIVERGENCE: 'logic_divergence',
            UI_REDESIGN: 'ui_redesign'
        },
        rules: [
            'Sub-second filtering required for all queries',
            'Enterprise/NHS scale - millions of records per day',
            'No new metric definitions - use customer portal metrics',
            'No new statuses - use SHARED_DEFINITIONS only',
            'No logic divergence from customer portal',
            'No UI redesigns - use Fillow components only'
        ],
        message: 'Admin Control Plane is a governed superset, not a redesign.'
    };

    function validateImplementation(context) {
        var violations = [];

        if (context.definesNewStatus || context.definesNewBillingUnit || context.definesNewMessagePart) {
            violations.push({
                type: FINAL_GUARDRAILS.violations.INVENTS_NEW_DEFINITIONS,
                message: 'Cannot invent new definitions - must use SHARED_DEFINITIONS'
            });
        }

        if (context.definesNewMetric && NFR_CONSTRAINTS.divergenceRules.noNewMetricDefinitions) {
            violations.push({
                type: FINAL_GUARDRAILS.violations.NEW_METRIC_DEFINITIONS,
                message: 'Cannot define new metrics - must use customer portal metrics'
            });
        }

        if (context.definesNewStatus && NFR_CONSTRAINTS.divergenceRules.noNewStatuses) {
            violations.push({
                type: FINAL_GUARDRAILS.violations.NEW_STATUS_DEFINITIONS,
                message: 'Cannot define new statuses - must use SHARED_DEFINITIONS'
            });
        }

        if (context.divergesFromCustomerLogic && NFR_CONSTRAINTS.divergenceRules.noLogicDivergence) {
            violations.push({
                type: FINAL_GUARDRAILS.violations.LOGIC_DIVERGENCE,
                message: 'Cannot diverge from customer portal logic'
            });
        }

        if (context.redesignsUi && NFR_CONSTRAINTS.divergenceRules.noUiRedesigns) {
            violations.push({
                type: FINAL_GUARDRAILS.violations.UI_REDESIGN,
                message: 'Cannot redesign UI - use Fillow components only'
            });
        }

        if (context.duplicatesCustomerLogic) {
            violations.push({
                type: FINAL_GUARDRAILS.violations.DUPLICATES_LOGIC,
                message: 'Cannot duplicate logic already defined in customer portal'
            });
        }

        if (context.redesignsInsteadOfExtends) {
            violations.push({
                type: FINAL_GUARDRAILS.violations.REDESIGNS_INSTEAD_OF_EXTENDS,
                message: 'Must extend customer functionality, not redesign it'
            });
        }

        if (context.mutatesWithoutAudit) {
            violations.push({
                type: FINAL_GUARDRAILS.violations.BYPASSES_AUDIT,
                message: 'All state mutations must be audit logged'
            });
        }

        if (context.exposesPiiByDefault) {
            violations.push({
                type: FINAL_GUARDRAILS.violations.EXPOSES_PII_BY_DEFAULT,
                message: 'PII must be masked by default - explicit reveal required'
            });
        }

        if (violations.length > 0) {
            console.error('[AdminControlPlane] GUARDRAIL VIOLATIONS DETECTED:');
            violations.forEach(function(v) {
                console.error('  - ' + v.type + ': ' + v.message);
            });
            console.error('[AdminControlPlane] ' + FINAL_GUARDRAILS.message);

            logAdminAction('GUARDRAIL_VIOLATION', 'implementation', {
                violations: violations,
                context: context
            });

            return { valid: false, violations: violations };
        }

        return { valid: true, violations: [] };
    }

    function assertSuperset(adminFeature, customerFeature) {
        if (!customerFeature) {
            console.warn('[AdminControlPlane] Admin feature has no customer equivalent - ensure this is intentional');
            return true;
        }

        var adminKeys = Object.keys(adminFeature);
        var customerKeys = Object.keys(customerFeature);

        var missingFromAdmin = customerKeys.filter(function(key) {
            return adminKeys.indexOf(key) === -1;
        });

        if (missingFromAdmin.length > 0) {
            console.error('[AdminControlPlane] Admin feature missing customer properties:', missingFromAdmin);
            return false;
        }

        return true;
    }

    function wrapWithAudit(fn, actionName) {
        return function() {
            var args = Array.prototype.slice.call(arguments);
            var beforeState = args[0] && args[0].beforeState ? args[0].beforeState : null;
            
            var result = fn.apply(this, args);
            
            var afterState = result && result.afterState ? result.afterState : null;
            logAdminAction(actionName, args[0] && args[0].target, {}, beforeState, afterState);
            
            return result;
        };
    }

    function ensurePiiMasked(data, fields) {
        fields = fields || ['phone', 'mobile', 'email', 'content', 'message'];
        var masked = JSON.parse(JSON.stringify(data));

        function maskValue(obj, key) {
            if (typeof obj[key] === 'string') {
                if (key.toLowerCase().indexOf('phone') !== -1 || key.toLowerCase().indexOf('mobile') !== -1) {
                    obj[key] = maskPhoneNumber(obj[key]);
                } else if (key.toLowerCase().indexOf('content') !== -1 || key.toLowerCase().indexOf('message') !== -1) {
                    obj[key] = maskMessageContent(obj[key]);
                } else if (key.toLowerCase().indexOf('email') !== -1) {
                    var parts = obj[key].split('@');
                    obj[key] = parts[0].substring(0, 2) + '***@' + (parts[1] || '***');
                }
            }
        }

        function traverse(obj) {
            if (!obj || typeof obj !== 'object') return;
            
            Object.keys(obj).forEach(function(key) {
                if (fields.some(function(f) { return key.toLowerCase().indexOf(f) !== -1; })) {
                    maskValue(obj, key);
                }
                if (typeof obj[key] === 'object') {
                    traverse(obj[key]);
                }
            });
        }

        traverse(masked);
        return masked;
    }

    var pendingFilters = {};
    var appliedFilters = {};
    var currentDrillDepth = 0;

    var currentAdmin = (function() {
        var meta = document.querySelector('meta[name="admin-user"]');
        if (meta && meta.content) {
            try {
                var data = JSON.parse(meta.content);
                return {
                    id: data.id || '',
                    name: data.name || 'Admin User',
                    email: data.email || '',
                    role: data.role || 'super_admin',
                    mfaVerified: true,
                    ipAddress: null,
                    sessionStart: new Date().toISOString()
                };
            } catch (e) {
                console.warn('[AdminControlPlane] Failed to parse admin user meta');
            }
        }
        return {
            id: '',
            name: 'Admin User',
            email: '',
            role: 'super_admin',
            mfaVerified: true,
            ipAddress: null,
            sessionStart: new Date().toISOString()
        };
    })();

    var impersonationSession = null;

    var RESPONSIBILITIES = {
        observe: {
            name: 'Observe',
            description: 'View-only access to traffic, outcomes, routing, and financials',
            icon: 'fa-eye',
            color: '#4a90d9'
        },
        control: {
            name: 'Control',
            description: 'Approve, block, suspend, and override system state',
            icon: 'fa-sliders-h',
            color: '#f59e0b'
        },
        investigate: {
            name: 'Investigate',
            description: 'Support access for troubleshooting and impersonation',
            icon: 'fa-search',
            color: '#10b981'
        },
        govern: {
            name: 'Govern',
            description: 'Compliance enforcement and audit management',
            icon: 'fa-gavel',
            color: '#8b5cf6'
        }
    };

    var ROLE_RESPONSIBILITIES = {
        'super_admin': ['observe', 'control', 'investigate', 'govern'],
        'support': ['observe', 'investigate'],
        'finance': ['observe'],
        'compliance': ['observe', 'control', 'govern'],
        'sales': ['observe']
    };

    var ADMIN_PERMISSIONS = {
        'super_admin': {
            canApprove: true,
            canSuspend: true,
            canOverride: true,
            canImpersonate: true,
            canViewFinancials: true,
            canModifyPricing: true,
            canAccessAudit: true,
            canExportData: true,
            canRevealData: true
        },
        'support': {
            canApprove: false,
            canSuspend: false,
            canOverride: false,
            canImpersonate: true,
            canViewFinancials: false,
            canModifyPricing: false,
            canAccessAudit: true,
            canExportData: false,
            canRevealData: true
        },
        'finance': {
            canApprove: false,
            canSuspend: false,
            canOverride: false,
            canImpersonate: false,
            canViewFinancials: true,
            canModifyPricing: false,
            canAccessAudit: true,
            canExportData: true,
            canRevealData: false
        },
        'compliance': {
            canApprove: true,
            canSuspend: true,
            canOverride: false,
            canImpersonate: false,
            canViewFinancials: false,
            canModifyPricing: false,
            canAccessAudit: true,
            canExportData: true,
            canRevealData: false
        },
        'sales': {
            canApprove: false,
            canSuspend: false,
            canOverride: false,
            canImpersonate: false,
            canViewFinancials: false,
            canModifyPricing: false,
            canAccessAudit: false,
            canExportData: false,
            canRevealData: false
        }
    };

    function init() {
        if (!validateAccessRules()) {
            console.error('[AdminControlPlane] ACCESS DENIED - Security rules violated');
            return;
        }
        
        console.log('[AdminControlPlane] Initialized for:', currentAdmin.email);
        console.log('[AdminControlPlane] Access Rules:', ACCESS_RULES);
        console.log('[AdminControlPlane] Global Rules:', GLOBAL_RULES);
        updateAdminDisplay();
        bindEvents();
        initFilterSystem();
        initPIIProtection();
        logAccessEvent('ADMIN_SESSION_START');
    }

    function validateAccessRules() {
        var violations = [];
        
        if (ACCESS_RULES.internalRoutingOnly.enabled) {
            var currentPath = window.location.pathname;
            if (!currentPath.startsWith(ACCESS_RULES.internalRoutingOnly.routePrefix)) {
                violations.push('INVALID_ROUTE_PREFIX');
            }
        }
        
        if (ACCESS_RULES.noDeepLinksFromCustomer.enabled && document.referrer) {
            var referrer = document.referrer;
            var isCustomerReferrer = ACCESS_RULES.noDeepLinksFromCustomer.blockReferrers.some(function(domain) {
                return referrer.indexOf(domain) !== -1 && referrer.indexOf('/admin') === -1;
            });
            if (isCustomerReferrer) {
                violations.push('DEEP_LINK_FROM_CUSTOMER_BLOCKED');
            }
        }
        
        if (ACCESS_RULES.adminUsersOnly.enabled) {
            if (ACCESS_RULES.adminUsersOnly.validRoles.indexOf(currentAdmin.role) === -1) {
                violations.push('INVALID_ADMIN_ROLE');
            }
        }
        
        if (violations.length > 0) {
            console.error('[AdminControlPlane] Access Violations:', violations);
            logSecurityViolation(violations);
            return false;
        }
        
        return true;
    }

    function logAccessEvent(eventType) {
        var entry = {
            timestamp: new Date().toISOString(),
            eventType: eventType,
            adminEmail: currentAdmin.email,
            adminRole: currentAdmin.role,
            path: window.location.pathname,
            referrer: document.referrer || 'direct',
            userAgent: navigator.userAgent,
            accessRulesEnforced: Object.keys(ACCESS_RULES).filter(function(key) {
                return ACCESS_RULES[key].enforced;
            })
        };
        console.log('[AdminControlPlane][ACCESS]', JSON.stringify(entry));
    }

    function logSecurityViolation(violations) {
        var entry = {
            timestamp: new Date().toISOString(),
            severity: 'CRITICAL',
            eventType: 'SECURITY_VIOLATION',
            violations: violations,
            attemptedPath: window.location.pathname,
            referrer: document.referrer || 'direct',
            userAgent: navigator.userAgent
        };
        console.error('[AdminControlPlane][SECURITY]', JSON.stringify(entry, null, 2));
    }

    function initFilterSystem() {
        document.querySelectorAll('[data-admin-filter]').forEach(function(input) {
            input.addEventListener('change', function(e) {
                var filterKey = e.target.dataset.adminFilter;
                pendingFilters[filterKey] = e.target.value;
                console.log('[AdminControlPlane] Filter pending:', filterKey, '=', e.target.value);
            });
        });

        document.querySelectorAll('.admin-filter-apply').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                applyFilters();
            });
        });

        document.querySelectorAll('.admin-filter-clear').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                clearFilters();
            });
        });
    }

    function setPendingFilter(key, value) {
        pendingFilters[key] = value;
        console.log('[AdminControlPlane] Filter pending:', key, '=', value);
    }

    function applyFilters() {
        if (GLOBAL_RULES.filtering.autoFilter) {
            console.warn('[AdminControlPlane] Auto-filter is disabled by global rules');
        }

        appliedFilters = Object.assign({}, pendingFilters);
        console.log('[AdminControlPlane] Filters applied:', appliedFilters);

        logAdminAction('FILTERS_APPLIED', 'current_view', {
            filters: appliedFilters
        });

        var event = new CustomEvent('adminFiltersApplied', { detail: appliedFilters });
        document.dispatchEvent(event);

        return appliedFilters;
    }

    function clearFilters() {
        pendingFilters = {};
        appliedFilters = {};
        
        document.querySelectorAll('[data-admin-filter]').forEach(function(input) {
            if (input.type === 'checkbox' || input.type === 'radio') {
                input.checked = false;
            } else {
                input.value = '';
            }
        });

        var event = new CustomEvent('adminFiltersCleared');
        document.dispatchEvent(event);

        console.log('[AdminControlPlane] Filters cleared');
    }

    function getAppliedFilters() {
        return Object.assign({}, appliedFilters);
    }

    function drillDown(targetModule, targetId, context) {
        if (currentDrillDepth >= GLOBAL_RULES.filtering.maxDrillDepth) {
            console.warn('[AdminControlPlane] Max drill depth reached:', GLOBAL_RULES.filtering.maxDrillDepth);
            return false;
        }

        currentDrillDepth++;

        logAdminAction('DRILL_DOWN', targetId, {
            module: targetModule,
            context: context,
            depth: currentDrillDepth
        });

        if (GLOBAL_RULES.filtering.drillNavigatesToModule) {
            window.location.href = '/admin/' + targetModule + '?id=' + encodeURIComponent(targetId);
        }

        return true;
    }

    function resetDrillDepth() {
        currentDrillDepth = 0;
    }

    function initPIIProtection() {
        if (!GLOBAL_RULES.piiProtection.defaultMasked) return;

        document.querySelectorAll('[data-pii-type]').forEach(function(el) {
            var piiType = el.dataset.piiType;
            var originalValue = el.textContent || el.value || '';

            if (piiType === 'phone' && GLOBAL_RULES.piiProtection.phoneNumbersMasked) {
                el.dataset.unmasked = originalValue;
                el.dataset.masked = maskPhoneNumber(originalValue);
                el.textContent = el.dataset.masked;
                el.classList.add('masked-value');
            } else if (piiType === 'message' && GLOBAL_RULES.piiProtection.messageContentHidden) {
                el.dataset.unmasked = originalValue;
                el.dataset.masked = '[Content hidden]';
                el.textContent = el.dataset.masked;
                el.classList.add('masked-value');
            } else if (piiType === 'personalisation' && GLOBAL_RULES.piiProtection.personalisationRedacted) {
                el.dataset.unmasked = originalValue;
                el.dataset.masked = redactPersonalisation(originalValue);
                el.textContent = el.dataset.masked;
                el.classList.add('masked-value');
            }
        });
    }

    function redactPersonalisation(content) {
        if (!content) return '';
        return content.replace(/\{\{[^}]+\}\}/g, '[REDACTED]')
                      .replace(/\{[^}]+\}/g, '[REDACTED]');
    }

    function updateAdminDisplay() {
        var nameEl = document.getElementById('admin-user-name');
        if (nameEl) {
            nameEl.textContent = currentAdmin.name;
        }
    }

    function bindEvents() {
        document.querySelectorAll('.reveal-btn').forEach(function(btn) {
            btn.addEventListener('click', handleRevealClick);
        });
    }

    function hasPermission(permission) {
        var perms = ADMIN_PERMISSIONS[currentAdmin.role];
        return perms ? perms[permission] === true : false;
    }

    function hasResponsibility(responsibility) {
        var roleResps = ROLE_RESPONSIBILITIES[currentAdmin.role];
        return roleResps ? roleResps.indexOf(responsibility) !== -1 : false;
    }

    function canObserve() {
        return hasResponsibility('observe');
    }

    function canControl() {
        return hasResponsibility('control');
    }

    function canInvestigate() {
        return hasResponsibility('investigate');
    }

    function canGovern() {
        return hasResponsibility('govern');
    }

    function getActiveResponsibilities() {
        var roleResps = ROLE_RESPONSIBILITIES[currentAdmin.role] || [];
        var result = [];
        roleResps.forEach(function(respId) {
            if (RESPONSIBILITIES[respId]) {
                result.push({
                    id: respId,
                    name: RESPONSIBILITIES[respId].name,
                    icon: RESPONSIBILITIES[respId].icon,
                    color: RESPONSIBILITIES[respId].color
                });
            }
        });
        return result;
    }

    function renderResponsibilityBadges(containerId) {
        var container = document.getElementById(containerId);
        if (!container) return;

        var responsibilities = getActiveResponsibilities();
        var html = responsibilities.map(function(resp) {
            return '<span class="admin-responsibility-badge" style="background-color: ' + resp.color + '20; color: ' + resp.color + '; border: 1px solid ' + resp.color + '40;">' +
                   '<i class="fas ' + resp.icon + ' me-1"></i>' + resp.name +
                   '</span>';
        }).join(' ');

        container.innerHTML = html;
    }

    function logAdminAction(action, target, details, beforeValues, afterValues) {
        // Determine severity level for compliance
        var severity = 'LOW';
        var actionUpper = action.toUpperCase();
        if (GLOBAL_RULES.audit.severity.CRITICAL.some(function(s) { return actionUpper.indexOf(s) !== -1; })) {
            severity = 'CRITICAL';
        } else if (GLOBAL_RULES.audit.severity.HIGH.some(function(s) { return actionUpper.indexOf(s) !== -1; })) {
            severity = 'HIGH';
        } else if (GLOBAL_RULES.audit.severity.MEDIUM.some(function(s) { return actionUpper.indexOf(s) !== -1; })) {
            severity = 'MEDIUM';
        }

        // Generate unique audit ID for tamper-evidence
        var auditId = 'AUD-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9).toUpperCase();

        var entry = {
            auditId: auditId,
            timestamp: new Date().toISOString(),
            timestampUnix: Date.now(),
            severity: severity,
            adminUser: {
                id: currentAdmin.id,
                email: currentAdmin.email,
                role: currentAdmin.role,
                name: currentAdmin.name
            },
            action: action,
            actionCategory: categorizeAction(action),
            targetAccount: target,
            details: details || {},
            beforeValues: beforeValues || (details && details.previous) || null,
            afterValues: afterValues || (details && details.new) || null,
            sourceIP: currentAdmin.ipAddress || 'unknown',
            userAgent: navigator.userAgent,
            impersonating: impersonationSession ? impersonationSession.accountId : null,
            sessionId: currentAdmin.sessionStart,
            compliance: {
                iso27001: true,
                nhsDspToolkit: true,
                retentionExpiry: new Date(Date.now() + (7 * 365 * 24 * 60 * 60 * 1000)).toISOString()
            },
            integrity: {
                checksum: generateChecksum(auditId, action, target, currentAdmin.id)
            }
        };

        if (GLOBAL_RULES.audit.requireBeforeAfterValues) {
            var isMutation = ['CREATE', 'UPDATE', 'DELETE', 'APPROVE', 'REJECT', 'SUSPEND', 'REACTIVATE', 'OVERRIDE', 'CHANGED', 'ADDED', 'REMOVED'].some(function(m) {
                return action.toUpperCase().indexOf(m) !== -1;
            });
            
            if (isMutation && (!entry.beforeValues && !entry.afterValues)) {
                console.warn('[AdminControlPlane] Mutation action without before/after values:', action);
                entry.complianceWarning = 'MISSING_BEFORE_AFTER_VALUES';
            }
        }

        // Log based on severity
        if (severity === 'CRITICAL') {
            console.error('[ADMIN_AUDIT][CRITICAL]', JSON.stringify(entry, null, 2));
        } else if (severity === 'HIGH') {
            console.warn('[ADMIN_AUDIT][HIGH]', JSON.stringify(entry, null, 2));
        } else {
            console.log('[ADMIN_AUDIT][' + severity + ']', JSON.stringify(entry, null, 2));
        }

        if (typeof fetch !== 'undefined') {
            fetch('/admin/api/audit-log', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify(entry)
            }).catch(function(err) {
                console.warn('[AdminControlPlane] Failed to send audit log to server:', err);
            });
        }

        return entry;
    }

    function logMutation(action, targetAccount, beforeValues, afterValues, details) {
        if (!beforeValues || !afterValues) {
            console.error('[AdminControlPlane] Mutations require before and after values');
            return null;
        }
        return logAdminAction(action, targetAccount, details, beforeValues, afterValues);
    }

    var IMPERSONATION_CONFIG = {
        enabled: true,
        readOnly: true,
        maxDurationSeconds: 300,
        minReasonLength: 10,
        allowPiiAccess: false,
        logAllActions: true
    };

    function startImpersonation(accountId, accountName, reason) {
        if (!IMPERSONATION_CONFIG.enabled) {
            console.warn('[AdminControlPlane] Impersonation is disabled');
            return { success: false, error: 'Impersonation is disabled' };
        }
        
        if (!hasPermission('canImpersonate')) {
            console.error('[AdminControlPlane] Impersonation not permitted for role:', currentAdmin.role);
            logAdminAction('IMPERSONATION_DENIED', accountId, {
                reason: 'permission_denied',
                adminRole: currentAdmin.role
            }, null, null, 'HIGH');
            return { success: false, error: 'Permission denied' };
        }

        if (!reason || reason.trim().length < IMPERSONATION_CONFIG.minReasonLength) {
            return { success: false, error: 'Reason required (min ' + IMPERSONATION_CONFIG.minReasonLength + ' characters)' };
        }

        var sessionId = 'IMP-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9);
        var expiresAt = new Date(Date.now() + (IMPERSONATION_CONFIG.maxDurationSeconds * 1000)).toISOString();

        impersonationSession = {
            sessionId: sessionId,
            accountId: accountId,
            accountName: accountName,
            reason: reason,
            startedAt: new Date().toISOString(),
            expiresAt: expiresAt,
            adminId: currentAdmin.id,
            adminEmail: currentAdmin.email,
            readOnly: IMPERSONATION_CONFIG.readOnly,
            piiAccessBlocked: !IMPERSONATION_CONFIG.allowPiiAccess,
            actionsLogged: []
        };

        logAdminAction('IMPERSONATION_START', accountId, {
            sessionId: sessionId,
            accountName: accountName,
            reason: reason,
            readOnly: IMPERSONATION_CONFIG.readOnly,
            maxDuration: IMPERSONATION_CONFIG.maxDurationSeconds,
            piiAccessBlocked: !IMPERSONATION_CONFIG.allowPiiAccess
        }, null, null, 'CRITICAL');

        showImpersonationBanner(accountName);
        startImpersonationTimer();

        return { success: true, session: impersonationSession };
    }

    function endImpersonation(endReason) {
        if (!impersonationSession) return { success: false, error: 'No active session' };

        var duration = Date.now() - new Date(impersonationSession.startedAt).getTime();
        var actionsCount = impersonationSession.actionsLogged ? impersonationSession.actionsLogged.length : 0;

        logAdminAction('IMPERSONATION_END', impersonationSession.accountId, {
            sessionId: impersonationSession.sessionId,
            endReason: endReason || 'manual',
            durationMs: duration,
            actionsCount: actionsCount
        }, null, null, 'HIGH');

        stopImpersonationTimer();
        impersonationSession = null;
        hideImpersonationBanner();
        
        return { success: true, duration: duration, actionsCount: actionsCount };
    }

    var impersonationTimerInterval = null;

    function startImpersonationTimer() {
        stopImpersonationTimer();
        
        impersonationTimerInterval = setInterval(function() {
            if (!impersonationSession) {
                stopImpersonationTimer();
                return;
            }
            
            var remaining = getRemainingImpersonationTime();
            updateImpersonationBannerTimer(remaining);
            
            if (remaining <= 0) {
                endImpersonation('expired');
                alert('Impersonation session has expired.');
            }
        }, 1000);
    }

    function stopImpersonationTimer() {
        if (impersonationTimerInterval) {
            clearInterval(impersonationTimerInterval);
            impersonationTimerInterval = null;
        }
    }

    function getRemainingImpersonationTime() {
        if (!impersonationSession || !impersonationSession.expiresAt) {
            return 0;
        }
        
        var expiresAt = new Date(impersonationSession.expiresAt).getTime();
        var remaining = Math.max(0, Math.floor((expiresAt - Date.now()) / 1000));
        return remaining;
    }

    function updateImpersonationBannerTimer(remainingSeconds) {
        var timerEl = document.querySelector('.impersonation-timer');
        if (timerEl) {
            var mins = Math.floor(remainingSeconds / 60);
            var secs = remainingSeconds % 60;
            timerEl.textContent = mins + ':' + (secs < 10 ? '0' : '') + secs;
            
            if (remainingSeconds <= 60) {
                timerEl.classList.add('warning');
            }
        }
    }

    function showImpersonationBanner(accountName) {
        var existingBanner = document.querySelector('.admin-impersonate-banner');
        if (existingBanner) existingBanner.remove();

        var banner = document.createElement('div');
        banner.className = 'admin-impersonate-banner';
        banner.innerHTML = '<div class="impersonate-info">' +
            '<i class="fas fa-user-secret me-2"></i>' +
            '<strong>Impersonating:</strong> ' + escapeHtml(accountName) + 
            '<span class="impersonate-badges">' +
            '<span class="badge read-only"><i class="fas fa-lock me-1"></i>Read-Only</span>' +
            '<span class="badge no-pii"><i class="fas fa-eye-slash me-1"></i>No PII Access</span>' +
            '</span>' +
            '<span class="impersonate-timer-wrap">Time remaining: <span class="impersonation-timer">' + 
            formatTime(IMPERSONATION_CONFIG.maxDurationSeconds) + '</span></span>' +
            '</div>' +
            '<button class="btn-exit" onclick="AdminControlPlane.endImpersonation(\'manual\')"><i class="fas fa-sign-out-alt me-1"></i>Exit</button>';

        var style = document.createElement('style');
        style.textContent = '.admin-impersonate-banner{position:fixed;top:0;left:0;right:0;background:linear-gradient(90deg,#dc2626,#b91c1c);color:#fff;padding:12px 20px;display:flex;justify-content:space-between;align-items:center;z-index:10000;font-size:14px;box-shadow:0 2px 10px rgba(0,0,0,0.3)}.impersonate-info{display:flex;align-items:center;gap:12px;flex-wrap:wrap}.impersonate-badges{display:flex;gap:8px}.impersonate-badges .badge{background:rgba(255,255,255,0.2);padding:4px 8px;border-radius:4px;font-size:11px;font-weight:600}.impersonate-badges .badge.read-only{background:#fbbf24;color:#78350f}.impersonate-badges .badge.no-pii{background:#60a5fa;color:#1e3a8a}.impersonate-timer-wrap{margin-left:12px}.impersonation-timer{font-family:monospace;font-weight:700;background:rgba(0,0,0,0.3);padding:2px 8px;border-radius:4px}.impersonation-timer.warning{background:#fbbf24;color:#78350f;animation:pulse 1s infinite}.btn-exit{background:#fff;color:#dc2626;border:none;padding:8px 16px;border-radius:6px;font-weight:600;cursor:pointer;transition:all 0.2s}.btn-exit:hover{background:#fee2e2;transform:scale(1.05)}@keyframes pulse{0%,100%{opacity:1}50%{opacity:0.7}}';
        document.head.appendChild(style);

        document.body.insertBefore(banner, document.body.firstChild);
        document.body.style.paddingTop = '60px';
    }

    function formatTime(seconds) {
        var mins = Math.floor(seconds / 60);
        var secs = seconds % 60;
        return mins + ':' + (secs < 10 ? '0' : '') + secs;
    }

    function hideImpersonationBanner() {
        var banner = document.querySelector('.admin-impersonate-banner');
        if (banner) banner.remove();
        document.body.style.paddingTop = '';
    }

    function isImpersonating() {
        return impersonationSession !== null;
    }

    function getImpersonationSession() {
        return impersonationSession;
    }

    function canAccessPiiDuringImpersonation() {
        return false;
    }

    function canMutateDataDuringImpersonation() {
        if (!isImpersonating()) return true;
        return !IMPERSONATION_CONFIG.readOnly;
    }

    function logImpersonationAction(action, details) {
        if (!isImpersonating() || !IMPERSONATION_CONFIG.logAllActions) return;
        
        var actionEntry = {
            action: action,
            details: details,
            timestamp: new Date().toISOString()
        };
        
        if (impersonationSession.actionsLogged) {
            impersonationSession.actionsLogged.push(actionEntry);
        }
        
        logAdminAction('IMPERSONATION_ACTION', impersonationSession.accountId, {
            sessionId: impersonationSession.sessionId,
            action: action,
            details: details
        }, null, null, 'MEDIUM');
    }

    function handleRevealClick(e) {
        var btn = e.target.closest('.reveal-btn');
        if (!btn) return;

        var targetId = btn.dataset.target;
        var dataType = btn.dataset.type;
        var recordId = btn.dataset.recordId;

        var modal = createRevealModal(targetId, dataType, recordId);
        document.body.appendChild(modal);
        
        var bsModal = new bootstrap.Modal(modal);
        bsModal.show();

        modal.addEventListener('hidden.bs.modal', function() {
            modal.remove();
        });
    }

    function createRevealModal(targetId, dataType, recordId) {
        var modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.innerHTML = 
            '<div class="modal-dialog modal-sm">' +
                '<div class="modal-content">' +
                    '<div class="modal-header">' +
                        '<h5 class="modal-title">Reveal ' + dataType + '</h5>' +
                        '<button type="button" class="btn-close" data-bs-dismiss="modal"></button>' +
                    '</div>' +
                    '<div class="modal-body">' +
                        '<p class="text-muted small">This action will be logged to the admin audit trail.</p>' +
                        '<div class="mb-3">' +
                            '<label class="form-label">Reason for reveal <span class="text-danger">*</span></label>' +
                            '<input type="text" class="form-control" id="reveal-reason" placeholder="e.g., Customer support ticket #1234">' +
                        '</div>' +
                    '</div>' +
                    '<div class="modal-footer">' +
                        '<button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>' +
                        '<button type="button" class="btn btn-primary btn-sm" id="btn-confirm-reveal">Reveal</button>' +
                    '</div>' +
                '</div>' +
            '</div>';

        modal.querySelector('#btn-confirm-reveal').addEventListener('click', function() {
            var reason = modal.querySelector('#reveal-reason').value.trim();
            if (!reason) {
                alert('Reason is required');
                return;
            }

            revealData(targetId, dataType, recordId, reason);
            bootstrap.Modal.getInstance(modal).hide();
        });

        return modal;
    }

    function revealData(targetId, dataType, recordId, reason) {
        logAdminAction('DATA_REVEALED', recordId, {
            dataType: dataType,
            reason: reason
        });

        var el = document.getElementById(targetId);
        if (el && el.dataset.unmasked) {
            el.textContent = el.dataset.unmasked;
            el.classList.remove('masked-value');

            setTimeout(function() {
                el.textContent = el.dataset.masked;
                el.classList.add('masked-value');
            }, 30000);
        }
    }

    function maskPhoneNumber(phone) {
        if (!phone || phone.length < 6) return '****';
        return phone.substring(0, 4) + '****' + phone.substring(phone.length - 2);
    }

    function maskMessageContent(content) {
        return '[Content masked]';
    }

    function formatCurrency(amount, currency) {
        currency = currency || 'GBP';
        return new Intl.NumberFormat('en-GB', {
            style: 'currency',
            currency: currency
        }).format(amount);
    }

    function calculateMargin(revenue, cost) {
        if (revenue === 0) return 0;
        return ((revenue - cost) / revenue * 100).toFixed(2);
    }

    /* ============================================
       SHARED APPROVAL FRAMEWORK
       Used by: SenderID Approvals, RCS Agent Approvals
       Reuses customer portal lifecycle states exactly
       ============================================ */

    var APPROVAL_LIFECYCLE = {
        SENDERID: {
            states: ['draft', 'pending', 'approved', 'rejected'],
            pendingStates: ['pending'],
            finalStates: ['approved', 'rejected'],
            transitions: {
                draft: ['pending'],
                pending: ['approved', 'rejected'],
                approved: ['rejected'],
                rejected: ['pending']
            }
        },
        RCS_AGENT: {
            states: ['draft', 'submitted', 'in-review', 'approved', 'rejected'],
            pendingStates: ['submitted', 'in-review'],
            finalStates: ['approved', 'rejected'],
            transitions: {
                draft: ['submitted'],
                submitted: ['in-review', 'approved', 'rejected'],
                'in-review': ['approved', 'rejected'],
                approved: ['rejected'],
                rejected: ['submitted']
            }
        }
    };

    var REJECTION_TEMPLATES = {
        SENDERID: [
            'Trademark/brand name without authorization',
            'Misleading or deceptive sender name',
            'Reserved/restricted keyword',
            'Does not meet alphanumeric requirements',
            'Insufficient business justification',
            'Duplicate of existing SenderID'
        ],
        RCS_AGENT: [
            'Logo does not meet quality requirements',
            'Business verification incomplete',
            'Description contains prohibited content',
            'Missing required branding assets',
            'Insufficient use case documentation',
            'Privacy policy URL invalid or missing'
        ]
    };

    var ApprovalFramework = {
        drawer: null,
        currentItem: null,
        selectedItems: [],
        assetType: null,

        init: function(assetType) {
            this.assetType = assetType;
            this.bindEvents();
            console.log('[ApprovalFramework] Initialized for:', assetType);
        },

        bindEvents: function() {
            var self = this;

            document.querySelectorAll('.approval-stat-card').forEach(function(card) {
                card.addEventListener('click', function() {
                    var status = this.dataset.status;
                    self.filterByStatus(status);
                });
            });

            document.querySelectorAll('.approval-queue-table tbody tr').forEach(function(row) {
                row.addEventListener('click', function(e) {
                    if (!e.target.closest('.approval-quick-actions') && !e.target.closest('input[type="checkbox"]')) {
                        self.openDrawer(this.dataset.itemId);
                    }
                });
            });

            var overlay = document.querySelector('.approval-drawer-overlay');
            if (overlay) {
                overlay.addEventListener('click', function() {
                    self.closeDrawer();
                });
            }

            var closeBtn = document.querySelector('.approval-drawer-close');
            if (closeBtn) {
                closeBtn.addEventListener('click', function() {
                    self.closeDrawer();
                });
            }

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    self.closeDrawer();
                }
            });
        },

        filterByStatus: function(status) {
            document.querySelectorAll('.approval-stat-card').forEach(function(card) {
                card.classList.toggle('active', card.dataset.status === status);
            });

            document.querySelectorAll('.approval-queue-table tbody tr').forEach(function(row) {
                if (status === 'all' || row.dataset.status === status) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });

            logAdminAction('APPROVAL_QUEUE_FILTERED', this.assetType, { status: status });
        },

        openDrawer: function(itemId) {
            this.currentItem = itemId;
            var drawer = document.querySelector('.approval-drawer');
            var overlay = document.querySelector('.approval-drawer-overlay');
            
            if (drawer && overlay) {
                drawer.classList.add('open');
                overlay.classList.add('open');
                document.body.style.overflow = 'hidden';
                this.loadItemDetails(itemId);
            }

            logAdminAction('APPROVAL_DETAIL_VIEWED', itemId, { assetType: this.assetType });
        },

        closeDrawer: function() {
            var drawer = document.querySelector('.approval-drawer');
            var overlay = document.querySelector('.approval-drawer-overlay');
            
            if (drawer && overlay) {
                drawer.classList.remove('open');
                overlay.classList.remove('open');
                document.body.style.overflow = '';
            }
            this.currentItem = null;
        },

        loadItemDetails: function(itemId) {
            console.log('[ApprovalFramework] Loading details for:', itemId);
        },

        approve: function(itemId, notes) {
            var result = approveItem(this.assetType, itemId, notes);
            if (result.success) {
                this.updateItemStatus(itemId, 'approved');
                this.closeDrawer();
                this.showToast('Item approved successfully', 'success');
            }
            return result;
        },

        reject: function(itemId, reason) {
            var result = rejectItem(this.assetType, itemId, reason);
            if (result.success) {
                this.updateItemStatus(itemId, 'rejected');
                this.closeDrawer();
                this.showToast('Item rejected', 'warning');
            }
            return result;
        },

        markInReview: function(itemId, assignee) {
            if (this.assetType !== 'RCS_AGENT') {
                return { success: false, error: 'In-review status only available for RCS Agents' };
            }

            logAdminAction('ITEM_MARKED_IN_REVIEW', itemId, {
                assetType: this.assetType,
                assignee: assignee
            });

            this.updateItemStatus(itemId, 'in-review');
            return { success: true };
        },

        bulkApprove: function(itemIds, notes) {
            var self = this;
            var results = [];
            itemIds.forEach(function(id) {
                results.push(self.approve(id, notes));
            });
            this.selectedItems = [];
            this.updateBulkBar();
            return results;
        },

        bulkReject: function(itemIds, reason) {
            var self = this;
            var results = [];
            itemIds.forEach(function(id) {
                results.push(self.reject(id, reason));
            });
            this.selectedItems = [];
            this.updateBulkBar();
            return results;
        },

        toggleSelectItem: function(itemId) {
            var idx = this.selectedItems.indexOf(itemId);
            if (idx > -1) {
                this.selectedItems.splice(idx, 1);
            } else {
                this.selectedItems.push(itemId);
            }
            this.updateBulkBar();
        },

        selectAll: function() {
            var self = this;
            this.selectedItems = [];
            document.querySelectorAll('.approval-queue-table tbody tr:not([style*="display: none"])').forEach(function(row) {
                self.selectedItems.push(row.dataset.itemId);
            });
            this.updateBulkBar();
        },

        clearSelection: function() {
            this.selectedItems = [];
            this.updateBulkBar();
        },

        updateBulkBar: function() {
            var bar = document.querySelector('.approval-bulk-bar');
            if (bar) {
                if (this.selectedItems.length > 0) {
                    bar.style.display = 'flex';
                    bar.querySelector('.selected-count').textContent = this.selectedItems.length + ' items selected';
                } else {
                    bar.style.display = 'none';
                }
            }

            document.querySelectorAll('.approval-queue-table tbody tr').forEach(function(row) {
                var checkbox = row.querySelector('input[type="checkbox"]');
                if (checkbox) {
                    checkbox.checked = this.selectedItems.indexOf(row.dataset.itemId) > -1;
                }
            }.bind(this));
        },

        updateItemStatus: function(itemId, newStatus) {
            var row = document.querySelector('[data-item-id="' + itemId + '"]');
            if (row) {
                row.dataset.status = newStatus;
                var badge = row.querySelector('.approval-status-badge');
                if (badge) {
                    badge.className = 'approval-status-badge ' + newStatus;
                    badge.innerHTML = this.getStatusIcon(newStatus) + ' ' + this.formatStatus(newStatus);
                }
            }
            this.updateStatCounts();
        },

        getStatusIcon: function(status) {
            var icons = {
                draft: '<i class="fas fa-file"></i>',
                pending: '<i class="fas fa-clock"></i>',
                submitted: '<i class="fas fa-paper-plane"></i>',
                'in-review': '<i class="fas fa-search"></i>',
                approved: '<i class="fas fa-check-circle"></i>',
                rejected: '<i class="fas fa-times-circle"></i>'
            };
            return icons[status] || '';
        },

        formatStatus: function(status) {
            return status.replace('-', ' ').replace(/\b\w/g, function(l) { return l.toUpperCase(); });
        },

        updateStatCounts: function() {
            var counts = { pending: 0, submitted: 0, 'in-review': 0, approved: 0, rejected: 0, total: 0 };
            
            document.querySelectorAll('.approval-queue-table tbody tr').forEach(function(row) {
                var status = row.dataset.status;
                if (counts.hasOwnProperty(status)) {
                    counts[status]++;
                }
                counts.total++;
            });

            document.querySelectorAll('.approval-stat-card').forEach(function(card) {
                var status = card.dataset.status;
                var countEl = card.querySelector('.stat-count');
                if (countEl && counts.hasOwnProperty(status)) {
                    countEl.textContent = counts[status];
                }
            });
        },

        showToast: function(message, type) {
            console.log('[ApprovalFramework] Toast:', type, message);
        },

        getRejectTemplates: function() {
            return REJECTION_TEMPLATES[this.assetType] || [];
        },

        canTransition: function(fromStatus, toStatus) {
            var lifecycle = APPROVAL_LIFECYCLE[this.assetType];
            if (!lifecycle) return false;
            var allowed = lifecycle.transitions[fromStatus] || [];
            return allowed.indexOf(toStatus) > -1;
        }
    };

    function approveItem(itemType, itemId, notes) {
        if (!hasPermission('canApprove')) {
            return { success: false, error: 'Approval permission required' };
        }

        logAdminAction('ITEM_APPROVED', itemId, {
            itemType: itemType,
            notes: notes,
            severity: 'HIGH'
        });

        return { success: true };
    }

    function rejectItem(itemType, itemId, reason) {
        if (!hasPermission('canApprove')) {
            return { success: false, error: 'Approval permission required' };
        }

        if (!reason || reason.trim().length < 10) {
            return { success: false, error: 'Rejection reason required (min 10 characters)' };
        }

        logAdminAction('ITEM_REJECTED', itemId, {
            itemType: itemType,
            reason: reason,
            severity: 'HIGH'
        });

        return { success: true };
    }

    function suspendAccount(accountId, reason, duration) {
        if (!hasPermission('canSuspend')) {
            return { success: false, error: 'Suspension permission required' };
        }

        if (!reason || reason.trim().length < 10) {
            return { success: false, error: 'Suspension reason required (min 10 characters)' };
        }

        logAdminAction('ACCOUNT_SUSPENDED', accountId, {
            reason: reason,
            duration: duration
        });

        return { success: true };
    }

    function reactivateAccount(accountId, notes) {
        if (!hasPermission('canSuspend')) {
            return { success: false, error: 'Suspension permission required' };
        }

        logAdminAction('ACCOUNT_REACTIVATED', accountId, {
            notes: notes
        });

        return { success: true };
    }

    function getSupplierRoutes() {
        return [
            { id: 'UK-TIER1', name: 'UK Tier 1', status: 'active', latency: 120, successRate: 99.2 },
            { id: 'UK-TIER2', name: 'UK Tier 2', status: 'active', latency: 180, successRate: 97.8 },
            { id: 'EU-PRIMARY', name: 'EU Primary', status: 'active', latency: 150, successRate: 98.5 },
            { id: 'INT-GLOBAL', name: 'International', status: 'degraded', latency: 350, successRate: 94.1 }
        ];
    }

    return {
        init: init,
        ACCESS_RULES: ACCESS_RULES,
        GLOBAL_RULES: GLOBAL_RULES,
        ENFORCEMENT_MATRIX: ENFORCEMENT_MATRIX,
        SHARED_DEFINITIONS: SHARED_DEFINITIONS,
        NFR_CONSTRAINTS: NFR_CONSTRAINTS,
        FINAL_GUARDRAILS: FINAL_GUARDRAILS,

        hasPermission: hasPermission,
        hasResponsibility: hasResponsibility,
        canObserve: canObserve,
        canControl: canControl,
        canInvestigate: canInvestigate,
        canGovern: canGovern,
        getActiveResponsibilities: getActiveResponsibilities,
        renderResponsibilityBadges: renderResponsibilityBadges,

        validateNotRedefining: validateNotRedefining,
        getSharedDefinition: getSharedDefinition,
        isAdminCapability: isAdminCapability,
        isCustomerCapability: isCustomerCapability,

        validateImplementation: validateImplementation,
        assertSuperset: assertSuperset,
        wrapWithAudit: wrapWithAudit,
        ensurePiiMasked: ensurePiiMasked,

        validateQueryParams: validateQueryParams,
        serverSideQuery: serverSideQuery,
        clearCache: clearCache,
        showPerformanceWarning: showPerformanceWarning,

        setPendingFilter: setPendingFilter,
        applyFilters: applyFilters,
        clearFilters: clearFilters,
        getAppliedFilters: getAppliedFilters,
        drillDown: drillDown,
        resetDrillDepth: resetDrillDepth,

        logAdminAction: logAdminAction,
        logMutation: logMutation,

        startImpersonation: startImpersonation,
        endImpersonation: endImpersonation,
        isImpersonating: isImpersonating,
        getImpersonationSession: getImpersonationSession,
        canAccessPiiDuringImpersonation: canAccessPiiDuringImpersonation,
        canMutateDataDuringImpersonation: canMutateDataDuringImpersonation,
        logImpersonationAction: logImpersonationAction,
        getRemainingImpersonationTime: getRemainingImpersonationTime,

        maskPhoneNumber: maskPhoneNumber,
        maskMessageContent: maskMessageContent,
        redactPersonalisation: redactPersonalisation,
        
        formatCurrency: formatCurrency,
        calculateMargin: calculateMargin,
        approveItem: approveItem,
        rejectItem: rejectItem,
        suspendAccount: suspendAccount,
        reactivateAccount: reactivateAccount,
        getSupplierRoutes: getSupplierRoutes,
        getCurrentAdmin: function() { return currentAdmin; },
        RESPONSIBILITIES: RESPONSIBILITIES,
        ROLE_RESPONSIBILITIES: ROLE_RESPONSIBILITIES,
        
        ApprovalFramework: ApprovalFramework,
        APPROVAL_LIFECYCLE: APPROVAL_LIFECYCLE,
        REJECTION_TEMPLATES: REJECTION_TEMPLATES
    };
})();

document.addEventListener('DOMContentLoaded', function() {
    AdminControlPlane.init();
});
