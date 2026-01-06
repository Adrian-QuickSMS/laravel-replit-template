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
                                    <div class="col-md-3">
                                        <label class="form-label small text-muted mb-1">Report Type</label>
                                        <select class="form-select form-select-sm" id="filterReportType">
                                            <option value="">All Types</option>
                                            <option value="message_log">Message Log</option>
                                            <option value="finance_data">Finance Data</option>
                                            <option value="contact_export">Contact Export</option>
                                            <option value="campaign_report">Campaign Report</option>
                                            <option value="audit_log">Audit Log</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small text-muted mb-1">File Format</label>
                                        <select class="form-select form-select-sm" id="filterFileFormat">
                                            <option value="">All Formats</option>
                                            <option value="csv">CSV</option>
                                            <option value="xlsx">Excel (XLSX)</option>
                                            <option value="pdf">PDF</option>
                                            <option value="zip">ZIP Archive</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small text-muted mb-1">Status</label>
                                        <select class="form-select form-select-sm" id="filterStatus">
                                            <option value="">All Statuses</option>
                                            <option value="ready">Ready</option>
                                            <option value="processing">Processing</option>
                                            <option value="expired">Expired</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small text-muted mb-1">Generated By</label>
                                        <select class="form-select form-select-sm" id="filterGeneratedBy">
                                            <option value="">All Users</option>
                                            <option value="current">Me Only</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row g-3 mt-1">
                                    <div class="col-md-6">
                                        <label class="form-label small text-muted mb-1">Date Range</label>
                                        <div class="d-flex gap-2 align-items-center">
                                            <input type="date" class="form-control form-control-sm" id="filterDateFrom" placeholder="From">
                                            <span class="text-muted">to</span>
                                            <input type="date" class="form-control form-control-sm" id="filterDateTo" placeholder="To">
                                        </div>
                                        <div class="mt-2 d-flex flex-wrap gap-1">
                                            <button type="button" class="date-preset-btn" data-preset="today">Today</button>
                                            <button type="button" class="date-preset-btn" data-preset="7days">Last 7 days</button>
                                            <button type="button" class="date-preset-btn" data-preset="30days">Last 30 days</button>
                                            <button type="button" class="date-preset-btn" data-preset="90days">Last 90 days</button>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small text-muted mb-1">Search</label>
                                        <input type="text" class="form-control form-control-sm" id="filterSearch" placeholder="Search by filename or description...">
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end mt-3 gap-2">
                                    <button class="btn btn-sm btn-outline-secondary" onclick="clearFilters()">Clear All</button>
                                    <button class="btn btn-sm btn-primary" onclick="applyFilters()">Apply Filters</button>
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
                                        <th>Filename</th>
                                        <th>Report Type</th>
                                        <th>Format</th>
                                        <th>Size</th>
                                        <th>Generated</th>
                                        <th>Expires</th>
                                        <th>Status</th>
                                        <th style="width: 100px;">Actions</th>
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
@endsection

@push('scripts')
<script>
var mockDownloads = [
    { id: 1, filename: 'message_log_2024-01-15.csv', reportType: 'Message Log', format: 'csv', size: '2.4 MB', generated: '2024-01-15 14:30', expires: '2024-01-22 14:30', status: 'ready', generatedBy: 'John Smith' },
    { id: 2, filename: 'finance_data_Q4_2023.xlsx', reportType: 'Finance Data', format: 'xlsx', size: '1.8 MB', generated: '2024-01-14 09:15', expires: '2024-01-21 09:15', status: 'ready', generatedBy: 'Jane Doe' },
    { id: 3, filename: 'contacts_export_all.csv', reportType: 'Contact Export', format: 'csv', size: '856 KB', generated: '2024-01-13 16:45', expires: '2024-01-20 16:45', status: 'ready', generatedBy: 'John Smith' },
    { id: 4, filename: 'campaign_report_winter_promo.pdf', reportType: 'Campaign Report', format: 'pdf', size: '3.2 MB', generated: '2024-01-12 11:20', expires: '2024-01-19 11:20', status: 'ready', generatedBy: 'Mike Johnson' },
    { id: 5, filename: 'audit_log_december.zip', reportType: 'Audit Log', format: 'zip', size: '15.6 MB', generated: '2024-01-10 08:00', expires: '2024-01-03 08:00', status: 'expired', generatedBy: 'System' },
    { id: 6, filename: 'message_log_large_export.csv', reportType: 'Message Log', format: 'csv', size: '—', generated: '2024-01-15 15:00', expires: '—', status: 'processing', generatedBy: 'John Smith' }
];

var selectedIds = [];
var currentPage = 1;
var pageSize = 25;

document.addEventListener('DOMContentLoaded', function() {
    renderDownloads();
    setupDatePresets();
});

