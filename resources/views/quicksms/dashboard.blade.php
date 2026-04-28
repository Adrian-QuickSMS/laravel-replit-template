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
                <div class="card dashboard-tile h-100" id="tile-rcs-advertisement">
                    <div class="card-body p-0 h-100">
                        <div class="row g-0 h-100 align-items-stretch">
                            <div class="col-md-7 unlock-rcs-content">
                                <div class="p-4 d-flex flex-column h-100">
                                    <span class="unlock-rcs-badge mb-3">New</span>
                                    <h3 class="unlock-rcs-heading mb-2">Unlock Rich Messaging with RCS</h3>
                                    <p class="unlock-rcs-subtitle mb-4">Create engaging conversations with branded sender identity, images, buttons and more.</p>

                                    <div class="unlock-rcs-features mb-4">
                                        <div class="unlock-rcs-feature">
                                            <div class="unlock-rcs-feature-icon"><i class="fas fa-shield-alt"></i></div>
                                            <div class="unlock-rcs-feature-text">
                                                <div class="unlock-rcs-feature-title">Verified sender identity</div>
                                                <div class="unlock-rcs-feature-subtitle">Build trust with your brand</div>
                                            </div>
                                        </div>
                                        <div class="unlock-rcs-feature">
                                            <div class="unlock-rcs-feature-icon"><i class="fas fa-images"></i></div>
                                            <div class="unlock-rcs-feature-text">
                                                <div class="unlock-rcs-feature-title">Rich media &amp; interactive</div>
                                                <div class="unlock-rcs-feature-subtitle">Images, videos, buttons and carousels</div>
                                            </div>
                                        </div>
                                        <div class="unlock-rcs-feature">
                                            <div class="unlock-rcs-feature-icon"><i class="fas fa-comment-dots"></i></div>
                                            <div class="unlock-rcs-feature-text">
                                                <div class="unlock-rcs-feature-title">SMS fallback included</div>
                                                <div class="unlock-rcs-feature-subtitle">Reach every customer, every time</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="unlock-rcs-cta-row mt-auto">
                                        <a href="{{ route('management.rcs-agent') }}" class="btn unlock-rcs-cta-primary">
                                            <i class="fas fa-arrow-right me-2"></i>Start using RCS
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-5 unlock-rcs-visual">
                                <img src="{{ asset('images/rcs-phone-mockup.png') }}"
                                     alt="QuickSMS RCS rich messaging shown on a mobile phone with branded sender, rich media and interactive buttons"
                                     class="unlock-rcs-image"
                                     loading="lazy">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-6 col-lg-6 mb-3">
                <div class="card dashboard-tile h-100" id="tile-test-rcs">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-start mb-4">
                            <div class="test-rcs-icon me-3"><i class="fas fa-mobile-alt"></i></div>
                            <div class="flex-grow-1">
                                <h5 class="card-title mb-1">Send yourself a test RCS message</h5>
                                <p class="text-muted small mb-0">Experience how your customers will receive rich messaging.</p>
                            </div>
                        </div>

                        {{-- Hidden input keeps existing JS hook (sendTestRcs reads/writes via id="testRcsMessageType") --}}
                        <input type="hidden" id="testRcsMessageType" value="basic-text">

                        <div class="mb-4">
                            <div class="test-rcs-step-label mb-2" id="testRcsTypeLabel">1. Choose test message type</div>
                            <div class="test-rcs-radios" role="radiogroup" aria-labelledby="testRcsTypeLabel">
                                <label class="test-rcs-radio selected" data-value="basic-text">
                                    <input type="radio" name="testRcsType" value="basic-text" checked class="visually-hidden">
                                    <span class="test-rcs-radio-circle" aria-hidden="true"></span>
                                    <span class="test-rcs-radio-text">
                                        <span class="test-rcs-radio-title">Basic RCS message</span>
                                        <span class="test-rcs-radio-subtitle">Simple text message</span>
                                    </span>
                                </label>
                                <label class="test-rcs-radio" data-value="rich-card">
                                    <input type="radio" name="testRcsType" value="rich-card" class="visually-hidden">
                                    <span class="test-rcs-radio-circle" aria-hidden="true"></span>
                                    <span class="test-rcs-radio-text">
                                        <span class="test-rcs-radio-title">Rich card message</span>
                                        <span class="test-rcs-radio-subtitle">Image, text and buttons</span>
                                    </span>
                                </label>
                                <label class="test-rcs-radio" data-value="carousel">
                                    <input type="radio" name="testRcsType" value="carousel" class="visually-hidden">
                                    <span class="test-rcs-radio-circle" aria-hidden="true"></span>
                                    <span class="test-rcs-radio-text">
                                        <span class="test-rcs-radio-title">Carousel message</span>
                                        <span class="test-rcs-radio-subtitle">Swipeable cards</span>
                                    </span>
                                </label>
                            </div>
                        </div>

                        <div class="mb-2">
                            <div class="test-rcs-step-label mb-2">2. Enter your mobile number</div>
                            <div class="test-rcs-input-group">
                                <span class="test-rcs-flag" aria-hidden="true">
                                    <span class="test-rcs-flag-emoji">🇬🇧</span>
                                    <span class="test-rcs-flag-code">+44</span>
                                </span>
                                <input type="tel" class="form-control test-rcs-mobile-input" id="testRcsMobile"
                                       placeholder="7700 900000" aria-describedby="testRcsFeedback">
                            </div>
                            <div class="invalid-feedback" id="testRcsFeedback">Please enter a valid UK mobile number</div>
                            <div class="test-rcs-helper mt-2">
                                <i class="fas fa-shield-alt me-1"></i>Your number is safe and only used for testing.
                            </div>
                        </div>

                        <div class="mt-4 mb-3">
                            <button class="btn test-rcs-cta w-100" id="btnSendTestRcs" onclick="sendTestRcs()">
                                <i class="fas fa-paper-plane me-2"></i>Send me a test RCS message
                            </button>
                        </div>

                        <div class="d-none mb-3" id="testRcsResult">
                            <div class="alert alert-success mb-0 py-2" id="testRcsSuccess">
                                <span id="testRcsSuccessMessage">Test message sent successfully!</span>
                            </div>
                            <div class="alert alert-danger mb-0 py-2 d-none" id="testRcsFail">
                                <span id="testRcsFailMessage">Failed to send test message. Please try again.</span>
                            </div>
                        </div>

                        <div class="test-rcs-footer-note">
                            <i class="fas fa-info-circle me-1"></i>Sent via your registered RCS agent with SMS fallback if unavailable.
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
    
    {{-- ============================================================
         HELP CENTRE — top row of three cards
         (Replaces the previous "Support & Notifications" section.)
         ============================================================ --}}
    <section class="mb-4" id="helpCentre">
        <div class="d-flex align-items-start mb-3">
            <div class="help-centre-section-icon me-2">
                <i class="fas fa-headset"></i>
            </div>
            <div>
                <h4 class="mb-0">Help Centre</h4>
                <small class="text-muted">Get support, find answers and stay up to date.</small>
            </div>
        </div>
        <div class="row g-3">
            {{-- Card 1: Open Support Tickets (HubSpot Service Hub) --}}
            <div class="col-lg-4 col-md-6">
                <div class="card dashboard-tile help-centre-card h-100" id="card-help-tickets">
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="d-flex align-items-start mb-3">
                            <div class="hc-icon-chip hc-chip-orange me-3">
                                <i class="fas fa-ticket-alt"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <h5 class="mb-1">Open Support Tickets</h5>
                                    <span class="hc-demo-badge d-none" id="hc-tickets-demo-badge" title="HubSpot is not connected — showing sample data.">Demo data</span>
                                </div>
                                <p class="text-muted small mb-0">You have active tickets that need attention</p>
                            </div>
                        </div>

                        <div class="hc-tickets-summary mb-3">
                            <span class="hc-tickets-count" id="hc-tickets-total">—</span>
                            <span class="hc-tickets-count-label">active tickets</span>
                        </div>

                        <div class="hc-tickets-stats row g-2 mb-3">
                            <div class="col-4">
                                <div class="hc-stat-chip">
                                    <span class="hc-stat-value"><span class="hc-dot hc-dot-red"></span><span id="hc-tickets-awaiting">—</span></span>
                                    <small class="hc-stat-label">Awaiting reply</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="hc-stat-chip">
                                    <span class="hc-stat-value"><span class="hc-dot hc-dot-amber"></span><span id="hc-tickets-progress">—</span></span>
                                    <small class="hc-stat-label">In progress</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="hc-stat-chip">
                                    <span class="hc-stat-value"><span class="hc-dot hc-dot-green"></span><span id="hc-tickets-resolved">—</span></span>
                                    <small class="hc-stat-label">Resolved</small>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-warning small py-2 px-3 mb-3 d-none" id="hc-tickets-error" role="status">
                            <i class="fas fa-exclamation-triangle me-1"></i>Live ticket data is temporarily unavailable.
                        </div>

                        <div class="d-flex gap-2 flex-wrap mt-auto">
                            <a href="{{ route('support.dashboard') }}" class="btn hc-btn-primary">
                                View tickets <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                            <a href="{{ route('support.create-ticket') }}" class="btn hc-btn-secondary">
                                <i class="fas fa-plus me-1"></i>Create ticket
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card 2: Knowledge Base (HubSpot KB search) --}}
            <div class="col-lg-4 col-md-6">
                <div class="card dashboard-tile help-centre-card h-100" id="card-help-kb">
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="d-flex align-items-start mb-3">
                            <div class="hc-icon-chip hc-chip-blue me-3">
                                <i class="fas fa-book"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <h5 class="mb-1">Knowledge Base</h5>
                                    <span class="hc-demo-badge d-none" id="hc-kb-demo-badge" title="HubSpot is not connected — showing sample articles.">Demo data</span>
                                </div>
                                <p class="text-muted small mb-0">Search guides, API docs and setup help</p>
                            </div>
                        </div>

                        <form id="hc-kb-form" class="hc-kb-search position-relative mb-3" autocomplete="off" role="search">
                            <i class="fas fa-search hc-kb-search-icon" aria-hidden="true"></i>
                            <input type="search" class="form-control" id="hc-kb-input" name="q"
                                placeholder="Search help articles…" maxlength="200" aria-label="Search the knowledge base">
                            <ul class="hc-kb-results d-none list-unstyled mb-0" id="hc-kb-results" role="listbox"></ul>
                        </form>

                        <div class="mb-3">
                            <small class="text-muted d-block mb-2">Popular topics</small>
                            <div class="d-flex flex-wrap gap-2 hc-topic-pills">
                                <button type="button" class="hc-topic-pill" data-topic="Getting started">Getting started</button>
                                <button type="button" class="hc-topic-pill" data-topic="API integration">API integration</button>
                                <button type="button" class="hc-topic-pill" data-topic="RCS guides">RCS guides</button>
                                <button type="button" class="hc-topic-pill" data-topic="Billing &amp; payments">Billing &amp; payments</button>
                                <button type="button" class="hc-topic-pill" data-topic="Account settings">Account settings</button>
                            </div>
                        </div>

                        <a href="{{ route('support.knowledge-base') }}" class="hc-link-arrow mt-auto">
                            Browse all articles <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>

            {{-- Card 3: Platform Updates --}}
            <div class="col-lg-4 col-md-12">
                <div class="card dashboard-tile help-centre-card h-100" id="card-help-updates">
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="d-flex align-items-start mb-3">
                            <div class="hc-icon-chip hc-chip-purple me-3">
                                <i class="fas fa-rocket"></i>
                            </div>
                            <div>
                                <h5 class="mb-1">Platform Updates</h5>
                                <p class="text-muted small mb-0">Stay informed about changes and updates</p>
                            </div>
                        </div>

                        <div class="hc-tickets-summary mb-3">
                            <span class="hc-tickets-count" id="hc-updates-count">—</span>
                            <span class="hc-tickets-count-label" id="hc-updates-count-label">new updates</span>
                        </div>

                        <div class="hc-status-panel hc-status-operational mb-3" id="hc-status-panel">
                            <div class="hc-status-icon">
                                <i class="fas fa-check"></i>
                            </div>
                            <div class="hc-status-text">
                                <div class="fw-semibold" id="hc-status-title">All systems operational</div>
                                <small class="text-muted" id="hc-status-checked">Last checked just now</small>
                            </div>
                        </div>

                        <a href="#platformUpdatesFeed" class="btn hc-btn-outline w-100 mt-auto" id="hc-view-all-updates">
                            View all updates <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================================
         PLATFORM UPDATES & ALERTS — full-width feed panel
         ============================================================ --}}
    <section class="mb-4" id="platformUpdatesFeed">
        <div class="card dashboard-tile help-centre-feed">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap align-items-start justify-content-between mb-3 hc-feed-header">
                    <div class="me-3 mb-2">
                        <h5 class="mb-1">Platform Updates &amp; Alerts</h5>
                        <small class="text-muted">Important announcements, maintenance and new features</small>
                    </div>
                    <div class="d-flex align-items-center gap-3 hc-feed-controls flex-wrap">
                        <ul class="nav hc-feed-tabs mb-0" role="tablist" id="hc-feed-tabs">
                            <li class="nav-item"><button type="button" class="hc-feed-tab active" data-filter="all">All</button></li>
                            <li class="nav-item"><button type="button" class="hc-feed-tab" data-filter="update">Updates</button></li>
                            <li class="nav-item"><button type="button" class="hc-feed-tab" data-filter="maintenance">Maintenance</button></li>
                            <li class="nav-item"><button type="button" class="hc-feed-tab" data-filter="feature">Features</button></li>
                        </ul>
                        <button type="button" class="btn btn-link btn-sm p-0 hc-mark-read" id="hc-mark-read-btn">
                            <i class="fas fa-check me-1"></i>Mark all as read
                        </button>
                    </div>
                </div>

                <div id="hc-feed-list" class="hc-feed-list" aria-live="polite">
                    <div class="hc-feed-skeleton skeleton-shimmer"></div>
                    <div class="hc-feed-skeleton skeleton-shimmer mt-2"></div>
                </div>

                <div class="text-center mt-3 d-none" id="hc-feed-empty">
                    <p class="text-muted small mb-0"><i class="fas fa-check-circle me-1"></i>No updates in this category yet.</p>
                </div>

                <div class="text-center mt-4 hc-feed-footer d-none" id="hc-feed-footer">
                    <span class="text-muted small">View all updates <i class="fas fa-chevron-down ms-1"></i></span>
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
#helpCentre,
#platformUpdatesFeed {
    margin-bottom: 1.5rem;
}

