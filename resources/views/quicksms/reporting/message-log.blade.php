@extends('layouts.quicksms')

@section('title', 'Message Log')

@push('styles')
<style>
.message-log-container {
    height: calc(100vh - 120px);
    display: flex;
    flex-direction: column;
    overflow: hidden;
}
.message-log-container .card {
    flex: 1;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    margin-bottom: 0 !important;
}
.message-log-container .card-body {
    flex: 1;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    padding-bottom: 0;
}
.message-log-fixed-header {
    flex-shrink: 0;
}
.message-log-table-wrapper {
    flex: 1;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    min-height: 0;
}
#tableContainer {
    flex: 1;
    overflow-y: auto;
    overflow-x: auto;
    min-height: 0;
}
.message-log-footer {
    flex-shrink: 0;
    margin-top: auto;
}
#messageLogTable tbody tr {
    cursor: pointer;
}
#messageLogTable tbody tr:hover {
    background-color: rgba(111, 66, 193, 0.05);
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
.bg-primary-light {
    background-color: rgba(111, 66, 193, 0.1);
}
.bg-info-light {
    background-color: rgba(23, 162, 184, 0.1);
}
.drag-handle {
    cursor: grab;
    opacity: 0.5;
}
.drag-handle:hover {
    opacity: 1;
}
#columnConfigMenu .form-check {
    padding-left: 1.5em;
}
#columnConfigMenu .form-check-label {
    display: flex;
    align-items: center;
}
#tableContainer thead th {
    border-bottom: 2px solid #dee2e6;
    white-space: nowrap;
}
#messageLogTable tbody tr:hover {
    background-color: rgba(111, 66, 193, 0.05);
}
.content-masked {
    letter-spacing: 2px;
    font-family: monospace;
}
.action-dots {
    color: inherit;
    opacity: 0.7;
}
.action-dots:hover {
    opacity: 1;
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
    border-color: #6f42c1;
}
.date-preset-btn.active {
    background: #6f42c1;
    color: #fff;
    border-color: #6f42c1;
}
.btn-xs {
    padding: 0.2rem 0.5rem;
    font-size: 0.7rem;
    line-height: 1.4;
}
.multi-value-input {
    display: flex;
    flex-wrap: wrap;
    gap: 0.25rem;
    padding: 0.25rem;
    border: 1px solid #ced4da;
    border-radius: 0.375rem;
    min-height: 38px;
    background: #fff;
}
.multi-value-input:focus-within {
    border-color: #6f42c1;
    box-shadow: 0 0 0 0.2rem rgba(111, 66, 193, 0.25);
}
.multi-value-input input {
    border: none;
    outline: none;
    flex: 1;
    min-width: 100px;
    font-size: 0.875rem;
    padding: 0.25rem;
}
.multi-value-tag {
    display: inline-flex;
    align-items: center;
    padding: 0.125rem 0.5rem;
    background: #e9ecef;
    border-radius: 0.25rem;
    font-size: 0.75rem;
}
.multi-value-tag .remove-tag {
    margin-left: 0.25rem;
    cursor: pointer;
    opacity: 0.7;
}
.multi-value-tag .remove-tag:hover {
    opacity: 1;
}
.multiselect-dropdown {
    position: relative;
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
.multiselect-toggle {
    display: flex;
    justify-content: space-between;
    align-items: center;
    text-align: left;
    background: #fff;
}
.multiselect-toggle .selected-count {
    background: #6f42c1;
    color: #fff;
    font-size: 0.65rem;
    padding: 0.125rem 0.375rem;
    border-radius: 0.75rem;
    margin-left: 0.5rem;
}
.predictive-input-wrapper {
    position: relative;
}
.predictive-suggestions {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: #fff;
    border: 1px solid #ced4da;
    border-top: none;
    border-radius: 0 0 0.375rem 0.375rem;
    max-height: 150px;
    overflow-y: auto;
    z-index: 1000;
    display: none;
}
.predictive-suggestions.show {
    display: block;
}
.predictive-suggestion {
    padding: 0.5rem 0.75rem;
    cursor: pointer;
    font-size: 0.875rem;
}
.predictive-suggestion:hover {
    background: #f8f9fa;
}
</style>
@endpush

@section('content')
<div class="container-fluid message-log-container">
    <div class="row page-titles mb-2" style="flex-shrink: 0;">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('reporting') }}">Reporting</a></li>
            <li class="breadcrumb-item active">Message Log</li>
        </ol>
    </div>
    
    <div class="row flex-grow-1" style="min-height: 0;">
        <div class="col-12 d-flex flex-column" style="min-height: 0;">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap message-log-fixed-header">
                    <h5 class="card-title mb-2 mb-md-0">Message Log</h5>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#filtersPanel">
                            <i class="fas fa-filter me-1"></i> Filters
                        </button>
                        <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#exportModal">
                            <i class="fas fa-download me-1"></i> Export
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="message-log-fixed-header">
                        <div class="mb-3">
                            <div class="input-group">
                                <span class="input-group-text bg-transparent"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control" id="messageSearch" placeholder="Search by recipient, sender, message content...">
                            </div>
                        </div>

                        <div class="collapse mb-3" id="filtersPanel">
                        <div class="card card-body border-0 rounded-3" style="background-color: #f0ebf8;">
                            <!-- Row 1: Date Range with datetime -->
                            <div class="row g-3 align-items-end">
                                <div class="col-12 col-lg-6">
                                    <label class="form-label small fw-bold">Date Range</label>
                                    <div class="d-flex gap-2 align-items-center">
                                        <input type="datetime-local" class="form-control form-control-sm" id="filterDateFrom" placeholder="dd/mm/yyyy hh:mm:ss">
                                        <span class="text-muted small">to</span>
                                        <input type="datetime-local" class="form-control form-control-sm" id="filterDateTo" placeholder="dd/mm/yyyy hh:mm:ss">
                                    </div>
                                    <div class="d-flex flex-wrap gap-1 mt-2">
                                        <button type="button" class="btn btn-outline-primary btn-xs" data-preset="today">Today</button>
                                        <button type="button" class="btn btn-outline-primary btn-xs" data-preset="yesterday">Yesterday</button>
                                        <button type="button" class="btn btn-outline-primary btn-xs" data-preset="7days">Last 7 Days</button>
                                        <button type="button" class="btn btn-outline-primary btn-xs" data-preset="30days">Last 30 Days</button>
                                        <button type="button" class="btn btn-outline-primary btn-xs" data-preset="thismonth">This Month</button>
                                        <button type="button" class="btn btn-outline-primary btn-xs" data-preset="lastmonth">Last Month</button>
                                    </div>
                                </div>
                                <div class="col-6 col-md-4 col-lg-2">
                                    <label class="form-label small fw-bold">Sub Account</label>
                                    <select class="form-select form-select-sm" id="filterSubAccountToggle">
                                        <option value="">All Sub Accounts</option>
                                        <option value="main">Main Account</option>
                                        <option value="marketing">Marketing Team</option>
                                        <option value="support">Support Team</option>
                                        <option value="sales">Sales Team</option>
                                    </select>
                                </div>
                                <div class="col-6 col-md-4 col-lg-2">
                                    <label class="form-label small fw-bold">User</label>
                                    <select class="form-select form-select-sm" id="filterUserToggle">
                                        <option value="">All Users</option>
                                        <option value="john">John Smith</option>
                                        <option value="sarah">Sarah Johnson</option>
                                        <option value="mike">Mike Williams</option>
                                        <option value="emma">Emma Davis</option>
                                    </select>
                                </div>
                                <div class="col-6 col-md-4 col-lg-2">
                                    <label class="form-label small fw-bold">Origin</label>
                                    <select class="form-select form-select-sm" id="filterOriginToggle">
                                        <option value="">All Origins</option>
                                        <option value="portal">Portal</option>
                                        <option value="api">API</option>
                                        <option value="email-to-sms">Email-to-SMS</option>
                                        <option value="integration">Integration</option>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Row 2: Mobile Number + SenderID + Status + Country + Type (all same width) -->
                            <div class="row g-3 align-items-end mt-2">
                                <div class="col-6 col-md-4 col-lg-2">
                                    <label class="form-label small fw-bold">Mobile Number</label>
                                    <input type="text" class="form-control form-control-sm" id="filterMobileInput" placeholder="Enter number...">
                                </div>
                                <div class="col-6 col-md-4 col-lg-2">
                                    <label class="form-label small fw-bold">SenderID</label>
                                    <input type="text" class="form-control form-control-sm" id="filterSenderId" placeholder="Type to search...">
                                </div>
                                <div class="col-6 col-md-4 col-lg-2">
                                    <label class="form-label small fw-bold">Message Status</label>
                                    <select class="form-select form-select-sm" id="filterStatusToggle">
                                        <option value="">All Statuses</option>
                                        <option value="delivered">Delivered</option>
                                        <option value="pending">Pending</option>
                                        <option value="undeliverable">Undeliverable</option>
                                        <option value="rejected">Rejected</option>
                                    </select>
                                </div>
                                <div class="col-6 col-md-4 col-lg-2">
                                    <label class="form-label small fw-bold">Country</label>
                                    <select class="form-select form-select-sm" id="filterCountryToggle">
                                        <option value="">All Countries</option>
                                        <option value="uk">United Kingdom</option>
                                        <option value="us">United States</option>
                                        <option value="de">Germany</option>
                                        <option value="fr">France</option>
                                        <option value="es">Spain</option>
                                        <option value="ie">Ireland</option>
                                    </select>
                                </div>
                                <div class="col-6 col-md-4 col-lg-2">
                                    <label class="form-label small fw-bold">Message Type</label>
                                    <select class="form-select form-select-sm" id="filterTypeToggle">
                                        <option value="">All Types</option>
                                        <option value="sms">SMS</option>
                                        <option value="rcs-basic">RCS Basic</option>
                                        <option value="rcs-rich">RCS Rich</option>
                                    </select>
                                </div>
                                <div class="col-6 col-md-4 col-lg-2">
                                    <label class="form-label small fw-bold">Message ID</label>
                                    <input type="text" class="form-control form-control-sm" id="filterMessageIdInput" placeholder="Enter ID...">
                                </div>
                            </div>
                            
                            <!-- Row 3: Action buttons aligned right -->
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

                        <div class="mb-3" id="summaryBar" style="display: none;">
                        <div class="row g-3">
                            <div class="col-6 col-md-3">
                                <div class="card">
                                    <div class="card-body py-3 px-4">
                                        <div class="d-flex align-items-center">
                                            <div class="me-3">
                                                <span class="bg-primary-light rounded-circle d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                                                    <i class="fas fa-envelope text-primary"></i>
                                                </span>
                                            </div>
                                            <div>
                                                <p class="mb-0 text-muted small">Total Messages</p>
                                                <h4 class="mb-0 fw-bold" id="summaryTotal">0</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="card">
                                    <div class="card-body py-3 px-4">
                                        <div class="d-flex align-items-center">
                                            <div class="me-3">
                                                <span class="bg-info-light rounded-circle d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                                                    <i class="fas fa-puzzle-piece text-info"></i>
                                                </span>
                                            </div>
                                            <div>
                                                <p class="mb-0 text-muted small">Total Parts/Fragments</p>
                                                <h4 class="mb-0 fw-bold" id="summaryParts">0</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="text-muted small" id="rowCountInfo">
                                <span id="renderedCount">0</span> rows loaded (max 10,000)
                            </div>
                            <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#columnSettingsModal">
                                <i class="fas fa-cog me-1"></i> Column Settings
                            </button>
                        </div>
                    </div>
                    
                    <div class="message-log-table-wrapper">
                        <div class="table-responsive" id="tableContainer">
                        <table class="table table-hover mb-0" id="messageLogTable">
                            <thead class="sticky-top bg-white" style="z-index: 10;">
                                <tr id="tableHeaderRow">
                                    <th data-column="mobileNumber">Mobile Number</th>
                                    <th data-column="senderId">SenderID</th>
                                    <th data-column="status">
                                        <div class="dropdown d-inline-block">
                                            <span class="dropdown-toggle" style="cursor: pointer;" data-bs-toggle="dropdown">
                                                Status <i class="fas fa-sort ms-1 text-muted"></i>
                                            </span>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="#!" onclick="sortTable('status', 'asc'); return false;"><i class="fas fa-check-circle me-2 text-success"></i> Delivered First</a></li>
                                                <li><a class="dropdown-item" href="#!" onclick="sortTable('status', 'desc'); return false;"><i class="fas fa-times-circle me-2 text-danger"></i> Failed First</a></li>
                                            </ul>
                                        </div>
                                    </th>
                                    <th data-column="sentTime">
                                        <div class="dropdown d-inline-block">
                                            <span class="dropdown-toggle" style="cursor: pointer;" data-bs-toggle="dropdown">
                                                Sent Time <i class="fas fa-sort ms-1 text-muted"></i>
                                            </span>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="#!" onclick="sortTable('sentTime', 'desc'); return false;"><i class="fas fa-calendar-alt me-2"></i> Most Recent</a></li>
                                                <li><a class="dropdown-item" href="#!" onclick="sortTable('sentTime', 'asc'); return false;"><i class="fas fa-calendar me-2"></i> Oldest First</a></li>
                                            </ul>
                                        </div>
                                    </th>
                                    <th data-column="deliveryTime">Delivery Time</th>
                                    <th data-column="completedTime">Completed Time</th>
                                    <th data-column="cost">Cost</th>
                                    <th data-column="messageType" class="d-none">Message Type</th>
                                    <th data-column="subAccount" class="d-none">Sub Account</th>
                                    <th data-column="user" class="d-none">User</th>
                                    <th data-column="origin" class="d-none">Origin</th>
                                    <th data-column="country" class="d-none">Country</th>
                                    <th data-column="parts" class="d-none">Fragments/Parts</th>
                                    <th data-column="encoding" class="d-none">Encoding</th>
                                    <th data-column="messageId" class="d-none">Message ID</th>
                                    <th data-column="content" class="d-none">Content</th>
                                    <th data-column="actions" style="width: 50px;"></th>
                                </tr>
                            </thead>
                            <tbody id="messageLogTableBody">
                                <tr id="loadingInitialRow">
                                    <td colspan="17" class="text-center py-5">
                                        <div class="spinner-border spinner-border-sm text-primary me-2" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <span class="text-muted">Loading messages...</span>
                                    </td>
                                </tr>
                                <!-- Rows populated by mock API -->
                            </tbody>
                        </table>
                            <div id="noResultsState" class="text-center py-5 text-muted d-none">
                                <i class="fas fa-search fa-3x mb-3 d-block opacity-25"></i>
                                <p class="mb-2">No messages match your filters.</p>
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="btnClearFiltersEmpty">
                                    <i class="fas fa-times me-1"></i> Clear filters
                                </button>
                            </div>
                            <div id="loadingMore" class="text-center py-3 d-none">
                                <div class="spinner-border spinner-border-sm text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <span class="ms-2 text-muted small">Loading more messages...</span>
                            </div>
                        </div>
                    </div>

                    <div class="card card-body bg-light border mt-2 message-log-footer" id="exportBar">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                            <div class="text-muted small">
                                <i class="fas fa-info-circle me-1"></i>
                                Showing <span id="displayedCount">0</span> of <span id="totalCount">0</span> messages
                            </div>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="text-muted small me-2">Export:</span>
                                <button type="button" class="btn btn-outline-primary btn-sm" id="btnExportCsv" onclick="exportMessages('csv')">
                                    <i class="fas fa-file-csv me-1"></i> CSV
                                </button>
                                <button type="button" class="btn btn-outline-success btn-sm" id="btnExportExcel" onclick="exportMessages('excel')">
                                    <i class="fas fa-file-excel me-1"></i> Excel
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="btnExportTxt" onclick="exportMessages('txt')">
                                    <i class="fas fa-file-alt me-1"></i> TXT
                                </button>
                            </div>
                        </div>
                        <div class="mt-2 pt-2 border-top d-none" id="exportProgressBar">
                            <div class="d-flex align-items-center">
                                <div class="spinner-border spinner-border-sm text-primary me-2" role="status">
                                    <span class="visually-hidden">Exporting...</span>
                                </div>
                                <span class="text-muted small" id="exportProgressText">Preparing export...</span>
                            </div>
                        </div>
                        <p class="text-muted small mb-0 mt-2">
                            <i class="fas fa-lightbulb me-1 text-warning"></i>
                            Exports respect applied filters and selected columns. Large exports are processed in the background and available in the <a href="/reporting/download-area" class="text-primary">Download Area</a>.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="columnSettingsModal" tabindex="-1" aria-labelledby="columnSettingsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="columnSettingsModalLabel">
                    <i class="fas fa-columns me-2 text-primary"></i>Column Settings
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3">Select which columns to display in the results table. Changes are saved automatically.</p>
                
                <h6 class="text-muted small fw-bold text-uppercase mb-2">Default Columns</h6>
                <div class="list-group list-group-flush mb-3" id="defaultColumnsList">
                    <label class="list-group-item d-flex align-items-center">
                        <input class="form-check-input column-toggle me-3" type="checkbox" id="col-mobileNumber" data-column="mobileNumber" checked>
                        <span>Mobile Number</span>
                    </label>
                    <label class="list-group-item d-flex align-items-center">
                        <input class="form-check-input column-toggle me-3" type="checkbox" id="col-senderId" data-column="senderId" checked>
                        <span>SenderID</span>
                    </label>
                    <label class="list-group-item d-flex align-items-center">
                        <input class="form-check-input column-toggle me-3" type="checkbox" id="col-status" data-column="status" checked>
                        <span>Message Status</span>
                    </label>
                    <label class="list-group-item d-flex align-items-center">
                        <input class="form-check-input column-toggle me-3" type="checkbox" id="col-sentTime" data-column="sentTime" checked>
                        <span>Sent Time</span>
                    </label>
                    <label class="list-group-item d-flex align-items-center">
                        <input class="form-check-input column-toggle me-3" type="checkbox" id="col-deliveryTime" data-column="deliveryTime" checked>
                        <span>Delivery Time</span>
                    </label>
                    <label class="list-group-item d-flex align-items-center">
                        <input class="form-check-input column-toggle me-3" type="checkbox" id="col-completedTime" data-column="completedTime" checked>
                        <span>Completed Time</span>
                    </label>
                    <label class="list-group-item d-flex align-items-center">
                        <input class="form-check-input column-toggle me-3" type="checkbox" id="col-cost" data-column="cost" checked>
                        <span>Cost</span>
                    </label>
                </div>
                
                <h6 class="text-muted small fw-bold text-uppercase mb-2">Optional Columns</h6>
                <div class="list-group list-group-flush" id="optionalColumnsList">
                    <label class="list-group-item d-flex align-items-center">
                        <input class="form-check-input column-toggle me-3" type="checkbox" id="col-messageType" data-column="messageType">
                        <span>Message Type</span>
                        <span class="badge bg-light text-muted ms-auto small">SMS / RCS</span>
                    </label>
                    <label class="list-group-item d-flex align-items-center">
                        <input class="form-check-input column-toggle me-3" type="checkbox" id="col-subAccount" data-column="subAccount">
                        <span>Sub Account</span>
                    </label>
                    <label class="list-group-item d-flex align-items-center">
                        <input class="form-check-input column-toggle me-3" type="checkbox" id="col-user" data-column="user">
                        <span>User</span>
                    </label>
                    <label class="list-group-item d-flex align-items-center">
                        <input class="form-check-input column-toggle me-3" type="checkbox" id="col-origin" data-column="origin">
                        <span>Origin</span>
                        <span class="badge bg-light text-muted ms-auto small">Portal / API / etc.</span>
                    </label>
                    <label class="list-group-item d-flex align-items-center">
                        <input class="form-check-input column-toggle me-3" type="checkbox" id="col-country" data-column="country">
                        <span>Country</span>
                    </label>
                    <label class="list-group-item d-flex align-items-center">
                        <input class="form-check-input column-toggle me-3" type="checkbox" id="col-parts" data-column="parts">
                        <span>Fragments / Parts</span>
                    </label>
                    <label class="list-group-item d-flex align-items-center">
                        <input class="form-check-input column-toggle me-3" type="checkbox" id="col-encoding" data-column="encoding">
                        <span>Encoding</span>
                        <span class="badge bg-light text-muted ms-auto small">GSM-7 / Unicode</span>
                    </label>
                    <label class="list-group-item d-flex align-items-center">
                        <input class="form-check-input column-toggle me-3" type="checkbox" id="col-messageId" data-column="messageId">
                        <span>Message ID</span>
                    </label>
                    <label class="list-group-item d-flex align-items-center">
                        <input class="form-check-input column-toggle me-3" type="checkbox" id="col-content" data-column="content">
                        <span>Content</span>
                        <span class="badge bg-warning text-dark ms-auto small"><i class="fas fa-lock me-1"></i>Security Controlled</span>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" id="btnResetColumns">
                    <i class="fas fa-undo me-1"></i> Reset to Default
                </button>
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Done</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="messageDetailsModal" tabindex="-1" aria-labelledby="messageDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="messageDetailsModalLabel">
                    <i class="fas fa-envelope me-2 text-primary"></i>Message Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted small mb-1">Message ID</label>
                        <p class="mb-0 fw-medium" id="detailMessageId">-</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small mb-1">Status</label>
                        <p class="mb-0" id="detailStatus">-</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small mb-1">Mobile Number</label>
                        <p class="mb-0" id="detailMobile">-</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small mb-1">SenderID</label>
                        <p class="mb-0" id="detailSenderId">-</p>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small mb-1">Sent Time</label>
                        <p class="mb-0" id="detailSentTime">-</p>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small mb-1">Delivery Time</label>
                        <p class="mb-0" id="detailDeliveryTime">-</p>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small mb-1">Completed Time</label>
                        <p class="mb-0" id="detailCompletedTime">-</p>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small mb-1">Message Type</label>
                        <p class="mb-0" id="detailType">-</p>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small mb-1">Encoding</label>
                        <p class="mb-0" id="detailEncoding">-</p>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small mb-1">Parts / Cost</label>
                        <p class="mb-0" id="detailCost">-</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small mb-1">Sub Account</label>
                        <p class="mb-0" id="detailSubAccount">-</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small mb-1">User</label>
                        <p class="mb-0" id="detailUser">-</p>
                    </div>
                    <div class="col-12">
                        <label class="form-label text-muted small mb-1">Origin</label>
                        <p class="mb-0" id="detailOrigin">-</p>
                    </div>
                    <div class="col-12">
                        <label class="form-label text-muted small mb-1">Content</label>
                        <div class="bg-light rounded p-3" id="detailContent">
                            <span class="text-muted fst-italic">Content not available</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-primary" onclick="copyToClipboard(document.getElementById('detailMessageId').textContent, 'Message ID')">
                    <i class="fas fa-copy me-1"></i> Copy Message ID
                </button>
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Export Modal -->
<div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exportModalLabel"><i class="fas fa-download me-2 text-primary"></i>Export Message Log</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-3">Choose your preferred export format:</p>
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-outline-primary text-start" onclick="exportData('csv')">
                        <i class="fas fa-file-csv me-2"></i> Export as CSV
                        <small class="text-muted d-block ms-4">Comma-separated values, compatible with Excel</small>
                    </button>
                    <button type="button" class="btn btn-outline-primary text-start" onclick="exportData('xlsx')">
                        <i class="fas fa-file-excel me-2"></i> Export as XLSX
                        <small class="text-muted d-block ms-4">Microsoft Excel format</small>
                    </button>
                    <button type="button" class="btn btn-outline-primary text-start" onclick="exportData('txt')">
                        <i class="fas fa-file-alt me-2"></i> Export as TXT
                        <small class="text-muted d-block ms-4">Plain text file, tab-separated</small>
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// ========================================
// Row Action Functions (Global scope for onclick handlers)
// ========================================

