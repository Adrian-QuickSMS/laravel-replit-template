@extends('layouts.quicksms')

@section('title', 'Opt-Out Lists')

@push('styles')
<style>
/* API Integration code blocks - black background with white text */
#optout-api code.bg-light,
#optout-api pre.bg-light {
    background-color: #1e1e1e !important;
    color: #f8f8f2 !important;
}
/* API Integration info box - pastel purple with black text and purple icon */
#optout-api .alert-info {
    background-color: rgba(111, 66, 193, 0.08) !important;
    border: 1px solid rgba(111, 66, 193, 0.2) !important;
    color: #1f2937 !important;
}
#optout-api .alert-info i {
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
.optout-table-container {
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
            <li class="breadcrumb-item active">Opt-Out Lists</li>
        </ol>
    </div>
    
    <div class="row">
        <div class="col-12">
            <ul class="nav nav-tabs" id="optOutTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="manage-lists-tab" data-bs-toggle="tab" data-bs-target="#manage-lists" type="button" role="tab">
                        <i class="fas fa-list me-2"></i>Manage Lists <span class="badge badge-pastel-primary ms-1">{{ count($opt_out_lists) }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="view-optouts-tab" data-bs-toggle="tab" data-bs-target="#view-optouts" type="button" role="tab">
                        <i class="fas fa-ban me-2"></i>View Opt-Outs <span class="badge badge-pastel-danger ms-1">{{ $total_opt_outs }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="optout-api-tab" data-bs-toggle="tab" data-bs-target="#optout-api" type="button" role="tab">
                        <i class="fas fa-code me-2"></i>API Integration
                    </button>
                </li>
            </ul>
            
            <div class="tab-content" id="optOutTabContent">
                <div class="tab-pane fade show active" id="manage-lists" role="tabpanel">
                    <div class="card border-top-0 rounded-top-0">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="card-title mb-0">Opt-Out Lists</h5>
                                <small class="text-muted">Master and secondary suppression lists</small>
                            </div>
                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createOptOutListModal">
                                <i class="fas fa-plus me-1"></i> Create List
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-pastel-primary mb-4">
                                <i class="fas fa-info-circle text-primary me-2"></i>
                                <strong>Master Opt-Out List</strong> contains all suppressed numbers globally. <strong>Secondary lists</strong> allow brand/campaign-specific suppression management.
                            </div>
                            
                            <div class="optout-table-container">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>List Name</th>
                                            <th>Description</th>
                                            <th>Opt-Outs</th>
                                            <th>Created</th>
                                            <th>Last Updated</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="optOutListsBody">
                                        @foreach($opt_out_lists as $list)
                                        <tr data-list-id="{{ $list['id'] }}">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 36px; height: 36px; background-color: {{ $list['is_master'] ? 'rgba(220, 53, 69, 0.15)' : 'rgba(111, 66, 193, 0.15)' }}; color: {{ $list['is_master'] ? '#dc3545' : '#6f42c1' }};">
                                                        <i class="fas {{ $list['is_master'] ? 'fa-shield-alt' : 'fa-ban' }}"></i>
                                                    </div>
                                                    <div>
                                                        <span>{{ $list['name'] }}</span>
                                                        @if($list['is_master'])
                                                        <span class="badge badge-pastel-danger ms-2">Master</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td style="color: #000;">{{ $list['description'] }}</td>
                                            <td>
                                                <span class="badge badge-pastel-secondary">
                                                    <i class="fas fa-user-slash me-1"></i>{{ number_format($list['count']) }}
                                                </span>
                                            </td>
                                            <td style="color: #000;">{{ \Carbon\Carbon::parse($list['created_at'])->format('d-m-Y') }}</td>
                                            <td style="color: #000;">{{ \Carbon\Carbon::parse($list['updated_at'])->format('d-m-Y') }}</td>
                                            <td class="text-end">
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
                                                            <a class="dropdown-item" href="#!" onclick="viewOptOuts({{ $list['id'] }}, '{{ $list['name'] }}')">
                                                                <i class="fas fa-eye me-2 text-dark"></i> View Opt-Outs
                                                            </a>
                                                            <a class="dropdown-item" href="#!" onclick="exportOptOuts({{ $list['id'] }}, '{{ $list['name'] }}')">
                                                                <i class="fas fa-file-export me-2 text-dark"></i> Export
                                                            </a>
                                                            <a class="dropdown-item" href="#!" onclick="importOptOuts({{ $list['id'] }}, '{{ $list['name'] }}')">
                                                                <i class="fas fa-file-import me-2 text-dark"></i> Import
                                                            </a>
                                                            @if(!$list['is_master'])
                                                            <a class="dropdown-item" href="#!" onclick="renameOptOutList({{ $list['id'] }}, '{{ $list['name'] }}', '{{ $list['description'] }}')">
                                                                <i class="fas fa-edit me-2 text-dark"></i> Rename
                                                            </a>
                                                            <div class="dropdown-divider"></div>
                                                            <a class="dropdown-item text-danger" href="#!" onclick="deleteOptOutList({{ $list['id'] }}, '{{ $list['name'] }}')">
                                                                <i class="fas fa-trash me-2"></i> Delete
                                                            </a>
                                                            @endif
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
                
                <div class="tab-pane fade" id="view-optouts" role="tabpanel">
                    <div class="card border-top-0 rounded-top-0">
                        <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                            <div>
                                <h5 class="card-title mb-0">All Opt-Outs</h5>
                                <small class="text-muted">View and manage all opted-out numbers</small>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="btn btn-outline-success btn-sm" onclick="exportAllOptOuts()">
                                    <i class="fas fa-file-export me-1"></i> Export
                                </button>
                                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#importOptOutsModal">
                                    <i class="fas fa-file-import me-1"></i> Import
                                </button>
                                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addOptOutModal">
                                    <i class="fas fa-plus me-1"></i> Add Opt-Out
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-4 mb-2 mb-md-0">
                                    <div class="input-group">
                                        <span class="input-group-text bg-transparent"><i class="fas fa-search"></i></span>
                                        <input type="text" class="form-control" id="optOutSearch" placeholder="Search by mobile number or campaign...">
                                    </div>
                                </div>
                                <div class="col-md-3 mb-2 mb-md-0">
                                    <select class="form-select" id="filterSource">
                                        <option value="">All Sources</option>
                                        <option value="sms_reply">SMS Reply</option>
                                        <option value="url_click">URL Click</option>
                                        <option value="api">API</option>
                                        <option value="manual">Manual</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-select" id="filterList">
                                        <option value="">All Lists</option>
                                        @foreach($opt_out_lists as $list)
                                        <option value="{{ $list['id'] }}">{{ $list['name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th><input type="checkbox" id="checkAllOptOuts" class="form-check-input"></th>
                                            <th>Mobile Number</th>
                                            <th>Source</th>
                                            <th>Timestamp</th>
                                            <th>Campaign Ref</th>
                                            <th>List</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="optOutsTableBody">
                                        @foreach($opt_outs as $optout)
                                        <tr data-optout-id="{{ $optout['id'] }}" data-source="{{ $optout['source'] }}" data-list="{{ $optout['list_id'] }}">
                                            <td><input type="checkbox" class="form-check-input optout-checkbox" value="{{ $optout['id'] }}"></td>
                                            <td>
                                                <span class="mobile-masked" data-full="{{ $optout['mobile'] }}" style="color: #000; cursor: pointer;" onclick="toggleMobileVisibility(this)">
                                                    {{ substr($optout['mobile'], 0, 7) }}***{{ substr($optout['mobile'], -3) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($optout['source'] === 'sms_reply')
                                                <span class="badge badge-pastel-pink"><i class="fas fa-sms me-1"></i>SMS Reply</span>
                                                @elseif($optout['source'] === 'url_click')
                                                <span class="badge badge-pastel-success"><i class="fas fa-link me-1"></i>URL Click</span>
                                                @elseif($optout['source'] === 'api')
                                                <span class="badge badge-pastel-primary"><i class="fas fa-code me-1"></i>API</span>
                                                @else
                                                <span class="badge badge-pastel-warning"><i class="fas fa-hand-paper me-1"></i>Manual</span>
                                                @endif
                                            </td>
                                            <td style="color: #000;">{{ \Carbon\Carbon::parse($optout['timestamp'])->format('d-m-Y H:i') }}</td>
                                            <td style="color: #000;">
                                                @if($optout['campaign_ref'])
                                                <code>{{ $optout['campaign_ref'] }}</code>
                                                @else
                                                <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge badge-pastel-secondary">{{ $optout['list_name'] }}</span>
                                            </td>
                                            <td class="text-end">
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
                                                            <a class="dropdown-item" href="#!" onclick="viewOptOutHistory({{ $optout['id'] }})">
                                                                <i class="fas fa-history me-2 text-dark"></i> View History
                                                            </a>
                                                            <a class="dropdown-item" href="#!" onclick="moveToList({{ $optout['id'] }})">
                                                                <i class="fas fa-exchange-alt me-2 text-dark"></i> Move to List
                                                            </a>
                                                            <div class="dropdown-divider"></div>
                                                            <a class="dropdown-item text-danger" href="#!" onclick="removeOptOut({{ $optout['id'] }})">
                                                                <i class="fas fa-trash me-2"></i> Remove
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
                            
                            <div id="bulkActionBar" class="d-none mt-3 p-3 bg-light rounded border">
                                <div class="d-flex justify-content-between align-items-center flex-wrap">
                                    <span><strong id="selectedOptOutCount">0</strong> opt-outs selected</span>
                                    <div class="d-flex gap-2 mt-2 mt-md-0">
                                        <button class="btn btn-outline-primary btn-sm" onclick="bulkMoveToList()">
                                            <i class="fas fa-exchange-alt me-1"></i> Move to List
                                        </button>
                                        <button class="btn btn-outline-success btn-sm" onclick="bulkExport()">
                                            <i class="fas fa-file-export me-1"></i> Export Selected
                                        </button>
                                        <button class="btn btn-outline-danger btn-sm" onclick="bulkRemove()">
                                            <i class="fas fa-trash me-1"></i> Remove Selected
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap">
                                <div class="text-muted small mb-2 mb-md-0">
                                    Showing {{ count($opt_outs) }} of {{ $total_opt_outs }} opt-outs
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
                
                <div class="tab-pane fade" id="optout-api" role="tabpanel">
                    <div class="card border-top-0 rounded-top-0">
                        <div class="card-header">
                            <h5 class="card-title mb-0">API Integration</h5>
                            <small class="text-muted">Manage opt-outs programmatically via the API</small>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info mb-4">
                                <i class="fas fa-info-circle me-2"></i>
                                Use these API endpoints to manage opt-out lists and suppression records from external systems, CRMs, or automation workflows.
                            </div>
                            
                            <h6 class="mb-3"><i class="fas fa-list me-2"></i>Opt-Out List Management</h6>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <div class="border rounded p-3 h-100">
                                        <h6><i class="fas fa-plus-circle text-success me-2"></i>Create List</h6>
                                        <p class="small text-muted mb-2">Create a new opt-out list</p>
                                        <code class="small d-block bg-light p-2 rounded">POST /api/opt-out-lists</code>
                                        <p class="small text-muted mt-2 mb-0">Body:</p>
                                        <pre class="small bg-light p-2 rounded mb-0">{
  "name": "Brand X Opt-Outs",
  "description": "Opt-outs for Brand X campaigns"
}</pre>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="border rounded p-3 h-100">
                                        <h6><i class="fas fa-edit text-primary me-2"></i>Update List</h6>
                                        <p class="small text-muted mb-2">Update list name or description</p>
                                        <code class="small d-block bg-light p-2 rounded">PUT /api/opt-out-lists/{id}</code>
                                        <p class="small text-muted mt-2 mb-0">Body:</p>
                                        <pre class="small bg-light p-2 rounded mb-0">{
  "name": "Updated Name",
  "description": "Updated description"
}</pre>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="border rounded p-3 h-100">
                                        <h6><i class="fas fa-trash text-danger me-2"></i>Delete List</h6>
                                        <p class="small text-muted mb-2">Remove an opt-out list</p>
                                        <code class="small d-block bg-light p-2 rounded">DELETE /api/opt-out-lists/{id}</code>
                                        <p class="small text-muted mt-2 mb-0">Returns: <code>204 No Content</code></p>
                                        <p class="small text-muted mb-0">Note: Cannot delete Master list.</p>
                                    </div>
                                </div>
                            </div>

                            <h6 class="mb-3 mt-4"><i class="fas fa-ban me-2"></i>Opt-Out Management</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="border rounded p-3 h-100">
                                        <h6><i class="fas fa-user-slash text-danger me-2"></i>Add Opt-Out</h6>
                                        <p class="small text-muted mb-2">Add a number to an opt-out list</p>
                                        <code class="small d-block bg-light p-2 rounded">POST /api/opt-outs</code>
                                        <p class="small text-muted mt-2 mb-0">Body:</p>
                                        <pre class="small bg-light p-2 rounded mb-0">{
  "mobile": "+447700900123",
  "list_id": 1,
  "source": "api",
  "campaign_ref": "CAMPAIGN_ID"
}</pre>
                                        <p class="small text-muted mt-2 mb-0">Sources: <code>sms_reply</code>, <code>url_click</code>, <code>api</code>, <code>manual</code></p>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="border rounded p-3 h-100">
                                        <h6><i class="fas fa-user-check text-success me-2"></i>Remove Opt-Out</h6>
                                        <p class="small text-muted mb-2">Remove a number from opt-out list</p>
                                        <code class="small d-block bg-light p-2 rounded">DELETE /api/opt-outs/{id}</code>
                                        <p class="small text-muted mt-2 mb-0">Or by mobile number:</p>
                                        <code class="small d-block bg-light p-2 rounded">DELETE /api/opt-outs?mobile=+447700900123</code>
                                        <p class="small text-muted mt-2 mb-0">Returns: <code>204 No Content</code></p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="border rounded p-3 h-100">
                                        <h6><i class="fas fa-search text-info me-2"></i>Check Opt-Out Status</h6>
                                        <p class="small text-muted mb-2">Check if a number is opted out</p>
                                        <code class="small d-block bg-light p-2 rounded">GET /api/opt-outs/check?mobile=+447700900123</code>
                                        <p class="small text-muted mt-2 mb-0">Response:</p>
                                        <pre class="small bg-light p-2 rounded mb-0">{
  "opted_out": true,
  "lists": ["Master Opt-Out List"],
  "timestamp": "2024-12-21T14:32:15Z"
}</pre>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="border rounded p-3 h-100">
                                        <h6><i class="fas fa-file-import text-primary me-2"></i>Bulk Import</h6>
                                        <p class="small text-muted mb-2">Import multiple opt-outs at once</p>
                                        <code class="small d-block bg-light p-2 rounded">POST /api/opt-outs/bulk</code>
                                        <p class="small text-muted mt-2 mb-0">Body:</p>
                                        <pre class="small bg-light p-2 rounded mb-0">{
  "list_id": 1,
  "source": "api",
  "mobiles": ["+447700900123", "+447700900456"]
}</pre>
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

<div class="modal fade" id="createOptOutListModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Create Opt-Out List</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">List Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="newOptOutListName" placeholder="Enter list name">
                    <div class="invalid-feedback" id="listNameError"></div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Description</label>
                    <textarea class="form-control" id="newOptOutListDesc" rows="2" placeholder="Enter optional description"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="createOptOutList()">Create List</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addOptOutModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-slash me-2"></i>Add Opt-Out</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Mobile Number <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="addOptOutMobile" placeholder="+447700900123">
                    <div class="invalid-feedback" id="mobileError"></div>
                    <small class="text-muted">Enter in international format (e.g., +447700900123)</small>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Opt-Out List <span class="text-danger">*</span></label>
                    <select class="form-select" id="addOptOutList">
                        @foreach($opt_out_lists as $list)
                        <option value="{{ $list['id'] }}" {{ $list['is_master'] ? 'selected' : '' }}>{{ $list['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Source</label>
                    <select class="form-select" id="addOptOutSource">
                        <option value="manual" selected>Manual</option>
                        <option value="sms_reply">SMS Reply</option>
                        <option value="url_click">URL Click</option>
                        <option value="api">API</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Campaign Reference</label>
                    <input type="text" class="form-control" id="addOptOutCampaign" placeholder="Optional campaign reference">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="addOptOut()">Add Opt-Out</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="importOptOutsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-file-import me-2"></i>Import Opt-Outs</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-3">
                        <div class="text-center flex-fill">
                            <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" id="importStep1Circle">1</div>
                            <div class="small mt-1">Upload File</div>
                        </div>
                        <div class="text-center flex-fill">
                            <div class="rounded-circle bg-secondary text-white d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" id="importStep2Circle">2</div>
                            <div class="small mt-1">Map Columns</div>
                        </div>
                        <div class="text-center flex-fill">
                            <div class="rounded-circle bg-secondary text-white d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" id="importStep3Circle">3</div>
                            <div class="small mt-1">Confirm</div>
                        </div>
                    </div>
                </div>

                <div id="importStep1">
                    <h6 class="mb-3">Step 1: Upload File</h6>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Target List <span class="text-danger">*</span></label>
                        <select class="form-select" id="importTargetList">
                            @foreach($opt_out_lists as $list)
                            <option value="{{ $list['id'] }}" {{ $list['is_master'] ? 'selected' : '' }}>{{ $list['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">File Upload <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="importFile" accept=".csv,.xlsx,.xls">
                        <small class="text-muted">Supported formats: CSV, Excel (.xlsx, .xls)</small>
                    </div>
                    <div class="alert alert-warning" id="excelZeroWarning" style="display: none;">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Excel Zero-Strip Detection:</strong> Mobile numbers may have leading zeros stripped. Would you like to apply automatic correction?
                        <div class="mt-2">
                            <button class="btn btn-warning btn-sm" onclick="applyExcelCorrection()">Yes, Apply Correction</button>
                            <button class="btn btn-outline-secondary btn-sm" onclick="skipExcelCorrection()">No, Keep As-Is</button>
                        </div>
                    </div>
                </div>

                <div id="importStep2" class="d-none">
                    <h6 class="mb-3">Step 2: Map Columns</h6>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        We detected the following columns. Please map them to the required fields.
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Detected Column</th>
                                    <th>Map To</th>
                                    <th>Sample Data</th>
                                </tr>
                            </thead>
                            <tbody id="columnMappingBody">
                                <tr>
                                    <td>phone</td>
                                    <td>
                                        <select class="form-select form-select-sm">
                                            <option value="mobile" selected>Mobile Number</option>
                                            <option value="campaign_ref">Campaign Reference</option>
                                            <option value="ignore">Ignore</option>
                                        </select>
                                    </td>
                                    <td class="text-muted">+447700900123</td>
                                </tr>
                                <tr>
                                    <td>campaign</td>
                                    <td>
                                        <select class="form-select form-select-sm">
                                            <option value="mobile">Mobile Number</option>
                                            <option value="campaign_ref" selected>Campaign Reference</option>
                                            <option value="ignore">Ignore</option>
                                        </select>
                                    </td>
                                    <td class="text-muted">XMAS2024</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="importStep3" class="d-none">
                    <h6 class="mb-3">Step 3: Confirm Import</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h3 class="text-primary" id="importTotalRows">0</h3>
                                    <p class="text-muted mb-0">Total Rows</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h3 class="text-success" id="importValidNumbers">0</h3>
                                    <p class="text-muted mb-0">Valid Numbers</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h3 class="text-warning" id="importDuplicates">0</h3>
                                    <p class="text-muted mb-0">Duplicates (Skipped)</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h3 class="text-danger" id="importInvalid">0</h3>
                                    <p class="text-muted mb-0">Invalid Numbers</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3" id="importIndicators"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-outline-primary d-none" id="importBackBtn" onclick="importPrevStep()">Back</button>
                <button type="button" class="btn btn-primary" id="importNextBtn" onclick="importNextStep()">Next</button>
                <button type="button" class="btn btn-success d-none" id="importConfirmBtn" onclick="confirmImport()">Import</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="renameOptOutListModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Rename Opt-Out List</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="renameListId">
                <div class="mb-3">
                    <label class="form-label fw-bold">List Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="renameListName" placeholder="Enter list name">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Description</label>
                    <textarea class="form-control" id="renameListDesc" rows="2" placeholder="Enter optional description"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveRenameList()">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Move to List Modal -->
<div class="modal fade" id="moveToListModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-white border-bottom">
                <h5 class="modal-title text-dark"><i class="fas fa-exchange-alt me-2"></i>Move to Opt-Out List</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="moveOptOutId">
                <p class="mb-3">Move this number to a different opt-out list:</p>
                <div class="mb-3">
                    <label class="form-label fw-bold">Current Number</label>
                    <input type="text" class="form-control" id="moveOptOutNumber" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Target List <span class="text-danger">*</span></label>
                    <select class="form-select" id="moveTargetList">
                        <option value="">Select target list...</option>
                        @foreach($opt_out_lists as $list)
                        <option value="{{ $list['id'] }}">{{ $list['name'] }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn" style="background-color: #6b5b95; color: white;" onclick="confirmMoveToList()">
                    <i class="fas fa-exchange-alt me-1"></i> Move
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Remove Opt-Out Confirmation Modal -->
<div class="modal fade" id="removeOptOutConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-white border-bottom">
                <h5 class="modal-title text-dark"><i class="fas fa-trash me-2 text-danger"></i>Remove Opt-Out</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="fas fa-exclamation-triangle text-warning fa-3x mb-3"></i>
                <p class="mb-2" id="removeOptOutMessage">Are you sure you want to remove this opt-out?</p>
                <p class="text-muted small mb-0">The contact will be able to receive messages again.</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger btn-sm" id="confirmRemoveOptOutBtn">Remove</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="exportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-file-export me-2"></i>Export Opt-Outs</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="exportListId">
                <p id="exportListName" class="fw-bold"></p>
                <div class="mb-3">
                    <label class="form-label fw-bold">Export Format</label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="exportFormat" id="exportCSV" value="csv" checked>
                        <label class="form-check-label" for="exportCSV">CSV (.csv)</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="exportFormat" id="exportExcel" value="xlsx">
                        <label class="form-check-label" for="exportExcel">Excel (.xlsx)</label>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Include Fields</label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="exportMobile" checked disabled>
                        <label class="form-check-label" for="exportMobile">Mobile Number (required)</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="exportSource" checked>
                        <label class="form-check-label" for="exportSource">Source</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="exportTimestamp" checked>
                        <label class="form-check-label" for="exportTimestamp">Timestamp</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="exportCampaign" checked>
                        <label class="form-check-label" for="exportCampaign">Campaign Reference</label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="downloadExport()">
                    <i class="fas fa-download me-1"></i> Download
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="confirmActionModal" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title" id="confirmActionTitle"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-2" id="confirmActionBody"></div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmActionBtn">Delete</button>
            </div>
        </div>
    </div>
</div>

<script>
var _csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
function showToast(message, type) {
    type = type || 'success';
    var bgColor = type === 'error' ? '#dc3545' : (type === 'warning' ? '#ffc107' : '#28a745');
    var textColor = type === 'warning' ? '#000' : '#fff';
    var toast = document.createElement('div');
    toast.style.cssText = 'position:fixed;top:20px;right:20px;z-index:9999;padding:12px 24px;border-radius:8px;color:' + textColor + ';background:' + bgColor + ';box-shadow:0 4px 12px rgba(0,0,0,0.15);font-size:14px;max-width:400px;animation:fadeIn 0.3s ease;';
    toast.innerHTML = '<i class="fas fa-' + (type === 'error' ? 'exclamation-circle' : 'check-circle') + ' me-2"></i>' + message;
    document.body.appendChild(toast);
    setTimeout(function() { toast.style.opacity = '0'; toast.style.transition = 'opacity 0.3s'; }, 2500);
    setTimeout(function() { toast.remove(); }, 3000);
}
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

function showConfirmModal(title, body, btnText, btnClass, onConfirm) {
    document.getElementById('confirmActionTitle').textContent = title;
    document.getElementById('confirmActionBody').innerHTML = body;
    var btn = document.getElementById('confirmActionBtn');
    btn.textContent = btnText;
    btn.className = 'btn ' + btnClass;
    btn.onclick = function() {
        bootstrap.Modal.getInstance(document.getElementById('confirmActionModal')).hide();
        onConfirm();
    };
    var confirmModal = new bootstrap.Modal(document.getElementById('confirmActionModal'), { backdrop: true });
    confirmModal.show();
    document.getElementById('confirmActionModal').addEventListener('shown.bs.modal', function handler() {
        var backdrops = document.querySelectorAll('.modal-backdrop');
        if (backdrops.length > 1) {
            backdrops[backdrops.length - 1].style.zIndex = '1055';
        }
        this.removeEventListener('shown.bs.modal', handler);
    });
}

var optOutLists = @json($opt_out_lists);
var optOuts = @json($opt_outs);
var importStep = 1;

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('optOutSearch');
    const filterSource = document.getElementById('filterSource');
    const filterList = document.getElementById('filterList');
    const checkAll = document.getElementById('checkAllOptOuts');
    
    if (searchInput) {
        searchInput.addEventListener('input', filterOptOuts);
    }
    if (filterSource) {
        filterSource.addEventListener('change', filterOptOuts);
    }
    if (filterList) {
        filterList.addEventListener('change', filterOptOuts);
    }
    if (checkAll) {
        checkAll.addEventListener('change', function() {
            document.querySelectorAll('.optout-checkbox').forEach(cb => {
                if (!cb.closest('tr').classList.contains('d-none')) {
                    cb.checked = this.checked;
                }
            });
            updateBulkActionBar();
        });
    }
    
    document.getElementById('optOutsTableBody').addEventListener('change', function(e) {
        if (e.target.classList.contains('optout-checkbox')) {
            updateBulkActionBar();
        }
    });
});

function filterOptOuts() {
    const searchTerm = document.getElementById('optOutSearch').value.toLowerCase();
    const sourceFilter = document.getElementById('filterSource').value;
    const listFilter = document.getElementById('filterList').value;
    
    document.querySelectorAll('#optOutsTableBody tr').forEach(row => {
        const text = row.textContent.toLowerCase();
        const source = row.dataset.source;
        const list = row.dataset.list;
        
        let show = true;
        if (searchTerm && !text.includes(searchTerm)) show = false;
        if (sourceFilter && source !== sourceFilter) show = false;
        if (listFilter && list !== listFilter) show = false;
        
        row.classList.toggle('d-none', !show);
    });
}

function updateBulkActionBar() {
    const checked = document.querySelectorAll('.optout-checkbox:checked').length;
    const bar = document.getElementById('bulkActionBar');
    const count = document.getElementById('selectedOptOutCount');
    
    if (checked > 0) {
        bar.classList.remove('d-none');
        count.textContent = checked;
    } else {
        bar.classList.add('d-none');
    }
}

function toggleMobileVisibility(el) {
    const full = el.dataset.full;
    const masked = el.dataset.full.substring(0, 7) + '***' + el.dataset.full.slice(-3);
    
    if (el.textContent === masked) {
        el.textContent = full;
    } else {
        el.textContent = masked;
    }
}

function createOptOutList() {
    var name = document.getElementById('newOptOutListName').value.trim();
    var desc = document.getElementById('newOptOutListDesc').value.trim();
    
    if (!name) {
        document.getElementById('newOptOutListName').classList.add('is-invalid');
        document.getElementById('listNameError').textContent = 'List name is required';
        return;
    }
    
    fetch('/api/opt-out-lists', {
        method: 'POST',
        headers: _apiHeaders(),
        body: JSON.stringify({ name: name, description: desc })
    })
    .then(_handleApiResponse)
    .then(function() {
        showToast('List "' + name + '" created successfully', 'success');
        bootstrap.Modal.getInstance(document.getElementById('createOptOutListModal')).hide();
        document.getElementById('newOptOutListName').value = '';
        document.getElementById('newOptOutListDesc').value = '';
        setTimeout(function() { location.reload(); }, 800);
    })
    .catch(function(err) {
        showToast(err.message || 'Failed to create list', 'error');
    });
}

function addOptOut() {
    var mobile = document.getElementById('addOptOutMobile').value.trim();
    var listId = document.getElementById('addOptOutList').value;
    var source = document.getElementById('addOptOutSource').value;
    var campaign = document.getElementById('addOptOutCampaign').value.trim();
    
    var mobileRegex = /^\+[1-9]\d{6,14}$/;
    if (!mobile) {
        document.getElementById('addOptOutMobile').classList.add('is-invalid');
        document.getElementById('mobileError').textContent = 'Mobile number is required';
        return;
    }
    if (!mobileRegex.test(mobile)) {
        document.getElementById('addOptOutMobile').classList.add('is-invalid');
        document.getElementById('mobileError').textContent = 'Invalid format. Use international format (e.g., +447700900123)';
        return;
    }
    
    fetch('/api/opt-out-lists/' + listId + '/records', {
        method: 'POST',
        headers: _apiHeaders(),
        body: JSON.stringify({ msisdn: mobile, source: source, campaign_ref: campaign })
    })
    .then(_handleApiResponse)
    .then(function() {
        showToast('Opt-out for ' + mobile + ' added successfully', 'success');
        bootstrap.Modal.getInstance(document.getElementById('addOptOutModal')).hide();
        document.getElementById('addOptOutMobile').value = '';
        document.getElementById('addOptOutMobile').classList.remove('is-invalid');
        setTimeout(function() { location.reload(); }, 800);
    })
    .catch(function(err) {
        showToast(err.message || 'Failed to add opt-out', 'error');
    });
}

function viewOptOuts(listId, listName) {
    document.getElementById('view-optouts-tab').click();
    document.getElementById('filterList').value = listId;
    filterOptOuts();
}

function exportOptOuts(listId, listName) {
    document.getElementById('exportListId').value = listId;
    document.getElementById('exportListName').textContent = 'Exporting: ' + listName;
    new bootstrap.Modal(document.getElementById('exportModal')).show();
}

function exportAllOptOuts() {
    document.getElementById('exportListId').value = '';
    document.getElementById('exportListName').textContent = 'Exporting: All Opt-Outs';
    new bootstrap.Modal(document.getElementById('exportModal')).show();
}

function downloadExport() {
    var format = document.querySelector('input[name="exportFormat"]:checked').value;
    showToast('Export as .' + format + ' will be available in a future update.', 'warning');
    bootstrap.Modal.getInstance(document.getElementById('exportModal')).hide();
}

function importOptOuts(listId, listName) {
    document.getElementById('importTargetList').value = listId;
    new bootstrap.Modal(document.getElementById('importOptOutsModal')).show();
}

function importNextStep() {
    if (importStep === 1) {
        if (!document.getElementById('importFile').value) {
            alert('Please select a file to import');
            return;
        }
        document.getElementById('importStep1').classList.add('d-none');
        document.getElementById('importStep2').classList.remove('d-none');
        document.getElementById('importStep1Circle').classList.replace('bg-primary', 'bg-success');
        document.getElementById('importStep2Circle').classList.replace('bg-secondary', 'bg-primary');
        document.getElementById('importBackBtn').classList.remove('d-none');
        importStep = 2;
    } else if (importStep === 2) {
        document.getElementById('importStep2').classList.add('d-none');
        document.getElementById('importStep3').classList.remove('d-none');
        document.getElementById('importStep2Circle').classList.replace('bg-primary', 'bg-success');
        document.getElementById('importStep3Circle').classList.replace('bg-secondary', 'bg-primary');
        document.getElementById('importNextBtn').classList.add('d-none');
        document.getElementById('importConfirmBtn').classList.remove('d-none');
        
        // Simulate validation results
        document.getElementById('importTotalRows').textContent = '245';
        document.getElementById('importValidNumbers').textContent = '238';
        document.getElementById('importDuplicates').textContent = '5';
        document.getElementById('importInvalid').textContent = '2';
        document.getElementById('importIndicators').innerHTML = '<span class="badge bg-info me-2"><i class="fas fa-globe me-1"></i> UK format normalized</span><span class="badge bg-secondary"><i class="fas fa-copy me-1"></i> De-duplicated</span>';
        
        importStep = 3;
    }
}

function importPrevStep() {
    if (importStep === 2) {
        document.getElementById('importStep2').classList.add('d-none');
        document.getElementById('importStep1').classList.remove('d-none');
        document.getElementById('importStep1Circle').classList.replace('bg-success', 'bg-primary');
        document.getElementById('importStep2Circle').classList.replace('bg-primary', 'bg-secondary');
        document.getElementById('importBackBtn').classList.add('d-none');
        importStep = 1;
    } else if (importStep === 3) {
        document.getElementById('importStep3').classList.add('d-none');
        document.getElementById('importStep2').classList.remove('d-none');
        document.getElementById('importStep2Circle').classList.replace('bg-success', 'bg-primary');
        document.getElementById('importStep3Circle').classList.replace('bg-primary', 'bg-secondary');
        document.getElementById('importNextBtn').classList.remove('d-none');
        document.getElementById('importConfirmBtn').classList.add('d-none');
        importStep = 2;
    }
}

function confirmImport() {
    showToast('Import functionality will be available in a future update.', 'warning');
    bootstrap.Modal.getInstance(document.getElementById('importOptOutsModal')).hide();
    resetImportWizard();
}

function resetImportWizard() {
    importStep = 1;
    document.getElementById('importStep1').classList.remove('d-none');
    document.getElementById('importStep2').classList.add('d-none');
    document.getElementById('importStep3').classList.add('d-none');
    document.getElementById('importStep1Circle').className = 'rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center';
    document.getElementById('importStep2Circle').className = 'rounded-circle bg-secondary text-white d-inline-flex align-items-center justify-content-center';
    document.getElementById('importStep3Circle').className = 'rounded-circle bg-secondary text-white d-inline-flex align-items-center justify-content-center';
    document.getElementById('importBackBtn').classList.add('d-none');
    document.getElementById('importNextBtn').classList.remove('d-none');
    document.getElementById('importConfirmBtn').classList.add('d-none');
    document.getElementById('importFile').value = '';
}

function applyExcelCorrection() {
    document.getElementById('excelZeroWarning').style.display = 'none';
    showToast('Excel zero-strip correction applied.', 'success');
}

function skipExcelCorrection() {
    document.getElementById('excelZeroWarning').style.display = 'none';
}

function renameOptOutList(listId, name, desc) {
    // TODO: Connect to API - PUT /api/opt-out-lists/{id}
    document.getElementById('renameListId').value = listId;
    document.getElementById('renameListName').value = name;
    document.getElementById('renameListDesc').value = desc;
    new bootstrap.Modal(document.getElementById('renameOptOutListModal')).show();
}

function saveRenameList() {
    var id = document.getElementById('renameListId').value;
    var name = document.getElementById('renameListName').value.trim();
    var desc = document.getElementById('renameListDesc').value.trim();
    if (!name) {
        showToast('List name is required', 'warning');
        return;
    }
    
    fetch('/api/opt-out-lists/' + id, {
        method: 'PUT',
        headers: _apiHeaders(),
        body: JSON.stringify({ name: name, description: desc })
    })
    .then(_handleApiResponse)
    .then(function() {
        showToast('List renamed to "' + name + '"', 'success');
        bootstrap.Modal.getInstance(document.getElementById('renameOptOutListModal')).hide();
        setTimeout(function() { location.reload(); }, 800);
    })
    .catch(function(err) {
        showToast(err.message || 'Failed to rename list', 'error');
    });
}

function deleteOptOutList(listId, name) {
    showConfirmModal(
        'Delete Opt-Out List',
        '<p>Are you sure you want to delete <strong>"' + name + '"</strong>?</p><p class="text-muted mb-0"><small>This action cannot be undone. All opt-out records in this list will also be removed.</small></p>',
        'Delete',
        'btn-danger',
        function() {
            fetch('/api/opt-out-lists/' + listId, {
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
    );
}

function viewOptOutHistory(optOutId) {
    // Find the row to get the mobile number
    var row = document.querySelector('tr[data-id="' + optOutId + '"]');
    var mobileNumber = '';
    if (row) {
        var mobileCell = row.querySelector('td:nth-child(2)');
        if (mobileCell) {
            mobileNumber = mobileCell.textContent.trim();
        }
    }
    
    // Navigate to All Contacts with Activity Timeline filter for opt-out events
    // The URL includes query params to trigger the timeline view with opt-out filter
    var url = '/contacts/all-contacts?timeline=open&filter=opt_out';
    if (mobileNumber) {
        url += '&search=' + encodeURIComponent(mobileNumber);
    }
    window.location.href = url;
}

function moveToList(optOutId) {
    // Find the row to get the mobile number
    var row = document.querySelector('tr[data-id="' + optOutId + '"]');
    var mobileNumber = '';
    if (row) {
        var mobileCell = row.querySelector('td:nth-child(2)');
        if (mobileCell) {
            mobileNumber = mobileCell.textContent.trim();
        }
    }
    
    document.getElementById('moveOptOutId').value = optOutId;
    document.getElementById('moveOptOutNumber').value = mobileNumber;
    document.getElementById('moveTargetList').value = '';
    
    var modal = new bootstrap.Modal(document.getElementById('moveToListModal'));
    modal.show();
}

function confirmMoveToList() {
    var optOutId = document.getElementById('moveOptOutId').value;
    var targetListId = document.getElementById('moveTargetList').value;
    var targetListName = document.getElementById('moveTargetList').options[document.getElementById('moveTargetList').selectedIndex].text;
    
    if (!targetListId) {
        showToast('Please select a target list.', 'warning');
        return;
    }
    
    showToast('Move functionality will be available in a future update.', 'warning');
    var modal = bootstrap.Modal.getInstance(document.getElementById('moveToListModal'));
    modal.hide();
}

var pendingRemoveOptOut = null;

function removeOptOut(optOutId) {
    pendingRemoveOptOut = { id: optOutId };
    
    document.getElementById('removeOptOutMessage').textContent = 'Are you sure you want to remove this opt-out?';
    
    var confirmModal = new bootstrap.Modal(document.getElementById('removeOptOutConfirmModal'));
    confirmModal.show();
}

function executeRemoveOptOut() {
    if (!pendingRemoveOptOut) return;
    
    fetch('/api/opt-out-records/' + pendingRemoveOptOut.id, {
        method: 'DELETE',
        headers: _apiHeaders()
    })
    .then(_handleApiResponse)
    .then(function() {
        var confirmModal = bootstrap.Modal.getInstance(document.getElementById('removeOptOutConfirmModal'));
        confirmModal.hide();
        showToast('Opt-out removed successfully', 'success');
        pendingRemoveOptOut = null;
        setTimeout(function() { location.reload(); }, 800);
    })
    .catch(function(err) {
        showToast(err.message || 'Failed to remove opt-out', 'error');
    });
}

// Set up confirm remove button click handler
document.addEventListener('DOMContentLoaded', function() {
    var confirmRemoveBtn = document.getElementById('confirmRemoveOptOutBtn');
    if (confirmRemoveBtn) {
        confirmRemoveBtn.addEventListener('click', executeRemoveOptOut);
    }
});

function bulkMoveToList() {
    var count = document.querySelectorAll('.optout-checkbox:checked').length;
    showToast('Bulk move (' + count + ' opt-outs) will be available in a future update.', 'warning');
}

function bulkExport() {
    var count = document.querySelectorAll('.optout-checkbox:checked').length;
    document.getElementById('exportListId').value = '';
    document.getElementById('exportListName').textContent = 'Exporting: ' + count + ' selected opt-outs';
    new bootstrap.Modal(document.getElementById('exportModal')).show();
}

var pendingBulkRemove = null;

function bulkRemove() {
    var selectedIds = [];
    document.querySelectorAll('.optout-checkbox:checked').forEach(function(cb) {
        selectedIds.push(cb.value);
    });
    var count = selectedIds.length;
    pendingBulkRemove = { count: count, ids: selectedIds };
    
    document.getElementById('removeOptOutMessage').textContent = 'Are you sure you want to remove ' + count + ' opt-out(s)?';
    
    var confirmBtn = document.getElementById('confirmRemoveOptOutBtn');
    confirmBtn.onclick = function() {
        var deletePromises = pendingBulkRemove.ids.map(function(id) {
            return fetch('/api/opt-out-records/' + id, {
                method: 'DELETE',
                headers: _apiHeaders()
            }).then(_handleApiResponse);
        });
        
        Promise.all(deletePromises)
        .then(function() {
            var confirmModal = bootstrap.Modal.getInstance(document.getElementById('removeOptOutConfirmModal'));
            confirmModal.hide();
            showToast(pendingBulkRemove.count + ' opt-out(s) removed successfully', 'success');
            pendingBulkRemove = null;
            confirmBtn.onclick = executeRemoveOptOut;
            setTimeout(function() { location.reload(); }, 800);
        })
        .catch(function(err) {
            showToast(err.message || 'Failed to remove opt-outs', 'error');
            confirmBtn.onclick = executeRemoveOptOut;
        });
    };
    
    var confirmModal = new bootstrap.Modal(document.getElementById('removeOptOutConfirmModal'));
    confirmModal.show();
}

// Show Excel warning when xlsx/xls file is selected
document.getElementById('importFile').addEventListener('change', function() {
    const file = this.value;
    if (file.endsWith('.xlsx') || file.endsWith('.xls')) {
        document.getElementById('excelZeroWarning').style.display = 'block';
    } else {
        document.getElementById('excelZeroWarning').style.display = 'none';
    }
});

// Clear validation states on input
document.getElementById('newOptOutListName').addEventListener('input', function() {
    this.classList.remove('is-invalid');
});
document.getElementById('addOptOutMobile').addEventListener('input', function() {
    this.classList.remove('is-invalid');
});
</script>
@endsection
