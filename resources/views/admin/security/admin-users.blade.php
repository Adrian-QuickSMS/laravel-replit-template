{{-- Admin Users Management - INTERNAL ONLY --}}
{{-- This module is restricted to Super Admin and Internal Support roles --}}
{{-- Never accessible from customer portal --}}
@extends('layouts.admin')

@section('title', 'Admin Users - Admin')

@push('styles')
<style>
:root {
    --admin-primary: #1e3a5f;
    --admin-secondary: #2d5a87;
    --admin-accent: #4a90d9;
}
.admin-users-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}
.admin-users-header h2 {
    margin: 0;
    font-weight: 600;
    color: #1e3a5f;
}
.admin-users-header p {
    margin: 0;
    color: #6c757d;
}
.table-container {
    background: #fff;
    border-radius: 8px;
    border: 1px solid #e0e6ed;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
}
.api-table {
    width: 100%;
    margin: 0;
    table-layout: fixed;
}
.api-table thead th:first-child { width: 18%; }
.api-table thead th:nth-child(2) { width: 20%; }
.api-table thead th:nth-child(3) { width: 14%; }
.api-table thead th:nth-child(4) { width: 12%; }
.api-table thead th:nth-child(5) { width: 10%; }
.api-table thead th:nth-child(6) { width: 16%; }
.api-table thead th:last-child { width: 10%; text-align: center; }
.api-table thead th {
    background: #f8f9fa;
    padding: 0.75rem 0.5rem;
    font-weight: 600;
    font-size: 0.8rem;
    color: #495057;
    border-bottom: 1px solid #e9ecef;
    cursor: pointer;
    white-space: nowrap;
    user-select: none;
}
.api-table thead th:hover {
    background: #e9ecef;
}
.api-table thead th i.sort-icon {
    margin-left: 0.25rem;
    opacity: 0.5;
}
.api-table thead th.sorted i.sort-icon {
    opacity: 1;
    color: #1e3a5f;
}
.api-table tbody tr {
    border-bottom: 1px solid #e9ecef;
}
.api-table tbody tr:last-child {
    border-bottom: none;
}
.api-table tbody tr:hover {
    background: #f8f9fa;
}
.api-table tbody td {
    padding: 0.75rem 0.5rem;
    vertical-align: middle;
    font-size: 0.85rem;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.user-name {
    font-weight: 500;
    color: #343a40;
}
.user-email {
    font-size: 0.75rem;
    color: #6c757d;
}
.badge-role {
    font-size: 0.7rem;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
}
.badge-super-admin {
    background: rgba(30, 58, 95, 0.15);
    color: #1e3a5f;
}
.badge-internal-support {
    background: rgba(74, 144, 217, 0.15);
    color: #4a90d9;
}
.badge-status {
    font-size: 0.7rem;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
}
.badge-active {
    background: rgba(28, 187, 140, 0.15);
    color: #1cbb8c;
}
.badge-suspended {
    background: rgba(220, 53, 69, 0.15);
    color: #dc3545;
}
.mfa-indicator {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    font-size: 0.75rem;
}
.mfa-enabled {
    color: #1cbb8c;
}
.mfa-disabled {
    color: #dc3545;
}
.action-dots-btn {
    background: none;
    border: none;
    padding: 0.25rem 0.5rem;
    color: #6c757d;
    cursor: pointer;
    border-radius: 4px;
}
.action-dots-btn:hover {
    background: #e9ecef;
    color: #1e3a5f;
}
.stats-row {
    display: flex;
    gap: 1rem;
    margin-bottom: 1.5rem;
}
.stat-card {
    flex: 1;
    background: #fff;
    border-radius: 8px;
    border: 1px solid #e0e6ed;
    padding: 1rem 1.25rem;
    display: flex;
    align-items: center;
    gap: 1rem;
}
.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
}
.stat-icon.primary {
    background: rgba(30, 58, 95, 0.1);
    color: #1e3a5f;
}
.stat-icon.success {
    background: rgba(28, 187, 140, 0.1);
    color: #1cbb8c;
}
.stat-icon.warning {
    background: rgba(255, 193, 7, 0.1);
    color: #ffc107;
}
.stat-icon.danger {
    background: rgba(220, 53, 69, 0.1);
    color: #dc3545;
}
.stat-content h3 {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 600;
    color: #343a40;
}
.stat-content p {
    margin: 0;
    font-size: 0.8rem;
    color: #6c757d;
}
.search-filter-card {
    background: #fff;
    border: 1px solid #e0e6ed;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    margin-bottom: 0.75rem;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row page-titles">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
            <li class="breadcrumb-item"><a href="#">Security & Compliance</a></li>
            <li class="breadcrumb-item active">Admin Users</li>
        </ol>
    </div>

    <div class="admin-users-header">
        <div>
            <h2>Admin Users</h2>
            <p>Manage internal QuickSMS administrator accounts</p>
        </div>
        <button class="btn" style="background: #1e3a5f; color: white;" onclick="showAddUserModal()">
            <i class="fas fa-plus me-1"></i> Add Admin User
        </button>
    </div>

    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-icon primary">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <h3>{{ count($adminUsers) }}</h3>
                <p>Total Admin Users</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon success">
                <i class="fas fa-user-check"></i>
            </div>
            <div class="stat-content">
                <h3>{{ collect($adminUsers)->where('status', 'Active')->count() }}</h3>
                <p>Active Users</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon warning">
                <i class="fas fa-user-shield"></i>
            </div>
            <div class="stat-content">
                <h3>{{ collect($adminUsers)->where('role', 'Super Admin')->count() }}</h3>
                <p>Super Admins</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon danger">
                <i class="fas fa-user-slash"></i>
            </div>
            <div class="stat-content">
                <h3>{{ collect($adminUsers)->where('status', 'Suspended')->count() }}</h3>
                <p>Suspended</p>
            </div>
        </div>
    </div>

    <div class="search-filter-card">
        <div class="card-body py-2 px-3">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2 flex-grow-1">
                    <div class="input-group" style="width: 320px;">
                        <span class="input-group-text bg-transparent border-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" class="form-control border-0 ps-0" id="searchInput" placeholder="Search by name, email, or role...">
                    </div>
                </div>
                <button type="button" class="btn btn-sm" data-bs-toggle="collapse" data-bs-target="#filtersPanel" style="border: 1px solid #6f42c1; color: #6f42c1; background: transparent;">
                    <i class="fas fa-filter me-1"></i> Filters
                </button>
            </div>
        </div>
        <div class="collapse" id="filtersPanel">
            <div class="card-body border-top pt-3">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Role</label>
                        <select class="form-select form-select-sm" id="filterRole">
                            <option value="">All Roles</option>
                            <option value="Super Admin">Super Admin</option>
                            <option value="Internal Support">Internal Support</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Status</label>
                        <select class="form-select form-select-sm" id="filterStatus">
                            <option value="">All Statuses</option>
                            <option value="Active">Active</option>
                            <option value="Suspended">Suspended</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Department</label>
                        <select class="form-select form-select-sm" id="filterDepartment">
                            <option value="">All Departments</option>
                            <option value="Engineering">Engineering</option>
                            <option value="Operations">Operations</option>
                            <option value="Customer Success">Customer Success</option>
                            <option value="Technical Support">Technical Support</option>
                            <option value="Security">Security</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end gap-2">
                        <button class="btn btn-sm" style="background: #1e3a5f; color: white;" onclick="applyFilters()">
                            <i class="fas fa-check me-1"></i> Apply
                        </button>
                        <button class="btn btn-outline-secondary btn-sm" onclick="resetFilters()">
                            <i class="fas fa-undo me-1"></i> Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="table-container">
        <div class="table-responsive">
            <table class="table api-table mb-0">
                <thead>
                    <tr>
                        <th data-sort="name">Name <i class="fas fa-sort sort-icon"></i></th>
                        <th data-sort="email">Email <i class="fas fa-sort sort-icon"></i></th>
                        <th data-sort="role">Role <i class="fas fa-sort sort-icon"></i></th>
                        <th data-sort="department">Department <i class="fas fa-sort sort-icon"></i></th>
                        <th data-sort="status">Status <i class="fas fa-sort sort-icon"></i></th>
                        <th data-sort="lastLogin">Last Login <i class="fas fa-sort sort-icon"></i></th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="adminUsersTableBody">
                    @foreach($adminUsers as $user)
                    <tr data-id="{{ $user['id'] }}" 
                        data-name="{{ strtolower($user['name']) }}"
                        data-email="{{ strtolower($user['email']) }}"
                        data-role="{{ $user['role'] }}"
                        data-department="{{ $user['department'] }}"
                        data-status="{{ $user['status'] }}">
                        <td>
                            <div class="user-name">{{ $user['name'] }}</div>
                            <div class="user-email">{{ $user['id'] }}</div>
                        </td>
                        <td>
                            <span title="{{ $user['email'] }}">{{ $user['email'] }}</span>
                        </td>
                        <td>
                            <span class="badge badge-role {{ $user['role'] === 'Super Admin' ? 'badge-super-admin' : 'badge-internal-support' }}">
                                {{ $user['role'] }}
                            </span>
                        </td>
                        <td>{{ $user['department'] }}</td>
                        <td>
                            <span class="badge badge-status {{ $user['status'] === 'Active' ? 'badge-active' : 'badge-suspended' }}">
                                {{ $user['status'] }}
                            </span>
                        </td>
                        <td>
                            <div>{{ \Carbon\Carbon::parse($user['last_login'])->format('d-m-Y') }}</div>
                            <div class="text-muted small">{{ \Carbon\Carbon::parse($user['last_login'])->format('H:i') }}</div>
                        </td>
                        <td class="text-center">
                            <div class="dropdown">
                                <button class="action-dots-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                    <li><a class="dropdown-item" href="#" onclick="viewUserDetails('{{ $user['id'] }}')"><i class="fas fa-eye me-2"></i>View Details</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="editUser('{{ $user['id'] }}')"><i class="fas fa-edit me-2"></i>Edit User</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    @if($user['status'] === 'Active')
                                    <li><a class="dropdown-item text-warning" href="#" onclick="suspendUser('{{ $user['id'] }}')"><i class="fas fa-user-slash me-2"></i>Suspend User</a></li>
                                    @else
                                    <li><a class="dropdown-item text-success" href="#" onclick="reactivateUser('{{ $user['id'] }}')"><i class="fas fa-user-check me-2"></i>Reactivate User</a></li>
                                    @endif
                                    <li><a class="dropdown-item" href="#" onclick="resetMfa('{{ $user['id'] }}')"><i class="fas fa-key me-2"></i>Reset MFA</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="#" onclick="deleteUser('{{ $user['id'] }}')"><i class="fas fa-trash me-2"></i>Delete User</a></li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div id="emptyState" class="text-center py-5 d-none">
        <div class="text-muted">
            <i class="fas fa-users fa-3x mb-3 d-block"></i>
            <h6>No admin users found</h6>
            <p class="mb-0 small">Try adjusting your search or filter criteria.</p>
        </div>
    </div>
</div>

<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Admin User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addUserForm">
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="newUserName" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="newUserEmail" required>
                        <div class="form-text">Must be a @quicksms.co.uk email address</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select class="form-select" id="newUserRole" required>
                            <option value="">Select Role</option>
                            <option value="Super Admin">Super Admin</option>
                            <option value="Internal Support">Internal Support</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Department</label>
                        <select class="form-select" id="newUserDepartment" required>
                            <option value="">Select Department</option>
                            <option value="Engineering">Engineering</option>
                            <option value="Operations">Operations</option>
                            <option value="Customer Success">Customer Success</option>
                            <option value="Technical Support">Technical Support</option>
                            <option value="Security">Security</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn" style="background: #1e3a5f; color: white;" onclick="createUser()">
                    <i class="fas fa-plus me-1"></i> Create User
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('[AdminUsers] Module initialized - Internal Only');
    
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            filterTable();
        });
    }

    document.querySelectorAll('.api-table thead th[data-sort]').forEach(function(th) {
        th.addEventListener('click', function() {
            const sortKey = this.dataset.sort;
            sortTable(sortKey, this);
        });
    });
});

