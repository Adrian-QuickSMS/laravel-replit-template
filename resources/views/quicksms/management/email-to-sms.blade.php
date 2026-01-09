@extends('layouts.quicksms')

@section('title', 'Email-to-SMS')

@push('styles')
<style>
.email-sms-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}
.email-sms-header h2 {
    margin: 0;
    font-weight: 600;
}
.email-sms-header p {
    margin: 0;
    color: #6c757d;
}
.filter-chip {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.5rem;
    background-color: #e9ecef;
    border-radius: 1rem;
    font-size: 0.75rem;
    margin-right: 0.5rem;
    margin-bottom: 0.5rem;
}
.filter-chip .remove-chip {
    margin-left: 0.5rem;
    cursor: pointer;
    opacity: 0.7;
}
.filter-chip .remove-chip:hover {
    opacity: 1;
}
.email-address-display {
    font-family: monospace;
    background: #f8f9fa;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.85rem;
}
.email-sms-table {
    width: 100%;
    margin: 0;
}
.email-sms-table th {
    font-weight: 600;
    background: #f8f9fa;
    white-space: nowrap;
}
.email-sms-table td {
    vertical-align: middle;
}
.email-sms-table tbody tr {
    cursor: pointer;
}
.email-sms-table tbody tr:hover {
    background-color: rgba(136, 108, 192, 0.05);
}
.table-container {
    background: #fff;
    border-radius: 0.75rem;
    border: 1px solid #e9ecef;
    overflow: hidden;
}
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
}
.empty-state-icon {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: rgba(136, 108, 192, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
}
.empty-state-icon i {
    font-size: 2rem;
    color: var(--primary);
}
.empty-state h4 {
    margin-bottom: 0.5rem;
    color: #343a40;
}
.empty-state p {
    color: #6c757d;
    margin-bottom: 1.5rem;
    max-width: 400px;
    margin-left: auto;
    margin-right: auto;
}
.multiselect-dropdown .dropdown-menu {
    max-height: 200px;
    overflow-y: auto;
    min-width: 100%;
}
.multiselect-dropdown .form-check {
    padding: 0.5rem 1rem 0.5rem 2.5rem;
}
.multiselect-dropdown .form-check:hover {
    background: #f8f9fa;
}
.drawer {
    position: fixed;
    top: 0;
    right: -500px;
    width: 500px;
    max-width: 100vw;
    height: 100vh;
    background: #fff;
    box-shadow: -5px 0 25px rgba(0,0,0,0.1);
    z-index: 1050;
    transition: right 0.3s ease;
    display: flex;
    flex-direction: column;
}
.drawer.open {
    right: 0;
}
.drawer-header {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #fff;
}
.drawer-header h5 {
    margin: 0;
    font-weight: 600;
    color: #343a40;
}
.drawer-body {
    flex: 1;
    overflow-y: auto;
    padding: 1.5rem;
}
.drawer-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid #e9ecef;
    background: #f8f9fa;
}
.drawer-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(0,0,0,0.5);
    z-index: 1040;
    display: none;
}
.drawer-backdrop.show {
    display: block;
}
.detail-row {
    display: flex;
    justify-content: space-between;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f0f0f0;
}
.detail-row:last-child {
    border-bottom: none;
}
.detail-label {
    font-weight: 500;
    color: #6c757d;
}
.detail-value {
    text-align: right;
}
.detail-value code {
    background: #f8f9fa;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.85rem;
}
.copy-btn {
    background: none;
    border: none;
    color: #6c757d;
    cursor: pointer;
    padding: 0.25rem;
    margin-left: 0.5rem;
}
.copy-btn:hover {
    color: var(--primary);
}
.reporting-group-card {
    border: 1px solid #e9ecef;
    border-radius: 0.5rem;
    padding: 1rem;
    margin-bottom: 1rem;
    transition: all 0.2s ease;
}
.reporting-group-card:hover {
    border-color: #886CC0;
    box-shadow: 0 2px 8px rgba(136, 108, 192, 0.1);
}
.btn-xs {
    padding: 0.2rem 0.5rem;
    font-size: 0.7rem;
    line-height: 1.4;
}
.date-preset-btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
    border: 1px solid #dee2e6;
    background: #fff;
    border-radius: 0.25rem;
    cursor: pointer;
    transition: all 0.15s ease;
}
.date-preset-btn:hover {
    background: #f8f9fa;
    border-color: #886CC0;
}
.date-preset-btn.active {
    background: #886CC0;
    color: #fff;
    border-color: #886CC0;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row page-titles">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('management') }}">Management</a></li>
            <li class="breadcrumb-item active">Email-to-SMS</li>
        </ol>
    </div>
    
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                    <div>
                        <h4 class="card-title mb-1">Email-to-SMS</h4>
                        <p class="mb-0 text-muted small">Configure email addresses to trigger SMS messages to your Contact Lists.</p>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#filtersPanel">
                            <i class="fas fa-filter me-1"></i> Filters
                        </button>
                        <button type="button" class="btn btn-primary" id="btnCreateAddress">
                            <i class="fas fa-plus me-1"></i> Create Address
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <ul class="nav nav-tabs" id="emailSmsTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="addresses-tab" data-bs-toggle="tab" data-bs-target="#addresses" type="button" role="tab">
                                <i class="fas fa-at me-1"></i> Email-to-SMS Addresses
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="reporting-groups-tab" data-bs-toggle="tab" data-bs-target="#reporting-groups" type="button" role="tab">
                                <i class="fas fa-layer-group me-1"></i> Reporting Groups
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="configuration-tab" data-bs-toggle="tab" data-bs-target="#configuration" type="button" role="tab">
                                <i class="fas fa-cog me-1"></i> Configuration
                            </button>
                        </li>
                    </ul>
                    
                    <div class="tab-content pt-3" id="emailSmsTabContent">
                        <div class="tab-pane fade show active" id="addresses" role="tabpanel">
                            <div class="collapse mb-3" id="filtersPanel">
                                <div class="card card-body border-0 rounded-3" style="background-color: #f0ebf8;">
                                    <div class="row g-3 align-items-start">
                                        <div class="col-12 col-lg-6">
                                            <label class="form-label small fw-bold">Date Created</label>
                                            <div class="d-flex gap-2 align-items-center">
                                                <input type="date" class="form-control form-control-sm" id="filterDateFrom">
                                                <span class="text-muted small">to</span>
                                                <input type="date" class="form-control form-control-sm" id="filterDateTo">
                                            </div>
                                            <div class="d-flex flex-wrap gap-1 mt-2">
                                                <button type="button" class="btn btn-outline-primary btn-xs date-preset-btn" data-preset="7days">Last 7 Days</button>
                                                <button type="button" class="btn btn-outline-primary btn-xs date-preset-btn" data-preset="30days">Last 30 Days</button>
                                                <button type="button" class="btn btn-outline-primary btn-xs date-preset-btn" data-preset="thismonth">This Month</button>
                                                <button type="button" class="btn btn-outline-primary btn-xs date-preset-btn" data-preset="lastmonth">Last Month</button>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-4 col-lg-2">
                                            <label class="form-label small fw-bold">Sub Account</label>
                                            <div class="dropdown multiselect-dropdown" data-filter="subAccounts">
                                                <button class="btn btn-sm dropdown-toggle w-100 text-start d-flex justify-content-between align-items-center" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" style="background-color: #fff; border: 1px solid #ced4da; color: #495057;">
                                                    <span class="dropdown-label">All Sub Accounts</span>
                                                </button>
                                                <div class="dropdown-menu w-100 p-2">
                                                    <div class="d-flex justify-content-between mb-2 border-bottom pb-2">
                                                        <a href="#" class="small text-decoration-none select-all-btn">Select All</a>
                                                        <a href="#" class="small text-decoration-none clear-all-btn">Clear</a>
                                                    </div>
                                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="Main Account" id="subAcc1"><label class="form-check-label small" for="subAcc1">Main Account</label></div>
                                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="Marketing Team" id="subAcc2"><label class="form-check-label small" for="subAcc2">Marketing Team</label></div>
                                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="Support Team" id="subAcc3"><label class="form-check-label small" for="subAcc3">Support Team</label></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-4 col-lg-2">
                                            <label class="form-label small fw-bold">Status</label>
                                            <div class="dropdown multiselect-dropdown" data-filter="statuses">
                                                <button class="btn btn-sm dropdown-toggle w-100 text-start d-flex justify-content-between align-items-center" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" style="background-color: #fff; border: 1px solid #ced4da; color: #495057;">
                                                    <span class="dropdown-label">All Statuses</span>
                                                </button>
                                                <div class="dropdown-menu w-100 p-2">
                                                    <div class="d-flex justify-content-between mb-2 border-bottom pb-2">
                                                        <a href="#" class="small text-decoration-none select-all-btn">Select All</a>
                                                        <a href="#" class="small text-decoration-none clear-all-btn">Clear</a>
                                                    </div>
                                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="Active" id="statusActive"><label class="form-check-label small" for="statusActive">Active</label></div>
                                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="Suspended" id="statusSuspended"><label class="form-check-label small" for="statusSuspended">Suspended</label></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-4 col-lg-2">
                                            <label class="form-label small fw-bold">Reporting Group</label>
                                            <div class="dropdown multiselect-dropdown" data-filter="reportingGroups">
                                                <button class="btn btn-sm dropdown-toggle w-100 text-start d-flex justify-content-between align-items-center" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" style="background-color: #fff; border: 1px solid #ced4da; color: #495057;">
                                                    <span class="dropdown-label">All Groups</span>
                                                </button>
                                                <div class="dropdown-menu w-100 p-2">
                                                    <div class="d-flex justify-content-between mb-2 border-bottom pb-2">
                                                        <a href="#" class="small text-decoration-none select-all-btn">Select All</a>
                                                        <a href="#" class="small text-decoration-none clear-all-btn">Clear</a>
                                                    </div>
                                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="Default" id="rgDefault"><label class="form-check-label small" for="rgDefault">Default</label></div>
                                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="Appointments" id="rgAppointments"><label class="form-check-label small" for="rgAppointments">Appointments</label></div>
                                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="Reminders" id="rgReminders"><label class="form-check-label small" for="rgReminders">Reminders</label></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row g-3 align-items-end mt-2">
                                        <div class="col-6 col-md-4 col-lg-3">
                                            <label class="form-label small fw-bold">Search</label>
                                            <input type="text" class="form-control form-control-sm" id="filterSearch" placeholder="Search by name or email...">
                                        </div>
                                    </div>
                                    
                                    <div class="row mt-3">
                                        <div class="col-12 d-flex justify-content-end gap-2">
                                            <button type="button" class="btn btn-primary btn-sm" id="btnApplyFilters">
                                                <i class="fas fa-check me-1"></i> Apply Filters
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary btn-sm" id="btnResetFilters">
                                                <i class="fas fa-undo me-1"></i> Reset Filters
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3" id="activeFiltersContainer" style="display: none;">
                                <div class="d-flex flex-wrap align-items-center">
                                    <span class="small text-muted me-2">Active filters:</span>
                                    <div id="activeFiltersChips"></div>
                                    <button type="button" class="btn btn-link btn-sm text-decoration-none p-0 ms-2" id="btnClearAllFilters">
                                        Clear all
                                    </button>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent"><i class="fas fa-search"></i></span>
                                    <input type="text" class="form-control" id="quickSearchInput" placeholder="Quick search by name, email address, or Contact List...">
                                </div>
                            </div>
                            
                            <div class="table-container" id="addressesTableContainer">
                                <div class="table-responsive">
                                    <table class="table email-sms-table mb-0">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Email-to-SMS Address</th>
                                                <th>Contact List</th>
                                                <th>Template</th>
                                                <th>Reporting Group</th>
                                                <th>Status</th>
                                                <th>Created</th>
                                                <th class="text-end">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="addressesTableBody">
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            <div class="empty-state" id="emptyStateAddresses" style="display: none;">
                                <div class="empty-state-icon">
                                    <i class="fas fa-at"></i>
                                </div>
                                <h4>No Email-to-SMS Addresses</h4>
                                <p>Create your first Email-to-SMS address to start sending SMS messages via email.</p>
                                <button class="btn btn-primary" id="btnCreateAddressEmpty">
                                    <i class="fas fa-plus me-1"></i> Create Address
                                </button>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div class="text-muted small">
                                    Showing <span id="showingCount">0</span> of <span id="totalCount">0</span> addresses
                                </div>
                                <nav>
                                    <ul class="pagination pagination-sm mb-0" id="addressesPagination">
                                    </ul>
                                </nav>
                            </div>
                        </div>
                        
                        <div class="tab-pane fade" id="reporting-groups" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <p class="text-muted mb-0">Reporting Groups are for reporting and billing attribution only. They do not control recipients or content.</p>
                                </div>
                                <button type="button" class="btn btn-primary" id="btnCreateReportingGroup">
                                    <i class="fas fa-plus me-1"></i> Create Reporting Group
                                </button>
                            </div>
                            
                            <div class="collapse mb-3" id="rgFiltersPanel">
                                <div class="card card-body border-0 rounded-3" style="background-color: #f0ebf8;">
                                    <div class="row g-3 align-items-start">
                                        <div class="col-12 col-lg-6">
                                            <label class="form-label small fw-bold">Date Created</label>
                                            <div class="d-flex gap-2 align-items-center">
                                                <input type="date" class="form-control form-control-sm" id="rgFilterDateFrom">
                                                <span class="text-muted small">to</span>
                                                <input type="date" class="form-control form-control-sm" id="rgFilterDateTo">
                                            </div>
                                            <div class="d-flex flex-wrap gap-1 mt-2">
                                                <button type="button" class="btn btn-outline-primary btn-xs date-preset-btn rg-date-preset" data-preset="7days">Last 7 Days</button>
                                                <button type="button" class="btn btn-outline-primary btn-xs date-preset-btn rg-date-preset" data-preset="30days">Last 30 Days</button>
                                                <button type="button" class="btn btn-outline-primary btn-xs date-preset-btn rg-date-preset" data-preset="thismonth">This Month</button>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-4 col-lg-3">
                                            <label class="form-label small fw-bold">Status</label>
                                            <select class="form-select form-select-sm" id="rgFilterStatus">
                                                <option value="">All Statuses</option>
                                                <option value="Active">Active</option>
                                                <option value="Archived">Archived</option>
                                            </select>
                                        </div>
                                        <div class="col-6 col-md-4 col-lg-3">
                                            <label class="form-label small fw-bold">Search</label>
                                            <input type="text" class="form-control form-control-sm" id="rgFilterSearch" placeholder="Search group name...">
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex justify-content-end gap-2 mt-3">
                                        <button type="button" class="btn btn-outline-secondary btn-sm" id="btnResetRgFilters">Reset Filters</button>
                                        <button type="button" class="btn btn-primary btn-sm" id="btnApplyRgFilters">Apply Filters</button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center mb-3" id="rgActiveFiltersContainer" style="display: none;">
                                <div id="rgActiveFiltersChips"></div>
                                <button type="button" class="btn btn-link btn-sm text-danger" id="btnClearRgFilters">Clear All</button>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="d-flex align-items-center gap-2">
                                    <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#rgFiltersPanel">
                                        <i class="fas fa-filter me-1"></i> Filters
                                    </button>
                                    <div class="input-group" style="width: 280px;">
                                        <span class="input-group-text bg-transparent"><i class="fas fa-search"></i></span>
                                        <input type="text" class="form-control" id="rgQuickSearchInput" placeholder="Quick search by group name...">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="table-container" id="reportingGroupsTableContainer">
                                <div class="table-responsive">
                                    <table class="table email-sms-table mb-0">
                                        <thead>
                                            <tr>
                                                <th>Group Name</th>
                                                <th>Description</th>
                                                <th>Linked Addresses</th>
                                                <th>Messages Sent</th>
                                                <th>Last Activity</th>
                                                <th>Created</th>
                                                <th class="text-end">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="reportingGroupsTableBody">
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            <div class="empty-state" id="emptyStateReportingGroups" style="display: none;">
                                <div class="empty-state-icon">
                                    <i class="fas fa-layer-group"></i>
                                </div>
                                <h4>No Reporting Groups</h4>
                                <p>Create Reporting Groups to organize your Email-to-SMS addresses for easier reporting and filtering.</p>
                                <button class="btn btn-primary" id="btnCreateReportingGroupEmpty">
                                    <i class="fas fa-plus me-1"></i> Create Reporting Group
                                </button>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div class="text-muted small">
                                    Showing <span id="rgShowingCount">0</span> of <span id="rgTotalCount">0</span> groups
                                </div>
                            </div>
                        </div>
                        
                        <div class="tab-pane fade" id="configuration" role="tabpanel">
                            <div class="alert alert-warning d-none" id="configConflictWarning" style="background-color: rgba(255, 191, 0, 0.15); border: none; color: #856404;">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Configuration Conflict:</strong> Both "Fixed SenderID" and "Subject as SenderID" are enabled. When Fixed SenderID is ON, the subject line will not be used as the SenderID.
                            </div>
                            
                            <form id="configurationForm">
                                <div class="row">
                                    <div class="col-lg-8">
                                        <div class="card border-0 shadow-sm mb-4">
                                            <div class="card-header bg-transparent border-bottom">
                                                <h6 class="mb-0"><i class="fas fa-envelope me-2 text-primary"></i>Email Settings</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-4">
                                                    <label class="form-label">Originating Email Addresses</label>
                                                    <textarea class="form-control" id="configOriginatingEmails" rows="3" placeholder="Enter allowed email addresses, one per line or comma-separated..."></textarea>
                                                    <div class="form-text">Only emails from these addresses will trigger SMS messages. Leave empty to allow all.</div>
                                                </div>
                                                
                                                <div class="row g-4">
                                                    <div class="col-md-6">
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox" id="configEmailViaMailClient" checked>
                                                            <label class="form-check-label" for="configEmailViaMailClient">
                                                                Email-to-SMS via Mail Client
                                                            </label>
                                                        </div>
                                                        <div class="form-text">Allow sending SMS via standard email clients (Outlook, Gmail, etc.)</div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox" id="configEmailFromAttachments" disabled>
                                                            <label class="form-check-label text-muted" for="configEmailFromAttachments">
                                                                Email-to-SMS from Attachments
                                                                <span class="badge bg-secondary ms-1">Coming Soon</span>
                                                            </label>
                                                        </div>
                                                        <div class="form-text">Parse attached files to extract recipients and messages.</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="card border-0 shadow-sm mb-4">
                                            <div class="card-header bg-transparent border-bottom">
                                                <h6 class="mb-0"><i class="fas fa-sms me-2 text-primary"></i>Message Settings</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-4">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" id="configMultipartSms">
                                                        <label class="form-check-label" for="configMultipartSms">
                                                            Multipart SMS Enabled
                                                        </label>
                                                    </div>
                                                    <div class="form-text">Allow messages longer than 160 characters to be sent as multiple SMS parts.</div>
                                                </div>
                                                
                                                <hr class="my-4">
                                                
                                                <div class="mb-3">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" id="configFixedSenderId">
                                                        <label class="form-check-label" for="configFixedSenderId">
                                                            Fixed SenderID
                                                        </label>
                                                    </div>
                                                    <div class="form-text">Use a specific SenderID for all messages instead of email subject.</div>
                                                </div>
                                                
                                                <div class="mb-4 ps-4" id="senderIdSelectorWrapper" style="display: none;">
                                                    <label class="form-label">SenderID</label>
                                                    <select class="form-select" id="configSenderIdSelector">
                                                        <option value="">Select SenderID...</option>
                                                        <option value="QuickSMS">QuickSMS</option>
                                                        <option value="ALERTS">ALERTS</option>
                                                        <option value="INFO">INFO</option>
                                                        <option value="NOTIFY">NOTIFY</option>
                                                        <option value="NHSTrust">NHSTrust</option>
                                                    </select>
                                                    <div class="form-text">Select a registered SenderID to use for all messages.</div>
                                                    <div class="invalid-feedback" id="senderIdError">SenderID must be 3-11 alphanumeric characters.</div>
                                                </div>
                                                
                                                <div class="mb-3" id="subjectAsSenderIdWrapper">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" id="configSubjectAsSenderId">
                                                        <label class="form-check-label" for="configSubjectAsSenderId">
                                                            Subject as SenderID
                                                        </label>
                                                    </div>
                                                    <div class="form-text">Use the email subject line as the SenderID (max 11 characters, alphanumeric only).</div>
                                                </div>
                                                
                                                <hr class="my-4">
                                                
                                                <div class="mb-0">
                                                    <h6 class="mb-3"><i class="fas fa-flask me-2 text-primary"></i>Resolution Preview</h6>
                                                    <div class="form-text mb-3">See how SenderID will be resolved for different email subjects based on current settings.</div>
                                                    
                                                    <div class="table-responsive">
                                                        <table class="table table-sm table-bordered mb-0" id="resolutionPreviewTable">
                                                            <thead class="table-light">
                                                                <tr>
                                                                    <th style="width: 40%;">Email Subject</th>
                                                                    <th style="width: 30%;">Resolved SenderID</th>
                                                                    <th style="width: 30%;">Result</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody id="resolutionPreviewBody">
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    
                                                    <div class="alert mt-3 mb-0" id="resolutionRuleAlert" style="background-color: rgba(111, 66, 193, 0.08); border: none;">
                                                        <i class="fas fa-info-circle me-2 text-primary"></i>
                                                        <span id="resolutionRuleText">Current rule: Subject extraction mode</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="card border-0 shadow-sm mb-4">
                                            <div class="card-header bg-transparent border-bottom">
                                                <h6 class="mb-0"><i class="fas fa-bell me-2 text-primary"></i>Delivery Receipts</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" id="configDeliveryReceipts">
                                                        <label class="form-check-label" for="configDeliveryReceipts">
                                                            Delivery Receipts to Email
                                                        </label>
                                                    </div>
                                                    <div class="form-text">Receive delivery status notifications via email.</div>
                                                </div>
                                                
                                                <div class="mb-3" id="alternateReceiptsEmailWrapper" style="display: none;">
                                                    <label class="form-label">Alternate Receipts Email</label>
                                                    <input type="email" class="form-control" id="configAlternateReceiptsEmail" placeholder="e.g., reports@yourcompany.com">
                                                    <div class="form-text">Optional. Send delivery receipts to a different email address than the sender.</div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="card border-0 shadow-sm mb-4">
                                            <div class="card-header bg-transparent border-bottom">
                                                <h6 class="mb-0"><i class="fas fa-broom me-2 text-primary"></i>Content Processing</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Signature Removal Patterns</label>
                                                    <textarea class="form-control font-monospace" id="configSignatureRemoval" rows="4" placeholder="Enter patterns to remove from emails (one per line, regex supported)..."></textarea>
                                                    <div class="form-text">
                                                        Remove email signatures or unwanted content before sending. Supports regex patterns.<br>
                                                        <strong>Examples:</strong><br>
                                                        <code>^--\s*$</code> - Standard email signature delimiter<br>
                                                        <code>^Sent from my iPhone$</code> - Mobile signature<br>
                                                        <code>^Kind regards,[\s\S]*$</code> - Remove everything after "Kind regards,"
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-lg-4">
                                        <div class="card border-0 shadow-sm bg-light">
                                            <div class="card-body">
                                                <h6 class="mb-3"><i class="fas fa-info-circle me-2 text-primary"></i>Configuration Tips</h6>
                                                <ul class="small text-muted mb-0">
                                                    <li class="mb-2">Use <strong>Fixed SenderID</strong> for consistent branding across all messages.</li>
                                                    <li class="mb-2"><strong>Subject as SenderID</strong> allows dynamic sender names but must follow the 11-character alphanumeric limit.</li>
                                                    <li class="mb-2">Enable <strong>Multipart SMS</strong> for longer messages, but note this may increase costs.</li>
                                                    <li class="mb-2">Use <strong>Signature Removal</strong> patterns to clean up email content before SMS conversion.</li>
                                                    <li class="mb-2">Restrict <strong>Originating Emails</strong> for security to prevent unauthorized SMS sending.</li>
                                                </ul>
                                            </div>
                                        </div>
                                        
                                        <div class="card border-0 shadow-sm mt-3">
                                            <div class="card-body">
                                                <h6 class="mb-3"><i class="fas fa-history me-2 text-primary"></i>Last Updated</h6>
                                                <p class="small text-muted mb-1">Modified by <strong>John Smith</strong></p>
                                                <p class="small text-muted mb-0">2025-01-08 at 14:32</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-end gap-2 mt-3 pt-3 border-top">
                                    <button type="button" class="btn btn-outline-secondary" id="btnResetConfig">
                                        <i class="fas fa-undo me-1"></i> Reset to Defaults
                                    </button>
                                    <button type="submit" class="btn btn-primary" id="btnSaveConfig">
                                        <i class="fas fa-save me-1"></i> Save Configuration
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="drawer-backdrop" id="drawerBackdrop"></div>
<div class="drawer" id="detailsDrawer">
    <div class="drawer-header">
        <h5 id="drawerTitle">Email-to-SMS Address Details</h5>
        <button type="button" class="btn-close" id="closeDrawerBtn"></button>
    </div>
    <div class="drawer-body">
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="badge badge-live-status" id="drawerStatus">Active</span>
                <div class="dropdown">
                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        Actions
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" id="actionEdit"><i class="fas fa-edit me-2"></i> Edit</a></li>
                        <li><a class="dropdown-item" href="#" id="actionSuspend"><i class="fas fa-pause me-2"></i> Suspend</a></li>
                        <li><a class="dropdown-item" href="#" id="actionViewHistory"><i class="fas fa-history me-2"></i> View History</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="#" id="actionDelete"><i class="fas fa-trash me-2"></i> Delete</a></li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="mb-4">
            <h6 class="text-muted mb-3">Configuration</h6>
            <div class="detail-row">
                <span class="detail-label">Name</span>
                <span class="detail-value" id="drawerName">-</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Email-to-SMS Address</span>
                <span class="detail-value">
                    <code id="drawerEmailAddress">-</code>
                    <button class="copy-btn" onclick="copyToClipboard('drawerEmailAddress')"><i class="fas fa-copy"></i></button>
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Description</span>
                <span class="detail-value" id="drawerDescription">-</span>
            </div>
        </div>
        
        <div class="mb-4">
            <h6 class="text-muted mb-3">Messaging Settings</h6>
            <div class="detail-row">
                <span class="detail-label">Contact List</span>
                <span class="detail-value" id="drawerContactList">-</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Template</span>
                <span class="detail-value" id="drawerTemplate">-</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">SenderID</span>
                <span class="detail-value" id="drawerSenderId">-</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Opt-Out Compliance</span>
                <span class="detail-value" id="drawerOptOut">-</span>
            </div>
        </div>
        
        <div class="mb-4">
            <h6 class="text-muted mb-3">Organization</h6>
            <div class="detail-row">
                <span class="detail-label">Sub Account</span>
                <span class="detail-value" id="drawerSubAccount">-</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Reporting Group</span>
                <span class="detail-value" id="drawerReportingGroup">-</span>
            </div>
        </div>
        
        <div class="mb-4">
            <h6 class="text-muted mb-3">Security</h6>
            <div class="detail-row">
                <span class="detail-label">Allowed Senders</span>
                <span class="detail-value" id="drawerAllowedSenders">-</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Daily Limit</span>
                <span class="detail-value" id="drawerDailyLimit">-</span>
            </div>
        </div>
        
        <div class="mb-4">
            <h6 class="text-muted mb-3">Activity</h6>
            <div class="detail-row">
                <span class="detail-label">Created</span>
                <span class="detail-value" id="drawerCreated">-</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Last Used</span>
                <span class="detail-value" id="drawerLastUsed">-</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Messages Sent</span>
                <span class="detail-value" id="drawerMessagesSent">-</span>
            </div>
        </div>
    </div>
    <div class="drawer-footer">
        <div class="d-flex gap-2">
            <button class="btn btn-primary flex-grow-1" id="btnEditFromDrawer">
                <i class="fas fa-edit me-1"></i> Edit Configuration
            </button>
            <a href="#" class="btn btn-outline-primary" id="btnViewInMessageLog">
                <i class="fas fa-external-link-alt me-1"></i> Message Log
            </a>
        </div>
    </div>
</div>

<div class="modal fade" id="createAddressModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Email-to-SMS Address</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="createName" placeholder="e.g., Appointment Reminders">
                        <div class="form-text">A descriptive name to identify this Email-to-SMS address.</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" id="createDescription" rows="2" placeholder="Optional description..."></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Sub Account <span class="text-danger">*</span></label>
                        <select class="form-select" id="createSubAccount">
                            <option value="">Select sub account...</option>
                            <option value="main">Main Account</option>
                            <option value="marketing">Marketing Team</option>
                            <option value="support">Support Team</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Reporting Group</label>
                        <select class="form-select" id="createReportingGroup">
                            <option value="">Select reporting group...</option>
                            <option value="default">Default</option>
                            <option value="appointments">Appointments</option>
                            <option value="reminders">Reminders</option>
                        </select>
                    </div>
                    
                    <div class="col-12">
                        <hr class="my-2">
                        <h6 class="mb-3">Messaging Configuration</h6>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Contact List <span class="text-danger">*</span></label>
                        <select class="form-select" id="createContactList">
                            <option value="">Select Contact List...</option>
                            <option value="patients">NHS Patients</option>
                            <option value="appointments">Appointment List</option>
                            <option value="newsletter">Newsletter Subscribers</option>
                        </select>
                        <div class="form-text">The Contact List that will receive SMS messages.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Template</label>
                        <select class="form-select" id="createTemplate">
                            <option value="">Use email body as message</option>
                            <option value="apt-reminder">Appointment Reminder</option>
                            <option value="general-notify">General Notification</option>
                        </select>
                        <div class="form-text">Optional template to format the SMS message.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">SenderID <span class="text-danger">*</span></label>
                        <select class="form-select" id="createSenderId">
                            <option value="">Select SenderID...</option>
                            <option value="QuickSMS">QuickSMS</option>
                            <option value="ALERTS">ALERTS</option>
                            <option value="INFO">INFO</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Opt-Out List</label>
                        <select class="form-select" id="createOptOutList">
                            <option value="">No opt-out list</option>
                            <option value="global">Global Opt-Out</option>
                            <option value="marketing">Marketing Opt-Out</option>
                        </select>
                    </div>
                    
                    <div class="col-12">
                        <hr class="my-2">
                        <h6 class="mb-3">Security Settings</h6>
                    </div>
                    
                    <div class="col-12">
                        <label class="form-label">Allowed Sender Emails</label>
                        <textarea class="form-control" id="createAllowedSenders" rows="3" placeholder="Enter allowed email addresses, one per line..."></textarea>
                        <div class="form-text">Leave empty to allow all senders. Restrict to specific email addresses for security.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Daily Message Limit</label>
                        <input type="number" class="form-control" id="createDailyLimit" placeholder="e.g., 1000" min="0">
                        <div class="form-text">Maximum messages per day. Leave empty for no limit.</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btnSaveAddress">
                    <i class="fas fa-check me-1"></i> Create Address
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="createReportingGroupModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Reporting Group</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Group Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="rgName" placeholder="e.g., Patient Communications">
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" id="rgDescription" rows="2" placeholder="Optional description..."></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Color</label>
                    <div class="d-flex gap-2">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="rgColor" id="rgColorPrimary" value="primary" checked>
                            <label class="form-check-label" for="rgColorPrimary"><span class="badge badge-bulk">Primary</span></label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="rgColor" id="rgColorSuccess" value="success">
                            <label class="form-check-label" for="rgColorSuccess"><span class="badge badge-live-status">Success</span></label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="rgColor" id="rgColorWarning" value="warning">
                            <label class="form-check-label" for="rgColorWarning"><span class="badge badge-campaign">Warning</span></label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="rgColor" id="rgColorInfo" value="info">
                            <label class="form-check-label" for="rgColorInfo"><span class="badge badge-test">Info</span></label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btnSaveReportingGroup">
                    <i class="fas fa-check me-1"></i> Create Group
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="suspendModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Suspend Email-to-SMS Address</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to suspend <strong id="suspendAddressName"></strong>?</p>
                <p class="text-muted small">While suspended, emails sent to this address will not trigger SMS messages. You can reactivate it at any time.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="btnConfirmSuspend">
                    <i class="fas fa-pause me-1"></i> Suspend
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Email-to-SMS Address</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <strong id="deleteAddressName"></strong>?</p>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    This action cannot be undone. All configuration and history for this address will be permanently deleted.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="btnConfirmDelete">
                    <i class="fas fa-trash me-1"></i> Delete
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    var mockAddresses = [
        {
            id: 'addr-001',
            name: 'Appointment Reminders',
            emailAddress: 'appointments.12abc@sms.quicksms.io',
            description: 'Automated appointment reminder notifications',
            contactList: 'NHS Patients',
            contactListId: 'patients',
            template: 'Appointment Reminder',
            templateId: 'apt-reminder',
            senderId: 'NHS Trust',
            optOut: 'Global Opt-Out',
            subAccount: 'Main Account',
            reportingGroup: 'Appointments',
            allowedSenders: ['admin@nhstrust.nhs.uk', 'system@nhstrust.nhs.uk'],
            dailyLimit: 5000,
            status: 'Active',
            created: '2024-11-15',
            lastUsed: '2025-01-09 08:45',
            messagesSent: 12847
        },
        {
            id: 'addr-002',
            name: 'Prescription Ready',
            emailAddress: 'prescriptions.45def@sms.quicksms.io',
            description: 'Notify patients when prescriptions are ready',
            contactList: 'Pharmacy Patients',
            contactListId: 'pharmacy',
            template: null,
            templateId: null,
            senderId: 'Pharmacy',
            optOut: 'Marketing Opt-Out',
            subAccount: 'Marketing Team',
            reportingGroup: 'Reminders',
            allowedSenders: [],
            dailyLimit: 2000,
            status: 'Active',
            created: '2024-12-01',
            lastUsed: '2025-01-08 16:20',
            messagesSent: 3421
        },
        {
            id: 'addr-003',
            name: 'Test Notifications',
            emailAddress: 'test.78ghi@sms.quicksms.io',
            description: 'Test address for development',
            contactList: 'Test List',
            contactListId: 'test',
            template: 'General Notification',
            templateId: 'general-notify',
            senderId: 'QuickSMS',
            optOut: null,
            subAccount: 'Support Team',
            reportingGroup: 'Default',
            allowedSenders: ['developer@company.com'],
            dailyLimit: 100,
            status: 'Suspended',
            created: '2025-01-02',
            lastUsed: '2025-01-05 11:30',
            messagesSent: 156
        }
    ];
    
    var mockReportingGroups = [
        { 
            id: 'rg-001', 
            name: 'Default', 
            description: 'Default reporting group for uncategorized messages', 
            linkedAddresses: ['Test Notifications'],
            messagesSent: 156,
            lastActivity: '2025-01-05 11:30',
            created: '2024-10-01',
            status: 'Active'
        },
        { 
            id: 'rg-002', 
            name: 'Appointments', 
            description: 'All appointment-related SMS communications', 
            linkedAddresses: ['Appointment Reminders'],
            messagesSent: 12847,
            lastActivity: '2025-01-09 08:45',
            created: '2024-11-10',
            status: 'Active'
        },
        { 
            id: 'rg-003', 
            name: 'Reminders', 
            description: 'General reminder and notification messages', 
            linkedAddresses: ['Prescription Ready'],
            messagesSent: 3421,
            lastActivity: '2025-01-08 16:20',
            created: '2024-11-25',
            status: 'Active'
        },
        { 
            id: 'rg-004', 
            name: 'Marketing Campaigns', 
            description: 'Promotional and marketing SMS campaigns', 
            linkedAddresses: [],
            messagesSent: 45892,
            lastActivity: '2024-12-20 14:00',
            created: '2024-08-15',
            status: 'Archived'
        },
        { 
            id: 'rg-005', 
            name: 'Urgent Alerts', 
            description: 'High-priority urgent notifications', 
            linkedAddresses: ['Emergency Alerts', 'System Notifications'],
            messagesSent: 892,
            lastActivity: '2025-01-07 09:15',
            created: '2024-12-01',
            status: 'Active'
        }
    ];
    
    var rgAppliedFilters = {};
    
    var selectedAddress = null;
    var appliedFilters = {};
    
    function renderAddressesTable(addresses) {
        var tbody = $('#addressesTableBody');
        tbody.empty();
        
        if (addresses.length === 0) {
            $('#addressesTableContainer').hide();
            $('#emptyStateAddresses').show();
            return;
        }
        
        $('#addressesTableContainer').show();
        $('#emptyStateAddresses').hide();
        
        addresses.forEach(function(addr) {
            var statusBadge = addr.status === 'Active' 
                ? '<span class="badge badge-live-status">Active</span>'
                : '<span class="badge badge-suspended">Suspended</span>';
            
            var templateDisplay = addr.template || '<span class="text-muted">Email body</span>';
            
            var row = '<tr data-id="' + addr.id + '">' +
                '<td><strong>' + addr.name + '</strong></td>' +
                '<td><code class="email-address-display">' + addr.emailAddress + '</code></td>' +
                '<td>' + addr.contactList + '</td>' +
                '<td>' + templateDisplay + '</td>' +
                '<td><span class="badge badge-bulk">' + addr.reportingGroup + '</span></td>' +
                '<td>' + statusBadge + '</td>' +
                '<td>' + addr.created + '</td>' +
                '<td class="text-end">' +
                    '<div class="dropdown">' +
                        '<button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" onclick="event.stopPropagation();">' +
                            '<i class="fas fa-ellipsis-v"></i>' +
                        '</button>' +
                        '<ul class="dropdown-menu dropdown-menu-end">' +
                            '<li><a class="dropdown-item view-details" href="#" data-id="' + addr.id + '"><i class="fas fa-eye me-2"></i> View Details</a></li>' +
                            '<li><a class="dropdown-item edit-address" href="#" data-id="' + addr.id + '"><i class="fas fa-edit me-2"></i> Edit</a></li>' +
                            (addr.status === 'Active' 
                                ? '<li><a class="dropdown-item suspend-address" href="#" data-id="' + addr.id + '"><i class="fas fa-pause me-2"></i> Suspend</a></li>'
                                : '<li><a class="dropdown-item reactivate-address" href="#" data-id="' + addr.id + '"><i class="fas fa-play me-2"></i> Reactivate</a></li>') +
                            '<li><hr class="dropdown-divider"></li>' +
                            '<li><a class="dropdown-item text-danger delete-address" href="#" data-id="' + addr.id + '"><i class="fas fa-trash me-2"></i> Delete</a></li>' +
                        '</ul>' +
                    '</div>' +
                '</td>' +
            '</tr>';
            
            tbody.append(row);
        });
        
        $('#showingCount').text(addresses.length);
        $('#totalCount').text(addresses.length);
    }
    
    function renderReportingGroups(groups) {
        var tbody = $('#reportingGroupsTableBody');
        tbody.empty();
        
        if (groups.length === 0) {
            $('#reportingGroupsTableContainer').hide();
            $('#emptyStateReportingGroups').show();
            $('#rgShowingCount').text(0);
            $('#rgTotalCount').text(0);
            return;
        }
        
        $('#reportingGroupsTableContainer').show();
        $('#emptyStateReportingGroups').hide();
        
        groups.forEach(function(group) {
            var statusBadge = group.status === 'Active' 
                ? '<span class="badge badge-live-status">Active</span>'
                : '<span class="badge badge-test">Archived</span>';
            
            var linkedDisplay = group.linkedAddresses.length > 0 
                ? group.linkedAddresses.map(function(addr) { return '<span class="badge badge-bulk me-1">' + addr + '</span>'; }).join('')
                : '<span class="text-muted">None</span>';
            
            var row = '<tr data-id="' + group.id + '">' +
                '<td><strong>' + group.name + '</strong></td>' +
                '<td class="text-muted small">' + (group.description || '-') + '</td>' +
                '<td>' + linkedDisplay + '</td>' +
                '<td>' + group.messagesSent.toLocaleString() + '</td>' +
                '<td>' + group.lastActivity + '</td>' +
                '<td>' + group.created + '</td>' +
                '<td class="text-end">' +
                    '<div class="dropdown">' +
                        '<button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" onclick="event.stopPropagation();">' +
                            '<i class="fas fa-ellipsis-v"></i>' +
                        '</button>' +
                        '<ul class="dropdown-menu dropdown-menu-end">' +
                            '<li><a class="dropdown-item edit-rg" href="#" data-id="' + group.id + '"><i class="fas fa-edit me-2"></i> Edit</a></li>' +
                            (group.status === 'Active' 
                                ? '<li><a class="dropdown-item archive-rg" href="#" data-id="' + group.id + '"><i class="fas fa-archive me-2"></i> Archive</a></li>'
                                : '<li><a class="dropdown-item unarchive-rg" href="#" data-id="' + group.id + '"><i class="fas fa-undo me-2"></i> Unarchive</a></li>') +
                        '</ul>' +
                    '</div>' +
                '</td>' +
            '</tr>';
            
            tbody.append(row);
        });
        
        $('#rgShowingCount').text(groups.length);
        $('#rgTotalCount').text(mockReportingGroups.length);
    }
    
    function filterReportingGroups() {
        var filtered = mockReportingGroups.slice();
        var chips = [];
        
        var searchTerm = ($('#rgQuickSearchInput').val() || $('#rgFilterSearch').val() || '').toLowerCase().trim();
        if (searchTerm) {
            filtered = filtered.filter(function(g) {
                return g.name.toLowerCase().indexOf(searchTerm) !== -1;
            });
            chips.push({ filter: 'search', value: 'Search: ' + searchTerm });
        }
        
        var statusFilter = $('#rgFilterStatus').val();
        if (statusFilter) {
            filtered = filtered.filter(function(g) {
                return g.status === statusFilter;
            });
            chips.push({ filter: 'status', value: 'Status: ' + statusFilter });
        }
        
        var dateFrom = $('#rgFilterDateFrom').val();
        var dateTo = $('#rgFilterDateTo').val();
        if (dateFrom || dateTo) {
            filtered = filtered.filter(function(g) {
                var groupDate = g.created;
                if (dateFrom && groupDate < dateFrom) return false;
                if (dateTo && groupDate > dateTo) return false;
                return true;
            });
            var dateLabel = '';
            if (dateFrom && dateTo) {
                dateLabel = 'Date: ' + dateFrom + ' to ' + dateTo;
            } else if (dateFrom) {
                dateLabel = 'Date: From ' + dateFrom;
            } else {
                dateLabel = 'Date: To ' + dateTo;
            }
            chips.push({ filter: 'date', value: dateLabel });
        }
        
        if (chips.length > 0) {
            var chipsHtml = '';
            chips.forEach(function(chip) {
                chipsHtml += '<span class="filter-chip">' + chip.value + 
                    '<span class="remove-chip rg-remove-chip" data-filter="' + chip.filter + '">&times;</span></span>';
            });
            $('#rgActiveFiltersChips').html(chipsHtml);
            $('#rgActiveFiltersContainer').show();
        } else {
            $('#rgActiveFiltersContainer').hide();
        }
        
        renderReportingGroups(filtered);
    }
    
    function resetRgFilters() {
        $('#rgQuickSearchInput').val('');
        $('#rgFilterSearch').val('');
        $('#rgFilterStatus').val('');
        $('#rgFilterDateFrom').val('');
        $('#rgFilterDateTo').val('');
        $('.rg-date-preset').removeClass('active');
        rgAppliedFilters = {};
        $('#rgActiveFiltersContainer').hide();
        renderReportingGroups(mockReportingGroups);
    }
    
    function openDetailsDrawer(address) {
        selectedAddress = address;
        
        $('#drawerName').text(address.name);
        $('#drawerEmailAddress').text(address.emailAddress);
        $('#drawerDescription').text(address.description || '-');
        $('#drawerContactList').text(address.contactList);
        $('#drawerTemplate').text(address.template || 'Using email body');
        $('#drawerSenderId').text(address.senderId);
        $('#drawerOptOut').text(address.optOut || 'None configured');
        $('#drawerSubAccount').text(address.subAccount);
        $('#drawerReportingGroup').text(address.reportingGroup);
        $('#drawerAllowedSenders').text(address.allowedSenders.length > 0 ? address.allowedSenders.join(', ') : 'All senders allowed');
        $('#drawerDailyLimit').text(address.dailyLimit ? address.dailyLimit.toLocaleString() + ' messages/day' : 'No limit');
        $('#drawerCreated').text(address.created);
        $('#drawerLastUsed').text(address.lastUsed || 'Never');
        $('#drawerMessagesSent').text(address.messagesSent.toLocaleString());
        
        if (address.status === 'Active') {
            $('#drawerStatus').removeClass('badge-suspended').addClass('badge-live-status').text('Active');
            $('#actionSuspend').html('<i class="fas fa-pause me-2"></i> Suspend');
        } else {
            $('#drawerStatus').removeClass('badge-live-status').addClass('badge-suspended').text('Suspended');
            $('#actionSuspend').html('<i class="fas fa-play me-2"></i> Reactivate');
        }
        
        $('#drawerBackdrop').addClass('show');
        $('#detailsDrawer').addClass('open');
    }
    
    function closeDetailsDrawer() {
        $('#drawerBackdrop').removeClass('show');
        $('#detailsDrawer').removeClass('open');
        selectedAddress = null;
    }
    
    function applyFilters() {
        var chips = [];
        var filtered = mockAddresses.slice();
        
        $('.multiselect-dropdown').each(function() {
            var filterName = $(this).data('filter');
            var selected = $(this).find('input:checked').map(function() {
                return $(this).val();
            }).get();
            
            if (selected.length > 0) {
                appliedFilters[filterName] = selected;
                selected.forEach(function(val) {
                    chips.push({ filter: filterName, value: val });
                });
            }
        });
        
        var search = $('#filterSearch').val().trim();
        if (search) {
            appliedFilters.search = search;
            chips.push({ filter: 'search', value: 'Search: ' + search });
        }
        
        if (chips.length > 0) {
            var chipsHtml = '';
            chips.forEach(function(chip) {
                chipsHtml += '<span class="filter-chip">' + chip.value + 
                    '<span class="remove-chip" data-filter="' + chip.filter + '" data-value="' + chip.value + '">&times;</span></span>';
            });
            $('#activeFiltersChips').html(chipsHtml);
            $('#activeFiltersContainer').show();
        } else {
            $('#activeFiltersContainer').hide();
        }
        
        renderAddressesTable(filtered);
    }
    
    function resetFilters() {
        $('.multiselect-dropdown input').prop('checked', false);
        $('.multiselect-dropdown .dropdown-label').each(function() {
            var defaultText = 'All ' + $(this).closest('.multiselect-dropdown').data('filter');
            $(this).text(defaultText.charAt(0).toUpperCase() + defaultText.slice(1));
        });
        $('#filterSearch').val('');
        $('#filterDateFrom').val('');
        $('#filterDateTo').val('');
        $('.date-preset-btn').removeClass('active');
        appliedFilters = {};
        $('#activeFiltersContainer').hide();
        renderAddressesTable(mockAddresses);
    }
    
    $('#btnCreateAddress, #btnCreateAddressEmpty').on('click', function() {
        $('#createAddressModal').modal('show');
    });
    
    $('#btnCreateReportingGroup, #btnCreateReportingGroupEmpty').on('click', function() {
        $('#createReportingGroupModal').modal('show');
    });
    
    $(document).on('click', '#addressesTableBody tr', function(e) {
        if ($(e.target).closest('.dropdown').length) return;
        var id = $(this).data('id');
        var address = mockAddresses.find(function(a) { return a.id === id; });
        if (address) openDetailsDrawer(address);
    });
    
    $(document).on('click', '.view-details', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var address = mockAddresses.find(function(a) { return a.id === id; });
        if (address) openDetailsDrawer(address);
    });
    
    $(document).on('click', '.suspend-address', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var address = mockAddresses.find(function(a) { return a.id === id; });
        if (address) {
            $('#suspendAddressName').text(address.name);
            $('#suspendModal').data('id', id).modal('show');
        }
    });
    
    $(document).on('click', '.delete-address', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var address = mockAddresses.find(function(a) { return a.id === id; });
        if (address) {
            $('#deleteAddressName').text(address.name);
            $('#deleteModal').data('id', id).modal('show');
        }
    });
    
    $('#closeDrawerBtn, #drawerBackdrop').on('click', closeDetailsDrawer);
    
    $('#btnApplyFilters').on('click', applyFilters);
    $('#btnResetFilters').on('click', resetFilters);
    $('#btnClearAllFilters').on('click', resetFilters);
    
    $(document).on('click', '.remove-chip', function() {
        var filter = $(this).data('filter');
        var value = $(this).data('value');
        $(this).parent().remove();
        if ($('#activeFiltersChips .filter-chip').length === 0) {
            $('#activeFiltersContainer').hide();
        }
    });
    
    var quickSearchTimeout;
    $('#quickSearchInput').on('input', function() {
        var query = $(this).val().toLowerCase().trim();
        clearTimeout(quickSearchTimeout);
        quickSearchTimeout = setTimeout(function() {
            if (query.length === 0) {
                renderAddressesTable(mockAddresses);
                return;
            }
            var filtered = mockAddresses.filter(function(addr) {
                return addr.name.toLowerCase().includes(query) ||
                       addr.emailAddress.toLowerCase().includes(query) ||
                       addr.contactList.toLowerCase().includes(query);
            });
            renderAddressesTable(filtered);
        }, 300);
    });
    
    $('.date-preset-btn').on('click', function() {
        $('.date-preset-btn').removeClass('active');
        $(this).addClass('active');
    });
    
    $('.select-all-btn').on('click', function(e) {
        e.preventDefault();
        $(this).closest('.dropdown-menu').find('input[type="checkbox"]').prop('checked', true);
    });
    
    $('.clear-all-btn').on('click', function(e) {
        e.preventDefault();
        $(this).closest('.dropdown-menu').find('input[type="checkbox"]').prop('checked', false);
    });
    
    $('#btnSaveAddress').on('click', function() {
        var name = $('#createName').val().trim();
        var subAccount = $('#createSubAccount').val();
        var contactList = $('#createContactList').val();
        var senderId = $('#createSenderId').val();
        
        if (!name || !subAccount || !contactList || !senderId) {
            alert('Please fill in all required fields.');
            return;
        }
        
        var newAddress = {
            id: 'addr-' + Date.now(),
            name: name,
            emailAddress: name.toLowerCase().replace(/\s+/g, '-') + '.' + Math.random().toString(36).substr(2, 5) + '@sms.quicksms.io',
            description: $('#createDescription').val().trim(),
            contactList: $('#createContactList option:selected').text(),
            contactListId: contactList,
            template: $('#createTemplate option:selected').text() || null,
            templateId: $('#createTemplate').val() || null,
            senderId: senderId,
            optOut: $('#createOptOutList option:selected').text() || null,
            subAccount: $('#createSubAccount option:selected').text(),
            reportingGroup: $('#createReportingGroup option:selected').text() || 'Default',
            allowedSenders: $('#createAllowedSenders').val().split('\n').filter(function(s) { return s.trim(); }),
            dailyLimit: parseInt($('#createDailyLimit').val()) || null,
            status: 'Active',
            created: new Date().toISOString().split('T')[0],
            lastUsed: null,
            messagesSent: 0
        };
        
        mockAddresses.unshift(newAddress);
        renderAddressesTable(mockAddresses);
        $('#createAddressModal').modal('hide');
        
        $('#createName, #createDescription, #createAllowedSenders, #createDailyLimit').val('');
        $('#createSubAccount, #createContactList, #createTemplate, #createSenderId, #createOptOutList, #createReportingGroup').val('');
    });
    
    $('#btnSaveReportingGroup').on('click', function() {
        var name = $('#rgName').val().trim();
        if (!name) {
            alert('Please enter a group name.');
            return;
        }
        
        var newGroup = {
            id: 'rg-' + Date.now(),
            name: name,
            description: $('#rgDescription').val().trim(),
            linkedAddresses: [],
            messagesSent: 0,
            lastActivity: '-',
            created: new Date().toISOString().split('T')[0],
            status: 'Active'
        };
        
        mockReportingGroups.push(newGroup);
        renderReportingGroups(mockReportingGroups);
        $('#createReportingGroupModal').modal('hide');
        
        $('#rgName, #rgDescription').val('');
        $('#rgColorPrimary').prop('checked', true);
    });
    
    // Reporting Groups filter/search handlers
    $('#btnApplyRgFilters').on('click', filterReportingGroups);
    $('#btnResetRgFilters').on('click', resetRgFilters);
    $('#btnClearRgFilters').on('click', resetRgFilters);
    
    var rgQuickSearchTimeout;
    $('#rgQuickSearchInput').on('input', function() {
        clearTimeout(rgQuickSearchTimeout);
        rgQuickSearchTimeout = setTimeout(filterReportingGroups, 300);
    });
    
    $('#rgFilterSearch').on('input', function() {
        clearTimeout(rgQuickSearchTimeout);
        rgQuickSearchTimeout = setTimeout(filterReportingGroups, 300);
    });
    
    $(document).on('click', '.rg-remove-chip', function() {
        var filter = $(this).data('filter');
        if (filter === 'search') {
            $('#rgQuickSearchInput').val('');
            $('#rgFilterSearch').val('');
        } else if (filter === 'status') {
            $('#rgFilterStatus').val('');
        } else if (filter === 'date') {
            $('#rgFilterDateFrom').val('');
            $('#rgFilterDateTo').val('');
            $('.rg-date-preset').removeClass('active');
        }
        filterReportingGroups();
    });
    
    $('.rg-date-preset').on('click', function() {
        $('.rg-date-preset').removeClass('active');
        $(this).addClass('active');
        
        var preset = $(this).data('preset');
        var today = new Date();
        var fromDate, toDate;
        
        toDate = today.toISOString().split('T')[0];
        
        if (preset === '7days') {
            fromDate = new Date(today.setDate(today.getDate() - 7)).toISOString().split('T')[0];
        } else if (preset === '30days') {
            fromDate = new Date(today.setDate(today.getDate() - 30)).toISOString().split('T')[0];
        } else if (preset === 'thismonth') {
            fromDate = new Date(today.getFullYear(), today.getMonth(), 1).toISOString().split('T')[0];
        }
        
        $('#rgFilterDateFrom').val(fromDate);
        $('#rgFilterDateTo').val(new Date().toISOString().split('T')[0]);
        filterReportingGroups();
    });
    
    $(document).on('click', '.archive-rg', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var group = mockReportingGroups.find(function(g) { return g.id === id; });
        if (group) {
            group.status = 'Archived';
            renderReportingGroups(mockReportingGroups);
        }
    });
    
    $(document).on('click', '.unarchive-rg', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var group = mockReportingGroups.find(function(g) { return g.id === id; });
        if (group) {
            group.status = 'Active';
            renderReportingGroups(mockReportingGroups);
        }
    });
    
    $('#btnConfirmSuspend').on('click', function() {
        var id = $('#suspendModal').data('id');
        var address = mockAddresses.find(function(a) { return a.id === id; });
        if (address) {
            address.status = address.status === 'Active' ? 'Suspended' : 'Active';
            renderAddressesTable(mockAddresses);
            if (selectedAddress && selectedAddress.id === id) {
                openDetailsDrawer(address);
            }
        }
        $('#suspendModal').modal('hide');
    });
    
    $('#btnConfirmDelete').on('click', function() {
        var id = $('#deleteModal').data('id');
        mockAddresses = mockAddresses.filter(function(a) { return a.id !== id; });
        renderAddressesTable(mockAddresses);
        closeDetailsDrawer();
        $('#deleteModal').modal('hide');
    });
    
    $('#actionSuspend').on('click', function(e) {
        e.preventDefault();
        if (selectedAddress) {
            $('#suspendAddressName').text(selectedAddress.name);
            $('#suspendModal').data('id', selectedAddress.id).modal('show');
        }
    });
    
    $('#actionDelete').on('click', function(e) {
        e.preventDefault();
        if (selectedAddress) {
            $('#deleteAddressName').text(selectedAddress.name);
            $('#deleteModal').data('id', selectedAddress.id).modal('show');
        }
    });
    
    renderAddressesTable(mockAddresses);
    renderReportingGroups(mockReportingGroups);
    
    // Configuration tab handlers
    function checkConfigConflict() {
        var fixedSenderId = $('#configFixedSenderId').is(':checked');
        var subjectAsSenderId = $('#configSubjectAsSenderId').is(':checked');
        
        if (fixedSenderId && subjectAsSenderId) {
            $('#configConflictWarning').removeClass('d-none');
        } else {
            $('#configConflictWarning').addClass('d-none');
        }
    }
    
    function validateSenderId(value) {
        if (!value) return false;
        if (value.length < 3 || value.length > 11) return false;
        return /^[a-zA-Z0-9]+$/.test(value);
    }
    
    function logAuditEvent(action, details) {
        // TODO: Backend integration - log audit event
        console.log('[AUDIT]', new Date().toISOString(), action, details);
    }
    
    $('#configFixedSenderId').on('change', function() {
        var isChecked = $(this).is(':checked');
        if (isChecked) {
            $('#senderIdSelectorWrapper').slideDown();
        } else {
            $('#senderIdSelectorWrapper').slideUp();
            $('#configSenderIdSelector').removeClass('is-invalid');
        }
        checkConfigConflict();
    });
    
    $('#configSubjectAsSenderId').on('change', function() {
        checkConfigConflict();
    });
    
    $('#configDeliveryReceipts').on('change', function() {
        var isChecked = $(this).is(':checked');
        if (isChecked) {
            $('#alternateReceiptsEmailWrapper').slideDown();
        } else {
            $('#alternateReceiptsEmailWrapper').slideUp();
        }
    });
    
    $('#configSenderIdSelector').on('change', function() {
        var value = $(this).val();
        if (value && !validateSenderId(value)) {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });
    
    $('#btnSaveConfig').on('click', function(e) {
        e.preventDefault();
        
        var fixedSenderId = $('#configFixedSenderId').is(':checked');
        var selectedSenderId = $('#configSenderIdSelector').val();
        
        if (fixedSenderId && !selectedSenderId) {
            $('#configSenderIdSelector').addClass('is-invalid');
            $('#configSenderIdSelector').focus();
            return;
        }
        
        if (fixedSenderId && selectedSenderId && !validateSenderId(selectedSenderId)) {
            $('#configSenderIdSelector').addClass('is-invalid');
            return;
        }
        
        var configData = {
            originatingEmails: $('#configOriginatingEmails').val(),
            emailViaMailClient: $('#configEmailViaMailClient').is(':checked'),
            emailFromAttachments: $('#configEmailFromAttachments').is(':checked'),
            multipartSms: $('#configMultipartSms').is(':checked'),
            fixedSenderId: fixedSenderId,
            senderId: selectedSenderId,
            subjectAsSenderId: $('#configSubjectAsSenderId').is(':checked'),
            deliveryReceipts: $('#configDeliveryReceipts').is(':checked'),
            alternateReceiptsEmail: $('#configAlternateReceiptsEmail').val(),
            signatureRemoval: $('#configSignatureRemoval').val()
        };
        
        logAuditEvent('CONFIG_UPDATE', configData);
        
        // TODO: Backend integration - save configuration
        console.log('Saving configuration:', configData);
        
        var btn = $(this);
        var originalHtml = btn.html();
        btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Saving...');
        btn.prop('disabled', true);
        
        setTimeout(function() {
            btn.html('<i class="fas fa-check me-1"></i> Saved!');
            setTimeout(function() {
                btn.html(originalHtml);
                btn.prop('disabled', false);
            }, 1500);
        }, 800);
    });
    
    $('#btnResetConfig').on('click', function() {
        if (!confirm('Reset all configuration settings to defaults?')) return;
        
        $('#configOriginatingEmails').val('');
        $('#configEmailViaMailClient').prop('checked', true);
        $('#configMultipartSms').prop('checked', false);
        $('#configFixedSenderId').prop('checked', false);
        $('#senderIdSelectorWrapper').hide();
        $('#configSenderIdSelector').val('').removeClass('is-invalid');
        $('#configSubjectAsSenderId').prop('checked', false);
        $('#configDeliveryReceipts').prop('checked', false);
        $('#alternateReceiptsEmailWrapper').hide();
        $('#configAlternateReceiptsEmail').val('');
        $('#configSignatureRemoval').val('');
        $('#configConflictWarning').addClass('d-none');
        
        logAuditEvent('CONFIG_RESET', { resetBy: 'user' });
        updateResolutionPreview();
    });
    
    // Resolution Preview Logic
    var mockSubjects = [
        { subject: 'ALERTS', description: 'Valid alphanumeric (6 chars)' },
        { subject: 'NHS2024', description: 'Valid alphanumeric (7 chars)' },
        { subject: 'Reminder: Your appointment', description: 'Contains spaces and special chars' },
        { subject: 'AB', description: 'Too short (2 chars)' },
        { subject: 'ABCDEFGHIJKL', description: 'Too long (12 chars)' },
        { subject: '', description: 'Empty subject' }
    ];
    
    function extractSenderIdFromSubject(subject) {
        if (!subject || subject.trim() === '') {
            return { valid: false, senderId: null, reason: 'Empty subject' };
        }
        
        var cleaned = subject.replace(/[^a-zA-Z0-9]/g, '');
        
        if (cleaned.length < 3) {
            return { valid: false, senderId: null, reason: 'Too short after cleaning' };
        }
        
        if (cleaned.length > 11) {
            cleaned = cleaned.substring(0, 11);
        }
        
        if (/^[a-zA-Z0-9]{3,11}$/.test(cleaned)) {
            return { valid: true, senderId: cleaned, reason: 'Extracted from subject' };
        }
        
        return { valid: false, senderId: null, reason: 'Invalid format' };
    }
    
    function updateResolutionPreview() {
        var fixedSenderId = $('#configFixedSenderId').is(':checked');
        var selectedSenderId = $('#configSenderIdSelector').val();
        var subjectAsSenderId = $('#configSubjectAsSenderId').is(':checked');
        
        var tbody = $('#resolutionPreviewBody');
        tbody.empty();
        
        var ruleText = '';
        
        if (fixedSenderId) {
            ruleText = '<strong>Fixed SenderID Mode:</strong> All emails use "' + (selectedSenderId || '<em>not selected</em>') + '" regardless of subject.';
        } else if (subjectAsSenderId) {
            ruleText = '<strong>Subject Extraction Mode:</strong> SenderID extracted from email subject. Invalid subjects will be rejected.';
        } else {
            ruleText = '<strong>Default Mode:</strong> No SenderID resolution configured. Emails will be rejected.';
        }
        
        $('#resolutionRuleText').html(ruleText);
        
        mockSubjects.forEach(function(mock) {
            var resolvedSenderId = '';
            var resultHtml = '';
            
            if (fixedSenderId) {
                if (selectedSenderId) {
                    resolvedSenderId = selectedSenderId;
                    resultHtml = '<span class="badge badge-live-status">Accepted</span>';
                } else {
                    resolvedSenderId = '<em class="text-muted">Not configured</em>';
                    resultHtml = '<span class="badge badge-suspended">Rejected</span>';
                }
            } else if (subjectAsSenderId) {
                var extraction = extractSenderIdFromSubject(mock.subject);
                if (extraction.valid) {
                    resolvedSenderId = extraction.senderId;
                    resultHtml = '<span class="badge badge-live-status">Accepted</span>';
                } else {
                    resolvedSenderId = '<em class="text-muted">' + extraction.reason + '</em>';
                    resultHtml = '<span class="badge badge-suspended">Rejected</span>';
                }
            } else {
                resolvedSenderId = '<em class="text-muted">No extraction</em>';
                resultHtml = '<span class="badge badge-suspended">Rejected</span>';
            }
            
            var subjectDisplay = mock.subject ? '<code>' + mock.subject + '</code>' : '<em class="text-muted">(empty)</em>';
            
            var row = '<tr>' +
                '<td>' + subjectDisplay + '<br><small class="text-muted">' + mock.description + '</small></td>' +
                '<td>' + resolvedSenderId + '</td>' +
                '<td>' + resultHtml + '</td>' +
            '</tr>';
            
            tbody.append(row);
        });
    }
    
    $('#configFixedSenderId').on('change', function() {
        updateResolutionPreview();
    });
    
    $('#configSubjectAsSenderId').on('change', function() {
        updateResolutionPreview();
    });
    
    $('#configSenderIdSelector').on('change', function() {
        updateResolutionPreview();
    });
    
    updateResolutionPreview();
});

function copyToClipboard(elementId) {
    var text = document.getElementById(elementId).textContent;
    navigator.clipboard.writeText(text).then(function() {
        var btn = document.querySelector('[onclick="copyToClipboard(\'' + elementId + '\')"]');
        var originalIcon = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check text-success"></i>';
        setTimeout(function() {
            btn.innerHTML = originalIcon;
        }, 1500);
    });
}
</script>
@endpush
