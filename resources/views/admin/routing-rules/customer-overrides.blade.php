@extends('layouts.admin')

@section('title', 'Customer Overrides - Routing Rules')

@push('styles')
<style>
:root {
    --admin-primary: #1e3a5f;
    --admin-secondary: #2d5a87;
    --admin-accent: #4a90d9;
}

.filter-tabs {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 1.5rem;
}

.filter-tab {
    padding: 0.5rem 1.25rem;
    border: 1px solid #dde4ea;
    border-radius: 20px;
    background: #fff;
    color: #6c757d;
    font-size: 0.8rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}

.filter-tab:hover { border-color: var(--admin-primary); color: var(--admin-primary); }
.filter-tab.active { background: var(--admin-primary); color: #fff; border-color: var(--admin-primary); }

.filter-tab .count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 20px;
    height: 20px;
    border-radius: 10px;
    font-size: 0.65rem;
    font-weight: 700;
    margin-left: 0.35rem;
    padding: 0 0.35rem;
}

.filter-tab.active .count { background: rgba(255,255,255,0.2); color: #fff; }
.filter-tab:not(.active) .count { background: #e9ecef; color: #495057; }

.override-card {
    background: #fff;
    border-radius: 12px;
    border: 1px solid #dde4ea;
    padding: 1.25rem;
    margin-bottom: 1rem;
    box-shadow: 0 1px 4px rgba(0,0,0,0.06);
    transition: border-color 0.2s;
}

.override-card:hover { border-color: #b8c4ce; }

.override-card .card-header-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
}

.override-card .customer-name {
    font-weight: 600;
    font-size: 1rem;
    color: #212529;
}

.override-card .override-id {
    font-size: 0.7rem;
    color: #6c757d;
    font-family: monospace;
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
.status-badge.expired { background: #fff3cd; color: #856404; }
.status-badge.cancelled { background: #e0e0e0; color: #6c757d; }

.scope-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 0.35rem;
    margin-bottom: 0.75rem;
}

.scope-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.2rem 0.6rem;
    border-radius: 6px;
    font-size: 0.7rem;
    font-weight: 500;
    background: #e8edf3;
    color: var(--admin-primary);
}

.scope-badge i { font-size: 0.6rem; }

.override-details {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    padding: 0.75rem 0;
    border-top: 1px solid #f1f3f5;
    border-bottom: 1px solid #f1f3f5;
    margin-bottom: 0.75rem;
}

.detail-item .detail-label {
    font-size: 0.65rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    margin-bottom: 0.15rem;
}

.detail-item .detail-value {
    font-size: 0.85rem;
    font-weight: 500;
    color: #212529;
}

.detail-item .detail-value code {
    font-size: 0.75rem;
    padding: 0.1rem 0.3rem;
    background: #f8f9fa;
    border-radius: 4px;
}

.override-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.override-meta {
    font-size: 0.7rem;
    color: #6c757d;
}

.override-actions { display: flex; gap: 0.35rem; }
.override-actions .btn { font-size: 0.7rem; padding: 0.25rem 0.6rem; border-radius: 6px; }

.reason-text {
    background: #f8f9fa;
    padding: 0.5rem 0.75rem;
    border-radius: 6px;
    font-size: 0.8rem;
    color: #495057;
    margin-bottom: 0.75rem;
    border-left: 3px solid var(--admin-primary);
}

.empty-state {
    text-align: center;
    padding: 3rem 2rem;
    color: #6c757d;
}

.empty-state i { font-size: 3rem; margin-bottom: 1rem; opacity: 0.4; }
.empty-state p { margin: 0; font-size: 0.9rem; }

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
        <li class="breadcrumb-item"><a href="#">Routing</a></li>
        <li class="breadcrumb-item active">Customer Overrides</li>
    </ol>
</div>

<div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <div>
        <h2 style="margin: 0; color: var(--admin-primary);">Routing Rules</h2>
        <p class="text-muted mb-0" style="font-size: 0.85rem;">Manage route priorities, gateway weights, and customer overrides</p>
    </div>
    <div>
        <button class="btn btn-sm" style="background: var(--admin-primary); color: #fff;" onclick="openCreateOverrideModal()">
            <i class="fas fa-plus me-1"></i>Create Override
        </button>
    </div>
</div>

{{-- Tab Navigation --}}
<ul class="nav nav-tabs mb-0" style="border-bottom: 2px solid #e9ecef;">
    <li class="nav-item">
        <a class="nav-link" href="{{ route('admin.system.routing') }}" style="color: #6c757d;">
            <i class="fas fa-flag me-1"></i>UK Routes
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('admin.system.routing') }}?tab=international" style="color: #6c757d;">
            <i class="fas fa-globe me-1"></i>International Routes
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link active" href="{{ route('admin.system.routing') }}?tab=overrides" style="color: var(--admin-primary); font-weight: 600; border-bottom: 2px solid var(--admin-primary); margin-bottom: -2px;">
            <i class="fas fa-user-cog me-1"></i>Customer Overrides
        </a>
    </li>
</ul>

{{-- Filter Tabs --}}
<div class="filter-tabs mt-3">
    <button class="filter-tab active" data-filter="active" onclick="filterOverrides('active', this)">Active <span class="count">{{ $overrides->where('status', 'active')->count() }}</span></button>
    <button class="filter-tab" data-filter="expired" onclick="filterOverrides('expired', this)">Expired <span class="count">{{ $overrides->where('status', 'expired')->count() }}</span></button>
    <button class="filter-tab" data-filter="all" onclick="filterOverrides('all', this)">All <span class="count">{{ $overrides->count() }}</span></button>
</div>

{{-- Search --}}
<div class="filter-toolbar">
    <div class="row g-3 align-items-center">
        <div class="col-md-5">
            <input type="text" class="form-control form-control-sm" id="searchOverrides" placeholder="Search by customer name..." onkeyup="searchOverrides()">
        </div>
        <div class="col-md-3">
            <select class="form-select form-select-sm" id="filterProduct" onchange="searchOverrides()">
                <option value="">All Products</option>
                <option value="sms">SMS</option>
                <option value="rcs_basic">RCS Basic</option>
                <option value="rcs_single">RCS Single</option>
            </select>
        </div>
        <div class="col-md-2">
            <select class="form-select form-select-sm" id="filterScope" onchange="searchOverrides()">
                <option value="">All Scopes</option>
                <option value="global">Global</option>
                <option value="uk_network">UK Network</option>
                <option value="country">Country</option>
            </select>
        </div>
        <div class="col-md-2 text-end">
            <span class="text-muted" style="font-size: 0.75rem;" id="overrideCount">Showing 3 overrides</span>
        </div>
    </div>
</div>

{{-- Override Cards --}}
<div id="overridesContainer">
    @forelse($overrides as $override)
    @php
        $scopeType = 'Global';
        $scopeCode = 'global';
        $scopeValue = '—';
        if ($override->mcc && $override->mnc) {
            $scopeType = 'UK Network';
            $scopeCode = 'uk_network';
            $matchedNetwork = $ukNetworks->first(function($n) use ($override) { return $n->mcc === $override->mcc && $n->mnc === $override->mnc; });
            $scopeValue = $matchedNetwork ? $matchedNetwork->network_name : ($override->mcc . '/' . $override->mnc);
        } elseif ($override->country_iso) {
            $scopeType = 'Country';
            $scopeCode = 'country';
            $matchedCountry = $countries->firstWhere('country_iso', $override->country_iso);
            $scopeValue = $matchedCountry ? $matchedCountry->country_name . ' (' . $override->country_iso . ')' : $override->country_iso;
        }
        $productLabel = $override->product_type ? ucfirst($override->product_type) : 'All';
        $productCode = $override->product_type ? strtolower($override->product_type) : 'all';
    @endphp
    @php
        $customerLabel = 'Account #' . $override->account_id . ($override->sub_account_id ? ' / Sub #' . $override->sub_account_id : '');
    @endphp
    <div class="override-card" data-status="{{ $override->status }}" data-product="{{ $productCode }}" data-scope="{{ $scopeCode }}" data-search="{{ strtolower($customerLabel . ' ovr-' . $override->id . ' ' . $scopeValue) }}">
        <div class="card-header-row">
            <div>
                <div class="customer-name">{{ $customerLabel }}</div>
                <div class="override-id">OVR-{{ str_pad($override->id, 3, '0', STR_PAD_LEFT) }}</div>
            </div>
            <span class="status-badge {{ $override->status }}">
                <i class="fas fa-circle" style="font-size: 5px;"></i>
                {{ ucfirst($override->status) }}
            </span>
        </div>

        <div class="scope-badges">
            <span class="scope-badge"><i class="fas fa-box"></i> {{ $productLabel }}</span>
            <span class="scope-badge"><i class="fas fa-crosshairs"></i> {{ $scopeType }}</span>
            @if($scopeValue !== '—')
            <span class="scope-badge"><i class="fas fa-tag"></i> {{ $scopeValue }}</span>
            @endif
        </div>

        <div class="override-details">
            <div class="detail-item">
                <div class="detail-label">Forced Gateway</div>
                <div class="detail-value"><code>{{ $override->forcedGateway ? $override->forcedGateway->name : '—' }}</code></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Blocked Gateway</div>
                <div class="detail-value">{{ $override->blockedGateway ? $override->blockedGateway->name : '—' }}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Time Period</div>
                <div class="detail-value" style="font-size: 0.8rem;">
                    {{ $override->valid_from ? $override->valid_from->format('d M Y') : '—' }}
                    &rarr;
                    {{ $override->valid_to ? $override->valid_to->format('d M Y') : 'Indefinite' }}
                </div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Created By</div>
                <div class="detail-value">{{ $override->created_by ?? '—' }}</div>
            </div>
        </div>

        @if($override->reason)
        <div class="reason-text">
            <i class="fas fa-quote-left me-1" style="font-size: 0.6rem; color: var(--admin-primary);"></i>
            {{ $override->reason }}
        </div>
        @endif

        <div class="override-footer">
            <div class="override-meta">
                Created {{ $override->created_at ? $override->created_at->format('d-m-Y') : '—' }}
            </div>
            <div class="override-actions">
                @if($override->status === 'active')
                <button class="btn btn-outline-primary btn-sm" onclick="editOverride('OVR-{{ str_pad($override->id, 3, '0', STR_PAD_LEFT) }}')">
                    <i class="fas fa-edit me-1"></i>Edit
                </button>
                <button class="btn btn-outline-danger btn-sm" onclick="cancelOverride('OVR-{{ str_pad($override->id, 3, '0', STR_PAD_LEFT) }}', 'Account #{{ $override->account_id }}')">
                    <i class="fas fa-ban me-1"></i>Cancel
                </button>
                @else
                <button class="btn btn-outline-secondary btn-sm" onclick="viewOverride('OVR-{{ str_pad($override->id, 3, '0', STR_PAD_LEFT) }}')">
                    <i class="fas fa-eye me-1"></i>View
                </button>
                @endif
            </div>
        </div>
    </div>
    @empty
    <div class="empty-state">
        <i class="fas fa-user-cog"></i>
        <p>No customer overrides found.</p>
        <p class="text-muted mt-2" style="font-size: 0.8rem;">Create an override to force specific routing for a customer.</p>
    </div>
    @endforelse
</div>

{{-- Create Override Modal --}}
<div class="modal fade" id="createOverrideModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: var(--admin-primary); color: #fff;">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Create Customer Override</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="createOverrideForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Customer <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="coCustomer" placeholder="Search customer..." autocomplete="off">
                            <div id="customerSuggestions" class="dropdown-menu" style="width: 100%;"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Product <span class="text-danger">*</span></label>
                            <select class="form-select" id="coProduct">
                                <option value="all">All Products</option>
                                <option value="sms">SMS</option>
                                <option value="rcs_basic">RCS Basic</option>
                                <option value="rcs_single">RCS Single</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">Scope <span class="text-danger">*</span></label>
                            <select class="form-select" id="coScope" onchange="toggleScopeValue()">
                                <option value="global">Global</option>
                                <option value="uk_network">UK Network</option>
                                <option value="country">Country</option>
                            </select>
                        </div>
                        <div class="col-md-8 mb-3" id="scopeValueContainer" style="display: none;">
                            <label class="form-label fw-semibold">Scope Value <span class="text-danger">*</span></label>
                            <select class="form-select" id="coScopeValue">
                                <option value="">Select...</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Forced Gateway <span class="text-danger">*</span></label>
                            <select class="form-select" id="coForcedGateway">
                                <option value="">Select gateway...</option>
                                @foreach($gateways as $gw)
                                <option value="{{ $gw->id }}">{{ $gw->name }} ({{ $gw->supplier->name ?? 'Unknown' }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Secondary Gateway</label>
                            <select class="form-select" id="coSecondaryGateway">
                                <option value="">None (optional)</option>
                                @foreach($gateways as $gw)
                                <option value="{{ $gw->id }}">{{ $gw->name }} ({{ $gw->supplier->name ?? 'Unknown' }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Start Date/Time <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" id="coStartDate">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">End Date/Time</label>
                            <input type="datetime-local" class="form-control" id="coEndDate">
                            <small class="text-muted">Leave empty for indefinite override</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Reason <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="coReason" rows="3" placeholder="Explain why this override is needed..." required></textarea>
                    </div>

                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="coNotify">
                        <label class="form-check-label" for="coNotify">Notify customer about this routing change</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn" style="background: var(--admin-primary); color: #fff;" onclick="confirmCreateOverride()">
                    <i class="fas fa-check me-1"></i>Create Override
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let currentFilter = 'active';

document.addEventListener('DOMContentLoaded', function() {
    filterOverrides('active', document.querySelector('.filter-tab.active'));

    const customerInput = document.getElementById('coCustomer');
    if (customerInput) {
        customerInput.addEventListener('input', function() {
            const query = this.value.toLowerCase();
            const suggestions = document.getElementById('customerSuggestions');
            const customers = ['Acme Corp', 'TechStart Ltd', 'FastDelivery Inc', 'GlobalMsg Ltd', 'MediaBuzz', 'RetailChain PLC', 'DataFlow Systems', 'CloudReach UK'];

            if (query.length < 2) { suggestions.classList.remove('show'); return; }

            const matches = customers.filter(c => c.toLowerCase().includes(query));
            if (matches.length === 0) { suggestions.classList.remove('show'); return; }

            suggestions.innerHTML = matches.map(c => `<a class="dropdown-item" href="#" onclick="selectCustomer('${c}')">${c}</a>`).join('');
            suggestions.classList.add('show');
            suggestions.style.display = 'block';
        });
    }
});

function selectCustomer(name) {
    document.getElementById('coCustomer').value = name;
    document.getElementById('customerSuggestions').classList.remove('show');
    document.getElementById('customerSuggestions').style.display = 'none';
}

function filterOverrides(status, btn) {
    currentFilter = status;
    document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
    btn.classList.add('active');
    applyFilters();
}

function searchOverrides() {
    applyFilters();
}

function applyFilters() {
    const search = document.getElementById('searchOverrides').value.toLowerCase();
    const product = document.getElementById('filterProduct').value;
    const scope = document.getElementById('filterScope').value;
    let visible = 0;

    document.querySelectorAll('.override-card').forEach(card => {
        const matchStatus = currentFilter === 'all' || card.dataset.status === currentFilter;
        const matchSearch = !search || card.dataset.search.includes(search);
        const matchProduct = !product || card.dataset.product === product || card.dataset.product === 'all';
        const matchScope = !scope || card.dataset.scope === scope;
        const show = matchStatus && matchSearch && matchProduct && matchScope;
        card.style.display = show ? '' : 'none';
        if (show) visible++;
    });

    document.getElementById('overrideCount').textContent = 'Showing ' + visible + ' override' + (visible !== 1 ? 's' : '');
}

function toggleScopeValue() {
    const scope = document.getElementById('coScope').value;
    const container = document.getElementById('scopeValueContainer');
    const select = document.getElementById('coScopeValue');

    if (scope === 'global') {
        container.style.display = 'none';
        return;
    }

    container.style.display = '';
    select.innerHTML = '<option value="">Select...</option>';

    if (scope === 'uk_network') {
        @foreach($ukNetworks as $network)
        select.innerHTML += `<option value="{{ $network->mcc }}/{{ $network->mnc }}">{{ $network->network_name }}</option>`;
        @endforeach
    } else if (scope === 'country') {
        @foreach($countries as $country)
        select.innerHTML += `<option value="{{ $country->country_iso }}">{{ $country->country_name }} ({{ $country->country_iso }})</option>`;
        @endforeach
    }
}

function openCreateOverrideModal() {
    document.getElementById('createOverrideForm').reset();
    document.getElementById('scopeValueContainer').style.display = 'none';
    new bootstrap.Modal(document.getElementById('createOverrideModal')).show();
}

function confirmCreateOverride() {
    const customer = document.getElementById('coCustomer').value;
    const gateway = document.getElementById('coForcedGateway').value;
    const reason = document.getElementById('coReason').value;
    const startDate = document.getElementById('coStartDate').value;

    if (!customer) { showToast('Please select a customer', 'warning'); return; }
    if (!gateway) { showToast('Please select a forced gateway', 'warning'); return; }
    if (!startDate) { showToast('Please set a start date', 'warning'); return; }
    if (!reason.trim()) { showToast('Reason is required', 'warning'); return; }

    bootstrap.Modal.getInstance(document.getElementById('createOverrideModal')).hide();
    showToast('Override created successfully', 'success');
}

function editOverride(id) {
    showToast('Opening editor for ' + id + '...', 'info');
}

function cancelOverride(id, customer) {
    if (confirm('Cancel routing override ' + id + ' for ' + customer + '? This will revert to standard routing rules.')) {
        showToast('Override ' + id + ' cancelled', 'success');
    }
}

function viewOverride(id) {
    showToast('Viewing details for ' + id, 'info');
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
