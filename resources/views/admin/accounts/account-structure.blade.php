@extends('layouts.admin')

@section('title', 'Account Structure - ' . ($account->company_name ?? $account_id))

@push('styles')
<style>
.admin-page { padding: 1.5rem; }

.structure-breadcrumb {
    background: transparent;
    padding: 0;
    margin: 0;
}
.structure-breadcrumb a {
    color: #6c757d;
    text-decoration: none;
}
.structure-breadcrumb a:hover {
    color: #1e3a5f;
}
.structure-breadcrumb .active {
    font-weight: 500;
}

.structure-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #e9ecef;
}
.structure-header h4 {
    margin: 0;
    font-weight: 600;
}
.structure-header .account-id {
    font-size: 0.8rem;
    color: #6c757d;
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
    background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%);
    color: #fff;
    border-radius: 0.5rem;
    padding: 0.625rem 1rem;
    margin-bottom: 0;
    position: relative;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
}
.main-account-node.has-users {
    border-radius: 0.5rem 0.5rem 0 0;
}
.main-account-node .account-name {
    font-weight: 600;
    font-size: 1rem;
    margin-bottom: 0.25rem;
}
.main-account-node .account-info {
    font-size: 0.75rem;
    opacity: 0.85;
}

.contextual-btn {
    font-size: 0.7rem;
    padding: 3px 10px;
    border-radius: 4px;
    border: 1px solid rgba(255,255,255,0.5);
    background: rgba(255,255,255,0.15);
    color: #fff;
    cursor: pointer;
    transition: all 0.15s;
}
.contextual-btn:hover {
    background: rgba(255,255,255,0.3);
}

.tree-connector {
    width: 2px;
    height: 1rem;
    background: #e5e7eb;
    margin-left: 2rem;
}

