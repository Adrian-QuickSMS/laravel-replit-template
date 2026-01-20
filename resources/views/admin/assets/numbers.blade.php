@extends('layouts.admin')

@section('title', 'Global Numbers Library')

@push('styles')
<style>
.admin-page { padding: 1.5rem; }

.admin-filter-bar {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    align-items: flex-end;
    background: #fff;
    padding: 1rem 1.25rem;
    border-radius: 0.5rem;
    margin-bottom: 1rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
}
.admin-filter-bar .filter-group {
    display: flex;
    flex-direction: column;
    min-width: 140px;
}
.admin-filter-bar .filter-group label {
    font-size: 0.75rem;
    font-weight: 600;
    color: #6c757d;
    margin-bottom: 0.25rem;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}
.admin-filter-bar .filter-group.search-group {
    flex: 1;
    min-width: 200px;
}
.admin-btn-apply {
    background: var(--admin-primary);
    color: #fff;
    border: none;
    padding: 0.375rem 1rem;
    font-size: 0.875rem;
    font-weight: 500;
    border-radius: 0.375rem;
    height: 31px;
}
.admin-btn-apply:hover {
    background: var(--admin-secondary);
    color: #fff;
}
.admin-btn-reset {
    background: transparent;
    color: #6c757d;
    border: 1px solid #dee2e6;
    padding: 0.375rem 1rem;
    font-size: 0.875rem;
    font-weight: 500;
    border-radius: 0.375rem;
    height: 31px;
}
.admin-btn-reset:hover {
    background: #f8f9fa;
    color: #495057;
}

#numbersTable tbody tr td { padding: 0.5rem 0.75rem; vertical-align: middle; }
#numbersTable thead th { padding: 0.5rem 0.75rem; font-size: 0.8rem; font-weight: 600; color: #495057; background: #f8f9fa; }
#numbersTable tbody tr:hover { background: #f8f9fa; }

.number-value { 
    font-weight: 600; 
    color: var(--admin-primary); 
    white-space: nowrap;
}
.account-cell .account-name { 
    font-weight: 500; 
    color: #333; 
}
.account-cell .sub-account { 
    font-size: 0.75rem; 
    color: #6c757d; 
}

