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
.page-header {
    margin-bottom: 2rem;
}
.page-header h2 {
    margin-bottom: 0.25rem;
    color: #2c2c2c;
}
.page-header p {
    color: #6c757d;
    margin-bottom: 0;
}
.section-header {
    display: flex;
    align-items: center;
    margin-bottom: 1.5rem;
    padding-bottom: 0.75rem;
    border-bottom: 2px solid #f0ebf8;
}
.section-header i {
    font-size: 1.5rem;
    color: #6f42c1;
    margin-right: 0.75rem;
}
.section-header h4 {
    margin: 0;
    font-weight: 600;
    color: #2c2c2c;
}
.section-header .badge {
    margin-left: 0.75rem;
    font-size: 0.7rem;
    font-weight: 500;
}
.purchase-section {
    margin-bottom: 2.5rem;
}
.purchase-card {
    border: none;
    border-radius: 0.75rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    transition: all 0.2s ease;
    height: 100%;
}
.purchase-card:hover {
    box-shadow: 0 4px 16px rgba(111, 66, 193, 0.15);
    transform: translateY(-2px);
}
.purchase-card .card-body {
    padding: 1.5rem;
}
.number-type-card {
    border-left: 4px solid #6f42c1;
}
.number-type-card.shortcode {
    border-left-color: #20c997;
}
.number-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    margin-bottom: 1rem;
}
.number-icon.vmn {
    background: rgba(111, 66, 193, 0.12);
    color: #6f42c1;
}
.number-icon.shortcode {
    background: rgba(32, 201, 151, 0.12);
    color: #20c997;
}
.number-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #2c2c2c;
    margin-bottom: 0.5rem;
}
.number-description {
    font-size: 0.875rem;
    color: #6c757d;
    margin-bottom: 1rem;
    line-height: 1.5;
}
.feature-list {
    list-style: none;
    padding: 0;
    margin: 0 0 1.25rem 0;
}
.feature-list li {
    display: flex;
    align-items: flex-start;
    padding: 0.375rem 0;
    font-size: 0.8125rem;
    color: #495057;
}
.feature-list li i {
    color: #28a745;
    margin-right: 0.5rem;
    margin-top: 0.125rem;
    flex-shrink: 0;
}
.price-indicator {
    display: flex;
    align-items: baseline;
    margin-bottom: 1rem;
}
.price-label {
    font-size: 0.75rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-right: 0.5rem;
}
.price-value {
    font-size: 1.25rem;
    font-weight: 700;
    color: #2c2c2c;
}
.price-period {
    font-size: 0.8125rem;
    color: #6c757d;
    margin-left: 0.25rem;
}
.section-intro {
    background: #f8f9fa;
    border-radius: 0.5rem;
    padding: 1rem 1.25rem;
    margin-bottom: 1.5rem;
}
.section-intro p {
    margin: 0;
    font-size: 0.875rem;
    color: #495057;
}
.section-intro i {
    color: #6f42c1;
    margin-right: 0.5rem;
}
.availability-note {
    display: flex;
    align-items: center;
    padding: 0.625rem 0.875rem;
    background: #fff3cd;
    border-radius: 0.375rem;
    font-size: 0.75rem;
    color: #856404;
    margin-top: auto;
}
.availability-note i {
    margin-right: 0.5rem;
    flex-shrink: 0;
}
.coming-soon-badge {
    position: absolute;
    top: 1rem;
    right: 1rem;
    background: linear-gradient(135deg, #6f42c1, #8b5cf6);
    color: #fff;
    font-size: 0.65rem;
    font-weight: 600;
    padding: 0.25rem 0.625rem;
    border-radius: 1rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.empty-state-inline {
    text-align: center;
    padding: 3rem 2rem;
    background: #f8f9fa;
    border-radius: 0.75rem;
    border: 2px dashed #dee2e6;
}
.empty-state-inline i {
    font-size: 2.5rem;
    color: #adb5bd;
    margin-bottom: 1rem;
}
.empty-state-inline h5 {
    color: #495057;
    margin-bottom: 0.5rem;
}
.empty-state-inline p {
    color: #6c757d;
    font-size: 0.875rem;
    margin-bottom: 0;
}
.vmn-table-card {
    margin-top: 2rem;
}
.vmn-table-card .card-header {
    background: #fff;
    border-bottom: 1px solid #f0ebf8;
    padding: 1rem 1.25rem;
}
.vmn-table-card .card-header h5 {
    margin: 0;
    font-weight: 600;
    font-size: 1rem;
    color: #2c2c2c;
}
.vmn-search-box {
    max-width: 300px;
}
.vmn-table {
    margin-bottom: 0;
}
.vmn-table th {
    background: #f8f9fa;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #6c757d;
    border-bottom: 2px solid #e9ecef;
    padding: 0.75rem 1rem;
    white-space: nowrap;
    cursor: pointer;
    user-select: none;
}
.vmn-table th:hover {
    background: #f0ebf8;
}
.vmn-table th.sortable i {
    margin-left: 0.5rem;
    opacity: 0.5;
}
.vmn-table th.sorted-asc i.fa-sort-up,
.vmn-table th.sorted-desc i.fa-sort-down {
    opacity: 1;
    color: #6f42c1;
}
.vmn-table td {
    padding: 0.875rem 1rem;
    vertical-align: middle;
    font-size: 0.875rem;
    border-bottom: 1px solid #f0f0f0;
}
.vmn-table tbody tr:hover {
    background: rgba(111, 66, 193, 0.04);
}
.vmn-table tbody tr.selected {
    background: rgba(111, 66, 193, 0.08);
}
.vmn-table tbody tr.row-reserved {
    opacity: 0.6;
}
.vmn-number {
    font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
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
.country-code {
    font-size: 0.75rem;
    color: #6c757d;
}
.fee-cell {
    font-weight: 500;
}
.status-available {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.625rem;
    background: #d4edda;
    color: #155724;
    border-radius: 1rem;
    font-size: 0.75rem;
    font-weight: 500;
}
.status-reserved {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.625rem;
    background: #e2e3e5;
    color: #6c757d;
    border-radius: 1rem;
    font-size: 0.75rem;
    font-weight: 500;
}
.vmn-select-checkbox {
    width: 1.125rem;
    height: 1.125rem;
    cursor: pointer;
}
.vmn-select-checkbox:disabled {
    cursor: not-allowed;
    opacity: 0.5;
}
.vmn-table-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.25rem;
    background: #f8f9fa;
    border-top: 1px solid #e9ecef;
}
.selection-summary {
    font-size: 0.875rem;
    color: #495057;
}
.selection-summary strong {
    color: #6f42c1;
}
.vmn-empty-state {
    text-align: center;
    padding: 3rem;
    color: #6c757d;
}
.vmn-empty-state i {
    font-size: 2.5rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}
.subaccount-selector-card {
    background: linear-gradient(135deg, #f8f6fc 0%, #f0ebf8 100%);
    border: 1px solid #e0d6f2;
    border-radius: 0.5rem;
    margin-bottom: 1.5rem;
}
.subaccount-selector-card .card-body {
    padding: 1.25rem;
}
.subaccount-selector-card label {
    font-weight: 600;
    color: #495057;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.subaccount-selector-card label .required-badge {
    background: #dc3545;
    color: #fff;
    font-size: 0.625rem;
    padding: 0.125rem 0.375rem;
    border-radius: 0.25rem;
    text-transform: uppercase;
    font-weight: 700;
}
.subaccount-selector-card .form-select {
    max-width: 400px;
}
.subaccount-selector-card .help-text {
    font-size: 0.8125rem;
    color: #6c757d;
    margin-top: 0.5rem;
}
.purchase-confirmation-modal .modal-header {
    background: linear-gradient(135deg, #6f42c1 0%, #8b5cf6 100%);
    color: #fff;
    border-bottom: none;
}
.purchase-confirmation-modal .modal-header .btn-close {
    filter: invert(1);
}
.purchase-confirmation-modal .confirmation-summary {
    background: #f8f9fa;
    border-radius: 0.5rem;
    padding: 1.25rem;
    margin-bottom: 1rem;
}
.purchase-confirmation-modal .summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0;
    border-bottom: 1px solid #e9ecef;
}
.purchase-confirmation-modal .summary-row:last-child {
    border-bottom: none;
}
.purchase-confirmation-modal .summary-row.total {
    font-weight: 600;
    font-size: 1.1rem;
    padding-top: 0.75rem;
    border-top: 2px solid #6f42c1;
    margin-top: 0.5rem;
}
.purchase-confirmation-modal .billing-info {
    background: #fff3cd;
    border: 1px solid #ffc107;
    border-radius: 0.5rem;
    padding: 1rem;
    margin-bottom: 1rem;
}
.purchase-confirmation-modal .billing-info i {
    color: #856404;
}
.purchase-confirmation-modal .numbers-list {
    max-height: 150px;
    overflow-y: auto;
    font-family: 'SFMono-Regular', Consolas, monospace;
    font-size: 0.875rem;
    background: #f8f9fa;
    padding: 0.75rem;
    border-radius: 0.375rem;
    margin-bottom: 1rem;
}
.shared-shortcode-display {
    background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
    border: 2px solid #28a745;
    border-radius: 0.75rem;
    padding: 1.5rem;
    text-align: center;
    margin-bottom: 1.5rem;
}
.shared-shortcode-display .shortcode-label {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #155724;
    font-weight: 600;
    margin-bottom: 0.5rem;
}
.shared-shortcode-display .shortcode-number {
    font-size: 2.5rem;
    font-weight: 700;
    font-family: 'SFMono-Regular', Consolas, monospace;
    color: #155724;
    letter-spacing: 2px;
}
.shared-shortcode-display .shortcode-note {
    font-size: 0.8125rem;
    color: #155724;
    margin-top: 0.5rem;
}
.keyword-table-card {
    margin-top: 1rem;
}
.keyword-table-card .card-header {
    background: #fff;
    border-bottom: 1px solid #d4edda;
    padding: 1rem 1.25rem;
}
.keyword-table-card .card-header h5 {
    margin: 0;
    font-weight: 600;
    font-size: 1rem;
    color: #2c2c2c;
}
.keyword-search-box {
    max-width: 300px;
}
.keyword-table {
    margin-bottom: 0;
}
.keyword-table th {
    background: #f8f9fa;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #6c757d;
    border-bottom: 2px solid #e9ecef;
    padding: 0.75rem 1rem;
    white-space: nowrap;
    cursor: pointer;
    user-select: none;
}
.keyword-table th:hover {
    background: #d4edda;
}
.keyword-table th.sortable i {
    margin-left: 0.5rem;
    opacity: 0.5;
}
.keyword-table th.sorted-asc i.fa-sort-up,
.keyword-table th.sorted-desc i.fa-sort-down {
    opacity: 1;
    color: #28a745;
}
.keyword-table td {
    padding: 0.875rem 1rem;
    vertical-align: middle;
    font-size: 0.875rem;
    border-bottom: 1px solid #f0f0f0;
}
.keyword-table tbody tr:hover {
    background: rgba(40, 167, 69, 0.04);
}
.keyword-table tbody tr.selected {
    background: rgba(40, 167, 69, 0.08);
}
.keyword-table tbody tr.row-taken {
    opacity: 0.6;
}
.keyword-text {
    font-family: 'SFMono-Regular', Consolas, monospace;
    font-weight: 600;
    color: #2c2c2c;
    text-transform: uppercase;
}
.status-available-kw {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.625rem;
    background: #d4edda;
    color: #155724;
    border-radius: 1rem;
    font-size: 0.75rem;
    font-weight: 500;
}
.status-taken {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.625rem;
    background: #f8d7da;
    color: #721c24;
    border-radius: 1rem;
    font-size: 0.75rem;
    font-weight: 500;
}
.keyword-select-checkbox {
    width: 1.125rem;
    height: 1.125rem;
    cursor: pointer;
}
.keyword-select-checkbox:disabled {
    cursor: not-allowed;
    opacity: 0.5;
}
.keyword-table-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.25rem;
    background: #f8f9fa;
    border-top: 1px solid #e9ecef;
}
.keyword-empty-state {
    text-align: center;
    padding: 3rem;
    color: #6c757d;
}
.keyword-empty-state i {
    font-size: 2.5rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}
.custom-keyword-section {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 0.5rem;
    padding: 1.25rem;
    margin-bottom: 1rem;
}
.custom-keyword-section h6 {
    font-weight: 600;
    color: #495057;
    margin-bottom: 0.75rem;
}
.custom-keyword-input-group {
    max-width: 400px;
}
.custom-keyword-input-group .form-control {
    text-transform: uppercase;
    font-family: 'SFMono-Regular', Consolas, monospace;
    font-weight: 600;
    letter-spacing: 1px;
}
.custom-keyword-input-group .form-control.is-valid {
    border-color: #28a745;
}
.custom-keyword-input-group .form-control.is-invalid {
    border-color: #dc3545;
}
.keyword-validation-feedback {
    font-size: 0.8125rem;
    margin-top: 0.5rem;
    min-height: 1.5rem;
}
.keyword-validation-feedback.valid {
    color: #28a745;
}
.keyword-validation-feedback.invalid {
    color: #dc3545;
}
.keyword-validation-feedback i {
    margin-right: 0.375rem;
}
.keyword-rules {
    font-size: 0.75rem;
    color: #6c757d;
    margin-top: 0.75rem;
    padding-top: 0.75rem;
    border-top: 1px solid #e9ecef;
}
.keyword-rules ul {
    margin: 0;
    padding-left: 1.25rem;
}
.keyword-rules li {
    margin-bottom: 0.25rem;
}
</style>
@endpush

@section('content')
<div class="container-fluid purchase-numbers-container">
    <div class="row page-titles">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('purchase') }}">Purchase</a></li>
            <li class="breadcrumb-item active">Numbers</li>
        </ol>
    </div>

    <div id="accessDeniedView" style="display: none;">
        <div class="card access-denied-card">
            <div class="card-body py-5">
                <div class="icon-wrapper">
                    <i class="fas fa-lock"></i>
                </div>
                <h4 class="mb-3">Access Restricted</h4>
                <p class="text-muted mb-4">
                    You don't have permission to view this page. Only Admin, Finance, or Messaging Managers with the appropriate permissions can access number purchasing.
                </p>
                <a href="{{ route('dashboard') }}" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left me-2"></i>Return to Dashboard
                </a>
            </div>
        </div>
    </div>

    <div id="purchaseContent">
        <div class="page-header">
            <h2>Purchase Numbers</h2>
            <p>Acquire dedicated numbers for two-way messaging and customer engagement.</p>
        </div>

        <div class="purchase-section" id="vmnSection">
            <div class="section-header">
                <i class="fas fa-mobile-alt"></i>
                <h4>Virtual Mobile Numbers</h4>
                <span class="badge bg-primary">Inbound & Outbound</span>
            </div>
            
            <div class="section-intro">
                <p><i class="fas fa-info-circle"></i>Virtual Mobile Numbers (VMNs) enable two-way SMS and RCS messaging. Customers can reply directly to your messages, creating conversational experiences.</p>
            </div>

            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="card purchase-card number-type-card position-relative">
                        <div class="card-body d-flex flex-column">
                            <div class="number-icon vmn">
                                <i class="fas fa-mobile-alt"></i>
                            </div>
                            <h5 class="number-title">UK Long Code</h5>
                            <p class="number-description">Standard UK mobile number (07xxx) for local presence and two-way messaging.</p>
                            <ul class="feature-list">
                                <li><i class="fas fa-check"></i>Full two-way SMS capability</li>
                                <li><i class="fas fa-check"></i>RCS Business Messaging ready</li>
                                <li><i class="fas fa-check"></i>Instant provisioning</li>
                                <li><i class="fas fa-check"></i>Webhook delivery reports</li>
                            </ul>
                            <div class="price-indicator">
                                <span class="price-label">From</span>
                                <span class="price-value">£10</span>
                                <span class="price-period">/month</span>
                            </div>
                            <button class="btn btn-primary w-100" onclick="selectNumberType('uk-longcode')" disabled>
                                <i class="fas fa-plus me-2"></i>Select Number
                            </button>
                            <div class="availability-note mt-3">
                                <i class="fas fa-clock"></i>
                                Coming soon - contact sales for early access
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="card purchase-card number-type-card position-relative">
                        <div class="card-body d-flex flex-column">
                            <div class="number-icon vmn">
                                <i class="fas fa-globe"></i>
                            </div>
                            <h5 class="number-title">International VMN</h5>
                            <p class="number-description">Virtual numbers in 50+ countries for global reach and local presence.</p>
                            <ul class="feature-list">
                                <li><i class="fas fa-check"></i>50+ country coverage</li>
                                <li><i class="fas fa-check"></i>Local regulatory compliance</li>
                                <li><i class="fas fa-check"></i>Multi-language support</li>
                                <li><i class="fas fa-check"></i>Pooled number options</li>
                            </ul>
                            <div class="price-indicator">
                                <span class="price-label">From</span>
                                <span class="price-value">£15</span>
                                <span class="price-period">/month</span>
                            </div>
                            <button class="btn btn-primary w-100" onclick="selectNumberType('international')" disabled>
                                <i class="fas fa-plus me-2"></i>Select Number
                            </button>
                            <div class="availability-note mt-3">
                                <i class="fas fa-clock"></i>
                                Coming soon - contact sales for early access
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="card purchase-card number-type-card position-relative">
                        <div class="card-body d-flex flex-column">
                            <div class="number-icon vmn">
                                <i class="fas fa-phone-volume"></i>
                            </div>
                            <h5 class="number-title">Toll-Free Number</h5>
                            <p class="number-description">Free-to-text numbers that remove cost barriers for customer engagement.</p>
                            <ul class="feature-list">
                                <li><i class="fas fa-check"></i>Free for customers to text</li>
                                <li><i class="fas fa-check"></i>Memorable number patterns</li>
                                <li><i class="fas fa-check"></i>High trust & recognition</li>
                                <li><i class="fas fa-check"></i>Available in select regions</li>
                            </ul>
                            <div class="price-indicator">
                                <span class="price-label">From</span>
                                <span class="price-value">£25</span>
                                <span class="price-period">/month</span>
                            </div>
                            <button class="btn btn-primary w-100" onclick="selectNumberType('toll-free')" disabled>
                                <i class="fas fa-plus me-2"></i>Select Number
                            </button>
                            <div class="availability-note mt-3">
                                <i class="fas fa-clock"></i>
                                Coming soon - contact sales for early access
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card subaccount-selector-card">
                <div class="card-body">
                    <label for="vmnSubAccountSelect">
                        <i class="fas fa-building text-primary"></i>
                        Assign to Sub-Account
                        <span class="required-badge">Required</span>
                    </label>
                    <select class="form-select" id="vmnSubAccountSelect" onchange="onSubAccountChange()">
                        <option value="">-- Select Sub-Account --</option>
                    </select>
                    <p class="help-text">
                        <i class="fas fa-info-circle me-1"></i>
                        All selected numbers will be assigned to this sub-account. One sub-account per purchase.
                    </p>
                </div>
            </div>

            <div class="card vmn-table-card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5><i class="fas fa-list me-2 text-primary"></i>Available Numbers</h5>
                    <div class="d-flex align-items-center gap-2">
                        <div class="input-group input-group-sm vmn-search-box">
                            <span class="input-group-text bg-transparent"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control" id="vmnSearchInput" placeholder="Search by number...">
                        </div>
                        <select class="form-select form-select-sm" id="vmnCountryFilter" style="width: auto;">
                            <option value="">All Countries</option>
                            <option value="GB">United Kingdom</option>
                            <option value="US">United States</option>
                            <option value="DE">Germany</option>
                            <option value="FR">France</option>
                            <option value="ES">Spain</option>
                            <option value="IE">Ireland</option>
                        </select>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table vmn-table" id="vmnTable">
                        <thead>
                            <tr>
                                <th style="width: 50px;">
                                    <input type="checkbox" class="form-check-input vmn-select-checkbox" id="vmnSelectAll" onclick="toggleVmnSelectAll()">
                                </th>
                                <th class="sortable" data-sort="number" onclick="sortVmnTable('number')">
                                    Mobile Number <i class="fas fa-sort"></i>
                                </th>
                                <th class="sortable" data-sort="country" onclick="sortVmnTable('country')">
                                    Country <i class="fas fa-sort"></i>
                                </th>
                                <th class="sortable" data-sort="setupFee" onclick="sortVmnTable('setupFee')">
                                    Setup Fee <i class="fas fa-sort"></i>
                                </th>
                                <th class="sortable" data-sort="monthlyFee" onclick="sortVmnTable('monthlyFee')">
                                    Monthly Fee <i class="fas fa-sort"></i>
                                </th>
                                <th class="sortable" data-sort="availability" onclick="sortVmnTable('availability')">
                                    Availability <i class="fas fa-sort"></i>
                                </th>
                            </tr>
                        </thead>
                        <tbody id="vmnTableBody">
                        </tbody>
                    </table>
                </div>
                <div class="vmn-table-footer">
                    <div class="selection-summary">
                        <span id="vmnSelectedCount">0</span> number(s) selected
                        <span id="vmnTotalCost" class="ms-3" style="display: none;">
                            Total: <strong>£<span id="vmnSetupTotal">0</span></strong> setup + 
                            <strong>£<span id="vmnMonthlyTotal">0</span></strong>/month
                        </span>
                    </div>
                    <button class="btn btn-primary btn-sm" id="vmnProceedBtn" onclick="showPurchaseConfirmation()" disabled>
                        <i class="fas fa-credit-card me-2"></i>Purchase Selected Numbers
                    </button>
                </div>
            </div>
        </div>

        <div class="purchase-section" id="shortcodeSection">
            <div class="section-header">
                <i class="fas fa-hashtag"></i>
                <h4>Shortcode Keywords</h4>
                <span class="badge bg-success">High Volume</span>
            </div>
            
            <div class="section-intro">
                <p><i class="fas fa-info-circle"></i>Shortcodes are memorable 5-6 digit numbers ideal for marketing campaigns, competitions, and high-volume two-way messaging. Keywords allow multiple campaigns on a shared shortcode.</p>
            </div>

            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="card purchase-card number-type-card shortcode position-relative">
                        <div class="card-body d-flex flex-column">
                            <div class="number-icon shortcode">
                                <i class="fas fa-hashtag"></i>
                            </div>
                            <h5 class="number-title">Shared Shortcode Keyword</h5>
                            <p class="number-description">Reserve a keyword on our shared shortcode for cost-effective campaigns.</p>
                            <ul class="feature-list">
                                <li><i class="fas fa-check"></i>Instant setup</li>
                                <li><i class="fas fa-check"></i>Cost-effective entry point</li>
                                <li><i class="fas fa-check"></i>Text-to-win campaigns</li>
                                <li><i class="fas fa-check"></i>Opt-in list building</li>
                            </ul>
                            <div class="price-indicator">
                                <span class="price-label">From</span>
                                <span class="price-value">£50</span>
                                <span class="price-period">/month</span>
                            </div>
                            <button class="btn btn-success w-100" onclick="selectKeywordType('shared')" disabled>
                                <i class="fas fa-plus me-2"></i>Reserve Keyword
                            </button>
                            <div class="availability-note mt-3">
                                <i class="fas fa-clock"></i>
                                Coming soon - contact sales for early access
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="card purchase-card number-type-card shortcode position-relative">
                        <div class="card-body d-flex flex-column">
                            <div class="number-icon shortcode">
                                <i class="fas fa-star"></i>
                            </div>
                            <h5 class="number-title">Dedicated Shortcode</h5>
                            <p class="number-description">Your own exclusive shortcode for maximum brand recognition and control.</p>
                            <ul class="feature-list">
                                <li><i class="fas fa-check"></i>Exclusive to your brand</li>
                                <li><i class="fas fa-check"></i>Unlimited keywords</li>
                                <li><i class="fas fa-check"></i>Custom vanity numbers</li>
                                <li><i class="fas fa-check"></i>Highest throughput</li>
                            </ul>
                            <div class="price-indicator">
                                <span class="price-label">From</span>
                                <span class="price-value">£500</span>
                                <span class="price-period">/month</span>
                            </div>
                            <button class="btn btn-success w-100" onclick="selectKeywordType('dedicated')" disabled>
                                <i class="fas fa-plus me-2"></i>Request Quote
                            </button>
                            <div class="availability-note mt-3">
                                <i class="fas fa-clock"></i>
                                Contact sales for dedicated shortcode enquiries
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="card purchase-card number-type-card shortcode position-relative">
                        <div class="card-body d-flex flex-column">
                            <div class="number-icon shortcode">
                                <i class="fas fa-award"></i>
                            </div>
                            <h5 class="number-title">Premium Rate Shortcode</h5>
                            <p class="number-description">Revenue-generating shortcodes for competitions, voting, and donations.</p>
                            <ul class="feature-list">
                                <li><i class="fas fa-check"></i>Revenue share model</li>
                                <li><i class="fas fa-check"></i>PSA & charity approved</li>
                                <li><i class="fas fa-check"></i>Compliance managed</li>
                                <li><i class="fas fa-check"></i>Real-time reporting</li>
                            </ul>
                            <div class="price-indicator">
                                <span class="price-label">Setup</span>
                                <span class="price-value">Custom</span>
                                <span class="price-period">pricing</span>
                            </div>
                            <button class="btn btn-success w-100" onclick="selectKeywordType('premium')" disabled>
                                <i class="fas fa-envelope me-2"></i>Contact Sales
                            </button>
                            <div class="availability-note mt-3">
                                <i class="fas fa-info-circle"></i>
                                Requires regulatory approval - 4-6 week lead time
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="shared-shortcode-display">
                <div class="shortcode-label">
                    <i class="fas fa-hashtag me-1"></i>Shared Shortcode
                </div>
                <div class="shortcode-number">82228</div>
                <div class="shortcode-note">
                    <i class="fas fa-info-circle me-1"></i>Text your keyword to this shortcode
                </div>
            </div>

            <div class="custom-keyword-section">
                <h6><i class="fas fa-plus-circle me-2 text-success"></i>Request a Custom Keyword</h6>
                <div class="d-flex align-items-start gap-2">
                    <div class="custom-keyword-input-group flex-grow-1">
                        <div class="input-group">
                            <input type="text" class="form-control" id="customKeywordInput" placeholder="Enter keyword..." maxlength="20" oninput="validateCustomKeyword()">
                            <button class="btn btn-success" type="button" id="addCustomKeywordBtn" onclick="addCustomKeyword()" disabled>
                                <i class="fas fa-plus me-1"></i>Add
                            </button>
                        </div>
                        <div class="keyword-validation-feedback" id="keywordValidationFeedback"></div>
                    </div>
                </div>
                <div class="keyword-rules">
                    <strong>Keyword Rules:</strong>
                    <ul>
                        <li>3-20 characters (alphanumeric only)</li>
                        <li>No spaces or special characters</li>
                        <li>Must be unique (case-insensitive)</li>
                    </ul>
                </div>
            </div>

            <div class="card keyword-table-card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5><i class="fas fa-key me-2 text-success"></i>Available Keywords</h5>
                    <div class="d-flex align-items-center gap-2">
                        <div class="input-group input-group-sm keyword-search-box">
                            <span class="input-group-text bg-transparent"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control" id="keywordSearchInput" placeholder="Search keywords...">
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table keyword-table" id="keywordTable">
                        <thead>
                            <tr>
                                <th style="width: 50px;">
                                    <input type="checkbox" class="form-check-input keyword-select-checkbox" id="keywordSelectAll" onclick="toggleKeywordSelectAll()">
                                </th>
                                <th class="sortable" data-sort="keyword" onclick="sortKeywordTable('keyword')">
                                    Keyword <i class="fas fa-sort"></i>
                                </th>
                                <th class="sortable" data-sort="status" onclick="sortKeywordTable('status')">
                                    Status <i class="fas fa-sort"></i>
                                </th>
                            </tr>
                        </thead>
                        <tbody id="keywordTableBody">
                        </tbody>
                    </table>
                </div>
                <div class="keyword-table-footer">
                    <div class="selection-summary">
                        <span id="keywordSelectedCount">0</span> keyword(s) selected
                    </div>
                    <button class="btn btn-success btn-sm" id="keywordReserveBtn" onclick="reserveSelectedKeywords()" disabled>
                        <i class="fas fa-check me-2"></i>Reserve Selected Keywords
                    </button>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-body">
                <div class="d-flex align-items-start">
                    <div class="flex-shrink-0 me-3">
                        <div class="number-icon vmn">
                            <i class="fas fa-question"></i>
                        </div>
                    </div>
                    <div>
                        <h5 class="mb-2">Need help choosing?</h5>
                        <p class="text-muted mb-3">Our team can help you select the right number type for your messaging needs. Whether you're setting up customer support, running marketing campaigns, or building two-way conversational experiences.</p>
                        <a href="{{ route('support.create-ticket') }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-headset me-2"></i>Contact Support
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade purchase-confirmation-modal" id="purchaseConfirmationModal" tabindex="-1" aria-labelledby="purchaseConfirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="purchaseConfirmationModalLabel">
                    <i class="fas fa-credit-card me-2"></i>Confirm Purchase
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">You are about to purchase the following virtual mobile numbers:</p>
                
                <div class="numbers-list" id="confirmNumbersList"></div>
                
                <div class="confirmation-summary">
                    <div class="summary-row">
                        <span>Numbers Selected</span>
                        <strong id="confirmNumberCount">0</strong>
                    </div>
                    <div class="summary-row">
                        <span>Assigned To</span>
                        <strong id="confirmSubAccount">-</strong>
                    </div>
                    <div class="summary-row">
                        <span>Setup Fee (charged now)</span>
                        <strong>£<span id="confirmSetupFee">0.00</span></strong>
                    </div>
                    <div class="summary-row">
                        <span>Monthly Fee</span>
                        <strong>£<span id="confirmMonthlyFee">0.00</span>/month</strong>
                    </div>
                    <div class="summary-row total">
                        <span>Due Now</span>
                        <strong class="text-primary">£<span id="confirmDueNow">0.00</span></strong>
                    </div>
                </div>
                
                <div class="billing-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Billing Information:</strong>
                    <ul class="mb-0 mt-2 ps-4">
                        <li>Setup fee will be charged immediately upon confirmation.</li>
                        <li>Monthly fees are charged on the 1st of each month.</li>
                        <li>First monthly charge will be pro-rated based on remaining days.</li>
                    </ul>
                </div>
                
                <p class="text-muted small mb-0">
                    <i class="fas fa-lock me-1"></i>
                    By clicking "Confirm Purchase", you agree to purchase all selected numbers. This action cannot be partially completed.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmPurchaseBtn" onclick="executePurchase()">
                    <i class="fas fa-check me-2"></i>Confirm Purchase
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="insufficientBalanceModal" tabindex="-1" aria-labelledby="insufficientBalanceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="insufficientBalanceModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>Insufficient Balance
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <div class="mb-4">
                    <i class="fas fa-wallet text-danger" style="font-size: 3rem; opacity: 0.8;"></i>
                </div>
                <h5 class="mb-3">You do not have sufficient balance to complete this purchase.</h5>
                <div class="bg-light rounded p-3 mb-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Current Balance:</span>
                        <strong>£<span id="insufficientCurrentBalance">0.00</span></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Required Amount:</span>
                        <strong class="text-danger">£<span id="insufficientRequiredAmount">0.00</span></strong>
                    </div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Shortfall:</span>
                        <strong class="text-danger">£<span id="insufficientShortfall">0.00</span></strong>
                    </div>
                </div>
                <p class="text-muted small mb-0">Please top up your account balance before proceeding with this purchase.</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <a href="{{ route('purchase.messages') }}" class="btn btn-primary">
                    <i class="fas fa-coins me-2"></i>Go to Purchase → Messages
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

