@extends('layouts.admin')

@section('title', 'MCC/MNC Reference - Supplier Management')

@push('styles')
<style>
:root {
    --admin-primary: #1e3a5f;
    --admin-secondary: #2d5a87;
    --admin-accent: #4a90d9;
}

.mcc-mnc-table-card {
    background: #fff;
    border-radius: 12px;
    border: 1px solid #dde4ea;
    overflow: hidden;
    box-shadow: 0 1px 4px rgba(0,0,0,0.08);
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 500;
}

.status-badge.active {
    background: #d4f4dd;
    color: #198754;
}

.status-badge.inactive {
    background: #e0e0e0;
    color: #6c757d;
}

.network-type-badge {
    padding: 0.15rem 0.5rem;
    border-radius: 8px;
    font-size: 0.7rem;
    font-weight: 500;
}

.network-type-badge.mobile {
    background: #e3f2fd;
    color: #1976d2;
}

.network-type-badge.fixed {
    background: #f3e5f5;
    color: #7b1fa2;
}

.network-type-badge.virtual {
    background: #fff3e0;
    color: #e65100;
}

.filter-toolbar {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
}

.action-menu-btn {
    background: transparent;
    border: none;
    padding: 0.25rem 0.5rem;
    cursor: pointer;
    color: #6c757d;
}

.action-menu-btn:hover {
    color: #1e3a5f;
}

.import-section {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}
</style>
@endpush

@section('content')
<div class="page-titles">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
        <li class="breadcrumb-item"><a href="#">Supplier Management</a></li>
        <li class="breadcrumb-item active">MCC/MNC Reference</li>
    </ol>
</div>

<div class="page-header">
    <div>
        <h2>MCC/MNC Master Database</h2>
        <p>Mobile Country Code and Mobile Network Code reference</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-admin-primary" onclick="openAddMccMncModal()">
            <i class="fas fa-plus me-2"></i>Add Network
        </button>
        <button class="btn btn-outline-primary" onclick="openImportModal()">
            <i class="fas fa-file-import me-2"></i>Bulk Import
        </button>
    </div>
</div>