.sub-accounts-container {
    margin-left: 2rem;
    border-left: 2px solid #e5e7eb;
    padding-left: 0;
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
    background: rgba(30, 58, 95, 0.15);
    color: #1e3a5f;
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
    background: rgba(30, 58, 95, 0.12);
    color: #1e3a5f;
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
</style>
@endpush

@section('content')
<div class="admin-page">
    <div class="row page-titles mb-3">
        <ol class="breadcrumb structure-breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.accounts.overview') }}">Accounts</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.accounts.details', $account_id) }}">{{ $account->company_name ?? 'Account' }}</a></li>
            <li class="breadcrumb-item active">Account Structure</li>
        </ol>
    </div>

    <div class="structure-header">
        <div>
            <h4>{{ $account->company_name ?? 'Unknown Account' }}</h4>
            <div class="account-id">{{ $account->account_number ?? $account_id }}</div>
        </div>
        <div>
            <a href="{{ route('admin.accounts.details', $account_id) }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i>Back to Account Details
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Account Hierarchy</h4>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-secondary" id="btn-expand-all">Expand All</button>
                        <button class="btn btn-sm btn-outline-secondary" id="btn-collapse-all">Collapse All</button>
                    </div>
                </div>
                <div class="card-body">
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
                <div class="alert alert-info mb-4">
                    <div class="d-flex align-items-start">
                        <i class="fas fa-info-circle me-3 mt-1"></i>
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
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btn-send-invite">Send Invitation</button>
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
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btn-create-sub-account">Create Sub-Account</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    var accountName = @json($account->company_name ?? 'Unknown Account');
    var accountId = @json($account_id);

    var subAccountsRaw = @json($subAccounts);
    var allUsersRaw = @json($allUsers);

    var subAccountsData = subAccountsRaw.map(function(sa) {
        return {
            id: sa.id,
            name: sa.name,
            accountStatus: sa.sub_account_status || 'live',
            users: []
        };
    });

    var unassignedUsers = [];
    allUsersRaw.forEach(function(u) {
        var mapped = {
            id: u.id,
            sub_account_id: u.sub_account_id,
            name: ((u.first_name || '') + ' ' + (u.last_name || '')).trim(),
            email: u.email,
            role: u.role,
            status: u.status,
            senderCapability: u.sender_capability || null,
            isAccountOwner: u.is_account_owner || false
        };
        if (u.sub_account_id) {
            var targetSa = subAccountsData.find(function(s) { return s.id === u.sub_account_id; });
            if (targetSa) {
                targetSa.users.push(mapped);
            } else {
                unassignedUsers.push(mapped);
            }
        } else {
            unassignedUsers.push(mapped);
        }
    });

    var hierarchyData = {
        mainAccount: { name: accountName },
        subAccounts: subAccountsData,
        mainAccountUsers: unassignedUsers
    };

    renderHierarchy();

    function renderHierarchy() {
        var tree = document.getElementById('hierarchy-tree');
        var html = '';

        var mainUsers = hierarchyData.mainAccountUsers || [];
        html += '<div class="main-account-node' + (mainUsers.length > 0 ? ' has-users' : '') + '">';
        html += '<div>';
        html += '<div class="account-name">' + escapeHtml(hierarchyData.mainAccount.name) + '</div>';
        html += '<div class="account-info">Main Account</div>';
        html += '</div>';
        html += '<div style="display:flex;gap:6px;align-items:center;">';
        html += '<button class="contextual-btn btn-add-user" data-sub-id="" type="button">+ Add User</button>';
        html += '<button class="contextual-btn btn-add-sub-account" type="button">+ Add Sub-Account</button>';
        html += '</div>';

        html += '</div>';

        if (mainUsers.length > 0) {
            html += '<div class="main-account-users" style="background: #fff; border: 1px solid #e9ecef; border-top: none; border-radius: 0 0 0.5rem 0.5rem; padding: 0.5rem 0;">';
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
                    html += '<span class="badge" style="background: #1e3a5f; color: #fff; font-size: 0.7rem; padding: 3px 8px;">Account Owner</span>';
                }
                html += '</div>';
                html += '</div>';
            });
            html += '</div>';
        }

        html += '<div class="tree-connector"></div>';

        html += '<div class="sub-accounts-container">';

        hierarchyData.subAccounts.forEach(function(subAccount, index) {
            html += '<div class="sub-account-branch">';
            html += '<div class="sub-account-node" data-sub-id="' + subAccount.id + '">';

            html += '<div class="sub-account-header" data-sub-id="' + subAccount.id + '">';
            html += '<div class="d-flex align-items-center gap-2 flex-grow-1">';
            html += '<div>';
            html += '<div class="sub-name">' + escapeHtml(subAccount.name) + '</div>';
            html += '<div class="sub-meta"><span class="account-status-pill ' + subAccount.accountStatus + ' me-1">' + capitalise(subAccount.accountStatus) + '</span>' + subAccount.users.length + ' user' + (subAccount.users.length !== 1 ? 's' : '') + '</div>';
            html += '</div>';
            html += '</div>';
            html += '<div class="d-flex align-items-center gap-2">';
            html += '<button class="contextual-btn btn-add-user" data-sub-id="' + subAccount.id + '" type="button" style="border-color: #1e3a5f; color: #1e3a5f; background: transparent;">+ Add User</button>';
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

        updateStats();
        bindToggleEvents();
    }

    function updateStats() {
        var totalUsers = 0;
        var activeUsers = 0;
        var pendingInvites = 0;

        var mainUsers = hierarchyData.mainAccountUsers || [];
        totalUsers += mainUsers.length;
        mainUsers.forEach(function(user) {
            if (user.status === 'active') activeUsers++;
            if (user.status === 'invited') pendingInvites++;
        });

        hierarchyData.subAccounts.forEach(function(sub) {
            totalUsers += sub.users.length;
            sub.users.forEach(function(user) {
                if (user.status === 'active') activeUsers++;
                if (user.status === 'invited') pendingInvites++;
            });
        });

        document.getElementById('stat-sub-accounts').textContent = hierarchyData.subAccounts.length;
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
        select.innerHTML = '<option value="">Main Account (no sub-account)</option>';
        hierarchyData.subAccounts.forEach(function(sub) {
            var selected = String(sub.id) === String(preSelectedSubId) ? ' selected' : '';
            select.innerHTML += '<option value="' + sub.id + '"' + selected + '>' + escapeHtml(sub.name) + '</option>';
        });

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
        if (!str) return '';
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    function escapeHtml(str) {
        var div = document.createElement('div');
        div.textContent = str || '';
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

    document.getElementById('btn-create-sub-account').addEventListener('click', function() {
        var name = document.getElementById('sub-account-name').value.trim();
        if (!name) {
            document.getElementById('sub-account-name').classList.add('is-invalid');
            document.getElementById('sub-account-name').focus();
            return;
        }
        alert('Sub-account creation from admin console is coming soon. Use the customer portal or database tools to create sub-accounts.');
    });

    document.getElementById('btn-send-invite').addEventListener('click', function() {
        var email = document.getElementById('invite-email').value.trim();
        if (!email) {
            alert('Please enter an email address');
            return;
        }
        alert('User invitation from admin console is coming soon. Use the customer portal to send invitations.');
    });
})();
</script>
@endpush
