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
@media (max-width: 768px) {
    .invoice-drawer {
        width: 100%;
        right: -100%;
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

    <div id="accountSummary" class="row mb-3" style="flex-shrink: 0;">
        <div class="col-12">
            <div class="card mb-0">
                <div class="card-body py-3">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="row g-3">
                                <div class="col-6 col-md-3">
                                    <div class="summary-stat-card">
                                        <div class="stat-value" id="totalInvoices">12</div>
                                        <div class="stat-label">Total Invoices</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="summary-stat-card">
                                        <div class="stat-value text-success" id="paidAmount">&pound;8,450.00</div>
                                        <div class="stat-label">Paid (YTD)</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="summary-stat-card">
                                        <div class="stat-value text-warning" id="pendingAmount">&pound;1,250.00</div>
                                        <div class="stat-label">Pending</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="summary-stat-card">
                                        <div class="stat-value text-danger" id="overdueAmount">&pound;0.00</div>
                                        <div class="stat-label">Overdue</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-md-end mt-3 mt-md-0">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#topUpModal">
                                <i class="fas fa-plus-circle me-1"></i> Top Up Balance
                            </button>
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
                                        <th>Date</th>
                                        <th>Due Date</th>
                                        <th>Type</th>
                                        <th>Description</th>
                                        <th class="text-end">Amount</th>
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
    const mockInvoices = [
        {
            id: 'INV-2025-0012',
            date: '2025-01-02',
            dueDate: '2025-01-16',
            type: 'purchase',
            description: 'SMS Credits - Enterprise Tier (100,000 SMS)',
            amount: 3360.00,
            netAmount: 2800.00,
            vat: 560.00,
            status: 'paid',
            paymentDate: '2025-01-05',
            paymentMethod: 'Visa ****4242',
            lineItems: [
                { name: 'SMS Credits - Enterprise Tier', detail: '100,000 SMS @ £0.028/msg', amount: 2800.00 }
            ]
        },
        {
            id: 'INV-2025-0011',
            date: '2024-12-15',
            dueDate: '2024-12-29',
            type: 'subscription',
            description: 'Monthly Platform Subscription - Pro',
            amount: 119.99,
            netAmount: 99.99,
            vat: 20.00,
            status: 'paid',
            paymentDate: '2024-12-15',
            paymentMethod: 'Direct Debit',
            lineItems: [
                { name: 'Pro Platform Subscription', detail: 'December 2024', amount: 99.99 }
            ]
        },
        {
            id: 'INV-2025-0010',
            date: '2024-12-01',
            dueDate: '2024-12-15',
            type: 'purchase',
            description: 'SMS Credits - Starter Tier (25,000 SMS)',
            amount: 900.00,
            netAmount: 750.00,
            vat: 150.00,
            status: 'paid',
            paymentDate: '2024-12-03',
            paymentMethod: 'Visa ****4242',
            lineItems: [
                { name: 'SMS Credits - Starter Tier', detail: '25,000 SMS @ £0.030/msg', amount: 750.00 }
            ]
        },
        {
            id: 'INV-2025-0009',
            date: '2024-11-20',
            dueDate: '2024-12-04',
            type: 'addon',
            description: 'VMN Rental - 12 Month Term',
            amount: 144.00,
            netAmount: 120.00,
            vat: 24.00,
            status: 'paid',
            paymentDate: '2024-11-22',
            paymentMethod: 'Mastercard ****8523',
            lineItems: [
                { name: 'Virtual Mobile Number', detail: '+44 7700 900123 (12 months)', amount: 120.00 }
            ]
        },
        {
            id: 'INV-2025-0008',
            date: '2024-11-15',
            dueDate: '2025-01-15',
            type: 'purchase',
            description: 'SMS Credits - Enterprise Tier (50,000 SMS)',
            amount: 1500.00,
            netAmount: 1250.00,
            vat: 250.00,
            status: 'pending',
            paymentDate: null,
            paymentMethod: null,
            lineItems: [
                { name: 'SMS Credits - Enterprise Tier', detail: '50,000 SMS @ £0.025/msg', amount: 1250.00 }
            ]
        },
        {
            id: 'INV-2025-0007',
            date: '2024-11-01',
            dueDate: '2024-11-15',
            type: 'subscription',
            description: 'Monthly Platform Subscription - Pro',
            amount: 119.99,
            netAmount: 99.99,
            vat: 20.00,
            status: 'paid',
            paymentDate: '2024-11-01',
            paymentMethod: 'Direct Debit',
            lineItems: [
                { name: 'Pro Platform Subscription', detail: 'November 2024', amount: 99.99 }
            ]
        },
        {
            id: 'INV-2025-0006',
            date: '2024-10-15',
            dueDate: '2024-10-29',
            type: 'purchase',
            description: 'SMS Credits - Starter Tier (10,000 SMS)',
            amount: 360.00,
            netAmount: 300.00,
            vat: 60.00,
            status: 'paid',
            paymentDate: '2024-10-18',
            paymentMethod: 'Visa ****4242',
            lineItems: [
                { name: 'SMS Credits - Starter Tier', detail: '10,000 SMS @ £0.030/msg', amount: 300.00 }
            ]
        },
        {
            id: 'INV-2025-0005',
            date: '2024-10-01',
            dueDate: '2024-10-15',
            type: 'subscription',
            description: 'Monthly Platform Subscription - Pro',
            amount: 119.99,
            netAmount: 99.99,
            vat: 20.00,
            status: 'paid',
            paymentDate: '2024-10-01',
            paymentMethod: 'Direct Debit',
            lineItems: [
                { name: 'Pro Platform Subscription', detail: 'October 2024', amount: 99.99 }
            ]
        },
        {
            id: 'INV-2025-0004',
            date: '2024-09-20',
            dueDate: '2024-10-04',
            type: 'overage',
            description: 'Overage Charges - September 2024',
            amount: 45.00,
            netAmount: 37.50,
            vat: 7.50,
            status: 'paid',
            paymentDate: '2024-09-25',
            paymentMethod: 'Visa ****4242',
            lineItems: [
                { name: 'SMS Overage', detail: '1,250 SMS @ £0.030/msg', amount: 37.50 }
            ]
        },
        {
            id: 'INV-2025-0003',
            date: '2024-09-01',
            dueDate: '2024-09-15',
            type: 'subscription',
            description: 'Monthly Platform Subscription - Pro',
            amount: 119.99,
            netAmount: 99.99,
            vat: 20.00,
            status: 'paid',
            paymentDate: '2024-09-01',
            paymentMethod: 'Direct Debit',
            lineItems: [
                { name: 'Pro Platform Subscription', detail: 'September 2024', amount: 99.99 }
            ]
        },
        {
            id: 'INV-2025-0002',
            date: '2024-08-15',
            dueDate: '2024-08-29',
            type: 'purchase',
            description: 'SMS Credits - Enterprise Tier (200,000 SMS)',
            amount: 5400.00,
            netAmount: 4500.00,
            vat: 900.00,
            status: 'paid',
            paymentDate: '2024-08-18',
            paymentMethod: 'Bank Transfer',
            lineItems: [
                { name: 'SMS Credits - Enterprise Tier', detail: '200,000 SMS @ £0.0225/msg', amount: 4500.00 }
            ]
        },
        {
            id: 'INV-2025-0001',
            date: '2024-08-01',
            dueDate: '2024-08-15',
            type: 'subscription',
            description: 'Monthly Platform Subscription - Pro',
            amount: 119.99,
            netAmount: 99.99,
            vat: 20.00,
            status: 'paid',
            paymentDate: '2024-08-01',
            paymentMethod: 'Direct Debit',
            lineItems: [
                { name: 'Pro Platform Subscription', detail: 'August 2024', amount: 99.99 }
            ]
        }
    ];

    const billingDetails = {
        company: 'Acme Corporation Ltd',
        address: '123 Business Park, London, EC1A 1BB',
        vatNumber: 'GB123456789',
        poRef: 'PO-2025-0042'
    };

    function formatDate(dateStr) {
        const date = new Date(dateStr);
        return date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
    }

    function formatCurrency(amount) {
        return '£' + amount.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }

    function getStatusBadge(status) {
        const statusMap = {
            'paid': '<span class="status-badge status-paid">Paid</span>',
            'pending': '<span class="status-badge status-pending">Pending</span>',
            'overdue': '<span class="status-badge status-overdue">Overdue</span>',
            'draft': '<span class="status-badge status-draft">Draft</span>',
            'cancelled': '<span class="status-badge status-cancelled">Cancelled</span>'
        };
        return statusMap[status] || status;
    }

    function getTypeBadge(type) {
        const typeMap = {
            'purchase': '<span class="badge badge-pastel-primary">Credit Purchase</span>',
            'subscription': '<span class="badge badge-pastel-info">Subscription</span>',
            'addon': '<span class="badge badge-pastel-pink">Add-on</span>',
            'overage': '<span class="badge badge-pastel-warning">Overage</span>'
        };
        return typeMap[type] || type;
    }

    function renderInvoices(invoices) {
        const tbody = document.getElementById('invoicesTableBody');
        tbody.innerHTML = '';

        invoices.forEach(inv => {
            const row = document.createElement('tr');
            row.setAttribute('data-invoice-id', inv.id);
            row.innerHTML = `
                <td onclick="event.stopPropagation();">
                    <input type="checkbox" class="form-check-input invoice-checkbox" value="${inv.id}">
                </td>
                <td><strong>${inv.id}</strong></td>
                <td>${formatDate(inv.date)}</td>
                <td>${formatDate(inv.dueDate)}</td>
                <td>${getTypeBadge(inv.type)}</td>
                <td class="text-truncate" style="max-width: 250px;" title="${inv.description}">${inv.description}</td>
                <td class="text-end fw-medium">${formatCurrency(inv.amount)}</td>
                <td>${getStatusBadge(inv.status)}</td>
                <td class="text-end" onclick="event.stopPropagation();">
                    <div class="dropdown">
                        <button class="btn btn-sm btn-link text-muted p-0" data-bs-toggle="dropdown">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#" onclick="openDrawer('${inv.id}')"><i class="fas fa-eye me-2"></i>View Details</a></li>
                            <li><a class="dropdown-item" href="#" onclick="downloadPdf('${inv.id}')"><i class="fas fa-file-pdf me-2"></i>Download PDF</a></li>
                            ${inv.status === 'pending' || inv.status === 'overdue' ? 
                                `<li><hr class="dropdown-divider"></li><li><a class="dropdown-item text-primary" href="#" onclick="payInvoice('${inv.id}')"><i class="fas fa-credit-card me-2"></i>Pay Now</a></li>` : ''
                            }
                        </ul>
                    </div>
                </td>
            `;
            row.addEventListener('click', () => openDrawer(inv.id));
            tbody.appendChild(row);
        });

        document.getElementById('totalCount').textContent = invoices.length;
        document.getElementById('showingEnd').textContent = Math.min(10, invoices.length);
    }

    function openDrawer(invoiceId) {
        const invoice = mockInvoices.find(i => i.id === invoiceId);
        if (!invoice) return;

        document.getElementById('drawerInvoiceNumber').textContent = 'Invoice #' + invoice.id;
        document.getElementById('drawerInvoiceDate').textContent = 'Issued: ' + formatDate(invoice.date);
        
        const statusEl = document.getElementById('drawerStatus');
        statusEl.className = 'status-badge status-' + invoice.status;
        statusEl.textContent = invoice.status.charAt(0).toUpperCase() + invoice.status.slice(1);
        
        document.getElementById('drawerDueDate').textContent = formatDate(invoice.dueDate);
        document.getElementById('drawerPaymentDate').textContent = invoice.paymentDate ? formatDate(invoice.paymentDate) : '-';
        document.getElementById('drawerPaymentMethod').textContent = invoice.paymentMethod || '-';

        const lineItemsHtml = invoice.lineItems.map(item => `
            <div class="invoice-line-item">
                <div>
                    <div class="fw-medium">${item.name}</div>
                    <div class="small text-muted">${item.detail}</div>
                </div>
                <div class="text-end">
                    <div class="fw-medium">${formatCurrency(item.amount)}</div>
                </div>
            </div>
        `).join('');
        document.getElementById('drawerLineItems').innerHTML = lineItemsHtml;

        document.getElementById('drawerSubtotal').textContent = formatCurrency(invoice.netAmount);
        document.getElementById('drawerVat').textContent = formatCurrency(invoice.vat);
        document.getElementById('drawerTotal').textContent = formatCurrency(invoice.amount);

        document.getElementById('drawerCompany').textContent = billingDetails.company;
        document.getElementById('drawerAddress').textContent = billingDetails.address;
        document.getElementById('drawerVatNumber').textContent = billingDetails.vatNumber;
        document.getElementById('drawerPoRef').textContent = billingDetails.poRef;

        const payNowBtn = document.getElementById('payNowBtn');
        if (invoice.status === 'pending' || invoice.status === 'overdue') {
            payNowBtn.style.display = 'block';
            payNowBtn.onclick = () => payInvoice(invoice.id);
        } else {
            payNowBtn.style.display = 'none';
        }

        document.getElementById('downloadPdfBtn').onclick = () => downloadPdf(invoice.id);

        document.getElementById('invoiceDrawer').classList.add('open');
        document.getElementById('drawerOverlay').classList.add('show');
    }

    function closeDrawer() {
        document.getElementById('invoiceDrawer').classList.remove('open');
        document.getElementById('drawerOverlay').classList.remove('show');
    }

    window.openDrawer = openDrawer;
    window.closeDrawer = closeDrawer;

    document.getElementById('closeDrawerBtn').addEventListener('click', closeDrawer);
    document.getElementById('drawerOverlay').addEventListener('click', closeDrawer);

    window.downloadPdf = function(invoiceId) {
        alert('TODO: Download PDF for invoice ' + invoiceId + '\n\nThis will fetch the PDF from HubSpot invoice API.');
    };

    window.payInvoice = function(invoiceId) {
        const invoice = mockInvoices.find(i => i.id === invoiceId);
        if (!invoice) return;

        document.getElementById('paymentInvoiceNumber').textContent = 'Invoice #' + invoice.id;
        document.getElementById('paymentAmount').textContent = formatCurrency(invoice.amount);
        
        const modal = new bootstrap.Modal(document.getElementById('paymentModal'));
        modal.show();
        closeDrawer();
    };

    document.getElementById('confirmPaymentBtn').addEventListener('click', function() {
        alert('TODO: Redirect to Stripe checkout\n\nThis will create a HubSpot invoice payment link and redirect user to Stripe.');
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
        alert('TODO: Create HubSpot invoice and redirect to Stripe\n\nThis will use HubSpot Products API to create an invoice and get a Stripe payment link.');
    });

    document.getElementById('selectAll').addEventListener('change', function() {
        document.querySelectorAll('.invoice-checkbox').forEach(cb => {
            cb.checked = this.checked;
        });
    });

    document.getElementById('applyFiltersBtn').addEventListener('click', function() {
        console.log('TODO: Apply filters and fetch from HubSpot API');
        renderInvoices(mockInvoices);
    });

    document.getElementById('clearFiltersBtn').addEventListener('click', function() {
        document.getElementById('dateRangeFilter').value = '365';
        document.getElementById('statusFilter').value = '';
        document.getElementById('typeFilter').value = '';
        document.getElementById('amountFilter').value = '';
        document.getElementById('searchFilter').value = '';
        renderInvoices(mockInvoices);
    });

    document.getElementById('exportBtn').addEventListener('click', function() {
        alert('TODO: Export invoices to CSV\n\nThis will generate a CSV file of filtered invoices.');
    });

    renderInvoices(mockInvoices);
    updateTopUpSummary(500);
});
</script>
@endpush
