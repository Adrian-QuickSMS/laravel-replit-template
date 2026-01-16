@extends('layouts.quicksms')

@section('title', 'Users and Access')

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
    opacity: 0;
    transition: opacity 0.15s;
    font-size: 0.75rem;
    padding: 0.25rem 0.625rem;
    border-radius: 0.25rem;
    white-space: nowrap;
}
.main-account-node:hover .contextual-btn,
.sub-account-header:hover .contextual-btn {
    opacity: 1;
}
.main-account-node .contextual-btn {
    background: rgba(255, 255, 255, 0.2);
    border: 1px solid rgba(255, 255, 255, 0.4);
    color: #fff;
}
.main-account-node .contextual-btn:hover {
    background: rgba(255, 255, 255, 0.3);
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

.role-pill {
    font-size: 0.7rem;
    font-weight: 500;
    padding: 0.2rem 0.5rem;
    border-radius: 0.25rem;
    text-transform: capitalize;
}
.role-pill.owner {
    background: rgba(136, 108, 192, 0.15);
    color: #886cc0;
}
.role-pill.admin {
    background: rgba(59, 130, 246, 0.12);
    color: #3b82f6;
}
.role-pill.messaging-manager {
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
.role-pill.auditor {
    background: rgba(107, 114, 128, 0.12);
    color: #6b7280;
}

.status-pill {
    font-size: 0.65rem;
    font-weight: 500;
    padding: 0.15rem 0.4rem;
    border-radius: 0.25rem;
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
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row page-titles">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="#">Account</a></li>
            <li class="breadcrumb-item active">Users and Access</li>
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
                            <span class="badge bg-warning text-dark ms-1" style="font-size: 0.65rem;">Admin Only</span>
                        </button>
                    </li>
                </ul>
                
                <div class="tab-content" id="addUserTabContent">
                    <div class="tab-pane fade show active" id="invite-pane" role="tabpanel">
                        <div class="alert alert-light border mb-4" style="font-size: 0.85rem;">
                            <strong>Invitation Flow:</strong> The user will receive an email to set their password and enrol MFA. Once completed, they become Active.
                        </div>
                        
                        <form id="invite-user-form">
                            <div class="mb-3">
                                <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="invite-email" placeholder="user@company.com" required>
                                <div class="form-text">Invitation will be sent to this email address</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Assign to Sub-Account <span class="text-danger">*</span></label>
                                <select class="form-select" id="invite-sub-account" required>
                                    <option value="">Select Sub-Account...</option>
                                </select>
                                <div class="form-text">Users belong to exactly one Sub-Account</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Role <span class="text-danger">*</span></label>
                                <select class="form-select" id="invite-role" required>
                                    <option value="">Select Role...</option>
                                    <option value="admin">Admin</option>
                                    <option value="messaging-manager">Messaging Manager</option>
                                    <option value="finance">Finance / Billing</option>
                                    <option value="developer">Developer / API User</option>
                                    <option value="auditor">Read-Only / Auditor</option>
                                    <optgroup label="Optional Roles">
                                        <option value="campaign-approver">Campaign Approver</option>
                                        <option value="security-officer">Security Officer</option>
                                    </optgroup>
                                </select>
                                <div class="form-text">Determines navigation and feature access</div>
                                <div id="invite-role-info" class="mt-2 p-2 rounded" style="background: #f8f9fa; font-size: 0.8rem; display: none;"></div>
                            </div>
                            <div class="mb-3" id="sender-capability-group">
                                <label class="form-label">Sender Capability Level <span class="text-danger">*</span></label>
                                <select class="form-select" id="invite-sender-capability" required>
                                    <option value="">Select Capability...</option>
                                    <option value="advanced">Advanced Sender - Full content creation, Contact Book, CSV uploads</option>
                                    <option value="restricted">Restricted Sender - Templates only, predefined lists only</option>
                                </select>
                                <div class="form-text">Controls how messages can be composed and sent</div>
                            </div>
                        </form>
                    </div>
                    
                    <div class="tab-pane fade" id="direct-pane" role="tabpanel">
                        <div class="alert alert-warning mb-4" style="font-size: 0.85rem;">
                            <strong>Elevated Risk Action</strong><br>
                            Direct user creation bypasses the standard invitation flow. The user will be required to:
                            <ul class="mb-0 mt-2">
                                <li>Reset their password on first login</li>
                                <li>Enrol MFA immediately before accessing the platform</li>
                            </ul>
                            This action is logged as a high-risk audit event.
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
                                <label class="form-label">Assign to Sub-Account <span class="text-danger">*</span></label>
                                <select class="form-select" id="direct-sub-account" required>
                                    <option value="">Select Sub-Account...</option>
                                </select>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Role <span class="text-danger">*</span></label>
                                    <select class="form-select" id="direct-role" required>
                                        <option value="">Select Role...</option>
                                        <option value="admin">Admin</option>
                                        <option value="messaging-manager">Messaging Manager</option>
                                        <option value="finance">Finance / Billing</option>
                                        <option value="developer">Developer / API User</option>
                                        <option value="auditor">Read-Only / Auditor</option>
                                        <optgroup label="Optional Roles">
                                            <option value="campaign-approver">Campaign Approver</option>
                                            <option value="security-officer">Security Officer</option>
                                        </optgroup>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3" id="direct-sender-capability-group">
                                    <label class="form-label">Sender Capability Level <span class="text-danger">*</span></label>
                                    <select class="form-select" id="direct-sender-capability" required>
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
                <button type="button" class="btn btn-warning" id="btn-direct-create" style="display: none;">Create User Directly</button>
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
                            <span class="badge bg-secondary">Optional</span>
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
                                <label class="form-label">Campaign Approval Required</label>
                                <select class="form-select" id="sub-approval-required">
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
                        
                        <div class="alert alert-light border mt-3 mb-0" style="font-size: 0.8rem;">
                            <strong>Note:</strong> These rules can be modified later in the Sub-Account settings. Users in this sub-account will inherit these defaults.
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
                <div class="alert alert-info mb-4" style="font-size: 0.85rem;">
                    <strong>Role-Based Navigation:</strong> Roles control which sections of QuickSMS the user can access, not individual feature toggles.
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
                    <select class="form-select" id="change-role-new" required>
                        <option value="">Select New Role...</option>
                        <option value="admin">Admin</option>
                        <option value="messaging-manager">Messaging Manager</option>
                        <option value="finance">Finance / Billing</option>
                        <option value="developer">Developer / API User</option>
                        <option value="auditor">Read-Only / Auditor</option>
                        <optgroup label="Optional Roles">
                            <option value="campaign-approver">Campaign Approver</option>
                            <option value="security-officer">Security Officer</option>
                        </optgroup>
                    </select>
                    <div id="change-role-info" class="mt-2 p-2 rounded" style="background: #f8f9fa; font-size: 0.8rem; display: none;"></div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Reason for Change <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="change-role-reason" rows="2" placeholder="e.g., Promotion to team lead, department transfer" required></textarea>
                    <div class="form-text">This will be recorded in the audit log</div>
                </div>
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
                <div class="alert alert-info mb-4" style="font-size: 0.85rem;">
                    <strong>Sender Capability:</strong> Controls what messaging features a user can access, separate from their role.
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
                        <span class="badge bg-secondary ms-2" id="perm-role-display"></span>
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
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var currentUserRole = 'admin';
    var isMainAccountAdmin = true;
    
    var ROLE_NAV_ACCESS = {
        'owner': { label: 'Account Owner', nav: ['Dashboard', 'Messages', 'Contact Book', 'Reporting', 'Purchase', 'Management', 'Account', 'Support'], note: 'Full access. One per Main Account.' },
        'admin': { label: 'Admin', nav: ['Dashboard', 'Messages', 'Contact Book', 'Reporting', 'Purchase', 'Management', 'Account', 'Support'], note: 'Full access within their scope.' },
        'messaging-manager': { label: 'Messaging Manager', nav: ['Dashboard', 'Messages', 'Contact Book', 'Reporting', 'Management', 'Support'], note: 'Can send messages, manage contacts and templates.' },
        'finance': { label: 'Finance / Billing', nav: ['Dashboard', 'Reporting', 'Purchase', 'Support'], note: 'Access to billing, invoices, and purchases.' },
        'developer': { label: 'Developer / API User', nav: ['Dashboard', 'Management', 'Reporting', 'Support'], note: 'Access to API connections and technical settings.' },
        'auditor': { label: 'Read-Only / Auditor', nav: ['Dashboard', 'Messages', 'Contact Book', 'Reporting', 'Management', 'Account', 'Support'], note: 'View-only access for compliance.' },
        'campaign-approver': { label: 'Campaign Approver', nav: ['Dashboard', 'Messages', 'Reporting', 'Support'], note: 'Can review and approve campaigns.' },
        'security-officer': { label: 'Security Officer', nav: ['Dashboard', 'Account', 'Reporting', 'Support'], note: 'Manages security and access reviews.' }
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
    
    var hierarchyData = {
        mainAccount: {
            id: 'main-001',
            name: 'Acme Corporation Ltd',
            companyNumber: '12345678',
            accountOwner: {
                id: 'user-001',
                name: 'Sarah Mitchell',
                email: 'sarah.mitchell@acme.co.uk',
                role: 'owner',
                status: 'active'
            }
        },
        subAccounts: [
            {
                id: 'sub-001',
                name: 'Marketing Department',
                description: 'Marketing and communications team',
                users: [
                    { id: 'user-002', name: 'James Wilson', email: 'james.wilson@acme.co.uk', role: 'admin', status: 'active' },
                    { id: 'user-003', name: 'Emma Thompson', email: 'emma.t@acme.co.uk', role: 'messaging-manager', status: 'active' },
                    { id: 'user-004', name: 'Michael Brown', email: 'michael.b@acme.co.uk', role: 'messaging-manager', status: 'active' },
                    { id: 'user-005', name: 'Lisa Chen', email: 'lisa.chen@acme.co.uk', role: 'auditor', status: 'invited' }
                ]
            },
            {
                id: 'sub-002',
                name: 'Finance Team',
                description: 'Finance and billing department',
                users: [
                    { id: 'user-006', name: 'Robert Taylor', email: 'robert.t@acme.co.uk', role: 'admin', status: 'active' },
                    { id: 'user-007', name: 'Jennifer Adams', email: 'jennifer.a@acme.co.uk', role: 'finance', status: 'active' }
                ]
            },
            {
                id: 'sub-003',
                name: 'IT & Development',
                description: 'Technical and API integration team',
                users: [
                    { id: 'user-008', name: 'David Park', email: 'david.park@acme.co.uk', role: 'admin', status: 'active' },
                    { id: 'user-009', name: 'Alex Johnson', email: 'alex.j@acme.co.uk', role: 'developer', status: 'active' },
                    { id: 'user-010', name: 'Sophie Williams', email: 'sophie.w@acme.co.uk', role: 'developer', status: 'suspended' }
                ]
            },
            {
                id: 'sub-004',
                name: 'Customer Support',
                description: 'Customer service and support',
                users: [
                    { id: 'user-011', name: 'Chris Martinez', email: 'chris.m@acme.co.uk', role: 'messaging-manager', status: 'active' }
                ]
            }
        ]
    };

    function renderHierarchy() {
        var tree = document.getElementById('hierarchy-tree');
        var html = '';
        
        html += '<div class="main-account-node">';
        html += '<div>';
        html += '<div class="account-name">' + escapeHtml(hierarchyData.mainAccount.name) + '</div>';
        html += '<div class="account-info">Main Account</div>';
        html += '</div>';
        html += '<button class="contextual-btn btn-add-sub-account" type="button">+ Add Sub-Account</button>';
        html += '</div>';
        
        html += '<div class="tree-connector"></div>';
        
        html += '<div class="sub-accounts-container">';
        
        var visibleSubAccounts = hierarchyData.subAccounts;
        if (!isMainAccountAdmin) {
            visibleSubAccounts = hierarchyData.subAccounts.filter(function(sub) {
                return sub.id === 'sub-001';
            });
            document.getElementById('restricted-notice').style.display = 'block';
        }
        
        visibleSubAccounts.forEach(function(subAccount, index) {
            html += '<div class="sub-account-branch">';
            html += '<div class="sub-account-node" data-sub-id="' + subAccount.id + '">';
            
            html += '<div class="sub-account-header" data-sub-id="' + subAccount.id + '">';
            html += '<div class="d-flex align-items-center gap-2 flex-grow-1" data-toggle-users="' + subAccount.id + '">';
            html += '<div>';
            html += '<div class="sub-name">' + escapeHtml(subAccount.name) + '</div>';
            html += '<div class="sub-meta">' + subAccount.users.length + ' user' + (subAccount.users.length !== 1 ? 's' : '') + '</div>';
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
                    var hasMessagingRole = ['owner', 'admin', 'messaging-manager', 'campaign-approver'].includes(user.role);
                    
                    html += '<div class="user-row" data-user-id="' + user.id + '" data-sub-account-id="' + subAccount.id + '">';
                    html += '<div class="user-info">';
                    html += '<span class="user-name">' + escapeHtml(user.name) + '</span>';
                    html += '<span class="user-email">' + escapeHtml(user.email) + '</span>';
                    html += '</div>';
                    html += '<div class="user-pills">';
                    html += '<span class="role-pill ' + user.role + '">' + formatRole(user.role) + '</span>';
                    
                    if (hasMessagingRole && user.senderCapability) {
                        var capClass = user.senderCapability === 'advanced' ? 'capability-advanced' : 'capability-restricted';
                        var capLabel = user.senderCapability === 'advanced' ? 'Advanced' : 'Restricted';
                        html += '<span class="capability-pill ' + capClass + '" style="background: ' + (user.senderCapability === 'advanced' ? 'linear-gradient(135deg, #886cc0 0%, #a78bfa 100%); color: #fff;' : '#f3f4f6; color: #6b7280; border: 1px solid #e5e7eb;') + ' padding: 2px 8px; border-radius: 4px; font-size: 0.7rem; margin-left: 4px;">' + capLabel + '</span>';
                    }
                    
                    html += '<span class="status-pill ' + user.status + '">' + capitalise(user.status) + '</span>';
                    
                    if (user.role !== 'owner' && isMainAccountAdmin) {
                        html += '<button class="btn btn-sm btn-outline-secondary ms-2 btn-change-role" data-user-id="' + user.id + '" data-user-name="' + escapeHtml(user.name) + '" data-user-role="' + user.role + '" data-sub-account-id="' + subAccount.id + '" style="font-size: 0.7rem; padding: 2px 8px;">Change Role</button>';
                        
                        if (hasMessagingRole) {
                            html += '<button class="btn btn-sm btn-outline-primary ms-1 btn-change-capability" data-user-id="' + user.id + '" data-user-name="' + escapeHtml(user.name) + '" data-user-capability="' + (user.senderCapability || 'restricted') + '" data-sub-account-id="' + subAccount.id + '" style="font-size: 0.7rem; padding: 2px 8px;">Sender Level</button>';
                        }
                        
                        var overrideCount = user.permissionOverrides ? Object.keys(user.permissionOverrides).length : 0;
                        var overrideBadge = overrideCount > 0 ? ' <span class="badge bg-warning text-dark" style="font-size: 0.6rem;">' + overrideCount + '</span>' : '';
                        html += '<button class="btn btn-sm btn-outline-info ms-1 btn-manage-permissions" data-user-id="' + user.id + '" data-user-name="' + escapeHtml(user.name) + '" data-user-role="' + user.role + '" data-sub-account-id="' + subAccount.id + '" style="font-size: 0.7rem; padding: 2px 8px;">Permissions' + overrideBadge + '</button>';
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
        
        totalUsers = 1;
        activeUsers = 1;
        
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
    }
    
    function openInviteUserModal(preSelectedSubId) {
        document.getElementById('invite-user-form').reset();
        var select = document.getElementById('invite-sub-account');
        select.innerHTML = '<option value="">Select Sub-Account...</option>';
        hierarchyData.subAccounts.forEach(function(sub) {
            var selected = sub.id === preSelectedSubId ? ' selected' : '';
            select.innerHTML += '<option value="' + sub.id + '"' + selected + '>' + escapeHtml(sub.name) + '</option>';
        });
        
        var modal = new bootstrap.Modal(document.getElementById('inviteUserModal'));
        modal.show();
    }
    
    function formatRole(role) {
        var roleMap = {
            'owner': 'Account Owner',
            'admin': 'Admin',
            'messaging-manager': 'Messaging Manager',
            'finance': 'Finance',
            'developer': 'Developer',
            'auditor': 'Auditor',
            'campaign-approver': 'Campaign Approver',
            'security-officer': 'Security Officer'
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
    
    document.getElementById('btn-create-sub-account').addEventListener('click', function() {
        var name = document.getElementById('sub-account-name').value.trim();
        var description = document.getElementById('sub-account-description').value.trim();
        
        if (!name) {
            alert('Please enter a sub-account name');
            return;
        }
        
        var dailyLimit = document.getElementById('sub-daily-limit').value;
        var monthlyCap = document.getElementById('sub-monthly-cap').value;
        var approvalRequired = document.getElementById('sub-approval-required').value;
        var limitEnforcement = document.getElementById('sub-limit-enforcement').value;
        
        var enforcementRules = {
            dailySendLimit: dailyLimit ? parseInt(dailyLimit) : null,
            monthlySpendCap: monthlyCap ? parseFloat(monthlyCap) : null,
            campaignApprovalRequired: approvalRequired === 'yes',
            limitEnforcement: limitEnforcement
        };
        
        var newSubAccount = {
            id: 'sub-' + Date.now(),
            name: name,
            description: description,
            users: [],
            enforcementRules: enforcementRules,
            createdAt: new Date().toISOString(),
            createdBy: 'current-user'
        };
        
        hierarchyData.subAccounts.push(newSubAccount);
        
        bootstrap.Modal.getInstance(document.getElementById('addSubAccountModal')).hide();
        document.getElementById('add-sub-account-form').reset();
        
        renderHierarchy();
        
        var auditEntry = {
            action: 'SUB_ACCOUNT_CREATED',
            subAccountId: newSubAccount.id,
            subAccountName: name,
            description: description || null,
            enforcementRules: enforcementRules,
            createdBy: {
                userId: 'user-001',
                userName: 'Sarah Mitchell',
                role: 'admin'
            },
            timestamp: new Date().toISOString(),
            ipAddress: '192.168.1.100'
        };
        
        console.log('[Audit] Sub-Account created:', auditEntry);
    });
    
    var roleSelect = document.getElementById('invite-role');
    var senderCapabilityGroup = document.getElementById('sender-capability-group');
    
    roleSelect.addEventListener('change', function() {
        var role = this.value;
        var nonMessagingRoles = ['finance', 'auditor'];
        
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
        var role = document.getElementById('invite-role').value;
        var senderCapability = document.getElementById('invite-sender-capability').value;
        
        var nonMessagingRoles = ['finance', 'auditor'];
        var requiresCapability = !nonMessagingRoles.includes(role);
        
        if (!email || !subAccountId || !role) {
            alert('Please fill in all required fields');
            return;
        }
        
        if (requiresCapability && !senderCapability) {
            alert('Please select a Sender Capability Level');
            return;
        }
        
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            alert('Please enter a valid email address');
            return;
        }
        
        var subAccount = hierarchyData.subAccounts.find(function(s) { return s.id === subAccountId; });
        if (subAccount) {
            var newUser = {
                id: 'user-' + Date.now(),
                name: email.split('@')[0],
                email: email,
                role: role,
                senderCapability: requiresCapability ? senderCapability : null,
                status: 'invited',
                invitedAt: new Date().toISOString(),
                expiresAt: new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString()
            };
            
            subAccount.users.push(newUser);
        }
        
        bootstrap.Modal.getInstance(document.getElementById('inviteUserModal')).hide();
        document.getElementById('invite-user-form').reset();
        senderCapabilityGroup.style.display = 'block';
        
        renderHierarchy();
        
        var auditEntry = {
            action: 'USER_INVITED',
            email: email,
            subAccountId: subAccountId,
            subAccountName: subAccount ? subAccount.name : null,
            role: role,
            senderCapability: requiresCapability ? senderCapability : 'N/A',
            invitedBy: {
                userId: 'user-001',
                userName: 'Sarah Mitchell',
                role: 'admin'
            },
            timestamp: new Date().toISOString(),
            inviteExpiresAt: new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString(),
            ipAddress: '192.168.1.100'
        };
        
        console.log('[Audit] User invited:', auditEntry);
        
        alert('Invitation sent to ' + email + '. The user will receive an email to complete their setup.');
    });
    
    var isMainAccountAdmin = true;
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
        var nonMessagingRoles = ['finance', 'auditor'];
        
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
        var role = document.getElementById('direct-role').value;
        var senderCapability = document.getElementById('direct-sender-capability').value;
        var reason = document.getElementById('direct-reason').value.trim();
        var confirmRisk = document.getElementById('direct-confirm-risk').checked;
        
        var nonMessagingRoles = ['finance', 'auditor'];
        var requiresCapability = !nonMessagingRoles.includes(role);
        
        if (!firstName || !lastName || !email || !tempPassword || !subAccountId || !role || !reason) {
            alert('Please fill in all required fields');
            return;
        }
        
        if (requiresCapability && !senderCapability) {
            alert('Please select a Sender Capability Level');
            return;
        }
        
        if (tempPassword.length < 12) {
            alert('Password must be at least 12 characters');
            return;
        }
        
        if (!confirmRisk) {
            alert('You must acknowledge the risk before creating a user directly');
            return;
        }
        
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            alert('Please enter a valid email address');
            return;
        }
        
        var subAccount = hierarchyData.subAccounts.find(function(s) { return s.id === subAccountId; });
        if (subAccount) {
            var newUser = {
                id: 'user-' + Date.now(),
                name: firstName + ' ' + lastName,
                email: email,
                role: role,
                senderCapability: requiresCapability ? senderCapability : null,
                status: 'active',
                createdAt: new Date().toISOString(),
                mustResetPassword: true,
                mfaEnforced: true,
                creationMethod: 'direct'
            };
            
            subAccount.users.push(newUser);
        }
        
        bootstrap.Modal.getInstance(document.getElementById('inviteUserModal')).hide();
        document.getElementById('direct-create-form').reset();
        document.getElementById('direct-confirm-risk').checked = false;
        directSenderCapabilityGroup.style.display = 'block';
        
        var inviteTab = document.getElementById('invite-tab');
        var bsTab = new bootstrap.Tab(inviteTab);
        bsTab.show();
        btnSendInvite.style.display = 'inline-block';
        btnDirectCreate.style.display = 'none';
        
        renderHierarchy();
        
        var auditEntry = {
            action: 'USER_CREATED_DIRECT',
            riskLevel: 'HIGH',
            user: {
                name: firstName + ' ' + lastName,
                email: email,
                role: role,
                senderCapability: requiresCapability ? senderCapability : 'N/A'
            },
            subAccountId: subAccountId,
            subAccountName: subAccount ? subAccount.name : null,
            reason: reason,
            securityFlags: {
                mustResetPassword: true,
                mfaEnforced: true
            },
            createdBy: {
                userId: 'user-001',
                userName: 'Sarah Mitchell',
                role: 'main-account-admin'
            },
            timestamp: new Date().toISOString(),
            ipAddress: '192.168.1.100'
        };
        
        console.log('[HIGH-RISK AUDIT] Direct user creation:', auditEntry);
        
        alert('User "' + firstName + ' ' + lastName + '" has been created.\n\nThey must:\n• Reset their password on first login\n• Enrol MFA immediately\n\nThis action has been logged.');
    });
    
    document.getElementById('inviteUserModal').addEventListener('show.bs.modal', function() {
        var subAccounts = hierarchyData.subAccounts;
        
        var inviteSelect = document.getElementById('invite-sub-account');
        var directSelect = document.getElementById('direct-sub-account');
        
        [inviteSelect, directSelect].forEach(function(select) {
            select.innerHTML = '<option value="">Select Sub-Account...</option>';
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
            document.getElementById('change-role-info').style.display = 'none';
            
            var modal = new bootstrap.Modal(document.getElementById('changeRoleModal'));
            modal.show();
        }
    });
    
    document.getElementById('btn-confirm-role-change').addEventListener('click', function() {
        var userId = document.getElementById('change-role-user-id').value;
        var userName = document.getElementById('change-role-user-name').value;
        var previousRole = document.getElementById('change-role-current-value').value;
        var newRole = document.getElementById('change-role-new').value;
        var reason = document.getElementById('change-role-reason').value.trim();
        var subAccountId = document.getElementById('change-role-sub-account-id').value;
        
        if (!newRole || !reason) {
            alert('Please select a new role and provide a reason for the change');
            return;
        }
        
        if (newRole === previousRole) {
            alert('The new role must be different from the current role');
            return;
        }
        
        var subAccount = hierarchyData.subAccounts.find(function(s) { return s.id === subAccountId; });
        if (subAccount) {
            var user = subAccount.users.find(function(u) { return u.id === userId; });
            if (user) {
                user.role = newRole;
            }
        }
        
        var auditEntry = {
            action: 'ROLE_CHANGED',
            userId: userId,
            userName: userName,
            previousRole: previousRole,
            newRole: newRole,
            reason: reason,
            subAccountId: subAccountId,
            changedBy: {
                userId: 'user-001',
                userName: 'Sarah Mitchell',
                role: 'admin'
            },
            timestamp: new Date().toISOString(),
            ipAddress: '192.168.1.100'
        };
        
        console.log('[AUDIT] Role changed:', auditEntry);
        
        bootstrap.Modal.getInstance(document.getElementById('changeRoleModal')).hide();
        renderHierarchy();
        
        alert('Role changed successfully.\n\n' + userName + ' is now a ' + formatRole(newRole) + '.\n\nThis change has been logged.');
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
            alert('Please select a new capability level and provide a reason for the change');
            return;
        }
        
        newCapability = newCapability.value;
        
        if (newCapability === previousCapability) {
            alert('The new capability must be different from the current level');
            return;
        }
        
        var subAccount = hierarchyData.subAccounts.find(function(s) { return s.id === subAccountId; });
        if (subAccount) {
            var user = subAccount.users.find(function(u) { return u.id === userId; });
            if (user) {
                user.senderCapability = newCapability;
            }
        }
        
        var auditEntry = {
            action: 'SENDER_CAPABILITY_CHANGED',
            userId: userId,
            userName: userName,
            previousCapability: previousCapability,
            newCapability: newCapability,
            reason: reason,
            subAccountId: subAccountId,
            changedBy: {
                userId: 'user-001',
                userName: 'Sarah Mitchell',
                role: 'admin'
            },
            timestamp: new Date().toISOString(),
            ipAddress: '192.168.1.100'
        };
        
        console.log('[AUDIT] Sender capability changed:', auditEntry);
        
        bootstrap.Modal.getInstance(document.getElementById('changeCapabilityModal')).hide();
        renderHierarchy();
        
        var capLabel = newCapability === 'advanced' ? 'Advanced Sender' : 'Restricted Sender';
        alert('Sender capability changed successfully.\n\n' + userName + ' is now a ' + capLabel + '.\n\nThis change has been logged.');
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
        var subAccountId = document.getElementById('perm-sub-account-id').value;
        
        var subAccount = hierarchyData.subAccounts.find(function(s) { return s.id === subAccountId; });
        if (subAccount) {
            var user = subAccount.users.find(function(u) { return u.id === userId; });
            if (user) {
                var previousOverrides = user.permissionOverrides || {};
                user.permissionOverrides = JSON.parse(JSON.stringify(tempPermissionChanges));
                
                var changedPerms = [];
                Object.keys(tempPermissionChanges).forEach(function(k) {
                    if (previousOverrides[k] !== tempPermissionChanges[k]) {
                        changedPerms.push(k);
                    }
                });
                Object.keys(previousOverrides).forEach(function(k) {
                    if (tempPermissionChanges[k] === undefined) {
                        changedPerms.push(k + ' (reset)');
                    }
                });
                
                if (changedPerms.length > 0) {
                    var auditEntry = {
                        action: 'PERMISSIONS_UPDATED',
                        userId: userId,
                        userName: user.name,
                        changesCount: changedPerms.length,
                        changes: changedPerms,
                        newOverrides: tempPermissionChanges,
                        changedBy: { userId: 'user-001', userName: 'Sarah Mitchell', role: 'admin' },
                        timestamp: new Date().toISOString(),
                        ipAddress: '192.168.1.100'
                    };
                    console.log('[AUDIT] Permissions updated:', auditEntry);
                }
            }
        }
        
        bootstrap.Modal.getInstance(document.getElementById('managePermissionsModal')).hide();
        renderHierarchy();
        
        var overrideCount = Object.keys(tempPermissionChanges).length;
        alert('Permissions saved.\n\n' + overrideCount + ' override(s) active.\n\nChanges have been logged.');
    });
    
    document.getElementById('btn-reset-all-overrides').addEventListener('click', function() {
        if (!confirm('Reset all permissions to role defaults? This will remove all overrides.')) {
            return;
        }
        
        tempPermissionChanges = {};
        
        var userId = document.getElementById('perm-user-id').value;
        var userRole = document.getElementById('perm-user-role').value;
        var roleDefaults = typeof PermissionManager !== 'undefined' ? PermissionManager.getRoleDefaults(userRole) : {};
        
        renderPermissionsUI(roleDefaults, {});
    });
    
    renderHierarchy();
});
</script>
@endpush