/* Section headers consistent spacing */
#operationalOverview > .d-flex,
#rcsPromotion > .d-flex,
#helpCentre > .d-flex {
    margin-bottom: 1rem !important;
}

/* Ensure rows have consistent gutters */
#operationalOverview .row,
#rcsPromotion .row,
#helpCentre .row {
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

/* ========================================
   HELP CENTRE — section header & cards
   ======================================== */
#helpCentre .help-centre-section-icon {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    background: rgba(136, 108, 192, 0.12);
    color: #886CC0;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 1rem;
}

.help-centre-card,
.help-centre-feed {
    border-radius: 0.625rem;
    border: 1px solid rgba(0, 0, 0, 0.06);
    background: #ffffff;
    cursor: default;
}
.help-centre-card:hover,
.help-centre-feed:hover {
    transform: none;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.06);
}

.help-centre-card h5,
.help-centre-feed h5 {
    color: #1f2937;
    font-weight: 700;
    font-size: 1.05rem;
}

/* "Demo data" pill — surfaced when HUBSPOT_ACCESS_TOKEN is missing
   so users know the figures are samples, not live HubSpot data. */
.hc-demo-badge {
    display: inline-block;
    background: rgba(255, 193, 7, 0.16);
    color: #8a6100;
    border: 1px solid rgba(255, 193, 7, 0.4);
    border-radius: 999px;
    padding: 0.1rem 0.55rem;
    font-size: 0.68rem;
    font-weight: 600;
    line-height: 1.4;
    cursor: help;
}

