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
        
        <!-- Row 1: Four stat tiles -->
        <div class="row">
            <!-- Balance Tile -->
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 mb-3">
                <a href="{{ route('purchase') }}" class="text-decoration-none">
                    <div class="widget-stat card" id="tile-balance">
                        <div class="card-body p-4">
                            <div class="tile-loading d-none">
                                <div class="d-flex align-items-center">
                                    <div class="skeleton-shimmer rounded-circle me-3" style="width: 85px; height: 85px;"></div>
                                    <div class="flex-grow-1">
                                        <div class="skeleton-shimmer mb-2" style="height: 14px; width: 50%;"></div>
                                        <div class="skeleton-shimmer" style="height: 28px; width: 70%;"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="tile-error d-none">
                                <div class="text-center text-danger py-3">
                                    <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                                    <p class="mb-0">Error loading</p>
                                </div>
                            </div>
                            <div class="tile-content">
                                <div class="media ai-icon">
                                    <span class="me-3 bgl-primary text-primary">
                                        <i class="fas fa-sterling-sign"></i>
                                    </span>
                                    <div class="media-body">
                                        <p class="mb-1">Balance</p>
                                        <h4 class="mb-0 tile-value" id="balance-value">£0.00</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            
            <!-- Inbound Tile -->
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 mb-3">
                <a href="{{ route('messages.inbox') }}?filter=waiting" class="text-decoration-none">
                    <div class="widget-stat card" id="tile-inbound">
                        <div class="card-body p-4">
                            <div class="tile-loading d-none">
                                <div class="d-flex align-items-center">
                                    <div class="skeleton-shimmer rounded-circle me-3" style="width: 85px; height: 85px;"></div>
                                    <div class="flex-grow-1">
                                        <div class="skeleton-shimmer mb-2" style="height: 14px; width: 60%;"></div>
                                        <div class="skeleton-shimmer" style="height: 28px; width: 40%;"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="tile-error d-none">
                                <div class="text-center text-danger py-3">
                                    <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                                    <p class="mb-0">Error loading</p>
                                </div>
                            </div>
                            <div class="tile-content">
                                <div class="media ai-icon">
                                    <span class="me-3 bgl-warning text-warning">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                            <polyline points="22,6 12,13 2,6"></polyline>
                                        </svg>
                                    </span>
                                    <div class="media-body">
                                        <p class="mb-1">Unread Messages</p>
                                        <h4 class="mb-0 tile-value" id="inbound-value">0</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            
            <!-- Messages Sent Today Tile -->
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 mb-3">
                <a href="{{ route('messages.campaign-history') }}?date=today" class="text-decoration-none">
                    <div class="widget-stat card" id="tile-messages-today">
                        <div class="card-body p-4">
                            <div class="tile-loading d-none">
                                <div class="d-flex align-items-center">
                                    <div class="skeleton-shimmer rounded-circle me-3" style="width: 85px; height: 85px;"></div>
                                    <div class="flex-grow-1">
                                        <div class="skeleton-shimmer mb-2" style="height: 14px; width: 70%;"></div>
                                        <div class="skeleton-shimmer" style="height: 28px; width: 35%;"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="tile-error d-none">
                                <div class="text-center text-danger py-3">
                                    <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                                    <p class="mb-0">Error loading</p>
                                </div>
                            </div>
                            <div class="tile-content">
                                <div class="media ai-icon">
                                    <span class="me-3 bgl-success text-success">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <line x1="22" y1="2" x2="11" y2="13"></line>
                                            <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                                        </svg>
                                    </span>
                                    <div class="media-body">
                                        <p class="mb-1">Messages Sent Today</p>
                                        <h4 class="mb-0 tile-value" id="messages-today-value">0</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            
            <!-- Delivery Rate Tile -->
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 mb-3">
                <div class="widget-stat card" id="tile-delivery-rate">
                    <div class="card-body p-4">
                        <div class="tile-loading d-none">
                            <div class="d-flex align-items-center">
                                <div class="skeleton-shimmer rounded-circle me-3" style="width: 85px; height: 85px;"></div>
                                <div class="flex-grow-1">
                                    <div class="skeleton-shimmer mb-2" style="height: 14px; width: 55%;"></div>
                                    <div class="skeleton-shimmer" style="height: 28px; width: 40%;"></div>
                                </div>
                            </div>
                        </div>
                        <div class="tile-error d-none">
                            <div class="text-center text-danger py-3">
                                <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                                <p class="mb-0">Error loading</p>
                            </div>
                        </div>
                        <div class="tile-content">
                            <div class="media ai-icon">
                                <span class="me-3 bgl-info text-info">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="19" y1="5" x2="5" y2="19"></line>
                                        <circle cx="6.5" cy="6.5" r="2.5"></circle>
                                        <circle cx="17.5" cy="17.5" r="2.5"></circle>
                                    </svg>
                                </span>
                                <div class="media-body">
                                    <p class="mb-1">Delivery Rate</p>
                                    <h4 class="mb-0 tile-value delivery-rate-value" id="delivery-rate-value" data-rate="0">0%</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Row 2: Quick Action tiles (left-justified) -->
        <div class="row">
            <!-- Make a Payment -->
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 mb-3">
                <a href="{{ route('purchase.messages') }}" class="text-decoration-none">
                    <div class="widget-stat card bg-primary" id="tile-make-payment">
                        <div class="card-body p-4">
                            <div class="media">
                                <span class="me-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                                        <line x1="1" y1="10" x2="23" y2="10"></line>
                                    </svg>
                                </span>
                                <div class="media-body text-white text-end">
                                    <p class="mb-1">Make a</p>
                                    <h4 class="text-white mb-0">Payment</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            
            <!-- Buy a Number -->
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 mb-3">
                <a href="{{ route('purchase.numbers') }}" class="text-decoration-none">
                    <div class="widget-stat card bg-warning" id="tile-buy-number">
                        <div class="card-body p-4">
                            <div class="media">
                                <span class="me-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="5" y="2" width="14" height="20" rx="2" ry="2"></rect>
                                        <line x1="12" y1="18" x2="12.01" y2="18"></line>
                                    </svg>
                                </span>
                                <div class="media-body text-white text-end">
                                    <p class="mb-1">Buy a</p>
                                    <h4 class="text-white mb-0">Number</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            
            <!-- View a Invoice -->
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 mb-3">
                <a href="{{ route('reporting.invoices') }}" class="text-decoration-none">
                    <div class="widget-stat card bg-success" id="tile-view-invoice">
                        <div class="card-body p-4">
                            <div class="media">
                                <span class="me-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                        <polyline points="14 2 14 8 20 8"></polyline>
                                        <line x1="16" y1="13" x2="8" y2="13"></line>
                                        <line x1="16" y1="17" x2="8" y2="17"></line>
                                        <polyline points="10 9 9 9 8 9"></polyline>
                                    </svg>
                                </span>
                                <div class="media-body text-white text-end">
                                    <p class="mb-1">View a</p>
                                    <h4 class="text-white mb-0">Invoice</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            
            <!-- Send a Campaign -->
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 mb-3">
                <a href="{{ route('messages.send') }}" class="text-decoration-none">
                    <div class="widget-stat card bg-info" id="tile-send-campaign">
                        <div class="card-body p-4">
                            <div class="media">
                                <span class="me-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M3 11l18-5v12L3 13v-2z"></path>
                                        <path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"></path>
                                        <line x1="21" y1="3" x2="23" y2="1"></line>
                                        <line x1="21" y1="11" x2="24" y2="11"></line>
                                        <line x1="21" y1="19" x2="23" y2="21"></line>
                                    </svg>
                                </span>
                                <div class="media-body text-white text-end">
                                    <p class="mb-1">Send a</p>
                                    <h4 class="text-white mb-0">Campaign</h4>
                                </div>
                            </div>
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
                            <label class="btn btn-outline-primary btn-sm traffic-toggle-btn" for="trafficToday">Today</label>
                            <input type="radio" class="btn-check" name="trafficPeriod" id="traffic7Days" value="7days">
                            <label class="btn btn-outline-primary btn-sm traffic-toggle-btn" for="traffic7Days">7 Days</label>
                            <input type="radio" class="btn-check" name="trafficPeriod" id="traffic30Days" value="30days">
                            <label class="btn btn-outline-primary btn-sm traffic-toggle-btn" for="traffic30Days">30 Days</label>
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
                <div class="card dashboard-tile h-100" id="tile-test-rcs">
                    <div class="card-header border-0 pb-0">
                        <h5 class="card-title mb-0"><i class="fas fa-mobile-alt me-2" style="color: #886CC0;"></i>Test RCS Message</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">Send a test RCS message to your mobile to see it in action.</p>
                        <div class="mb-3">
                            <label class="form-label small mb-1">Message Type</label>
                            <select class="form-select" id="testRcsMessageType" style="height: 45px;">
                                <option value="basic-text" selected>Basic Text</option>
                                <option value="rich-card">Rich Card</option>
                                <option value="appointment-reminder">Appointment Reminder</option>
                                <option value="carousel">Carousel</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small mb-1">UK Mobile Number</label>
                            <input type="tel" class="form-control" id="testRcsMobile" placeholder="+44 7700 900000" aria-describedby="testRcsFeedback" style="height: 45px;">
                            <div class="invalid-feedback" id="testRcsFeedback">Please enter a valid UK mobile number</div>
                        </div>
                        <div class="mb-3">
                            <button class="btn btn-primary btn-rounded" id="btnSendTestRcs" onclick="sendTestRcs()">Send Test</button>
                        </div>
                        <div class="d-none" id="testRcsResult">
                            <div class="alert alert-success mb-0 py-2" id="testRcsSuccess">
                                <span id="testRcsSuccessMessage">Test message sent successfully!</span>
                            </div>
                            <div class="alert alert-danger mb-0 py-2 d-none" id="testRcsFail">
                                <span id="testRcsFailMessage">Failed to send test message. Please try again.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 mb-3">
                <div class="card dashboard-tile h-100" id="tile-rcs-calculator">
                    <div class="card-header border-0 pb-0">
                        <div class="d-flex align-items-start justify-content-between flex-wrap gap-2">
                            <div>
                                <h5 class="card-title mb-1"><i class="fas fa-calculator me-2" style="color: #886CC0;"></i>RCS vs SMS Savings Calculator</h5>
                                <p class="text-muted small mb-0">See how much you can save by moving suitable SMS traffic to RCS.</p>
                            </div>
                            <span class="calc-mode-badge" id="calcModeIndicator">RCS Basic</span>
                        </div>
                    </div>
                    <div class="card-body pt-3">
                        <div class="row g-4">
                            <div class="col-12 col-lg-7">
                                <p class="calc-section-title"><i class="fas fa-sliders-h me-1"></i>Your inputs</p>
                                <div class="calc-input-list">
                                    <div class="calc-input-row">
                                        <div class="calc-input-meta">
                                            <p class="calc-input-label">Monthly messages</p>
                                            <p class="calc-input-help">Total messages you send per month</p>
                                        </div>
                                        <div class="calc-input-control">
                                            <input type="number" class="form-control" id="calcMonthlyMessages" min="0" step="1000" value="{{ $calculatorData['monthly_messages'] }}">
                                        </div>
                                    </div>
                                    <div class="calc-input-row">
                                        <div class="calc-input-meta">
                                            <p class="calc-input-label">SMS cost per fragment</p>
                                            <p class="calc-input-help">Cost of one SMS fragment</p>
                                        </div>
                                        <div class="calc-input-control">
                                            <div class="calc-readonly">£{{ number_format($pricingData['sms'] ?? 0, 4) }}</div>
                                            <input type="hidden" id="calcSmsPrice" value="{{ $pricingData['sms'] ?? 0 }}">
                                        </div>
                                    </div>
                                    <div class="calc-input-row">
                                        <div class="calc-input-meta">
                                            <p class="calc-input-label">RCS Basic cost</p>
                                            <p class="calc-input-help">Cost per RCS Basic message</p>
                                        </div>
                                        <div class="calc-input-control">
                                            <div class="calc-readonly">£{{ number_format($pricingData['rcs_basic'] ?? 0, 4) }}</div>
                                            <input type="hidden" id="calcRcsBasicPrice" value="{{ $pricingData['rcs_basic'] ?? 0 }}">
                                        </div>
                                    </div>
                                    <div class="calc-input-row">
                                        <div class="calc-input-meta">
                                            <p class="calc-input-label">RCS Single cost</p>
                                            <p class="calc-input-help">Cost per RCS Single message</p>
                                        </div>
                                        <div class="calc-input-control">
                                            <div class="calc-readonly">£{{ number_format($pricingData['rcs_single'] ?? 0, 4) }}</div>
                                            <input type="hidden" id="calcRcsSinglePrice" value="{{ $pricingData['rcs_single'] ?? 0 }}">
                                        </div>
                                    </div>
                                    <div class="calc-input-row calc-slider-row">
                                        <div class="calc-input-meta">
                                            <p class="calc-input-label">Average SMS fragments</p>
                                            <p class="calc-input-help">Average number of fragments per SMS</p>
                                        </div>
                                        <div class="calc-slider-control">
                                            <input type="range" class="form-range calc-slider" id="calcFragments" min="1" max="5" step="0.1" value="{{ $calculatorData['avg_fragments'] }}">
                                            <span class="calc-slider-value" id="calcFragmentsValue">{{ number_format($calculatorData['avg_fragments'], 1) }}</span>
                                        </div>
                                    </div>
                                    <div class="calc-input-row calc-slider-row">
                                        <div class="calc-input-meta">
                                            <p class="calc-input-label">RCS reach %</p>
                                            <p class="calc-input-help">Percentage of users reachable on RCS</p>
                                        </div>
                                        <div class="calc-slider-control">
                                            <input type="range" class="form-range calc-slider" id="calcPenetration" min="0" max="100" step="1" value="65">
                                            <span class="calc-slider-value" id="calcPenetrationValue">65%</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="calc-info-note mt-3">
                                    <i class="fas fa-info-circle me-2 mt-1"></i>
                                    <span>RCS Basic replaces short SMS (up to 160 characters). Longer messages use RCS Single. SMS fallback is used where RCS is unavailable.</span>
                                </div>
                            </div>
                            <div class="col-12 col-lg-5">
                                <div class="calc-hero-panel">
                                    <p class="calc-hero-eyebrow">Estimated monthly saving</p>
                                    <h2 class="calc-hero-figure" id="calcHeroSaving">£0</h2>
                                    <p class="calc-hero-percent" id="calcHeroPercent">0% cheaper than SMS</p>
                                    <div class="calc-hero-pill-wrap">
                                        <span class="calc-hero-pill"><i class="fas fa-users me-2"></i><span id="calcReachBadge">Based on 65% RCS reach</span></span>
                                    </div>
                                    <div class="calc-hero-divider"></div>
                                    <div class="d-flex justify-content-between align-items-center calc-hero-line">
                                        <span>Current SMS cost</span>
                                        <strong id="calcCurrentSmsCost">£0</strong>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center calc-hero-line">
                                        <span>Blended RCS cost</span>
                                        <strong id="calcBlendedCostMonthly">£0</strong>
                                    </div>
                                    <div class="calc-save-card">
                                        <div class="calc-save-icon"><i class="fas fa-chart-line"></i></div>
                                        <div class="flex-grow-1 d-flex justify-content-between align-items-center flex-wrap gap-2">
                                            <p class="calc-save-label mb-0">You save</p>
                                            <p class="calc-save-value mb-0"><span id="calcSaveAmount">£0</span><small> / month</small></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row g-3 mt-1 calc-metric-row">
                            <div class="col-6 col-md-3">
                                <div class="calc-metric-card calc-metric-purple">
                                    <div class="calc-metric-icon"><i class="fas fa-comment"></i></div>
                                    <div class="flex-grow-1 min-w-0">
                                        <p class="calc-metric-label">Current SMS Cost</p>
                                        <p class="calc-metric-value" id="calcMetricSmsCost">£0</p>
                                        <p class="calc-metric-sub">per month</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="calc-metric-card calc-metric-green">
                                    <div class="calc-metric-icon"><i class="fas fa-layer-group"></i></div>
                                    <div class="flex-grow-1 min-w-0">
                                        <p class="calc-metric-label">Blended RCS Cost</p>
                                        <p class="calc-metric-value" id="calcMetricBlendedCost">£0</p>
                                        <p class="calc-metric-sub">per month</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="calc-metric-card calc-metric-amber">
                                    <div class="calc-metric-icon"><i class="fas fa-percent"></i></div>
                                    <div class="flex-grow-1 min-w-0">
                                        <p class="calc-metric-label">Saving</p>
                                        <p class="calc-metric-value" id="calcMetricSavingPct">0%</p>
                                        <p class="calc-metric-sub">cheaper</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="calc-metric-card calc-metric-blue">
                                    <div class="calc-metric-icon"><i class="fas fa-wallet"></i></div>
                                    <div class="flex-grow-1 min-w-0">
                                        <p class="calc-metric-label">Annual Saving</p>
                                        <p class="calc-metric-value" id="calcMetricAnnualSaving">£0</p>
                                        <p class="calc-metric-sub">per year</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="calc-help-strip mt-4">
                            <div class="calc-help-icon"><i class="fas fa-rocket"></i></div>
                            <div class="flex-grow-1">
                                <p class="calc-help-title">Maximise your savings</p>
                                <p class="calc-help-text">Increase RCS reach by encouraging users to update their devices and enable RCS in messaging settings.</p>
                            </div>
                            <a href="https://www.quicksms.com/rcs-for-business" target="_blank" rel="noopener" class="btn calc-help-btn">Learn how to improve reach <i class="fas fa-arrow-right ms-1"></i></a>
                        </div>
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
            <div class="col-xl-3 col-lg-4 col-md-6 mb-3">
                <a href="{{ route('support.dashboard') }}" class="text-decoration-none">
                    <div class="card dashboard-tile h-100" id="tile-support-tickets">
                        <div class="card-body d-flex flex-column align-items-center justify-content-center text-center py-4">
                            <div class="icon-box bg-danger rounded-circle mb-3" style="width: 56px; height: 56px;">
                                <i class="fas fa-ticket-alt text-white fa-lg"></i>
                            </div>
                            <h2 class="mb-1 text-dark" id="support-tickets-count">3</h2>
                            <span class="text-muted small">Open Support Tickets</span>
                        </div>
                    </div>
                </a>
            </div>
            
            <div class="col-xl-3 col-lg-4 col-md-6 mb-3">
                <div class="card dashboard-tile h-100" id="tile-knowledge-base">
                    <div class="card-body d-flex flex-column align-items-center justify-content-center text-center py-4">
                        <div class="icon-box bg-info rounded-circle mb-3" style="width: 56px; height: 56px;">
                            <i class="fas fa-book text-white fa-lg"></i>
                        </div>
                        <h5 class="mb-2 text-dark">Knowledge Base</h5>
                        <p class="text-muted small mb-3">Find answers to common questions</p>
                        <a href="#" target="_blank" class="btn btn-info btn-sm" id="btnOpenKnowledgeBase">
                            <i class="fas fa-external-link-alt me-1"></i>Open Knowledge Base
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-6 col-lg-8 mb-3">
                <div class="card dashboard-tile h-100" id="tile-notifications">
                    <div class="card-header border-0 pb-0 d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0"><i class="fas fa-bullhorn me-2 text-warning"></i>Announcements</h5>
                        <span class="badge bg-warning text-dark">1 New</span>
                    </div>
                    <div class="card-body">
                        <div class="notification-item p-3 rounded mb-2" style="background: linear-gradient(135deg, #fff9e6 0%, #fff3cd 100%); border-left: 3px solid #ffc107;" id="notification-1">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="mb-0 fw-semibold">Platform Maintenance Scheduled</h6>
                                <button class="btn btn-sm btn-link text-muted p-0" onclick="dismissNotification('notification-1')" title="Dismiss">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <p class="mb-2 small text-muted">We will be performing scheduled maintenance on Saturday, January 4th from 02:00 - 04:00 GMT. Some services may be temporarily unavailable.</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted small" id="notification-1-timestamp">
                                    <i class="fas fa-clock me-1"></i>Posted: Dec 28, 2025 at 14:30
                                </span>
                                <a href="#" class="small text-primary">Read more</a>
                            </div>
                        </div>
                        
                        <div class="notification-item p-3 rounded" style="background: #f8f9fa; border-left: 3px solid #6c757d;" id="notification-2">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="mb-0 fw-semibold text-muted">New RCS Features Available</h6>
                                <button class="btn btn-sm btn-link text-muted p-0" onclick="dismissNotification('notification-2')" title="Dismiss">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <p class="mb-2 small text-muted">Check out our new RCS carousel templates and rich card builder in the Templates section.</p>
                            <span class="text-muted small" id="notification-2-timestamp">
                                <i class="fas fa-clock me-1"></i>Posted: Dec 20, 2025 at 09:15
                            </span>
                        </div>
                        
                        <div class="text-center mt-3 d-none" id="no-notifications">
                            <p class="text-muted small mb-0"><i class="fas fa-check-circle me-1"></i>No new announcements</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

