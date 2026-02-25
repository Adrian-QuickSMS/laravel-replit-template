@extends('layouts.quicksms')

@section('title', 'Purchase Numbers')

@push('styles')
<style>
.purchase-numbers-container {
    min-height: calc(100vh - 200px);
}
.access-denied-card {
    max-width: 500px;
    margin: 100px auto;
    text-align: center;
}
.access-denied-card .icon-wrapper {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: rgba(220, 53, 69, 0.15);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
}
.access-denied-card .icon-wrapper i {
    font-size: 2rem;
    color: #dc3545;
}
.purchase-header {
    margin-bottom: 1.5rem;
}
.purchase-header h2 {
    margin-bottom: 0.25rem;
}
.purchase-header p {
    color: #6c757d;
    margin-bottom: 0;
}
.product-card {
    border: none;
    border-radius: 0.75rem;
    transition: all 0.2s ease;
    overflow: hidden;
    cursor: pointer;
    display: flex;
    flex-direction: column;
}
.product-card:hover {
    box-shadow: 0 8px 24px rgba(111, 66, 193, 0.25);
    transform: translateY(-2px);
}
.product-card.selected {
    box-shadow: 0 0 0 3px rgba(111, 66, 193, 0.4), 0 8px 24px rgba(111, 66, 193, 0.25);
}
.product-card .product-header {
    padding: 1rem 1.25rem;
    text-align: center;
    position: relative;
    z-index: 1;
    min-height: 120px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}
.product-card .product-header h4 {
    color: #fff;
    font-weight: 700;
    font-size: 1.1rem;
    margin-bottom: 0.375rem;
    line-height: 1.3;
}
.product-card .product-header p {
    color: rgba(255, 255, 255, 0.85);
    font-size: 0.8rem;
    margin-bottom: 0;
    line-height: 1.4;
}
.product-card .product-body {
    padding: 0.75rem 1.25rem;
    background: #fff;
    flex: 1;
}
.best-value-badge {
    display: inline-block;
    background: rgba(255, 255, 255, 0.95);
    color: #6f42c1;
    padding: 0.25rem 0.75rem;
    border-radius: 1rem;
    font-size: 0.75rem;
    font-weight: 600;
}
.product-body .price-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.375rem 0;
    border-bottom: 1px solid #f0f0f0;
}
.product-body .price-row:last-child {
    border-bottom: none;
}
.product-body .price-label {
    font-size: 0.8rem;
    color: #6c757d;
}
.product-body .price-value {
    font-weight: 600;
    font-size: 0.875rem;
    color: var(--primary);
}
.product-body .price-value.contact-sales {
    color: #6c757d;
    font-size: 0.7rem;
    font-style: italic;
    text-align: right;
}
.product-footer {
    padding: 0.75rem 1.25rem 1rem;
    background: #fff;
    border-bottom-left-radius: 0.75rem;
    border-bottom-right-radius: 0.75rem;
    margin-top: auto;
    position: relative;
    z-index: 2;
}
.product-footer .btn-select {
    width: 100%;
    padding: 0.5rem 1rem;
    font-weight: 600;
    font-size: 0.875rem;
}
.product-card .product-footer .btn.btn-select {
    background-color: var(--primary) !important;
    border-color: var(--primary) !important;
    color: #fff !important;
    opacity: 1 !important;
}
.product-card .product-footer .btn.btn-select:hover {
    background-color: var(--primary-hover) !important;
    border-color: var(--primary-hover) !important;
}
.product-card.selected .btn-select {
    background: #1cbb8c !important;
    border-color: #1cbb8c !important;
}
.product-card.selected .btn-select::after {
    content: ' ✓';
}
.selection-panel {
    margin-top: 2rem;
    padding: 1.5rem;
    background: #fff;
    border-radius: 0.75rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    display: none;
}
.selection-panel.active {
    display: block;
}
.panel-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #f0ebf8;
}
.panel-header h5 {
    margin: 0;
    font-weight: 600;
    color: #2c2c2c;
}
.panel-header .btn-purchase {
    background: var(--primary);
    border-color: var(--primary);
    color: #fff;
    padding: 0.5rem 1.25rem;
    font-weight: 600;
}
.panel-header .btn-purchase:hover:not(:disabled) {
    background: var(--primary-hover);
    border-color: var(--primary-hover);
}
.panel-header .btn-purchase:disabled {
    background: #ccc;
    border-color: #ccc;
}
.vmn-table-container {
    overflow-x: auto;
}
.vmn-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
}
.vmn-table th {
    background: #f8f9fa;
    padding: 0.75rem 1rem;
    font-size: 0.8125rem;
    font-weight: 600;
    color: #495057;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 2px solid #e9ecef;
    cursor: pointer;
    user-select: none;
}
.vmn-table th:hover {
    background: #e9ecef;
}
.vmn-table th .sort-icon {
    margin-left: 0.5rem;
    opacity: 0.3;
}
.vmn-table th.sorted .sort-icon {
    opacity: 1;
    color: var(--primary);
}
.vmn-table td {
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #f0f0f0;
    font-size: 0.875rem;
    vertical-align: middle;
}
.vmn-table tr:hover {
    background: #faf8fc;
}
.vmn-table tr.selected {
    background: rgba(111, 66, 193, 0.08);
}
.vmn-number {
    color: #2c2c2c;
}
.fee-cell {
    font-weight: 600;
    color: var(--primary);
}
.table-controls {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    gap: 1rem;
    flex-wrap: wrap;
}
.table-controls .search-box {
    flex: 1;
    max-width: 300px;
}
.table-controls .filter-box {
    min-width: 180px;
}
.selection-summary {
    background: rgba(111, 66, 193, 0.08);
    border-radius: 0.5rem;
    padding: 1rem 1.25rem;
    margin-top: 1rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}
