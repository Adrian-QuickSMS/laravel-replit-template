@extends('layouts.admin')

@section('title', 'Auto Top-Up Management')

@push('styles')
<style>
.auto-topup-admin { min-height: calc(100vh - 200px); }
.filter-bar { display: flex; gap: 12px; align-items: center; flex-wrap: wrap; margin-bottom: 1.5rem; }
.filter-bar .form-control, .filter-bar .form-select { max-width: 200px; }
.stat-cards { display: flex; gap: 1rem; margin-bottom: 1.5rem; flex-wrap: wrap; }
.stat-card { flex: 1; min-width: 140px; background: #fff; border-radius: 0.5rem; padding: 1rem; border: 1px solid #e5e7eb; text-align: center; }
.stat-card .value { font-size: 1.5rem; font-weight: 700; }
.stat-card .label { font-size: 0.8rem; color: #6b7280; }
.badge-enabled { background: #dcfce7; color: #166534; }
.badge-disabled { background: #e5e7eb; color: #374151; }
.badge-locked { background: #fee2e2; color: #991b1b; }
.badge-failed { background: #fef3c7; color: #92400e; }
.action-menu { position: relative; }
.action-menu .dropdown-menu { min-width: 180px; }
.events-modal .table { font-size: 0.85rem; }
</style>
@endpush

@section('content')
<div class="auto-topup-admin">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Auto Top-Up Management</h2>
            <p class="text-muted mb-0">Monitor and manage customer auto top-up configurations.</p>
        </div>
        <button class="btn btn-outline-secondary btn-sm" id="refreshBtn"><i class="fas fa-sync-alt me-1"></i> Refresh</button>
    </div>

    <!-- Stats -->
    <div class="stat-cards" id="statCards">
        <div class="stat-card"><div class="value" id="statTotal">—</div><div class="label">Total Configured</div></div>
        <div class="stat-card"><div class="value text-success" id="statEnabled">—</div><div class="label">Enabled</div></div>
        <div class="stat-card"><div class="value text-danger" id="statLocked">—</div><div class="label">Locked</div></div>
        <div class="stat-card"><div class="value text-warning" id="statFailed">—</div><div class="label">With Failures</div></div>
    </div>

    <!-- Filters -->
    <div class="filter-bar">
        <input type="text" class="form-control" id="searchInput" placeholder="Search account name or number...">
        <select class="form-select" id="statusFilter">
            <option value="">All Statuses</option>
            <option value="enabled">Enabled</option>
            <option value="disabled">Disabled</option>
            <option value="locked">Locked</option>
            <option value="failed">Has Failures</option>
        </select>
    </div>

    <!-- Table -->
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Account</th>
                        <th>Status</th>
                        <th>Threshold</th>
                        <th>Amount</th>
                        <th>Daily Usage</th>
                        <th>Card</th>
                        <th>Failures</th>
                        <th>Last Top-Up</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <tr><td colspan="9" class="text-center text-muted py-4">Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-between align-items-center mt-3" id="pagination" style="display:none !important;"></div>
</div>

<!-- Events Modal -->
<div class="modal fade events-modal" id="eventsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Auto Top-Up Events — <span id="eventsAccountName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Event</th>
                                <th>Status</th>
                                <th>Amount</th>
                                <th>Stripe PI</th>
                                <th>Failure</th>
                                <th>Detail</th>
                            </tr>
                        </thead>
                        <tbody id="eventsBody">
                            <tr><td colspan="7" class="text-center py-4">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Disable Modal -->
<div class="modal fade" id="disableModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Disable &amp; Lock Auto Top-Up</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>This will disable and lock auto top-up for <strong id="disableAccountName"></strong>. The customer will not be able to re-enable until you unlock.</p>
                <div class="mb-3">
                    <label class="form-label">Reason (visible to customer)</label>
                    <textarea class="form-control" id="disableReason" rows="3" placeholder="e.g. Billing dispute under review" required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDisableBtn">Disable &amp; Lock</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    'use strict';

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    const API_BASE = '/admin/api/billing/auto-topup';
    let currentPage = 1;
    let selectedAccountId = null;

    function fmt(v) { return v != null ? '£' + parseFloat(v).toFixed(2) : '—'; }
    function fmtDate(iso) {
        if (!iso) return '—';
        const d = new Date(iso);
        return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short' }) + ' ' + d.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' });
    }

    async function apiFetch(url, opts = {}) {
        const res = await fetch(url, {
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken, ...opts.headers },
            ...opts,
        });
        if (!res.ok) { const e = await res.json().catch(() => ({})); throw e; }
        return res.json();
    }

    async function loadData() {
        const search = document.getElementById('searchInput').value;
        const status = document.getElementById('statusFilter').value;
        const params = new URLSearchParams({ page: currentPage });
        if (search) params.set('search', search);
        if (status) params.set('status', status);

        try {
            const resp = await apiFetch(`${API_BASE}?${params}`);
            renderTable(resp.data);
            renderStats(resp.data);
        } catch (e) {
            document.getElementById('tableBody').innerHTML = '<tr><td colspan="9" class="text-center text-danger py-4">Failed to load data.</td></tr>';
        }
    }

    function renderStats(items) {
        document.getElementById('statTotal').textContent = items.length;
        document.getElementById('statEnabled').textContent = items.filter(i => i.enabled && !i.admin_locked).length;
        document.getElementById('statLocked').textContent = items.filter(i => i.admin_locked).length;
        document.getElementById('statFailed').textContent = items.filter(i => i.consecutive_failure_count > 0).length;
    }

    function statusBadge(item) {
        if (item.admin_locked) return '<span class="badge badge-locked">Locked</span>';
        if (item.enabled) return '<span class="badge badge-enabled">Enabled</span>';
        return '<span class="badge badge-disabled">Disabled</span>';
    }

    function renderTable(items) {
        const tbody = document.getElementById('tableBody');
        if (!items.length) {
            tbody.innerHTML = '<tr><td colspan="9" class="text-center text-muted py-4">No auto top-up configurations found.</td></tr>';
            return;
        }

        tbody.innerHTML = items.map(item => `
            <tr>
                <td>
                    <div class="fw-semibold">${item.account_name || '—'}</div>
                    <small class="text-muted">${item.account_number || ''}</small>
                </td>
                <td>${statusBadge(item)}</td>
                <td>${fmt(item.threshold_amount)}</td>
                <td>${fmt(item.topup_amount)}</td>
                <td>
                    <small>${item.daily_stats.count} / ${item.max_topups_per_day}</small><br>
                    <small class="text-muted">${fmt(item.daily_stats.value)}${item.daily_topup_cap ? ' / ' + fmt(item.daily_topup_cap) : ''}</small>
                </td>
                <td><small>${item.card_brand ? item.card_brand + ' …' + item.card_last4 : '—'}</small></td>
                <td>${item.consecutive_failure_count > 0 ? '<span class="badge badge-failed">' + item.consecutive_failure_count + '</span>' : '0'}</td>
                <td><small>${fmtDate(item.last_successful_topup_at)}</small></td>
                <td>
                    <div class="dropdown action-menu">
                        <button class="btn btn-sm btn-light" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-v"></i></button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#" onclick="viewEvents('${item.account_id}', '${item.account_name || ''}');return false;"><i class="fas fa-history me-2"></i>View Events</a></li>
                            <li><hr class="dropdown-divider"></li>
                            ${item.admin_locked
                                ? `<li><a class="dropdown-item" href="#" onclick="unlockAccount('${item.account_id}');return false;"><i class="fas fa-unlock me-2"></i>Unlock</a></li>`
                                : `<li><a class="dropdown-item text-danger" href="#" onclick="disableAccount('${item.account_id}', '${item.account_name || ''}');return false;"><i class="fas fa-lock me-2"></i>Disable &amp; Lock</a></li>`
                            }
                        </ul>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    // Events modal
    window.viewEvents = async function(accountId, accountName) {
        document.getElementById('eventsAccountName').textContent = accountName;
        document.getElementById('eventsBody').innerHTML = '<tr><td colspan="7" class="text-center py-4">Loading...</td></tr>';
        new bootstrap.Modal(document.getElementById('eventsModal')).show();

        try {
            const resp = await apiFetch(`${API_BASE}/${accountId}/events`);
            const tbody = document.getElementById('eventsBody');
            if (!resp.data.length) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">No events.</td></tr>';
                return;
            }
            tbody.innerHTML = resp.data.map(e => `
                <tr>
                    <td>${fmtDate(e.created_at)}</td>
                    <td>${e.event_type}</td>
                    <td><span class="badge badge-${e.status}">${e.status}</span></td>
                    <td>${e.topup_amount ? fmt(e.topup_amount) : '—'}</td>
                    <td><small class="text-muted">${e.stripe_payment_intent_id || '—'}</small></td>
                    <td><small>${e.failure_code || '—'}</small></td>
                    <td><small>${e.failure_message || '—'}</small></td>
                </tr>
            `).join('');
        } catch (e) {
            document.getElementById('eventsBody').innerHTML = '<tr><td colspan="7" class="text-center text-danger py-4">Failed to load events.</td></tr>';
        }
    };

    // Disable & Lock
    window.disableAccount = function(accountId, accountName) {
        selectedAccountId = accountId;
        document.getElementById('disableAccountName').textContent = accountName;
        document.getElementById('disableReason').value = '';
        new bootstrap.Modal(document.getElementById('disableModal')).show();
    };

    document.getElementById('confirmDisableBtn').addEventListener('click', async function() {
        const reason = document.getElementById('disableReason').value.trim();
        if (!reason) return;

        try {
            await apiFetch(`${API_BASE}/${selectedAccountId}/disable`, {
                method: 'POST',
                body: JSON.stringify({ reason }),
            });
            bootstrap.Modal.getInstance(document.getElementById('disableModal')).hide();
            loadData();
        } catch (e) {
            alert(e.message || 'Failed to disable.');
        }
    });

    // Unlock
    window.unlockAccount = async function(accountId) {
        if (!confirm('Unlock auto top-up for this account? The customer will be able to re-enable it.')) return;
        try {
            await apiFetch(`${API_BASE}/${accountId}/unlock`, { method: 'POST' });
            loadData();
        } catch (e) {
            alert(e.message || 'Failed to unlock.');
        }
    };

    // Search & filter
    let searchTimeout;
    document.getElementById('searchInput').addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(loadData, 300);
    });
    document.getElementById('statusFilter').addEventListener('change', loadData);
    document.getElementById('refreshBtn').addEventListener('click', loadData);

    // Init
    loadData();
})();
</script>
@endpush
