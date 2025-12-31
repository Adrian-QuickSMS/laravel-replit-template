@extends('layouts.quicksms')

@section('title', 'Lists')

@push('styles')
<style>
/* Fillow Pastel Color Scheme for Lists */
.list-icon-static {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: rgba(111, 66, 193, 0.15);
    color: #6f42c1;
}
.list-icon-dynamic {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: rgba(111, 66, 193, 0.15);
    color: #6f42c1;
}

/* Pastel badge styles */
.badge-pastel-primary {
    background-color: rgba(111, 66, 193, 0.15) !important;
    color: #6f42c1 !important;
}
.badge-pastel-pink {
    background-color: rgba(232, 62, 140, 0.15) !important;
    color: #e83e8c !important;
}
.badge-pastel-secondary {
    background-color: rgba(108, 117, 125, 0.15) !important;
    color: #6c757d !important;
}
.badge-pastel-success {
    background-color: rgba(28, 187, 140, 0.15) !important;
    color: #1cbb8c !important;
}
.badge-pastel-info {
    background-color: rgba(48, 101, 208, 0.15) !important;
    color: #3065D0 !important;
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
                                                    <strong style="color: #000;">{{ $list['name'] }}</strong>
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
                                                            <a class="dropdown-item" href="#!" onclick="viewListContacts({{ $list['id'] }}, '{{ $list['name'] }}')"><i class="fas fa-eye me-2 text-info"></i>View Contacts</a>
                                                            <a class="dropdown-item" href="#!" onclick="addContactsToList({{ $list['id'] }}, '{{ $list['name'] }}')"><i class="fas fa-user-plus me-2 text-success"></i>Add Contacts</a>
                                                            <a class="dropdown-item" href="#!" onclick="renameList({{ $list['id'] }}, '{{ $list['name'] }}', '{{ $list['description'] }}')"><i class="fas fa-edit me-2 text-primary"></i>Rename</a>
                                                            <div class="dropdown-divider"></div>
                                                            <a class="dropdown-item text-danger" href="#!" onclick="deleteList({{ $list['id'] }}, '{{ $list['name'] }}')"><i class="fas fa-trash me-2"></i>Delete</a>
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
                            <div class="alert alert-info small mb-3">
                                <i class="fas fa-info-circle me-2"></i>
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
                                                        <strong style="color: #000;">{{ $list['name'] }}</strong>
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
                                                            <a class="dropdown-item" href="#!" onclick="viewDynamicListContacts({{ $list['id'] }}, '{{ $list['name'] }}')"><i class="fas fa-eye me-2 text-info"></i>View Contacts</a>
                                                            <a class="dropdown-item" href="#!" onclick="editDynamicListRules({{ $list['id'] }}, '{{ $list['name'] }}')"><i class="fas fa-filter me-2 text-primary"></i>Edit Rules</a>
                                                            <a class="dropdown-item" href="#!" onclick="refreshDynamicList({{ $list['id'] }})"><i class="fas fa-sync-alt me-2 text-success"></i>Refresh Now</a>
                                                            <div class="dropdown-divider"></div>
                                                            <a class="dropdown-item text-danger" href="#!" onclick="deleteDynamicList({{ $list['id'] }}, '{{ $list['name'] }}')"><i class="fas fa-trash me-2"></i>Delete</a>
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
                    <div class="card bg-light">
                        <div class="card-body">
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
                <button type="button" class="btn btn-success d-none" id="createConfirmBtn" onclick="confirmCreateList()">
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
                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeSelectedFromList()" id="removeFromListBtn" disabled>
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
    var contactCount = document.querySelectorAll('.contact-select-create:checked').length;
    
    console.log('TODO: API call POST /api/lists to create list');
    console.log('TODO: API call POST /api/lists/{id}/contacts to add contacts');
    
    alert('List "' + name + '" created with ' + contactCount + ' contact(s)!\n\nThis requires backend implementation.');
    
    var modal = bootstrap.Modal.getInstance(document.getElementById('createListModal'));
    modal.hide();
    
    resetCreateListModal();
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
        alert('Please enter a list name.');
        return;
    }
    
    console.log('TODO: API call PUT /api/lists/' + id);
    alert('List renamed to "' + name + '"!\n\nThis requires backend implementation.');
    
    var modal = bootstrap.Modal.getInstance(document.getElementById('renameListModal'));
    modal.hide();
}