.selection-summary .summary-text {
    font-size: 0.9375rem;
    color: #495057;
}
.selection-summary .summary-text strong {
    color: var(--primary);
}
.selection-summary .summary-costs {
    display: flex;
    gap: 1.5rem;
}
.selection-summary .cost-item {
    text-align: right;
}
.selection-summary .cost-label {
    font-size: 0.75rem;
    color: #6c757d;
    display: block;
}
.selection-summary .cost-value {
    font-size: 1.125rem;
    font-weight: 700;
    color: var(--primary);
}
.keyword-section {
    margin-top: 1.5rem;
}
.keyword-input-group {
    display: flex;
    gap: 0.75rem;
    margin-bottom: 1rem;
}
.keyword-input-group .form-control {
    flex: 1;
    max-width: 300px;
}
.keyword-input-group .btn {
    min-width: 100px;
}
.keyword-validation-feedback {
    font-size: 0.8125rem;
    margin-top: 0.25rem;
}
.keyword-validation-feedback.valid {
    color: #1cbb8c;
}
.keyword-validation-feedback.invalid {
    color: #dc3545;
}
.selected-keywords-list {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-bottom: 1rem;
}
.keyword-tag {
    display: inline-flex;
    align-items: center;
    background: rgba(111, 66, 193, 0.12);
    color: var(--primary);
    padding: 0.375rem 0.75rem;
    border-radius: 1rem;
    font-size: 0.8125rem;
    font-weight: 600;
}
.keyword-tag .remove-keyword {
    margin-left: 0.5rem;
    cursor: pointer;
    opacity: 0.7;
}
.keyword-tag .remove-keyword:hover {
    opacity: 1;
}
.taken-keywords-section {
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 1px solid #e9ecef;
}
.taken-keywords-section h6 {
    font-weight: 600;
    color: #6c757d;
    margin-bottom: 1rem;
}
.taken-keywords-table {
    max-height: 300px;
    overflow-y: auto;
}
.contact-sales-panel {
    text-align: center;
    padding: 3rem 2rem;
}
.contact-sales-panel .icon-wrapper {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: rgba(111, 66, 193, 0.12);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
}
.contact-sales-panel .icon-wrapper i {
    font-size: 2rem;
    color: var(--primary);
}
.contact-sales-panel h5 {
    font-weight: 600;
    margin-bottom: 0.75rem;
}
.contact-sales-panel p {
    color: #6c757d;
    margin-bottom: 1.5rem;
    max-width: 400px;
    margin-left: auto;
    margin-right: auto;
}
.pricing-loading {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 1rem;
    background: rgba(111, 66, 193, 0.08);
    border-radius: 0.5rem;
    margin-bottom: 1.5rem;
}
.pricing-warning {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem;
    background: rgba(255, 193, 7, 0.15);
    border: 1px solid rgba(255, 193, 7, 0.3);
    border-radius: 0.5rem;
    margin-bottom: 1.5rem;
    color: #856404;
}
.empty-state {
    text-align: center;
    padding: 3rem;
    color: #6c757d;
}
.empty-state i {
    font-size: 3rem;
    opacity: 0.3;
    margin-bottom: 1rem;
}
.success-icon-wrapper {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: rgba(28, 187, 140, 0.15);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
}
.success-icon-wrapper i {
    font-size: 2rem;
    color: #1cbb8c;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row page-titles">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="#">Purchase</a></li>
            <li class="breadcrumb-item active"><a href="javascript:void(0)">Numbers</a></li>
        </ol>
    </div>
</div>
<div class="container-fluid purchase-numbers-container">
    @php
        $currentUserRole = 'admin';
        $allowedRoles = ['admin', 'finance', 'messaging_manager'];
    @endphp
    
    <div id="accessDeniedView" style="display: none;">
        <div class="card access-denied-card">
            <div class="card-body py-5">
                <div class="icon-wrapper">
                    <i class="fas fa-lock"></i>
                </div>
                <h4 class="mb-3">Access Restricted</h4>
                <p class="text-muted mb-4">This page is only accessible to Admin, Finance, and Messaging Manager users. Please contact your administrator if you need access.</p>
                <a href="{{ route('dashboard') }}" class="btn btn-primary">
                    <i class="fas fa-arrow-left me-2"></i>Return to Dashboard
                </a>
            </div>
        </div>
    </div>
    
    <div id="purchaseContent">
        <div class="purchase-header d-flex justify-content-between align-items-start">
            <div>
                <h2>Purchase Numbers</h2>
                <p>Acquire dedicated numbers for two-way messaging and customer engagement</p>
            </div>
            <div class="badge bg-light text-dark fs-6 px-3 py-2">
                <i class="fas fa-globe me-1"></i>
                <span>GBP</span>
            </div>
        </div>

<div class="row g-4 mb-4" id="productCards" style="align-items: stretch;">
            <div class="col-md-4 d-flex">
                <div class="card product-card tryal-gradient w-100" data-product="vmn" onclick="selectProduct('vmn')">
                    <div class="product-header">
                        <div class="mb-2">
                            <span class="best-value-badge">Best value</span>
                        </div>
                        <h4>UK Virtual Mobile Number</h4>
                        <p>Standard UK mobile (07xxx) for two-way SMS messaging</p>
                    </div>
                    <div class="product-body">
                        <div class="price-row">
                            <span class="price-label">Setup Cost</span>
                            <span class="price-value" id="vmnSetupPrice">£2.00</span>
                        </div>
                        <div class="price-row">
                            <span class="price-label">Monthly Cost</span>
                            <span class="price-value" id="vmnMonthlyPrice">£2.00</span>
                        </div>
                    </div>
                    <div class="product-footer">
                        <button class="btn btn-select" style="background-color: #886CC0; border-color: #886CC0; color: #fff;">Select</button>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 d-flex">
                <div class="card product-card tryal-gradient w-100" data-product="shared" onclick="selectProduct('shared')">
                    <div class="product-header">
                        <h4>UK Shared Short Code</h4>
                        <p>Share shortcode 60866 with custom keywords for inbound messaging</p>
                    </div>
                    <div class="product-body">
                        <div class="price-row">
                            <span class="price-label">Setup Cost</span>
                            <span class="price-value" id="keywordSetupPrice">£2.00</span>
                        </div>
                        <div class="price-row">
                            <span class="price-label">Monthly Cost</span>
                            <span class="price-value" id="keywordMonthlyPrice">£2.00</span>
                        </div>
                    </div>
                    <div class="product-footer">
                        <button class="btn btn-select" style="background-color: #886CC0; border-color: #886CC0; color: #fff;">Select</button>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 d-flex">
                <div class="card product-card tryal-gradient w-100" data-product="dedicated" onclick="selectProduct('dedicated')">
                    <div class="product-header">
                        <h4>UK Dedicated Short Code</h4>
                        <p>Exclusive shortcode for high-volume enterprise campaigns</p>
                    </div>
                    <div class="product-body">
                        <div class="price-row">
                            <span class="price-label">Setup Cost</span>
                            <span class="price-value" id="dedicatedSetupPrice">Loading...</span>
                        </div>
                        <div class="price-row">
                            <span class="price-label">Monthly Cost</span>
                            <span class="price-value" id="dedicatedMonthlyPrice">Loading...</span>
                        </div>
                    </div>
                    <div class="product-footer">
                        <button class="btn btn-select" style="background-color: #886CC0; border-color: #886CC0; color: #fff;">Select</button>
                    </div>
                </div>
            </div>
        </div>

        <div id="vmnPanel" class="selection-panel">
            <div class="panel-header">
                <h5><i class="fas fa-mobile-alt me-2 text-primary"></i>Available UK Virtual Mobile Numbers</h5>
                <button class="btn btn-purchase" id="vmnPurchaseBtn" disabled onclick="showVmnPurchaseModal()">
                    <i class="fas fa-shopping-cart me-2"></i>Purchase Selected
                </button>
            </div>

<div class="table-controls">
                <div class="search-box">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" id="vmnSearchInput" placeholder="Search numbers...">
                    </div>
                </div>
                <div class="filter-box">
                    <select class="form-select" id="vmnCountryFilter">
                        <option value="">All Countries</option>
                        <option value="GB">United Kingdom</option>
                    </select>
                </div>
            </div>

            <div class="vmn-table-container">
                <table class="vmn-table">
                    <thead>
                        <tr>
                            <th style="width: 50px;">
                                <input type="checkbox" class="form-check-input" id="vmnSelectAll" onchange="toggleVmnSelectAll()">
                            </th>
                            <th onclick="sortVmnTable('number')">
                                Mobile Number <i class="fas fa-sort sort-icon"></i>
                            </th>
                            <th onclick="sortVmnTable('country')">
                                Country <i class="fas fa-sort sort-icon"></i>
                            </th>
                            <th onclick="sortVmnTable('setupFee')">
                                Setup Fee <i class="fas fa-sort sort-icon"></i>
                            </th>
                            <th onclick="sortVmnTable('monthlyFee')">
                                Monthly Fee <i class="fas fa-sort sort-icon"></i>
                            </th>
                        </tr>
                    </thead>
                    <tbody id="vmnTableBody">
                    </tbody>
                </table>
            </div>

            <div id="vmnSelectionSummary" class="selection-summary" style="display: none;">
                <div class="summary-text">
                    <strong id="vmnSelectedCount">0</strong> number(s) selected
                </div>
                <div class="summary-costs">
                    <div class="cost-item">
                        <span class="cost-label">Setup (ex VAT)</span>
                        <span class="cost-value">£<span id="vmnSetupTotal">0.00</span></span>
                    </div>
                    <div class="cost-item" id="vmnSetupVatRow" style="display:none">
                        <span class="cost-label">VAT (<span id="vmnVatRatePct">20</span>%)</span>
                        <span class="cost-value">£<span id="vmnSetupVat">0.00</span></span>
                    </div>
                    <div class="cost-item" id="vmnSetupIncVatRow" style="display:none">
                        <span class="cost-label fw-bold">Setup Total inc VAT</span>
                        <span class="cost-value fw-bold">£<span id="vmnSetupIncVat">0.00</span></span>
                    </div>
                    <div class="cost-item">
                        <span class="cost-label">Monthly (ex VAT)</span>
                        <span class="cost-value">£<span id="vmnMonthlyTotal">0.00</span>/mo</span>
                    </div>
                    <div class="cost-item" id="vmnMonthlyVatRow" style="display:none">
                        <span class="cost-label">Monthly VAT</span>
                        <span class="cost-value">£<span id="vmnMonthlyVat">0.00</span>/mo</span>
                    </div>
                    <div class="cost-item" id="vmnMonthlyIncVatRow" style="display:none">
                        <span class="cost-label fw-bold">Monthly Total inc VAT</span>
                        <span class="cost-value fw-bold">£<span id="vmnMonthlyIncVat">0.00</span>/mo</span>
                    </div>
                </div>
            </div>
        </div>

        <div id="sharedPanel" class="selection-panel">
            <div class="panel-header">
                <h5><i class="fas fa-share-alt me-2 text-primary"></i>UK Shared Short Code - Keywords</h5>
                <button class="btn btn-purchase" id="keywordPurchaseBtn" disabled onclick="showKeywordPurchaseModal()">
                    <i class="fas fa-shopping-cart me-2"></i>Purchase Selected
                </button>
            </div>

            <div class="alert alert-pastel-primary mb-3">
                <i class="fas fa-info-circle me-2 text-primary"></i>
                Shared shortcode: <strong>60866</strong> — Add keywords to receive inbound messages on this shared number.
            </div>

            <div class="keyword-section">
                <label class="form-label fw-semibold">Enter Keywords</label>
                <div class="keyword-input-group">
                    <input type="text" class="form-control" id="keywordInput" placeholder="Enter keyword (e.g., PROMO)" maxlength="20">
                    <button class="btn btn-primary" id="addKeywordBtn" onclick="addKeyword()" disabled>
                        <i class="fas fa-plus me-1"></i>Add
                    </button>
                </div>
                <div id="keywordValidationFeedback" class="keyword-validation-feedback"></div>
            </div>

            <div id="selectedKeywordsContainer" style="display: none;">
                <label class="form-label fw-semibold mt-3">Selected Keywords</label>
                <div id="selectedKeywordsList" class="selected-keywords-list"></div>
            </div>

            <div id="keywordSelectionSummary" class="selection-summary" style="display: none;">
                <div class="summary-text">
                    <strong id="keywordSelectedCount">0</strong> keyword(s) selected
                </div>
                <div class="summary-costs">
                    <div class="cost-item">
                        <span class="cost-label">Setup (ex VAT)</span>
                        <span class="cost-value">£<span id="keywordSetupTotal">0.00</span></span>
                    </div>
                    <div class="cost-item" id="kwSetupVatRow" style="display:none">
                        <span class="cost-label">VAT (<span id="kwVatRatePct">20</span>%)</span>
                        <span class="cost-value">£<span id="kwSetupVat">0.00</span></span>
                    </div>
                    <div class="cost-item" id="kwSetupIncVatRow" style="display:none">
                        <span class="cost-label fw-bold">Setup Total inc VAT</span>
                        <span class="cost-value fw-bold">£<span id="kwSetupIncVat">0.00</span></span>
                    </div>
                    <div class="cost-item">
                        <span class="cost-label">Monthly (ex VAT)</span>
                        <span class="cost-value">£<span id="keywordMonthlyTotal">0.00</span>/mo</span>
                    </div>
                    <div class="cost-item" id="kwMonthlyVatRow" style="display:none">
                        <span class="cost-label">Monthly VAT</span>
                        <span class="cost-value">£<span id="kwMonthlyVat">0.00</span>/mo</span>
                    </div>
                    <div class="cost-item" id="kwMonthlyIncVatRow" style="display:none">
                        <span class="cost-label fw-bold">Monthly Total inc VAT</span>
                        <span class="cost-value fw-bold">£<span id="kwMonthlyIncVat">0.00</span>/mo</span>
                    </div>
                </div>
            </div>

            <div class="taken-keywords-section">
                <h6><i class="fas fa-ban me-2"></i>Taken Keywords (unavailable)</h6>
                <div class="table-controls">
                    <div class="search-box">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control" id="takenKeywordSearch" placeholder="Search taken keywords...">
                        </div>
                    </div>
                </div>
                <div class="taken-keywords-table">
                    <table class="vmn-table">
                        <thead>
                            <tr>
                                <th>Keyword</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="takenKeywordsBody">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div id="dedicatedPanel" class="selection-panel">
            <div class="contact-sales-panel">
                <div class="icon-wrapper">
                    <i class="fas fa-headset"></i>
                </div>
                <h5>Contact Sales for Dedicated Short Codes</h5>
                <p>Dedicated short codes are exclusive numbers for your organization. Our team will help you choose the right short code and guide you through the regulatory approval process.</p>
                <a href="mailto:sales@quicksms.com" class="btn btn-primary btn-lg">
                    <i class="fas fa-envelope me-2"></i>Contact Sales
                </a>
                <p class="mt-3 text-muted small">Or call us at <strong>0800 123 4567</strong></p>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="vmnPurchaseModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-shopping-cart me-2 text-primary"></i>Confirm VMN Purchase</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Selected Numbers</label>
                    <div id="modalVmnList" class="border rounded p-2" style="max-height: 150px; overflow-y: auto;"></div>
                </div>
                
                <table class="table table-sm mb-3">
                    <tbody>
                        <tr>
                            <td class="text-muted">Setup fee (ex VAT)</td>
                            <td class="text-end">£<span id="modalVmnSetup">0.00</span></td>
                        </tr>
                        <tr id="modalVmnVatRow">
                            <td class="text-muted">VAT (<span id="modalVmnVatRate">20</span>%)</td>
                            <td class="text-end">£<span id="modalVmnSetupVat">0.00</span></td>
                        </tr>
                        <tr class="fw-bold border-top">
                            <td>Total due now (inc VAT)</td>
                            <td class="text-end text-primary">£<span id="modalVmnSetupTotal">0.00</span></td>
                        </tr>
                        <tr>
                            <td class="text-muted pt-2">Monthly fee (ex VAT)</td>
                            <td class="text-end pt-2">£<span id="modalVmnMonthly">0.00</span>/mo</td>
                        </tr>
                        <tr id="modalVmnMonthlyVatRow">
                            <td class="text-muted">Monthly VAT</td>
                            <td class="text-end">£<span id="modalVmnMonthlyVat">0.00</span>/mo</td>
                        </tr>
                        <tr class="fw-bold border-top">
                            <td>Monthly total (inc VAT)</td>
                            <td class="text-end text-primary">£<span id="modalVmnMonthlyTotal">0.00</span>/mo</td>
                        </tr>
                    </tbody>
                </table>

                <div class="alert alert-pastel-primary small mb-0">
                    <i class="fas fa-info-circle me-1 text-primary"></i>
                    Setup fee is charged immediately. Monthly fee starts on the 1st of each month.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="executeVmnPurchase()">
                    <i class="fas fa-check me-2"></i>Confirm Purchase
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="keywordPurchaseModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-shopping-cart me-2 text-primary"></i>Confirm Keyword Purchase</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Selected Keywords</label>
                    <div id="modalKeywordList" class="border rounded p-2" style="max-height: 150px; overflow-y: auto;"></div>
                </div>

                <table class="table table-sm mb-3">
                    <tbody>
                        <tr>
                            <td class="text-muted">Setup fee (ex VAT)</td>
                            <td class="text-end">£<span id="modalKeywordSetup">0.00</span></td>
                        </tr>
                        <tr id="modalKeywordVatRow">
                            <td class="text-muted">VAT (<span id="modalKeywordVatRate">20</span>%)</td>
                            <td class="text-end">£<span id="modalKeywordSetupVat">0.00</span></td>
                        </tr>
                        <tr class="fw-bold border-top">
                            <td>Total due now (inc VAT)</td>
                            <td class="text-end text-primary">£<span id="modalKeywordSetupTotal">0.00</span></td>
                        </tr>
                        <tr>
                            <td class="text-muted pt-2">Monthly fee (ex VAT)</td>
                            <td class="text-end pt-2">£<span id="modalKeywordMonthly">0.00</span>/mo</td>
                        </tr>
                        <tr id="modalKeywordMonthlyVatRow">
                            <td class="text-muted">Monthly VAT</td>
                            <td class="text-end">£<span id="modalKeywordMonthlyVat">0.00</span>/mo</td>
                        </tr>
                        <tr class="fw-bold border-top">
                            <td>Monthly total (inc VAT)</td>
                            <td class="text-end text-primary">£<span id="modalKeywordMonthlyTotal">0.00</span>/mo</td>
                        </tr>
                    </tbody>
                </table>

                <div class="alert alert-pastel-primary small mb-0">
                    <i class="fas fa-info-circle me-1 text-primary"></i>
                    Setup fee is charged immediately. Monthly fee starts on the 1st of each month.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="executeKeywordPurchase()">
                    <i class="fas fa-check me-2"></i>Confirm Purchase
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="insufficientBalanceModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle text-warning me-2"></i>Insufficient Balance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <div class="mb-4">
                    <i class="fas fa-wallet text-warning" style="font-size: 3rem; opacity: 0.6;"></i>
                </div>
                <p class="mb-3">Your account balance is insufficient to complete this purchase.</p>
                <div class="row g-3 mb-3">
                    <div class="col-4">
                        <div class="border rounded p-2">
                            <div class="text-muted small">Current Balance</div>
                            <div class="fw-bold">£<span id="insufficientBalance">0.00</span></div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="border rounded p-2">
                            <div class="text-muted small">Required</div>
                            <div class="fw-bold text-danger">£<span id="insufficientRequired">0.00</span></div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="border rounded p-2">
                            <div class="text-muted small">Shortfall</div>
                            <div class="fw-bold text-danger">£<span id="insufficientShortfall">0.00</span></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <a href="{{ route('purchase.messages') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Top Up Balance
                </a>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="purchaseSuccessModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center py-5">
                <div class="mb-4">
                    <div class="success-icon-wrapper">
                        <i class="fas fa-check"></i>
                    </div>
                </div>
                <h4 class="mb-2">Payment successful</h4>
                <p class="text-muted mb-4" id="successMessage">Your number has been purchased successfully.</p>
                <a href="{{ route('management.numbers') }}" class="btn btn-primary btn-lg">
                    <i class="fas fa-cog me-2"></i>Configure number
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
var accountBalance = {{ $accountBalance }};
var selectedProduct = null;
var vmnPoolData = [];
var takenKeywords = [];
var sharedShortcodes = [];
var sharedShortcodeId = null;
var selectedVmnIds = [];
var selectedKeywords = [];
var vmnSortColumn = 'number';
var vmnSortDirection = 'asc';
var vmnSearchTerm = '';
var keywordSetupFee = 0;
var keywordMonthlyFee = 0;
var vmnSetupFee = 0;
var vmnMonthlyFee = 0;
var vatRate = 0;

var countryNames = {
    'GB': 'United Kingdom', 'US': 'United States', 'AU': 'Australia',
    'DE': 'Germany', 'FR': 'France', 'ES': 'Spain', 'IT': 'Italy', 'NL': 'Netherlands'
};

function csrfHeaders() {
    var token = document.querySelector('meta[name=csrf-token]');
    return {
        'X-CSRF-TOKEN': token ? token.content : '',
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    };
}

document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('accessDeniedView').style.display = 'none';
    document.getElementById('purchaseContent').style.display = 'block';
    loadPricing();
    setupEventListeners();
});

