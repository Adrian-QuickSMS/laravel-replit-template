@extends('layouts.quicksms')

@section('title', 'Purchase Messages')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/15.7.1/nouislider.min.css">
<style>
.purchase-messages-container {
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
.tier-card {
    border: none;
    border-radius: 0.75rem;
    transition: all 0.2s ease;
    height: 100%;
    overflow: hidden;
}
.tier-card:hover {
    box-shadow: 0 8px 24px rgba(111, 66, 193, 0.25);
    transform: translateY(-2px);
}
.tier-card .tier-header {
    position: relative;
    z-index: 1;
}
.tier-card .tier-header h4,
.tier-card .tier-header .tier-volume,
.tier-card .tier-header .tier-volume strong {
    color: #fff;
}
.tier-card .tier-badge {
    background: rgba(255, 255, 255, 0.25);
    color: #fff;
}
.tier-card .noUi-connect {
    background: #fff;
}
.tier-card .noUi-handle {
    border-color: var(--primary);
}
.tier-card .noUi-target {
    background: rgba(255, 255, 255, 0.3);
}
.tier-card .slider-label span,
.tier-card .slider-range-labels span {
    color: rgba(255, 255, 255, 0.8);
}
.tier-card .slider-value {
    color: #fff;
}
.tier-header {
    padding: 1.5rem;
    text-align: center;
}
.tier-header .tier-title {
    margin-bottom: 0.5rem;
    font-weight: 700;
    font-size: 1.75rem;
    color: #fff;
}
.tier-volume {
    font-size: 0.875rem;
    color: #6c757d;
}
.tier-volume strong {
    color: #2c2c2c;
}
.tier-slider-section {
    padding: 1.25rem 1.5rem;
    background: transparent;
    position: relative;
    z-index: 1;
}
.slider-label {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}
.slider-label span {
    font-size: 0.875rem;
    color: #6c757d;
}
.slider-value {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--primary);
}
.volume-slider {
    height: 8px;
}
.volume-slider .noUi-handle {
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
.volume-slider .noUi-handle:before,
.volume-slider .noUi-handle:after {
    display: none;
}
.volume-slider .noUi-connect {
    background: var(--primary);
}
.volume-slider .noUi-target {
    background: #dee2e6;
    border: none;
    border-radius: 4px;
}
.slider-range-labels {
    display: flex;
    justify-content: space-between;
    margin-top: 0.5rem;
    font-size: 0.75rem;
    color: #6c757d;
}
.numeric-inputs {
    display: flex;
    gap: 1rem;
    margin-top: 1.25rem;
    padding-top: 1rem;
    border-top: 1px solid #e9ecef;
}
.numeric-input-group {
    flex: 1;
}
.numeric-input-group label {
    display: block;
    font-size: 0.75rem;
    color: #6c757d;
    margin-bottom: 0.375rem;
    font-weight: 500;
}
.numeric-input-group .input-group {
    position: relative;
}
.numeric-input-group .input-group-text {
    background: #fff;
    border-color: #dee2e6;
    color: #6c757d;
    font-size: 0.875rem;
    padding: 0.5rem 0.75rem;
}
.numeric-input-group input {
    border-color: #dee2e6;
    font-size: 0.875rem;
    font-weight: 600;
    padding: 0.5rem 0.75rem;
    text-align: right;
}
.numeric-input-group input:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 0.2rem rgba(111, 66, 193, 0.15);
}
.tier-starter .numeric-input-group input:focus {
    border-color: #1cbb8c;
    box-shadow: 0 0 0 0.2rem rgba(28, 187, 140, 0.15);
}
.tier-enterprise .numeric-input-group input:focus {
    border-color: #6f42c1;
    box-shadow: 0 0 0 0.2rem rgba(111, 66, 193, 0.15);
}
.tier-bespoke .numeric-input-group input:focus {
    border-color: #D653C1;
    box-shadow: 0 0 0 0.2rem rgba(214, 83, 193, 0.15);
}
.pricing-badges {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.75rem;
    margin-top: 1rem;
}
.pricing-badge {
    background: #f8f9fa;
    border: none;
    border-radius: 0.75rem;
    padding: 0.875rem 0.5rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}
