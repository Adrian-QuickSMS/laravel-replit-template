@extends('layouts.admin')

@section('title', 'Suppliers - Supplier Management')

@push('styles')
<style>
:root {
    --admin-primary: #1e3a5f;
    --admin-secondary: #2d5a87;
    --admin-accent: #4a90d9;
}

.supplier-table-card {
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

.status-badge.suspended {
    background: #ffe0e0;
    color: #dc3545;
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
        <li class="breadcrumb-item active">Suppliers</li>
    </ol>
</div>

<div class="page-header">
    <div>
        <h2>Supplier Library</h2>
        <p>Manage SMS and RCS supplier configurations</p>
    </div>
    <div>
        <button class="btn btn-admin-primary" onclick="openAddSupplierModal()">
            <i class="fas fa-plus me-2"></i>Add Supplier
        </button>
    </div>
</div>

<div class="supplier-table-card">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Supplier Name</th>
                    <th>Code</th>
                    <th>Status</th>
                    <th>Currency</th>
                    <th>Billing Method</th>
                    <th>Gateways</th>
                    <th>Last Rate Update</th>
                    <th style="width: 100px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($suppliers as $supplier)
                <tr>
                    <td><strong>{{ $supplier->name }}</strong></td>
                    <td><code>{{ $supplier->supplier_code }}</code></td>
                    <td>
                        <span class="status-badge {{ $supplier->status }}">
                            <i class="fas fa-circle" style="font-size: 6px;"></i>
                            {{ ucfirst($supplier->status) }}
                        </span>
                    </td>
                    <td>{{ $supplier->default_currency }}</td>
                    <td>{{ ucfirst($supplier->default_billing_method) }}</td>
                    <td>{{ $supplier->gateway_count }}</td>
                    <td>{{ $supplier->last_rate_update ? $supplier->last_rate_update->format('d M Y H:i') : '—' }}</td>
                    <td>
                        <button class="action-menu-btn" data-supplier-id="{{ $supplier->id }}">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-4 text-muted">
                        <i class="fas fa-inbox fa-2x mb-2"></i>
                        <p>No suppliers found</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Add Supplier Modal -->
<div class="modal fade" id="addSupplierModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Supplier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addSupplierForm">
                    <div class="mb-3">
                        <label class="form-label">Supplier Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Default Currency <span class="text-danger">*</span></label>
                            <select class="form-select" name="default_currency" required>
                                <option value="GBP">GBP</option>
                                <option value="EUR">EUR</option>
                                <option value="USD">USD</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Billing Method <span class="text-danger">*</span></label>
                            <select class="form-select" name="default_billing_method" required>
                                <option value="delivered">Delivered</option>
                                <option value="submitted">Submitted</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contact Name</label>
                        <input type="text" class="form-control" name="contact_name">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contact Email</label>
                        <input type="email" class="form-control" name="contact_email">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contact Phone</label>
                        <input type="text" class="form-control" name="contact_phone">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" name="notes" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-admin-primary" onclick="submitAddSupplier()">Create Supplier</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openAddSupplierModal() {
    new bootstrap.Modal(document.getElementById('addSupplierModal')).show();
}

function submitAddSupplier() {
    const form = document.getElementById('addSupplierForm');
    const formData = new FormData(form);

    fetch('{{ route('admin.suppliers.store') }}', {
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
</script>
@endpush
