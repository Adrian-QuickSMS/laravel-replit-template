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

<!-- Bulk Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bulk Import MCC/MNC Data</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <strong>Required CSV Format:</strong>
                    <code>mcc,mnc,country_name,country_iso,network_name,network_type</code>
                    <br><small>Example: 234,10,United Kingdom,GB,O2,mobile</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Upload CSV File</label>
                    <input type="file" class="form-control" id="importFile" accept=".csv">
                </div>

                <div class="mt-3">
                    <a href="/downloads/mcc-mnc-template.csv" class="btn btn-sm btn-outline-primary" download>
                        <i class="fas fa-download me-1"></i>Download Template
                    </a>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-admin-primary" onclick="submitImport()">Import Data</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function escapeHtml(str) {
    if (str === null || str === undefined) return '';
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(String(str)));
    return div.innerHTML;
}

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
            alert('Error: ' + escapeHtml(data.message));
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
            alert('Error: ' + escapeHtml(data.message));
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

function openImportModal() {
    new bootstrap.Modal(document.getElementById('importModal')).show();
}

function submitImport() {
    const fileInput = document.getElementById('importFile');
    if (!fileInput.files[0]) {
        alert('Please select a file');
        return;
    }

    const formData = new FormData();
    formData.append('file', fileInput.files[0]);

    fetch('{{ route('admin.mcc-mnc.import') }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`Successfully imported ${escapeHtml(data.imported)} networks`);
            location.reload();
        } else {
            alert('Import failed: ' + escapeHtml(data.message));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred during import');
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
