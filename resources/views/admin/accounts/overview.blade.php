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

/* Hierarchy Tree Styles */
.hierarchy-tree { font-size: 0.9rem; }
.tree-node { position: relative; padding-left: 1.5rem; }
.tree-node::before { content: ''; position: absolute; left: 0.5rem; top: 0; bottom: 0; width: 1px; background: #dee2e6; }
.tree-node:last-child::before { height: 1.25rem; }
.tree-item { position: relative; padding: 0.5rem 0.75rem; margin-bottom: 0.25rem; border-radius: 4px; cursor: pointer; transition: background-color 0.15s; }
.tree-item::before { content: ''; position: absolute; left: -1rem; top: 1.25rem; width: 1rem; height: 1px; background: #dee2e6; }
.tree-item:hover { background-color: #f8f9fa; }
.tree-item.selected { background-color: #e3f2fd; border: 1px solid #1e3a5f; }
.tree-item.main-account { font-weight: 600; background-color: #f8f9fa; border: 1px solid #dee2e6; }
.tree-item.main-account::before { display: none; }
.tree-toggle { cursor: pointer; user-select: none; margin-right: 0.25rem; font-size: 0.75rem; color: #6c757d; }
.tree-toggle:hover { color: #1e3a5f; }
.tree-children { display: block; }
.tree-children.collapsed { display: none; }
.tree-node-name { display: inline-block; }
.tree-node-badges { display: inline-block; margin-left: 0.5rem; }
.tree-node-badges .badge { font-size: 0.7rem; padding: 0.2rem 0.4rem; }
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
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="accountStructureModalLabel">Account Hierarchy</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="row g-0">
                    <!-- Left Panel - Hierarchy Tree -->
                    <div class="col-md-5 border-end" style="max-height: 500px; overflow-y: auto;">
                        <div class="p-3">
                            <h6 class="text-muted mb-3">Hierarchy Tree</h6>
                            <div id="hierarchyTree" class="hierarchy-tree">
                                <!-- Tree populated by JavaScript -->
                            </div>
                        </div>
                    </div>
                    <!-- Right Panel - Selected Node Details -->
                    <div class="col-md-7" style="max-height: 500px; overflow-y: auto;">
                        <div class="p-3" id="nodeDetailsPanel">
                            <div class="text-center text-muted py-5">
                                <p>Select a node to view details</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <span class="text-muted small me-auto">Read-only view</span>
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="addSubAccount()">
                    <i class="fa fa-plus me-1"></i>Add Sub-account
                </button>
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="inviteUser()">
                    <i class="fa fa-user-plus me-1"></i>Invite User
                </button>
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
var currentHierarchyData = null;

window.openAccountStructure = function(accountId, accountName) {
    if (!accountStructureModal) {
        accountStructureModal = new bootstrap.Modal(document.getElementById('accountStructureModal'));
    }

    document.getElementById('accountStructureModalLabel').textContent = 'Account Structure — ' + accountName;

    // Mock hierarchy data
    var hierarchyData = {
        'ACC-1234': {
            main: { name: 'Acme Corporation', id: 'ACC-1234', status: 'Active', type: 'Enterprise', created: 'Jan 15, 2024', balance: '£5,420' },
            subAccounts: [
                { 
                    name: 'Acme Marketing', id: 'SUB-001', status: 'Active', 
                    users: [
                        { name: 'Sarah Wilson', email: 's.wilson@acme.com', role: 'Admin', status: 'Active' },
                        { name: 'Tom Brown', email: 't.brown@acme.com', role: 'Messaging Manager', status: 'Active' },
                        { name: 'Lisa Green', email: 'l.green@acme.com', role: 'Read-Only', status: 'Active' }
                    ]
                },
                { 
                    name: 'Acme Sales', id: 'SUB-002', status: 'Active',
                    users: [
                        { name: 'James Miller', email: 'j.miller@acme.com', role: 'Admin', status: 'Active' },
                        { name: 'Emma Davis', email: 'e.davis@acme.com', role: 'Messaging Manager', status: 'Active' }
                    ]
                },
                { 
                    name: 'Acme Support', id: 'SUB-003', status: 'Suspended',
                    users: [
                        { name: 'Chris Taylor', email: 'c.taylor@acme.com', role: 'Finance', status: 'Suspended' }
                    ]
                }
            ],
            mainUsers: [
                { name: 'John Smith', email: 'j.smith@acme.com', role: 'Account Owner', status: 'Active' },
                { name: 'Jane Doe', email: 'j.doe@acme.com', role: 'Admin', status: 'Active' }
            ]
        },
        'ACC-5678': {
            main: { name: 'Finance Ltd', id: 'ACC-5678', status: 'Active', type: 'Enterprise', created: 'Mar 02, 2024', balance: '£12,100' },
            subAccounts: [
                { 
                    name: 'Finance Retail', id: 'SUB-101', status: 'Active',
                    users: [
                        { name: 'Alex Johnson', email: 'a.johnson@finance.com', role: 'Messaging Manager', status: 'Active' }
                    ]
                }
            ],
            mainUsers: [
                { name: 'Mike Johnson', email: 'm.johnson@finance.com', role: 'Account Owner', status: 'Active' }
            ]
        }
    };

    currentHierarchyData = hierarchyData[accountId] || {
        main: { name: accountName, id: accountId, status: 'Active', type: 'SMB', created: 'Jan 2026', balance: '£0' },
        subAccounts: [],
        mainUsers: [{ name: 'Primary User', email: 'user@example.com', role: 'Account Owner', status: 'Active' }]
    };

    renderHierarchyTree();
    document.getElementById('nodeDetailsPanel').innerHTML = '<div class="text-center text-muted py-5"><p>Select a node to view details</p></div>';
    accountStructureModal.show();

    if (typeof AdminControlPlane !== 'undefined') {
        AdminControlPlane.logAdminAction('ACCOUNT_STRUCTURE_VIEWED', 'ACCOUNTS', { accountId: accountId });
    }
};

function renderHierarchyTree() {
    var data = currentHierarchyData;
    var html = '';

    // Main Account Node
    html += '<div class="tree-item main-account" onclick="selectNode(\'main\', null)">';
    html += '<span class="tree-node-name">' + data.main.name + '</span>';
    html += '<span class="tree-node-badges">';
    html += '<span class="badge light badge-' + (data.main.status === 'Active' ? 'success' : 'danger') + '">' + data.main.status + '</span>';
    html += '</span>';
    html += '</div>';

    // Main Account Users
    if (data.mainUsers && data.mainUsers.length > 0) {
        html += '<div class="tree-node">';
        data.mainUsers.forEach(function(user, idx) {
            html += '<div class="tree-item" onclick="selectNode(\'main-user\', ' + idx + ')">';
            html += '<span class="tree-node-name">' + user.name + '</span>';
            html += '<span class="tree-node-badges">';
            html += '<span class="badge light badge-' + (user.status === 'Active' ? 'success' : 'warning') + '">' + user.status + '</span>';
            html += '<span class="badge light badge-primary">' + user.role + '</span>';
            html += '</span>';
            html += '</div>';
        });
        html += '</div>';
    }

    // Sub-Accounts
    if (data.subAccounts && data.subAccounts.length > 0) {
        data.subAccounts.forEach(function(sub, subIdx) {
            html += '<div class="tree-node">';
            html += '<div class="tree-item" onclick="selectNode(\'sub\', ' + subIdx + ')">';
            html += '<span class="tree-toggle" onclick="event.stopPropagation(); toggleTreeNode(this);">▼</span>';
            html += '<span class="tree-node-name">' + sub.name + '</span>';
            html += '<span class="tree-node-badges">';
            html += '<span class="badge light badge-' + (sub.status === 'Active' ? 'success' : 'danger') + '">' + sub.status + '</span>';
            html += '</span>';
            html += '</div>';

            // Sub-Account Users
            if (sub.users && sub.users.length > 0) {
                html += '<div class="tree-children">';
                sub.users.forEach(function(user, userIdx) {
                    html += '<div class="tree-node">';
                    html += '<div class="tree-item" onclick="selectNode(\'sub-user\', {sub: ' + subIdx + ', user: ' + userIdx + '})">';
                    html += '<span class="tree-node-name">' + user.name + '</span>';
                    html += '<span class="tree-node-badges">';
                    html += '<span class="badge light badge-' + (user.status === 'Active' ? 'success' : 'warning') + '">' + user.status + '</span>';
                    html += '<span class="badge light badge-primary">' + user.role + '</span>';
                    html += '</span>';
                    html += '</div>';
                    html += '</div>';
                });
                html += '</div>';
            }
            html += '</div>';
        });
    }

    document.getElementById('hierarchyTree').innerHTML = html;
}

window.toggleTreeNode = function(toggle) {
    var parent = toggle.closest('.tree-item').nextElementSibling;
    if (parent && parent.classList.contains('tree-children')) {
        parent.classList.toggle('collapsed');
        toggle.textContent = parent.classList.contains('collapsed') ? '▶' : '▼';
    }
};

window.selectNode = function(type, index) {
    // Clear previous selection
    document.querySelectorAll('.tree-item.selected').forEach(function(el) {
        el.classList.remove('selected');
    });
    event.currentTarget.classList.add('selected');

    var data = currentHierarchyData;
    var html = '';

    if (type === 'main') {
        html = renderMainAccountDetails(data.main);
    } else if (type === 'main-user') {
        html = renderUserDetails(data.mainUsers[index], data.main.name);
    } else if (type === 'sub') {
        html = renderSubAccountDetails(data.subAccounts[index]);
    } else if (type === 'sub-user') {
        var sub = data.subAccounts[index.sub];
        html = renderUserDetails(sub.users[index.user], sub.name);
    }

    document.getElementById('nodeDetailsPanel').innerHTML = html;
};

function renderMainAccountDetails(account) {
    var data = currentHierarchyData;
    var totalSubAccounts = data.subAccounts ? data.subAccounts.length : 0;
    var totalUsers = (data.mainUsers ? data.mainUsers.length : 0);
    data.subAccounts.forEach(function(sub) { totalUsers += sub.users ? sub.users.length : 0; });

    return '<h6 class="mb-3">Main Account</h6>' +
        '<table class="table table-sm mb-4">' +
        '<tr><th class="text-muted" style="width:45%">Account Owner</th><td>' + (data.mainUsers && data.mainUsers.length > 0 ? data.mainUsers[0].name : 'N/A') + '</td></tr>' +
        '<tr><th class="text-muted">Account Status</th><td><span class="badge light badge-' + (account.status === 'Active' ? 'success' : 'danger') + '">' + account.status + '</span></td></tr>' +
        '<tr><th class="text-muted">Pricing Model</th><td><span class="badge light badge-info">' + (account.pricingModel || 'Delivered') + '</span></td></tr>' +
        '<tr><th class="text-muted">Credit Model</th><td><span class="badge light badge-secondary">' + (account.creditModel || 'Prepaid') + '</span></td></tr>' +
        '<tr><th class="text-muted">Total Sub-accounts</th><td>' + totalSubAccounts + '</td></tr>' +
        '<tr><th class="text-muted">Total Users</th><td>' + totalUsers + '</td></tr>' +
        '</table>';
}

function renderSubAccountDetails(sub) {
    var spendUsed = sub.spendUsed || 2450;
    var spendCap = sub.spendCap || 5000;
    var msgUsed = sub.msgUsed || 45000;
    var msgCap = sub.msgCap || 100000;
    var spendPct = Math.round((spendUsed / spendCap) * 100);
    var msgPct = Math.round((msgUsed / msgCap) * 100);

    return '<h6 class="mb-3">Sub-Account</h6>' +
        '<table class="table table-sm mb-3">' +
        '<tr><th class="text-muted" style="width:45%">Status</th><td><span class="badge light badge-' + (sub.status === 'Active' ? 'success' : 'danger') + '">' + sub.status + '</span></td></tr>' +
        '</table>' +
        '<h6 class="text-muted mb-2" style="font-size:0.85rem;">Enforcement Summary</h6>' +
        '<table class="table table-sm mb-3">' +
        '<tr><th class="text-muted" style="width:45%">Monthly Spend Cap</th><td>£' + spendCap.toLocaleString() + '</td></tr>' +
        '<tr><th class="text-muted">Monthly Message Cap</th><td>' + msgCap.toLocaleString() + '</td></tr>' +
        '<tr><th class="text-muted">Enforcement Type</th><td><span class="badge light badge-warning">' + (sub.enforcementType || 'Warn Only') + '</span></td></tr>' +
        '</table>' +
        '<h6 class="text-muted mb-2" style="font-size:0.85rem;">Usage vs Limits</h6>' +
        '<div class="mb-2"><small class="text-muted">Spend: £' + spendUsed.toLocaleString() + ' / £' + spendCap.toLocaleString() + '</small>' +
        '<div class="progress" style="height:8px;"><div class="progress-bar bg-' + (spendPct > 80 ? 'danger' : spendPct > 60 ? 'warning' : 'success') + '" style="width:' + spendPct + '%"></div></div></div>' +
        '<div class="mb-3"><small class="text-muted">Messages: ' + msgUsed.toLocaleString() + ' / ' + msgCap.toLocaleString() + '</small>' +
        '<div class="progress" style="height:8px;"><div class="progress-bar bg-' + (msgPct > 80 ? 'danger' : msgPct > 60 ? 'warning' : 'success') + '" style="width:' + msgPct + '%"></div></div></div>' +
        '<h6 class="text-muted mb-2" style="font-size:0.85rem;">Assigned Assets</h6>' +
        '<table class="table table-sm mb-3">' +
        '<tr><th class="text-muted" style="width:45%">Sender IDs</th><td>' + (sub.senderIds || 3) + '</td></tr>' +
        '<tr><th class="text-muted">Numbers</th><td>' + (sub.numbers || 2) + '</td></tr>' +
        '<tr><th class="text-muted">Templates</th><td>' + (sub.templates || 8) + '</td></tr>' +
        '<tr><th class="text-muted">RCS Agents</th><td>' + (sub.rcsAgents || 1) + '</td></tr>' +
        '<tr><th class="text-muted">API Connections</th><td>' + (sub.apiConnections || 2) + '</td></tr>' +
        '<tr><th class="text-muted">Email-to-SMS Configs</th><td>' + (sub.emailConfigs || 1) + '</td></tr>' +
        '</table>' +
        '<button class="btn btn-primary btn-sm" onclick="manageSubAccount(\'' + sub.id + '\')">Manage Sub-account</button>';
}

function renderUserDetails(user, parentName) {
    return '<h6 class="mb-3">User</h6>' +
        '<table class="table table-sm mb-3">' +
        '<tr><th class="text-muted" style="width:45%">Role</th><td><span class="badge light badge-primary">' + user.role + '</span></td></tr>' +
        '<tr><th class="text-muted">Sender Capability</th><td><span class="badge light badge-' + ((user.senderCapability || 'Advanced') === 'Advanced' ? 'success' : 'secondary') + '">' + (user.senderCapability || 'Advanced') + '</span></td></tr>' +
        '<tr><th class="text-muted">MFA Status</th><td><span class="badge light badge-' + ((user.mfaEnabled !== false) ? 'success' : 'warning') + '">' + ((user.mfaEnabled !== false) ? 'Enabled' : 'Disabled') + '</span></td></tr>' +
        '<tr><th class="text-muted">Last Login</th><td>' + (user.lastLogin || '2 hours ago') + '</td></tr>' +
        '</table>' +
        '<button class="btn btn-primary btn-sm" onclick="viewUserDetails(\'' + user.email + '\')">View User Details</button>';
}

window.manageSubAccount = function(subId) {
    if (typeof AdminControlPlane !== 'undefined') {
        AdminControlPlane.logAdminAction('SUB_ACCOUNT_MANAGE_CLICKED', 'ACCOUNTS', { subAccountId: subId });
    }
    alert('Navigate to Sub-account management: ' + subId);
};

window.viewUserDetails = function(email) {
    if (typeof AdminControlPlane !== 'undefined') {
        AdminControlPlane.logAdminAction('USER_DETAILS_CLICKED', 'ACCOUNTS', { userEmail: email });
    }
    alert('Navigate to User details: ' + email);
};

window.addSubAccount = function() {
    var accountId = currentHierarchyData ? currentHierarchyData.main.id : null;
    if (typeof AdminControlPlane !== 'undefined') {
        AdminControlPlane.logAdminAction('ADD_SUB_ACCOUNT_INITIATED', 'ACCOUNTS', { parentAccountId: accountId });
    }
    alert('Open Add Sub-account wizard for: ' + (accountId || 'Unknown'));
};

window.inviteUser = function() {
    var accountId = currentHierarchyData ? currentHierarchyData.main.id : null;
    if (typeof AdminControlPlane !== 'undefined') {
        AdminControlPlane.logAdminAction('INVITE_USER_INITIATED', 'ACCOUNTS', { accountId: accountId });
    }
    alert('Open Invite User modal for: ' + (accountId || 'Unknown'));
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
