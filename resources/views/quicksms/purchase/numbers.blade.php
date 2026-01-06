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
    height: 100%;
    overflow: hidden;
    cursor: pointer;
}
.product-card:hover {
    box-shadow: 0 8px 24px rgba(111, 66, 193, 0.25);
    transform: translateY(-2px);
}
.product-card.selected {
    box-shadow: 0 0 0 3px rgba(111, 66, 193, 0.4), 0 8px 24px rgba(111, 66, 193, 0.25);
}
.product-card .product-header {
    padding: 1.5rem;
    text-align: center;
    position: relative;
    z-index: 1;
}
.product-card .product-header h4 {
    color: #fff;
    font-weight: 700;
    font-size: 1.25rem;
    margin-bottom: 0.5rem;
}
.product-card .product-header p {
    color: rgba(255, 255, 255, 0.85);
    font-size: 0.875rem;
    margin-bottom: 0;
}
.product-card .product-body {
    padding: 1.25rem 1.5rem;
    background: #fff;
}
.pricing-badge {
    display: inline-flex;
    align-items: center;
    background: rgba(255, 255, 255, 0.25);
    color: #fff;
    padding: 0.375rem 0.75rem;
    border-radius: 1rem;
    font-size: 0.75rem;
    font-weight: 600;
    margin: 0.25rem;
}
.pricing-badge i {
    margin-right: 0.375rem;
}
.product-body .price-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0;
    border-bottom: 1px solid #f0f0f0;
}
.product-body .price-row:last-child {
    border-bottom: none;
}
.product-body .price-label {
    font-size: 0.875rem;
    color: #6c757d;
}
.product-body .price-value {
    font-weight: 600;
    color: var(--primary);
}
.product-body .price-value.contact-sales {
    color: #6c757d;
    font-size: 0.8rem;
    font-style: italic;
}
.product-footer {
    padding: 1rem 1.5rem 1.5rem;
    background: #fff;
    border-bottom-left-radius: 0.75rem;
    border-bottom-right-radius: 0.75rem;
}
.product-footer .btn-select {
    background: var(--primary);
    border-color: var(--primary);
    color: #fff;
    width: 100%;
    padding: 0.625rem 1.5rem;
    font-weight: 600;
}
.product-footer .btn-select:hover {
    background: var(--primary-hover);
    border-color: var(--primary-hover);
}
.product-card.selected .btn-select {
    background: #1cbb8c;
    border-color: #1cbb8c;
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
    font-family: 'Monaco', 'Consolas', monospace;
    font-weight: 600;
    color: #2c2c2c;
}
.country-cell {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.country-flag {
    font-size: 1.25rem;
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
.sub-account-selector {
    margin-bottom: 1rem;
}
.sub-account-selector label {
    font-weight: 600;
    font-size: 0.875rem;
    color: #495057;
    margin-bottom: 0.375rem;
    display: block;
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
</style>
@endpush

@section('content')
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

        <div id="pricingLoadingIndicator" class="pricing-loading" style="display: none;">
            <div class="spinner-border spinner-border-sm text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <span>Fetching live pricing from HubSpot...</span>
        </div>

        <div id="pricingWarning" class="pricing-warning" style="display: none;">
            <i class="fas fa-exclamation-triangle"></i>
            <span id="pricingWarningMessage">Unable to fetch live pricing. Using cached values.</span>
            <button type="button" class="btn btn-sm btn-outline-warning ms-auto" onclick="fetchNumbersPricing()">
                <i class="fas fa-sync-alt me-1"></i>Retry
            </button>
        </div>

        <div class="row g-4 mb-4" id="productCards">
            <div class="col-md-4">
                <div class="card product-card tryal-gradient" data-product="vmn" onclick="selectProduct('vmn')">
                    <div class="product-header">
                        <div class="mb-2">
                            <span class="pricing-badge"><i class="fas fa-mobile-alt"></i>Long Code</span>
                        </div>
                        <h4>UK Virtual Mobile Number</h4>
                        <p>Standard UK mobile (07xxx) for two-way SMS and RCS messaging</p>
                    </div>
                    <div class="product-body">
                        <div class="price-row">
                            <span class="price-label">Setup Cost</span>
                            <span class="price-value" id="vmnSetupPrice">£10.00</span>
                        </div>
                        <div class="price-row">
                            <span class="price-label">Monthly Cost</span>
                            <span class="price-value" id="vmnMonthlyPrice">£8.00</span>
                        </div>
                    </div>
                    <div class="product-footer">
                        <button class="btn btn-select">Select</button>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card product-card tryal-gradient" data-product="shared" onclick="selectProduct('shared')">
                    <div class="product-header">
                        <div class="mb-2">
                            <span class="pricing-badge"><i class="fas fa-share-alt"></i>Shared</span>
                        </div>
                        <h4>UK Shared Short Code</h4>
                        <p>Share shortcode 82228 with custom keywords for inbound messaging</p>
                    </div>
                    <div class="product-body">
                        <div class="price-row">
                            <span class="price-label">Setup Cost (per keyword)</span>
                            <span class="price-value" id="keywordSetupPrice">£25.00</span>
                        </div>
                        <div class="price-row">
                            <span class="price-label">Monthly Cost (per keyword)</span>
                            <span class="price-value" id="keywordMonthlyPrice">£50.00</span>
                        </div>
                    </div>
                    <div class="product-footer">
                        <button class="btn btn-select">Select</button>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card product-card tryal-gradient" data-product="dedicated" onclick="selectProduct('dedicated')">
                    <div class="product-header">
                        <div class="mb-2">
                            <span class="pricing-badge"><i class="fas fa-star"></i>Dedicated</span>
                        </div>
                        <h4>UK Dedicated Short Code</h4>
                        <p>Exclusive shortcode for high-volume enterprise campaigns</p>
                    </div>
                    <div class="product-body">
                        <div class="price-row">
                            <span class="price-label">Setup Cost</span>
                            <span class="price-value contact-sales">Contact sales (price on request)</span>
                        </div>
                        <div class="price-row">
                            <span class="price-label">Monthly Cost</span>
                            <span class="price-value contact-sales">Contact sales (price on request)</span>
                        </div>
                    </div>
                    <div class="product-footer">
                        <button class="btn btn-select">Select</button>
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

            <div class="sub-account-selector">
                <label>Assign to Sub-Account (optional)</label>
                <select class="form-select" id="vmnSubAccountSelect" style="max-width: 300px;">
                    <option value="">Choose later...</option>
                </select>
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
                        <span class="cost-label">Setup Total</span>
                        <span class="cost-value">£<span id="vmnSetupTotal">0.00</span></span>
                    </div>
                    <div class="cost-item">
                        <span class="cost-label">Monthly Total</span>
                        <span class="cost-value">£<span id="vmnMonthlyTotal">0.00</span>/mo</span>
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

            <div class="alert alert-info mb-3">
                <i class="fas fa-info-circle me-2"></i>
                Shared shortcode: <strong>82228</strong> — Add keywords to receive inbound messages on this shared number.
            </div>

            <div class="sub-account-selector">
                <label>Assign to Sub-Account (optional)</label>
                <select class="form-select" id="keywordSubAccountSelect" style="max-width: 300px;">
                    <option value="">Choose later...</option>
                </select>
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
                        <span class="cost-label">Setup Total</span>
                        <span class="cost-value">£<span id="keywordSetupTotal">0.00</span></span>
                    </div>
                    <div class="cost-item">
                        <span class="cost-label">Monthly Total</span>
                        <span class="cost-value">£<span id="keywordMonthlyTotal">0.00</span>/mo</span>
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
                
                <div class="row mb-3">
                    <div class="col-6">
                        <div class="border rounded p-3 text-center">
                            <div class="text-muted small">Setup Cost (due now)</div>
                            <div class="fs-4 fw-bold text-primary">£<span id="modalVmnSetup">0.00</span></div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border rounded p-3 text-center">
                            <div class="text-muted small">Monthly Cost</div>
                            <div class="fs-4 fw-bold text-primary">£<span id="modalVmnMonthly">0.00</span>/mo</div>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Assign to Sub-Account</label>
                    <select class="form-select" id="modalVmnSubAccount">
                        <option value="">Choose later...</option>
                    </select>
                    <div class="form-text">You can assign these numbers to a sub-account now or later.</div>
                </div>

                <div class="alert alert-info small mb-0">
                    <i class="fas fa-info-circle me-1"></i>
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
                
                <div class="row mb-3">
                    <div class="col-6">
                        <div class="border rounded p-3 text-center">
                            <div class="text-muted small">Setup Cost (due now)</div>
                            <div class="fs-4 fw-bold text-primary">£<span id="modalKeywordSetup">0.00</span></div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border rounded p-3 text-center">
                            <div class="text-muted small">Monthly Cost</div>
                            <div class="fs-4 fw-bold text-primary">£<span id="modalKeywordMonthly">0.00</span>/mo</div>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Assign to Sub-Account</label>
                    <select class="form-select" id="modalKeywordSubAccount">
                        <option value="">Choose later...</option>
                    </select>
                    <div class="form-text">You can assign these keywords to a sub-account now or later.</div>
                </div>

                <div class="alert alert-info small mb-0">
                    <i class="fas fa-info-circle me-1"></i>
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
@endsection

@push('scripts')
<script>
var currentUserRole = 'admin';
var allowedRoles = ['admin', 'finance', 'messaging_manager'];
var accountBalance = 45.00;
var selectedProduct = null;

var subAccountsMockData = [
    { id: 'sa_001', name: 'Main Account' },
    { id: 'sa_002', name: 'Marketing Team' },
    { id: 'sa_003', name: 'Sales Department' },
    { id: 'sa_004', name: 'Customer Support' },
    { id: 'sa_005', name: 'Operations' }
];

var vmnMockData = [
    { id: 1, number: '+447700900001', country: 'GB', countryName: 'United Kingdom', flag: '🇬🇧', setupFee: 10.00, monthlyFee: 8.00 },
    { id: 2, number: '+447700900002', country: 'GB', countryName: 'United Kingdom', flag: '🇬🇧', setupFee: 10.00, monthlyFee: 8.00 },
    { id: 3, number: '+447700900004', country: 'GB', countryName: 'United Kingdom', flag: '🇬🇧', setupFee: 10.00, monthlyFee: 8.00 },
    { id: 4, number: '+447700900005', country: 'GB', countryName: 'United Kingdom', flag: '🇬🇧', setupFee: 10.00, monthlyFee: 8.00 },
    { id: 5, number: '+447700900100', country: 'GB', countryName: 'United Kingdom', flag: '🇬🇧', setupFee: 10.00, monthlyFee: 8.00 },
    { id: 6, number: '+447700900101', country: 'GB', countryName: 'United Kingdom', flag: '🇬🇧', setupFee: 10.00, monthlyFee: 8.00 },
    { id: 7, number: '+447700900102', country: 'GB', countryName: 'United Kingdom', flag: '🇬🇧', setupFee: 10.00, monthlyFee: 8.00 },
    { id: 8, number: '+447700900103', country: 'GB', countryName: 'United Kingdom', flag: '🇬🇧', setupFee: 10.00, monthlyFee: 8.00 }
];

var takenKeywords = ['SALE', 'FREE', 'VOTE', 'STOP', 'ALERT', 'VIP'];
var selectedVmnIds = [];
var selectedKeywords = [];
var vmnSortColumn = 'number';
var vmnSortDirection = 'asc';
var vmnSearchTerm = '';

var numbersPricing = null;
var keywordSetupFee = 25.00;
var keywordMonthlyFee = 50.00;

document.addEventListener('DOMContentLoaded', function() {
    checkAccess();
    populateSubAccountDropdowns();
    fetchNumbersPricing();
    setupEventListeners();
});

function checkAccess() {
    var hasAccess = allowedRoles.includes(currentUserRole);
    document.getElementById('accessDeniedView').style.display = hasAccess ? 'none' : 'block';
    document.getElementById('purchaseContent').style.display = hasAccess ? 'block' : 'none';
}

function populateSubAccountDropdowns() {
    var selects = ['vmnSubAccountSelect', 'keywordSubAccountSelect', 'modalVmnSubAccount', 'modalKeywordSubAccount'];
    selects.forEach(function(selectId) {
        var select = document.getElementById(selectId);
        if (!select) return;
        subAccountsMockData.forEach(function(sa) {
            var option = document.createElement('option');
            option.value = sa.id;
            option.textContent = sa.name;
            select.appendChild(option);
        });
    });
}

function fetchNumbersPricing() {
    document.getElementById('pricingLoadingIndicator').style.display = 'flex';
    
    fetch('/api/purchase/numbers/pricing?currency=GBP')
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success && data.pricing) {
                numbersPricing = data.pricing;
                applyPricingToUI();
                
                if (data.is_mock) {
                    showPricingWarning('Using cached pricing data');
                }
            } else {
                throw new Error('Failed to fetch pricing');
            }
        })
        .catch(function(error) {
            console.error('Pricing error:', error);
            showPricingWarning('Unable to fetch live pricing. Using cached values.');
        })
        .finally(function() {
            document.getElementById('pricingLoadingIndicator').style.display = 'none';
            renderVmnTable();
            renderTakenKeywords();
        });
}

