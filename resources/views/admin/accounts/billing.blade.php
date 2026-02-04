@extends('layouts.admin')

@section('title', 'Billing - ' . $account_id)

@push('styles')
<style>
:root {
    --admin-primary: #1e3a5f;
    --admin-primary-hover: #2d5a87;
    --admin-primary-light: rgba(30, 58, 95, 0.08);
}

.admin-page { padding: 1.5rem; }

.billing-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #e9ecef;
}
.billing-header-left {
    display: flex;
    align-items: center;
    gap: 1rem;
}
.billing-header-info h4 {
    color: var(--admin-primary);
    font-weight: 600;
    margin-bottom: 0.25rem;
}
.billing-header-info .account-id-text {
    font-size: 0.875rem;
    color: #6c757d;
}
.billing-header-pills {
    display: flex;
    gap: 0.5rem;
    margin-top: 0.5rem;
}
.billing-header-actions {
    display: flex;
    gap: 0.5rem;
}

.pill-status {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.25rem 0.625rem;
    border-radius: 1rem;
    font-size: 0.75rem;
    font-weight: 600;
}
.pill-live { background: rgba(28, 187, 140, 0.15); color: #1cbb8c; }
.pill-test { background: rgba(108, 117, 125, 0.15); color: #6c757d; }
.pill-suspended { background: rgba(220, 53, 69, 0.15); color: #dc3545; }
.pill-prepaid { background: rgba(30, 58, 95, 0.15); color: var(--admin-primary); }
.pill-postpaid { background: rgba(111, 66, 193, 0.15); color: #6f42c1; }

.billing-summary-bar {
    background: linear-gradient(135deg, #fff 0%, #f0f4f8 100%);
    border: 1px solid rgba(30, 58, 95, 0.2);
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    margin-bottom: 1.5rem;
}
.summary-metric {
    text-align: center;
    padding: 0.875rem 0.5rem;
    border-right: 1px solid rgba(30, 58, 95, 0.1);
}
.summary-metric:last-child {
    border-right: none;
}
.summary-metric .metric-label {
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #6c757d;
    margin-bottom: 0.25rem;
}
.summary-metric .metric-value {
    font-size: 1.1rem;
    font-weight: 700;
    color: #2c2c2c;
}
.summary-metric .metric-value.text-success { color: #1cbb8c !important; }
.summary-metric .metric-value.text-warning { color: #cc9900 !important; }
.summary-metric .metric-value.text-danger { color: #dc3545 !important; }
.summary-metric .metric-value.text-primary { color: var(--admin-primary) !important; }
.summary-metric .metric-timestamp {
    font-size: 0.7rem;
    color: #6c757d;
    margin-top: 0.125rem;
}

.billing-card {
    border: 1px solid #e9ecef;
    border-radius: 0.75rem;
    background: #fff;
    margin-bottom: 1.5rem;
}
.billing-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #e9ecef;
    background: #f8f9fa;
    border-radius: 0.75rem 0.75rem 0 0;
}
.billing-card-header h6 {
    margin: 0;
    font-weight: 600;
    color: var(--admin-primary);
}
.billing-card-body {
    padding: 1.25rem;
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
.btn-admin-outline {
    border-color: var(--admin-primary);
    color: var(--admin-primary);
}
.btn-admin-outline:hover {
    background-color: var(--admin-primary);
    color: #fff;
}

.invoices-table {
    width: 100%;
    font-size: 0.875rem;
}
.invoices-table th {
    background: #f8f9fa;
    font-weight: 600;
    color: #495057;
    padding: 0.75rem;
    border-bottom: 2px solid #e9ecef;
    white-space: nowrap;
}
.invoices-table td {
    padding: 0.75rem;
    vertical-align: middle;
    border-bottom: 1px solid #f1f3f5;
}
.invoices-table tbody tr:hover {
    background: var(--admin-primary-light);
}
.invoice-link {
    color: var(--admin-primary);
    text-decoration: none;
    font-weight: 500;
}
.invoice-link:hover {
    text-decoration: underline;
}

.badge-paid { background: rgba(28, 187, 140, 0.15); color: #1cbb8c; }
.badge-issued { background: rgba(23, 162, 184, 0.15); color: #17a2b8; }
.badge-overdue { background: rgba(220, 53, 69, 0.15); color: #dc3545; }
.badge-void { background: rgba(108, 117, 125, 0.15); color: #6c757d; }
.badge-draft { background: rgba(255, 193, 7, 0.15); color: #856404; }

.invoices-table thead th.sortable {
    cursor: pointer;
    white-space: nowrap;
}
.invoices-table thead th.sortable:hover {
    color: var(--admin-primary);
    background-color: #e9ecef;
}
.invoices-table thead th .sort-icon {
    margin-left: 0.25rem;
    opacity: 0.4;
    font-size: 0.75rem;
}
.invoices-table thead th.sorted .sort-icon {
    opacity: 1;
    color: var(--admin-primary);
}
.invoices-table tbody tr {
    cursor: pointer;
    transition: background-color 0.15s ease;
}
.invoices-table tbody tr:hover td {
    background-color: #f8f9fa;
}
.invoices-table .actions-cell {
    white-space: nowrap;
}
.invoices-table .actions-cell .btn {
    padding: 0.25rem 0.5rem;
}
.filter-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    background: var(--admin-primary);
    color: #fff;
    padding: 0.25rem 0.5rem;
    border-radius: 1rem;
    font-size: 0.75rem;
}
.filter-chip .chip-remove {
    cursor: pointer;
    opacity: 0.7;
}
.filter-chip .chip-remove:hover {
    opacity: 1;
}

.billing-setting-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f1f3f5;
}
.billing-setting-row:last-child {
    border-bottom: none;
}
.billing-setting-label {
    font-weight: 500;
    color: #495057;
}
.billing-setting-value {
    font-weight: 600;
    color: #2c2c2c;
}

/* Segmented Toggle Control */
.segmented-toggle {
    display: inline-flex;
    background: #f1f3f5;
    border-radius: 0.5rem;
    padding: 3px;
    gap: 2px;
}
.segmented-toggle .toggle-option {
    padding: 0.375rem 1rem;
    border-radius: 0.375rem;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    color: #6c757d;
    border: none;
    background: transparent;
}
.segmented-toggle .toggle-option:hover:not(.active):not(:disabled) {
    background: rgba(30, 58, 95, 0.08);
    color: #495057;
}
.segmented-toggle .toggle-option.active {
    background: var(--admin-primary);
    color: #fff;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
.segmented-toggle .toggle-option:disabled {
    cursor: not-allowed;
    opacity: 0.6;
}
.segmented-toggle.readonly .toggle-option {
    cursor: default;
}
.segmented-toggle.readonly .toggle-option:hover:not(.active) {
    background: transparent;
    color: #6c757d;
}

/* Billing Risk Warning Banner */
.billing-risk-banner {
    background: linear-gradient(135deg, rgba(255, 193, 7, 0.1) 0%, rgba(255, 193, 7, 0.05) 100%);
    border: 1px solid rgba(255, 193, 7, 0.4);
    border-radius: 0.5rem;
    padding: 0.75rem 1rem;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}
.billing-risk-banner.hidden { display: none; }
.billing-risk-banner .risk-icon {
    color: #856404;
    font-size: 1.25rem;
}
.billing-risk-banner .risk-text {
    flex: 1;
    font-size: 0.875rem;
    color: #856404;
}
.billing-risk-banner .risk-text strong {
    display: block;
    margin-bottom: 0.125rem;
}

/* Inline Error Message */
.billing-inline-error {
    background: rgba(220, 53, 69, 0.1);
    border: 1px solid rgba(220, 53, 69, 0.3);
    border-radius: 0.375rem;
    padding: 0.5rem 0.75rem;
    margin-top: 0.75rem;
    font-size: 0.8rem;
    color: #dc3545;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.billing-inline-error.hidden { display: none; }
.permission-lock.hidden { display: none; }

/* Permission Lock Indicator */
.permission-lock {
    font-size: 0.75rem;
    color: #6c757d;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

/* Confirmation Modal Styling */
.billing-confirm-modal .modal-header {
    border-bottom: 1px solid #e9ecef;
    padding: 1rem 1.25rem;
}
.billing-confirm-modal .modal-title {
    font-weight: 600;
    color: var(--admin-primary);
}
.billing-confirm-modal .modal-body {
    padding: 1.25rem;
}
.billing-confirm-modal .modal-footer {
    border-top: 1px solid #e9ecef;
    padding: 1rem 1.25rem;
}
.billing-confirm-modal .change-summary {
    background: #f8f9fa;
    border-radius: 0.5rem;
    padding: 1rem;
    margin-bottom: 1rem;
}
.billing-confirm-modal .change-arrow {
    color: #6c757d;
    font-size: 1.25rem;
}

/* Inline Edit Controls */
.inline-edit-container {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.inline-edit-value {
    font-weight: 600;
    color: #2c2c2c;
}
.inline-edit-btn {
    padding: 0.125rem 0.5rem;
    font-size: 0.75rem;
    border: 1px solid var(--admin-primary);
    background: transparent;
    color: var(--admin-primary);
    border-radius: 0.25rem;
    cursor: pointer;
    transition: all 0.2s ease;
}
.inline-edit-btn:hover {
    background: var(--admin-primary);
    color: #fff;
}
.inline-edit-btn.hidden { display: none; }
.inline-edit-input-group {
    display: none;
    align-items: center;
    gap: 0.375rem;
}
.inline-edit-input-group.active {
    display: flex;
}
.inline-edit-input {
    width: 120px;
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
    border: 1px solid #ced4da;
    border-radius: 0.25rem;
}
.inline-edit-input:focus {
    outline: none;
    border-color: var(--admin-primary);
    box-shadow: 0 0 0 2px rgba(30, 58, 95, 0.15);
}
.inline-edit-input.is-invalid {
    border-color: #dc3545;
}
.inline-edit-save {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
    background: var(--admin-primary);
    color: #fff;
    border: none;
    border-radius: 0.25rem;
    cursor: pointer;
}
.inline-edit-save:hover { background: var(--admin-primary-hover); }
.inline-edit-save:disabled { opacity: 0.6; cursor: not-allowed; }
.inline-edit-cancel {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
    background: transparent;
    color: #6c757d;
    border: 1px solid #ced4da;
    border-radius: 0.25rem;
    cursor: pointer;
}
.inline-edit-cancel:hover { background: #f8f9fa; }
.inline-edit-helper {
    font-size: 0.7rem;
    color: #6c757d;
    margin-top: 0.25rem;
}
.inline-edit-error {
    font-size: 0.75rem;
    color: #dc3545;
    margin-top: 0.25rem;
    display: none;
}
.inline-edit-error.show { display: block; }
.credit-limit-row {
    flex-direction: column;
    align-items: flex-start !important;
}
.credit-limit-row .billing-setting-label {
    margin-bottom: 0.25rem;
}
.credit-limit-content {
    width: 100%;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.empty-state {
    text-align: center;
    padding: 3rem 1rem;
    color: #6c757d;
}
.empty-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

.loading-state {
    text-align: center;
    padding: 2rem;
    color: #6c757d;
}

@media (max-width: 768px) {
    .summary-metric {
        border-right: none;
        border-bottom: 1px solid rgba(30, 58, 95, 0.1);
        padding: 0.5rem;
    }
    .summary-metric:last-child {
        border-bottom: none;
    }
    .billing-header {
        flex-direction: column;
        gap: 1rem;
    }
    .billing-header-actions {
        width: 100%;
        justify-content: flex-start;
    }
}
</style>
@endpush

@section('content')
<div class="admin-page" id="billingPageContent">
    <div class="row page-titles">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.accounts.overview') }}">Accounts</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.accounts.details', ['accountId' => $account_id]) }}">{{ $account_id }}</a></li>
            <li class="breadcrumb-item active">Billing</li>
        </ol>
    </div>

    <div class="billing-header">
        <div class="billing-header-left">
            <div class="billing-header-info">
                <h4 id="customerName">Loading...</h4>
                <div class="account-id-text">{{ $account_id }}</div>
                <div class="billing-header-pills">
                    <span class="pill-status pill-live" id="statusPill"><i class="fas fa-circle me-1" style="font-size: 0.5rem;"></i>Live</span>
                    <span class="pill-status pill-prepaid" id="billingModePill"><i class="fas fa-wallet me-1"></i>Prepaid</span>
                </div>
            </div>
        </div>
        <div class="billing-header-actions">
            <button class="btn btn-admin-primary btn-sm" id="createInvoiceBtn" style="display: none;">
                <i class="fas fa-file-invoice me-1"></i>Create Invoice
            </button>
            <button class="btn btn-admin-outline btn-sm" id="createCreditBtn" style="display: none;">
                <i class="fas fa-credit-card me-1"></i>Create Credit
            </button>
            <a href="#" class="btn btn-admin-outline btn-sm" id="hubspotLink" target="_blank">
                <i class="fas fa-external-link-alt me-1"></i>View in HubSpot
            </a>
            <a href="{{ route('admin.accounts.details', ['accountId' => $account_id]) }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i>Back to Account
            </a>
        </div>
    </div>

    <div class="billing-summary-bar" id="billingSummaryBar">
        <div class="row align-items-center g-0">
            <div class="col-6 col-md-4 col-lg-2 summary-metric">
                <div class="metric-label">Billing Mode</div>
                <div class="metric-value" id="summaryBillingMode">
                    <span class="pill-status pill-prepaid" style="font-size: 0.8rem;"><i class="fas fa-wallet me-1"></i>Prepaid</span>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2 summary-metric">
                <div class="metric-label">Current Balance</div>
                <div class="metric-value text-primary" id="summaryCurrentBalance">&pound;0.00</div>
            </div>
            <div class="col-6 col-md-4 col-lg-2 summary-metric">
                <div class="metric-label">Credit Limit</div>
                <div class="metric-value" id="summaryCreditLimit">&pound;0.00</div>
            </div>
            <div class="col-6 col-md-4 col-lg-2 summary-metric">
                <div class="metric-label">Available Credit</div>
                <div class="metric-value text-success" id="summaryAvailableCredit">&pound;0.00</div>
            </div>
            <div class="col-6 col-md-4 col-lg-2 summary-metric">
                <div class="metric-label">Account Status</div>
                <div class="metric-value" id="summaryAccountStatus">
                    <span class="pill-status pill-live" style="font-size: 0.8rem;"><i class="fas fa-check-circle me-1"></i>Active</span>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2 summary-metric">
                <div class="metric-label">Last Updated</div>
                <div class="metric-value" id="summaryLastUpdated" style="font-size: 0.875rem;">--</div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4">
            <div class="billing-card">
                <div class="billing-card-header">
                    <h6><i class="fas fa-cog me-2"></i>Billing Settings</h6>
                </div>
                <div class="billing-card-body">
                    <!-- Billing Risk Warning Banner -->
                    <div class="billing-risk-banner hidden" id="billingRiskBanner">
                        <i class="fas fa-exclamation-triangle risk-icon"></i>
                        <div class="risk-text">
                            <strong>Outstanding Invoices</strong>
                            <span id="riskBannerMessage">This account has unpaid invoices. Billing mode changes are restricted.</span>
                        </div>
                    </div>
                    
                    <div class="billing-setting-row">
                        <span class="billing-setting-label">
                            Billing Type
                            <span class="permission-lock hidden" id="permissionLock">
                                <i class="fas fa-lock"></i> Read only
                            </span>
                        </span>
                        <div id="billingTypeControl">
                            <div class="segmented-toggle" id="billingTypeToggle">
                                <button type="button" class="toggle-option" data-value="prepaid">Prepaid</button>
                                <button type="button" class="toggle-option" data-value="postpaid">Postpaid</button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Inline Error Message -->
                    <div class="billing-inline-error hidden" id="billingModeError">
                        <i class="fas fa-times-circle"></i>
                        <span id="billingModeErrorText">Could not update HubSpot. No changes were saved.</span>
                    </div>
                    
                    <div class="billing-setting-row credit-limit-row" id="creditLimitRow">
                        <span class="billing-setting-label">Credit Limit</span>
                        <div class="credit-limit-content">
                            <div class="inline-edit-container">
                                <span class="inline-edit-value" id="settingCreditLimit">&pound;0.00</span>
                                <button type="button" class="inline-edit-btn hidden" id="creditLimitEditBtn">
                                    <i class="fas fa-pencil-alt"></i> Edit
                                </button>
                            </div>
                            <div class="inline-edit-input-group" id="creditLimitInputGroup">
                                <span class="text-muted">&pound;</span>
                                <input type="number" class="inline-edit-input" id="creditLimitInput" 
                                       min="0" max="1000000" step="0.01" placeholder="0.00">
                                <button type="button" class="inline-edit-save" id="creditLimitSaveBtn">
                                    <i class="fas fa-check"></i>
                                </button>
                                <button type="button" class="inline-edit-cancel" id="creditLimitCancelBtn">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <div class="inline-edit-helper">Credit limit is stored in HubSpot and will be synced.</div>
                        <div class="inline-edit-error" id="creditLimitError">Could not update HubSpot. No changes were saved.</div>
                    </div>
                    <div class="billing-setting-row">
                        <span class="billing-setting-label">Payment Terms</span>
                        <span class="billing-setting-value" id="settingPaymentTerms">Immediate</span>
                    </div>
                    <div class="billing-setting-row">
                        <span class="billing-setting-label">Currency</span>
                        <span class="billing-setting-value" id="settingCurrency">GBP (&pound;)</span>
                    </div>
                    <div class="billing-setting-row">
                        <span class="billing-setting-label">VAT Registered</span>
                        <span class="billing-setting-value" id="settingVatRegistered">Yes</span>
                    </div>
                    <div class="mt-3 text-muted small">
                        <i class="fas fa-info-circle me-1"></i>Billing settings are synced with HubSpot.
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-8">
            <div class="billing-card" id="invoicesCard">
                <div class="billing-card-header">
                    <h6><i class="fas fa-file-invoice me-2"></i>Customer Invoices</h6>
                    <div id="invoicesHeaderActions" class="d-flex gap-2">
                        <button class="btn btn-sm btn-admin-outline" data-bs-toggle="collapse" data-bs-target="#invoiceFiltersPanel">
                            <i class="fas fa-filter me-1"></i>Filters
                        </button>
                        <button class="btn btn-sm btn-admin-outline" id="exportInvoicesBtn">
                            <i class="fas fa-download me-1"></i>Export
                        </button>
                    </div>
                </div>
                
                <div id="permissionDeniedState" class="billing-card-body text-center py-5" style="display: none;">
                    <i class="fas fa-lock fa-3x text-muted mb-3"></i>
                    <h6 class="text-muted">Permission Required</h6>
                    <p class="text-muted small mb-0">You do not have permission to view invoices for this account.</p>
                </div>
                
                <div id="invoicesContent" class="billing-card-body p-0">
                    <div class="collapse p-3 bg-light border-bottom" id="invoiceFiltersPanel">
                        <div class="row g-3 align-items-end">
                            <div class="col-6 col-md-3">
                                <label class="form-label small fw-bold">Invoice Number</label>
                                <input type="text" class="form-control form-control-sm" id="invoiceNumberFilter" placeholder="Search..." style="background-color: #fff;">
                            </div>
                            <div class="col-6 col-md-3">
                                <label class="form-label small fw-bold">Status</label>
                                <select class="form-select form-select-sm" id="statusFilter" style="background-color: #fff;">
                                    <option value="">All Statuses</option>
                                    <option value="draft">Draft</option>
                                    <option value="issued">Issued</option>
                                    <option value="paid">Paid</option>
                                    <option value="overdue">Overdue</option>
                                    <option value="void">Void</option>
                                </select>
                            </div>
                            <div class="col-6 col-md-3">
                                <label class="form-label small fw-bold">Year</label>
                                <select class="form-select form-select-sm" id="yearFilter" style="background-color: #fff;">
                                    <option value="">All Years</option>
                                    <option value="2026">2026</option>
                                    <option value="2025">2025</option>
                                    <option value="2024">2024</option>
                                    <option value="2023">2023</option>
                                </select>
                            </div>
                            <div class="col-6 col-md-3 d-flex gap-2">
                                <button type="button" class="btn btn-admin-primary btn-sm flex-grow-1" id="applyInvoiceFilters">
                                    <i class="fas fa-check me-1"></i>Apply
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm flex-grow-1" id="resetInvoiceFilters">
                                    <i class="fas fa-undo me-1"></i>Reset
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div id="activeInvoiceFilters" class="px-3 py-2 border-bottom bg-light" style="display: none;">
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <span class="small text-muted fw-bold">Active Filters:</span>
                            <div id="activeFilterChips" class="d-flex flex-wrap gap-1"></div>
                        </div>
                    </div>
                    
                    <div id="invoicesTableWrapper">
                        <div class="table-responsive">
                            <table class="invoices-table" id="customerInvoicesTable">
                                <thead>
                                    <tr>
                                        <th class="sortable" data-sort="number">Invoice # <i class="fas fa-sort sort-icon"></i></th>
                                        <th class="sortable" data-sort="period">Period <i class="fas fa-sort sort-icon"></i></th>
                                        <th class="sortable" data-sort="date">Date <i class="fas fa-sort sort-icon"></i></th>
                                        <th class="sortable" data-sort="dueDate">Due Date <i class="fas fa-sort sort-icon"></i></th>
                                        <th>Status</th>
                                        <th class="text-end sortable" data-sort="amountExVat">Amount (ex VAT) <i class="fas fa-sort sort-icon"></i></th>
                                        <th class="text-end">VAT</th>
                                        <th class="text-end sortable" data-sort="total">Total <i class="fas fa-sort sort-icon"></i></th>
                                        <th class="text-end">Outstanding</th>
                                        <th class="text-center" style="width: 80px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="invoicesTableBody">
                                    <tr>
                                        <td colspan="10" class="loading-state">
                                            <i class="fas fa-spinner fa-spin me-2"></i>Loading invoices...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <div id="invoicesErrorState" class="text-center py-5" style="display: none;">
                            <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                            <h6 class="text-muted">Failed to Load Invoices</h6>
                            <p class="text-muted small mb-3">There was an error loading invoices. Please try again.</p>
                            <button type="button" class="btn btn-admin-primary btn-sm" id="retryLoadInvoices">
                                <i class="fas fa-redo me-1"></i>Retry
                            </button>
                        </div>
                    </div>
                    
                    <div class="invoices-footer border-top p-3" id="invoicesPagination">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-muted small">
                                Showing <span id="showingStart">0</span>-<span id="showingEnd">0</span> of <span id="totalInvoiceCount">0</span> invoices
                            </div>
                            <nav>
                                <ul class="pagination pagination-sm mb-0" id="paginationContainer">
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Billing Mode Change Confirmation Modal -->
<div class="modal fade billing-confirm-modal" id="billingModeConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-exchange-alt me-2"></i>Change Billing Type</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="change-summary">
                    <div class="d-flex align-items-center justify-content-center gap-3">
                        <div class="text-center">
                            <div class="text-muted small mb-1">Current</div>
                            <span class="pill-status" id="confirmOldMode">Prepaid</span>
                        </div>
                        <i class="fas fa-arrow-right change-arrow"></i>
                        <div class="text-center">
                            <div class="text-muted small mb-1">New</div>
                            <span class="pill-status" id="confirmNewMode">Postpaid</span>
                        </div>
                    </div>
                </div>
                <p class="mb-0 text-center">This will affect billing behaviour for this customer. Continue?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-admin-primary" id="confirmBillingModeChange">
                    <i class="fas fa-check me-1"></i>Confirm Change
                </button>
            </div>
        </div>
    </div>
</div>

@include('admin.partials.create-invoice-credit-modal')
@endsection

@push('scripts')
<script src="{{ asset('js/admin-control-plane.js') }}"></script>
<script src="{{ asset('js/billing-services.js') }}"></script>
<script src="{{ asset('js/invoice-credit-modal.js') }}"></script>
<script>
/**
 * AdminAccountBillingService - Adapter Layer
 * 
 * This adapter bridges the billing page with the unified BillingServices layer.
 * It provides backward compatibility while using the new service architecture.
 * 
 * Backend Integration:
 * - Set BillingServices.config.useMockData = false to use real APIs
 * - All service calls flow through the unified BillingServices module
 */
var AdminAccountBillingService = (function() {
    var Services = window.BillingServices;
    
    if (!Services) {
        console.error('[AdminAccountBillingService] BillingServices not loaded');
        return {
            getAccountBilling: function() { return Promise.reject(new Error('BillingServices not available')); },
            getAccountInvoices: function() { return Promise.reject(new Error('BillingServices not available')); },
            calculateAvailableCredit: function() { return 0; },
            updateBillingMode: function() { return Promise.reject(new Error('BillingServices not available')); },
            updateCreditLimit: function() { return Promise.reject(new Error('BillingServices not available')); }
        };
    }
    
    return {
        getAccountBilling: function(accountId) {
            return Services.BillingFacade.loadCompleteBillingData(accountId);
        },
        
        getAccountInvoices: function(accountId) {
            return Services.InvoicesService.listInvoices({ customerAccountId: accountId })
                .then(function(result) {
                    return result.invoices;
                });
        },
        
        calculateAvailableCredit: function(billingData) {
            return Services.InternalBillingLedgerService.calculateAvailableCredit(
                billingData.billingMode,
                billingData.currentBalance,
                billingData.creditLimit
            );
        },
        
        updateBillingMode: function(accountId, newMode) {
            return Services.HubSpotBillingService.updateBillingMode(accountId, newMode)
                .then(function(response) {
                    if (response.success) {
                        return { success: true, accountId: accountId, billingMode: newMode };
                    }
                    throw new Error(response.error || 'Failed to update billing mode');
                });
        },
        
        updateCreditLimit: function(accountId, newLimit) {
            return Services.HubSpotBillingService.updateCreditLimit(accountId, newLimit)
                .then(function(response) {
                    if (response.success) {
                        return { success: true, accountId: accountId, creditLimit: newLimit };
                    }
                    throw new Error(response.error || 'Failed to update credit limit');
                });
        }
    };
})();

/**
 * Legacy compatibility services - now delegate to BillingServices
 */
var HubSpotBillingService = (function() {
    var Services = window.BillingServices;
    var simulateFailure = false;
    
    return {
        updateBillingMode: function(accountId, mode) {
            if (simulateFailure) {
                return Promise.reject(new Error('HubSpot API error: Unable to update billing mode'));
            }
            return Services.HubSpotBillingService.updateBillingMode(accountId, mode);
        },
        
        updateCreditLimit: function(accountId, newLimit) {
            if (simulateFailure) {
                return Promise.reject(new Error('HubSpot API error: Unable to update credit limit'));
            }
            return Services.HubSpotBillingService.updateCreditLimit(accountId, newLimit);
        },
        
        setSimulateFailure: function(value) {
            simulateFailure = value;
        }
    };
})();

var InternalBillingConfigService = (function() {
    return {
        updateBillingMode: function(accountId, mode) {
            console.log('[InternalBillingConfigService] Updated billing mode for ' + accountId + ' to ' + mode);
            return Promise.resolve({
                success: true,
                accountId: accountId,
                billingMode: mode,
                effectiveDate: new Date().toISOString()
            });
        }
    };
})();

var BillingRiskService = (function() {
    var Services = window.BillingServices;
    
    return {
        checkBillingRisk: function(accountId) {
            return Services.BillingFacade.checkOutstandingInvoices(accountId)
                .then(function(result) {
                    return {
                        hasOutstandingInvoices: result.hasOutstanding,
                        overdueAmount: result.totalOutstanding,
                        overdueCount: result.count
                    };
                });
        }
    };
})();

var AdminPermissionService = (function() {
    var mockPermissions = {
        'accounts.view_billing': true,
        'billing.edit_mode': true,
        'billing.override_risk': false,
        'billing.edit_credit_limit': true,
        'billing.create_invoice': true,
        'billing.create_credit': true,
        'billing.view_invoices': true
    };
    
    return {
        hasPermission: function(permission) {
            return mockPermissions[permission] === true;
        },
        
        setPermission: function(permission, value) {
            mockPermissions[permission] = value;
        },
        
        getAllPermissions: function() {
            return Object.assign({}, mockPermissions);
        }
    };
})();

/**
 * Admin Billing Audit Logger
 * Logs billing-related admin actions with required metadata
 * Uses admin-only audit trail (not customer logs)
 */
var AdminBillingAuditLogger = (function() {
    var SOURCE_SCREEN = 'Admin > Accounts > Billing';
    
    function getAdminContext() {
        var adminUser = window.AdminControlPlane ? AdminControlPlane.getCurrentUser() : null;
        return {
            adminUserId: adminUser ? adminUser.id : 'unknown',
            adminEmail: adminUser ? adminUser.email : 'admin@quicksms.co.uk',
            adminRole: adminUser ? adminUser.role : 'super_admin',
            adminName: adminUser ? adminUser.name : 'System Administrator',
            ipAddress: null,
            userAgent: navigator.userAgent
        };
    }
    
    function generateAuditId() {
        return 'BAUD-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9).toUpperCase();
    }
    
    function logAuditEvent(eventType, customerAccountId, details, oldValue, newValue) {
        var context = getAdminContext();
        var auditEntry = {
            auditId: generateAuditId(),
            timestamp: new Date().toISOString(),
            timestampUnix: Date.now(),
            eventType: eventType,
            sourceScreen: SOURCE_SCREEN,
            adminUser: {
                id: context.adminUserId,
                email: context.adminEmail,
                role: context.adminRole,
                name: context.adminName
            },
            customerAccountId: customerAccountId,
            oldValue: oldValue !== undefined ? oldValue : null,
            newValue: newValue !== undefined ? newValue : null,
            details: details || {},
            ipAddress: context.ipAddress,
            userAgent: context.userAgent,
            success: true
        };
        
        console.log('[ADMIN_BILLING_AUDIT]', JSON.stringify(auditEntry, null, 2));
        
        if (window.AdminControlPlane && AdminControlPlane.logAdminAction) {
            AdminControlPlane.logAdminAction(eventType, customerAccountId, details, 
                oldValue ? { value: oldValue } : null, 
                newValue ? { value: newValue } : null
            );
        }
        
        return auditEntry;
    }
    
    function logFailure(eventType, customerAccountId, error, referenceId) {
        var context = getAdminContext();
        var auditEntry = {
            auditId: generateAuditId(),
            timestamp: new Date().toISOString(),
            timestampUnix: Date.now(),
            eventType: eventType + '_FAILED',
            sourceScreen: SOURCE_SCREEN,
            adminUser: {
                id: context.adminUserId,
                email: context.adminEmail,
                role: context.adminRole,
                name: context.adminName
            },
            customerAccountId: customerAccountId,
            error: error.message || String(error),
            referenceId: referenceId || null,
            success: false
        };
        
        console.error('[ADMIN_BILLING_AUDIT][FAILURE]', JSON.stringify(auditEntry, null, 2));
        
        return auditEntry;
    }
    
    return {
        logBillingModeChanged: function(customerAccountId, oldMode, newMode) {
            return logAuditEvent('BILLING_MODE_CHANGED', customerAccountId, {
                action: 'Billing type changed from ' + oldMode + ' to ' + newMode,
                hubspotSync: true
            }, oldMode, newMode);
        },
        
        logBillingModeChangeFailed: function(customerAccountId, attemptedMode, error) {
            return logFailure('BILLING_MODE_CHANGE', customerAccountId, error, attemptedMode);
        },
        
        logCreditLimitChanged: function(customerAccountId, oldLimit, newLimit, currency) {
            return logAuditEvent('CREDIT_LIMIT_CHANGED', customerAccountId, {
                action: 'Credit limit changed',
                currency: currency || 'GBP',
                hubspotSync: true
            }, oldLimit, newLimit);
        },
        
        logCreditLimitChangeFailed: function(customerAccountId, attemptedLimit, error) {
            return logFailure('CREDIT_LIMIT_CHANGE', customerAccountId, error, attemptedLimit);
        },
        
        logInvoiceCreated: function(customerAccountId, invoiceNumber, total) {
            return logAuditEvent('INVOICE_CREATED', customerAccountId, {
                action: 'Invoice created',
                invoiceNumber: invoiceNumber,
                total: total
            }, null, invoiceNumber);
        },
        
        logCreditCreated: function(customerAccountId, creditNumber, total) {
            return logAuditEvent('CREDIT_NOTE_CREATED', customerAccountId, {
                action: 'Credit note created',
                creditNumber: creditNumber,
                total: total
            }, null, creditNumber);
        },
        
        logPageViewed: function(customerAccountId) {
            return logAuditEvent('BILLING_PAGE_VIEWED', customerAccountId, {
                action: 'Admin viewed billing page'
            });
        }
    };
})();

document.addEventListener('DOMContentLoaded', function() {
    var accountId = '{{ $account_id }}';
    
    // Page-level access check
    if (!AdminPermissionService.hasPermission('accounts.view_billing')) {
        document.getElementById('billingPageContent').innerHTML = 
            '<div class="card shadow-none border">' +
            '<div class="card-body text-center py-5">' +
            '<i class="fas fa-lock fa-4x text-muted mb-4"></i>' +
            '<h4 class="text-muted">Not Authorised</h4>' +
            '<p class="text-muted mb-4">You do not have permission to view billing information for this account.</p>' +
            '<a href="/admin/accounts" class="btn btn-admin-primary">' +
            '<i class="fas fa-arrow-left me-2"></i>Back to Accounts</a>' +
            '</div></div>';
        return;
    }
    
    // Log page view
    AdminBillingAuditLogger.logPageViewed(accountId);
    
    // Apply permission-based UI states
    applyPermissionStates();
    
    function applyPermissionStates() {
        var canEditMode = AdminPermissionService.hasPermission('billing.edit_mode');
        var canEditCreditLimit = AdminPermissionService.hasPermission('billing.edit_credit_limit');
        var canCreateInvoice = AdminPermissionService.hasPermission('billing.create_invoice');
        var canCreateCredit = AdminPermissionService.hasPermission('billing.create_credit');
        
        // Hide Create Invoice button if no permission
        if (!canCreateInvoice) {
            var createInvoiceBtn = document.getElementById('createInvoiceBtn');
            if (createInvoiceBtn) createInvoiceBtn.style.display = 'none';
        }
        
        // Hide Create Credit button if no permission
        if (!canCreateCredit) {
            var createCreditBtn = document.getElementById('createCreditBtn');
            if (createCreditBtn) createCreditBtn.style.display = 'none';
        }
        
        // Make billing type toggle read-only if no edit permission
        if (!canEditMode) {
            var billingTypeToggle = document.getElementById('billingTypeToggle');
            if (billingTypeToggle) {
                billingTypeToggle.classList.add('readonly');
                var buttons = billingTypeToggle.querySelectorAll('button');
                buttons.forEach(function(btn) {
                    btn.disabled = true;
                });
            }
        }
        
        // Hide credit limit edit button if no edit permission
        if (!canEditCreditLimit) {
            var creditLimitEditBtn = document.getElementById('creditLimitEditBtn');
            if (creditLimitEditBtn) creditLimitEditBtn.style.display = 'none';
        }
    }
    
    function formatCurrency(amount) {
        return '£' + Math.abs(amount).toLocaleString('en-GB', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
    
    function formatDate(dateStr) {
        var date = new Date(dateStr);
        return date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
    }
    
    function formatTimestamp(dateStr) {
        var date = new Date(dateStr);
        return date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) + 
               ' ' + date.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' });
    }
    
    function getStatusPillClass(status) {
        switch(status.toLowerCase()) {
            case 'live': return 'pill-live';
            case 'test': return 'pill-test';
            case 'suspended': return 'pill-suspended';
            default: return 'pill-test';
        }
    }
    
    function getBillingModePillClass(mode) {
        return mode === 'postpaid' ? 'pill-postpaid' : 'pill-prepaid';
    }
    
    function getInvoiceStatusBadge(status) {
        var badgeClass = 'badge-' + status.toLowerCase();
        var label = status.charAt(0).toUpperCase() + status.slice(1);
        return '<span class="badge ' + badgeClass + '">' + label + '</span>';
    }
    
    AdminAccountBillingService.getAccountBilling(accountId).then(function(data) {
        document.getElementById('customerName').textContent = data.name;
        
        var statusPill = document.getElementById('statusPill');
        statusPill.className = 'pill-status ' + getStatusPillClass(data.status);
        statusPill.innerHTML = '<i class="fas fa-circle me-1" style="font-size: 0.5rem;"></i>' + 
            data.status.charAt(0).toUpperCase() + data.status.slice(1);
        
        var billingModePill = document.getElementById('billingModePill');
        billingModePill.className = 'pill-status ' + getBillingModePillClass(data.billingMode);
        billingModePill.innerHTML = '<i class="fas fa-' + (data.billingMode === 'postpaid' ? 'credit-card' : 'wallet') + 
            ' me-1"></i>' + data.billingMode.charAt(0).toUpperCase() + data.billingMode.slice(1);
        
        if (data.hubspotId) {
            document.getElementById('hubspotLink').href = 'https://app.hubspot.com/contacts/company/' + data.hubspotId;
        } else {
            document.getElementById('hubspotLink').classList.add('d-none');
        }
        
        document.getElementById('summaryBillingMode').innerHTML = 
            '<span class="pill-status ' + getBillingModePillClass(data.billingMode) + '" style="font-size: 0.8rem;">' +
            '<i class="fas fa-' + (data.billingMode === 'postpaid' ? 'credit-card' : 'wallet') + ' me-1"></i>' +
            data.billingMode.charAt(0).toUpperCase() + data.billingMode.slice(1) + '</span>';
        
        var balanceEl = document.getElementById('summaryCurrentBalance');
        balanceEl.textContent = (data.currentBalance < 0 ? '-' : '') + formatCurrency(data.currentBalance);
        balanceEl.className = 'metric-value ' + (data.currentBalance < 0 ? 'text-danger' : 'text-primary');
        
        document.getElementById('summaryCreditLimit').textContent = formatCurrency(data.creditLimit);
        
        var availableCredit = AdminAccountBillingService.calculateAvailableCredit(data);
        var availableCreditEl = document.getElementById('summaryAvailableCredit');
        availableCreditEl.textContent = (availableCredit < 0 ? '-' : '') + formatCurrency(availableCredit);
        availableCreditEl.className = 'metric-value ' + (availableCredit <= 0 ? 'text-danger' : 'text-success');
        
        var accountStatusEl = document.getElementById('summaryAccountStatus');
        var statusLabel = data.status === 'suspended' ? 'Suspended' : 'Active';
        var statusClass = data.status === 'suspended' ? 'pill-suspended' : 'pill-live';
        var statusIcon = data.status === 'suspended' ? 'ban' : 'check-circle';
        accountStatusEl.innerHTML = '<span class="pill-status ' + statusClass + '" style="font-size: 0.8rem;">' +
            '<i class="fas fa-' + statusIcon + ' me-1"></i>' + statusLabel + '</span>';
        
        document.getElementById('summaryLastUpdated').textContent = formatTimestamp(data.lastUpdated);
        
        document.getElementById('settingCreditLimit').textContent = formatCurrency(data.creditLimit);
        document.getElementById('settingPaymentTerms').textContent = data.paymentTerms;
        document.getElementById('settingCurrency').textContent = data.currency + ' (£)';
        document.getElementById('settingVatRegistered').textContent = data.vatRegistered ? 'Yes' : 'No';
        
        initBillingTypeToggle(data.billingMode, data.name);
        
        if (typeof AdminControlPlane !== 'undefined') {
            AdminControlPlane.logAdminAction('ACCOUNT_BILLING_VIEWED', accountId, { accountName: data.name });
        }
    }).catch(function(error) {
        console.error('[AdminAccountBilling] Failed to load billing data:', error);
        document.getElementById('customerName').textContent = 'Error loading account';
        document.getElementById('summaryBillingMode').innerHTML = '<span class="text-muted">--</span>';
        document.getElementById('summaryCurrentBalance').innerHTML = '<span class="text-muted">--</span>';
        document.getElementById('summaryCreditLimit').innerHTML = '<span class="text-muted">--</span>';
        document.getElementById('summaryAvailableCredit').innerHTML = '<span class="text-muted">--</span>';
        document.getElementById('summaryAccountStatus').innerHTML = '<span class="text-muted">--</span>';
        document.getElementById('summaryLastUpdated').innerHTML = '<span class="text-muted">--</span>';
        
        var errorAlert = document.createElement('div');
        errorAlert.className = 'alert alert-danger mt-3';
        errorAlert.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>Failed to load billing data. Please refresh the page or contact support.';
        document.getElementById('billingPageContent').prepend(errorAlert);
    });
    
    var currentBillingMode = null;
    var pendingBillingMode = null;
    var canEditBillingMode = true;
    var hasOutstandingInvoices = false;
    var canOverrideRisk = false;
    var currentAccountName = '';
    
    function initBillingTypeToggle(billingMode, accountName) {
        currentBillingMode = billingMode;
        currentAccountName = accountName;
        
        var toggle = document.getElementById('billingTypeToggle');
        var toggleOptions = toggle.querySelectorAll('.toggle-option');
        
        toggleOptions.forEach(function(option) {
            if (option.dataset.value === billingMode) {
                option.classList.add('active');
            } else {
                option.classList.remove('active');
            }
        });
        
        canEditBillingMode = AdminPermissionService.hasPermission('billing.edit_mode');
        canOverrideRisk = AdminPermissionService.hasPermission('billing.override_risk');
        
        if (!canEditBillingMode) {
            toggle.classList.add('readonly');
            toggleOptions.forEach(function(option) {
                option.disabled = true;
            });
            document.getElementById('permissionLock').classList.remove('hidden');
        }
        
        BillingRiskService.checkBillingRisk(accountId).then(function(risk) {
            hasOutstandingInvoices = risk.hasOutstandingInvoices;
            
            if (hasOutstandingInvoices) {
                var banner = document.getElementById('billingRiskBanner');
                banner.classList.remove('hidden');
                
                var message = 'This account has ';
                if (risk.overdueAmount > 0) {
                    message += formatCurrency(risk.overdueAmount) + ' in overdue invoices (' + risk.overdueCount + ' invoice' + (risk.overdueCount > 1 ? 's' : '') + ').';
                } else {
                    message += 'outstanding invoices that need attention.';
                }
                message += ' Billing mode changes are ' + (canOverrideRisk ? 'restricted but can be overridden.' : 'restricted.');
                document.getElementById('riskBannerMessage').textContent = message;
                
                if (!canOverrideRisk) {
                    toggle.classList.add('readonly');
                    toggleOptions.forEach(function(option) {
                        option.disabled = true;
                    });
                }
            }
        });
        
        toggleOptions.forEach(function(option) {
            option.addEventListener('click', function() {
                if (option.disabled || toggle.classList.contains('readonly')) return;
                if (option.classList.contains('active')) return;
                
                pendingBillingMode = option.dataset.value;
                showBillingModeConfirmModal(currentBillingMode, pendingBillingMode);
            });
        });
    }
    
    function showBillingModeConfirmModal(oldMode, newMode) {
        var oldModeEl = document.getElementById('confirmOldMode');
        var newModeEl = document.getElementById('confirmNewMode');
        
        oldModeEl.className = 'pill-status ' + getBillingModePillClass(oldMode);
        oldModeEl.innerHTML = '<i class="fas fa-' + (oldMode === 'postpaid' ? 'credit-card' : 'wallet') + ' me-1"></i>' +
            oldMode.charAt(0).toUpperCase() + oldMode.slice(1);
        
        newModeEl.className = 'pill-status ' + getBillingModePillClass(newMode);
        newModeEl.innerHTML = '<i class="fas fa-' + (newMode === 'postpaid' ? 'credit-card' : 'wallet') + ' me-1"></i>' +
            newMode.charAt(0).toUpperCase() + newMode.slice(1);
        
        var modal = new bootstrap.Modal(document.getElementById('billingModeConfirmModal'));
        modal.show();
    }
    
    function updateBillingModeUI(newMode) {
        var toggle = document.getElementById('billingTypeToggle');
        var toggleOptions = toggle.querySelectorAll('.toggle-option');
        
        toggleOptions.forEach(function(option) {
            if (option.dataset.value === newMode) {
                option.classList.add('active');
            } else {
                option.classList.remove('active');
            }
        });
        
        var billingModePill = document.getElementById('billingModePill');
        billingModePill.className = 'pill-status ' + getBillingModePillClass(newMode);
        billingModePill.innerHTML = '<i class="fas fa-' + (newMode === 'postpaid' ? 'credit-card' : 'wallet') + 
            ' me-1"></i>' + newMode.charAt(0).toUpperCase() + newMode.slice(1);
        
        document.getElementById('summaryBillingMode').innerHTML = 
            '<span class="pill-status ' + getBillingModePillClass(newMode) + '" style="font-size: 0.8rem;">' +
            '<i class="fas fa-' + (newMode === 'postpaid' ? 'credit-card' : 'wallet') + ' me-1"></i>' +
            newMode.charAt(0).toUpperCase() + newMode.slice(1) + '</span>';
        
        currentBillingMode = newMode;
    }
    
    function showBillingModeError(message) {
        var errorEl = document.getElementById('billingModeError');
        document.getElementById('billingModeErrorText').textContent = message;
        errorEl.classList.remove('hidden');
        
        setTimeout(function() {
            errorEl.classList.add('hidden');
        }, 8000);
    }
    
    function hideBillingModeError() {
        document.getElementById('billingModeError').classList.add('hidden');
    }
    
    var billingModeModal = document.getElementById('billingModeConfirmModal');
    billingModeModal.addEventListener('hidden.bs.modal', function() {
        pendingBillingMode = null;
    });
    
    document.getElementById('confirmBillingModeChange').addEventListener('click', function() {
        var confirmBtn = this;
        var oldMode = currentBillingMode;
        var newMode = pendingBillingMode;
        
        if (!canEditBillingMode) {
            showBillingModeError('You do not have permission to change billing mode.');
            var modal = bootstrap.Modal.getInstance(billingModeModal);
            modal.hide();
            return;
        }
        
        if (hasOutstandingInvoices && !canOverrideRisk) {
            showBillingModeError('Cannot change billing mode while account has outstanding invoices.');
            var modal = bootstrap.Modal.getInstance(billingModeModal);
            modal.hide();
            return;
        }
        
        if (!newMode || newMode === oldMode) {
            var modal = bootstrap.Modal.getInstance(billingModeModal);
            modal.hide();
            return;
        }
        
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Updating...';
        
        hideBillingModeError();
        
        Promise.all([
            HubSpotBillingService.updateBillingMode(accountId, newMode),
            InternalBillingConfigService.updateBillingMode(accountId, newMode)
        ])
        .then(function(results) {
            return AdminAccountBillingService.updateBillingMode(accountId, newMode);
        })
        .then(function(result) {
            updateBillingModeUI(newMode);
            
            // Admin audit logging
            AdminBillingAuditLogger.logBillingModeChanged(accountId, oldMode, newMode);
            
            var modal = bootstrap.Modal.getInstance(document.getElementById('billingModeConfirmModal'));
            modal.hide();
        })
        .catch(function(error) {
            console.error('Billing mode update failed:', error);
            AdminBillingAuditLogger.logBillingModeChangeFailed(accountId, newMode, error);
            showBillingModeError('Could not update HubSpot. No changes were saved.');
            
            var modal = bootstrap.Modal.getInstance(document.getElementById('billingModeConfirmModal'));
            modal.hide();
        })
        .finally(function() {
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = '<i class="fas fa-check me-1"></i>Confirm Change';
            pendingBillingMode = null;
        });
    });
    
    // Enhanced Invoices Table with Filters, Sorting, Pagination
    var canViewInvoices = AdminPermissionService.hasPermission('billing.view_invoices');
    var allInvoices = [];
    var filteredInvoices = [];
    var currentSort = { field: 'date', direction: 'desc' };
    var currentFilters = { number: '', status: '', year: '' };
    var currentPage = 1;
    var pageSize = 10;
    var isLoadingInvoices = false;
    
    function initInvoicesTable() {
        if (!canViewInvoices) {
            document.getElementById('invoicesContent').style.display = 'none';
            document.getElementById('invoicesHeaderActions').style.display = 'none';
            document.getElementById('permissionDeniedState').style.display = '';
            return;
        }
        
        loadInvoices();
        setupInvoiceTableEvents();
    }
    
    function loadInvoices() {
        if (isLoadingInvoices) return;
        isLoadingInvoices = true;
        
        var tbody = document.getElementById('invoicesTableBody');
        var errorState = document.getElementById('invoicesErrorState');
        var tableWrapper = document.getElementById('invoicesTableWrapper');
        var paginationFooter = document.getElementById('invoicesPagination');
        
        errorState.style.display = 'none';
        tableWrapper.style.display = '';
        paginationFooter.style.display = '';
        tbody.innerHTML = '<tr><td colspan="10" class="loading-state text-center py-4">' +
            '<i class="fas fa-spinner fa-spin me-2"></i>Loading invoices...</td></tr>';
        
        AdminAccountBillingService.getAccountInvoices(accountId)
            .then(function(invoices) {
                allInvoices = invoices;
                applyFiltersAndRender();
                isLoadingInvoices = false;
            })
            .catch(function(error) {
                console.error('Failed to load invoices:', error);
                tbody.innerHTML = '';
                tableWrapper.style.display = 'none';
                paginationFooter.style.display = 'none';
                errorState.style.display = '';
                isLoadingInvoices = false;
            });
    }
    
    function applyFiltersAndRender() {
        filteredInvoices = allInvoices.filter(function(inv) {
            if (currentFilters.number && inv.number.toLowerCase().indexOf(currentFilters.number.toLowerCase()) === -1) {
                return false;
            }
            if (currentFilters.status && inv.status !== currentFilters.status) {
                return false;
            }
            if (currentFilters.year && inv.date.indexOf(currentFilters.year) === -1) {
                return false;
            }
            return true;
        });
        
        sortInvoices();
        currentPage = 1;
        renderInvoicesTable();
        renderPagination();
        updateActiveFiltersDisplay();
    }
    
    function parsePeriodToDate(period) {
        var months = { 'Jan': 0, 'Feb': 1, 'Mar': 2, 'Apr': 3, 'May': 4, 'Jun': 5,
                       'Jul': 6, 'Aug': 7, 'Sep': 8, 'Oct': 9, 'Nov': 10, 'Dec': 11 };
        var parts = period.split(' ');
        if (parts.length === 2 && months[parts[0]] !== undefined) {
            return new Date(parseInt(parts[1]), months[parts[0]], 1).getTime();
        }
        return 0;
    }
    
    function sortInvoices() {
        filteredInvoices.sort(function(a, b) {
            var aVal, bVal;
            switch (currentSort.field) {
                case 'number': aVal = a.number; bVal = b.number; break;
                case 'period': 
                    aVal = parsePeriodToDate(a.period); 
                    bVal = parsePeriodToDate(b.period); 
                    break;
                case 'date': 
                    aVal = new Date(a.date).getTime(); 
                    bVal = new Date(b.date).getTime(); 
                    break;
                case 'dueDate': 
                    aVal = new Date(a.dueDate).getTime(); 
                    bVal = new Date(b.dueDate).getTime(); 
                    break;
                case 'amountExVat': aVal = a.amountExVat; bVal = b.amountExVat; break;
                case 'total': aVal = a.total; bVal = b.total; break;
                default: 
                    aVal = new Date(a.date).getTime(); 
                    bVal = new Date(b.date).getTime();
            }
            if (typeof aVal === 'string') {
                return currentSort.direction === 'asc' ? aVal.localeCompare(bVal) : bVal.localeCompare(aVal);
            }
            return currentSort.direction === 'asc' ? aVal - bVal : bVal - aVal;
        });
    }
    
    function renderInvoicesTable() {
        var tbody = document.getElementById('invoicesTableBody');
        var start = (currentPage - 1) * pageSize;
        var end = Math.min(start + pageSize, filteredInvoices.length);
        var pageInvoices = filteredInvoices.slice(start, end);
        
        if (filteredInvoices.length === 0) {
            tbody.innerHTML = '<tr><td colspan="10" class="empty-state text-center py-5">' +
                '<i class="fas fa-file-invoice fa-3x text-muted mb-3"></i>' +
                '<div class="text-muted">No invoices found</div></td></tr>';
            return;
        }
        
        var html = '';
        pageInvoices.forEach(function(inv) {
            var outstandingClass = inv.outstanding > 0 ? 'text-danger fw-bold' : 'text-muted';
            html += '<tr data-invoice="' + inv.number + '">' +
                '<td><a href="#" class="invoice-link">' + inv.number + '</a></td>' +
                '<td>' + inv.period + '</td>' +
                '<td>' + formatDate(inv.date) + '</td>' +
                '<td>' + formatDate(inv.dueDate) + '</td>' +
                '<td>' + getInvoiceStatusBadge(inv.status) + '</td>' +
                '<td class="text-end">' + formatCurrency(inv.amountExVat) + '</td>' +
                '<td class="text-end">' + formatCurrency(inv.vat) + '</td>' +
                '<td class="text-end fw-bold">' + formatCurrency(inv.total) + '</td>' +
                '<td class="text-end ' + outstandingClass + '">' + formatCurrency(inv.outstanding) + '</td>' +
                '<td class="text-center actions-cell">' +
                    '<button class="btn btn-sm btn-link text-admin-primary p-0" title="View Details"><i class="fas fa-eye"></i></button>' +
                '</td>' +
                '</tr>';
        });
        
        tbody.innerHTML = html;
        
        document.getElementById('showingStart').textContent = filteredInvoices.length > 0 ? start + 1 : 0;
        document.getElementById('showingEnd').textContent = end;
        document.getElementById('totalInvoiceCount').textContent = filteredInvoices.length;
    }
    
    function renderPagination() {
        var container = document.getElementById('paginationContainer');
        var totalPages = Math.ceil(filteredInvoices.length / pageSize);
        
        if (totalPages <= 1) {
            container.innerHTML = '';
            return;
        }
        
        var html = '<li class="page-item ' + (currentPage === 1 ? 'disabled' : '') + '">' +
            '<a class="page-link" href="#" data-page="' + (currentPage - 1) + '"><i class="fas fa-chevron-left"></i></a></li>';
        
        for (var i = 1; i <= totalPages; i++) {
            if (i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
                html += '<li class="page-item ' + (i === currentPage ? 'active' : '') + '">' +
                    '<a class="page-link" href="#" data-page="' + i + '">' + i + '</a></li>';
            } else if (i === currentPage - 2 || i === currentPage + 2) {
                html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
        }
        
        html += '<li class="page-item ' + (currentPage === totalPages ? 'disabled' : '') + '">' +
            '<a class="page-link" href="#" data-page="' + (currentPage + 1) + '"><i class="fas fa-chevron-right"></i></a></li>';
        
        container.innerHTML = html;
    }
    
    function updateActiveFiltersDisplay() {
        var container = document.getElementById('activeInvoiceFilters');
        var chipsContainer = document.getElementById('activeFilterChips');
        var hasFilters = currentFilters.number || currentFilters.status || currentFilters.year;
        
        if (!hasFilters) {
            container.style.display = 'none';
            return;
        }
        
        container.style.display = '';
        var chips = '';
        
        if (currentFilters.number) {
            chips += '<span class="filter-chip">Invoice: ' + currentFilters.number + 
                ' <i class="fas fa-times chip-remove" data-filter="number"></i></span>';
        }
        if (currentFilters.status) {
            chips += '<span class="filter-chip">Status: ' + currentFilters.status + 
                ' <i class="fas fa-times chip-remove" data-filter="status"></i></span>';
        }
        if (currentFilters.year) {
            chips += '<span class="filter-chip">Year: ' + currentFilters.year + 
                ' <i class="fas fa-times chip-remove" data-filter="year"></i></span>';
        }
        
        chipsContainer.innerHTML = chips;
    }
    
    function setupInvoiceTableEvents() {
        document.getElementById('applyInvoiceFilters').addEventListener('click', function() {
            currentFilters.number = document.getElementById('invoiceNumberFilter').value.trim();
            currentFilters.status = document.getElementById('statusFilter').value;
            currentFilters.year = document.getElementById('yearFilter').value;
            applyFiltersAndRender();
        });
        
        document.getElementById('resetInvoiceFilters').addEventListener('click', function() {
            document.getElementById('invoiceNumberFilter').value = '';
            document.getElementById('statusFilter').value = '';
            document.getElementById('yearFilter').value = '';
            currentFilters = { number: '', status: '', year: '' };
            applyFiltersAndRender();
        });
        
        document.getElementById('activeFilterChips').addEventListener('click', function(e) {
            if (e.target.classList.contains('chip-remove')) {
                var filter = e.target.dataset.filter;
                currentFilters[filter] = '';
                document.getElementById(filter === 'number' ? 'invoiceNumberFilter' : filter + 'Filter').value = '';
                applyFiltersAndRender();
            }
        });
        
        document.getElementById('paginationContainer').addEventListener('click', function(e) {
            e.preventDefault();
            var pageLink = e.target.closest('[data-page]');
            if (pageLink && !pageLink.parentElement.classList.contains('disabled')) {
                currentPage = parseInt(pageLink.dataset.page);
                renderInvoicesTable();
                renderPagination();
            }
        });
        
        document.querySelectorAll('#customerInvoicesTable thead th.sortable').forEach(function(th) {
            th.addEventListener('click', function() {
                var sortField = this.dataset.sort;
                if (currentSort.field === sortField) {
                    currentSort.direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
                } else {
                    currentSort.field = sortField;
                    currentSort.direction = 'desc';
                }
                
                document.querySelectorAll('#customerInvoicesTable thead th.sortable').forEach(function(header) {
                    header.classList.remove('sorted');
                    header.querySelector('.sort-icon').className = 'fas fa-sort sort-icon';
                });
                
                this.classList.add('sorted');
                this.querySelector('.sort-icon').className = 'fas fa-sort-' + 
                    (currentSort.direction === 'asc' ? 'up' : 'down') + ' sort-icon';
                
                sortInvoices();
                renderInvoicesTable();
            });
        });
        
        document.getElementById('retryLoadInvoices').addEventListener('click', function() {
            loadInvoices();
        });
        
        document.getElementById('invoicesTableBody').addEventListener('click', function(e) {
            var link = e.target.closest('.invoice-link');
            if (link) {
                e.preventDefault();
                var row = link.closest('tr');
                var invoiceNumber = row.dataset.invoice;
                alert('View invoice details: ' + invoiceNumber);
            }
        });
    }
    
    initInvoicesTable();
    
    document.getElementById('exportInvoicesBtn').addEventListener('click', function() {
        alert('Export functionality - would generate CSV of invoices for ' + accountId);
    });
    
    // Credit Limit Inline Edit
    var currentCreditLimit = 0;
    var canEditCreditLimit = AdminPermissionService.hasPermission('billing.edit_credit_limit');
    var currentBillingData = null;
    
    var creditLimitEditBtn = document.getElementById('creditLimitEditBtn');
    var creditLimitInputGroup = document.getElementById('creditLimitInputGroup');
    var creditLimitInput = document.getElementById('creditLimitInput');
    var creditLimitSaveBtn = document.getElementById('creditLimitSaveBtn');
    var creditLimitCancelBtn = document.getElementById('creditLimitCancelBtn');
    var creditLimitValueEl = document.getElementById('settingCreditLimit');
    var creditLimitError = document.getElementById('creditLimitError');
    
    function initCreditLimitEdit(billingData) {
        currentBillingData = billingData;
        currentCreditLimit = billingData.creditLimit;
        
        if (canEditCreditLimit) {
            creditLimitEditBtn.classList.remove('hidden');
        }
    }
    
    function showCreditLimitEditMode() {
        creditLimitValueEl.style.display = 'none';
        creditLimitEditBtn.style.display = 'none';
        creditLimitInputGroup.classList.add('active');
        creditLimitInput.value = currentCreditLimit.toFixed(2);
        creditLimitInput.focus();
        creditLimitInput.select();
        hideCreditLimitError();
    }
    
    function hideCreditLimitEditMode() {
        creditLimitValueEl.style.display = '';
        creditLimitEditBtn.style.display = '';
        creditLimitInputGroup.classList.remove('active');
        creditLimitInput.classList.remove('is-invalid');
    }
    
    function showCreditLimitError(message) {
        creditLimitError.textContent = message;
        creditLimitError.classList.add('show');
    }
    
    function hideCreditLimitError() {
        creditLimitError.classList.remove('show');
    }
    
    function validateCreditLimit(value) {
        var num = parseFloat(value);
        if (isNaN(num)) return { valid: false, error: 'Please enter a valid number' };
        if (num < 0) return { valid: false, error: 'Credit limit cannot be negative' };
        if (num > 1000000) return { valid: false, error: 'Credit limit cannot exceed £1,000,000' };
        return { valid: true, value: Math.round(num * 100) / 100 };
    }
    
    function updateCreditLimitUI(newLimit) {
        currentCreditLimit = newLimit;
        creditLimitValueEl.textContent = formatCurrency(newLimit);
        document.getElementById('summaryCreditLimit').textContent = formatCurrency(newLimit);
        
        if (currentBillingData) {
            currentBillingData.creditLimit = newLimit;
            var availableCredit = AdminAccountBillingService.calculateAvailableCredit(currentBillingData);
            var availableCreditEl = document.getElementById('summaryAvailableCredit');
            availableCreditEl.textContent = (availableCredit < 0 ? '-' : '') + formatCurrency(availableCredit);
            availableCreditEl.className = 'metric-value ' + (availableCredit <= 0 ? 'text-danger' : 'text-success');
        }
    }
    
    creditLimitEditBtn.addEventListener('click', function() {
        showCreditLimitEditMode();
    });
    
    creditLimitCancelBtn.addEventListener('click', function() {
        hideCreditLimitEditMode();
        creditLimitInput.value = currentCreditLimit.toFixed(2);
    });
    
    creditLimitInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            creditLimitSaveBtn.click();
        } else if (e.key === 'Escape') {
            creditLimitCancelBtn.click();
        }
    });
    
    creditLimitSaveBtn.addEventListener('click', function() {
        var validation = validateCreditLimit(creditLimitInput.value);
        
        if (!validation.valid) {
            creditLimitInput.classList.add('is-invalid');
            showCreditLimitError(validation.error);
            return;
        }
        
        var newLimit = validation.value;
        var oldLimit = currentCreditLimit;
        
        if (newLimit === oldLimit) {
            hideCreditLimitEditMode();
            return;
        }
        
        creditLimitInput.classList.remove('is-invalid');
        creditLimitSaveBtn.disabled = true;
        creditLimitSaveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        
        HubSpotBillingService.updateCreditLimit(accountId, newLimit)
            .then(function(result) {
                return AdminAccountBillingService.updateCreditLimit(accountId, newLimit);
            })
            .then(function(result) {
                updateCreditLimitUI(newLimit);
                hideCreditLimitEditMode();
                
                // Admin audit logging
                AdminBillingAuditLogger.logCreditLimitChanged(accountId, oldLimit, newLimit, 'GBP');
            })
            .catch(function(error) {
                console.error('Credit limit update failed:', error);
                AdminBillingAuditLogger.logCreditLimitChangeFailed(accountId, newLimit, error);
                creditLimitInput.value = currentCreditLimit.toFixed(2);
                showCreditLimitError('Could not update HubSpot. No changes were saved.');
            })
            .finally(function() {
                creditLimitSaveBtn.disabled = false;
                creditLimitSaveBtn.innerHTML = '<i class="fas fa-check"></i>';
            });
    });
    
    // Hook into billing data load to initialize credit limit edit
    AdminAccountBillingService.getAccountBilling(accountId).then(function(data) {
        initCreditLimitEdit(data);
    });
    
    // Create Invoice/Credit buttons visibility based on permissions
    (function() {
        var createInvoiceBtn = document.getElementById('createInvoiceBtn');
        var createCreditBtn = document.getElementById('createCreditBtn');
        
        if (AdminPermissionService.hasPermission('billing.create_invoice')) {
            createInvoiceBtn.style.display = '';
        }
        if (AdminPermissionService.hasPermission('billing.create_credit')) {
            createCreditBtn.style.display = '';
        }
    })();
    
    // Initialize modal with locked customer after billing data loads
    AdminAccountBillingService.getAccountBilling(accountId).then(function(data) {
        var customerData = {
            id: accountId,
            name: currentAccountName,
            status: data.status === 'live' ? 'Live' : (data.status === 'test' ? 'Test' : 'Suspended'),
            vatRegistered: data.vatRegistered !== undefined ? data.vatRegistered : true,
            vatRate: data.vatRate !== undefined ? data.vatRate : 20,
            reverseCharge: data.reverseCharge || false,
            vatCountry: data.vatCountry || 'GB'
        };
        
        InvoiceCreditModal.init({
            lockedCustomer: customerData,
            onSuccess: function(response, payload) {
                var toastMessage = payload.mode === 'invoice' 
                    ? 'Invoice created in Xero' + (response.emailSent ? ' and sent to customer.' : '.')
                    : 'Credit note created in Xero' + (response.emailSent ? ' and sent to customer.' : '.');
                showSuccessToast(toastMessage);
                
                refreshInvoicesTable(response.xeroDocumentNumber);
            }
        });
        
        document.getElementById('createInvoiceBtn').addEventListener('click', function() {
            InvoiceCreditModal.open('invoice');
        });
        
        document.getElementById('createCreditBtn').addEventListener('click', function() {
            InvoiceCreditModal.open('credit');
        });
    });
    
    function showSuccessToast(message) {
        var toastContainer = document.getElementById('toastContainer');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toastContainer';
            toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
            toastContainer.style.zIndex = '1080';
            document.body.appendChild(toastContainer);
        }
        
        var toastId = 'toast-' + Date.now();
        var toastHtml = '<div id="' + toastId + '" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">' +
            '<div class="d-flex">' +
                '<div class="toast-body">' +
                    '<i class="fas fa-check-circle me-2"></i>' + message +
                '</div>' +
                '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>' +
            '</div>' +
        '</div>';
        
        toastContainer.insertAdjacentHTML('beforeend', toastHtml);
        var toastEl = document.getElementById(toastId);
        var toast = new bootstrap.Toast(toastEl, { delay: 5000 });
        toast.show();
        
        toastEl.addEventListener('hidden.bs.toast', function() {
            toastEl.remove();
        });
    }
    
    function refreshInvoicesTable(newDocumentNumber) {
        var tbody = document.getElementById('invoicesTableBody');
        tbody.innerHTML = '<tr><td colspan="10" class="text-center py-4"><i class="fas fa-spinner fa-spin me-2"></i>Refreshing...</td></tr>';
        
        AdminAccountBillingService.getAccountInvoices(accountId).then(function(invoices) {
            allInvoices = invoices;
            
            if (newDocumentNumber) {
                document.getElementById('invoiceNumberFilter').value = '';
                document.getElementById('statusFilter').value = '';
                document.getElementById('yearFilter').value = '';
                currentFilters = { number: '', status: '', year: '' };
                currentSort = { field: 'date', direction: 'desc' };
            }
            
            applyFiltersAndRender();
            
            if (newDocumentNumber) {
                setTimeout(function() {
                    var row = document.querySelector('tr[data-invoice="' + newDocumentNumber + '"]');
                    if (row) {
                        row.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        row.style.transition = 'background-color 0.5s ease';
                        row.style.backgroundColor = 'rgba(30, 58, 95, 0.15)';
                        setTimeout(function() {
                            row.style.backgroundColor = '';
                        }, 3000);
                    }
                }, 100);
            }
        });
    }
});
</script>
@endpush
