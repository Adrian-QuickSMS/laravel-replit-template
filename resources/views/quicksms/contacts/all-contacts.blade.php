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
                        <table class="table table-sm mb-0 table-striped table-hover" id="contactsTable">
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
                                                <li><a class="dropdown-item" href="#!" onclick="sortContacts('contact', 'asc'); return false;"><i class="fas fa-sort-alpha-down me-2"></i> A-Z</a></li>
                                                <li><a class="dropdown-item" href="#!" onclick="sortContacts('contact', 'desc'); return false;"><i class="fas fa-sort-alpha-up me-2"></i> Z-A</a></li>
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
    var sortedContacts = [...contactsData].sort((a, b) => {
        let valA, valB;
        
        switch(sortKey) {
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
@endsection
