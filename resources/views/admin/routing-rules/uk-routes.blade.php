@extends('layouts.admin')

@section('title', 'UK Routes - Routing Rules')

@push('styles')
<style>
:root {
    --admin-primary: #1e3a5f;
    --admin-secondary: #2d5a87;
    --admin-accent: #4a90d9;
}

.product-tabs {
    display: flex;
    gap: 0;
    border-bottom: 2px solid #e9ecef;
    margin-bottom: 1.5rem;
}

.product-tab {
    padding: 0.75rem 1.5rem;
    border: none;
    background: transparent;
    color: #6c757d;
    font-weight: 500;
    font-size: 0.875rem;
    cursor: pointer;
    border-bottom: 2px solid transparent;
    margin-bottom: -2px;
    transition: all 0.2s;
}

.product-tab:hover {
    color: var(--admin-primary);
}

.product-tab.active {
    color: var(--admin-primary);
    border-bottom-color: var(--admin-primary);
    font-weight: 600;
}

.route-card {
    background: #fff;
    border-radius: 12px;
    border: 1px solid #dde4ea;
    overflow: hidden;
    box-shadow: 0 1px 4px rgba(0,0,0,0.08);
    margin-bottom: 1.5rem;
}

.route-table th {
    padding: 0.5rem 0.35rem;
    font-size: 0.75rem;
    font-weight: 600;
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
    color: #495057;
    white-space: nowrap;
}

.route-table td {
    padding: 0.5rem 0.35rem;
    font-size: 0.8rem;
    border-bottom: 1px solid #f1f3f5;
    vertical-align: middle;
}

.route-row {
    cursor: pointer;
    transition: background 0.15s;
}

.route-row:hover {
    background: #f8f9fa;
}

.route-row.expanded {
    background: #f0f4f8;
}

.expand-icon {
    transition: transform 0.2s;
    color: #6c757d;
    font-size: 0.7rem;
}

.route-row.expanded .expand-icon {
    transform: rotate(90deg);
}

.expand-panel {
    display: none;
    background: #f8f9fb;
    border-top: 1px solid #e9ecef;
}

.expand-panel.show {
    display: table-row;
}

.gateway-cards-container {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    padding: 1.25rem;
}

.gateway-card {
    background: #fff;
    border-radius: 10px;
    border: 1px solid #dde4ea;
    padding: 1rem;
    min-width: 280px;
    flex: 1;
    max-width: 340px;
    position: relative;
}

.gateway-card.primary-gw {
    border-color: var(--admin-primary);
    border-width: 2px;
}

.gateway-card .gw-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 0.75rem;
}

.gateway-card .gw-name {
    font-weight: 600;
    font-size: 0.875rem;
    color: #212529;
}

.gateway-card .gw-supplier {
    font-size: 0.75rem;
    color: #6c757d;
    margin-top: 0.15rem;
}

.gw-primary-badge {
    background: var(--admin-primary);
    color: #fff;
    padding: 0.15rem 0.5rem;
    border-radius: 10px;
    font-size: 0.65rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.weight-display {
    text-align: center;
    padding: 0.75rem 0;
    margin-bottom: 0.75rem;
    border-top: 1px solid #f1f3f5;
    border-bottom: 1px solid #f1f3f5;
}

.weight-value {
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--admin-primary);
    line-height: 1;
}

.weight-label {
    font-size: 0.65rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-top: 0.25rem;
}

.telemetry-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.5rem;
    margin-bottom: 0.75rem;
}

.telemetry-stat {
    background: #f8f9fa;
    padding: 0.4rem 0.5rem;
    border-radius: 6px;
}

.telemetry-stat .stat-label {
    font-size: 0.6rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.telemetry-stat .stat-value {
    font-size: 0.8rem;
    font-weight: 600;
    color: #212529;
}

.gw-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.35rem;
    padding-top: 0.5rem;
    border-top: 1px solid #f1f3f5;
}

.gw-actions .btn {
    font-size: 0.7rem;
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.2rem 0.6rem;
    border-radius: 12px;
    font-size: 0.7rem;
    font-weight: 500;
}

