@extends('layouts.admin')

@section('title', 'Sender ID Approvals')

@push('styles')
<style>
.admin-page { padding: 1.5rem; }
.approval-stats { display: flex; gap: 1rem; margin-bottom: 1.5rem; }
.approval-stat { background: #fff; padding: 1rem 1.5rem; border-radius: 8px; text-align: center; min-width: 120px; }
.approval-stat .count { font-size: 1.5rem; font-weight: 700; color: #1e3a5f; }
.approval-stat .label { font-size: 0.8rem; color: #64748b; }
.approval-stat.pending .count { color: #f59e0b; }
.approval-stat.approved .count { color: #059669; }
.approval-stat.rejected .count { color: #dc2626; }
</style>
@endpush

@section('content')
<div class="admin-page">
    <div class="admin-breadcrumb">
        <a href="{{ route('admin.dashboard') }}">Admin</a>
        <span class="separator">/</span>
        <a href="#">Messaging Assets</a>
        <span class="separator">/</span>
        <span>Sender ID Approvals</span>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 style="color: #1e3a5f; font-weight: 600;">Sender ID Approvals</h4>
            <p class="text-muted mb-0">Review and approve/reject Sender ID registration requests</p>
        </div>
    </div>

    <div class="approval-stats">
        <div class="approval-stat pending">
            <div class="count">7</div>
            <div class="label">Pending</div>
        </div>
        <div class="approval-stat approved">
            <div class="count">1,234</div>
            <div class="label">Approved</div>
        </div>
        <div class="approval-stat rejected">
            <div class="count">45</div>
            <div class="label">Rejected</div>
        </div>
        <div class="approval-stat">
            <div class="count">12</div>
            <div class="label">Under Review</div>
        </div>
    </div>

    <div class="admin-filter-bar">
        <div class="filter-group">
            <label>Status</label>
            <select class="form-select form-select-sm">
                <option>Pending Review</option>
                <option>All Statuses</option>
                <option>Approved</option>
                <option>Rejected</option>
                <option>Under Review</option>
            </select>
        </div>
        <div class="filter-group">
            <label>Type</label>
            <select class="form-select form-select-sm">
                <option>All Types</option>
                <option>Alphanumeric</option>
                <option>Numeric</option>
                <option>Shortcode</option>
            </select>
        </div>
        <div class="filter-group">
            <label>Search</label>
            <input type="text" class="form-control form-control-sm" placeholder="Sender ID or Account...">
        </div>
        <button class="btn admin-btn-apply">Apply</button>
    </div>

    <div class="admin-card">
        <div class="card-body p-0">
            <table class="table admin-table mb-0">
                <thead>
                    <tr>
                        <th>Sender ID</th>
                        <th>Type</th>
                        <th>Account</th>
                        <th>Submitted</th>
                        <th>Use Case</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>ALERTS24</strong></td>
                        <td>Alphanumeric</td>
                        <td>
                            <div class="admin-account-card" style="padding: 0; border: none;">
                                <div class="account-avatar" style="width: 28px; height: 28px; font-size: 0.7rem;">AC</div>
                                <div>
                                    <div class="account-name" style="font-size: 0.85rem;">Acme Corp</div>
                                    <div class="account-id" style="font-size: 0.7rem;">ACC-1234</div>
                                </div>
                            </div>
                        </td>
                        <td>2h ago</td>
                        <td>Transactional alerts</td>
                        <td><span class="admin-status-badge pending">Pending</span></td>
                        <td>
                            <div class="admin-quick-actions">
                                <button class="btn btn-success btn-sm">Approve</button>
                                <button class="btn btn-outline-danger btn-sm">Reject</button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>MYBANK</strong></td>
                        <td>Alphanumeric</td>
                        <td>
                            <div class="admin-account-card" style="padding: 0; border: none;">
                                <div class="account-avatar" style="width: 28px; height: 28px; font-size: 0.7rem;">FL</div>
                                <div>
                                    <div class="account-name" style="font-size: 0.85rem;">Finance Ltd</div>
                                    <div class="account-id" style="font-size: 0.7rem;">ACC-5678</div>
                                </div>
                            </div>
                        </td>
                        <td>4h ago</td>
                        <td>OTP and security</td>
                        <td><span class="admin-status-badge pending">Pending</span></td>
                        <td>
                            <div class="admin-quick-actions">
                                <button class="btn btn-success btn-sm">Approve</button>
                                <button class="btn btn-outline-danger btn-sm">Reject</button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>PROMO</strong></td>
                        <td>Alphanumeric</td>
                        <td>
                            <div class="admin-account-card" style="padding: 0; border: none;">
                                <div class="account-avatar" style="width: 28px; height: 28px; font-size: 0.7rem;">RC</div>
                                <div>
                                    <div class="account-name" style="font-size: 0.85rem;">Retail Co</div>
                                    <div class="account-id" style="font-size: 0.7rem;">ACC-9012</div>
                                </div>
                            </div>
                        </td>
                        <td>1d ago</td>
                        <td>Marketing campaigns</td>
                        <td><span class="admin-status-badge pending">Under Review</span></td>
                        <td>
                            <div class="admin-quick-actions">
                                <button class="btn btn-outline-primary btn-sm">Review</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
