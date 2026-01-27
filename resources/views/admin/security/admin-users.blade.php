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
.support-mode-banner {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    height: 48px;
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    color: white;
    z-index: 9999;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 1.5rem;
    box-shadow: 0 2px 10px rgba(220, 53, 69, 0.4);
    animation: supportModePulse 2s ease-in-out infinite;
}
@keyframes supportModePulse {
    0%, 100% { box-shadow: 0 2px 10px rgba(220, 53, 69, 0.4); }
    50% { box-shadow: 0 2px 20px rgba(220, 53, 69, 0.6); }
}
.support-mode-content {
    display: flex;
    align-items: center;
    font-size: 0.9rem;
}
.support-mode-timer {
    background: rgba(0,0,0,0.2);
    padding: 0.25rem 0.75rem;
    border-radius: 4px;
    font-family: monospace;
}
.support-mode-actions .btn {
    font-size: 0.8rem;
}
body.support-mode-active {
    padding-top: 48px !important;
}
body.support-mode-active .pii-masked {
    filter: blur(4px);
    pointer-events: none;
    user-select: none;
}
body.support-mode-active .pii-masked::before {
    content: 'PII Masked';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: rgba(220, 53, 69, 0.9);
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    z-index: 10;
}
.pii-overlay {
    position: relative;
}
.pii-overlay::after {
    content: 'PII Protected - Support Mode';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: repeating-linear-gradient(45deg, rgba(220,53,69,0.1), rgba(220,53,69,0.1) 10px, rgba(220,53,69,0.05) 10px, rgba(220,53,69,0.05) 20px);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #dc3545;
    font-weight: 600;
    font-size: 0.9rem;
    z-index: 5;
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
                                    @if($user['status'] !== 'Archived')
                                    <li><a class="dropdown-item" href="#" onclick="editUser('{{ $user['id'] }}')"><i class="fas fa-edit me-2"></i>Edit User</a></li>
                                    @else
                                    <li><a class="dropdown-item disabled text-muted" href="#" style="pointer-events: none;"><i class="fas fa-edit me-2"></i>Edit User</a></li>
                                    @endif
                                    <li><hr class="dropdown-divider"></li>
                                    <li class="dropdown-header" style="font-size: 0.7rem; color: #6c757d;">Account Status</li>
                                    @if($user['status'] === 'Active')
                                    <li><a class="dropdown-item text-warning" href="#" onclick="suspendUser('{{ $user['id'] }}')"><i class="fas fa-user-slash me-2"></i>Suspend</a></li>
                                    @elseif($user['status'] === 'Suspended')
                                    <li><a class="dropdown-item text-success" href="#" onclick="reactivateUser('{{ $user['id'] }}')"><i class="fas fa-user-check me-2"></i>Reactivate</a></li>
                                    <li><a class="dropdown-item text-danger" href="#" onclick="archiveUser('{{ $user['id'] }}')"><i class="fas fa-archive me-2"></i>Archive</a></li>
                                    @elseif($user['status'] === 'Invited')
                                    <li><a class="dropdown-item" href="#" onclick="resendInvite('{{ $user['id'] }}')"><i class="fas fa-paper-plane me-2"></i>Resend Invite</a></li>
                                    <li><a class="dropdown-item text-danger" href="#" onclick="revokeInvite('{{ $user['id'] }}')"><i class="fas fa-times me-2"></i>Revoke Invite</a></li>
                                    @elseif($user['status'] === 'Archived')
                                    <li><a class="dropdown-item disabled text-muted" href="#" style="pointer-events: none;"><i class="fas fa-lock me-2"></i>No actions available</a></li>
                                    @endif
                                    @if($user['status'] !== 'Invited' && $user['status'] !== 'Archived')
                                    <li><hr class="dropdown-divider"></li>
                                    <li class="dropdown-header" style="font-size: 0.7rem; color: #6c757d;">Security</li>
                                    <li><a class="dropdown-item" href="#" onclick="resetPassword('{{ $user['id'] }}')"><i class="fas fa-key me-2"></i>Reset Password</a></li>
                                    @if($user['status'] === 'Active')
                                    <li><a class="dropdown-item" href="#" onclick="forceLogout('{{ $user['id'] }}')"><i class="fas fa-sign-out-alt me-2"></i>Force Logout</a></li>
                                    @endif
                                    <li><a class="dropdown-item" href="#" onclick="resetMfa('{{ $user['id'] }}')"><i class="fas fa-shield-alt me-2"></i>Reset MFA</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="updateMfaDetails('{{ $user['id'] }}')"><i class="fas fa-mobile-alt me-2"></i>MFA Settings</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="updateEmail('{{ $user['id'] }}')"><i class="fas fa-envelope me-2"></i>Update Email</a></li>
                                    @if($user['status'] === 'Active')
                                    <li><hr class="dropdown-divider"></li>
                                    <li class="dropdown-header" style="font-size: 0.7rem; color: #6c757d;">Support</li>
                                    <li><a class="dropdown-item text-danger impersonate-action" href="#" onclick="impersonateUser('{{ $user['id'] }}')"><i class="fas fa-user-secret me-2"></i>Impersonate User</a></li>
                                    @endif
                                    @endif
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

<div class="modal fade" id="suspendUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: #fff3cd; border-bottom: 1px solid #ffc107;">
                <h5 class="modal-title" style="color: #856404;"><i class="fas fa-user-slash me-2"></i>Suspend Admin User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning mb-3" style="background: #fff3cd; border: 1px solid #ffc107;">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Warning:</strong> This will immediately revoke all active sessions for this user.
                </div>
                <p class="mb-3">You are about to suspend <strong id="suspendUserName"></strong>.</p>
                <div class="mb-0">
                    <label class="form-label">Reason for suspension <span class="text-muted small">(optional)</span></label>
                    <textarea class="form-control" id="suspendReason" rows="3" placeholder="Enter reason for suspension..."></textarea>
                </div>
                <input type="hidden" id="suspendUserId">
            </div>
            <div class="modal-footer" style="background: #f8f9fa;">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="confirmSuspendBtn" onclick="confirmSuspend()">
                    <i class="fas fa-user-slash me-1"></i> Suspend User
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="reactivateUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: #d4edda; border-bottom: 1px solid #28a745;">
                <h5 class="modal-title" style="color: #155724;"><i class="fas fa-user-check me-2"></i>Reactivate Admin User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">You are about to reactivate <strong id="reactivateUserName"></strong>.</p>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="requireMfaReenrol" checked>
                    <label class="form-check-label" for="requireMfaReenrol">
                        Require MFA re-enrollment on next login
                    </label>
                    <div class="form-text">Recommended for security after suspension</div>
                </div>
                <input type="hidden" id="reactivateUserId">
            </div>
            <div class="modal-footer" style="background: #f8f9fa;">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="confirmReactivateBtn" onclick="confirmReactivate()">
                    <i class="fas fa-user-check me-1"></i> Reactivate User
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="archiveUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: #f8d7da; border-bottom: 1px solid #dc3545;">
                <h5 class="modal-title" style="color: #721c24;"><i class="fas fa-archive me-2"></i>Archive Admin User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger mb-3">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <strong>Permanent Action:</strong> Archived users cannot be reactivated. This action is final.
                </div>
                <p class="mb-3">You are about to archive <strong id="archiveUserName"></strong>.</p>
                <div class="mb-3">
                    <label class="form-label">Reason for archiving <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="archiveReason" rows="3" placeholder="Enter reason for archiving..." required></textarea>
                    <div class="invalid-feedback">Reason is required for archiving</div>
                </div>
                <div class="form-check mb-0">
                    <input class="form-check-input" type="checkbox" id="confirmArchiveCheck" onchange="toggleArchiveBtn()">
                    <label class="form-check-label" for="confirmArchiveCheck">
                        I understand this action is permanent and cannot be undone
                    </label>
                </div>
                <input type="hidden" id="archiveUserId">
            </div>
            <div class="modal-footer" style="background: #f8f9fa;">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmArchiveBtn" onclick="confirmArchive()" disabled>
                    <i class="fas fa-archive me-1"></i> Archive Permanently
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="resetPasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: #e7f1ff; border-bottom: 1px solid #b6d4fe;">
                <h5 class="modal-title" style="color: #1e3a5f;"><i class="fas fa-key me-2"></i>Reset Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info mb-3">
                    <i class="fas fa-info-circle me-2"></i>
                    This will send a password reset email and revoke all active sessions.
                </div>
                <p class="mb-0">Send password reset email to <strong id="resetPasswordUserName"></strong>?</p>
                <p class="text-muted small mb-0" id="resetPasswordEmail"></p>
                <input type="hidden" id="resetPasswordUserId">
            </div>
            <div class="modal-footer" style="background: #f8f9fa;">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn" style="background: #1e3a5f; color: white;" id="confirmResetPasswordBtn" onclick="confirmResetPassword()">
                    <i class="fas fa-paper-plane me-1"></i> Send Reset Email
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="forceLogoutModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: #fff3cd; border-bottom: 1px solid #ffc107;">
                <h5 class="modal-title" style="color: #856404;"><i class="fas fa-sign-out-alt me-2"></i>Force Logout</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning mb-3">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    This will immediately terminate all active sessions for this user.
                </div>
                <p class="mb-2">Force logout <strong id="forceLogoutUserName"></strong>?</p>
                <p class="mb-0"><span class="badge bg-secondary" id="forceLogoutSessionCount">0</span> active session(s) will be terminated.</p>
                <input type="hidden" id="forceLogoutUserId">
            </div>
            <div class="modal-footer" style="background: #f8f9fa;">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="confirmForceLogoutBtn" onclick="confirmForceLogout()">
                    <i class="fas fa-sign-out-alt me-1"></i> Force Logout
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="resetMfaModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: #e7f1ff; border-bottom: 1px solid #b6d4fe;">
                <h5 class="modal-title" style="color: #1e3a5f;"><i class="fas fa-shield-alt me-2"></i>Reset MFA</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">Reset MFA for <strong id="resetMfaUserName"></strong></p>
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="mfaResetAction" id="mfaReenroll" value="reenroll" checked onchange="toggleTempDisableMfa()">
                        <label class="form-check-label" for="mfaReenroll">
                            <strong>Force MFA Re-enrollment</strong>
                            <div class="form-text">User must set up MFA again on next login</div>
                        </label>
                    </div>
                    <div class="form-check mt-2" id="tempDisableContainer">
                        <input class="form-check-input" type="radio" name="mfaResetAction" id="mfaTempDisable" value="temp_disable" onchange="toggleTempDisableMfa()">
                        <label class="form-check-label" for="mfaTempDisable">
                            <strong>Temporarily Disable MFA</strong> <span class="badge bg-danger">Super Admin Only</span>
                            <div class="form-text">Allows login without MFA until re-enrolled</div>
                        </label>
                    </div>
                </div>
                <div class="alert alert-danger mb-0 d-none" id="tempDisableWarning">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <strong>Security Warning:</strong> Temporarily disabling MFA significantly reduces account security. 
                    This action is logged and requires justification. Only use for verified account recovery scenarios.
                    <div class="mt-2">
                        <label class="form-label">Justification <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="mfaDisableReason" rows="2" placeholder="Why is MFA being temporarily disabled?"></textarea>
                    </div>
                </div>
                <input type="hidden" id="resetMfaUserId">
            </div>
            <div class="modal-footer" style="background: #f8f9fa;">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn" style="background: #1e3a5f; color: white;" id="confirmResetMfaBtn" onclick="confirmResetMfa()">
                    <i class="fas fa-shield-alt me-1"></i> Reset MFA
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="updateMfaModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: #e7f1ff; border-bottom: 1px solid #b6d4fe;">
                <h5 class="modal-title" style="color: #1e3a5f;"><i class="fas fa-mobile-alt me-2"></i>Update MFA Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">Update MFA settings for <strong id="updateMfaUserName"></strong></p>
                <div class="mb-3">
                    <label class="form-label">Allowed MFA Methods <span class="text-danger">*</span></label>
                    <select class="form-select" id="updateMfaMethod">
                        <option value="Authenticator">Authenticator App Only</option>
                        <option value="SMS">SMS OTP Only</option>
                        <option value="Both">Both (Authenticator + SMS)</option>
                    </select>
                    <div class="form-text">Admin security policy may override individual settings</div>
                </div>
                <div class="mb-3" id="smsPhoneContainer">
                    <label class="form-label">SMS Phone Number</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-phone"></i></span>
                        <input type="tel" class="form-control" id="updateMfaPhone" placeholder="+44 7XXX XXXXXX">
                    </div>
                    <div class="form-text">Required if SMS OTP is enabled</div>
                </div>
                <div class="alert alert-secondary mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    <small>Current MFA Status: <strong id="currentMfaStatus">-</strong></small>
                </div>
                <input type="hidden" id="updateMfaUserId">
            </div>
            <div class="modal-footer" style="background: #f8f9fa;">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn" style="background: #1e3a5f; color: white;" id="confirmUpdateMfaBtn" onclick="confirmUpdateMfa()">
                    <i class="fas fa-save me-1"></i> Save Changes
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="updateEmailModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: #fff3cd; border-bottom: 1px solid #ffc107;">
                <h5 class="modal-title" style="color: #856404;"><i class="fas fa-envelope me-2"></i>Update Email Address</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning mb-3">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Important:</strong> Changing email will force re-verification and revoke all active sessions.
                </div>
                <p class="mb-3">Update email for <strong id="updateEmailUserName"></strong></p>
                <div class="mb-3">
                    <label class="form-label">Current Email</label>
                    <input type="email" class="form-control" id="currentEmail" disabled>
                </div>
                <div class="mb-3">
                    <label class="form-label">New Email Address <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" id="newEmail" placeholder="new.email@quicksms.co.uk">
                    <div class="invalid-feedback" id="newEmailError">Must be a valid @quicksms.co.uk email</div>
                    <div class="form-text">Must be a @quicksms.co.uk email address</div>
                </div>
                <div class="mb-0">
                    <label class="form-label">Reason for Change <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="emailChangeReason" rows="2" placeholder="Why is the email being changed?"></textarea>
                    <div class="invalid-feedback">Reason is required</div>
                </div>
                <input type="hidden" id="updateEmailUserId">
            </div>
            <div class="modal-footer" style="background: #f8f9fa;">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="confirmUpdateEmailBtn" onclick="confirmUpdateEmail()">
                    <i class="fas fa-save me-1"></i> Update Email
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="impersonateUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: #dc3545; border-bottom: 1px solid #bd2130;">
                <h5 class="modal-title text-white"><i class="fas fa-user-secret me-2"></i>Start Support Session</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger mb-3">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Security Notice:</strong> This action will be logged and audited. You are entering a time-limited support session.
                </div>
                <p class="mb-3">Start support session as <strong id="impersonateUserName"></strong>?</p>
                <div class="card bg-light mb-3">
                    <div class="card-body py-2">
                        <small class="text-muted d-block"><i class="fas fa-envelope me-2"></i><span id="impersonateUserEmail"></span></small>
                        <small class="text-muted d-block"><i class="fas fa-user-tag me-2"></i><span id="impersonateUserRole"></span></small>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Session Duration <span class="text-danger">*</span></label>
                    <select class="form-select" id="impersonateDuration">
                        <option value="15">15 minutes</option>
                        <option value="30" selected>30 minutes</option>
                        <option value="60">1 hour</option>
                        <option value="120">2 hours</option>
                    </select>
                    <div class="form-text">Session will automatically end after this time</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Reason for Support Session <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="impersonateReason" rows="2" placeholder="Describe the support issue being investigated..."></textarea>
                    <div class="invalid-feedback">Reason is required for audit trail</div>
                </div>
                <div class="form-check mb-0">
                    <input class="form-check-input" type="checkbox" id="impersonateAcknowledge" onchange="toggleImpersonateBtn()">
                    <label class="form-check-label small" for="impersonateAcknowledge">
                        I acknowledge this session will be fully logged and PII areas will be masked
                    </label>
                </div>
                <input type="hidden" id="impersonateUserId">
            </div>
            <div class="modal-footer" style="background: #f8f9fa;">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmImpersonateBtn" onclick="confirmImpersonate()" disabled>
                    <i class="fas fa-user-secret me-1"></i> Start Support Session
                </button>
            </div>
        </div>
    </div>
</div>

<div id="supportModeBanner" class="support-mode-banner d-none">
    <div class="support-mode-content">
        <i class="fas fa-user-secret me-2"></i>
        <span><strong>Support Mode Active</strong> — Viewing as <span id="supportModeUserName"></span></span>
        <span class="support-mode-timer ms-3"><i class="fas fa-clock me-1"></i><span id="supportModeTimer">30:00</span> remaining</span>
    </div>
    <div class="support-mode-actions">
        <button class="btn btn-sm btn-outline-light" onclick="endSupportSession()"><i class="fas fa-sign-out-alt me-1"></i>End Session</button>
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

var AdminUsersService = (function() {
    var mockMode = true;
    var baseUrl = '/admin/api/admin-users';
    
    function generateRefId() {
        return 'ERR-' + Date.now().toString(36).toUpperCase() + '-' + Math.random().toString(36).substr(2, 4).toUpperCase();
    }
    
    function makeRequest(endpoint, method, data) {
        return new Promise(function(resolve, reject) {
            if (mockMode) {
                setTimeout(function() {
                    if (Math.random() < 0.02) {
                        reject({ success: false, error: 'Simulated server error', refId: generateRefId() });
                    } else {
                        resolve({ success: true, data: data, timestamp: new Date().toISOString() });
                    }
                }, 400 + Math.random() * 400);
                return;
            }
            
            fetch(baseUrl + endpoint, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(data)
            })
            .then(function(response) {
                if (!response.ok) {
                    return response.json().then(function(err) {
                        throw { success: false, error: err.message || 'Request failed', refId: generateRefId() };
                    });
                }
                return response.json();
            })
            .then(resolve)
            .catch(function(err) {
                reject({ success: false, error: err.error || 'Network error', refId: err.refId || generateRefId() });
            });
        });
    }
    
    return {
        suspendUser: function(userId, reason) {
            return makeRequest('/suspend', 'POST', { user_id: userId, reason: reason });
        },
        reactivateUser: function(userId, requireMfa) {
            return makeRequest('/reactivate', 'POST', { user_id: userId, require_mfa: requireMfa });
        },
        archiveUser: function(userId, reason) {
            return makeRequest('/archive', 'POST', { user_id: userId, reason: reason });
        },
        inviteUser: function(userData) {
            return makeRequest('/invite', 'POST', userData);
        },
        resendInvite: function(userId) {
            return makeRequest('/resend-invite', 'POST', { user_id: userId });
        },
        resetPassword: function(userId) {
            return makeRequest('/reset-password', 'POST', { user_id: userId });
        },
        forceLogout: function(userId) {
            return makeRequest('/force-logout', 'POST', { user_id: userId });
        },
        resetMfa: function(userId, tempDisable, reason) {
            return makeRequest('/reset-mfa', 'POST', { user_id: userId, temp_disable: tempDisable, reason: reason });
        },
        updateMfa: function(userId, method, phone) {
            return makeRequest('/update-mfa', 'POST', { user_id: userId, method: method, phone: phone });
        },
        updateEmail: function(userId, newEmail, reason) {
            return makeRequest('/update-email', 'POST', { user_id: userId, new_email: newEmail, reason: reason });
        },
        startImpersonation: function(userId, duration, reason) {
            return makeRequest('/impersonate', 'POST', { user_id: userId, duration: duration, reason: reason });
        },
        endImpersonation: function(sessionId) {
            return makeRequest('/end-impersonation', 'POST', { session_id: sessionId });
        }
    };
})();

