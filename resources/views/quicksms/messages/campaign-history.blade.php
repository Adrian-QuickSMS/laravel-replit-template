@extends('layouts.quicksms')

@section('title', 'Campaign History')

@php
/**
 * SCALABILITY GUARDRAILS (10K-1M campaigns)
 * ==========================================
 * TODO: All aggregates must be computed server-side for performance
 * - Total campaigns count: paginated query with COUNT()
 * - Filter counts: computed via indexed queries
 * - Dashboard metrics: pre-aggregated or computed on-demand via API
 * 
 * Current mock data is for UI development only.
 * Replace with: GET /api/campaigns?page=X&limit=Y&filters=Z
 */

/**
 * RBAC PERMISSION STUBS
 * =====================
 * TODO: Integrate with existing RBAC system (e.g., Spatie Laravel-Permission)
 * Roles: Viewer, Editor, Admin
 * 
 * Permissions matrix:
 * - view_campaigns: Viewer, Editor, Admin
 * - edit_campaign: Editor, Admin
 * - cancel_campaign: Editor, Admin
 * - duplicate_campaign: Editor, Admin
 * - export_delivery_report: Editor, Admin
 * - export_message_log: Admin only (contains PII)
 * - view_audit_log: Admin only
 */

// Stub permission checks - replace with actual RBAC implementation
$userRole = 'admin'; // TODO: Auth::user()->role or Auth::user()->hasRole()
$permissions = [
    'can_edit' => in_array($userRole, ['editor', 'admin']),
    'can_cancel' => in_array($userRole, ['editor', 'admin']),
    'can_duplicate' => in_array($userRole, ['editor', 'admin']),
    'can_export_delivery' => in_array($userRole, ['editor', 'admin']),
    'can_export_messages' => $userRole === 'admin', // PII access
    'can_view_audit' => $userRole === 'admin',
];

/**
 * GDPR COMPLIANCE
 * ================
 * - Message previews MUST use placeholders (e.g., {{firstName}}) never real PII
 * - Exports containing PII require elevated permissions
 * - All PII access must be logged in audit trail
 */
@endphp

