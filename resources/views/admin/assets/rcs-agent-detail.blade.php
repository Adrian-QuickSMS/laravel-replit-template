@extends('layouts.admin')

@section('title', 'RCS Agent Approval Detail')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-approval-workflow.css') }}">
<link rel="stylesheet" href="{{ asset('css/admin-external-validation.css') }}">
<link rel="stylesheet" href="{{ asset('css/admin-notifications.css') }}">
<style>
@keyframes slideInRight {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}

.detail-page { 
    padding: 1.5rem; 
    padding-bottom: 5rem;
    min-height: auto;
    overflow: visible !important;
    height: auto !important;
}

.content-body {
    overflow: visible !important;
    overflow-y: auto !important;
    height: auto !important;
    max-height: none !important;
}

.content-body.default-height {
    min-height: auto !important;
    height: auto !important;
    overflow: visible !important;
    overflow-y: auto !important;
}

html, body {
    overflow-y: auto !important;
    height: auto !important;
}

#main-wrapper {
    overflow: visible !important;
    height: auto !important;
}

.qsms-main, .qsms-content-wrap {
    overflow: visible !important;
    height: auto !important;
}

.header-action-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    font-size: 0.85rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}

.header-action-btn.primary {
    background: var(--admin-primary, #1e3a5f);
    color: #fff;
    border: 1px solid var(--admin-primary, #1e3a5f);
}

.header-action-btn.primary:hover {
    background: var(--admin-secondary, #2d5a87);
    border-color: var(--admin-secondary, #2d5a87);
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1.5rem;
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

.agent-name-display {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--admin-primary);
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

.status-pill.draft { background: #f3f4f6; color: #374151; }
.status-pill.submitted { background: #dbeafe; color: #1e40af; }
.status-pill.in_review { background: #e0e7ff; color: #3730a3; }
.status-pill.pending_info { background: #fef3c7; color: #92400e; }
.status-pill.info_provided { background: #fce7f3; color: #9d174d; }
.status-pill.sent_to_supplier { background: #e0e7ff; color: #4338ca; }
.status-pill.supplier_approved { background: #ccfbf1; color: #0f766e; }
.status-pill.approved { background: #d1fae5; color: #065f46; }
.status-pill.rejected { background: #fee2e2; color: #991b1b; }
.status-pill.suspended { background: #ffedd5; color: #9a3412; }
.status-pill.revoked { background: #f3f4f6; color: #6b7280; }

.context-info {
    display: flex;
    gap: 1.5rem;
    padding: 0.75rem 1rem;
    background: #f0f9ff;
    border: 1px solid #bae6fd;
    border-radius: 6px;
    margin-bottom: 1rem;
    flex-wrap: wrap;
}

.context-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.8rem;
}

.context-label { color: #64748b; }
.context-value { color: var(--admin-primary); font-weight: 600; }

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
    margin-bottom: 1rem;
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
    align-items: flex-start;
}

.detail-row:last-child { border-bottom: none; }

.detail-label {
    font-size: 0.8rem;
    color: #64748b;
    font-weight: 500;
    flex-shrink: 0;
}

.detail-value {
    font-size: 0.875rem;
    color: #1e293b;
    font-weight: 500;
    text-align: right;
    max-width: 60%;
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

.brand-color-preview {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.color-swatch {
    width: 24px;
    height: 24px;
    border-radius: 4px;
    border: 2px solid #e2e8f0;
    display: inline-block;
    vertical-align: middle;
}

.asset-preview-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.asset-preview-box {
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    overflow: hidden;
}

.asset-preview-header {
    background: #f8fafc;
    padding: 0.5rem 0.75rem;
    font-size: 0.75rem;
    font-weight: 600;
    color: #64748b;
    border-bottom: 1px solid #e2e8f0;
}

.asset-preview-content {
    padding: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 120px;
    background: #fafafa;
}

.logo-preview {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-weight: 700;
    font-size: 1.5rem;
    border: 3px solid #fff;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    overflow: hidden;
}

.logo-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.hero-preview {
    width: 100%;
    height: 100px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 8px;
    overflow: hidden;
}

.hero-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
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

.action-btn.primary { background: var(--admin-primary); color: #fff; }
.action-btn.primary:hover { background: var(--admin-accent); }
.action-btn.success { background: #22c55e; color: #fff; }
.action-btn.success:hover { background: #16a34a; }
.action-btn.warning { background: #f59e0b; color: #fff; }
.action-btn.warning:hover { background: #d97706; }
.action-btn.danger { background: #ef4444; color: #fff; }
.action-btn.danger:hover { background: #dc2626; }
.action-btn.outline { background: #fff; border-color: #e2e8f0; color: #475569; }
.action-btn.outline:hover { border-color: var(--admin-primary); color: var(--admin-primary); }

.audit-trail { max-height: 400px; overflow-y: auto; }

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

.url-link {
    color: var(--admin-primary);
    text-decoration: none;
    word-break: break-all;
}

.url-link:hover { text-decoration: underline; }

.sidebar-card { margin-bottom: 1rem; }

.test-numbers-list {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.test-number-pill {
    background: #f1f5f9;
    padding: 0.25rem 0.75rem;
    border-radius: 50px;
    font-size: 0.8rem;
    font-family: 'SF Mono', monospace;
}

.comment-entry {
    border-radius: 6px;
    padding: 0.75rem;
    margin-bottom: 0.75rem;
}

.loading-spinner {
    text-align: center;
    padding: 3rem;
}
</style>
@endpush

@section('content')
<div class="detail-page">
    <a href="{{ route('admin.assets.rcs-agents') }}" class="back-link">
        <i class="fas fa-arrow-left"></i> Back to RCS Agent Approvals
    </a>

    <div class="row page-titles">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.assets.rcs-agents') }}">RCS Agent Approvals</a></li>
            <li class="breadcrumb-item active">RCS Agent Detail</li>
        </ol>
    </div>

    <div id="detailLoading" class="loading-spinner">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="text-muted mt-2">Loading RCS Agent details...</p>
    </div>

    <div id="detailContent" style="display: none;">
        <div class="status-header">
            <span class="agent-name-display" id="agentNameDisplay"></span>
            <span class="status-pill" id="currentStatus"></span>
            <div style="margin-left: auto; display: flex; gap: 1rem; align-items: center;">
                <div style="font-size: 0.8rem; color: #64748b; display: flex; gap: 1rem;" id="headerMeta"></div>
            </div>
        </div>

        <div class="context-info" id="contextInfo"></div>

        <div class="detail-grid">
            <div class="main-content">
                <div class="detail-card">
                    <div class="detail-card-header">
                        <i class="fas fa-robot"></i> Agent Identity
                    </div>
                    <div class="detail-card-body" id="sectionIdentity"></div>
                </div>

                <div class="detail-card">
                    <div class="detail-card-header">
                        <i class="fas fa-headset"></i> Contact & Support
                    </div>
                    <div class="detail-card-body" id="sectionContact"></div>
                </div>

                <div class="detail-card">
                    <div class="detail-card-header">
                        <i class="fas fa-tags"></i> Business Classification
                    </div>
                    <div class="detail-card-body" id="sectionClassification"></div>
                </div>

                <div class="detail-card">
                    <div class="detail-card-header">
                        <i class="fas fa-bullhorn"></i> Campaign & Compliance
                    </div>
                    <div class="detail-card-body" id="sectionCampaign"></div>
                </div>

                <div class="detail-card">
                    <div class="detail-card-header">
                        <i class="fas fa-building"></i> Company Details
                    </div>
                    <div class="detail-card-body" id="sectionCompany"></div>
                </div>

                <div class="detail-card">
                    <div class="detail-card-header">
                        <i class="fas fa-user-check"></i> Approver / Signatory
                    </div>
                    <div class="detail-card-body" id="sectionApprover"></div>
                </div>

                <div class="action-panel">
                    <div class="action-panel-title"><i class="fas fa-gavel me-2"></i>Admin Actions</div>
                    <div class="action-buttons" id="actionButtons">
                        <button class="action-btn primary" id="btnStartReview" style="display:none;" onclick="startReview()"><i class="fas fa-search"></i> Start Review</button>
                        <button class="action-btn success" id="btnApproveSubmit" style="display:none;" onclick="showApproveSubmitModal()"><i class="fas fa-paper-plane"></i> Approve & Send to RCS Supplier</button>
                        <button class="action-btn danger" id="btnReject" style="display:none;" onclick="showRejectModal()"><i class="fas fa-times-circle"></i> Reject</button>
                        <button class="action-btn warning" id="btnRequestInfo" style="display:none;" onclick="requestInfo()"><i class="fas fa-question-circle"></i> Return with Comments</button>
                        <button class="action-btn success" id="btnSupplierApproved" style="display:none;" onclick="supplierApprovedAction()"><i class="fas fa-check-double"></i> Mark Supplier Approved</button>
                        <button class="action-btn success" id="btnMarkLive" style="display:none;" onclick="markLiveAction()"><i class="fas fa-broadcast-tower"></i> Mark Live</button>
                        <button class="action-btn warning" id="btnSuspend" style="display:none;" onclick="suspendAgent()"><i class="fas fa-pause-circle"></i> Suspend</button>
                        <button class="action-btn success" id="btnReactivate" style="display:none;" onclick="reactivateAgent()"><i class="fas fa-play-circle"></i> Reactivate</button>
                        <button class="action-btn danger" id="btnRevoke" style="display:none;" onclick="revokeAgent()"><i class="fas fa-ban"></i> Revoke</button>
                        <span class="text-muted small" id="noActionsMsg" style="display:none;">No actions available for this status.</span>
                    </div>
                </div>

                <div class="detail-card" id="commentThreadSection" style="display: none; margin-top: 1rem;">
                    <div class="detail-card-header">
                        <i class="fas fa-comments"></i> Comments <span class="badge bg-secondary ms-2" id="commentCount">0</span>
                    </div>
                    <div class="detail-card-body" id="commentThreadBody"></div>
                </div>
            </div>

            <div class="sidebar">
                <div class="detail-card sidebar-card">
                    <div class="detail-card-header">
                        <i class="fas fa-history"></i> Status History
                    </div>
                    <div class="detail-card-body">
                        <div class="audit-trail" id="auditTrail">
                            <div class="text-center text-muted small py-3">Loading...</div>
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
                            <span class="detail-value mono" id="sidebarRequestId">-</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">SLA Timer</span>
                            <span class="detail-value" id="sidebarSlaTimer">-</span>
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
                            <span class="detail-value" id="sidebarAccountStatus">-</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Account Age</span>
                            <span class="detail-value" id="sidebarAccountAge">-</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Approved Agents</span>
                            <span class="detail-value" id="sidebarApprovedAgents">-</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Rejected Agents</span>
                            <span class="detail-value" id="sidebarRejectedAgents">-</span>
                        </div>
                        <a href="#" class="action-btn outline" id="sidebarViewAccountLink" style="width: 100%; justify-content: center; margin-top: 0.75rem;">
                            <i class="fas fa-external-link-alt"></i> View Full Account
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="approveSubmitModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #fff; border-bottom: 1px solid #e9ecef;">
                <h5 class="modal-title" style="color: #212529;"><i class="fas fa-paper-plane me-2 text-success"></i>Approve & Send to RCS Supplier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info" style="font-size: 0.85rem;">
                    <i class="fas fa-info-circle me-1"></i>
                    This will approve the RCS Agent and submit the registration data to the RCS supplier for provisioning. The customer will be notified of the approval.
                </div>
                <div class="mb-3">
                    <label class="form-label">Admin Notes <span class="text-muted">(optional)</span></label>
                    <textarea class="form-control" id="approveSubmitNotes" rows="3" placeholder="Add any notes about this approval..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="btnConfirmApproveSubmit" onclick="confirmApproveSubmit()"><i class="fas fa-paper-plane me-1"></i> Approve & Send to Supplier</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-times-circle text-danger me-2"></i>Reject RCS Agent</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="rejectReasonText" rows="4" placeholder="Provide a reason for rejection (minimum 10 characters)..."></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted" style="font-size: 0.8rem;">Quick Templates</label>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge bg-light text-dark border" style="cursor:pointer;" onclick="document.getElementById('rejectReasonText').value=this.textContent+': '">Brand guidelines violation</span>
                        <span class="badge bg-light text-dark border" style="cursor:pointer;" onclick="document.getElementById('rejectReasonText').value=this.textContent+': '">Asset quality issues</span>
                        <span class="badge bg-light text-dark border" style="cursor:pointer;" onclick="document.getElementById('rejectReasonText').value=this.textContent+': '">Use case mismatch</span>
                        <span class="badge bg-light text-dark border" style="cursor:pointer;" onclick="document.getElementById('rejectReasonText').value=this.textContent+': '">Compliance requirements not met</span>
                        <span class="badge bg-light text-dark border" style="cursor:pointer;" onclick="document.getElementById('rejectReasonText').value=this.textContent+': '">Business verification failed</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="confirmReject()"><i class="fas fa-times me-1"></i> Reject</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="requestInfoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-question-circle text-warning me-2"></i>Request More Information</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Message to Customer <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="requestInfoText" rows="4" placeholder="Explain what information is needed (minimum 5 characters)..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" onclick="confirmRequestInfo()"><i class="fas fa-paper-plane me-1"></i> Send & Return</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="suspendModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-pause-circle text-warning me-2"></i>Suspend RCS Agent</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Suspension Reason <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="suspendReasonText" rows="4" placeholder="Explain why this agent is being suspended (minimum 5 characters)..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" onclick="confirmSuspend()"><i class="fas fa-pause-circle me-1"></i> Suspend</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="revokeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #fff; border-bottom: 1px solid #e9ecef;">
                <h5 class="modal-title" style="color: #212529;"><i class="fas fa-ban me-2 text-danger"></i>Revoke RCS Agent</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger small"><i class="fas fa-exclamation-triangle me-1"></i> This action is permanent and cannot be reversed.</div>
                <div class="mb-3">
                    <label class="form-label">Revocation Reason <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="revokeReasonText" rows="4" placeholder="Explain why this agent is being permanently revoked (minimum 5 characters)..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="confirmRevoke()"><i class="fas fa-ban me-1"></i> Revoke Permanently</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="supplierApprovedModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #fff; border-bottom: 1px solid #e9ecef;">
                <h5 class="modal-title" style="color: #212529;"><i class="fas fa-check-double me-2 text-success"></i>Mark Supplier Approved</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info small"><i class="fas fa-info-circle me-1"></i> This confirms the mobile network has approved the RCS agent. The agent will move to "Supplier Approved" status.</div>
                <div class="mb-3">
                    <label class="form-label">Admin Notes <span class="text-muted">(optional)</span></label>
                    <textarea class="form-control" id="supplierApprovedNotes" rows="3" placeholder="Add any notes about the supplier approval..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="btnConfirmSupplierApproved" onclick="confirmSupplierApproved()"><i class="fas fa-check-double me-1"></i> Confirm Supplier Approved</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="markLiveModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #fff; border-bottom: 1px solid #e9ecef;">
                <h5 class="modal-title" style="color: #212529;"><i class="fas fa-broadcast-tower me-2 text-success"></i>Mark Agent Live</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info small"><i class="fas fa-info-circle me-1"></i> This will set the agent to "Live" status. The agent will be fully active for RCS messaging.</div>
                <div class="mb-3">
                    <label class="form-label">Admin Notes <span class="text-muted">(optional)</span></label>
                    <textarea class="form-control" id="markLiveNotes" rows="3" placeholder="Add any notes about going live..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="btnConfirmMarkLive" onclick="confirmMarkLive()"><i class="fas fa-broadcast-tower me-1"></i> Mark Live</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="confirmActionModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" id="confirmModalHeader" style="background-color: #fff; border-bottom: 1px solid #e9ecef;">
                <h5 class="modal-title" style="color: #212529;"><i id="confirmModalIcon" class="fas fa-question-circle me-2"></i><span id="confirmModalTitle">Confirm</span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="confirmModalMessage"></p>
                <div id="confirmModalWarning" class="alert alert-danger small" style="display:none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" id="confirmModalBtn">Confirm</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/admin-control-plane.js') }}"></script>
<script src="{{ asset('js/admin-notifications.js') }}"></script>
<script>
var agentUuid = @json($agent_id ?? '');
var csrfToken = $('meta[name="csrf-token"]').attr('content');
var currentAgentData = null;

function ajaxHeaders() {
    return { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'Content-Type': 'application/json' };
}

function escapeHtml(str) {
    if (!str) return '';
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
}

document.addEventListener('DOMContentLoaded', function() {
    console.log('[RCS Agent Detail] Initialized with UUID:', agentUuid);
    
    if (typeof AdminControlPlane !== 'undefined') {
        AdminControlPlane.logAdminAction('PAGE_VIEW', 'rcs-agent-detail', { requestId: agentUuid }, 'LOW');
    }

    if (agentUuid) {
        loadAgentDetail();
    }
});

function loadAgentDetail() {
    $.ajax({
        url: '/admin/api/rcs-agents/' + agentUuid,
        method: 'GET',
        headers: ajaxHeaders(),
        success: function(response) {
            if (response.success) {
                currentAgentData = response.data;
                populateDetailPage(response.data, response.status_history, response.comments, response.account);
                document.getElementById('detailLoading').style.display = 'none';
                document.getElementById('detailContent').style.display = '';
            } else {
                document.getElementById('detailLoading').innerHTML = '<div class="text-danger"><i class="fas fa-exclamation-circle me-2"></i>Agent not found.</div>';
            }
        },
        error: function(xhr) {
            console.error('[RCS Agent Detail] Load error:', xhr.responseText);
            document.getElementById('detailLoading').innerHTML = '<div class="text-danger"><i class="fas fa-exclamation-circle me-2"></i>Failed to load agent details.</div>';
        }
    });
}

function populateDetailPage(data, statusHistory, comments, account) {
    document.getElementById('agentNameDisplay').textContent = data.name || '';

    var workflowStatus = data.workflow_status || data.status || '';
    updateStatusPill(workflowStatus);

    var headerMeta = document.getElementById('headerMeta');
    if (headerMeta) {
        var submittedDate = data.submitted_at ? new Date(data.submitted_at).toLocaleString('en-GB', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : 'N/A';
        headerMeta.innerHTML = '<span><i class="fas fa-hashtag me-1"></i>UUID: <strong>' + escapeHtml(data.uuid || '') + '</strong></span>' +
            '<span><i class="fas fa-clock me-1"></i>Submitted: <strong>' + escapeHtml(submittedDate) + '</strong></span>';
    }

    var contextInfo = document.getElementById('contextInfo');
    if (contextInfo) {
        var accountName = (account && account.company_name) ? account.company_name : '';
        var accountNum = (account && account.account_number) ? account.account_number : '';
        var createdByEmail = '';
        if (data.created_by && typeof data.created_by === 'object') {
            createdByEmail = data.created_by.email || '';
        } else if (data.created_by_email) {
            createdByEmail = data.created_by_email;
        }

        contextInfo.innerHTML = '<div class="context-item"><i class="fas fa-building"></i><span class="context-label">Account:</span><span class="context-value">' + escapeHtml(accountName) + ' (' + escapeHtml(accountNum) + ')</span></div>' +
            '<div class="context-item"><i class="fas fa-user"></i><span class="context-label">Created By:</span><span class="context-value">' + escapeHtml(createdByEmail || 'N/A') + '</span></div>' +
            '<div class="context-item"><i class="fas fa-tag"></i><span class="context-label">Category:</span><span class="context-value">' + escapeHtml(data.billing_category || '-') + '</span></div>' +
            '<div class="context-item"><i class="fas fa-crosshairs"></i><span class="context-label">Use Case:</span><span class="context-value">' + escapeHtml(data.use_case || '-') + '</span></div>';
    }

    renderIdentitySection(data);
    renderContactSection(data);
    renderClassificationSection(data);
    renderCampaignSection(data);
    renderCompanySection(data);
    renderApproverSection(data);
    updateActionButtonVisibility(workflowStatus);

    if (statusHistory && statusHistory.length > 0) {
        renderAuditTrail(statusHistory);
    } else {
        document.getElementById('auditTrail').innerHTML = '<div class="text-muted small text-center py-3">No status history available.</div>';
    }

    if (comments && comments.length > 0) {
        renderComments(comments);
    }

    renderSidebar(data, account);
}

function renderIdentitySection(data) {
    var html = '';
    html += '<div class="detail-row"><span class="detail-label">Agent Name</span><span class="detail-value">' + escapeHtml(data.name || '-') + '</span></div>';
    html += '<div class="detail-row"><span class="detail-label">Description</span><span class="detail-value">' + escapeHtml(data.description || '-') + '</span></div>';
    html += '<div class="detail-row"><span class="detail-label">Brand Color</span><span class="detail-value"><span class="brand-color-preview"><span class="color-swatch" style="background: ' + escapeHtml(data.brand_color || '#ccc') + ';"></span>' + escapeHtml(data.brand_color || '-') + '</span></span></div>';

    html += '<div class="asset-preview-grid mt-3">';
    html += '<div class="asset-preview-box"><div class="asset-preview-header">Logo</div><div class="asset-preview-content">';
    if (data.logo_url) {
        html += '<div class="logo-preview"><img src="' + escapeHtml(data.logo_url) + '" alt="Logo"></div>';
    } else {
        html += '<div class="logo-preview"><i class="fas fa-image" style="font-size: 1.5rem; opacity: 0.5;"></i></div>';
    }
    html += '</div></div>';
    html += '<div class="asset-preview-box"><div class="asset-preview-header">Hero Image</div><div class="asset-preview-content">';
    if (data.hero_url) {
        html += '<div class="hero-preview"><img src="' + escapeHtml(data.hero_url) + '" alt="Hero"></div>';
    } else {
        html += '<div class="hero-preview" style="display: flex; align-items: center; justify-content: center;"><i class="fas fa-image" style="font-size: 1.5rem; color: #fff; opacity: 0.5;"></i></div>';
    }
    html += '</div></div>';
    html += '</div>';

    document.getElementById('sectionIdentity').innerHTML = html;
}

function renderContactSection(data) {
    var html = '';
    html += '<div class="detail-row"><span class="detail-label">Support Phone</span><span class="detail-value">' + escapeHtml(data.support_phone || '-') + '</span></div>';
    html += '<div class="detail-row"><span class="detail-label">Show Phone</span><span class="detail-value">' + (data.show_phone ? '<span class="yes-badge"><i class="fas fa-check"></i> Yes</span>' : '<span class="no-badge"><i class="fas fa-times"></i> No</span>') + '</span></div>';
    html += '<div class="detail-row"><span class="detail-label">Support Email</span><span class="detail-value">' + escapeHtml(data.support_email || '-') + '</span></div>';
    html += '<div class="detail-row"><span class="detail-label">Show Email</span><span class="detail-value">' + (data.show_email ? '<span class="yes-badge"><i class="fas fa-check"></i> Yes</span>' : '<span class="no-badge"><i class="fas fa-times"></i> No</span>') + '</span></div>';
    html += '<div class="detail-row"><span class="detail-label">Website</span><span class="detail-value">' + (data.website ? '<a href="' + escapeHtml(data.website) + '" target="_blank" class="url-link">' + escapeHtml(data.website) + '</a>' : '-') + '</span></div>';
    html += '<div class="detail-row"><span class="detail-label">Privacy URL</span><span class="detail-value">' + (data.privacy_url ? '<a href="' + escapeHtml(data.privacy_url) + '" target="_blank" class="url-link">' + escapeHtml(data.privacy_url) + '</a>' : '-') + '</span></div>';
    html += '<div class="detail-row"><span class="detail-label">Terms URL</span><span class="detail-value">' + (data.terms_url ? '<a href="' + escapeHtml(data.terms_url) + '" target="_blank" class="url-link">' + escapeHtml(data.terms_url) + '</a>' : '-') + '</span></div>';
    document.getElementById('sectionContact').innerHTML = html;
}

function renderClassificationSection(data) {
    var html = '';
    html += '<div class="detail-row"><span class="detail-label">Billing Category</span><span class="detail-value">' + escapeHtml(data.billing_category || '-') + '</span></div>';
    html += '<div class="detail-row"><span class="detail-label">Use Case</span><span class="detail-value">' + escapeHtml(data.use_case || '-') + '</span></div>';
    html += '<div class="detail-row"><span class="detail-label">Use Case Overview</span><span class="detail-value">' + escapeHtml(data.use_case_overview || '-') + '</span></div>';
    document.getElementById('sectionClassification').innerHTML = html;
}

function renderCampaignSection(data) {
    var html = '';
    html += '<div class="detail-row"><span class="detail-label">Campaign Frequency</span><span class="detail-value">' + escapeHtml(data.campaign_frequency || '-') + '</span></div>';
    html += '<div class="detail-row"><span class="detail-label">Monthly Volume</span><span class="detail-value">' + escapeHtml(data.monthly_volume || '-') + '</span></div>';
    html += '<div class="detail-row"><span class="detail-label">Opt-In Description</span><span class="detail-value">' + escapeHtml(data.opt_in_description || '-') + '</span></div>';
    html += '<div class="detail-row"><span class="detail-label">Opt-Out Description</span><span class="detail-value">' + escapeHtml(data.opt_out_description || '-') + '</span></div>';
    
    if (data.test_numbers && data.test_numbers.length > 0) {
        html += '<div class="detail-row"><span class="detail-label">Test Numbers</span><span class="detail-value"><div class="test-numbers-list">';
        data.test_numbers.forEach(function(num) {
            html += '<span class="test-number-pill">' + escapeHtml(num) + '</span>';
        });
        html += '</div></span></div>';
    }
    document.getElementById('sectionCampaign').innerHTML = html;
}

function renderCompanySection(data) {
    var html = '';
    html += '<div class="detail-row"><span class="detail-label">Company Number</span><span class="detail-value">' + escapeHtml(data.company_number || '-') + '</span></div>';
    html += '<div class="detail-row"><span class="detail-label">Company Website</span><span class="detail-value">' + (data.company_website ? '<a href="' + escapeHtml(data.company_website) + '" target="_blank" class="url-link">' + escapeHtml(data.company_website) + '</a>' : '-') + '</span></div>';
    
    var addr = data.registered_address;
    if (addr) {
        if (typeof addr === 'string') {
            try { addr = JSON.parse(addr); } catch (e) {}
        }
        if (typeof addr === 'object' && addr !== null) {
            var parts = [addr.line1, addr.line2, addr.city, addr.post_code, addr.country].filter(Boolean);
            html += '<div class="detail-row"><span class="detail-label">Registered Address</span><span class="detail-value">' + escapeHtml(parts.join(', ')) + '</span></div>';
        } else {
            html += '<div class="detail-row"><span class="detail-label">Registered Address</span><span class="detail-value">' + escapeHtml(String(addr)) + '</span></div>';
        }
    }
    document.getElementById('sectionCompany').innerHTML = html;
}

function renderApproverSection(data) {
    var html = '';
    html += '<div class="detail-row"><span class="detail-label">Approver Name</span><span class="detail-value">' + escapeHtml(data.approver_name || '-') + '</span></div>';
    html += '<div class="detail-row"><span class="detail-label">Job Title</span><span class="detail-value">' + escapeHtml(data.approver_job_title || '-') + '</span></div>';
    html += '<div class="detail-row"><span class="detail-label">Email</span><span class="detail-value">' + escapeHtml(data.approver_email || '-') + '</span></div>';
    document.getElementById('sectionApprover').innerHTML = html;
}

function renderAuditTrail(statusHistory) {
    var container = document.getElementById('auditTrail');
    if (!container) return;

    var iconMap = {
        'created': 'fa-plus-circle',
        'submitted': 'fa-paper-plane',
        'review_started': 'fa-eye',
        'approved': 'fa-check-circle',
        'rejected': 'fa-times-circle',
        'info_requested': 'fa-question-circle',
        'info_provided': 'fa-reply',
        'review_resumed': 'fa-eye',
        'suspended': 'fa-pause-circle',
        'reactivated': 'fa-play-circle',
        'revoked': 'fa-ban',
        'status_changed': 'fa-exchange-alt',
        'resubmission_started': 'fa-redo',
        'resubmitted': 'fa-redo',
        'edited': 'fa-pencil-alt'
    };

    var html = '';
    statusHistory.forEach(function(entry) {
        var icon = iconMap[entry.action] || 'fa-circle';
        var actionLabel = (entry.action || '').replace(/_/g, ' ');
        actionLabel = actionLabel.charAt(0).toUpperCase() + actionLabel.slice(1);
        var meta = (entry.user_name || entry.user_email || 'System');
        if (entry.created_at) {
            meta += ' | ' + new Date(entry.created_at).toLocaleString('en-GB', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' });
        }

        html += '<div class="audit-entry">';
        html += '<div class="audit-icon"><i class="fas ' + icon + '"></i></div>';
        html += '<div class="audit-content">';
        html += '<div class="audit-action">' + escapeHtml(actionLabel) + '</div>';
        html += '<div class="audit-meta">' + escapeHtml(meta) + '</div>';
        if (entry.reason) {
            html += '<div class="audit-meta" style="color: #64748b; margin-top: 0.25rem;">' + escapeHtml(entry.reason) + '</div>';
        }
        if (entry.notes) {
            html += '<div class="audit-meta" style="color: #475569; margin-top: 0.25rem; font-style: italic;">' + escapeHtml(entry.notes) + '</div>';
        }
        html += '</div></div>';
    });

    container.innerHTML = html;
}

function renderComments(comments) {
    var section = document.getElementById('commentThreadSection');
    var body = document.getElementById('commentThreadBody');
    var countEl = document.getElementById('commentCount');

    if (!comments || comments.length === 0) {
        if (section) section.style.display = 'none';
        return;
    }

    if (section) section.style.display = '';
    if (countEl) countEl.textContent = comments.length;

    var html = '';
    comments.forEach(function(comment) {
        var isAdmin = comment.created_by_actor_type === 'admin';
        var isCustomer = comment.created_by_actor_type === 'customer';
        var bgColor = isAdmin ? '#f0f4ff' : (isCustomer ? '#f0fdf4' : '#f8fafc');
        var borderColor = isAdmin ? '#dbeafe' : (isCustomer ? '#bbf7d0' : '#e2e8f0');
        var icon = isAdmin ? 'fa-shield-alt' : (isCustomer ? 'fa-user' : 'fa-robot');
        var iconColor = isAdmin ? '#3b82f6' : (isCustomer ? '#22c55e' : '#94a3b8');
        var label = isAdmin ? 'Admin' : (isCustomer ? 'Customer' : 'System');
        var date = comment.created_at ? new Date(comment.created_at).toLocaleString('en-GB', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '';

        html += '<div class="comment-entry" style="background: ' + bgColor + '; border: 1px solid ' + borderColor + ';">';
        html += '<div style="display: flex; align-items: center; margin-bottom: 0.5rem;">';
        html += '<i class="fas ' + icon + ' me-2" style="color: ' + iconColor + ';"></i>';
        html += '<strong style="font-size: 0.8rem;">' + label + '</strong>';
        if (comment.created_by_actor_name) html += '<span class="text-muted small ms-2">(' + escapeHtml(comment.created_by_actor_name) + ')</span>';
        html += '<span class="text-muted small ms-auto">' + date + '</span>';
        html += '</div>';
        html += '<div style="font-size: 0.85rem; line-height: 1.6; white-space: pre-wrap;">' + escapeHtml(comment.comment_text) + '</div>';
        if (comment.comment_type) {
            var badgeBg = comment.comment_type === 'internal' ? '#e0e7ff' : '#dcfce7';
            var badgeColor = comment.comment_type === 'internal' ? '#3730a3' : '#166534';
            html += '<div style="margin-top: 0.5rem;"><span class="badge" style="background: ' + badgeBg + '; color: ' + badgeColor + '; font-size: 0.7rem;">' + escapeHtml(comment.comment_type) + '</span></div>';
        }
        html += '</div>';
    });

    if (body) body.innerHTML = html;
}

function renderSidebar(data, account) {
    var sidebarRequestId = document.getElementById('sidebarRequestId');
    if (sidebarRequestId) {
        sidebarRequestId.textContent = data.uuid ? data.uuid.substring(0, 8) : '-';
    }

    var sidebarSlaTimer = document.getElementById('sidebarSlaTimer');
    if (sidebarSlaTimer && data.submitted_at) {
        var submitted = new Date(data.submitted_at);
        var now = new Date();
        var hoursElapsed = (now - submitted) / (1000 * 60 * 60);
        var slaHours = 24;
        var remaining = Math.max(0, slaHours - hoursElapsed);
        if (remaining <= 0) {
            sidebarSlaTimer.innerHTML = '<span style="color: #dc2626;"><i class="fas fa-exclamation-triangle me-1"></i>SLA Breached</span>';
        } else if (remaining <= 4) {
            sidebarSlaTimer.innerHTML = '<span style="color: #f59e0b;"><i class="fas fa-hourglass-half me-1"></i>' + Math.round(remaining) + 'h remaining</span>';
        } else {
            sidebarSlaTimer.innerHTML = '<span style="color: #22c55e;"><i class="fas fa-hourglass-half me-1"></i>' + Math.round(remaining) + 'h remaining</span>';
        }
    }

    if (account) {
        var sidebarAccountStatus = document.getElementById('sidebarAccountStatus');
        if (sidebarAccountStatus) {
            var isActive = (account.status === 'active' || account.status === 'Active');
            sidebarAccountStatus.innerHTML = isActive
                ? '<span class="yes-badge">Active</span>'
                : '<span class="no-badge">' + escapeHtml(account.status || 'Unknown') + '</span>';
        }

        var sidebarAccountAge = document.getElementById('sidebarAccountAge');
        if (sidebarAccountAge && account.created_at) {
            var created = new Date(account.created_at);
            var nowDate = new Date();
            var diffMs = nowDate - created;
            var totalMonths = Math.floor(diffMs / (1000 * 60 * 60 * 24 * 30.44));
            var years = Math.floor(totalMonths / 12);
            var months = totalMonths % 12;
            var ageStr = '';
            if (years > 0) ageStr += years + (years === 1 ? ' year' : ' years');
            if (months > 0) ageStr += (ageStr ? ', ' : '') + months + (months === 1 ? ' month' : ' months');
            sidebarAccountAge.textContent = ageStr || 'Less than a month';
        }

        var sidebarApproved = document.getElementById('sidebarApprovedAgents');
        if (sidebarApproved) {
            sidebarApproved.textContent = (account.approved_rcs_agents !== undefined ? account.approved_rcs_agents : 0) + ' approved';
        }

        var sidebarRejected = document.getElementById('sidebarRejectedAgents');
        if (sidebarRejected) {
            sidebarRejected.textContent = account.rejected_rcs_agents !== undefined ? account.rejected_rcs_agents : 0;
        }

        var viewAccountLink = document.getElementById('sidebarViewAccountLink');
        if (viewAccountLink && account.id) {
            viewAccountLink.href = '/admin/accounts/' + account.id;
        }
    }
}

function updateStatusPill(status) {
    var statusMap = {
        'draft': { icon: 'fa-pencil-alt', label: 'Draft' },
        'submitted': { icon: 'fa-paper-plane', label: 'Submitted' },
        'in_review': { icon: 'fa-search', label: 'In Review' },
        'pending_info': { icon: 'fa-undo', label: 'Pending Info' },
        'info_provided': { icon: 'fa-reply', label: 'Info Provided' },
        'sent_to_supplier': { icon: 'fa-satellite-dish', label: 'Sent to Mobile Networks' },
        'supplier_approved': { icon: 'fa-check-double', label: 'Supplier Approved' },
        'approved': { icon: 'fa-check-circle', label: 'Live' },
        'rejected': { icon: 'fa-times-circle', label: 'Rejected' },
        'suspended': { icon: 'fa-pause-circle', label: 'Suspended' },
        'revoked': { icon: 'fa-ban', label: 'Revoked' }
    };
    var info = statusMap[status] || { icon: 'fa-question', label: status };
    var pill = document.getElementById('currentStatus');
    pill.className = 'status-pill ' + status;
    pill.innerHTML = '<i class="fas ' + info.icon + '"></i> ' + info.label;
}

function updateActionButtonVisibility(status) {
    var btnIds = ['btnStartReview', 'btnApproveSubmit', 'btnReject', 'btnRequestInfo', 'btnSupplierApproved', 'btnMarkLive', 'btnSuspend', 'btnReactivate', 'btnRevoke'];
    var noActionsMsg = document.getElementById('noActionsMsg');

    btnIds.forEach(function(id) {
        var btn = document.getElementById(id);
        if (btn) btn.style.display = 'none';
    });
    if (noActionsMsg) noActionsMsg.style.display = 'none';

    var shownCount = 0;
    function showBtn(id) {
        var btn = document.getElementById(id);
        if (btn) { btn.style.display = ''; shownCount++; }
    }

    switch (status) {
        case 'submitted':
            showBtn('btnStartReview');
            showBtn('btnApproveSubmit');
            showBtn('btnReject');
            showBtn('btnRequestInfo');
            break;
        case 'in_review':
            showBtn('btnApproveSubmit');
            showBtn('btnReject');
            showBtn('btnRequestInfo');
            break;
        case 'pending_info':
        case 'info_provided':
            showBtn('btnApproveSubmit');
            showBtn('btnReject');
            showBtn('btnRequestInfo');
            break;
        case 'sent_to_supplier':
            showBtn('btnSupplierApproved');
            showBtn('btnSuspend');
            showBtn('btnRevoke');
            break;
        case 'supplier_approved':
            showBtn('btnMarkLive');
            showBtn('btnSuspend');
            showBtn('btnRevoke');
            break;
        case 'approved':
            showBtn('btnSuspend');
            showBtn('btnRevoke');
            break;
        case 'suspended':
            showBtn('btnReactivate');
            showBtn('btnRevoke');
            break;
    }

    if (shownCount === 0 && noActionsMsg) {
        noActionsMsg.style.display = '';
    }
}

function performAction(action, body, successMsg) {
    $.ajax({
        url: '/admin/api/rcs-agents/' + agentUuid + '/' + action,
        method: 'POST',
        headers: ajaxHeaders(),
        data: JSON.stringify(body || {}),
        success: function(response) {
            if (response.success) {
                showToast(successMsg || response.message || 'Action completed.', 'success');
                loadAgentDetail();
            } else {
                showToast(response.error || 'Action failed.', 'error');
            }
        },
        error: function(xhr) {
            var msg = 'Action failed.';
            try { msg = JSON.parse(xhr.responseText).error || msg; } catch(e) {}
            showToast(msg, 'error');
        }
    });
}

function startReview() {
    if (confirm('Start reviewing this RCS Agent registration?')) {
        performAction('review', {}, 'RCS Agent is now in review.');
    }
}

function showApproveSubmitModal() {
    document.getElementById('approveSubmitNotes').value = '';
    new bootstrap.Modal(document.getElementById('approveSubmitModal')).show();
}

function confirmApproveSubmit() {
    var notes = document.getElementById('approveSubmitNotes').value.trim();
    var btn = document.getElementById('btnConfirmApproveSubmit');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Submitting...';

    var modal = bootstrap.Modal.getInstance(document.getElementById('approveSubmitModal'));

    $.ajax({
        url: '/admin/api/rcs-agents/' + agentUuid + '/approve-and-submit',
        method: 'POST',
        headers: ajaxHeaders(),
        data: JSON.stringify({ notes: notes || 'Approved and submitted to RCS supplier' }),
        success: function(response) {
            if (modal) modal.hide();
            if (response.success) {
                showToast('RCS Agent approved and submitted to supplier.', 'success');
                loadAgentDetail();
            } else {
                showToast(response.error || 'Action failed.', 'error');
            }
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane me-1"></i> Approve & Send to Supplier';
        },
        error: function(xhr) {
            if (modal) modal.hide();
            var msg = 'Action failed.';
            try { msg = JSON.parse(xhr.responseText).error || msg; } catch(e) {}
            showToast(msg, 'error');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane me-1"></i> Approve & Send to Supplier';
        }
    });
}

function showRejectModal() {
    document.getElementById('rejectReasonText').value = '';
    new bootstrap.Modal(document.getElementById('rejectModal')).show();
}

function confirmReject() {
    var reason = document.getElementById('rejectReasonText').value.trim();
    if (!reason || reason.length < 10) {
        showToast('Please provide a rejection reason (minimum 10 characters).', 'warning');
        return;
    }

    var modal = bootstrap.Modal.getInstance(document.getElementById('rejectModal'));
    if (modal) modal.hide();

    performAction('reject', { reason: reason }, 'RCS Agent rejected.');
}

function requestInfo() {
    document.getElementById('requestInfoText').value = '';
    new bootstrap.Modal(document.getElementById('requestInfoModal')).show();
}

function confirmRequestInfo() {
    var notes = document.getElementById('requestInfoText').value.trim();
    if (!notes || notes.length < 5) {
        showToast('Please provide a message (minimum 5 characters).', 'warning');
        return;
    }

    var modal = bootstrap.Modal.getInstance(document.getElementById('requestInfoModal'));
    if (modal) modal.hide();

    performAction('request-info', { notes: notes }, 'Returned to customer for more information.');
}

function supplierApprovedAction() {
    document.getElementById('supplierApprovedNotes').value = '';
    new bootstrap.Modal(document.getElementById('supplierApprovedModal')).show();
}

function confirmSupplierApproved() {
    var notes = document.getElementById('supplierApprovedNotes').value.trim() || 'Supplier has approved the RCS agent';
    var modal = bootstrap.Modal.getInstance(document.getElementById('supplierApprovedModal'));
    if (modal) modal.hide();
    performAction('supplier-approved', { notes: notes }, 'Agent marked as Supplier Approved.');
}

function markLiveAction() {
    document.getElementById('markLiveNotes').value = '';
    new bootstrap.Modal(document.getElementById('markLiveModal')).show();
}

function confirmMarkLive() {
    var notes = document.getElementById('markLiveNotes').value.trim() || 'Agent is now live';
    var modal = bootstrap.Modal.getInstance(document.getElementById('markLiveModal'));
    if (modal) modal.hide();
    performAction('mark-live', { notes: notes }, 'Agent is now Live.');
}

function suspendAgent() {
    document.getElementById('suspendReasonText').value = '';
    new bootstrap.Modal(document.getElementById('suspendModal')).show();
}

function confirmSuspend() {
    var reason = document.getElementById('suspendReasonText').value.trim();
    if (!reason || reason.length < 5) {
        showToast('Please provide a suspension reason (minimum 5 characters).', 'warning');
        return;
    }

    var modal = bootstrap.Modal.getInstance(document.getElementById('suspendModal'));
    if (modal) modal.hide();

    performAction('suspend', { reason: reason }, 'RCS Agent suspended.');
}

function reactivateAgent() {
    if (confirm('Reactivate this suspended RCS Agent?')) {
        performAction('reactivate', { notes: 'Reactivated by admin' }, 'RCS Agent reactivated.');
    }
}

function revokeAgent() {
    document.getElementById('revokeReasonText').value = '';
    new bootstrap.Modal(document.getElementById('revokeModal')).show();
}

function confirmRevoke() {
    var reason = document.getElementById('revokeReasonText').value.trim();
    if (!reason || reason.length < 5) {
        showToast('Please provide a revocation reason (minimum 5 characters).', 'warning');
        return;
    }

    var modal = bootstrap.Modal.getInstance(document.getElementById('revokeModal'));
    if (modal) modal.hide();

    performAction('revoke', { reason: reason }, 'RCS Agent revoked permanently.');
}

function showToast(message, type) {
    type = type || 'success';
    var colors = {
        success: { bg: '#059669', icon: 'fa-check-circle' },
        error: { bg: '#dc2626', icon: 'fa-times-circle' },
        warning: { bg: '#d97706', icon: 'fa-exclamation-triangle' },
        info: { bg: '#1e3a5f', icon: 'fa-info-circle' }
    };
    var c = colors[type] || colors.info;
    var toast = document.createElement('div');
    toast.style.cssText = 'position:fixed;top:1rem;right:1rem;z-index:99999;background:' + c.bg + ';color:#fff;padding:0.75rem 1.25rem;border-radius:8px;font-size:0.85rem;font-weight:500;box-shadow:0 8px 24px rgba(0,0,0,0.2);display:flex;align-items:center;gap:0.5rem;animation:slideInRight 0.3s ease;max-width:400px;';
    toast.innerHTML = '<i class="fas ' + c.icon + '"></i> ' + escapeHtml(message);
    document.body.appendChild(toast);
    setTimeout(function() { toast.style.opacity = '0'; toast.style.transition = 'opacity 0.3s'; }, 4000);
    setTimeout(function() { toast.remove(); }, 4500);
}
</script>
@endpush
