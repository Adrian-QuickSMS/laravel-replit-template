@extends('layouts.quicksms')

@section('page_title', 'Audit Logs')

@push('styles')
<link href="{{ asset('css/quicksms-pastel.css') }}" rel="stylesheet">
<style>
.severity-badge-low { background-color: rgba(108, 117, 125, 0.15); color: #6c757d; }
.severity-badge-medium { background-color: rgba(111, 66, 193, 0.15); color: #6f42c1; }
.severity-badge-high { background-color: rgba(220, 53, 69, 0.15); color: #dc3545; }
.severity-badge-critical { background-color: rgba(220, 53, 69, 0.25); color: #dc3545; font-weight: 600; }

.category-badge-user_management { background-color: rgba(111, 66, 193, 0.15); color: #6f42c1; }
.category-badge-access_control { background-color: rgba(48, 101, 208, 0.15); color: #3065D0; }
.category-badge-security { background-color: rgba(220, 53, 69, 0.15); color: #dc3545; }
.category-badge-authentication { background-color: rgba(28, 187, 140, 0.15); color: #1cbb8c; }
.category-badge-enforcement { background-color: rgba(214, 83, 193, 0.15); color: #D653C1; }
.category-badge-data_access { background-color: rgba(255, 191, 0, 0.15); color: #cc9900; }
.category-badge-account { background-color: rgba(111, 66, 193, 0.15); color: #6f42c1; }

.audit-log-row { cursor: pointer; }
.audit-log-row:hover { background-color: rgba(111, 66, 193, 0.03); }

.log-detail-section { padding: 1rem; background-color: #fafafa; border-radius: 0.5rem; margin-bottom: 1rem; }
.log-detail-section h6 { color: #6f42c1; margin-bottom: 0.75rem; font-size: 0.875rem; }
.log-detail-row { display: flex; margin-bottom: 0.5rem; }
.log-detail-label { width: 140px; font-weight: 500; color: #6c757d; font-size: 0.8125rem; }
.log-detail-value { flex: 1; font-size: 0.8125rem; color: #212529; }

.filter-active { background-color: #f3e8ff !important; border-color: #886CC0 !important; }

.stats-card { border: none; background: #fff; border-radius: 0.5rem; }
.stats-card .stat-value { font-size: 1.5rem; font-weight: 600; color: #886CC0; }
.stats-card .stat-label { font-size: 0.75rem; color: #6c757d; text-transform: uppercase; }

.empty-state { padding: 4rem 2rem; text-align: center; }
.empty-state i { font-size: 3rem; color: #dee2e6; margin-bottom: 1rem; }
</style>
@endpush

@section('content')
<div class="row page-titles">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('account') }}">Account</a></li>
        <li class="breadcrumb-item active">Audit Logs</li>
    </ol>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="card-title mb-0">Audit Logs</h5>
            <small class="text-muted">Immutable record of all account activity and security events</small>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-primary btn-sm" id="exportCsvBtn">
                <i class="fas fa-download me-1"></i>Export CSV
            </button>
            <button type="button" class="btn btn-outline-primary btn-sm" id="exportJsonBtn">
                <i class="fas fa-code me-1"></i>Export JSON
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="alert alert-pastel-primary mb-4">
            <div class="d-flex align-items-start">
                <i class="fas fa-info-circle text-primary me-3 mt-1"></i>
                <div>
                    <strong>Audit logs are read-only and immutable.</strong>
                    <p class="mb-0 mt-1 small">All account activity is automatically recorded. Logs cannot be modified or deleted. Use filters to find specific events. Data is retained according to your compliance requirements.</p>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-3 mb-3 mb-md-0">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control" id="searchInput" placeholder="Search logs...">
                </div>
            </div>
            <div class="col-md-2 mb-3 mb-md-0">
                <select class="form-select" id="categoryFilter">
                    <option value="">All Categories</option>
                    <option value="user_management">User Management</option>
                    <option value="access_control">Access Control</option>
                    <option value="security">Security</option>
                    <option value="authentication">Authentication</option>
                    <option value="enforcement">Enforcement</option>
                    <option value="data_access">Data Access</option>
                    <option value="account">Account</option>
                </select>
            </div>
            <div class="col-md-2 mb-3 mb-md-0">
                <select class="form-select" id="severityFilter">
                    <option value="">All Severities</option>
                    <option value="low">Low</option>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                    <option value="critical">Critical</option>
                </select>
            </div>
            <div class="col-md-2 mb-3 mb-md-0">
                <input type="date" class="form-control" id="dateFromFilter" placeholder="From date">
            </div>
            <div class="col-md-2 mb-3 mb-md-0">
                <input type="date" class="form-control" id="dateToFilter" placeholder="To date">
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-outline-secondary w-100" id="clearFilters" title="Clear all filters">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <div class="row mb-4" id="statsRow">
            <div class="col-md-3">
                <div class="stats-card p-3">
                    <div class="stat-value" id="totalLogsCount">0</div>
                    <div class="stat-label">Total Events</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card p-3">
                    <div class="stat-value" id="todayLogsCount">0</div>
                    <div class="stat-label">Today</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card p-3">
                    <div class="stat-value text-danger" id="highSeverityCount">0</div>
                    <div class="stat-label">High/Critical (24h)</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card p-3">
                    <div class="stat-value" id="uniqueActorsCount">0</div>
                    <div class="stat-label">Unique Actors (24h)</div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover mb-0" id="auditLogsTable">
                <thead>
                    <tr>
                        <th style="width: 160px;">Timestamp</th>
                        <th>Action</th>
                        <th>Category</th>
                        <th>Severity</th>
                        <th>Actor</th>
                        <th>Target</th>
                        <th>IP Address</th>
                        <th style="width: 40px;"></th>
                    </tr>
                </thead>
                <tbody id="auditLogsTableBody">
                </tbody>
            </table>
        </div>

        <div class="empty-state" id="emptyState" style="display: none;">
            <i class="fas fa-clipboard-list"></i>
            <h5 class="text-muted mb-2">No audit logs found</h5>
            <p class="text-muted small mb-0">Adjust your filters or check back later for new activity.</p>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-4" id="paginationRow">
            <div class="text-muted small">
                Showing <span id="showingStart">0</span>-<span id="showingEnd">0</span> of <span id="totalFiltered">0</span> events
            </div>
            <nav>
                <ul class="pagination pagination-sm mb-0" id="paginationControls">
                </ul>
            </nav>
        </div>
    </div>
</div>

<div class="modal fade" id="logDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-file-alt me-2 text-primary"></i>Audit Log Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="logDetailContent">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-primary btn-sm" id="copyLogDetail">
                    <i class="fas fa-copy me-1"></i>Copy to Clipboard
                </button>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/quicksms-audit-logger.js') }}"></script>
<script>
$(document).ready(function() {
    var currentPage = 1;
    var itemsPerPage = 25;
    var filteredLogs = [];
    var allLogs = [];

    function generateMockAuditData() {
        var actions = [
            { type: 'USER_CREATED', category: 'user_management', severity: 'high' },
            { type: 'USER_INVITED', category: 'user_management', severity: 'medium' },
            { type: 'USER_SUSPENDED', category: 'user_management', severity: 'high' },
            { type: 'ROLE_CHANGED', category: 'access_control', severity: 'high' },
            { type: 'PERMISSION_GRANTED', category: 'access_control', severity: 'medium' },
            { type: 'PERMISSION_REVOKED', category: 'access_control', severity: 'medium' },
            { type: 'MFA_ENABLED', category: 'security', severity: 'medium' },
            { type: 'MFA_DISABLED', category: 'security', severity: 'high' },
            { type: 'LOGIN_SUCCESS', category: 'authentication', severity: 'low' },
            { type: 'LOGIN_FAILED', category: 'authentication', severity: 'medium' },
            { type: 'LOGIN_BLOCKED', category: 'authentication', severity: 'high' },
            { type: 'PASSWORD_CHANGED', category: 'authentication', severity: 'medium' },
            { type: 'ENFORCEMENT_TRIGGERED', category: 'enforcement', severity: 'medium' },
            { type: 'ENFORCEMENT_OVERRIDE_APPROVED', category: 'enforcement', severity: 'high' },
            { type: 'DATA_EXPORTED', category: 'data_access', severity: 'medium' },
            { type: 'DATA_UNMASKED', category: 'data_access', severity: 'high' },
            { type: 'ACCOUNT_ACTIVATED', category: 'account', severity: 'high' }
        ];

        var actors = [
            { userId: 'usr-001', userName: 'Sarah Johnson', role: 'owner', subAccountId: null },
            { userId: 'usr-002', userName: 'James Wilson', role: 'admin', subAccountId: 'sa-001' },
            { userId: 'usr-003', userName: 'Emily Chen', role: 'messaging_manager', subAccountId: 'sa-002' },
            { userId: 'usr-004', userName: 'Michael Brown', role: 'developer', subAccountId: 'sa-001' },
            { userId: 'usr-005', userName: 'Lisa Anderson', role: 'finance', subAccountId: 'sa-003' }
        ];

        var targets = [
            { userId: 'usr-006', userName: 'New User', role: 'read_only', subAccountId: 'sa-002' },
            { userId: 'usr-007', userName: 'Test User', role: 'messaging_manager', subAccountId: 'sa-001' },
            { resourceType: 'sub_account', resourceId: 'sa-001', name: 'Marketing Department' },
            { resourceType: 'sub_account', resourceId: 'sa-002', name: 'Customer Support' },
            { resourceType: 'campaign', resourceId: 'camp-123', name: 'Spring Sale Campaign' }
        ];

        var ipAddresses = ['192.168.1.100', '10.0.0.45', '172.16.0.22', '192.168.2.50', '10.1.1.1'];

        var logs = [];
        var now = new Date();

        for (var i = 0; i < 150; i++) {
            var action = actions[Math.floor(Math.random() * actions.length)];
            var actor = actors[Math.floor(Math.random() * actors.length)];
            var target = Math.random() > 0.3 ? targets[Math.floor(Math.random() * targets.length)] : null;
            var timestamp = new Date(now.getTime() - Math.random() * 7 * 24 * 60 * 60 * 1000);

            logs.push({
                id: 'audit-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9),
                timestamp: timestamp.toISOString(),
                action: action.type,
                actionLabel: AuditLogger.ACTION_TYPES[action.type]?.label || action.type.replace(/_/g, ' '),
                category: action.category,
                severity: action.severity,
                actor: {
                    userId: actor.userId,
                    userName: actor.userName,
                    role: actor.role,
                    subAccountId: actor.subAccountId
                },
                target: target,
                context: {
                    ipAddress: ipAddresses[Math.floor(Math.random() * ipAddresses.length)],
                    userAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    sessionId: 'sess-' + Math.random().toString(36).substr(2, 12),
                    requestId: 'req-' + Math.random().toString(36).substr(2, 9)
                },
                result: Math.random() > 0.1 ? 'success' : 'failure',
                details: {},
                reason: null
            });
        }

        logs.sort(function(a, b) {
            return new Date(b.timestamp) - new Date(a.timestamp);
        });

        return logs;
    }

    function init() {
        allLogs = generateMockAuditData();
        applyFilters();
        updateStats();
        bindEvents();
    }

    function updateStats() {
        var now = new Date();
        var today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
        var yesterday = new Date(today.getTime() - 24 * 60 * 60 * 1000);

        var todayLogs = allLogs.filter(function(log) {
            return new Date(log.timestamp) >= today;
        });

        var last24h = allLogs.filter(function(log) {
            return new Date(log.timestamp) >= yesterday;
        });

        var highSeverity = last24h.filter(function(log) {
            return log.severity === 'high' || log.severity === 'critical';
        });

        var uniqueActors = [];
        last24h.forEach(function(log) {
            if (uniqueActors.indexOf(log.actor.userId) === -1) {
                uniqueActors.push(log.actor.userId);
            }
        });

        $('#totalLogsCount').text(allLogs.length);
        $('#todayLogsCount').text(todayLogs.length);
        $('#highSeverityCount').text(highSeverity.length);
        $('#uniqueActorsCount').text(uniqueActors.length);
    }

    function applyFilters() {
        var search = $('#searchInput').val().toLowerCase();
        var category = $('#categoryFilter').val();
        var severity = $('#severityFilter').val();
        var dateFrom = $('#dateFromFilter').val();
        var dateTo = $('#dateToFilter').val();

        filteredLogs = allLogs.filter(function(log) {
            if (search && !matchesSearch(log, search)) return false;
            if (category && log.category !== category) return false;
            if (severity && log.severity !== severity) return false;
            if (dateFrom && new Date(log.timestamp) < new Date(dateFrom)) return false;
            if (dateTo) {
                var toDate = new Date(dateTo);
                toDate.setHours(23, 59, 59, 999);
                if (new Date(log.timestamp) > toDate) return false;
            }
            return true;
        });

        currentPage = 1;
        renderTable();
        renderPagination();
    }

    function matchesSearch(log, search) {
        return log.actionLabel.toLowerCase().indexOf(search) !== -1 ||
               log.actor.userName.toLowerCase().indexOf(search) !== -1 ||
               (log.target && log.target.userName && log.target.userName.toLowerCase().indexOf(search) !== -1) ||
               (log.target && log.target.name && log.target.name.toLowerCase().indexOf(search) !== -1) ||
               log.context.ipAddress.indexOf(search) !== -1 ||
               log.id.toLowerCase().indexOf(search) !== -1;
    }

    function renderTable() {
        var tbody = $('#auditLogsTableBody');
        tbody.empty();

        var startIndex = (currentPage - 1) * itemsPerPage;
        var endIndex = Math.min(startIndex + itemsPerPage, filteredLogs.length);
        var pageData = filteredLogs.slice(startIndex, endIndex);

        if (pageData.length === 0) {
            $('#auditLogsTable').hide();
            $('#emptyState').show();
            $('#paginationRow').hide();
            return;
        }

        $('#auditLogsTable').show();
        $('#emptyState').hide();
        $('#paginationRow').show();

        pageData.forEach(function(log) {
            var timestamp = new Date(log.timestamp);
            var formattedDate = timestamp.toLocaleDateString('en-GB') + ' ' + timestamp.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' });

            var targetDisplay = '-';
            if (log.target) {
                if (log.target.userName) {
                    targetDisplay = log.target.userName;
                } else if (log.target.name) {
                    targetDisplay = log.target.name;
                } else if (log.target.resourceId) {
                    targetDisplay = log.target.resourceType + ': ' + log.target.resourceId;
                }
            }

            var row = $('<tr class="audit-log-row" data-log-id="' + log.id + '">' +
                '<td class="small">' + formattedDate + '</td>' +
                '<td><span class="fw-medium">' + log.actionLabel + '</span></td>' +
                '<td><span class="badge category-badge-' + log.category + '">' + formatCategory(log.category) + '</span></td>' +
                '<td><span class="badge severity-badge-' + log.severity + '">' + capitalizeFirst(log.severity) + '</span></td>' +
                '<td>' + log.actor.userName + '</td>' +
                '<td>' + targetDisplay + '</td>' +
                '<td class="small text-muted">' + log.context.ipAddress + '</td>' +
                '<td><i class="fas fa-chevron-right text-muted"></i></td>' +
            '</tr>');

            row.on('click', function() {
                showLogDetail(log);
            });

            tbody.append(row);
        });

        $('#showingStart').text(startIndex + 1);
        $('#showingEnd').text(endIndex);
        $('#totalFiltered').text(filteredLogs.length);
    }

    function renderPagination() {
        var totalPages = Math.ceil(filteredLogs.length / itemsPerPage);
        var pagination = $('#paginationControls');
        pagination.empty();

        if (totalPages <= 1) return;

        pagination.append('<li class="page-item ' + (currentPage === 1 ? 'disabled' : '') + '">' +
            '<a class="page-link" href="#" data-page="' + (currentPage - 1) + '"><i class="fas fa-chevron-left"></i></a></li>');

        var startPage = Math.max(1, currentPage - 2);
        var endPage = Math.min(totalPages, startPage + 4);
        if (endPage - startPage < 4) {
            startPage = Math.max(1, endPage - 4);
        }

        for (var i = startPage; i <= endPage; i++) {
            pagination.append('<li class="page-item ' + (i === currentPage ? 'active' : '') + '">' +
                '<a class="page-link" href="#" data-page="' + i + '">' + i + '</a></li>');
        }

        pagination.append('<li class="page-item ' + (currentPage === totalPages ? 'disabled' : '') + '">' +
            '<a class="page-link" href="#" data-page="' + (currentPage + 1) + '"><i class="fas fa-chevron-right"></i></a></li>');

        pagination.find('a').on('click', function(e) {
            e.preventDefault();
            var page = parseInt($(this).data('page'));
            if (page >= 1 && page <= totalPages) {
                currentPage = page;
                renderTable();
                renderPagination();
            }
        });
    }

    function showLogDetail(log) {
        var content = $('#logDetailContent');
        var timestamp = new Date(log.timestamp);

        var html = '<div class="log-detail-section">' +
            '<h6><i class="fas fa-clock me-2"></i>Event Information</h6>' +
            '<div class="log-detail-row"><span class="log-detail-label">Log ID</span><span class="log-detail-value"><code>' + log.id + '</code></span></div>' +
            '<div class="log-detail-row"><span class="log-detail-label">Timestamp</span><span class="log-detail-value">' + timestamp.toISOString() + '</span></div>' +
            '<div class="log-detail-row"><span class="log-detail-label">Action</span><span class="log-detail-value">' + log.actionLabel + ' <code class="ms-2">(' + log.action + ')</code></span></div>' +
            '<div class="log-detail-row"><span class="log-detail-label">Category</span><span class="log-detail-value"><span class="badge category-badge-' + log.category + '">' + formatCategory(log.category) + '</span></span></div>' +
            '<div class="log-detail-row"><span class="log-detail-label">Severity</span><span class="log-detail-value"><span class="badge severity-badge-' + log.severity + '">' + capitalizeFirst(log.severity) + '</span></span></div>' +
            '<div class="log-detail-row"><span class="log-detail-label">Result</span><span class="log-detail-value"><span class="badge ' + (log.result === 'success' ? 'badge-pastel-success' : 'badge-pastel-danger') + '">' + capitalizeFirst(log.result) + '</span></span></div>' +
        '</div>';

        html += '<div class="log-detail-section">' +
            '<h6><i class="fas fa-user me-2"></i>Actor (Who performed the action)</h6>' +
            '<div class="log-detail-row"><span class="log-detail-label">User ID</span><span class="log-detail-value"><code>' + log.actor.userId + '</code></span></div>' +
            '<div class="log-detail-row"><span class="log-detail-label">Name</span><span class="log-detail-value">' + log.actor.userName + '</span></div>' +
            '<div class="log-detail-row"><span class="log-detail-label">Role</span><span class="log-detail-value"><span class="badge badge-pastel-primary">' + formatRole(log.actor.role) + '</span></span></div>' +
            (log.actor.subAccountId ? '<div class="log-detail-row"><span class="log-detail-label">Sub-Account</span><span class="log-detail-value">' + log.actor.subAccountId + '</span></div>' : '') +
        '</div>';

        if (log.target) {
            html += '<div class="log-detail-section">' +
                '<h6><i class="fas fa-bullseye me-2"></i>Target (What was affected)</h6>';
            if (log.target.userId) {
                html += '<div class="log-detail-row"><span class="log-detail-label">User ID</span><span class="log-detail-value"><code>' + log.target.userId + '</code></span></div>' +
                    '<div class="log-detail-row"><span class="log-detail-label">Name</span><span class="log-detail-value">' + log.target.userName + '</span></div>' +
                    (log.target.role ? '<div class="log-detail-row"><span class="log-detail-label">Role</span><span class="log-detail-value"><span class="badge badge-pastel-primary">' + formatRole(log.target.role) + '</span></span></div>' : '');
            } else if (log.target.resourceType) {
                html += '<div class="log-detail-row"><span class="log-detail-label">Resource Type</span><span class="log-detail-value">' + formatCategory(log.target.resourceType) + '</span></div>' +
                    '<div class="log-detail-row"><span class="log-detail-label">Resource ID</span><span class="log-detail-value"><code>' + log.target.resourceId + '</code></span></div>' +
                    (log.target.name ? '<div class="log-detail-row"><span class="log-detail-label">Name</span><span class="log-detail-value">' + log.target.name + '</span></div>' : '');
            }
            html += '</div>';
        }

        html += '<div class="log-detail-section">' +
            '<h6><i class="fas fa-network-wired me-2"></i>Context</h6>' +
            '<div class="log-detail-row"><span class="log-detail-label">IP Address</span><span class="log-detail-value">' + log.context.ipAddress + '</span></div>' +
            '<div class="log-detail-row"><span class="log-detail-label">Session ID</span><span class="log-detail-value"><code>' + log.context.sessionId + '</code></span></div>' +
            '<div class="log-detail-row"><span class="log-detail-label">Request ID</span><span class="log-detail-value"><code>' + log.context.requestId + '</code></span></div>' +
            '<div class="log-detail-row"><span class="log-detail-label">User Agent</span><span class="log-detail-value small">' + log.context.userAgent + '</span></div>' +
        '</div>';

        if (log.reason) {
            html += '<div class="log-detail-section">' +
                '<h6><i class="fas fa-comment me-2"></i>Reason</h6>' +
                '<p class="mb-0">' + log.reason + '</p>' +
            '</div>';
        }

        content.html(html);

        $('#copyLogDetail').off('click').on('click', function() {
            var logJson = JSON.stringify(log, null, 2);
            navigator.clipboard.writeText(logJson).then(function() {
                showToast('Log details copied to clipboard', 'success');
            });
        });

        $('#logDetailModal').modal('show');
    }

    function bindEvents() {
        var filterDebounce;
        $('#searchInput').on('input', function() {
            clearTimeout(filterDebounce);
            filterDebounce = setTimeout(applyFilters, 300);
        });

        $('#categoryFilter, #severityFilter, #dateFromFilter, #dateToFilter').on('change', applyFilters);

        $('#clearFilters').on('click', function() {
            $('#searchInput').val('');
            $('#categoryFilter').val('');
            $('#severityFilter').val('');
            $('#dateFromFilter').val('');
            $('#dateToFilter').val('');
            applyFilters();
        });

        $('#exportCsvBtn').on('click', function() {
            exportLogs('csv');
        });

        $('#exportJsonBtn').on('click', function() {
            exportLogs('json');
        });
    }

    function exportLogs(format) {
        var data = filteredLogs.length > 0 ? filteredLogs : allLogs;
        var content, filename, mimeType;

        AuditLogger.log('DATA_EXPORTED', {
            data: {
                exportType: 'audit_log',
                format: format,
                recordCount: data.length
            }
        });

        if (format === 'csv') {
            content = convertToCSV(data);
            filename = 'audit-logs-' + new Date().toISOString().split('T')[0] + '.csv';
            mimeType = 'text/csv';
        } else {
            content = JSON.stringify(data, null, 2);
            filename = 'audit-logs-' + new Date().toISOString().split('T')[0] + '.json';
            mimeType = 'application/json';
        }

        var blob = new Blob([content], { type: mimeType });
        var url = URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url;
        a.download = filename;
        a.click();
        URL.revokeObjectURL(url);

        showToast('Exported ' + data.length + ' records as ' + format.toUpperCase(), 'success');
    }

    function convertToCSV(data) {
        var headers = ['id', 'timestamp', 'action', 'action_label', 'category', 'severity', 'actor_id', 'actor_name', 'actor_role', 'target_id', 'target_name', 'ip_address', 'session_id', 'result'];
        var rows = data.map(function(log) {
            return [
                log.id,
                log.timestamp,
                log.action,
                log.actionLabel,
                log.category,
                log.severity,
                log.actor.userId,
                log.actor.userName,
                log.actor.role,
                log.target ? (log.target.userId || log.target.resourceId) : '',
                log.target ? (log.target.userName || log.target.name || '') : '',
                log.context.ipAddress,
                log.context.sessionId,
                log.result
            ].map(function(v) { return '"' + String(v || '').replace(/"/g, '""') + '"'; }).join(',');
        });
        return headers.join(',') + '\n' + rows.join('\n');
    }

    function formatCategory(category) {
        return category.split('_').map(capitalizeFirst).join(' ');
    }

    function formatRole(role) {
        return role.split('_').map(capitalizeFirst).join(' ');
    }

    function capitalizeFirst(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    function showToast(message, type) {
        var bgClass = type === 'success' ? 'bg-success' : (type === 'error' ? 'bg-danger' : 'bg-primary');
        var toast = $('<div class="toast align-items-center text-white ' + bgClass + ' border-0 position-fixed" style="top: 20px; right: 20px; z-index: 9999;" role="alert">' +
            '<div class="d-flex"><div class="toast-body">' + message + '</div>' +
            '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div></div>');
        $('body').append(toast);
        var bsToast = new bootstrap.Toast(toast[0], { delay: 3000 });
        bsToast.show();
        toast.on('hidden.bs.toast', function() { toast.remove(); });
    }

    init();
});
</script>
@endpush
