@extends('layouts.quicksms')

@section('title', $page_title . ' - QuickSMS')

@push('css')
<link href="{{ asset('css/quicksms-pastel.css') }}" rel="stylesheet">
<link href="{{ asset('css/quicksms-global-layout.css') }}" rel="stylesheet">
<style>
.page-header {
    margin-bottom: 1.5rem;
}
.breadcrumb-nav {
    font-size: 0.8rem;
    margin-bottom: 0.75rem;
}
.breadcrumb-nav a {
    color: #886cc0;
    text-decoration: none;
}
.breadcrumb-nav a:hover {
    text-decoration: underline;
}
.breadcrumb-nav .separator {
    color: #9ca3af;
    margin: 0 0.5rem;
}
.breadcrumb-nav .current {
    color: #6b7280;
}

.page-title-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
}
.page-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: #1f2937;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}
.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #886cc0, #a78bfa);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 1rem;
}
.page-actions {
    display: flex;
    gap: 0.5rem;
}

.section-card {
    background: white;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    margin-bottom: 1.5rem;
}
.section-header {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.section-title {
    font-size: 0.95rem;
    font-weight: 600;
    color: #374151;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.section-title i {
    color: #886cc0;
    font-size: 0.9rem;
}
.section-body {
    padding: 1.25rem;
}

.status-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 500;
}
.status-pill.active {
    background: #dcfce7;
    color: #166534;
}
.status-pill.suspended {
    background: #fef3c7;
    color: #92400e;
}
.status-pill.invited {
    background: #dbeafe;
    color: #1e40af;
}
.status-pill.archived {
    background: #f3f4f6;
    color: #6b7280;
}
.status-pill .dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
}
.status-pill.active .dot { background: #16a34a; }
.status-pill.suspended .dot { background: #d97706; }
.status-pill.invited .dot { background: #2563eb; }
.status-pill.archived .dot { background: #9ca3af; }

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.25rem;
}
.info-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}
.info-label {
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
    color: #9ca3af;
    letter-spacing: 0.025em;
}
.info-value {
    font-size: 0.9rem;
    color: #374151;
    font-weight: 500;
}
.info-value.mono {
    font-family: monospace;
    font-size: 0.85rem;
}

.role-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.25rem 0.625rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 500;
    background: #f3e8ff;
    color: #7c3aed;
}
.capability-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.25rem 0.625rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 500;
}
.capability-badge.advanced {
    background: #dcfce7;
    color: #166534;
}
.capability-badge.restricted {
    background: #fef3c7;
    color: #92400e;
}

.mfa-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.25rem 0.625rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 500;
}
.mfa-badge.enabled {
    background: #dcfce7;
    color: #166534;
}
.mfa-badge.disabled {
    background: #fee2e2;
    color: #dc2626;
}