<div class="filter-toolbar">
    <div class="row g-3">
        <div class="col-md-3">
            <select class="form-select" id="filterCountry" onchange="filterNetworks()">
                <option value="">All Countries</option>
                @foreach($countries as $country)
                <option value="{{ $country->country_iso }}">{{ $country->country_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <select class="form-select" id="filterType" onchange="filterNetworks()">
                <option value="">All Network Types</option>
                <option value="mobile">Mobile</option>
                <option value="fixed">Fixed</option>
                <option value="virtual">Virtual</option>
            </select>
        </div>
        <div class="col-md-3">
            <select class="form-select" id="filterStatus" onchange="filterNetworks()">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
        <div class="col-md-3">
            <input type="text" class="form-control" id="searchNetwork" placeholder="Search MCC/MNC or network..." onkeyup="filterNetworks()">
        </div>
    </div>
</div>

<div class="mcc-mnc-table-card">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>MCC</th>
                    <th>MNC</th>
                    <th>Country</th>
                    <th>Network Name</th>
                    <th>Network Type</th>
                    <th>Status</th>
                    <th>Rate Cards</th>
                    <th style="width: 100px;">Actions</th>
                </tr>
            </thead>
            <tbody id="mccMncTableBody">
                @forelse($mccMncList as $network)
                <tr data-country="{{ $network->country_iso }}"
                    data-type="{{ $network->network_type }}"
                    data-status="{{ $network->active ? 'active' : 'inactive' }}"
                    data-search="{{ strtolower($network->mcc . $network->mnc . $network->network_name . $network->country_name) }}">
                    <td><code>{{ $network->mcc }}</code></td>
                    <td><code>{{ $network->mnc }}</code></td>
                    <td>
                        <strong>{{ $network->country_name }}</strong>
                        <br><small class="text-muted">{{ $network->country_iso }}</small>
                    </td>
                    <td>{{ $network->network_name }}</td>
                    <td>
                        <span class="network-type-badge {{ $network->network_type }}">
                            {{ ucfirst($network->network_type) }}
                        </span>
                    </td>
                    <td>
                        <span class="status-badge {{ $network->active ? 'active' : 'inactive' }}">
                            <i class="fas fa-circle" style="font-size: 6px;"></i>
                            {{ $network->active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>{{ $network->rate_cards_count ?? 0 }}</td>
                    <td>
                        <div class="dropdown">
                            <button class="action-menu-btn" data-bs-toggle="dropdown">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#" onclick="editNetwork({{ $network->id }})"><i class="fas fa-edit me-2"></i>Edit</a></li>
                                <li><a class="dropdown-item" href="#" onclick="toggleNetworkStatus({{ $network->id }})"><i class="fas fa-toggle-{{ $network->active ? 'off' : 'on' }} me-2"></i>{{ $network->active ? 'Deactivate' : 'Activate' }}</a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.rate-cards.index', ['mcc' => $network->mcc, 'mnc' => $network->mnc]) }}"><i class="fas fa-dollar-sign me-2"></i>View Rates</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="#" onclick="deleteNetwork({{ $network->id }})"><i class="fas fa-trash me-2"></i>Delete</a></li>
                            </ul>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-4 text-muted">
                        <i class="fas fa-inbox fa-2x mb-2"></i>
                        <p>No MCC/MNC records found</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($mccMncList->hasPages())
<div class="d-flex justify-content-between align-items-center mt-3">
    <div class="text-muted">
        Showing {{ $mccMncList->firstItem() }} to {{ $mccMncList->lastItem() }} of {{ $mccMncList->total() }} networks
    </div>
    <div>
        {{ $mccMncList->links() }}
    </div>
</div>
@endif

<!-- Add MCC/MNC Modal -->
<div class="modal fade" id="addMccMncModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Network</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addMccMncForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">MCC <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="mcc" maxlength="3" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">MNC <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="mnc" maxlength="3" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Country Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="country_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Country ISO Code <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="country_iso" maxlength="2" required>
                        <small class="text-muted">2-letter code (e.g., GB, US)</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Network Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="network_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Network Type <span class="text-danger">*</span></label>
                        <select class="form-select" name="network_type" required>
                            <option value="mobile">Mobile</option>
                            <option value="fixed">Fixed</option>
                            <option value="virtual">Virtual</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-admin-primary" onclick="submitAddMccMnc()">Add Network</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit MCC/MNC Modal -->
<div class="modal fade" id="editMccMncModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Network</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editMccMncForm">
                    <input type="hidden" name="network_id" id="editNetworkId">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">MCC</label>
                            <input type="text" class="form-control" id="editMcc" readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">MNC</label>
                            <input type="text" class="form-control" id="editMnc" readonly>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Network Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="network_name" id="editNetworkName" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Network Type <span class="text-danger">*</span></label>
                        <select class="form-select" name="network_type" id="editNetworkType" required>
                            <option value="mobile">Mobile</option>
                            <option value="fixed">Fixed</option>
                            <option value="virtual">Virtual</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-admin-primary" onclick="submitEditMccMnc()">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Import Wizard Modal -->
<div class="modal fade" id="importModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: var(--admin-primary); color: #fff;">
                <h5 class="modal-title"><i class="fas fa-file-import me-2"></i>Bulk Import MCC/MNC Data</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" onclick="resetImportWizard()"></button>
            </div>
            <div class="modal-body p-0">
                <!-- Step indicators -->
                <div class="import-steps d-flex border-bottom">
                    <div class="import-step active" id="stepIndicator1" style="flex:1; text-align:center; padding:0.75rem; font-size:0.85rem; font-weight:600; border-bottom:3px solid var(--admin-primary); color: var(--admin-primary);">
                        <span class="step-number" style="display:inline-flex;width:22px;height:22px;border-radius:50%;background:var(--admin-primary);color:#fff;align-items:center;justify-content:center;font-size:0.7rem;margin-right:0.4rem;">1</span>Upload File
                    </div>
                    <div class="import-step" id="stepIndicator2" style="flex:1; text-align:center; padding:0.75rem; font-size:0.85rem; font-weight:600; border-bottom:3px solid #dee2e6; color: #adb5bd;">
                        <span class="step-number" style="display:inline-flex;width:22px;height:22px;border-radius:50%;background:#adb5bd;color:#fff;align-items:center;justify-content:center;font-size:0.7rem;margin-right:0.4rem;">2</span>Map Columns
                    </div>
                    <div class="import-step" id="stepIndicator3" style="flex:1; text-align:center; padding:0.75rem; font-size:0.85rem; font-weight:600; border-bottom:3px solid #dee2e6; color: #adb5bd;">
                        <span class="step-number" style="display:inline-flex;width:22px;height:22px;border-radius:50%;background:#adb5bd;color:#fff;align-items:center;justify-content:center;font-size:0.7rem;margin-right:0.4rem;">3</span>Preview & Import
                    </div>
                </div>

                <!-- Step 1: Upload File -->
                <div class="import-step-content p-4" id="step1Content">
                    <p class="text-muted mb-3">Upload a CSV or Excel file containing MCC/MNC network data. Your file can have any column layout — you'll map columns in the next step.</p>
                    <div class="upload-zone text-center p-4 rounded mb-3" id="dropZone" style="border:2px dashed #ccc; cursor:pointer; background:#fafbfc; transition: all 0.2s;">
                        <i class="fas fa-cloud-upload-alt fa-3x mb-3" style="color: var(--admin-accent);"></i>
                        <p class="mb-1 fw-semibold">Drag & drop your file here</p>
                        <p class="text-muted mb-2" style="font-size:0.85rem;">or click to browse</p>
                        <span class="badge bg-light text-dark border">.csv</span>
                        <span class="badge bg-light text-dark border">.xlsx</span>
                        <span class="badge bg-light text-dark border">.xls</span>
                        <input type="file" id="importFile" accept=".csv,.xlsx,.xls" style="display:none;">
                    </div>
                    <div id="fileInfo" class="d-none">
                        <div class="d-flex align-items-center gap-3 p-3 rounded" style="background:#f0f7ff; border:1px solid #c5ddf7;">
                            <i class="fas fa-file-alt fa-2x" style="color: var(--admin-accent);"></i>
                            <div class="flex-grow-1">
                                <div class="fw-semibold" id="fileName"></div>
                                <small class="text-muted" id="fileSize"></small>
                            </div>
                            <button class="btn btn-sm btn-outline-danger" onclick="clearFile()"><i class="fas fa-times"></i></button>
                        </div>
                    </div>
                    <div id="uploadError" class="alert alert-danger mt-3 d-none"></div>
                    <div id="uploadSpinner" class="text-center py-3 d-none">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-2 text-muted">Reading file...</p>
                    </div>
                </div>

                <!-- Step 2: Map Columns -->
                <div class="import-step-content p-4 d-none" id="step2Content">
                    <p class="text-muted mb-3">Map your file's columns to the required MCC/MNC fields. We've auto-detected likely matches where possible.</p>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">MCC <span class="text-danger">*</span></label>
                            <select class="form-select" id="mapMcc"></select>
                            <small class="text-muted">Mobile Country Code (3 digits)</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">MNC <span class="text-danger">*</span></label>
                            <select class="form-select" id="mapMnc"></select>
                            <small class="text-muted">Mobile Network Code (2-3 digits)</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Country Name <span class="text-danger">*</span></label>
                            <select class="form-select" id="mapCountryName"></select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Country ISO <span class="text-danger">*</span></label>
                            <select class="form-select" id="mapCountryIso"></select>
                            <small class="text-muted">2-letter code (e.g., GB, US)</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Network Name <span class="text-danger">*</span></label>
                            <select class="form-select" id="mapNetworkName"></select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Network Type <span class="text-muted">(optional)</span></label>
                            <select class="form-select" id="mapNetworkType"></select>
                            <small class="text-muted">Defaults to "mobile" if not mapped</small>
                        </div>
                    </div>
                    <div id="mappingError" class="alert alert-danger mt-3 d-none"></div>
                </div>

                <!-- Step 3: Preview & Import -->
                <div class="import-step-content p-4 d-none" id="step3Content">
                    <div id="previewSection">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h6 class="mb-0">Data Preview</h6>
                                <small class="text-muted">Showing first 5 rows of <span id="totalRowCount"></span> total</small>
                            </div>
                            <span class="badge" style="background: var(--admin-primary); font-size:0.8rem;" id="readyBadge"></span>
                        </div>
                        <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                            <table class="table table-sm table-bordered mb-0" id="previewTable">
                                <thead style="background:#f8f9fa; position:sticky; top:0;">
                                    <tr id="previewHeader"></tr>
                                </thead>
                                <tbody id="previewBody"></tbody>
                            </table>
                        </div>
                        <div class="alert alert-info mt-3 mb-0" style="font-size:0.85rem;">
                            <i class="fas fa-info-circle me-1"></i>
                            Duplicate MCC/MNC pairs will be <strong>updated</strong> with the new data. New entries will be <strong>created</strong>.
                        </div>
                    </div>
                    <div id="importProgress" class="d-none text-center py-4">
                        <div class="spinner-border text-primary mb-3" role="status" style="width:3rem;height:3rem;"></div>
                        <h6>Importing data...</h6>
                        <p class="text-muted" id="importProgressText">Please wait</p>
                    </div>
                    <div id="importResults" class="d-none">
                        <div class="text-center py-3">
                            <i class="fas fa-check-circle fa-3x mb-3" style="color:#198754;"></i>
                            <h5>Import Complete</h5>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-4 text-center">
                                <div class="p-3 rounded" style="background:#d4f4dd;">
                                    <div class="fs-4 fw-bold" style="color:#198754;" id="resultCreated">0</div>
                                    <small class="text-muted">Created</small>
                                </div>
                            </div>
                            <div class="col-4 text-center">
                                <div class="p-3 rounded" style="background:#fff3cd;">
                                    <div class="fs-4 fw-bold" style="color:#856404;" id="resultUpdated">0</div>
                                    <small class="text-muted">Updated</small>
                                </div>
                            </div>
                            <div class="col-4 text-center">
                                <div class="p-3 rounded" style="background:#f8d7da;">
                                    <div class="fs-4 fw-bold" style="color:#842029;" id="resultErrors">0</div>
                                    <small class="text-muted">Errors</small>
                                </div>
                            </div>
                        </div>
                        <div id="errorDetails" class="d-none">
                            <h6 class="text-danger"><i class="fas fa-exclamation-triangle me-1"></i>Error Details</h6>
                            <div class="table-responsive" style="max-height:150px; overflow-y:auto;">
                                <table class="table table-sm mb-0">
                                    <thead><tr><th>Row</th><th>Error</th></tr></thead>
                                    <tbody id="errorTableBody"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="btnBack" onclick="wizardBack()" style="display:none;">
                    <i class="fas fa-arrow-left me-1"></i>Back
                </button>
                <div class="flex-grow-1"></div>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="resetImportWizard()">Cancel</button>
                <button type="button" class="btn btn-admin-primary" id="btnNext" onclick="wizardNext()" disabled>
                    Next <i class="fas fa-arrow-right ms-1"></i>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openAddMccMncModal() {
    new bootstrap.Modal(document.getElementById('addMccMncModal')).show();
}

function submitAddMccMnc() {
    const form = document.getElementById('addMccMncForm');
    const formData = new FormData(form);

    fetch('{{ route('admin.mcc-mnc.store') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(Object.fromEntries(formData))
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred');
    });
}

function editNetwork(networkId) {
    fetch(`/admin/supplier-management/mcc-mnc/${networkId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('editNetworkId').value = data.id;
            document.getElementById('editMcc').value = data.mcc;
            document.getElementById('editMnc').value = data.mnc;
            document.getElementById('editNetworkName').value = data.network_name;
            document.getElementById('editNetworkType').value = data.network_type;
            new bootstrap.Modal(document.getElementById('editMccMncModal')).show();
        });
}

function submitEditMccMnc() {
    const networkId = document.getElementById('editNetworkId').value;
    const form = document.getElementById('editMccMncForm');
    const formData = new FormData(form);

    fetch(`/admin/supplier-management/mcc-mnc/${networkId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(Object.fromEntries(formData))
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    });
}

function toggleNetworkStatus(networkId) {
    if (confirm('Are you sure you want to change this network status?')) {
        fetch(`/admin/supplier-management/mcc-mnc/${networkId}/toggle`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }
}

function deleteNetwork(networkId) {
    if (confirm('Are you sure you want to delete this network? This may affect existing rate cards.')) {
        fetch(`/admin/supplier-management/mcc-mnc/${networkId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }
}

let importState = { step: 1, headers: [], preview: [], totalRows: 0, importId: '' };

function openImportModal() {
    resetImportWizard();
    new bootstrap.Modal(document.getElementById('importModal')).show();
}

function resetImportWizard() {
    importState = { step: 1, headers: [], preview: [], totalRows: 0, importId: '' };
    showStep(1);
    document.getElementById('fileInfo').classList.add('d-none');
    document.getElementById('dropZone').classList.remove('d-none');
    document.getElementById('uploadError').classList.add('d-none');
    document.getElementById('uploadSpinner').classList.add('d-none');
    document.getElementById('importResults').classList.add('d-none');
    document.getElementById('importProgress').classList.add('d-none');
    document.getElementById('previewSection').classList.remove('d-none');
    document.getElementById('importFile').value = '';
    document.getElementById('btnNext').disabled = true;
    document.getElementById('btnNext').innerHTML = 'Next <i class="fas fa-arrow-right ms-1"></i>';
}

function showStep(step) {
    importState.step = step;
    [1,2,3].forEach(s => {
        document.getElementById('step' + s + 'Content').classList.toggle('d-none', s !== step);
        const ind = document.getElementById('stepIndicator' + s);
        if (s <= step) {
            ind.style.borderBottomColor = 'var(--admin-primary)';
            ind.style.color = 'var(--admin-primary)';
            ind.querySelector('.step-number').style.background = 'var(--admin-primary)';
        } else {
            ind.style.borderBottomColor = '#dee2e6';
            ind.style.color = '#adb5bd';
            ind.querySelector('.step-number').style.background = '#adb5bd';
        }
    });
    document.getElementById('btnBack').style.display = step > 1 ? '' : 'none';
    if (step === 3) {
        document.getElementById('btnNext').innerHTML = '<i class="fas fa-upload me-1"></i>Import Data';
    } else {
        document.getElementById('btnNext').innerHTML = 'Next <i class="fas fa-arrow-right ms-1"></i>';
    }
}

const dropZone = document.getElementById('dropZone');
const fileInput = document.getElementById('importFile');

dropZone.addEventListener('click', () => fileInput.click());
dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.style.borderColor = 'var(--admin-accent)'; dropZone.style.background = '#e8f0fe'; });
dropZone.addEventListener('dragleave', () => { dropZone.style.borderColor = '#ccc'; dropZone.style.background = '#fafbfc'; });
dropZone.addEventListener('drop', e => {
    e.preventDefault();
    dropZone.style.borderColor = '#ccc'; dropZone.style.background = '#fafbfc';
    if (e.dataTransfer.files.length) { fileInput.files = e.dataTransfer.files; handleFileSelected(); }
});
fileInput.addEventListener('change', handleFileSelected);

function handleFileSelected() {
    const file = fileInput.files[0];
    if (!file) return;
    const ext = file.name.split('.').pop().toLowerCase();
    if (!['csv','xlsx','xls'].includes(ext)) {
        document.getElementById('uploadError').textContent = 'Please upload a .csv, .xlsx, or .xls file.';
        document.getElementById('uploadError').classList.remove('d-none');
        return;
    }
    document.getElementById('uploadError').classList.add('d-none');
    document.getElementById('fileName').textContent = file.name;
    document.getElementById('fileSize').textContent = formatBytes(file.size);
    document.getElementById('fileInfo').classList.remove('d-none');
    document.getElementById('dropZone').classList.add('d-none');
    uploadFile(file);
}

function clearFile() {
    fileInput.value = '';
    document.getElementById('fileInfo').classList.add('d-none');
    document.getElementById('dropZone').classList.remove('d-none');
    document.getElementById('btnNext').disabled = true;
    importState.headers = [];
}

function formatBytes(bytes) {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / 1048576).toFixed(1) + ' MB';
}

function uploadFile(file) {
    document.getElementById('uploadSpinner').classList.remove('d-none');
    document.getElementById('btnNext').disabled = true;
    const formData = new FormData();
    formData.append('file', file);

    fetch('{{ route('admin.mcc-mnc.parse-file') }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('uploadSpinner').classList.add('d-none');
        if (!data.success) {
            document.getElementById('uploadError').textContent = data.message;
            document.getElementById('uploadError').classList.remove('d-none');
            return;
        }
        importState.headers = data.headers;
        importState.preview = data.preview;
        importState.totalRows = data.totalRows;
        importState.importId = data.importId;
        document.getElementById('btnNext').disabled = false;
    })
    .catch(err => {
        document.getElementById('uploadSpinner').classList.add('d-none');
        document.getElementById('uploadError').textContent = 'Failed to upload file. Please try again.';
        document.getElementById('uploadError').classList.remove('d-none');
    });
}

function populateMappingDropdowns() {
    const fields = [
        { id: 'mapMcc', keywords: ['mcc'] },
        { id: 'mapMnc', keywords: ['mnc'] },
        { id: 'mapCountryName', keywords: ['country_name','country name','country','countryname'] },
        { id: 'mapCountryIso', keywords: ['country_iso','iso','country_code','countryiso','country iso','cc'] },
        { id: 'mapNetworkName', keywords: ['network_name','network name','operator','network','networkname','carrier'] },
        { id: 'mapNetworkType', keywords: ['network_type','type','network type','networktype'], optional: true },
    ];
    fields.forEach(field => {
        const sel = document.getElementById(field.id);
        sel.innerHTML = field.optional
            ? '<option value="">— Not mapped (defaults to mobile) —</option>'
            : '<option value="">— Select column —</option>';
        importState.headers.forEach((h, i) => {
            const opt = document.createElement('option');
            opt.value = i;
            opt.textContent = h;
            const lower = h.toLowerCase().trim();
            if (field.keywords.some(k => lower === k || lower.replace(/[\s_-]/g,'') === k.replace(/[\s_-]/g,''))) {
                opt.selected = true;
            }
            sel.appendChild(opt);
        });
    });
}

function buildPreviewTable() {
    const mapping = getMappingValues();
    const headerRow = document.getElementById('previewHeader');
    const tbody = document.getElementById('previewBody');
    headerRow.innerHTML = '<th>MCC</th><th>MNC</th><th>Country</th><th>ISO</th><th>Network</th><th>Type</th>';
    tbody.innerHTML = '';
    importState.preview.forEach(row => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><code>${row[mapping.mcc] || ''}</code></td>
            <td><code>${row[mapping.mnc] || ''}</code></td>
            <td>${row[mapping.country_name] || ''}</td>
            <td>${row[mapping.country_iso] || ''}</td>
            <td>${row[mapping.network_name] || ''}</td>
            <td>${mapping.network_type !== '' ? (row[mapping.network_type] || 'mobile') : 'mobile'}</td>`;
        tbody.appendChild(tr);
    });
    document.getElementById('totalRowCount').textContent = importState.totalRows;
    document.getElementById('readyBadge').textContent = importState.totalRows + ' rows ready';
}

function getMappingValues() {
    return {
        mcc: parseInt(document.getElementById('mapMcc').value),
        mnc: parseInt(document.getElementById('mapMnc').value),
        country_name: parseInt(document.getElementById('mapCountryName').value),
        country_iso: parseInt(document.getElementById('mapCountryIso').value),
        network_name: parseInt(document.getElementById('mapNetworkName').value),
        network_type: document.getElementById('mapNetworkType').value,
    };
}

function wizardNext() {
    if (importState.step === 1) {
        if (!importState.headers.length) return;
        populateMappingDropdowns();
        showStep(2);
        document.getElementById('btnNext').disabled = false;
    } else if (importState.step === 2) {
        const required = ['mapMcc','mapMnc','mapCountryName','mapCountryIso','mapNetworkName'];
        const missing = required.filter(id => document.getElementById(id).value === '');
        if (missing.length) {
            document.getElementById('mappingError').textContent = 'Please map all required fields (marked with *).';
            document.getElementById('mappingError').classList.remove('d-none');
            return;
        }
        document.getElementById('mappingError').classList.add('d-none');
        buildPreviewTable();
        showStep(3);
    } else if (importState.step === 3) {
        runImport();
    }
}

function wizardBack() {
    if (importState.step === 2) showStep(1);
    else if (importState.step === 3) showStep(2);
}

function runImport() {
    const mapping = getMappingValues();
    document.getElementById('previewSection').classList.add('d-none');
    document.getElementById('importProgress').classList.remove('d-none');
    document.getElementById('btnNext').disabled = true;
    document.getElementById('btnBack').style.display = 'none';

    fetch('{{ route('admin.mcc-mnc.import') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ importId: importState.importId, mapping })
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('importProgress').classList.add('d-none');
        document.getElementById('importResults').classList.remove('d-none');
        if (data.success) {
            document.getElementById('resultCreated').textContent = data.imported;
            document.getElementById('resultUpdated').textContent = data.updated;
            document.getElementById('resultErrors').textContent = data.errors.length;
            if (data.errors.length) {
                document.getElementById('errorDetails').classList.remove('d-none');
                const errBody = document.getElementById('errorTableBody');
                errBody.innerHTML = '';
                data.errors.slice(0, 20).forEach(e => {
                    errBody.innerHTML += `<tr><td>${e.row}</td><td>${e.error}</td></tr>`;
                });
            }
            document.getElementById('btnNext').innerHTML = '<i class="fas fa-check me-1"></i>Done';
            document.getElementById('btnNext').disabled = false;
            document.getElementById('btnNext').onclick = () => location.reload();
        } else {
            document.getElementById('resultCreated').textContent = '0';
            document.getElementById('resultUpdated').textContent = '0';
            document.getElementById('resultErrors').textContent = '1';
            document.getElementById('errorDetails').classList.remove('d-none');
            document.getElementById('errorTableBody').innerHTML = `<tr><td>-</td><td>${data.message || 'Unknown error occurred'}</td></tr>`;
            document.getElementById('btnNext').innerHTML = '<i class="fas fa-redo me-1"></i>Try Again';
            document.getElementById('btnNext').disabled = false;
            document.getElementById('btnNext').onclick = () => { resetImportWizard(); };
        }
    })
    .catch(err => {
        document.getElementById('importProgress').classList.add('d-none');
        document.getElementById('importResults').classList.remove('d-none');
        document.getElementById('resultCreated').textContent = '0';
        document.getElementById('resultUpdated').textContent = '0';
        document.getElementById('resultErrors').textContent = '1';
        document.getElementById('errorDetails').classList.remove('d-none');
        document.getElementById('errorTableBody').innerHTML = `<tr><td>-</td><td>Network error. Please check your connection and try again.</td></tr>`;
        document.getElementById('btnNext').innerHTML = '<i class="fas fa-redo me-1"></i>Try Again';
        document.getElementById('btnNext').disabled = false;
        document.getElementById('btnNext').onclick = () => { resetImportWizard(); };
    });
}

function filterNetworks() {
    const countryFilter = document.getElementById('filterCountry').value;
    const typeFilter = document.getElementById('filterType').value;
    const statusFilter = document.getElementById('filterStatus').value;
    const searchText = document.getElementById('searchNetwork').value.toLowerCase();
    const rows = document.querySelectorAll('#mccMncTableBody tr[data-country]');

    rows.forEach(row => {
        let show = true;

        if (countryFilter && row.dataset.country !== countryFilter) show = false;
        if (typeFilter && row.dataset.type !== typeFilter) show = false;
        if (statusFilter && row.dataset.status !== statusFilter) show = false;
        if (searchText && !row.dataset.search.includes(searchText)) show = false;

        row.style.display = show ? '' : 'none';
    });
}
</script>
@endpush
