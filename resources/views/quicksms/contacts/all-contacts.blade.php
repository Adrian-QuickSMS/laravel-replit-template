@extends('layouts.quicksms')

@section('title', 'All Contacts')

@push('styles')
<style>
/* API Integration code blocks - black background with white text */
#contacts-api code.bg-light,
#contacts-api pre.bg-light {
    background-color: #1e1e1e !important;
    color: #f8f8f2 !important;
}
/* API Integration info box - pastel purple with black text and purple icon */
#contacts-api .alert-info {
    background-color: rgba(111, 66, 193, 0.08) !important;
    border: 1px solid rgba(111, 66, 193, 0.2) !important;
    color: #1f2937 !important;
}
#contacts-api .alert-info i {
    color: #6f42c1 !important;
}
/* Success and Error modals must appear on top of everything */
#successModal, #errorModal {
    z-index: 10060 !important;
}
#successModal + .modal-backdrop, #errorModal + .modal-backdrop {
    z-index: 10055 !important;
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
.contacts-table-container {
    background: #fff;
    border-radius: 0.75rem;
    border: 1px solid #e9ecef;
    overflow: visible;
}
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
@keyframes slideIn {
    from { opacity: 0; transform: translateX(100px); }
    to { opacity: 1; transform: translateX(0); }
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
                                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addFieldModal">
                                    <i class="fas fa-columns me-1"></i> Add Field
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

                    <div id="bulkActionBar" class="d-none mb-3 rounded p-3" style="background-color: #f0ebf8; border: 1px solid #d4c8e8;">
                        <div class="d-flex justify-content-between align-items-center flex-wrap">
                            <span style="color: #6b5b95;"><strong id="selectedCount">0</strong> contact(s) selected</span>
                            <div class="d-flex gap-2 flex-wrap mt-2 mt-md-0">
                                <button type="button" class="btn btn-sm" style="background-color: #fff; border: 1px solid #6b5b95; color: #6b5b95;" onmouseover="this.style.backgroundColor='#e8e0f0'" onmouseout="this.style.backgroundColor='#fff'" onclick="bulkAddToList()">
                                    <i class="fas fa-plus me-1"></i> Add to List
                                </button>
                                <button type="button" class="btn btn-sm" style="background-color: #fff; border: 1px solid #6b5b95; color: #6b5b95;" onmouseover="this.style.backgroundColor='#e8e0f0'" onmouseout="this.style.backgroundColor='#fff'" onclick="bulkRemoveFromList()">
                                    <i class="fas fa-minus me-1"></i> Remove from List
                                </button>
                                <button type="button" class="btn btn-sm" style="background-color: #fff; border: 1px solid #6b5b95; color: #6b5b95;" onmouseover="this.style.backgroundColor='#e8e0f0'" onmouseout="this.style.backgroundColor='#fff'" onclick="bulkAddTags()">
                                    <i class="fas fa-tag me-1"></i> Add Tags
                                </button>
                                <button type="button" class="btn btn-sm" style="background-color: #fff; border: 1px solid #6b5b95; color: #6b5b95;" onmouseover="this.style.backgroundColor='#e8e0f0'" onmouseout="this.style.backgroundColor='#fff'" onclick="bulkRemoveTags()">
                                    <i class="fas fa-times me-1"></i> Remove Tags
                                </button>
                                <button type="button" class="btn btn-sm" style="background-color: #fff; border: 1px solid #6b5b95; color: #6b5b95;" onmouseover="this.style.backgroundColor='#e8e0f0'" onmouseout="this.style.backgroundColor='#fff'" onclick="bulkSendMessage()">
                                    <i class="fas fa-paper-plane me-1"></i> Send Message
                                </button>
                                <button type="button" class="btn btn-sm" style="background-color: #fff; border: 1px solid #6b5b95; color: #6b5b95;" onmouseover="this.style.backgroundColor='#e8e0f0'" onmouseout="this.style.backgroundColor='#fff'" onclick="openExportModal()">
                                    <i class="fas fa-download me-1"></i> Export
                                </button>
                                <button type="button" class="btn btn-sm" style="background-color: #fff; border: 1px solid #dc3545; color: #dc3545;" onmouseover="this.style.backgroundColor='#ffe6e6'" onmouseout="this.style.backgroundColor='#fff'" onclick="bulkDelete()">
                                    <i class="fas fa-trash me-1"></i> Delete
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="contacts-table-container">
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
                                <tr class="btn-reveal-trigger" data-contact-id="{{ $contact['id'] }}" data-first-name="{{ $contact['first_name'] }}" data-last-name="{{ $contact['last_name'] }}" data-mobile="{{ $contact['mobile'] }}" data-status="{{ $contact['status'] }}" data-list-scope="{{ $contact['list_scope'] ?? '' }}">
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
                                                        <i class="fas fa-eye me-2 text-dark"></i> View Details
                                                    </a>
                                                    <a class="dropdown-item" href="#!" onclick="editContact({{ $contact['id'] }}); return false;">
                                                        <i class="fas fa-edit me-2 text-dark"></i> Edit
                                                    </a>
                                                    <a class="dropdown-item" href="#!" onclick="sendMessage({{ $contact['id'] }}); return false;">
                                                        <i class="fas fa-paper-plane me-2 text-dark"></i> Send Message
                                                    </a>
                                                    <a class="dropdown-item" href="#!" onclick="viewTimeline({{ $contact['id'] }}); return false;">
                                                        <i class="fas fa-history me-2 text-dark"></i> Activity Timeline
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

<script src="{{ asset('js/contacts-service.js') }}"></script>
<script src="{{ asset('js/contact-timeline-service.js') }}"></script>
<script>
var contactsData = @json($contacts);
var customFieldDefinitions = [
    { id: 1, name: 'Company', slug: 'company', type: 'text', defaultValue: '' },
    { id: 2, name: 'Job Title', slug: 'job_title', type: 'text', defaultValue: '' }
];

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
function reloadContactsFromServer() {
    fetch('/api/contacts?per_page=500', { headers: _apiHeaders() })
        .then(_handleApiResponse)
        .then(function(result) {
            contactsData = result.data || [];
            renderContactsTable(contactsData);
        })
        .catch(function(err) { console.error('Failed to reload contacts:', err); });
}
function showSuccessToast(message) {
    if (typeof toastr !== 'undefined') {
        toastr.success(message);
    } else {
        var toast = document.createElement('div');
        toast.className = 'alert alert-success position-fixed';
        toast.style.cssText = 'top:20px;right:20px;z-index:99999;min-width:300px;box-shadow:0 4px 12px rgba(0,0,0,0.15);';
        toast.innerHTML = '<i class="fas fa-check-circle me-2"></i>' + message;
        document.body.appendChild(toast);
        setTimeout(function() { toast.remove(); }, 3000);
    }
}

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

    function getFilterDropdown(filterName) {
        return document.querySelector('.multiselect-dropdown[data-filter="' + filterName + '"]');
    }
    
    function getSelectedValues(filterName) {
        var dropdown = getFilterDropdown(filterName);
        if (!dropdown) return [];
        var checkboxes = dropdown.querySelectorAll('input[type="checkbox"]:checked');
        return Array.from(checkboxes).map(function(cb) { return cb.value; });
    }
    
    function updateDropdownLabel(filterName) {
        var dropdown = getFilterDropdown(filterName);
        if (!dropdown) return;
        var selected = getSelectedValues(filterName);
        var label = dropdown.querySelector('.dropdown-label');
        var defaultLabels = { statuses: 'All Statuses', tags: 'All Tags', lists: 'All Lists', sources: 'All Sources' };
        if (selected.length === 0) {
            label.textContent = defaultLabels[filterName] || 'All';
        } else if (selected.length === 1) {
            label.textContent = selected[0];
        } else {
            label.textContent = selected.length + ' selected';
        }
    }
    
    document.querySelectorAll('.multiselect-dropdown').forEach(function(dropdown) {
        dropdown.addEventListener('change', function(e) {
            if (e.target.type === 'checkbox') {
                var filterName = dropdown.getAttribute('data-filter');
                updateDropdownLabel(filterName);
            }
        });
        
        var selectAllBtn = dropdown.querySelector('.select-all-btn');
        var clearAllBtn = dropdown.querySelector('.clear-all-btn');
        
        if (selectAllBtn) {
            selectAllBtn.addEventListener('click', function(e) {
                e.preventDefault();
                dropdown.querySelectorAll('input[type="checkbox"]').forEach(function(cb) { cb.checked = true; });
                var filterName = dropdown.getAttribute('data-filter');
                updateDropdownLabel(filterName);
            });
        }
        
        if (clearAllBtn) {
            clearAllBtn.addEventListener('click', function(e) {
                e.preventDefault();
                dropdown.querySelectorAll('input[type="checkbox"]').forEach(function(cb) { cb.checked = false; });
                var filterName = dropdown.getAttribute('data-filter');
                updateDropdownLabel(filterName);
            });
        }
    });
    
    var btnApplyFilters = document.getElementById('btnApplyFilters');
    if (btnApplyFilters) {
        btnApplyFilters.addEventListener('click', function() {
            applyFilters();
            showToast('Filters applied successfully', 'success');
        });
    }
    
    var btnResetFilters = document.getElementById('btnResetFilters');
    if (btnResetFilters) {
        btnResetFilters.addEventListener('click', function() {
            searchInput.value = '';
            document.getElementById('filterCreatedDate').value = '';
            document.querySelectorAll('.multiselect-dropdown input[type="checkbox"]').forEach(function(cb) {
                cb.checked = false;
            });
            ['statuses', 'tags', 'lists', 'sources'].forEach(updateDropdownLabel);
            applyFilters();
            updateActiveFiltersDisplay();
            showToast('Filters reset', 'success');
        });
    }
    
    var btnClearAllFilters = document.getElementById('btnClearAllFilters');
    if (btnClearAllFilters) {
        btnClearAllFilters.addEventListener('click', function() {
            searchInput.value = '';
            document.getElementById('filterCreatedDate').value = '';
            document.querySelectorAll('.multiselect-dropdown input[type="checkbox"]').forEach(function(cb) {
                cb.checked = false;
            });
            ['statuses', 'tags', 'lists', 'sources'].forEach(updateDropdownLabel);
            applyFilters();
            updateActiveFiltersDisplay();
        });
    }
    
    function applyFilters() {
        var searchTerm = searchInput.value.toLowerCase();
        var selectedStatuses = getSelectedValues('statuses');
        var selectedTags = getSelectedValues('tags');
        var selectedLists = getSelectedValues('lists');
        var selectedSources = getSelectedValues('sources');
        var createdDateFilter = document.getElementById('filterCreatedDate').value;
        
        var filteredContacts = contactsData.filter(function(contact) {
            var searchMatch = searchTerm === '' || 
                (contact.first_name + ' ' + contact.last_name).toLowerCase().includes(searchTerm) ||
                contact.mobile.includes(searchTerm) ||
                contact.tags.some(function(t) { return t.toLowerCase().includes(searchTerm); }) ||
                contact.lists.some(function(l) { return l.toLowerCase().includes(searchTerm); });
            
            var statusMatch = selectedStatuses.length === 0 || selectedStatuses.includes(contact.status);
            
            var tagsMatch = selectedTags.length === 0 || selectedTags.some(function(tag) {
                return contact.tags.includes(tag);
            });
            
            var listsMatch = selectedLists.length === 0 || selectedLists.some(function(list) {
                return contact.lists.includes(list);
            });
            
            var sourceMatch = selectedSources.length === 0 || selectedSources.includes(contact.source);
            
            var dateMatch = true;
            if (createdDateFilter && contact.created_at) {
                dateMatch = contact.created_at.startsWith(createdDateFilter);
            }
            
            return searchMatch && statusMatch && tagsMatch && listsMatch && sourceMatch && dateMatch;
        });
        
        renderContactsTable(filteredContacts);
        updateActiveFiltersDisplay();
    }
    
    function updateActiveFiltersDisplay() {
        var container = document.getElementById('activeFiltersContainer');
        var chipsDiv = document.getElementById('activeFiltersChips');
        var chips = [];
        
        var selectedStatuses = getSelectedValues('statuses');
        var selectedTags = getSelectedValues('tags');
        var selectedLists = getSelectedValues('lists');
        var selectedSources = getSelectedValues('sources');
        
        selectedStatuses.forEach(function(val) {
            chips.push('<span class="filter-chip">Status: ' + val + ' <span class="remove-chip" data-filter="statuses" data-value="' + val + '">&times;</span></span>');
        });
        selectedTags.forEach(function(val) {
            chips.push('<span class="filter-chip">Tag: ' + val + ' <span class="remove-chip" data-filter="tags" data-value="' + val + '">&times;</span></span>');
        });
        selectedLists.forEach(function(val) {
            chips.push('<span class="filter-chip">List: ' + val + ' <span class="remove-chip" data-filter="lists" data-value="' + val + '">&times;</span></span>');
        });
        selectedSources.forEach(function(val) {
            chips.push('<span class="filter-chip">Source: ' + val + ' <span class="remove-chip" data-filter="sources" data-value="' + val + '">&times;</span></span>');
        });
        
        if (chips.length > 0) {
            chipsDiv.innerHTML = chips.join('');
            container.style.display = 'block';
            
            chipsDiv.querySelectorAll('.remove-chip').forEach(function(chip) {
                chip.addEventListener('click', function() {
                    var filterName = this.getAttribute('data-filter');
                    var value = this.getAttribute('data-value');
                    var dropdown = getFilterDropdown(filterName);
                    if (dropdown) {
                        var checkbox = dropdown.querySelector('input[type="checkbox"][value="' + value + '"]');
                        if (checkbox) {
                            checkbox.checked = false;
                            updateDropdownLabel(filterName);
                            applyFilters();
                        }
                    }
                });
            });
        } else {
            container.style.display = 'none';
            chipsDiv.innerHTML = '';
        }
    }

    document.querySelectorAll('.mobile-number').forEach(function(el) {
        el.style.cursor = 'pointer';
        el.title = 'Click to toggle masking';
        el.addEventListener('click', function() {
            var full = this.dataset.full;
            var masked = this.dataset.masked;
            this.textContent = this.textContent === masked ? full : masked;
        });
    });
});

