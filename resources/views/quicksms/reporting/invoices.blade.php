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
#filtersPanel select[multiple] {
    height: 38px;
    min-height: 38px;
    padding: 0.25rem 0.5rem;
}
#filtersPanel .bootstrap-select {
    width: 100% !important;
}
#filtersPanel .bootstrap-select .dropdown-toggle {
    height: 38px;
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
    border-radius: 0.25rem;
    background-color: #fff;
    border-color: #ced4da;
}
#filtersPanel .bootstrap-select .dropdown-toggle .filter-option-inner-inner {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
#filtersPanel .bootstrap-select .dropdown-menu {
    max-height: 250px;
    overflow-y: auto;
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
}
#invoicesTable thead th {
    background-color: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
    padding: 12px 15px;
    font-weight: 600;
    color: #495057;
    white-space: nowrap;
    position: sticky;
    top: 0;
    z-index: 10;
}
#invoicesTable tbody tr {
    cursor: pointer;
    transition: background-color 0.15s ease;
    border-bottom: 1px solid #e9ecef;
}
#invoicesTable tbody tr:hover {
    background-color: rgba(136, 108, 192, 0.08) !important;
}
#invoicesTable tbody td {
    padding: 12px 15px;
    vertical-align: middle;
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
                                <i class="fas fa-wallet me-1"></i> Prepaid
                            </span>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2 financial-metric">
                        <div class="metric-label">Current Balance</div>
                        <div class="metric-value text-success" id="currentBalance">&pound;2,450.00</div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2 financial-metric">
                        <div class="metric-label">Credit Limit</div>
                        <div class="metric-value" id="creditLimit">&pound;5,000.00</div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2 financial-metric">
                        <div class="metric-label">Available Credit</div>
                        <div class="metric-value text-success" id="availableCredit">&pound;7,450.00</div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2 financial-metric">
                        <div class="metric-label">Account Status</div>
                        <div id="accountStatus">
                            <span class="account-status-badge active">
                                <i class="fas fa-check-circle"></i> Active
                            </span>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2 financial-metric">
                        <div class="metric-label">Last Updated</div>
                        <div class="financial-refresh-indicator" id="lastRefreshed">
                            <i class="fas fa-sync-alt me-1"></i> Just now
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 mt-3 mt-lg-0">
                <div class="d-flex flex-column gap-2">
                    @if($canMakePayments)
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#topUpModal">
                        <i class="fas fa-plus-circle me-1"></i> Top Up Balance
                    </button>
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
                                <li><strong>Credit Limit:</strong> Additional credit extended to your account (postpaid accounts only).</li>
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
                                <div class="row g-3 align-items-center">
                                    <div class="col-6 col-md-4 col-lg-2">
                                        <label class="form-label small fw-bold mb-1">Billing Year</label>
                                        <select class="form-select form-select-sm" id="billingYearFilter" multiple data-placeholder="All Years">
                                        </select>
                                    </div>
                                    <div class="col-6 col-md-4 col-lg-2">
                                        <label class="form-label small fw-bold mb-1">Billing Month</label>
                                        <select class="form-select form-select-sm" id="billingMonthFilter" multiple data-placeholder="All Months">
                                            <option value="01">January</option>
                                            <option value="02">February</option>
                                            <option value="03">March</option>
                                            <option value="04">April</option>
                                            <option value="05">May</option>
                                            <option value="06">June</option>
                                            <option value="07">July</option>
                                            <option value="08">August</option>
                                            <option value="09">September</option>
                                            <option value="10">October</option>
                                            <option value="11">November</option>
                                            <option value="12">December</option>
                                        </select>
                                    </div>
                                    <div class="col-6 col-md-4 col-lg-2">
                                        <label class="form-label small fw-bold mb-1">Invoice Status</label>
                                        <select class="form-select form-select-sm" id="statusFilter" multiple data-placeholder="All Statuses">
                                            <option value="draft">Draft</option>
                                            <option value="issued">Issued</option>
                                            <option value="paid">Paid</option>
                                            <option value="overdue">Overdue</option>
                                            <option value="void">Void / Cancelled</option>
                                        </select>
                                    </div>
                                    <div class="col-6 col-md-4 col-lg-3">
                                        <label class="form-label small fw-bold mb-1">Invoice Number</label>
                                        <input type="text" class="form-control form-control-sm" id="invoiceNumberFilter" placeholder="Search invoice number...">
                                    </div>
                                    <div class="col-12 col-md-4 col-lg-3 d-flex gap-2 align-items-center">
                                        <button type="button" class="btn btn-primary btn-sm flex-grow-1" id="applyFiltersBtn">
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