function applyPricingToUI() {
    if (!numbersPricing) return;
    
    if (numbersPricing.vmn && numbersPricing.vmn.uk_longcode) {
        document.getElementById('vmnSetupPrice').textContent = '£' + numbersPricing.vmn.uk_longcode.setup_fee.toFixed(2);
        document.getElementById('vmnMonthlyPrice').textContent = '£' + numbersPricing.vmn.uk_longcode.monthly_fee.toFixed(2);
        
        vmnMockData.forEach(function(vmn) {
            vmn.setupFee = numbersPricing.vmn.uk_longcode.setup_fee;
            vmn.monthlyFee = numbersPricing.vmn.uk_longcode.monthly_fee;
        });
    }
    
    if (numbersPricing.keyword) {
        keywordSetupFee = numbersPricing.keyword.setup_fee;
        keywordMonthlyFee = numbersPricing.keyword.monthly_fee;
        document.getElementById('keywordSetupPrice').textContent = '£' + keywordSetupFee.toFixed(2);
        document.getElementById('keywordMonthlyPrice').textContent = '£' + keywordMonthlyFee.toFixed(2);
    }
}

function showPricingWarning(message) {
    document.getElementById('pricingWarningMessage').textContent = message;
    document.getElementById('pricingWarning').style.display = 'flex';
}