function clearFilterByName(filterName, value) {
    var dropdown = document.querySelector('.multiselect-dropdown[data-filter="' + filterName + '"]');
    if (dropdown) {
        var checkbox = dropdown.querySelector('input[type="checkbox"][value="' + value + '"]');
        if (checkbox) checkbox.checked = false;
    }
}

function clearAllFilters() {
    document.querySelectorAll('.multiselect-dropdown input[type="checkbox"]').forEach(function(cb) { cb.checked = false; });
    document.getElementById('contactSearch').value = '';
    var createdDate = document.getElementById('filterCreatedDate');
    if (createdDate) createdDate.value = '';
    ['statuses', 'tags', 'lists', 'sources'].forEach(function(filterName) {
        var dropdown = document.querySelector('.multiselect-dropdown[data-filter="' + filterName + '"]');
        if (dropdown) {
            var label = dropdown.querySelector('.dropdown-label');
            var defaults = { statuses: 'All Statuses', tags: 'All Tags', lists: 'All Lists', sources: 'All Sources' };
            if (label) label.textContent = defaults[filterName] || 'All';
        }
    });
    var container = document.getElementById('activeFiltersContainer');
    if (container) container.style.display = 'none';
    var chipsDiv = document.getElementById('activeFiltersChips');
    if (chipsDiv) chipsDiv.innerHTML = '';
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
        <tr class="btn-reveal-trigger" data-contact-id="${contact.id}" data-first-name="${contact.firstName || ''}" data-last-name="${contact.lastName || ''}" data-mobile="${contact.mobile || ''}" data-status="${contact.status || 'active'}" data-list-scope="${contact.listScope || ''}">
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
                                <i class="fas fa-eye me-2 text-dark"></i> View Details
                            </a>
                            <a class="dropdown-item" href="#!" onclick="editContact(${contact.id}); return false;">
                                <i class="fas fa-edit me-2 text-dark"></i> Edit
                            </a>
                            <a class="dropdown-item" href="#!" onclick="sendMessage(${contact.id}); return false;">
                                <i class="fas fa-paper-plane me-2 text-dark"></i> Send Message
                            </a>
                            <a class="dropdown-item" href="#!" onclick="viewTimeline(${contact.id}); return false;">
                                <i class="fas fa-history me-2 text-dark"></i> Activity Timeline
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
    var contact = contactsData.find(c => c.id === id);
    if (!contact) return;
    
    document.getElementById('viewContactName').textContent = contact.first_name + ' ' + contact.last_name;
    document.getElementById('viewContactInitials').textContent = contact.initials;
    document.getElementById('viewContactMobile').textContent = contact.mobile;
    document.getElementById('viewContactEmail').textContent = contact.email || 'Not provided';
    document.getElementById('viewContactStatus').innerHTML = contact.status === 'active' 
        ? '<span class="badge" style="background-color: #d4edda; color: #155724;">Active</span>' 
        : '<span class="badge" style="background-color: #f8d7da; color: #721c24;">Opted Out</span>';
    document.getElementById('viewContactSource').textContent = contact.source;
    document.getElementById('viewContactCreated').textContent = contact.created_at;
    
    var tagsHtml = contact.tags.length > 0 
        ? contact.tags.map(t => '<span class="badge me-1" style="background-color: #e8f4fd; color: #0c5460; border: 1px solid #bee5eb;">' + t + '</span>').join('') 
        : '<span class="text-muted">No tags</span>';
    document.getElementById('viewContactTags').innerHTML = tagsHtml;
    
    var listsHtml = contact.lists.length > 0 
        ? contact.lists.map(l => '<span class="badge me-1" style="background-color: #f0ebf8; color: #6c5ce7;">' + l + '</span>').join('') 
        : '<span class="text-muted">No lists</span>';
    document.getElementById('viewContactLists').innerHTML = listsHtml;
    
    var modal = new bootstrap.Modal(document.getElementById('viewContactModal'));
    modal.show();
}

function editContact(id) {
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
    var contact = contactsData.find(function(c) { return c.id === id; });
    if (!contact) {
        alert('Contact not found');
        return;
    }
    
    sessionStorage.setItem('sendMessageRecipients', JSON.stringify({
        contactIds: [id],
        contactNames: [contact.name],
        contactMobiles: [contact.mobile],
        source: 'contacts'
    }));
    
    window.location.href = '{{ route("messages.send") }}?from=contacts&count=1';
}

function viewTimeline(id) {
    var contact = getContactById(id);
    if (!contact) {
        console.error('Contact not found:', id);
        return;
    }
    
    currentTimelineContact = contact;
    currentTimelineContactId = contact.id;
    msisdnRevealed = false;
    
    var contactFullName = ((contact.firstName || '') + ' ' + (contact.lastName || '')).trim() || 'Unknown contact';
    var maskedPhone = maskMsisdn(contact.mobile);
    var statusPillHtml = renderStatusPill(contact.status, contact.listScope);
    
    document.getElementById('timelineContactName').textContent = contactFullName;
    document.getElementById('timelineContactPhone').textContent = maskedPhone;
    document.getElementById('timelineContactNameModal').textContent = contactFullName;
    document.getElementById('timelineContactPhoneModal').textContent = maskedPhone;
    document.getElementById('timelineStatusPill').innerHTML = statusPillHtml;
    document.getElementById('timelineStatusPillModal').innerHTML = statusPillHtml;
    
    document.getElementById('revealMsisdnBtn').innerHTML = '<i class="fas fa-eye me-1"></i>Reveal';
    document.getElementById('revealMsisdnBtnModal').innerHTML = '<i class="fas fa-eye me-1"></i>Reveal';
    
    var timelineContainer = document.getElementById('timelineEvents');
    timelineContainer.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><p class="text-muted mt-2 mb-0">Loading timeline...</p></div>';
    
    var isMobile = window.innerWidth < 768;
    
    if (isMobile) {
        var modal = new bootstrap.Modal(document.getElementById('activityTimelineModal'));
        modal.show();
    } else {
        var offcanvas = new bootstrap.Offcanvas(document.getElementById('activityTimelineDrawer'));
        offcanvas.show();
    }
    
    initTimelineFilters();
    applyTimelineFilters();
}

function getContactById(id) {
    var row = document.querySelector('tr[data-contact-id="' + id + '"]');
    if (!row) return null;
    return {
        id: id,
        firstName: row.dataset.firstName || row.getAttribute('data-first-name') || '',
        lastName: row.dataset.lastName || row.getAttribute('data-last-name') || '',
        mobile: row.dataset.mobile || row.getAttribute('data-mobile') || '',
        status: row.dataset.status || row.getAttribute('data-status') || 'active',
        listScope: row.dataset.listScope || row.getAttribute('data-list-scope') || ''
    };
}

function maskMsisdn(msisdn) {
    if (!msisdn || msisdn.length < 6) return msisdn || '';
    var visible = msisdn.slice(0, 6);
    var hidden = msisdn.slice(6).replace(/\d/g, '*');
    return visible + hidden;
}

var currentTimelineContact = null;
var msisdnRevealed = false;

function revealMsisdn() {
    if (!currentTimelineContact || !currentTimelineContact.mobile) return;
    
    if (msisdnRevealed) {
        document.getElementById('timelineContactPhone').textContent = maskMsisdn(currentTimelineContact.mobile);
        document.getElementById('timelineContactPhoneModal').textContent = maskMsisdn(currentTimelineContact.mobile);
        document.getElementById('revealMsisdnBtn').innerHTML = '<i class="fas fa-eye me-1"></i>Reveal';
        document.getElementById('revealMsisdnBtnModal').innerHTML = '<i class="fas fa-eye me-1"></i>Reveal';
        msisdnRevealed = false;
    } else {
        ContactTimelineService.revealMsisdn(currentTimelineContactId, 'User requested MSISDN reveal')
            .then(function(result) {
                console.log('[AUDIT] MSISDN revealed via service for contact:', currentTimelineContactId, 'at:', result.revealed_at);
                document.getElementById('timelineContactPhone').textContent = currentTimelineContact.mobile;
                document.getElementById('timelineContactPhoneModal').textContent = currentTimelineContact.mobile;
                document.getElementById('revealMsisdnBtn').innerHTML = '<i class="fas fa-eye-slash me-1"></i>Hide';
                document.getElementById('revealMsisdnBtnModal').innerHTML = '<i class="fas fa-eye-slash me-1"></i>Hide';
                msisdnRevealed = true;
            })
            .catch(function(error) {
                console.error('[Timeline] Failed to reveal MSISDN:', error);
            });
    }
}

function renderStatusPill(status, listScope) {
    var pillHtml = '';
    if (status === 'opted-out' || status === 'opted_out') {
        pillHtml = '<span class="badge badge-pastel-danger">Opted Out</span>';
        if (listScope) {
            pillHtml += ' <span class="badge badge-pastel-secondary ms-1">' + listScope + '</span>';
        }
    } else {
        pillHtml = '<span class="badge badge-pastel-success">Active</span>';
    }
    return pillHtml;
}