function filterTable() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const roleFilter = document.getElementById('filterRole').value;
    const statusFilter = document.getElementById('filterStatus').value;
    const deptFilter = document.getElementById('filterDepartment').value;
    
    const rows = document.querySelectorAll('#adminUsersTableBody tr');
    let visibleCount = 0;
    
    rows.forEach(function(row) {
        const name = row.dataset.name || '';
        const email = row.dataset.email || '';
        const role = row.dataset.role || '';
        const status = row.dataset.status || '';
        const dept = row.dataset.department || '';
        
        const matchesSearch = !searchTerm || 
            name.includes(searchTerm) || 
            email.includes(searchTerm) ||
            role.toLowerCase().includes(searchTerm);
        
        const matchesRole = !roleFilter || role === roleFilter;
        const matchesStatus = !statusFilter || status === statusFilter;
        const matchesDept = !deptFilter || dept === deptFilter;
        
        if (matchesSearch && matchesRole && matchesStatus && matchesDept) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
    
    document.getElementById('emptyState').classList.toggle('d-none', visibleCount > 0);
    document.querySelector('.table-container').classList.toggle('d-none', visibleCount === 0);
}

function applyFilters() {
    filterTable();
}

function resetFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('filterRole').value = '';
    document.getElementById('filterStatus').value = '';
    document.getElementById('filterDepartment').value = '';
    filterTable();
}

