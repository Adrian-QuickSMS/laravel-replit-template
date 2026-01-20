var APPROVAL_WORKFLOW = (function() {
    'use strict';

    var RETURN_REASON_CODES = {
        'INCOMPLETE_INFO': {
            code: 'INCOMPLETE_INFO',
            label: 'Incomplete Information',
            description: 'Required fields or documentation missing'
        },
        'ASSET_QUALITY': {
            code: 'ASSET_QUALITY',
            label: 'Asset Quality Issues',
            description: 'Images do not meet quality requirements'
        },
        'BRAND_CLARIFICATION': {
            code: 'BRAND_CLARIFICATION',
            label: 'Brand Clarification Needed',
            description: 'Brand ownership or authorization needs verification'
        },
        'USE_CASE_UNCLEAR': {
            code: 'USE_CASE_UNCLEAR',
            label: 'Use Case Unclear',
            description: 'Messaging purpose requires clarification'
        },
        'COMPLIANCE_ISSUE': {
            code: 'COMPLIANCE_ISSUE',
            label: 'Compliance Issue',
            description: 'Legal or regulatory requirements not met'
        },
        'OPTIN_OPTOUT_MISSING': {
            code: 'OPTIN_OPTOUT_MISSING',
            label: 'Opt-in/Opt-out Missing',
            description: 'Consent mechanism not properly documented'
        },
        'VOLUME_JUSTIFICATION': {
            code: 'VOLUME_JUSTIFICATION',
            label: 'Volume Justification Required',
            description: 'Estimated volume needs supporting evidence'
        },
        'OTHER': {
            code: 'OTHER',
            label: 'Other (Specify)',
            description: 'Custom reason - requires explanation'
        }
    };

    var versionHistory = [];
    var currentVersion = null;
    var requestId = null;
    var requestType = null;

    function init(config) {
        requestId = config.requestId;
        requestType = config.requestType;
        currentVersion = config.currentVersion || 1;
        versionHistory = config.versionHistory || [];
        
        if (versionHistory.length === 0 && config.initialData) {
            versionHistory.push(createVersion(1, config.initialData, 'submitted'));
        }
        
        console.log('[ApprovalWorkflow] Initialized for ' + requestId + ' (v' + currentVersion + ')');
        updateVersionIndicator();
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

    function getCurrentStatus() {
        if (versionHistory.length === 0) return 'submitted';
        return versionHistory[versionHistory.length - 1].status;
    }

    function isActionLocked() {
        var status = getCurrentStatus();
        return status === 'returned-to-customer';
    }

    function lockApprovalActions() {
        var actionsToLock = [
            'approveBtn',
            'rejectBtn',
            'submitProviderBtn',
            'forceApproveBtn',
            'provisionBtn'
        ];

        document.querySelectorAll('.action-btn').forEach(function(btn) {
            var btnText = btn.textContent.toLowerCase();
            if (btnText.includes('approve') || 
                btnText.includes('reject') || 
                btnText.includes('submit to') ||
                btnText.includes('provision')) {
                btn.disabled = true;
                btn.classList.add('action-locked');
                btn.title = 'Action locked - awaiting customer resubmission';
            }
        });

        var lockNotice = document.createElement('div');
        lockNotice.id = 'actionLockNotice';
        lockNotice.className = 'action-lock-notice';
        lockNotice.innerHTML = '<i class="fas fa-lock me-2"></i>Approval actions locked. Awaiting customer resubmission.';
        
        var actionPanel = document.querySelector('.action-panel-title');
        if (actionPanel && !document.getElementById('actionLockNotice')) {
            actionPanel.parentNode.insertBefore(lockNotice, actionPanel.nextSibling);
        }
    }

    function unlockApprovalActions() {
        document.querySelectorAll('.action-btn.action-locked').forEach(function(btn) {
            btn.disabled = false;
            btn.classList.remove('action-locked');
            btn.title = '';
        });

        var notice = document.getElementById('actionLockNotice');
        if (notice) notice.remove();
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
                            <select class="form-select" id="returnReasonCode" onchange="APPROVAL_WORKFLOW.updateReturnReasonDescription()">\
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
                                <span class="version-badge">v' + currentVersion + '</span>\
                                <span class="version-date">Submitted: ' + new Date().toLocaleString() + '</span>\
                            </div>\
                        </div>\
                    </div>\
                    <div class="modal-footer">\
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>\
                        <button type="button" class="btn btn-warning" onclick="APPROVAL_WORKFLOW.confirmReturn()">\
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

        var currentData = captureCurrentFormData();
        versionHistory.push(createVersion(currentVersion, currentData, 'returned-to-customer'));

        if (typeof AdminControlPlane !== 'undefined') {
            AdminControlPlane.logAdminAction('RETURN_TO_CUSTOMER', requestId, {
                version: currentVersion,
                reasonCode: reasonCode,
                reasonLabel: RETURN_REASON_CODES[reasonCode].label,
                hasGuidance: !!guidance
            }, 'HIGH');
        }

        bootstrap.Modal.getInstance(document.getElementById('returnToCustomerModal')).hide();

        updateStatusDisplay('returned-to-customer', 'Returned to Customer', 'fa-reply');
        lockApprovalActions();
        addAuditEntry('Returned to Customer', 'Reason: ' + RETURN_REASON_CODES[reasonCode].label);
        updateVersionIndicator();

        showNotification('Request returned to customer. They will receive a notification email.', 'warning');
    }

    function handleResubmission(newData) {
        var previousVersion = currentVersion;
        currentVersion++;

        versionHistory.push(createVersion(currentVersion, newData, 'resubmitted'));

        if (typeof AdminControlPlane !== 'undefined') {
            AdminControlPlane.logAdminAction('CUSTOMER_RESUBMISSION', requestId, {
                previousVersion: previousVersion,
                newVersion: currentVersion
            }, 'MEDIUM');
        }

        updateStatusDisplay('resubmitted', 'Resubmitted', 'fa-redo');
        unlockApprovalActions();
        resetSlaTimer();
        updateVersionIndicator();
        showDiffView(previousVersion, currentVersion);

        showNotification('Customer has resubmitted. Review the changes in the diff view.', 'info');
    }

    function captureCurrentFormData() {
        return {
            capturedAt: new Date().toISOString(),
            senderIdValue: document.getElementById('senderIdValue')?.textContent || null,
            agentName: document.getElementById('agentNameDisplay')?.textContent || null,
            requestId: requestId,
            type: requestType
        };
    }

    function showDiffView(oldVersion, newVersion) {
        var oldData = versionHistory.find(function(v) { return v.version === oldVersion; });
        var newData = versionHistory.find(function(v) { return v.version === newVersion; });
        
        if (!oldData || !newData) {
            console.error('[ApprovalWorkflow] Version not found for diff');
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
                <button class="diff-close-btn" onclick="APPROVAL_WORKFLOW.closeDiffView()">\
                    <i class="fas fa-times"></i>\
                </button>\
            </div>\
            <div class="diff-view-body">\
                <div class="diff-columns">\
                    <div class="diff-column diff-old">\
                        <div class="diff-column-header">\
                            <span class="version-badge old">v' + oldVersion + '</span>\
                            Previous Version\
                            <span class="diff-timestamp">' + new Date(oldData.timestamp).toLocaleString() + '</span>\
                        </div>\
                        <div class="diff-column-content" id="diffOldContent">\
                        </div>\
                    </div>\
                    <div class="diff-column diff-new">\
                        <div class="diff-column-header">\
                            <span class="version-badge new">v' + newVersion + '</span>\
                            Current Version\
                            <span class="diff-timestamp">' + new Date(newData.timestamp).toLocaleString() + '</span>\
                        </div>\
                        <div class="diff-column-content" id="diffNewContent">\
                        </div>\
                    </div>\
                </div>\
                <div class="diff-legend">\
                    <span class="diff-legend-item"><span class="diff-indicator removed"></span> Removed</span>\
                    <span class="diff-legend-item"><span class="diff-indicator added"></span> Added</span>\
                    <span class="diff-legend-item"><span class="diff-indicator changed"></span> Changed</span>\
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

    function formatFieldLabel(key) {
        return key
            .replace(/([A-Z])/g, ' $1')
            .replace(/^./, function(str) { return str.toUpperCase(); })
            .replace(/Id$/, ' ID');
    }

    function closeDiffView() {
        var panel = document.getElementById('diffViewPanel');
        if (panel) panel.remove();
    }

    function updateStatusDisplay(status, label, icon) {
        var pill = document.getElementById('currentStatus');
        if (pill) {
            pill.className = 'status-pill ' + status;
            pill.innerHTML = '<i class="fas ' + icon + '"></i> ' + label;
        }
    }

    function updateVersionIndicator() {
        var indicator = document.getElementById('versionIndicator');
        if (!indicator) {
            var statusHeader = document.querySelector('.status-header');
            if (statusHeader) {
                var versionHtml = '<span class="version-indicator" id="versionIndicator">v' + currentVersion + '</span>';
                statusHeader.insertAdjacentHTML('beforeend', versionHtml);
            }
        } else {
            indicator.textContent = 'v' + currentVersion;
            if (currentVersion > 1) {
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
            var statusClass = version.status.replace(/-/g, '-');
            var isCurrent = index === versionHistory.length - 1;
            
            html += '\
            <div class="version-entry ' + (isCurrent ? 'current' : '') + '">\
                <div class="version-entry-marker"></div>\
                <div class="version-entry-content">\
                    <div class="version-entry-header">\
                        <span class="version-badge">v' + version.version + '</span>\
                        <span class="version-status-pill ' + statusClass + '">' + formatStatus(version.status) + '</span>\
                        ' + (isCurrent ? '<span class="current-badge">Current</span>' : '') + '\
                    </div>\
                    <div class="version-entry-time">' + new Date(version.timestamp).toLocaleString() + '</div>\
                    ' + (index > 0 ? '<button class="btn btn-sm btn-outline-primary mt-2" onclick="APPROVAL_WORKFLOW.showDiffView(' + (version.version - 1) + ', ' + version.version + ')">View Changes</button>' : '') + '\
                </div>\
            </div>';
        });

        timeline.innerHTML = html || '<p class="text-muted">No version history available.</p>';
    }

    function formatStatus(status) {
        return status.split('-').map(function(word) {
            return word.charAt(0).toUpperCase() + word.slice(1);
        }).join(' ');
    }

    function resetSlaTimer() {
        var slaElement = document.querySelector('.detail-row .detail-value:has(i.fa-hourglass-half)') ||
                        document.querySelector('[class*="sla"]');
        
        if (slaElement) {
            slaElement.innerHTML = '<i class="fas fa-hourglass-start me-1" style="color: #22c55e;"></i>24h (Reset)';
            slaElement.style.color = '#22c55e';
        }

        if (typeof AdminControlPlane !== 'undefined') {
            AdminControlPlane.logAdminAction('SLA_TIMER_RESET', requestId, {
                version: currentVersion,
                newSla: '24h'
            }, 'LOW');
        }
    }

    function addAuditEntry(action, detail) {
        var auditTrail = document.querySelector('.audit-trail');
        if (!auditTrail) return;

        var entry = document.createElement('div');
        entry.className = 'audit-entry';
        entry.innerHTML = '\
            <div class="audit-icon"><i class="fas fa-reply"></i></div>\
            <div class="audit-content">\
                <div class="audit-action">' + action + '</div>\
                <div class="audit-meta">' + detail + ' | ' + new Date().toLocaleString() + '</div>\
            </div>';
        
        auditTrail.insertBefore(entry, auditTrail.firstChild);
    }

    function showNotification(message, type) {
        var notification = document.createElement('div');
        notification.className = 'workflow-notification ' + type;
        notification.innerHTML = '<i class="fas fa-' + (type === 'warning' ? 'exclamation-triangle' : 'info-circle') + ' me-2"></i>' + message;
        
        document.body.appendChild(notification);
        
        setTimeout(function() {
            notification.classList.add('show');
        }, 100);
        
        setTimeout(function() {
            notification.classList.remove('show');
            setTimeout(function() { notification.remove(); }, 300);
        }, 4000);
    }

    function simulateResubmission() {
        var newData = captureCurrentFormData();
        newData.simulatedChange = 'Updated at ' + new Date().toISOString();
        handleResubmission(newData);
    }

    return {
        init: init,
        showReturnModal: showReturnModal,
        confirmReturn: confirmReturn,
        updateReturnReasonDescription: updateReturnReasonDescription,
        showDiffView: showDiffView,
        closeDiffView: closeDiffView,
        showVersionHistory: showVersionHistory,
        simulateResubmission: simulateResubmission,
        isActionLocked: isActionLocked,
        getCurrentStatus: getCurrentStatus,
        RETURN_REASON_CODES: RETURN_REASON_CODES
    };
})();