function setupEventListeners() {
    document.getElementById('vmnSearchInput').addEventListener('input', function(e) {
        vmnSearchTerm = e.target.value.toLowerCase();
        renderVmnTable();
    });
    
    document.getElementById('keywordInput').addEventListener('input', validateKeywordInput);
    document.getElementById('keywordInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && !document.getElementById('addKeywordBtn').disabled) {
            addKeyword();
        }
    });
    
    document.getElementById('takenKeywordSearch').addEventListener('input', renderTakenKeywords);
}

function selectProduct(product) {
    document.querySelectorAll('.product-card').forEach(function(card) {
        card.classList.remove('selected');
    });
    document.querySelector('[data-product="' + product + '"]').classList.add('selected');
    
    document.querySelectorAll('.selection-panel').forEach(function(panel) {
        panel.classList.remove('active');
    });
    
    selectedProduct = product;
    document.getElementById(product + 'Panel').classList.add('active');
}

function renderVmnTable() {
    var tbody = document.getElementById('vmnTableBody');
    var filtered = vmnMockData.filter(function(vmn) {
        if (vmnSearchTerm && !vmn.number.toLowerCase().includes(vmnSearchTerm)) {
            return false;
        }
        return true;
    });
    
    filtered.sort(function(a, b) {
        var valA = a[vmnSortColumn];
        var valB = b[vmnSortColumn];
        if (typeof valA === 'string') valA = valA.toLowerCase();
        if (typeof valB === 'string') valB = valB.toLowerCase();
        if (valA < valB) return vmnSortDirection === 'asc' ? -1 : 1;
        if (valA > valB) return vmnSortDirection === 'asc' ? 1 : -1;
        return 0;
    });
    
    if (filtered.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5"><div class="empty-state"><i class="fas fa-search"></i><h5>No numbers found</h5><p>Try adjusting your search.</p></div></td></tr>';
        return;
    }
    
    var html = '';
    filtered.forEach(function(vmn) {
        var isSelected = selectedVmnIds.includes(vmn.id);
        html += '<tr class="' + (isSelected ? 'selected' : '') + '">';
        html += '<td><input type="checkbox" class="form-check-input" ' + (isSelected ? 'checked' : '') + ' onchange="toggleVmnSelect(' + vmn.id + ')"></td>';
        html += '<td><span class="vmn-number">' + vmn.number + '</span></td>';
        html += '<td><div class="country-cell"><span class="country-flag">' + vmn.flag + '</span>' + vmn.countryName + '</div></td>';
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
    if (idx === -1) {
        selectedVmnIds.push(id);
    } else {
        selectedVmnIds.splice(idx, 1);
    }
    renderVmnTable();
}

function toggleVmnSelectAll() {
    var allChecked = document.getElementById('vmnSelectAll').checked;
    if (allChecked) {
        selectedVmnIds = vmnMockData.map(function(v) { return v.id; });
    } else {
        selectedVmnIds = [];
    }
    renderVmnTable();
}

function updateVmnSelection() {
    var count = selectedVmnIds.length;
    var setupTotal = 0;
    var monthlyTotal = 0;
    
    selectedVmnIds.forEach(function(id) {
        var vmn = vmnMockData.find(function(v) { return v.id === id; });
        if (vmn) {
            setupTotal += vmn.setupFee;
            monthlyTotal += vmn.monthlyFee;
        }
    });
    
    document.getElementById('vmnPurchaseBtn').disabled = count === 0;
    document.getElementById('vmnSelectedCount').textContent = count;
    document.getElementById('vmnSetupTotal').textContent = setupTotal.toFixed(2);
    document.getElementById('vmnMonthlyTotal').textContent = monthlyTotal.toFixed(2);
    document.getElementById('vmnSelectionSummary').style.display = count > 0 ? 'flex' : 'none';
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
    
    document.getElementById('keywordSelectedCount').textContent = count;
    document.getElementById('keywordSetupTotal').textContent = setupTotal.toFixed(2);
    document.getElementById('keywordMonthlyTotal').textContent = monthlyTotal.toFixed(2);
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
        html += '<tr><td>' + kw + '</td><td><span class="badge bg-secondary">Taken</span></td></tr>';
    });
    tbody.innerHTML = html || '<tr><td colspan="2" class="text-muted text-center">No taken keywords found</td></tr>';
}