var subAccountsMockData = [
    { id: 'sa_001', name: 'Main Account', canPurchase: true, allowedRoles: ['admin', 'finance', 'messaging_manager'] },
    { id: 'sa_002', name: 'Marketing Team', canPurchase: true, allowedRoles: ['admin', 'finance', 'messaging_manager'] },
    { id: 'sa_003', name: 'Sales Department', canPurchase: true, allowedRoles: ['admin', 'finance', 'messaging_manager'] },
    { id: 'sa_004', name: 'Customer Support', canPurchase: true, allowedRoles: ['admin', 'messaging_manager'] },
    { id: 'sa_005', name: 'Operations', canPurchase: true, allowedRoles: ['admin'] },
    { id: 'sa_006', name: 'HR Department', canPurchase: false, allowedRoles: ['admin'] }
];

var selectedSubAccountId = '';

var accountBalance = 45.00;

var vmnMockData = [
    { id: 1, number: '+447700900001', country: 'GB', countryName: 'United Kingdom', flag: '🇬🇧', setupFee: 10.00, monthlyFee: 8.00, availability: 'Available', hubspotProductId: 'prod_uk_vmn_001' },
    { id: 2, number: '+447700900002', country: 'GB', countryName: 'United Kingdom', flag: '🇬🇧', setupFee: 10.00, monthlyFee: 8.00, availability: 'Available', hubspotProductId: 'prod_uk_vmn_002' },
    { id: 3, number: '+447700900003', country: 'GB', countryName: 'United Kingdom', flag: '🇬🇧', setupFee: 10.00, monthlyFee: 8.00, availability: 'Reserved', hubspotProductId: 'prod_uk_vmn_003' },
    { id: 4, number: '+447700900004', country: 'GB', countryName: 'United Kingdom', flag: '🇬🇧', setupFee: 10.00, monthlyFee: 8.00, availability: 'Available', hubspotProductId: 'prod_uk_vmn_004' },
    { id: 5, number: '+447700900100', country: 'GB', countryName: 'United Kingdom', flag: '🇬🇧', setupFee: 15.00, monthlyFee: 10.00, availability: 'Available', hubspotProductId: 'prod_uk_vmn_100' },
    { id: 6, number: '+12025551234', country: 'US', countryName: 'United States', flag: '🇺🇸', setupFee: 15.00, monthlyFee: 12.00, availability: 'Available', hubspotProductId: 'prod_us_vmn_001' },
    { id: 7, number: '+12025551235', country: 'US', countryName: 'United States', flag: '🇺🇸', setupFee: 15.00, monthlyFee: 12.00, availability: 'Reserved', hubspotProductId: 'prod_us_vmn_002' },
    { id: 8, number: '+12025551236', country: 'US', countryName: 'United States', flag: '🇺🇸', setupFee: 15.00, monthlyFee: 12.00, availability: 'Available', hubspotProductId: 'prod_us_vmn_003' },
    { id: 9, number: '+4915123456789', country: 'DE', countryName: 'Germany', flag: '🇩🇪', setupFee: 20.00, monthlyFee: 15.00, availability: 'Available', hubspotProductId: 'prod_de_vmn_001' },
    { id: 10, number: '+4915123456790', country: 'DE', countryName: 'Germany', flag: '🇩🇪', setupFee: 20.00, monthlyFee: 15.00, availability: 'Available', hubspotProductId: 'prod_de_vmn_002' },
    { id: 11, number: '+33612345678', country: 'FR', countryName: 'France', flag: '🇫🇷', setupFee: 18.00, monthlyFee: 14.00, availability: 'Available', hubspotProductId: 'prod_fr_vmn_001' },
    { id: 12, number: '+33612345679', country: 'FR', countryName: 'France', flag: '🇫🇷', setupFee: 18.00, monthlyFee: 14.00, availability: 'Reserved', hubspotProductId: 'prod_fr_vmn_002' },
    { id: 13, number: '+34612345678', country: 'ES', countryName: 'Spain', flag: '🇪🇸', setupFee: 18.00, monthlyFee: 14.00, availability: 'Available', hubspotProductId: 'prod_es_vmn_001' },
    { id: 14, number: '+353871234567', country: 'IE', countryName: 'Ireland', flag: '🇮🇪', setupFee: 12.00, monthlyFee: 10.00, availability: 'Available', hubspotProductId: 'prod_ie_vmn_001' },
    { id: 15, number: '+353871234568', country: 'IE', countryName: 'Ireland', flag: '🇮🇪', setupFee: 12.00, monthlyFee: 10.00, availability: 'Available', hubspotProductId: 'prod_ie_vmn_002' }
];