.status-badge.active { background: #d4f4dd; color: #198754; }
.status-badge.blocked { background: #fde2e2; color: #dc3545; }
.status-badge.online { background: #d4f4dd; color: #198754; }
.status-badge.offline { background: #e0e0e0; color: #6c757d; }

.gateway-count-badge {
    background: #e8edf3;
    color: var(--admin-primary);
    padding: 0.2rem 0.5rem;
    border-radius: 8px;
    font-size: 0.7rem;
    font-weight: 600;
}

.rate-snapshot {
    font-family: 'SFMono-Regular', monospace;
    font-size: 0.8rem;
    color: #212529;
}

.filter-toolbar {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
}

.add-gw-btn {
    position: absolute;
    right: 1.25rem;
    bottom: 1.25rem;
}

.no-gateways-placeholder {
    text-align: center;
    padding: 2rem;
    color: #6c757d;
}

.no-gateways-placeholder i {
    font-size: 2rem;
    margin-bottom: 0.5rem;
    opacity: 0.5;
}
</style>
@endpush

@section('content')
<div class="page-titles">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
        <li class="breadcrumb-item"><a href="#">Routing</a></li>
        <li class="breadcrumb-item active">UK Routes</li>
    </ol>
</div>

<div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <div>
        <h2 style="margin: 0; color: var(--admin-primary);">Routing Rules</h2>
        <p class="text-muted mb-0" style="font-size: 0.85rem;">Manage route priorities, gateway weights, and customer overrides</p>
    </div>
    <div>
        <button class="btn btn-sm" style="background: var(--admin-primary); color: #fff;" onclick="openAddGatewayToRouteModal()">
            <i class="fas fa-plus me-1"></i>Add Gateway to Route
        </button>
    </div>
</div>

{{-- Tab Navigation --}}
<ul class="nav nav-tabs mb-0" style="border-bottom: 2px solid #e9ecef;">
    <li class="nav-item">
        <a class="nav-link active" href="{{ route('admin.system.routing') }}" style="color: var(--admin-primary); font-weight: 600; border-bottom: 2px solid var(--admin-primary); margin-bottom: -2px;">
            <i class="fas fa-flag me-1"></i>UK Routes
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('admin.system.routing') }}?tab=international" style="color: #6c757d;">
            <i class="fas fa-globe me-1"></i>International Routes
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('admin.system.routing') }}?tab=overrides" style="color: #6c757d;">
            <i class="fas fa-user-cog me-1"></i>Customer Overrides
        </a>
    </li>
</ul>

{{-- Product Selector Tabs --}}
<div class="product-tabs mt-3">
    @foreach($productTypes as $index => $pt)
    <button class="product-tab {{ $index === 0 ? 'active' : '' }}" data-product="{{ strtolower($pt) }}" onclick="switchProduct('{{ strtolower($pt) }}', this)">{{ strtoupper($pt) }}</button>
    @endforeach
    @if(count($productTypes) === 0)
    <button class="product-tab active" data-product="sms" onclick="switchProduct('sms', this)">SMS</button>
    @endif
</div>

{{-- Filter Toolbar --}}
<div class="filter-toolbar">
    <div class="row g-3 align-items-center">
        <div class="col-md-4">
            <input type="text" class="form-control form-control-sm" id="searchRoutes" placeholder="Search by network or prefix..." onkeyup="filterRoutes()">
        </div>
        <div class="col-md-3">
            <select class="form-select form-select-sm" id="filterStatus" onchange="filterRoutes()">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="blocked">Blocked</option>
            </select>
        </div>
        <div class="col-md-3">
            <select class="form-select form-select-sm" id="filterGateway" onchange="filterRoutes()">
                <option value="">All Gateways</option>
                @foreach($gateways as $gw)
                <option value="{{ $gw->gateway_code }}">{{ $gw->name }} ({{ $gw->supplier->name ?? 'Unknown' }})</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2 text-end">
            <span class="text-muted" style="font-size: 0.75rem;" id="routeCount">Showing {{ count($ukNetworks) }} routes</span>
        </div>
    </div>
</div>