.sortable { cursor: pointer; user-select: none; position: relative; white-space: nowrap; }
.sortable:hover { background: #e9ecef; }
.sortable::after {
    content: '\f0dc';
    font-family: 'Font Awesome 6 Free', 'Font Awesome 5 Free';
    font-weight: 900;
    margin-left: 0.5rem;
    color: #adb5bd;
    font-size: 0.7rem;
}
.sortable.sort-asc::after { content: '\f0de'; color: var(--admin-primary); }
.sortable.sort-desc::after { content: '\f0dd'; color: var(--admin-primary); }

.badge-admin-active { background: rgba(5, 150, 105, 0.15); color: #059669; }
.badge-admin-suspended { background: rgba(220, 38, 38, 0.15); color: #dc2626; }
.badge-admin-pending { background: rgba(245, 158, 11, 0.15); color: #d97706; }
.badge-admin-portal { background: rgba(74, 144, 217, 0.15); color: var(--admin-accent); }
.badge-admin-api { background: rgba(30, 58, 95, 0.15); color: var(--admin-primary); }

.type-vmn { color: var(--admin-primary); font-weight: 500; }
.type-shortcode-keyword { color: #7c3aed; font-weight: 500; }
.type-dedicated { color: #059669; font-weight: 500; }

.cost-value { font-weight: 500; color: #333; }
.supplier-value { color: #6c757d; font-size: 0.85rem; }
.date-value { color: #6c757d; font-size: 0.85rem; }

.action-dots-btn {
    background: transparent;
    border: none;
    padding: 0.25rem 0.5rem;
    color: #6c757d;
    cursor: pointer;
}
.action-dots-btn:hover { color: var(--admin-primary); }

.table-footer {
    padding: 0.75rem 1rem;
    border-top: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #fff;
}
.table-footer .pagination-info {
    font-size: 0.85rem;
    color: #6c757d;
}
.table-footer .pagination {
    margin: 0;
}
.table-footer .page-link {
    padding: 0.35rem 0.65rem;
    font-size: 0.85rem;
    color: var(--admin-primary);
    border-color: #dee2e6;
}
.table-footer .page-item.active .page-link {
    background: var(--admin-primary);
    border-color: var(--admin-primary);
    color: #fff;
}
.table-footer .page-link:hover {
    background: #f8f9fa;
    color: var(--admin-primary);
}

.filter-chips-row {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-bottom: 1rem;
}
.filter-chip {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.75rem;
    background: rgba(74, 144, 217, 0.12);
    color: var(--admin-primary);
    border-radius: 1rem;
    font-size: 0.75rem;
    font-weight: 500;
}
.filter-chip .chip-label {
    margin-right: 0.25rem;
    color: #6c757d;
}
.filter-chip .remove-chip {
    margin-left: 0.5rem;
    cursor: pointer;
    opacity: 0.7;
    font-size: 0.7rem;
}
.filter-chip .remove-chip:hover { opacity: 1; }

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    color: #6c757d;
}
.empty-state i { font-size: 3rem; opacity: 0.3; margin-bottom: 1rem; }

.export-btn {
    background: transparent;
    border: 1px solid #dee2e6;
    color: #6c757d;
    padding: 0.375rem 0.75rem;
    font-size: 0.85rem;
    border-radius: 0.375rem;
}
.export-btn:hover {
    background: #f8f9fa;
    color: #495057;
}
</style>
@endpush

@section('content')
<div class="admin-page">
    <div class="admin-breadcrumb">
        <a href="{{ route('admin.dashboard') }}">Admin</a>
        <span class="separator">/</span>
        <a href="#">Messaging Assets</a>
        <span class="separator">/</span>
        <span>Numbers</span>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 style="color: var(--admin-primary); font-weight: 600;">Global Numbers Library</h4>
            <p class="text-muted mb-0">All numbers and keywords across the platform</p>
        </div>
        <div>
            <button class="export-btn" onclick="exportNumbers()">
                <i class="fas fa-download me-1"></i> Export
            </button>
        </div>
    </div>

    <div class="admin-filter-bar">
        <div class="filter-group">
            <label>Country</label>
            <select class="form-select form-select-sm" id="countryFilter">
                <option value="">All Countries</option>
                <option value="UK">United Kingdom</option>
                <option value="US">United States</option>
                <option value="DE">Germany</option>
                <option value="FR">France</option>
            </select>
        </div>
        <div class="filter-group">
            <label>Number Type</label>
            <select class="form-select form-select-sm" id="typeFilter">
                <option value="">All Types</option>
                <option value="vmn">VMN</option>
                <option value="shortcode_keyword">Shared Shortcode Keyword</option>
                <option value="dedicated">Dedicated Shortcode</option>
            </select>
        </div>
        <div class="filter-group">
            <label>Status</label>
            <select class="form-select form-select-sm" id="statusFilter">
                <option value="">All Statuses</option>
                <option value="active">Active</option>
                <option value="suspended">Suspended</option>
                <option value="pending">Pending</option>
            </select>
        </div>
        <div class="filter-group">
            <label>Mode</label>
            <select class="form-select form-select-sm" id="modeFilter">
                <option value="">All Modes</option>
                <option value="portal">Portal</option>
                <option value="api">API</option>
            </select>
        </div>
        <div class="filter-group">
            <label>Supplier</label>
            <select class="form-select form-select-sm" id="supplierFilter">
                <option value="">All Suppliers</option>
                <option value="sinch">Sinch</option>
                <option value="twilio">Twilio</option>
                <option value="vonage">Vonage</option>
            </select>
        </div>
        <div class="filter-group search-group">
            <label>Search</label>
            <input type="text" class="form-control form-control-sm" id="searchInput" placeholder="Number, keyword, account, sub-account...">
        </div>
        <button class="btn admin-btn-apply" onclick="applyFilters()">Apply</button>
        <button class="btn admin-btn-reset" onclick="resetFilters()">Reset</button>
    </div>

    <div class="filter-chips-row" id="activeFiltersRow" style="display: none;">
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="numbersTable">
                    <thead>
                        <tr>
                            <th class="sortable" data-sort="number">Number / Keyword</th>
                            <th class="sortable" data-sort="country">Country</th>
                            <th class="sortable" data-sort="type">Number Type</th>
                            <th class="sortable" data-sort="status">Status</th>
                            <th class="sortable" data-sort="mode">Mode</th>
                            <th class="sortable" data-sort="account">Customer Account</th>
                            <th class="sortable text-end" data-sort="cost">Monthly Cost</th>
                            <th class="sortable" data-sort="supplier">Supplier</th>
                            <th class="sortable" data-sort="created">Created Date</th>
                            <th class="text-center" style="width: 50px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="numbersTableBody">
                    </tbody>
                </table>
            </div>
            <div class="table-footer">
                <div class="pagination-info">
                    Showing <span id="showingStart">1</span>-<span id="showingEnd">20</span> of <span id="totalCount">156</span> numbers
                </div>
                <nav>
                    <ul class="pagination pagination-sm" id="tablePagination">
                        <li class="page-item disabled"><a class="page-link" href="#"><i class="fas fa-chevron-left"></i></a></li>
                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                        <li class="page-item"><a class="page-link" href="#">4</a></li>
                        <li class="page-item"><a class="page-link" href="#">...</a></li>
                        <li class="page-item"><a class="page-link" href="#">8</a></li>
                        <li class="page-item"><a class="page-link" href="#"><i class="fas fa-chevron-right"></i></a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="numberDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: var(--admin-primary); color: #fff;">
                <h5 class="modal-title"><i class="fas fa-phone-alt me-2"></i>Number Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="numberDetailsContent">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('[Admin Numbers] Initializing Global Numbers Library');
    
    initializeSorting();
    loadNumbersData();
    
    if (typeof AdminAudit !== 'undefined') {
        AdminAudit.log('NUMBERS_LIBRARY_VIEWED', {
            module: 'numbers',
            action: 'view_list'
        }, 'LOW');
    }
});

const mockNumbersData = [
    { id: 'NUM-001', number: '+447700900123', country: 'UK', type: 'vmn', status: 'active', mode: 'portal', account: 'Acme Corporation', subAccount: 'Marketing', cost: 2.00, supplier: 'Sinch', created: '2025-10-15' },
    { id: 'NUM-002', number: '+447700900456', country: 'UK', type: 'vmn', status: 'active', mode: 'api', account: 'Finance Ltd', subAccount: 'Retail', cost: 2.00, supplier: 'Sinch', created: '2025-09-20' },
    { id: 'NUM-003', number: 'PROMO', country: 'UK', type: 'shortcode_keyword', status: 'active', mode: 'portal', account: 'Acme Corporation', subAccount: 'Sales', cost: 2.00, supplier: 'Sinch', created: '2025-11-01' },
    { id: 'NUM-004', number: '+447700900789', country: 'UK', type: 'vmn', status: 'suspended', mode: 'portal', account: 'TechStart Inc', subAccount: 'Main', cost: 2.00, supplier: 'Twilio', created: '2025-08-10' },
    { id: 'NUM-005', number: '82228', country: 'UK', type: 'dedicated', status: 'active', mode: 'portal', account: 'Big Enterprise', subAccount: 'Operations', cost: 500.00, supplier: 'Vonage', created: '2024-06-15' },
    { id: 'NUM-006', number: '+447700900111', country: 'UK', type: 'vmn', status: 'pending', mode: 'api', account: 'NewClient', subAccount: 'Main', cost: 2.00, supplier: 'Sinch', created: '2026-01-18' },
    { id: 'NUM-007', number: 'SALE', country: 'UK', type: 'shortcode_keyword', status: 'active', mode: 'portal', account: 'Retail Corp', subAccount: 'Marketing', cost: 2.00, supplier: 'Sinch', created: '2025-12-05' },
    { id: 'NUM-008', number: '+447700900222', country: 'UK', type: 'vmn', status: 'active', mode: 'portal', account: 'Healthcare Plus', subAccount: 'Notifications', cost: 2.00, supplier: 'Twilio', created: '2025-07-22' },
    { id: 'NUM-009', number: '+14155551234', country: 'US', type: 'vmn', status: 'active', mode: 'api', account: 'US Branch Corp', subAccount: 'Sales', cost: 3.50, supplier: 'Twilio', created: '2025-11-10' },
    { id: 'NUM-010', number: 'HELP', country: 'UK', type: 'shortcode_keyword', status: 'active', mode: 'portal', account: 'Support Services', subAccount: 'Customer Care', cost: 2.00, supplier: 'Sinch', created: '2025-10-01' },
    { id: 'NUM-011', number: '+447700900333', country: 'UK', type: 'vmn', status: 'active', mode: 'portal', account: 'Logistics Ltd', subAccount: 'Dispatch', cost: 2.00, supplier: 'Vonage', created: '2025-09-15' },
    { id: 'NUM-012', number: '+447700900444', country: 'UK', type: 'vmn', status: 'suspended', mode: 'api', account: 'Old Account', subAccount: 'Legacy', cost: 2.00, supplier: 'Sinch', created: '2024-03-20' },
    { id: 'NUM-013', number: 'INFO', country: 'UK', type: 'shortcode_keyword', status: 'pending', mode: 'portal', account: 'Media Group', subAccount: 'News', cost: 2.00, supplier: 'Sinch', created: '2026-01-15' },
    { id: 'NUM-014', number: '+447700900555', country: 'UK', type: 'vmn', status: 'active', mode: 'portal', account: 'Banking Secure', subAccount: 'Alerts', cost: 2.00, supplier: 'Twilio', created: '2025-08-30' },
    { id: 'NUM-015', number: '+49170123456', country: 'DE', type: 'vmn', status: 'active', mode: 'api', account: 'Euro Expansion', subAccount: 'Germany', cost: 4.00, supplier: 'Vonage', created: '2025-11-20' },
    { id: 'NUM-016', number: '+447700900666', country: 'UK', type: 'vmn', status: 'active', mode: 'portal', account: 'Acme Corporation', subAccount: 'Support', cost: 2.00, supplier: 'Sinch', created: '2025-10-25' },
    { id: 'NUM-017', number: 'DEAL', country: 'UK', type: 'shortcode_keyword', status: 'active', mode: 'portal', account: 'Retail Corp', subAccount: 'Promotions', cost: 2.00, supplier: 'Sinch', created: '2025-12-10' },
    { id: 'NUM-018', number: '+447700900777', country: 'UK', type: 'vmn', status: 'active', mode: 'api', account: 'Tech Solutions', subAccount: 'API Team', cost: 2.00, supplier: 'Twilio', created: '2025-09-05' },
    { id: 'NUM-019', number: '+33612345678', country: 'FR', type: 'vmn', status: 'pending', mode: 'portal', account: 'Euro Expansion', subAccount: 'France', cost: 3.50, supplier: 'Vonage', created: '2026-01-10' },
    { id: 'NUM-020', number: '+447700900888', country: 'UK', type: 'vmn', status: 'active', mode: 'portal', account: 'Finance Ltd', subAccount: 'Alerts', cost: 2.00, supplier: 'Sinch', created: '2025-07-15' }
];

let currentPage = 1;
const rowsPerPage = 20;
let filteredData = [...mockNumbersData];
let sortColumn = 'created';
let sortDirection = 'desc';

function loadNumbersData() {
    renderTable(filteredData);
    updatePaginationInfo();
}

function renderTable(data) {
    const tbody = document.getElementById('numbersTableBody');
    
    if (data.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="10" class="empty-state">
                    <i class="fas fa-phone-slash d-block"></i>
                    <p>No numbers found matching your criteria</p>
                </td>
            </tr>
        `;
        return;
    }
    
    const start = (currentPage - 1) * rowsPerPage;
    const end = Math.min(start + rowsPerPage, data.length);
    const pageData = data.slice(start, end);
    
    tbody.innerHTML = pageData.map(num => `
        <tr data-id="${num.id}">
            <td><span class="number-value">${num.number}</span></td>
            <td>${getCountryFlag(num.country)} ${num.country}</td>
            <td>${getTypeLabel(num.type)}</td>
            <td>${getStatusBadge(num.status)}</td>
            <td>${getModeBadge(num.mode)}</td>
            <td class="account-cell">
                <div class="account-name">${num.account}</div>
                <div class="sub-account">${num.subAccount}</div>
            </td>
            <td class="text-end"><span class="cost-value">£${num.cost.toFixed(2)}</span></td>
            <td><span class="supplier-value">${num.supplier}</span></td>
            <td><span class="date-value">${formatDate(num.created)}</span></td>
            <td class="text-center">
                <div class="dropdown">
                    <button class="action-dots-btn" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#" onclick="viewNumberDetails('${num.id}')"><i class="fas fa-eye me-2"></i>View Details</a></li>
                        <li><a class="dropdown-item" href="#" onclick="viewAuditTrail('${num.id}')"><i class="fas fa-history me-2"></i>View Audit Trail</a></li>
                    </ul>
                </div>
            </td>
        </tr>
    `).join('');
}

function getCountryFlag(country) {
    const flags = { 'UK': '🇬🇧', 'US': '🇺🇸', 'DE': '🇩🇪', 'FR': '🇫🇷' };
    return flags[country] || '🌍';
}

function getTypeLabel(type) {
    const types = {
        'vmn': '<span class="type-vmn">VMN</span>',
        'shortcode_keyword': '<span class="type-shortcode-keyword">Shared Shortcode Keyword</span>',
        'dedicated': '<span class="type-dedicated">Dedicated Shortcode</span>'
    };
    return types[type] || type;
}

function getStatusBadge(status) {
    const badges = {
        'active': '<span class="badge badge-admin-active">Active</span>',
        'suspended': '<span class="badge badge-admin-suspended">Suspended</span>',
        'pending': '<span class="badge badge-admin-pending">Pending</span>'
    };
    return badges[status] || status;
}

function getModeBadge(mode) {
    const badges = {
        'portal': '<span class="badge badge-admin-portal">Portal</span>',
        'api': '<span class="badge badge-admin-api">API</span>'
    };
    return badges[mode] || mode;
}

function formatDate(dateStr) {
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
}

function applyFilters() {
    const country = document.getElementById('countryFilter').value;
    const type = document.getElementById('typeFilter').value;
    const status = document.getElementById('statusFilter').value;
    const mode = document.getElementById('modeFilter').value;
    const supplier = document.getElementById('supplierFilter').value;
    const search = document.getElementById('searchInput').value.toLowerCase();
    
    filteredData = mockNumbersData.filter(num => {
        if (country && num.country !== country) return false;
        if (type && num.type !== type) return false;
        if (status && num.status !== status) return false;
        if (mode && num.mode !== mode) return false;
        if (supplier && num.supplier.toLowerCase() !== supplier) return false;
        if (search) {
            const searchFields = [num.number, num.account, num.subAccount].join(' ').toLowerCase();
            if (!searchFields.includes(search)) return false;
        }
        return true;
    });
    
    currentPage = 1;
    renderTable(filteredData);
    updatePaginationInfo();
    updateFilterChips();
    
    if (typeof AdminAudit !== 'undefined') {
        AdminAudit.log('NUMBERS_FILTERED', {
            module: 'numbers',
            filters: { country, type, status, mode, supplier, search },
            resultCount: filteredData.length
        }, 'LOW');
    }
}

function resetFilters() {
    document.getElementById('countryFilter').value = '';
    document.getElementById('typeFilter').value = '';
    document.getElementById('statusFilter').value = '';
    document.getElementById('modeFilter').value = '';
    document.getElementById('supplierFilter').value = '';
    document.getElementById('searchInput').value = '';
    
    filteredData = [...mockNumbersData];
    currentPage = 1;
    renderTable(filteredData);
    updatePaginationInfo();
    updateFilterChips();
}

function updateFilterChips() {
    const container = document.getElementById('activeFiltersRow');
    const chips = [];
    
    const country = document.getElementById('countryFilter');
    const type = document.getElementById('typeFilter');
    const status = document.getElementById('statusFilter');
    const mode = document.getElementById('modeFilter');
    const supplier = document.getElementById('supplierFilter');
    
    if (country.value) chips.push({ label: 'Country', value: country.options[country.selectedIndex].text, filter: 'countryFilter' });
    if (type.value) chips.push({ label: 'Type', value: type.options[type.selectedIndex].text, filter: 'typeFilter' });
    if (status.value) chips.push({ label: 'Status', value: status.options[status.selectedIndex].text, filter: 'statusFilter' });
    if (mode.value) chips.push({ label: 'Mode', value: mode.options[mode.selectedIndex].text, filter: 'modeFilter' });
    if (supplier.value) chips.push({ label: 'Supplier', value: supplier.options[supplier.selectedIndex].text, filter: 'supplierFilter' });
    
    if (chips.length === 0) {
        container.style.display = 'none';
        return;
    }
    
    container.style.display = 'flex';
    container.innerHTML = chips.map(chip => `
        <span class="filter-chip">
            <span class="chip-label">${chip.label}:</span> ${chip.value}
            <i class="fas fa-times remove-chip" onclick="removeFilter('${chip.filter}')"></i>
        </span>
    `).join('');
}

function removeFilter(filterId) {
    document.getElementById(filterId).value = '';
    applyFilters();
}

function updatePaginationInfo() {
    const total = filteredData.length;
    const start = total === 0 ? 0 : (currentPage - 1) * rowsPerPage + 1;
    const end = Math.min(currentPage * rowsPerPage, total);
    
    document.getElementById('showingStart').textContent = start;
    document.getElementById('showingEnd').textContent = end;
    document.getElementById('totalCount').textContent = total;
}

function initializeSorting() {
    document.querySelectorAll('#numbersTable th.sortable').forEach(th => {
        th.addEventListener('click', function() {
            const column = this.dataset.sort;
            
            document.querySelectorAll('#numbersTable th.sortable').forEach(h => {
                h.classList.remove('sort-asc', 'sort-desc');
            });
            
            if (sortColumn === column) {
                sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                sortColumn = column;
                sortDirection = 'asc';
            }
            
            this.classList.add(sortDirection === 'asc' ? 'sort-asc' : 'sort-desc');
            
            sortTable();
        });
    });
}

function sortTable() {
    filteredData.sort((a, b) => {
        let aVal, bVal;
        
        switch (sortColumn) {
            case 'number': aVal = a.number; bVal = b.number; break;
            case 'country': aVal = a.country; bVal = b.country; break;
            case 'type': aVal = a.type; bVal = b.type; break;
            case 'status': aVal = a.status; bVal = b.status; break;
            case 'mode': aVal = a.mode; bVal = b.mode; break;
            case 'account': aVal = a.account; bVal = b.account; break;
            case 'cost': aVal = a.cost; bVal = b.cost; break;
            case 'supplier': aVal = a.supplier; bVal = b.supplier; break;
            case 'created': aVal = new Date(a.created); bVal = new Date(b.created); break;
            default: aVal = a.number; bVal = b.number;
        }
        
        if (typeof aVal === 'string') {
            return sortDirection === 'asc' ? aVal.localeCompare(bVal) : bVal.localeCompare(aVal);
        }
        return sortDirection === 'asc' ? aVal - bVal : bVal - aVal;
    });
    
    renderTable(filteredData);
}

function viewNumberDetails(numberId) {
    const num = mockNumbersData.find(n => n.id === numberId);
    if (!num) return;
    
    const content = `
        <div class="row">
            <div class="col-md-6">
                <h6 class="text-muted mb-2">Number Information</h6>
                <table class="table table-sm">
                    <tr><td class="text-muted">Number/Keyword</td><td class="fw-bold">${num.number}</td></tr>
                    <tr><td class="text-muted">Country</td><td>${getCountryFlag(num.country)} ${num.country}</td></tr>
                    <tr><td class="text-muted">Type</td><td>${getTypeLabel(num.type)}</td></tr>
                    <tr><td class="text-muted">Status</td><td>${getStatusBadge(num.status)}</td></tr>
                    <tr><td class="text-muted">Mode</td><td>${getModeBadge(num.mode)}</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6 class="text-muted mb-2">Assignment & Billing</h6>
                <table class="table table-sm">
                    <tr><td class="text-muted">Customer Account</td><td class="fw-bold">${num.account}</td></tr>
                    <tr><td class="text-muted">Sub-Account</td><td>${num.subAccount}</td></tr>
                    <tr><td class="text-muted">Monthly Cost</td><td>£${num.cost.toFixed(2)}</td></tr>
                    <tr><td class="text-muted">Supplier</td><td>${num.supplier}</td></tr>
                    <tr><td class="text-muted">Created</td><td>${formatDate(num.created)}</td></tr>
                </table>
            </div>
        </div>
    `;
    
    document.getElementById('numberDetailsContent').innerHTML = content;
    new bootstrap.Modal(document.getElementById('numberDetailsModal')).show();
    
    if (typeof AdminAudit !== 'undefined') {
        AdminAudit.log('NUMBER_DETAILS_VIEWED', {
            module: 'numbers',
            numberId: numberId,
            number: num.number,
            account: num.account
        }, 'LOW');
    }
}

function viewAuditTrail(numberId) {
    const num = mockNumbersData.find(n => n.id === numberId);
    if (!num) return;
    
    const content = `
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            Audit trail for <strong>${num.number}</strong> (${num.account})
        </div>
        <div class="list-group">
            <div class="list-group-item">
                <div class="d-flex justify-content-between">
                    <strong>Number Provisioned</strong>
                    <small class="text-muted">${formatDate(num.created)}</small>
                </div>
                <small class="text-muted">Number added to platform via supplier ${num.supplier}</small>
            </div>
            <div class="list-group-item">
                <div class="d-flex justify-content-between">
                    <strong>Assigned to Account</strong>
                    <small class="text-muted">${formatDate(num.created)}</small>
                </div>
                <small class="text-muted">Assigned to ${num.account} / ${num.subAccount}</small>
            </div>
            <div class="list-group-item">
                <div class="d-flex justify-content-between">
                    <strong>Mode Set to ${num.mode.charAt(0).toUpperCase() + num.mode.slice(1)}</strong>
                    <small class="text-muted">${formatDate(num.created)}</small>
                </div>
                <small class="text-muted">Operating mode configured</small>
            </div>
        </div>
    `;
    
    document.getElementById('numberDetailsContent').innerHTML = content;
    document.querySelector('#numberDetailsModal .modal-title').innerHTML = '<i class="fas fa-history me-2"></i>Audit Trail';
    new bootstrap.Modal(document.getElementById('numberDetailsModal')).show();
}

function exportNumbers() {
    if (typeof AdminAudit !== 'undefined') {
        AdminAudit.log('NUMBERS_EXPORTED', {
            module: 'numbers',
            recordCount: filteredData.length,
            format: 'CSV'
        }, 'MEDIUM');
    }
    
    alert('Export functionality: ' + filteredData.length + ' records would be exported to CSV.');
}
</script>
@endpush