var vmnSelectedIds = [];
var vmnSortColumn = 'number';
var vmnSortDirection = 'asc';
var vmnSearchTerm = '';
var vmnCountryFilter = '';

var keywordMockData = [
    { id: 1, keyword: 'WIN', status: 'Available' },
    { id: 2, keyword: 'PRIZE', status: 'Available' },
    { id: 3, keyword: 'SALE', status: 'Taken' },
    { id: 4, keyword: 'DEAL', status: 'Available' },
    { id: 5, keyword: 'FREE', status: 'Taken' },
    { id: 6, keyword: 'OFFER', status: 'Available' },
    { id: 7, keyword: 'SAVE', status: 'Available' },
    { id: 8, keyword: 'JOIN', status: 'Available' },
    { id: 9, keyword: 'VOTE', status: 'Taken' },
    { id: 10, keyword: 'HELP', status: 'Available' },
    { id: 11, keyword: 'INFO', status: 'Available' },
    { id: 12, keyword: 'STOP', status: 'Taken' },
    { id: 13, keyword: 'START', status: 'Available' },
    { id: 14, keyword: 'NEWS', status: 'Available' },
    { id: 15, keyword: 'ALERT', status: 'Taken' },
    { id: 16, keyword: 'UPDATE', status: 'Available' },
    { id: 17, keyword: 'PROMO', status: 'Available' },
    { id: 18, keyword: 'CLUB', status: 'Available' },
    { id: 19, keyword: 'VIP', status: 'Taken' },
    { id: 20, keyword: 'REWARDS', status: 'Available' }
];

