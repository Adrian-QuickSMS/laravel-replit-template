@extends('layouts.quicksms')

@section('title', 'Campaign History')

@push('styles')
<style>
#campaignsTable tbody tr {
    cursor: pointer;
}
#campaignsTable tbody tr:hover {
    background-color: rgba(111, 66, 193, 0.05);
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row page-titles">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('messages') }}">Messages</a></li>
            <li class="breadcrumb-item active">Campaign History</li>
        </ol>
    </div>
    
    <div class="row align-items-start">
        <div class="col-12">
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Campaign History</h5>
                    <a href="{{ route('messages.send') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i> Create Campaign
                    </a>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <div class="d-flex gap-2">
                            <div class="input-group flex-grow-1">
                                <span class="input-group-text bg-transparent"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control" id="campaignSearch" placeholder="Search by name, sender ID, agent, tags, or template...">
                                <button class="btn btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#filtersPanel">
                                    <i class="fas fa-filter me-1"></i> Filters
                                    <span class="badge bg-primary ms-1 d-none" id="activeFiltersBadge">0</span>
                                </button>
                            </div>
                            <div class="input-group" style="width: auto;">
                                <span class="input-group-text bg-transparent"><i class="fas fa-sort"></i></span>
                                <select class="form-select" id="sortSelect" style="min-width: 180px;" onchange="sortCampaigns()">
                                    <option value="recent">Most recent first</option>
                                    <option value="oldest">Oldest first</option>
                                    <option value="recipients">Highest recipients</option>
                                    <option value="failure">Highest failure rate</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="collapse mb-3" id="filtersPanel">
                        <div class="card card-body bg-light border">
                            <div class="row g-3">
                                <div class="col-md-4 col-lg-3">
                                    <label class="form-label small text-muted">Status</label>
                                    <select class="form-select form-select-sm" id="filterStatus">
                                        <option value="">All Statuses</option>
                                        <option value="scheduled">Scheduled</option>
                                        <option value="sending">Sending</option>
                                        <option value="complete">Complete</option>
                                    </select>
                                </div>
                                <div class="col-md-4 col-lg-3">
                                    <label class="form-label small text-muted">Channel</label>
                                    <select class="form-select form-select-sm" id="filterChannel">
                                        <option value="">All Channels</option>
                                        <option value="sms_only">SMS</option>
                                        <option value="basic_rcs">Basic RCS</option>
                                        <option value="rich_rcs">Rich RCS</option>
                                    </select>
                                </div>
                                <div class="col-md-4 col-lg-3">
                                    <label class="form-label small text-muted">Sender ID</label>
                                    <select class="form-select form-select-sm" id="filterSenderId">
                                        <option value="">All Sender IDs</option>
                                        @php
                                            $senderIds = collect($campaigns)->pluck('sender_id')->unique()->sort();
                                        @endphp
                                        @foreach($senderIds as $sid)
                                            <option value="{{ $sid }}">{{ $sid }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 col-lg-3">
                                    <label class="form-label small text-muted">RCS Agent</label>
                                    <select class="form-select form-select-sm" id="filterRcsAgent">
                                        <option value="">All Agents</option>
                                        @php
                                            $agents = collect($campaigns)->pluck('rcs_agent')->filter()->unique()->sort();
                                        @endphp
                                        @foreach($agents as $agent)
                                            <option value="{{ $agent }}">{{ $agent }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 col-lg-3">
                                    <label class="form-label small text-muted">Date From</label>
                                    <input type="date" class="form-control form-control-sm" id="filterDateFrom">
                                </div>
                                <div class="col-md-4 col-lg-3">
                                    <label class="form-label small text-muted">Date To</label>
                                    <input type="date" class="form-control form-control-sm" id="filterDateTo">
                                </div>
                                <div class="col-md-4 col-lg-3">
                                    <label class="form-label small text-muted">Has Tracking Link</label>
                                    <select class="form-select form-select-sm" id="filterTracking">
                                        <option value="">Any</option>
                                        <option value="yes">Yes</option>
                                        <option value="no">No</option>
                                    </select>
                                </div>
                                <div class="col-md-4 col-lg-3">
                                    <label class="form-label small text-muted">Has Opt-Out</label>
                                    <select class="form-select form-select-sm" id="filterOptout">
                                        <option value="">Any</option>
                                        <option value="yes">Yes</option>
                                        <option value="no">No</option>
                                    </select>
                                </div>
                            </div>
                            <div class="d-flex gap-2 mt-3 pt-3 border-top">
                                <button type="button" class="btn btn-primary btn-sm" onclick="applyFilters()">
                                    <i class="fas fa-check me-1"></i> Apply Filters
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="resetFilters()">
                                    <i class="fas fa-undo me-1"></i> Reset
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="campaignsTable">
                            <thead>
                                <tr>
                                    <th>Campaign Name</th>
                                    <th>Channel</th>
                                    <th>Status</th>
                                    <th>Recipients</th>
                                    <th>Send Date</th>
                                </tr>
                            </thead>
                            <tbody id="campaignsTableBody">
                                @forelse($campaigns as $campaign)
                                <tr onclick="openCampaignDrawer('{{ $campaign['id'] }}')" 
                                    data-id="{{ $campaign['id'] }}"
                                    data-name="{{ $campaign['name'] }}"
                                    data-channel="{{ $campaign['channel'] }}"
                                    data-status="{{ $campaign['status'] }}"
                                    data-recipients-total="{{ $campaign['recipients_total'] }}"
                                    data-recipients-delivered="{{ $campaign['recipients_delivered'] ?? '' }}"
                                    data-send-date="{{ $campaign['send_date'] }}"
                                    data-sender-id="{{ $campaign['sender_id'] }}"
                                    data-rcs-agent="{{ $campaign['rcs_agent'] ?? '' }}"
                                    data-tags="{{ implode(',', $campaign['tags'] ?? []) }}"
                                    data-template="{{ $campaign['template'] ?? '' }}"
                                    data-has-tracking="{{ $campaign['has_tracking'] ? 'yes' : 'no' }}"
                                    data-has-optout="{{ $campaign['has_optout'] ? 'yes' : 'no' }}">
                                    <td class="fw-medium">{{ $campaign['name'] }}</td>
                                    <td>
                                        @if($campaign['channel'] === 'sms_only')
                                            <span class="badge bg-secondary">SMS</span>
                                        @elseif($campaign['channel'] === 'basic_rcs')
                                            <span class="badge bg-success">Basic RCS</span>
                                        @else
                                            <span class="badge bg-primary">Rich RCS</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($campaign['status'] === 'scheduled')
                                            <span class="badge bg-info">Scheduled</span>
                                        @elseif($campaign['status'] === 'sending')
                                            <span class="badge bg-warning text-dark">Sending</span>
                                        @else
                                            <span class="badge bg-success">Complete</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($campaign['recipients_delivered'] !== null)
                                            {{ number_format($campaign['recipients_delivered']) }}/{{ number_format($campaign['recipients_total']) }}
                                        @else
                                            {{ number_format($campaign['recipients_total']) }}
                                        @endif
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($campaign['send_date'])->format('d/m/Y H:i') }}</td>
                                </tr>
                                @empty
                                <tr id="emptyStateRow">
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="fas fa-inbox fa-3x mb-3 d-block opacity-25"></i>
                                        <p class="mb-2">No campaigns to display yet.</p>
                                        <a href="{{ route('messages.send') }}" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-plus me-1"></i> Create your first campaign
                                        </a>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                        <div id="noResultsState" class="text-center py-5 text-muted d-none">
                            <i class="fas fa-search fa-3x mb-3 d-block opacity-25"></i>
                            <p class="mb-2">No campaigns match your search.</p>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="clearSearch()">
                                <i class="fas fa-times me-1"></i> Clear search
                            </button>
                        </div>
                    </div>
                    <div class="mt-3" id="resultsCount">
                        <small class="text-muted">Showing <span id="visibleCount">{{ count($campaigns) }}</span> of {{ count($campaigns) }} campaign(s)</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="campaignDrawer" style="width: 480px;">
    <div class="offcanvas-header border-bottom py-2">
        <h6 class="offcanvas-title text-muted mb-0"><i class="fas fa-bullhorn me-2"></i>Campaign Overview</h6>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-0">
        <div class="bg-gradient-primary text-white p-4" style="background: linear-gradient(135deg, #6f42c1 0%, #5a32a3 100%);">
            <h4 id="drawerCampaignName" class="mb-3 fw-semibold">-</h4>
            <div class="d-flex flex-wrap gap-2 mb-3">
                <span id="drawerStatusBadge" class="badge bg-white text-dark">-</span>
                <span id="drawerChannelBadge" class="badge bg-white bg-opacity-25">-</span>
                <span id="drawerLiveStateBadge" class="badge bg-white bg-opacity-25">-</span>
            </div>
            <div class="d-flex align-items-center small opacity-75">
                <i class="fas fa-clock me-2"></i>
                <span id="drawerSendTimeLabel">Send Time:</span>
                <span class="ms-1 fw-medium" id="drawerSendTime">-</span>
            </div>
            
            <div class="mt-3" id="statusActionsContainer">
                <div id="scheduledActions" style="display: none;">
                    <div class="d-flex gap-2 mb-2">
                        <a href="#" id="editCampaignBtn" class="btn btn-light btn-sm flex-fill" onclick="editCampaign(event)">
                            <i class="fas fa-edit me-1"></i>Edit Campaign
                        </a>
                        <button type="button" class="btn btn-outline-light btn-sm flex-fill" onclick="showCancelConfirmation()">
                            <i class="fas fa-times-circle me-1"></i>Cancel
                        </button>
                    </div>
                </div>
                <div id="sendingNotice" style="display: none;">
                    <div class="alert alert-light mb-2 py-2 px-3 small d-flex align-items-center" style="background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.3);">
                        <i class="fas fa-spinner fa-spin me-2"></i>
                        <span>Campaign is currently sending and cannot be cancelled.</span>
                    </div>
                </div>
                <div id="duplicateAction">
                    <button type="button" class="btn btn-outline-light btn-sm w-100" onclick="duplicateCampaign()">
                        <i class="fas fa-copy me-1"></i>Duplicate Campaign
                    </button>
                </div>
            </div>
        </div>

        <div class="p-4">
            <div class="row g-3 mb-4">
                <div class="col-6">
                    <div class="border rounded p-3 text-center h-100">
                        <div class="text-muted small mb-1">Total Recipients</div>
                        <div class="fs-4 fw-bold text-primary" id="drawerRecipientsTotal">-</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="border rounded p-3 text-center h-100">
                        <div class="text-muted small mb-1">Delivered</div>
                        <div class="fs-4 fw-bold text-success" id="drawerRecipientsDelivered">-</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="border rounded p-3 text-center h-100">
                        <div class="text-muted small mb-1">Failed</div>
                        <div class="fs-4 fw-bold text-danger" id="drawerRecipientsFailed">-</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="border rounded p-3 text-center h-100">
                        <div class="text-muted small mb-1">Delivery Rate</div>
                        <div class="fs-4 fw-bold" id="drawerDeliveryRate">-</div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body p-3">
                    <h6 class="text-muted mb-3"><i class="fas fa-info-circle me-2"></i>Campaign Details</h6>
                    <div class="row mb-2">
                        <div class="col-5 text-muted small">Campaign ID</div>
                        <div class="col-7 small" id="drawerCampaignId">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5 text-muted small">Sender ID</div>
                        <div class="col-7 small" id="drawerSenderId">-</div>
                    </div>
                    <div class="row mb-2" id="drawerRcsAgentRow">
                        <div class="col-5 text-muted small">RCS Agent</div>
                        <div class="col-7 small" id="drawerRcsAgent">-</div>
                    </div>
                    <div class="row mb-2" id="drawerTemplateRow">
                        <div class="col-5 text-muted small">Template</div>
                        <div class="col-7 small" id="drawerTemplate">-</div>
                    </div>
                    <div class="row">
                        <div class="col-5 text-muted small">Tags</div>
                        <div class="col-7 small" id="drawerTags">-</div>
                    </div>
                </div>
            </div>

            <div class="card mb-3" id="channelSplitCard">
                <div class="card-body p-3">
                    <h6 class="text-muted mb-3"><i class="fas fa-random me-2"></i>Channel Split</h6>
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="border rounded p-3 text-center">
                                <div class="d-flex align-items-center justify-content-center mb-2">
                                    <i class="fas fa-sms text-secondary me-2"></i>
                                    <span class="small fw-medium">SMS</span>
                                </div>
                                <div class="fs-4 fw-bold" id="channelSmsPercent">-</div>
                                <div class="small text-muted" id="channelSmsCount">-</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-3 text-center">
                                <div class="d-flex align-items-center justify-content-center mb-2">
                                    <i class="fas fa-comment-dots text-primary me-2"></i>
                                    <span class="small fw-medium">RCS</span>
                                </div>
                                <div class="fs-4 fw-bold" id="channelRcsPercent">-</div>
                                <div class="small text-muted" id="channelRcsCount">-</div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="d-flex rounded overflow-hidden" style="height: 8px;">
                            <div id="channelBarSms" class="bg-secondary" style="width: 0%; transition: width 0.3s;"></div>
                            <div id="channelBarRcs" class="bg-primary" style="width: 0%; transition: width 0.3s;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3" id="deliveryOutcomesCard">
                <div class="card-body p-3">
                    <h6 class="text-muted mb-3"><i class="fas fa-chart-pie me-2"></i>Delivery Outcomes</h6>
                    
                    <div class="mb-3">
                        <div class="d-flex rounded overflow-hidden" style="height: 24px;" id="outcomeBar">
                            <div id="barDelivered" class="bg-success" style="width: 0%; transition: width 0.3s;"></div>
                            <div id="barPending" class="bg-warning" style="width: 0%; transition: width 0.3s;"></div>
                            <div id="barUndeliverable" class="bg-danger" style="width: 0%; transition: width 0.3s;"></div>
                        </div>
                    </div>
                    
                    <div class="row g-2 text-center">
                        <div class="col-4">
                            <div class="d-flex align-items-center justify-content-center mb-1">
                                <span class="rounded-circle bg-success me-2" style="width: 10px; height: 10px; display: inline-block;"></span>
                                <span class="small text-muted">Delivered</span>
                            </div>
                            <div class="fw-bold" id="outcomeDelivered">-</div>
                            <div class="small text-muted" id="outcomeDeliveredPct">-</div>
                        </div>
                        <div class="col-4">
                            <div class="d-flex align-items-center justify-content-center mb-1">
                                <span class="rounded-circle bg-warning me-2" style="width: 10px; height: 10px; display: inline-block;"></span>
                                <span class="small text-muted">Pending</span>
                            </div>
                            <div class="fw-bold" id="outcomePending">-</div>
                            <div class="small text-muted" id="outcomePendingPct">-</div>
                        </div>
                        <div class="col-4">
                            <div class="d-flex align-items-center justify-content-center mb-1">
                                <span class="rounded-circle bg-danger me-2" style="width: 10px; height: 10px; display: inline-block;"></span>
                                <span class="small text-muted">Undeliverable</span>
                            </div>
                            <div class="fw-bold" id="outcomeUndeliverable">-</div>
                            <div class="small text-muted" id="outcomeUndeliverablePct">-</div>
                        </div>
                    </div>
                    
                    <div class="mt-2 pt-2 border-top">
                        <small class="text-muted"><i class="fas fa-info-circle me-1"></i>TODO: Integrate Chart.js pie chart for richer visualization</small>
                    </div>
                </div>
            </div>

            <div class="card mb-3" id="engagementMetricsCard" style="display: none;">
                <div class="card-body p-3">
                    <h6 class="text-muted mb-3"><i class="fas fa-mouse-pointer me-2"></i>Engagement Metrics</h6>
                    
                    <div id="trackingMetrics" style="display: none;">
                        <div class="row g-2 mb-3">
                            <div class="col-4">
                                <div class="border rounded p-2 text-center">
                                    <div class="small text-muted mb-1">Total Clicks</div>
                                    <div class="fs-5 fw-bold text-primary" id="metricTotalClicks">-</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="border rounded p-2 text-center">
                                    <div class="small text-muted mb-1">Unique Clicks</div>
                                    <div class="fs-5 fw-bold text-info" id="metricUniqueClicks">-</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="border rounded p-2 text-center">
                                    <div class="small text-muted mb-1">CTR</div>
                                    <div class="fs-5 fw-bold text-success" id="metricCtr">-</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div id="rcsSeenMetrics" style="display: none;">
                        <div class="border rounded p-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-eye text-primary me-2"></i>
                                    <span class="small">RCS Seen Rate</span>
                                </div>
                                <div class="text-end">
                                    <span class="fs-5 fw-bold text-primary" id="metricSeenPercent">-</span>
                                    <div class="small text-muted" id="metricSeenCount">-</div>
                                </div>
                            </div>
                            <div class="progress mt-2" style="height: 6px;">
                                <div class="progress-bar bg-primary" id="metricSeenBar" role="progressbar" style="width: 0%; transition: width 0.3s;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3" id="costSummaryCard" style="display: none;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="text-muted mb-0"><i class="fas fa-pound-sign me-2"></i><span id="costLabel">Cost Summary</span></h6>
                        <span class="badge" id="costStatusBadge">-</span>
                    </div>
                    
                    <div id="smsCostSection" style="display: none;">
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <span class="text-muted small">SMS Messages</span>
                            <span class="small" id="smsCostCount">-</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <span class="text-muted small">Unit Price</span>
                            <span class="small" id="smsCostUnit">-</span>
                        </div>
                    </div>
                    
                    <div id="rcsCostSection" style="display: none;">
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <span class="text-muted small">SMS Fallback</span>
                            <div class="text-end">
                                <span class="small" id="rcsFallbackCount">-</span>
                                <span class="small text-muted ms-2" id="rcsFallbackCost">-</span>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <span class="text-muted small">RCS Messages</span>
                            <div class="text-end">
                                <span class="small" id="rcsMessageCount">-</span>
                                <span class="small text-muted ms-2" id="rcsMessageCost">-</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center pt-3">
                        <span class="fw-medium" id="costTotalLabel">Total</span>
                        <span class="fs-5 fw-bold text-primary" id="costTotal">-</span>
                    </div>
                    
                    <div class="mt-2 small text-muted" id="costDisclaimer" style="display: none;">
                        <i class="fas fa-info-circle me-1"></i>
                        <span id="costDisclaimerText">-</span>
                    </div>
                </div>
            </div>

            <div class="card mb-3" id="recipientBreakdownCard">
                <div class="card-header p-3 bg-white border-bottom-0" style="cursor: pointer;" onclick="toggleRecipientBreakdown()">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="text-muted mb-0"><i class="fas fa-users me-2"></i>Recipient Breakdown</h6>
                        <i class="fas fa-chevron-down text-muted" id="recipientBreakdownChevron" style="transition: transform 0.2s;"></i>
                    </div>
                </div>
                <div class="card-body p-3 pt-0" id="recipientBreakdownBody" style="display: none;">
                    <h6 class="small text-muted mb-2 mt-2">Sources Used</h6>
                    <div class="d-flex flex-wrap gap-2 mb-3" id="recipientSourcesContainer">
                    </div>
                    
                    <h6 class="small text-muted mb-2">De-duplication Summary</h6>
                    <div class="border rounded p-3 bg-light">
                        <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                            <span class="small text-muted">Total Selected</span>
                            <span class="small fw-medium" id="dedupTotalSelected">-</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                            <span class="small text-success">
                                <i class="fas fa-check-circle me-1"></i>Unique Sent
                            </span>
                            <span class="small fw-bold text-success" id="dedupUniqueSent">-</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                            <span class="small text-warning">
                                <i class="fas fa-exclamation-triangle me-1"></i>Excluded (Invalid)
                            </span>
                            <span class="small fw-medium text-warning" id="dedupExcludedInvalid">-</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-1">
                            <span class="small text-danger">
                                <i class="fas fa-ban me-1"></i>Excluded (Opted-out)
                            </span>
                            <span class="small fw-medium text-danger" id="dedupExcludedOptout">-</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3" id="optoutSummaryCard" style="display: none;">
                <div class="card-body p-3">
                    <h6 class="text-muted mb-3"><i class="fas fa-user-slash me-2"></i>Compliance & Opt-outs</h6>
                    
                    <div class="border rounded p-3 mb-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-muted small">Total Opted Out</span>
                            <span class="fs-5 fw-bold text-danger" id="optoutTotal">-</span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-danger" id="optoutBar" role="progressbar" style="width: 0%; transition: width 0.3s;"></div>
                        </div>
                        <div class="small text-muted mt-1" id="optoutPercent">-</div>
                    </div>
                    
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <div class="border rounded p-2 text-center">
                                <div class="d-flex align-items-center justify-content-center mb-1">
                                    <i class="fas fa-reply text-secondary me-1" style="font-size: 0.75rem;"></i>
                                    <span class="small text-muted">Reply STOP</span>
                                </div>
                                <div class="fw-bold" id="optoutReplyStop">-</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-2 text-center">
                                <div class="d-flex align-items-center justify-content-center mb-1">
                                    <i class="fas fa-link text-secondary me-1" style="font-size: 0.75rem;"></i>
                                    <span class="small text-muted">Opt-out URL</span>
                                </div>
                                <div class="fw-bold" id="optoutUrl">-</div>
                            </div>
                        </div>
                    </div>
                    
                    <a href="#" class="btn btn-outline-secondary btn-sm w-100" onclick="event.preventDefault(); alert('TODO: Implement opt-out export for this campaign');">
                        <i class="fas fa-download me-1"></i> View/Export Opt-out Records
                    </a>
                </div>
            </div>

            <div class="card mb-3" id="messagePreviewCard">
                <div class="card-body p-3">
                    <h6 class="text-muted mb-3"><i class="fas fa-eye me-2"></i>Message Preview</h6>
                    
                    <div id="smsPreviewSection" style="display: none;">
                        <div class="border rounded p-3 bg-light" style="max-width: 280px; margin: 0 auto;">
                            <div class="d-flex align-items-center mb-2">
                                <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center me-2" style="width: 28px; height: 28px;">
                                    <i class="fas fa-sms text-white" style="font-size: 12px;"></i>
                                </div>
                                <span class="small fw-medium" id="smsPreviewSender">-</span>
                            </div>
                            <div class="bg-white rounded p-2 border" style="font-size: 13px; line-height: 1.4;">
                                <span id="smsPreviewContent">-</span>
                            </div>
                        </div>
                    </div>
                    
                    <div id="rcsPreviewSection" style="display: none;">
                        <div class="border rounded overflow-hidden" style="max-width: 280px; margin: 0 auto; background: #f5f5f5;">
                            <div class="bg-primary text-white p-2 d-flex align-items-center">
                                <div class="rounded-circle bg-white d-flex align-items-center justify-content-center me-2" style="width: 24px; height: 24px;">
                                    <i class="fas fa-building text-primary" style="font-size: 10px;"></i>
                                </div>
                                <div>
                                    <div class="small fw-medium" id="rcsPreviewAgent">-</div>
                                    <div style="font-size: 10px; opacity: 0.8;">Verified Business</div>
                                </div>
                            </div>
                            
                            <div class="p-2" id="rcsPreviewBasic" style="display: none;">
                                <div class="bg-white rounded p-2 border" style="font-size: 13px; line-height: 1.4;">
                                    <span id="rcsPreviewBasicContent">-</span>
                                </div>
                            </div>
                            
                            <div class="p-2" id="rcsPreviewRich" style="display: none;">
                                <div class="bg-white rounded overflow-hidden border">
                                    <div class="bg-secondary" style="height: 100px; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-image text-white fa-2x opacity-50"></i>
                                    </div>
                                    <div class="p-2">
                                        <div class="fw-medium small" id="rcsCardTitle">-</div>
                                        <div class="text-muted" style="font-size: 11px;" id="rcsCardDesc">-</div>
                                        <div class="mt-2" id="rcsCardButtons"></div>
                                    </div>
                                </div>
                                <div class="text-center mt-2" id="rcsCarouselIndicator" style="display: none;">
                                    <span class="badge bg-primary me-1" style="width: 8px; height: 8px; padding: 0; border-radius: 50%;"></span>
                                    <span class="badge bg-secondary me-1" style="width: 6px; height: 6px; padding: 0; border-radius: 50%; opacity: 0.5;"></span>
                                    <span class="badge bg-secondary" style="width: 6px; height: 6px; padding: 0; border-radius: 50%; opacity: 0.5;"></span>
                                    <div class="small text-muted mt-1" id="rcsCarouselCount">-</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center mt-2">
                        <small class="text-muted"><i class="fas fa-shield-alt me-1"></i>Placeholders shown for privacy</small>
                    </div>
                </div>
            </div>

            <div class="card mb-3" id="logsExportsCard">
                <div class="card-body p-3">
                    <h6 class="text-muted mb-3"><i class="fas fa-file-export me-2"></i>Logs & Exports</h6>
                    
                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-primary btn-sm text-start" onclick="exportDeliveryReport()">
                            <i class="fas fa-file-csv me-2"></i>Export Delivery Report
                            <span class="float-end text-muted small">CSV / Excel</span>
                        </button>
                        
                        <button class="btn btn-outline-primary btn-sm text-start" onclick="exportMessageLog()">
                            <i class="fas fa-list-alt me-2"></i>Export Message Log
                            <span class="float-end text-muted small">Per recipient</span>
                        </button>
                        
                        <button class="btn btn-outline-secondary btn-sm text-start" onclick="viewAuditLog()">
                            <i class="fas fa-history me-2"></i>View Audit Log
                            <span class="float-end text-muted small">Activity trail</span>
                        </button>
                    </div>
                    
                    <div class="small text-muted mt-2">
                        <i class="fas fa-lock me-1"></i>Export access based on user permissions
                    </div>
                </div>
            </div>

            <div class="card bg-light border-0">
                <div class="card-body p-3 text-center text-muted">
                    <i class="fas fa-chart-line fa-2x mb-2 opacity-50"></i>
                    <p class="mb-0 small">Detailed analytics coming soon</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="comingSoonModal" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <i class="fas fa-tools fa-3x text-primary mb-3"></i>
                <h5 class="mb-2">Coming Soon</h5>
                <p class="text-muted small mb-3" id="comingSoonMessage">This feature is under development.</p>
                <button type="button" class="btn btn-primary btn-sm" data-bs-dismiss="modal">Got it</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="cancelCampaignModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle text-warning me-2"></i>Cancel Campaign</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">Are you sure you want to cancel this scheduled campaign?</p>
                <div class="alert alert-light border mb-0">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-bullhorn text-muted me-2"></i>
                        <div>
                            <strong id="cancelCampaignName">-</strong>
                            <div class="small text-muted">Scheduled for <span id="cancelCampaignTime">-</span></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Keep Campaign</button>
                <button type="button" class="btn btn-danger" onclick="confirmCancelCampaign()">
                    <i class="fas fa-times-circle me-1"></i>Cancel Campaign
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
var campaignDrawer = null;
var totalCampaigns = {{ count($campaigns) }};

