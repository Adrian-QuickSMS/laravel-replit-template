@extends('layouts.quicksms')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid">
    <div class="row page-titles">
        <ol class="breadcrumb">
            <li class="breadcrumb-item active">Dashboard</li>
        </ol>
    </div>
    
    <section class="mb-4" id="operationalOverview">
        <div class="d-flex align-items-center mb-3">
            <h4 class="mb-0"><i class="fas fa-chart-line me-2 text-primary"></i>Operational Overview</h4>
        </div>
        <div class="row">
            <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6 mb-3">
                <a href="{{ route('purchase') }}" class="text-decoration-none">
                    <div class="card dashboard-tile h-100" id="tile-balance">
                        <div class="card-body p-3">
                            <div class="tile-loading d-none">
                                <div class="skeleton-shimmer mb-2" style="height: 14px; width: 60%;"></div>
                                <div class="skeleton-shimmer" style="height: 28px; width: 80%;"></div>
                            </div>
                            <div class="tile-error d-none">
                                <div class="text-center text-danger">
                                    <i class="fas fa-exclamation-triangle mb-1"></i>
                                    <p class="mb-0 small">Error loading</p>
                                </div>
                            </div>
                            <div class="tile-content">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="text-muted small">Balance</span>
                                    <div class="icon-box-sm bg-primary-light rounded-circle">
                                        <i class="fas fa-sterling-sign text-primary"></i>
                                    </div>
                                </div>
                                <h3 class="mb-0 tile-value" id="balance-value">£0.00</h3>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6 mb-3">
                <a href="{{ route('messages.inbox') }}?filter=waiting" class="text-decoration-none">
                    <div class="card dashboard-tile h-100" id="tile-inbound">
                        <div class="card-body p-3">
                            <div class="tile-loading d-none">
                                <div class="skeleton-shimmer mb-2" style="height: 14px; width: 80%;"></div>
                                <div class="skeleton-shimmer" style="height: 28px; width: 50%;"></div>
                            </div>
                            <div class="tile-error d-none">
                                <div class="text-center text-danger">
                                    <i class="fas fa-exclamation-triangle mb-1"></i>
                                    <p class="mb-0 small">Error loading</p>
                                </div>
                            </div>
                            <div class="tile-content">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="text-muted small">Inbound (Unresponded)</span>
                                    <div class="icon-box-sm bg-warning-light rounded-circle">
                                        <i class="fas fa-inbox text-warning"></i>
                                    </div>
                                </div>
                                <h3 class="mb-0 tile-value" id="inbound-value">0</h3>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6 mb-3">
                <a href="{{ route('messages.campaign-history') }}?date=today" class="text-decoration-none">
                    <div class="card dashboard-tile h-100" id="tile-messages-today">
                        <div class="card-body p-3">
                            <div class="tile-loading d-none">
                                <div class="skeleton-shimmer mb-2" style="height: 14px; width: 90%;"></div>
                                <div class="skeleton-shimmer" style="height: 28px; width: 40%;"></div>
                            </div>
                            <div class="tile-error d-none">
                                <div class="text-center text-danger">
                                    <i class="fas fa-exclamation-triangle mb-1"></i>
                                    <p class="mb-0 small">Error loading</p>
                                </div>
                            </div>
                            <div class="tile-content">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="text-muted small">Messages Sent Today</span>
                                    <div class="icon-box-sm bg-success-light rounded-circle">
                                        <i class="fas fa-paper-plane text-success"></i>
                                    </div>
                                </div>
                                <h3 class="mb-0 tile-value" id="messages-today-value">0</h3>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6 mb-3">
                <div class="card dashboard-tile h-100" id="tile-delivery-rate">
                    <div class="card-body p-3">
                        <div class="tile-loading d-none">
                            <div class="skeleton-shimmer mb-2" style="height: 14px; width: 70%;"></div>
                            <div class="skeleton-shimmer" style="height: 28px; width: 45%;"></div>
                        </div>
                        <div class="tile-error d-none">
                            <div class="text-center text-danger">
                                <i class="fas fa-exclamation-triangle mb-1"></i>
                                <p class="mb-0 small">Error loading</p>
                            </div>
                        </div>
                        <div class="tile-content">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="text-muted small">Delivery Rate (%)</span>
                                <div class="icon-box-sm bg-info-light rounded-circle">
                                    <i class="fas fa-check-double text-info"></i>
                                </div>
                            </div>
                            <h3 class="mb-0 tile-value delivery-rate-value" id="delivery-rate-value" data-rate="0">0%</h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6 mb-3">
                <a href="{{ route('purchase') }}" class="text-decoration-none">
                    <div class="card dashboard-tile dashboard-action-tile h-100" id="tile-make-payment">
                        <div class="card-body p-3 d-flex flex-column align-items-center justify-content-center text-center">
                            <div class="icon-box bg-primary rounded-circle mb-2" style="width: 48px; height: 48px;">
                                <i class="fas fa-credit-card text-white fa-lg"></i>
                            </div>
                            <span class="fw-medium text-dark">Make Payment</span>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6 mb-3">
                <a href="{{ route('management.numbers') }}" class="text-decoration-none">
                    <div class="card dashboard-tile dashboard-action-tile h-100" id="tile-buy-number">
                        <div class="card-body p-3 d-flex flex-column align-items-center justify-content-center text-center">
                            <div class="icon-box bg-success rounded-circle mb-2" style="width: 48px; height: 48px;">
                                <i class="fas fa-sim-card text-white fa-lg"></i>
                            </div>
                            <span class="fw-medium text-dark">Buy Number</span>
                        </div>
                    </div>
                </a>
            </div>
        </div>
        
        <div class="row">
            <div class="col-12 mb-3">
                <div class="card dashboard-tile" id="tile-traffic-graph">
                    <div class="card-header border-0 pb-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h5 class="card-title mb-0"><i class="fas fa-chart-area me-2 text-primary"></i>Message Traffic</h5>
                        <div class="btn-group" role="group" id="trafficToggle">
                            <input type="radio" class="btn-check" name="trafficPeriod" id="trafficToday" value="today" checked>
                            <label class="btn btn-outline-primary btn-sm" for="trafficToday">Today</label>
                            <input type="radio" class="btn-check" name="trafficPeriod" id="traffic7Days" value="7days">
                            <label class="btn btn-outline-primary btn-sm" for="traffic7Days">Last 7 Days</label>
                            <input type="radio" class="btn-check" name="trafficPeriod" id="traffic30Days" value="30days">
                            <label class="btn btn-outline-primary btn-sm" for="traffic30Days">Last 30 Days</label>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="tile-loading d-none">
                            <div class="d-flex align-items-center justify-content-center" style="height: 300px;">
                                <div class="skeleton-shimmer" style="width: 100%; height: 250px;"></div>
                            </div>
                        </div>
                        <div class="tile-error d-none">
                            <div class="d-flex align-items-center justify-content-center" style="height: 300px;">
                                <div class="text-center text-danger">
                                    <i class="fas fa-exclamation-triangle fa-3x mb-2"></i>
                                    <p class="mb-0">Failed to load chart data</p>
                                </div>
                            </div>
                        </div>
                        <div class="tile-content">
                            <div id="trafficChart" style="height: 300px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <section class="mb-4" id="rcsPromotion">
        <div class="d-flex align-items-center mb-3">
            <h4 class="mb-0"><i class="fas fa-rocket me-2 text-primary"></i>RCS Promotion & Tools</h4>
        </div>
        <div class="row">
            <div class="col-xl-6 col-lg-6 mb-3">
                <a href="#" class="text-decoration-none" id="rcs-ad-link">
                    <div class="card dashboard-tile h-100" id="tile-rcs-advertisement">
                        <div class="row g-0 h-100">
                            <div class="col-md-5">
                                <div class="rcs-ad-image d-flex align-items-center justify-content-center h-100" style="background: linear-gradient(135deg, #886cc0 0%, #6a4fa0 100%); border-radius: 0.625rem 0 0 0.625rem; min-height: 200px;">
                                    <div class="text-center text-white p-3">
                                        <i class="fas fa-comment-dots fa-4x mb-2 opacity-75"></i>
                                        <p class="mb-0 small opacity-75">Promo Image</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-7">
                                <div class="card-body d-flex flex-column justify-content-center h-100">
                                    <span class="badge bg-primary mb-2" style="width: fit-content;">New Feature</span>
                                    <h4 class="card-title mb-2" id="rcs-ad-header">Unlock RCS Messaging</h4>
                                    <p class="card-text text-muted mb-3" id="rcs-ad-subtitle">Transform your customer engagement with rich, interactive messages. Get higher open rates and better conversions.</p>
                                    <button class="btn btn-primary" style="width: fit-content;" id="rcs-ad-cta">
                                        <i class="fas fa-arrow-right me-2"></i>Get Started
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            
            <div class="col-xl-6 col-lg-6 mb-3">
                <div class="card dashboard-tile h-100" id="tile-rcs-calculator">
                    <div class="card-header border-0 pb-0">
                        <h5 class="card-title mb-0"><i class="fas fa-calculator me-2 text-success"></i>RCS vs SMS Savings Calculator</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-2 mb-3">
                            <div class="col-6 col-md-4">
                                <label class="form-label small mb-1">SMS Price (£)</label>
                                <input type="number" class="form-control form-control-sm" id="calcSmsPrice" placeholder="0.035" step="0.001" value="0.035">
                            </div>
                            <div class="col-6 col-md-4">
                                <label class="form-label small mb-1">RCS Price (£)</label>
                                <input type="number" class="form-control form-control-sm" id="calcRcsPrice" placeholder="0.045" step="0.001" value="0.045">
                            </div>
                            <div class="col-6 col-md-4">
                                <label class="form-label small mb-1">Avg Fragments</label>
                                <input type="number" class="form-control form-control-sm" id="calcFragments" placeholder="2" min="1" value="2">
                            </div>
                            <div class="col-6 col-md-4">
                                <label class="form-label small mb-1">RCS Penetration %</label>
                                <input type="number" class="form-control form-control-sm" id="calcPenetration" placeholder="60" min="0" max="100" value="60">
                            </div>
                            <div class="col-6 col-md-4">
                                <label class="form-label small mb-1">Messages</label>
                                <input type="number" class="form-control form-control-sm" id="calcMessages" placeholder="10000" value="10000">
                            </div>
                            <div class="col-6 col-md-4 d-flex align-items-end">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="calcVat">
                                    <label class="form-check-label small" for="calcVat">Include VAT</label>
                                </div>
                            </div>
                        </div>
                        <hr class="my-2">
                        <div class="row g-2">
                            <div class="col-4 text-center">
                                <p class="mb-1 text-muted small">SMS Only Cost</p>
                                <h5 class="mb-0 text-danger" id="calcSmsOnlyCost">£0.00</h5>
                            </div>
                            <div class="col-4 text-center">
                                <p class="mb-1 text-muted small">Blended Cost</p>
                                <h5 class="mb-0 text-success" id="calcBlendedCost">£0.00</h5>
                            </div>
                            <div class="col-4 text-center">
                                <p class="mb-1 text-muted small">You Save</p>
                                <h5 class="mb-0 text-primary" id="calcSavings">0%</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-xl-6 col-lg-6 mb-3">
                <div class="card dashboard-tile h-100" id="tile-test-rcs">
                    <div class="card-header border-0 pb-0">
                        <h5 class="card-title mb-0"><i class="fas fa-mobile-alt me-2 text-info"></i>Test RCS Message</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">Send a test RCS message to your mobile to see it in action.</p>
                        <div class="row g-2 align-items-end">
                            <div class="col">
                                <label class="form-label small mb-1">Mobile Number</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                    <input type="tel" class="form-control" id="testRcsMobile" placeholder="+44 7700 900000">
                                </div>
                            </div>
                            <div class="col-auto">
                                <button class="btn btn-info" id="btnSendTestRcs" onclick="sendTestRcs()">
                                    <i class="fas fa-paper-plane me-1"></i>Send Test
                                </button>
                            </div>
                        </div>
                        <div class="mt-3 d-none" id="testRcsResult">
                            <div class="alert alert-success mb-0 py-2" id="testRcsSuccess">
                                <i class="fas fa-check-circle me-2"></i>Test message sent successfully!
                            </div>
                            <div class="alert alert-danger mb-0 py-2 d-none" id="testRcsFail">
                                <i class="fas fa-times-circle me-2"></i>Failed to send test message. Please try again.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-6 col-lg-6 mb-3" id="tile-register-rcs-wrapper">
                <div class="card dashboard-tile h-100" id="tile-register-rcs">
                    <div class="card-body d-flex align-items-center">
                        <div class="icon-box bg-warning rounded-circle me-3" style="width: 56px; height: 56px; flex-shrink: 0;">
                            <i class="fas fa-id-card text-white fa-lg"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="mb-1">Register for RCS</h5>
                            <p class="text-muted mb-0 small">Get your brand verified and start sending rich messages to your customers.</p>
                        </div>
                        <a href="{{ route('management.rcs-agent') }}" class="btn btn-warning ms-3" id="btnRegisterRcs">
                            <i class="fas fa-arrow-right me-1"></i>Register Now
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <section class="mb-4" id="supportNotifications">
        <div class="d-flex align-items-center mb-3">
            <h4 class="mb-0"><i class="fas fa-bell me-2 text-primary"></i>Support & Notifications</h4>
        </div>
        <div class="row">
            <div class="col-xl-4 col-lg-6">
                <div class="card" id="tile-open-tickets">
                    <div class="card-header border-0 pb-0 d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Open Tickets</h5>
                        <a href="{{ route('support.dashboard') }}" class="text-primary small">View All</a>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-center" style="height: 150px; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 0.5rem;">
                            <div class="text-center text-muted">
                                <i class="fas fa-ticket-alt fa-2x mb-2 opacity-50"></i>
                                <p class="mb-0 small">No open tickets</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-lg-6">
                <div class="card" id="tile-system-alerts">
                    <div class="card-header border-0 pb-0 d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">System Alerts</h5>
                        <span class="badge bg-success">All Clear</span>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-center" style="height: 150px; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 0.5rem;">
                            <div class="text-center text-muted">
                                <i class="fas fa-shield-alt fa-2x mb-2 opacity-50"></i>
                                <p class="mb-0 small">No active alerts</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-lg-6">
                <div class="card" id="tile-recent-activity">
                    <div class="card-header border-0 pb-0 d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Recent Activity</h5>
                        <a href="{{ route('account.audit-logs') }}" class="text-primary small">View All</a>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-center" style="height: 150px; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 0.5rem;">
                            <div class="text-center text-muted">
                                <i class="fas fa-history fa-2x mb-2 opacity-50"></i>
                                <p class="mb-0 small">Loading activity...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-xl-6">
                <div class="card" id="tile-announcements">
                    <div class="card-header border-0 pb-0">
                        <h5 class="card-title mb-0">Announcements</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-center" style="height: 120px; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 0.5rem;">
                            <div class="text-center text-muted">
                                <i class="fas fa-bullhorn fa-2x mb-2 opacity-50"></i>
                                <p class="mb-0 small">No new announcements</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-6">
                <div class="card" id="tile-help-resources">
                    <div class="card-header border-0 pb-0">
                        <h5 class="card-title mb-0">Help & Resources</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-2">
                            <div class="col-6">
                                <a href="{{ route('support.knowledge-base') }}" class="btn btn-light w-100 py-2 d-flex align-items-center gap-2">
                                    <i class="fas fa-book text-primary"></i>
                                    <span class="small">Knowledge Base</span>
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="{{ route('support.create-ticket') }}" class="btn btn-light w-100 py-2 d-flex align-items-center gap-2">
                                    <i class="fas fa-headset text-primary"></i>
                                    <span class="small">Create Ticket</span>
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="#" class="btn btn-light w-100 py-2 d-flex align-items-center gap-2 disabled">
                                    <i class="fas fa-video text-primary"></i>
                                    <span class="small">Video Tutorials</span>
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="{{ route('management.api-connections') }}" class="btn btn-light w-100 py-2 d-flex align-items-center gap-2">
                                    <i class="fas fa-code text-primary"></i>
                                    <span class="small">API Docs</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