@push('styles')
<style>
/* ========================================
   DASHBOARD LAYOUT & SPACING
   ======================================== */

/* Consistent section spacing */
#operationalOverview,
#rcsPromotion,
#supportNotifications {
    margin-bottom: 1.5rem;
}

/* Section headers consistent spacing */
#operationalOverview > .d-flex,
#rcsPromotion > .d-flex,
#supportNotifications > .d-flex {
    margin-bottom: 1rem !important;
}

/* Ensure rows have consistent gutters */
#operationalOverview .row,
#rcsPromotion .row,
#supportNotifications .row {
    --bs-gutter-x: 1rem;
    --bs-gutter-y: 1rem;
}

/* ========================================
   ICON BOXES
   ======================================== */
.icon-box {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.icon-box-sm {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

/* Light background variants */
.bg-primary-light { background-color: rgba(136, 108, 192, 0.1); }
.bg-success-light { background-color: rgba(40, 167, 69, 0.1); }
.bg-info-light { background-color: rgba(23, 162, 184, 0.1); }
.bg-warning-light { background-color: rgba(255, 193, 7, 0.1); }
.bg-danger-light { background-color: rgba(220, 53, 69, 0.1); }

/* ========================================
   OPERATIONAL OVERVIEW STAT CARDS
   ======================================== */
#tile-balance,
#tile-inbound,
#tile-messages-today,
#tile-delivery-rate {
    background-color: #fff !important;
    border: none !important;
}

#tile-balance .media-body h4,
#tile-inbound .media-body h4,
#tile-messages-today .media-body h4,
#tile-delivery-rate .media-body h4 {
    color: #1a1a1a !important;
}

