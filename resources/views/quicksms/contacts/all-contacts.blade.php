@extends('layouts.quicksms')

@section('title', 'All Contacts')

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
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                    <h4 class="card-title mb-2 mb-md-0">All Contacts</h4>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#filterPanel">
                            <i class="fas fa-filter me-1"></i> Filters
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
                        <div class="card card-body bg-light border">
                            <div class="row g-3">
                                <div class="col-md-3 col-lg-2">
                                    <label class="form-label small fw-bold">Status</label>
                                    <select class="form-select form-select-sm" id="filterStatus">
                                        <option value="">All Statuses</option>
                                        <option value="active">Active</option>
                                        <option value="opted-out">Opted Out</option>
                                    </select>
                                </div>
                                <div class="col-md-3 col-lg-2">
                                    <label class="form-label small fw-bold">Tags</label>
                                    <select class="form-select form-select-sm" id="filterTags">
                                        <option value="">All Tags</option>
                                        @foreach($available_tags as $tag)
                                        <option value="{{ $tag }}">{{ $tag }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3 col-lg-2">
                                    <label class="form-label small fw-bold">Lists</label>
                                    <select class="form-select form-select-sm" id="filterLists">
                                        <option value="">All Lists</option>
                                        @foreach($available_lists as $list)
                                        <option value="{{ $list }}">{{ $list }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3 col-lg-2">
                                    <label class="form-label small fw-bold">Source</label>
                                    <select class="form-select form-select-sm" id="filterSource">
                                        <option value="">All Sources</option>
                                        <option value="UI">UI</option>
                                        <option value="Import">Import</option>
                                        <option value="API">API</option>
                                        <option value="Email-to-SMS">Email-to-SMS</option>
                                    </select>
                                </div>
                                <div class="col-md-3 col-lg-2">
                                    <label class="form-label small fw-bold">Date of Birth</label>
                                    <input type="date" class="form-control form-control-sm" id="filterDOB" disabled title="TODO: Implement date range filter">
                                </div>
                                <div class="col-md-3 col-lg-2">
                                    <label class="form-label small fw-bold">Created Date</label>
                                    <input type="date" class="form-control form-control-sm" id="filterCreatedDate" disabled title="TODO: Implement date range filter">
                                </div>
                            </div>
                            <div class="row g-3 mt-1">
                                <div class="col-12">
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Custom field filters will appear here when custom fields are defined.
                                    </small>
                                </div>
                            </div>
                            <div class="mt-3" id="activeFilters"></div>
                        </div>
                    </div>

                    <div id="bulkActionBar" class="alert alert-light border d-none mb-3">
                        <div class="d-flex justify-content-between align-items-center flex-wrap">
                            <span><strong id="selectedCount">0</strong> contact(s) selected</span>
                            <div class="d-flex gap-2 flex-wrap mt-2 mt-md-0">
                                <button type="button" class="btn btn-sm btn-outline-primary" disabled title="TODO: Implement add to list">
                                    <i class="fas fa-plus me-1"></i> Add to List
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-primary" disabled title="TODO: Implement remove from list">
                                    <i class="fas fa-minus me-1"></i> Remove from List
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-primary" disabled title="TODO: Implement add tags">
                                    <i class="fas fa-tag me-1"></i> Add Tags
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-primary" disabled title="TODO: Implement remove tags">
                                    <i class="fas fa-times me-1"></i> Remove Tags
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-success" disabled title="TODO: Implement send message">
                                    <i class="fas fa-paper-plane me-1"></i> Send Message
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" disabled title="TODO: Implement export">
                                    <i class="fas fa-download me-1"></i> Export
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger" disabled title="TODO: Implement delete">
                                    <i class="fas fa-trash me-1"></i> Delete
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm mb-0 table-striped table-hover" id="contactsTable">
                            <thead>
                                <tr>
                                    <th class="pe-3" style="width: 40px;">
                                        <div class="form-check custom-checkbox">
                                            <input type="checkbox" class="form-check-input" id="checkAll">
                                            <label class="form-check-label" for="checkAll"></label>
                                        </div>
                                    </th>
                                    <th>Contact</th>
                                    <th>Mobile Number</th>
                                    <th>Tags</th>
                                    <th>Lists</th>
                                    <th>Status</th>
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
                                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2" style="width: 36px; height: 36px; font-size: 14px; font-weight: 600;">
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
                                        <span class="badge bg-light text-dark border me-1">{{ $tag }}</span>
                                        @endforeach
                                    </td>
                                    <td class="py-2">
                                        @foreach($contact['lists'] as $list)
                                        <span class="badge bg-info text-white me-1">{{ $list }}</span>
                                        @endforeach
                                    </td>
                                    <td class="py-2">
                                        @if($contact['status'] === 'active')
                                        <span class="badge bg-success">Active</span>
                                        @else
                                        <span class="badge bg-danger">Opted Out</span>
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
                                    <a class="page-link" href="#" tabindex="-1">Previous</a>
                                </li>
                                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                <li class="page-item"><a class="page-link" href="#">2</a></li>
                                <li class="page-item"><a class="page-link" href="#">3</a></li>
                                <li class="page-item">
                                    <a class="page-link" href="#">Next</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
var contactsData = @json($contacts);

document.addEventListener('DOMContentLoaded', function() {
    const checkAll = document.getElementById('checkAll');
    const contactCheckboxes = document.querySelectorAll('.contact-checkbox');
    const bulkActionBar = document.getElementById('bulkActionBar');
    const selectedCount = document.getElementById('selectedCount');
    const searchInput = document.getElementById('contactSearch');

    checkAll.addEventListener('change', function() {
        contactCheckboxes.forEach(cb => cb.checked = this.checked);
        updateBulkActionBar();
    });

    contactCheckboxes.forEach(cb => {
        cb.addEventListener('change', updateBulkActionBar);
    });

    function updateBulkActionBar() {
        const checkedCount = document.querySelectorAll('.contact-checkbox:checked').length;
        selectedCount.textContent = checkedCount;
        
        if (checkedCount > 0) {
            bulkActionBar.classList.remove('d-none');
        } else {
            bulkActionBar.classList.add('d-none');
        }

        const allChecked = checkedCount === contactCheckboxes.length;
        checkAll.checked = allChecked;
        checkAll.indeterminate = checkedCount > 0 && !allChecked;
    }

    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('#contactsTableBody tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });

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
</script>
@endsection
