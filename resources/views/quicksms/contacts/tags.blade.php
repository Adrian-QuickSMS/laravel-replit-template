@extends('layouts.quicksms')

@section('title', 'Tags')

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
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row page-titles">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('contacts.all') }}">Contact Book</a></li>
            <li class="breadcrumb-item active">Tags</li>
        </ol>
    </div>
    
    <div class="row">
        <div class="col-12">
            <ul class="nav nav-tabs" id="tagsTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="manage-tab" data-bs-toggle="tab" data-bs-target="#manage" type="button" role="tab">
                        <i class="fas fa-tags me-2"></i>Manage Tags <span class="badge badge-pastel-primary ms-1">{{ count($tags) }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="api-tab" data-bs-toggle="tab" data-bs-target="#api" type="button" role="tab">
                        <i class="fas fa-code me-2"></i>API Integration
                    </button>
                </li>
            </ul>
            
            <div class="tab-content" id="tagsTabContent">
                <div class="tab-pane fade show active" id="manage" role="tabpanel">
                    <div class="card border-top-0 rounded-top-0">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="card-title mb-0">Manage Tags</h5>
                                <small class="text-muted">Lightweight, flexible classification for contacts</small>
                            </div>
                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createTagModal">
                                <i class="fas fa-plus me-1"></i> Create Tag
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-pastel-primary mb-4">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-info-circle text-primary me-3 mt-1"></i>
                                    <div>
                                        <strong>Tags are labels, not audience definitions.</strong>
                                        <p class="mb-0 mt-1 small">
                                            Tags provide flexible classification for filtering, campaign targeting, and API-driven state markers. 
                                            Unlike lists, tags do not store ordering or membership history. Use Lists for managing audiences.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="fas fa-search"></i></span>
                                        <input type="text" class="form-control" id="tagSearch" placeholder="Search tags...">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-select" id="sourceFilter">
                                        <option value="">All Sources</option>
                                        <option value="manual">Manual</option>
                                        <option value="campaign">Campaign Auto-tag</option>
                                        <option value="api">API</option>
                                    </select>
                                </div>
                            </div>

                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="tagsTable">
                            <thead>
                                <tr>
                                    <th>Tag</th>
                                    <th>Contacts</th>
                                    <th>Source</th>
                                    <th>Created</th>
                                    <th>Last Used</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="tagsTableBody">
                                @foreach($tags as $tag)
                                <tr data-tag-id="{{ $tag['id'] }}" data-source="{{ $tag['source'] }}">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="badge me-2" style="background-color: {{ $tag['color'] }}; width: 12px; height: 12px; padding: 0; border-radius: 50%;"></span>
                                            <span>{{ $tag['name'] }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="#!" onclick="viewTaggedContacts({{ $tag['id'] }}, '{{ $tag['name'] }}')" class="text-decoration-none">
                                            <span class="badge badge-pastel-secondary">
                                                <i class="fas fa-users me-1"></i>{{ number_format($tag['contact_count']) }}
                                            </span>
                                        </a>
                                    </td>
                                    <td>
                                        @if($tag['source'] === 'manual')
                                            <span class="badge badge-pastel-warning">Manual</span>
                                        @elseif($tag['source'] === 'campaign')
                                            <span class="badge badge-pastel-pink">Campaign</span>
                                        @elseif($tag['source'] === 'api')
                                            <span class="badge badge-pastel-primary">API</span>
                                        @endif
                                    </td>
                                    <td style="color: #000;">{{ \Carbon\Carbon::parse($tag['created_at'])->format('d-m-Y') }}</td>
                                    <td style="color: #000;">{{ \Carbon\Carbon::parse($tag['last_used'])->format('d-m-Y') }}</td>
                                    <td class="text-end">
                                        <div class="dropdown">
                                            <button class="btn btn-primary tp-btn-light sharp" type="button" data-bs-toggle="dropdown">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end border py-0">
                                                <div class="dropdown-content">
                                                    <a class="dropdown-item" href="#!" onclick="viewTaggedContacts({{ $tag['id'] }}, '{{ $tag['name'] }}')">
                                                        <i class="fas fa-users me-2 text-info"></i>View Contacts
                                                    </a>
                                                    <a class="dropdown-item" href="#!" onclick="editTag({{ $tag['id'] }}, '{{ $tag['name'] }}', '{{ $tag['color'] }}')">
                                                        <i class="fas fa-edit me-2 text-primary"></i>Edit Tag
                                                    </a>
                                                    <a class="dropdown-item" href="#!" onclick="mergeTag({{ $tag['id'] }}, '{{ $tag['name'] }}')">
                                                        <i class="fas fa-compress-arrows-alt me-2 text-warning"></i>Merge Into...
                                                    </a>
                                                    <div class="dropdown-divider"></div>
                                                    <a class="dropdown-item text-danger" href="#!" onclick="deleteTag({{ $tag['id'] }}, '{{ $tag['name'] }}', {{ $tag['contact_count'] }})">
                                                        <i class="fas fa-trash me-2"></i>Delete Tag
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-4">
                                <div class="text-muted small">
                                    Showing <span id="visibleCount">{{ count($tags) }}</span> of {{ count($tags) }} tags
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="tab-pane fade" id="api" role="tabpanel">
                    <div class="card border-top-0 rounded-top-0">
                        <div class="card-header">
                            <h5 class="card-title mb-0">API Integration</h5>
                            <small class="text-muted">External systems can manage tags via the API</small>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info mb-4">
                                <i class="fas fa-info-circle me-2"></i>
                                Use these API endpoints to programmatically manage tags from external systems, CRMs, or automation workflows.
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <div class="border rounded p-3 h-100">
                                        <h6><i class="fas fa-plus-circle text-success me-2"></i>Apply Tags</h6>
                                        <p class="small text-muted mb-2">Add tags to contacts programmatically</p>
                                        <code class="small d-block bg-light p-2 rounded">POST /api/contacts/{id}/tags</code>
                                        <p class="small text-muted mt-2 mb-0">Body: <code>{"tags": ["vip", "customer"]}</code></p>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="border rounded p-3 h-100">
                                        <h6><i class="fas fa-minus-circle text-danger me-2"></i>Remove Tags</h6>
                                        <p class="small text-muted mb-2">Remove tags from contacts</p>
                                        <code class="small d-block bg-light p-2 rounded">DELETE /api/contacts/{id}/tags/{tag}</code>
                                        <p class="small text-muted mt-2 mb-0">Returns: <code>204 No Content</code></p>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="border rounded p-3 h-100">
                                        <h6><i class="fas fa-rocket text-primary me-2"></i>Trigger Campaigns</h6>
                                        <p class="small text-muted mb-2">Start campaigns based on tags</p>
                                        <code class="small d-block bg-light p-2 rounded">POST /api/campaigns/trigger?tag={tag}</code>
                                        <p class="small text-muted mt-2 mb-0">Sends to all contacts with tag</p>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-4 mb-3">
                                    <div class="border rounded p-3 h-100">
                                        <h6><i class="fas fa-list text-info me-2"></i>List All Tags</h6>
                                        <p class="small text-muted mb-2">Get all tags with contact counts</p>
                                        <code class="small d-block bg-light p-2 rounded">GET /api/tags</code>
                                        <p class="small text-muted mt-2 mb-0">Returns: Array of tag objects</p>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="border rounded p-3 h-100">
                                        <h6><i class="fas fa-tag text-warning me-2"></i>Create Tag</h6>
                                        <p class="small text-muted mb-2">Create a new tag via API</p>
                                        <code class="small d-block bg-light p-2 rounded">POST /api/tags</code>
                                        <p class="small text-muted mt-2 mb-0">Body: <code>{"name": "new-tag"}</code></p>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="border rounded p-3 h-100">
                                        <h6><i class="fas fa-users text-secondary me-2"></i>Get Tagged Contacts</h6>
                                        <p class="small text-muted mb-2">List contacts with specific tag</p>
                                        <code class="small d-block bg-light p-2 rounded">GET /api/tags/{tag}/contacts</code>
                                        <p class="small text-muted mt-2 mb-0">Supports pagination</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="createTagModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-tag me-2"></i>Create Tag</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Tag Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="newTagName" placeholder="Enter tag name" maxlength="50">
                    <div class="invalid-feedback" id="tagNameError"></div>
                    <small class="text-muted">Max 50 characters. Use lowercase with hyphens for API compatibility.</small>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Color</label>
                    <div class="d-flex flex-wrap gap-2" id="colorPicker">
                        @foreach($available_colors as $hex => $name)
                        <div class="color-option" data-color="{{ $hex }}" title="{{ $name }}" 
                             style="width: 32px; height: 32px; background-color: {{ $hex }}; border-radius: 50%; cursor: pointer; border: 3px solid transparent;"
                             onclick="selectColor('{{ $hex }}')"></div>
                        @endforeach
                    </div>
                    <input type="hidden" id="selectedColor" value="#6f42c1">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Preview</label>
                    <div>
                        <span class="badge" id="tagPreview" style="background-color: #6f42c1; font-size: 14px; padding: 8px 12px;">
                            <i class="fas fa-tag me-1"></i><span id="tagPreviewText">New Tag</span>
                        </span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="confirmCreateTag()">
                    <i class="fas fa-check me-1"></i> Create Tag
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editTagModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Tag</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="editTagId">
                <div class="mb-3">
                    <label class="form-label fw-bold">Tag Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="editTagName" placeholder="Enter tag name" maxlength="50">
                    <div class="invalid-feedback" id="editTagNameError"></div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Color</label>
                    <div class="d-flex flex-wrap gap-2" id="editColorPicker">
                        @foreach($available_colors as $hex => $name)
                        <div class="edit-color-option" data-color="{{ $hex }}" title="{{ $name }}" 
                             style="width: 32px; height: 32px; background-color: {{ $hex }}; border-radius: 50%; cursor: pointer; border: 3px solid transparent;"
                             onclick="selectEditColor('{{ $hex }}')"></div>
                        @endforeach
                    </div>
                    <input type="hidden" id="editSelectedColor" value="">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="confirmEditTag()">
                    <i class="fas fa-save me-1"></i> Save Changes
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="mergeTagModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-compress-arrows-alt me-2"></i>Merge Tag</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="mergeSourceTagId">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Warning:</strong> Merging is permanent. The source tag will be deleted and all contacts will be moved to the target tag.
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Source Tag</label>
                    <input type="text" class="form-control" id="mergeSourceTagName" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Merge Into <span class="text-danger">*</span></label>
                    <select class="form-select" id="mergeTargetTag">
                        <option value="">Select target tag...</option>
                        @foreach($tags as $tag)
                        <option value="{{ $tag['id'] }}">{{ $tag['name'] }} ({{ number_format($tag['contact_count']) }} contacts)</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" onclick="confirmMergeTag()">
                    <i class="fas fa-compress-arrows-alt me-1"></i> Merge Tags
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="viewContactsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-users me-2"></i>Contacts tagged with <span id="viewTagName" class="badge bg-primary"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <input type="text" class="form-control" id="taggedContactSearch" placeholder="Search contacts...">
                </div>
                <div class="border rounded" style="max-height: 350px; overflow-y: auto;">
                    <table class="table table-hover mb-0">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th>Name</th>
                                <th>Mobile</th>
                                <th>Tagged On</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="taggedContactsList">
                        </tbody>
                    </table>
                </div>
                <div class="mt-2 text-muted small">
                    Showing <span id="taggedContactsCount">0</span> contact(s)
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
var tagsData = @json($tags);
var localTags = [...tagsData];

document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('tagSearch').addEventListener('input', filterTags);
    document.getElementById('sourceFilter').addEventListener('change', filterTags);
    
    document.getElementById('newTagName').addEventListener('input', function() {
        var name = this.value.trim() || 'New Tag';
        document.getElementById('tagPreviewText').textContent = name;
        validateTagName(this.value, 'tagNameError', 'newTagName');
    });
    
    selectColor('#6f42c1');
});