/* ========================================
   DASHBOARD TILES
   ======================================== */
.dashboard-tile {
    transition: all 0.2s ease;
    cursor: pointer;
    border: 1px solid rgba(0,0,0,0.08);
    height: 100%;
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

/* Tile values - centered alignment */
.tile-value {
    font-size: 1.5rem;
    font-weight: 600;
    color: #333;
    text-align: left;
}

/* Metric tiles value alignment */
#tile-balance .tile-value,
#tile-inbound .tile-value,
#tile-messages-today .tile-value,
#tile-delivery-rate .tile-value {
    text-align: left;
}

/* Support ticket count centered */
#tile-support-tickets h2 {
    text-align: center;
}


/* ========================================
   SKELETON LOADING ANIMATION
   ======================================== */
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

/* ========================================
   DELIVERY RATE COLOR CODING
   ======================================== */
.delivery-rate-green { color: #28a745 !important; }
.delivery-rate-amber { color: #ffc107 !important; }
.delivery-rate-red { color: #dc3545 !important; }

/* ========================================
   CARD HOVER EFFECTS
   ======================================== */
#operationalOverview .card,
#rcsPromotion .card,
#supportNotifications .card {
    transition: box-shadow 0.2s ease, transform 0.2s ease;
}
#rcsPromotion .card:hover,
#supportNotifications .card:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
}

/* ========================================
   CTA BUTTONS - PLATFORM CONSISTENT STYLE
   ======================================== */
#rcsPromotion .btn,
#supportNotifications .btn,
#tile-test-rcs .btn {
    font-weight: 500;
    border-radius: 0.375rem;
    padding: 0.5rem 1rem;
    transition: all 0.2s ease;
}