function deleteContact(id) {
    if (confirm('Are you sure you want to delete this contact?\n\nThis action cannot be undone.')) {
        fetch('/api/contacts/' + id, {
            method: 'DELETE',
            headers: _apiHeaders()
        })
        .then(_handleApiResponse)
        .then(function(result) {
            reloadContactsFromServer();
            showSuccessToast('Contact deleted successfully');
        })
        .catch(function(err) {
            alert('Failed to delete contact: ' + (err.message || 'Unknown error'));
        });
    }
}

function getSelectedContactIds() {
    var ids = [];
    document.querySelectorAll('.contact-checkbox:checked').forEach(cb => {
        var row = cb.closest('tr');
        if (row && row.dataset.contactId) {
            ids.push(row.dataset.contactId);
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
    console.log('[BulkAction] confirmBulkAddToList called');
    var ids = getSelectedContactIds();
    var count = ids.length;
    var listSelect = document.getElementById('bulkListSelect');
    var selectedList = listSelect ? listSelect.value : null;
    
    if (!selectedList) {
        showValidationError('Please select a list.');
        return;
    }
    
    // Get Bootstrap modal instance (don't create new one)
    var bulkModalEl = document.getElementById('bulkAddToListModal');
    var bulkModal = bootstrap.Modal.getInstance(bulkModalEl);
    
    // Wait for modal to be fully hidden before showing next modal
    bulkModalEl.addEventListener('hidden.bs.modal', function onHidden() {
        // Remove listener to prevent multiple calls
        bulkModalEl.removeEventListener('hidden.bs.modal', onHidden);
        
        console.log('[BulkAction] First modal hidden, showing processing modal');
        showProcessingModal('Adding contacts to list...');
        
        ContactsService.bulkAddToList(ids, selectedList).then(function(result) {
            console.log('[BulkAction] Service result:', result);
            
            // Get processing modal instance
            var processingEl = document.getElementById('processingModal');
            var processingModal = bootstrap.Modal.getInstance(processingEl);
            
            // Wait for processing modal to hide
            processingEl.addEventListener('hidden.bs.modal', function onProcessingHidden() {
                processingEl.removeEventListener('hidden.bs.modal', onProcessingHidden);
                
                // Now show result modal
                if (result.success) {
                    clearBulkSelection();
                    console.log('[BulkAction] Showing success modal');
                    showSuccessModal('Contacts Added', count + ' contact(s) have been added to "' + selectedList + '" successfully.');
                } else {
                    console.log('[BulkAction] Showing error modal');
                    showErrorModal('Action Failed', result.message || 'Failed to add contacts to list.');
                }
            }, { once: true });
            
            // Hide processing modal using Bootstrap API
            if (processingModal) {
                processingModal.hide();
            }
            
        }).catch(function(error) {
            console.error('[BulkAction] Error:', error);
            
            var processingEl = document.getElementById('processingModal');
            var processingModal = bootstrap.Modal.getInstance(processingEl);
            
            processingEl.addEventListener('hidden.bs.modal', function() {
                showErrorModal('Error', 'An unexpected error occurred. Please try again.');
            }, { once: true });
            
            if (processingModal) {
                processingModal.hide();
            }
        });
    }, { once: true });
    
    // Hide the first modal using Bootstrap API (not manual DOM manipulation)
    if (bulkModal) {
        bulkModal.hide();
    }
}

function bulkRemoveFromList() {
    var ids = getSelectedContactIds();
    var modal = new bootstrap.Modal(document.getElementById('bulkRemoveFromListModal'));
    document.getElementById('bulkRemoveFromListCount').textContent = ids.length;
    modal.show();
}

function confirmBulkRemoveFromList() {
    console.log('[BulkAction] confirmBulkRemoveFromList called');
    var ids = getSelectedContactIds();
    var count = ids.length;
    var listSelect = document.getElementById('bulkRemoveListSelect');
    var selectedList = listSelect ? listSelect.value : null;
    
    if (!selectedList) {
        showValidationError('Please select a list.');
        return;
    }
    
    var modalEl = document.getElementById('bulkRemoveFromListModal');
    var modal = bootstrap.Modal.getInstance(modalEl);
    
    // Wait for modal to fully hide before showing processing modal
    var executeAction = function() {
        console.log('[BulkAction] Modal hidden, showing processing modal');
        showProcessingModal('Removing contacts from list...');
        
        ContactsService.bulkRemoveFromList(ids, selectedList).then(function(result) {
            console.log('[BulkAction] Remove from list result:', result);
            if (result.success) {
                clearBulkSelection();
                hideProcessingModal(function() {
                    showSuccessModal('Contacts Removed', count + ' contact(s) have been removed from "' + selectedList + '" successfully.');
                });
            } else {
                hideProcessingModal(function() {
                    showErrorModal('Action Failed', result.message || 'Failed to remove contacts from list.');
                });
            }
        }).catch(function(error) {
            console.error('[BulkAction] Error:', error);
            hideProcessingModal(function() {
                showErrorModal('Error', 'An unexpected error occurred. Please try again.');
            });
        });
    };
    
    if (modal) {
        var handler = function() {
            modalEl.removeEventListener('hidden.bs.modal', handler);
            executeAction();
        };
        modalEl.addEventListener('hidden.bs.modal', handler);
        modal.hide();
    } else {
        executeAction();
    }
}

function bulkAddTags() {
    var ids = getSelectedContactIds();
    var modal = new bootstrap.Modal(document.getElementById('bulkAddTagsModal'));
    document.getElementById('bulkAddTagsCount').textContent = ids.length;
    modal.show();
}

function confirmBulkAddTags() {
    console.log('[BulkAction] confirmBulkAddTags called');
    var ids = getSelectedContactIds();
    var count = ids.length;
    var tagSelect = document.getElementById('bulkTagSelect');
    var selectedTags = tagSelect ? Array.from(tagSelect.selectedOptions).map(o => o.value) : [];
    var tagCount = selectedTags.length;
    console.log('[BulkAction] Selected tags:', selectedTags);
    
    if (selectedTags.length === 0) {
        showValidationError('Please select at least one tag.');
        return;
    }
    
    var modalEl = document.getElementById('bulkAddTagsModal');
    var modal = bootstrap.Modal.getInstance(modalEl);
    
    // Wait for modal to fully hide before showing processing modal
    var executeAction = function() {
        console.log('[BulkAction] Modal hidden, showing processing modal');
        showProcessingModal('Adding tags to contacts...');
        
        ContactsService.bulkAddTags(ids, selectedTags).then(function(result) {
            console.log('[BulkAction] Add tags result:', result);
            if (result.success) {
                clearBulkSelection();
                hideProcessingModal(function() {
                    showSuccessModal('Tags Added', tagCount + ' tag(s) have been added to ' + count + ' contact(s) successfully.');
                });
            } else {
                hideProcessingModal(function() {
                    showErrorModal('Action Failed', result.message || 'Failed to add tags.');
                });
            }
        }).catch(function(error) {
            console.error('[BulkAction] Error:', error);
            hideProcessingModal(function() {
                showErrorModal('Error', 'An unexpected error occurred. Please try again.');
            });
        });
    };
    
    if (modal) {
        var handler = function() {
            modalEl.removeEventListener('hidden.bs.modal', handler);
            executeAction();
        };
        modalEl.addEventListener('hidden.bs.modal', handler);
        modal.hide();
    } else {
        executeAction();
    }
}

function bulkRemoveTags() {
    var ids = getSelectedContactIds();
    var modal = new bootstrap.Modal(document.getElementById('bulkRemoveTagsModal'));
    document.getElementById('bulkRemoveTagsCount').textContent = ids.length;
    modal.show();
}

function confirmBulkRemoveTags() {
    console.log('[BulkAction] confirmBulkRemoveTags called');
    var ids = getSelectedContactIds();
    var count = ids.length;
    var tagSelect = document.getElementById('bulkRemoveTagSelect');
    var selectedTags = tagSelect ? Array.from(tagSelect.selectedOptions).map(o => o.value) : [];
    var tagCount = selectedTags.length;
    console.log('[BulkAction] Selected tags to remove:', selectedTags);
    
    if (selectedTags.length === 0) {
        showValidationError('Please select at least one tag.');
        return;
    }
    
    var modalEl = document.getElementById('bulkRemoveTagsModal');
    var modal = bootstrap.Modal.getInstance(modalEl);
    
    // Wait for modal to fully hide before showing processing modal
    var executeAction = function() {
        console.log('[BulkAction] Modal hidden, showing processing modal');
        showProcessingModal('Removing tags from contacts...');
        
        ContactsService.bulkRemoveTags(ids, selectedTags).then(function(result) {
            console.log('[BulkAction] Remove tags result:', result);
            if (result.success) {
                clearBulkSelection();
                hideProcessingModal(function() {
                    showSuccessModal('Tags Removed', tagCount + ' tag(s) have been removed from ' + count + ' contact(s) successfully.');
                });
            } else {
                hideProcessingModal(function() {
                    showErrorModal('Action Failed', result.message || 'Failed to remove tags.');
                });
            }
        }).catch(function(error) {
            console.error('[BulkAction] Error:', error);
            hideProcessingModal(function() {
                showErrorModal('Error', 'An unexpected error occurred. Please try again.');
            });
        });
    };
    
    if (modal) {
        var handler = function() {
            modalEl.removeEventListener('hidden.bs.modal', handler);
            executeAction();
        };
        modalEl.addEventListener('hidden.bs.modal', handler);
        modal.hide();
    } else {
        executeAction();
    }
}

function bulkDelete() {
    var ids = getSelectedContactIds();
    var names = getSelectedContactNames();
    
    document.getElementById('bulkDeleteCount').textContent = ids.length;
    var contactListHtml = names.slice(0, 10).map(function(name) {
        return '<li class="py-1">' + name + '</li>';
    }).join('');
    if (names.length > 10) {
        contactListHtml += '<li class="py-1 text-muted">...and ' + (names.length - 10) + ' more</li>';
    }
    document.getElementById('bulkDeleteContactList').innerHTML = contactListHtml;
    
    var modal = new bootstrap.Modal(document.getElementById('bulkDeleteModal'));
    modal.show();
}

function confirmBulkDelete() {
    var ids = getSelectedContactIds();
    var count = ids.length;
    
    var modalEl = document.getElementById('bulkDeleteModal');
    var modal = bootstrap.Modal.getInstance(modalEl);
    
    // Wait for modal to fully hide before showing processing modal
    var executeAction = function() {
        console.log('[BulkAction] Modal hidden, showing processing modal');
        showProcessingModal('Deleting contacts...');
        
        ContactsService.bulkDelete(ids).then(function(result) {
            if (result.success) {
                clearBulkSelection();
                hideProcessingModal(function() {
                    showSuccessModal('Contacts Deleted', count + ' contact(s) have been deleted successfully.');
                });
            } else {
                hideProcessingModal(function() {
                    showErrorModal('Error', result.message || 'Failed to delete contacts.');
                });
            }
        }).catch(function(error) {
            console.error('[ContactsService] Error:', error);
            hideProcessingModal(function() {
                showErrorModal('Error', 'An unexpected error occurred. Please try again.');
            });
        });
    };
    
    if (modal) {
        var handler = function() {
            modalEl.removeEventListener('hidden.bs.modal', handler);
            executeAction();
        };
        modalEl.addEventListener('hidden.bs.modal', handler);
        modal.hide();
    } else {
        executeAction();
    }
}

function clearBulkSelection() {
    document.querySelectorAll('.contact-checkbox:checked').forEach(cb => cb.checked = false);
    document.getElementById('checkAll').checked = false;
    document.getElementById('bulkActionBar').classList.add('d-none');
    document.getElementById('selectedCount').textContent = '0';
}

function cleanupModalBackdrops() {
    console.log('[Modal] Cleaning up backdrops...');
    document.querySelectorAll('.modal-backdrop').forEach(function(backdrop) {
        backdrop.remove();
    });
    document.body.classList.remove('modal-open');
    document.body.style.overflow = '';
    document.body.style.paddingRight = '';
}

function showProcessingModal(message) {
    console.log('[Modal] showProcessingModal called:', message);
    document.getElementById('processingMessage').textContent = message || 'Processing...';
    
    var processingEl = document.getElementById('processingModal');
    
    // Dispose old instance if exists to ensure clean state
    var existingInstance = bootstrap.Modal.getInstance(processingEl);
    if (existingInstance) {
        existingInstance.dispose();
    }
    
    // Create fresh instance
    window.processingModal = new bootstrap.Modal(processingEl, { 
        backdrop: 'static', 
        keyboard: false 
    });
    window.processingModal.show();
    console.log('[Modal] Processing modal shown');
}

function hideProcessingModal(callback) {
    console.log('[Modal] hideProcessingModal called, has callback:', !!callback);
    
    var processingEl = document.getElementById('processingModal');
    var processingModal = bootstrap.Modal.getInstance(processingEl);
    
    if (processingModal) {
        if (callback) {
            // Wait for modal to fully hide before calling callback
            processingEl.addEventListener('hidden.bs.modal', function onHidden() {
                processingEl.removeEventListener('hidden.bs.modal', onHidden);
                console.log('[Modal] Processing modal hidden, executing callback');
                callback();
            }, { once: true });
        }
        processingModal.hide();
    } else {
        console.log('[Modal] No processing modal instance, calling callback directly');
        if (callback) callback();
    }
}

function showSuccessModal(title, message) {
    console.log('[Modal] showSuccessModal called:', title, message);
    
    var successModalEl = document.getElementById('successModal');
    document.getElementById('successModalTitle').innerHTML = '<i class="fas fa-check-circle me-2"></i>' + title;
    document.getElementById('successModalMessage').textContent = message;
    
    // Dispose old instance if exists to ensure clean state
    var existingInstance = bootstrap.Modal.getInstance(successModalEl);
    if (existingInstance) {
        existingInstance.dispose();
    }
    
    // Create fresh instance
    window.successModal = new bootstrap.Modal(successModalEl);
    window.successModal.show();
    console.log('[Modal] Success modal shown');
}

function showErrorModal(title, message) {
    console.log('[Modal] showErrorModal called:', title, message);
    
    var errorModalEl = document.getElementById('errorModal');
    document.getElementById('errorModalTitle').innerHTML = '<i class="fas fa-exclamation-circle me-2"></i>' + title;
    document.getElementById('errorModalMessage').textContent = message;
    
    // Dispose old instance if exists to ensure clean state
    var existingInstance = bootstrap.Modal.getInstance(errorModalEl);
    if (existingInstance) {
        existingInstance.dispose();
    }
    
    // Create fresh instance
    window.errorModal = new bootstrap.Modal(errorModalEl);
    window.errorModal.show();
    console.log('[Modal] Error modal shown');
}

function showToast(message, type) {
    type = type || 'success';
    
    var existingContainer = document.getElementById('toastContainer');
    if (existingContainer) {
        existingContainer.remove();
    }
    
    var container = document.createElement('div');
    container.id = 'toastContainer';
    container.style.cssText = 'position: fixed; top: 80px; right: 20px; z-index: 999999; pointer-events: auto;';
    document.body.appendChild(container);
    
    var bgColor = type === 'success' ? '#6b5b95' : (type === 'error' ? '#dc3545' : '#6c757d');
    var icon = type === 'success' ? 'fa-check-circle' : (type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle');
    
    var toastId = 'toast_' + Date.now();
    var toastHtml = '<div id="' + toastId + '" style="background-color: ' + bgColor + '; color: white; padding: 12px 20px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.25); display: flex; align-items: center; gap: 10px; min-width: 280px; animation: slideIn 0.3s ease;">' +
        '<i class="fas ' + icon + '"></i>' +
        '<span style="flex: 1;">' + message + '</span>' +
        '<span style="cursor: pointer; opacity: 0.8;" onclick="this.parentElement.remove()">&times;</span>' +
        '</div>';
    
    container.insertAdjacentHTML('beforeend', toastHtml);
    
    console.log('[Toast] ' + type + ': ' + message);
    
    setTimeout(function() {
        var toast = document.getElementById(toastId);
        if (toast) {
            toast.style.transition = 'opacity 0.3s, transform 0.3s';
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100px)';
            setTimeout(function() { 
                if (toast.parentElement) toast.parentElement.remove();
            }, 300);
        }
    }, 5000);
}

function showValidationError(message) {
    document.getElementById('validationErrorMessage').textContent = message;
    if (!window.validationModal) {
        window.validationModal = new bootstrap.Modal(document.getElementById('validationModal'));
    }
    window.validationModal.show();
}

function bulkSendMessage() {
    var ids = getSelectedContactIds();
    var names = getSelectedContactNames();
    
    document.getElementById('sendMessageContactCount').textContent = ids.length;
    var contactListHtml = names.map(function(name) {
        return '<li class="py-1">' + name + '</li>';
    }).join('');
    document.getElementById('sendMessageContactList').innerHTML = contactListHtml;
    
    var modal = new bootstrap.Modal(document.getElementById('bulkSendMessageModal'));
    modal.show();
}

function confirmBulkSendMessage() {
    var ids = getSelectedContactIds();
    var names = getSelectedContactNames();
    var mobiles = getSelectedContactMobiles();
    
    var modal = bootstrap.Modal.getInstance(document.getElementById('bulkSendMessageModal'));
    modal.hide();
    
    sessionStorage.setItem('sendMessageRecipients', JSON.stringify({
        contactIds: ids,
        contactNames: names,
        contactMobiles: mobiles,
        source: 'contacts'
    }));
    
    window.location.href = '{{ route("messages.send") }}?from=contacts&count=' + ids.length;
}

function getSelectedContactMobiles() {
    var mobiles = [];
    document.querySelectorAll('.contact-checkbox:checked').forEach(function(cb) {
        var row = cb.closest('tr');
        if (row) {
            var mobileCell = row.querySelector('td:nth-child(3)');
            if (mobileCell) {
                mobiles.push(mobileCell.textContent.trim());
            }
        }
    });
    return mobiles;
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
        ContactsService.bulkDelete(ids)
            .then(function(result) {
                document.querySelectorAll('.contact-checkbox:checked').forEach(function(cb) { cb.checked = false; });
                document.getElementById('checkAll').checked = false;
                document.getElementById('bulkActionBar').classList.add('d-none');
                reloadContactsFromServer();
                showSuccessToast(result.message || 'Contacts deleted successfully');
            })
            .catch(function(err) {
                alert('Failed to delete contacts: ' + (err.message || 'Unknown error'));
            });
    }
}

document.addEventListener('DOMContentLoaded', function() {
    var fieldTypeSelect = document.getElementById('customFieldType');
    if (fieldTypeSelect) {
        fieldTypeSelect.addEventListener('change', function() {
            var dropdownContainer = document.getElementById('dropdownOptionsContainer');
            if (this.value === 'dropdown') {
                dropdownContainer.classList.remove('d-none');
            } else {
                dropdownContainer.classList.add('d-none');
            }
        });
    }
});

function saveCustomField() {
    var fieldName = document.getElementById('customFieldName').value.trim();
    var fieldType = document.getElementById('customFieldType').value;
    var fieldDefault = document.getElementById('customFieldDefault').value.trim();
    var fieldRequired = document.getElementById('customFieldRequired').checked;
    var dropdownOptions = document.getElementById('dropdownOptions').value.trim();
    
    if (!fieldName) {
        showToast('Please enter a field name.', 'error');
        return;
    }
    
    if (fieldType === 'dropdown' && !dropdownOptions) {
        showToast('Please enter at least one dropdown option.', 'error');
        return;
    }
    
    var fieldData = {
        name: fieldName,
        type: fieldType,
        default_value: fieldDefault,
        required: fieldRequired,
        options: fieldType === 'dropdown' ? dropdownOptions.split('\n').filter(o => o.trim()) : []
    };
    
    console.log('[CustomFields] Saving field:', fieldData);
    
    var modal = bootstrap.Modal.getInstance(document.getElementById('addFieldModal'));
    modal.hide();
    
    document.getElementById('addFieldForm').reset();
    document.getElementById('dropdownOptionsContainer').classList.add('d-none');
    
    showToast('Custom field "' + fieldName + '" created successfully!', 'success');
    
    console.log('TODO: Save custom field to backend\n- API endpoint: POST /api/contacts/custom-fields\n- Payload:', JSON.stringify(fieldData));
}
</script>

<div class="modal fade" id="addFieldModal" tabindex="-1" aria-labelledby="addFieldModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-white border-bottom">
                <h5 class="modal-title text-dark" id="addFieldModalLabel"><i class="fas fa-columns me-2 text-dark"></i>Add Custom Field</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addFieldForm">
                    <div class="mb-3">
                        <label class="form-label">Field Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="customFieldName" placeholder="e.g., Company Name, Birthday, Loyalty ID" required>
                        <div class="form-text">This name will appear as a column header in your contacts table.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Field Type <span class="text-danger">*</span></label>
                        <select class="form-select" id="customFieldType">
                            <option value="text">Text</option>
                            <option value="number">Number</option>
                            <option value="date">Date</option>
                            <option value="email">Email</option>
                            <option value="url">URL</option>
                            <option value="dropdown">Dropdown (Single Select)</option>
                        </select>
                    </div>
                    <div class="mb-3 d-none" id="dropdownOptionsContainer">
                        <label class="form-label">Dropdown Options</label>
                        <textarea class="form-control" id="dropdownOptions" rows="3" placeholder="Enter each option on a new line"></textarea>
                        <div class="form-text">One option per line. These will be the choices available in the dropdown.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Default Value (Optional)</label>
                        <input type="text" class="form-control" id="customFieldDefault" placeholder="Leave blank for no default">
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="customFieldRequired">
                        <label class="form-check-label" for="customFieldRequired">Make this field required</label>
                    </div>
                </form>
                
                <div class="rounded p-3" style="background-color: #f0ebf8;">
                    <i class="fas fa-info-circle me-2 text-dark"></i>
                    <strong class="text-dark">Note:</strong> <span class="text-dark">Custom fields can be used in message personalisation and for filtering contacts.</span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" onclick="saveCustomField()">
                    <i class="fas fa-save me-1"></i> Save Field
                </button>
            </div>
        </div>
    </div>
</div>

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
                        <div class="card border-0" style="background-color: #f8f9fa;">
                            <div class="card-body">
                                <small class="text-muted d-block">Mobile Number</small>
                                <strong class="text-dark" id="viewContactMobile"></strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0" style="background-color: #f8f9fa;">
                            <div class="card-body">
                                <small class="text-muted d-block">Email</small>
                                <strong class="text-dark" id="viewContactEmail"></strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0" style="background-color: #f8f9fa;">
                            <div class="card-body">
                                <small class="text-muted d-block">Source</small>
                                <strong class="text-dark" id="viewContactSource"></strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0" style="background-color: #f8f9fa;">
                            <div class="card-body">
                                <small class="text-muted d-block">Created Date</small>
                                <strong class="text-dark" id="viewContactCreated"></strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="card border-0" style="background-color: #f8f9fa;">
                            <div class="card-body">
                                <small class="text-muted d-block mb-2">Tags</small>
                                <div id="viewContactTags"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="card border-0" style="background-color: #f8f9fa;">
                            <div class="card-body">
                                <small class="text-muted d-block mb-2">Lists</small>
                                <div id="viewContactLists"></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4 mb-0 p-3 rounded" style="background-color: #f0ebf8;">
                    <i class="fas fa-info-circle me-2 text-dark"></i>
                    <strong class="text-dark">Activity Timeline:</strong> <span class="text-dark">Campaign history, replies, and opt-out events will appear here when backend is implemented.</span>
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

    var cleanMobile = mobile.replace(/[\s\-]/g, '');
    var firstNameEl = document.getElementById('editContactFirstName');
    var lastNameEl = document.getElementById('editContactLastName');
    var emailEl = document.getElementById('editContactEmail');
    var statusEl = document.getElementById('editContactStatus');

    var payload = {
        mobile_number: cleanMobile,
        first_name: firstNameEl ? firstNameEl.value.trim() || null : null,
        last_name: lastNameEl ? lastNameEl.value.trim() || null : null,
        email: emailEl ? emailEl.value.trim() || null : null
    };

    var saveBtn = document.querySelector('#editContactModal .btn-primary');
    if (saveBtn) {
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving...';
    }

    fetch('/api/contacts/' + id, {
        method: 'PUT',
        headers: _apiHeaders(),
        body: JSON.stringify(payload)
    })
    .then(_handleApiResponse)
    .then(function(result) {
        var modal = bootstrap.Modal.getInstance(document.getElementById('editContactModal'));
        modal.hide();
        reloadContactsFromServer();
        showSuccessToast('Contact updated successfully');
    })
    .catch(function(err) {
        validationMsg.textContent = err.message || 'Failed to update contact. Please try again.';
        validationMsg.classList.remove('d-none');
    })
    .finally(function() {
        if (saveBtn) {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="fas fa-save me-1"></i> Save Changes';
        }
    });
}
</script>

<script>
function saveContact() {
    var form = document.getElementById('addContactForm');
    var mobile = document.getElementById('contactMobile').value.trim();
    var firstName = document.getElementById('contactFirstName').value.trim();
    var lastName = document.getElementById('contactLastName').value.trim();
    var validationMsg = document.getElementById('formValidationMessage');
    var emailField = document.getElementById('contactEmail');
    var dobField = document.getElementById('contactDob');
    var postcodeField = document.getElementById('contactPostcode');
    var cityField = document.getElementById('contactCity');
    var countryField = document.getElementById('contactCountry');

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

    var cleanMobile = mobile.replace(/[\s\-]/g, '');

    var payload = {
        mobile_number: cleanMobile,
        first_name: firstName || null,
        last_name: lastName || null,
        email: (emailField && emailField.value.trim()) || null,
        date_of_birth: (dobField && dobField.value) || null,
        postcode: (postcodeField && postcodeField.value.trim()) || null,
        city: (cityField && cityField.value.trim()) || null,
        country: (countryField && countryField.value) || null
    };

    var customData = {};
    customFieldDefinitions.forEach(function(field) {
        var el = document.getElementById('custom_' + field.slug);
        if (el && el.value) {
            customData[field.slug] = el.value;
        }
    });
    if (Object.keys(customData).length > 0) {
        payload.custom_data = customData;
    }

    var saveBtn = document.querySelector('#addContactModal .btn-primary');
    if (saveBtn) {
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving...';
    }

    fetch('/api/contacts', {
        method: 'POST',
        headers: _apiHeaders(),
        body: JSON.stringify(payload)
    })
    .then(_handleApiResponse)
    .then(function(result) {
        var modal = bootstrap.Modal.getInstance(document.getElementById('addContactModal'));
        modal.hide();
        form.reset();
        reloadContactsFromServer();
        showSuccessToast('Contact created successfully');
    })
    .catch(function(err) {
        validationMsg.textContent = err.message || 'Failed to save contact. Please try again.';
        validationMsg.classList.remove('d-none');
    })
    .finally(function() {
        if (saveBtn) {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="fas fa-save me-1"></i> Save Contact';
        }
    });
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

<div class="modal fade" id="bulkSendMessageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-white border-bottom">
                <h5 class="modal-title text-dark"><i class="fas fa-paper-plane me-2 text-dark"></i>Send Message</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Send message to <strong id="sendMessageContactCount">0</strong> contact(s):</p>
                <div class="border rounded p-2 mb-3" style="max-height: 200px; overflow-y: auto; background-color: #f8f9fa;">
                    <ul class="list-unstyled mb-0 small" id="sendMessageContactList">
                    </ul>
                </div>
                <div class="rounded p-3 mb-0" style="background-color: #f0ebf8;">
                    <i class="fas fa-info-circle me-2 text-primary"></i>
                    This will redirect to the Send Message screen with these contacts pre-selected as recipients.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" onclick="confirmBulkSendMessage()">
                    <i class="fas fa-paper-plane me-1"></i> Continue to Send Message
                </button>
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

<div class="modal fade" id="bulkDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-trash me-2"></i>Delete Contacts</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger mb-3">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Warning:</strong> This action cannot be undone.
                </div>
                <p>You are about to delete <strong id="bulkDeleteCount">0</strong> contact(s):</p>
                <div class="border rounded p-2 mb-3" style="max-height: 200px; overflow-y: auto; background-color: #f8f9fa;">
                    <ul class="list-unstyled mb-0 small" id="bulkDeleteContactList">
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger btn-sm" onclick="confirmBulkDelete()">
                    <i class="fas fa-trash me-1"></i> Delete Permanently
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="processingModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center py-4">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mb-0" id="processingMessage">Processing...</p>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-white border-bottom">
                <h5 class="modal-title text-dark" id="successModalTitle"><i class="fas fa-check-circle me-2 text-success"></i>Success</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="fas fa-check-circle text-success fa-3x mb-3"></i>
                <p class="mb-0" id="successModalMessage">Operation completed successfully.</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-sm" style="background-color: #6b5b95; color: white;" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="errorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="errorModalTitle"><i class="fas fa-times-circle me-2"></i>Error</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="fas fa-times-circle text-danger fa-3x mb-3"></i>
                <p class="mb-0" id="errorModalMessage">An error occurred.</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="validationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Validation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="fas fa-exclamation-triangle text-warning fa-3x mb-3"></i>
                <p class="mb-0" id="validationErrorMessage">Please check your input.</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-warning btn-sm" data-bs-dismiss="modal">OK</button>
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
                            <div class="rounded-circle d-inline-flex align-items-center justify-content-center import-step-circle active" style="width: 32px; height: 32px; background-color: #886CC0; color: #fff;" id="stepCircle1">1</div>
                            <div class="small mt-1">Upload</div>
                        </div>
                        <div class="text-center flex-fill">
                            <div class="rounded-circle d-inline-flex align-items-center justify-content-center import-step-circle" style="width: 32px; height: 32px; background-color: #fff; color: #886CC0; border: 2px solid #886CC0;" id="stepCircle2">2</div>
                            <div class="small mt-1">Map Columns</div>
                        </div>
                        <div class="text-center flex-fill">
                            <div class="rounded-circle d-inline-flex align-items-center justify-content-center import-step-circle" style="width: 32px; height: 32px; background-color: #fff; color: #886CC0; border: 2px solid #886CC0;" id="stepCircle3">3</div>
                            <div class="small mt-1">Review</div>
                        </div>
                        <div class="text-center flex-fill">
                            <div class="rounded-circle d-inline-flex align-items-center justify-content-center import-step-circle" style="width: 32px; height: 32px; background-color: #fff; color: #886CC0; border: 2px solid #886CC0;" id="stepCircle4">4</div>
                            <div class="small mt-1">Complete</div>
                        </div>
                    </div>
                </div>

                <div id="importStep1">
                    <h6 class="mb-3">Step 1: Upload File</h6>
                    <div class="border rounded p-4 text-center" id="dropZone" style="border-style: dashed !important; background-color: #f0ebf8; border-color: #886CC0 !important;">
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
                    <div class="small p-3 rounded" style="background-color: #f0ebf8; color: #6c5ce7;">
                        <i class="fas fa-info-circle me-1"></i>
                        Map your file columns to contact fields. <strong style="color: #886CC0;">Mobile Number</strong> <span class="text-dark">is required.</span>
                    </div>
                    
                    <div class="table-responsive mt-3">
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
                    <div id="excelZeroWarning" class="d-none p-3 rounded" style="background-color: #f0ebf8;">
                        <div id="excelZeroWarningContent">
                            <i class="fas fa-exclamation-triangle me-2" style="color: #886CC0;"></i>
                            <strong style="color: #886CC0;">Excel Number Detection</strong>
                            <p class="mb-2 mt-2 text-dark">We've detected mobile numbers starting with '7'. This often occurs when Excel removes the leading zero from UK mobile numbers.</p>
                            <p class="mb-2 text-dark">Should these be treated as UK numbers and converted to international format (+447...)?</p>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-sm text-white" style="background-color: #886CC0;" onclick="setExcelCorrection(true)">
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
                            <div class="card border-0" style="background-color: #f0ebf8;">
                                <div class="card-body text-center py-3">
                                    <div class="h3 mb-0 text-dark" id="statTotalRows">0</div>
                                    <div class="small text-dark">Total Rows</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-0" style="background-color: #f0ebf8;">
                                <div class="card-body text-center py-3">
                                    <div class="h3 mb-0 text-dark" id="statUniqueNumbers">0</div>
                                    <div class="small text-dark">Unique Numbers</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-0" style="background-color: #f0ebf8;">
                                <div class="card-body text-center py-3">
                                    <div class="h3 mb-0 text-dark" id="statValidNumbers">0</div>
                                    <div class="small text-dark">Valid Numbers</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-0" style="background-color: #f0ebf8;">
                                <div class="card-body text-center py-3">
                                    <div class="h3 mb-0 text-dark" id="statInvalidNumbers">0</div>
                                    <div class="small text-dark">Invalid Numbers</div>
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
                        <div class="small mt-3 p-3 rounded" style="background-color: #f0ebf8;">
                            <i class="fas fa-info-circle me-1 text-dark"></i>
                            <span class="text-dark">Your contacts are now available in the contact list and can be used for messaging.</span>
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
        var circle = document.getElementById('stepCircle' + i);
        circle.style.backgroundColor = '#fff';
        circle.style.color = '#886CC0';
        circle.style.border = '2px solid #886CC0';
    }
    
    document.getElementById('importStep' + step).classList.remove('d-none');
    for (var i = 1; i <= step; i++) {
        var circle = document.getElementById('stepCircle' + i);
        circle.style.backgroundColor = '#886CC0';
        circle.style.color = '#fff';
        circle.style.border = 'none';
    }
    
    document.getElementById('importBackBtn').classList.toggle('d-none', step === 1 || step === 4);
    document.getElementById('importNextBtn').classList.toggle('d-none', step >= 3);
    document.getElementById('importConfirmBtn').classList.toggle('d-none', step !== 3);
    document.getElementById('importDoneBtn').classList.toggle('d-none', step !== 4);
    document.getElementById('importCancelBtn').classList.toggle('d-none', step === 4);
}

