@extends('layouts.quicksms')

@section('title', 'Numbers')

@push('styles')
<style>
.numbers-table-container {
    overflow-x: auto;
    background: #fff;
    border-radius: 0.75rem;
    border: 1px solid #e9ecef;
}
.numbers-table {
    width: 100%;
    margin: 0;
    min-width: 1200px;
    table-layout: fixed;
}
.numbers-table thead th {
    background: #f8f9fa;
    padding: 0.5rem 0.35rem;
    font-weight: 600;
    font-size: 0.75rem;
    color: #495057;
    border-bottom: 1px solid #e9ecef;
    cursor: pointer;
    white-space: nowrap;
    user-select: none;
}
.numbers-table thead th.checkbox-col { width: 40px !important; min-width: 40px; max-width: 40px; }
.numbers-table thead th:nth-child(2) { width: 12%; }
.numbers-table thead th:nth-child(3) { width: 10%; }
.numbers-table thead th:nth-child(4) { width: 7%; }
.numbers-table thead th:nth-child(5) { width: 7%; }
.numbers-table thead th:nth-child(6) { width: 14%; }
.numbers-table thead th:nth-child(7) { width: 6%; }
.numbers-table thead th:nth-child(8) { width: 10%; }
.numbers-table thead th:nth-child(9) { width: 9%; }
.numbers-table thead th:nth-child(10) { width: 11%; }
.numbers-table thead th:last-child { 
    width: 7%; 
    position: sticky;
    right: 0;
    background: #f8f9fa;
    z-index: 2;
    cursor: default;
}
.numbers-table thead th:hover {
    background: #e9ecef;
}
.numbers-table thead th:last-child:hover {
    background: #f8f9fa;
}
.numbers-table thead th .sort-icon {
    margin-left: 0.25rem;
    opacity: 0.4;
}
.numbers-table thead th.sorted .sort-icon {
    opacity: 1;
    color: var(--primary);
}
.numbers-table tbody td {
    padding: 0.5rem 0.35rem;
    vertical-align: middle;
    border-bottom: 1px solid #f1f3f5;
    font-size: 0.8rem;
}
.numbers-table tbody td:last-child {
    position: sticky;
    right: 0;
    background: #fff;
    z-index: 1;
    box-shadow: -2px 0 4px rgba(0,0,0,0.05);
}
.numbers-table tbody td:last-child:has(.dropdown.show),
.numbers-table tbody td:last-child.dropdown-active {
    z-index: 2000 !important;
}
.numbers-table .dropdown-menu {
    z-index: 2050 !important;
    position: absolute !important;
    inset: auto 0 auto auto !important;
    transform: translate(0, 0) !important;
}
.numbers-table .dropdown {
    position: relative;
}
.numbers-table-container:has(.dropdown.show),
.numbers-table-container.has-dropdown-open {
    overflow: visible !important;
}
.numbers-table-container .table-responsive:has(.dropdown.show),
.numbers-table-container.has-dropdown-open .table-responsive {
    overflow: visible !important;
}
.numbers-table tbody tr:last-child td {
    border-bottom: none;
}
.numbers-table tbody tr:hover td {
    background: #f8f9fa;
    cursor: pointer;
}
.numbers-table tbody tr:hover td:last-child {
    background: #f8f9fa;
}
.number-value {
    font-weight: 500;
    color: #343a40;
}
.badge-active {
    background: rgba(28, 187, 140, 0.15);
    color: #1cbb8c;
}
.badge-suspended {
    background: rgba(220, 53, 69, 0.15);
    color: #dc3545;
}
.badge-pending {
    background: rgba(255, 191, 0, 0.15);
    color: #cc9900;
}
.capability-pill {
    display: inline-block;
    padding: 0.15rem 0.5rem;
    font-size: 0.7rem;
    font-weight: 500;
    border-radius: 1rem;
    margin-right: 0.25rem;
    margin-bottom: 0.15rem;
}
.capability-api {
    background: rgba(48, 101, 208, 0.15);
    color: #3065D0;
}
.capability-portal {
    background: rgba(111, 66, 193, 0.15);
    color: #6f42c1;
}
.capability-inbox {
    background: rgba(28, 187, 140, 0.15);
    color: #1cbb8c;
}
.capability-optout {
    background: rgba(214, 83, 193, 0.15);
    color: #D653C1;
}
.mode-btn {
    border: 2px solid #dee2e6;
    background: #fff;
    padding: 1rem;
    text-align: center;
    transition: all 0.2s ease;
}
.mode-btn:hover {
    border-color: #886CC0;
    background: rgba(136, 108, 192, 0.05);
}
.mode-btn.active {
    border-color: #886CC0;
    background: rgba(136, 108, 192, 0.1);
    box-shadow: 0 0 0 3px rgba(136, 108, 192, 0.2);
}
.mode-btn.active i {
    color: #886CC0;
}
.mode-btn i {
    font-size: 1.5rem;
    color: #6c757d;
}
.mode-features {
    background: #f8f9fa;
    border-radius: 0.5rem;
    padding: 1rem;
}
.mode-features ul {
    list-style: none;
}
.mode-features li {
    padding: 0.25rem 0;
}
.capability-toggle {
    padding: 0.75rem;
    background: #f8f9fa;
    border-radius: 0.5rem;
    border: 1px solid #e9ecef;
}
.capability-toggle:hover {
    background: #f1f3f5;
}
.audit-history-list {
    max-height: 200px;
    overflow-y: auto;
}
.audit-history-item {
    padding: 0.5rem;
    border-left: 3px solid #886CC0;
    background: #f8f9fa;
    margin-bottom: 0.5rem;
    border-radius: 0 0.25rem 0.25rem 0;
}
.audit-history-item:last-child {
    margin-bottom: 0;
}
.audit-history-item .audit-action {
    font-weight: 600;
    font-size: 0.8rem;
    color: #495057;
}
.audit-history-item .audit-details {
    font-size: 0.75rem;
    color: #6c757d;
}
.audit-history-item .audit-meta {
    font-size: 0.7rem;
    color: #adb5bd;
    margin-top: 0.25rem;
}
.form-switch .form-check-input {
    width: 2.5rem;
    height: 1.25rem;
    cursor: pointer;
}
.form-switch .form-check-input:checked {
    background-color: #886CC0;
    border-color: #886CC0;
}
.subaccount-defaults-item {
    border: 1px solid #e9ecef;
    border-radius: 0.5rem;
    padding: 0.75rem;
    margin-bottom: 0.75rem;
    background: #fff;
}
.subaccount-defaults-item .subaccount-name {
    font-weight: 600;
    font-size: 0.85rem;
    color: #495057;
    margin-bottom: 0.5rem;
}
.default-toggle {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.5rem;
    border-radius: 0.375rem;
    margin-bottom: 0.25rem;
}
.default-toggle:hover {
    background: #f8f9fa;
}
.default-toggle.is-default {
    background: rgba(136, 108, 192, 0.1);
    border: 1px solid rgba(136, 108, 192, 0.3);
}
.default-toggle label {
    font-size: 0.8rem;
    color: #495057;
    margin-bottom: 0;
}
.default-badge {
    font-size: 0.65rem;
    padding: 0.2rem 0.5rem;
    background: #886CC0;
    color: #fff;
    border-radius: 1rem;
}
.api-restrictions {
    background: #f8f9fa;
    border-radius: 0.5rem;
    padding: 0.75rem;
    border: 1px solid #e9ecef;
}
.api-restrictions ul {
    list-style: none;
}
.api-restrictions li {
    padding: 0.25rem 0;
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
.suspended-row {
    opacity: 0.6;
    background-color: #f8f9fa;
}
.suspended-row:hover {
    opacity: 0.8;
}
.table-footer {
    padding: 1rem;
    border-top: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.pagination-info {
    font-size: 0.85rem;
    color: #6c757d;
}
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    background: #fff;
    border-radius: 0.75rem;
    border: 1px solid #e9ecef;
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
.filter-chip {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.75rem;
    background-color: rgba(136, 108, 192, 0.15);
    color: #886CC0;
    border-radius: 1rem;
    font-size: 0.75rem;
    font-weight: 500;
    margin-right: 0.5rem;
    margin-bottom: 0.25rem;
}
.filter-chip .chip-label {
    margin-right: 0.25rem;
    color: #6c757d;
}
.filter-chip .remove-chip {
    margin-left: 0.5rem;
    cursor: pointer;
    opacity: 0.7;
    font-size: 0.7rem;
}
.filter-chip .remove-chip:hover {
    opacity: 1;
}
.cost-value {
    font-weight: 500;
    color: #343a40;
}
.bulk-action-bar {
    display: none;
    background: linear-gradient(135deg, rgba(136, 108, 192, 0.1) 0%, rgba(111, 66, 193, 0.15) 100%);
    border: 1px solid rgba(136, 108, 192, 0.3);
    border-radius: 0.5rem;
    padding: 0.75rem 1rem;
    margin-bottom: 1rem;
    align-items: center;
    gap: 1rem;
}
.bulk-action-bar.show {
    display: flex;
}
.bulk-action-bar .selection-info {
    font-weight: 500;
    color: #495057;
}
.bulk-action-bar .selection-count {
    color: var(--primary);
    font-weight: 600;
}
.bulk-action-bar .btn-bulk {
    padding: 0.35rem 0.75rem;
    font-size: 0.8rem;
}
.numbers-table tbody td.checkbox-col {
    width: 40px !important;
    min-width: 40px;
    max-width: 40px;
    text-align: center;
    cursor: default;
}
.numbers-table thead th.checkbox-col:hover {
    background: #f8f9fa;
}
.row-checkbox {
    cursor: pointer;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row page-titles">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="#">Management</a></li>
            <li class="breadcrumb-item active"><a href="javascript:void(0)">Numbers</a></li>
        </ol>
    </div>
    
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
            <h5 class="card-title mb-0">Numbers Library</h5>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#filtersPanel">
                    <i class="fas fa-filter me-1"></i> Filters
                </button>
                <button type="button" class="btn btn-primary btn-sm" id="btnConfigureSelected" disabled>
                    <i class="fas fa-cog me-1"></i>Configure
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <div class="input-group" style="max-width: 350px;">
                    <span class="input-group-text bg-transparent border-end-0">
                        <i class="fas fa-search text-muted"></i>
                    </span>
                    <input type="text" class="form-control border-start-0" id="searchInput" placeholder="Search by number or keyword...">
                </div>
            </div>
        
            <div class="collapse" id="filtersPanel">
                <div class="card card-body border-0 rounded-0" style="background-color: #f0ebf8; border-bottom: 1px solid #e9ecef !important;">
                    <div class="row g-3 align-items-end">
                        <div class="col-6 col-md-4 col-lg-2">
                            <label class="form-label small fw-bold">Country</label>
                            <div class="dropdown multiselect-dropdown" data-filter="countries">
                                <button class="btn btn-sm dropdown-toggle w-100 text-start d-flex justify-content-between align-items-center" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" style="background-color: #fff; border: 1px solid #ced4da; color: #495057;">
                                    <span class="dropdown-label">All Countries</span>
                                </button>
                                <div class="dropdown-menu w-100 p-2">
                                    <div class="d-flex justify-content-between mb-2 border-bottom pb-2">
                                        <a href="#" class="small text-decoration-none select-all-btn">Select All</a>
                                        <a href="#" class="small text-decoration-none clear-all-btn">Clear</a>
                                    </div>
                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="UK" id="countryUK"><label class="form-check-label small" for="countryUK">United Kingdom</label></div>
                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="US" id="countryUS"><label class="form-check-label small" for="countryUS">United States</label></div>
                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="DE" id="countryDE"><label class="form-check-label small" for="countryDE">Germany</label></div>
                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="FR" id="countryFR"><label class="form-check-label small" for="countryFR">France</label></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-4 col-lg-2">
                            <label class="form-label small fw-bold">Type</label>
                            <div class="dropdown multiselect-dropdown" data-filter="types">
                                <button class="btn btn-sm dropdown-toggle w-100 text-start d-flex justify-content-between align-items-center" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" style="background-color: #fff; border: 1px solid #ced4da; color: #495057;">
                                    <span class="dropdown-label">All Types</span>
                                </button>
                                <div class="dropdown-menu w-100 p-2">
                                    <div class="d-flex justify-content-between mb-2 border-bottom pb-2">
                                        <a href="#" class="small text-decoration-none select-all-btn">Select All</a>
                                        <a href="#" class="small text-decoration-none clear-all-btn">Clear</a>
                                    </div>
                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="vmn" id="typeVMN"><label class="form-check-label small" for="typeVMN">VMN (Virtual Mobile Number)</label></div>
                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="dedicated_shortcode" id="typeDedicated"><label class="form-check-label small" for="typeDedicated">Dedicated Shortcode</label></div>
                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="shortcode_keyword" id="typeKeyword"><label class="form-check-label small" for="typeKeyword">Shortcode Keyword</label></div>
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
                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="active" id="statusActive"><label class="form-check-label small" for="statusActive">Active</label></div>
                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="suspended" id="statusSuspended"><label class="form-check-label small" for="statusSuspended">Suspended</label></div>
                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="pending" id="statusPending"><label class="form-check-label small" for="statusPending">Pending</label></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-4 col-lg-2">
                            <label class="form-label small fw-bold">Mode</label>
                            <div class="dropdown multiselect-dropdown" data-filter="modes">
                                <button class="btn btn-sm dropdown-toggle w-100 text-start d-flex justify-content-between align-items-center" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" style="background-color: #fff; border: 1px solid #ced4da; color: #495057;">
                                    <span class="dropdown-label">All Modes</span>
                                </button>
                                <div class="dropdown-menu w-100 p-2">
                                    <div class="d-flex justify-content-between mb-2 border-bottom pb-2">
                                        <a href="#" class="small text-decoration-none select-all-btn">Select All</a>
                                        <a href="#" class="small text-decoration-none clear-all-btn">Clear</a>
                                    </div>
                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="portal" id="modePortal"><label class="form-check-label small" for="modePortal">Portal</label></div>
                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="api" id="modeAPI"><label class="form-check-label small" for="modeAPI">API</label></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-4 col-lg-2">
                            <label class="form-label small fw-bold">Capability</label>
                            <div class="dropdown multiselect-dropdown" data-filter="capabilities">
                                <button class="btn btn-sm dropdown-toggle w-100 text-start d-flex justify-content-between align-items-center" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" style="background-color: #fff; border: 1px solid #ced4da; color: #495057;">
                                    <span class="dropdown-label">All Capabilities</span>
                                </button>
                                <div class="dropdown-menu w-100 p-2">
                                    <div class="d-flex justify-content-between mb-2 border-bottom pb-2">
                                        <a href="#" class="small text-decoration-none select-all-btn">Select All</a>
                                        <a href="#" class="small text-decoration-none clear-all-btn">Clear</a>
                                    </div>
                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="api" id="capAPI"><label class="form-check-label small" for="capAPI">API</label></div>
                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="portal" id="capPortal"><label class="form-check-label small" for="capPortal">Portal SenderID</label></div>
                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="inbox" id="capInbox"><label class="form-check-label small" for="capInbox">Inbox</label></div>
                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="optout" id="capOptout"><label class="form-check-label small" for="capOptout">Opt-out</label></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row g-3 align-items-end mt-1">
                        <div class="col-6 col-md-4 col-lg-2">
                            <label class="form-label small fw-bold">Sub-Account</label>
                            <div class="dropdown multiselect-dropdown" data-filter="subAccounts">
                                <button class="btn btn-sm dropdown-toggle w-100 text-start d-flex justify-content-between align-items-center" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" style="background-color: #fff; border: 1px solid #ced4da; color: #495057;">
                                    <span class="dropdown-label">All Sub-Accounts</span>
                                </button>
                                <div class="dropdown-menu w-100 p-2">
                                    <div class="d-flex justify-content-between mb-2 border-bottom pb-2">
                                        <a href="#" class="small text-decoration-none select-all-btn">Select All</a>
                                        <a href="#" class="small text-decoration-none clear-all-btn">Clear</a>
                                    </div>
                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="Main Account" id="subAccMain"><label class="form-check-label small" for="subAccMain">Main Account</label></div>
                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="Marketing" id="subAccMarketing"><label class="form-check-label small" for="subAccMarketing">Marketing</label></div>
                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="Support" id="subAccSupport"><label class="form-check-label small" for="subAccSupport">Support</label></div>
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
            
            <div class="bulk-action-bar" id="bulkActionBar">
                <div class="selection-info">
                    <span class="selection-count" id="selectedCount">0</span> number(s) selected
                </div>
                <div class="d-flex gap-2 ms-auto">
                    <button type="button" class="btn btn-warning btn-bulk" id="btnBulkSuspend" disabled>
                        <i class="fas fa-pause-circle me-1"></i> Suspend
                    </button>
                    <button type="button" class="btn btn-success btn-bulk" id="btnBulkReactivate" disabled>
                        <i class="fas fa-play-circle me-1"></i> Reactivate
                    </button>
                    <button type="button" class="btn btn-primary btn-bulk" id="btnBulkAssignSubAccounts" disabled>
                        <i class="fas fa-users me-1"></i> Assign Sub-Accounts
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-bulk" id="btnClearSelection">
                        <i class="fas fa-times me-1"></i> Clear
                    </button>
                </div>
            </div>
            
            <div class="numbers-table-container" id="numbersTableContainer">
                <div class="table-responsive">
                    <table class="numbers-table mb-0" id="numbersTable">
                        <thead>
                            <tr>
                                <th class="checkbox-col"><input type="checkbox" class="form-check-input" id="selectAllCheckbox" title="Select all"></th>
                                <th data-sort="number">Number <i class="fas fa-sort sort-icon"></i></th>
                                <th data-sort="country">Country <i class="fas fa-sort sort-icon"></i></th>
                                <th data-sort="type">Type <i class="fas fa-sort sort-icon"></i></th>
                                <th data-sort="status">Status <i class="fas fa-sort sort-icon"></i></th>
                                <th>Capabilities</th>
                                <th data-sort="mode">Mode <i class="fas fa-sort sort-icon"></i></th>
                                <th data-sort="subAccounts">Sub-Accounts <i class="fas fa-sort sort-icon"></i></th>
                                <th data-sort="monthlyCost">Monthly Cost <i class="fas fa-sort sort-icon"></i></th>
                                <th data-sort="lastUsed">Last Used <i class="fas fa-sort sort-icon"></i></th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="numbersTableBody">
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="empty-state" id="emptyState" style="display: none;">
                <div class="empty-state-icon">
                    <i class="fas fa-phone-alt"></i>
                </div>
                <h4>No Numbers Found</h4>
                <p>You don't have any numbers yet. Purchase your first number to start receiving messages.</p>
                <a href="{{ route('purchase.numbers') }}" class="btn btn-primary">
                    <i class="fas fa-cog me-1"></i> Configure
                </a>
            </div>
        
            <div class="table-footer mt-3">
                <div class="pagination-info">
                    Showing <span id="showingCount">0</span> of <span id="totalCount">0</span> numbers
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Number Configuration Offcanvas --}}
<div class="offcanvas offcanvas-end" tabindex="-1" id="numberConfigDrawer" style="width: 500px;">
    <div class="offcanvas-header border-bottom py-3">
        <h6 class="offcanvas-title mb-0"><i class="fas fa-cog me-2 text-primary"></i>Number Configuration</h6>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-0">
        <div class="p-4 border-bottom">
            <h5 id="drawerNumber" class="mb-3 fw-semibold font-monospace">-</h5>
            <div class="d-flex flex-wrap gap-2 mb-2">
                <span id="drawerTypeBadge" class="badge rounded-pill badge-pastel-primary">-</span>
                <span id="drawerStatusBadge" class="badge rounded-pill">-</span>
                <span id="drawerModeBadge" class="badge rounded-pill badge-pastel-secondary">-</span>
            </div>
        </div>

        <div class="p-4">
            <div class="card mb-3">
                <div class="card-body p-3">
                    <h6 class="card-title mb-3"><i class="fas fa-info-circle me-2 text-primary"></i>Number Details</h6>
                    <div class="row mb-2">
                        <div class="col-5 text-muted small">Number</div>
                        <div class="col-7 small fw-medium font-monospace" id="drawerNumberDetail">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5 text-muted small">Country</div>
                        <div class="col-7 small" id="drawerCountry">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5 text-muted small">Type</div>
                        <div class="col-7 small" id="drawerType">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5 text-muted small">Mode</div>
                        <div class="col-7 small" id="drawerMode">-</div>
                    </div>
                    <div class="row">
                        <div class="col-5 text-muted small">Monthly Cost</div>
                        <div class="col-7 small fw-medium" id="drawerCost">-</div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body p-3">
                    <h6 class="card-title mb-3"><i class="fas fa-check-circle me-2 text-primary"></i>Capabilities</h6>
                    <div id="drawerCapabilities" class="d-flex flex-wrap gap-1">
                        -
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body p-3">
                    <h6 class="card-title mb-3"><i class="fas fa-users me-2 text-primary"></i>Assigned Sub-Accounts</h6>
                    <div id="drawerSubAccounts" class="small">
                        -
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body p-3">
                    <h6 class="card-title mb-3"><i class="fas fa-history me-2 text-primary"></i>Audit Trail</h6>
                    <div class="row mb-2">
                        <div class="col-5 text-muted small">Created</div>
                        <div class="col-7 small" id="drawerCreatedAt">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5 text-muted small">Created By</div>
                        <div class="col-7 small" id="drawerCreatedBy">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5 text-muted small">Last Modified</div>
                        <div class="col-7 small" id="drawerModifiedAt">-</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-5 text-muted small">Modified By</div>
                        <div class="col-7 small" id="drawerModifiedBy">-</div>
                    </div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small fw-bold">Recent Changes</span>
                        <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none" id="btnViewFullAudit">
                            View All <i class="fas fa-external-link-alt ms-1"></i>
                        </button>
                    </div>
                    <div id="drawerAuditHistory" class="audit-history-list">
                        <div class="text-muted small">No audit history available</div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body p-3">
                    <h6 class="card-title mb-3"><i class="fas fa-history me-2 text-primary"></i>Usage</h6>
                    <div class="row mb-2">
                        <div class="col-5 text-muted small">Last Used</div>
                        <div class="col-7 small" id="drawerLastUsed">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5 text-muted small">Purchase Date</div>
                        <div class="col-7 small" id="drawerPurchaseDate">-</div>
                    </div>
                    <div class="row">
                        <div class="col-5 text-muted small">Renewal Date</div>
                        <div class="col-7 small" id="drawerRenewalDate">-</div>
                    </div>
                </div>
            </div>

            <div class="card mb-3" id="modeSelectionCard">
                <div class="card-body p-3">
                    <h6 class="card-title mb-3"><i class="fas fa-exchange-alt me-2 text-primary"></i>Operating Mode</h6>
                    <p class="small text-muted mb-3">Each number must operate in exactly one mode. Switching mode will affect feature availability.</p>
                    
                    <div class="mode-selector d-flex gap-2">
                        <button type="button" class="btn mode-btn flex-fill" id="btnModePortal" data-mode="portal">
                            <i class="fas fa-desktop me-2"></i>
                            <span class="fw-semibold">Portal Mode</span>
                            <div class="small text-muted mt-1">Campaigns, Inbox, Opt-out</div>
                        </button>
                        <button type="button" class="btn mode-btn flex-fill" id="btnModeAPI" data-mode="api">
                            <i class="fas fa-code me-2"></i>
                            <span class="fw-semibold">API Mode</span>
                            <div class="small text-muted mt-1">API Integration Only</div>
                        </button>
                    </div>
                    
                    <div class="mode-features mt-3" id="portalModeFeatures" style="display: none;">
                        <div class="small fw-bold text-primary mb-2">Portal Mode Features:</div>
                        <ul class="small mb-0 ps-3">
                            <li class="text-success"><i class="fas fa-check me-1"></i>Available in Campaign Composer</li>
                            <li class="text-success"><i class="fas fa-check me-1"></i>Visible in Inbox for two-way messaging</li>
                            <li class="text-success"><i class="fas fa-check me-1"></i>Usable for Opt-out management</li>
                            <li class="text-success"><i class="fas fa-check me-1"></i>Available as Portal SenderID</li>
                            <li class="text-danger"><i class="fas fa-times me-1"></i>Not available via API</li>
                        </ul>
                    </div>
                    
                    <div class="mode-features mt-3" id="apiModeFeatures" style="display: none;">
                        <div class="small fw-bold text-primary mb-2">API Mode Features:</div>
                        <ul class="small mb-0 ps-3">
                            <li class="text-success"><i class="fas fa-check me-1"></i>Available via REST API</li>
                            <li class="text-danger"><i class="fas fa-times me-1"></i>Not visible in Portal SenderID picker</li>
                            <li class="text-danger"><i class="fas fa-times me-1"></i>Not available in Inbox</li>
                            <li class="text-danger"><i class="fas fa-times me-1"></i>Not usable for Campaigns</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="card mb-3" id="portalConfigCard" style="display: none;">
                <div class="card-body p-3">
                    <h6 class="card-title mb-3"><i class="fas fa-cogs me-2 text-primary"></i>Portal Configuration</h6>
                    <p class="small text-muted mb-3">Configure how this number is used within the Portal.</p>
                    
                    <div id="portalShortcodeKeywordNotice" class="alert alert-info small mb-3" style="display: none;">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Shortcode Keyword:</strong> This number can only be used for opt-out handling. SenderID and Inbox options are not available for shortcode keywords.
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold small">Sub-Account Assignment</label>
                        <p class="text-muted small mb-2">Controls visibility, defaults, and reporting scope.</p>
                        <div class="border rounded p-2" style="max-height: 150px; overflow-y: auto;">
                            <div class="form-check mb-1">
                                <input class="form-check-input portal-subacc-check" type="checkbox" value="Main Account" id="portalSubAccMain">
                                <label class="form-check-label small" for="portalSubAccMain">Main Account</label>
                            </div>
                            <div class="form-check mb-1">
                                <input class="form-check-input portal-subacc-check" type="checkbox" value="Marketing" id="portalSubAccMarketing">
                                <label class="form-check-label small" for="portalSubAccMarketing">Marketing</label>
                            </div>
                            <div class="form-check mb-1">
                                <input class="form-check-input portal-subacc-check" type="checkbox" value="Support" id="portalSubAccSupport">
                                <label class="form-check-label small" for="portalSubAccSupport">Support</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold small">Portal Capabilities</label>
                        <p class="text-muted small mb-2">Enable or disable specific features for this number.</p>
                        
                        <div class="capability-toggle mb-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1 me-3">
                                    <div class="fw-semibold small">Allow as SenderID</div>
                                    <div class="text-muted small">Makes this number selectable in Campaign Builder</div>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="toggleSenderID" checked>
                                </div>
                            </div>
                        </div>
                        
                        <div class="capability-toggle mb-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1 me-3">
                                    <div class="fw-semibold small">Enable Inbox Replies</div>
                                    <div class="text-muted small">SMS replies appear in Inbox for two-way messaging</div>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="toggleInboxReplies" checked>
                                </div>
                            </div>
                        </div>
                        
                        <div class="capability-toggle">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1 me-3">
                                    <div class="fw-semibold small">Enable Opt-out Handling</div>
                                    <div class="text-muted small">STOP messages automatically update opt-out lists</div>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="toggleOptout" checked>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3" id="defaultsSection">
                        <label class="form-label fw-bold small">Defaults Per Sub-Account</label>
                        <p class="text-muted small mb-2">Set this number as default for specific functions. Only one default per capability per sub-account.</p>
                        
                        <div id="subAccountDefaults">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3" id="apiConfigCard" style="display: none;">
                <div class="card-body p-3">
                    <h6 class="card-title mb-3"><i class="fas fa-plug me-2 text-primary"></i>API Configuration</h6>
                    <p class="small text-muted mb-3">Configure API-specific settings for this number.</p>
                    
                    <div class="alert alert-info small mb-4">
                        <i class="fas fa-info-circle me-2"></i>
                        API mode numbers are used exclusively via REST API. They cannot be used as SenderID, Inbox number, or in Portal features.
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold small">Sub-Account Attribution</label>
                        <p class="text-muted small mb-2">API numbers can belong to only one sub-account for reporting and access control.</p>
                        <select class="form-select form-select-sm" id="apiSubAccountSelect">
                            <option value="">-- Select Sub-Account --</option>
                            <option value="Main Account">Main Account</option>
                            <option value="Marketing">Marketing</option>
                            <option value="Support">Support</option>
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold small">Inbound Message Handling</label>
                        <p class="text-muted small mb-2">Configure how incoming messages to this number are processed.</p>
                        
                        <div class="capability-toggle mb-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1 me-3">
                                    <div class="fw-semibold small">Enable Inbound Forwarding</div>
                                    <div class="text-muted small">Forward incoming messages to a webhook URL</div>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="toggleInboundForwarding">
                                </div>
                            </div>
                        </div>
                        
                        <div id="inboundUrlSection" style="display: none;">
                            <label class="form-label small fw-medium">Inbound Webhook URL</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="fas fa-link"></i></span>
                                <input type="url" class="form-control" id="apiInboundUrl" placeholder="https://your-domain.com/webhook/inbound">
                            </div>
                            <div class="form-text small">
                                <i class="fas fa-lock me-1 text-success"></i>HTTPS URLs only. If empty, inbound messages will not be forwarded.
                            </div>
                            <div id="inboundUrlError" class="text-danger small mt-1" style="display: none;">
                                <i class="fas fa-exclamation-circle me-1"></i>URL must start with https://
                            </div>
                        </div>
                    </div>
                    
                    <div id="apiShortcodeKeywordNotice" class="alert alert-warning small mb-3" style="display: none;">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Shortcode Keyword:</strong> This number can only be used for opt-out keywords or API inbound triggers. It cannot be used as SenderID or Inbox number.
                    </div>

                    <div class="api-restrictions">
                        <label class="form-label fw-bold small text-muted">API Mode Restrictions</label>
                        <ul class="small text-muted mb-0 ps-3">
                            <li><i class="fas fa-times text-danger me-1"></i>Cannot be used as Portal SenderID</li>
                            <li><i class="fas fa-times text-danger me-1"></i>Cannot receive messages in Inbox</li>
                            <li><i class="fas fa-times text-danger me-1"></i>Cannot be used in Campaign Builder</li>
                            <li><i class="fas fa-times text-danger me-1"></i>Cannot handle opt-out via Portal</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="p-4 border-top">
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-primary btn-sm flex-fill" id="btnEditNumber">
                    <i class="fas fa-edit me-1"></i> Edit Configuration
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="offcanvas">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Confirm Action Modal --}}
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmModalLabel">Confirm Action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="confirmModalWarning" class="alert mb-3" style="background-color: rgba(255, 193, 7, 0.15); border: 1px solid rgba(255, 193, 7, 0.3); color: #856404; display: none;">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <span id="confirmModalWarningText"></span>
                </div>
                <p id="confirmModalMessage">Are you sure you want to proceed?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmModalBtn">Confirm</button>
            </div>
        </div>
    </div>
