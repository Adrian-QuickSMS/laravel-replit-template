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
                                        <th>Filename</th>
                                        <th>Module</th>
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
    { id: 1, filename: 'message_log_2026-01-15.csv', module: 'message_logs', format: 'csv', size: '2.4 MB', generated: '2026-01-15 14:30', expires: '2026-01-22 14:30', status: 'ready', generatedBy: 'John Smith', subAccount: 'Main Account', year: 2026, month: 1 },
    { id: 2, filename: 'finance_data_Q4_2025.xlsx', module: 'finance_data', format: 'xlsx', size: '1.8 MB', generated: '2025-12-14 09:15', expires: '2025-12-21 09:15', status: 'ready', generatedBy: 'Jane Doe', subAccount: 'Sub Account A', year: 2025, month: 12 },
    { id: 3, filename: 'message_log_november.csv', module: 'message_logs', format: 'csv', size: '856 KB', generated: '2025-11-13 16:45', expires: '2025-11-20 16:45', status: 'ready', generatedBy: 'John Smith', subAccount: 'Sub Account B', year: 2025, month: 11 },
    { id: 4, filename: 'finance_data_october.pdf', module: 'finance_data', format: 'pdf', size: '3.2 MB', generated: '2025-10-12 11:20', expires: '2025-10-19 11:20', status: 'ready', generatedBy: 'Mike Johnson', subAccount: 'Main Account', year: 2025, month: 10 },
    { id: 5, filename: 'message_log_archive.zip', module: 'message_logs', format: 'zip', size: '15.6 MB', generated: '2024-06-10 08:00', expires: '2024-06-17 08:00', status: 'expired', generatedBy: 'System', subAccount: 'Sub Account C', year: 2024, month: 6 },
    { id: 6, filename: 'message_log_large_export.csv', module: 'message_logs', format: 'csv', size: '—', generated: '2026-01-06 15:00', expires: '—', status: 'processing', generatedBy: 'John Smith', subAccount: 'Main Account', year: 2026, month: 1 }
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
    });
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
        html += '<td>' + formatModuleName(item.module) + '</td>';
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