function parseCSVLine(line) {
    line = line.trim();
    if (line.length >= 2 && line[0] === '"' && line[line.length - 1] === '"') {
        var inner = line.substring(1, line.length - 1);
        if (inner.indexOf('"') === -1) {
            line = inner;
        }
    }
    var result = [];
    var current = '';
    var inQuotes = false;
    for (var i = 0; i < line.length; i++) {
        var ch = line[i];
        if (inQuotes) {
            if (ch === '"' && i + 1 < line.length && line[i + 1] === '"') {
                current += '"';
                i++;
            } else if (ch === '"') {
                inQuotes = false;
            } else {
                current += ch;
            }
        } else {
            if (ch === '"') {
                inQuotes = true;
            } else if (ch === ',') {
                result.push(current.trim());
                current = '';
            } else {
                current += ch;
            }
        }
    }
    result.push(current.trim());
    return result;
}

function simulateColumnDetection() {
    if (!importFileData || !importFileData.file) return;

    var reader = new FileReader();
    reader.onload = function(e) {
        var text = e.target.result;
        var lines = text.split(/\r?\n/).filter(function(l) { return l.trim().length > 0; });
        if (lines.length === 0) return;

        var hasHeaders = document.querySelector('input[name="hasHeaders"]:checked').value === 'yes';
        var headerRow = parseCSVLine(lines[0]);
        var sampleRow = lines.length > 1 ? parseCSVLine(lines[1]) : headerRow;

        importFileData.parsedHeaders = headerRow;
        importFileData.parsedRows = lines.slice(hasHeaders ? 1 : 0).map(parseCSVLine);

        var columns = hasHeaders
            ? headerRow
            : headerRow.map(function(_, i) { return 'Column ' + String.fromCharCode(65 + i); });
        var samples = hasHeaders ? sampleRow : headerRow;

        var tbody = document.getElementById('columnMappingBody');
        tbody.innerHTML = '';

        var mappingOptions = '<option value="">-- Do not import --</option>' +
            '<option value="mobile">Mobile Number *</option>' +
            '<option value="first_name">First Name</option>' +
            '<option value="last_name">Last Name</option>' +
            '<option value="email">Email</option>' +
            '<option value="custom">Custom Field</option>';

        columns.forEach(function(col, idx) {
            var autoMap = '';
            var colLower = col.toLowerCase();
            if (colLower.includes('mobile') || colLower.includes('phone') || colLower.includes('msisdn') || colLower.includes('number')) autoMap = 'mobile';
            else if (colLower.includes('first')) autoMap = 'first_name';
            else if (colLower.includes('last') || colLower.includes('surname')) autoMap = 'last_name';
            else if (colLower.includes('email')) autoMap = 'email';

            var sampleVal = samples[idx] || '';
            var row = document.createElement('tr');
            row.innerHTML = '<td><strong>' + col + '</strong></td>' +
                '<td class="text-muted small">' + sampleVal + '</td>' +
                '<td><div class="d-flex gap-2 align-items-center">' +
                '<select class="form-select form-select-sm column-mapping" data-column="' + idx + '" onchange="handleMappingChange(this)">' +
                mappingOptions + '</select>' +
                '<input type="text" class="form-control form-control-sm custom-field-name d-none" data-column="' + idx + '" placeholder="Field name" style="width: 120px;">' +
                '</div></td>';
            tbody.appendChild(row);

            if (autoMap) {
                row.querySelector('select').value = autoMap;
            }
        });

        var firstSample = samples[0] || '';
        if (firstSample.match && firstSample.match(/^7\d{9,}$/)) {
            document.getElementById('excelZeroWarning').classList.remove('d-none');
        } else {
            document.getElementById('excelZeroWarning').classList.add('d-none');
        }
    };
    reader.readAsText(importFileData.file);
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
        <i class="fas fa-check-circle me-2" style="color: #886CC0;"></i>
        <strong style="color: #886CC0;">${apply ? 'UK number conversion will be applied' : 'Numbers will be left as-is'}</strong>
        <button type="button" class="btn btn-sm btn-link" style="color: #886CC0;" onclick="resetExcelCorrection()">Change</button>
    `;
}

function resetExcelCorrection() {
    document.getElementById('excelCorrectionApplied').value = '';
    document.getElementById('excelZeroWarningContent').innerHTML = `
        <i class="fas fa-exclamation-triangle me-2" style="color: #886CC0;"></i>
        <strong style="color: #886CC0;">Excel Number Detection</strong>
        <p class="mb-2 mt-2 text-dark">We've detected mobile numbers starting with '7'. This often occurs when Excel removes the leading zero from UK mobile numbers.</p>
        <p class="mb-2 text-dark">Should these be treated as UK numbers and converted to international format (+447...)?</p>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm text-white" style="background-color: #886CC0;" onclick="setExcelCorrection(true)">
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
    var rows = (importFileData && importFileData.parsedRows) ? importFileData.parsedRows : [];
    var mappings = {};
    document.querySelectorAll('.column-mapping').forEach(function(sel) {
        if (sel.value) mappings[sel.value] = parseInt(sel.dataset.column, 10);
    });

    var mobileIdx = typeof mappings.mobile === 'number' ? mappings.mobile : -1;
    var applyExcelCorrection = document.getElementById('excelCorrectionApplied').value === 'yes';
    var seenNumbers = {};
    var duplicateCount = 0;
    var invalidCount = 0;
    var invalidRows = [];
    var validNumbers = 0;

    rows.forEach(function(row, rowIdx) {
        var mobile = (mobileIdx >= 0 && row[mobileIdx]) ? row[mobileIdx].replace(/[\s\-\+]/g, '') : '';
        if (!mobile) return;

        if (applyExcelCorrection && mobile.match(/^7\d{9,}$/)) {
            mobile = '44' + mobile;
        }

        if (!mobile.match(/^\d{10,15}$/)) {
            invalidCount++;
            var reason = 'Invalid format';
            if (mobile.match(/[a-zA-Z]/)) reason = 'Contains letters';
            else if (mobile.length < 10) reason = 'Too short';
            invalidRows.push({ row: rowIdx + 1, value: row[mobileIdx], reason: reason });
            return;
        }

        if (seenNumbers[mobile]) {
            duplicateCount++;
            return;
        }
        seenNumbers[mobile] = true;
        validNumbers++;
    });

    var totalRows = rows.length;
    var uniqueNumbers = validNumbers;

    document.getElementById('statTotalRows').textContent = totalRows;
    document.getElementById('statUniqueNumbers').textContent = uniqueNumbers;
    document.getElementById('statValidNumbers').textContent = validNumbers;
    document.getElementById('statInvalidNumbers').textContent = invalidCount;

    var indicators = document.getElementById('importIndicators');
    indicators.innerHTML = '';
    if (applyExcelCorrection) {
        indicators.innerHTML += '<span class="badge me-2" style="background-color: #f0ebf8; color: #886CC0; border: 1px solid #886CC0;"><i class="fas fa-sync-alt me-1"></i> Excel correction applied</span>';
    }
    indicators.innerHTML += '<span class="badge" style="background-color: #f0ebf8; color: #886CC0; border: 1px solid #886CC0;"><i class="fas fa-globe me-1"></i> UK format normalized</span>';

    if (invalidRows.length > 0) {
        document.getElementById('invalidRowsSection').classList.remove('d-none');
        var tbody = document.getElementById('invalidRowsBody');
        tbody.innerHTML = '';
        invalidRows.forEach(function(item) {
            var row = document.createElement('tr');
            row.innerHTML = '<td>' + item.row + '</td>' +
                '<td class="text-muted">' + item.value + '</td>' +
                '<td><span class="badge" style="background-color: #ffe0e0; color: #dc3545;">' + item.reason + '</span></td>';
            tbody.appendChild(row);
        });
    } else {
        document.getElementById('invalidRowsSection').classList.add('d-none');
    }

    importValidationResults = { totalRows: totalRows, uniqueNumbers: uniqueNumbers, validNumbers: validNumbers, invalidCount: invalidCount };
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

    var rows = (importFileData && importFileData.parsedRows) ? importFileData.parsedRows : [];
    if (rows.length === 0) {
        alert('No data rows found to import.');
        return;
    }

    var mappings = {};
    document.querySelectorAll('.column-mapping').forEach(function(sel) {
        if (sel.value && sel.value !== 'custom') {
            mappings[sel.value] = parseInt(sel.dataset.column, 10);
        } else if (sel.value === 'custom') {
            var customName = document.querySelector('.custom-field-name[data-column="' + sel.dataset.column + '"]');
            if (customName && customName.value) {
                mappings['custom_' + sel.dataset.column] = { idx: parseInt(sel.dataset.column, 10), name: customName.value };
            }
        }
    });

    if (typeof mappings.mobile !== 'number') {
        alert('Mobile Number mapping is required.');
        return;
    }

    var applyExcelCorrection = document.getElementById('excelCorrectionApplied').value === 'yes';
    var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    var importBtn = document.getElementById('importConfirmBtn');
    importBtn.disabled = true;
    importBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Importing...';

    var contacts = [];
    var seenMobiles = {};
    rows.forEach(function(row) {
        var mobile = (row[mappings.mobile] || '').replace(/[\s\-\+]/g, '');
        if (!mobile || !mobile.match(/^\d{7,15}$/)) return;
        if (applyExcelCorrection && mobile.match(/^7\d{9,}$/)) {
            mobile = '44' + mobile;
        }
        if (seenMobiles[mobile]) return;
        seenMobiles[mobile] = true;

        var contact = { mobile_number: mobile };
        if (typeof mappings.first_name === 'number' && row[mappings.first_name]) contact.first_name = row[mappings.first_name];
        if (typeof mappings.last_name === 'number' && row[mappings.last_name]) contact.last_name = row[mappings.last_name];
        if (typeof mappings.email === 'number' && row[mappings.email]) contact.email = row[mappings.email];

        var customData = {};
        Object.keys(mappings).forEach(function(key) {
            if (key.startsWith('custom_')) {
                var m = mappings[key];
                if (row[m.idx]) customData[m.name] = row[m.idx];
            }
        });
        if (Object.keys(customData).length > 0) contact.custom_data = customData;

        contacts.push(contact);
    });

    if (contacts.length === 0) {
        alert('No valid contacts found to import.');
        importBtn.disabled = false;
        importBtn.innerHTML = '<i class="fas fa-check me-1"></i> Start Import';
        return;
    }

    var successCount = 0;
    var failCount = 0;
    var batchSize = 5;
    var idx = 0;

    function processBatch() {
        if (idx >= contacts.length) {
            importBtn.disabled = false;
            importBtn.innerHTML = '<i class="fas fa-check me-1"></i> Start Import';
            document.getElementById('importCompleteMessage').textContent =
                'Successfully imported ' + successCount + ' contacts.' +
                (failCount > 0 ? ' ' + failCount + ' failed (may already exist).' : '');
            showStep(4);
            return;
        }

        var batch = contacts.slice(idx, idx + batchSize);
        idx += batchSize;

        Promise.all(batch.map(function(c) {
            return fetch('/api/contacts', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify(c)
            }).then(function(resp) {
                if (resp.ok) { successCount++; }
                else { failCount++; }
            }).catch(function() { failCount++; });
        })).then(function() {
            importBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Importing... (' + Math.min(idx, contacts.length) + '/' + contacts.length + ')';
            processBatch();
        });
    }

    processBatch();
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

