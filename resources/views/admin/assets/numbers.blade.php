@extends('layouts.admin')

@section('title', 'Global Numbers Library')

@push('styles')
<style>
.admin-page { padding: 1.5rem; }

#numbersTableBody .dropdown-menu {
    z-index: 9999 !important;
    min-width: 180px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    border: 1px solid #e2e8f0;
    border-radius: 6px;
}
#numbersTableBody .dropdown-menu.show {
    display: block !important;
    position: fixed !important;
}
#numbersTableBody .dropdown {
    position: static;
}
.action-dots-btn {
    background: transparent;
    border: none;
    padding: 0.25rem 0.5rem;
    cursor: pointer;
    color: #6c757d;
    position: relative;
}
.action-dots-btn:hover {
    color: var(--admin-primary, #1e3a5f);
}
.action-dots-btn:focus {
    outline: none;
    box-shadow: none;
}

.search-filter-toolbar {
    background: #fff;
    padding: 0.75rem 1rem;
    border-radius: 0.5rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
}
.search-filter-toolbar .input-group {
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    overflow: hidden;
}
.search-filter-toolbar .input-group .input-group-text,
.search-filter-toolbar .input-group .form-control,
.search-filter-toolbar .input-group .btn {
    border: none;
}
.search-filter-toolbar .form-control:focus {
    box-shadow: none;
}
.filter-pill-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: transparent;
    border: 1.5px solid #c5d3e0;
    color: var(--admin-primary, #1e3a5f);
    font-weight: 500;
    font-size: 0.875rem;
    padding: 0.5rem 1.25rem;
    border-radius: 50px;
    transition: all 0.2s;
    cursor: pointer;
}
.filter-pill-btn:hover {
    background: rgba(30, 58, 95, 0.05);
    border-color: var(--admin-primary, #1e3a5f);
    color: var(--admin-primary, #1e3a5f);
}
.filter-pill-btn.active {
    background: rgba(30, 58, 95, 0.08);
    border-color: var(--admin-primary, #1e3a5f);
    color: var(--admin-primary, #1e3a5f);
}
.filter-pill-btn i {
    font-size: 0.8rem;
    color: var(--admin-primary, #1e3a5f);
}
.filter-count-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: var(--admin-primary, #1e3a5f);
    color: #fff;
    font-size: 0.7rem;
    min-width: 18px;
    height: 18px;
    border-radius: 9px;
    margin-left: 0.25rem;
    padding: 0 5px;
}
.admin-filter-panel {
    background: #fff;
    border-radius: 0.5rem;
    margin-bottom: 1rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
}
.admin-filter-panel .filter-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 1.25rem;
    border-bottom: 1px solid #e9ecef;
    cursor: pointer;
}
.admin-filter-panel .filter-header h6 {
    margin: 0;
    font-weight: 600;
    color: var(--admin-primary);
    font-size: 0.9rem;
}
.admin-filter-panel .filter-header .toggle-icon {
    transition: transform 0.2s;
}
.admin-filter-panel .filter-header.collapsed .toggle-icon {
    transform: rotate(-90deg);
}
.admin-filter-panel .filter-body {
    padding: 1rem 1.25rem;
    background: #f8fafc;
}
.admin-filter-panel .filter-row {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1rem;
}
.admin-filter-panel .filter-group {
    display: flex;
    flex-direction: column;
    min-width: 160px;
    flex: 1;
    max-width: 200px;
}
.admin-filter-panel .filter-group.wide {
    min-width: 200px;
    max-width: 250px;
}
.admin-filter-panel .filter-group label {
    font-size: 0.75rem;
    font-weight: 600;
    color: #6c757d;
    margin-bottom: 0.375rem;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}
.admin-filter-panel .filter-actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
    padding-top: 0.75rem;
    border-top: 1px solid #e9ecef;
}

.multiselect-dropdown {
    position: relative;
}
.multiselect-dropdown .dropdown-toggle {
    background: #fff;
    border: 1px solid #ced4da;
    color: #495057;
    font-size: 0.85rem;
    padding: 0.375rem 0.75rem;
    width: 100%;
    text-align: left;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.multiselect-dropdown .dropdown-toggle:hover {
    border-color: var(--admin-accent);
}
.multiselect-dropdown .dropdown-toggle::after {
    margin-left: 0.5rem;
}
.multiselect-dropdown .dropdown-menu {
    max-height: 250px;
    overflow-y: auto;
    min-width: 100%;
    padding: 0.5rem;
}
.multiselect-dropdown .dropdown-menu .search-box {
    margin-bottom: 0.5rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #e9ecef;
}
.multiselect-dropdown .dropdown-menu .search-box input {
    font-size: 0.8rem;
    padding: 0.35rem 0.5rem;
}
.multiselect-dropdown .dropdown-menu .select-actions {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #e9ecef;
}
.multiselect-dropdown .dropdown-menu .select-actions a {
    font-size: 0.75rem;
    color: var(--admin-accent);
    text-decoration: none;
}
.multiselect-dropdown .dropdown-menu .select-actions a:hover {
    text-decoration: underline;
}
.multiselect-dropdown .form-check {
    padding: 0.25rem 0 0.25rem 1.75rem;
}
.multiselect-dropdown .form-check:hover {
    background: #f8f9fa;
    border-radius: 0.25rem;
}
.multiselect-dropdown .form-check-label {
    font-size: 0.8rem;
    cursor: pointer;
}
.multiselect-dropdown .selected-count {
    background: var(--admin-accent);
    color: #fff;
    font-size: 0.65rem;
    padding: 0.1rem 0.4rem;
    border-radius: 0.75rem;
    margin-left: 0.5rem;
}

.admin-btn-apply {
    background: var(--admin-primary);
    color: #fff;
    border: none;
    padding: 0.5rem 1.25rem;
    font-size: 0.85rem;
    font-weight: 500;
    border-radius: 0.375rem;
}
.admin-btn-apply:hover {
    background: var(--admin-secondary);
    color: #fff;
}
.admin-btn-reset {
    background: transparent;
    color: #6c757d;
    border: 1px solid #dee2e6;
    padding: 0.5rem 1rem;
    font-size: 0.85rem;
    font-weight: 500;
    border-radius: 0.375rem;
}
.admin-btn-reset:hover {
    background: #f8f9fa;
    color: #495057;
}

#numbersTable tbody tr td { padding: 0.5rem 0.75rem; vertical-align: middle; }
#numbersTable thead th { padding: 0.5rem 0.75rem; font-size: 0.8rem; font-weight: 600; color: #495057; background: #f8f9fa; }
#numbersTable tbody tr:hover { background: #f8f9fa; }

.number-value { 
    font-weight: 600; 
    color: var(--admin-primary); 
    white-space: nowrap;
}
.account-cell .account-name { 
    font-weight: 500; 
    color: #333; 
}
.account-cell .sub-account { 
    font-size: 0.75rem; 
    color: #6c757d; 
}

.sortable { cursor: pointer; user-select: none; position: relative; white-space: nowrap; }
.sortable:hover { background: #e9ecef; }
.sortable::after {
    content: '\f0dc';
    font-family: 'Font Awesome 6 Free', 'Font Awesome 5 Free';
    font-weight: 900;
    margin-left: 0.5rem;
    color: #adb5bd;
    font-size: 0.7rem;
}
.sortable.sort-asc::after { content: '\f0de'; color: var(--admin-primary); }
.sortable.sort-desc::after { content: '\f0dd'; color: var(--admin-primary); }

.badge-admin-active { background: rgba(30, 58, 95, 0.15); color: var(--admin-primary); }
.badge-admin-suspended { background: rgba(30, 58, 95, 0.08); color: #6c757d; }
.badge-admin-pending { background: rgba(74, 144, 217, 0.15); color: var(--admin-accent); }
.badge-admin-portal { background: rgba(74, 144, 217, 0.15); color: var(--admin-accent); }
.badge-admin-api { background: rgba(30, 58, 95, 0.15); color: var(--admin-primary); }

.type-vmn { color: var(--admin-primary); font-weight: 500; }
.type-shortcode-keyword { color: var(--admin-secondary); font-weight: 500; }
.type-dedicated { color: var(--admin-primary); font-weight: 500; }

.capability-pill {
    display: inline-block;
    padding: 0.15rem 0.4rem;
    font-size: 0.65rem;
    font-weight: 500;
    border-radius: 0.75rem;
    margin-right: 0.2rem;
}
.capability-senderid { background: rgba(74, 144, 217, 0.15); color: var(--admin-accent); }
.capability-inbox { background: rgba(45, 90, 135, 0.15); color: var(--admin-secondary); }
.capability-optout { background: rgba(74, 144, 217, 0.15); color: var(--admin-accent); }
.capability-api { background: rgba(30, 58, 95, 0.15); color: var(--admin-primary); }

.cost-value { font-weight: 500; color: #333; }
.supplier-value { color: #6c757d; font-size: 0.85rem; }
.date-value { color: #6c757d; font-size: 0.85rem; }

.action-dots-btn {
    background: transparent;
    border: none;
    padding: 0.25rem 0.5rem;
    color: #6c757d;
    cursor: pointer;
}
.action-dots-btn:hover { color: var(--admin-primary); }

.table-footer {
    padding: 0.75rem 1rem;
    border-top: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #fff;
}
.table-footer .pagination-info {
    font-size: 0.85rem;
    color: #6c757d;
}
.table-footer .pagination {
    margin: 0;
}
.table-footer .page-link {
    padding: 0.35rem 0.65rem;
    font-size: 0.85rem;
    color: var(--admin-primary);
    border-color: #dee2e6;
}
.table-footer .page-item.active .page-link {
    background: var(--admin-primary);
    border-color: var(--admin-primary);
    color: #fff;
}
.table-footer .page-link:hover {
    background: #f8f9fa;
    color: var(--admin-primary);
}

.filter-chips-row {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    align-items: center;
    margin-bottom: 1rem;
    padding: 0.75rem 1rem;
    background: rgba(74, 144, 217, 0.08);
    border-radius: 0.5rem;
    border: 1px solid rgba(74, 144, 217, 0.15);
}
.filter-chips-row .chips-label {
    font-size: 0.8rem;
    color: #6c757d;
    margin-right: 0.5rem;
}
.filter-chip {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.75rem;
    background: #fff;
    border: 1px solid rgba(74, 144, 217, 0.3);
    color: var(--admin-primary);
    border-radius: 1rem;
    font-size: 0.75rem;
    font-weight: 500;
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
.filter-chip .remove-chip:hover { opacity: 1; color: #dc2626; }
.filter-chips-row .clear-all-link {
    margin-left: auto;
    font-size: 0.75rem;
    color: var(--admin-accent);
    text-decoration: none;
}
.filter-chips-row .clear-all-link:hover { text-decoration: underline; }

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    color: #6c757d;
}
.empty-state i { font-size: 3rem; opacity: 0.3; margin-bottom: 1rem; }

.export-btn {
    background: transparent;
    border: 1px solid #dee2e6;
    color: #6c757d;
    padding: 0.375rem 0.75rem;
    font-size: 0.85rem;
    border-radius: 0.375rem;
}
.export-btn:hover {
    background: #f8f9fa;
    color: #495057;
}

.bulk-actions-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 1rem;
    background: linear-gradient(135deg, rgba(30, 58, 95, 0.08) 0%, rgba(74, 144, 217, 0.12) 100%);
    border: 1px solid rgba(74, 144, 217, 0.25);
    border-radius: 0.5rem;
    margin-bottom: 1rem;
}
.bulk-actions-bar .bulk-actions-left {
    display: flex;
    align-items: center;
    gap: 1rem;
}
.bulk-actions-bar .bulk-count {
    color: var(--admin-primary);
    font-size: 0.9rem;
}
.bulk-actions-bar .bulk-count strong {
    font-size: 1.1rem;
}

.row-checkbox {
    cursor: pointer;
}
tr.selected-row {
    background-color: rgba(74, 144, 217, 0.08) !important;
}
tr.selected-row:hover {
    background-color: rgba(74, 144, 217, 0.12) !important;
}

.bulk-summary-table {
    font-size: 0.85rem;
}
.bulk-summary-table th {
    background: #f8f9fa;
    font-weight: 600;
    color: #6c757d;
    text-transform: uppercase;
    font-size: 0.7rem;
    letter-spacing: 0.5px;
}
.bulk-summary-table td {
    padding: 0.5rem;
}
.bulk-change-arrow {
    color: var(--admin-accent);
    font-weight: bold;
}

.dropdown-item.disabled {
    color: #adb5bd !important;
    pointer-events: none;
}
.dropdown-item .incompatible-reason {
    font-size: 0.7rem;
    color: #dc3545;
    display: block;
    margin-top: 0.15rem;
}

.billing-impact-panel {
    border: 2px solid var(--admin-primary);
    border-radius: 0.5rem;
    overflow: hidden;
    background: #fff;
}
.billing-impact-header {
    background: linear-gradient(135deg, var(--admin-primary) 0%, var(--admin-secondary) 100%);
    color: #fff;
    padding: 0.75rem 1rem;
    font-size: 0.95rem;
}
.billing-impact-body {
    padding: 1rem;
    background: rgba(30, 58, 95, 0.03);
}
.billing-changes-grid {
    display: grid;
    gap: 0.75rem;
}
.billing-change-item {
    display: flex;
    align-items: center;
    padding: 0.75rem;
    background: #fff;
    border: 1px solid rgba(30, 58, 95, 0.15);
    border-radius: 0.375rem;
}
.billing-change-item .change-label {
    flex: 0 0 140px;
    font-weight: 600;
    color: var(--admin-primary);
    font-size: 0.85rem;
}
.billing-change-item .change-values {
    flex: 1;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}
.billing-change-item .change-before {
    padding: 0.25rem 0.5rem;
    background: rgba(108, 117, 125, 0.1);
    border-radius: 0.25rem;
    font-size: 0.85rem;
    color: #6c757d;
}
.billing-change-item .change-arrow {
    color: var(--admin-accent);
    font-weight: bold;
}
.billing-change-item .change-after {
    padding: 0.25rem 0.5rem;
    background: rgba(74, 144, 217, 0.15);
    border-radius: 0.25rem;
    font-size: 0.85rem;
    color: var(--admin-primary);
    font-weight: 600;
}
.billing-confirm-section {
    padding-top: 0.75rem;
    border-top: 1px solid rgba(30, 58, 95, 0.15);
}
.billing-confirm-section .form-check-label {
    font-weight: 500;
    color: var(--admin-primary);
}
.billing-confirm-section .form-check-input:checked {
    background-color: var(--admin-primary);
    border-color: var(--admin-primary);
}

.readonly-section {
    background: linear-gradient(135deg, rgba(108, 117, 125, 0.05) 0%, rgba(108, 117, 125, 0.08) 100%);
    border: 1px solid rgba(108, 117, 125, 0.2);
    border-radius: 0.5rem;
    padding: 1rem;
}
.readonly-section .section-header {
    display: flex;
    align-items: center;
    margin-bottom: 0.75rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid rgba(108, 117, 125, 0.15);
}
.readonly-section .section-header i {
    color: #6c757d;
    margin-right: 0.5rem;
}
.readonly-section .section-header span {
    font-weight: 600;
    color: #495057;
    font-size: 0.9rem;
}
.readonly-section .readonly-badge {
    margin-left: auto;
    font-size: 0.65rem;
    padding: 0.2rem 0.5rem;
    background: rgba(108, 117, 125, 0.15);
    color: #6c757d;
    border-radius: 0.25rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.readonly-field {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0;
    border-bottom: 1px dashed rgba(108, 117, 125, 0.15);
}
.readonly-field:last-child {
    border-bottom: none;
    padding-bottom: 0;
}
.readonly-field .field-label {
    font-size: 0.8rem;
    color: #6c757d;
    font-weight: 500;
}
.readonly-field .field-value {
    font-size: 0.85rem;
    color: #495057;
    font-weight: 500;
    text-align: right;
}
.readonly-field .field-value.not-available {
    color: #adb5bd;
    font-style: italic;
}
.readonly-field .ported-indicator {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
}
.readonly-field .ported-indicator i {
    color: var(--admin-accent);
    font-size: 0.75rem;
}
</style>
@endpush

@section('content')
<div class="admin-page">
    <div class="admin-breadcrumb">
        <a href="{{ route('admin.dashboard') }}">Admin</a>
        <span class="separator">/</span>
        <a href="#">Management</a>
        <span class="separator">/</span>
        <span>Numbers</span>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 style="color: var(--admin-primary); font-weight: 600;">Global Numbers Library</h4>
            <p class="text-muted mb-0">All numbers and keywords across the platform</p>
        </div>
        <div>
            <button class="export-btn" onclick="exportNumbers()">
                <i class="fas fa-download me-1"></i> Export
            </button>
        </div>
    </div>

    <div class="search-filter-toolbar mb-3">
        <div class="d-flex align-items-center justify-content-between">
            <div class="search-box" style="max-width: 350px; flex: 1;">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" class="form-control" id="globalSearch" placeholder="Search VMN, keyword, account..." onkeyup="handleSearch(this.value)">
                    <button class="btn btn-link text-muted" type="button" onclick="clearSearch()" id="clearSearchBtn" style="display: none;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <button class="filter-pill-btn" type="button" id="filterPillBtn">
                <i class="fas fa-filter"></i>
                <span>Filters</span>
                <span class="filter-count-badge" id="activeFilterCount" style="display: none;">0</span>
            </button>
        </div>
    </div>

    <div class="admin-filter-panel" id="filterPanel" style="display: none;">
        <div class="filter-body" id="filterBody">
            <div class="filter-row">
                <div class="filter-group">
                    <label>Country</label>
                    <div class="dropdown multiselect-dropdown" id="countryDropdown">
                        <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                            <span class="dropdown-label">All Countries</span>
                        </button>
                        <div class="dropdown-menu">
                            <div class="select-actions">
                                <a href="#" onclick="selectAll('countryDropdown'); return false;">Select All</a>
                                <a href="#" onclick="clearAll('countryDropdown'); return false;">Clear</a>
                            </div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" value="UK" id="country_UK"><label class="form-check-label" for="country_UK">United Kingdom</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" value="US" id="country_US"><label class="form-check-label" for="country_US">United States</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" value="DE" id="country_DE"><label class="form-check-label" for="country_DE">Germany</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" value="FR" id="country_FR"><label class="form-check-label" for="country_FR">France</label></div>
                        </div>
                    </div>
                </div>
                <div class="filter-group">
                    <label>Number Type</label>
                    <div class="dropdown multiselect-dropdown" id="typeDropdown">
                        <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                            <span class="dropdown-label">All Types</span>
                        </button>
                        <div class="dropdown-menu">
                            <div class="select-actions">
                                <a href="#" onclick="selectAll('typeDropdown'); return false;">Select All</a>
                                <a href="#" onclick="clearAll('typeDropdown'); return false;">Clear</a>
                            </div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" value="vmn" id="type_vmn"><label class="form-check-label" for="type_vmn">VMN</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" value="shortcode_keyword" id="type_shortcode"><label class="form-check-label" for="type_shortcode">Shared Shortcode Keyword</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" value="dedicated" id="type_dedicated"><label class="form-check-label" for="type_dedicated">Dedicated Shortcode</label></div>
                        </div>
                    </div>
                </div>
                <div class="filter-group">
                    <label>Status</label>
                    <div class="dropdown multiselect-dropdown" id="statusDropdown">
                        <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                            <span class="dropdown-label">All Statuses</span>
                        </button>
                        <div class="dropdown-menu">
                            <div class="select-actions">
                                <a href="#" onclick="selectAll('statusDropdown'); return false;">Select All</a>
                                <a href="#" onclick="clearAll('statusDropdown'); return false;">Clear</a>
                            </div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" value="active" id="status_active"><label class="form-check-label" for="status_active">Active</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" value="suspended" id="status_suspended"><label class="form-check-label" for="status_suspended">Suspended</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" value="pending" id="status_pending"><label class="form-check-label" for="status_pending">Pending</label></div>
                        </div>
                    </div>
                </div>
                <div class="filter-group">
                    <label>Capability</label>
                    <div class="dropdown multiselect-dropdown" id="capabilityDropdown">
                        <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                            <span class="dropdown-label">All Capabilities</span>
                        </button>
                        <div class="dropdown-menu">
                            <div class="select-actions">
                                <a href="#" onclick="selectAll('capabilityDropdown'); return false;">Select All</a>
                                <a href="#" onclick="clearAll('capabilityDropdown'); return false;">Clear</a>
                            </div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" value="senderid" id="cap_senderid"><label class="form-check-label" for="cap_senderid">SenderID</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" value="inbox" id="cap_inbox"><label class="form-check-label" for="cap_inbox">Inbox</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" value="optout" id="cap_optout"><label class="form-check-label" for="cap_optout">Opt-out</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" value="api" id="cap_api"><label class="form-check-label" for="cap_api">API</label></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="filter-row">
                <div class="filter-group wide">
                    <label>Customer Account</label>
                    <div class="dropdown multiselect-dropdown" id="accountDropdown">
                        <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                            <span class="dropdown-label">All Accounts</span>
                        </button>
                        <div class="dropdown-menu">
                            <div class="search-box">
                                <input type="text" class="form-control form-control-sm" placeholder="Search accounts..." onkeyup="filterDropdownOptions('accountDropdown', this.value)">
                            </div>
                            <div class="select-actions">
                                <a href="#" onclick="selectAll('accountDropdown'); return false;">Select All</a>
                                <a href="#" onclick="clearAll('accountDropdown'); return false;">Clear</a>
                            </div>
                            <div class="dropdown-options">
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Acme Corporation" id="acc_acme"><label class="form-check-label" for="acc_acme">Acme Corporation</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Finance Ltd" id="acc_finance"><label class="form-check-label" for="acc_finance">Finance Ltd</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="TechStart Inc" id="acc_techstart"><label class="form-check-label" for="acc_techstart">TechStart Inc</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Big Enterprise" id="acc_big"><label class="form-check-label" for="acc_big">Big Enterprise</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="NewClient" id="acc_new"><label class="form-check-label" for="acc_new">NewClient</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Retail Corp" id="acc_retail"><label class="form-check-label" for="acc_retail">Retail Corp</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Healthcare Plus" id="acc_health"><label class="form-check-label" for="acc_health">Healthcare Plus</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="US Branch Corp" id="acc_usbranch"><label class="form-check-label" for="acc_usbranch">US Branch Corp</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Support Services" id="acc_support"><label class="form-check-label" for="acc_support">Support Services</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Logistics Ltd" id="acc_logistics"><label class="form-check-label" for="acc_logistics">Logistics Ltd</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Old Account" id="acc_old"><label class="form-check-label" for="acc_old">Old Account</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Media Group" id="acc_media"><label class="form-check-label" for="acc_media">Media Group</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Banking Secure" id="acc_banking"><label class="form-check-label" for="acc_banking">Banking Secure</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Euro Expansion" id="acc_euro"><label class="form-check-label" for="acc_euro">Euro Expansion</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Tech Solutions" id="acc_techsol"><label class="form-check-label" for="acc_techsol">Tech Solutions</label></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="filter-group wide">
                    <label>Sub-Account</label>
                    <div class="dropdown multiselect-dropdown" id="subAccountDropdown">
                        <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                            <span class="dropdown-label">All Sub-Accounts</span>
                        </button>
                        <div class="dropdown-menu">
                            <div class="search-box">
                                <input type="text" class="form-control form-control-sm" placeholder="Search sub-accounts..." onkeyup="filterDropdownOptions('subAccountDropdown', this.value)">
                            </div>
                            <div class="select-actions">
                                <a href="#" onclick="selectAll('subAccountDropdown'); return false;">Select All</a>
                                <a href="#" onclick="clearAll('subAccountDropdown'); return false;">Clear</a>
                            </div>
                            <div class="dropdown-options" id="subAccountOptions">
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Marketing" id="sub_marketing"><label class="form-check-label" for="sub_marketing">Marketing</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Sales" id="sub_sales"><label class="form-check-label" for="sub_sales">Sales</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Retail" id="sub_retail"><label class="form-check-label" for="sub_retail">Retail</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Main" id="sub_main"><label class="form-check-label" for="sub_main">Main</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Operations" id="sub_ops"><label class="form-check-label" for="sub_ops">Operations</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Notifications" id="sub_notif"><label class="form-check-label" for="sub_notif">Notifications</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Support" id="sub_support"><label class="form-check-label" for="sub_support">Support</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Customer Care" id="sub_care"><label class="form-check-label" for="sub_care">Customer Care</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Dispatch" id="sub_dispatch"><label class="form-check-label" for="sub_dispatch">Dispatch</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Legacy" id="sub_legacy"><label class="form-check-label" for="sub_legacy">Legacy</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="News" id="sub_news"><label class="form-check-label" for="sub_news">News</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Alerts" id="sub_alerts"><label class="form-check-label" for="sub_alerts">Alerts</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Germany" id="sub_germany"><label class="form-check-label" for="sub_germany">Germany</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="France" id="sub_france"><label class="form-check-label" for="sub_france">France</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="Promotions" id="sub_promo"><label class="form-check-label" for="sub_promo">Promotions</label></div>
                                <div class="form-check"><input class="form-check-input" type="checkbox" value="API Team" id="sub_api"><label class="form-check-label" for="sub_api">API Team</label></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="filter-group">
                    <label>Supplier</label>
                    <div class="dropdown multiselect-dropdown" id="supplierDropdown">
                        <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                            <span class="dropdown-label">All Suppliers</span>
                        </button>
                        <div class="dropdown-menu">
                            <div class="select-actions">
                                <a href="#" onclick="selectAll('supplierDropdown'); return false;">Select All</a>
                                <a href="#" onclick="clearAll('supplierDropdown'); return false;">Clear</a>
                            </div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" value="Sinch" id="sup_sinch"><label class="form-check-label" for="sup_sinch">Sinch</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" value="Twilio" id="sup_twilio"><label class="form-check-label" for="sup_twilio">Twilio</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" value="Vonage" id="sup_vonage"><label class="form-check-label" for="sup_vonage">Vonage</label></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="filter-actions">
                <button class="btn admin-btn-reset" onclick="resetFilters()"><i class="fas fa-undo me-1"></i> Reset</button>
                <button class="btn admin-btn-apply" onclick="applyFilters()"><i class="fas fa-check me-1"></i> Apply Filters</button>
            </div>
        </div>
    </div>

    <div class="filter-chips-row" id="activeFiltersRow" style="display: none;">
        <span class="chips-label">Active filters:</span>
        <div id="filterChipsContainer"></div>
        <a href="#" class="clear-all-link" onclick="resetFilters(); return false;">Clear all</a>
    </div>

    <div class="bulk-actions-bar" id="bulkActionsBar" style="display: none;">
        <div class="bulk-actions-left">
            <span class="bulk-count"><strong id="selectedCount">0</strong> selected</span>
            <button type="button" class="btn btn-link btn-sm text-muted" onclick="clearSelection()">Clear selection</button>
        </div>
        <div class="bulk-actions-right">
            <div class="dropdown d-inline-block">
                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-tasks me-1"></i> Bulk Actions
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><h6 class="dropdown-header">Status Actions</h6></li>
                    <li><a class="dropdown-item" href="#" id="bulkSuspendBtn" onclick="initBulkAction('suspend'); return false;"><i class="fas fa-pause-circle me-2 text-warning"></i>Suspend Selected</a></li>
                    <li><a class="dropdown-item" href="#" id="bulkReactivateBtn" onclick="initBulkAction('reactivate'); return false;"><i class="fas fa-play-circle me-2 text-success"></i>Reactivate Selected</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><h6 class="dropdown-header">Assignment Actions</h6></li>
                    <li><a class="dropdown-item" href="#" onclick="initBulkAction('assignCustomer'); return false;"><i class="fas fa-building me-2 text-muted"></i>Assign to Customer</a></li>
                    <li><a class="dropdown-item" href="#" onclick="initBulkAction('assignSubAccount'); return false;"><i class="fas fa-sitemap me-2 text-muted"></i>Assign to Sub-Account</a></li>
                    <li><a class="dropdown-item text-danger" href="#" id="bulkReturnToPoolBtn" onclick="initBulkAction('returnToPool'); return false;"><i class="fas fa-undo me-2"></i>Return to Pool</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><h6 class="dropdown-header">Configuration Actions</h6></li>
                    <li><a class="dropdown-item" href="#" id="bulkChangeModeBtn" onclick="initBulkAction('changeMode'); return false;"><i class="fas fa-sync-alt me-2 text-muted"></i>Change Mode</a></li>
                    <li><a class="dropdown-item" href="#" id="bulkCapabilitiesBtn" onclick="initBulkAction('capabilities'); return false;"><i class="fas fa-cogs me-2 text-muted"></i>Apply Capability Toggles</a></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="numbersTable">
                    <thead>
                        <tr>
                            <th style="width: 40px;" class="text-center">
                                <input type="checkbox" class="form-check-input" id="selectAllCheckbox" onchange="toggleSelectAll()">
                            </th>
                            <th class="sortable" data-sort="number">Number / Keyword</th>
                            <th class="sortable" data-sort="country">Country</th>
                            <th class="sortable" data-sort="type">Number Type</th>
                            <th class="sortable" data-sort="status">Status</th>
                            <th class="sortable" data-sort="account">Customer Account</th>
                            <th class="sortable text-end" data-sort="cost">Monthly Cost</th>
                            <th class="sortable" data-sort="supplier">Supplier</th>
                            <th class="sortable" data-sort="created">Created Date</th>
                            <th class="text-center" style="width: 50px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="numbersTableBody">
                        <!-- Dynamic content populated by JavaScript -->
                    </tbody>
                </table>
            </div>
            <div class="table-footer">
                <div class="pagination-info">
                    Showing <span id="showingStart">1</span>-<span id="showingEnd">20</span> of <span id="totalCount">156</span> numbers
                </div>
                <nav>
                    <ul class="pagination pagination-sm" id="tablePagination">
                        <li class="page-item disabled"><a class="page-link" href="#"><i class="fas fa-chevron-left"></i></a></li>
                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                        <li class="page-item"><a class="page-link" href="#"><i class="fas fa-chevron-right"></i></a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="numberDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: var(--admin-primary); color: #fff;">
                <h5 class="modal-title"><i class="fas fa-phone-alt me-2"></i>Number Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="numberDetailsContent">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="configurationDrawer" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header" style="background: var(--admin-primary); color: #fff;">
                <h5 class="modal-title"><i class="fas fa-cog me-2"></i>Number Configuration</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="config-summary p-3" style="background: #f8fafc; border-bottom: 1px solid #e9ecef;">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="config-field">
                                <label class="text-muted small text-uppercase">Number/Keyword</label>
                                <div id="cfg_number" class="fw-bold" style="color: var(--admin-primary);"></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="config-field">
                                <label class="text-muted small text-uppercase">Type</label>
                                <div id="cfg_type"></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="config-field">
                                <label class="text-muted small text-uppercase">Status</label>
                                <div id="cfg_status"></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="config-field">
                                <label class="text-muted small text-uppercase">Customer Account</label>
                                <div id="cfg_account" class="fw-medium"></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="config-field">
                                <label class="text-muted small text-uppercase">Sub-Account(s)</label>
                                <div id="cfg_subaccount"></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="config-field">
                                <label class="text-muted small text-uppercase">Current Mode</label>
                                <div id="cfg_mode"></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="config-field">
                                <label class="text-muted small text-uppercase">Billing Model</label>
                                <div id="cfg_billing">Monthly Subscription</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="config-field">
                                <label class="text-muted small text-uppercase">Monthly Cost</label>
                                <div id="cfg_cost" class="fw-medium"></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="config-field">
                                <label class="text-muted small text-uppercase">Supplier</label>
                                <div id="cfg_supplier"></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="config-sections p-3">
                    <div class="config-section mb-4" id="sectionModeSelection">
                        <h6 class="section-title d-flex align-items-center mb-3">
                            <span class="section-icon me-2" style="background: var(--admin-primary); color: #fff; width: 24px; height: 24px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 0.7rem;">A</span>
                            Mode Selection
                        </h6>
                        <div class="section-content bg-light p-3 rounded">
                            <div class="btn-group w-100" role="group" id="modeToggleGroup">
                                <input type="radio" class="btn-check" name="configMode" id="configModePortal" value="portal" onchange="onModeChange('portal')">
                                <label class="btn btn-outline-primary" for="configModePortal">
                                    <i class="fas fa-desktop me-2"></i>Portal Mode
                                </label>
                                <input type="radio" class="btn-check" name="configMode" id="configModeAPI" value="api" onchange="onModeChange('api')">
                                <label class="btn btn-outline-primary" for="configModeAPI">
                                    <i class="fas fa-code me-2"></i>API Mode
                                </label>
                            </div>
                            <div id="modeChangeWarning" class="alert alert-warning mt-3" style="display: none;">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Warning:</strong> Changing mode affects customer functionality and may impact billing.
                            </div>
                        </div>
                    </div>
                    
                    <div class="config-section mb-4" id="sectionPortalConfig" style="display: none;">
                        <h6 class="section-title d-flex align-items-center mb-3">
                            <span class="section-icon me-2" style="background: var(--admin-accent); color: #fff; width: 24px; height: 24px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 0.7rem;">B</span>
                            Portal Configuration
                        </h6>
                        <div class="section-content bg-light p-3 rounded">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Assigned Sub-Accounts</label>
                                <div id="portalSubAccountsContainer">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="Marketing" id="portal_sub_marketing" checked>
                                        <label class="form-check-label" for="portal_sub_marketing">Marketing</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="Sales" id="portal_sub_sales">
                                        <label class="form-check-label" for="portal_sub_sales">Sales</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="Support" id="portal_sub_support">
                                        <label class="form-check-label" for="portal_sub_support">Support</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="Operations" id="portal_sub_ops">
                                        <label class="form-check-label" for="portal_sub_ops">Operations</label>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Default Sub-Account</label>
                                <select class="form-select" id="portalDefaultSubAccount">
                                    <option value="">Select default...</option>
                                    <option value="Marketing" selected>Marketing</option>
                                    <option value="Sales">Sales</option>
                                    <option value="Support">Support</option>
                                    <option value="Operations">Operations</option>
                                </select>
                                <small class="text-muted">Used when no sub-account is specified</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Capability Toggles</label>
                                <div id="portalCapabilityToggles">
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="cap_portal_senderid" checked>
                                        <label class="form-check-label" for="cap_portal_senderid">
                                            <strong>Allow as SenderID</strong>
                                            <small class="d-block text-muted">Use this number as sender ID for outbound messages</small>
                                        </label>
                                    </div>
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="cap_portal_inbox" checked>
                                        <label class="form-check-label" for="cap_portal_inbox">
                                            <strong>Enable Inbox Replies</strong>
                                            <small class="d-block text-muted">Receive inbound messages to portal inbox</small>
                                        </label>
                                    </div>
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="cap_portal_optout" checked>
                                        <label class="form-check-label" for="cap_portal_optout">
                                            <strong>Enable Opt-out Handling</strong>
                                            <small class="d-block text-muted">Automatically process STOP/unsubscribe requests</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-0">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="portalAdminOverride">
                                    <label class="form-check-label" for="portalAdminOverride">
                                        <strong>Override Customer Defaults</strong>
                                        <small class="d-block text-muted">Admin override flag - ignore customer-level default settings</small>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="config-section mb-4" id="sectionAPIConfig" style="display: none;">
                        <h6 class="section-title d-flex align-items-center mb-3">
                            <span class="section-icon me-2" style="background: var(--admin-secondary); color: #fff; width: 24px; height: 24px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 0.7rem;">C</span>
                            API Configuration
                        </h6>
                        <div class="section-content bg-light p-3 rounded">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Assigned Sub-Account</label>
                                <select class="form-select" id="apiSubAccount">
                                    <option value="">Select sub-account...</option>
                                    <option value="API Team" selected>API Team</option>
                                    <option value="Main">Main</option>
                                    <option value="Development">Development</option>
                                </select>
                                <small class="text-muted">API mode allows only one sub-account assignment</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Inbound Webhook URL</label>
                                <input type="url" class="form-control" id="apiWebhookUrl" placeholder="https://api.example.com/webhook/sms" value="">
                                <div id="webhookValidation" class="invalid-feedback">URL must start with https://</div>
                                <small class="text-muted">Inbound messages will be POSTed to this URL</small>
                            </div>
                            <div class="mb-0">
                                <label class="form-label fw-bold">API Connection Association</label>
                                <div class="bg-white p-3 rounded border">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <span class="badge bg-success me-2">Connected</span>
                                            <strong id="apiConnectionName">Production API Key</strong>
                                        </div>
                                        <small class="text-muted">Read-only</small>
                                    </div>
                                    <div class="mt-2 small text-muted">
                                        <span>Key ID:</span> <code id="apiKeyId">pk_live_abc123...xyz</code>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div id="keywordRestrictionAlert" class="alert alert-info" style="display: none;">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Shared Shortcode Keywords</strong> have limited configuration options. They can only use Opt-out or API capabilities.
                    </div>
                    
                    <div class="config-section mb-0" id="sectionNetworkInfo">
                        <h6 class="section-title d-flex align-items-center mb-3">
                            <span class="section-icon me-2" style="background: #6c757d; color: #fff; width: 24px; height: 24px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 0.7rem;"><i class="fas fa-network-wired" style="font-size: 0.6rem;"></i></span>
                            Supplier & Network Information
                        </h6>
                        <div class="readonly-section">
                            <div class="section-header">
                                <i class="fas fa-lock"></i>
                                <span>Routing Details</span>
                                <span class="readonly-badge">Read-only</span>
                            </div>
                            <div class="readonly-field">
                                <span class="field-label">Supplier</span>
                                <span class="field-value" id="cfg_network_supplier">—</span>
                            </div>
                            <div class="readonly-field">
                                <span class="field-label">Route</span>
                                <span class="field-value" id="cfg_network_route">—</span>
                            </div>
                            <div class="readonly-field">
                                <span class="field-label">Network Destination</span>
                                <span class="field-value" id="cfg_network_destination">—</span>
                            </div>
                            <div class="readonly-field" id="portedToRow" style="display: none;">
                                <span class="field-label">Ported To Network (UK)</span>
                                <span class="field-value" id="cfg_ported_to">—</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveConfiguration()">
                    <i class="fas fa-save me-1"></i> Save Configuration
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="confirmActionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" id="confirmModalHeader">
                <h5 class="modal-title" id="confirmModalTitle">Confirm Action</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="confirmModalBody">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn" id="confirmModalBtn" onclick="executeConfirmedAction()">Confirm</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="reassignModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: var(--admin-primary); color: #fff;">
                <h5 class="modal-title"><i class="fas fa-exchange-alt me-2"></i>Reassign Number</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info mb-3">
                    <i class="fas fa-info-circle me-2"></i>
                    Reassigning a number will transfer ownership and billing to the new account.
                </div>
                <div id="reassignCurrentInfo" class="mb-3"></div>
                <div class="mb-3">
                    <label class="form-label fw-bold">New Customer Account</label>
                    <select class="form-select" id="reassignAccount">
                        <option value="">Select account...</option>
                        <option value="Acme Corporation">Acme Corporation</option>
                        <option value="Finance Ltd">Finance Ltd</option>
                        <option value="TechStart Inc">TechStart Inc</option>
                        <option value="Big Enterprise">Big Enterprise</option>
                        <option value="Retail Corp">Retail Corp</option>
                        <option value="Healthcare Plus">Healthcare Plus</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">New Sub-Account</label>
                    <select class="form-select" id="reassignSubAccount">
                        <option value="">Select sub-account...</option>
                        <option value="Main">Main</option>
                        <option value="Marketing">Marketing</option>
                        <option value="Sales">Sales</option>
                        <option value="Support">Support</option>
                        <option value="Operations">Operations</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Reason for Reassignment</label>
                    <textarea class="form-control" id="reassignReason" rows="2" placeholder="Enter reason (required for audit)..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="executeReassign()">Reassign Number</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editCapabilitiesModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: var(--admin-primary); color: #fff;">
                <h5 class="modal-title"><i class="fas fa-cogs me-2"></i>Edit Capabilities</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="capabilitiesNumberInfo" class="mb-3"></div>
                <div id="capabilitiesRulesAlert" class="alert alert-warning mb-3" style="display: none;">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <span id="capabilitiesRulesText"></span>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Available Capabilities</label>
                    <div id="capabilityToggles"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveCapabilities()">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="optoutRoutingModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: var(--admin-primary); color: #fff;">
                <h5 class="modal-title"><i class="fas fa-route me-2"></i>Edit Opt-out Routing</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="optoutKeywordInfo" class="mb-3"></div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Opt-out Keywords</label>
                    <input type="text" class="form-control" id="optoutKeywords" value="STOP, UNSUBSCRIBE, QUIT, END">
                    <small class="text-muted">Comma-separated list of keywords that trigger opt-out</small>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Auto-Reply Message</label>
                    <textarea class="form-control" id="optoutReply" rows="2">You have been unsubscribed. Reply START to resubscribe.</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Forward Opt-outs To</label>
                    <select class="form-select" id="optoutForward">
                        <option value="none">Do not forward</option>
                        <option value="email">Email notification</option>
                        <option value="webhook">Webhook URL</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveOptoutRouting()">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="bulkActionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" id="bulkModalHeader" style="background: var(--admin-primary); color: #fff;">
                <h5 class="modal-title" id="bulkModalTitle"><i class="fas fa-tasks me-2"></i>Bulk Action</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="bulkActionSummary"></div>
                <div id="bulkActionOptions" class="mt-3"></div>
                
                <div id="bulkBillingWarning" class="billing-impact-panel mt-3" style="display: none;">
                    <div class="billing-impact-header">
                        <i class="fas fa-pound-sign me-2"></i>
                        <strong>Billing Impact</strong>
                    </div>
                    <div class="billing-impact-body">
                        <p class="mb-2">This action will affect monthly billing for the selected numbers:</p>
                        <div id="billingChangesSummary" class="billing-changes-grid"></div>
                        <div class="billing-confirm-section mt-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="billingConfirmCheckbox" onchange="updateBillingConfirmState()">
                                <label class="form-check-label" for="billingConfirmCheckbox">
                                    I understand this action impacts billing and wish to proceed
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div id="bulkIncompatibleWarning" class="alert alert-danger mt-3" style="display: none;">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <span id="bulkIncompatibleText"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="bulkExecuteBtn" onclick="executeBulkAction()" disabled>
                    <i class="fas fa-check me-1"></i> Apply to <span id="bulkApplyCount">0</span> Numbers
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/numbers-admin-service.js') }}"></script>
<script>
console.log('[Admin Numbers] Script starting...');
console.log('[Admin Numbers] NumbersAdminService loaded:', typeof NumbersAdminService !== 'undefined');

let numbersData = [];
let currentPage = 1;
const rowsPerPage = 20;
let filteredData = [];
let sortColumn = 'created';
let sortDirection = 'desc';
let appliedFilters = {};
let selectedRows = new Set();
let currentBulkAction = null;
let isLoading = false;

function initNumbersPage() {
    try {
        console.log('[Admin Numbers] Initializing Global Numbers Library');
        
        if (typeof NumbersAdminService === 'undefined') {
            console.error('[Admin Numbers] ERROR: NumbersAdminService not loaded!');
            return;
        }
        
        console.log('[Admin Numbers] Service loaded, mock mode:', NumbersAdminService.config.useMockData);
        
        numbersData = NumbersAdminService._mockDb.numbers.slice();
        filteredData = numbersData.slice();
        console.log('[Admin Numbers] Mock data loaded:', numbersData.length, 'numbers');
        
        initializeSorting();
        initializeMultiSelectDropdowns();
        
        renderTable(numbersData);
        updatePaginationInfo({ totalCount: numbersData.length, page: 1, pageSize: rowsPerPage });
        
        if (typeof ADMIN_AUDIT !== 'undefined') {
            ADMIN_AUDIT.logDataAccess('NUMBERS_LIBRARY_VIEWED', 'numbers', { action: 'view_list' });
        }
        
        console.log('[Admin Numbers] Page initialized successfully');
    } catch (e) {
        console.error('[Admin Numbers] Init error:', e);
    }
}

async function loadNumbersData() {
    console.log('[Admin Numbers] loadNumbersData called');
    showLoadingState(true);
    
    try {
        console.log('[Admin Numbers] Calling NumbersAdminService.listNumbers...');
        const result = await NumbersAdminService.listNumbers(
            appliedFilters,
            { page: currentPage, pageSize: rowsPerPage },
            { field: sortColumn, direction: sortDirection }
        );
        
        console.log('[Admin Numbers] Service returned:', result);
        
        if (result.success) {
            numbersData = result.data;
            filteredData = numbersData;
            console.log('[Admin Numbers] Data loaded, count:', filteredData.length);
            renderTable(filteredData);
            updatePaginationInfo(result.pagination);
        } else {
            showToast('Failed to load numbers: ' + result.error, 'error');
            document.getElementById('numbersTableBody').innerHTML = '<tr><td colspan="11" class="text-center text-warning">Failed to load: ' + result.error + '</td></tr>';
        }
    } catch (error) {
        document.getElementById('numbersTableBody').innerHTML = '<tr><td colspan="11" class="text-center text-danger">Load error: ' + error.message + '</td></tr>';
        console.error('[Admin Numbers] Error loading data:', error);
        showToast('Error loading numbers data', 'error');
    } finally {
        showLoadingState(false);
    }
}

function showLoadingState(loading) {
    isLoading = loading;
    const tableBody = document.getElementById('numbersTableBody');
    if (loading && tableBody) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="12" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Loading numbers...</p>
                </td>
            </tr>
        `;
    }
}

function toggleFilterPanel() {
    console.log('[Admin Numbers] toggleFilterPanel called');
    const panel = document.getElementById('filterPanel');
    const btn = document.getElementById('filterPillBtn');
    
    if (!panel) {
        console.error('[Admin Numbers] Filter panel element not found!');
        return;
    }
    if (!btn) {
        console.error('[Admin Numbers] Filter button element not found!');
        return;
    }
    
    console.log('[Admin Numbers] Current panel display:', panel.style.display);
    
    if (panel.style.display === 'none' || panel.style.display === '') {
        panel.style.display = 'block';
        btn.classList.add('active');
        console.log('[Admin Numbers] Filter panel opened');
    } else {
        panel.style.display = 'none';
        btn.classList.remove('active');
        console.log('[Admin Numbers] Filter panel closed');
    }
}

let searchTimeout = null;
function handleSearch(value) {
    const clearBtn = document.getElementById('clearSearchBtn');
    clearBtn.style.display = value ? 'block' : 'none';
    
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        const searchTerm = value.toLowerCase().trim();
        if (!searchTerm) {
            filteredData = numbersData.slice();
        } else {
            filteredData = numbersData.filter(num => 
                num.number.toLowerCase().includes(searchTerm) ||
                num.account.toLowerCase().includes(searchTerm) ||
                num.subAccount.toLowerCase().includes(searchTerm) ||
                num.type.toLowerCase().includes(searchTerm) ||
                num.supplier.toLowerCase().includes(searchTerm) ||
                num.country.toLowerCase().includes(searchTerm)
            );
        }
        currentPage = 1;
        renderTable(filteredData);
        updatePaginationInfo({ totalCount: filteredData.length, page: 1, pageSize: rowsPerPage });
    }, 300);
}

function clearSearch() {
    document.getElementById('globalSearch').value = '';
    document.getElementById('clearSearchBtn').style.display = 'none';
    filteredData = numbersData.slice();
    currentPage = 1;
    renderTable(filteredData);
    updatePaginationInfo({ totalCount: filteredData.length, page: 1, pageSize: rowsPerPage });
}

function initializeMultiSelectDropdowns() {
    document.querySelectorAll('.multiselect-dropdown').forEach(dropdown => {
        dropdown.querySelectorAll('input[type="checkbox"]').forEach(cb => {
            cb.addEventListener('change', () => updateDropdownLabel(dropdown.id));
        });
    });
}

function updateDropdownLabel(dropdownId) {
    const dropdown = document.getElementById(dropdownId);
    const checked = dropdown.querySelectorAll('input[type="checkbox"]:checked');
    const label = dropdown.querySelector('.dropdown-label');
    const allLabels = {
        'countryDropdown': 'All Countries',
        'typeDropdown': 'All Types',
        'statusDropdown': 'All Statuses',
        'modeDropdown': 'All Modes',
        'capabilityDropdown': 'All Capabilities',
        'accountDropdown': 'All Accounts',
        'subAccountDropdown': 'All Sub-Accounts',
        'supplierDropdown': 'All Suppliers'
    };
    
    if (checked.length === 0) {
        label.innerHTML = allLabels[dropdownId] || 'All';
    } else if (checked.length === 1) {
        label.innerHTML = checked[0].nextElementSibling.textContent;
    } else {
        label.innerHTML = `${checked.length} selected <span class="selected-count">${checked.length}</span>`;
    }
}

function selectAll(dropdownId) {
    const dropdown = document.getElementById(dropdownId);
    dropdown.querySelectorAll('.dropdown-options input[type="checkbox"], .dropdown-menu > .form-check input[type="checkbox"]').forEach(cb => {
        if (cb.closest('.form-check').style.display !== 'none') {
            cb.checked = true;
        }
    });
    updateDropdownLabel(dropdownId);
}

function clearAll(dropdownId) {
    const dropdown = document.getElementById(dropdownId);
    dropdown.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
    updateDropdownLabel(dropdownId);
}

function filterDropdownOptions(dropdownId, searchTerm) {
    const dropdown = document.getElementById(dropdownId);
    const options = dropdown.querySelectorAll('.dropdown-options .form-check, .dropdown-menu > .form-check');
    const term = searchTerm.toLowerCase();
    
    options.forEach(option => {
        const label = option.querySelector('label').textContent.toLowerCase();
        option.style.display = label.includes(term) ? '' : 'none';
    });
}

function getSelectedValues(dropdownId) {
    const dropdown = document.getElementById(dropdownId);
    const checked = dropdown.querySelectorAll('input[type="checkbox"]:checked');
    return Array.from(checked).map(cb => cb.value);
}

function loadNumbersData() {
    renderTable(filteredData);
    updatePaginationInfo();
}

function renderTable(data) {
    const tbody = document.getElementById('numbersTableBody');
    
    if (!data || data.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="11" class="empty-state">
                    <i class="fas fa-phone-slash d-block"></i>
                    <p>No numbers found matching your criteria</p>
                </td>
            </tr>
        `;
        document.getElementById('selectAllCheckbox').checked = false;
        return;
    }
    
    console.log('[Admin Numbers] Rendering', data.length, 'numbers');
    
    tbody.innerHTML = data.map(num => `
        <tr data-id="${num.id}" class="${selectedRows.has(num.id) ? 'selected-row' : ''}">
            <td class="text-center">
                <input type="checkbox" class="form-check-input row-checkbox" 
                       data-id="${num.id}" 
                       onchange="toggleRowSelection('${num.id}')"
                       ${selectedRows.has(num.id) ? 'checked' : ''}>
            </td>
            <td><span class="number-value">${num.number}</span></td>
            <td>${getCountryFlag(num.country)} ${num.country}</td>
            <td>${getTypeLabel(num.type)}</td>
            <td>${getStatusBadge(num.status)}</td>
            <td class="account-cell">
                <div class="account-name">${num.account}</div>
                <div class="sub-account">${num.subAccount}</div>
            </td>
            <td class="text-end"><span class="cost-value">£${num.cost.toFixed(2)}</span></td>
            <td><span class="supplier-value">${num.supplier}</span></td>
            <td><span class="date-value">${formatDate(num.created)}</span></td>
            <td class="text-center">
                <div class="dropdown">
                    <button class="action-dots-btn" type="button" 
                            id="dropdown-${num.id}" 
                            aria-expanded="false">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                    ${buildContextMenu(num)}
                </div>
            </td>
        </tr>
    `).join('');
    
    updateSelectAllState();
    initializeDropdowns();
}

function initializeDropdowns() {
    setupActionDropdownHandler();
}

let actionDropdownHandlerInitialized = false;
function setupActionDropdownHandler() {
    if (actionDropdownHandlerInitialized) return;
    actionDropdownHandlerInitialized = true;
    
    console.log('[Admin Numbers] Setting up action dropdown handler');
    
    function closeAllDropdowns(except) {
        document.querySelectorAll('#numbersTableBody .dropdown-menu.show').forEach(m => {
            if (m !== except) {
                m.classList.remove('show');
                const prevBtn = m.previousElementSibling;
                if (prevBtn) prevBtn.setAttribute('aria-expanded', 'false');
            }
        });
    }
    
    function positionMenu(menu, btn) {
        const btnRect = btn.getBoundingClientRect();
        const menuHeight = 200;
        const viewportHeight = window.innerHeight;
        
        let topPos = btnRect.bottom + 2;
        if (topPos + menuHeight > viewportHeight - 10) {
            topPos = Math.max(10, btnRect.top - menuHeight - 2);
        }
        
        menu.style.top = topPos + 'px';
        menu.style.right = (window.innerWidth - btnRect.right) + 'px';
        menu.style.left = 'auto';
    }
    
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('#numbersTableBody .action-dots-btn');
        const dropdownItem = e.target.closest('#numbersTableBody .dropdown-menu .dropdown-item');
        
        if (dropdownItem) {
            e.preventDefault();
            e.stopPropagation();
            
            const action = dropdownItem.dataset.action;
            const numberId = dropdownItem.dataset.id;
            
            console.log('[Admin Numbers] Dropdown item clicked - action:', action, 'id:', numberId);
            
            const menu = dropdownItem.closest('.dropdown-menu');
            if (menu) {
                menu.classList.remove('show');
                const prevBtn = menu.previousElementSibling;
                if (prevBtn) prevBtn.setAttribute('aria-expanded', 'false');
            }
            
            handleDropdownAction(action, numberId, dropdownItem.dataset);
            return;
        }
        
        if (btn) {
            e.preventDefault();
            e.stopPropagation();
            
            const menu = btn.nextElementSibling;
            if (!menu || !menu.classList.contains('dropdown-menu')) {
                console.error('[Admin Numbers] No dropdown menu found for button');
                return;
            }
            
            closeAllDropdowns(menu);
            
            const isCurrentlyShown = menu.classList.contains('show');
            
            if (isCurrentlyShown) {
                menu.classList.remove('show');
                btn.setAttribute('aria-expanded', 'false');
            } else {
                positionMenu(menu, btn);
                menu.classList.add('show');
                btn.setAttribute('aria-expanded', 'true');
            }
            
            console.log('[Admin Numbers] Dropdown toggled:', !isCurrentlyShown ? 'opened' : 'closed');
            return;
        }
        
        if (!e.target.closest('.dropdown-menu')) {
            closeAllDropdowns(null);
        }
    });
}

function handleDropdownAction(action, numberId, dataset) {
    console.log('[Admin Numbers] Handling action:', action, 'for number:', numberId);
    
    switch (action) {
        case 'view-config':
            viewNumberDetails(numberId);
            break;
        case 'view-audit':
            viewAuditTrail(numberId);
            break;
        case 'suspend':
            confirmSuspend(numberId);
            break;
        case 'reactivate':
            confirmReactivate(numberId);
            break;
        case 'reassign':
            openReassignModal(numberId);
            break;
        case 'return-to-pool':
            confirmReturnToPool(numberId);
            break;
        case 'change-mode':
            const targetMode = dataset.targetMode || 'api';
            confirmChangeMode(numberId, targetMode);
            break;
        case 'edit-capabilities':
            openEditCapabilities(numberId);
            break;
        case 'assign-subaccounts':
            openSubAccountAssign(numberId);
            break;
        case 'override-usage':
            openOverrideUsage(numberId);
            break;
        case 'reassign-subaccount':
            openReassignSubAccountOnly(numberId);
            break;
        case 'edit-optout-routing':
            openOptoutRouting(numberId);
            break;
        case 'disable-keyword':
            confirmDisableKeyword(numberId);
            break;
        default:
            console.warn('[Admin Numbers] Unknown action:', action);
    }
}

function buildContextMenu(num) {
    let menuItems = [];
    
    menuItems.push(`<li><a class="dropdown-item" href="#" data-action="view-config" data-id="${num.id}"><i class="fas fa-cog me-2 text-muted"></i>View Configuration</a></li>`);
    menuItems.push(`<li><a class="dropdown-item" href="#" data-action="view-audit" data-id="${num.id}"><i class="fas fa-history me-2 text-muted"></i>View Audit History</a></li>`);
    menuItems.push('<li><hr class="dropdown-divider"></li>');
    
    if (num.status === 'active') {
        menuItems.push(`<li><a class="dropdown-item text-warning" href="#" data-action="suspend" data-id="${num.id}"><i class="fas fa-pause-circle me-2"></i>Suspend Number</a></li>`);
    } else if (num.status === 'suspended') {
        menuItems.push(`<li><a class="dropdown-item text-success" href="#" data-action="reactivate" data-id="${num.id}"><i class="fas fa-play-circle me-2"></i>Reactivate Number</a></li>`);
    }
    
    menuItems.push(`<li><a class="dropdown-item" href="#" data-action="reassign" data-id="${num.id}"><i class="fas fa-exchange-alt me-2 text-muted"></i>Reassign Customer / Sub-Account</a></li>`);
    menuItems.push(`<li><a class="dropdown-item text-danger" href="#" data-action="return-to-pool" data-id="${num.id}"><i class="fas fa-undo me-2"></i>Return to Pool</a></li>`);
    
    if (num.type === 'vmn' || num.type === 'dedicated_shortcode' || num.type === 'dedicated') {
        menuItems.push('<li><hr class="dropdown-divider"></li>');
        
        const targetMode = num.mode === 'portal' ? 'api' : 'portal';
        const targetLabel = num.mode === 'portal' ? 'API' : 'Portal';
        menuItems.push(`<li><a class="dropdown-item" href="#" data-action="change-mode" data-id="${num.id}" data-target-mode="${targetMode}"><i class="fas fa-sync-alt me-2 text-muted"></i>Change Mode to ${targetLabel}</a></li>`);
        
        menuItems.push(`<li><a class="dropdown-item" href="#" data-action="edit-capabilities" data-id="${num.id}"><i class="fas fa-cogs me-2 text-muted"></i>Edit Capabilities</a></li>`);
        
        if (num.mode === 'portal') {
            menuItems.push(`<li><a class="dropdown-item" href="#" data-action="assign-subaccounts" data-id="${num.id}"><i class="fas fa-sitemap me-2 text-muted"></i>Assign / Remove Sub-Accounts</a></li>`);
            menuItems.push(`<li><a class="dropdown-item" href="#" data-action="override-usage" data-id="${num.id}"><i class="fas fa-sliders-h me-2 text-muted"></i>Override Default Usage</a></li>`);
        }
    }
    
    if (num.type === 'shortcode_keyword') {
        menuItems.push('<li><hr class="dropdown-divider"></li>');
        menuItems.push(`<li><a class="dropdown-item" href="#" data-action="reassign-subaccount" data-id="${num.id}"><i class="fas fa-sitemap me-2 text-muted"></i>Reassign Sub-Account</a></li>`);
        menuItems.push(`<li><a class="dropdown-item" href="#" data-action="edit-optout-routing" data-id="${num.id}"><i class="fas fa-route me-2 text-muted"></i>Edit Opt-out Routing</a></li>`);
        
        if (num.status === 'active') {
            menuItems.push(`<li><a class="dropdown-item text-danger" href="#" data-action="disable-keyword" data-id="${num.id}"><i class="fas fa-ban me-2"></i>Disable Keyword</a></li>`);
        }
    }
    
    return `<ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdown-${num.id}">${menuItems.join('')}</ul>`;
}

function getCountryFlag(country) {
    const flags = { 'UK': '🇬🇧', 'US': '🇺🇸', 'DE': '🇩🇪', 'FR': '🇫🇷', 'IE': '🇮🇪' };
    return flags[country] || '🌍';
}

function getTypeLabel(type) {
    const types = {
        'vmn': '<span class="type-vmn">VMN</span>',
        'shortcode_keyword': '<span class="type-shortcode-keyword">Shortcode Keyword</span>',
        'dedicated_shortcode': '<span class="type-dedicated">Dedicated Shortcode</span>',
        'dedicated': '<span class="type-dedicated">Dedicated Shortcode</span>'
    };
    return types[type] || type;
}

function getStatusBadge(status) {
    const badges = {
        'active': '<span class="badge badge-admin-active">Active</span>',
        'suspended': '<span class="badge badge-admin-suspended">Suspended</span>',
        'pending': '<span class="badge badge-admin-pending">Pending</span>'
    };
    return badges[status] || status;
}

function getModeBadge(mode) {
    const badges = {
        'portal': '<span class="badge badge-admin-portal">Portal</span>',
        'api': '<span class="badge badge-admin-api">API</span>'
    };
    return badges[mode] || mode;
}

function formatDate(dateStr) {
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
}

async function applyFilters() {
    const countries = getSelectedValues('countryDropdown');
    const types = getSelectedValues('typeDropdown');
    const statuses = getSelectedValues('statusDropdown');
    const modes = getSelectedValues('modeDropdown');
    const capabilities = getSelectedValues('capabilityDropdown');
    const accounts = getSelectedValues('accountDropdown');
    const subAccounts = getSelectedValues('subAccountDropdown');
    const suppliers = getSelectedValues('supplierDropdown');
    
    appliedFilters = { 
        country: countries, 
        type: types, 
        status: statuses, 
        mode: modes, 
        capability: capabilities, 
        account: accounts, 
        subAccount: subAccounts, 
        supplier: suppliers 
    };
    
    currentPage = 1;
    showLoadingState(true);
    
    try {
        const result = await NumbersAdminService.listNumbers(
            appliedFilters,
            { page: currentPage, pageSize: rowsPerPage },
            { field: sortColumn, direction: sortDirection }
        );
        
        if (result.success) {
            numbersData = result.data;
            filteredData = numbersData;
            renderTable(filteredData);
            updatePaginationInfo(result.pagination);
            updateFilterChips();
            
            if (typeof ADMIN_AUDIT !== 'undefined') {
                ADMIN_AUDIT.logDataAccess('NUMBERS_FILTERED', 'numbers', { 
                    filters: appliedFilters, 
                    resultCount: filteredData.length 
                });
            }
        } else {
            showToast('Failed to filter numbers: ' + result.error, 'error');
        }
    } catch (error) {
        console.error('[Admin Numbers] Filter error:', error);
        showToast('Error filtering numbers', 'error');
    } finally {
        showLoadingState(false);
    }
}

async function resetFilters() {
    ['countryDropdown', 'typeDropdown', 'statusDropdown', 'modeDropdown', 'capabilityDropdown', 'accountDropdown', 'subAccountDropdown', 'supplierDropdown'].forEach(id => {
        clearAll(id);
    });
    
    appliedFilters = {};
    currentPage = 1;
    await loadNumbersData();
    updateFilterChips();
}

function updateFilterChips() {
    const container = document.getElementById('filterChipsContainer');
    const row = document.getElementById('activeFiltersRow');
    const chips = [];
    
    const filterLabels = {
        countries: 'Country',
        types: 'Type',
        statuses: 'Status',
        modes: 'Mode',
        capabilities: 'Capability',
        accounts: 'Account',
        subAccounts: 'Sub-Account',
        suppliers: 'Supplier'
    };
    
    Object.entries(appliedFilters).forEach(([key, values]) => {
        if (values && values.length > 0) {
            values.forEach(val => {
                chips.push({ filterKey: key, value: val, label: filterLabels[key] });
            });
        }
    });
    
    if (chips.length === 0) {
        row.style.display = 'none';
        return;
    }
    
    row.style.display = 'flex';
    container.innerHTML = chips.map(chip => `
        <span class="filter-chip">
            <span class="chip-label">${chip.label}:</span> ${chip.value}
            <i class="fas fa-times remove-chip" onclick="removeFilterChip('${chip.filterKey}', '${chip.value}')"></i>
        </span>
    `).join('');
}

function removeFilterChip(filterKey, value) {
    const dropdownMap = {
        countries: 'countryDropdown',
        types: 'typeDropdown',
        statuses: 'statusDropdown',
        modes: 'modeDropdown',
        capabilities: 'capabilityDropdown',
        accounts: 'accountDropdown',
        subAccounts: 'subAccountDropdown',
        suppliers: 'supplierDropdown'
    };
    
    const dropdown = document.getElementById(dropdownMap[filterKey]);
    const checkbox = dropdown.querySelector(`input[value="${value}"]`);
    if (checkbox) {
        checkbox.checked = false;
        updateDropdownLabel(dropdownMap[filterKey]);
    }
    
    applyFilters();
}

function updatePaginationInfo(pagination = null) {
    let total, start, end;
    
    if (pagination) {
        total = pagination.totalCount;
        start = total === 0 ? 0 : (pagination.page - 1) * pagination.pageSize + 1;
        end = Math.min(pagination.page * pagination.pageSize, total);
    } else {
        total = filteredData.length;
        start = total === 0 ? 0 : (currentPage - 1) * rowsPerPage + 1;
        end = Math.min(currentPage * rowsPerPage, total);
    }
    
    document.getElementById('showingStart').textContent = start;
    document.getElementById('showingEnd').textContent = end;
    document.getElementById('totalCount').textContent = total;
}

function initializeSorting() {
    document.querySelectorAll('#numbersTable th.sortable').forEach(th => {
        th.addEventListener('click', function() {
            const column = this.dataset.sort;
            
            document.querySelectorAll('#numbersTable th.sortable').forEach(h => {
                h.classList.remove('sort-asc', 'sort-desc');
            });
            
            if (sortColumn === column) {
                sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                sortColumn = column;
                sortDirection = 'asc';
            }
            
            this.classList.add(sortDirection === 'asc' ? 'sort-asc' : 'sort-desc');
            
            sortTable();
        });
    });
}

function sortTable() {
    filteredData.sort((a, b) => {
        let aVal, bVal;
        
        switch (sortColumn) {
            case 'number': aVal = a.number; bVal = b.number; break;
            case 'country': aVal = a.country; bVal = b.country; break;
            case 'type': aVal = a.type; bVal = b.type; break;
            case 'status': aVal = a.status; bVal = b.status; break;
            case 'mode': aVal = a.mode; bVal = b.mode; break;
            case 'account': aVal = a.account; bVal = b.account; break;
            case 'cost': aVal = a.cost; bVal = b.cost; break;
            case 'supplier': aVal = a.supplier; bVal = b.supplier; break;
            case 'created': aVal = new Date(a.created); bVal = new Date(b.created); break;
            default: aVal = a.number; bVal = b.number;
        }
        
        if (typeof aVal === 'string') {
            return sortDirection === 'asc' ? aVal.localeCompare(bVal) : bVal.localeCompare(aVal);
        }
        return sortDirection === 'asc' ? aVal - bVal : bVal - aVal;
    });
    
    renderTable(filteredData);
}

let currentConfigNumberId = null;
let originalMode = null;

function viewNumberDetails(numberId) {
    console.log('[Admin Numbers] viewNumberDetails called with:', numberId);
    window.location.href = `/admin/assets/numbers/${numberId}/configure`;
}

function viewNumberDetailsOLD(numberId) {
    let num = numbersData.find(n => n.id === numberId);
    if (!num && typeof NumbersAdminService !== 'undefined') {
        num = NumbersAdminService._mockDb.numbers.find(n => n.id === numberId);
    }
    if (!num) {
        console.error('[Admin Numbers] Number not found:', numberId);
        return;
    }
    console.log('[Admin Numbers] Opening config for:', numberId, num);
    
    currentConfigNumberId = numberId;
    originalMode = num.mode;
    
    document.getElementById('cfg_number').textContent = num.number;
    document.getElementById('cfg_type').innerHTML = getTypeLabel(num.type);
    document.getElementById('cfg_status').innerHTML = getStatusBadge(num.status);
    document.getElementById('cfg_account').textContent = num.account;
    document.getElementById('cfg_subaccount').textContent = num.subAccount;
    document.getElementById('cfg_mode').innerHTML = getModeBadge(num.mode);
    document.getElementById('cfg_cost').textContent = `£${num.cost.toFixed(2)}`;
    document.getElementById('cfg_supplier').textContent = num.supplier;
    
    document.getElementById('cfg_network_supplier').textContent = num.supplier || '—';
    document.getElementById('cfg_network_route').textContent = num.route || '—';
    document.getElementById('cfg_network_destination').textContent = num.network || '—';
    
    const portedToRow = document.getElementById('portedToRow');
    const portedToValue = document.getElementById('cfg_ported_to');
    if (num.portedTo && num.country === 'UK') {
        portedToRow.style.display = 'flex';
        portedToValue.innerHTML = `<span class="ported-indicator"><i class="fas fa-exchange-alt"></i>${num.portedTo}</span>`;
    } else {
        portedToRow.style.display = 'none';
    }
    
    const isKeyword = num.type === 'shortcode_keyword';
    document.getElementById('keywordRestrictionAlert').style.display = isKeyword ? 'block' : 'none';
    
    if (isKeyword) {
        document.getElementById('sectionModeSelection').style.display = 'none';
        document.getElementById('sectionPortalConfig').style.display = 'none';
        document.getElementById('sectionAPIConfig').style.display = 'none';
    } else {
        document.getElementById('sectionModeSelection').style.display = 'block';
        
        if (num.mode === 'portal') {
            document.getElementById('configModePortal').checked = true;
            document.getElementById('sectionPortalConfig').style.display = 'block';
            document.getElementById('sectionAPIConfig').style.display = 'none';
        } else {
            document.getElementById('configModeAPI').checked = true;
            document.getElementById('sectionPortalConfig').style.display = 'none';
            document.getElementById('sectionAPIConfig').style.display = 'block';
        }
        
        document.getElementById('modeChangeWarning').style.display = 'none';
        
        document.getElementById('cap_portal_senderid').checked = num.capabilities.includes('senderid');
        document.getElementById('cap_portal_inbox').checked = num.capabilities.includes('inbox');
        document.getElementById('cap_portal_optout').checked = num.capabilities.includes('optout');
        
        document.getElementById('apiWebhookUrl').value = num.webhookUrl || '';
        document.getElementById('apiWebhookUrl').classList.remove('is-invalid');
    }
    
    new bootstrap.Modal(document.getElementById('configurationDrawer')).show();
    
    if (typeof ADMIN_AUDIT !== 'undefined') {
        ADMIN_AUDIT.logDataAccess('NUMBER_CONFIG_VIEWED', 'numbers', {
            numberId: numberId,
            number: num.number,
            account: num.account
        });
    }
}

function onModeChange(newMode) {
    const showWarning = newMode !== originalMode;
    document.getElementById('modeChangeWarning').style.display = showWarning ? 'block' : 'none';
    
    if (newMode === 'portal') {
        document.getElementById('sectionPortalConfig').style.display = 'block';
        document.getElementById('sectionAPIConfig').style.display = 'none';
    } else {
        document.getElementById('sectionPortalConfig').style.display = 'none';
        document.getElementById('sectionAPIConfig').style.display = 'block';
    }
}

function validateWebhookUrl() {
    const url = document.getElementById('apiWebhookUrl').value;
    const input = document.getElementById('apiWebhookUrl');
    
    if (url && !url.startsWith('https://')) {
        input.classList.add('is-invalid');
        return false;
    }
    input.classList.remove('is-invalid');
    return true;
}

function saveConfiguration() {
    const num = numbersData.find(n => n.id === currentConfigNumberId);
    if (!num) return;
    
    const isKeyword = num.type === 'shortcode_keyword';
    
    if (!isKeyword) {
        const selectedMode = document.querySelector('input[name="configMode"]:checked')?.value;
        
        if (selectedMode === 'api') {
            if (!validateWebhookUrl()) {
                alert('Please enter a valid HTTPS webhook URL.');
                return;
            }
            
            const apiSubAccount = document.getElementById('apiSubAccount').value;
            if (!apiSubAccount) {
                alert('API mode requires exactly one sub-account assignment.');
                return;
            }
            
            num.mode = 'api';
            num.subAccount = apiSubAccount;
            num.webhookUrl = document.getElementById('apiWebhookUrl').value;
            num.capabilities = ['api'];
        } else {
            const portalSubAccounts = [];
            document.querySelectorAll('#portalSubAccountsContainer input:checked').forEach(cb => {
                portalSubAccounts.push(cb.value);
            });
            
            if (portalSubAccounts.length === 0) {
                alert('Portal mode requires at least one sub-account assignment.');
                return;
            }
            
            num.mode = 'portal';
            num.subAccount = portalSubAccounts[0];
            
            const newCaps = [];
            if (document.getElementById('cap_portal_senderid').checked) newCaps.push('senderid');
            if (document.getElementById('cap_portal_inbox').checked) newCaps.push('inbox');
            if (document.getElementById('cap_portal_optout').checked) newCaps.push('optout');
            num.capabilities = newCaps;
        }
    }
    
    if (typeof ADMIN_AUDIT !== 'undefined') {
        if (num.mode !== originalMode) {
            ADMIN_AUDIT.logNumberModeChanged(
                currentConfigNumberId,
                num.number,
                num.account,
                num.account,
                originalMode,
                num.mode,
                'Configuration drawer save'
            );
        } else {
            ADMIN_AUDIT.logDataAccess('NUMBER_CONFIG_SAVED', 'numbers', {
                numberId: currentConfigNumberId,
                number: num.number,
                mode: num.mode
            });
        }
    }
    
    bootstrap.Modal.getInstance(document.getElementById('configurationDrawer')).hide();
    currentConfigNumberId = null;
    originalMode = null;
    
    applyFilters();
    showToast('Configuration saved successfully', 'success');
}

document.getElementById('apiWebhookUrl')?.addEventListener('blur', validateWebhookUrl);

function viewAuditTrail(numberId) {
    let num = numbersData.find(n => n.id === numberId);
    if (!num && typeof NumbersAdminService !== 'undefined') {
        num = NumbersAdminService._mockDb.numbers.find(n => n.id === numberId);
    }
    if (!num) {
        console.error('[Admin Numbers] Number not found for audit:', numberId);
        return;
    }
    
    const content = `
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            Audit trail for <strong>${num.number}</strong> (${num.account})
        </div>
        <div class="list-group">
            <div class="list-group-item">
                <div class="d-flex justify-content-between">
                    <strong>Number Provisioned</strong>
                    <small class="text-muted">${formatDate(num.created)}</small>
                </div>
                <small class="text-muted">Number added to platform via supplier ${num.supplier}</small>
            </div>
            <div class="list-group-item">
                <div class="d-flex justify-content-between">
                    <strong>Assigned to Account</strong>
                    <small class="text-muted">${formatDate(num.created)}</small>
                </div>
                <small class="text-muted">Assigned to ${num.account} / ${num.subAccount}</small>
            </div>
            <div class="list-group-item">
                <div class="d-flex justify-content-between">
                    <strong>Mode Set to ${num.mode.charAt(0).toUpperCase() + num.mode.slice(1)}</strong>
                    <small class="text-muted">${formatDate(num.created)}</small>
                </div>
                <small class="text-muted">Operating mode configured</small>
            </div>
        </div>
    `;
    
    document.getElementById('numberDetailsContent').innerHTML = content;
    document.querySelector('#numberDetailsModal .modal-title').innerHTML = '<i class="fas fa-history me-2"></i>Audit Trail';
    new bootstrap.Modal(document.getElementById('numberDetailsModal')).show();
}

async function exportNumbers() {
    try {
        const result = await NumbersAdminService.exportNumbers(appliedFilters, 'csv');
        
        if (typeof ADMIN_AUDIT !== 'undefined') {
            ADMIN_AUDIT.logDataAccess('NUMBERS_EXPORTED', 'numbers', {
                recordCount: result.recordCount || filteredData.length,
                format: 'CSV'
            });
        }
        
        showToast(`Export ready: ${result.recordCount || filteredData.length} records prepared for CSV download`, 'success');
    } catch (error) {
        console.error('[Admin Numbers] Export error:', error);
        showToast('Error exporting numbers', 'error');
    }
}

let pendingAction = null;

function confirmSuspend(numberId) {
    let num = numbersData.find(n => n.id === numberId);
    if (!num && typeof NumbersAdminService !== 'undefined') {
        num = NumbersAdminService._mockDb.numbers.find(n => n.id === numberId);
    }
    if (!num) return;
    
    pendingAction = { type: 'suspend', numberId, num };
    
    document.getElementById('confirmModalHeader').style.background = 'var(--admin-primary)';
    document.getElementById('confirmModalHeader').style.color = '#fff';
    document.getElementById('confirmModalTitle').textContent = 'Suspend Number';
    document.getElementById('confirmModalBody').innerHTML = `
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Warning:</strong> Suspending this number will immediately stop all messaging.
        </div>
        <p>Are you sure you want to suspend <strong>${num.number}</strong>?</p>
        <table class="table table-sm">
            <tr><td class="text-muted">Account</td><td>${num.account}</td></tr>
            <tr><td class="text-muted">Sub-Account</td><td>${num.subAccount}</td></tr>
            <tr><td class="text-muted">Type</td><td>${getTypeLabel(num.type)}</td></tr>
        </table>
        <div class="mb-3">
            <label class="form-label fw-bold">Reason for Suspension</label>
            <textarea class="form-control" id="actionReason" rows="2" placeholder="Enter reason (required)..."></textarea>
        </div>
    `;
    document.getElementById('confirmModalBtn').className = 'btn btn-warning';
    document.getElementById('confirmModalBtn').textContent = 'Suspend Number';
    
    new bootstrap.Modal(document.getElementById('confirmActionModal')).show();
}

function confirmReactivate(numberId) {
    let num = numbersData.find(n => n.id === numberId);
    if (!num && typeof NumbersAdminService !== 'undefined') {
        num = NumbersAdminService._mockDb.numbers.find(n => n.id === numberId);
    }
    if (!num) return;
    
    pendingAction = { type: 'reactivate', numberId, num };
    
    document.getElementById('confirmModalHeader').style.background = 'var(--admin-primary)';
    document.getElementById('confirmModalHeader').style.color = '#fff';
    document.getElementById('confirmModalTitle').textContent = 'Reactivate Number';
    document.getElementById('confirmModalBody').innerHTML = `
        <div class="alert alert-success">
            <i class="fas fa-check-circle me-2"></i>
            Reactivating this number will restore messaging capabilities.
        </div>
        <p>Are you sure you want to reactivate <strong>${num.number}</strong>?</p>
        <table class="table table-sm">
            <tr><td class="text-muted">Account</td><td>${num.account}</td></tr>
            <tr><td class="text-muted">Sub-Account</td><td>${num.subAccount}</td></tr>
        </table>
    `;
    document.getElementById('confirmModalBtn').className = 'btn btn-success';
    document.getElementById('confirmModalBtn').textContent = 'Reactivate Number';
    
    new bootstrap.Modal(document.getElementById('confirmActionModal')).show();
}

function confirmReturnToPool(numberId) {
    let num = numbersData.find(n => n.id === numberId);
    if (!num && typeof NumbersAdminService !== 'undefined') {
        num = NumbersAdminService._mockDb.numbers.find(n => n.id === numberId);
    }
    if (!num) return;
    
    pendingAction = { type: 'return_to_pool', numberId, num };
    
    document.getElementById('confirmModalHeader').style.background = '#dc3545';
    document.getElementById('confirmModalHeader').style.color = '#fff';
    document.getElementById('confirmModalTitle').textContent = 'Return Number to Pool';
    document.getElementById('confirmModalBody').innerHTML = `
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Warning:</strong> This action will unassign the number from its current customer and return it to the available pool.
        </div>
        <p>Are you sure you want to return <strong>${num.number}</strong> to the pool?</p>
        <table class="table table-sm">
            <tr><td class="text-muted">Current Account</td><td>${num.account}</td></tr>
            <tr><td class="text-muted">Sub-Account</td><td>${num.subAccount}</td></tr>
            <tr><td class="text-muted">Type</td><td>${getTypeLabel(num.type)}</td></tr>
        </table>
        <div class="mt-3">
            <label class="form-label">Reason for returning to pool <span class="text-danger">*</span></label>
            <textarea id="returnToPoolReason" class="form-control" rows="2" placeholder="Enter reason..." required></textarea>
        </div>
    `;
    document.getElementById('confirmModalBtn').className = 'btn btn-danger';
    document.getElementById('confirmModalBtn').textContent = 'Return to Pool';
    
    new bootstrap.Modal(document.getElementById('confirmActionModal')).show();
}

function confirmChangeMode(numberId, targetMode) {
    const num = numbersData.find(n => n.id === numberId);
    if (!num) return;
    
    pendingAction = { type: 'changeMode', numberId, num, targetMode };
    
    const isBillingImpact = true;
    
    document.getElementById('confirmModalHeader').style.background = 'var(--admin-primary)';
    document.getElementById('confirmModalHeader').style.color = '#fff';
    document.getElementById('confirmModalTitle').textContent = 'Change Operating Mode';
    document.getElementById('confirmModalBody').innerHTML = `
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            Changing mode will affect how this number can be used.
        </div>
        <p>Change <strong>${num.number}</strong> from <strong>${num.mode.charAt(0).toUpperCase() + num.mode.slice(1)}</strong> to <strong>${targetMode}</strong> mode?</p>
        <table class="table table-sm">
            <tr><td class="text-muted">Account</td><td>${num.account}</td></tr>
            <tr><td class="text-muted">Current Mode</td><td>${getModeBadge(num.mode)}</td></tr>
            <tr><td class="text-muted">New Mode</td><td><span class="badge badge-admin-${targetMode.toLowerCase()}">${targetMode}</span></td></tr>
        </table>
        ${isBillingImpact ? '<div class="alert alert-warning mt-3"><i class="fas fa-pound-sign me-2"></i>This change may affect billing.</div>' : ''}
    `;
    document.getElementById('confirmModalBtn').className = 'btn btn-primary';
    document.getElementById('confirmModalBtn').textContent = 'Change Mode';
    
    new bootstrap.Modal(document.getElementById('confirmActionModal')).show();
}

function confirmDisableKeyword(numberId) {
    const num = numbersData.find(n => n.id === numberId);
    if (!num) return;
    
    pendingAction = { type: 'disableKeyword', numberId, num };
    
    document.getElementById('confirmModalHeader').style.background = 'var(--admin-primary)';
    document.getElementById('confirmModalHeader').style.color = '#fff';
    document.getElementById('confirmModalTitle').textContent = 'Disable Keyword';
    document.getElementById('confirmModalBody').innerHTML = `
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle me-2"></i>
            <strong>Destructive Action:</strong> Disabling this keyword will stop all opt-out processing.
        </div>
        <p>Are you sure you want to disable keyword <strong>${num.number}</strong>?</p>
        <table class="table table-sm">
            <tr><td class="text-muted">Account</td><td>${num.account}</td></tr>
            <tr><td class="text-muted">Sub-Account</td><td>${num.subAccount}</td></tr>
        </table>
        <div class="mb-3">
            <label class="form-label fw-bold">Reason for Disabling</label>
            <textarea class="form-control" id="actionReason" rows="2" placeholder="Enter reason (required)..."></textarea>
        </div>
    `;
    document.getElementById('confirmModalBtn').className = 'btn btn-danger';
    document.getElementById('confirmModalBtn').textContent = 'Disable Keyword';
    
    new bootstrap.Modal(document.getElementById('confirmActionModal')).show();
}

async function executeConfirmedAction() {
    if (!pendingAction) return;
    
    const reason = document.getElementById('actionReason')?.value || '';
    
    if (['suspend', 'disableKeyword'].includes(pendingAction.type) && !reason.trim()) {
        alert('Please provide a reason for this action.');
        return;
    }
    
    const num = pendingAction.num;
    let result;
    
    try {
        switch (pendingAction.type) {
            case 'suspend':
                result = await NumbersAdminService.suspendNumber(pendingAction.numberId, reason);
                break;
            case 'reactivate':
                result = await NumbersAdminService.reactivateNumber(pendingAction.numberId, reason);
                break;
            case 'changeMode':
                result = await NumbersAdminService.changeMode(pendingAction.numberId, pendingAction.targetMode, reason);
                break;
            case 'disableKeyword':
                result = await NumbersAdminService.disableKeyword(pendingAction.numberId, reason);
                break;
        }
        
        if (result && result.success) {
            if (typeof ADMIN_AUDIT !== 'undefined') {
                switch (pendingAction.type) {
                    case 'suspend':
                        ADMIN_AUDIT.logNumberSuspended(
                            pendingAction.numberId,
                            num.number,
                            num.account,
                            num.account,
                            reason
                        );
                        break;
                    case 'reactivate':
                        ADMIN_AUDIT.logNumberReactivated(
                            pendingAction.numberId,
                            num.number,
                            num.account,
                            num.account,
                            reason
                        );
                        break;
                    case 'changeMode':
                        ADMIN_AUDIT.logNumberModeChanged(
                            pendingAction.numberId,
                            num.number,
                            num.account,
                            num.account,
                            result.changes.before.mode,
                            result.changes.after.mode,
                            reason
                        );
                        break;
                    case 'disableKeyword':
                        ADMIN_AUDIT.logNumberAction('KEYWORD_DISABLED', {
                            numberId: pendingAction.numberId,
                            number: num.number,
                            accountId: num.account,
                            accountName: num.account,
                            before: result.changes.before,
                            after: result.changes.after,
                            reason: reason
                        });
                        break;
                }
            }
            
            bootstrap.Modal.getInstance(document.getElementById('confirmActionModal')).hide();
            pendingAction = null;
            
            await loadNumbersData();
            showToast('Action completed successfully', 'success');
        } else {
            showToast('Action failed: ' + (result?.error || 'Unknown error'), 'error');
        }
    } catch (error) {
        console.error('[Admin Numbers] Action error:', error);
        showToast('Error executing action: ' + error.message, 'error');
    }
}

let currentReassignNumberId = null;

function openReassignModal(numberId) {
    const num = numbersData.find(n => n.id === numberId);
    if (!num) return;
    
    currentReassignNumberId = numberId;
    
    document.getElementById('reassignCurrentInfo').innerHTML = `
        <div class="bg-light p-3 rounded">
            <strong>Current Assignment:</strong><br>
            <span class="text-muted">Number:</span> ${num.number}<br>
            <span class="text-muted">Account:</span> ${num.account}<br>
            <span class="text-muted">Sub-Account:</span> ${num.subAccount}
        </div>
    `;
    
    document.getElementById('reassignAccount').value = '';
    document.getElementById('reassignSubAccount').value = '';
    document.getElementById('reassignReason').value = '';
    
    new bootstrap.Modal(document.getElementById('reassignModal')).show();
}

async function executeReassign() {
    const newAccountId = document.getElementById('reassignAccount').value;
    const newSubAccountId = document.getElementById('reassignSubAccount').value;
    const reason = document.getElementById('reassignReason').value;
    
    if (!newAccountId || !newSubAccountId) {
        alert('Please select both a customer account and sub-account.');
        return;
    }
    if (!reason.trim()) {
        alert('Please provide a reason for the reassignment.');
        return;
    }
    
    try {
        const result = await NumbersAdminService.reassignNumber(
            currentReassignNumberId, 
            newAccountId, 
            newSubAccountId, 
            reason
        );
        
        if (result.success) {
            if (typeof ADMIN_AUDIT !== 'undefined') {
                ADMIN_AUDIT.logNumberReassigned(
                    currentReassignNumberId,
                    result.data.number,
                    result.changes.before.account,
                    result.changes.after.account,
                    result.changes.before.subAccount,
                    result.changes.after.subAccount,
                    reason
                );
            }
            
            bootstrap.Modal.getInstance(document.getElementById('reassignModal')).hide();
            currentReassignNumberId = null;
            
            await loadNumbersData();
            showToast('Number reassigned successfully', 'success');
        } else {
            showToast('Reassignment failed: ' + result.error, 'error');
        }
    } catch (error) {
        console.error('[Admin Numbers] Reassign error:', error);
        showToast('Error reassigning number: ' + error.message, 'error');
    }
}

let currentCapabilitiesNumberId = null;

function openEditCapabilities(numberId) {
    const num = numbersData.find(n => n.id === numberId);
    if (!num) return;
    
    currentCapabilitiesNumberId = numberId;
    
    document.getElementById('capabilitiesNumberInfo').innerHTML = `
        <div class="bg-light p-3 rounded">
            <strong>${num.number}</strong> (${num.account})
        </div>
    `;
    
    const isKeyword = num.type === 'shortcode_keyword';
    const rulesAlert = document.getElementById('capabilitiesRulesAlert');
    const rulesText = document.getElementById('capabilitiesRulesText');
    
    if (isKeyword) {
        rulesAlert.style.display = 'block';
        rulesText.textContent = 'Shared Shortcode Keywords cannot have SenderID or Inbox capabilities. Only Opt-out and API are allowed.';
    } else {
        rulesAlert.style.display = 'none';
    }
    
    const allCapabilities = [
        { id: 'senderid', label: 'SenderID', desc: 'Use as sender ID for outbound messages', disabled: isKeyword },
        { id: 'inbox', label: 'Inbox', desc: 'Receive inbound messages', disabled: isKeyword },
        { id: 'optout', label: 'Opt-out', desc: 'Handle opt-out/unsubscribe requests', disabled: false },
        { id: 'api', label: 'API', desc: 'Available via API integration', disabled: false }
    ];
    
    document.getElementById('capabilityToggles').innerHTML = allCapabilities.map(cap => `
        <div class="form-check form-switch mb-2 ${cap.disabled ? 'opacity-50' : ''}">
            <input class="form-check-input" type="checkbox" id="cap_toggle_${cap.id}" 
                   ${num.capabilities.includes(cap.id) ? 'checked' : ''} 
                   ${cap.disabled ? 'disabled' : ''}>
            <label class="form-check-label" for="cap_toggle_${cap.id}">
                <strong>${cap.label}</strong>
                <small class="d-block text-muted">${cap.desc}</small>
            </label>
        </div>
    `).join('');
    
    new bootstrap.Modal(document.getElementById('editCapabilitiesModal')).show();
}

async function saveCapabilities() {
    const newCaps = [];
    ['senderid', 'inbox', 'optout', 'api'].forEach(cap => {
        const toggle = document.getElementById(`cap_toggle_${cap}`);
        if (toggle && toggle.checked && !toggle.disabled) {
            newCaps.push(cap);
        }
    });
    
    try {
        const result = await NumbersAdminService.updateCapabilities(
            currentCapabilitiesNumberId,
            newCaps,
            'Admin capability update'
        );
        
        if (result.success) {
            if (typeof ADMIN_AUDIT !== 'undefined') {
                ADMIN_AUDIT.logNumberCapabilityChanged(
                    currentCapabilitiesNumberId,
                    result.data.number,
                    result.data.account,
                    result.data.account,
                    result.changes.before.capabilities,
                    result.changes.after.capabilities
                );
            }
            
            bootstrap.Modal.getInstance(document.getElementById('editCapabilitiesModal')).hide();
            currentCapabilitiesNumberId = null;
            
            await loadNumbersData();
            showToast('Capabilities updated successfully', 'success');
        } else {
            showToast('Update failed: ' + result.error, 'error');
        }
    } catch (error) {
        console.error('[Admin Numbers] Capabilities error:', error);
        showToast('Error updating capabilities: ' + error.message, 'error');
    }
}

function openReassignSubAccountOnly(numberId) {
    openReassignModal(numberId);
}

let currentOptoutNumberId = null;

function openOptoutRouting(numberId) {
    const num = numbersData.find(n => n.id === numberId);
    if (!num) return;
    
    currentOptoutNumberId = numberId;
    
    document.getElementById('optoutKeywordInfo').innerHTML = `
        <div class="bg-light p-3 rounded">
            <strong>Keyword:</strong> ${num.number}<br>
            <span class="text-muted">Account:</span> ${num.account} / ${num.subAccount}
        </div>
    `;
    
    new bootstrap.Modal(document.getElementById('optoutRoutingModal')).show();
}

async function saveOptoutRouting() {
    const keywords = document.getElementById('optoutKeywords').value;
    const reply = document.getElementById('optoutReply').value;
    const forward = document.getElementById('optoutForward').value;
    
    const routingConfig = {
        keywords: keywords,
        reply: reply,
        forward: forward || null
    };
    
    try {
        const result = await NumbersAdminService.updateOptoutRouting(
            currentOptoutNumberId,
            routingConfig,
            'Admin opt-out routing update'
        );
        
        if (result.success) {
            if (typeof ADMIN_AUDIT !== 'undefined') {
                ADMIN_AUDIT.logOptoutRoutingChanged(
                    currentOptoutNumberId,
                    result.data.number,
                    result.data.account,
                    result.data.account,
                    result.changes.before.optoutConfig,
                    result.changes.after.optoutConfig
                );
            }
            
            bootstrap.Modal.getInstance(document.getElementById('optoutRoutingModal')).hide();
            currentOptoutNumberId = null;
            
            await loadNumbersData();
            showToast('Opt-out routing updated successfully', 'success');
        } else {
            showToast('Update failed: ' + result.error, 'error');
        }
    } catch (error) {
        console.error('[Admin Numbers] Optout routing error:', error);
        showToast('Error updating opt-out routing: ' + error.message, 'error');
    }
}

function openSubAccountAssign(numberId) {
    const num = numbersData.find(n => n.id === numberId);
    if (!num) return;
    
    alert(`TODO: Open Sub-Account assignment modal for ${num.number}\nThis would allow assigning/removing sub-accounts in Portal mode.`);
}

function openOverrideUsage(numberId) {
    const num = numbersData.find(n => n.id === numberId);
    if (!num) return;
    
    alert(`TODO: Open Override Default Usage modal for ${num.number}\nThis would allow overriding default usage settings in Portal mode.`);
}

function showToast(message, type = 'info') {
    const toastHtml = `
        <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'primary'} border-0 position-fixed" 
             role="alert" style="top: 20px; right: 20px; z-index: 9999;">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'} me-2"></i>
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;
    
    const container = document.createElement('div');
    container.innerHTML = toastHtml;
    document.body.appendChild(container.firstElementChild);
    
    const toast = document.body.lastElementChild;
    const bsToast = new bootstrap.Toast(toast, { delay: 3000 });
    bsToast.show();
    
    toast.addEventListener('hidden.bs.toast', () => toast.remove());
}

function toggleRowSelection(id) {
    if (selectedRows.has(id)) {
        selectedRows.delete(id);
    } else {
        selectedRows.add(id);
    }
    updateSelectionUI();
}

function toggleSelectAll() {
    const checkbox = document.getElementById('selectAllCheckbox');
    const start = (currentPage - 1) * rowsPerPage;
    const end = Math.min(start + rowsPerPage, filteredData.length);
    const pageData = filteredData.slice(start, end);
    
    if (checkbox.checked) {
        pageData.forEach(num => selectedRows.add(num.id));
    } else {
        pageData.forEach(num => selectedRows.delete(num.id));
    }
    
    updateSelectionUI();
}

function updateSelectAllState() {
    const start = (currentPage - 1) * rowsPerPage;
    const end = Math.min(start + rowsPerPage, filteredData.length);
    const pageData = filteredData.slice(start, end);
    const checkbox = document.getElementById('selectAllCheckbox');
    
    if (pageData.length === 0) {
        checkbox.checked = false;
        checkbox.indeterminate = false;
        return;
    }
    
    const selectedOnPage = pageData.filter(num => selectedRows.has(num.id)).length;
    
    if (selectedOnPage === 0) {
        checkbox.checked = false;
        checkbox.indeterminate = false;
    } else if (selectedOnPage === pageData.length) {
        checkbox.checked = true;
        checkbox.indeterminate = false;
    } else {
        checkbox.checked = false;
        checkbox.indeterminate = true;
    }
}

function updateSelectionUI() {
    document.querySelectorAll('#numbersTableBody tr').forEach(row => {
        const id = row.dataset.id;
        const checkbox = row.querySelector('.row-checkbox');
        if (selectedRows.has(id)) {
            row.classList.add('selected-row');
            if (checkbox) checkbox.checked = true;
        } else {
            row.classList.remove('selected-row');
            if (checkbox) checkbox.checked = false;
        }
    });
    
    const bulkBar = document.getElementById('bulkActionsBar');
    const countSpan = document.getElementById('selectedCount');
    
    if (selectedRows.size > 0) {
        bulkBar.style.display = 'flex';
        countSpan.textContent = selectedRows.size;
    } else {
        bulkBar.style.display = 'none';
    }
    
    updateSelectAllState();
    updateBulkActionAvailability();
}

function clearSelection() {
    selectedRows.clear();
    updateSelectionUI();
}

function updateBulkActionAvailability() {
    const selected = getSelectedNumbersData();
    
    const canSuspend = selected.filter(n => n.status === 'active').length;
    const canReactivate = selected.filter(n => n.status === 'suspended').length;
    const canChangeMode = selected.filter(n => n.type !== 'keyword').length;
    const canChangeCapabilities = selected.filter(n => n.type !== 'keyword').length;
    
    const suspendBtn = document.getElementById('bulkSuspendBtn');
    const reactivateBtn = document.getElementById('bulkReactivateBtn');
    const changeModeBtn = document.getElementById('bulkChangeModeBtn');
    const capabilitiesBtn = document.getElementById('bulkCapabilitiesBtn');
    
    suspendBtn.classList.toggle('disabled', canSuspend === 0);
    reactivateBtn.classList.toggle('disabled', canReactivate === 0);
    changeModeBtn.classList.toggle('disabled', canChangeMode === 0);
    capabilitiesBtn.classList.toggle('disabled', canChangeCapabilities === 0);
}

function getSelectedNumbersData() {
    return Array.from(selectedRows).map(id => numbersData.find(n => n.id === id)).filter(Boolean);
}

function initBulkAction(actionType) {
    const selected = getSelectedNumbersData();
    if (selected.length === 0) return;
    
    currentBulkAction = actionType;
    
    const modal = document.getElementById('bulkActionModal');
    const titleEl = document.getElementById('bulkModalTitle');
    const summaryEl = document.getElementById('bulkActionSummary');
    const optionsEl = document.getElementById('bulkActionOptions');
    const billingWarning = document.getElementById('bulkBillingWarning');
    const billingChangesSummary = document.getElementById('billingChangesSummary');
    const billingCheckbox = document.getElementById('billingConfirmCheckbox');
    const incompatibleWarning = document.getElementById('bulkIncompatibleWarning');
    const applyCountEl = document.getElementById('bulkApplyCount');
    const executeBtn = document.getElementById('bulkExecuteBtn');
    
    billingWarning.style.display = 'none';
    billingCheckbox.checked = false;
    incompatibleWarning.style.display = 'none';
    optionsEl.innerHTML = '';
    billingChangesSummary.innerHTML = '';
    
    let compatible = selected;
    let incompatible = [];
    let title = 'Bulk Action';
    let summary = '';
    let hasBillingImpact = false;
    let billingChanges = [];
    
    switch(actionType) {
        case 'suspend':
            title = 'Suspend Numbers';
            compatible = selected.filter(n => n.status === 'active');
            incompatible = selected.filter(n => n.status !== 'active');
            summary = buildBulkSummaryTable(compatible, 'Status', n => getStatusBadge(n.status), () => '<span class="badge bg-warning">Suspended</span>');
            hasBillingImpact = true;
            billingChanges = [
                { label: 'Status Change', before: 'Active', after: 'Suspended' },
                { label: 'Billing Effect', before: 'Full monthly rate', after: 'Suspended (no charge)' }
            ];
            break;
            
        case 'reactivate':
            title = 'Reactivate Numbers';
            compatible = selected.filter(n => n.status === 'suspended');
            incompatible = selected.filter(n => n.status !== 'suspended');
            summary = buildBulkSummaryTable(compatible, 'Status', n => getStatusBadge(n.status), () => '<span class="badge bg-success">Active</span>');
            hasBillingImpact = true;
            billingChanges = [
                { label: 'Status Change', before: 'Suspended', after: 'Active' },
                { label: 'Billing Effect', before: 'No charge', after: 'Full monthly rate resumes' }
            ];
            break;
            
        case 'assignCustomer':
            title = 'Assign to Customer';
            compatible = selected;
            summary = buildBulkSummaryTable(compatible, 'Account', n => n.account, () => '<em>Select below</em>');
            hasBillingImpact = true;
            const uniqueAccounts = [...new Set(compatible.map(n => n.account))];
            billingChanges = [
                { label: 'Account Attribution', before: uniqueAccounts.length > 1 ? 'Multiple accounts' : uniqueAccounts[0], after: 'New customer account' },
                { label: 'Invoice Allocation', before: 'Current customer', after: 'New customer' }
            ];
            optionsEl.innerHTML = `
                <div class="mb-3">
                    <label class="form-label fw-bold">Select Customer Account</label>
                    <select class="form-select" id="bulkCustomerSelect" onchange="updateBillingPreview('assignCustomer')">
                        <option value="">Choose account...</option>
                        <option value="Acme Corp">Acme Corp</option>
                        <option value="TechStart Ltd">TechStart Ltd</option>
                        <option value="RetailMax">RetailMax</option>
                        <option value="Healthcare Plus">Healthcare Plus</option>
                        <option value="Finance Ltd">Finance Ltd</option>
                    </select>
                </div>
            `;
            break;
            
        case 'assignSubAccount':
            title = 'Assign to Sub-Account';
            compatible = selected;
            summary = buildBulkSummaryTable(compatible, 'Sub-Account', n => n.subAccount, () => '<em>Select below</em>');
            hasBillingImpact = true;
            const uniqueSubAccounts = [...new Set(compatible.map(n => n.subAccount))];
            billingChanges = [
                { label: 'Sub-Account', before: uniqueSubAccounts.length > 1 ? 'Multiple sub-accounts' : uniqueSubAccounts[0], after: 'New sub-account' },
                { label: 'Cost Centre', before: 'Current allocation', after: 'New allocation' }
            ];
            optionsEl.innerHTML = `
                <div class="mb-3">
                    <label class="form-label fw-bold">Select Customer Account First</label>
                    <select class="form-select mb-2" id="bulkParentAccount" onchange="loadSubAccounts()">
                        <option value="">Choose account...</option>
                        <option value="Acme Corp">Acme Corp</option>
                        <option value="TechStart Ltd">TechStart Ltd</option>
                        <option value="RetailMax">RetailMax</option>
                    </select>
                </div>
                <div class="mb-3" id="subAccountContainer" style="display: none;">
                    <label class="form-label fw-bold">Select Sub-Account</label>
                    <select class="form-select" id="bulkSubAccountSelect" onchange="updateBillingPreview('assignSubAccount')">
                        <option value="">Choose sub-account...</option>
                    </select>
                </div>
            `;
            break;
            
        case 'changeMode':
            title = 'Change Mode';
            compatible = selected.filter(n => n.type !== 'keyword');
            incompatible = selected.filter(n => n.type === 'keyword');
            summary = buildBulkSummaryTable(compatible, 'Mode', n => getModeBadge(n.mode), () => '<em>Select below</em>');
            hasBillingImpact = true;
            const portalCount = compatible.filter(n => n.mode === 'portal').length;
            const apiCount = compatible.filter(n => n.mode === 'api').length;
            billingChanges = [
                { label: 'Mode Change', before: `${portalCount} Portal, ${apiCount} API`, after: 'Select mode below' },
                { label: 'Billing Model', before: 'Current rates apply', after: 'Mode-specific rates apply' }
            ];
            optionsEl.innerHTML = `
                <div class="mb-3">
                    <label class="form-label fw-bold">Select New Mode</label>
                    <div class="btn-group w-100" role="group">
                        <input type="radio" class="btn-check" name="bulkMode" id="bulkModePortal" value="portal" onchange="updateBillingPreview('changeMode')">
                        <label class="btn btn-outline-primary" for="bulkModePortal">
                            <i class="fas fa-desktop me-2"></i>Portal Mode
                        </label>
                        <input type="radio" class="btn-check" name="bulkMode" id="bulkModeAPI" value="api" onchange="updateBillingPreview('changeMode')">
                        <label class="btn btn-outline-primary" for="bulkModeAPI">
                            <i class="fas fa-code me-2"></i>API Mode
                        </label>
                    </div>
                </div>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Note:</strong> API Mode is limited to 1 sub-account and requires HTTPS webhook URL.
                </div>
            `;
            break;
            
        case 'capabilities':
            title = 'Apply Capability Toggles';
            compatible = selected.filter(n => n.type !== 'keyword');
            incompatible = selected.filter(n => n.type === 'keyword');
            summary = buildBulkSummaryTable(compatible, 'Type', n => getTypeLabel(n.type), null);
            hasBillingImpact = false;
            optionsEl.innerHTML = `
                <div class="mb-3">
                    <label class="form-label fw-bold">Select Capabilities to Apply</label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="bulkCapSenderID" value="senderid">
                        <label class="form-check-label" for="bulkCapSenderID">SenderID</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="bulkCapInbox" value="inbox">
                        <label class="form-check-label" for="bulkCapInbox">Inbox</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="bulkCapOptout" value="optout">
                        <label class="form-check-label" for="bulkCapOptout">Opt-out</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="bulkCapAPI" value="api">
                        <label class="form-check-label" for="bulkCapAPI">API</label>
                    </div>
                </div>
                <div class="alert alert-secondary">
                    <small><i class="fas fa-info-circle me-2"></i>Selected capabilities will replace existing ones on all compatible numbers.</small>
                </div>
            `;
            break;
            
        case 'returnToPool':
            title = 'Return Numbers to Pool';
            compatible = selected.filter(n => n.accountId !== null && n.account !== 'Unassigned Pool');
            incompatible = selected.filter(n => n.accountId === null || n.account === 'Unassigned Pool');
            summary = buildBulkSummaryTable(compatible, 'Account', n => n.account, () => '<span class="text-muted">Unassigned Pool</span>');
            hasBillingImpact = true;
            billingChanges = [
                { label: 'Account Attribution', before: 'Current customer', after: 'Unassigned Pool' },
                { label: 'Billing Effect', before: 'Customer billed', after: 'Platform inventory (no customer billing)' }
            ];
            optionsEl.innerHTML = `
                <div class="mb-3">
                    <label class="form-label fw-bold">Reason for Returning to Pool <span class="text-danger">*</span></label>
                    <textarea id="bulkReturnReason" class="form-control" rows="2" placeholder="Enter reason for returning numbers to pool..." required></textarea>
                    <small class="text-muted">This will be recorded in the audit trail</small>
                </div>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Warning:</strong> This action will unassign these numbers from their current customers and return them to the available inventory pool.
                </div>
            `;
            break;
    }
    
    titleEl.innerHTML = `<i class="fas fa-tasks me-2"></i>${title}`;
    summaryEl.innerHTML = summary;
    applyCountEl.textContent = compatible.length;
    
    if (incompatible.length > 0) {
        incompatibleWarning.style.display = 'block';
        document.getElementById('bulkIncompatibleText').innerHTML = 
            `<strong>${incompatible.length} number(s)</strong> are not compatible with this action and will be skipped.`;
    }
    
    if (hasBillingImpact && compatible.length > 0) {
        billingWarning.style.display = 'block';
        billingChangesSummary.innerHTML = billingChanges.map(change => `
            <div class="billing-change-item">
                <span class="change-label">${change.label}</span>
                <div class="change-values">
                    <span class="change-before">${change.before}</span>
                    <span class="change-arrow">→</span>
                    <span class="change-after">${change.after}</span>
                </div>
            </div>
        `).join('');
        executeBtn.disabled = true;
    } else {
        executeBtn.disabled = compatible.length === 0;
    }
    
    new bootstrap.Modal(modal).show();
}

function updateBillingPreview(actionType) {
    const billingChangesSummary = document.getElementById('billingChangesSummary');
    const selected = getSelectedNumbersData();
    
    switch(actionType) {
        case 'assignCustomer':
            const customer = document.getElementById('bulkCustomerSelect')?.value || 'Not selected';
            const uniqueAccounts = [...new Set(selected.map(n => n.account))];
            billingChangesSummary.innerHTML = `
                <div class="billing-change-item">
                    <span class="change-label">Account Attribution</span>
                    <div class="change-values">
                        <span class="change-before">${uniqueAccounts.length > 1 ? 'Multiple accounts' : uniqueAccounts[0]}</span>
                        <span class="change-arrow">→</span>
                        <span class="change-after">${customer || 'Not selected'}</span>
                    </div>
                </div>
                <div class="billing-change-item">
                    <span class="change-label">Invoice Allocation</span>
                    <div class="change-values">
                        <span class="change-before">Current customer</span>
                        <span class="change-arrow">→</span>
                        <span class="change-after">${customer || 'Not selected'}</span>
                    </div>
                </div>
            `;
            break;
            
        case 'assignSubAccount':
            const subAccount = document.getElementById('bulkSubAccountSelect')?.value || 'Not selected';
            const uniqueSubAccounts = [...new Set(selected.map(n => n.subAccount))];
            billingChangesSummary.innerHTML = `
                <div class="billing-change-item">
                    <span class="change-label">Sub-Account</span>
                    <div class="change-values">
                        <span class="change-before">${uniqueSubAccounts.length > 1 ? 'Multiple' : uniqueSubAccounts[0]}</span>
                        <span class="change-arrow">→</span>
                        <span class="change-after">${subAccount || 'Not selected'}</span>
                    </div>
                </div>
                <div class="billing-change-item">
                    <span class="change-label">Cost Centre</span>
                    <div class="change-values">
                        <span class="change-before">Current allocation</span>
                        <span class="change-arrow">→</span>
                        <span class="change-after">${subAccount || 'Not selected'}</span>
                    </div>
                </div>
            `;
            break;
            
        case 'changeMode':
            const mode = document.querySelector('input[name="bulkMode"]:checked')?.value;
            const modeLabel = mode === 'portal' ? 'Portal Mode' : mode === 'api' ? 'API Mode' : 'Not selected';
            const compatible = selected.filter(n => n.type !== 'keyword');
            const portalCount = compatible.filter(n => n.mode === 'portal').length;
            const apiCount = compatible.filter(n => n.mode === 'api').length;
            billingChangesSummary.innerHTML = `
                <div class="billing-change-item">
                    <span class="change-label">Mode Change</span>
                    <div class="change-values">
                        <span class="change-before">${portalCount} Portal, ${apiCount} API</span>
                        <span class="change-arrow">→</span>
                        <span class="change-after">${modeLabel}</span>
                    </div>
                </div>
                <div class="billing-change-item">
                    <span class="change-label">Billing Model</span>
                    <div class="change-values">
                        <span class="change-before">Current rates</span>
                        <span class="change-arrow">→</span>
                        <span class="change-after">${modeLabel} rates</span>
                    </div>
                </div>
            `;
            break;
    }
}

function updateBillingConfirmState() {
    const checkbox = document.getElementById('billingConfirmCheckbox');
    const executeBtn = document.getElementById('bulkExecuteBtn');
    const billingPanel = document.getElementById('bulkBillingWarning');
    const selected = getSelectedNumbersData();
    
    if (billingPanel.style.display === 'none') {
        executeBtn.disabled = selected.length === 0;
    } else {
        executeBtn.disabled = !checkbox.checked;
    }
}

function buildBulkSummaryTable(items, changeColumn, currentFn, newFn) {
    if (items.length === 0) {
        return '<div class="alert alert-warning">No compatible numbers selected for this action.</div>';
    }
    
    const showMax = 5;
    const displayItems = items.slice(0, showMax);
    const remaining = items.length - showMax;
    
    let html = `
        <div class="mb-2"><strong>${items.length}</strong> number(s) will be affected:</div>
        <div class="table-responsive" style="max-height: 200px; overflow-y: auto;">
            <table class="table table-sm bulk-summary-table mb-0">
                <thead>
                    <tr>
                        <th>Number</th>
                        <th>Account</th>
                        <th>Current ${changeColumn}</th>
                        ${newFn ? `<th></th><th>New ${changeColumn}</th>` : ''}
                    </tr>
                </thead>
                <tbody>
    `;
    
    displayItems.forEach(item => {
        html += `
            <tr>
                <td><code>${item.number}</code></td>
                <td>${item.account}</td>
                <td>${currentFn(item)}</td>
                ${newFn ? `<td class="bulk-change-arrow">→</td><td>${newFn(item)}</td>` : ''}
            </tr>
        `;
    });
    
    html += '</tbody></table></div>';
    
    if (remaining > 0) {
        html += `<div class="text-muted small mt-2">...and ${remaining} more</div>`;
    }
    
    return html;
}

function loadSubAccounts() {
    const parent = document.getElementById('bulkParentAccount').value;
    const container = document.getElementById('subAccountContainer');
    const select = document.getElementById('bulkSubAccountSelect');
    
    if (!parent) {
        container.style.display = 'none';
        return;
    }
    
    const subAccounts = {
        'Acme Corp': ['Marketing', 'Sales', 'Support', 'Operations'],
        'TechStart Ltd': ['Development', 'QA', 'DevOps'],
        'RetailMax': ['Stores', 'eCommerce', 'Logistics']
    };
    
    select.innerHTML = '<option value="">Choose sub-account...</option>';
    (subAccounts[parent] || []).forEach(sa => {
        select.innerHTML += `<option value="${sa}">${sa}</option>`;
    });
    
    container.style.display = 'block';
}

async function executeBulkAction() {
    const selected = getSelectedNumbersData();
    let compatible = selected;
    let result;
    
    try {
        switch(currentBulkAction) {
            case 'suspend':
                compatible = selected.filter(n => n.status === 'active');
                result = await NumbersAdminService.bulkSuspend(
                    compatible.map(n => n.id),
                    'Bulk admin suspend action'
                );
                break;
                
            case 'reactivate':
                compatible = selected.filter(n => n.status === 'suspended');
                result = await NumbersAdminService.bulkReactivate(
                    compatible.map(n => n.id),
                    'Bulk admin reactivate action'
                );
                break;
                
            case 'assignCustomer':
                const customer = document.getElementById('bulkCustomerSelect').value;
                if (!customer) {
                    showToast('Please select a customer account', 'error');
                    return;
                }
                const subAccountForCustomer = document.getElementById('bulkSubAccountSelect')?.value;
                result = await NumbersAdminService.bulkReassign(
                    compatible.map(n => n.id),
                    customer,
                    subAccountForCustomer || 'SUB-001',
                    'Bulk admin reassign action'
                );
                break;
                
            case 'assignSubAccount':
                const subAccount = document.getElementById('bulkSubAccountSelect').value;
                if (!subAccount) {
                    showToast('Please select a sub-account', 'error');
                    return;
                }
                result = await NumbersAdminService.bulkReassign(
                    compatible.map(n => n.id),
                    compatible[0]?.accountId || 'ACC-001',
                    subAccount,
                    'Bulk admin sub-account assignment'
                );
                break;
                
            case 'changeMode':
                const mode = document.querySelector('input[name="bulkMode"]:checked')?.value;
                if (!mode) {
                    showToast('Please select a mode', 'error');
                    return;
                }
                compatible = selected.filter(n => n.type !== 'shortcode_keyword');
                result = await NumbersAdminService.bulkChangeMode(
                    compatible.map(n => n.id),
                    mode,
                    'Bulk admin mode change'
                );
                break;
                
            case 'capabilities':
                compatible = selected.filter(n => n.type !== 'shortcode_keyword');
                const caps = [];
                if (document.getElementById('bulkCapSenderID').checked) caps.push('senderid');
                if (document.getElementById('bulkCapInbox').checked) caps.push('inbox');
                if (document.getElementById('bulkCapOptout').checked) caps.push('optout');
                if (document.getElementById('bulkCapAPI').checked) caps.push('api');
                
                if (caps.length === 0) {
                    showToast('Please select at least one capability', 'error');
                    return;
                }
                result = await NumbersAdminService.bulkUpdateCapabilities(
                    compatible.map(n => n.id),
                    caps,
                    'Bulk admin capability update'
                );
                break;
                
            case 'returnToPool':
                compatible = selected.filter(n => n.accountId !== null && n.account !== 'Unassigned Pool');
                const returnReason = document.getElementById('bulkReturnReason')?.value;
                if (!returnReason || returnReason.trim().length < 5) {
                    showToast('Please provide a reason for returning numbers to pool (min 5 characters)', 'error');
                    return;
                }
                result = await NumbersAdminService.bulkReturnToPool(
                    compatible.map(n => n.id),
                    returnReason
                );
                break;
        }
        
        if (result) {
            if (typeof ADMIN_AUDIT !== 'undefined') {
                const eventTypeMap = {
                    'suspend': 'NUMBER_SUSPENDED',
                    'reactivate': 'NUMBER_REACTIVATED',
                    'assignCustomer': 'NUMBER_REASSIGNED',
                    'assignSubAccount': 'NUMBER_REASSIGNED',
                    'changeMode': 'NUMBER_MODE_CHANGED',
                    'capabilities': 'NUMBER_CAPABILITY_CHANGED',
                    'returnToPool': 'NUMBER_RETURNED_TO_POOL'
                };
                ADMIN_AUDIT.logBulkNumberAction(
                    eventTypeMap[currentBulkAction] || currentBulkAction.toUpperCase(),
                    compatible,
                    null
                );
            }
            
            bootstrap.Modal.getInstance(document.getElementById('bulkActionModal')).hide();
            
            await loadNumbersData();
            clearSelection();
            
            if (result.success) {
                showToast(`Successfully applied ${currentBulkAction} to ${result.successCount} number(s)`, 'success');
            } else {
                showToast(`Bulk action completed with ${result.failedCount} failures`, 'warning');
            }
        }
        
        currentBulkAction = null;
    } catch (error) {
        console.error('[Admin Numbers] Bulk action error:', error);
        showToast('Error executing bulk action: ' + error.message, 'error');
    }
}

function setupFilterButton() {
    const filterBtn = document.getElementById('filterPillBtn');
    if (filterBtn) {
        filterBtn.removeAttribute('onclick');
        filterBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('[Admin Numbers] Filter button clicked via event listener');
            toggleFilterPanel();
        });
        console.log('[Admin Numbers] Filter button handler attached');
    } else {
        console.error('[Admin Numbers] Filter button not found!');
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        initNumbersPage();
        setupFilterButton();
    });
} else {
    initNumbersPage();
    setupFilterButton();
}
</script>
@endpush