@push('styles')
<style>
.table thead th {
    background: #f8f9fa !important;
    border-bottom: 1px solid #e9ecef !important;
    padding: 0.75rem 0.5rem !important;
    font-weight: 600 !important;
    font-size: 0.8rem !important;
    color: #495057 !important;
    text-transform: none !important;
    letter-spacing: normal !important;
}
.table tbody td {
    padding: 0.75rem 0.5rem !important;
    vertical-align: middle;
    border-bottom: 1px solid #f1f3f5 !important;
    font-size: 0.85rem;
    color: #495057;
}
.table tbody tr:last-child td {
    border-bottom: none !important;
}
.table tbody tr:hover td {
    background-color: #f8f9fa !important;
}
#campaignsTable tbody tr {
    cursor: pointer;
}
.filter-chip {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.5rem;
    background-color: #e9ecef;
    border-radius: 1rem;
    font-size: 0.75rem;
    margin-right: 0.5rem;
    margin-bottom: 0.5rem;
}
.filter-chip .remove-chip {
    margin-left: 0.5rem;
    cursor: pointer;
    opacity: 0.7;
}
.filter-chip .remove-chip:hover {
    opacity: 1;
}
.btn-xs {
    padding: 0.2rem 0.5rem;
    font-size: 0.7rem;
    line-height: 1.4;
}
.badge-status-draft {
    background-color: #e9ecef;
    color: #495057;
}
.badge-channel-sms {
    background-color: #d4edda;
    color: #155724;
}
.badge-channel-rcs {
    background-color: #cce5ff;
    color: #004085;
}
.date-preset-btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
    border: 1px solid #dee2e6;
    background: #fff;
    border-radius: 0.25rem;
    cursor: pointer;
    transition: all 0.15s ease;
}
.date-preset-btn:hover {
    background: #f8f9fa;
    border-color: #6f42c1;
}
.date-preset-btn.active {
    background: #6f42c1;
    color: #fff;
    border-color: #6f42c1;
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
    
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                    <h5 class="card-title mb-2 mb-md-0">Campaign History</h5>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#filtersPanel">
                            <i class="fas fa-filter me-1"></i> Filters
                        </button>
                        <a href="{{ route('messages.send') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus me-1"></i> Create Campaign
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="input-group">
                            <span class="input-group-text bg-transparent"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control" id="campaignSearch" placeholder="Search by name, sender ID, agent, tags, or template...">
                        </div>
                    </div>

                    <div class="collapse mb-3" id="filtersPanel">
                        <div class="card card-body border-0 rounded-3" style="background-color: #f0ebf8;">
                            <!-- Row 1: Date Range -->
                            <div class="row g-3 align-items-end">
                                <div class="col-12 col-lg-6">
                                    <label class="form-label small fw-bold">Date Range</label>
                                    <div class="d-flex gap-2 align-items-center">
                                        <input type="date" class="form-control form-control-sm" id="filterDateFrom">
                                        <span class="text-muted small">to</span>
                                        <input type="date" class="form-control form-control-sm" id="filterDateTo">
                                    </div>
                                    <div class="d-flex flex-wrap gap-1 mt-2">
                                        <button type="button" class="btn btn-outline-primary btn-xs date-preset-btn" data-preset="today">Today</button>
                                        <button type="button" class="btn btn-outline-primary btn-xs date-preset-btn" data-preset="yesterday">Yesterday</button>
                                        <button type="button" class="btn btn-outline-primary btn-xs date-preset-btn" data-preset="7days">Last 7 Days</button>
                                        <button type="button" class="btn btn-outline-primary btn-xs date-preset-btn" data-preset="30days">Last 30 Days</button>
                                        <button type="button" class="btn btn-outline-primary btn-xs date-preset-btn" data-preset="thismonth">This Month</button>
                                    </div>
                                </div>
                                <div class="col-6 col-md-4 col-lg-2">
                                    <label class="form-label small fw-bold">Status</label>
                                    <div class="dropdown multiselect-dropdown" data-filter="statuses">
                                        <button class="btn btn-sm dropdown-toggle w-100 text-start d-flex justify-content-between align-items-center" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" style="background-color: #fff; border: 1px solid #ced4da; color: #495057;">
                                            <span class="dropdown-label">All Statuses</span>
                                        </button>
                                        <div class="dropdown-menu w-100 p-2">
                                            <div class="d-flex justify-content-between mb-2 border-bottom pb-2">
                                                <a href="#" class="small text-decoration-none select-all-btn">Select All</a>
                                                <a href="#" class="small text-decoration-none clear-all-btn">Clear</a>
                                            </div>
                                            <div class="form-check"><input class="form-check-input" type="checkbox" value="draft" id="statusDraft"><label class="form-check-label small" for="statusDraft">Draft</label></div>
                                            <div class="form-check"><input class="form-check-input" type="checkbox" value="scheduled" id="statusScheduled"><label class="form-check-label small" for="statusScheduled">Scheduled</label></div>
                                            <div class="form-check"><input class="form-check-input" type="checkbox" value="sending" id="statusSending"><label class="form-check-label small" for="statusSending">Sending</label></div>
                                            <div class="form-check"><input class="form-check-input" type="checkbox" value="complete" id="statusComplete"><label class="form-check-label small" for="statusComplete">Complete</label></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-4 col-lg-2">
                                    <label class="form-label small fw-bold">Channel</label>
                                    <div class="dropdown multiselect-dropdown" data-filter="channels">
                                        <button class="btn btn-sm dropdown-toggle w-100 text-start d-flex justify-content-between align-items-center" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" style="background-color: #fff; border: 1px solid #ced4da; color: #495057;">
                                            <span class="dropdown-label">All Channels</span>
                                        </button>
                                        <div class="dropdown-menu w-100 p-2">
                                            <div class="d-flex justify-content-between mb-2 border-bottom pb-2">
                                                <a href="#" class="small text-decoration-none select-all-btn">Select All</a>
                                                <a href="#" class="small text-decoration-none clear-all-btn">Clear</a>
                                            </div>
                                            <div class="form-check"><input class="form-check-input" type="checkbox" value="sms_only" id="channelSMS"><label class="form-check-label small" for="channelSMS">SMS</label></div>
                                            <div class="form-check"><input class="form-check-input" type="checkbox" value="basic_rcs" id="channelBasicRCS"><label class="form-check-label small" for="channelBasicRCS">Basic RCS</label></div>
                                            <div class="form-check"><input class="form-check-input" type="checkbox" value="rich_rcs" id="channelRichRCS"><label class="form-check-label small" for="channelRichRCS">Rich RCS</label></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-4 col-lg-2">
                                    <label class="form-label small fw-bold">Sender ID</label>
                                    @php
                                        $senderIds = collect($campaigns)->pluck('sender_id')->unique()->sort();
                                    @endphp
                                    <div class="dropdown multiselect-dropdown" data-filter="senderIds">
                                        <button class="btn btn-sm dropdown-toggle w-100 text-start d-flex justify-content-between align-items-center" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" style="background-color: #fff; border: 1px solid #ced4da; color: #495057;">
                                            <span class="dropdown-label">All Sender IDs</span>
                                        </button>
                                        <div class="dropdown-menu w-100 p-2" style="max-height: 250px; overflow-y: auto;">
                                            <div class="d-flex justify-content-between mb-2 border-bottom pb-2">
                                                <a href="#" class="small text-decoration-none select-all-btn">Select All</a>
                                                <a href="#" class="small text-decoration-none clear-all-btn">Clear</a>
                                            </div>
                                            @foreach($senderIds as $index => $sid)
                                            <div class="form-check"><input class="form-check-input" type="checkbox" value="{{ $sid }}" id="sender{{ $index }}"><label class="form-check-label small" for="sender{{ $index }}">{{ $sid }}</label></div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Row 2: Additional Filters -->
                            <div class="row g-3 align-items-end mt-2">
                                <div class="col-6 col-md-4 col-lg-2">
                                    <label class="form-label small fw-bold">RCS Agent</label>
                                    @php
                                        $agents = collect($campaigns)->pluck('rcs_agent')->filter()->unique()->sort();
                                    @endphp
                                    <div class="dropdown multiselect-dropdown" data-filter="rcsAgents">
                                        <button class="btn btn-sm dropdown-toggle w-100 text-start d-flex justify-content-between align-items-center" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" style="background-color: #fff; border: 1px solid #ced4da; color: #495057;">
                                            <span class="dropdown-label">All Agents</span>
                                        </button>
                                        <div class="dropdown-menu w-100 p-2" style="max-height: 250px; overflow-y: auto;">
                                            <div class="d-flex justify-content-between mb-2 border-bottom pb-2">
                                                <a href="#" class="small text-decoration-none select-all-btn">Select All</a>
                                                <a href="#" class="small text-decoration-none clear-all-btn">Clear</a>
                                            </div>
                                            @foreach($agents as $index => $agent)
                                            <div class="form-check"><input class="form-check-input" type="checkbox" value="{{ $agent }}" id="agent{{ $index }}"><label class="form-check-label small" for="agent{{ $index }}">{{ $agent }}</label></div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-4 col-lg-2">
                                    <label class="form-label small fw-bold">Has Tracking</label>
                                    <div class="dropdown multiselect-dropdown" data-filter="tracking">
                                        <button class="btn btn-sm dropdown-toggle w-100 text-start d-flex justify-content-between align-items-center" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" style="background-color: #fff; border: 1px solid #ced4da; color: #495057;">
                                            <span class="dropdown-label">Any</span>
                                        </button>
                                        <div class="dropdown-menu w-100 p-2">
                                            <div class="d-flex justify-content-between mb-2 border-bottom pb-2">
                                                <a href="#" class="small text-decoration-none select-all-btn">Select All</a>
                                                <a href="#" class="small text-decoration-none clear-all-btn">Clear</a>
                                            </div>
                                            <div class="form-check"><input class="form-check-input" type="checkbox" value="yes" id="trackingYes"><label class="form-check-label small" for="trackingYes">Yes</label></div>
                                            <div class="form-check"><input class="form-check-input" type="checkbox" value="no" id="trackingNo"><label class="form-check-label small" for="trackingNo">No</label></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-4 col-lg-2">
                                    <label class="form-label small fw-bold">Has Opt-Out</label>
                                    <div class="dropdown multiselect-dropdown" data-filter="optout">
                                        <button class="btn btn-sm dropdown-toggle w-100 text-start d-flex justify-content-between align-items-center" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" style="background-color: #fff; border: 1px solid #ced4da; color: #495057;">
                                            <span class="dropdown-label">Any</span>
                                        </button>
                                        <div class="dropdown-menu w-100 p-2">
                                            <div class="d-flex justify-content-between mb-2 border-bottom pb-2">
                                                <a href="#" class="small text-decoration-none select-all-btn">Select All</a>
                                                <a href="#" class="small text-decoration-none clear-all-btn">Clear</a>
                                            </div>
                                            <div class="form-check"><input class="form-check-input" type="checkbox" value="yes" id="optoutYes"><label class="form-check-label small" for="optoutYes">Yes</label></div>
                                            <div class="form-check"><input class="form-check-input" type="checkbox" value="no" id="optoutNo"><label class="form-check-label small" for="optoutNo">No</label></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Button Row -->
                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="d-flex gap-2 justify-content-end">
                                        <button type="button" class="btn btn-primary btn-sm" id="btnApplyFilters" style="white-space: nowrap;">
                                            <i class="fas fa-check me-1"></i> Apply Filters
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" id="btnResetFilters" style="white-space: nowrap;">
                                            <i class="fas fa-undo me-1"></i> Reset Filters
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3" id="activeFiltersContainer" style="display: none;">
                        <div class="d-flex flex-wrap align-items-center">
                            <span class="small text-muted me-2">Active filters:</span>
                            <div id="activeFiltersChips"></div>
                            <button type="button" class="btn btn-link btn-sm text-decoration-none p-0 ms-2" id="btnClearAllFilters">
                                Clear all
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="campaignsTable">
                            <thead>
                                <tr>
                                    <th>
                                        <div class="dropdown d-inline-block">
                                            <span class="dropdown-toggle" style="cursor: pointer;" data-bs-toggle="dropdown">
                                                Campaign Name <i class="fas fa-sort ms-1 text-muted"></i>
                                            </span>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="#!" onclick="sortCampaigns('name', 'asc'); return false;"><i class="fas fa-sort-alpha-down me-2"></i> A-Z</a></li>
                                                <li><a class="dropdown-item" href="#!" onclick="sortCampaigns('name', 'desc'); return false;"><i class="fas fa-sort-alpha-up me-2"></i> Z-A</a></li>
                                            </ul>
                                        </div>
                                    </th>
                                    <th>Channel</th>
                                    <th>
                                        <div class="dropdown d-inline-block">
                                            <span class="dropdown-toggle" style="cursor: pointer;" data-bs-toggle="dropdown">
                                                Status <i class="fas fa-sort ms-1 text-muted"></i>
                                            </span>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="#!" onclick="sortCampaigns('status', 'asc'); return false;"><i class="fas fa-clock me-2 text-info"></i> Scheduled First</a></li>
                                                <li><a class="dropdown-item" href="#!" onclick="sortCampaigns('status', 'desc'); return false;"><i class="fas fa-check-circle me-2 text-success"></i> Complete First</a></li>
                                            </ul>
                                        </div>
                                    </th>
                                    <th>
                                        <div class="dropdown d-inline-block">
                                            <span class="dropdown-toggle" style="cursor: pointer;" data-bs-toggle="dropdown">
                                                Recipients <i class="fas fa-sort ms-1 text-muted"></i>
                                            </span>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="#!" onclick="sortCampaigns('recipients', 'desc'); return false;"><i class="fas fa-sort-amount-down me-2"></i> Highest First</a></li>
                                                <li><a class="dropdown-item" href="#!" onclick="sortCampaigns('recipients', 'asc'); return false;"><i class="fas fa-sort-amount-up me-2"></i> Lowest First</a></li>
                                            </ul>
                                        </div>
                                    </th>
                                    <th>
                                        <div class="dropdown d-inline-block">
                                            <span class="dropdown-toggle" style="cursor: pointer;" data-bs-toggle="dropdown">
                                                Send Date <i class="fas fa-sort ms-1 text-muted"></i>
                                            </span>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="#!" onclick="sortCampaigns('date', 'desc'); return false;"><i class="fas fa-calendar-alt me-2"></i> Most Recent</a></li>
                                                <li><a class="dropdown-item" href="#!" onclick="sortCampaigns('date', 'asc'); return false;"><i class="fas fa-calendar me-2"></i> Oldest First</a></li>
                                            </ul>
                                        </div>
                                    </th>
                                    <th style="width: 90px;"></th>
                                </tr>
                            </thead>
                            <tbody id="campaignsTableBody">
                                @forelse($campaigns as $campaign)
                                <tr class="btn-reveal-trigger" onclick="openCampaignDrawer('{{ $campaign['id'] }}')" 
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
                                    <td class="py-2">
                                        <h6 class="mb-0 fs-6">{{ $campaign['name'] }}</h6>
                                    </td>
                                    <td class="py-2">
                                        @if($campaign['channel'] === 'sms_only')
                                            <span class="badge badge-pastel-success">SMS</span>
                                        @elseif($campaign['channel'] === 'basic_rcs')
                                            <span class="badge badge-pastel-primary">Basic RCS</span>
                                        @else
                                            <span class="badge badge-pastel-primary">Rich RCS</span>
                                        @endif
                                    </td>
                                    <td class="py-2">
                                        @if($campaign['status'] === 'scheduled')
                                            <span class="badge badge-pastel-pink">Scheduled</span>
                                        @elseif($campaign['status'] === 'sending')
                                            <span class="badge badge-pastel-warning">Sending</span>
                                        @else
                                            <span class="badge badge-pastel-success">Complete</span>
                                        @endif
                                    </td>
                                    <td class="py-2">
                                        @if($campaign['recipients_delivered'] !== null)
                                            {{ number_format($campaign['recipients_delivered']) }}/{{ number_format($campaign['recipients_total']) }}
                                        @else
                                            {{ number_format($campaign['recipients_total']) }}
                                        @endif
                                    </td>
                                    <td class="py-2">{{ \Carbon\Carbon::parse($campaign['send_date'])->format('d/m/Y H:i') }}</td>
                                    <td class="py-2"></td>
                                </tr>
                                @empty
                                <tr id="emptyStateRow">
                                    <td colspan="6" class="text-center py-5 text-muted">
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
        <div class="tryal-gradient text-white p-4 rounded-3">
            <h4 id="drawerCampaignName" class="mb-3 fw-semibold" style="color: white !important;">-</h4>
            <div class="d-flex flex-wrap gap-2 mb-3" style="position: relative; z-index: 10;">
                <span id="drawerStatusBadge" class="badge">-</span>
                <span id="drawerChannelBadge" class="badge">-</span>
                <span id="drawerLiveStateBadge" class="badge">-</span>
            </div>
            <div class="d-flex align-items-center small opacity-75">
                <i class="fas fa-clock me-2"></i>
                <span id="drawerSendTimeLabel">Send Time:</span>
                <span class="ms-1 fw-medium" id="drawerSendTime">-</span>
            </div>
            
            <div class="mt-3" id="statusActionsContainer">
                <div id="scheduledActions" style="display: none;">
                    <div class="d-flex gap-2 mb-2">
                        @if($permissions['can_edit'])
                        <a href="#" id="editCampaignBtn" class="btn btn-sm flex-fill" style="background: transparent; color: white; border: 1px solid rgba(255,255,255,0.5);" onclick="editCampaign(event)">
                            <i class="fas fa-edit me-1"></i>Edit Campaign
                        </a>
                        @endif
                        @if($permissions['can_cancel'])
                        <button type="button" class="btn btn-sm flex-fill" style="background: transparent; color: white; border: 1px solid rgba(255,255,255,0.5);" onclick="showCancelConfirmation()">
                            <i class="fas fa-times-circle me-1"></i>Cancel
                        </button>
                        @endif
                    </div>
                </div>
                <div id="sendingNotice" style="display: none;">
                    <div class="alert alert-light mb-2 py-2 px-3 small d-flex align-items-center" style="background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.3);">
                        <i class="fas fa-spinner fa-spin me-2"></i>
                        <span>Campaign is currently sending and cannot be cancelled.</span>
                    </div>
                </div>
                @if($permissions['can_duplicate'])
                <div id="duplicateAction">
                    <button type="button" class="btn btn-sm w-100" style="background: transparent; color: white; border: 1px solid rgba(255,255,255,0.5);" onclick="duplicateCampaign()">
                        <i class="fas fa-copy me-1"></i>Duplicate Campaign
                    </button>
                </div>
                @endif
            </div>
        </div>

        <div class="p-4" style="background: #f0ebf8;">
            <div class="row g-3 mb-4">
                <div class="col-6">
                    <div class="border rounded p-3 text-center h-100" style="background: #ffffff;">
                        <div class="text-muted small mb-1">Total Recipients</div>
                        <div class="fs-4 fw-bold text-primary" id="drawerRecipientsTotal">-</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="border rounded p-3 text-center h-100" style="background: #ffffff;">
                        <div class="text-muted small mb-1">Delivered</div>
                        <div class="fs-4 fw-bold text-success" id="drawerRecipientsDelivered">-</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="border rounded p-3 text-center h-100" style="background: #ffffff;">
                        <div class="text-muted small mb-1">Failed</div>
                        <div class="fs-4 fw-bold text-danger" id="drawerRecipientsFailed">-</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="border rounded p-3 text-center h-100" style="background: #ffffff;">
                        <div class="text-muted small mb-1">Delivery Rate</div>
                        <div class="fs-4 fw-bold" id="drawerDeliveryRate">-</div>
                    </div>
                </div>
            </div>

            <div class="card mb-3" style="background: #ffffff; border: none;">
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

            <div class="card mb-3" id="channelSplitCard" style="background: #ffffff; border: none;">
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

            <div class="card mb-3" id="deliveryOutcomesCard" style="background: #ffffff; border: none;">
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

            <div class="card mb-3" id="engagementMetricsCard" style="display: none; background: #ffffff; border: none;">
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

            <div class="card mb-3" id="costSummaryCard" style="display: none; background: #ffffff; border: none;">
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
                    <div class="border rounded p-3" style="background-color: #f0ebf8;">
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

            <div class="card mb-3" id="optoutSummaryCard" style="display: none; background: #ffffff; border: none;">
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

            <div class="card mb-3" id="messagePreviewCard" style="background: #ffffff; border: none;">
                <div class="card-body p-3">
                    <h6 class="text-muted mb-3"><i class="fas fa-eye me-2"></i>Message Preview</h6>
                    
                    <div class="d-flex justify-content-center" style="background-color: #ffffff; border-radius: 0.5rem; padding: 1rem;">
                        <div id="campaignPreviewContainer"></div>
                    </div>
                    
                    <div class="text-center mt-2 d-none" id="campaignPreviewToggle">
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-sm py-0 px-3 active" id="campaignPreviewRCSBtn" onclick="toggleCampaignPreview('rcs')" style="font-size: 11px; background: #886CC0; color: white; border: 1px solid #886CC0;">RCS</button>
                            <button type="button" class="btn btn-sm py-0 px-3" id="campaignPreviewSMSBtn" onclick="toggleCampaignPreview('sms')" style="font-size: 11px; background: white; color: #886CC0; border: 1px solid #886CC0;">SMS</button>
                        </div>
                    </div>
                    
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
<link rel="stylesheet" href="{{ asset('css/rcs-preview.css') }}">
<script src="{{ asset('js/rcs-preview-renderer.js') }}"></script>
<script>
/**
 * SCALABILITY NOTES
 * =================
 * Current implementation uses client-side filtering/sorting for demo purposes.
 * 
 * TODO: For 10K-1M campaigns, replace with:
 * - Server-side pagination: GET /api/campaigns?page=X&limit=50
 * - Server-side search: GET /api/campaigns?search=term
 * - Server-side filters: GET /api/campaigns?status=X&channel=Y
 * - Server-side sorting: GET /api/campaigns?sort=created_at&order=desc
 * - Lazy-load drawer data: GET /api/campaigns/{id}/dashboard
 * - Pre-aggregated metrics: Compute via scheduled jobs or materialized views
 */

var campaignDrawer = null;
var totalCampaigns = {{ count($campaigns) }};

var userPermissions = {
    canEdit: {{ $permissions['can_edit'] ? 'true' : 'false' }},
    canCancel: {{ $permissions['can_cancel'] ? 'true' : 'false' }},
    canDuplicate: {{ $permissions['can_duplicate'] ? 'true' : 'false' }},
    canExportDelivery: {{ $permissions['can_export_delivery'] ? 'true' : 'false' }},
    canExportMessages: {{ $permissions['can_export_messages'] ? 'true' : 'false' }},
    canViewAudit: {{ $permissions['can_view_audit'] ? 'true' : 'false' }}
};

document.addEventListener('DOMContentLoaded', function() {
    campaignDrawer = new bootstrap.Offcanvas(document.getElementById('campaignDrawer'));
    
    var searchInput = document.getElementById('campaignSearch');
    searchInput.addEventListener('input', filterCampaigns);
    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            filterCampaigns();
        }
    });
    
    var applyBtn = document.getElementById('btnApplyFilters');
    if (applyBtn) {
        applyBtn.addEventListener('click', applyFilters);
    }
    
    var resetBtn = document.getElementById('btnResetFilters');
    if (resetBtn) {
        resetBtn.addEventListener('click', resetFilters);
    }
    
    document.querySelectorAll('.select-all-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var dropdown = this.closest('.multiselect-dropdown');
            dropdown.querySelectorAll('input[type="checkbox"]').forEach(function(cb) {
                cb.checked = true;
            });
            updateDropdownLabel(dropdown);
        });
    });
    
    document.querySelectorAll('.clear-all-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var dropdown = this.closest('.multiselect-dropdown');
            dropdown.querySelectorAll('input[type="checkbox"]').forEach(function(cb) {
                cb.checked = false;
            });
            updateDropdownLabel(dropdown);
        });
    });
    
    document.querySelectorAll('.multiselect-dropdown input[type="checkbox"]').forEach(function(cb) {
        cb.addEventListener('change', function() {
            var dropdown = this.closest('.multiselect-dropdown');
            updateDropdownLabel(dropdown);
        });
    });
    
    loadDraftsFromStorage();
});