<!-- Activity Timeline Drawer (Desktop) -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="activityTimelineDrawer" style="width: 420px;">
    <div class="offcanvas-header border-bottom py-3">
        <div class="flex-grow-1">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <h6 class="offcanvas-title mb-0"><i class="fas fa-history me-2 text-primary"></i>Activity Timeline</h6>
            </div>
            <h5 class="mb-1 fw-semibold" id="timelineContactName">Unknown contact</h5>
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="text-muted small" id="timelineContactPhone">+44 7*** ***###</span>
                <button type="button" class="btn btn-link btn-sm p-0 text-primary" id="revealMsisdnBtn" onclick="revealMsisdn()" style="font-size: 0.75rem;">
                    <i class="fas fa-eye me-1"></i>Reveal
                </button>
            </div>
            <div id="timelineStatusPill">
                <span class="badge badge-pastel-success">Active</span>
            </div>
        </div>
        <button type="button" class="btn-close align-self-start" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-0">
        <!-- Filters Toggle -->
        <div class="px-3 pt-3">
            <button type="button" class="btn btn-outline-primary btn-sm w-100 d-flex align-items-center justify-content-center" data-bs-toggle="collapse" data-bs-target="#timelineFiltersPanel" style="background-color: #fff; border-color: #6b5b95; color: #6b5b95;">
                <i class="fas fa-filter me-1"></i> Filters
                <i class="fas fa-chevron-down ms-2"></i>
            </button>
        </div>
        
        <!-- Collapsible Filters Panel -->
        <div class="collapse px-3 pt-2" id="timelineFiltersPanel">
            <div class="border rounded p-3 mb-3" style="background-color: #fff; border-color: #e0e0e0 !important;">
                <!-- Date Range -->
                <div class="mb-3">
                    <label class="form-label small mb-1" style="color: #000;">Date Range</label>
                    <div class="row g-2">
                        <div class="col-6">
                            <input type="date" class="form-control form-control-sm" id="timelineDateFrom" style="border: 1px solid #ced4da;">
                        </div>
                        <div class="col-6">
                            <input type="date" class="form-control form-control-sm" id="timelineDateTo" style="border: 1px solid #ced4da;">
                        </div>
                    </div>
                </div>
                
                <!-- Event Type Multi-select -->
                <div class="mb-3">
                    <label class="form-label small mb-1" style="color: #000;">Event Type</label>
                    <div class="dropdown multiselect-dropdown w-100" data-filter="eventTypes">
                        <button class="btn btn-sm w-100 text-start dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" style="border: 1px solid #ced4da; background-color: #fff;">
                            <span class="dropdown-label" style="color: #6b5b95;">All Event Types</span>
                        </button>
                        <div class="dropdown-menu w-100 p-2" style="max-height: 200px; overflow-y: auto;">
                            <div class="form-check"><input class="form-check-input timeline-filter-check" type="checkbox" value="outbound" id="evtOutbound" checked><label class="form-check-label small" for="evtOutbound">Sent Message</label></div>
                            <div class="form-check"><input class="form-check-input timeline-filter-check" type="checkbox" value="inbound" id="evtInbound" checked><label class="form-check-label small" for="evtInbound">Received Message</label></div>
                            <div class="form-check"><input class="form-check-input timeline-filter-check" type="checkbox" value="lists" id="evtLists" checked><label class="form-check-label small" for="evtLists">Added/Removed from List</label></div>
                            <div class="form-check"><input class="form-check-input timeline-filter-check" type="checkbox" value="tags" id="evtTags" checked><label class="form-check-label small" for="evtTags">Added/Removed from Tag</label></div>
                            <div class="form-check"><input class="form-check-input timeline-filter-check" type="checkbox" value="optout" id="evtOptout" checked><label class="form-check-label small" for="evtOptout">Added/Removed from Optout</label></div>
                        </div>
                    </div>
                </div>
                
                <!-- Channel Multi-select -->
                <div class="mb-3">
                    <label class="form-label small mb-1" style="color: #000;">Channel</label>
                    <div class="dropdown multiselect-dropdown w-100" data-filter="channels">
                        <button class="btn btn-sm w-100 text-start dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" style="border: 1px solid #ced4da; background-color: #fff;">
                            <span class="dropdown-label" style="color: #6b5b95;">All Channels</span>
                        </button>
                        <div class="dropdown-menu w-100 p-2">
                            <div class="form-check"><input class="form-check-input timeline-filter-check" type="checkbox" value="sms" id="chSms" checked><label class="form-check-label small" for="chSms">SMS</label></div>
                            <div class="form-check"><input class="form-check-input timeline-filter-check" type="checkbox" value="rcs" id="chRcs" checked><label class="form-check-label small" for="chRcs">RCS</label></div>
                        </div>
                    </div>
                </div>
                
                <!-- Source Multi-select -->
                <div class="mb-3">
                    <label class="form-label small mb-1" style="color: #000;">Source</label>
                    <div class="dropdown multiselect-dropdown w-100" data-filter="sources">
                        <button class="btn btn-sm w-100 text-start dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" style="border: 1px solid #ced4da; background-color: #fff;">
                            <span class="dropdown-label" style="color: #6b5b95;">All Sources</span>
                        </button>
                        <div class="dropdown-menu w-100 p-2">
                            <div class="form-check"><input class="form-check-input timeline-filter-check" type="checkbox" value="campaign" id="srcCampaign" checked><label class="form-check-label small" for="srcCampaign">Campaign</label></div>
                            <div class="form-check"><input class="form-check-input timeline-filter-check" type="checkbox" value="inbox" id="srcInbox" checked><label class="form-check-label small" for="srcInbox">Inbox</label></div>
                            <div class="form-check"><input class="form-check-input timeline-filter-check" type="checkbox" value="api" id="srcApi" checked><label class="form-check-label small" for="srcApi">API</label></div>
                            <div class="form-check"><input class="form-check-input timeline-filter-check" type="checkbox" value="email-to-sms" id="srcEmailSms" checked><label class="form-check-label small" for="srcEmailSms">Email-to-SMS</label></div>
                            <div class="form-check"><input class="form-check-input timeline-filter-check" type="checkbox" value="system" id="srcSystem" checked><label class="form-check-label small" for="srcSystem">System</label></div>
                        </div>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="resetTimelineFilters()">
                        <i class="fas fa-undo me-1"></i> Reset
                    </button>
                    <button type="button" class="btn btn-sm" onclick="applyTimelineFilters()" style="background-color: #6b5b95; color: #fff; border-color: #6b5b95;">
                        <i class="fas fa-check me-1"></i> Apply Filters
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Timeline Events -->
        <div class="px-3 pb-3">
            <div id="timelineEvents">
                <p class="text-muted text-center py-4">Loading...</p>
            </div>
        </div>
    </div>