function showErrorToast(message, refId) {
    var fullMessage = message;
    if (refId) {
        fullMessage += ' (Ref: ' + refId + ')';
    }
    showToast(fullMessage, 'error');
    console.error('[AdminUsers] Error:', message, 'RefId:', refId);
}

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
    
    var actionsHtml = '<div class="d-flex flex-column gap-2" style="padding: 0.5rem;">';
    
    if (user.status === 'Invited') {
        actionsHtml += '<div class="d-flex flex-wrap gap-2">';
        actionsHtml += '<button class="btn btn-sm" style="background: #1e3a5f; color: white;" onclick="resendInvite(\'' + userId + '\')"><i class="fas fa-paper-plane me-1"></i>Resend Invite</button>';
        actionsHtml += '<button class="btn btn-sm btn-outline-danger" onclick="revokeInvite(\'' + userId + '\'); closeUserDetail();"><i class="fas fa-times me-1"></i>Revoke</button>';
        actionsHtml += '</div>';
    } else if (user.status === 'Active') {
        actionsHtml += '<div class="mb-2"><small class="text-muted fw-bold">Account Actions</small></div>';
        actionsHtml += '<div class="d-flex flex-wrap gap-2 mb-2">';
        actionsHtml += '<button class="btn btn-sm" style="background: #1e3a5f; color: white;" onclick="editUser(\'' + userId + '\')"><i class="fas fa-edit me-1"></i>Edit</button>';
        actionsHtml += '<button class="btn btn-sm btn-outline-warning" onclick="suspendUser(\'' + userId + '\')"><i class="fas fa-user-slash me-1"></i>Suspend</button>';
        actionsHtml += '</div>';
        actionsHtml += '<div class="mb-2"><small class="text-muted fw-bold">Security Actions</small></div>';
        actionsHtml += '<div class="d-flex flex-wrap gap-2">';
        actionsHtml += '<button class="btn btn-sm btn-outline-primary" onclick="resetPassword(\'' + userId + '\')"><i class="fas fa-key me-1"></i>Reset Password</button>';
        if (user.active_sessions > 0) {
            actionsHtml += '<button class="btn btn-sm btn-outline-danger" onclick="forceLogout(\'' + userId + '\')"><i class="fas fa-sign-out-alt me-1"></i>Force Logout (' + user.active_sessions + ')</button>';
        } else {
            actionsHtml += '<button class="btn btn-sm btn-outline-secondary" disabled title="No active sessions"><i class="fas fa-sign-out-alt me-1"></i>Force Logout</button>';
        }
        actionsHtml += '<button class="btn btn-sm btn-outline-info" onclick="resetMfa(\'' + userId + '\')"><i class="fas fa-shield-alt me-1"></i>Reset MFA</button>';
        actionsHtml += '<button class="btn btn-sm btn-outline-secondary" onclick="updateMfaDetails(\'' + userId + '\')"><i class="fas fa-mobile-alt me-1"></i>MFA Settings</button>';
        actionsHtml += '<button class="btn btn-sm btn-outline-secondary" onclick="updateEmail(\'' + userId + '\')"><i class="fas fa-envelope me-1"></i>Update Email</button>';
        actionsHtml += '</div>';
        if (currentAdminRole === 'super_admin') {
            actionsHtml += '<div class="mb-2 mt-2"><small class="text-muted fw-bold">Support</small></div>';
            actionsHtml += '<div class="d-flex flex-wrap gap-2">';
            actionsHtml += '<button class="btn btn-sm btn-danger" onclick="impersonateUser(\'' + userId + '\')"><i class="fas fa-user-secret me-1"></i>Impersonate User</button>';
            actionsHtml += '</div>';
        }
    } else if (user.status === 'Suspended') {
        actionsHtml += '<div class="mb-2"><small class="text-muted fw-bold">Account Actions</small></div>';
        actionsHtml += '<div class="d-flex flex-wrap gap-2 mb-2">';
        actionsHtml += '<button class="btn btn-sm btn-success" onclick="reactivateUser(\'' + userId + '\')"><i class="fas fa-user-check me-1"></i>Reactivate</button>';
        actionsHtml += '<button class="btn btn-sm btn-outline-danger" onclick="archiveUser(\'' + userId + '\')"><i class="fas fa-archive me-1"></i>Archive</button>';
        actionsHtml += '</div>';
        actionsHtml += '<div class="mb-2"><small class="text-muted fw-bold">Security Actions</small></div>';
        actionsHtml += '<div class="d-flex flex-wrap gap-2">';
        actionsHtml += '<button class="btn btn-sm btn-outline-primary" onclick="resetPassword(\'' + userId + '\')"><i class="fas fa-key me-1"></i>Reset Password</button>';
        actionsHtml += '<button class="btn btn-sm btn-outline-info" onclick="resetMfa(\'' + userId + '\')"><i class="fas fa-shield-alt me-1"></i>Reset MFA</button>';
        actionsHtml += '</div>';
    } else if (user.status === 'Archived') {
        actionsHtml += '<div class="text-center py-2"><span class="text-muted small"><i class="fas fa-lock me-1"></i>Archived users cannot be modified</span></div>';
    }
    
    actionsHtml += '</div>';
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
    
    var userData = {
        first_name: firstName,
        last_name: lastName,
        email: email,
        role: role,
        department: department,
        note: note
    };
    
    AdminUsersService.inviteUser(userData)
        .then(function(response) {
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
            
            logAdminUserAudit('ADMIN_USER_INVITED', email, 
                null, 
                { status: 'Invited', role: role }
            );
            
            addTableRow(newUser);
            updateStats();
            filterTable();
            highlightRow(newId);
            
            bootstrap.Modal.getInstance(document.getElementById('inviteUserModal')).hide();
            showToast('Invitation sent to ' + email, 'success');
            console.log('[AdminUsers] Invite sent:', { email: email, role: role, note: note || 'none' });
        })
        .catch(function(err) {
            showErrorToast('Failed to send invitation: ' + err.error, err.refId);
        })
        .finally(function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane me-1"></i> Send Invite';
        });
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

