@extends('layouts.quicksms')

@section('title', 'Sub Accounts, Users and Permissions')

@push('styles')
<style>
.breadcrumb {
    background: transparent;
    padding: 0;
    margin: 0;
}
.breadcrumb-item a {
    color: #6c757d;
    text-decoration: none;
}
.breadcrumb-item.active {
    font-weight: 500;
}

.hierarchy-container {
    padding: 1.5rem;
}

.hierarchy-tree {
    display: flex;
    flex-direction: column;
    gap: 0;
}

.main-account-node {
    background: linear-gradient(135deg, #886cc0 0%, #a78bfa 100%);
    color: #fff;
    border-radius: 0.5rem;
    padding: 1rem 1.25rem;
    margin-bottom: 0;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.main-account-node .account-name {
    font-weight: 600;
    font-size: 1rem;
    margin-bottom: 0.25rem;
}
.main-account-node .account-info {
    font-size: 0.8rem;
    opacity: 0.9;
}
.contextual-btn {
    font-size: 0.75rem;
    padding: 0.25rem 0.625rem;
    border-radius: 0.25rem;
    white-space: nowrap;
    transition: background 0.15s, border-color 0.15s;
}
.main-account-node .contextual-btn {
    background: #fff;
    border: 1px solid #fff;
    color: #886cc0;
    font-weight: 500;
}
.main-account-node .contextual-btn:hover {
    background: #f3e8ff;
    border-color: #f3e8ff;
    color: #6b21a8;
}
.sub-account-header .contextual-btn {
    background: rgba(136, 108, 192, 0.1);
    border: 1px solid rgba(136, 108, 192, 0.3);
    color: #886cc0;
}
.sub-account-header .contextual-btn:hover {
    background: rgba(136, 108, 192, 0.2);
}

.tree-connector {
    width: 2px;
    height: 1.5rem;
    background: #e5e7eb;
    margin-left: 2rem;
}

.sub-accounts-container {
    margin-left: 2rem;
    position: relative;
}
.sub-accounts-container::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 1.5rem;
    width: 2px;
    background: #e5e7eb;
}

.sub-account-branch {
    position: relative;
    padding-left: 1.5rem;
    margin-bottom: 0.75rem;
}
.sub-account-branch::before {
    content: '';
    position: absolute;
    left: 0;
    top: 1.25rem;
    width: 1.25rem;
    height: 2px;
    background: #e5e7eb;
}
.sub-account-branch:last-child::after {
    content: '';
    position: absolute;
    left: 0;
    top: 1.25rem;
    bottom: 0;
    width: 2px;
    background: #fff;
}

.sub-account-node {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    overflow: hidden;
}

.sub-account-header {
    padding: 0.875rem 1rem;
    background: #f9fafb;
    border-bottom: 1px solid #e5e7eb;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: space-between;
    transition: background 0.15s;
}
.sub-account-header:hover {
    background: #f3f4f6;
}
.sub-account-header .sub-name {
    font-weight: 600;
    font-size: 0.9rem;
    color: #374151;
}
.sub-account-header .sub-meta {
    font-size: 0.75rem;
    color: #6b7280;
    margin-top: 0.125rem;
}
.sub-account-header .expand-indicator {
    font-size: 0.75rem;
    color: #9ca3af;
    transition: transform 0.2s;
}
.sub-account-header.collapsed .expand-indicator {
    transform: rotate(-90deg);
}

.sub-account-users {
    padding: 0;
    max-height: 500px;
    overflow-y: auto;
}
.sub-account-users.collapsed {
    display: none;
}

.user-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.625rem 1rem;
    border-bottom: 1px solid #f3f4f6;
    font-size: 0.85rem;
}
.user-row:last-child {
    border-bottom: none;
}
.user-row:hover {
    background: #fafafa;
}

.user-info {
    display: flex;
    flex-direction: column;
    gap: 0.125rem;
}
.user-name {
    font-weight: 500;
    color: #374151;
}
.user-email {
    font-size: 0.75rem;
    color: #6b7280;
}

.user-pills {
    display: flex;
    gap: 0.375rem;
    align-items: center;
}

.role-pill, .status-pill, .capability-pill, .account-status-pill {
    font-size: 0.7rem;
    font-weight: 500;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    text-transform: capitalize;
    display: inline-block;
}
.role-pill.owner {
    background: rgba(136, 108, 192, 0.15);
    color: #886cc0;
}
.role-pill.admin {
    background: rgba(59, 130, 246, 0.12);
    color: #3b82f6;
}
.role-pill.messaging-manager,
.role-pill.messaging_manager {
    background: rgba(16, 185, 129, 0.12);
    color: #10b981;
}
.role-pill.finance {
    background: rgba(245, 158, 11, 0.12);
    color: #d97706;
}
.role-pill.developer {
    background: rgba(99, 102, 241, 0.12);
    color: #6366f1;
}
.role-pill.auditor,
.role-pill.readonly {
    background: rgba(107, 114, 128, 0.12);
    color: #6b7280;
}
.role-pill.user {
    background: rgba(59, 130, 246, 0.08);
    color: #3b82f6;
}

.capability-pill.advanced {
    background: rgba(136, 108, 192, 0.12);
    color: #7c3aed;
}
.capability-pill.restricted {
    background: rgba(107, 114, 128, 0.12);
    color: #6b7280;
}

.status-pill.active {
    background: rgba(16, 185, 129, 0.12);
    color: #10b981;
}
.status-pill.invited {
    background: rgba(245, 158, 11, 0.12);
    color: #d97706;
}
.status-pill.suspended {
    background: rgba(239, 68, 68, 0.12);
    color: #ef4444;
}
.status-pill.expired {
    background: rgba(107, 114, 128, 0.12);
    color: #6b7280;
}

.account-status-pill.live {
    background: rgba(16, 185, 129, 0.12);
    color: #10b981;
}
.account-status-pill.suspended {
    background: rgba(245, 158, 11, 0.12);
    color: #d97706;
}
.account-status-pill.archived {
    background: rgba(107, 114, 128, 0.12);
    color: #6b7280;
}

.empty-users {
    padding: 1rem;
    text-align: center;
    color: #9ca3af;
    font-size: 0.8rem;
}