var keywordSelectedIds = [];
var keywordSortColumn = 'keyword';
var keywordSortDirection = 'asc';
var keywordSearchTerm = '';

var keywordValidationConfig = {
    minLength: 3,
    maxLength: 20,
    pattern: /^[A-Za-z0-9]+$/
};

document.addEventListener('DOMContentLoaded', function() {
    checkAccess();
    populateSubAccountDropdown();
    initializeVmnTable();
    initializeKeywordTable();
});

function checkAccess() {
    var hasAccess = allowedRoles.includes(currentUserRole);
    document.getElementById('accessDeniedView').style.display = hasAccess ? 'none' : 'block';
    document.getElementById('purchaseContent').style.display = hasAccess ? 'block' : 'none';
}

function populateSubAccountDropdown() {
    var select = document.getElementById('vmnSubAccountSelect');
    var availableAccounts = subAccountsMockData.filter(function(sa) {
        return sa.canPurchase && sa.allowedRoles.includes(currentUserRole);
    });
    
    availableAccounts.forEach(function(sa) {
        var option = document.createElement('option');
        option.value = sa.id;
        option.textContent = sa.name;
        select.appendChild(option);
    });
}

function onSubAccountChange() {
    var select = document.getElementById('vmnSubAccountSelect');
    selectedSubAccountId = select.value;
    updateVmnSelection();
}