function filterTags() {
    var searchTerm = document.getElementById('tagSearch').value.toLowerCase();
    var sourceFilter = document.getElementById('sourceFilter').value;
    var rows = document.querySelectorAll('#tagsTableBody tr');
    var visibleCount = 0;
    
    rows.forEach(function(row) {
        var tagNameEl = row.querySelector('td:first-child span:last-child');
        var tagName = tagNameEl ? tagNameEl.textContent.toLowerCase() : '';
        var source = row.getAttribute('data-source');
        var matchesSearch = tagName.includes(searchTerm);
        var matchesSource = !sourceFilter || source === sourceFilter;
        
        if (matchesSearch && matchesSource) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
    
    document.getElementById('visibleCount').textContent = visibleCount;
}

function selectColor(color) {
    document.querySelectorAll('.color-option').forEach(function(el) {
        el.style.border = '3px solid transparent';
    });
    document.querySelector('.color-option[data-color="' + color + '"]').style.border = '3px solid #000';
    document.getElementById('selectedColor').value = color;
    document.getElementById('tagPreview').style.backgroundColor = color;
}

function selectEditColor(color) {
    document.querySelectorAll('.edit-color-option').forEach(function(el) {
        el.style.border = '3px solid transparent';
    });
    document.querySelector('.edit-color-option[data-color="' + color + '"]').style.border = '3px solid #000';
    document.getElementById('editSelectedColor').value = color;
}

function validateTagName(name, errorId, inputId) {
    var errorEl = document.getElementById(errorId);
    var inputEl = document.getElementById(inputId);
    var trimmed = name.trim();
    
    if (!trimmed) {
        errorEl.textContent = 'Tag name is required';
        inputEl.classList.add('is-invalid');
        return false;
    }
    
    if (trimmed.length < 2) {
        errorEl.textContent = 'Tag name must be at least 2 characters';
        inputEl.classList.add('is-invalid');
        return false;
    }
    
    var exists = localTags.some(function(t) {
        return t.name.toLowerCase() === trimmed.toLowerCase();
    });
    
    if (exists && inputId === 'newTagName') {
        errorEl.textContent = 'A tag with this name already exists';
        inputEl.classList.add('is-invalid');
        return false;
    }
    
    inputEl.classList.remove('is-invalid');
    return true;
}

function confirmCreateTag() {
    var name = document.getElementById('newTagName').value.trim();
    var color = document.getElementById('selectedColor').value;
    
    if (!validateTagName(name, 'tagNameError', 'newTagName')) {
        return;
    }
    
    var newTag = {
        id: Date.now(),
        name: name,
        color: color,
        contact_count: 0,
        created_at: new Date().toISOString().split('T')[0],
        last_used: null,
        source: 'manual'
    };
    
    localTags.push(newTag);
    
    console.log('TODO: API call POST /api/tags to create tag');
    console.log('New tag:', newTag);
    
    showToast('Tag "' + name + '" created successfully', 'success');
    
    var modal = bootstrap.Modal.getInstance(document.getElementById('createTagModal'));
    modal.hide();
    
    document.getElementById('newTagName').value = '';
    document.getElementById('tagPreviewText').textContent = 'New Tag';
    selectColor('#6f42c1');
}

function editTag(id, name, color) {
    document.getElementById('editTagId').value = id;
    document.getElementById('editTagName').value = name;
    document.getElementById('editSelectedColor').value = color;
    
    selectEditColor(color);
    
    var modal = new bootstrap.Modal(document.getElementById('editTagModal'));
    modal.show();
}

function confirmEditTag() {
    var id = document.getElementById('editTagId').value;
    var name = document.getElementById('editTagName').value.trim();
    var color = document.getElementById('editSelectedColor').value;
    
    if (!name) {
        document.getElementById('editTagNameError').textContent = 'Tag name is required';
        document.getElementById('editTagName').classList.add('is-invalid');
        return;
    }
    
    var tagIndex = localTags.findIndex(function(t) { return t.id == id; });
    if (tagIndex !== -1) {
        localTags[tagIndex].name = name;
        localTags[tagIndex].color = color;
    }
    
    console.log('TODO: API call PUT /api/tags/' + id);
    showToast('Tag updated to "' + name + '"', 'success');
    
    var modal = bootstrap.Modal.getInstance(document.getElementById('editTagModal'));
    modal.hide();
}

function mergeTag(id, name) {
    document.getElementById('mergeSourceTagId').value = id;
    document.getElementById('mergeSourceTagName').value = name;
    
    var select = document.getElementById('mergeTargetTag');
    Array.from(select.options).forEach(function(opt) {
        opt.disabled = (opt.value == id);
    });
    select.value = '';
    
    var modal = new bootstrap.Modal(document.getElementById('mergeTagModal'));
    modal.show();
}

function confirmMergeTag() {
    var sourceId = document.getElementById('mergeSourceTagId').value;
    var targetId = document.getElementById('mergeTargetTag').value;
    var sourceName = document.getElementById('mergeSourceTagName').value;
    
    if (!targetId) {
        alert('Please select a target tag.');
        return;
    }
    
    var targetTag = localTags.find(function(t) { return t.id == targetId; });
    
    if (confirm('Merge "' + sourceName + '" into "' + targetTag.name + '"?\n\nThis action cannot be undone.')) {
        console.log('TODO: API call POST /api/tags/' + sourceId + '/merge/' + targetId);
        
        localTags = localTags.filter(function(t) { return t.id != sourceId; });
        
        showToast('Tags merged successfully', 'success');
        
        var modal = bootstrap.Modal.getInstance(document.getElementById('mergeTagModal'));
        modal.hide();
    }
}

function deleteTag(id, name, contactCount) {
    var message = 'Are you sure you want to delete the tag "' + name + '"?';
    if (contactCount > 0) {
        message += '\n\nThis tag is applied to ' + contactCount.toLocaleString() + ' contact(s). The tag will be removed from all contacts.';
    }
    
    if (confirm(message)) {
        console.log('TODO: API call DELETE /api/tags/' + id);
        
        localTags = localTags.filter(function(t) { return t.id != id; });
        
        showToast('Tag "' + name + '" deleted', 'success');
    }
}

function viewTaggedContacts(id, name) {
    document.getElementById('viewTagName').textContent = name;
    
    var mockContacts = [
        { id: 1, name: 'Emma Thompson', mobile: '+44 77** ***123', tagged_on: '2024-12-15' },
        { id: 2, name: 'James Wilson', mobile: '+44 77** ***456', tagged_on: '2024-12-10' },
        { id: 3, name: 'Sarah Mitchell', mobile: '+44 77** ***789', tagged_on: '2024-11-28' },
        { id: 4, name: 'Michael Brown', mobile: '+44 77** ***321', tagged_on: '2024-11-20' },
    ];
    
    var tbody = document.getElementById('taggedContactsList');
    tbody.innerHTML = '';
    
    mockContacts.forEach(function(contact) {
        var row = document.createElement('tr');
        row.innerHTML = `
            <td style="color: #000;">${contact.name}</td>
            <td style="color: #000;">${contact.mobile}</td>
            <td style="color: #000;">${contact.tagged_on}</td>
            <td class="text-end">
                <button class="btn btn-sm btn-outline-danger" onclick="removeTagFromContact(${contact.id}, ${id})">
                    <i class="fas fa-times"></i> Remove
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });
    
    document.getElementById('taggedContactsCount').textContent = mockContacts.length;
    
    console.log('TODO: API call GET /api/tags/' + id + '/contacts');
    
    var modal = new bootstrap.Modal(document.getElementById('viewContactsModal'));
    modal.show();
}

function removeTagFromContact(contactId, tagId) {
    console.log('TODO: API call DELETE /api/contacts/' + contactId + '/tags/' + tagId);
    showToast('Tag removed from contact successfully', 'success');
}

document.addEventListener('DOMContentLoaded', function() {
    var searchInput = document.getElementById('taggedContactSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            var searchTerm = this.value.toLowerCase();
            var rows = document.querySelectorAll('#taggedContactsBody tr');
            var visibleCount = 0;
            
            rows.forEach(function(row) {
                var name = row.cells[0] ? row.cells[0].textContent.toLowerCase() : '';
                var mobile = row.cells[1] ? row.cells[1].textContent.toLowerCase() : '';
                
                if (name.includes(searchTerm) || mobile.includes(searchTerm)) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });
            
            document.getElementById('taggedContactsCount').textContent = visibleCount;
        });
    }
});

function showToast(message, type) {
    type = type || 'success';
    var container = document.getElementById('toastContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toastContainer';
        container.className = 'position-fixed bottom-0 end-0 p-3';
        container.style.zIndex = '1100';
        document.body.appendChild(container);
    }
    
    var bgColor = type === 'success' ? '#6b5b95' : (type === 'error' ? '#dc3545' : '#6c757d');
    var icon = type === 'success' ? 'fa-check-circle' : (type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle');
    
    var toastId = 'toast_' + Date.now();
    var toastHtml = '<div id="' + toastId + '" class="toast align-items-center text-white border-0 show" role="alert" style="background-color: ' + bgColor + ';">' +
        '<div class="d-flex">' +
        '<div class="toast-body"><i class="fas ' + icon + ' me-2"></i>' + message + '</div>' +
        '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>' +
        '</div></div>';
    
    container.insertAdjacentHTML('beforeend', toastHtml);
    
    setTimeout(function() {
        var toast = document.getElementById(toastId);
        if (toast) toast.remove();
    }, 4000);
}
</script>
@endsection