</div>

{{-- Single Number Assign Sub-Accounts Modal --}}
<div class="modal fade" id="assignSubAccountModal" tabindex="-1" aria-labelledby="assignSubAccountModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="assignSubAccountModalLabel"><i class="fas fa-building me-2"></i>Assign Sub-Accounts</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info small mb-3">
                    <i class="fas fa-info-circle me-1"></i>
                    Assigning sub-accounts for number: <strong><span id="assignSubAccountNumber"></span></strong>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Select Sub-Accounts</label>
                    <p class="text-muted small mb-2">Controls visibility, defaults, and reporting scope for this number.</p>
                    <div class="border rounded p-2" style="max-height: 200px; overflow-y: auto;">
                        <div class="form-check mb-2">
                            <input class="form-check-input assign-subacc-check" type="checkbox" value="Main Account" id="assignSubAccMainSingle">
                            <label class="form-check-label" for="assignSubAccMainSingle">Main Account</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input assign-subacc-check" type="checkbox" value="Marketing" id="assignSubAccMarketingSingle">
                            <label class="form-check-label" for="assignSubAccMarketingSingle">Marketing</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input assign-subacc-check" type="checkbox" value="Support" id="assignSubAccSupportSingle">
                            <label class="form-check-label" for="assignSubAccSupportSingle">Support</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input assign-subacc-check" type="checkbox" value="Sales" id="assignSubAccSalesSingle">
                            <label class="form-check-label" for="assignSubAccSalesSingle">Sales</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btnConfirmAssignSubAccounts">
                    <i class="fas fa-check me-1"></i> Apply Changes
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Bulk Assign Sub-Accounts Modal --}}
<div class="modal fade" id="assignSubAccountsModal" tabindex="-1" aria-labelledby="assignSubAccountsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="assignSubAccountsModalLabel">Assign Sub-Accounts</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info small mb-3">
                    <i class="fas fa-info-circle me-1"></i>
                    Assigning sub-accounts to <strong><span id="assignNumbersCount">0</span></strong> selected number(s).
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Select Sub-Accounts</label>
                    <div class="border rounded p-2" style="max-height: 200px; overflow-y: auto;">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="assignSubAccAll">
                            <label class="form-check-label fw-bold" for="assignSubAccAll">Select All</label>
                        </div>
                        <hr class="my-2">
                        <div class="form-check"><input class="form-check-input assign-subacc-check" type="checkbox" value="Main Account" id="assignSubAccMain"><label class="form-check-label" for="assignSubAccMain">Main Account</label></div>
                        <div class="form-check"><input class="form-check-input assign-subacc-check" type="checkbox" value="Marketing" id="assignSubAccMarketing"><label class="form-check-label" for="assignSubAccMarketing">Marketing</label></div>
                        <div class="form-check"><input class="form-check-input assign-subacc-check" type="checkbox" value="Support" id="assignSubAccSupport"><label class="form-check-label" for="assignSubAccSupport">Support</label></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btnConfirmAssignSubAccounts">Assign</button>
            </div>
        </div>
    </div>