<div class="modal fade" id="topUpModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-2 text-primary"></i>Top Up Balance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="topUpStep1">
                <div class="alert alert-pastel-primary mb-4">
                    <i class="fas fa-info-circle text-primary me-2"></i>
                    Add credits to your account. Choose a tier based on your messaging needs.
                </div>

                <div id="topUpTiersContainer">
                    <div id="topUpTiersLoading" class="text-center py-4">
                        <div class="spinner-border text-primary" role="status"></div>
                        <div class="mt-2 text-muted">Loading pricing...</div>
                    </div>

                    <div id="topUpBespokeTier" class="d-none">
                        <div class="card border-0 rounded-3" style="background: linear-gradient(135deg, var(--primary), #a78bfa);">
                            <div class="card-body text-white text-center py-4">
                                <div class="mb-2">
                                    <span class="badge bg-white text-primary px-3 py-2">
                                        <i class="fas fa-gem me-1"></i> Custom Contract
                                    </span>
                                </div>
                                <h4 class="mb-2">Bespoke Pricing</h4>
                                <p class="mb-3 opacity-75">Your account has a custom contract rate</p>
                                <div class="bg-white bg-opacity-25 rounded p-3 d-inline-block">
                                    <div class="h3 mb-0" id="bespokeRateDisplay">&pound;0.0285<small class="fs-6">/msg</small></div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4">
                            <label class="form-label fw-bold">Top-Up Amount</label>
                            <div class="row g-2">
                                <div class="col-3"><button type="button" class="btn btn-outline-primary w-100 topup-amount-btn" data-amount="250">&pound;250</button></div>
                                <div class="col-3"><button type="button" class="btn btn-outline-primary w-100 topup-amount-btn" data-amount="500">&pound;500</button></div>
                                <div class="col-3"><button type="button" class="btn btn-outline-primary w-100 topup-amount-btn active" data-amount="1000">&pound;1,000</button></div>
                                <div class="col-3"><button type="button" class="btn btn-outline-primary w-100 topup-amount-btn" data-amount="2500">&pound;2,500</button></div>
                            </div>
                            <div class="mt-3">
                                <label class="form-label small">Or enter custom amount</label>
                                <div class="input-group">
                                    <span class="input-group-text">&pound;</span>
                                    <input type="number" class="form-control" id="bespokeCustomAmount" min="100" max="50000" placeholder="Enter amount (min £100)">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="topUpStandardTiers" class="d-none">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="card topup-tier-card h-100" data-tier="starter" style="cursor: pointer;">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h5 class="card-title mb-0">Starter</h5>
                                            <span class="badge bg-secondary" id="starterCurrentBadge" style="display: none;">Current</span>
                                        </div>
                                        <p class="text-muted small mb-3">For smaller messaging needs</p>
                                        <div class="tier-rate-display mb-3">
                                            <span class="h4 text-primary" id="starterRateDisplay">&pound;0.0350</span>
                                            <span class="text-muted">/msg</span>
                                        </div>
                                        <div class="tier-volumes text-muted small">
                                            <div>Up to 50,000 messages</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card topup-tier-card h-100 border-primary" data-tier="enterprise" style="cursor: pointer;">
                                    <div class="position-absolute top-0 start-50 translate-middle">
                                        <span class="badge bg-success px-3 py-2"><i class="fas fa-star me-1"></i>Best Value</span>
                                    </div>
                                    <div class="card-body pt-4">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h5 class="card-title mb-0">Enterprise</h5>
                                            <span class="badge bg-secondary" id="enterpriseCurrentBadge" style="display: none;">Current</span>
                                        </div>
                                        <p class="text-muted small mb-3">For high-volume messaging</p>
                                        <div class="tier-rate-display mb-3">
                                            <span class="h4 text-primary" id="enterpriseRateDisplay">&pound;0.0285</span>
                                            <span class="text-muted">/msg</span>
                                        </div>
                                        <div class="tier-volumes text-muted small">
                                            <div>50,000 - 1,000,000 messages</div>
                                            <div class="text-success"><i class="fas fa-check me-1"></i>18% savings vs Starter</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4" id="standardAmountSelector" style="display: none;">
                            <label class="form-label fw-bold">Top-Up Amount for <span id="selectedTierLabel">Starter</span></label>
                            <div class="row g-2">
                                <div class="col-3"><button type="button" class="btn btn-outline-primary w-100 topup-amount-btn" data-amount="250">&pound;250</button></div>
                                <div class="col-3"><button type="button" class="btn btn-outline-primary w-100 topup-amount-btn" data-amount="500">&pound;500</button></div>
                                <div class="col-3"><button type="button" class="btn btn-outline-primary w-100 topup-amount-btn active" data-amount="1000">&pound;1,000</button></div>
                                <div class="col-3"><button type="button" class="btn btn-outline-primary w-100 topup-amount-btn" data-amount="2500">&pound;2,500</button></div>
                            </div>
                            <div class="mt-3">
                                <label class="form-label small">Or enter custom amount</label>
                                <div class="input-group">
                                    <span class="input-group-text">&pound;</span>
                                    <input type="number" class="form-control" id="standardCustomAmount" min="100" max="50000" placeholder="Enter amount (min £100)">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="topUpError" class="d-none">
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <span id="topUpErrorMessage">Failed to load pricing. Please try again.</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-body d-none" id="topUpStep2">
                <div class="text-center mb-4">
                    <div class="mb-3">
                        <span class="badge bg-primary fs-6 px-3 py-2" id="confirmTierBadge">Enterprise</span>
                    </div>
                    <div class="display-5 fw-bold text-primary mb-1" id="confirmTopUpAmount">&pound;1,000.00</div>
                    <small class="text-muted">Credit Amount</small>
                </div>

                <div class="card mb-3" style="background-color: #f8f9fa; border: none;">
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-4 border-end">
                                <div class="text-muted small">Effective Rate</div>
                                <div class="fw-bold text-primary" id="confirmEffectiveRate">&pound;0.0285/msg</div>
                            </div>
                            <div class="col-4 border-end">
                                <div class="text-muted small">Est. Messages</div>
                                <div class="fw-bold" id="confirmEstMessages">~35,088</div>
                            </div>
                            <div class="col-4">
                                <div class="text-muted small">VAT (20%)</div>
                                <div class="fw-bold" id="confirmVatAmount">&pound;200.00</div>
                            </div>
                        </div>
                        <hr class="my-3">
                        <div class="d-flex justify-content-between fs-5">
                            <span class="fw-bold">Total to Pay</span>
                            <span class="fw-bold text-primary" id="confirmTotalPayable">&pound;1,200.00</span>
                        </div>
                    </div>
                </div>

                <div class="alert alert-pastel-primary mb-0">
                    <i class="fas fa-lock text-primary me-2"></i>
                    <strong>Secure Payment</strong><br>
                    <small>You'll be redirected to Stripe to complete your payment. We never store your card details (PCI DSS compliant).</small>
                </div>
            </div>

            <div class="modal-footer" id="topUpFooter1">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="continueToConfirmBtn" disabled>
                    Continue <i class="fas fa-arrow-right ms-1"></i>
                </button>
            </div>
            <div class="modal-footer d-none" id="topUpFooter2">
                <button type="button" class="btn btn-outline-secondary" id="backToTiersBtn">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </button>
                <button type="button" class="btn btn-primary" id="proceedTopUpBtn">
                    <i class="fas fa-external-link-alt me-1"></i> Pay with Stripe
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.topup-tier-card {
    transition: all 0.2s ease;
    border: 2px solid #e9ecef;
}
.topup-tier-card:hover {
    border-color: var(--primary);
    box-shadow: 0 4px 12px rgba(111, 66, 193, 0.15);
}
.topup-tier-card.selected {
    border-color: var(--primary);
    background-color: rgba(111, 66, 193, 0.05);
}
.topup-amount-btn.active {
    background-color: var(--primary);
    border-color: var(--primary);
    color: white;
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
<script>
document.addEventListener('DOMContentLoaded', function() {
    let invoicesData = [];
    let isLoading = false;
    let isMockData = false;

    const currentUserRole = @json($currentUserRole);
    const canMakePayments = ['admin', 'finance'].includes(currentUserRole);

    const billingDetails = {
        company: 'Acme Corporation Ltd',
        address: '123 Business Park, London, EC1A 1BB',
        vatNumber: 'GB123456789',
        poRef: '-'
    };

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

    function showMockDataNotice() {
        if (!document.getElementById('mockDataNotice')) {
            const container = document.querySelector('.invoices-fixed-header');
            const notice = document.createElement('div');
            notice.id = 'mockDataNotice';
            notice.className = 'alert alert-pastel-primary small mb-3';
            notice.innerHTML = `
                <i class="fas fa-info-circle text-primary me-2"></i>
                <strong>Demo Mode:</strong> Displaying sample invoice data. Connect your HubSpot account to view real invoices.
            `;
            container.insertBefore(notice, container.firstChild);
        }
    }

    async function loadInvoices() {
        showLoading();

        try {
            const params = new URLSearchParams();
            const statusEl = document.getElementById('statusFilter');
            const invoiceNumberEl = document.getElementById('invoiceNumberFilter');
            
            const status = statusEl ? statusEl.value : '';
            const search = invoiceNumberEl ? invoiceNumberEl.value : '';

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
            isMockData = data.isMockData || false;

            if (isMockData) {
                showMockDataNotice();
            }

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
        selectedAmount: 1000,
        effectiveRate: 0.035,
        isBespoke: false,
        isLoading: true,
        currency: 'GBP',
        vatRate: 0.20
    };

    const topUpModal = document.getElementById('topUpModal');
    topUpModal.addEventListener('show.bs.modal', loadTopUpPricing);

    async function loadTopUpPricing() {
        topUpState.isLoading = true;
        document.getElementById('topUpTiersLoading').classList.remove('d-none');
        document.getElementById('topUpBespokeTier').classList.add('d-none');
        document.getElementById('topUpStandardTiers').classList.add('d-none');
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
                showBespokeTier();
            } else {
                showStandardTiers();
            }

            topUpState.isLoading = false;
        } catch (error) {
            console.error('Failed to load pricing:', error);
            document.getElementById('topUpTiersLoading').classList.add('d-none');
            document.getElementById('topUpError').classList.remove('d-none');
            document.getElementById('topUpErrorMessage').textContent = error.message;
        }
    }

    function showBespokeTier() {
        document.getElementById('topUpBespokeTier').classList.remove('d-none');
        
        const smsProduct = topUpState.products['sms'];
        if (smsProduct) {
            const rate = smsProduct.price_enterprise || smsProduct.price;
            topUpState.effectiveRate = rate;
            document.getElementById('bespokeRateDisplay').innerHTML = 
                formatCurrency(rate, 4) + '<small class="fs-6">/msg</small>';
        }
        
        topUpState.selectedTier = 'bespoke';
        topUpState.selectedAmount = 1000;
        updateContinueButton();
    }

    function showStandardTiers() {
        document.getElementById('topUpStandardTiers').classList.remove('d-none');
        
        const smsProduct = topUpState.products['sms'];
        if (smsProduct) {
            const starterRate = smsProduct.price;
            const enterpriseRate = smsProduct.price_enterprise || smsProduct.price;
            
            document.getElementById('starterRateDisplay').textContent = formatCurrency(starterRate, 4);
            document.getElementById('enterpriseRateDisplay').textContent = formatCurrency(enterpriseRate, 4);
        }

        topUpState.selectedTier = null;
        topUpState.selectedAmount = 1000;
        updateContinueButton();
    }

    function formatCurrency(amount, decimals = 2) {
        const symbols = { 'GBP': '£', 'EUR': '€', 'USD': '$' };
        const symbol = symbols[topUpState.currency] || '£';
        return symbol + parseFloat(amount || 0).toFixed(decimals);
    }

    document.querySelectorAll('.topup-tier-card').forEach(card => {
        card.addEventListener('click', function() {
            document.querySelectorAll('.topup-tier-card').forEach(c => c.classList.remove('selected'));
            this.classList.add('selected');
            
            topUpState.selectedTier = this.dataset.tier;
            document.getElementById('selectedTierLabel').textContent = 
                topUpState.selectedTier === 'starter' ? 'Starter' : 'Enterprise';
            document.getElementById('standardAmountSelector').style.display = 'block';
            
            const smsProduct = topUpState.products['sms'];
            if (smsProduct) {
                topUpState.effectiveRate = topUpState.selectedTier === 'enterprise' 
                    ? (smsProduct.price_enterprise || smsProduct.price)
                    : smsProduct.price;
            }
            
            updateContinueButton();
        });
    });

    document.querySelectorAll('.topup-amount-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const parent = this.closest('#topUpBespokeTier, #standardAmountSelector');
            if (parent) {
                parent.querySelectorAll('.topup-amount-btn').forEach(b => b.classList.remove('active'));
            }
            this.classList.add('active');
            
            topUpState.selectedAmount = parseFloat(this.dataset.amount);
            updateContinueButton();
        });
    });

    document.getElementById('bespokeCustomAmount')?.addEventListener('input', function() {
        const amount = parseFloat(this.value) || 0;
        if (amount >= 100) {
            topUpState.selectedAmount = amount;
            document.querySelectorAll('#topUpBespokeTier .topup-amount-btn').forEach(b => b.classList.remove('active'));
        }
        updateContinueButton();
    });

    document.getElementById('standardCustomAmount')?.addEventListener('input', function() {
        const amount = parseFloat(this.value) || 0;
        if (amount >= 100) {
            topUpState.selectedAmount = amount;
            document.querySelectorAll('#standardAmountSelector .topup-amount-btn').forEach(b => b.classList.remove('active'));
        }
        updateContinueButton();
    });

    function updateContinueButton() {
        const canContinue = topUpState.selectedTier && topUpState.selectedAmount >= 100;
        document.getElementById('continueToConfirmBtn').disabled = !canContinue;
    }

    document.getElementById('continueToConfirmBtn').addEventListener('click', function() {
        const tierName = topUpState.selectedTier === 'bespoke' ? 'Custom Contract' :
                         topUpState.selectedTier === 'enterprise' ? 'Enterprise' : 'Starter';
        
        const creditAmount = topUpState.selectedAmount;
        const vatAmount = creditAmount * topUpState.vatRate;
        const totalPayable = creditAmount + vatAmount;
        const estMessages = Math.floor(creditAmount / topUpState.effectiveRate);

        document.getElementById('confirmTierBadge').textContent = tierName;
        document.getElementById('confirmTopUpAmount').textContent = formatCurrency(creditAmount);
        document.getElementById('confirmEffectiveRate').textContent = formatCurrency(topUpState.effectiveRate, 4) + '/msg';
        document.getElementById('confirmEstMessages').textContent = '~' + estMessages.toLocaleString();
        document.getElementById('confirmVatAmount').textContent = formatCurrency(vatAmount);
        document.getElementById('confirmTotalPayable').textContent = formatCurrency(totalPayable);

        document.getElementById('topUpStep1').classList.add('d-none');
        document.getElementById('topUpStep2').classList.remove('d-none');
        document.getElementById('topUpFooter1').classList.add('d-none');
        document.getElementById('topUpFooter2').classList.remove('d-none');
    });

    document.getElementById('backToTiersBtn').addEventListener('click', function() {
        document.getElementById('topUpStep2').classList.add('d-none');
        document.getElementById('topUpStep1').classList.remove('d-none');
        document.getElementById('topUpFooter2').classList.add('d-none');
        document.getElementById('topUpFooter1').classList.remove('d-none');
    });

    document.getElementById('proceedTopUpBtn').addEventListener('click', async function() {
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Processing...';

        try {
            const response = await fetch('/api/topup/create-checkout-session', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    tier: topUpState.selectedTier,
                    amount: topUpState.selectedAmount,
                    currency: topUpState.currency
                })
            });

            const data = await response.json();

            if (data.success && data.checkoutUrl) {
                window.location.href = data.checkoutUrl;
            } else {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-external-link-alt me-1"></i> Pay with Stripe';
                alert(data.error || 'Failed to create payment session. Please try again.');
            }
        } catch (error) {
            console.error('Top-up error:', error);
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-external-link-alt me-1"></i> Pay with Stripe';
            alert('An error occurred. Please try again.');
        }
    });

    topUpModal.addEventListener('hidden.bs.modal', function() {
        document.getElementById('topUpStep2').classList.add('d-none');
        document.getElementById('topUpStep1').classList.remove('d-none');
        document.getElementById('topUpFooter2').classList.add('d-none');
        document.getElementById('topUpFooter1').classList.remove('d-none');
        document.getElementById('standardAmountSelector').style.display = 'none';
        document.querySelectorAll('.topup-tier-card').forEach(c => c.classList.remove('selected'));
        topUpState.selectedTier = null;
        topUpState.selectedAmount = 1000;
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

    function populateBillingYearOptions() {
        const yearSelect = document.getElementById('billingYearFilter');
        const currentYear = new Date().getFullYear();
        for (let year = currentYear; year >= 2015; year--) {
            const option = document.createElement('option');
            option.value = year;
            option.textContent = year;
            yearSelect.appendChild(option);
        }
        $(yearSelect).selectpicker('refresh');
    }

    function getMultiSelectValues(selectId) {
        const select = document.getElementById(selectId);
        return Array.from(select.selectedOptions).map(opt => opt.value);
    }

    function getCurrentFilterValues() {
        return {
            billingYear: getMultiSelectValues('billingYearFilter'),
            billingMonth: getMultiSelectValues('billingMonthFilter'),
            status: getMultiSelectValues('statusFilter'),
            invoiceNumber: document.getElementById('invoiceNumberFilter').value.trim()
        };
    }

    function setFilterInputs(filters) {
        $('#billingYearFilter').selectpicker('val', filters.billingYear);
        $('#billingMonthFilter').selectpicker('val', filters.billingMonth);
        $('#statusFilter').selectpicker('val', filters.status);
        document.getElementById('invoiceNumberFilter').value = filters.invoiceNumber;
    }

    function hasAnyFilters(filters) {
        return filters.billingYear.length > 0 || filters.billingMonth.length > 0 || filters.status.length > 0 || filters.invoiceNumber;
    }

    function arraysMatch(a, b) {
        if (a.length !== b.length) return false;
        return a.every((val, i) => val === b[i]);
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
            const selectId = filterKey === 'billingYear' ? 'billingYearFilter' :
                            filterKey === 'billingMonth' ? 'billingMonthFilter' : 'statusFilter';
            const select = document.getElementById(selectId);
            const option = select.querySelector(`option[value="${filterValue}"]`);
            if (option) option.selected = false;
            $(select).selectpicker('refresh');
            
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
        setFilterInputs({ billingYear: [], billingMonth: [], status: [], invoiceNumber: '' });
        appliedFilters = { billingYear: [], billingMonth: [], status: [], invoiceNumber: '' };
        pendingFilters = { billingYear: [], billingMonth: [], status: [], invoiceNumber: '' };
        updateActiveFilterChips();
        filterAndRenderInvoices();
    }

    $('#billingYearFilter').selectpicker({
        noneSelectedText: 'All Years',
        selectedTextFormat: 'count > 2',
        countSelectedText: '{0} years',
        actionsBox: true,
        liveSearch: true,
        liveSearchPlaceholder: 'Search years...',
        style: '',
        styleBase: 'form-control form-control-sm'
    });
    $('#billingMonthFilter').selectpicker({
        noneSelectedText: 'All Months',
        selectedTextFormat: 'count > 2',
        countSelectedText: '{0} months',
        actionsBox: true,
        style: '',
        styleBase: 'form-control form-control-sm'
    });
    $('#statusFilter').selectpicker({
        noneSelectedText: 'All Statuses',
        selectedTextFormat: 'count > 2',
        countSelectedText: '{0} statuses',
        actionsBox: true,
        style: '',
        styleBase: 'form-control form-control-sm'
    });

    populateBillingYearOptions();

    ['billingYearFilter', 'billingMonthFilter', 'statusFilter'].forEach(id => {
        $('#' + id).on('changed.bs.select', updateActiveFilterChips);
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
    updateTopUpSummary(500);
});
</script>
@endpush