function loadDraftsFromStorage() {
    var drafts = JSON.parse(localStorage.getItem('quicksms_drafts') || '[]');
    if (drafts.length === 0) return;
    
    var tbody = document.getElementById('campaignsTableBody');
    
    drafts.forEach(function(draft) {
        var existingRow = document.querySelector('tr[data-id="' + draft.id + '"]');
        if (existingRow) return;
        
        var channelLabel = draft.channel === 'sms_only' ? 'SMS' : (draft.channel === 'basic_rcs' ? 'Basic RCS' : 'Rich RCS');
        var channelBadgeClass = draft.channel === 'sms_only' ? 'badge-pastel-success' : 'badge-pastel-primary';
        
        var createdDate = new Date(draft.created_at);
        var formattedDate = createdDate.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
        var formattedTime = createdDate.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' });
        
        var row = document.createElement('tr');
        row.setAttribute('data-id', draft.id);
        row.setAttribute('data-name', draft.name.toLowerCase());
        row.setAttribute('data-channel', draft.channel);
        row.setAttribute('data-status', 'draft');
        row.setAttribute('data-sender-id', draft.sender_id || '');
        row.setAttribute('data-rcs-agent', draft.rcs_agent || '');
        row.setAttribute('data-send-date', draft.created_at);
        row.setAttribute('data-recipients-total', draft.recipients);
        row.setAttribute('data-has-tracking', draft.has_tracking);
        row.setAttribute('data-has-optout', draft.has_optout);
        row.setAttribute('data-tags', '');
        row.setAttribute('data-template', draft.template || '');
        row.className = 'campaign-row';
        row.style.cursor = 'pointer';
        row.onclick = function() { openCampaignDrawer(draft.id); };
        
        row.innerHTML = '<td class="py-2">' +
            '<h6 class="mb-0 fs-6">' + escapeHtml(draft.name) + '</h6>' +
            '</td>' +
            '<td class="py-2"><span class="badge ' + channelBadgeClass + '">' + channelLabel + '</span></td>' +
            '<td class="py-2"><span class="badge" style="background-color: #e9ecef; color: #495057;"><i class="fas fa-file-alt me-1"></i>Draft</span></td>' +
            '<td class="py-2">' + draft.recipients + '</td>' +
            '<td class="py-2">' + formattedDate + '<br><small class="text-muted">' + formattedTime + '</small></td>' +
            '<td class="py-2 text-end">' +
            '<a href="/messages/send-message?edit=' + draft.id + '" class="btn btn-sm btn-outline-primary me-1" onclick="event.stopPropagation();" title="Edit Draft">' +
            '<i class="fas fa-edit"></i>' +
            '</a>' +
            '<button class="btn btn-sm btn-outline-danger" onclick="event.stopPropagation(); deleteDraft(\'' + draft.id + '\');" title="Delete Draft">' +
            '<i class="fas fa-trash"></i>' +
            '</button>' +
            '</td>';
        
        tbody.insertBefore(row, tbody.firstChild);
    });
    
    var visibleCountEl = document.getElementById('visibleCount');
    var totalCountEl = document.getElementById('totalCount');
    if (visibleCountEl && totalCountEl) {
        var totalRows = tbody.querySelectorAll('tr[data-id]').length;
        visibleCountEl.textContent = totalRows;
        totalCountEl.textContent = totalRows;
    }
}

