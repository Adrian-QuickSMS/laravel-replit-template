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
.api-table thead th:nth-child(1) { width: 12%; }
.api-table thead th:nth-child(2) { width: 16%; }
.api-table thead th:nth-child(3) { width: 9%; }
.api-table thead th:nth-child(4) { width: 9%; }
.api-table thead th:nth-child(5) { width: 10%; }
.api-table thead th:nth-child(6) { width: 10%; }
.api-table thead th:nth-child(7) { width: 10%; }
.api-table thead th:nth-child(8) { width: 8%; }
.api-table thead th:nth-child(9) { width: 10%; }
.api-table thead th:last-child { width: 6%; text-align: center; }
.api-table thead th {
    background: #f8f9fa;
    padding: 0.6rem 0.4rem;
    font-weight: 600;
    font-size: 0.75rem;
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
    margin-left: 0.2rem;
    opacity: 0.5;
    font-size: 0.65rem;
}
.api-table thead th.sorted i.sort-icon {
    opacity: 1;
    color: #1e3a5f;
}
.api-table tbody tr {
    border-bottom: 1px solid #e9ecef;
    cursor: pointer;
}
.api-table tbody tr:last-child {
    border-bottom: none;
}
.api-table tbody tr:hover {
    background: #f8f9fa;
}
.api-table tbody td {
    padding: 0.5rem 0.4rem;
    vertical-align: middle;
    font-size: 0.8rem;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.user-name {
    font-weight: 500;
    color: #343a40;
}
.user-email {
    font-size: 0.7rem;
    color: #6c757d;
}
.badge-pill {
    font-size: 0.65rem;
    padding: 0.2rem 0.45rem;
    border-radius: 10px;
    font-weight: 500;
}
.badge-active { background: rgba(28, 187, 140, 0.15); color: #1cbb8c; }
.badge-invited { background: rgba(74, 144, 217, 0.15); color: #4a90d9; }
.badge-suspended { background: rgba(255, 193, 7, 0.15); color: #d39e00; }
.badge-archived { background: rgba(108, 117, 125, 0.15); color: #6c757d; }
.badge-enrolled { background: rgba(28, 187, 140, 0.15); color: #1cbb8c; }
.badge-not-enrolled { background: rgba(220, 53, 69, 0.15); color: #dc3545; }
.failed-logins-warning { color: #dc3545; font-weight: 600; }
.action-dots-btn {
    background: none;
    border: none;
    padding: 0.2rem 0.4rem;
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
    padding: 0.75rem 1rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}
.stat-icon {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
}
.stat-icon.primary { background: rgba(30, 58, 95, 0.1); color: #1e3a5f; }
.stat-icon.success { background: rgba(28, 187, 140, 0.1); color: #1cbb8c; }
.stat-icon.warning { background: rgba(255, 193, 7, 0.1); color: #ffc107; }
.stat-icon.danger { background: rgba(220, 53, 69, 0.1); color: #dc3545; }
.stat-content h3 {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 600;
    color: #343a40;
}
.stat-content p {
    margin: 0;
    font-size: 0.75rem;
    color: #6c757d;
}
.search-filter-card {
    background: #fff;
    border: 1px solid #e0e6ed;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    margin-bottom: 0.75rem;
}
.pagination-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 1rem;
    background: #fff;
    border-top: 1px solid #e9ecef;
}
.pagination-info {
    font-size: 0.8rem;
    color: #6c757d;
}
.pagination .page-link {
    font-size: 0.8rem;
    padding: 0.35rem 0.65rem;
    color: #1e3a5f;
}
.pagination .page-item.active .page-link {
    background-color: #1e3a5f;
    border-color: #1e3a5f;
}
.user-detail-panel {
    position: fixed;
    top: 0;
    right: -500px;
    width: 480px;
    height: 100vh;
    background: #fff;
    box-shadow: -4px 0 20px rgba(0,0,0,0.15);
    z-index: 1050;
    transition: right 0.3s ease;
    overflow-y: auto;
}
.user-detail-panel.open {
    right: 0;
}
.panel-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.3);
    z-index: 1049;
    display: none;
}
.panel-overlay.show {
    display: block;
}
.panel-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #e9ecef;
    background: #f8f9fa;
}
.panel-header h5 {
    margin: 0;
    font-weight: 600;
    color: #1e3a5f;
}
.panel-body {
    padding: 1.25rem;
}
.detail-section {
    margin-bottom: 1.5rem;
}
.detail-section h6 {
    font-size: 0.75rem;
    font-weight: 600;
    color: #6c757d;
    text-transform: uppercase;
    margin-bottom: 0.75rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #e9ecef;
}
.detail-row {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    font-size: 0.85rem;
}
.detail-row .label {
    color: #6c757d;
}
.detail-row .value {
    font-weight: 500;
    color: #343a40;
}
.panel-actions {
    display: flex;
    gap: 0.5rem;
    padding: 1rem 1.25rem;
    border-top: 1px solid #e9ecef;
    background: #f8f9fa;
}
</style>
@endpush

@php
$totalUsers = count($adminUsers);
$activeUsers = collect($adminUsers)->where('status', 'Active')->count();
$suspendedUsers = collect($adminUsers)->whereIn('status', ['Suspended', 'Archived'])->count();
$invitedUsers = collect($adminUsers)->where('status', 'Invited')->count();
@endphp

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
        <button class="btn" style="background: #1e3a5f; color: white;" onclick="showInviteModal()">
            <i class="fas fa-envelope me-1"></i> Invite Admin User
        </button>
    </div>

    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-icon primary"><i class="fas fa-users"></i></div>
            <div class="stat-content"><h3>{{ $totalUsers }}</h3><p>Total Users</p></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon success"><i class="fas fa-user-check"></i></div>
            <div class="stat-content"><h3>{{ $activeUsers }}</h3><p>Active</p></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon warning"><i class="fas fa-envelope"></i></div>
            <div class="stat-content"><h3>{{ $invitedUsers }}</h3><p>Invited</p></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon danger"><i class="fas fa-user-slash"></i></div>
            <div class="stat-content"><h3>{{ $suspendedUsers }}</h3><p>Suspended/Archived</p></div>
        </div>
    </div>

    <div class="search-filter-card">
        <div class="card-body py-2 px-3">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2 flex-grow-1">
                    <div class="input-group" style="width: 320px;">
                        <span class="input-group-text bg-transparent border-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" class="form-control border-0 ps-0" id="searchInput" placeholder="Search by name or email...">
                    </div>
                    <span class="text-muted small" id="resultCount">Showing {{ $totalUsers }} of {{ $totalUsers }} users</span>
                </div>
                <button type="button" class="btn btn-sm" data-bs-toggle="collapse" data-bs-target="#filtersPanel" style="border: 1px solid #6f42c1; color: #6f42c1; background: transparent;">
                    <i class="fas fa-filter me-1"></i> Filters
                </button>
            </div>
        </div>
        <div class="collapse" id="filtersPanel">
            <div class="card-body border-top pt-3">
                <div class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label small fw-bold">Status</label>
                        <select class="form-select form-select-sm" id="filterStatus">
                            <option value="">All</option>
                            <option value="Invited">Invited</option>
                            <option value="Active">Active</option>
                            <option value="Suspended">Suspended</option>
                            <option value="Archived">Archived</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold">MFA Status</label>
                        <select class="form-select form-select-sm" id="filterMfaStatus">
                            <option value="">All</option>
                            <option value="Enrolled">Enrolled</option>
                            <option value="Not Enrolled">Not Enrolled</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold">MFA Method</label>
                        <select class="form-select form-select-sm" id="filterMfaMethod">
                            <option value="">All</option>
                            <option value="Authenticator">Authenticator</option>
                            <option value="SMS">SMS</option>
                            <option value="Both">Both</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold">Role</label>
                        <select class="form-select form-select-sm" id="filterRole">
                            <option value="">All</option>
                            <option value="Super Admin">Super Admin</option>
                            <option value="Internal Support">Internal Support</option>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end gap-2">
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
                        <th data-sort="name">User Name <i class="fas fa-sort sort-icon"></i></th>
                        <th data-sort="email">Email Address <i class="fas fa-sort sort-icon"></i></th>
                        <th data-sort="status">Status <i class="fas fa-sort sort-icon"></i></th>
                        <th data-sort="mfaStatus">MFA Status <i class="fas fa-sort sort-icon"></i></th>
                        <th data-sort="mfaMethod">MFA Method <i class="fas fa-sort sort-icon"></i></th>
                        <th data-sort="lastLogin">Last Login <i class="fas fa-sort sort-icon"></i></th>
                        <th data-sort="lastActivity">Last Activity <i class="fas fa-sort sort-icon"></i></th>
                        <th data-sort="failedLogins">Failed (24h) <i class="fas fa-sort sort-icon"></i></th>
                        <th data-sort="created">Created <i class="fas fa-sort sort-icon"></i></th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="adminUsersTableBody">
                    @foreach($adminUsers as $user)
                    <tr class="user-row" 
                        data-id="{{ $user['id'] }}" 
                        data-user="{{ json_encode($user) }}"
                        onclick="openUserDetail('{{ $user['id'] }}')">
                        <td onclick="event.stopPropagation(); openUserDetail('{{ $user['id'] }}')">
                            <div class="user-name">{{ $user['name'] }}</div>
                        </td>
                        <td title="{{ $user['email'] }}">{{ $user['email'] }}</td>
                        <td>
                            @php
                                $statusClass = match($user['status']) {
                                    'Active' => 'badge-active',
                                    'Invited' => 'badge-invited',
                                    'Suspended' => 'badge-suspended',
                                    'Archived' => 'badge-archived',
                                    default => 'badge-archived'
                                };
                            @endphp
                            <span class="badge-pill {{ $statusClass }}">{{ $user['status'] }}</span>
                        </td>
                        <td>
                            <span class="badge-pill {{ $user['mfa_status'] === 'Enrolled' ? 'badge-enrolled' : 'badge-not-enrolled' }}">
                                {{ $user['mfa_status'] }}
                            </span>
                        </td>
                        <td>{{ $user['mfa_method'] ?? '-' }}</td>
                        <td>
                            @if($user['last_login'])
                                {{ \Carbon\Carbon::parse($user['last_login'])->format('d-m-Y H:i') }}
                            @else
                                <span class="text-muted">Never</span>
                            @endif
                        </td>
                        <td>
                            @if($user['last_activity'])
                                {{ \Carbon\Carbon::parse($user['last_activity'])->format('d-m-Y H:i') }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($user['failed_logins_24h'] > 0)
                                <span class="failed-logins-warning">{{ $user['failed_logins_24h'] }}</span>
                            @else
                                0
                            @endif
                        </td>
                        <td>{{ \Carbon\Carbon::parse($user['created_at'])->format('d-m-Y') }}</td>
                        <td class="text-center" onclick="event.stopPropagation()">
                            <div class="dropdown">
                                <button class="action-dots-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                    <li><a class="dropdown-item" href="#" onclick="openUserDetail('{{ $user['id'] }}')"><i class="fas fa-eye me-2"></i>View Details</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="editUser('{{ $user['id'] }}')"><i class="fas fa-edit me-2"></i>Edit User</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    @if($user['status'] === 'Active')
                                    <li><a class="dropdown-item text-warning" href="#" onclick="suspendUser('{{ $user['id'] }}')"><i class="fas fa-user-slash me-2"></i>Suspend</a></li>
                                    @elseif($user['status'] === 'Suspended')
                                    <li><a class="dropdown-item text-success" href="#" onclick="reactivateUser('{{ $user['id'] }}')"><i class="fas fa-user-check me-2"></i>Reactivate</a></li>
                                    @elseif($user['status'] === 'Invited')
                                    <li><a class="dropdown-item" href="#" onclick="resendInvite('{{ $user['id'] }}')"><i class="fas fa-paper-plane me-2"></i>Resend Invite</a></li>
                                    @endif
                                    <li><a class="dropdown-item" href="#" onclick="resetMfa('{{ $user['id'] }}')"><i class="fas fa-key me-2"></i>Reset MFA</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="#" onclick="archiveUser('{{ $user['id'] }}')"><i class="fas fa-archive me-2"></i>Archive</a></li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="pagination-container">
            <div class="pagination-info">
                Showing <span id="showingStart">1</span>-<span id="showingEnd">20</span> of <span id="totalFiltered">{{ $totalUsers }}</span> users
            </div>
            <nav>
                <ul class="pagination pagination-sm mb-0" id="paginationNav">
                </ul>
            </nav>
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

<div class="panel-overlay" id="panelOverlay" onclick="closeUserDetail()"></div>
<div class="user-detail-panel" id="userDetailPanel">
    <div class="panel-header">
        <h5><i class="fas fa-user me-2"></i>User Details</h5>
        <button type="button" class="btn-close" onclick="closeUserDetail()"></button>
    </div>
    <div class="panel-body" id="userDetailBody">
    </div>
    <div class="panel-actions" id="userDetailActions">
    </div>
</div>

<div class="modal fade" id="inviteUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: #f8f9fa; border-bottom: 1px solid #e9ecef;">
                <h5 class="modal-title" style="color: #1e3a5f;"><i class="fas fa-envelope me-2"></i>Invite Admin User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="inviteUserForm" novalidate>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="inviteFirstName" required>
                            <div class="invalid-feedback">First name is required</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="inviteLastName" required>
                            <div class="invalid-feedback">Last name is required</div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email Address <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="inviteEmail" required>
                        <div class="invalid-feedback" id="emailError">Valid email is required</div>
                        <div class="form-text">Must be a @quicksms.co.uk email address</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Role <span class="text-danger">*</span></label>
                            <select class="form-select" id="inviteRole" required>
                                <option value="">Select Role</option>
                                <option value="Super Admin">Super Admin</option>
                                <option value="Internal Support">Internal Support</option>
                            </select>
                            <div class="invalid-feedback">Please select a role</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Department <span class="text-danger">*</span></label>
                            <select class="form-select" id="inviteDepartment" required>
                                <option value="">Select Department</option>
                                <option value="Engineering">Engineering</option>
                                <option value="Operations">Operations</option>
                                <option value="Customer Success">Customer Success</option>
                                <option value="Technical Support">Technical Support</option>
                                <option value="Security">Security</option>
                            </select>
                            <div class="invalid-feedback">Please select a department</div>
                        </div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Internal Note <span class="text-muted small">(optional)</span></label>
                        <textarea class="form-control" id="inviteNote" rows="2" placeholder="Add any notes about this user..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer" style="background: #f8f9fa; border-top: 1px solid #e9ecef;">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn" style="background: #1e3a5f; color: white;" id="sendInviteBtn" onclick="sendInvite()">
                    <i class="fas fa-paper-plane me-1"></i> Send Invite
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
var allUsers = @json($adminUsers);
var filteredUsers = [...allUsers];
var currentPage = 1;
var rowsPerPage = 20;

document.addEventListener('DOMContentLoaded', function() {
    console.log('[AdminUsers] Module initialized - Internal Only');
    
    document.getElementById('searchInput').addEventListener('keyup', function() {
        filterTable();
    });

    document.querySelectorAll('.api-table thead th[data-sort]').forEach(function(th) {
        th.addEventListener('click', function() {
            sortTable(this.dataset.sort, this);
        });
    });

    renderPagination();
    showPage(1);
});

function filterTable() {
    var searchTerm = document.getElementById('searchInput').value.toLowerCase();
    var statusFilter = document.getElementById('filterStatus').value;
    var mfaStatusFilter = document.getElementById('filterMfaStatus').value;
    var mfaMethodFilter = document.getElementById('filterMfaMethod').value;
    var roleFilter = document.getElementById('filterRole').value;
    
    filteredUsers = allUsers.filter(function(user) {
        var matchesSearch = !searchTerm || 
            user.name.toLowerCase().includes(searchTerm) || 
            user.email.toLowerCase().includes(searchTerm);
        var matchesStatus = !statusFilter || user.status === statusFilter;
        var matchesMfaStatus = !mfaStatusFilter || user.mfa_status === mfaStatusFilter;
        var matchesMfaMethod = !mfaMethodFilter || user.mfa_method === mfaMethodFilter;
        var matchesRole = !roleFilter || user.role === roleFilter;
        
        return matchesSearch && matchesStatus && matchesMfaStatus && matchesMfaMethod && matchesRole;
    });
    
    currentPage = 1;
    renderPagination();
    showPage(1);
    
    document.getElementById('resultCount').textContent = 'Showing ' + filteredUsers.length + ' of ' + allUsers.length + ' users';
    document.getElementById('emptyState').classList.toggle('d-none', filteredUsers.length > 0);
    document.querySelector('.table-container').classList.toggle('d-none', filteredUsers.length === 0);
}

function applyFilters() { filterTable(); }
function resetFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('filterStatus').value = '';
    document.getElementById('filterMfaStatus').value = '';
    document.getElementById('filterMfaMethod').value = '';
    document.getElementById('filterRole').value = '';
    filterTable();
}

function renderPagination() {
    var totalPages = Math.ceil(filteredUsers.length / rowsPerPage);
    var nav = document.getElementById('paginationNav');
    nav.innerHTML = '';
    
    if (totalPages <= 1) return;
    
    var prevLi = document.createElement('li');
    prevLi.className = 'page-item' + (currentPage === 1 ? ' disabled' : '');
    prevLi.innerHTML = '<a class="page-link" href="#" onclick="goToPage(' + (currentPage - 1) + '); return false;">&laquo;</a>';
    nav.appendChild(prevLi);
    
    for (var i = 1; i <= totalPages; i++) {
        var li = document.createElement('li');
        li.className = 'page-item' + (i === currentPage ? ' active' : '');
        li.innerHTML = '<a class="page-link" href="#" onclick="goToPage(' + i + '); return false;">' + i + '</a>';
        nav.appendChild(li);
    }
    
    var nextLi = document.createElement('li');
    nextLi.className = 'page-item' + (currentPage === totalPages ? ' disabled' : '');
    nextLi.innerHTML = '<a class="page-link" href="#" onclick="goToPage(' + (currentPage + 1) + '); return false;">&raquo;</a>';
    nav.appendChild(nextLi);
}

function goToPage(page) {
    var totalPages = Math.ceil(filteredUsers.length / rowsPerPage);
    if (page < 1 || page > totalPages) return;
    currentPage = page;
    showPage(page);
    renderPagination();
}

function showPage(page) {
    var start = (page - 1) * rowsPerPage;
    var end = Math.min(start + rowsPerPage, filteredUsers.length);
    var rows = document.querySelectorAll('#adminUsersTableBody tr');
    
    rows.forEach(function(row) { row.style.display = 'none'; });
    
    var visibleIds = filteredUsers.slice(start, end).map(function(u) { return u.id; });
    rows.forEach(function(row) {
        if (visibleIds.includes(row.dataset.id)) {
            row.style.display = '';
        }
    });
    
    document.getElementById('showingStart').textContent = filteredUsers.length > 0 ? start + 1 : 0;
    document.getElementById('showingEnd').textContent = end;
    document.getElementById('totalFiltered').textContent = filteredUsers.length;
}

function sortTable(key, th) {
    console.log('[AdminUsers] Sorting by:', key);
}

function openUserDetail(userId) {
    var user = allUsers.find(function(u) { return u.id === userId; });
    if (!user) return;
    
    var statusClass = {
        'Active': 'badge-active',
        'Invited': 'badge-invited', 
        'Suspended': 'badge-suspended',
        'Archived': 'badge-archived'
    }[user.status] || 'badge-archived';
    
    var mfaClass = user.mfa_status === 'Enrolled' ? 'badge-enrolled' : 'badge-not-enrolled';
    
    var inviteExpired = false;
    if (user.status === 'Invited' && user.invite_sent_at) {
        var inviteDate = new Date(user.invite_sent_at);
        var now = new Date();
        var daysDiff = (now - inviteDate) / (1000 * 60 * 60 * 24);
        if (daysDiff > 7) inviteExpired = true;
    }
    
    var html = '';
    
    html += '<div class="panel-user-header" style="display: flex; align-items: center; gap: 1rem; padding-bottom: 1rem; border-bottom: 1px solid #e9ecef; margin-bottom: 1rem;">' +
        '<div class="user-avatar" style="width: 56px; height: 56px; border-radius: 50%; background: linear-gradient(135deg, #1e3a5f, #4a90d9); display: flex; align-items: center; justify-content: center; color: white; font-size: 1.25rem; font-weight: 600;">' + getInitials(user.name) + '</div>' +
        '<div class="user-header-info" style="flex: 1;">' +
            '<h5 style="margin: 0; font-weight: 600; color: #343a40;">' + user.name + '</h5>' +
            '<div style="font-size: 0.85rem; color: #6c757d;">' + user.email + '</div>' +
            '<div style="margin-top: 0.35rem;"><span class="badge-pill ' + statusClass + '">' + user.status + '</span></div>' +
        '</div>' +
        '</div>';
    
    if (inviteExpired) {
        html += '<div class="alert alert-warning d-flex align-items-center mb-3" style="background: #fff3cd; border: 1px solid #ffc107; border-radius: 6px; padding: 0.75rem 1rem;">' +
            '<i class="fas fa-exclamation-triangle me-2 text-warning"></i>' +
            '<div><strong>Invite Expired</strong><br><small class="text-muted">This invitation was sent more than 7 days ago. Consider resending.</small></div>' +
            '</div>';
    }
    
    html += '<div class="detail-section">' +
        '<h6><i class="fas fa-id-card me-2" style="color: #1e3a5f;"></i>Identity</h6>' +
        '<div class="detail-row"><span class="label">User ID</span><span class="value">' + user.id + '</span></div>' +
        '<div class="detail-row"><span class="label">Created By</span><span class="value">' + (user.created_by || 'System') + '</span></div>' +
        '<div class="detail-row"><span class="label">Created At</span><span class="value">' + formatDate(user.created_at) + '</span></div>' +
        '<div class="detail-row"><span class="label">Department</span><span class="value">' + user.department + '</span></div>' +
        '</div>';
    
    if (user.status === 'Invited') {
        html += '<div class="detail-section">' +
            '<h6><i class="fas fa-envelope me-2" style="color: #1e3a5f;"></i>Invitation</h6>' +
            '<div class="detail-row"><span class="label">Invite Sent</span><span class="value">' + (user.invite_sent_at ? formatDate(user.invite_sent_at) : formatDate(user.created_at)) + '</span></div>' +
            '<div class="detail-row"><span class="label">Expires</span><span class="value">' + (inviteExpired ? '<span class="text-danger">Expired</span>' : '7 days after sent') + '</span></div>' +
            (user.internal_note ? '<div class="detail-row"><span class="label">Internal Note</span><span class="value" style="font-style: italic;">' + user.internal_note + '</span></div>' : '') +
            '</div>';
    }
    
    html += '<div class="detail-section">' +
        '<h6><i class="fas fa-shield-alt me-2" style="color: #1e3a5f;"></i>Access & Security</h6>' +
        '<div class="detail-row"><span class="label">MFA Enrolled</span><span class="value"><span class="badge-pill ' + mfaClass + '">' + user.mfa_status + '</span></span></div>' +
        '<div class="detail-row"><span class="label">MFA Method</span><span class="value">' + (user.mfa_method || 'Not configured') + '</span></div>' +
        '<div class="detail-row"><span class="label">Last Login</span><span class="value">' + (user.last_login ? formatDate(user.last_login) : 'Never') + '</span></div>' +
        '<div class="detail-row"><span class="label">Last Activity</span><span class="value">' + (user.last_activity ? formatDate(user.last_activity) : '-') + '</span></div>' +
        '<div class="detail-row"><span class="label">Active Sessions</span><span class="value">' + (user.active_sessions || 0) + (user.active_sessions > 0 ? ' <i class="fas fa-circle text-success" style="font-size: 0.5rem;"></i>' : '') + '</span></div>' +
        '<div class="detail-row"><span class="label">Failed Logins (24h)</span><span class="value">' + (user.failed_logins_24h > 0 ? '<span class="failed-logins-warning">' + user.failed_logins_24h + ' <i class="fas fa-exclamation-circle"></i></span>' : '0') + '</span></div>' +
        '</div>';
    
    html += '<div class="detail-section">' +
        '<h6><i class="fas fa-user-shield me-2" style="color: #1e3a5f;"></i>Permissions</h6>' +
        '<div class="detail-row"><span class="label">Role</span><span class="value"><strong>' + user.role + '</strong></span></div>';
    
    if (user.role === 'Super Admin') {
        html += '<div class="detail-row"><span class="label">Access Level</span><span class="value">Full administrative access</span></div>' +
            '<div class="permissions-summary" style="background: #f8f9fa; border-radius: 6px; padding: 0.75rem; margin-top: 0.5rem; font-size: 0.8rem;">' +
            '<div style="color: #495057; margin-bottom: 0.5rem;"><i class="fas fa-check-circle text-success me-1"></i> User Management</div>' +
            '<div style="color: #495057; margin-bottom: 0.5rem;"><i class="fas fa-check-circle text-success me-1"></i> System Configuration</div>' +
            '<div style="color: #495057; margin-bottom: 0.5rem;"><i class="fas fa-check-circle text-success me-1"></i> Security Settings</div>' +
            '<div style="color: #495057; margin-bottom: 0.5rem;"><i class="fas fa-check-circle text-success me-1"></i> Audit Log Access</div>' +
            '<div style="color: #495057;"><i class="fas fa-check-circle text-success me-1"></i> Impersonation (with audit)</div>' +
            '</div>';
    } else {
        html += '<div class="detail-row"><span class="label">Access Level</span><span class="value">Support operations access</span></div>' +
            '<div class="permissions-summary" style="background: #f8f9fa; border-radius: 6px; padding: 0.75rem; margin-top: 0.5rem; font-size: 0.8rem;">' +
            '<div style="color: #495057; margin-bottom: 0.5rem;"><i class="fas fa-check-circle text-success me-1"></i> Customer Account View</div>' +
            '<div style="color: #495057; margin-bottom: 0.5rem;"><i class="fas fa-check-circle text-success me-1"></i> Support Ticket Management</div>' +
            '<div style="color: #495057; margin-bottom: 0.5rem;"><i class="fas fa-check-circle text-success me-1"></i> Campaign Review</div>' +
            '<div style="color: #495057; margin-bottom: 0.5rem;"><i class="fas fa-times-circle text-muted me-1"></i> System Configuration</div>' +
            '<div style="color: #495057;"><i class="fas fa-times-circle text-muted me-1"></i> Security Settings</div>' +
            '</div>';
    }
    html += '</div>';
    
    document.getElementById('userDetailBody').innerHTML = html;
    
    var actionsHtml = '';
    if (user.status === 'Invited') {
        actionsHtml += '<button class="btn btn-sm" style="background: #1e3a5f; color: white;" onclick="resendInvite(\'' + userId + '\')"><i class="fas fa-paper-plane me-1"></i>Resend Invite</button>';
        actionsHtml += '<button class="btn btn-sm btn-outline-danger" onclick="revokeInvite(\'' + userId + '\'); closeUserDetail();"><i class="fas fa-times me-1"></i>Revoke</button>';
    } else if (user.status === 'Active') {
        actionsHtml += '<button class="btn btn-sm" style="background: #1e3a5f; color: white;" onclick="editUser(\'' + userId + '\')"><i class="fas fa-edit me-1"></i>Edit</button>';
        actionsHtml += '<button class="btn btn-sm btn-outline-warning" onclick="suspendUser(\'' + userId + '\')"><i class="fas fa-user-slash me-1"></i>Suspend</button>';
        actionsHtml += '<button class="btn btn-sm btn-outline-secondary" onclick="resetMfa(\'' + userId + '\')"><i class="fas fa-key me-1"></i>Reset MFA</button>';
        if (user.active_sessions > 0) {
            actionsHtml += '<button class="btn btn-sm btn-outline-danger" onclick="terminateSessions(\'' + userId + '\')"><i class="fas fa-sign-out-alt me-1"></i>End Sessions</button>';
        }
    } else if (user.status === 'Suspended') {
        actionsHtml += '<button class="btn btn-sm btn-outline-success" onclick="reactivateUser(\'' + userId + '\')"><i class="fas fa-user-check me-1"></i>Reactivate</button>';
        actionsHtml += '<button class="btn btn-sm btn-outline-secondary" onclick="archiveUser(\'' + userId + '\')"><i class="fas fa-archive me-1"></i>Archive</button>';
    } else if (user.status === 'Archived') {
        actionsHtml += '<button class="btn btn-sm btn-outline-primary" onclick="reactivateUser(\'' + userId + '\')"><i class="fas fa-undo me-1"></i>Restore</button>';
    }
    
    document.getElementById('userDetailActions').innerHTML = actionsHtml;
    
    document.getElementById('panelOverlay').classList.add('show');
    document.getElementById('userDetailPanel').classList.add('open');
}

function getInitials(name) {
    if (!name) return '?';
    var parts = name.split(' ');
    if (parts.length >= 2) {
        return parts[0].charAt(0).toUpperCase() + parts[parts.length - 1].charAt(0).toUpperCase();
    }
    return parts[0].charAt(0).toUpperCase();
}

function terminateSessions(userId) {
    if (!confirm('Terminate all active sessions for this user? They will be logged out immediately.')) return;
    var user = allUsers.find(function(u) { return u.id === userId; });
    if (user) {
        user.active_sessions = 0;
        openUserDetail(userId);
        showToast('All sessions terminated', 'warning');
        console.log('[AdminUsers] Sessions terminated:', userId);
    }
}

function closeUserDetail() {
    document.getElementById('panelOverlay').classList.remove('show');
    document.getElementById('userDetailPanel').classList.remove('open');
}

function formatDate(dateStr) {
    if (!dateStr) return '-';
    var d = new Date(dateStr);
    return d.toLocaleDateString('en-GB') + ' ' + d.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' });
}

function showInviteModal() {
    document.getElementById('inviteUserForm').reset();
    document.getElementById('inviteUserForm').classList.remove('was-validated');
    document.querySelectorAll('#inviteUserForm .is-invalid').forEach(function(el) {
        el.classList.remove('is-invalid');
    });
    new bootstrap.Modal(document.getElementById('inviteUserModal')).show();
}

function validateInviteForm() {
    var form = document.getElementById('inviteUserForm');
    var firstName = document.getElementById('inviteFirstName').value.trim();
    var lastName = document.getElementById('inviteLastName').value.trim();
    var email = document.getElementById('inviteEmail').value.trim();
    var role = document.getElementById('inviteRole').value;
    var department = document.getElementById('inviteDepartment').value;
    var isValid = true;
    
    document.querySelectorAll('#inviteUserForm .form-control, #inviteUserForm .form-select').forEach(function(el) {
        el.classList.remove('is-invalid');
    });
    
    if (!firstName) {
        document.getElementById('inviteFirstName').classList.add('is-invalid');
        isValid = false;
    }
    if (!lastName) {
        document.getElementById('inviteLastName').classList.add('is-invalid');
        isValid = false;
    }
    if (!email) {
        document.getElementById('inviteEmail').classList.add('is-invalid');
        document.getElementById('emailError').textContent = 'Email is required';
        isValid = false;
    } else if (!email.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
        document.getElementById('inviteEmail').classList.add('is-invalid');
        document.getElementById('emailError').textContent = 'Please enter a valid email address';
        isValid = false;
    } else if (!email.endsWith('@quicksms.co.uk')) {
        document.getElementById('inviteEmail').classList.add('is-invalid');
        document.getElementById('emailError').textContent = 'Email must be a @quicksms.co.uk address';
        isValid = false;
    } else if (allUsers.some(function(u) { return u.email.toLowerCase() === email.toLowerCase(); })) {
        document.getElementById('inviteEmail').classList.add('is-invalid');
        document.getElementById('emailError').textContent = 'This email address is already registered';
        isValid = false;
    }
    if (!role) {
        document.getElementById('inviteRole').classList.add('is-invalid');
        isValid = false;
    }
    if (!department) {
        document.getElementById('inviteDepartment').classList.add('is-invalid');
        isValid = false;
    }
    
    return isValid;
}

function sendInvite() {
    if (!validateInviteForm()) return;
    
    var firstName = document.getElementById('inviteFirstName').value.trim();
    var lastName = document.getElementById('inviteLastName').value.trim();
    var email = document.getElementById('inviteEmail').value.trim();
    var role = document.getElementById('inviteRole').value;
    var department = document.getElementById('inviteDepartment').value;
    var note = document.getElementById('inviteNote').value.trim();
    
    var btn = document.getElementById('sendInviteBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Sending...';
    
    setTimeout(function() {
        var newId = 'ADM' + String(allUsers.length + 1).padStart(3, '0');
        var newUser = {
            id: newId,
            name: firstName + ' ' + lastName,
            email: email,
            role: role,
            department: department,
            status: 'Invited',
            mfa_status: 'Not Enrolled',
            mfa_method: null,
            last_login: null,
            last_activity: null,
            failed_logins_24h: 0,
            created_at: new Date().toISOString().split('T')[0],
            internal_note: note,
            invite_sent_at: new Date().toISOString()
        };
        
        allUsers.unshift(newUser);
        filteredUsers = [...allUsers];
        
        addTableRow(newUser);
        updateStats();
        filterTable();
        highlightRow(newId);
        
        bootstrap.Modal.getInstance(document.getElementById('inviteUserModal')).hide();
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-paper-plane me-1"></i> Send Invite';
        
        showToast('Invitation sent to ' + email, 'success');
        console.log('[AdminUsers] Invite sent:', { email: email, role: role, note: note || 'none' });
    }, 800);
}

function addTableRow(user) {
    var tbody = document.getElementById('adminUsersTableBody');
    var statusClass = 'badge-invited';
    var mfaClass = 'badge-not-enrolled';
    
    var tr = document.createElement('tr');
    tr.className = 'user-row';
    tr.dataset.id = user.id;
    tr.dataset.user = JSON.stringify(user);
    tr.onclick = function() { openUserDetail(user.id); };
    
    tr.innerHTML = '<td><div class="user-name">' + user.name + '</div></td>' +
        '<td title="' + user.email + '">' + user.email + '</td>' +
        '<td><span class="badge-pill ' + statusClass + '">' + user.status + '</span></td>' +
        '<td><span class="badge-pill ' + mfaClass + '">' + user.mfa_status + '</span></td>' +
        '<td>-</td>' +
        '<td><span class="text-muted">Never</span></td>' +
        '<td><span class="text-muted">-</span></td>' +
        '<td>0</td>' +
        '<td>' + formatDateShort(user.created_at) + '</td>' +
        '<td class="text-center" onclick="event.stopPropagation()">' +
            '<div class="dropdown">' +
                '<button class="action-dots-btn" type="button" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-v"></i></button>' +
                '<ul class="dropdown-menu dropdown-menu-end shadow-sm">' +
                    '<li><a class="dropdown-item" href="#" onclick="openUserDetail(\'' + user.id + '\')"><i class="fas fa-eye me-2"></i>View Details</a></li>' +
                    '<li><a class="dropdown-item" href="#" onclick="resendInvite(\'' + user.id + '\')"><i class="fas fa-paper-plane me-2"></i>Resend Invite</a></li>' +
                    '<li><hr class="dropdown-divider"></li>' +
                    '<li><a class="dropdown-item text-danger" href="#" onclick="revokeInvite(\'' + user.id + '\')"><i class="fas fa-times me-2"></i>Revoke Invite</a></li>' +
                '</ul>' +
            '</div>' +
        '</td>';
    
    tbody.insertBefore(tr, tbody.firstChild);
}

function formatDateShort(dateStr) {
    if (!dateStr) return '-';
    var d = new Date(dateStr);
    var day = String(d.getDate()).padStart(2, '0');
    var month = String(d.getMonth() + 1).padStart(2, '0');
    var year = d.getFullYear();
    return day + '-' + month + '-' + year;
}

function highlightRow(userId) {
    var row = document.querySelector('tr[data-id="' + userId + '"]');
    if (row) {
        row.style.backgroundColor = '#e8f4fd';
        row.style.transition = 'background-color 0.3s ease';
        setTimeout(function() {
            row.style.backgroundColor = '';
        }, 3000);
    }
}

function updateStats() {
    var total = allUsers.length;
    var active = allUsers.filter(function(u) { return u.status === 'Active'; }).length;
    var invited = allUsers.filter(function(u) { return u.status === 'Invited'; }).length;
    var suspendedArchived = allUsers.filter(function(u) { return u.status === 'Suspended' || u.status === 'Archived'; }).length;
    
    document.querySelectorAll('.stat-card .stat-content h3')[0].textContent = total;
    document.querySelectorAll('.stat-card .stat-content h3')[1].textContent = active;
    document.querySelectorAll('.stat-card .stat-content h3')[2].textContent = invited;
    document.querySelectorAll('.stat-card .stat-content h3')[3].textContent = suspendedArchived;
}

function editUser(userId) { console.log('[AdminUsers] Edit:', userId); closeUserDetail(); }
function suspendUser(userId) { if (confirm('Suspend this user?')) { showToast('User suspended', 'warning'); closeUserDetail(); } }
function reactivateUser(userId) { showToast('User reactivated', 'success'); closeUserDetail(); }
function resetMfa(userId) { if (confirm('Reset MFA for this user?')) { showToast('MFA reset email sent', 'info'); } }

function resendInvite(userId) {
    var user = allUsers.find(function(u) { return u.id === userId; });
    if (!user || user.status !== 'Invited') return;
    
    showToast('Resending invitation to ' + user.email + '...', 'info');
    setTimeout(function() {
        user.invite_sent_at = new Date().toISOString();
        showToast('Invitation resent to ' + user.email, 'success');
        console.log('[AdminUsers] Invite resent:', userId);
    }, 500);
}

function revokeInvite(userId) {
    if (!confirm('Revoke this invitation? The user will no longer be able to accept it.')) return;
    var idx = allUsers.findIndex(function(u) { return u.id === userId; });
    if (idx > -1 && allUsers[idx].status === 'Invited') {
        allUsers.splice(idx, 1);
        filteredUsers = [...allUsers];
        var row = document.querySelector('tr[data-id="' + userId + '"]');
        if (row) row.remove();
        updateStats();
        filterTable();
        showToast('Invitation revoked', 'warning');
    }
}

function archiveUser(userId) { if (confirm('Archive this user? This action can be reversed.')) { showToast('User archived', 'warning'); } }

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
    toastEl.addEventListener('hidden.bs.toast', function() { toastEl.remove(); });
}
</script>
@endpush