{{-- Routes Table --}}
<div class="route-card">
    <div class="table-responsive">
        <table class="table route-table mb-0">
            <thead>
                <tr>
                    <th style="width: 30px;"></th>
                    <th>Network</th>
                    <th>Prefix Group</th>
                    <th>Gateways</th>
                    <th>Primary Gateway</th>
                    <th>Billing</th>
                    <th>Rate</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="routesTableBody">
                @forelse($ukNetworks as $route)
                <tr class="route-row" data-route-id="{{ $route['id'] }}" data-status="{{ $route['status'] }}" data-search="{{ strtolower($route['network'] . ' ' . $route['prefix']) }}" onclick="toggleRoutePanel({{ $route['id'] }})">
                    <td><i class="fas fa-chevron-right expand-icon"></i></td>
                    <td><strong>{{ $route['network'] }}</strong></td>
                    <td><code style="font-size: 0.75rem;">{{ $route['prefix'] }}</code></td>
                    <td><span class="gateway-count-badge">{{ $route['gateway_count'] }} {{ $route['gateway_count'] === 1 ? 'gateway' : 'gateways' }}</span></td>
                    <td style="font-size: 0.8rem;">{{ $route['primary_gw'] }}</td>
                    <td style="font-size: 0.75rem;">{{ $route['billing'] }}</td>
                    <td><span class="rate-snapshot">{{ $route['rate'] !== '—' ? '£' . $route['rate'] : '—' }}</span></td>
                    <td>
                        <span class="status-badge {{ $route['status'] }}">
                            <i class="fas fa-circle" style="font-size: 5px;"></i>
                            {{ ucfirst($route['status']) }}
                        </span>
                    </td>
                </tr>
                <tr class="expand-panel" id="panel-{{ $route['id'] }}">
                    <td colspan="8" style="padding: 0;">
                        <div class="gateway-cards-container">
                            @foreach($route['gateways'] as $gw)
                            <div class="gateway-card {{ $gw['primary'] ? 'primary-gw' : '' }}">
                                <div class="gw-header">
                                    <div>
                                        <div class="gw-name">{{ $gw['name'] }}</div>
                                        <div class="gw-supplier">{{ $gw['supplier'] }} &middot; <code style="font-size: 0.65rem;">{{ $gw['code'] }}</code></div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        @if($gw['primary'])
                                        <span class="gw-primary-badge">Primary</span>
                                        @endif
                                        <span class="status-badge {{ $gw['status'] }}">
                                            <i class="fas fa-circle" style="font-size: 5px;"></i>
                                            {{ ucfirst($gw['status']) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="weight-display">
                                    <div class="weight-value">{{ $gw['weight'] !== null ? $gw['weight'] . '%' : '—' }}</div>
                                    <div class="weight-label">Traffic Weight</div>
                                </div>
                                <div class="telemetry-grid">
                                    {{-- TODO: Pull from telemetry service --}}
                                    <div class="telemetry-stat">
                                        <div class="stat-label">Delivery Rate</div>
                                        <div class="stat-value">—</div>
                                    </div>
                                    <div class="telemetry-stat">
                                        <div class="stat-label">Response Time</div>
                                        <div class="stat-value">—</div>
                                    </div>
                                    <div class="telemetry-stat">
                                        <div class="stat-label">Median DLR</div>
                                        <div class="stat-value">—</div>
                                    </div>
                                    <div class="telemetry-stat">
                                        <div class="stat-label">Rate</div>
                                        <div class="stat-value">£{{ $gw['rate'] }}</div>
                                    </div>
                                </div>
                                <div class="gw-actions">
                                    @if(!$gw['primary'])
                                    <button class="btn btn-outline-primary btn-sm" onclick="event.stopPropagation(); setPrimaryGateway({{ $route['id'] }}, '{{ $gw['code'] }}')">
                                        <i class="fas fa-star me-1"></i>Set Primary
                                    </button>
                                    @endif
                                    <button class="btn btn-outline-secondary btn-sm" onclick="event.stopPropagation(); openChangeWeightModal({{ $route['id'] }}, '{{ $gw['code'] }}', '{{ $gw['name'] }}', {{ $gw['weight'] ?? 0 }})">
                                        <i class="fas fa-balance-scale me-1"></i>Change Weight
                                    </button>
                                    <button class="btn btn-outline-{{ $gw['status'] === 'online' ? 'warning' : 'success' }} btn-sm" onclick="event.stopPropagation(); toggleGatewayBlock({{ $route['id'] }}, '{{ $gw['code'] }}')">
                                        <i class="fas fa-{{ $gw['status'] === 'online' ? 'ban' : 'check' }} me-1"></i>{{ $gw['status'] === 'online' ? 'Block' : 'Allow' }}
                                    </button>
                                    <button class="btn btn-outline-danger btn-sm" onclick="event.stopPropagation(); removeGateway({{ $route['id'] }}, '{{ $gw['code'] }}', '{{ $gw['name'] }}')">
                                        <i class="fas fa-times me-1"></i>Remove
                                    </button>
                                </div>
                            </div>
                            @endforeach

                            <div class="gateway-card" style="border-style: dashed; display: flex; align-items: center; justify-content: center; min-height: 200px; cursor: pointer;" onclick="event.stopPropagation(); openAddGatewayToRouteModal({{ $route['id'] }})">
                                <div class="text-center text-muted">
                                    <i class="fas fa-plus-circle fa-2x mb-2" style="opacity: 0.4;"></i>
                                    <div style="font-size: 0.8rem;">Add Gateway</div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-4 text-muted">
                        <i class="fas fa-route fa-2x mb-2" style="opacity: 0.4;"></i>
                        <p class="mb-0">No UK routes configured</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Add Gateway to Route Modal --}}