function showVmnPurchaseModal() {
    if (selectedVmnIds.length === 0) return;
    
    var selectedVmns = selectedVmnIds.map(function(id) {
        return vmnMockData.find(function(v) { return v.id === id; });
    });
    
    var setupTotal = 0;
    var monthlyTotal = 0;
    var listHtml = '';
    
    selectedVmns.forEach(function(vmn) {
        setupTotal += vmn.setupFee;
        monthlyTotal += vmn.monthlyFee;
        listHtml += '<div class="d-flex justify-content-between py-1 border-bottom"><span>' + vmn.number + '</span><span class="text-muted">£' + vmn.setupFee.toFixed(2) + '</span></div>';
    });
    
    if (setupTotal > accountBalance) {
        document.getElementById('insufficientBalance').textContent = accountBalance.toFixed(2);
        document.getElementById('insufficientRequired').textContent = setupTotal.toFixed(2);
        document.getElementById('insufficientShortfall').textContent = (setupTotal - accountBalance).toFixed(2);
        new bootstrap.Modal(document.getElementById('insufficientBalanceModal')).show();
        return;
    }
    
    document.getElementById('modalVmnList').innerHTML = listHtml;
    document.getElementById('modalVmnSetup').textContent = setupTotal.toFixed(2);
    document.getElementById('modalVmnMonthly').textContent = monthlyTotal.toFixed(2);
    
    var subAccountValue = document.getElementById('vmnSubAccountSelect').value;
    document.getElementById('modalVmnSubAccount').value = subAccountValue;
    
    new bootstrap.Modal(document.getElementById('vmnPurchaseModal')).show();
}

