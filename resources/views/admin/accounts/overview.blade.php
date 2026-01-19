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

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="accountsTable">
                    <thead>
                        <tr>
                            <th class="sortable" data-sort="name">Client Name</th>
                            <th class="sortable" data-sort="status">Account Status</th>
                            <th class="sortable text-end" data-sort="volume-year">Volume (Year)</th>
                            <th class="sortable text-end" data-sort="volume-month">Volume (Month)</th>
                            <th class="sortable text-end" data-sort="balance">Balance / Credit</th>
                            <th class="sortable" data-sort="pricing">Pricing Model</th>
                            <th>Risk Flags</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div>
                                        <a href="#" class="text-primary fw-semibold" onclick="openAccountDetail('ACC-1234')">Acme Corporation</a>
                                        <div class="text-muted small">ACC-1234</div>
                                    </div>
                                    <button class="btn btn-outline-secondary btn-xs" onclick="openAccountStructure('ACC-1234', 'Acme Corporation')">View Structure</button>
                                </div>
                            </td>
                            <td><span class="badge light badge-success">Live</span></td>
                            <td class="text-end">14,892,456</td>
                            <td class="text-end">1,247,832</td>
                            <td class="text-end">£5,420</td>
                            <td><span class="badge light badge-primary">Submitted</span></td>
                            <td></td>
                            <td class="text-center">
                                <div class="dropdown">
                                    <button class="btn btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" href="#" onclick="openAccountDetail('ACC-1234')">View Details</a></li>
                                        <li><a class="dropdown-item" href="#" onclick="impersonateAccount('ACC-1234')">Impersonate</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="#">Edit Pricing</a></li>
                                        <li><a class="dropdown-item" href="#">View Invoices</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item text-warning" href="#">Suspend Account</a></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div>
                                        <a href="#" class="text-primary fw-semibold" onclick="openAccountDetail('ACC-5678')">Finance Ltd</a>
                                        <div class="text-muted small">ACC-5678</div>
                                    </div>
                                    <button class="btn btn-outline-secondary btn-xs" onclick="openAccountStructure('ACC-5678', 'Finance Ltd')">View Structure</button>
                                </div>
                            </td>
                            <td><span class="badge light badge-success">Live</span></td>
                            <td class="text-end">10,456,234</td>
                            <td class="text-end">892,156</td>
                            <td class="text-end">£12,100</td>
                            <td><span class="badge light badge-info">Delivered</span></td>
                            <td></td>
                            <td class="text-center">
                                <div class="dropdown">
                                    <button class="btn btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" href="#" onclick="openAccountDetail('ACC-5678')">View Details</a></li>
                                        <li><a class="dropdown-item" href="#" onclick="impersonateAccount('ACC-5678')">Impersonate</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="#">Edit Pricing</a></li>
                                        <li><a class="dropdown-item" href="#">View Invoices</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item text-warning" href="#">Suspend Account</a></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div>
                                        <a href="#" class="text-primary fw-semibold" onclick="openAccountDetail('ACC-7890')">NewClient</a>
                                        <div class="text-muted small">ACC-7890</div>
                                    </div>
                                    <button class="btn btn-outline-secondary btn-xs" onclick="openAccountStructure('ACC-7890', 'NewClient')">View Structure</button>
                                </div>
                            </td>
                            <td><span class="badge light badge-info">Test</span></td>
                            <td class="text-end">0</td>
                            <td class="text-end">47</td>
                            <td class="text-end">£0</td>
                            <td><span class="badge light badge-primary">Submitted</span></td>
                            <td></td>
                            <td class="text-center">
                                <div class="dropdown">
                                    <button class="btn btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" href="#" onclick="openAccountDetail('ACC-7890')">View Details</a></li>
                                        <li><a class="dropdown-item" href="#" onclick="impersonateAccount('ACC-7890')">Impersonate</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item text-success" href="#">Activate Account</a></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div>
                                        <a href="#" class="text-primary fw-semibold" onclick="openAccountDetail('ACC-4567')">TestCo</a>
                                        <div class="text-muted small">ACC-4567</div>
                                    </div>
                                    <button class="btn btn-outline-secondary btn-xs" onclick="openAccountStructure('ACC-4567', 'TestCo')">View Structure</button>
                                </div>
                            </td>
                            <td><span class="badge light badge-danger">Suspended</span></td>
                            <td class="text-end">234,567</td>
                            <td class="text-end">0</td>
                            <td class="text-end text-danger">-£240</td>
                            <td><span class="badge light badge-primary">Submitted</span></td>
                            <td><span class="badge light badge-warning">Watchlist</span></td>
                            <td class="text-center">
                                <div class="dropdown">
                                    <button class="btn btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" href="#" onclick="openAccountDetail('ACC-4567')">View Details</a></li>
                                        <li><a class="dropdown-item" href="#" onclick="impersonateAccount('ACC-4567')">Impersonate</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item text-success" href="#">Reactivate Account</a></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div>
                                        <a href="#" class="text-primary fw-semibold" onclick="openAccountDetail('ACC-9012')">HighRisk Corp</a>
                                        <div class="text-muted small">ACC-9012</div>
                                    </div>
                                    <button class="btn btn-outline-secondary btn-xs" onclick="openAccountStructure('ACC-9012', 'HighRisk Corp')">View Structure</button>
                                </div>
                            </td>
                            <td><span class="badge light badge-success">Live</span></td>
                            <td class="text-end">5,678,901</td>
                            <td class="text-end">456,789</td>
                            <td class="text-end">£3,250</td>
                            <td><span class="badge light badge-info">Delivered</span></td>
                            <td>
                                <span class="badge light badge-danger">Fraud</span>
                                <span class="badge light badge-secondary">Restricted</span>
                            </td>
                            <td class="text-center">
                                <div class="dropdown">
                                    <button class="btn btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" href="#" onclick="openAccountDetail('ACC-9012')">View Details</a></li>
                                        <li><a class="dropdown-item" href="#" onclick="impersonateAccount('ACC-9012')">Impersonate</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="#">Review Risk</a></li>
                                        <li><a class="dropdown-item text-warning" href="#">Suspend Account</a></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div>
                                        <a href="#" class="text-primary fw-semibold" onclick="openAccountDetail('ACC-3456')">MedTech Solutions</a>
                                        <div class="text-muted small">ACC-3456</div>
                                    </div>
                                    <button class="btn btn-outline-secondary btn-xs" onclick="openAccountStructure('ACC-3456', 'MedTech Solutions')">View Structure</button>
                                </div>
                            </td>
                            <td><span class="badge light badge-success">Live</span></td>
                            <td class="text-end">8,901,234</td>
                            <td class="text-end">743,102</td>
                            <td class="text-end">£8,900</td>
                            <td><span class="badge light badge-info">Delivered</span></td>
                            <td></td>
                            <td class="text-center">
                                <div class="dropdown">
                                    <button class="btn btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" href="#" onclick="openAccountDetail('ACC-3456')">View Details</a></li>
                                        <li><a class="dropdown-item" href="#" onclick="impersonateAccount('ACC-3456')">Impersonate</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="#">Edit Pricing</a></li>
                                        <li><a class="dropdown-item" href="#">View Invoices</a></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-between align-items-center">
            <span class="text-muted small">Showing 1-6 of 847 accounts</span>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                    <li class="page-item"><a class="page-link" href="#">...</a></li>
                    <li class="page-item"><a class="page-link" href="#">142</a></li>
                    <li class="page-item"><a class="page-link" href="#">Next</a></li>
                </ul>
            </nav>
        </div>
    </div>
