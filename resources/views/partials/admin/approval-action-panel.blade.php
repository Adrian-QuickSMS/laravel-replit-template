{{-- 
    Admin Approval Action Panel - Shared Component
    ADMIN-ONLY: Never visible to customers
    Used by: SenderID and RCS Agent approval overview pages
--}}
<style>
.admin-action-panel {
    background: #fff;
    border: 2px solid var(--admin-primary, #1e3a5f);
    border-radius: 8px;
    overflow: hidden;
}

.admin-action-panel-header {
    background: linear-gradient(135deg, var(--admin-primary, #1e3a5f) 0%, #2d5a87 100%);
    color: #fff;
    padding: 1rem 1.25rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.admin-action-panel-body {
    padding: 1.25rem;
}

.admin-action-group {
    margin-bottom: 1.25rem;
}

.admin-action-group:last-child {
    margin-bottom: 0;
}

.admin-action-group-title {
    font-size: 0.75rem;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.75rem;
}

.admin-action-buttons {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.admin-action-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
    border-radius: 6px;
    font-size: 0.8rem;
    font-weight: 500;
    cursor: pointer;
    border: 1px solid transparent;
    transition: all 0.2s;
    width: 100%;
    text-align: left;
}

.admin-action-btn:hover {
    transform: translateX(2px);
}

.admin-action-btn.approve {
    background: #d9f99d;
    color: #3f6212;
    border-color: #a3e635;
}

.admin-action-btn.approve:hover {
    background: #bef264;
}

.admin-action-btn.reject {
    background: #fee2e2;
    color: #991b1b;
    border-color: #fca5a5;
}

.admin-action-btn.reject:hover {
    background: #fecaca;
}

.admin-action-btn.return {
    background: #fef3c7;
    color: #92400e;
    border-color: #fcd34d;
}

.admin-action-btn.return:hover {
    background: #fde68a;
}

.admin-action-btn.validation {
    background: #dbeafe;
    color: #1e40af;
    border-color: #93c5fd;
}

.admin-action-btn.validation:hover {
    background: #bfdbfe;
}

.admin-action-btn.force-approve {
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    color: #fff;
    border-color: #dc2626;
}

.admin-action-btn.force-approve:hover {
    background: linear-gradient(135deg, #b91c1c 0%, #991b1b 100%);
}

.admin-action-btn.force-approve i {
    color: #fbbf24;
}

.admin-divider {
    height: 1px;
    background: #e2e8f0;
    margin: 1rem 0;
}

.notes-section {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    overflow: hidden;
}

.notes-tabs {
    display: flex;
    background: #fff;
    border-bottom: 1px solid #e2e8f0;
}

.notes-tab {
    flex: 1;
    padding: 0.75rem;
    font-size: 0.8rem;
    font-weight: 500;
    color: #64748b;
    background: #fff;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
}

.notes-tab:hover {
    background: #f8fafc;
}

.notes-tab.active {
    color: var(--admin-primary, #1e3a5f);
    background: #f8fafc;
    border-bottom: 2px solid var(--admin-primary, #1e3a5f);
}

.notes-tab-content {
    padding: 1rem;
}

.notes-tab-pane {
    display: none;
}

.notes-tab-pane.active {
    display: block;
}

.notes-textarea {
    width: 100%;
    min-height: 100px;
    padding: 0.75rem;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    font-size: 0.8rem;
    resize: vertical;
}

.notes-textarea:focus {
    outline: none;
    border-color: var(--admin-primary, #1e3a5f);
    box-shadow: 0 0 0 3px rgba(30, 58, 95, 0.1);
}

.notes-actions {
    display: flex;
    gap: 0.5rem;
    margin-top: 0.75rem;
}

.notes-btn {
    padding: 0.5rem 1rem;
    font-size: 0.75rem;
    font-weight: 500;
    border-radius: 4px;
    cursor: pointer;
    border: 1px solid transparent;
    transition: all 0.2s;
}

.notes-btn.primary {
    background: var(--admin-primary, #1e3a5f);
    color: #fff;
}

.notes-btn.primary:hover {
    background: #2d5a87;
}

.notes-btn.outline {
    background: #fff;
    color: var(--admin-primary, #1e3a5f);
    border-color: var(--admin-primary, #1e3a5f);
}

.notes-btn.outline:hover {
    background: #f8fafc;
}

.internal-note-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    background: #fef3c7;
    color: #92400e;
    padding: 0.125rem 0.5rem;
    border-radius: 4px;
    font-size: 0.65rem;
    font-weight: 600;
    margin-left: 0.5rem;
}

.external-validation-status {
    margin-bottom: 0.5rem;
}

.validation-status-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 0.75rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 600;
}

.validation-status-pill.not-sent {
    background: #f1f5f9;
    color: #64748b;
    border: 1px solid #e2e8f0;
}

.validation-status-pill.in-progress {
    background: #dbeafe;
    color: #1e40af;
    border: 1px solid #93c5fd;
}

.validation-status-pill.in-progress i {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.validation-status-pill.passed {
    background: #dcfce7;
    color: #166534;
    border: 1px solid #86efac;
}

.validation-status-pill.failed {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #fca5a5;
}

.validation-status-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-top: 0.5rem;
    font-size: 0.7rem;
    color: #64748b;
}

.validation-status-meta .meta-item {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    background: #f8fafc;
    padding: 0.2rem 0.4rem;
    border-radius: 3px;
}

.validation-status-meta .meta-item.ref-id {
    font-family: monospace;
    background: #e0e7ff;
    color: #3730a3;
}
</style>

<div class="admin-action-panel" @if(isset($isModal) && $isModal) style="border: none;" @endif>
    @if(!isset($isModal) || !$isModal)
    <div class="admin-action-panel-header">
        <i class="fas fa-gavel"></i>
        <span>Admin Actions</span>
        <span class="internal-note-badge"><i class="fas fa-lock"></i> INTERNAL ONLY</span>
    </div>
    @endif
    <div class="admin-action-panel-body">
        <div class="admin-action-group">
            <div class="admin-action-group-title">Standard Actions</div>
            <div class="admin-action-buttons">
                <button class="admin-action-btn approve" onclick="approveEntity()">
                    <i class="fas fa-check-circle"></i>
                    <span>Approve</span>
                </button>
                <button class="admin-action-btn reject" onclick="showRejectModal()">
                    <i class="fas fa-times-circle"></i>
                    <span>Reject</span>
                </button>
                <button class="admin-action-btn return" onclick="returnToCustomer()">
                    <i class="fas fa-reply"></i>
                    <span>Return to Customer</span>
                </button>
            </div>
        </div>

        <div class="admin-action-group">
            <div class="admin-action-group-title">External Validation</div>
            <div class="external-validation-status" id="externalValidationStatus">
                <div class="validation-status-pill not-sent">
                    <i class="fas fa-circle"></i>
                    <span>Not Sent</span>
                </div>
            </div>
            <div class="admin-action-buttons" style="margin-top: 0.75rem;">
                <button class="admin-action-btn validation" onclick="showExternalValidationModal()" id="submitExternalBtn">
                    <i class="fas fa-shield-alt"></i>
                    <span>Submit to {{ $validationProvider ?? 'External Validation' }}</span>
                </button>
            </div>
        </div>

        <div class="admin-divider"></div>

        <div class="notes-section">
            <div class="notes-tabs">
                <button class="notes-tab active" onclick="switchNotesTab('internal')">
                    <i class="fas fa-lock me-1"></i> Internal Notes
                </button>
                <button class="notes-tab" onclick="switchNotesTab('customer')">
                    <i class="fas fa-envelope me-1"></i> Customer Message
                </button>
            </div>
            <div class="notes-tab-content">
                <div class="notes-tab-pane active" id="tab-internal">
                    <textarea class="notes-textarea" id="internalNoteText" placeholder="Add internal note (admin-only, never visible to customer)..."></textarea>
                    <div class="notes-actions">
                        <button class="notes-btn primary" onclick="addInternalNote()">
                            <i class="fas fa-plus me-1"></i> Add Note
                        </button>
                    </div>
                </div>
                <div class="notes-tab-pane" id="tab-customer">
                    <textarea class="notes-textarea" id="customerMessageText" placeholder="Message to customer (shown when returned/rejected)..."></textarea>
                    <div class="notes-actions">
                        <button class="notes-btn outline" onclick="previewCustomerMessage()">
                            <i class="fas fa-eye me-1"></i> Preview
                        </button>
                        <button class="notes-btn primary" onclick="sendCustomerMessage()">
                            <i class="fas fa-paper-plane me-1"></i> Send
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
var AdminApprovalContext = {
    entityType: '{{ $entityType ?? "unknown" }}',
    entityId: '{{ $entityId ?? "" }}',
    submissionId: '{{ $submissionId ?? "" }}',
    versionId: '{{ $versionId ?? "1" }}',
    providerName: '{{ $validationProvider ?? "External Provider" }}'
};

function getInternalNotesSummary() {
    var noteText = document.getElementById('internalNoteText');
    return noteText ? noteText.value.trim().substring(0, 200) : null;
}

function getCustomerMessage() {
    var msgText = document.getElementById('customerMessageText');
    return msgText ? msgText.value.trim() : null;
}

function approveEntity() {
    if (!confirm('Are you sure you want to approve this ' + AdminApprovalContext.entityType.replace('_', ' ') + '?')) {
        return;
    }
    
    if (typeof ADMIN_AUDIT !== 'undefined') {
        ADMIN_AUDIT.logApproval(
            AdminApprovalContext.entityType,
            AdminApprovalContext.entityId,
            AdminApprovalContext.submissionId,
            AdminApprovalContext.versionId,
            getInternalNotesSummary()
        );
    }
    
    if (typeof UNIFIED_APPROVAL !== 'undefined') {
        UNIFIED_APPROVAL.approve();
    }
    
    alert('Entity approved successfully.');
    location.reload();
}

function showRejectModal() {
    var modal = document.getElementById('rejectModal');
    if (!modal) {
        createRejectModal();
        modal = document.getElementById('rejectModal');
    }
    new bootstrap.Modal(modal).show();
}

function createRejectModal() {
    var html = '\
    <div class="modal fade" id="rejectModal" tabindex="-1">\
        <div class="modal-dialog">\
            <div class="modal-content">\
                <div class="modal-header" style="background: #991b1b; color: #fff;">\
                    <h5 class="modal-title"><i class="fas fa-times-circle me-2"></i>Reject Entity</h5>\
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>\
                </div>\
                <div class="modal-body">\
                    <div class="alert alert-danger" style="font-size: 0.85rem;">\
                        <i class="fas fa-exclamation-triangle me-2"></i>\
                        <strong>Warning:</strong> This action is final. The customer will be notified.\
                    </div>\
                    <div class="mb-3">\
                        <label class="form-label fw-semibold">Reason for Rejection <span class="text-danger">*</span></label>\
                        <select class="form-select" id="rejectReasonCode">\
                            <option value="">Select reason...</option>\
                            <option value="INVALID_BUSINESS">Invalid Business Information</option>\
                            <option value="POLICY_VIOLATION">Policy Violation</option>\
                            <option value="FRAUDULENT">Suspected Fraudulent Activity</option>\
                            <option value="DUPLICATE">Duplicate Registration</option>\
                            <option value="INCOMPLETE">Incomplete Documentation</option>\
                            <option value="OTHER">Other</option>\
                        </select>\
                    </div>\
                    <div class="mb-3">\
                        <label class="form-label fw-semibold">Customer Message <span class="text-danger">*</span></label>\
                        <textarea class="form-control" id="rejectCustomerMessage" rows="4" placeholder="Explain why this was rejected (visible to customer)..." minlength="20"></textarea>\
                        <div class="form-text">Minimum 20 characters required.</div>\
                    </div>\
                </div>\
                <div class="modal-footer">\
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>\
                    <button type="button" class="btn btn-danger" onclick="confirmReject()">\
                        <i class="fas fa-times me-1"></i>Confirm Rejection\
                    </button>\
                </div>\
            </div>\
        </div>\
    </div>';
    document.body.insertAdjacentHTML('beforeend', html);
}

function confirmReject() {
    var reasonCode = document.getElementById('rejectReasonCode').value;
    var customerMessage = document.getElementById('rejectCustomerMessage').value.trim();
    
    if (!reasonCode) {
        alert('Please select a reason for rejection.');
        return;
    }
    if (customerMessage.length < 20) {
        alert('Customer message must be at least 20 characters.');
        return;
    }
    
    bootstrap.Modal.getInstance(document.getElementById('rejectModal')).hide();
    
    if (typeof ADMIN_AUDIT !== 'undefined') {
        ADMIN_AUDIT.logRejection(
            AdminApprovalContext.entityType,
            AdminApprovalContext.entityId,
            AdminApprovalContext.submissionId,
            AdminApprovalContext.versionId,
            customerMessage,
            getInternalNotesSummary()
        );
    }
    
    if (typeof UNIFIED_APPROVAL !== 'undefined') {
        UNIFIED_APPROVAL.reject(reasonCode, customerMessage);
    }
    
    alert('Entity rejected. Customer will be notified.');
    location.reload();
}

function returnToCustomer() {
    var modal = document.getElementById('returnModal');
    if (!modal) {
        createReturnModal();
        modal = document.getElementById('returnModal');
    }
    new bootstrap.Modal(modal).show();
}

function createReturnModal() {
    var html = '\
    <div class="modal fade" id="returnModal" tabindex="-1">\
        <div class="modal-dialog">\
            <div class="modal-content">\
                <div class="modal-header" style="background: #92400e; color: #fff;">\
                    <h5 class="modal-title"><i class="fas fa-undo me-2"></i>Return to Customer</h5>\
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>\
                </div>\
                <div class="modal-body">\
                    <div class="alert alert-warning" style="font-size: 0.85rem;">\
                        <i class="fas fa-info-circle me-2"></i>\
                        The customer will be asked to make changes and resubmit.\
                    </div>\
                    <div class="mb-3">\
                        <label class="form-label fw-semibold">Reason Code <span class="text-danger">*</span></label>\
                        <select class="form-select" id="returnReasonCode">\
                            <option value="">Select reason...</option>\
                            <option value="ADDITIONAL_INFO">Additional Information Required</option>\
                            <option value="CLARIFICATION">Clarification Needed</option>\
                            <option value="DOCUMENT_UPDATE">Document Update Required</option>\
                            <option value="FORMATTING">Formatting Issues</option>\
                            <option value="OTHER">Other</option>\
                        </select>\
                    </div>\
                    <div class="mb-3">\
                        <label class="form-label fw-semibold">Customer Message <span class="text-danger">*</span></label>\
                        <textarea class="form-control" id="returnCustomerMessage" rows="4" placeholder="Explain what changes are needed (visible to customer)..." minlength="20"></textarea>\
                        <div class="form-text">Minimum 20 characters required.</div>\
                    </div>\
                </div>\
                <div class="modal-footer">\
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>\
                    <button type="button" class="btn btn-warning" onclick="confirmReturn()">\
                        <i class="fas fa-undo me-1"></i>Return to Customer\
                    </button>\
                </div>\
            </div>\
        </div>\
    </div>';
    document.body.insertAdjacentHTML('beforeend', html);
}

function confirmReturn() {
    var reasonCode = document.getElementById('returnReasonCode').value;
    var customerMessage = document.getElementById('returnCustomerMessage').value.trim();
    
    if (!reasonCode) {
        alert('Please select a reason code.');
        return;
    }
    if (customerMessage.length < 20) {
        alert('Customer message must be at least 20 characters.');
        return;
    }
    
    bootstrap.Modal.getInstance(document.getElementById('returnModal')).hide();
    
    if (typeof ADMIN_AUDIT !== 'undefined') {
        ADMIN_AUDIT.logReturn(
            AdminApprovalContext.entityType,
            AdminApprovalContext.entityId,
            AdminApprovalContext.submissionId,
            AdminApprovalContext.versionId,
            customerMessage,
            reasonCode,
            getInternalNotesSummary()
        );
    }
    
    if (typeof UNIFIED_APPROVAL !== 'undefined') {
        UNIFIED_APPROVAL.returnToCustomer(reasonCode, customerMessage);
    }
    
    alert('Returned to customer. They will be notified to make changes.');
    location.reload();
}

function forceApprove() {
    var modal = document.getElementById('forceApproveModal');
    if (!modal) {
        createForceApproveModal();
        modal = document.getElementById('forceApproveModal');
    }
    new bootstrap.Modal(modal).show();
}

function createForceApproveModal() {
    var html = '\
    <div class="modal fade" id="forceApproveModal" tabindex="-1">\
        <div class="modal-dialog">\
            <div class="modal-content">\
                <div class="modal-header" style="background: #dc2626; color: #fff;">\
                    <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Force Approve (Override)</h5>\
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>\
                </div>\
                <div class="modal-body">\
                    <div class="alert alert-danger" style="font-size: 0.85rem;">\
                        <i class="fas fa-shield-alt me-2"></i>\
                        <strong>CRITICAL:</strong> Force approval bypasses all validation checks. This action will be logged with CRITICAL severity and reviewed.\
                    </div>\
                    <div class="mb-3">\
                        <label class="form-label fw-semibold">Justification <span class="text-danger">*</span></label>\
                        <textarea class="form-control" id="forceApproveReason" rows="4" placeholder="Explain why external validation is being bypassed..." minlength="50"></textarea>\
                        <div class="form-text">Minimum 50 characters required. This will be audited.</div>\
                    </div>\
                    <div class="form-check mb-3">\
                        <input class="form-check-input" type="checkbox" id="forceApproveConfirm">\
                        <label class="form-check-label" for="forceApproveConfirm">\
                            I understand this bypasses external validation and accept responsibility\
                        </label>\
                    </div>\
                </div>\
                <div class="modal-footer">\
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>\
                    <button type="button" class="btn btn-danger" onclick="confirmForceApprove()">\
                        <i class="fas fa-bolt me-1"></i>Force Approve\
                    </button>\
                </div>\
            </div>\
        </div>\
    </div>';
    document.body.insertAdjacentHTML('beforeend', html);
}

function confirmForceApprove() {
    var reason = document.getElementById('forceApproveReason').value.trim();
    var confirmed = document.getElementById('forceApproveConfirm').checked;
    
    if (reason.length < 50) {
        alert('Justification must be at least 50 characters.');
        return;
    }
    if (!confirmed) {
        alert('You must confirm understanding of the override implications.');
        return;
    }
    
    bootstrap.Modal.getInstance(document.getElementById('forceApproveModal')).hide();
    
    if (typeof ADMIN_AUDIT !== 'undefined') {
        ADMIN_AUDIT.logForceApprove(
            AdminApprovalContext.entityType,
            AdminApprovalContext.entityId,
            reason,
            'in_review',
            AdminApprovalContext.submissionId,
            AdminApprovalContext.versionId,
            getInternalNotesSummary()
        );
    }
    
    if (typeof UNIFIED_APPROVAL !== 'undefined') {
        UNIFIED_APPROVAL.forceApprove(reason);
    }
    
    alert('Entity force approved. This action has been logged with CRITICAL severity.');
    location.reload();
}

function switchNotesTab(tab) {
    document.querySelectorAll('.notes-tab').forEach(function(t) {
        t.classList.remove('active');
    });
    document.querySelectorAll('.notes-tab-pane').forEach(function(p) {
        p.classList.remove('active');
    });
    
    event.target.closest('.notes-tab').classList.add('active');
    document.getElementById('tab-' + tab).classList.add('active');
}

function addInternalNote() {
    var note = document.getElementById('internalNoteText').value.trim();
    if (!note) {
        alert('Please enter a note.');
        return;
    }
    
    if (typeof ADMIN_AUDIT !== 'undefined') {
        ADMIN_AUDIT.log({
            eventCode: 'INTERNAL_NOTE_ADDED',
            module: '{{ $entityType ?? "approval" }}',
            entityType: '{{ $entityType ?? "unknown" }}',
            entityId: '{{ $entityId ?? "" }}',
            previousValue: null,
            newValue: note.substring(0, 100) + '...',
            severity: 'LOW'
        });
    }
    
    if (typeof AdminControlPlane !== 'undefined') {
        AdminControlPlane.logAdminAction('ADD_INTERNAL_NOTE', '{{ $entityId ?? "" }}', { note: note.substring(0, 100) }, 'LOW');
    }
    
    document.getElementById('internalNoteText').value = '';
    alert('Internal note added.');
}

function previewCustomerMessage() {
    var message = document.getElementById('customerMessageText').value.trim();
    if (!message) {
        alert('Please enter a message.');
        return;
    }
    
    if (typeof ADMIN_NOTIFICATIONS !== 'undefined') {
        ADMIN_NOTIFICATIONS.previewEmail({
            entityType: '{{ $entityType ?? "unknown" }}',
            entityId: '{{ $entityId ?? "" }}',
            message: message
        });
    } else {
        alert('Preview:\n\n' + message);
    }
}

function sendCustomerMessage() {
    var message = document.getElementById('customerMessageText').value.trim();
    if (!message) {
        alert('Please enter a message.');
        return;
    }
    
    if (!confirm('Send this message to the customer?')) {
        return;
    }
    
    if (typeof ADMIN_AUDIT !== 'undefined') {
        ADMIN_AUDIT.log({
            eventCode: 'CUSTOMER_MESSAGE_SENT',
            module: '{{ $entityType ?? "approval" }}',
            entityType: '{{ $entityType ?? "unknown" }}',
            entityId: '{{ $entityId ?? "" }}',
            previousValue: null,
            newValue: 'message_sent',
            severity: 'MEDIUM'
        });
    }
    
    if (typeof AdminControlPlane !== 'undefined') {
        AdminControlPlane.logAdminAction('SEND_CUSTOMER_MESSAGE', '{{ $entityId ?? "" }}', {}, 'MEDIUM');
    }
    
    document.getElementById('customerMessageText').value = '';
    alert('Message sent to customer.');
}

var ExternalValidation = (function() {
    var providerName = '{{ $validationProvider ?? "External Provider" }}';
    var entityType = '{{ $entityType ?? "unknown" }}';
    var entityId = '{{ $entityId ?? "" }}';
    
    var currentStatus = 'not-sent';
    var currentRefId = null;
    var submittedAt = null;
    
    function init() {
        if (typeof UNIFIED_APPROVAL !== 'undefined') {
            var history = UNIFIED_APPROVAL.getExternalValidationHistory();
            if (history && history.length > 0) {
                var latest = history[history.length - 1];
                updateStatusFromHistory(latest);
            }
        }
    }
    
    function updateStatusFromHistory(entry) {
        currentRefId = entry.externalRequestId;
        submittedAt = entry.timestamp;
        
        var status = (entry.status || '').toUpperCase();
        
        if (status === 'SUBMITTED' || status === 'IN_PROGRESS' || status === 'PENDING') {
            currentStatus = 'in-progress';
        } else if (status === 'VERIFIED' || status === 'PASSED' || status === 'APPROVED') {
            currentStatus = 'passed';
        } else if (status === 'FAILED' || status === 'REJECTED' || status === 'ERROR') {
            currentStatus = 'failed';
        } else {
            currentStatus = 'not-sent';
        }
        
        renderStatus();
    }
    
    function renderStatus() {
        var container = document.getElementById('externalValidationStatus');
        if (!container) return;
        
        var icons = {
            'not-sent': 'fa-circle',
            'in-progress': 'fa-spinner',
            'passed': 'fa-check-circle',
            'failed': 'fa-times-circle'
        };
        
        var labels = {
            'not-sent': 'Not Sent',
            'in-progress': 'In Progress',
            'passed': 'Passed',
            'failed': 'Failed'
        };
        
        var html = '<div class="validation-status-pill ' + currentStatus + '">' +
            '<i class="fas ' + icons[currentStatus] + '"></i>' +
            '<span>' + labels[currentStatus] + '</span>' +
            '</div>';
        
        if (currentRefId || submittedAt) {
            html += '<div class="validation-status-meta">';
            if (currentRefId) {
                html += '<span class="meta-item ref-id"><i class="fas fa-hashtag"></i>' + escapeHtml(currentRefId) + '</span>';
            }
            if (submittedAt) {
                html += '<span class="meta-item"><i class="fas fa-clock"></i>' + formatDate(submittedAt) + '</span>';
            }
            html += '</div>';
        }
        
        container.innerHTML = html;
        
        var submitBtn = document.getElementById('submitExternalBtn');
        if (submitBtn) {
            if (currentStatus === 'in-progress') {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Awaiting Response...</span>';
            } else if (currentStatus === 'passed') {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-check"></i> <span>Validation Complete</span>';
            } else if (currentStatus === 'failed') {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-redo"></i> <span>Retry ' + providerName + '</span>';
            }
        }
    }
    
    function escapeHtml(text) {
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    function formatDate(timestamp) {
        try {
            var date = new Date(timestamp);
            return date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short' }) + ', ' +
                   date.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' });
        } catch (e) {
            return timestamp;
        }
    }
    
    function showModal() {
        var modal = document.getElementById('externalValidationModal');
        if (!modal) {
            createModal();
            modal = document.getElementById('externalValidationModal');
        }
        
        document.getElementById('extValProviderName').textContent = providerName;
        document.getElementById('extValEntityType').textContent = entityType.toUpperCase().replace('_', ' ');
        document.getElementById('extValEntityId').textContent = entityId;
        
        new bootstrap.Modal(modal).show();
    }
    
    function createModal() {
        var modalHtml = '\
        <div class="modal fade" id="externalValidationModal" tabindex="-1">\
            <div class="modal-dialog">\
                <div class="modal-content">\
                    <div class="modal-header" style="background: var(--admin-primary, #1e3a5f); color: #fff;">\
                        <h5 class="modal-title"><i class="fas fa-shield-alt me-2"></i>Submit to External Validation</h5>\
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>\
                    </div>\
                    <div class="modal-body">\
                        <div class="alert alert-info" style="font-size: 0.85rem;">\
                            <i class="fas fa-info-circle me-2"></i>\
                            You are about to submit this entity to <strong id="extValProviderName"></strong> for external validation.\
                        </div>\
                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 1rem; margin-bottom: 1rem;">\
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">\
                                <span style="color: #64748b; font-size: 0.8rem;">Entity Type:</span>\
                                <span style="font-weight: 600;" id="extValEntityType"></span>\
                            </div>\
                            <div style="display: flex; justify-content: space-between;">\
                                <span style="color: #64748b; font-size: 0.8rem;">Entity ID:</span>\
                                <span style="font-weight: 600; font-family: monospace;" id="extValEntityId"></span>\
                            </div>\
                        </div>\
                        <div style="background: #fef3c7; border: 1px solid #fcd34d; border-radius: 6px; padding: 0.75rem; font-size: 0.8rem; color: #92400e;">\
                            <i class="fas fa-exclamation-triangle me-2"></i>\
                            <strong>Note:</strong> This will create a validation request. The entity status will change to "Validation In Progress" until a response is received.\
                        </div>\
                    </div>\
                    <div class="modal-footer">\
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>\
                        <button type="button" class="btn btn-primary" onclick="ExternalValidation.confirmSubmit()" style="background: var(--admin-primary, #1e3a5f); border-color: var(--admin-primary, #1e3a5f);">\
                            <i class="fas fa-paper-plane me-1"></i>Submit for Validation\
                        </button>\
                    </div>\
                </div>\
            </div>\
        </div>';
        
        document.body.insertAdjacentHTML('beforeend', modalHtml);
    }
    
    function confirmSubmit() {
        bootstrap.Modal.getInstance(document.getElementById('externalValidationModal')).hide();
        
        var payload = collectPayload();
        var entry = null;
        
        if (typeof UNIFIED_APPROVAL !== 'undefined') {
            entry = UNIFIED_APPROVAL.submitToExternalProvider(payload);
        }
        
        if (entry) {
            currentRefId = entry.externalRequestId;
            submittedAt = entry.timestamp;
        } else {
            currentRefId = generateRequestId();
            submittedAt = new Date().toISOString();
        }
        
        currentStatus = 'in-progress';
        renderStatus();
        
        var jobRecord = {
            id: entry ? entry.id : ('JOB-' + Date.now()),
            provider: providerName.toLowerCase().replace(/\s+/g, '_'),
            externalRequestId: currentRefId,
            entityType: entityType,
            entityId: entityId,
            status: 'IN_PROGRESS',
            payload: payload,
            submittedAt: submittedAt,
            submittedBy: 'admin@quicksms.co.uk',
            responseAt: null,
            responseCode: null,
            responseMessage: null
        };
        
        storeValidationJob(jobRecord);
        
        if (typeof ADMIN_AUDIT !== 'undefined') {
            ADMIN_AUDIT.logSubmitExternal(
                entityType,
                entityId,
                AdminApprovalContext.submissionId,
                AdminApprovalContext.versionId,
                providerName,
                currentRefId
            );
        }
    }
    
    function generateRequestId() {
        var prefix = providerName.toLowerCase().indexOf('brand') !== -1 ? 'BASRQ-' : 'RCSP-';
        return prefix + Math.random().toString(36).substring(2, 10).toUpperCase();
    }
    
    function collectPayload() {
        if (typeof UNIFIED_APPROVAL !== 'undefined') {
            var entity = UNIFIED_APPROVAL.getCurrentEntity();
            return entity ? entity.data : {};
        }
        return {};
    }
    
    function storeValidationJob(job) {
        try {
            var jobs = JSON.parse(localStorage.getItem('validationJobs') || '[]');
            jobs.push(job);
            localStorage.setItem('validationJobs', JSON.stringify(jobs));
        } catch (e) {
            console.warn('[ExternalValidation] Could not store job:', e);
        }
    }
    
    function getValidationJobs(entityId) {
        try {
            var jobs = JSON.parse(localStorage.getItem('validationJobs') || '[]');
            return jobs.filter(function(j) { return j.entityId === entityId; });
        } catch (e) {
            return [];
        }
    }
    
    document.addEventListener('DOMContentLoaded', init);
    
    return {
        init: init,
        showModal: showModal,
        confirmSubmit: confirmSubmit,
        renderStatus: renderStatus,
        getValidationJobs: getValidationJobs
    };
})();

function showExternalValidationModal() {
    ExternalValidation.showModal();
}
</script>