function loadPricing() {
    fetch('/api/numbers/pricing', { headers: { 'Accept': 'application/json' } })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            vatRate = parseFloat(data.vat_rate) || 0;
            if (data.vmn) {
                vmnSetupFee = parseFloat(data.vmn.setup_fee) || 0;
                vmnMonthlyFee = parseFloat(data.vmn.monthly_fee) || 0;
                var sym = data.vmn.currency === 'GBP' ? '£' : (data.vmn.currency + ' ');
                var el1 = document.getElementById('vmnSetupPrice');
                var el2 = document.getElementById('vmnMonthlyPrice');
                if (el1) el1.textContent = sym + vmnSetupFee.toFixed(2);
                if (el2) el2.textContent = sym + vmnMonthlyFee.toFixed(2);
            }
            if (data.keyword) {
                keywordSetupFee = parseFloat(data.keyword.setup_fee) || 0;
                keywordMonthlyFee = parseFloat(data.keyword.monthly_fee) || 0;
                var sym = data.keyword.currency === 'GBP' ? '£' : (data.keyword.currency + ' ');
                var el3 = document.getElementById('keywordSetupPrice');
                var el4 = document.getElementById('keywordMonthlyPrice');
                if (el3) el3.textContent = sym + keywordSetupFee.toFixed(2);
                if (el4) el4.textContent = sym + keywordMonthlyFee.toFixed(2);
            }
            var el5 = document.getElementById('dedicatedSetupPrice');
            var el6 = document.getElementById('dedicatedMonthlyPrice');
            if (data.dedicated_shortcode) {
                var dsym = data.dedicated_shortcode.currency === 'GBP' ? '£' : (data.dedicated_shortcode.currency + ' ');
                var dsSetup = parseFloat(data.dedicated_shortcode.setup_fee) || 0;
                var dsMonthly = parseFloat(data.dedicated_shortcode.monthly_fee) || 0;
                if (el5) el5.textContent = dsym + dsSetup.toFixed(2);
                if (el6) el6.textContent = dsym + dsMonthly.toFixed(2);
            } else {
                if (el5) { el5.textContent = 'Contact sales'; el5.classList.add('contact-sales'); }
                if (el6) { el6.textContent = 'Contact sales'; el6.classList.add('contact-sales'); }
            }
            sharedShortcodes = data.shared_shortcodes || [];
            sharedShortcodeId = sharedShortcodes.length > 0 ? sharedShortcodes[0].id : null;
        })
        .catch(function(err) { console.error('Pricing load error', err); });
}

