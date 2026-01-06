@extends('layouts.quicksms')

@section('title', 'Download Area')

@push('styles')
<style>
.download-area-container {
    height: calc(100vh - 120px);
    display: flex;
    flex-direction: column;
    overflow: hidden;
}
.download-area-container .card {
    flex: 1;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    margin-bottom: 0 !important;
}
.download-area-container .card-body {
    flex: 1;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    padding-bottom: 0;
}
.download-area-fixed-header {
    flex-shrink: 0;
    overflow: visible;
}
#filtersPanel {
    overflow: visible !important;
}
#filtersPanel .card-body {
    overflow: visible !important;
}
.download-area-table-wrapper {
    flex: 1 1 0;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    min-height: 0;
    max-height: 100%;
}
#tableContainer {
    flex: 1 1 0;
    overflow-y: auto !important;
    overflow-x: auto;
    min-height: 0;
    max-height: 100%;
}
.download-area-footer {
    flex-shrink: 0;
    margin-top: auto;
}
#downloadAreaTable {
    width: 100%;
    border-collapse: collapse;
}
#downloadAreaTable thead th {
    background-color: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
    padding: 12px 15px;
    font-weight: 600;
    color: #495057;
    white-space: nowrap;
    position: sticky;
    top: 0;
    z-index: 10;
}
#downloadAreaTable tbody tr {
    cursor: pointer;
    transition: background-color 0.15s ease;
    border-bottom: 1px solid #e9ecef;
}
#downloadAreaTable tbody tr:hover {
    background-color: rgba(136, 108, 192, 0.08) !important;
}
#downloadAreaTable tbody td {
    padding: 12px 15px;
    vertical-align: middle;
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
.file-type-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    font-weight: 500;
}
.file-type-csv { background-color: #d4edda; color: #155724; }
.file-type-xlsx { background-color: #cce5ff; color: #004085; }
.file-type-pdf { background-color: #f8d7da; color: #721c24; }
.file-type-zip { background-color: #fff3cd; color: #856404; }
.status-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    font-weight: 500;
}
.status-ready { background-color: #d4edda; color: #155724; }
.status-expired { background-color: #e2e3e5; color: #6c757d; }
.status-processing { background-color: #fff3cd; color: #856404; }
.empty-state {
    text-align: center;
    padding: 3rem;
    color: #6c757d;
}
.empty-state i {
    font-size: 4rem;
    margin-bottom: 1rem;
    opacity: 0.5;
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
    border-color: #886cc0;
}
.date-preset-btn.active {
    background: #886cc0;
    color: #fff;
    border-color: #886cc0;
}
.multiselect-dropdown {
    position: relative;
}
.multiselect-dropdown .dropdown-menu {
    position: absolute;
    top: 100%;
    left: 0;
    z-index: 1050;
    display: none;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}
.multiselect-dropdown .dropdown-menu.show {
    display: block;
}
.sortable {
    cursor: pointer;
    user-select: none;
    white-space: nowrap;
}
.sortable:hover {
    background-color: rgba(136, 108, 192, 0.08);
}
.sortable i {
    opacity: 0.4;
    transition: opacity 0.15s ease;
}
.sortable:hover i {
    opacity: 0.7;
}
.sortable.sort-asc i,
.sortable.sort-desc i {
    opacity: 1;
    color: #886cc0;
}
.action-menu {
    position: relative;
    display: inline-block;
}
.action-menu-btn {
    background: none;
    border: none;
    padding: 0.25rem 0.5rem;
    cursor: pointer;
    color: #6c757d;
    border-radius: 0.25rem;
}
.action-menu-btn:hover {
    background-color: #f8f9fa;
    color: #495057;
}
.action-dropdown {
    position: absolute;
    right: 0;
    top: 100%;
    background: #fff;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    min-width: 140px;
    z-index: 1050;
    display: none;
}
.action-dropdown.show {
    display: block;
}
.action-dropdown-item {
    display: block;
    width: 100%;
    padding: 0.5rem 1rem;
    border: none;
    background: none;
    text-align: left;
    cursor: pointer;
    font-size: 0.875rem;
    color: #212529;
}
.action-dropdown-item:hover {
    background-color: #f8f9fa;
}
.action-dropdown-item.text-danger:hover {
    background-color: #fff5f5;
}
.action-dropdown .dropdown-divider {
    height: 0;
    margin: 0.5rem 0;
    border-top: 1px solid #e9ecef;
}
.status-text-completed {
    color: #198754;
}
.status-text-failed {
    color: #dc3545;
}
.details-drawer {
    position: fixed;
    top: 0;
    right: -400px;
    width: 400px;
    height: 100%;
    background: #fff;
    box-shadow: -4px 0 20px rgba(0, 0, 0, 0.15);
    z-index: 1060;
    transition: right 0.3s ease;
    display: flex;
    flex-direction: column;
}
.details-drawer.show {
    right: 0;
}
.details-drawer-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.3);
    z-index: 1055;
    display: none;
}
.details-drawer-backdrop.show {
    display: block;
}
.details-drawer-header {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.details-drawer-body {
    flex: 1;
    overflow-y: auto;
    padding: 1.5rem;
}
.details-drawer-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid #e9ecef;
    background: #f8f9fa;
}
.detail-row {
    margin-bottom: 1rem;
}
.detail-label {
    font-size: 0.75rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.25rem;
}
.detail-value {
    font-size: 0.9375rem;
    color: #212529;
}
</style>
@endpush

@section('content')
<div class="container-fluid download-area-container">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('reporting.dashboard') }}">Reporting</a></li>
                <li class="breadcrumb-item active" aria-current="page">Download Area</li>
            </ol>
        </nav>
    </div>

    <div class="row flex-grow-1" style="min-height: 0;">
        <div class="col-12 d-flex flex-column" style="min-height: 0;">
            <div class="card">
                <div class="card-header border-0 pb-0 download-area-fixed-header">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                        <h4 class="card-title mb-0">Download Area</h4>
                        <div class="d-flex align-items-center gap-2">
                            <button class="btn btn-sm btn-outline-secondary" onclick="toggleFilters()" id="filterToggleBtn">
                                <i class="fas fa-filter me-1"></i>Filters
                            </button>
                            <button class="btn btn-sm btn-outline-secondary" onclick="refreshDownloads()">
                                <i class="fas fa-sync-alt me-1"></i>Refresh
                            </button>
                        </div>
                    </div>

                    <div id="filtersPanel" class="mb-3" style="display: none;">
                        <div class="card bg-light border-0">
                            <div class="card-body py-3">
                                <div class="row g-3">
                                    <div class="col-md-2">
                                        <label class="form-label small text-muted mb-1">Year</label>
                                        <select class="form-select form-select-sm" id="filterYear">
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label small text-muted mb-1">Month</label>
                                        <select class="form-select form-select-sm" id="filterMonth">
                                            <option value="">All Months</option>
                                            <option value="1">January</option>
                                            <option value="2">February</option>
                                            <option value="3">March</option>
                                            <option value="4">April</option>
                                            <option value="5">May</option>
                                            <option value="6">June</option>
                                            <option value="7">July</option>
                                            <option value="8">August</option>
                                            <option value="9">September</option>
                                            <option value="10">October</option>
                                            <option value="11">November</option>
                                            <option value="12">December</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label small text-muted mb-1">Module</label>
                                        <select class="form-select form-select-sm" id="filterModule">
                                            <option value="">All Modules</option>
                                            <option value="message_logs">Message Logs</option>
                                            <option value="finance_data">Finance Data</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small text-muted mb-1">Sub-account</label>
                                        <div class="multiselect-dropdown">
                                            <div class="form-control form-control-sm d-flex align-items-center justify-content-between" 
                                                 id="subAccountDropdownTrigger" 
                                                 onclick="toggleSubAccountDropdown()" 
                                                 style="cursor: pointer; min-height: 31px;">
                                                <span id="subAccountDisplayText">All Sub-accounts</span>
                                                <i class="fas fa-chevron-down small"></i>
                                            </div>
                                            <div class="dropdown-menu p-2" id="subAccountDropdown" style="min-width: 250px; max-height: 250px; overflow-y: auto;">
                                                <div class="mb-2">
                                                    <input type="text" class="form-control form-control-sm" id="subAccountSearch" placeholder="Search sub-accounts..." oninput="filterSubAccountOptions()">
                                                </div>
                                                <div class="form-check mb-1">
                                                    <input type="checkbox" class="form-check-input" id="subAccountSelectAll" onchange="toggleAllSubAccounts()">
                                                    <label class="form-check-label small" for="subAccountSelectAll">Select All</label>
                                                </div>
                                                <hr class="my-2">
                                                <div id="subAccountOptions">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small text-muted mb-1">User</label>
                                        <select class="form-select form-select-sm" id="filterUser">
                                            <option value="">All Users</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end mt-3 gap-2">
                                    <button class="btn btn-sm btn-outline-secondary" onclick="resetFilters()">
                                        <i class="fas fa-undo me-1"></i>Reset Filters
                                    </button>
                                    <button class="btn btn-sm btn-primary" onclick="applyFilters()">
                                        <i class="fas fa-check me-1"></i>Apply Filters
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="activeFilters" class="mb-2" style="display: none;">
                        <span class="text-muted small me-2">Active filters:</span>
                        <span id="filterChips"></span>
                        <a href="#" class="small text-decoration-none" onclick="clearFilters(); return false;">Clear all</a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="download-area-table-wrapper">
                        <div id="tableContainer" class="table-responsive">
                            <table class="table table-hover mb-0" id="downloadAreaTable">
                                <thead>
                                    <tr>
                                        <th style="width: 40px;">
                                            <input type="checkbox" class="form-check-input" id="selectAll" onclick="toggleSelectAll()">
                                        </th>
                                        <th class="sortable" data-sort="reportName" onclick="sortTable('reportName')">
                                            Report Name <i class="fas fa-sort text-muted ms-1"></i>
                                        </th>
                                        <th class="sortable" data-sort="module" onclick="sortTable('module')">
                                            Module <i class="fas fa-sort text-muted ms-1"></i>
                                        </th>
                                        <th class="sortable" data-sort="subAccount" onclick="sortTable('subAccount')">
                                            Sub-account <i class="fas fa-sort text-muted ms-1"></i>
                                        </th>
                                        <th class="sortable" data-sort="generatedBy" onclick="sortTable('generatedBy')">
                                            Generated By <i class="fas fa-sort text-muted ms-1"></i>
                                        </th>
                                        <th class="sortable" data-sort="dateGenerated" onclick="sortTable('dateGenerated')">
                                            Date Generated <i class="fas fa-sort text-muted ms-1"></i>
                                        </th>
                                        <th class="sortable" data-sort="fileType" onclick="sortTable('fileType')">
                                            File Type <i class="fas fa-sort text-muted ms-1"></i>
                                        </th>
                                        <th class="sortable" data-sort="fileSize" onclick="sortTable('fileSize')">
                                            File Size <i class="fas fa-sort text-muted ms-1"></i>
                                        </th>
                                        <th class="sortable" data-sort="status" onclick="sortTable('status')">
                                            Status <i class="fas fa-sort text-muted ms-1"></i>
                                        </th>
                                        <th style="width: 60px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="downloadsTableBody">
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="download-area-footer border-top pt-3 mt-3">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-3">
                                <span class="text-muted small">
                                    Showing <span id="showingCount">0</span> of <span id="totalCount">0</span> downloads
                                </span>
                                <div class="btn-group" id="bulkActions" style="display: none;">
                                    <button class="btn btn-sm btn-outline-primary" onclick="downloadSelected()">
                                        <i class="fas fa-download me-1"></i>Download Selected
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteSelected()">
                                        <i class="fas fa-trash me-1"></i>Delete Selected
                                    </button>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <select class="form-select form-select-sm" style="width: auto;" id="pageSize" onchange="changePageSize()">
                                    <option value="10">10 per page</option>
                                    <option value="25" selected>25 per page</option>
                                    <option value="50">50 per page</option>
                                    <option value="100">100 per page</option>
                                </select>
                                <nav aria-label="Downloads pagination">
                                    <ul class="pagination pagination-sm mb-0" id="pagination">
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="deleteConfirmModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">Are you sure you want to delete <span id="deleteCount">1</span> download(s)?</p>
                <p class="text-muted small mb-0 mt-2">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete()">Delete</button>
            </div>
        </div>
    </div>
</div>


<div class="details-drawer-backdrop" id="detailsDrawerBackdrop" onclick="closeDetailsDrawer()"></div>
<div class="details-drawer" id="detailsDrawer">
    <div class="details-drawer-header">
        <h5 class="mb-0">Report Details</h5>
        <button type="button" class="btn-close" onclick="closeDetailsDrawer()"></button>
    </div>
    <div class="details-drawer-body">
        <div class="detail-row">
            <div class="detail-label">Report Name</div>
            <div class="detail-value" id="detailReportName">—</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Module</div>
            <div class="detail-value" id="detailModule">—</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Sub-account</div>
            <div class="detail-value" id="detailSubAccount">—</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Generated By</div>
            <div class="detail-value" id="detailGeneratedBy">—</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Date Generated</div>
            <div class="detail-value" id="detailDateGenerated">—</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">File Type</div>
            <div class="detail-value" id="detailFileType">—</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">File Size</div>
            <div class="detail-value" id="detailFileSize">—</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Status</div>
            <div class="detail-value" id="detailStatus">—</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Report ID</div>
            <div class="detail-value text-muted" id="detailReportId">—</div>
        </div>
    </div>
    <div class="details-drawer-footer">
        <div class="d-flex gap-2">
            <button class="btn btn-primary flex-grow-1" id="detailDownloadBtn" onclick="downloadFromDetails()">
                <i class="fas fa-download me-1"></i>Download Report
            </button>
            <button class="btn btn-outline-secondary" onclick="scheduleFromDetails()">
                <i class="fas fa-clock"></i>
            </button>
        </div>
    </div>
</div>


<div class="modal fade" id="scheduleRecurringModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Schedule Recurring Report</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3">Set up automatic generation of this report on a schedule.</p>
                
                <input type="hidden" id="scheduleReportId">
                
                <div class="mb-3">
                    <label class="form-label">Report Name</label>
                    <input type="text" class="form-control" id="scheduleReportName" readonly>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Frequency</label>
                    <select class="form-select" id="scheduleFrequency" onchange="onFrequencyChange()">
                        <option value="daily">Daily</option>
                        <option value="weekly" selected>Weekly</option>
                        <option value="monthly">Monthly</option>
                        <option value="quarterly">Quarterly</option>
                    </select>
                </div>
                
                <div class="mb-3" id="weeklyOptions">
                    <label class="form-label">Day of Week</label>
                    <select class="form-select" id="scheduleDayOfWeek">
                        <option value="1">Monday</option>
                        <option value="2">Tuesday</option>
                        <option value="3">Wednesday</option>
                        <option value="4">Thursday</option>
                        <option value="5">Friday</option>
                        <option value="6">Saturday</option>
                        <option value="0">Sunday</option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Time</label>
                    <input type="time" class="form-control" id="scheduleTime" value="09:00">
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Email Recipients</label>
                    <input type="text" class="form-control" id="scheduleRecipients" placeholder="email@example.com, another@example.com">
                    <div class="form-text">Comma-separated email addresses to receive the report.</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveSchedule()">
                    <i class="fas fa-check me-1"></i>Save Schedule
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
var mockDownloads = [
    { id: 1, reportName: 'Message Logs – Jan 01-15 2026 – 143022', module: 'message_logs', subAccount: 'Main Account', generatedBy: 'John Smith', dateGenerated: '2026-01-15 14:30:22', fileType: 'CSV', fileSize: 2.4, status: 'Completed', year: 2026, month: 1 },
    { id: 2, reportName: 'Finance Data – Q4 2025 – 091545', module: 'finance_data', subAccount: 'Sub Account A', generatedBy: 'Jane Doe', dateGenerated: '2025-12-14 09:15:45', fileType: 'XLSX', fileSize: 1.8, status: 'Completed', year: 2025, month: 12 },
    { id: 3, reportName: 'Message Logs – Nov 2025 – 164512', module: 'message_logs', subAccount: 'Sub Account B', generatedBy: 'John Smith', dateGenerated: '2025-11-13 16:45:12', fileType: 'CSV', fileSize: 0.856, status: 'Completed', year: 2025, month: 11 },
    { id: 4, reportName: 'Finance Data – Oct 2025 – 112033', module: 'finance_data', subAccount: 'Main Account', generatedBy: 'Mike Johnson', dateGenerated: '2025-10-12 11:20:33', fileType: 'XLSX', fileSize: 3.2, status: 'Completed', year: 2025, month: 10 },
    { id: 5, reportName: 'Message Logs – Jun 2024 – 080011', module: 'message_logs', subAccount: 'Sub Account C', generatedBy: 'System', dateGenerated: '2024-06-10 08:00:11', fileType: 'CSV', fileSize: 15.6, status: 'Failed', year: 2024, month: 6 },
    { id: 6, reportName: 'Message Logs – Jan 01-06 2026 – 150002', module: 'message_logs', subAccount: 'Main Account', generatedBy: 'John Smith', dateGenerated: '2026-01-06 15:00:02', fileType: 'CSV', fileSize: 0, status: 'Completed', year: 2026, month: 1 },
    { id: 7, reportName: 'Finance Data – Dec 2025 – 101530', module: 'finance_data', subAccount: 'Sub Account B', generatedBy: 'Jane Doe', dateGenerated: '2025-12-20 10:15:30', fileType: 'XLSX', fileSize: 4.5, status: 'Completed', year: 2025, month: 12 },
    { id: 8, reportName: 'Message Logs – Full Year 2024 – 235959', module: 'message_logs', subAccount: 'Main Account', generatedBy: 'Mike Johnson', dateGenerated: '2024-12-31 23:59:59', fileType: 'CSV', fileSize: 128.5, status: 'Failed', year: 2024, month: 12 }
];

var mockSubAccounts = [
    { id: 'main', name: 'Main Account' },
    { id: 'sub_a', name: 'Sub Account A' },
    { id: 'sub_b', name: 'Sub Account B' },
    { id: 'sub_c', name: 'Sub Account C' },
    { id: 'sub_d', name: 'Sub Account D' }
];

var mockUsers = [
    { id: 'john', name: 'John Smith' },
    { id: 'jane', name: 'Jane Doe' },
    { id: 'mike', name: 'Mike Johnson' },
    { id: 'system', name: 'System' }
];

var selectedIds = [];
var currentPage = 1;
var pageSize = 25;
var selectedSubAccounts = [];
var sortColumn = 'dateGenerated';
var sortDirection = 'desc';
var openActionMenuId = null;

var appliedFilters = {
    year: new Date().getFullYear().toString(),
    month: '',
    module: '',
    subAccounts: [],
    user: ''
};

var pendingFilters = {
    year: new Date().getFullYear().toString(),
    month: '',
    module: '',
    subAccounts: [],
    user: ''
};

document.addEventListener('DOMContentLoaded', function() {
    initializeFilters();
    renderDownloads();
    
    document.addEventListener('click', function(e) {
        var dropdown = document.getElementById('subAccountDropdown');
        var trigger = document.getElementById('subAccountDropdownTrigger');
        if (!dropdown.contains(e.target) && !trigger.contains(e.target)) {
            dropdown.classList.remove('show');
        }
        
        if (!e.target.closest('.action-menu')) {
            closeAllActionMenus();
        }
    });
});

function renderDownloads() {
    var tbody = document.getElementById('downloadsTableBody');
    var filtered = applyClientFilters(mockDownloads);
    var sorted = sortData(filtered);
    var paginated = paginateData(sorted);
    
    if (filtered.length === 0) {
        tbody.innerHTML = '<tr><td colspan="10"><div class="empty-state"><i class="fas fa-folder-open"></i><h5>No downloads available</h5><p class="text-muted">Generated reports and exports will appear here.</p></div></td></tr>';
        document.getElementById('showingCount').textContent = '0';
        document.getElementById('totalCount').textContent = '0';
        renderPagination(0);
        return;
    }
    
    var html = '';
    paginated.forEach(function(item) {
        var isSelected = selectedIds.includes(item.id);
        var statusClass = item.status === 'Completed' ? 'status-text-completed' : 'status-text-failed';
        var fileSizeDisplay = item.fileSize > 0 ? formatFileSize(item.fileSize) : '—';
        
        html += '<tr data-id="' + item.id + '">';
        html += '<td><input type="checkbox" class="form-check-input row-select" ' + (isSelected ? 'checked' : '') + ' onchange="toggleRowSelect(' + item.id + ')"></td>';
        html += '<td>' + item.reportName + '</td>';
        html += '<td>' + formatModuleName(item.module) + '</td>';
        html += '<td>' + item.subAccount + '</td>';
        html += '<td>' + item.generatedBy + '</td>';
        html += '<td>' + formatDateTime(item.dateGenerated) + '</td>';
        html += '<td>' + item.fileType + '</td>';
        html += '<td>' + fileSizeDisplay + '</td>';
        html += '<td class="' + statusClass + '">' + item.status + '</td>';
        html += '<td>';
        html += '<div class="action-menu">';
        html += '<button class="action-menu-btn" onclick="toggleActionMenu(' + item.id + ', event)" title="Actions"><i class="fas fa-ellipsis-v"></i></button>';
        html += '<div class="action-dropdown" id="actionMenu_' + item.id + '">';
        html += '<button class="action-dropdown-item" onclick="viewReportDetails(' + item.id + ')"><i class="fas fa-info-circle me-2"></i>View Report Details</button>';
        if (item.status === 'Completed') {
            html += '<button class="action-dropdown-item" onclick="downloadReport(' + item.id + ')"><i class="fas fa-download me-2"></i>Download Report</button>';
        }
        html += '<button class="action-dropdown-item" onclick="scheduleRecurring(' + item.id + ')"><i class="fas fa-clock me-2"></i>Schedule Recurring Report</button>';
        html += '<div class="dropdown-divider"></div>';
        html += '<button class="action-dropdown-item text-danger" onclick="deleteReport(' + item.id + ')"><i class="fas fa-trash me-2"></i>Delete</button>';
        html += '</div>';
        html += '</div>';
        html += '</td>';
        html += '</tr>';
    });
    
    tbody.innerHTML = html;
    
    var startIdx = (currentPage - 1) * pageSize + 1;
    var endIdx = Math.min(currentPage * pageSize, filtered.length);
    document.getElementById('showingCount').textContent = startIdx + '-' + endIdx;
    document.getElementById('totalCount').textContent = filtered.length;
    
    renderPagination(filtered.length);
    updateBulkActions();
    updateSortIndicators();
}

function sortData(data) {
    return data.slice().sort(function(a, b) {
        var valA = a[sortColumn];
        var valB = b[sortColumn];
        
        if (sortColumn === 'fileSize') {
            valA = parseFloat(valA) || 0;
            valB = parseFloat(valB) || 0;
        } else if (sortColumn === 'dateGenerated') {
            valA = new Date(valA).getTime();
            valB = new Date(valB).getTime();
        } else if (typeof valA === 'string') {
            valA = valA.toLowerCase();
            valB = valB.toLowerCase();
        }
        
        if (valA < valB) return sortDirection === 'asc' ? -1 : 1;
        if (valA > valB) return sortDirection === 'asc' ? 1 : -1;
        return 0;
    });
}

function paginateData(data) {
    var start = (currentPage - 1) * pageSize;
    var end = start + pageSize;
    return data.slice(start, end);
}

function sortTable(column) {
    if (sortColumn === column) {
        sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
    } else {
        sortColumn = column;
        sortDirection = 'asc';
    }
    currentPage = 1;
    renderDownloads();
}

function updateSortIndicators() {
    document.querySelectorAll('.sortable').forEach(function(th) {
        th.classList.remove('sort-asc', 'sort-desc');
        var icon = th.querySelector('i');
        icon.className = 'fas fa-sort text-muted ms-1';
    });
    
    var activeHeader = document.querySelector('.sortable[data-sort="' + sortColumn + '"]');
    if (activeHeader) {
        activeHeader.classList.add(sortDirection === 'asc' ? 'sort-asc' : 'sort-desc');
        var icon = activeHeader.querySelector('i');
        icon.className = 'fas fa-sort-' + (sortDirection === 'asc' ? 'up' : 'down') + ' ms-1';
    }
}

function renderPagination(totalItems) {
    var totalPages = Math.ceil(totalItems / pageSize);
    var pagination = document.getElementById('pagination');
    
    if (totalPages <= 1) {
        pagination.innerHTML = '';
        return;
    }
    
    var html = '';
    html += '<li class="page-item ' + (currentPage === 1 ? 'disabled' : '') + '">';
    html += '<a class="page-link" href="#" onclick="goToPage(' + (currentPage - 1) + '); return false;">&laquo;</a></li>';
    
    var startPage = Math.max(1, currentPage - 2);
    var endPage = Math.min(totalPages, startPage + 4);
    if (endPage - startPage < 4) startPage = Math.max(1, endPage - 4);
    
    for (var i = startPage; i <= endPage; i++) {
        html += '<li class="page-item ' + (currentPage === i ? 'active' : '') + '">';
        html += '<a class="page-link" href="#" onclick="goToPage(' + i + '); return false;">' + i + '</a></li>';
    }
    
    html += '<li class="page-item ' + (currentPage === totalPages ? 'disabled' : '') + '">';
    html += '<a class="page-link" href="#" onclick="goToPage(' + (currentPage + 1) + '); return false;">&raquo;</a></li>';
    
    pagination.innerHTML = html;
}

function goToPage(page) {
    var totalPages = Math.ceil(applyClientFilters(mockDownloads).length / pageSize);
    if (page < 1 || page > totalPages) return;
    currentPage = page;
    renderDownloads();
}

function formatFileSize(sizeMB) {
    if (sizeMB >= 1000) {
        return (sizeMB / 1000).toFixed(1) + ' GB';
    } else if (sizeMB >= 1) {
        return sizeMB.toFixed(1) + ' MB';
    } else {
        return (sizeMB * 1024).toFixed(0) + ' KB';
    }
}

function formatDateTime(dateStr) {
    var date = new Date(dateStr);
    var day = String(date.getDate()).padStart(2, '0');
    var month = String(date.getMonth() + 1).padStart(2, '0');
    var year = date.getFullYear();
    var hours = String(date.getHours()).padStart(2, '0');
    var mins = String(date.getMinutes()).padStart(2, '0');
    return day + '/' + month + '/' + year + ' ' + hours + ':' + mins;
}

function toggleActionMenu(id, event) {
    event.stopPropagation();
    var menu = document.getElementById('actionMenu_' + id);
    var isOpen = menu.classList.contains('show');
    
    closeAllActionMenus();
    
    if (!isOpen) {
        menu.classList.add('show');
        openActionMenuId = id;
    }
}

function closeAllActionMenus() {
    document.querySelectorAll('.action-dropdown.show').forEach(function(menu) {
        menu.classList.remove('show');
    });
    openActionMenuId = null;
}

var currentDetailReportId = null;

function viewReportDetails(id) {
    closeAllActionMenus();
    var report = mockDownloads.find(function(d) { return d.id === id; });
    if (!report) return;
    
    currentDetailReportId = id;
    
    document.getElementById('detailReportName').textContent = report.reportName;
    document.getElementById('detailModule').textContent = formatModuleName(report.module);
    document.getElementById('detailSubAccount').textContent = report.subAccount;
    document.getElementById('detailGeneratedBy').textContent = report.generatedBy;
    document.getElementById('detailDateGenerated').textContent = formatDateTime(report.dateGenerated);
    document.getElementById('detailFileType').textContent = report.fileType;
    document.getElementById('detailFileSize').textContent = report.fileSize > 0 ? formatFileSize(report.fileSize) : '—';
    document.getElementById('detailStatus').textContent = report.status;
    document.getElementById('detailStatus').className = 'detail-value ' + (report.status === 'Completed' ? 'status-text-completed' : 'status-text-failed');
    document.getElementById('detailReportId').textContent = 'RPT-' + String(id).padStart(6, '0');
    
    var downloadBtn = document.getElementById('detailDownloadBtn');
    if (report.status === 'Completed') {
        downloadBtn.disabled = false;
        downloadBtn.classList.remove('btn-secondary');
        downloadBtn.classList.add('btn-primary');
    } else {
        downloadBtn.disabled = true;
        downloadBtn.classList.remove('btn-primary');
        downloadBtn.classList.add('btn-secondary');
    }
    
    document.getElementById('detailsDrawerBackdrop').classList.add('show');
    document.getElementById('detailsDrawer').classList.add('show');
    
    console.log('TODO: API call - GET /api/downloads/' + id + '/details');
}

function closeDetailsDrawer() {
    document.getElementById('detailsDrawerBackdrop').classList.remove('show');
    document.getElementById('detailsDrawer').classList.remove('show');
    currentDetailReportId = null;
}

function downloadFromDetails() {
    if (currentDetailReportId) {
        downloadReport(currentDetailReportId);
    }
}

function scheduleFromDetails() {
    if (currentDetailReportId) {
        closeDetailsDrawer();
        scheduleRecurring(currentDetailReportId);
    }
}

function downloadReport(id) {
    closeAllActionMenus();
    var report = mockDownloads.find(function(d) { return d.id === id; });
    if (!report || report.status !== 'Completed') {
        alert('This report is not available for download.');
        return;
    }
    
    console.log('TODO: Download original file for report ID:', id);
    console.log('TODO: API call - GET /api/downloads/' + id + '/file');
    console.log('File reference:', report.reportName + '.' + report.fileType.toLowerCase());
    
    alert('Download started for: ' + report.reportName);
}

function scheduleRecurring(id) {
    closeAllActionMenus();
    var report = mockDownloads.find(function(d) { return d.id === id; });
    if (!report) return;
    
    document.getElementById('scheduleReportId').value = id;
    document.getElementById('scheduleReportName').value = report.reportName;
    document.getElementById('scheduleFrequency').value = 'weekly';
    document.getElementById('scheduleDayOfWeek').value = '1';
    document.getElementById('scheduleTime').value = '09:00';
    document.getElementById('scheduleRecipients').value = '';
    
    var modal = new bootstrap.Modal(document.getElementById('scheduleRecurringModal'));
    modal.show();
    
    console.log('TODO: Load existing schedule if any for report ID:', id);
}

function onFrequencyChange() {
    var frequency = document.getElementById('scheduleFrequency').value;
    var weeklyOptions = document.getElementById('weeklyOptions');
    weeklyOptions.style.display = frequency === 'weekly' ? 'block' : 'none';
}

function saveSchedule() {
    var reportId = document.getElementById('scheduleReportId').value;
    var frequency = document.getElementById('scheduleFrequency').value;
    var dayOfWeek = document.getElementById('scheduleDayOfWeek').value;
    var time = document.getElementById('scheduleTime').value;
    var recipients = document.getElementById('scheduleRecipients').value;
    
    if (!recipients.trim()) {
        alert('Please enter at least one email recipient.');
        return;
    }
    
    var scheduleData = {
        reportId: reportId,
        frequency: frequency,
        dayOfWeek: frequency === 'weekly' ? dayOfWeek : null,
        time: time,
        recipients: recipients.split(',').map(function(e) { return e.trim(); }).filter(function(e) { return e; })
    };
    
    console.log('TODO: Save schedule:', scheduleData);
    console.log('TODO: API call - POST /api/downloads/' + reportId + '/schedule');
    
    bootstrap.Modal.getInstance(document.getElementById('scheduleRecurringModal')).hide();
    alert('Schedule saved successfully!');
}

function deleteReport(id) {
    closeAllActionMenus();
    document.getElementById('deleteCount').textContent = '1';
    selectedIds = [id];
    var modal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    modal.show();
}

function initializeFilters() {
    var yearSelect = document.getElementById('filterYear');
    var currentYear = new Date().getFullYear();
    var yearHtml = '';
    for (var y = currentYear; y >= currentYear - 5; y--) {
        yearHtml += '<option value="' + y + '"' + (y === currentYear ? ' selected' : '') + '>' + y + '</option>';
    }
    yearSelect.innerHTML = yearHtml;
    
    var userSelect = document.getElementById('filterUser');
    var userHtml = '<option value="">All Users</option>';
    mockUsers.forEach(function(user) {
        userHtml += '<option value="' + user.id + '">' + user.name + '</option>';
    });
    userSelect.innerHTML = userHtml;
    
    renderSubAccountOptions();
    
    pendingFilters.year = currentYear.toString();
    appliedFilters.year = currentYear.toString();
}

function renderSubAccountOptions() {
    var container = document.getElementById('subAccountOptions');
    var html = '';
    mockSubAccounts.forEach(function(acc) {
        var isChecked = selectedSubAccounts.includes(acc.id);
        html += '<div class="form-check mb-1 sub-account-option" data-name="' + acc.name.toLowerCase() + '">';
        html += '<input type="checkbox" class="form-check-input sub-account-cb" id="subAcc_' + acc.id + '" value="' + acc.id + '" ' + (isChecked ? 'checked' : '') + ' onchange="updateSubAccountSelection()">';
        html += '<label class="form-check-label small" for="subAcc_' + acc.id + '">' + acc.name + '</label>';
        html += '</div>';
    });
    container.innerHTML = html;
    updateSubAccountDisplayText();
}

function toggleSubAccountDropdown() {
    var dropdown = document.getElementById('subAccountDropdown');
    dropdown.classList.toggle('show');
}

function filterSubAccountOptions() {
    var search = document.getElementById('subAccountSearch').value.toLowerCase();
    document.querySelectorAll('.sub-account-option').forEach(function(opt) {
        var name = opt.dataset.name;
        opt.style.display = name.includes(search) ? 'block' : 'none';
    });
}

function toggleAllSubAccounts() {
    var selectAll = document.getElementById('subAccountSelectAll').checked;
    selectedSubAccounts = selectAll ? mockSubAccounts.map(function(a) { return a.id; }) : [];
    document.querySelectorAll('.sub-account-cb').forEach(function(cb) {
        cb.checked = selectAll;
    });
    updateSubAccountDisplayText();
}

function updateSubAccountSelection() {
    selectedSubAccounts = [];
    document.querySelectorAll('.sub-account-cb:checked').forEach(function(cb) {
        selectedSubAccounts.push(cb.value);
    });
    document.getElementById('subAccountSelectAll').checked = selectedSubAccounts.length === mockSubAccounts.length;
    updateSubAccountDisplayText();
}

function updateSubAccountDisplayText() {
    var display = document.getElementById('subAccountDisplayText');
    if (selectedSubAccounts.length === 0) {
        display.textContent = 'All Sub-accounts';
    } else if (selectedSubAccounts.length === mockSubAccounts.length) {
        display.textContent = 'All Sub-accounts';
    } else if (selectedSubAccounts.length === 1) {
        var acc = mockSubAccounts.find(function(a) { return a.id === selectedSubAccounts[0]; });
        display.textContent = acc ? acc.name : '1 selected';
    } else {
        display.textContent = selectedSubAccounts.length + ' selected';
    }
}

function applyClientFilters(data) {
    return data.filter(function(item) {
        if (appliedFilters.year && item.year !== parseInt(appliedFilters.year)) return false;
        if (appliedFilters.month && item.month !== parseInt(appliedFilters.month)) return false;
        if (appliedFilters.module && item.module !== appliedFilters.module) return false;
        if (appliedFilters.subAccounts.length > 0) {
            var matchesSubAccount = appliedFilters.subAccounts.some(function(subId) {
                var acc = mockSubAccounts.find(function(a) { return a.id === subId; });
                return acc && item.subAccount === acc.name;
            });
            if (!matchesSubAccount) return false;
        }
        if (appliedFilters.user) {
            var user = mockUsers.find(function(u) { return u.id === appliedFilters.user; });
            if (user && item.generatedBy !== user.name) return false;
        }
        return true;
    });
}

function toggleFilters() {
    var panel = document.getElementById('filtersPanel');
    panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
}

function applyFilters() {
    pendingFilters.year = document.getElementById('filterYear').value;
    pendingFilters.month = document.getElementById('filterMonth').value;
    pendingFilters.module = document.getElementById('filterModule').value;
    pendingFilters.subAccounts = selectedSubAccounts.slice();
    pendingFilters.user = document.getElementById('filterUser').value;
    
    appliedFilters = JSON.parse(JSON.stringify(pendingFilters));
    
    console.log('TODO: API call - GET /api/downloads with filters:', appliedFilters);
    
    renderDownloads();
    updateFilterChips();
}

function resetFilters() {
    var currentYear = new Date().getFullYear();
    
    document.getElementById('filterYear').value = currentYear.toString();
    document.getElementById('filterMonth').value = '';
    document.getElementById('filterModule').value = '';
    document.getElementById('filterUser').value = '';
    
    selectedSubAccounts = [];
    document.querySelectorAll('.sub-account-cb').forEach(function(cb) { cb.checked = false; });
    document.getElementById('subAccountSelectAll').checked = false;
    updateSubAccountDisplayText();
    
    pendingFilters = {
        year: currentYear.toString(),
        month: '',
        module: '',
        subAccounts: [],
        user: ''
    };
    appliedFilters = JSON.parse(JSON.stringify(pendingFilters));
    
    renderDownloads();
    document.getElementById('activeFilters').style.display = 'none';
}

function updateFilterChips() {
    var chips = [];
    var yearSelect = document.getElementById('filterYear');
    var monthSelect = document.getElementById('filterMonth');
    var moduleSelect = document.getElementById('filterModule');
    var userSelect = document.getElementById('filterUser');
    
    var currentYear = new Date().getFullYear().toString();
    if (appliedFilters.year && appliedFilters.year !== currentYear) {
        chips.push({ label: 'Year: ' + appliedFilters.year, field: 'filterYear', value: currentYear });
    }
    if (appliedFilters.month) {
        chips.push({ label: monthSelect.options[monthSelect.selectedIndex].text, field: 'filterMonth', value: '' });
    }
    if (appliedFilters.module) {
        chips.push({ label: moduleSelect.options[moduleSelect.selectedIndex].text, field: 'filterModule', value: '' });
    }
    if (appliedFilters.subAccounts.length > 0 && appliedFilters.subAccounts.length < mockSubAccounts.length) {
        chips.push({ label: appliedFilters.subAccounts.length + ' Sub-account(s)', field: 'subAccounts', value: '' });
    }
    if (appliedFilters.user) {
        chips.push({ label: userSelect.options[userSelect.selectedIndex].text, field: 'filterUser', value: '' });
    }
    
    if (chips.length === 0) {
        document.getElementById('activeFilters').style.display = 'none';
        return;
    }
    
    var html = '';
    chips.forEach(function(chip) {
        html += '<span class="filter-chip">' + chip.label + ' <span class="remove-chip" onclick="removeFilterChip(\'' + chip.field + '\', \'' + chip.value + '\')">&times;</span></span>';
    });
    document.getElementById('filterChips').innerHTML = html;
    document.getElementById('activeFilters').style.display = 'block';
}

function removeFilterChip(field, defaultValue) {
    if (field === 'subAccounts') {
        selectedSubAccounts = [];
        document.querySelectorAll('.sub-account-cb').forEach(function(cb) { cb.checked = false; });
        document.getElementById('subAccountSelectAll').checked = false;
        updateSubAccountDisplayText();
        appliedFilters.subAccounts = [];
    } else {
        var el = document.getElementById(field);
        el.value = defaultValue;
        appliedFilters[field.replace('filter', '').toLowerCase()] = defaultValue;
    }
    renderDownloads();
    updateFilterChips();
}

function toggleSelectAll() {
    var checked = document.getElementById('selectAll').checked;
    var filtered = applyClientFilters(mockDownloads);
    selectedIds = checked ? filtered.map(function(d) { return d.id; }) : [];
    renderDownloads();
}

function toggleRowSelect(id) {
    var idx = selectedIds.indexOf(id);
    if (idx > -1) selectedIds.splice(idx, 1);
    else selectedIds.push(id);
    updateBulkActions();
}

function updateBulkActions() {
    document.getElementById('bulkActions').style.display = selectedIds.length > 0 ? 'flex' : 'none';
}

function downloadSelected() {
    var completedIds = selectedIds.filter(function(id) {
        var report = mockDownloads.find(function(d) { return d.id === id; });
        return report && report.status === 'Completed';
    });
    
    if (completedIds.length === 0) {
        alert('No completed reports selected for download.');
        return;
    }
    
    console.log('TODO: Download selected files:', completedIds);
    console.log('TODO: API call - POST /api/downloads/bulk-download with IDs:', completedIds);
    alert('Downloading ' + completedIds.length + ' report(s)...');
}

function deleteSelected() {
    document.getElementById('deleteCount').textContent = selectedIds.length;
    var modal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    modal.show();
}

function confirmDelete() {
    console.log('TODO: Delete files:', selectedIds);
    console.log('TODO: API call - DELETE /api/downloads/bulk with IDs:', selectedIds);
    mockDownloads = mockDownloads.filter(function(d) { return !selectedIds.includes(d.id); });
    selectedIds = [];
    bootstrap.Modal.getInstance(document.getElementById('deleteConfirmModal')).hide();
    renderDownloads();
}

function refreshDownloads() {
    console.log('TODO: Refresh downloads list');
    console.log('TODO: API call - GET /api/downloads');
    renderDownloads();
}

function changePageSize() {
    pageSize = parseInt(document.getElementById('pageSize').value);
    currentPage = 1;
    renderDownloads();
}

function formatModuleName(module) {
    var moduleNames = {
        'message_logs': 'Message Logs',
        'finance_data': 'Finance Data'
    };
    return moduleNames[module] || module;
}

function capitalizeFirst(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}
</script>
@endpush
