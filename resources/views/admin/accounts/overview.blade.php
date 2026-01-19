@extends('layouts.admin')

@section('title', 'Account Overview')

@push('styles')
<style>
.admin-page { padding: 1.5rem; }
.account-filters { margin-bottom: 1.5rem; }
.kpi-tile-row { margin-bottom: 1.5rem; }
.kpi-tile-row .widget-stat { cursor: pointer; transition: all 0.2s ease; border: 2px solid transparent; }
.kpi-tile-row .widget-stat:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
.kpi-tile-row .widget-stat.active { border-color: #1e3a5f; }
.kpi-tile-row .widget-stat .card-body { padding: 1rem; }
.kpi-tile-row .widget-stat h4 { font-size: 1.5rem; margin-bottom: 0; }
.kpi-tile-row .widget-stat p { font-size: 0.8rem; margin-bottom: 0.25rem; }
</style>
@endpush

@section('content')
<div class="admin-page">
    <div class="admin-breadcrumb">
        <a href="{{ route('admin.dashboard') }}">Admin</a>
        <span class="separator">/</span>
        <a href="#">Accounts</a>
        <span class="separator">/</span>
        <span>Overview</span>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 style="color: #1e3a5f; font-weight: 600;">Account Overview</h4>
            <p class="text-muted mb-0">Manage all client accounts across the platform</p>
        </div>
    </div>

    <!-- KPI Tiles Row -->
    <div class="row kpi-tile-row">
        <div class="col-xl-3 col-lg-4 col-sm-6 mb-3">
            <div class="widget-stat card" onclick="filterAccounts('live')" data-filter="live" data-bs-toggle="tooltip" title="COUNT(*) WHERE status = 'live'">
                <div class="card-body">
                    <div class="media ai-icon">
                        <span class="me-3 bgl-success text-success">
                            <i class="fas fa-check-circle"></i>
                        </span>
                        <div class="media-body">
                            <p class="mb-1">Active Accounts</p>
                            <h4 class="mb-0">847</h4>
                            <small class="text-success">Live</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-4 col-sm-6 mb-3">
            <div class="widget-stat card" onclick="filterAccounts('test')" data-filter="test" data-bs-toggle="tooltip" title="COUNT(*) WHERE status = 'test'">
                <div class="card-body">
                    <div class="media ai-icon">
                        <span class="me-3 bgl-info text-info">
                            <i class="fas fa-flask"></i>
                        </span>
                        <div class="media-body">
                            <p class="mb-1">Test Accounts</p>
                            <h4 class="mb-0">156</h4>
                            <small class="text-info">Testing</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-4 col-sm-6 mb-3">
            <div class="widget-stat card" onclick="filterAccounts('suspended')" data-filter="suspended" data-bs-toggle="tooltip" title="COUNT(*) WHERE status = 'suspended'">
                <div class="card-body">
                    <div class="media ai-icon">
                        <span class="me-3 bgl-danger text-danger">
                            <i class="fas fa-ban"></i>
                        </span>
                        <div class="media-body">
                            <p class="mb-1">Suspended Accounts</p>
                            <h4 class="mb-0">23</h4>
                            <small class="text-danger">Blocked</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-4 col-sm-6 mb-3">
            <div class="widget-stat card" onclick="filterAccounts('pending')" data-filter="pending" data-bs-toggle="tooltip" title="COUNT(*) WHERE approval_status = 'pending'">
                <div class="card-body">
                    <div class="media ai-icon">
                        <span class="me-3 bgl-warning text-warning">
                            <i class="fas fa-clock"></i>
                        </span>
                        <div class="media-body">
                            <p class="mb-1">Pending Approvals</p>
                            <h4 class="mb-0">12</h4>
                            <small class="text-warning">Awaiting</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-4 col-sm-6 mb-3">
            <div class="widget-stat card" onclick="filterAccounts('senderid')" data-filter="senderid" data-bs-toggle="tooltip" title="COUNT(*) FROM sender_id_requests WHERE status = 'pending'">
                <div class="card-body">
                    <div class="media ai-icon">
                        <span class="me-3 bgl-primary text-primary">
                            <i class="fas fa-id-badge"></i>
                        </span>
                        <div class="media-body">
                            <p class="mb-1">Sender ID Requests</p>
                            <h4 class="mb-0">8</h4>
                            <small class="text-primary">Pending</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-4 col-sm-6 mb-3">
            <div class="widget-stat card" onclick="filterAccounts('rcs')" data-filter="rcs" data-bs-toggle="tooltip" title="COUNT(*) FROM rcs_registrations WHERE status = 'pending'">
                <div class="card-body">
                    <div class="media ai-icon">
                        <span class="me-3 bgl-secondary text-secondary">
                            <i class="fas fa-comments"></i>
                        </span>
                        <div class="media-body">
                            <p class="mb-1">RCS Registrations</p>
                            <h4 class="mb-0">5</h4>
                            <small class="text-secondary">Pending</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-4 col-sm-6 mb-3">
            <div class="widget-stat card" onclick="filterAccounts('testnumber')" data-filter="testnumber" data-bs-toggle="tooltip" title="COUNT(*) FROM test_number_requests WHERE status = 'pending'">
                <div class="card-body">
                    <div class="media ai-icon">
                        <span class="me-3 bgl-info text-info">
                            <i class="fas fa-phone"></i>
                        </span>
                        <div class="media-body">
                            <p class="mb-1">Test Number Requests</p>
                            <h4 class="mb-0">3</h4>
                            <small class="text-info">Pending</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-4 col-sm-6 mb-3">
            <div class="widget-stat card" onclick="filterAccounts('flagged')" data-filter="flagged" data-bs-toggle="tooltip" title="COUNT(*) WHERE fraud_flag = true OR risk_level = 'high'">
                <div class="card-body">
                    <div class="media ai-icon">
                        <span class="me-3 bgl-danger text-danger">
                            <i class="fas fa-exclamation-triangle"></i>
                        </span>
                        <div class="media-body">
                            <p class="mb-1">Fraud / Risk</p>
                            <h4 class="mb-0">4</h4>
                            <small class="text-danger">Flagged</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="admin-filter-bar">
        <div class="filter-group">
            <label>Status</label>
            <select class="form-select form-select-sm">
                <option>All Statuses</option>
                <option>Live</option>
                <option>Test</option>
                <option>Suspended</option>
                <option>Archived</option>
            </select>
        </div>
        <div class="filter-group">
            <label>Account Type</label>
            <select class="form-select form-select-sm">
                <option>All Types</option>
                <option>Enterprise</option>
                <option>SMB</option>
                <option>Startup</option>
            </select>
        </div>
        <div class="filter-group">
            <label>Search</label>
            <input type="text" class="form-control form-control-sm" placeholder="Account ID or name...">
        </div>
        <button class="btn admin-btn-apply">Apply</button>
    </div>

    <div class="admin-card">
        <div class="card-body p-0">
            <table class="table admin-table mb-0">
                <thead>
                    <tr>
                        <th>Account</th>
                        <th>Status</th>
                        <th>Type</th>
                        <th>Created</th>
                        <th class="text-end">MTD Volume</th>
                        <th class="text-end">MTD Revenue</th>
                        <th class="text-end">Balance</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <div class="admin-account-card" style="padding: 0; border: none;">
                                <div class="account-avatar" style="width: 32px; height: 32px; font-size: 0.75rem;">AC</div>
                                <div>
                                    <div class="account-name">Acme Corporation</div>
                                    <div class="account-id">ACC-1234</div>
                                </div>
                            </div>
                        </td>
                        <td><span class="admin-status-badge live">Live</span></td>
                        <td>Enterprise</td>
                        <td>Jan 15, 2024</td>
                        <td class="text-end">1,247,832</td>
                        <td class="text-end">£24,892</td>
                        <td class="text-end">£5,420</td>
                        <td>
                            <div class="admin-quick-actions">
                                <button class="btn btn-outline-primary btn-sm">View</button>
                                <button class="btn btn-outline-secondary btn-sm">Impersonate</button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="admin-account-card" style="padding: 0; border: none;">
                                <div class="account-avatar" style="width: 32px; height: 32px; font-size: 0.75rem;">FL</div>
                                <div>
                                    <div class="account-name">Finance Ltd</div>
                                    <div class="account-id">ACC-5678</div>
                                </div>
                            </div>
                        </td>
                        <td><span class="admin-status-badge live">Live</span></td>
                        <td>Enterprise</td>
                        <td>Mar 02, 2024</td>
                        <td class="text-end">892,156</td>
                        <td class="text-end">£18,432</td>
                        <td class="text-end">£12,100</td>
                        <td>
                            <div class="admin-quick-actions">
                                <button class="btn btn-outline-primary btn-sm">View</button>
                                <button class="btn btn-outline-secondary btn-sm">Impersonate</button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="admin-account-card" style="padding: 0; border: none;">
                                <div class="account-avatar" style="width: 32px; height: 32px; font-size: 0.75rem;">NC</div>
                                <div>
                                    <div class="account-name">NewClient</div>
                                    <div class="account-id">ACC-7890</div>
                                </div>
                            </div>
                        </td>
                        <td><span class="admin-status-badge test">Test</span></td>
                        <td>Startup</td>
                        <td>Jan 18, 2026</td>
                        <td class="text-end">47</td>
                        <td class="text-end">£0</td>
                        <td class="text-end">£0</td>
                        <td>
                            <div class="admin-quick-actions">
                                <button class="btn btn-outline-primary btn-sm">View</button>
                                <button class="btn btn-outline-success btn-sm">Activate</button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="admin-account-card" style="padding: 0; border: none;">
                                <div class="account-avatar" style="width: 32px; height: 32px; font-size: 0.75rem;">TC</div>
                                <div>
                                    <div class="account-name">TestCo</div>
                                    <div class="account-id">ACC-4567</div>
                                </div>
                            </div>
                        </td>
                        <td><span class="admin-status-badge suspended">Suspended</span></td>
                        <td>SMB</td>
                        <td>Nov 10, 2025</td>
                        <td class="text-end">0</td>
                        <td class="text-end">£0</td>
                        <td class="text-end">-£240</td>
                        <td>
                            <div class="admin-quick-actions">
                                <button class="btn btn-outline-primary btn-sm">View</button>
                                <button class="btn btn-outline-warning btn-sm">Reactivate</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

var currentFilter = null;

window.filterAccounts = function(filter) {
    var tiles = document.querySelectorAll('.kpi-tile-row .widget-stat');
    
    // Toggle active state
    if (currentFilter === filter) {
        // Clear filter
        currentFilter = null;
        tiles.forEach(function(tile) {
            tile.classList.remove('active');
        });
        // Reset table to show all
        filterTable(null);
    } else {
        // Set new filter
        currentFilter = filter;
        tiles.forEach(function(tile) {
            tile.classList.remove('active');
            if (tile.dataset.filter === filter) {
                tile.classList.add('active');
            }
        });
        filterTable(filter);
    }

    // Audit log
    if (typeof AdminControlPlane !== 'undefined') {
        AdminControlPlane.logAdminAction('ACCOUNTS_FILTER_APPLIED', 'ACCOUNTS', {
            filter: filter,
            action: currentFilter ? 'applied' : 'cleared'
        });
    }
};

function filterTable(filter) {
    var rows = document.querySelectorAll('.admin-table tbody tr');
    
    if (!filter) {
        // Show all rows
        rows.forEach(function(row) {
            row.style.display = '';
        });
        return;
    }

    rows.forEach(function(row) {
        var statusBadge = row.querySelector('.admin-status-badge');
        var status = statusBadge ? statusBadge.textContent.toLowerCase().trim() : '';
        var show = false;

        switch(filter) {
            case 'live':
                show = status === 'live';
                break;
            case 'test':
                show = status === 'test';
                break;
            case 'suspended':
                show = status === 'suspended';
                break;
            case 'pending':
                show = status === 'pending' || row.textContent.toLowerCase().includes('pending');
                break;
            case 'senderid':
            case 'rcs':
            case 'testnumber':
                // These would filter based on related data - for now show pending
                show = status === 'pending' || status === 'test';
                break;
            case 'flagged':
                // Show suspended or accounts with negative balance
                show = status === 'suspended' || row.textContent.includes('-£');
                break;
            default:
                show = true;
        }

        row.style.display = show ? '' : 'none';
    });
}
</script>
@endpush