function setupEventListeners() {
    document.getElementById('vmnSearchInput').addEventListener('input', function(e) {
        vmnSearchTerm = e.target.value.toLowerCase();
        renderVmnTable();
    });
    document.getElementById('vmnCountryFilter').addEventListener('change', function() {
        loadVmnPool(this.value || null);
    });
    document.getElementById('keywordInput').addEventListener('input', validateKeywordInput);
    document.getElementById('keywordInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && !document.getElementById('addKeywordBtn').disabled) addKeyword();
    });
    document.getElementById('takenKeywordSearch').addEventListener('input', renderTakenKeywords);
}

function selectProduct(product) {
    document.querySelectorAll('.product-card').forEach(function(c) { c.classList.remove('selected'); });
    document.querySelector('[data-product="' + product + '"]').classList.add('selected');
    document.querySelectorAll('.selection-panel').forEach(function(p) { p.classList.remove('active'); });
    selectedProduct = product;
    document.getElementById(product + 'Panel').classList.add('active');
    if (product === 'vmn') {
        if (vmnPoolData.length === 0) loadVmnPool(null);
    } else if (product === 'shared') {
        loadTakenKeywords();
        if (sharedShortcodeId === null) {
            setTimeout(function() { if (sharedShortcodeId === null) disableKeywordPurchase(); }, 1500);
        }
    }
}

