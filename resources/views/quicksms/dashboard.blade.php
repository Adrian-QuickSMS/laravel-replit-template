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
                <div class="card dashboard-tile h-100" id="tile-rcs-calculator">
                    <div class="card-header border-0 pb-0">
                        <h5 class="card-title mb-0"><i class="fas fa-calculator me-2" style="color: #886CC0;"></i>RCS vs SMS Savings Calculator</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-2 mb-3">
                            <div class="col-6 col-md-4">
                                <label class="form-label small mb-1">SMS Price (£)</label>
                                <input type="number" class="form-control form-control-sm bg-light" id="calcSmsPrice" value="{{ $pricingData['sms'] ?? '0' }}" readonly>
                            </div>
                            <div class="col-6 col-md-4">
                                <label class="form-label small mb-1">RCS Basic (£)</label>
                                <input type="number" class="form-control form-control-sm bg-light" id="calcRcsBasicPrice" value="{{ $pricingData['rcs_basic'] ?? '0' }}" readonly>
                            </div>
                            <div class="col-6 col-md-4">
                                <label class="form-label small mb-1">RCS Single (£)</label>
                                <input type="number" class="form-control form-control-sm bg-light" id="calcRcsSinglePrice" value="{{ $pricingData['rcs_single'] ?? '0' }}" readonly>
                            </div>
                            <div class="col-6 col-md-4">
                                <label class="form-label small mb-1">Avg Fragments</label>
                                <input type="number" class="form-control form-control-sm" id="calcFragments" placeholder="1" min="1" step="0.1" value="1">
                            </div>
                            <div class="col-6 col-md-4">
                                <label class="form-label small mb-1">Penetration %</label>
                                <input type="number" class="form-control form-control-sm" id="calcPenetration" placeholder="65" min="0" max="100" value="65">
                            </div>
                            <div class="col-6 col-md-4 d-flex align-items-end">
                                <small class="text-muted" id="calcModeIndicator">Mode: RCS Basic</small>
                            </div>
                        </div>
                        <hr class="my-2">
                        <div class="row g-2">
                            <div class="col-4 text-center">
                                <p class="mb-1 text-muted small">Avg SMS Cost</p>
                                <h5 class="mb-0 text-danger" id="calcSmsOnlyCost">£0.000</h5>
                            </div>
                            <div class="col-4 text-center">
                                <p class="mb-1 text-muted small">Avg Blended Cost</p>
                                <h5 class="mb-0 text-success" id="calcBlendedCost">£0.000</h5>
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
            
            <div class="col-xl-6 col-lg-6 mb-3" id="tile-register-rcs-wrapper">
                <div class="card tryal-gradient h-100" id="tile-register-rcs">
                    <div class="card-body tryal row">
                        <div class="col-xl-7 col-sm-7">
                            <h2 class="mb-0">Register for RCS</h2>
                            <span>Get your brand verified and start sending rich, interactive messages to your customers.</span>
                            <a href="{{ route('management.rcs-agent') }}" class="btn btn-rounded" id="btnRegisterRcs" style="background: transparent; border: 2px solid rgba(255,255,255,0.8); color: #fff;">Register Now</a>
                        </div>
                        <div class="col-xl-5 col-sm-5">
                            <svg xmlns="http://www.w3.org/2000/svg" width="120" height="100" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.7)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="sd-shape">
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                                <circle cx="9" cy="10" r="1" fill="rgba(255,255,255,0.7)"></circle>
                                <circle cx="12" cy="10" r="1" fill="rgba(255,255,255,0.7)"></circle>
                                <circle cx="15" cy="10" r="1" fill="rgba(255,255,255,0.7)"></circle>
                            </svg>
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

/* Calculator outputs centered */
#tile-rcs-calculator .col-4 h5 {
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
#tile-test-rcs .btn,
#tile-register-rcs .btn {
    font-weight: 500;
    border-radius: 0.375rem;
    padding: 0.5rem 1rem;
    transition: all 0.2s ease;
}

/* Primary CTA */
#tile-rcs-advertisement .btn-primary,
#tile-register-rcs .btn-warning {
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}
#tile-rcs-advertisement .btn-primary:hover,
#tile-register-rcs .btn-warning:hover {
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
    
    /* Register RCS tile stacks */
    #tile-register-rcs .card-body {
        flex-direction: column;
        text-align: center;
        gap: 1rem;
    }
    #tile-register-rcs .btn {
        margin-left: 0 !important;
        margin-top: 0.5rem;
    }
    
    /* Calculator inputs 2 per row */
    #tile-rcs-calculator .col-md-4 {
        flex: 0 0 50%;
        max-width: 50%;
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
    
    /* Calculator outputs smaller */
    #tile-rcs-calculator .col-4 h5 {
        font-size: 0.9rem;
    }
    #tile-rcs-calculator .col-4 p {
        font-size: 0.7rem;
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

