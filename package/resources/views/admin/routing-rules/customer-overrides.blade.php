@extends('layouts.admin')

@section('title', 'Customer Overrides - Routing Rules')

@push('styles')
<style>
:root {
    --admin-primary: #1e3a5f;
}

.override-card {
    background: #fff;
    border: 1px solid #dde4ea;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 1rem;
}

.override-card.expired {
    opacity: 0.7;
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

.status-badge.expired {
    background: #e0e0e0;
    color: #6c757d;
}

.status-badge.cancelled {
    background: #ffe0e0;
    color: #dc3545;
}

.scope-badge {
    background: #e3f2fd;
    color: #1976d2;
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
    font-size: 0.7rem;
    font-weight: 500;
}
</style>
@endpush

@section('content')
<div class="page-titles">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
        <li class="breadcrumb-item"><a href="#">Routing Rules</a></li>
        <li class="breadcrumb-item active">Customer Overrides</li>
    </ol>
</div>

<div class="page-header">
    <div>
        <h2>Customer Routing Overrides</h2>
        <p>Force specific routing for customer accounts</p>
    </div>
    <div>
        <button class="btn btn-admin-primary" onclick="openCreateOverrideModal()">
            <i class="fas fa-plus me-2"></i>Create Override
        </button>
    </div>
</div>

<!-- Status Filter -->
<div class="mb-3">
    <div class="btn-group" role="group">
        <a href="{{ route('admin.routing.customer-overrides', ['status' => 'active']) }}" class="btn btn-outline-primary {{ $status === 'active' ? 'active' : '' }}">Active</a>
        <a href="{{ route('admin.routing.customer-overrides', ['status' => 'expired']) }}" class="btn btn-outline-primary {{ $status === 'expired' ? 'active' : '' }}">Expired</a>
        <a href="{{ route('admin.routing.customer-overrides', ['status' => 'all']) }}" class="btn btn-outline-primary {{ $status === 'all' ? 'active' : '' }}">All</a>
    </div>
</div>

@forelse($overrides as $override)
<div class="override-card {{ $override->status }}">
    <div class="row">
        <div class="col-md-8">
            <div class="d-flex align-items-center gap-2 mb-2">
                <h5 class="mb-0">{{ $override->customer_name }}</h5>
                <span class="status-badge {{ $override->status }}">
                    <i class="fas fa-circle" style="font-size: 6px;"></i>
                    {{ ucfirst($override->status) }}
                </span>
                <span class="scope-badge">{{ $override->product_type }}</span>
                <span class="scope-badge">{{ $override->scope_type }}</span>
                @if($override->scope_value)
                <span class="scope-badge">{{ $override->scope_value }}</span>
                @endif
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="text-muted small">Forced Gateway</div>
                    <div><strong>{{ $override->forcedGateway->name }}</strong></div>
                    <div class="text-muted small">{{ $override->forcedGateway->supplier->name }}</div>
                </div>

                @if($override->secondaryGateway)
                <div class="col-md-6">
                    <div class="text-muted small">Secondary Gateway</div>
                    <div><strong>{{ $override->secondaryGateway->name }}</strong></div>
                    <div class="text-muted small">{{ $override->secondaryGateway->supplier->name }}</div>
                </div>
                @endif

                <div class="col-md-6">
                    <div class="text-muted small">Period</div>
                    <div>{{ $override->start_datetime->format('d M Y H:i') }}</div>
                    <div>→ {{ $override->end_datetime ? $override->end_datetime->format('d M Y H:i') : 'Indefinite' }}</div>
                </div>

                <div class="col-md-6">
                    <div class="text-muted small">Created By</div>
                    <div>{{ $override->created_by }}</div>
                    <div class="text-muted small">{{ $override->created_at->format('d M Y H:i') }}</div>
                </div>

                @if($override->reason)
                <div class="col-12">
                    <div class="text-muted small">Reason</div>
                    <div>{{ $override->reason }}</div>
                </div>
                @endif
            </div>
        </div>

        <div class="col-md-4 text-end">
            @if($override->status === 'active')
            <div class="btn-group-vertical" role="group">
                <button class="btn btn-sm btn-outline-secondary" onclick="editOverride({{ $override->id }})">Edit End Date</button>
                <button class="btn btn-sm btn-outline-danger" onclick="cancelOverride({{ $override->id }})">Cancel Override</button>
            </div>
            @endif
        </div>
    </div>
</div>
@empty
<div class="text-center py-5 text-muted">
    <i class="fas fa-inbox fa-3x mb-3"></i>
    <p>No customer overrides found</p>
</div>
@endforelse

{{ $overrides->links() }}

<!-- Create Override Modal -->
<div class="modal fade" id="createOverrideModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Customer Override</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="createOverrideForm">
                    <div class="mb-3">
                        <label class="form-label">Customer <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="customerSearch" placeholder="Search customer...">
                        <input type="hidden" id="customerId">
                        <input type="hidden" id="customerName">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Product <span class="text-danger">*</span></label>
                            <select class="form-select" id="productType" required>
                                <option value="ALL">All Products</option>
                                <option value="SMS">SMS</option>
                                <option value="RCS_BASIC">RCS Basic</option>
                                <option value="RCS_SINGLE">RCS Single</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Scope <span class="text-danger">*</span></label>
                            <select class="form-select" id="scopeType" required onchange="toggleScopeValue()">
                                <option value="GLOBAL">Global (All Destinations)</option>
                                <option value="UK_NETWORK">UK Network Specific</option>
                                <option value="COUNTRY">Country Specific</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3" id="scopeValueContainer" style="display: none;">
                        <label class="form-label">Scope Value</label>
                        <input type="text" class="form-control" id="scopeValue" placeholder="Network code or country ISO">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Forced Gateway <span class="text-danger">*</span></label>
                        <select class="form-select" id="forcedGatewayId" required>
                            <option value="">Select gateway...</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Secondary Gateway (Optional)</label>
                        <select class="form-select" id="secondaryGatewayId">
                            <option value="">None</option>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Start Date/Time <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" id="startDatetime" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">End Date/Time</label>
                            <input type="datetime-local" class="form-control" id="endDatetime">
                            <small class="text-muted">Leave empty for indefinite</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Reason <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="reason" rows="3" required></textarea>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="notifyCustomer">
                        <label class="form-check-label" for="notifyCustomer">Notify customer of this override</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-admin-primary" onclick="submitCreateOverride()">Create Override</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openCreateOverrideModal() {
    // Load gateways
    fetch('/admin/supplier-management/gateways')
        .then(response => response.json())
        .then(gateways => {
            const forced = document.getElementById('forcedGatewayId');
            const secondary = document.getElementById('secondaryGatewayId');

            forced.innerHTML = '<option value="">Select gateway...</option>';
            secondary.innerHTML = '<option value="">None</option>';

            gateways.forEach(gw => {
                const option = `<option value="${gw.id}">${gw.name} (${gw.supplier.name})</option>`;
                forced.innerHTML += option;
                secondary.innerHTML += option;
            });
        });

    new bootstrap.Modal(document.getElementById('createOverrideModal')).show();
}

function toggleScopeValue() {
    const scopeType = document.getElementById('scopeType').value;
    const container = document.getElementById('scopeValueContainer');
    container.style.display = scopeType === 'GLOBAL' ? 'none' : 'block';
}

function submitCreateOverride() {
    const data = {
        customer_id: document.getElementById('customerId').value || 1, // Placeholder
        customer_name: document.getElementById('customerSearch').value || 'Test Customer',
        product_type: document.getElementById('productType').value,
        scope_type: document.getElementById('scopeType').value,
        scope_value: document.getElementById('scopeValue').value,
        forced_gateway_id: document.getElementById('forcedGatewayId').value,
        secondary_gateway_id: document.getElementById('secondaryGatewayId').value || null,
        start_datetime: document.getElementById('startDatetime').value,
        end_datetime: document.getElementById('endDatetime').value || null,
        reason: document.getElementById('reason').value,
        notify_customer: document.getElementById('notifyCustomer').checked,
    };

    fetch('/admin/routing-rules/overrides', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            location.reload();
        } else {
            alert('Error: ' + result.message);
        }
    });
}

function cancelOverride(id) {
    if (confirm('Cancel this override?')) {
        fetch(`/admin/routing-rules/overrides/${id}/cancel`, {
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
</script>
@endpush