@push('styles')
<style>
.icon-box {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.icon-box-sm {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.bg-primary-light {
    background-color: rgba(136, 108, 192, 0.1);
}
.bg-success-light {
    background-color: rgba(40, 167, 69, 0.1);
}
.bg-info-light {
    background-color: rgba(23, 162, 184, 0.1);
}
.bg-warning-light {
    background-color: rgba(255, 193, 7, 0.1);
}
.bg-danger-light {
    background-color: rgba(220, 53, 69, 0.1);
}

.dashboard-tile {
    transition: all 0.2s ease;
    cursor: pointer;
    border: 1px solid rgba(0,0,0,0.08);
}
.dashboard-tile:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}
.dashboard-action-tile {
    min-height: 110px;
    background: linear-gradient(135deg, #fafbfc 0%, #f4f5f7 100%);
}
.dashboard-action-tile:hover {
    background: linear-gradient(135deg, #f0f1f3 0%, #e8e9eb 100%);
}

.tile-value {
    font-size: 1.5rem;
    font-weight: 600;
    color: #333;
}

.skeleton-shimmer {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: shimmer 1.5s infinite;
    border-radius: 4px;
}
@keyframes shimmer {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}

.delivery-rate-green {
    color: #28a745 !important;
}
.delivery-rate-amber {
    color: #ffc107 !important;
}
.delivery-rate-red {
    color: #dc3545 !important;
}

#operationalOverview .card,
#rcsPromotion .card,
#supportNotifications .card {
    transition: box-shadow 0.2s ease;
}
#rcsPromotion .card:hover,
#supportNotifications .card:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
}
</style>
@endpush

@push('scripts')
<script src="/vendor/apexchart/apexchart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    function updateDeliveryRateColor() {
        var rateEl = document.getElementById('delivery-rate-value');
        if (!rateEl) return;
        
        var rate = parseFloat(rateEl.dataset.rate) || 0;
        rateEl.classList.remove('delivery-rate-green', 'delivery-rate-amber', 'delivery-rate-red');
        
        if (rate > 95) {
            rateEl.classList.add('delivery-rate-green');
        } else if (rate >= 90) {
            rateEl.classList.add('delivery-rate-amber');
        } else {
            rateEl.classList.add('delivery-rate-red');
        }
    }
    
    function setTileLoading(tileId, isLoading) {
        var tile = document.getElementById(tileId);
        if (!tile) return;
        
        var loadingEl = tile.querySelector('.tile-loading');
        var contentEl = tile.querySelector('.tile-content');
        var errorEl = tile.querySelector('.tile-error');
        
        if (isLoading) {
            if (loadingEl) loadingEl.classList.remove('d-none');
            if (contentEl) contentEl.classList.add('d-none');
            if (errorEl) errorEl.classList.add('d-none');
        } else {
            if (loadingEl) loadingEl.classList.add('d-none');
            if (contentEl) contentEl.classList.remove('d-none');
        }
    }
    
    function setTileError(tileId, hasError) {
        var tile = document.getElementById(tileId);
        if (!tile) return;
        
        var loadingEl = tile.querySelector('.tile-loading');
        var contentEl = tile.querySelector('.tile-content');
        var errorEl = tile.querySelector('.tile-error');
        
        if (hasError) {
            if (loadingEl) loadingEl.classList.add('d-none');
            if (contentEl) contentEl.classList.add('d-none');
            if (errorEl) errorEl.classList.remove('d-none');
        } else {
            if (errorEl) errorEl.classList.add('d-none');
        }
    }
    
    // TODO: Replace with actual API calls to load tile data
    
    updateDeliveryRateColor();
    
    window.setTileLoading = setTileLoading;
    window.setTileError = setTileError;
    window.updateDeliveryRateColor = updateDeliveryRateColor;
    
    // Traffic Graph
    var trafficChart = null;
    
    function getDummyData(period) {
        var categories = [];
        var data = [];
        
        if (period === 'today') {
            categories = ['00:00', '03:00', '06:00', '09:00', '12:00', '15:00', '18:00', '21:00'];
            data = [12, 8, 25, 89, 156, 134, 98, 45];
        } else if (period === '7days') {
            var days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
            categories = days;
            data = [342, 456, 389, 512, 478, 234, 189];
        } else if (period === '30days') {
            for (var i = 1; i <= 30; i++) {
                categories.push('Day ' + i);
                data.push(Math.floor(Math.random() * 400) + 100);
            }
        }
        
        return { categories: categories, data: data };
    }
    
    function renderTrafficChart(period) {
        var dummyData = getDummyData(period);
        
        var options = {
            series: [{
                name: 'Total Messages',
                data: dummyData.data
            }],
            chart: {
                type: 'area',
                height: 300,
                fontFamily: 'inherit',
                toolbar: {
                    show: false
                },
                zoom: {
                    enabled: false
                }
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'smooth',
                width: 2
            },
            colors: ['#886cc0'],
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.4,
                    opacityTo: 0.1,
                    stops: [0, 90, 100]
                }
            },
            xaxis: {
                categories: dummyData.categories,
                labels: {
                    style: {
                        colors: '#888',
                        fontSize: '11px'
                    }
                },
                axisBorder: {
                    show: false
                },
                axisTicks: {
                    show: false
                }
            },
            yaxis: {
                labels: {
                    style: {
                        colors: '#888',
                        fontSize: '11px'
                    },
                    formatter: function(val) {
                        return Math.round(val);
                    }
                }
            },
            grid: {
                borderColor: '#f1f1f1',
                strokeDashArray: 3
            },
            tooltip: {
                y: {
                    formatter: function(val) {
                        return val + ' messages';
                    }
                }
            }
        };
        
        if (trafficChart) {
            trafficChart.destroy();
        }
        
        var chartEl = document.querySelector('#trafficChart');
        if (chartEl) {
            trafficChart = new ApexCharts(chartEl, options);
            trafficChart.render();
        }
    }
    
    // TODO: Replace with actual API call
    function loadTrafficData(period) {
        // setTileLoading('tile-traffic-graph', true);
        // fetch('/api/traffic?period=' + period)
        //     .then(res => res.json())
        //     .then(data => {
        //         renderTrafficChart(period, data);
        //         setTileLoading('tile-traffic-graph', false);
        //     })
        //     .catch(() => setTileError('tile-traffic-graph', true));
        
        renderTrafficChart(period);
    }
    
    // Toggle handlers
    var toggleInputs = document.querySelectorAll('input[name="trafficPeriod"]');
    toggleInputs.forEach(function(input) {
        input.addEventListener('change', function() {
            loadTrafficData(this.value);
        });
    });
    
    // Initial render
    loadTrafficData('today');
    
    window.loadTrafficData = loadTrafficData;
    window.renderTrafficChart = renderTrafficChart;
});