/* Primary CTA */
#tile-rcs-advertisement .btn-primary {
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}
#tile-rcs-advertisement .btn-primary:hover {
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    transform: translateY(-1px);
}

/* ========================================
   TOOLTIPS Z-INDEX FIX
   ======================================== */
.tooltip {
    z-index: 1080 !important;
}
.tooltip-inner {
    max-width: 250px;
}

/* ApexCharts tooltip fix */
.apexcharts-tooltip {
    z-index: 1070 !important;
}

/* ========================================
   RESPONSIVE HANDLING
   ======================================== */

/* Prevent horizontal scroll */
.container-fluid {
    overflow-x: hidden;
}

/* Tablet breakpoint (< 992px) */
@media (max-width: 991.98px) {
    /* RCS Advertisement tile stacks vertically */
    #tile-rcs-advertisement .row {
        flex-direction: column;
    }
    #tile-rcs-advertisement .col-md-5,
    #tile-rcs-advertisement .col-md-7 {
        width: 100%;
    }
    #tile-rcs-advertisement .rcs-ad-image {
        border-radius: 0.625rem 0.625rem 0 0 !important;
        min-height: 150px !important;
    }

}

/* Mobile breakpoint (< 576px) */
@media (max-width: 575.98px) {
    /* Section headers smaller */
    #operationalOverview h4,
    #rcsPromotion h4,
    #supportNotifications h4 {
        font-size: 1.1rem;
    }
    
    /* Metric tiles full width */
    #operationalOverview .col-sm-6 {
        flex: 0 0 50%;
        max-width: 50%;
    }
    
    /* Tile values smaller on mobile */
    .tile-value {
        font-size: 1.25rem;
    }
    
    /* Calculator result figures smaller */
    #tile-rcs-calculator h4 {
        font-size: 1.1rem;
    }
    #tile-rcs-calculator .text-uppercase {
        font-size: 0.65rem;
    }
    
    /* Test RCS input stacks */
    #tile-test-rcs .row.g-2 {
        flex-direction: column;
    }
    #tile-test-rcs .col-auto {
        width: 100%;
        margin-top: 0.5rem;
    }
    #tile-test-rcs .btn {
        width: 100%;
    }
    
    /* Notifications tile adjustments */
    .notification-item {
        padding: 0.75rem !important;
    }
    .notification-item h6 {
        font-size: 0.9rem;
    }
    
    /* Support tiles stack nicely */
    #supportNotifications .col-xl-3,
    #supportNotifications .col-lg-4 {
        flex: 0 0 100%;
        max-width: 100%;
    }
}

/* Extra small devices (<= 400px) */
@media (max-width: 400px) {
    /* Metric tiles single column */
    #operationalOverview .col-sm-6 {
        flex: 0 0 100%;
        max-width: 100%;
    }
    
    /* Traffic graph period buttons wrap */
    #trafficToggle {
        flex-wrap: wrap;
    }
    #trafficToggle .btn {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }
}

/* Equal-width traffic toggle buttons */
.traffic-toggle-btn {
    min-width: 70px;
    text-align: center;
}

/* ========================================
   FORM INPUT CONSISTENCY
   ======================================== */
#tile-test-rcs .form-control {
    border-radius: 0.375rem;
}

/* ========================================
   RCS SAVINGS CALCULATOR (premium)
   ======================================== */