function showKeywordPurchaseModal() {
    if (selectedKeywords.length === 0) return;
    
    var setupTotal = selectedKeywords.length * keywordSetupFee;
    var monthlyTotal = selectedKeywords.length * keywordMonthlyFee;
    
    if (setupTotal > accountBalance) {
        document.getElementById('insufficientBalance').textContent = accountBalance.toFixed(2);
        document.getElementById('insufficientRequired').textContent = setupTotal.toFixed(2);
        document.getElementById('insufficientShortfall').textContent = (setupTotal - accountBalance).toFixed(2);
        new bootstrap.Modal(document.getElementById('insufficientBalanceModal')).show();
        return;
    }
    
    var listHtml = '';
    selectedKeywords.forEach(function(kw) {
        listHtml += '<div class="d-flex justify-content-between py-1 border-bottom"><span>' + kw + '</span><span class="text-muted">£' + keywordSetupFee.toFixed(2) + '</span></div>';
    });
    
    document.getElementById('modalKeywordList').innerHTML = listHtml;
    document.getElementById('modalKeywordSetup').textContent = setupTotal.toFixed(2);
    document.getElementById('modalKeywordMonthly').textContent = monthlyTotal.toFixed(2);
    
    var subAccountValue = document.getElementById('keywordSubAccountSelect').value;
    document.getElementById('modalKeywordSubAccount').value = subAccountValue;
    
    new bootstrap.Modal(document.getElementById('keywordPurchaseModal')).show();
}

