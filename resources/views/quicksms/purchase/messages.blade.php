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
    border: 2px solid #e9ecef;
    border-radius: 0.75rem;
    transition: all 0.2s ease;
    height: 100%;
    overflow: hidden;
}
.tier-card:hover {
    border-color: var(--primary);
    box-shadow: 0 8px 24px rgba(111, 66, 193, 0.15);
}
.tier-card.tier-starter .tier-header {
    background: linear-gradient(135deg, rgba(28, 187, 140, 0.15) 0%, rgba(28, 187, 140, 0.05) 100%);
    border-bottom: 2px solid rgba(28, 187, 140, 0.2);
}
.tier-card.tier-starter .tier-badge {
    background: rgba(28, 187, 140, 0.15);
    color: #1cbb8c;
}
.tier-card.tier-starter .noUi-connect {
    background: #1cbb8c;
}
.tier-card.tier-starter .noUi-handle {
    border-color: #1cbb8c;
}
.tier-card.tier-enterprise .tier-header {
    background: linear-gradient(135deg, rgba(111, 66, 193, 0.15) 0%, rgba(111, 66, 193, 0.05) 100%);
    border-bottom: 2px solid rgba(111, 66, 193, 0.2);
}
.tier-card.tier-enterprise .tier-badge {
    background: rgba(111, 66, 193, 0.15);
    color: #6f42c1;
}
.tier-card.tier-enterprise .noUi-connect {
    background: #6f42c1;
}
.tier-card.tier-enterprise .noUi-handle {
    border-color: #6f42c1;
}
.tier-card.tier-bespoke .tier-header {
    background: linear-gradient(135deg, rgba(214, 83, 193, 0.15) 0%, rgba(214, 83, 193, 0.05) 100%);
    border-bottom: 2px solid rgba(214, 83, 193, 0.2);
}
.tier-card.tier-bespoke .tier-badge {
    background: rgba(214, 83, 193, 0.15);
    color: #D653C1;
}
.tier-card.tier-bespoke .noUi-connect {
    background: #D653C1;
}
.tier-card.tier-bespoke .noUi-handle {
    border-color: #D653C1;
}
.tier-header {
    padding: 1.5rem;
    text-align: center;
}
.tier-header h4 {
    margin-bottom: 0.5rem;
    font-weight: 700;
}
.tier-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 1rem;
    font-size: 0.75rem;
    font-weight: 600;
    margin-bottom: 0.75rem;
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
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
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
.tier-body {
    padding: 1.5rem;
}
.tier-description {
    color: #6c757d;
    font-size: 0.875rem;
    margin-bottom: 1.5rem;
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
    background: #f8f9fa;
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
                            <div class="card tier-card tier-bespoke" data-tier="bespoke">
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
                                </div>
                                <div class="tier-body">
                                    <p class="tier-description">Tailored pricing for high-volume enterprise customers with custom requirements and dedicated support.</p>
                                    <div id="bespokePrices">
                                        <div class="loading-skeleton skeleton-row"></div>
                                        <div class="loading-skeleton skeleton-row"></div>
                                        <div class="loading-skeleton skeleton-row"></div>
                                    </div>
                                </div>
                                <div class="tier-footer">
                                    <button class="btn btn-primary w-100" onclick="selectTier('bespoke')">
                                        <i class="fas fa-check me-2"></i>Select Bespoke Plan
                                    </button>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="col-md-6">
                            <div class="card tier-card tier-starter" data-tier="starter">
                                <div class="tier-header">
                                    <span class="tier-badge"><i class="fas fa-rocket me-1"></i>SMB</span>
                                    <h4>Starter</h4>
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
                                </div>
                                <div class="tier-body">
                                    <p class="tier-description">Perfect for small and medium businesses getting started with SMS and RCS messaging.</p>
                                    <div id="starterPrices">
                                        <div class="loading-skeleton skeleton-row"></div>
                                        <div class="loading-skeleton skeleton-row"></div>
                                        <div class="loading-skeleton skeleton-row"></div>
                                    </div>
                                </div>
                                <div class="tier-footer">
                                    <button class="btn btn-outline-primary w-100" onclick="selectTier('starter')">
                                        <i class="fas fa-check me-2"></i>Select Starter
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card tier-card tier-enterprise" data-tier="enterprise">
                                <div class="tier-header">
                                    <span class="tier-badge"><i class="fas fa-building me-1"></i>Business</span>
                                    <h4>Enterprise</h4>
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
                                </div>
                                <div class="tier-body">
                                    <p class="tier-description">Designed for larger organizations with higher messaging volumes and advanced needs.</p>
                                    <div id="enterprisePrices">
                                        <div class="loading-skeleton skeleton-row"></div>
                                        <div class="loading-skeleton skeleton-row"></div>
                                        <div class="loading-skeleton skeleton-row"></div>
                                    </div>
                                </div>
                                <div class="tier-footer">
                                    <button class="btn btn-outline-primary w-100" onclick="selectTier('enterprise')">
                                        <i class="fas fa-check me-2"></i>Select Enterprise
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
            increment: 10000,
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

            state.sliders[tierId].on('update', function(values) {
                const value = parseInt(values[0].replace(/,/g, ''));
                state.sliderValues[tierId] = value;
                document.getElementById(tierId + 'SliderValue').textContent = formatNumber(value);
                
                if (state.selectedTier === tierId) {
                    updateOrderSummary();
                }
            });

            console.log(`[Slider] Initialized ${tierId} slider with ${snapValues.length} snap points`);
        });
    }

    function renderPricesForTier(tierId) {
        const container = document.getElementById(tierId + 'Prices');
        if (!container) return;

        if (state.error) {
            container.innerHTML = `<p class="text-danger small"><i class="fas fa-exclamation-circle me-1"></i>Failed to load prices</p>`;
            return;
        }

        if (Object.keys(state.products).length === 0) {
            container.innerHTML = `<p class="text-muted small">No products available</p>`;
            return;
        }

        let html = '';
        for (const [key, product] of Object.entries(state.products)) {
            const label = productLabels[key] || { name: product.name, icon: 'fa-tag', unit: '' };
            html += `
                <div class="price-row">
                    <span class="product-name">
                        <i class="fas ${label.icon}"></i>${label.name}
                    </span>
                    <span class="product-price">
                        ${formatCurrency(product.price)}
                        <span class="product-unit">${label.unit}</span>
                    </span>
                </div>
            `;
        }
        
        container.innerHTML = html;
    }

    window.selectTier = function(tierId) {
        document.querySelectorAll('.tier-card').forEach(card => {
            card.classList.remove('selected-tier');
            const btn = card.querySelector('.tier-footer button');
            if (btn) {
                btn.classList.remove('btn-primary');
                btn.classList.add('btn-outline-primary');
                btn.innerHTML = `<i class="fas fa-check me-2"></i>Select ${tierConfig[card.dataset.tier]?.name || 'Plan'}`;
            }
        });

        const selectedCard = document.querySelector(`[data-tier="${tierId}"]`);
        if (selectedCard) {
            selectedCard.classList.add('selected-tier');
            const btn = selectedCard.querySelector('.tier-footer button');
            if (btn) {
                btn.classList.remove('btn-outline-primary');
                btn.classList.add('btn-primary');
                btn.innerHTML = `<i class="fas fa-check-circle me-2"></i>Selected`;
            }
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
        const smsPrice = state.products.sms?.price || 0;
        const netTotal = quantity * smsPrice;
        const vatAmount = state.vatApplicable ? netTotal * (state.vatRate / 100) : 0;
        const totalPayable = netTotal + vatAmount;

        orderItems.innerHTML = '';
        orderSummary.classList.remove('d-none');

        document.getElementById('selectedTierName').textContent = tier.name;
        document.getElementById('selectedQuantity').textContent = formatNumber(quantity) + ' messages';
        document.getElementById('netTotal').textContent = formatCurrencyShort(netTotal);
        document.getElementById('vatAmount').textContent = formatCurrencyShort(vatAmount);
        document.getElementById('vatRateDisplay').textContent = state.vatRate;
        document.getElementById('totalPayable').textContent = formatCurrencyShort(totalPayable);

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
                renderPricesForTier('bespoke');
            } else {
                renderPricesForTier('starter');
                renderPricesForTier('enterprise');
            }

            console.log('[Purchase] Loaded', Object.keys(state.products).length, 'products from HubSpot');

        } catch (error) {
            console.error('[Purchase] Error loading pricing:', error);
            state.error = error.message;
            state.isLoading = false;
            
            if (state.bespokePricing) {
                renderPricesForTier('bespoke');
            } else {
                renderPricesForTier('starter');
                renderPricesForTier('enterprise');
            }
        }
    };

    initializeSliders();
    loadPricing();
});
</script>
@endpush
