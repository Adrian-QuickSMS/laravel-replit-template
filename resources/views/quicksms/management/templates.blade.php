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
.table-controls {
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
    max-width: 250px;
    min-width: 180px;
}
.filter-box {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}
.filter-box .form-select {
    min-width: 120px;
    font-size: 0.875rem;
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
        <div class="table-controls">
            <div class="search-box">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control" id="templateSearch" placeholder="Search templates...">
                </div>
            </div>
            <div class="filter-box">
                <select class="form-select" id="channelFilter">
                    <option value="">All Channels</option>
                    <option value="sms">SMS</option>
                    <option value="basic_rcs">Basic RCS + SMS</option>
                    <option value="rich_rcs">Rich RCS + SMS</option>
                </select>
                <select class="form-select" id="triggerFilter">
                    <option value="">All Triggers</option>
                    <option value="api">API</option>
                    <option value="portal">Portal</option>
                    <option value="email">Email-to-SMS</option>
                </select>
                <select class="form-select" id="statusFilter">
                    <option value="">All Statuses</option>
                    <option value="draft">Draft</option>
                    <option value="live">Live</option>
                    <option value="paused">Paused</option>
                    <option value="archived">Archived</option>
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table class="templates-table">
                <thead>
                    <tr>
                        <th data-sort="name" onclick="sortTable('name')">Template Name <i class="fas fa-sort sort-icon"></i></th>
                        <th data-sort="templateId" onclick="sortTable('templateId')">Template ID <i class="fas fa-sort sort-icon"></i></th>
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
        status: 'live',
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
        status: 'live',
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
        status: 'draft',
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
        status: 'paused',
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
        status: 'live',
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
        status: 'archived',
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
        status: 'draft',
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
        status: 'live',
        lastUpdated: '2026-01-02'
    }
];

var sortColumn = 'lastUpdated';
var sortDirection = 'desc';

document.addEventListener('DOMContentLoaded', function() {
    renderTemplates();
    setupEventListeners();
});

function setupEventListeners() {
    document.getElementById('createTemplateBtn').addEventListener('click', showCreateModal);
    document.getElementById('templateContent').addEventListener('input', updateCharCount);
    document.getElementById('templateSearch').addEventListener('input', renderTemplates);
    document.getElementById('channelFilter').addEventListener('change', renderTemplates);
    document.getElementById('triggerFilter').addEventListener('change', renderTemplates);
    document.getElementById('statusFilter').addEventListener('change', renderTemplates);
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
    var search = document.getElementById('templateSearch').value.toLowerCase();
    var channelFilter = document.getElementById('channelFilter').value;
    var triggerFilter = document.getElementById('triggerFilter').value;
    var statusFilter = document.getElementById('statusFilter').value;
    
    var filtered = mockTemplates.filter(function(t) {
        var matchSearch = !search || t.name.toLowerCase().includes(search) || t.templateId.includes(search) || t.content.toLowerCase().includes(search);
        var matchChannel = !channelFilter || t.channel === channelFilter;
        var matchTrigger = !triggerFilter || t.trigger === triggerFilter;
        var matchStatus = !statusFilter || t.status === statusFilter;
        return matchSearch && matchChannel && matchTrigger && matchStatus;
    });
    
    filtered.sort(function(a, b) {
        var aVal = a[sortColumn] || '';
        var bVal = b[sortColumn] || '';
        
        if (sortColumn === 'lastUpdated') {
            aVal = new Date(aVal);
            bVal = new Date(bVal);
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
        html += '<tr>';
        html += '<td><span class="template-name">' + template.name + '</span></td>';
        html += '<td><span class="template-id">' + template.templateId + '</span></td>';
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
        html += '<li><a class="dropdown-item" href="#"><i class="fas fa-eye"></i>View</a></li>';
        html += '<li><a class="dropdown-item" href="#"><i class="fas fa-edit"></i>Edit</a></li>';
        html += '<li><a class="dropdown-item" href="#"><i class="fas fa-copy"></i>Duplicate</a></li>';
        if (template.status === 'live') {
            html += '<li><a class="dropdown-item" href="#"><i class="fas fa-pause"></i>Pause</a></li>';
        } else if (template.status === 'paused' || template.status === 'draft') {
            html += '<li><a class="dropdown-item" href="#"><i class="fas fa-play"></i>Go Live</a></li>';
        }
        if (template.status !== 'archived') {
            html += '<li><a class="dropdown-item" href="#"><i class="fas fa-archive"></i>Archive</a></li>';
        }
        html += '</ul>';
        html += '</div>';
        html += '</td>';
        html += '</tr>';
    });
    
    tbody.innerHTML = html || '<tr><td colspan="9" class="text-center text-muted py-4">No templates match your filters</td></tr>';
}
</script>
@endpush