function editUser(userId) { 
    console.log('[AdminUsers] Edit:', userId); 
    closeUserDetail(); 
    showToast('Edit functionality coming soon', 'info');
}

function suspendUser(userId) {
    var user = allUsers.find(function(u) { return u.id === userId; });
    if (!user) {
        showToast('User not found', 'error');
        return;
    }
    if (user.status !== 'Active') {
        showToast('Only active users can be suspended', 'error');
        return;
    }
    
    document.getElementById('suspendUserId').value = userId;
    document.getElementById('suspendUserName').textContent = user.name;
    document.getElementById('suspendReason').value = '';
    closeUserDetail();
    new bootstrap.Modal(document.getElementById('suspendUserModal')).show();
}

function confirmSuspend() {
    var userId = document.getElementById('suspendUserId').value;
    var reason = document.getElementById('suspendReason').value.trim();
    var user = allUsers.find(function(u) { return u.id === userId; });
    
    if (!user || user.status !== 'Active') {
        showToast('Cannot suspend: Invalid user state', 'error');
        bootstrap.Modal.getInstance(document.getElementById('suspendUserModal')).hide();
        return;
    }
    
    var btn = document.getElementById('confirmSuspendBtn');
    var previousStatus = user.status;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Suspending...';
    
    AdminUsersService.suspendUser(userId, reason || 'No reason provided')
        .then(function(response) {
            user.status = 'Suspended';
            user.active_sessions = 0;
            user.suspended_at = new Date().toISOString();
            user.suspended_reason = reason || null;
            
            logAdminUserAudit('ADMIN_USER_SUSPENDED', user.email, 
                { status: previousStatus }, 
                { status: 'Suspended' }, 
                reason || 'No reason provided'
            );
            
            updateTableRowStatus(userId, 'Suspended');
            updateStats();
            
            bootstrap.Modal.getInstance(document.getElementById('suspendUserModal')).hide();
            showToast(user.name + ' has been suspended. All sessions revoked.', 'warning');
            console.log('[AdminUsers] User suspended:', { userId: userId, reason: reason || 'none' });
        })
        .catch(function(err) {
            showErrorToast('Failed to suspend user: ' + err.error, err.refId);
        })
        .finally(function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-user-slash me-1"></i> Suspend User';
        });
}

