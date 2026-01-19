var AdminControlPlane = (function() {
    'use strict';

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
            requiredFields: ['adminUser', 'action', 'targetAccount', 'beforeValues', 'afterValues', 'timestamp']
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
            responsiveUnderWideFilters: true
        },
        performance: {
            heavyQueriesServerSide: true,
            aggregationsIndexed: true,
            cachingReadOnlyOnly: true,
            maxClientSideRecords: 1000,
            paginationRequired: true,
            serverSideFilteringRequired: true
        },
        queryLimits: {
            maxDateRangeDays: 365,
            defaultPageSize: 50,
            maxPageSize: 100,
            warnOnWideFilter: true,
            requireDateRange: true
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
            EXPOSES_PII_BY_DEFAULT: 'exposes_pii_by_default'
        },
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
        console.log('[AdminControlPlane] Initialized for:', currentAdmin.email);
        console.log('[AdminControlPlane] Global Rules:', GLOBAL_RULES);
        updateAdminDisplay();
        bindEvents();
        initFilterSystem();
        initPIIProtection();
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
        var entry = {
            timestamp: new Date().toISOString(),
            adminUser: {
                id: currentAdmin.id,
                email: currentAdmin.email,
                role: currentAdmin.role,
                name: currentAdmin.name
            },
            action: action,
            targetAccount: target,
            details: details || {},
            beforeValues: beforeValues || null,
            afterValues: afterValues || null,
            ipAddress: currentAdmin.ipAddress || 'unknown',
            impersonating: impersonationSession ? impersonationSession.accountId : null,
            sessionId: currentAdmin.sessionStart
        };

        if (GLOBAL_RULES.audit.requireBeforeAfterValues) {
            var isMutation = ['CREATE', 'UPDATE', 'DELETE', 'APPROVE', 'REJECT', 'SUSPEND', 'REACTIVATE', 'OVERRIDE'].some(function(m) {
                return action.toUpperCase().indexOf(m) !== -1;
            });
            
            if (isMutation && (!beforeValues && !afterValues)) {
                console.warn('[AdminControlPlane] Mutation action without before/after values:', action);
            }
        }

        console.log('[ADMIN_AUDIT]', JSON.stringify(entry, null, 2));

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

    function startImpersonation(accountId, accountName, reason) {
        if (!hasPermission('canImpersonate')) {
            console.error('[AdminControlPlane] Impersonation not permitted for role:', currentAdmin.role);
            return { success: false, error: 'Permission denied' };
        }

        if (!reason || reason.trim().length < 10) {
            return { success: false, error: 'Reason required (min 10 characters)' };
        }

        impersonationSession = {
            accountId: accountId,
            accountName: accountName,
            reason: reason,
            startedAt: new Date().toISOString(),
            adminId: currentAdmin.id
        };

        logAdminAction('IMPERSONATION_START', accountId, {
            accountName: accountName,
            reason: reason
        });

        showImpersonationBanner(accountName);

        return { success: true, session: impersonationSession };
    }

    function endImpersonation() {
        if (!impersonationSession) return;

        logAdminAction('IMPERSONATION_END', impersonationSession.accountId, {
            duration: Date.now() - new Date(impersonationSession.startedAt).getTime()
        });

        impersonationSession = null;
        hideImpersonationBanner();
    }

    function showImpersonationBanner(accountName) {
        var existingBanner = document.querySelector('.admin-impersonate-banner');
        if (existingBanner) existingBanner.remove();

        var banner = document.createElement('div');
        banner.className = 'admin-impersonate-banner';
        banner.innerHTML = '<div><i class="fas fa-eye me-2"></i><strong>Impersonating:</strong> ' + accountName + 
            ' <span class="ms-2">(View Only - Changes will NOT affect customer audit log)</span></div>' +
            '<button class="btn-exit" onclick="AdminControlPlane.endImpersonation()"><i class="fas fa-times me-1"></i>Exit</button>';

        var contentBody = document.querySelector('.content-body');
        if (contentBody) {
            contentBody.insertBefore(banner, contentBody.firstChild);
        }
    }

    function hideImpersonationBanner() {
        var banner = document.querySelector('.admin-impersonate-banner');
        if (banner) banner.remove();
    }

    function isImpersonating() {
        return impersonationSession !== null;
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

    function approveItem(itemType, itemId, notes) {
        if (!hasPermission('canApprove')) {
            return { success: false, error: 'Approval permission required' };
        }

        logAdminAction('ITEM_APPROVED', itemId, {
            itemType: itemType,
            notes: notes
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
            reason: reason
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
        ROLE_RESPONSIBILITIES: ROLE_RESPONSIBILITIES
    };
})();

document.addEventListener('DOMContentLoaded', function() {
    AdminControlPlane.init();
});
