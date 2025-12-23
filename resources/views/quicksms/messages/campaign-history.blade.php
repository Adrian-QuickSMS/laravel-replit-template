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

<div class="offcanvas offcanvas-end" tabindex="-1" id="campaignDrawer" style="width: 450px;">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title">Campaign Overview</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
        <div class="mb-4">
            <h4 id="drawerCampaignName" class="mb-3">-</h4>
            <div class="d-flex gap-2 mb-3">
                <span id="drawerChannelBadge" class="badge bg-secondary">-</span>
                <span id="drawerStatusBadge" class="badge bg-secondary">-</span>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body p-3">
                <h6 class="text-muted mb-3"><i class="fas fa-info-circle me-2"></i>Campaign Details</h6>
                <div class="row mb-2">
                    <div class="col-5 text-muted">Campaign ID</div>
                    <div class="col-7" id="drawerCampaignId">-</div>
                </div>
                <div class="row mb-2">
                    <div class="col-5 text-muted">Send Date</div>
                    <div class="col-7" id="drawerSendDate">-</div>
                </div>
                <div class="row mb-2">
                    <div class="col-5 text-muted">Total Recipients</div>
                    <div class="col-7" id="drawerRecipientsTotal">-</div>
                </div>
                <div class="row" id="drawerDeliveredRow">
                    <div class="col-5 text-muted">Delivered</div>
                    <div class="col-7" id="drawerRecipientsDelivered">-</div>
                </div>
            </div>
        </div>

        <div class="card bg-light border-0">
            <div class="card-body p-3 text-center text-muted">
                <i class="fas fa-chart-bar fa-2x mb-2 opacity-50"></i>
                <p class="mb-0 small">Delivery analytics and cost breakdown coming soon</p>
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
    var recipientsTotal = row.dataset.recipientsTotal;
    var recipientsDelivered = row.dataset.recipientsDelivered;
    var sendDate = row.dataset.sendDate;

    document.getElementById('drawerCampaignName').textContent = name;
    document.getElementById('drawerCampaignId').textContent = campaignId;
    document.getElementById('drawerSendDate').textContent = formatDate(sendDate);
    document.getElementById('drawerRecipientsTotal').textContent = Number(recipientsTotal).toLocaleString();

    var channelBadge = document.getElementById('drawerChannelBadge');
    if (channel === 'sms_only') {
        channelBadge.className = 'badge bg-secondary';
        channelBadge.textContent = 'SMS';
    } else if (channel === 'basic_rcs') {
        channelBadge.className = 'badge bg-success';
        channelBadge.textContent = 'Basic RCS';
    } else {
        channelBadge.className = 'badge bg-primary';
        channelBadge.textContent = 'Rich RCS';
    }

    var statusBadge = document.getElementById('drawerStatusBadge');
    if (status === 'scheduled') {
        statusBadge.className = 'badge bg-info';
        statusBadge.textContent = 'Scheduled';
    } else if (status === 'sending') {
        statusBadge.className = 'badge bg-warning text-dark';
        statusBadge.textContent = 'Sending';
    } else {
        statusBadge.className = 'badge bg-success';
        statusBadge.textContent = 'Complete';
    }

    var deliveredRow = document.getElementById('drawerDeliveredRow');
    if (recipientsDelivered) {
        deliveredRow.style.display = '';
        document.getElementById('drawerRecipientsDelivered').textContent = Number(recipientsDelivered).toLocaleString();
    } else {
        deliveredRow.style.display = 'none';
    }

    campaignDrawer.show();
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