function initializeVmnTable() {
    document.getElementById('vmnSearchInput').addEventListener('input', function(e) {
        vmnSearchTerm = e.target.value.toLowerCase();
        renderVmnTable();
    });
    
    document.getElementById('vmnCountryFilter').addEventListener('change', function(e) {
        vmnCountryFilter = e.target.value;
        renderVmnTable();
    });
    
    renderVmnTable();
}

function getFilteredVmnData() {
    return vmnMockData.filter(function(item) {
        if (vmnSearchTerm && !item.number.toLowerCase().includes(vmnSearchTerm)) {
            return false;
        }
        if (vmnCountryFilter && item.country !== vmnCountryFilter) {
            return false;
        }
        return true;
    });
}

function getSortedVmnData(data) {
    return data.slice().sort(function(a, b) {
        var valA = a[vmnSortColumn];
        var valB = b[vmnSortColumn];
        
        if (typeof valA === 'number') {
            return vmnSortDirection === 'asc' ? valA - valB : valB - valA;
        }
        
        valA = String(valA).toLowerCase();
        valB = String(valB).toLowerCase();
        
        if (valA < valB) return vmnSortDirection === 'asc' ? -1 : 1;
        if (valA > valB) return vmnSortDirection === 'asc' ? 1 : -1;
        return 0;
    });
}