#tile-rcs-calculator .calc-mode-badge {
    background: rgba(136, 108, 192, 0.08);
    color: #6a4fa0;
    font-size: 0.78rem;
    font-weight: 600;
    padding: 0.45rem 0.95rem;
    border-radius: 999px;
    border: 1px solid rgba(136, 108, 192, 0.2);
    white-space: nowrap;
}
#tile-rcs-calculator .calc-section-title {
    color: #6a4fa0;
    font-weight: 700;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.6px;
    margin-bottom: 0.85rem;
}
#tile-rcs-calculator .calc-input-list {
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
}
#tile-rcs-calculator .calc-input-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    background: #fbfafd;
    border: 1px solid #ece7f5;
    border-radius: 0.7rem;
    padding: 0.85rem 1.05rem;
}
#tile-rcs-calculator .calc-input-meta {
    flex: 1 1 auto;
    min-width: 0;
}
#tile-rcs-calculator .calc-input-label {
    font-weight: 600;
    margin: 0;
    color: #2c2640;
    font-size: 0.9rem;
}
#tile-rcs-calculator .calc-input-help {
    color: #8a83a0;
    font-size: 0.78rem;
    margin: 0.15rem 0 0;
    line-height: 1.3;
}
#tile-rcs-calculator .calc-input-control {
    width: 130px;
    flex-shrink: 0;
}
#tile-rcs-calculator .calc-input-control .form-control,
#tile-rcs-calculator .calc-input-control .calc-readonly {
    border: 1px solid #e3dcef;
    background: #fff;
    border-radius: 0.5rem;
    text-align: right;
    font-weight: 600;
    color: #2c2640;
    font-size: 0.9rem;
}
#tile-rcs-calculator .calc-input-control .calc-readonly {
    padding: 0.45rem 0.75rem;
    line-height: 1.4;
}
#tile-rcs-calculator .calc-input-control .form-control:focus {
    border-color: #886CC0;
    box-shadow: 0 0 0 0.15rem rgba(136, 108, 192, 0.2);
}
#tile-rcs-calculator .calc-slider-row .calc-slider-control {
    width: 240px;
    display: flex;
    align-items: center;
    gap: 0.85rem;
    flex-shrink: 0;
}
#tile-rcs-calculator .calc-slider {
    flex: 1;
    accent-color: #886CC0;
    height: 0.4rem;
}
#tile-rcs-calculator .calc-slider-value {
    font-weight: 700;
    color: #886CC0;
    min-width: 48px;
    text-align: right;
    font-size: 0.95rem;
}
#tile-rcs-calculator .calc-info-note {
    display: flex;
    align-items: flex-start;
    background: rgba(136, 108, 192, 0.06);
    border: 1px solid rgba(136, 108, 192, 0.15);
    border-radius: 0.7rem;
    padding: 0.85rem 1rem;
    color: #6a4fa0;
    font-size: 0.82rem;
    line-height: 1.45;
}
#tile-rcs-calculator .calc-hero-panel {
    background: linear-gradient(160deg, #f6f2fc 0%, #efe9fa 100%);
    border-radius: 1rem;
    padding: 1.75rem 1.5rem;
    height: 100%;
    border: 1px solid rgba(136, 108, 192, 0.12);
    display: flex;
    flex-direction: column;
}
#tile-rcs-calculator .calc-hero-eyebrow {
    color: #6a4fa0;
    font-weight: 600;
    text-align: center;
    margin: 0 0 0.5rem;
    font-size: 0.95rem;
}
#tile-rcs-calculator .calc-hero-figure {
    color: #6a4fa0;
    font-weight: 800;
    font-size: 3.25rem;
    text-align: center;
    margin: 0 0 0.25rem;
    line-height: 1.05;
    letter-spacing: -0.02em;
}
#tile-rcs-calculator .calc-hero-percent {
    color: #2bb673;
    font-weight: 600;
    text-align: center;
    margin: 0 0 0.85rem;
    font-size: 0.95rem;
}
#tile-rcs-calculator .calc-hero-pill-wrap {
    text-align: center;
    margin-bottom: 1.25rem;
}
#tile-rcs-calculator .calc-hero-pill {
    display: inline-flex;
    align-items: center;
    background: rgba(255, 255, 255, 0.85);
    color: #6a4fa0;
    font-weight: 500;
    font-size: 0.8rem;
    padding: 0.5rem 1rem;
    border-radius: 999px;
    border: 1px solid rgba(136, 108, 192, 0.2);
}
#tile-rcs-calculator .calc-hero-divider {
    border-top: 1px solid rgba(136, 108, 192, 0.18);
    margin: 0.25rem 0 0.5rem;
}
#tile-rcs-calculator .calc-hero-line {
    color: #2c2640;
    padding: 0.4rem 0;
    font-size: 0.95rem;
}
#tile-rcs-calculator .calc-hero-line strong {
    color: #2c2640;
    font-weight: 700;
}
#tile-rcs-calculator .calc-save-card {
    margin-top: auto;
    background: #fff;
    border-radius: 0.75rem;
    padding: 1rem 1.1rem;
    display: flex;
    align-items: center;
    gap: 0.85rem;
    box-shadow: 0 4px 14px rgba(136, 108, 192, 0.08);
}
#tile-rcs-calculator .calc-save-icon {
    width: 42px;
    height: 42px;
    border-radius: 999px;
    background: rgba(43, 182, 115, 0.12);
    color: #2bb673;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 1rem;
}
#tile-rcs-calculator .calc-save-label {
    color: #6a6580;
    font-size: 0.9rem;
    font-weight: 500;
}
#tile-rcs-calculator .calc-save-value {
    color: #2bb673;
    font-weight: 800;
    font-size: 1.5rem;
    line-height: 1;
    white-space: nowrap;
}
#tile-rcs-calculator .calc-save-value small {
    color: #8a83a0;
    font-weight: 500;
    font-size: 0.8rem;
}

/* 4 metric cards */
#tile-rcs-calculator .calc-metric-row { margin-top: 0.5rem; }
#tile-rcs-calculator .calc-metric-card {
    border-radius: 0.875rem;
    padding: 1rem 1.1rem;
    display: flex;
    align-items: center;
    gap: 0.85rem;
    height: 100%;
}
#tile-rcs-calculator .calc-metric-icon {
    width: 48px;
    height: 48px;
    border-radius: 999px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.15rem;
    flex-shrink: 0;
}
#tile-rcs-calculator .calc-metric-label {
    margin: 0;
    font-size: 0.78rem;
    font-weight: 600;
    opacity: 0.95;
}
#tile-rcs-calculator .calc-metric-value {
    margin: 0.1rem 0 0;
    font-size: 1.35rem;
    font-weight: 800;
    line-height: 1.1;
    letter-spacing: -0.01em;
    overflow-wrap: break-word;
}
#tile-rcs-calculator .calc-metric-sub {
    margin: 0.15rem 0 0;
    font-size: 0.72rem;
    opacity: 0.7;
}
#tile-rcs-calculator .calc-metric-purple { background: #f1ecfa; }
#tile-rcs-calculator .calc-metric-purple .calc-metric-icon { background: rgba(136, 108, 192, 0.18); color: #6a4fa0; }
#tile-rcs-calculator .calc-metric-purple .calc-metric-label,
#tile-rcs-calculator .calc-metric-purple .calc-metric-value { color: #6a4fa0; }
#tile-rcs-calculator .calc-metric-purple .calc-metric-sub { color: #8a7ab0; }
#tile-rcs-calculator .calc-metric-green { background: #e8f7ee; }
#tile-rcs-calculator .calc-metric-green .calc-metric-icon { background: rgba(43, 182, 115, 0.18); color: #2bb673; }
#tile-rcs-calculator .calc-metric-green .calc-metric-label,
#tile-rcs-calculator .calc-metric-green .calc-metric-value { color: #1e8b56; }
#tile-rcs-calculator .calc-metric-green .calc-metric-sub { color: #66a585; }
#tile-rcs-calculator .calc-metric-amber { background: #fdf3e2; }
#tile-rcs-calculator .calc-metric-amber .calc-metric-icon { background: rgba(232, 158, 28, 0.2); color: #c98214; }
#tile-rcs-calculator .calc-metric-amber .calc-metric-label,
#tile-rcs-calculator .calc-metric-amber .calc-metric-value { color: #b87510; }
#tile-rcs-calculator .calc-metric-amber .calc-metric-sub { color: #b69457; }
#tile-rcs-calculator .calc-metric-blue { background: #eaf1fb; }
#tile-rcs-calculator .calc-metric-blue .calc-metric-icon { background: rgba(81, 124, 196, 0.18); color: #4174c2; }
#tile-rcs-calculator .calc-metric-blue .calc-metric-label,
#tile-rcs-calculator .calc-metric-blue .calc-metric-value { color: #355d9b; }
#tile-rcs-calculator .calc-metric-blue .calc-metric-sub { color: #7a91b5; }