function sendTestRcs() {
    var mobileInput = document.getElementById('testRcsMobile');
    var resultDiv = document.getElementById('testRcsResult');
    var successDiv = document.getElementById('testRcsSuccess');
    var failDiv = document.getElementById('testRcsFail');
    var btn = document.getElementById('btnSendTestRcs');
    
    var mobile = mobileInput.value.trim();
    
    if (!mobile) {
        mobileInput.classList.add('is-invalid');
        return;
    }
    
    mobileInput.classList.remove('is-invalid');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Sending...';
    
    // TODO: Replace with actual API call
    // POST /api/rcs/test { mobile: mobile }
    setTimeout(function() {
        resultDiv.classList.remove('d-none');
        
        // Placeholder: randomly show success or fail for demo
        var isSuccess = Math.random() > 0.3;
        
        if (isSuccess) {
            successDiv.classList.remove('d-none');
            failDiv.classList.add('d-none');
        } else {
            successDiv.classList.add('d-none');
            failDiv.classList.remove('d-none');
        }
        
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-paper-plane me-1"></i>Send Test';
    }, 1500);
}

// TODO: Placeholder calculator function - no math yet
function calculateSavings() {
    // Will be implemented when backend is ready
    // Read: calcSmsPrice, calcRcsPrice, calcFragments, calcPenetration, calcMessages, calcVat
    // Update: calcSmsOnlyCost, calcBlendedCost, calcSavings
}
</script>
@endpush
@endsection
