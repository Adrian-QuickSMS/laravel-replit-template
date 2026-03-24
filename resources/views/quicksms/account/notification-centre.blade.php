@extends('layouts.quicksms')

@section('title', 'Notification Centre')

@push('styles')
<style>
.breadcrumb {
    background: transparent;
    padding: 0;
    margin: 0;
}
.breadcrumb-item a {
    color: #6c757d;
    text-decoration: none;
}
.breadcrumb-item.active {
    font-weight: 500;
}
.nc-card {
    background: #fff;
    border-radius: 0.5rem;
    margin-bottom: 1rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
}
.nc-card-header {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}
.nc-card-header i {
    color: #886cc0;
    font-size: 1.1rem;
}
.nc-card-header h6 {
    margin: 0;
    font-weight: 600;
    color: #374151;
    font-size: 0.95rem;
    flex: 1;
}
.nc-card-body {
    padding: 1.25rem;
}
.nc-tabs .nav-link {
    color: #6c757d;
    font-weight: 500;
    border: none;
    padding: 0.75rem 1.25rem;
    font-size: 0.9rem;
}
.nc-tabs .nav-link.active {
    color: #886cc0;
    border-bottom: 2px solid #886cc0;
    background: transparent;
}
.nc-tabs .nav-link:hover:not(.active) {
    color: #374151;
}
.nc-severity-badge {
    font-size: 0.7rem;
    padding: 0.2rem 0.5rem;
    border-radius: 0.25rem;
    font-weight: 600;
    text-transform: uppercase;
}
.category-chip {
    font-size: 0.7rem;
    padding: 0.15rem 0.45rem;
    border-radius: 1rem;
    background: #f3f4f6;
    color: #6b7280;
    font-weight: 500;
}
.notif-item {
    padding: 1rem;
    border-bottom: 1px solid #f3f4f6;
    transition: background 0.15s;
}
.notif-item:hover {
    background: #f9fafb;
}
.notif-item.unread {
    border-left: 3px solid #886cc0;
    background: #faf9fc;
}
.notif-item:last-child {
    border-bottom: none;
}
.notif-actions .btn {
    font-size: 0.75rem;
    padding: 0.2rem 0.5rem;
}
.empty-state {
    text-align: center;
    padding: 3rem 1rem;
    color: #9ca3af;
}
.empty-state i {
    font-size: 2.5rem;
    margin-bottom: 1rem;
    display: block;
}
.filter-bar {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
    margin-bottom: 1rem;
}
.filter-bar select {
    font-size: 0.85rem;
    padding: 0.35rem 0.75rem;
    border-radius: 0.375rem;
    border: 1px solid #d1d5db;
    color: #374151;
}
.rule-row {
    display: flex;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f3f4f6;
    gap: 0.75rem;
}
.rule-row:last-child {
    border-bottom: none;
}
.channel-tag {
    font-size: 0.7rem;
    padding: 0.15rem 0.4rem;
    border-radius: 0.25rem;
    background: #ede9fe;
    color: #7c3aed;
    margin-right: 0.25rem;
}
.nc-table {
    width: 100%;
    font-size: 0.85rem;
}
.nc-table th {
    font-weight: 600;
    color: #6b7280;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    padding: 0.75rem;
    border-bottom: 2px solid #e5e7eb;
}
.nc-table td {
    padding: 0.75rem;
    border-bottom: 1px solid #f3f4f6;
    vertical-align: middle;
}
.status-chip {
    font-size: 0.7rem;
    padding: 0.2rem 0.5rem;
    border-radius: 1rem;
    font-weight: 600;
}
.status-dispatched { background: #dbeafe; color: #2563eb; }
.status-delivered { background: #d1fae5; color: #059669; }
.status-failed { background: #fee2e2; color: #dc2626; }
.status-suppressed { background: #f3f4f6; color: #6b7280; }
.pref-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem 0;
    border-bottom: 1px solid #f3f4f6;
    flex-wrap: wrap;
    gap: 0.5rem;
}
.pref-row:last-child {
    border-bottom: none;
}
.channel-cfg-card {
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    padding: 1.25rem;
    margin-bottom: 1rem;
}
.channel-cfg-card h6 {
    font-weight: 600;
    margin-bottom: 0.75rem;
    font-size: 0.9rem;
}
.nc-pagination {
    display: flex;
    justify-content: center;
    gap: 0.25rem;
    margin-top: 1rem;
}
.nc-pagination button {
    padding: 0.35rem 0.75rem;
    border: 1px solid #d1d5db;
    background: #fff;
    border-radius: 0.25rem;
    font-size: 0.8rem;
    cursor: pointer;
}
.nc-pagination button.active {
    background: #886cc0;
    color: #fff;
    border-color: #886cc0;
}
.nc-pagination button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
.action-dropdown {
    position: relative;
    display: inline-block;
}
.action-dropdown .dropdown-menu {
    font-size: 0.85rem;
}
.nc-loading {
    text-align: center;
    padding: 2rem;
    color: #9ca3af;
}
.nc-error {
    text-align: center;
    padding: 2rem;
    color: #dc2626;
}
.nc-error i {
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
    display: block;
}
.summary-card {
    text-align: center;
    padding: 1rem;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
}
.summary-card .count {
    font-size: 1.75rem;
    font-weight: 700;
    color: #374151;
}
.summary-card .label {
    font-size: 0.8rem;
    color: #6b7280;
    margin-top: 0.25rem;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('account.details') }}">Account</a></li>
            <li class="breadcrumb-item active">Notification Centre</li>
        </ol>
    </nav>

    <div class="nc-card">
        <div class="nc-card-header">
            <i class="fas fa-bell"></i>
            <h6>Notification Centre</h6>
        </div>
        <ul class="nav nc-tabs px-3 pt-2" id="ncTabs" role="tablist">
            <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tab-notifications" role="tab">Notifications</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-rules" role="tab">Alert Rules</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-history" role="tab">History</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-preferences" role="tab">Preferences</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-channels" role="tab">Channel Settings</a></li>
        </ul>

        <div class="tab-content nc-card-body">
            <div class="tab-pane fade show active" id="tab-notifications" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="filter-bar">
                        <select id="notifFilterCategory">
                            <option value="">All Categories</option>
                        </select>
                        <select id="notifFilterSeverity">
                            <option value="">All Severities</option>
                            <option value="critical">Critical</option>
                            <option value="warning">Warning</option>
                            <option value="info">Info</option>
                        </select>
                        <select id="notifFilterRead">
                            <option value="">All</option>
                            <option value="unread">Unread</option>
                            <option value="read">Read</option>
                        </select>
                    </div>
                    <button class="btn btn-sm btn-outline-primary" id="btnMarkAllRead" style="display: none;">
                        <i class="fas fa-check-double me-1"></i>Mark All Read
                    </button>
                </div>
                <div id="notifList">
                    <div class="nc-loading"><i class="fas fa-spinner fa-spin"></i> Loading notifications...</div>
                </div>
                <div id="notifPagination" class="nc-pagination"></div>
            </div>

            <div class="tab-pane fade" id="tab-rules" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <p class="text-muted mb-0" style="font-size: 0.85rem;">Manage alert rules that trigger notifications when conditions are met.</p>
                    <button class="btn btn-sm btn-primary" id="btnAddRule">
                        <i class="fas fa-plus me-1"></i>Add Rule
                    </button>
                </div>
                <div id="rulesList">
                    <div class="nc-loading"><i class="fas fa-spinner fa-spin"></i> Loading rules...</div>
                </div>
            </div>

            <div class="tab-pane fade" id="tab-history" role="tabpanel">
                <div id="historySummary" class="row g-3 mb-3"></div>
                <div class="filter-bar mb-3">
                    <select id="histFilterCategory">
                        <option value="">All Categories</option>
                    </select>
                    <select id="histFilterSeverity">
                        <option value="">All Severities</option>
                        <option value="critical">Critical</option>
                        <option value="warning">Warning</option>
                        <option value="info">Info</option>
                    </select>
                    <select id="histFilterStatus">
                        <option value="">All Statuses</option>
                        <option value="dispatched">Dispatched</option>
                        <option value="delivered">Delivered</option>
                        <option value="failed">Failed</option>
                        <option value="suppressed">Suppressed</option>
                    </select>
                    <input type="date" class="form-control form-control-sm" id="histFilterDateFrom" style="max-width: 160px; font-size: 0.85rem;" title="Since date">
                </div>
                <div class="table-responsive">
                    <table class="nc-table">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Alert</th>
                                <th>Severity</th>
                                <th>Status</th>
                                <th>Channels</th>
                            </tr>
                        </thead>
                        <tbody id="historyBody">
                            <tr><td colspan="5" class="nc-loading"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
                <div id="histPagination" class="nc-pagination"></div>
            </div>

            <div class="tab-pane fade" id="tab-preferences" role="tabpanel">
                <p class="text-muted mb-3" style="font-size: 0.85rem;">Control which alert categories you receive and through which channels.</p>
                <div id="prefsList">
                    <div class="nc-loading"><i class="fas fa-spinner fa-spin"></i> Loading preferences...</div>
                </div>
            </div>

            <div class="tab-pane fade" id="tab-channels" role="tabpanel">
                <p class="text-muted mb-3" style="font-size: 0.85rem;">Configure notification delivery channels for your account.</p>
                <div id="channelsList">
                    <div class="nc-loading"><i class="fas fa-spinner fa-spin"></i> Loading channel settings...</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="ruleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ruleModalTitle">Add Alert Rule</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="ruleEditId" value="">
                <div class="mb-3">
                    <label class="form-label">Category</label>
                    <select class="form-select" id="ruleCategory"></select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Trigger</label>
                    <select class="form-select" id="ruleTriggerKey"></select>
                </div>
                <div class="row mb-3">
                    <div class="col-6">
                        <label class="form-label">Operator</label>
                        <select class="form-select" id="ruleOperator"></select>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Value</label>
                        <input type="number" class="form-control" id="ruleCondValue" placeholder="e.g. 80">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Channels</label>
                    <div id="ruleChannelsGroup" class="d-flex flex-wrap gap-2"></div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Frequency</label>
                    <select class="form-select" id="ruleFrequency"></select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Cooldown (minutes)</label>
                    <input type="number" class="form-control" id="ruleCooldown" value="60" min="0">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btnSaveRule">Save Rule</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    var CSRF = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').content : '';
    var CATEGORIES = @json(config('alerting.categories'));
    var CHANNELS = @json(config('alerting.channels'));
    var FREQUENCIES = @json(config('alerting.frequencies'));
    var OPERATORS = @json(config('alerting.condition_operators'));
    var DEFAULTS = @json(config('alerting.defaults'));

    var OPERATOR_LABELS = {
        'lt': 'Less than', 'gt': 'Greater than', 'lte': 'Less than or equal',
        'gte': 'Greater than or equal', 'eq': 'Equals', 'drops_by': 'Drops by', 'increases_by': 'Increases by'
    };

    var state = { notifPage: 1, histPage: 1 };

    function sanitizeUrl(url) {
        if (!url || typeof url !== 'string') return '#';
        var trimmed = url.trim();
        if (/^https?:\/\//i.test(trimmed) || trimmed.startsWith('/')) return trimmed;
        return '#';
    }

    function escapeHtml(s) {
        if (!s) return '';
        var d = document.createElement('div');
        d.appendChild(document.createTextNode(s));
        return d.innerHTML;
    }

    function formatTimeAgo(dateStr) {
        if (!dateStr) return '';
        var d = new Date(dateStr);
        var now = new Date();
        var diffMin = Math.floor((now - d) / 60000);
        if (diffMin < 1) return 'Just now';
        if (diffMin < 60) return diffMin + 'm ago';
        var diffHr = Math.floor(diffMin / 60);
        if (diffHr < 24) return diffHr + 'h ago';
        var diffDay = Math.floor(diffHr / 24);
        if (diffDay < 30) return diffDay + 'd ago';
        return d.toLocaleDateString();
    }

    function formatDate(dateStr) {
        if (!dateStr) return '';
        var d = new Date(dateStr);
        return d.toLocaleDateString() + ' ' + d.toLocaleTimeString([], {hour: '2-digit', minute: '2-digit'});
    }

    function severityBadge(sev) {
        var cls = 'badge-info';
        if (sev === 'critical') cls = 'badge-danger';
        else if (sev === 'warning') cls = 'badge-warning';
        return '<span class="badge ' + cls + '">' + escapeHtml(sev) + '</span>';
    }

    function statusChip(status) {
        var cls = 'status-' + (status || 'dispatched');
        return '<span class="status-chip ' + cls + '">' + escapeHtml(status || 'unknown') + '</span>';
    }

    function channelTags(channels) {
        if (!channels || !channels.length) return '<span class="text-muted">—</span>';
        return channels.map(function(c) { return '<span class="channel-tag">' + escapeHtml(c) + '</span>'; }).join('');
    }

    function apiGet(url) {
        return fetch(url).then(function(r) {
            if (!r.ok) throw new Error('[NotificationCentre] GET ' + url + ' failed: ' + r.status);
            return r.json();
        });
    }

    function apiPost(url, body) {
        return fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: body ? JSON.stringify(body) : undefined
        }).then(function(r) {
            if (!r.ok) throw new Error('[NotificationCentre] POST ' + url + ' failed: ' + r.status);
            return r.json();
        });
    }

    function apiPut(url, body) {
        return fetch(url, {
            method: 'PUT',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify(body)
        }).then(function(r) {
            if (!r.ok) throw new Error('[NotificationCentre] PUT ' + url + ' failed: ' + r.status);
            return r.json();
        });
    }

    function apiDelete(url) {
        return fetch(url, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
        }).then(function(r) {
            if (!r.ok) throw new Error('[NotificationCentre] DELETE ' + url + ' failed: ' + r.status);
            return r.json();
        });
    }

    function renderPagination(containerId, pagination, loadFn) {
        var el = document.getElementById(containerId);
        if (!pagination || pagination.last_page <= 1) { el.innerHTML = ''; return; }
        var html = '';
        html += '<button ' + (pagination.current_page <= 1 ? 'disabled' : '') + ' data-page="' + (pagination.current_page - 1) + '">&laquo;</button>';
        for (var i = 1; i <= pagination.last_page; i++) {
            html += '<button class="' + (i === pagination.current_page ? 'active' : '') + '" data-page="' + i + '">' + i + '</button>';
        }
        html += '<button ' + (pagination.current_page >= pagination.last_page ? 'disabled' : '') + ' data-page="' + (pagination.current_page + 1) + '">&raquo;</button>';
        el.innerHTML = html;
        el.querySelectorAll('button').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var page = parseInt(this.getAttribute('data-page'));
                if (!isNaN(page)) loadFn(page);
            });
        });
    }

    function populateCategoryFilters() {
        var selects = [document.getElementById('notifFilterCategory'), document.getElementById('histFilterCategory')];
        selects.forEach(function(sel) {
            if (!sel) return;
            Object.keys(CATEGORIES).forEach(function(k) {
                var opt = document.createElement('option');
                opt.value = k;
                opt.textContent = CATEGORIES[k];
                sel.appendChild(opt);
            });
        });
    }

    function loadNotifications(page) {
        page = page || 1;
        state.notifPage = page;
        var params = '?page=' + page + '&per_page=15';
        var cat = document.getElementById('notifFilterCategory').value;
        var sev = document.getElementById('notifFilterSeverity').value;
        var read = document.getElementById('notifFilterRead').value;
        if (cat) params += '&category=' + cat;
        if (sev) params += '&severity=' + sev;
        if (read === 'unread') params += '&unread_only=1';
        else if (read === 'read') params += '&unread_only=0';
        else params += '&unread_only=0';

        var listEl = document.getElementById('notifList');
        listEl.innerHTML = '<div class="nc-loading"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';

        apiGet('/api/notifications/' + params)
            .then(function(result) {
                if (!result.success) throw new Error('API returned success=false');
                var items = result.data || [];
                var markBtn = document.getElementById('btnMarkAllRead');
                if (result.unread_count > 0) {
                    markBtn.style.display = 'inline-block';
                } else {
                    markBtn.style.display = 'none';
                }

                if (items.length === 0) {
                    listEl.innerHTML = '<div class="empty-state"><i class="fas fa-bell-slash"></i><p>No notifications yet</p></div>';
                    document.getElementById('notifPagination').innerHTML = '';
                    return;
                }

                var html = '';
                items.forEach(function(n) {
                    var isUnread = !n.read_at;
                    html += '<div class="notif-item ' + (isUnread ? 'unread' : '') + '" data-uuid="' + escapeHtml(n.uuid) + '">';
                    html += '<div class="d-flex justify-content-between align-items-start">';
                    html += '<div class="flex-grow-1">';
                    html += '<div class="d-flex align-items-center gap-2 mb-1">';
                    html += severityBadge(n.severity);
                    html += '<span class="category-chip">' + escapeHtml(CATEGORIES[n.category] || n.category) + '</span>';
                    html += '<small class="text-muted">' + formatTimeAgo(n.created_at) + '</small>';
                    html += '</div>';
                    html += '<h6 class="mb-1" style="font-size: 0.9rem; font-weight: ' + (isUnread ? '600' : '400') + ';">' + escapeHtml(n.title) + '</h6>';
                    html += '<p class="mb-1 text-muted" style="font-size: 0.8rem;">' + escapeHtml(n.body) + '</p>';
                    if (n.action_url && n.action_label) {
                        html += '<a href="' + escapeHtml(sanitizeUrl(n.action_url)) + '" class="btn btn-sm btn-outline-primary mt-1" style="font-size: 0.75rem;">' + escapeHtml(n.action_label) + '</a>';
                    }
                    html += '</div>';
                    html += '<div class="notif-actions d-flex gap-1">';
                    if (isUnread) {
                        html += '<button class="btn btn-sm btn-outline-secondary btn-mark-read" data-uuid="' + escapeHtml(n.uuid) + '" title="Mark read"><i class="fas fa-check"></i></button>';
                    }
                    if (!n.dismissed_at) {
                        html += '<button class="btn btn-sm btn-outline-secondary btn-dismiss" data-uuid="' + escapeHtml(n.uuid) + '" title="Dismiss"><i class="fas fa-times"></i></button>';
                    }
                    html += '</div>';
                    html += '</div></div>';
                });
                listEl.innerHTML = html;
                renderPagination('notifPagination', result.pagination, loadNotifications);

                listEl.querySelectorAll('.btn-mark-read').forEach(function(btn) {
                    btn.addEventListener('click', function() {
                        var uuid = this.getAttribute('data-uuid');
                        apiPost('/api/notifications/' + uuid + '/read').then(function() { loadNotifications(state.notifPage); })
                        .catch(function(err) { console.error(err.message); });
                    });
                });
                listEl.querySelectorAll('.btn-dismiss').forEach(function(btn) {
                    btn.addEventListener('click', function() {
                        var uuid = this.getAttribute('data-uuid');
                        apiPost('/api/notifications/' + uuid + '/dismiss').then(function() { loadNotifications(state.notifPage); })
                        .catch(function(err) { console.error(err.message); });
                    });
                });
            })
            .catch(function(err) {
                console.error(err.message);
                listEl.innerHTML = '<div class="nc-error"><i class="fas fa-exclamation-triangle"></i><p>Failed to load notifications</p><small>' + escapeHtml(err.message) + '</small></div>';
            });
    }

    function getTriggerTitle(triggerKey) {
        for (var i = 0; i < DEFAULTS.length; i++) {
            if (DEFAULTS[i].trigger_key === triggerKey) return DEFAULTS[i].title;
        }
        return triggerKey.replace(/_/g, ' ');
    }

    function loadRules() {
        var el = document.getElementById('rulesList');
        el.innerHTML = '<div class="nc-loading"><i class="fas fa-spinner fa-spin"></i> Loading rules...</div>';

        apiGet('/api/v1/alerts/rules')
            .then(function(result) {
                if (!result.success) throw new Error('API returned success=false');
                var rules = result.data || [];
                if (rules.length === 0) {
                    el.innerHTML = '<div class="empty-state"><i class="fas fa-cog"></i><p>No alert rules configured</p><small>Click "Add Rule" to create your first alert rule.</small></div>';
                    return;
                }
                var html = '';
                rules.forEach(function(r) {
                    html += '<div class="rule-row">';
                    html += '<div class="flex-grow-1">';
                    html += '<div class="d-flex align-items-center gap-2 mb-1">';
                    html += '<strong style="font-size: 0.9rem;">' + escapeHtml(getTriggerTitle(r.trigger_key)) + '</strong>';
                    html += '<span class="category-chip">' + escapeHtml(CATEGORIES[r.category] || r.category) + '</span>';
                    if (r.is_system_default) html += '<span class="category-chip" style="background: #ede9fe; color: #7c3aed;">System Default</span>';
                    html += '</div>';
                    html += '<div class="d-flex align-items-center gap-2" style="font-size: 0.8rem; color: #6b7280;">';
                    if (r.condition_operator && r.condition_value !== null) {
                        html += '<span>' + escapeHtml(OPERATOR_LABELS[r.condition_operator] || r.condition_operator) + ' ' + r.condition_value + '</span>';
                        html += '<span>·</span>';
                    }
                    html += '<span>' + escapeHtml(FREQUENCIES[r.frequency] || r.frequency) + '</span>';
                    html += '<span>·</span>';
                    html += channelTags(r.channels);
                    html += '</div>';
                    html += '</div>';
                    html += '<div class="d-flex align-items-center gap-2">';
                    html += '<div class="form-check form-switch">';
                    html += '<input class="form-check-input rule-toggle" type="checkbox" data-id="' + r.id + '" ' + (r.is_enabled ? 'checked' : '') + '>';
                    html += '</div>';
                    if (!r.is_system_default) {
                        html += '<div class="dropdown action-dropdown">';
                        html += '<button class="btn btn-sm btn-link text-muted" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-v"></i></button>';
                        html += '<ul class="dropdown-menu dropdown-menu-end">';
                        html += '<li><a class="dropdown-item rule-edit" href="#" data-id="' + r.id + '"><i class="fas fa-edit me-2"></i>Edit</a></li>';
                        html += '<li><a class="dropdown-item rule-delete text-danger" href="#" data-id="' + r.id + '"><i class="fas fa-trash me-2"></i>Delete</a></li>';
                        html += '</ul></div>';
                    }
                    html += '</div></div>';
                });
                el.innerHTML = html;

                el.querySelectorAll('.rule-toggle').forEach(function(toggle) {
                    toggle.addEventListener('change', function() {
                        var id = this.getAttribute('data-id');
                        var enabled = this.checked;
                        apiPut('/api/v1/alerts/rules/' + id, { is_enabled: enabled })
                            .catch(function(err) { console.error(err.message); loadRules(); });
                    });
                });

                el.querySelectorAll('.rule-edit').forEach(function(btn) {
                    btn.addEventListener('click', function(e) {
                        e.preventDefault();
                        var id = this.getAttribute('data-id');
                        var rule = rules.find(function(r) { return r.id == id; });
                        if (rule) openRuleModal(rule);
                    });
                });

                el.querySelectorAll('.rule-delete').forEach(function(btn) {
                    btn.addEventListener('click', function(e) {
                        e.preventDefault();
                        var id = this.getAttribute('data-id');
                        if (confirm('Are you sure you want to delete this rule?')) {
                            apiDelete('/api/v1/alerts/rules/' + id).then(function() { loadRules(); })
                                .catch(function(err) { console.error(err.message); });
                        }
                    });
                });
            })
            .catch(function(err) {
                console.error(err.message);
                el.innerHTML = '<div class="nc-error"><i class="fas fa-exclamation-triangle"></i><p>Failed to load rules</p><small>' + escapeHtml(err.message) + '</small></div>';
            });
    }

    function populateRuleModal() {
        var catSelect = document.getElementById('ruleCategory');
        catSelect.innerHTML = '';
        Object.keys(CATEGORIES).forEach(function(k) {
            var opt = document.createElement('option');
            opt.value = k;
            opt.textContent = CATEGORIES[k];
            catSelect.appendChild(opt);
        });

        var opSelect = document.getElementById('ruleOperator');
        opSelect.innerHTML = '';
        OPERATORS.forEach(function(op) {
            var opt = document.createElement('option');
            opt.value = op;
            opt.textContent = OPERATOR_LABELS[op] || op;
            opSelect.appendChild(opt);
        });

        var freqSelect = document.getElementById('ruleFrequency');
        freqSelect.innerHTML = '';
        Object.keys(FREQUENCIES).forEach(function(k) {
            var opt = document.createElement('option');
            opt.value = k;
            opt.textContent = FREQUENCIES[k];
            freqSelect.appendChild(opt);
        });

        var channelsEl = document.getElementById('ruleChannelsGroup');
        channelsEl.innerHTML = '';
        CHANNELS.forEach(function(ch) {
            var div = document.createElement('div');
            div.className = 'form-check';
            div.innerHTML = '<input class="form-check-input" type="checkbox" value="' + ch + '" id="ruleCh_' + ch + '">' +
                '<label class="form-check-label" for="ruleCh_' + ch + '">' + ch.charAt(0).toUpperCase() + ch.slice(1).replace('_', ' ') + '</label>';
            channelsEl.appendChild(div);
        });

        updateTriggerKeys();
        catSelect.addEventListener('change', updateTriggerKeys);
    }

    function updateTriggerKeys() {
        var cat = document.getElementById('ruleCategory').value;
        var tkSelect = document.getElementById('ruleTriggerKey');
        tkSelect.innerHTML = '';
        DEFAULTS.forEach(function(d) {
            if (d.category === cat) {
                var opt = document.createElement('option');
                opt.value = d.trigger_key;
                opt.textContent = d.title;
                tkSelect.appendChild(opt);
            }
        });
        if (tkSelect.options.length === 0) {
            var opt = document.createElement('option');
            opt.value = '';
            opt.textContent = 'No triggers for this category';
            tkSelect.appendChild(opt);
        }
    }

    function openRuleModal(rule) {
        document.getElementById('ruleModalTitle').textContent = rule ? 'Edit Alert Rule' : 'Add Alert Rule';
        document.getElementById('ruleEditId').value = rule ? rule.id : '';
        if (rule) {
            document.getElementById('ruleCategory').value = rule.category;
            updateTriggerKeys();
            document.getElementById('ruleTriggerKey').value = rule.trigger_key;
            document.getElementById('ruleOperator').value = rule.condition_operator || 'gte';
            document.getElementById('ruleCondValue').value = rule.condition_value || '';
            document.getElementById('ruleFrequency').value = rule.frequency || 'instant';
            document.getElementById('ruleCooldown').value = rule.cooldown_minutes || 60;
            CHANNELS.forEach(function(ch) {
                var cb = document.getElementById('ruleCh_' + ch);
                if (cb) cb.checked = rule.channels && rule.channels.indexOf(ch) !== -1;
            });
        } else {
            document.getElementById('ruleCategory').selectedIndex = 0;
            updateTriggerKeys();
            document.getElementById('ruleOperator').selectedIndex = 0;
            document.getElementById('ruleCondValue').value = '';
            document.getElementById('ruleFrequency').selectedIndex = 0;
            document.getElementById('ruleCooldown').value = 60;
            CHANNELS.forEach(function(ch) {
                var cb = document.getElementById('ruleCh_' + ch);
                if (cb) cb.checked = (ch === 'in_app');
            });
        }
        var modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('ruleModal'));
        modal.show();
    }

    function saveRule() {
        var editId = document.getElementById('ruleEditId').value;
        var selectedChannels = [];
        CHANNELS.forEach(function(ch) {
            var cb = document.getElementById('ruleCh_' + ch);
            if (cb && cb.checked) selectedChannels.push(ch);
        });
        var data = {
            category: document.getElementById('ruleCategory').value,
            trigger_key: document.getElementById('ruleTriggerKey').value,
            trigger_type: 'threshold',
            condition_operator: document.getElementById('ruleOperator').value,
            condition_value: document.getElementById('ruleCondValue').value ? parseFloat(document.getElementById('ruleCondValue').value) : null,
            channels: selectedChannels,
            frequency: document.getElementById('ruleFrequency').value,
            cooldown_minutes: parseInt(document.getElementById('ruleCooldown').value) || 60
        };

        var matched = DEFAULTS.find(function(d) { return d.trigger_key === data.trigger_key; });
        if (matched) data.trigger_type = matched.trigger_type;

        var promise = editId
            ? apiPut('/api/v1/alerts/rules/' + editId, data)
            : apiPost('/api/v1/alerts/rules', data);

        promise.then(function() {
            bootstrap.Modal.getOrCreateInstance(document.getElementById('ruleModal')).hide();
            loadRules();
        }).catch(function(err) {
            console.error(err.message);
            alert('Failed to save rule: ' + err.message);
        });
    }

    function loadHistory(page) {
        page = page || 1;
        state.histPage = page;
        var params = '?page=' + page + '&per_page=20';
        var cat = document.getElementById('histFilterCategory').value;
        var sev = document.getElementById('histFilterSeverity').value;
        var status = document.getElementById('histFilterStatus').value;
        var dateFrom = document.getElementById('histFilterDateFrom').value;
        if (cat) params += '&category=' + cat;
        if (sev) params += '&severity=' + sev;
        if (status) params += '&status=' + status;
        if (dateFrom) params += '&since=' + dateFrom + ' 00:00:00';

        var tbody = document.getElementById('historyBody');
        tbody.innerHTML = '<tr><td colspan="5" class="nc-loading"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>';

        apiGet('/api/v1/alerts/history' + params)
            .then(function(result) {
                if (!result.success) throw new Error('API returned success=false');
                var items = result.data || [];
                if (items.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="5" class="empty-state"><i class="fas fa-history"></i> No alert history</td></tr>';
                    document.getElementById('histPagination').innerHTML = '';
                    return;
                }
                var html = '';
                items.forEach(function(h) {
                    html += '<tr>';
                    html += '<td>' + formatDate(h.created_at) + '</td>';
                    html += '<td><strong>' + escapeHtml(h.title || getTriggerTitle(h.trigger_key)) + '</strong><br><small class="text-muted">' + escapeHtml(h.body || '') + '</small></td>';
                    html += '<td>' + severityBadge(h.severity) + '</td>';
                    html += '<td>' + statusChip(h.status) + '</td>';
                    html += '<td>' + channelTags(h.channels_dispatched) + '</td>';
                    html += '</tr>';
                });
                tbody.innerHTML = html;
                renderPagination('histPagination', result.pagination, loadHistory);
            })
            .catch(function(err) {
                console.error(err.message);
                tbody.innerHTML = '<tr><td colspan="5" class="nc-error"><i class="fas fa-exclamation-triangle"></i> Failed to load history</td></tr>';
            });
    }

    function loadHistorySummary() {
        apiGet('/api/v1/alerts/history/summary')
            .then(function(result) {
                if (!result.success) return;
                var d = result.data || {};
                var el = document.getElementById('historySummary');
                var cards = [
                    { label: 'Total Dispatched', count: d.dispatched_count || 0, icon: 'fa-paper-plane', color: '#2563eb' },
                    { label: 'Suppressed', count: d.suppressed_count || 0, icon: 'fa-ban', color: '#6b7280' },
                    { label: 'Critical', count: (d.by_severity || {}).critical || 0, icon: 'fa-exclamation-circle', color: '#dc2626' },
                    { label: 'Warning', count: (d.by_severity || {}).warning || 0, icon: 'fa-exclamation-triangle', color: '#d97706' }
                ];
                var html = '';
                cards.forEach(function(c) {
                    html += '<div class="col-6 col-md-3"><div class="summary-card">';
                    html += '<div class="count" style="color: ' + c.color + ';">' + c.count + '</div>';
                    html += '<div class="label"><i class="fas ' + c.icon + ' me-1"></i>' + c.label + '</div>';
                    html += '</div></div>';
                });
                el.innerHTML = html;
            })
            .catch(function(err) { console.warn('[NotificationCentre] Summary load failed:', err.message); });
    }

    function loadPreferences() {
        var el = document.getElementById('prefsList');
        el.innerHTML = '<div class="nc-loading"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';

        apiGet('/api/v1/alerts/preferences')
            .then(function(result) {
                if (!result.success) throw new Error('API returned success=false');
                var prefs = result.data || [];
                if (prefs.length === 0) {
                    el.innerHTML = '<div class="empty-state"><i class="fas fa-sliders-h"></i><p>No preferences available</p></div>';
                    return;
                }
                var html = '';
                prefs.forEach(function(p) {
                    var mutedUntilVal = '';
                    if (p.muted_until) {
                        var mu = new Date(p.muted_until);
                        mutedUntilVal = mu.toISOString().slice(0, 16);
                    }
                    html += '<div class="pref-row" data-category="' + escapeHtml(p.category) + '">';
                    html += '<div style="min-width: 180px;">';
                    html += '<strong style="font-size: 0.9rem;">' + escapeHtml(p.label || CATEGORIES[p.category] || p.category) + '</strong>';
                    if (p.muted_until) {
                        html += '<br><small class="text-warning"><i class="fas fa-clock me-1"></i>Muted until ' + formatDate(p.muted_until) + '</small>';
                    }
                    html += '</div>';
                    html += '<div class="d-flex align-items-center gap-3 flex-wrap">';
                    html += '<div class="d-flex gap-1">';
                    CHANNELS.forEach(function(ch) {
                        var active = p.channels && p.channels.indexOf(ch) !== -1;
                        html += '<button class="btn btn-sm ' + (active ? 'btn-outline-primary' : 'btn-outline-secondary') + ' pref-channel-toggle" data-category="' + escapeHtml(p.category) + '" data-channel="' + ch + '" style="font-size: 0.7rem; padding: 0.15rem 0.4rem;">' + ch + '</button>';
                    });
                    html += '</div>';
                    html += '<div class="d-flex align-items-center gap-2">';
                    html += '<input type="datetime-local" class="form-control form-control-sm pref-mute-until" data-category="' + escapeHtml(p.category) + '" value="' + mutedUntilVal + '" style="font-size: 0.75rem; width: 180px;" title="Mute until (leave empty for indefinite)">';
                    if (p.muted_until) {
                        html += '<button class="btn btn-sm btn-outline-warning pref-clear-mute" data-category="' + escapeHtml(p.category) + '" title="Clear mute timer" style="font-size: 0.7rem;"><i class="fas fa-times"></i></button>';
                    }
                    html += '</div>';
                    html += '<div class="form-check form-switch">';
                    html += '<input class="form-check-input pref-mute-toggle" type="checkbox" data-category="' + escapeHtml(p.category) + '" ' + (p.is_muted ? '' : 'checked') + ' title="' + (p.is_muted ? 'Muted' : 'Active') + '">';
                    html += '</div>';
                    html += '</div></div>';
                });
                el.innerHTML = html;

                el.querySelectorAll('.pref-mute-toggle').forEach(function(toggle) {
                    toggle.addEventListener('change', function() {
                        var cat = this.getAttribute('data-category');
                        apiPut('/api/v1/alerts/preferences', { category: cat, is_muted: !this.checked })
                            .then(function() { loadPreferences(); })
                            .catch(function(err) { console.error(err.message); loadPreferences(); });
                    });
                });

                el.querySelectorAll('.pref-channel-toggle').forEach(function(btn) {
                    btn.addEventListener('click', function() {
                        var cat = this.getAttribute('data-category');
                        var channel = this.getAttribute('data-channel');
                        var pref = prefs.find(function(p) { return p.category === cat; });
                        if (!pref) return;
                        var channels = (pref.channels || []).slice();
                        var idx = channels.indexOf(channel);
                        if (idx !== -1) channels.splice(idx, 1);
                        else channels.push(channel);
                        apiPut('/api/v1/alerts/preferences', { category: cat, channels: channels })
                            .then(function() { loadPreferences(); })
                            .catch(function(err) { console.error(err.message); loadPreferences(); });
                    });
                });

                el.querySelectorAll('.pref-mute-until').forEach(function(input) {
                    input.addEventListener('change', function() {
                        var cat = this.getAttribute('data-category');
                        var val = this.value;
                        var mutedUntil = val ? new Date(val).toISOString() : null;
                        apiPut('/api/v1/alerts/preferences', { category: cat, is_muted: !!val, muted_until: mutedUntil })
                            .then(function() { loadPreferences(); })
                            .catch(function(err) { console.error(err.message); loadPreferences(); });
                    });
                });

                el.querySelectorAll('.pref-clear-mute').forEach(function(btn) {
                    btn.addEventListener('click', function() {
                        var cat = this.getAttribute('data-category');
                        apiPut('/api/v1/alerts/preferences', { category: cat, is_muted: false, muted_until: null })
                            .then(function() { loadPreferences(); })
                            .catch(function(err) { console.error(err.message); loadPreferences(); });
                    });
                });
            })
            .catch(function(err) {
                console.error(err.message);
                el.innerHTML = '<div class="nc-error"><i class="fas fa-exclamation-triangle"></i><p>Failed to load preferences</p></div>';
            });
    }

    function loadChannels() {
        var el = document.getElementById('channelsList');
        el.innerHTML = '<div class="nc-loading"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';

        apiGet('/api/v1/alerts/channels')
            .then(function(result) {
                if (!result.success) throw new Error('API returned success=false');
                var channels = result.data || [];
                var html = '';

                html += '<div class="channel-cfg-card">';
                html += '<h6><i class="fas fa-envelope me-2 text-primary"></i>Email</h6>';
                html += '<p style="font-size: 0.85rem; color: #6b7280;">Email notifications are sent to your account email address. Enable or disable them in the Preferences tab.</p>';
                html += '</div>';

                var channelDefs = [
                    { key: 'webhook', icon: 'fa-plug', label: 'Webhook', color: 'text-warning' },
                    { key: 'slack', icon: 'fa-hashtag', label: 'Slack', color: 'text-success' },
                    { key: 'teams', icon: 'fa-users', label: 'Microsoft Teams', color: 'text-info' },
                    { key: 'sms', icon: 'fa-mobile-alt', label: 'SMS', color: 'text-danger' }
                ];

                channelDefs.forEach(function(cd) {
                    var existing = channels.find(function(c) { return c.channel === cd.key; });
                    html += '<div class="channel-cfg-card" data-channel="' + cd.key + '">';
                    html += '<div class="d-flex justify-content-between align-items-center mb-2">';
                    html += '<h6 class="mb-0"><i class="fas ' + cd.icon + ' me-2 ' + cd.color + '"></i>' + cd.label + '</h6>';
                    html += '<div class="form-check form-switch">';
                    html += '<input class="form-check-input channel-enabled-toggle" type="checkbox" data-channel="' + cd.key + '" data-id="' + (existing ? existing.id : '') + '" ' + (existing && existing.is_enabled ? 'checked' : '') + '>';
                    html += '</div></div>';

                    if (cd.key === 'webhook') {
                        var urlSet = existing && existing.config && existing.config.webhook_url_set;
                        var hmac = existing && existing.config ? existing.config.hmac_secret : '';
                        html += '<div class="mb-2"><label class="form-label" style="font-size: 0.8rem;">Webhook URL</label>';
                        html += '<div class="input-group"><input type="url" class="form-control form-control-sm channel-webhook-url" placeholder="https://your-server.com/webhook" data-channel="webhook"></div></div>';
                        if (urlSet) html += '<small class="text-success d-block mb-2"><i class="fas fa-check-circle me-1"></i>Webhook URL is configured</small>';
                        if (hmac) html += '<div class="mb-2"><label class="form-label" style="font-size: 0.8rem;">HMAC Secret</label><input type="text" class="form-control form-control-sm" value="' + escapeHtml(hmac) + '" readonly></div>';
                        html += '<button class="btn btn-sm btn-outline-primary channel-save" data-channel="webhook"><i class="fas fa-save me-1"></i>Save</button>';
                    } else if (cd.key === 'slack') {
                        var slackUrl = existing && existing.config ? existing.config.slack_webhook_url : '';
                        html += '<div class="mb-2"><label class="form-label" style="font-size: 0.8rem;">Slack Webhook URL</label>';
                        html += '<input type="url" class="form-control form-control-sm channel-slack-url" value="' + escapeHtml(slackUrl || '') + '" placeholder="https://hooks.slack.com/services/..." data-channel="slack"></div>';
                        html += '<button class="btn btn-sm btn-outline-primary channel-save" data-channel="slack"><i class="fas fa-save me-1"></i>Save</button>';
                    } else if (cd.key === 'teams') {
                        var teamsUrl = existing && existing.config ? existing.config.teams_webhook_url : '';
                        html += '<div class="mb-2"><label class="form-label" style="font-size: 0.8rem;">Teams Webhook URL</label>';
                        html += '<input type="url" class="form-control form-control-sm channel-teams-url" value="' + escapeHtml(teamsUrl || '') + '" placeholder="https://outlook.office.com/webhook/..." data-channel="teams"></div>';
                        html += '<button class="btn btn-sm btn-outline-primary channel-save" data-channel="teams"><i class="fas fa-save me-1"></i>Save</button>';
                    } else if (cd.key === 'sms') {
                        var smsPhone = existing && existing.config ? existing.config.phone : '';
                        html += '<div class="mb-2"><label class="form-label" style="font-size: 0.8rem;">Phone Number</label>';
                        html += '<input type="tel" class="form-control form-control-sm channel-sms-phone" value="' + escapeHtml(smsPhone || '') + '" placeholder="+44 7xxx xxx xxx" data-channel="sms"></div>';
                        html += '<button class="btn btn-sm btn-outline-primary channel-save" data-channel="sms"><i class="fas fa-save me-1"></i>Save</button>';
                    }

                    if (existing) {
                        html += ' <button class="btn btn-sm btn-outline-danger channel-delete ms-2" data-channel="' + cd.key + '" title="Remove configuration"><i class="fas fa-trash me-1"></i>Remove</button>';
                    }

                    html += '</div>';
                });

                el.innerHTML = html;

                el.querySelectorAll('.channel-enabled-toggle').forEach(function(toggle) {
                    toggle.addEventListener('change', function() {
                        var channel = this.getAttribute('data-channel');
                        apiPut('/api/v1/alerts/channels/' + channel, { is_enabled: this.checked })
                            .then(function() { loadChannels(); })
                            .catch(function(err) { console.error(err.message); loadChannels(); });
                    });
                });

                el.querySelectorAll('.channel-save').forEach(function(btn) {
                    btn.addEventListener('click', function() {
                        var channel = this.getAttribute('data-channel');
                        var config = {};
                        if (channel === 'webhook') {
                            var urlInput = el.querySelector('.channel-webhook-url');
                            if (urlInput && urlInput.value) config.webhook_url = urlInput.value;
                        } else if (channel === 'slack') {
                            var slackInput = el.querySelector('.channel-slack-url');
                            if (slackInput) config.slack_webhook_url = slackInput.value;
                        } else if (channel === 'teams') {
                            var teamsInput = el.querySelector('.channel-teams-url');
                            if (teamsInput) config.teams_webhook_url = teamsInput.value;
                        } else if (channel === 'sms') {
                            var smsInput = el.querySelector('.channel-sms-phone');
                            if (smsInput) config.phone = smsInput.value;
                        }
                        apiPut('/api/v1/alerts/channels/' + channel, { config: config, is_enabled: true })
                            .then(function() { loadChannels(); alert('Channel saved successfully.'); })
                            .catch(function(err) { console.error(err.message); alert('Failed to save: ' + err.message); });
                    });
                });

                el.querySelectorAll('.channel-delete').forEach(function(btn) {
                    btn.addEventListener('click', function() {
                        var channel = this.getAttribute('data-channel');
                        if (confirm('Remove ' + channel + ' channel configuration?')) {
                            apiDelete('/api/v1/alerts/channels/' + channel)
                                .then(function() { loadChannels(); })
                                .catch(function(err) { console.error(err.message); alert('Failed to remove: ' + err.message); });
                        }
                    });
                });

            })
            .catch(function(err) {
                console.error(err.message);
                el.innerHTML = '<div class="nc-error"><i class="fas fa-exclamation-triangle"></i><p>Failed to load channels</p></div>';
            });
    }

    document.addEventListener('DOMContentLoaded', function() {
        console.log('[NotificationCentre] Initialized');
        populateCategoryFilters();
        populateRuleModal();
        loadNotifications(1);

        document.getElementById('btnMarkAllRead').addEventListener('click', function() {
            apiPost('/api/notifications/mark-all-read').then(function() { loadNotifications(state.notifPage); })
                .catch(function(err) { console.error(err.message); });
        });

        document.getElementById('btnAddRule').addEventListener('click', function() { openRuleModal(null); });
        document.getElementById('btnSaveRule').addEventListener('click', saveRule);

        ['notifFilterCategory', 'notifFilterSeverity', 'notifFilterRead'].forEach(function(id) {
            document.getElementById(id).addEventListener('change', function() { loadNotifications(1); });
        });
        ['histFilterCategory', 'histFilterSeverity', 'histFilterStatus', 'histFilterDateFrom'].forEach(function(id) {
            document.getElementById(id).addEventListener('change', function() { loadHistory(1); });
        });

        document.querySelectorAll('#ncTabs .nav-link').forEach(function(tab) {
            tab.addEventListener('shown.bs.tab', function(e) {
                var target = e.target.getAttribute('href');
                if (target === '#tab-notifications') loadNotifications(state.notifPage);
                else if (target === '#tab-rules') loadRules();
                else if (target === '#tab-history') { loadHistory(1); loadHistorySummary(); }
                else if (target === '#tab-preferences') loadPreferences();
                else if (target === '#tab-channels') loadChannels();
            });
        });
    });
})();
</script>
@endpush
