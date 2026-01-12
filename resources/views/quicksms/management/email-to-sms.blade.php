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
.action-menu-btn {
    background: transparent;
    border: none;
    padding: 0.25rem 0.5rem;
    cursor: pointer;
    color: #6c757d;
}
.action-menu-btn:hover {
    color: var(--primary);
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
#standard textarea.form-control {
    min-height: auto;
    height: auto;
}
#standard .card-body {
    padding: 1rem;
}
#standard .card-header {
    padding: 0.75rem 1rem;
}
.email-sms-table {
    width: 100%;
    margin: 0;
    border-collapse: collapse;
}
.email-sms-table thead th {
    font-weight: 600;
    color: #495057;
    font-size: 0.8rem;
    padding: 0.75rem 1rem;
    text-align: left;
    white-space: nowrap;
    border: none;
    border-bottom: 1px solid #e9ecef;
    background: #f8f9fa;
}
.email-sms-table tbody td {
    vertical-align: middle;
    padding: 0.75rem 1rem;
    border: none;
    border-bottom: 1px solid #f1f3f5;
    font-size: 0.85rem;
    color: #495057;
    font-weight: 400;
}
.email-sms-table tbody tr:last-child td {
    border-bottom: none;
}
.email-sms-table tbody tr {
    cursor: pointer;
}
.email-sms-table tbody tr:hover td {
    background-color: #f8f9fa;
}
.email-sms-name {
    font-weight: 500;
    color: #343a40;
}
.table-container {
    background: #fff;
    border-radius: 0.75rem;
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
.email-tags-container {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    min-height: 38px;
    padding: 0.5rem 0;
}
.email-tag {
    display: inline-flex;
    align-items: center;
    background: #f0ebf8;
    border: 1px solid rgba(136, 108, 192, 0.3);
    border-radius: 2rem;
    padding: 0.35rem 0.75rem;
    font-size: 0.85rem;
    color: #2c2c2c;
}
.email-tag.wildcard-tag {
    background: #fff3cd;
    border-color: rgba(255, 193, 7, 0.5);
}
.email-tag .remove-email {
    margin-left: 0.5rem;
    color: #886CC0;
    cursor: pointer;
    transition: color 0.2s;
}
.email-tag .remove-email:hover {
    color: #dc3545;
}
.create-modal-section {
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 0.5rem;
    margin-bottom: 1.5rem;
}
.create-modal-section-header {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #e9ecef;
    background: #f8f9fa;
    border-radius: 0.5rem 0.5rem 0 0;
}
.create-modal-section-header h6 {
    margin: 0;
    font-weight: 600;
    color: #343a40;
    font-size: 0.95rem;
}
.create-modal-section-body {
    padding: 1.25rem;
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
                <div class="card-header">
                    <div>
                        <h4 class="card-title mb-1">Email-to-SMS</h4>
                        <p class="mb-0 text-muted small">Configure email addresses to trigger SMS messages to your Contact Lists.</p>
                    </div>
                </div>
                <div class="card-body">
                    <ul class="nav nav-tabs" id="emailSmsTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="addresses-tab" data-bs-toggle="tab" data-bs-target="#addresses" type="button" role="tab">
                                <i class="fas fa-at me-1"></i> Overview
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="standard-tab" data-bs-toggle="tab" data-bs-target="#standard" type="button" role="tab">
                                <i class="fas fa-envelope-open-text me-1"></i> Email-to-SMS – Standard
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="contact-lists-tab" data-bs-toggle="tab" data-bs-target="#contact-lists" type="button" role="tab">
                                <i class="fas fa-link me-1"></i> Email-to-SMS – Contact List
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="reporting-groups-tab" data-bs-toggle="tab" data-bs-target="#reporting-groups" type="button" role="tab">
                                <i class="fas fa-layer-group me-1"></i> Reporting Groups
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
                            
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="input-group" style="width: 280px;">
                                        <span class="input-group-text bg-transparent"><i class="fas fa-search"></i></span>
                                        <input type="text" class="form-control" id="quickSearchInput" placeholder="Quick search by name or email address...">
                                    </div>
                                    <div id="activeFiltersChips" class="d-flex flex-wrap gap-1"></div>
                                </div>
                                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#filtersPanel">
                                    <i class="fas fa-filter me-1"></i> Filters
                                </button>
                            </div>
                            
                            <div class="table-container" id="addressesTableContainer">
                                <div class="table-responsive">
                                    <table class="table email-sms-table mb-0">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Originating Emails</th>
                                                <th>Type</th>
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
                        
                        <div class="tab-pane fade" id="contact-lists" role="tabpanel">
                            <p class="text-muted mb-3">Map email addresses to Contact Book Lists. When an email is received, SMS is sent to all recipients in the linked Contact List.</p>
                            
                            <div class="collapse mb-3" id="clFiltersPanel">
                                <div class="card card-body border-0 rounded-3" style="background-color: #f0ebf8;">
                                    <div class="row g-3 align-items-start">
                                        <div class="col-12 col-lg-6">
                                            <label class="form-label small fw-bold">Date Created</label>
                                            <div class="d-flex gap-2 align-items-center">
                                                <input type="date" class="form-control form-control-sm" id="clFilterDateFrom">
                                                <span class="text-muted small">to</span>
                                                <input type="date" class="form-control form-control-sm" id="clFilterDateTo">
                                            </div>
                                            <div class="d-flex flex-wrap gap-1 mt-2">
                                                <button type="button" class="btn btn-outline-primary btn-xs date-preset-btn cl-date-preset" data-preset="7days">Last 7 Days</button>
                                                <button type="button" class="btn btn-outline-primary btn-xs date-preset-btn cl-date-preset" data-preset="30days">Last 30 Days</button>
                                                <button type="button" class="btn btn-outline-primary btn-xs date-preset-btn cl-date-preset" data-preset="thismonth">This Month</button>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-4 col-lg-3">
                                            <label class="form-label small fw-bold">Contact List</label>
                                            <select class="form-select form-select-sm" id="clFilterContactList">
                                                <option value="">All Contact Lists</option>
                                                <option value="NHS Patients">NHS Patients</option>
                                                <option value="Pharmacy Patients">Pharmacy Patients</option>
                                                <option value="Appointment List">Appointment List</option>
                                                <option value="Newsletter Subscribers">Newsletter Subscribers</option>
                                                <option value="Emergency Contacts">Emergency Contacts</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex justify-content-end gap-2 mt-3">
                                        <button type="button" class="btn btn-outline-secondary btn-sm" id="btnResetClFilters">Reset Filters</button>
                                        <button type="button" class="btn btn-primary btn-sm" id="btnApplyClFilters">Apply Filters</button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="input-group" style="width: 280px;">
                                        <span class="input-group-text bg-transparent"><i class="fas fa-search"></i></span>
                                        <input type="text" class="form-control" id="clQuickSearchInput" placeholder="Quick search by name...">
                                    </div>
                                    <div class="form-check form-switch ms-2">
                                        <input class="form-check-input" type="checkbox" id="clShowArchived">
                                        <label class="form-check-label small text-muted" for="clShowArchived">Show archived</label>
                                    </div>
                                    <div id="clActiveFiltersChips" class="d-flex flex-wrap gap-1"></div>
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#clFiltersPanel">
                                        <i class="fas fa-filter me-1"></i> Filters
                                    </button>
                                    <button type="button" class="btn btn-primary btn-sm" id="btnCreateContactListMapping">
                                        <i class="fas fa-plus me-1"></i> Create
                                    </button>
                                </div>
                            </div>
                            
                            <div class="table-container" id="contactListsTableContainer">
                                <div class="table-responsive">
                                    <table class="table email-sms-table mb-0">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Subaccount</th>
                                                <th>Allowed Sender Emails</th>
                                                <th>Target Lists</th>
                                                <th>Opt-out Lists</th>
                                                <th>Created</th>
                                                <th>Last Updated</th>
                                                <th class="text-end">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="contactListsTableBody">
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            <div class="empty-state" id="emptyStateContactLists" style="display: none;">
                                <div class="empty-state-icon">
                                    <i class="fas fa-link"></i>
                                </div>
                                <h4>No Contact List Mappings</h4>
                                <p>Create a mapping to link an Email-to-SMS Address to a Contact Book List.</p>
                                <button class="btn btn-primary" id="btnCreateMappingEmpty">
                                    <i class="fas fa-plus me-1"></i> Create Mapping
                                </button>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div class="text-muted small">
                                    Showing <span id="clShowingCount">0</span> of <span id="clTotalCount">0</span> mappings
                                </div>
                                <nav>
                                    <ul class="pagination pagination-sm mb-0" id="contactListsPagination">
                                    </ul>
                                </nav>
                            </div>
                        </div>
                        
                        <div class="tab-pane fade" id="reporting-groups" role="tabpanel">
                            <p class="text-muted mb-3">Reporting Groups are for reporting and billing attribution only. They do not control recipients or content.</p>
                            
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
                                    </div>
                                    
                                    <div class="d-flex justify-content-end gap-2 mt-3">
                                        <button type="button" class="btn btn-outline-secondary btn-sm" id="btnResetRgFilters">Reset Filters</button>
                                        <button type="button" class="btn btn-primary btn-sm" id="btnApplyRgFilters">Apply Filters</button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="input-group" style="width: 280px;">
                                        <span class="input-group-text bg-transparent"><i class="fas fa-search"></i></span>
                                        <input type="text" class="form-control" id="rgQuickSearchInput" placeholder="Quick search by group name...">
                                    </div>
                                    <div id="rgActiveFiltersChips" class="d-flex flex-wrap gap-1"></div>
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#rgFiltersPanel">
                                        <i class="fas fa-filter me-1"></i> Filters
                                    </button>
                                    <button type="button" class="btn btn-primary btn-sm" id="btnCreateReportingGroup">
                                        <i class="fas fa-plus me-1"></i> Create
                                    </button>
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
                                    <i class="fas fa-plus me-1"></i> Create
                                </button>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div class="text-muted small">
                                    Showing <span id="rgShowingCount">0</span> of <span id="rgTotalCount">0</span> groups
                                </div>
                            </div>
                        </div>
                        
                        <div class="tab-pane fade" id="standard" role="tabpanel">
                            <p class="text-muted mb-3">Create Standard Email-to-SMS setups to send messages directly from email without mapping to Contact Lists.</p>
                            
                            <div class="collapse mb-3" id="stdFiltersPanel">
                                <div class="card card-body border-0 rounded-3" style="background-color: #f0ebf8;">
                                    <div class="row g-3 align-items-start">
                                        <div class="col-12 col-lg-6">
                                            <label class="form-label small fw-bold">Date Created</label>
                                            <div class="d-flex gap-2 align-items-center">
                                                <input type="date" class="form-control form-control-sm" id="stdFilterDateFrom">
                                                <span class="text-muted small">to</span>
                                                <input type="date" class="form-control form-control-sm" id="stdFilterDateTo">
                                            </div>
                                            <div class="d-flex flex-wrap gap-1 mt-2">
                                                <button type="button" class="btn btn-outline-primary btn-xs date-preset-btn std-date-preset" data-preset="7days">Last 7 Days</button>
                                                <button type="button" class="btn btn-outline-primary btn-xs date-preset-btn std-date-preset" data-preset="30days">Last 30 Days</button>
                                                <button type="button" class="btn btn-outline-primary btn-xs date-preset-btn std-date-preset" data-preset="thismonth">This Month</button>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-4 col-lg-3">
                                            <label class="form-label small fw-bold">Subaccount</label>
                                            <select class="form-select form-select-sm" id="stdFilterSubaccount">
                                                <option value="">All Subaccounts</option>
                                                <option value="main">Main Account</option>
                                                <option value="marketing">Marketing Team</option>
                                                <option value="support">Support Team</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex justify-content-end gap-2 mt-3">
                                        <button type="button" class="btn btn-outline-secondary btn-sm" id="btnResetStdFilters">Reset Filters</button>
                                        <button type="button" class="btn btn-primary btn-sm" id="btnApplyStdFilters">Apply Filters</button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="input-group" style="width: 280px;">
                                        <span class="input-group-text bg-transparent"><i class="fas fa-search"></i></span>
                                        <input type="text" class="form-control" id="stdQuickSearchInput" placeholder="Quick search by name...">
                                    </div>
                                    <div class="form-check form-switch ms-2">
                                        <input class="form-check-input" type="checkbox" id="stdShowArchived">
                                        <label class="form-check-label small text-muted" for="stdShowArchived">Show archived</label>
                                    </div>
                                    <div id="stdActiveFiltersChips" class="d-flex flex-wrap gap-1"></div>
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#stdFiltersPanel">
                                        <i class="fas fa-filter me-1"></i> Filters
                                    </button>
                                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createStandardModal">
                                        <i class="fas fa-plus me-1"></i> Create
                                    </button>
                                </div>
                            </div>
                            
                            <div class="table-container" id="standardSmsTableContainer">
                                <div class="table-responsive">
                                    <table class="email-sms-table">
                                        <thead>
                                            <tr>
                                                <th style="width: 18%;">Name</th>
                                                <th style="width: 12%;">Subaccount</th>
                                                <th style="width: 26%;">Allowed Sender Emails</th>
                                                <th style="width: 10%;">Status</th>
                                                <th style="width: 10%;">Created</th>
                                                <th style="width: 10%;">Last Updated</th>
                                                <th class="text-end" style="width: 14%;">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="standardSmsTableBody">
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            <div class="empty-state" id="emptyStateStandardSms" style="display: none;">
                                <div class="empty-state-icon">
                                    <i class="fas fa-envelope-open-text"></i>
                                </div>
                                <h4>No Standard Email-to-SMS Setups</h4>
                                <p>Create a Standard Email-to-SMS setup to send messages directly from email without mapping to Contact Lists.</p>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createStandardModal">
                                    <i class="fas fa-plus me-1"></i> Create Standard Email-to-SMS
                                </button>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div class="text-muted small">
                                    Showing <span id="stdShowingCount">0</span> of <span id="stdTotalCount">0</span> setups
                                </div>
                            </div>
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
                <span class="detail-label">Primary Email Address</span>
                <span class="detail-value">
                    <code id="drawerPrimaryEmail">-</code>
                    <button class="copy-btn" id="copyPrimaryEmailBtn"><i class="fas fa-copy"></i></button>
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Type</span>
                <span class="detail-value" id="drawerType">-</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Description</span>
                <span class="detail-value" id="drawerDescription">-</span>
            </div>
        </div>
        
        <div class="mb-4" id="drawerAdditionalEmailsSection" style="display: none;">
            <h6 class="text-muted mb-3">Additional Originating Emails</h6>
            <div id="drawerAdditionalEmails">-</div>
        </div>
        
        <div class="mb-4">
            <h6 class="text-muted mb-3">Messaging Settings</h6>
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
                        <label class="form-label">Type <span class="text-danger">*</span></label>
                        <select class="form-select" id="createType">
                            <option value="">Select Type...</option>
                            <option value="Standard">Standard</option>
                            <option value="Contact List">Contact List</option>
                        </select>
                        <div class="form-text">Standard setup or Contact List-based delivery.</div>
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
                <div class="alert alert-info small mb-3" style="background-color: rgba(48, 101, 208, 0.1); border: none;">
                    <i class="fas fa-info-circle me-2"></i>
                    Reporting Groups are for billing and reporting attribution only. Each Email-to-SMS Address can only belong to one Reporting Group.
                </div>
                <div class="mb-3">
                    <label class="form-label">Group Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="rgName" placeholder="e.g., Patient Communications">
                    <div class="invalid-feedback">Group name is required.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" id="rgDescription" rows="2" placeholder="Optional description..."></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Assign Email-to-SMS Address <span class="text-danger">*</span></label>
                    <select class="form-select" id="rgAssignAddress">
                        <option value="">Select an address...</option>
                    </select>
                    <div class="invalid-feedback" id="rgAddressError">Please select an Email-to-SMS Address.</div>
                    <div class="form-text">Select an Email-to-SMS Address to assign to this Reporting Group.</div>
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
                <h5 class="modal-title" id="suspendModalTitle">Suspend Email-to-SMS Address</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="suspendModalMessage">Are you sure you want to suspend <strong id="suspendAddressName"></strong>?</p>
                <p class="text-muted small" id="suspendModalDescription">While suspended, emails sent to this address will not trigger SMS messages. You can reactivate it at any time.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="btnConfirmSuspend">
                    <i class="fas fa-pause me-1" id="suspendModalIcon"></i> <span id="suspendModalAction">Suspend</span>
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

<div class="drawer-backdrop" id="stdDrawerBackdrop"></div>
<div class="drawer" id="stdDetailsDrawer">
    <div class="drawer-header">
        <h5>Standard Email-to-SMS Details</h5>
        <button type="button" class="btn-close" id="stdCloseDrawerBtn"></button>
    </div>
    <div class="drawer-body">
        <div class="mb-4">
            <h6 class="text-muted mb-3">General</h6>
            <div class="detail-row">
                <span class="detail-label">Name</span>
                <span class="detail-value" id="stdDrawerName">-</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Description</span>
                <span class="detail-value" id="stdDrawerDescription">-</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Subaccount</span>
                <span class="detail-value" id="stdDrawerSubaccount">-</span>
            </div>
        </div>
        
        <div class="mb-4">
            <h6 class="text-muted mb-3">Email Settings</h6>
            <div class="detail-row">
                <span class="detail-label">Allowed Sender Emails</span>
                <span class="detail-value" id="stdDrawerAllowedEmails">-</span>
            </div>
        </div>
        
        <div class="mb-4">
            <h6 class="text-muted mb-3">Message Settings</h6>
            <div class="detail-row">
                <span class="detail-label">SenderID</span>
                <span class="detail-value" id="stdDrawerSenderId">-</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Subject as SenderID</span>
                <span class="detail-value" id="stdDrawerSubjectAsSenderId">-</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Multiple SMS</span>
                <span class="detail-value" id="stdDrawerMultipleSms">-</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Delivery Reports</span>
                <span class="detail-value" id="stdDrawerDeliveryReports">-</span>
            </div>
            <div class="detail-row" id="stdDrawerDeliveryEmailRow" style="display: none;">
                <span class="detail-label">Delivery Email</span>
                <span class="detail-value" id="stdDrawerDeliveryEmail">-</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Signature Filter</span>
                <span class="detail-value" id="stdDrawerSignatureFilter">-</span>
            </div>
        </div>
        
        <div class="mb-4">
            <h6 class="text-muted mb-3">Dates</h6>
            <div class="detail-row">
                <span class="detail-label">Created</span>
                <span class="detail-value" id="stdDrawerCreated">-</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Last Updated</span>
                <span class="detail-value" id="stdDrawerLastUpdated">-</span>
            </div>
        </div>
    </div>
    <div class="drawer-footer">
        <button type="button" class="btn btn-outline-secondary" id="stdDrawerEditBtn">
            <i class="fas fa-edit me-1"></i> Edit
        </button>
    </div>
</div>

<div class="drawer-backdrop" id="clmDrawerBackdrop"></div>
<div class="drawer" id="clmDetailsDrawer">
    <div class="drawer-header">
        <h5 class="drawer-title" id="clmDrawerTitle">Contact List Setup Details</h5>
        <button type="button" class="drawer-close" id="clmDrawerCloseBtn">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <div class="drawer-body">
        <div class="mb-4">
            <h6 class="text-muted mb-3">General</h6>
            <div class="detail-row">
                <span class="detail-label">Name</span>
                <span class="detail-value" id="clmDrawerName">-</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Description</span>
                <span class="detail-value" id="clmDrawerDescription">-</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Subaccount</span>
                <span class="detail-value" id="clmDrawerSubaccount">-</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Status</span>
                <span class="detail-value" id="clmDrawerStatus">-</span>
            </div>
        </div>
        
        <div class="mb-4">
            <h6 class="text-muted mb-3">Email Settings</h6>
            <div class="detail-row">
                <span class="detail-label">Allowed Sender Emails</span>
                <span class="detail-value" id="clmDrawerAllowedSenders">-</span>
            </div>
        </div>
        
        <div class="mb-4">
            <h6 class="text-muted mb-3">Contact Book</h6>
            <div class="detail-row">
                <span class="detail-label">Target Lists</span>
                <span class="detail-value" id="clmDrawerTargetLists">-</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Opt-out Lists</span>
                <span class="detail-value" id="clmDrawerOptOutLists">-</span>
            </div>
        </div>
        
        <div class="mb-4">
            <h6 class="text-muted mb-3">Dates</h6>
            <div class="detail-row">
                <span class="detail-label">Created</span>
                <span class="detail-value" id="clmDrawerCreated">-</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Last Updated</span>
                <span class="detail-value" id="clmDrawerLastUpdated">-</span>
            </div>
        </div>
    </div>
    <div class="drawer-footer">
        <button type="button" class="btn btn-outline-secondary" id="clmDrawerEditBtn">
            <i class="fas fa-edit me-1"></i> Edit
        </button>
    </div>
</div>

<div class="modal fade" id="clmArchiveModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-semibold">
                    <i class="fas fa-archive me-2 text-warning"></i>Archive Setup
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">Are you sure you want to archive <strong id="clmArchiveName"></strong>?</p>
                <p class="text-muted small mt-2 mb-0">Archived setups will no longer process incoming emails. You can view archived setups by enabling "Show archived" and unarchive them at any time.</p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="clmArchiveConfirmBtn">
                    <i class="fas fa-archive me-1"></i> Archive
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="createContactListMappingModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-semibold"><i class="fas fa-link me-2 text-primary"></i>Create Email-to-SMS – Contact List</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="container" style="max-width: 800px;">
                    
                    <div class="create-modal-section">
                        <div class="create-modal-section-header">
                            <h6><i class="fas fa-info-circle me-2 text-primary"></i>General</h6>
                        </div>
                        <div class="create-modal-section-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="clmCreateName" placeholder="e.g., NHS Patient Notifications">
                                    <div class="invalid-feedback">Name is required.</div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Description</label>
                                    <input type="text" class="form-control" id="clmCreateDescription" placeholder="Optional description...">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Subaccount <span class="text-danger">*</span></label>
                                    <select class="form-select" id="clmCreateSubaccount">
                                        <option value="">Select subaccount...</option>
                                        <option value="main">Main Account</option>
                                        <option value="marketing">Marketing Team</option>
                                        <option value="support">Support Team</option>
                                    </select>
                                    <div class="invalid-feedback">Subaccount is required.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="create-modal-section">
                        <div class="create-modal-section-header">
                            <h6><i class="fas fa-envelope-open-text me-2 text-primary"></i>Email Settings (Sender Allowlist)</h6>
                        </div>
                        <div class="create-modal-section-body">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Allowed Sender Emails</label>
                                <p class="text-muted small mb-2">Only emails from these addresses will trigger SMS. Leave empty to allow all senders. Supports wildcard domains (e.g., *@company.com).</p>
                                <div class="input-group mb-2">
                                    <input type="email" class="form-control" id="clmCreateEmailInput" placeholder="email@example.com or *@domain.com">
                                    <button class="btn btn-primary" type="button" id="clmAddEmailBtn">
                                        <i class="fas fa-plus me-1"></i> Add
                                    </button>
                                </div>
                                <div class="invalid-feedback" id="clmEmailError" style="display: none;">Invalid email format.</div>
                                <div id="clmEmailTagsContainer" class="email-tags-container"></div>
                                <div class="d-flex justify-content-between align-items-center mt-1">
                                    <small class="text-muted"><span id="clmEmailCount">0</span> email(s) added</small>
                                    <button type="button" class="btn btn-link btn-sm text-danger p-0" id="clmClearAllEmails" style="display: none;">
                                        <i class="fas fa-trash-alt me-1"></i> Clear All
                                    </button>
                                </div>
                            </div>
                            
                            <div id="clmWildcardWarning" class="alert alert-warning d-none" style="font-size: 0.85rem;">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Warning:</strong> Wildcard domains are less secure and may result in unintended messages being sent.
                            </div>
                        </div>
                    </div>
                    
                    <div class="create-modal-section">
                        <div class="create-modal-section-header">
                            <h6><i class="fas fa-address-book me-2 text-primary"></i>Contact Book (Recipient Targeting)</h6>
                        </div>
                        <div class="create-modal-section-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Select Contact List(s) <span class="text-danger">*</span></label>
                                    <p class="text-muted small mb-2">Recipients from selected lists will receive SMS when an email is received.</p>
                                    <div class="dropdown multiselect-dropdown w-100" id="clmContactListsDropdown">
                                        <button class="btn dropdown-toggle w-100 text-start d-flex justify-content-between align-items-center" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" style="background-color: #fff; border: 1px solid #ced4da; color: #495057;">
                                            <span class="dropdown-label">Select list(s)...</span>
                                        </button>
                                        <div class="dropdown-menu w-100 p-2" style="max-height: 300px; overflow-y: auto;">
                                            <div class="d-flex justify-content-between mb-2 border-bottom pb-2">
                                                <a href="#" class="small text-decoration-none clm-select-all-lists">Select All</a>
                                                <a href="#" class="small text-decoration-none clm-clear-all-lists">Clear</a>
                                            </div>
                                            <div class="small text-muted mb-1 fw-bold">Static Lists</div>
                                            <div class="form-check"><input class="form-check-input clm-contact-list-cb" type="checkbox" value="static-nhs-patients" id="clmList1"><label class="form-check-label small" for="clmList1">NHS Patients (1,245)</label></div>
                                            <div class="form-check"><input class="form-check-input clm-contact-list-cb" type="checkbox" value="static-pharmacy" id="clmList2"><label class="form-check-label small" for="clmList2">Pharmacy Patients (892)</label></div>
                                            <div class="form-check"><input class="form-check-input clm-contact-list-cb" type="checkbox" value="static-appointments" id="clmList3"><label class="form-check-label small" for="clmList3">Appointment List (2,156)</label></div>
                                            <div class="form-check"><input class="form-check-input clm-contact-list-cb" type="checkbox" value="static-newsletter" id="clmList4"><label class="form-check-label small" for="clmList4">Newsletter Subscribers (5,678)</label></div>
                                            <div class="dropdown-divider"></div>
                                            <div class="small text-muted mb-1 fw-bold">Dynamic Lists</div>
                                            <div class="form-check"><input class="form-check-input clm-contact-list-cb" type="checkbox" value="dynamic-active-patients" id="clmList5"><label class="form-check-label small" for="clmList5"><i class="fas fa-sync-alt me-1 text-info"></i>Active Patients (var)</label></div>
                                            <div class="form-check"><input class="form-check-input clm-contact-list-cb" type="checkbox" value="dynamic-recent-orders" id="clmList6"><label class="form-check-label small" for="clmList6"><i class="fas fa-sync-alt me-1 text-info"></i>Recent Orders (var)</label></div>
                                            <div class="form-check"><input class="form-check-input clm-contact-list-cb" type="checkbox" value="dynamic-birthdays" id="clmList7"><label class="form-check-label small" for="clmList7"><i class="fas fa-sync-alt me-1 text-info"></i>Upcoming Birthdays (var)</label></div>
                                        </div>
                                    </div>
                                    <div class="invalid-feedback" id="clmContactListsError">At least one Contact List is required.</div>
                                    <div id="clmSelectedListsDisplay" class="mt-2"></div>
                                </div>
                                
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Opt-out List(s)</label>
                                    <p class="text-muted small mb-2">Contacts in selected opt-out lists will not receive SMS.</p>
                                    <div class="dropdown multiselect-dropdown w-100" id="clmOptOutDropdown">
                                        <button class="btn dropdown-toggle w-100 text-start d-flex justify-content-between align-items-center" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" style="background-color: #fff; border: 1px solid #ced4da; color: #495057;">
                                            <span class="dropdown-label">No opt-out list</span>
                                        </button>
                                        <div class="dropdown-menu w-100 p-2" style="max-height: 250px; overflow-y: auto;">
                                            <div class="d-flex justify-content-between mb-2 border-bottom pb-2">
                                                <a href="#" class="small text-decoration-none clm-select-all-optouts">Select All</a>
                                                <a href="#" class="small text-decoration-none clm-clear-all-optouts">Clear</a>
                                            </div>
                                            <div class="form-check"><input class="form-check-input clm-optout-cb" type="checkbox" value="no" id="clmOptNo" checked><label class="form-check-label small" for="clmOptNo">No opt-out list</label></div>
                                            <div class="dropdown-divider"></div>
                                            <div class="form-check"><input class="form-check-input clm-optout-cb" type="checkbox" value="global-optout" id="clmOpt1"><label class="form-check-label small" for="clmOpt1">Global Opt-Out (543)</label></div>
                                            <div class="form-check"><input class="form-check-input clm-optout-cb" type="checkbox" value="marketing-optout" id="clmOpt2"><label class="form-check-label small" for="clmOpt2">Marketing Opt-Out (1,892)</label></div>
                                            <div class="form-check"><input class="form-check-input clm-optout-cb" type="checkbox" value="sms-optout" id="clmOpt3"><label class="form-check-label small" for="clmOpt3">SMS Opt-Out (267)</label></div>
                                            <div class="form-check"><input class="form-check-input clm-optout-cb" type="checkbox" value="dnc-list" id="clmOpt4"><label class="form-check-label small" for="clmOpt4">Do Not Contact (89)</label></div>
                                        </div>
                                    </div>
                                    <div id="clmSelectedOptOutsDisplay" class="mt-2"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="create-modal-section">
                        <div class="create-modal-section-header">
                            <h6><i class="fas fa-sms me-2 text-primary"></i>Message Settings</h6>
                        </div>
                        <div class="create-modal-section-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">SenderID <span class="text-danger">*</span></label>
                                    <select class="form-select" id="clmCreateSenderId">
                                        <option value="">Select SenderID...</option>
                                        <option value="QuickSMS">QuickSMS</option>
                                        <option value="ALERTS">ALERTS</option>
                                        <option value="NHS">NHS</option>
                                        <option value="INFO">INFO</option>
                                        <option value="Pharmacy">Pharmacy</option>
                                    </select>
                                    <small class="text-muted">Only approved/live SenderIDs are shown.</small>
                                    <div class="invalid-feedback">SenderID is required.</div>
                                </div>
                                
                                <div class="col-md-6" id="clmSubjectAsSenderIdGroup" style="display: none;">
                                    <label class="form-label fw-semibold">Subject as SenderID</label>
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" id="clmCreateSubjectAsSenderId">
                                        <label class="form-check-label" for="clmCreateSubjectAsSenderId">
                                            Extract SenderID from email subject
                                        </label>
                                    </div>
                                    <small class="text-muted">When enabled, the SenderID is extracted from the email subject line.</small>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Enable Multiple SMS</label>
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" id="clmCreateMultipleSms">
                                        <label class="form-check-label" for="clmCreateMultipleSms">
                                            Allow multipart SMS messages
                                        </label>
                                    </div>
                                    <small class="text-muted">Messages over 160 characters will be sent as multiple parts.</small>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Send Delivery Reports</label>
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" id="clmCreateDeliveryReports">
                                        <label class="form-check-label" for="clmCreateDeliveryReports">
                                            Enable delivery report notifications
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="col-md-6" id="clmDeliveryEmailGroup" style="display: none;">
                                    <label class="form-label fw-semibold">Delivery Reports Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="clmCreateDeliveryEmail" placeholder="reports@yourcompany.com">
                                    <div class="invalid-feedback" id="clmDeliveryEmailError">Valid email address required for delivery reports.</div>
                                </div>
                                
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Filter Content (Signature Removal)</label>
                                    <textarea class="form-control" id="clmCreateSignatureFilter" rows="3" placeholder="e.g., --\n.*\nSent from.*"></textarea>
                                    <div class="invalid-feedback" id="clmSignatureFilterError">Invalid regex pattern</div>
                                    <small class="text-muted">Remove matching content from inbound emails (e.g., signatures). Regex supported. One pattern per line.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancel
                </button>
                <button type="button" class="btn btn-primary" id="btnSaveContactListMapping">
                    <i class="fas fa-check me-1"></i> Save
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="stdArchiveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Archive Standard Email-to-SMS</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to archive <strong id="stdArchiveName"></strong>?</p>
                <div class="alert alert-warning">
                    <i class="fas fa-info-circle me-2"></i>
                    Archived setups will no longer process incoming emails. You can view archived items by enabling "Show archived" in the table.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="btnConfirmStdArchive">
                    <i class="fas fa-archive me-1"></i> Archive
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="createStandardModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header py-3" style="background: var(--primary); color: #fff;">
                <h5 class="modal-title"><i class="fas fa-envelope me-2"></i>Create Standard Email-to-SMS</h5>
                <div class="d-flex align-items-center gap-3">
                    <span class="std-wizard-autosave small" id="stdWizardAutosave">
                        <i class="fas fa-cloud me-1"></i><span id="stdWizardAutosaveText">Draft saved</span>
                    </span>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body p-4">
                <div class="container" style="max-width: 900px;">
                    <div id="stdWizard" class="std-wizard">
                        <ul class="nav std-wizard-nav" id="stdWizardNav">
                            <li class="std-wizard-step">
                                <a href="#stdStep1" class="std-wizard-link active" data-step="0">
                                    <span class="std-wizard-number">1</span>
                                    <span class="std-wizard-label">General</span>
                                </a>
                            </li>
                            <li class="std-wizard-step">
                                <a href="#stdStep2" class="std-wizard-link" data-step="1">
                                    <span class="std-wizard-number">2</span>
                                    <span class="std-wizard-label">Email Settings</span>
                                </a>
                            </li>
                            <li class="std-wizard-step">
                                <a href="#stdStep3" class="std-wizard-link" data-step="2">
                                    <span class="std-wizard-number">3</span>
                                    <span class="std-wizard-label">Message Settings</span>
                                </a>
                            </li>
                        </ul>
                        
                        <div class="std-wizard-content">
                            <div id="stdStep1" class="std-wizard-pane active">
                                <div class="alert alert-pastel-primary mb-4">
                                    <strong>Step 1: General</strong> - Define the setup name, description, and assign to a subaccount.
                                </div>
                                
                                <div class="row">
                                    <div class="col-lg-8">
                                        <div class="mb-3">
                                            <label class="form-label">Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="stdWizardName" placeholder="e.g., Appointment Reminders" maxlength="50">
                                            <div class="invalid-feedback" id="stdWizardNameError">Please enter a name.</div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Description</label>
                                            <textarea class="form-control" id="stdWizardDescription" rows="2" placeholder="Brief description of this Email-to-SMS setup..." maxlength="200"></textarea>
                                            <small class="text-muted"><span id="stdWizardDescCharCount">0</span>/200 characters</small>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Sub-Account <span class="text-danger">*</span></label>
                                            <select class="form-select" id="stdWizardSubaccount">
                                                <option value="">Select sub-account...</option>
                                                <option value="main">Main Account</option>
                                                <option value="marketing">Marketing Team</option>
                                                <option value="support">Support Team</option>
                                            </select>
                                            <div class="invalid-feedback">Please select a sub-account.</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div id="stdStep2" class="std-wizard-pane">
                                <div class="alert alert-pastel-primary mb-4">
                                    <strong>Step 2: Email Settings</strong> - Configure allowed sender emails. <span class="badge bg-pastel-primary">Optional</span>
                                </div>
                                
                                <div class="row">
                                    <div class="col-lg-10">
                                        <div class="mb-3">
                                            <label class="form-label fw-medium">Allowed Sender Email(s)</label>
                                            <p class="text-muted small mb-2">Only emails from these addresses will trigger SMS. Leave empty to allow all senders.</p>
                                            <div class="input-group mb-2">
                                                <input type="text" class="form-control" id="stdWizardEmailInput" placeholder="user@domain.com or *@domain.com">
                                                <button class="btn btn-primary" type="button" id="stdWizardAddEmailBtn">
                                                    <i class="fas fa-plus me-1"></i> Add
                                                </button>
                                            </div>
                                            <div class="invalid-feedback" id="stdWizardEmailError" style="display: none;">Invalid email format.</div>
                                            <small class="text-muted">Supports single emails (user@domain.com) or wildcard domains (*@domain.com).</small>
                                        </div>
                                        
                                        <div id="stdWizardEmailTagsContainer" class="email-tags-container mb-3"></div>
                                        
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted"><span id="stdWizardEmailCount">0</span> email(s) added</small>
                                            <button type="button" class="btn btn-link btn-sm text-danger p-0" id="stdWizardClearAllEmails" style="display: none;">
                                                <i class="fas fa-trash-alt me-1"></i> Clear All
                                            </button>
                                        </div>
                                        
                                        <div id="stdWizardWildcardWarning" class="alert alert-warning d-none mt-3">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            <strong>Warning:</strong> Wildcard email addresses are less secure and may result in unintended messages being sent.
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div id="stdStep3" class="std-wizard-pane">
                                <div class="alert alert-pastel-primary mb-4">
                                    <strong>Step 3: Message Settings</strong> - Configure SenderID, delivery options, and content processing.
                                </div>
                                
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">SenderID <span class="text-danger">*</span></label>
                                        <select class="form-select" id="stdWizardSenderId">
                                            <option value="">Select SenderID...</option>
                                        </select>
                                        <small class="text-muted">Only approved/live SenderIDs are shown.</small>
                                        <div class="invalid-feedback">Please select a SenderID.</div>
                                    </div>
                                    
                                    <div class="col-md-6" id="stdWizardSubjectAsSenderIdGroup">
                                        <label class="form-label">Subject as SenderID</label>
                                        <div class="form-check form-switch mt-2">
                                            <input class="form-check-input" type="checkbox" id="stdWizardSubjectAsSenderId">
                                            <label class="form-check-label" for="stdWizardSubjectAsSenderId">
                                                Extract SenderID from email subject
                                            </label>
                                        </div>
                                        <small class="text-muted">Overrides selected SenderID with subject line content.</small>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label class="form-label">Enable Multiple SMS</label>
                                        <div class="form-check form-switch mt-2">
                                            <input class="form-check-input" type="checkbox" id="stdWizardMultipleSms">
                                            <label class="form-check-label" for="stdWizardMultipleSms">
                                                Allow multipart SMS messages
                                            </label>
                                        </div>
                                        <small class="text-muted">Messages over 160 characters sent as multiple parts.</small>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label class="form-label">Send Delivery Reports</label>
                                        <div class="form-check form-switch mt-2">
                                            <input class="form-check-input" type="checkbox" id="stdWizardDeliveryReports">
                                            <label class="form-check-label" for="stdWizardDeliveryReports">
                                                Enable delivery report notifications
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6" id="stdWizardDeliveryEmailGroup" style="display: none;">
                                        <label class="form-label">Delivery Reports Email <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" id="stdWizardDeliveryEmail" placeholder="reports@yourcompany.com">
                                        <small class="text-muted">Receive delivery status notifications.</small>
                                        <div class="invalid-feedback" id="stdWizardDeliveryEmailError">Valid email required.</div>
                                    </div>
                                    
                                    <div class="col-12">
                                        <label class="form-label">Filter Content (Signature Removal)</label>
                                        <textarea class="form-control" id="stdWizardSignatureFilter" rows="3" placeholder="e.g., --\n.*\nSent from.*"></textarea>
                                        <div class="invalid-feedback" id="stdWizardSignatureFilterError">Invalid regex pattern.</div>
                                        <small class="text-muted">Remove matching content from emails. Regex patterns supported, one pattern per line.</small>
                                    </div>
                                </div>
                                
                                <hr class="my-4">
                                
                                <h5 class="mb-3"><i class="fas fa-check-circle me-2 text-primary"></i>Review Configuration</h5>
                                
                                <div class="std-wizard-review">
                                    <div class="std-wizard-review-section">
                                        <h6><i class="fas fa-info-circle me-2"></i>General</h6>
                                        <div class="std-wizard-review-row">
                                            <span class="std-wizard-review-label">Name</span>
                                            <span class="std-wizard-review-value" id="stdWizardReviewName">-</span>
                                        </div>
                                        <div class="std-wizard-review-row">
                                            <span class="std-wizard-review-label">Description</span>
                                            <span class="std-wizard-review-value" id="stdWizardReviewDescription">-</span>
                                        </div>
                                        <div class="std-wizard-review-row">
                                            <span class="std-wizard-review-label">Subaccount</span>
                                            <span class="std-wizard-review-value" id="stdWizardReviewSubaccount">-</span>
                                        </div>
                                    </div>
                                    
                                    <div class="std-wizard-review-section">
                                        <h6><i class="fas fa-envelope me-2"></i>Email Settings</h6>
                                        <div class="std-wizard-review-row">
                                            <span class="std-wizard-review-label">Allowed Senders</span>
                                            <span class="std-wizard-review-value" id="stdWizardReviewAllowedSenders">All senders allowed</span>
                                        </div>
                                    </div>
                                    
                                    <div class="std-wizard-review-section">
                                        <h6><i class="fas fa-sms me-2"></i>Message Settings</h6>
                                        <div class="std-wizard-review-row">
                                            <span class="std-wizard-review-label">SenderID</span>
                                            <span class="std-wizard-review-value" id="stdWizardReviewSenderId">-</span>
                                        </div>
                                        <div class="std-wizard-review-row" id="stdWizardReviewSubjectAsSenderIdRow">
                                            <span class="std-wizard-review-label">Subject as SenderID</span>
                                            <span class="std-wizard-review-value" id="stdWizardReviewSubjectAsSenderId">No</span>
                                        </div>
                                        <div class="std-wizard-review-row">
                                            <span class="std-wizard-review-label">Multiple SMS</span>
                                            <span class="std-wizard-review-value" id="stdWizardReviewMultipleSms">No</span>
                                        </div>
                                        <div class="std-wizard-review-row">
                                            <span class="std-wizard-review-label">Delivery Reports</span>
                                            <span class="std-wizard-review-value" id="stdWizardReviewDeliveryReports">No</span>
                                        </div>
                                        <div class="std-wizard-review-row">
                                            <span class="std-wizard-review-label">Content Filter</span>
                                            <span class="std-wizard-review-value" id="stdWizardReviewContentFilter">None</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="std-wizard-toolbar">
                            <button type="button" class="btn btn-outline-secondary" id="stdWizardBtnPrev" disabled>
                                <i class="fas fa-arrow-left me-1"></i> Back
                            </button>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-primary" id="stdWizardBtnSaveDraft">
                                    <i class="fas fa-save me-1"></i> Save as Draft
                                </button>
                                <button type="button" class="btn btn-primary" id="stdWizardBtnNext">
                                    Next <i class="fas fa-arrow-right ms-1"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.alert-pastel-primary {
    background-color: rgba(136, 108, 192, 0.1);
    border-color: rgba(136, 108, 192, 0.2);
    color: #5a4a7a;
}
.bg-pastel-primary {
    background-color: rgba(136, 108, 192, 0.2) !important;
    color: #5a4a7a !important;
}
.std-wizard-autosave {
    opacity: 0.9;
}
.std-wizard-autosave.saving {
    color: #ffc107;
}
.std-wizard-autosave.saved {
    color: #d4edda;
}
.std-wizard-nav {
    display: flex;
    justify-content: center;
    list-style: none;
    padding: 0;
    margin: 0 0 2rem 0;
    gap: 0;
}
.std-wizard-step {
    flex: 1;
    max-width: 180px;
    position: relative;
}
.std-wizard-link {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-decoration: none;
    color: #6c757d;
    padding: 0;
    cursor: pointer;
}
.std-wizard-link:after {
    position: absolute;
    top: 1.5rem;
    left: 50%;
    height: 3px;
    background: #e9ecef;
    content: "";
    z-index: 0;
    width: 100%;
}
.std-wizard-step:last-child .std-wizard-link:after {
    content: none;
}
.std-wizard-number {
    width: 3rem;
    height: 3rem;
    border: 2px solid var(--primary, #886CC0);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.125rem;
    font-weight: 500;
    background: #fff;
    color: var(--primary, #886CC0);
    position: relative;
    z-index: 10;
    transition: all 0.2s ease;
}
.std-wizard-label {
    margin-top: 0.5rem;
    font-size: 0.85rem;
    font-weight: 500;
    text-align: center;
}
.std-wizard-link.active .std-wizard-number,
.std-wizard-link.completed .std-wizard-number {
    background: var(--primary, #886CC0);
    color: #fff;
    border-color: var(--primary, #886CC0);
}
.std-wizard-link.active:after,
.std-wizard-link.completed:after {
    background: var(--primary, #886CC0);
}
.std-wizard-link.incomplete .std-wizard-number {
    background: rgba(220, 53, 69, 0.15);
    color: #dc3545;
    border-color: #dc3545;
}
.std-wizard-link.incomplete:after {
    background: #e9ecef;
}
.std-wizard-content {
    min-height: 400px;
}
.std-wizard-pane {
    display: none;
}
.std-wizard-pane.active {
    display: block;
}
.std-wizard-toolbar {
    display: flex;
    justify-content: space-between;
    margin-top: 2rem;
    padding-top: 1rem;
    border-top: 1px solid #e9ecef;
}
.std-wizard-review {
    background: #f8f9fa;
    border-radius: 0.5rem;
    padding: 1.25rem;
}
.std-wizard-review-section {
    margin-bottom: 1rem;
}
.std-wizard-review-section:last-child {
    margin-bottom: 0;
}
.std-wizard-review-section h6 {
    margin-bottom: 0.75rem;
    color: #495057;
    font-weight: 600;
}
.std-wizard-review-row {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    border-bottom: 1px solid #e9ecef;
}
.std-wizard-review-row:last-child {
    border-bottom: none;
}
.std-wizard-review-label {
    color: #6c757d;
    font-size: 0.875rem;
}
.std-wizard-review-value {
    font-weight: 500;
    color: #212529;
    text-align: right;
    max-width: 60%;
    word-break: break-word;
}
</style>
@endpush

@push('scripts')
<script src="{{ asset('js/services/email-to-sms-service.js') }}"></script>
<script>
$(document).ready(function() {
    var EMAIL_DOMAIN = '@sms.quicksms.io';
    
    var overviewAddresses = EmailToSmsService.getMockOverviewAddresses();
    var reportingGroups = EmailToSmsService.getMockReportingGroups();
    
    // ========================================
    // Standard Email-to-SMS Wizard
    // ========================================
    var stdWizardCurrentStep = 0;
    var stdWizardAllowedEmails = [];
    var stdWizardStepVisited = [true, false, false];
    var stdWizardSetupCreated = false;
    
    function stdWizardGoToStep(stepIndex) {
        if (stepIndex < 0 || stepIndex > 2) return;
        
        stdWizardStepVisited[stepIndex] = true;
        stdWizardCurrentStep = stepIndex;
        
        $('.std-wizard-pane').removeClass('active');
        $('#stdStep' + (stepIndex + 1)).addClass('active');
        
        $('.std-wizard-link').removeClass('active');
        $('.std-wizard-link[data-step="' + stepIndex + '"]').addClass('active');
        
        stdWizardUpdateStepIndicators();
        stdWizardUpdateButtons();
        
        if (stepIndex === 2) {
            stdWizardPopulateReview();
        }
    }
    
    function stdWizardCheckStepValidity(stepIndex) {
        if (stepIndex === 0) {
            var name = $('#stdWizardName').val().trim();
            var subaccount = $('#stdWizardSubaccount').val();
            return name.length > 0 && subaccount.length > 0;
        } else if (stepIndex === 1) {
            return true;
        } else if (stepIndex === 2) {
            var senderId = $('#stdWizardSenderId').val();
            if (!senderId) return false;
            
            if ($('#stdWizardDeliveryReports').is(':checked')) {
                var deliveryEmail = $('#stdWizardDeliveryEmail').val().trim();
                var validation = EmailToSmsService.validateEmail(deliveryEmail);
                if (!validation.valid || validation.isWildcard) return false;
            }
            
            var contentFilter = $('#stdWizardSignatureFilter').val().trim();
            var regexValidation = EmailToSmsService.validateContentFilterRegex(contentFilter);
            if (!regexValidation.valid) return false;
            
            return true;
        }
        return true;
    }
    
    function stdWizardUpdateStepIndicators() {
        for (var i = 0; i < 3; i++) {
            var $link = $('.std-wizard-link[data-step="' + i + '"]');
            $link.removeClass('completed incomplete');
            
            if (i === stdWizardCurrentStep) {
                continue;
            }
            
            if (stdWizardStepVisited[i]) {
                if (stdWizardCheckStepValidity(i)) {
                    $link.addClass('completed');
                } else {
                    $link.addClass('incomplete');
                }
            }
        }
    }
    
    function stdWizardUpdateButtons() {
        $('#stdWizardBtnPrev').prop('disabled', stdWizardCurrentStep === 0);
        
        if (stdWizardCurrentStep === 2) {
            $('#stdWizardBtnNext').html('<i class="fas fa-check me-1"></i> Create Setup');
        } else {
            $('#stdWizardBtnNext').html('Next <i class="fas fa-arrow-right ms-1"></i>');
        }
    }
    
    function stdWizardPopulateReview() {
        $('#stdWizardReviewName').text($('#stdWizardName').val().trim() || '-');
        $('#stdWizardReviewDescription').text($('#stdWizardDescription').val().trim() || 'None');
        $('#stdWizardReviewSubaccount').text($('#stdWizardSubaccount option:selected').text() || '-');
        
        if (stdWizardAllowedEmails.length > 0) {
            $('#stdWizardReviewAllowedSenders').text(stdWizardAllowedEmails.join(', '));
        } else {
            $('#stdWizardReviewAllowedSenders').text('All senders allowed');
        }
        
        $('#stdWizardReviewSenderId').text($('#stdWizardSenderId option:selected').text() || '-');
        $('#stdWizardReviewSubjectAsSenderId').text($('#stdWizardSubjectAsSenderId').is(':checked') ? 'Yes' : 'No');
        $('#stdWizardReviewMultipleSms').text($('#stdWizardMultipleSms').is(':checked') ? 'Yes' : 'No');
        
        if ($('#stdWizardDeliveryReports').is(':checked')) {
            var email = $('#stdWizardDeliveryEmail').val().trim();
            $('#stdWizardReviewDeliveryReports').text('Yes (' + (email || 'email not set') + ')');
        } else {
            $('#stdWizardReviewDeliveryReports').text('No');
        }
        
        var contentFilter = $('#stdWizardSignatureFilter').val().trim();
        if (contentFilter) {
            var lines = contentFilter.split('\n').filter(function(l) { return l.trim(); });
            $('#stdWizardReviewContentFilter').text(lines.length + ' pattern(s) configured');
        } else {
            $('#stdWizardReviewContentFilter').text('None');
        }
    }
    
    function stdWizardValidateAllSteps() {
        var isValid = true;
        
        var name = $('#stdWizardName').val().trim();
        if (!name) {
            $('#stdWizardName').addClass('is-invalid');
            isValid = false;
        } else {
            $('#stdWizardName').removeClass('is-invalid');
        }
        
        var subaccount = $('#stdWizardSubaccount').val();
        if (!subaccount) {
            $('#stdWizardSubaccount').addClass('is-invalid');
            isValid = false;
        } else {
            $('#stdWizardSubaccount').removeClass('is-invalid');
        }
        
        var senderId = $('#stdWizardSenderId').val();
        if (!senderId) {
            $('#stdWizardSenderId').addClass('is-invalid');
            isValid = false;
        } else {
            $('#stdWizardSenderId').removeClass('is-invalid');
        }
        
        if ($('#stdWizardDeliveryReports').is(':checked')) {
            var deliveryEmail = $('#stdWizardDeliveryEmail').val().trim();
            var emailValidation = EmailToSmsService.validateEmail(deliveryEmail);
            if (!deliveryEmail || !emailValidation.valid || emailValidation.isWildcard) {
                $('#stdWizardDeliveryEmail').addClass('is-invalid');
                isValid = false;
            } else {
                $('#stdWizardDeliveryEmail').removeClass('is-invalid');
            }
        }
        
        var contentFilter = $('#stdWizardSignatureFilter').val().trim();
        var regexValidation = EmailToSmsService.validateContentFilterRegex(contentFilter);
        if (!regexValidation.valid) {
            $('#stdWizardSignatureFilter').addClass('is-invalid');
            $('#stdWizardSignatureFilterError').text(regexValidation.error);
            isValid = false;
        } else {
            $('#stdWizardSignatureFilter').removeClass('is-invalid');
        }
        
        return isValid;
    }
    
    function stdWizardSaveSetup() {
        if (stdWizardSetupCreated) return;
        
        var $btn = $('#stdWizardBtnNext');
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Saving...');
        
        var payload = {
            name: $('#stdWizardName').val().trim(),
            description: $('#stdWizardDescription').val().trim(),
            subaccount: $('#stdWizardSubaccount').val(),
            subaccountName: $('#stdWizardSubaccount option:selected').text(),
            allowedSenders: stdWizardAllowedEmails.slice(),
            senderId: $('#stdWizardSenderId').val(),
            subjectAsSenderId: $('#stdWizardSubjectAsSenderId').is(':checked'),
            multipleSms: $('#stdWizardMultipleSms').is(':checked'),
            deliveryReports: $('#stdWizardDeliveryReports').is(':checked'),
            deliveryEmail: $('#stdWizardDeliveryEmail').val().trim(),
            signatureFilter: $('#stdWizardSignatureFilter').val().trim(),
            status: 'active'
        };
        
        EmailToSmsService.createStandardEmailToSmsSetup(payload).then(function(response) {
            if (response.success) {
                stdWizardSetupCreated = true;
                showSuccessToast('Standard Email-to-SMS is now Live');
                bootstrap.Modal.getInstance($('#createStandardModal')[0]).hide();
                stdWizardReset();
                loadStandardSmsTable();
            } else {
                showErrorToast(response.error || 'Failed to create setup');
                $btn.prop('disabled', false).html('<i class="fas fa-check me-1"></i> Create Setup');
            }
        }).catch(function(error) {
            console.error('Save failed:', error);
            showErrorToast('An error occurred while saving');
            $btn.prop('disabled', false).html('<i class="fas fa-check me-1"></i> Create Setup');
        });
    }
    
    function stdWizardReset() {
        stdWizardCurrentStep = 0;
        stdWizardAllowedEmails = [];
        stdWizardStepVisited = [true, false, false];
        stdWizardSetupCreated = false;
        
        $('#stdWizardName').val('').removeClass('is-invalid');
        $('#stdWizardDescription').val('');
        $('#stdWizardDescCharCount').text('0');
        $('#stdWizardSubaccount').val('').removeClass('is-invalid');
        $('#stdWizardEmailInput').val('');
        $('#stdWizardEmailTagsContainer').empty();
        $('#stdWizardEmailCount').text('0');
        $('#stdWizardClearAllEmails').hide();
        $('#stdWizardWildcardWarning').addClass('d-none');
        $('#stdWizardSenderId').val('').removeClass('is-invalid');
        $('#stdWizardSubjectAsSenderId').prop('checked', false);
        $('#stdWizardMultipleSms').prop('checked', false);
        $('#stdWizardDeliveryReports').prop('checked', false);
        $('#stdWizardDeliveryEmailGroup').hide();
        $('#stdWizardDeliveryEmail').val('').removeClass('is-invalid');
        $('#stdWizardSignatureFilter').val('').removeClass('is-invalid');
        
        $('.std-wizard-link').removeClass('completed incomplete active');
        $('.std-wizard-link[data-step="0"]').addClass('active');
        $('.std-wizard-pane').removeClass('active');
        $('#stdStep1').addClass('active');
        
        stdWizardUpdateButtons();
    }
    
    function stdWizardAddEmail() {
        var input = $('#stdWizardEmailInput');
        var email = input.val().trim().toLowerCase();
        var errorEl = $('#stdWizardEmailError');
        
        if (!email) return;
        
        var validation = EmailToSmsService.validateEmail(email);
        if (!validation.valid) {
            errorEl.text('Invalid email format. Use email@domain.com or *@domain.com for wildcards.').show();
            input.addClass('is-invalid');
            return;
        }
        
        if (stdWizardAllowedEmails.includes(email)) {
            errorEl.text('This email has already been added.').show();
            input.addClass('is-invalid');
            return;
        }
        
        errorEl.hide();
        input.removeClass('is-invalid');
        
        stdWizardAllowedEmails.push(email);
        input.val('');
        stdWizardRenderEmailTags();
        stdWizardUpdateWildcardWarning();
    }
    
    function stdWizardRemoveEmail(email) {
        var index = stdWizardAllowedEmails.indexOf(email);
        if (index > -1) {
            stdWizardAllowedEmails.splice(index, 1);
            stdWizardRenderEmailTags();
            stdWizardUpdateWildcardWarning();
        }
    }
    
    function stdWizardRenderEmailTags() {
        var container = $('#stdWizardEmailTagsContainer');
        container.empty();
        
        stdWizardAllowedEmails.forEach(function(email) {
            var isWildcard = email.startsWith('*@');
            var tag = $('<span class="email-tag' + (isWildcard ? ' email-tag-wildcard' : '') + '">' +
                        '<span class="email-text">' + escapeHtml(email) + '</span>' +
                        '<span class="remove-email std-wizard-remove-email" data-email="' + escapeHtml(email) + '">&times;</span>' +
                        '</span>');
            container.append(tag);
        });
        
        $('#stdWizardEmailCount').text(stdWizardAllowedEmails.length);
        
        if (stdWizardAllowedEmails.length > 0) {
            $('#stdWizardClearAllEmails').show();
        } else {
            $('#stdWizardClearAllEmails').hide();
        }
    }
    
    function stdWizardUpdateWildcardWarning() {
        var hasWildcard = stdWizardAllowedEmails.some(function(email) {
            return email.startsWith('*@');
        });
        
        if (hasWildcard) {
            $('#stdWizardWildcardWarning').removeClass('d-none');
        } else {
            $('#stdWizardWildcardWarning').addClass('d-none');
        }
    }
    
    function stdWizardValidateCurrentStep() {
        var isValid = true;
        
        if (stdWizardCurrentStep === 0) {
            var name = $('#stdWizardName').val().trim();
            var $nameInput = $('#stdWizardName');
            var $nameError = $('#stdWizardNameError');
            
            if (!name) {
                $nameInput.addClass('is-invalid');
                $nameError.text('Please enter a name.');
                isValid = false;
            } else if (EmailToSmsService.checkNameExists(name)) {
                $nameInput.addClass('is-invalid');
                $nameError.text('This name is already in use. Please choose a unique name.');
                isValid = false;
            } else {
                $nameInput.removeClass('is-invalid');
            }
            
            var subaccount = $('#stdWizardSubaccount').val();
            if (!subaccount) {
                $('#stdWizardSubaccount').addClass('is-invalid');
                isValid = false;
            } else {
                $('#stdWizardSubaccount').removeClass('is-invalid');
            }
        }
        
        return isValid;
    }
    
    $('#stdWizardBtnNext').on('click', function() {
        if (stdWizardCurrentStep < 2) {
            if (!stdWizardValidateCurrentStep()) {
                showErrorToast('Please fill in all required fields before proceeding');
                return;
            }
            stdWizardGoToStep(stdWizardCurrentStep + 1);
        } else {
            if (!stdWizardValidateAllSteps()) {
                showErrorToast('Please fill in all required fields');
                return;
            }
            stdWizardSaveSetup();
        }
    });
    
    $('#stdWizardBtnPrev').on('click', function() {
        if (stdWizardCurrentStep > 0) {
            stdWizardGoToStep(stdWizardCurrentStep - 1);
        }
    });
    
    $('.std-wizard-link').on('click', function(e) {
        e.preventDefault();
        var step = parseInt($(this).data('step'));
        stdWizardGoToStep(step);
    });
    
    $('#stdWizardAddEmailBtn').on('click', stdWizardAddEmail);
    $('#stdWizardEmailInput').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            stdWizardAddEmail();
        }
    });
    
    $(document).on('click', '.std-wizard-remove-email', function() {
        var email = $(this).data('email');
        stdWizardRemoveEmail(email);
    });
    
    $('#stdWizardClearAllEmails').on('click', function() {
        stdWizardAllowedEmails = [];
        stdWizardRenderEmailTags();
        stdWizardUpdateWildcardWarning();
    });
    
    $('#stdWizardDeliveryReports').on('change', function() {
        if ($(this).is(':checked')) {
            $('#stdWizardDeliveryEmailGroup').slideDown(200);
        } else {
            $('#stdWizardDeliveryEmailGroup').slideUp(200);
            $('#stdWizardDeliveryEmail').val('').removeClass('is-invalid');
        }
    });
    
    $('#stdWizardDescription').on('input', function() {
        var len = $(this).val().length;
        $('#stdWizardDescCharCount').text(len);
    });
    
    $('#stdWizardBtnSaveDraft').on('click', function() {
        var $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Saving...');
        $('#stdWizardAutosave').addClass('saving');
        $('#stdWizardAutosaveText').text('Saving draft...');
        
        var payload = {
            name: $('#stdWizardName').val().trim(),
            description: $('#stdWizardDescription').val().trim(),
            subaccount: $('#stdWizardSubaccount').val(),
            subaccountName: $('#stdWizardSubaccount option:selected').text(),
            allowedEmails: stdWizardAllowedEmails,
            senderId: $('#stdWizardSenderId').val(),
            subjectAsSenderId: $('#stdWizardSubjectAsSenderId').is(':checked'),
            multipleSms: $('#stdWizardMultipleSms').is(':checked'),
            deliveryReports: $('#stdWizardDeliveryReports').is(':checked'),
            deliveryReportsEmail: $('#stdWizardDeliveryEmail').val().trim(),
            signatureFilter: $('#stdWizardSignatureFilter').val().trim(),
            status: 'draft'
        };
        
        EmailToSmsService.createStandardEmailToSmsSetup(payload).then(function(response) {
            if (response.success) {
                $('#stdWizardAutosave').removeClass('saving').addClass('saved');
                $('#stdWizardAutosaveText').text('Draft saved');
                showSuccessToast('Draft saved successfully');
                bootstrap.Modal.getInstance($('#createStandardModal')[0]).hide();
                stdWizardReset();
                loadStandardSmsTable();
            } else {
                showErrorToast(response.error || 'Failed to save draft');
            }
            $btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save as Draft');
        }).catch(function(error) {
            console.error('Save draft failed:', error);
            showErrorToast('An error occurred while saving draft');
            $btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save as Draft');
            $('#stdWizardAutosave').removeClass('saving');
            $('#stdWizardAutosaveText').text('Save failed');
        });
    });
    
    $('#createStandardModal').on('hidden.bs.modal', function() {
        stdWizardReset();
    });
    
    $('#createStandardModal').on('shown.bs.modal', function() {
        stdWizardPopulateSenderIds();
        stdWizardCheckAccountFlags();
    });
    
    function stdWizardPopulateSenderIds() {
        var $dropdown = $('#stdWizardSenderId');
        $dropdown.html('<option value="">Loading...</option>');
        
        EmailToSmsService.getTemplatesForSenderIdDropdown().then(function(response) {
            $dropdown.empty().append('<option value="">Select SenderID...</option>');
            
            if (response.success && response.data && response.data.length > 0) {
                response.data.forEach(function(template) {
                    $dropdown.append('<option value="' + template.id + '">' + escapeHtml(template.senderId) + '</option>');
                });
            } else {
                $dropdown.append('<option value="" disabled>No approved SenderIDs available</option>');
            }
        }).catch(function() {
            $dropdown.empty()
                .append('<option value="">Select SenderID...</option>')
                .append('<option value="QuickSMS">QuickSMS</option>')
                .append('<option value="ALERTS">ALERTS</option>')
                .append('<option value="NHS">NHS</option>')
                .append('<option value="INFO">INFO</option>')
                .append('<option value="Pharmacy">Pharmacy</option>');
        });
    }
    
    function stdWizardCheckAccountFlags() {
        EmailToSmsService.getAccountFlags().then(function(response) {
            if (response.success && response.data) {
                if (response.data.dynamicSenderIdEnabled) {
                    $('#stdWizardSubjectAsSenderIdGroup').show();
                    $('#stdWizardReviewSubjectAsSenderIdRow').show();
                } else {
                    $('#stdWizardSubjectAsSenderIdGroup').hide();
                    $('#stdWizardReviewSubjectAsSenderIdRow').hide();
                    $('#stdWizardSubjectAsSenderId').prop('checked', false);
                }
            }
        }).catch(function() {
            $('#stdWizardSubjectAsSenderIdGroup').show();
            $('#stdWizardReviewSubjectAsSenderIdRow').show();
        });
    }
    
    // ========================================
    // End Standard Email-to-SMS Wizard
    // ========================================
    
    // Contact List setups - populated from service layer
    var contactListSetups = [];
    var contactListSetupsLoading = false;
    
    /**
     * Load Contact List setups from service
     */
    function loadContactListSetups(options) {
        options = options || {};
        contactListSetupsLoading = true;
        
        return EmailToSmsService.listEmailToSmsContactListSetups({
            includeArchived: options.includeArchived || $('#clShowArchived').is(':checked'),
            search: options.search || ''
        }).then(function(response) {
            contactListSetupsLoading = false;
            if (response.success) {
                contactListSetups = response.data.map(function(item) {
                    return {
                        id: item.id,
                        name: item.name,
                        description: item.description,
                        subaccountId: item.subaccountId,
                        subaccountName: item.subaccountName,
                        allowedSenders: item.allowedSenderEmails || [],
                        targetLists: item.targetLists || item.contactBookListNames || [],
                        optOutLists: item.optOutLists || item.optOutListNames || [],
                        created: item.created,
                        lastUpdated: item.lastUpdated,
                        status: item.status === 'active' ? 'Active' : 'Archived'
                    };
                });
            }
            return contactListSetups;
        }).catch(function(error) {
            contactListSetupsLoading = false;
            console.error('Failed to load contact list setups:', error);
            return [];
        });
    }
    
    var rgAppliedFilters = {};
    var clAppliedFilters = {};
    
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
            
            var emailsDisplay = addr.originatingEmails.slice(0, 2).map(function(email) {
                return '<code class="email-address-display d-block mb-1">' + email + '</code>';
            }).join('');
            if (addr.originatingEmails.length > 2) {
                emailsDisplay += '<span class="text-muted small">+' + (addr.originatingEmails.length - 2) + ' more</span>';
            }
            
            var row = '<tr data-id="' + addr.id + '">' +
                '<td><span class="email-sms-name">' + addr.name + '</span></td>' +
                '<td>' + emailsDisplay + '</td>' +
                '<td>' + addr.type + '</td>' +
                '<td>' + addr.reportingGroup + '</td>' +
                '<td>' + statusBadge + '</td>' +
                '<td>' + addr.created + '</td>' +
                '<td class="text-end">' +
                    '<div class="dropdown">' +
                        '<button class="action-menu-btn" type="button" data-bs-toggle="dropdown" onclick="event.stopPropagation();">' +
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
                ? group.linkedAddresses.map(function(addr) { return '<span class="text-muted small me-1">' + addr + '</span>'; }).join('')
                : '<span class="text-muted">None</span>';
            
            var row = '<tr data-id="' + group.id + '">' +
                '<td><span class="email-sms-name">' + group.name + '</span></td>' +
                '<td class="text-muted small">' + (group.description || '-') + '</td>' +
                '<td>' + linkedDisplay + '</td>' +
                '<td>' + group.messagesSent.toLocaleString() + '</td>' +
                '<td>' + group.lastActivity + '</td>' +
                '<td>' + group.created + '</td>' +
                '<td class="text-end">' +
                    '<div class="dropdown">' +
                        '<button class="action-menu-btn" type="button" data-bs-toggle="dropdown" onclick="event.stopPropagation();">' +
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
        $('#rgTotalCount').text(reportingGroups.length);
    }
    
    function filterReportingGroups() {
        var filtered = reportingGroups.slice();
        var chips = [];
        
        var searchTerm = ($('#rgQuickSearchInput').val() || '').toLowerCase().trim();
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
        $('#rgFilterStatus').val('');
        $('#rgFilterDateFrom').val('');
        $('#rgFilterDateTo').val('');
        $('.rg-date-preset').removeClass('active');
        rgAppliedFilters = {};
        $('#rgActiveFiltersContainer').hide();
        renderReportingGroups(reportingGroups);
    }
    
    function renderContactListMappings(mappings) {
        var tbody = $('#contactListsTableBody');
        tbody.empty();
        
        if (mappings.length === 0) {
            $('#contactListsTableContainer').hide();
            $('#emptyStateContactLists').show();
            $('#clShowingCount').text(0);
            $('#clTotalCount').text(0);
            return;
        }
        
        $('#contactListsTableContainer').show();
        $('#emptyStateContactLists').hide();
        
        mappings.forEach(function(mapping) {
            // Allowed Sender Emails - truncate with "+X more"
            var allowedDisplay = '';
            if (mapping.allowedSenders.length === 0) {
                allowedDisplay = '<span class="text-muted small">All senders</span>';
            } else if (mapping.allowedSenders.length === 1) {
                allowedDisplay = '<span class="small">' + escapeHtml(mapping.allowedSenders[0]) + '</span>';
            } else {
                allowedDisplay = '<span class="small">' + escapeHtml(mapping.allowedSenders[0]) + '</span>' +
                    '<span class="text-muted small ms-1">+' + (mapping.allowedSenders.length - 1) + ' more</span>';
            }
            
            // Target Lists - show first 1-2 + "+X more"
            var targetDisplay = '';
            if (mapping.targetLists.length === 0) {
                targetDisplay = '<span class="text-muted small">None</span>';
            } else if (mapping.targetLists.length === 1) {
                targetDisplay = '<span class="small">' + escapeHtml(mapping.targetLists[0]) + '</span>';
            } else if (mapping.targetLists.length === 2) {
                targetDisplay = '<span class="small">' + escapeHtml(mapping.targetLists[0]) + ', ' + escapeHtml(mapping.targetLists[1]) + '</span>';
            } else {
                targetDisplay = '<span class="small">' + escapeHtml(mapping.targetLists[0]) + ', ' + escapeHtml(mapping.targetLists[1]) + '</span>' +
                    '<span class="text-muted small ms-1">+' + (mapping.targetLists.length - 2) + ' more</span>';
            }
            
            // Opt-out Lists - show "NO" or list names
            var optOutDisplay = '';
            if (mapping.optOutLists.length === 0) {
                optOutDisplay = '<span class="text-muted small">NO</span>';
            } else if (mapping.optOutLists.length === 1) {
                optOutDisplay = '<span class="small">' + escapeHtml(mapping.optOutLists[0]) + '</span>';
            } else {
                optOutDisplay = '<span class="small">' + escapeHtml(mapping.optOutLists[0]) + '</span>' +
                    '<span class="text-muted small ms-1">+' + (mapping.optOutLists.length - 1) + ' more</span>';
            }
            
            var statusClass = mapping.status === 'Active' ? '' : 'table-secondary';
            var archivedBadge = mapping.status === 'Archived' ? ' <span class="badge bg-secondary ms-1">Archived</span>' : '';
            
            var row = '<tr data-id="' + mapping.id + '" class="' + statusClass + '">' +
                '<td><span class="fw-medium">' + escapeHtml(mapping.name) + '</span>' + archivedBadge + '</td>' +
                '<td>' + escapeHtml(mapping.subaccountName) + '</td>' +
                '<td>' + allowedDisplay + '</td>' +
                '<td>' + targetDisplay + '</td>' +
                '<td>' + optOutDisplay + '</td>' +
                '<td>' + mapping.created + '</td>' +
                '<td>' + mapping.lastUpdated + '</td>' +
                '<td class="text-end">' +
                    '<div class="dropdown">' +
                        '<button class="action-menu-btn" type="button" data-bs-toggle="dropdown" onclick="event.stopPropagation();">' +
                            '<i class="fas fa-ellipsis-v"></i>' +
                        '</button>' +
                        '<ul class="dropdown-menu dropdown-menu-end">' +
                            '<li><a class="dropdown-item clm-action-view" href="#" data-id="' + mapping.id + '"><i class="fas fa-eye me-2"></i> View</a></li>' +
                            '<li><a class="dropdown-item clm-action-edit" href="#" data-id="' + mapping.id + '"><i class="fas fa-edit me-2"></i> Edit</a></li>' +
                            (mapping.status === 'Active' 
                                ? '<li><a class="dropdown-item clm-action-archive" href="#" data-id="' + mapping.id + '"><i class="fas fa-archive me-2"></i> Archive</a></li>'
                                : '<li><a class="dropdown-item clm-action-unarchive" href="#" data-id="' + mapping.id + '"><i class="fas fa-undo me-2"></i> Unarchive</a></li>') +
                        '</ul>' +
                    '</div>' +
                '</td>' +
            '</tr>';
            
            tbody.append(row);
        });
        
        $('#clShowingCount').text(mappings.length);
        $('#clTotalCount').text(contactListSetups.length);
        
        // Bind action handlers
        $('.clm-action-view').off('click').on('click', function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            openClmViewDrawer(id);
        });
        
        $('.clm-action-edit').off('click').on('click', function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            openClmEditModal(id);
        });
        
        $('.clm-action-archive').off('click').on('click', function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            openClmArchiveModal(id);
        });
        
        $('.clm-action-unarchive').off('click').on('click', function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            EmailToSmsService.unarchiveEmailToSmsContactListSetup(id).then(function(response) {
                if (response.success) {
                    loadContactListSetups().then(function(data) {
                        filterContactListMappings();
                    });
                }
            });
        });
    }
    
    function findClmById(id) {
        return contactListSetups.find(function(m) { return m.id === id; });
    }
    
    var clmEditingId = null;
    
    function openClmViewDrawer(id) {
        var item = findClmById(id);
        if (!item) return;
        
        $('#clmDrawerTitle').text(item.name);
        $('#clmDrawerName').text(item.name);
        $('#clmDrawerDescription').text(item.description || '-');
        $('#clmDrawerSubaccount').text(item.subaccountName);
        
        var statusHtml = item.status === 'Active' 
            ? '<span class="badge badge-live-status">Active</span>'
            : '<span class="badge bg-secondary">Archived</span>';
        $('#clmDrawerStatus').html(statusHtml);
        
        if (item.allowedSenders.length === 0) {
            $('#clmDrawerAllowedSenders').html('<span class="text-muted">All senders allowed</span>');
        } else {
            var sendersHtml = item.allowedSenders.map(function(email) {
                return '<code class="d-block mb-1">' + escapeHtml(email) + '</code>';
            }).join('');
            $('#clmDrawerAllowedSenders').html(sendersHtml);
        }
        
        if (item.targetLists.length === 0) {
            $('#clmDrawerTargetLists').html('<span class="text-muted">None</span>');
        } else {
            var listsHtml = item.targetLists.map(function(list) {
                return '<span class="badge bg-light text-dark me-1 mb-1">' + escapeHtml(list) + '</span>';
            }).join('');
            $('#clmDrawerTargetLists').html(listsHtml);
        }
        
        if (item.optOutLists.length === 0) {
            $('#clmDrawerOptOutLists').html('<span class="text-muted">NO</span>');
        } else {
            var optOutHtml = item.optOutLists.map(function(list) {
                return '<span class="badge bg-light text-dark me-1 mb-1">' + escapeHtml(list) + '</span>';
            }).join('');
            $('#clmDrawerOptOutLists').html(optOutHtml);
        }
        
        $('#clmDrawerCreated').text(item.created);
        $('#clmDrawerLastUpdated').text(item.lastUpdated);
        
        $('#clmDrawerBackdrop').addClass('show');
        $('#clmDetailsDrawer').addClass('open');
        
        $('#clmDrawerEditBtn').off('click').on('click', function() {
            closeClmDrawer();
            openClmEditModal(id);
        });
    }
    
    function closeClmDrawer() {
        $('#clmDrawerBackdrop').removeClass('show');
        $('#clmDetailsDrawer').removeClass('open');
    }
    
    function openClmEditModal(id) {
        var item = findClmById(id);
        if (!item) return;
        
        clmEditingId = id;
        
        $('#createContactListMappingModal .modal-title').html('<i class="fas fa-edit me-2 text-primary"></i>Edit Email-to-SMS – Contact List');
        
        $('#clmCreateName').val(item.name);
        $('#clmCreateDescription').val(item.description || '');
        $('#clmCreateSubaccount').val(item.subaccountId);
        
        clmAllowedEmails = item.allowedSenders.slice();
        renderClmEmailTags();
        
        // Build clmSelectedLists array from targetLists names
        clmSelectedLists = item.targetLists.map(function(name) {
            return { value: name.toLowerCase().replace(/\s+/g, '-'), label: name };
        });
        
        // Update checkboxes for Contact Lists
        $('.clm-contact-list-cb').each(function() {
            var label = $(this).data('label');
            if (item.targetLists.indexOf(label) !== -1) {
                $(this).prop('checked', true);
            } else {
                $(this).prop('checked', false);
            }
        });
        updateClmContactListsSelection();
        
        // Build clmSelectedOptOuts array from optOutLists names
        if (item.optOutLists.length === 0) {
            clmSelectedOptOuts = [];
            $('.clm-optout-cb').prop('checked', false);
            $('#clmOptNo').prop('checked', true);
        } else {
            clmSelectedOptOuts = item.optOutLists.map(function(name) {
                return { value: name.toLowerCase().replace(/\s+/g, '-'), label: name };
            });
            $('#clmOptNo').prop('checked', false);
            $('.clm-optout-cb').each(function() {
                var label = $(this).data('label');
                if (item.optOutLists.indexOf(label) !== -1) {
                    $(this).prop('checked', true);
                } else {
                    $(this).prop('checked', false);
                }
            });
        }
        updateClmOptOutSelection();
        
        var modal = new bootstrap.Modal(document.getElementById('createContactListMappingModal'));
        modal.show();
    }
    
    function updateClmContactListsSelection() {
        // Update display based on current selections
        if (clmSelectedLists.length === 0) {
            $('#clmContactListsDropdown .dropdown-label').text('Select list(s)...');
        } else if (clmSelectedLists.length === 1) {
            $('#clmContactListsDropdown .dropdown-label').text(clmSelectedLists[0].label);
        } else {
            $('#clmContactListsDropdown .dropdown-label').text(clmSelectedLists.length + ' lists selected');
        }
        $('#clmContactListsDropdown button').removeClass('is-invalid');
        
        var display = $('#clmSelectedListsDisplay');
        display.empty();
        clmSelectedLists.forEach(function(list) {
            display.append('<span class="badge bg-light text-dark me-1 mb-1">' + escapeHtml(list.label) + '</span>');
        });
    }
    
    function updateClmOptOutSelection() {
        // Update display based on current selections
        if ($('#clmOptNo').is(':checked') || clmSelectedOptOuts.length === 0) {
            $('#clmOptOutDropdown .dropdown-label').text('No opt-out list');
        } else if (clmSelectedOptOuts.length === 1) {
            $('#clmOptOutDropdown .dropdown-label').text(clmSelectedOptOuts[0].label);
        } else {
            $('#clmOptOutDropdown .dropdown-label').text(clmSelectedOptOuts.length + ' lists selected');
        }
        
        var display = $('#clmSelectedOptOutsDisplay');
        display.empty();
        clmSelectedOptOuts.forEach(function(list) {
            display.append('<span class="badge bg-light text-dark me-1 mb-1">' + escapeHtml(list.label) + '</span>');
        });
    }
    
    var clmArchiveTargetId = null;
    
    function openClmArchiveModal(id) {
        var item = findClmById(id);
        if (!item) return;
        
        clmArchiveTargetId = id;
        $('#clmArchiveName').text(item.name);
        var modal = new bootstrap.Modal(document.getElementById('clmArchiveModal'));
        modal.show();
    }
    
    function confirmClmArchive() {
        if (!clmArchiveTargetId) return;
        
        EmailToSmsService.archiveEmailToSmsContactListSetup(clmArchiveTargetId).then(function(response) {
            if (response.success) {
                loadContactListSetups().then(function() {
                    filterContactListMappings();
                });
            } else {
                alert('Error: ' + (response.error || 'Failed to archive setup'));
            }
            bootstrap.Modal.getInstance(document.getElementById('clmArchiveModal')).hide();
            clmArchiveTargetId = null;
        }).catch(function(error) {
            console.error('Archive failed:', error);
            alert('Error archiving setup. Please try again.');
            bootstrap.Modal.getInstance(document.getElementById('clmArchiveModal')).hide();
            clmArchiveTargetId = null;
        });
    }
    
    function filterContactListMappings() {
        var filtered = contactListSetups.slice();
        var chips = [];
        
        var showArchived = $('#clShowArchived').is(':checked');
        if (!showArchived) {
            filtered = filtered.filter(function(m) {
                return m.status === 'Active';
            });
        }
        
        var searchTerm = ($('#clQuickSearchInput').val() || '').toLowerCase().trim();
        if (searchTerm) {
            filtered = filtered.filter(function(m) {
                return m.name.toLowerCase().indexOf(searchTerm) !== -1 ||
                       m.subaccountName.toLowerCase().indexOf(searchTerm) !== -1 ||
                       m.targetLists.some(function(list) {
                           return list.toLowerCase().indexOf(searchTerm) !== -1;
                       }) ||
                       m.allowedSenders.some(function(email) {
                           return email.toLowerCase().indexOf(searchTerm) !== -1;
                       });
            });
            chips.push({ filter: 'search', value: 'Search: ' + searchTerm });
        }
        
        var contactListFilter = $('#clFilterContactList').val();
        if (contactListFilter) {
            filtered = filtered.filter(function(m) {
                return m.targetLists.indexOf(contactListFilter) !== -1;
            });
            chips.push({ filter: 'contactlist', value: 'List: ' + contactListFilter });
        }
        
        var dateFrom = $('#clFilterDateFrom').val();
        var dateTo = $('#clFilterDateTo').val();
        if (dateFrom || dateTo) {
            filtered = filtered.filter(function(m) {
                var mappingDate = m.created;
                if (dateFrom && mappingDate < dateFrom) return false;
                if (dateTo && mappingDate > dateTo) return false;
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
                    '<span class="remove-chip cl-remove-chip" data-filter="' + chip.filter + '">&times;</span></span>';
            });
            $('#clActiveFiltersChips').html(chipsHtml);
            $('#clActiveFiltersContainer').show();
        } else {
            $('#clActiveFiltersContainer').hide();
        }
        
        renderContactListMappings(filtered);
    }
    
    function resetClFilters() {
        $('#clQuickSearchInput').val('');
        $('#clFilterContactList').val('');
        $('#clFilterDateFrom').val('');
        $('#clFilterDateTo').val('');
        $('.cl-date-preset').removeClass('active');
        clAppliedFilters = {};
        $('#clActiveFiltersContainer').hide();
        loadContactListSetups().then(function() {
            filterContactListMappings();
        });
    }
    
    function openDetailsDrawer(address) {
        selectedAddress = address;
        
        $('#drawerName').text(address.name);
        
        var primaryEmail = address.originatingEmails[0] || '-';
        $('#drawerPrimaryEmail').text(primaryEmail);
        $('#copyPrimaryEmailBtn').off('click').on('click', function() {
            copyEmailWithFeedback(primaryEmail, $(this));
        });
        
        $('#drawerType').text(address.type);
        $('#drawerDescription').text(address.description || '-');
        
        if (address.originatingEmails.length > 1) {
            var additionalEmails = address.originatingEmails.slice(1);
            var emailsHtml = additionalEmails.map(function(email, idx) {
                return '<div class="detail-row"><span class="detail-value"><code>' + email + '</code><button class="copy-btn copy-additional-email" data-email="' + email + '"><i class="fas fa-copy"></i></button></span></div>';
            }).join('');
            $('#drawerAdditionalEmails').html(emailsHtml);
            $('#drawerAdditionalEmailsSection').show();
        } else {
            $('#drawerAdditionalEmailsSection').hide();
        }
        
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
    
    function copyEmailWithFeedback(email, $btn) {
        navigator.clipboard.writeText(email).then(function() {
            var originalIcon = $btn.html();
            $btn.html('<i class="fas fa-check text-success"></i>');
            setTimeout(function() {
                $btn.html(originalIcon);
            }, 1500);
        });
    }
    
    $(document).on('click', '.copy-additional-email', function() {
        var email = $(this).data('email');
        copyEmailWithFeedback(email, $(this));
    });
    
    function closeDetailsDrawer() {
        $('#drawerBackdrop').removeClass('show');
        $('#detailsDrawer').removeClass('open');
        selectedAddress = null;
    }
    
    function applyFilters() {
        var chips = [];
        var filtered = overviewAddresses.slice();
        
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
        
        var search = $('#quickSearchInput').val().trim();
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
        $('#quickSearchInput').val('');
        $('#filterDateFrom').val('');
        $('#filterDateTo').val('');
        $('.date-preset-btn').removeClass('active');
        appliedFilters = {};
        $('#activeFiltersContainer').hide();
        renderAddressesTable(overviewAddresses);
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
        var address = overviewAddresses.find(function(a) { return a.id === id; });
        if (address) openDetailsDrawer(address);
    });
    
    $(document).on('click', '.view-details', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var address = overviewAddresses.find(function(a) { return a.id === id; });
        if (address) openDetailsDrawer(address);
    });
    
    $(document).on('click', '.suspend-address', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var address = overviewAddresses.find(function(a) { return a.id === id; });
        if (address) {
            $('#suspendModalTitle').text('Suspend Email-to-SMS Address');
            $('#suspendModalMessage').html('Are you sure you want to suspend <strong>' + address.name + '</strong>?');
            $('#suspendModalDescription').text('While suspended, emails sent to this address will not trigger SMS messages. You can reactivate it at any time.');
            $('#suspendModalIcon').removeClass('fa-play').addClass('fa-pause');
            $('#suspendModalAction').text('Suspend');
            $('#btnConfirmSuspend').removeClass('btn-success').addClass('btn-warning');
            $('#suspendModal').attr('data-address-id', id).attr('data-action', 'suspend');
            var modal = new bootstrap.Modal(document.getElementById('suspendModal'));
            modal.show();
        }
    });
    
    $(document).on('click', '.reactivate-address', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var address = overviewAddresses.find(function(a) { return a.id === id; });
        if (address) {
            $('#suspendModalTitle').text('Reactivate Email-to-SMS Address');
            $('#suspendModalMessage').html('Are you sure you want to reactivate <strong>' + address.name + '</strong>?');
            $('#suspendModalDescription').text('Once reactivated, emails sent to this address will resume triggering SMS messages.');
            $('#suspendModalIcon').removeClass('fa-pause').addClass('fa-play');
            $('#suspendModalAction').text('Reactivate');
            $('#btnConfirmSuspend').removeClass('btn-warning').addClass('btn-success');
            $('#suspendModal').attr('data-address-id', id).attr('data-action', 'reactivate');
            var modal = new bootstrap.Modal(document.getElementById('suspendModal'));
            modal.show();
        }
    });
    
    $(document).on('click', '.delete-address', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var address = overviewAddresses.find(function(a) { return a.id === id; });
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
                renderAddressesTable(overviewAddresses);
                return;
            }
            var filtered = overviewAddresses.filter(function(addr) {
                var emailMatch = addr.originatingEmails.some(function(email) {
                    return email.toLowerCase().includes(query);
                });
                return addr.name.toLowerCase().includes(query) || emailMatch;
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
        var setupType = $('#createType').val();
        var senderId = $('#createSenderId').val();
        
        if (!name || !subAccount || !setupType || !senderId) {
            alert('Please fill in all required fields.');
            return;
        }
        
        var generatedEmail = name.toLowerCase().replace(/\s+/g, '-') + '.' + Math.random().toString(36).substr(2, 5) + EMAIL_DOMAIN;
        
        var newAddress = {
            id: 'addr-' + Date.now(),
            name: name,
            originatingEmails: [generatedEmail],
            description: $('#createDescription').val().trim(),
            type: setupType,
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
        
        overviewAddresses.unshift(newAddress);
        renderAddressesTable(overviewAddresses);
        $('#createAddressModal').modal('hide');
        
        $('#createName, #createDescription, #createAllowedSenders, #createDailyLimit').val('');
        $('#createSubAccount, #createType, #createSenderId, #createOptOutList, #createReportingGroup').val('');
    });
    
    function getLinkedAddressNames() {
        var linkedNames = [];
        reportingGroups.forEach(function(group) {
            if (group.linkedAddresses && group.linkedAddresses.length > 0) {
                group.linkedAddresses.forEach(function(addrName) {
                    linkedNames.push(addrName);
                });
            }
        });
        return linkedNames;
    }
    
    function findGroupByAddressName(addressName) {
        return reportingGroups.find(function(g) {
            return g.linkedAddresses && g.linkedAddresses.indexOf(addressName) !== -1;
        });
    }
    
    function populateAddressDropdown() {
        var $dropdown = $('#rgAssignAddress');
        $dropdown.empty().append('<option value="">Select an address...</option>');
        
        var linkedNames = getLinkedAddressNames();
        
        overviewAddresses.forEach(function(addr) {
            var isLinked = linkedNames.indexOf(addr.name) !== -1;
            var optionText = addr.name + (isLinked ? ' (already linked)' : '');
            $dropdown.append('<option value="' + addr.id + '" data-name="' + addr.name + '" data-linked="' + isLinked + '">' + optionText + '</option>');
        });
    }
    
    $('#createReportingGroupModal').on('show.bs.modal', function() {
        $('#rgName').val('').removeClass('is-invalid');
        $('#rgDescription').val('');
        $('#rgAssignAddress').removeClass('is-invalid');
        $('#rgAddressError').text('Please select an Email-to-SMS Address.');
        
        populateAddressDropdown();
    });
    
    $('#rgAssignAddress').on('change', function() {
        var $selected = $(this).find('option:selected');
        var isLinked = $selected.data('linked');
        var addressName = $selected.data('name');
        
        if (isLinked) {
            $(this).addClass('is-invalid');
            var linkedGroup = findGroupByAddressName(addressName);
            var groupName = linkedGroup ? linkedGroup.name : 'another group';
            $('#rgAddressError').text('"' + addressName + '" is already assigned to "' + groupName + '". Each address can only belong to one Reporting Group.');
        } else {
            $(this).removeClass('is-invalid');
            $('#rgAddressError').text('Please select an Email-to-SMS Address.');
        }
    });
    
    $('#btnSaveReportingGroup').on('click', function() {
        var isValid = true;
        
        var name = $('#rgName').val().trim();
        if (!name) {
            $('#rgName').addClass('is-invalid');
            isValid = false;
        } else {
            $('#rgName').removeClass('is-invalid');
        }
        
        var $selectedAddress = $('#rgAssignAddress option:selected');
        var addressId = $('#rgAssignAddress').val();
        var addressName = $selectedAddress.data('name');
        var isLinked = $selectedAddress.data('linked');
        
        if (!addressId) {
            $('#rgAssignAddress').addClass('is-invalid');
            $('#rgAddressError').text('Please select an Email-to-SMS Address.');
            isValid = false;
        } else if (isLinked) {
            $('#rgAssignAddress').addClass('is-invalid');
            var linkedGroup = findGroupByAddressName(addressName);
            var groupName = linkedGroup ? linkedGroup.name : 'another group';
            $('#rgAddressError').text('"' + addressName + '" is already assigned to "' + groupName + '". Each address can only belong to one Reporting Group.');
            isValid = false;
        } else {
            $('#rgAssignAddress').removeClass('is-invalid');
        }
        
        if (!isValid) return;
        
        var newGroup = {
            id: 'rg-' + Date.now(),
            name: name,
            description: $('#rgDescription').val().trim(),
            linkedAddresses: [addressName],
            messagesSent: 0,
            lastActivity: '-',
            created: new Date().toISOString().split('T')[0],
            status: 'Active'
        };
        
        reportingGroups.push(newGroup);
        renderReportingGroups(reportingGroups);
        $('#createReportingGroupModal').modal('hide');
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
    
    $(document).on('click', '.rg-remove-chip', function() {
        var filter = $(this).data('filter');
        if (filter === 'search') {
            $('#rgQuickSearchInput').val('');
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
        var $btn = $(this);
        var originalHtml = $btn.html();
        
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
        
        EmailToSmsService.archiveReportingGroup(id).then(function(response) {
            if (response.success) {
                reportingGroups = EmailToSmsService.getMockReportingGroups();
                renderReportingGroups(reportingGroups);
                showSuccessToast(response.message);
            } else {
                showErrorToast(response.error || 'Failed to archive group');
            }
        }).catch(function(error) {
            console.error('Error archiving group:', error);
            showErrorToast('An error occurred while archiving the group');
        }).finally(function() {
            $btn.prop('disabled', false).html(originalHtml);
        });
    });
    
    $(document).on('click', '.unarchive-rg', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var $btn = $(this);
        var originalHtml = $btn.html();
        
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
        
        EmailToSmsService.unarchiveReportingGroup(id).then(function(response) {
            if (response.success) {
                reportingGroups = EmailToSmsService.getMockReportingGroups();
                renderReportingGroups(reportingGroups);
                showSuccessToast(response.message);
            } else {
                showErrorToast(response.error || 'Failed to unarchive group');
            }
        }).catch(function(error) {
            console.error('Error unarchiving group:', error);
            showErrorToast('An error occurred while unarchiving the group');
        }).finally(function() {
            $btn.prop('disabled', false).html(originalHtml);
        });
    });
    
    $('#btnConfirmSuspend').on('click', function() {
        var id = $('#suspendModal').attr('data-address-id');
        var action = $('#suspendModal').attr('data-action');
        var $btn = $(this);
        
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...');
        
        var serviceMethod = (action === 'suspend') 
            ? EmailToSmsService.suspendOverviewAddress(id)
            : EmailToSmsService.reactivateOverviewAddress(id);
        
        serviceMethod.then(function(response) {
            if (response.success) {
                overviewAddresses = EmailToSmsService.getMockOverviewAddresses();
                renderAddressesTable(overviewAddresses);
                
                if (selectedAddress && selectedAddress.id === id) {
                    var updatedAddress = overviewAddresses.find(function(a) { return a.id === id; });
                    if (updatedAddress) {
                        selectedAddress = updatedAddress;
                        openDetailsDrawer(updatedAddress);
                    }
                }
                
                showSuccessToast(response.message);
            } else {
                showErrorToast(response.error || 'Failed to update address status');
            }
        }).catch(function(error) {
            console.error('Error updating address status:', error);
            showErrorToast('An error occurred while updating the address status');
        }).finally(function() {
            $btn.prop('disabled', false);
            if (action === 'suspend') {
                $btn.html('<i class="fas fa-pause me-1"></i> Suspend');
            } else {
                $btn.html('<i class="fas fa-play me-1"></i> Reactivate');
            }
            bootstrap.Modal.getInstance(document.getElementById('suspendModal')).hide();
        });
    });
    
    function showSuccessToast(message) {
        var toastHtml = '<div class="toast align-items-center text-bg-success border-0 position-fixed" style="top: 20px; right: 20px; z-index: 9999;" role="alert" aria-live="assertive" aria-atomic="true">' +
            '<div class="d-flex">' +
                '<div class="toast-body">' +
                    '<i class="fas fa-check-circle me-2"></i>' + message +
                '</div>' +
                '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>' +
            '</div>' +
        '</div>';
        var toastEl = $(toastHtml).appendTo('body');
        var toast = new bootstrap.Toast(toastEl[0], { delay: 3000 });
        toast.show();
        toastEl.on('hidden.bs.toast', function() { $(this).remove(); });
    }
    
    function showErrorToast(message) {
        var toastHtml = '<div class="toast align-items-center text-bg-danger border-0 position-fixed" style="top: 20px; right: 20px; z-index: 9999;" role="alert" aria-live="assertive" aria-atomic="true">' +
            '<div class="d-flex">' +
                '<div class="toast-body">' +
                    '<i class="fas fa-exclamation-circle me-2"></i>' + message +
                '</div>' +
                '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>' +
            '</div>' +
        '</div>';
        var toastEl = $(toastHtml).appendTo('body');
        var toast = new bootstrap.Toast(toastEl[0], { delay: 5000 });
        toast.show();
        toastEl.on('hidden.bs.toast', function() { $(this).remove(); });
    }
    
    $('#btnConfirmDelete').on('click', function() {
        var id = $('#deleteModal').data('id');
        var $btn = $(this);
        
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Deleting...');
        
        EmailToSmsService.deleteOverviewAddress(id).then(function(response) {
            if (response.success) {
                overviewAddresses = EmailToSmsService.getMockOverviewAddresses();
                renderAddressesTable(overviewAddresses);
                closeDetailsDrawer();
                showSuccessToast(response.message);
            } else {
                showErrorToast(response.error || 'Failed to delete address');
            }
        }).catch(function(error) {
            console.error('Error deleting address:', error);
            showErrorToast('An error occurred while deleting the address');
        }).finally(function() {
            $btn.prop('disabled', false).html('<i class="fas fa-trash-alt me-1"></i> Delete');
            $('#deleteModal').modal('hide');
        });
    });
    
    $('#actionSuspend').on('click', function(e) {
        e.preventDefault();
        if (selectedAddress) {
            var isSuspended = selectedAddress.status === 'Suspended';
            if (isSuspended) {
                $('#suspendModalTitle').text('Reactivate Email-to-SMS Address');
                $('#suspendModalMessage').html('Are you sure you want to reactivate <strong>' + selectedAddress.name + '</strong>?');
                $('#suspendModalDescription').text('Once reactivated, emails sent to this address will resume triggering SMS messages.');
                $('#suspendModalIcon').removeClass('fa-pause').addClass('fa-play');
                $('#suspendModalAction').text('Reactivate');
                $('#btnConfirmSuspend').removeClass('btn-warning').addClass('btn-success');
                $('#suspendModal').attr('data-address-id', selectedAddress.id).attr('data-action', 'reactivate');
            } else {
                $('#suspendModalTitle').text('Suspend Email-to-SMS Address');
                $('#suspendModalMessage').html('Are you sure you want to suspend <strong>' + selectedAddress.name + '</strong>?');
                $('#suspendModalDescription').text('While suspended, emails sent to this address will not trigger SMS messages. You can reactivate it at any time.');
                $('#suspendModalIcon').removeClass('fa-play').addClass('fa-pause');
                $('#suspendModalAction').text('Suspend');
                $('#btnConfirmSuspend').removeClass('btn-success').addClass('btn-warning');
                $('#suspendModal').attr('data-address-id', selectedAddress.id).attr('data-action', 'suspend');
            }
            var modal = new bootstrap.Modal(document.getElementById('suspendModal'));
            modal.show();
        }
    });
    
    $('#actionDelete').on('click', function(e) {
        e.preventDefault();
        if (selectedAddress) {
            $('#deleteAddressName').text(selectedAddress.name);
            $('#deleteModal').data('id', selectedAddress.id).modal('show');
        }
    });
    
    renderAddressesTable(overviewAddresses);
    renderReportingGroups(reportingGroups);
    filterContactListMappings();
    
    // Contact Lists tab handlers
    $('#btnApplyClFilters').on('click', filterContactListMappings);
    $('#btnResetClFilters').on('click', resetClFilters);
    $('#btnClearClFilters').on('click', resetClFilters);
    
    // Show archived toggle
    $('#clShowArchived').on('change', filterContactListMappings);
    
    // CLM Drawer close handlers
    $('#clmDrawerCloseBtn, #clmDrawerBackdrop').on('click', closeClmDrawer);
    
    // CLM Archive confirm handler
    $('#clmArchiveConfirmBtn').on('click', confirmClmArchive);
    
    // Reset modal to create mode when closed
    $('#createContactListMappingModal').on('hidden.bs.modal', function() {
        clmEditingId = null;
        $('#createContactListMappingModal .modal-title').html('<i class="fas fa-link me-2 text-primary"></i>Create Email-to-SMS – Contact List');
    });
    
    var clQuickSearchTimeout;
    $('#clQuickSearchInput').on('input', function() {
        clearTimeout(clQuickSearchTimeout);
        clQuickSearchTimeout = setTimeout(filterContactListMappings, 300);
    });
    
    $(document).on('click', '.cl-remove-chip', function() {
        var filter = $(this).data('filter');
        if (filter === 'search') {
            $('#clQuickSearchInput').val('');
        } else if (filter === 'contactlist') {
            $('#clFilterContactList').val('');
        } else if (filter === 'date') {
            $('#clFilterDateFrom').val('');
            $('#clFilterDateTo').val('');
            $('.cl-date-preset').removeClass('active');
        }
        filterContactListMappings();
    });
    
    $('.cl-date-preset').on('click', function() {
        $('.cl-date-preset').removeClass('active');
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
        
        $('#clFilterDateFrom').val(fromDate);
        $('#clFilterDateTo').val(new Date().toISOString().split('T')[0]);
        filterContactListMappings();
    });
    
    $(document).on('click', '.copy-email', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var email = $(this).data('email');
        navigator.clipboard.writeText(email).then(function() {
            // TODO: Show toast notification
        });
    });
    
    $(document).on('click', '.archive-mapping', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        EmailToSmsService.archiveEmailToSmsContactListSetup(id).then(function(response) {
            if (response.success) {
                loadContactListSetups().then(function() {
                    filterContactListMappings();
                });
            }
        });
    });
    
    $(document).on('click', '.unarchive-mapping', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        EmailToSmsService.unarchiveEmailToSmsContactListSetup(id).then(function(response) {
            if (response.success) {
                loadContactListSetups().then(function() {
                    filterContactListMappings();
                });
            }
        });
    });
    
    // Contact List Mapping Create Modal Logic
    var clmAllowedEmails = [];
    var clmSelectedLists = [];
    var clmSelectedOptOuts = [];
    
    // Mock account setting for dynamic SenderID
    var clmAccountSettings = {
        dynamic_senderid_allowed: true
    };
    
    function openCreateContactListMappingModal() {
        clmEditingId = null;
        
        // Update modal title
        $('#createContactListMappingModal .modal-title').html('<i class="fas fa-link me-2 text-primary"></i>Create Email-to-SMS – Contact List');
        $('#btnSaveContactListMapping').html('<i class="fas fa-check me-1"></i> Save');
        
        // Reset form
        clmAllowedEmails = [];
        clmSelectedLists = [];
        clmSelectedOptOuts = [];
        
        $('#clmCreateName').val('').removeClass('is-invalid');
        $('#clmCreateDescription').val('');
        $('#clmCreateSubaccount').val('').removeClass('is-invalid');
        $('#clmCreateEmailInput').val('').removeClass('is-invalid');
        $('#clmEmailError').hide();
        $('#clmEmailTagsContainer').empty();
        $('#clmEmailCount').text('0');
        $('#clmClearAllEmails').hide();
        $('#clmWildcardWarning').addClass('d-none');
        
        // Reset Contact Lists multi-select
        $('.clm-contact-list-cb').prop('checked', false);
        $('#clmContactListsDropdown .dropdown-label').text('Select list(s)...');
        $('#clmContactListsDropdown button').removeClass('is-invalid');
        $('#clmContactListsError').hide();
        $('#clmSelectedListsDisplay').empty();
        
        // Reset Opt-out multi-select
        $('.clm-optout-cb').prop('checked', false);
        $('#clmOptNo').prop('checked', true);
        $('#clmOptOutDropdown .dropdown-label').text('No opt-out list');
        $('#clmSelectedOptOutsDisplay').empty();
        
        $('#clmCreateSenderId').val('').removeClass('is-invalid');
        $('#clmCreateSubjectAsSenderId').prop('checked', false);
        $('#clmCreateMultipleSms').prop('checked', false);
        $('#clmCreateDeliveryReports').prop('checked', false);
        $('#clmCreateDeliveryEmail').val('').removeClass('is-invalid');
        $('#clmDeliveryEmailError').hide();
        $('#clmDeliveryEmailGroup').hide();
        $('#clmCreateSignatureFilter').val('').removeClass('is-invalid');
        $('#clmSignatureFilterError').hide();
        
        // Show/hide Subject as SenderID based on account setting
        if (clmAccountSettings.dynamic_senderid_allowed) {
            $('#clmSubjectAsSenderIdGroup').show();
        } else {
            $('#clmSubjectAsSenderIdGroup').hide();
        }
        
        var modal = new bootstrap.Modal(document.getElementById('createContactListMappingModal'));
        modal.show();
    }
    
    $('#btnCreateContactListMapping').on('click', function() {
        window.location.href = '{{ route("management.email-to-sms.create-mapping") }}';
    });
    
    // Email chip input for Contact List modal
    function clmAddAllowedEmail() {
        var input = $('#clmCreateEmailInput');
        var email = input.val().trim().toLowerCase();
        var errorEl = $('#clmEmailError');
        
        if (!email) return;
        
        var validation = EmailToSmsService.validateEmail(email);
        if (!validation.valid) {
            errorEl.text('Invalid email format. Use email@domain.com or *@domain.com for wildcards.').show();
            input.addClass('is-invalid');
            return;
        }
        
        if (clmAllowedEmails.includes(email)) {
            errorEl.text('This email has already been added.').show();
            input.addClass('is-invalid');
            return;
        }
        
        errorEl.hide();
        input.removeClass('is-invalid');
        
        clmAllowedEmails.push(email);
        input.val('');
        clmRenderEmailTags();
        clmUpdateWildcardWarning();
    }
    
    function clmRemoveAllowedEmail(email) {
        var index = clmAllowedEmails.indexOf(email);
        if (index > -1) {
            clmAllowedEmails.splice(index, 1);
            clmRenderEmailTags();
            clmUpdateWildcardWarning();
        }
    }
    
    function clmRenderEmailTags() {
        var container = $('#clmEmailTagsContainer');
        container.empty();
        
        clmAllowedEmails.forEach(function(email) {
            var isWildcard = email.startsWith('*@');
            var tag = $('<span class="email-tag' + (isWildcard ? ' email-tag-wildcard' : '') + '">' +
                        '<span class="email-text">' + escapeHtml(email) + '</span>' +
                        '<span class="remove-email" data-email="' + escapeHtml(email) + '">&times;</span>' +
                        '</span>');
            container.append(tag);
        });
        
        $('#clmEmailCount').text(clmAllowedEmails.length);
        
        if (clmAllowedEmails.length > 0) {
            $('#clmClearAllEmails').show();
        } else {
            $('#clmClearAllEmails').hide();
        }
    }
    
    function clmClearAllEmails() {
        clmAllowedEmails = [];
        clmRenderEmailTags();
        clmUpdateWildcardWarning();
    }
    
    function clmUpdateWildcardWarning() {
        var hasWildcard = clmAllowedEmails.some(function(email) {
            return email.startsWith('*@');
        });
        
        if (hasWildcard) {
            $('#clmWildcardWarning').removeClass('d-none');
        } else {
            $('#clmWildcardWarning').addClass('d-none');
        }
    }
    
    $('#clmAddEmailBtn').on('click', clmAddAllowedEmail);
    
    $('#clmCreateEmailInput').on('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            clmAddAllowedEmail();
        }
    });
    
    $('#clmClearAllEmails').on('click', clmClearAllEmails);
    
    $(document).on('click', '#clmEmailTagsContainer .remove-email', function() {
        var email = $(this).data('email');
        clmRemoveAllowedEmail(email);
    });
    
    // Contact Lists multi-select handlers
    $('.clm-contact-list-cb').on('change', function() {
        clmUpdateSelectedLists();
    });
    
    $('.clm-select-all-lists').on('click', function(e) {
        e.preventDefault();
        $('.clm-contact-list-cb').prop('checked', true);
        clmUpdateSelectedLists();
    });
    
    $('.clm-clear-all-lists').on('click', function(e) {
        e.preventDefault();
        $('.clm-contact-list-cb').prop('checked', false);
        clmUpdateSelectedLists();
    });
    
    function clmUpdateSelectedLists() {
        clmSelectedLists = [];
        $('.clm-contact-list-cb:checked').each(function() {
            clmSelectedLists.push({
                value: $(this).val(),
                label: $(this).next('label').text()
            });
        });
        
        if (clmSelectedLists.length === 0) {
            $('#clmContactListsDropdown .dropdown-label').text('Select list(s)...');
        } else if (clmSelectedLists.length === 1) {
            $('#clmContactListsDropdown .dropdown-label').text(clmSelectedLists[0].label);
        } else {
            $('#clmContactListsDropdown .dropdown-label').text(clmSelectedLists.length + ' lists selected');
        }
        
        // Render selected tags
        var display = $('#clmSelectedListsDisplay');
        display.empty();
        clmSelectedLists.forEach(function(list) {
            display.append('<span class="badge bg-primary-light text-primary me-1 mb-1">' + escapeHtml(list.label) + '</span>');
        });
        
        // Remove validation error
        if (clmSelectedLists.length > 0) {
            $('#clmContactListsDropdown button').removeClass('is-invalid');
        }
    }
    
    // Opt-out multi-select handlers
    $('.clm-optout-cb').on('change', function() {
        var clickedValue = $(this).val();
        
        // If "No" is selected, uncheck all others
        if (clickedValue === 'no' && $(this).is(':checked')) {
            $('.clm-optout-cb').not(this).prop('checked', false);
        } else if ($(this).is(':checked')) {
            // If any other is selected, uncheck "No"
            $('#clmOptNo').prop('checked', false);
        }
        
        // If nothing is selected, default to "No"
        if ($('.clm-optout-cb:checked').length === 0) {
            $('#clmOptNo').prop('checked', true);
        }
        
        clmUpdateSelectedOptOuts();
    });
    
    $('.clm-select-all-optouts').on('click', function(e) {
        e.preventDefault();
        $('#clmOptNo').prop('checked', false);
        $('.clm-optout-cb').not('#clmOptNo').prop('checked', true);
        clmUpdateSelectedOptOuts();
    });
    
    $('.clm-clear-all-optouts').on('click', function(e) {
        e.preventDefault();
        $('.clm-optout-cb').prop('checked', false);
        $('#clmOptNo').prop('checked', true);
        clmUpdateSelectedOptOuts();
    });
    
    function clmUpdateSelectedOptOuts() {
        clmSelectedOptOuts = [];
        $('.clm-optout-cb:checked').not('#clmOptNo').each(function() {
            clmSelectedOptOuts.push({
                value: $(this).val(),
                label: $(this).next('label').text()
            });
        });
        
        if ($('#clmOptNo').is(':checked') || clmSelectedOptOuts.length === 0) {
            $('#clmOptOutDropdown .dropdown-label').text('No opt-out list');
        } else if (clmSelectedOptOuts.length === 1) {
            $('#clmOptOutDropdown .dropdown-label').text(clmSelectedOptOuts[0].label);
        } else {
            $('#clmOptOutDropdown .dropdown-label').text(clmSelectedOptOuts.length + ' lists selected');
        }
        
        // Render selected tags
        var display = $('#clmSelectedOptOutsDisplay');
        display.empty();
        clmSelectedOptOuts.forEach(function(list) {
            display.append('<span class="badge bg-warning-light text-warning me-1 mb-1">' + escapeHtml(list.label) + '</span>');
        });
    }
    
    // Toggle delivery reports email field
    $('#clmCreateDeliveryReports').on('change', function() {
        if ($(this).is(':checked')) {
            $('#clmDeliveryEmailGroup').slideDown(200);
        } else {
            $('#clmDeliveryEmailGroup').slideUp(200);
            $('#clmCreateDeliveryEmail').val('').removeClass('is-invalid');
        }
    });
    
    // Save Contact List Mapping
    $('#btnSaveContactListMapping').on('click', function() {
        var isValid = true;
        var hasWildcard = false;
        
        // Validate name
        var name = $('#clmCreateName').val().trim();
        if (!name) {
            $('#clmCreateName').addClass('is-invalid');
            isValid = false;
        } else {
            $('#clmCreateName').removeClass('is-invalid');
        }
        
        // Validate subaccount
        var subaccount = $('#clmCreateSubaccount').val();
        if (!subaccount) {
            $('#clmCreateSubaccount').addClass('is-invalid');
            isValid = false;
        } else {
            $('#clmCreateSubaccount').removeClass('is-invalid');
        }
        
        // Validate Contact Lists selection (must select >=1)
        if (clmSelectedLists.length === 0) {
            $('#clmContactListsDropdown button').addClass('is-invalid');
            $('#clmContactListsError').text('Please select at least one Contact Book list.').show();
            isValid = false;
        } else {
            $('#clmContactListsDropdown button').removeClass('is-invalid');
            $('#clmContactListsError').hide();
        }
        
        // Validate allowed sender emails for invalid entries and check for wildcards
        var hasInvalidEmail = false;
        clmAllowedEmails.forEach(function(email) {
            var validation = EmailToSmsService.validateEmail(email);
            if (!validation.valid) {
                hasInvalidEmail = true;
            }
            if (validation.isWildcard) {
                hasWildcard = true;
            }
        });
        
        if (hasInvalidEmail) {
            $('#clmEmailError').text('One or more allowed sender emails are invalid. Please remove and re-add them.').show();
            isValid = false;
        } else {
            $('#clmEmailError').hide();
        }
        
        // Show wildcard warning (non-blocking)
        if (hasWildcard) {
            $('#clmWildcardWarning').removeClass('d-none');
        } else {
            $('#clmWildcardWarning').addClass('d-none');
        }
        
        // Validate SenderID
        var senderId = $('#clmCreateSenderId').val();
        if (!senderId) {
            $('#clmCreateSenderId').addClass('is-invalid');
            isValid = false;
        } else {
            $('#clmCreateSenderId').removeClass('is-invalid');
        }
        
        // Validate delivery email if reports enabled
        if ($('#clmCreateDeliveryReports').is(':checked')) {
            var deliveryEmail = $('#clmCreateDeliveryEmail').val().trim();
            var emailValidation = EmailToSmsService.validateEmail(deliveryEmail);
            if (!deliveryEmail || !emailValidation.valid || emailValidation.isWildcard) {
                $('#clmCreateDeliveryEmail').addClass('is-invalid');
                $('#clmDeliveryEmailError').text(deliveryEmail ? 'Please enter a valid email address (wildcards not allowed).' : 'Delivery reports email is required.').show();
                isValid = false;
            } else {
                $('#clmCreateDeliveryEmail').removeClass('is-invalid');
                $('#clmDeliveryEmailError').hide();
            }
        } else {
            $('#clmCreateDeliveryEmail').removeClass('is-invalid');
            $('#clmDeliveryEmailError').hide();
        }
        
        // Validate content filter regex
        var contentFilter = $('#clmCreateSignatureFilter').val().trim();
        var regexValidation = EmailToSmsService.validateContentFilterRegex(contentFilter);
        if (!regexValidation.valid) {
            $('#clmCreateSignatureFilter').addClass('is-invalid');
            $('#clmSignatureFilterError').text(regexValidation.error).show();
            isValid = false;
        } else {
            $('#clmCreateSignatureFilter').removeClass('is-invalid');
            $('#clmSignatureFilterError').hide();
        }
        
        if (!isValid) {
            return;
        }
        
        // Build payload for service layer
        var listNames = clmSelectedLists.map(function(l) { return l.label; });
        var listIds = clmSelectedLists.map(function(l) { return l.value; });
        var optOutNames = clmSelectedOptOuts.filter(function(o) { return o.value !== 'NO'; }).map(function(o) { return o.label; });
        var optOutIds = clmSelectedOptOuts.filter(function(o) { return o.value !== 'NO'; }).map(function(o) { return o.value; });
        
        var payload = {
            name: name,
            description: $('#clmCreateDescription').val().trim(),
            subaccountId: subaccount,
            allowedSenderEmails: clmAllowedEmails.slice(),
            contactBookListIds: listIds,
            contactBookListNames: listNames,
            optOutMode: optOutIds.length === 0 ? 'NONE' : 'SELECTED',
            optOutListIds: optOutIds,
            optOutListNames: optOutNames,
            senderIdTemplateId: $('#clmCreateSenderId').val(),
            senderId: $('#clmCreateSenderId option:selected').text(),
            subjectOverridesSenderId: $('#clmCreateSubjectAsSenderId').is(':checked'),
            multipleSmsEnabled: $('#clmCreateMultipleSms').is(':checked'),
            deliveryReportsEnabled: $('#clmCreateDeliveryReports').is(':checked'),
            deliveryReportsEmail: $('#clmCreateDeliveryEmail').val().trim(),
            contentFilter: contentFilter
        };
        
        var savePromise;
        if (clmEditingId) {
            // Edit mode: update existing record via service
            savePromise = EmailToSmsService.updateEmailToSmsContactListSetup(clmEditingId, payload);
        } else {
            // Create mode: add new record via service
            savePromise = EmailToSmsService.createEmailToSmsContactListSetup(payload);
        }
        
        savePromise.then(function(response) {
            if (response.success) {
                console.log(clmEditingId ? 'Updated' : 'Created', 'Contact List Mapping:', response.data);
                clmEditingId = null;
                
                // Close modal
                bootstrap.Modal.getInstance(document.getElementById('createContactListMappingModal')).hide();
                
                // Refresh table from service
                loadContactListSetups().then(function() {
                    filterContactListMappings();
                });
            } else {
                alert('Error: ' + (response.error || 'Failed to save setup'));
            }
        }).catch(function(error) {
            console.error('Save failed:', error);
            alert('Error saving setup. Please try again.');
        });
    });
    
    (function() {
        var urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('tab') === 'contact-lists') {
            $('#contact-lists-tab').tab('show');
        } else if (urlParams.get('tab') === 'standard') {
            $('#standard-tab').tab('show');
        }
        
        if (urlParams.get('created') === '1') {
            sessionStorage.removeItem('newMapping');
            window.history.replaceState({}, document.title, window.location.pathname);
            // Data will be reloaded from service
        }
    })();
    
    // Standard Email-to-SMS tab handlers
    var mockStandardSms = [
        {
            id: 'std-001',
            name: 'General Notifications',
            description: 'General purpose notification emails converted to SMS',
            subaccount: 'main',
            subaccountName: 'Main Account',
            allowedSenders: ['admin@company.com', 'system@company.com', 'notifications@company.com'],
            senderId: 'QuickSMS',
            subjectAsSenderId: false,
            multipleSms: true,
            deliveryReports: true,
            deliveryEmail: 'reports@company.com',
            signatureFilter: '--\n.*\nSent from my iPhone',
            created: '2024-10-20',
            lastUpdated: '2025-01-09',
            archived: false
        },
        {
            id: 'std-002',
            name: 'Urgent Alerts',
            description: 'High priority alerts requiring immediate attention',
            subaccount: 'marketing',
            subaccountName: 'Marketing Team',
            allowedSenders: ['alerts@marketing.com'],
            senderId: 'ALERTS',
            subjectAsSenderId: false,
            multipleSms: false,
            deliveryReports: false,
            deliveryEmail: '',
            signatureFilter: '',
            created: '2024-11-05',
            lastUpdated: '2025-01-08',
            archived: false
        },
        {
            id: 'std-003',
            name: 'Patient Communications',
            description: 'NHS Trust patient communication system',
            subaccount: 'support',
            subaccountName: 'Support Team',
            allowedSenders: ['*@nhstrust.nhs.uk'],
            senderId: 'NHS',
            subjectAsSenderId: true,
            multipleSms: true,
            deliveryReports: true,
            deliveryEmail: 'nhs-reports@support.com',
            signatureFilter: 'Kind regards,\n.*',
            created: '2024-11-18',
            lastUpdated: '2025-01-07',
            archived: false
        },
        {
            id: 'std-004',
            name: 'Appointment Reminders',
            description: 'Clinic appointment reminder system',
            subaccount: 'main',
            subaccountName: 'Main Account',
            allowedSenders: ['bookings@clinic.com', 'reception@clinic.com'],
            senderId: 'Pharmacy',
            subjectAsSenderId: false,
            multipleSms: false,
            deliveryReports: false,
            deliveryEmail: '',
            signatureFilter: '',
            created: '2024-12-01',
            lastUpdated: '2025-01-06',
            archived: false
        },
        {
            id: 'std-005',
            name: 'Delivery Updates',
            description: 'Shipping and delivery notification emails',
            subaccount: 'marketing',
            subaccountName: 'Marketing Team',
            allowedSenders: [],
            senderId: 'INFO',
            subjectAsSenderId: false,
            multipleSms: true,
            deliveryReports: false,
            deliveryEmail: '',
            signatureFilter: '',
            created: '2024-12-10',
            lastUpdated: '2025-01-05',
            archived: false
        },
        {
            id: 'std-006',
            name: 'Internal Testing',
            description: 'Development and QA testing setup',
            subaccount: 'support',
            subaccountName: 'Support Team',
            allowedSenders: ['dev@quicksms.io', 'qa@quicksms.io', 'test@quicksms.io', 'staging@quicksms.io'],
            senderId: 'QuickSMS',
            subjectAsSenderId: false,
            multipleSms: true,
            deliveryReports: true,
            deliveryEmail: 'dev-team@quicksms.io',
            signatureFilter: '',
            created: '2024-12-15',
            lastUpdated: '2025-01-04',
            archived: false
        },
        {
            id: 'std-007',
            name: 'Legacy Alerts',
            description: 'Old alerting system - archived',
            subaccount: 'main',
            subaccountName: 'Main Account',
            allowedSenders: ['old-system@company.com'],
            senderId: 'ALERTS',
            subjectAsSenderId: false,
            multipleSms: false,
            deliveryReports: false,
            deliveryEmail: '',
            signatureFilter: '',
            created: '2024-06-01',
            lastUpdated: '2024-09-15',
            archived: true
        }
    ];
    
    var stdShowArchived = false;
    var stdEditingId = null;
    
    function formatAllowedSenders(senders) {
        if (!senders || senders.length === 0) {
            return '<span class="text-muted">All senders allowed</span>';
        }
        
        var maxDisplay = 2;
        var displayed = senders.slice(0, maxDisplay);
        var remaining = senders.length - maxDisplay;
        
        var html = displayed.map(function(email) {
            return '<span class="d-block text-truncate" style="max-width: 250px;" title="' + email + '">' + email + '</span>';
        }).join('');
        
        if (remaining > 0) {
            html += '<span class="text-muted small">+' + remaining + ' more</span>';
        }
        
        return html;
    }
    
    function getFilteredStandardSms() {
        return mockStandardSms.filter(function(item) {
            if (!stdShowArchived && item.archived) {
                return false;
            }
            return true;
        });
    }
    
    function findStandardSmsById(id) {
        return mockStandardSms.find(function(item) { return item.id === id; });
    }
    
    function renderStandardSmsTable(items) {
        var tbody = $('#standardSmsTableBody');
        tbody.empty();
        
        // Apply archived filter
        var filteredItems = items.filter(function(item) {
            if (!stdShowArchived && item.archived) {
                return false;
            }
            return true;
        });
        
        if (filteredItems.length === 0) {
            $('#standardSmsTableContainer').hide();
            $('#emptyStateStandardSms').show();
            $('#stdShowingCount').text(0);
            $('#stdTotalCount').text(getFilteredStandardSms().length);
            return;
        }
        
        $('#standardSmsTableContainer').show();
        $('#emptyStateStandardSms').hide();
        
        filteredItems.forEach(function(item) {
            var allowedSendersHtml = formatAllowedSenders(item.allowedSenders);
            var archivedBadge = item.archived ? ' <span class="badge bg-secondary">Archived</span>' : '';
            
            var statusBadge = '';
            if (item.archived) {
                statusBadge = '<span class="badge bg-secondary">Archived</span>';
            } else if (item.status === 'draft') {
                statusBadge = '<span class="badge bg-warning text-dark">Draft</span>';
            } else if (item.status === 'suspended') {
                statusBadge = '<span class="badge badge-suspended">Suspended</span>';
            } else {
                statusBadge = '<span class="badge badge-live-status">Live</span>';
            }
            
            var suspendReactivateAction = '';
            if (!item.archived) {
                if (item.status === 'suspended') {
                    suspendReactivateAction = '<li><a class="dropdown-item std-action-reactivate" href="#" data-id="' + item.id + '"><i class="fas fa-play-circle me-2"></i> Reactivate</a></li>';
                } else if (item.status === 'active') {
                    suspendReactivateAction = '<li><a class="dropdown-item std-action-suspend" href="#" data-id="' + item.id + '"><i class="fas fa-pause-circle me-2"></i> Suspend</a></li>';
                }
            }
            
            var row = '<tr data-id="' + item.id + '"' + (item.archived ? ' class="table-secondary"' : '') + '>' +
                '<td><span class="email-sms-name">' + escapeHtml(item.name) + '</span></td>' +
                '<td>' + escapeHtml(item.subaccountName) + '</td>' +
                '<td>' + allowedSendersHtml + '</td>' +
                '<td>' + statusBadge + '</td>' +
                '<td>' + item.created + '</td>' +
                '<td>' + item.lastUpdated + '</td>' +
                '<td class="text-end">' +
                    '<div class="dropdown">' +
                        '<button class="action-menu-btn" type="button" data-bs-toggle="dropdown" onclick="event.stopPropagation();">' +
                            '<i class="fas fa-ellipsis-v"></i>' +
                        '</button>' +
                        '<ul class="dropdown-menu dropdown-menu-end">' +
                            '<li><a class="dropdown-item std-action-view" href="#" data-id="' + item.id + '"><i class="fas fa-eye me-2"></i> View</a></li>' +
                            '<li><a class="dropdown-item" href="/management/email-to-sms/standard/' + item.id + '/edit"><i class="fas fa-edit me-2"></i> Edit</a></li>' +
                            suspendReactivateAction +
                            (item.archived 
                                ? '<li><a class="dropdown-item std-action-unarchive" href="#" data-id="' + item.id + '"><i class="fas fa-box-open me-2"></i> Unarchive</a></li>'
                                : '<li><a class="dropdown-item std-action-archive" href="#" data-id="' + item.id + '"><i class="fas fa-archive me-2"></i> Archive</a></li>') +
                        '</ul>' +
                    '</div>' +
                '</td>' +
            '</tr>';
            
            tbody.append(row);
        });
        
        $('#stdShowingCount').text(filteredItems.length);
        $('#stdTotalCount').text(getFilteredStandardSms().length);
        
        // Bind action handlers
        $('.std-action-view').off('click').on('click', function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            openStdViewDrawer(id);
        });
        
        
        $('.std-action-archive').off('click').on('click', function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            openStdArchiveModal(id);
        });
        
        $('.std-action-unarchive').off('click').on('click', function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            
            EmailToSmsService.unarchiveEmailToSmsSetup(id).then(function(response) {
                if (response.success) {
                    showSuccessToast('Setup unarchived successfully');
                    loadStandardSmsTable();
                } else {
                    showErrorToast(response.error || 'Failed to unarchive');
                }
            }).catch(function(err) {
                console.error('Unarchive error:', err);
                showErrorToast('An error occurred. Please try again.');
            });
        });
        
        $('.std-action-suspend').off('click').on('click', function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            
            EmailToSmsService.suspendEmailToSmsSetup(id).then(function(response) {
                if (response.success) {
                    showSuccessToast('Setup suspended successfully');
                    loadStandardSmsTable();
                } else {
                    showErrorToast(response.error || 'Failed to suspend');
                }
            }).catch(function(err) {
                console.error('Suspend error:', err);
                showErrorToast('An error occurred. Please try again.');
            });
        });
        
        $('.std-action-reactivate').off('click').on('click', function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            
            EmailToSmsService.reactivateEmailToSmsSetup(id).then(function(response) {
                if (response.success) {
                    showSuccessToast('Setup reactivated successfully');
                    loadStandardSmsTable();
                } else {
                    showErrorToast(response.error || 'Failed to reactivate');
                }
            }).catch(function(err) {
                console.error('Reactivate error:', err);
                showErrorToast('An error occurred. Please try again.');
            });
        });
    }
    
    function refreshStandardSmsTable() {
        var searchTerm = $('#stdQuickSearchInput').val().toLowerCase().trim();
        if (!searchTerm) {
            renderStandardSmsTable(mockStandardSms);
        } else {
            var filtered = mockStandardSms.filter(function(item) {
                return item.name.toLowerCase().indexOf(searchTerm) !== -1 ||
                       item.subaccountName.toLowerCase().indexOf(searchTerm) !== -1 ||
                       item.allowedSenders.some(function(email) {
                           return email.toLowerCase().indexOf(searchTerm) !== -1;
                       });
            });
            renderStandardSmsTable(filtered);
        }
    }
    
    // View Drawer Functions
    function openStdViewDrawer(id) {
        var item = findStandardSmsById(id);
        if (!item) return;
        
        $('#stdDrawerName').text(item.name);
        $('#stdDrawerDescription').text(item.description || '-');
        $('#stdDrawerSubaccount').text(item.subaccountName);
        
        if (item.allowedSenders && item.allowedSenders.length > 0) {
            $('#stdDrawerAllowedEmails').html(item.allowedSenders.map(function(e) {
                return '<span class="d-block">' + e + '</span>';
            }).join(''));
        } else {
            $('#stdDrawerAllowedEmails').html('<span class="text-muted">All senders allowed</span>');
        }
        
        $('#stdDrawerSenderId').text(item.senderId);
        $('#stdDrawerSubjectAsSenderId').text(item.subjectAsSenderId ? 'Enabled' : 'Disabled');
        $('#stdDrawerMultipleSms').text(item.multipleSms ? 'Enabled' : 'Disabled');
        $('#stdDrawerDeliveryReports').text(item.deliveryReports ? 'Enabled' : 'Disabled');
        
        if (item.deliveryReports && item.deliveryEmail) {
            $('#stdDrawerDeliveryEmailRow').show();
            $('#stdDrawerDeliveryEmail').text(item.deliveryEmail);
        } else {
            $('#stdDrawerDeliveryEmailRow').hide();
        }
        
        $('#stdDrawerSignatureFilter').text(item.signatureFilter || '-');
        $('#stdDrawerCreated').text(item.created);
        $('#stdDrawerLastUpdated').text(item.lastUpdated);
        
        // Store ID for edit button
        $('#stdDrawerEditBtn').data('id', id);
        
        // Open drawer
        $('#stdDetailsDrawer').addClass('open');
        $('#stdDrawerBackdrop').addClass('show');
    }
    
    function closeStdDrawer() {
        $('#stdDetailsDrawer').removeClass('open');
        $('#stdDrawerBackdrop').removeClass('show');
    }
    
    $('#stdCloseDrawerBtn').on('click', closeStdDrawer);
    $('#stdDrawerBackdrop').on('click', closeStdDrawer);
    
    $('#stdDrawerEditBtn').on('click', function() {
        var id = $(this).data('id');
        window.location.href = '/management/email-to-sms/standard/' + id + '/edit';
    });
    
    // Archive Modal Functions
    var stdArchiveTargetId = null;
    
    function openStdArchiveModal(id) {
        var item = findStandardSmsById(id);
        if (!item) return;
        
        stdArchiveTargetId = id;
        $('#stdArchiveName').text(item.name);
        
        var modal = new bootstrap.Modal(document.getElementById('stdArchiveModal'));
        modal.show();
    }
    
    $('#btnConfirmStdArchive').on('click', function() {
        if (stdArchiveTargetId) {
            var $btn = $(this);
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Archiving...');
            
            EmailToSmsService.archiveEmailToSmsSetup(stdArchiveTargetId).then(function(response) {
                if (response.success) {
                    loadStandardSmsTable();
                } else {
                    alert('Error: ' + (response.error || 'Failed to archive'));
                }
            }).catch(function(err) {
                console.error('Archive error:', err);
                alert('An error occurred. Please try again.');
            }).finally(function() {
                $btn.prop('disabled', false).html('<i class="fas fa-archive me-1"></i> Archive');
                stdArchiveTargetId = null;
                bootstrap.Modal.getInstance(document.getElementById('stdArchiveModal')).hide();
            });
        }
    });
    
    // Show Archived Toggle
    $('#stdShowArchived').on('change', function() {
        stdShowArchived = $(this).is(':checked');
        loadStandardSmsTable();
    });
    
    var stdSearchDebounce;
    $('#stdQuickSearchInput').on('input', function() {
        clearTimeout(stdSearchDebounce);
        stdSearchDebounce = setTimeout(function() {
            loadStandardSmsTable();
        }, 300);
    });
    
    // Initialize Standard Email-to-SMS table from service
    loadStandardSmsTable();
    
    // Load table data from service
    function loadStandardSmsTable() {
        var options = {
            includeArchived: stdShowArchived,
            search: $('#stdQuickSearchInput').val()
        };
        
        EmailToSmsService.listEmailToSmsSetups(options).then(function(response) {
            if (response.success) {
                mockStandardSms = response.data;
                renderStandardSmsTable(mockStandardSms);
            }
        });
    }
    
    // Email Parsing Test Logic
    function validateSenderIdFormat(senderId) {
        if (!senderId || senderId.trim() === '') {
            return { valid: false, reason: 'SenderID is required. Email subject cannot be empty.' };
        }
        
        var trimmed = senderId.trim();
        var alphanumeric = trimmed.replace(/[^a-zA-Z0-9]/g, '');
        
        if (alphanumeric.length === 0) {
            return { valid: false, reason: 'SenderID must contain at least one alphanumeric character.' };
        }
        
        if (alphanumeric.length < 3) {
            return { valid: false, reason: 'SenderID must be at least 3 characters after removing non-alphanumeric characters.' };
        }
        
        if (alphanumeric.length > 11) {
            alphanumeric = alphanumeric.substring(0, 11);
        }
        
        return { valid: true, senderId: alphanumeric, reason: 'Valid SenderID extracted.' };
    }
    
    function extractPlainTextFromBody(body) {
        if (!body || body.trim() === '') {
            return { valid: false, content: '', reason: 'Message body is empty. SMS content is required.' };
        }
        
        var content = body.trim();
        
        // Check if body contains HTML and extract plain text
        if (/<[^>]+>/.test(content)) {
            var tempDiv = document.createElement('div');
            tempDiv.innerHTML = content;
            content = tempDiv.textContent || tempDiv.innerText || '';
            content = content.trim();
        }
        
        // Apply signature removal patterns from config
        var signaturePatterns = $('#configSignatureRemoval').val();
        if (signaturePatterns) {
            var patterns = signaturePatterns.split('\n').filter(function(p) { return p.trim() !== ''; });
            patterns.forEach(function(pattern) {
                try {
                    var regex = new RegExp(pattern, 'gm');
                    content = content.replace(regex, '');
                } catch (e) {
                    // Invalid regex, skip
                }
            });
            content = content.trim();
        }
        
        if (content === '') {
            return { valid: false, content: '', reason: 'Message body is empty after processing.' };
        }
        
        return { valid: true, content: content, reason: 'Content extracted successfully.' };
    }
    
    function parseEmailForSms(subject, body) {
        var result = {
            senderIdExtraction: null,
            contentExtraction: null,
            deliveryStatus: 'rejected',
            rejectionReason: null
        };
        
        // Check configuration settings
        var fixedSenderId = $('#configFixedSenderId').is(':checked');
        var selectedSenderId = $('#configSenderIdSelector').val();
        var subjectAsSenderId = $('#configSubjectAsSenderId').is(':checked');
        
        // Step 1: Resolve SenderID
        if (fixedSenderId && selectedSenderId) {
            result.senderIdExtraction = { 
                valid: true, 
                senderId: selectedSenderId, 
                reason: 'Using fixed SenderID from configuration.' 
            };
        } else if (subjectAsSenderId || !fixedSenderId) {
            result.senderIdExtraction = validateSenderIdFormat(subject);
        } else {
            result.senderIdExtraction = { 
                valid: false, 
                senderId: null, 
                reason: 'Fixed SenderID enabled but no SenderID selected.' 
            };
        }
        
        // Step 2: Extract content
        result.contentExtraction = extractPlainTextFromBody(body);
        
        // Step 3: Determine delivery status
        if (!result.senderIdExtraction.valid) {
            result.deliveryStatus = 'rejected';
            result.rejectionReason = 'Invalid SenderID: ' + result.senderIdExtraction.reason;
        } else if (!result.contentExtraction.valid) {
            result.deliveryStatus = 'rejected';
            result.rejectionReason = 'Invalid content: ' + result.contentExtraction.reason;
        } else {
            result.deliveryStatus = 'accepted';
            result.rejectionReason = null;
        }
        
        return result;
    }
    
    $('#btnTestParsing').on('click', function() {
        var subject = $('#testEmailSubject').val();
        var body = $('#testEmailBody').val();
        
        var result = parseEmailForSms(subject, body);
        
        // Display results
        $('#parsingResultContainer').show();
        
        // SenderID
        if (result.senderIdExtraction.valid) {
            $('#parsedSenderId').html('<code>' + result.senderIdExtraction.senderId + '</code> <small class="text-muted">(' + result.senderIdExtraction.reason + ')</small>');
            $('#parsedSenderIdValid').html('<span class="badge badge-live-status">Valid</span>');
        } else {
            $('#parsedSenderId').html('<em class="text-muted">-</em>');
            $('#parsedSenderIdValid').html('<span class="badge badge-suspended">Invalid</span> <small class="text-danger">' + result.senderIdExtraction.reason + '</small>');
        }
        
        // Content
        if (result.contentExtraction.valid) {
            var content = result.contentExtraction.content;
            var displayContent = content.length > 160 ? content.substring(0, 160) + '...' : content;
            $('#parsedContent').html('<span class="font-monospace small">' + displayContent.replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</span>');
            $('#parsedCharCount').html(content.length + ' characters' + (content.length > 160 ? ' <small class="text-muted">(multipart SMS)</small>' : ''));
        } else {
            $('#parsedContent').html('<em class="text-muted">-</em>');
            $('#parsedCharCount').html('<em class="text-muted">-</em>');
        }
        
        // Delivery Status
        if (result.deliveryStatus === 'accepted') {
            $('#parsedDeliveryStatus').html('<span class="badge badge-live-status">Will be Delivered</span>');
            $('#parsingResultAlert').removeClass('alert-danger').addClass('alert-success');
            $('#parsingResultAlert').html('<i class="fas fa-check-circle me-2"></i><strong>Email will be processed.</strong> SMS will be sent to the linked Contact List.');
        } else {
            $('#parsedDeliveryStatus').html('<span class="badge badge-suspended">Rejected</span>');
            $('#parsingResultAlert').removeClass('alert-success').addClass('alert-danger');
            $('#parsingResultAlert').html('<i class="fas fa-times-circle me-2"></i><strong>Email will be rejected.</strong> ' + result.rejectionReason + '<br><small class="text-muted mt-1 d-block">Sender will be notified of the rejection via email.</small>');
        }
        
        // Scroll to results
        $('html, body').animate({
            scrollTop: $('#parsingResultContainer').offset().top - 100
        }, 300);
    });
    
    // Clear parsing results when inputs change
    $('#testEmailSubject, #testEmailBody').on('input', function() {
        // Don't hide results, just indicate they may be stale
    });
});

function copyToClipboard(textOrElementId) {
    var text;
    var element = document.getElementById(textOrElementId);
    if (element) {
        text = element.textContent;
    } else {
        text = textOrElementId;
    }
    navigator.clipboard.writeText(text).then(function() {
        var btn = document.querySelector('[onclick="copyToClipboard(\'' + textOrElementId.replace(/'/g, "\\'") + '\')"]');
        if (btn) {
            var originalIcon = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check text-success"></i>';
            setTimeout(function() {
                btn.innerHTML = originalIcon;
            }, 1500);
        }
    });
}
</script>
@endpush