document.addEventListener('DOMContentLoaded', function() {
    campaignDrawer = new bootstrap.Offcanvas(document.getElementById('campaignDrawer'));
    
    var searchInput = document.getElementById('campaignSearch');
    searchInput.addEventListener('input', filterCampaigns);
    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            filterCampaigns();
        }
    });
});

function filterCampaigns() {
    var searchTerm = document.getElementById('campaignSearch').value.toLowerCase().trim();
    var rows = document.querySelectorAll('#campaignsTableBody tr[data-id]');
    var visibleCount = 0;
    var hasActiveFilters = Object.values(activeFilters).some(function(v) { return v !== ''; });
    
    rows.forEach(function(row) {
        var name = (row.dataset.name || '').toLowerCase();
        var senderId = (row.dataset.senderId || '');
        var rcsAgent = (row.dataset.rcsAgent || '');
        var tags = (row.dataset.tags || '').toLowerCase();
        var template = (row.dataset.template || '').toLowerCase();
        var channel = (row.dataset.channel || '');
        var status = (row.dataset.status || '');
        var sendDate = row.dataset.sendDate || '';
        var hasTracking = row.dataset.hasTracking || '';
        var hasOptout = row.dataset.hasOptout || '';
        
        var searchable = name + ' ' + senderId.toLowerCase() + ' ' + rcsAgent.toLowerCase() + ' ' + tags + ' ' + template + ' ' + channel.toLowerCase().replace('_', ' ') + ' ' + status.toLowerCase();
        var matchesSearch = searchTerm === '' || searchable.includes(searchTerm);
        
        var matchesFilters = true;
        if (hasActiveFilters) {
            if (activeFilters.status && status !== activeFilters.status) matchesFilters = false;
            if (activeFilters.channel && channel !== activeFilters.channel) matchesFilters = false;
            if (activeFilters.senderId && senderId !== activeFilters.senderId) matchesFilters = false;
            if (activeFilters.rcsAgent && rcsAgent !== activeFilters.rcsAgent) matchesFilters = false;
            if (activeFilters.tracking && hasTracking !== activeFilters.tracking) matchesFilters = false;
            if (activeFilters.optout && hasOptout !== activeFilters.optout) matchesFilters = false;
            
            if (activeFilters.dateFrom) {
                var rowDate = new Date(sendDate);
                var fromDate = new Date(activeFilters.dateFrom);
                if (rowDate < fromDate) matchesFilters = false;
            }
            if (activeFilters.dateTo) {
                var rowDate = new Date(sendDate);
                var toDate = new Date(activeFilters.dateTo);
                toDate.setHours(23, 59, 59);
                if (rowDate > toDate) matchesFilters = false;
            }
        }
        
        if (matchesSearch && matchesFilters) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
    
    document.getElementById('visibleCount').textContent = visibleCount;
    
    var noResultsState = document.getElementById('noResultsState');
    var table = document.getElementById('campaignsTable');
    
    if (visibleCount === 0 && (searchTerm !== '' || hasActiveFilters)) {
        noResultsState.classList.remove('d-none');
        table.classList.add('d-none');
    } else {
        noResultsState.classList.add('d-none');
        table.classList.remove('d-none');
    }
}

function clearSearch() {
    document.getElementById('campaignSearch').value = '';
    filterCampaigns();
}

var activeFilters = {};

function applyFilters() {
    activeFilters = {
        status: document.getElementById('filterStatus').value,
        channel: document.getElementById('filterChannel').value,
        senderId: document.getElementById('filterSenderId').value,
        rcsAgent: document.getElementById('filterRcsAgent').value,
        dateFrom: document.getElementById('filterDateFrom').value,
        dateTo: document.getElementById('filterDateTo').value,
        tracking: document.getElementById('filterTracking').value,
        optout: document.getElementById('filterOptout').value
    };
    
    updateFilterBadge();
    filterCampaigns();
}

function resetFilters() {
    document.getElementById('filterStatus').value = '';
    document.getElementById('filterChannel').value = '';
    document.getElementById('filterSenderId').value = '';
    document.getElementById('filterRcsAgent').value = '';
    document.getElementById('filterDateFrom').value = '';
    document.getElementById('filterDateTo').value = '';
    document.getElementById('filterTracking').value = '';
    document.getElementById('filterOptout').value = '';
    
    activeFilters = {};
    updateFilterBadge();
    filterCampaigns();
}

function updateFilterBadge() {
    var count = Object.values(activeFilters).filter(function(v) { return v !== ''; }).length;
    var badge = document.getElementById('activeFiltersBadge');
    
    if (count > 0) {
        badge.textContent = count;
        badge.classList.remove('d-none');
    } else {
        badge.classList.add('d-none');
    }
}

function sortCampaigns() {
    var sortBy = document.getElementById('sortSelect').value;
    var tbody = document.getElementById('campaignsTableBody');
    var rows = Array.from(tbody.querySelectorAll('tr[data-id]'));
    
    rows.sort(function(a, b) {
        if (sortBy === 'recent') {
            var dateA = new Date(a.dataset.sendDate);
            var dateB = new Date(b.dataset.sendDate);
            return dateB - dateA;
        } else if (sortBy === 'oldest') {
            var dateA = new Date(a.dataset.sendDate);
            var dateB = new Date(b.dataset.sendDate);
            return dateA - dateB;
        } else if (sortBy === 'recipients') {
            var recipA = parseInt(a.dataset.recipientsTotal) || 0;
            var recipB = parseInt(b.dataset.recipientsTotal) || 0;
            return recipB - recipA;
        } else if (sortBy === 'failure') {
            var totalA = parseInt(a.dataset.recipientsTotal) || 0;
            var deliveredA = parseInt(a.dataset.recipientsDelivered) || totalA;
            var failureRateA = totalA > 0 ? (totalA - deliveredA) / totalA : 0;
            
            var totalB = parseInt(b.dataset.recipientsTotal) || 0;
            var deliveredB = parseInt(b.dataset.recipientsDelivered) || totalB;
            var failureRateB = totalB > 0 ? (totalB - deliveredB) / totalB : 0;
            
            return failureRateB - failureRateA;
        }
        return 0;
    });
    
    rows.forEach(function(row) {
        tbody.appendChild(row);
    });
    
    filterCampaigns();
}

function openCampaignDrawer(campaignId) {
    var row = document.querySelector('tr[data-id="' + campaignId + '"]');
    if (!row) return;

    var name = row.dataset.name;
    var channel = row.dataset.channel;
    var status = row.dataset.status;
    var recipientsTotal = parseInt(row.dataset.recipientsTotal) || 0;
    var recipientsDelivered = row.dataset.recipientsDelivered ? parseInt(row.dataset.recipientsDelivered) : null;
    var sendDate = row.dataset.sendDate;
    var senderId = row.dataset.senderId || '-';
    var rcsAgent = row.dataset.rcsAgent || '';
    var tags = row.dataset.tags || '';
    var template = row.dataset.template || '';

    document.getElementById('drawerCampaignName').textContent = name;
    document.getElementById('drawerCampaignId').textContent = campaignId;
    document.getElementById('drawerRecipientsTotal').textContent = recipientsTotal.toLocaleString();
    document.getElementById('drawerSenderId').textContent = senderId;

    var sendTimeLabel = document.getElementById('drawerSendTimeLabel');
    var sendTime = document.getElementById('drawerSendTime');
    if (status === 'scheduled') {
        sendTimeLabel.textContent = 'Scheduled:';
    } else {
        sendTimeLabel.textContent = 'Sent:';
    }
    sendTime.textContent = formatDate(sendDate);

    if (rcsAgent) {
        document.getElementById('drawerRcsAgentRow').style.display = '';
        document.getElementById('drawerRcsAgent').textContent = rcsAgent;
    } else {
        document.getElementById('drawerRcsAgentRow').style.display = 'none';
    }

    if (template) {
        document.getElementById('drawerTemplateRow').style.display = '';
        document.getElementById('drawerTemplate').textContent = template;
    } else {
        document.getElementById('drawerTemplateRow').style.display = 'none';
    }

    if (tags) {
        var tagArray = tags.split(',');
        var tagHtml = tagArray.map(function(t) {
            return '<span class="badge bg-light text-dark me-1">' + t.trim() + '</span>';
        }).join('');
        document.getElementById('drawerTags').innerHTML = tagHtml;
    } else {
        document.getElementById('drawerTags').textContent = '-';
    }

    var channelBadge = document.getElementById('drawerChannelBadge');
    channelBadge.className = 'badge bg-white bg-opacity-25';
    if (channel === 'sms_only') {
        channelBadge.textContent = 'SMS';
    } else if (channel === 'basic_rcs') {
        channelBadge.textContent = 'Basic RCS';
    } else {
        channelBadge.textContent = 'Rich RCS';
    }

    var statusBadge = document.getElementById('drawerStatusBadge');
    statusBadge.className = 'badge bg-white text-dark';
    if (status === 'scheduled') {
        statusBadge.innerHTML = '<i class="fas fa-calendar-alt me-1"></i>Scheduled';
    } else if (status === 'sending') {
        statusBadge.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Sending';
    } else {
        statusBadge.innerHTML = '<i class="fas fa-check me-1"></i>Complete';
    }

    // TODO: Replace with backend validity_window field
    var liveStateBadge = document.getElementById('drawerLiveStateBadge');
    liveStateBadge.className = 'badge bg-white bg-opacity-25';
    var campaignDate = new Date(sendDate);
    var now = new Date();
    var validityHours = 24;
    var expiryDate = new Date(campaignDate.getTime() + (validityHours * 60 * 60 * 1000));
    
    if (status === 'scheduled') {
        liveStateBadge.innerHTML = '<i class="fas fa-hourglass-start me-1"></i>Pending';
    } else if (status === 'sending') {
        if (now < expiryDate) {
            liveStateBadge.innerHTML = '<i class="fas fa-broadcast-tower me-1"></i>Live';
            liveStateBadge.className = 'badge bg-success';
        } else {
            liveStateBadge.innerHTML = '<i class="fas fa-clock me-1"></i>Expired';
            liveStateBadge.className = 'badge bg-secondary';
        }
    } else {
        if (recipientsDelivered !== null && recipientsDelivered >= recipientsTotal) {
            liveStateBadge.innerHTML = '<i class="fas fa-check-double me-1"></i>Complete';
        } else if (now > expiryDate) {
            liveStateBadge.innerHTML = '<i class="fas fa-clock me-1"></i>Expired';
        } else {
            liveStateBadge.innerHTML = '<i class="fas fa-check-double me-1"></i>Complete';
        }
    }

    var failed = 0;
    var deliveryRate = '-';
    var deliveredDisplay = document.getElementById('drawerRecipientsDelivered');
    var failedDisplay = document.getElementById('drawerRecipientsFailed');
    var rateDisplay = document.getElementById('drawerDeliveryRate');
    
    if (recipientsDelivered !== null) {
        failed = recipientsTotal - recipientsDelivered;
        deliveryRate = recipientsTotal > 0 ? ((recipientsDelivered / recipientsTotal) * 100).toFixed(1) + '%' : '-';
        deliveredDisplay.textContent = recipientsDelivered.toLocaleString();
        failedDisplay.textContent = failed.toLocaleString();
        rateDisplay.textContent = deliveryRate;
        rateDisplay.className = 'fs-4 fw-bold ' + (parseFloat(deliveryRate) >= 95 ? 'text-success' : parseFloat(deliveryRate) >= 80 ? 'text-warning' : 'text-danger');
    } else {
        deliveredDisplay.textContent = '-';
        failedDisplay.textContent = '-';
        rateDisplay.textContent = '-';
        rateDisplay.className = 'fs-4 fw-bold text-muted';
    }

    updateDeliveryOutcomes(status, recipientsTotal, recipientsDelivered);
    updateChannelSplit(channel, status, recipientsTotal, recipientsDelivered);
    
    var hasTracking = row.dataset.hasTracking === 'yes';
    var hasOptout = row.dataset.hasOptout === 'yes';
    updateEngagementMetrics(channel, status, recipientsTotal, recipientsDelivered, hasTracking);
    updateCostSummary(channel, status, recipientsTotal, recipientsDelivered);
    updateOptoutSummary(status, recipientsTotal, recipientsDelivered, hasOptout);
    updateMessagePreview(channel, senderId, rcsAgent, template);
    updateRecipientBreakdown(recipientsTotal, recipientsDelivered);
    updateStatusActions(status, row.dataset.id, name, sendDate);
    
    document.getElementById('recipientBreakdownBody').style.display = 'none';
    document.getElementById('recipientBreakdownChevron').style.transform = 'rotate(0deg)';

    campaignDrawer.show();
}

function updateDeliveryOutcomes(status, total, delivered) {
    var outcomesCard = document.getElementById('deliveryOutcomesCard');
    
    if (status === 'scheduled' || total === 0) {
        outcomesCard.style.display = 'none';
        return;
    }
    outcomesCard.style.display = '';
    
    // TODO: Replace with real pending/undeliverable data from backend
    var deliveredCount = delivered !== null ? delivered : 0;
    var pending = 0;
    var undeliverable = 0;
    
    if (status === 'sending') {
        pending = Math.floor((total - deliveredCount) * 0.7);
        undeliverable = Math.floor((total - deliveredCount) * 0.3);
    } else {
        pending = 0;
        undeliverable = total - deliveredCount;
    }
    
    var deliveredPct = total > 0 ? (deliveredCount / total * 100) : 0;
    var pendingPct = total > 0 ? (pending / total * 100) : 0;
    var undeliverablePct = total > 0 ? (undeliverable / total * 100) : 0;
    
    document.getElementById('barDelivered').style.width = deliveredPct + '%';
    document.getElementById('barPending').style.width = pendingPct + '%';
    document.getElementById('barUndeliverable').style.width = undeliverablePct + '%';
    
    document.getElementById('outcomeDelivered').textContent = deliveredCount.toLocaleString();
    document.getElementById('outcomeDeliveredPct').textContent = deliveredPct.toFixed(1) + '%';
    
    document.getElementById('outcomePending').textContent = pending.toLocaleString();
    document.getElementById('outcomePendingPct').textContent = pendingPct.toFixed(1) + '%';
    
    document.getElementById('outcomeUndeliverable').textContent = undeliverable.toLocaleString();
    document.getElementById('outcomeUndeliverablePct').textContent = undeliverablePct.toFixed(1) + '%';
}

function updateChannelSplit(channel, status, total, delivered) {
    var channelCard = document.getElementById('channelSplitCard');
    
    if (status === 'scheduled' || total === 0) {
        channelCard.style.display = 'none';
        return;
    }
    channelCard.style.display = '';
    
    var smsCount = 0;
    var rcsCount = 0;
    var deliveredCount = delivered !== null ? delivered : total;
    
    // TODO: Replace with real channel split data from backend
    if (channel === 'sms_only') {
        smsCount = deliveredCount;
        rcsCount = 0;
    } else if (channel === 'basic_rcs' || channel === 'rich_rcs') {
        // Mock: RCS campaigns typically have ~85% RCS success, 15% SMS fallback
        rcsCount = Math.floor(deliveredCount * 0.85);
        smsCount = deliveredCount - rcsCount;
    }
    
    var smsPct = deliveredCount > 0 ? (smsCount / deliveredCount * 100) : 0;
    var rcsPct = deliveredCount > 0 ? (rcsCount / deliveredCount * 100) : 0;
    
    document.getElementById('channelSmsPercent').textContent = smsPct.toFixed(1) + '%';
    document.getElementById('channelSmsCount').textContent = smsCount.toLocaleString() + ' msgs';
    
    document.getElementById('channelRcsPercent').textContent = rcsPct.toFixed(1) + '%';
    document.getElementById('channelRcsCount').textContent = rcsCount.toLocaleString() + ' msgs';
    
    document.getElementById('channelBarSms').style.width = smsPct + '%';
    document.getElementById('channelBarRcs').style.width = rcsPct + '%';
}

function updateEngagementMetrics(channel, status, total, delivered, hasTracking) {
    var engagementCard = document.getElementById('engagementMetricsCard');
    var trackingMetrics = document.getElementById('trackingMetrics');
    var rcsSeenMetrics = document.getElementById('rcsSeenMetrics');
    
    var isRcs = channel === 'basic_rcs' || channel === 'rich_rcs';
    var showSection = (hasTracking || isRcs) && status !== 'scheduled';
    
    if (!showSection) {
        engagementCard.style.display = 'none';
        return;
    }
    engagementCard.style.display = '';
    
    var deliveredCount = delivered !== null ? delivered : total;
    
    // TODO: Replace with real tracking data from backend
    if (hasTracking) {
        trackingMetrics.style.display = '';
        
        var uniqueClicks = Math.floor(deliveredCount * 0.12);
        var totalClicks = Math.floor(uniqueClicks * 1.4);
        var ctr = deliveredCount > 0 ? (uniqueClicks / deliveredCount * 100) : 0;
        
        document.getElementById('metricTotalClicks').textContent = totalClicks.toLocaleString();
        document.getElementById('metricUniqueClicks').textContent = uniqueClicks.toLocaleString();
        document.getElementById('metricCtr').textContent = ctr.toFixed(1) + '%';
    } else {
        trackingMetrics.style.display = 'none';
    }
    
    // TODO: Replace with real RCS read receipts from backend
    if (isRcs) {
        rcsSeenMetrics.style.display = '';
        
        var rcsDelivered = Math.floor(deliveredCount * 0.85);
        var seenCount = Math.floor(rcsDelivered * 0.72);
        var seenPct = rcsDelivered > 0 ? (seenCount / rcsDelivered * 100) : 0;
        
        document.getElementById('metricSeenPercent').textContent = seenPct.toFixed(1) + '%';
        document.getElementById('metricSeenCount').textContent = seenCount.toLocaleString() + ' of ' + rcsDelivered.toLocaleString() + ' RCS';
        document.getElementById('metricSeenBar').style.width = seenPct + '%';
    } else {
        rcsSeenMetrics.style.display = 'none';
    }
}

function updateCostSummary(channel, status, total, delivered) {
    var costCard = document.getElementById('costSummaryCard');
    
    if (status === 'scheduled') {
        costCard.style.display = 'none';
        return;
    }
    costCard.style.display = '';
    
    var isRcs = channel === 'basic_rcs' || channel === 'rich_rcs';
    var isComplete = status === 'complete';
    var deliveredCount = delivered !== null ? delivered : total;
    
    // TODO: Replace with real pricing from backend
    var smsUnitPrice = 0.038;
    var rcsUnitPrice = 0.025;
    
    var costLabel = document.getElementById('costLabel');
    var costStatusBadge = document.getElementById('costStatusBadge');
    var costTotalLabel = document.getElementById('costTotalLabel');
    var costDisclaimer = document.getElementById('costDisclaimer');
    var costDisclaimerText = document.getElementById('costDisclaimerText');
    var smsCostSection = document.getElementById('smsCostSection');
    var rcsCostSection = document.getElementById('rcsCostSection');
    
    if (isComplete) {
        costLabel.textContent = 'Final Cost';
        costStatusBadge.className = 'badge bg-success';
        costStatusBadge.textContent = 'Final';
        costTotalLabel.textContent = 'Total';
        costDisclaimer.style.display = 'none';
    } else {
        costLabel.textContent = 'Estimated Cost';
        costStatusBadge.className = 'badge bg-warning text-dark';
        costStatusBadge.textContent = 'Estimated';
        costTotalLabel.textContent = 'Est. Total';
        costDisclaimer.style.display = '';
        costDisclaimerText.textContent = 'Final cost will be calculated when delivery completes.';
    }
    
    var totalCost = 0;
    
    if (!isRcs) {
        smsCostSection.style.display = '';
        rcsCostSection.style.display = 'none';
        
        document.getElementById('smsCostCount').textContent = deliveredCount.toLocaleString() + ' msgs';
        document.getElementById('smsCostUnit').textContent = '£' + smsUnitPrice.toFixed(3);
        
        totalCost = deliveredCount * smsUnitPrice;
    } else {
        smsCostSection.style.display = 'none';
        rcsCostSection.style.display = '';
        
        // Mock: 15% SMS fallback, 85% RCS
        var smsFallbackCount = Math.floor(deliveredCount * 0.15);
        var rcsCount = deliveredCount - smsFallbackCount;
        
        var smsFallbackCost = smsFallbackCount * smsUnitPrice;
        var rcsCost = rcsCount * rcsUnitPrice;
        totalCost = smsFallbackCost + rcsCost;
        
        document.getElementById('rcsFallbackCount').textContent = smsFallbackCount.toLocaleString() + ' msgs';
        document.getElementById('rcsFallbackCost').textContent = '£' + smsFallbackCost.toFixed(2);
        
        document.getElementById('rcsMessageCount').textContent = rcsCount.toLocaleString() + ' msgs';
        document.getElementById('rcsMessageCost').textContent = '£' + rcsCost.toFixed(2);
    }
    
    document.getElementById('costTotal').textContent = '£' + totalCost.toFixed(2);
}

function updateOptoutSummary(status, total, delivered, hasOptout) {
    var optoutCard = document.getElementById('optoutSummaryCard');
    
    if (!hasOptout || status === 'scheduled') {
        optoutCard.style.display = 'none';
        return;
    }
    optoutCard.style.display = '';
    
    var deliveredCount = delivered !== null ? delivered : total;
    
    // TODO: Replace with real opt-out data from backend
    var optoutRate = 0.008 + (Math.random() * 0.007);
    var totalOptouts = Math.floor(deliveredCount * optoutRate);
    
    var replyStopCount = Math.floor(totalOptouts * 0.65);
    var urlOptoutCount = totalOptouts - replyStopCount;
    
    var optoutPct = deliveredCount > 0 ? (totalOptouts / deliveredCount * 100) : 0;
    
    document.getElementById('optoutTotal').textContent = totalOptouts.toLocaleString();
    document.getElementById('optoutPercent').textContent = optoutPct.toFixed(2) + '% of delivered';
    document.getElementById('optoutBar').style.width = Math.min(optoutPct * 10, 100) + '%';
    
    document.getElementById('optoutReplyStop').textContent = replyStopCount.toLocaleString();
    document.getElementById('optoutUrl').textContent = urlOptoutCount.toLocaleString();
}

function updateMessagePreview(channel, senderId, rcsAgent, template) {
    var smsSection = document.getElementById('smsPreviewSection');
    var rcsSection = document.getElementById('rcsPreviewSection');
    var rcsBasic = document.getElementById('rcsPreviewBasic');
    var rcsRich = document.getElementById('rcsPreviewRich');
    var carouselIndicator = document.getElementById('rcsCarouselIndicator');
    
    // TODO: Replace with actual message content from backend (with placeholders, never real data)
    var mockMessages = {
        'Sale Announcement': { type: 'rich', title: 'Flash Sale!', desc: 'Hi @{{firstName}}, enjoy 30% off today!', buttons: ['Shop Now', 'View Details'], carousel: 3 },
        'Flash Deal': { type: 'rich', title: 'Limited Offer', desc: '@{{firstName}}, this deal expires soon!', buttons: ['Grab It'], carousel: 0 },
        'Reminder': { type: 'sms', content: 'Hi @{{firstName}}, reminder: your appointment is tomorrow at @{{time}}. Reply STOP to opt out.' },
        'Product Showcase': { type: 'rich', title: 'New Arrivals', desc: 'Check out our latest @{{category}} collection!', buttons: ['Browse', 'Learn More'], carousel: 4 },
        'Weekend Deal': { type: 'basic', content: '@{{firstName}}, weekend special: use code SAVE20 for 20% off! Shop now: @{{link}}' },
        'VIP Invitation': { type: 'rich', title: 'VIP Access', desc: 'Exclusive early access for you, @{{firstName}}!', buttons: ['Access Now'], carousel: 0 },
        'Shipping Update': { type: 'sms', content: 'Your order #@{{orderNumber}} has shipped! Track: @{{trackingLink}}' },
        'Survey Request': { type: 'basic', content: 'Hi @{{firstName}}, we value your feedback! Take our quick survey: @{{surveyLink}}' },
        'Order Confirm': { type: 'sms', content: 'Order confirmed! #@{{orderNumber}} - Total: @{{amount}}. Thank you for shopping!' },
        'Appointment': { type: 'sms', content: 'Reminder: @{{firstName}}, your appointment is on @{{date}} at @{{time}}.' },
        'Product Launch': { type: 'rich', title: 'Introducing @{{productName}}', desc: 'Be the first to experience our newest innovation!', buttons: ['Pre-order', 'Learn More', 'Watch Video'], carousel: 5 }
    };
    
    var defaultSms = 'Hi @{{firstName}}, thank you for being a valued customer! @{{message}} Reply STOP to opt out.';
    var defaultBasic = 'Hi @{{firstName}}, @{{message}} Tap to learn more: @{{link}}';
    
    if (channel === 'sms_only') {
        smsSection.style.display = '';
        rcsSection.style.display = 'none';
        
        document.getElementById('smsPreviewSender').textContent = senderId || 'QuickSMS';
        
        var msgData = mockMessages[template];
        if (msgData && msgData.type === 'sms') {
            document.getElementById('smsPreviewContent').textContent = msgData.content;
        } else {
            document.getElementById('smsPreviewContent').textContent = defaultSms;
        }
    } else {
        smsSection.style.display = 'none';
        rcsSection.style.display = '';
        
        document.getElementById('rcsPreviewAgent').textContent = rcsAgent || 'QuickSMS Brand';
        
        var msgData = mockMessages[template];
        
        if (channel === 'basic_rcs' || (msgData && msgData.type === 'basic')) {
            rcsBasic.style.display = '';
            rcsRich.style.display = 'none';
            
            if (msgData && msgData.type === 'basic') {
                document.getElementById('rcsPreviewBasicContent').textContent = msgData.content;
            } else {
                document.getElementById('rcsPreviewBasicContent').textContent = defaultBasic;
            }
        } else {
            rcsBasic.style.display = 'none';
            rcsRich.style.display = '';
            
            if (msgData && msgData.type === 'rich') {
                document.getElementById('rcsCardTitle').textContent = msgData.title;
                document.getElementById('rcsCardDesc').textContent = msgData.desc;
                
                var buttonsHtml = msgData.buttons.map(function(btn) {
                    return '<button class="btn btn-outline-primary btn-sm w-100 mb-1" style="font-size: 11px;" disabled>' + btn + '</button>';
                }).join('');
                document.getElementById('rcsCardButtons').innerHTML = buttonsHtml;
                
                if (msgData.carousel > 1) {
                    carouselIndicator.style.display = '';
                    document.getElementById('rcsCarouselCount').textContent = '1 of ' + msgData.carousel + ' cards';
                } else {
                    carouselIndicator.style.display = 'none';
                }
            } else {
                document.getElementById('rcsCardTitle').textContent = 'Special Offer';
                document.getElementById('rcsCardDesc').textContent = 'Hi @{{firstName}}, check out this exclusive offer!';
                document.getElementById('rcsCardButtons').innerHTML = '<button class="btn btn-outline-primary btn-sm w-100" style="font-size: 11px;" disabled>Learn More</button>';
                carouselIndicator.style.display = 'none';
            }
        }
    }
}

function toggleRecipientBreakdown() {
    var body = document.getElementById('recipientBreakdownBody');
    var chevron = document.getElementById('recipientBreakdownChevron');
    
    if (body.style.display === 'none') {
        body.style.display = '';
        chevron.style.transform = 'rotate(180deg)';
    } else {
        body.style.display = 'none';
        chevron.style.transform = 'rotate(0deg)';
    }
}

function updateRecipientBreakdown(total, delivered) {
    var container = document.getElementById('recipientSourcesContainer');
    
    // TODO: Replace with actual recipient source data from backend
    var allSources = [
        { id: 'manual', label: 'Manual Entry', icon: 'fa-keyboard', color: 'primary' },
        { id: 'upload', label: 'CSV Upload', icon: 'fa-file-upload', color: 'info' },
        { id: 'contacts', label: 'Contacts', icon: 'fa-address-book', color: 'success' },
        { id: 'lists', label: 'Lists', icon: 'fa-list', color: 'warning' },
        { id: 'dynamic', label: 'Dynamic Lists', icon: 'fa-magic', color: 'purple' },
        { id: 'tags', label: 'Tags', icon: 'fa-tags', color: 'danger' }
    ];
    
    // Mock: randomly select 1-3 sources for this campaign
    var numSources = 1 + Math.floor(Math.random() * 3);
    var shuffled = allSources.sort(function() { return 0.5 - Math.random(); });
    var usedSources = shuffled.slice(0, numSources);
    
    var sourcesHtml = usedSources.map(function(src) {
        var bgClass = src.color === 'purple' ? 'bg-purple' : 'bg-' + src.color;
        var style = src.color === 'purple' ? 'background-color: #7c3aed;' : '';
        return '<span class="badge ' + bgClass + ' text-white" style="' + style + '">' +
               '<i class="fas ' + src.icon + ' me-1"></i>' + src.label + '</span>';
    }).join('');
    
    container.innerHTML = sourcesHtml;
    
    // De-dup calculation (mock data)
    var totalSelected = total + Math.floor(total * 0.15);
    var invalidRate = 0.02 + (Math.random() * 0.03);
    var optoutRate = 0.03 + (Math.random() * 0.04);
    
    var excludedInvalid = Math.floor(totalSelected * invalidRate);
    var excludedOptout = Math.floor(totalSelected * optoutRate);
    var uniqueSent = total;
    
    document.getElementById('dedupTotalSelected').textContent = totalSelected.toLocaleString();
    document.getElementById('dedupUniqueSent').textContent = uniqueSent.toLocaleString();
    document.getElementById('dedupExcludedInvalid').textContent = excludedInvalid.toLocaleString();
    document.getElementById('dedupExcludedOptout').textContent = excludedOptout.toLocaleString();
}

function formatDate(dateStr) {
    var date = new Date(dateStr);
    var day = String(date.getDate()).padStart(2, '0');
    var month = String(date.getMonth() + 1).padStart(2, '0');
    var year = date.getFullYear();
    var hours = String(date.getHours()).padStart(2, '0');
    var minutes = String(date.getMinutes()).padStart(2, '0');
    return day + '/' + month + '/' + year + ' ' + hours + ':' + minutes;
}

var comingSoonModal = null;

function showComingSoon(message) {
    if (!comingSoonModal) {
        comingSoonModal = new bootstrap.Modal(document.getElementById('comingSoonModal'));
    }
    document.getElementById('comingSoonMessage').textContent = message;
    comingSoonModal.show();
}

function exportDeliveryReport() {
    // TODO: Implement delivery report export
    // - Check user permissions (canExportReports)
    // - Call GET /api/campaigns/{id}/export/delivery?format=csv|xlsx
    // - Generate CSV/Excel with: recipient, status, delivered_at, failed_reason, channel
    showComingSoon('Delivery report export will generate a CSV/Excel file with delivery status for all recipients.');
}

function exportMessageLog() {
    // TODO: Implement message log export
    // - Check user permissions (canExportLogs)
    // - Call GET /api/campaigns/{id}/export/messages
    // - Generate detailed log: recipient, message_content (with merged fields), sent_at, status, channel, cost
    showComingSoon('Message log export will include per-recipient status, timestamps, and message content.');
}

function viewAuditLog() {
    // TODO: Implement audit log viewer
    // - Check user permissions (canViewAuditLogs)
    // - Call GET /api/campaigns/{id}/audit-log
    // - Display modal/drawer with: created_by, created_at, edited_by, edited_at, approved_by, approved_at, cancelled_by, cancelled_at
    showComingSoon('Audit log will show who created, edited, approved, or cancelled this campaign and when.');
}

var currentCampaignId = null;
var currentCampaignName = null;
var currentCampaignTime = null;
var cancelCampaignModal = null;

function updateStatusActions(status, campaignId, campaignName, sendDate) {
    var scheduledActions = document.getElementById('scheduledActions');
    var sendingNotice = document.getElementById('sendingNotice');
    
    currentCampaignId = campaignId;
    currentCampaignName = campaignName;
    currentCampaignTime = formatDate(sendDate);
    
    scheduledActions.style.display = 'none';
    sendingNotice.style.display = 'none';
    
    if (status === 'scheduled') {
        scheduledActions.style.display = '';
    } else if (status === 'sending') {
        sendingNotice.style.display = '';
    }
}

function editCampaign(event) {
    event.preventDefault();
    // TODO: Implement edit campaign
    // - Navigate to /messages/send-message?edit={campaignId}
    // - Load campaign config into form
    // - Check permissions (canEditCampaign)
    showComingSoon('Edit campaign will load the Send Message screen with the saved campaign configuration.');
}

function showCancelConfirmation() {
    if (!cancelCampaignModal) {
        cancelCampaignModal = new bootstrap.Modal(document.getElementById('cancelCampaignModal'));
    }
    
    document.getElementById('cancelCampaignName').textContent = currentCampaignName;
    document.getElementById('cancelCampaignTime').textContent = currentCampaignTime;
    
    cancelCampaignModal.show();
}

function confirmCancelCampaign() {
    // TODO: Implement cancel campaign
    // - Call POST /api/campaigns/{id}/cancel
    // - Check permissions (canCancelCampaign)
    // - Update campaign status to 'cancelled'
    // - Refresh campaign list or update row in-place
    // - Log action in audit trail
    
    cancelCampaignModal.hide();
    campaignDrawer.hide();
    
    showComingSoon('Campaign "' + currentCampaignName + '" would be cancelled. Backend integration required.');
}

function duplicateCampaign() {
    // TODO: Implement campaign duplication
    // - Call GET /api/campaigns/{id}/config to load campaign configuration
    // - Store config in sessionStorage for Send Message page to read
    // - Navigate to Send Message with duplicate flag
    // - Check permissions (canCreateCampaign)
    
    // Mock: Store campaign info in sessionStorage for prefill
    var duplicateConfig = {
        source: 'duplicate',
        originalId: currentCampaignId,
        originalName: currentCampaignName,
        // TODO: Load actual config from backend
        name: currentCampaignName + ' (Copy)',
        prefilled: true
    };
    
    sessionStorage.setItem('campaignDuplicateConfig', JSON.stringify(duplicateConfig));
    
    campaignDrawer.hide();
    
    // Navigate to Send Message page with duplicate flag
    window.location.href = '/messages/send-message?duplicate=' + currentCampaignId;
}
</script>
@endpush
