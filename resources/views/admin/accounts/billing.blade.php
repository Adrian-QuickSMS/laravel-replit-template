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
.admin-breadcrumb { margin-bottom: 1rem; }
.admin-breadcrumb a { color: #6c757d; text-decoration: none; }
.admin-breadcrumb a:hover { color: var(--admin-primary); }
.admin-breadcrumb .separator { margin: 0 0.5rem; color: #adb5bd; }

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
<div class="admin-page">
    <div class="admin-breadcrumb">
        <a href="{{ route('admin.dashboard') }}">Admin</a>
        <span class="separator">/</span>
        <a href="{{ route('admin.accounts.overview') }}">Accounts</a>
        <span class="separator">/</span>
        <a href="{{ route('admin.accounts.details', ['accountId' => $account_id]) }}">{{ $account_id }}</a>
        <span class="separator">/</span>
        <span>Billing</span>
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
                    
                    <div class="billing-setting-row">
                        <span class="billing-setting-label">Credit Limit</span>
                        <span class="billing-setting-value" id="settingCreditLimit">&pound;0.00</span>
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
            <div class="billing-card">
                <div class="billing-card-header">
                    <h6><i class="fas fa-file-invoice me-2"></i>Customer Invoices</h6>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-admin-outline" id="exportInvoicesBtn">
                            <i class="fas fa-download me-1"></i>Export
                        </button>
                    </div>
                </div>
                <div class="billing-card-body p-0">
                    <div class="table-responsive">
                        <table class="invoices-table" id="customerInvoicesTable">
                            <thead>
                                <tr>
                                    <th>Invoice #</th>
                                    <th>Period</th>
                                    <th>Date</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                    <th class="text-end">Amount (ex VAT)</th>
                                    <th class="text-end">VAT</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody id="invoicesTableBody">
                                <tr>
                                    <td colspan="8" class="loading-state">
                                        <i class="fas fa-spinner fa-spin me-2"></i>Loading invoices...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
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
@endsection

@push('scripts')
<script src="{{ asset('js/admin-control-plane.js') }}"></script>
<script>
var AdminAccountBillingService = (function() {
    var mockAccounts = {
        'ACC-1234': {
            name: 'Acme Corporation',
            status: 'live',
            hubspotId: 'HS-12345',
            billingMode: 'prepaid',
            currentBalance: 2450.00,
            creditLimit: 0,
            paymentTerms: 'Immediate',
            currency: 'GBP',
            vatRegistered: true,
            lastUpdated: '2026-01-23T10:30:00Z'
        },
        'ACC-5678': {
            name: 'Finance Ltd',
            status: 'live',
            hubspotId: 'HS-67890',
            billingMode: 'postpaid',
            currentBalance: -1250.00,
            creditLimit: 5000.00,
            paymentTerms: 'Net 30',
            currency: 'GBP',
            vatRegistered: true,
            lastUpdated: '2026-01-22T14:15:00Z'
        },
        'ACC-7890': {
            name: 'NewClient Inc',
            status: 'test',
            hubspotId: 'HS-78901',
            billingMode: 'prepaid',
            currentBalance: 100.00,
            creditLimit: 0,
            paymentTerms: 'Immediate',
            currency: 'GBP',
            vatRegistered: false,
            lastUpdated: '2026-01-20T09:00:00Z'
        },
        'ACC-4567': {
            name: 'TestCo Ltd',
            status: 'suspended',
            hubspotId: 'HS-45678',
            billingMode: 'postpaid',
            currentBalance: -3500.00,
            creditLimit: 2000.00,
            paymentTerms: 'Net 14',
            currency: 'GBP',
            vatRegistered: true,
            lastUpdated: '2026-01-15T16:45:00Z'
        }
    };
    
    var mockInvoices = {
        'ACC-1234': [
            { number: 'INV-2024-0074', period: 'Dec 2024', date: '2024-12-25', dueDate: '2025-01-08', status: 'paid', amountExVat: 1815.00, vat: 363.00, total: 2178.00 },
            { number: 'INV-2024-0065', period: 'Nov 2024', date: '2024-11-25', dueDate: '2024-12-09', status: 'paid', amountExVat: 1420.00, vat: 284.00, total: 1704.00 },
            { number: 'INV-2024-0052', period: 'Oct 2024', date: '2024-10-25', dueDate: '2024-11-08', status: 'paid', amountExVat: 1680.00, vat: 336.00, total: 2016.00 }
        ],
        'ACC-5678': [
            { number: 'INV-2024-0026', period: 'Dec 2024', date: '2024-12-10', dueDate: '2025-01-09', status: 'issued', amountExVat: 2509.00, vat: 501.80, total: 3010.80 },
            { number: 'INV-2024-0018', period: 'Nov 2024', date: '2024-11-10', dueDate: '2024-12-10', status: 'paid', amountExVat: 2150.00, vat: 430.00, total: 2580.00 }
        ],
        'ACC-7890': [],
        'ACC-4567': [
            { number: 'INV-2024-0045', period: 'Nov 2024', date: '2024-11-15', dueDate: '2024-11-29', status: 'overdue', amountExVat: 3500.00, vat: 700.00, total: 4200.00 }
        ]
    };
    
    return {
        getAccountBilling: function(accountId) {
            return new Promise(function(resolve, reject) {
                setTimeout(function() {
                    var data = mockAccounts[accountId];
                    if (data) {
                        resolve(Object.assign({}, data, { accountId: accountId }));
                    } else {
                        resolve({
                            accountId: accountId,
                            name: 'Unknown Account',
                            status: 'test',
                            hubspotId: null,
                            billingMode: 'prepaid',
                            currentBalance: 0,
                            creditLimit: 0,
                            paymentTerms: 'Immediate',
                            currency: 'GBP',
                            vatRegistered: false,
                            lastUpdated: new Date().toISOString()
                        });
                    }
                }, 300);
            });
        },
        
        getAccountInvoices: function(accountId) {
            return new Promise(function(resolve, reject) {
                setTimeout(function() {
                    var invoices = mockInvoices[accountId] || [];
                    resolve(invoices);
                }, 400);
            });
        },
        
        calculateAvailableCredit: function(billingData) {
            if (billingData.billingMode === 'prepaid') {
                return Math.max(0, billingData.currentBalance);
            } else {
                return billingData.currentBalance + billingData.creditLimit;
            }
        },
        
        updateBillingMode: function(accountId, newMode) {
            var self = this;
            return new Promise(function(resolve, reject) {
                setTimeout(function() {
                    if (mockAccounts[accountId]) {
                        mockAccounts[accountId].billingMode = newMode;
                        mockAccounts[accountId].lastUpdated = new Date().toISOString();
                        resolve({ success: true, accountId: accountId, billingMode: newMode });
                    } else {
                        reject(new Error('Account not found'));
                    }
                }, 200);
            });
        }
    };
})();

var HubSpotBillingService = (function() {
    var simulateFailure = false;
    
    return {
        updateBillingMode: function(accountId, mode) {
            return new Promise(function(resolve, reject) {
                setTimeout(function() {
                    if (simulateFailure) {
                        reject(new Error('HubSpot API error: Unable to update billing mode'));
                    } else {
                        console.log('[HubSpotBillingService] Updated billing mode for ' + accountId + ' to ' + mode);
                        resolve({
                            success: true,
                            hubspotRecordId: 'HS-' + Math.random().toString(36).substr(2, 9),
                            updatedAt: new Date().toISOString()
                        });
                    }
                }, 500);
            });
        },
        
        setSimulateFailure: function(value) {
            simulateFailure = value;
        }
    };
})();

var InternalBillingConfigService = (function() {
    return {
        updateBillingMode: function(accountId, mode) {
            return new Promise(function(resolve, reject) {
                setTimeout(function() {
                    console.log('[InternalBillingConfigService] Updated billing mode for ' + accountId + ' to ' + mode);
                    resolve({
                        success: true,
                        accountId: accountId,
                        billingMode: mode,
                        effectiveDate: new Date().toISOString()
                    });
                }, 300);
            });
        }
    };
})();

var BillingRiskService = (function() {
    var mockRiskData = {
        'ACC-1234': { hasOutstandingInvoices: false, overdueAmount: 0, overdueCount: 0 },
        'ACC-5678': { hasOutstandingInvoices: true, overdueAmount: 0, overdueCount: 0 },
        'ACC-7890': { hasOutstandingInvoices: false, overdueAmount: 0, overdueCount: 0 },
        'ACC-4567': { hasOutstandingInvoices: true, overdueAmount: 4200.00, overdueCount: 1 }
    };
    
    return {
        checkBillingRisk: function(accountId) {
            return new Promise(function(resolve) {
                setTimeout(function() {
                    var risk = mockRiskData[accountId] || { hasOutstandingInvoices: false, overdueAmount: 0, overdueCount: 0 };
                    resolve(risk);
                }, 200);
            });
        }
    };
})();

var AdminPermissionService = (function() {
    var mockPermissions = {
        'billing.edit_mode': true,
        'billing.override_risk': false
    };
    
    return {
        hasPermission: function(permission) {
            return mockPermissions[permission] === true;
        },
        
        setPermission: function(permission, value) {
            mockPermissions[permission] = value;
        }
    };
})();

document.addEventListener('DOMContentLoaded', function() {
    var accountId = '{{ $account_id }}';
    
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
    
    document.getElementById('confirmBillingModeChange').addEventListener('click', function() {
        var confirmBtn = this;
        var oldMode = currentBillingMode;
        var newMode = pendingBillingMode;
        
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
            
            if (typeof AdminControlPlane !== 'undefined') {
                AdminControlPlane.logAdminAction('BILLING_MODE_CHANGED', accountId, {
                    accountName: currentAccountName,
                    oldValue: oldMode,
                    newValue: newMode,
                    sourceScreen: 'Admin > Accounts > Billing'
                });
            }
            
            var modal = bootstrap.Modal.getInstance(document.getElementById('billingModeConfirmModal'));
            modal.hide();
        })
        .catch(function(error) {
            console.error('Billing mode update failed:', error);
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
    
    AdminAccountBillingService.getAccountInvoices(accountId).then(function(invoices) {
        var tbody = document.getElementById('invoicesTableBody');
        
        if (invoices.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" class="empty-state">' +
                '<i class="fas fa-file-invoice"></i>' +
                '<div>No invoices found for this customer</div></td></tr>';
            return;
        }
        
        var html = '';
        invoices.forEach(function(inv) {
            html += '<tr>' +
                '<td><a href="#" class="invoice-link">' + inv.number + '</a></td>' +
                '<td>' + inv.period + '</td>' +
                '<td>' + formatDate(inv.date) + '</td>' +
                '<td>' + formatDate(inv.dueDate) + '</td>' +
                '<td>' + getInvoiceStatusBadge(inv.status) + '</td>' +
                '<td class="text-end">' + formatCurrency(inv.amountExVat) + '</td>' +
                '<td class="text-end">' + formatCurrency(inv.vat) + '</td>' +
                '<td class="text-end fw-bold">' + formatCurrency(inv.total) + '</td>' +
                '</tr>';
        });
        
        tbody.innerHTML = html;
    });
    
    document.getElementById('exportInvoicesBtn').addEventListener('click', function() {
        alert('Export functionality - would generate CSV of invoices for ' + accountId);
    });
});
</script>
@endpush