function copyToClipboard(text, label) {
    navigator.clipboard.writeText(text).then(() => {
        showToast(`${label} copied to clipboard`, 'success');
    }).catch(err => {
        console.error('Failed to copy:', err);
        showToast('Failed to copy to clipboard', 'error');
    });
}

function showToast(message, type) {
    const toastContainer = document.getElementById('toastContainer') || createToastContainer();
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white border-0 ${type === 'success' ? 'bg-success' : 'bg-danger'}`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} me-2"></i>${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    toastContainer.appendChild(toast);
    const bsToast = new bootstrap.Toast(toast, { delay: 3000 });
    bsToast.show();
    toast.addEventListener('hidden.bs.toast', () => toast.remove());
}

function createToastContainer() {
    const container = document.createElement('div');
    container.id = 'toastContainer';
    container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
    container.style.zIndex = '1100';
    document.body.appendChild(container);
    return container;
}

function exportData(format) {
    // Close the modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('exportModal'));
    if (modal) modal.hide();
    
    // Show processing toast
    showToast(`Preparing ${format.toUpperCase()} export...`, 'success');
    
    // TODO: Backend Integration - Implement actual export
    // For now, simulate export with a delay
    setTimeout(() => {
        showToast(`${format.toUpperCase()} export ready for download`, 'success');
        // TODO: Trigger actual file download
    }, 1500);
}

function exportMessages(format) {
    // TODO: Backend Integration - Async export with Download Centre handoff
    // POST /api/messages/export
    // Request body: { format: 'csv'|'excel'|'txt', filters: {...}, columns: [...] }
    // Response: { exportId: 'xxx', status: 'queued', estimatedTime: 30 }
    // For large exports (>1000 rows), queue job and redirect to Download Area
    // For small exports (<1000 rows), return file directly
    
    // Get current applied filters
    const appliedFilters = typeof filterState !== 'undefined' ? filterState : {};
    
    // Get currently visible columns
    const visibleColumns = typeof columnConfig !== 'undefined' ? columnConfig.visible : [];
    
    // Log export request details
    console.log('=== Export Request ===');
    console.log('Format:', format.toUpperCase());
    console.log('Applied Filters:', JSON.stringify(appliedFilters, null, 2));
    console.log('Selected Columns:', visibleColumns);
    console.log('Total Records:', document.getElementById('totalCount')?.textContent || 'Unknown');
    console.log('======================');
    
    // Show progress indicator
    const progressBar = document.getElementById('exportProgressBar');
    const progressText = document.getElementById('exportProgressText');
    
    if (progressBar && progressText) {
        progressBar.classList.remove('d-none');
        progressText.textContent = `Preparing ${format.toUpperCase()} export...`;
        
        // Simulate async export process
        setTimeout(() => {
            progressText.textContent = `Export queued. Check Download Area for your file.`;
            
            setTimeout(() => {
                progressBar.classList.add('d-none');
                showToast(`${format.toUpperCase()} export queued. Visit Download Area when ready.`, 'success');
            }, 2000);
        }, 1500);
    }
}

function viewMessageDetails(messageId) {
    // TODO: Backend Integration - Fetch message details from API
    // GET /api/messages/{messageId}
    
    // Mock data for placeholder modal
    const mockMessage = {
        messageId: messageId,
        status: 'Delivered',
        mobile: '+44 77** ***456',
        senderId: 'QuickSMS',
        sentTime: '30/12/2024 14:23',
        deliveryTime: '30/12/2024 14:23',
        completedTime: '30/12/2024 14:23',
        type: 'SMS',
        encoding: 'GSM-7',
        parts: 1,
        cost: '£0.035',
        subAccount: 'Main Account',
        user: 'John Smith',
        origin: 'Portal',
        content: 'Content not available - requires Super Admin permission'
    };
    
    document.getElementById('detailMessageId').textContent = mockMessage.messageId;
    document.getElementById('detailStatus').innerHTML = `<span class="badge bg-success">Delivered</span>`;
    document.getElementById('detailMobile').textContent = mockMessage.mobile;
    document.getElementById('detailSenderId').textContent = mockMessage.senderId;
    document.getElementById('detailSentTime').textContent = mockMessage.sentTime;
    document.getElementById('detailDeliveryTime').textContent = mockMessage.deliveryTime;
    document.getElementById('detailCompletedTime').textContent = mockMessage.completedTime;
    document.getElementById('detailType').innerHTML = `<span class="badge bg-secondary">SMS</span>`;
    document.getElementById('detailEncoding').innerHTML = `<span class="badge bg-light text-dark border">GSM-7</span>`;
    document.getElementById('detailCost').textContent = `${mockMessage.parts} part(s) / ${mockMessage.cost}`;
    document.getElementById('detailSubAccount').textContent = mockMessage.subAccount;
    document.getElementById('detailUser').textContent = mockMessage.user;
    document.getElementById('detailOrigin').textContent = mockMessage.origin;
    document.getElementById('detailContent').innerHTML = `<span class="text-muted fst-italic">${mockMessage.content}</span>`;
    
    const modal = new bootstrap.Modal(document.getElementById('messageDetailsModal'));
    modal.show();
}

// ========================================
// Mock API Layer
// ========================================
const MockAPI = {
    // Mock data constants
    statuses: [
        { class: 'bg-success', text: 'Delivered', weight: 70 },
        { class: 'bg-warning text-dark', text: 'Pending', weight: 10 },
        { class: 'bg-danger', text: 'Undeliverable', weight: 15 },
        { class: 'bg-secondary', text: 'Rejected', weight: 5 }
    ],
    senders: ['QuickSMS', 'ALERTS', 'PROMO', 'QuickSMS Brand', 'INFO', 'NOTIFY'],
    origins: ['Portal', 'API', 'Email-to-SMS', 'Integration'],
    messageTypes: [
        { class: 'bg-secondary', text: 'SMS', weight: 60 },
        { class: 'bg-info', text: 'RCS Basic', weight: 25 },
        { class: 'bg-info', text: 'RCS Rich', weight: 15 }
    ],
    subAccounts: ['Main Account', 'Marketing Team', 'Support Team', 'Sales Team'],
    users: ['John Smith', 'Sarah Johnson', 'Mike Williams', 'Emma Davis', 'James Wilson'],
    encodings: [
        { class: 'bg-light text-dark border', text: 'GSM-7', weight: 80 },
        { class: 'bg-primary text-white', text: 'Unicode', weight: 20 }
    ],
    countries: ['UK', 'US', 'DE', 'FR', 'ES', 'IE'],
    messages: [
        'Your order has been dispatched and will arrive tomorrow.',
        'Reminder: Your appointment is scheduled for tomorrow at 2pm.',
        'Your verification code is 123456. Valid for 5 minutes.',
        'Thank you for your purchase! Your receipt is attached.',
        'Flash sale! 50% off all items this weekend only.',
        'Your account balance is low. Please top up soon.',
        'Delivery update: Your package is out for delivery.',
        'Welcome to QuickSMS! Your account is now active.'
    ],
    
    // Weighted random selection
    weightedRandom(items) {
        const totalWeight = items.reduce((sum, item) => sum + (item.weight || 1), 0);
        let random = Math.random() * totalWeight;
        for (const item of items) {
            random -= (item.weight || 1);
            if (random <= 0) return item;
        }
        return items[0];
    },
    
    // Generate a single mock message
    generateMessage(index, baseTime) {
        const status = this.weightedRandom(this.statuses);
        const messageType = this.weightedRandom(this.messageTypes);
        const encoding = this.weightedRandom(this.encodings);
        const parts = Math.random() < 0.2 ? Math.floor(Math.random() * 3) + 2 : 1;
        
        const sentTime = new Date(baseTime);
        sentTime.setMinutes(sentTime.getMinutes() - index * 2);
        
        const deliveryTime = status.text === 'Delivered' ? new Date(sentTime.getTime() + Math.random() * 30000) : null;
        const completedTime = status.text !== 'Pending' ? new Date(sentTime.getTime() + Math.random() * 60000) : null;
        
        const phoneDigits = String(Math.floor(Math.random() * 10000000000)).padStart(10, '0');
        const phone = `+44 7${phoneDigits.substring(0, 1)}** ***${phoneDigits.substring(7)}`;
        const phoneRaw = `+447${phoneDigits}`;
        
        return {
            id: `MSG-${String(index + 1).padStart(9, '0')}`,
            mobileNumber: phone,
            mobileNumberRaw: phoneRaw,
            senderId: this.senders[Math.floor(Math.random() * this.senders.length)],
            status: status,
            sentTime: sentTime,
            deliveryTime: deliveryTime,
            completedTime: completedTime,
            cost: (parts * 0.035).toFixed(3),
            messageType: messageType,
            subAccount: this.subAccounts[Math.floor(Math.random() * this.subAccounts.length)],
            user: this.users[Math.floor(Math.random() * this.users.length)],
            origin: this.origins[Math.floor(Math.random() * this.origins.length)],
            country: this.countries[Math.floor(Math.random() * this.countries.length)],
            parts: parts,
            encoding: encoding,
            content: this.messages[Math.floor(Math.random() * this.messages.length)]
        };
    },
    
    // Fetch messages (simulates API call)
    async fetchMessages(filters, page = 1, limit = 50) {
        // Simulate network delay (200-500ms)
        await new Promise(resolve => setTimeout(resolve, 200 + Math.random() * 300));
        
        const totalAvailable = 1247; // Mock total count
        const baseTime = new Date();
        const startIndex = (page - 1) * limit;
        
        // Generate mock data
        const messages = [];
        const count = Math.min(limit, totalAvailable - startIndex);
        
        for (let i = 0; i < count; i++) {
            messages.push(this.generateMessage(startIndex + i, baseTime));
        }
        
        console.log(`[Mock API] Fetched page ${page}, ${messages.length} rows (filters:`, filters, ')');
        
        return {
            data: messages,
            meta: {
                currentPage: page,
                perPage: limit,
                total: totalAvailable,
                totalPages: Math.ceil(totalAvailable / limit),
                hasMore: startIndex + count < totalAvailable
            }
        };
    }
};

// ========================================
// Main Application Logic
// ========================================
document.addEventListener('DOMContentLoaded', function() {
    // State management
    let filterState = {
        dateFrom: '',
        dateTo: '',
        subAccounts: [],
        users: [],
        origins: [],
        mobileNumbers: [],
        senderId: '',
        statuses: [],
        countries: [],
        messageTypes: [],
        messageIds: []
    };
    
    let pendingFilters = { ...filterState };
    let currentPage = 1;
    let isLoading = false;
    let hasMore = true;
    let totalMessages = 0;
    const PAGE_SIZE = 50;
    const MAX_ROWS = 10000;

    // DOM elements
    const tableBody = document.getElementById('messageLogTableBody');
    const tableContainer = document.getElementById('tableContainer');
    const loadingMore = document.getElementById('loadingMore');
    const noResultsState = document.getElementById('noResultsState');
    const displayedCount = document.getElementById('displayedCount');
    const totalCount = document.getElementById('totalCount');
    const summaryBar = document.getElementById('summaryBar');
    
    // Column configuration
    const STORAGE_KEY = 'messageLogColumnConfig';
    const currentUserRole = 'user'; // TODO: Replace with Auth::user()->role
    
    function canViewMessageContent() {
        return currentUserRole === 'super_admin';
    }
    
    function renderMessageContent(plaintext) {
        if (canViewMessageContent()) {
            const truncated = plaintext.length > 50 ? plaintext.substring(0, 50) + '...' : plaintext;
            return `<span class="text-dark" title="${plaintext.replace(/"/g, '&quot;')}">${truncated}</span>`;
        } else {
            return `<span class="text-muted"><i class="fas fa-lock me-1 small"></i><span class="content-masked">••••••••</span></span>`;
        }
    }
    
    const allColumnsList = ['mobileNumber', 'senderId', 'status', 'sentTime', 'deliveryTime', 'completedTime', 'cost', 'messageType', 'subAccount', 'user', 'origin', 'country', 'parts', 'encoding', 'messageId', 'content'];
    const defaultColumns = { visible: ['mobileNumber', 'senderId', 'status', 'sentTime', 'deliveryTime', 'completedTime', 'cost'], order: allColumnsList };
    let columnConfig = loadColumnConfig();
    
    function loadColumnConfig() {
        try {
            const saved = localStorage.getItem(STORAGE_KEY);
            if (saved) {
                const parsed = JSON.parse(saved);
                if (parsed.visible && Array.isArray(parsed.visible)) return parsed;
            }
        } catch (e) { console.error('Error loading column config:', e); }
        return { visible: [...defaultColumns.visible], order: [...defaultColumns.order] };
    }
    
    function saveColumnConfig() {
        try { localStorage.setItem(STORAGE_KEY, JSON.stringify(columnConfig)); } catch (e) { console.error('Error saving column config:', e); }
    }
    
    function applyColumnVisibility() {
        document.querySelectorAll('[data-column]').forEach(el => {
            const colName = el.getAttribute('data-column');
            if (columnConfig.visible.includes(colName) || colName === 'actions') {
                el.classList.remove('d-none');
            } else {
                el.classList.add('d-none');
            }
        });
        document.querySelectorAll('.column-toggle').forEach(cb => {
            const colName = cb.getAttribute('data-column') || cb.id.replace('col-', '');
            cb.checked = columnConfig.visible.includes(colName);
        });
    }
    
    // Format date for display
    function formatDateTime(date) {
        if (!date) return '-';
        const d = new Date(date);
        return d.toLocaleDateString('en-GB') + ' ' + d.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' });
    }
    
    // Create table row from message data
    function createRow(msg) {
        const statusText = msg.status.text;
        const typeClass = msg.messageType.class;
        const typeText = msg.messageType.text;
        const encodingClass = msg.encoding.class;
        const encodingText = msg.encoding.text;
        
        // Determine row class based on status (Fillow contextual table classes)
        let rowClass = '';
        if (statusText === 'Delivered') {
            rowClass = 'table-success';
        } else if (statusText === 'Pending') {
            rowClass = 'table-primary';
        } else if (['Undeliverable', 'Rejected', 'Expired', 'Failed', 'Blocked', 'Blacklisted'].includes(statusText)) {
            rowClass = 'table-danger';
        }
        
        return `<tr class="${rowClass}">
            <td class="py-2 ${columnConfig.visible.includes('mobileNumber') ? '' : 'd-none'}" data-column="mobileNumber"><span class="mobile-masked">${msg.mobileNumber}</span></td>
            <td class="py-2 ${columnConfig.visible.includes('senderId') ? '' : 'd-none'}" data-column="senderId">${msg.senderId}</td>
            <td class="py-2 ${columnConfig.visible.includes('status') ? '' : 'd-none'}" data-column="status">${statusText}</td>
            <td class="py-2 ${columnConfig.visible.includes('sentTime') ? '' : 'd-none'}" data-column="sentTime">${formatDateTime(msg.sentTime)}</td>
            <td class="py-2 ${columnConfig.visible.includes('deliveryTime') ? '' : 'd-none'}" data-column="deliveryTime">${formatDateTime(msg.deliveryTime)}</td>
            <td class="py-2 ${columnConfig.visible.includes('completedTime') ? '' : 'd-none'}" data-column="completedTime">${formatDateTime(msg.completedTime)}</td>
            <td class="py-2 ${columnConfig.visible.includes('cost') ? '' : 'd-none'}" data-column="cost">£${msg.cost}</td>
            <td class="py-2 ${columnConfig.visible.includes('messageType') ? '' : 'd-none'}" data-column="messageType"><span class="badge ${typeClass}">${typeText}</span></td>
            <td class="py-2 ${columnConfig.visible.includes('subAccount') ? '' : 'd-none'}" data-column="subAccount">${msg.subAccount}</td>
            <td class="py-2 ${columnConfig.visible.includes('user') ? '' : 'd-none'}" data-column="user">${msg.user}</td>
            <td class="py-2 ${columnConfig.visible.includes('origin') ? '' : 'd-none'}" data-column="origin">${msg.origin}</td>
            <td class="py-2 ${columnConfig.visible.includes('country') ? '' : 'd-none'}" data-column="country">${msg.country}</td>
            <td class="py-2 ${columnConfig.visible.includes('parts') ? '' : 'd-none'}" data-column="parts">${msg.parts}</td>
            <td class="py-2 ${columnConfig.visible.includes('encoding') ? '' : 'd-none'}" data-column="encoding"><span class="badge ${encodingClass}">${encodingText}</span></td>
            <td class="py-2 ${columnConfig.visible.includes('messageId') ? '' : 'd-none'}" data-column="messageId">${msg.id}</td>
            <td class="py-2 ${columnConfig.visible.includes('content') ? '' : 'd-none'}" data-column="content">${renderMessageContent(msg.content)}</td>
            <td class="py-2 text-center" data-column="actions">
                <div class="dropdown">
                    <span class="action-dots" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="cursor: pointer;">
                        <i class="fas fa-ellipsis-v"></i>
                    </span>
                    <div class="dropdown-menu dropdown-menu-end border py-0">
                        <div class="dropdown-content">
                            <a class="dropdown-item" href="#!" onclick="viewMessageDetails('${msg.id}'); return false;"><i class="fas fa-eye me-2 text-info"></i>View Details</a>
                            <a class="dropdown-item" href="#!" onclick="copyToClipboard('${msg.id}', 'Message ID'); return false;"><i class="fas fa-copy me-2 text-primary"></i>Copy Message ID</a>
                            <a class="dropdown-item" href="#!" onclick="copyToClipboard('${msg.mobileNumberRaw}', 'Mobile Number'); return false;"><i class="fas fa-phone me-2 text-success"></i>Copy Mobile Number</a>
                        </div>
                    </div>
                </div>
            </td>
        </tr>`;
    }
    
    // Load messages from mock API
    async function loadMessages(reset = false) {
        if (isLoading) return;
        if (!reset && !hasMore) return;
        
        isLoading = true;
        
        if (reset) {
            currentPage = 1;
            tableBody.innerHTML = `<tr id="loadingInitialRow"><td colspan="17" class="text-center py-5"><div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div><span class="text-muted">Loading messages...</span></td></tr>`;
            noResultsState.classList.add('d-none');
        } else {
            loadingMore.classList.remove('d-none');
        }
        
        try {
            const response = await MockAPI.fetchMessages(filterState, currentPage, PAGE_SIZE);
            const { data, meta } = response;
            
            if (reset) {
                tableBody.innerHTML = '';
                totalMessages = meta.total;
                if (totalCount) totalCount.textContent = totalMessages.toLocaleString();
                
                // Update summary bar if it exists
                const summaryTotalEl = document.getElementById('summaryTotal');
                const summaryPartsEl = document.getElementById('summaryParts');
                const renderedCountEl = document.getElementById('renderedCount');
                if (summaryTotalEl) summaryTotalEl.textContent = totalMessages.toLocaleString();
                if (summaryPartsEl) summaryPartsEl.textContent = Math.floor(totalMessages * 1.15).toLocaleString();
                if (summaryBar) summaryBar.style.display = 'block';
            }
            
            // Check max rows limit
            const currentRows = tableBody.querySelectorAll('tr').length;
            if (currentRows >= MAX_ROWS) {
                hasMore = false;
                console.log('[Message Log] Max rows limit reached');
                return;
            }
            
            // Append rows
            data.forEach(msg => {
                tableBody.insertAdjacentHTML('beforeend', createRow(msg));
            });
            
            // Update counts
            const rowCount = tableBody.querySelectorAll('tr').length;
            if (displayedCount) displayedCount.textContent = rowCount.toLocaleString();
            const renderedCount = document.getElementById('renderedCount');
            if (renderedCount) renderedCount.textContent = rowCount.toLocaleString();
            
            // Update pagination state
            hasMore = meta.hasMore && rowCount < MAX_ROWS;
            currentPage++;
            
            // Show no results state
            if (reset && data.length === 0) {
                noResultsState.classList.remove('d-none');
            }
            
        } catch (error) {
            console.error('[Message Log] Error loading messages:', error);
            if (reset) {
                tableBody.innerHTML = `<tr><td colspan="17" class="text-center py-5 text-danger"><i class="fas fa-exclamation-circle me-2"></i>Error loading messages. Please try again.</td></tr>`;
            }
        } finally {
            isLoading = false;
            loadingMore.classList.add('d-none');
        }
    }
    
    // Infinite scroll
    tableContainer?.addEventListener('scroll', function() {
        if (isLoading || !hasMore) return;
        const { scrollTop, scrollHeight, clientHeight } = this;
        if (scrollTop + clientHeight >= scrollHeight - 100) {
            loadMessages(false);
        }
    });
    
    // Initial load
    loadMessages(true);
    
    // ========================================
    // Filter handling (existing logic)
    // ========================================
    const labelMappings = {
        subAccounts: { main: 'Main Account', marketing: 'Marketing Team', support: 'Support Team', sales: 'Sales Team' },
        users: { john: 'John Smith', sarah: 'Sarah Johnson', mike: 'Mike Williams', emma: 'Emma Davis' },
        origins: { portal: 'Portal', api: 'API', 'email-to-sms': 'Email-to-SMS', integration: 'Integration' },
        statuses: { delivered: 'Delivered', pending: 'Pending', undeliverable: 'Undeliverable', rejected: 'Rejected' },
        countries: { uk: 'United Kingdom', us: 'United States', de: 'Germany', fr: 'France', es: 'Spain', ie: 'Ireland' },
        messageTypes: { sms: 'SMS', 'rcs-basic': 'RCS Basic', 'rcs-rich': 'RCS Rich' }
    };
    
    function formatDateInput(date) {
        return date.toISOString().split('T')[0];
    }
    
    // Date preset buttons
    document.querySelectorAll('.date-preset-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.date-preset-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            const preset = this.dataset.preset;
            const today = new Date();
            let fromDate, toDate = today;
            switch(preset) {
                case 'today': fromDate = today; break;
                case 'yesterday': fromDate = new Date(today); fromDate.setDate(fromDate.getDate() - 1); toDate = fromDate; break;
                case '7days': fromDate = new Date(today); fromDate.setDate(fromDate.getDate() - 7); break;
                case '30days': fromDate = new Date(today); fromDate.setDate(fromDate.getDate() - 30); break;
                case 'thismonth': fromDate = new Date(today.getFullYear(), today.getMonth(), 1); break;
                case 'lastmonth': fromDate = new Date(today.getFullYear(), today.getMonth() - 1, 1); toDate = new Date(today.getFullYear(), today.getMonth(), 0); break;
            }
            document.getElementById('filterDateFrom').value = formatDateInput(fromDate);
            document.getElementById('filterDateTo').value = formatDateInput(toDate);
            pendingFilters.dateFrom = formatDateInput(fromDate);
            pendingFilters.dateTo = formatDateInput(toDate);
        });
    });
    
    document.getElementById('filterDateFrom')?.addEventListener('change', function() {
        pendingFilters.dateFrom = this.value;
        document.querySelectorAll('.date-preset-btn').forEach(b => b.classList.remove('active'));
    });
    
    document.getElementById('filterDateTo')?.addEventListener('change', function() {
        pendingFilters.dateTo = this.value;
        document.querySelectorAll('.date-preset-btn').forEach(b => b.classList.remove('active'));
    });
    
    // Multi-select dropdown handlers
    function setupMultiselect(menuId, toggleId, stateKey, defaultText) {
        const menu = document.getElementById(menuId);
        const toggle = document.getElementById(toggleId);
        if (!menu || !toggle) return;
        menu.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const selected = Array.from(menu.querySelectorAll('input:checked')).map(cb => cb.value);
                pendingFilters[stateKey] = selected;
                updateMultiselectToggle(toggle, selected.length, defaultText);
            });
        });
    }
    
    function updateMultiselectToggle(toggle, count, defaultText) {
        if (count > 0) {
            toggle.querySelector('.filter-text').textContent = `${count} selected`;
        } else {
            toggle.querySelector('.filter-text').textContent = defaultText;
        }
    }
    
    setupMultiselect('subAccountsMenu', 'subAccountsDropdown', 'subAccounts', 'All Sub Accounts');
    setupMultiselect('usersMenu', 'usersDropdown', 'users', 'All Users');
    setupMultiselect('originsMenu', 'originsDropdown', 'origins', 'All Origins');
    setupMultiselect('statusesMenu', 'statusesDropdown', 'statuses', 'All Statuses');
    setupMultiselect('countriesMenu', 'countriesDropdown', 'countries', 'All Countries');
    setupMultiselect('messageTypesMenu', 'messageTypesDropdown', 'messageTypes', 'All Types');
    
    // Free text multi-value inputs (Mobile Number, Message ID)
    function setupMultiValueInput(inputId, stateKey) {
        const input = document.getElementById(inputId);
        if (!input) return;
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const value = this.value.trim();
                if (value && !pendingFilters[stateKey].includes(value)) {
                    pendingFilters[stateKey].push(value);
                    updateMultiValueDisplay(inputId, stateKey);
                }
                this.value = '';
            }
        });
    }
    
    function updateMultiValueDisplay(inputId, stateKey) {
        console.log(`[Filter] ${stateKey}:`, pendingFilters[stateKey]);
    }
    
    setupMultiValueInput('filterMobileNumber', 'mobileNumbers');
    setupMultiValueInput('filterMessageId', 'messageIds');
    
    // SenderID input
    document.getElementById('filterSenderId')?.addEventListener('input', function() {
        pendingFilters.senderId = this.value.trim();
    });
    
    // Apply Filters button
    document.getElementById('btnApplyFilters')?.addEventListener('click', function() {
        filterState = JSON.parse(JSON.stringify(pendingFilters));
        console.log('[Filter] Applied filters:', filterState);
        updateActiveFilterChips();
        hasMore = true;
        loadMessages(true);
    });
    
    // Reset Filters button (only resets UI state, does not apply)
    document.getElementById('btnResetFilters')?.addEventListener('click', function() {
        pendingFilters = {
            dateFrom: '', dateTo: '', subAccounts: [], users: [], origins: [],
            mobileNumbers: [], senderId: '', statuses: [], countries: [], messageTypes: [], messageIds: []
        };
        
        // Reset UI elements
        document.getElementById('filterDateFrom').value = '';
        document.getElementById('filterDateTo').value = '';
        document.getElementById('filterMobileNumber').value = '';
        document.getElementById('filterSenderId').value = '';
        document.getElementById('filterMessageId').value = '';
        document.querySelectorAll('.date-preset-btn').forEach(b => b.classList.remove('active'));
        
        // Reset multi-selects
        document.querySelectorAll('.dropdown-menu input[type="checkbox"]').forEach(cb => {
            cb.checked = false;
        });
        
        // Reset toggle text
        ['subAccountsDropdown', 'usersDropdown', 'originsDropdown', 'statusesDropdown', 'countriesDropdown', 'messageTypesDropdown'].forEach(id => {
            const toggle = document.getElementById(id);
            if (toggle) toggle.querySelector('.filter-text').textContent = toggle.dataset.default || 'All';
        });
        
        console.log('[Filter] Pending filters reset (not applied)');
    });
    
    // Update active filter chips
    function updateActiveFilterChips() {
        const container = document.getElementById('activeFiltersChips');
        const wrapper = document.getElementById('activeFiltersContainer');
        if (!container) return;
        container.innerHTML = '';
        
        let hasFilters = false;
        
        if (filterState.dateFrom || filterState.dateTo) {
            const dateText = `${filterState.dateFrom || 'Start'} to ${filterState.dateTo || 'End'}`;
            container.innerHTML += createChip('Date Range', dateText, 'dateRange');
            hasFilters = true;
        }
        
        ['subAccounts', 'users', 'origins', 'statuses', 'countries', 'messageTypes'].forEach(key => {
            if (filterState[key].length > 0) {
                const labels = filterState[key].map(v => labelMappings[key]?.[v] || v);
                container.innerHTML += createChip(key.replace(/([A-Z])/g, ' $1').trim(), labels.join(', '), key);
                hasFilters = true;
            }
        });
        
        if (filterState.senderId) {
            container.innerHTML += createChip('SenderID', filterState.senderId, 'senderId');
            hasFilters = true;
        }
        
        if (filterState.mobileNumbers.length > 0) {
            container.innerHTML += createChip('Mobile', `${filterState.mobileNumbers.length} number(s)`, 'mobileNumbers');
            hasFilters = true;
        }
        
        if (filterState.messageIds.length > 0) {
            container.innerHTML += createChip('Message ID', `${filterState.messageIds.length} ID(s)`, 'messageIds');
            hasFilters = true;
        }
        
        if (wrapper) {
            wrapper.style.display = hasFilters ? 'block' : 'none';
        }
    }
    
    function createChip(label, value, key) {
        return `<span class="badge bg-primary bg-opacity-10 text-primary me-2 mb-1 d-inline-flex align-items-center">
            <span class="fw-bold me-1">${label}:</span> ${value}
            <button type="button" class="btn-close btn-close-sm ms-2" style="font-size: 0.6rem;" data-filter="${key}"></button>
        </span>`;
    }
    
    // Column toggle handlers
    document.querySelectorAll('.column-toggle').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const colName = this.getAttribute('data-column') || this.id.replace('col-', '');
            if (this.checked) {
                if (!columnConfig.visible.includes(colName)) columnConfig.visible.push(colName);
            } else {
                columnConfig.visible = columnConfig.visible.filter(c => c !== colName);
            }
            saveColumnConfig();
            applyColumnVisibility();
        });
    });
    
    document.getElementById('btnResetColumns')?.addEventListener('click', function() {
        columnConfig = { visible: [...defaultColumns.visible], order: [...defaultColumns.order] };
        saveColumnConfig();
        applyColumnVisibility();
    });
    
    // Clear filters from empty state
    document.getElementById('btnClearFiltersEmpty')?.addEventListener('click', function() {
        document.getElementById('btnResetFilters').click();
        document.getElementById('btnApplyFilters').click();
    });
    
    // Apply initial column visibility
    applyColumnVisibility();
});
</script>
@endpush
