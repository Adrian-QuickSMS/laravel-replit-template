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
</style>

<div class="admin-action-panel">
    <div class="admin-action-panel-header">
        <i class="fas fa-gavel"></i>
        <span>Admin Actions</span>
        <span class="internal-note-badge"><i class="fas fa-lock"></i> INTERNAL ONLY</span>
    </div>
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
            <div class="admin-action-buttons">
                <button class="admin-action-btn validation" onclick="submitToExternalValidation()">
                    <i class="fas fa-shield-alt"></i>
                    <span>Submit to {{ $validationProvider ?? 'External Validation' }}</span>
                </button>
            </div>
        </div>

        <div class="admin-divider"></div>

        <div class="admin-action-group">
            <div class="admin-action-group-title">Enterprise Override</div>
            <div class="admin-action-buttons">
                <button class="admin-action-btn force-approve" onclick="forceApprove()">
                    <i class="fas fa-bolt"></i>
                    <span>Force Approve (Enterprise)</span>
                </button>
            </div>
            <small class="text-muted d-block mt-1" style="font-size: 0.7rem;">
                <i class="fas fa-exclamation-triangle me-1"></i>
                Bypasses validation. Logged with CRITICAL severity.
            </small>
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
</script>