function sortTable(key, th) {
    console.log('[AdminUsers] Sorting by:', key);
}

function showAddUserModal() {
    const modal = new bootstrap.Modal(document.getElementById('addUserModal'));
    modal.show();
}

function createUser() {
    const name = document.getElementById('newUserName').value;
    const email = document.getElementById('newUserEmail').value;
    const role = document.getElementById('newUserRole').value;
    const dept = document.getElementById('newUserDepartment').value;
    
    if (!name || !email || !role || !dept) {
        alert('Please fill in all fields');
        return;
    }
    
    if (!email.endsWith('@quicksms.co.uk')) {
        alert('Email must be a @quicksms.co.uk address');
        return;
    }
    
    console.log('[AdminUsers] Creating user:', { name, email, role, dept });
    bootstrap.Modal.getInstance(document.getElementById('addUserModal')).hide();
    showToast('Admin user created successfully', 'success');
}

function viewUserDetails(userId) {
    console.log('[AdminUsers] View details:', userId);
}

function editUser(userId) {
    console.log('[AdminUsers] Edit user:', userId);
}

function suspendUser(userId) {
    if (confirm('Are you sure you want to suspend this admin user?')) {
        console.log('[AdminUsers] Suspend user:', userId);
        showToast('User suspended', 'warning');
    }
}