</div>

<!-- Activity Timeline Modal (Mobile) -->
<div class="modal fade" id="activityTimelineModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen-sm-down modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header border-bottom py-3">
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h6 class="modal-title mb-0"><i class="fas fa-history me-2 text-primary"></i>Activity Timeline</h6>
                    </div>
                    <h5 class="mb-1 fw-semibold" id="timelineContactNameModal">Unknown contact</h5>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="text-muted small" id="timelineContactPhoneModal">+44 7*** ***###</span>
                        <button type="button" class="btn btn-link btn-sm p-0 text-primary" id="revealMsisdnBtnModal" onclick="revealMsisdn()" style="font-size: 0.75rem;">
                            <i class="fas fa-eye me-1"></i>Reveal
                        </button>
                    </div>
                    <div id="timelineStatusPillModal">
                        <span class="badge badge-pastel-success">Active</span>
                    </div>
                </div>
                <button type="button" class="btn-close align-self-start" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <!-- Filters Toggle -->
                <div class="px-3 pt-3">
                    <button type="button" class="btn btn-outline-primary btn-sm w-100 d-flex align-items-center justify-content-center" data-bs-toggle="collapse" data-bs-target="#timelineFiltersPanelModal" style="background-color: #fff; border-color: #6b5b95; color: #6b5b95;">
                        <i class="fas fa-filter me-1"></i> Filters
                        <i class="fas fa-chevron-down ms-2"></i>
                    </button>
                </div>
                
                <!-- Collapsible Filters Panel -->
                <div class="collapse px-3 pt-2" id="timelineFiltersPanelModal">
                    <div class="border rounded p-3 mb-3" style="background-color: #fff; border-color: #e0e0e0 !important;">
                        <!-- Date Range -->
                        <div class="mb-3">
                            <label class="form-label small mb-1" style="color: #000;">Date Range</label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="date" class="form-control form-control-sm" id="timelineDateFromModal" style="border: 1px solid #ced4da;">
                                </div>
                                <div class="col-6">
                                    <input type="date" class="form-control form-control-sm" id="timelineDateToModal" style="border: 1px solid #ced4da;">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Event Type Multi-select -->
                        <div class="mb-3">
                            <label class="form-label small mb-1" style="color: #000;">Event Type</label>
                            <div class="dropdown multiselect-dropdown w-100" data-filter="eventTypesModal">
                                <button class="btn btn-sm w-100 text-start dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" style="border: 1px solid #ced4da; background-color: #fff;">
                                    <span class="dropdown-label" style="color: #6b5b95;">All Event Types</span>
                                </button>
                                <div class="dropdown-menu w-100 p-2" style="max-height: 200px; overflow-y: auto;">
                                    <div class="form-check"><input class="form-check-input timeline-filter-check-modal" type="checkbox" value="outbound" id="evtOutboundM" checked><label class="form-check-label small" for="evtOutboundM">Sent Message</label></div>
                                    <div class="form-check"><input class="form-check-input timeline-filter-check-modal" type="checkbox" value="inbound" id="evtInboundM" checked><label class="form-check-label small" for="evtInboundM">Received Message</label></div>
                                    <div class="form-check"><input class="form-check-input timeline-filter-check-modal" type="checkbox" value="lists" id="evtListsM" checked><label class="form-check-label small" for="evtListsM">Added/Removed from List</label></div>
                                    <div class="form-check"><input class="form-check-input timeline-filter-check-modal" type="checkbox" value="tags" id="evtTagsM" checked><label class="form-check-label small" for="evtTagsM">Added/Removed from Tag</label></div>
                                    <div class="form-check"><input class="form-check-input timeline-filter-check-modal" type="checkbox" value="optout" id="evtOptoutM" checked><label class="form-check-label small" for="evtOptoutM">Added/Removed from Optout</label></div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Channel Multi-select -->
                        <div class="mb-3">
                            <label class="form-label small mb-1" style="color: #000;">Channel</label>
                            <div class="dropdown multiselect-dropdown w-100" data-filter="channelsModal">
                                <button class="btn btn-sm w-100 text-start dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" style="border: 1px solid #ced4da; background-color: #fff;">
                                    <span class="dropdown-label" style="color: #6b5b95;">All Channels</span>
                                </button>
                                <div class="dropdown-menu w-100 p-2">
                                    <div class="form-check"><input class="form-check-input timeline-filter-check-modal" type="checkbox" value="sms" id="chSmsM" checked><label class="form-check-label small" for="chSmsM">SMS</label></div>
                                    <div class="form-check"><input class="form-check-input timeline-filter-check-modal" type="checkbox" value="rcs" id="chRcsM" checked><label class="form-check-label small" for="chRcsM">RCS</label></div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Source Multi-select -->
                        <div class="mb-3">
                            <label class="form-label small mb-1" style="color: #000;">Source</label>
                            <div class="dropdown multiselect-dropdown w-100" data-filter="sourcesModal">
                                <button class="btn btn-sm w-100 text-start dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" style="border: 1px solid #ced4da; background-color: #fff;">
                                    <span class="dropdown-label" style="color: #6b5b95;">All Sources</span>
                                </button>
                                <div class="dropdown-menu w-100 p-2">
                                    <div class="form-check"><input class="form-check-input timeline-filter-check-modal" type="checkbox" value="campaign" id="srcCampaignM" checked><label class="form-check-label small" for="srcCampaignM">Campaign</label></div>
                                    <div class="form-check"><input class="form-check-input timeline-filter-check-modal" type="checkbox" value="inbox" id="srcInboxM" checked><label class="form-check-label small" for="srcInboxM">Inbox</label></div>
                                    <div class="form-check"><input class="form-check-input timeline-filter-check-modal" type="checkbox" value="api" id="srcApiM" checked><label class="form-check-label small" for="srcApiM">API</label></div>
                                    <div class="form-check"><input class="form-check-input timeline-filter-check-modal" type="checkbox" value="email-to-sms" id="srcEmailSmsM" checked><label class="form-check-label small" for="srcEmailSmsM">Email-to-SMS</label></div>
                                    <div class="form-check"><input class="form-check-input timeline-filter-check-modal" type="checkbox" value="system" id="srcSystemM" checked><label class="form-check-label small" for="srcSystemM">System</label></div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="resetTimelineFilters()">
                                <i class="fas fa-undo me-1"></i> Reset
                            </button>
                            <button type="button" class="btn btn-sm" onclick="applyTimelineFilters()" style="background-color: #6b5b95; color: #fff; border-color: #6b5b95;">
                                <i class="fas fa-check me-1"></i> Apply Filters
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Timeline Events -->
                <div class="px-3 pb-3">
                    <div id="timelineEventsModal">
                        <p class="text-muted text-center py-4">Loading...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function initTimelineFilters() {
    var today = new Date();
    var ninetyDaysAgo = new Date(today.getTime() - (90 * 24 * 60 * 60 * 1000));
    
    var toDate = today.toISOString().split('T')[0];
    var fromDate = ninetyDaysAgo.toISOString().split('T')[0];
    
    document.getElementById('timelineDateFrom').value = fromDate;
    document.getElementById('timelineDateTo').value = toDate;
    document.getElementById('timelineDateFromModal').value = fromDate;
    document.getElementById('timelineDateToModal').value = toDate;
    
    document.querySelectorAll('.timeline-filter-check, .timeline-filter-check-modal').forEach(function(cb) {
        cb.checked = true;
    });
}