function renderVmnTable() {
    var tbody = document.getElementById('vmnTableBody');
    var filtered = getFilteredVmnData();
    var sorted = getSortedVmnData(filtered);
    
    if (sorted.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6"><div class="vmn-empty-state"><i class="fas fa-search"></i><h5>No numbers found</h5><p>Try adjusting your search or filter criteria.</p></div></td></tr>';
        updateVmnSortIndicators();
        updateVmnSelection();
        return;
    }
    
    var html = '';
    sorted.forEach(function(item) {
        var isSelected = vmnSelectedIds.includes(item.id);
        var isAvailable = item.availability === 'Available';
        var rowClass = isSelected ? 'selected' : '';
        if (!isAvailable) rowClass += ' row-reserved';
        
        html += '<tr class="' + rowClass + '" data-id="' + item.id + '">';
        html += '<td><input type="checkbox" class="form-check-input vmn-select-checkbox" ' + 
                (isSelected ? 'checked' : '') + ' ' + 
                (!isAvailable ? 'disabled' : '') + 
                ' onchange="toggleVmnSelect(' + item.id + ')"></td>';
        html += '<td><span class="vmn-number">' + item.number + '</span></td>';
        html += '<td><div class="country-cell"><span class="country-flag">' + item.flag + '</span><span>' + item.countryName + '</span><span class="country-code">(' + item.country + ')</span></div></td>';
        html += '<td class="fee-cell">£' + item.setupFee.toFixed(2) + '</td>';
        html += '<td class="fee-cell">£' + item.monthlyFee.toFixed(2) + '</td>';
        html += '<td><span class="' + (isAvailable ? 'status-available' : 'status-reserved') + '">' + item.availability + '</span></td>';
        html += '</tr>';
    });
    
    tbody.innerHTML = html;
    updateVmnSortIndicators();
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

function updateVmnSortIndicators() {
    document.querySelectorAll('#vmnTable th.sortable').forEach(function(th) {
        th.classList.remove('sorted-asc', 'sorted-desc');
        var icon = th.querySelector('i');
        icon.className = 'fas fa-sort';
    });
    
    var activeHeader = document.querySelector('#vmnTable th[data-sort="' + vmnSortColumn + '"]');
    if (activeHeader) {
        activeHeader.classList.add(vmnSortDirection === 'asc' ? 'sorted-asc' : 'sorted-desc');
        var icon = activeHeader.querySelector('i');
        icon.className = vmnSortDirection === 'asc' ? 'fas fa-sort-up' : 'fas fa-sort-down';
    }
}

function toggleVmnSelectAll() {
    var selectAllCheckbox = document.getElementById('vmnSelectAll');
    var filtered = getFilteredVmnData().filter(function(item) {
        return item.availability === 'Available';
    });
    
    if (selectAllCheckbox.checked) {
        vmnSelectedIds = filtered.map(function(item) { return item.id; });
    } else {
        vmnSelectedIds = [];
    }
    
    renderVmnTable();
}

function toggleVmnSelect(id) {
    var idx = vmnSelectedIds.indexOf(id);
    if (idx > -1) {
        vmnSelectedIds.splice(idx, 1);
    } else {
        var item = vmnMockData.find(function(n) { return n.id === id; });
        if (item && item.availability === 'Available') {
            vmnSelectedIds.push(id);
        }
    }
    updateVmnSelection();
}

function updateVmnSelection() {
    var count = vmnSelectedIds.length;
    document.getElementById('vmnSelectedCount').textContent = count;
    
    var canPurchase = count > 0 && selectedSubAccountId !== '';
    document.getElementById('vmnProceedBtn').disabled = !canPurchase;
    
    var totalCostEl = document.getElementById('vmnTotalCost');
    if (count > 0) {
        var setupTotal = 0;
        var monthlyTotal = 0;
        vmnSelectedIds.forEach(function(id) {
            var item = vmnMockData.find(function(n) { return n.id === id; });
            if (item) {
                setupTotal += item.setupFee;
                monthlyTotal += item.monthlyFee;
            }
        });
        document.getElementById('vmnSetupTotal').textContent = setupTotal.toFixed(2);
        document.getElementById('vmnMonthlyTotal').textContent = monthlyTotal.toFixed(2);
        totalCostEl.style.display = 'inline';
    } else {
        totalCostEl.style.display = 'none';
    }
    
    var availableFiltered = getFilteredVmnData().filter(function(item) {
        return item.availability === 'Available';
    });
    var allSelected = availableFiltered.length > 0 && availableFiltered.every(function(item) {
        return vmnSelectedIds.includes(item.id);
    });
    document.getElementById('vmnSelectAll').checked = allSelected;
}

function showPurchaseConfirmation() {
    if (vmnSelectedIds.length === 0 || !selectedSubAccountId) return;
    
    var selectedNumbers = vmnSelectedIds.map(function(id) {
        return vmnMockData.find(function(n) { return n.id === id; });
    });
    
    var subAccount = subAccountsMockData.find(function(sa) { return sa.id === selectedSubAccountId; });
    
    var setupTotal = 0;
    var monthlyTotal = 0;
    var numbersList = [];
    
    selectedNumbers.forEach(function(item) {
        setupTotal += item.setupFee;
        monthlyTotal += item.monthlyFee;
        numbersList.push(item.number + ' (' + item.countryName + ')');
    });
    
    if (setupTotal > accountBalance) {
        document.getElementById('insufficientCurrentBalance').textContent = accountBalance.toFixed(2);
        document.getElementById('insufficientRequiredAmount').textContent = setupTotal.toFixed(2);
        document.getElementById('insufficientShortfall').textContent = (setupTotal - accountBalance).toFixed(2);
        
        var insufficientModal = new bootstrap.Modal(document.getElementById('insufficientBalanceModal'));
        insufficientModal.show();
        return;
    }
    
    document.getElementById('confirmNumbersList').innerHTML = numbersList.join('<br>');
    document.getElementById('confirmNumberCount').textContent = selectedNumbers.length;
    document.getElementById('confirmSubAccount').textContent = subAccount ? subAccount.name : '-';
    document.getElementById('confirmSetupFee').textContent = setupTotal.toFixed(2);
    document.getElementById('confirmMonthlyFee').textContent = monthlyTotal.toFixed(2);
    document.getElementById('confirmDueNow').textContent = setupTotal.toFixed(2);
    
    var modal = new bootstrap.Modal(document.getElementById('purchaseConfirmationModal'));
    modal.show();
}

function executePurchase() {
    if (vmnSelectedIds.length === 0 || !selectedSubAccountId) return;
    
    var selectedNumbers = vmnSelectedIds.map(function(id) {
        return vmnMockData.find(function(n) { return n.id === id; });
    });
    
    var subAccount = subAccountsMockData.find(function(sa) { return sa.id === selectedSubAccountId; });
    
    var setupTotal = 0;
    var monthlyTotal = 0;
    selectedNumbers.forEach(function(item) {
        setupTotal += item.setupFee;
        monthlyTotal += item.monthlyFee;
    });
    
    console.log('TODO: API call - POST /api/purchase/numbers/execute');
    console.log('TODO: Atomic transaction required - all or nothing');
    console.log('TODO: 1. Deduct setup fee from account balance');
    console.log('TODO: 2. Schedule monthly fee for 1st of each month');
    console.log('TODO: 3. Mark numbers as owned/reserved');
    console.log('TODO: 4. Assign numbers to sub-account');
    console.log('Purchase details:', {
        subAccountId: selectedSubAccountId,
        subAccountName: subAccount ? subAccount.name : null,
        numbers: selectedNumbers.map(function(n) { return { id: n.id, number: n.number, hubspotProductId: n.hubspotProductId }; }),
        setupFeeTotal: setupTotal,
        monthlyFeeTotal: monthlyTotal
    });
    
    var modal = bootstrap.Modal.getInstance(document.getElementById('purchaseConfirmationModal'));
    modal.hide();
    
    alert('Purchase confirmed!\n\n' + selectedNumbers.length + ' number(s) purchased and assigned to ' + (subAccount ? subAccount.name : 'Unknown') + '.\n\nSetup fee charged: £' + setupTotal.toFixed(2) + '\nMonthly fee: £' + monthlyTotal.toFixed(2) + '/month\n\n(This is a demo - no actual purchase was made)');
    
    vmnSelectedIds = [];
    selectedSubAccountId = '';
    document.getElementById('vmnSubAccountSelect').value = '';
    renderVmnTable();
    updateVmnSelection();
}

function selectNumberType(type) {
    console.log('TODO: API call - POST /api/purchase/numbers/vmn with type:', type);
    alert('Number selection coming soon. Please contact sales for early access.');
}

function selectKeywordType(type) {
    console.log('TODO: API call - POST /api/purchase/numbers/shortcode with type:', type);
    alert('Keyword reservation coming soon. Please contact sales for enquiries.');
}

function initializeKeywordTable() {
    document.getElementById('keywordSearchInput').addEventListener('input', function(e) {
        keywordSearchTerm = e.target.value.toLowerCase();
        renderKeywordTable();
    });
    
    renderKeywordTable();
}

function getFilteredKeywordData() {
    return keywordMockData.filter(function(item) {
        if (keywordSearchTerm && !item.keyword.toLowerCase().includes(keywordSearchTerm)) {
            return false;
        }
        return true;
    });
}

function getSortedKeywordData(data) {
    return data.slice().sort(function(a, b) {
        var valA = a[keywordSortColumn];
        var valB = b[keywordSortColumn];
        
        valA = String(valA).toLowerCase();
        valB = String(valB).toLowerCase();
        
        if (valA < valB) return keywordSortDirection === 'asc' ? -1 : 1;
        if (valA > valB) return keywordSortDirection === 'asc' ? 1 : -1;
        return 0;
    });
}

function renderKeywordTable() {
    var tbody = document.getElementById('keywordTableBody');
    var filtered = getFilteredKeywordData();
    var sorted = getSortedKeywordData(filtered);
    
    if (sorted.length === 0) {
        tbody.innerHTML = '<tr><td colspan="3"><div class="keyword-empty-state"><i class="fas fa-search"></i><h5>No keywords found</h5><p>Try adjusting your search criteria.</p></div></td></tr>';
        updateKeywordSortIndicators();
        updateKeywordSelection();
        return;
    }
    
    var html = '';
    sorted.forEach(function(item) {
        var isSelected = keywordSelectedIds.includes(item.id);
        var isAvailable = item.status === 'Available';
        var rowClass = isSelected ? 'selected' : '';
        if (!isAvailable) rowClass += ' row-taken';
        
        html += '<tr class="' + rowClass + '" data-id="' + item.id + '">';
        html += '<td><input type="checkbox" class="form-check-input keyword-select-checkbox" ' + 
                (isSelected ? 'checked' : '') + ' ' + 
                (!isAvailable ? 'disabled' : '') + 
                ' onchange="toggleKeywordSelect(' + item.id + ')"></td>';
        html += '<td><span class="keyword-text">' + item.keyword + '</span></td>';
        html += '<td><span class="' + (isAvailable ? 'status-available-kw' : 'status-taken') + '">' + item.status + '</span></td>';
        html += '</tr>';
    });
    
    tbody.innerHTML = html;
    updateKeywordSortIndicators();
    updateKeywordSelection();
}

function sortKeywordTable(column) {
    if (keywordSortColumn === column) {
        keywordSortDirection = keywordSortDirection === 'asc' ? 'desc' : 'asc';
    } else {
        keywordSortColumn = column;
        keywordSortDirection = 'asc';
    }
    renderKeywordTable();
}

function updateKeywordSortIndicators() {
    document.querySelectorAll('#keywordTable th.sortable').forEach(function(th) {
        th.classList.remove('sorted-asc', 'sorted-desc');
        var icon = th.querySelector('i');
        icon.className = 'fas fa-sort';
    });
    
    var activeHeader = document.querySelector('#keywordTable th[data-sort="' + keywordSortColumn + '"]');
    if (activeHeader) {
        activeHeader.classList.add(keywordSortDirection === 'asc' ? 'sorted-asc' : 'sorted-desc');
        var icon = activeHeader.querySelector('i');
        icon.className = keywordSortDirection === 'asc' ? 'fas fa-sort-up' : 'fas fa-sort-down';
    }
}

function toggleKeywordSelectAll() {
    var selectAllCheckbox = document.getElementById('keywordSelectAll');
    var filtered = getFilteredKeywordData().filter(function(item) {
        return item.status === 'Available';
    });
    
    if (selectAllCheckbox.checked) {
        keywordSelectedIds = filtered.map(function(item) { return item.id; });
    } else {
        keywordSelectedIds = [];
    }
    
    renderKeywordTable();
}

function toggleKeywordSelect(id) {
    var idx = keywordSelectedIds.indexOf(id);
    if (idx > -1) {
        keywordSelectedIds.splice(idx, 1);
    } else {
        var item = keywordMockData.find(function(k) { return k.id === id; });
        if (item && item.status === 'Available') {
            keywordSelectedIds.push(id);
        }
    }
    updateKeywordSelection();
}

function updateKeywordSelection() {
    var count = keywordSelectedIds.length;
    document.getElementById('keywordSelectedCount').textContent = count;
    document.getElementById('keywordReserveBtn').disabled = count === 0;
    
    var availableFiltered = getFilteredKeywordData().filter(function(item) {
        return item.status === 'Available';
    });
    var allSelected = availableFiltered.length > 0 && availableFiltered.every(function(item) {
        return keywordSelectedIds.includes(item.id);
    });
    document.getElementById('keywordSelectAll').checked = allSelected;
}

function reserveSelectedKeywords() {
    if (keywordSelectedIds.length === 0) return;
    
    var selectedKeywords = keywordSelectedIds.map(function(id) {
        return keywordMockData.find(function(k) { return k.id === id; });
    });
    
    console.log('TODO: API call - POST /api/purchase/keywords/reserve');
    console.log('TODO: No keyword routing logic here - UI only');
    console.log('Selected keywords:', selectedKeywords);
    
    var keywordList = selectedKeywords.map(function(k) { return k.keyword; }).join(', ');
    alert('Keyword reservation coming soon.\n\nSelected keywords: ' + keywordList + '\n\n(This is a demo - no actual reservation was made)');
}

function validateCustomKeyword() {
    var input = document.getElementById('customKeywordInput');
    var feedback = document.getElementById('keywordValidationFeedback');
    var addBtn = document.getElementById('addCustomKeywordBtn');
    var value = input.value.trim().toUpperCase();
    
    input.classList.remove('is-valid', 'is-invalid');
    feedback.classList.remove('valid', 'invalid');
    addBtn.disabled = true;
    
    if (value === '') {
        feedback.innerHTML = '';
        return { valid: false, message: '' };
    }
    
    if (value.length < keywordValidationConfig.minLength) {
        input.classList.add('is-invalid');
        feedback.classList.add('invalid');
        feedback.innerHTML = '<i class="fas fa-times-circle"></i>Keyword must be at least ' + keywordValidationConfig.minLength + ' characters';
        return { valid: false, message: 'Too short' };
    }
    
    if (value.length > keywordValidationConfig.maxLength) {
        input.classList.add('is-invalid');
        feedback.classList.add('invalid');
        feedback.innerHTML = '<i class="fas fa-times-circle"></i>Keyword cannot exceed ' + keywordValidationConfig.maxLength + ' characters';
        return { valid: false, message: 'Too long' };
    }
    
    if (!keywordValidationConfig.pattern.test(value)) {
        input.classList.add('is-invalid');
        feedback.classList.add('invalid');
        feedback.innerHTML = '<i class="fas fa-times-circle"></i>Alphanumeric characters only (no spaces or special characters)';
        return { valid: false, message: 'Invalid characters' };
    }
    
    var existingKeyword = keywordMockData.find(function(k) {
        return k.keyword.toUpperCase() === value;
    });
    
    if (existingKeyword) {
        if (existingKeyword.status === 'Taken') {
            input.classList.add('is-invalid');
            feedback.classList.add('invalid');
            feedback.innerHTML = '<i class="fas fa-times-circle"></i>This keyword is already taken';
            return { valid: false, message: 'Keyword taken' };
        } else {
            input.classList.add('is-valid');
            feedback.classList.add('valid');
            feedback.innerHTML = '<i class="fas fa-check-circle"></i>Keyword available! Click Add to select it.';
            addBtn.disabled = false;
            return { valid: true, message: 'Available', existing: true, id: existingKeyword.id };
        }
    }
    
    console.log('TODO: API call - GET /api/keywords/check-availability?keyword=' + value);
    
    input.classList.add('is-valid');
    feedback.classList.add('valid');
    feedback.innerHTML = '<i class="fas fa-check-circle"></i>Keyword available for reservation!';
    addBtn.disabled = false;
    return { valid: true, message: 'Available', existing: false };
}

function addCustomKeyword() {
    var input = document.getElementById('customKeywordInput');
    var value = input.value.trim().toUpperCase();
    
    var validation = validateCustomKeyword();
    if (!validation.valid) return;
    
    if (validation.existing) {
        if (!keywordSelectedIds.includes(validation.id)) {
            keywordSelectedIds.push(validation.id);
            renderKeywordTable();
            updateKeywordSelection();
        }
        input.value = '';
        document.getElementById('keywordValidationFeedback').innerHTML = '<i class="fas fa-check-circle text-success"></i>Keyword "' + value + '" added to selection!';
        document.getElementById('keywordValidationFeedback').classList.remove('invalid');
        document.getElementById('keywordValidationFeedback').classList.add('valid');
        document.getElementById('addCustomKeywordBtn').disabled = true;
        input.classList.remove('is-valid', 'is-invalid');
    } else {
        var newId = Math.max.apply(null, keywordMockData.map(function(k) { return k.id; })) + 1;
        keywordMockData.push({
            id: newId,
            keyword: value,
            status: 'Available'
        });
        keywordSelectedIds.push(newId);
        renderKeywordTable();
        updateKeywordSelection();
        
        input.value = '';
        document.getElementById('keywordValidationFeedback').innerHTML = '<i class="fas fa-check-circle text-success"></i>New keyword "' + value + '" created and added to selection!';
        document.getElementById('keywordValidationFeedback').classList.remove('invalid');
        document.getElementById('keywordValidationFeedback').classList.add('valid');
        document.getElementById('addCustomKeywordBtn').disabled = true;
        input.classList.remove('is-valid', 'is-invalid');
        
        console.log('TODO: API call - POST /api/keywords/reserve with keyword:', value);
    }
}
</script>
@endpush
