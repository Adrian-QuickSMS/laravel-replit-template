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
#configuration textarea.form-control {
    min-height: auto;
    height: auto;
}
#configuration .card-body {
    padding: 1rem;
}
#configuration .card-header {
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
#createStandardSmsModal .modal-header {
    border-bottom: 1px solid #e9ecef;
    background: #fff;
    padding: 1rem 1.5rem;
}
#createStandardSmsModal .modal-body {
    background: #f8f9fa;
    padding: 2rem;
    overflow-y: auto;
}
#createStandardSmsModal .modal-footer {
    border-top: 1px solid #e9ecef;
    background: #fff;
    padding: 1rem 1.5rem;
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
                            <button class="nav-link" id="contact-lists-tab" data-bs-toggle="tab" data-bs-target="#contact-lists" type="button" role="tab">
                                <i class="fas fa-link me-1"></i> Email-to-SMS – Contact List
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="reporting-groups-tab" data-bs-toggle="tab" data-bs-target="#reporting-groups" type="button" role="tab">
                                <i class="fas fa-layer-group me-1"></i> Reporting Groups
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="configuration-tab" data-bs-toggle="tab" data-bs-target="#configuration" type="button" role="tab">
                                <i class="fas fa-envelope-open-text me-1"></i> Standard Email-to-SMS
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
                                    <input type="text" class="form-control" id="quickSearchInput" placeholder="Quick search by name or email address...">
                                </div>
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
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <p class="text-muted mb-0">Map email addresses to Contact Book Lists. When an email is received, SMS is sent to all recipients in the linked Contact List.</p>
                                </div>
                                <button type="button" class="btn btn-primary" id="btnCreateContactListMapping">
                                    <i class="fas fa-plus me-1"></i> Create
                                </button>
                            </div>
                            
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
                            
                            <div class="d-flex justify-content-between align-items-center mb-3" id="clActiveFiltersContainer" style="display: none;">
                                <div id="clActiveFiltersChips"></div>
                                <button type="button" class="btn btn-link btn-sm text-danger" id="btnClearClFilters">Clear All</button>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="d-flex align-items-center gap-2">
                                    <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#clFiltersPanel">
                                        <i class="fas fa-filter me-1"></i> Filters
                                    </button>
                                    <div class="input-group" style="width: 280px;">
                                        <span class="input-group-text bg-transparent"><i class="fas fa-search"></i></span>
                                        <input type="text" class="form-control" id="clQuickSearchInput" placeholder="Quick search by address...">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="table-container" id="contactListsTableContainer">
                                <div class="table-responsive">
                                    <table class="table email-sms-table mb-0">
                                        <thead>
                                            <tr>
                                                <th>Email-to-SMS Address</th>
                                                <th>Linked Contact List</th>
                                                <th>Recipients</th>
                                                <th>Allowed Senders</th>
                                                <th>Last Used</th>
                                                <th>Created</th>
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
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="stdShowArchived">
                                    <label class="form-check-label text-muted small" for="stdShowArchived">Show archived</label>
                                </div>
                                <button class="btn btn-primary" id="btnCreateStandardSms">
                                    <i class="fas fa-plus me-1"></i> Create
                                </button>
                            </div>
                            
                            <div class="search-container mb-3">
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent border-end-0"><i class="fas fa-search text-muted"></i></span>
                                    <input type="text" class="form-control border-start-0" id="stdQuickSearchInput" placeholder="Quick search by name or email address...">
                                </div>
                            </div>
                            
                            <div class="table-container" id="standardSmsTableContainer">
                                <div class="table-responsive">
                                    <table class="email-sms-table">
                                        <thead>
                                            <tr>
                                                <th style="width: 20%;">Name</th>
                                                <th style="width: 15%;">Subaccount</th>
                                                <th style="width: 30%;">Allowed Sender Emails</th>
                                                <th style="width: 12%;">Created</th>
                                                <th style="width: 12%;">Last Updated</th>
                                                <th class="text-end" style="width: 11%;">Actions</th>
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
                                <button class="btn btn-primary" id="btnCreateStandardSmsEmpty">
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

