@extends('layouts.admin')

@section('title', 'Account Overview')

@push('styles')
<style>
.admin-page { padding: 1.5rem; }
.account-filters { margin-bottom: 1.5rem; }
.kpi-tile-row { margin-bottom: 1.5rem; transition: all 0.3s ease; }
.kpi-tile-row .widget-stat { cursor: pointer; transition: all 0.2s ease; border: 2px solid transparent; }
.kpi-tile-row .widget-stat:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
.kpi-tile-row .widget-stat.active { border-color: #1e3a5f; }
.kpi-tile-row .widget-stat .card-body { padding: 1rem; }
.kpi-tile-row .widget-stat h4 { font-size: 1.5rem; margin-bottom: 0; }
.kpi-tile-row .widget-stat p { font-size: 0.8rem; margin-bottom: 0.25rem; }

/* Collapsed KPI Strip */
.kpi-collapsed-strip { 
    display: none; 
    background: #fff; 
    border: 1px solid #e6e6e6; 
    border-radius: 0.5rem; 
    padding: 0.5rem 1rem; 
    margin-bottom: 1rem; 
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
}
.kpi-collapsed-strip.visible { display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem 1.5rem; }
.kpi-collapsed-strip .kpi-item { 
    display: flex; 
    align-items: center; 
    gap: 0.5rem; 
    cursor: pointer; 
    padding: 0.25rem 0.5rem; 
    border-radius: 0.25rem;
    transition: background-color 0.15s;
}
.kpi-collapsed-strip .kpi-item:hover { background-color: #f8f9fa; }
.kpi-collapsed-strip .kpi-item.active { background-color: #e3f2fd; }
.kpi-collapsed-strip .kpi-item .kpi-icon { font-size: 0.875rem; }
.kpi-collapsed-strip .kpi-item .kpi-label { font-size: 0.75rem; color: #6c757d; }
.kpi-collapsed-strip .kpi-item .kpi-count { font-weight: 600; font-size: 0.875rem; color: #1e3a5f; }
.kpi-tile-row.collapsed { display: none; }

/* Compact Table Rows */
#accountsTable tbody tr td { padding: 0.5rem 0.75rem; vertical-align: middle; }
#accountsTable thead th { padding: 0.5rem 0.75rem; }
.client-name-cell { max-width: 200px; }
.client-name-cell .client-name { 
    font-weight: 600; 
    color: #1e3a5f; 
    white-space: nowrap; 
    overflow: hidden; 
    text-overflow: ellipsis; 
    display: block;
    max-width: 180px;
}
.client-name-cell .client-name:hover { text-decoration: underline; cursor: pointer; }
.client-name-cell .account-number { font-size: 0.75rem; color: #6c757d; margin-top: 0.125rem; }

/* Expand/Collapse Row Controls */
.expand-toggle {
    width: 20px;
    height: 20px;
    border: 1px solid #dee2e6;
    border-radius: 3px;
    background: #fff;
    color: #6c757d;
    font-size: 0.7rem;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-right: 0.5rem;
    transition: all 0.15s ease;
    flex-shrink: 0;
}
.expand-toggle:hover { border-color: #1e3a5f; color: #1e3a5f; background: #f8f9fa; }
.expand-toggle.expanded { background: #1e3a5f; color: #fff; border-color: #1e3a5f; }
.client-name-wrapper { display: flex; align-items: flex-start; }
.client-name-content { flex: 1; min-width: 0; }

/* Sub-Account Expanded Row */
.sub-account-row { display: none; }
.sub-account-row.visible { display: table-row; }
.sub-account-row td { 
    background: #f8f9fa !important; 
    padding: 0 !important; 
    border-top: none !important;
}
.sub-account-table-wrapper { padding: 0.75rem 1rem 0.75rem 2.5rem; }
.sub-account-table { 
    width: 100%; 
    font-size: 0.8rem; 
    margin: 0;
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 0.375rem;
}
.sub-account-table thead th { 
    background: #f1f3f5; 
    padding: 0.5rem 0.75rem; 
    font-weight: 600; 
    font-size: 0.75rem;
    color: #495057;
    border-bottom: 1px solid #dee2e6;
}
.sub-account-table tbody td { 
    padding: 0.5rem 0.75rem; 
    vertical-align: middle;
    border-bottom: 1px solid #f0f0f0;
}
.sub-account-table tbody tr:last-child td { border-bottom: none; }
.sub-account-table .sub-name { font-weight: 500; color: #333; }
.no-sub-accounts { color: #6c757d; font-style: italic; padding: 0.5rem 0; }

/* Sortable Headers */
.sortable { cursor: pointer; user-select: none; position: relative; white-space: nowrap; }
.sortable:hover { background: #f8f9fa; }
.sortable::after {
    content: '\f0dc';
    font-family: 'Font Awesome 6 Free', 'Font Awesome 5 Free';
    font-weight: 900;
    margin-left: 0.5rem;
    color: #adb5bd;
    font-size: 0.7rem;
}
.sortable.sort-asc::after { content: '\f0de'; color: #1e3a5f; }
.sortable.sort-desc::after { content: '\f0dd'; color: #1e3a5f; }

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

    <!-- Collapsed KPI Strip (shown when scrolled) -->
    <div class="kpi-collapsed-strip" id="kpiCollapsedStrip">
        <div class="kpi-item" onclick="filterAccounts('live')" data-filter="live">
            <i class="fas fa-check-circle text-success kpi-icon"></i>
            <span class="kpi-label">Active</span>
            <span class="kpi-count">847</span>
        </div>
        <div class="kpi-item" onclick="filterAccounts('test')" data-filter="test">
            <i class="fas fa-flask text-info kpi-icon"></i>
            <span class="kpi-label">Test</span>
            <span class="kpi-count">156</span>
        </div>
        <div class="kpi-item" onclick="filterAccounts('suspended')" data-filter="suspended">
            <i class="fas fa-ban text-danger kpi-icon"></i>
            <span class="kpi-label">Suspended</span>
            <span class="kpi-count">23</span>
        </div>
        <div class="kpi-item" onclick="filterAccounts('pending')" data-filter="pending">
            <i class="fas fa-clock text-warning kpi-icon"></i>
            <span class="kpi-label">Pending</span>
            <span class="kpi-count">12</span>
        </div>
        <div class="kpi-item" onclick="filterAccounts('senderid')" data-filter="senderid">
            <i class="fas fa-id-badge text-primary kpi-icon"></i>
            <span class="kpi-label">Sender ID</span>
            <span class="kpi-count">8</span>
        </div>
        <div class="kpi-item" onclick="filterAccounts('rcs')" data-filter="rcs">
            <i class="fas fa-comments text-secondary kpi-icon"></i>
            <span class="kpi-label">RCS</span>
            <span class="kpi-count">5</span>
        </div>
        <div class="kpi-item" onclick="filterAccounts('testnumber')" data-filter="testnumber">
            <i class="fas fa-phone text-info kpi-icon"></i>
            <span class="kpi-label">Test #</span>
            <span class="kpi-count">3</span>
        </div>
        <div class="kpi-item" onclick="filterAccounts('flagged')" data-filter="flagged">
            <i class="fas fa-exclamation-triangle text-danger kpi-icon"></i>
            <span class="kpi-label">Fraud/Risk</span>
            <span class="kpi-count">4</span>
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
                            <th class="text-center">Actions</th>
                            <th class="text-center" style="width: 50px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="account-row" data-account="ACC-1234" data-name="Acme Corporation" data-status="live" data-volume-year="14892456" data-volume-month="1247832" data-balance="5420">
                            <td class="client-name-cell">
                                <div class="client-name-wrapper">
                                    <button class="expand-toggle" onclick="toggleSubAccounts('ACC-1234', this)" title="Show sub-accounts"><i class="fas fa-plus"></i></button>
                                    <div class="client-name-content">
                                        <a href="{{ route('admin.accounts.details', 'ACC-1234') }}" class="client-name">Acme Corporation</a>
                                        <div class="account-number">ACC-1234</div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge light badge-success">Live</span></td>
                            <td class="text-end">14,892,456</td>
                            <td class="text-end">1,247,832</td>
                            <td class="text-end">£5,420</td>
                            <td class="text-center">
                                <div class="action-icons">
                                    <button class="action-icon-btn" onclick="openBillingModal('ACC-1234', 'Acme Corporation')" title="Billing"><i class="fas fa-pound-sign"></i></button>
                                    <button class="action-icon-btn" onclick="rowAction('ACC-1234', 'Acme Corporation', 'pricing')" title="Pricing"><i class="fas fa-tags"></i></button>
                                    <button class="action-icon-btn" onclick="rowAction('ACC-1234', 'Acme Corporation', 'routing')" title="Routing"><i class="fas fa-route"></i></button>
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="dropdown">
                                    <button class="btn btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" href="#" onclick="rowAction('ACC-1234', 'Acme Corporation', 'add_credit')"><i class="fas fa-plus-circle me-2 text-success"></i>Add Credit</a></li>
                                        <li><a class="dropdown-item" href="#" onclick="rowAction('ACC-1234', 'Acme Corporation', 'change_name')"><i class="fas fa-edit me-2"></i>Change Account Name</a></li>
                                        <li><a class="dropdown-item" href="#" onclick="openAccountDetail('ACC-1234')"><i class="fas fa-cog me-2"></i>Edit Account Details</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="#" onclick="openFraudRisk('ACC-1234', 'Acme Corporation')"><i class="fas fa-shield-alt me-2 text-danger"></i>Fraud & Risk</a></li>
                                        <li><a class="dropdown-item text-warning" href="#" onclick="rowAction('ACC-1234', 'Acme Corporation', 'suspend')"><i class="fas fa-pause-circle me-2"></i>Suspend Account</a></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        <tr class="sub-account-row" data-parent="ACC-1234">
                            <td colspan="7">
                                <div class="sub-account-table-wrapper">
                                    <table class="sub-account-table">
                                        <thead><tr><th>Sub-Account</th><th class="text-end">Volume (Month)</th><th class="text-end">Volume (Year)</th><th>Status</th></tr></thead>
                                        <tbody>
                                            <tr><td class="sub-name">Acme Marketing</td><td class="text-end">456,234</td><td class="text-end">5,234,567</td><td><span class="badge light badge-success">Active</span></td></tr>
                                            <tr><td class="sub-name">Acme Sales</td><td class="text-end">521,598</td><td class="text-end">6,123,456</td><td><span class="badge light badge-success">Active</span></td></tr>
                                            <tr><td class="sub-name">Acme Support</td><td class="text-end">270,000</td><td class="text-end">3,534,433</td><td><span class="badge light badge-warning">Limited</span></td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </td>
                        </tr>
                        <tr class="account-row" data-account="ACC-5678" data-name="Finance Ltd" data-status="live" data-volume-year="10456234" data-volume-month="892156" data-balance="12100">
                            <td class="client-name-cell">
                                <div class="client-name-wrapper">
                                    <button class="expand-toggle" onclick="toggleSubAccounts('ACC-5678', this)" title="Show sub-accounts"><i class="fas fa-plus"></i></button>
                                    <div class="client-name-content">
                                        <a href="{{ route('admin.accounts.details', 'ACC-5678') }}" class="client-name">Finance Ltd</a>
                                        <div class="account-number">ACC-5678</div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge light badge-success">Live</span></td>
                            <td class="text-end">10,456,234</td>
                            <td class="text-end">892,156</td>
                            <td class="text-end">£12,100</td>
                            <td class="text-center">
                                <div class="action-icons">
                                    <button class="action-icon-btn" onclick="openBillingModal('ACC-5678', 'Finance Ltd')" title="Billing"><i class="fas fa-pound-sign"></i></button>
                                    <button class="action-icon-btn" onclick="rowAction('ACC-5678', 'Finance Ltd', 'pricing')" title="Pricing"><i class="fas fa-tags"></i></button>
                                    <button class="action-icon-btn" onclick="rowAction('ACC-5678', 'Finance Ltd', 'routing')" title="Routing"><i class="fas fa-route"></i></button>
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="dropdown">
                                    <button class="btn btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" href="#" onclick="rowAction('ACC-5678', 'Finance Ltd', 'add_credit')"><i class="fas fa-plus-circle me-2 text-success"></i>Add Credit</a></li>
                                        <li><a class="dropdown-item" href="#" onclick="rowAction('ACC-5678', 'Finance Ltd', 'change_name')"><i class="fas fa-edit me-2"></i>Change Account Name</a></li>
                                        <li><a class="dropdown-item" href="#" onclick="openAccountDetail('ACC-5678')"><i class="fas fa-cog me-2"></i>Edit Account Details</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="#" onclick="openFraudRisk('ACC-5678', 'Finance Ltd')"><i class="fas fa-shield-alt me-2 text-danger"></i>Fraud & Risk</a></li>
                                        <li><a class="dropdown-item text-warning" href="#" onclick="rowAction('ACC-5678', 'Finance Ltd', 'suspend')"><i class="fas fa-pause-circle me-2"></i>Suspend Account</a></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        <tr class="sub-account-row" data-parent="ACC-5678">
                            <td colspan="7">
                                <div class="sub-account-table-wrapper">
                                    <table class="sub-account-table">
                                        <thead><tr><th>Sub-Account</th><th class="text-end">Volume (Month)</th><th class="text-end">Volume (Year)</th><th>Status</th></tr></thead>
                                        <tbody>
                                            <tr><td class="sub-name">Finance Retail</td><td class="text-end">892,156</td><td class="text-end">10,456,234</td><td><span class="badge light badge-success">Active</span></td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </td>
                        </tr>
                        <tr class="account-row" data-account="ACC-7890" data-name="NewClient" data-status="test" data-volume-year="0" data-volume-month="47" data-balance="0">
                            <td class="client-name-cell">
                                <div class="client-name-wrapper">
                                    <button class="expand-toggle" onclick="toggleSubAccounts('ACC-7890', this)" title="Show sub-accounts"><i class="fas fa-plus"></i></button>
                                    <div class="client-name-content">
                                        <a href="{{ route('admin.accounts.details', 'ACC-7890') }}" class="client-name">NewClient</a>
                                        <div class="account-number">ACC-7890</div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge light badge-info">Test</span></td>
                            <td class="text-end">0</td>
                            <td class="text-end">47</td>
                            <td class="text-end">£0</td>
                            <td class="text-center">
                                <div class="action-icons">
                                    <button class="action-icon-btn" onclick="openBillingModal('ACC-7890', 'NewClient')" title="Billing"><i class="fas fa-pound-sign"></i></button>
                                    <button class="action-icon-btn" onclick="rowAction('ACC-7890', 'NewClient', 'pricing')" title="Pricing"><i class="fas fa-tags"></i></button>
                                    <button class="action-icon-btn" onclick="rowAction('ACC-7890', 'NewClient', 'routing')" title="Routing"><i class="fas fa-route"></i></button>
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="dropdown">
                                    <button class="btn btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" href="#" onclick="rowAction('ACC-7890', 'NewClient', 'add_credit')"><i class="fas fa-plus-circle me-2 text-success"></i>Add Credit</a></li>
                                        <li><a class="dropdown-item" href="#" onclick="rowAction('ACC-7890', 'NewClient', 'change_name')"><i class="fas fa-edit me-2"></i>Change Account Name</a></li>
                                        <li><a class="dropdown-item" href="#" onclick="openAccountDetail('ACC-7890')"><i class="fas fa-cog me-2"></i>Edit Account Details</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="#" onclick="openFraudRisk('ACC-7890', 'NewClient')"><i class="fas fa-shield-alt me-2 text-danger"></i>Fraud & Risk</a></li>
                                        <li><a class="dropdown-item text-warning" href="#" onclick="rowAction('ACC-7890', 'NewClient', 'suspend')"><i class="fas fa-pause-circle me-2"></i>Suspend Account</a></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        <tr class="sub-account-row" data-parent="ACC-7890">
                            <td colspan="7">
                                <div class="sub-account-table-wrapper">
                                    <span class="no-sub-accounts">No sub-accounts configured</span>
                                </div>
                            </td>
                        </tr>
                        <tr class="account-row" data-account="ACC-4567" data-name="TestCo" data-status="suspended" data-volume-year="234567" data-volume-month="0" data-balance="-240">
                            <td class="client-name-cell">
                                <div class="client-name-wrapper">
                                    <button class="expand-toggle" onclick="toggleSubAccounts('ACC-4567', this)" title="Show sub-accounts"><i class="fas fa-plus"></i></button>
                                    <div class="client-name-content">
                                        <a href="{{ route('admin.accounts.details', 'ACC-4567') }}" class="client-name">TestCo</a>
                                        <div class="account-number">ACC-4567</div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge light badge-danger">Suspended</span></td>
                            <td class="text-end">234,567</td>
                            <td class="text-end">0</td>
                            <td class="text-end text-danger">-£240</td>
                            <td class="text-center">
                                <div class="action-icons">
                                    <button class="action-icon-btn" onclick="openBillingModal('ACC-4567', 'TestCo')" title="Billing"><i class="fas fa-pound-sign"></i></button>
                                    <button class="action-icon-btn" onclick="rowAction('ACC-4567', 'TestCo', 'pricing')" title="Pricing"><i class="fas fa-tags"></i></button>
                                    <button class="action-icon-btn" onclick="rowAction('ACC-4567', 'TestCo', 'routing')" title="Routing"><i class="fas fa-route"></i></button>
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="dropdown">
                                    <button class="btn btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" href="#" onclick="rowAction('ACC-4567', 'TestCo', 'add_credit')"><i class="fas fa-plus-circle me-2 text-success"></i>Add Credit</a></li>
                                        <li><a class="dropdown-item" href="#" onclick="rowAction('ACC-4567', 'TestCo', 'change_name')"><i class="fas fa-edit me-2"></i>Change Account Name</a></li>
                                        <li><a class="dropdown-item" href="#" onclick="openAccountDetail('ACC-4567')"><i class="fas fa-cog me-2"></i>Edit Account Details</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="#" onclick="openFraudRisk('ACC-4567', 'TestCo')"><i class="fas fa-shield-alt me-2 text-danger"></i>Fraud & Risk</a></li>
                                        <li><a class="dropdown-item text-success" href="#" onclick="rowAction('ACC-4567', 'TestCo', 'reactivate')"><i class="fas fa-play-circle me-2"></i>Reactivate Account</a></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        <tr class="sub-account-row" data-parent="ACC-4567">
                            <td colspan="7">
                                <div class="sub-account-table-wrapper">
                                    <table class="sub-account-table">
                                        <thead><tr><th>Sub-Account</th><th class="text-end">Volume (Month)</th><th class="text-end">Volume (Year)</th><th>Status</th></tr></thead>
                                        <tbody>
                                            <tr><td class="sub-name">TestCo Dev</td><td class="text-end">0</td><td class="text-end">234,567</td><td><span class="badge light badge-danger">Suspended</span></td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </td>
                        </tr>
                        <tr class="account-row" data-account="ACC-9012" data-name="HighRisk Corp" data-status="live" data-volume-year="5678901" data-volume-month="456789" data-balance="3250">
                            <td class="client-name-cell">
                                <div class="client-name-wrapper">
                                    <button class="expand-toggle" onclick="toggleSubAccounts('ACC-9012', this)" title="Show sub-accounts"><i class="fas fa-plus"></i></button>
                                    <div class="client-name-content">
                                        <a href="{{ route('admin.accounts.details', 'ACC-9012') }}" class="client-name">HighRisk Corp</a>
                                        <div class="account-number">ACC-9012</div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge light badge-success">Live</span></td>
                            <td class="text-end">5,678,901</td>
                            <td class="text-end">456,789</td>
                            <td class="text-end">£3,250</td>
                            <td class="text-center">
                                <div class="action-icons">
                                    <button class="action-icon-btn" onclick="openBillingModal('ACC-9012', 'HighRisk Corp')" title="Billing"><i class="fas fa-pound-sign"></i></button>
                                    <button class="action-icon-btn" onclick="rowAction('ACC-9012', 'HighRisk Corp', 'pricing')" title="Pricing"><i class="fas fa-tags"></i></button>
                                    <button class="action-icon-btn" onclick="rowAction('ACC-9012', 'HighRisk Corp', 'routing')" title="Routing"><i class="fas fa-route"></i></button>
                                </div>
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
                        <tr class="sub-account-row" data-parent="ACC-9012">
                            <td colspan="7">
                                <div class="sub-account-table-wrapper">
                                    <table class="sub-account-table">
                                        <thead><tr><th>Sub-Account</th><th class="text-end">Volume (Month)</th><th class="text-end">Volume (Year)</th><th>Status</th></tr></thead>
                                        <tbody>
                                            <tr><td class="sub-name">HighRisk Alerts</td><td class="text-end">300,456</td><td class="text-end">3,567,890</td><td><span class="badge light badge-warning">Restricted</span></td></tr>
                                            <tr><td class="sub-name">HighRisk Promos</td><td class="text-end">156,333</td><td class="text-end">2,111,011</td><td><span class="badge light badge-danger">Blocked</span></td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </td>
                        </tr>
                        <tr class="account-row" data-account="ACC-3456" data-name="MedTech Solutions" data-status="live" data-volume-year="8901234" data-volume-month="743102" data-balance="8900">
                            <td class="client-name-cell">
                                <div class="client-name-wrapper">
                                    <button class="expand-toggle" onclick="toggleSubAccounts('ACC-3456', this)" title="Show sub-accounts"><i class="fas fa-plus"></i></button>
                                    <div class="client-name-content">
                                        <a href="{{ route('admin.accounts.details', 'ACC-3456') }}" class="client-name">MedTech Solutions</a>
                                        <div class="account-number">ACC-3456</div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge light badge-success">Live</span></td>
                            <td class="text-end">8,901,234</td>
                            <td class="text-end">743,102</td>
                            <td class="text-end">£8,900</td>
                            <td class="text-center">
                                <div class="action-icons">
                                    <button class="action-icon-btn" onclick="openBillingModal('ACC-3456', 'MedTech Solutions')" title="Billing"><i class="fas fa-pound-sign"></i></button>
                                    <button class="action-icon-btn" onclick="rowAction('ACC-3456', 'MedTech Solutions', 'pricing')" title="Pricing"><i class="fas fa-tags"></i></button>
                                    <button class="action-icon-btn" onclick="rowAction('ACC-3456', 'MedTech Solutions', 'routing')" title="Routing"><i class="fas fa-route"></i></button>
                                </div>
                            </td>
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
                        <tr class="sub-account-row" data-parent="ACC-3456">
                            <td colspan="7">
                                <div class="sub-account-table-wrapper">
                                    <table class="sub-account-table">
                                        <thead><tr><th>Sub-Account</th><th class="text-end">Volume (Month)</th><th class="text-end">Volume (Year)</th><th>Status</th></tr></thead>
                                        <tbody>
                                            <tr><td class="sub-name">MedTech Alerts</td><td class="text-end">450,000</td><td class="text-end">5,400,123</td><td><span class="badge light badge-success">Active</span></td></tr>
                                            <tr><td class="sub-name">MedTech Reminders</td><td class="text-end">293,102</td><td class="text-end">3,501,111</td><td><span class="badge light badge-success">Active</span></td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </td>
                        </tr>
                        <tr class="account-row" data-account="ACC-4001" data-name="RetailMax Group" data-status="live" data-volume-year="6234567" data-volume-month="521234" data-balance="4320">
                            <td class="client-name-cell">
                                <div class="client-name-wrapper">
                                    <button class="expand-toggle" onclick="toggleSubAccounts('ACC-4001', this)" title="Show sub-accounts"><i class="fas fa-plus"></i></button>
                                    <div class="client-name-content">
                                        <a href="{{ route('admin.accounts.details', 'ACC-4001') }}" class="client-name">RetailMax Group</a>
                                        <div class="account-number">ACC-4001</div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge light badge-success">Live</span></td>
                            <td class="text-end">6,234,567</td>
                            <td class="text-end">521,234</td>
                            <td class="text-end">£4,320</td>
                            <td class="text-center"><div class="action-icons"><button class="action-icon-btn" onclick="openBillingModal('ACC-4001', 'RetailMax Group')" title="Billing"><i class="fas fa-pound-sign"></i></button><button class="action-icon-btn" onclick="rowAction('ACC-4001', 'RetailMax Group', 'pricing')" title="Pricing"><i class="fas fa-tags"></i></button><button class="action-icon-btn" onclick="rowAction('ACC-4001', 'RetailMax Group', 'routing')" title="Routing"><i class="fas fa-route"></i></button></div></td>
                            <td class="text-center"><div class="dropdown"><button class="btn btn-sm" type="button" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-v"></i></button><ul class="dropdown-menu dropdown-menu-end"><li><a class="dropdown-item" href="#">View Details</a></li></ul></div></td>
                        </tr>
                        <tr class="sub-account-row" data-parent="ACC-4001">
                            <td colspan="7">
                                <div class="sub-account-table-wrapper">
                                    <table class="sub-account-table">
                                        <thead><tr><th>Sub-Account</th><th class="text-end">Volume (Month)</th><th class="text-end">Volume (Year)</th><th>Status</th></tr></thead>
                                        <tbody>
                                            <tr><td class="sub-name">RetailMax Online</td><td class="text-end">321,234</td><td class="text-end">3,900,000</td><td><span class="badge light badge-success">Active</span></td></tr>
                                            <tr><td class="sub-name">RetailMax Stores</td><td class="text-end">200,000</td><td class="text-end">2,334,567</td><td><span class="badge light badge-success">Active</span></td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </td>
                        </tr>
                        <tr class="account-row" data-account="ACC-4002" data-name="CloudFirst Tech" data-status="live" data-volume-year="4567890" data-volume-month="382456" data-balance="3150">
                            <td class="client-name-cell">
                                <div class="client-name-wrapper">
                                    <button class="expand-toggle" onclick="toggleSubAccounts('ACC-4002', this)" title="Show sub-accounts"><i class="fas fa-plus"></i></button>
                                    <div class="client-name-content">
                                        <a href="{{ route('admin.accounts.details', 'ACC-4002') }}" class="client-name">CloudFirst Tech</a>
                                        <div class="account-number">ACC-4002</div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge light badge-success">Live</span></td>
                            <td class="text-end">4,567,890</td>
                            <td class="text-end">382,456</td>
                            <td class="text-end">£3,150</td>
                            <td class="text-center"><div class="action-icons"><button class="action-icon-btn" onclick="openBillingModal('ACC-4002', 'CloudFirst Tech')" title="Billing"><i class="fas fa-pound-sign"></i></button><button class="action-icon-btn" onclick="rowAction('ACC-4002', 'CloudFirst Tech', 'pricing')" title="Pricing"><i class="fas fa-tags"></i></button><button class="action-icon-btn" onclick="rowAction('ACC-4002', 'CloudFirst Tech', 'routing')" title="Routing"><i class="fas fa-route"></i></button></div></td>
                            <td class="text-center"><div class="dropdown"><button class="btn btn-sm" type="button" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-v"></i></button><ul class="dropdown-menu dropdown-menu-end"><li><a class="dropdown-item" href="#">View Details</a></li></ul></div></td>
                        </tr>
                        <tr class="sub-account-row" data-parent="ACC-4002"><td colspan="7"><div class="sub-account-table-wrapper"><table class="sub-account-table"><thead><tr><th>Sub-Account</th><th class="text-end">Volume (Month)</th><th class="text-end">Volume (Year)</th><th>Status</th></tr></thead><tbody><tr><td class="sub-name">CloudFirst SaaS</td><td class="text-end">382,456</td><td class="text-end">4,567,890</td><td><span class="badge light badge-success">Active</span></td></tr></tbody></table></div></td></tr>
                        <tr class="account-row" data-account="ACC-4003" data-name="GreenEnergy Ltd" data-status="live" data-volume-year="3456789" data-volume-month="287654" data-balance="2890">
                            <td class="client-name-cell"><div class="client-name-wrapper"><button class="expand-toggle" onclick="toggleSubAccounts('ACC-4003', this)" title="Show sub-accounts"><i class="fas fa-plus"></i></button><div class="client-name-content"><a href="{{ route('admin.accounts.details', 'ACC-4003') }}" class="client-name">GreenEnergy Ltd</a><div class="account-number">ACC-4003</div></div></div></td>
                            <td><span class="badge light badge-success">Live</span></td>
                            <td class="text-end">3,456,789</td>
                            <td class="text-end">287,654</td>
                            <td class="text-end">£2,890</td>
                            <td class="text-center"><div class="action-icons"><button class="action-icon-btn" onclick="openBillingModal('ACC-4003', 'GreenEnergy Ltd')" title="Billing"><i class="fas fa-pound-sign"></i></button><button class="action-icon-btn" onclick="rowAction('ACC-4003', 'GreenEnergy Ltd', 'pricing')" title="Pricing"><i class="fas fa-tags"></i></button><button class="action-icon-btn" onclick="rowAction('ACC-4003', 'GreenEnergy Ltd', 'routing')" title="Routing"><i class="fas fa-route"></i></button></div></td>
                            <td class="text-center"><div class="dropdown"><button class="btn btn-sm" type="button" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-v"></i></button><ul class="dropdown-menu dropdown-menu-end"><li><a class="dropdown-item" href="#">View Details</a></li></ul></div></td>
                        </tr>
                        <tr class="sub-account-row" data-parent="ACC-4003"><td colspan="7"><div class="sub-account-table-wrapper"><span class="no-sub-accounts">No sub-accounts configured</span></div></td></tr>
                        <tr class="account-row" data-account="ACC-4004" data-name="FastLogistics" data-status="test" data-volume-year="0" data-volume-month="156" data-balance="0">
                            <td class="client-name-cell"><div class="client-name-wrapper"><button class="expand-toggle" onclick="toggleSubAccounts('ACC-4004', this)" title="Show sub-accounts"><i class="fas fa-plus"></i></button><div class="client-name-content"><a href="{{ route('admin.accounts.details', 'ACC-4004') }}" class="client-name">FastLogistics</a><div class="account-number">ACC-4004</div></div></div></td>
                            <td><span class="badge light badge-info">Test</span></td>
                            <td class="text-end">0</td>
                            <td class="text-end">156</td>
                            <td class="text-end">£0</td>
                            <td class="text-center"><div class="action-icons"><button class="action-icon-btn" onclick="openBillingModal('ACC-4004', 'FastLogistics')" title="Billing"><i class="fas fa-pound-sign"></i></button><button class="action-icon-btn" onclick="rowAction('ACC-4004', 'FastLogistics', 'pricing')" title="Pricing"><i class="fas fa-tags"></i></button><button class="action-icon-btn" onclick="rowAction('ACC-4004', 'FastLogistics', 'routing')" title="Routing"><i class="fas fa-route"></i></button></div></td>
                            <td class="text-center"><div class="dropdown"><button class="btn btn-sm" type="button" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-v"></i></button><ul class="dropdown-menu dropdown-menu-end"><li><a class="dropdown-item" href="#">View Details</a></li></ul></div></td>
                        </tr>
                        <tr class="sub-account-row" data-parent="ACC-4004"><td colspan="7"><div class="sub-account-table-wrapper"><span class="no-sub-accounts">No sub-accounts configured</span></div></td></tr>
                        <tr class="account-row" data-account="ACC-4005" data-name="HealthPlus Care" data-status="live" data-volume-year="7890123" data-volume-month="654321" data-balance="6540">
                            <td class="client-name-cell"><div class="client-name-wrapper"><button class="expand-toggle" onclick="toggleSubAccounts('ACC-4005', this)" title="Show sub-accounts"><i class="fas fa-plus"></i></button><div class="client-name-content"><a href="{{ route('admin.accounts.details', 'ACC-4005') }}" class="client-name">HealthPlus Care</a><div class="account-number">ACC-4005</div></div></div></td>
                            <td><span class="badge light badge-success">Live</span></td>
                            <td class="text-end">7,890,123</td>
                            <td class="text-end">654,321</td>
                            <td class="text-end">£6,540</td>
                            <td class="text-center"><div class="action-icons"><button class="action-icon-btn" onclick="openBillingModal('ACC-4005', 'HealthPlus Care')" title="Billing"><i class="fas fa-pound-sign"></i></button><button class="action-icon-btn" onclick="rowAction('ACC-4005', 'HealthPlus Care', 'pricing')" title="Pricing"><i class="fas fa-tags"></i></button><button class="action-icon-btn" onclick="rowAction('ACC-4005', 'HealthPlus Care', 'routing')" title="Routing"><i class="fas fa-route"></i></button></div></td>
                            <td class="text-center"><div class="dropdown"><button class="btn btn-sm" type="button" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-v"></i></button><ul class="dropdown-menu dropdown-menu-end"><li><a class="dropdown-item" href="#">View Details</a></li></ul></div></td>
                        </tr>
                        <tr class="sub-account-row" data-parent="ACC-4005"><td colspan="7"><div class="sub-account-table-wrapper"><table class="sub-account-table"><thead><tr><th>Sub-Account</th><th class="text-end">Volume (Month)</th><th class="text-end">Volume (Year)</th><th>Status</th></tr></thead><tbody><tr><td class="sub-name">HealthPlus Clinics</td><td class="text-end">400,000</td><td class="text-end">4,800,000</td><td><span class="badge light badge-success">Active</span></td></tr><tr><td class="sub-name">HealthPlus Labs</td><td class="text-end">254,321</td><td class="text-end">3,090,123</td><td><span class="badge light badge-success">Active</span></td></tr></tbody></table></div></td></tr>
                        <tr class="account-row" data-account="ACC-4006" data-name="EduLearn Academy" data-status="live" data-volume-year="2345678" data-volume-month="198765" data-balance="1980">
                            <td class="client-name-cell"><div class="client-name-wrapper"><button class="expand-toggle" onclick="toggleSubAccounts('ACC-4006', this)" title="Show sub-accounts"><i class="fas fa-plus"></i></button><div class="client-name-content"><a href="{{ route('admin.accounts.details', 'ACC-4006') }}" class="client-name">EduLearn Academy</a><div class="account-number">ACC-4006</div></div></div></td>
                            <td><span class="badge light badge-success">Live</span></td>
                            <td class="text-end">2,345,678</td>
                            <td class="text-end">198,765</td>
                            <td class="text-end">£1,980</td>
                            <td class="text-center"><div class="action-icons"><button class="action-icon-btn" onclick="openBillingModal('ACC-4006', 'EduLearn Academy')" title="Billing"><i class="fas fa-pound-sign"></i></button><button class="action-icon-btn" onclick="rowAction('ACC-4006', 'EduLearn Academy', 'pricing')" title="Pricing"><i class="fas fa-tags"></i></button><button class="action-icon-btn" onclick="rowAction('ACC-4006', 'EduLearn Academy', 'routing')" title="Routing"><i class="fas fa-route"></i></button></div></td>
                            <td class="text-center"><div class="dropdown"><button class="btn btn-sm" type="button" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-v"></i></button><ul class="dropdown-menu dropdown-menu-end"><li><a class="dropdown-item" href="#">View Details</a></li></ul></div></td>
                        </tr>
                        <tr class="sub-account-row" data-parent="ACC-4006"><td colspan="7"><div class="sub-account-table-wrapper"><span class="no-sub-accounts">No sub-accounts configured</span></div></td></tr>
                        <tr class="account-row" data-account="ACC-4007" data-name="AutoDrive Motors" data-status="live" data-volume-year="5678901" data-volume-month="476543" data-balance="4760">
                            <td class="client-name-cell"><div class="client-name-wrapper"><button class="expand-toggle" onclick="toggleSubAccounts('ACC-4007', this)" title="Show sub-accounts"><i class="fas fa-plus"></i></button><div class="client-name-content"><a href="{{ route('admin.accounts.details', 'ACC-4007') }}" class="client-name">AutoDrive Motors</a><div class="account-number">ACC-4007</div></div></div></td>
                            <td><span class="badge light badge-success">Live</span></td>
                            <td class="text-end">5,678,901</td>
                            <td class="text-end">476,543</td>
                            <td class="text-end">£4,760</td>
                            <td class="text-center"><div class="action-icons"><button class="action-icon-btn" onclick="openBillingModal('ACC-4007', 'AutoDrive Motors')" title="Billing"><i class="fas fa-pound-sign"></i></button><button class="action-icon-btn" onclick="rowAction('ACC-4007', 'AutoDrive Motors', 'pricing')" title="Pricing"><i class="fas fa-tags"></i></button><button class="action-icon-btn" onclick="rowAction('ACC-4007', 'AutoDrive Motors', 'routing')" title="Routing"><i class="fas fa-route"></i></button></div></td>
                            <td class="text-center"><div class="dropdown"><button class="btn btn-sm" type="button" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-v"></i></button><ul class="dropdown-menu dropdown-menu-end"><li><a class="dropdown-item" href="#">View Details</a></li></ul></div></td>
                        </tr>
                        <tr class="sub-account-row" data-parent="ACC-4007"><td colspan="7"><div class="sub-account-table-wrapper"><table class="sub-account-table"><thead><tr><th>Sub-Account</th><th class="text-end">Volume (Month)</th><th class="text-end">Volume (Year)</th><th>Status</th></tr></thead><tbody><tr><td class="sub-name">AutoDrive Sales</td><td class="text-end">300,000</td><td class="text-end">3,500,000</td><td><span class="badge light badge-success">Active</span></td></tr><tr><td class="sub-name">AutoDrive Service</td><td class="text-end">176,543</td><td class="text-end">2,178,901</td><td><span class="badge light badge-success">Active</span></td></tr></tbody></table></div></td></tr>
                        <tr class="account-row" data-account="ACC-4008" data-name="TechStartup Inc" data-status="test" data-volume-year="0" data-volume-month="89" data-balance="0">
                            <td class="client-name-cell"><div class="client-name-wrapper"><button class="expand-toggle" onclick="toggleSubAccounts('ACC-4008', this)" title="Show sub-accounts"><i class="fas fa-plus"></i></button><div class="client-name-content"><a href="{{ route('admin.accounts.details', 'ACC-4008') }}" class="client-name">TechStartup Inc</a><div class="account-number">ACC-4008</div></div></div></td>
                            <td><span class="badge light badge-info">Test</span></td>
                            <td class="text-end">0</td>
                            <td class="text-end">89</td>
                            <td class="text-end">£0</td>
                            <td class="text-center"><div class="action-icons"><button class="action-icon-btn" onclick="openBillingModal('ACC-4008', 'TechStartup Inc')" title="Billing"><i class="fas fa-pound-sign"></i></button><button class="action-icon-btn" onclick="rowAction('ACC-4008', 'TechStartup Inc', 'pricing')" title="Pricing"><i class="fas fa-tags"></i></button><button class="action-icon-btn" onclick="rowAction('ACC-4008', 'TechStartup Inc', 'routing')" title="Routing"><i class="fas fa-route"></i></button></div></td>
                            <td class="text-center"><div class="dropdown"><button class="btn btn-sm" type="button" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-v"></i></button><ul class="dropdown-menu dropdown-menu-end"><li><a class="dropdown-item" href="#">View Details</a></li></ul></div></td>
                        </tr>
                        <tr class="sub-account-row" data-parent="ACC-4008"><td colspan="7"><div class="sub-account-table-wrapper"><span class="no-sub-accounts">No sub-accounts configured</span></div></td></tr>
                        <tr class="account-row" data-account="ACC-4009" data-name="FoodDelivery Pro" data-status="live" data-volume-year="9012345" data-volume-month="756789" data-balance="7560">
                            <td class="client-name-cell"><div class="client-name-wrapper"><button class="expand-toggle" onclick="toggleSubAccounts('ACC-4009', this)" title="Show sub-accounts"><i class="fas fa-plus"></i></button><div class="client-name-content"><a href="{{ route('admin.accounts.details', 'ACC-4009') }}" class="client-name">FoodDelivery Pro</a><div class="account-number">ACC-4009</div></div></div></td>
                            <td><span class="badge light badge-success">Live</span></td>
                            <td class="text-end">9,012,345</td>
                            <td class="text-end">756,789</td>
                            <td class="text-end">£7,560</td>
                            <td class="text-center"><div class="action-icons"><button class="action-icon-btn" onclick="openBillingModal('ACC-4009', 'FoodDelivery Pro')" title="Billing"><i class="fas fa-pound-sign"></i></button><button class="action-icon-btn" onclick="rowAction('ACC-4009', 'FoodDelivery Pro', 'pricing')" title="Pricing"><i class="fas fa-tags"></i></button><button class="action-icon-btn" onclick="rowAction('ACC-4009', 'FoodDelivery Pro', 'routing')" title="Routing"><i class="fas fa-route"></i></button></div></td>
                            <td class="text-center"><div class="dropdown"><button class="btn btn-sm" type="button" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-v"></i></button><ul class="dropdown-menu dropdown-menu-end"><li><a class="dropdown-item" href="#">View Details</a></li></ul></div></td>
                        </tr>
                        <tr class="sub-account-row" data-parent="ACC-4009"><td colspan="7"><div class="sub-account-table-wrapper"><table class="sub-account-table"><thead><tr><th>Sub-Account</th><th class="text-end">Volume (Month)</th><th class="text-end">Volume (Year)</th><th>Status</th></tr></thead><tbody><tr><td class="sub-name">FoodDelivery Express</td><td class="text-end">500,000</td><td class="text-end">6,000,000</td><td><span class="badge light badge-success">Active</span></td></tr><tr><td class="sub-name">FoodDelivery Premium</td><td class="text-end">256,789</td><td class="text-end">3,012,345</td><td><span class="badge light badge-success">Active</span></td></tr></tbody></table></div></td></tr>
                        <tr class="account-row" data-account="ACC-4010" data-name="PropertyHub UK" data-status="live" data-volume-year="4123456" data-volume-month="345678" data-balance="3450">
                            <td class="client-name-cell"><div class="client-name-wrapper"><button class="expand-toggle" onclick="toggleSubAccounts('ACC-4010', this)" title="Show sub-accounts"><i class="fas fa-plus"></i></button><div class="client-name-content"><a href="{{ route('admin.accounts.details', 'ACC-4010') }}" class="client-name">PropertyHub UK</a><div class="account-number">ACC-4010</div></div></div></td>
                            <td><span class="badge light badge-success">Live</span></td>
                            <td class="text-end">4,123,456</td>
                            <td class="text-end">345,678</td>
                            <td class="text-end">£3,450</td>
                            <td class="text-center"><div class="action-icons"><button class="action-icon-btn" onclick="openBillingModal('ACC-4010', 'PropertyHub UK')" title="Billing"><i class="fas fa-pound-sign"></i></button><button class="action-icon-btn" onclick="rowAction('ACC-4010', 'PropertyHub UK', 'pricing')" title="Pricing"><i class="fas fa-tags"></i></button><button class="action-icon-btn" onclick="rowAction('ACC-4010', 'PropertyHub UK', 'routing')" title="Routing"><i class="fas fa-route"></i></button></div></td>
                            <td class="text-center"><div class="dropdown"><button class="btn btn-sm" type="button" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-v"></i></button><ul class="dropdown-menu dropdown-menu-end"><li><a class="dropdown-item" href="#">View Details</a></li></ul></div></td>
                        </tr>
                        <tr class="sub-account-row" data-parent="ACC-4010"><td colspan="7"><div class="sub-account-table-wrapper"><span class="no-sub-accounts">No sub-accounts configured</span></div></td></tr>
                        <tr class="account-row" data-account="ACC-4011" data-name="TravelWorld Agency" data-status="live" data-volume-year="3789012" data-volume-month="312456" data-balance="3120">
                            <td class="client-name-cell"><div class="client-name-wrapper"><button class="expand-toggle" onclick="toggleSubAccounts('ACC-4011', this)" title="Show sub-accounts"><i class="fas fa-plus"></i></button><div class="client-name-content"><a href="{{ route('admin.accounts.details', 'ACC-4011') }}" class="client-name">TravelWorld Agency</a><div class="account-number">ACC-4011</div></div></div></td>
                            <td><span class="badge light badge-success">Live</span></td>
                            <td class="text-end">3,789,012</td>
                            <td class="text-end">312,456</td>
                            <td class="text-end">£3,120</td>
                            <td class="text-center"><div class="action-icons"><button class="action-icon-btn" onclick="openBillingModal('ACC-4011', 'TravelWorld Agency')" title="Billing"><i class="fas fa-pound-sign"></i></button><button class="action-icon-btn" onclick="rowAction('ACC-4011', 'TravelWorld Agency', 'pricing')" title="Pricing"><i class="fas fa-tags"></i></button><button class="action-icon-btn" onclick="rowAction('ACC-4011', 'TravelWorld Agency', 'routing')" title="Routing"><i class="fas fa-route"></i></button></div></td>
                            <td class="text-center"><div class="dropdown"><button class="btn btn-sm" type="button" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-v"></i></button><ul class="dropdown-menu dropdown-menu-end"><li><a class="dropdown-item" href="#">View Details</a></li></ul></div></td>
                        </tr>
                        <tr class="sub-account-row" data-parent="ACC-4011"><td colspan="7"><div class="sub-account-table-wrapper"><table class="sub-account-table"><thead><tr><th>Sub-Account</th><th class="text-end">Volume (Month)</th><th class="text-end">Volume (Year)</th><th>Status</th></tr></thead><tbody><tr><td class="sub-name">TravelWorld Holidays</td><td class="text-end">200,000</td><td class="text-end">2,400,000</td><td><span class="badge light badge-success">Active</span></td></tr><tr><td class="sub-name">TravelWorld Business</td><td class="text-end">112,456</td><td class="text-end">1,389,012</td><td><span class="badge light badge-success">Active</span></td></tr></tbody></table></div></td></tr>
                        <tr class="account-row" data-account="ACC-4012" data-name="SecureBank Financial" data-status="live" data-volume-year="12345678" data-volume-month="1034567" data-balance="10340">
                            <td class="client-name-cell"><div class="client-name-wrapper"><button class="expand-toggle" onclick="toggleSubAccounts('ACC-4012', this)" title="Show sub-accounts"><i class="fas fa-plus"></i></button><div class="client-name-content"><a href="{{ route('admin.accounts.details', 'ACC-4012') }}" class="client-name">SecureBank Financial</a><div class="account-number">ACC-4012</div></div></div></td>
                            <td><span class="badge light badge-success">Live</span></td>
                            <td class="text-end">12,345,678</td>
                            <td class="text-end">1,034,567</td>
                            <td class="text-end">£10,340</td>
                            <td class="text-center"><div class="action-icons"><button class="action-icon-btn" onclick="openBillingModal('ACC-4012', 'SecureBank Financial')" title="Billing"><i class="fas fa-pound-sign"></i></button><button class="action-icon-btn" onclick="rowAction('ACC-4012', 'SecureBank Financial', 'pricing')" title="Pricing"><i class="fas fa-tags"></i></button><button class="action-icon-btn" onclick="rowAction('ACC-4012', 'SecureBank Financial', 'routing')" title="Routing"><i class="fas fa-route"></i></button></div></td>
                            <td class="text-center"><div class="dropdown"><button class="btn btn-sm" type="button" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-v"></i></button><ul class="dropdown-menu dropdown-menu-end"><li><a class="dropdown-item" href="#">View Details</a></li></ul></div></td>
                        </tr>
                        <tr class="sub-account-row" data-parent="ACC-4012"><td colspan="7"><div class="sub-account-table-wrapper"><table class="sub-account-table"><thead><tr><th>Sub-Account</th><th class="text-end">Volume (Month)</th><th class="text-end">Volume (Year)</th><th>Status</th></tr></thead><tbody><tr><td class="sub-name">SecureBank Retail</td><td class="text-end">600,000</td><td class="text-end">7,200,000</td><td><span class="badge light badge-success">Active</span></td></tr><tr><td class="sub-name">SecureBank Business</td><td class="text-end">434,567</td><td class="text-end">5,145,678</td><td><span class="badge light badge-success">Active</span></td></tr></tbody></table></div></td></tr>
                        <tr class="account-row" data-account="ACC-4013" data-name="SmartHome Systems" data-status="test" data-volume-year="0" data-volume-month="234" data-balance="0">
                            <td class="client-name-cell"><div class="client-name-wrapper"><button class="expand-toggle" onclick="toggleSubAccounts('ACC-4013', this)" title="Show sub-accounts"><i class="fas fa-plus"></i></button><div class="client-name-content"><a href="{{ route('admin.accounts.details', 'ACC-4013') }}" class="client-name">SmartHome Systems</a><div class="account-number">ACC-4013</div></div></div></td>
                            <td><span class="badge light badge-info">Test</span></td>
                            <td class="text-end">0</td>
                            <td class="text-end">234</td>
                            <td class="text-end">£0</td>
                            <td class="text-center"><div class="action-icons"><button class="action-icon-btn" onclick="openBillingModal('ACC-4013', 'SmartHome Systems')" title="Billing"><i class="fas fa-pound-sign"></i></button><button class="action-icon-btn" onclick="rowAction('ACC-4013', 'SmartHome Systems', 'pricing')" title="Pricing"><i class="fas fa-tags"></i></button><button class="action-icon-btn" onclick="rowAction('ACC-4013', 'SmartHome Systems', 'routing')" title="Routing"><i class="fas fa-route"></i></button></div></td>
                            <td class="text-center"><div class="dropdown"><button class="btn btn-sm" type="button" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-v"></i></button><ul class="dropdown-menu dropdown-menu-end"><li><a class="dropdown-item" href="#">View Details</a></li></ul></div></td>
                        </tr>
                        <tr class="sub-account-row" data-parent="ACC-4013"><td colspan="7"><div class="sub-account-table-wrapper"><span class="no-sub-accounts">No sub-accounts configured</span></div></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <span class="text-muted small" id="paginationInfo">Showing 1-20 of 847 accounts</span>
                <div class="d-flex align-items-center gap-2">
                    <label class="text-muted small mb-0">Per page:</label>
                    <select class="form-select form-select-sm" id="pageSizeSelect" style="width: auto;">
                        <option value="10">10</option>
                        <option value="20" selected>20</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>
            <nav>
                <ul class="pagination pagination-sm mb-0" id="paginationNav">
                    <li class="page-item disabled"><a class="page-link" href="#" data-page="prev">Previous</a></li>
                    <li class="page-item active"><a class="page-link" href="#" data-page="1">1</a></li>
                    <li class="page-item"><a class="page-link" href="#" data-page="2">2</a></li>
                    <li class="page-item"><a class="page-link" href="#" data-page="3">3</a></li>
                    <li class="page-item"><a class="page-link" href="#" data-page="...">...</a></li>
                    <li class="page-item"><a class="page-link" href="#" data-page="43">43</a></li>
                    <li class="page-item"><a class="page-link" href="#" data-page="next">Next</a></li>
                </ul>
            </nav>
        </div>
    </div>
</div>

<!-- Fraud & Risk Modal -->
<div class="modal fade" id="fraudRiskModal" tabindex="-1" aria-labelledby="fraudRiskModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="fraudRiskModalLabel">Fraud & Risk Controls</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="fraudAccountId">
                <input type="hidden" id="fraudAccountName">
                
                <!-- Suspicion Flag -->
                <div class="card mb-3">
                    <div class="card-header py-2 bg-light">
                        <strong>Account Status</strong>
                    </div>
                    <div class="card-body py-3">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" id="flagSuspicious">
                            <label class="form-check-label" for="flagSuspicious">
                                <strong class="text-danger">Flag as Suspicious</strong>
                            </label>
                        </div>
                        <small class="text-muted">Flagged accounts appear in the Fraud/Risk KPI tile and are marked in the accounts table.</small>
                    </div>
                </div>

                <!-- Restrictions -->
                <div class="card mb-3">
                    <div class="card-header py-2 bg-light">
                        <strong>Sending Restrictions</strong>
                    </div>
                    <div class="card-body py-3">
                        <div class="mb-3">
                            <label class="form-label">Restricted Countries</label>
                            <select class="form-select" id="restrictedCountries" multiple size="4">
                                <option value="NG">Nigeria</option>
                                <option value="PH">Philippines</option>
                                <option value="IN">India</option>
                                <option value="PK">Pakistan</option>
                                <option value="BD">Bangladesh</option>
                                <option value="GH">Ghana</option>
                                <option value="KE">Kenya</option>
                            </select>
                            <small class="text-muted">Hold Ctrl/Cmd to select multiple. Messages to these countries will be blocked.</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Allowed Sender IDs</label>
                            <input type="text" class="form-control" id="allowedSenderIds" placeholder="e.g., ACME, AcmeCorp (comma-separated)">
                            <small class="text-muted">Leave blank for no restriction. Only these Sender IDs will be permitted.</small>
                        </div>
                        
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="blockDynamicSenderId">
                            <label class="form-check-label" for="blockDynamicSenderId">
                                Block Dynamic Sender ID Usage
                            </label>
                        </div>
                        <small class="text-muted">Prevents account from using dynamic/custom sender IDs per message.</small>
                    </div>
                </div>

                <!-- Test Mode -->
                <div class="card">
                    <div class="card-header py-2 bg-light">
                        <strong>Test Mode</strong>
                    </div>
                    <div class="card-body py-3">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" id="enableTestMode">
                            <label class="form-check-label" for="enableTestMode">
                                Enable Test Mode
                            </label>
                        </div>
                        <small class="text-muted d-block mb-3">When enabled, messages can only be sent to approved test numbers.</small>
                        
                        <div class="mb-0" id="testNumbersSection" style="display:none;">
                            <label class="form-label">Approved Test Numbers</label>
                            <textarea class="form-control" id="testNumbers" rows="3" placeholder="Enter numbers, one per line&#10;e.g., +447700900123"></textarea>
                            <small class="text-muted">Only these numbers can receive messages while Test Mode is active.</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <span class="text-muted small me-auto"><i class="fas fa-lock me-1"></i>All changes logged to audit trail</span>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="saveFraudRisk()">Save Risk Controls</button>
            </div>
        </div>
    </div>
</div>

<!-- Enforcement Controls Modal -->
<div class="modal fade" id="enforcementModal" tabindex="-1" aria-labelledby="enforcementModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="enforcementModalLabel">Edit Enforcement Controls</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info small mb-3">
                    <i class="fas fa-info-circle me-2"></i>Changes apply immediately. Notifications will be sent to sub-account and main account admins.
                </div>
                <input type="hidden" id="enforcementSubId">
                <input type="hidden" id="enforcementPrevSpend">
                <input type="hidden" id="enforcementPrevMsg">
                <input type="hidden" id="enforcementPrevType">
                
                <div class="mb-3">
                    <label class="form-label">Sub-Account</label>
                    <input type="text" class="form-control" id="enforcementSubName" readonly>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Monthly Spend Cap (£)</label>
                    <input type="number" class="form-control" id="enforcementSpendCap" min="0" step="100" placeholder="e.g., 5000">
                    <small class="text-muted">Set to 0 for unlimited</small>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Monthly Message Cap</label>
                    <input type="number" class="form-control" id="enforcementMsgCap" min="0" step="1000" placeholder="e.g., 100000">
                    <small class="text-muted">Set to 0 for unlimited</small>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Enforcement Type</label>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="enforcementType" id="enfTypeWarn" value="Warn Only">
                        <label class="form-check-label" for="enfTypeWarn">
                            <strong>Warn Only</strong>
                            <div class="text-muted small">Send notifications when limits are approached, but allow continued sending</div>
                        </label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="enforcementType" id="enfTypeBlock" value="Block Sends">
                        <label class="form-check-label" for="enfTypeBlock">
                            <strong>Block Sends</strong>
                            <div class="text-muted small">Block new messages when limit is reached, existing queued messages proceed</div>
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="enforcementType" id="enfTypeHard" value="Hard Stop">
                        <label class="form-check-label" for="enfTypeHard">
                            <strong>Hard Stop</strong>
                            <div class="text-muted small">Immediately halt all messaging including queued messages</div>
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveEnforcement()">Save & Apply</button>
            </div>
        </div>
    </div>
</div>

<!-- Row Action Confirmation Modal -->
<div class="modal fade" id="rowActionModal" tabindex="-1" aria-labelledby="rowActionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rowActionModalLabel">Confirm Action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="rowActionModalBody">
                <!-- Content populated by JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="rowActionConfirmBtn">Confirm</button>
            </div>
        </div>
    </div>
</div>

@include('admin.accounts.partials.account-structure-modal')

<!-- Billing / Invoices Modal -->
<div class="modal fade" id="billingModal" tabindex="-1" aria-labelledby="billingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="billingModalLabel">Invoices — <span id="billingClientName">Client Name</span></h5>
                <div class="d-flex align-items-center gap-2 ms-auto me-3">
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="openCreateInvoice()">
                        <i class="fas fa-plus me-1"></i>Create Invoice
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="openCreateCreditNote()">
                        <i class="fas fa-minus me-1"></i>Create Credit Note
                    </button>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="billingAccountId">
                
                <!-- Loading State -->
                <div id="billingLoading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Loading invoices...</p>
                </div>
                
                <!-- Invoices Table -->
                <div id="billingContent" style="display: none;">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="invoicesTable">
                            <thead>
                                <tr>
                                    <th>Invoice #</th>
                                    <th>Period</th>
                                    <th>Issue Date</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-end">Balance</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="invoicesTableBody">
                                <!-- Populated by JavaScript -->
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Empty State -->
                    <div id="invoicesEmpty" class="text-center py-5" style="display: none;">
                        <i class="fas fa-file-invoice fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No invoices found for this client</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <span class="text-muted small me-auto"><i class="fas fa-sync-alt me-1"></i>Xero sync ready</span>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Create Invoice Modal -->
<div class="modal fade" id="createInvoiceModal" tabindex="-1" aria-labelledby="createInvoiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createInvoiceModalLabel">Create Invoice</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="createInvoiceForm">
                    <div class="mb-3">
                        <label class="form-label">Client</label>
                        <input type="text" class="form-control" id="invoiceClientDisplay" readonly>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Period Start</label>
                            <input type="date" class="form-control" id="invoicePeriodStart">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Period End</label>
                            <input type="date" class="form-control" id="invoicePeriodEnd">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Due Date</label>
                        <input type="date" class="form-control" id="invoiceDueDate">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" id="invoiceDescription" rows="2" placeholder="Optional invoice description"></textarea>
                    </div>
                    <div class="alert alert-info small mb-0">
                        <i class="fas fa-info-circle me-1"></i>
                        Invoice will be generated based on message usage during the selected period. Amount will be calculated automatically.
                    </div>
                </div>
                <div id="createInvoiceSuccess" style="display: none;">
                    <div class="text-center py-4">
                        <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                        <h5>Invoice Created Successfully</h5>
                        <p class="text-muted mb-3">Invoice <strong id="newInvoiceNumber"></strong> has been created.</p>
                        <p class="small text-muted">This invoice will be synced to Xero when integration is configured.</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="createInvoiceBtn" onclick="submitCreateInvoice()">
                    <i class="fas fa-file-invoice me-1"></i>Create Invoice
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Create Credit Note Modal -->
<div class="modal fade" id="createCreditNoteModal" tabindex="-1" aria-labelledby="createCreditNoteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createCreditNoteModalLabel">Create Credit Note</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="createCreditNoteForm">
                    <div class="mb-3">
                        <label class="form-label">Client</label>
                        <input type="text" class="form-control" id="creditNoteClientDisplay" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Related Invoice (Optional)</label>
                        <select class="form-select" id="creditNoteInvoice">
                            <option value="">— No linked invoice —</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">£</span>
                            <input type="number" class="form-control" id="creditNoteAmount" step="0.01" min="0" placeholder="0.00">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason</label>
                        <select class="form-select" id="creditNoteReason">
                            <option value="goodwill">Goodwill Gesture</option>
                            <option value="billing_error">Billing Error</option>
                            <option value="service_issue">Service Issue</option>
                            <option value="refund">Refund Request</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" id="creditNoteNotes" rows="2" placeholder="Internal notes (required)"></textarea>
                    </div>
                    <div class="alert alert-warning small mb-0">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        Credit notes are logged to the audit trail and cannot be deleted.
                    </div>
                </div>
                <div id="createCreditNoteSuccess" style="display: none;">
                    <div class="text-center py-4">
                        <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                        <h5>Credit Note Created Successfully</h5>
                        <p class="text-muted mb-3">Credit Note <strong id="newCreditNoteNumber"></strong> has been created.</p>
                        <p class="small text-muted">This credit note will be synced to Xero when integration is configured.</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="createCreditNoteBtn" onclick="submitCreateCreditNote()">
                    <i class="fas fa-file-invoice me-1"></i>Create Credit Note
                </button>
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

    // KPI Tiles Collapse on Scroll
    initKpiScrollCollapse();
});

function initKpiScrollCollapse() {
    var kpiTileRow = document.querySelector('.kpi-tile-row');
    var kpiCollapsedStrip = document.getElementById('kpiCollapsedStrip');
    var contentArea = document.querySelector('.content-body') || document.querySelector('.admin-page').parentElement || window;
    
    if (!kpiTileRow || !kpiCollapsedStrip) return;

    var collapseThreshold = 100;
    var isCollapsed = false;

    function handleScroll() {
        var scrollTop = contentArea === window ? window.scrollY : contentArea.scrollTop;
        
        if (scrollTop > collapseThreshold && !isCollapsed) {
            kpiTileRow.classList.add('collapsed');
            kpiCollapsedStrip.classList.add('visible');
            isCollapsed = true;
            syncActiveFilter();
        } else if (scrollTop <= collapseThreshold && isCollapsed) {
            kpiTileRow.classList.remove('collapsed');
            kpiCollapsedStrip.classList.remove('visible');
            isCollapsed = false;
        }
    }

    function syncActiveFilter() {
        var activeTile = kpiTileRow.querySelector('.widget-stat.active');
        var stripItems = kpiCollapsedStrip.querySelectorAll('.kpi-item');
        
        stripItems.forEach(function(item) {
            item.classList.remove('active');
            if (activeTile && item.dataset.filter === activeTile.dataset.filter) {
                item.classList.add('active');
            }
        });
    }

    if (contentArea === window) {
        window.addEventListener('scroll', handleScroll, { passive: true });
    } else {
        contentArea.addEventListener('scroll', handleScroll, { passive: true });
    }
}

// ========================
// Sub-Account Expand/Collapse
// ========================
window.toggleSubAccounts = function(accountId, button) {
    var subAccountRow = document.querySelector('.sub-account-row[data-parent="' + accountId + '"]');
    if (!subAccountRow) return;
    
    var isExpanded = subAccountRow.classList.contains('visible');
    
    if (isExpanded) {
        subAccountRow.classList.remove('visible');
        button.classList.remove('expanded');
        button.innerHTML = '<i class="fas fa-plus"></i>';
        button.title = 'Show sub-accounts';
    } else {
        subAccountRow.classList.add('visible');
        button.classList.add('expanded');
        button.innerHTML = '<i class="fas fa-minus"></i>';
        button.title = 'Hide sub-accounts';
    }
};

// ========================
// Table Sorting
// ========================
var currentSort = { column: null, direction: null };

function initTableSorting() {
    var headers = document.querySelectorAll('#accountsTable thead th.sortable');
    headers.forEach(function(header) {
        header.addEventListener('click', function() {
            var sortKey = this.dataset.sort;
            sortTable(sortKey, this);
        });
    });
}

function sortTable(sortKey, headerElement) {
    var table = document.getElementById('accountsTable');
    var tbody = table.querySelector('tbody');
    var rows = Array.from(tbody.querySelectorAll('tr.account-row'));
    var headers = table.querySelectorAll('thead th.sortable');
    
    // Determine sort direction
    var direction = 'asc';
    if (currentSort.column === sortKey && currentSort.direction === 'asc') {
        direction = 'desc';
    }
    
    // Update header styling
    headers.forEach(function(h) {
        h.classList.remove('sort-asc', 'sort-desc');
    });
    headerElement.classList.add(direction === 'asc' ? 'sort-asc' : 'sort-desc');
    
    // Sort rows
    rows.sort(function(a, b) {
        var aVal, bVal;
        
        switch(sortKey) {
            case 'name':
                aVal = (a.dataset.name || '').toLowerCase();
                bVal = (b.dataset.name || '').toLowerCase();
                break;
            case 'volume-year':
                aVal = parseInt(a.dataset.volumeYear) || 0;
                bVal = parseInt(b.dataset.volumeYear) || 0;
                break;
            case 'volume-month':
                aVal = parseInt(a.dataset.volumeMonth) || 0;
                bVal = parseInt(b.dataset.volumeMonth) || 0;
                break;
            case 'balance':
                aVal = parseInt(a.dataset.balance) || 0;
                bVal = parseInt(b.dataset.balance) || 0;
                break;
            case 'status':
                aVal = (a.dataset.status || '').toLowerCase();
                bVal = (b.dataset.status || '').toLowerCase();
                break;
            default:
                aVal = '';
                bVal = '';
        }
        
        var result = 0;
        if (typeof aVal === 'string') {
            result = aVal.localeCompare(bVal);
        } else {
            result = aVal - bVal;
        }
        
        return direction === 'asc' ? result : -result;
    });
    
    // Re-append sorted rows with their sub-account rows
    rows.forEach(function(row) {
        var accountId = row.dataset.account;
        var subRow = tbody.querySelector('.sub-account-row[data-parent="' + accountId + '"]');
        tbody.appendChild(row);
        if (subRow) {
            tbody.appendChild(subRow);
        }
    });
    
    currentSort = { column: sortKey, direction: direction };
}

// Initialize sorting on page load
document.addEventListener('DOMContentLoaded', function() {
    initTableSorting();
});

var currentFilter = null;

window.filterAccounts = function(filter) {
    var tiles = document.querySelectorAll('.kpi-tile-row .widget-stat');
    var stripItems = document.querySelectorAll('.kpi-collapsed-strip .kpi-item');
    
    // Toggle active state
    if (currentFilter === filter) {
        // Clear filter
        currentFilter = null;
        tiles.forEach(function(tile) {
            tile.classList.remove('active');
        });
        stripItems.forEach(function(item) {
            item.classList.remove('active');
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
        stripItems.forEach(function(item) {
            item.classList.remove('active');
            if (item.dataset.filter === filter) {
                item.classList.add('active');
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
    var enforcementType = sub.enforcementType || 'Warn Only';
    var enforcementClass = enforcementType === 'Hard Stop' ? 'danger' : enforcementType === 'Block Sends' ? 'warning' : 'info';

    return '<h6 class="mb-3">Sub-Account</h6>' +
        '<table class="table table-sm mb-3">' +
        '<tr><th class="text-muted" style="width:45%">Status</th><td><span class="badge light badge-' + (sub.status === 'Active' ? 'success' : 'danger') + '">' + sub.status + '</span></td></tr>' +
        '</table>' +
        '<div class="d-flex justify-content-between align-items-center mb-2">' +
        '<h6 class="text-muted mb-0" style="font-size:0.85rem;">Enforcement Controls</h6>' +
        '<button class="btn btn-outline-primary btn-xs" onclick="editEnforcement(\'' + sub.id + '\', \'' + sub.name + '\', ' + spendCap + ', ' + msgCap + ', \'' + enforcementType + '\')"><i class="fas fa-edit me-1"></i>Edit</button>' +
        '</div>' +
        '<table class="table table-sm mb-3">' +
        '<tr><th class="text-muted" style="width:45%">Monthly Spend Cap</th><td>£' + spendCap.toLocaleString() + '</td></tr>' +
        '<tr><th class="text-muted">Monthly Message Cap</th><td>' + msgCap.toLocaleString() + '</td></tr>' +
        '<tr><th class="text-muted">Enforcement Type</th><td><span class="badge light badge-' + enforcementClass + '">' + enforcementType + '</span></td></tr>' +
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

// Fraud & Risk Controls
var fraudRiskModal = null;

window.openFraudRisk = function(accountId, accountName) {
    if (!fraudRiskModal) {
        fraudRiskModal = new bootstrap.Modal(document.getElementById('fraudRiskModal'));
        
        // Toggle test numbers section visibility
        document.getElementById('enableTestMode').addEventListener('change', function() {
            document.getElementById('testNumbersSection').style.display = this.checked ? 'block' : 'none';
        });
    }

    document.getElementById('fraudRiskModalLabel').textContent = 'Fraud & Risk Controls — ' + accountName;
    document.getElementById('fraudAccountId').value = accountId;
    document.getElementById('fraudAccountName').value = accountName;

    // Reset form (in production, would load current settings)
    document.getElementById('flagSuspicious').checked = (accountId === 'ACC-4567'); // TestCo is flagged
    document.getElementById('restrictedCountries').selectedIndex = -1;
    document.getElementById('allowedSenderIds').value = '';
    document.getElementById('blockDynamicSenderId').checked = false;
    document.getElementById('enableTestMode').checked = false;
    document.getElementById('testNumbers').value = '';
    document.getElementById('testNumbersSection').style.display = 'none';

    if (typeof AdminControlPlane !== 'undefined') {
        AdminControlPlane.logAdminAction('FRAUD_RISK_MODAL_OPENED', 'ACCOUNTS', { 
            accountId: accountId, 
            accountName: accountName,
            accessType: 'admin_only',
            piiAccess: false
        });
    }

    fraudRiskModal.show();
};

window.saveFraudRisk = function() {
    var accountId = document.getElementById('fraudAccountId').value;
    var accountName = document.getElementById('fraudAccountName').value;

    var isSuspicious = document.getElementById('flagSuspicious').checked;
    var restrictedCountries = Array.from(document.getElementById('restrictedCountries').selectedOptions).map(o => o.value);
    var allowedSenderIds = document.getElementById('allowedSenderIds').value.split(',').map(s => s.trim()).filter(s => s);
    var blockDynamic = document.getElementById('blockDynamicSenderId').checked;
    var testMode = document.getElementById('enableTestMode').checked;
    var testNumbers = testMode ? document.getElementById('testNumbers').value.split('\n').map(n => n.trim()).filter(n => n) : [];

    var changes = {
        accountId: accountId,
        accountName: accountName,
        flaggedSuspicious: isSuspicious,
        restrictedCountries: restrictedCountries,
        allowedSenderIds: allowedSenderIds,
        blockDynamicSenderId: blockDynamic,
        testModeEnabled: testMode,
        testNumbers: testNumbers.length
    };

    // Log to ADMIN audit only (not customer-visible)
    if (typeof AdminControlPlane !== 'undefined') {
        AdminControlPlane.logAdminAction('FRAUD_RISK_CONTROLS_UPDATED', 'ACCOUNTS', {
            ...changes,
            auditScope: 'admin_only',
            customerVisible: false
        });
    }

    var summary = [];
    if (isSuspicious) summary.push('Account flagged as Suspicious');
    if (restrictedCountries.length) summary.push('Restricted countries: ' + restrictedCountries.join(', '));
    if (allowedSenderIds.length) summary.push('Allowed Sender IDs: ' + allowedSenderIds.join(', '));
    if (blockDynamic) summary.push('Dynamic Sender ID blocked');
    if (testMode) summary.push('Test Mode enabled with ' + testNumbers.length + ' approved numbers');

    alert('Fraud & Risk controls saved for ' + accountName + '.\n\n' + (summary.length ? summary.join('\n') : 'All restrictions cleared.'));
    fraudRiskModal.hide();
};

var enforcementModal = null;

window.editEnforcement = function(subId, subName, spendCap, msgCap, enfType) {
    if (!enforcementModal) {
        enforcementModal = new bootstrap.Modal(document.getElementById('enforcementModal'));
    }

    document.getElementById('enforcementModalLabel').textContent = 'Edit Enforcement Controls — ' + subName;
    document.getElementById('enforcementSubId').value = subId;
    document.getElementById('enforcementSubName').value = subName;
    document.getElementById('enforcementSpendCap').value = spendCap;
    document.getElementById('enforcementMsgCap').value = msgCap;
    document.getElementById('enforcementPrevSpend').value = spendCap;
    document.getElementById('enforcementPrevMsg').value = msgCap;
    document.getElementById('enforcementPrevType').value = enfType;

    // Set the correct radio button
    document.getElementById('enfTypeWarn').checked = (enfType === 'Warn Only');
    document.getElementById('enfTypeBlock').checked = (enfType === 'Block Sends');
    document.getElementById('enfTypeHard').checked = (enfType === 'Hard Stop');

    if (typeof AdminControlPlane !== 'undefined') {
        AdminControlPlane.logAdminAction('ENFORCEMENT_EDIT_OPENED', 'ACCOUNTS', { subAccountId: subId, subAccountName: subName });
    }

    enforcementModal.show();
};

window.saveEnforcement = function() {
    var subId = document.getElementById('enforcementSubId').value;
    var subName = document.getElementById('enforcementSubName').value;
    var newSpendCap = parseInt(document.getElementById('enforcementSpendCap').value) || 0;
    var newMsgCap = parseInt(document.getElementById('enforcementMsgCap').value) || 0;
    var newEnfType = document.querySelector('input[name="enforcementType"]:checked')?.value;

    var prevSpendCap = parseInt(document.getElementById('enforcementPrevSpend').value) || 0;
    var prevMsgCap = parseInt(document.getElementById('enforcementPrevMsg').value) || 0;
    var prevEnfType = document.getElementById('enforcementPrevType').value;

    if (!newEnfType) {
        alert('Please select an enforcement type.');
        return;
    }

    // Build change summary for audit
    var changes = [];
    if (newSpendCap !== prevSpendCap) {
        changes.push('Spend Cap: £' + prevSpendCap.toLocaleString() + ' → £' + newSpendCap.toLocaleString());
    }
    if (newMsgCap !== prevMsgCap) {
        changes.push('Message Cap: ' + prevMsgCap.toLocaleString() + ' → ' + newMsgCap.toLocaleString());
    }
    if (newEnfType !== prevEnfType) {
        changes.push('Enforcement Type: ' + prevEnfType + ' → ' + newEnfType);
    }

    if (changes.length === 0) {
        alert('No changes were made.');
        enforcementModal.hide();
        return;
    }

    // Log to audit trail with before/after
    if (typeof AdminControlPlane !== 'undefined') {
        AdminControlPlane.logAdminAction('ENFORCEMENT_CONTROLS_UPDATED', 'ACCOUNTS', { 
            subAccountId: subId,
            subAccountName: subName,
            previous: {
                spendCap: prevSpendCap,
                msgCap: prevMsgCap,
                enforcementType: prevEnfType
            },
            new: {
                spendCap: newSpendCap,
                msgCap: newMsgCap,
                enforcementType: newEnfType
            },
            changesSummary: changes,
            notificationsSent: ['sub_account_admins', 'main_account_admins'],
            appliedImmediately: true
        });
    }

    alert('Enforcement controls updated for ' + subName + '.\n\nChanges:\n• ' + changes.join('\n• ') + '\n\nNotifications sent to sub-account and main account admins.');
    enforcementModal.hide();
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

var rowActionModal = null;
var pendingRowAction = null;

window.rowAction = function(accountId, accountName, action) {
    if (!rowActionModal) {
        rowActionModal = new bootstrap.Modal(document.getElementById('rowActionModal'));
    }

    pendingRowAction = { accountId: accountId, accountName: accountName, action: action };

    var actionConfig = {
        add_credit: {
            title: 'Add Credit',
            body: '<p>Add credit to <strong>' + accountName + '</strong></p>' +
                  '<div class="mb-3"><label class="form-label">Credit Amount (£)</label>' +
                  '<input type="number" class="form-control" id="creditAmount" placeholder="Enter amount" min="1" step="0.01"></div>' +
                  '<div class="mb-3"><label class="form-label">Reason</label>' +
                  '<input type="text" class="form-control" id="creditReason" placeholder="e.g., Manual top-up, Goodwill credit"></div>',
            btnClass: 'btn-success',
            btnText: 'Add Credit',
            auditEvent: 'ACCOUNT_CREDIT_ADDED'
        },
        change_name: {
            title: 'Change Account Name',
            body: '<p>Change name for <strong>' + accountName + '</strong> (' + accountId + ')</p>' +
                  '<div class="mb-3"><label class="form-label">New Account Name</label>' +
                  '<input type="text" class="form-control" id="newAccountName" value="' + accountName + '"></div>',
            btnClass: 'btn-primary',
            btnText: 'Save Changes',
            auditEvent: 'ACCOUNT_NAME_CHANGED'
        },
        view_pricing: {
            title: 'Pricing Model',
            body: '<p>Pricing configuration for <strong>' + accountName + '</strong></p>' +
                  '<div class="alert alert-info small mb-3"><i class="fas fa-info-circle me-2"></i>Pricing data synced from HubSpot. Changes here update HubSpot records.</div>' +
                  '<table class="table table-sm mb-3">' +
                  '<tr><th class="text-muted" style="width:45%">Current Model</th><td><span class="badge light badge-primary" id="currentPricingBadge">Delivered</span></td></tr>' +
                  '<tr><th class="text-muted">UK SMS Rate</th><td>£0.032</td></tr>' +
                  '<tr><th class="text-muted">International Rate</th><td>From £0.045</td></tr>' +
                  '<tr><th class="text-muted">RCS Rate</th><td>£0.015</td></tr>' +
                  '</table>' +
                  '<hr>' +
                  '<h6 class="mb-2">Change Pricing Model</h6>' +
                  '<div class="mb-3">' +
                  '<div class="form-check mb-2">' +
                  '<input class="form-check-input" type="radio" name="pricingModel" id="pricingSubmitted" value="Submitted">' +
                  '<label class="form-check-label" for="pricingSubmitted"><strong>Submitted</strong> - Charge for all messages sent</label>' +
                  '</div>' +
                  '<div class="form-check">' +
                  '<input class="form-check-input" type="radio" name="pricingModel" id="pricingDelivered" value="Delivered" checked>' +
                  '<label class="form-check-label" for="pricingDelivered"><strong>Delivered</strong> - Charge only for successfully delivered messages</label>' +
                  '</div>' +
                  '</div>' +
                  '<div class="alert alert-secondary small"><i class="fas fa-lock me-2"></i>Only Admin and Super Admin roles can modify pricing models.</div>',
            btnClass: 'btn-primary',
            btnText: 'Save Pricing Model',
            isViewOnly: false,
            auditEvent: 'ACCOUNT_PRICING_CHANGED'
        },
        suspend: {
            title: 'Suspend Account',
            body: '<div class="alert alert-warning mb-3"><i class="fas fa-exclamation-triangle me-2"></i>This will immediately stop all messaging for this account.</div>' +
                  '<p>You are about to suspend <strong>' + accountName + '</strong> (' + accountId + ')</p>' +
                  '<div class="mb-3"><label class="form-label">Suspension Reason <span class="text-danger">*</span></label>' +
                  '<select class="form-select" id="suspendReason">' +
                  '<option value="">Select reason...</option>' +
                  '<option value="payment">Payment Issues</option>' +
                  '<option value="abuse">Policy Violation / Abuse</option>' +
                  '<option value="fraud">Fraud Investigation</option>' +
                  '<option value="request">Customer Request</option>' +
                  '<option value="other">Other</option>' +
                  '</select></div>',
            btnClass: 'btn-warning',
            btnText: 'Suspend Account',
            auditEvent: 'ACCOUNT_SUSPENDED'
        },
        reactivate: {
            title: 'Reactivate Account',
            body: '<div class="alert alert-info mb-3"><i class="fas fa-info-circle me-2"></i>This will restore messaging capabilities for this account.</div>' +
                  '<p>You are about to reactivate <strong>' + accountName + '</strong> (' + accountId + ')</p>' +
                  '<div class="mb-3"><label class="form-label">Reactivation Notes</label>' +
                  '<input type="text" class="form-control" id="reactivateNotes" placeholder="Optional notes"></div>',
            btnClass: 'btn-success',
            btnText: 'Reactivate Account',
            auditEvent: 'ACCOUNT_REACTIVATED'
        }
    };

    var config = actionConfig[action];
    if (!config) return;

    document.getElementById('rowActionModalLabel').textContent = config.title;
    document.getElementById('rowActionModalBody').innerHTML = config.body;

    var confirmBtn = document.getElementById('rowActionConfirmBtn');
    confirmBtn.className = 'btn ' + config.btnClass;
    confirmBtn.textContent = config.btnText;

    if (config.isViewOnly) {
        confirmBtn.onclick = function() { rowActionModal.hide(); };
    } else {
        confirmBtn.onclick = function() { executeRowAction(config.auditEvent); };
    }

    // Log that the action modal was opened
    if (typeof AdminControlPlane !== 'undefined') {
        AdminControlPlane.logAdminAction(config.auditEvent + '_INITIATED', 'ACCOUNTS', { 
            accountId: accountId, 
            accountName: accountName 
        });
    }

    rowActionModal.show();
};

function executeRowAction(auditEvent) {
    var action = pendingRowAction;
    if (!action) return;

    var additionalData = {};

    // Collect form data based on action type
    if (action.action === 'add_credit') {
        var amount = document.getElementById('creditAmount')?.value;
        var reason = document.getElementById('creditReason')?.value;
        if (!amount || parseFloat(amount) <= 0) {
            alert('Please enter a valid credit amount.');
            return;
        }
        additionalData = { amount: amount, reason: reason };
    } else if (action.action === 'change_name') {
        var newName = document.getElementById('newAccountName')?.value;
        if (!newName || newName.trim() === '') {
            alert('Please enter a valid account name.');
            return;
        }
        additionalData = { oldName: action.accountName, newName: newName };
    } else if (action.action === 'view_pricing') {
        var selectedModel = document.querySelector('input[name="pricingModel"]:checked')?.value;
        var currentModel = document.getElementById('currentPricingBadge')?.textContent || 'Delivered';
        if (selectedModel === currentModel) {
            alert('No changes made to pricing model.');
            rowActionModal.hide();
            return;
        }
        additionalData = { 
            previousModel: currentModel, 
            newModel: selectedModel,
            hubspotSyncRequired: true
        };
    } else if (action.action === 'suspend') {
        var reason = document.getElementById('suspendReason')?.value;
        if (!reason) {
            alert('Please select a suspension reason.');
            return;
        }
        additionalData = { reason: reason };
    } else if (action.action === 'reactivate') {
        var notes = document.getElementById('reactivateNotes')?.value;
        additionalData = { notes: notes };
    }

    // Log the action
    if (typeof AdminControlPlane !== 'undefined') {
        AdminControlPlane.logAdminAction(auditEvent, 'ACCOUNTS', { 
            accountId: action.accountId, 
            accountName: action.accountName,
            ...additionalData
        });
    }

    // Show confirmation
    alert('Action completed: ' + auditEvent + ' for ' + action.accountName);
    rowActionModal.hide();
    pendingRowAction = null;
}

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

// ========================
// Billing Service Layer (Xero-Ready)
// ========================
var BillingService = (function() {
    // Mock invoice data - will be replaced with real API calls
    var mockInvoices = {
        'ACC-1234': [
            { invoiceNumber: 'INV-2025-0142', period: 'Dec 2024', issueDate: '2025-01-01', dueDate: '2025-01-15', status: 'paid', total: 2450.00, balance: 0.00 },
            { invoiceNumber: 'INV-2025-0098', period: 'Nov 2024', issueDate: '2024-12-01', dueDate: '2024-12-15', status: 'paid', total: 2180.50, balance: 0.00 },
            { invoiceNumber: 'INV-2025-0201', period: 'Jan 2025', issueDate: '2025-01-20', dueDate: '2025-02-03', status: 'outstanding', total: 2890.00, balance: 2890.00 }
        ],
        'ACC-5678': [
            { invoiceNumber: 'INV-2025-0156', period: 'Dec 2024', issueDate: '2025-01-01', dueDate: '2025-01-15', status: 'overdue', total: 3200.00, balance: 3200.00 },
            { invoiceNumber: 'INV-2025-0089', period: 'Nov 2024', issueDate: '2024-12-01', dueDate: '2024-12-15', status: 'paid', total: 2890.00, balance: 0.00 }
        ],
        'ACC-7890': [],
        'ACC-4567': [
            { invoiceNumber: 'INV-2024-0892', period: 'Oct 2024', issueDate: '2024-11-01', dueDate: '2024-11-15', status: 'overdue', total: 450.00, balance: 240.00 }
        ]
    };

    // Generate mock invoices for other accounts
    function generateMockInvoices(accountId) {
        if (mockInvoices[accountId]) return mockInvoices[accountId];
        
        var invoices = [];
        var numInvoices = Math.floor(Math.random() * 5) + 1;
        for (var i = 0; i < numInvoices; i++) {
            var month = 12 - i;
            var year = month < 1 ? 2024 : 2025;
            month = month < 1 ? 12 + month : month;
            var statuses = ['paid', 'paid', 'paid', 'outstanding', 'overdue'];
            var status = statuses[Math.floor(Math.random() * statuses.length)];
            var total = Math.floor(Math.random() * 5000) + 500;
            var balance = status === 'paid' ? 0 : (status === 'overdue' ? total * 0.5 : total);
            
            invoices.push({
                invoiceNumber: 'INV-' + year + '-' + String(Math.floor(Math.random() * 900) + 100).padStart(4, '0'),
                period: new Date(year, month - 1).toLocaleDateString('en-GB', { month: 'short', year: 'numeric' }),
                issueDate: year + '-' + String(month).padStart(2, '0') + '-01',
                dueDate: year + '-' + String(month).padStart(2, '0') + '-15',
                status: status,
                total: total,
                balance: balance
            });
        }
        mockInvoices[accountId] = invoices;
        return invoices;
    }

    return {
        // TODO: Replace with real API endpoint when Xero integration is ready
        // @param accountId - Client account ID
        // @returns Promise<Invoice[]>
        listClientInvoices: function(accountId) {
            return new Promise(function(resolve, reject) {
                // Simulate API delay
                setTimeout(function() {
                    var invoices = generateMockInvoices(accountId);
                    resolve({ success: true, data: invoices });
                }, 500);
            });
        },

        // TODO: Replace with real API endpoint when Xero integration is ready
        // @param accountId - Client account ID
        // @param payload - { periodStart, periodEnd, dueDate, description }
        // @returns Promise<{ invoiceNumber, ... }>
        createInvoice: function(accountId, payload) {
            return new Promise(function(resolve, reject) {
                setTimeout(function() {
                    var newInvoiceNumber = 'INV-2025-' + String(Math.floor(Math.random() * 9000) + 1000);
                    resolve({ 
                        success: true, 
                        data: { 
                            invoiceNumber: newInvoiceNumber,
                            accountId: accountId,
                            ...payload
                        }
                    });
                }, 800);
            });
        },

        // TODO: Replace with real API endpoint when Xero integration is ready
        // @param accountId - Client account ID
        // @param payload - { invoiceId, amount, reason, notes }
        // @returns Promise<{ creditNoteNumber, ... }>
        createCreditNote: function(accountId, payload) {
            return new Promise(function(resolve, reject) {
                setTimeout(function() {
                    var newCreditNoteNumber = 'CN-2025-' + String(Math.floor(Math.random() * 9000) + 1000);
                    resolve({ 
                        success: true, 
                        data: { 
                            creditNoteNumber: newCreditNoteNumber,
                            accountId: accountId,
                            ...payload
                        }
                    });
                }, 800);
            });
        }
    };
})();

// ========================
// Billing UI Functions
// ========================
var currentBillingAccountId = null;
var currentBillingClientName = null;
var billingModal = null;
var createInvoiceModal = null;
var createCreditNoteModal = null;

document.addEventListener('DOMContentLoaded', function() {
    billingModal = new bootstrap.Modal(document.getElementById('billingModal'));
    createInvoiceModal = new bootstrap.Modal(document.getElementById('createInvoiceModal'));
    createCreditNoteModal = new bootstrap.Modal(document.getElementById('createCreditNoteModal'));
});

window.openBillingModal = function(accountId, clientName) {
    currentBillingAccountId = accountId;
    currentBillingClientName = clientName;
    
    document.getElementById('billingAccountId').value = accountId;
    document.getElementById('billingClientName').textContent = clientName;
    
    // Show loading, hide content
    document.getElementById('billingLoading').style.display = 'block';
    document.getElementById('billingContent').style.display = 'none';
    
    billingModal.show();
    
    // Fetch invoices
    BillingService.listClientInvoices(accountId).then(function(response) {
        document.getElementById('billingLoading').style.display = 'none';
        document.getElementById('billingContent').style.display = 'block';
        
        renderInvoicesTable(response.data);
        
        // Audit log
        if (typeof AdminControlPlane !== 'undefined') {
            AdminControlPlane.logAdminAction('BILLING_MODAL_OPENED', 'BILLING', {
                accountId: accountId,
                clientName: clientName,
                invoiceCount: response.data.length
            });
        }
    }).catch(function(error) {
        document.getElementById('billingLoading').innerHTML = '<div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i>Failed to load invoices. Please try again.</div>';
    });
};

function renderInvoicesTable(invoices) {
    var tbody = document.getElementById('invoicesTableBody');
    var emptyState = document.getElementById('invoicesEmpty');
    
    if (invoices.length === 0) {
        tbody.innerHTML = '';
        emptyState.style.display = 'block';
        return;
    }
    
    emptyState.style.display = 'none';
    
    var html = '';
    invoices.forEach(function(inv) {
        var statusClass = getStatusBadgeClass(inv.status);
        var balanceClass = inv.balance > 0 ? 'text-danger' : '';
        
        html += '<tr>';
        html += '<td><strong>' + inv.invoiceNumber + '</strong></td>';
        html += '<td>' + inv.period + '</td>';
        html += '<td>' + formatDate(inv.issueDate) + '</td>';
        html += '<td>' + formatDate(inv.dueDate) + '</td>';
        html += '<td><span class="badge light ' + statusClass + '">' + capitalizeFirst(inv.status) + '</span></td>';
        html += '<td class="text-end">£' + formatNumber(inv.total) + '</td>';
        html += '<td class="text-end ' + balanceClass + '">£' + formatNumber(inv.balance) + '</td>';
        html += '<td class="text-center">';
        html += '<button class="btn btn-sm btn-outline-secondary me-1" onclick="viewInvoice(\'' + inv.invoiceNumber + '\')" title="View"><i class="fas fa-eye"></i></button>';
        html += '<button class="btn btn-sm btn-outline-secondary" onclick="downloadInvoice(\'' + inv.invoiceNumber + '\')" title="Download"><i class="fas fa-download"></i></button>';
        html += '</td>';
        html += '</tr>';
    });
    
    tbody.innerHTML = html;
}

function getStatusBadgeClass(status) {
    switch (status) {
        case 'paid': return 'badge-success';
        case 'outstanding': return 'badge-warning';
        case 'overdue': return 'badge-danger';
        case 'draft': return 'badge-secondary';
        case 'voided': return 'badge-dark';
        default: return 'badge-info';
    }
}

function formatDate(dateStr) {
    var date = new Date(dateStr);
    return date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
}

function formatNumber(num) {
    return num.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

function capitalizeFirst(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}

window.viewInvoice = function(invoiceNumber) {
    // TODO: Open invoice detail view or PDF
    alert('View invoice: ' + invoiceNumber + '\n\nThis will open the invoice detail when Xero integration is configured.');
};

window.downloadInvoice = function(invoiceNumber) {
    // TODO: Download invoice PDF from Xero
    alert('Download invoice: ' + invoiceNumber + '\n\nThis will download the PDF when Xero integration is configured.');
};

window.openCreateInvoice = function() {
    document.getElementById('invoiceClientDisplay').value = currentBillingClientName;
    document.getElementById('createInvoiceForm').style.display = 'block';
    document.getElementById('createInvoiceSuccess').style.display = 'none';
    document.getElementById('createInvoiceBtn').disabled = false;
    
    // Set default dates
    var today = new Date();
    var firstOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
    var lastOfMonth = new Date(today.getFullYear(), today.getMonth() + 1, 0);
    var dueDate = new Date(today.getTime() + 14 * 24 * 60 * 60 * 1000);
    
    document.getElementById('invoicePeriodStart').value = formatDateForInput(firstOfMonth);
    document.getElementById('invoicePeriodEnd').value = formatDateForInput(lastOfMonth);
    document.getElementById('invoiceDueDate').value = formatDateForInput(dueDate);
    document.getElementById('invoiceDescription').value = '';
    
    createInvoiceModal.show();
};

function formatDateForInput(date) {
    return date.toISOString().split('T')[0];
}

window.submitCreateInvoice = function() {
    var btn = document.getElementById('createInvoiceBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Creating...';
    
    var payload = {
        periodStart: document.getElementById('invoicePeriodStart').value,
        periodEnd: document.getElementById('invoicePeriodEnd').value,
        dueDate: document.getElementById('invoiceDueDate').value,
        description: document.getElementById('invoiceDescription').value
    };
    
    BillingService.createInvoice(currentBillingAccountId, payload).then(function(response) {
        document.getElementById('createInvoiceForm').style.display = 'none';
        document.getElementById('createInvoiceSuccess').style.display = 'block';
        document.getElementById('newInvoiceNumber').textContent = response.data.invoiceNumber;
        
        btn.innerHTML = '<i class="fas fa-file-invoice me-1"></i>Create Invoice';
        
        // Audit log
        if (typeof AdminControlPlane !== 'undefined') {
            AdminControlPlane.logAdminAction('INVOICE_CREATED', 'BILLING', {
                accountId: currentBillingAccountId,
                clientName: currentBillingClientName,
                invoiceNumber: response.data.invoiceNumber,
                ...payload
            });
        }
        
        // Refresh invoices table after short delay
        setTimeout(function() {
            BillingService.listClientInvoices(currentBillingAccountId).then(function(res) {
                renderInvoicesTable(res.data);
            });
        }, 1500);
        
    }).catch(function(error) {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-file-invoice me-1"></i>Create Invoice';
        alert('Failed to create invoice. Please try again.');
    });
};

window.openCreateCreditNote = function() {
    document.getElementById('creditNoteClientDisplay').value = currentBillingClientName;
    document.getElementById('createCreditNoteForm').style.display = 'block';
    document.getElementById('createCreditNoteSuccess').style.display = 'none';
    document.getElementById('createCreditNoteBtn').disabled = false;
    
    // Populate invoice dropdown
    var select = document.getElementById('creditNoteInvoice');
    select.innerHTML = '<option value="">— No linked invoice —</option>';
    
    BillingService.listClientInvoices(currentBillingAccountId).then(function(response) {
        response.data.forEach(function(inv) {
            if (inv.status !== 'voided') {
                var option = document.createElement('option');
                option.value = inv.invoiceNumber;
                option.textContent = inv.invoiceNumber + ' - £' + formatNumber(inv.total) + ' (' + inv.status + ')';
                select.appendChild(option);
            }
        });
    });
    
    document.getElementById('creditNoteAmount').value = '';
    document.getElementById('creditNoteReason').value = 'goodwill';
    document.getElementById('creditNoteNotes').value = '';
    
    createCreditNoteModal.show();
};

window.submitCreateCreditNote = function() {
    var notes = document.getElementById('creditNoteNotes').value.trim();
    var amount = parseFloat(document.getElementById('creditNoteAmount').value);
    
    if (!notes) {
        alert('Please enter notes explaining the credit note reason.');
        return;
    }
    
    if (isNaN(amount) || amount <= 0) {
        alert('Please enter a valid amount greater than 0.');
        return;
    }
    
    var btn = document.getElementById('createCreditNoteBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Creating...';
    
    var payload = {
        invoiceId: document.getElementById('creditNoteInvoice').value,
        amount: amount,
        reason: document.getElementById('creditNoteReason').value,
        notes: notes
    };
    
    BillingService.createCreditNote(currentBillingAccountId, payload).then(function(response) {
        document.getElementById('createCreditNoteForm').style.display = 'none';
        document.getElementById('createCreditNoteSuccess').style.display = 'block';
        document.getElementById('newCreditNoteNumber').textContent = response.data.creditNoteNumber;
        
        btn.innerHTML = '<i class="fas fa-file-invoice me-1"></i>Create Credit Note';
        
        // Audit log
        if (typeof AdminControlPlane !== 'undefined') {
            AdminControlPlane.logAdminAction('CREDIT_NOTE_CREATED', 'BILLING', {
                accountId: currentBillingAccountId,
                clientName: currentBillingClientName,
                creditNoteNumber: response.data.creditNoteNumber,
                ...payload
            });
        }
        
    }).catch(function(error) {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-file-invoice me-1"></i>Create Credit Note';
        alert('Failed to create credit note. Please try again.');
    });
};
</script>
@endpush
