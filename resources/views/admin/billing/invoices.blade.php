@extends('layouts.admin')

@section('title', 'Invoices (All Clients)')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/quicksms-pastel.css') }}">
<style>
:root {
    --admin-primary: #1e3a5f;
    --admin-primary-light: rgba(30, 58, 95, 0.1);
    --admin-primary-hover: #2a4a73;
}
.invoices-container {
    height: calc(100vh - 120px);
    display: flex;
    flex-direction: column;
    overflow: hidden;
}
.invoices-container .card {
    flex: 1;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    margin-bottom: 0 !important;
}
.invoices-container .card-body {
    flex: 1;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    padding-bottom: 0;
}
.invoices-fixed-header {
    flex-shrink: 0;
    overflow: visible;
}
#filtersPanel {
    overflow: visible !important;
}
#filtersPanel .card-body {
    overflow: visible !important;
}
#filtersPanel .dropdown-menu {
    z-index: 1050;
}
#filtersPanel .multiselect-dropdown .dropdown-toggle {
    height: 38px;
    font-size: 0.875rem;
}
#filtersPanel .multiselect-dropdown .dropdown-label {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    flex: 1;
}
#filtersPanel .multiselect-dropdown .dropdown-menu {
    z-index: 1050;
}
#filtersPanel .multiselect-dropdown .form-check {
    padding-left: 1.5rem;
    margin-bottom: 0.25rem;
}
#filtersPanel .multiselect-dropdown .form-check-label {
    cursor: pointer;
}
.invoices-table-wrapper {
    flex: 1 1 0;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    min-height: 0;
    max-height: 100%;
}
#tableContainer {
    flex: 1 1 0;
    overflow-y: auto !important;
    overflow-x: auto;
    min-height: 0;
    max-height: 100%;
}
.invoices-footer {
    flex-shrink: 0;
    margin-top: auto;
}
#invoicesTable {
    width: 100%;
    border-collapse: collapse;
    margin: 0;
}
#invoicesTable thead th {
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
#invoicesTable thead th:hover {
    background: #e9ecef !important;
}
#invoicesTable thead th.sortable {
    cursor: pointer;
}
#invoicesTable thead th.sortable:hover {
    color: var(--admin-primary) !important;
}
#invoicesTable thead th .sort-icon {
    margin-left: 0.25rem;
    opacity: 0.4;
}
#invoicesTable thead th.sorted .sort-icon {
    opacity: 1;
    color: var(--admin-primary);
}
#invoicesTable tbody tr {
    cursor: pointer;
    transition: background-color 0.15s ease;
}
#invoicesTable tbody tr:hover td {
    background-color: #f8f9fa !important;
}
#invoicesTable tbody td {
    padding: 0.75rem 0.5rem;
    vertical-align: middle;
    border-bottom: 1px solid #f1f3f5;
    font-size: 0.85rem;
    color: #495057;
}
#invoicesTable tbody tr:last-child td {
    border-bottom: none;
}
#invoicesTable tbody td:first-child {
    font-weight: 500;
    color: #343a40;
}
.account-name-cell {
    max-width: 180px;
}
.account-name-cell .account-name {
    font-weight: 600;
    color: var(--admin-primary);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    display: block;
    max-width: 160px;
    cursor: pointer;
}
.account-name-cell .account-name:hover {
    text-decoration: underline;
}
.filter-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.25rem 0.5rem;
    border-radius: 16px;
    font-size: 0.75rem;
    font-weight: 500;
    background-color: var(--admin-primary);
    color: #fff;
}
.filter-chip .chip-remove {
    cursor: pointer;
    opacity: 0.8;
    transition: opacity 0.2s;
}
.filter-chip .chip-remove:hover {
    opacity: 1;
}
.filter-chip.pending {
    background-color: rgba(30, 58, 95, 0.3);
    color: #6c757d;
    border: 1px dashed var(--admin-primary);
}
.btn-xs {
    padding: 0.2rem 0.5rem;
    font-size: 0.7rem;
    line-height: 1.4;
}
.summary-stat-card {
    background: linear-gradient(135deg, var(--admin-primary-light) 0%, rgba(30, 58, 95, 0.02) 100%);
    border: 1px solid rgba(30, 58, 95, 0.15);
    border-radius: 8px;
    padding: 1rem;
    text-align: center;
}
.summary-stat-card .stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: #2c2c2c;
}
.summary-stat-card .stat-label {
    font-size: 0.75rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.status-badge {
    padding: 0.35rem 0.65rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 500;
}
.status-paid {
    background-color: rgba(28, 187, 140, 0.15);
    color: #1cbb8c;
}
.status-issued {
    background-color: rgba(23, 162, 184, 0.15);
    color: #17a2b8;
}
.status-pending {
    background-color: rgba(255, 191, 0, 0.15);
    color: #cc9900;
}
.status-overdue {
    background-color: rgba(220, 53, 69, 0.15);
    color: #dc3545;
}
.status-draft {
    background-color: rgba(108, 117, 125, 0.15);
    color: #6c757d;
}
.status-void {
    background-color: rgba(108, 117, 125, 0.15);
    color: #6c757d;
    text-decoration: line-through;
}
.status-cancelled {
    background-color: rgba(108, 117, 125, 0.15);
    color: #6c757d;
    text-decoration: line-through;
}
.invoice-drawer {
    position: fixed;
    top: 0;
    right: -500px;
    width: 500px;
    height: 100vh;
    background: #fff;
    box-shadow: -4px 0 15px rgba(0,0,0,0.1);
    z-index: 1050;
    transition: right 0.3s ease;
    display: flex;
    flex-direction: column;
}
.invoice-drawer.open {
    right: 0;
}
.invoice-drawer-header {
    padding: 1.25rem;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-shrink: 0;
}
.invoice-drawer-body {
    flex: 1;
    overflow-y: auto;
    padding: 1.25rem;
}
.invoice-drawer-footer {
    padding: 1rem 1.25rem;
    border-top: 1px solid #e9ecef;
    background: #f8f9fa;
    flex-shrink: 0;
}
.drawer-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.3);
    z-index: 1040;
    display: none;
}
.drawer-overlay.show {
    display: block;
}
.invoice-line-item {
    display: flex;
    justify-content: space-between;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f0f0f0;
}
.invoice-line-item:last-child {
    border-bottom: none;
}
.invoice-total-row {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    font-weight: 600;
}
.invoice-total-row.grand-total {
    font-size: 1.1rem;
    border-top: 2px solid #dee2e6;
    padding-top: 0.75rem;
    margin-top: 0.5rem;
}
.account-typeahead-wrapper {
    position: relative;
}
.account-typeahead-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: #fff;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    max-height: 250px;
    overflow-y: auto;
    z-index: 1060;
    display: none;
}
.account-typeahead-dropdown.show {
    display: block;
}
.account-typeahead-item {
    padding: 0.5rem 0.75rem;
    cursor: pointer;
    border-bottom: 1px solid #f1f3f5;
}
.account-typeahead-item:last-child {
    border-bottom: none;
}
.account-typeahead-item:hover {
    background-color: #f8f9fa;
}
.account-typeahead-item.selected {
    background-color: var(--admin-primary-light);
}
.account-typeahead-item .account-name {
    font-weight: 600;
    color: var(--admin-primary);
}
.account-typeahead-item .account-id {
    font-size: 0.75rem;
    color: #6c757d;
}
.customer-typeahead-wrapper {
    position: relative;
}
.customer-typeahead-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: #fff;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    max-height: 250px;
    overflow-y: auto;
    z-index: 1070;
    display: none;
}
.customer-typeahead-dropdown.show {
    display: block;
}
.customer-typeahead-item {
    padding: 0.625rem 0.875rem;
    cursor: pointer;
    border-bottom: 1px solid #f1f3f5;
    transition: background-color 0.15s;
}
.customer-typeahead-item:last-child {
    border-bottom: none;
}
.customer-typeahead-item:hover {
    background-color: var(--admin-primary-light);
}
.customer-typeahead-item .customer-name {
    font-weight: 600;
    color: var(--admin-primary);
}
.customer-typeahead-item .customer-account-id {
    font-size: 0.75rem;
    color: #6c757d;
}
.customer-typeahead-no-results {
    padding: 0.75rem;
    text-align: center;
    color: #6c757d;
    font-style: italic;
}
.global-summary-strip {
    background: linear-gradient(135deg, #fff 0%, #f0f4f8 100%);
    border: 1px solid rgba(30, 58, 95, 0.2);
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}
.global-metric {
    text-align: center;
    padding: 0.75rem;
    border-right: 1px solid rgba(30, 58, 95, 0.1);
}
.global-metric:last-child {
    border-right: none;
}
.global-metric .metric-label {
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #6c757d;
    margin-bottom: 0.25rem;
}
.global-metric .metric-value {
    font-size: 1.1rem;
    font-weight: 700;
    color: #2c2c2c;
}
.global-metric .metric-value.text-success { color: #1cbb8c !important; }
.global-metric .metric-value.text-warning { color: #cc9900 !important; }
.global-metric .metric-value.text-danger { color: #dc3545 !important; }
@media (max-width: 768px) {
    .invoice-drawer {
        width: 100%;
        right: -100%;
    }
    .global-metric {
        border-right: none;
        border-bottom: 1px solid rgba(30, 58, 95, 0.1);
        padding: 0.5rem;
    }
    .global-metric:last-child {
        border-bottom: none;
    }
}
.admin-filter-bg {
    background-color: #e8eef4 !important;
}
.btn-admin-primary {
    background-color: var(--admin-primary);
    border-color: var(--admin-primary);
    color: #fff;
}
.btn-admin-primary:hover {
    background-color: var(--admin-primary-hover);
    border-color: var(--admin-primary-hover);
    color: #fff;
}
.btn-outline-admin {
    border-color: var(--admin-primary);
    color: var(--admin-primary);
}
.btn-outline-admin:hover {
    background-color: var(--admin-primary);
    color: #fff;
}
.text-admin-primary {
    color: var(--admin-primary) !important;
}
</style>
@endpush

@section('content')
<div class="container-fluid invoices-container" style="padding: 1.5rem;">
    <div class="admin-breadcrumb mb-3" style="flex-shrink: 0;">
        <a href="{{ route('admin.dashboard') }}">Admin</a>
        <span class="separator">/</span>
        <a href="#">Invoices & Payments</a>
        <span class="separator">/</span>
        <span>Invoices</span>
    </div>

    <div id="globalSummaryStrip" class="global-summary-strip mb-3 p-3" style="flex-shrink: 0;">
        <div class="row align-items-center">
            <div class="col-6 col-md-4 col-lg-2 global-metric">
                <div class="metric-label">Total Invoices</div>
                <div class="metric-value" id="totalInvoicesCount">--</div>
            </div>
            <div class="col-6 col-md-4 col-lg-2 global-metric">
                <div class="metric-label">Total Value</div>
                <div class="metric-value" id="totalInvoiceValue">&pound;--</div>
            </div>
            <div class="col-6 col-md-4 col-lg-2 global-metric">
                <div class="metric-label">Outstanding</div>
                <div class="metric-value text-warning" id="totalOutstanding">&pound;--</div>
            </div>
            <div class="col-6 col-md-4 col-lg-2 global-metric">
                <div class="metric-label">Overdue</div>
                <div class="metric-value text-danger" id="totalOverdue">&pound;--</div>
            </div>
            <div class="col-6 col-md-4 col-lg-2 global-metric">
                <div class="metric-label">Paid This Month</div>
                <div class="metric-value text-success" id="paidThisMonth">&pound;--</div>
            </div>
            <div class="col-6 col-md-4 col-lg-2 global-metric">
                <div class="metric-label">Accounts</div>
                <div class="metric-value" id="uniqueAccounts">--</div>
            </div>
        </div>
    </div>

    <div class="row flex-grow-1" style="min-height: 0;">
        <div class="col-12 d-flex flex-column" style="min-height: 0;">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap invoices-fixed-header">
                    <h5 class="card-title mb-2 mb-md-0">All Client Invoices</h5>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-outline-admin btn-sm" data-bs-toggle="collapse" data-bs-target="#filtersPanel">
                            <i class="fas fa-filter me-1"></i> Filters
                        </button>
                        <button type="button" class="btn btn-outline-admin btn-sm" id="exportBtn">
                            <i class="fas fa-download me-1"></i> Export
                        </button>
                        <button type="button" class="btn btn-outline-admin btn-sm" id="createCreditBtn">
                            <i class="fas fa-plus me-1"></i> Create Credit
                        </button>
                        <button type="button" class="btn btn-admin-primary btn-sm" id="createInvoiceBtn">
                            <i class="fas fa-plus me-1"></i> Create Invoice
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="invoices-fixed-header">
                        <div class="collapse mb-3" id="filtersPanel">
                            <div class="card card-body border-0 rounded-3 admin-filter-bg">
                                <div class="row g-3 align-items-end">
                                    <div class="col-6 col-md-4 col-lg-2">
                                        <label class="form-label small fw-bold">Account Name</label>
                                        <div class="account-typeahead-wrapper">
                                            <input type="text" class="form-control form-control-sm" id="accountNameFilter" placeholder="Search accounts..." autocomplete="off" style="background-color: #fff;">
                                            <div class="account-typeahead-dropdown" id="accountTypeaheadDropdown"></div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-4 col-lg-2">
                                        <label class="form-label small fw-bold">Billing Year</label>
                                        <div class="dropdown multiselect-dropdown" data-filter="billingYear">
                                            <button class="btn btn-sm dropdown-toggle w-100 text-start d-flex justify-content-between align-items-center" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" style="background-color: #fff; border: 1px solid #ced4da; color: #495057;">
                                                <span class="dropdown-label">All Years</span>
                                            </button>
                                            <div class="dropdown-menu w-100 p-2" style="max-height: 250px; overflow-y: auto;">
                                                <div class="d-flex justify-content-between mb-2 border-bottom pb-2">
                                                    <a href="#" class="small text-decoration-none select-all-btn">Select All</a>
                                                    <a href="#" class="small text-decoration-none clear-all-btn">Clear</a>
                                                </div>
                                                <div class="filter-options" id="billingYearOptions"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-4 col-lg-2">
                                        <label class="form-label small fw-bold">Billing Month</label>
                                        <div class="dropdown multiselect-dropdown" data-filter="billingMonth">
                                            <button class="btn btn-sm dropdown-toggle w-100 text-start d-flex justify-content-between align-items-center" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" style="background-color: #fff; border: 1px solid #ced4da; color: #495057;">
                                                <span class="dropdown-label">All Months</span>
                                            </button>
                                            <div class="dropdown-menu w-100 p-2">
                                                <div class="d-flex justify-content-between mb-2 border-bottom pb-2">
                                                    <a href="#" class="small text-decoration-none select-all-btn">Select All</a>
                                                    <a href="#" class="small text-decoration-none clear-all-btn">Clear</a>
                                                </div>
                                                <div class="filter-options">
                                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="01" id="month01"><label class="form-check-label small" for="month01">January</label></div>
                                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="02" id="month02"><label class="form-check-label small" for="month02">February</label></div>
                                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="03" id="month03"><label class="form-check-label small" for="month03">March</label></div>
                                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="04" id="month04"><label class="form-check-label small" for="month04">April</label></div>
                                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="05" id="month05"><label class="form-check-label small" for="month05">May</label></div>
                                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="06" id="month06"><label class="form-check-label small" for="month06">June</label></div>
                                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="07" id="month07"><label class="form-check-label small" for="month07">July</label></div>
                                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="08" id="month08"><label class="form-check-label small" for="month08">August</label></div>
                                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="09" id="month09"><label class="form-check-label small" for="month09">September</label></div>
                                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="10" id="month10"><label class="form-check-label small" for="month10">October</label></div>
                                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="11" id="month11"><label class="form-check-label small" for="month11">November</label></div>
                                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="12" id="month12"><label class="form-check-label small" for="month12">December</label></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-4 col-lg-2">
                                        <label class="form-label small fw-bold">Invoice Status</label>
                                        <div class="dropdown multiselect-dropdown" data-filter="status">
                                            <button class="btn btn-sm dropdown-toggle w-100 text-start d-flex justify-content-between align-items-center" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" style="background-color: #fff; border: 1px solid #ced4da; color: #495057;">
                                                <span class="dropdown-label">All Statuses</span>
                                            </button>
                                            <div class="dropdown-menu w-100 p-2">
                                                <div class="d-flex justify-content-between mb-2 border-bottom pb-2">
                                                    <a href="#" class="small text-decoration-none select-all-btn">Select All</a>
                                                    <a href="#" class="small text-decoration-none clear-all-btn">Clear</a>
                                                </div>
                                                <div class="filter-options">
                                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="draft" id="statusDraft"><label class="form-check-label small" for="statusDraft">Draft</label></div>
                                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="issued" id="statusIssued"><label class="form-check-label small" for="statusIssued">Issued</label></div>
                                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="paid" id="statusPaid"><label class="form-check-label small" for="statusPaid">Paid</label></div>
                                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="overdue" id="statusOverdue"><label class="form-check-label small" for="statusOverdue">Overdue</label></div>
                                                    <div class="form-check"><input class="form-check-input" type="checkbox" value="void" id="statusVoid"><label class="form-check-label small" for="statusVoid">Void / Cancelled</label></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-4 col-lg-2">
                                        <label class="form-label small fw-bold">Invoice Number</label>
                                        <input type="text" class="form-control form-control-sm" id="invoiceNumberFilter" placeholder="Enter ID..." style="background-color: #fff;">
                                    </div>
                                    <div class="col-12 col-md-4 col-lg-2 d-flex gap-2">
                                        <button type="button" class="btn btn-admin-primary btn-sm flex-grow-1" id="applyFiltersBtn">
                                            <i class="fas fa-check me-1"></i> Apply Filters
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm flex-grow-1" id="resetFiltersBtn">
                                            <i class="fas fa-undo me-1"></i> Reset
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="activeFiltersContainer" class="mb-2" style="display: none;">
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <span class="small text-muted fw-bold">Active Filters:</span>
                                <div id="activeFilterChips" class="d-flex flex-wrap gap-1"></div>
                                <span class="small text-muted fst-italic ms-2" id="filterPendingNotice" style="display: none;">
                                    <i class="fas fa-info-circle me-1"></i>Click "Apply Filters" to update results
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="invoices-table-wrapper">
                        <div id="tableContainer" class="table-responsive">
                            <table class="table table-hover mb-0" id="invoicesTable">
                                <thead>
                                    <tr>
                                        <th style="width: 40px;">
                                            <input type="checkbox" class="form-check-input" id="selectAll">
                                        </th>
                                        <th class="sortable" data-sort="accountName">Account Name <i class="fas fa-sort sort-icon"></i></th>
                                        <th class="sortable" data-sort="invoiceNumber">Invoice Number <i class="fas fa-sort sort-icon"></i></th>
                                        <th class="sortable" data-sort="billingPeriod">Billing Period <i class="fas fa-sort sort-icon"></i></th>
                                        <th class="sortable" data-sort="issueDate">Invoice Date <i class="fas fa-sort sort-icon"></i></th>
                                        <th class="sortable" data-sort="dueDate">Due Date <i class="fas fa-sort sort-icon"></i></th>
                                        <th>Status</th>
                                        <th class="text-end sortable" data-sort="subtotal">Amount (ex VAT) <i class="fas fa-sort sort-icon"></i></th>
                                        <th class="text-end">VAT</th>
                                        <th class="text-end sortable" data-sort="total">Total <i class="fas fa-sort sort-icon"></i></th>
                                        <th class="text-end sortable" data-sort="balanceDue">Outstanding <i class="fas fa-sort sort-icon"></i></th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="invoicesTableBody">
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="invoices-footer border-top pt-3 mt-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-muted small">
                                Showing <span id="showingStart">1</span>-<span id="showingEnd">10</span> of <span id="totalCount">0</span> invoices
                            </div>
                            <nav>
                                <ul class="pagination pagination-sm mb-0" id="paginationContainer">
                                    <li class="page-item disabled">
                                        <a class="page-link" href="#" tabindex="-1"><i class="fas fa-chevron-left"></i></a>
                                    </li>
                                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                    <li class="page-item">
                                        <a class="page-link" href="#"><i class="fas fa-chevron-right"></i></a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="drawer-overlay" id="drawerOverlay"></div>
<div class="invoice-drawer" id="invoiceDrawer">
    <div class="invoice-drawer-header">
        <div class="flex-grow-1">
            <div class="d-flex align-items-center gap-2 mb-1">
                <h5 class="mb-0" id="drawerInvoiceNumber">Invoice #INV-2025-0012</h5>
                <span class="status-badge status-paid" id="drawerStatusBadge">Paid</span>
            </div>
            <div class="small text-muted" id="drawerBillingPeriod">Billing Period: Jan 2025</div>
            <div class="small mt-1">
                <span class="fw-medium text-admin-primary" id="drawerAccountName">Account Name</span>
            </div>
        </div>
        <button type="button" class="btn-close" id="closeDrawerBtn"></button>
    </div>
    <div class="invoice-drawer-body">
        <div class="alert alert-info mb-3" style="background-color: var(--admin-primary-light); border-color: rgba(30, 58, 95, 0.2);">
            <i class="fas fa-info-circle text-admin-primary me-2"></i>
            Invoice data is synchronized from HubSpot. View-only access for admin users.
        </div>

        <div class="card mb-3" style="background-color: #f8f9fa; border: none;">
            <div class="card-body py-2">
                <div class="row small">
                    <div class="col-6">
                        <div class="text-muted">Issue Date</div>
                        <div class="fw-medium" id="drawerIssueDate">02 Jan 2025</div>
                    </div>
                    <div class="col-6 text-end">
                        <div class="text-muted">Due Date</div>
                        <div class="fw-medium" id="drawerDueDate">16 Jan 2025</div>
                    </div>
                </div>
            </div>
        </div>

        <h6 class="mb-3"><i class="fas fa-receipt me-2 text-admin-primary"></i>Invoice Summary</h6>
        <div class="mb-4" id="drawerSummary">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted">Subtotal (ex VAT)</span>
                <span id="drawerSubtotal">&pound;2,800.00</span>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted">VAT</span>
                <span id="drawerVat">&pound;560.00</span>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                <span class="fw-bold">Total (inc VAT)</span>
                <span class="fw-bold" id="drawerTotal">&pound;3,360.00</span>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted">Amount Paid</span>
                <span class="text-success" id="drawerAmountPaid">&pound;3,360.00</span>
            </div>
            <div class="d-flex justify-content-between align-items-center">
                <span class="fw-bold">Balance Outstanding</span>
                <span class="fw-bold" id="drawerBalanceOutstanding">&pound;0.00</span>
            </div>
        </div>

        <hr>

        <h6 class="mb-3"><i class="fas fa-list me-2 text-admin-primary"></i>Line Items</h6>
        <div id="drawerLineItems">
            <div class="text-center text-muted py-3">
                <i class="fas fa-spinner fa-spin"></i> Loading...
            </div>
        </div>
    </div>
    <div class="invoice-drawer-footer">
        <div class="d-flex flex-column gap-2">
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-admin flex-grow-1" id="downloadPdfBtn">
                    <i class="fas fa-file-pdf me-1"></i> Download PDF
                </button>
                <a href="#" class="btn btn-admin-primary flex-grow-1" id="viewAccountBtn">
                    <i class="fas fa-building me-1"></i> View Account
                </a>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="createInvoiceCreditModal" tabindex="-1" aria-labelledby="createInvoiceCreditModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: var(--admin-primary, #1e3a5f); color: #fff;">
                <h5 class="modal-title" id="createInvoiceCreditModalLabel">
                    <i class="fas fa-file-invoice me-2" id="modalTitleIcon"></i>
                    <span id="modalTitleText">Create Customer Invoice</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-4" id="modalDescription">Complete the form below to create a new customer invoice.</p>
                
                <form id="createInvoiceCreditForm" novalidate>
                    <input type="hidden" id="formMode" name="mode" value="invoice">
                    <input type="hidden" id="selectedCustomerId" name="customerId" value="">
                    
                    <div class="mb-4">
                        <label for="customerSearchInput" class="form-label fw-semibold">Customer <span class="text-danger">*</span></label>
                        <div class="customer-typeahead-wrapper position-relative">
                            <input type="text" class="form-control" id="customerSearchInput" placeholder="Select customer..." autocomplete="off">
                            <div class="customer-typeahead-dropdown" id="customerTypeaheadDropdown"></div>
                            <div class="selected-customer-display d-none" id="selectedCustomerDisplay">
                                <div class="d-flex align-items-center justify-content-between p-2 border rounded bg-light">
                                    <div>
                                        <span class="fw-semibold" id="selectedCustomerName"></span>
                                        <small class="text-muted ms-2" id="selectedCustomerAccountId"></small>
                                        <span class="badge ms-2" id="selectedCustomerStatus"></span>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-link text-danger p-0" id="clearCustomerBtn" title="Clear selection">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="invalid-feedback" id="customerError">Please select a customer</div>
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    <h6 class="text-muted text-uppercase small mb-3">Line Item</h6>
                    
                    <div class="mb-3">
                        <label for="itemDescription" class="form-label fw-semibold">Item description <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="itemDescription" maxlength="255" placeholder="e.g. Setup fee, Price correction, Professional services" required>
                        <div class="d-flex justify-content-between">
                            <div class="invalid-feedback" id="itemDescriptionError">Please enter an item description</div>
                            <small class="text-muted mt-1"><span id="descCharCount">0</span>/255</small>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="itemQuantity" class="form-label fw-semibold">Quantity <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="itemQuantity" value="1" min="0.01" step="0.01" required>
                            <div class="invalid-feedback" id="itemQuantityError">Minimum 0.01</div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="itemUnitPrice" class="form-label fw-semibold">Unit price (&pound;) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="itemUnitPrice" placeholder="0.0000" required>
                            <div class="invalid-feedback" id="itemUnitPriceError">Enter a valid price (max 4 decimal places)</div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="lineTotal" class="form-label fw-semibold">Line total (&pound;)</label>
                            <div class="input-group">
                                <input type="text" class="form-control bg-light" id="lineTotal" value="0.00" readonly>
                                <span class="input-group-text" id="lineTotalTooltip" data-bs-toggle="tooltip" data-bs-placement="top" title="Calculated: qty × unit price">
                                    <i class="fas fa-info-circle text-muted"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    
                    <div class="mb-3">
                        <label for="overrideEmail" class="form-label fw-semibold">Send invoice to different email <small class="text-muted fw-normal">(optional)</small></label>
                        <input type="email" class="form-control" id="overrideEmail" placeholder="email@example.com">
                        <div class="invalid-feedback" id="overrideEmailError">Please enter a valid email address</div>
                    </div>
                    
                    <div class="card mt-4" id="invoiceSummaryCard">
                        <div class="card-header py-2">
                            <h6 class="mb-0 small text-uppercase fw-bold">Invoice Summary</h6>
                        </div>
                        <div class="card-body py-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Subtotal</span>
                                <span class="fw-semibold" id="summarySubtotal">&pound;0.00</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">
                                    VAT <small id="vatRateDisplay" class="text-muted">(0%)</small>
                                </span>
                                <span class="fw-semibold" id="summaryVat">&pound;0.00</span>
                            </div>
                            <div id="vatNoteRow" class="d-none mb-2">
                                <small class="text-info" id="vatNote"></small>
                            </div>
                            <hr class="my-2">
                            <div class="d-flex justify-content-between">
                                <span class="fw-bold">Total</span>
                                <span class="fw-bold fs-5" id="summaryTotal">&pound;0.00</span>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-admin-primary" id="modalSubmitBtn" disabled>
                    <i class="fas fa-plus me-1"></i>
                    <span id="modalSubmitBtnText">Create invoice</span>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let invoicesData = [];
    let allAccountsData = [];
    let isLoading = false;
    let currentSort = { field: 'issueDate', direction: 'desc' };
    let currentPage = 1;
    const pageSize = 25;

    const appliedFilters = {
        accountName: null,
        accountId: null,
        billingYear: [],
        billingMonth: [],
        status: [],
        invoiceNumber: ''
    };

    const pendingFilters = { ...appliedFilters };

    function formatDate(dateStr) {
        if (!dateStr) return '-';
        const date = new Date(dateStr);
        return date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
    }

    function formatBillingPeriodMonthYear(start, end) {
        if (!start && !end) return '-';
        const dateToUse = start || end;
        const date = new Date(dateToUse);
        return date.toLocaleDateString('en-GB', { month: 'short', year: 'numeric' });
    }

    function formatCurrency(amount, currency = 'GBP') {
        const symbols = { 'GBP': '£', 'EUR': '€', 'USD': '$' };
        const symbol = symbols[currency] || '£';
        return symbol + parseFloat(amount || 0).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }

    function getStatusBadge(status) {
        const statusMap = {
            'draft': '<span class="status-badge status-draft">Draft</span>',
            'issued': '<span class="status-badge status-issued">Issued</span>',
            'pending': '<span class="status-badge status-issued">Issued</span>',
            'paid': '<span class="status-badge status-paid">Paid</span>',
            'overdue': '<span class="status-badge status-overdue">Overdue</span>',
            'void': '<span class="status-badge status-void">Void</span>',
            'voided': '<span class="status-badge status-void">Void</span>',
            'cancelled': '<span class="status-badge status-void">Cancelled</span>'
        };
        return statusMap[status] || '<span class="status-badge">' + status + '</span>';
    }

    function showLoading() {
        isLoading = true;
        const tbody = document.getElementById('invoicesTableBody');
        tbody.innerHTML = `
            <tr>
                <td colspan="12" class="text-center py-5">
                    <div class="spinner-border text-admin-primary" role="status" style="color: var(--admin-primary);">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <div class="mt-2 text-muted">Loading invoices...</div>
                </td>
            </tr>
        `;
    }

    function showError(message) {
        const tbody = document.getElementById('invoicesTableBody');
        tbody.innerHTML = `
            <tr>
                <td colspan="12" class="text-center py-5">
                    <div class="text-danger mb-2">
                        <i class="fas fa-exclamation-triangle fa-2x"></i>
                    </div>
                    <div class="fw-medium">Unable to load invoices</div>
                    <div class="text-muted small mt-1">${message}</div>
                    <button class="btn btn-outline-admin btn-sm mt-3" onclick="loadInvoices()">
                        <i class="fas fa-sync-alt me-1"></i> Try Again
                    </button>
                </td>
            </tr>
        `;
    }

    function generateMockData() {
        const accounts = [
            { id: 'ACC-001', name: 'Acme Corporation Ltd' },
            { id: 'ACC-002', name: 'TechStart Solutions' },
            { id: 'ACC-003', name: 'Global Retail Group' },
            { id: 'ACC-004', name: 'FinanceFirst Partners' },
            { id: 'ACC-005', name: 'HealthCare Plus' },
            { id: 'ACC-006', name: 'MediaMax Agency' },
            { id: 'ACC-007', name: 'LogiTrans Shipping' },
            { id: 'ACC-008', name: 'EduLearn Institute' },
            { id: 'ACC-009', name: 'GreenEnergy Co' },
            { id: 'ACC-010', name: 'FoodService Network' }
        ];

        allAccountsData = accounts;

        const statuses = ['paid', 'issued', 'overdue', 'draft', 'void'];
        const invoices = [];

        for (let i = 0; i < 87; i++) {
            const account = accounts[Math.floor(Math.random() * accounts.length)];
            const status = statuses[Math.floor(Math.random() * statuses.length)];
            const subtotal = Math.floor(Math.random() * 5000) + 500;
            const vat = subtotal * 0.2;
            const total = subtotal + vat;
            const balanceDue = status === 'paid' ? 0 : (status === 'draft' ? total : Math.random() > 0.5 ? total : 0);

            const issueDate = new Date(2024, Math.floor(Math.random() * 12), Math.floor(Math.random() * 28) + 1);
            const dueDate = new Date(issueDate);
            dueDate.setDate(dueDate.getDate() + 14);

            invoices.push({
                id: `INV-${String(i + 1).padStart(4, '0')}`,
                invoiceNumber: `INV-2024-${String(i + 1).padStart(4, '0')}`,
                accountId: account.id,
                accountName: account.name,
                billingPeriodStart: new Date(issueDate.getFullYear(), issueDate.getMonth(), 1).toISOString(),
                billingPeriodEnd: new Date(issueDate.getFullYear(), issueDate.getMonth() + 1, 0).toISOString(),
                issueDate: issueDate.toISOString(),
                dueDate: dueDate.toISOString(),
                status: status,
                subtotal: subtotal,
                vat: vat,
                total: total,
                balanceDue: balanceDue,
                currency: 'GBP',
                lineItems: [
                    { name: 'SMS Messages', description: `${Math.floor(Math.random() * 50000) + 5000} messages`, amount: subtotal * 0.7 },
                    { name: 'RCS Messages', description: `${Math.floor(Math.random() * 5000) + 500} messages`, amount: subtotal * 0.25 },
                    { name: 'Platform Fee', amount: subtotal * 0.05 }
                ]
            });
        }

        return invoices;
    }

    async function loadInvoices() {
        showLoading();

        try {
            await new Promise(resolve => setTimeout(resolve, 500));
            invoicesData = generateMockData();

            const filteredData = filterInvoices(invoicesData);
            const sortedData = sortInvoices(filteredData);

            renderInvoices(sortedData);
            updateGlobalSummary(invoicesData);
            populateBillingYearOptions();

        } catch (error) {
            console.error('Error loading invoices:', error);
            showError('Network error. Please check your connection and try again.');
        }
    }

    function filterInvoices(invoices) {
        return invoices.filter(inv => {
            if (appliedFilters.accountId && inv.accountId !== appliedFilters.accountId) return false;
            if (appliedFilters.accountName && !inv.accountName.toLowerCase().includes(appliedFilters.accountName.toLowerCase())) return false;

            if (appliedFilters.billingYear.length > 0) {
                const year = new Date(inv.issueDate).getFullYear().toString();
                if (!appliedFilters.billingYear.includes(year)) return false;
            }

            if (appliedFilters.billingMonth.length > 0) {
                const month = String(new Date(inv.issueDate).getMonth() + 1).padStart(2, '0');
                if (!appliedFilters.billingMonth.includes(month)) return false;
            }

            if (appliedFilters.status.length > 0) {
                if (!appliedFilters.status.includes(inv.status)) return false;
            }

            if (appliedFilters.invoiceNumber && !inv.invoiceNumber.toLowerCase().includes(appliedFilters.invoiceNumber.toLowerCase())) return false;

            return true;
        });
    }

    function sortInvoices(invoices) {
        return [...invoices].sort((a, b) => {
            let aVal = a[currentSort.field];
            let bVal = b[currentSort.field];

            if (currentSort.field === 'issueDate' || currentSort.field === 'dueDate' || currentSort.field === 'billingPeriod') {
                aVal = new Date(a.issueDate || a.billingPeriodStart);
                bVal = new Date(b.issueDate || b.billingPeriodStart);
            } else if (['subtotal', 'total', 'balanceDue'].includes(currentSort.field)) {
                aVal = parseFloat(aVal) || 0;
                bVal = parseFloat(bVal) || 0;
            } else {
                aVal = String(aVal || '').toLowerCase();
                bVal = String(bVal || '').toLowerCase();
            }

            if (aVal < bVal) return currentSort.direction === 'asc' ? -1 : 1;
            if (aVal > bVal) return currentSort.direction === 'asc' ? 1 : -1;
            return 0;
        });
    }

    function renderInvoices(invoices) {
        isLoading = false;
        const tbody = document.getElementById('invoicesTableBody');
        tbody.innerHTML = '';

        if (invoices.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="12" class="text-center py-5 text-muted">
                        <i class="fas fa-file-invoice fa-2x mb-2"></i>
                        <div>No invoices found</div>
                    </td>
                </tr>
            `;
            document.getElementById('totalCount').textContent = '0';
            document.getElementById('showingStart').textContent = '0';
            document.getElementById('showingEnd').textContent = '0';
            return;
        }

        const startIdx = (currentPage - 1) * pageSize;
        const endIdx = Math.min(startIdx + pageSize, invoices.length);
        const pageInvoices = invoices.slice(startIdx, endIdx);

        pageInvoices.forEach(inv => {
            const row = document.createElement('tr');
            row.setAttribute('data-invoice-id', inv.id);

            const billingPeriod = formatBillingPeriodMonthYear(inv.billingPeriodStart, inv.billingPeriodEnd);
            const hasPdfUrl = inv.pdfUrl && inv.pdfUrl.length > 0;

            row.innerHTML = `
                <td onclick="event.stopPropagation();">
                    <input type="checkbox" class="form-check-input invoice-checkbox" value="${inv.id}">
                </td>
                <td class="account-name-cell">
                    <span class="account-name" onclick="event.stopPropagation(); navigateToAccount('${inv.accountId}')" title="${inv.accountName}">${inv.accountName}</span>
                </td>
                <td><strong>${inv.invoiceNumber}</strong></td>
                <td>${billingPeriod}</td>
                <td>${formatDate(inv.issueDate)}</td>
                <td>${formatDate(inv.dueDate)}</td>
                <td>${getStatusBadge(inv.status)}</td>
                <td class="text-end">${formatCurrency(inv.subtotal, inv.currency)}</td>
                <td class="text-end">${formatCurrency(inv.vat, inv.currency)}</td>
                <td class="text-end fw-medium">${formatCurrency(inv.total, inv.currency)}</td>
                <td class="text-end ${inv.balanceDue > 0 ? 'text-danger fw-bold' : ''}">${formatCurrency(inv.balanceDue, inv.currency)}</td>
                <td class="text-end" onclick="event.stopPropagation();">
                    <div class="dropdown">
                        <button class="btn btn-sm btn-link text-muted p-0" data-bs-toggle="dropdown">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#" onclick="openDrawer('${inv.id}'); return false;"><i class="fas fa-eye me-2"></i>View Invoice</a></li>
                            <li><a class="dropdown-item ${hasPdfUrl ? '' : 'disabled text-muted'}" href="#" onclick="downloadPdf('${inv.id}', '${inv.pdfUrl || ''}'); return false;"><i class="fas fa-file-pdf me-2"></i>Download PDF</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#" onclick="navigateToAccount('${inv.accountId}'); return false;"><i class="fas fa-building me-2"></i>View Account</a></li>
                        </ul>
                    </div>
                </td>
            `;
            row.addEventListener('click', () => openDrawer(inv.id));
            tbody.appendChild(row);
        });

        document.getElementById('totalCount').textContent = invoices.length;
        document.getElementById('showingStart').textContent = invoices.length > 0 ? startIdx + 1 : 0;
        document.getElementById('showingEnd').textContent = endIdx;

        updateSortIndicators();
        renderPagination(invoices.length);
    }

    function updateGlobalSummary(invoices) {
        const totalValue = invoices.reduce((sum, inv) => sum + inv.total, 0);
        const outstanding = invoices.filter(inv => inv.status !== 'paid' && inv.status !== 'void').reduce((sum, inv) => sum + inv.balanceDue, 0);
        const overdue = invoices.filter(inv => inv.status === 'overdue').reduce((sum, inv) => sum + inv.balanceDue, 0);

        const now = new Date();
        const thisMonthStart = new Date(now.getFullYear(), now.getMonth(), 1);
        const paidThisMonth = invoices.filter(inv => {
            if (inv.status !== 'paid') return false;
            const issueDate = new Date(inv.issueDate);
            return issueDate >= thisMonthStart;
        }).reduce((sum, inv) => sum + inv.total, 0);

        const uniqueAccountIds = new Set(invoices.map(inv => inv.accountId));

        document.getElementById('totalInvoicesCount').textContent = invoices.length.toLocaleString();
        document.getElementById('totalInvoiceValue').textContent = formatCurrency(totalValue);
        document.getElementById('totalOutstanding').textContent = formatCurrency(outstanding);
        document.getElementById('totalOverdue').textContent = formatCurrency(overdue);
        document.getElementById('paidThisMonth').textContent = formatCurrency(paidThisMonth);
        document.getElementById('uniqueAccounts').textContent = uniqueAccountIds.size.toLocaleString();
    }

    function populateBillingYearOptions() {
        const years = [...new Set(invoicesData.map(inv => new Date(inv.issueDate).getFullYear()))].sort((a, b) => b - a);
        const container = document.getElementById('billingYearOptions');
        container.innerHTML = years.map(year => `
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="${year}" id="year${year}">
                <label class="form-check-label small" for="year${year}">${year}</label>
            </div>
        `).join('');
    }

    function updateSortIndicators() {
        document.querySelectorAll('#invoicesTable thead th.sortable').forEach(th => {
            th.classList.remove('sorted');
            const icon = th.querySelector('.sort-icon');
            if (icon) icon.className = 'fas fa-sort sort-icon';
        });

        const activeHeader = document.querySelector(`#invoicesTable thead th[data-sort="${currentSort.field}"]`);
        if (activeHeader) {
            activeHeader.classList.add('sorted');
            const icon = activeHeader.querySelector('.sort-icon');
            if (icon) icon.className = `fas fa-sort-${currentSort.direction === 'asc' ? 'up' : 'down'} sort-icon`;
        }
    }

    function renderPagination(totalItems) {
        const totalPages = Math.ceil(totalItems / pageSize);
        const container = document.getElementById('paginationContainer');

        let html = `
            <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${currentPage - 1}"><i class="fas fa-chevron-left"></i></a>
            </li>
        `;

        for (let i = 1; i <= totalPages; i++) {
            if (i === 1 || i === totalPages || (i >= currentPage - 2 && i <= currentPage + 2)) {
                html += `<li class="page-item ${i === currentPage ? 'active' : ''}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
            } else if (i === currentPage - 3 || i === currentPage + 3) {
                html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
        }

        html += `
            <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${currentPage + 1}"><i class="fas fa-chevron-right"></i></a>
            </li>
        `;

        container.innerHTML = html;

        container.querySelectorAll('a[data-page]').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const page = parseInt(this.dataset.page);
                if (page >= 1 && page <= totalPages) {
                    currentPage = page;
                    const filteredData = filterInvoices(invoicesData);
                    const sortedData = sortInvoices(filteredData);
                    renderInvoices(sortedData);
                }
            });
        });
    }

    document.querySelectorAll('#invoicesTable thead th.sortable').forEach(th => {
        th.addEventListener('click', function() {
            const field = this.dataset.sort;
            if (currentSort.field === field) {
                currentSort.direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
            } else {
                currentSort.field = field;
                currentSort.direction = 'asc';
            }
            currentPage = 1;
            const filteredData = filterInvoices(invoicesData);
            const sortedData = sortInvoices(filteredData);
            renderInvoices(sortedData);
        });
    });

    const accountInput = document.getElementById('accountNameFilter');
    const accountDropdown = document.getElementById('accountTypeaheadDropdown');
    let selectedAccountId = null;

    accountInput.addEventListener('input', function() {
        const query = this.value.toLowerCase().trim();
        selectedAccountId = null;

        if (query.length < 2) {
            accountDropdown.classList.remove('show');
            return;
        }

        const matches = allAccountsData.filter(acc =>
            acc.name.toLowerCase().includes(query) || acc.id.toLowerCase().includes(query)
        ).slice(0, 10);

        if (matches.length === 0) {
            accountDropdown.innerHTML = '<div class="account-typeahead-item text-muted">No accounts found</div>';
        } else {
            accountDropdown.innerHTML = matches.map(acc => `
                <div class="account-typeahead-item" data-id="${acc.id}" data-name="${acc.name}">
                    <div class="account-name">${acc.name}</div>
                    <div class="account-id">${acc.id}</div>
                </div>
            `).join('');

            accountDropdown.querySelectorAll('.account-typeahead-item[data-id]').forEach(item => {
                item.addEventListener('click', function() {
                    selectedAccountId = this.dataset.id;
                    accountInput.value = this.dataset.name;
                    accountDropdown.classList.remove('show');
                    pendingFilters.accountId = selectedAccountId;
                    pendingFilters.accountName = this.dataset.name;
                });
            });
        }

        accountDropdown.classList.add('show');
    });

    accountInput.addEventListener('blur', function() {
        setTimeout(() => accountDropdown.classList.remove('show'), 200);
    });

    document.querySelectorAll('.multiselect-dropdown .select-all-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const dropdown = this.closest('.multiselect-dropdown');
            dropdown.querySelectorAll('.form-check-input').forEach(cb => cb.checked = true);
            updateDropdownLabel(dropdown);
        });
    });

    document.querySelectorAll('.multiselect-dropdown .clear-all-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const dropdown = this.closest('.multiselect-dropdown');
            dropdown.querySelectorAll('.form-check-input').forEach(cb => cb.checked = false);
            updateDropdownLabel(dropdown);
        });
    });

    document.querySelectorAll('.multiselect-dropdown .form-check-input').forEach(cb => {
        cb.addEventListener('change', function() {
            updateDropdownLabel(this.closest('.multiselect-dropdown'));
        });
    });

    function updateDropdownLabel(dropdown) {
        const filter = dropdown.dataset.filter;
        const checked = dropdown.querySelectorAll('.form-check-input:checked');
        const label = dropdown.querySelector('.dropdown-label');

        if (checked.length === 0) {
            label.textContent = filter === 'billingYear' ? 'All Years' : filter === 'billingMonth' ? 'All Months' : 'All Statuses';
        } else if (checked.length === 1) {
            label.textContent = checked[0].nextElementSibling.textContent;
        } else {
            label.textContent = `${checked.length} selected`;
        }
    }

    document.getElementById('applyFiltersBtn').addEventListener('click', function() {
        appliedFilters.accountId = pendingFilters.accountId || null;
        appliedFilters.accountName = document.getElementById('accountNameFilter').value || null;
        appliedFilters.invoiceNumber = document.getElementById('invoiceNumberFilter').value || '';

        appliedFilters.billingYear = [];
        document.querySelectorAll('[data-filter="billingYear"] .form-check-input:checked').forEach(cb => {
            appliedFilters.billingYear.push(cb.value);
        });

        appliedFilters.billingMonth = [];
        document.querySelectorAll('[data-filter="billingMonth"] .form-check-input:checked').forEach(cb => {
            appliedFilters.billingMonth.push(cb.value);
        });

        appliedFilters.status = [];
        document.querySelectorAll('[data-filter="status"] .form-check-input:checked').forEach(cb => {
            appliedFilters.status.push(cb.value);
        });

        currentPage = 1;
        const filteredData = filterInvoices(invoicesData);
        const sortedData = sortInvoices(filteredData);
        renderInvoices(sortedData);
        updateActiveFiltersDisplay();
    });

    document.getElementById('resetFiltersBtn').addEventListener('click', function() {
        document.getElementById('accountNameFilter').value = '';
        document.getElementById('invoiceNumberFilter').value = '';
        document.querySelectorAll('.multiselect-dropdown .form-check-input').forEach(cb => cb.checked = false);
        document.querySelectorAll('.multiselect-dropdown').forEach(dd => updateDropdownLabel(dd));

        selectedAccountId = null;
        pendingFilters.accountId = null;
        pendingFilters.accountName = null;

        Object.keys(appliedFilters).forEach(key => {
            if (Array.isArray(appliedFilters[key])) {
                appliedFilters[key] = [];
            } else {
                appliedFilters[key] = null;
            }
        });

        currentPage = 1;
        const sortedData = sortInvoices(invoicesData);
        renderInvoices(sortedData);
        updateActiveFiltersDisplay();
    });

    function updateActiveFiltersDisplay() {
        const container = document.getElementById('activeFiltersContainer');
        const chipsContainer = document.getElementById('activeFilterChips');
        const hasFilters = appliedFilters.accountName || appliedFilters.billingYear.length > 0 ||
            appliedFilters.billingMonth.length > 0 || appliedFilters.status.length > 0 || appliedFilters.invoiceNumber;

        if (!hasFilters) {
            container.style.display = 'none';
            return;
        }

        container.style.display = 'block';
        let chips = '';

        if (appliedFilters.accountName) {
            chips += `<span class="filter-chip">Account: ${appliedFilters.accountName} <i class="fas fa-times chip-remove" data-filter="account"></i></span>`;
        }

        if (appliedFilters.billingYear.length > 0) {
            chips += `<span class="filter-chip">Year: ${appliedFilters.billingYear.join(', ')} <i class="fas fa-times chip-remove" data-filter="billingYear"></i></span>`;
        }

        if (appliedFilters.billingMonth.length > 0) {
            const monthNames = { '01': 'Jan', '02': 'Feb', '03': 'Mar', '04': 'Apr', '05': 'May', '06': 'Jun', '07': 'Jul', '08': 'Aug', '09': 'Sep', '10': 'Oct', '11': 'Nov', '12': 'Dec' };
            const months = appliedFilters.billingMonth.map(m => monthNames[m]).join(', ');
            chips += `<span class="filter-chip">Month: ${months} <i class="fas fa-times chip-remove" data-filter="billingMonth"></i></span>`;
        }

        if (appliedFilters.status.length > 0) {
            chips += `<span class="filter-chip">Status: ${appliedFilters.status.join(', ')} <i class="fas fa-times chip-remove" data-filter="status"></i></span>`;
        }

        if (appliedFilters.invoiceNumber) {
            chips += `<span class="filter-chip">Invoice: ${appliedFilters.invoiceNumber} <i class="fas fa-times chip-remove" data-filter="invoiceNumber"></i></span>`;
        }

        chipsContainer.innerHTML = chips;

        chipsContainer.querySelectorAll('.chip-remove').forEach(btn => {
            btn.addEventListener('click', function() {
                const filter = this.dataset.filter;
                if (filter === 'account') {
                    appliedFilters.accountId = null;
                    appliedFilters.accountName = null;
                    document.getElementById('accountNameFilter').value = '';
                } else if (filter === 'invoiceNumber') {
                    appliedFilters.invoiceNumber = '';
                    document.getElementById('invoiceNumberFilter').value = '';
                } else {
                    appliedFilters[filter] = [];
                    document.querySelectorAll(`[data-filter="${filter}"] .form-check-input`).forEach(cb => cb.checked = false);
                    updateDropdownLabel(document.querySelector(`[data-filter="${filter}"]`));
                }

                currentPage = 1;
                const filteredData = filterInvoices(invoicesData);
                const sortedData = sortInvoices(filteredData);
                renderInvoices(sortedData);
                updateActiveFiltersDisplay();
            });
        });
    }

    let currentDrawerInvoice = null;

    function openDrawer(invoiceId) {
        const invoice = invoicesData.find(inv => inv.id === invoiceId);
        if (!invoice) return;

        currentDrawerInvoice = invoice;

        document.getElementById('invoiceDrawer').classList.add('open');
        document.getElementById('drawerOverlay').classList.add('show');

        document.getElementById('drawerInvoiceNumber').textContent = 'Invoice #' + invoice.invoiceNumber;
        document.getElementById('drawerAccountName').textContent = invoice.accountName;

        const statusBadge = document.getElementById('drawerStatusBadge');
        statusBadge.outerHTML = getStatusBadge(invoice.status).replace('status-badge', 'status-badge" id="drawerStatusBadge');

        const billingPeriod = formatBillingPeriodMonthYear(invoice.billingPeriodStart, invoice.billingPeriodEnd);
        document.getElementById('drawerBillingPeriod').textContent = 'Billing Period: ' + billingPeriod;

        document.getElementById('drawerIssueDate').textContent = formatDate(invoice.issueDate);
        document.getElementById('drawerDueDate').textContent = formatDate(invoice.dueDate);

        document.getElementById('drawerSubtotal').textContent = formatCurrency(invoice.subtotal, invoice.currency);
        document.getElementById('drawerVat').textContent = formatCurrency(invoice.vat, invoice.currency);
        document.getElementById('drawerTotal').textContent = formatCurrency(invoice.total, invoice.currency);

        const amountPaid = (invoice.total || 0) - (invoice.balanceDue || 0);
        document.getElementById('drawerAmountPaid').textContent = formatCurrency(amountPaid, invoice.currency);

        const balanceEl = document.getElementById('drawerBalanceOutstanding');
        balanceEl.textContent = formatCurrency(invoice.balanceDue, invoice.currency);
        balanceEl.className = invoice.balanceDue > 0 ? 'fw-bold text-danger' : 'fw-bold text-success';

        const lineItemsContainer = document.getElementById('drawerLineItems');
        if (invoice.lineItems && invoice.lineItems.length > 0) {
            lineItemsContainer.innerHTML = invoice.lineItems.map(item => `
                <div class="invoice-line-item">
                    <div>
                        <div class="fw-medium">${item.name || 'Item'}</div>
                        <div class="small text-muted">${item.description || ''}</div>
                    </div>
                    <div class="text-end">
                        <div class="fw-medium">${formatCurrency(item.amount, invoice.currency)}</div>
                    </div>
                </div>
            `).join('');
        } else {
            lineItemsContainer.innerHTML = '<div class="text-muted small fst-italic">No line items available</div>';
        }

        document.getElementById('viewAccountBtn').href = `/admin/accounts/${invoice.accountId}`;
    }

    function closeDrawer() {
        document.getElementById('invoiceDrawer').classList.remove('open');
        document.getElementById('drawerOverlay').classList.remove('show');
    }

    window.openDrawer = openDrawer;
    window.closeDrawer = closeDrawer;
    window.loadInvoices = loadInvoices;

    window.navigateToAccount = function(accountId) {
        window.location.href = '/admin/accounts/' + accountId;
    };

    window.downloadPdf = function(invoiceId, pdfUrl) {
        if (pdfUrl) {
            window.open(pdfUrl, '_blank');
        } else {
            alert('PDF not available for this invoice.');
        }
    };

    document.getElementById('closeDrawerBtn').addEventListener('click', closeDrawer);
    document.getElementById('drawerOverlay').addEventListener('click', closeDrawer);

    document.getElementById('downloadPdfBtn').addEventListener('click', function() {
        if (currentDrawerInvoice && currentDrawerInvoice.pdfUrl) {
            window.open(currentDrawerInvoice.pdfUrl, '_blank');
        } else {
            alert('PDF not available for this invoice.');
        }
    });

    document.getElementById('selectAll').addEventListener('change', function() {
        const checked = this.checked;
        document.querySelectorAll('.invoice-checkbox').forEach(cb => cb.checked = checked);
    });

    document.getElementById('exportBtn').addEventListener('click', function() {
        alert('Export functionality will be implemented. This will export the filtered invoice data to CSV/Excel.');
    });

    const createInvoiceCreditModal = new bootstrap.Modal(document.getElementById('createInvoiceCreditModal'));
    
    let selectedCustomer = null;
    let customerSearchTimeout = null;
    
    const mockCustomers = [
        { id: 'ACC-001', name: 'TechStart Solutions', status: 'Live', vatRegistered: true, vatRate: 20, reverseCharge: false, vatCountry: 'GB' },
        { id: 'ACC-002', name: 'EduLearn Institute', status: 'Live', vatRegistered: false, vatRate: 0, reverseCharge: false, vatCountry: 'GB' },
        { id: 'ACC-003', name: 'GreenEnergy Co', status: 'Test', vatRegistered: true, vatRate: 20, reverseCharge: false, vatCountry: 'GB' },
        { id: 'ACC-004', name: 'HealthCare Plus', status: 'Live', vatRegistered: true, vatRate: 0, reverseCharge: true, vatCountry: 'DE' },
        { id: 'ACC-005', name: 'FoodService Network', status: 'Suspended', vatRegistered: true, vatRate: 20, reverseCharge: false, vatCountry: 'GB' },
        { id: 'ACC-006', name: 'RetailMax Ltd', status: 'Live', vatRegistered: true, vatRate: 20, reverseCharge: false, vatCountry: 'GB' },
        { id: 'ACC-007', name: 'LogiTrans Systems', status: 'Test', vatRegistered: false, vatRate: 0, reverseCharge: false, vatCountry: 'GB' },
        { id: 'ACC-008', name: 'MediaWorks Agency', status: 'Live', vatRegistered: true, vatRate: 0, reverseCharge: true, vatCountry: 'FR' },
        { id: 'ACC-009', name: 'FinanceFirst Group', status: 'Live', vatRegistered: true, vatRate: 20, reverseCharge: false, vatCountry: 'GB' },
        { id: 'ACC-010', name: 'BuildRight Construction', status: 'Test', vatRegistered: true, vatRate: 20, reverseCharge: false, vatCountry: 'GB' }
    ];
    
    function searchCustomers(query) {
        const lowerQuery = query.toLowerCase();
        return mockCustomers.filter(c => 
            c.name.toLowerCase().includes(lowerQuery) || 
            c.id.toLowerCase().includes(lowerQuery)
        );
    }
    
    function getStatusBadgeClass(status) {
        switch(status) {
            case 'Live': return 'bg-success';
            case 'Test': return 'bg-warning text-dark';
            case 'Suspended': return 'bg-danger';
            default: return 'bg-secondary';
        }
    }
    
    function renderCustomerDropdown(customers) {
        const dropdown = document.getElementById('customerTypeaheadDropdown');
        if (customers.length === 0) {
            dropdown.innerHTML = '<div class="customer-typeahead-no-results">No customers found</div>';
        } else {
            dropdown.innerHTML = customers.map(c => `
                <div class="customer-typeahead-item" data-id="${c.id}" data-name="${c.name}" data-status="${c.status}">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="customer-name">${c.name}</span>
                            <span class="customer-account-id ms-2">${c.id}</span>
                        </div>
                        <span class="badge ${getStatusBadgeClass(c.status)} badge-sm">${c.status}</span>
                    </div>
                </div>
            `).join('');
        }
        dropdown.classList.add('show');
    }
    
    function selectCustomer(id, name, status) {
        const customer = mockCustomers.find(c => c.id === id);
        selectedCustomer = customer || { id, name, status, vatRegistered: false, vatRate: 0, reverseCharge: false, vatCountry: 'GB' };
        document.getElementById('selectedCustomerId').value = id;
        document.getElementById('customerSearchInput').classList.add('d-none');
        document.getElementById('selectedCustomerDisplay').classList.remove('d-none');
        document.getElementById('selectedCustomerName').textContent = name;
        document.getElementById('selectedCustomerAccountId').textContent = id;
        const statusBadge = document.getElementById('selectedCustomerStatus');
        statusBadge.textContent = status;
        statusBadge.className = `badge ms-2 ${getStatusBadgeClass(status)}`;
        document.getElementById('customerTypeaheadDropdown').classList.remove('show');
        document.getElementById('customerSearchInput').classList.remove('is-invalid');
        updateInvoiceSummary();
        validateForm();
    }
    
    function clearCustomerSelection() {
        selectedCustomer = null;
        document.getElementById('selectedCustomerId').value = '';
        document.getElementById('customerSearchInput').value = '';
        document.getElementById('customerSearchInput').classList.remove('d-none');
        document.getElementById('selectedCustomerDisplay').classList.add('d-none');
        updateInvoiceSummary();
        validateForm();
    }
    
    document.getElementById('customerSearchInput').addEventListener('input', function(e) {
        const query = e.target.value.trim();
        clearTimeout(customerSearchTimeout);
        
        if (query.length < 2) {
            document.getElementById('customerTypeaheadDropdown').classList.remove('show');
            return;
        }
        
        customerSearchTimeout = setTimeout(() => {
            const results = searchCustomers(query);
            renderCustomerDropdown(results);
        }, 300);
    });
    
    document.getElementById('customerSearchInput').addEventListener('focus', function() {
        if (this.value.trim().length >= 2) {
            const results = searchCustomers(this.value.trim());
            renderCustomerDropdown(results);
        }
    });
    
    document.getElementById('customerTypeaheadDropdown').addEventListener('click', function(e) {
        const item = e.target.closest('.customer-typeahead-item');
        if (item) {
            selectCustomer(item.dataset.id, item.dataset.name, item.dataset.status);
        }
    });
    
    document.getElementById('clearCustomerBtn').addEventListener('click', clearCustomerSelection);
    
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.customer-typeahead-wrapper')) {
            document.getElementById('customerTypeaheadDropdown').classList.remove('show');
        }
    });
    
    document.getElementById('itemDescription').addEventListener('input', function() {
        document.getElementById('descCharCount').textContent = this.value.length;
        validateField('itemDescription');
        validateForm();
    });
    
    document.getElementById('itemQuantity').addEventListener('input', function() {
        validateField('itemQuantity');
        calculateLineTotal();
        validateForm();
    });
    
    document.getElementById('itemUnitPrice').addEventListener('input', function() {
        validateField('itemUnitPrice');
        calculateLineTotal();
        validateForm();
    });
    
    document.getElementById('overrideEmail').addEventListener('input', function() {
        validateField('overrideEmail');
        validateForm();
    });
    
    function validateField(fieldName) {
        let isValid = true;
        let field, errorEl;
        
        switch(fieldName) {
            case 'itemDescription':
                field = document.getElementById('itemDescription');
                isValid = field.value.trim().length > 0 && field.value.length <= 255;
                field.classList.toggle('is-invalid', !isValid && field.value.length > 0);
                break;
                
            case 'itemQuantity':
                field = document.getElementById('itemQuantity');
                const qty = parseFloat(field.value);
                isValid = !isNaN(qty) && qty >= 0.01;
                field.classList.toggle('is-invalid', !isValid && field.value.length > 0);
                break;
                
            case 'itemUnitPrice':
                field = document.getElementById('itemUnitPrice');
                const priceStr = field.value.trim();
                const priceRegex = /^-?\d+(\.\d{1,4})?$/;
                const price = parseFloat(priceStr);
                isValid = priceRegex.test(priceStr) && !isNaN(price);
                
                if (priceStr.length > 0 && priceStr.includes('.')) {
                    const decimals = priceStr.split('.')[1];
                    if (decimals && decimals.length > 4) {
                        isValid = false;
                        document.getElementById('itemUnitPriceError').textContent = 'Maximum 4 decimal places allowed';
                    }
                }
                field.classList.toggle('is-invalid', !isValid && priceStr.length > 0);
                break;
                
            case 'overrideEmail':
                field = document.getElementById('overrideEmail');
                const email = field.value.trim();
                if (email.length > 0) {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    isValid = emailRegex.test(email);
                    field.classList.toggle('is-invalid', !isValid);
                } else {
                    field.classList.remove('is-invalid');
                }
                break;
        }
        
        return isValid;
    }
    
    function calculateLineTotal() {
        const qty = parseFloat(document.getElementById('itemQuantity').value) || 0;
        const priceStr = document.getElementById('itemUnitPrice').value.trim();
        const price = parseFloat(priceStr) || 0;
        const total = qty * price;
        const totalRounded = total.toFixed(2);
        document.getElementById('lineTotal').value = totalRounded;
        
        const tooltipEl = document.getElementById('lineTotalTooltip');
        tooltipEl.setAttribute('data-bs-original-title', `Calculated: ${qty} × ${price} = ${total}`);
        
        updateInvoiceSummary();
    }
    
    function updateInvoiceSummary() {
        const qty = parseFloat(document.getElementById('itemQuantity').value) || 0;
        const priceStr = document.getElementById('itemUnitPrice').value.trim();
        const price = parseFloat(priceStr) || 0;
        const subtotal = qty * price;
        
        let vatRate = 0;
        let vatAmount = 0;
        let vatNote = '';
        
        if (selectedCustomer) {
            if (selectedCustomer.reverseCharge) {
                vatRate = 0;
                vatAmount = 0;
                vatNote = `Reverse charge applies (${selectedCustomer.vatCountry})`;
            } else if (selectedCustomer.vatRegistered) {
                vatRate = selectedCustomer.vatRate;
                vatAmount = subtotal * (vatRate / 100);
            } else {
                vatRate = 0;
                vatAmount = 0;
                vatNote = 'Customer not VAT registered';
            }
        }
        
        const total = subtotal + vatAmount;
        
        document.getElementById('summarySubtotal').textContent = '£' + subtotal.toFixed(2);
        document.getElementById('vatRateDisplay').textContent = `(${vatRate}%)`;
        document.getElementById('summaryVat').textContent = '£' + vatAmount.toFixed(2);
        document.getElementById('summaryTotal').textContent = '£' + total.toFixed(2);
        
        const vatNoteRow = document.getElementById('vatNoteRow');
        const vatNoteEl = document.getElementById('vatNote');
        if (vatNote) {
            vatNoteEl.textContent = vatNote;
            vatNoteRow.classList.remove('d-none');
        } else {
            vatNoteRow.classList.add('d-none');
        }
    }
    
    function validateForm() {
        const customerValid = selectedCustomer !== null;
        const descValid = document.getElementById('itemDescription').value.trim().length > 0;
        
        const qtyValue = parseFloat(document.getElementById('itemQuantity').value);
        const qtyValid = !isNaN(qtyValue) && qtyValue >= 0.01;
        
        const priceStr = document.getElementById('itemUnitPrice').value.trim();
        const priceRegex = /^-?\d+(\.\d{1,4})?$/;
        const priceValid = priceRegex.test(priceStr) && !isNaN(parseFloat(priceStr));
        
        const emailField = document.getElementById('overrideEmail');
        const email = emailField.value.trim();
        let emailValid = true;
        if (email.length > 0) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            emailValid = emailRegex.test(email);
        }
        
        const formValid = customerValid && descValid && qtyValid && priceValid && emailValid;
        document.getElementById('modalSubmitBtn').disabled = !formValid;
        
        return formValid;
    }
    
    function resetModalForm() {
        document.getElementById('createInvoiceCreditForm').reset();
        clearCustomerSelection();
        document.getElementById('itemQuantity').value = '1';
        document.getElementById('lineTotal').value = '0.00';
        document.getElementById('descCharCount').textContent = '0';
        
        document.getElementById('summarySubtotal').textContent = '£0.00';
        document.getElementById('vatRateDisplay').textContent = '(0%)';
        document.getElementById('summaryVat').textContent = '£0.00';
        document.getElementById('summaryTotal').textContent = '£0.00';
        document.getElementById('vatNoteRow').classList.add('d-none');
        
        ['itemDescription', 'itemQuantity', 'itemUnitPrice', 'overrideEmail', 'customerSearchInput'].forEach(id => {
            document.getElementById(id).classList.remove('is-invalid');
        });
        
        document.getElementById('modalSubmitBtn').disabled = true;
    }
    
    function openCreateModal(mode) {
        const isInvoice = mode === 'invoice';
        
        document.getElementById('formMode').value = mode;
        document.getElementById('modalTitleText').textContent = isInvoice ? 'Create Customer Invoice' : 'Create Customer Credit';
        document.getElementById('modalTitleIcon').className = isInvoice ? 'fas fa-file-invoice me-2' : 'fas fa-credit-card me-2';
        document.getElementById('modalDescription').textContent = isInvoice 
            ? 'Complete the form below to create a new customer invoice.' 
            : 'Complete the form below to create a new customer credit.';
        document.getElementById('modalSubmitBtnText').textContent = isInvoice ? 'Create invoice' : 'Create credit';
        
        resetModalForm();
        
        const tooltip = new bootstrap.Tooltip(document.getElementById('lineTotalTooltip'));
        
        createInvoiceCreditModal.show();
    }
    
    document.getElementById('createInvoiceBtn').addEventListener('click', function() {
        openCreateModal('invoice');
    });
    
    document.getElementById('createCreditBtn').addEventListener('click', function() {
        openCreateModal('credit');
    });
    
    document.getElementById('createInvoiceCreditModal').addEventListener('hidden.bs.modal', function() {
        resetModalForm();
    });

    loadInvoices();
});
</script>
@endpush