function loadVmnPool(countryIso) {
    var tbody = document.getElementById('vmnTableBody');
    tbody.innerHTML = '<tr><td colspan="5" class="text-center py-3"><span class="spinner-border spinner-border-sm me-2"></span>Loading available numbers...</td></tr>';
    selectedVmnIds = [];
    var params = new URLSearchParams({ per_page: 100 });
    if (countryIso) params.set('country_iso', countryIso);
    fetch('/api/numbers/pool?' + params.toString(), { headers: { 'Accept': 'application/json' } })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            vmnPoolData = (data.data || []).map(function(item) {
                return {
                    id: item.id,
                    number: item.number,
                    country: item.country_iso,
                    countryName: countryNames[item.country_iso] || item.country_iso,
                    setupFee: item.setup_fee != null ? parseFloat(item.setup_fee) : vmnSetupFee,
                    monthlyFee: item.monthly_fee != null ? parseFloat(item.monthly_fee) : vmnMonthlyFee,
                };
            });
            renderVmnTable();
        })
        .catch(function(err) {
            console.error('Pool load error', err);
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-danger py-3"><i class="fas fa-exclamation-circle me-2"></i>Failed to load numbers. Please refresh and try again.</td></tr>';
        });
}

function loadTakenKeywords() {
    fetch('/api/numbers/keywords/taken?shortcode=60866', { headers: { 'Accept': 'application/json' } })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            takenKeywords = data.data || [];
            renderTakenKeywords();
            if (document.getElementById('keywordInput').value.trim()) validateKeywordInput();
        })
        .catch(function(err) { console.error('Taken keywords error', err); });
}

function disableKeywordPurchase() {
    var keywordInput = document.getElementById('keywordInput');
    var addBtn = document.getElementById('addKeywordBtn');
    var purchaseBtn = document.getElementById('keywordPurchaseBtn');
    if (keywordInput) { keywordInput.disabled = true; keywordInput.placeholder = 'No shared shortcode available'; }
    if (addBtn) addBtn.disabled = true;
    if (purchaseBtn) purchaseBtn.disabled = true;
    var feedback = document.getElementById('keywordValidationFeedback');
    if (feedback) { feedback.className = 'keyword-validation-feedback invalid'; feedback.innerHTML = '<i class="fas fa-info-circle me-1"></i>Contact support to enable shared shortcode access'; }
}

