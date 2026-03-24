@extends('layouts.admin')

@section('title', 'Notification Centre')

@push('styles')
<style>
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
.notif-item.severity-critical {
    border-left: 3px solid #ef4444;
    background: #fff5f5;
}
.notif-item.severity-warning {
    border-left: 3px solid #f59e0b;
    background: #fff9e6;
}
.notif-item.severity-info {
    border-left: 3px solid #886cc0;
    background: #faf9fc;
}
.notif-item.severity-success {
    border-left: 3px solid #22c55e;
    background: #f0fdf4;
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
.status-batched { background: #ede9fe; color: #7c3aed; }
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
.dash-card {
    text-align: center;
    padding: 1.25rem;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    background: #fff;
}
.dash-card .count {
    font-size: 2rem;
    font-weight: 700;
    color: #374151;
}
.dash-card .label {
    font-size: 0.8rem;
    color: #6b7280;
    margin-top: 0.25rem;
}
.dash-card.critical .count { color: #dc2626; }
.dash-card.warning .count { color: #d97706; }
.dash-card.info .count { color: #2563eb; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1" style="background: transparent; padding: 0;">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" style="color: #6c757d; text-decoration: none;">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#" style="color: #6c757d; text-decoration: none;">Management</a></li>
                    <li class="breadcrumb-item active" style="font-weight: 500;">Notification Centre</li>
                </ol>
            </nav>
            <h4 class="mb-0" style="font-weight: 600; color: #374151;">Notification Centre</h4>
        </div>
    </div>

    <div class="nc-card">
        <ul class="nav nc-tabs px-3 pt-2" id="adminNcTabs" role="tablist">
            <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tab-admin-notifications" role="tab">Notifications</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-admin-rules" role="tab">Alert Rules</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-admin-dashboard" role="tab">Dashboard</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-admin-history" role="tab">History</a></li>
        </ul>

        <div class="tab-content nc-card-body">
            <div class="tab-pane fade show active" id="tab-admin-notifications" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="filter-bar">
                        <select id="adminNotifFilterCategory">
                            <option value="">All Categories</option>
                        </select>
                        <select id="adminNotifFilterSeverity">
                            <option value="">All Severities</option>
                            <option value="critical">Critical</option>
                            <option value="warning">Warning</option>
                            <option value="info">Info</option>
                        </select>
                        <select id="adminNotifFilterRead">
                            <option value="">All</option>
                            <option value="unread">Unread</option>
                            <option value="read">Read</option>
                        </select>
                    </div>
                    <button class="btn btn-sm btn-outline-primary" id="adminBtnMarkAllRead" style="display: none;">
                        <i class="fas fa-check-double me-1"></i>Mark All Read
                    </button>
                </div>
                <div id="adminNotifList">
                    <div class="nc-loading"><i class="fas fa-spinner fa-spin"></i> Loading notifications...</div>
                </div>
                <div id="adminNotifPagination" class="nc-pagination"></div>
            </div>

            <div class="tab-pane fade" id="tab-admin-rules" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <p class="text-muted mb-0" style="font-size: 0.85rem;">Manage system-wide alert rules for platform monitoring.</p>
                    <button class="btn btn-sm btn-primary" id="adminBtnAddRule">
                        <i class="fas fa-plus me-1"></i>Add Rule
                    </button>
                </div>
                <div id="adminRulesList">
                    <div class="nc-loading"><i class="fas fa-spinner fa-spin"></i> Loading rules...</div>
                </div>
            </div>

            <div class="tab-pane fade" id="tab-admin-dashboard" role="tabpanel">
                <div id="adminDashboard">
                    <div class="nc-loading"><i class="fas fa-spinner fa-spin"></i> Loading dashboard...</div>
                </div>
            </div>

            <div class="tab-pane fade" id="tab-admin-history" role="tabpanel">
                <div class="filter-bar mb-3">
                    <select id="adminHistFilterCategory">
                        <option value="">All Categories</option>
                    </select>
                    <select id="adminHistFilterSeverity">
                        <option value="">All Severities</option>
                        <option value="critical">Critical</option>
                        <option value="warning">Warning</option>
                        <option value="info">Info</option>
                    </select>
                    <select id="adminHistFilterStatus">
                        <option value="">All Statuses</option>
                        <option value="dispatched">Dispatched</option>
                        <option value="delivered">Delivered</option>
                        <option value="failed">Failed</option>
                        <option value="suppressed">Suppressed</option>
                        <option value="batched">Batched</option>
                    </select>
                    <input type="text" class="form-control form-control-sm" id="adminHistFilterTrigger" placeholder="Trigger key" style="max-width: 200px; font-size: 0.85rem;">
                    <input type="text" class="form-control form-control-sm" id="adminHistFilterTenant" placeholder="Tenant ID" style="max-width: 250px; font-size: 0.85rem;">
                    <input type="date" class="form-control form-control-sm" id="adminHistFilterDateFrom" style="max-width: 160px; font-size: 0.85rem;" title="Since date">
                </div>
                <div class="table-responsive">
                    <table class="nc-table">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Trigger</th>
                                <th>Severity</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Channels</th>
                                <th>Tenant</th>
                            </tr>
                        </thead>
                        <tbody id="adminHistoryBody">
                            <tr><td colspan="7" class="nc-loading"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
                <div id="adminHistPagination" class="nc-pagination"></div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="adminRuleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="adminRuleModalTitle">Add Alert Rule</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="adminRuleEditId" value="">
                <div class="mb-3">
                    <label class="form-label">Category</label>
                    <select class="form-select" id="adminRuleCategory"></select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Trigger Key</label>
                    <select class="form-select" id="adminRuleTriggerKey"></select>
                </div>
                <div class="row mb-3" id="adminRuleOperatorValueRow">
                    <div class="col-6" id="adminRuleOperatorCol">
                        <label class="form-label">Operator</label>
                        <select class="form-select" id="adminRuleOperator"></select>
                    </div>
                    <div class="col-6" id="adminRuleValueCol">
                        <label class="form-label" id="adminRuleCondValueLabel">Value</label>
                        <input type="number" class="form-control" id="adminRuleCondValue" placeholder="e.g. 80">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Channels</label>
                    <div id="adminRuleChannelsGroup" class="d-flex flex-wrap gap-2"></div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Frequency</label>
                    <select class="form-select" id="adminRuleFrequency"></select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Cooldown (minutes)</label>
                    <input type="number" class="form-control" id="adminRuleCooldown" value="60" min="0">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="adminBtnSaveRule">Save Rule</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    var CSRF = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').content : '';
    var CUSTOMER_CATEGORIES = @json(config('alerting.categories'));
    var ADMIN_ONLY_CATEGORIES = @json(config('alerting.admin_categories'));
    var ADMIN_CATEGORIES = Object.assign({}, CUSTOMER_CATEGORIES, ADMIN_ONLY_CATEGORIES);
    var CHANNELS = @json(config('alerting.channels'));
    var FREQUENCIES = @json(config('alerting.frequencies'));
    var OPERATORS = @json(config('alerting.condition_operators'));
    var ADMIN_DEFAULTS = @json(config('alerting.admin_defaults'));

    var OPERATOR_LABELS = {
        'lt': 'Less than', 'gt': 'Greater than', 'lte': 'Less than or equal',
        'gte': 'Greater than or equal', 'eq': 'Equals', 'drops_by': 'Drops by', 'increases_by': 'Increases by'
    };

    var CATEGORY_COLORS = {
        'billing': { bg: '#fef3c7', color: '#92400e' },
        'messaging': { bg: '#dbeafe', color: '#1e40af' },
        'compliance': { bg: '#fce7f3', color: '#9d174d' },
        'security': { bg: '#fee2e2', color: '#991b1b' },
        'system': { bg: '#e0e7ff', color: '#3730a3' },
        'campaign': { bg: '#d1fae5', color: '#065f46' },
        'sub_account': { bg: '#ede9fe', color: '#5b21b6' },
        'fraud': { bg: '#fee2e2', color: '#991b1b' },
        'platform_health': { bg: '#cffafe', color: '#155e75' },
        'customer_risk': { bg: '#ffedd5', color: '#9a3412' },
        'commercial': { bg: '#fef9c3', color: '#854d0e' },
        'compliance_legal': { bg: '#fce7f3', color: '#9d174d' }
    };

    var CHANNEL_LABELS = {
        'in_app': 'In App', 'email': 'Email', 'sms': 'SMS',
        'webhook': 'Webhook', 'slack': 'Slack', 'teams': 'Teams'
    };

    var TRIGGER_VALUE_LABELS = {
        'credit_balance_percentage': 'Balance (£)',
        'spend_rate': 'Spend Change (%)',
        'delivery_rate': 'Delivery Rate (%)',
        'failed_messages': 'Message Count',
        'campaign_delivery_rate': 'Delivery Rate (%)',
        'campaign_roi': 'ROI (%)',
        'daily_message_volume': 'Message Count',
        'api_error_rate': 'Error Rate (%)',
        'api_latency': 'Latency (ms)',
        'sub_account_spend_cap_approaching': 'Within (% of cap)',
        'sub_account_volume_cap_approaching': 'Within (% of cap)',
        'sub_account_daily_limit_approaching': 'Within (% of cap)',
        'queue_backlog': 'Queue Size',
        'dlr_latency_seconds': 'Latency (seconds)'
    };

    var CAP_REACHED_TRIGGERS = [
        'sub_account_spend_cap',
        'sub_account_volume_cap',
        'sub_account_daily_limit'
    ];

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

    function categoryChip(categoryKey) {
        var label = ADMIN_CATEGORIES[categoryKey] || categoryKey;
        var colors = CATEGORY_COLORS[categoryKey] || { bg: '#f3f4f6', color: '#6b7280' };
        return '<span class="category-chip" style="background: ' + colors.bg + '; color: ' + colors.color + ';">' + escapeHtml(label) + '</span>';
    }

    function channelTags(channels) {
        if (!channels || !channels.length) return '<span class="text-muted">—</span>';
        return '<span style="font-size: 0.8rem; color: #6b7280;">' + channels.map(function(c) { return escapeHtml(CHANNEL_LABELS[c] || c); }).join(' | ') + '</span>';
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

    function getAdminTriggerTitle(triggerKey) {
        for (var i = 0; i < ADMIN_DEFAULTS.length; i++) {
            if (ADMIN_DEFAULTS[i].trigger_key === triggerKey) return ADMIN_DEFAULTS[i].title;
        }
        return triggerKey.replace(/_/g, ' ');
    }

    function loadAdminNotifications(page) {
        page = page || 1;
        state.notifPage = page;
        var params = '?page=' + page + '&per_page=15';
        var cat = document.getElementById('adminNotifFilterCategory').value;
        var sev = document.getElementById('adminNotifFilterSeverity').value;
        var read = document.getElementById('adminNotifFilterRead').value;
        if (cat) params += '&category=' + cat;
        if (sev) params += '&severity=' + sev;
        if (read === 'unread') params += '&unread_only=1';
        else if (read === 'read') params += '&unread_only=0';
        else params += '&unread_only=0';

        var listEl = document.getElementById('adminNotifList');
        listEl.innerHTML = '<div class="nc-loading"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';

        apiGet('/admin/api/notifications/' + params)
            .then(function(result) {
                if (!result.success) throw new Error('API returned success=false');
                var items = result.data || [];
                var markBtn = document.getElementById('adminBtnMarkAllRead');
                if (result.unread_count > 0) {
                    markBtn.style.display = 'inline-block';
                } else {
                    markBtn.style.display = 'none';
                }

                if (items.length === 0) {
                    listEl.innerHTML = '<div class="empty-state"><i class="fas fa-bell-slash"></i><p>No notifications yet</p></div>';
                    document.getElementById('adminNotifPagination').innerHTML = '';
                    return;
                }

                var html = '';
                items.forEach(function(n) {
                    var isUnread = !n.read_at;
                    var sevClass = 'severity-' + (n.severity || 'info');
                    html += '<div class="notif-item ' + sevClass + ' ' + (isUnread ? 'unread' : '') + '" data-uuid="' + escapeHtml(n.uuid) + '">';
                    html += '<div class="d-flex justify-content-between align-items-start">';
                    html += '<div class="flex-grow-1">';
                    html += '<div class="d-flex align-items-center gap-2 mb-1">';
                    html += categoryChip(n.category);
                    html += '<small class="text-muted">' + formatTimeAgo(n.created_at) + '</small>';
                    if (n.resolved_at) html += '<span class="category-chip" style="background: #d1fae5; color: #059669;"><i class="fas fa-check me-1"></i>Resolved</span>';
                    html += '</div>';
                    html += '<h6 class="mb-1" style="font-size: 0.9rem; font-weight: ' + (isUnread ? '600' : '400') + ';">' + escapeHtml(n.title) + '</h6>';
                    html += '<p class="mb-1 text-muted" style="font-size: 0.8rem;">' + escapeHtml(n.body) + '</p>';
                    if (n.action_url && n.action_label) {
                        html += '<a href="' + escapeHtml(sanitizeUrl(n.action_url)) + '" class="btn btn-sm btn-outline-primary mt-1" style="font-size: 0.75rem;">' + escapeHtml(n.action_label) + '</a>';
                    }
                    html += '</div>';
                    html += '<div class="notif-actions d-flex gap-1">';
                    if (!n.resolved_at) {
                        html += '<button class="btn btn-sm btn-outline-success btn-resolve" data-uuid="' + escapeHtml(n.uuid) + '" title="Resolve"><i class="fas fa-check-circle"></i></button>';
                    }
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
                renderPagination('adminNotifPagination', result.pagination, loadAdminNotifications);

                listEl.querySelectorAll('.btn-mark-read').forEach(function(btn) {
                    btn.addEventListener('click', function() {
                        var uuid = this.getAttribute('data-uuid');
                        apiPost('/admin/api/notifications/' + uuid + '/read').then(function() { loadAdminNotifications(state.notifPage); })
                        .catch(function(err) { console.error(err.message); });
                    });
                });
                listEl.querySelectorAll('.btn-dismiss').forEach(function(btn) {
                    btn.addEventListener('click', function() {
                        var uuid = this.getAttribute('data-uuid');
                        apiPost('/admin/api/notifications/' + uuid + '/dismiss').then(function() { loadAdminNotifications(state.notifPage); })
                        .catch(function(err) { console.error(err.message); });
                    });
                });
                listEl.querySelectorAll('.btn-resolve').forEach(function(btn) {
                    btn.addEventListener('click', function() {
                        var uuid = this.getAttribute('data-uuid');
                        apiPost('/admin/api/notifications/' + uuid + '/resolve').then(function() { loadAdminNotifications(state.notifPage); })
                        .catch(function(err) { console.error(err.message); });
                    });
                });
            })
            .catch(function(err) {
                console.error(err.message);
                listEl.innerHTML = '<div class="nc-error"><i class="fas fa-exclamation-triangle"></i><p>Failed to load notifications</p><small>' + escapeHtml(err.message) + '</small></div>';
            });
    }

    function loadAdminRules() {
        var el = document.getElementById('adminRulesList');
        el.innerHTML = '<div class="nc-loading"><i class="fas fa-spinner fa-spin"></i> Loading rules...</div>';

        apiGet('/admin/api/alerts/rules')
            .then(function(result) {
                if (!result.success) throw new Error('API returned success=false');
                var rules = result.data || [];
                if (rules.length === 0) {
                    el.innerHTML = '<div class="empty-state"><i class="fas fa-cog"></i><p>No alert rules configured</p></div>';
                    return;
                }
                var html = '';
                rules.forEach(function(r) {
                    html += '<div class="rule-row">';
                    html += '<div class="flex-grow-1">';
                    html += '<div class="d-flex align-items-center gap-2 mb-1">';
                    html += '<strong style="font-size: 0.9rem;">' + escapeHtml(getAdminTriggerTitle(r.trigger_key)) + '</strong>';
                    html += categoryChip(r.category);
                    html += '</div>';
                    html += '<div class="d-flex align-items-center gap-2" style="font-size: 0.8rem; color: #6b7280;">';
                    if (r.condition_operator && r.condition_value !== null) {
                        html += '<span>' + escapeHtml(OPERATOR_LABELS[r.condition_operator] || r.condition_operator) + ' ' + r.condition_value + '</span>';
                        html += '<span>·</span>';
                    }
                    html += '<span>' + escapeHtml(FREQUENCIES[r.frequency] || r.frequency) + '</span>';
                    if (r.cooldown_minutes) {
                        html += '<span>·</span><span>Cooldown: ' + r.cooldown_minutes + 'min</span>';
                    }
                    html += '</div>';
                    html += '<div style="font-size: 0.8rem; color: #6b7280; margin-top: 2px;">';
                    html += channelTags(r.channels);
                    html += '</div>';
                    html += '</div>';
                    html += '<div class="d-flex align-items-center gap-2">';
                    html += '<div class="form-check form-switch">';
                    html += '<input class="form-check-input admin-rule-toggle" type="checkbox" data-id="' + r.id + '" ' + (r.is_enabled ? 'checked' : '') + '>';
                    html += '</div>';
                    html += '<div class="dropdown action-dropdown">';
                    html += '<button class="btn btn-sm btn-link text-muted" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-v"></i></button>';
                    html += '<ul class="dropdown-menu dropdown-menu-end">';
                    html += '<li><a class="dropdown-item admin-rule-edit" href="#" data-id="' + r.id + '"><i class="fas fa-edit me-2"></i>Edit</a></li>';
                    if (!r.is_system_default) {
                        html += '<li><a class="dropdown-item admin-rule-delete text-danger" href="#" data-id="' + r.id + '"><i class="fas fa-trash me-2"></i>Delete</a></li>';
                    }
                    html += '</ul></div>';
                    html += '</div></div>';
                });
                el.innerHTML = html;

                el.querySelectorAll('.admin-rule-toggle').forEach(function(toggle) {
                    toggle.addEventListener('change', function() {
                        var id = this.getAttribute('data-id');
                        apiPut('/admin/api/alerts/rules/' + id, { is_enabled: this.checked })
                            .catch(function(err) { console.error(err.message); loadAdminRules(); });
                    });
                });

                el.querySelectorAll('.admin-rule-edit').forEach(function(btn) {
                    btn.addEventListener('click', function(e) {
                        e.preventDefault();
                        var id = this.getAttribute('data-id');
                        var rule = rules.find(function(r) { return r.id == id; });
                        if (rule) openAdminRuleModal(rule);
                    });
                });

                el.querySelectorAll('.admin-rule-delete').forEach(function(btn) {
                    btn.addEventListener('click', function(e) {
                        e.preventDefault();
                        var id = this.getAttribute('data-id');
                        if (confirm('Are you sure you want to delete this rule?')) {
                            apiDelete('/admin/api/alerts/rules/' + id).then(function() { loadAdminRules(); })
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

    function populateAdminRuleModal() {
        var catSelect = document.getElementById('adminRuleCategory');
        catSelect.innerHTML = '';
        Object.keys(ADMIN_CATEGORIES).forEach(function(k) {
            var opt = document.createElement('option');
            opt.value = k;
            opt.textContent = ADMIN_CATEGORIES[k];
            catSelect.appendChild(opt);
        });

        var opSelect = document.getElementById('adminRuleOperator');
        opSelect.innerHTML = '';
        OPERATORS.forEach(function(op) {
            var opt = document.createElement('option');
            opt.value = op;
            opt.textContent = OPERATOR_LABELS[op] || op;
            opSelect.appendChild(opt);
        });

        var freqSelect = document.getElementById('adminRuleFrequency');
        freqSelect.innerHTML = '';
        Object.keys(FREQUENCIES).forEach(function(k) {
            var opt = document.createElement('option');
            opt.value = k;
            opt.textContent = FREQUENCIES[k];
            freqSelect.appendChild(opt);
        });

        var channelsEl = document.getElementById('adminRuleChannelsGroup');
        channelsEl.innerHTML = '';
        CHANNELS.forEach(function(ch) {
            var div = document.createElement('div');
            div.className = 'form-check';
            div.innerHTML = '<input class="form-check-input" type="checkbox" value="' + ch + '" id="adminRuleCh_' + ch + '">' +
                '<label class="form-check-label" for="adminRuleCh_' + ch + '">' + ch.charAt(0).toUpperCase() + ch.slice(1).replace('_', ' ') + '</label>';
            channelsEl.appendChild(div);
        });

        updateAdminTriggerKeys();
        catSelect.addEventListener('change', updateAdminTriggerKeys);
        document.getElementById('adminRuleTriggerKey').addEventListener('change', updateAdminCondValueLabel);
    }

    function updateAdminTriggerKeys() {
        var cat = document.getElementById('adminRuleCategory').value;
        var tkSelect = document.getElementById('adminRuleTriggerKey');
        tkSelect.innerHTML = '';
        ADMIN_DEFAULTS.forEach(function(d) {
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
        updateAdminCondValueLabel();
    }

    function getAdminTriggerType(triggerKey) {
        for (var i = 0; i < ADMIN_DEFAULTS.length; i++) {
            if (ADMIN_DEFAULTS[i].trigger_key === triggerKey) return ADMIN_DEFAULTS[i].trigger_type;
        }
        return 'threshold';
    }

    function updateAdminCondValueLabel() {
        var tk = document.getElementById('adminRuleTriggerKey').value;
        var isEvent = (getAdminTriggerType(tk) === 'event');
        var isCapReached = (CAP_REACHED_TRIGGERS.indexOf(tk) !== -1);
        var isApproaching = (tk.indexOf('_approaching') !== -1);
        var row = document.getElementById('adminRuleOperatorValueRow');
        var opCol = document.getElementById('adminRuleOperatorCol');
        var valCol = document.getElementById('adminRuleValueCol');
        if (isEvent || isCapReached) {
            row.classList.add('d-none');
        } else if (isApproaching) {
            row.classList.remove('d-none');
            opCol.classList.add('d-none');
            valCol.classList.remove('col-6');
            valCol.classList.add('col-12');
        } else {
            row.classList.remove('d-none');
            opCol.classList.remove('d-none');
            valCol.classList.remove('col-12');
            valCol.classList.add('col-6');
        }
        document.getElementById('adminRuleCondValueLabel').textContent = TRIGGER_VALUE_LABELS[tk] || 'Value';
    }

    function openAdminRuleModal(rule) {
        document.getElementById('adminRuleModalTitle').textContent = rule ? 'Edit Alert Rule' : 'Add Alert Rule';
        document.getElementById('adminRuleEditId').value = rule ? rule.id : '';
        if (rule) {
            document.getElementById('adminRuleCategory').value = rule.category;
            updateAdminTriggerKeys();
            document.getElementById('adminRuleTriggerKey').value = rule.trigger_key;
            updateAdminCondValueLabel();
            document.getElementById('adminRuleOperator').value = rule.condition_operator || 'gte';
            document.getElementById('adminRuleCondValue').value = rule.condition_value || '';
            document.getElementById('adminRuleFrequency').value = rule.frequency || 'instant';
            document.getElementById('adminRuleCooldown').value = rule.cooldown_minutes || 60;
            CHANNELS.forEach(function(ch) {
                var cb = document.getElementById('adminRuleCh_' + ch);
                if (cb) cb.checked = rule.channels && rule.channels.indexOf(ch) !== -1;
            });
        } else {
            document.getElementById('adminRuleCategory').selectedIndex = 0;
            updateAdminTriggerKeys();
            document.getElementById('adminRuleOperator').selectedIndex = 0;
            document.getElementById('adminRuleCondValue').value = '';
            document.getElementById('adminRuleFrequency').selectedIndex = 0;
            document.getElementById('adminRuleCooldown').value = 60;
            CHANNELS.forEach(function(ch) {
                var cb = document.getElementById('adminRuleCh_' + ch);
                if (cb) cb.checked = (ch === 'in_app');
            });
        }
        var modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('adminRuleModal'));
        modal.show();
    }

    function saveAdminRule() {
        var editId = document.getElementById('adminRuleEditId').value;
        var selectedChannels = [];
        CHANNELS.forEach(function(ch) {
            var cb = document.getElementById('adminRuleCh_' + ch);
            if (cb && cb.checked) selectedChannels.push(ch);
        });
        var data = {
            category: document.getElementById('adminRuleCategory').value,
            trigger_key: document.getElementById('adminRuleTriggerKey').value,
            trigger_type: 'event',
            condition_operator: document.getElementById('adminRuleOperator').value,
            condition_value: document.getElementById('adminRuleCondValue').value ? parseFloat(document.getElementById('adminRuleCondValue').value) : null,
            channels: selectedChannels,
            frequency: document.getElementById('adminRuleFrequency').value,
            cooldown_minutes: parseInt(document.getElementById('adminRuleCooldown').value) || 60
        };

        var matched = ADMIN_DEFAULTS.find(function(d) { return d.trigger_key === data.trigger_key; });
        if (matched) data.trigger_type = matched.trigger_type;

        var promise = editId
            ? apiPut('/admin/api/alerts/rules/' + editId, data)
            : apiPost('/admin/api/alerts/rules', data);

        promise.then(function() {
            bootstrap.Modal.getOrCreateInstance(document.getElementById('adminRuleModal')).hide();
            loadAdminRules();
        }).catch(function(err) {
            console.error(err.message);
            alert('Failed to save rule: ' + err.message);
        });
    }

    function loadDashboard() {
        var el = document.getElementById('adminDashboard');
        el.innerHTML = '<div class="nc-loading"><i class="fas fa-spinner fa-spin"></i> Loading dashboard...</div>';

        apiGet('/admin/api/alerts/dashboard')
            .then(function(result) {
                if (!result.success) throw new Error('API returned success=false');
                var d = result.data || {};
                var bySev = d.by_severity || {};
                var html = '';

                html += '<div class="row g-3 mb-4">';
                html += '<div class="col-6 col-md-3"><div class="dash-card"><div class="count">' + (d.dispatched_count || 0) + '</div><div class="label"><i class="fas fa-paper-plane me-1"></i>Dispatched</div></div></div>';
                html += '<div class="col-6 col-md-3"><div class="dash-card critical"><div class="count">' + (bySev.critical || 0) + '</div><div class="label"><i class="fas fa-exclamation-circle me-1"></i>Critical</div></div></div>';
                html += '<div class="col-6 col-md-3"><div class="dash-card warning"><div class="count">' + (bySev.warning || 0) + '</div><div class="label"><i class="fas fa-exclamation-triangle me-1"></i>Warning</div></div></div>';
                html += '<div class="col-6 col-md-3"><div class="dash-card info"><div class="count">' + (bySev.info || 0) + '</div><div class="label"><i class="fas fa-info-circle me-1"></i>Info</div></div></div>';
                html += '</div>';

                html += '<div class="row g-3 mb-4">';
                html += '<div class="col-6"><div class="dash-card"><div class="count">' + (d.suppressed_count || 0) + '</div><div class="label"><i class="fas fa-ban me-1"></i>Suppressed</div></div></div>';
                html += '<div class="col-6"><div class="dash-card"><div class="count">' + (d.batched_count || 0) + '</div><div class="label"><i class="fas fa-layer-group me-1"></i>Batched</div></div></div>';
                html += '</div>';

                if (d.period_since) {
                    html += '<p class="text-muted mb-3" style="font-size: 0.8rem;"><i class="fas fa-calendar me-1"></i>Period: since ' + formatDate(d.period_since) + '</p>';
                }

                var mostTriggered = d.most_triggered || [];
                if (mostTriggered.length > 0) {
                    html += '<h6 style="font-weight: 600; font-size: 0.9rem; margin-bottom: 0.75rem;">Most Triggered Rules</h6>';
                    html += '<div class="table-responsive"><table class="nc-table"><thead><tr><th>Trigger</th><th>Category</th><th>Count</th></tr></thead><tbody>';
                    mostTriggered.forEach(function(t) {
                        html += '<tr>';
                        html += '<td>' + escapeHtml(getAdminTriggerTitle(t.trigger_key)) + '</td>';
                        html += '<td>' + categoryChip(t.category) + '</td>';
                        html += '<td><strong>' + (t.count || 0) + '</strong></td>';
                        html += '</tr>';
                    });
                    html += '</tbody></table></div>';
                }

                var recentCritical = d.recent_critical || [];
                if (recentCritical.length > 0) {
                    html += '<h6 style="font-weight: 600; font-size: 0.9rem; margin-top: 1.5rem; margin-bottom: 0.75rem;">Recent Critical Alerts</h6>';
                    recentCritical.forEach(function(a) {
                        html += '<div class="notif-item">';
                        html += '<div class="d-flex align-items-center gap-2 mb-1">';
                        html += severityBadge(a.severity || 'critical');
                        html += '<small class="text-muted">' + formatTimeAgo(a.created_at) + '</small>';
                        html += '</div>';
                        html += '<strong style="font-size: 0.85rem;">' + escapeHtml(a.title || getAdminTriggerTitle(a.trigger_key)) + '</strong>';
                        if (a.body) html += '<p class="mb-0 text-muted" style="font-size: 0.8rem;">' + escapeHtml(a.body) + '</p>';
                        html += '</div>';
                    });
                }

                if (!mostTriggered.length && !recentCritical.length && !d.dispatched_count) {
                    html += '<div class="empty-state"><i class="fas fa-chart-bar"></i><p>No alert data available yet</p></div>';
                }

                el.innerHTML = html;
            })
            .catch(function(err) {
                console.error(err.message);
                el.innerHTML = '<div class="nc-error"><i class="fas fa-exclamation-triangle"></i><p>Failed to load dashboard</p><small>' + escapeHtml(err.message) + '</small></div>';
            });
    }

    function loadAdminHistory(page) {
        page = page || 1;
        state.histPage = page;
        var params = '?page=' + page + '&per_page=20';
        var cat = document.getElementById('adminHistFilterCategory').value;
        var sev = document.getElementById('adminHistFilterSeverity').value;
        var status = document.getElementById('adminHistFilterStatus').value;
        var trigger = document.getElementById('adminHistFilterTrigger').value;
        var tenant = document.getElementById('adminHistFilterTenant').value;
        var dateFrom = document.getElementById('adminHistFilterDateFrom').value;
        if (cat) params += '&category=' + cat;
        if (sev) params += '&severity=' + sev;
        if (status) params += '&status=' + status;
        if (trigger) params += '&trigger_key=' + encodeURIComponent(trigger);
        if (tenant) params += '&tenant_id=' + encodeURIComponent(tenant);
        if (dateFrom) params += '&since=' + dateFrom + ' 00:00:00';

        var tbody = document.getElementById('adminHistoryBody');
        tbody.innerHTML = '<tr><td colspan="7" class="nc-loading"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>';

        apiGet('/admin/api/alerts/history' + params)
            .then(function(result) {
                if (!result.success) throw new Error('API returned success=false');
                var items = result.data || [];
                if (items.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="7" class="empty-state"><i class="fas fa-history"></i> No alert history</td></tr>';
                    document.getElementById('adminHistPagination').innerHTML = '';
                    return;
                }
                var html = '';
                items.forEach(function(h) {
                    html += '<tr>';
                    html += '<td>' + formatDate(h.created_at) + '</td>';
                    html += '<td><strong>' + escapeHtml(h.title || getAdminTriggerTitle(h.trigger_key)) + '</strong></td>';
                    html += '<td>' + severityBadge(h.severity) + '</td>';
                    html += '<td>' + categoryChip(h.category) + '</td>';
                    html += '<td>' + statusChip(h.status) + '</td>';
                    html += '<td>' + channelTags(h.channels_dispatched) + '</td>';
                    html += '<td style="font-size: 0.75rem; font-family: monospace;">' + escapeHtml(h.tenant_id ? h.tenant_id.substring(0, 8) + '...' : 'System') + '</td>';
                    html += '</tr>';
                });
                tbody.innerHTML = html;
                renderPagination('adminHistPagination', result.pagination, loadAdminHistory);
            })
            .catch(function(err) {
                console.error(err.message);
                tbody.innerHTML = '<tr><td colspan="7" class="nc-error"><i class="fas fa-exclamation-triangle"></i> Failed to load history</td></tr>';
            });
    }

    document.addEventListener('DOMContentLoaded', function() {
        console.log('[NotificationCentre] Admin Initialized');

        var catSelect = document.getElementById('adminNotifFilterCategory');
        Object.keys(ADMIN_CATEGORIES).forEach(function(k) {
            var opt = document.createElement('option');
            opt.value = k;
            opt.textContent = ADMIN_CATEGORIES[k];
            catSelect.appendChild(opt);
        });

        var histCatSelect = document.getElementById('adminHistFilterCategory');
        Object.keys(ADMIN_CATEGORIES).forEach(function(k) {
            var opt = document.createElement('option');
            opt.value = k;
            opt.textContent = ADMIN_CATEGORIES[k];
            histCatSelect.appendChild(opt);
        });

        populateAdminRuleModal();
        loadAdminNotifications(1);

        document.getElementById('adminBtnMarkAllRead').addEventListener('click', function() {
            apiPost('/admin/api/notifications/mark-all-read').then(function() { loadAdminNotifications(state.notifPage); })
                .catch(function(err) { console.error(err.message); });
        });

        document.getElementById('adminBtnAddRule').addEventListener('click', function() { openAdminRuleModal(null); });
        document.getElementById('adminBtnSaveRule').addEventListener('click', saveAdminRule);

        ['adminNotifFilterCategory', 'adminNotifFilterSeverity', 'adminNotifFilterRead'].forEach(function(id) {
            document.getElementById(id).addEventListener('change', function() { loadAdminNotifications(1); });
        });
        ['adminHistFilterCategory', 'adminHistFilterSeverity', 'adminHistFilterStatus', 'adminHistFilterDateFrom'].forEach(function(id) {
            document.getElementById(id).addEventListener('change', function() { loadAdminHistory(1); });
        });
        ['adminHistFilterTrigger', 'adminHistFilterTenant'].forEach(function(id) {
            var timer = null;
            document.getElementById(id).addEventListener('input', function() {
                clearTimeout(timer);
                timer = setTimeout(function() { loadAdminHistory(1); }, 500);
            });
        });

        document.querySelectorAll('#adminNcTabs .nav-link').forEach(function(tab) {
            tab.addEventListener('shown.bs.tab', function(e) {
                var target = e.target.getAttribute('href');
                if (target === '#tab-admin-notifications') loadAdminNotifications(state.notifPage);
                else if (target === '#tab-admin-rules') loadAdminRules();
                else if (target === '#tab-admin-dashboard') loadDashboard();
                else if (target === '#tab-admin-history') loadAdminHistory(1);
            });
        });
    });
})();
</script>
@endpush
