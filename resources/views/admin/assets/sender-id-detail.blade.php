@extends('layouts.admin')

@section('title', 'SenderID Approval Detail')

@push('styles')
<style>
.detail-page { padding: 1.5rem; }

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1.5rem;
}

.page-header h4 {
    color: var(--admin-primary);
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.page-header p {
    color: #64748b;
    font-size: 0.875rem;
    margin: 0;
}

.back-link {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: #64748b;
    text-decoration: none;
    font-size: 0.875rem;
    margin-bottom: 1rem;
}

.back-link:hover { color: var(--admin-primary); }

.status-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.5rem;
    padding: 1rem 1.5rem;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
}

.sender-id-display {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--admin-primary);
    font-family: 'SF Mono', monospace;
    background: #e0e7ff;
    padding: 0.5rem 1rem;
    border-radius: 6px;
}

.status-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.375rem 0.875rem;
    border-radius: 50px;
    font-size: 0.8rem;
    font-weight: 600;
}

.status-pill.submitted { background: #dbeafe; color: #1e40af; }
.status-pill.in-review { background: #e0e7ff; color: #3730a3; }
.status-pill.returned-to-customer { background: #fef3c7; color: #92400e; }
.status-pill.validation-in-progress { background: #fce7f3; color: #9d174d; }
.status-pill.validation-failed { background: #fee2e2; color: #991b1b; }
.status-pill.approved { background: #d9f99d; color: #3f6212; }
.status-pill.rejected { background: #fecaca; color: #7f1d1d; }
.status-pill.live { background: #bbf7d0; color: #15803d; }

.detail-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 1.5rem;
}

.detail-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    overflow: hidden;
}

.detail-card-header {
    background: #f8fafc;
    padding: 0.875rem 1.25rem;
    border-bottom: 1px solid #e2e8f0;
    font-weight: 600;
    color: var(--admin-primary);
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.detail-card-body { padding: 1.25rem; }

.detail-row {
    display: flex;
    justify-content: space-between;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f1f5f9;
}

.detail-row:last-child { border-bottom: none; }

.detail-label {
    font-size: 0.8rem;
    color: #64748b;
    font-weight: 500;
}

.detail-value {
    font-size: 0.875rem;
    color: #1e293b;
    font-weight: 500;
    text-align: right;
}

.detail-value.mono {
    font-family: 'SF Mono', monospace;
    background: #f1f5f9;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
}

.yes-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    background: #d9f99d;
    color: #3f6212;
    padding: 0.125rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 600;
}

.no-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    background: #fecaca;
    color: #991b1b;
    padding: 0.125rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 600;
}

.channels-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 0.75rem;
}

.channel-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 0.875rem;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    font-size: 0.8rem;
}

.channel-item.enabled {
    background: #f0fdf4;
    border-color: #86efac;
}

.channel-item.disabled {
    background: #fef2f2;
    border-color: #fecaca;
    color: #9ca3af;
}

.channel-icon {
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
    font-size: 0.7rem;
}

.channel-item.enabled .channel-icon { background: #bbf7d0; color: #15803d; }
.channel-item.disabled .channel-icon { background: #fee2e2; color: #dc2626; }

.validation-section { margin-top: 1.5rem; }

.validation-item {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    padding: 0.75rem;
    border-radius: 6px;
    margin-bottom: 0.5rem;
}

.validation-item.pass { background: #f0fdf4; border: 1px solid #bbf7d0; }
.validation-item.fail { background: #fef2f2; border: 1px solid #fecaca; }
.validation-item.warn { background: #fffbeb; border: 1px solid #fde68a; }

.validation-icon {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.65rem;
    flex-shrink: 0;
    margin-top: 2px;
}

.validation-item.pass .validation-icon { background: #22c55e; color: #fff; }
.validation-item.fail .validation-icon { background: #ef4444; color: #fff; }
.validation-item.warn .validation-icon { background: #f59e0b; color: #fff; }

.validation-content { flex: 1; }

.validation-title {
    font-weight: 600;
    font-size: 0.8rem;
    color: #1e293b;
    margin-bottom: 0.125rem;
}

.validation-detail {
    font-size: 0.75rem;
    color: #64748b;
}

.explanation-box {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 1rem;
    font-size: 0.875rem;
    color: #475569;
    font-style: italic;
    margin-top: 0.5rem;
}

.action-panel {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 1.25rem;
    margin-top: 1.5rem;
}

.action-panel-title {
    font-weight: 600;
    color: var(--admin-primary);
    margin-bottom: 1rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid #e2e8f0;
}

.action-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
}

.action-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 1rem;
    border-radius: 6px;
    font-size: 0.8rem;
    font-weight: 500;
    cursor: pointer;
    border: 1px solid transparent;
    transition: all 0.2s;
}

.action-btn.primary {
    background: var(--admin-primary);
    color: #fff;
}

.action-btn.primary:hover { background: var(--admin-accent); }

.action-btn.success {
    background: #22c55e;
    color: #fff;
}

.action-btn.success:hover { background: #16a34a; }

.action-btn.warning {
    background: #f59e0b;
    color: #fff;
}

.action-btn.warning:hover { background: #d97706; }

.action-btn.danger {
    background: #ef4444;
    color: #fff;
}

.action-btn.danger:hover { background: #dc2626; }

.action-btn.outline {
    background: #fff;
    border-color: #e2e8f0;
    color: #475569;
}

.action-btn.outline:hover {
    border-color: var(--admin-primary);
    color: var(--admin-primary);
}

.action-btn.enterprise {
    background: linear-gradient(135deg, #7c3aed, #a855f7);
    color: #fff;
}

.action-btn.enterprise:hover { opacity: 0.9; }

.notes-section { margin-top: 1.5rem; }

.notes-tabs {
    display: flex;
    gap: 0;
    border-bottom: 2px solid #e2e8f0;
    margin-bottom: 1rem;
}

.notes-tab {
    padding: 0.75rem 1.25rem;
    font-size: 0.85rem;
    font-weight: 500;
    color: #64748b;
    cursor: pointer;
    border-bottom: 2px solid transparent;
    margin-bottom: -2px;
    background: none;
    border: none;
}

.notes-tab.active {
    color: var(--admin-primary);
    border-bottom-color: var(--admin-primary);
}

.notes-content { display: none; }
.notes-content.active { display: block; }

.notes-textarea {
    width: 100%;
    min-height: 120px;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 0.875rem;
    font-size: 0.875rem;
    resize: vertical;
}

.notes-textarea:focus {
    outline: none;
    border-color: var(--admin-primary);
    box-shadow: 0 0 0 3px rgba(30, 58, 95, 0.1);
}

.note-entry {
    padding: 0.875rem;
    background: #f8fafc;
    border-radius: 6px;
    margin-bottom: 0.75rem;
}

.note-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.note-author {
    font-weight: 600;
    font-size: 0.8rem;
    color: var(--admin-primary);
}

.note-time {
    font-size: 0.7rem;
    color: #94a3b8;
}

.note-text {
    font-size: 0.85rem;
    color: #475569;
}

.audit-trail {
    max-height: 300px;
    overflow-y: auto;
}

.audit-entry {
    display: flex;
    gap: 0.75rem;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f1f5f9;
}

.audit-entry:last-child { border-bottom: none; }

.audit-icon {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.7rem;
    background: #e0e7ff;
    color: #3730a3;
    flex-shrink: 0;
}

.audit-content { flex: 1; }

.audit-action {
    font-size: 0.8rem;
    color: #1e293b;
    font-weight: 500;
}

.audit-meta {
    font-size: 0.7rem;
    color: #94a3b8;
    margin-top: 0.125rem;
}

.context-info {
    display: flex;
    gap: 1.5rem;
    padding: 0.75rem 1rem;
    background: #f0f9ff;
    border: 1px solid #bae6fd;
    border-radius: 6px;
    margin-bottom: 1rem;
}

.context-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.8rem;
}

.context-label { color: #64748b; }
.context-value { color: var(--admin-primary); font-weight: 600; }

.sidebar-card { margin-bottom: 1rem; }
</style>
@endpush

@section('content')
<div class="detail-page">
    <a href="{{ route('admin.approval-queue') }}" class="back-link">
        <i class="fas fa-arrow-left"></i> Back to Approval Queue
    </a>

    <div class="admin-breadcrumb">
        <a href="{{ route('admin.dashboard') }}">Admin</a>
        <span class="separator">/</span>
        <a href="{{ route('admin.approval-queue') }}">Approval Queue</a>
        <span class="separator">/</span>
        <span>SenderID Detail</span>
    </div>

    <div class="status-header">
        <span class="sender-id-display" id="senderIdValue">ACMEBANK</span>
        <span class="status-pill submitted" id="currentStatus"><i class="fas fa-paper-plane"></i> Submitted</span>
        <div style="margin-left: auto; display: flex; gap: 1rem; font-size: 0.8rem; color: #64748b;">
            <span><i class="fas fa-hashtag me-1"></i>Request ID: <strong>SID-001</strong></span>
            <span><i class="fas fa-clock me-1"></i>Submitted: <strong>Jan 20, 2026, 10:15 AM</strong></span>
        </div>
    </div>

    <div class="context-info">
        <div class="context-item">
            <i class="fas fa-building"></i>
            <span class="context-label">Account:</span>
            <span class="context-value">Acme Corporation</span>
        </div>
        <div class="context-item">
            <i class="fas fa-sitemap"></i>
            <span class="context-label">Sub-Account:</span>
            <span class="context-value">Marketing Dept</span>
        </div>
        <div class="context-item">
            <i class="fas fa-user"></i>
            <span class="context-label">Submitted By:</span>
            <span class="context-value">John Smith (j.smith@acme.com)</span>
        </div>
    </div>

    <div class="detail-grid">
        <div class="main-content">
            <div class="detail-card">
                <div class="detail-card-header">
                    <i class="fas fa-id-card"></i> SenderID Information
                </div>
                <div class="detail-card-body">
                    <div class="detail-row">
                        <span class="detail-label">SenderID Value</span>
                        <span class="detail-value mono">ACMEBANK</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">SenderID Type</span>
                        <span class="detail-value">Alphanumeric</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Brand / Business Name</span>
                        <span class="detail-value">Acme Bank Ltd</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Permission Confirmation</span>
                        <span class="detail-value">
                            <span class="yes-badge"><i class="fas fa-check"></i> Yes</span>
                        </span>
                    </div>

                    <div style="margin-top: 1rem;">
                        <div class="detail-label" style="margin-bottom: 0.5rem;">Customer Explanation</div>
                        <div class="explanation-box">
                            "We are registering ACMEBANK as our official sender ID for transactional banking notifications including balance alerts, payment confirmations, and security notifications to our customers."
                        </div>
                    </div>
                </div>
            </div>

            <div class="detail-card" style="margin-top: 1rem;">
                <div class="detail-card-header">
                    <i class="fas fa-broadcast-tower"></i> Enabled Channels
                </div>
                <div class="detail-card-body">
                    <div class="channels-grid">
                        <div class="channel-item enabled">
                            <div class="channel-icon"><i class="fas fa-desktop"></i></div>
                            <span>Portal</span>
                            <i class="fas fa-check-circle ms-auto" style="color: #22c55e;"></i>
                        </div>
                        <div class="channel-item enabled">
                            <div class="channel-icon"><i class="fas fa-inbox"></i></div>
                            <span>Inbox</span>
                            <i class="fas fa-check-circle ms-auto" style="color: #22c55e;"></i>
                        </div>
                        <div class="channel-item disabled">
                            <div class="channel-icon"><i class="fas fa-envelope"></i></div>
                            <span>Email-to-SMS</span>
                            <i class="fas fa-times-circle ms-auto" style="color: #dc2626;"></i>
                        </div>
                        <div class="channel-item enabled">
                            <div class="channel-icon"><i class="fas fa-code"></i></div>
                            <span>API</span>
                            <i class="fas fa-check-circle ms-auto" style="color: #22c55e;"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="detail-card validation-section">
                <div class="detail-card-header">
                    <i class="fas fa-shield-alt"></i> Automated Validation Results
                </div>
                <div class="detail-card-body">
                    <div class="validation-item pass">
                        <div class="validation-icon"><i class="fas fa-check"></i></div>
                        <div class="validation-content">
                            <div class="validation-title">Character Rules</div>
                            <div class="validation-detail">Only alphanumeric characters (A-Z, 0-9) detected. No special characters or spaces.</div>
                        </div>
                    </div>
                    <div class="validation-item pass">
                        <div class="validation-icon"><i class="fas fa-check"></i></div>
                        <div class="validation-content">
                            <div class="validation-title">Length Rules</div>
                            <div class="validation-detail">8 characters - within the 3-11 character limit for alphanumeric SenderIDs.</div>
                        </div>
                    </div>
                    <div class="validation-item pass">
                        <div class="validation-icon"><i class="fas fa-check"></i></div>
                        <div class="validation-content">
                            <div class="validation-title">UK Shortcode Rules</div>
                            <div class="validation-detail">Not applicable - this is an alphanumeric SenderID, not a shortcode.</div>
                        </div>
                    </div>
                    <div class="validation-item pass">
                        <div class="validation-icon"><i class="fas fa-check"></i></div>
                        <div class="validation-content">
                            <div class="validation-title">Numeric Normalisation</div>
                            <div class="validation-detail">No leading zeros or numeric-only patterns detected.</div>
                        </div>
                    </div>
                    <div class="validation-item pass">
                        <div class="validation-icon"><i class="fas fa-check"></i></div>
                        <div class="validation-content">
                            <div class="validation-title">Restricted Keyword Detection</div>
                            <div class="validation-detail">No restricted keywords (HMRC, NHS, GOV, Police, etc.) detected.</div>
                        </div>
                    </div>
                    <div class="validation-item warn">
                        <div class="validation-icon"><i class="fas fa-exclamation"></i></div>
                        <div class="validation-content">
                            <div class="validation-title">Brand Mismatch Warning</div>
                            <div class="validation-detail">SenderID contains "BANK" - verify business is authorised to use banking-related terminology. Recommend BrandAssure verification.</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="action-panel">
                <div class="action-panel-title"><i class="fas fa-gavel me-2"></i>Admin Actions</div>
                <div class="action-buttons">
                    <button class="action-btn warning" onclick="returnToCustomer()">
                        <i class="fas fa-reply"></i> Return to Customer
                    </button>
                    <button class="action-btn danger" onclick="showRejectModal()">
                        <i class="fas fa-times-circle"></i> Reject
                    </button>
                    <button class="action-btn success" onclick="approveSenderId()">
                        <i class="fas fa-check-circle"></i> Approve
                    </button>
                    <button class="action-btn primary" onclick="submitToBrandAssure()">
                        <i class="fas fa-shield-alt"></i> Submit to BrandAssure
                    </button>
                    <button class="action-btn outline" onclick="markValidationFailed()">
                        <i class="fas fa-exclamation-triangle"></i> Mark Validation Failed
                    </button>
                    <button class="action-btn enterprise" onclick="forceApprove()">
                        <i class="fas fa-bolt"></i> Force Approve (Enterprise)
                    </button>
                </div>
            </div>

            <div class="detail-card notes-section">
                <div class="detail-card-header">
                    <i class="fas fa-sticky-note"></i> Notes & Communication
                </div>
                <div class="detail-card-body">
                    <div class="notes-tabs">
                        <button class="notes-tab active" onclick="switchNotesTab('internal')">Internal Notes</button>
                        <button class="notes-tab" onclick="switchNotesTab('customer')">Customer Message</button>
                    </div>

                    <div class="notes-content active" id="tab-internal">
                        <div class="note-entry">
                            <div class="note-header">
                                <span class="note-author">Sarah Johnson</span>
                                <span class="note-time">Jan 20, 2026 11:30 AM</span>
                            </div>
                            <div class="note-text">Brand contains "BANK" - flagged for additional verification. Awaiting confirmation from compliance team.</div>
                        </div>
                        <textarea class="notes-textarea" placeholder="Add internal note (admin-only, not visible to customer)..."></textarea>
                        <button class="action-btn primary" style="margin-top: 0.75rem;" onclick="addInternalNote()">
                            <i class="fas fa-plus"></i> Add Note
                        </button>
                    </div>

                    <div class="notes-content" id="tab-customer">
                        <textarea class="notes-textarea" placeholder="Compose message to customer (will be included in status notification email)..."></textarea>
                        <div style="margin-top: 0.75rem; display: flex; gap: 0.75rem;">
                            <button class="action-btn outline" onclick="previewCustomerMessage()">
                                <i class="fas fa-eye"></i> Preview
                            </button>
                            <button class="action-btn primary" onclick="sendCustomerMessage()">
                                <i class="fas fa-paper-plane"></i> Send Message
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="sidebar">
            <div class="detail-card sidebar-card">
                <div class="detail-card-header">
                    <i class="fas fa-history"></i> Audit Trail
                </div>
                <div class="detail-card-body">
                    <div class="audit-trail">
                        <div class="audit-entry">
                            <div class="audit-icon"><i class="fas fa-paper-plane"></i></div>
                            <div class="audit-content">
                                <div class="audit-action">Request Submitted</div>
                                <div class="audit-meta">John Smith | Jan 20, 2026, 10:15 AM</div>
                            </div>
                        </div>
                        <div class="audit-entry">
                            <div class="audit-icon"><i class="fas fa-robot"></i></div>
                            <div class="audit-content">
                                <div class="audit-action">Automated Validation Complete</div>
                                <div class="audit-meta">System | Jan 20, 2026, 10:15 AM</div>
                            </div>
                        </div>
                        <div class="audit-entry">
                            <div class="audit-icon"><i class="fas fa-exclamation-triangle"></i></div>
                            <div class="audit-content">
                                <div class="audit-action">Brand Warning Flagged</div>
                                <div class="audit-meta">System | Jan 20, 2026, 10:15 AM</div>
                            </div>
                        </div>
                        <div class="audit-entry">
                            <div class="audit-icon"><i class="fas fa-eye"></i></div>
                            <div class="audit-content">
                                <div class="audit-action">Viewed by Admin</div>
                                <div class="audit-meta">Sarah Johnson | Jan 20, 2026, 11:28 AM</div>
                            </div>
                        </div>
                        <div class="audit-entry">
                            <div class="audit-icon"><i class="fas fa-comment"></i></div>
                            <div class="audit-content">
                                <div class="audit-action">Internal Note Added</div>
                                <div class="audit-meta">Sarah Johnson | Jan 20, 2026, 11:30 AM</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="detail-card sidebar-card">
                <div class="detail-card-header">
                    <i class="fas fa-info-circle"></i> Request Details
                </div>
                <div class="detail-card-body">
                    <div class="detail-row">
                        <span class="detail-label">Request ID</span>
                        <span class="detail-value mono">SID-001</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">SLA Timer</span>
                        <span class="detail-value" style="color: #22c55e;"><i class="fas fa-hourglass-half me-1"></i>18h remaining</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Risk Level</span>
                        <span class="detail-value"><span class="yes-badge" style="background: #fef3c7; color: #92400e;">Medium</span></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Assigned Admin</span>
                        <span class="detail-value">Unassigned</span>
                    </div>
                </div>
            </div>

            <div class="detail-card sidebar-card">
                <div class="detail-card-header">
                    <i class="fas fa-user-circle"></i> Customer Account
                </div>
                <div class="detail-card-body">
                    <div class="detail-row">
                        <span class="detail-label">Account Status</span>
                        <span class="detail-value"><span class="yes-badge">Active</span></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Account Age</span>
                        <span class="detail-value">2 years, 4 months</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Existing SenderIDs</span>
                        <span class="detail-value">3 approved</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Previous Rejections</span>
                        <span class="detail-value">0</span>
                    </div>
                    <a href="{{ route('admin.accounts.details', ['accountId' => 'ACC-1234']) }}" class="action-btn outline" style="width: 100%; justify-content: center; margin-top: 0.75rem;">
                        <i class="fas fa-external-link-alt"></i> View Full Account
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reject SenderID Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Rejection Reason</label>
                    <select class="form-select" id="rejectReason">
                        <option value="">Select a reason...</option>
                        <option value="brand-unauthorized">Brand/trademark not authorized</option>
                        <option value="restricted-keyword">Contains restricted keyword</option>
                        <option value="verification-failed">Business verification failed</option>
                        <option value="policy-violation">Policy violation</option>
                        <option value="duplicate">Duplicate of existing SenderID</option>
                        <option value="other">Other (specify below)</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Customer-Facing Message</label>
                    <textarea class="form-control" id="rejectMessage" rows="4" placeholder="Explain the reason for rejection (will be sent to customer)..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="confirmReject()">Reject Request</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/admin-control-plane.js') }}"></script>
<script>
var SENDER_ID_VALIDATION = {
    characterRules: function(value) {
        var alphanumericOnly = /^[A-Za-z0-9]+$/.test(value);
        var startsWithLetter = /^[A-Za-z]/.test(value);
        return {
            pass: alphanumericOnly && startsWithLetter,
            message: alphanumericOnly ? 'Only alphanumeric characters detected' : 'Contains invalid characters'
        };
    },
    
    lengthRules: function(value, type) {
        var limits = {
            'alphanumeric': { min: 3, max: 11 },
            'numeric': { min: 10, max: 15 },
            'shortcode': { min: 5, max: 6 }
        };
        var limit = limits[type] || limits.alphanumeric;
        var pass = value.length >= limit.min && value.length <= limit.max;
        return {
            pass: pass,
            message: value.length + ' characters - ' + (pass ? 'within' : 'outside') + ' the ' + limit.min + '-' + limit.max + ' character limit'
        };
    },
    
    ukShortcodeRules: function(value, type) {
        if (type !== 'shortcode') {
            return { pass: true, message: 'Not applicable - not a shortcode' };
        }
        var validShortcode = /^[0-9]{5,6}$/.test(value);
        return {
            pass: validShortcode,
            message: validShortcode ? 'Valid UK shortcode format' : 'Invalid UK shortcode format'
        };
    },
    
    numericNormalisation: function(value) {
        var hasLeadingZero = /^0/.test(value);
        var numericOnly = /^[0-9]+$/.test(value);
        return {
            pass: !hasLeadingZero,
            message: hasLeadingZero ? 'Leading zeros detected - normalisation required' : 'No leading zeros detected'
        };
    },
    
    restrictedKeywords: function(value) {
        var restricted = ['HMRC', 'NHS', 'GOV', 'POLICE', 'DVLA', 'UKGOV', 'GOVT', 'ROYAL'];
        var upper = value.toUpperCase();
        var found = restricted.filter(function(k) { return upper.includes(k); });
        return {
            pass: found.length === 0,
            message: found.length === 0 ? 'No restricted keywords detected' : 'Restricted keywords found: ' + found.join(', ')
        };
    },
    
    brandMismatch: function(value, brandName) {
        var sensitiveTerms = ['BANK', 'FINANCE', 'INSURANCE', 'CREDIT', 'LOAN'];
        var upper = value.toUpperCase();
        var found = sensitiveTerms.filter(function(t) { return upper.includes(t); });
        return {
            warn: found.length > 0,
            message: found.length > 0 
                ? 'Contains "' + found.join(', ') + '" - verify business authorisation'
                : 'No sensitive industry terms detected'
        };
    },
    
    runAllValidations: function(value, type, brandName) {
        return {
            characterRules: this.characterRules(value),
            lengthRules: this.lengthRules(value, type),
            ukShortcodeRules: this.ukShortcodeRules(value, type),
            numericNormalisation: this.numericNormalisation(value),
            restrictedKeywords: this.restrictedKeywords(value),
            brandMismatch: this.brandMismatch(value, brandName)
        };
    }
};

document.addEventListener('DOMContentLoaded', function() {
    console.log('[SenderID Detail] Initialized');
    
    if (typeof AdminControlPlane !== 'undefined') {
        AdminControlPlane.logAdminAction('PAGE_VIEW', 'sender-id-detail', { requestId: 'SID-001' }, 'LOW');
    }
});

function switchNotesTab(tab) {
    document.querySelectorAll('.notes-tab').forEach(function(t) {
        t.classList.remove('active');
    });
    document.querySelectorAll('.notes-content').forEach(function(c) {
        c.classList.remove('active');
    });
    
    event.target.classList.add('active');
    document.getElementById('tab-' + tab).classList.add('active');
}

function returnToCustomer() {
    if (confirm('Return this request to the customer for additional information?')) {
        if (typeof AdminControlPlane !== 'undefined') {
            AdminControlPlane.logAdminAction('STATUS_TRANSITION', 'SID-001', { 
                from: 'submitted', 
                to: 'returned-to-customer' 
            }, 'HIGH');
        }
        alert('Request returned to customer. They will receive a notification email.');
        updateStatus('returned-to-customer', 'Returned to Customer', 'fa-reply');
    }
}

function showRejectModal() {
    new bootstrap.Modal(document.getElementById('rejectModal')).show();
}

function confirmReject() {
    var reason = document.getElementById('rejectReason').value;
    var message = document.getElementById('rejectMessage').value;
    
    if (!reason) {
        alert('Please select a rejection reason');
        return;
    }
    
    if (typeof AdminControlPlane !== 'undefined') {
        AdminControlPlane.logAdminAction('REJECT', 'SID-001', { reason: reason, message: message }, 'HIGH');
    }
    
    bootstrap.Modal.getInstance(document.getElementById('rejectModal')).hide();
    updateStatus('rejected', 'Rejected', 'fa-times-circle');
    alert('SenderID request rejected. Customer will be notified.');
}

function approveSenderId() {
    if (confirm('Approve this SenderID request?')) {
        if (typeof AdminControlPlane !== 'undefined') {
            AdminControlPlane.logAdminAction('APPROVE', 'SID-001', {}, 'HIGH');
        }
        updateStatus('approved', 'Approved', 'fa-check-circle');
        setTimeout(function() {
            updateStatus('live', 'Live', 'fa-broadcast-tower');
        }, 1500);
        alert('SenderID approved and now live.');
    }
}

function submitToBrandAssure() {
    if (confirm('Submit this SenderID to BrandAssure for external validation?')) {
        if (typeof AdminControlPlane !== 'undefined') {
            AdminControlPlane.logAdminAction('SUBMIT_TO_BRANDASSURE', 'SID-001', {}, 'MEDIUM');
        }
        updateStatus('validation-in-progress', 'Validation In Progress', 'fa-spinner fa-spin');
        alert('Submitted to BrandAssure. You will be notified when validation completes.');
    }
}

function markValidationFailed() {
    if (confirm('Mark external validation as failed?')) {
        if (typeof AdminControlPlane !== 'undefined') {
            AdminControlPlane.logAdminAction('VALIDATION_FAILED', 'SID-001', {}, 'HIGH');
        }
        updateStatus('validation-failed', 'Validation Failed', 'fa-exclamation-circle');
    }
}

function forceApprove() {
    if (confirm('ENTERPRISE OVERRIDE: Force approve this SenderID bypassing validation? This action is logged and audited.')) {
        if (typeof AdminControlPlane !== 'undefined') {
            AdminControlPlane.logAdminAction('FORCE_APPROVE', 'SID-001', { override: true, reason: 'enterprise' }, 'CRITICAL');
        }
        updateStatus('approved', 'Approved', 'fa-check-circle');
        setTimeout(function() {
            updateStatus('live', 'Live', 'fa-broadcast-tower');
        }, 1000);
        alert('SenderID force approved (enterprise override).');
    }
}

function updateStatus(status, label, icon) {
    var pill = document.getElementById('currentStatus');
    pill.className = 'status-pill ' + status;
    pill.innerHTML = '<i class="fas ' + icon + '"></i> ' + label;
}

function addInternalNote() {
    var textarea = document.querySelector('#tab-internal .notes-textarea');
    var note = textarea.value.trim();
    if (!note) {
        alert('Please enter a note');
        return;
    }
    
    if (typeof AdminControlPlane !== 'undefined') {
        AdminControlPlane.logAdminAction('ADD_INTERNAL_NOTE', 'SID-001', { note: note.substring(0, 100) }, 'LOW');
    }
    
    textarea.value = '';
    alert('Internal note added.');
}

function previewCustomerMessage() {
    var message = document.querySelector('#tab-customer .notes-textarea').value;
    alert('Preview:\n\n' + (message || '(No message entered)'));
}

function sendCustomerMessage() {
    var message = document.querySelector('#tab-customer .notes-textarea').value.trim();
    if (!message) {
        alert('Please enter a message');
        return;
    }
    
    if (typeof AdminControlPlane !== 'undefined') {
        AdminControlPlane.logAdminAction('SEND_CUSTOMER_MESSAGE', 'SID-001', {}, 'MEDIUM');
    }
    
    document.querySelector('#tab-customer .notes-textarea').value = '';
    alert('Message sent to customer.');
}
</script>
@endpush
