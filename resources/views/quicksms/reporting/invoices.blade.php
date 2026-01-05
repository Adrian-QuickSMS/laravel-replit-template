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
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#topUpModal">
                        <i class="fas fa-plus-circle me-1"></i> Top Up Balance
                    </button>
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

    <div id="invoiceSummary" class="row mb-3" style="flex-shrink: 0;">
        <div class="col-12">
            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <div class="summary-stat-card">
                        <div class="stat-value" id="totalInvoices">-</div>
                        <div class="stat-label">Total Invoices</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="summary-stat-card">
                        <div class="stat-value text-success" id="paidAmount">-</div>
                        <div class="stat-label">Paid (YTD)</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="summary-stat-card">
                        <div class="stat-value text-warning" id="pendingAmount">-</div>
                        <div class="stat-label">Pending</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="summary-stat-card">
                        <div class="stat-value text-danger" id="overdueAmount">-</div>
                        <div class="stat-label">Overdue</div>
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
                                        <label class="form-label small fw-bold">Date Range</label>
                                        <select class="form-select form-select-sm" id="dateRangeFilter">
                                            <option value="">All Time</option>
                                            <option value="30">Last 30 Days</option>
                                            <option value="90">Last 90 Days</option>
                                            <option value="180">Last 6 Months</option>
                                            <option value="365" selected>Last 12 Months</option>
                                            <option value="custom">Custom Range</option>
                                        </select>
                                    </div>
                                    <div class="col-6 col-md-4 col-lg-2">
                                        <label class="form-label small fw-bold">Status</label>
                                        <select class="form-select form-select-sm" id="statusFilter">
                                            <option value="">All Statuses</option>
                                            <option value="paid">Paid</option>
                                            <option value="pending">Pending</option>
                                            <option value="overdue">Overdue</option>
                                            <option value="draft">Draft</option>
                                            <option value="cancelled">Cancelled</option>
                                        </select>
                                    </div>
                                    <div class="col-6 col-md-4 col-lg-2">
                                        <label class="form-label small fw-bold">Invoice Type</label>
                                        <select class="form-select form-select-sm" id="typeFilter">
                                            <option value="">All Types</option>
                                            <option value="purchase">Credit Purchase</option>
                                            <option value="subscription">Subscription</option>
                                            <option value="addon">Add-on Service</option>
                                            <option value="overage">Overage</option>
                                        </select>
                                    </div>
                                    <div class="col-6 col-md-4 col-lg-2">
                                        <label class="form-label small fw-bold">Amount Range</label>
                                        <select class="form-select form-select-sm" id="amountFilter">
                                            <option value="">Any Amount</option>
                                            <option value="0-100">Under &pound;100</option>
                                            <option value="100-500">&pound;100 - &pound;500</option>
                                            <option value="500-1000">&pound;500 - &pound;1,000</option>
                                            <option value="1000+">&pound;1,000+</option>
                                        </select>
                                    </div>
                                    <div class="col-6 col-md-4 col-lg-2">
                                        <label class="form-label small fw-bold">Search</label>
                                        <input type="text" class="form-control form-control-sm" id="searchFilter" placeholder="Invoice # or reference...">
                                    </div>
                                    <div class="col-6 col-md-4 col-lg-2 d-flex gap-2">
                                        <button type="button" class="btn btn-primary btn-sm flex-grow-1" id="applyFiltersBtn">
                                            <i class="fas fa-check me-1"></i> Apply
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" id="clearFiltersBtn">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="activeFilters" class="mb-2" style="display: none;">
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
                                        <th>Invoice #</th>
                                        <th>Issue Date</th>
                                        <th>Due Date</th>
                                        <th>Billing Period</th>
                                        <th class="text-end">Total</th>
                                        <th class="text-end">Balance Due</th>
                                        <th>Status</th>
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
        <div>
            <h5 class="mb-0" id="drawerInvoiceNumber">Invoice #INV-2025-0012</h5>
            <small class="text-muted" id="drawerInvoiceDate">Issued: 02 Jan 2025</small>
        </div>
        <button type="button" class="btn-close" id="closeDrawerBtn"></button>
    </div>
    <div class="invoice-drawer-body">
        <div class="alert alert-pastel-primary mb-3">
            <i class="fas fa-info-circle text-primary me-2"></i>
            Invoice data is synchronized from HubSpot. For billing queries, contact finance@quicksms.com.
        </div>

        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small">Status</span>
                <span class="status-badge status-paid" id="drawerStatus">Paid</span>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small">Due Date</span>
                <span id="drawerDueDate">16 Jan 2025</span>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small">Payment Date</span>
                <span id="drawerPaymentDate">05 Jan 2025</span>
            </div>
            <div class="d-flex justify-content-between align-items-center">
                <span class="text-muted small">Payment Method</span>
                <span id="drawerPaymentMethod">Visa ****4242</span>
            </div>
        </div>

        <hr>

        <h6 class="mb-3">Line Items</h6>
        <div id="drawerLineItems">
            <div class="invoice-line-item">
                <div>
                    <div class="fw-medium">SMS Credits - Enterprise Tier</div>
                    <div class="small text-muted">100,000 SMS @ &pound;0.028/msg</div>
                </div>
                <div class="text-end">
                    <div class="fw-medium">&pound;2,800.00</div>
                </div>
            </div>
        </div>

        <hr>

        <div id="drawerTotals">
            <div class="invoice-total-row">
                <span>Subtotal</span>
                <span id="drawerSubtotal">&pound;2,800.00</span>
            </div>
            <div class="invoice-total-row">
                <span>VAT (20%)</span>
                <span id="drawerVat">&pound;560.00</span>
            </div>
            <div class="invoice-total-row grand-total">
                <span>Total</span>
                <span id="drawerTotal">&pound;3,360.00</span>
            </div>
        </div>

        <hr>

        <h6 class="mb-3">Billing Details</h6>
        <div class="small">
            <div class="mb-1"><strong>Company:</strong> <span id="drawerCompany">Acme Corporation Ltd</span></div>
            <div class="mb-1"><strong>Address:</strong> <span id="drawerAddress">123 Business Park, London, EC1A 1BB</span></div>
            <div class="mb-1"><strong>VAT Number:</strong> <span id="drawerVatNumber">GB123456789</span></div>
            <div><strong>PO Reference:</strong> <span id="drawerPoRef">PO-2025-0042</span></div>
        </div>
    </div>
    <div class="invoice-drawer-footer">
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-primary flex-grow-1" id="downloadPdfBtn">
                <i class="fas fa-file-pdf me-1"></i> Download PDF
            </button>
            <button type="button" class="btn btn-primary flex-grow-1" id="payNowBtn" style="display: none;">
                <i class="fas fa-credit-card me-1"></i> Pay Now
            </button>
        </div>
    </div>
