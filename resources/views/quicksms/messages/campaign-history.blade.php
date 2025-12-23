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
                            <tbody>
                                @forelse($campaigns as $campaign)
                                <tr onclick="openCampaignDrawer('{{ $campaign['id'] }}')" 
                                    data-id="{{ $campaign['id'] }}"
                                    data-name="{{ $campaign['name'] }}"
                                    data-channel="{{ $campaign['channel'] }}"
                                    data-status="{{ $campaign['status'] }}"
                                    data-recipients-total="{{ $campaign['recipients_total'] }}"
                                    data-recipients-delivered="{{ $campaign['recipients_delivered'] ?? '' }}"
                                    data-send-date="{{ $campaign['send_date'] }}">
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
                                <tr>
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
                    </div>
                    @if(count($campaigns) > 0)
                    <div class="mt-3">
                        <small class="text-muted">Showing {{ count($campaigns) }} campaign(s)</small>
                    </div>
                    @endif
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

document.addEventListener('DOMContentLoaded', function() {
    campaignDrawer = new bootstrap.Offcanvas(document.getElementById('campaignDrawer'));
});

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