function escapeHtml(text) {
    var div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function deleteDraft(draftId) {
    if (!confirm('Are you sure you want to delete this draft?')) return;
    
    var drafts = JSON.parse(localStorage.getItem('quicksms_drafts') || '[]');
    drafts = drafts.filter(function(d) { return d.id !== draftId; });
    localStorage.setItem('quicksms_drafts', JSON.stringify(drafts));
    
    var row = document.querySelector('tr[data-id="' + draftId + '"]');
    if (row) row.remove();
    
    showToast('Draft deleted', 'success');
    
    var tbody = document.getElementById('campaignsTableBody');
    var visibleCountEl = document.getElementById('visibleCount');
    var totalCountEl = document.getElementById('totalCount');
    if (visibleCountEl && totalCountEl && tbody) {
        var totalRows = tbody.querySelectorAll('tr[data-id]').length;
        visibleCountEl.textContent = totalRows;
        totalCountEl.textContent = totalRows;
    }
}

function updateDropdownLabel(dropdown) {
    var label = dropdown.querySelector('.dropdown-label');
    var checked = dropdown.querySelectorAll('input[type="checkbox"]:checked');
    var filter = dropdown.dataset.filter;
    
    if (checked.length === 0) {
        if (filter === 'statuses') label.textContent = 'All Statuses';
        else if (filter === 'channels') label.textContent = 'All Channels';
        else if (filter === 'senderIds') label.textContent = 'All Sender IDs';
        else if (filter === 'rcsAgents') label.textContent = 'All Agents';
        else label.textContent = 'Any';
    } else if (checked.length === 1) {
        label.textContent = checked[0].nextElementSibling.textContent;
    } else {
        label.textContent = checked.length + ' selected';
    }
}

function showToast(message, type) {
    type = type || 'info';
    var toastContainer = document.getElementById('toastContainer');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toastContainer';
        toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        toastContainer.style.zIndex = '9999';
        document.body.appendChild(toastContainer);
    }
    
    var bgClass = 'bg-primary';
    if (type === 'success') bgClass = 'bg-success';
    else if (type === 'warning') bgClass = 'bg-warning text-dark';
    else if (type === 'error' || type === 'danger') bgClass = 'bg-danger';
    
    var toastEl = document.createElement('div');
    toastEl.className = 'toast align-items-center text-white ' + bgClass + ' border-0';
    toastEl.setAttribute('role', 'alert');
    
    var toastContent = document.createElement('div');
    toastContent.className = 'd-flex';
    
    var toastBody = document.createElement('div');
    toastBody.className = 'toast-body';
    toastBody.textContent = message;
    
    var closeBtn = document.createElement('button');
    closeBtn.type = 'button';
    closeBtn.className = 'btn-close btn-close-white me-2 m-auto';
    closeBtn.setAttribute('data-bs-dismiss', 'toast');
    
    toastContent.appendChild(toastBody);
    toastContent.appendChild(closeBtn);
    toastEl.appendChild(toastContent);
    toastContainer.appendChild(toastEl);
    
    var toast = new bootstrap.Toast(toastEl, { delay: 4000 });
    toast.show();
    
    toastEl.addEventListener('hidden.bs.toast', function() {
        toastEl.remove();
    });
}