.status-section {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
}
.status-info {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}
.status-label {
    font-size: 0.75rem;
    color: #6b7280;
}
.status-actions {
    display: flex;
    gap: 0.5rem;
}
.status-desc {
    font-size: 0.8rem;
    color: #6b7280;
    padding: 0.75rem;
    background: #f9fafb;
    border-radius: 6px;
    margin-top: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.status-desc i {
    color: #886cc0;
}

.btn-action {
    padding: 0.375rem 0.75rem;
    font-size: 0.8rem;
    border-radius: 6px;
    border: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    font-weight: 500;
    transition: all 0.15s;
}
.btn-action.warning {
    background: #fef3c7;
    color: #92400e;
    border: 1px solid #fcd34d;
}
.btn-action.warning:hover {
    background: #fcd34d;
}
.btn-action.success {
    background: #dcfce7;
    color: #166534;
    border: 1px solid #86efac;
}
.btn-action.success:hover {
    background: #86efac;
}
.btn-action.danger {
    background: #fee2e2;
    color: #dc2626;
    border: 1px solid #fca5a5;
}
.btn-action.danger:hover {
    background: #fca5a5;
}
.btn-action.primary {
    background: #886cc0;
    color: white;
    border: 1px solid #886cc0;
}
.btn-action.primary:hover {
    background: #7c3aed;
}

.limits-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 1.25rem;
}
.limit-card {
    background: #f9fafb;
    border-radius: 8px;
    padding: 1rem;
    border: 1px solid #e5e7eb;
}
.limit-card.inherited {
    border-style: dashed;
    background: #faf8ff;
}
.limit-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 0.5rem;
}
.limit-label {
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
    color: #9ca3af;
    letter-spacing: 0.025em;
}
.inheritance-badge {
    font-size: 0.65rem;
    padding: 0.15rem 0.4rem;
    border-radius: 4px;
    font-weight: 500;
}
.inheritance-badge.inherited {
    background: #f3e8ff;
    color: #7c3aed;
}
.inheritance-badge.override {
    background: #fef3c7;
    color: #92400e;
}
.limit-value {
    font-size: 1.25rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.25rem;
}
.limit-source {
    font-size: 0.75rem;
    color: #9ca3af;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}
.limit-source i {
    font-size: 0.65rem;
}
.sub-account-limit {
    font-size: 0.75rem;
    color: #6b7280;
    margin-top: 0.5rem;
    padding-top: 0.5rem;
    border-top: 1px solid #e5e7eb;
}
.sub-account-limit span {
    font-weight: 500;
    color: #374151;
}

.modal-header {
    border-bottom: 1px solid #e5e7eb;
}
.modal-footer {
    border-top: 1px solid #e5e7eb;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <nav class="breadcrumb-nav">
            <a href="{{ route('account.details') }}">Account</a>
            <span class="separator">/</span>
            <a href="{{ route('account.sub-accounts') }}">Sub-Accounts</a>
            <span class="separator">/</span>
            <a href="{{ route('account.sub-accounts.detail', $sub_account['id']) }}">{{ $sub_account['name'] }}</a>
            <span class="separator">/</span>
            <span class="current">{{ $user['name'] }}</span>
        </nav>
        
        <div class="page-title-row">
            <h1 class="page-title">
                <div class="user-avatar">{{ strtoupper(substr($user['name'], 0, 1)) }}{{ strtoupper(substr(explode(' ', $user['name'])[1] ?? '', 0, 1)) }}</div>
                {{ $user['name'] }}
            </h1>
            <div class="page-actions">
                <a href="{{ route('account.sub-accounts.detail', $sub_account['id']) }}" class="btn btn-action" style="background: #f3f4f6; color: #374151; border: 1px solid #d1d5db;">
                    <i class="fas fa-arrow-left"></i> Back to Sub-Account
                </a>
            </div>
        </div>
    </div>
    
    <div class="section-card" id="status-section">
        <div class="section-header">
            <h2 class="section-title">
                <i class="fas fa-user-check"></i>
                User Status
            </h2>
        </div>
        <div class="section-body">
            <div class="status-section">
                <div class="status-info">
                    <span class="status-label">Current Status</span>
                    <span class="status-pill {{ $user['status'] }}">
                        <span class="dot"></span>
                        {{ ucfirst($user['status']) }}
                    </span>
                </div>
                <div class="status-actions">
                    @if($user['status'] === 'active')
                        <button class="btn-action warning" data-bs-toggle="modal" data-bs-target="#suspendUserModal">
                            <i class="fas fa-pause"></i> Suspend User
                        </button>
                    @elseif($user['status'] === 'suspended')
                        <button class="btn-action success" id="btn-reactivate-user">
                            <i class="fas fa-play"></i> Reactivate
                        </button>
                        <button class="btn-action danger" data-bs-toggle="modal" data-bs-target="#archiveUserModal">
                            <i class="fas fa-archive"></i> Archive
                        </button>
                    @elseif($user['status'] === 'invited')
                        <button class="btn-action primary" id="btn-resend-invite">
                            <i class="fas fa-envelope"></i> Resend Invite
                        </button>
                        <button class="btn-action danger" id="btn-revoke-invite">
                            <i class="fas fa-times"></i> Revoke Invite
                        </button>
                    @endif
                </div>
            </div>
            <div class="status-desc">
                <i class="fas fa-info-circle"></i>
                @if($user['status'] === 'active')
                    This user is active and can access the platform according to their role and permissions.
                @elseif($user['status'] === 'suspended')
                    This user is suspended. They cannot log in or perform any actions until reactivated.
                @elseif($user['status'] === 'invited')
                    This user has been invited but hasn't completed registration. Invites expire after 7 days.
                @else
                    This user has been archived and cannot access the platform.
                @endif
            </div>
        </div>
    </div>
    
    <div class="section-card" id="details-section">
        <div class="section-header">
            <h2 class="section-title">
                <i class="fas fa-id-card"></i>
                User Details
            </h2>
        </div>
        <div class="section-body">
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Full Name</span>
                    <span class="info-value">{{ $user['name'] }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Email Address</span>
                    <span class="info-value mono">{{ $user['email'] }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">User ID</span>
                    <span class="info-value mono">{{ $user['id'] }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Sub-Account</span>
                    <span class="info-value">
                        <a href="{{ route('account.sub-accounts.detail', $sub_account['id']) }}" style="color: #886cc0;">{{ $sub_account['name'] }}</a>
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-label">Created</span>
                    <span class="info-value">{{ $user['created_at'] }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Last Login</span>
                    <span class="info-value">{{ $user['last_login'] }}</span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="section-card" id="role-section">
        <div class="section-header">
            <h2 class="section-title">
                <i class="fas fa-shield-alt"></i>
                Role & Permissions
            </h2>
            <button class="btn-action primary" data-bs-toggle="modal" data-bs-target="#editRoleModal">
                <i class="fas fa-edit"></i> Edit
            </button>
        </div>
        <div class="section-body">
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Role</span>
                    <span class="role-badge">
                        <i class="fas fa-user-tag"></i>
                        {{ $user['role_label'] }}
                    </span>
                </div>
                @if($user['sender_capability'])
                <div class="info-item">
                    <span class="info-label">Sender Capability</span>
                    <span class="capability-badge {{ $user['sender_capability'] }}">
                        <i class="fas fa-{{ $user['sender_capability'] === 'advanced' ? 'unlock' : 'lock' }}"></i>
                        {{ ucfirst($user['sender_capability']) }} Sender
                    </span>
                </div>
                @endif
                <div class="info-item">
                    <span class="info-label">MFA Status</span>
                    <span class="mfa-badge {{ $user['mfa_enabled'] ? 'enabled' : 'disabled' }}">
                        <i class="fas fa-{{ $user['mfa_enabled'] ? 'shield-alt' : 'exclamation-triangle' }}"></i>
                        {{ $user['mfa_enabled'] ? 'Enabled' : 'Not Enabled' }}
                    </span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="section-card" id="limits-section">
        <div class="section-header">
            <h2 class="section-title">
                <i class="fas fa-tachometer-alt"></i>
                User-Level Limits
            </h2>
            <button class="btn-action primary" data-bs-toggle="modal" data-bs-target="#editLimitsModal">
                <i class="fas fa-edit"></i> Edit
            </button>
        </div>
        <div class="section-body">
            @php
                $userSpendCap = $user['spend_cap'] ?? null;
                $userMessageCap = $user['message_cap'] ?? null;
                $userEnforcement = $user['enforcement_type'] ?? null;
                $subAccountSpendCap = 5000;
                $subAccountMessageCap = 100000;
                $subAccountEnforcement = 'soft_stop';
            @endphp
            <div class="limits-grid">
                <div class="limit-card {{ $userSpendCap === null ? 'inherited' : '' }}">
                    <div class="limit-header">
                        <span class="limit-label">Monthly Spend Cap</span>
                        <span class="inheritance-badge {{ $userSpendCap === null ? 'inherited' : 'override' }}">
                            <i class="fas fa-{{ $userSpendCap === null ? 'link' : 'pen' }}"></i>
                            {{ $userSpendCap === null ? 'Inherited' : 'Override' }}
                        </span>
                    </div>
                    <div class="limit-value">£{{ number_format($userSpendCap ?? $subAccountSpendCap, 2) }}</div>
                    <div class="limit-source">
                        @if($userSpendCap === null)
                            <i class="fas fa-arrow-up"></i> From Sub-Account
                        @else
                            <i class="fas fa-user"></i> User-specific limit
                        @endif
                    </div>
                    <div class="sub-account-limit">
                        Sub-Account max: <span>£{{ number_format($subAccountSpendCap, 2) }}</span>
                    </div>
                </div>
                
                <div class="limit-card {{ $userMessageCap === null ? 'inherited' : '' }}">
                    <div class="limit-header">
                        <span class="limit-label">Monthly Message Cap</span>
                        <span class="inheritance-badge {{ $userMessageCap === null ? 'inherited' : 'override' }}">
                            <i class="fas fa-{{ $userMessageCap === null ? 'link' : 'pen' }}"></i>
                            {{ $userMessageCap === null ? 'Inherited' : 'Override' }}
                        </span>
                    </div>
                    <div class="limit-value">{{ number_format($userMessageCap ?? $subAccountMessageCap) }}</div>
                    <div class="limit-source">
                        @if($userMessageCap === null)
                            <i class="fas fa-arrow-up"></i> From Sub-Account
                        @else
                            <i class="fas fa-user"></i> User-specific limit
                        @endif
                    </div>
                    <div class="sub-account-limit">
                        Sub-Account max: <span>{{ number_format($subAccountMessageCap) }}</span>
                    </div>
                </div>
                
                <div class="limit-card {{ $userEnforcement === null ? 'inherited' : '' }}">
                    <div class="limit-header">
                        <span class="limit-label">Enforcement Type</span>
                        <span class="inheritance-badge {{ $userEnforcement === null ? 'inherited' : 'override' }}">
                            <i class="fas fa-{{ $userEnforcement === null ? 'link' : 'pen' }}"></i>
                            {{ $userEnforcement === null ? 'Inherited' : 'Override' }}
                        </span>
                    </div>
                    @php
                        $effectiveEnforcement = $userEnforcement ?? $subAccountEnforcement;
                        $enforcementLabels = [
                            'warn_only' => 'Warn Only',
                            'soft_stop' => 'Soft Stop',
                            'hard_stop' => 'Hard Stop'
                        ];
                    @endphp
                    <div class="limit-value" style="font-size: 1rem;">{{ $enforcementLabels[$effectiveEnforcement] ?? 'Unknown' }}</div>
                    <div class="limit-source">
                        @if($userEnforcement === null)
                            <i class="fas fa-arrow-up"></i> From Sub-Account
                        @else
                            <i class="fas fa-user"></i> User-specific setting
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="section-card" id="activity-section">
        <div class="section-header">
            <h2 class="section-title">
                <i class="fas fa-chart-bar"></i>
                Monthly Activity
            </h2>
        </div>
        <div class="section-body">
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Messages Sent</span>
                    <span class="info-value" style="font-size: 1.25rem; color: #886cc0;">{{ number_format($user['monthly_messages']) }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Spend</span>
                    <span class="info-value" style="font-size: 1.25rem; color: #886cc0;">£{{ number_format($user['monthly_spend'], 2) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="suspendUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-pause-circle text-warning me-2"></i>Suspend User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning mb-3" style="font-size: 0.85rem;">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    <strong>Warning:</strong> This user will immediately lose access to the platform.
                </div>
                <p style="font-size: 0.9rem;">Are you sure you want to suspend <strong>{{ $user['name'] }}</strong>?</p>
                <div class="mb-3">
                    <label class="form-label" style="font-size: 0.85rem;">Reason for suspension <span class="text-muted">(optional)</span></label>
                    <textarea class="form-control" id="suspend-user-reason" rows="2" placeholder="Enter reason for audit trail..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn" id="btn-confirm-suspend-user" style="background: #d97706; color: white;">
                    <i class="fas fa-pause me-1"></i>Suspend User
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="archiveUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-archive text-danger me-2"></i>Archive User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger mb-3" style="font-size: 0.85rem;">
                    <i class="fas fa-exclamation-circle me-1"></i>
                    <strong>Warning:</strong> Archiving a user is a permanent action. This cannot be undone.
                </div>
                <p style="font-size: 0.9rem;">Are you sure you want to archive <strong>{{ $user['name'] }}</strong>?</p>
                <div class="mb-3">
                    <label class="form-label" style="font-size: 0.85rem;">Reason for archiving <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="archive-user-reason" rows="2" placeholder="Required for audit trail..." required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="btn-confirm-archive-user">
                    <i class="fas fa-archive me-1"></i>Archive User
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editRoleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-cog me-2" style="color: #886cc0;"></i>Edit Role & Permissions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label" style="font-size: 0.85rem;">Role</label>
                    <select class="form-select" id="edit-user-role">
                        <option value="admin" {{ $user['role'] === 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="messaging-manager" {{ $user['role'] === 'messaging-manager' ? 'selected' : '' }}>Messaging Manager</option>
                        <option value="finance" {{ $user['role'] === 'finance' ? 'selected' : '' }}>Finance/Billing</option>
                        <option value="developer" {{ $user['role'] === 'developer' ? 'selected' : '' }}>Developer/API User</option>
                        <option value="read-only" {{ $user['role'] === 'read-only' ? 'selected' : '' }}>Read-Only/Auditor</option>
                        <option value="campaign-approver" {{ $user['role'] === 'campaign-approver' ? 'selected' : '' }}>Campaign Approver</option>
                    </select>
                </div>
                <div class="mb-3" id="sender-capability-section" style="{{ $user['sender_capability'] ? '' : 'display: none;' }}">
                    <label class="form-label" style="font-size: 0.85rem;">Sender Capability Level</label>
                    <select class="form-select" id="edit-sender-capability">
                        <option value="advanced" {{ $user['sender_capability'] === 'advanced' ? 'selected' : '' }}>Advanced Sender</option>
                        <option value="restricted" {{ $user['sender_capability'] === 'restricted' ? 'selected' : '' }}>Restricted Sender</option>
                    </select>
                    <div class="form-text" style="font-size: 0.75rem;">
                        Advanced senders can compose free-form messages. Restricted senders can only use approved templates.
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label" style="font-size: 0.85rem;">Reason for change <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="role-change-reason" rows="2" placeholder="Required for audit trail..." required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn" id="btn-save-role" style="background: #886cc0; color: white;">
                    <i class="fas fa-save me-1"></i>Save Changes
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editLimitsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-tachometer-alt me-2" style="color: #886cc0;"></i>Edit User-Level Limits</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info mb-3" style="font-size: 0.8rem; background: #f3e8ff; border-color: #886cc0; color: #5b21b6;">
                    <i class="fas fa-info-circle me-1"></i>
                    User limits cannot exceed Sub-Account limits. Leave blank to inherit from Sub-Account.
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label" style="font-size: 0.85rem;">
                            Monthly Spend Cap
                            <span class="text-muted">(Sub-Account max: £5,000.00)</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">£</span>
                            <input type="number" class="form-control" id="edit-spend-cap" placeholder="Inherit from Sub-Account" min="0" max="5000" step="0.01" data-max="5000">
                        </div>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" id="inherit-spend-cap" checked>
                            <label class="form-check-label" for="inherit-spend-cap" style="font-size: 0.8rem;">
                                Inherit from Sub-Account
                            </label>
                        </div>
                        <div class="invalid-feedback" id="spend-cap-error" style="display: none;">
                            Value exceeds Sub-Account limit of £5,000.00
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" style="font-size: 0.85rem;">
                            Monthly Message Cap
                            <span class="text-muted">(Sub-Account max: 100,000)</span>
                        </label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="edit-message-cap" placeholder="Inherit from Sub-Account" min="0" max="100000" data-max="100000">
                            <span class="input-group-text">msgs</span>
                        </div>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" id="inherit-message-cap" checked>
                            <label class="form-check-label" for="inherit-message-cap" style="font-size: 0.8rem;">
                                Inherit from Sub-Account
                            </label>
                        </div>
                        <div class="invalid-feedback" id="message-cap-error" style="display: none;">
                            Value exceeds Sub-Account limit of 100,000
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label" style="font-size: 0.85rem;">
                        Enforcement Type
                        <span class="text-muted">(Sub-Account: Soft Stop)</span>
                    </label>
                    <select class="form-select" id="edit-enforcement-type">
                        <option value="" selected>Inherit from Sub-Account</option>
                        <option value="warn_only">Warn Only - Alert but allow sending</option>
                        <option value="soft_stop">Soft Stop - Block with admin override</option>
                        <option value="hard_stop">Hard Stop - Block completely</option>
                    </select>
                    <div class="form-text" style="font-size: 0.75rem;">
                        Enforcement determines what happens when limits are reached.
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label" style="font-size: 0.85rem;">Reason for change <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="limits-change-reason" rows="2" placeholder="Required for audit trail..." required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn" id="btn-save-limits" style="background: #886cc0; color: white;">
                    <i class="fas fa-save me-1"></i>Save Changes
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const SUB_ACCOUNT_SPEND_CAP = 5000;
    const SUB_ACCOUNT_MESSAGE_CAP = 100000;
    
    const messagingRoles = ['messaging-manager', 'admin'];
    const roleSelect = document.getElementById('edit-user-role');
    const capabilitySection = document.getElementById('sender-capability-section');
    
    if (roleSelect) {
        roleSelect.addEventListener('change', function() {
            if (messagingRoles.includes(this.value)) {
                capabilitySection.style.display = '';
            } else {
                capabilitySection.style.display = 'none';
            }
        });
    }
    
    document.getElementById('btn-confirm-suspend-user')?.addEventListener('click', function() {
        const reason = document.getElementById('suspend-user-reason').value;
        console.log('[Audit] User suspended', { reason, timestamp: new Date().toISOString() });
        bootstrap.Modal.getInstance(document.getElementById('suspendUserModal')).hide();
        showToast('User suspended successfully', 'warning');
        setTimeout(() => location.reload(), 1500);
    });
    
    document.getElementById('btn-reactivate-user')?.addEventListener('click', function() {
        console.log('[Audit] User reactivated', { timestamp: new Date().toISOString() });
        showToast('User reactivated successfully', 'success');
        setTimeout(() => location.reload(), 1500);
    });
    
    document.getElementById('btn-confirm-archive-user')?.addEventListener('click', function() {
        const reason = document.getElementById('archive-user-reason').value;
        if (!reason.trim()) {
            alert('Please provide a reason for archiving.');
            return;
        }
        console.log('[Audit] User archived', { reason, timestamp: new Date().toISOString() });
        bootstrap.Modal.getInstance(document.getElementById('archiveUserModal')).hide();
        showToast('User archived permanently', 'danger');
        setTimeout(() => location.reload(), 1500);
    });
    
    document.getElementById('btn-resend-invite')?.addEventListener('click', function() {
        console.log('[Audit] Invite resent', { timestamp: new Date().toISOString() });
        showToast('Invitation resent successfully', 'success');
    });
    
    document.getElementById('btn-revoke-invite')?.addEventListener('click', function() {
        if (confirm('Are you sure you want to revoke this invitation?')) {
            console.log('[Audit] Invite revoked', { timestamp: new Date().toISOString() });
            showToast('Invitation revoked', 'warning');
            setTimeout(() => window.location.href = '{{ route("account.sub-accounts.detail", $sub_account["id"]) }}', 1500);
        }
    });
    
    document.getElementById('btn-save-role')?.addEventListener('click', function() {
        const reason = document.getElementById('role-change-reason').value;
        if (!reason.trim()) {
            alert('Please provide a reason for the role change.');
            return;
        }
        const role = document.getElementById('edit-user-role').value;
        const capability = document.getElementById('edit-sender-capability').value;
        console.log('[Audit] Role changed', { role, capability, reason, timestamp: new Date().toISOString() });
        bootstrap.Modal.getInstance(document.getElementById('editRoleModal')).hide();
        showToast('Role and permissions updated', 'success');
        setTimeout(() => location.reload(), 1500);
    });
    
    const inheritSpendCap = document.getElementById('inherit-spend-cap');
    const inheritMessageCap = document.getElementById('inherit-message-cap');
    const editSpendCap = document.getElementById('edit-spend-cap');
    const editMessageCap = document.getElementById('edit-message-cap');
    const spendCapError = document.getElementById('spend-cap-error');
    const messageCapError = document.getElementById('message-cap-error');
    
    inheritSpendCap?.addEventListener('change', function() {
        editSpendCap.disabled = this.checked;
        if (this.checked) {
            editSpendCap.value = '';
            spendCapError.style.display = 'none';
            editSpendCap.classList.remove('is-invalid');
        }
    });
    
    inheritMessageCap?.addEventListener('change', function() {
        editMessageCap.disabled = this.checked;
        if (this.checked) {
            editMessageCap.value = '';
            messageCapError.style.display = 'none';
            editMessageCap.classList.remove('is-invalid');
        }
    });
    
    editSpendCap?.addEventListener('input', function() {
        const value = parseFloat(this.value);
        if (value > SUB_ACCOUNT_SPEND_CAP) {
            this.classList.add('is-invalid');
            spendCapError.style.display = 'block';
        } else {
            this.classList.remove('is-invalid');
            spendCapError.style.display = 'none';
        }
        if (this.value) {
            inheritSpendCap.checked = false;
        }
    });
    
    editMessageCap?.addEventListener('input', function() {
        const value = parseInt(this.value);
        if (value > SUB_ACCOUNT_MESSAGE_CAP) {
            this.classList.add('is-invalid');
            messageCapError.style.display = 'block';
        } else {
            this.classList.remove('is-invalid');
            messageCapError.style.display = 'none';
        }
        if (this.value) {
            inheritMessageCap.checked = false;
        }
    });
    
    document.getElementById('btn-save-limits')?.addEventListener('click', function() {
        const reason = document.getElementById('limits-change-reason').value;
        if (!reason.trim()) {
            alert('Please provide a reason for the limit changes.');
            return;
        }
        
        const spendCap = editSpendCap.value ? parseFloat(editSpendCap.value) : null;
        const messageCap = editMessageCap.value ? parseInt(editMessageCap.value) : null;
        const enforcement = document.getElementById('edit-enforcement-type').value || null;
        
        if (spendCap !== null && spendCap > SUB_ACCOUNT_SPEND_CAP) {
            alert('Spend cap cannot exceed Sub-Account limit of £' + SUB_ACCOUNT_SPEND_CAP.toLocaleString());
            return;
        }
        
        if (messageCap !== null && messageCap > SUB_ACCOUNT_MESSAGE_CAP) {
            alert('Message cap cannot exceed Sub-Account limit of ' + SUB_ACCOUNT_MESSAGE_CAP.toLocaleString());
            return;
        }
        
        const changes = {
            spend_cap: { 
                value: spendCap, 
                inherited: inheritSpendCap.checked,
                sub_account_max: SUB_ACCOUNT_SPEND_CAP
            },
            message_cap: { 
                value: messageCap, 
                inherited: inheritMessageCap.checked,
                sub_account_max: SUB_ACCOUNT_MESSAGE_CAP
            },
            enforcement_type: { 
                value: enforcement, 
                inherited: !enforcement
            },
            reason: reason,
            timestamp: new Date().toISOString()
        };
        
        console.log('[Audit] User limits changed', changes);
        bootstrap.Modal.getInstance(document.getElementById('editLimitsModal')).hide();
        showToast('User limits updated successfully', 'success');
        setTimeout(() => location.reload(), 1500);
    });
    
    function showToast(message, type) {
        const colors = {
            success: '#16a34a',
            warning: '#d97706',
            danger: '#dc2626',
            info: '#886cc0'
        };
        const toast = document.createElement('div');
        toast.style.cssText = `
            position: fixed;
            top: 80px;
            right: 20px;
            background: ${colors[type] || colors.info};
            color: white;
            padding: 0.75rem 1.25rem;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 500;
            z-index: 9999;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            animation: slideIn 0.3s ease;
        `;
        toast.innerHTML = `<i class="fas fa-check-circle me-2"></i>${message}`;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    }
});
</script>
<style>
@keyframes slideIn {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}
</style>
@endpush
