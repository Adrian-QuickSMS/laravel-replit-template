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
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Invite User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="invite-user-form">
                    <div class="mb-3">
                        <label class="form-label">Email Address <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="invite-email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">First Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="invite-first-name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Last Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="invite-last-name" required>
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
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btn-send-invite">Send Invite</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addSubAccountModal" tabindex="-1">
    <div class="modal-dialog">
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
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description <span class="text-muted">(Optional)</span></label>
                        <textarea class="form-control" id="sub-account-description" rows="2" placeholder="Brief description of this sub-account"></textarea>
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
document.addEventListener('DOMContentLoaded', function() {
    var currentUserRole = 'admin';
    var isMainAccountAdmin = true;
    
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
                    html += '<div class="user-row" data-user-id="' + user.id + '">';
                    html += '<div class="user-info">';
                    html += '<span class="user-name">' + escapeHtml(user.name) + '</span>';
                    html += '<span class="user-email">' + escapeHtml(user.email) + '</span>';
                    html += '</div>';
                    html += '<div class="user-pills">';
                    html += '<span class="role-pill ' + user.role + '">' + formatRole(user.role) + '</span>';
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
            'auditor': 'Auditor'
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
        
        var newSubAccount = {
            id: 'sub-' + Date.now(),
            name: name,
            description: description,
            users: []
        };
        
        hierarchyData.subAccounts.push(newSubAccount);
        
        bootstrap.Modal.getInstance(document.getElementById('addSubAccountModal')).hide();
        
        renderHierarchy();
        
        console.log('[Audit] Sub-Account created:', {
            id: newSubAccount.id,
            name: name,
            createdBy: 'current-user',
            timestamp: new Date().toISOString()
        });
    });
    
    document.getElementById('btn-send-invite').addEventListener('click', function() {
        var email = document.getElementById('invite-email').value.trim();
        var firstName = document.getElementById('invite-first-name').value.trim();
        var lastName = document.getElementById('invite-last-name').value.trim();
        var subAccountId = document.getElementById('invite-sub-account').value;
        var role = document.getElementById('invite-role').value;
        
        if (!email || !firstName || !lastName || !subAccountId || !role) {
            alert('Please fill in all required fields');
            return;
        }
        
        var subAccount = hierarchyData.subAccounts.find(function(s) { return s.id === subAccountId; });
        if (subAccount) {
            subAccount.users.push({
                id: 'user-' + Date.now(),
                name: firstName + ' ' + lastName,
                email: email,
                role: role,
                status: 'invited'
            });
        }
        
        bootstrap.Modal.getInstance(document.getElementById('inviteUserModal')).hide();
        document.getElementById('invite-user-form').reset();
        
        renderHierarchy();
        
        console.log('[Audit] User invited:', {
            email: email,
            subAccountId: subAccountId,
            role: role,
            invitedBy: 'current-user',
            timestamp: new Date().toISOString()
        });
    });
    
    renderHierarchy();
});
</script>
@endpush
