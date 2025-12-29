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
    </section>
    
    <section class="mb-4" id="rcsPromotion">
        <div class="d-flex align-items-center mb-3">
            <h4 class="mb-0"><i class="fas fa-rocket me-2 text-primary"></i>RCS Promotion & Tools</h4>
        </div>
        <div class="row">
            <div class="col-xl-4 col-lg-6">
                <div class="card" id="tile-rcs-upgrade">
                    <div class="card-body">
                        <div class="d-flex align-items-start">
                            <div class="icon-box bg-primary rounded me-3" style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-comment-dots text-white fa-lg"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="mb-1">Upgrade to RCS</h5>
                                <p class="text-muted mb-2 small">Enhance your messaging with rich media, carousels, and interactive buttons.</p>
                                <a href="#" class="btn btn-sm btn-outline-primary disabled">Learn More</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-lg-6">
                <div class="card" id="tile-rcs-agents">
                    <div class="card-body">
                        <div class="d-flex align-items-start">
                            <div class="icon-box bg-success rounded me-3" style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-robot text-white fa-lg"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="mb-1">RCS Agents</h5>
                                <p class="text-muted mb-2 small">Manage your verified RCS agents and brand identities.</p>
                                <a href="{{ route('management.rcs-agent') }}" class="btn btn-sm btn-outline-success">Manage Agents</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-lg-6">
                <div class="card" id="tile-rcs-templates">
                    <div class="card-body">
                        <div class="d-flex align-items-start">
                            <div class="icon-box bg-info rounded me-3" style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-layer-group text-white fa-lg"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="mb-1">RCS Templates</h5>
                                <p class="text-muted mb-2 small">Create and manage rich card templates for consistent messaging.</p>
                                <a href="{{ route('management.templates') }}" class="btn btn-sm btn-outline-info">View Templates</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-12">
                <div class="card" id="tile-rcs-quick-actions">
                    <div class="card-header border-0 pb-0">
                        <h5 class="card-title mb-0">Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-xl-3 col-lg-4 col-md-6">
                                <a href="{{ route('messages.send') }}" class="btn btn-outline-primary w-100 py-3 d-flex align-items-center justify-content-center gap-2">
                                    <i class="fas fa-paper-plane"></i>
                                    <span>Send Message</span>
                                </a>
                            </div>
                            <div class="col-xl-3 col-lg-4 col-md-6">
                                <a href="{{ route('messages.inbox') }}" class="btn btn-outline-primary w-100 py-3 d-flex align-items-center justify-content-center gap-2">
                                    <i class="fas fa-inbox"></i>
                                    <span>View Inbox</span>
                                </a>
                            </div>
                            <div class="col-xl-3 col-lg-4 col-md-6">
                                <a href="{{ route('contacts.lists') }}" class="btn btn-outline-primary w-100 py-3 d-flex align-items-center justify-content-center gap-2">
                                    <i class="fas fa-list"></i>
                                    <span>Manage Lists</span>
                                </a>
                            </div>
                            <div class="col-xl-3 col-lg-4 col-md-6">
                                <a href="{{ route('purchase') }}" class="btn btn-outline-primary w-100 py-3 d-flex align-items-center justify-content-center gap-2">
                                    <i class="fas fa-shopping-cart"></i>
                                    <span>Buy Credits</span>
                                </a>
                            </div>
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
    // Example usage:
    // setTileLoading('tile-balance', true);
    // fetch('/api/balance').then(res => res.json()).then(data => {
    //     document.getElementById('balance-value').textContent = '£' + data.balance;
    //     setTileLoading('tile-balance', false);
    // }).catch(() => setTileError('tile-balance', true));
    
    updateDeliveryRateColor();
    
    window.setTileLoading = setTileLoading;
    window.setTileError = setTileError;
    window.updateDeliveryRateColor = updateDeliveryRateColor;
});
</script>
@endpush
@endsection