<div class="modal fade" id="addGatewayRouteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: var(--admin-primary); color: #fff;">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Add Gateway to Route</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="addGwRouteId">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Select Gateway <span class="text-danger">*</span></label>
                    <select class="form-select" id="addGwSelect">
                        <option value="">Choose a gateway...</option>
                        @foreach($gateways as $gw)
                        <option value="{{ $gw->gateway_code }}">{{ $gw->name }} ({{ $gw->supplier->name ?? 'Unknown' }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Initial Weight (%) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="addGwWeight" min="1" max="100" value="10">
                    <small class="text-muted">Weights across all gateways for this route will be rebalanced automatically.</small>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="addGwPrimary">
                    <label class="form-check-label" for="addGwPrimary">Set as Primary Gateway</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn" style="background: var(--admin-primary); color: #fff;" onclick="confirmAddGateway()">
                    <i class="fas fa-plus me-1"></i>Add Gateway
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Change Weight Modal --}}
<div class="modal fade" id="changeWeightModal" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: var(--admin-primary); color: #fff;">
                <h5 class="modal-title"><i class="fas fa-balance-scale me-2"></i>Change Weight</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="cwRouteId">
                <input type="hidden" id="cwGatewayCode">
                <p class="mb-2" style="font-size: 0.85rem;">Gateway: <strong id="cwGatewayName"></strong></p>
                <div class="mb-3">
                    <label class="form-label fw-semibold">New Weight (%) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="cwNewWeight" min="1" max="100">
                    <small class="text-muted">Current: <span id="cwCurrentWeight"></span>%</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn" style="background: var(--admin-primary); color: #fff;" onclick="confirmChangeWeight()">
                    <i class="fas fa-check me-1"></i>Update Weight
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    updateRouteCount();
});

function switchProduct(product, btn) {
    document.querySelectorAll('.product-tab').forEach(t => t.classList.remove('active'));
    btn.classList.add('active');
    showToast('Switched to ' + btn.textContent.trim() + ' routes', 'info');
}

function toggleRoutePanel(routeId) {
    const row = document.querySelector(`tr.route-row[data-route-id="${routeId}"]`);
    const panel = document.getElementById('panel-' + routeId);

    if (row.classList.contains('expanded')) {
        row.classList.remove('expanded');
        panel.classList.remove('show');
    } else {
        document.querySelectorAll('.route-row.expanded').forEach(r => {
            r.classList.remove('expanded');
            document.getElementById('panel-' + r.dataset.routeId).classList.remove('show');
        });
        row.classList.add('expanded');
        panel.classList.add('show');
    }
}

function filterRoutes() {
    const search = document.getElementById('searchRoutes').value.toLowerCase();
    const status = document.getElementById('filterStatus').value;
    let visible = 0;

    document.querySelectorAll('.route-row').forEach(row => {
        const matchSearch = !search || row.dataset.search.includes(search);
        const matchStatus = !status || row.dataset.status === status;
        const show = matchSearch && matchStatus;
        row.style.display = show ? '' : 'none';
        const panel = document.getElementById('panel-' + row.dataset.routeId);
        if (!show && panel) {
            panel.classList.remove('show');
            row.classList.remove('expanded');
        }
        if (show) visible++;
    });

    updateRouteCount(visible);
}

function updateRouteCount(count) {
    if (count === undefined) {
        count = document.querySelectorAll('.route-row').length;
    }
    document.getElementById('routeCount').textContent = 'Showing ' + count + ' route' + (count !== 1 ? 's' : '');
}

function openAddGatewayToRouteModal(routeId) {
    document.getElementById('addGwRouteId').value = routeId || '';
    document.getElementById('addGwSelect').value = '';
    document.getElementById('addGwWeight').value = 10;
    document.getElementById('addGwPrimary').checked = false;
    new bootstrap.Modal(document.getElementById('addGatewayRouteModal')).show();
}

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

function apiPost(url, data) {
    return fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: JSON.stringify(data)
    }).then(r => r.json());
}

