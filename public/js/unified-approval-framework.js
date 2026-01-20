var UNIFIED_APPROVAL = (function() {
    'use strict';

    var ENTITY_TYPES = {
        SENDER_ID: {
            code: 'sender_id',
            label: 'SenderID',
            prefix: 'SID',
            externalProvider: 'brandassure',
            slaHours: 24,
            icon: 'fa-id-card',
            color: '#886cc0'
        },
        RCS_AGENT: {
            code: 'rcs_agent',
            label: 'RCS Agent',
            prefix: 'RCS',
            externalProvider: 'rcs_provider',
            slaHours: 48,
            icon: 'fa-robot',
            color: '#4a90d9'
        }
    };

    var LIFECYCLE_STATUSES = {
        SUBMITTED: { code: 'submitted', label: 'Submitted', order: 1, icon: 'fa-paper-plane', class: 'submitted', terminal: false },
        IN_REVIEW: { code: 'in_review', label: 'In Review', order: 2, icon: 'fa-search', class: 'in-review', terminal: false },
        RETURNED: { code: 'returned', label: 'Returned to Customer', order: 3, icon: 'fa-reply', class: 'returned', terminal: false },
        RESUBMITTED: { code: 'resubmitted', label: 'Resubmitted', order: 4, icon: 'fa-redo', class: 'resubmitted', terminal: false },
        VALIDATION_IN_PROGRESS: { code: 'validation_in_progress', label: 'Validation In Progress', order: 5, icon: 'fa-spinner fa-spin', class: 'validation-in-progress', terminal: false },
        VALIDATION_FAILED: { code: 'validation_failed', label: 'Validation Failed', order: 6, icon: 'fa-exclamation-circle', class: 'validation-failed', terminal: false },
        APPROVED: { code: 'approved', label: 'Approved', order: 7, icon: 'fa-check-circle', class: 'approved', terminal: false },
        REJECTED: { code: 'rejected', label: 'Rejected', order: 8, icon: 'fa-times-circle', class: 'rejected', terminal: true },
        PROVISIONING: { code: 'provisioning', label: 'Provisioning In Progress', order: 9, icon: 'fa-cog fa-spin', class: 'provisioning', terminal: false },
        LIVE: { code: 'live', label: 'Live', order: 10, icon: 'fa-broadcast-tower', class: 'live', terminal: true }
    };

    var RETURN_REASON_CODES = {
        INCOMPLETE_INFO: { code: 'INCOMPLETE_INFO', label: 'Incomplete Information', description: 'Required fields or documentation missing' },
        ASSET_QUALITY: { code: 'ASSET_QUALITY', label: 'Asset Quality Issues', description: 'Images do not meet quality requirements' },
        BRAND_CLARIFICATION: { code: 'BRAND_CLARIFICATION', label: 'Brand Clarification Needed', description: 'Brand ownership or authorization needs verification' },
        USE_CASE_UNCLEAR: { code: 'USE_CASE_UNCLEAR', label: 'Use Case Unclear', description: 'Messaging purpose requires clarification' },
        COMPLIANCE_ISSUE: { code: 'COMPLIANCE_ISSUE', label: 'Compliance Issue', description: 'Legal or regulatory requirements not met' },
        OPTIN_OPTOUT_MISSING: { code: 'OPTIN_OPTOUT_MISSING', label: 'Opt-in/Opt-out Missing', description: 'Consent mechanism not properly documented' },
        VOLUME_JUSTIFICATION: { code: 'VOLUME_JUSTIFICATION', label: 'Volume Justification Required', description: 'Estimated volume needs supporting evidence' },
        OTHER: { code: 'OTHER', label: 'Other (Specify)', description: 'Custom reason - requires explanation' }
    };

    var REJECTION_REASON_CODES = {
        BRAND_IMPERSONATION: { code: 'BRAND_IMPERSONATION', label: 'Brand Impersonation', description: 'Attempt to impersonate another brand' },
        TRADEMARK_VIOLATION: { code: 'TRADEMARK_VIOLATION', label: 'Trademark Violation', description: 'Infringes registered trademark' },
        PROHIBITED_CONTENT: { code: 'PROHIBITED_CONTENT', label: 'Prohibited Content', description: 'Content violates acceptable use policy' },
        REGULATORY_NON_COMPLIANCE: { code: 'REGULATORY_NON_COMPLIANCE', label: 'Regulatory Non-Compliance', description: 'Does not meet regulatory requirements' },
        FRAUDULENT_ACTIVITY: { code: 'FRAUDULENT_ACTIVITY', label: 'Suspected Fraud', description: 'Evidence of fraudulent intent' },
        REPEATED_VIOLATIONS: { code: 'REPEATED_VIOLATIONS', label: 'Repeated Violations', description: 'Multiple previous violations on record' }
    };

    var EXTERNAL_PROVIDERS = {
        brandassure: {
            name: 'BrandAssure',
            description: 'Brand verification service for SenderIDs',
            statuses: {
                PENDING: { label: 'Pending', class: 'pending', icon: 'fa-clock' },
                SUBMITTED: { label: 'Submitted', class: 'submitted', icon: 'fa-paper-plane' },
                IN_PROGRESS: { label: 'In Progress', class: 'in-progress', icon: 'fa-spinner fa-spin' },
                VERIFIED: { label: 'Verified', class: 'verified', icon: 'fa-check-circle' },
                FAILED: { label: 'Failed', class: 'failed', icon: 'fa-times-circle' },
                TIMEOUT: { label: 'Timeout', class: 'timeout', icon: 'fa-exclamation-triangle' }
            }
        },
        rcs_provider: {
            name: 'RCS Provider',
            description: 'RCS agent registration and provisioning',
            statuses: {
                PENDING: { label: 'Pending', class: 'pending', icon: 'fa-clock' },
                SUBMITTED: { label: 'Submitted', class: 'submitted', icon: 'fa-paper-plane' },
                REVIEW: { label: 'Under Review', class: 'review', icon: 'fa-search' },
                APPROVED: { label: 'Approved', class: 'approved', icon: 'fa-check-circle' },
                REJECTED: { label: 'Rejected', class: 'rejected', icon: 'fa-times-circle' },
                PROVISIONING: { label: 'Provisioning', class: 'provisioning', icon: 'fa-cog fa-spin' },
                LIVE: { label: 'Live', class: 'live', icon: 'fa-broadcast-tower' },
                FAILED: { label: 'Failed', class: 'failed', icon: 'fa-times-circle' }
            }
        }
    };

    var HIGH_RISK_KEYWORDS = ['BANK', 'NHS', 'HMRC', 'GOV', 'POLICE', 'COURT', 'TAX', 'DVLA'];
    var HIGH_RISK_VERTICALS = ['Financial Services', 'Healthcare', 'Government', 'Legal'];

    var currentEntity = null;
    var versionHistory = [];
    var externalValidationHistory = [];

    function init(config) {
        if (!config.entityType || !ENTITY_TYPES[config.entityType]) {
            console.error('[UnifiedApproval] Invalid entity type:', config.entityType);
            return;
        }

        currentEntity = {
            type: ENTITY_TYPES[config.entityType],
            id: config.entityId,
            currentVersion: config.currentVersion || 1,
            currentStatus: config.currentStatus || 'submitted',
            accountId: config.accountId,
            accountName: config.accountName,
            submittedBy: config.submittedBy,
            submittedAt: config.submittedAt,
            data: config.entityData || {}
        };

        versionHistory = config.versionHistory || [];
        if (versionHistory.length === 0 && config.entityData) {
            versionHistory.push(createVersion(1, config.entityData, 'submitted'));
        }

        externalValidationHistory = config.externalValidationHistory || [];

        console.log('[UnifiedApproval] Initialized for', currentEntity.type.label, currentEntity.id);
        
        checkHighRisk();
        updateUI();
        
        return currentEntity;
    }

    function createVersion(versionNumber, data, status) {
        return {
            version: versionNumber,
            timestamp: new Date().toISOString(),
            data: JSON.parse(JSON.stringify(data)),
            status: status,
            immutable: true
        };
    }

    function checkHighRisk() {
        var isHighRisk = false;
        var riskReasons = [];

        var entityValue = currentEntity.data.value || currentEntity.data.name || '';
        HIGH_RISK_KEYWORDS.forEach(function(keyword) {
            if (entityValue.toUpperCase().indexOf(keyword) !== -1) {
                isHighRisk = true;
                riskReasons.push('Contains regulated term: ' + keyword);
            }
        });

        var vertical = currentEntity.data.vertical || currentEntity.data.billingCategory || '';
        if (HIGH_RISK_VERTICALS.indexOf(vertical) !== -1) {
            isHighRisk = true;
            riskReasons.push('High-risk vertical: ' + vertical);
        }

        if (isHighRisk && typeof ADMIN_NOTIFICATIONS !== 'undefined') {
            ADMIN_NOTIFICATIONS.triggerInternalAlert('HIGH_RISK', currentEntity.id, riskReasons.join('; '));
        }

        currentEntity.isHighRisk = isHighRisk;
        currentEntity.riskReasons = riskReasons;

        return isHighRisk;
    }

    function transitionStatus(newStatus, reason, externalRefs) {
        var previousStatus = currentEntity.currentStatus;
        
        if (!isValidTransition(previousStatus, newStatus)) {
            console.error('[UnifiedApproval] Invalid transition:', previousStatus, '->', newStatus);
            return { success: false, error: 'Invalid status transition' };
        }

        currentEntity.currentStatus = newStatus;

        if (typeof ADMIN_AUDIT !== 'undefined') {
            ADMIN_AUDIT.logStatusTransition(
                currentEntity.type.code,
                currentEntity.id,
                previousStatus,
                newStatus,
                reason,
                externalRefs || {}
            );
        }

        updateUI();

        return { 
            success: true, 
            previousStatus: previousStatus, 
            newStatus: newStatus 
        };
    }

    function isValidTransition(from, to) {
        var validTransitions = {
            submitted: ['in_review', 'returned', 'rejected'],
            in_review: ['returned', 'validation_in_progress', 'approved', 'rejected'],
            returned: ['resubmitted'],
            resubmitted: ['in_review', 'validation_in_progress', 'returned', 'rejected'],
            validation_in_progress: ['validation_failed', 'approved', 'rejected'],
            validation_failed: ['validation_in_progress', 'approved', 'rejected', 'returned'],
            approved: ['provisioning', 'live'],
            provisioning: ['live', 'validation_failed'],
            rejected: [],
            live: []
        };

        return validTransitions[from] && validTransitions[from].indexOf(to) !== -1;
    }

    function approve(reason) {
        var result = transitionStatus('approved', reason || 'Manual approval by admin', getExternalRefs());
        
        if (result.success) {
            showCustomerNotification('APPROVED');
        }
        
        return result;
    }

    function reject(reasonCode, customMessage) {
        var reasonInfo = REJECTION_REASON_CODES[reasonCode] || { label: reasonCode };
        var fullReason = reasonInfo.label + (customMessage ? ': ' + customMessage : '');
        
        var result = transitionStatus('rejected', fullReason, getExternalRefs());
        
        if (result.success) {
            showCustomerNotification('REJECTED', customMessage);
        }
        
        return result;
    }

    function returnToCustomer(reasonCode, guidance) {
        var reasonInfo = RETURN_REASON_CODES[reasonCode] || { label: reasonCode };
        var fullReason = reasonInfo.label + (guidance ? ': ' + guidance : '');
        
        versionHistory.push(createVersion(currentEntity.currentVersion, currentEntity.data, 'returned'));
        
        var result = transitionStatus('returned', fullReason, {});
        
        if (result.success) {
            lockApprovalActions();
            showCustomerNotification('RETURNED', guidance);
        }
        
        return result;
    }

    function forceApprove(reason) {
        if (!reason || reason.trim().length < 10) {
            return { success: false, error: 'Reason required (min 10 characters) for force approve' };
        }

        if (typeof ADMIN_AUDIT !== 'undefined') {
            ADMIN_AUDIT.logForceApprove(
                currentEntity.type.code,
                currentEntity.id,
                reason,
                currentEntity.currentStatus
            );
        }

        currentEntity.currentStatus = 'approved';
        updateUI();

        return { success: true, bypassedValidation: true };
    }

    function provision() {
        var result = transitionStatus('provisioning', 'Provisioning initiated', getExternalRefs());
        
        if (result.success) {
            setTimeout(function() {
                var liveResult = transitionStatus('live', 'Provisioning completed successfully', getExternalRefs());
                if (liveResult.success) {
                    showCustomerNotification('LIVE');
                }
            }, 2000);
        }
        
        return result;
    }

    function handleResubmission(newData) {
        var previousVersion = currentEntity.currentVersion;
        currentEntity.currentVersion++;
        currentEntity.data = newData;

        versionHistory.push(createVersion(currentEntity.currentVersion, newData, 'resubmitted'));
        
        var result = transitionStatus('resubmitted', 'Customer resubmission v' + currentEntity.currentVersion, {});
        
        if (result.success) {
            unlockApprovalActions();
            resetSlaTimer();
            showDiffView(previousVersion, currentEntity.currentVersion);
        }
        
        return result;
    }

    function submitToExternalProvider(entityData) {
        var provider = currentEntity.type.externalProvider;
        var providerInfo = EXTERNAL_PROVIDERS[provider];
        
        var requestId = (provider === 'brandassure' ? 'BASRQ-' : 'RCSP-') + 
                        Math.random().toString(36).substring(2, 10).toUpperCase();

        var entry = {
            id: provider.toUpperCase() + '-' + Date.now(),
            provider: provider,
            externalRequestId: requestId,
            timestamp: new Date().toISOString(),
            status: 'SUBMITTED',
            payloadSent: entityData,
            responseCode: null,
            responseMessage: null,
            rawResponse: null,
            callbacks: []
        };

        externalValidationHistory.push(entry);

        if (typeof ADMIN_AUDIT !== 'undefined') {
            ADMIN_AUDIT.logExternalValidation(
                currentEntity.type.code,
                currentEntity.id,
                providerInfo.name,
                requestId,
                'submit',
                entityData,
                null
            );
        }

        transitionStatus('validation_in_progress', 'Submitted to ' + providerInfo.name, { externalRequestId: requestId });

        renderExternalValidationSection();

        setTimeout(function() {
            simulateExternalResponse(entry.id, true);
        }, 2000);

        return entry;
    }

    function simulateExternalResponse(entryId, success) {
        var entry = externalValidationHistory.find(function(e) { return e.id === entryId; });
        if (!entry) return;

        var providerInfo = EXTERNAL_PROVIDERS[entry.provider];

        entry.status = success ? 'VERIFIED' : 'FAILED';
        entry.responseCode = success ? 200 : 422;
        entry.responseMessage = success ? 'Verification successful' : 'Verification failed';
        entry.rawResponse = {
            requestId: entry.externalRequestId,
            status: entry.status,
            timestamp: new Date().toISOString(),
            confidence: success ? 0.95 : 0.12,
            processingTime: '1.234s',
            _internal: {
                nodeId: entry.provider + '-node-eu-west-1a',
                version: '2.4.1'
            }
        };

        if (typeof ADMIN_AUDIT !== 'undefined') {
            ADMIN_AUDIT.logExternalValidation(
                currentEntity.type.code,
                currentEntity.id,
                providerInfo.name,
                entry.externalRequestId,
                'response',
                null,
                { code: entry.responseCode, status: entry.status }
            );
        }

        if (!success) {
            transitionStatus('validation_failed', 'External validation failed: ' + entry.responseMessage, { externalRequestId: entry.externalRequestId });
            
            if (typeof ADMIN_NOTIFICATIONS !== 'undefined') {
                ADMIN_NOTIFICATIONS.triggerInternalAlert('VALIDATION_FAILED', entry.externalRequestId, entry.responseMessage);
            }
        }

        renderExternalValidationSection();
    }

    function handleExternalCallback(callbackData) {
        var entry = externalValidationHistory.find(function(e) { 
            return e.externalRequestId === callbackData.referenceId; 
        });
        
        if (!entry) {
            console.error('[UnifiedApproval] No entry found for callback reference:', callbackData.referenceId);
            return;
        }

        var callback = {
            timestamp: new Date().toISOString(),
            status: callbackData.status,
            rawPayload: callbackData
        };

        entry.callbacks.push(callback);
        entry.status = callbackData.status;

        renderExternalValidationSection();
    }

    function getExternalRefs() {
        if (externalValidationHistory.length === 0) return {};
        var latest = externalValidationHistory[externalValidationHistory.length - 1];
        return { externalRequestId: latest.externalRequestId };
    }

    function showCustomerNotification(notificationType, customMessage) {
        if (typeof ADMIN_NOTIFICATIONS !== 'undefined') {
            ADMIN_NOTIFICATIONS.showCustomerNotificationModal(
                notificationType,
                currentEntity.id,
                currentEntity.type.label,
                currentEntity.submittedBy,
                customMessage
            );
        }
    }

    function lockApprovalActions() {
        document.querySelectorAll('.action-btn').forEach(function(btn) {
            var btnText = btn.textContent.toLowerCase();
            if (btnText.includes('approve') || btnText.includes('reject') || 
                btnText.includes('submit to') || btnText.includes('provision')) {
                btn.disabled = true;
                btn.classList.add('action-locked');
                btn.title = 'Action locked - awaiting customer resubmission';
            }
        });

        showLockNotice();
    }

    function unlockApprovalActions() {
        document.querySelectorAll('.action-btn.action-locked').forEach(function(btn) {
            btn.disabled = false;
            btn.classList.remove('action-locked');
            btn.title = '';
        });

        hideLockNotice();
    }

    function showLockNotice() {
        if (document.getElementById('actionLockNotice')) return;

        var notice = document.createElement('div');
        notice.id = 'actionLockNotice';
        notice.className = 'action-lock-notice';
        notice.innerHTML = '<i class="fas fa-lock me-2"></i>Approval actions locked. Awaiting customer resubmission.';
        
        var actionPanel = document.querySelector('.action-panel-title');
        if (actionPanel) {
            actionPanel.parentNode.insertBefore(notice, actionPanel.nextSibling);
        }
    }

    function hideLockNotice() {
        var notice = document.getElementById('actionLockNotice');
        if (notice) notice.remove();
    }

    function resetSlaTimer() {
        var slaElement = document.querySelector('[class*="sla"]');
        if (slaElement) {
            slaElement.innerHTML = '<i class="fas fa-hourglass-start me-1" style="color: #22c55e;"></i>' + 
                                   currentEntity.type.slaHours + 'h (Reset)';
            slaElement.style.color = '#22c55e';
        }
    }

    function updateUI() {
        updateStatusPill();
        updateVersionIndicator();
    }

    function updateStatusPill() {
        var pill = document.getElementById('currentStatus');
        if (!pill) return;

        var statusInfo = LIFECYCLE_STATUSES[currentEntity.currentStatus.toUpperCase()] || 
                        Object.values(LIFECYCLE_STATUSES).find(function(s) { 
                            return s.code === currentEntity.currentStatus; 
                        });

        if (statusInfo) {
            pill.className = 'status-pill ' + statusInfo.class;
            pill.innerHTML = '<i class="fas ' + statusInfo.icon + '"></i> ' + statusInfo.label;
        }
    }

    function updateVersionIndicator() {
        var indicator = document.getElementById('versionIndicator');
        if (!indicator) {
            var statusHeader = document.querySelector('.status-header');
            if (statusHeader) {
                var versionHtml = '<span class="version-indicator" id="versionIndicator">v' + currentEntity.currentVersion + '</span>';
                statusHeader.insertAdjacentHTML('beforeend', versionHtml);
                indicator = document.getElementById('versionIndicator');
            }
        }
        
        if (indicator) {
            indicator.textContent = 'v' + currentEntity.currentVersion;
            if (currentEntity.currentVersion > 1) {
                indicator.classList.add('has-history');
                indicator.onclick = function() { showVersionHistory(); };
                indicator.title = 'Click to view version history';
            }
        }
    }

    function showVersionHistory() {
        var modal = document.getElementById('versionHistoryModal');
        if (!modal) {
            createVersionHistoryModal();
            modal = document.getElementById('versionHistoryModal');
        }
        
        populateVersionHistory();
        new bootstrap.Modal(modal).show();
    }

    function createVersionHistoryModal() {
        var modalHtml = '\
        <div class="modal fade" id="versionHistoryModal" tabindex="-1">\
            <div class="modal-dialog modal-lg">\
                <div class="modal-content">\
                    <div class="modal-header">\
                        <h5 class="modal-title"><i class="fas fa-history me-2"></i>Version History</h5>\
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>\
                    </div>\
                    <div class="modal-body">\
                        <div class="version-timeline" id="versionTimeline"></div>\
                    </div>\
                    <div class="modal-footer">\
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>\
                    </div>\
                </div>\
            </div>\
        </div>';

        document.body.insertAdjacentHTML('beforeend', modalHtml);
    }

    function populateVersionHistory() {
        var timeline = document.getElementById('versionTimeline');
        if (!timeline) return;

        var html = '';
        versionHistory.forEach(function(version, index) {
            var isCurrent = index === versionHistory.length - 1;
            
            html += '\
            <div class="version-entry ' + (isCurrent ? 'current' : '') + '">\
                <div class="version-entry-marker"></div>\
                <div class="version-entry-content">\
                    <div class="version-entry-header">\
                        <span class="version-badge">v' + version.version + '</span>\
                        <span class="version-status-pill ' + version.status + '">' + formatStatus(version.status) + '</span>\
                        ' + (isCurrent ? '<span class="current-badge">Current</span>' : '') + '\
                    </div>\
                    <div class="version-entry-time">' + new Date(version.timestamp).toLocaleString() + '</div>\
                    ' + (index > 0 ? '<button class="btn btn-sm btn-outline-primary mt-2" onclick="UNIFIED_APPROVAL.showDiffView(' + (version.version - 1) + ', ' + version.version + ')">View Changes</button>' : '') + '\
                </div>\
            </div>';
        });

        timeline.innerHTML = html || '<p class="text-muted">No version history available.</p>';
    }

    function showDiffView(oldVersion, newVersion) {
        var oldData = versionHistory.find(function(v) { return v.version === oldVersion; });
        var newData = versionHistory.find(function(v) { return v.version === newVersion; });
        
        if (!oldData || !newData) {
            console.error('[UnifiedApproval] Version not found for diff');
            return;
        }

        var existingDiff = document.getElementById('diffViewPanel');
        if (existingDiff) existingDiff.remove();

        var diffHtml = '\
        <div id="diffViewPanel" class="diff-view-panel">\
            <div class="diff-view-header">\
                <div class="diff-view-title">\
                    <i class="fas fa-code-compare me-2"></i>\
                    Changes: v' + oldVersion + ' → v' + newVersion + '\
                </div>\
                <button class="diff-close-btn" onclick="UNIFIED_APPROVAL.closeDiffView()">\
                    <i class="fas fa-times"></i>\
                </button>\
            </div>\
            <div class="diff-view-body">\
                <div class="diff-columns">\
                    <div class="diff-column diff-old">\
                        <div class="diff-column-header">\
                            <span class="version-badge old">v' + oldVersion + '</span>\
                            Previous Version\
                        </div>\
                        <div class="diff-column-content" id="diffOldContent"></div>\
                    </div>\
                    <div class="diff-column diff-new">\
                        <div class="diff-column-header">\
                            <span class="version-badge new">v' + newVersion + '</span>\
                            Current Version\
                        </div>\
                        <div class="diff-column-content" id="diffNewContent"></div>\
                    </div>\
                </div>\
            </div>\
        </div>';

        var mainContent = document.querySelector('.main-content') || document.querySelector('.detail-grid');
        if (mainContent) {
            mainContent.insertAdjacentHTML('afterbegin', diffHtml);
            populateDiffContent(oldData.data, newData.data);
        }
    }

    function populateDiffContent(oldData, newData) {
        var oldContent = document.getElementById('diffOldContent');
        var newContent = document.getElementById('diffNewContent');
        
        if (!oldContent || !newContent) return;

        var allKeys = new Set([...Object.keys(oldData || {}), ...Object.keys(newData || {})]);
        
        var oldHtml = '';
        var newHtml = '';
        
        allKeys.forEach(function(key) {
            if (key === 'capturedAt') return;
            
            var oldVal = oldData ? oldData[key] : null;
            var newVal = newData ? newData[key] : null;
            var label = formatFieldLabel(key);
            
            if (oldVal === newVal) {
                oldHtml += '<div class="diff-row unchanged"><span class="diff-label">' + label + ':</span> ' + (oldVal || '—') + '</div>';
                newHtml += '<div class="diff-row unchanged"><span class="diff-label">' + label + ':</span> ' + (newVal || '—') + '</div>';
            } else if (oldVal && !newVal) {
                oldHtml += '<div class="diff-row removed"><span class="diff-label">' + label + ':</span> ' + oldVal + '</div>';
                newHtml += '<div class="diff-row removed"><span class="diff-label">' + label + ':</span> —</div>';
            } else if (!oldVal && newVal) {
                oldHtml += '<div class="diff-row added"><span class="diff-label">' + label + ':</span> —</div>';
                newHtml += '<div class="diff-row added"><span class="diff-label">' + label + ':</span> ' + newVal + '</div>';
            } else {
                oldHtml += '<div class="diff-row changed"><span class="diff-label">' + label + ':</span> ' + oldVal + '</div>';
                newHtml += '<div class="diff-row changed"><span class="diff-label">' + label + ':</span> ' + newVal + '</div>';
            }
        });
        
        oldContent.innerHTML = oldHtml || '<div class="diff-empty">No data</div>';
        newContent.innerHTML = newHtml || '<div class="diff-empty">No data</div>';
    }

    function closeDiffView() {
        var panel = document.getElementById('diffViewPanel');
        if (panel) panel.remove();
    }

    function formatFieldLabel(key) {
        return key
            .replace(/([A-Z])/g, ' $1')
            .replace(/^./, function(str) { return str.toUpperCase(); })
            .replace(/Id$/, ' ID');
    }

    function formatStatus(status) {
        return status.split(/[-_]/).map(function(word) {
            return word.charAt(0).toUpperCase() + word.slice(1);
        }).join(' ');
    }

    function renderExternalValidationSection() {
        var container = document.getElementById('externalValidationTracking');
        if (!container) return;

        var providerInfo = EXTERNAL_PROVIDERS[currentEntity.type.externalProvider];

        if (externalValidationHistory.length === 0) {
            container.innerHTML = '\
                <div class="validation-empty">\
                    <i class="fas fa-shield-alt"></i>\
                    <p>No ' + providerInfo.name + ' validation requests yet</p>\
                    <small>Click "Submit to ' + providerInfo.name + '" to initiate external validation</small>\
                </div>';
            return;
        }

        var html = '';
        externalValidationHistory.forEach(function(entry, index) {
            var statusInfo = providerInfo.statuses[entry.status] || providerInfo.statuses.PENDING;
            var isLatest = index === externalValidationHistory.length - 1;
            
            html += '\
            <div class="validation-entry ' + (isLatest ? 'latest' : '') + '">\
                <div class="validation-entry-header">\
                    <span class="validation-status ' + statusInfo.class + '">\
                        <i class="fas ' + statusInfo.icon + '"></i> ' + statusInfo.label + '\
                    </span>\
                    <span class="validation-timestamp">' + new Date(entry.timestamp).toLocaleString() + '</span>\
                </div>\
                <div class="validation-details">\
                    <div class="validation-detail-row">\
                        <span class="detail-label">Request ID</span>\
                        <span class="detail-value mono">' + entry.externalRequestId + '</span>\
                    </div>';
            
            if (entry.responseCode) {
                html += '\
                    <div class="validation-detail-row">\
                        <span class="detail-label">Response Code</span>\
                        <span class="detail-value ' + (entry.responseCode === 200 ? 'text-success' : 'text-danger') + '">' + entry.responseCode + '</span>\
                    </div>';
            }
            
            if (entry.responseMessage) {
                html += '\
                    <div class="validation-detail-row">\
                        <span class="detail-label">Response</span>\
                        <span class="detail-value">' + entry.responseMessage + '</span>\
                    </div>';
            }

            html += '\
                    <div class="validation-detail-row">\
                        <span class="detail-label">Payload Sent</span>\
                        <button class="btn-view-payload" onclick="UNIFIED_APPROVAL.showPayload(' + index + ', \'sent\')">\
                            <i class="fas fa-code"></i> View Payload\
                        </button>\
                    </div>';

            if (entry.rawResponse) {
                html += '\
                    <div class="validation-detail-row">\
                        <span class="detail-label">Raw Response</span>\
                        <button class="btn-view-payload admin-only" onclick="UNIFIED_APPROVAL.showPayload(' + index + ', \'response\')">\
                            <i class="fas fa-lock"></i> View Raw (Admin)\
                        </button>\
                    </div>';
            }

            if (entry.callbacks && entry.callbacks.length > 0) {
                html += '\
                    <div class="validation-detail-row">\
                        <span class="detail-label">Callbacks (' + entry.callbacks.length + ')</span>\
                        <button class="btn-view-payload admin-only" onclick="UNIFIED_APPROVAL.showCallbacks(' + index + ')">\
                            <i class="fas fa-lock"></i> View Callbacks (Admin)\
                        </button>\
                    </div>';
            }

            html += '\
                </div>\
            </div>';
        });

        container.innerHTML = html;
    }

    function showPayload(index, payloadType) {
        var entry = externalValidationHistory[index];
        var providerInfo = EXTERNAL_PROVIDERS[entry.provider];
        
        var payload = payloadType === 'sent' ? entry.payloadSent : entry.rawResponse;
        var title = providerInfo.name + ' ' + (payloadType === 'sent' ? 'Request Payload' : 'Raw Response (Admin Only)');

        if (payloadType === 'response' && typeof AdminControlPlane !== 'undefined') {
            AdminControlPlane.logAdminAction('VIEW_RAW_PAYLOAD', entry.externalRequestId, {
                provider: entry.provider,
                payloadType: payloadType
            }, 'MEDIUM');
        }

        showPayloadModal(title, payload);
    }

    function showCallbacks(index) {
        var entry = externalValidationHistory[index];
        
        if (typeof AdminControlPlane !== 'undefined') {
            AdminControlPlane.logAdminAction('VIEW_CALLBACKS', entry.externalRequestId, {
                callbackCount: entry.callbacks.length
            }, 'MEDIUM');
        }

        var content = entry.callbacks.map(function(cb, i) {
            return {
                callbackNumber: i + 1,
                timestamp: cb.timestamp,
                status: cb.status,
                payload: cb.rawPayload
            };
        });

        showPayloadModal('External Callbacks (Admin Only)', content);
    }

    function showPayloadModal(title, payload) {
        var existingModal = document.getElementById('payloadModal');
        if (existingModal) existingModal.remove();

        var formattedPayload = JSON.stringify(payload, null, 2);

        var modalHtml = '\
        <div class="modal fade" id="payloadModal" tabindex="-1">\
            <div class="modal-dialog modal-lg">\
                <div class="modal-content">\
                    <div class="modal-header">\
                        <h5 class="modal-title"><i class="fas fa-code me-2"></i>' + title + '</h5>\
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>\
                    </div>\
                    <div class="modal-body">\
                        <div class="payload-warning">\
                            <i class="fas fa-exclamation-triangle"></i>\
                            <span>This data is for admin troubleshooting only. Never share with customers.</span>\
                        </div>\
                        <pre class="payload-display">' + escapeHtml(formattedPayload) + '</pre>\
                    </div>\
                    <div class="modal-footer">\
                        <button type="button" class="btn btn-outline-secondary" onclick="UNIFIED_APPROVAL.copyPayload()">\
                            <i class="fas fa-copy me-1"></i> Copy\
                        </button>\
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>\
                    </div>\
                </div>\
            </div>\
        </div>';

        document.body.insertAdjacentHTML('beforeend', modalHtml);
        new bootstrap.Modal(document.getElementById('payloadModal')).show();
    }

    function copyPayload() {
        var payloadText = document.querySelector('.payload-display').textContent;
        navigator.clipboard.writeText(payloadText).then(function() {
            alert('Payload copied to clipboard');
        });
    }

    function escapeHtml(text) {
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function showReturnModal() {
        var modal = document.getElementById('returnToCustomerModal');
        if (!modal) {
            createReturnModal();
            modal = document.getElementById('returnToCustomerModal');
        }
        
        document.getElementById('returnReasonCode').value = '';
        document.getElementById('returnGuidance').value = '';
        updateReturnReasonDescription();
        
        new bootstrap.Modal(modal).show();
    }

    function createReturnModal() {
        var reasonOptions = '';
        Object.keys(RETURN_REASON_CODES).forEach(function(key) {
            var reason = RETURN_REASON_CODES[key];
            reasonOptions += '<option value="' + reason.code + '">' + reason.label + '</option>';
        });

        var modalHtml = '\
        <div class="modal fade" id="returnToCustomerModal" tabindex="-1">\
            <div class="modal-dialog modal-lg">\
                <div class="modal-content">\
                    <div class="modal-header">\
                        <h5 class="modal-title"><i class="fas fa-reply me-2"></i>Return to Customer</h5>\
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>\
                    </div>\
                    <div class="modal-body">\
                        <div class="return-info-box">\
                            <i class="fas fa-info-circle"></i>\
                            <div>\
                                <strong>This action will:</strong>\
                                <ul style="margin: 0.5rem 0 0 0; padding-left: 1.25rem;">\
                                    <li>Preserve the current submission as an immutable version</li>\
                                    <li>Notify the customer with your guidance</li>\
                                    <li>Lock approval actions until resubmission</li>\
                                </ul>\
                            </div>\
                        </div>\
                        <div class="mb-3">\
                            <label class="form-label">Reason Code <span class="text-danger">*</span></label>\
                            <select class="form-select" id="returnReasonCode" onchange="UNIFIED_APPROVAL.updateReturnReasonDescription()">\
                                <option value="">Select a reason...</option>\
                                ' + reasonOptions + '\
                            </select>\
                            <div class="reason-description" id="returnReasonDescription"></div>\
                        </div>\
                        <div class="mb-3">\
                            <label class="form-label">Guidance for Customer <small class="text-muted">(Optional)</small></label>\
                            <textarea class="form-control" id="returnGuidance" rows="4" placeholder="Provide specific guidance on what needs to be corrected or clarified..."></textarea>\
                            <small class="text-muted">This message will be included in the customer notification email.</small>\
                        </div>\
                        <div class="version-preview">\
                            <div class="version-preview-header">\
                                <i class="fas fa-history me-2"></i>Version to be Preserved\
                            </div>\
                            <div class="version-preview-body">\
                                <span class="version-badge">v' + currentEntity.currentVersion + '</span>\
                            </div>\
                        </div>\
                    </div>\
                    <div class="modal-footer">\
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>\
                        <button type="button" class="btn btn-warning" onclick="UNIFIED_APPROVAL.confirmReturn()">\
                            <i class="fas fa-reply me-1"></i>Return to Customer\
                        </button>\
                    </div>\
                </div>\
            </div>\
        </div>';

        document.body.insertAdjacentHTML('beforeend', modalHtml);
    }

    function updateReturnReasonDescription() {
        var code = document.getElementById('returnReasonCode').value;
        var descEl = document.getElementById('returnReasonDescription');
        
        if (code && RETURN_REASON_CODES[code]) {
            descEl.textContent = RETURN_REASON_CODES[code].description;
            descEl.style.display = 'block';
        } else {
            descEl.style.display = 'none';
        }
    }

    function confirmReturn() {
        var reasonCode = document.getElementById('returnReasonCode').value;
        var guidance = document.getElementById('returnGuidance').value.trim();
        
        if (!reasonCode) {
            alert('Please select a reason code');
            return;
        }

        if (reasonCode === 'OTHER' && !guidance) {
            alert('Please provide guidance when selecting "Other" as the reason');
            return;
        }

        bootstrap.Modal.getInstance(document.getElementById('returnToCustomerModal')).hide();
        
        returnToCustomer(reasonCode, guidance);
    }

    function showRejectModal() {
        var modal = document.getElementById('rejectModal');
        if (!modal) {
            createRejectModal();
            modal = document.getElementById('rejectModal');
        }
        
        document.getElementById('rejectReason').value = '';
        document.getElementById('rejectMessage').value = '';
        
        new bootstrap.Modal(modal).show();
    }

    function createRejectModal() {
        var reasonOptions = '';
        Object.keys(REJECTION_REASON_CODES).forEach(function(key) {
            var reason = REJECTION_REASON_CODES[key];
            reasonOptions += '<option value="' + reason.code + '">' + reason.label + '</option>';
        });

        var modalHtml = '\
        <div class="modal fade" id="rejectModal" tabindex="-1">\
            <div class="modal-dialog">\
                <div class="modal-content">\
                    <div class="modal-header bg-danger text-white">\
                        <h5 class="modal-title"><i class="fas fa-times-circle me-2"></i>Reject ' + currentEntity.type.label + '</h5>\
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>\
                    </div>\
                    <div class="modal-body">\
                        <div class="alert alert-danger">\
                            <i class="fas fa-exclamation-triangle me-2"></i>\
                            <strong>Warning:</strong> This action is permanent and cannot be undone.\
                        </div>\
                        <div class="mb-3">\
                            <label class="form-label">Rejection Reason <span class="text-danger">*</span></label>\
                            <select class="form-select" id="rejectReason">\
                                <option value="">Select reason...</option>\
                                ' + reasonOptions + '\
                            </select>\
                        </div>\
                        <div class="mb-3">\
                            <label class="form-label">Additional Details <small class="text-muted">(Optional)</small></label>\
                            <textarea class="form-control" id="rejectMessage" rows="3" placeholder="Provide additional context..."></textarea>\
                        </div>\
                    </div>\
                    <div class="modal-footer">\
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>\
                        <button type="button" class="btn btn-danger" onclick="UNIFIED_APPROVAL.confirmReject()">\
                            <i class="fas fa-times me-1"></i>Confirm Rejection\
                        </button>\
                    </div>\
                </div>\
            </div>\
        </div>';

        document.body.insertAdjacentHTML('beforeend', modalHtml);
    }

    function confirmReject() {
        var reasonCode = document.getElementById('rejectReason').value;
        var message = document.getElementById('rejectMessage').value.trim();
        
        if (!reasonCode) {
            alert('Please select a rejection reason');
            return;
        }

        bootstrap.Modal.getInstance(document.getElementById('rejectModal')).hide();
        
        reject(reasonCode, message);
    }

    function getCurrentEntity() {
        return currentEntity;
    }

    function getVersionHistory() {
        return versionHistory;
    }

    function getExternalValidationHistory() {
        return externalValidationHistory;
    }

    return {
        init: init,
        ENTITY_TYPES: ENTITY_TYPES,
        LIFECYCLE_STATUSES: LIFECYCLE_STATUSES,
        RETURN_REASON_CODES: RETURN_REASON_CODES,
        REJECTION_REASON_CODES: REJECTION_REASON_CODES,
        EXTERNAL_PROVIDERS: EXTERNAL_PROVIDERS,
        
        approve: approve,
        reject: reject,
        returnToCustomer: returnToCustomer,
        forceApprove: forceApprove,
        provision: provision,
        handleResubmission: handleResubmission,
        
        submitToExternalProvider: submitToExternalProvider,
        handleExternalCallback: handleExternalCallback,
        
        showReturnModal: showReturnModal,
        showRejectModal: showRejectModal,
        confirmReturn: confirmReturn,
        confirmReject: confirmReject,
        updateReturnReasonDescription: updateReturnReasonDescription,
        
        showVersionHistory: showVersionHistory,
        showDiffView: showDiffView,
        closeDiffView: closeDiffView,
        
        showPayload: showPayload,
        showCallbacks: showCallbacks,
        copyPayload: copyPayload,
        
        getCurrentEntity: getCurrentEntity,
        getVersionHistory: getVersionHistory,
        getExternalValidationHistory: getExternalValidationHistory
    };
})();