function renderVmnTable() {
    var tbody = document.getElementById('vmnTableBody');
    var filtered = vmnPoolData.filter(function(vmn) {
        return !vmnSearchTerm || vmn.number.toLowerCase().includes(vmnSearchTerm);
    });
    filtered.sort(function(a, b) {
        var valA = a[vmnSortColumn]; var valB = b[vmnSortColumn];
        if (typeof valA === 'string') valA = valA.toLowerCase();
        if (typeof valB === 'string') valB = valB.toLowerCase();
        if (valA < valB) return vmnSortDirection === 'asc' ? -1 : 1;
        if (valA > valB) return vmnSortDirection === 'asc' ? 1 : -1;
        return 0;
    });
    if (filtered.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5"><div class="empty-state"><i class="fas fa-search"></i><h5>No numbers found</h5><p>Try adjusting your search or filters.</p></div></td></tr>';
        updateVmnSelection();
        return;
    }
    var html = '';
    filtered.forEach(function(vmn) {
        var isSelected = selectedVmnIds.includes(vmn.id);
        html += '<tr class="' + (isSelected ? 'selected' : '') + '">';
        html += '<td><input type="checkbox" class="form-check-input" ' + (isSelected ? 'checked' : '') + ' onchange="toggleVmnSelect(\'' + vmn.id + '\')"></td>';
        html += '<td><span class="vmn-number">' + vmn.number + '</span></td>';
        html += '<td>' + vmn.countryName + '</td>';
        html += '<td><span class="fee-cell">£' + vmn.setupFee.toFixed(2) + '</span></td>';
        html += '<td><span class="fee-cell">£' + vmn.monthlyFee.toFixed(2) + '/mo</span></td>';
        html += '</tr>';
    });
    tbody.innerHTML = html;
    updateVmnSelection();
}

function sortVmnTable(column) {
    if (vmnSortColumn === column) {
        vmnSortDirection = vmnSortDirection === 'asc' ? 'desc' : 'asc';
    } else {
        vmnSortColumn = column;
        vmnSortDirection = 'asc';
    }
    renderVmnTable();
}

function toggleVmnSelect(id) {
    var idx = selectedVmnIds.indexOf(id);
    if (idx === -1) selectedVmnIds.push(id);
    else selectedVmnIds.splice(idx, 1);
    renderVmnTable();
}

function toggleVmnSelectAll() {
    var allChecked = document.getElementById('vmnSelectAll').checked;
    selectedVmnIds = allChecked ? vmnPoolData.map(function(v) { return v.id; }) : [];
    renderVmnTable();
}

function updateVmnSelection() {
    var count = selectedVmnIds.length;
    var setupTotal = 0; var monthlyTotal = 0;
    selectedVmnIds.forEach(function(id) {
        var vmn = vmnPoolData.find(function(v) { return v.id === id; });
        if (vmn) { setupTotal += vmn.setupFee; monthlyTotal += vmn.monthlyFee; }
    });
    var setupVat = setupTotal * vatRate / 100;
    var monthlyVat = monthlyTotal * vatRate / 100;
    var hasVat = vatRate > 0;

    document.getElementById('vmnPurchaseBtn').disabled = count === 0;
    document.getElementById('vmnSelectedCount').textContent = count;
    document.getElementById('vmnSetupTotal').textContent = setupTotal.toFixed(2);
    document.getElementById('vmnMonthlyTotal').textContent = monthlyTotal.toFixed(2);
    if (hasVat && count > 0) {
        document.getElementById('vmnVatRatePct').textContent = vatRate.toFixed(0);
        document.getElementById('vmnSetupVat').textContent = setupVat.toFixed(2);
        document.getElementById('vmnSetupIncVat').textContent = (setupTotal + setupVat).toFixed(2);
        document.getElementById('vmnMonthlyVat').textContent = monthlyVat.toFixed(2);
        document.getElementById('vmnMonthlyIncVat').textContent = (monthlyTotal + monthlyVat).toFixed(2);
    }
    document.getElementById('vmnSetupVatRow').style.display = (hasVat && count > 0) ? '' : 'none';
    document.getElementById('vmnSetupIncVatRow').style.display = (hasVat && count > 0) ? '' : 'none';
    document.getElementById('vmnMonthlyVatRow').style.display = (hasVat && count > 0) ? '' : 'none';
    document.getElementById('vmnMonthlyIncVatRow').style.display = (hasVat && count > 0) ? '' : 'none';
    document.getElementById('vmnSelectionSummary').style.display = count > 0 ? 'flex' : 'none';
    document.getElementById('vmnSelectAll').checked = count > 0 && count === vmnPoolData.length;
}

function validateKeywordInput() {
    var input = document.getElementById('keywordInput');
    var feedback = document.getElementById('keywordValidationFeedback');
    var btn = document.getElementById('addKeywordBtn');
    var value = input.value.trim().toUpperCase();
    
    input.classList.remove('is-valid', 'is-invalid');
    feedback.classList.remove('valid', 'invalid');
    btn.disabled = true;
    
    if (value === '') {
        feedback.innerHTML = '';
        return;
    }
    
    if (value.length < 3) {
        input.classList.add('is-invalid');
        feedback.classList.add('invalid');
        feedback.innerHTML = '<i class="fas fa-times-circle me-1"></i>Minimum 3 characters';
        return;
    }
    
    if (value.length > 20) {
        input.classList.add('is-invalid');
        feedback.classList.add('invalid');
        feedback.innerHTML = '<i class="fas fa-times-circle me-1"></i>Maximum 20 characters';
        return;
    }
    
    if (!/^[A-Za-z0-9]+$/.test(value)) {
        input.classList.add('is-invalid');
        feedback.classList.add('invalid');
        feedback.innerHTML = '<i class="fas fa-times-circle me-1"></i>Alphanumeric only (no spaces)';
        return;
    }
    
    if (takenKeywords.includes(value)) {
        input.classList.add('is-invalid');
        feedback.classList.add('invalid');
        feedback.innerHTML = '<i class="fas fa-times-circle me-1"></i>This keyword is already taken';
        return;
    }
    
    if (selectedKeywords.includes(value)) {
        input.classList.add('is-invalid');
        feedback.classList.add('invalid');
        feedback.innerHTML = '<i class="fas fa-times-circle me-1"></i>Already added to your selection';
        return;
    }
    
    input.classList.add('is-valid');
    feedback.classList.add('valid');
    feedback.innerHTML = '<i class="fas fa-check-circle me-1"></i>Keyword available';
    btn.disabled = false;
}