/* Extra small devices (< 400px) */
@media (max-width: 399.98px) {
    /* Metric tiles single column */
    #operationalOverview .col-sm-6 {
        flex: 0 0 100%;
        max-width: 100%;
    }
    
    /* Calculator inputs single column */
    #tile-rcs-calculator .col-6 {
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
#tile-rcs-calculator .form-control-sm,
#tile-test-rcs .form-control {
    border-radius: 0.375rem;
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
        
        // GET /api/pricing/rcs-calculator
        getCalculatorDefaults: function() {
            return new Promise(function(resolve, reject) {
                setTimeout(function() {
                    if (MockAPI.shouldFail()) {
                        reject(new Error('Failed to fetch pricing data'));
                    } else {
                        resolve({
                            smsPrice: 0.035,
                            rcsBasicPrice: 0.040,
                            rcsSinglePrice: 0.055,
                            avgFragments: 1,
                            penetration: 65
                        });
                    }
                }, MockAPI.delay());
            });
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
        MockAPI.getCalculatorDefaults()
            .then(function(data) {
                if (data.smsPrice !== undefined) {
                    document.getElementById('calcSmsPrice').value = data.smsPrice;
                }
                if (data.rcsBasicPrice !== undefined) {
                    document.getElementById('calcRcsBasicPrice').value = data.rcsBasicPrice;
                }
                if (data.rcsSinglePrice !== undefined) {
                    document.getElementById('calcRcsSinglePrice').value = data.rcsSinglePrice;
                }
                if (data.avgFragments !== undefined) {
                    document.getElementById('calcFragments').value = data.avgFragments;
                }
                if (data.penetration !== undefined) {
                    document.getElementById('calcPenetration').value = data.penetration;
                }
                // Trigger initial calculation
                calculateSavings();
            })
            .catch(function(err) {
                console.error('Calculator defaults error:', err);
                // Use fallback defaults already in HTML
                calculateSavings();
            });
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

function calculateSavings() {
    var smsPrice = parseFloat(document.getElementById('calcSmsPrice').value) || 0;
    var rcsBasicPrice = parseFloat(document.getElementById('calcRcsBasicPrice').value) || 0;
    var rcsSinglePrice = parseFloat(document.getElementById('calcRcsSinglePrice').value) || 0;
    var avgFragments = parseFloat(document.getElementById('calcFragments').value) || 1;
    var penetration = parseFloat(document.getElementById('calcPenetration').value) || 65;
    
    // Determine which RCS pricing mode to use based on avgFragments
    var useRcsBasic = (avgFragments <= 1);
    var modeIndicator = document.getElementById('calcModeIndicator');
    
    // Update mode indicator
    if (modeIndicator) {
        if (useRcsBasic) {
            modeIndicator.textContent = 'Mode: RCS Basic';
            modeIndicator.classList.remove('text-warning');
            modeIndicator.classList.add('text-muted');
        } else {
            modeIndicator.textContent = 'Mode: RCS Single';
            modeIndicator.classList.remove('text-muted');
            modeIndicator.classList.add('text-warning');
        }
    }
    
    // Calculate average SMS cost per message (based on fragments)
    var avgSmsCost = smsPrice * avgFragments;
    
    // Calculate blended cost based on formula:
    // If avgFragments = 1: 65% RCS Basic + 35% SMS
    // If avgFragments > 1: 65% RCS Single + 35% (SMS * avgFragments)
    var rcsPortion = penetration / 100;
    var smsPortion = 1 - rcsPortion;
    
    var avgBlendedCost;
    if (useRcsBasic) {
        // avgFragments = 1: Use RCS Basic price
        avgBlendedCost = (rcsPortion * rcsBasicPrice) + (smsPortion * smsPrice);
    } else {
        // avgFragments > 1: Use RCS Single price, SMS uses fragments
        avgBlendedCost = (rcsPortion * rcsSinglePrice) + (smsPortion * smsPrice * avgFragments);
    }
    
    // Calculate savings percentage
    var savings = avgSmsCost > 0 ? ((avgSmsCost - avgBlendedCost) / avgSmsCost) * 100 : 0;
    
    // Update display (show 3 decimal places for per-message costs)
    document.getElementById('calcSmsOnlyCost').textContent = '£' + avgSmsCost.toFixed(3);
    document.getElementById('calcBlendedCost').textContent = '£' + avgBlendedCost.toFixed(3);
    document.getElementById('calcSavings').textContent = savings.toFixed(1) + '%';
    
    // Color coding for savings
    var savingsEl = document.getElementById('calcSavings');
    savingsEl.classList.remove('text-primary', 'text-success', 'text-danger');
    if (savings > 10) {
        savingsEl.classList.add('text-success');
    } else if (savings > 0) {
        savingsEl.classList.add('text-primary');
    } else {
        savingsEl.classList.add('text-danger');
    }
}

// Add event listeners for calculator inputs
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