function renderDownloads() {
    var tbody = document.getElementById('downloadsTableBody');
    var filtered = applyClientFilters(mockDownloads);
    
    if (filtered.length === 0) {
        tbody.innerHTML = '<tr><td colspan="9"><div class="empty-state"><i class="fas fa-folder-open"></i><h5>No downloads available</h5><p class="text-muted">Generated reports and exports will appear here.</p></div></td></tr>';
        document.getElementById('showingCount').textContent = '0';
        document.getElementById('totalCount').textContent = '0';
        return;
    }
    
    var html = '';
    filtered.forEach(function(item) {
        var isSelected = selectedIds.includes(item.id);
        var statusClass = 'status-' + item.status;
        var formatClass = 'file-type-' + item.format;
        var isDownloadable = item.status === 'ready';
        
        html += '<tr data-id="' + item.id + '">';
        html += '<td><input type="checkbox" class="form-check-input row-select" ' + (isSelected ? 'checked' : '') + ' onchange="toggleRowSelect(' + item.id + ')"></td>';
        html += '<td><i class="fas fa-file-alt text-muted me-2"></i>' + item.filename + '</td>';
        html += '<td>' + item.reportType + '</td>';
        html += '<td><span class="file-type-badge ' + formatClass + '">' + item.format.toUpperCase() + '</span></td>';
        html += '<td>' + item.size + '</td>';
        html += '<td>' + item.generated + '</td>';
        html += '<td>' + item.expires + '</td>';
        html += '<td><span class="status-badge ' + statusClass + '">' + capitalizeFirst(item.status) + '</span></td>';
        html += '<td>';
        if (isDownloadable) {
            html += '<button class="btn btn-sm btn-outline-primary me-1" onclick="downloadFile(' + item.id + ')" title="Download"><i class="fas fa-download"></i></button>';
        } else if (item.status === 'processing') {
            html += '<button class="btn btn-sm btn-outline-secondary me-1" disabled title="Processing"><i class="fas fa-spinner fa-spin"></i></button>';
        } else {
            html += '<button class="btn btn-sm btn-outline-secondary me-1" disabled title="Expired"><i class="fas fa-download"></i></button>';
        }
        html += '<button class="btn btn-sm btn-outline-danger" onclick="deleteFile(' + item.id + ')" title="Delete"><i class="fas fa-trash"></i></button>';
        html += '</td>';
        html += '</tr>';
    });
    
    tbody.innerHTML = html;
    document.getElementById('showingCount').textContent = filtered.length;
    document.getElementById('totalCount').textContent = mockDownloads.length;
    updateBulkActions();
}

function applyClientFilters(data) {
    var reportType = document.getElementById('filterReportType').value;
    var fileFormat = document.getElementById('filterFileFormat').value;
    var status = document.getElementById('filterStatus').value;
    var search = document.getElementById('filterSearch').value.toLowerCase();
    
    return data.filter(function(item) {
        if (reportType && item.reportType.toLowerCase().replace(/\s/g, '_') !== reportType) return false;
        if (fileFormat && item.format !== fileFormat) return false;
        if (status && item.status !== status) return false;
        if (search && !item.filename.toLowerCase().includes(search) && !item.reportType.toLowerCase().includes(search)) return false;
        return true;
    });
}

function toggleFilters() {
    var panel = document.getElementById('filtersPanel');
    panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
}

function applyFilters() {
    renderDownloads();
    updateFilterChips();
}

function clearFilters() {
    document.getElementById('filterReportType').value = '';
    document.getElementById('filterFileFormat').value = '';
    document.getElementById('filterStatus').value = '';
    document.getElementById('filterGeneratedBy').value = '';
    document.getElementById('filterDateFrom').value = '';
    document.getElementById('filterDateTo').value = '';
    document.getElementById('filterSearch').value = '';
    document.querySelectorAll('.date-preset-btn').forEach(function(btn) { btn.classList.remove('active'); });
    renderDownloads();
    document.getElementById('activeFilters').style.display = 'none';
}

function updateFilterChips() {
    var chips = [];
    var reportType = document.getElementById('filterReportType');
    var fileFormat = document.getElementById('filterFileFormat');
    var status = document.getElementById('filterStatus');
    var search = document.getElementById('filterSearch').value;
    
    if (reportType.value) chips.push({ label: reportType.options[reportType.selectedIndex].text, field: 'filterReportType' });
    if (fileFormat.value) chips.push({ label: fileFormat.options[fileFormat.selectedIndex].text, field: 'filterFileFormat' });
    if (status.value) chips.push({ label: status.options[status.selectedIndex].text, field: 'filterStatus' });
    if (search) chips.push({ label: 'Search: ' + search, field: 'filterSearch' });
    
    if (chips.length === 0) {
        document.getElementById('activeFilters').style.display = 'none';
        return;
    }
    
    var html = '';
    chips.forEach(function(chip) {
        html += '<span class="filter-chip">' + chip.label + ' <span class="remove-chip" onclick="removeFilter(\'' + chip.field + '\')">&times;</span></span>';
    });
    document.getElementById('filterChips').innerHTML = html;
    document.getElementById('activeFilters').style.display = 'block';
}

function removeFilter(field) {
    var el = document.getElementById(field);
    if (el.tagName === 'SELECT') el.value = '';
    else el.value = '';
    applyFilters();
}

function setupDatePresets() {
    document.querySelectorAll('.date-preset-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.date-preset-btn').forEach(function(b) { b.classList.remove('active'); });
            this.classList.add('active');
            
            var preset = this.dataset.preset;
            var today = new Date();
            var fromDate = new Date();
            
            if (preset === 'today') {
                fromDate = today;
            } else if (preset === '7days') {
                fromDate.setDate(today.getDate() - 7);
            } else if (preset === '30days') {
                fromDate.setDate(today.getDate() - 30);
            } else if (preset === '90days') {
                fromDate.setDate(today.getDate() - 90);
            }
            
            document.getElementById('filterDateFrom').value = fromDate.toISOString().split('T')[0];
            document.getElementById('filterDateTo').value = today.toISOString().split('T')[0];
        });
    });
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

function downloadFile(id) {
    console.log('TODO: Download file ID:', id);
    console.log('TODO: API call - GET /api/downloads/' + id + '/file');
}

function downloadSelected() {
    console.log('TODO: Download selected files:', selectedIds);
    console.log('TODO: API call - POST /api/downloads/bulk-download with IDs:', selectedIds);
}

function deleteFile(id) {
    document.getElementById('deleteCount').textContent = '1';
    selectedIds = [id];
    var modal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    modal.show();
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

function capitalizeFirst(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}
</script>
@endpush