</div>

<!-- Account Structure Modal -->
<div class="modal fade" id="accountStructureModal" tabindex="-1" aria-labelledby="accountStructureModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="accountStructureModalLabel">Account Hierarchy</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="accountStructureContent">
                <!-- Content populated by JavaScript -->
            </div>
            <div class="modal-footer">
                <span class="text-muted small me-auto">Read-only view</span>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
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

window.openAccountDetail = function(accountId) {
    if (typeof AdminControlPlane !== 'undefined') {
        AdminControlPlane.logAdminAction('ACCOUNT_VIEWED', 'ACCOUNTS', { accountId: accountId });
    }
    // Navigate to account details page
    window.location.href = '/admin/accounts/details?id=' + accountId;
};

window.impersonateAccount = function(accountId) {
    if (confirm('You are about to impersonate account ' + accountId + '. This action will be logged. Continue?')) {
        if (typeof AdminControlPlane !== 'undefined') {
            AdminControlPlane.logAdminAction('ACCOUNT_IMPERSONATION_STARTED', 'ACCOUNTS', { 
                accountId: accountId,
                reason: 'Admin initiated impersonation'
            });
        }
        alert('Impersonation session started for ' + accountId + ' (5-minute limit)');
    }
};

var accountStructureModal = null;

window.openAccountStructure = function(accountId, accountName) {
    if (!accountStructureModal) {
        accountStructureModal = new bootstrap.Modal(document.getElementById('accountStructureModal'));
    }

    // Update modal title
    document.getElementById('accountStructureModalLabel').textContent = 'Account Hierarchy: ' + accountName;

    // Mock hierarchy data - in production would come from API
    var hierarchyData = {
        'ACC-1234': {
            main: { name: 'Acme Corporation', id: 'ACC-1234', status: 'Live', type: 'Enterprise' },
            subAccounts: [
                { name: 'Acme Marketing', id: 'SUB-001', status: 'Active', users: 5 },
                { name: 'Acme Sales', id: 'SUB-002', status: 'Active', users: 12 },
                { name: 'Acme Support', id: 'SUB-003', status: 'Active', users: 8 }
            ],
            users: [
                { name: 'John Smith', email: 'j.smith@acme.com', role: 'Account Owner', status: 'Active' },
                { name: 'Jane Doe', email: 'j.doe@acme.com', role: 'Admin', status: 'Active' }
            ]
        },
        'ACC-5678': {
            main: { name: 'Finance Ltd', id: 'ACC-5678', status: 'Live', type: 'Enterprise' },
            subAccounts: [
                { name: 'Finance Retail', id: 'SUB-101', status: 'Active', users: 3 }
            ],
            users: [
                { name: 'Mike Johnson', email: 'm.johnson@finance.com', role: 'Account Owner', status: 'Active' }
            ]
        }
    };

    var data = hierarchyData[accountId] || {
        main: { name: accountName, id: accountId, status: 'Live', type: 'SMB' },
        subAccounts: [],
        users: [{ name: 'Primary User', email: 'user@example.com', role: 'Account Owner', status: 'Active' }]
    };

    var html = '<div class="account-hierarchy">';
    
    // Main Account
    html += '<div class="hierarchy-section mb-4">';
    html += '<h6 class="text-muted mb-3">Main Account</h6>';
    html += '<div class="card bg-light">';
    html += '<div class="card-body py-3">';
    html += '<div class="d-flex justify-content-between align-items-center">';
    html += '<div>';
    html += '<strong>' + data.main.name + '</strong>';
    html += '<div class="text-muted small">' + data.main.id + '</div>';
    html += '</div>';
    html += '<div>';
    html += '<span class="badge light badge-' + (data.main.status === 'Live' ? 'success' : 'info') + '">' + data.main.status + '</span> ';
    html += '<span class="badge light badge-secondary">' + data.main.type + '</span>';
    html += '</div>';
    html += '</div>';
    html += '</div>';
    html += '</div>';
    html += '</div>';

    // Sub-Accounts
    html += '<div class="hierarchy-section mb-4">';
    html += '<h6 class="text-muted mb-3">Sub-Accounts (' + data.subAccounts.length + ')</h6>';
    if (data.subAccounts.length > 0) {
        html += '<div class="table-responsive">';
        html += '<table class="table table-sm table-bordered mb-0">';
        html += '<thead><tr><th>Name</th><th>ID</th><th>Status</th><th>Users</th></tr></thead>';
        html += '<tbody>';
        data.subAccounts.forEach(function(sub) {
            html += '<tr>';
            html += '<td>' + sub.name + '</td>';
            html += '<td class="text-muted">' + sub.id + '</td>';
            html += '<td><span class="badge light badge-success">' + sub.status + '</span></td>';
            html += '<td>' + sub.users + '</td>';
            html += '</tr>';
        });
        html += '</tbody></table></div>';
    } else {
        html += '<p class="text-muted mb-0">No sub-accounts</p>';
    }
    html += '</div>';

    // Users
    html += '<div class="hierarchy-section">';
    html += '<h6 class="text-muted mb-3">Users (' + data.users.length + ')</h6>';
    html += '<div class="table-responsive">';
    html += '<table class="table table-sm table-bordered mb-0">';
    html += '<thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th></tr></thead>';
    html += '<tbody>';
    data.users.forEach(function(user) {
        html += '<tr>';
        html += '<td>' + user.name + '</td>';
        html += '<td class="text-muted">' + user.email + '</td>';
        html += '<td><span class="badge light badge-primary">' + user.role + '</span></td>';
        html += '<td><span class="badge light badge-success">' + user.status + '</span></td>';
        html += '</tr>';
    });
    html += '</tbody></table></div>';
    html += '</div>';

    html += '</div>';

    document.getElementById('accountStructureContent').innerHTML = html;
    accountStructureModal.show();

    // Audit log
    if (typeof AdminControlPlane !== 'undefined') {
        AdminControlPlane.logAdminAction('ACCOUNT_STRUCTURE_VIEWED', 'ACCOUNTS', { accountId: accountId });
    }
};

function filterTable(filter) {
    var rows = document.querySelectorAll('#accountsTable tbody tr');
    
    if (!filter) {
        // Show all rows
        rows.forEach(function(row) {
            row.style.display = '';
        });
        return;
    }

    rows.forEach(function(row) {
        var statusBadge = row.querySelector('.badge');
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
                // Show accounts with risk flags (Fraud, Restricted, Watchlist)
                var hasRiskFlag = row.textContent.includes('Fraud') || 
                                  row.textContent.includes('Restricted') || 
                                  row.textContent.includes('Watchlist');
                show = hasRiskFlag || row.textContent.includes('-£');
                break;
            default:
                show = true;
        }

        row.style.display = show ? '' : 'none';
    });
}
</script>
@endpush