function reactivateUser(userId) {
    var user = allUsers.find(function(u) { return u.id === userId; });
    if (!user) {
        showToast('User not found', 'error');
        return;
    }
    if (user.status !== 'Suspended') {
        showToast('Only suspended users can be reactivated', 'error');
        return;
    }
    
    document.getElementById('reactivateUserId').value = userId;
    document.getElementById('reactivateUserName').textContent = user.name;
    document.getElementById('requireMfaReenrol').checked = true;
    closeUserDetail();
    new bootstrap.Modal(document.getElementById('reactivateUserModal')).show();
}

function confirmReactivate() {
    var userId = document.getElementById('reactivateUserId').value;
    var requireMfa = document.getElementById('requireMfaReenrol').checked;
    var user = allUsers.find(function(u) { return u.id === userId; });
    
    if (!user || user.status !== 'Suspended') {
        showToast('Cannot reactivate: Invalid user state', 'error');
        bootstrap.Modal.getInstance(document.getElementById('reactivateUserModal')).hide();
        return;
    }
    
    var btn = document.getElementById('confirmReactivateBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Reactivating...';
    
    AdminUsersService.reactivateUser(userId, requireMfa)
        .then(function(response) {
            user.status = 'Active';
            user.reactivated_at = new Date().toISOString();
            if (requireMfa) {
                user.mfa_status = 'Not Enrolled';
                user.mfa_method = null;
                user.require_mfa_reenrol = true;
            }
            
            logAdminUserAudit('ADMIN_USER_REACTIVATED', user.email, 
                { status: 'Suspended' }, 
                { status: 'Active', require_mfa_reenrol: requireMfa },
                'User reactivated'
            );
            
            updateTableRowStatus(userId, 'Active');
            updateTableRowMfa(userId, requireMfa ? 'Not Enrolled' : user.mfa_status);
            updateStats();
            
            bootstrap.Modal.getInstance(document.getElementById('reactivateUserModal')).hide();
            
            var msg = user.name + ' has been reactivated.';
            if (requireMfa) msg += ' MFA re-enrollment required on next login.';
            showToast(msg, 'success');
            console.log('[AdminUsers] User reactivated:', { userId: userId, requireMfa: requireMfa });
        })
        .catch(function(err) {
            showErrorToast('Failed to reactivate user: ' + err.error, err.refId);
        })
        .finally(function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-user-check me-1"></i> Reactivate User';
        });
}