</div>

<div class="modal fade" id="topUpModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-2 text-primary"></i>Top Up Balance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-pastel-primary mb-4">
                    <i class="fas fa-info-circle text-primary me-2"></i>
                    Add credits to your account. You'll be redirected to our secure payment partner to complete the transaction.
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Select Amount</label>
                    <div class="row g-2 mb-3">
                        <div class="col-4">
                            <button type="button" class="btn btn-outline-primary w-100 amount-preset" data-amount="100">&pound;100</button>
                        </div>
                        <div class="col-4">
                            <button type="button" class="btn btn-outline-primary w-100 amount-preset" data-amount="250">&pound;250</button>
                        </div>
                        <div class="col-4">
                            <button type="button" class="btn btn-outline-primary w-100 amount-preset" data-amount="500">&pound;500</button>
                        </div>
                        <div class="col-4">
                            <button type="button" class="btn btn-outline-primary w-100 amount-preset" data-amount="1000">&pound;1,000</button>
                        </div>
                        <div class="col-4">
                            <button type="button" class="btn btn-outline-primary w-100 amount-preset" data-amount="2500">&pound;2,500</button>
                        </div>
                        <div class="col-4">
                            <button type="button" class="btn btn-outline-primary w-100 amount-preset active" data-amount="custom">Custom</button>
                        </div>
                    </div>
                    <div id="customAmountWrapper">
                        <label class="form-label small">Enter Amount (&pound;)</label>
                        <input type="number" class="form-control" id="customAmount" min="50" max="50000" placeholder="Enter amount (min &pound;50)">
                    </div>
                </div>

                <div class="bg-light rounded p-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Credit Amount</span>
                        <span id="topUpAmount">&pound;500.00</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>VAT (20%)</span>
                        <span id="topUpVat">&pound;100.00</span>
                    </div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between fw-bold">
                        <span>Total to Pay</span>
                        <span id="topUpTotal">&pound;600.00</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="proceedTopUpBtn">
                    <i class="fas fa-lock me-1"></i> Proceed to Payment
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-credit-card me-2 text-primary"></i>Pay Invoice</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <h6 id="paymentInvoiceNumber">Invoice #INV-2025-0008</h6>
                    <div class="display-6 fw-bold text-primary" id="paymentAmount">&pound;1,250.00</div>
                    <small class="text-muted">Amount due</small>
                </div>

                <div class="alert alert-pastel-primary">
                    <i class="fas fa-lock text-primary me-2"></i>
                    You'll be securely redirected to Stripe to complete your payment. We never store your card details.
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
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let invoicesData = [];
    let isLoading = false;
    let isMockData = false;

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
            'paid': '<span class="status-badge status-paid">Paid</span>',
            'pending': '<span class="status-badge status-pending">Pending</span>',
            'overdue': '<span class="status-badge status-overdue">Overdue</span>',
            'draft': '<span class="status-badge status-draft">Draft</span>',
            'cancelled': '<span class="status-badge status-cancelled">Cancelled</span>'
        };
        return statusMap[status] || '<span class="status-badge">' + status + '</span>';
    }

    function showLoading() {
        isLoading = true;
        const tbody = document.getElementById('invoicesTableBody');
        tbody.innerHTML = `
            <tr>
                <td colspan="9" class="text-center py-5">
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
                <td colspan="9" class="text-center py-5">
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
        document.getElementById('totalInvoices').textContent = '-';
        document.getElementById('paidAmount').textContent = '-';
        document.getElementById('pendingAmount').textContent = '-';
        document.getElementById('overdueAmount').textContent = '-';
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

    function updateSummary(summary) {
        document.getElementById('totalInvoices').textContent = summary.totalInvoices || 0;
        document.getElementById('paidAmount').innerHTML = formatCurrency(summary.paidAmount || 0);
        document.getElementById('pendingAmount').innerHTML = formatCurrency(summary.pendingAmount || 0);
        document.getElementById('overdueAmount').innerHTML = formatCurrency(summary.overdueAmount || 0);
    }

    async function loadInvoices() {
        showLoading();

        try {
            const params = new URLSearchParams();
            const status = document.getElementById('statusFilter').value;
            const dateRange = document.getElementById('dateRangeFilter').value;
            const search = document.getElementById('searchFilter').value;

            if (status) params.append('status', status);
            if (dateRange) params.append('dateRange', dateRange);
            if (search) params.append('search', search);

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

            updateSummary(data.summary || {});
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
                    <td colspan="9" class="text-center py-5 text-muted">
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

            const billingPeriod = formatBillingPeriod(inv.billingPeriodStart, inv.billingPeriodEnd);

            row.innerHTML = `
                <td onclick="event.stopPropagation();">
                    <input type="checkbox" class="form-check-input invoice-checkbox" value="${inv.id}">
                </td>
                <td><strong>${inv.invoiceNumber}</strong></td>
                <td>${formatDate(inv.issueDate)}</td>
                <td>${formatDate(inv.dueDate)}</td>
                <td><span class="small text-muted">${billingPeriod}</span></td>
                <td class="text-end fw-medium">${formatCurrency(inv.total, inv.currency)}</td>
                <td class="text-end">${formatCurrency(inv.balanceDue, inv.currency)}</td>
                <td>${getStatusBadge(inv.status)}</td>
                <td class="text-end" onclick="event.stopPropagation();">
                    <div class="dropdown">
                        <button class="btn btn-sm btn-link text-muted p-0" data-bs-toggle="dropdown">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#" onclick="openDrawer('${inv.id}'); return false;"><i class="fas fa-eye me-2"></i>View Details</a></li>
                            <li><a class="dropdown-item" href="#" onclick="downloadPdf('${inv.id}'); return false;"><i class="fas fa-file-pdf me-2"></i>Download PDF</a></li>
                            ${inv.status === 'pending' || inv.status === 'overdue' ? 
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

    function renderDrawerContent(invoice) {
        document.getElementById('drawerInvoiceNumber').textContent = 'Invoice #' + invoice.invoiceNumber;
        document.getElementById('drawerInvoiceDate').textContent = 'Issued: ' + formatDate(invoice.issueDate);

        const billingPeriod = formatBillingPeriod(invoice.billingPeriodStart, invoice.billingPeriodEnd);

        const drawerBody = document.querySelector('.invoice-drawer-body');
        drawerBody.innerHTML = `
            <div class="alert alert-pastel-primary mb-3">
                <i class="fas fa-info-circle text-primary me-2"></i>
                Invoice data is synchronized from HubSpot. Values shown are read-only.
            </div>

            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small">Status</span>
                    ${getStatusBadge(invoice.status)}
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small">Billing Period</span>
                    <span>${billingPeriod}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small">Due Date</span>
                    <span>${formatDate(invoice.dueDate)}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small">Payment Date</span>
                    <span>${invoice.paymentDate ? formatDate(invoice.paymentDate) : '-'}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted small">Balance Outstanding</span>
                    <span class="${invoice.balanceDue > 0 ? 'text-danger fw-bold' : ''}">${formatCurrency(invoice.balanceDue, invoice.currency)}</span>
                </div>
            </div>

            <hr>

            <h6 class="mb-3">Line Items</h6>
            <div id="drawerLineItems">
                ${(invoice.lineItems || []).map(item => `
                    <div class="invoice-line-item">
                        <div>
                            <div class="fw-medium">${item.name}</div>
                            <div class="small text-muted">${item.description || (item.quantity > 1 ? item.quantity + ' x ' + formatCurrency(item.unitPrice, invoice.currency) : '')}</div>
                        </div>
                        <div class="text-end">
                            <div class="fw-medium">${formatCurrency(item.amount, invoice.currency)}</div>
                        </div>
                    </div>
                `).join('') || '<div class="text-muted small">No line items available</div>'}
            </div>

            <hr>

            <div id="drawerTotals">
                <div class="invoice-total-row">
                    <span>Subtotal</span>
                    <span>${formatCurrency(invoice.subtotal, invoice.currency)}</span>
                </div>
                <div class="invoice-total-row">
                    <span>VAT</span>
                    <span>${formatCurrency(invoice.vat, invoice.currency)}</span>
                </div>
                <div class="invoice-total-row grand-total">
                    <span>Total</span>
                    <span>${formatCurrency(invoice.total, invoice.currency)}</span>
                </div>
            </div>
        `;

        const payNowBtn = document.getElementById('payNowBtn');
        if (invoice.status === 'pending' || invoice.status === 'overdue') {
            payNowBtn.style.display = 'block';
            payNowBtn.onclick = () => payInvoice(invoice.id);
        } else {
            payNowBtn.style.display = 'none';
        }

        document.getElementById('downloadPdfBtn').onclick = () => downloadPdf(invoice.id, invoice.pdfUrl);
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

    window.payInvoice = function(invoiceId) {
        const invoice = invoicesData.find(i => i.id === invoiceId);
        if (!invoice) return;

        document.getElementById('paymentInvoiceNumber').textContent = 'Invoice #' + invoice.invoiceNumber;
        document.getElementById('paymentAmount').textContent = formatCurrency(invoice.balanceDue, invoice.currency);

        const modal = new bootstrap.Modal(document.getElementById('paymentModal'));
        modal.show();
        closeDrawer();
    };

    document.getElementById('confirmPaymentBtn').addEventListener('click', function() {
        alert('Payment processing via Stripe will be implemented with HubSpot payment links integration.');
    });

    const amountPresets = document.querySelectorAll('.amount-preset');
    const customAmountWrapper = document.getElementById('customAmountWrapper');
    const customAmountInput = document.getElementById('customAmount');

    function updateTopUpSummary(amount) {
        const vat = amount * 0.20;
        const total = amount + vat;
        document.getElementById('topUpAmount').textContent = formatCurrency(amount);
        document.getElementById('topUpVat').textContent = formatCurrency(vat);
        document.getElementById('topUpTotal').textContent = formatCurrency(total);
    }

    amountPresets.forEach(btn => {
        btn.addEventListener('click', function() {
            amountPresets.forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            const amount = this.dataset.amount;
            if (amount === 'custom') {
                customAmountWrapper.style.display = 'block';
                customAmountInput.focus();
            } else {
                customAmountWrapper.style.display = 'none';
                updateTopUpSummary(parseFloat(amount));
            }
        });
    });

    customAmountInput.addEventListener('input', function() {
        const amount = parseFloat(this.value) || 0;
        updateTopUpSummary(amount);
    });

    document.getElementById('proceedTopUpBtn').addEventListener('click', function() {
        alert('Top-up processing via Stripe will be implemented with HubSpot invoice creation.');
    });

    document.getElementById('selectAll').addEventListener('change', function() {
        document.querySelectorAll('.invoice-checkbox').forEach(cb => {
            cb.checked = this.checked;
        });
    });

    document.getElementById('applyFiltersBtn').addEventListener('click', function() {
        loadInvoices();
    });

    document.getElementById('clearFiltersBtn').addEventListener('click', function() {
        document.getElementById('dateRangeFilter').value = '365';
        document.getElementById('statusFilter').value = '';
        document.getElementById('typeFilter').value = '';
        document.getElementById('amountFilter').value = '';
        document.getElementById('searchFilter').value = '';
        loadInvoices();
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