function getTimelineFilters() {
    var eventTypes = [];
    document.querySelectorAll('.timeline-filter-check[type="checkbox"]:checked').forEach(function(cb) {
        if (['outbound', 'inbound', 'delivery', 'lists', 'tags', 'optout', 'notes'].includes(cb.value)) {
            eventTypes.push(cb.value);
        }
    });
    
    var channels = [];
    document.querySelectorAll('#chSms:checked, #chRcs:checked').forEach(function(cb) {
        channels.push(cb.value);
    });
    
    var sources = [];
    document.querySelectorAll('#srcCampaign:checked, #srcInbox:checked, #srcApi:checked, #srcEmailSms:checked, #srcSystem:checked').forEach(function(cb) {
        sources.push(cb.value);
    });
    
    return {
        dateFrom: document.getElementById('timelineDateFrom').value,
        dateTo: document.getElementById('timelineDateTo').value,
        eventTypes: eventTypes,
        channels: channels,
        sources: sources
    };
}

var timelineLoadedCount = 0;
var timelinePageSize = 50;
var timelineTotalEvents = 0;
var timelineNextCursor = null;
var currentTimelineContactId = null;

function getSourcePillHtml(source) {
    var pillColors = {
        'campaign': 'badge-pastel-primary',
        'inbox': 'badge-pastel-info',
        'api': 'badge-pastel-warning',
        'email-to-sms': 'badge-pastel-success',
        'system': 'badge-pastel-secondary'
    };
    var pillLabels = {
        'campaign': 'Campaign',
        'inbox': 'Inbox',
        'api': 'API',
        'email-to-sms': 'Email-to-SMS',
        'system': 'System'
    };
    var colorClass = pillColors[source] || 'badge-pastel-secondary';
    var label = pillLabels[source] || 'System';
    return '<span class="badge ' + colorClass + ' me-2">' + label + '</span>';
}