function archiveUser(userId) {
    var user = allUsers.find(function(u) { return u.id === userId; });
    if (!user) {
        showToast('User not found', 'error');
        return;
    }
    if (user.status !== 'Suspended') {
        showToast('Only suspended users can be archived', 'error');
        return;
    }
    
    document.getElementById('archiveUserId').value = userId;
    document.getElementById('archiveUserName').textContent = user.name;
    document.getElementById('archiveReason').value = '';
    document.getElementById('archiveReason').classList.remove('is-invalid');
    document.getElementById('confirmArchiveCheck').checked = false;
    document.getElementById('confirmArchiveBtn').disabled = true;
    closeUserDetail();
    new bootstrap.Modal(document.getElementById('archiveUserModal')).show();
}

function toggleArchiveBtn() {
    var checked = document.getElementById('confirmArchiveCheck').checked;
    document.getElementById('confirmArchiveBtn').disabled = !checked;
}

function confirmArchive() {
    var userId = document.getElementById('archiveUserId').value;
    var reason = document.getElementById('archiveReason').value.trim();
    var confirmed = document.getElementById('confirmArchiveCheck').checked;
    var user = allUsers.find(function(u) { return u.id === userId; });
    
    if (!reason) {
        document.getElementById('archiveReason').classList.add('is-invalid');
        return;
    }
    
    if (!confirmed) {
        showToast('Please confirm you understand this action is permanent', 'warning');
        return;
    }
    
    if (!user || user.status !== 'Suspended') {
        showToast('Cannot archive: Only suspended users can be archived', 'error');
        bootstrap.Modal.getInstance(document.getElementById('archiveUserModal')).hide();
        return;
    }
    
    var btn = document.getElementById('confirmArchiveBtn');
    var previousStatus = user.status;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Archiving...';
    
    AdminUsersService.archiveUser(userId, reason)
        .then(function(response) {
            user.status = 'Archived';
            user.archived_at = new Date().toISOString();
            user.archived_reason = reason;
            
            logAdminUserAudit('ADMIN_USER_ARCHIVED', user.email, 
                { status: previousStatus }, 
                { status: 'Archived' },
                reason
            );
            
            updateTableRowStatus(userId, 'Archived');
            updateStats();
            
            bootstrap.Modal.getInstance(document.getElementById('archiveUserModal')).hide();
            showToast(user.name + ' has been permanently archived.', 'warning');
            console.log('[AdminUsers] User archived:', { userId: userId, reason: reason });
        })
        .catch(function(err) {
            showErrorToast('Failed to archive user: ' + err.error, err.refId);
        })
        .finally(function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-archive me-1"></i> Archive Permanently';
        });
}

function updateTableRowStatus(userId, newStatus) {
    var row = document.querySelector('tr[data-id="' + userId + '"]');
    if (!row) return;
    
    var statusCell = row.querySelector('td:nth-child(3)');
    if (!statusCell) return;
    
    var statusClass = {
        'Active': 'badge-active',
        'Invited': 'badge-invited',
        'Suspended': 'badge-suspended',
        'Archived': 'badge-archived'
    }[newStatus] || 'badge-archived';
    
    statusCell.innerHTML = '<span class="badge-pill ' + statusClass + '">' + newStatus + '</span>';
    highlightRow(userId);
}

function updateTableRowMfa(userId, mfaStatus) {
    var row = document.querySelector('tr[data-id="' + userId + '"]');
    if (!row) return;
    
    var mfaCell = row.querySelector('td:nth-child(4)');
    if (!mfaCell) return;
    
    var mfaClass = mfaStatus === 'Enrolled' ? 'badge-enrolled' : 'badge-not-enrolled';
    mfaCell.innerHTML = '<span class="badge-pill ' + mfaClass + '">' + mfaStatus + '</span>';
}

var currentAdminRole = '{{ session("admin_role", "super_admin") }}';

function resetPassword(userId) {
    var user = allUsers.find(function(u) { return u.id === userId; });
    if (!user) {
        showToast('User not found', 'error');
        return;
    }
    if (user.status === 'Archived' || user.status === 'Invited') {
        showToast('Cannot reset password for ' + user.status.toLowerCase() + ' users', 'error');
        return;
    }
    
    document.getElementById('resetPasswordUserId').value = userId;
    document.getElementById('resetPasswordUserName').textContent = user.name;
    document.getElementById('resetPasswordEmail').textContent = user.email;
    closeUserDetail();
    new bootstrap.Modal(document.getElementById('resetPasswordModal')).show();
}

