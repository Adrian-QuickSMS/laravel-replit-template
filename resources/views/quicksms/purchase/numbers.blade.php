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
@endsection

@push('scripts')
<script>
var currentUserRole = 'admin';

var allowedRoles = ['admin', 'finance', 'messaging_manager'];

document.addEventListener('DOMContentLoaded', function() {
    checkAccess();
});

function checkAccess() {
    var hasAccess = allowedRoles.includes(currentUserRole);
    
    document.getElementById('accessDeniedView').style.display = hasAccess ? 'none' : 'block';
    document.getElementById('purchaseContent').style.display = hasAccess ? 'block' : 'none';
}

function selectNumberType(type) {
    console.log('TODO: API call - POST /api/purchase/numbers/vmn with type:', type);
    alert('Number selection coming soon. Please contact sales for early access.');
}

function selectKeywordType(type) {
    console.log('TODO: API call - POST /api/purchase/numbers/shortcode with type:', type);
    alert('Keyword reservation coming soon. Please contact sales for enquiries.');
}
</script>
@endpush