function getCheckedValues(dropdownSelector) {
    var values = [];
    var dropdown = document.querySelector(dropdownSelector);
    if (dropdown) {
        dropdown.querySelectorAll('input[type="checkbox"]:checked').forEach(function(cb) {
            values.push(cb.value);
        });
    }
    return values;
}

function filterCampaigns() {
    var searchTerm = document.getElementById('campaignSearch').value.toLowerCase().trim();
    var rows = document.querySelectorAll('#campaignsTableBody tr[data-id]');
    var visibleCount = 0;
    var hasActiveFilters = Object.values(activeFilters).some(function(v) { 
        return Array.isArray(v) ? v.length > 0 : v !== ''; 
    });
    
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
            if (activeFilters.statuses && activeFilters.statuses.length > 0 && !activeFilters.statuses.includes(status)) matchesFilters = false;
            if (activeFilters.channels && activeFilters.channels.length > 0 && !activeFilters.channels.includes(channel)) matchesFilters = false;
            if (activeFilters.senderIds && activeFilters.senderIds.length > 0 && !activeFilters.senderIds.includes(senderId)) matchesFilters = false;
            if (activeFilters.rcsAgents && activeFilters.rcsAgents.length > 0 && !activeFilters.rcsAgents.includes(rcsAgent)) matchesFilters = false;
            if (activeFilters.tracking && activeFilters.tracking.length > 0 && !activeFilters.tracking.includes(hasTracking)) matchesFilters = false;
            if (activeFilters.optout && activeFilters.optout.length > 0 && !activeFilters.optout.includes(hasOptout)) matchesFilters = false;
            
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
        statuses: getCheckedValues('[data-filter="statuses"]'),
        channels: getCheckedValues('[data-filter="channels"]'),
        senderIds: getCheckedValues('[data-filter="senderIds"]'),
        rcsAgents: getCheckedValues('[data-filter="rcsAgents"]'),
        dateFrom: document.getElementById('filterDateFrom').value,
        dateTo: document.getElementById('filterDateTo').value,
        tracking: getCheckedValues('[data-filter="tracking"]'),
        optout: getCheckedValues('[data-filter="optout"]')
    };
    
    updateFilterBadge();
    filterCampaigns();
}

