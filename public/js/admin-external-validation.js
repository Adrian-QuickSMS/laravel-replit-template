var EXTERNAL_VALIDATION = (function() {
    'use strict';

    var BRANDASSURE_STATUSES = {
        'PENDING': { label: 'Pending', class: 'pending', icon: 'fa-clock' },
        'SUBMITTED': { label: 'Submitted', class: 'submitted', icon: 'fa-paper-plane' },
        'IN_PROGRESS': { label: 'In Progress', class: 'in-progress', icon: 'fa-spinner fa-spin' },
        'VERIFIED': { label: 'Verified', class: 'verified', icon: 'fa-check-circle' },
        'FAILED': { label: 'Failed', class: 'failed', icon: 'fa-times-circle' },
        'TIMEOUT': { label: 'Timeout', class: 'timeout', icon: 'fa-exclamation-triangle' }
    };

    var RCS_PROVIDER_STATUSES = {
        'PENDING': { label: 'Pending', class: 'pending', icon: 'fa-clock' },
        'SUBMITTED': { label: 'Submitted', class: 'submitted', icon: 'fa-paper-plane' },
        'REVIEW': { label: 'Under Review', class: 'review', icon: 'fa-search' },
        'APPROVED': { label: 'Approved', class: 'approved', icon: 'fa-check-circle' },
        'REJECTED': { label: 'Rejected', class: 'rejected', icon: 'fa-times-circle' },
        'PROVISIONING': { label: 'Provisioning', class: 'provisioning', icon: 'fa-cog fa-spin' },
        'LIVE': { label: 'Live', class: 'live', icon: 'fa-broadcast-tower' },
        'FAILED': { label: 'Failed', class: 'failed', icon: 'fa-times-circle' }
    };

    var brandAssureHistory = [];
    var rcsProviderHistory = [];

    function initBrandAssure(config) {
        console.log('[ExternalValidation] BrandAssure tracking initialized');
        brandAssureHistory = config.history || [];
        renderBrandAssureSection();
    }

    function initRcsProvider(config) {
        console.log('[ExternalValidation] RCS Provider tracking initialized');
        rcsProviderHistory = config.history || [];
        renderRcsProviderSection();
    }

    function createBrandAssureEntry(requestId, payloadSent) {
        return {
            id: 'BA-' + Date.now(),
            externalRequestId: requestId,
            timestamp: new Date().toISOString(),
            status: 'SUBMITTED',
            payloadSent: payloadSent,
            responseCode: null,
            responseMessage: null,
            rawResponse: null,
            callbacks: []
        };
    }

    function createRcsProviderEntry(providerRefId, payloadSent) {
        return {
            id: 'RCS-P-' + Date.now(),
            providerReferenceId: providerRefId,
            submissionTimestamp: new Date().toISOString(),
            status: 'SUBMITTED',
            payloadSent: payloadSent,
            provisioningStatus: null,
            callbacks: [],
            failureReason: null,
            rawResponse: null
        };
    }

    function submitToBrandAssure(requestId, senderIdData) {
        var payload = {
            senderId: senderIdData.value,
            type: senderIdData.type,
            brand: senderIdData.brand,
            accountId: senderIdData.accountId,
            submittedAt: new Date().toISOString()
        };

        var externalRequestId = 'BASRQ-' + Math.random().toString(36).substring(2, 10).toUpperCase();
        var entry = createBrandAssureEntry(externalRequestId, payload);
        brandAssureHistory.push(entry);

        if (typeof AdminControlPlane !== 'undefined') {
            AdminControlPlane.logAdminAction('BRANDASSURE_SUBMIT', requestId, {
                externalRequestId: externalRequestId,
                payload: JSON.stringify(payload).substring(0, 200)
            }, 'MEDIUM');
        }

        renderBrandAssureSection();
        
        setTimeout(function() {
            simulateBrandAssureResponse(entry.id, true);
        }, 2000);

        return entry;
    }

    function simulateBrandAssureResponse(entryId, success) {
        var entry = brandAssureHistory.find(function(e) { return e.id === entryId; });
        if (!entry) return;

        entry.status = success ? 'VERIFIED' : 'FAILED';
        entry.responseCode = success ? 200 : 422;
        entry.responseMessage = success ? 'Brand verification successful' : 'Brand verification failed - trademark conflict detected';
        entry.rawResponse = {
            requestId: entry.externalRequestId,
            status: success ? 'VERIFIED' : 'FAILED',
            timestamp: new Date().toISOString(),
            confidence: success ? 0.95 : 0.12,
            matchedTrademarks: success ? [] : ['ACME CORP LTD - TM2019001234'],
            riskScore: success ? 'LOW' : 'HIGH',
            processingTime: '1.234s',
            _internal: {
                nodeId: 'ba-node-eu-west-1a',
                version: '2.4.1'
            }
        };

        if (typeof AdminControlPlane !== 'undefined') {
            AdminControlPlane.logAdminAction('BRANDASSURE_RESPONSE', entry.externalRequestId, {
                status: entry.status,
                responseCode: entry.responseCode
            }, success ? 'MEDIUM' : 'HIGH');
        }

        if (!success && typeof ADMIN_NOTIFICATIONS !== 'undefined') {
            ADMIN_NOTIFICATIONS.triggerInternalAlert('VALIDATION_FAILED', entry.externalRequestId, 'BrandAssure verification failed - trademark conflict');
        }

        renderBrandAssureSection();
    }

    function submitToRcsProvider(agentId, agentData) {
        var payload = {
            agentName: agentData.name,
            agentDescription: agentData.description,
            brandColor: agentData.brandColor,
            logoUrl: agentData.logoUrl,
            heroUrl: agentData.heroUrl,
            billingCategory: agentData.billingCategory,
            submittedAt: new Date().toISOString()
        };

        var providerRefId = 'RCSP-' + Math.random().toString(36).substring(2, 12).toUpperCase();
        var entry = createRcsProviderEntry(providerRefId, payload);
        rcsProviderHistory.push(entry);

        if (typeof AdminControlPlane !== 'undefined') {
            AdminControlPlane.logAdminAction('RCS_PROVIDER_SUBMIT', agentId, {
                providerReferenceId: providerRefId,
                payload: JSON.stringify(payload).substring(0, 200)
            }, 'MEDIUM');
        }

        renderRcsProviderSection();

        setTimeout(function() {
            simulateRcsProviderCallback(entry.id, 'REVIEW');
        }, 1500);

        return entry;
    }

    function simulateRcsProviderCallback(entryId, status) {
        var entry = rcsProviderHistory.find(function(e) { return e.id === entryId; });
        if (!entry) return;

        var callback = {
            timestamp: new Date().toISOString(),
            status: status,
            rawPayload: {
                event: 'status_update',
                reference: entry.providerReferenceId,
                newStatus: status,
                previousStatus: entry.status,
                updatedAt: new Date().toISOString(),
                _meta: {
                    webhookId: 'wh-' + Math.random().toString(36).substring(2, 8),
                    signature: 'sha256=' + Math.random().toString(36).substring(2, 66)
                }
            }
        };

        entry.callbacks.push(callback);
        entry.status = status;
        entry.provisioningStatus = status === 'PROVISIONING' ? 'IN_PROGRESS' : 
                                   status === 'LIVE' ? 'COMPLETE' : null;

        if (status === 'REJECTED' || status === 'FAILED') {
            entry.failureReason = 'Brand guidelines violation: Hero image does not meet RCS specifications';
        }

        if (typeof AdminControlPlane !== 'undefined') {
            AdminControlPlane.logAdminAction('RCS_PROVIDER_CALLBACK', entry.providerReferenceId, {
                status: status,
                callbackCount: entry.callbacks.length
            }, status === 'REJECTED' || status === 'FAILED' ? 'HIGH' : 'MEDIUM');
        }

        if ((status === 'REJECTED' || status === 'FAILED') && typeof ADMIN_NOTIFICATIONS !== 'undefined') {
            ADMIN_NOTIFICATIONS.triggerInternalAlert('VALIDATION_FAILED', entry.providerReferenceId, 'RCS Provider rejected agent - ' + entry.failureReason);
        }

        renderRcsProviderSection();
    }

    function renderBrandAssureSection() {
        var container = document.getElementById('brandAssureTracking');
        if (!container) return;

        if (brandAssureHistory.length === 0) {
            container.innerHTML = '\
                <div class="validation-empty">\
                    <i class="fas fa-shield-alt"></i>\
                    <p>No BrandAssure validation requests yet</p>\
                    <small>Click "Submit to BrandAssure" to initiate external brand verification</small>\
                </div>';
            return;
        }

        var html = '';
        brandAssureHistory.forEach(function(entry, index) {
            var statusInfo = BRANDASSURE_STATUSES[entry.status] || BRANDASSURE_STATUSES.PENDING;
            var isLatest = index === brandAssureHistory.length - 1;
            
            html += '\
            <div class="validation-entry ' + (isLatest ? 'latest' : '') + '">\
                <div class="validation-entry-header">\
                    <span class="validation-status ' + statusInfo.class + '">\
                        <i class="fas ' + statusInfo.icon + '"></i> ' + statusInfo.label + '\
                    </span>\
                    <span class="validation-timestamp">' + formatTimestamp(entry.timestamp) + '</span>\
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
                        <button class="btn-view-payload" onclick="EXTERNAL_VALIDATION.showPayload(\'brandassure\', ' + index + ', \'sent\')">\
                            <i class="fas fa-code"></i> View Payload\
                        </button>\
                    </div>';

            if (entry.rawResponse) {
                html += '\
                    <div class="validation-detail-row">\
                        <span class="detail-label">Raw Response</span>\
                        <button class="btn-view-payload admin-only" onclick="EXTERNAL_VALIDATION.showPayload(\'brandassure\', ' + index + ', \'response\')">\
                            <i class="fas fa-lock"></i> View Raw (Admin)\
                        </button>\
                    </div>';
            }

            html += '\
                </div>\
            </div>';
        });

        container.innerHTML = html;
    }

    function renderRcsProviderSection() {
        var container = document.getElementById('rcsProviderTracking');
        if (!container) return;

        if (rcsProviderHistory.length === 0) {
            container.innerHTML = '\
                <div class="validation-empty">\
                    <i class="fas fa-cloud-upload-alt"></i>\
                    <p>No RCS Provider submissions yet</p>\
                    <small>Click "Submit to RCS Provider" to initiate agent registration</small>\
                </div>';
            return;
        }

        var html = '';
        rcsProviderHistory.forEach(function(entry, index) {
            var statusInfo = RCS_PROVIDER_STATUSES[entry.status] || RCS_PROVIDER_STATUSES.PENDING;
            var isLatest = index === rcsProviderHistory.length - 1;
            
            html += '\
            <div class="validation-entry ' + (isLatest ? 'latest' : '') + '">\
                <div class="validation-entry-header">\
                    <span class="validation-status ' + statusInfo.class + '">\
                        <i class="fas ' + statusInfo.icon + '"></i> ' + statusInfo.label + '\
                    </span>\
                    <span class="validation-timestamp">' + formatTimestamp(entry.submissionTimestamp) + '</span>\
                </div>\
                <div class="validation-details">\
                    <div class="validation-detail-row">\
                        <span class="detail-label">Provider Reference ID</span>\
                        <span class="detail-value mono">' + entry.providerReferenceId + '</span>\
                    </div>';
            
            if (entry.provisioningStatus) {
                html += '\
                    <div class="validation-detail-row">\
                        <span class="detail-label">Provisioning Status</span>\
                        <span class="detail-value">' + entry.provisioningStatus + '</span>\
                    </div>';
            }

            if (entry.failureReason) {
                html += '\
                    <div class="validation-detail-row failure">\
                        <span class="detail-label">Failure Reason</span>\
                        <span class="detail-value text-danger">' + entry.failureReason + '</span>\
                    </div>';
            }

            html += '\
                    <div class="validation-detail-row">\
                        <span class="detail-label">Payload Sent</span>\
                        <button class="btn-view-payload" onclick="EXTERNAL_VALIDATION.showPayload(\'rcsprovider\', ' + index + ', \'sent\')">\
                            <i class="fas fa-code"></i> View Payload\
                        </button>\
                    </div>';

            if (entry.callbacks.length > 0) {
                html += '\
                    <div class="validation-detail-row">\
                        <span class="detail-label">Callbacks (' + entry.callbacks.length + ')</span>\
                        <button class="btn-view-payload admin-only" onclick="EXTERNAL_VALIDATION.showCallbacks(' + index + ')">\
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

    function showPayload(type, index, payloadType) {
        var entry, payload, title;
        
        if (type === 'brandassure') {
            entry = brandAssureHistory[index];
            payload = payloadType === 'sent' ? entry.payloadSent : entry.rawResponse;
            title = payloadType === 'sent' ? 'BrandAssure Request Payload' : 'BrandAssure Raw Response (Admin Only)';
        } else {
            entry = rcsProviderHistory[index];
            payload = payloadType === 'sent' ? entry.payloadSent : entry.rawResponse;
            title = payloadType === 'sent' ? 'RCS Provider Request Payload' : 'RCS Provider Raw Response (Admin Only)';
        }

        if (payloadType === 'response') {
            if (typeof AdminControlPlane !== 'undefined') {
                AdminControlPlane.logAdminAction('VIEW_RAW_PAYLOAD', entry.externalRequestId || entry.providerReferenceId, {
                    type: type,
                    payloadType: payloadType
                }, 'MEDIUM');
            }
        }

        showPayloadModal(title, payload);
    }

    function showCallbacks(index) {
        var entry = rcsProviderHistory[index];
        
        if (typeof AdminControlPlane !== 'undefined') {
            AdminControlPlane.logAdminAction('VIEW_CALLBACKS', entry.providerReferenceId, {
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

        showPayloadModal('RCS Provider Callbacks (Admin Only)', content);
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
                        <button type="button" class="btn btn-outline-secondary" onclick="EXTERNAL_VALIDATION.copyPayload()">\
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

    function formatTimestamp(isoString) {
        return new Date(isoString).toLocaleString();
    }

    function escapeHtml(text) {
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function getBrandAssureHistory() {
        return brandAssureHistory;
    }

    function getRcsProviderHistory() {
        return rcsProviderHistory;
    }

    return {
        initBrandAssure: initBrandAssure,
        initRcsProvider: initRcsProvider,
        submitToBrandAssure: submitToBrandAssure,
        submitToRcsProvider: submitToRcsProvider,
        simulateRcsProviderCallback: simulateRcsProviderCallback,
        showPayload: showPayload,
        showCallbacks: showCallbacks,
        copyPayload: copyPayload,
        getBrandAssureHistory: getBrandAssureHistory,
        getRcsProviderHistory: getRcsProviderHistory,
        BRANDASSURE_STATUSES: BRANDASSURE_STATUSES,
        RCS_PROVIDER_STATUSES: RCS_PROVIDER_STATUSES
    };
})();
