var AdminControlPlane = (function() {
    'use strict';

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

    var ADMIN_PERMISSIONS = {
        'super_admin': {
            canApprove: true,
            canSuspend: true,
            canOverride: true,
            canImpersonate: true,
            canViewFinancials: true,
            canModifyPricing: true,
            canAccessAudit: true,
            canExportData: true
        },
        'support': {
            canApprove: false,
            canSuspend: false,
            canOverride: false,
            canImpersonate: true,
            canViewFinancials: false,
            canModifyPricing: false,
            canAccessAudit: true,
            canExportData: false
        },
        'finance': {
            canApprove: false,
            canSuspend: false,
            canOverride: false,
            canImpersonate: false,
            canViewFinancials: true,
            canModifyPricing: false,
            canAccessAudit: true,
            canExportData: true
        },
        'compliance': {
            canApprove: true,
            canSuspend: true,
            canOverride: false,
            canImpersonate: false,
            canViewFinancials: false,
            canModifyPricing: false,
            canAccessAudit: true,
            canExportData: true
        }
    };

    function init() {
        console.log('[AdminControlPlane] Initialized for:', currentAdmin.email);
        updateAdminDisplay();
        bindEvents();
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

    function logAdminAction(action, target, details) {
        var entry = {
            timestamp: new Date().toISOString(),
            adminId: currentAdmin.id,
            adminEmail: currentAdmin.email,
            adminRole: currentAdmin.role,
            action: action,
            target: target,
            details: details,
            ipAddress: currentAdmin.ipAddress || 'unknown',
            impersonating: impersonationSession ? impersonationSession.accountId : null
        };

        console.log('[ADMIN_AUDIT]', JSON.stringify(entry, null, 2));

        return entry;
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
        hasPermission: hasPermission,
        logAdminAction: logAdminAction,
        startImpersonation: startImpersonation,
        endImpersonation: endImpersonation,
        isImpersonating: isImpersonating,
        maskPhoneNumber: maskPhoneNumber,
        maskMessageContent: maskMessageContent,
        formatCurrency: formatCurrency,
        calculateMargin: calculateMargin,
        approveItem: approveItem,
        rejectItem: rejectItem,
        suspendAccount: suspendAccount,
        reactivateAccount: reactivateAccount,
        getSupplierRoutes: getSupplierRoutes,
        getCurrentAdmin: function() { return currentAdmin; }
    };
})();

document.addEventListener('DOMContentLoaded', function() {
    AdminControlPlane.init();
});
