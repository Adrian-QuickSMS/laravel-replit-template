@extends('layouts.quicksms')

@section('title', 'Tags')

@push('styles')
<style>
/* API Integration code blocks - black background with white text */
#api code.bg-light,
#api pre.bg-light {
    background-color: #1e1e1e !important;
    color: #f8f8f2 !important;
}
/* API Integration info box - pastel purple with black text and purple icon */
#api .alert-info {
    background-color: rgba(111, 66, 193, 0.08) !important;
    border: 1px solid rgba(111, 66, 193, 0.2) !important;
    color: #1f2937 !important;
}
#api .alert-info i {
    color: #6f42c1 !important;
}
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
.tags-table-container {
    background: #fff;
    border-radius: 0.75rem;
    border: 1px solid #e9ecef;
    overflow: visible;
}
.table-responsive {
    overflow: visible !important;
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

                    <div class="tags-table-container">
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
                                        <a href="#!" onclick="viewTaggedContacts('{{ $tag['id'] }}', '{{ $tag['name'] }}')" class="text-decoration-none">
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
                                                    <a class="dropdown-item" href="#!" onclick="viewTaggedContacts('{{ $tag['id'] }}', '{{ $tag['name'] }}')">
                                                        <i class="fas fa-users me-2 text-dark"></i>View Contacts
                                                    </a>
                                                    <a class="dropdown-item" href="#!" onclick="editTag('{{ $tag['id'] }}', '{{ $tag['name'] }}', '{{ $tag['color'] }}')">
                                                        <i class="fas fa-edit me-2 text-dark"></i>Edit Tag
                                                    </a>
                                                    <a class="dropdown-item" href="#!" onclick="mergeTag('{{ $tag['id'] }}', '{{ $tag['name'] }}')">
                                                        <i class="fas fa-compress-arrows-alt me-2 text-dark"></i>Merge Into...
                                                    </a>
                                                    <div class="dropdown-divider"></div>
                                                    <a class="dropdown-item text-danger" href="#!" onclick="deleteTag('{{ $tag['id'] }}', '{{ $tag['name'] }}', {{ $tag['contact_count'] }})">
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
                <div class="alert" style="background-color: #f0ebf8; border-color: #d8d0e8; color: #6b5b95;">
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
                <button type="button" class="btn" style="background-color: #6b5b95; color: white;" onclick="confirmMergeTag()">
                    <i class="fas fa-compress-arrows-alt me-1"></i> Merge Tags
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Merge Confirmation Modal -->
<div class="modal fade" id="mergeConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-white border-bottom">
                <h5 class="modal-title text-dark"><i class="fas fa-exclamation-triangle me-2 text-warning"></i>Confirm Merge</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="fas fa-compress-arrows-alt text-warning fa-3x mb-3"></i>
                <p class="mb-2" id="mergeConfirmMessage">Are you sure you want to merge these tags?</p>
                <p class="text-muted small mb-0">This action cannot be undone.</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-sm" style="background-color: #6b5b95; color: white;" id="confirmMergeBtn">Merge Tags</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteTagConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-white border-bottom">
                <h5 class="modal-title text-dark"><i class="fas fa-trash me-2 text-danger"></i>Delete Tag</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="fas fa-trash text-danger fa-3x mb-3"></i>
                <p class="mb-2" id="deleteTagConfirmMessage">Are you sure you want to delete this tag?</p>
                <p class="text-muted small mb-0" id="deleteTagContactWarning"></p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger btn-sm" id="confirmDeleteTagBtn">Delete Tag</button>
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

var _csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
function _apiHeaders() {
    return { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': _csrfToken };
}
function _handleApiResponse(response) {
    if (!response.ok) {
        return response.json().then(function(err) {
            var msg = err.message || '';
            if (err.errors) {
                var firstField = Object.keys(err.errors)[0];
                if (firstField && err.errors[firstField].length) {
                    msg = err.errors[firstField][0];
                }
            }
            throw new Error(msg || 'Request failed');
        }).catch(function(e) {
            if (e instanceof Error && e.message) throw e;
            throw new Error('Request failed');
        });
    }
    return response.json();
}

function confirmCreateTag() {
    var name = document.getElementById('newTagName').value.trim();
    var color = document.getElementById('selectedColor').value;
    
    if (!validateTagName(name, 'tagNameError', 'newTagName')) {
        return;
    }
    
    fetch('/api/tags', {
        method: 'POST',
        headers: _apiHeaders(),
        body: JSON.stringify({ name: name, color: color })
    })
    .then(_handleApiResponse)
    .then(function() {
        showToast('Tag "' + name + '" created successfully', 'success');
        var modal = bootstrap.Modal.getInstance(document.getElementById('createTagModal'));
        modal.hide();
        document.getElementById('newTagName').value = '';
        document.getElementById('tagPreviewText').textContent = 'New Tag';
        selectColor('#6f42c1');
        setTimeout(function() { location.reload(); }, 800);
    })
    .catch(function(err) {
        showToast(err.message || 'Failed to create tag', 'error');
    });
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
    
    fetch('/api/tags/' + id, {
        method: 'PUT',
        headers: _apiHeaders(),
        body: JSON.stringify({ name: name, color: color })
    })
    .then(_handleApiResponse)
    .then(function() {
        showToast('Tag updated to "' + name + '"', 'success');
        var modal = bootstrap.Modal.getInstance(document.getElementById('editTagModal'));
        modal.hide();
        setTimeout(function() { location.reload(); }, 800);
    })
    .catch(function(err) {
        showToast(err.message || 'Failed to update tag', 'error');
    });
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

var pendingMergeData = null;

function confirmMergeTag() {
    var sourceId = document.getElementById('mergeSourceTagId').value;
    var targetId = document.getElementById('mergeTargetTag').value;
    var sourceName = document.getElementById('mergeSourceTagName').value;
    
    if (!targetId) {
        showToast('Please select a target tag.', 'warning');
        return;
    }
    
    var targetTag = localTags.find(function(t) { return t.id == targetId; });
    
    // Store pending merge data
    pendingMergeData = {
        sourceId: sourceId,
        targetId: targetId,
        sourceName: sourceName,
        targetName: targetTag.name
    };
    
    // Hide merge modal first
    var mergeModal = bootstrap.Modal.getInstance(document.getElementById('mergeTagModal'));
    
    document.getElementById('mergeTagModal').addEventListener('hidden.bs.modal', function onHidden() {
        document.getElementById('mergeTagModal').removeEventListener('hidden.bs.modal', onHidden);
        
        // Show confirmation modal
        document.getElementById('mergeConfirmMessage').innerHTML = 
            'Merge "<strong>' + pendingMergeData.sourceName + '</strong>" into "<strong>' + pendingMergeData.targetName + '</strong>"?';
        
        var confirmModal = new bootstrap.Modal(document.getElementById('mergeConfirmModal'));
        confirmModal.show();
    }, { once: true });
    
    mergeModal.hide();
}

function executeMergeTag() {
    if (!pendingMergeData) return;
    
    fetch('/api/contacts/bulk/remove-tags', {
        method: 'POST',
        headers: _apiHeaders(),
        body: JSON.stringify({ tag_ids: [pendingMergeData.sourceId], target_tag_id: pendingMergeData.targetId })
    })
    .then(function(response) {
        return fetch('/api/tags/' + pendingMergeData.sourceId, {
            method: 'DELETE',
            headers: _apiHeaders()
        });
    })
    .then(_handleApiResponse)
    .then(function() {
        var confirmModal = bootstrap.Modal.getInstance(document.getElementById('mergeConfirmModal'));
        confirmModal.hide();
        showToast('Tags merged successfully', 'success');
        pendingMergeData = null;
        setTimeout(function() { location.reload(); }, 800);
    })
    .catch(function(err) {
        showToast(err.message || 'Failed to merge tags', 'error');
    });
}

// Set up confirm merge button click handler
document.addEventListener('DOMContentLoaded', function() {
    var confirmMergeBtn = document.getElementById('confirmMergeBtn');
    if (confirmMergeBtn) {
        confirmMergeBtn.addEventListener('click', executeMergeTag);
    }
    
    var confirmDeleteTagBtn = document.getElementById('confirmDeleteTagBtn');
    if (confirmDeleteTagBtn) {
        confirmDeleteTagBtn.addEventListener('click', executeDeleteTag);
    }
});

var pendingDeleteTag = null;

function deleteTag(id, name, contactCount) {
    pendingDeleteTag = { id: id, name: name, contactCount: contactCount };
    
    document.getElementById('deleteTagConfirmMessage').innerHTML = 
        'Are you sure you want to delete the tag "<strong>' + name + '</strong>"?';
    
    if (contactCount > 0) {
        document.getElementById('deleteTagContactWarning').innerHTML = 
            'This tag is applied to <strong>' + contactCount.toLocaleString() + '</strong> contact(s). The tag will be removed from all contacts.';
    } else {
        document.getElementById('deleteTagContactWarning').textContent = '';
    }
    
    var confirmModal = new bootstrap.Modal(document.getElementById('deleteTagConfirmModal'));
    confirmModal.show();
}

function executeDeleteTag() {
    if (!pendingDeleteTag) return;
    
    fetch('/api/tags/' + pendingDeleteTag.id, {
        method: 'DELETE',
        headers: _apiHeaders()
    })
    .then(_handleApiResponse)
    .then(function() {
        var confirmModal = bootstrap.Modal.getInstance(document.getElementById('deleteTagConfirmModal'));
        confirmModal.hide();
        showToast('Tag "' + pendingDeleteTag.name + '" deleted', 'success');
        pendingDeleteTag = null;
        setTimeout(function() { location.reload(); }, 800);
    })
    .catch(function(err) {
        showToast(err.message || 'Failed to delete tag', 'error');
    });
}

function viewTaggedContacts(id, name) {
    document.getElementById('viewTagName').textContent = name;
    
    var tbody = document.getElementById('taggedContactsList');
    tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-3">Loading...</td></tr>';
    
    var modal = new bootstrap.Modal(document.getElementById('viewContactsModal'));
    modal.show();
    
    fetch('/api/contacts?tag=' + encodeURIComponent(name) + '&per_page=500', { headers: _apiHeaders() })
    .then(_handleApiResponse)
    .then(function(result) {
        var contacts = result.data || result || [];
        tbody.innerHTML = '';
        if (contacts.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-3">No contacts with this tag</td></tr>';
        } else {
            contacts.forEach(function(contact) {
                var displayName = (contact.first_name || '') + ' ' + (contact.last_name || '');
                displayName = displayName.trim() || 'Unknown';
                var mobile = contact.mobile_display || contact.msisdn || '';
                var row = document.createElement('tr');
                row.innerHTML =
                    '<td style="color: #000;">' + displayName + '</td>' +
                    '<td style="color: #000;">' + mobile + '</td>' +
                    '<td style="color: #000;">' + (contact.created_at ? contact.created_at.substring(0, 10) : '') + '</td>' +
                    '<td class="text-end"><button class="btn btn-sm btn-outline-danger" onclick="removeTagFromContact(\'' + contact.id + '\', \'' + id + '\')"><i class="fas fa-times"></i> Remove</button></td>';
                tbody.appendChild(row);
            });
        }
        document.getElementById('taggedContactsCount').textContent = contacts.length;
    })
    .catch(function(err) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center text-danger py-3">Failed to load contacts</td></tr>';
        document.getElementById('taggedContactsCount').textContent = '0';
    });
}

function removeTagFromContact(contactId, tagId) {
    fetch('/api/contacts/bulk/remove-tags', {
        method: 'POST',
        headers: _apiHeaders(),
        body: JSON.stringify({ contact_ids: [contactId], tag_ids: [tagId] })
    })
    .then(_handleApiResponse)
    .then(function() {
        showToast('Tag removed from contact successfully', 'success');
        setTimeout(function() { location.reload(); }, 800);
    })
    .catch(function(err) {
        showToast(err.message || 'Failed to remove tag', 'error');
    });
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