function confirmResetPassword() {
    var userId = document.getElementById('resetPasswordUserId').value;
    var user = allUsers.find(function(u) { return u.id === userId; });
    
    if (!user) {
        showToast('User not found', 'error');
        bootstrap.Modal.getInstance(document.getElementById('resetPasswordModal')).hide();
        return;
    }
    
    var btn = document.getElementById('confirmResetPasswordBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Sending...';
    
    AdminUsersService.resetPassword(userId)
        .then(function(response) {
            user.active_sessions = 0;
            user.password_reset_sent = new Date().toISOString();
            
            logAdminUserAudit('ADMIN_USER_PASSWORD_RESET', user.email, 
                null, 
                { password_reset_sent: true }
            );
            
            bootstrap.Modal.getInstance(document.getElementById('resetPasswordModal')).hide();
            showToast('Password reset email sent to ' + user.email + '. All sessions revoked.', 'success');
            console.log('[AdminUsers][Security] Password reset:', { userId: userId, email: user.email });
        })
        .catch(function(err) {
            showErrorToast('Failed to send password reset: ' + err.error, err.refId);
        })
        .finally(function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane me-1"></i> Send Reset Email';
        });
}

function forceLogout(userId) {
    var user = allUsers.find(function(u) { return u.id === userId; });
    if (!user) {
        showToast('User not found', 'error');
        return;
    }
    if (user.status !== 'Active') {
        showToast('Can only force logout active users', 'error');
        return;
    }
    if (user.active_sessions === 0) {
        showToast('User has no active sessions', 'info');
        return;
    }
    
    document.getElementById('forceLogoutUserId').value = userId;
    document.getElementById('forceLogoutUserName').textContent = user.name;
    document.getElementById('forceLogoutSessionCount').textContent = user.active_sessions || 0;
    closeUserDetail();
    new bootstrap.Modal(document.getElementById('forceLogoutModal')).show();
}

function confirmForceLogout() {
    var userId = document.getElementById('forceLogoutUserId').value;
    var user = allUsers.find(function(u) { return u.id === userId; });
    
    if (!user) {
        showToast('User not found', 'error');
        bootstrap.Modal.getInstance(document.getElementById('forceLogoutModal')).hide();
        return;
    }
    
    var btn = document.getElementById('confirmForceLogoutBtn');
    var sessionCount = user.active_sessions || 0;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Terminating...';
    
    AdminUsersService.forceLogout(userId)
        .then(function(response) {
            user.active_sessions = 0;
            
            logAdminUserAudit('ADMIN_USER_SESSIONS_REVOKED', user.email, 
                null, 
                { sessions_revoked: sessionCount }
            );
            
            bootstrap.Modal.getInstance(document.getElementById('forceLogoutModal')).hide();
            showToast(sessionCount + ' session(s) terminated for ' + user.name, 'warning');
            console.log('[AdminUsers][Security] Force logout:', { userId: userId, sessionsTerminated: sessionCount });
        })
        .catch(function(err) {
            showErrorToast('Failed to terminate sessions: ' + err.error, err.refId);
        })
        .finally(function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-sign-out-alt me-1"></i> Force Logout';
        });
}

function resetMfa(userId) {
    var user = allUsers.find(function(u) { return u.id === userId; });
    if (!user) {
        showToast('User not found', 'error');
        return;
    }
    if (user.status === 'Archived' || user.status === 'Invited') {
        showToast('Cannot reset MFA for ' + user.status.toLowerCase() + ' users', 'error');
        return;
    }
    
    document.getElementById('resetMfaUserId').value = userId;
    document.getElementById('resetMfaUserName').textContent = user.name;
    document.getElementById('mfaReenroll').checked = true;
    document.getElementById('mfaDisableReason').value = '';
    document.getElementById('tempDisableWarning').classList.add('d-none');
    
    var tempDisableContainer = document.getElementById('tempDisableContainer');
    if (currentAdminRole !== 'super_admin') {
        tempDisableContainer.style.display = 'none';
    } else {
        tempDisableContainer.style.display = 'block';
    }
    
    closeUserDetail();
    new bootstrap.Modal(document.getElementById('resetMfaModal')).show();
}

function toggleTempDisableMfa() {
    var isTempDisable = document.getElementById('mfaTempDisable').checked;
    var warningEl = document.getElementById('tempDisableWarning');
    var btn = document.getElementById('confirmResetMfaBtn');
    
    if (isTempDisable) {
        warningEl.classList.remove('d-none');
        btn.classList.remove('btn');
        btn.classList.add('btn', 'btn-danger');
        btn.style.background = '';
        btn.style.color = '';
        btn.innerHTML = '<i class="fas fa-exclamation-triangle me-1"></i> Disable MFA';
    } else {
        warningEl.classList.add('d-none');
        btn.classList.remove('btn-danger');
        btn.style.background = '#1e3a5f';
        btn.style.color = 'white';
        btn.innerHTML = '<i class="fas fa-shield-alt me-1"></i> Reset MFA';
    }
}