/* Soft pastel icon chips reused for cards and feed items */
.hc-icon-chip {
    width: 56px;
    height: 56px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 1.25rem;
}
.hc-chip-orange { background: rgba(255, 138, 77, 0.14); color: #e87a3a; }
.hc-chip-blue   { background: rgba(56, 128, 255, 0.12); color: #3a7bdc; }
.hc-chip-purple { background: rgba(136, 108, 192, 0.14); color: #6a4fa0; }
.hc-chip-amber  { background: rgba(255, 193, 7, 0.16);  color: #b27a00; }
.hc-chip-grey   { background: rgba(108, 117, 125, 0.12); color: #5a6268; }

/* Big purple count + label (used by Tickets and Updates cards) */
.hc-tickets-summary {
    display: flex;
    align-items: baseline;
    gap: 0.5rem;
}
.hc-tickets-count {
    font-size: 2.5rem;
    font-weight: 700;
    color: #886CC0;
    line-height: 1;
}
.hc-tickets-count-label {
    font-size: 0.95rem;
    color: #886CC0;
    font-weight: 600;
}

/* Sub-stat chips inside the Tickets card */
.hc-stat-chip {
    background: #f7f8fb;
    border: 1px solid rgba(0, 0, 0, 0.05);
    border-radius: 10px;
    padding: 0.55rem 0.5rem;
    text-align: center;
    height: 100%;
}
.hc-stat-chip .hc-stat-value {
    display: block;
    font-weight: 700;
    color: #1f2937;
    font-size: 1.05rem;
    line-height: 1.2;
    margin-bottom: 0.25rem;
}
.hc-stat-chip .hc-stat-label {
    color: #6c757d;
    font-size: 0.72rem;
    line-height: 1.1;
}
.hc-dot {
    display: inline-block;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    margin-right: 0.4rem;
    vertical-align: middle;
}
.hc-dot-red   { background: #dc3545; }
.hc-dot-amber { background: #ffc107; }
.hc-dot-green { background: #28a745; }

/* Help Centre buttons (override Bootstrap defaults to match Fillow) */
.btn.hc-btn-primary {
    background: #886CC0;
    border: 1px solid #886CC0;
    border-radius: 10px;
    padding: 0.55rem 1.1rem;
    font-weight: 600;
    color: #fff;
}
.btn.hc-btn-primary:hover,
.btn.hc-btn-primary:focus {
    background: #6a4fa0;
    border-color: #6a4fa0;
    color: #fff;
}
.btn.hc-btn-secondary {
    border-radius: 10px;
    padding: 0.55rem 1.1rem;
    font-weight: 600;
    border: 1px solid rgba(0, 0, 0, 0.12);
    color: #1f2937;
    background: #fff;
}
.btn.hc-btn-secondary:hover,
.btn.hc-btn-secondary:focus {
    background: #f5f5f7;
    border-color: rgba(0, 0, 0, 0.18);
    color: #1f2937;
}
.btn.hc-btn-outline {
    border-radius: 10px;
    padding: 0.55rem 1.1rem;
    font-weight: 600;
    border: 1px solid rgba(136, 108, 192, 0.4);
    color: #886CC0;
    background: #fff;
}
.btn.hc-btn-outline:hover,
.btn.hc-btn-outline:focus {
    background: rgba(136, 108, 192, 0.08);
    border-color: #886CC0;
    color: #6a4fa0;
}

/* KB search box */
.hc-kb-search-icon {
    position: absolute;
    top: 50%;
    left: 0.85rem;
    transform: translateY(-50%);
    color: #9aa0aa;
    pointer-events: none;
    z-index: 2;
}
.hc-kb-search input {
    border-radius: 10px;
    padding-left: 2.4rem;
    background: #fafbfd;
    border-color: rgba(0, 0, 0, 0.08);
}
.hc-kb-search input:focus {
    background: #fff;
    border-color: #886CC0;
    box-shadow: 0 0 0 0.2rem rgba(136, 108, 192, 0.12);
}
.hc-kb-results {
    position: absolute;
    top: calc(100% + 4px);
    left: 0;
    right: 0;
    z-index: 20;
    background: #fff;
    border: 1px solid rgba(0, 0, 0, 0.08);
    border-radius: 10px;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08);
    max-height: 320px;
    overflow-y: auto;
}
.hc-kb-results li a {
    display: block;
    padding: 0.65rem 0.85rem;
    color: #1f2937;
    text-decoration: none;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}
.hc-kb-results li:last-child a { border-bottom: none; }
.hc-kb-results li a:hover { background: #f7f8fb; }
.hc-kb-result-title { font-weight: 600; font-size: 0.9rem; display: block; }
.hc-kb-result-snippet { display: block; color: #6c757d; font-size: 0.78rem; margin-top: 2px; }
.hc-kb-empty { padding: 0.65rem 0.85rem; color: #6c757d; font-size: 0.85rem; }

/* Popular topic pills */
.hc-topic-pill {
    background: #fff;
    border: 1px solid rgba(0, 0, 0, 0.1);
    color: #495057;
    border-radius: 999px;
    padding: 0.3rem 0.75rem;
    font-size: 0.78rem;
    font-weight: 500;
    transition: all 0.15s ease;
    cursor: pointer;
}
.hc-topic-pill:hover {
    background: rgba(136, 108, 192, 0.08);
    border-color: #886CC0;
    color: #6a4fa0;
}

.hc-link-arrow {
    color: #886CC0;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.85rem;
    align-self: flex-start;
}
.hc-link-arrow:hover { color: #6a4fa0; }

/* Status panel */
.hc-status-panel {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    border-radius: 12px;
    padding: 0.85rem 0.95rem;
    border: 1px solid transparent;
}
.hc-status-icon {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    flex-shrink: 0;
    font-size: 0.8rem;
}
.hc-status-operational {
    background: rgba(40, 167, 69, 0.08);
    border-color: rgba(40, 167, 69, 0.2);
}
.hc-status-operational .hc-status-icon { background: #28a745; }
.hc-status-degraded {
    background: rgba(255, 193, 7, 0.10);
    border-color: rgba(255, 193, 7, 0.3);
}
.hc-status-degraded .hc-status-icon { background: #ffc107; color: #1f2937; }
.hc-status-outage {
    background: rgba(220, 53, 69, 0.08);
    border-color: rgba(220, 53, 69, 0.25);
}
.hc-status-outage .hc-status-icon { background: #dc3545; }

/* ========================================
   PLATFORM UPDATES & ALERTS — feed panel
   ======================================== */
.hc-feed-tabs {
    display: flex;
    background: #f5f4f9;
    border-radius: 10px;
    padding: 0.25rem;
    gap: 2px;
    list-style: none;
}
.hc-feed-tab {
    border: none;
    background: transparent;
    color: #6c757d;
    padding: 0.4rem 0.95rem;
    border-radius: 8px;
    font-weight: 500;
    font-size: 0.85rem;
    cursor: pointer;
    transition: all 0.15s ease;
}
.hc-feed-tab:hover { color: #1f2937; }
.hc-feed-tab.active {
    background: #fff;
    color: #886CC0;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}
.hc-mark-read {
    color: #6c757d !important;
    font-size: 0.85rem;
    text-decoration: none;
    border: none;
    background: transparent;
}
.hc-mark-read:hover { color: #886CC0 !important; }

.hc-feed-list { display: flex; flex-direction: column; gap: 0.75rem; }
.hc-feed-skeleton { height: 110px; border-radius: 12px; }

.hc-feed-item {
    display: flex;
    gap: 1rem;
    padding: 1rem 1.1rem;
    border-radius: 12px;
    border-left: 4px solid #d1d5db;
    background: #f7f8fb;
    transition: opacity 0.2s ease;
}
.hc-feed-item.is-read { opacity: 0.65; }
.hc-feed-item.type-maintenance {
    background: linear-gradient(135deg, #fff9e6 0%, #fff3cd 100%);
    border-left-color: #ffc107;
}
.hc-feed-item.type-feature {
    background: rgba(136, 108, 192, 0.07);
    border-left-color: #886CC0;
}
.hc-feed-item.type-update {
    background: #f7f8fb;
    border-left-color: #6c757d;
}
.hc-feed-item .hc-icon-chip {
    width: 48px;
    height: 48px;
    font-size: 1.05rem;
    border-radius: 12px;
}
.hc-feed-item-body { flex: 1; min-width: 0; }
.hc-feed-item-badge {
    display: inline-block;
    background: rgba(255, 255, 255, 0.7);
    color: #6c4d20;
    font-weight: 600;
    font-size: 0.7rem;
    padding: 0.2rem 0.55rem;
    border-radius: 999px;
    margin-bottom: 0.35rem;
}
.hc-feed-item.type-feature .hc-feed-item-badge { color: #6a4fa0; }
.hc-feed-item.type-update .hc-feed-item-badge { color: #495057; background: #ffffff; }
.hc-feed-item-title {
    font-weight: 700;
    font-size: 0.95rem;
    color: #1f2937;
    margin: 0 0 0.25rem 0;
}
.hc-feed-item-text {
    color: #6c757d;
    font-size: 0.85rem;
    margin-bottom: 0.5rem;
}
.hc-feed-item-meta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 0.5rem;
}
.hc-feed-item-time { color: #9aa0aa; font-size: 0.78rem; }
.hc-feed-item-time i { margin-right: 0.25rem; }
.hc-feed-item-readmore {
    color: #886CC0;
    font-weight: 600;
    font-size: 0.82rem;
    text-decoration: none;
}
.hc-feed-item-readmore:hover { color: #6a4fa0; }
.hc-feed-item-aside {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 0.4rem;
    flex-shrink: 0;
}
.hc-new-pill {
    background: #ffe69c;
    color: #6c4d20;
    font-weight: 700;
    font-size: 0.7rem;
    padding: 0.2rem 0.6rem;
    border-radius: 999px;
}
.hc-feed-item.is-read .hc-new-pill { display: none; }

@media (max-width: 768px) {
    .hc-feed-header { flex-direction: column; align-items: stretch !important; }
    .hc-feed-controls { width: 100%; justify-content: space-between; }
    .hc-feed-tabs { width: 100%; overflow-x: auto; }
    .hc-feed-item { flex-wrap: wrap; }
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
#rcsPromotion .card {
    transition: box-shadow 0.2s ease, transform 0.2s ease;
}
#rcsPromotion .card:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
}

/* ========================================
   CTA BUTTONS - PLATFORM CONSISTENT STYLE
   ======================================== */
#rcsPromotion .btn,
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

/* Below md (< 768px): Unlock RCS tile stacks vertically; phone image renders
   in normal flow (no breakout) so the stacked layout stays tidy. */
@media (max-width: 767.98px) {
    #tile-rcs-advertisement .row {
        flex-direction: column;
    }
    #tile-rcs-advertisement .col-md-5,
    #tile-rcs-advertisement .col-md-7 {
        width: 100%;
        flex: 0 0 100%;
        max-width: 100%;
    }
    #tile-rcs-advertisement .unlock-rcs-visual {
        order: -1;
        min-height: 280px;
        padding: 1.25rem 1rem 0;
        position: relative;
    }
    #tile-rcs-advertisement .unlock-rcs-image {
        position: static;
        transform: none;
        width: auto;
        max-width: 90%;
        max-height: 280px;
        height: auto;
        margin: 0 auto;
    }
}

/* Mobile breakpoint (< 576px) */
@media (max-width: 575.98px) {
    /* Section headers smaller */
    #operationalOverview h4,
    #rcsPromotion h4,
    #helpCentre h4 {
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
    
    /* Test RCS tile mobile tweaks */
    #tile-test-rcs .test-rcs-radio {
        padding: 0.75rem 0.85rem;
    }
    #tile-test-rcs .test-rcs-radio-title {
        font-size: 0.9rem;
    }
    #tile-test-rcs .test-rcs-cta {
        font-size: 0.95rem;
    }
    #tile-rcs-advertisement .unlock-rcs-heading {
        font-size: 1.25rem;
    }
    #tile-rcs-advertisement .unlock-rcs-cta-row {
        flex-direction: column;
        align-items: stretch;
    }
    #tile-rcs-advertisement .unlock-rcs-cta-row .btn {
        width: 100%;
        text-align: center;
    }

    /* Help Centre cards stack nicely */
    #helpCentre .col-lg-4,
    #helpCentre .col-md-6,
    #helpCentre .col-md-12 {
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

/* ========================================
   UNLOCK RCS TILE (premium Fillow style)
   ======================================== */
#tile-rcs-advertisement .unlock-rcs-content {
    /* Transparent on purpose: the .card already paints a white, rounded
       background. A white background here would be a rectangle, which —
       once the card has overflow:visible to allow the phone image to
       break out — would extend past the card's rounded corners and make
       the top-left / bottom-left corners look square. */
    background: transparent;
}
#tile-rcs-advertisement .unlock-rcs-badge {
    display: inline-block;
    width: fit-content;
    background: rgba(136, 108, 192, 0.12);
    color: #6a4fa0;
    font-weight: 600;
    font-size: 0.75rem;
    letter-spacing: 0.02em;
    padding: 0.32rem 0.85rem;
    border-radius: 999px;
    text-transform: none;
}
#tile-rcs-advertisement .unlock-rcs-heading {
    color: #1f2937;
    font-weight: 700;
    font-size: 1.5rem;
    line-height: 1.25;
    letter-spacing: -0.01em;
}
#tile-rcs-advertisement .unlock-rcs-subtitle {
    color: #6b7280;
    font-size: 0.95rem;
    line-height: 1.5;
}
#tile-rcs-advertisement .unlock-rcs-features {
    display: flex;
    flex-direction: column;
    gap: 0.95rem;
}
#tile-rcs-advertisement .unlock-rcs-feature {
    display: flex;
    align-items: flex-start;
    gap: 0.85rem;
}
#tile-rcs-advertisement .unlock-rcs-feature-icon {
    flex: 0 0 38px;
    width: 38px;
    height: 38px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(136, 108, 192, 0.10);
    color: #6a4fa0;
    border-radius: 10px;
    font-size: 1rem;
}
#tile-rcs-advertisement .unlock-rcs-feature-text {
    flex: 1;
    min-width: 0;
}
#tile-rcs-advertisement .unlock-rcs-feature-title {
    color: #1f2937;
    font-weight: 600;
    font-size: 0.92rem;
    line-height: 1.3;
}
#tile-rcs-advertisement .unlock-rcs-feature-subtitle {
    color: #6b7280;
    font-size: 0.82rem;
    line-height: 1.35;
    margin-top: 0.1rem;
}
#tile-rcs-advertisement .unlock-rcs-cta-row {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
}
#tile-rcs-advertisement .unlock-rcs-cta-primary {
    background: #886CC0;
    color: #ffffff;
    border: 1px solid #886CC0;
    font-weight: 600;
    font-size: 0.9rem;
    padding: 0.6rem 1.1rem;
    border-radius: 0.5rem;
    box-shadow: 0 2px 6px rgba(136, 108, 192, 0.25);
    transition: background 0.18s ease, box-shadow 0.18s ease, transform 0.18s ease;
}
#tile-rcs-advertisement .unlock-rcs-cta-primary:hover,
#tile-rcs-advertisement .unlock-rcs-cta-primary:focus {
    background: #6a4fa0;
    border-color: #6a4fa0;
    color: #ffffff;
    box-shadow: 0 4px 10px rgba(136, 108, 192, 0.35);
    transform: translateY(-1px);
}
/* Allow the phone image to break out of the tile boundary on desktop */
#tile-rcs-advertisement.dashboard-tile,
#tile-rcs-advertisement .card-body,
#tile-rcs-advertisement .card-body > .row {
    overflow: visible;
}
#tile-rcs-advertisement .unlock-rcs-visual {
    background: transparent;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    border-radius: 0;
    overflow: visible;
    position: relative;
    min-height: 360px;
}
#tile-rcs-advertisement .unlock-rcs-image {
    position: absolute;
    top: 50%;
    left: 50%;
    width: 145%;
    max-width: none;
    max-height: none;
    height: auto;
    transform: translate(-50%, -52%);
    object-fit: contain;
    display: block;
    filter: drop-shadow(0 24px 42px rgba(136, 108, 192, 0.30))
            drop-shadow(0 6px 14px rgba(136, 108, 192, 0.18));
    pointer-events: none;
}

