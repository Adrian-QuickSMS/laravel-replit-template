@extends('layouts.quicksms')

@section('title', 'Purchase Messages')

@push('styles')
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
.pricing-card {
    border: 1px solid #e9ecef;
    border-radius: 0.5rem;
    transition: all 0.2s ease;
    height: 100%;
}
.pricing-card:hover {
    border-color: var(--primary);
    box-shadow: 0 4px 12px rgba(111, 66, 193, 0.15);
}
.pricing-card .card-header {
    background: linear-gradient(135deg, rgba(111, 66, 193, 0.1) 0%, rgba(111, 66, 193, 0.05) 100%);
    border-bottom: 1px solid #e9ecef;
    padding: 1rem 1.25rem;
}
.pricing-card .card-header h5 {
    margin-bottom: 0;
    font-weight: 600;
    color: #2c2c2c;
}
.pricing-card .card-header .sku {
    font-size: 0.75rem;
    color: #6c757d;
    margin-top: 0.25rem;
}
.pricing-card .card-body {
    padding: 1.25rem;
}
.price-display {
    font-size: 2rem;
    font-weight: 700;
    color: var(--primary);
    margin-bottom: 0.5rem;
}
.price-display .currency {
    font-size: 1rem;
    vertical-align: super;
    margin-right: 0.25rem;
}
.price-display .unit {
    font-size: 0.875rem;
    font-weight: 400;
    color: #6c757d;
}
.product-description {
    color: #6c757d;
    font-size: 0.875rem;
    min-height: 40px;
}
.quantity-input {
    max-width: 120px;
}
.order-summary-card {
    position: sticky;
    top: 100px;
}
.order-summary-card .card-header {
    background: var(--primary);
    color: #fff;
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
.skeleton-price {
    height: 48px;
    width: 120px;
    margin-bottom: 0.5rem;
}
.skeleton-text {
    height: 16px;
    width: 80%;
    margin-bottom: 0.5rem;
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
.no-vat-badge {
    background: rgba(28, 187, 140, 0.15);
    color: #1cbb8c;
    padding: 0.25rem 0.75rem;
    border-radius: 1rem;
    font-size: 0.75rem;
    font-weight: 600;
}
</style>
@endpush

@section('content')
<div class="container-fluid purchase-messages-container">
    @php
        $currentUserRole = 'admin';
        $vatApplicable = true;
        $accountCurrency = 'GBP';
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
                <div id="pricingContainer">
                    <div class="row g-4" id="pricingGrid">
                        @for($i = 0; $i < 6; $i++)
                        <div class="col-md-6 col-xl-4">
                            <div class="card pricing-card">
                                <div class="card-header">
                                    <div class="loading-skeleton skeleton-text" style="width: 60%;"></div>
                                </div>
                                <div class="card-body">
                                    <div class="loading-skeleton skeleton-price"></div>
                                    <div class="loading-skeleton skeleton-text"></div>
                                    <div class="loading-skeleton skeleton-text" style="width: 50%;"></div>
                                </div>
                            </div>
                        </div>
                        @endfor
                    </div>
                </div>
                
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
                                Select products to add to your order
                            </p>
                        </div>
                        
                        <div id="orderSummary" class="d-none">
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
                        
                        <button id="proceedBtn" class="btn btn-primary w-100 mt-4" disabled>
                            <i class="fas fa-credit-card me-2"></i>Proceed to Payment
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('[Purchase Messages] Page initialized');
    
    const state = {
        products: {},
        cart: {},
        currency: '{{ $accountCurrency }}',
        vatApplicable: {{ $vatApplicable ? 'true' : 'false' }},
        vatRate: 20,
        isLoading: true,
        error: null
    };

    const productLabels = {
        'sms': { name: 'SMS Message', icon: 'fa-sms', unit: 'per message' },
        'rcs_basic': { name: 'RCS Basic', icon: 'fa-comment-dots', unit: 'per message' },
        'rcs_single': { name: 'RCS Single', icon: 'fa-comments', unit: 'per message' },
        'vmn': { name: 'Virtual Mobile Number', icon: 'fa-phone', unit: 'per month' },
        'shortcode_keyword': { name: 'Shortcode Keyword', icon: 'fa-hashtag', unit: 'per month' },
        'ai': { name: 'AI Credits', icon: 'fa-robot', unit: 'per credit' }
    };

    function formatCurrency(amount) {
        const symbols = { 'GBP': '£', 'EUR': '€', 'USD': '$' };
        const symbol = symbols[state.currency] || state.currency + ' ';
        return symbol + amount.toFixed(2);
    }

    function renderPricingCards() {
        const grid = document.getElementById('pricingGrid');
        
        if (state.error) {
            document.getElementById('pricingContainer').classList.add('d-none');
            document.getElementById('errorState').classList.remove('d-none');
            document.getElementById('errorMessage').textContent = state.error;
            return;
        }

        if (Object.keys(state.products).length === 0) {
            grid.innerHTML = `
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                            <h5>No Products Available</h5>
                            <p class="text-muted">No pricing products are currently configured in HubSpot.</p>
                        </div>
                    </div>
                </div>
            `;
            return;
        }

        document.getElementById('pricingContainer').classList.remove('d-none');
        document.getElementById('errorState').classList.add('d-none');

        let html = '';
        for (const [key, product] of Object.entries(state.products)) {
            const label = productLabels[key] || { name: product.name, icon: 'fa-tag', unit: 'each' };
            const quantity = state.cart[key] || 0;
            
            html += `
                <div class="col-md-6 col-xl-4">
                    <div class="card pricing-card">
                        <div class="card-header">
                            <h5><i class="fas ${label.icon} me-2"></i>${label.name}</h5>
                            <div class="sku">SKU: ${product.sku}</div>
                        </div>
                        <div class="card-body">
                            <div class="price-display">
                                <span class="currency">${state.currency === 'GBP' ? '£' : (state.currency === 'EUR' ? '€' : '$')}</span>${product.price.toFixed(2)}
                                <span class="unit">/ ${label.unit}</span>
                            </div>
                            <p class="product-description">${product.description || 'Standard pricing for ' + label.name.toLowerCase()}</p>
                            
                            <div class="d-flex align-items-center gap-2 mt-3">
                                <label class="form-label mb-0 me-2">Qty:</label>
                                <input type="number" 
                                       class="form-control quantity-input" 
                                       min="0" 
                                       value="${quantity}"
                                       data-product="${key}"
                                       data-price="${product.price}"
                                       onchange="updateQuantity('${key}', this.value, ${product.price})">
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }
        
        grid.innerHTML = html;
    }

    window.updateQuantity = function(productKey, quantity, unitPrice) {
        const qty = parseInt(quantity) || 0;
        
        if (qty > 0) {
            state.cart[productKey] = { quantity: qty, unitPrice: unitPrice };
        } else {
            delete state.cart[productKey];
        }
        
        updateOrderSummary();
    };

    function updateOrderSummary() {
        const orderItems = document.getElementById('orderItems');
        const orderSummary = document.getElementById('orderSummary');
        const proceedBtn = document.getElementById('proceedBtn');
        
        const cartItems = Object.entries(state.cart);
        
        if (cartItems.length === 0) {
            orderItems.innerHTML = `
                <p class="text-muted text-center py-3">
                    <i class="fas fa-info-circle me-1"></i>
                    Select products to add to your order
                </p>
            `;
            orderSummary.classList.add('d-none');
            proceedBtn.disabled = true;
            return;
        }

        let itemsHtml = '';
        let netTotal = 0;

        for (const [key, item] of cartItems) {
            const label = productLabels[key] || { name: key };
            const lineTotal = item.quantity * item.unitPrice;
            netTotal += lineTotal;
            
            itemsHtml += `
                <div class="summary-row">
                    <span>${label.name} x ${item.quantity}</span>
                    <span>${formatCurrency(lineTotal)}</span>
                </div>
            `;
        }

        orderItems.innerHTML = itemsHtml;
        orderSummary.classList.remove('d-none');

        const vatAmount = state.vatApplicable ? netTotal * (state.vatRate / 100) : 0;
        const totalPayable = netTotal + vatAmount;

        document.getElementById('netTotal').textContent = formatCurrency(netTotal);
        document.getElementById('vatAmount').textContent = formatCurrency(vatAmount);
        document.getElementById('vatRateDisplay').textContent = state.vatRate;
        document.getElementById('totalPayable').textContent = formatCurrency(totalPayable);

        const vatRow = document.getElementById('vatRow');
        if (state.vatApplicable) {
            vatRow.classList.remove('d-none');
        } else {
            vatRow.classList.add('d-none');
        }

        proceedBtn.disabled = false;
    }

    window.loadPricing = async function() {
        state.isLoading = true;
        state.error = null;
        
        document.getElementById('pricingContainer').classList.remove('d-none');
        document.getElementById('errorState').classList.add('d-none');
        
        document.getElementById('pricingGrid').innerHTML = Array(6).fill(`
            <div class="col-md-6 col-xl-4">
                <div class="card pricing-card">
                    <div class="card-header">
                        <div class="loading-skeleton skeleton-text" style="width: 60%;"></div>
                    </div>
                    <div class="card-body">
                        <div class="loading-skeleton skeleton-price"></div>
                        <div class="loading-skeleton skeleton-text"></div>
                        <div class="loading-skeleton skeleton-text" style="width: 50%;"></div>
                    </div>
                </div>
            </div>
        `).join('');

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

            renderPricingCards();
            console.log('[Purchase] Loaded', Object.keys(state.products).length, 'products from HubSpot');

        } catch (error) {
            console.error('[Purchase] Error loading pricing:', error);
            state.error = error.message;
            state.isLoading = false;
            renderPricingCards();
        }
    };

    loadPricing();
});
</script>
@endpush