function confirmResetMfa() {
    var userId = document.getElementById('resetMfaUserId').value;
    var isTempDisable = document.getElementById('mfaTempDisable').checked;
    var reason = document.getElementById('mfaDisableReason').value.trim();
    var user = allUsers.find(function(u) { return u.id === userId; });
    
    if (!user) {
        showToast('User not found', 'error');
        bootstrap.Modal.getInstance(document.getElementById('resetMfaModal')).hide();
        return;
    }
    
    if (isTempDisable && !reason) {
        document.getElementById('mfaDisableReason').classList.add('is-invalid');
        showToast('Justification required for temporarily disabling MFA', 'error');
        return;
    }
    
    if (isTempDisable && currentAdminRole !== 'super_admin') {
        showToast('Only Super Admins can temporarily disable MFA', 'error');
        return;
    }
    
    var btn = document.getElementById('confirmResetMfaBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Processing...';
    
    AdminUsersService.resetMfa(userId, isTempDisable, reason)
        .then(function(response) {
            user.mfa_status = 'Not Enrolled';
            user.mfa_method = null;
            if (isTempDisable) {
                user.mfa_temp_disabled = true;
                user.mfa_temp_disabled_reason = reason;
                user.mfa_temp_disabled_at = new Date().toISOString();
            } else {
                user.mfa_temp_disabled = false;
                user.require_mfa_reenrol = true;
            }
            
            logAdminUserAudit('ADMIN_USER_MFA_RESET', user.email, 
                { mfa_enrolled: true }, 
                { mfa_reset: true, temporary_disable: isTempDisable, disable_hours: isTempDisable ? 24 : null }
            );
            
            updateTableRowMfa(userId, 'Not Enrolled');
            
            bootstrap.Modal.getInstance(document.getElementById('resetMfaModal')).hide();
            
            if (isTempDisable) {
                showToast('MFA temporarily disabled for ' + user.name + '. Action logged.', 'warning');
                console.log('[AdminUsers][Security][CRITICAL] MFA temporarily disabled:', { userId: userId, reason: reason });
            } else {
                showToast('MFA reset. ' + user.name + ' must re-enroll on next login.', 'success');
                console.log('[AdminUsers][Security] MFA reset:', { userId: userId });
            }
        })
        .catch(function(err) {
            showErrorToast('Failed to reset MFA: ' + err.error, err.refId);
        })
        .finally(function() {
            btn.disabled = false;
            btn.style.background = '#1e3a5f';
            btn.style.color = 'white';
            btn.innerHTML = '<i class="fas fa-shield-alt me-1"></i> Reset MFA';
        });
}

function updateMfaDetails(userId) {
    var user = allUsers.find(function(u) { return u.id === userId; });
    if (!user) {
        showToast('User not found', 'error');
        return;
    }
    if (user.status === 'Archived' || user.status === 'Invited') {
        showToast('Cannot update MFA for ' + user.status.toLowerCase() + ' users', 'error');
        return;
    }
    
    document.getElementById('updateMfaUserId').value = userId;
    document.getElementById('updateMfaUserName').textContent = user.name;
    document.getElementById('updateMfaMethod').value = user.mfa_method || 'Authenticator';
    document.getElementById('updateMfaPhone').value = user.mfa_phone || '';
    document.getElementById('currentMfaStatus').textContent = user.mfa_status || 'Not Enrolled';
    
    toggleSmsPhoneField();
    closeUserDetail();
    new bootstrap.Modal(document.getElementById('updateMfaModal')).show();
}

function toggleSmsPhoneField() {
    var method = document.getElementById('updateMfaMethod').value;
    var phoneContainer = document.getElementById('smsPhoneContainer');
    if (method === 'SMS' || method === 'Both') {
        phoneContainer.style.display = 'block';
    } else {
        phoneContainer.style.display = 'none';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    var methodSelect = document.getElementById('updateMfaMethod');
    if (methodSelect) {
        methodSelect.addEventListener('change', toggleSmsPhoneField);
    }
});

function confirmUpdateMfa() {
    var userId = document.getElementById('updateMfaUserId').value;
    var method = document.getElementById('updateMfaMethod').value;
    var phone = document.getElementById('updateMfaPhone').value.trim();
    var user = allUsers.find(function(u) { return u.id === userId; });
    
    if (!user) {
        showToast('User not found', 'error');
        bootstrap.Modal.getInstance(document.getElementById('updateMfaModal')).hide();
        return;
    }
    
    if ((method === 'SMS' || method === 'Both') && !phone) {
        showToast('Phone number required for SMS OTP', 'error');
        document.getElementById('updateMfaPhone').classList.add('is-invalid');
        return;
    }
    
    var btn = document.getElementById('confirmUpdateMfaBtn');
    var previousMethod = user.mfa_method;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving...';
    
    AdminUsersService.updateMfa(userId, method, phone)
        .then(function(response) {
            user.mfa_method = method;
            if (method === 'SMS' || method === 'Both') {
                user.mfa_phone = phone;
            }
            
            logAdminUserAudit('ADMIN_USER_MFA_UPDATED', user.email, 
                { mfa_method: previousMethod }, 
                { mfa_method: method, mfa_phone: phone || null }
            );
            
            var row = document.querySelector('tr[data-id="' + userId + '"]');
            if (row) {
                var methodCell = row.querySelector('td:nth-child(5)');
                if (methodCell) methodCell.textContent = method;
            }
            
            bootstrap.Modal.getInstance(document.getElementById('updateMfaModal')).hide();
            showToast('MFA settings updated for ' + user.name, 'success');
            console.log('[AdminUsers][Security] MFA settings updated:', { userId: userId, method: method });
        })
        .catch(function(err) {
            showErrorToast('Failed to update MFA settings: ' + err.error, err.refId);
        })
        .finally(function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-save me-1"></i> Save Changes';
        });
}

function updateEmail(userId) {
    var user = allUsers.find(function(u) { return u.id === userId; });
    if (!user) {
        showToast('User not found', 'error');
        return;
    }
    if (user.status === 'Archived') {
        showToast('Cannot update email for archived users', 'error');
        return;
    }
    
    document.getElementById('updateEmailUserId').value = userId;
    document.getElementById('updateEmailUserName').textContent = user.name;
    document.getElementById('currentEmail').value = user.email;
    document.getElementById('newEmail').value = '';
    document.getElementById('newEmail').classList.remove('is-invalid');
    document.getElementById('emailChangeReason').value = '';
    document.getElementById('emailChangeReason').classList.remove('is-invalid');
    
    closeUserDetail();
    new bootstrap.Modal(document.getElementById('updateEmailModal')).show();
}

function confirmUpdateEmail() {
    var userId = document.getElementById('updateEmailUserId').value;
    var newEmail = document.getElementById('newEmail').value.trim();
    var reason = document.getElementById('emailChangeReason').value.trim();
    var user = allUsers.find(function(u) { return u.id === userId; });
    
    if (!user) {
        showToast('User not found', 'error');
        bootstrap.Modal.getInstance(document.getElementById('updateEmailModal')).hide();
        return;
    }
    
    var isValid = true;
    
    if (!newEmail || !newEmail.endsWith('@quicksms.co.uk')) {
        document.getElementById('newEmail').classList.add('is-invalid');
        isValid = false;
    } else {
        document.getElementById('newEmail').classList.remove('is-invalid');
    }
    
    if (!reason) {
        document.getElementById('emailChangeReason').classList.add('is-invalid');
        isValid = false;
    } else {
        document.getElementById('emailChangeReason').classList.remove('is-invalid');
    }
    
    if (!isValid) {
        return;
    }
    
    var existingUser = allUsers.find(function(u) { return u.email.toLowerCase() === newEmail.toLowerCase() && u.id !== userId; });
    if (existingUser) {
        document.getElementById('newEmail').classList.add('is-invalid');
        document.getElementById('newEmailError').textContent = 'This email is already in use';
        showToast('Email address already in use', 'error');
        return;
    }
    
    var btn = document.getElementById('confirmUpdateEmailBtn');
    var oldEmail = user.email;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Updating...';
    
    AdminUsersService.updateEmail(userId, newEmail, reason)
        .then(function(response) {
            user.email = newEmail;
            user.email_verified = false;
            user.active_sessions = 0;
            user.email_change_reason = reason;
            user.email_changed_at = new Date().toISOString();
            
            logAdminUserAudit('ADMIN_USER_EMAIL_UPDATED', newEmail, 
                { email: oldEmail }, 
                { email: newEmail },
                reason
            );
            
            var row = document.querySelector('tr[data-id="' + userId + '"]');
            if (row) {
                var emailCell = row.querySelector('td:nth-child(2)');
                if (emailCell) emailCell.textContent = newEmail;
            }
            
            bootstrap.Modal.getInstance(document.getElementById('updateEmailModal')).hide();
            showToast('Email updated. Verification email sent. All sessions revoked.', 'success');
            console.log('[AdminUsers][Security] Email changed:', { userId: userId, from: oldEmail, to: newEmail, reason: reason });
        })
        .catch(function(err) {
            showErrorToast('Failed to update email: ' + err.error, err.refId);
        })
        .finally(function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-save me-1"></i> Update Email';
        });
}

function terminateSessions(userId) {
    forceLogout(userId);
}