/* Help strip */
#tile-rcs-calculator .calc-help-strip {
    background: #fbfafd;
    border: 1px solid #ece7f5;
    border-radius: 0.875rem;
    padding: 1.1rem 1.25rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}
#tile-rcs-calculator .calc-help-icon {
    width: 44px;
    height: 44px;
    border-radius: 999px;
    background: rgba(136, 108, 192, 0.14);
    color: #6a4fa0;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 1.1rem;
}
#tile-rcs-calculator .calc-help-title {
    margin: 0;
    font-weight: 700;
    color: #2c2640;
    font-size: 0.95rem;
}
#tile-rcs-calculator .calc-help-text {
    margin: 0.15rem 0 0;
    color: #6a6580;
    font-size: 0.85rem;
    line-height: 1.4;
}
#tile-rcs-calculator .calc-help-btn {
    flex-shrink: 0;
    border: 1px solid #886CC0;
    color: #6a4fa0;
    background: #fff;
    border-radius: 0.6rem;
    font-weight: 600;
    padding: 0.55rem 1rem;
    font-size: 0.85rem;
    transition: background 0.15s ease, color 0.15s ease;
}
#tile-rcs-calculator .calc-help-btn:hover,
#tile-rcs-calculator .calc-help-btn:focus {
    background: #886CC0;
    color: #fff;
    border-color: #886CC0;
}

/* Calculator responsive: stack input rows on narrow screens */
@media (max-width: 575.98px) {
    #tile-rcs-calculator .calc-input-row {
        flex-direction: column;
        align-items: stretch;
        gap: 0.65rem;
    }
    #tile-rcs-calculator .calc-input-control,
    #tile-rcs-calculator .calc-slider-row .calc-slider-control {
        width: 100%;
    }
    #tile-rcs-calculator .calc-input-control .form-control,
    #tile-rcs-calculator .calc-input-control .calc-readonly {
        text-align: left;
    }
    #tile-rcs-calculator .calc-hero-figure { font-size: 2.5rem; }
    #tile-rcs-calculator .calc-help-btn { width: 100%; text-align: center; }
}

/* Valid/invalid states */
#tile-test-rcs .form-control.is-valid {
    border-color: #28a745;
    background-image: none;
}
#tile-test-rcs .form-control.is-invalid {
    border-color: #dc3545;
}

/* ========================================
   ALERT MESSAGES
   ======================================== */
#testRcsResult .alert {
    border-radius: 0.375rem;
    font-size: 0.875rem;
}
</style>
@endpush