function addKeyword() {
    var input = document.getElementById('keywordInput');
    var value = input.value.trim().toUpperCase();
    
    if (value && !selectedKeywords.includes(value) && !takenKeywords.includes(value)) {
        selectedKeywords.push(value);
        input.value = '';
        document.getElementById('keywordValidationFeedback').innerHTML = '';
        document.getElementById('addKeywordBtn').disabled = true;
        input.classList.remove('is-valid', 'is-invalid');
        renderSelectedKeywords();
    }
}

function removeKeyword(keyword) {
    var idx = selectedKeywords.indexOf(keyword);
    if (idx !== -1) {
        selectedKeywords.splice(idx, 1);
        renderSelectedKeywords();
    }
}

function renderSelectedKeywords() {
    var container = document.getElementById('selectedKeywordsContainer');
    var list = document.getElementById('selectedKeywordsList');
    
    if (selectedKeywords.length === 0) {
        container.style.display = 'none';
        document.getElementById('keywordSelectionSummary').style.display = 'none';
        document.getElementById('keywordPurchaseBtn').disabled = true;
        return;
    }
    
    container.style.display = 'block';
    
    var html = '';
    selectedKeywords.forEach(function(kw) {
        html += '<span class="keyword-tag">' + kw + '<i class="fas fa-times remove-keyword" onclick="removeKeyword(\'' + kw + '\')"></i></span>';
    });
    list.innerHTML = html;
    
    var count = selectedKeywords.length;
    var setupTotal = count * keywordSetupFee;
    var monthlyTotal = count * keywordMonthlyFee;
    var setupVat = setupTotal * vatRate / 100;
    var monthlyVat = monthlyTotal * vatRate / 100;
    var hasVat = vatRate > 0;

    document.getElementById('keywordSelectedCount').textContent = count;
    document.getElementById('keywordSetupTotal').textContent = setupTotal.toFixed(2);
    document.getElementById('keywordMonthlyTotal').textContent = monthlyTotal.toFixed(2);
    if (hasVat) {
        document.getElementById('kwVatRatePct').textContent = vatRate.toFixed(0);
        document.getElementById('kwSetupVat').textContent = setupVat.toFixed(2);
        document.getElementById('kwSetupIncVat').textContent = (setupTotal + setupVat).toFixed(2);
        document.getElementById('kwMonthlyVat').textContent = monthlyVat.toFixed(2);
        document.getElementById('kwMonthlyIncVat').textContent = (monthlyTotal + monthlyVat).toFixed(2);
    }
    document.getElementById('kwSetupVatRow').style.display = hasVat ? '' : 'none';
    document.getElementById('kwSetupIncVatRow').style.display = hasVat ? '' : 'none';
    document.getElementById('kwMonthlyVatRow').style.display = hasVat ? '' : 'none';
    document.getElementById('kwMonthlyIncVatRow').style.display = hasVat ? '' : 'none';
    document.getElementById('keywordSelectionSummary').style.display = 'flex';
    document.getElementById('keywordPurchaseBtn').disabled = false;
}

function renderTakenKeywords() {
    var tbody = document.getElementById('takenKeywordsBody');
    var searchTerm = document.getElementById('takenKeywordSearch').value.toLowerCase();
    
    var filtered = takenKeywords.filter(function(kw) {
        return !searchTerm || kw.toLowerCase().includes(searchTerm);
    });
    
    var html = '';
    filtered.forEach(function(kw) {
        html += '<tr><td>' + kw + '</td><td><span class="badge badge-pastel-danger rounded-pill">Taken</span></td></tr>';
    });
    tbody.innerHTML = html || '<tr><td colspan="2" class="text-muted text-center">No taken keywords found</td></tr>';
}

function showVmnPurchaseModal() {
    if (selectedVmnIds.length === 0) return;
    var selectedVmns = selectedVmnIds.map(function(id) {
        return vmnPoolData.find(function(v) { return v.id === id; });
    }).filter(Boolean);
    var setupTotal = 0; var monthlyTotal = 0; var listHtml = '';
    selectedVmns.forEach(function(vmn) {
        setupTotal += vmn.setupFee;
        monthlyTotal += vmn.monthlyFee;
        listHtml += '<div class="d-flex justify-content-between py-1 border-bottom"><span>' + vmn.number + '</span><span class="text-muted">£' + vmn.setupFee.toFixed(2) + ' ex VAT</span></div>';
    });
    var setupVat = setupTotal * vatRate / 100;
    var monthlyVat = monthlyTotal * vatRate / 100;
    var setupIncVat = setupTotal + setupVat;
    var hasVat = vatRate > 0;

    if (setupIncVat > accountBalance) {
        document.getElementById('insufficientBalance').textContent = accountBalance.toFixed(2);
        document.getElementById('insufficientRequired').textContent = setupIncVat.toFixed(2);
        document.getElementById('insufficientShortfall').textContent = (setupIncVat - accountBalance).toFixed(2);
        new bootstrap.Modal(document.getElementById('insufficientBalanceModal')).show();
        return;
    }
    document.getElementById('modalVmnList').innerHTML = listHtml;
    document.getElementById('modalVmnSetup').textContent = setupTotal.toFixed(2);
    document.getElementById('modalVmnSetupVat').textContent = setupVat.toFixed(2);
    document.getElementById('modalVmnSetupTotal').textContent = setupIncVat.toFixed(2);
    document.getElementById('modalVmnMonthly').textContent = monthlyTotal.toFixed(2);
    document.getElementById('modalVmnMonthlyVat').textContent = monthlyVat.toFixed(2);
    document.getElementById('modalVmnMonthlyTotal').textContent = (monthlyTotal + monthlyVat).toFixed(2);
    document.getElementById('modalVmnVatRate').textContent = vatRate.toFixed(0);
    document.getElementById('modalVmnVatRow').style.display = hasVat ? '' : 'none';
    document.getElementById('modalVmnMonthlyVatRow').style.display = hasVat ? '' : 'none';
    new bootstrap.Modal(document.getElementById('vmnPurchaseModal')).show();
}