function resendInvite(userId) {
    var user = allUsers.find(function(u) { return u.id === userId; });
    if (!user || user.status !== 'Invited') {
        showToast('Cannot resend: User is not in invited state', 'error');
        return;
    }
    
    showToast('Resending invitation to ' + user.email + '...', 'info');
    
    AdminUsersService.resendInvite(userId)
        .then(function(response) {
            user.invite_sent_at = new Date().toISOString();
            
            logAdminUserAudit('ADMIN_USER_INVITE_RESENT', user.email, null, null);
            
            showToast('Invitation resent to ' + user.email, 'success');
            console.log('[AdminUsers] Invite resent:', userId);
        })
        .catch(function(err) {
            showErrorToast('Failed to resend invitation: ' + err.error, err.refId);
        });
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

function canSuspend(status) { return status === 'Active'; }
function canReactivate(status) { return status === 'Suspended'; }
function canArchive(status) { return status === 'Suspended'; }

var supportSession = {
    active: false,
    userId: null,
    userName: null,
    startTime: null,
    duration: 0,
    timer: null,
    reason: null
};

function impersonateUser(userId) {
    if (currentAdminRole !== 'super_admin') {
        showToast('Only Super Admins can impersonate users', 'error');
        return;
    }
    
    var user = allUsers.find(function(u) { return u.id === userId; });
    if (!user) {
        showToast('User not found', 'error');
        return;
    }
    if (user.status !== 'Active') {
        showToast('Can only impersonate active users', 'error');
        return;
    }
    
    document.getElementById('impersonateUserId').value = userId;
    document.getElementById('impersonateUserName').textContent = user.name;
    document.getElementById('impersonateUserEmail').textContent = user.email;
    document.getElementById('impersonateUserRole').textContent = user.role + ' - ' + user.department;
    document.getElementById('impersonateDuration').value = '30';
    document.getElementById('impersonateReason').value = '';
    document.getElementById('impersonateReason').classList.remove('is-invalid');
    document.getElementById('impersonateAcknowledge').checked = false;
    document.getElementById('confirmImpersonateBtn').disabled = true;
    
    closeUserDetail();
    new bootstrap.Modal(document.getElementById('impersonateUserModal')).show();
}

function toggleImpersonateBtn() {
    var checked = document.getElementById('impersonateAcknowledge').checked;
    document.getElementById('confirmImpersonateBtn').disabled = !checked;
}

function confirmImpersonate() {
    var userId = document.getElementById('impersonateUserId').value;
    var duration = parseInt(document.getElementById('impersonateDuration').value);
    var reason = document.getElementById('impersonateReason').value.trim();
    var user = allUsers.find(function(u) { return u.id === userId; });
    
    if (!reason) {
        document.getElementById('impersonateReason').classList.add('is-invalid');
        showToast('Reason is required for audit trail', 'error');
        return;
    }
    
    if (currentAdminRole !== 'super_admin') {
        showToast('Only Super Admins can impersonate users', 'error');
        bootstrap.Modal.getInstance(document.getElementById('impersonateUserModal')).hide();
        return;
    }
    
    var btn = document.getElementById('confirmImpersonateBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Starting Session...';
    
    var sessionId = 'IMP-' + Date.now();
    console.log('[AdminUsers][Impersonation][AUDIT] Session started:', {
        sessionId: sessionId,
        adminEmail: '{{ session("admin_email", "admin@quicksms.co.uk") }}',
        targetUserId: userId,
        targetUserEmail: user.email,
        duration: duration + ' minutes',
        reason: reason,
        timestamp: new Date().toISOString(),
        auditType: 'INTERNAL_ADMIN_ONLY'
    });
    
    setTimeout(function() {
        supportSession.active = true;
        supportSession.userId = userId;
        supportSession.userName = user.name;
        supportSession.startTime = Date.now();
        supportSession.duration = duration * 60;
        supportSession.reason = reason;
        supportSession.sessionId = sessionId;
        
        document.getElementById('supportModeUserName').textContent = user.name;
        document.getElementById('supportModeBanner').classList.remove('d-none');
        document.body.classList.add('support-mode-active');
        
        startSupportTimer();
        applyPiiMasking();
        
        bootstrap.Modal.getInstance(document.getElementById('impersonateUserModal')).hide();
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-user-secret me-1"></i> Start Support Session';
        
        showToast('Support session started for ' + user.name + '. PII areas are masked.', 'warning');
    }, 800);
}

function startSupportTimer() {
    updateSupportTimer();
    supportSession.timer = setInterval(function() {
        var elapsed = Math.floor((Date.now() - supportSession.startTime) / 1000);
        var remaining = supportSession.duration - elapsed;
        
        if (remaining <= 0) {
            endSupportSession(true);
        } else {
            updateSupportTimer();
            if (remaining <= 300 && remaining % 60 === 0) {
                showToast('Support session expires in ' + Math.floor(remaining / 60) + ' minutes', 'warning');
            }
        }
    }, 1000);
}

function updateSupportTimer() {
    var elapsed = Math.floor((Date.now() - supportSession.startTime) / 1000);
    var remaining = Math.max(0, supportSession.duration - elapsed);
    var minutes = Math.floor(remaining / 60);
    var seconds = remaining % 60;
    document.getElementById('supportModeTimer').textContent = 
        String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');
}

function endSupportSession(expired) {
    if (supportSession.timer) {
        clearInterval(supportSession.timer);
        supportSession.timer = null;
    }
    
    console.log('[AdminUsers][Impersonation][AUDIT] Session ended:', {
        sessionId: supportSession.sessionId,
        targetUserId: supportSession.userId,
        targetUserName: supportSession.userName,
        reason: supportSession.reason,
        endType: expired ? 'EXPIRED' : 'MANUAL',
        duration: Math.floor((Date.now() - supportSession.startTime) / 1000) + ' seconds',
        timestamp: new Date().toISOString(),
        auditType: 'INTERNAL_ADMIN_ONLY'
    });
    
    document.getElementById('supportModeBanner').classList.add('d-none');
    document.body.classList.remove('support-mode-active');
    removePiiMasking();
    
    var userName = supportSession.userName;
    supportSession = {
        active: false,
        userId: null,
        userName: null,
        startTime: null,
        duration: 0,
        timer: null,
        reason: null
    };
    
    if (expired) {
        showToast('Support session for ' + userName + ' has expired.', 'info');
    } else {
        showToast('Support session ended.', 'info');
    }
}

function applyPiiMasking() {
    var piiSelectors = [
        '.contact-list',
        '.message-content',
        '.export-button',
        '[data-pii="true"]',
        '.phone-number',
        '.customer-email'
    ];
    
    piiSelectors.forEach(function(selector) {
        document.querySelectorAll(selector).forEach(function(el) {
            el.classList.add('pii-masked');
        });
    });
    
    console.log('[AdminUsers][Impersonation] PII masking applied');
}

function removePiiMasking() {
    document.querySelectorAll('.pii-masked').forEach(function(el) {
        el.classList.remove('pii-masked');
    });
    console.log('[AdminUsers][Impersonation] PII masking removed');
}

function logAdminUserAudit(eventType, targetEmail, beforeValues, afterValues, reason) {
    fetch('/admin/api/admin-users/audit', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            event_type: eventType,
            target_admin_email: targetEmail,
            before_values: beforeValues || null,
            after_values: afterValues || null,
            reason: reason || null
        })
    }).then(function(response) {
        return response.json();
    }).then(function(data) {
        console.log('[AdminAudit] Event logged:', eventType, data);
    }).catch(function(error) {
        console.error('[AdminAudit] Failed to log event:', eventType, error);
    });
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
    toastEl.addEventListener('hidden.bs.toast', function() { toastEl.remove(); });
}
</script>
@endpush
