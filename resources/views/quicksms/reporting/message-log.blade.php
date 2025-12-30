@extends('layouts.quicksms')

@section('title', 'Message Log')

@push('styles')
<style>
#messageLogTable tbody tr {
    cursor: pointer;
}
#messageLogTable tbody tr:hover {
    background-color: rgba(111, 66, 193, 0.05);
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
.summary-bar {
    background-color: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 0.375rem;
    padding: 1rem;
}
.summary-stat {
    text-align: center;
    padding: 0.5rem;
}
.summary-stat .stat-value {
    font-size: 1.5rem;
    font-weight: 600;
    color: #6f42c1;
}
.summary-stat .stat-label {
    font-size: 0.75rem;
    color: #6c757d;
    text-transform: uppercase;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row page-titles">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('reporting') }}">Reporting</a></li>
            <li class="breadcrumb-item active">Message Log</li>
        </ol>
    </div>
    
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                    <h5 class="card-title mb-2 mb-md-0">Message Log</h5>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#filtersPanel">
                            <i class="fas fa-filter me-1"></i> Filters
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="input-group">
                            <span class="input-group-text bg-transparent"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control" id="messageSearch" placeholder="Search by recipient, sender, message content...">
                        </div>
                    </div>

                    <div class="collapse mb-3" id="filtersPanel">
                        <div class="card card-body bg-light border">
                            <div class="row g-3">
                                <div class="col-md-3 col-lg-2">
                                    <label class="form-label small fw-bold">Direction</label>
                                    <select class="form-select form-select-sm" id="filterDirection">
                                        <option value="">All Directions</option>
                                        <option value="outbound">Outbound</option>
                                        <option value="inbound">Inbound</option>
                                    </select>
                                </div>
                                <div class="col-md-3 col-lg-2">
                                    <label class="form-label small fw-bold">Status</label>
                                    <select class="form-select form-select-sm" id="filterStatus">
                                        <option value="">All Statuses</option>
                                        <option value="delivered">Delivered</option>
                                        <option value="sent">Sent</option>
                                        <option value="pending">Pending</option>
                                        <option value="failed">Failed</option>
                                        <option value="expired">Expired</option>
                                    </select>
                                </div>
                                <div class="col-md-3 col-lg-2">
                                    <label class="form-label small fw-bold">Channel</label>
                                    <select class="form-select form-select-sm" id="filterChannel">
                                        <option value="">All Channels</option>
                                        <option value="sms">SMS</option>
                                        <option value="rcs">RCS</option>
                                    </select>
                                </div>
                                <div class="col-md-3 col-lg-2">
                                    <label class="form-label small fw-bold">Sender ID</label>
                                    <select class="form-select form-select-sm" id="filterSenderId">
                                        <option value="">All Sender IDs</option>
                                        <option value="QuickSMS">QuickSMS</option>
                                        <option value="ALERTS">ALERTS</option>
                                        <option value="+447700900100">+447700900100</option>
                                    </select>
                                </div>
                                <div class="col-md-3 col-lg-2">
                                    <label class="form-label small fw-bold">Date From</label>
                                    <input type="date" class="form-control form-control-sm" id="filterDateFrom">
                                </div>
                                <div class="col-md-3 col-lg-2">
                                    <label class="form-label small fw-bold">Date To</label>
                                    <input type="date" class="form-control form-control-sm" id="filterDateTo">
                                </div>
                                <div class="col-md-3 col-lg-2">
                                    <label class="form-label small fw-bold">Campaign</label>
                                    <select class="form-select form-select-sm" id="filterCampaign">
                                        <option value="">All Campaigns</option>
                                        <option value="summer-sale">Summer Sale 2024</option>
                                        <option value="black-friday">Black Friday Promo</option>
                                        <option value="newsletter">Weekly Newsletter</option>
                                    </select>
                                </div>
                                <div class="col-md-3 col-lg-2">
                                    <label class="form-label small fw-bold">Source</label>
                                    <select class="form-select form-select-sm" id="filterSource">
                                        <option value="">All Sources</option>
                                        <option value="campaign">Campaign</option>
                                        <option value="api">API</option>
                                        <option value="inbox">Inbox Reply</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mt-3 d-flex gap-2">
                                <button type="button" class="btn btn-primary btn-sm" id="btnApplyFilters">
                                    <i class="fas fa-check me-1"></i> Apply Filters
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="btnResetFilters">
                                    <i class="fas fa-times me-1"></i> Reset Filters
                                </button>
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

                    <div class="summary-bar mb-3" id="summaryBar" style="display: none;">
                        <div class="row">
                            <div class="col-6 col-md-3 summary-stat">
                                <div class="stat-value" id="summaryTotal">0</div>
                                <div class="stat-label">Total Messages</div>
                            </div>
                            <div class="col-6 col-md-3 summary-stat">
                                <div class="stat-value text-success" id="summaryDelivered">0</div>
                                <div class="stat-label">Delivered</div>
                            </div>
                            <div class="col-6 col-md-3 summary-stat">
                                <div class="stat-value text-danger" id="summaryFailed">0</div>
                                <div class="stat-label">Failed</div>
                            </div>
                            <div class="col-6 col-md-3 summary-stat">
                                <div class="stat-value text-info" id="summaryCredits">0</div>
                                <div class="stat-label">Credits Used</div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive" id="tableContainer" style="max-height: 600px; overflow-y: auto;">
                        <table class="table table-hover mb-0" id="messageLogTable">
                            <thead class="sticky-top bg-white">
                                <tr>
                                    <th>
                                        <div class="dropdown d-inline-block">
                                            <span class="dropdown-toggle" style="cursor: pointer;" data-bs-toggle="dropdown">
                                                Date/Time <i class="fas fa-sort ms-1 text-muted"></i>
                                            </span>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="#!"><i class="fas fa-calendar-alt me-2"></i> Most Recent</a></li>
                                                <li><a class="dropdown-item" href="#!"><i class="fas fa-calendar me-2"></i> Oldest First</a></li>
                                            </ul>
                                        </div>
                                    </th>
                                    <th>Direction</th>
                                    <th>Recipient</th>
                                    <th>Sender</th>
                                    <th>Channel</th>
                                    <th>Status</th>
                                    <th>Message Preview</th>
                                    <th>Credits</th>
                                </tr>
                            </thead>
                            <tbody id="messageLogTableBody">
                                <tr>
                                    <td class="py-2">30/12/2024 14:23</td>
                                    <td class="py-2"><span class="badge bg-primary"><i class="fas fa-arrow-up me-1"></i>Out</span></td>
                                    <td class="py-2">+44 77** ***456</td>
                                    <td class="py-2">QuickSMS</td>
                                    <td class="py-2"><span class="badge bg-secondary">SMS</span></td>
                                    <td class="py-2"><span class="badge bg-success">Delivered</span></td>
                                    <td class="py-2 text-truncate" style="max-width: 200px;">Hi @{{firstName}}, your order #@{{orderNumber}} has been shipped...</td>
                                    <td class="py-2">1</td>
                                </tr>
                                <tr>
                                    <td class="py-2">30/12/2024 14:21</td>
                                    <td class="py-2"><span class="badge bg-success"><i class="fas fa-arrow-down me-1"></i>In</span></td>
                                    <td class="py-2">+44 78** ***789</td>
                                    <td class="py-2">+447700900100</td>
                                    <td class="py-2"><span class="badge bg-secondary">SMS</span></td>
                                    <td class="py-2"><span class="badge bg-info">Received</span></td>
                                    <td class="py-2 text-truncate" style="max-width: 200px;">Yes please, I'd like to confirm my appointment</td>
                                    <td class="py-2">-</td>
                                </tr>
                                <tr>
                                    <td class="py-2">30/12/2024 14:18</td>
                                    <td class="py-2"><span class="badge bg-primary"><i class="fas fa-arrow-up me-1"></i>Out</span></td>
                                    <td class="py-2">+44 79** ***123</td>
                                    <td class="py-2">ALERTS</td>
                                    <td class="py-2"><span class="badge bg-info">RCS</span></td>
                                    <td class="py-2"><span class="badge bg-success">Delivered</span></td>
                                    <td class="py-2 text-truncate" style="max-width: 200px;">Your appointment reminder for tomorrow at 10:00 AM...</td>
                                    <td class="py-2">0.5</td>
                                </tr>
                                <tr>
                                    <td class="py-2">30/12/2024 14:15</td>
                                    <td class="py-2"><span class="badge bg-primary"><i class="fas fa-arrow-up me-1"></i>Out</span></td>
                                    <td class="py-2">+44 77** ***321</td>
                                    <td class="py-2">QuickSMS</td>
                                    <td class="py-2"><span class="badge bg-secondary">SMS</span></td>
                                    <td class="py-2"><span class="badge bg-danger">Failed</span></td>
                                    <td class="py-2 text-truncate" style="max-width: 200px;">Special offer: Get 20% off your next purchase...</td>
                                    <td class="py-2">0</td>
                                </tr>
                                <tr>
                                    <td class="py-2">30/12/2024 14:12</td>
                                    <td class="py-2"><span class="badge bg-primary"><i class="fas fa-arrow-up me-1"></i>Out</span></td>
                                    <td class="py-2">+44 78** ***654</td>
                                    <td class="py-2">QuickSMS</td>
                                    <td class="py-2"><span class="badge bg-secondary">SMS</span></td>
                                    <td class="py-2"><span class="badge bg-warning text-dark">Pending</span></td>
                                    <td class="py-2 text-truncate" style="max-width: 200px;">Thank you for your order! Tracking: @{{trackingNumber}}</td>
                                    <td class="py-2">1</td>
                                </tr>
                                <tr>
                                    <td class="py-2">30/12/2024 14:08</td>
                                    <td class="py-2"><span class="badge bg-primary"><i class="fas fa-arrow-up me-1"></i>Out</span></td>
                                    <td class="py-2">+44 79** ***987</td>
                                    <td class="py-2">QuickSMS Brand</td>
                                    <td class="py-2"><span class="badge bg-info">RCS</span></td>
                                    <td class="py-2"><span class="badge bg-success">Delivered</span></td>
                                    <td class="py-2 text-truncate" style="max-width: 200px;">[Rich Card] Check out our new arrivals!</td>
                                    <td class="py-2">0.5</td>
                                </tr>
                                <tr>
                                    <td class="py-2">30/12/2024 14:05</td>
                                    <td class="py-2"><span class="badge bg-success"><i class="fas fa-arrow-down me-1"></i>In</span></td>
                                    <td class="py-2">+44 77** ***147</td>
                                    <td class="py-2">+447700900100</td>
                                    <td class="py-2"><span class="badge bg-secondary">SMS</span></td>
                                    <td class="py-2"><span class="badge bg-info">Received</span></td>
                                    <td class="py-2 text-truncate" style="max-width: 200px;">STOP</td>
                                    <td class="py-2">-</td>
                                </tr>
                                <tr>
                                    <td class="py-2">30/12/2024 14:02</td>
                                    <td class="py-2"><span class="badge bg-primary"><i class="fas fa-arrow-up me-1"></i>Out</span></td>
                                    <td class="py-2">+44 78** ***258</td>
                                    <td class="py-2">ALERTS</td>
                                    <td class="py-2"><span class="badge bg-secondary">SMS</span></td>
                                    <td class="py-2"><span class="badge bg-secondary">Expired</span></td>
                                    <td class="py-2 text-truncate" style="max-width: 200px;">Your verification code is: ******</td>
                                    <td class="py-2">1</td>
                                </tr>
                            </tbody>
                        </table>
                        <div id="noResultsState" class="text-center py-5 text-muted d-none">
                            <i class="fas fa-search fa-3x mb-3 d-block opacity-25"></i>
                            <p class="mb-2">No messages match your filters.</p>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="btnClearFiltersEmpty">
                                <i class="fas fa-times me-1"></i> Clear filters
                            </button>
                        </div>
                        <div id="loadingMore" class="text-center py-3 d-none">
                            <div class="spinner-border spinner-border-sm text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <span class="ms-2 text-muted small">Loading more messages...</span>
                        </div>
                    </div>

                    <div class="card card-body bg-light border mt-3" id="exportBar">
                        <div class="d-flex justify-content-between align-items-center flex-wrap">
                            <div class="text-muted small">
                                <i class="fas fa-info-circle me-1"></i>
                                Showing <span id="displayedCount">8</span> of <span id="totalCount">1,247</span> messages
                            </div>
                            <div class="d-flex gap-2 mt-2 mt-md-0">
                                <button type="button" class="btn btn-outline-primary btn-sm" disabled>
                                    <i class="fas fa-file-csv me-1"></i> Export CSV
                                </button>
                                <button type="button" class="btn btn-outline-primary btn-sm" disabled>
                                    <i class="fas fa-file-excel me-1"></i> Export Excel
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // TODO: Implement filter logic
    // TODO: Implement infinite scroll
    // TODO: Implement export functionality
    // TODO: Connect to backend API: GET /api/messages?page=X&limit=Y&filters=Z
    
    const btnApplyFilters = document.getElementById('btnApplyFilters');
    const btnResetFilters = document.getElementById('btnResetFilters');
    const btnClearAllFilters = document.getElementById('btnClearAllFilters');
    const btnClearFiltersEmpty = document.getElementById('btnClearFiltersEmpty');
    
    btnApplyFilters?.addEventListener('click', function() {
        console.log('TODO: Apply filters');
    });
    
    btnResetFilters?.addEventListener('click', function() {
        console.log('TODO: Reset filters');
    });
    
    btnClearAllFilters?.addEventListener('click', function() {
        console.log('TODO: Clear all filters');
    });
    
    btnClearFiltersEmpty?.addEventListener('click', function() {
        console.log('TODO: Clear filters from empty state');
    });
});
</script>
@endpush