function showKeywordPurchaseModal() {
    if (selectedKeywords.length === 0) return;
    var setupTotal = selectedKeywords.length * keywordSetupFee;
    var monthlyTotal = selectedKeywords.length * keywordMonthlyFee;
    var setupVat = setupTotal * vatRate / 100;
    var monthlyVat = monthlyTotal * vatRate / 100;
    var setupIncVat = setupTotal + setupVat;
    var hasVat = vatRate > 0;

    if (setupIncVat > accountBalance) {
        document.getElementById('insufficientBalance').textContent = accountBalance.toFixed(2);
        document.getElementById('insufficientRequired').textContent = setupIncVat.toFixed(2);
        document.getElementById('insufficientShortfall').textContent = (setupIncVat - accountBalance).toFixed(2);
        new bootstrap.Modal(document.getElementById('insufficientBalanceModal')).show();
        return;
    }
    var listHtml = '';
    selectedKeywords.forEach(function(kw) {
        listHtml += '<div class="d-flex justify-content-between py-1 border-bottom"><span>' + kw + '</span><span class="text-muted">£' + keywordSetupFee.toFixed(2) + ' ex VAT</span></div>';
    });
    document.getElementById('modalKeywordList').innerHTML = listHtml;
    document.getElementById('modalKeywordSetup').textContent = setupTotal.toFixed(2);
    document.getElementById('modalKeywordSetupVat').textContent = setupVat.toFixed(2);
    document.getElementById('modalKeywordSetupTotal').textContent = setupIncVat.toFixed(2);
    document.getElementById('modalKeywordMonthly').textContent = monthlyTotal.toFixed(2);
    document.getElementById('modalKeywordMonthlyVat').textContent = monthlyVat.toFixed(2);
    document.getElementById('modalKeywordMonthlyTotal').textContent = (monthlyTotal + monthlyVat).toFixed(2);
    document.getElementById('modalKeywordVatRate').textContent = vatRate.toFixed(0);
    document.getElementById('modalKeywordVatRow').style.display = hasVat ? '' : 'none';
    document.getElementById('modalKeywordMonthlyVatRow').style.display = hasVat ? '' : 'none';
    new bootstrap.Modal(document.getElementById('keywordPurchaseModal')).show();
}

function executeVmnPurchase() {
    var modal = bootstrap.Modal.getInstance(document.getElementById('vmnPurchaseModal'));
    var confirmBtn = document.querySelector('#vmnPurchaseModal .btn-primary');
    var originalText = confirmBtn.innerHTML;
    confirmBtn.disabled = true;
    confirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
    fetch('/api/numbers/purchase-vmn', {
        method: 'POST',
        headers: csrfHeaders(),
        body: JSON.stringify({ pool_number_ids: selectedVmnIds })
    })
    .then(function(r) { return r.json().then(function(d) { return { ok: r.ok, status: r.status, data: d }; }); })
    .then(function(res) {
        confirmBtn.disabled = false;
        confirmBtn.innerHTML = originalText;
        if (!res.ok) {
            var d = res.data;
            if (d.required != null && d.available != null) {
                modal.hide();
                document.getElementById('insufficientBalance').textContent = parseFloat(d.available).toFixed(2);
                document.getElementById('insufficientRequired').textContent = parseFloat(d.required).toFixed(2);
                document.getElementById('insufficientShortfall').textContent = (parseFloat(d.required) - parseFloat(d.available)).toFixed(2);
                new bootstrap.Modal(document.getElementById('insufficientBalanceModal')).show();
            } else {
                showErrorToast('Purchase Failed', d.error || d.message || 'An error occurred. Please try again.');
            }
            return;
        }
        modal.hide();
        var count = selectedVmnIds.length;
        selectedVmnIds = [];
        var countryFilter = document.getElementById('vmnCountryFilter') ? document.getElementById('vmnCountryFilter').value : null;
        loadVmnPool(countryFilter || null);
        var message = count === 1 ? 'Your number has been purchased successfully.' : 'Your ' + count + ' numbers have been purchased successfully.';
        document.getElementById('successMessage').textContent = message;
        new bootstrap.Modal(document.getElementById('purchaseSuccessModal')).show();
    })
    .catch(function(err) {
        confirmBtn.disabled = false;
        confirmBtn.innerHTML = originalText;
        showErrorToast('Error', 'Network error. Please try again.');
    });
}

function executeKeywordPurchase() {
    if (!sharedShortcodeId) {
        showErrorToast('Error', 'No shared shortcode is available. Please contact support.');
        return;
    }
    var modal = bootstrap.Modal.getInstance(document.getElementById('keywordPurchaseModal'));
    var confirmBtn = document.querySelector('#keywordPurchaseModal .btn-primary');
    var originalText = confirmBtn.innerHTML;
    confirmBtn.disabled = true;
    confirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
    var keywordsToProcess = selectedKeywords.slice();
    var purchasedCount = 0;
    var failedKeywords = [];
    function purchaseNext(index) {
        if (index >= keywordsToProcess.length) {
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = originalText;
            modal.hide();
            if (purchasedCount > 0) {
                keywordsToProcess.slice(0, purchasedCount).forEach(function(kw) { takenKeywords.push(kw); });
                selectedKeywords = failedKeywords.slice();
                renderSelectedKeywords();
                renderTakenKeywords();
                var message = purchasedCount === 1 ? 'Your keyword has been purchased successfully.' : 'Your ' + purchasedCount + ' keywords have been purchased successfully.';
                if (failedKeywords.length > 0) message += ' ' + failedKeywords.length + ' keyword(s) could not be purchased.';
                document.getElementById('successMessage').textContent = message;
                new bootstrap.Modal(document.getElementById('purchaseSuccessModal')).show();
            } else {
                showErrorToast('Purchase Failed', 'No keywords could be purchased. Please try again.');
            }
            return;
        }
        var keyword = keywordsToProcess[index];
        fetch('/api/numbers/purchase-keyword', {
            method: 'POST',
            headers: csrfHeaders(),
            body: JSON.stringify({ shortcode_number_id: sharedShortcodeId, keyword: keyword })
        })
        .then(function(r) { return r.json().then(function(d) { return { ok: r.ok, data: d }; }); })
        .then(function(res) {
            if (res.ok && res.data.success !== false) purchasedCount++;
            else failedKeywords.push(keyword);
            purchaseNext(index + 1);
        })
        .catch(function() { failedKeywords.push(keyword); purchaseNext(index + 1); });
    }
    purchaseNext(0);
}

function showSuccessToast(title, message) {
    var container = document.getElementById('toastContainer') || createToastContainer();
    container.innerHTML = '<div class="toast align-items-center text-white bg-success border-0" role="alert"><div class="d-flex"><div class="toast-body"><strong>' + title + '</strong><br>' + message + '</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div></div>';
    new bootstrap.Toast(container.querySelector('.toast')).show();
}

function showErrorToast(title, message) {
    var container = document.getElementById('toastContainer') || createToastContainer();
    container.innerHTML = '<div class="toast align-items-center text-white bg-danger border-0" role="alert"><div class="d-flex"><div class="toast-body"><strong>' + title + '</strong><br>' + message + '</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div></div>';
    new bootstrap.Toast(container.querySelector('.toast')).show();
}

function createToastContainer() {
    var container = document.createElement('div');
    container.id = 'toastContainer';
    container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
    document.body.appendChild(container);
    return container;
}
</script>
@endpush
