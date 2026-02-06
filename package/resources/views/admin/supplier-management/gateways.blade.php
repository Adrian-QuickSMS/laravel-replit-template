@extends('layouts.admin')

@section('title', 'Gateways - Supplier Management')

@push('styles')
<style>
:root {
    --admin-primary: #1e3a5f;
    --admin-secondary: #2d5a87;
    --admin-accent: #4a90d9;
}

.gateway-table-card {
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

.filter-toolbar {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
}
</style>
@endpush

@section('content')
<div class="page-titles">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
        <li class="breadcrumb-item"><a href="#">Supplier Management</a></li>
        <li class="breadcrumb-item active">Gateways</li>
    </ol>
</div>

<div class="page-header">
    <div>
        <h2>Gateway Management</h2>
        <p>Manage supplier routes and connections</p>
    </div>
    <div>
        <button class="btn btn-admin-primary" onclick="openAddGatewayModal()">
            <i class="fas fa-plus me-2"></i>Add Gateway
        </button>
    </div>
</div>

<div class="filter-toolbar">
    <div class="row g-3">
        <div class="col-md-4">
            <select class="form-select" id="filterSupplier" onchange="filterGateways()">
                <option value="">All Suppliers</option>
                @foreach($suppliers as $supplier)
                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <select class="form-select" id="filterStatus" onchange="filterGateways()">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
        <div class="col-md-4">
            <input type="text" class="form-control" id="searchGateway" placeholder="Search gateways..." onkeyup="filterGateways()">
        </div>
    </div>
</div>

<div class="gateway-table-card">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Gateway Name</th>
                    <th>Gateway Code</th>
                    <th>Supplier</th>
                    <th>Status</th>
                    <th>Currency</th>
                    <th>Billing Method</th>
                    <th>Last Rate Update</th>
                    <th style="width: 100px;">Actions</th>
                </tr>
            </thead>
            <tbody id="gatewayTableBody">
                @forelse($gateways as $gateway)
                <tr data-supplier-id="{{ $gateway->supplier_id }}" data-status="{{ $gateway->active ? 'active' : 'inactive' }}" data-search="{{ strtolower($gateway->name . ' ' . $gateway->gateway_code) }}">
                    <td><strong>{{ $gateway->name }}</strong></td>
                    <td><code>{{ $gateway->gateway_code }}</code></td>
                    <td>{{ $gateway->supplier->name }}</td>
                    <td>
                        <span class="status-badge {{ $gateway->active ? 'active' : 'inactive' }}">
                            <i class="fas fa-circle" style="font-size: 6px;"></i>
                            {{ $gateway->active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>{{ $gateway->currency }}</td>
                    <td>{{ ucfirst($gateway->billing_method) }}</td>
                    <td>{{ $gateway->last_rate_update ? $gateway->last_rate_update->format('d M Y H:i') : '—' }}</td>
                    <td>
                        <div class="dropdown">
                            <button class="action-menu-btn" data-bs-toggle="dropdown">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#" onclick="editGateway({{ $gateway->id }})"><i class="fas fa-edit me-2"></i>Edit</a></li>
                                <li><a class="dropdown-item" href="#" onclick="toggleGatewayStatus({{ $gateway->id }})"><i class="fas fa-toggle-{{ $gateway->active ? 'off' : 'on' }} me-2"></i>{{ $gateway->active ? 'Deactivate' : 'Activate' }}</a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.rate-cards.index', ['gateway_id' => $gateway->id]) }}"><i class="fas fa-dollar-sign me-2"></i>View Rates</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="#" onclick="deleteGateway({{ $gateway->id }})"><i class="fas fa-trash me-2"></i>Delete</a></li>
                            </ul>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-4 text-muted">
                        <i class="fas fa-inbox fa-2x mb-2"></i>
                        <p>No gateways found</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Add Gateway Modal -->
<div class="modal fade" id="addGatewayModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Gateway</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addGatewayForm">
                    <div class="mb-3">
                        <label class="form-label">Supplier <span class="text-danger">*</span></label>
                        <select class="form-select" name="supplier_id" required>
                            <option value="">Select supplier...</option>
                            @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Gateway Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Gateway Code <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="gateway_code" required>
                        <small class="text-muted">Unique identifier (e.g., SUPP1_UK_PREM)</small>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Currency <span class="text-danger">*</span></label>
                            <select class="form-select" name="currency" required>
                                <option value="GBP">GBP</option>
                                <option value="EUR">EUR</option>
                                <option value="USD">USD</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Billing Method <span class="text-danger">*</span></label>
                            <select class="form-select" name="billing_method" required>
                                <option value="delivered">Delivered</option>
                                <option value="submitted">Submitted</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">FX Source</label>
                        <input type="text" class="form-control" name="fx_source" value="ECB">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" name="notes" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-admin-primary" onclick="submitAddGateway()">Create Gateway</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Gateway Modal -->
<div class="modal fade" id="editGatewayModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Gateway</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editGatewayForm">
                    <input type="hidden" name="gateway_id" id="editGatewayId">
                    <div class="mb-3">
                        <label class="form-label">Gateway Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" id="editGatewayName" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Currency <span class="text-danger">*</span></label>
                            <select class="form-select" name="currency" id="editGatewayCurrency" required>
                                <option value="GBP">GBP</option>
                                <option value="EUR">EUR</option>
                                <option value="USD">USD</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Billing Method <span class="text-danger">*</span></label>
                            <select class="form-select" name="billing_method" id="editGatewayBillingMethod" required>
                                <option value="delivered">Delivered</option>
                                <option value="submitted">Submitted</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" name="notes" id="editGatewayNotes" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-admin-primary" onclick="submitEditGateway()">Save Changes</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openAddGatewayModal() {
    new bootstrap.Modal(document.getElementById('addGatewayModal')).show();
}

function submitAddGateway() {
    const form = document.getElementById('addGatewayForm');
    const formData = new FormData(form);

    fetch('{{ route('admin.gateways.store') }}', {
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

function editGateway(gatewayId) {
    fetch(`/admin/supplier-management/gateways/${gatewayId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('editGatewayId').value = data.id;
            document.getElementById('editGatewayName').value = data.name;
            document.getElementById('editGatewayCurrency').value = data.currency;
            document.getElementById('editGatewayBillingMethod').value = data.billing_method;
            document.getElementById('editGatewayNotes').value = data.notes || '';
            new bootstrap.Modal(document.getElementById('editGatewayModal')).show();
        });
}

function submitEditGateway() {
    const gatewayId = document.getElementById('editGatewayId').value;
    const form = document.getElementById('editGatewayForm');
    const formData = new FormData(form);

    fetch(`/admin/supplier-management/gateways/${gatewayId}`, {
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

function toggleGatewayStatus(gatewayId) {
    if (confirm('Are you sure you want to change this gateway status?')) {
        fetch(`/admin/supplier-management/gateways/${gatewayId}/toggle`, {
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

function deleteGateway(gatewayId) {
    if (confirm('Are you sure you want to delete this gateway? This will also delete all associated rate cards.')) {
        fetch(`/admin/supplier-management/gateways/${gatewayId}`, {
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

function filterGateways() {
    const supplierFilter = document.getElementById('filterSupplier').value;
    const statusFilter = document.getElementById('filterStatus').value;
    const searchText = document.getElementById('searchGateway').value.toLowerCase();
    const rows = document.querySelectorAll('#gatewayTableBody tr[data-supplier-id]');

    rows.forEach(row => {
        let show = true;

        if (supplierFilter && row.dataset.supplierId !== supplierFilter) {
            show = false;
        }

        if (statusFilter && row.dataset.status !== statusFilter) {
            show = false;
        }

        if (searchText && !row.dataset.search.includes(searchText)) {
            show = false;
        }

        row.style.display = show ? '' : 'none';
    });
}
</script>
@endpush