function executeVmnPurchase() {
    var modal = bootstrap.Modal.getInstance(document.getElementById('vmnPurchaseModal'));
    var confirmBtn = document.querySelector('#vmnPurchaseModal .btn-primary');
    var originalText = confirmBtn.innerHTML;
    confirmBtn.disabled = true;
    confirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
    
    var subAccountId = document.getElementById('modalVmnSubAccount').value;
    var subAccount = subAccountsMockData.find(function(sa) { return sa.id === subAccountId; });
    
    var items = selectedVmnIds.map(function(id) {
        var vmn = vmnMockData.find(function(v) { return v.id === id; });
        return { type: 'vmn', identifier: vmn.number, country_code: vmn.country };
    });
    
    fetch('/api/purchase/numbers/lock', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: JSON.stringify({ items: items, purchase_type: 'vmn' })
    })
    .then(function(r) { return r.json(); })
    .then(function(lockResult) {
        if (!lockResult.success) throw new Error(lockResult.message || 'Failed to lock items');
        return fetch('/api/purchase/numbers/purchase', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: JSON.stringify({
                session_id: lockResult.session_id,
                sub_account_id: subAccountId || 'unassigned',
                sub_account_name: subAccount ? subAccount.name : 'Unassigned',
                purchase_type: 'vmn',
                items: items
            })
        });
    })
    .then(function(r) { return r.json(); })
    .then(function(result) {
        if (!result.success) throw new Error(result.message || 'Purchase failed');
        
        modal.hide();
        accountBalance = result.balance_after;
        
        selectedVmnIds.forEach(function(id) {
            var idx = vmnMockData.findIndex(function(v) { return v.id === id; });
            if (idx !== -1) vmnMockData.splice(idx, 1);
        });
        
        selectedVmnIds = [];
        renderVmnTable();
        
        showSuccessToast('Purchase Complete', result.items_purchased + ' number(s) purchased. Ref: ' + result.transaction_reference);
    })
    .catch(function(error) {
        showErrorToast('Purchase Failed', error.message);
    })
    .finally(function() {
        confirmBtn.disabled = false;
        confirmBtn.innerHTML = originalText;
    });
}

function executeKeywordPurchase() {
    var modal = bootstrap.Modal.getInstance(document.getElementById('keywordPurchaseModal'));
    var confirmBtn = document.querySelector('#keywordPurchaseModal .btn-primary');
    var originalText = confirmBtn.innerHTML;
    confirmBtn.disabled = true;
    confirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
    
    var subAccountId = document.getElementById('modalKeywordSubAccount').value;
    var subAccount = subAccountsMockData.find(function(sa) { return sa.id === subAccountId; });
    
    var items = selectedKeywords.map(function(kw) {
        return { type: 'keyword', identifier: kw };
    });
    
    fetch('/api/purchase/numbers/lock', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: JSON.stringify({ items: items, purchase_type: 'keyword' })
    })
    .then(function(r) { return r.json(); })
    .then(function(lockResult) {
        if (!lockResult.success) throw new Error(lockResult.message || 'Failed to lock keywords');
        return fetch('/api/purchase/numbers/purchase', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: JSON.stringify({
                session_id: lockResult.session_id,
                sub_account_id: subAccountId || 'unassigned',
                sub_account_name: subAccount ? subAccount.name : 'Unassigned',
                purchase_type: 'keyword',
                items: items
            })
        });
    })
    .then(function(r) { return r.json(); })
    .then(function(result) {
        if (!result.success) throw new Error(result.message || 'Purchase failed');
        
        modal.hide();
        accountBalance = result.balance_after;
        
        selectedKeywords.forEach(function(kw) {
            takenKeywords.push(kw);
        });
        
        selectedKeywords = [];
        renderSelectedKeywords();
        renderTakenKeywords();
        
        showSuccessToast('Purchase Complete', result.items_purchased + ' keyword(s) purchased. Ref: ' + result.transaction_reference);
    })
    .catch(function(error) {
        showErrorToast('Purchase Failed', error.message);
    })
    .finally(function() {
        confirmBtn.disabled = false;
        confirmBtn.innerHTML = originalText;
    });
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
