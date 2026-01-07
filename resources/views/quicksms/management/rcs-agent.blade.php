@extends('layouts.quicksms')

@section('title', 'RCS Agent Library')

@push('styles')
<style>
.rcs-agents-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}
.rcs-agents-header h2 {
    margin: 0;
    font-weight: 600;
}
.rcs-agents-header p {
    margin: 0;
    color: #6c757d;
}
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    background: #fff;
    border-radius: 0.75rem;
    border: 1px solid #e9ecef;
}
.empty-state-icon {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: rgba(136, 108, 192, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
}
.empty-state-icon i {
    font-size: 2rem;
    color: var(--primary);
}
.empty-state h4 {
    margin-bottom: 0.5rem;
    color: #343a40;
}
.empty-state p {
    color: #6c757d;
    margin-bottom: 1.5rem;
    max-width: 400px;
    margin-left: auto;
    margin-right: auto;
}
.agents-table-container {
    background: #fff;
    border-radius: 0.75rem;
    border: 1px solid #e9ecef;
    overflow-x: auto;
}
.agents-table {
    width: 100%;
    margin: 0;
    min-width: 900px;
    table-layout: fixed;
}
.agents-table thead th {
    background: #f8f9fa;
    padding: 0.75rem 0.5rem;
    font-weight: 600;
    font-size: 0.8rem;
    color: #495057;
    border-bottom: 1px solid #e9ecef;
    cursor: pointer;
    white-space: nowrap;
    user-select: none;
}
.agents-table thead th:first-child { width: 22%; }
.agents-table thead th:nth-child(2) { width: 12%; }
.agents-table thead th:nth-child(3) { width: 16%; }
.agents-table thead th:nth-child(4) { width: 14%; }
.agents-table thead th:nth-child(5) { width: 12%; }
.agents-table thead th:nth-child(6) { width: 12%; }
.agents-table thead th:last-child { 
    width: 7%; 
    position: sticky;
    right: 0;
    background: #f8f9fa;
    z-index: 2;
    cursor: default;
}
.agents-table thead th:hover {
    background: #e9ecef;
}
.agents-table thead th:last-child:hover {
    background: #f8f9fa;
}
.agents-table thead th .sort-icon {
    margin-left: 0.25rem;
    opacity: 0.4;
}
.agents-table thead th.sorted .sort-icon {
    opacity: 1;
    color: var(--primary);
}
.agents-table tbody td {
    padding: 0.75rem 0.5rem;
    vertical-align: middle;
    border-bottom: 1px solid #f1f3f5;
    font-size: 0.85rem;
}
.agents-table tbody td:last-child {
    position: sticky;
    right: 0;
    background: #fff;
    z-index: 1;
    box-shadow: -2px 0 4px rgba(0,0,0,0.05);
}
.agents-table tbody tr:last-child td {
    border-bottom: none;
}
.agents-table tbody tr:hover td {
    background: #f8f9fa;
}
.agents-table tbody tr:hover td:last-child {
    background: #f8f9fa;
}
.agent-name {
    font-weight: 500;
    color: #343a40;
}
.badge-draft {
    background: rgba(108, 117, 125, 0.15);
    color: #6c757d;
}
.badge-submitted {
    background: rgba(48, 101, 208, 0.15);
    color: #3065D0;
}
.badge-in-review {
    background: rgba(255, 191, 0, 0.15);
    color: #cc9900;
}
.badge-approved {
    background: rgba(28, 187, 140, 0.15);
    color: #1cbb8c;
}
.badge-rejected {
    background: rgba(220, 53, 69, 0.15);
    color: #dc3545;
}
.badge-conversational {
    background: rgba(136, 108, 192, 0.15);
    color: #886CC0;
}
.badge-non-conversational {
    background: rgba(108, 117, 125, 0.15);
    color: #6c757d;
}
.search-filter-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    border-bottom: 1px solid #e9ecef;
    gap: 1rem;
    flex-wrap: wrap;
}
.search-box {
    flex: 1;
    max-width: 300px;
    min-width: 200px;
}
.filters-group {
    display: flex;
    gap: 0.5rem;
    align-items: center;
    flex-wrap: wrap;
}
.action-menu .dropdown-item {
    font-size: 0.85rem;
    padding: 0.5rem 1rem;
}
.action-menu .dropdown-item i {
    width: 18px;
    margin-right: 0.5rem;
}
.action-menu .dropdown-item.disabled {
    color: #adb5bd;
    pointer-events: none;
}
.date-text {
    font-size: 0.85rem;
    color: #495057;
}
.use-case-text {
    font-size: 0.85rem;
    color: #495057;
}
.pagination-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    border-top: 1px solid #e9ecef;
    flex-wrap: wrap;
    gap: 1rem;
}
.pagination-info {
    font-size: 0.85rem;
    color: #6c757d;
}
.pagination-controls {
    display: flex;
    gap: 0.25rem;
}
.pagination-controls .btn {
    padding: 0.375rem 0.75rem;
    font-size: 0.85rem;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="rcs-agents-header">
        <div>
            <h2>RCS Agent Library</h2>
            <p>View, manage, and track all RCS Agents for your account</p>
        </div>
        <button class="btn btn-primary" id="createAgentBtn">
            <i class="fas fa-plus me-2"></i>Create RCS Agent
        </button>
    </div>

    <div class="agents-table-container" id="agentsTableContainer">
        <div class="search-filter-bar">
            <div class="search-box">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0">
                        <i class="fas fa-search text-muted"></i>
                    </span>
                    <input type="text" class="form-control border-start-0" id="searchInput" placeholder="Search agents...">
                </div>
            </div>
            <div class="filters-group">
                <select class="form-select form-select-sm" id="statusFilter" style="width: auto;">
                    <option value="">All Statuses</option>
                    <option value="draft">Draft</option>
                    <option value="submitted">Submitted</option>
                    <option value="in-review">In Review</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                </select>
                <select class="form-select form-select-sm" id="billingFilter" style="width: auto;">
                    <option value="">All Billing Types</option>
                    <option value="conversational">Conversational</option>
                    <option value="non-conversational">Non-conversational</option>
                </select>
                <select class="form-select form-select-sm" id="useCaseFilter" style="width: auto;">
                    <option value="">All Use Cases</option>
                    <option value="otp">OTP</option>
                    <option value="transactional">Transactional</option>
                    <option value="promotional">Promotional</option>
                    <option value="multi-use">Multi-use</option>
                </select>
            </div>
        </div>

        <table class="agents-table" id="agentsTable">
            <thead>
                <tr>
                    <th data-sort="name" onclick="sortTable('name')">Agent Name <i class="fas fa-sort sort-icon"></i></th>
                    <th data-sort="status" onclick="sortTable('status')">Status <i class="fas fa-sort sort-icon"></i></th>
                    <th data-sort="billing" onclick="sortTable('billing')">Billing Category <i class="fas fa-sort sort-icon"></i></th>
                    <th data-sort="useCase" onclick="sortTable('useCase')">Use Case <i class="fas fa-sort sort-icon"></i></th>
                    <th data-sort="created" onclick="sortTable('created')">Created <i class="fas fa-sort sort-icon"></i></th>
                    <th data-sort="updated" onclick="sortTable('updated')">Last Updated <i class="fas fa-sort sort-icon"></i></th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="agentsTableBody">
            </tbody>
        </table>

        <div class="pagination-bar">
            <div class="pagination-info">
                Showing <span id="showingStart">1</span>-<span id="showingEnd">10</span> of <span id="totalCount">0</span> agents
            </div>
            <div class="pagination-controls">
                <button class="btn btn-outline-secondary btn-sm" id="prevPageBtn" disabled>
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="btn btn-outline-secondary btn-sm" id="nextPageBtn">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>

    <div class="empty-state d-none" id="emptyState">
        <div class="empty-state-icon">
            <i class="fas fa-robot"></i>
        </div>
        <h4>No RCS Agents Yet</h4>
        <p>Create your first RCS Agent to enable rich messaging experiences for your customers.</p>
        <button class="btn btn-primary" id="createAgentEmptyBtn">
            <i class="fas fa-plus me-2"></i>Create RCS Agent
        </button>
    </div>
</div>

<div class="modal fade" id="viewAgentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">View RCS Agent</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label small text-muted">Agent Name</label>
                        <p class="fw-semibold mb-0" id="viewAgentName"></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small text-muted">Status</label>
                        <p class="mb-0" id="viewAgentStatus"></p>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label small text-muted">Billing Category</label>
                        <p class="mb-0" id="viewAgentBilling"></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small text-muted">Use Case</label>
                        <p class="mb-0" id="viewAgentUseCase"></p>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label small text-muted">Created Date</label>
                        <p class="mb-0" id="viewAgentCreated"></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small text-muted">Last Updated</label>
                        <p class="mb-0" id="viewAgentUpdated"></p>
                    </div>
                </div>
                <div class="mb-3" id="viewRejectionReasonContainer" style="display: none;">
                    <label class="form-label small text-muted">Rejection Reason</label>
                    <div class="border rounded p-3 bg-white">
                        <p class="mb-0 text-danger" id="viewAgentRejectionReason"></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-info" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="resubmitAgentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Resubmit RCS Agent</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-pastel-primary mb-3">
                    You are about to resubmit <strong id="resubmitAgentName"></strong> for review.
                </div>
                <p class="text-muted">The agent will be placed back in the review queue. You will be notified once a decision has been made.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-info" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmResubmitBtn">Resubmit for Review</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
var mockAgents = [
    {
        id: 'agent-001',
        name: 'QuickSMS Notifications',
        status: 'approved',
        billing: 'conversational',
        useCase: 'transactional',
        created: '2025-09-15',
        updated: '2025-10-02',
        rejectionReason: null
    },
    {
        id: 'agent-002',
        name: 'Marketing Campaigns',
        status: 'approved',
        billing: 'non-conversational',
        useCase: 'promotional',
        created: '2025-08-20',
        updated: '2025-09-10',
        rejectionReason: null
    },
    {
        id: 'agent-003',
        name: 'OTP Verification',
        status: 'in-review',
        billing: 'non-conversational',
        useCase: 'otp',
        created: '2025-12-01',
        updated: '2025-12-01',
        rejectionReason: null
    },
    {
        id: 'agent-004',
        name: 'Customer Support Bot',
        status: 'submitted',
        billing: 'conversational',
        useCase: 'multi-use',
        created: '2025-12-28',
        updated: '2025-12-28',
        rejectionReason: null
    },
    {
        id: 'agent-005',
        name: 'Holiday Promotions',
        status: 'rejected',
        billing: 'non-conversational',
        useCase: 'promotional',
        created: '2025-11-15',
        updated: '2025-11-20',
        rejectionReason: 'Brand logo does not meet minimum resolution requirements. Please upload a logo with at least 224x224 pixels.'
    },
    {
        id: 'agent-006',
        name: 'Appointment Reminders',
        status: 'draft',
        billing: 'non-conversational',
        useCase: 'transactional',
        created: '2026-01-05',
        updated: '2026-01-05',
        rejectionReason: null
    },
    {
        id: 'agent-007',
        name: 'Order Updates',
        status: 'approved',
        billing: 'non-conversational',
        useCase: 'transactional',
        created: '2025-07-10',
        updated: '2025-08-15',
        rejectionReason: null
    },
    {
        id: 'agent-008',
        name: 'Loyalty Program',
        status: 'draft',
        billing: 'conversational',
        useCase: 'promotional',
        created: '2026-01-02',
        updated: '2026-01-06',
        rejectionReason: null
    }
];

var filteredAgents = [...mockAgents];
var currentSort = { field: 'updated', direction: 'desc' };
var currentPage = 1;
var pageSize = 10;

document.addEventListener('DOMContentLoaded', function() {
    renderTable();
    
    document.getElementById('searchInput').addEventListener('input', debounce(applyFilters, 300));
    document.getElementById('statusFilter').addEventListener('change', applyFilters);
    document.getElementById('billingFilter').addEventListener('change', applyFilters);
    document.getElementById('useCaseFilter').addEventListener('change', applyFilters);
    
    document.getElementById('createAgentBtn').addEventListener('click', function() {
        alert('TODO: Open Create RCS Agent wizard');
    });
    
    document.getElementById('createAgentEmptyBtn').addEventListener('click', function() {
        alert('TODO: Open Create RCS Agent wizard');
    });
    
    document.getElementById('confirmResubmitBtn').addEventListener('click', function() {
        alert('TODO: Resubmit agent via API');
        bootstrap.Modal.getInstance(document.getElementById('resubmitAgentModal')).hide();
    });
    
    document.getElementById('prevPageBtn').addEventListener('click', function() {
        if (currentPage > 1) {
            currentPage--;
            renderTable();
        }
    });
    
    document.getElementById('nextPageBtn').addEventListener('click', function() {
        var maxPages = Math.ceil(filteredAgents.length / pageSize);
        if (currentPage < maxPages) {
            currentPage++;
            renderTable();
        }
    });
});

function debounce(func, wait) {
    var timeout;
    return function() {
        var context = this, args = arguments;
        clearTimeout(timeout);
        timeout = setTimeout(function() {
            func.apply(context, args);
        }, wait);
    };
}

function applyFilters() {
    var search = document.getElementById('searchInput').value.toLowerCase();
    var status = document.getElementById('statusFilter').value;
    var billing = document.getElementById('billingFilter').value;
    var useCase = document.getElementById('useCaseFilter').value;
    
    filteredAgents = mockAgents.filter(function(agent) {
        var matchesSearch = !search || agent.name.toLowerCase().includes(search);
        var matchesStatus = !status || agent.status === status;
        var matchesBilling = !billing || agent.billing === billing;
        var matchesUseCase = !useCase || agent.useCase === useCase;
        
        return matchesSearch && matchesStatus && matchesBilling && matchesUseCase;
    });
    
    currentPage = 1;
    sortAgents();
    renderTable();
}

function sortTable(field) {
    var headers = document.querySelectorAll('.agents-table thead th');
    headers.forEach(function(th) { th.classList.remove('sorted'); });
    
    if (currentSort.field === field) {
        currentSort.direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
    } else {
        currentSort.field = field;
        currentSort.direction = 'asc';
    }
    
    var sortedHeader = document.querySelector('[data-sort="' + field + '"]');
    if (sortedHeader) {
        sortedHeader.classList.add('sorted');
        var icon = sortedHeader.querySelector('.sort-icon');
        icon.className = 'fas fa-sort-' + (currentSort.direction === 'asc' ? 'up' : 'down') + ' sort-icon';
    }
    
    sortAgents();
    renderTable();
}

function sortAgents() {
    filteredAgents.sort(function(a, b) {
        var aVal = a[currentSort.field];
        var bVal = b[currentSort.field];
        
        if (typeof aVal === 'string') {
            aVal = aVal.toLowerCase();
            bVal = bVal.toLowerCase();
        }
        
        if (aVal < bVal) return currentSort.direction === 'asc' ? -1 : 1;
        if (aVal > bVal) return currentSort.direction === 'asc' ? 1 : -1;
        return 0;
    });
}

function renderTable() {
    var tbody = document.getElementById('agentsTableBody');
    var tableContainer = document.getElementById('agentsTableContainer');
    var emptyState = document.getElementById('emptyState');
    
    if (filteredAgents.length === 0) {
        tableContainer.classList.add('d-none');
        emptyState.classList.remove('d-none');
        return;
    }
    
    tableContainer.classList.remove('d-none');
    emptyState.classList.add('d-none');
    
    var start = (currentPage - 1) * pageSize;
    var end = Math.min(start + pageSize, filteredAgents.length);
    var pageAgents = filteredAgents.slice(start, end);
    
    tbody.innerHTML = pageAgents.map(function(agent) {
        return '<tr>' +
            '<td><span class="agent-name">' + escapeHtml(agent.name) + '</span></td>' +
            '<td>' + getStatusBadge(agent.status) + '</td>' +
            '<td>' + getBillingBadge(agent.billing) + '</td>' +
            '<td><span class="use-case-text">' + formatUseCase(agent.useCase) + '</span></td>' +
            '<td><span class="date-text">' + formatDate(agent.created) + '</span></td>' +
            '<td><span class="date-text">' + formatDate(agent.updated) + '</span></td>' +
            '<td>' + getActionsMenu(agent) + '</td>' +
        '</tr>';
    }).join('');
    
    document.getElementById('showingStart').textContent = start + 1;
    document.getElementById('showingEnd').textContent = end;
    document.getElementById('totalCount').textContent = filteredAgents.length;
    
    document.getElementById('prevPageBtn').disabled = currentPage === 1;
    document.getElementById('nextPageBtn').disabled = end >= filteredAgents.length;
}

function getStatusBadge(status) {
    var labels = {
        'draft': 'Draft',
        'submitted': 'Submitted',
        'in-review': 'In Review',
        'approved': 'Approved',
        'rejected': 'Rejected'
    };
    var classes = {
        'draft': 'badge-draft',
        'submitted': 'badge-submitted',
        'in-review': 'badge-in-review',
        'approved': 'badge-approved',
        'rejected': 'badge-rejected'
    };
    return '<span class="badge ' + classes[status] + '">' + labels[status] + '</span>';
}

function getBillingBadge(billing) {
    var label = billing === 'conversational' ? 'Conversational' : 'Non-conversational';
    var cls = billing === 'conversational' ? 'badge-conversational' : 'badge-non-conversational';
    return '<span class="badge ' + cls + '">' + label + '</span>';
}

function formatUseCase(useCase) {
    var labels = {
        'otp': 'OTP',
        'transactional': 'Transactional',
        'promotional': 'Promotional',
        'multi-use': 'Multi-use'
    };
    return labels[useCase] || useCase;
}

function formatDate(dateStr) {
    var date = new Date(dateStr);
    return date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
}

function getActionsMenu(agent) {
    var canEdit = agent.status === 'draft' || agent.status === 'rejected';
    var canResubmit = agent.status === 'rejected';
    
    return '<div class="dropdown action-menu">' +
        '<button class="btn btn-link text-muted p-0" data-bs-toggle="dropdown">' +
            '<i class="fas fa-ellipsis-v"></i>' +
        '</button>' +
        '<ul class="dropdown-menu dropdown-menu-end">' +
            '<li><a class="dropdown-item" href="javascript:void(0)" onclick="viewAgent(\'' + agent.id + '\')">' +
                '<i class="fas fa-eye"></i>View</a></li>' +
            '<li><a class="dropdown-item' + (canEdit ? '' : ' disabled') + '" href="javascript:void(0)"' + (canEdit ? ' onclick="editAgent(\'' + agent.id + '\')"' : '') + '>' +
                '<i class="fas fa-edit"></i>Edit</a></li>' +
            (canResubmit ? '<li><a class="dropdown-item" href="javascript:void(0)" onclick="resubmitAgent(\'' + agent.id + '\')">' +
                '<i class="fas fa-redo"></i>Resubmit</a></li>' : '') +
        '</ul>' +
    '</div>';
}

function viewAgent(agentId) {
    var agent = mockAgents.find(function(a) { return a.id === agentId; });
    if (!agent) return;
    
    document.getElementById('viewAgentName').textContent = agent.name;
    document.getElementById('viewAgentStatus').innerHTML = getStatusBadge(agent.status);
    document.getElementById('viewAgentBilling').innerHTML = getBillingBadge(agent.billing);
    document.getElementById('viewAgentUseCase').textContent = formatUseCase(agent.useCase);
    document.getElementById('viewAgentCreated').textContent = formatDate(agent.created);
    document.getElementById('viewAgentUpdated').textContent = formatDate(agent.updated);
    
    var rejectionContainer = document.getElementById('viewRejectionReasonContainer');
    if (agent.rejectionReason) {
        rejectionContainer.style.display = 'block';
        document.getElementById('viewAgentRejectionReason').textContent = agent.rejectionReason;
    } else {
        rejectionContainer.style.display = 'none';
    }
    
    new bootstrap.Modal(document.getElementById('viewAgentModal')).show();
}

function editAgent(agentId) {
    alert('TODO: Open Edit RCS Agent wizard for agent ' + agentId);
}

function resubmitAgent(agentId) {
    var agent = mockAgents.find(function(a) { return a.id === agentId; });
    if (!agent) return;
    
    document.getElementById('resubmitAgentName').textContent = agent.name;
    new bootstrap.Modal(document.getElementById('resubmitAgentModal')).show();
}

function escapeHtml(text) {
    var div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
@endpush