</div>

{{-- Mode Change Confirmation Modal --}}
<div class="modal fade" id="modeChangeModal" tabindex="-1" aria-labelledby="modeChangeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modeChangeModalLabel">
                    <i class="fas fa-exchange-alt me-2 text-warning"></i>Change Operating Mode
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning mb-4">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Important:</strong> Changing the operating mode will immediately affect how this number can be used across all modules.
                </div>
                
                <div class="row mb-4">
                    <div class="col-5 text-center">
                        <div class="p-3 rounded border" id="modeChangeFrom">
                            <i class="fas fa-desktop fa-2x text-muted mb-2 d-block" id="modeFromIcon"></i>
                            <span class="fw-bold" id="modeFromLabel">Portal Mode</span>
                        </div>
                    </div>
                    <div class="col-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-arrow-right fa-2x text-warning"></i>
                    </div>
                    <div class="col-5 text-center">
                        <div class="p-3 rounded border border-warning" id="modeChangeTo">
                            <i class="fas fa-code fa-2x text-warning mb-2 d-block" id="modeToIcon"></i>
                            <span class="fw-bold" id="modeToLabel">API Mode</span>
                        </div>
                    </div>
                </div>
                
                <div class="mb-4">
                    <h6 class="fw-bold mb-3"><i class="fas fa-times-circle text-danger me-2"></i>Features That Will Be Disabled</h6>
                    <div id="disabledFeaturesList" class="ps-3">
                        <div class="text-danger small mb-1"><i class="fas fa-times me-2"></i>Campaign Composer access</div>
                        <div class="text-danger small mb-1"><i class="fas fa-times me-2"></i>Inbox visibility</div>
                        <div class="text-danger small mb-1"><i class="fas fa-times me-2"></i>Opt-out management</div>
                    </div>
                </div>
                
                <div class="mb-4">
                    <h6 class="fw-bold mb-3"><i class="fas fa-check-circle text-success me-2"></i>Features That Will Be Enabled</h6>
                    <div id="enabledFeaturesList" class="ps-3">
                        <div class="text-success small mb-1"><i class="fas fa-check me-2"></i>REST API access</div>
                    </div>
                </div>
                
                <div class="alert alert-info mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    This change will be logged for audit purposes and will take effect immediately across all modules.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="btnConfirmModeChange">
                    <i class="fas fa-exchange-alt me-1"></i> Confirm Mode Change
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // TODO: Replace with backend API data
    var numbersData = [
        {
            id: 1,
            number: '+447700900123',
            country: 'UK',
            countryName: 'United Kingdom',
            type: 'vmn',
            status: 'active',
            capabilities: ['api', 'portal', 'inbox', 'optout'],
            mode: 'portal',
            subAccounts: ['Main Account'],
            monthlyCost: 5.00,
            lastUsed: '2025-01-13 14:32:45',
            purchaseDate: '2024-06-15',
            renewalDate: '2025-02-15',
            createdAt: '2024-06-15 10:00:00',
            createdBy: 'admin@quicksms.com',
            modifiedAt: '2025-01-13 09:45:12',
            modifiedBy: 'john.smith@company.com',
            auditHistory: [
                { timestamp: '2025-01-13 09:45:12', user: 'john.smith@company.com', action: 'Sub-account changed', details: 'Added to Marketing sub-account' },
                { timestamp: '2025-01-10 14:22:08', user: 'john.smith@company.com', action: 'Mode changed', details: 'Changed from API to Portal' },
                { timestamp: '2024-06-15 10:00:00', user: 'admin@quicksms.com', action: 'Number purchased', details: 'Initial setup' }
            ],
            portalConfig: {
                allowSenderID: true,
                enableInboxReplies: true,
                enableOptout: true,
                defaults: {
                    'Main Account': { defaultSender: true, defaultInbox: false, defaultOptout: false }
                }
            }
        },
        {
            id: 2,
            number: '+447700900456',
            country: 'UK',
            countryName: 'United Kingdom',
            type: 'vmn',
            status: 'active',
            capabilities: ['api', 'inbox'],
            mode: 'api',
            subAccounts: ['Main Account'],
            monthlyCost: 5.00,
            lastUsed: '2025-01-12 09:15:22',
            purchaseDate: '2024-08-20',
            renewalDate: '2025-02-20',
            createdAt: '2024-08-20 11:30:00',
            createdBy: 'admin@quicksms.com',
            modifiedAt: '2025-01-12 09:15:22',
            modifiedBy: 'api.integration@company.com',
            auditHistory: [
                { timestamp: '2025-01-12 09:15:22', user: 'api.integration@company.com', action: 'Inbound URL updated', details: 'Webhook URL configured' },
                { timestamp: '2024-08-20 11:30:00', user: 'admin@quicksms.com', action: 'Number purchased', details: 'Initial setup as API mode' }
            ],
            apiConfig: {
                inboundForwarding: true,
                inboundUrl: 'https://api.example.com/webhooks/sms/inbound'
            }
        },
        {
            id: 3,
            number: '88600',
            country: 'UK',
            countryName: 'United Kingdom',
            type: 'dedicated_shortcode',
            status: 'active',
            capabilities: ['api', 'portal', 'inbox', 'optout'],
            mode: 'portal',
            subAccounts: ['Main Account', 'Marketing', 'Support'],
            monthlyCost: 500.00,
            lastUsed: '2025-01-13 11:45:18',
            purchaseDate: '2024-01-10',
            renewalDate: '2025-02-10',
            createdAt: '2024-01-10 09:00:00',
            createdBy: 'admin@quicksms.com',
            modifiedAt: '2025-01-13 11:45:18',
            modifiedBy: 'sarah.jones@company.com',
            auditHistory: [
                { timestamp: '2025-01-13 11:45:18', user: 'sarah.jones@company.com', action: 'Sub-account changed', details: 'Added to Support sub-account' },
                { timestamp: '2025-01-05 16:10:00', user: 'john.smith@company.com', action: 'Status changed', details: 'Reactivated from suspended' },
                { timestamp: '2025-01-02 11:00:00', user: 'admin@quicksms.com', action: 'Status changed', details: 'Suspended for maintenance' },
                { timestamp: '2024-01-10 09:00:00', user: 'admin@quicksms.com', action: 'Number purchased', details: 'Dedicated shortcode acquired' }
            ],
            portalConfig: {
                allowSenderID: true,
                enableInboxReplies: true,
                enableOptout: true,
                defaults: {
                    'Main Account': { defaultSender: false, defaultInbox: true, defaultOptout: false },
                    'Marketing': { defaultSender: true, defaultInbox: false, defaultOptout: true },
                    'Support': { defaultSender: false, defaultInbox: false, defaultOptout: false }
                }
            }
        },
        {
            id: 4,
            number: 'OFFER on 88000',
            country: 'UK',
            countryName: 'United Kingdom',
            type: 'shortcode_keyword',
            status: 'active',
            capabilities: ['portal', 'optout'],
            mode: 'portal',
            subAccounts: ['Marketing'],
            monthlyCost: 25.00,
            lastUsed: '2025-01-10 16:30:00',
            purchaseDate: '2024-09-05',
            renewalDate: '2025-02-05'
        },
        {
            id: 5,
            number: '+14155551234',
            country: 'US',
            countryName: 'United States',
            type: 'vmn',
            status: 'suspended',
            capabilities: ['api'],
            mode: 'api',
            subAccounts: ['Main Account'],
            monthlyCost: 8.00,
            lastUsed: '2024-12-15 08:00:00',
            purchaseDate: '2024-03-22',
            renewalDate: '2025-02-22',
            apiConfig: {
                inboundForwarding: false,
                inboundUrl: ''
            }
        },
        {
            id: 6,
            number: '+447700900789',
            country: 'UK',
            countryName: 'United Kingdom',
            type: 'vmn',
            status: 'pending',
            capabilities: ['portal', 'inbox'],
            mode: 'portal',
            subAccounts: ['Support'],
            monthlyCost: 5.00,
            lastUsed: null,
            purchaseDate: '2025-01-10',
            renewalDate: '2025-02-10'
        },
        {
            id: 7,
            number: 'INFO on 88000',
            country: 'UK',
            countryName: 'United Kingdom',
            type: 'shortcode_keyword',
            status: 'active',
            capabilities: ['api', 'portal', 'inbox'],
            mode: 'portal',
            subAccounts: ['Main Account'],
            monthlyCost: 25.00,
            lastUsed: '2025-01-08 10:22:14',
            purchaseDate: '2024-11-18',
            renewalDate: '2025-02-18'
        }
    ];

    var currentSort = { column: 'number', direction: 'asc' };
    
    var appliedFilters = {
        search: '',
        countries: [],
        types: [],
        statuses: [],
        modes: [],
        capabilities: [],
        subAccounts: []
    };

    var selectedNumbers = [];

    // Add/remove dropdown-active class on parent td for z-index fix
    // Also add has-dropdown-open class to container for overflow visibility (browser compatibility)
    $(document).on('shown.bs.dropdown', '.numbers-table .dropdown', function() {
        $(this).closest('td').addClass('dropdown-active');
        $('#numbersTableContainer').addClass('has-dropdown-open');
    });
    $(document).on('hidden.bs.dropdown', '.numbers-table .dropdown', function() {
        $(this).closest('td').removeClass('dropdown-active');
        $('#numbersTableContainer').removeClass('has-dropdown-open');
    });

    function getTypeLabel(type) {
        switch(type) {
            case 'vmn': return 'VMN';
            case 'dedicated_shortcode': return 'Dedicated Shortcode';
            case 'shortcode_keyword': return 'Shortcode Keyword';
            default: return type;
        }
    }

    function getStatusBadgeClass(status) {
        switch(status) {
            case 'active': return 'badge-active';
            case 'suspended': return 'badge-suspended';
            case 'pending': return 'badge-pending';
            default: return 'badge-pastel-secondary';
        }
    }

    function getStatusLabel(status) {
        switch(status) {
            case 'active': return 'Active';
            case 'suspended': return 'Suspended';
            case 'pending': return 'Pending';
            default: return status;
        }
    }

    function formatCapabilities(capabilities) {
        var html = '';
        if (capabilities.includes('api')) {
            html += '<span class="capability-pill capability-api">API</span>';
        }
        if (capabilities.includes('portal')) {
            html += '<span class="capability-pill capability-portal">Portal SenderID</span>';
        }
        if (capabilities.includes('inbox')) {
            html += '<span class="capability-pill capability-inbox">Inbox</span>';
        }
        if (capabilities.includes('optout')) {
            html += '<span class="capability-pill capability-optout">Opt-out</span>';
        }
        return html || '<span class="text-muted small">None</span>';
    }

    function formatSubAccounts(subAccounts) {
        if (!subAccounts || subAccounts.length === 0) return '<span class="text-muted">-</span>';
        if (subAccounts.length === 1) return subAccounts[0];
        return 'Multiple (' + subAccounts.length + ')';
    }

    function formatCurrency(amount) {
        return '£' + amount.toFixed(2);
    }

    function formatDateTime(dateStr) {
        if (!dateStr) return '<span class="text-muted">Never</span>';
        var date = new Date(dateStr);
        var year = date.getFullYear();
        var month = String(date.getMonth() + 1).padStart(2, '0');
        var day = String(date.getDate()).padStart(2, '0');
        var hours = String(date.getHours()).padStart(2, '0');
        var minutes = String(date.getMinutes()).padStart(2, '0');
        return day + '-' + month + '-' + year + ' ' + hours + ':' + minutes;
    }

    function formatDate(dateStr) {
        if (!dateStr) return '-';
        var date = new Date(dateStr);
        var year = date.getFullYear();
        var month = String(date.getMonth() + 1).padStart(2, '0');
        var day = String(date.getDate()).padStart(2, '0');
        return day + '-' + month + '-' + year;
    }

    function renderTable() {
        var filtered = numbersData.filter(function(num) {
            if (appliedFilters.search) {
                var search = appliedFilters.search.toLowerCase();
                if (!num.number.toLowerCase().includes(search)) return false;
            }
            
            if (appliedFilters.countries.length > 0 && !appliedFilters.countries.includes(num.country)) return false;
            if (appliedFilters.types.length > 0 && !appliedFilters.types.includes(num.type)) return false;
            if (appliedFilters.statuses.length > 0 && !appliedFilters.statuses.includes(num.status)) return false;
            if (appliedFilters.modes.length > 0 && !appliedFilters.modes.includes(num.mode)) return false;
            
            if (appliedFilters.capabilities.length > 0) {
                var hasCapMatch = appliedFilters.capabilities.some(function(cap) {
                    return num.capabilities.includes(cap);
                });
                if (!hasCapMatch) return false;
            }
            
            if (appliedFilters.subAccounts.length > 0) {
                var hasMatch = num.subAccounts.some(function(sa) {
                    return appliedFilters.subAccounts.includes(sa);
                });
                if (!hasMatch) return false;
            }
            
            return true;
        });
        
        filtered.sort(function(a, b) {
            var aVal = a[currentSort.column];
            var bVal = b[currentSort.column];
            
            if (currentSort.column === 'subAccounts') {
                aVal = a.subAccounts.length;
                bVal = b.subAccounts.length;
            }
            
            if (aVal === null || aVal === undefined) aVal = '';
            if (bVal === null || bVal === undefined) bVal = '';
            
            if (typeof aVal === 'string') aVal = aVal.toLowerCase();
            if (typeof bVal === 'string') bVal = bVal.toLowerCase();
            
            if (aVal < bVal) return currentSort.direction === 'asc' ? -1 : 1;
            if (aVal > bVal) return currentSort.direction === 'asc' ? 1 : -1;
            return 0;
        });
        
        if (filtered.length === 0) {
            $('#numbersTableContainer').hide();
            $('#emptyState').show();
            $('#showingCount').text(0);
            $('#totalCount').text(numbersData.length);
            return;
        }
        
        $('#numbersTableContainer').show();
        $('#emptyState').hide();
        
        var html = '';
        filtered.forEach(function(num) {
            var rowClass = num.status === 'suspended' ? 'suspended-row' : '';
            var isChecked = selectedNumbers.includes(num.id) ? 'checked' : '';
            html += '<tr class="' + rowClass + '" data-id="' + num.id + '" data-mode="' + num.mode + '" data-status="' + num.status + '">';
            
            html += '<td class="checkbox-col"><input type="checkbox" class="form-check-input row-checkbox" data-id="' + num.id + '" ' + isChecked + '></td>';
            html += '<td><span class="number-value">' + num.number + '</span></td>';
            html += '<td>' + num.countryName + '</td>';
            html += '<td>' + getTypeLabel(num.type) + '</td>';
            html += '<td><span class="badge rounded-pill ' + getStatusBadgeClass(num.status) + '">' + getStatusLabel(num.status) + '</span></td>';
            html += '<td>' + formatCapabilities(num.capabilities) + '</td>';
            html += '<td>' + (num.mode === 'portal' ? 'Portal' : 'API') + '</td>';
            html += '<td>' + formatSubAccounts(num.subAccounts) + '</td>';
            html += '<td><span class="cost-value">' + formatCurrency(num.monthlyCost) + '</span></td>';
            html += '<td>' + formatDateTime(num.lastUsed) + '</td>';
            
            html += '<td>';
            html += '<div class="dropdown">';
            html += '<button class="action-menu-btn" type="button" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">';
            html += '<i class="fas fa-ellipsis-v"></i>';
            html += '</button>';
            html += '<ul class="dropdown-menu dropdown-menu-end">';
            
            // View Details - always available
            html += '<li><a class="dropdown-item" href="#" onclick="viewNumber(' + num.id + '); return false;"><i class="fas fa-eye me-2 text-muted"></i>View Details</a></li>';
            
            // Edit Configuration - always available
            html += '<li><a class="dropdown-item" href="#" onclick="editNumber(' + num.id + '); return false;"><i class="fas fa-cog me-2 text-muted"></i>Edit Configuration</a></li>';
            
            // Assign Sub-Accounts - Portal mode only
            if (num.mode === 'portal') {
                html += '<li><a class="dropdown-item" href="#" onclick="assignSubAccountsToNumber(' + num.id + '); return false;"><i class="fas fa-building me-2 text-muted"></i>Assign Sub-Accounts</a></li>';
            }
            
            // Suspend - only for active numbers
            if (num.status === 'active') {
                html += '<li><hr class="dropdown-divider"></li>';
                html += '<li><a class="dropdown-item text-warning" href="#" onclick="suspendNumber(' + num.id + '); return false;"><i class="fas fa-pause me-2"></i>Suspend</a></li>';
            }
            
            // Reactivate - only for suspended numbers
            if (num.status === 'suspended') {
                html += '<li><hr class="dropdown-divider"></li>';
                html += '<li><a class="dropdown-item text-success" href="#" onclick="reactivateNumber(' + num.id + '); return false;"><i class="fas fa-play me-2"></i>Reactivate</a></li>';
            }
            
            // NOTE: No delete/release action - numbers are never deleted
            
            html += '</ul>';
            html += '</div>';
            html += '</td>';
            
            html += '</tr>';
        });
        
        $('#numbersTableBody').html(html);
        $('#showingCount').text(filtered.length);
        $('#totalCount').text(numbersData.length);
    }

    // Row click to navigate to configuration page
    $(document).on('click', '.numbers-table tbody tr', function(e) {
        if ($(e.target).closest('.dropdown').length) return;
        if ($(e.target).hasClass('row-checkbox') || $(e.target).hasClass('form-check-input')) return;
        var id = $(this).data('id');
        navigateToConfigurePage([id]);
    });

    // Checkbox selection handling
    $(document).on('change', '.row-checkbox', function() {
        var id = $(this).data('id');
        if ($(this).is(':checked')) {
            if (!selectedNumbers.includes(id)) {
                selectedNumbers.push(id);
            }
        } else {
            selectedNumbers = selectedNumbers.filter(function(n) { return n !== id; });
        }
        updateBulkActionBar();
        updateSelectAllCheckbox();
    });

    $('#selectAllCheckbox').on('change', function() {
        var isChecked = $(this).is(':checked');
        $('.row-checkbox').each(function() {
            $(this).prop('checked', isChecked);
            var id = $(this).data('id');
            if (isChecked) {
                if (!selectedNumbers.includes(id)) {
                    selectedNumbers.push(id);
                }
            } else {
                selectedNumbers = selectedNumbers.filter(function(n) { return n !== id; });
            }
        });
        updateBulkActionBar();
    });

    function updateSelectAllCheckbox() {
        var total = $('.row-checkbox').length;
        var checked = $('.row-checkbox:checked').length;
        $('#selectAllCheckbox').prop('checked', total > 0 && total === checked);
        $('#selectAllCheckbox').prop('indeterminate', checked > 0 && checked < total);
    }

    function updateBulkActionBar() {
        var count = selectedNumbers.length;
        $('#selectedCount').text(count);
        
        if (count > 0) {
            $('#bulkActionBar').addClass('show');
            $('#btnConfigureSelected').prop('disabled', false);
        } else {
            $('#bulkActionBar').removeClass('show');
            $('#btnConfigureSelected').prop('disabled', true);
        }
        
        // Get selected numbers data
        var selectedData = numbersData.filter(function(n) { 
            return selectedNumbers.includes(n.id); 
        });
        
        // Check if any active numbers are selected (can suspend)
        var hasActive = selectedData.some(function(n) { return n.status === 'active'; });
        $('#btnBulkSuspend').prop('disabled', !hasActive);
        
        // Check if any suspended numbers are selected (can reactivate)
        var hasSuspended = selectedData.some(function(n) { return n.status === 'suspended'; });
        $('#btnBulkReactivate').prop('disabled', !hasSuspended);
        
        // Assign Sub-Accounts only for Portal mode numbers
        var allPortal = selectedData.every(function(n) { return n.mode === 'portal'; });
        $('#btnBulkAssignSubAccounts').prop('disabled', !allPortal || count === 0);
    }
    
    // Navigate to dedicated configuration page
    function navigateToConfigurePage(ids) {
        var idsParam = Array.isArray(ids) ? ids.join(',') : ids;
        window.location.href = '{{ route("management.numbers.configure") }}?ids=' + idsParam;
    }
    
    // Configure button click handler
    $('#btnConfigureSelected').on('click', function() {
        if (selectedNumbers.length === 0) {
            toastr.warning('Please select at least one number to configure');
            return;
        }
        navigateToConfigurePage(selectedNumbers);
    });

    $('#btnClearSelection').on('click', function() {
        selectedNumbers = [];
        $('.row-checkbox').prop('checked', false);
        $('#selectAllCheckbox').prop('checked', false).prop('indeterminate', false);
        updateBulkActionBar();
    });

    // Bulk Suspend
    $('#btnBulkSuspend').on('click', function() {
        var activeNumbers = numbersData.filter(function(n) {
            return selectedNumbers.includes(n.id) && n.status === 'active';
        });
        
        if (activeNumbers.length === 0) {
            toastr.warning('No active numbers selected to suspend');
            return;
        }
        
        $('#confirmModalLabel').text('Bulk Suspend Numbers');
        $('#confirmModalMessage').text('Are you sure you want to suspend ' + activeNumbers.length + ' active number(s)?');
        $('#confirmModalWarning').show();
        $('#confirmModalWarningText').text('These numbers will be hidden from sender pickers across all modules.');
        $('#confirmModalBtn').removeClass('btn-primary btn-danger').addClass('btn-warning').text('Suspend');
        
        $('#confirmModalBtn').off('click').on('click', function() {
            // TODO: Backend API call
            activeNumbers.forEach(function(num) {
                num.status = 'suspended';
            });
            selectedNumbers = [];
            $('.row-checkbox').prop('checked', false);
            $('#selectAllCheckbox').prop('checked', false);
            updateBulkActionBar();
            renderTable();
            bootstrap.Modal.getInstance(document.getElementById('confirmModal')).hide();
            toastr.success(activeNumbers.length + ' number(s) suspended successfully');
        });
        
        new bootstrap.Modal(document.getElementById('confirmModal')).show();
    });

    // Bulk Reactivate
    $('#btnBulkReactivate').on('click', function() {
        var suspendedNumbers = numbersData.filter(function(n) {
            return selectedNumbers.includes(n.id) && n.status === 'suspended';
        });
        
        if (suspendedNumbers.length === 0) {
            toastr.warning('No suspended numbers selected to reactivate');
            return;
        }
        
        $('#confirmModalLabel').text('Bulk Reactivate Numbers');
        $('#confirmModalMessage').text('Are you sure you want to reactivate ' + suspendedNumbers.length + ' suspended number(s)?');
        $('#confirmModalWarning').hide();
        $('#confirmModalBtn').removeClass('btn-warning btn-danger').addClass('btn-primary').text('Reactivate');
        
        $('#confirmModalBtn').off('click').on('click', function() {
            // TODO: Backend API call
            suspendedNumbers.forEach(function(num) {
                num.status = 'active';
            });
            selectedNumbers = [];
            $('.row-checkbox').prop('checked', false);
            $('#selectAllCheckbox').prop('checked', false);
            updateBulkActionBar();
            renderTable();
            bootstrap.Modal.getInstance(document.getElementById('confirmModal')).hide();
            toastr.success(suspendedNumbers.length + ' number(s) reactivated successfully');
        });
        
        new bootstrap.Modal(document.getElementById('confirmModal')).show();
    });

    // Bulk Assign Sub-Accounts
    $('#btnBulkAssignSubAccounts').on('click', function() {
        var portalNumbers = numbersData.filter(function(n) {
            return selectedNumbers.includes(n.id) && n.mode === 'portal';
        });
        
        if (portalNumbers.length === 0) {
            toastr.warning('Assign Sub-Accounts is only available for Portal mode numbers');
            return;
        }
        
        $('#assignNumbersCount').text(portalNumbers.length);
        $('.assign-subacc-check').prop('checked', false);
        $('#assignSubAccAll').prop('checked', false);
        
        new bootstrap.Modal(document.getElementById('assignSubAccountsModal')).show();
    });

    // Select All in Assign Sub-Accounts modal
    $('#assignSubAccAll').on('change', function() {
        $('.assign-subacc-check').prop('checked', $(this).is(':checked'));
    });

    // Confirm Assign Sub-Accounts
    $('#btnConfirmAssignSubAccounts').on('click', function() {
        var selectedSubAccs = [];
        $('.assign-subacc-check:checked').each(function() {
            selectedSubAccs.push($(this).val());
        });
        
        if (selectedSubAccs.length === 0) {
            toastr.warning('Please select at least one sub-account');
            return;
        }
        
        var portalNumbers = numbersData.filter(function(n) {
            return selectedNumbers.includes(n.id) && n.mode === 'portal';
        });
        
        // TODO: Backend API call
        portalNumbers.forEach(function(num) {
            num.subAccounts = selectedSubAccs.slice();
        });
        
        selectedNumbers = [];
        $('.row-checkbox').prop('checked', false);
        $('#selectAllCheckbox').prop('checked', false);
        updateBulkActionBar();
        renderTable();
        bootstrap.Modal.getInstance(document.getElementById('assignSubAccountsModal')).hide();
        toastr.success('Sub-accounts assigned to ' + portalNumbers.length + ' number(s)');
    });

    // Sorting
    $('.numbers-table thead th[data-sort]').on('click', function() {
        var column = $(this).data('sort');
        
        if (currentSort.column === column) {
            currentSort.direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
        } else {
            currentSort.column = column;
            currentSort.direction = 'asc';
        }
        
        $('.numbers-table thead th').removeClass('sorted');
        $(this).addClass('sorted');
        $(this).find('.sort-icon').removeClass('fa-sort fa-sort-up fa-sort-down')
            .addClass(currentSort.direction === 'asc' ? 'fa-sort-up' : 'fa-sort-down');
        
        renderTable();
    });

    // Search
    $('#searchInput').on('input', function() {
        appliedFilters.search = $(this).val();
        renderTable();
    });

    // Filter handling
    $('.multiselect-dropdown .form-check-input').on('change', function() {
        updateFilterLabel($(this).closest('.multiselect-dropdown'));
    });

    $('.select-all-btn').on('click', function(e) {
        e.preventDefault();
        $(this).closest('.dropdown-menu').find('.form-check-input').prop('checked', true);
        updateFilterLabel($(this).closest('.multiselect-dropdown'));
    });

    $('.clear-all-btn').on('click', function(e) {
        e.preventDefault();
        $(this).closest('.dropdown-menu').find('.form-check-input').prop('checked', false);
        updateFilterLabel($(this).closest('.multiselect-dropdown'));
    });

    function updateFilterLabel(dropdown) {
        var checked = dropdown.find('.form-check-input:checked');
        var label = dropdown.find('.dropdown-label');
        var filterType = dropdown.data('filter');
        
        if (checked.length === 0) {
            label.text('All ' + getFilterName(filterType));
        } else if (checked.length === 1) {
            label.text(checked.first().next('label').text());
        } else {
            label.text(checked.length + ' selected');
        }
    }

    function getFilterName(filterType) {
        switch(filterType) {
            case 'countries': return 'Countries';
            case 'types': return 'Types';
            case 'statuses': return 'Statuses';
            case 'modes': return 'Modes';
            case 'capabilities': return 'Capabilities';
            case 'subAccounts': return 'Sub-Accounts';
            default: return '';
        }
    }

    function getCapabilityLabel(cap) {
        switch(cap) {
            case 'api': return 'API';
            case 'portal': return 'Portal SenderID';
            case 'inbox': return 'Inbox';
            case 'optout': return 'Opt-out';
            default: return cap;
        }
    }

    $('#btnApplyFilters').on('click', function() {
        appliedFilters.countries = [];
        appliedFilters.types = [];
        appliedFilters.statuses = [];
        appliedFilters.modes = [];
        appliedFilters.capabilities = [];
        appliedFilters.subAccounts = [];
        
        $('[data-filter="countries"] .form-check-input:checked').each(function() {
            appliedFilters.countries.push($(this).val());
        });
        $('[data-filter="types"] .form-check-input:checked').each(function() {
            appliedFilters.types.push($(this).val());
        });
        $('[data-filter="statuses"] .form-check-input:checked').each(function() {
            appliedFilters.statuses.push($(this).val());
        });
        $('[data-filter="modes"] .form-check-input:checked').each(function() {
            appliedFilters.modes.push($(this).val());
        });
        $('[data-filter="capabilities"] .form-check-input:checked').each(function() {
            appliedFilters.capabilities.push($(this).val());
        });
        $('[data-filter="subAccounts"] .form-check-input:checked').each(function() {
            appliedFilters.subAccounts.push($(this).val());
        });
        
        updateActiveFiltersDisplay();
        renderTable();
        $('#filtersPanel').collapse('hide');
    });

    $('#btnResetFilters').on('click', function() {
        $('.multiselect-dropdown .form-check-input').prop('checked', false);
        $('.multiselect-dropdown').each(function() {
            updateFilterLabel($(this));
        });
        appliedFilters.countries = [];
        appliedFilters.types = [];
        appliedFilters.statuses = [];
        appliedFilters.modes = [];
        appliedFilters.capabilities = [];
        appliedFilters.subAccounts = [];
        updateActiveFiltersDisplay();
        renderTable();
    });

    $('#btnClearAllFilters').on('click', function() {
        $('#btnResetFilters').click();
    });

    function updateActiveFiltersDisplay() {
        var hasFilters = appliedFilters.countries.length > 0 || 
                        appliedFilters.types.length > 0 || 
                        appliedFilters.statuses.length > 0 || 
                        appliedFilters.modes.length > 0 ||
                        appliedFilters.capabilities.length > 0 ||
                        appliedFilters.subAccounts.length > 0;
        
        if (hasFilters) {
            $('#activeFiltersContainer').show();
            var html = '';
            
            appliedFilters.countries.forEach(function(c) {
                html += '<span class="filter-chip"><span class="chip-label">Country:</span>' + c + '<span class="remove-chip" data-filter="countries" data-value="' + c + '"><i class="fas fa-times"></i></span></span>';
            });
            appliedFilters.types.forEach(function(t) {
                html += '<span class="filter-chip"><span class="chip-label">Type:</span>' + getTypeLabel(t) + '<span class="remove-chip" data-filter="types" data-value="' + t + '"><i class="fas fa-times"></i></span></span>';
            });
            appliedFilters.statuses.forEach(function(s) {
                html += '<span class="filter-chip"><span class="chip-label">Status:</span>' + getStatusLabel(s) + '<span class="remove-chip" data-filter="statuses" data-value="' + s + '"><i class="fas fa-times"></i></span></span>';
            });
            appliedFilters.modes.forEach(function(m) {
                html += '<span class="filter-chip"><span class="chip-label">Mode:</span>' + (m === 'portal' ? 'Portal' : 'API') + '<span class="remove-chip" data-filter="modes" data-value="' + m + '"><i class="fas fa-times"></i></span></span>';
            });
            appliedFilters.capabilities.forEach(function(cap) {
                html += '<span class="filter-chip"><span class="chip-label">Capability:</span>' + getCapabilityLabel(cap) + '<span class="remove-chip" data-filter="capabilities" data-value="' + cap + '"><i class="fas fa-times"></i></span></span>';
            });
            appliedFilters.subAccounts.forEach(function(sa) {
                html += '<span class="filter-chip"><span class="chip-label">Sub-Account:</span>' + sa + '<span class="remove-chip" data-filter="subAccounts" data-value="' + sa + '"><i class="fas fa-times"></i></span></span>';
            });
            
            $('#activeFiltersChips').html(html);
        } else {
            $('#activeFiltersContainer').hide();
        }
    }

    $(document).on('click', '.remove-chip', function() {
        var filterType = $(this).data('filter');
        var value = $(this).data('value');
        
        var index = appliedFilters[filterType].indexOf(value);
        if (index > -1) {
            appliedFilters[filterType].splice(index, 1);
        }
        
        $('[data-filter="' + filterType + '"] .form-check-input[value="' + value + '"]').prop('checked', false);
        updateFilterLabel($('[data-filter="' + filterType + '"]'));
        updateActiveFiltersDisplay();
        renderTable();
    });

    var currentEditingNumberId = null;

    // View number function
    window.viewNumber = function(id) {
        var num = numbersData.find(function(n) { return n.id === id; });
        if (!num) return;
        
        currentEditingNumberId = id;
        
        $('#drawerNumber').text(num.number);
        $('#drawerNumberDetail').text(num.number);
        $('#drawerCountry').text(num.countryName);
        $('#drawerType').text(getTypeLabel(num.type));
        $('#drawerMode').text(num.mode === 'portal' ? 'Portal' : 'API');
        $('#drawerCost').text(formatCurrency(num.monthlyCost));
        
        $('#drawerTypeBadge').text(getTypeLabel(num.type));
        $('#drawerStatusBadge').removeClass('badge-active badge-suspended badge-pending')
            .addClass(getStatusBadgeClass(num.status)).text(getStatusLabel(num.status));
        $('#drawerModeBadge').text(num.mode === 'portal' ? 'Portal' : 'API');
        
        $('#drawerCapabilities').html(formatCapabilities(num.capabilities));
        
        var saHtml = num.subAccounts.map(function(sa) {
            return '<span class="badge badge-pastel-secondary me-1 mb-1">' + sa + '</span>';
        }).join('');
        $('#drawerSubAccounts').html(saHtml || '<span class="text-muted">None assigned</span>');
        
        $('#drawerLastUsed').text(num.lastUsed ? formatDateTime(num.lastUsed) : 'Never');
        $('#drawerPurchaseDate').text(formatDate(num.purchaseDate));
        $('#drawerRenewalDate').text(formatDate(num.renewalDate));
        
        // Populate audit trail
        $('#drawerCreatedAt').text(num.createdAt ? formatDateTime(num.createdAt) : '-');
        $('#drawerCreatedBy').text(num.createdBy || '-');
        $('#drawerModifiedAt').text(num.modifiedAt ? formatDateTime(num.modifiedAt) : '-');
        $('#drawerModifiedBy').text(num.modifiedBy || '-');
        renderAuditHistory(num);
        
        // Update mode selection buttons
        updateModeSelectionUI(num.mode);
        
        // Populate configuration based on mode
        if (num.mode === 'portal') {
            populatePortalConfig(num);
        } else {
            populateApiConfig(num);
        }
        
        var offcanvas = new bootstrap.Offcanvas(document.getElementById('numberConfigDrawer'));
        offcanvas.show();
    };

    function updateModeSelectionUI(currentMode) {
        $('.mode-btn').removeClass('active');
        if (currentMode === 'portal') {
            $('#btnModePortal').addClass('active');
            $('#portalModeFeatures').show();
            $('#apiModeFeatures').hide();
            $('#portalConfigCard').show();
            $('#apiConfigCard').hide();
        } else {
            $('#btnModeAPI').addClass('active');
            $('#apiModeFeatures').show();
            $('#portalModeFeatures').hide();
            $('#portalConfigCard').hide();
            $('#apiConfigCard').show();
        }
    }

    function renderAuditHistory(num) {
        var container = $('#drawerAuditHistory');
        
        if (!num.auditHistory || num.auditHistory.length === 0) {
            container.html('<div class="text-muted small">No audit history available</div>');
            return;
        }
        
        // Show last 5 entries (most recent first)
        var recentHistory = num.auditHistory.slice(0, 5);
        
        var html = '';
        recentHistory.forEach(function(entry) {
            html += '<div class="audit-history-item">';
            html += '<div class="audit-action">' + entry.action + '</div>';
            html += '<div class="audit-details">' + entry.details + '</div>';
            html += '<div class="audit-meta">';
            html += '<i class="fas fa-user me-1"></i>' + entry.user;
            html += ' &bull; <i class="fas fa-clock ms-1 me-1"></i>' + formatDateTime(entry.timestamp);
            html += '</div>';
            html += '</div>';
        });
        
        container.html(html);
    }

    // Add audit entry helper - called when changes are made
    // TODO: Backend integration - audit entries should be created server-side with authenticated user info
    function addAuditEntry(num, action, details) {
        if (!num.auditHistory) {
            num.auditHistory = [];
        }
        
        var entry = {
            timestamp: new Date().toISOString().replace('T', ' ').substring(0, 19),
            user: 'current.user@company.com', // TODO: Get from authenticated session
            action: action,
            details: details
        };
        
        // Add to beginning of array (most recent first)
        num.auditHistory.unshift(entry);
        
        // Update modification timestamp
        num.modifiedAt = entry.timestamp;
        num.modifiedBy = entry.user;
        
        /*
         * TODO: Backend propagation - changes must reflect instantly across all modules:
         * - Send Message: Update available SenderID pickers
         * - Inbox: Update available inbox numbers and routing
         * - Email-to-SMS: Update number assignments and defaults
         * - API usage: Update webhook configurations and routing
         * - Reporting attribution: Update sub-account associations for analytics
         * 
         * Implementation approach:
         * 1. All changes should trigger a backend API call
         * 2. Backend should update the database and broadcast changes via websockets/events
         * 3. Other modules should listen for number configuration changes
         * 4. Consider using Laravel events: NumberStatusChanged, NumberModeChanged, NumberConfigUpdated
         */
    }

    function populatePortalConfig(num) {
        if (num.mode !== 'portal') return;
        
        var isShortcodeKeyword = num.type === 'shortcode_keyword';
        
        // Populate sub-account checkboxes
        $('.portal-subacc-check').prop('checked', false);
        if (num.subAccounts) {
            num.subAccounts.forEach(function(sa) {
                $('.portal-subacc-check[value="' + sa + '"]').prop('checked', true);
            });
        }
        
        // Populate capability toggles from portalConfig
        var config = num.portalConfig || {};
        
        // HARD RULE: Shortcode Keywords cannot be SenderID or Inbox
        if (isShortcodeKeyword) {
            // Show notice for shortcode keywords
            $('#portalShortcodeKeywordNotice').show();
            // Hide SenderID toggle - shortcode keywords cannot be SenderID
            $('#toggleSenderID').closest('.capability-toggle').hide();
            // Hide Inbox toggle - shortcode keywords cannot be Inbox number
            $('#toggleInboxReplies').closest('.capability-toggle').hide();
            // Force these to false
            config.allowSenderID = false;
            config.enableInboxReplies = false;
        } else {
            $('#portalShortcodeKeywordNotice').hide();
            $('#toggleSenderID').closest('.capability-toggle').show();
            $('#toggleInboxReplies').closest('.capability-toggle').show();
            $('#toggleSenderID').prop('checked', config.allowSenderID !== false);
            $('#toggleInboxReplies').prop('checked', config.enableInboxReplies !== false);
        }
        
        // Opt-out is always available (shortcode keywords CAN be used for opt-out)
        $('#toggleOptout').prop('checked', config.enableOptout !== false);
        
        // Render defaults per sub-account (will also enforce shortcode keyword rules)
        renderSubAccountDefaults(num);
    }

    function renderSubAccountDefaults(num) {
        var container = $('#subAccountDefaults');
        container.empty();
        
        if (!num.subAccounts || num.subAccounts.length === 0) {
            container.html('<div class="text-muted small">No sub-accounts assigned. Assign sub-accounts above to configure defaults.</div>');
            return;
        }
        
        var isShortcodeKeyword = num.type === 'shortcode_keyword';
        var config = num.portalConfig || {};
        var defaults = config.defaults || {};
        
        num.subAccounts.forEach(function(sa) {
            var saDefaults = defaults[sa] || {};
            var saKey = sa.replace(/\s+/g, '_');
            
            var html = '<div class="subaccount-defaults-item">';
            html += '<div class="subaccount-name"><i class="fas fa-building me-2"></i>' + sa + '</div>';
            
            // HARD RULE: Shortcode Keywords cannot be SenderID - hide this option entirely
            if (!isShortcodeKeyword) {
                var isSenderDefault = saDefaults.defaultSender === true;
                html += '<div class="default-toggle' + (isSenderDefault ? ' is-default' : '') + '">';
                html += '<label><i class="fas fa-paper-plane me-2 text-primary"></i>Default Sender Number</label>';
                if (isSenderDefault) {
                    html += '<span class="default-badge">DEFAULT</span>';
                } else {
                    html += '<button type="button" class="btn btn-sm btn-outline-primary btn-set-default" data-subaccount="' + sa + '" data-type="sender" style="font-size: 0.7rem; padding: 0.15rem 0.5rem;">Set Default</button>';
                }
                html += '</div>';
            }
            
            // HARD RULE: Shortcode Keywords cannot be Inbox number - hide this option entirely
            if (!isShortcodeKeyword) {
                var isInboxDefault = saDefaults.defaultInbox === true;
                html += '<div class="default-toggle' + (isInboxDefault ? ' is-default' : '') + '">';
                html += '<label><i class="fas fa-inbox me-2 text-success"></i>Default Inbox Number</label>';
                if (isInboxDefault) {
                    html += '<span class="default-badge">DEFAULT</span>';
                } else {
                    html += '<button type="button" class="btn btn-sm btn-outline-primary btn-set-default" data-subaccount="' + sa + '" data-type="inbox" style="font-size: 0.7rem; padding: 0.15rem 0.5rem;">Set Default</button>';
                }
                html += '</div>';
            }
            
            // Default Opt-out Number - always available (shortcode keywords CAN be used for opt-out)
            var isOptoutDefault = saDefaults.defaultOptout === true;
            html += '<div class="default-toggle' + (isOptoutDefault ? ' is-default' : '') + '">';
            html += '<label><i class="fas fa-ban me-2 text-warning"></i>Default Opt-out Number</label>';
            if (isOptoutDefault) {
                html += '<span class="default-badge">DEFAULT</span>';
            } else {
                html += '<button type="button" class="btn btn-sm btn-outline-primary btn-set-default" data-subaccount="' + sa + '" data-type="optout" style="font-size: 0.7rem; padding: 0.15rem 0.5rem;">Set Default</button>';
            }
            html += '</div>';
            
            html += '</div>';
            container.append(html);
        });
    }

    function populateApiConfig(num) {
        if (num.mode !== 'api') return;
        
        var isShortcodeKeyword = num.type === 'shortcode_keyword';
        var config = num.apiConfig || {};
        
        // Set sub-account (single select for API mode)
        var subAccount = num.subAccounts && num.subAccounts.length > 0 ? num.subAccounts[0] : '';
        $('#apiSubAccountSelect').val(subAccount);
        
        // Set inbound forwarding toggle
        // Shortcode Keywords CAN be used for API inbound triggers
        var inboundEnabled = config.inboundForwarding === true;
        $('#toggleInboundForwarding').prop('checked', inboundEnabled);
        
        // Show/hide URL section based on toggle
        if (inboundEnabled) {
            $('#inboundUrlSection').show();
        } else {
            $('#inboundUrlSection').hide();
        }
        
        // Set inbound URL
        $('#apiInboundUrl').val(config.inboundUrl || '');
        $('#inboundUrlError').hide();
        
        // Update API restrictions display for shortcode keywords
        if (isShortcodeKeyword) {
            $('#apiShortcodeKeywordNotice').show();
        } else {
            $('#apiShortcodeKeywordNotice').hide();
        }
    }

    // Handle API sub-account selection (single only)
    $('#apiSubAccountSelect').on('change', function() {
        var num = numbersData.find(function(n) { return n.id === currentEditingNumberId; });
        if (!num) return;
        
        var selectedSubAccount = $(this).val();
        
        // API numbers can only belong to one sub-account
        if (selectedSubAccount) {
            num.subAccounts = [selectedSubAccount];
        } else {
            num.subAccounts = [];
        }
        
        // TODO: Backend API call to update sub-account attribution
        
        // Update drawer display
        var saHtml = num.subAccounts.map(function(sa) {
            return '<span class="badge badge-pastel-secondary me-1 mb-1">' + sa + '</span>';
        }).join('');
        $('#drawerSubAccounts').html(saHtml || '<span class="text-muted">None assigned</span>');
        
        renderTable();
        toastr.success('Sub-account attribution updated');
    });

    // Handle inbound forwarding toggle
    $('#toggleInboundForwarding').on('change', function() {
        var num = numbersData.find(function(n) { return n.id === currentEditingNumberId; });
        if (!num) return;
        
        var isEnabled = $(this).is(':checked');
        
        if (!num.apiConfig) num.apiConfig = {};
        num.apiConfig.inboundForwarding = isEnabled;
        
        if (isEnabled) {
            $('#inboundUrlSection').slideDown(200);
        } else {
            $('#inboundUrlSection').slideUp(200);
            // Clear URL when disabled
            num.apiConfig.inboundUrl = '';
            $('#apiInboundUrl').val('');
        }
        
        // TODO: Backend API call to update inbound forwarding setting
        toastr.success('Inbound forwarding ' + (isEnabled ? 'enabled' : 'disabled'));
    });

    // Handle inbound URL input with HTTPS validation
    $('#apiInboundUrl').on('input blur', function() {
        var num = numbersData.find(function(n) { return n.id === currentEditingNumberId; });
        if (!num) return;
        
        var url = $(this).val().trim();
        
        // Validate HTTPS
        if (url && !url.startsWith('https://')) {
            $('#inboundUrlError').show();
            $(this).addClass('is-invalid');
            return;
        } else {
            $('#inboundUrlError').hide();
            $(this).removeClass('is-invalid');
        }
        
        if (!num.apiConfig) num.apiConfig = {};
        num.apiConfig.inboundUrl = url;
        
        // TODO: Backend API call to update inbound URL
    });

    // Save inbound URL on enter key
    $('#apiInboundUrl').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            var url = $(this).val().trim();
            
            if (url && !url.startsWith('https://')) {
                toastr.error('URL must start with https://');
                return;
            }
            
            $(this).blur();
            toastr.success('Inbound URL saved');
        }
    });

    // Handle sub-account checkbox changes in portal config
    $(document).on('change', '.portal-subacc-check', function() {
        var num = numbersData.find(function(n) { return n.id === currentEditingNumberId; });
        if (!num) return;
        
        var selectedSubAccounts = [];
        $('.portal-subacc-check:checked').each(function() {
            selectedSubAccounts.push($(this).val());
        });
        
        // TODO: Backend API call to update sub-accounts
        num.subAccounts = selectedSubAccounts;
        
        // Re-render defaults section
        renderSubAccountDefaults(num);
        
        // Update drawer display
        var saHtml = num.subAccounts.map(function(sa) {
            return '<span class="badge badge-pastel-secondary me-1 mb-1">' + sa + '</span>';
        }).join('');
        $('#drawerSubAccounts').html(saHtml || '<span class="text-muted">None assigned</span>');
        
        renderTable();
    });

    // Handle capability toggles
    $('#toggleSenderID, #toggleInboxReplies, #toggleOptout').on('change', function() {
        var num = numbersData.find(function(n) { return n.id === currentEditingNumberId; });
        if (!num) return;
        
        if (!num.portalConfig) num.portalConfig = {};
        
        num.portalConfig.allowSenderID = $('#toggleSenderID').is(':checked');
        num.portalConfig.enableInboxReplies = $('#toggleInboxReplies').is(':checked');
        num.portalConfig.enableOptout = $('#toggleOptout').is(':checked');
        
        // Update capabilities array based on toggles
        var caps = [];
        if (num.portalConfig.allowSenderID) caps.push('portal');
        if (num.portalConfig.enableInboxReplies) caps.push('inbox');
        if (num.portalConfig.enableOptout) caps.push('optout');
        num.capabilities = caps;
        
        // TODO: Backend API call to update portal config
        
        // Update drawer capabilities display
        $('#drawerCapabilities').html(formatCapabilities(num.capabilities));
        
        renderTable();
        toastr.success('Portal capabilities updated');
    });

    // Handle set default button clicks
    $(document).on('click', '.btn-set-default', function() {
        var subAccount = $(this).data('subaccount');
        var type = $(this).data('type');
        
        var num = numbersData.find(function(n) { return n.id === currentEditingNumberId; });
        if (!num || num.status !== 'active') {
            toastr.error('Only active numbers can be set as defaults');
            return;
        }
        
        if (!num.portalConfig) num.portalConfig = {};
        if (!num.portalConfig.defaults) num.portalConfig.defaults = {};
        if (!num.portalConfig.defaults[subAccount]) num.portalConfig.defaults[subAccount] = {};
        
        // Clear this default from any other number for the same sub-account
        numbersData.forEach(function(n) {
            if (n.id !== num.id && n.portalConfig && n.portalConfig.defaults && n.portalConfig.defaults[subAccount]) {
                if (type === 'sender') n.portalConfig.defaults[subAccount].defaultSender = false;
                if (type === 'inbox') n.portalConfig.defaults[subAccount].defaultInbox = false;
                if (type === 'optout') n.portalConfig.defaults[subAccount].defaultOptout = false;
            }
        });
        
        // Set this number as default
        if (type === 'sender') num.portalConfig.defaults[subAccount].defaultSender = true;
        if (type === 'inbox') num.portalConfig.defaults[subAccount].defaultInbox = true;
        if (type === 'optout') num.portalConfig.defaults[subAccount].defaultOptout = true;
        
        // TODO: Backend API call to update defaults
        
        renderSubAccountDefaults(num);
        
        var typeLabel = type === 'sender' ? 'Sender' : (type === 'inbox' ? 'Inbox' : 'Opt-out');
        toastr.success('Set as default ' + typeLabel + ' number for ' + subAccount);
    });

    // Mode button click handlers
    $('.mode-btn').on('click', function() {
        var newMode = $(this).data('mode');
        var num = numbersData.find(function(n) { return n.id === currentEditingNumberId; });
        if (!num) return;
        
        // If clicking the currently active mode, just show features
        if (num.mode === newMode) {
            updateModeSelectionUI(newMode);
            return;
        }
        
        // Show mode change confirmation modal
        showModeChangeModal(num, newMode);
    });

    function showModeChangeModal(num, newMode) {
        var oldMode = num.mode;
        
        // Update modal visuals
        if (oldMode === 'portal') {
            $('#modeFromIcon').removeClass('fa-code').addClass('fa-desktop');
            $('#modeFromLabel').text('Portal Mode');
        } else {
            $('#modeFromIcon').removeClass('fa-desktop').addClass('fa-code');
            $('#modeFromLabel').text('API Mode');
        }
        
        if (newMode === 'portal') {
            $('#modeToIcon').removeClass('fa-code').addClass('fa-desktop');
            $('#modeToLabel').text('Portal Mode');
        } else {
            $('#modeToIcon').removeClass('fa-desktop').addClass('fa-code');
            $('#modeToLabel').text('API Mode');
        }
        
        // Update features lists based on mode change direction
        if (newMode === 'api') {
            // Portal -> API: Losing portal features
            $('#disabledFeaturesList').html(
                '<div class="text-danger small mb-1"><i class="fas fa-times me-2"></i>Campaign Composer access</div>' +
                '<div class="text-danger small mb-1"><i class="fas fa-times me-2"></i>Inbox visibility</div>' +
                '<div class="text-danger small mb-1"><i class="fas fa-times me-2"></i>Portal SenderID availability</div>' +
                '<div class="text-danger small mb-1"><i class="fas fa-times me-2"></i>Opt-out management</div>'
            );
            $('#enabledFeaturesList').html(
                '<div class="text-success small mb-1"><i class="fas fa-check me-2"></i>REST API access</div>' +
                '<div class="text-success small mb-1"><i class="fas fa-check me-2"></i>Programmatic sending</div>'
            );
        } else {
            // API -> Portal: Gaining portal features
            $('#disabledFeaturesList').html(
                '<div class="text-danger small mb-1"><i class="fas fa-times me-2"></i>REST API access</div>'
            );
            $('#enabledFeaturesList').html(
                '<div class="text-success small mb-1"><i class="fas fa-check me-2"></i>Campaign Composer access</div>' +
                '<div class="text-success small mb-1"><i class="fas fa-check me-2"></i>Inbox visibility</div>' +
                '<div class="text-success small mb-1"><i class="fas fa-check me-2"></i>Portal SenderID availability</div>' +
                '<div class="text-success small mb-1"><i class="fas fa-check me-2"></i>Opt-out management</div>'
            );
        }
        
        // Store pending mode change
        $('#btnConfirmModeChange').data('number-id', num.id).data('new-mode', newMode);
        
        new bootstrap.Modal(document.getElementById('modeChangeModal')).show();
    }

    // Confirm mode change
    $('#btnConfirmModeChange').on('click', function() {
        var numberId = $(this).data('number-id');
        var newMode = $(this).data('new-mode');
        
        var num = numbersData.find(function(n) { return n.id === numberId; });
        if (!num) return;
        
        var oldMode = num.mode;
        
        // TODO: Backend API call to update mode with audit logging
        // API should: 
        // 1. Update number mode in database
        // 2. Update capabilities based on new mode
        // 3. Log audit entry with user, timestamp, old mode, new mode
        // 4. Propagate changes to all modules immediately
        
        // Update local data
        var oldMode = num.mode;
        num.mode = newMode;
        addAuditEntry(num, 'Mode changed', 'Changed from ' + (oldMode === 'portal' ? 'Portal' : 'API') + ' to ' + (newMode === 'portal' ? 'Portal' : 'API'));
        
        // Update capabilities based on mode
        if (newMode === 'api') {
            // API mode: Only API capability
            num.capabilities = ['api'];
        } else {
            // Portal mode: Portal-related capabilities
            num.capabilities = ['portal', 'inbox', 'optout'];
        }
        
        // Log mode change for audit (simulated)
        console.log('AUDIT LOG: Mode change for number ' + num.number + ' from ' + oldMode + ' to ' + newMode + ' at ' + new Date().toISOString());
        
        // Update UI
        updateModeSelectionUI(newMode);
        $('#drawerMode').text(newMode === 'portal' ? 'Portal' : 'API');
        $('#drawerModeBadge').text(newMode === 'portal' ? 'Portal' : 'API');
        $('#drawerCapabilities').html(formatCapabilities(num.capabilities));
        
        // Populate portal config if switching to portal mode
        if (newMode === 'portal') {
            if (!num.portalConfig) {
                num.portalConfig = {
                    allowSenderID: true,
                    enableInboxReplies: true,
                    enableOptout: true,
                    defaults: {}
                };
            }
            populatePortalConfig(num);
        } else {
            // Initialize API config if switching to API mode
            if (!num.apiConfig) {
                num.apiConfig = {
                    inboundForwarding: false,
                    inboundUrl: ''
                };
            }
            // API mode numbers can only have one sub-account
            if (num.subAccounts && num.subAccounts.length > 1) {
                num.subAccounts = [num.subAccounts[0]];
            }
            populateApiConfig(num);
        }
        
        renderTable();
        
        bootstrap.Modal.getInstance(document.getElementById('modeChangeModal')).hide();
        toastr.success('Operating mode changed to ' + (newMode === 'portal' ? 'Portal' : 'API') + ' Mode');
    });

    // Edit number function - navigate to configuration page
    window.editNumber = function(id) {
        navigateToConfigurePage([id]);
    };

    // Helper function for two-step confirmation modal
    function showConfirmModal(title, message, btnText, btnClass, callback, warning) {
        $('#confirmModalLabel').text(title);
        $('#confirmModalMessage').text(message);
        
        if (warning) {
            $('#confirmModalWarning').show();
            $('#confirmModalWarningText').text(warning);
        } else {
            $('#confirmModalWarning').hide();
        }
        
        $('#confirmModalBtn').removeClass('btn-primary btn-warning btn-danger btn-success').addClass(btnClass).text(btnText);
        
        $('#confirmModalBtn').off('click').on('click', function() {
            bootstrap.Modal.getInstance(document.getElementById('confirmModal')).hide();
            if (callback) {
                setTimeout(callback, 300);
            }
        });
        
        new bootstrap.Modal(document.getElementById('confirmModal')).show();
    }

    // Suspend number function - two-step confirmation
    window.suspendNumber = function(id) {
        var num = numbersData.find(function(n) { return n.id === id; });
        if (!num) return;
        
        // Step 1 of 2
        showConfirmModal(
            'Suspend Number - Step 1 of 2',
            'Suspending "' + num.number + '" will hide it from sender pickers across all modules. Any campaigns using this number will be affected.',
            'Continue',
            'btn-primary',
            function() {
                // Step 2 of 2 - Final confirmation
                showConfirmModal(
                    'Suspend Number - Final Confirmation',
                    'Please confirm you want to suspend "' + num.number + '". This will affect Send Message, Inbox, and Email-to-SMS functionality.',
                    'Suspend Now',
                    'btn-danger',
                    function() {
                        // TODO: Backend API call with audit logging
                        num.status = 'suspended';
                        addAuditEntry(num, 'Status changed', 'Number suspended');
                        renderTable();
                        toastr.success('Number "' + num.number + '" has been suspended.');
                    },
                    null
                );
            },
            'This will affect Send Message, Inbox, and Email-to-SMS functionality.'
        );
    };

    // Reactivate number function - two-step confirmation
    window.reactivateNumber = function(id) {
        var num = numbersData.find(function(n) { return n.id === id; });
        if (!num) return;
        
        // Block if already active
        if (num.status === 'active') {
            toastr.error('This number is already active.');
            return;
        }
        
        // Step 1 of 2
        showConfirmModal(
            'Reactivate Number - Step 1 of 2',
            'Reactivating "' + num.number + '" will restore it to sender pickers. The number will immediately become available for messaging.',
            'Continue',
            'btn-primary',
            function() {
                // Step 2 of 2 - Final confirmation
                showConfirmModal(
                    'Reactivate Number - Final Confirmation',
                    'Please confirm you want to reactivate "' + num.number + '". The number will be available immediately.',
                    'Reactivate Now',
                    'btn-primary',
                    function() {
                        // TODO: Backend API call with audit logging
                        num.status = 'active';
                        addAuditEntry(num, 'Status changed', 'Number reactivated');
                        renderTable();
                        toastr.success('Number "' + num.number + '" has been reactivated.');
                    },
                    null
                );
            },
            null
        );
    };

    // Assign Sub-Accounts function (Portal mode only)
    window.assignSubAccountsToNumber = function(id) {
        var num = numbersData.find(function(n) { return n.id === id; });
        if (!num || num.mode !== 'portal') return;
        
        // Populate modal with current assignments
        $('#assignSubAccountNumber').text(num.number);
        $('.assign-subacc-check').prop('checked', false);
        if (num.subAccounts) {
            num.subAccounts.forEach(function(sa) {
                $('.assign-subacc-check[value="' + sa + '"]').prop('checked', true);
            });
        }
        
        // Store current number ID for confirmation
        $('#assignSubAccountModal').data('number-id', id);
        
        new bootstrap.Modal(document.getElementById('assignSubAccountModal')).show();
    };
    
    // Handle Assign Sub-Accounts confirmation
    $('#btnConfirmAssignSubAccounts').on('click', function() {
        var id = $('#assignSubAccountModal').data('number-id');
        var num = numbersData.find(function(n) { return n.id === id; });
        if (!num) return;
        
        // Get selected sub-accounts
        var selectedSubAccounts = [];
        $('.assign-subacc-check:checked').each(function() {
            selectedSubAccounts.push($(this).val());
        });
        
        if (selectedSubAccounts.length === 0) {
            toastr.warning('Please select at least one sub-account');
            return;
        }
        
        // TODO: Backend API call with audit logging
        var oldSubAccounts = num.subAccounts ? num.subAccounts.slice() : [];
        num.subAccounts = selectedSubAccounts;
        
        // Log sub-account changes
        var added = selectedSubAccounts.filter(function(sa) { return !oldSubAccounts.includes(sa); });
        var removed = oldSubAccounts.filter(function(sa) { return !selectedSubAccounts.includes(sa); });
        var changes = [];
        if (added.length > 0) changes.push('Added: ' + added.join(', '));
        if (removed.length > 0) changes.push('Removed: ' + removed.join(', '));
        if (changes.length > 0) {
            addAuditEntry(num, 'Sub-account changed', changes.join('; '));
        }
        
        // Update portalConfig defaults - remove defaults for unassigned sub-accounts
        if (num.portalConfig && num.portalConfig.defaults) {
            var newDefaults = {};
            selectedSubAccounts.forEach(function(sa) {
                if (num.portalConfig.defaults[sa]) {
                    newDefaults[sa] = num.portalConfig.defaults[sa];
                }
            });
            num.portalConfig.defaults = newDefaults;
        }
        
        renderTable();
        bootstrap.Modal.getInstance(document.getElementById('assignSubAccountModal')).hide();
        toastr.success('Sub-account assignments updated for ' + num.number);
    });

    // Initial render
    renderTable();
});
</script>
@endpush
