@extends('layouts.admin')

@section('title', 'Rate Cards - Supplier Management')

@push('styles')
<style>
:root {
    --admin-primary: #1e3a5f;
    --admin-secondary: #2d5a87;
    --admin-accent: #4a90d9;
}

.rate-card-table {
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

.rate-value {
    font-family: 'Courier New', monospace;
    font-weight: 600;
    color: #1e3a5f;
}

.version-badge {
    background: #e3f2fd;
    color: #1976d2;
    padding: 0.15rem 0.5rem;
    border-radius: 8px;
    font-size: 0.7rem;
    font-weight: 500;
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
</style>
@endpush

@section('content')
<div class="page-titles">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
        <li class="breadcrumb-item"><a href="#">Supplier Management</a></li>
        <li class="breadcrumb-item active">Rate Cards</li>
    </ol>
</div>

<div class="page-header">
    <div>
        <h2>Rate Card Management</h2>
        <p>View and manage supplier pricing rates</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.rate-cards.upload') }}" class="btn btn-admin-primary">
            <i class="fas fa-upload me-2"></i>Upload Rates
        </a>
        <button class="btn btn-outline-primary" onclick="exportRates()">
            <i class="fas fa-download me-2"></i>Export
        </button>
    </div>
</div>

<div class="filter-toolbar">
    <div class="row g-3">
        <div class="col-md-3">
            <select class="form-select" id="filterSupplier" onchange="filterRates()">
                <option value="">All Suppliers</option>
                @foreach($suppliers as $supplier)
                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <select class="form-select" id="filterGateway" onchange="filterRates()">
                <option value="">All Gateways</option>
                @foreach($gateways as $gateway)
                <option value="{{ $gateway->id }}">{{ $gateway->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select class="form-select" id="filterCountry" onchange="filterRates()">
                <option value="">All Countries</option>
                @foreach($countries as $country)
                <option value="{{ $country }}">{{ $country }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select class="form-select" id="filterStatus" onchange="filterRates()">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
        <div class="col-md-2">
            <input type="text" class="form-control" id="searchRate" placeholder="Search MCC/MNC..." onkeyup="filterRates()">
        </div>
    </div>
</div>

<div class="rate-card-table">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Network</th>
                    <th>MCC/MNC</th>
                    <th>Gateway</th>
                    <th>Product</th>
                    <th>Rate (Native)</th>
                    <th>Rate (GBP)</th>
                    <th>Valid From</th>
                    <th>Valid To</th>
                    <th>Version</th>
                    <th>Status</th>
                    <th style="width: 100px;">Actions</th>
                </tr>
            </thead>
            <tbody id="rateTableBody">
                @forelse($rateCards as $rate)
                <tr data-supplier-id="{{ $rate->supplier_id }}"
                    data-gateway-id="{{ $rate->gateway_id }}"
                    data-country="{{ $rate->country_iso }}"
                    data-status="{{ $rate->active ? 'active' : 'inactive' }}"
                    data-search="{{ strtolower($rate->mcc . $rate->mnc . $rate->network_name) }}">
                    <td>
                        <strong>{{ $rate->network_name }}</strong>
                        <br><small class="text-muted">{{ $rate->country_name }}</small>
                    </td>
                    <td><code>{{ $rate->mcc }}/{{ $rate->mnc }}</code></td>
                    <td>{{ $rate->gateway->name }}</td>
                    <td>
                        <span class="badge bg-secondary">{{ $rate->product_type }}</span>
                    </td>
                    <td class="rate-value">{{ $rate->currency }} {{ number_format($rate->native_rate, 4) }}</td>
                    <td class="rate-value">£{{ number_format($rate->gbp_rate, 4) }}</td>
                    <td>{{ \Carbon\Carbon::parse($rate->valid_from)->format('d M Y') }}</td>
                    <td>{{ $rate->valid_to ? \Carbon\Carbon::parse($rate->valid_to)->format('d M Y') : '—' }}</td>
                    <td><span class="version-badge">v{{ $rate->version }}</span></td>
                    <td>
                        <span class="status-badge {{ $rate->active ? 'active' : 'inactive' }}">
                            <i class="fas fa-circle" style="font-size: 6px;"></i>
                            {{ $rate->active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>
                        <div class="dropdown">
                            <button class="action-menu-btn" data-bs-toggle="dropdown">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#" onclick="editRate({{ $rate->id }})"><i class="fas fa-edit me-2"></i>Edit Rate</a></li>
                                <li><a class="dropdown-item" href="#" onclick="viewHistory({{ $rate->id }})"><i class="fas fa-history me-2"></i>Version History</a></li>
                                <li><a class="dropdown-item" href="#" onclick="scheduleRate({{ $rate->id }})"><i class="fas fa-calendar me-2"></i>Schedule Change</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="#" onclick="deactivateRate({{ $rate->id }})"><i class="fas fa-ban me-2"></i>Deactivate</a></li>
                            </ul>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="11" class="text-center py-4 text-muted">
                        <i class="fas fa-inbox fa-2x mb-2"></i>
                        <p>No rate cards found</p>
                        <a href="{{ route('admin.rate-cards.upload') }}" class="btn btn-sm btn-admin-primary mt-2">
                            <i class="fas fa-upload me-1"></i>Upload Rates
                        </a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($rateCards->hasPages())
<div class="d-flex justify-content-between align-items-center mt-3">
    <div class="text-muted">
        Showing {{ $rateCards->firstItem() }} to {{ $rateCards->lastItem() }} of {{ $rateCards->total() }} rates
    </div>
    <div>
        {{ $rateCards->links() }}
    </div>
</div>
@endif

<!-- Edit Rate Modal -->
<div class="modal fade" id="editRateModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Rate Card</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Editing will create a new version. The current rate will be marked as inactive.
                </div>
                <form id="editRateForm">
                    <input type="hidden" name="rate_id" id="editRateId">
                    <div class="mb-3">
                        <label class="form-label">Network</label>
                        <input type="text" class="form-control" id="editRateNetwork" readonly>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">MCC</label>
                            <input type="text" class="form-control" id="editRateMcc" readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">MNC</label>
                            <input type="text" class="form-control" id="editRateMnc" readonly>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">New Rate <span class="text-danger">*</span></label>
                            <input type="number" step="0.0001" class="form-control" name="native_rate" id="editRateValue" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Currency</label>
                            <input type="text" class="form-control" id="editRateCurrency" readonly>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Valid From <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="valid_from" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Change Reason <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="change_reason" rows="2" required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-admin-primary" onclick="submitEditRate()">Create New Version</button>
            </div>
        </div>
    </div>
</div>

<!-- Version History Modal -->
<div class="modal fade" id="versionHistoryModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Rate Version History</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="versionHistoryContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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

function filterRates() {
    const supplierFilter = document.getElementById('filterSupplier').value;
    const gatewayFilter = document.getElementById('filterGateway').value;
    const countryFilter = document.getElementById('filterCountry').value;
    const statusFilter = document.getElementById('filterStatus').value;
    const searchText = document.getElementById('searchRate').value.toLowerCase();
    const rows = document.querySelectorAll('#rateTableBody tr[data-supplier-id]');

    rows.forEach(row => {
        let show = true;

        if (supplierFilter && row.dataset.supplierId !== supplierFilter) show = false;
        if (gatewayFilter && row.dataset.gatewayId !== gatewayFilter) show = false;
        if (countryFilter && row.dataset.country !== countryFilter) show = false;
        if (statusFilter && row.dataset.status !== statusFilter) show = false;
        if (searchText && !row.dataset.search.includes(searchText)) show = false;

        row.style.display = show ? '' : 'none';
    });
}

function editRate(rateId) {
    fetch(`/admin/supplier-management/rate-cards/${rateId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('editRateId').value = data.id;
            document.getElementById('editRateNetwork').value = data.network_name;
            document.getElementById('editRateMcc').value = data.mcc;
            document.getElementById('editRateMnc').value = data.mnc;
            document.getElementById('editRateValue').value = data.native_rate;
            document.getElementById('editRateCurrency').value = data.currency;
            new bootstrap.Modal(document.getElementById('editRateModal')).show();
        });
}

function submitEditRate() {
    const rateId = document.getElementById('editRateId').value;
    const form = document.getElementById('editRateForm');
    const formData = new FormData(form);

    fetch(`/admin/supplier-management/rate-cards/${rateId}`, {
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

function viewHistory(rateId) {
    const modal = new bootstrap.Modal(document.getElementById('versionHistoryModal'));
    modal.show();

    fetch(`/admin/supplier-management/rate-cards/${rateId}/history`)
        .then(response => response.json())
        .then(data => {
            let html = '<div class="timeline">';
            data.forEach((version, index) => {
                html += `
                    <div class="timeline-item mb-3 ${index === 0 ? 'border-start border-3 border-success ps-3' : 'ps-3'}">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <span class="version-badge">v${escapeHtml(version.version)}</span>
                                ${index === 0 ? '<span class="badge bg-success ms-2">Current</span>' : ''}
                            </div>
                            <small class="text-muted">${escapeHtml(version.created_at)}</small>
                        </div>
                        <div class="rate-value">${escapeHtml(version.currency)} ${parseFloat(version.native_rate).toFixed(4)} → £${parseFloat(version.gbp_rate).toFixed(4)}</div>
                        <div class="text-muted small">Valid: ${escapeHtml(version.valid_from)} ${version.valid_to ? '→ ' + escapeHtml(version.valid_to) : '(ongoing)'}</div>
                        ${version.change_reason ? '<div class="text-muted small mt-1"><i class="fas fa-comment me-1"></i>' + escapeHtml(version.change_reason) + '</div>' : ''}
                        ${version.created_by ? '<div class="text-muted small"><i class="fas fa-user me-1"></i>' + escapeHtml(version.created_by) + '</div>' : ''}
                    </div>
                `;
            });
            html += '</div>';
            document.getElementById('versionHistoryContent').innerHTML = html;
        });
}

function scheduleRate(rateId) {
    alert('Rate scheduling feature - coming soon');
}

function deactivateRate(rateId) {
    if (confirm('Are you sure you want to deactivate this rate?')) {
        fetch(`/admin/supplier-management/rate-cards/${rateId}/deactivate`, {
            method: 'POST',
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

function exportRates() {
    const params = new URLSearchParams({
        supplier_id: document.getElementById('filterSupplier').value,
        gateway_id: document.getElementById('filterGateway').value,
        country: document.getElementById('filterCountry').value,
        status: document.getElementById('filterStatus').value
    });
    window.location.href = `/admin/supplier-management/rate-cards/export?${params}`;
}
</script>
@endpush