function resetFilters() {
    document.querySelectorAll('.multiselect-dropdown input[type="checkbox"]').forEach(function(cb) {
        cb.checked = false;
    });
    
    document.querySelectorAll('.multiselect-dropdown .dropdown-label').forEach(function(label) {
        var dropdown = label.closest('.multiselect-dropdown');
        var filter = dropdown ? dropdown.dataset.filter : '';
        if (filter === 'statuses') label.textContent = 'All Statuses';
        else if (filter === 'channels') label.textContent = 'All Channels';
        else if (filter === 'senderIds') label.textContent = 'All Sender IDs';
        else if (filter === 'rcsAgents') label.textContent = 'All Agents';
        else label.textContent = 'Any';
    });
    
    document.getElementById('filterDateFrom').value = '';
    document.getElementById('filterDateTo').value = '';
    
    activeFilters = {};
    updateFilterBadge();
    filterCampaigns();
}

function updateFilterBadge() {
    var count = Object.values(activeFilters).filter(function(v) { 
        return Array.isArray(v) ? v.length > 0 : v !== ''; 
    }).length;
    var badge = document.getElementById('activeFiltersBadge');
    
    if (count > 0) {
        badge.textContent = count;
        badge.classList.remove('d-none');
    } else {
        badge.classList.add('d-none');
    }
}

