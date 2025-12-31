@extends('layouts.quicksms')

@section('title', 'All Contacts')

@push('styles')
<style>
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
.btn-xs {
    padding: 0.2rem 0.5rem;
    font-size: 0.7rem;
    line-height: 1.4;
}

/* Fillow Pastel Color Scheme for Contacts */
.contact-avatar {
    width: 36px;
    height: 36px;
    font-size: 14px;
    font-weight: 600;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: rgba(111, 66, 193, 0.15);
    color: #6f42c1;
}

/* Pastel badge styles matching Fillow template */
.badge-pastel-primary {
    background-color: rgba(111, 66, 193, 0.15) !important;
    color: #6f42c1 !important;
}
.badge-pastel-success {
    background-color: rgba(28, 187, 140, 0.15) !important;
    color: #1cbb8c !important;
}
.badge-pastel-danger {
    background-color: rgba(220, 53, 69, 0.15) !important;
    color: #dc3545 !important;
}
.badge-pastel-info {
    background-color: rgba(48, 101, 208, 0.15) !important;
    color: #3065D0 !important;
}
.badge-pastel-warning {
    background-color: rgba(255, 191, 0, 0.15) !important;
    color: #cc9900 !important;
}
.badge-pastel-secondary {
    background-color: rgba(108, 117, 125, 0.15) !important;
    color: #6c757d !important;
}
.badge-pastel-pink {
    background-color: rgba(232, 62, 140, 0.15) !important;
    color: #e83e8c !important;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row page-titles">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('contacts') }}">Contact Book</a></li>
            <li class="breadcrumb-item active">All Contacts</li>
        </ol>
    </div>
    
    <div class="row">
        <div class="col-12">
            <ul class="nav nav-tabs" id="contactsTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="manage-contacts-tab" data-bs-toggle="tab" data-bs-target="#manage-contacts" type="button" role="tab">
                        <i class="fas fa-users me-2"></i>Manage Contacts <span class="badge bg-primary ms-1">{{ count($contacts) }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="contacts-api-tab" data-bs-toggle="tab" data-bs-target="#contacts-api" type="button" role="tab">
                        <i class="fas fa-code me-2"></i>API Integration
                    </button>
                </li>
            </ul>
            
            <div class="tab-content" id="contactsTabContent">
                <div class="tab-pane fade show active" id="manage-contacts" role="tabpanel">
                    <div class="card border-top-0 rounded-top-0">
                        <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                            <h5 class="card-title mb-2 mb-md-0">All Contacts</h5>
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#filterPanel">
                                    <i class="fas fa-filter me-1"></i> Filters
                                </button>
                                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#importContactsModal">
                                    <i class="fas fa-file-import me-1"></i> Import
                                </button>
                                <button type="button" class="btn btn-primary btn-sm" id="btnAddContact" data-bs-toggle="modal" data-bs-target="#addContactModal">
                                    <i class="fas fa-plus me-1"></i> Add Contact
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                    <div class="mb-3">
                        <div class="input-group">
                            <span class="input-group-text bg-transparent"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control" id="contactSearch" placeholder="Search across names, numbers, tags, lists, and custom fields">
                        </div>
                    </div>

                    <div class="collapse mb-3" id="filterPanel">
                        <div class="card card-body border-0 rounded-3" style="background-color: #f0ebf8;">
                            <div class="row g-3 align-items-end">
                                <div class="col-6 col-md-4 col-lg-2">
                                    <label class="form-label small fw-bold">Status</label>
                                    <div class="dropdown multiselect-dropdown" data-filter="statuses">
                                        <button class="btn btn-sm dropdown-toggle w-100 text-start d-flex justify-content-between align-items-center" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" style="background-color: #fff; border: 1px solid #ced4da; color: #495057;">
                                            <span class="dropdown-label">All Statuses</span>
                                        </button>
                                        <div class="dropdown-menu w-100 p-2">
                                            <div class="d-flex justify-content-between mb-2 border-bottom pb-2">
                                                <a href="#" class="small text-decoration-none select-all-btn">Select All</a>
                                                <a href="#" class="small text-decoration-none clear-all-btn">Clear</a>
                                            </div>
                                            <div class="form-check"><input class="form-check-input" type="checkbox" value="active" id="statusActive"><label class="form-check-label small" for="statusActive">Active</label></div>
                                            <div class="form-check"><input class="form-check-input" type="checkbox" value="opted-out" id="statusOptedOut"><label class="form-check-label small" for="statusOptedOut">Opted Out</label></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-4 col-lg-2">
                                    <label class="form-label small fw-bold">Tags</label>
                                    <div class="dropdown multiselect-dropdown" data-filter="tags">
                                        <button class="btn btn-sm dropdown-toggle w-100 text-start d-flex justify-content-between align-items-center" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" style="background-color: #fff; border: 1px solid #ced4da; color: #495057;">
                                            <span class="dropdown-label">All Tags</span>
                                        </button>
                                        <div class="dropdown-menu w-100 p-2" style="max-height: 250px; overflow-y: auto;">
                                            <div class="d-flex justify-content-between mb-2 border-bottom pb-2">
                                                <a href="#" class="small text-decoration-none select-all-btn">Select All</a>
                                                <a href="#" class="small text-decoration-none clear-all-btn">Clear</a>
                                            </div>
                                            @foreach($available_tags as $index => $tag)
                                            <div class="form-check"><input class="form-check-input" type="checkbox" value="{{ $tag }}" id="tag{{ $index }}"><label class="form-check-label small" for="tag{{ $index }}">{{ $tag }}</label></div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-4 col-lg-2">
                                    <label class="form-label small fw-bold">Lists</label>
                                    <div class="dropdown multiselect-dropdown" data-filter="lists">
                                        <button class="btn btn-sm dropdown-toggle w-100 text-start d-flex justify-content-between align-items-center" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" style="background-color: #fff; border: 1px solid #ced4da; color: #495057;">
                                            <span class="dropdown-label">All Lists</span>
                                        </button>
                                        <div class="dropdown-menu w-100 p-2" style="max-height: 250px; overflow-y: auto;">
                                            <div class="d-flex justify-content-between mb-2 border-bottom pb-2">
                                                <a href="#" class="small text-decoration-none select-all-btn">Select All</a>
                                                <a href="#" class="small text-decoration-none clear-all-btn">Clear</a>
                                            </div>
                                            @foreach($available_lists as $index => $list)
                                            <div class="form-check"><input class="form-check-input" type="checkbox" value="{{ $list }}" id="list{{ $index }}"><label class="form-check-label small" for="list{{ $index }}">{{ $list }}</label></div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-4 col-lg-2">
                                    <label class="form-label small fw-bold">Source</label>
                                    <div class="dropdown multiselect-dropdown" data-filter="sources">
                                        <button class="btn btn-sm dropdown-toggle w-100 text-start d-flex justify-content-between align-items-center" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" style="background-color: #fff; border: 1px solid #ced4da; color: #495057;">
                                            <span class="dropdown-label">All Sources</span>
                                        </button>
                                        <div class="dropdown-menu w-100 p-2">
                                            <div class="d-flex justify-content-between mb-2 border-bottom pb-2">
                                                <a href="#" class="small text-decoration-none select-all-btn">Select All</a>
                                                <a href="#" class="small text-decoration-none clear-all-btn">Clear</a>
                                            </div>
                                            <div class="form-check"><input class="form-check-input" type="checkbox" value="UI" id="sourceUI"><label class="form-check-label small" for="sourceUI">UI</label></div>
                                            <div class="form-check"><input class="form-check-input" type="checkbox" value="Import" id="sourceImport"><label class="form-check-label small" for="sourceImport">Import</label></div>
                                            <div class="form-check"><input class="form-check-input" type="checkbox" value="API" id="sourceAPI"><label class="form-check-label small" for="sourceAPI">API</label></div>
                                            <div class="form-check"><input class="form-check-input" type="checkbox" value="Email-to-SMS" id="sourceEmail"><label class="form-check-label small" for="sourceEmail">Email-to-SMS</label></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-4 col-lg-2">
                                    <label class="form-label small fw-bold">Created Date</label>
                                    <input type="date" class="form-control form-control-sm" id="filterCreatedDate">
                                </div>
                            </div>
                            <!-- Button Row -->
                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="d-flex gap-2 justify-content-end">
                                        <button type="button" class="btn btn-primary btn-sm" id="btnApplyFilters" style="white-space: nowrap;">
                                            <i class="fas fa-check me-1"></i> Apply Filters
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" id="btnResetFilters" style="white-space: nowrap;">
                                            <i class="fas fa-undo me-1"></i> Reset Filters
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3" id="activeFiltersContainer" style="display: none;">
                        <div class="d-flex flex-wrap align-items-center">
                            <span class="small text-muted me-2">Active filters:</span>
                            <div id="activeFiltersChips"></div>
                            <button type="button" class="btn btn-link btn-sm text-decoration-none p-0 ms-2" id="btnClearAllFilters">
                                Clear all
                            </button>
                        </div>
                    </div>

                    <div id="bulkActionBar" class="alert alert-light border d-none mb-3">
                        <div class="d-flex justify-content-between align-items-center flex-wrap">
                            <span><strong id="selectedCount">0</strong> contact(s) selected</span>
                            <div class="d-flex gap-2 flex-wrap mt-2 mt-md-0">
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="bulkAddToList()">
                                    <i class="fas fa-plus me-1"></i> Add to List
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="bulkRemoveFromList()">
                                    <i class="fas fa-minus me-1"></i> Remove from List
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="bulkAddTags()">
                                    <i class="fas fa-tag me-1"></i> Add Tags
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="bulkRemoveTags()">
                                    <i class="fas fa-times me-1"></i> Remove Tags
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="bulkSendMessage()">
                                    <i class="fas fa-paper-plane me-1"></i> Send Message
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="openExportModal()">
                                    <i class="fas fa-download me-1"></i> Export
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="bulkDelete()">
                                    <i class="fas fa-trash me-1"></i> Delete
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="contactsTable">
                            <thead>
                                <tr>
                                    <th class="pe-3" style="width: 40px;">
                                        <div class="form-check custom-checkbox">
                                            <input type="checkbox" class="form-check-input" id="checkAll">
                                            <label class="form-check-label" for="checkAll"></label>
                                        </div>
                                    </th>
                                    <th>
                                        <div class="dropdown d-inline-block">
                                            <span class="dropdown-toggle" style="cursor: pointer;" data-bs-toggle="dropdown">
                                                Contact <i class="fas fa-sort ms-1 text-muted"></i>
                                            </span>
                                            <ul class="dropdown-menu">
                                                <li class="dropdown-header small text-muted">First Name</li>
                                                <li><a class="dropdown-item" href="#!" onclick="sortContacts('firstName', 'asc'); return false;"><i class="fas fa-sort-alpha-down me-2"></i> A-Z</a></li>
                                                <li><a class="dropdown-item" href="#!" onclick="sortContacts('firstName', 'desc'); return false;"><i class="fas fa-sort-alpha-up me-2"></i> Z-A</a></li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li class="dropdown-header small text-muted">Last Name</li>
                                                <li><a class="dropdown-item" href="#!" onclick="sortContacts('lastName', 'asc'); return false;"><i class="fas fa-sort-alpha-down me-2"></i> A-Z</a></li>
                                                <li><a class="dropdown-item" href="#!" onclick="sortContacts('lastName', 'desc'); return false;"><i class="fas fa-sort-alpha-up me-2"></i> Z-A</a></li>
                                            </ul>
                                        </div>
                                    </th>
                                    <th>Mobile Number</th>
                                    <th>Tags</th>
                                    <th>Lists</th>
                                    <th>
                                        <div class="dropdown d-inline-block">
                                            <span class="dropdown-toggle" style="cursor: pointer;" data-bs-toggle="dropdown">
                                                Status <i class="fas fa-sort ms-1 text-muted"></i>
                                            </span>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="#!" onclick="sortContacts('status', 'asc'); return false;"><i class="fas fa-check-circle me-2 text-success"></i> Active First</a></li>
                                                <li><a class="dropdown-item" href="#!" onclick="sortContacts('status', 'desc'); return false;"><i class="fas fa-ban me-2 text-danger"></i> Opted Out First</a></li>
                                            </ul>
                                        </div>
                                    </th>
                                    <th style="width: 50px;"></th>
                                </tr>
                            </thead>
                            <tbody id="contactsTableBody">
                                @foreach($contacts as $index => $contact)
                                <tr class="btn-reveal-trigger" data-contact-id="{{ $contact['id'] }}">
                                    <td class="py-2">
                                        <div class="form-check custom-checkbox">
                                            <input type="checkbox" class="form-check-input contact-checkbox" id="checkbox{{ $contact['id'] }}">
                                            <label class="form-check-label" for="checkbox{{ $contact['id'] }}"></label>
                                        </div>
                                    </td>
                                    <td class="py-2">
                                        <div class="d-flex align-items-center">
                                            <div class="contact-avatar me-2">
                                                {{ $contact['initials'] }}
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fs-6">{{ $contact['first_name'] }} {{ $contact['last_name'] }}</h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-2">
                                        <span class="mobile-number" data-full="{{ $contact['mobile'] }}" data-masked="{{ $contact['mobile_masked'] }}">
                                            {{ $contact['mobile_masked'] }}
                                        </span>
                                    </td>
                                    <td class="py-2">
                                        @foreach($contact['tags'] as $tag)
                                        <span class="badge badge-pastel-secondary me-1">{{ $tag }}</span>
                                        @endforeach
                                    </td>
                                    <td class="py-2">
                                        @foreach($contact['lists'] as $list)
                                        <span class="badge badge-pastel-pink me-1">{{ $list }}</span>
                                        @endforeach
                                    </td>
                                    <td class="py-2">
                                        @if($contact['status'] === 'active')
                                        <span class="badge badge-pastel-success">Active</span>
                                        @else
                                        <span class="badge badge-pastel-danger">Opted Out</span>
                                        @endif
                                    </td>
                                    <td class="py-2 text-end">
                                        <div class="dropdown">
                                            <button class="btn btn-primary tp-btn-light sharp" type="button" data-bs-toggle="dropdown">
                                                <span class="fs--1">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="18px" height="18px" viewBox="0 0 24 24">
                                                        <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                            <rect x="0" y="0" width="24" height="24"></rect>
                                                            <circle fill="#000000" cx="5" cy="12" r="2"></circle>
                                                            <circle fill="#000000" cx="12" cy="12" r="2"></circle>
                                                            <circle fill="#000000" cx="19" cy="12" r="2"></circle>
                                                        </g>
                                                    </svg>
                                                </span>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end border py-0">
                                                <div class="py-2">
                                                    <a class="dropdown-item" href="#!" onclick="viewContact({{ $contact['id'] }}); return false;">
                                                        <i class="fas fa-eye me-2 text-primary"></i> View Details
                                                    </a>
                                                    <a class="dropdown-item" href="#!" onclick="editContact({{ $contact['id'] }}); return false;">
                                                        <i class="fas fa-edit me-2 text-info"></i> Edit
                                                    </a>
                                                    <a class="dropdown-item" href="#!" onclick="sendMessage({{ $contact['id'] }}); return false;">
                                                        <i class="fas fa-paper-plane me-2 text-success"></i> Send Message
                                                    </a>
                                                    <a class="dropdown-item" href="#!" onclick="viewTimeline({{ $contact['id'] }}); return false;">
                                                        <i class="fas fa-history me-2 text-secondary"></i> Activity Timeline
                                                    </a>
                                                    <div class="dropdown-divider"></div>
                                                    <a class="dropdown-item text-danger" href="#!" onclick="deleteContact({{ $contact['id'] }}); return false;">
                                                        <i class="fas fa-trash me-2"></i> Delete
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

                    <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap">
                        <div class="text-muted small mb-2 mb-md-0">
                            Showing {{ count($contacts) }} of {{ $total_contacts }} contacts
                        </div>
                        <nav>
                            <ul class="pagination pagination-sm mb-0">
                                <li class="page-item disabled">
                                    <a class="page-link" href="#" tabindex="-1"><i class="fas fa-chevron-left"></i></a>
                                </li>
                                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                <li class="page-item"><a class="page-link" href="#">2</a></li>
                                <li class="page-item"><a class="page-link" href="#">3</a></li>
                                <li class="page-item">
                                    <a class="page-link" href="#"><i class="fas fa-chevron-right"></i></a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                        </div>
                    </div>
                </div>
                
                <div class="tab-pane fade" id="contacts-api" role="tabpanel">
                    <div class="card border-top-0 rounded-top-0">
                        <div class="card-header">
                            <h5 class="card-title mb-0">API Integration</h5>
                            <small class="text-muted">Manage contacts programmatically via the API</small>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info mb-4">
                                <i class="fas fa-info-circle me-2"></i>
                                Use these API endpoints to create, update, and manage contacts from external systems, CRMs, or automation workflows.
                            </div>
                            
                            <h6 class="mb-3"><i class="fas fa-user me-2"></i>Contact Management</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="border rounded p-3 h-100">
                                        <h6><i class="fas fa-user-plus text-success me-2"></i>Create a Contact</h6>
                                        <p class="small text-muted mb-2">Add a new contact to the system</p>
                                        <code class="small d-block bg-light p-2 rounded">POST /api/contacts</code>
                                        <p class="small text-muted mt-2 mb-0">Body:</p>
                                        <pre class="small bg-light p-2 rounded mb-0">{
  "name": "John Smith",
  "mobile": "+447700900123",
  "email": "john@example.com",
  "tags": ["customer"],
  "lists": ["marketing"]
}</pre>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="border rounded p-3 h-100">
                                        <h6><i class="fas fa-user-edit text-primary me-2"></i>Update a Contact</h6>
                                        <p class="small text-muted mb-2">Modify an existing contact's details</p>
                                        <code class="small d-block bg-light p-2 rounded">PUT /api/contacts/{id}</code>
                                        <p class="small text-muted mt-2 mb-0">Body:</p>
                                        <pre class="small bg-light p-2 rounded mb-0">{
  "name": "John Smith Jr",
  "email": "john.jr@example.com",
  "status": "active"
}</pre>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="border rounded p-3 h-100">
                                        <h6><i class="fas fa-user-minus text-danger me-2"></i>Remove Contact</h6>
                                        <p class="small text-muted mb-2">Delete a contact from the system</p>
                                        <code class="small d-block bg-light p-2 rounded">DELETE /api/contacts/{id}</code>
                                        <p class="small text-muted mt-2 mb-0">Returns: <code>204 No Content</code></p>
                                        <p class="small text-muted mb-0">Note: This action is permanent and cannot be undone.</p>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="border rounded p-3 h-100">
                                        <h6><i class="fas fa-search text-info me-2"></i>Get Contact Details</h6>
                                        <p class="small text-muted mb-2">Retrieve a single contact by ID</p>
                                        <code class="small d-block bg-light p-2 rounded">GET /api/contacts/{id}</code>
                                        <p class="small text-muted mt-2 mb-0">Returns: Contact object with all fields</p>
                                    </div>
                                </div>
                            </div>

                            <h6 class="mb-3 mt-4"><i class="fas fa-sliders-h me-2"></i>Custom Field Management</h6>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <div class="border rounded p-3 h-100">
                                        <h6><i class="fas fa-plus-square text-success me-2"></i>Add Custom Field</h6>
                                        <p class="small text-muted mb-2">Define a new custom field</p>
                                        <code class="small d-block bg-light p-2 rounded">POST /api/custom-fields</code>
                                        <p class="small text-muted mt-2 mb-0">Body:</p>
                                        <pre class="small bg-light p-2 rounded mb-0">{
  "name": "Company",
  "type": "text",
  "required": false
}</pre>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="border rounded p-3 h-100">
                                        <h6><i class="fas fa-edit text-primary me-2"></i>Edit Custom Field</h6>
                                        <p class="small text-muted mb-2">Update custom field properties</p>
                                        <code class="small d-block bg-light p-2 rounded">PUT /api/custom-fields/{id}</code>
                                        <p class="small text-muted mt-2 mb-0">Body:</p>
                                        <pre class="small bg-light p-2 rounded mb-0">{
  "name": "Company Name",
  "required": true
}</pre>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="border rounded p-3 h-100">
                                        <h6><i class="fas fa-minus-square text-danger me-2"></i>Remove Custom Field</h6>
                                        <p class="small text-muted mb-2">Delete a custom field definition</p>
                                        <code class="small d-block bg-light p-2 rounded">DELETE /api/custom-fields/{id}</code>
                                        <p class="small text-muted mt-2 mb-0">Returns: <code>204 No Content</code></p>
                                        <p class="small text-muted mb-0">Warning: Removes field from all contacts.</p>
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

<script>
var contactsData = @json($contacts);
var customFieldDefinitions = [
    { id: 1, name: 'Company', slug: 'company', type: 'text', defaultValue: '' },
    { id: 2, name: 'Job Title', slug: 'job_title', type: 'text', defaultValue: '' }
];

document.addEventListener('DOMContentLoaded', function() {
    const checkAll = document.getElementById('checkAll');
    const bulkActionBar = document.getElementById('bulkActionBar');
    const selectedCount = document.getElementById('selectedCount');
    const searchInput = document.getElementById('contactSearch');

    checkAll.addEventListener('change', function() {
        document.querySelectorAll('.contact-checkbox').forEach(cb => cb.checked = this.checked);
        updateBulkActionBar();
    });

    document.getElementById('contactsTableBody').addEventListener('change', function(e) {
        if (e.target.classList.contains('contact-checkbox')) {
            updateBulkActionBar();
        }
    });

    function updateBulkActionBar() {
        const allCheckboxes = document.querySelectorAll('.contact-checkbox');
        const checkedCount = document.querySelectorAll('.contact-checkbox:checked').length;
        selectedCount.textContent = checkedCount;
        
        if (checkedCount > 0) {
            bulkActionBar.classList.remove('d-none');
        } else {
            bulkActionBar.classList.add('d-none');
        }

        const allChecked = checkedCount === allCheckboxes.length && allCheckboxes.length > 0;
        checkAll.checked = allChecked;
        checkAll.indeterminate = checkedCount > 0 && !allChecked;
    }

    searchInput.addEventListener('input', applyFilters);

    // Filter elements
    const filterStatus = document.getElementById('filterStatus');
    const filterTags = document.getElementById('filterTags');
    const filterLists = document.getElementById('filterLists');
    const filterSource = document.getElementById('filterSource');
    
    filterStatus.addEventListener('change', applyFilters);
    filterTags.addEventListener('change', applyFilters);
    filterLists.addEventListener('change', applyFilters);
    filterSource.addEventListener('change', applyFilters);
    
    function applyFilters() {
        const searchTerm = searchInput.value.toLowerCase();
        const statusFilter = filterStatus.value;
        const tagsFilter = filterTags.value;
        const listsFilter = filterLists.value;
        const sourceFilter = filterSource.value;
        
        var filteredContacts = contactsData.filter(contact => {
            // Search filter
            const searchMatch = searchTerm === '' || 
                (contact.first_name + ' ' + contact.last_name).toLowerCase().includes(searchTerm) ||
                contact.mobile.includes(searchTerm) ||
                contact.tags.some(t => t.toLowerCase().includes(searchTerm)) ||
                contact.lists.some(l => l.toLowerCase().includes(searchTerm));
            
            // Status filter
            const statusMatch = statusFilter === '' || contact.status === statusFilter;
            
            // Tags filter
            const tagsMatch = tagsFilter === '' || contact.tags.includes(tagsFilter);
            
            // Lists filter
            const listsMatch = listsFilter === '' || contact.lists.includes(listsFilter);
            
            // Source filter
            const sourceMatch = sourceFilter === '' || contact.source === sourceFilter;
            
            return searchMatch && statusMatch && tagsMatch && listsMatch && sourceMatch;
        });
        
        renderContactsTable(filteredContacts);
        updateActiveFilters();
    }
    
    function updateActiveFilters() {
        const activeFiltersDiv = document.getElementById('activeFilters');
        let badges = [];
        
        if (filterStatus.value) {
            badges.push(`<span class="badge bg-primary me-1">Status: ${filterStatus.options[filterStatus.selectedIndex].text} <i class="fas fa-times ms-1" style="cursor:pointer" onclick="clearFilter('filterStatus')"></i></span>`);
        }
        if (filterTags.value) {
            badges.push(`<span class="badge bg-primary me-1">Tag: ${filterTags.value} <i class="fas fa-times ms-1" style="cursor:pointer" onclick="clearFilter('filterTags')"></i></span>`);
        }
        if (filterLists.value) {
            badges.push(`<span class="badge bg-primary me-1">List: ${filterLists.value} <i class="fas fa-times ms-1" style="cursor:pointer" onclick="clearFilter('filterLists')"></i></span>`);
        }
        if (filterSource.value) {
            badges.push(`<span class="badge bg-primary me-1">Source: ${filterSource.value} <i class="fas fa-times ms-1" style="cursor:pointer" onclick="clearFilter('filterSource')"></i></span>`);
        }
        
        if (badges.length > 0) {
            badges.push(`<a href="#!" class="small text-danger ms-2" onclick="clearAllFilters(); return false;"><i class="fas fa-times-circle me-1"></i>Clear All</a>`);
        }
        
        activeFiltersDiv.innerHTML = badges.join('');
    }

    document.querySelectorAll('.mobile-number').forEach(el => {
        el.style.cursor = 'pointer';
        el.title = 'Click to toggle masking';
        el.addEventListener('click', function() {
            const full = this.dataset.full;
            const masked = this.dataset.masked;
            this.textContent = this.textContent === masked ? full : masked;
        });
    });
});

function clearFilter(filterId) {
    document.getElementById(filterId).value = '';
    document.getElementById(filterId).dispatchEvent(new Event('change'));
}

function clearAllFilters() {
    document.getElementById('filterStatus').value = '';
    document.getElementById('filterTags').value = '';
    document.getElementById('filterLists').value = '';
    document.getElementById('filterSource').value = '';
    document.getElementById('contactSearch').value = '';
    document.getElementById('filterStatus').dispatchEvent(new Event('change'));
}

function sortContacts(sortKey, direction) {
    /**
     * TODO: Replace client-side sorting with server-side for scalability
     * - Call GET /api/contacts?sort={sortKey}&order={direction}&page=X
     * - Server should use indexed columns for firstName/lastName sorting
     */
    var sortedContacts = [...contactsData].sort((a, b) => {
        let valA, valB;
        
        switch(sortKey) {
            case 'firstName':
                valA = (a.first_name || '').toLowerCase();
                valB = (b.first_name || '').toLowerCase();
                break;
            case 'lastName':
                valA = (a.last_name || '').toLowerCase();
                valB = (b.last_name || '').toLowerCase();
                break;
            case 'contact':
                valA = (a.first_name + ' ' + a.last_name).toLowerCase();
                valB = (b.first_name + ' ' + b.last_name).toLowerCase();
                break;
            case 'status':
                valA = a.status === 'active' ? 0 : 1;
                valB = b.status === 'active' ? 0 : 1;
                break;
            default:
                return 0;
        }
        
        if (valA < valB) return direction === 'asc' ? -1 : 1;
        if (valA > valB) return direction === 'asc' ? 1 : -1;
        return 0;
    });
    
    renderContactsTable(sortedContacts);
}

function renderContactsTable(contacts) {
    const tbody = document.getElementById('contactsTableBody');
    tbody.innerHTML = contacts.map(contact => `
        <tr class="btn-reveal-trigger" data-contact-id="${contact.id}">
            <td class="py-2">
                <div class="form-check custom-checkbox">
                    <input type="checkbox" class="form-check-input contact-checkbox" id="checkbox${contact.id}">
                    <label class="form-check-label" for="checkbox${contact.id}"></label>
                </div>
            </td>
            <td class="py-2">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2" style="width: 36px; height: 36px; font-size: 14px; font-weight: 600;">
                        ${contact.initials}
                    </div>
                    <div>
                        <h6 class="mb-0 fs-6">${contact.first_name} ${contact.last_name}</h6>
                    </div>
                </div>
            </td>
            <td class="py-2">
                <span class="mobile-number" data-full="${contact.mobile}" data-masked="${contact.mobile_masked}" style="cursor: pointer;" title="Click to toggle masking">
                    ${contact.mobile_masked}
                </span>
            </td>
            <td class="py-2">
                ${contact.tags.map(tag => `<span class="badge bg-light text-dark border me-1">${tag}</span>`).join('')}
            </td>
            <td class="py-2">
                ${contact.lists.map(list => `<span class="badge bg-info text-white me-1">${list}</span>`).join('')}
            </td>
            <td class="py-2">
                ${contact.status === 'active' 
                    ? '<span class="badge bg-success">Active</span>' 
                    : '<span class="badge bg-danger">Opted Out</span>'}
            </td>
            <td class="py-2 text-end">
                <div class="dropdown">
                    <button class="btn btn-primary tp-btn-light sharp" type="button" data-bs-toggle="dropdown">
                        <span class="fs--1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18px" height="18px" viewBox="0 0 24 24">
                                <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                    <rect x="0" y="0" width="24" height="24"></rect>
                                    <circle fill="#000000" cx="5" cy="12" r="2"></circle>
                                    <circle fill="#000000" cx="12" cy="12" r="2"></circle>
                                    <circle fill="#000000" cx="19" cy="12" r="2"></circle>
                                </g>
                            </svg>
                        </span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end border py-0">
                        <div class="py-2">
                            <a class="dropdown-item" href="#!" onclick="viewContact(${contact.id}); return false;">
                                <i class="fas fa-eye me-2 text-primary"></i> View Details
                            </a>
                            <a class="dropdown-item" href="#!" onclick="editContact(${contact.id}); return false;">
                                <i class="fas fa-edit me-2 text-info"></i> Edit
                            </a>
                            <a class="dropdown-item" href="#!" onclick="sendMessage(${contact.id}); return false;">
                                <i class="fas fa-paper-plane me-2 text-success"></i> Send Message
                            </a>
                            <a class="dropdown-item" href="#!" onclick="viewTimeline(${contact.id}); return false;">
                                <i class="fas fa-history me-2 text-secondary"></i> Activity Timeline
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item text-danger" href="#!" onclick="deleteContact(${contact.id}); return false;">
                                <i class="fas fa-trash me-2"></i> Delete
                            </a>
                        </div>
                    </div>
                </div>
            </td>
        </tr>
    `).join('');
    
    document.querySelectorAll('.contact-checkbox').forEach(cb => {
        cb.addEventListener('change', function() {
            const checkedCount = document.querySelectorAll('.contact-checkbox:checked').length;
            document.getElementById('selectedCount').textContent = checkedCount;
            const bulkActionBar = document.getElementById('bulkActionBar');
            if (checkedCount > 0) {
                bulkActionBar.classList.remove('d-none');
            } else {
                bulkActionBar.classList.add('d-none');
            }
        });
    });
    
    document.querySelectorAll('.mobile-number').forEach(el => {
        el.addEventListener('click', function() {
            const full = this.dataset.full;
            const masked = this.dataset.masked;
            this.textContent = this.textContent === masked ? full : masked;
        });
    });
}

function viewContact(id) {
    console.log('TODO: viewContact - Fetch from API: GET /api/contacts/' + id);
    var contact = contactsData.find(c => c.id === id);
    if (!contact) return;
    
    document.getElementById('viewContactName').textContent = contact.first_name + ' ' + contact.last_name;
    document.getElementById('viewContactInitials').textContent = contact.initials;
    document.getElementById('viewContactMobile').textContent = contact.mobile;
    document.getElementById('viewContactEmail').textContent = contact.email || 'Not provided';
    document.getElementById('viewContactStatus').innerHTML = contact.status === 'active' 
        ? '<span class="badge bg-success">Active</span>' 
        : '<span class="badge bg-danger">Opted Out</span>';
    document.getElementById('viewContactSource').textContent = contact.source;
    document.getElementById('viewContactCreated').textContent = contact.created_at;
    
    var tagsHtml = contact.tags.length > 0 
        ? contact.tags.map(t => '<span class="badge bg-light text-dark border me-1">' + t + '</span>').join('') 
        : '<span class="text-muted">No tags</span>';
    document.getElementById('viewContactTags').innerHTML = tagsHtml;
    
    var listsHtml = contact.lists.length > 0 
        ? contact.lists.map(l => '<span class="badge bg-info text-white me-1">' + l + '</span>').join('') 
        : '<span class="text-muted">No lists</span>';
    document.getElementById('viewContactLists').innerHTML = listsHtml;
    
    var modal = new bootstrap.Modal(document.getElementById('viewContactModal'));
    modal.show();
}

function editContact(id) {
    console.log('TODO: editContact - Submit updates via API: PUT /api/contacts/' + id);
    var contact = contactsData.find(c => c.id === id);
    if (!contact) return;
    
    document.getElementById('editContactId').value = contact.id;
    document.getElementById('editContactFirstName').value = contact.first_name;
    document.getElementById('editContactLastName').value = contact.last_name;
    document.getElementById('editContactMobile').value = contact.mobile;
    document.getElementById('editContactEmail').value = contact.email || '';
    document.getElementById('editContactStatus').value = contact.status;
    
    var modal = new bootstrap.Modal(document.getElementById('editContactModal'));
    modal.show();
}

function sendMessage(id) {
    console.log('TODO: sendMessage - Navigate to Send Message screen');
    console.log('TODO: Pre-populate recipients section with contact ID: ' + id);
    console.log('TODO: Integrate with Messages > Send Message module');
    alert('Send Message\n\nContact ID: ' + id + '\n\nThis feature requires:\n- Navigation to Send Message screen\n- Pre-populate recipient with selected contact\n- Standard campaign flow integration');
}

function viewTimeline(id) {
    console.log('TODO: viewTimeline - Display activity timeline');
    console.log('TODO: Fetch activity history from API: GET /api/contacts/' + id + '/timeline');
    console.log('TODO: Show campaigns sent, replies received, opt-out events, tag/list changes');
    alert('Activity Timeline\n\nContact ID: ' + id + '\n\nThis feature requires backend implementation:\n- API endpoint: GET /api/contacts/{id}/timeline\n- Activity log database table\n- Timeline UI component');
}

function deleteContact(id) {
    if (confirm('Are you sure you want to delete this contact?\n\nThis action cannot be undone.')) {
        console.log('TODO: deleteContact - Permission check required');
        console.log('TODO: Call API: DELETE /api/contacts/' + id);
        console.log('TODO: Remove row from table on success');
        console.log('TODO: Show success/error notification');
        alert('Delete Contact\n\nContact ID: ' + id + '\n\nThis feature requires backend implementation:\n- Permission check\n- API endpoint: DELETE /api/contacts/{id}\n- Cascade delete or soft delete logic');
    }
}

function getSelectedContactIds() {
    var ids = [];
    document.querySelectorAll('.contact-checkbox:checked').forEach(cb => {
        var row = cb.closest('tr');
        if (row) {
            ids.push(parseInt(row.dataset.contactId));
        }
    });
    return ids;
}

function getSelectedContactNames() {
    var ids = getSelectedContactIds();
    return ids.map(id => {
        var contact = contactsData.find(c => c.id === id);
        return contact ? contact.first_name + ' ' + contact.last_name : 'Unknown';
    });
}

function bulkAddToList() {
    var ids = getSelectedContactIds();
    var names = getSelectedContactNames();
    var modal = new bootstrap.Modal(document.getElementById('bulkAddToListModal'));
    document.getElementById('bulkAddToListCount').textContent = ids.length;
    modal.show();
}

function confirmBulkAddToList() {
    var ids = getSelectedContactIds();
    var listSelect = document.getElementById('bulkListSelect');
    var selectedList = listSelect.value;
    
    if (!selectedList) {
        alert('Please select a list.');
        return;
    }
    
    console.log('TODO: Add contacts ' + ids.join(', ') + ' to list: ' + selectedList);
    alert('Added ' + ids.length + ' contact(s) to "' + selectedList + '"!\n\nThis requires backend implementation.');
    
    var modal = bootstrap.Modal.getInstance(document.getElementById('bulkAddToListModal'));
    modal.hide();
    
    document.querySelectorAll('.contact-checkbox:checked').forEach(cb => cb.checked = false);
    document.getElementById('checkAll').checked = false;
    document.getElementById('bulkActionBar').classList.add('d-none');
}

function bulkRemoveFromList() {
    var ids = getSelectedContactIds();
    var modal = new bootstrap.Modal(document.getElementById('bulkRemoveFromListModal'));
    document.getElementById('bulkRemoveFromListCount').textContent = ids.length;
    modal.show();
}

function confirmBulkRemoveFromList() {
    var ids = getSelectedContactIds();
    var listSelect = document.getElementById('bulkRemoveListSelect');
    var selectedList = listSelect.value;
    
    if (!selectedList) {
        alert('Please select a list.');
        return;
    }
    
    console.log('TODO: Remove contacts ' + ids.join(', ') + ' from list: ' + selectedList);
    alert('Removed ' + ids.length + ' contact(s) from "' + selectedList + '"!\n\nThis requires backend implementation.');
    
    var modal = bootstrap.Modal.getInstance(document.getElementById('bulkRemoveFromListModal'));
    modal.hide();
    
    document.querySelectorAll('.contact-checkbox:checked').forEach(cb => cb.checked = false);
    document.getElementById('checkAll').checked = false;
    document.getElementById('bulkActionBar').classList.add('d-none');
}

function bulkAddTags() {
    var ids = getSelectedContactIds();
    var modal = new bootstrap.Modal(document.getElementById('bulkAddTagsModal'));
    document.getElementById('bulkAddTagsCount').textContent = ids.length;
    modal.show();
}

function confirmBulkAddTags() {
    var ids = getSelectedContactIds();
    var tagSelect = document.getElementById('bulkTagSelect');
    var selectedTags = Array.from(tagSelect.selectedOptions).map(o => o.value);
    
    if (selectedTags.length === 0) {
        alert('Please select at least one tag.');
        return;
    }
    
    console.log('TODO: Add tags ' + selectedTags.join(', ') + ' to contacts: ' + ids.join(', '));
    alert('Added tags "' + selectedTags.join(', ') + '" to ' + ids.length + ' contact(s)!\n\nThis requires backend implementation.');
    
    var modal = bootstrap.Modal.getInstance(document.getElementById('bulkAddTagsModal'));
    modal.hide();
    
    document.querySelectorAll('.contact-checkbox:checked').forEach(cb => cb.checked = false);
    document.getElementById('checkAll').checked = false;
    document.getElementById('bulkActionBar').classList.add('d-none');
}

function bulkRemoveTags() {
    var ids = getSelectedContactIds();
    var modal = new bootstrap.Modal(document.getElementById('bulkRemoveTagsModal'));
    document.getElementById('bulkRemoveTagsCount').textContent = ids.length;
    modal.show();
}

function confirmBulkRemoveTags() {
    var ids = getSelectedContactIds();
    var tagSelect = document.getElementById('bulkRemoveTagSelect');
    var selectedTags = Array.from(tagSelect.selectedOptions).map(o => o.value);
    
    if (selectedTags.length === 0) {
        alert('Please select at least one tag.');
        return;
    }
    
    console.log('TODO: Remove tags ' + selectedTags.join(', ') + ' from contacts: ' + ids.join(', '));
    alert('Removed tags "' + selectedTags.join(', ') + '" from ' + ids.length + ' contact(s)!\n\nThis requires backend implementation.');
    
    var modal = bootstrap.Modal.getInstance(document.getElementById('bulkRemoveTagsModal'));
    modal.hide();
    
    document.querySelectorAll('.contact-checkbox:checked').forEach(cb => cb.checked = false);
    document.getElementById('checkAll').checked = false;
    document.getElementById('bulkActionBar').classList.add('d-none');
}

function bulkSendMessage() {
    var ids = getSelectedContactIds();
    var names = getSelectedContactNames();
    
    alert('Send Message to ' + ids.length + ' contact(s):\n\n' + names.join('\n') + '\n\nThis will redirect to the Send Message screen with these contacts pre-selected.\n\nRequires Messages module integration.');
    console.log('TODO: Navigate to Send Message with contact IDs: ' + ids.join(', '));
}

function openExportModal() {
    var ids = getSelectedContactIds();
    document.getElementById('exportContactCount').textContent = ids.length;
    var modal = new bootstrap.Modal(document.getElementById('bulkExportModal'));
    modal.show();
}

function formatMobileInternational(mobile) {
    var cleaned = mobile.replace(/[^0-9]/g, '');
    if (cleaned.startsWith('0')) {
        cleaned = '44' + cleaned.substring(1);
    }
    return cleaned;
}

function performExport() {
    var ids = getSelectedContactIds();
    var format = document.querySelector('input[name="exportFormat"]:checked').value;
    
    var selectedFields = [];
    document.querySelectorAll('.export-field-checkbox:checked').forEach(cb => {
        selectedFields.push(cb.value);
    });
    
    if (selectedFields.length === 0) {
        alert('Please select at least one field to export.');
        return;
    }
    
    var fieldLabels = {
        'name': 'Name',
        'first_name': 'First Name',
        'last_name': 'Last Name',
        'email': 'Email',
        'mobile': 'Mobile Number',
        'tags': 'Tags',
        'lists': 'Lists',
        'status': 'Status',
        'source': 'Source',
        'created_date': 'Created Date'
    };
    
    var header = selectedFields.map(f => fieldLabels[f] || f).join(',');
    var rows = [header];
    
    ids.forEach(id => {
        var contact = contactsData.find(c => c.id === id);
        if (contact) {
            var row = selectedFields.map(field => {
                var value = '';
                switch(field) {
                    case 'name':
                        value = contact.first_name + ' ' + contact.last_name;
                        break;
                    case 'first_name':
                        value = contact.first_name;
                        break;
                    case 'last_name':
                        value = contact.last_name;
                        break;
                    case 'email':
                        value = contact.email;
                        break;
                    case 'mobile':
                        value = formatMobileInternational(contact.mobile);
                        break;
                    case 'tags':
                        value = contact.tags.join('; ');
                        break;
                    case 'lists':
                        value = contact.lists.join('; ');
                        break;
                    case 'status':
                        value = contact.status;
                        break;
                    case 'source':
                        value = contact.source || '';
                        break;
                    case 'created_date':
                        value = contact.created_date || '';
                        break;
                    default:
                        value = '';
                }
                return '"' + String(value).replace(/"/g, '""') + '"';
            });
            rows.push(row.join(','));
        }
    });
    
    var csvContent = rows.join('\n');
    var filename = 'contacts_export_' + new Date().toISOString().slice(0,10);
    
    if (format === 'csv') {
        var blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        var url = window.URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url;
        a.download = filename + '.csv';
        a.click();
        window.URL.revokeObjectURL(url);
    } else {
        var blob = new Blob([csvContent], { type: 'application/vnd.ms-excel;charset=utf-8;' });
        var url = window.URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url;
        a.download = filename + '.xlsx';
        a.click();
        window.URL.revokeObjectURL(url);
        console.log('Note: True XLSX export requires a library like SheetJS. This is a CSV with .xlsx extension.');
    }
    
    var modal = bootstrap.Modal.getInstance(document.getElementById('bulkExportModal'));
    modal.hide();
    
    alert('Exported ' + ids.length + ' contact(s) to ' + format.toUpperCase() + '!');
}

function bulkDelete() {
    var ids = getSelectedContactIds();
    var names = getSelectedContactNames();
    
    if (confirm('Are you sure you want to delete ' + ids.length + ' contact(s)?\n\n' + names.join('\n') + '\n\nThis action cannot be undone.')) {
        console.log('TODO: Delete contacts: ' + ids.join(', '));
        alert('Deleted ' + ids.length + ' contact(s)!\n\nThis requires backend implementation:\n- API endpoint: DELETE /api/contacts/bulk\n- Permission checks');
        
        document.querySelectorAll('.contact-checkbox:checked').forEach(cb => cb.checked = false);
        document.getElementById('checkAll').checked = false;
        document.getElementById('bulkActionBar').classList.add('d-none');
    }
}
</script>

<div class="modal fade" id="addContactModal" tabindex="-1" aria-labelledby="addContactModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addContactModalLabel">Add New Contact</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addContactForm">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">First Name</label>
                            <input type="text" class="form-control" id="contactFirstName" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="contactLastName" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mobile Number <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" id="contactMobile" placeholder="+44 7700 900000" required>
                            <small class="text-muted">E.164 format preferred</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" id="contactEmail">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" class="form-control" id="contactDOB">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Postcode</label>
                            <input type="text" class="form-control" id="contactPostcode">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">City / Town</label>
                            <input type="text" class="form-control" id="contactCity">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Country</label>
                            <select class="form-select" id="contactCountry">
                                <option value="">Select Country</option>
                                <option value="UK">United Kingdom</option>
                                <option value="US">United States</option>
                                <option value="CA">Canada</option>
                                <option value="AU">Australia</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Tags</label>
                            <select class="form-select" id="contactTags" multiple>
                                @foreach($available_tags as $tag)
                                <option value="{{ $tag }}">{{ $tag }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Hold Ctrl/Cmd to select multiple</small>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Lists</label>
                            <select class="form-select" id="contactLists" multiple>
                                @foreach($available_lists as $list)
                                <option value="{{ $list }}">{{ $list }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Hold Ctrl/Cmd to select multiple</small>
                        </div>
                        
                        <div class="col-12">
                            <hr class="my-3">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0"><i class="fas fa-sliders-h me-2"></i>Custom Fields</h6>
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="openManageCustomFields()">
                                    <i class="fas fa-cog me-1"></i> Manage Fields
                                </button>
                            </div>
                            <div id="customFieldsContainer" class="row g-3">
                            </div>
                        </div>
                    </div>
                    <div id="formValidationMessage" class="alert alert-danger mt-3 d-none"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveContact()">
                    <i class="fas fa-save me-1"></i> Save Contact
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="viewContactModal" tabindex="-1" aria-labelledby="viewContactModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewContactModalLabel">Contact Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center mx-auto" style="width: 80px; height: 80px; font-size: 28px; font-weight: 600;">
                        <span id="viewContactInitials"></span>
                    </div>
                    <h4 class="mt-3 mb-1" id="viewContactName"></h4>
                    <div id="viewContactStatus"></div>
                </div>
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card bg-light border-0">
                            <div class="card-body">
                                <small class="text-muted d-block">Mobile Number</small>
                                <strong id="viewContactMobile"></strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-light border-0">
                            <div class="card-body">
                                <small class="text-muted d-block">Email</small>
                                <strong id="viewContactEmail"></strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-light border-0">
                            <div class="card-body">
                                <small class="text-muted d-block">Source</small>
                                <strong id="viewContactSource"></strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-light border-0">
                            <div class="card-body">
                                <small class="text-muted d-block">Created Date</small>
                                <strong id="viewContactCreated"></strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="card bg-light border-0">
                            <div class="card-body">
                                <small class="text-muted d-block mb-2">Tags</small>
                                <div id="viewContactTags"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="card bg-light border-0">
                            <div class="card-body">
                                <small class="text-muted d-block mb-2">Lists</small>
                                <div id="viewContactLists"></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-info mt-4 mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Activity Timeline:</strong> Campaign history, replies, and opt-out events will appear here when backend is implemented.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editContactModal" tabindex="-1" aria-labelledby="editContactModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editContactModalLabel">Edit Contact</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editContactForm">
                    <input type="hidden" id="editContactId">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">First Name</label>
                            <input type="text" class="form-control" id="editContactFirstName">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="editContactLastName">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mobile Number <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" id="editContactMobile" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" id="editContactEmail">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select class="form-select" id="editContactStatus">
                                <option value="active">Active</option>
                                <option value="opted-out">Opted Out</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" class="form-control" id="editContactDOB">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Postcode</label>
                            <input type="text" class="form-control" id="editContactPostcode">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">City / Town</label>
                            <input type="text" class="form-control" id="editContactCity">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Tags</label>
                            <select class="form-select" id="editContactTags" multiple>
                                @foreach($available_tags as $tag)
                                <option value="{{ $tag }}">{{ $tag }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Hold Ctrl/Cmd to select multiple</small>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Lists</label>
                            <select class="form-select" id="editContactLists" multiple>
                                @foreach($available_lists as $list)
                                <option value="{{ $list }}">{{ $list }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Hold Ctrl/Cmd to select multiple</small>
                        </div>
                    </div>
                    <div id="editFormValidationMessage" class="alert alert-danger mt-3 d-none"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="updateContact()">
                    <i class="fas fa-save me-1"></i> Save Changes
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function updateContact() {
    var id = document.getElementById('editContactId').value;
    var mobile = document.getElementById('editContactMobile').value.trim();
    var validationMsg = document.getElementById('editFormValidationMessage');
    
    validationMsg.classList.add('d-none');
    
    if (!mobile) {
        validationMsg.textContent = 'Mobile number is required.';
        validationMsg.classList.remove('d-none');
        return;
    }
    
    console.log('TODO: updateContact - Submit to API: PUT /api/contacts/' + id);
    
    alert('Contact Updated!\n\nContact ID: ' + id + '\n\nThis feature requires backend implementation:\n- API endpoint: PUT /api/contacts/{id}\n- Database persistence');
    
    var modal = bootstrap.Modal.getInstance(document.getElementById('editContactModal'));
    modal.hide();
}
</script>

<script>
function saveContact() {
    const form = document.getElementById('addContactForm');
    const mobile = document.getElementById('contactMobile').value.trim();
    const firstName = document.getElementById('contactFirstName').value.trim();
    const lastName = document.getElementById('contactLastName').value.trim();
    const validationMsg = document.getElementById('formValidationMessage');
    
    validationMsg.classList.add('d-none');
    
    if (!mobile) {
        validationMsg.textContent = 'Mobile number is required.';
        validationMsg.classList.remove('d-none');
        return;
    }
    
    if (!mobile.match(/^\+?[0-9\s\-]{10,}$/)) {
        validationMsg.textContent = 'Please enter a valid mobile number (E.164 format preferred, e.g., +44 7700 900000).';
        validationMsg.classList.remove('d-none');
        return;
    }
    
    console.log('TODO: saveContact - Submit to API');
    console.log('TODO: POST /api/contacts with form data');
    console.log('TODO: Validate mobile number format on server');
    console.log('TODO: Check for duplicate mobile numbers');
    console.log('TODO: Persist to database and refresh table');
    
    alert('Contact Validated Successfully!\n\nFirst Name: ' + firstName + '\nLast Name: ' + lastName + '\nMobile: ' + mobile + '\n\nThis feature requires backend implementation:\n- API endpoint: POST /api/contacts\n- Database persistence\n- Duplicate check');
    
    var modal = bootstrap.Modal.getInstance(document.getElementById('addContactModal'));
    modal.hide();
    form.reset();
}

function renderCustomFields() {
    const container = document.getElementById('customFieldsContainer');
    if (customFieldDefinitions.length === 0) {
        container.innerHTML = '<div class="col-12"><p class="text-muted small mb-0"><i class="fas fa-info-circle me-1"></i>No custom fields defined. Click "Manage Fields" to create custom fields.</p></div>';
        return;
    }
    
    container.innerHTML = customFieldDefinitions.map(field => `
        <div class="col-md-6">
            <label class="form-label">${field.name}</label>
            ${field.type === 'text' ? `<input type="text" class="form-control" id="custom_${field.slug}" placeholder="${field.defaultValue || ''}">` : ''}
            ${field.type === 'number' ? `<input type="number" class="form-control" id="custom_${field.slug}">` : ''}
            ${field.type === 'date' ? `<input type="date" class="form-control" id="custom_${field.slug}">` : ''}
            ${field.type === 'dropdown' ? `<select class="form-select" id="custom_${field.slug}"><option value="">Select...</option>${(field.options || []).map(o => `<option value="${o}">${o}</option>`).join('')}</select>` : ''}
        </div>
    `).join('');
}

function openManageCustomFields() {
    renderCustomFieldsList();
    var modal = new bootstrap.Modal(document.getElementById('manageCustomFieldsModal'));
    modal.show();
}

function renderCustomFieldsList() {
    const list = document.getElementById('customFieldsList');
    if (customFieldDefinitions.length === 0) {
        list.innerHTML = '<p class="text-muted text-center py-3"><i class="fas fa-info-circle me-1"></i>No custom fields defined yet.</p>';
        return;
    }
    
    list.innerHTML = `
        <table class="table table-sm table-hover mb-0">
            <thead>
                <tr>
                    <th>Field Name</th>
                    <th>Type</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                ${customFieldDefinitions.map(field => `
                    <tr>
                        <td>${field.name}</td>
                        <td><span class="badge bg-light text-dark">${field.type}</span></td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteCustomField(${field.id})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `).join('')}
            </tbody>
        </table>
    `;
}

function addCustomField() {
    const nameInput = document.getElementById('newFieldName');
    const typeSelect = document.getElementById('newFieldType');
    const name = nameInput.value.trim();
    const type = typeSelect.value;
    
    if (!name) {
        alert('Please enter a field name.');
        return;
    }
    
    const slug = name.toLowerCase().replace(/[^a-z0-9]/g, '_');
    
    if (customFieldDefinitions.some(f => f.slug === slug)) {
        alert('A field with this name already exists.');
        return;
    }
    
    const newField = {
        id: Date.now(),
        name: name,
        slug: slug,
        type: type,
        defaultValue: '',
        options: type === 'dropdown' ? ['Option 1', 'Option 2'] : []
    };
    
    customFieldDefinitions.push(newField);
    renderCustomFieldsList();
    renderCustomFields();
    
    nameInput.value = '';
    typeSelect.value = 'text';
    
    console.log('TODO: Persist custom field to database');
}

function deleteCustomField(id) {
    if (confirm('Are you sure you want to delete this custom field? This will remove it from all contacts.')) {
        customFieldDefinitions = customFieldDefinitions.filter(f => f.id !== id);
        renderCustomFieldsList();
        renderCustomFields();
        console.log('TODO: Delete custom field from database');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    renderCustomFields();
    
    document.getElementById('addContactModal').addEventListener('show.bs.modal', function() {
        renderCustomFields();
    });
});
</script>

<div class="modal fade" id="bulkAddToListModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus me-2"></i>Add to List</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Add <strong id="bulkAddToListCount">0</strong> contact(s) to:</p>
                <select class="form-select" id="bulkListSelect">
                    <option value="">Select a list...</option>
                    @foreach($available_lists as $list)
                    <option value="{{ $list }}">{{ $list }}</option>
                    @endforeach
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" onclick="confirmBulkAddToList()">Add to List</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="bulkRemoveFromListModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-minus me-2"></i>Remove from List</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Remove <strong id="bulkRemoveFromListCount">0</strong> contact(s) from:</p>
                <select class="form-select" id="bulkRemoveListSelect">
                    <option value="">Select a list...</option>
                    @foreach($available_lists as $list)
                    <option value="{{ $list }}">{{ $list }}</option>
                    @endforeach
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" onclick="confirmBulkRemoveFromList()">Remove from List</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="bulkAddTagsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-tag me-2"></i>Add Tags</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Add tags to <strong id="bulkAddTagsCount">0</strong> contact(s):</p>
                <select class="form-select" id="bulkTagSelect" multiple>
                    @foreach($available_tags as $tag)
                    <option value="{{ $tag }}">{{ $tag }}</option>
                    @endforeach
                </select>
                <small class="text-muted">Hold Ctrl/Cmd to select multiple</small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" onclick="confirmBulkAddTags()">Add Tags</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="bulkRemoveTagsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-times me-2"></i>Remove Tags</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Remove tags from <strong id="bulkRemoveTagsCount">0</strong> contact(s):</p>
                <select class="form-select" id="bulkRemoveTagSelect" multiple>
                    @foreach($available_tags as $tag)
                    <option value="{{ $tag }}">{{ $tag }}</option>
                    @endforeach
                </select>
                <small class="text-muted">Hold Ctrl/Cmd to select multiple</small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger btn-sm" onclick="confirmBulkRemoveTags()">Remove Tags</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="bulkExportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-download me-2"></i>Export Contacts</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Export <strong id="exportContactCount">0</strong> contact(s)</p>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Select Fields to Export</label>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-check">
                                <input class="form-check-input export-field-checkbox" type="checkbox" value="name" id="exportName" checked>
                                <label class="form-check-label" for="exportName">Full Name</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input export-field-checkbox" type="checkbox" value="first_name" id="exportFirstName">
                                <label class="form-check-label" for="exportFirstName">First Name</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input export-field-checkbox" type="checkbox" value="last_name" id="exportLastName">
                                <label class="form-check-label" for="exportLastName">Last Name</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input export-field-checkbox" type="checkbox" value="email" id="exportEmail" checked>
                                <label class="form-check-label" for="exportEmail">Email</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input export-field-checkbox" type="checkbox" value="mobile" id="exportMobile" checked>
                                <label class="form-check-label" for="exportMobile">Mobile Number</label>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-check">
                                <input class="form-check-input export-field-checkbox" type="checkbox" value="tags" id="exportTags">
                                <label class="form-check-label" for="exportTags">Tags</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input export-field-checkbox" type="checkbox" value="lists" id="exportLists">
                                <label class="form-check-label" for="exportLists">Lists</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input export-field-checkbox" type="checkbox" value="status" id="exportStatus" checked>
                                <label class="form-check-label" for="exportStatus">Status</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input export-field-checkbox" type="checkbox" value="source" id="exportSource">
                                <label class="form-check-label" for="exportSource">Source</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input export-field-checkbox" type="checkbox" value="created_date" id="exportCreatedDate">
                                <label class="form-check-label" for="exportCreatedDate">Created Date</label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Export Format</label>
                    <div class="d-flex gap-4">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="exportFormat" id="formatCSV" value="csv" checked>
                            <label class="form-check-label" for="formatCSV">
                                <i class="fas fa-file-csv me-1"></i> CSV
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="exportFormat" id="formatXLSX" value="xlsx">
                            <label class="form-check-label" for="formatXLSX">
                                <i class="fas fa-file-excel me-1"></i> XLSX
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-info small mb-0">
                    <i class="fas fa-info-circle me-1"></i>
                    Mobile numbers will be exported in international format (e.g., 447712345678)
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" onclick="performExport()">
                    <i class="fas fa-download me-1"></i> Export
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="manageCustomFieldsModal" tabindex="-1" aria-labelledby="manageCustomFieldsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="manageCustomFieldsModalLabel"><i class="fas fa-sliders-h me-2"></i>Manage Custom Fields</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="card bg-light border-0 mb-3">
                    <div class="card-body">
                        <h6 class="card-title">Add New Field</h6>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <input type="text" class="form-control form-control-sm" id="newFieldName" placeholder="Field name">
                            </div>
                            <div class="col-md-4">
                                <select class="form-select form-select-sm" id="newFieldType">
                                    <option value="text">Text</option>
                                    <option value="number">Number</option>
                                    <option value="date">Date</option>
                                    <option value="dropdown">Dropdown</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-primary btn-sm w-100" onclick="addCustomField()">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <h6>Existing Fields</h6>
                <div id="customFieldsList" class="border rounded">
                </div>
                
                <div class="alert alert-info mt-3 mb-0 small">
                    <i class="fas fa-info-circle me-1"></i>
                    Custom fields will appear in all contact forms and can be used for filtering and searching.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="importContactsModal" tabindex="-1" aria-labelledby="importContactsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importContactsModalLabel"><i class="fas fa-file-import me-2"></i>Import Contacts</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-3">
                        <div class="text-center flex-fill">
                            <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center import-step-circle active" style="width: 32px; height: 32px;" id="stepCircle1">1</div>
                            <div class="small mt-1">Upload</div>
                        </div>
                        <div class="text-center flex-fill">
                            <div class="rounded-circle bg-secondary text-white d-inline-flex align-items-center justify-content-center import-step-circle" style="width: 32px; height: 32px;" id="stepCircle2">2</div>
                            <div class="small mt-1">Map Columns</div>
                        </div>
                        <div class="text-center flex-fill">
                            <div class="rounded-circle bg-secondary text-white d-inline-flex align-items-center justify-content-center import-step-circle" style="width: 32px; height: 32px;" id="stepCircle3">3</div>
                            <div class="small mt-1">Review</div>
                        </div>
                        <div class="text-center flex-fill">
                            <div class="rounded-circle bg-secondary text-white d-inline-flex align-items-center justify-content-center import-step-circle" style="width: 32px; height: 32px;" id="stepCircle4">4</div>
                            <div class="small mt-1">Complete</div>
                        </div>
                    </div>
                </div>

                <div id="importStep1">
                    <h6 class="mb-3">Step 1: Upload File</h6>
                    <div class="border rounded p-4 text-center bg-light" id="dropZone" style="border-style: dashed !important;">
                        <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                        <p class="mb-2">Drag and drop your file here, or click to browse</p>
                        <input type="file" class="d-none" id="importFileInput" accept=".csv,.xlsx">
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="document.getElementById('importFileInput').click()">
                            <i class="fas fa-folder-open me-1"></i> Browse Files
                        </button>
                        <p class="text-muted small mt-2 mb-0">Accepted formats: CSV, Excel (.xlsx)</p>
                    </div>
                    <div id="selectedFileInfo" class="d-none mt-3">
                        <div class="alert alert-success d-flex align-items-center">
                            <i class="fas fa-file-alt fa-2x me-3"></i>
                            <div>
                                <strong id="selectedFileName">filename.csv</strong>
                                <div class="small text-muted" id="selectedFileSize">123 KB</div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger ms-auto" onclick="clearImportFile()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div id="worksheetSelection" class="d-none mt-3">
                        <label class="form-label fw-bold">Select Worksheet</label>
                        <select class="form-select" id="worksheetSelect">
                            <option value="Sheet1">Sheet1</option>
                        </select>
                    </div>
                    
                    <div class="mt-3">
                        <label class="form-label fw-bold">Does the first row contain column headings?</label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="hasHeaders" id="hasHeadersYes" value="yes" checked>
                                <label class="form-check-label" for="hasHeadersYes">Yes - first row contains headings</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="hasHeaders" id="hasHeadersNo" value="no">
                                <label class="form-check-label" for="hasHeadersNo">No - first row contains data</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="importStep2" class="d-none">
                    <h6 class="mb-3">Step 2: Map Columns</h6>
                    <div class="alert alert-info small">
                        <i class="fas fa-info-circle me-1"></i>
                        Map your file columns to contact fields. <strong>Mobile Number</strong> is required.
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Your Column</th>
                                    <th>Sample Data</th>
                                    <th>Map To</th>
                                </tr>
                            </thead>
                            <tbody id="columnMappingBody">
                            </tbody>
                        </table>
                    </div>
                    
                    <input type="hidden" id="excelCorrectionApplied" value="">
                    <div id="excelZeroWarning" class="alert alert-warning d-none">
                        <div id="excelZeroWarningContent">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Excel Number Detection</strong>
                            <p class="mb-2 mt-2">We've detected mobile numbers starting with '7'. This often occurs when Excel removes the leading zero from UK mobile numbers.</p>
                            <p class="mb-2">Should these be treated as UK numbers and converted to international format (+447...)?</p>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-sm btn-primary" onclick="setExcelCorrection(true)">
                                    <i class="fas fa-check me-1"></i> Yes, convert to UK format
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setExcelCorrection(false)">
                                    <i class="fas fa-times me-1"></i> No, leave as-is
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="importStep3" class="d-none">
                    <h6 class="mb-3">Step 3: Review & Validate</h6>
                    
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body text-center py-3">
                                    <div class="h3 mb-0 text-primary" id="statTotalRows">0</div>
                                    <div class="small text-muted">Total Rows</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body text-center py-3">
                                    <div class="h3 mb-0 text-info" id="statUniqueNumbers">0</div>
                                    <div class="small text-muted">Unique Numbers</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body text-center py-3">
                                    <div class="h3 mb-0 text-success" id="statValidNumbers">0</div>
                                    <div class="small text-muted">Valid Numbers</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body text-center py-3">
                                    <div class="h3 mb-0 text-danger" id="statInvalidNumbers">0</div>
                                    <div class="small text-muted">Invalid Numbers</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div id="importIndicators" class="mb-3">
                    </div>
                    
                    <div id="invalidRowsSection" class="d-none">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0"><i class="fas fa-exclamation-circle text-danger me-2"></i>Invalid Rows</h6>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="downloadInvalidRows()">
                                <i class="fas fa-download me-1"></i> Download Invalid Rows
                            </button>
                        </div>
                        <div class="table-responsive" style="max-height: 200px; overflow-y: auto;">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th>Row</th>
                                        <th>Original Value</th>
                                        <th>Reason</th>
                                    </tr>
                                </thead>
                                <tbody id="invalidRowsBody">
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <h6>Confirm Settings</h6>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="confirmMappings" checked>
                            <label class="form-check-label" for="confirmMappings">I confirm the column mappings are correct</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="confirmRules" checked>
                            <label class="form-check-label" for="confirmRules">I confirm the number formatting rules</label>
                        </div>
                    </div>
                </div>

                <div id="importStep4" class="d-none">
                    <div class="text-center py-4">
                        <i class="fas fa-check-circle fa-5x text-success mb-3"></i>
                        <h4>Import Complete!</h4>
                        <p class="text-muted" id="importCompleteMessage">Successfully imported 0 contacts.</p>
                        <div class="alert alert-info small mt-3">
                            <i class="fas fa-info-circle me-1"></i>
                            Your contacts are now available in the contact list and can be used for messaging.
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="importCancelBtn">Cancel</button>
                <button type="button" class="btn btn-outline-primary d-none" id="importBackBtn" onclick="importPrevStep()">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </button>
                <button type="button" class="btn btn-primary" id="importNextBtn" onclick="importNextStep()" disabled>
                    Next <i class="fas fa-arrow-right ms-1"></i>
                </button>
                <button type="button" class="btn btn-success d-none" id="importConfirmBtn" onclick="confirmImport()">
                    <i class="fas fa-check me-1"></i> Confirm & Import
                </button>
                <button type="button" class="btn btn-primary d-none" id="importDoneBtn" data-bs-dismiss="modal">
                    <i class="fas fa-check me-1"></i> Done
                </button>
            </div>
        </div>
    </div>
</div>

<script>
var importCurrentStep = 1;
var importFileData = null;
var importMappings = {};
var importValidationResults = null;

document.getElementById('importFileInput').addEventListener('change', function(e) {
    handleImportFile(e.target.files[0]);
});

var dropZone = document.getElementById('dropZone');
dropZone.addEventListener('dragover', function(e) {
    e.preventDefault();
    this.classList.add('border-primary');
});
dropZone.addEventListener('dragleave', function(e) {
    e.preventDefault();
    this.classList.remove('border-primary');
});
dropZone.addEventListener('drop', function(e) {
    e.preventDefault();
    this.classList.remove('border-primary');
    if (e.dataTransfer.files.length) {
        handleImportFile(e.dataTransfer.files[0]);
    }
});

function handleImportFile(file) {
    if (!file) return;
    
    var validTypes = ['text/csv', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'];
    var validExtensions = ['.csv', '.xlsx', '.xls'];
    var ext = file.name.substring(file.name.lastIndexOf('.')).toLowerCase();
    
    if (!validExtensions.includes(ext)) {
        alert('Please upload a CSV or Excel file.');
        return;
    }
    
    importFileData = {
        file: file,
        name: file.name,
        size: formatFileSize(file.size),
        type: ext === '.csv' ? 'csv' : 'excel'
    };
    
    document.getElementById('selectedFileName').textContent = file.name;
    document.getElementById('selectedFileSize').textContent = formatFileSize(file.size);
    document.getElementById('selectedFileInfo').classList.remove('d-none');
    document.getElementById('dropZone').classList.add('d-none');
    
    if (importFileData.type === 'excel') {
        document.getElementById('worksheetSelection').classList.remove('d-none');
    }
    
    document.getElementById('importNextBtn').disabled = false;
}

function formatFileSize(bytes) {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
}

function clearImportFile() {
    importFileData = null;
    document.getElementById('importFileInput').value = '';
    document.getElementById('selectedFileInfo').classList.add('d-none');
    document.getElementById('dropZone').classList.remove('d-none');
    document.getElementById('worksheetSelection').classList.add('d-none');
    document.getElementById('importNextBtn').disabled = true;
}

function importNextStep() {
    if (importCurrentStep === 1) {
        showStep(2);
        simulateColumnDetection();
    } else if (importCurrentStep === 2) {
        if (!validateMappings()) return;
        showStep(3);
        simulateValidation();
    }
}

function importPrevStep() {
    if (importCurrentStep > 1) {
        showStep(importCurrentStep - 1);
    }
}

function showStep(step) {
    importCurrentStep = step;
    
    for (var i = 1; i <= 4; i++) {
        document.getElementById('importStep' + i).classList.add('d-none');
        document.getElementById('stepCircle' + i).classList.remove('bg-primary');
        document.getElementById('stepCircle' + i).classList.add('bg-secondary');
    }
    
    document.getElementById('importStep' + step).classList.remove('d-none');
    for (var i = 1; i <= step; i++) {
        document.getElementById('stepCircle' + i).classList.remove('bg-secondary');
        document.getElementById('stepCircle' + i).classList.add('bg-primary');
    }
    
    document.getElementById('importBackBtn').classList.toggle('d-none', step === 1 || step === 4);
    document.getElementById('importNextBtn').classList.toggle('d-none', step >= 3);
    document.getElementById('importConfirmBtn').classList.toggle('d-none', step !== 3);
    document.getElementById('importDoneBtn').classList.toggle('d-none', step !== 4);
    document.getElementById('importCancelBtn').classList.toggle('d-none', step === 4);
}

function simulateColumnDetection() {
    var hasHeaders = document.querySelector('input[name="hasHeaders"]:checked').value === 'yes';
    
    var mockColumns = hasHeaders 
        ? ['Mobile', 'First Name', 'Last Name', 'Email', 'Company']
        : ['Column A', 'Column B', 'Column C', 'Column D', 'Column E'];
    
    var mockSamples = ['7712345678', 'John', 'Smith', 'john@example.com', 'Acme Ltd'];
    
    var tbody = document.getElementById('columnMappingBody');
    tbody.innerHTML = '';
    
    var mappingOptions = `
        <option value="">-- Do not import --</option>
        <option value="mobile">Mobile Number *</option>
        <option value="first_name">First Name</option>
        <option value="last_name">Last Name</option>
        <option value="email">Email</option>
        <option value="custom">Custom Field</option>
    `;
    
    mockColumns.forEach(function(col, idx) {
        var autoMap = '';
        var colLower = col.toLowerCase();
        if (colLower.includes('mobile') || colLower.includes('phone') || colLower.includes('msisdn')) autoMap = 'mobile';
        else if (colLower.includes('first')) autoMap = 'first_name';
        else if (colLower.includes('last') || colLower.includes('surname')) autoMap = 'last_name';
        else if (colLower.includes('email')) autoMap = 'email';
        
        var row = document.createElement('tr');
        row.innerHTML = `
            <td><strong>${col}</strong></td>
            <td class="text-muted small">${mockSamples[idx] || ''}</td>
            <td>
                <div class="d-flex gap-2 align-items-center">
                    <select class="form-select form-select-sm column-mapping" data-column="${idx}" onchange="handleMappingChange(this)">
                        ${mappingOptions}
                    </select>
                    <input type="text" class="form-control form-control-sm custom-field-name d-none" data-column="${idx}" placeholder="Field name" style="width: 120px;">
                </div>
            </td>
        `;
        tbody.appendChild(row);
        
        if (autoMap) {
            row.querySelector('select').value = autoMap;
        }
    });
    
    if (mockSamples[0] && mockSamples[0].startsWith('7') && mockSamples[0].length >= 10) {
        document.getElementById('excelZeroWarning').classList.remove('d-none');
    } else {
        document.getElementById('excelZeroWarning').classList.add('d-none');
    }
}

function handleMappingChange(select) {
    var colIdx = select.dataset.column;
    var customInput = document.querySelector('.custom-field-name[data-column="' + colIdx + '"]');
    
    if (select.value === 'custom') {
        customInput.classList.remove('d-none');
        customInput.focus();
    } else {
        customInput.classList.add('d-none');
        customInput.value = '';
    }
}

function setExcelCorrection(apply) {
    document.getElementById('excelCorrectionApplied').value = apply ? 'yes' : 'no';
    var content = document.getElementById('excelZeroWarningContent');
    content.innerHTML = `
        <i class="fas fa-check-circle text-success me-2"></i>
        <strong>${apply ? 'UK number conversion will be applied' : 'Numbers will be left as-is'}</strong>
        <button type="button" class="btn btn-sm btn-link" onclick="resetExcelCorrection()">Change</button>
    `;
}

function resetExcelCorrection() {
    document.getElementById('excelCorrectionApplied').value = '';
    document.getElementById('excelZeroWarningContent').innerHTML = `
        <i class="fas fa-exclamation-triangle me-2"></i>
        <strong>Excel Number Detection</strong>
        <p class="mb-2 mt-2">We've detected mobile numbers starting with '7'. This often occurs when Excel removes the leading zero from UK mobile numbers.</p>
        <p class="mb-2">Should these be treated as UK numbers and converted to international format (+447...)?</p>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-primary" onclick="setExcelCorrection(true)">
                <i class="fas fa-check me-1"></i> Yes, convert to UK format
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setExcelCorrection(false)">
                <i class="fas fa-times me-1"></i> No, leave as-is
            </button>
        </div>
    `;
}

function validateMappings() {
    var hasMobile = false;
    var customFieldsValid = true;
    
    document.querySelectorAll('.column-mapping').forEach(function(select) {
        if (select.value === 'mobile') hasMobile = true;
        
        if (select.value === 'custom') {
            var colIdx = select.dataset.column;
            var customInput = document.querySelector('.custom-field-name[data-column="' + colIdx + '"]');
            if (!customInput.value.trim()) {
                customFieldsValid = false;
                customInput.classList.add('is-invalid');
            } else {
                customInput.classList.remove('is-invalid');
            }
        }
    });
    
    if (!hasMobile) {
        alert('Please map at least one column to Mobile Number.');
        return false;
    }
    
    if (!customFieldsValid) {
        alert('Please provide a name for all custom fields.');
        return false;
    }
    
    var excelWarning = document.getElementById('excelZeroWarning');
    if (!excelWarning.classList.contains('d-none') && document.getElementById('excelCorrectionApplied').value === '') {
        alert('Please confirm the Excel number correction option.');
        return false;
    }
    
    return true;
}

function simulateValidation() {
    var totalRows = Math.floor(Math.random() * 500) + 100;
    var uniqueNumbers = totalRows - Math.floor(Math.random() * 20);
    var invalidCount = Math.floor(Math.random() * 10);
    var validNumbers = uniqueNumbers - invalidCount;
    
    document.getElementById('statTotalRows').textContent = totalRows;
    document.getElementById('statUniqueNumbers').textContent = uniqueNumbers;
    document.getElementById('statValidNumbers').textContent = validNumbers;
    document.getElementById('statInvalidNumbers').textContent = invalidCount;
    
    var indicators = document.getElementById('importIndicators');
    indicators.innerHTML = '';
    
    if (document.getElementById('excelCorrectionApplied').value === 'yes') {
        indicators.innerHTML += '<span class="badge bg-info me-2"><i class="fas fa-sync-alt me-1"></i> Excel correction applied</span>';
    }
    indicators.innerHTML += '<span class="badge bg-secondary"><i class="fas fa-globe me-1"></i> UK format normalized</span>';
    
    if (invalidCount > 0) {
        document.getElementById('invalidRowsSection').classList.remove('d-none');
        var tbody = document.getElementById('invalidRowsBody');
        tbody.innerHTML = '';
        
        var reasons = ['Invalid format', 'Too short', 'Contains letters', 'Not a mobile number'];
        for (var i = 0; i < invalidCount; i++) {
            var row = document.createElement('tr');
            row.innerHTML = `
                <td>${Math.floor(Math.random() * totalRows) + 1}</td>
                <td class="text-muted">123ABC${i}</td>
                <td><span class="badge bg-danger">${reasons[i % reasons.length]}</span></td>
            `;
            tbody.appendChild(row);
        }
    } else {
        document.getElementById('invalidRowsSection').classList.add('d-none');
    }
    
    importValidationResults = { totalRows, uniqueNumbers, validNumbers, invalidCount };
}

function downloadInvalidRows() {
    var csvContent = 'Row,Original Value,Reason\n';
    document.querySelectorAll('#invalidRowsBody tr').forEach(function(row) {
        var cells = row.querySelectorAll('td');
        csvContent += `"${cells[0].textContent}","${cells[1].textContent}","${cells[2].textContent}"\n`;
    });
    
    var blob = new Blob([csvContent], { type: 'text/csv' });
    var url = window.URL.createObjectURL(blob);
    var a = document.createElement('a');
    a.href = url;
    a.download = 'invalid_rows_' + new Date().toISOString().slice(0,10) + '.csv';
    a.click();
    window.URL.revokeObjectURL(url);
}

function confirmImport() {
    if (!document.getElementById('confirmMappings').checked || !document.getElementById('confirmRules').checked) {
        alert('Please confirm both settings before importing.');
        return;
    }
    
    console.log('TODO: Implement actual import with streaming/chunked processing');
    console.log('TODO: API endpoint: POST /api/contacts/import');
    console.log('TODO: Log user confirmations and upload metadata');
    
    var validCount = importValidationResults ? importValidationResults.validNumbers : 0;
    document.getElementById('importCompleteMessage').textContent = 
        'Successfully imported ' + validCount + ' contacts.';
    
    showStep(4);
}

document.getElementById('importContactsModal').addEventListener('hidden.bs.modal', function() {
    importCurrentStep = 1;
    importFileData = null;
    importMappings = {};
    importValidationResults = null;
    clearImportFile();
    showStep(1);
    document.getElementById('excelZeroWarning').classList.add('d-none');
    document.getElementById('invalidRowsSection').classList.add('d-none');
    document.getElementById('confirmMappings').checked = true;
    document.getElementById('confirmRules').checked = true;
});
</script>
@endsection