function confirmAddGateway() {
    const gw = document.getElementById('addGwSelect').value;
    const weight = document.getElementById('addGwWeight').value;
    const routeId = document.getElementById('addGwRouteId').value;
    const setPrimary = document.getElementById('addGwPrimary')?.checked || false;
    if (!gw) { showToast('Please select a gateway', 'warning'); return; }
    if (!weight || weight < 1 || weight > 100) { showToast('Weight must be between 1 and 100', 'warning'); return; }

    bootstrap.Modal.getInstance(document.getElementById('addGatewayRouteModal')).hide();
    apiPost('/admin/system/routing/add-gateway', { route_id: String(routeId), gateway_code: gw, weight: parseInt(weight), set_primary: setPrimary, route_type: 'uk' })
        .then(data => { if (data.success) { showToast(data.message, 'success'); setTimeout(() => location.reload(), 500); } else { showToast(data.message || 'Failed to add gateway', 'danger'); } })
        .catch(() => showToast('Request failed', 'danger'));
}

function openChangeWeightModal(routeId, gwCode, gwName, currentWeight) {
    document.getElementById('cwRouteId').value = routeId;
    document.getElementById('cwGatewayCode').value = gwCode;
    document.getElementById('cwGatewayName').textContent = gwName;
    document.getElementById('cwCurrentWeight').textContent = currentWeight;
    document.getElementById('cwNewWeight').value = currentWeight;
    new bootstrap.Modal(document.getElementById('changeWeightModal')).show();
}

function confirmChangeWeight() {
    const routeId = document.getElementById('cwRouteId').value;
    const gwCode = document.getElementById('cwGatewayCode').value;
    const newWeight = document.getElementById('cwNewWeight').value;
    if (!newWeight || newWeight < 1 || newWeight > 100) { showToast('Weight must be between 1 and 100', 'warning'); return; }

    bootstrap.Modal.getInstance(document.getElementById('changeWeightModal')).hide();
    apiPost('/admin/system/routing/change-weight', { route_id: String(routeId), gateway_code: gwCode, new_weight: parseInt(newWeight), route_type: 'uk' })
        .then(data => { if (data.success) { showToast(data.message, 'success'); setTimeout(() => location.reload(), 500); } else { showToast(data.message || 'Failed to update weight', 'danger'); } })
        .catch(() => showToast('Request failed', 'danger'));
}

function setPrimaryGateway(routeId, gwCode) {
    if (confirm('Set this gateway as primary for this route? The current primary will be demoted.')) {
        apiPost('/admin/system/routing/set-primary', { route_id: String(routeId), gateway_code: gwCode, route_type: 'uk' })
            .then(data => { if (data.success) { showToast(data.message, 'success'); setTimeout(() => location.reload(), 500); } else { showToast(data.message || 'Failed to set primary', 'danger'); } })
            .catch(() => showToast('Request failed', 'danger'));
    }
}

function toggleGatewayBlock(routeId, gwCode) {
    if (confirm('Are you sure you want to change the status of this gateway?')) {
        apiPost('/admin/system/routing/toggle-block', { route_id: String(routeId), gateway_code: gwCode, route_type: 'uk' })
            .then(data => { if (data.success) { showToast(data.message, 'success'); setTimeout(() => location.reload(), 500); } else { showToast(data.message || 'Failed to update status', 'danger'); } })
            .catch(() => showToast('Request failed', 'danger'));
    }
}

function removeGateway(routeId, gwCode, gwName) {
    if (confirm('Remove "' + gwName + '" from this route? This action cannot be undone.')) {
        apiPost('/admin/system/routing/remove-gateway', { route_id: String(routeId), gateway_code: gwCode, route_type: 'uk' })
            .then(data => { if (data.success) { showToast(data.message, 'success'); setTimeout(() => location.reload(), 500); } else { showToast(data.message || 'Failed to remove gateway', 'danger'); } })
            .catch(() => showToast('Request failed', 'danger'));
    }
}

function showToast(message, type) {
    type = type || 'info';
    const colors = { success: '#198754', warning: '#ffc107', info: '#0dcaf0', danger: '#dc3545' };
    const toast = document.createElement('div');
    toast.style.cssText = 'position:fixed;top:20px;right:20px;z-index:9999;padding:0.75rem 1.25rem;border-radius:8px;color:#fff;font-size:0.85rem;box-shadow:0 4px 12px rgba(0,0,0,0.15);background:' + (colors[type] || colors.info);
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => { toast.style.opacity = '0'; toast.style.transition = 'opacity 0.3s'; setTimeout(() => toast.remove(), 300); }, 3000);
}
</script>
@endpush