function renderTimelineActions(actions) {
    if (!actions || actions.length === 0) return '';
    
    var html = '<div class="mt-2 pt-2 border-top">';
    actions.forEach(function(action) {
        if (action.type === 'link') {
            html += '<a href="' + action.url + '" class="btn btn-sm btn-outline-primary me-1" target="' + (action.target || '_self') + '">' +
                '<i class="fas ' + action.icon + ' me-1"></i>' + action.label +
            '</a>';
        }
    });
    html += '</div>';
    return html;
}

function renderTimelineEvents(events) {
    var html = '';
    
    events.forEach(function(event) {
        var eventId = 'timeline-evt-' + event.event_id;
        var ui = event._ui || {};
        var metadata = event.metadata || {};
        var actions = metadata.actions || [];
        
        var isBlocked = metadata.is_blocked === true;
        var iconColorClass = isBlocked ? 'danger' : (ui.color || 'secondary');
        
        html += '<div class="timeline-event-card border-bottom py-2">' +
            '<div class="d-flex align-items-start">' +
                '<div class="timeline-icon bg-' + iconColorClass + ' text-white rounded-circle d-flex align-items-center justify-content-center me-2 flex-shrink-0" style="width: 32px; height: 32px;">' +
                    '<i class="fas ' + (isBlocked ? 'fa-ban' : (ui.icon || 'fa-circle')) + '" style="font-size: 0.75rem;"></i>' +
                '</div>' +
                '<div class="flex-grow-1 min-width-0">' +
                    '<div class="d-flex justify-content-between align-items-center mb-1">' +
                        '<div class="d-flex align-items-center flex-wrap gap-1">' +
                            getSourcePillHtml(event.source_module) +
                            (metadata.channel_label ? '<span class="badge badge-pastel-secondary me-1">' + metadata.channel_label + '</span>' : '') +
                            '<span class="fw-medium small">' + (ui.title || event.event_type) + '</span>' +
                        '</div>' +
                        '<small class="text-muted flex-shrink-0 ms-2">' + (ui.formattedDate || '') + '</small>' +
                    '</div>' +
                    '<p class="mb-0 text-muted small text-truncate' + (isBlocked ? ' text-danger' : '') + '">' + (ui.summary || '') + '</p>' +
                    '<div class="mt-1">' +
                        '<a class="small text-primary text-decoration-none" data-bs-toggle="collapse" href="#' + eventId + '" role="button" aria-expanded="false">' +
                            '<i class="fas fa-chevron-down me-1" style="font-size: 0.6rem;"></i>Details' +
                        '</a>' +
                        '<div class="collapse mt-2" id="' + eventId + '">' +
                            '<div class="small rounded p-2" style="background-color: #f0ebf8;">' +
                                '<div class="mb-1" style="color: #000;"><strong>Actor:</strong> ' + (event.actor_name || 'System') + ' (' + event.actor_type + ')</div>' +
                                (ui.details || '') +
                                renderTimelineActions(actions) +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
            '</div>' +
        '</div>';
    });
    
    return html;
}

function applyTimelineFilters() {
    var filters = getTimelineFilters();
    console.log('[Timeline] Applying filters via ContactTimelineService:', filters);
    
    if (!currentTimelineContactId) {
        console.error('[Timeline] No contact ID set');
        return;
    }
    
    timelineLoadedCount = 0;
    timelineNextCursor = null;
    
    var timelineContainer = document.getElementById('timelineEvents');
    var timelineContainerModal = document.getElementById('timelineEventsModal');
    
    var loadingHtml = '<div class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary" role="status"></div><p class="text-muted mt-2 mb-0 small">Loading timeline...</p></div>';
    timelineContainer.innerHTML = loadingHtml;
    if (timelineContainerModal) timelineContainerModal.innerHTML = loadingHtml;
    
    ContactTimelineService.getContactTimeline(currentTimelineContactId, filters, { limit: timelinePageSize })
        .then(function(response) {
            timelineLoadedCount = response.returned;
            timelineTotalEvents = response.total;
            timelineNextCursor = response.cursor;
            
            var html = renderTimelineEvents(response.events);
            
            if (response.hasMore) {
                html += '<div class="text-center py-3" id="loadMoreContainer">' +
                    '<button class="btn btn-outline-primary btn-sm" onclick="loadMoreTimelineEvents()">' +
                        '<i class="fas fa-plus me-1"></i> Load More (' + (response.total - timelineLoadedCount) + ' remaining)' +
                    '</button>' +
                '</div>';
            }
            
            var resultHtml = html || '<p class="text-muted text-center py-4">No activity found matching the selected filters.</p>';
            var countHtml = '<div class="small text-muted mb-2">Showing ' + timelineLoadedCount + ' of ' + response.total + ' events</div>';
            
            timelineContainer.innerHTML = countHtml + resultHtml;
            if (timelineContainerModal) timelineContainerModal.innerHTML = countHtml + resultHtml;
            
            var bsCollapse = bootstrap.Collapse.getInstance(document.getElementById('timelineFiltersPanel'));
            if (bsCollapse) bsCollapse.hide();
            var bsCollapseModal = bootstrap.Collapse.getInstance(document.getElementById('timelineFiltersPanelModal'));
            if (bsCollapseModal) bsCollapseModal.hide();
        })
        .catch(function(error) {
            console.error('[Timeline] Error loading timeline:', error);
            var errorHtml = '<p class="text-danger text-center py-4">Failed to load timeline. Please try again.</p>';
            timelineContainer.innerHTML = errorHtml;
            if (timelineContainerModal) timelineContainerModal.innerHTML = errorHtml;
        });
}

function loadMoreTimelineEvents() {
    if (!currentTimelineContactId || !timelineNextCursor) {
        console.log('[Timeline] No more events to load');
        return;
    }
    
    var filters = getTimelineFilters();
    var loadMoreBtn = document.querySelector('#loadMoreContainer button');
    if (loadMoreBtn) {
        loadMoreBtn.disabled = true;
        loadMoreBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Loading...';
    }
    
    ContactTimelineService.getContactTimeline(currentTimelineContactId, filters, { 
        cursor: timelineNextCursor, 
        limit: timelinePageSize 
    })
    .then(function(response) {
        timelineLoadedCount += response.returned;
        timelineNextCursor = response.cursor;
        
        var newHtml = renderTimelineEvents(response.events);
        
        var loadMoreContainer = document.getElementById('loadMoreContainer');
        if (loadMoreContainer) {
            loadMoreContainer.insertAdjacentHTML('beforebegin', newHtml);
            
            if (!response.hasMore) {
                loadMoreContainer.innerHTML = '<p class="text-muted small mb-0">All events loaded</p>';
            } else {
                loadMoreContainer.innerHTML = '<button class="btn btn-outline-primary btn-sm" onclick="loadMoreTimelineEvents()">' +
                    '<i class="fas fa-plus me-1"></i> Load More (' + (timelineTotalEvents - timelineLoadedCount) + ' remaining)' +
                '</button>';
            }
        }
        
        var countEl = document.querySelector('#timelineEvents .small.text-muted');
        if (countEl) {
            countEl.textContent = 'Showing ' + timelineLoadedCount + ' of ' + timelineTotalEvents + ' events';
        }
        
        var loadMoreContainerModal = document.querySelector('#timelineEventsModal #loadMoreContainer');
        if (loadMoreContainerModal) {
            loadMoreContainerModal.insertAdjacentHTML('beforebegin', newHtml);
            if (!response.hasMore) {
                loadMoreContainerModal.innerHTML = '<p class="text-muted small mb-0">All events loaded</p>';
            } else {
                loadMoreContainerModal.innerHTML = '<button class="btn btn-outline-primary btn-sm" onclick="loadMoreTimelineEvents()">' +
                    '<i class="fas fa-plus me-1"></i> Load More (' + (timelineTotalEvents - timelineLoadedCount) + ' remaining)' +
                '</button>';
            }
        }
    })
    .catch(function(error) {
        console.error('[Timeline] Error loading more events:', error);
        if (loadMoreBtn) {
            loadMoreBtn.disabled = false;
            loadMoreBtn.innerHTML = '<i class="fas fa-exclamation-triangle me-1"></i> Retry';
        }
    });
}

function resetTimelineFilters() {
    initTimelineFilters();
    console.log('[Timeline] Filters reset to defaults');
}

document.addEventListener('DOMContentLoaded', function() {
    initTimelineFilters();
});
</script>
@endsection
