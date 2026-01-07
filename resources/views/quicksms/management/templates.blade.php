@extends('layouts.quicksms')

@section('title', 'Message Templates')

@push('styles')
<style>
.templates-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}
.templates-header h2 {
    margin: 0;
    font-weight: 600;
}
.templates-header p {
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
.templates-table-container {
    background: #fff;
    border-radius: 0.75rem;
    border: 1px solid #e9ecef;
    overflow: hidden;
}
.templates-table {
    width: 100%;
    margin: 0;
}
.templates-table thead th {
    background: #f8f9fa;
    padding: 0.75rem 0.75rem;
    font-weight: 600;
    font-size: 0.8rem;
    color: #495057;
    border-bottom: 1px solid #e9ecef;
    cursor: pointer;
    white-space: nowrap;
    user-select: none;
}
.templates-table thead th:hover {
    background: #e9ecef;
}
.templates-table thead th .sort-icon {
    margin-left: 0.25rem;
    opacity: 0.4;
}
.templates-table thead th.sorted .sort-icon {
    opacity: 1;
    color: var(--primary);
}
.templates-table tbody td {
    padding: 0.75rem 0.75rem;
    vertical-align: middle;
    border-bottom: 1px solid #f1f3f5;
    font-size: 0.875rem;
}
.templates-table tbody tr:last-child td {
    border-bottom: none;
}
.templates-table tbody tr:hover {
    background: #f8f9fa;
}
.template-name {
    font-weight: 500;
    color: #343a40;
}
.template-id {
    font-family: monospace;
    font-size: 0.8rem;
    color: #6c757d;
}
.badge-sms {
    background: rgba(48, 101, 208, 0.15);
    color: #3065D0;
}
.badge-basic-rcs {
    background: rgba(28, 187, 140, 0.15);
    color: #1cbb8c;
}
.badge-rich-rcs {
    background: rgba(136, 108, 192, 0.15);
    color: #886CC0;
}
.badge-api {
    background: rgba(108, 117, 125, 0.15);
    color: #6c757d;
}
.badge-portal {
    background: rgba(255, 191, 0, 0.15);
    color: #cc9900;
}
.badge-email {
    background: rgba(214, 83, 193, 0.15);
    color: #D653C1;
}
.badge-draft {
    background: rgba(108, 117, 125, 0.15);
    color: #6c757d;
}
.badge-live {
    background: rgba(28, 187, 140, 0.15);
    color: #1cbb8c;
}
.badge-paused {
    background: rgba(255, 191, 0, 0.15);
    color: #cc9900;
}
.badge-archived {
    background: rgba(220, 53, 69, 0.15);
    color: #dc3545;
}
.badge-rich-card {
    background: rgba(136, 108, 192, 0.15);
    color: #886CC0;
}
.badge-carousel {
    background: rgba(214, 83, 193, 0.15);
    color: #D653C1;
}
.content-preview {
    color: #6c757d;
    font-size: 0.8rem;
    max-width: 180px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.access-scope {
    font-size: 0.8rem;
    color: #495057;
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
.filters-panel {
    background-color: #f0ebf8;
    border-radius: 0.5rem;
    padding: 1rem;
    margin: 0 1rem 1rem;
}
.filters-panel .form-label {
    font-size: 0.8rem;
    font-weight: 600;
    margin-bottom: 0.25rem;
}
.filters-panel .form-select,
.filters-panel .form-control {
    font-size: 0.875rem;
}
.filter-actions {
    display: flex;
    gap: 0.5rem;
    align-items: flex-end;
}
.multiselect-dropdown .dropdown-menu {
    max-height: 250px;
    overflow-y: auto;
    min-width: 200px;
}
.multiselect-dropdown .form-check {
    padding: 0.25rem 0.5rem;
    margin: 0;
}
.multiselect-dropdown .form-check:hover {
    background-color: #f8f9fa;
}
.multiselect-dropdown .dropdown-toggle {
    background-color: #fff;
    border: 1px solid #ced4da;
    color: #495057;
    text-align: left;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.multiselect-dropdown .dropdown-toggle::after {
    margin-left: auto;
}
.active-filters {
    padding: 0.5rem 1rem;
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    align-items: center;
}
.active-filters:empty {
    display: none;
}
.filter-chip {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.75rem;
    background-color: rgba(136, 108, 192, 0.15);
    color: #886CC0;
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
.filter-chip .remove-chip:hover {
    opacity: 1;
}
.dropdown-menu {
    min-width: 120px;
}
.dropdown-item {
    font-size: 0.875rem;
    padding: 0.5rem 1rem;
}
.dropdown-item i {
    width: 16px;
    margin-right: 0.5rem;
}
.action-menu-btn {
    background: transparent;
    border: none;
    padding: 0.25rem 0.5rem;
    cursor: pointer;
    color: #6c757d;
}
.action-menu-btn:hover {
    color: var(--primary);
}
.version-badge {
    display: inline-block;
    padding: 0.2rem 0.5rem;
    background-color: #f0ebf8;
    color: var(--primary);
    border-radius: 0.25rem;
    font-size: 0.75rem;
    font-weight: 600;
}
.archived-row {
    opacity: 0.6;
    background-color: #f8f9fa;
}
.archived-row:hover {
    opacity: 0.8;
}
.form-check-input:checked {
    background-color: var(--primary);
    border-color: var(--primary);
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="templates-header">
        <div>
            <h2>Message Templates</h2>
            <p>Create and manage reusable SMS and RCS message templates</p>
        </div>
        <button class="btn btn-primary" id="createTemplateBtn">
            <i class="fas fa-plus me-2"></i>Create Template
        </button>
    </div>

    <div id="emptyState" class="empty-state" style="display: none;">
        <div class="empty-state-icon">
            <i class="fas fa-file-alt"></i>
        </div>
        <h4>No templates yet</h4>
        <p>Create your first message template to save time when sending messages. Templates can include personalization tags and are available for both SMS and RCS.</p>
        <button class="btn btn-primary" onclick="showCreateModal()">
            <i class="fas fa-plus me-2"></i>Create Template
        </button>
    </div>

    <div id="templatesTableContainer" class="templates-table-container">
        <div class="search-filter-bar">
            <div class="search-box">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control" id="templateSearch" placeholder="Search by name or ID...">
                </div>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="form-check form-switch mb-0">
                    <input class="form-check-input" type="checkbox" id="showArchivedToggle">
                    <label class="form-check-label small" for="showArchivedToggle">Show Archived</label>
                </div>
                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#filtersPanel">
                    <i class="fas fa-filter me-1"></i>Filters
                </button>
            </div>
        </div>

        <div class="collapse" id="filtersPanel">
            <div class="filters-panel">
                <div class="row g-3 align-items-end">
                    <div class="col-6 col-md-3 col-lg-2">
                        <label class="form-label">Channel</label>
                        <select class="form-select form-select-sm" id="channelFilter">
                            <option value="">All Channels</option>
                            <option value="sms">SMS</option>
                            <option value="basic_rcs">Basic RCS + SMS</option>
                            <option value="rich_rcs">Rich RCS + SMS</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <label class="form-label">Trigger</label>
                        <select class="form-select form-select-sm" id="triggerFilter">
                            <option value="">All Triggers</option>
                            <option value="api">API</option>
                            <option value="portal">Portal</option>
                            <option value="email">Email-to-SMS</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <label class="form-label">Status</label>
                        <select class="form-select form-select-sm" id="statusFilter">
                            <option value="">All Statuses</option>
                            <option value="draft">Draft</option>
                            <option value="live">Live</option>
                            <option value="paused">Paused</option>
                            <option value="archived">Archived</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <label class="form-label">Sub-account</label>
                        <div class="dropdown multiselect-dropdown" id="subAccountDropdown">
                            <button class="btn btn-sm dropdown-toggle w-100" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                                <span class="dropdown-label">All Sub-accounts</span>
                            </button>
                            <div class="dropdown-menu w-100 p-2">
                                <div class="form-check">
                                    <input class="form-check-input subaccount-check" type="checkbox" value="marketing" id="subMarketing">
                                    <label class="form-check-label" for="subMarketing">Marketing Team</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input subaccount-check" type="checkbox" value="sales" id="subSales">
                                    <label class="form-check-label" for="subSales">Sales</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input subaccount-check" type="checkbox" value="support" id="subSupport">
                                    <label class="form-check-label" for="subSupport">Support Team</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input subaccount-check" type="checkbox" value="it" id="subIT">
                                    <label class="form-check-label" for="subIT">IT Security</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input subaccount-check" type="checkbox" value="all" id="subAll">
                                    <label class="form-check-label" for="subAll">All Sub-accounts</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-4">
                        <div class="filter-actions">
                            <button type="button" class="btn btn-primary btn-sm" id="applyFiltersBtn">
                                <i class="fas fa-check me-1"></i>Apply Filters
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="resetFiltersBtn">
                                <i class="fas fa-undo me-1"></i>Reset
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="active-filters" id="activeFilters"></div>

        <div class="table-responsive">
            <table class="templates-table">
                <thead>
                    <tr>
                        <th data-sort="name" onclick="sortTable('name')">Template Name <i class="fas fa-sort sort-icon"></i></th>
                        <th data-sort="templateId" onclick="sortTable('templateId')">Template ID <i class="fas fa-sort sort-icon"></i></th>
                        <th data-sort="version" onclick="sortTable('version')">Version <i class="fas fa-sort sort-icon"></i></th>
                        <th data-sort="channel" onclick="sortTable('channel')">Channel <i class="fas fa-sort sort-icon"></i></th>
                        <th data-sort="trigger" onclick="sortTable('trigger')">Trigger <i class="fas fa-sort sort-icon"></i></th>
                        <th>Content Preview</th>
                        <th data-sort="accessScope" onclick="sortTable('accessScope')">Access Scope <i class="fas fa-sort sort-icon"></i></th>
                        <th data-sort="status" onclick="sortTable('status')">Status <i class="fas fa-sort sort-icon"></i></th>
                        <th data-sort="lastUpdated" onclick="sortTable('lastUpdated')">Last Updated <i class="fas fa-sort sort-icon"></i></th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="templatesBody">
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="createTemplateModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-file-alt me-2 text-primary"></i>Create Template</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-pastel-primary mb-3">
                    <i class="fas fa-info-circle me-2 text-primary"></i>
                    Templates allow you to save message content for quick reuse. Add personalization tags like {FirstName} to customize messages.
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-semibold">Template Name</label>
                    <input type="text" class="form-control" id="templateName" placeholder="e.g., Welcome Message, Appointment Reminder">
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-semibold">Channel</label>
                    <div class="d-flex gap-3 flex-wrap">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="templateChannel" id="channelSms" value="sms" checked>
                            <label class="form-check-label" for="channelSms">SMS</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="templateChannel" id="channelBasicRcs" value="basic_rcs">
                            <label class="form-check-label" for="channelBasicRcs">Basic RCS + SMS</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="templateChannel" id="channelRichRcs" value="rich_rcs">
                            <label class="form-check-label" for="channelRichRcs">Rich RCS + SMS</label>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-semibold">Message Content</label>
                    <textarea class="form-control" id="templateContent" rows="5" placeholder="Enter your message content..."></textarea>
                    <div class="d-flex justify-content-between mt-2">
                        <small class="text-muted">Use {FirstName}, {LastName}, {Company} for personalization</small>
                        <small class="text-muted"><span id="charCount">0</span> characters</small>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveTemplate()">
                    <i class="fas fa-save me-2"></i>Save Template
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
var mockTemplates = [
    {
        id: 1,
        templateId: '10483726',
        name: 'Welcome Message',
        channel: 'sms',
        trigger: 'portal',
        content: 'Hi {FirstName}, welcome to QuickSMS! Your account is now active. Reply HELP for support or STOP to opt out.',
        contentType: 'text',
        accessScope: 'All Sub-accounts',
        subAccounts: ['all'],
        status: 'live',
        version: 3,
        lastUpdated: '2026-01-05'
    },
    {
        id: 2,
        templateId: '20957341',
        name: 'Appointment Reminder',
        channel: 'basic_rcs',
        trigger: 'api',
        content: 'Reminder: Your appointment with {Company} is scheduled for tomorrow at {Time}. Reply YES to confirm.',
        contentType: 'text',
        accessScope: 'Marketing Team',
        subAccounts: ['marketing'],
        status: 'live',
        version: 2,
        lastUpdated: '2026-01-04'
    },
    {
        id: 3,
        templateId: '38472615',
        name: 'Product Showcase',
        channel: 'rich_rcs',
        trigger: 'portal',
        content: '',
        contentType: 'rich_card',
        accessScope: 'Sales, Support',
        subAccounts: ['sales', 'support'],
        status: 'draft',
        version: 1,
        lastUpdated: '2026-01-06'
    },
    {
        id: 4,
        templateId: '47291830',
        name: 'Holiday Promotions',
        channel: 'rich_rcs',
        trigger: 'api',
        content: '',
        contentType: 'carousel',
        accessScope: 'Marketing Team',
        subAccounts: ['marketing'],
        status: 'draft',
        version: 4,
        lastUpdated: '2025-12-20'
    },
    {
        id: 5,
        templateId: '56384029',
        name: 'Order Confirmation',
        channel: 'sms',
        trigger: 'email',
        content: 'Order #{OrderID} confirmed! Your items will ship within 2 business days. Track at: {TrackingURL}',
        contentType: 'text',
        accessScope: 'All Sub-accounts',
        subAccounts: ['all'],
        status: 'live',
        version: 1,
        lastUpdated: '2026-01-03'
    },
    {
        id: 6,
        templateId: '69102847',
        name: 'Password Reset',
        channel: 'sms',
        trigger: 'api',
        content: 'Your verification code is {Code}. This code expires in 10 minutes. Do not share this code.',
        contentType: 'text',
        accessScope: 'IT Security',
        subAccounts: ['it'],
        status: 'archived',
        version: 5,
        lastUpdated: '2025-11-15'
    },
    {
        id: 7,
        templateId: '71829364',
        name: 'Flash Sale Alert',
        channel: 'basic_rcs',
        trigger: 'portal',
        content: 'Flash Sale! 50% off all items for the next 24 hours. Shop now at {ShopURL}. Limited stock available!',
        contentType: 'text',
        accessScope: 'Marketing Team',
        subAccounts: ['marketing'],
        status: 'draft',
        version: 1,
        lastUpdated: '2026-01-07'
    },
    {
        id: 8,
        templateId: '82946150',
        name: 'Customer Feedback',
        channel: 'rich_rcs',
        trigger: 'email',
        content: '',
        contentType: 'rich_card',
        accessScope: 'Support Team',
        subAccounts: ['support'],
        status: 'live',
        version: 2,
        lastUpdated: '2026-01-02'
    }
];

var showArchived = false;

var sortColumn = 'lastUpdated';
var sortDirection = 'desc';

var appliedFilters = {
    search: '',
    channel: '',
    trigger: '',
    status: '',
    subAccounts: []
};

var pendingFilters = {
    channel: '',
    trigger: '',
    status: '',
    subAccounts: []
};

var subAccountLabels = {
    'marketing': 'Marketing Team',
    'sales': 'Sales',
    'support': 'Support Team',
    'it': 'IT Security',
    'all': 'All Sub-accounts'
};

document.addEventListener('DOMContentLoaded', function() {
    renderTemplates();
    setupEventListeners();
});

function setupEventListeners() {
    document.getElementById('createTemplateBtn').addEventListener('click', showCreateModal);
    document.getElementById('templateContent').addEventListener('input', updateCharCount);
    
    document.getElementById('templateSearch').addEventListener('input', function() {
        appliedFilters.search = this.value;
        renderTemplates();
        renderActiveFilters();
    });
    
    document.getElementById('showArchivedToggle').addEventListener('change', function() {
        showArchived = this.checked;
        renderTemplates();
    });
    
    document.getElementById('applyFiltersBtn').addEventListener('click', applyFilters);
    document.getElementById('resetFiltersBtn').addEventListener('click', resetFilters);
    
    document.querySelectorAll('.subaccount-check').forEach(function(checkbox) {
        checkbox.addEventListener('change', updateSubAccountDropdownLabel);
    });
}

function updateSubAccountDropdownLabel() {
    var checked = document.querySelectorAll('.subaccount-check:checked');
    var label = document.querySelector('#subAccountDropdown .dropdown-label');
    
    if (checked.length === 0) {
        label.textContent = 'All Sub-accounts';
    } else if (checked.length === 1) {
        label.textContent = subAccountLabels[checked[0].value] || checked[0].value;
    } else {
        label.textContent = checked.length + ' selected';
    }
}

function applyFilters() {
    appliedFilters.channel = document.getElementById('channelFilter').value;
    appliedFilters.trigger = document.getElementById('triggerFilter').value;
    appliedFilters.status = document.getElementById('statusFilter').value;
    
    var checkedSubAccounts = [];
    document.querySelectorAll('.subaccount-check:checked').forEach(function(cb) {
        checkedSubAccounts.push(cb.value);
    });
    appliedFilters.subAccounts = checkedSubAccounts;
    
    renderTemplates();
    renderActiveFilters();
}

function resetFilters() {
    document.getElementById('channelFilter').value = '';
    document.getElementById('triggerFilter').value = '';
    document.getElementById('statusFilter').value = '';
    document.querySelectorAll('.subaccount-check').forEach(function(cb) {
        cb.checked = false;
    });
    updateSubAccountDropdownLabel();
    
    appliedFilters.channel = '';
    appliedFilters.trigger = '';
    appliedFilters.status = '';
    appliedFilters.subAccounts = [];
    
    renderTemplates();
    renderActiveFilters();
}

function removeFilter(filterType) {
    if (filterType === 'search') {
        document.getElementById('templateSearch').value = '';
        appliedFilters.search = '';
    } else if (filterType === 'channel') {
        document.getElementById('channelFilter').value = '';
        appliedFilters.channel = '';
    } else if (filterType === 'trigger') {
        document.getElementById('triggerFilter').value = '';
        appliedFilters.trigger = '';
    } else if (filterType === 'status') {
        document.getElementById('statusFilter').value = '';
        appliedFilters.status = '';
    } else if (filterType === 'subAccounts') {
        document.querySelectorAll('.subaccount-check').forEach(function(cb) {
            cb.checked = false;
        });
        updateSubAccountDropdownLabel();
        appliedFilters.subAccounts = [];
    }
    
    renderTemplates();
    renderActiveFilters();
}

function renderActiveFilters() {
    var container = document.getElementById('activeFilters');
    var html = '';
    
    if (appliedFilters.search) {
        html += createChip('Search', appliedFilters.search, 'search');
    }
    
    if (appliedFilters.channel) {
        html += createChip('Channel', getChannelLabel(appliedFilters.channel), 'channel');
    }
    
    if (appliedFilters.trigger) {
        html += createChip('Trigger', getTriggerLabel(appliedFilters.trigger), 'trigger');
    }
    
    if (appliedFilters.status) {
        html += createChip('Status', getStatusLabel(appliedFilters.status), 'status');
    }
    
    if (appliedFilters.subAccounts.length > 0) {
        var labels = appliedFilters.subAccounts.map(function(v) {
            return subAccountLabels[v] || v;
        });
        html += createChip('Sub-account', labels.join(', '), 'subAccounts');
    }
    
    container.innerHTML = html;
}

function createChip(label, value, filterType) {
    return '<span class="filter-chip">' +
        '<span class="chip-label">' + label + ':</span>' +
        '<span class="chip-value">' + value + '</span>' +
        '<i class="fas fa-times remove-chip" onclick="removeFilter(\'' + filterType + '\')"></i>' +
        '</span>';
}

function showCreateModal() {
    document.getElementById('templateName').value = '';
    document.getElementById('templateContent').value = '';
    document.getElementById('charCount').textContent = '0';
    document.getElementById('channelSms').checked = true;
    new bootstrap.Modal(document.getElementById('createTemplateModal')).show();
}

function updateCharCount() {
    var content = document.getElementById('templateContent').value;
    document.getElementById('charCount').textContent = content.length;
}

function saveTemplate() {
    var name = document.getElementById('templateName').value.trim();
    var content = document.getElementById('templateContent').value.trim();
    var channel = document.querySelector('input[name="templateChannel"]:checked').value;
    
    if (!name) {
        alert('Please enter a template name.');
        return;
    }
    
    var templateId = Math.floor(10000000 + Math.random() * 90000000).toString();
    
    var template = {
        id: Date.now(),
        templateId: templateId,
        name: name,
        channel: channel,
        trigger: 'portal',
        content: content,
        contentType: channel === 'rich_rcs' ? 'rich_card' : 'text',
        accessScope: 'All Sub-accounts',
        subAccounts: ['all'],
        status: 'draft',
        lastUpdated: new Date().toISOString().split('T')[0]
    };
    
    mockTemplates.unshift(template);
    bootstrap.Modal.getInstance(document.getElementById('createTemplateModal')).hide();
    renderTemplates();
}

function sortTable(column) {
    if (sortColumn === column) {
        sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
    } else {
        sortColumn = column;
        sortDirection = 'asc';
    }
    
    document.querySelectorAll('.templates-table thead th').forEach(function(th) {
        th.classList.remove('sorted');
        var icon = th.querySelector('.sort-icon');
        if (icon) {
            icon.className = 'fas fa-sort sort-icon';
        }
    });
    
    var activeTh = document.querySelector('[data-sort="' + column + '"]');
    if (activeTh) {
        activeTh.classList.add('sorted');
        var icon = activeTh.querySelector('.sort-icon');
        if (icon) {
            icon.className = 'fas fa-sort-' + (sortDirection === 'asc' ? 'up' : 'down') + ' sort-icon';
        }
    }
    
    renderTemplates();
}

function getChannelLabel(channel) {
    switch(channel) {
        case 'sms': return 'SMS';
        case 'basic_rcs': return 'Basic RCS + SMS';
        case 'rich_rcs': return 'Rich RCS + SMS';
        default: return channel;
    }
}

function getChannelBadgeClass(channel) {
    switch(channel) {
        case 'sms': return 'badge-sms';
        case 'basic_rcs': return 'badge-basic-rcs';
        case 'rich_rcs': return 'badge-rich-rcs';
        default: return 'badge-sms';
    }
}

function getTriggerLabel(trigger) {
    switch(trigger) {
        case 'api': return 'API';
        case 'portal': return 'Portal';
        case 'email': return 'Email-to-SMS';
        default: return trigger;
    }
}

function getTriggerBadgeClass(trigger) {
    switch(trigger) {
        case 'api': return 'badge-api';
        case 'portal': return 'badge-portal';
        case 'email': return 'badge-email';
        default: return 'badge-api';
    }
}

function getStatusLabel(status) {
    return status.charAt(0).toUpperCase() + status.slice(1);
}

function getStatusBadgeClass(status) {
    switch(status) {
        case 'draft': return 'badge-draft';
        case 'live': return 'badge-live';
        case 'paused': return 'badge-paused';
        case 'archived': return 'badge-archived';
        default: return 'badge-draft';
    }
}

function getContentPreview(template) {
    if (template.contentType === 'rich_card') {
        return '<span class="badge rounded-pill badge-rich-card">Rich Card</span>';
    } else if (template.contentType === 'carousel') {
        return '<span class="badge rounded-pill badge-carousel">Carousel</span>';
    } else {
        var preview = template.content.length > 100 ? template.content.substring(0, 100) + '...' : template.content;
        return '<span class="content-preview" title="' + template.content.replace(/"/g, '&quot;') + '">' + preview + '</span>';
    }
}

function renderTemplates() {
    var search = appliedFilters.search.toLowerCase();
    
    var filtered = mockTemplates.filter(function(t) {
        if (!showArchived && t.status === 'archived') {
            return false;
        }
        
        var matchSearch = !search || t.name.toLowerCase().includes(search) || t.templateId.includes(search);
        var matchChannel = !appliedFilters.channel || t.channel === appliedFilters.channel;
        var matchTrigger = !appliedFilters.trigger || t.trigger === appliedFilters.trigger;
        var matchStatus = !appliedFilters.status || t.status === appliedFilters.status;
        
        var matchSubAccount = appliedFilters.subAccounts.length === 0 || 
            appliedFilters.subAccounts.some(function(sa) {
                return t.subAccounts.includes(sa) || t.subAccounts.includes('all');
            });
        
        return matchSearch && matchChannel && matchTrigger && matchStatus && matchSubAccount;
    });
    
    filtered.sort(function(a, b) {
        var aVal = a[sortColumn] || '';
        var bVal = b[sortColumn] || '';
        
        if (sortColumn === 'lastUpdated') {
            aVal = new Date(aVal);
            bVal = new Date(bVal);
        } else if (sortColumn === 'version') {
            aVal = parseInt(aVal) || 0;
            bVal = parseInt(bVal) || 0;
        }
        
        if (aVal < bVal) return sortDirection === 'asc' ? -1 : 1;
        if (aVal > bVal) return sortDirection === 'asc' ? 1 : -1;
        return 0;
    });
    
    if (mockTemplates.length === 0) {
        document.getElementById('emptyState').style.display = 'block';
        document.getElementById('templatesTableContainer').style.display = 'none';
        return;
    }
    
    document.getElementById('emptyState').style.display = 'none';
    document.getElementById('templatesTableContainer').style.display = 'block';
    
    var tbody = document.getElementById('templatesBody');
    var html = '';
    
    filtered.forEach(function(template) {
        var isArchived = template.status === 'archived';
        var rowClass = isArchived ? 'archived-row' : '';
        
        html += '<tr class="' + rowClass + '">';
        html += '<td><span class="template-name">' + template.name + '</span></td>';
        html += '<td><span class="template-id">' + template.templateId + '</span></td>';
        html += '<td><span class="version-badge">v' + template.version + '</span></td>';
        html += '<td><span class="badge rounded-pill ' + getChannelBadgeClass(template.channel) + '">' + getChannelLabel(template.channel) + '</span></td>';
        html += '<td><span class="badge rounded-pill ' + getTriggerBadgeClass(template.trigger) + '">' + getTriggerLabel(template.trigger) + '</span></td>';
        html += '<td>' + getContentPreview(template) + '</td>';
        html += '<td><span class="access-scope">' + template.accessScope + '</span></td>';
        html += '<td><span class="badge rounded-pill ' + getStatusBadgeClass(template.status) + '">' + getStatusLabel(template.status) + '</span></td>';
        html += '<td>' + template.lastUpdated + '</td>';
        html += '<td>';
        html += '<div class="dropdown">';
        html += '<button class="action-menu-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">';
        html += '<i class="fas fa-ellipsis-v"></i>';
        html += '</button>';
        html += '<ul class="dropdown-menu dropdown-menu-end">';
        
        if (!isArchived) {
            html += '<li><a class="dropdown-item" href="#" onclick="editTemplate(' + template.id + '); return false;"><i class="fas fa-edit me-2"></i>Edit</a></li>';
        }
        html += '<li><a class="dropdown-item" href="#" onclick="duplicateTemplate(' + template.id + '); return false;"><i class="fas fa-copy me-2"></i>Duplicate</a></li>';
        if (!isArchived) {
            html += '<li><a class="dropdown-item" href="#" onclick="managePermissions(' + template.id + '); return false;"><i class="fas fa-lock me-2"></i>Permissions</a></li>';
        }
        if (template.trigger === 'api') {
            html += '<li><a class="dropdown-item" href="#" onclick="viewApiStructure(' + template.id + '); return false;"><i class="fas fa-code me-2"></i>API Structure</a></li>';
        }
        if (!isArchived) {
            html += '<li><hr class="dropdown-divider"></li>';
            html += '<li><a class="dropdown-item text-warning" href="#" onclick="archiveTemplate(' + template.id + '); return false;"><i class="fas fa-archive me-2"></i>Archive</a></li>';
        }
        
        html += '</ul>';
        html += '</div>';
        html += '</td>';
        html += '</tr>';
    });
    
    tbody.innerHTML = html || '<tr><td colspan="10" class="text-center text-muted py-4">No templates match your filters</td></tr>';
}

function editTemplate(id) {
    var template = mockTemplates.find(function(t) { return t.id === id; });
    if (!template || template.status === 'archived') {
        showToast('Archived templates cannot be edited', 'warning');
        return;
    }
    showToast('Opening editor for "' + template.name + '"...', 'info');
}

function duplicateTemplate(id) {
    var template = mockTemplates.find(function(t) { return t.id === id; });
    if (!template) return;
    
    var newId = mockTemplates.length + 1;
    var newTemplateId = Math.floor(10000000 + Math.random() * 90000000).toString();
    
    var duplicate = Object.assign({}, template, {
        id: newId,
        templateId: newTemplateId,
        name: template.name + ' (Copy)',
        status: 'draft',
        version: 1,
        lastUpdated: new Date().toISOString().split('T')[0]
    });
    
    mockTemplates.push(duplicate);
    renderTemplates();
    showToast('Template duplicated as "' + duplicate.name + '"', 'success');
}

function managePermissions(id) {
    var template = mockTemplates.find(function(t) { return t.id === id; });
    if (!template) return;
    showToast('Opening permissions for "' + template.name + '"...', 'info');
}

function viewApiStructure(id) {
    var template = mockTemplates.find(function(t) { return t.id === id; });
    if (!template) return;
    showToast('Viewing API structure for "' + template.name + '"...', 'info');
}

function archiveTemplate(id) {
    var template = mockTemplates.find(function(t) { return t.id === id; });
    if (!template) return;
    
    if (confirm('Are you sure you want to archive "' + template.name + '"? Archived templates cannot be edited or used.')) {
        template.status = 'archived';
        template.lastUpdated = new Date().toISOString().split('T')[0];
        renderTemplates();
        showToast('Template "' + template.name + '" has been archived', 'success');
    }
}

function goLiveTemplate(id) {
    var template = mockTemplates.find(function(t) { return t.id === id; });
    if (!template || template.status === 'archived') return;
    
    template.status = 'live';
    template.version = template.version + 1;
    template.lastUpdated = new Date().toISOString().split('T')[0];
    renderTemplates();
    showToast('Template "' + template.name + '" is now Live (v' + template.version + ')', 'success');
}

function showToast(message, type) {
    var toastContainer = document.getElementById('toastContainer');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toastContainer';
        toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        document.body.appendChild(toastContainer);
    }
    
    var bgClass = type === 'success' ? 'bg-success' : type === 'warning' ? 'bg-warning' : 'bg-info';
    var textClass = type === 'warning' ? 'text-dark' : 'text-white';
    
    var toastHtml = '<div class="toast align-items-center ' + bgClass + ' ' + textClass + ' border-0" role="alert">' +
        '<div class="d-flex">' +
        '<div class="toast-body">' + message + '</div>' +
        '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>' +
        '</div></div>';
    
    toastContainer.insertAdjacentHTML('beforeend', toastHtml);
    var toastEl = toastContainer.lastElementChild;
    var toast = new bootstrap.Toast(toastEl, { delay: 3000 });
    toast.show();
    
    toastEl.addEventListener('hidden.bs.toast', function() {
        toastEl.remove();
    });
}
</script>
@endpush
