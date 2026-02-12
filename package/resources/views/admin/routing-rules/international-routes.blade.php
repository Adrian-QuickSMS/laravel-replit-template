@extends('layouts.admin')

@section('title', 'International Routes - Routing Rules')

@push('styles')
<style>
:root {
    --admin-primary: #1e3a5f;
    --admin-secondary: #2d5a87;
    --admin-accent: #4a90d9;
}

.routing-table-card {
    background: #fff;
    border-radius: 12px;
    border: 1px solid #dde4ea;
    overflow: hidden;
    box-shadow: 0 1px 4px rgba(0,0,0,0.08);
}

.route-row {
    cursor: pointer;
    transition: background 0.2s;
}

.route-row:hover {
    background: #f8f9fa;
}

.route-row.expanded {
    background: #e3f2fd;
}

.route-details-panel {
    background: #f8f9fa;
    padding: 1.5rem;
    border-top: 1px solid #dde4ea;
    display: none;
}

.route-details-panel.show {
    display: block;
}

.gateway-card {
    background: #fff;
    border: 1px solid #dde4ea;
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1rem;
}

.gateway-card.primary {
    border-left: 4px solid #198754;
}

.gateway-card.blocked {
    opacity: 0.6;
    border-left: 4px solid #dc3545;
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

.status-badge.blocked {
    background: #ffe0e0;
    color: #dc3545;
}

.status-badge.online {
    background: #d4f4dd;
    color: #198754;
}

.status-badge.offline {
    background: #e0e0e0;
    color: #6c757d;
}

.telemetry-stat {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.telemetry-stat-label {
    font-size: 0.7rem;
    color: #6c757d;
    text-transform: uppercase;
}

.telemetry-stat-value {
    font-size: 1.1rem;
    font-weight: 600;
    color: #1e3a5f;
}

.weight-display {
    font-family: 'Courier New', monospace;
    font-weight: 700;
    font-size: 1.2rem;
    color: #1e3a5f;
}

.product-tabs {
    display: flex;
    gap: 1rem;
    border-bottom: 2px solid #dde4ea;
    margin-bottom: 1.5rem;
}

.product-tab {
    padding: 0.75rem 1.5rem;
    background: none;
    border: none;
    color: #6c757d;
    font-weight: 500;
    cursor: pointer;
    border-bottom: 3px solid transparent;
    transition: all 0.2s;
}

.product-tab.active {
    color: #1e3a5f;
    border-bottom-color: #1e3a5f;
}

.product-tab:hover {
    color: #1e3a5f;
}
</style>
@endpush

@section('content')
<div class="page-titles">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
        <li class="breadcrumb-item"><a href="#">Routing Rules</a></li>
        <li class="breadcrumb-item active">International Routes</li>
    </ol>
</div>

<div class="page-header">
    <div>
        <h2>International Routes</h2>
        <p>Manual routing control by destination country</p>
    </div>
    <div class="d-flex gap-2">
        <label class="form-check">
            <input type="checkbox" class="form-check-input" id="showBlocked" {{ $showBlocked ? 'checked' : '' }} onchange="toggleBlocked()">
            <span class="form-check-label">Show Blocked</span>
        </label>
    </div>
</div>

<!-- Product Selector -->
<div class="product-tabs">
    <button class="product-tab {{ $productType === 'SMS' ? 'active' : '' }}" onclick="changeProduct('SMS')">SMS</button>
    <button class="product-tab {{ $productType === 'RCS_BASIC' ? 'active' : '' }}" onclick="changeProduct('RCS_BASIC')">RCS Basic</button>
    <button class="product-tab {{ $productType === 'RCS_SINGLE' ? 'active' : '' }}" onclick="changeProduct('RCS_SINGLE')">RCS Single</button>
</div>

<div class="routing-table-card">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Network Name</th>
                    <th>Prefix Group</th>
                    <th>In-route Gateways</th>
                    <th>Primary Gateway</th>
                    <th>Billing Type</th>
                    <th>Rate Snapshot</th>
                    <th>Status</th>
                    <th style="width: 100px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($routes as $route)
                <tr class="route-row" onclick="toggleRoute({{ $route->id }})" id="route-row-{{ $route->id }}">
                    <td><strong>{{ $route->destination_name }}</strong></td>
                    <td><code>{{ $route->destination_code }}</code></td>
                    <td>{{ $route->gatewayWeights->count() }}</td>
                    <td>{{ $route->primaryGateway ? $route->primaryGateway->name : '—' }}</td>
                    <td>{{ $route->primaryGateway ? ucfirst($route->primaryGateway->billing_method) : '—' }}</td>
                    <td>
                        @if($route->primaryGateway)
                            <span class="text-muted">£0.0000</span>
                        @else
                            —
                        @endif
                    </td>
                    <td>
                        <span class="status-badge {{ $route->status }}">
                            <i class="fas fa-circle" style="font-size: 6px;"></i>
                            {{ ucfirst($route->status) }}
                        </span>
                    </td>
                    <td onclick="event.stopPropagation()">
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#" onclick="addGatewayModal({{ $route->id }})"><i class="fas fa-plus me-2"></i>Add Gateway</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="#" onclick="toggleDestination({{ $route->id }})"><i class="fas fa-ban me-2"></i>{{ $route->status === 'active' ? 'Block' : 'Unblock' }} Destination</a></li>
                            </ul>
                        </div>
                    </td>
                </tr>
                <tr id="route-details-{{ $route->id }}" style="display: none;">
                    <td colspan="8">
                        <div class="route-details-panel" id="route-panel-{{ $route->id }}">
                            <h6 class="mb-3">Route Details for {{ $route->destination_name }}</h6>

                            @foreach($route->gatewayWeights as $weight)
                            <div class="gateway-card {{ $weight->is_primary ? 'primary' : '' }} {{ $weight->route_status === 'blocked' ? 'blocked' : '' }}">
                                <div class="row align-items-center">
                                    <div class="col-md-3">
                                        <div class="mb-2">
                                            <strong>{{ $weight->gateway->name }}</strong>
                                            @if($weight->is_primary)
                                                <span class="badge bg-success ms-2">PRIMARY</span>
                                            @endif
                                        </div>
                                        <div class="text-muted small">{{ $weight->gateway->supplier->name }}</div>
                                        <div class="mt-2">
                                            <span class="status-badge {{ $weight->gateway->active ? 'online' : 'offline' }}">
                                                <i class="fas fa-circle" style="font-size: 6px;"></i>
                                                {{ $weight->gateway->active ? 'Online' : 'Offline' }}
                                            </span>
                                            <span class="status-badge {{ $weight->route_status === 'allowed' ? 'active' : 'blocked' }} ms-2">
                                                {{ ucfirst($weight->route_status) }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="col-md-2 text-center">
                                        <div class="weight-display">{{ $weight->weight }}%</div>
                                        <div class="text-muted small">Weight</div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="row g-3">
                                            <div class="col-6">
                                                <div class="telemetry-stat">
                                                    <span class="telemetry-stat-label">Delivery Rate</span>
                                                    <span class="telemetry-stat-value">—%</span>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="telemetry-stat">
                                                    <span class="telemetry-stat-label">Response Time</span>
                                                    <span class="telemetry-stat-value">—ms</span>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="telemetry-stat">
                                                    <span class="telemetry-stat-label">Median DLR</span>
                                                    <span class="telemetry-stat-value">—s</span>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="telemetry-stat">
                                                    <span class="telemetry-stat-label">Rate</span>
                                                    <span class="telemetry-stat-value">£0.0000</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-3 text-end">
                                        <div class="btn-group-vertical" role="group">
                                            @if(!$weight->is_primary)
                                            <button class="btn btn-sm btn-outline-primary" onclick="setPrimary({{ $route->id }}, {{ $weight->gateway_id }})">Set Primary</button>
                                            @endif
                                            <button class="btn btn-sm btn-outline-secondary" onclick="changeWeight({{ $weight->id }}, {{ $weight->weight }})">Change Weight</button>
                                            <button class="btn btn-sm btn-outline-warning" onclick="toggleGatewayStatus({{ $weight->id }})">{{ $weight->route_status === 'allowed' ? 'Block' : 'Allow' }}</button>
                                            <button class="btn btn-sm btn-outline-danger" onclick="removeGateway({{ $weight->id }})">Remove</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach

                            @if($route->gatewayWeights->isEmpty())
                            <div class="text-center py-4 text-muted">
                                <i class="fas fa-info-circle fa-2x mb-2"></i>
                                <p>No gateways configured for this route</p>
                                <button class="btn btn-admin-primary" onclick="addGatewayModal({{ $route->id }})">
                                    <i class="fas fa-plus me-2"></i>Add Gateway
                                </button>
                            </div>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-4 text-muted">
                        <i class="fas fa-inbox fa-2x mb-2"></i>
                        <p>No UK routes found for {{ $productType }}</p>
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
                <h5 class="modal-title">Add Gateway to Route</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addGatewayForm">
                    <input type="hidden" id="addGatewayRouteId">
                    <div class="mb-3">
                        <label class="form-label">Gateway <span class="text-danger">*</span></label>
                        <select class="form-select" id="addGatewayId" required>
                            <option value="">Select gateway...</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Weight (%) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="addGatewayWeight" min="1" max="100" value="100" required>
                        <small class="text-muted">All weights must total 100%</small>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="addGatewayPrimary">
                        <label class="form-check-label" for="addGatewayPrimary">Set as primary gateway</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-admin-primary" onclick="submitAddGateway()">Add Gateway</button>
            </div>
        </div>
    </div>
</div>

<!-- Change Weight Modal -->
<div class="modal fade" id="changeWeightModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Change Weight</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="changeWeightForm">
                    <input type="hidden" id="changeWeightId">
                    <div class="mb-3">
                        <label class="form-label">Weight (%) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control form-control-lg" id="changeWeightValue" min="1" max="100" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-admin-primary" onclick="submitChangeWeight()">Update Weight</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function changeProduct(product) {
    window.location.href = '{{ route("admin.routing.international-routes") }}?product=' + product + '&show_blocked={{ $showBlocked ? "1" : "0" }}';
}

function toggleBlocked() {
    const checked = document.getElementById('showBlocked').checked;
    window.location.href = '{{ route("admin.routing.international-routes") }}?product={{ $productType }}&show_blocked=' + (checked ? '1' : '0');
}

function toggleRoute(ruleId) {
    const row = document.getElementById('route-row-' + ruleId);
    const details = document.getElementById('route-details-' + ruleId);

    if (details.style.display === 'none') {
        details.style.display = 'table-row';
        row.classList.add('expanded');
    } else {
        details.style.display = 'none';
        row.classList.remove('expanded');
    }
}

function addGatewayModal(ruleId) {
    document.getElementById('addGatewayRouteId').value = ruleId;

    // Load available gateways
    fetch(`/admin/routing-rules/${ruleId}/available-gateways`)
        .then(response => response.json())
        .then(gateways => {
            const select = document.getElementById('addGatewayId');
            select.innerHTML = '<option value="">Select gateway...</option>';
            gateways.forEach(gw => {
                select.innerHTML += `<option value="${gw.id}">${gw.name} (${gw.supplier.name})</option>`;
            });
        });

    new bootstrap.Modal(document.getElementById('addGatewayModal')).show();
}

function submitAddGateway() {
    const ruleId = document.getElementById('addGatewayRouteId').value;
    const gatewayId = document.getElementById('addGatewayId').value;
    const weight = document.getElementById('addGatewayWeight').value;
    const setPrimary = document.getElementById('addGatewayPrimary').checked;

    fetch(`/admin/routing-rules/${ruleId}/add-gateway`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ gateway_id: gatewayId, weight: weight, set_primary: setPrimary })
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

function changeWeight(weightId, currentWeight) {
    document.getElementById('changeWeightId').value = weightId;
    document.getElementById('changeWeightValue').value = currentWeight;
    new bootstrap.Modal(document.getElementById('changeWeightModal')).show();
}

function submitChangeWeight() {
    const weightId = document.getElementById('changeWeightId').value;
    const weight = document.getElementById('changeWeightValue').value;

    fetch(`/admin/routing-rules/weight/${weightId}/change`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ weight: weight })
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

function setPrimary(ruleId, gatewayId) {
    if (confirm('Set this gateway as primary?')) {
        fetch(`/admin/routing-rules/${ruleId}/set-primary`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ gateway_id: gatewayId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }
}

function toggleGatewayStatus(weightId) {
    if (confirm('Change gateway status?')) {
        fetch(`/admin/routing-rules/weight/${weightId}/toggle`, {
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

function removeGateway(weightId) {
    if (confirm('Remove this gateway from the route?')) {
        fetch(`/admin/routing-rules/weight/${weightId}/remove`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
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
}

function toggleDestination(ruleId) {
    if (confirm('Block/unblock this entire destination?')) {
        fetch(`/admin/routing-rules/${ruleId}/toggle-destination`, {
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
