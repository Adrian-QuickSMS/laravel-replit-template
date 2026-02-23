@extends('layouts.quicksms')

@section('title', 'Invoices')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/quicksms-pastel.css') }}">
<style>
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
    background: #fff;
    border-radius: 0.75rem;
    border: 1px solid #e9ecef;
}
.table thead th {
    background: #f8f9fa !important;
    border-bottom: 1px solid #e9ecef !important;
    padding: 0.75rem 0.5rem !important;
    font-weight: 600 !important;
    font-size: 0.8rem !important;
    color: #495057 !important;
    text-transform: none !important;
    letter-spacing: normal !important;
}
.table tbody td {
    padding: 0.75rem 0.5rem !important;
    vertical-align: middle;
    border-bottom: 1px solid #f1f3f5 !important;
    font-size: 0.85rem;
    color: #495057;
}
.table tbody tr:last-child td {
    border-bottom: none !important;
}
.table tbody tr:hover td {
    background-color: #f8f9fa !important;
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
.btn-xs {
    padding: 0.2rem 0.5rem;
    font-size: 0.7rem;
    line-height: 1.4;
}
.summary-stat-card {
    background: linear-gradient(135deg, rgba(136, 108, 192, 0.05) 0%, rgba(136, 108, 192, 0.02) 100%);
    border: 1px solid rgba(136, 108, 192, 0.15);
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
.filter-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.25rem 0.5rem;
    border-radius: 16px;
    font-size: 0.75rem;
    font-weight: 500;
    background-color: var(--primary);
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
    background-color: rgba(136, 108, 192, 0.3);
    color: #6c757d;
    border: 1px dashed var(--primary);
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
    border-color: var(--primary);
}
.date-preset-btn.active {
    background: var(--primary);
    color: #fff;
    border-color: var(--primary);
}
.account-financial-summary {
    background: linear-gradient(135deg, #fff 0%, #faf8fc 100%);
    border: 1px solid rgba(136, 108, 192, 0.2);
    border-radius: 12px;
    position: sticky;
    top: 0;
    z-index: 100;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}
.financial-metric {
    text-align: center;
    padding: 0.75rem;
    border-right: 1px solid rgba(136, 108, 192, 0.1);
}
.financial-metric:last-child {
    border-right: none;
}
.financial-metric .metric-label {
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #6c757d;
    margin-bottom: 0.25rem;
}
.financial-metric .metric-value {
    font-size: 1.1rem;
    font-weight: 700;
    color: #2c2c2c;
}
.financial-metric .metric-value.text-success { color: #1cbb8c !important; }
.financial-metric .metric-value.text-warning { color: #cc9900 !important; }
.financial-metric .metric-value.text-danger { color: #dc3545 !important; }
.billing-mode-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.35rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
}
.billing-mode-badge.prepaid {
    background: rgba(28, 187, 140, 0.15);
    color: #1cbb8c;
}
.billing-mode-badge.postpaid {
    background: rgba(136, 108, 192, 0.15);
    color: #886CC0;
}
.account-status-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.35rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
}
.account-status-badge.active {
    background: rgba(28, 187, 140, 0.15);
    color: #1cbb8c;
}
.account-status-badge.credit-hold {
    background: rgba(220, 53, 69, 0.15);
    color: #dc3545;
}
.account-status-badge i {
    margin-right: 0.35rem;
}
.credit-helper-text {
    font-size: 0.75rem;
    color: #6c757d;
    line-height: 1.4;
}
.credit-helper-text strong {
    color: #495057;
}
.financial-refresh-indicator {
    font-size: 0.65rem;
    color: #adb5bd;
}
.financial-refresh-indicator.refreshing {
    color: var(--primary);
}
@media (max-width: 768px) {
    .invoice-drawer {
        width: 100%;
        right: -100%;
    }
    .financial-metric {
        border-right: none;
        border-bottom: 1px solid rgba(136, 108, 192, 0.1);
        padding: 0.5rem;
    }
    .financial-metric:last-child {
        border-bottom: none;
    }
}
</style>
@endpush

@section('content')
@php
    // TODO: Replace with actual user role from auth system
    // Allowed roles: 'admin' (full access), 'finance' (purchase & invoices), 'viewer' (view/download only)
    $currentUserRole = 'admin';
    $canMakePayments = in_array($currentUserRole, ['admin', 'finance']);
@endphp
<div class="container-fluid invoices-container">
    <div class="row page-titles mb-2" style="flex-shrink: 0;">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('reporting') }}">Reporting</a></li>
            <li class="breadcrumb-item active">Invoices</li>
        </ol>
    </div>

    <div id="accountFinancialSummary" class="account-financial-summary mb-3 p-3" style="flex-shrink: 0;">
        <div class="row align-items-center">
            <div class="col-lg-9">
                <div class="row align-items-center">
                    <div class="col-6 col-md-4 col-lg-2 financial-metric">
                        <div class="metric-label">Billing Mode</div>
                        <div id="billingMode">
                            <span class="billing-mode-badge prepaid">
                                <i class="fas fa-wallet me-1"></i> Loading...
                            </span>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2 financial-metric">
                        <div class="metric-label">Current Balance</div>
                        <div class="metric-value text-muted" id="currentBalance">--</div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2 financial-metric">
                        <div class="metric-label">Credit Limit</div>
                        <div class="metric-value" id="creditLimit">--</div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2 financial-metric">
                        <div class="metric-label">Available Credit</div>
                        <div class="metric-value text-muted" id="availableCredit">--</div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2 financial-metric">
                        <div class="metric-label">Account Status</div>
                        <div id="accountStatus">
                            <span class="account-status-badge">
                                <i class="fas fa-spinner fa-spin"></i> Loading...
                            </span>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2 financial-metric">
                        <div class="metric-label">Last Updated</div>
                        <div class="financial-refresh-indicator" id="lastRefreshed">
                            <i class="fas fa-sync-alt me-1"></i> --
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 mt-3 mt-lg-0">
                <div class="d-flex flex-column gap-2">
                    @if($canMakePayments)
                    <a href="{{ route('purchase.messages') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus-circle me-1"></i> Top Up Balance
                    </a>
                    @endif
                    <button type="button" class="btn btn-link btn-sm text-muted p-0" data-bs-toggle="collapse" data-bs-target="#creditHelperPanel">
                        <i class="fas fa-question-circle me-1"></i> How does credit work?
                    </button>
                </div>
            </div>
        </div>
        <div class="collapse mt-3" id="creditHelperPanel">
            <div class="alert alert-pastel-primary mb-0">
                <div class="row">
                    <div class="col-md-6">
                        <div class="credit-helper-text">
                            <strong><i class="fas fa-info-circle me-1"></i> Understanding Your Credit</strong>
                            <ul class="mb-0 mt-2 ps-3">
                                <li><strong>Current Balance:</strong> Your prepaid credit available for sending messages.</li>
                                <li><strong>Credit Limit:</strong> Additional credit extended to your account.</li>
                                <li><strong>Available Credit:</strong> Total funds you can use (Balance + Credit Limit).</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="credit-helper-text">
                            <strong><i class="fas fa-exclamation-triangle me-1 text-warning"></i> Credit Limit Exceeded</strong>
                            <ul class="mb-0 mt-2 ps-3">
                                <li>If your balance falls below zero and exceeds your credit limit, your account enters <strong>Credit Hold</strong>.</li>
                                <li>While on Credit Hold, you cannot send messages until the balance is topped up.</li>
                                <li>Scheduled campaigns will be paused until credit is restored.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <div class="row flex-grow-1" style="min-height: 0;">
        <div class="col-12 d-flex flex-column" style="min-height: 0;">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap invoices-fixed-header">
                    <h5 class="card-title mb-2 mb-md-0">Invoice History</h5>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#filtersPanel">
                            <i class="fas fa-filter me-1"></i> Filters
                        </button>
                        <button type="button" class="btn btn-outline-primary btn-sm" id="exportBtn">
                            <i class="fas fa-download me-1"></i> Export
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="invoices-fixed-header">
                        <div class="collapse mb-3" id="filtersPanel">
                            <div class="card card-body border-0 rounded-3" style="background-color: #f0ebf8;">
                                <div class="row g-3 align-items-end">
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
                                    <div class="col-6 col-md-4 col-lg-3">
                                        <label class="form-label small fw-bold">Invoice Number</label>
                                        <input type="text" class="form-control form-control-sm" id="invoiceNumberFilter" placeholder="Enter ID..." style="background-color: #fff;">
                                    </div>
                                    <div class="col-12 col-md-4 col-lg-3 d-flex gap-2">
                                        <button type="button" class="btn btn-primary btn-sm flex-grow-1" id="applyFiltersBtn">
                                            <i class="fas fa-check me-1"></i> Apply Filters
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm flex-grow-1" id="resetFiltersBtn">
                                            <i class="fas fa-undo me-1"></i> Reset Filters
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
                                        <th>Invoice Number</th>
                                        <th>Billing Period</th>
                                        <th>Invoice Date</th>
                                        <th>Due Date</th>
                                        <th>Status</th>
                                        <th class="text-end">Amount (ex VAT)</th>
                                        <th class="text-end">VAT Amount</th>
                                        <th class="text-end">Total (inc VAT)</th>
                                        <th class="text-end">Balance Outstanding</th>
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
                                Showing <span id="showingStart">1</span>-<span id="showingEnd">10</span> of <span id="totalCount">12</span> invoices
                            </div>
                            <nav>
                                <ul class="pagination pagination-sm mb-0">
                                    <li class="page-item disabled">
                                        <a class="page-link" href="#" tabindex="-1"><i class="fas fa-chevron-left"></i></a>
                                    </li>
                                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                    <li class="page-item"><a class="page-link" href="#">2</a></li>
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
        </div>
        <button type="button" class="btn-close" id="closeDrawerBtn"></button>
    </div>
    <div class="invoice-drawer-body">
        <div class="alert alert-pastel-primary mb-3">
            <i class="fas fa-info-circle text-primary me-2"></i>
            Invoice data is synchronized from HubSpot. All values are read-only.
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

        <h6 class="mb-3"><i class="fas fa-receipt me-2 text-primary"></i>Invoice Summary</h6>
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

        <h6 class="mb-3"><i class="fas fa-list me-2 text-primary"></i>Line Items</h6>
        <div id="drawerLineItems">
            <div class="text-center text-muted py-3">
                <i class="fas fa-spinner fa-spin"></i> Loading...
            </div>
        </div>
    </div>
    <div class="invoice-drawer-footer">
        <div class="d-flex flex-column gap-2">
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-primary flex-grow-1" id="downloadPdfBtn">
                    <i class="fas fa-file-pdf me-1"></i> Download PDF
                </button>
                @if($canMakePayments)
                <button type="button" class="btn btn-primary flex-grow-1" id="payNowBtn" style="display: none;">
                    <i class="fas fa-credit-card me-1"></i> Pay Invoice
                </button>
                @endif
            </div>
            <button type="button" class="btn btn-outline-secondary w-100" id="viewBillingBreakdownBtn">
                <i class="fas fa-chart-pie me-1"></i> View Billing Breakdown
            </button>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/15.7.1/nouislider.min.css">
<div class="modal fade" id="topUpModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-2 text-primary"></i>Top Up Balance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-lg-8">
                        <div id="topUpTiersLoading" class="text-center py-5">
                            <div class="spinner-border text-primary" role="status"></div>
                            <div class="mt-2 text-muted">Loading pricing...</div>
                        </div>

                        <div id="topUpError" class="d-none">
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <span id="topUpErrorMessage">Failed to load pricing. Please try again.</span>
                            </div>
                        </div>

                        <div id="topUpBespokeTier" class="d-none">
                            <div class="col-12">
                                <div class="card topup-tier-card tier-bespoke tryal-gradient" data-tier="bespoke">
                                    <div class="tier-header">
                                        <span class="badge bg-white text-primary px-3 py-2 mb-2">
                                            <i class="fas fa-gem me-1"></i> Custom Contract
                                        </span>
                                        <h3 class="tier-title">Bespoke Pricing</h3>
                                        <p class="tier-volume">Volume: <strong>50,000 – 5,000,000</strong> messages</p>
                                    </div>
                                    <div class="tier-slider-section">
                                        <div class="slider-label">
                                            <span>Select Volume</span>
                                            <span class="slider-value" id="topUpBespokeSliderValue">100,000</span>
                                        </div>
                                        <div id="topUpBespokeSlider" class="volume-slider"></div>
                                        <div class="slider-range-labels">
                                            <span>50K</span>
                                            <span>5M</span>
                                        </div>
                                        <div class="numeric-inputs">
                                            <div class="numeric-input-group">
                                                <label>Message Volume</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control volume-input" id="topUpBespokeVolumeInput" data-tier="bespoke" value="100,000">
                                                    <span class="input-group-text">SMS</span>
                                                </div>
                                            </div>
                                            <div class="numeric-input-group">
                                                <label>Total Cost</label>
                                                <div class="input-group">
                                                    <span class="input-group-text currency-symbol">£</span>
                                                    <input type="text" class="form-control cost-input" id="topUpBespokeCostInput" data-tier="bespoke" value="2,850.00" readonly>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tier-body">
                                        <p class="tier-description">Tailored pricing for high-volume enterprise customers with custom requirements and dedicated support.</p>
                                        <div id="topUpBespokePricingBadges" class="pricing-badges">
                                            <div class="skeleton-badge"></div>
                                            <div class="skeleton-badge"></div>
                                        </div>
                                    </div>
                                    <div class="tier-footer">
                                        <button class="btn btn-purchase" onclick="selectTopUpTier('bespoke')">
                                            Select Plan
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="topUpTiersContainer" class="row g-4 d-none align-items-stretch">
                            <div class="col-md-6 d-flex">
                                <div class="card topup-tier-card tier-starter tryal-gradient w-100" data-tier="starter">
                                    <div class="tier-header">
                                        <h3 class="tier-title">Starter</h3>
                                        <p class="tier-volume">Volume: <strong>0 – 50,000</strong> messages</p>
                                    </div>
                                    <div class="tier-slider-section">
                                        <div class="slider-label">
                                            <span>Select Volume</span>
                                            <span class="slider-value" id="topUpStarterSliderValue">10,000</span>
                                        </div>
                                        <div id="topUpStarterSlider" class="volume-slider"></div>
                                        <div class="slider-range-labels">
                                            <span>0</span>
                                            <span>50K</span>
                                        </div>
                                        <div class="numeric-inputs">
                                            <div class="numeric-input-group">
                                                <label>Message Volume</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control volume-input" id="topUpStarterVolumeInput" data-tier="starter" value="10,000">
                                                    <span class="input-group-text">SMS</span>
                                                </div>
                                            </div>
                                            <div class="numeric-input-group">
                                                <label>Total Cost</label>
                                                <div class="input-group">
                                                    <span class="input-group-text currency-symbol">£</span>
                                                    <input type="text" class="form-control cost-input" id="topUpStarterCostInput" data-tier="starter" value="350.00" readonly>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tier-body">
                                        <p class="tier-description">Perfect for small and medium businesses getting started with SMS messaging.</p>
                                        <div id="topUpStarterPricingBadges" class="pricing-badges">
                                            <div class="skeleton-badge"></div>
                                            <div class="skeleton-badge"></div>
                                        </div>
                                    </div>
                                    <div class="tier-footer">
                                        <button class="btn btn-purchase" onclick="selectTopUpTier('starter')">
                                            Select Starter
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6 d-flex">
                                <div class="card topup-tier-card tier-enterprise tryal-gradient w-100" data-tier="enterprise">
                                    <div class="best-value-badge">
                                        <span class="badge bg-success px-3 py-2"><i class="fas fa-star me-1"></i>Best Value</span>
                                    </div>
                                    <div class="tier-header">
                                        <h3 class="tier-title">Enterprise</h3>
                                        <p class="tier-volume">Volume: <strong>50,000 – 1,000,000</strong> messages</p>
                                    </div>
                                    <div class="tier-slider-section">
                                        <div class="slider-label">
                                            <span>Select Volume</span>
                                            <span class="slider-value" id="topUpEnterpriseSliderValue">100,000</span>
                                        </div>
                                        <div id="topUpEnterpriseSlider" class="volume-slider"></div>
                                        <div class="slider-range-labels">
                                            <span>50K</span>
                                            <span>1M</span>
                                        </div>
                                        <div class="numeric-inputs">
                                            <div class="numeric-input-group">
                                                <label>Message Volume</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control volume-input" id="topUpEnterpriseVolumeInput" data-tier="enterprise" value="100,000">
                                                    <span class="input-group-text">SMS</span>
                                                </div>
                                            </div>
                                            <div class="numeric-input-group">
                                                <label>Total Cost</label>
                                                <div class="input-group">
                                                    <span class="input-group-text currency-symbol">£</span>
                                                    <input type="text" class="form-control cost-input" id="topUpEnterpriseCostInput" data-tier="enterprise" value="2,850.00" readonly>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tier-body">
                                        <p class="tier-description">Designed for larger organizations with higher messaging volumes.</p>
                                        <div id="topUpEnterprisePricingBadges" class="pricing-badges">
                                            <div class="skeleton-badge"></div>
                                            <div class="skeleton-badge"></div>
                                        </div>
                                    </div>
                                    <div class="tier-footer">
                                        <button class="btn btn-purchase" onclick="selectTopUpTier('enterprise')">
                                            Select Enterprise
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4">
                        <div class="card order-summary-card sticky-top" style="top: 1rem;">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-shopping-cart me-2"></i>Order Summary</h5>
                            </div>
                            <div class="card-body">
                                <div id="topUpOrderPlaceholder">
                                    <p class="text-muted text-center py-3">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Select a pricing tier to continue
                                    </p>
                                </div>
                                
                                <div id="topUpOrderSummary" class="d-none">
                                    <div class="summary-row">
                                        <span>Selected Tier</span>
                                        <span id="topUpSelectedTierName">-</span>
                                    </div>
                                    <div class="summary-row">
                                        <span>Message Volume</span>
                                        <span id="topUpSelectedQuantity">-</span>
                                    </div>
                                    <div class="summary-row">
                                        <span>Net Total</span>
                                        <span id="topUpNetTotal">-</span>
                                    </div>
                                    <div class="summary-row" id="topUpVatRow">
                                        <span>VAT (20%)</span>
                                        <span id="topUpVatAmount">-</span>
                                    </div>
                                    <div class="summary-row total">
                                        <span>Total Payable</span>
                                        <span id="topUpTotalPayable">-</span>
                                    </div>
                                </div>
                                
                                <div class="vat-info mt-3">
                                    <i class="fas fa-info-circle me-1"></i>
                                    VAT at 20% will be applied at invoice level.
                                </div>
                                
                                <button id="topUpProceedBtn" class="btn btn-primary w-100 mt-4" disabled>
                                    <i class="fas fa-credit-card me-2"></i>Proceed to Payment
                                </button>
                                
                                <div class="mt-3 text-center">
                                    <small class="text-muted">
                                        <i class="fas fa-lock me-1"></i>
                                        Secure payment via Stripe (PCI DSS compliant)
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<style>
.tryal-gradient {
    background: linear-gradient(135deg, var(--primary, #886CC0) 0%, #a78bfa 50%, #c4b5fd 100%);
}
.best-value-badge {
    position: absolute;
    top: -12px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 10;
}
.best-value-badge .badge {
    border-radius: 0.5rem;
    font-size: 0.75rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}
.topup-tier-card {
    border: none;
    border-radius: 0.75rem;
    transition: all 0.2s ease;
    height: 100%;
    overflow: visible;
    position: relative;
    display: flex;
    flex-direction: column;
}
.topup-tier-card:hover {
    box-shadow: 0 8px 24px rgba(111, 66, 193, 0.25);
    transform: translateY(-2px);
}
.topup-tier-card.selected-tier {
    border: 3px solid var(--primary) !important;
    box-shadow: 0 0 0 4px rgba(111, 66, 193, 0.2) !important;
}
.topup-tier-card .tier-header {
    padding: 1.5rem;
    text-align: center;
    position: relative;
    z-index: 1;
}
.topup-tier-card .tier-header h3,
.topup-tier-card .tier-header .tier-volume,
.topup-tier-card .tier-header .tier-volume strong {
    color: #fff;
}
.topup-tier-card .tier-title {
    margin-bottom: 0.5rem;
    font-weight: 700;
    font-size: 1.5rem;
}
.topup-tier-card .tier-slider-section {
    padding: 1.25rem 1.5rem;
    background: transparent;
    position: relative;
    z-index: 1;
}
.topup-tier-card .slider-label {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}
.topup-tier-card .slider-label span,
.topup-tier-card .slider-range-labels span {
    color: rgba(255, 255, 255, 0.8);
}
.topup-tier-card .slider-value {
    font-size: 1.25rem;
    font-weight: 700;
    color: #fff;
}
.topup-tier-card .volume-slider {
    height: 8px;
}
.topup-tier-card .volume-slider .noUi-handle {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    top: -6px;
    right: -10px;
    background: #fff;
    border: 2px solid var(--primary);
    box-shadow: 0 2px 6px rgba(0,0,0,0.15);
    cursor: pointer;
}
.topup-tier-card .volume-slider .noUi-handle:before,
.topup-tier-card .volume-slider .noUi-handle:after {
    display: none;
}
.topup-tier-card .volume-slider .noUi-connect {
    background: #fff;
}
.topup-tier-card .volume-slider .noUi-target {
    background: rgba(255, 255, 255, 0.3);
    border: none;
    border-radius: 4px;
}
.topup-tier-card .slider-range-labels {
    display: flex;
    justify-content: space-between;
    margin-top: 0.5rem;
    font-size: 0.75rem;
}
.topup-tier-card .numeric-inputs {
    display: flex;
    gap: 1rem;
    margin-top: 1.25rem;
    padding-top: 1rem;
    border-top: 1px solid rgba(255,255,255,0.2);
}
.topup-tier-card .numeric-input-group {
    flex: 1;
}
.topup-tier-card .numeric-input-group label {
    display: block;
    font-size: 0.75rem;
    color: #fff;
    margin-bottom: 0.375rem;
    font-weight: 500;
}
.topup-tier-card .numeric-input-group .input-group-text {
    background: #fff;
    border-color: #dee2e6;
    font-size: 0.875rem;
}
.topup-tier-card .numeric-input-group .form-control {
    font-size: 0.875rem;
}
.topup-tier-card .tier-body {
    padding: 1rem 1.5rem;
    background: #fff;
    position: relative;
    z-index: 1;
    min-height: 140px;
    display: flex;
    flex-direction: column;
}
.topup-tier-card .tier-description {
    color: #6c757d;
    font-size: 0.875rem;
    margin-bottom: 1rem;
}
.topup-tier-card .pricing-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    flex: 1;
}
.topup-tier-card .pricing-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.375rem 0.75rem;
    background: #f8f9fa;
    border-radius: 2rem;
    font-size: 0.75rem;
    color: #495057;
}
.topup-tier-card .skeleton-badge {
    width: 100px;
    height: 24px;
    background: linear-gradient(90deg, #e9ecef 25%, #f8f9fa 50%, #e9ecef 75%);
    background-size: 200% 100%;
    animation: shimmer 1.5s infinite;
    border-radius: 2rem;
}
@keyframes shimmer {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}
.topup-tier-card .tier-footer {
    padding: 1rem 1.5rem 1.5rem;
    background: #fff;
    text-align: center;
    margin-top: auto;
    border-radius: 0 0 0.75rem 0.75rem;
}
.topup-tier-card .btn-purchase {
    width: 100%;
    padding: 0.75rem 1.5rem;
    background: var(--primary);
    border: none;
    color: #fff;
    border-radius: 0.5rem;
    font-weight: 600;
    transition: all 0.2s;
}
.topup-tier-card .btn-purchase:hover {
    background: #7659b5;
    transform: translateY(-1px);
}
.order-summary-card {
    border: none;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}
.order-summary-card .card-header {
    background: var(--primary);
    color: #fff;
    border: none;
}
.order-summary-card .card-header h5 {
    margin: 0;
}
.order-summary-card .summary-row {
    display: flex;
    justify-content: space-between;
    padding: 0.75rem 0;
    border-bottom: 1px solid #e9ecef;
}
.order-summary-card .summary-row.total {
    border-bottom: none;
    padding-top: 1rem;
    font-size: 1.125rem;
    color: var(--primary);
}
.order-summary-card .vat-info {
    font-size: 0.75rem;
    color: #6c757d;
    background: #f8f9fa;
    padding: 0.75rem;
    border-radius: 0.375rem;
}
</style>

<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-credit-card me-2 text-primary"></i>Pay Invoice</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="paymentModalBody">
                <div class="text-center mb-4">
                    <div class="mb-3">
                        <span class="badge bg-primary fs-6 px-3 py-2" id="paymentInvoiceNumber">INV-2025-0008</span>
                    </div>
                    <div class="display-5 fw-bold text-primary mb-1" id="paymentAmount">&pound;1,250.00</div>
                    <small class="text-muted">Balance Outstanding</small>
                </div>

                <div class="card mb-3" style="background-color: #f8f9fa; border: none;">
                    <div class="card-body py-2">
                        <div class="row small">
                            <div class="col-6">
                                <div class="text-muted">Status</div>
                                <div class="fw-medium" id="paymentInvoiceStatus">Issued</div>
                            </div>
                            <div class="col-6 text-end">
                                <div class="text-muted">Due Date</div>
                                <div class="fw-medium" id="paymentDueDate">16 Jan 2025</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="alert alert-pastel-primary mb-0">
                    <i class="fas fa-lock text-primary me-2"></i>
                    <strong>Secure Payment</strong><br>
                    <small>You'll be redirected to Stripe to complete your payment. We never store your card details (PCI DSS compliant).</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmPaymentBtn">
                    <i class="fas fa-external-link-alt me-1"></i> Pay with Stripe
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="paymentProcessingModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-body text-center py-4">
                <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;"></div>
                <h6 class="mb-1">Preparing Payment</h6>
                <small class="text-muted">Redirecting to Stripe...</small>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/15.7.1/nouislider.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let invoicesData = [];
    let isLoading = false;

    const currentUserRole = @json($currentUserRole);
    const canMakePayments = ['admin', 'finance'].includes(currentUserRole);

    const billingDetails = {
        company: '',
        address: '',
        vatNumber: '',
        poRef: '-'
    };

    async function loadAccountSummary() {
        try {
            const response = await fetch('/api/invoices/account-summary');
            const data = await response.json();
            if (data.success) {
                const sym = {'GBP':'£','EUR':'€','USD':'$'}[data.currency] || '£';
                const fmt = (v) => sym + parseFloat(v || 0).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');

                const mode = data.billingMode || 'prepaid';
                const modeLabel = mode === 'postpaid' ? 'Postpaid' : 'Prepaid';
                const modeIcon = mode === 'postpaid' ? 'fa-file-invoice-dollar' : 'fa-wallet';
                document.getElementById('billingMode').innerHTML = `<span class="billing-mode-badge ${mode}"><i class="fas ${modeIcon} me-1"></i> ${modeLabel}</span>`;

                const balEl = document.getElementById('currentBalance');
                balEl.textContent = fmt(data.currentBalance);
                balEl.className = 'metric-value ' + (parseFloat(data.currentBalance) >= 0 ? 'text-success' : 'text-danger');

                document.getElementById('creditLimit').textContent = fmt(data.creditLimit);

                const availEl = document.getElementById('availableCredit');
                availEl.textContent = fmt(data.availableCredit);
                availEl.className = 'metric-value ' + (parseFloat(data.availableCredit) >= 0 ? 'text-success' : 'text-danger');

                const status = data.accountStatus || 'active';
                const statusClass = status === 'active' ? 'active' : 'credit-hold';
                const statusIcon = status === 'active' ? 'fa-check-circle' : 'fa-exclamation-triangle';
                const statusLabel = status.charAt(0).toUpperCase() + status.slice(1);
                document.getElementById('accountStatus').innerHTML = `<span class="account-status-badge ${statusClass}"><i class="fas ${statusIcon}"></i> ${statusLabel}</span>`;

                const lastUp = data.lastUpdated ? new Date(data.lastUpdated).toLocaleString('en-GB', {day:'2-digit',month:'short',hour:'2-digit',minute:'2-digit'}) : 'Just now';
                document.getElementById('lastRefreshed').innerHTML = `<i class="fas fa-sync-alt me-1"></i> ${lastUp}`;
            }
        } catch (e) {
            console.error('Error loading account summary:', e);
        }
    }

    loadAccountSummary();

    function formatDate(dateStr) {
        if (!dateStr) return '-';
        const date = new Date(dateStr);
        return date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
    }

    function formatBillingPeriod(start, end) {
        if (!start && !end) return '-';
        if (start && end) {
            return formatDate(start) + ' - ' + formatDate(end);
        }
        return start ? formatDate(start) : formatDate(end);
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

    function formatBillingPeriodMonthYear(start, end) {
        if (!start && !end) return '-';
        const dateToUse = start || end;
        const date = new Date(dateToUse);
        return date.toLocaleDateString('en-GB', { month: 'short', year: 'numeric' });
    }

    function showLoading() {
        isLoading = true;
        const tbody = document.getElementById('invoicesTableBody');
        tbody.innerHTML = `
            <tr>
                <td colspan="11" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <div class="mt-2 text-muted">Loading invoices from HubSpot...</div>
                </td>
            </tr>
        `;
    }

    function showError(message) {
        const tbody = document.getElementById('invoicesTableBody');
        tbody.innerHTML = `
            <tr>
                <td colspan="11" class="text-center py-5">
                    <div class="text-danger mb-2">
                        <i class="fas fa-exclamation-triangle fa-2x"></i>
                    </div>
                    <div class="fw-medium">Unable to load invoices</div>
                    <div class="text-muted small mt-1">${message}</div>
                    <button class="btn btn-outline-primary btn-sm mt-3" onclick="loadInvoices()">
                        <i class="fas fa-sync-alt me-1"></i> Try Again
                    </button>
                </td>
            </tr>
        `;
    }

    async function loadInvoices() {
        showLoading();

        try {
            const params = new URLSearchParams();

            if (appliedFilters.status) params.append('status', appliedFilters.status);
            if (appliedFilters.invoiceNumber) params.append('search', appliedFilters.invoiceNumber);
            if (appliedFilters.billingYear) params.append('billingYear', appliedFilters.billingYear);
            if (appliedFilters.billingMonth) params.append('billingMonth', appliedFilters.billingMonth);

            const response = await fetch('/api/invoices?' + params.toString());
            const data = await response.json();

            if (!data.success) {
                showError(data.error || 'Failed to load invoices');
                return;
            }

            invoicesData = data.invoices || [];

            renderInvoices(invoicesData);

        } catch (error) {
            console.error('Error loading invoices:', error);
            showError('Network error. Please check your connection and try again.');
        }
    }

    function renderInvoices(invoices) {
        isLoading = false;
        const tbody = document.getElementById('invoicesTableBody');
        tbody.innerHTML = '';

        if (invoices.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="11" class="text-center py-5 text-muted">
                        <i class="fas fa-file-invoice fa-2x mb-2"></i>
                        <div>No invoices found</div>
                    </td>
                </tr>
            `;
            return;
        }

        invoices.forEach(inv => {
            const row = document.createElement('tr');
            row.setAttribute('data-invoice-id', inv.id);

            const billingPeriod = formatBillingPeriodMonthYear(inv.billingPeriodStart, inv.billingPeriodEnd);
            const isPayableStatus = inv.status === 'issued' || inv.status === 'pending' || inv.status === 'overdue';
            const showPayNow = isPayableStatus && canMakePayments && inv.balanceDue > 0;
            const hasPdfUrl = inv.pdfUrl && inv.pdfUrl.length > 0;

            row.innerHTML = `
                <td onclick="event.stopPropagation();">
                    <input type="checkbox" class="form-check-input invoice-checkbox" value="${inv.id}">
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
                            <li><a class="dropdown-item ${hasPdfUrl ? '' : 'disabled text-muted'}" href="#" onclick="downloadPdf('${inv.id}', '${inv.pdfUrl || ''}'); return false;"><i class="fas fa-file-pdf me-2"></i>Download PDF${hasPdfUrl ? '' : ' <small class="text-muted">(unavailable)</small>'}</a></li>
                            ${showPayNow ? 
                                `<li><hr class="dropdown-divider"></li><li><a class="dropdown-item text-primary" href="#" onclick="payInvoice('${inv.id}'); return false;"><i class="fas fa-credit-card me-2"></i>Pay Now</a></li>` : ''
                            }
                        </ul>
                    </div>
                </td>
            `;
            row.addEventListener('click', () => openDrawer(inv.id));
            tbody.appendChild(row);
        });

        document.getElementById('totalCount').textContent = invoices.length;
        document.getElementById('showingStart').textContent = invoices.length > 0 ? '1' : '0';
        document.getElementById('showingEnd').textContent = Math.min(10, invoices.length);
    }

    async function openDrawer(invoiceId) {
        const drawerBody = document.querySelector('.invoice-drawer-body');
        const originalContent = drawerBody.innerHTML;

        document.getElementById('invoiceDrawer').classList.add('open');
        document.getElementById('drawerOverlay').classList.add('show');

        drawerBody.innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status"></div>
                <div class="mt-2 text-muted">Loading invoice details...</div>
            </div>
        `;

        try {
            const response = await fetch('/api/invoices/' + invoiceId);
            const data = await response.json();

            if (!data.success) {
                drawerBody.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        ${data.error || 'Failed to load invoice details'}
                    </div>
                `;
                return;
            }

            const invoice = data.invoice;
            renderDrawerContent(invoice);

        } catch (error) {
            console.error('Error loading invoice:', error);
            drawerBody.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Network error loading invoice details
                </div>
            `;
        }
    }

    let currentDrawerInvoice = null;

    function renderDrawerContent(invoice) {
        currentDrawerInvoice = invoice;
        
        document.getElementById('drawerInvoiceNumber').textContent = 'Invoice #' + invoice.invoiceNumber;
        
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
                        <div class="small text-muted">${item.description || (item.quantity && item.quantity > 1 ? item.quantity + ' x ' + formatCurrency(item.unitPrice, invoice.currency) : '')}</div>
                    </div>
                    <div class="text-end">
                        <div class="fw-medium">${formatCurrency(item.amount, invoice.currency)}</div>
                    </div>
                </div>
            `).join('');
        } else {
            lineItemsContainer.innerHTML = '<div class="text-muted small fst-italic">No line items available</div>';
        }
        
        const payNowBtn = document.getElementById('payNowBtn');
        if (payNowBtn) {
            const isPayableStatus = invoice.status === 'issued' || invoice.status === 'pending' || invoice.status === 'overdue';
            const showPayNow = isPayableStatus && canMakePayments && invoice.balanceDue > 0;
            
            if (showPayNow) {
                payNowBtn.style.display = 'block';
                payNowBtn.onclick = () => payInvoice(invoice.id);
            } else {
                payNowBtn.style.display = 'none';
            }
        }

        const downloadPdfBtn = document.getElementById('downloadPdfBtn');
        if (invoice.pdfUrl) {
            downloadPdfBtn.classList.remove('disabled');
            downloadPdfBtn.onclick = () => downloadPdf(invoice.id, invoice.pdfUrl);
        } else {
            downloadPdfBtn.classList.add('disabled');
            downloadPdfBtn.onclick = () => alert('PDF not yet available for this invoice. Please try again later.');
        }
        
        document.getElementById('viewBillingBreakdownBtn').onclick = () => navigateToBillingBreakdown(invoice);
    }
    
    function navigateToBillingBreakdown(invoice) {
        const billingDate = invoice.billingPeriodStart || invoice.issueDate;
        let billingMonth = '';
        
        if (billingDate) {
            const date = new Date(billingDate);
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            billingMonth = `${year}-${month}`;
        }
        
        const params = new URLSearchParams();
        
        if (billingMonth) {
            params.append('billingMonth', billingMonth);
        }
        
        if (invoice.invoiceNumber) {
            params.append('invoiceRef', invoice.invoiceNumber);
            params.append('fromInvoice', invoice.invoiceNumber);
        }
        
        const financeDataUrl = '/reporting/finance-data' + (params.toString() ? '?' + params.toString() : '');
        window.location.href = financeDataUrl;
    }

    function closeDrawer() {
        document.getElementById('invoiceDrawer').classList.remove('open');
        document.getElementById('drawerOverlay').classList.remove('show');
    }

    window.openDrawer = openDrawer;
    window.closeDrawer = closeDrawer;
    window.loadInvoices = loadInvoices;

    document.getElementById('closeDrawerBtn').addEventListener('click', closeDrawer);
    document.getElementById('drawerOverlay').addEventListener('click', closeDrawer);

    window.downloadPdf = async function(invoiceId, pdfUrl) {
        if (pdfUrl) {
            window.open(pdfUrl, '_blank');
            return;
        }

        try {
            const response = await fetch('/api/invoices/' + invoiceId + '/pdf');
            const data = await response.json();

            if (data.success && data.pdfUrl) {
                window.open(data.pdfUrl, '_blank');
            } else {
                alert(data.error || 'PDF not available for this invoice');
            }
        } catch (error) {
            alert('Error downloading PDF. Please try again.');
        }
    };

    let currentPaymentInvoice = null;

    window.payInvoice = function(invoiceId) {
        const invoice = invoicesData.find(i => i.id === invoiceId);
        if (!invoice) return;

        currentPaymentInvoice = invoice;

        document.getElementById('paymentInvoiceNumber').textContent = invoice.invoiceNumber;
        document.getElementById('paymentAmount').textContent = formatCurrency(invoice.balanceDue, invoice.currency);
        document.getElementById('paymentInvoiceStatus').textContent = invoice.status.charAt(0).toUpperCase() + invoice.status.slice(1);
        document.getElementById('paymentDueDate').textContent = formatDate(invoice.dueDate);

        const modal = new bootstrap.Modal(document.getElementById('paymentModal'));
        modal.show();
        closeDrawer();
    };

    document.getElementById('confirmPaymentBtn').addEventListener('click', async function() {
        if (!currentPaymentInvoice) return;

        const paymentModal = bootstrap.Modal.getInstance(document.getElementById('paymentModal'));
        paymentModal.hide();

        const processingModal = new bootstrap.Modal(document.getElementById('paymentProcessingModal'));
        processingModal.show();

        try {
            const response = await fetch('/api/invoices/' + currentPaymentInvoice.id + '/create-checkout-session', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    invoiceId: currentPaymentInvoice.id,
                    invoiceNumber: currentPaymentInvoice.invoiceNumber,
                    amount: currentPaymentInvoice.balanceDue,
                    currency: currentPaymentInvoice.currency || 'GBP'
                })
            });

            const data = await response.json();

            if (data.success && data.checkoutUrl) {
                window.location.href = data.checkoutUrl;
            } else {
                processingModal.hide();
                alert(data.error || 'Failed to create payment session. Please try again.');
            }
        } catch (error) {
            console.error('Payment error:', error);
            processingModal.hide();
            alert('An error occurred while preparing your payment. Please try again.');
        }
    });

    function handlePaymentReturn() {
        const urlParams = new URLSearchParams(window.location.search);
        const paymentStatus = urlParams.get('payment');
        const invoiceId = urlParams.get('invoice');
        const topupStatus = urlParams.get('topup');
        const topupAmount = urlParams.get('amount');

        window.history.replaceState({}, document.title, window.location.pathname);

        if (paymentStatus === 'success' && invoiceId) {
            showSuccessBanner('Invoice Payment Successful', 'Your invoice has been paid and your account has been updated.');
            loadInvoices();
            loadAccountSummary();
        } else if (paymentStatus === 'cancelled') {
            showWarningBanner('Payment Cancelled', 'Your invoice payment was cancelled. No charges were made.');
        } else if (topupStatus === 'success' && topupAmount) {
            const formattedAmount = '£' + parseFloat(topupAmount).toFixed(2);
            showSuccessBanner('Top-Up Successful', `${formattedAmount} has been added to your account balance.`);
            loadAccountSummary();
        } else if (topupStatus === 'cancelled') {
            showWarningBanner('Top-Up Cancelled', 'Your balance top-up was cancelled. No charges were made.');
        }
    }

    function showSuccessBanner(title, message) {
        const alertHtml = `
            <div class="alert alert-success alert-dismissible fade show payment-banner" role="alert" style="position: fixed; top: 80px; left: 50%; transform: translateX(-50%); z-index: 1060; max-width: 500px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                <i class="fas fa-check-circle me-2"></i>
                <strong>${title}</strong> ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', alertHtml);
        
        setTimeout(() => {
            const alert = document.querySelector('.payment-banner.alert-success');
            if (alert) {
                alert.classList.remove('show');
                setTimeout(() => alert.remove(), 150);
            }
        }, 8000);
    }

    function showWarningBanner(title, message) {
        const alertHtml = `
            <div class="alert alert-warning alert-dismissible fade show payment-banner" role="alert" style="position: fixed; top: 80px; left: 50%; transform: translateX(-50%); z-index: 1060; max-width: 500px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                <i class="fas fa-exclamation-circle me-2"></i>
                <strong>${title}</strong> ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', alertHtml);
        
        setTimeout(() => {
            const alert = document.querySelector('.payment-banner.alert-warning');
            if (alert) {
                alert.classList.remove('show');
                setTimeout(() => alert.remove(), 150);
            }
        }, 5000);
    }

    function showErrorBanner(title, message) {
        const alertHtml = `
            <div class="alert alert-danger alert-dismissible fade show payment-banner" role="alert" style="position: fixed; top: 80px; left: 50%; transform: translateX(-50%); z-index: 1060; max-width: 500px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                <i class="fas fa-times-circle me-2"></i>
                <strong>${title}</strong> ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', alertHtml);
        
        setTimeout(() => {
            const alert = document.querySelector('.payment-banner.alert-danger');
            if (alert) {
                alert.classList.remove('show');
                setTimeout(() => alert.remove(), 150);
            }
        }, 8000);
    }

    handlePaymentReturn();

    const topUpState = {
        products: {},
        selectedTier: null,
        sliderValues: {
            starter: 10000,
            enterprise: 100000,
            bespoke: 100000
        },
        isBespoke: false,
        isLoading: true,
        currency: 'GBP',
        vatRate: 0.20,
        sliders: {}
    };

    const topUpTierConfig = {
        starter: {
            name: 'Starter',
            volumeMin: 0,
            volumeMax: 50000,
            increment: 1000,
            defaultVolume: 10000
        },
        enterprise: {
            name: 'Enterprise',
            volumeMin: 50000,
            volumeMax: 1000000,
            increment: 50000,
            defaultVolume: 100000
        },
        bespoke: {
            name: 'Custom Contract',
            volumeMin: 50000,
            volumeMax: 5000000,
            increment: 50000,
            defaultVolume: 100000
        }
    };

    const topUpModal = document.getElementById('topUpModal');
    topUpModal.addEventListener('show.bs.modal', loadTopUpPricing);

    async function loadTopUpPricing() {
        topUpState.isLoading = true;
        document.getElementById('topUpTiersLoading').classList.remove('d-none');
        document.getElementById('topUpTiersContainer').classList.add('d-none');
        document.getElementById('topUpBespokeTier').classList.add('d-none');
        document.getElementById('topUpError').classList.add('d-none');

        try {
            const response = await fetch('/api/purchase/products');
            const data = await response.json();

            if (!data.success) {
                throw new Error(data.error || 'Failed to load pricing');
            }

            topUpState.products = data.products || {};
            topUpState.isBespoke = data.isBespoke || false;
            
            document.getElementById('topUpTiersLoading').classList.add('d-none');

            if (topUpState.isBespoke) {
                document.getElementById('topUpBespokeTier').classList.remove('d-none');
                initTopUpSliders(['bespoke']);
            } else {
                document.getElementById('topUpTiersContainer').classList.remove('d-none');
                initTopUpSliders(['starter', 'enterprise']);
            }
            
            updateTopUpPricingDisplay();

            topUpState.isLoading = false;
        } catch (error) {
            console.error('Failed to load pricing:', error);
            document.getElementById('topUpTiersLoading').classList.add('d-none');
            document.getElementById('topUpError').classList.remove('d-none');
            document.getElementById('topUpErrorMessage').textContent = error.message;
        }
    }

    function initTopUpSliders(tiers = ['starter', 'enterprise']) {
        tiers.forEach(tier => {
            const sliderEl = document.getElementById(`topUp${tier.charAt(0).toUpperCase() + tier.slice(1)}Slider`);
            if (!sliderEl || sliderEl.noUiSlider) return;

            const config = topUpTierConfig[tier];
            
            noUiSlider.create(sliderEl, {
                start: [config.defaultVolume],
                connect: [true, false],
                range: {
                    'min': config.volumeMin,
                    'max': config.volumeMax
                },
                step: config.increment,
                format: {
                    to: value => Math.round(value),
                    from: value => Number(value)
                }
            });

            topUpState.sliders[tier] = sliderEl.noUiSlider;

            sliderEl.noUiSlider.on('update', function(values) {
                const volume = values[0];
                topUpState.sliderValues[tier] = volume;
                updateTopUpTierDisplay(tier, volume);
            });
        });
    }

    function updateTopUpTierDisplay(tier, volume) {
        const tierCap = tier.charAt(0).toUpperCase() + tier.slice(1);
        const sliderValueEl = document.getElementById(`topUp${tierCap}SliderValue`);
        const volumeInputEl = document.getElementById(`topUp${tierCap}VolumeInput`);
        const costInputEl = document.getElementById(`topUp${tierCap}CostInput`);

        if (sliderValueEl) sliderValueEl.textContent = formatNumber(volume);
        if (volumeInputEl) volumeInputEl.value = formatNumber(volume);

        const smsProduct = topUpState.products['sms'];
        if (smsProduct && costInputEl) {
            const rate = tier === 'enterprise' 
                ? (smsProduct.price_enterprise || smsProduct.price) 
                : smsProduct.price;
            const cost = volume * rate;
            costInputEl.value = cost.toFixed(2);
        }

        if (topUpState.selectedTier === tier) {
            updateTopUpOrderSummary();
        }
    }

    const productLabels = {
        'sms': { name: 'SMS', unit: '/msg', decimals: 'trim' },
        'rcs_basic': { name: 'RCS Basic', unit: '/msg', decimals: 'trim' },
        'rcs_single': { name: 'RCS Single', unit: '/msg', decimals: 'trim' },
        'vmn': { name: 'VMN', unit: '/mo', decimals: 0 },
        'shortcode_keyword': { name: 'Shortcode', unit: '/mo', decimals: 0 },
        'ai': { name: 'AI Credits', unit: '/credit', decimals: 2 }
    };

    function formatProductPrice(price, decimals) {
        const symbols = { 'GBP': '£', 'EUR': '€', 'USD': '$' };
        const symbol = symbols[topUpState.currency] || '£';
        
        if (decimals === 'trim') {
            let formatted = parseFloat(price).toFixed(4);
            formatted = formatted.replace(/0+$/, '').replace(/\.$/, '');
            return symbol + formatted;
        } else {
            return symbol + parseFloat(price).toFixed(decimals);
        }
    }

    function generatePricingBadges(tier) {
        const products = topUpState.products;
        let badgesHtml = '';
        
        for (const [key, product] of Object.entries(products)) {
            const label = productLabels[key] || { name: key, unit: '', decimals: 2 };
            let price;
            
            if (tier === 'starter') {
                price = product.price;
            } else {
                price = product.price_enterprise || product.price;
            }
            
            if (price) {
                const formattedPrice = formatProductPrice(price, label.decimals);
                badgesHtml += `<span class="pricing-badge">${label.name}:<strong>${formattedPrice}</strong>${label.unit}</span>`;
            }
        }
        
        return badgesHtml;
    }

    function updateTopUpPricingDisplay() {
        if (topUpState.isBespoke) {
            const badgesEl = document.getElementById('topUpBespokePricingBadges');
            if (badgesEl) badgesEl.innerHTML = generatePricingBadges('bespoke');
            updateTopUpTierDisplay('bespoke', topUpState.sliderValues.bespoke);
        } else {
            const starterBadgesEl = document.getElementById('topUpStarterPricingBadges');
            const enterpriseBadgesEl = document.getElementById('topUpEnterprisePricingBadges');
            
            if (starterBadgesEl) starterBadgesEl.innerHTML = generatePricingBadges('starter');
            if (enterpriseBadgesEl) enterpriseBadgesEl.innerHTML = generatePricingBadges('enterprise');

            ['starter', 'enterprise'].forEach(tier => {
                updateTopUpTierDisplay(tier, topUpState.sliderValues[tier]);
            });
        }
    }

    function formatNumber(num) {
        return num.toLocaleString('en-GB');
    }

    function formatTopUpCurrency(amount, decimals = 2) {
        const symbols = { 'GBP': '£', 'EUR': '€', 'USD': '$' };
        const symbol = symbols[topUpState.currency] || '£';
        return symbol + parseFloat(amount || 0).toFixed(decimals);
    }

    window.selectTopUpTier = function(tier) {
        document.querySelectorAll('.topup-tier-card').forEach(c => c.classList.remove('selected-tier'));
        document.querySelector(`.topup-tier-card[data-tier="${tier}"]`)?.classList.add('selected-tier');
        
        topUpState.selectedTier = tier;
        updateTopUpOrderSummary();
    };

    function updateTopUpOrderSummary() {
        const tier = topUpState.selectedTier;
        
        if (!tier) {
            document.getElementById('topUpOrderPlaceholder').classList.remove('d-none');
            document.getElementById('topUpOrderSummary').classList.add('d-none');
            document.getElementById('topUpProceedBtn').disabled = true;
            return;
        }

        document.getElementById('topUpOrderPlaceholder').classList.add('d-none');
        document.getElementById('topUpOrderSummary').classList.remove('d-none');

        const volume = topUpState.sliderValues[tier];
        const smsProduct = topUpState.products['sms'];
        let rate;
        
        if (tier === 'bespoke' || tier === 'enterprise') {
            rate = smsProduct?.price_enterprise || smsProduct?.price || 0.0285;
        } else {
            rate = smsProduct?.price || 0.035;
        }
        
        const netTotal = volume * rate;
        const vatAmount = netTotal * topUpState.vatRate;
        const totalPayable = netTotal + vatAmount;

        const tierName = topUpTierConfig[tier].name;
        document.getElementById('topUpSelectedTierName').textContent = tierName;
        document.getElementById('topUpSelectedQuantity').textContent = formatNumber(volume) + ' SMS';
        document.getElementById('topUpNetTotal').textContent = formatTopUpCurrency(netTotal);
        document.getElementById('topUpVatAmount').textContent = formatTopUpCurrency(vatAmount);
        document.getElementById('topUpTotalPayable').textContent = formatTopUpCurrency(totalPayable);

        const minVolume = (tier === 'enterprise' || tier === 'bespoke') ? 50000 : 1000;
        document.getElementById('topUpProceedBtn').disabled = volume < minVolume;
    }

    document.querySelectorAll('#topUpModal .volume-input').forEach(input => {
        input.addEventListener('change', function() {
            const tier = this.dataset.tier;
            const config = topUpTierConfig[tier];
            let value = parseInt(this.value.replace(/,/g, '')) || 0;
            
            value = Math.max(config.volumeMin, Math.min(config.volumeMax, value));
            value = Math.round(value / config.increment) * config.increment;
            
            if (topUpState.sliders[tier]) {
                topUpState.sliders[tier].set(value);
            }
        });
    });


    document.getElementById('topUpProceedBtn').addEventListener('click', async function() {
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Processing...';

        const tier = topUpState.selectedTier;
        const volume = topUpState.sliderValues[tier];
        const smsProduct = topUpState.products['sms'];
        const rate = tier === 'enterprise' 
            ? (smsProduct?.price_enterprise || smsProduct?.price || 0.0285) 
            : (smsProduct?.price || 0.035);
        const amount = volume * rate;

        try {
            const response = await fetch('/api/topup/create-checkout-session', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    tier: tier,
                    volume: volume,
                    amount: amount,
                    currency: topUpState.currency
                })
            });

            const data = await response.json();

            if (data.success && data.checkoutUrl) {
                window.location.href = data.checkoutUrl;
            } else {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-credit-card me-2"></i>Proceed to Payment';
                alert(data.error || 'Failed to create payment session. Please try again.');
            }
        } catch (error) {
            console.error('Top-up error:', error);
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-credit-card me-2"></i>Proceed to Payment';
            alert('An error occurred. Please try again.');
        }
    });

    topUpModal.addEventListener('hidden.bs.modal', function() {
        document.querySelectorAll('.topup-tier-card').forEach(c => c.classList.remove('selected-tier'));
        topUpState.selectedTier = null;
        topUpState.sliderValues = { starter: 10000, enterprise: 100000, bespoke: 100000 };
        
        ['starter', 'enterprise', 'bespoke'].forEach(tier => {
            if (topUpState.sliders[tier]) {
                topUpState.sliders[tier].set(topUpTierConfig[tier].defaultVolume);
            }
        });
        
        document.getElementById('topUpOrderPlaceholder').classList.remove('d-none');
        document.getElementById('topUpOrderSummary').classList.add('d-none');
        document.getElementById('topUpProceedBtn').disabled = true;
    });

    document.getElementById('selectAll').addEventListener('change', function() {
        document.querySelectorAll('.invoice-checkbox').forEach(cb => {
            cb.checked = this.checked;
        });
    });

    let appliedFilters = {
        billingYear: [],
        billingMonth: [],
        status: [],
        invoiceNumber: ''
    };
    let pendingFilters = { ...appliedFilters };
    let filtersArePending = false;

    const monthNames = {
        '01': 'January', '02': 'February', '03': 'March', '04': 'April',
        '05': 'May', '06': 'June', '07': 'July', '08': 'August',
        '09': 'September', '10': 'October', '11': 'November', '12': 'December'
    };

    const defaultLabels = {
        billingYear: 'All Years',
        billingMonth: 'All Months',
        status: 'All Statuses'
    };

    function populateBillingYearOptions() {
        const container = document.getElementById('billingYearOptions');
        const currentYear = new Date().getFullYear();
        container.innerHTML = '';
        for (let year = currentYear; year >= 2015; year--) {
            const div = document.createElement('div');
            div.className = 'form-check';
            div.innerHTML = `<input class="form-check-input" type="checkbox" value="${year}" id="year${year}"><label class="form-check-label small" for="year${year}">${year}</label>`;
            container.appendChild(div);
        }
    }

    function getDropdownSelectedValues(filterName) {
        const dropdown = document.querySelector(`.multiselect-dropdown[data-filter="${filterName}"]`);
        if (!dropdown) return [];
        const checkboxes = dropdown.querySelectorAll('.filter-options input[type="checkbox"]:checked');
        return Array.from(checkboxes).map(cb => cb.value);
    }

    function getCurrentFilterValues() {
        return {
            billingYear: getDropdownSelectedValues('billingYear'),
            billingMonth: getDropdownSelectedValues('billingMonth'),
            status: getDropdownSelectedValues('status'),
            invoiceNumber: document.getElementById('invoiceNumberFilter').value.trim()
        };
    }

    function clearDropdownSelections(filterName) {
        const dropdown = document.querySelector(`.multiselect-dropdown[data-filter="${filterName}"]`);
        if (!dropdown) return;
        dropdown.querySelectorAll('.filter-options input[type="checkbox"]').forEach(cb => cb.checked = false);
        updateDropdownLabel(dropdown);
    }

    function setDropdownSelections(filterName, values) {
        const dropdown = document.querySelector(`.multiselect-dropdown[data-filter="${filterName}"]`);
        if (!dropdown) return;
        dropdown.querySelectorAll('.filter-options input[type="checkbox"]').forEach(cb => {
            cb.checked = values.includes(cb.value);
        });
        updateDropdownLabel(dropdown);
    }

    function updateDropdownLabel(dropdown) {
        const filterName = dropdown.dataset.filter;
        const checkboxes = dropdown.querySelectorAll('.filter-options input[type="checkbox"]:checked');
        const label = dropdown.querySelector('.dropdown-label');
        const count = checkboxes.length;
        
        if (count === 0) {
            label.textContent = defaultLabels[filterName] || 'All';
        } else if (count === 1) {
            const val = checkboxes[0].value;
            if (filterName === 'billingMonth') {
                label.textContent = monthNames[val] || val;
            } else if (filterName === 'status') {
                label.textContent = val.charAt(0).toUpperCase() + val.slice(1);
            } else {
                label.textContent = val;
            }
        } else {
            label.textContent = count + ' selected';
        }
    }

    function setFilterInputs(filters) {
        setDropdownSelections('billingYear', filters.billingYear);
        setDropdownSelections('billingMonth', filters.billingMonth);
        setDropdownSelections('status', filters.status);
        document.getElementById('invoiceNumberFilter').value = filters.invoiceNumber;
    }

    function hasAnyFilters(filters) {
        return filters.billingYear.length > 0 || filters.billingMonth.length > 0 || filters.status.length > 0 || filters.invoiceNumber;
    }

    function arraysMatch(a, b) {
        if (a.length !== b.length) return false;
        const sortedA = [...a].sort();
        const sortedB = [...b].sort();
        return sortedA.every((val, i) => val === sortedB[i]);
    }

    function filtersMatch(a, b) {
        return arraysMatch(a.billingYear, b.billingYear) &&
               arraysMatch(a.billingMonth, b.billingMonth) &&
               arraysMatch(a.status, b.status) &&
               a.invoiceNumber === b.invoiceNumber;
    }

    function updateActiveFilterChips() {
        const container = document.getElementById('activeFiltersContainer');
        const chipsContainer = document.getElementById('activeFilterChips');
        const pendingNotice = document.getElementById('filterPendingNotice');
        
        const currentFilters = getCurrentFilterValues();
        pendingFilters = { ...currentFilters };
        filtersArePending = !filtersMatch(currentFilters, appliedFilters);

        chipsContainer.innerHTML = '';

        const hasApplied = hasAnyFilters(appliedFilters);
        const hasCurrent = hasAnyFilters(currentFilters);

        if (!hasApplied && !hasCurrent) {
            container.style.display = 'none';
            return;
        }

        container.style.display = 'block';

        appliedFilters.billingYear.forEach(year => {
            chipsContainer.appendChild(createFilterChip('Year: ' + year, 'billingYear', year));
        });
        appliedFilters.billingMonth.forEach(month => {
            chipsContainer.appendChild(createFilterChip('Month: ' + monthNames[month], 'billingMonth', month));
        });
        appliedFilters.status.forEach(status => {
            const statusLabel = status.charAt(0).toUpperCase() + status.slice(1);
            chipsContainer.appendChild(createFilterChip('Status: ' + statusLabel, 'status', status));
        });
        if (appliedFilters.invoiceNumber) {
            chipsContainer.appendChild(createFilterChip('Invoice: ' + appliedFilters.invoiceNumber, 'invoiceNumber', null));
        }

        pendingNotice.style.display = filtersArePending ? 'inline' : 'none';
    }

    function createFilterChip(label, filterKey, filterValue) {
        const chip = document.createElement('span');
        chip.className = 'filter-chip';
        chip.innerHTML = `
            ${label}
            <i class="fas fa-times chip-remove" data-filter="${filterKey}" data-value="${filterValue || ''}"></i>
        `;
        chip.querySelector('.chip-remove').addEventListener('click', function(e) {
            e.stopPropagation();
            removeFilter(filterKey, filterValue);
        });
        return chip;
    }

    function removeFilter(filterKey, filterValue) {
        if (filterKey === 'invoiceNumber') {
            document.getElementById('invoiceNumberFilter').value = '';
            appliedFilters.invoiceNumber = '';
            pendingFilters.invoiceNumber = '';
        } else {
            const dropdown = document.querySelector(`.multiselect-dropdown[data-filter="${filterKey}"]`);
            if (dropdown) {
                const checkbox = dropdown.querySelector(`input[type="checkbox"][value="${filterValue}"]`);
                if (checkbox) checkbox.checked = false;
                updateDropdownLabel(dropdown);
            }
            
            appliedFilters[filterKey] = appliedFilters[filterKey].filter(v => v !== filterValue);
            pendingFilters[filterKey] = pendingFilters[filterKey].filter(v => v !== filterValue);
        }
        
        updateActiveFilterChips();
        filterAndRenderInvoices();
    }

    function applyFilters() {
        appliedFilters = getCurrentFilterValues();
        pendingFilters = { ...appliedFilters };
        filtersArePending = false;
        
        updateActiveFilterChips();
        filterAndRenderInvoices();
    }

    function filterAndRenderInvoices() {
        let filtered = [...invoicesData];

        if (appliedFilters.billingYear.length > 0) {
            filtered = filtered.filter(inv => {
                const date = new Date(inv.billingPeriodStart || inv.issueDate);
                return appliedFilters.billingYear.includes(date.getFullYear().toString());
            });
        }

        if (appliedFilters.billingMonth.length > 0) {
            filtered = filtered.filter(inv => {
                const date = new Date(inv.billingPeriodStart || inv.issueDate);
                const month = String(date.getMonth() + 1).padStart(2, '0');
                return appliedFilters.billingMonth.includes(month);
            });
        }

        if (appliedFilters.status.length > 0) {
            filtered = filtered.filter(inv => {
                const invStatus = inv.status.toLowerCase();
                return appliedFilters.status.some(statusVal => {
                    if (statusVal === 'void') {
                        return invStatus === 'void' || invStatus === 'voided' || invStatus === 'cancelled';
                    }
                    return invStatus === statusVal || (statusVal === 'issued' && invStatus === 'pending');
                });
            });
        }

        if (appliedFilters.invoiceNumber) {
            const searchTerm = appliedFilters.invoiceNumber.toLowerCase();
            filtered = filtered.filter(inv => 
                inv.invoiceNumber.toLowerCase().includes(searchTerm)
            );
        }

        renderInvoices(filtered);
    }

    function resetFilters() {
        clearDropdownSelections('billingYear');
        clearDropdownSelections('billingMonth');
        clearDropdownSelections('status');
        document.getElementById('invoiceNumberFilter').value = '';
        appliedFilters = { billingYear: [], billingMonth: [], status: [], invoiceNumber: '' };
        pendingFilters = { billingYear: [], billingMonth: [], status: [], invoiceNumber: '' };
        updateActiveFilterChips();
        filterAndRenderInvoices();
    }

    populateBillingYearOptions();

    document.querySelectorAll('.multiselect-dropdown').forEach(dropdown => {
        dropdown.querySelectorAll('.filter-options input[type="checkbox"]').forEach(cb => {
            cb.addEventListener('change', function() {
                updateDropdownLabel(dropdown);
                updateActiveFilterChips();
            });
        });
        
        const selectAllBtn = dropdown.querySelector('.select-all-btn');
        const clearAllBtn = dropdown.querySelector('.clear-all-btn');
        
        if (selectAllBtn) {
            selectAllBtn.addEventListener('click', function(e) {
                e.preventDefault();
                dropdown.querySelectorAll('.filter-options input[type="checkbox"]').forEach(cb => cb.checked = true);
                updateDropdownLabel(dropdown);
                updateActiveFilterChips();
            });
        }
        
        if (clearAllBtn) {
            clearAllBtn.addEventListener('click', function(e) {
                e.preventDefault();
                dropdown.querySelectorAll('.filter-options input[type="checkbox"]').forEach(cb => cb.checked = false);
                updateDropdownLabel(dropdown);
                updateActiveFilterChips();
            });
        }
    });

    document.getElementById('invoiceNumberFilter').addEventListener('input', updateActiveFilterChips);

    document.getElementById('applyFiltersBtn').addEventListener('click', function() {
        applyFilters();
    });

    document.getElementById('resetFiltersBtn').addEventListener('click', function() {
        resetFilters();
    });

    document.getElementById('exportBtn').addEventListener('click', function() {
        if (invoicesData.length === 0) {
            alert('No invoices to export');
            return;
        }

        const headers = ['Invoice #', 'Issue Date', 'Due Date', 'Billing Period', 'Subtotal', 'VAT', 'Total', 'Balance Due', 'Status'];
        const rows = invoicesData.map(inv => [
            inv.invoiceNumber,
            inv.issueDate || '',
            inv.dueDate || '',
            formatBillingPeriod(inv.billingPeriodStart, inv.billingPeriodEnd),
            inv.subtotal,
            inv.vat,
            inv.total,
            inv.balanceDue,
            inv.status
        ]);

        const csv = [headers.join(','), ...rows.map(r => r.join(','))].join('\n');
        const blob = new Blob([csv], { type: 'text/csv' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'invoices_export_' + new Date().toISOString().split('T')[0] + '.csv';
        a.click();
        URL.revokeObjectURL(url);
    });

    let accountSummaryRefreshInterval = null;
    const REFRESH_INTERVAL_MS = 30000;

    async function loadAccountSummary() {
        const refreshIndicator = document.getElementById('lastRefreshed');
        refreshIndicator.innerHTML = '<i class="fas fa-sync-alt fa-spin me-1"></i> Updating...';
        refreshIndicator.classList.add('refreshing');

        try {
            const response = await fetch('/api/invoices/account-summary');
            const data = await response.json();

            if (!data.success) {
                console.error('Failed to load account summary:', data.error);
                refreshIndicator.innerHTML = '<i class="fas fa-exclamation-circle me-1"></i> Error';
                refreshIndicator.classList.remove('refreshing');
                return;
            }

            updateAccountFinancialSummary(data);

        } catch (error) {
            console.error('Error loading account summary:', error);
            refreshIndicator.innerHTML = '<i class="fas fa-exclamation-circle me-1"></i> Error';
            refreshIndicator.classList.remove('refreshing');
        }
    }

    function updateAccountFinancialSummary(data) {
        const billingModeEl = document.getElementById('billingMode');
        const isPrepaid = data.billingMode === 'prepaid';
        billingModeEl.innerHTML = isPrepaid
            ? '<span class="billing-mode-badge prepaid"><i class="fas fa-wallet me-1"></i> Prepaid</span>'
            : '<span class="billing-mode-badge postpaid"><i class="fas fa-credit-card me-1"></i> Postpaid</span>';

        const currentBalanceEl = document.getElementById('currentBalance');
        currentBalanceEl.textContent = formatCurrency(data.currentBalance, data.currency);
        currentBalanceEl.className = 'metric-value ' + (data.currentBalance >= 0 ? 'text-success' : 'text-danger');

        const creditLimitEl = document.getElementById('creditLimit');
        if (data.creditLimit > 0) {
            creditLimitEl.textContent = formatCurrency(data.creditLimit, data.currency);
            creditLimitEl.closest('.financial-metric').style.display = '';
        } else {
            creditLimitEl.textContent = '-';
        }

        const availableCreditEl = document.getElementById('availableCredit');
        availableCreditEl.textContent = formatCurrency(data.availableCredit, data.currency);
        availableCreditEl.className = 'metric-value ' + (data.availableCredit >= 0 ? 'text-success' : 'text-danger');

        const accountStatusEl = document.getElementById('accountStatus');
        const isCreditHold = data.accountStatus === 'credit_hold';
        accountStatusEl.innerHTML = isCreditHold
            ? '<span class="account-status-badge credit-hold"><i class="fas fa-pause-circle"></i> Credit Hold</span>'
            : '<span class="account-status-badge active"><i class="fas fa-check-circle"></i> Active</span>';

        const refreshIndicator = document.getElementById('lastRefreshed');
        const lastUpdate = data.lastUpdated ? new Date(data.lastUpdated) : new Date();
        const timeAgo = getTimeAgo(lastUpdate);
        refreshIndicator.innerHTML = '<i class="fas fa-sync-alt me-1"></i> ' + timeAgo;
        refreshIndicator.classList.remove('refreshing');

        if (isCreditHold && !document.getElementById('creditHoldWarning')) {
            const financialSummary = document.getElementById('accountFinancialSummary');
            const warning = document.createElement('div');
            warning.id = 'creditHoldWarning';
            warning.className = 'alert alert-danger mt-3 mb-0';
            warning.innerHTML = `
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                    <div>
                        <strong>Account on Credit Hold</strong>
                        <div class="small">Your available credit has been exceeded. Message sending is paused until you top up your balance.</div>
                    </div>
                    <button type="button" class="btn btn-danger btn-sm ms-auto" data-bs-toggle="modal" data-bs-target="#topUpModal">
                        <i class="fas fa-plus-circle me-1"></i> Top Up Now
                    </button>
                </div>
            `;
            financialSummary.appendChild(warning);
        } else if (!isCreditHold) {
            const existingWarning = document.getElementById('creditHoldWarning');
            if (existingWarning) {
                existingWarning.remove();
            }
        }
    }

    function getTimeAgo(date) {
        const seconds = Math.floor((new Date() - date) / 1000);
        if (seconds < 10) return 'Just now';
        if (seconds < 60) return seconds + 's ago';
        const minutes = Math.floor(seconds / 60);
        if (minutes < 60) return minutes + 'm ago';
        const hours = Math.floor(minutes / 60);
        if (hours < 24) return hours + 'h ago';
        return formatDate(date.toISOString());
    }

    function startAccountSummaryRefresh() {
        loadAccountSummary();
        accountSummaryRefreshInterval = setInterval(loadAccountSummary, REFRESH_INTERVAL_MS);
    }

    function stopAccountSummaryRefresh() {
        if (accountSummaryRefreshInterval) {
            clearInterval(accountSummaryRefreshInterval);
            accountSummaryRefreshInterval = null;
        }
    }

    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            stopAccountSummaryRefresh();
        } else {
            startAccountSummaryRefresh();
        }
    });

    loadInvoices();
    startAccountSummaryRefresh();
});
</script>
@endpush
