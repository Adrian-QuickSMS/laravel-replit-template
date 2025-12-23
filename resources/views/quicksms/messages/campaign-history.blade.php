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

            <div class="card bg-light border-0">
                <div class="card-body p-3 text-center text-muted">
                    <i class="fas fa-chart-line fa-2x mb-2 opacity-50"></i>
                    <p class="mb-0 small">Detailed analytics coming soon</p>
                </div>
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

function formatDate(dateStr) {
    var date = new Date(dateStr);
    var day = String(date.getDate()).padStart(2, '0');
    var month = String(date.getMonth() + 1).padStart(2, '0');
    var year = date.getFullYear();
    var hours = String(date.getHours()).padStart(2, '0');
    var minutes = String(date.getMinutes()).padStart(2, '0');
    return day + '/' + month + '/' + year + ' ' + hours + ':' + minutes;
}
</script>
@endpush
