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

<form method="GET" action="{{ route('admin.rate-cards.index') }}" id="filterForm" class="filter-toolbar">
    <div class="row g-3">
        <div class="col-md-3">
            <select class="form-select" name="supplier_id" onchange="this.form.submit()">
                <option value="">All Suppliers</option>
                @foreach($suppliers as $supplier)
                <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <select class="form-select" name="gateway_id" onchange="this.form.submit()">
                <option value="">All Gateways</option>
                @foreach($gateways as $gateway)
                <option value="{{ $gateway->id }}" {{ request('gateway_id') == $gateway->id ? 'selected' : '' }}>{{ $gateway->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select class="form-select" name="country_iso" onchange="this.form.submit()">
                <option value="">All Countries</option>
                @foreach($countries as $country)
                <option value="{{ $country->country_iso }}" {{ request('country_iso') == $country->country_iso ? 'selected' : '' }}>{{ $country->country_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select class="form-select" name="status" onchange="this.form.submit()">
                <option value="">All Status</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>
        <div class="col-md-2">
            <input type="text" class="form-control" name="search" placeholder="Search MCC/MNC..." value="{{ request('search') }}" onchange="this.form.submit()">
        </div>
    </div>
</form>

<div class="rate-card-table">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Supplier</th>
                    <th>Country</th>
                    <th>Network</th>
                    <th>Product</th>
                    <th>Rate Updated</th>
                    <th>Billing</th>
                    <th>Rate (GBP)</th>
                    <th style="width: 80px;">Actions</th>
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
                        <strong>{{ $rate->supplier->name ?? '—' }}</strong>
                        <br><small class="text-muted">{{ $rate->gateway->name ?? '—' }}</small>
                    </td>
                    <td>{{ $rate->country_name ?? '—' }}</td>
                    <td>
                        <strong>{{ $rate->network_name }}</strong>
                        <br><small class="text-muted">{{ $rate->mcc }}/{{ $rate->mnc }}</small>
                    </td>
                    <td>
                        <span class="badge bg-secondary">{{ $rate->product_type }}</span>
                    </td>
                    <td>{{ $rate->updated_at ? $rate->updated_at->format('d-m-Y') : '—' }}</td>
                    <td>
                        @if($rate->billing_method === 'delivered')
                            <span class="badge" style="background: #d1fae5; color: #065f46;">Delivered</span>
                        @else
                            <span class="badge" style="background: #fef3c7; color: #92400e;">Submitted</span>
                        @endif
                    </td>
                    <td class="rate-value">£{{ number_format($rate->gbp_rate, 4) }}</td>
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
                    <td colspan="8" class="text-center py-4 text-muted">
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
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Billing Method</label>
                            <select class="form-select" name="billing_method" id="editRateBillingMethod">
                                <option value="submitted">Submitted</option>
                                <option value="delivered">Delivered</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Valid From <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="valid_from" required>
                        </div>
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

function editRate(rateId) {
    fetch(`/admin/supplier-management/rate-cards/${rateId}`, {
        headers: { 'Accept': 'application/json' }
    })
    .then(response => {
        if (!response.ok) throw new Error('Failed to load rate card');
        return response.json();
    })
    .then(data => {
        document.getElementById('editRateId').value = data.id;
        document.getElementById('editRateNetwork').value = data.network_name;
        document.getElementById('editRateMcc').value = data.mcc;
        document.getElementById('editRateMnc').value = data.mnc;
        document.getElementById('editRateValue').value = data.native_rate;
        document.getElementById('editRateCurrency').value = data.currency;
        document.getElementById('editRateBillingMethod').value = data.billing_method || 'submitted';
        new bootstrap.Modal(document.getElementById('editRateModal')).show();
    })
    .catch(err => {
        alert('Could not load rate card details. Please try again.');
        console.error(err);
    });
}

function submitEditRate() {
    const rateId = document.getElementById('editRateId').value;
    const form = document.getElementById('editRateForm');
    const formData = new FormData(form);

    const payload = Object.fromEntries(formData);
    if (payload.change_reason && !payload.reason) {
        payload.reason = payload.change_reason;
    }

    fetch(`/admin/supplier-management/rate-cards/${rateId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(payload)
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
                                <span class="version-badge">v${version.version}</span>
                                ${index === 0 ? '<span class="badge bg-success ms-2">Current</span>' : ''}
                            </div>
                            <small class="text-muted">${version.created_at}</small>
                        </div>
                        <div class="rate-value">${version.currency} ${parseFloat(version.native_rate).toFixed(4)} → £${parseFloat(version.gbp_rate).toFixed(4)}</div>
                        <div class="text-muted small">Valid: ${version.valid_from} ${version.valid_to ? '→ ' + version.valid_to : '(ongoing)'}</div>
                        ${version.change_reason ? '<div class="text-muted small mt-1"><i class="fas fa-comment me-1"></i>' + version.change_reason + '</div>' : ''}
                        ${version.created_by ? '<div class="text-muted small"><i class="fas fa-user me-1"></i>' + version.created_by + '</div>' : ''}
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
    const form = document.getElementById('filterForm');
    const formData = new FormData(form);
    const params = new URLSearchParams();
    for (const [key, value] of formData.entries()) {
        if (value) params.set(key, value);
    }
    window.location.href = `/admin/supplier-management/rate-cards/export?${params}`;
}
</script>
@endpush