<div class="modal fade" id="createStandardSmsModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-semibold"><i class="fas fa-envelope me-2 text-primary"></i>Create Standard Email-to-SMS</h5>
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
                                    <input type="text" class="form-control" id="stdCreateName" placeholder="e.g., Appointment Reminders">
                                    <div class="invalid-feedback">Name is required.</div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Description</label>
                                    <input type="text" class="form-control" id="stdCreateDescription" placeholder="Optional description...">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Subaccount <span class="text-danger">*</span></label>
                                    <select class="form-select" id="stdCreateSubaccount">
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
                                    <input type="email" class="form-control" id="stdCreateEmailInput" placeholder="email@example.com or *@domain.com">
                                    <button class="btn btn-primary" type="button" id="stdAddEmailBtn">
                                        <i class="fas fa-plus me-1"></i> Add
                                    </button>
                                </div>
                                <div class="invalid-feedback" id="stdEmailError" style="display: none;">Invalid email format.</div>
                                <div id="stdEmailTagsContainer" class="email-tags-container"></div>
                                <div class="d-flex justify-content-between align-items-center mt-1">
                                    <small class="text-muted"><span id="stdEmailCount">0</span> email(s) added</small>
                                    <button type="button" class="btn btn-link btn-sm text-danger p-0" id="stdClearAllEmails" style="display: none;">
                                        <i class="fas fa-trash-alt me-1"></i> Clear All
                                    </button>
                                </div>
                            </div>
                            
                            <div id="stdWildcardWarning" class="alert alert-warning d-none" style="font-size: 0.85rem;">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Warning:</strong> Wildcard domains are less secure and may result in unintended messages being sent.
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
                                    <select class="form-select" id="stdCreateSenderId">
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
                                
                                <div class="col-md-6" id="stdSubjectAsSenderIdGroup" style="display: none;">
                                    <label class="form-label fw-semibold">Subject as SenderID</label>
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" id="stdCreateSubjectAsSenderId">
                                        <label class="form-check-label" for="stdCreateSubjectAsSenderId">
                                            Extract SenderID from email subject
                                        </label>
                                    </div>
                                    <small class="text-muted">When enabled, the SenderID is extracted from the email subject line.</small>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Enable Multiple SMS</label>
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" id="stdCreateMultipleSms">
                                        <label class="form-check-label" for="stdCreateMultipleSms">
                                            Allow multipart SMS messages
                                        </label>
                                    </div>
                                    <small class="text-muted">Messages over 160 characters will be sent as multiple parts.</small>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Send Delivery Reports</label>
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" id="stdCreateDeliveryReports">
                                        <label class="form-check-label" for="stdCreateDeliveryReports">
                                            Enable delivery report notifications
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="col-md-6" id="stdDeliveryEmailGroup" style="display: none;">
                                    <label class="form-label fw-semibold">Delivery Reports Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="stdCreateDeliveryEmail" placeholder="reports@yourcompany.com">
                                    <small class="text-muted">Email address to receive delivery status reports.</small>
                                    <div class="invalid-feedback">Valid email is required for delivery reports.</div>
                                </div>
                                
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Filter Content (Signature Removal)</label>
                                    <textarea class="form-control" id="stdCreateSignatureFilter" rows="3" placeholder="e.g., --\n.*\nSent from.*"></textarea>
                                    <div class="invalid-feedback" id="stdSignatureFilterError">Invalid regex pattern</div>
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
                <button type="button" class="btn btn-primary" id="btnSaveStandardSms">
                    <i class="fas fa-check me-1"></i> Save
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
@endsection