.pricing-badge .badge-price {
    font-size: 1rem;
    font-weight: 700;
    color: #2c2c2c;
    white-space: nowrap;
    margin-bottom: 0.25rem;
}
.pricing-badge .badge-label {
    font-size: 0.75rem;
    color: #6c757d;
    font-weight: 400;
    white-space: nowrap;
}
.pricing-badges-skeleton {
    display: flex;
    gap: 0.5rem;
    margin-top: 1rem;
}
.pricing-badges-skeleton .skeleton-badge {
    width: 70px;
    height: 44px;
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: shimmer 1.5s infinite;
    border-radius: 0.5rem;
}
.tier-body {
    padding: 1.5rem;
    background: #fff;
    position: relative;
    z-index: 2;
}
.tier-description {
    color: #6c757d;
    font-size: 0.875rem;
    margin-bottom: 1rem;
    min-height: 40px;
}
.price-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid #e9ecef;
}
.price-row:last-child {
    border-bottom: none;
}
.price-row .product-name {
    font-weight: 500;
    color: #2c2c2c;
}
.price-row .product-name i {
    width: 20px;
    color: #6c757d;
    margin-right: 0.5rem;
}
.price-row .product-price {
    font-weight: 600;
    color: var(--primary);
}
.price-row .product-unit {
    font-size: 0.75rem;
    color: #6c757d;
    font-weight: 400;
}
.tier-footer {
    padding: 1rem 1.5rem 1.5rem;
    background: #fff;
    border-bottom-left-radius: 0.75rem;
    border-bottom-right-radius: 0.75rem;
    position: relative;
    z-index: 3;
}
.tier-footer .btn-purchase {
    background: var(--primary);
    border-color: var(--primary);
    color: #fff;
    width: 100%;
    padding: 0.75rem 1.5rem;
    font-weight: 600;
    font-size: 1rem;
}
.tier-footer .btn-purchase:hover {
    background: var(--primary-hover);
    border-color: var(--primary-hover);
}
.order-summary-card {
    position: sticky;
    top: 100px;
}
.order-summary-card .card-header {
    background: linear-gradient(to right, #c165dd 0%, #5c27fe 100%);
    color: #fff;
}
.order-summary-card .card-header h5 {
    color: #fff;
}
#proceedBtn {
    background: var(--primary);
    border-color: var(--primary);
}
#proceedBtn:hover:not(:disabled) {
    background: var(--primary-hover);
    border-color: var(--primary-hover);
}
#proceedBtn:disabled {
    background: var(--primary-light);
    border-color: var(--primary-light);
}
.summary-row {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    border-bottom: 1px solid #e9ecef;
}
.summary-row:last-child {
    border-bottom: none;
}
.summary-row.total {
    font-weight: 700;
    font-size: 1.125rem;
    padding-top: 1rem;
    margin-top: 0.5rem;
    border-top: 2px solid #dee2e6;
    border-bottom: none;
}
.vat-info {
    background: rgba(255, 191, 0, 0.1);
    border: 1px solid rgba(255, 191, 0, 0.3);
    border-radius: 0.375rem;
    padding: 0.75rem;
    font-size: 0.875rem;
    color: #856404;
}
.no-vat-badge {
    background: rgba(28, 187, 140, 0.15);
    color: #1cbb8c;
    padding: 0.25rem 0.75rem;
    border-radius: 1rem;
    font-size: 0.75rem;
    font-weight: 600;
}
.loading-skeleton {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: shimmer 1.5s infinite;
    border-radius: 0.25rem;
}
@keyframes shimmer {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}
.skeleton-row {
    height: 24px;
    margin-bottom: 0.75rem;
}
.error-state {
    text-align: center;
    padding: 3rem;
    color: #dc3545;
}
.error-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}
.selected-tier {
    border-color: var(--primary) !important;
    box-shadow: 0 0 0 3px rgba(111, 66, 193, 0.2) !important;
}
</style>
@endpush