function reactivateUser(userId) {
    console.log('[AdminUsers] Reactivate user:', userId);
    showToast('User reactivated', 'success');
}

function resetMfa(userId) {
    if (confirm('Are you sure you want to reset MFA for this user?')) {
        console.log('[AdminUsers] Reset MFA:', userId);
        showToast('MFA reset email sent', 'info');
    }
}

function deleteUser(userId) {
    if (confirm('Are you sure you want to permanently delete this admin user? This action cannot be undone.')) {
        console.log('[AdminUsers] Delete user:', userId);
        showToast('User deleted', 'danger');
    }
}

function showToast(message, type) {
    var toastContainer = document.getElementById('toastContainer');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toastContainer';
        toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        toastContainer.style.zIndex = '9999';
        document.body.appendChild(toastContainer);
    }
    
    var bgClass = 'bg-primary';
    if (type === 'success') bgClass = 'bg-success';
    if (type === 'error' || type === 'danger') bgClass = 'bg-danger';
    if (type === 'warning') bgClass = 'bg-warning text-dark';
    if (type === 'info') bgClass = 'bg-info';
    
    var toastId = 'toast-' + Date.now();
    var toastHtml = '<div id="' + toastId + '" class="toast ' + bgClass + ' text-white" role="alert">' +
        '<div class="toast-body d-flex justify-content-between align-items-center">' +
        '<span>' + message + '</span>' +
        '<button type="button" class="btn-close btn-close-white ms-2" data-bs-dismiss="toast"></button>' +
        '</div></div>';
    
    toastContainer.insertAdjacentHTML('beforeend', toastHtml);
    
    var toastEl = document.getElementById(toastId);
    var toast = new bootstrap.Toast(toastEl, { delay: 4000 });
    toast.show();
    
    toastEl.addEventListener('hidden.bs.toast', function() {
        toastEl.remove();
    });
}
</script>
@endpush
