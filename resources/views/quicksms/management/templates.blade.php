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
    padding: 0.875rem 1rem;
    font-weight: 600;
    font-size: 0.875rem;
    color: #495057;
    border-bottom: 1px solid #e9ecef;
}
.templates-table tbody td {
    padding: 0.875rem 1rem;
    vertical-align: middle;
    border-bottom: 1px solid #f1f3f5;
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
.template-channel {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
}
.badge-sms {
    background: rgba(48, 101, 208, 0.15);
    color: #3065D0;
}
.badge-rcs {
    background: rgba(136, 108, 192, 0.15);
    color: #886CC0;
}
.template-preview {
    color: #6c757d;
    font-size: 0.875rem;
    max-width: 300px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.action-btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}
.table-controls {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    border-bottom: 1px solid #e9ecef;
    gap: 1rem;
}
.search-box {
    flex: 1;
    max-width: 300px;
}
.filter-box {
    display: flex;
    gap: 0.5rem;
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

    <div id="emptyState" class="empty-state">
        <div class="empty-state-icon">
            <i class="fas fa-file-alt"></i>
        </div>
        <h4>No templates yet</h4>
        <p>Create your first message template to save time when sending messages. Templates can include personalization tags and are available for both SMS and RCS.</p>
        <button class="btn btn-primary" onclick="showCreateModal()">
            <i class="fas fa-plus me-2"></i>Create Template
        </button>
    </div>

    <div id="templatesTableContainer" class="templates-table-container" style="display: none;">
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
                    <option value="rcs">RCS</option>
                </select>
            </div>
        </div>

        <table class="templates-table">
            <thead>
                <tr>
                    <th>Template Name</th>
                    <th>Channel</th>
                    <th>Preview</th>
                    <th>Created</th>
                    <th>Last Modified</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="templatesBody">
            </tbody>
        </table>
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
                    <div class="d-flex gap-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="templateChannel" id="channelSms" value="sms" checked>
                            <label class="form-check-label" for="channelSms">SMS</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="templateChannel" id="channelRcs" value="rcs">
                            <label class="form-check-label" for="channelRcs">RCS</label>
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
var mockTemplates = [];

document.addEventListener('DOMContentLoaded', function() {
    renderTemplates();
    setupEventListeners();
});

function setupEventListeners() {
    document.getElementById('createTemplateBtn').addEventListener('click', showCreateModal);
    document.getElementById('templateContent').addEventListener('input', updateCharCount);
    document.getElementById('templateSearch').addEventListener('input', renderTemplates);
    document.getElementById('channelFilter').addEventListener('change', renderTemplates);
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
    
    if (!name || !content) {
        alert('Please fill in all required fields.');
        return;
    }
    
    var template = {
        id: Date.now(),
        name: name,
        channel: channel,
        content: content,
        created: new Date().toISOString().split('T')[0],
        modified: new Date().toISOString().split('T')[0]
    };
    
    mockTemplates.push(template);
    bootstrap.Modal.getInstance(document.getElementById('createTemplateModal')).hide();
    renderTemplates();
}

function renderTemplates() {
    var search = document.getElementById('templateSearch').value.toLowerCase();
    var channelFilter = document.getElementById('channelFilter').value;
    
    var filtered = mockTemplates.filter(function(t) {
        var matchSearch = !search || t.name.toLowerCase().includes(search) || t.content.toLowerCase().includes(search);
        var matchChannel = !channelFilter || t.channel === channelFilter;
        return matchSearch && matchChannel;
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
        var badgeClass = template.channel === 'sms' ? 'badge-sms' : 'badge-rcs';
        var channelLabel = template.channel.toUpperCase();
        var preview = template.content.length > 50 ? template.content.substring(0, 50) + '...' : template.content;
        
        html += '<tr>';
        html += '<td><span class="template-name">' + template.name + '</span></td>';
        html += '<td><span class="badge rounded-pill ' + badgeClass + '">' + channelLabel + '</span></td>';
        html += '<td><span class="template-preview">' + preview + '</span></td>';
        html += '<td>' + template.created + '</td>';
        html += '<td>' + template.modified + '</td>';
        html += '<td>';
        html += '<button class="btn btn-sm btn-light action-btn me-1" title="Edit"><i class="fas fa-edit"></i></button>';
        html += '<button class="btn btn-sm btn-light action-btn me-1" title="Duplicate"><i class="fas fa-copy"></i></button>';
        html += '<button class="btn btn-sm btn-light action-btn text-danger" title="Delete" onclick="deleteTemplate(' + template.id + ')"><i class="fas fa-trash"></i></button>';
        html += '</td>';
        html += '</tr>';
    });
    
    tbody.innerHTML = html || '<tr><td colspan="6" class="text-center text-muted py-4">No templates match your search</td></tr>';
}

function deleteTemplate(id) {
    if (confirm('Are you sure you want to delete this template?')) {
        mockTemplates = mockTemplates.filter(function(t) { return t.id !== id; });
        renderTemplates();
    }
}
</script>
@endpush