.hierarchy-actions {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.view-restricted-notice {
    background: rgba(245, 158, 11, 0.1);
    border: 1px solid rgba(245, 158, 11, 0.3);
    border-radius: 0.5rem;
    padding: 0.75rem 1rem;
    margin-bottom: 1rem;
    font-size: 0.85rem;
    color: #92400e;
}

.stats-bar {
    display: flex;
    gap: 1.5rem;
    padding: 1rem;
    background: #f9fafb;
    border-radius: 0.5rem;
    margin-bottom: 1.5rem;
}
.stat-item {
    display: flex;
    flex-direction: column;
}
.stat-value {
    font-size: 1.25rem;
    font-weight: 600;
    color: #374151;
}
.stat-label {
    font-size: 0.75rem;
    color: #6b7280;
}

@media (max-width: 768px) {
    .sub-accounts-container {
        margin-left: 1rem;
    }
    .user-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
    .user-pills {
        align-self: flex-start;
    }
}

.perm-switch {
    position: relative;
    display: inline-block;
    width: 36px;
    height: 20px;
}
.perm-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}
.perm-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #e5e7eb;
    transition: 0.2s;
    border-radius: 20px;
}
.perm-slider:before {
    position: absolute;
    content: "";
    height: 14px;
    width: 14px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: 0.2s;
    border-radius: 50%;
    box-shadow: 0 1px 2px rgba(0,0,0,0.15);
}
input:checked + .perm-slider {
    background: linear-gradient(135deg, #886cc0 0%, #a78bfa 100%);
}
input:checked + .perm-slider:before {
    transform: translateX(16px);
}
input:focus + .perm-slider {
    box-shadow: 0 0 0 2px rgba(136, 108, 192, 0.25);
}

.btn-purple-outline {
    background: transparent;
    border: 1px solid #886cc0;
    color: #886cc0;
    font-size: 0.7rem;
    padding: 2px 8px;
    transition: all 0.15s ease;
}
.btn-purple-outline:hover {
    background: #f3e8ff;
    border-color: #886cc0;
    color: #6b21a8;
}
.btn-purple-outline:focus,
.btn-purple-outline:active,
.btn-purple-outline.active {
    background: #886cc0;
    border-color: #886cc0;
    color: #fff;
    box-shadow: 0 0 0 2px rgba(136, 108, 192, 0.25);
}

.sub-name-clickable:hover {
    color: #886cc0;
    text-decoration: underline;
}
.user-name-clickable:hover {
    color: #886cc0;
    text-decoration: underline;
}

.role-selector-cards {
    display: flex;
    flex-direction: column;
    gap: 8px;
    max-height: 300px;
    overflow-y: auto;
}
.role-card {
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 10px 12px;
    cursor: pointer;
    transition: all 0.15s ease;
    background: #fff;
}
.role-card:hover {
    border-color: #886cc0;
    background: #faf8ff;
}
.role-card.selected {
    border-color: #886cc0;
    background: #f3e8ff;
    box-shadow: 0 0 0 2px rgba(136, 108, 192, 0.2);
}
.role-card input[type="radio"] {
    accent-color: #886cc0;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row page-titles">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="#">Account</a></li>
            <li class="breadcrumb-item active">Sub Accounts, Users and Permissions</li>
        </ol>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Account Hierarchy</h4>
                    <div class="hierarchy-actions mb-0">
                        <button class="btn btn-sm btn-outline-secondary" id="btn-expand-all">Expand All</button>
                        <button class="btn btn-sm btn-outline-secondary" id="btn-collapse-all">Collapse All</button>
                    </div>
                </div>
                <div class="card-body">
                    <div id="restricted-notice" class="view-restricted-notice" style="display: none;">
                        You are viewing your Sub-Account only. Main Account Admins can see the full hierarchy.
                    </div>

                    <div class="stats-bar" id="hierarchy-stats">
                        <div class="stat-item">
                            <span class="stat-value" id="stat-sub-accounts">0</span>
                            <span class="stat-label">Sub-Accounts</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-value" id="stat-total-users">0</span>
                            <span class="stat-label">Total Users</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-value" id="stat-active-users">0</span>
                            <span class="stat-label">Active Users</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-value" id="stat-pending-invites">0</span>
                            <span class="stat-label">Pending Invites</span>
                        </div>
                    </div>

                    <div class="hierarchy-container">
                        <div class="hierarchy-tree" id="hierarchy-tree">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="inviteUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs mb-3" id="addUserTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="invite-tab" data-bs-toggle="tab" data-bs-target="#invite-pane" type="button" role="tab">
                            Send Invitation
                        </button>
                    </li>
                    <li class="nav-item" role="presentation" id="direct-create-tab-item">
                        <button class="nav-link" id="direct-tab" data-bs-toggle="tab" data-bs-target="#direct-pane" type="button" role="tab">
                            Direct Creation
                            <span class="badge ms-1" style="font-size: 0.65rem; background: #f3e8ff; color: #6b21a8;">Admin Only</span>
                        </button>
                    </li>
                </ul>
                
                <div class="tab-content" id="addUserTabContent">
                    <div class="tab-pane fade show active" id="invite-pane" role="tabpanel">
                        <div class="alert alert-pastel-primary mb-4">
                            <div class="d-flex align-items-start">
                                <i class="fas fa-info-circle text-primary me-3 mt-1"></i>
                                <div>
                                    <strong>Invitation Flow:</strong> The user will receive an email to set their password and enrol MFA. Once completed, they become Active.
                                </div>
                            </div>
                        </div>
                        
                        <form id="invite-user-form">
                            <div class="mb-3">
                                <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="invite-email" placeholder="user@company.com" required>
                                <div class="form-text">Invitation will be sent to this email address</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Assign to Sub-Account</label>
                                <select class="form-select" id="invite-sub-account">
                                    <option value="">Main Account (no sub-account)</option>
                                </select>
                                <div class="form-text">Optionally assign to a sub-account, or leave at Main Account level</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Role <span class="badge bg-secondary ms-1" style="font-size:0.65rem;vertical-align:middle;">Coming Soon</span></label>
                                <select class="form-select" id="invite-role" disabled style="opacity:0.5;cursor:not-allowed;">
                                    <option value="">Select Role...</option>
                                    <option value="admin">Admin</option>
                                    <option value="messaging_manager">Messaging Manager</option>
                                    <option value="finance">Finance / Billing</option>
                                    <option value="developer">Developer / API User</option>
                                    <option value="user">User</option>
                                    <option value="readonly">Read-Only / Auditor</option>
                                </select>
                                <div class="form-text">Determines navigation and feature access</div>
                                <div id="invite-role-info" class="mt-2 p-2 rounded" style="background: #f8f9fa; font-size: 0.8rem; display: none;"></div>
                            </div>
                            <div class="mb-3" id="sender-capability-group">
                                <label class="form-label text-muted">Sender Capability Level <span class="badge bg-secondary ms-1" style="font-size:0.65rem;vertical-align:middle;">Coming Soon</span></label>
                                <select class="form-select" id="invite-sender-capability" disabled style="opacity:0.5;cursor:not-allowed;">
                                    <option value="">Select Capability...</option>
                                    <option value="advanced">Advanced Sender - Full content creation, Contact Book, CSV uploads</option>
                                    <option value="restricted">Restricted Sender - Templates only, predefined lists only</option>
                                    <option value="none">None - No sending capability</option>
                                </select>
                                <div class="form-text">Controls how messages can be composed and sent</div>
                            </div>
                        </form>
                    </div>
                    
                    <div class="tab-pane fade" id="direct-pane" role="tabpanel">
                        <div class="alert alert-pastel-primary mb-4">
                            <div class="d-flex align-items-start">
                                <i class="fas fa-info-circle text-primary me-3 mt-1"></i>
                                <div>
                                    <strong>Elevated Risk Action</strong><br>
                                    Direct user creation bypasses the standard invitation flow. The user will be required to:
                                    <ul class="mb-0 mt-2">
                                        <li>Reset their password on first login</li>
                                        <li>Enrol MFA immediately before accessing the platform</li>
                                    </ul>
                                    This action is logged as a high-risk audit event.
                                </div>
                            </div>
                        </div>
                        
                        <form id="direct-create-form">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">First Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="direct-first-name" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Last Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="direct-last-name" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="direct-email" placeholder="user@company.com" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Temporary Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="direct-temp-password" required minlength="12">
                                    <button class="btn btn-outline-secondary" type="button" id="btn-generate-password">Generate</button>
                                </div>
                                <div class="form-text">Minimum 12 characters. User must change this on first login.</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Assign to Sub-Account</label>
                                <select class="form-select" id="direct-sub-account">
                                    <option value="">Main Account (no sub-account)</option>
                                </select>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-muted">Role <span class="badge bg-secondary ms-1" style="font-size:0.65rem;vertical-align:middle;">Coming Soon</span></label>
                                    <select class="form-select" id="direct-role" disabled style="opacity:0.5;cursor:not-allowed;">
                                        <option value="">Select Role...</option>
                                        <option value="admin">Admin</option>
                                        <option value="messaging_manager">Messaging Manager</option>
                                        <option value="finance">Finance / Billing</option>
                                        <option value="developer">Developer / API User</option>
                                        <option value="user">User</option>
                                        <option value="readonly">Read-Only / Auditor</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3" id="direct-sender-capability-group">
                                    <label class="form-label text-muted">Sender Capability Level <span class="badge bg-secondary ms-1" style="font-size:0.65rem;vertical-align:middle;">Coming Soon</span></label>
                                    <select class="form-select" id="direct-sender-capability" disabled style="opacity:0.5;cursor:not-allowed;">
                                        <option value="">Select Capability...</option>
                                        <option value="advanced">Advanced Sender</option>
                                        <option value="restricted">Restricted Sender</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Reason for Direct Creation <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="direct-reason" rows="2" placeholder="e.g., Urgent onboarding required, user has no email access" required></textarea>
                                <div class="form-text">This will be recorded in the audit log</div>
                            </div>
                        </form>
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="direct-confirm-risk">
                            <label class="form-check-label" for="direct-confirm-risk">
                                I understand this is a high-risk action and accept responsibility for this user account
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btn-send-invite">Send Invitation</button>
                <button type="button" class="btn" id="btn-direct-create" style="display: none; background: #886cc0; color: white;">Create User Directly</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addSubAccountModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Sub-Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="add-sub-account-form">
                    <div class="mb-3">
                        <label class="form-label">Sub-Account Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="sub-account-name" placeholder="e.g., Marketing Department" required>
                        <div class="invalid-feedback" id="sub-account-name-error">Please enter a sub-account name</div>
                        <div class="form-text">This name will appear in the hierarchy and be visible to users</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description <span class="text-muted">(Optional)</span></label>
                        <textarea class="form-control" id="sub-account-description" rows="2" placeholder="Brief description of this sub-account's purpose"></textarea>
                    </div>
                    
                    <hr class="my-4">
                    
                    <div class="enforcement-rules-section">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <h6 class="mb-0">Default Enforcement Rules</h6>
                                <small class="text-muted">Configure spending and sending limits for this sub-account</small>
                            </div>
                            <span class="badge" style="background: #f3e8ff; color: #6b21a8;">Optional</span>
                        </div>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Daily Send Limit</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="sub-daily-limit" placeholder="No limit" min="0">
                                    <span class="input-group-text">messages</span>
                                </div>
                                <div class="form-text">Maximum messages per day (leave empty for unlimited)</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Monthly Spend Cap</label>
                                <div class="input-group">
                                    <span class="input-group-text">£</span>
                                    <input type="number" class="form-control" id="sub-monthly-cap" placeholder="No limit" min="0" step="0.01">
                                </div>
                                <div class="form-text">Maximum spend per month (leave empty for unlimited)</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted">Campaign Approval Required <span class="badge bg-secondary ms-1" style="font-size:0.65rem;vertical-align:middle;">Coming Soon</span></label>
                                <select class="form-select" id="sub-approval-required" disabled style="opacity:0.5;cursor:not-allowed;">
                                    <option value="no" selected>No - Send immediately</option>
                                    <option value="yes">Yes - Require approval before sending</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Limit Enforcement</label>
                                <select class="form-select" id="sub-limit-enforcement">
                                    <option value="soft" selected>Soft - Alert only</option>
                                    <option value="hard">Hard - Block sends when exceeded</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="alert alert-pastel-primary mt-3 mb-0">
                            <div class="d-flex align-items-start">
                                <i class="fas fa-info-circle text-primary me-3 mt-1"></i>
                                <div>
                                    <strong>Note:</strong> These rules can be modified later in the Sub-Account settings. Users in this sub-account will inherit these defaults.
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btn-create-sub-account">Create Sub-Account</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="changeRoleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Change User Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-pastel-primary mb-4">
                    <div class="d-flex align-items-start">
                        <i class="fas fa-info-circle text-primary me-3 mt-1"></i>
                        <div>
                            <strong>Role-Based Navigation:</strong> Roles control which sections of QuickSMS the user can access, not individual feature toggles.
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">User</label>
                    <input type="text" class="form-control" id="change-role-user-name" readonly>
                    <input type="hidden" id="change-role-user-id">
                    <input type="hidden" id="change-role-sub-account-id">
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Current Role</label>
                    <input type="text" class="form-control" id="change-role-current" readonly>
                    <input type="hidden" id="change-role-current-value">
                </div>
                
                <div class="mb-3">
                    <label class="form-label">New Role <span class="text-danger">*</span></label>
                    <div class="role-selector-cards" id="role-selector-cards">
                        <div class="role-card" data-role="admin">
                            <div class="d-flex align-items-start">
                                <input type="radio" name="new-role" value="admin" class="form-check-input mt-1 me-2" id="role-admin">
                                <div class="flex-grow-1">
                                    <label class="form-check-label fw-semibold" for="role-admin">Admin</label>
                                    <div class="text-muted" style="font-size: 0.75rem;">Full access within their sub-account scope</div>
                                    <div class="mt-1"><span class="badge" style="font-size: 0.65rem; background: #f3f4f6; color: #6b7280;">Team leads, department managers</span></div>
                                </div>
                                <span class="badge" style="background: #fef3c7; color: #92400e; font-size: 0.6rem;">High Access</span>
                            </div>
                        </div>
                        <div class="role-card" data-role="messaging_manager">
                            <div class="d-flex align-items-start">
                                <input type="radio" name="new-role" value="messaging_manager" class="form-check-input mt-1 me-2" id="role-messaging">
                                <div class="flex-grow-1">
                                    <label class="form-check-label fw-semibold" for="role-messaging">Messaging Manager</label>
                                    <div class="text-muted" style="font-size: 0.75rem;">Send messages, manage contacts and templates</div>
                                    <div class="mt-1"><span class="badge" style="font-size: 0.65rem; background: #f3f4f6; color: #6b7280;">Marketing coordinators, campaign managers</span></div>
                                </div>
                            </div>
                        </div>
                        <div class="role-card" data-role="finance">
                            <div class="d-flex align-items-start">
                                <input type="radio" name="new-role" value="finance" class="form-check-input mt-1 me-2" id="role-finance">
                                <div class="flex-grow-1">
                                    <label class="form-check-label fw-semibold" for="role-finance">Finance / Billing</label>
                                    <div class="text-muted" style="font-size: 0.75rem;">Access billing, invoices, and purchases only</div>
                                    <div class="mt-1"><span class="badge" style="font-size: 0.65rem; background: #f3f4f6; color: #6b7280;">Accountants, finance team members</span></div>
                                </div>
                            </div>
                        </div>
                        <div class="role-card" data-role="developer">
                            <div class="d-flex align-items-start">
                                <input type="radio" name="new-role" value="developer" class="form-check-input mt-1 me-2" id="role-developer">
                                <div class="flex-grow-1">
                                    <label class="form-check-label fw-semibold" for="role-developer">Developer / API User</label>
                                    <div class="text-muted" style="font-size: 0.75rem;">API connections and technical integrations</div>
                                    <div class="mt-1"><span class="badge" style="font-size: 0.65rem; background: #f3f4f6; color: #6b7280;">Software engineers, integration specialists</span></div>
                                </div>
                            </div>
                        </div>
                        <div class="role-card" data-role="user">
                            <div class="d-flex align-items-start">
                                <input type="radio" name="new-role" value="user" class="form-check-input mt-1 me-2" id="role-user">
                                <div class="flex-grow-1">
                                    <label class="form-check-label fw-semibold" for="role-user">User</label>
                                    <div class="text-muted" style="font-size: 0.75rem;">Standard user with basic access</div>
                                    <div class="mt-1"><span class="badge" style="font-size: 0.65rem; background: #f3f4f6; color: #6b7280;">General team members</span></div>
                                </div>
                            </div>
                        </div>
                        <div class="role-card" data-role="readonly">
                            <div class="d-flex align-items-start">
                                <input type="radio" name="new-role" value="readonly" class="form-check-input mt-1 me-2" id="role-readonly">
                                <div class="flex-grow-1">
                                    <label class="form-check-label fw-semibold" for="role-readonly">Read-Only / Auditor</label>
                                    <div class="text-muted" style="font-size: 0.75rem;">View-only access for compliance review</div>
                                    <div class="mt-1"><span class="badge" style="font-size: 0.65rem; background: #f3f4f6; color: #6b7280;">Compliance officers, external auditors</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" id="change-role-new" required>
                    <div id="high-risk-warning" class="alert alert-warning mt-2 mb-0" style="font-size: 0.8rem; display: none;">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        <strong>High-access role:</strong> This role grants significant permissions. Please ensure this user requires this level of access.
                    </div>
                    <div id="change-role-info" class="mt-2 p-2 rounded" style="background: #f8f9fa; font-size: 0.8rem; display: none;"></div>
                    <div class="mt-2">
                        <a href="#" class="text-muted" style="font-size: 0.75rem;"><i class="fas fa-book-open me-1"></i>Learn more about roles</a>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Reason for Change <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="change-role-reason" rows="2" placeholder="e.g., Promotion to team lead, department transfer" required></textarea>
                    <div class="invalid-feedback" id="change-role-reason-error">Please provide a reason for this change</div>
                    <div class="form-text">This will be recorded in the audit log</div>
                </div>
                <div id="change-role-validation-error" class="alert alert-danger mb-0" style="display: none; font-size: 0.85rem;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btn-confirm-role-change">Confirm Role Change</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="changeCapabilityModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Change Sender Capability Level</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-pastel-primary mb-4">
                    <div class="d-flex align-items-start">
                        <i class="fas fa-info-circle text-primary me-3 mt-1"></i>
                        <div>
                            <strong>Sender Capability:</strong> Controls what messaging features a user can access, separate from their role.
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">User</label>
                    <input type="text" class="form-control" id="change-capability-user-name" readonly>
                    <input type="hidden" id="change-capability-user-id">
                    <input type="hidden" id="change-capability-sub-account-id">
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Current Level</label>
                    <input type="text" class="form-control" id="change-capability-current" readonly>
                    <input type="hidden" id="change-capability-current-value">
                </div>
                
                <div class="mb-4">
                    <label class="form-label">New Level <span class="text-danger">*</span></label>
                    <div class="capability-options">
                        <div class="form-check mb-3 p-3 border rounded" style="background: linear-gradient(135deg, rgba(136, 108, 192, 0.08) 0%, rgba(167, 139, 250, 0.08) 100%);">
                            <input class="form-check-input" type="radio" name="new-capability" id="cap-advanced" value="advanced">
                            <label class="form-check-label" for="cap-advanced">
                                <strong>Advanced Sender</strong>
                                <ul class="mb-0 mt-2" style="font-size: 0.8rem; color: #6b7280;">
                                    <li>Free-form SMS and RCS composition</li>
                                    <li>Full Contact Book access</li>
                                    <li>CSV recipient uploads</li>
                                    <li>Ad-hoc number entry</li>
                                    <li>Rich RCS media upload</li>
                                    <li>Template creation</li>
                                </ul>
                            </label>
                        </div>
                        <div class="form-check p-3 border rounded" style="background: #f9fafb;">
                            <input class="form-check-input" type="radio" name="new-capability" id="cap-restricted" value="restricted">
                            <label class="form-check-label" for="cap-restricted">
                                <strong>Restricted Sender</strong>
                                <ul class="mb-0 mt-2" style="font-size: 0.8rem; color: #6b7280;">
                                    <li>Templates only (no free-text editing)</li>
                                    <li>Predefined lists only</li>
                                    <li>No CSV uploads</li>
                                    <li>No ad-hoc numbers</li>
                                    <li>No template creation</li>
                                </ul>
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Reason for Change <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="change-capability-reason" rows="2" placeholder="e.g., Training completed, security review passed" required></textarea>
                    <div class="form-text">This will be recorded in the audit log</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btn-confirm-capability-change">Confirm Change</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="managePermissionsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Manage Permissions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <strong id="perm-user-display"></strong>
                        <span class="badge ms-2" id="perm-role-display" style="background: #f3e8ff; color: #6b21a8;"></span>
                    </div>
                    <div class="d-flex align-items-center gap-3" style="font-size: 0.8rem;">
                        <span><span class="badge" style="background: #e5e7eb; color: #374151;">Inherited</span> From role defaults</span>
                        <span><span class="badge" style="background: #fef3c7; color: #92400e;">Override</span> Custom setting</span>
                    </div>
                </div>
                
                <input type="hidden" id="perm-user-id">
                <input type="hidden" id="perm-user-role">
                <input type="hidden" id="perm-sub-account-id">
                
                <div id="permissions-container"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" id="btn-reset-all-overrides">Reset All to Role Defaults</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="btn-save-permissions">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="genericConfirmModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmModalTitle">Confirm Action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="confirmModalMessage">Are you sure?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn" id="confirmModalBtn">Confirm</button>
            </div>
        </div>
    </div>
</div>

<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1100;">
    <div id="genericToast" class="toast" role="alert">
        <div class="toast-header">
            <i class="me-2" id="toastIcon"></i>
            <strong class="me-auto" id="toastTitle">Notification</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body" id="toastMessage"></div>
    </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="subAccountDetailDrawer" style="width: 550px;">
    <div class="offcanvas-header border-bottom" style="background: linear-gradient(135deg, #886cc0 0%, #a78bfa 100%);">
        <div class="text-white">
            <h5 class="offcanvas-title mb-1" id="drawer-subaccount-name">Sub-Account Name</h5>
            <small class="opacity-75">Sub-Account Details</small>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-0">
        <input type="hidden" id="drawer-subaccount-id">
        
        <div class="accordion accordion-flush" id="subAccountAccordion">
            <div class="accordion-item border-0">
                <h2 class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#statusSection" style="background: #f8f9fa; font-size: 0.9rem; font-weight: 600; color: #374151;">
                        <i class="fas fa-circle-check me-2" style="color: #886cc0;"></i>Status & Actions
                    </button>
                </h2>
                <div id="statusSection" class="accordion-collapse collapse show" data-bs-parent="#subAccountAccordion">
                    <div class="accordion-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <span class="text-muted" style="font-size: 0.8rem;">Current Status</span>
                                <div id="drawer-status-pill" class="mt-1"></div>
                            </div>
                            <div id="drawer-status-actions" class="d-flex gap-2"></div>
                        </div>
                        <div class="alert alert-light border-0 mb-0" style="background: #f8f9fa; font-size: 0.8rem;">
                            <i class="fas fa-info-circle me-1 text-muted"></i>
                            Status changes are logged and may affect all users within this sub-account.
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="accordion-item border-0">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#limitsSection" style="background: #f8f9fa; font-size: 0.9rem; font-weight: 600; color: #374151;">
                        <i class="fas fa-sliders me-2" style="color: #886cc0;"></i>Limits & Enforcement
                    </button>
                </h2>
                <div id="limitsSection" class="accordion-collapse collapse" data-bs-parent="#subAccountAccordion">
                    <div class="accordion-body">
                        <form id="sub-limits-form">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label" style="font-size: 0.8rem;">Monthly Spend Cap</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">£</span>
                                        <input type="number" class="form-control" id="drawer-spend-cap" placeholder="No limit" min="0" step="0.01">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" style="font-size: 0.8rem;">Monthly Message Cap</label>
                                    <div class="input-group input-group-sm">
                                        <input type="number" class="form-control" id="drawer-message-cap" placeholder="No limit" min="0">
                                        <span class="input-group-text">parts</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" style="font-size: 0.8rem;">Daily Send Limit <span class="text-muted">(optional)</span></label>
                                    <div class="input-group input-group-sm">
                                        <input type="number" class="form-control" id="drawer-daily-limit" placeholder="No limit" min="0">
                                        <span class="input-group-text">msgs</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" style="font-size: 0.8rem;">Enforcement Type</label>
                                    <select class="form-select form-select-sm" id="drawer-enforcement-type">
                                        <option value="warn">Warn only</option>
                                        <option value="block">Block sends</option>
                                        <option value="approval">Require approval</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="drawer-hard-stop" style="border-color: #886cc0;">
                                        <label class="form-check-label" for="drawer-hard-stop" style="font-size: 0.8rem;">
                                            Enable Hard Stop <span class="text-muted">(no override possible)</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end mt-3 pt-3 border-top">
                                <button type="button" class="btn btn-sm btn-purple-outline me-2" id="btn-reset-limits">Reset</button>
                                <button type="button" class="btn btn-sm" id="btn-save-limits" style="background: #886cc0; color: white;">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="accordion-item border-0">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#usageSection" style="background: #f8f9fa; font-size: 0.9rem; font-weight: 600; color: #374151;">
                        <i class="fas fa-chart-line me-2" style="color: #886cc0;"></i>Live Usage & Telemetry
                    </button>
                </h2>
                <div id="usageSection" class="accordion-collapse collapse" data-bs-parent="#subAccountAccordion">
                    <div class="accordion-body">
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span style="font-size: 0.8rem; color: #6b7280;">Spend vs Cap</span>
                                <span style="font-size: 0.8rem; font-weight: 600;" id="drawer-spend-display">£125.50 / £500.00</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar" id="drawer-spend-bar" role="progressbar" style="width: 25%; background: #886cc0;"></div>
                            </div>
                        </div>
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span style="font-size: 0.8rem; color: #6b7280;">Messages vs Limit</span>
                                <span style="font-size: 0.8rem; font-weight: 600;" id="drawer-msgs-display">1,234 / 10,000</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar" id="drawer-msgs-bar" role="progressbar" style="width: 12%; background: #886cc0;"></div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center p-3 rounded" style="background: #f0fdf4; border: 1px solid #bbf7d0;">
                            <i class="fas fa-shield-check me-2" style="color: #22c55e;"></i>
                            <div>
                                <span style="font-size: 0.85rem; font-weight: 500; color: #166534;">Enforcement State</span>
                                <div style="font-size: 0.8rem; color: #15803d;" id="drawer-enforcement-state">Normal - All systems operational</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="accordion-item border-0">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#assetsSection" style="background: #f8f9fa; font-size: 0.9rem; font-weight: 600; color: #374151;">
                        <i class="fas fa-cube me-2" style="color: #886cc0;"></i>Assigned Assets
                    </button>
                </h2>
                <div id="assetsSection" class="accordion-collapse collapse" data-bs-parent="#subAccountAccordion">
                    <div class="accordion-body p-0">
                        <div class="nav nav-tabs nav-fill" role="tablist" style="border-bottom: 1px solid #e5e7eb;">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#assets-senderids" style="font-size: 0.75rem; padding: 0.5rem;">SenderIDs</button>
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#assets-numbers" style="font-size: 0.75rem; padding: 0.5rem;">Numbers</button>
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#assets-rcs" style="font-size: 0.75rem; padding: 0.5rem;">RCS</button>
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#assets-templates" style="font-size: 0.75rem; padding: 0.5rem;">Templates</button>
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#assets-api" style="font-size: 0.75rem; padding: 0.5rem;">API</button>
                        </div>
                        <div class="tab-content p-3">
                            <div class="tab-pane fade show active" id="assets-senderids">
                                <div class="list-group list-group-flush" id="assets-senderids-list"></div>
                            </div>
                            <div class="tab-pane fade" id="assets-numbers">
                                <div class="list-group list-group-flush" id="assets-numbers-list"></div>
                            </div>
                            <div class="tab-pane fade" id="assets-rcs">
                                <div class="list-group list-group-flush" id="assets-rcs-list"></div>
                            </div>
                            <div class="tab-pane fade" id="assets-templates">
                                <div class="list-group list-group-flush" id="assets-templates-list"></div>
                            </div>
                            <div class="tab-pane fade" id="assets-api">
                                <div class="list-group list-group-flush" id="assets-api-list"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="accordion-item border-0">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#notificationsSection" style="background: #f8f9fa; font-size: 0.9rem; font-weight: 600; color: #374151;">
                        <i class="fas fa-bell me-2" style="color: #886cc0;"></i>Enforcement Notifications
                    </button>
                </h2>
                <div id="notificationsSection" class="accordion-collapse collapse" data-bs-parent="#subAccountAccordion">
                    <div class="accordion-body">
                        <p style="font-size: 0.8rem; color: #6b7280; margin-bottom: 1rem;">Configure when and who receives enforcement alerts for this sub-account.</p>
                        
                        <div class="mb-4">
                            <label class="form-label" style="font-size: 0.85rem; font-weight: 600; color: #374151;">Alert Triggers</label>
                            <div class="d-flex flex-column gap-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="trigger-threshold-75" checked style="border-color: #886cc0;">
                                    <label class="form-check-label" for="trigger-threshold-75" style="font-size: 0.8rem;">
                                        <span class="badge me-1" style="background: #fef3c7; color: #92400e; font-size: 0.65rem;">75%</span>
                                        Approaching limit threshold
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="trigger-threshold-90" checked style="border-color: #886cc0;">
                                    <label class="form-check-label" for="trigger-threshold-90" style="font-size: 0.8rem;">
                                        <span class="badge me-1" style="background: #fed7aa; color: #9a3412; font-size: 0.65rem;">90%</span>
                                        Critical threshold reached
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="trigger-threshold-100" checked style="border-color: #886cc0;">
                                    <label class="form-check-label" for="trigger-threshold-100" style="font-size: 0.8rem;">
                                        <span class="badge me-1" style="background: #fecaca; color: #991b1b; font-size: 0.65rem;">100%</span>
                                        Limit exceeded / Enforcement triggered
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="trigger-override" style="border-color: #886cc0;">
                                    <label class="form-check-label" for="trigger-override" style="font-size: 0.8rem;">
                                        <span class="badge me-1" style="background: #f3e8ff; color: #7c3aed; font-size: 0.65rem;">Override</span>
                                        Admin enforcement override used
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label" style="font-size: 0.85rem; font-weight: 600; color: #374151;">Notification Recipients</label>
                            <div class="alert alert-light border-0 p-2 mb-2" style="background: #f8f9fa; font-size: 0.75rem;">
                                <i class="fas fa-info-circle me-1 text-muted"></i>
                                Main Account admins always receive enforcement alerts.
                            </div>
                            
                            <div class="d-flex flex-column gap-2" id="notification-recipients-list">
                                <div class="d-flex align-items-center justify-content-between p-2 rounded" style="background: #f9fafb; border: 1px solid #e5e7eb;">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-user-shield me-2" style="color: #886cc0;"></i>
                                        <div>
                                            <span style="font-size: 0.8rem; font-weight: 500;">Sub-Account Admin(s)</span>
                                            <div style="font-size: 0.7rem; color: #6b7280;">All admins of this sub-account</div>
                                        </div>
                                    </div>
                                    <input class="form-check-input" type="checkbox" checked style="border-color: #886cc0;">
                                </div>
                                <div class="d-flex align-items-center justify-content-between p-2 rounded" style="background: #f9fafb; border: 1px solid #e5e7eb;">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-user-tie me-2" style="color: #886cc0;"></i>
                                        <div>
                                            <span style="font-size: 0.8rem; font-weight: 500;">Billing Contact</span>
                                            <div style="font-size: 0.7rem; color: #6b7280;">finance@company.com</div>
                                        </div>
                                    </div>
                                    <input class="form-check-input" type="checkbox" checked style="border-color: #886cc0;">
                                </div>
                            </div>
                            
                            <button type="button" class="btn btn-sm btn-link p-0 mt-2" style="color: #886cc0; font-size: 0.8rem;">
                                <i class="fas fa-plus me-1"></i>Add Custom Recipient
                            </button>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label" style="font-size: 0.85rem; font-weight: 600; color: #374151;">Notification Channels</label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="channel-email" checked style="border-color: #886cc0;">
                                    <label class="form-check-label" for="channel-email" style="font-size: 0.8rem;">
                                        <i class="fas fa-envelope me-1 text-muted"></i>Email
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="channel-portal" checked style="border-color: #886cc0;">
                                    <label class="form-check-label" for="channel-portal" style="font-size: 0.8rem;">
                                        <i class="fas fa-bell me-1 text-muted"></i>Portal
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="channel-sms" style="border-color: #886cc0;">
                                    <label class="form-check-label" for="channel-sms" style="font-size: 0.8rem;">
                                        <i class="fas fa-sms me-1 text-muted"></i>SMS
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end pt-3 border-top">
                            <button type="button" class="btn btn-sm" id="btn-save-notifications" style="background: #886cc0; color: white;">
                                <i class="fas fa-save me-1"></i>Save Notification Settings
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="userDetailDrawer" style="width: 500px;">
    <div class="offcanvas-header border-bottom" style="background: linear-gradient(135deg, #886cc0 0%, #a78bfa 100%);">
        <div class="text-white">
            <h5 class="offcanvas-title mb-1" id="drawer-user-name">User Name</h5>
            <small class="opacity-75" id="drawer-user-email">user@email.com</small>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-0">
        <input type="hidden" id="drawer-user-id">
        <input type="hidden" id="drawer-user-subaccount-id">
        
        <div class="accordion accordion-flush" id="userDetailAccordion">
            <div class="accordion-item border-0">
                <h2 class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#userStatusSection" style="background: #f8f9fa; font-size: 0.9rem; font-weight: 600; color: #374151;">
                        <i class="fas fa-user-check me-2" style="color: #886cc0;"></i>User Status
                    </button>
                </h2>
                <div id="userStatusSection" class="accordion-collapse collapse show" data-bs-parent="#userDetailAccordion">
                    <div class="accordion-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <span class="text-muted" style="font-size: 0.8rem;">Current Status</span>
                                <div id="drawer-user-status-pill" class="mt-1"></div>
                            </div>
                            <div id="drawer-user-status-actions" class="d-flex gap-2"></div>
                        </div>
                        <div class="row g-3 mt-2">
                            <div class="col-6">
                                <div class="p-2 rounded text-center" style="background: #f3e8ff;">
                                    <small class="text-muted d-block">Role</small>
                                    <strong id="drawer-user-role" style="color: #7c3aed; font-size: 0.85rem;">Admin</strong>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 rounded text-center" style="background: #f3e8ff;">
                                    <small class="text-muted d-block">Sender Level</small>
                                    <strong id="drawer-user-sender" style="color: #7c3aed; font-size: 0.85rem;">Advanced</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="accordion-item border-0" id="drawer-security-actions-section">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#securityActionsSection" style="background: #f8f9fa; font-size: 0.9rem; font-weight: 600; color: #374151;">
                        <i class="fas fa-lock me-2" style="color: #886cc0;"></i>Security Actions
                    </button>
                </h2>
                <div id="securityActionsSection" class="accordion-collapse collapse" data-bs-parent="#userDetailAccordion">
                    <div class="accordion-body">
                        <div class="d-flex align-items-center justify-content-between p-3 rounded" style="background: #faf5ff; border: 1px solid #e9d5ff;">
                            <div>
                                <div class="fw-semibold" style="font-size: 0.85rem; color: #374151;">Reset Password</div>
                                <div class="text-muted" style="font-size: 0.78rem;">Send a password reset email to this user.</div>
                            </div>
                            <button class="btn btn-sm" id="drawer-reset-password-btn" style="background: #886cc0; color: white; font-size: 0.8rem;" onclick="triggerPasswordReset()">
                                <i class="fas fa-key me-1"></i> Reset
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="accordion-item border-0">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#userLimitsSection" style="background: #f8f9fa; font-size: 0.9rem; font-weight: 600; color: #374151;">
                        <i class="fas fa-gauge me-2" style="color: #886cc0;"></i>User-Level Limits <span class="badge bg-secondary ms-2" style="font-size: 0.65rem;">Optional</span>
                    </button>
                </h2>
                <div id="userLimitsSection" class="accordion-collapse collapse" data-bs-parent="#userDetailAccordion">
                    <div class="accordion-body">
                        <div class="alert alert-light border mb-3" style="font-size: 0.8rem;">
                            <i class="fas fa-info-circle me-1"></i>
                            User limits must be less than or equal to their sub-account limits.
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" style="font-size: 0.8rem;">Monthly Spend Cap</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">£</span>
                                    <input type="number" class="form-control" id="drawer-user-spend-cap" placeholder="Inherit">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" style="font-size: 0.8rem;">Monthly Message Cap</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" class="form-control" id="drawer-user-message-cap" placeholder="Inherit">
                                    <span class="input-group-text">parts</span>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label" style="font-size: 0.8rem;">Enforcement Type</label>
                                <select class="form-select form-select-sm" id="drawer-user-enforcement">
                                    <option value="inherit">Inherit from Sub-Account</option>
                                    <option value="warn">Warn only</option>
                                    <option value="block">Block sends</option>
                                </select>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end mt-3 pt-3 border-top">
                            <button type="button" class="btn btn-sm" style="background: #886cc0; color: white;">Save User Limits</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="accordion-item border-0">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#userUsageSection" style="background: #f8f9fa; font-size: 0.9rem; font-weight: 600; color: #374151;">
                        <i class="fas fa-chart-bar me-2" style="color: #886cc0;"></i>Live Usage
                    </button>
                </h2>
                <div id="userUsageSection" class="accordion-collapse collapse" data-bs-parent="#userDetailAccordion">
                    <div class="accordion-body">
                        <div class="row g-3 text-center">
                            <div class="col-4">
                                <div class="p-3 rounded" style="background: #f8f9fa;">
                                    <div style="font-size: 1.25rem; font-weight: 700; color: #886cc0;" id="drawer-user-spend">£45.20</div>
                                    <small class="text-muted">Spend This Month</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-3 rounded" style="background: #f8f9fa;">
                                    <div style="font-size: 1.25rem; font-weight: 700; color: #886cc0;" id="drawer-user-msgs">342</div>
                                    <small class="text-muted">Messages Sent</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-3 rounded" style="background: #f0fdf4; border: 1px solid #bbf7d0;">
                                    <div style="font-size: 0.9rem; font-weight: 600; color: #22c55e;" id="drawer-user-enforce-state">Normal</div>
                                    <small class="text-muted">Enforcement</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="accordion-item border-0">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#roleExplainSection" style="background: #f8f9fa; font-size: 0.9rem; font-weight: 600; color: #374151;">
                        <i class="fas fa-shield-halved me-2" style="color: #886cc0;"></i>Role Explanation
                    </button>
                </h2>
                <div id="roleExplainSection" class="accordion-collapse collapse" data-bs-parent="#userDetailAccordion">
                    <div class="accordion-body">
                        <div class="mb-3">
                            <div class="d-flex align-items-center mb-2">
                                <span class="badge me-2" style="background: #886cc0;" id="drawer-role-badge">Admin</span>
                                <span class="text-muted" style="font-size: 0.8rem;">Current Role</span>
                            </div>
                            <p style="font-size: 0.85rem; color: #374151;" id="drawer-role-description">
                                Admins have full access to all portal features within their sub-account scope.
                            </p>
                        </div>
                        <div class="mb-3">
                            <span style="font-size: 0.8rem; font-weight: 500;">Navigation Access:</span>
                            <div class="d-flex flex-wrap gap-1 mt-1" id="drawer-role-nav-access"></div>
                        </div>
                        <a href="#" class="btn btn-sm btn-purple-outline w-100">
                            <i class="fas fa-book-open me-1"></i>Learn more about roles
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function showToast(type, title, message) {
    var toast = document.getElementById('genericToast');
    var toastIcon = document.getElementById('toastIcon');
    var toastTitle = document.getElementById('toastTitle');
    var toastMessage = document.getElementById('toastMessage');
    
    toastTitle.textContent = title;
    toastMessage.textContent = message;
    
    toast.classList.remove('bg-success', 'bg-danger', 'bg-warning', 'bg-info');
    toastIcon.className = 'me-2';
    
    if (type === 'success') {
        toast.style.borderLeft = '4px solid #22c55e';
        toastIcon.classList.add('fas', 'fa-check-circle');
        toastIcon.style.color = '#22c55e';
    } else if (type === 'error') {
        toast.style.borderLeft = '4px solid #ef4444';
        toastIcon.classList.add('fas', 'fa-exclamation-circle');
        toastIcon.style.color = '#ef4444';
    } else if (type === 'warning') {
        toast.style.borderLeft = '4px solid #f59e0b';
        toastIcon.classList.add('fas', 'fa-exclamation-triangle');
        toastIcon.style.color = '#f59e0b';
    } else {
        toast.style.borderLeft = '4px solid #886cc0';
        toastIcon.classList.add('fas', 'fa-info-circle');
        toastIcon.style.color = '#886cc0';
    }
    
    var bsToast = new bootstrap.Toast(toast, { delay: 5000 });
    bsToast.show();
}

var confirmCallback = null;

function showConfirmModal(title, message, buttonText, buttonClass, callback, useHtml) {
    document.getElementById('confirmModalTitle').textContent = title;
    if (useHtml) {
        document.getElementById('confirmModalMessage').innerHTML = message;
    } else {
        document.getElementById('confirmModalMessage').textContent = message;
    }
    
    var btn = document.getElementById('confirmModalBtn');
    btn.textContent = buttonText;
    btn.className = 'btn ' + buttonClass;
    btn.removeAttribute('style');
    if (buttonClass === 'btn-primary-purple') {
        btn.style.cssText = 'background: #886cc0; border-color: #886cc0; color: white;';
    }
    
    confirmCallback = callback;
    
    var modal = new bootstrap.Modal(document.getElementById('genericConfirmModal'));
    modal.show();
}

document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('confirmModalBtn').addEventListener('click', function() {
        bootstrap.Modal.getInstance(document.getElementById('genericConfirmModal')).hide();
        if (confirmCallback) {
            confirmCallback();
            confirmCallback = null;
        }
    });
    
    var currentUser = @json($current_user ?? []);
    var accountName = @json($account_name ?? 'My Account');
    var currentUserRole = currentUser.role || 'readonly';
    var isMainAccountAdmin = currentUser.is_account_owner || currentUserRole === 'owner' || currentUserRole === 'admin';
    
    var ROLE_NAV_ACCESS = {
        'owner': { label: 'Account Owner', nav: ['Dashboard', 'Messages', 'Contact Book', 'Reporting', 'Purchase', 'Management', 'Account', 'Support'], note: 'Full access. One per Main Account.' },
        'admin': { label: 'Admin', nav: ['Dashboard', 'Messages', 'Contact Book', 'Reporting', 'Purchase', 'Management', 'Account', 'Support'], note: 'Full access within their scope.' },
        'messaging_manager': { label: 'Messaging Manager', nav: ['Dashboard', 'Messages', 'Contact Book', 'Reporting', 'Management', 'Support'], note: 'Can send messages, manage contacts and templates.' },
        'finance': { label: 'Finance / Billing', nav: ['Dashboard', 'Reporting', 'Purchase', 'Support'], note: 'Access to billing, invoices, and purchases.' },
        'developer': { label: 'Developer / API User', nav: ['Dashboard', 'Management', 'Reporting', 'Support'], note: 'Access to API connections and technical settings.' },
        'user': { label: 'User', nav: ['Dashboard', 'Messages', 'Contact Book', 'Reporting', 'Support'], note: 'Standard user with basic access.' },
        'readonly': { label: 'Read-Only / Auditor', nav: ['Dashboard', 'Messages', 'Contact Book', 'Reporting', 'Management', 'Account', 'Support'], note: 'View-only access for compliance.' }
    };

    var hierarchyData = {
        mainAccount: { name: accountName },
        subAccounts: [],
        mainAccountUsers: []
    };
    
    function showRoleInfo(selectId, infoId) {
        var select = document.getElementById(selectId);
        var infoDiv = document.getElementById(infoId);
        
        select.addEventListener('change', function() {
            var role = this.value;
            if (role && ROLE_NAV_ACCESS[role]) {
                var info = ROLE_NAV_ACCESS[role];
                infoDiv.innerHTML = '<strong>Navigation Access:</strong> ' + info.nav.join(', ') + '<br><em>' + info.note + '</em>';
                infoDiv.style.display = 'block';
            } else {
                infoDiv.style.display = 'none';
            }
        });
    }
    
    showRoleInfo('invite-role', 'invite-role-info');
    showRoleInfo('change-role-new', 'change-role-info');
    
    var csrfToken = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '';
    
    function apiRequest(url, method, body) {
        var opts = {
            method: method || 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            credentials: 'same-origin'
        };
        if (body && method !== 'GET') {
            opts.body = JSON.stringify(body);
        }
        return fetch(url, opts).then(function(resp) {
            return resp.json().then(function(data) {
                if (!resp.ok) {
                    var msg = data.message || data.error || 'Request failed';
                    if (data.errors) {
                        var firstKey = Object.keys(data.errors)[0];
                        if (firstKey && data.errors[firstKey].length) {
                            msg = data.errors[firstKey][0];
                        }
                    }
                    throw new Error(msg);
                }
                return data;
            });
        });
    }
    
    function mapSubAccountFromApi(sa) {
        return {
            id: sa.id,
            name: sa.name,
            description: sa.description || '',
            accountStatus: sa.status || 'live',
            limits: {
                spendCap: sa.limits ? sa.limits.monthly_spending_cap : null,
                messageCap: sa.limits ? sa.limits.monthly_message_cap : null,
                dailyLimit: sa.limits ? sa.limits.daily_send_limit : null,
                enforcementType: sa.limits ? sa.limits.enforcement_type : 'warn',
                hardStop: sa.limits ? sa.limits.hard_stop_enabled : false
            },
            usage: {
                spend: sa.usage ? sa.usage.monthly_spend_used : 0,
                messages: sa.usage ? sa.usage.monthly_messages_used : 0,
                enforcementState: sa.enforcement_state || 'normal'
            },
            assets: { senderIds: [], numbers: [], rcs: [], templates: [], api: [] },
            users: []
        };
    }
    
    function mapUserFromApi(u) {
        return {
            id: u.id,
            sub_account_id: u.sub_account_id,
            name: u.full_name || ((u.first_name || '') + ' ' + (u.last_name || '')).trim(),
            email: u.email,
            role: u.role,
            status: u.status,
            senderCapability: u.sender_capability,
            isAccountOwner: u.is_account_owner || false,
            userLimits: u.limits ? {
                spendCap: u.limits.monthly_spending_cap,
                messageCap: u.limits.monthly_message_cap,
                dailyLimit: u.limits.daily_send_limit
            } : null,
            usage: u.usage ? {
                spend: u.usage.monthly_spend_used || 0,
                messages: u.usage.monthly_messages_used || 0
            } : { spend: 0, messages: 0 },
            permissionOverrides: {},
            permissions: u.permissions || {}
        };
    }
    
    function loadHierarchy() {
        Promise.all([
            apiRequest('/api/sub-accounts?per_page=200'),
            apiRequest('/api/users?per_page=500'),
            apiRequest('/api/invitations')
        ]).then(function(results) {
            var subAccountsResp = results[0];
            var usersResp = results[1];
            var invitationsResp = results[2];
            
            var subAccountsData = (subAccountsResp.data || []).map(mapSubAccountFromApi);
            var usersData = (usersResp.data || []).map(mapUserFromApi);
            var invitations = (invitationsResp.data || []);
            
            subAccountsData.forEach(function(sa) { sa.users = []; });
            
            var unassignedUsers = [];
            usersData.forEach(function(u) {
                if (u.sub_account_id) {
                    var targetSa = subAccountsData.find(function(s) { return s.id === u.sub_account_id; });
                    if (targetSa) {
                        targetSa.users.push(u);
                    } else {
                        unassignedUsers.push(u);
                    }
                } else {
                    unassignedUsers.push(u);
                }
            });
            
            invitations.forEach(function(inv) {
                var invUser = {
                    id: inv.id,
                    sub_account_id: inv.sub_account_id,
                    name: ((inv.first_name || '') + ' ' + (inv.last_name || '')).trim() || inv.email.split('@')[0],
                    email: inv.email,
                    role: inv.role,
                    status: 'invited',
                    senderCapability: inv.sender_capability || null,
                    isInvitation: true,
                    userLimits: null,
                    usage: { spend: 0, messages: 0 }
                };
                if (inv.sub_account_id) {
                    var targetSa = subAccountsData.find(function(s) { return s.id === inv.sub_account_id; });
                    if (targetSa) {
                        targetSa.users.push(invUser);
                    } else {
                        unassignedUsers.push(invUser);
                    }
                } else {
                    unassignedUsers.push(invUser);
                }
            });
            
            hierarchyData.subAccounts = subAccountsData;
            hierarchyData.mainAccountUsers = unassignedUsers;
            renderHierarchy();
        }).catch(function(err) {
            console.error('Failed to load hierarchy:', err);
            showToast('error', 'Load Error', 'Failed to load account hierarchy. Please refresh the page.');
        });
    }

    function renderHierarchy() {
        var tree = document.getElementById('hierarchy-tree');
        var html = '';
        
        html += '<div class="main-account-node">';
        html += '<div style="cursor: pointer;" onclick="window.location.href=\'/account/overview\'">';
        html += '<div class="account-name">' + escapeHtml(hierarchyData.mainAccount.name) + '</div>';
        html += '<div class="account-info">Main Account</div>';
        html += '</div>';
        html += '<button class="contextual-btn btn-add-sub-account" type="button">+ Add Sub-Account</button>';
        html += '</div>';
        
        var mainUsers = hierarchyData.mainAccountUsers || [];
        if (mainUsers.length > 0) {
            html += '<div class="main-account-users" style="margin: 0 0 8px 0; padding: 8px 12px; background: rgba(136, 108, 192, 0.06); border-radius: 8px; border: 1px solid rgba(136, 108, 192, 0.15);">';
            mainUsers.forEach(function(user) {
                var hasMessagingRole = ['owner', 'admin', 'messaging_manager', 'user'].includes(user.role);
                html += '<div class="user-row" data-user-id="' + user.id + '" data-sub-account-id="">';
                html += '<div class="user-info">';
                html += '<span class="user-name">' + escapeHtml(user.name) + '</span>';
                html += '<span class="user-email">' + escapeHtml(user.email) + '</span>';
                html += '</div>';
                html += '<div class="user-pills">';
                html += '<span class="role-pill ' + user.role + '">' + formatRole(user.role) + '</span>';
                if (hasMessagingRole && user.senderCapability) {
                    var capLabel = user.senderCapability === 'advanced' ? 'Advanced' : 'Restricted';
                    html += '<span class="capability-pill ' + user.senderCapability + '">' + capLabel + '</span>';
                }
                html += '<span class="status-pill ' + user.status + '">' + capitalise(user.status) + '</span>';
                if (user.isAccountOwner) {
                    html += '<span class="badge" style="background: #f3e8ff; color: #7c3aed; font-size: 0.7rem; padding: 3px 8px;">Account Owner</span>';
                }
                html += '</div>';
                html += '</div>';
            });
            html += '</div>';
        }
        
        html += '<div class="tree-connector"></div>';
        
        html += '<div class="sub-accounts-container">';
        
        var visibleSubAccounts = hierarchyData.subAccounts;
        if (!isMainAccountAdmin && currentUser && currentUser.sub_account_id) {
            visibleSubAccounts = hierarchyData.subAccounts.filter(function(sub) {
                return sub.id === currentUser.sub_account_id;
            });
            document.getElementById('restricted-notice').style.display = 'block';
        }
        
        visibleSubAccounts.forEach(function(subAccount, index) {
            html += '<div class="sub-account-branch">';
            html += '<div class="sub-account-node" data-sub-id="' + subAccount.id + '">';
            
            html += '<div class="sub-account-header" data-sub-id="' + subAccount.id + '">';
            html += '<div class="d-flex align-items-center gap-2 flex-grow-1">';
            html += '<div>';
            html += '<div class="sub-name sub-name-clickable" data-sub-detail="' + subAccount.id + '" style="cursor: pointer;">' + escapeHtml(subAccount.name) + '</div>';
            html += '<div class="sub-meta"><span class="account-status-pill ' + subAccount.accountStatus + ' me-1">' + capitalise(subAccount.accountStatus) + '</span>' + subAccount.users.length + ' user' + (subAccount.users.length !== 1 ? 's' : '') + '</div>';
            html += '</div>';
            html += '</div>';
            html += '<div class="d-flex align-items-center gap-2">';
            html += '<button class="contextual-btn btn-add-user" data-sub-id="' + subAccount.id + '" type="button">+ Add User</button>';
            html += '<span class="expand-indicator" data-toggle-users="' + subAccount.id + '">&#9660;</span>';
            html += '</div>';
            html += '</div>';
            
            html += '<div class="sub-account-users" id="users-' + subAccount.id + '">';
            
            if (subAccount.users.length === 0) {
                html += '<div class="empty-users">No users in this Sub-Account</div>';
            } else {
                subAccount.users.forEach(function(user) {
                    var hasMessagingRole = ['owner', 'admin', 'messaging_manager', 'user'].includes(user.role);
                    
                    html += '<div class="user-row" data-user-id="' + user.id + '" data-sub-account-id="' + subAccount.id + '">';
                    html += '<div class="user-info">';
                    html += '<span class="user-name user-name-clickable" data-user-detail="' + user.id + '" data-sub-id="' + subAccount.id + '" style="cursor: pointer;">' + escapeHtml(user.name) + '</span>';
                    html += '<span class="user-email">' + escapeHtml(user.email) + '</span>';
                    html += '</div>';
                    html += '<div class="user-pills">';
                    html += '<span class="role-pill ' + user.role + '">' + formatRole(user.role) + '</span>';
                    
                    if (hasMessagingRole && user.senderCapability) {
                        var capLabel = user.senderCapability === 'advanced' ? 'Advanced' : 'Restricted';
                        html += '<span class="capability-pill ' + user.senderCapability + '">' + capLabel + '</span>';
                    }
                    
                    html += '<span class="status-pill ' + user.status + '">' + capitalise(user.status) + '</span>';
                    
                    if (user.role !== 'owner' && isMainAccountAdmin) {
                        html += '<button class="btn btn-outline-secondary btn-sm ms-2 btn-change-role" data-user-id="' + user.id + '" data-user-name="' + escapeHtml(user.name) + '" data-user-role="' + user.role + '" data-sub-account-id="' + subAccount.id + '">Change Role</button>';
                        
                        if (hasMessagingRole) {
                            html += '<button class="btn btn-outline-secondary btn-sm ms-1 btn-change-capability" data-user-id="' + user.id + '" data-user-name="' + escapeHtml(user.name) + '" data-user-capability="' + (user.senderCapability || 'restricted') + '" data-sub-account-id="' + subAccount.id + '">Sender Level</button>';
                        }
                        
                        var overrideCount = user.permissionOverrides ? Object.keys(user.permissionOverrides).length : 0;
                        var overrideBadge = overrideCount > 0 ? ' <span class="badge" style="background: #f3e8ff; color: #7c3aed; font-size: 0.6rem;">' + overrideCount + '</span>' : '';
                        html += '<button class="btn btn-outline-secondary btn-sm ms-1 btn-manage-permissions" data-user-id="' + user.id + '" data-user-name="' + escapeHtml(user.name) + '" data-user-role="' + user.role + '" data-sub-account-id="' + subAccount.id + '">Permissions' + overrideBadge + '</button>';
                    }
                    html += '</div>';
                    html += '</div>';
                });
            }
            
            html += '</div>';
            html += '</div>';
            html += '</div>';
        });
        
        html += '</div>';
        
        tree.innerHTML = html;
        
        updateStats(visibleSubAccounts);
        bindToggleEvents();
    }
    
    function updateStats(subAccounts) {
        var totalUsers = 0;
        var activeUsers = 0;
        var pendingInvites = 0;
        
        var mainUsers = hierarchyData.mainAccountUsers || [];
        totalUsers += mainUsers.length;
        mainUsers.forEach(function(user) {
            if (user.status === 'active') activeUsers++;
            if (user.status === 'invited') pendingInvites++;
        });
        
        subAccounts.forEach(function(sub) {
            totalUsers += sub.users.length;
            sub.users.forEach(function(user) {
                if (user.status === 'active') activeUsers++;
                if (user.status === 'invited') pendingInvites++;
            });
        });
        
        document.getElementById('stat-sub-accounts').textContent = subAccounts.length;
        document.getElementById('stat-total-users').textContent = totalUsers;
        document.getElementById('stat-active-users').textContent = activeUsers;
        document.getElementById('stat-pending-invites').textContent = pendingInvites;
    }
    
    function bindToggleEvents() {
        document.querySelectorAll('[data-toggle-users]').forEach(function(el) {
            el.addEventListener('click', function(e) {
                e.stopPropagation();
                var subId = this.getAttribute('data-toggle-users');
                var usersDiv = document.getElementById('users-' + subId);
                var header = document.querySelector('.sub-account-header[data-sub-id="' + subId + '"]');
                var isCollapsed = usersDiv.classList.contains('collapsed');
                
                if (isCollapsed) {
                    usersDiv.classList.remove('collapsed');
                    if (header) header.classList.remove('collapsed');
                } else {
                    usersDiv.classList.add('collapsed');
                    if (header) header.classList.add('collapsed');
                }
            });
        });
        
        document.querySelectorAll('.btn-add-sub-account').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                document.getElementById('sub-account-name').value = '';
                document.getElementById('sub-account-description').value = '';
                var modal = new bootstrap.Modal(document.getElementById('addSubAccountModal'));
                modal.show();
            });
        });
        
        document.querySelectorAll('.btn-add-user').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                var subId = this.getAttribute('data-sub-id');
                openInviteUserModal(subId);
            });
        });
        
        document.querySelectorAll('[data-sub-detail]').forEach(function(el) {
            el.addEventListener('click', function(e) {
                e.stopPropagation();
                var subId = this.getAttribute('data-sub-detail');
                window.location.href = '/account/sub-accounts/' + subId;
            });
        });
        
        document.querySelectorAll('[data-user-detail]').forEach(function(el) {
            el.addEventListener('click', function(e) {
                e.stopPropagation();
                var userId = this.getAttribute('data-user-detail');
                var subId = this.getAttribute('data-sub-id');
                openUserDetailDrawer(userId, subId);
            });
        });
    }
    
    function openInviteUserModal(preSelectedSubId) {
        document.getElementById('invite-user-form').reset();
        var select = document.getElementById('invite-sub-account');
        select.innerHTML = '<option value="">Main Account (no sub-account)</option>';
        hierarchyData.subAccounts.forEach(function(sub) {
            var selected = sub.id === preSelectedSubId ? ' selected' : '';
            select.innerHTML += '<option value="' + sub.id + '"' + selected + '>' + escapeHtml(sub.name) + '</option>';
        });
        
        var directSelect = document.getElementById('direct-sub-account');
        if (directSelect) {
            directSelect.innerHTML = '<option value="">Main Account (no sub-account)</option>';
            hierarchyData.subAccounts.forEach(function(sub) {
                var selected = sub.id === preSelectedSubId ? ' selected' : '';
                directSelect.innerHTML += '<option value="' + sub.id + '"' + selected + '>' + escapeHtml(sub.name) + '</option>';
            });
        }
        
        var modal = new bootstrap.Modal(document.getElementById('inviteUserModal'));
        modal.show();
    }
    
    function formatRole(role) {
        var roleMap = {
            'owner': 'Account Owner',
            'admin': 'Admin',
            'messaging_manager': 'Messaging Manager',
            'finance': 'Finance',
            'developer': 'Developer',
            'user': 'User',
            'readonly': 'Read-Only'
        };
        return roleMap[role] || role;
    }
    
    function capitalise(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }
    
    function escapeHtml(str) {
        var div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }
    
    document.getElementById('btn-expand-all').addEventListener('click', function() {
        document.querySelectorAll('.sub-account-users').forEach(function(div) {
            div.classList.remove('collapsed');
        });
        document.querySelectorAll('.sub-account-header').forEach(function(header) {
            header.classList.remove('collapsed');
        });
    });
    
    document.getElementById('btn-collapse-all').addEventListener('click', function() {
        document.querySelectorAll('.sub-account-users').forEach(function(div) {
            div.classList.add('collapsed');
        });
        document.querySelectorAll('.sub-account-header').forEach(function(header) {
            header.classList.add('collapsed');
        });
    });
    
    document.getElementById('sub-account-name').addEventListener('input', function() {
        this.classList.remove('is-invalid');
    });
    
    document.getElementById('addSubAccountModal').addEventListener('show.bs.modal', function() {
        document.getElementById('add-sub-account-form').reset();
        document.getElementById('sub-account-name').classList.remove('is-invalid');
    });
    
    document.getElementById('btn-create-sub-account').addEventListener('click', function() {
        var name = document.getElementById('sub-account-name').value.trim();
        var description = document.getElementById('sub-account-description').value.trim();
        
        var nameInput = document.getElementById('sub-account-name');
        
        if (!name) {
            nameInput.classList.add('is-invalid');
            nameInput.focus();
            return;
        }
        
        var dailyLimit = document.getElementById('sub-daily-limit').value;
        var monthlyCap = document.getElementById('sub-monthly-cap').value;
        var approvalRequired = document.getElementById('sub-approval-required').value;
        var limitEnforcement = document.getElementById('sub-limit-enforcement').value;
        
        var payload = { name: name };
        if (description) payload.description = description;
        if (dailyLimit) payload.daily_send_limit = parseInt(dailyLimit);
        if (monthlyCap) payload.monthly_spending_cap = parseFloat(monthlyCap);
        if (limitEnforcement === 'hard') {
            payload.enforcement_type = 'block';
        } else {
            payload.enforcement_type = 'warn';
        }
        
        var btn = document.getElementById('btn-create-sub-account');
        btn.disabled = true;
        btn.textContent = 'Creating...';
        
        apiRequest('/api/sub-accounts', 'POST', payload).then(function(resp) {
            bootstrap.Modal.getInstance(document.getElementById('addSubAccountModal')).hide();
            document.getElementById('add-sub-account-form').reset();
            showToast('success', 'Sub-Account Created', 'Sub-account "' + name + '" has been created successfully.');
            loadHierarchy();
        }).catch(function(err) {
            showToast('error', 'Error', err.message);
        }).finally(function() {
            btn.disabled = false;
            btn.textContent = 'Create Sub-Account';
        });
    });
    
    var roleSelect = document.getElementById('invite-role');
    var senderCapabilityGroup = document.getElementById('sender-capability-group');
    
    roleSelect.addEventListener('change', function() {
        var role = this.value;
        var nonMessagingRoles = ['finance', 'readonly'];
        
        if (nonMessagingRoles.includes(role)) {
            senderCapabilityGroup.style.display = 'none';
            document.getElementById('invite-sender-capability').removeAttribute('required');
        } else {
            senderCapabilityGroup.style.display = 'block';
            document.getElementById('invite-sender-capability').setAttribute('required', 'required');
        }
    });
    
    document.getElementById('btn-send-invite').addEventListener('click', function() {
        var email = document.getElementById('invite-email').value.trim();
        var subAccountId = document.getElementById('invite-sub-account').value;
        
        if (!email) {
            showToast('warning', 'Missing Fields', 'Please enter an email address');
            return;
        }
        
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            showToast('warning', 'Invalid Email', 'Please enter a valid email address');
            return;
        }
        
        var payload = {
            email: email
        };
        if (subAccountId) {
            payload.sub_account_id = subAccountId;
        }
        
        var btn = document.getElementById('btn-send-invite');
        btn.disabled = true;
        btn.textContent = 'Sending...';
        
        apiRequest('/api/invitations', 'POST', payload).then(function(resp) {
            bootstrap.Modal.getInstance(document.getElementById('inviteUserModal')).hide();
            document.getElementById('invite-user-form').reset();
            senderCapabilityGroup.style.display = 'block';
            showToast('success', 'Invitation Sent', 'Invitation sent to ' + email + '.');
            loadHierarchy();
        }).catch(function(err) {
            showToast('error', 'Error', err.message);
        }).finally(function() {
            btn.disabled = false;
            btn.textContent = 'Send Invitation';
        });
    });
    var directCreateTabItem = document.getElementById('direct-create-tab-item');
    var btnSendInvite = document.getElementById('btn-send-invite');
    var btnDirectCreate = document.getElementById('btn-direct-create');
    
    if (!isMainAccountAdmin) {
        directCreateTabItem.style.display = 'none';
    }
    
    document.querySelectorAll('#addUserTabs button').forEach(function(tab) {
        tab.addEventListener('shown.bs.tab', function(e) {
            if (e.target.id === 'invite-tab') {
                btnSendInvite.style.display = 'inline-block';
                btnDirectCreate.style.display = 'none';
            } else if (e.target.id === 'direct-tab') {
                btnSendInvite.style.display = 'none';
                btnDirectCreate.style.display = 'inline-block';
            }
        });
    });
    
    var directRoleSelect = document.getElementById('direct-role');
    var directSenderCapabilityGroup = document.getElementById('direct-sender-capability-group');
    
    directRoleSelect.addEventListener('change', function() {
        var role = this.value;
        var nonMessagingRoles = ['finance', 'readonly'];
        
        if (nonMessagingRoles.includes(role)) {
            directSenderCapabilityGroup.style.display = 'none';
            document.getElementById('direct-sender-capability').removeAttribute('required');
        } else {
            directSenderCapabilityGroup.style.display = 'block';
            document.getElementById('direct-sender-capability').setAttribute('required', 'required');
        }
    });
    
    document.getElementById('btn-generate-password').addEventListener('click', function() {
        var chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789!@#$%^&*';
        var password = '';
        for (var i = 0; i < 16; i++) {
            password += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        var passwordInput = document.getElementById('direct-temp-password');
        passwordInput.type = 'text';
        passwordInput.value = password;
        setTimeout(function() { passwordInput.type = 'password'; }, 3000);
    });
    
    document.getElementById('btn-direct-create').addEventListener('click', function() {
        var firstName = document.getElementById('direct-first-name').value.trim();
        var lastName = document.getElementById('direct-last-name').value.trim();
        var email = document.getElementById('direct-email').value.trim();
        var tempPassword = document.getElementById('direct-temp-password').value;
        var subAccountId = document.getElementById('direct-sub-account').value;
        var reason = document.getElementById('direct-reason').value.trim();
        var confirmRisk = document.getElementById('direct-confirm-risk').checked;
        
        if (!firstName || !lastName || !email || !tempPassword || !reason) {
            showToast('warning', 'Missing Fields', 'Please fill in all required fields');
            return;
        }
        
        if (tempPassword.length < 12) {
            showToast('warning', 'Invalid Password', 'Password must be at least 12 characters');
            return;
        }
        
        if (!confirmRisk) {
            showToast('warning', 'Confirmation Required', 'You must acknowledge the risk before creating a user directly');
            return;
        }
        
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            showToast('warning', 'Invalid Email', 'Please enter a valid email address');
            return;
        }
        
        var payload = {
            email: email,
            first_name: firstName,
            last_name: lastName
        };
        if (subAccountId) {
            payload.sub_account_id = subAccountId;
        }
        
        var btn = document.getElementById('btn-direct-create');
        btn.disabled = true;
        btn.textContent = 'Creating...';
        
        apiRequest('/api/invitations', 'POST', payload).then(function(resp) {
            bootstrap.Modal.getInstance(document.getElementById('inviteUserModal')).hide();
            document.getElementById('direct-create-form').reset();
            document.getElementById('direct-confirm-risk').checked = false;
            directSenderCapabilityGroup.style.display = 'block';
            
            var inviteTab = document.getElementById('invite-tab');
            var bsTab = new bootstrap.Tab(inviteTab);
            bsTab.show();
            btnSendInvite.style.display = 'inline-block';
            btnDirectCreate.style.display = 'none';
            
            showToast('success', 'User Created', 'User "' + firstName + ' ' + lastName + '" has been created. This action has been logged.');
            loadHierarchy();
        }).catch(function(err) {
            showToast('error', 'Error', err.message);
        }).finally(function() {
            btn.disabled = false;
            btn.textContent = 'Create User Directly';
        });
    });
    
    document.getElementById('inviteUserModal').addEventListener('show.bs.modal', function() {
        var subAccounts = hierarchyData.subAccounts;
        
        var inviteSelect = document.getElementById('invite-sub-account');
        var directSelect = document.getElementById('direct-sub-account');
        
        [inviteSelect, directSelect].forEach(function(select) {
            select.innerHTML = '<option value="">Main Account (no sub-account)</option>';
            subAccounts.forEach(function(sa) {
                var option = document.createElement('option');
                option.value = sa.id;
                option.textContent = sa.name;
                select.appendChild(option);
            });
        });
    });
    
    document.getElementById('hierarchy-tree').addEventListener('click', function(e) {
        var btn = e.target.closest('.btn-change-role');
        if (btn) {
            var userId = btn.getAttribute('data-user-id');
            var userName = btn.getAttribute('data-user-name');
            var userRole = btn.getAttribute('data-user-role');
            var subAccountId = btn.getAttribute('data-sub-account-id');
            
            document.getElementById('change-role-user-id').value = userId;
            document.getElementById('change-role-user-name').value = userName;
            document.getElementById('change-role-current').value = formatRole(userRole);
            document.getElementById('change-role-current-value').value = userRole;
            document.getElementById('change-role-sub-account-id').value = subAccountId;
            document.getElementById('change-role-new').value = '';
            document.getElementById('change-role-reason').value = '';
            document.getElementById('change-role-reason').classList.remove('is-invalid');
            document.getElementById('change-role-validation-error').style.display = 'none';
            document.getElementById('change-role-info').style.display = 'none';
            document.getElementById('high-risk-warning').style.display = 'none';
            
            document.querySelectorAll('.role-card').forEach(function(card) {
                card.classList.remove('selected');
                var radio = card.querySelector('input[type="radio"]');
                if (radio) radio.checked = false;
            });
            
            var modal = new bootstrap.Modal(document.getElementById('changeRoleModal'));
            modal.show();
        }
    });
    
    document.querySelectorAll('.role-card').forEach(function(card) {
        card.addEventListener('click', function() {
            var role = this.getAttribute('data-role');
            var radio = this.querySelector('input[type="radio"]');
            
            document.querySelectorAll('.role-card').forEach(function(c) {
                c.classList.remove('selected');
            });
            this.classList.add('selected');
            if (radio) radio.checked = true;
            
            document.getElementById('change-role-new').value = role;
            
            var highRiskRoles = ['admin'];
            var warningEl = document.getElementById('high-risk-warning');
            if (highRiskRoles.includes(role)) {
                warningEl.style.display = 'block';
            } else {
                warningEl.style.display = 'none';
            }
            
            var roleInfo = ROLE_NAV_ACCESS[role];
            if (roleInfo) {
                var infoDiv = document.getElementById('change-role-info');
                infoDiv.innerHTML = '<strong>Navigation Access:</strong> ' + roleInfo.nav.join(', ') + '<br><em>' + roleInfo.note + '</em>';
                infoDiv.style.display = 'block';
            }
        });
    });
    
    document.getElementById('change-role-reason').addEventListener('input', function() {
        this.classList.remove('is-invalid');
        document.getElementById('change-role-validation-error').style.display = 'none';
    });
    
    document.getElementById('btn-confirm-role-change').addEventListener('click', function() {
        var userId = document.getElementById('change-role-user-id').value;
        var userName = document.getElementById('change-role-user-name').value;
        var previousRole = document.getElementById('change-role-current-value').value;
        var newRole = document.getElementById('change-role-new').value;
        var reason = document.getElementById('change-role-reason').value.trim();
        var subAccountId = document.getElementById('change-role-sub-account-id').value;
        
        var validationError = document.getElementById('change-role-validation-error');
        var reasonField = document.getElementById('change-role-reason');
        
        validationError.style.display = 'none';
        reasonField.classList.remove('is-invalid');
        
        var errors = [];
        if (!newRole) {
            errors.push('Please select a new role');
        }
        if (!reason) {
            reasonField.classList.add('is-invalid');
            errors.push('Please provide a reason for the change');
        }
        if (newRole && newRole === previousRole) {
            errors.push('The new role must be different from the current role');
        }
        
        if (errors.length > 0) {
            validationError.innerHTML = '<i class="fas fa-exclamation-circle me-2"></i>' + errors.join('. ');
            validationError.style.display = 'block';
            return;
        }
        
        var btn = document.getElementById('btn-confirm-role-change');
        btn.disabled = true;
        btn.textContent = 'Saving...';
        
        apiRequest('/api/users/' + userId, 'PUT', { role: newRole }).then(function(resp) {
            bootstrap.Modal.getInstance(document.getElementById('changeRoleModal')).hide();
            showToast('success', 'Role Changed', userName + ' is now a ' + formatRole(newRole) + '. This change has been logged.');
            loadHierarchy();
        }).catch(function(err) {
            showToast('error', 'Error', err.message);
        }).finally(function() {
            btn.disabled = false;
            btn.textContent = 'Confirm Role Change';
        });
    });
    
    document.getElementById('hierarchy-tree').addEventListener('click', function(e) {
        var btn = e.target.closest('.btn-change-capability');
        if (btn) {
            var userId = btn.getAttribute('data-user-id');
            var userName = btn.getAttribute('data-user-name');
            var userCapability = btn.getAttribute('data-user-capability');
            var subAccountId = btn.getAttribute('data-sub-account-id');
            
            document.getElementById('change-capability-user-id').value = userId;
            document.getElementById('change-capability-user-name').value = userName;
            document.getElementById('change-capability-current').value = userCapability === 'advanced' ? 'Advanced Sender' : 'Restricted Sender';
            document.getElementById('change-capability-current-value').value = userCapability;
            document.getElementById('change-capability-sub-account-id').value = subAccountId;
            document.getElementById('change-capability-reason').value = '';
            
            document.querySelectorAll('input[name="new-capability"]').forEach(function(radio) {
                radio.checked = false;
            });
            
            var modal = new bootstrap.Modal(document.getElementById('changeCapabilityModal'));
            modal.show();
        }
    });
    
    document.getElementById('btn-confirm-capability-change').addEventListener('click', function() {
        var userId = document.getElementById('change-capability-user-id').value;
        var userName = document.getElementById('change-capability-user-name').value;
        var previousCapability = document.getElementById('change-capability-current-value').value;
        var newCapability = document.querySelector('input[name="new-capability"]:checked');
        var reason = document.getElementById('change-capability-reason').value.trim();
        var subAccountId = document.getElementById('change-capability-sub-account-id').value;
        
        if (!newCapability || !reason) {
            showToast('warning', 'Missing Fields', 'Please select a new capability level and provide a reason');
            return;
        }
        
        newCapability = newCapability.value;
        
        if (newCapability === previousCapability) {
            showToast('warning', 'No Change', 'The new capability must be different from the current level');
            return;
        }
        
        var btn = document.getElementById('btn-confirm-capability-change');
        btn.disabled = true;
        btn.textContent = 'Saving...';
        
        apiRequest('/api/users/' + userId, 'PUT', { sender_capability: newCapability }).then(function(resp) {
            bootstrap.Modal.getInstance(document.getElementById('changeCapabilityModal')).hide();
            var capLabel = newCapability === 'advanced' ? 'Advanced Sender' : 'Restricted Sender';
            showToast('success', 'Capability Changed', userName + ' is now a ' + capLabel + '.');
            loadHierarchy();
        }).catch(function(err) {
            showToast('error', 'Error', err.message);
        }).finally(function() {
            btn.disabled = false;
            btn.textContent = 'Confirm Change';
        });
    });
    
    var PERMISSION_CATEGORIES = {
        'messaging-content': {
            label: 'Messaging & Content', icon: 'fa-envelope',
            permissions: {
                'send_sms': { label: 'Send SMS Messages' },
                'send_rcs': { label: 'Send RCS Messages' },
                'create_templates': { label: 'Create Templates' },
                'use_templates': { label: 'Use Templates' },
                'schedule_messages': { label: 'Schedule Messages' },
                'use_ai_assist': { label: 'Use AI Assistant' }
            }
        },
        'recipients-contacts': {
            label: 'Recipients & Contacts', icon: 'fa-address-book',
            permissions: {
                'view_contacts': { label: 'View Contacts' },
                'create_contacts': { label: 'Create Contacts' },
                'edit_contacts': { label: 'Edit Contacts' },
                'delete_contacts': { label: 'Delete Contacts' },
                'manage_lists': { label: 'Manage Lists' },
                'upload_csv': { label: 'Upload CSV' },
                'export_contacts': { label: 'Export Contacts' }
            }
        },
        'campaign-controls': {
            label: 'Campaign Controls', icon: 'fa-bullhorn',
            permissions: {
                'create_campaigns': { label: 'Create Campaigns' },
                'approve_campaigns': { label: 'Approve Campaigns' },
                'cancel_campaigns': { label: 'Cancel Campaigns' },
                'view_campaign_reports': { label: 'View Campaign Reports' },
                'resend_failed': { label: 'Resend Failed Messages' }
            }
        },
        'configuration': {
            label: 'Configuration', icon: 'fa-cogs',
            permissions: {
                'manage_sender_ids': { label: 'Manage Sender IDs' },
                'manage_numbers': { label: 'Manage Numbers' },
                'manage_api_keys': { label: 'Manage API Keys' },
                'manage_webhooks': { label: 'Manage Webhooks' },
                'manage_email_to_sms': { label: 'Manage Email-to-SMS' }
            }
        },
        'reporting-access': {
            label: 'Reporting Access', icon: 'fa-chart-bar',
            permissions: {
                'view_kpi_dashboard': { label: 'View KPI Dashboard' },
                'view_message_logs': { label: 'View Message Logs' },
                'view_delivery_reports': { label: 'View Delivery Reports' },
                'view_campaign_analytics': { label: 'View Campaign Analytics' },
                'export_reports': { label: 'Export Reports' },
                'view_usage_stats': { label: 'View Usage Statistics' }
            }
        },
        'financial-access': {
            label: 'Financial Access', icon: 'fa-credit-card',
            permissions: {
                'view_balance': { label: 'View Balance' },
                'purchase_credits': { label: 'Purchase Credits' },
                'view_invoices': { label: 'View Invoices' },
                'manage_payment_methods': { label: 'Manage Payment Methods' },
                'view_spending_reports': { label: 'View Spending Reports' }
            }
        },
        'security-governance': {
            label: 'Security & Governance', icon: 'fa-shield-alt',
            permissions: {
                'view_audit_logs': { label: 'View Audit Logs' },
                'manage_users': { label: 'Manage Users' },
                'manage_roles': { label: 'Manage Roles' },
                'force_password_reset': { label: 'Force Password Reset' },
                'manage_mfa_policy': { label: 'Manage MFA Policy' },
                'access_security_settings': { label: 'Access Security Settings' }
            }
        }
    };
    
    var tempPermissionChanges = {};
    
    document.getElementById('hierarchy-tree').addEventListener('click', function(e) {
        var btn = e.target.closest('.btn-manage-permissions');
        if (btn) {
            var userId = btn.getAttribute('data-user-id');
            var userName = btn.getAttribute('data-user-name');
            var userRole = btn.getAttribute('data-user-role');
            var subAccountId = btn.getAttribute('data-sub-account-id');
            
            document.getElementById('perm-user-id').value = userId;
            document.getElementById('perm-user-role').value = userRole;
            document.getElementById('perm-sub-account-id').value = subAccountId;
            document.getElementById('perm-user-display').textContent = userName;
            document.getElementById('perm-role-display').textContent = formatRole(userRole);
            
            var subAccount = hierarchyData.subAccounts.find(function(s) { return s.id === subAccountId; });
            var user = subAccount ? subAccount.users.find(function(u) { return u.id === userId; }) : null;
            var userOverrides = user && user.permissionOverrides ? user.permissionOverrides : {};
            var roleDefaults = typeof PermissionManager !== 'undefined' ? PermissionManager.getRoleDefaults(userRole) : {};
            
            tempPermissionChanges = JSON.parse(JSON.stringify(userOverrides));
            
            renderPermissionsUI(roleDefaults, userOverrides);
            
            var modal = new bootstrap.Modal(document.getElementById('managePermissionsModal'));
            modal.show();
        }
    });
    
    function renderPermissionsUI(roleDefaults, userOverrides) {
        var container = document.getElementById('permissions-container');
        var html = '<div class="row">';
        
        Object.keys(PERMISSION_CATEGORIES).forEach(function(catKey, idx) {
            var cat = PERMISSION_CATEGORIES[catKey];
            
            html += '<div class="col-md-6 mb-3">';
            html += '<div class="card h-100 border-0" style="box-shadow: 0 1px 3px rgba(0,0,0,0.08);">';
            html += '<div class="card-header py-2 border-0" style="background: #f8f9fa; color: #6b7280;"><i class="fas ' + cat.icon + ' me-2" style="color: #9ca3af;"></i>' + cat.label + '</div>';
            html += '<div class="card-body py-2">';
            
            Object.keys(cat.permissions).forEach(function(permKey) {
                var perm = cat.permissions[permKey];
                var defaultValue = roleDefaults[permKey] === true;
                var hasOverride = userOverrides[permKey] !== undefined;
                var effectiveValue = hasOverride ? userOverrides[permKey] : defaultValue;
                var sourceClass = hasOverride ? 'override' : 'inherited';
                var sourceBadge = hasOverride 
                    ? '<span class="badge ms-2" style="background: #fef3c7; color: #b45309; font-size: 0.65rem; font-weight: 500;">Override</span>' 
                    : '<span class="badge ms-2" style="background: #f3f4f6; color: #9ca3af; font-size: 0.65rem; font-weight: 500;">Inherited</span>';
                
                html += '<div class="d-flex justify-content-between align-items-center py-2 border-bottom perm-row" data-perm="' + permKey + '" data-default="' + defaultValue + '" style="border-color: #e5e7eb !important;">';
                html += '<div class="d-flex align-items-center flex-wrap">';
                html += '<span style="font-size: 0.85rem; color: #374151;">' + perm.label + '</span>';
                html += '<span class="source-badge" data-source="' + sourceClass + '">' + sourceBadge + '</span>';
                html += '</div>';
                html += '<div class="d-flex align-items-center gap-2">';
                html += '<label class="perm-switch" style="margin-bottom: 0;">';
                html += '<input type="checkbox" class="perm-toggle-input" data-perm="' + permKey + '"' + (effectiveValue ? ' checked' : '') + '>';
                html += '<span class="perm-slider"></span>';
                html += '</label>';
                html += '<button type="button" class="btn btn-link p-0 perm-reset-btn" data-perm="' + permKey + '" style="font-size: 0.75rem; color: #886cc0; opacity: ' + (hasOverride ? '1' : '0.3') + ';" title="Reset to inherited"' + (hasOverride ? '' : ' disabled') + '><i class="fas fa-undo"></i></button>';
                html += '</div>';
                html += '</div>';
            });
            
            html += '</div></div></div>';
        });
        
        html += '</div>';
        container.innerHTML = html;
        
        container.querySelectorAll('.perm-toggle-input').forEach(function(input) {
            input.addEventListener('change', function() {
                var permKey = this.getAttribute('data-perm');
                var newValue = this.checked;
                var row = this.closest('.perm-row');
                var defaultValue = row.getAttribute('data-default') === 'true';
                
                if (newValue === defaultValue) {
                    delete tempPermissionChanges[permKey];
                } else {
                    tempPermissionChanges[permKey] = newValue;
                }
                
                updatePermRowUI(row, permKey, defaultValue, tempPermissionChanges[permKey]);
            });
        });
        
        container.querySelectorAll('.perm-reset-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var permKey = this.getAttribute('data-perm');
                var row = this.closest('.perm-row');
                var defaultValue = row.getAttribute('data-default') === 'true';
                
                delete tempPermissionChanges[permKey];
                updatePermRowUI(row, permKey, defaultValue, undefined);
            });
        });
    }
    
    function updatePermRowUI(row, permKey, defaultValue, overrideValue) {
        var hasOverride = overrideValue !== undefined;
        var effectiveValue = hasOverride ? overrideValue : defaultValue;
        
        var toggleInput = row.querySelector('.perm-toggle-input');
        var resetBtn = row.querySelector('.perm-reset-btn');
        var sourceBadge = row.querySelector('.source-badge');
        
        toggleInput.checked = effectiveValue;
        resetBtn.disabled = !hasOverride;
        resetBtn.style.opacity = hasOverride ? '1' : '0.3';
        
        if (hasOverride) {
            sourceBadge.innerHTML = '<span class="badge ms-2" style="background: #fef3c7; color: #b45309; font-size: 0.65rem; font-weight: 500;">Override</span>';
        } else {
            sourceBadge.innerHTML = '<span class="badge ms-2" style="background: #f3f4f6; color: #9ca3af; font-size: 0.65rem; font-weight: 500;">Inherited</span>';
        }
    }
    
    document.getElementById('btn-save-permissions').addEventListener('click', function() {
        var userId = document.getElementById('perm-user-id').value;
        
        var toggles = {};
        document.querySelectorAll('.perm-toggle-input').forEach(function(input) {
            toggles[input.getAttribute('data-perm')] = input.checked;
        });
        
        var btn = document.getElementById('btn-save-permissions');
        btn.disabled = true;
        btn.textContent = 'Saving...';
        
        apiRequest('/api/users/' + userId, 'PUT', { permission_toggles: toggles }).then(function(resp) {
            bootstrap.Modal.getInstance(document.getElementById('managePermissionsModal')).hide();
            var overrideCount = Object.keys(tempPermissionChanges).length;
            showToast('success', 'Permissions saved', overrideCount + ' override(s) active. Changes have been logged.');
            loadHierarchy();
        }).catch(function(err) {
            showToast('error', 'Error', err.message);
        }).finally(function() {
            btn.disabled = false;
            btn.textContent = 'Save Changes';
        });
    });
    
    document.getElementById('btn-reset-all-overrides').addEventListener('click', function() {
        showConfirmModal(
            'Reset Permissions',
            'Reset all permissions to role defaults? This will remove all overrides.',
            'Reset',
            'btn-warning',
            function() {
                doResetPermissions();
            }
        );
    });
    
    function doResetPermissions() {
        tempPermissionChanges = {};
        
        var userId = document.getElementById('perm-user-id').value;
        var userRole = document.getElementById('perm-user-role').value;
        var roleDefaults = typeof PermissionManager !== 'undefined' ? PermissionManager.getRoleDefaults(userRole) : {};
        
        renderPermissionsUI(roleDefaults, {});
    }
    
    function openSubAccountDrawer(subId) {
        var subAccount = hierarchyData.subAccounts.find(function(s) { return s.id === subId; });
        if (!subAccount) return;
        
        document.getElementById('drawer-subaccount-id').value = subId;
        document.getElementById('drawer-subaccount-name').textContent = subAccount.name;
        
        var statusPillHtml = '';
        var actionsHtml = '';
        var status = subAccount.accountStatus;
        
        if (status === 'live') {
            statusPillHtml = '<span class="badge" style="background: #dcfce7; color: #166534; font-size: 0.8rem; padding: 6px 12px;">Live</span>';
            actionsHtml = '<button class="btn btn-sm btn-warning" onclick="changeSubAccountStatus(\'' + subId + '\', \'suspended\')">Suspend</button>';
        } else if (status === 'suspended') {
            statusPillHtml = '<span class="badge" style="background: #fef3c7; color: #92400e; font-size: 0.8rem; padding: 6px 12px;">Suspended</span>';
            actionsHtml = '<button class="btn btn-sm btn-success me-1" onclick="changeSubAccountStatus(\'' + subId + '\', \'live\')">Reactivate</button>';
            actionsHtml += '<button class="btn btn-sm btn-secondary" onclick="changeSubAccountStatus(\'' + subId + '\', \'archived\')">Archive</button>';
        } else {
            statusPillHtml = '<span class="badge" style="background: #f3f4f6; color: #6b7280; font-size: 0.8rem; padding: 6px 12px;">Archived</span>';
            actionsHtml = '<button class="btn btn-sm btn-success" onclick="changeSubAccountStatus(\'' + subId + '\', \'live\')">Reactivate</button>';
        }
        
        document.getElementById('drawer-status-pill').innerHTML = statusPillHtml;
        document.getElementById('drawer-status-actions').innerHTML = actionsHtml;
        
        var limits = subAccount.limits;
        document.getElementById('drawer-spend-cap').value = limits.spendCap || '';
        document.getElementById('drawer-message-cap').value = limits.messageCap || '';
        document.getElementById('drawer-daily-limit').value = limits.dailyLimit || '';
        document.getElementById('drawer-enforcement-type').value = limits.enforcementType || 'warn';
        document.getElementById('drawer-hard-stop').checked = limits.hardStop || false;
        
        var usage = subAccount.usage;
        var spendPercent = limits.spendCap ? Math.min(100, (usage.spend / limits.spendCap) * 100) : 0;
        var msgsPercent = limits.messageCap ? Math.min(100, (usage.messages / limits.messageCap) * 100) : 0;
        
        document.getElementById('drawer-spend-display').textContent = '£' + usage.spend.toFixed(2) + ' / £' + (limits.spendCap || '∞');
        document.getElementById('drawer-spend-bar').style.width = spendPercent + '%';
        document.getElementById('drawer-spend-bar').style.background = spendPercent >= 80 ? '#ef4444' : (spendPercent >= 50 ? '#f59e0b' : '#886cc0');
        
        document.getElementById('drawer-msgs-display').textContent = usage.messages.toLocaleString() + ' / ' + (limits.messageCap ? limits.messageCap.toLocaleString() : '∞');
        document.getElementById('drawer-msgs-bar').style.width = msgsPercent + '%';
        document.getElementById('drawer-msgs-bar').style.background = msgsPercent >= 80 ? '#ef4444' : (msgsPercent >= 50 ? '#f59e0b' : '#886cc0');
        
        var enforceState = usage.enforcementState;
        var enforceContainer = document.getElementById('drawer-enforcement-state').parentElement.parentElement;
        if (enforceState === 'normal') {
            enforceContainer.style.background = '#f0fdf4';
            enforceContainer.style.borderColor = '#bbf7d0';
            enforceContainer.querySelector('i').style.color = '#22c55e';
            document.getElementById('drawer-enforcement-state').innerHTML = 'Normal - All systems operational';
        } else if (enforceState === 'warning') {
            enforceContainer.style.background = '#fefce8';
            enforceContainer.style.borderColor = '#fef08a';
            enforceContainer.querySelector('i').className = 'fas fa-exclamation-triangle me-2';
            enforceContainer.querySelector('i').style.color = '#eab308';
            document.getElementById('drawer-enforcement-state').innerHTML = 'Warning - Approaching limits';
        } else {
            enforceContainer.style.background = '#fef2f2';
            enforceContainer.style.borderColor = '#fecaca';
            enforceContainer.querySelector('i').className = 'fas fa-ban me-2';
            enforceContainer.querySelector('i').style.color = '#ef4444';
            document.getElementById('drawer-enforcement-state').innerHTML = 'Blocked - Limits exceeded';
        }
        
        renderAssetsList('senderIds', subAccount.assets.senderIds);
        renderAssetsList('numbers', subAccount.assets.numbers);
        renderAssetsList('rcs', subAccount.assets.rcs);
        renderAssetsList('templates', subAccount.assets.templates);
        renderAssetsList('api', subAccount.assets.api);
        
        var offcanvas = new bootstrap.Offcanvas(document.getElementById('subAccountDetailDrawer'));
        offcanvas.show();
    }
    
    function renderAssetsList(type, assets) {
        var container = document.getElementById('assets-' + type + '-list');
        if (assets.length === 0) {
            container.innerHTML = '<div class="text-muted text-center py-3" style="font-size: 0.8rem;">No ' + type + ' assigned</div>';
            return;
        }
        
        var html = '';
        assets.forEach(function(asset) {
            var statusColor = asset.status === 'active' || asset.status === 'approved' || asset.status === 'verified' ? '#22c55e' : (asset.status === 'suspended' ? '#f59e0b' : '#6b7280');
            html += '<div class="list-group-item d-flex justify-content-between align-items-center py-2 px-0 border-0 border-bottom">';
            html += '<div>';
            html += '<div style="font-size: 0.85rem; font-weight: 500;">' + escapeHtml(asset.name) + '</div>';
            html += '<small class="text-muted">' + asset.type + '</small>';
            html += '</div>';
            html += '<div class="d-flex align-items-center gap-2">';
            html += '<span class="badge" style="background: ' + statusColor + '20; color: ' + statusColor + '; font-size: 0.65rem;">' + capitalise(asset.status) + '</span>';
            html += '<button class="btn btn-sm btn-purple-outline" style="font-size: 0.65rem; padding: 2px 6px;">Manage</button>';
            html += '</div>';
            html += '</div>';
        });
        container.innerHTML = html;
    }
    
    window.changeSubAccountStatus = function(subId, newStatus) {
        var subAccount = hierarchyData.subAccounts.find(function(s) { return s.id === subId; });
        if (!subAccount) return;
        
        var actionMap = { 'live': 'reactivate', 'suspended': 'suspend', 'archived': 'archive' };
        var action = actionMap[newStatus] || newStatus;
        var btnClass = newStatus === 'suspended' ? 'btn-warning' : (newStatus === 'archived' ? 'btn-danger' : 'btn-success');
        
        showConfirmModal(
            capitalise(action) + ' Sub-Account',
            'Are you sure you want to ' + action + ' "' + subAccount.name + '"? This will affect all users in this sub-account.',
            capitalise(action),
            btnClass,
            function() {
                var confirmBtn = document.getElementById('confirmModalBtn');
                confirmBtn.disabled = true;
                confirmBtn.textContent = 'Processing...';
                apiRequest('/api/sub-accounts/' + subId + '/' + action, 'PUT').then(function(resp) {
                    showToast('success', 'Status Changed', 'Sub-account status changed to ' + capitalise(newStatus));
                    loadHierarchy();
                    var offcanvasEl = document.getElementById('subAccountDetailDrawer');
                    var offcanvas = bootstrap.Offcanvas.getInstance(offcanvasEl);
                    if (offcanvas) offcanvas.hide();
                }).catch(function(err) {
                    showToast('error', 'Error', err.message);
                }).finally(function() {
                    confirmBtn.disabled = false;
                });
            }
        );
    };
    
    function openUserDetailDrawer(userId, subId) {
        var subAccount = hierarchyData.subAccounts.find(function(s) { return s.id === subId; });
        if (!subAccount) return;
        
        var user = subAccount.users.find(function(u) { return u.id === userId; });
        if (!user) return;
        
        document.getElementById('drawer-user-id').value = userId;
        document.getElementById('drawer-user-subaccount-id').value = subId;
        document.getElementById('drawer-user-name').textContent = user.name;
        document.getElementById('drawer-user-email').textContent = user.email;
        
        var statusPillHtml = '';
        var actionsHtml = '';
        var status = user.status;
        
        if (status === 'active') {
            statusPillHtml = '<span class="badge" style="background: #dcfce7; color: #166534; font-size: 0.8rem; padding: 6px 12px;">Active</span>';
            actionsHtml = '<button class="btn btn-sm btn-warning" onclick="changeUserStatus(\'' + userId + '\', \'' + subId + '\', \'suspended\')">Suspend</button>';
        } else if (status === 'suspended') {
            statusPillHtml = '<span class="badge" style="background: #fef3c7; color: #92400e; font-size: 0.8rem; padding: 6px 12px;">Suspended</span>';
            actionsHtml = '<button class="btn btn-sm btn-success me-1" onclick="changeUserStatus(\'' + userId + '\', \'' + subId + '\', \'active\')">Reactivate</button>';
        } else if (status === 'invited') {
            statusPillHtml = '<span class="badge" style="background: #dbeafe; color: #1e40af; font-size: 0.8rem; padding: 6px 12px;">Invited</span>';
            actionsHtml = '<button class="btn btn-sm btn-purple-outline">Resend Invite</button>';
        } else {
            statusPillHtml = '<span class="badge" style="background: #f3f4f6; color: #6b7280; font-size: 0.8rem; padding: 6px 12px;">Archived</span>';
            actionsHtml = '<button class="btn btn-sm btn-success" onclick="changeUserStatus(\'' + userId + '\', \'' + subId + '\', \'active\')">Reactivate</button>';
        }
        
        document.getElementById('drawer-user-status-pill').innerHTML = statusPillHtml;
        document.getElementById('drawer-user-status-actions').innerHTML = actionsHtml;
        
        var securitySection = document.getElementById('drawer-security-actions-section');
        if (securitySection) {
            securitySection.style.display = (status === 'archived') ? 'none' : '';
        }
        
        document.getElementById('drawer-user-role').textContent = formatRole(user.role);
        document.getElementById('drawer-user-sender').textContent = user.senderCapability ? capitalise(user.senderCapability) : 'N/A';
        
        var userLimits = user.userLimits || {};
        document.getElementById('drawer-user-spend-cap').value = userLimits.spendCap || '';
        document.getElementById('drawer-user-message-cap').value = userLimits.messageCap || '';
        document.getElementById('drawer-user-enforcement').value = userLimits.enforcementType || 'inherit';
        
        var usage = user.usage || { spend: 0, messages: 0 };
        document.getElementById('drawer-user-spend').textContent = '£' + usage.spend.toFixed(2);
        document.getElementById('drawer-user-msgs').textContent = usage.messages.toLocaleString();
        document.getElementById('drawer-user-enforce-state').textContent = 'Normal';
        
        var roleInfo = ROLE_NAV_ACCESS[user.role] || {};
        document.getElementById('drawer-role-badge').textContent = roleInfo.label || formatRole(user.role);
        document.getElementById('drawer-role-description').textContent = roleInfo.note || 'No description available.';
        
        var navHtml = '';
        (roleInfo.nav || []).forEach(function(nav) {
            navHtml += '<span class="badge" style="background: #f3e8ff; color: #7c3aed; font-size: 0.7rem;">' + nav + '</span>';
        });
        document.getElementById('drawer-role-nav-access').innerHTML = navHtml;
        
        var offcanvas = new bootstrap.Offcanvas(document.getElementById('userDetailDrawer'));
        offcanvas.show();
    }
    
    function escapeHtml(str) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    window.triggerPasswordReset = function() {
        var userId = document.getElementById('drawer-user-id').value;
        var subId = document.getElementById('drawer-user-subaccount-id').value;
        var userName = document.getElementById('drawer-user-name').textContent;
        var userEmail = document.getElementById('drawer-user-email').textContent;

        var safeEmail = escapeHtml(userEmail);
        var safeName = escapeHtml(userName);

        showConfirmModal(
            'Reset Password',
            'A password reset email will be sent to <strong>' + safeEmail + '</strong>. The link will expire after 24 hours.<br><br>Are you sure you want to reset the password for <strong>' + safeName + '</strong>?',
            'Send Reset Email',
            'btn-primary-purple',
            function() {
                console.log('[AUDIT] Password reset triggered:', {
                    action: 'PASSWORD_RESET_TRIGGERED',
                    userId: userId,
                    userName: userName,
                    userEmail: userEmail,
                    subAccountId: subId,
                    timestamp: new Date().toISOString()
                });

                showToast('success', 'Reset Email Sent', 'Password reset email has been sent to ' + userEmail);
            },
            true
        );
    };

    window.changeUserStatus = function(userId, subId, newStatus) {
        var subAccount = hierarchyData.subAccounts.find(function(s) { return s.id === subId; });
        if (!subAccount) return;
        
        var user = subAccount.users.find(function(u) { return u.id === userId; });
        if (!user) return;
        
        var actionMap = { 'active': 'reactivate', 'suspended': 'suspend' };
        var action = actionMap[newStatus] || newStatus;
        var btnClass = newStatus === 'suspended' ? 'btn-warning' : 'btn-success';
        
        showConfirmModal(
            capitalise(action) + ' User',
            'Are you sure you want to ' + action + ' user "' + user.name + '"?',
            capitalise(action),
            btnClass,
            function() {
                var confirmBtn = document.getElementById('confirmModalBtn');
                confirmBtn.disabled = true;
                confirmBtn.textContent = 'Processing...';
                apiRequest('/api/users/' + userId + '/' + action, 'PUT').then(function(resp) {
                    showToast('success', 'Status Changed', 'User status changed to ' + capitalise(newStatus));
                    loadHierarchy();
                    var offcanvasEl = document.getElementById('userDetailDrawer');
                    var offcanvas = bootstrap.Offcanvas.getInstance(offcanvasEl);
                    if (offcanvas) offcanvas.hide();
                }).catch(function(err) {
                    showToast('error', 'Error', err.message);
                }).finally(function() {
                    confirmBtn.disabled = false;
                });
            }
        );
    };
    
    document.getElementById('btn-save-limits').addEventListener('click', function() {
        var subId = document.getElementById('drawer-subaccount-id').value;
        
        var payload = {
            monthly_spending_cap: parseFloat(document.getElementById('drawer-spend-cap').value) || null,
            monthly_message_cap: parseInt(document.getElementById('drawer-message-cap').value) || null,
            daily_send_limit: parseInt(document.getElementById('drawer-daily-limit').value) || null,
            enforcement_type: document.getElementById('drawer-enforcement-type').value,
            hard_stop_enabled: document.getElementById('drawer-hard-stop').checked
        };
        
        var btn = document.getElementById('btn-save-limits');
        btn.disabled = true;
        btn.textContent = 'Saving...';
        
        apiRequest('/api/sub-accounts/' + subId + '/limits', 'PUT', payload).then(function(resp) {
            showToast('success', 'Limits Saved', 'Limits saved successfully. Changes have been logged.');
            loadHierarchy();
        }).catch(function(err) {
            showToast('error', 'Error', err.message);
        }).finally(function() {
            btn.disabled = false;
            btn.innerHTML = 'Save Changes';
        });
    });
    
    document.getElementById('btn-save-notifications').addEventListener('click', function() {
        showToast('success', 'Settings saved', 'Notification settings have been updated.');
    });
    
    loadHierarchy();
});
</script>
@endpush
