@extends('layouts.quicksms')

@section('title', 'Download Area')

@push('styles')
<style>
.download-area-fixed-header {
    overflow: visible;
}
#filtersPanel {
    overflow: visible !important;
}
#filtersPanel .card-body {
    overflow: visible !important;
}
.download-area-table-wrapper {
    display: flex;
    flex-direction: column;
    overflow: hidden;
    min-height: 0;
}
#tableContainer {
    overflow-y: auto;
    overflow-x: auto;
}
.download-area-footer {
    flex-shrink: 0;
    margin-top: auto;
}
#downloadAreaTable {
    width: 100%;
    border-collapse: collapse;
    margin: 0;
}
#downloadAreaTable thead th {
    background: #f8f9fa !important;
    border-bottom: 1px solid #e9ecef !important;
    padding: 0.75rem 0.5rem !important;
    font-weight: 600 !important;
    font-size: 0.8rem !important;
    color: #495057 !important;
    white-space: nowrap;
    text-transform: none !important;
    letter-spacing: normal !important;
    position: sticky;
    top: 0;
    z-index: 10;
}
#downloadAreaTable thead th:hover {
    background: #e9ecef !important;
}
#downloadAreaTable tbody tr {
    cursor: pointer;
    transition: background-color 0.15s ease;
}
#downloadAreaTable tbody tr:hover td {
    background-color: #f8f9fa !important;
}
#downloadAreaTable tbody td {
    padding: 0.75rem 0.5rem;
    vertical-align: middle;
    border-bottom: 1px solid #f1f3f5;
    font-size: 0.85rem;
    color: #495057;
}
#downloadAreaTable tbody tr:last-child td {
    border-bottom: none;
}
#downloadAreaTable tbody td:first-child {
    font-weight: 500;
    color: #343a40;
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
.file-type-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    font-weight: 500;
}
.file-type-csv { background-color: #d4edda; color: #155724; }
.file-type-xlsx { background-color: #cce5ff; color: #004085; }
.file-type-pdf { background-color: #f8d7da; color: #721c24; }
.file-type-zip { background-color: #fff3cd; color: #856404; }
.status-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    font-weight: 500;
}
.status-ready { background-color: #d4edda; color: #155724; }
.status-expired { background-color: #e2e3e5; color: #6c757d; }
.status-processing { background-color: #fff3cd; color: #856404; }
.status-pill {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.7rem;
    font-weight: 500;
    white-space: nowrap;
}
.status-pill-completed {
    background-color: rgba(28, 187, 140, 0.15);
    color: #1cbb8c;
}
.status-pill-failed {
    background-color: rgba(220, 53, 69, 0.15);
    color: #dc3545;
}
.status-pill-expired {
    background-color: rgba(108, 117, 125, 0.15);
    color: #6c757d;
}
.status-pill-processing {
    background-color: rgba(255, 191, 0, 0.15);
    color: #cc9900;
}
.empty-state {
    text-align: center;
    padding: 3rem;
    color: #6c757d;
}
.empty-state i {
    font-size: 4rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}
.loading-state {
    text-align: center;
    padding: 3rem;
    color: #6c757d;
}
.loading-state .spinner-border {
    width: 2rem;
    height: 2rem;
    margin-bottom: 1rem;
}
.table-loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 100;
}
.large-file-indicator {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
}
.large-file-indicator .file-size-warning {
    color: #856404;
    font-size: 0.75rem;
}
.pagination-info {
    font-size: 0.875rem;
    color: #6c757d;
}
.year-separator {
    background-color: #f3f0f9;
    font-weight: 600;
    color: #495057;
}
.year-separator td {
    padding: 0.5rem 1rem;
    border-bottom: 2px solid #886cc0;
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
    border-color: #886cc0;
}
.date-preset-btn.active {
    background: #886cc0;
    color: #fff;
    border-color: #886cc0;
}
.multiselect-dropdown {
    position: relative;
}
.multiselect-dropdown .dropdown-menu {
    position: absolute;
    top: 100%;
    left: 0;
    z-index: 1050;
    display: none;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}
.multiselect-dropdown .dropdown-menu.show {
    display: block;
}
.sortable {
    cursor: pointer;
    user-select: none;
    white-space: nowrap;
}
.sortable:hover {
    background-color: rgba(136, 108, 192, 0.08);
}
.sortable i {
    opacity: 0.4;
    transition: opacity 0.15s ease;
}
.sortable:hover i {
    opacity: 0.7;
}
.sortable.sort-asc i,
.sortable.sort-desc i {
    opacity: 1;
    color: #886cc0;
}
.action-menu {
    position: relative;
    display: inline-block;
}
.action-menu-btn {
    background: none;
    border: none;
    padding: 0.25rem 0.5rem;
    cursor: pointer;
    color: #6c757d;
    border-radius: 0.25rem;
}
.action-menu-btn:hover {
    background-color: #f8f9fa;
    color: #495057;
}
.action-dropdown {
    position: absolute;
    right: 0;
    top: 100%;
    background: #fff;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    min-width: 140px;
    z-index: 1050;
    display: none;
}
.action-dropdown.show {
    display: block;
}
.action-dropdown-item {
    display: block;
    width: 100%;
    padding: 0.5rem 1rem;
    border: none;
    background: none;
    text-align: left;
    cursor: pointer;
    font-size: 0.875rem;
    color: #212529;
}
.action-dropdown-item:hover {
    background-color: #f8f9fa;
}
.action-dropdown-item.text-danger:hover {
    background-color: #fff5f5;
}
.action-dropdown .dropdown-divider {
    height: 0;
    margin: 0.5rem 0;
    border-top: 1px solid #e9ecef;
}
.status-text-completed {
    color: #198754;
    font-weight: 500;
}
.status-text-failed {
    color: #dc3545;
    font-weight: 500;
}
.status-text-expired {
    color: #6c757d;
    font-weight: 500;
}
.row-expired {
    opacity: 0.6;
    background-color: #f8f9fa;
}
.row-expired td {
    color: #6c757d;
}
.row-failed {
    background-color: #fff5f5;
}
.expired-badge {
    display: inline-flex;
    align-items: center;
    font-size: 0.75rem;
    padding: 0.125rem 0.5rem;
    border-radius: 4px;
    background: #e9ecef;
    color: #6c757d;
}
.failed-badge {
    display: inline-flex;
    align-items: center;
    font-size: 0.75rem;
    padding: 0.125rem 0.5rem;
    border-radius: 4px;
    background: #f8d7da;
    color: #721c24;
}
.error-reason-box {
    background: #fff5f5;
    border: 1px solid #f5c6cb;
    border-radius: 6px;
    padding: 0.75rem;
    margin-top: 0.5rem;
}
.error-reason-box .error-title {
    font-weight: 600;
    color: #721c24;
    font-size: 0.8125rem;
    margin-bottom: 0.5rem;
}
.error-reason-box .error-message {
    font-size: 0.8125rem;
    color: #856404;
}
.retry-link {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: #f0f4ff;
    border: 1px solid #b8c9ff;
    border-radius: 6px;
    color: #4a6fd1;
    text-decoration: none;
    font-size: 0.875rem;
    margin-top: 0.75rem;
}
.retry-link:hover {
    background: #e0e8ff;
    color: #3a5fc1;
}
.expiry-info {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.8125rem;
    color: #6c757d;
    margin-top: 0.5rem;
}
.expiry-info.expiring-soon {
    color: #856404;
}
.expiry-info.expired {
    color: #dc3545;
}
.details-drawer {
    position: fixed;
    top: 0;
    right: -400px;
    width: 400px;
    height: 100%;
    background: #fff;
    box-shadow: -4px 0 20px rgba(0, 0, 0, 0.15);
    z-index: 1060;
    transition: right 0.3s ease;
    display: flex;
    flex-direction: column;
}
.details-drawer.show {
    right: 0;
}
.details-drawer-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.3);
    z-index: 1055;
    display: none;
}
.details-drawer-backdrop.show {
    display: block;
}
.details-drawer-header {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.details-drawer-body {
    flex: 1;
    overflow-y: auto;
    padding: 1.5rem;
}
.details-drawer-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid #e9ecef;
    background: #f8f9fa;
}
.detail-row {
    margin-bottom: 1rem;
}
.detail-label {
    font-size: 0.75rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.25rem;
}
.detail-value {
    font-size: 0.9375rem;
    color: #212529;
}
.detail-section {
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #f0f0f0;
}
.detail-section:last-child {
    border-bottom: none;
    margin-bottom: 0;
}
.detail-section-title {
    font-size: 0.8125rem;
    font-weight: 600;
    color: #495057;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.75rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #e9ecef;
}
.detail-filters-list {
    background: #f8f9fa;
    border-radius: 6px;
    padding: 0.75rem;
}
.detail-filter-item {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 0.5rem 0;
    border-bottom: 1px solid #e9ecef;
}
.detail-filter-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
}
.detail-filter-item:first-child {
    padding-top: 0;
}
.detail-filter-name {
    font-size: 0.8125rem;
    color: #6c757d;
    min-width: 100px;
}
.detail-filter-value {
    font-size: 0.8125rem;
    color: #212529;
    text-align: right;
    flex: 1;
    word-break: break-word;
}
.filters-preview-box {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 6px;
    padding: 0.75rem;
    max-height: 180px;
    overflow-y: auto;
}

.audit-trail-container {
    background: #f8f9fa;
    border-radius: 6px;
    padding: 0;
    max-height: 300px;
    overflow-y: auto;
}
.audit-trail-empty {
    padding: 1rem;
    text-align: center;
    color: #6c757d;
    font-size: 0.8125rem;
}
.audit-entry {
    padding: 0.75rem;
    border-bottom: 1px solid #e9ecef;
    position: relative;
}
.audit-entry:last-child {
    border-bottom: none;
}
.audit-entry-icon {
    position: absolute;
    left: 0.75rem;
    top: 0.875rem;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
}
.audit-entry-icon.generated {
    background: #d4edda;
    color: #155724;
}
.audit-entry-icon.downloaded {
    background: #cce5ff;
    color: #004085;
}
.audit-entry-icon.scheduled {
    background: #fff3cd;
    color: #856404;
}
.audit-entry-icon.schedule-changed {
    background: #e2e3e5;
    color: #383d41;
}
.audit-entry-icon.schedule-paused {
    background: #ffeeba;
    color: #856404;
}
.audit-entry-icon.schedule-resumed {
    background: #c3e6cb;
    color: #155724;
}
.audit-entry-content {
    padding-left: 2.5rem;
}
.audit-entry-action {
    font-size: 0.8125rem;
    font-weight: 500;
    color: #212529;
    margin-bottom: 0.125rem;
}
.audit-entry-user {
    font-size: 0.75rem;
    color: #6c757d;
}
.audit-entry-timestamp {
    font-size: 0.6875rem;
    color: #adb5bd;
    margin-top: 0.25rem;
}
.audit-read-only-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    background: #f0f0f0;
    color: #6c757d;
    font-size: 0.6875rem;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    margin-bottom: 0.5rem;
}
.audit-read-only-badge i {
    font-size: 0.625rem;
}
.filters-preview-box .filter-item {
    display: flex;
    justify-content: space-between;
    padding: 0.375rem 0;
    border-bottom: 1px solid #e9ecef;
    font-size: 0.8125rem;
}
.filters-preview-box .filter-item:last-child {
    border-bottom: none;
}
.filters-preview-box .filter-name {
    color: #6c757d;
}
.filters-preview-box .filter-value {
    color: #212529;
    font-weight: 500;
}
.naming-preview-box {
    background: #fff3cd;
    border: 1px solid #ffc107;
    border-radius: 6px;
    padding: 0.75rem;
    font-size: 0.875rem;
}
.schedule-filters-preview,
.schedule-naming-preview {
    margin-bottom: 1rem;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row page-titles mb-2">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('reporting.dashboard') }}">Reporting</a></li>
            <li class="breadcrumb-item active">Download Area</li>
        </ol>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                    <h5 class="card-title mb-2 mb-md-0">Download Area</h5>
                    <div class="d-flex align-items-center gap-2">
                        <div class="btn-group" id="bulkActionsHeader" style="display: none;">
                            <button class="btn btn-outline-primary btn-sm" onclick="downloadSelected()">
                                <i class="fas fa-download me-1"></i>Download Selected
                            </button>
                            <button class="btn btn-outline-danger btn-sm" onclick="deleteSelected()">
                                <i class="fas fa-trash me-1"></i>Delete
                            </button>
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#filtersPanel">
                            <i class="fas fa-filter me-1"></i> Filters
                        </button>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="refreshDownloads()">
                            <i class="fas fa-sync-alt me-1"></i> Refresh
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="download-area-fixed-header">
                        <div class="collapse mb-3" id="filtersPanel">
                        <div class="card card-body border-0 rounded-3" style="background-color: #f0ebf8;">
                                <div class="row g-3">
                                    <div class="col-md-2">
                                        <label class="form-label small text-muted mb-1">Year</label>
                                        <select class="form-select form-select-sm" id="filterYear">
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label small text-muted mb-1">Month</label>
                                        <select class="form-select form-select-sm" id="filterMonth">
                                            <option value="">All Months</option>
                                            <option value="1">January</option>
                                            <option value="2">February</option>
                                            <option value="3">March</option>
                                            <option value="4">April</option>
                                            <option value="5">May</option>
                                            <option value="6">June</option>
                                            <option value="7">July</option>
                                            <option value="8">August</option>
                                            <option value="9">September</option>
                                            <option value="10">October</option>
                                            <option value="11">November</option>
                                            <option value="12">December</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label small text-muted mb-1">Module</label>
                                        <select class="form-select form-select-sm" id="filterModule">
                                            <option value="">All Modules</option>
                                            <option value="message_logs">Message Logs</option>
                                            <option value="finance_data">Finance Data</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small text-muted mb-1">Sub-account</label>
                                        <div class="multiselect-dropdown">
                                            <div class="form-control form-control-sm d-flex align-items-center justify-content-between" 
                                                 id="subAccountDropdownTrigger" 
                                                 onclick="toggleSubAccountDropdown()" 
                                                 style="cursor: pointer; min-height: 31px;">
                                                <span id="subAccountDisplayText">All Sub-accounts</span>
                                                <i class="fas fa-chevron-down small"></i>
                                            </div>
                                            <div class="dropdown-menu p-2" id="subAccountDropdown" style="min-width: 250px; max-height: 250px; overflow-y: auto;">
                                                <div class="mb-2">
                                                    <input type="text" class="form-control form-control-sm" id="subAccountSearch" placeholder="Search sub-accounts..." oninput="filterSubAccountOptions()">
                                                </div>
                                                <div class="form-check mb-1">
                                                    <input type="checkbox" class="form-check-input" id="subAccountSelectAll" onchange="toggleAllSubAccounts()">
                                                    <label class="form-check-label small" for="subAccountSelectAll">Select All</label>
                                                </div>
                                                <hr class="my-2">
                                                <div id="subAccountOptions">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small fw-bold">User</label>
                                        <div class="dropdown multiselect-dropdown" data-filter="users">
                                            <button class="btn btn-sm dropdown-toggle w-100 text-start d-flex justify-content-between align-items-center" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" style="background-color: #fff; border: 1px solid #ced4da; color: #495057;">
                                                <span class="dropdown-label" id="userDropdownLabel">All Users</span>
                                            </button>
                                            <div class="dropdown-menu w-100 p-2" id="userDropdownMenu">
                                                <div class="d-flex justify-content-between mb-2 border-bottom pb-2">
                                                    <a href="#" class="small text-decoration-none" onclick="selectAllUsers(event)">Select All</a>
                                                    <a href="#" class="small text-decoration-none" onclick="clearAllUsers(event)">Clear</a>
                                                </div>
                                                <div id="userOptions"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-12 d-flex justify-content-end gap-2">
                                        <button type="button" class="btn btn-primary btn-sm" onclick="applyFilters()">
                                            <i class="fas fa-check me-1"></i> Apply Filters
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="resetFilters()">
                                            <i class="fas fa-undo me-1"></i> Reset Filters
                                        </button>
                                    </div>
                                </div>
                        </div>
                        </div>

                        <div class="mb-3" id="activeFiltersContainer" style="display: none;">
                            <div class="d-flex flex-wrap align-items-center">
                                <span class="small text-muted me-2">Active filters:</span>
                                <div id="filterChips"></div>
                                <button type="button" class="btn btn-link btn-sm text-decoration-none p-0 ms-2" onclick="clearFilters()">Clear all</button>
                            </div>
                        </div>
                    </div>
                    <div class="download-area-table-wrapper">
                        <div id="tableContainer" class="table-responsive">
                            <table class="table table-hover mb-0" id="downloadAreaTable">
                                <thead>
                                    <tr>
                                        <th style="width: 40px;">
                                            <input type="checkbox" class="form-check-input" id="selectAll" onclick="toggleSelectAll()">
                                        </th>
                                        <th class="sortable" data-sort="reportName" onclick="sortTable('reportName')">
                                            Report Name <i class="fas fa-sort text-muted ms-1"></i>
                                        </th>
                                        <th class="sortable" data-sort="module" onclick="sortTable('module')">
                                            Module <i class="fas fa-sort text-muted ms-1"></i>
                                        </th>
                                        <th class="sortable" data-sort="subAccount" onclick="sortTable('subAccount')">
                                            Sub-account <i class="fas fa-sort text-muted ms-1"></i>
                                        </th>
                                        <th class="sortable" data-sort="generatedBy" onclick="sortTable('generatedBy')">
                                            Generated By <i class="fas fa-sort text-muted ms-1"></i>
                                        </th>
                                        <th class="sortable" data-sort="dateGenerated" onclick="sortTable('dateGenerated')">
                                            Date Generated <i class="fas fa-sort text-muted ms-1"></i>
                                        </th>
                                        <th class="sortable" data-sort="fileType" onclick="sortTable('fileType')">
                                            File Type <i class="fas fa-sort text-muted ms-1"></i>
                                        </th>
                                        <th class="sortable" data-sort="fileSize" onclick="sortTable('fileSize')">
                                            File Size <i class="fas fa-sort text-muted ms-1"></i>
                                        </th>
                                        <th class="sortable" data-sort="status" onclick="sortTable('status')">
                                            Status <i class="fas fa-sort text-muted ms-1"></i>
                                        </th>
                                        <th style="width: 60px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="downloadsTableBody">
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="download-area-footer border-top pt-3 mt-3">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-3">
                                <span class="text-muted small">
                                    Showing <span id="showingCount">0</span> of <span id="totalCount">0</span> downloads
                                </span>
                                <div class="btn-group" id="bulkActions" style="display: none;">
                                    <button class="btn btn-sm btn-outline-primary" onclick="downloadSelected()">
                                        <i class="fas fa-download me-1"></i>Download Selected
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteSelected()">
                                        <i class="fas fa-trash me-1"></i>Delete Selected
                                    </button>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <select class="form-select form-select-sm" style="width: auto;" id="pageSize" onchange="changePageSize()">
                                    <option value="10">10 per page</option>
                                    <option value="25" selected>25 per page</option>
                                    <option value="50">50 per page</option>
                                    <option value="100">100 per page</option>
                                </select>
                                <nav aria-label="Downloads pagination">
                                    <ul class="pagination pagination-sm mb-0" id="pagination">
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="deleteConfirmModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">Are you sure you want to delete <span id="deleteCount">1</span> download(s)?</p>
                <p class="text-muted small mb-0 mt-2">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete()">Delete</button>
            </div>
        </div>
    </div>
</div>


<div class="details-drawer-backdrop" id="detailsDrawerBackdrop" onclick="closeDetailsDrawer()"></div>
<div class="details-drawer" id="detailsDrawer">
    <div class="details-drawer-header">
        <h5 class="mb-0">Report Details</h5>
        <button type="button" class="btn-close" onclick="closeDetailsDrawer()"></button>
    </div>
    <div class="details-drawer-body">
        <div class="detail-section">
            <h6 class="detail-section-title">Report Information</h6>
            <div class="detail-row">
                <div class="detail-label">Report Name</div>
                <div class="detail-value" id="detailReportName">—</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Report ID</div>
                <div class="detail-value text-muted" id="detailReportId">—</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Module</div>
                <div class="detail-value" id="detailModule">—</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Sub-account</div>
                <div class="detail-value" id="detailSubAccount">—</div>
            </div>
        </div>

        <div class="detail-section">
            <h6 class="detail-section-title">Generation Details</h6>
            <div class="detail-row">
                <div class="detail-label">Generated By</div>
                <div class="detail-value" id="detailGeneratedBy">—</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Date & Time Generated</div>
                <div class="detail-value" id="detailDateGenerated">—</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Timeframe Covered</div>
                <div class="detail-value" id="detailTimeframe">—</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Number of Records</div>
                <div class="detail-value" id="detailRecordCount">—</div>
            </div>
        </div>

        <div class="detail-section">
            <h6 class="detail-section-title">Filters Applied</h6>
            <div class="detail-filters-list" id="detailFiltersApplied">
                <div class="text-muted small">No filters applied</div>
            </div>
        </div>

        <div class="detail-section">
            <h6 class="detail-section-title">File Information</h6>
            <div class="detail-row">
                <div class="detail-label">File Format</div>
                <div class="detail-value" id="detailFileType">—</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">File Size</div>
                <div class="detail-value" id="detailFileSize">—</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Status</div>
                <div class="detail-value" id="detailStatus">—</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Retention</div>
                <div class="detail-value">
                    <span id="detailRetention">90 days</span>
                    <div class="expiry-info" id="detailExpiryInfo" style="display: none;">
                        <i class="fas fa-clock"></i>
                        <span id="detailExpiryText">Expires on 15/04/2026</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="detail-section" id="detailErrorSection" style="display: none;">
            <h6 class="detail-section-title">Error Information</h6>
            <div class="error-reason-box">
                <div class="error-title"><i class="fas fa-exclamation-triangle me-1"></i>Export Failed</div>
                <div class="error-message" id="detailErrorReason">—</div>
            </div>
            <a href="#" class="retry-link" id="detailRetryLink" onclick="retryFromModule(event)">
                <i class="fas fa-redo"></i>
                <span>Retry from source module</span>
            </a>
        </div>

        <div class="detail-section" id="detailExpiredSection" style="display: none;">
            <h6 class="detail-section-title">File Status</h6>
            <div class="alert alert-secondary small mb-0">
                <i class="fas fa-archive me-1"></i>
                <strong>File Expired</strong>
                <p class="mb-0 mt-1">This file has exceeded the retention period and is no longer available for download. The report metadata has been preserved for audit purposes.</p>
            </div>
            <a href="#" class="retry-link" id="detailRegenerateLink" onclick="regenerateFromModule(event)">
                <i class="fas fa-sync-alt"></i>
                <span>Generate new export from source module</span>
            </a>
        </div>

        <div class="detail-section" id="detailRecurrenceSection" style="display: none;">
            <h6 class="detail-section-title">Recurrence Schedule</h6>
            <div class="detail-row">
                <div class="detail-label">Frequency</div>
                <div class="detail-value" id="detailRecurrenceFrequency">—</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Next Run</div>
                <div class="detail-value" id="detailRecurrenceNext">—</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Recipients</div>
                <div class="detail-value" id="detailRecurrenceRecipients">—</div>
            </div>
        </div>

        <div class="detail-section">
            <h6 class="detail-section-title"><i class="fas fa-history me-1"></i>Audit Trail</h6>
            <div class="audit-trail-container" id="detailAuditTrail">
                <div class="text-muted small">Loading audit data...</div>
            </div>
        </div>
    </div>
    <div class="details-drawer-footer">
        <div class="d-flex gap-2">
            <button class="btn btn-primary flex-grow-1" id="detailDownloadBtn" onclick="downloadFromDetails()">
                <i class="fas fa-download me-1"></i>Download Report
            </button>
            <button class="btn btn-outline-secondary" onclick="scheduleFromDetails()">
                <i class="fas fa-clock"></i>
            </button>
        </div>
    </div>
</div>


<div class="modal fade" id="scheduleRecurringModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-clock me-2"></i>Schedule Recurring Report</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info small mb-3">
                    <i class="fas fa-info-circle me-1"></i>
                    Scheduled reports will use the <strong>exact same filters</strong> from the original export. Each run creates a new entry in the Download Area.
                </div>
                
                <input type="hidden" id="scheduleReportId">
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Base Report</label>
                            <input type="text" class="form-control" id="scheduleReportName" readonly>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Frequency <span class="text-danger">*</span></label>
                            <select class="form-select" id="scheduleFrequency" onchange="onFrequencyChange()">
                                <option value="daily">Daily</option>
                                <option value="weekly" selected>Weekly</option>
                                <option value="monthly">Monthly</option>
                            </select>
                        </div>
                        
                        <div class="mb-3" id="weeklyOptions">
                            <label class="form-label">Day of Week</label>
                            <select class="form-select" id="scheduleDayOfWeek">
                                <option value="1">Monday</option>
                                <option value="2">Tuesday</option>
                                <option value="3">Wednesday</option>
                                <option value="4">Thursday</option>
                                <option value="5">Friday</option>
                                <option value="6">Saturday</option>
                                <option value="0">Sunday</option>
                            </select>
                        </div>
                        
                        <div class="mb-3" id="monthlyOptions" style="display: none;">
                            <label class="form-label">Day of Month</label>
                            <select class="form-select" id="scheduleDayOfMonth">
                                <option value="1">1st</option>
                                <option value="5">5th</option>
                                <option value="10">10th</option>
                                <option value="15">15th</option>
                                <option value="20">20th</option>
                                <option value="25">25th</option>
                                <option value="last">Last day of month</option>
                            </select>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label">Run Time <span class="text-danger">*</span></label>
                                <input type="time" class="form-control" id="scheduleTime" value="09:00">
                            </div>
                            <div class="col-6">
                                <label class="form-label">Time Zone</label>
                                <select class="form-select" id="scheduleTimezone">
                                    <option value="Europe/London" selected>London (GMT/BST)</option>
                                    <option value="Europe/Paris">Paris (CET)</option>
                                    <option value="America/New_York">New York (EST)</option>
                                    <option value="America/Los_Angeles">Los Angeles (PST)</option>
                                    <option value="Asia/Tokyo">Tokyo (JST)</option>
                                    <option value="UTC">UTC</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Delivery Method</label>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="deliveryDownloadArea" checked disabled>
                                <label class="form-check-label" for="deliveryDownloadArea">
                                    Store in Download Area
                                </label>
                                <div class="form-text">Reports are always saved to Download Area</div>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="deliveryEmail" onchange="toggleEmailRecipients()">
                                <label class="form-check-label" for="deliveryEmail">
                                    Email notification
                                    <span class="badge bg-secondary ms-1">Coming Soon</span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="mb-3" id="emailRecipientsGroup" style="display: none;">
                            <label class="form-label">Email Recipients</label>
                            <input type="text" class="form-control" id="scheduleRecipients" placeholder="email@example.com, another@example.com">
                            <div class="form-text">Comma-separated email addresses to receive the report.</div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="schedule-filters-preview">
                            <label class="form-label">Filters to be Applied (Read-only)</label>
                            <div class="filters-preview-box" id="scheduleFiltersPreview">
                                <div class="text-muted small">Loading filters...</div>
                            </div>
                            <div class="form-text mt-2">
                                <i class="fas fa-lock me-1"></i>These filters cannot be modified. To use different filters, create a new export first.
                            </div>
                        </div>
                        
                        <div class="schedule-naming-preview mt-3">
                            <label class="form-label">Report Naming</label>
                            <div class="naming-preview-box" id="scheduleNamingPreview">
                                <span class="text-muted">Example: </span>
                                <span id="namingExample">Message Logs – Jan 22 2026 – 090000</span>
                            </div>
                            <div class="form-text">Each scheduled run includes the run date in the report name.</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveSchedule()">
                    <i class="fas fa-check me-1"></i>Save Schedule
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
var retentionDays = 90;

var mockDownloads = [
    { 
        id: 1, 
        reportName: 'Message Logs – Jan 01-15 2026 – 143022', 
        module: 'message_logs', 
        subAccount: 'Main Account', 
        generatedBy: 'John Smith', 
        dateGenerated: '2026-01-15 14:30:22', 
        fileType: 'CSV', 
        fileSize: 2.4, 
        status: 'Completed', 
        year: 2026, 
        month: 1,
        timeframe: { start: '2026-01-01', end: '2026-01-15' },
        recordCount: 1247,
        filters: { channel: 'All Channels', status: 'Delivered', direction: 'Outbound' },
        recurrence: { frequency: 'weekly', dayOfWeek: 1, time: '09:00', nextRun: '2026-01-22 09:00:00', recipients: ['john@example.com', 'reports@company.com'] },
        expiresAt: '2026-04-15 14:30:22',
        errorReason: null,
        auditTrail: [
            { type: 'generated', user: 'John Smith', timestamp: '2026-01-15 14:30:22', details: 'Report generated successfully' },
            { type: 'scheduled', user: 'John Smith', timestamp: '2026-01-15 14:35:10', details: 'Weekly schedule created (Mondays at 09:00 GMT)' },
            { type: 'downloaded', user: 'John Smith', timestamp: '2026-01-15 14:45:33', details: null },
            { type: 'downloaded', user: 'Jane Doe', timestamp: '2026-01-16 09:12:05', details: null },
            { type: 'schedule-changed', user: 'John Smith', timestamp: '2026-01-17 11:20:00', details: 'Added recipient: reports@company.com' }
        ]
    },
    { 
        id: 2, 
        reportName: 'Finance Data – Q4 2025 – 091545', 
        module: 'finance_data', 
        subAccount: 'Sub Account A', 
        generatedBy: 'Jane Doe', 
        dateGenerated: '2025-12-14 09:15:45', 
        fileType: 'XLSX', 
        fileSize: 1.8, 
        status: 'Completed', 
        year: 2025, 
        month: 12,
        timeframe: { start: '2025-10-01', end: '2025-12-31' },
        recordCount: 892,
        filters: { transactionType: 'All', costCenter: 'Marketing' },
        recurrence: null,
        expiresAt: '2026-03-14 09:15:45',
        errorReason: null,
        auditTrail: [
            { type: 'generated', user: 'Jane Doe', timestamp: '2025-12-14 09:15:45', details: 'Report generated successfully' },
            { type: 'downloaded', user: 'Jane Doe', timestamp: '2025-12-14 09:18:22', details: null },
            { type: 'downloaded', user: 'Mike Johnson', timestamp: '2025-12-15 14:05:11', details: null }
        ]
    },
    { 
        id: 3, 
        reportName: 'Message Logs – Nov 2025 – 164512', 
        module: 'message_logs', 
        subAccount: 'Sub Account B', 
        generatedBy: 'John Smith', 
        dateGenerated: '2025-11-13 16:45:12', 
        fileType: 'CSV', 
        fileSize: 0.856, 
        status: 'Expired', 
        year: 2025, 
        month: 11,
        timeframe: { start: '2025-11-01', end: '2025-11-30' },
        recordCount: 456,
        filters: { channel: 'SMS', status: 'All Statuses' },
        recurrence: null,
        expiresAt: '2025-02-11 16:45:12',
        errorReason: null,
        auditTrail: [
            { type: 'generated', user: 'John Smith', timestamp: '2025-11-13 16:45:12', details: 'Report generated successfully' },
            { type: 'downloaded', user: 'John Smith', timestamp: '2025-11-13 17:02:44', details: null }
        ]
    },
    { 
        id: 4, 
        reportName: 'Finance Data – Oct 2025 – 112033', 
        module: 'finance_data', 
        subAccount: 'Main Account', 
        generatedBy: 'Mike Johnson', 
        dateGenerated: '2025-10-12 11:20:33', 
        fileType: 'XLSX', 
        fileSize: 3.2, 
        status: 'Completed', 
        year: 2025, 
        month: 10,
        timeframe: { start: '2025-10-01', end: '2025-10-31' },
        recordCount: 2103,
        filters: { transactionType: 'Purchase', includeRefunds: 'Yes' },
        recurrence: { frequency: 'monthly', time: '06:00', nextRun: '2025-11-12 06:00:00', recipients: ['finance@company.com'] },
        expiresAt: '2026-01-10 11:20:33',
        errorReason: null,
        auditTrail: [
            { type: 'generated', user: 'Mike Johnson', timestamp: '2025-10-12 11:20:33', details: 'Report generated successfully' },
            { type: 'scheduled', user: 'Mike Johnson', timestamp: '2025-10-12 11:25:00', details: 'Monthly schedule created (12th of each month at 06:00 UTC)' },
            { type: 'downloaded', user: 'Mike Johnson', timestamp: '2025-10-12 11:30:15', details: null },
            { type: 'downloaded', user: 'Jane Doe', timestamp: '2025-10-14 08:45:22', details: null },
            { type: 'schedule-paused', user: 'Mike Johnson', timestamp: '2025-11-01 09:00:00', details: 'Schedule paused for review' },
            { type: 'schedule-resumed', user: 'Mike Johnson', timestamp: '2025-11-05 14:30:00', details: 'Schedule resumed' }
        ]
    },
    { 
        id: 5, 
        reportName: 'Message Logs – Jun 2024 – 080011', 
        module: 'message_logs', 
        subAccount: 'Sub Account C', 
        generatedBy: 'System', 
        dateGenerated: '2024-06-10 08:00:11', 
        fileType: 'CSV', 
        fileSize: 0, 
        status: 'Failed', 
        year: 2024, 
        month: 6,
        timeframe: { start: '2024-06-01', end: '2024-06-30' },
        recordCount: 0,
        filters: { channel: 'RCS', status: 'Failed' },
        recurrence: null,
        expiresAt: null,
        errorReason: 'Database connection timeout after 30 seconds. The query exceeded maximum execution time due to large dataset size.',
        auditTrail: [
            { type: 'generated', user: 'System', timestamp: '2024-06-10 08:00:11', details: 'Report generation failed - Database connection timeout' }
        ]
    },
    { 
        id: 6, 
        reportName: 'Message Logs – Jan 01-06 2026 – 150002', 
        module: 'message_logs', 
        subAccount: 'Main Account', 
        generatedBy: 'John Smith', 
        dateGenerated: '2026-01-06 15:00:02', 
        fileType: 'CSV', 
        fileSize: 0, 
        status: 'Completed', 
        year: 2026, 
        month: 1,
        timeframe: { start: '2026-01-01', end: '2026-01-06' },
        recordCount: 0,
        filters: {},
        recurrence: null,
        expiresAt: '2026-04-06 15:00:02',
        errorReason: null,
        auditTrail: [
            { type: 'generated', user: 'John Smith', timestamp: '2026-01-06 15:00:02', details: 'Report generated successfully' }
        ]
    },
    { 
        id: 7, 
        reportName: 'Finance Data – Dec 2025 – 101530', 
        module: 'finance_data', 
        subAccount: 'Sub Account B', 
        generatedBy: 'Jane Doe', 
        dateGenerated: '2025-12-20 10:15:30', 
        fileType: 'XLSX', 
        fileSize: 4.5, 
        status: 'Completed', 
        year: 2025, 
        month: 12,
        timeframe: { start: '2025-12-01', end: '2025-12-31' },
        recordCount: 1856,
        filters: { transactionType: 'All', currency: 'GBP' },
        recurrence: null,
        expiresAt: '2026-03-20 10:15:30',
        errorReason: null,
        auditTrail: [
            { type: 'generated', user: 'Jane Doe', timestamp: '2025-12-20 10:15:30', details: 'Report generated successfully' },
            { type: 'downloaded', user: 'Jane Doe', timestamp: '2025-12-20 10:20:45', details: null },
            { type: 'downloaded', user: 'John Smith', timestamp: '2025-12-22 14:30:00', details: null },
            { type: 'downloaded', user: 'Mike Johnson', timestamp: '2025-12-28 09:15:22', details: null }
        ]
    },
    { 
        id: 8, 
        reportName: 'Message Logs – Full Year 2024 – 235959', 
        module: 'message_logs', 
        subAccount: 'Main Account', 
        generatedBy: 'Mike Johnson', 
        dateGenerated: '2024-12-31 23:59:59', 
        fileType: 'CSV', 
        fileSize: 0, 
        status: 'Failed', 
        year: 2024, 
        month: 12,
        timeframe: { start: '2024-01-01', end: '2024-12-31' },
        recordCount: 0,
        filters: { channel: 'All Channels', status: 'All Statuses', direction: 'All' },
        recurrence: null,
        expiresAt: null,
        errorReason: 'Memory limit exceeded. The export contained over 500,000 records which exceeded the maximum allowed memory allocation.',
        auditTrail: [
            { type: 'generated', user: 'Mike Johnson', timestamp: '2024-12-31 23:59:59', details: 'Report generation failed - Memory limit exceeded' }
        ]
    },
    { 
        id: 9, 
        reportName: 'Finance Data – Jul 2025 – 083012', 
        module: 'finance_data', 
        subAccount: 'Main Account', 
        generatedBy: 'System', 
        dateGenerated: '2025-07-15 08:30:12', 
        fileType: 'XLSX', 
        fileSize: 2.1, 
        status: 'Expired', 
        year: 2025, 
        month: 7,
        timeframe: { start: '2025-07-01', end: '2025-07-31' },
        recordCount: 1203,
        filters: { transactionType: 'All' },
        recurrence: null,
        expiresAt: '2025-10-13 08:30:12',
        errorReason: null,
        auditTrail: [
            { type: 'generated', user: 'System', timestamp: '2025-07-15 08:30:12', details: 'Report generated successfully (scheduled run)' },
            { type: 'downloaded', user: 'Jane Doe', timestamp: '2025-07-15 09:00:15', details: null },
            { type: 'downloaded', user: 'Mike Johnson', timestamp: '2025-08-01 10:30:00', details: null }
        ]
    }
];

var mockSubAccounts = [
    { id: 'main', name: 'Main Account' },
    { id: 'sub_a', name: 'Sub Account A' },
    { id: 'sub_b', name: 'Sub Account B' },
    { id: 'sub_c', name: 'Sub Account C' },
    { id: 'sub_d', name: 'Sub Account D' }
];

var mockUsers = [
    { id: 'john', name: 'John Smith' },
    { id: 'jane', name: 'Jane Doe' },
    { id: 'mike', name: 'Mike Johnson' },
    { id: 'system', name: 'System' }
];

var selectedIds = [];
var currentPage = 1;
var pageSize = 25;
var selectedSubAccounts = [];
var sortColumn = 'dateGenerated';
var sortDirection = 'desc';
var openActionMenuId = null;

var appliedFilters = {
    year: new Date().getFullYear().toString(),
    month: '',
    module: '',
    subAccounts: [],
    users: []
};

var pendingFilters = {
    year: new Date().getFullYear().toString(),
    month: '',
    module: '',
    subAccounts: [],
    users: []
};

document.addEventListener('DOMContentLoaded', function() {
    initializeFilters();
    renderDownloads();
    
    document.addEventListener('click', function(e) {
        var dropdown = document.getElementById('subAccountDropdown');
        var trigger = document.getElementById('subAccountDropdownTrigger');
        if (!dropdown.contains(e.target) && !trigger.contains(e.target)) {
            dropdown.classList.remove('show');
        }
        
        if (!e.target.closest('.action-menu')) {
            closeAllActionMenus();
        }
    });
});

var isLoading = false;
var renderDebounceTimer = null;
var LARGE_FILE_THRESHOLD_MB = 100;
var MAX_RECORDS_CLIENT_SIDE = 1000;

function showLoadingState() {
    var tbody = document.getElementById('downloadsTableBody');
    tbody.innerHTML = '<tr><td colspan="10"><div class="loading-state"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="text-muted mb-0">Loading reports...</p></div></td></tr>';
}

function renderDownloads() {
    if (renderDebounceTimer) {
        clearTimeout(renderDebounceTimer);
    }
    
    renderDebounceTimer = setTimeout(function() {
        renderDownloadsInternal();
    }, 50);
}

function renderDownloadsInternal() {
    var tbody = document.getElementById('downloadsTableBody');
    var filtered = applyClientFilters(mockDownloads);
    var sorted = sortData(filtered);
    var paginated = paginateData(sorted);
    
    if (filtered.length === 0) {
        tbody.innerHTML = '<tr><td colspan="10"><div class="empty-state"><i class="fas fa-folder-open"></i><h5>No downloads available</h5><p class="text-muted">Generated reports and exports will appear here.</p></div></td></tr>';
        document.getElementById('showingCount').textContent = '0';
        document.getElementById('totalCount').textContent = '0';
        renderPagination(0);
        return;
    }
    
    var html = '';
    paginated.forEach(function(item) {
        var isSelected = selectedIds.includes(item.id);
        var statusClass = getStatusClass(item.status);
        var rowClass = getRowClass(item.status);
        var fileSizeDisplay = formatFileSize(item.fileSize, true);
        var statusDisplay = formatStatusDisplay(item.status, item.expiresAt);
        
        html += '<tr data-id="' + item.id + '" class="' + rowClass + '">';
        html += '<td><input type="checkbox" class="form-check-input row-select" ' + (isSelected ? 'checked' : '') + ' onchange="toggleRowSelect(' + item.id + ')"></td>';
        html += '<td>' + item.reportName + '</td>';
        html += '<td>' + formatModuleName(item.module) + '</td>';
        html += '<td>' + item.subAccount + '</td>';
        html += '<td>' + item.generatedBy + '</td>';
        html += '<td>' + formatDateTime(item.dateGenerated) + '</td>';
        html += '<td>' + item.fileType + '</td>';
        html += '<td>' + fileSizeDisplay + '</td>';
        html += '<td>' + getStatusPill(item.status) + '</td>';
        html += '<td>';
        html += '<div class="action-menu">';
        html += '<button class="action-menu-btn" onclick="toggleActionMenu(' + item.id + ', event)" title="Actions"><i class="fas fa-ellipsis-v"></i></button>';
        html += '<div class="action-dropdown" id="actionMenu_' + item.id + '">';
        html += '<button class="action-dropdown-item" onclick="viewReportDetails(' + item.id + ')"><i class="fas fa-info-circle me-2"></i>View Report Details</button>';
        if (item.status === 'Completed') {
            html += '<button class="action-dropdown-item" onclick="downloadReport(' + item.id + ')"><i class="fas fa-download me-2"></i>Download Report</button>';
        }
        html += '<button class="action-dropdown-item" onclick="scheduleRecurring(' + item.id + ')"><i class="fas fa-clock me-2"></i>Schedule Recurring Report</button>';
        html += '<div class="dropdown-divider"></div>';
        html += '<button class="action-dropdown-item text-danger" onclick="deleteReport(' + item.id + ')"><i class="fas fa-trash me-2"></i>Delete</button>';
        html += '</div>';
        html += '</div>';
        html += '</td>';
        html += '</tr>';
    });
    
    tbody.innerHTML = html;
    
    var startIdx = (currentPage - 1) * pageSize + 1;
    var endIdx = Math.min(currentPage * pageSize, filtered.length);
    document.getElementById('showingCount').textContent = startIdx + '-' + endIdx;
    document.getElementById('totalCount').textContent = filtered.length;
    
    renderPagination(filtered.length);
    updateBulkActions();
    updateSortIndicators();
}

function sortData(data) {
    return data.slice().sort(function(a, b) {
        var valA = a[sortColumn];
        var valB = b[sortColumn];
        
        if (sortColumn === 'fileSize') {
            valA = parseFloat(valA) || 0;
            valB = parseFloat(valB) || 0;
        } else if (sortColumn === 'dateGenerated') {
            valA = new Date(valA).getTime();
            valB = new Date(valB).getTime();
        } else if (typeof valA === 'string') {
            valA = valA.toLowerCase();
            valB = valB.toLowerCase();
        }
        
        if (valA < valB) return sortDirection === 'asc' ? -1 : 1;
        if (valA > valB) return sortDirection === 'asc' ? 1 : -1;
        return 0;
    });
}

function paginateData(data) {
    var start = (currentPage - 1) * pageSize;
    var end = start + pageSize;
    return data.slice(start, end);
}

function sortTable(column) {
    if (sortColumn === column) {
        sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
    } else {
        sortColumn = column;
        sortDirection = 'asc';
    }
    currentPage = 1;
    renderDownloads();
}

function updateSortIndicators() {
    document.querySelectorAll('.sortable').forEach(function(th) {
        th.classList.remove('sort-asc', 'sort-desc');
        var icon = th.querySelector('i');
        icon.className = 'fas fa-sort text-muted ms-1';
    });
    
    var activeHeader = document.querySelector('.sortable[data-sort="' + sortColumn + '"]');
    if (activeHeader) {
        activeHeader.classList.add(sortDirection === 'asc' ? 'sort-asc' : 'sort-desc');
        var icon = activeHeader.querySelector('i');
        icon.className = 'fas fa-sort-' + (sortDirection === 'asc' ? 'up' : 'down') + ' ms-1';
    }
}

function renderPagination(totalItems) {
    var totalPages = Math.ceil(totalItems / pageSize);
    var pagination = document.getElementById('pagination');
    
    if (totalPages <= 1) {
        pagination.innerHTML = '';
        return;
    }
    
    var html = '';
    html += '<li class="page-item ' + (currentPage === 1 ? 'disabled' : '') + '">';
    html += '<a class="page-link" href="#" onclick="goToPage(' + (currentPage - 1) + '); return false;">&laquo;</a></li>';
    
    var startPage = Math.max(1, currentPage - 2);
    var endPage = Math.min(totalPages, startPage + 4);
    if (endPage - startPage < 4) startPage = Math.max(1, endPage - 4);
    
    for (var i = startPage; i <= endPage; i++) {
        html += '<li class="page-item ' + (currentPage === i ? 'active' : '') + '">';
        html += '<a class="page-link" href="#" onclick="goToPage(' + i + '); return false;">' + i + '</a></li>';
    }
    
    html += '<li class="page-item ' + (currentPage === totalPages ? 'disabled' : '') + '">';
    html += '<a class="page-link" href="#" onclick="goToPage(' + (currentPage + 1) + '); return false;">&raquo;</a></li>';
    
    pagination.innerHTML = html;
}

function goToPage(page) {
    var totalPages = Math.ceil(applyClientFilters(mockDownloads).length / pageSize);
    if (page < 1 || page > totalPages) return;
    currentPage = page;
    renderDownloads();
}

function formatFileSize(sizeMB, showWarning) {
    var sizeText = '';
    if (sizeMB >= 1000) {
        sizeText = (sizeMB / 1000).toFixed(1) + ' GB';
    } else if (sizeMB >= 1) {
        sizeText = sizeMB.toFixed(1) + ' MB';
    } else if (sizeMB > 0) {
        sizeText = (sizeMB * 1024).toFixed(0) + ' KB';
    } else {
        return '—';
    }
    
    if (showWarning && sizeMB >= LARGE_FILE_THRESHOLD_MB) {
        return '<span class="large-file-indicator">' + sizeText + ' <i class="fas fa-exclamation-triangle file-size-warning" title="Large file - download may take longer"></i></span>';
    }
    return sizeText;
}

function formatFileSizeText(sizeMB) {
    if (sizeMB >= 1000) {
        return (sizeMB / 1000).toFixed(1) + ' GB';
    } else if (sizeMB >= 1) {
        return sizeMB.toFixed(1) + ' MB';
    } else if (sizeMB > 0) {
        return (sizeMB * 1024).toFixed(0) + ' KB';
    }
    return '—';
}

function getStatusClass(status) {
    switch(status) {
        case 'Completed': return 'status-text-completed';
        case 'Failed': return 'status-text-failed';
        case 'Expired': return 'status-text-expired';
        default: return '';
    }
}

function getStatusPill(status) {
    var pillClass = 'status-pill ';
    switch(status) {
        case 'Completed': pillClass += 'status-pill-completed'; break;
        case 'Failed': pillClass += 'status-pill-failed'; break;
        case 'Expired': pillClass += 'status-pill-expired'; break;
        case 'Processing': pillClass += 'status-pill-processing'; break;
        default: pillClass += 'status-pill-completed';
    }
    return '<span class="' + pillClass + '">' + status + '</span>';
}

function getRowClass(status) {
    switch(status) {
        case 'Failed': return 'row-failed';
        case 'Expired': return 'row-expired';
        default: return '';
    }
}

function formatStatusDisplay(status, expiresAt) {
    if (status === 'Expired') {
        return '<span class="expired-badge"><i class="fas fa-archive me-1"></i>Expired</span>';
    } else if (status === 'Failed') {
        return '<span class="failed-badge"><i class="fas fa-times-circle me-1"></i>Failed</span>';
    }
    return status;
}

function isExpiringSoon(expiresAt) {
    if (!expiresAt) return false;
    var expiry = new Date(expiresAt);
    var now = new Date();
    var daysUntil = Math.ceil((expiry - now) / (1000 * 60 * 60 * 24));
    return daysUntil <= 14 && daysUntil > 0;
}

function getDaysUntilExpiry(expiresAt) {
    if (!expiresAt) return null;
    var expiry = new Date(expiresAt);
    var now = new Date();
    return Math.ceil((expiry - now) / (1000 * 60 * 60 * 24));
}

function getModuleUrl(module) {
    var urls = {
        'message_logs': '/reporting/message-log',
        'finance_data': '/reporting/finance-data'
    };
    return urls[module] || '/reporting/dashboard';
}

function retryFromModule(event) {
    event.preventDefault();
    if (currentDetailReportId) {
        var report = mockDownloads.find(function(d) { return d.id === currentDetailReportId; });
        if (report) {
            var url = getModuleUrl(report.module);
            console.log('TODO: Navigate to module for retry:', url);
            console.log('TODO: Pre-populate filters from failed export');
            alert('Navigating to ' + formatModuleName(report.module) + ' to create a new export...');
            closeDetailsDrawer();
        }
    }
}

function regenerateFromModule(event) {
    event.preventDefault();
    if (currentDetailReportId) {
        var report = mockDownloads.find(function(d) { return d.id === currentDetailReportId; });
        if (report) {
            var url = getModuleUrl(report.module);
            console.log('TODO: Navigate to module for regeneration:', url);
            console.log('TODO: Pre-populate filters from expired export');
            alert('Navigating to ' + formatModuleName(report.module) + ' to generate a new export with the same filters...');
            closeDetailsDrawer();
        }
    }
}

function formatDateTime(dateStr) {
    var date = new Date(dateStr);
    var day = String(date.getDate()).padStart(2, '0');
    var month = String(date.getMonth() + 1).padStart(2, '0');
    var year = date.getFullYear();
    var hours = String(date.getHours()).padStart(2, '0');
    var mins = String(date.getMinutes()).padStart(2, '0');
    return day + '/' + month + '/' + year + ' ' + hours + ':' + mins;
}

function toggleActionMenu(id, event) {
    event.stopPropagation();
    var menu = document.getElementById('actionMenu_' + id);
    var isOpen = menu.classList.contains('show');
    
    closeAllActionMenus();
    
    if (!isOpen) {
        menu.classList.add('show');
        openActionMenuId = id;
    }
}

function closeAllActionMenus() {
    document.querySelectorAll('.action-dropdown.show').forEach(function(menu) {
        menu.classList.remove('show');
    });
    openActionMenuId = null;
}

var currentDetailReportId = null;

function formatDateRange(start, end) {
    var startDate = new Date(start);
    var endDate = new Date(end);
    var options = { day: '2-digit', month: 'short', year: 'numeric' };
    return startDate.toLocaleDateString('en-GB', options) + ' – ' + endDate.toLocaleDateString('en-GB', options);
}

function formatFilterName(key) {
    var names = {
        'channel': 'Channel',
        'status': 'Status',
        'direction': 'Direction',
        'transactionType': 'Transaction Type',
        'costCenter': 'Cost Center',
        'includeRefunds': 'Include Refunds',
        'currency': 'Currency'
    };
    return names[key] || key.replace(/([A-Z])/g, ' $1').replace(/^./, function(str) { return str.toUpperCase(); });
}

function formatFrequency(frequency) {
    var frequencies = {
        'daily': 'Daily',
        'weekly': 'Weekly',
        'monthly': 'Monthly',
        'quarterly': 'Quarterly'
    };
    return frequencies[frequency] || frequency;
}

function viewReportDetails(id) {
    closeAllActionMenus();
    var report = mockDownloads.find(function(d) { return d.id === id; });
    if (!report) return;
    
    currentDetailReportId = id;
    
    document.getElementById('detailReportName').textContent = report.reportName;
    document.getElementById('detailReportId').textContent = 'RPT-' + String(id).padStart(6, '0');
    document.getElementById('detailModule').textContent = formatModuleName(report.module);
    document.getElementById('detailSubAccount').textContent = report.subAccount;
    document.getElementById('detailGeneratedBy').textContent = report.generatedBy;
    document.getElementById('detailDateGenerated').textContent = formatDateTime(report.dateGenerated);
    
    if (report.timeframe && report.timeframe.start && report.timeframe.end) {
        document.getElementById('detailTimeframe').textContent = formatDateRange(report.timeframe.start, report.timeframe.end);
    } else {
        document.getElementById('detailTimeframe').textContent = '—';
    }
    
    document.getElementById('detailRecordCount').textContent = report.recordCount > 0 ? report.recordCount.toLocaleString() + ' records' : '0 records';
    
    var filtersContainer = document.getElementById('detailFiltersApplied');
    var filterKeys = report.filters ? Object.keys(report.filters) : [];
    if (filterKeys.length > 0) {
        var filtersHtml = '';
        filterKeys.forEach(function(key) {
            filtersHtml += '<div class="detail-filter-item">';
            filtersHtml += '<span class="detail-filter-name">' + formatFilterName(key) + '</span>';
            filtersHtml += '<span class="detail-filter-value">' + report.filters[key] + '</span>';
            filtersHtml += '</div>';
        });
        filtersContainer.innerHTML = filtersHtml;
    } else {
        filtersContainer.innerHTML = '<div class="text-muted small">No filters applied</div>';
    }
    
    document.getElementById('detailFileType').textContent = report.fileType;
    document.getElementById('detailFileSize').textContent = report.fileSize > 0 ? formatFileSize(report.fileSize) : '—';
    document.getElementById('detailStatus').textContent = report.status;
    document.getElementById('detailStatus').className = 'detail-value ' + getStatusClass(report.status);
    
    document.getElementById('detailRetention').textContent = retentionDays + ' days';
    var expiryInfo = document.getElementById('detailExpiryInfo');
    if (report.expiresAt && report.status === 'Completed') {
        var daysLeft = getDaysUntilExpiry(report.expiresAt);
        expiryInfo.style.display = 'flex';
        if (daysLeft <= 0) {
            expiryInfo.className = 'expiry-info expired';
            document.getElementById('detailExpiryText').textContent = 'Expired on ' + formatDateTime(report.expiresAt).split(' ')[0];
        } else if (daysLeft <= 14) {
            expiryInfo.className = 'expiry-info expiring-soon';
            document.getElementById('detailExpiryText').textContent = 'Expires in ' + daysLeft + ' days';
        } else {
            expiryInfo.className = 'expiry-info';
            document.getElementById('detailExpiryText').textContent = 'Expires on ' + formatDateTime(report.expiresAt).split(' ')[0];
        }
    } else {
        expiryInfo.style.display = 'none';
    }
    
    var errorSection = document.getElementById('detailErrorSection');
    if (report.status === 'Failed' && report.errorReason) {
        errorSection.style.display = 'block';
        document.getElementById('detailErrorReason').textContent = report.errorReason;
    } else {
        errorSection.style.display = 'none';
    }
    
    var expiredSection = document.getElementById('detailExpiredSection');
    if (report.status === 'Expired') {
        expiredSection.style.display = 'block';
    } else {
        expiredSection.style.display = 'none';
    }
    
    var recurrenceSection = document.getElementById('detailRecurrenceSection');
    if (report.recurrence) {
        recurrenceSection.style.display = 'block';
        document.getElementById('detailRecurrenceFrequency').textContent = formatFrequency(report.recurrence.frequency);
        document.getElementById('detailRecurrenceNext').textContent = report.recurrence.nextRun ? formatDateTime(report.recurrence.nextRun) : '—';
        document.getElementById('detailRecurrenceRecipients').textContent = report.recurrence.recipients ? report.recurrence.recipients.join(', ') : '—';
    } else {
        recurrenceSection.style.display = 'none';
    }
    
    var downloadBtn = document.getElementById('detailDownloadBtn');
    if (report.status === 'Completed') {
        downloadBtn.disabled = false;
        downloadBtn.classList.remove('btn-secondary');
        downloadBtn.classList.add('btn-primary');
        downloadBtn.innerHTML = '<i class="fas fa-download me-1"></i>Download Report';
    } else if (report.status === 'Expired') {
        downloadBtn.disabled = true;
        downloadBtn.classList.remove('btn-primary');
        downloadBtn.classList.add('btn-secondary');
        downloadBtn.innerHTML = '<i class="fas fa-archive me-1"></i>File Expired';
    } else {
        downloadBtn.disabled = true;
        downloadBtn.classList.remove('btn-primary');
        downloadBtn.classList.add('btn-secondary');
        downloadBtn.innerHTML = '<i class="fas fa-times me-1"></i>Not Available';
    }
    
    renderAuditTrail(report.auditTrail);
    
    document.getElementById('detailsDrawerBackdrop').classList.add('show');
    document.getElementById('detailsDrawer').classList.add('show');
    
    console.log('TODO: API call - GET /api/downloads/' + id + '/details');
    console.log('TODO: API call - GET /api/downloads/' + id + '/audit-trail');
}

function renderAuditTrail(auditTrail) {
    var container = document.getElementById('detailAuditTrail');
    
    if (!auditTrail || auditTrail.length === 0) {
        container.innerHTML = '<div class="audit-trail-empty"><i class="fas fa-history me-1"></i>No audit history available</div>';
        return;
    }
    
    var html = '<div class="audit-read-only-badge"><i class="fas fa-lock"></i>Read-only audit log</div>';
    
    var sortedAudit = auditTrail.slice().sort(function(a, b) {
        return new Date(b.timestamp) - new Date(a.timestamp);
    });
    
    sortedAudit.forEach(function(entry) {
        html += '<div class="audit-entry">';
        html += '<div class="audit-entry-icon ' + entry.type + '">' + getAuditIcon(entry.type) + '</div>';
        html += '<div class="audit-entry-content">';
        html += '<div class="audit-entry-action">' + getAuditActionText(entry.type, entry.details) + '</div>';
        html += '<div class="audit-entry-user"><i class="fas fa-user me-1"></i>' + entry.user + '</div>';
        html += '<div class="audit-entry-timestamp"><i class="fas fa-clock me-1"></i>' + formatDateTime(entry.timestamp) + '</div>';
        html += '</div>';
        html += '</div>';
    });
    
    container.innerHTML = html;
}

function getAuditIcon(type) {
    var icons = {
        'generated': '<i class="fas fa-file-alt"></i>',
        'downloaded': '<i class="fas fa-download"></i>',
        'scheduled': '<i class="fas fa-calendar-plus"></i>',
        'schedule-changed': '<i class="fas fa-edit"></i>',
        'schedule-paused': '<i class="fas fa-pause"></i>',
        'schedule-resumed': '<i class="fas fa-play"></i>'
    };
    return icons[type] || '<i class="fas fa-circle"></i>';
}

function getAuditActionText(type, details) {
    var actions = {
        'generated': 'Report generated',
        'downloaded': 'Report downloaded',
        'scheduled': 'Schedule created',
        'schedule-changed': 'Schedule modified',
        'schedule-paused': 'Schedule paused',
        'schedule-resumed': 'Schedule resumed'
    };
    var text = actions[type] || 'Action performed';
    if (details) {
        text += ' — ' + details;
    }
    return text;
}

function closeDetailsDrawer() {
    document.getElementById('detailsDrawerBackdrop').classList.remove('show');
    document.getElementById('detailsDrawer').classList.remove('show');
    currentDetailReportId = null;
}

function downloadFromDetails() {
    if (currentDetailReportId) {
        downloadReport(currentDetailReportId);
    }
}

function scheduleFromDetails() {
    if (currentDetailReportId) {
        closeDetailsDrawer();
        scheduleRecurring(currentDetailReportId);
    }
}

function downloadReport(id) {
    closeAllActionMenus();
    var report = mockDownloads.find(function(d) { return d.id === id; });
    if (!report || report.status !== 'Completed') {
        alert('This report is not available for download.');
        return;
    }
    
    console.log('TODO: Download original file for report ID:', id);
    console.log('TODO: API call - GET /api/downloads/' + id + '/file');
    console.log('File reference:', report.reportName + '.' + report.fileType.toLowerCase());
    
    alert('Download started for: ' + report.reportName);
}

var currentScheduleReport = null;

function scheduleRecurring(id) {
    closeAllActionMenus();
    var report = mockDownloads.find(function(d) { return d.id === id; });
    if (!report) return;
    
    currentScheduleReport = report;
    
    document.getElementById('scheduleReportId').value = id;
    document.getElementById('scheduleReportName').value = report.reportName;
    document.getElementById('scheduleFrequency').value = 'weekly';
    document.getElementById('scheduleDayOfWeek').value = '1';
    document.getElementById('scheduleDayOfMonth').value = '1';
    document.getElementById('scheduleTime').value = '09:00';
    document.getElementById('scheduleTimezone').value = 'Europe/London';
    document.getElementById('scheduleRecipients').value = '';
    document.getElementById('deliveryEmail').checked = false;
    document.getElementById('emailRecipientsGroup').style.display = 'none';
    
    onFrequencyChange();
    updateFiltersPreview(report);
    updateNamingPreview(report);
    
    var modal = new bootstrap.Modal(document.getElementById('scheduleRecurringModal'));
    modal.show();
    
    console.log('TODO: Load existing schedule if any for report ID:', id);
}

function updateFiltersPreview(report) {
    var filtersContainer = document.getElementById('scheduleFiltersPreview');
    var filterKeys = report.filters ? Object.keys(report.filters) : [];
    
    if (filterKeys.length > 0) {
        var html = '';
        filterKeys.forEach(function(key) {
            html += '<div class="filter-item">';
            html += '<span class="filter-name">' + formatFilterName(key) + '</span>';
            html += '<span class="filter-value">' + report.filters[key] + '</span>';
            html += '</div>';
        });
        filtersContainer.innerHTML = html;
    } else {
        filtersContainer.innerHTML = '<div class="text-muted small">No filters applied - all data will be included</div>';
    }
}

function updateNamingPreview(report) {
    var modulePrefix = report.module === 'message_logs' ? 'Message Logs' : 'Finance Data';
    var nextDate = getNextRunDate();
    var timestamp = document.getElementById('scheduleTime').value.replace(':', '') + '00';
    var example = modulePrefix + ' – ' + nextDate + ' – ' + timestamp;
    document.getElementById('namingExample').textContent = example;
}

function getNextRunDate() {
    var frequency = document.getElementById('scheduleFrequency').value;
    var now = new Date();
    var nextRun = new Date(now);
    
    if (frequency === 'daily') {
        nextRun.setDate(nextRun.getDate() + 1);
    } else if (frequency === 'weekly') {
        var targetDay = parseInt(document.getElementById('scheduleDayOfWeek').value);
        var currentDay = nextRun.getDay();
        var daysUntil = (targetDay - currentDay + 7) % 7;
        if (daysUntil === 0) daysUntil = 7;
        nextRun.setDate(nextRun.getDate() + daysUntil);
    } else if (frequency === 'monthly') {
        var targetDayOfMonth = document.getElementById('scheduleDayOfMonth').value;
        nextRun.setMonth(nextRun.getMonth() + 1);
        if (targetDayOfMonth === 'last') {
            nextRun = new Date(nextRun.getFullYear(), nextRun.getMonth() + 1, 0);
        } else {
            nextRun.setDate(parseInt(targetDayOfMonth));
        }
    }
    
    var options = { day: '2-digit', month: 'short', year: 'numeric' };
    return nextRun.toLocaleDateString('en-GB', options).replace(/ /g, ' ');
}

function onFrequencyChange() {
    var frequency = document.getElementById('scheduleFrequency').value;
    document.getElementById('weeklyOptions').style.display = frequency === 'weekly' ? 'block' : 'none';
    document.getElementById('monthlyOptions').style.display = frequency === 'monthly' ? 'block' : 'none';
    
    if (currentScheduleReport) {
        updateNamingPreview(currentScheduleReport);
    }
}

function toggleEmailRecipients() {
    var emailChecked = document.getElementById('deliveryEmail').checked;
    document.getElementById('emailRecipientsGroup').style.display = emailChecked ? 'block' : 'none';
}

function saveSchedule() {
    var reportId = document.getElementById('scheduleReportId').value;
    var frequency = document.getElementById('scheduleFrequency').value;
    var dayOfWeek = document.getElementById('scheduleDayOfWeek').value;
    var dayOfMonth = document.getElementById('scheduleDayOfMonth').value;
    var time = document.getElementById('scheduleTime').value;
    var timezone = document.getElementById('scheduleTimezone').value;
    var emailEnabled = document.getElementById('deliveryEmail').checked;
    var recipients = document.getElementById('scheduleRecipients').value;
    
    var scheduleData = {
        reportId: reportId,
        frequency: frequency,
        dayOfWeek: frequency === 'weekly' ? parseInt(dayOfWeek) : null,
        dayOfMonth: frequency === 'monthly' ? dayOfMonth : null,
        time: time,
        timezone: timezone,
        deliveryMethods: ['download_area'],
        recipients: emailEnabled ? recipients.split(',').map(function(e) { return e.trim(); }).filter(function(e) { return e; }) : []
    };
    
    console.log('TODO: Save schedule:', scheduleData);
    console.log('TODO: API call - POST /api/downloads/' + reportId + '/schedule');
    
    var originalReport = currentScheduleReport;
    if (originalReport) {
        var reportIdx = mockDownloads.findIndex(function(d) { return d.id === parseInt(reportId); });
        if (reportIdx > -1) {
            var nextRunDateStr = getNextRunDate();
            mockDownloads[reportIdx].recurrence = {
                frequency: frequency,
                dayOfWeek: frequency === 'weekly' ? parseInt(dayOfWeek) : null,
                dayOfMonth: frequency === 'monthly' ? dayOfMonth : null,
                time: time,
                timezone: timezone,
                nextRun: nextRunDateStr + ' ' + time + ':00',
                recipients: scheduleData.recipients
            };
        }
    }
    
    bootstrap.Modal.getInstance(document.getElementById('scheduleRecurringModal')).hide();
    alert('Schedule saved successfully! The next report will be generated on ' + getNextRunDate() + ' at ' + time + '.');
    currentScheduleReport = null;
}

function simulateScheduledRun(reportId) {
    var originalReport = mockDownloads.find(function(d) { return d.id === reportId; });
    if (!originalReport || !originalReport.recurrence) {
        console.log('Report has no schedule');
        return;
    }
    
    var now = new Date();
    var modulePrefix = originalReport.module === 'message_logs' ? 'Message Logs' : 'Finance Data';
    var dateStr = now.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
    var timeStr = String(now.getHours()).padStart(2, '0') + String(now.getMinutes()).padStart(2, '0') + String(now.getSeconds()).padStart(2, '0');
    var newReportName = modulePrefix + ' – ' + dateStr + ' – ' + timeStr;
    
    var newId = Math.max.apply(null, mockDownloads.map(function(d) { return d.id; })) + 1;
    
    var newReport = {
        id: newId,
        reportName: newReportName,
        module: originalReport.module,
        subAccount: originalReport.subAccount,
        generatedBy: 'System (Scheduled)',
        dateGenerated: now.toISOString().replace('T', ' ').substring(0, 19),
        fileType: originalReport.fileType,
        fileSize: Math.random() * 5 + 0.5,
        status: 'Completed',
        year: now.getFullYear(),
        month: now.getMonth() + 1,
        timeframe: originalReport.timeframe,
        recordCount: Math.floor(Math.random() * 2000) + 100,
        filters: JSON.parse(JSON.stringify(originalReport.filters)),
        recurrence: null,
        scheduledFromId: reportId
    };
    
    mockDownloads.unshift(newReport);
    renderDownloads();
    
    console.log('Simulated scheduled run created new report:', newReport);
    alert('Simulated scheduled run complete! New report "' + newReportName + '" has been added to the Download Area.');
}

function deleteReport(id) {
    closeAllActionMenus();
    document.getElementById('deleteCount').textContent = '1';
    selectedIds = [id];
    var modal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    modal.show();
}

var selectedUsers = [];

function initializeFilters() {
    var yearSelect = document.getElementById('filterYear');
    var currentYear = new Date().getFullYear();
    var yearHtml = '';
    for (var y = currentYear; y >= currentYear - 5; y--) {
        yearHtml += '<option value="' + y + '"' + (y === currentYear ? ' selected' : '') + '>' + y + '</option>';
    }
    yearSelect.innerHTML = yearHtml;
    
    renderUserOptions();
    renderSubAccountOptions();
    
    pendingFilters.year = currentYear.toString();
    appliedFilters.year = currentYear.toString();
}

function renderUserOptions() {
    var container = document.getElementById('userOptions');
    var html = '';
    mockUsers.forEach(function(user) {
        var isChecked = selectedUsers.includes(user.id);
        html += '<div class="form-check"><input class="form-check-input user-cb" type="checkbox" value="' + user.id + '" id="user_' + user.id + '" ' + (isChecked ? 'checked' : '') + ' onchange="updateUserSelection()"><label class="form-check-label small" for="user_' + user.id + '">' + user.name + '</label></div>';
    });
    container.innerHTML = html;
    updateUserDisplayText();
}

function selectAllUsers(e) {
    e.preventDefault();
    selectedUsers = mockUsers.map(function(u) { return u.id; });
    document.querySelectorAll('.user-cb').forEach(function(cb) { cb.checked = true; });
    updateUserDisplayText();
}

function clearAllUsers(e) {
    e.preventDefault();
    selectedUsers = [];
    document.querySelectorAll('.user-cb').forEach(function(cb) { cb.checked = false; });
    updateUserDisplayText();
}

function updateUserSelection() {
    selectedUsers = [];
    document.querySelectorAll('.user-cb:checked').forEach(function(cb) {
        selectedUsers.push(cb.value);
    });
    updateUserDisplayText();
}

function updateUserDisplayText() {
    var label = document.getElementById('userDropdownLabel');
    if (selectedUsers.length === 0 || selectedUsers.length === mockUsers.length) {
        label.textContent = 'All Users';
    } else if (selectedUsers.length === 1) {
        var user = mockUsers.find(function(u) { return u.id === selectedUsers[0]; });
        label.textContent = user ? user.name : '1 selected';
    } else {
        label.textContent = selectedUsers.length + ' selected';
    }
}

function renderSubAccountOptions() {
    var container = document.getElementById('subAccountOptions');
    var html = '';
    mockSubAccounts.forEach(function(acc) {
        var isChecked = selectedSubAccounts.includes(acc.id);
        html += '<div class="form-check mb-1 sub-account-option" data-name="' + acc.name.toLowerCase() + '">';
        html += '<input type="checkbox" class="form-check-input sub-account-cb" id="subAcc_' + acc.id + '" value="' + acc.id + '" ' + (isChecked ? 'checked' : '') + ' onchange="updateSubAccountSelection()">';
        html += '<label class="form-check-label small" for="subAcc_' + acc.id + '">' + acc.name + '</label>';
        html += '</div>';
    });
    container.innerHTML = html;
    updateSubAccountDisplayText();
}

function toggleSubAccountDropdown() {
    var dropdown = document.getElementById('subAccountDropdown');
    dropdown.classList.toggle('show');
}

function filterSubAccountOptions() {
    var search = document.getElementById('subAccountSearch').value.toLowerCase();
    document.querySelectorAll('.sub-account-option').forEach(function(opt) {
        var name = opt.dataset.name;
        opt.style.display = name.includes(search) ? 'block' : 'none';
    });
}

function toggleAllSubAccounts() {
    var selectAll = document.getElementById('subAccountSelectAll').checked;
    selectedSubAccounts = selectAll ? mockSubAccounts.map(function(a) { return a.id; }) : [];
    document.querySelectorAll('.sub-account-cb').forEach(function(cb) {
        cb.checked = selectAll;
    });
    updateSubAccountDisplayText();
}

function updateSubAccountSelection() {
    selectedSubAccounts = [];
    document.querySelectorAll('.sub-account-cb:checked').forEach(function(cb) {
        selectedSubAccounts.push(cb.value);
    });
    document.getElementById('subAccountSelectAll').checked = selectedSubAccounts.length === mockSubAccounts.length;
    updateSubAccountDisplayText();
}

function updateSubAccountDisplayText() {
    var display = document.getElementById('subAccountDisplayText');
    if (selectedSubAccounts.length === 0) {
        display.textContent = 'All Sub-accounts';
    } else if (selectedSubAccounts.length === mockSubAccounts.length) {
        display.textContent = 'All Sub-accounts';
    } else if (selectedSubAccounts.length === 1) {
        var acc = mockSubAccounts.find(function(a) { return a.id === selectedSubAccounts[0]; });
        display.textContent = acc ? acc.name : '1 selected';
    } else {
        display.textContent = selectedSubAccounts.length + ' selected';
    }
}

function applyClientFilters(data) {
    return data.filter(function(item) {
        if (appliedFilters.year && item.year !== parseInt(appliedFilters.year)) return false;
        if (appliedFilters.month && item.month !== parseInt(appliedFilters.month)) return false;
        if (appliedFilters.module && item.module !== appliedFilters.module) return false;
        if (appliedFilters.subAccounts.length > 0) {
            var matchesSubAccount = appliedFilters.subAccounts.some(function(subId) {
                var acc = mockSubAccounts.find(function(a) { return a.id === subId; });
                return acc && item.subAccount === acc.name;
            });
            if (!matchesSubAccount) return false;
        }
        if (appliedFilters.users && appliedFilters.users.length > 0) {
            var matchesUser = appliedFilters.users.some(function(userId) {
                var user = mockUsers.find(function(u) { return u.id === userId; });
                return user && item.generatedBy === user.name;
            });
            if (!matchesUser) return false;
        }
        return true;
    });
}

function toggleFilters() {
    var panel = document.getElementById('filtersPanel');
    panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
}

function applyFilters() {
    pendingFilters.year = document.getElementById('filterYear').value;
    pendingFilters.month = document.getElementById('filterMonth').value;
    pendingFilters.module = document.getElementById('filterModule').value;
    pendingFilters.subAccounts = selectedSubAccounts.slice();
    pendingFilters.users = selectedUsers.slice();
    
    appliedFilters = JSON.parse(JSON.stringify(pendingFilters));
    
    console.log('TODO: API call - GET /api/downloads with filters:', appliedFilters);
    
    renderDownloads();
    updateFilterChips();
}

function resetFilters() {
    var currentYear = new Date().getFullYear();
    
    document.getElementById('filterYear').value = currentYear.toString();
    document.getElementById('filterMonth').value = '';
    document.getElementById('filterModule').value = '';
    
    selectedSubAccounts = [];
    document.querySelectorAll('.sub-account-cb').forEach(function(cb) { cb.checked = false; });
    document.getElementById('subAccountSelectAll').checked = false;
    updateSubAccountDisplayText();
    
    selectedUsers = [];
    document.querySelectorAll('.user-cb').forEach(function(cb) { cb.checked = false; });
    updateUserDisplayText();
    
    pendingFilters = {
        year: currentYear.toString(),
        month: '',
        module: '',
        subAccounts: [],
        users: []
    };
    appliedFilters = JSON.parse(JSON.stringify(pendingFilters));
    
    renderDownloads();
    document.getElementById('activeFiltersContainer').style.display = 'none';
}

function updateFilterChips() {
    var chips = [];
    var yearSelect = document.getElementById('filterYear');
    var monthSelect = document.getElementById('filterMonth');
    var moduleSelect = document.getElementById('filterModule');
    
    var currentYear = new Date().getFullYear().toString();
    if (appliedFilters.year && appliedFilters.year !== currentYear) {
        chips.push({ label: 'Year: ' + appliedFilters.year, field: 'filterYear', value: currentYear });
    }
    if (appliedFilters.month) {
        chips.push({ label: monthSelect.options[monthSelect.selectedIndex].text, field: 'filterMonth', value: '' });
    }
    if (appliedFilters.module) {
        chips.push({ label: moduleSelect.options[moduleSelect.selectedIndex].text, field: 'filterModule', value: '' });
    }
    if (appliedFilters.subAccounts.length > 0 && appliedFilters.subAccounts.length < mockSubAccounts.length) {
        chips.push({ label: appliedFilters.subAccounts.length + ' Sub-account(s)', field: 'subAccounts', value: '' });
    }
    if (appliedFilters.users && appliedFilters.users.length > 0 && appliedFilters.users.length < mockUsers.length) {
        chips.push({ label: appliedFilters.users.length + ' User(s)', field: 'users', value: '' });
    }
    
    if (chips.length === 0) {
        document.getElementById('activeFiltersContainer').style.display = 'none';
        return;
    }
    
    var html = '';
    chips.forEach(function(chip) {
        html += '<span class="filter-chip">' + chip.label + ' <span class="remove-chip" onclick="removeFilterChip(\'' + chip.field + '\', \'' + chip.value + '\')">&times;</span></span>';
    });
    document.getElementById('filterChips').innerHTML = html;
    document.getElementById('activeFiltersContainer').style.display = 'flex';
}

function removeFilterChip(field, defaultValue) {
    if (field === 'subAccounts') {
        selectedSubAccounts = [];
        document.querySelectorAll('.sub-account-cb').forEach(function(cb) { cb.checked = false; });
        document.getElementById('subAccountSelectAll').checked = false;
        updateSubAccountDisplayText();
        appliedFilters.subAccounts = [];
    } else if (field === 'users') {
        selectedUsers = [];
        document.querySelectorAll('.user-cb').forEach(function(cb) { cb.checked = false; });
        updateUserDisplayText();
        appliedFilters.users = [];
    } else {
        var el = document.getElementById(field);
        el.value = defaultValue;
        appliedFilters[field.replace('filter', '').toLowerCase()] = defaultValue;
    }
    renderDownloads();
    updateFilterChips();
}

function toggleSelectAll() {
    var checked = document.getElementById('selectAll').checked;
    var filtered = applyClientFilters(mockDownloads);
    selectedIds = checked ? filtered.map(function(d) { return d.id; }) : [];
    renderDownloads();
}

function toggleRowSelect(id) {
    var idx = selectedIds.indexOf(id);
    if (idx > -1) selectedIds.splice(idx, 1);
    else selectedIds.push(id);
    updateBulkActions();
}

function updateBulkActions() {
    var showBulk = selectedIds.length > 0;
    document.getElementById('bulkActions').style.display = showBulk ? 'flex' : 'none';
    document.getElementById('bulkActionsHeader').style.display = showBulk ? 'flex' : 'none';
}

function downloadSelected() {
    var completedIds = selectedIds.filter(function(id) {
        var report = mockDownloads.find(function(d) { return d.id === id; });
        return report && report.status === 'Completed';
    });
    
    if (completedIds.length === 0) {
        alert('No completed reports selected for download.');
        return;
    }
    
    console.log('TODO: Download selected files:', completedIds);
    console.log('TODO: API call - POST /api/downloads/bulk-download with IDs:', completedIds);
    alert('Downloading ' + completedIds.length + ' report(s)...');
}

function deleteSelected() {
    document.getElementById('deleteCount').textContent = selectedIds.length;
    var modal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    modal.show();
}

function confirmDelete() {
    console.log('TODO: Delete files:', selectedIds);
    console.log('TODO: API call - DELETE /api/downloads/bulk with IDs:', selectedIds);
    mockDownloads = mockDownloads.filter(function(d) { return !selectedIds.includes(d.id); });
    selectedIds = [];
    bootstrap.Modal.getInstance(document.getElementById('deleteConfirmModal')).hide();
    renderDownloads();
}

function refreshDownloads() {
    console.log('TODO: Refresh downloads list');
    console.log('TODO: API call - GET /api/downloads');
    renderDownloads();
}

function changePageSize() {
    pageSize = parseInt(document.getElementById('pageSize').value);
    currentPage = 1;
    renderDownloads();
}

function formatModuleName(module) {
    var moduleNames = {
        'message_logs': 'Message Logs',
        'finance_data': 'Finance Data'
    };
    return moduleNames[module] || module;
}

function capitalizeFirst(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}

</script>

{{--
================================================================================
PERFORMANCE & SCALABILITY TODOs (Non-Functional Requirements)
================================================================================

TODO: Server-Side Pagination
- Current implementation uses client-side filtering/sorting for demo purposes
- For production with years of data (10,000+ records), implement server-side:
  - API endpoint: GET /api/downloads?page=1&per_page=25&sort=dateGenerated&order=desc
  - Pass filters as query params: year, month, module, subAccounts[], user
  - Return: { data: [...], meta: { total, per_page, current_page, last_page } }

TODO: Large File Handling
- Files > 100MB show warning indicator (LARGE_FILE_THRESHOLD_MB = 100)
- For very large files (>1GB), consider:
  - Chunked downloads with progress indicator
  - Background download with notification when complete
  - Streaming response for better memory usage

TODO: Data Volume Optimization
- Year filter defaults to current year to reduce initial dataset
- Consider implementing:
  - Lazy loading for older years (load on scroll or filter change)
  - Archiving old data to cold storage after 2+ years
  - Summarized views for historical data (monthly aggregates)

TODO: Caching Strategy
- Cache filter dropdowns (sub-accounts, users) - rarely changes
- Cache download list with short TTL (30 seconds)
- Invalidate cache on new export creation or deletion

TODO: Search Performance
- For text search across report names:
  - Use database full-text search (PostgreSQL tsvector)
  - Index on report_name, module, sub_account, generated_by

TODO: Bulk Operations
- Limit bulk selection to 100 items to prevent timeout
- For bulk downloads > 10 files, create ZIP archive server-side
- Process bulk deletes in background queue

TODO: API Rate Limiting
- Limit filter applications to prevent spam
- Debounce implemented client-side (50ms)
- Add server-side rate limiting: 60 requests/minute per user

================================================================================
--}}
@endpush