function sortCampaigns(field, direction) {
    var tbody = document.getElementById('campaignsTableBody');
    var rows = Array.from(tbody.querySelectorAll('tr[data-id]'));
    
    rows.sort(function(a, b) {
        var result = 0;
        
        if (field === 'name') {
            var nameA = (a.dataset.name || '').toLowerCase();
            var nameB = (b.dataset.name || '').toLowerCase();
            result = nameA.localeCompare(nameB);
        } else if (field === 'status') {
            var statusOrder = { 'scheduled': 1, 'sending': 2, 'complete': 3 };
            var statusA = statusOrder[a.dataset.status] || 0;
            var statusB = statusOrder[b.dataset.status] || 0;
            result = statusA - statusB;
        } else if (field === 'recipients') {
            var recipA = parseInt(a.dataset.recipientsTotal) || 0;
            var recipB = parseInt(b.dataset.recipientsTotal) || 0;
            result = recipA - recipB;
        } else if (field === 'date') {
            var dateA = new Date(a.dataset.sendDate);
            var dateB = new Date(b.dataset.sendDate);
            result = dateA - dateB;
        }
        
        return direction === 'desc' ? -result : result;
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
            return '<span class="badge badge-pastel-secondary me-1">' + t.trim() + '</span>';
        }).join('');
        document.getElementById('drawerTags').innerHTML = tagHtml;
    } else {
        document.getElementById('drawerTags').textContent = '-';
    }

    var channelBadge = document.getElementById('drawerChannelBadge');
    channelBadge.className = 'badge';
    channelBadge.style.background = '#d4e5f7';
    channelBadge.style.color = '#2563a8';
    if (channel === 'sms_only') {
        channelBadge.textContent = 'SMS';
    } else if (channel === 'basic_rcs') {
        channelBadge.textContent = 'Basic RCS';
    } else {
        channelBadge.textContent = 'Rich RCS';
    }

    var statusBadge = document.getElementById('drawerStatusBadge');
    statusBadge.className = 'badge';
    if (status === 'scheduled') {
        statusBadge.style.background = '#fff3cd';
        statusBadge.style.color = '#856404';
        statusBadge.textContent = 'Scheduled';
    } else if (status === 'sending') {
        statusBadge.style.background = '#e0cffc';
        statusBadge.style.color = '#6f42c1';
        statusBadge.textContent = 'Sending';
    } else {
        statusBadge.style.background = '#d1f2eb';
        statusBadge.style.color = '#0d6e5a';
        statusBadge.textContent = 'Complete';
    }

    // TODO: Replace with backend validity_window field
    var liveStateBadge = document.getElementById('drawerLiveStateBadge');
    var campaignDate = new Date(sendDate);
    var now = new Date();
    var validityHours = 24;
    var expiryDate = new Date(campaignDate.getTime() + (validityHours * 60 * 60 * 1000));
    
    liveStateBadge.className = 'badge';
    if (status === 'scheduled') {
        liveStateBadge.style.background = '#e9ecef';
        liveStateBadge.style.color = '#6c757d';
        liveStateBadge.textContent = 'Pending';
    } else if (status === 'sending') {
        if (now < expiryDate) {
            liveStateBadge.style.background = '#d1f2eb';
            liveStateBadge.style.color = '#0d6e5a';
            liveStateBadge.textContent = 'Live';
        } else {
            liveStateBadge.style.background = '#e9ecef';
            liveStateBadge.style.color = '#6c757d';
            liveStateBadge.textContent = 'Expired';
        }
    } else {
        if (recipientsDelivered !== null && recipientsDelivered >= recipientsTotal) {
            liveStateBadge.style.background = '#d1f2eb';
            liveStateBadge.style.color = '#0d6e5a';
            liveStateBadge.textContent = 'Complete';
        } else if (now > expiryDate) {
            liveStateBadge.style.background = '#e9ecef';
            liveStateBadge.style.color = '#6c757d';
            liveStateBadge.textContent = 'Expired';
        } else {
            liveStateBadge.style.background = '#d1f2eb';
            liveStateBadge.style.color = '#0d6e5a';
            liveStateBadge.textContent = 'Complete';
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
        costStatusBadge.className = 'badge';
        costStatusBadge.style.cssText = 'background-color: #d4edda; color: #155724;';
        costStatusBadge.textContent = 'Final';
        costTotalLabel.textContent = 'Total';
        costDisclaimer.style.display = 'none';
    } else {
        costLabel.textContent = 'Estimated Cost';
        costStatusBadge.className = 'badge';
        costStatusBadge.style.cssText = 'background-color: #fff3cd; color: #856404;';
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

var campaignPreviewMode = 'rcs';
var currentCampaignChannel = 'sms_only';
var currentCampaignSenderId = '';
var currentCampaignRcsAgent = '';
var currentCampaignTemplate = '';

function toggleCampaignPreview(mode) {
    campaignPreviewMode = mode;
    
    var rcsBtn = document.getElementById('campaignPreviewRCSBtn');
    var smsBtn = document.getElementById('campaignPreviewSMSBtn');
    
    if (mode === 'rcs') {
        rcsBtn.classList.add('active');
        rcsBtn.style.background = '#886CC0';
        rcsBtn.style.color = 'white';
        smsBtn.classList.remove('active');
        smsBtn.style.background = 'white';
        smsBtn.style.color = '#886CC0';
    } else {
        smsBtn.classList.add('active');
        smsBtn.style.background = '#886CC0';
        smsBtn.style.color = 'white';
        rcsBtn.classList.remove('active');
        rcsBtn.style.background = 'white';
        rcsBtn.style.color = '#886CC0';
    }
    
    updateMessagePreview(currentCampaignChannel, currentCampaignSenderId, currentCampaignRcsAgent, currentCampaignTemplate);
}

function updateMessagePreview(channel, senderId, rcsAgent, template) {
    var container = document.getElementById('campaignPreviewContainer');
    var toggleContainer = document.getElementById('campaignPreviewToggle');
    
    if (!container || typeof RcsPreviewRenderer === 'undefined') {
        console.warn('Preview container or RcsPreviewRenderer not available');
        return;
    }
    
    // Store current campaign params for toggle use
    currentCampaignChannel = channel;
    currentCampaignSenderId = senderId;
    currentCampaignRcsAgent = rcsAgent;
    currentCampaignTemplate = template;
    
    // Show/hide toggle based on channel (RCS channels have SMS fallback)
    if (channel === 'basic_rcs' || channel === 'rich_rcs') {
        toggleContainer.classList.remove('d-none');
    } else {
        toggleContainer.classList.add('d-none');
        campaignPreviewMode = 'rcs'; // Reset to RCS mode for non-RCS campaigns
    }
    
    // TODO: Replace with actual message content from backend (with placeholders, never real data)
    var mockMessages = {
        'Sale Announcement': { type: 'rich_card', title: 'Flash Sale!', description: 'Hi @{{firstName}}, enjoy 30% off today!', buttons: [{label: 'Shop Now', action: {type: 'url'}}, {label: 'View Details', action: {type: 'url'}}] },
        'Flash Deal': { type: 'rich_card', title: 'Limited Offer', description: '@{{firstName}}, this deal expires soon!', buttons: [{label: 'Grab It', action: {type: 'url'}}] },
        'Reminder': { type: 'text', body: 'Hi @{{firstName}}, reminder: your appointment is tomorrow at @{{time}}. Reply STOP to opt out.' },
        'Product Showcase': { type: 'carousel', cards: [
            { title: 'New Arrivals', description: 'Check out our latest @{{category}} collection!', buttons: [{label: 'Browse', action: {type: 'url'}}] },
            { title: 'Featured Items', description: 'Handpicked just for you!', buttons: [{label: 'View', action: {type: 'url'}}] },
            { title: 'Best Sellers', description: 'Our most popular products', buttons: [{label: 'Shop', action: {type: 'url'}}] }
        ]},
        'Weekend Deal': { type: 'text', body: '@{{firstName}}, weekend special: use code SAVE20 for 20% off! Shop now: @{{link}}' },
        'VIP Invitation': { type: 'rich_card', title: 'VIP Access', description: 'Exclusive early access for you, @{{firstName}}!', buttons: [{label: 'Access Now', action: {type: 'url'}}] },
        'Shipping Update': { type: 'text', body: 'Your order #@{{orderNumber}} has shipped! Track: @{{trackingLink}}' },
        'Survey Request': { type: 'text', body: 'Hi @{{firstName}}, we value your feedback! Take our quick survey: @{{surveyLink}}' },
        'Order Confirm': { type: 'text', body: 'Order confirmed! #@{{orderNumber}} - Total: @{{amount}}. Thank you for shopping!' },
        'Appointment': { type: 'text', body: 'Reminder: @{{firstName}}, your appointment is on @{{date}} at @{{time}}.' },
        'Product Launch': { type: 'rich_card', title: 'Introducing @{{productName}}', description: 'Be the first to experience our newest innovation!', buttons: [{label: 'Pre-order', action: {type: 'url'}}, {label: 'Learn More', action: {type: 'url'}}] }
    };
    
    var defaultSms = 'Hi @{{firstName}}, thank you for being a valued customer! @{{message}} Reply STOP to opt out.';
    var defaultBasic = 'Hi @{{firstName}}, @{{message}} Tap to learn more: @{{link}}';
    
    var previewConfig = {
        senderId: senderId || 'QuickSMS',
        agent: {
            name: rcsAgent || 'QuickSMS Brand',
            logo: '{{ asset("images/rcs-agents/quicksms-brand.svg") }}',
            verified: true,
            tagline: 'Business messaging'
        }
    };
    
    var msgData = mockMessages[template];
    
    if (channel === 'sms_only') {
        previewConfig.channel = 'sms';
        previewConfig.message = {
            type: 'text',
            body: (msgData && msgData.type === 'text') ? msgData.body : defaultSms
        };
    } else if (channel === 'basic_rcs') {
        // Check if user toggled to SMS fallback view
        if (campaignPreviewMode === 'sms') {
            previewConfig.channel = 'sms';
            previewConfig.message = {
                type: 'text',
                body: (msgData && msgData.type === 'text') ? msgData.body : defaultSms
            };
        } else {
            previewConfig.channel = 'basic_rcs';
            previewConfig.message = {
                type: 'text',
                body: (msgData && msgData.type === 'text') ? msgData.body : defaultBasic
            };
        }
    } else {
        // Rich RCS - check if user toggled to SMS fallback view
        if (campaignPreviewMode === 'sms') {
            previewConfig.channel = 'sms';
            previewConfig.message = {
                type: 'text',
                body: defaultSms
            };
        } else {
            previewConfig.channel = 'rich_rcs';
            if (msgData) {
                previewConfig.message = msgData;
            } else {
                previewConfig.message = {
                    type: 'rich_card',
                    title: 'Special Offer',
                    description: 'Hi @{{firstName}}, check out this exclusive offer!',
                    buttons: [{label: 'Learn More', action: {type: 'url'}}]
                };
            }
        }
    }
    
    container.innerHTML = RcsPreviewRenderer.renderPreview(previewConfig);
    
    // Initialize carousel behavior if present
    if (previewConfig.message && previewConfig.message.type === 'carousel') {
        RcsPreviewRenderer.initCarouselBehavior('#campaignPreviewContainer');
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
    
    var pastelColors = {
        'primary': { bg: '#cfe2ff', text: '#084298' },
        'info': { bg: '#cff4fc', text: '#055160' },
        'success': { bg: '#d1e7dd', text: '#0f5132' },
        'warning': { bg: '#fff3cd', text: '#664d03' },
        'purple': { bg: '#f0ebf8', text: '#5a4a7a' },
        'danger': { bg: '#f8d7da', text: '#842029' }
    };
    
    var sourcesHtml = usedSources.map(function(src) {
        var colors = pastelColors[src.color] || pastelColors['primary'];
        return '<span class="badge" style="background-color: ' + colors.bg + '; color: ' + colors.text + ';">' +
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
    // RBAC: Check permission before export
    if (!userPermissions.canExportDelivery) {
        showComingSoon('You do not have permission to export delivery reports. Contact your administrator.');
        return;
    }
    
    // TODO: Implement delivery report export
    // - Call GET /api/campaigns/{id}/export/delivery?format=csv|xlsx
    // - Generate CSV/Excel with: recipient (masked), status, delivered_at, failed_reason, channel
    // - Log export action in audit trail for GDPR compliance
    showComingSoon('Delivery report export will generate a CSV/Excel file with delivery status for all recipients.');
}

function exportMessageLog() {
    // RBAC: Admin-only due to PII content
    if (!userPermissions.canExportMessages) {
        showComingSoon('Message log exports contain personal data and require Admin access.');
        return;
    }
    
    // TODO: Implement message log export (GDPR-sensitive)
    // - Call GET /api/campaigns/{id}/export/messages
    // - Generate detailed log: recipient, message_content (with merged PII), sent_at, status, channel, cost
    // - MANDATORY: Log PII access in audit trail
    // - MANDATORY: Include export reason/justification
    showComingSoon('Message log export will include per-recipient status, timestamps, and message content.');
}

function viewAuditLog() {
    // RBAC: Admin-only for audit access
    if (!userPermissions.canViewAudit) {
        showComingSoon('Audit log access requires Admin privileges.');
        return;
    }
    
    // TODO: Implement audit log viewer
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
    
    if (!userPermissions.canEdit) {
        showToast('You do not have permission to edit campaigns.', 'warning');
        return;
    }
    
    var editConfig = {
        mode: 'edit',
        campaignId: currentCampaignId,
        campaignName: currentCampaignName
    };
    
    sessionStorage.setItem('campaignEditConfig', JSON.stringify(editConfig));
    campaignDrawer.hide();
    
    window.location.href = '/messages/send-message?edit=' + currentCampaignId;
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
    if (!userPermissions.canCancel) {
        showToast('You do not have permission to cancel campaigns.', 'warning');
        return;
    }
    
    var row = document.querySelector('tr[data-id="' + currentCampaignId + '"]');
    if (row) {
        row.dataset.status = 'cancelled';
        var statusCell = row.querySelector('td:nth-child(3) .badge');
        if (statusCell) {
            statusCell.className = 'badge badge-pastel-secondary';
            statusCell.textContent = 'Cancelled';
        }
    }
    
    cancelCampaignModal.hide();
    campaignDrawer.hide();
    
    showToast('Campaign "' + currentCampaignName + '" has been cancelled successfully.', 'success');
    
    console.log('[CampaignHistory] Campaign cancelled:', {
        campaignId: currentCampaignId,
        campaignName: currentCampaignName,
        timestamp: new Date().toISOString()
    });
}

function duplicateCampaign() {
    if (!userPermissions.canDuplicate) {
        showToast('You do not have permission to duplicate campaigns.', 'warning');
        return;
    }
    
    var row = document.querySelector('tr[data-id="' + currentCampaignId + '"]');
    var duplicateConfig = {
        mode: 'duplicate',
        originalId: currentCampaignId,
        originalName: currentCampaignName,
        name: currentCampaignName + ' (Copy)',
        channel: row ? row.dataset.channel : '',
        senderId: row ? row.dataset.senderId : '',
        rcsAgent: row ? row.dataset.rcsAgent : '',
        template: row ? row.dataset.template : '',
        tags: row ? row.dataset.tags : ''
    };
    
    sessionStorage.setItem('campaignDuplicateConfig', JSON.stringify(duplicateConfig));
    
    campaignDrawer.hide();
    
    window.location.href = '/messages/send-message?duplicate=' + currentCampaignId;
}
</script>
@endpush