function deleteList(id, name) {
    if (confirm('Are you sure you want to delete the list "' + name + '"?\n\nThis will not delete the contacts, only the list.')) {
        console.log('TODO: API call DELETE /api/lists/' + id);
        alert('List "' + name + '" deleted!\n\nThis requires backend implementation.');
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
    document.querySelectorAll('.contact-select-add:checked').forEach(cb => {
        selectedIds.push(cb.value);
    });
    
    if (selectedIds.length === 0) {
        alert('Please select at least one contact.');
        return;
    }
    
    console.log('TODO: API call POST /api/lists/' + listId + '/contacts with IDs: ' + selectedIds.join(', '));
    alert('Added ' + selectedIds.length + ' contact(s) to "' + listName + '"!\n\nThis requires backend implementation.');
    
    var modal = bootstrap.Modal.getInstance(document.getElementById('addContactsModal'));
    modal.hide();
}

function viewListContacts(id, name) {
    document.getElementById('viewContactsListId').value = id;
    document.getElementById('viewContactsListName').textContent = name;
    
    var mockContacts = [
        { id: 1, name: 'Emma Thompson', mobile: '+44 7700 900123', added: '2024-12-15' },
        { id: 2, name: 'James Wilson', mobile: '+44 7700 900456', added: '2024-12-10' },
        { id: 3, name: 'Sarah Mitchell', mobile: '+44 7700 900789', added: '2024-11-28' },
    ];
    
    var tbody = document.getElementById('contactsListView');
    tbody.innerHTML = '';
    mockContacts.forEach(function(c) {
        var row = document.createElement('tr');
        row.innerHTML = `
            <td><input type="checkbox" class="form-check-input contact-select-view" value="${c.id}"></td>
            <td>${c.name}</td>
            <td class="text-muted">${c.mobile}</td>
            <td class="text-muted small">${c.added}</td>
        `;
        tbody.appendChild(row);
    });
    
    document.getElementById('viewContactsCount').textContent = mockContacts.length;
    
    console.log('TODO: API call GET /api/lists/' + id + '/contacts');
    
    var modal = new bootstrap.Modal(document.getElementById('viewContactsModal'));
    modal.show();
}

function removeSelectedFromList() {
    var listId = document.getElementById('viewContactsListId').value;
    var selectedIds = [];
    document.querySelectorAll('.contact-select-view:checked').forEach(cb => {
        selectedIds.push(cb.value);
    });
    
    if (selectedIds.length === 0) return;
    
    if (confirm('Remove ' + selectedIds.length + ' contact(s) from this list?')) {
        console.log('TODO: API call DELETE /api/lists/' + listId + '/contacts with IDs: ' + selectedIds.join(', '));
        alert('Removed ' + selectedIds.length + ' contact(s) from list!\n\nThis requires backend implementation.');
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
    var count = Math.floor(Math.random() * 200) + 50;
    document.getElementById('dynamicMatchCount').textContent = count;
    document.getElementById('dynamicPreviewResults').classList.remove('d-none');
    console.log('TODO: API call POST /api/lists/preview with rules');
}

function confirmCreateDynamicList() {
    var name = document.getElementById('dynamicListName').value.trim();
    if (!name) {
        alert('Please enter a list name.');
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
        alert('Please add at least one filter rule.');
        return;
    }
    
    console.log('TODO: API call POST /api/lists/dynamic with name and rules');
    alert('Dynamic list "' + name + '" created with ' + rules.length + ' rule(s)!\n\nThis requires backend implementation.');
    
    var modal = bootstrap.Modal.getInstance(document.getElementById('createDynamicListModal'));
    modal.hide();
}

function viewDynamicListContacts(id, name) {
    viewListContacts(id, name);
    document.getElementById('removeFromListBtn').classList.add('d-none');
}

function editDynamicListRules(id, name) {
    console.log('TODO: Load existing rules for list ' + id);
    alert('Edit Rules for "' + name + '"\n\nThis requires loading existing rules and opening the editor.\n\nBackend implementation needed.');
}

function refreshDynamicList(id) {
    console.log('TODO: API call POST /api/lists/dynamic/' + id + '/refresh');
    alert('Dynamic list refreshed!\n\nMembership has been re-evaluated based on current rules.\n\nBackend implementation needed.');
}

function deleteDynamicList(id, name) {
    if (confirm('Are you sure you want to delete the dynamic list "' + name + '"?\n\nThis will only delete the list definition, not the contacts.')) {
        console.log('TODO: API call DELETE /api/lists/dynamic/' + id);
        alert('Dynamic list "' + name + '" deleted!\n\nThis requires backend implementation.');
    }
}

function previewFilterResults() {
    var count = Math.floor(Math.random() * 100) + 20;
    document.getElementById('filterMatchCount').textContent = count;
    document.getElementById('filterPreviewResults').classList.remove('d-none');
    console.log('TODO: API call to preview filter results');
}
</script>
@endsection