/* ========================================
   TEST RCS TILE (premium Fillow style)
   ======================================== */
#tile-test-rcs .test-rcs-icon {
    flex: 0 0 42px;
    width: 42px;
    height: 42px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(136, 108, 192, 0.10);
    color: #6a4fa0;
    border-radius: 10px;
    font-size: 1.05rem;
}
#tile-test-rcs .card-title {
    color: #1f2937;
    font-weight: 600;
    font-size: 1.05rem;
    line-height: 1.3;
}
#tile-test-rcs .test-rcs-step-label {
    color: #1f2937;
    font-weight: 600;
    font-size: 0.88rem;
}
#tile-test-rcs .test-rcs-radios {
    display: flex;
    flex-direction: column;
    gap: 0.55rem;
}
#tile-test-rcs .test-rcs-radio {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    padding: 0.85rem 1rem;
    border: 1px solid #e5e7eb;
    border-radius: 0.625rem;
    background: #ffffff;
    cursor: pointer;
    transition: border-color 0.15s ease, background 0.15s ease, box-shadow 0.15s ease;
    margin-bottom: 0;
}
#tile-test-rcs .test-rcs-radio:hover {
    border-color: rgba(136, 108, 192, 0.45);
    background: rgba(136, 108, 192, 0.03);
}
#tile-test-rcs .test-rcs-radio.selected {
    border-color: #886CC0;
    background: rgba(136, 108, 192, 0.06);
    box-shadow: 0 0 0 1px #886CC0 inset;
}
#tile-test-rcs .test-rcs-radio-circle {
    flex: 0 0 18px;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    border: 2px solid #d1d5db;
    background: #ffffff;
    position: relative;
    transition: border-color 0.15s ease;
}
#tile-test-rcs .test-rcs-radio.selected .test-rcs-radio-circle {
    border-color: #886CC0;
}
#tile-test-rcs .test-rcs-radio.selected .test-rcs-radio-circle::after {
    content: "";
    position: absolute;
    top: 50%;
    left: 50%;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #886CC0;
    transform: translate(-50%, -50%);
}
#tile-test-rcs .test-rcs-radio-text {
    display: flex;
    flex-direction: column;
    min-width: 0;
}
#tile-test-rcs .test-rcs-radio-title {
    color: #1f2937;
    font-weight: 600;
    font-size: 0.92rem;
    line-height: 1.25;
}
#tile-test-rcs .test-rcs-radio-subtitle {
    color: #6b7280;
    font-size: 0.8rem;
    line-height: 1.3;
    margin-top: 0.1rem;
}
#tile-test-rcs .test-rcs-input-group {
    display: flex;
    align-items: stretch;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    overflow: hidden;
    background: #ffffff;
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
}
#tile-test-rcs .test-rcs-input-group:focus-within {
    border-color: #886CC0;
    box-shadow: 0 0 0 3px rgba(136, 108, 192, 0.15);
}
#tile-test-rcs .test-rcs-flag {
    display: flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0 0.85rem;
    background: #f9fafb;
    border-right: 1px solid #e5e7eb;
    color: #374151;
    font-weight: 600;
    font-size: 0.92rem;
    white-space: nowrap;
}
#tile-test-rcs .test-rcs-flag-emoji {
    font-size: 1.1rem;
    line-height: 1;
}
#tile-test-rcs .test-rcs-mobile-input {
    border: none;
    border-radius: 0;
    box-shadow: none;
    padding: 0.6rem 0.85rem;
    font-size: 0.95rem;
    height: auto;
}
#tile-test-rcs .test-rcs-mobile-input:focus {
    outline: none;
    box-shadow: none;
    border: none;
}
#tile-test-rcs .test-rcs-mobile-input.is-invalid,
#tile-test-rcs .test-rcs-mobile-input.is-valid {
    background-image: none;
    border: none;
    padding-right: 0.85rem;
}
/* Invalid state — explicit class fallback (set by JS observer) plus :has() progressive enhancement */
#tile-test-rcs .test-rcs-input-group.has-error,
#tile-test-rcs:has(.test-rcs-mobile-input.is-invalid) .test-rcs-input-group {
    border-color: #dc3545;
    box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.12);
}
#tile-test-rcs .test-rcs-input-group.has-error ~ #testRcsFeedback,
#tile-test-rcs:has(.test-rcs-mobile-input.is-invalid) #testRcsFeedback {
    display: block;
}
#tile-test-rcs .test-rcs-helper {
    color: #6b7280;
    font-size: 0.78rem;
    display: flex;
    align-items: center;
}
#tile-test-rcs .test-rcs-helper i {
    color: #886CC0;
    opacity: 0.85;
}
#tile-test-rcs .test-rcs-cta {
    background: #886CC0;
    color: #ffffff;
    border: 1px solid #886CC0;
    font-weight: 600;
    font-size: 1rem;
    padding: 0.85rem 1.25rem;
    border-radius: 0.5rem;
    box-shadow: 0 2px 6px rgba(136, 108, 192, 0.25);
    transition: background 0.18s ease, box-shadow 0.18s ease, transform 0.18s ease;
}
#tile-test-rcs .test-rcs-cta:hover:not(:disabled),
#tile-test-rcs .test-rcs-cta:focus:not(:disabled) {
    background: #6a4fa0;
    border-color: #6a4fa0;
    color: #ffffff;
    box-shadow: 0 4px 10px rgba(136, 108, 192, 0.35);
    transform: translateY(-1px);
}
#tile-test-rcs .test-rcs-cta:disabled {
    background: #886CC0;
    border-color: #886CC0;
    opacity: 0.7;
    cursor: not-allowed;
}
#tile-test-rcs .test-rcs-footer-note {
    color: #6b7280;
    font-size: 0.78rem;
    line-height: 1.45;
    padding-top: 0.65rem;
    border-top: 1px solid #f1f3f5;
    display: flex;
    align-items: flex-start;
}
#tile-test-rcs .test-rcs-footer-note i {
    color: #886CC0;
    margin-top: 0.15rem;
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
    // +447xxxxxxxxx (13 chars) — full international
    // 447xxxxxxxxx  (12 chars) — international, no '+'
    // 07xxxxxxxxx   (11 chars) — UK national format with leading 0
    // 7xxxxxxxxx    (10 chars) — bare local digits, paired with the +44 prefix shown in the UI

    var patterns = [
        /^\+44(7\d{9})$/,      // +447xxxxxxxxx
        /^44(7\d{9})$/,        // 447xxxxxxxxx
        /^0(7\d{9})$/,         // 07xxxxxxxxx
        /^(7\d{9})$/           // 7xxxxxxxxx (matches the "+44 | 7700 900000" UI hint)
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
        error: 'Please enter a valid UK mobile number (e.g., 7700 900000, 07700 900000, or +44 7700 900000)' 
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

    // Read selected message type from the radio cards' hidden input
    // (basic-text | rich-card | carousel) — populated by initTestRcsRadios().
    var typeEl = document.getElementById('testRcsMessageType');
    var selectedType = (typeEl && typeEl.value) ? typeEl.value : 'basic-text';

    // Prepare payload for API
    var payload = {
        mobile: validation.normalized,  // Normalized UK format: +447xxxxxxxxx
        messageType: selectedType,
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
    btn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Send me a test RCS message';
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

// ========================================
// HELP CENTRE — tickets, KB search, platform updates feed
// ========================================
(function initHelpCentre() {
    if (!document.getElementById('helpCentre')) return;

    var endpoints = {
        tickets:         @json(route('portal.help-centre.tickets')),
        kbSearch:        @json(route('portal.help-centre.kb.search')),
        platformUpdates: @json(route('portal.help-centre.platform-updates')),
        markRead:        @json(route('portal.help-centre.platform-updates.mark-read')),
    };
    var csrfMeta = document.querySelector('meta[name="csrf-token"]');
    var csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

    var state = { updates: [], filter: 'all' };

    // ---------- Helpers ----------
    function escHtml(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return ({ '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;' })[c];
        });
    }
    function fmtDate(iso) {
        if (!iso) return '';
        try {
            var d = new Date(iso);
            return d.toLocaleString(undefined, {
                month: 'short', day: 'numeric', year: 'numeric',
                hour: '2-digit', minute: '2-digit', hour12: false
            });
        } catch (e) { return iso; }
    }

    // ---------- Tickets card ----------
    function renderTickets(d) {
        d = d || {};
        // When the HubSpot call failed (configured but not live) we show
        // em-dash placeholders so users do not mistake an outage for
        // "0 open tickets". Mock/demo mode keeps the numeric values.
        var failed = d.live === false && d.configured === true;
        var fmt = function (v) { return failed ? '—' : (v != null ? v : 0); };

        document.getElementById('hc-tickets-total').textContent     = fmt(d.total);
        document.getElementById('hc-tickets-awaiting').textContent  = fmt(d.awaiting_reply);
        document.getElementById('hc-tickets-progress').textContent  = fmt(d.in_progress);
        document.getElementById('hc-tickets-resolved').textContent  = fmt(d.resolved);

        // "Live data unavailable" warning vs "Demo data" pill — different states.
        var err = document.getElementById('hc-tickets-error');
        if (failed) {
            err.classList.remove('d-none');
        } else {
            err.classList.add('d-none');
        }
        var demo = document.getElementById('hc-tickets-demo-badge');
        if (demo) demo.classList.toggle('d-none', d.configured !== false);
    }
    function loadTickets() {
        return fetch(endpoints.tickets, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (j) { if (j && j.success) renderTickets(j.data); })
            .catch(function () {
                renderTickets({ total: 0, awaiting_reply: 0, in_progress: 0, resolved: 0, live: false, configured: true });
            });
    }

    // ---------- Knowledge Base search ----------
    var kbInput   = document.getElementById('hc-kb-input');
    var kbResults = document.getElementById('hc-kb-results');
    var kbForm    = document.getElementById('hc-kb-form');
    var kbDebounce;

    function renderKbResults(payload) {
        var results = (payload && payload.results) || [];
        var demo = document.getElementById('hc-kb-demo-badge');
        if (demo) demo.classList.toggle('d-none', payload && payload.live !== false);
        if (results.length === 0) {
            kbResults.innerHTML = '<li class="hc-kb-empty">No results — try different keywords.</li>';
        } else {
            kbResults.innerHTML = results.map(function (r) {
                var url = r.url || '#';
                return '<li role="option"><a href="' + escHtml(url) + '" target="_blank" rel="noopener">' +
                    '<span class="hc-kb-result-title">' + escHtml(r.title || 'Untitled') + '</span>' +
                    (r.snippet ? '<span class="hc-kb-result-snippet">' + escHtml(r.snippet) + '</span>' : '') +
                '</a></li>';
            }).join('');
        }
        kbResults.classList.remove('d-none');
    }
    function searchKnowledgeBase(q) {
        if (!q || q.length < 2) { kbResults.classList.add('d-none'); return; }
        fetch(endpoints.kbSearch + '?q=' + encodeURIComponent(q), { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (j) { if (j && j.success) renderKbResults(j.data); })
            .catch(function () {
                kbResults.innerHTML = '<li class="hc-kb-empty">Search is temporarily unavailable, try again shortly.</li>';
                kbResults.classList.remove('d-none');
            });
    }
    if (kbInput) {
        kbInput.addEventListener('input', function () {
            clearTimeout(kbDebounce);
            kbDebounce = setTimeout(function () { searchKnowledgeBase(kbInput.value.trim()); }, 250);
        });
        kbInput.addEventListener('focus', function () {
            if (kbInput.value.trim().length >= 2) kbResults.classList.remove('d-none');
        });
        document.addEventListener('click', function (e) {
            if (kbForm && !kbForm.contains(e.target)) kbResults.classList.add('d-none');
        });
    }
    if (kbForm) {
        kbForm.addEventListener('submit', function (e) { e.preventDefault(); searchKnowledgeBase(kbInput.value.trim()); });
    }
    Array.prototype.forEach.call(document.querySelectorAll('.hc-topic-pill'), function (btn) {
        btn.addEventListener('click', function () {
            var topic = btn.getAttribute('data-topic') || '';
            if (kbInput) {
                kbInput.value = topic;
                kbInput.focus();
                searchKnowledgeBase(topic);
            }
        });
    });

    // ---------- Platform Updates feed ----------
    var feedList   = document.getElementById('hc-feed-list');
    var feedEmpty  = document.getElementById('hc-feed-empty');
    var feedFooter = document.getElementById('hc-feed-footer');

    var typeMeta = {
        maintenance: { label: 'Scheduled Maintenance', icon: 'fas fa-wrench',     chip: 'hc-chip-amber' },
        feature:     { label: 'New Feature',           icon: 'fas fa-rocket',     chip: 'hc-chip-purple' },
        update:      { label: 'Update',                icon: 'fas fa-info-circle', chip: 'hc-chip-grey' }
    };

    function renderFeed() {
        var items = state.updates.filter(function (u) {
            return state.filter === 'all' || u.type === state.filter;
        });
        if (items.length === 0) {
            feedList.innerHTML = '';
            feedEmpty.classList.remove('d-none');
            feedFooter.classList.add('d-none');
            return;
        }
        feedEmpty.classList.add('d-none');
        feedFooter.classList.toggle('d-none', items.length <= 3);

        feedList.innerHTML = items.map(function (u) {
            var meta = typeMeta[u.type] || typeMeta.update;
            return '<article class="hc-feed-item type-' + escHtml(u.type) + (u.is_read ? ' is-read' : '') + '">' +
                '<div class="hc-icon-chip ' + meta.chip + '"><i class="' + meta.icon + '"></i></div>' +
                '<div class="hc-feed-item-body">' +
                    '<span class="hc-feed-item-badge">' + escHtml(meta.label) + '</span>' +
                    '<h6 class="hc-feed-item-title">' + escHtml(u.title) + '</h6>' +
                    '<p class="hc-feed-item-text">' + escHtml(u.body) + '</p>' +
                    '<div class="hc-feed-item-meta">' +
                        '<span class="hc-feed-item-time"><i class="fas fa-clock"></i>Posted: ' + escHtml(fmtDate(u.posted_at)) + '</span>' +
                        (u.link_url
                            ? '<a href="' + escHtml(u.link_url) + '" class="hc-feed-item-readmore" target="_blank" rel="noopener">Read more <i class="fas fa-arrow-right ms-1"></i></a>'
                            : '') +
                    '</div>' +
                '</div>' +
                '<div class="hc-feed-item-aside">' +
                    (!u.is_read ? '<span class="hc-new-pill">New</span>' : '') +
                '</div>' +
            '</article>';
        }).join('');
    }

    function renderUpdatesCard(data) {
        var unread = data.unread_count || 0;
        document.getElementById('hc-updates-count').textContent       = unread;
        document.getElementById('hc-updates-count-label').textContent = unread === 1 ? 'new update' : 'new updates';

        var panel   = document.getElementById('hc-status-panel');
        var title   = document.getElementById('hc-status-title');
        var checked = document.getElementById('hc-status-checked');
        panel.classList.remove('hc-status-operational', 'hc-status-degraded', 'hc-status-outage');
        var statusMap = {
            operational: { cls: 'hc-status-operational', label: 'All systems operational' },
            degraded:    { cls: 'hc-status-degraded',    label: 'Some systems degraded' },
            outage:      { cls: 'hc-status-outage',      label: 'Active outage in progress' }
        };
        var s = statusMap[data.system_status] || statusMap.operational;
        panel.classList.add(s.cls);
        title.textContent = s.label;
        checked.textContent = 'Last checked just now';
    }

    function loadPlatformUpdates() {
        return fetch(endpoints.platformUpdates, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (j) {
                if (!j || !j.success) throw new Error('failed');
                state.updates = j.data.updates || [];
                renderUpdatesCard(j.data);
                renderFeed();
            })
            .catch(function () {
                feedList.innerHTML = '';
                feedEmpty.classList.remove('d-none');
                var p = feedEmpty.querySelector('p');
                if (p) p.innerHTML = '<i class="fas fa-exclamation-circle me-1"></i>Updates are temporarily unavailable.';
            });
    }

    // Tab switching
    Array.prototype.forEach.call(document.querySelectorAll('.hc-feed-tab'), function (tab) {
        tab.addEventListener('click', function () {
            Array.prototype.forEach.call(document.querySelectorAll('.hc-feed-tab'), function (t) { t.classList.remove('active'); });
            tab.classList.add('active');
            state.filter = tab.getAttribute('data-filter') || 'all';
            renderFeed();
        });
    });

    // Mark all as read
    var markBtn = document.getElementById('hc-mark-read-btn');
    if (markBtn) {
        markBtn.addEventListener('click', function () {
            fetch(endpoints.markRead, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            }).then(function (res) {
                if (!res.ok) return;
                state.updates.forEach(function (u) { u.is_read = true; });
                renderUpdatesCard({ unread_count: 0, system_status: 'operational' });
                renderFeed();
            }).catch(function () { /* silent — non-critical */ });
        });
    }

    // "View all updates" → smooth scroll to feed
    var viewAllBtn = document.getElementById('hc-view-all-updates');
    if (viewAllBtn) {
        viewAllBtn.addEventListener('click', function (e) {
            var target = document.getElementById('platformUpdatesFeed');
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    }

    // Kick off initial loads
    loadTickets();
    loadPlatformUpdates();
})();

// ========================================
// TEST RCS — radio card selection + accessibility
// ========================================
(function initTestRcsRadios() {
    var init = function() {
        var radios = Array.prototype.slice.call(document.querySelectorAll('#tile-test-rcs .test-rcs-radio'));
        var hidden = document.getElementById('testRcsMessageType');
        if (!radios.length || !hidden) return;

        function syncAria() {
            radios.forEach(function(c, i) {
                var selected = c.classList.contains('selected');
                c.setAttribute('aria-checked', selected ? 'true' : 'false');
                // Roving tabindex: only the selected card is in the tab order
                c.setAttribute('tabindex', selected ? '0' : '-1');
            });
        }

        function selectByIndex(idx, focus) {
            if (idx < 0) idx = radios.length - 1;
            if (idx >= radios.length) idx = 0;
            var target = radios[idx];
            var value = target.getAttribute('data-value');
            radios.forEach(function(c) { c.classList.remove('selected'); });
            target.classList.add('selected');
            var input = target.querySelector('input[type="radio"]');
            if (input) input.checked = true;
            hidden.value = value;
            syncAria();
            if (focus) target.focus();
        }

        radios.forEach(function(card, idx) {
            card.setAttribute('role', 'radio');
            card.addEventListener('click', function() {
                selectByIndex(idx, false);
            });
            card.addEventListener('keydown', function(e) {
                switch (e.key) {
                    case 'Enter':
                    case ' ':
                        e.preventDefault();
                        selectByIndex(idx, true);
                        break;
                    case 'ArrowDown':
                    case 'ArrowRight':
                        e.preventDefault();
                        selectByIndex(idx + 1, true);
                        break;
                    case 'ArrowUp':
                    case 'ArrowLeft':
                        e.preventDefault();
                        selectByIndex(idx - 1, true);
                        break;
                }
            });
        });
        syncAria();

        // ----- Mobile-input invalid-state observer -----
        // sendTestRcs() toggles .is-invalid on #testRcsMobile but the input is now
        // nested inside .test-rcs-input-group, so the default Bootstrap sibling rule
        // for .invalid-feedback no longer fires. Mirror the state onto the wrapper
        // (and aria-invalid on the input) as a robust fallback alongside the :has()
        // CSS selector.
        var mobileInput = document.getElementById('testRcsMobile');
        var wrapper = mobileInput ? mobileInput.closest('.test-rcs-input-group') : null;
        if (mobileInput && wrapper && 'MutationObserver' in window) {
            var sync = function() {
                var invalid = mobileInput.classList.contains('is-invalid');
                wrapper.classList.toggle('has-error', invalid);
                mobileInput.setAttribute('aria-invalid', invalid ? 'true' : 'false');
            };
            new MutationObserver(sync).observe(mobileInput, {
                attributes: true,
                attributeFilter: ['class']
            });
            sync();
        }
    };
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
</script>
@endpush
@endsection
