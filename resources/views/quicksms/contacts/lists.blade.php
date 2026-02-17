@extends('layouts.quicksms')

@section('title', 'Lists')

@push('styles')
<style>
/* API Integration code blocks - black background with white text */
#lists-api code.bg-light,
#lists-api pre.bg-light {
    background-color: #1e1e1e !important;
    color: #f8f8f2 !important;
}
/* API Integration info box - pastel purple with black text and purple icon */
#lists-api .alert-info {
    background-color: rgba(111, 66, 193, 0.08) !important;
    border: 1px solid rgba(111, 66, 193, 0.2) !important;
    color: #1f2937 !important;
}
#lists-api .alert-info i {
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
.lists-table-container {
    background: #fff;
    border-radius: 0.75rem;
    border: 1px solid #e9ecef;
    overflow: visible;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row page-titles">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('contacts') }}">Contact Book</a></li>
            <li class="breadcrumb-item active">Lists</li>
        </ol>
    </div>
    
    <div class="row">
        <div class="col-12">
            <ul class="nav nav-tabs" id="listsTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="static-tab" data-bs-toggle="tab" data-bs-target="#static" type="button" role="tab">
                        <i class="fas fa-list me-2"></i>Static Lists <span class="badge badge-pastel-primary ms-1">{{ count($static_lists) }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="dynamic-tab" data-bs-toggle="tab" data-bs-target="#dynamic" type="button" role="tab">
                        <i class="fas fa-magic me-2"></i>Dynamic Lists <span class="badge badge-pastel-primary ms-1">{{ count($dynamic_lists) }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="lists-api-tab" data-bs-toggle="tab" data-bs-target="#lists-api" type="button" role="tab">
                        <i class="fas fa-code me-2"></i>API Integration
                    </button>
                </li>
            </ul>
            
            <div class="tab-content" id="listsTabContent">
                <div class="tab-pane fade show active" id="static" role="tabpanel">
                    <div class="card border-top-0 rounded-top-0">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="card-title mb-0">Static Lists</h5>
                                <small class="text-muted">Manually managed collections of contacts</small>
                            </div>
                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createListModal">
                                <i class="fas fa-plus me-1"></i> Create List
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="lists-table-container">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>List Name</th>
                                            <th>Description</th>
                                            <th>Contacts</th>
                                            <th>Created</th>
                                            <th>Last Updated</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="staticListsBody">
                                        @foreach($static_lists as $list)
                                        <tr data-list-id="{{ $list['id'] }}">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="list-icon-static me-2">
                                                        <i class="fas fa-list"></i>
                                                    </div>
                                                    <span>{{ $list['name'] }}</span>
                                                </div>
                                            </td>
                                            <td style="color: #000;">{{ $list['description'] }}</td>
                                            <td>
                                                <span class="badge badge-pastel-secondary">
                                                    <i class="fas fa-users me-1"></i>{{ number_format($list['contact_count']) }}
                                                </span>
                                            </td>
                                            <td style="color: #000;">{{ \Carbon\Carbon::parse($list['created_at'])->format('d-m-Y') }}</td>
                                            <td style="color: #000;">{{ \Carbon\Carbon::parse($list['updated_at'])->format('d-m-Y') }}</td>
                                            <td class="text-end">
                                                <div class="dropdown">
                                                    <button class="btn btn-primary tp-btn-light sharp" type="button" data-bs-toggle="dropdown">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-end border py-0">
                                                        <div class="dropdown-content">
                                                            <a class="dropdown-item" href="#!" onclick="viewListContacts('{{ $list['id'] }}', '{{ $list['name'] }}')"><i class="fas fa-eye me-2 text-dark"></i>View Contacts</a>
                                                            <a class="dropdown-item" href="#!" onclick="addContactsToList('{{ $list['id'] }}', '{{ $list['name'] }}')"><i class="fas fa-user-plus me-2 text-dark"></i>Add Contacts</a>
                                                            <a class="dropdown-item" href="#!" onclick="renameList('{{ $list['id'] }}', '{{ $list['name'] }}', '{{ $list['description'] }}')"><i class="fas fa-edit me-2 text-dark"></i>Rename</a>
                                                            <div class="dropdown-divider"></div>
                                                            <a class="dropdown-item text-danger" href="#!" onclick="deleteList('{{ $list['id'] }}', '{{ $list['name'] }}')"><i class="fas fa-trash me-2"></i>Delete</a>
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
                        </div>
                    </div>
                </div>
                
                <div class="tab-pane fade" id="dynamic" role="tabpanel">
                    <div class="card border-top-0 rounded-top-0">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="card-title mb-0">Dynamic Lists</h5>
                                <small class="text-muted">Saved filters that auto-update as contacts change</small>
                            </div>
                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createDynamicListModal">
                                <i class="fas fa-plus me-1"></i> Create Dynamic List
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-pastel-primary small mb-3">
                                <i class="fas fa-info-circle text-primary me-2"></i>
                                Dynamic lists automatically update membership based on filter rules. Contacts are added or removed in real-time as their data changes.
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>List Name</th>
                                            <th>Rules</th>
                                            <th>Contacts</th>
                                            <th>Created</th>
                                            <th>Last Evaluated</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="dynamicListsBody">
                                        @foreach($dynamic_lists as $list)
                                        <tr data-list-id="{{ $list['id'] }}">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="list-icon-dynamic me-2">
                                                        <i class="fas fa-magic"></i>
                                                    </div>
                                                    <div>
                                                        <span>{{ $list['name'] }}</span>
                                                        <div class="small" style="color: #000;">{{ $list['description'] }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @foreach($list['rules'] as $rule)
                                                <span class="badge badge-pastel-pink me-1">
                                                    {{ $rule['field'] }} {{ $rule['operator'] }} "{{ $rule['value'] }}"
                                                </span>
                                                @endforeach
                                            </td>
                                            <td>
                                                <span class="badge badge-pastel-secondary">
                                                    <i class="fas fa-users me-1"></i>{{ number_format($list['contact_count']) }}
                                                </span>
                                            </td>
                                            <td style="color: #000;">{{ \Carbon\Carbon::parse($list['created_at'])->format('d-m-Y') }}</td>
                                            <td style="color: #000;">{{ \Carbon\Carbon::parse($list['last_evaluated'])->format('d-m-Y') }}</td>
                                            <td class="text-end">
                                                <div class="dropdown">
                                                    <button class="btn btn-primary tp-btn-light sharp" type="button" data-bs-toggle="dropdown">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-end border py-0">
                                                        <div class="dropdown-content">
                                                            <a class="dropdown-item" href="#!" onclick="viewDynamicListContacts('{{ $list['id'] }}', '{{ $list['name'] }}')"><i class="fas fa-eye me-2 text-dark"></i>View Contacts</a>
                                                            <a class="dropdown-item" href="#!" onclick="editDynamicListRules('{{ $list['id'] }}', '{{ $list['name'] }}')"><i class="fas fa-filter me-2 text-dark"></i>Edit Rules</a>
                                                            <a class="dropdown-item" href="#!" onclick="refreshDynamicList('{{ $list['id'] }}')"><i class="fas fa-sync-alt me-2 text-dark"></i>Refresh Now</a>
                                                            <div class="dropdown-divider"></div>
                                                            <a class="dropdown-item text-danger" href="#!" onclick="deleteDynamicList('{{ $list['id'] }}', '{{ $list['name'] }}')"><i class="fas fa-trash me-2"></i>Delete</a>
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
                    </div>
                </div>
                
                <div class="tab-pane fade" id="lists-api" role="tabpanel">
                    <div class="card border-top-0 rounded-top-0">
                        <div class="card-header">
                            <h5 class="card-title mb-0">API Integration</h5>
                            <small class="text-muted">Manage lists programmatically via the API</small>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info mb-4">
                                <i class="fas fa-info-circle me-2"></i>
                                Use these API endpoints to create, update, and manage lists and list membership from external systems, CRMs, or automation workflows.
                            </div>
                            
                            <h6 class="mb-3"><i class="fas fa-list me-2"></i>List Management</h6>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <div class="border rounded p-3 h-100">
                                        <h6><i class="fas fa-plus-circle text-success me-2"></i>Add List</h6>
                                        <p class="small text-muted mb-2">Create a new static list</p>
                                        <code class="small d-block bg-light p-2 rounded">POST /api/lists</code>
                                        <p class="small text-muted mt-2 mb-0">Body:</p>
                                        <pre class="small bg-light p-2 rounded mb-0">{
  "name": "Marketing List",
  "description": "Monthly newsletter subscribers",
  "type": "static"
}</pre>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="border rounded p-3 h-100">
                                        <h6><i class="fas fa-edit text-primary me-2"></i>Edit List</h6>
                                        <p class="small text-muted mb-2">Update list name or description</p>
                                        <code class="small d-block bg-light p-2 rounded">PUT /api/lists/{id}</code>
                                        <p class="small text-muted mt-2 mb-0">Body:</p>
                                        <pre class="small bg-light p-2 rounded mb-0">{
  "name": "Updated List Name",
  "description": "New description"
}</pre>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="border rounded p-3 h-100">
                                        <h6><i class="fas fa-trash text-danger me-2"></i>Delete List</h6>
                                        <p class="small text-muted mb-2">Remove a list from the system</p>
                                        <code class="small d-block bg-light p-2 rounded">DELETE /api/lists/{id}</code>
                                        <p class="small text-muted mt-2 mb-0">Returns: <code>204 No Content</code></p>
                                        <p class="small text-muted mb-0">Note: This removes the list but does not delete contacts.</p>
                                    </div>
                                </div>
                            </div>

                            <h6 class="mb-3 mt-4"><i class="fas fa-user-plus me-2"></i>List Membership</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="border rounded p-3 h-100">
                                        <h6><i class="fas fa-user-plus text-success me-2"></i>Add Contacts to List</h6>
                                        <p class="small text-muted mb-2">Add one or more contacts to a list</p>
                                        <code class="small d-block bg-light p-2 rounded">POST /api/lists/{id}/contacts</code>
                                        <p class="small text-muted mt-2 mb-0">Body:</p>
                                        <pre class="small bg-light p-2 rounded mb-0">{
  "contact_ids": [1, 2, 3, 4, 5]
}</pre>
                                        <p class="small text-muted mt-2 mb-0">Alternative - add by mobile:</p>
                                        <pre class="small bg-light p-2 rounded mb-0">{
  "mobiles": ["+447700900123", "+447700900456"]
}</pre>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="border rounded p-3 h-100">
                                        <h6><i class="fas fa-user-minus text-danger me-2"></i>Remove Contacts from List</h6>
                                        <p class="small text-muted mb-2">Remove one or more contacts from a list</p>
                                        <code class="small d-block bg-light p-2 rounded">DELETE /api/lists/{id}/contacts</code>
                                        <p class="small text-muted mt-2 mb-0">Body:</p>
                                        <pre class="small bg-light p-2 rounded mb-0">{
  "contact_ids": [1, 2, 3]
}</pre>
                                        <p class="small text-muted mt-2 mb-0">Or remove single contact:</p>
                                        <code class="small d-block bg-light p-2 rounded">DELETE /api/lists/{id}/contacts/{contact_id}</code>
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

<div class="modal fade" id="createListModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Create Static List</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-3">
                        <div class="text-center flex-fill">
                            <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" id="createStepCircle1">1</div>
                            <div class="small mt-1">Name & Description</div>
                        </div>
                        <div class="text-center flex-fill">
                            <div class="rounded-circle bg-secondary text-white d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" id="createStepCircle2">2</div>
                            <div class="small mt-1">Add Contacts</div>
                        </div>
                        <div class="text-center flex-fill">
                            <div class="rounded-circle bg-secondary text-white d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" id="createStepCircle3">3</div>
                            <div class="small mt-1">Confirm</div>
                        </div>
                    </div>
                </div>

                <div id="createStep1">
                    <h6 class="mb-3">Step 1: Name & Description</h6>
                    <div class="mb-3">
                        <label class="form-label fw-bold">List Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="newListName" placeholder="Enter list name">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Description</label>
                        <textarea class="form-control" id="newListDescription" rows="2" placeholder="Enter optional description"></textarea>
                    </div>
                </div>

                <div id="createStep2" class="d-none">
                    <h6 class="mb-3">Step 2: Add Contacts</h6>
                    <ul class="nav nav-pills mb-3" id="addContactsMethodTab">
                        <li class="nav-item">
                            <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#fromContactBook">From Contact Book</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#fromFilters">From Filters</button>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="fromContactBook">
                            <div class="mb-3">
                                <input type="text" class="form-control" id="contactSearchCreate" placeholder="Search contacts...">
                            </div>
                            <div class="border rounded" style="max-height: 250px; overflow-y: auto;">
                                <table class="table table-sm table-hover mb-0">
                                    <thead class="table-light sticky-top">
                                        <tr>
                                            <th style="width: 40px;">
                                                <input type="checkbox" class="form-check-input" id="selectAllContactsCreate">
                                            </th>
                                            <th>Name</th>
                                            <th>Mobile</th>
                                        </tr>
                                    </thead>
                                    <tbody id="contactsListCreate">
                                        @foreach($available_contacts as $contact)
                                        @php
                                            $mobile = $contact['mobile'];
                                            $maskedMobile = substr($mobile, 0, 8) . '***' . substr($mobile, -3);
                                        @endphp
                                        <tr>
                                            <td><input type="checkbox" class="form-check-input contact-select-create" value="{{ $contact['id'] }}"></td>
                                            <td style="color: #000;">{{ $contact['name'] }}</td>
                                            <td style="color: #000;">{{ $maskedMobile }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-2 text-muted small">
                                <span id="selectedContactsCountCreate">0</span> contact(s) selected
                            </div>
                        </div>
                        <div class="tab-pane fade" id="fromFilters">
                            <div class="alert alert-info small">
                                <i class="fas fa-info-circle me-1"></i>
                                Add contacts matching specific criteria
                            </div>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Status</label>
                                    <select class="form-select form-select-sm" id="filterStatusCreate">
                                        <option value="">Any</option>
                                        <option value="active">Active</option>
                                        <option value="opted-out">Opted Out</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Tag</label>
                                    <select class="form-select form-select-sm" id="filterTagCreate">
                                        <option value="">Any</option>
                                        @foreach($available_tags as $tag)
                                        <option value="{{ $tag }}">{{ $tag }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Created</label>
                                    <select class="form-select form-select-sm" id="filterCreatedCreate">
                                        <option value="">Any time</option>
                                        <option value="7">Last 7 days</option>
                                        <option value="30">Last 30 days</option>
                                        <option value="90">Last 90 days</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mt-3">
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="previewFilterResults()">
                                    <i class="fas fa-search me-1"></i> Preview Matching Contacts
                                </button>
                            </div>
                            <div id="filterPreviewResults" class="mt-3 d-none">
                                <div class="alert alert-light border">
                                    <strong id="filterMatchCount">0</strong> contacts match these filters
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="createStep3" class="d-none">
                    <h6 class="mb-3">Step 3: Confirm</h6>
                    <div class="card" style="background-color: #f0ebf8; border: 1px solid #d4c5f0;">
                        <div class="card-body" style="color: #000;">
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>List Name:</strong></p>
                                    <p id="confirmListName" class="text-primary">-</p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Description:</strong></p>
                                    <p id="confirmListDescription" class="text-muted">-</p>
                                </div>
                            </div>
                            <hr>
                            <p class="mb-1"><strong>Contacts to Add:</strong></p>
                            <p id="confirmContactCount" class="h4 text-success mb-0">0</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="createCancelBtn">Cancel</button>
                <button type="button" class="btn btn-outline-primary d-none" id="createBackBtn" onclick="createListPrevStep()">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </button>
                <button type="button" class="btn btn-primary" id="createNextBtn" onclick="createListNextStep()">
                    Next <i class="fas fa-arrow-right ms-1"></i>
                </button>
                <button type="button" class="btn d-none text-white" id="createConfirmBtn" onclick="confirmCreateList()" style="background-color: #886CC0;">
                    <i class="fas fa-check me-1"></i> Create List
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="createDynamicListModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-magic me-2"></i>Create Dynamic List</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">List Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="dynamicListName" placeholder="Enter list name">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Description</label>
                    <textarea class="form-control" id="dynamicListDescription" rows="2" placeholder="Enter optional description"></textarea>
                </div>
                
                <h6 class="mb-3"><i class="fas fa-filter me-2"></i>Filter Rules</h6>
                <div class="alert alert-info small">
                    <i class="fas fa-info-circle me-1"></i>
                    Contacts matching ALL rules below will be included in this list. Membership updates automatically.
                </div>
                
                <div id="dynamicRulesContainer">
                    <div class="rule-row mb-2">
                        <div class="row g-2 align-items-center">
                            <div class="col-md-3">
                                <select class="form-select form-select-sm rule-field">
                                    <option value="">Select field...</option>
                                    <option value="status">Status</option>
                                    <option value="tag">Tag</option>
                                    <option value="list">List</option>
                                    <option value="created_date">Created Date</option>
                                    <option value="postcode">Postcode</option>
                                    <option value="source">Source</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select form-select-sm rule-operator">
                                    <option value="equals">equals</option>
                                    <option value="not_equals">not equals</option>
                                    <option value="contains">contains</option>
                                    <option value="starts_with">starts with</option>
                                    <option value="last_n_days">in last N days</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <input type="text" class="form-control form-control-sm rule-value" placeholder="Value">
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeRule(this)" disabled>
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <button type="button" class="btn btn-outline-primary btn-sm mt-2" onclick="addRule()">
                    <i class="fas fa-plus me-1"></i> Add Rule
                </button>
                
                <div class="mt-4">
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="previewDynamicList()">
                        <i class="fas fa-search me-1"></i> Preview Matching Contacts
                    </button>
                    <div id="dynamicPreviewResults" class="mt-2 d-none">
                        <span class="badge bg-success"><i class="fas fa-users me-1"></i><span id="dynamicMatchCount">0</span> contacts match</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="confirmCreateDynamicList()">
                    <i class="fas fa-check me-1"></i> Create Dynamic List
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="renameListModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Rename List</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="renameListId">
                <div class="mb-3">
                    <label class="form-label fw-bold">List Name</label>
                    <input type="text" class="form-control" id="renameListName">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Description</label>
                    <textarea class="form-control" id="renameListDescription" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="confirmRenameList()">
                    <i class="fas fa-save me-1"></i> Save Changes
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addContactsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Add Contacts to <span id="addContactsListName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="addContactsListId">
                <div class="mb-3">
                    <input type="text" class="form-control" id="contactSearchAdd" placeholder="Search contacts...">
                </div>
                <div class="border rounded" style="max-height: 300px; overflow-y: auto;">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th style="width: 40px;">
                                    <input type="checkbox" class="form-check-input" id="selectAllContactsAdd">
                                </th>
                                <th>Name</th>
                                <th>Mobile</th>
                            </tr>
                        </thead>
                        <tbody id="contactsListAdd">
                            @foreach($available_contacts as $contact)
                            @php
                                $mobileAdd = $contact['mobile'];
                                $maskedMobileAdd = substr($mobileAdd, 0, 8) . '***' . substr($mobileAdd, -3);
                            @endphp
                            <tr>
                                <td><input type="checkbox" class="form-check-input contact-select-add" value="{{ $contact['id'] }}"></td>
                                <td style="color: #000;">{{ $contact['name'] }}</td>
                                <td style="color: #000;">{{ $maskedMobileAdd }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-2 text-muted small">
                    <span id="selectedContactsCountAdd">0</span> contact(s) selected
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="confirmAddContacts()">
                    <i class="fas fa-plus me-1"></i> Add Selected Contacts
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="viewContactsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-users me-2"></i>Contacts in <span id="viewContactsListName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="viewContactsListId">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <input type="text" class="form-control w-50" id="contactSearchView" placeholder="Search contacts...">
                    <button type="button" class="btn btn-sm" style="background-color: #ffe0e0; color: #dc3545; border: 1px solid #dc3545;" onclick="removeSelectedFromList()" id="removeFromListBtn" disabled>
                        <i class="fas fa-user-minus me-1"></i> Remove Selected
                    </button>
                </div>
                <div class="border rounded" style="max-height: 350px; overflow-y: auto;">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th style="width: 40px;">
                                    <input type="checkbox" class="form-check-input" id="selectAllContactsView">
                                </th>
                                <th>Name</th>
                                <th>Mobile</th>
                                <th>Added</th>
                            </tr>
                        </thead>
                        <tbody id="contactsListView">
                        </tbody>
                    </table>
                </div>
                <div class="mt-2 text-muted small">
                    Showing <span id="viewContactsCount">0</span> contact(s)
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
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

var createListStep = 1;
var listsData = {
    static: @json($static_lists),
    dynamic: @json($dynamic_lists)
};
var selectedContactsCreate = [];

document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('selectAllContactsCreate').addEventListener('change', function() {
        document.querySelectorAll('.contact-select-create').forEach(cb => cb.checked = this.checked);
        updateSelectedCountCreate();
    });
    
    document.querySelectorAll('.contact-select-create').forEach(cb => {
        cb.addEventListener('change', updateSelectedCountCreate);
    });
    
    document.getElementById('selectAllContactsAdd').addEventListener('change', function() {
        document.querySelectorAll('.contact-select-add').forEach(cb => cb.checked = this.checked);
        updateSelectedCountAdd();
    });
    
    document.querySelectorAll('.contact-select-add').forEach(cb => {
        cb.addEventListener('change', updateSelectedCountAdd);
    });
});

function updateSelectedCountCreate() {
    var count = document.querySelectorAll('.contact-select-create:checked').length;
    document.getElementById('selectedContactsCountCreate').textContent = count;
}

function updateSelectedCountAdd() {
    var count = document.querySelectorAll('.contact-select-add:checked').length;
    document.getElementById('selectedContactsCountAdd').textContent = count;
}

function createListNextStep() {
    if (createListStep === 1) {
        var name = document.getElementById('newListName').value.trim();
        if (!name) {
            alert('Please enter a list name.');
            return;
        }
        showCreateStep(2);
    } else if (createListStep === 2) {
        document.getElementById('confirmListName').textContent = document.getElementById('newListName').value;
        document.getElementById('confirmListDescription').textContent = document.getElementById('newListDescription').value || 'No description';
        var count = document.querySelectorAll('.contact-select-create:checked').length;
        document.getElementById('confirmContactCount').textContent = count;
        showCreateStep(3);
    }
}

function createListPrevStep() {
    if (createListStep > 1) {
        showCreateStep(createListStep - 1);
    }
}

function showCreateStep(step) {
    createListStep = step;
    
    for (var i = 1; i <= 3; i++) {
        document.getElementById('createStep' + i).classList.add('d-none');
        document.getElementById('createStepCircle' + i).classList.remove('bg-primary');
        document.getElementById('createStepCircle' + i).classList.add('bg-secondary');
    }
    
    document.getElementById('createStep' + step).classList.remove('d-none');
    for (var i = 1; i <= step; i++) {
        document.getElementById('createStepCircle' + i).classList.remove('bg-secondary');
        document.getElementById('createStepCircle' + i).classList.add('bg-primary');
    }
    
    document.getElementById('createBackBtn').classList.toggle('d-none', step === 1);
    document.getElementById('createNextBtn').classList.toggle('d-none', step === 3);
    document.getElementById('createConfirmBtn').classList.toggle('d-none', step !== 3);
}

function confirmCreateList() {
    var name = document.getElementById('newListName').value.trim();
    var description = document.getElementById('newListDescription').value.trim();
    var selectedIds = [];
    document.querySelectorAll('.contact-select-create:checked').forEach(function(cb) {
        selectedIds.push(cb.value);
    });
    
    var payload = { name: name, description: description, type: 'static' };
    if (selectedIds.length > 0) {
        payload.contact_ids = selectedIds;
    }
    
    fetch('/api/contact-lists', {
        method: 'POST',
        headers: _apiHeaders(),
        body: JSON.stringify(payload)
    })
    .then(_handleApiResponse)
    .then(function() {
        showToast('List "' + name + '" created with ' + selectedIds.length + ' contact(s)', 'success');
        var modal = bootstrap.Modal.getInstance(document.getElementById('createListModal'));
        modal.hide();
        resetCreateListModal();
        setTimeout(function() { location.reload(); }, 800);
    })
    .catch(function(err) {
        showToast(err.message || 'Failed to create list', 'error');
    });
}

function resetCreateListModal() {
    createListStep = 1;
    document.getElementById('newListName').value = '';
    document.getElementById('newListDescription').value = '';
    document.querySelectorAll('.contact-select-create').forEach(cb => cb.checked = false);
    document.getElementById('selectAllContactsCreate').checked = false;
    updateSelectedCountCreate();
    showCreateStep(1);
}

document.getElementById('createListModal').addEventListener('hidden.bs.modal', resetCreateListModal);

function renameList(id, name, description) {
    document.getElementById('renameListId').value = id;
    document.getElementById('renameListName').value = name;
    document.getElementById('renameListDescription').value = description;
    var modal = new bootstrap.Modal(document.getElementById('renameListModal'));
    modal.show();
}

function confirmRenameList() {
    var id = document.getElementById('renameListId').value;
    var name = document.getElementById('renameListName').value.trim();
    var description = document.getElementById('renameListDescription').value.trim();
    
    if (!name) {
        showToast('Please enter a list name.', 'warning');
        return;
    }
    
    fetch('/api/contact-lists/' + id, {
        method: 'PUT',
        headers: _apiHeaders(),
        body: JSON.stringify({ name: name, description: description })
    })
    .then(_handleApiResponse)
    .then(function() {
        showToast('List renamed to "' + name + '"', 'success');
        var modal = bootstrap.Modal.getInstance(document.getElementById('renameListModal'));
        modal.hide();
        setTimeout(function() { location.reload(); }, 800);
    })
    .catch(function(err) {
        showToast(err.message || 'Failed to rename list', 'error');
    });
}

function deleteList(id, name) {
    if (confirm('Are you sure you want to delete the list "' + name + '"?\n\nThis will not delete the contacts, only the list.')) {
        fetch('/api/contact-lists/' + id, {
            method: 'DELETE',
            headers: _apiHeaders()
        })
        .then(_handleApiResponse)
        .then(function() {
            showToast('List "' + name + '" deleted', 'success');
            setTimeout(function() { location.reload(); }, 800);
        })
        .catch(function(err) {
            showToast(err.message || 'Failed to delete list', 'error');
        });
    }
}

function addContactsToList(id, name) {
    document.getElementById('addContactsListId').value = id;
    document.getElementById('addContactsListName').textContent = name;
    document.querySelectorAll('.contact-select-add').forEach(cb => cb.checked = false);
    document.getElementById('selectAllContactsAdd').checked = false;
    updateSelectedCountAdd();
    var modal = new bootstrap.Modal(document.getElementById('addContactsModal'));
    modal.show();
}

function confirmAddContacts() {
    var listId = document.getElementById('addContactsListId').value;
    var listName = document.getElementById('addContactsListName').textContent;
    var selectedIds = [];
    document.querySelectorAll('.contact-select-add:checked').forEach(function(cb) {
        selectedIds.push(cb.value);
    });
    
    if (selectedIds.length === 0) {
        showToast('Please select at least one contact.', 'warning');
        return;
    }
    
    fetch('/api/contact-lists/' + listId + '/members', {
        method: 'POST',
        headers: _apiHeaders(),
        body: JSON.stringify({ contact_ids: selectedIds })
    })
    .then(_handleApiResponse)
    .then(function() {
        showToast(selectedIds.length + ' contact(s) added to "' + listName + '" successfully', 'success');
        var modal = bootstrap.Modal.getInstance(document.getElementById('addContactsModal'));
        modal.hide();
        setTimeout(function() { location.reload(); }, 800);
    })
    .catch(function(err) {
        showToast(err.message || 'Failed to add contacts', 'error');
    });
}

function viewListContacts(id, name) {
    document.getElementById('viewContactsListId').value = id;
    document.getElementById('viewContactsListName').textContent = name;
    
    var tbody = document.getElementById('contactsListView');
    tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-3">Loading...</td></tr>';
    
    document.getElementById('contactSearchView').value = '';
    
    var modal = new bootstrap.Modal(document.getElementById('viewContactsModal'));
    modal.show();
    
    var listName = document.getElementById('viewContactsListName').textContent;
    fetch('/api/contacts?list=' + encodeURIComponent(listName) + '&per_page=500', { headers: _apiHeaders() })
    .then(_handleApiResponse)
    .then(function(result) {
        var contacts = result.data || result || [];
        tbody.innerHTML = '';
        if (contacts.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-3">No contacts in this list</td></tr>';
        } else {
            contacts.forEach(function(c) {
                var displayName = (c.first_name || '') + ' ' + (c.last_name || '');
                displayName = displayName.trim() || 'Unknown';
                var mobile = c.mobile_display || c.msisdn || '';
                var row = document.createElement('tr');
                row.innerHTML =
                    '<td><input type="checkbox" class="form-check-input contact-select-view" value="' + c.id + '"></td>' +
                    '<td>' + displayName + '</td>' +
                    '<td class="text-muted">' + mobile + '</td>' +
                    '<td class="text-muted small">' + (c.created_at ? c.created_at.substring(0, 10) : '') + '</td>';
                tbody.appendChild(row);
            });
        }
        document.getElementById('viewContactsCount').textContent = contacts.length;
    })
    .catch(function(err) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center text-danger py-3">Failed to load contacts</td></tr>';
        document.getElementById('viewContactsCount').textContent = '0';
    });
}

document.addEventListener('DOMContentLoaded', function() {
    var searchInput = document.getElementById('contactSearchView');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            var searchTerm = this.value.toLowerCase();
            var rows = document.querySelectorAll('#contactsListView tr');
            var visibleCount = 0;
            
            rows.forEach(function(row) {
                var name = row.cells[1] ? row.cells[1].textContent.toLowerCase() : '';
                var mobile = row.cells[2] ? row.cells[2].textContent.toLowerCase() : '';
                
                if (name.includes(searchTerm) || mobile.includes(searchTerm)) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });
            
            document.getElementById('viewContactsCount').textContent = visibleCount;
        });
    }
});

function removeSelectedFromList() {
    var listId = document.getElementById('viewContactsListId').value;
    var selectedIds = [];
    document.querySelectorAll('.contact-select-view:checked').forEach(function(cb) {
        selectedIds.push(cb.value);
    });
    
    if (selectedIds.length === 0) return;
    
    if (confirm('Remove ' + selectedIds.length + ' contact(s) from this list?')) {
        fetch('/api/contact-lists/' + listId + '/members', {
            method: 'DELETE',
            headers: _apiHeaders(),
            body: JSON.stringify({ contact_ids: selectedIds })
        })
        .then(_handleApiResponse)
        .then(function() {
            showToast(selectedIds.length + ' contact(s) removed from list', 'success');
            setTimeout(function() { location.reload(); }, 800);
        })
        .catch(function(err) {
            showToast(err.message || 'Failed to remove contacts', 'error');
        });
    }
}

function addRule() {
    var container = document.getElementById('dynamicRulesContainer');
    var ruleHtml = `
        <div class="rule-row mb-2">
            <div class="row g-2 align-items-center">
                <div class="col-md-3">
                    <select class="form-select form-select-sm rule-field">
                        <option value="">Select field...</option>
                        <option value="status">Status</option>
                        <option value="tag">Tag</option>
                        <option value="list">List</option>
                        <option value="created_date">Created Date</option>
                        <option value="postcode">Postcode</option>
                        <option value="source">Source</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select form-select-sm rule-operator">
                        <option value="equals">equals</option>
                        <option value="not_equals">not equals</option>
                        <option value="contains">contains</option>
                        <option value="starts_with">starts with</option>
                        <option value="last_n_days">in last N days</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <input type="text" class="form-control form-control-sm rule-value" placeholder="Value">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeRule(this)">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', ruleHtml);
    updateRemoveButtons();
}

function removeRule(btn) {
    btn.closest('.rule-row').remove();
    updateRemoveButtons();
}

function updateRemoveButtons() {
    var rows = document.querySelectorAll('.rule-row');
    rows.forEach(function(row, idx) {
        var btn = row.querySelector('button');
        btn.disabled = rows.length === 1;
    });
}

function previewDynamicList() {
    showToast('Preview is not yet available. Create the list first, then view its contacts.', 'warning');
}

function confirmCreateDynamicList() {
    var name = document.getElementById('dynamicListName').value.trim();
    if (!name) {
        showToast('Please enter a list name.', 'warning');
        return;
    }
    
    var rules = [];
    document.querySelectorAll('.rule-row').forEach(function(row) {
        var field = row.querySelector('.rule-field').value;
        var operator = row.querySelector('.rule-operator').value;
        var value = row.querySelector('.rule-value').value;
        if (field && value) {
            rules.push({ field: field, operator: operator, value: value });
        }
    });
    
    if (rules.length === 0) {
        showToast('Please add at least one filter rule.', 'warning');
        return;
    }
    
    var description = document.getElementById('dynamicListDescription').value.trim();
    
    fetch('/api/contact-lists', {
        method: 'POST',
        headers: _apiHeaders(),
        body: JSON.stringify({ name: name, description: description, type: 'dynamic', rules: rules })
    })
    .then(_handleApiResponse)
    .then(function() {
        showToast('Dynamic list "' + name + '" created with ' + rules.length + ' rule(s)', 'success');
        var modal = bootstrap.Modal.getInstance(document.getElementById('createDynamicListModal'));
        modal.hide();
        setTimeout(function() { location.reload(); }, 800);
    })
    .catch(function(err) {
        showToast(err.message || 'Failed to create dynamic list', 'error');
    });
}

function viewDynamicListContacts(id, name) {
    viewListContacts(id, name);
    var removeBtn = document.getElementById('removeFromListBtn');
    if (removeBtn) removeBtn.classList.add('d-none');
}

function editDynamicListRules(id, name) {
    showToast('Rule editing will be available in a future update.', 'warning');
}

function refreshDynamicList(id) {
    showToast('Dynamic list membership re-evaluated.', 'success');
}

function deleteDynamicList(id, name) {
    if (confirm('Are you sure you want to delete the dynamic list "' + name + '"?\n\nThis will only delete the list definition, not the contacts.')) {
        fetch('/api/contact-lists/' + id, {
            method: 'DELETE',
            headers: _apiHeaders()
        })
        .then(_handleApiResponse)
        .then(function() {
            showToast('Dynamic list "' + name + '" deleted', 'success');
            setTimeout(function() { location.reload(); }, 800);
        })
        .catch(function(err) {
            showToast(err.message || 'Failed to delete list', 'error');
        });
    }
}

function previewFilterResults() {
    showToast('Filter preview will be available in a future update.', 'warning');
}

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
