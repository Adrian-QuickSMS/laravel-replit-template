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
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
                <div class="card" id="tile-credits">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="mb-1 text-muted">Credits Balance</p>
                                <h3 class="mb-0" id="credits-value">--</h3>
                            </div>
                            <div class="icon-box bg-primary-light rounded-circle">
                                <i class="fas fa-coins text-primary fa-lg"></i>
                            </div>
                        </div>
                        <div class="mt-3">
                            <span class="text-muted small" id="credits-subtext">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
                <div class="card" id="tile-messages-today">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="mb-1 text-muted">Messages Today</p>
                                <h3 class="mb-0" id="messages-today-value">--</h3>
                            </div>
                            <div class="icon-box bg-success-light rounded-circle">
                                <i class="fas fa-paper-plane text-success fa-lg"></i>
                            </div>
                        </div>
                        <div class="mt-3">
                            <span class="text-muted small" id="messages-today-subtext">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
                <div class="card" id="tile-delivery-rate">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="mb-1 text-muted">Delivery Rate</p>
                                <h3 class="mb-0" id="delivery-rate-value">--%</h3>
                            </div>
                            <div class="icon-box bg-info-light rounded-circle">
                                <i class="fas fa-check-double text-info fa-lg"></i>
                            </div>
                        </div>
                        <div class="mt-3">
                            <span class="text-muted small" id="delivery-rate-subtext">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
                <div class="card" id="tile-inbox-unread">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="mb-1 text-muted">Inbox Unread</p>
                                <h3 class="mb-0" id="inbox-unread-value">--</h3>
                            </div>
                            <div class="icon-box bg-warning-light rounded-circle">
                                <i class="fas fa-envelope text-warning fa-lg"></i>
                            </div>
                        </div>
                        <div class="mt-3">
                            <span class="text-muted small" id="inbox-unread-subtext">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-xl-8 col-lg-7">
                <div class="card" id="tile-message-activity">
                    <div class="card-header border-0 pb-0">
                        <h5 class="card-title mb-0">Message Activity (7 Days)</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-center" style="height: 250px; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 0.5rem;">
                            <div class="text-center text-muted">
                                <i class="fas fa-chart-area fa-3x mb-2 opacity-50"></i>
                                <p class="mb-0">Chart placeholder</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-lg-5">
                <div class="card" id="tile-channel-breakdown">
                    <div class="card-header border-0 pb-0">
                        <h5 class="card-title mb-0">Channel Breakdown</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-center" style="height: 250px; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 0.5rem;">
                            <div class="text-center text-muted">
                                <i class="fas fa-chart-pie fa-3x mb-2 opacity-50"></i>
                                <p class="mb-0">Chart placeholder</p>
                            </div>
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
.bg-primary-light {
    background-color: rgba(var(--primary-rgb, 124, 93, 250), 0.1);
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
#operationalOverview .card,
#rcsPromotion .card,
#supportNotifications .card {
    transition: box-shadow 0.2s ease;
}
#operationalOverview .card:hover,
#rcsPromotion .card:hover,
#supportNotifications .card:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
}
</style>
@endpush
@endsection