@section('content')
<div class="container-fluid purchase-messages-container">
    @php
        // TODO: Replace with actual user role from auth system
        // Allowed roles: 'admin' (full access), 'finance' (purchase & invoices), 'standard' (no access)
        $currentUserRole = 'admin';
        $vatApplicable = true;
        $accountCurrency = 'GBP';
        $bespokePricing = false;
    @endphp
    
    @if(!in_array($currentUserRole, ['admin', 'finance']))
        <div class="card access-denied-card">
            <div class="card-body py-5">
                <div class="icon-wrapper">
                    <i class="fas fa-lock"></i>
                </div>
                <h4 class="mb-3">Access Restricted</h4>
                <p class="text-muted mb-4">This page is only accessible to Admin and Finance users. Please contact your administrator if you need access to purchasing features.</p>
                <a href="{{ route('dashboard') }}" class="btn btn-primary">
                    <i class="fas fa-arrow-left me-2"></i>Return to Dashboard
                </a>
            </div>
        </div>
    @else
        <div class="purchase-header d-flex justify-content-between align-items-start">
            <div>
                <h2>Purchase Messages</h2>
                <p>Buy message credits and packages for your account</p>
            </div>
            <div id="currencyDisplay" class="badge bg-light text-dark fs-6 px-3 py-2">
                <i class="fas fa-globe me-1"></i>
                <span id="currentCurrency">{{ $accountCurrency }}</span>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-8">
                <div id="errorState" class="card d-none">
                    <div class="card-body error-state">
                        <i class="fas fa-exclamation-triangle"></i>
                        <h5>Unable to Load Pricing</h5>
                        <p class="text-muted mb-3" id="errorMessage">Failed to fetch pricing data. Please try again.</p>
                        <button class="btn btn-primary" onclick="loadPricing()">
                            <i class="fas fa-refresh me-2"></i>Retry
                        </button>
                    </div>
                </div>
                
                <div id="tiersContainer" class="row g-4">
                    @if($bespokePricing)
                        <div class="col-12">
                            <div class="card tier-card tier-bespoke tryal-gradient" data-tier="bespoke">
                                <div class="tier-header">
                                    <span class="tier-badge"><i class="fas fa-gem me-1"></i>Custom Plan</span>
                                    <h4>Bespoke</h4>
                                    <p class="tier-volume">Volume: <strong>50,000 – 5,000,000</strong> messages</p>
                                </div>
                                <div class="tier-slider-section">
                                    <div class="slider-label">
                                        <span>Select Volume</span>
                                        <span class="slider-value" id="bespokeSliderValue">50,000</span>
                                    </div>
                                    <div id="bespokeSlider" class="volume-slider"></div>
                                    <div class="slider-range-labels">
                                        <span>50K</span>
                                        <span>5M</span>
                                    </div>
                                    <div class="numeric-inputs">
                                        <div class="numeric-input-group">
                                            <label>Message Volume</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control volume-input" id="bespokeVolumeInput" data-tier="bespoke" value="50,000">
                                                <span class="input-group-text">SMS</span>
                                            </div>
                                        </div>
                                        <div class="numeric-input-group">
                                            <label>Total Cost</label>
                                            <div class="input-group">
                                                <span class="input-group-text currency-symbol">£</span>
                                                <input type="text" class="form-control cost-input" id="bespokeCostInput" data-tier="bespoke" value="0.00">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="tier-body">
                                    <p class="tier-description">Tailored pricing for high-volume enterprise customers with custom requirements and dedicated support.</p>
                                    <div id="bespokePricingBadges" class="pricing-badges-skeleton">
                                        <div class="skeleton-badge"></div>
                                        <div class="skeleton-badge"></div>
                                        <div class="skeleton-badge"></div>
                                        <div class="skeleton-badge"></div>
                                    </div>
                                </div>
                                <div class="tier-footer">
                                    <button class="btn btn-purchase" onclick="selectTier('bespoke')">
                                        Select
                                    </button>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="col-md-6">
                            <div class="card tier-card tier-starter tryal-gradient" data-tier="starter">
                                <div class="tier-header">
                                    <h3 class="tier-title">Starter</h3>
                                    <p class="tier-volume">Volume: <strong>0 – 50,000</strong> messages</p>
                                </div>
                                <div class="tier-slider-section">
                                    <div class="slider-label">
                                        <span>Select Volume</span>
                                        <span class="slider-value" id="starterSliderValue">10,000</span>
                                    </div>
                                    <div id="starterSlider" class="volume-slider"></div>
                                    <div class="slider-range-labels">
                                        <span>0</span>
                                        <span>50K</span>
                                    </div>
                                    <div class="numeric-inputs">
                                        <div class="numeric-input-group">
                                            <label>Message Volume</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control volume-input" id="starterVolumeInput" data-tier="starter" value="10,000">
                                                <span class="input-group-text">SMS</span>
                                            </div>
                                        </div>
                                        <div class="numeric-input-group">
                                            <label>Total Cost</label>
                                            <div class="input-group">
                                                <span class="input-group-text currency-symbol">£</span>
                                                <input type="text" class="form-control cost-input" id="starterCostInput" data-tier="starter" value="0.00">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="tier-body">
                                    <p class="tier-description">Perfect for small and medium businesses getting started with SMS and RCS messaging.</p>
                                    <div id="starterPricingBadges" class="pricing-badges-skeleton">
                                        <div class="skeleton-badge"></div>
                                        <div class="skeleton-badge"></div>
                                        <div class="skeleton-badge"></div>
                                        <div class="skeleton-badge"></div>
                                    </div>
                                </div>
                                <div class="tier-footer">
                                    <button class="btn btn-purchase" onclick="selectTier('starter')">
                                        Select
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card tier-card tier-enterprise tryal-gradient" data-tier="enterprise">
                                <div class="tier-header">
                                    <h3 class="tier-title">Enterprise</h3>
                                    <p class="tier-volume">Volume: <strong>50,000 – 1,000,000</strong> messages</p>
                                </div>
                                <div class="tier-slider-section">
                                    <div class="slider-label">
                                        <span>Select Volume</span>
                                        <span class="slider-value" id="enterpriseSliderValue">100,000</span>
                                    </div>
                                    <div id="enterpriseSlider" class="volume-slider"></div>
                                    <div class="slider-range-labels">
                                        <span>50K</span>
                                        <span>1M</span>
                                    </div>
                                    <div class="numeric-inputs">
                                        <div class="numeric-input-group">
                                            <label>Message Volume</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control volume-input" id="enterpriseVolumeInput" data-tier="enterprise" value="100,000">
                                                <span class="input-group-text">SMS</span>
                                            </div>
                                        </div>
                                        <div class="numeric-input-group">
                                            <label>Total Cost</label>
                                            <div class="input-group">
                                                <span class="input-group-text currency-symbol">£</span>
                                                <input type="text" class="form-control cost-input" id="enterpriseCostInput" data-tier="enterprise" value="0.00">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="tier-body">
                                    <p class="tier-description">Designed for larger organizations with higher messaging volumes and advanced needs.</p>
                                    <div id="enterprisePricingBadges" class="pricing-badges-skeleton">
                                        <div class="skeleton-badge"></div>
                                        <div class="skeleton-badge"></div>
                                        <div class="skeleton-badge"></div>
                                        <div class="skeleton-badge"></div>
                                    </div>
                                </div>
                                <div class="tier-footer">
                                    <button class="btn btn-purchase" onclick="selectTier('enterprise')">
                                        Select
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card order-summary-card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-shopping-cart me-2"></i>Order Summary</h5>
                    </div>
                    <div class="card-body">
                        <div id="orderItems">
                            <p class="text-muted text-center py-3">
                                <i class="fas fa-info-circle me-1"></i>
                                Select a pricing tier to continue
                            </p>
                        </div>
                        
                        <div id="orderSummary" class="d-none">
                            <div class="summary-row">
                                <span>Selected Tier</span>
                                <span id="selectedTierName">-</span>
                            </div>
                            <div class="summary-row">
                                <span>Quantity</span>
                                <span id="selectedQuantity">-</span>
                            </div>
                            <div class="summary-row">
                                <span>Net Total</span>
                                <span id="netTotal">-</span>
                            </div>
                            <div class="summary-row" id="vatRow">
                                <span>VAT (<span id="vatRateDisplay">20</span>%)</span>
                                <span id="vatAmount">-</span>
                            </div>
                            <div class="summary-row total">
                                <span>Total Payable</span>
                                <span id="totalPayable">-</span>
                            </div>
                        </div>
                        
                        <div id="vatInfo" class="vat-info mt-3 {{ $vatApplicable ? '' : 'd-none' }}">
                            <i class="fas fa-info-circle me-1"></i>
                            VAT at 20% will be applied at invoice level.
                        </div>
                        
                        <div id="noVatInfo" class="mt-3 {{ $vatApplicable ? 'd-none' : '' }}">
                            <span class="no-vat-badge">
                                <i class="fas fa-check me-1"></i>VAT Not Applicable
                            </span>
                        </div>
                        
                        <button id="proceedBtn" class="btn btn-primary w-100 mt-4" disabled onclick="processPurchase()">
                            <i class="fas fa-credit-card me-2"></i>Proceed to Payment
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="modal fade" id="invoiceModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body text-center py-5">
                        <div id="invoiceLoading">
                            <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <h5 class="mb-2">Creating invoice, please wait...</h5>
                            <p class="text-muted mb-0">Processing your order with HubSpot</p>
                        </div>
                        <div id="invoiceSuccess" class="d-none">
                            <div class="icon-wrapper mx-auto mb-3" style="width: 60px; height: 60px; border-radius: 50%; background: rgba(28, 187, 140, 0.15); display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-check text-success" style="font-size: 1.5rem;"></i>
                            </div>
                            <h5 class="mb-2">Invoice Created!</h5>
                            <p class="text-muted mb-3">Opening Stripe payment page...</p>
                            <button class="btn btn-primary" id="openStripeBtn">
                                <i class="fas fa-external-link-alt me-2"></i>Open Payment Page
                            </button>
                        </div>
                        <div id="invoiceError" class="d-none">
                            <div class="icon-wrapper mx-auto mb-3" style="width: 60px; height: 60px; border-radius: 50%; background: rgba(220, 53, 69, 0.15); display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-exclamation-triangle text-danger" style="font-size: 1.5rem;"></i>
                            </div>
                            <h5 class="mb-2">Unable to Create Invoice</h5>
                            <p class="text-muted mb-3" id="invoiceErrorMessage">We were unable to create your invoice. Please try again or contact support.</p>
                            <div class="d-flex gap-2 justify-content-center">
                                <button class="btn btn-primary" onclick="processPurchase()">
                                    <i class="fas fa-redo me-2"></i>Try Again
                                </button>
                                <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div id="paymentFailureBanner" class="alert alert-warning alert-dismissible fade d-none" role="alert" style="position: fixed; top: 80px; left: 50%; transform: translateX(-50%); z-index: 1050; max-width: 500px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
            <i class="fas fa-exclamation-circle me-2"></i>
            <strong>Payment was not completed.</strong> Your order has not been processed.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        
        <div class="modal fade" id="paymentSuccessModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body text-center py-5">
                        <div class="icon-wrapper mx-auto mb-3" style="width: 80px; height: 80px; border-radius: 50%; background: rgba(28, 187, 140, 0.15); display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-check-circle text-success" style="font-size: 2.5rem;"></i>
                        </div>
                        <h4 class="mb-2">Payment Successful!</h4>
                        <p class="text-muted mb-3">Your balance has been updated.</p>
                        <div class="bg-light rounded p-3 mb-4">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Amount Paid:</span>
                                <span class="fw-bold" id="paidAmount">£0.00</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">New Balance:</span>
                                <span class="fw-bold text-success" id="newBalance">£0.00</span>
                            </div>
                        </div>
                        <button class="btn btn-primary" data-bs-dismiss="modal">
                            <i class="fas fa-check me-2"></i>Continue
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/15.7.1/nouislider.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/wnumb/1.2.0/wNumb.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('[Purchase Messages] Page initialized');
    
    const state = {
        products: {},
        selectedTier: null,
        sliderValues: {
            starter: 10000,
            enterprise: 100000,
            bespoke: 50000
        },
        currency: '{{ $accountCurrency }}',
        vatApplicable: {{ $vatApplicable ? 'true' : 'false' }},
        vatRate: 20,
        bespokePricing: {{ $bespokePricing ? 'true' : 'false' }},
        isLoading: true,
        error: null,
        sliders: {}
    };

    const tierConfig = {
        starter: {
            name: 'Starter',
            volumeMin: 0,
            volumeMax: 50000,
            increment: 1000,
            description: 'SMB'
        },
        enterprise: {
            name: 'Enterprise',
            volumeMin: 50000,
            volumeMax: 1000000,
            increment: 50000,
            description: 'Business'
        },
        bespoke: {
            name: 'Bespoke',
            volumeMin: 50000,
            volumeMax: 5000000,
            increment: 50000,
            description: 'Custom'
        }
    };

    const productLabels = {
        'sms': { name: 'SMS Message', icon: 'fa-sms', unit: '/msg' },
        'rcs_basic': { name: 'RCS Basic', icon: 'fa-comment-dots', unit: '/msg' },
        'rcs_single': { name: 'RCS Single', icon: 'fa-comments', unit: '/msg' },
        'vmn': { name: 'Virtual Mobile Number', icon: 'fa-phone', unit: '/mo' },
        'shortcode_keyword': { name: 'Shortcode Keyword', icon: 'fa-hashtag', unit: '/mo' },
        'ai': { name: 'AI Credits', icon: 'fa-robot', unit: '/credit' }
    };

    function formatNumber(num) {
        return num.toLocaleString('en-GB');
    }

    function formatCurrency(amount) {
        const symbols = { 'GBP': '£', 'EUR': '€', 'USD': '$' };
        const symbol = symbols[state.currency] || state.currency + ' ';
        return symbol + amount.toFixed(4);
    }

    function formatCurrencyShort(amount) {
        const symbols = { 'GBP': '£', 'EUR': '€', 'USD': '$' };
        const symbol = symbols[state.currency] || state.currency + ' ';
        return symbol + amount.toFixed(2);
    }

    function getSmsUnitPrice(tierId = null) {
        const tier = tierId || state.selectedTier;
        const smsProduct = state.products.sms;
        if (!smsProduct) return 0;
        
        // Enterprise and Bespoke tiers get discounted pricing
        if ((tier === 'enterprise' || tier === 'bespoke') && smsProduct.price_enterprise) {
            return smsProduct.price_enterprise;
        }
        return smsProduct.price || 0;
    }

    function calculatePurchase(volume, tierId = null) {
        const tier = tierId || state.selectedTier;
        const smsUnitPrice = getSmsUnitPrice(tier);
        const netCost = volume * smsUnitPrice;
        const vatRate = state.vatApplicable ? 0.20 : 0;
        const vatAmount = netCost * vatRate;
        const totalPayable = netCost + vatAmount;
        
        return {
            volume: volume,
            smsUnitPrice: smsUnitPrice,
            netCost: netCost,
            vatApplicable: state.vatApplicable,
            vatRate: vatRate,
            vatAmount: vatAmount,
            totalPayable: totalPayable
        };
    }

    function initializeSliders() {
        const slidersToInit = state.bespokePricing ? ['bespoke'] : ['starter', 'enterprise'];
        
        slidersToInit.forEach(tierId => {
            const sliderEl = document.getElementById(tierId + 'Slider');
            if (!sliderEl) return;

            const config = tierConfig[tierId];
            const snapValues = [];
            
            for (let i = config.volumeMin; i <= config.volumeMax; i += config.increment) {
                snapValues.push(i);
            }
            if (snapValues[snapValues.length - 1] !== config.volumeMax) {
                snapValues.push(config.volumeMax);
            }

            state.sliders[tierId] = noUiSlider.create(sliderEl, {
                start: state.sliderValues[tierId],
                connect: [true, false],
                snap: true,
                range: snapValues.reduce((acc, val, idx) => {
                    if (idx === 0) {
                        acc['min'] = val;
                    } else if (idx === snapValues.length - 1) {
                        acc['max'] = val;
                    } else {
                        const percent = ((val - config.volumeMin) / (config.volumeMax - config.volumeMin)) * 100;
                        acc[percent.toFixed(2) + '%'] = val;
                    }
                    return acc;
                }, {}),
                format: wNumb({
                    decimals: 0,
                    thousand: ','
                })
            });

            state.sliders[tierId].on('update', function(values, handle, unencoded, tap, positions, noUiSlider) {
                const value = parseInt(values[0].replace(/,/g, ''));
                state.sliderValues[tierId] = value;
                document.getElementById(tierId + 'SliderValue').textContent = formatNumber(value);
                
                updateNumericInputs(tierId, value);
                
                if (state.selectedTier === tierId) {
                    updateOrderSummary();
                }
            });

            console.log(`[Slider] Initialized ${tierId} slider with ${snapValues.length} snap points`);
        });
    }

    function updateNumericInputs(tierId, volume) {
        const volumeInput = document.getElementById(tierId + 'VolumeInput');
        const costInput = document.getElementById(tierId + 'CostInput');
        
        if (volumeInput && !volumeInput.matches(':focus')) {
            volumeInput.value = formatNumber(volume);
        }
        
        if (costInput && !costInput.matches(':focus')) {
            const calc = calculatePurchase(volume, tierId);
            costInput.value = calc.netCost.toFixed(2);
        }
    }

    function updateAllCostInputs() {
        const tiers = state.bespokePricing ? ['bespoke'] : ['starter', 'enterprise'];
        tiers.forEach(tierId => {
            updateNumericInputs(tierId, state.sliderValues[tierId]);
        });
    }

    function clampToTierRange(tierId, value) {
        const config = tierConfig[tierId];
        return Math.max(config.volumeMin, Math.min(config.volumeMax, value));
    }

    function snapToIncrement(tierId, value) {
        const config = tierConfig[tierId];
        const clamped = clampToTierRange(tierId, value);
        return Math.round(clamped / config.increment) * config.increment;
    }

    function initializeNumericInputs() {
        document.querySelectorAll('.volume-input').forEach(input => {
            input.addEventListener('input', function(e) {
                const tierId = this.dataset.tier;
                let rawValue = this.value.replace(/[^0-9]/g, '');
                if (!rawValue) return;
                
                let volume = parseInt(rawValue);
                volume = clampToTierRange(tierId, volume);
                
                if (state.sliders[tierId]) {
                    state.sliders[tierId].set(volume);
                }
            });
            
            input.addEventListener('blur', function(e) {
                const tierId = this.dataset.tier;
                let rawValue = this.value.replace(/[^0-9]/g, '');
                if (!rawValue) {
                    this.value = formatNumber(state.sliderValues[tierId]);
                    return;
                }
                
                let volume = parseInt(rawValue);
                volume = snapToIncrement(tierId, volume);
                
                if (state.sliders[tierId]) {
                    state.sliders[tierId].set(volume);
                }
                this.value = formatNumber(volume);
            });
        });

        document.querySelectorAll('.cost-input').forEach(input => {
            input.addEventListener('input', function(e) {
                const tierId = this.dataset.tier;
                let rawValue = this.value.replace(/[^0-9.]/g, '');
                if (!rawValue) return;
                
                const netCost = parseFloat(rawValue);
                const smsUnitPrice = getSmsUnitPrice(tierId);
                
                if (smsUnitPrice <= 0) return;
                
                let volume = Math.round(netCost / smsUnitPrice);
                volume = clampToTierRange(tierId, volume);
                
                if (state.sliders[tierId]) {
                    state.sliders[tierId].set(volume);
                }
            });
            
            input.addEventListener('blur', function(e) {
                const tierId = this.dataset.tier;
                let rawValue = this.value.replace(/[^0-9.]/g, '');
                
                const smsUnitPrice = getSmsUnitPrice(tierId);
                
                if (!rawValue || smsUnitPrice <= 0) {
                    const calc = calculatePurchase(state.sliderValues[tierId], tierId);
                    this.value = calc.netCost.toFixed(2);
                    return;
                }
                
                const netCost = parseFloat(rawValue);
                let volume = Math.round(netCost / smsUnitPrice);
                volume = snapToIncrement(tierId, volume);
                
                if (state.sliders[tierId]) {
                    state.sliders[tierId].set(volume);
                }
                
                const calc = calculatePurchase(volume, tierId);
                this.value = calc.netCost.toFixed(2);
            });
        });
    }

    function renderPricingBadges(tierId) {
        const container = document.getElementById(tierId + 'PricingBadges');
        if (!container) return;

        container.className = 'pricing-badges';

        if (state.error) {
            container.innerHTML = `<p class="text-danger small mb-0"><i class="fas fa-exclamation-circle me-1"></i>Failed to load prices</p>`;
            return;
        }

        if (Object.keys(state.products).length === 0) {
            container.innerHTML = `<p class="text-muted small mb-0">No products available</p>`;
            return;
        }

        const badgeOrder = ['sms', 'rcs_basic', 'rcs_single', 'vmn', 'shortcode_keyword', 'ai'];
        const badgeLabels = {
            'sms': 'SMS',
            'rcs_basic': 'RCS Basic',
            'rcs_single': 'RCS Single',
            'vmn': 'VMN',
            'shortcode_keyword': 'Shortcode',
            'ai': 'AI'
        };

        let html = '';
        for (const key of badgeOrder) {
            const product = state.products[key];
            if (!product) continue;
            
            // Use tier-specific pricing: Enterprise gets lower prices
            const price = (tierId === 'enterprise' || tierId === 'bespoke') && product.price_enterprise 
                ? product.price_enterprise 
                : product.price;
            
            const label = badgeLabels[key] || key.toUpperCase();
            html += `
                <div class="pricing-badge">
                    <span class="badge-price">${formatCurrencyBadge(price, key)}</span>
                    <span class="badge-label">${label}</span>
                </div>
            `;
        }
        
        container.innerHTML = html;
    }

    function formatCurrencyBadge(amount, productKey) {
        const symbols = { 'GBP': '£', 'EUR': '€', 'USD': '$' };
        const symbol = symbols[state.currency] || state.currency + ' ';
        
        if (productKey === 'ai') {
            return symbol + amount.toFixed(2);
        }
        
        if (productKey === 'vmn' || productKey === 'shortcode_keyword') {
            if (amount % 1 === 0) {
                return symbol + amount.toFixed(0);
            }
            return symbol + amount.toFixed(2);
        }
        
        let formatted = amount.toFixed(4);
        formatted = formatted.replace(/0+$/, '');
        if (formatted.endsWith('.')) {
            formatted = formatted.slice(0, -1);
        }
        return symbol + formatted;
    }

    window.selectTier = function(tierId) {
        document.querySelectorAll('.tier-card').forEach(card => {
            card.classList.remove('selected-tier');
        });

        const selectedCard = document.querySelector(`[data-tier="${tierId}"]`);
        if (selectedCard) {
            selectedCard.classList.add('selected-tier');
        }

        state.selectedTier = tierId;
        updateOrderSummary();
    };

    function updateOrderSummary() {
        const orderItems = document.getElementById('orderItems');
        const orderSummary = document.getElementById('orderSummary');
        const proceedBtn = document.getElementById('proceedBtn');
        
        if (!state.selectedTier) {
            orderItems.innerHTML = `
                <p class="text-muted text-center py-3">
                    <i class="fas fa-info-circle me-1"></i>
                    Select a pricing tier to continue
                </p>
            `;
            orderSummary.classList.add('d-none');
            proceedBtn.disabled = true;
            return;
        }

        const tier = tierConfig[state.selectedTier];
        const quantity = state.sliderValues[state.selectedTier];
        const calc = calculatePurchase(quantity, state.selectedTier);

        orderItems.innerHTML = '';
        orderSummary.classList.remove('d-none');

        document.getElementById('selectedTierName').textContent = tier.name;
        document.getElementById('selectedQuantity').textContent = formatNumber(calc.volume) + ' messages';
        document.getElementById('netTotal').textContent = formatCurrencyShort(calc.netCost);
        document.getElementById('vatAmount').textContent = formatCurrencyShort(calc.vatAmount);
        document.getElementById('vatRateDisplay').textContent = Math.round(calc.vatRate * 100);
        document.getElementById('totalPayable').textContent = formatCurrencyShort(calc.totalPayable);

        const vatRow = document.getElementById('vatRow');
        if (calc.vatApplicable) {
            vatRow.classList.remove('d-none');
        } else {
            vatRow.classList.add('d-none');
        }

        proceedBtn.disabled = false;
    }

    window.loadPricing = async function() {
        state.isLoading = true;
        state.error = null;
        
        document.getElementById('errorState').classList.add('d-none');
        document.getElementById('tiersContainer').classList.remove('d-none');

        try {
            const response = await fetch(`/api/purchase/products?currency=${state.currency}`);
            const data = await response.json();

            if (!data.success) {
                throw new Error(data.error || 'Failed to fetch pricing');
            }

            state.products = data.products;
            state.vatApplicable = data.vat_applicable;
            state.vatRate = data.vat_rate;
            state.isLoading = false;

            document.getElementById('vatInfo').classList.toggle('d-none', !state.vatApplicable);
            document.getElementById('noVatInfo').classList.toggle('d-none', state.vatApplicable);

            if (state.bespokePricing) {
                renderPricingBadges('bespoke');
            } else {
                renderPricingBadges('starter');
                renderPricingBadges('enterprise');
            }

            console.log('[Purchase] Loaded', Object.keys(state.products).length, 'products from HubSpot');
            
            updateAllCostInputs();

        } catch (error) {
            console.error('[Purchase] Error loading pricing:', error);
            state.error = error.message;
            state.isLoading = false;
            
            if (state.bespokePricing) {
                renderPricingBadges('bespoke');
            } else {
                renderPricingBadges('starter');
                renderPricingBadges('enterprise');
            }
        }
    };

    window.processPurchase = async function() {
        if (!state.selectedTier) {
            alert('Please select a pricing tier first.');
            return;
        }

        const calc = calculatePurchase(state.sliderValues[state.selectedTier], state.selectedTier);
        
        if (calc.smsUnitPrice <= 0) {
            alert('Unable to process purchase. Pricing data not available.');
            return;
        }

        const invoiceModal = new bootstrap.Modal(document.getElementById('invoiceModal'));
        
        document.getElementById('invoiceLoading').classList.remove('d-none');
        document.getElementById('invoiceSuccess').classList.add('d-none');
        document.getElementById('invoiceError').classList.add('d-none');
        
        invoiceModal.show();

        const payload = {
            account_id: 'ACC-001',
            tier: state.selectedTier,
            volume: calc.volume,
            sms_unit_price: calc.smsUnitPrice,
            net_cost: calc.netCost,
            vat_applicable: calc.vatApplicable,
            currency: state.currency
        };

        console.log('[Purchase] Creating invoice with payload:', payload);

        try {
            const response = await fetch('/api/purchase/create-invoice', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify(payload)
            });

            const data = await response.json();

            if (!data.success) {
                throw new Error(data.error || 'Failed to create invoice');
            }

            document.getElementById('invoiceLoading').classList.add('d-none');
            document.getElementById('invoiceSuccess').classList.remove('d-none');

            const stripeUrl = data.payment_url;
            
            document.getElementById('openStripeBtn').onclick = function() {
                window.open(stripeUrl, '_blank');
                invoiceModal.hide();
            };

            setTimeout(() => {
                window.open(stripeUrl, '_blank');
                startPaymentPolling();
            }, 1500);

            console.log('[Purchase] Invoice created:', data.invoice_id);

        } catch (error) {
            console.error('[Purchase] Invoice creation error:', error);
            
            logErrorForAudit({
                type: 'invoice_creation_failed',
                tier: selectedTier,
                volume: selectedVolume,
                error: error.message,
                timestamp: new Date().toISOString()
            });
            
            document.getElementById('invoiceLoading').classList.add('d-none');
            document.getElementById('invoiceError').classList.remove('d-none');
            document.getElementById('invoiceErrorMessage').textContent = 'We were unable to create your invoice. Please try again or contact support.';
        }
    };

    function logErrorForAudit(errorData) {
        fetch('/api/audit/log', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
            },
            body: JSON.stringify(errorData)
        }).catch(e => console.warn('[Audit] Failed to log error:', e));
        
        console.log('[Audit] Error logged:', errorData);
    }

    function checkPaymentStatus() {
        fetch('/api/account/payment-status?account_id=ACC-001')
            .then(response => response.json())
            .then(data => {
                if (data.payment_completed) {
                    showPaymentSuccess(data.amount, data.new_balance);
                    refreshBalanceWidgets();
                }
            })
            .catch(error => console.error('[Payment] Status check error:', error));
    }

    function showPaymentSuccess(amount, newBalance) {
        document.getElementById('paidAmount').textContent = formatCurrencyShort(amount);
        document.getElementById('newBalance').textContent = formatCurrencyShort(newBalance);
        
        const successModal = new bootstrap.Modal(document.getElementById('paymentSuccessModal'));
        successModal.show();
        
        console.log('[Payment] Success modal shown', { amount, newBalance });
    }

    function showPaymentFailure() {
        const banner = document.getElementById('paymentFailureBanner');
        banner.classList.remove('d-none');
        banner.classList.add('show');
        
        setTimeout(() => {
            banner.classList.remove('show');
            setTimeout(() => banner.classList.add('d-none'), 150);
        }, 8000);
        
        console.log('[Payment] Failure banner shown');
    }

    function refreshBalanceWidgets() {
        fetch('/api/account/balance?account_id=ACC-001')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.querySelectorAll('.account-balance-widget').forEach(widget => {
                        widget.textContent = formatCurrencyShort(data.balance);
                    });
                    console.log('[Balance] Widgets refreshed:', data.balance);
                }
            })
            .catch(error => console.error('[Balance] Refresh error:', error));
    }

    function checkUrlParameters() {
        const urlParams = new URLSearchParams(window.location.search);
        
        if (urlParams.get('payment') === 'cancelled' || urlParams.get('payment') === 'failed') {
            showPaymentFailure();
            window.history.replaceState({}, document.title, window.location.pathname);
        }
        
        if (urlParams.get('payment') === 'success') {
            checkPaymentStatus();
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    }

    let paymentPollInterval = null;

    window.startPaymentPolling = function() {
        if (paymentPollInterval) clearInterval(paymentPollInterval);
        
        paymentPollInterval = setInterval(() => {
            checkPaymentStatus();
        }, 5000);

        setTimeout(() => {
            if (paymentPollInterval) {
                clearInterval(paymentPollInterval);
                paymentPollInterval = null;
                console.log('[Payment] Polling stopped after timeout');
            }
        }, 300000);
    };

    window.stopPaymentPolling = function() {
        if (paymentPollInterval) {
            clearInterval(paymentPollInterval);
            paymentPollInterval = null;
        }
    };

    initializeSliders();
    initializeNumericInputs();
    loadPricing();
    checkUrlParameters();
    
    window.addEventListener('focus', checkPaymentStatus);
});
</script>
@endpush
