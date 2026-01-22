@extends('layouts.admin')

@section('title', 'Campaign History')

@push('styles')
<style>
.admin-page { padding: 1.5rem; }

.search-filter-toolbar {
    background: #fff;
    padding: 0.75rem 1rem;
    border-radius: 0.5rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
}
.search-filter-toolbar .input-group {
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    overflow: hidden;
}
.search-filter-toolbar .input-group .input-group-text,
.search-filter-toolbar .input-group .form-control,
.search-filter-toolbar .input-group .btn {
    border: none;
}
.search-filter-toolbar .form-control:focus {
    box-shadow: none;
}
.filter-pill-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: transparent;
    border: 1.5px solid #c5d3e0;
    color: var(--admin-primary, #1e3a5f);
    font-weight: 500;
    font-size: 0.875rem;
    padding: 0.5rem 1.25rem;
    border-radius: 50px;
    transition: all 0.2s;
    cursor: pointer;
}
.filter-pill-btn:hover {
    background: rgba(30, 58, 95, 0.05);
    border-color: var(--admin-primary, #1e3a5f);
    color: var(--admin-primary, #1e3a5f);
}
.filter-pill-btn.active {
    background: rgba(30, 58, 95, 0.08);
    border-color: var(--admin-primary, #1e3a5f);
    color: var(--admin-primary, #1e3a5f);
}
.filter-pill-btn i {
    font-size: 0.8rem;
    color: var(--admin-primary, #1e3a5f);
}
.filter-count-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: var(--admin-primary, #1e3a5f);
    color: #fff;
    font-size: 0.7rem;
    min-width: 18px;
    height: 18px;
    border-radius: 9px;
    margin-left: 0.25rem;
    padding: 0 5px;
}
.admin-filter-panel {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 0.5rem;
    padding: 1rem;
    margin-bottom: 1rem;
}
.admin-filter-panel .form-label {
    font-weight: 600;
    color: var(--admin-primary, #1e3a5f);
    font-size: 0.8rem;
    margin-bottom: 0.25rem;
}
.badge-admin-primary {
    background-color: rgba(30, 58, 95, 0.1);
    color: var(--admin-primary, #1e3a5f);
}
.badge-admin-success {
    background-color: rgba(34, 197, 94, 0.1);
    color: #16a34a;
}
.badge-admin-warning {
    background-color: rgba(251, 191, 36, 0.1);
    color: #d97706;
}
.badge-admin-info {
    background-color: rgba(59, 130, 246, 0.1);
    color: #2563eb;
}
.badge-admin-secondary {
    background-color: rgba(100, 116, 139, 0.1);
    color: #475569;
}
.badge-admin-danger {
    background-color: rgba(239, 68, 68, 0.1);
    color: #dc2626;
}
.table-admin th {
    background: #f8fafc;
    color: var(--admin-primary, #1e3a5f);
    font-weight: 600;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 2px solid #e2e8f0;
}
.table-admin td {
    vertical-align: middle;
    padding: 0.75rem;
    font-size: 0.875rem;
}
.table-admin tbody tr:hover {
    background: rgba(30, 58, 95, 0.02);
}
.account-link {
    color: var(--admin-primary, #1e3a5f);
    text-decoration: none;
    font-weight: 500;
}
.account-link:hover {
    text-decoration: underline;
}
.action-dots-btn {
    background: transparent;
    border: none;
    padding: 0.25rem 0.5rem;
    cursor: pointer;
    color: #6c757d;
}
.action-dots-btn:hover {
    color: var(--admin-primary, #1e3a5f);
}
.multiselect-dropdown .dropdown-toggle {
    background-color: #fff;
    border: 1px solid #ced4da;
    color: #495057;
}
.multiselect-dropdown .dropdown-menu {
    padding: 0.5rem;
    min-width: 200px;
}
</style>
@endpush

@php
$campaigns = [
    ['id' => 'C-2026-001', 'name' => 'Spring Promo Campaign', 'account' => 'Acme Corp', 'account_id' => 'ACC-001', 'channel' => 'basic_rcs', 'status' => 'pending', 'recipients' => 3500, 'delivered' => 0, 'send_date' => '2026-01-02 10:00', 'sender_id' => 'ACME'],
    ['id' => 'C-2026-002', 'name' => 'New Year Flash Sale', 'account' => 'RetailMax', 'account_id' => 'ACC-002', 'channel' => 'rich_rcs', 'status' => 'scheduled', 'recipients' => 5200, 'delivered' => 0, 'send_date' => '2026-01-25 00:00', 'sender_id' => 'RETAILMAX'],
    ['id' => 'C-2025-003', 'name' => 'Holiday Greetings', 'account' => 'ServicePro', 'account_id' => 'ACC-003', 'channel' => 'sms_only', 'status' => 'sending', 'recipients' => 3150, 'delivered' => 1840, 'send_date' => '2024-12-24 09:00', 'sender_id' => 'SVCPRO'],
    ['id' => 'C-2024-004', 'name' => 'Boxing Day Deals', 'account' => 'Acme Corp', 'account_id' => 'ACC-001', 'channel' => 'basic_rcs', 'status' => 'scheduled', 'recipients' => 2800, 'delivered' => 0, 'send_date' => '2024-12-26 08:00', 'sender_id' => 'ACME'],
    ['id' => 'C-2024-005', 'name' => 'Christmas Eve Reminder', 'account' => 'HealthFirst', 'account_id' => 'ACC-004', 'channel' => 'sms_only', 'status' => 'complete', 'recipients' => 1500, 'delivered' => 1487, 'send_date' => '2024-12-24 07:00', 'sender_id' => 'HEALTH1'],
    ['id' => 'C-2024-006', 'name' => 'Winter Clearance', 'account' => 'RetailMax', 'account_id' => 'ACC-002', 'channel' => 'rich_rcs', 'status' => 'complete', 'recipients' => 4200, 'delivered' => 4156, 'send_date' => '2024-12-23 14:30', 'sender_id' => 'RETAILMAX'],
    ['id' => 'C-2024-007', 'name' => 'Last Minute Gifts', 'account' => 'GiftZone', 'account_id' => 'ACC-005', 'channel' => 'sms_only', 'status' => 'complete', 'recipients' => 890, 'delivered' => 885, 'send_date' => '2024-12-23 10:00', 'sender_id' => 'GIFTZONE'],
    ['id' => 'C-2024-008', 'name' => 'Flash Sale Alert', 'account' => 'Acme Corp', 'account_id' => 'ACC-001', 'channel' => 'sms_only', 'status' => 'cancelled', 'recipients' => 1200, 'delivered' => 0, 'send_date' => '2024-12-22 15:00', 'sender_id' => 'ACME'],
    ['id' => 'C-2024-009', 'name' => 'Seasonal Offers', 'account' => 'ServicePro', 'account_id' => 'ACC-003', 'channel' => 'basic_rcs', 'status' => 'complete', 'recipients' => 2100, 'delivered' => 2089, 'send_date' => '2024-12-21 11:00', 'sender_id' => 'SVCPRO'],
    ['id' => 'C-2024-010', 'name' => 'Early Bird Special', 'account' => 'HealthFirst', 'account_id' => 'ACC-004', 'channel' => 'rich_rcs', 'status' => 'complete', 'recipients' => 950, 'delivered' => 942, 'send_date' => '2024-12-20 06:00', 'sender_id' => 'HEALTH1'],
];

$accounts = collect($campaigns)->pluck('account')->unique()->sort()->values();
$senderIds = collect($campaigns)->pluck('sender_id')->unique()->sort()->values();
@endphp

@section('content')
<div class="admin-page">
    <div class="row page-titles mb-3">
        <div class="col-12">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
                <li class="breadcrumb-item"><a href="javascript:void(0)">Messaging Assets</a></li>
                <li class="breadcrumb-item active">Campaigns</li>
            </ol>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap" style="background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 100%); color: #fff;">
            <h5 class="card-title mb-0 text-white">Campaign History</h5>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-outline-light btn-sm" data-bs-toggle="collapse" data-bs-target="#filtersPanel">
                    <i class="fas fa-filter me-1"></i> Filters
                    <span id="activeFiltersBadge" class="badge bg-light text-dark ms-1 d-none">0</span>
                </button>
                <button type="button" class="btn btn-light btn-sm">
                    <i class="fas fa-download me-1"></i> Export
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <div class="input-group">
                    <span class="input-group-text bg-transparent"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control" id="campaignSearch" placeholder="Search by campaign name, account, sender ID...">
                </div>
            </div>

            <div class="collapse mb-3" id="filtersPanel">
                <div class="admin-filter-panel">
                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-lg-4">
                            <label class="form-label small fw-bold">Date Range</label>
                            <div class="d-flex gap-2 align-items-center">
                                <input type="date" class="form-control form-control-sm" id="filterDateFrom">
                                <span class="text-muted small">to</span>
                                <input type="date" class="form-control form-control-sm" id="filterDateTo">
                            </div>
                        </div>
                        <div class="col-6 col-md-4 col-lg-2">
                            <label class="form-label small fw-bold">Account</label>
                            <select class="form-select form-select-sm" id="filterAccount">
                                <option value="">All Accounts</option>
                                @foreach($accounts as $account)
                                <option value="{{ $account }}">{{ $account }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-4 col-lg-2">
                            <label class="form-label small fw-bold">Status</label>
                            <select class="form-select form-select-sm" id="filterStatus">
                                <option value="">All Statuses</option>
                                <option value="pending">Pending</option>
                                <option value="scheduled">Scheduled</option>
                                <option value="sending">Sending</option>
                                <option value="complete">Complete</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-4 col-lg-2">
                            <label class="form-label small fw-bold">Channel</label>
                            <select class="form-select form-select-sm" id="filterChannel">
                                <option value="">All Channels</option>
                                <option value="sms_only">SMS</option>
                                <option value="basic_rcs">Basic RCS</option>
                                <option value="rich_rcs">Rich RCS</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-4 col-lg-2">
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-sm text-white" style="background-color: var(--admin-primary, #1e3a5f);" id="btnApplyFilters">
                                    <i class="fas fa-check me-1"></i> Apply
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="btnResetFilters">
                                    <i class="fas fa-undo me-1"></i> Reset
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-admin table-hover mb-0" id="campaignsTable">
                    <thead>
                        <tr>
                            <th>Account</th>
                            <th>Campaign Name</th>
                            <th>Channel</th>
                            <th>Status</th>
                            <th>Recipients</th>
                            <th>Sender ID</th>
                            <th>Send Date</th>
                            <th class="text-center" style="width: 60px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="campaignsTableBody">
                        @foreach($campaigns as $campaign)
                        <tr data-id="{{ $campaign['id'] }}" 
                            data-account="{{ strtolower($campaign['account']) }}"
                            data-name="{{ strtolower($campaign['name']) }}"
                            data-channel="{{ $campaign['channel'] }}"
                            data-status="{{ $campaign['status'] }}"
                            data-sender-id="{{ strtolower($campaign['sender_id']) }}">
                            <td>
                                <a href="{{ route('admin.accounts.details', $campaign['account_id']) }}" class="account-link">
                                    {{ $campaign['account'] }}
                                </a>
                            </td>
                            <td>
                                <span class="fw-medium">{{ $campaign['name'] }}</span>
                                <div class="text-muted small">{{ $campaign['id'] }}</div>
                            </td>
                            <td>
                                @if($campaign['channel'] === 'sms_only')
                                    <span class="badge badge-admin-success">SMS</span>
                                @elseif($campaign['channel'] === 'basic_rcs')
                                    <span class="badge badge-admin-primary">Basic RCS</span>
                                @else
                                    <span class="badge badge-admin-info">Rich RCS</span>
                                @endif
                            </td>
                            <td>
                                @if($campaign['status'] === 'pending')
                                    <span class="badge badge-admin-warning">Pending</span>
                                @elseif($campaign['status'] === 'scheduled')
                                    <span class="badge badge-admin-info">Scheduled</span>
                                @elseif($campaign['status'] === 'sending')
                                    <span class="badge badge-admin-primary">Sending</span>
                                @elseif($campaign['status'] === 'complete')
                                    <span class="badge badge-admin-success">Complete</span>
                                @elseif($campaign['status'] === 'cancelled')
                                    <span class="badge badge-admin-danger">Cancelled</span>
                                @endif
                            </td>
                            <td>
                                @if($campaign['status'] === 'complete' || $campaign['status'] === 'sending')
                                    {{ number_format($campaign['delivered']) }}/{{ number_format($campaign['recipients']) }}
                                @else
                                    {{ number_format($campaign['recipients']) }}
                                @endif
                            </td>
                            <td><code class="text-muted">{{ $campaign['sender_id'] }}</code></td>
                            <td>{{ \Carbon\Carbon::parse($campaign['send_date'])->format('d/m/Y H:i') }}</td>
                            <td class="text-center">
                                <div class="dropdown">
                                    <button class="action-dots-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" href="#"><i class="fas fa-eye me-2"></i> View Details</a></li>
                                        <li><a class="dropdown-item" href="#"><i class="fas fa-chart-bar me-2"></i> Delivery Report</a></li>
                                        @if($campaign['status'] === 'pending' || $campaign['status'] === 'scheduled')
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item text-danger" href="#"><i class="fas fa-ban me-2"></i> Cancel Campaign</a></li>
                                        @endif
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted small">
                    Showing <span id="visibleCount">{{ count($campaigns) }}</span> of {{ count($campaigns) }} campaigns
                </div>
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                        <li class="page-item"><a class="page-link" href="#">Next</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var searchInput = document.getElementById('campaignSearch');
    var filterAccount = document.getElementById('filterAccount');
    var filterStatus = document.getElementById('filterStatus');
    var filterChannel = document.getElementById('filterChannel');
    var applyBtn = document.getElementById('btnApplyFilters');
    var resetBtn = document.getElementById('btnResetFilters');

    function filterCampaigns() {
        var searchTerm = searchInput.value.toLowerCase();
        var account = filterAccount.value.toLowerCase();
        var status = filterStatus.value;
        var channel = filterChannel.value;

        var rows = document.querySelectorAll('#campaignsTableBody tr');
        var visibleCount = 0;

        rows.forEach(function(row) {
            var rowAccount = row.dataset.account || '';
            var rowName = row.dataset.name || '';
            var rowStatus = row.dataset.status || '';
            var rowChannel = row.dataset.channel || '';
            var rowSenderId = row.dataset.senderId || '';

            var matchesSearch = !searchTerm || 
                rowAccount.includes(searchTerm) || 
                rowName.includes(searchTerm) || 
                rowSenderId.includes(searchTerm);

            var matchesAccount = !account || rowAccount === account;
            var matchesStatus = !status || rowStatus === status;
            var matchesChannel = !channel || rowChannel === channel;

            if (matchesSearch && matchesAccount && matchesStatus && matchesChannel) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        document.getElementById('visibleCount').textContent = visibleCount;
        updateFilterBadge();
    }

    function updateFilterBadge() {
        var count = 0;
        if (filterAccount.value) count++;
        if (filterStatus.value) count++;
        if (filterChannel.value) count++;
        if (document.getElementById('filterDateFrom').value) count++;
        if (document.getElementById('filterDateTo').value) count++;

        var badge = document.getElementById('activeFiltersBadge');
        if (badge) {
            if (count > 0) {
                badge.textContent = count;
                badge.classList.remove('d-none');
            } else {
                badge.classList.add('d-none');
            }
        }
    }

    function resetFilters() {
        filterAccount.value = '';
        filterStatus.value = '';
        filterChannel.value = '';
        document.getElementById('filterDateFrom').value = '';
        document.getElementById('filterDateTo').value = '';
        searchInput.value = '';
        filterCampaigns();
    }

    if (searchInput) searchInput.addEventListener('input', filterCampaigns);
    if (applyBtn) applyBtn.addEventListener('click', filterCampaigns);
    if (resetBtn) resetBtn.addEventListener('click', resetFilters);
});
</script>
@endpush