@push('scripts')
<script src="{{ asset('js/services/email-to-sms-service.js') }}"></script>
<script>
$(document).ready(function() {
    var EMAIL_DOMAIN = '@sms.quicksms.io';
    
    var mockAddresses = [
        {
            id: 'addr-001',
            name: 'Appointment Reminders',
            originatingEmails: ['appointments.12abc' + EMAIL_DOMAIN, 'appts.nhs' + EMAIL_DOMAIN],
            description: 'Automated appointment reminder notifications',
            type: 'Contact List',
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
            originatingEmails: ['prescriptions.45def' + EMAIL_DOMAIN],
            description: 'Notify patients when prescriptions are ready',
            type: 'Standard',
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
            originatingEmails: ['test.78ghi' + EMAIL_DOMAIN, 'test.dev' + EMAIL_DOMAIN, 'test.qa' + EMAIL_DOMAIN],
            description: 'Test address for development',
            type: 'Standard',
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
    
    var mockContactListMappings = [
        {
            id: 'clm-001',
            emailAddress: 'patients.abc123@sms.quicksms.io',
            contactListName: 'NHS Patients',
            contactListId: 'cl-001',
            recipientsCount: 4521,
            allowedSenders: ['admin@nhstrust.nhs.uk', 'appointments@nhstrust.nhs.uk'],
            lastUsed: '2025-01-09 08:45',
            created: '2024-10-15',
            status: 'Active'
        },
        {
            id: 'clm-002',
            emailAddress: 'pharmacy.def456@sms.quicksms.io',
            contactListName: 'Pharmacy Patients',
            contactListId: 'cl-002',
            recipientsCount: 1892,
            allowedSenders: ['pharmacy@clinic.com'],
            lastUsed: '2025-01-08 16:20',
            created: '2024-11-01',
            status: 'Active'
        },
        {
            id: 'clm-003',
            emailAddress: 'appointments.ghi789@sms.quicksms.io',
            contactListName: 'Appointment List',
            contactListId: 'cl-003',
            recipientsCount: 3267,
            allowedSenders: [],
            lastUsed: '2025-01-07 14:30',
            created: '2024-11-20',
            status: 'Active'
        },
        {
            id: 'clm-004',
            emailAddress: 'newsletter.jkl012@sms.quicksms.io',
            contactListName: 'Newsletter Subscribers',
            contactListId: 'cl-004',
            recipientsCount: 8934,
            allowedSenders: ['marketing@company.com', 'newsletter@company.com'],
            lastUsed: '2024-12-20 10:00',
            created: '2024-08-05',
            status: 'Archived'
        },
        {
            id: 'clm-005',
            emailAddress: 'alerts.mno345@sms.quicksms.io',
            contactListName: 'Emergency Contacts',
            contactListId: 'cl-005',
            recipientsCount: 156,
            allowedSenders: ['system@quicksms.io', 'alerts@quicksms.io', 'admin@quicksms.io'],
            lastUsed: '2025-01-09 11:22',
            created: '2024-12-01',
            status: 'Active'
        },
        {
            id: 'clm-006',
            emailAddress: 'reminders.pqr678@sms.quicksms.io',
            contactListName: 'NHS Patients',
            contactListId: 'cl-001',
            recipientsCount: 4521,
            allowedSenders: ['reminders@nhstrust.nhs.uk'],
            lastUsed: '2025-01-06 09:15',
            created: '2024-12-15',
            status: 'Active'
        }
    ];
    
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
        $('#rgTotalCount').text(mockReportingGroups.length);
    }
    
    function filterReportingGroups() {
        var filtered = mockReportingGroups.slice();
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
        renderReportingGroups(mockReportingGroups);
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
            var allowedDisplay = mapping.allowedSenders.length > 0 
                ? mapping.allowedSenders.slice(0, 2).map(function(s) { return '<span class="text-muted small me-1">' + s + '</span>'; }).join('') + (mapping.allowedSenders.length > 2 ? '<span class="text-muted small">+' + (mapping.allowedSenders.length - 2) + ' more</span>' : '')
                : '<span class="text-muted small">All senders allowed</span>';
            
            var statusClass = mapping.status === 'Active' ? '' : 'text-muted';
            
            var row = '<tr data-id="' + mapping.id + '" class="' + statusClass + '">' +
                '<td>' +
                    '<code class="email-address-display">' + mapping.emailAddress + '</code>' +
                    '<button class="btn btn-link btn-sm p-0 ms-2 copy-email" data-email="' + mapping.emailAddress + '" title="Copy to clipboard">' +
                        '<i class="fas fa-copy text-muted"></i>' +
                    '</button>' +
                '</td>' +
                '<td>' + mapping.contactListName + '</td>' +
                '<td>' + mapping.recipientsCount.toLocaleString() + '</td>' +
                '<td>' + allowedDisplay + '</td>' +
                '<td>' + mapping.lastUsed + '</td>' +
                '<td>' + mapping.created + '</td>' +
                '<td class="text-end">' +
                    '<div class="dropdown">' +
                        '<button class="action-menu-btn" type="button" data-bs-toggle="dropdown" onclick="event.stopPropagation();">' +
                            '<i class="fas fa-ellipsis-v"></i>' +
                        '</button>' +
                        '<ul class="dropdown-menu dropdown-menu-end">' +
                            '<li><a class="dropdown-item view-mapping" href="#" data-id="' + mapping.id + '"><i class="fas fa-eye me-2"></i> View</a></li>' +
                            '<li><a class="dropdown-item edit-mapping" href="#" data-id="' + mapping.id + '"><i class="fas fa-edit me-2"></i> Edit</a></li>' +
                            (mapping.status === 'Active' 
                                ? '<li><a class="dropdown-item archive-mapping" href="#" data-id="' + mapping.id + '"><i class="fas fa-archive me-2"></i> Archive</a></li>'
                                : '<li><a class="dropdown-item unarchive-mapping" href="#" data-id="' + mapping.id + '"><i class="fas fa-undo me-2"></i> Unarchive</a></li>') +
                        '</ul>' +
                    '</div>' +
                '</td>' +
            '</tr>';
            
            tbody.append(row);
        });
        
        $('#clShowingCount').text(mappings.length);
        $('#clTotalCount').text(mockContactListMappings.length);
    }
    
    function filterContactListMappings() {
        var filtered = mockContactListMappings.slice();
        var chips = [];
        
        var searchTerm = ($('#clQuickSearchInput').val() || '').toLowerCase().trim();
        if (searchTerm) {
            filtered = filtered.filter(function(m) {
                return m.emailAddress.toLowerCase().indexOf(searchTerm) !== -1 ||
                       m.contactListName.toLowerCase().indexOf(searchTerm) !== -1;
            });
            chips.push({ filter: 'search', value: 'Search: ' + searchTerm });
        }
        
        var contactListFilter = $('#clFilterContactList').val();
        if (contactListFilter) {
            filtered = filtered.filter(function(m) {
                return m.contactListName === contactListFilter;
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
        renderContactListMappings(mockContactListMappings);
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
        
        mockAddresses.unshift(newAddress);
        renderAddressesTable(mockAddresses);
        $('#createAddressModal').modal('hide');
        
        $('#createName, #createDescription, #createAllowedSenders, #createDailyLimit').val('');
        $('#createSubAccount, #createType, #createSenderId, #createOptOutList, #createReportingGroup').val('');
    });
    
    function getLinkedAddressNames() {
        var linkedNames = [];
        mockReportingGroups.forEach(function(group) {
            if (group.linkedAddresses && group.linkedAddresses.length > 0) {
                group.linkedAddresses.forEach(function(addrName) {
                    linkedNames.push(addrName);
                });
            }
        });
        return linkedNames;
    }
    
    function findGroupByAddressName(addressName) {
        return mockReportingGroups.find(function(g) {
            return g.linkedAddresses && g.linkedAddresses.indexOf(addressName) !== -1;
        });
    }
    
    function populateAddressDropdown() {
        var $dropdown = $('#rgAssignAddress');
        $dropdown.empty().append('<option value="">Select an address...</option>');
        
        var linkedNames = getLinkedAddressNames();
        
        mockAddresses.forEach(function(addr) {
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
        
        mockReportingGroups.push(newGroup);
        renderReportingGroups(mockReportingGroups);
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
    renderContactListMappings(mockContactListMappings);
    
    // Contact Lists tab handlers
    $('#btnApplyClFilters').on('click', filterContactListMappings);
    $('#btnResetClFilters').on('click', resetClFilters);
    $('#btnClearClFilters').on('click', resetClFilters);
    
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
        var mapping = mockContactListMappings.find(function(m) { return m.id === id; });
        if (mapping) {
            mapping.status = 'Archived';
            renderContactListMappings(mockContactListMappings);
        }
    });
    
    $(document).on('click', '.unarchive-mapping', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var mapping = mockContactListMappings.find(function(m) { return m.id === id; });
        if (mapping) {
            mapping.status = 'Active';
            renderContactListMappings(mockContactListMappings);
        }
    });
    
    $('#btnCreateMapping, #btnCreateMappingEmpty').on('click', function() {
        window.location.href = '{{ route("management.email-to-sms.create-mapping") }}';
    });
    
    (function() {
        var urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('tab') === 'contact-lists') {
            $('#contact-lists-tab').tab('show');
        }
        
        if (urlParams.get('created') === '1') {
            var newMappingStr = sessionStorage.getItem('newMapping');
            if (newMappingStr) {
                try {
                    var newMapping = JSON.parse(newMappingStr);
                    mockContactListMappings.unshift(newMapping);
                    renderContactListMappings(mockContactListMappings);
                    sessionStorage.removeItem('newMapping');
                    
                    window.history.replaceState({}, document.title, window.location.pathname);
                } catch (e) {
                    console.error('Failed to parse new mapping', e);
                }
            }
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
            
            var row = '<tr data-id="' + item.id + '"' + (item.archived ? ' class="table-secondary"' : '') + '>' +
                '<td><span class="email-sms-name">' + item.name + '</span>' + archivedBadge + '</td>' +
                '<td>' + item.subaccountName + '</td>' +
                '<td>' + allowedSendersHtml + '</td>' +
                '<td>' + item.created + '</td>' +
                '<td>' + item.lastUpdated + '</td>' +
                '<td class="text-end">' +
                    '<div class="dropdown">' +
                        '<button class="action-menu-btn" type="button" data-bs-toggle="dropdown" onclick="event.stopPropagation();">' +
                            '<i class="fas fa-ellipsis-v"></i>' +
                        '</button>' +
                        '<ul class="dropdown-menu dropdown-menu-end">' +
                            '<li><a class="dropdown-item std-action-view" href="#" data-id="' + item.id + '"><i class="fas fa-eye me-2"></i> View</a></li>' +
                            '<li><a class="dropdown-item std-action-edit" href="#" data-id="' + item.id + '"><i class="fas fa-edit me-2"></i> Edit</a></li>' +
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
        
        $('.std-action-edit').off('click').on('click', function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            openStdEditModal(id);
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
                    loadStandardSmsTable();
                } else {
                    alert('Error: ' + (response.error || 'Failed to unarchive'));
                }
            }).catch(function(err) {
                console.error('Unarchive error:', err);
                alert('An error occurred. Please try again.');
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
        closeStdDrawer();
        openStdEditModal(id);
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
    
    $('#btnCreateStandardSms, #btnCreateStandardSmsEmpty').on('click', function() {
        openCreateStandardSmsModal();
    });
    
    // Initialize Standard Email-to-SMS table from service
    loadStandardSmsTable();
    
    // Mock account setting for dynamic SenderID
    var accountSettings = {
        dynamic_senderid_allowed: true
    };
    
    // Standard Email-to-SMS Create/Edit Modal Logic
    var stdAllowedEmails = [];
    
    function openCreateStandardSmsModal() {
        stdEditingId = null;
        
        // Update modal title
        $('#createStandardSmsModal .modal-title').html('<i class="fas fa-envelope me-2 text-primary"></i>Create Standard Email-to-SMS');
        $('#btnSaveStandardSms').html('<i class="fas fa-check me-1"></i> Save');
        
        // Reset form
        stdAllowedEmails = [];
        $('#stdCreateName').val('').removeClass('is-invalid');
        $('#stdCreateDescription').val('');
        $('#stdCreateSubaccount').val('').removeClass('is-invalid');
        $('#stdCreateEmailInput').val('').removeClass('is-invalid');
        $('#stdEmailError').hide();
        $('#stdEmailTagsContainer').empty();
        $('#stdEmailCount').text('0');
        $('#stdClearAllEmails').hide();
        $('#stdWildcardWarning').addClass('d-none');
        $('#stdCreateSenderId').val('').removeClass('is-invalid');
        $('#stdCreateSubjectAsSenderId').prop('checked', false);
        $('#stdCreateMultipleSms').prop('checked', false);
        $('#stdCreateDeliveryReports').prop('checked', false);
        $('#stdCreateDeliveryEmail').val('').removeClass('is-invalid');
        $('#stdDeliveryEmailGroup').hide();
        $('#stdCreateSignatureFilter').val('');
        
        // Show/hide Subject as SenderID based on account setting
        if (accountSettings.dynamic_senderid_allowed) {
            $('#stdSubjectAsSenderIdGroup').show();
        } else {
            $('#stdSubjectAsSenderIdGroup').hide();
        }
        
        // Open modal
        var modal = new bootstrap.Modal(document.getElementById('createStandardSmsModal'));
        modal.show();
    }
    
    function openStdEditModal(id) {
        var item = findStandardSmsById(id);
        if (!item) return;
        
        stdEditingId = id;
        
        // Update modal title
        $('#createStandardSmsModal .modal-title').html('<i class="fas fa-edit me-2 text-primary"></i>Edit Standard Email-to-SMS');
        $('#btnSaveStandardSms').html('<i class="fas fa-check me-1"></i> Update');
        
        // Populate form with existing data
        $('#stdCreateName').val(item.name).removeClass('is-invalid');
        $('#stdCreateDescription').val(item.description || '');
        $('#stdCreateSubaccount').val(item.subaccount).removeClass('is-invalid');
        
        // Populate allowed emails
        stdAllowedEmails = item.allowedSenders ? item.allowedSenders.slice() : [];
        renderEmailTags();
        updateWildcardWarning();
        
        $('#stdCreateSenderId').val(item.senderId).removeClass('is-invalid');
        $('#stdCreateSubjectAsSenderId').prop('checked', item.subjectAsSenderId);
        $('#stdCreateMultipleSms').prop('checked', item.multipleSms);
        $('#stdCreateDeliveryReports').prop('checked', item.deliveryReports);
        
        if (item.deliveryReports) {
            $('#stdDeliveryEmailGroup').show();
            $('#stdCreateDeliveryEmail').val(item.deliveryEmail || '').removeClass('is-invalid');
        } else {
            $('#stdDeliveryEmailGroup').hide();
            $('#stdCreateDeliveryEmail').val('').removeClass('is-invalid');
        }
        
        $('#stdCreateSignatureFilter').val(item.signatureFilter || '');
        
        // Show/hide Subject as SenderID based on account setting
        if (accountSettings.dynamic_senderid_allowed) {
            $('#stdSubjectAsSenderIdGroup').show();
        } else {
            $('#stdSubjectAsSenderIdGroup').hide();
        }
        
        // Open modal
        var modal = new bootstrap.Modal(document.getElementById('createStandardSmsModal'));
        modal.show();
    }
    
    function isValidEmail(email) {
        // Allow wildcard format *@domain.com
        if (/^\*@[a-zA-Z0-9]([a-zA-Z0-9-]*[a-zA-Z0-9])?(\.[a-zA-Z]{2,})+$/.test(email)) {
            return { valid: true, isWildcard: true };
        }
        // Standard email validation
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return { valid: emailRegex.test(email), isWildcard: false };
    }
    
    function addAllowedEmail() {
        var input = $('#stdCreateEmailInput');
        var email = input.val().trim().toLowerCase();
        var errorEl = $('#stdEmailError');
        
        if (!email) return;
        
        var validation = EmailToSmsService.validateEmail(email);
        if (!validation.valid) {
            errorEl.text('Invalid email format. Use email@domain.com or *@domain.com for wildcards.').show();
            input.addClass('is-invalid');
            return;
        }
        
        if (stdAllowedEmails.includes(email)) {
            errorEl.text('This email has already been added.').show();
            input.addClass('is-invalid');
            return;
        }
        
        errorEl.hide();
        input.removeClass('is-invalid');
        
        stdAllowedEmails.push(email);
        input.val('');
        renderEmailTags();
        
        // Show wildcard warning if any wildcard
        updateWildcardWarning();
    }
    
    function removeAllowedEmail(email) {
        var index = stdAllowedEmails.indexOf(email);
        if (index > -1) {
            stdAllowedEmails.splice(index, 1);
            renderEmailTags();
            updateWildcardWarning();
        }
    }
    
    function clearAllEmails() {
        stdAllowedEmails = [];
        renderEmailTags();
        updateWildcardWarning();
    }
    
    function renderEmailTags() {
        var container = $('#stdEmailTagsContainer');
        container.empty();
        
        stdAllowedEmails.forEach(function(email) {
            var isWildcard = email.startsWith('*@');
            var tag = $('<span class="email-tag' + (isWildcard ? ' wildcard-tag' : '') + '">' +
                '<i class="fas fa-' + (isWildcard ? 'asterisk' : 'envelope') + ' me-2 text-muted"></i>' +
                escapeHtml(email) +
                '<span class="remove-email"><i class="fas fa-times"></i></span>' +
                '</span>');
            tag.find('.remove-email').on('click', function() {
                removeAllowedEmail(email);
            });
            container.append(tag);
        });
        
        $('#stdEmailCount').text(stdAllowedEmails.length);
        $('#stdClearAllEmails').toggle(stdAllowedEmails.length > 0);
    }
    
    function updateWildcardWarning() {
        var hasWildcard = stdAllowedEmails.some(function(email) {
            return email.startsWith('*@');
        });
        $('#stdWildcardWarning').toggleClass('d-none', !hasWildcard);
    }
    
    function escapeHtml(text) {
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Event handlers for email chip input
    $('#stdAddEmailBtn').on('click', addAllowedEmail);
    
    $('#stdCreateEmailInput').on('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            addAllowedEmail();
        }
    });
    
    $('#stdClearAllEmails').on('click', clearAllEmails);
    
    // Toggle delivery reports email field
    $('#stdCreateDeliveryReports').on('change', function() {
        if ($(this).is(':checked')) {
            $('#stdDeliveryEmailGroup').slideDown(200);
        } else {
            $('#stdDeliveryEmailGroup').slideUp(200);
            $('#stdCreateDeliveryEmail').val('').removeClass('is-invalid');
        }
    });
    
    // Save Standard Email-to-SMS
    $('#btnSaveStandardSms').on('click', function() {
        var isValid = true;
        
        // Validate name
        var name = $('#stdCreateName').val().trim();
        if (!name) {
            $('#stdCreateName').addClass('is-invalid');
            isValid = false;
        } else {
            $('#stdCreateName').removeClass('is-invalid');
        }
        
        // Validate subaccount
        var subaccount = $('#stdCreateSubaccount').val();
        if (!subaccount) {
            $('#stdCreateSubaccount').addClass('is-invalid');
            isValid = false;
        } else {
            $('#stdCreateSubaccount').removeClass('is-invalid');
        }
        
        // Validate SenderID
        var senderId = $('#stdCreateSenderId').val();
        if (!senderId) {
            $('#stdCreateSenderId').addClass('is-invalid');
            isValid = false;
        } else {
            $('#stdCreateSenderId').removeClass('is-invalid');
        }
        
        // Validate delivery email if reports enabled
        if ($('#stdCreateDeliveryReports').is(':checked')) {
            var deliveryEmail = $('#stdCreateDeliveryEmail').val().trim();
            var emailValidation = EmailToSmsService.validateEmail(deliveryEmail);
            if (!deliveryEmail || !emailValidation.valid || emailValidation.isWildcard) {
                $('#stdCreateDeliveryEmail').addClass('is-invalid');
                isValid = false;
            } else {
                $('#stdCreateDeliveryEmail').removeClass('is-invalid');
            }
        }
        
        // Validate content filter regex
        var contentFilter = $('#stdCreateSignatureFilter').val().trim();
        var regexValidation = EmailToSmsService.validateContentFilterRegex(contentFilter);
        if (!regexValidation.valid) {
            $('#stdCreateSignatureFilter').addClass('is-invalid');
            $('#stdSignatureFilterError').text(regexValidation.error).show();
            isValid = false;
        } else {
            $('#stdCreateSignatureFilter').removeClass('is-invalid');
            $('#stdSignatureFilterError').hide();
        }
        
        if (!isValid) {
            return;
        }
        
        // Build payload for service layer
        var payload = {
            name: name,
            description: $('#stdCreateDescription').val().trim(),
            subaccountId: subaccount,
            allowedEmails: stdAllowedEmails.slice(),
            senderIdTemplateId: senderId,
            subjectOverridesSenderId: $('#stdCreateSubjectAsSenderId').is(':checked'),
            multipleSmsEnabled: $('#stdCreateMultipleSms').is(':checked'),
            deliveryReportsEnabled: $('#stdCreateDeliveryReports').is(':checked'),
            deliveryReportsEmail: $('#stdCreateDeliveryEmail').val().trim(),
            contentFilterRegex: contentFilter
        };
        
        // Disable save button during request
        var $saveBtn = $('#btnSaveStandardSms');
        $saveBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Saving...');
        
        var savePromise;
        if (stdEditingId) {
            savePromise = EmailToSmsService.updateEmailToSmsSetup(stdEditingId, payload);
        } else {
            savePromise = EmailToSmsService.createEmailToSmsSetup(payload);
        }
        
        savePromise.then(function(response) {
            if (response.success) {
                // Close modal
                bootstrap.Modal.getInstance(document.getElementById('createStandardSmsModal')).hide();
                
                // Reload table from service
                loadStandardSmsTable();
            } else {
                alert('Error: ' + (response.error || 'Failed to save setup'));
            }
        }).catch(function(err) {
            console.error('Save error:', err);
            alert('An error occurred while saving. Please try again.');
        }).finally(function() {
            $saveBtn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save');
        });
    });
    
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