@push('scripts')
<script src="/vendor/apexchart/apexchart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // ========================================
    // MOCK API SERVICE - Simulates backend calls
    // ========================================
    var MockAPI = {
        // Simulate network delay (300-800ms)
        delay: function() {
            return Math.floor(Math.random() * 500) + 300;
        },
        
        // Simulate random failures (2% chance - set higher to test error states)
        shouldFail: function() {
            return Math.random() < 0.02;
        },
        
        // GET /api/dashboard/balance
        getBalance: function() {
            return new Promise(function(resolve, reject) {
                setTimeout(function() {
                    if (MockAPI.shouldFail()) {
                        reject(new Error('Failed to fetch balance'));
                    } else {
                        resolve({
                            balance: 1247.85,
                            currency: 'GBP',
                            lastUpdated: new Date().toISOString()
                        });
                    }
                }, MockAPI.delay());
            });
        },
        
        // GET /api/dashboard/inbound-unresponded
        getInboundUnresponded: function() {
            return new Promise(function(resolve, reject) {
                setTimeout(function() {
                    if (MockAPI.shouldFail()) {
                        reject(new Error('Failed to fetch inbound count'));
                    } else {
                        resolve({
                            count: 12,
                            urgent: 3,
                            lastUpdated: new Date().toISOString()
                        });
                    }
                }, MockAPI.delay());
            });
        },
        
        // GET /api/dashboard/messages-today
        getMessagesToday: function() {
            return new Promise(function(resolve, reject) {
                setTimeout(function() {
                    if (MockAPI.shouldFail()) {
                        reject(new Error('Failed to fetch messages count'));
                    } else {
                        resolve({
                            sent: 1856,
                            delivered: 1798,
                            failed: 58,
                            lastUpdated: new Date().toISOString()
                        });
                    }
                }, MockAPI.delay());
            });
        },
        
        // GET /api/dashboard/delivery-rate
        getDeliveryRate: function() {
            return new Promise(function(resolve, reject) {
                setTimeout(function() {
                    if (MockAPI.shouldFail()) {
                        reject(new Error('Failed to fetch delivery rate'));
                    } else {
                        resolve({
                            rate: 96.8,
                            trend: 'up',
                            change: 1.2,
                            lastUpdated: new Date().toISOString()
                        });
                    }
                }, MockAPI.delay());
            });
        },
        
        // GET /api/dashboard/traffic?period=today|7days|30days
        getTrafficData: function(period) {
            return new Promise(function(resolve, reject) {
                setTimeout(function() {
                    if (MockAPI.shouldFail()) {
                        reject(new Error('Failed to fetch traffic data'));
                    } else {
                        var categories = [];
                        var data = [];
                        
                        var today = new Date();
                        var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                        
                        if (period === 'today') {
                            categories = ['00:00', '03:00', '06:00', '09:00', '12:00', '15:00', '18:00', '21:00'];
                            data = [12, 8, 25, 189, 356, 284, 198, 95];
                        } else if (period === '7days') {
                            for (var i = 6; i >= 0; i--) {
                                var d = new Date(today);
                                d.setDate(today.getDate() - i);
                                categories.push(d.getDate() + ' ' + months[d.getMonth()]);
                                data.push([1842, 2156, 1989, 2312, 2478, 1234, 989][6 - i]);
                            }
                        } else if (period === '30days') {
                            for (var i = 29; i >= 0; i--) {
                                var d = new Date(today);
                                d.setDate(today.getDate() - i);
                                categories.push(d.getDate() + ' ' + months[d.getMonth()]);
                                data.push(Math.floor(Math.random() * 2000) + 800);
                            }
                        }
                        
                        resolve({
                            categories: categories,
                            data: data,
                            period: period,
                            total: data.reduce(function(a, b) { return a + b; }, 0)
                        });
                    }
                }, MockAPI.delay());
            });
        },
        
        // GET /api/dashboard/support-tickets
        getSupportTickets: function() {
            return new Promise(function(resolve, reject) {
                setTimeout(function() {
                    if (MockAPI.shouldFail()) {
                        reject(new Error('Failed to fetch support tickets'));
                    } else {
                        resolve({
                            open: 3,
                            pending: 1,
                            closed: 47,
                            lastUpdated: new Date().toISOString()
                        });
                    }
                }, MockAPI.delay());
            });
        },
        
        getCalculatorDefaults: function() {
            return Promise.resolve({});
        }
    };
    
    // ========================================
    // TILE UTILITY FUNCTIONS
    // ========================================
    
    function updateDeliveryRateColor() {
        var rateEl = document.getElementById('delivery-rate-value');
        if (!rateEl) return;
        
        rateEl.classList.remove('delivery-rate-green', 'delivery-rate-amber', 'delivery-rate-red');
        rateEl.style.color = '#000';
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
    
    // ========================================
    // TILE DATA LOADERS
    // ========================================
    
    function loadBalance() {
        setTileLoading('tile-balance', true);
        var balanceData = @json($balanceData ?? ['effectiveAvailable' => 0, 'currency' => 'GBP']);
        var valueEl = document.getElementById('balance-value');
        if (valueEl) {
            valueEl.textContent = '£' + balanceData.balance.toLocaleString('en-GB', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
        setTileLoading('tile-balance', false);
    }
    
    function loadInboundUnresponded() {
        setTileLoading('tile-inbound', true);
        MockAPI.getInboundUnresponded()
            .then(function(data) {
                var valueEl = document.getElementById('inbound-value');
                if (valueEl && data.count !== undefined) {
                    valueEl.textContent = data.count.toLocaleString();
                }
                setTileLoading('tile-inbound', false);
            })
            .catch(function(err) {
                console.error('Inbound error:', err);
                setTileError('tile-inbound', true);
            });
    }
    
    function loadMessagesToday() {
        setTileLoading('tile-messages-today', true);
        MockAPI.getMessagesToday()
            .then(function(data) {
                var valueEl = document.getElementById('messages-today-value');
                if (valueEl && data.sent !== undefined) {
                    valueEl.textContent = data.sent.toLocaleString();
                }
                setTileLoading('tile-messages-today', false);
            })
            .catch(function(err) {
                console.error('Messages today error:', err);
                setTileError('tile-messages-today', true);
            });
    }
    
    function loadDeliveryRate() {
        setTileLoading('tile-delivery-rate', true);
        MockAPI.getDeliveryRate()
            .then(function(data) {
                var valueEl = document.getElementById('delivery-rate-value');
                if (valueEl && data.rate !== undefined) {
                    valueEl.textContent = data.rate.toFixed(1) + '%';
                    valueEl.dataset.rate = data.rate;
                    updateDeliveryRateColor();
                }
                setTileLoading('tile-delivery-rate', false);
            })
            .catch(function(err) {
                console.error('Delivery rate error:', err);
                setTileError('tile-delivery-rate', true);
            });
    }
    
    function loadSupportTickets() {
        var ticketCountEl = document.getElementById('support-tickets-count');
        if (!ticketCountEl) return;
        
        // Show loading state inline
        ticketCountEl.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        
        MockAPI.getSupportTickets()
            .then(function(data) {
                if (data.open !== undefined) {
                    ticketCountEl.textContent = data.open;
                }
            })
            .catch(function(err) {
                console.error('Support tickets error:', err);
                ticketCountEl.innerHTML = '<i class="fas fa-exclamation-triangle text-danger"></i>';
            });
    }
    
    function loadCalculatorDefaults() {
        calculateSavings();
    }
    
    // ========================================
    // INITIALIZE ALL TILES
    // ========================================
    
    loadBalance();
    loadInboundUnresponded();
    loadMessagesToday();
    loadDeliveryRate();
    loadSupportTickets();
    loadCalculatorDefaults();
    
    window.setTileLoading = setTileLoading;
    window.setTileError = setTileError;
    window.updateDeliveryRateColor = updateDeliveryRateColor;
    window.MockAPI = MockAPI;
    
    // Traffic Graph
    var trafficChart = null;
    
    function renderTrafficChart(chartData) {
        var options = {
            series: [{
                name: 'Total Messages',
                data: chartData.data
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
                categories: chartData.categories,
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
    
    function loadTrafficData(period) {
        setTileLoading('tile-traffic-graph', true);
        MockAPI.getTrafficData(period)
            .then(function(data) {
                renderTrafficChart(data);
                setTileLoading('tile-traffic-graph', false);
            })
            .catch(function(err) {
                console.error('Traffic data error:', err);
                setTileError('tile-traffic-graph', true);
            });
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

/**
 * Validates UK mobile number format
 * Accepts: +44 7xxx, 07xxx, 447xxx formats with various spacing/formatting
 * @param {string} mobile - The mobile number to validate
 * @returns {object} - { valid: boolean, normalized: string, error: string }
 */
function validateUKMobile(mobile) {
    if (!mobile || typeof mobile !== 'string') {
        return { valid: false, normalized: null, error: 'Mobile number is required' };
    }
    
    // Remove all spaces, dashes, parentheses
    var cleaned = mobile.replace(/[\s\-\(\)\.]/g, '');
    
    // UK mobile patterns:
    // +447xxxxxxxxx (13 chars)
    // 447xxxxxxxxx (12 chars)
    // 07xxxxxxxxx (11 chars)
    
    var patterns = [
        /^\+44(7\d{9})$/,      // +447xxxxxxxxx
        /^44(7\d{9})$/,        // 447xxxxxxxxx
        /^0(7\d{9})$/          // 07xxxxxxxxx
    ];
    
    for (var i = 0; i < patterns.length; i++) {
        var match = cleaned.match(patterns[i]);
        if (match) {
            // Normalize to +44 format
            return { 
                valid: true, 
                normalized: '+44' + match[1],
                error: null 
            };
        }
    }
    
    return { 
        valid: false, 
        normalized: null, 
        error: 'Please enter a valid UK mobile number (e.g., +44 7700 900000 or 07700 900000)' 
    };
}

function sendTestRcs() {
    var mobileInput = document.getElementById('testRcsMobile');
    var feedbackEl = document.getElementById('testRcsFeedback');
    var resultDiv = document.getElementById('testRcsResult');
    var successDiv = document.getElementById('testRcsSuccess');
    var failDiv = document.getElementById('testRcsFail');
    var successMsgEl = document.getElementById('testRcsSuccessMessage');
    var failMsgEl = document.getElementById('testRcsFailMessage');
    var btn = document.getElementById('btnSendTestRcs');
    
    var mobile = mobileInput.value.trim();
    
    // Hide previous results
    resultDiv.classList.add('d-none');
    successDiv.classList.add('d-none');
    failDiv.classList.add('d-none');
    
    // Validate UK mobile format
    var validation = validateUKMobile(mobile);
    
    if (!validation.valid) {
        mobileInput.classList.add('is-invalid');
        if (feedbackEl) {
            feedbackEl.textContent = validation.error;
        }
        return;
    }
    
    // Clear validation state
    mobileInput.classList.remove('is-invalid');
    mobileInput.classList.add('is-valid');
    
    // Show sending state
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Sending...';
    
    // Prepare payload for API
    var payload = {
        mobile: validation.normalized,  // Normalized UK format: +447xxxxxxxxx
        messageType: 'rcs_test',
        timestamp: new Date().toISOString(),
        // TODO: Add user/session context when available
        // userId: getCurrentUserId(),
        // sessionId: getSessionId()
    };
    
    console.log('[Test RCS] Sending test message:', payload);
    
    // TODO: Replace setTimeout with actual API call
    // Example API integration:
    // fetch('/api/rcs/send-test', {
    //     method: 'POST',
    //     headers: {
    //         'Content-Type': 'application/json',
    //         'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    //     },
    //     body: JSON.stringify(payload)
    // })
    // .then(response => response.json())
    // .then(data => handleTestRcsResponse(data))
    // .catch(error => handleTestRcsError(error));
    
    // Simulated API call (remove when backend is ready)
    setTimeout(function() {
        // Simulate API response
        var mockResponse = {
            success: Math.random() > 0.2,  // 80% success rate for demo
            messageId: 'msg_' + Date.now(),
            mobile: validation.normalized,
            error: null
        };
        
        if (!mockResponse.success) {
            mockResponse.error = 'RCS not available for this number. SMS fallback not enabled.';
        }
        
        handleTestRcsResponse(mockResponse);
    }, 1500);
}

function handleTestRcsResponse(response) {
    var resultDiv = document.getElementById('testRcsResult');
    var successDiv = document.getElementById('testRcsSuccess');
    var failDiv = document.getElementById('testRcsFail');
    var successMsgEl = document.getElementById('testRcsSuccessMessage');
    var failMsgEl = document.getElementById('testRcsFailMessage');
    var btn = document.getElementById('btnSendTestRcs');
    var mobileInput = document.getElementById('testRcsMobile');
    
    resultDiv.classList.remove('d-none');
    
    if (response.success) {
        // Success state
        successDiv.classList.remove('d-none');
        failDiv.classList.add('d-none');
        successMsgEl.textContent = 'Test message sent to ' + response.mobile + '. Check your phone!';
        mobileInput.classList.remove('is-valid');
        console.log('[Test RCS] Success:', response);
    } else {
        // Failure state
        successDiv.classList.add('d-none');
        failDiv.classList.remove('d-none');
        failMsgEl.textContent = response.error || 'Failed to send test message. Please try again.';
        mobileInput.classList.remove('is-valid');
        console.error('[Test RCS] Failed:', response);
    }
    
    // Reset button
    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-paper-plane me-1"></i>Send Test';
}

// TODO: Add error handler for network/API errors
function handleTestRcsError(error) {
    console.error('[Test RCS] Network error:', error);
    handleTestRcsResponse({
        success: false,
        error: 'Network error. Please check your connection and try again.'
    });
}

function calcFmtMoney(value, decimals) {
    decimals = decimals || 0;
    var n = isFinite(value) ? value : 0;
    return '£' + n.toLocaleString('en-GB', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals,
    });
}

function calcSetText(id, val) {
    var el = document.getElementById(id);
    if (el) el.textContent = val;
}

function calculateSavings() {
    var smsPrice = parseFloat(document.getElementById('calcSmsPrice').value) || 0;
    var rcsBasicPrice = parseFloat(document.getElementById('calcRcsBasicPrice').value) || 0;
    var rcsSinglePrice = parseFloat(document.getElementById('calcRcsSinglePrice').value) || 0;
    var monthlyMessages = parseFloat(document.getElementById('calcMonthlyMessages').value) || 0;
    var avgFragments = parseFloat(document.getElementById('calcFragments').value) || 1;
    var penetration = parseFloat(document.getElementById('calcPenetration').value) || 0;

    // Update slider value displays
    calcSetText('calcFragmentsValue', avgFragments.toFixed(1));
    calcSetText('calcPenetrationValue', Math.round(penetration) + '%');

    // Determine RCS pricing mode based on fragments
    var useRcsBasic = (avgFragments <= 1);
    calcSetText('calcModeIndicator', useRcsBasic ? 'RCS Basic' : 'RCS Single');

    // Per-message costs
    var avgSmsCost = smsPrice * avgFragments;
    var rcsPortion = penetration / 100;
    var smsPortion = 1 - rcsPortion;
    var avgBlendedCost = useRcsBasic
        ? (rcsPortion * rcsBasicPrice) + (smsPortion * smsPrice)
        : (rcsPortion * rcsSinglePrice) + (smsPortion * smsPrice * avgFragments);

    // Monthly totals
    var totalSmsCost = avgSmsCost * monthlyMessages;
    var totalBlendedCost = avgBlendedCost * monthlyMessages;
    var monthlySaving = totalSmsCost - totalBlendedCost;
    var savingPercent = totalSmsCost > 0 ? ((totalSmsCost - totalBlendedCost) / totalSmsCost) * 100 : 0;
    var annualSaving = monthlySaving * 12;

    // Hero figures (clamp negative to zero for display sanity)
    var displaySaving = Math.max(0, monthlySaving);
    var displayAnnual = Math.max(0, annualSaving);
    var displayPercent = Math.max(0, savingPercent);

    calcSetText('calcHeroSaving', calcFmtMoney(displaySaving, 0));
    calcSetText('calcHeroPercent', displayPercent.toFixed(1) + '% cheaper than SMS');
    calcSetText('calcReachBadge', 'Based on ' + Math.round(penetration) + '% RCS reach');
    calcSetText('calcCurrentSmsCost', calcFmtMoney(totalSmsCost, 0));
    calcSetText('calcBlendedCostMonthly', calcFmtMoney(totalBlendedCost, 0));
    calcSetText('calcSaveAmount', calcFmtMoney(displaySaving, 0));

    // Bottom 4 metric cards
    calcSetText('calcMetricSmsCost', calcFmtMoney(totalSmsCost, 0));
    calcSetText('calcMetricBlendedCost', calcFmtMoney(totalBlendedCost, 0));
    calcSetText('calcMetricSavingPct', displayPercent.toFixed(1) + '%');
    calcSetText('calcMetricAnnualSaving', calcFmtMoney(displayAnnual, 0));
}

// Add event listeners for calculator inputs (number + range)
document.querySelectorAll('#tile-rcs-calculator input').forEach(function(input) {
    input.addEventListener('input', calculateSavings);
    input.addEventListener('change', calculateSavings);
});

function dismissNotification(notificationId) {
    var notification = document.getElementById(notificationId);
    if (notification) {
        notification.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
        notification.style.opacity = '0';
        notification.style.transform = 'translateX(20px)';
        
        setTimeout(function() {
            notification.remove();
            
            // Check if any notifications remain
            var remaining = document.querySelectorAll('#tile-notifications .notification-item');
            if (remaining.length === 0) {
                document.getElementById('no-notifications').classList.remove('d-none');
                // Update badge
                var badge = document.querySelector('#tile-notifications .badge');
                if (badge) badge.remove();
            }
        }, 300);
    }
    
    // TODO: API call to mark notification as dismissed
    // POST /api/notifications/dismiss { id: notificationId }
}
</script>
@endpush
@endsection
