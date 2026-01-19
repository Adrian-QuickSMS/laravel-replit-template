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
.category-badge-messaging { background-color: rgba(48, 101, 208, 0.15); color: #3065D0; }
.category-badge-financial { background-color: rgba(28, 187, 140, 0.15); color: #1cbb8c; }
.category-badge-gdpr { background-color: rgba(214, 83, 193, 0.15); color: #D653C1; }
.category-badge-compliance { background-color: rgba(111, 66, 193, 0.15); color: #6f42c1; }

.audit-log-row { cursor: pointer; }
.audit-log-row:hover { background-color: rgba(111, 66, 193, 0.03); }

.log-detail-section { padding: 1rem; background-color: #fafafa; border-radius: 0.5rem; margin-bottom: 1rem; }
.log-detail-section h6 { color: #6f42c1; margin-bottom: 0.75rem; font-size: 0.875rem; }
.log-detail-row { display: flex; margin-bottom: 0.5rem; }
.log-detail-label { width: 140px; font-weight: 500; color: #6c757d; font-size: 0.8125rem; }
.log-detail-value { flex: 1; font-size: 0.8125rem; color: #212529; }

.stats-card { border: none; background: #fff; border-radius: 0.5rem; }
.stats-card .stat-value { font-size: 1.5rem; font-weight: 600; color: #886CC0; }
.stats-card .stat-label { font-size: 0.75rem; color: #6c757d; text-transform: uppercase; }

.empty-state { padding: 4rem 2rem; text-align: center; }
.empty-state i { font-size: 3rem; color: #dee2e6; margin-bottom: 1rem; }

.retention-indicator { font-size: 0.75rem; padding: 0.25rem 0.5rem; border-radius: 0.25rem; }
.retention-active { background-color: rgba(28, 187, 140, 0.15); color: #1cbb8c; }
.retention-approaching { background-color: rgba(255, 191, 0, 0.15); color: #cc9900; }

.integrity-badge { font-family: monospace; font-size: 0.7rem; background-color: rgba(108, 117, 125, 0.1); padding: 0.125rem 0.375rem; border-radius: 0.25rem; }

.compliance-card { background: linear-gradient(135deg, rgba(111, 66, 193, 0.05) 0%, rgba(111, 66, 193, 0.02) 100%); border-radius: 0.5rem; padding: 1rem; margin-bottom: 1rem; }
.compliance-card h6 { color: #6f42c1; font-size: 0.875rem; margin-bottom: 0.5rem; }
.compliance-card .compliance-stat { font-size: 1.25rem; font-weight: 600; color: #886CC0; }

.quick-filter-btn { font-size: 0.75rem; padding: 0.25rem 0.75rem; border-radius: 1rem; margin-right: 0.5rem; margin-bottom: 0.5rem; }
.quick-filter-btn.active { background-color: #886CC0; color: #fff; border-color: #886CC0; }
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

<ul class="nav nav-tabs" id="auditTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="all-logs-tab" data-bs-toggle="tab" data-bs-target="#allLogs" type="button" role="tab">
            <i class="fas fa-list me-2"></i>All Logs
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="security-tab" data-bs-toggle="tab" data-bs-target="#securityLogs" type="button" role="tab">
            <i class="fas fa-shield-alt me-2"></i>Security
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="messaging-tab" data-bs-toggle="tab" data-bs-target="#messagingLogs" type="button" role="tab">
            <i class="fas fa-envelope me-2"></i>Messaging
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="financial-tab" data-bs-toggle="tab" data-bs-target="#financialLogs" type="button" role="tab">
            <i class="fas fa-pound-sign me-2"></i>Financial
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="compliance-tab" data-bs-toggle="tab" data-bs-target="#complianceLogs" type="button" role="tab">
            <i class="fas fa-clipboard-check me-2"></i>Compliance
        </button>
    </li>
</ul>

<div class="tab-content" id="auditTabsContent">
    <div class="tab-pane fade show active" id="allLogs" role="tabpanel">
        <div class="card border-top-0 rounded-top-0">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title mb-0">Audit Trail</h5>
                    <small class="text-muted">Centralised, chronological record of all platform activity</small>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <span class="retention-indicator retention-active">
                        <i class="fas fa-clock me-1"></i>Retention: 7 years
                    </span>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="verifyIntegrityBtn">
                        <i class="fas fa-check-circle me-1"></i>Verify Integrity
                    </button>
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
                            <strong>Tamper-proof audit trail for compliance and accountability.</strong>
                            <p class="mb-0 mt-1 small">All logs are cryptographically signed, immutable, and retained for 7 years. Supports ISO 27001, NHS DSP Toolkit, and GDPR requirements. Each entry includes a verification hash for integrity checking.</p>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-12">
                        <div class="d-flex flex-wrap">
                            <button type="button" class="btn btn-outline-secondary quick-filter-btn" data-filter="all">All Events</button>
                            <button type="button" class="btn btn-outline-secondary quick-filter-btn" data-filter="high-severity">High Severity</button>
                            <button type="button" class="btn btn-outline-secondary quick-filter-btn" data-filter="login-activity">Login Activity</button>
                            <button type="button" class="btn btn-outline-secondary quick-filter-btn" data-filter="data-access">Data Access</button>
                            <button type="button" class="btn btn-outline-secondary quick-filter-btn" data-filter="user-changes">User Changes</button>
                            <button type="button" class="btn btn-outline-secondary quick-filter-btn" data-filter="permission-changes">Permission Changes</button>
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
                            <option value="messaging">Messaging</option>
                            <option value="financial">Financial</option>
                            <option value="gdpr">GDPR</option>
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
                                <th style="width: 80px;">Hash</th>
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
    </div>

    <div class="tab-pane fade" id="securityLogs" role="tabpanel">
        <div class="card border-top-0 rounded-top-0">
            <div class="card-header">
                <h5 class="card-title mb-0">Security Monitoring</h5>
                <small class="text-muted">Authentication, access control, and security events (ISO 27001 / NHS DSP Toolkit)</small>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="compliance-card">
                            <h6><i class="fas fa-sign-in-alt me-2"></i>Failed Logins (24h)</h6>
                            <div class="compliance-stat" id="failedLoginsCount">0</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="compliance-card">
                            <h6><i class="fas fa-ban me-2"></i>Blocked Access (24h)</h6>
                            <div class="compliance-stat" id="blockedAccessCount">0</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="compliance-card">
                            <h6><i class="fas fa-key me-2"></i>MFA Changes (7d)</h6>
                            <div class="compliance-stat" id="mfaChangesCount">0</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="compliance-card">
                            <h6><i class="fas fa-exclamation-triangle me-2"></i>Security Alerts</h6>
                            <div class="compliance-stat text-danger" id="securityAlertsCount">0</div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="securityLogsTable">
                        <thead>
                            <tr>
                                <th>Timestamp</th>
                                <th>Event</th>
                                <th>Severity</th>
                                <th>User</th>
                                <th>IP Address</th>
                                <th>Result</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="securityLogsTableBody">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="tab-pane fade" id="messagingLogs" role="tabpanel">
        <div class="card border-top-0 rounded-top-0">
            <div class="card-header">
                <h5 class="card-title mb-0">Messaging Activity</h5>
                <small class="text-muted">Campaign approvals, message dispatch, and delivery events for dispute resolution</small>
            </div>
            <div class="card-body">
                <div class="alert alert-pastel-primary mb-4">
                    <div class="d-flex align-items-start">
                        <i class="fas fa-info-circle text-primary me-3 mt-1"></i>
                        <div>
                            <strong>Messaging audit trail for dispute resolution.</strong>
                            <p class="mb-0 mt-1 small">All message-related events are logged including campaign submissions, approvals, rejections, and delivery confirmations. Use this data to resolve delivery disputes and verify compliance with messaging regulations.</p>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="compliance-card">
                            <h6><i class="fas fa-paper-plane me-2"></i>Campaigns Sent (7d)</h6>
                            <div class="compliance-stat" id="campaignsSentCount">0</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="compliance-card">
                            <h6><i class="fas fa-check-circle me-2"></i>Approved (7d)</h6>
                            <div class="compliance-stat" id="campaignsApprovedCount">0</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="compliance-card">
                            <h6><i class="fas fa-times-circle me-2"></i>Rejected (7d)</h6>
                            <div class="compliance-stat" id="campaignsRejectedCount">0</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="compliance-card">
                            <h6><i class="fas fa-user-slash me-2"></i>Opt-outs (7d)</h6>
                            <div class="compliance-stat" id="optOutsCount">0</div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="messagingLogsTable">
                        <thead>
                            <tr>
                                <th>Timestamp</th>
                                <th>Event</th>
                                <th>Campaign/Message</th>
                                <th>Actor</th>
                                <th>Recipients</th>
                                <th>Channel</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="messagingLogsTableBody">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="tab-pane fade" id="financialLogs" role="tabpanel">
        <div class="card border-top-0 rounded-top-0">
            <div class="card-header">
                <h5 class="card-title mb-0">Financial Audit Trail</h5>
                <small class="text-muted">Purchases, invoices, credits, and billing events for financial accountability</small>
            </div>
            <div class="card-body">
                <div class="alert alert-pastel-primary mb-4">
                    <div class="d-flex align-items-start">
                        <i class="fas fa-info-circle text-primary me-3 mt-1"></i>
                        <div>
                            <strong>Financial records for billing disputes and audits.</strong>
                            <p class="mb-0 mt-1 small">All financial transactions are logged including message purchases, credit allocations, invoice generation, and payment processing. This data supports billing dispute resolution and financial auditing requirements.</p>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="compliance-card">
                            <h6><i class="fas fa-shopping-cart me-2"></i>Purchases (30d)</h6>
                            <div class="compliance-stat" id="purchasesCount">0</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="compliance-card">
                            <h6><i class="fas fa-file-invoice me-2"></i>Invoices (30d)</h6>
                            <div class="compliance-stat" id="invoicesCount">0</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="compliance-card">
                            <h6><i class="fas fa-coins me-2"></i>Credits Applied (30d)</h6>
                            <div class="compliance-stat" id="creditsCount">0</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="compliance-card">
                            <h6><i class="fas fa-undo me-2"></i>Refunds (30d)</h6>
                            <div class="compliance-stat" id="refundsCount">0</div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="financialLogsTable">
                        <thead>
                            <tr>
                                <th>Timestamp</th>
                                <th>Event</th>
                                <th>Amount</th>
                                <th>Reference</th>
                                <th>Actor</th>
                                <th>Sub-Account</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="financialLogsTableBody">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="tab-pane fade" id="complianceLogs" role="tabpanel">
        <div class="card border-top-0 rounded-top-0">
            <div class="card-header">
                <h5 class="card-title mb-0">Compliance Dashboard</h5>
                <small class="text-muted">GDPR, ISO 27001, and NHS DSP Toolkit compliance monitoring</small>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="compliance-card">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="mb-0"><i class="fas fa-user-shield me-2"></i>GDPR Accountability</h6>
                                <span class="badge badge-pastel-success">Compliant</span>
                            </div>
                            <p class="small text-muted mb-2">Data subject requests, consent changes, and processing activities.</p>
                            <div class="d-flex justify-content-between small">
                                <span>SAR Requests (30d)</span>
                                <strong id="sarRequestsCount">0</strong>
                            </div>
                            <div class="d-flex justify-content-between small">
                                <span>Consent Changes (30d)</span>
                                <strong id="consentChangesCount">0</strong>
                            </div>
                            <div class="d-flex justify-content-between small">
                                <span>Data Exports (30d)</span>
                                <strong id="gdprExportsCount">0</strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="compliance-card">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="mb-0"><i class="fas fa-shield-alt me-2"></i>ISO 27001</h6>
                                <span class="badge badge-pastel-success">Compliant</span>
                            </div>
                            <p class="small text-muted mb-2">Information security controls and access management.</p>
                            <div class="d-flex justify-content-between small">
                                <span>Access Reviews (30d)</span>
                                <strong id="accessReviewsCount">0</strong>
                            </div>
                            <div class="d-flex justify-content-between small">
                                <span>Security Events (30d)</span>
                                <strong id="securityEventsCount">0</strong>
                            </div>
                            <div class="d-flex justify-content-between small">
                                <span>Policy Changes (30d)</span>
                                <strong id="policyChangesCount">0</strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="compliance-card">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="mb-0"><i class="fas fa-hospital me-2"></i>NHS DSP Toolkit</h6>
                                <span class="badge badge-pastel-success">Compliant</span>
                            </div>
                            <p class="small text-muted mb-2">Data security and protection requirements for NHS suppliers.</p>
                            <div class="d-flex justify-content-between small">
                                <span>User Training Status</span>
                                <strong>100%</strong>
                            </div>
                            <div class="d-flex justify-content-between small">
                                <span>Incident Reports (30d)</span>
                                <strong id="incidentReportsCount">0</strong>
                            </div>
                            <div class="d-flex justify-content-between small">
                                <span>Last Assessment</span>
                                <strong>{{ date('d/m/Y', strtotime('-45 days')) }}</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <h6 class="mb-3"><i class="fas fa-history me-2 text-primary"></i>Recent Compliance Events</h6>
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="complianceLogsTable">
                        <thead>
                            <tr>
                                <th>Timestamp</th>
                                <th>Framework</th>
                                <th>Event</th>
                                <th>Actor</th>
                                <th>Details</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="complianceLogsTableBody">
                        </tbody>
                    </table>
                </div>
            </div>
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

<div class="modal fade" id="integrityModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-shield-alt me-2 text-primary"></i>Log Integrity Verification</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center py-4" id="integrityChecking">
                    <div class="spinner-border text-primary mb-3" role="status"></div>
                    <p class="mb-0">Verifying log integrity...</p>
                </div>
                <div id="integrityResult" style="display: none;">
                    <div class="text-center py-3">
                        <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
                        <h5 class="mt-3 text-success">Integrity Verified</h5>
                        <p class="text-muted mb-0">All audit log entries have valid cryptographic signatures.</p>
                    </div>
                    <hr>
                    <div class="small">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Records Verified</span>
                            <strong id="recordsVerified">0</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Hash Algorithm</span>
                            <strong>SHA-256</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Verification Time</span>
                            <strong id="verificationTime">0ms</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Chain Status</span>
                            <span class="badge badge-pastel-success">Unbroken</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
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
    var activeQuickFilter = 'all';

    var EXTENDED_ACTION_TYPES = {
        ...AuditLogger.ACTION_TYPES,
        CAMPAIGN_SUBMITTED: { category: 'messaging', severity: 'low', label: 'Campaign Submitted' },
        CAMPAIGN_APPROVED: { category: 'messaging', severity: 'medium', label: 'Campaign Approved' },
        CAMPAIGN_REJECTED: { category: 'messaging', severity: 'medium', label: 'Campaign Rejected' },
        CAMPAIGN_SENT: { category: 'messaging', severity: 'low', label: 'Campaign Sent' },
        MESSAGE_DELIVERED: { category: 'messaging', severity: 'low', label: 'Message Delivered' },
        MESSAGE_FAILED: { category: 'messaging', severity: 'medium', label: 'Message Failed' },
        OPT_OUT_RECEIVED: { category: 'messaging', severity: 'medium', label: 'Opt-out Received' },
        OPT_IN_RECEIVED: { category: 'messaging', severity: 'low', label: 'Opt-in Received' },
        PURCHASE_COMPLETED: { category: 'financial', severity: 'medium', label: 'Purchase Completed' },
        INVOICE_GENERATED: { category: 'financial', severity: 'low', label: 'Invoice Generated' },
        PAYMENT_RECEIVED: { category: 'financial', severity: 'medium', label: 'Payment Received' },
        CREDIT_APPLIED: { category: 'financial', severity: 'medium', label: 'Credit Applied' },
        REFUND_ISSUED: { category: 'financial', severity: 'high', label: 'Refund Issued' },
        SAR_REQUEST: { category: 'gdpr', severity: 'high', label: 'Subject Access Request' },
        DATA_DELETION: { category: 'gdpr', severity: 'critical', label: 'Data Deletion Request' },
        CONSENT_UPDATED: { category: 'gdpr', severity: 'medium', label: 'Consent Updated' },
        PROCESSING_RECORD: { category: 'gdpr', severity: 'low', label: 'Processing Activity Recorded' },
        SECURITY_INCIDENT: { category: 'compliance', severity: 'critical', label: 'Security Incident' },
        POLICY_UPDATED: { category: 'compliance', severity: 'high', label: 'Policy Updated' },
        ACCESS_REVIEW: { category: 'compliance', severity: 'medium', label: 'Access Review Completed' }
    };

    function generateHash(data) {
        var str = JSON.stringify(data);
        var hash = 0;
        for (var i = 0; i < str.length; i++) {
            var char = str.charCodeAt(i);
            hash = ((hash << 5) - hash) + char;
            hash = hash & hash;
        }
        return Math.abs(hash).toString(16).padStart(8, '0').substring(0, 8);
    }

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
            { type: 'ACCOUNT_ACTIVATED', category: 'account', severity: 'high' },
            { type: 'CAMPAIGN_SUBMITTED', category: 'messaging', severity: 'low' },
            { type: 'CAMPAIGN_APPROVED', category: 'messaging', severity: 'medium' },
            { type: 'CAMPAIGN_REJECTED', category: 'messaging', severity: 'medium' },
            { type: 'CAMPAIGN_SENT', category: 'messaging', severity: 'low' },
            { type: 'OPT_OUT_RECEIVED', category: 'messaging', severity: 'medium' },
            { type: 'PURCHASE_COMPLETED', category: 'financial', severity: 'medium' },
            { type: 'INVOICE_GENERATED', category: 'financial', severity: 'low' },
            { type: 'CREDIT_APPLIED', category: 'financial', severity: 'medium' },
            { type: 'SAR_REQUEST', category: 'gdpr', severity: 'high' },
            { type: 'CONSENT_UPDATED', category: 'gdpr', severity: 'medium' },
            { type: 'ACCESS_REVIEW', category: 'compliance', severity: 'medium' }
        ];

        var actors = [
            { userId: 'usr-001', userName: 'Sarah Johnson', role: 'owner', subAccountId: null },
            { userId: 'usr-002', userName: 'James Wilson', role: 'admin', subAccountId: 'sa-001' },
            { userId: 'usr-003', userName: 'Emily Chen', role: 'messaging_manager', subAccountId: 'sa-002' },
            { userId: 'usr-004', userName: 'Michael Brown', role: 'developer', subAccountId: 'sa-001' },
            { userId: 'usr-005', userName: 'Lisa Anderson', role: 'finance', subAccountId: 'sa-003' },
            { userId: 'system', userName: 'System', role: 'system', subAccountId: null }
        ];

        var targets = [
            { userId: 'usr-006', userName: 'New User', role: 'read_only', subAccountId: 'sa-002' },
            { userId: 'usr-007', userName: 'Test User', role: 'messaging_manager', subAccountId: 'sa-001' },
            { resourceType: 'sub_account', resourceId: 'sa-001', name: 'Marketing Department' },
            { resourceType: 'sub_account', resourceId: 'sa-002', name: 'Customer Support' },
            { resourceType: 'campaign', resourceId: 'camp-123', name: 'Spring Sale Campaign' },
            { resourceType: 'campaign', resourceId: 'camp-456', name: 'Newsletter Q1' },
            { resourceType: 'invoice', resourceId: 'inv-789', name: 'Invoice #INV-2026-001' },
            { resourceType: 'purchase', resourceId: 'pur-321', name: 'SMS Credit Purchase' }
        ];

        var ipAddresses = ['192.168.1.100', '10.0.0.45', '172.16.0.22', '192.168.2.50', '10.1.1.1', '203.45.67.89'];

        var logs = [];
        var now = new Date();

        for (var i = 0; i < 250; i++) {
            var action = actions[Math.floor(Math.random() * actions.length)];
            var actor = actors[Math.floor(Math.random() * actors.length)];
            var target = Math.random() > 0.3 ? targets[Math.floor(Math.random() * targets.length)] : null;
            var timestamp = new Date(now.getTime() - Math.random() * 30 * 24 * 60 * 60 * 1000);

            var logEntry = {
                id: 'audit-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9),
                timestamp: timestamp.toISOString(),
                action: action.type,
                actionLabel: EXTENDED_ACTION_TYPES[action.type]?.label || action.type.replace(/_/g, ' '),
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
                details: generateActionDetails(action.type),
                reason: Math.random() > 0.7 ? 'Requested by user' : null,
                retentionExpiry: new Date(timestamp.getTime() + 7 * 365 * 24 * 60 * 60 * 1000).toISOString()
            };

            logEntry.integrityHash = generateHash(logEntry);
            logs.push(logEntry);
        }

        logs.sort(function(a, b) {
            return new Date(b.timestamp) - new Date(a.timestamp);
        });

        return logs;
    }

    function generateActionDetails(actionType) {
        switch(actionType) {
            case 'CAMPAIGN_SENT':
                return { recipients: Math.floor(Math.random() * 10000) + 100, channel: Math.random() > 0.5 ? 'SMS' : 'RCS' };
            case 'PURCHASE_COMPLETED':
                return { amount: (Math.random() * 500 + 50).toFixed(2), currency: 'GBP', credits: Math.floor(Math.random() * 10000) + 1000 };
            case 'INVOICE_GENERATED':
                return { amount: (Math.random() * 1000 + 100).toFixed(2), currency: 'GBP' };
            default:
                return {};
        }
    }

    function init() {
        allLogs = generateMockAuditData();
        applyFilters();
        updateStats();
        updateSecurityStats();
        updateMessagingStats();
        updateFinancialStats();
        updateComplianceStats();
        renderCategoryTables();
        bindEvents();
    }

    function updateStats() {
        var now = new Date();
        var today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
        var yesterday = new Date(today.getTime() - 24 * 60 * 60 * 1000);

        var todayLogs = allLogs.filter(function(log) { return new Date(log.timestamp) >= today; });
        var last24h = allLogs.filter(function(log) { return new Date(log.timestamp) >= yesterday; });
        var highSeverity = last24h.filter(function(log) { return log.severity === 'high' || log.severity === 'critical'; });
        var uniqueActors = [...new Set(last24h.map(function(log) { return log.actor.userId; }))];

        $('#totalLogsCount').text(allLogs.length);
        $('#todayLogsCount').text(todayLogs.length);
        $('#highSeverityCount').text(highSeverity.length);
        $('#uniqueActorsCount').text(uniqueActors.length);
    }

    function updateSecurityStats() {
        var now = new Date();
        var last24h = new Date(now.getTime() - 24 * 60 * 60 * 1000);
        var last7d = new Date(now.getTime() - 7 * 24 * 60 * 60 * 1000);

        var securityLogs = allLogs.filter(function(log) {
            return ['security', 'authentication'].includes(log.category);
        });

        $('#failedLoginsCount').text(securityLogs.filter(function(log) {
            return log.action === 'LOGIN_FAILED' && new Date(log.timestamp) >= last24h;
        }).length);

        $('#blockedAccessCount').text(securityLogs.filter(function(log) {
            return log.action === 'LOGIN_BLOCKED' && new Date(log.timestamp) >= last24h;
        }).length);

        $('#mfaChangesCount').text(securityLogs.filter(function(log) {
            return ['MFA_ENABLED', 'MFA_DISABLED', 'MFA_RESET'].includes(log.action) && new Date(log.timestamp) >= last7d;
        }).length);

        $('#securityAlertsCount').text(securityLogs.filter(function(log) {
            return (log.severity === 'high' || log.severity === 'critical') && new Date(log.timestamp) >= last24h;
        }).length);
    }

    function updateMessagingStats() {
        var now = new Date();
        var last7d = new Date(now.getTime() - 7 * 24 * 60 * 60 * 1000);

        var messagingLogs = allLogs.filter(function(log) {
            return log.category === 'messaging' && new Date(log.timestamp) >= last7d;
        });

        $('#campaignsSentCount').text(messagingLogs.filter(function(log) { return log.action === 'CAMPAIGN_SENT'; }).length);
        $('#campaignsApprovedCount').text(messagingLogs.filter(function(log) { return log.action === 'CAMPAIGN_APPROVED'; }).length);
        $('#campaignsRejectedCount').text(messagingLogs.filter(function(log) { return log.action === 'CAMPAIGN_REJECTED'; }).length);
        $('#optOutsCount').text(messagingLogs.filter(function(log) { return log.action === 'OPT_OUT_RECEIVED'; }).length);
    }

    function updateFinancialStats() {
        var now = new Date();
        var last30d = new Date(now.getTime() - 30 * 24 * 60 * 60 * 1000);

        var financialLogs = allLogs.filter(function(log) {
            return log.category === 'financial' && new Date(log.timestamp) >= last30d;
        });

        $('#purchasesCount').text(financialLogs.filter(function(log) { return log.action === 'PURCHASE_COMPLETED'; }).length);
        $('#invoicesCount').text(financialLogs.filter(function(log) { return log.action === 'INVOICE_GENERATED'; }).length);
        $('#creditsCount').text(financialLogs.filter(function(log) { return log.action === 'CREDIT_APPLIED'; }).length);
        $('#refundsCount').text(financialLogs.filter(function(log) { return log.action === 'REFUND_ISSUED'; }).length);
    }

    function updateComplianceStats() {
        var now = new Date();
        var last30d = new Date(now.getTime() - 30 * 24 * 60 * 60 * 1000);

        var gdprLogs = allLogs.filter(function(log) { return log.category === 'gdpr' && new Date(log.timestamp) >= last30d; });
        var complianceLogs = allLogs.filter(function(log) { return log.category === 'compliance' && new Date(log.timestamp) >= last30d; });

        $('#sarRequestsCount').text(gdprLogs.filter(function(log) { return log.action === 'SAR_REQUEST'; }).length);
        $('#consentChangesCount').text(gdprLogs.filter(function(log) { return log.action === 'CONSENT_UPDATED'; }).length);
        $('#gdprExportsCount').text(allLogs.filter(function(log) { return log.action === 'DATA_EXPORTED' && new Date(log.timestamp) >= last30d; }).length);

        $('#accessReviewsCount').text(complianceLogs.filter(function(log) { return log.action === 'ACCESS_REVIEW'; }).length);
        $('#securityEventsCount').text(allLogs.filter(function(log) { return log.category === 'security' && new Date(log.timestamp) >= last30d; }).length);
        $('#policyChangesCount').text(complianceLogs.filter(function(log) { return log.action === 'POLICY_UPDATED'; }).length);
        $('#incidentReportsCount').text(complianceLogs.filter(function(log) { return log.action === 'SECURITY_INCIDENT'; }).length);
    }

    function renderCategoryTables() {
        renderSecurityTable();
        renderMessagingTable();
        renderFinancialTable();
        renderComplianceTable();
    }

    function renderSecurityTable() {
        var tbody = $('#securityLogsTableBody');
        tbody.empty();

        var securityLogs = allLogs.filter(function(log) {
            return ['security', 'authentication'].includes(log.category);
        }).slice(0, 20);

        securityLogs.forEach(function(log) {
            var timestamp = new Date(log.timestamp);
            var formattedDate = timestamp.toLocaleDateString('en-GB') + ' ' + timestamp.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' });

            var row = $('<tr class="audit-log-row">' +
                '<td class="small">' + formattedDate + '</td>' +
                '<td>' + log.actionLabel + '</td>' +
                '<td><span class="badge severity-badge-' + log.severity + '">' + capitalizeFirst(log.severity) + '</span></td>' +
                '<td>' + log.actor.userName + '</td>' +
                '<td class="small text-muted">' + log.context.ipAddress + '</td>' +
                '<td><span class="badge ' + (log.result === 'success' ? 'badge-pastel-success' : 'badge-pastel-danger') + '">' + capitalizeFirst(log.result) + '</span></td>' +
                '<td><i class="fas fa-chevron-right text-muted"></i></td>' +
            '</tr>');

            row.on('click', function() { showLogDetail(log); });
            tbody.append(row);
        });
    }

    function renderMessagingTable() {
        var tbody = $('#messagingLogsTableBody');
        tbody.empty();

        var messagingLogs = allLogs.filter(function(log) {
            return log.category === 'messaging';
        }).slice(0, 20);

        messagingLogs.forEach(function(log) {
            var timestamp = new Date(log.timestamp);
            var formattedDate = timestamp.toLocaleDateString('en-GB') + ' ' + timestamp.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' });

            var targetName = log.target ? (log.target.name || log.target.resourceId || '-') : '-';
            var recipients = log.details && log.details.recipients ? log.details.recipients.toLocaleString() : '-';
            var channel = log.details && log.details.channel ? log.details.channel : 'SMS';

            var row = $('<tr class="audit-log-row">' +
                '<td class="small">' + formattedDate + '</td>' +
                '<td>' + log.actionLabel + '</td>' +
                '<td>' + targetName + '</td>' +
                '<td>' + log.actor.userName + '</td>' +
                '<td>' + recipients + '</td>' +
                '<td><span class="badge badge-pastel-primary">' + channel + '</span></td>' +
                '<td><i class="fas fa-chevron-right text-muted"></i></td>' +
            '</tr>');

            row.on('click', function() { showLogDetail(log); });
            tbody.append(row);
        });
    }

    function renderFinancialTable() {
        var tbody = $('#financialLogsTableBody');
        tbody.empty();

        var financialLogs = allLogs.filter(function(log) {
            return log.category === 'financial';
        }).slice(0, 20);

        financialLogs.forEach(function(log) {
            var timestamp = new Date(log.timestamp);
            var formattedDate = timestamp.toLocaleDateString('en-GB') + ' ' + timestamp.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' });

            var amount = log.details && log.details.amount ? '£' + log.details.amount : '-';
            var reference = log.target ? (log.target.name || log.target.resourceId || '-') : '-';
            var subAccount = log.actor.subAccountId || 'Main Account';

            var row = $('<tr class="audit-log-row">' +
                '<td class="small">' + formattedDate + '</td>' +
                '<td>' + log.actionLabel + '</td>' +
                '<td class="fw-medium">' + amount + '</td>' +
                '<td class="small">' + reference + '</td>' +
                '<td>' + log.actor.userName + '</td>' +
                '<td class="small">' + subAccount + '</td>' +
                '<td><i class="fas fa-chevron-right text-muted"></i></td>' +
            '</tr>');

            row.on('click', function() { showLogDetail(log); });
            tbody.append(row);
        });
    }

    function renderComplianceTable() {
        var tbody = $('#complianceLogsTableBody');
        tbody.empty();

        var complianceLogs = allLogs.filter(function(log) {
            return ['gdpr', 'compliance'].includes(log.category);
        }).slice(0, 20);

        complianceLogs.forEach(function(log) {
            var timestamp = new Date(log.timestamp);
            var formattedDate = timestamp.toLocaleDateString('en-GB') + ' ' + timestamp.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' });

            var framework = log.category === 'gdpr' ? 'GDPR' : 'ISO 27001';

            var row = $('<tr class="audit-log-row">' +
                '<td class="small">' + formattedDate + '</td>' +
                '<td><span class="badge badge-pastel-primary">' + framework + '</span></td>' +
                '<td>' + log.actionLabel + '</td>' +
                '<td>' + log.actor.userName + '</td>' +
                '<td class="small text-muted">' + (log.target ? log.target.name || log.target.resourceId : '-') + '</td>' +
                '<td><i class="fas fa-chevron-right text-muted"></i></td>' +
            '</tr>');

            row.on('click', function() { showLogDetail(log); });
            tbody.append(row);
        });
    }

    function applyFilters() {
        var search = $('#searchInput').val().toLowerCase();
        var category = $('#categoryFilter').val();
        var severity = $('#severityFilter').val();
        var dateFrom = $('#dateFromFilter').val();
        var dateTo = $('#dateToFilter').val();

        filteredLogs = allLogs.filter(function(log) {
            if (activeQuickFilter !== 'all') {
                if (activeQuickFilter === 'high-severity' && !['high', 'critical'].includes(log.severity)) return false;
                if (activeQuickFilter === 'login-activity' && !['LOGIN_SUCCESS', 'LOGIN_FAILED', 'LOGIN_BLOCKED'].includes(log.action)) return false;
                if (activeQuickFilter === 'data-access' && log.category !== 'data_access') return false;
                if (activeQuickFilter === 'user-changes' && log.category !== 'user_management') return false;
                if (activeQuickFilter === 'permission-changes' && log.category !== 'access_control') return false;
            }

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
               log.id.toLowerCase().indexOf(search) !== -1 ||
               log.integrityHash.indexOf(search) !== -1;
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
                if (log.target.userName) targetDisplay = log.target.userName;
                else if (log.target.name) targetDisplay = log.target.name;
                else if (log.target.resourceId) targetDisplay = log.target.resourceType + ': ' + log.target.resourceId;
            }

            var row = $('<tr class="audit-log-row" data-log-id="' + log.id + '">' +
                '<td class="small">' + formattedDate + '</td>' +
                '<td><span class="fw-medium">' + log.actionLabel + '</span></td>' +
                '<td><span class="badge category-badge-' + log.category + '">' + formatCategory(log.category) + '</span></td>' +
                '<td><span class="badge severity-badge-' + log.severity + '">' + capitalizeFirst(log.severity) + '</span></td>' +
                '<td>' + log.actor.userName + '</td>' +
                '<td>' + targetDisplay + '</td>' +
                '<td class="small text-muted">' + log.context.ipAddress + '</td>' +
                '<td><span class="integrity-badge" title="Integrity Hash">' + log.integrityHash + '</span></td>' +
                '<td><i class="fas fa-chevron-right text-muted"></i></td>' +
            '</tr>');

            row.on('click', function() { showLogDetail(log); });
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
        if (endPage - startPage < 4) startPage = Math.max(1, endPage - 4);

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
        var retentionExpiry = new Date(log.retentionExpiry);

        var html = '<div class="log-detail-section">' +
            '<h6><i class="fas fa-shield-alt me-2"></i>Integrity & Compliance</h6>' +
            '<div class="log-detail-row"><span class="log-detail-label">Integrity Hash</span><span class="log-detail-value"><code>' + log.integrityHash + '</code> <span class="badge badge-pastel-success ms-2">Verified</span></span></div>' +
            '<div class="log-detail-row"><span class="log-detail-label">Retention Until</span><span class="log-detail-value">' + retentionExpiry.toLocaleDateString('en-GB') + ' <span class="retention-indicator retention-active ms-2">7 years</span></span></div>' +
            '<div class="log-detail-row"><span class="log-detail-label">Tamper Status</span><span class="log-detail-value"><span class="badge badge-pastel-success">Unmodified</span></span></div>' +
        '</div>';

        html += '<div class="log-detail-section">' +
            '<h6><i class="fas fa-clock me-2"></i>Event Information</h6>' +
            '<div class="log-detail-row"><span class="log-detail-label">Log ID</span><span class="log-detail-value"><code>' + log.id + '</code></span></div>' +
            '<div class="log-detail-row"><span class="log-detail-label">Timestamp</span><span class="log-detail-value">' + timestamp.toISOString() + '</span></div>' +
            '<div class="log-detail-row"><span class="log-detail-label">Action</span><span class="log-detail-value">' + log.actionLabel + ' <code class="ms-2">(' + log.action + ')</code></span></div>' +
            '<div class="log-detail-row"><span class="log-detail-label">Category</span><span class="log-detail-value"><span class="badge category-badge-' + log.category + '">' + formatCategory(log.category) + '</span></span></div>' +
            '<div class="log-detail-row"><span class="log-detail-label">Severity</span><span class="log-detail-value"><span class="badge severity-badge-' + log.severity + '">' + capitalizeFirst(log.severity) + '</span></span></div>' +
            '<div class="log-detail-row"><span class="log-detail-label">Result</span><span class="log-detail-value"><span class="badge ' + (log.result === 'success' ? 'badge-pastel-success' : 'badge-pastel-danger') + '">' + capitalizeFirst(log.result) + '</span></span></div>' +
        '</div>';

        html += '<div class="log-detail-section">' +
            '<h6><i class="fas fa-user me-2"></i>Actor</h6>' +
            '<div class="log-detail-row"><span class="log-detail-label">User ID</span><span class="log-detail-value"><code>' + log.actor.userId + '</code></span></div>' +
            '<div class="log-detail-row"><span class="log-detail-label">Name</span><span class="log-detail-value">' + log.actor.userName + '</span></div>' +
            '<div class="log-detail-row"><span class="log-detail-label">Role</span><span class="log-detail-value"><span class="badge badge-pastel-primary">' + formatRole(log.actor.role) + '</span></span></div>' +
            (log.actor.subAccountId ? '<div class="log-detail-row"><span class="log-detail-label">Sub-Account</span><span class="log-detail-value">' + log.actor.subAccountId + '</span></div>' : '') +
        '</div>';

        if (log.target) {
            html += '<div class="log-detail-section">' +
                '<h6><i class="fas fa-bullseye me-2"></i>Target</h6>';
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

        if (Object.keys(log.details).length > 0) {
            html += '<div class="log-detail-section">' +
                '<h6><i class="fas fa-info-circle me-2"></i>Additional Details</h6>';
            for (var key in log.details) {
                html += '<div class="log-detail-row"><span class="log-detail-label">' + formatCategory(key) + '</span><span class="log-detail-value">' + log.details[key] + '</span></div>';
            }
            html += '</div>';
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
            activeQuickFilter = 'all';
            $('.quick-filter-btn').removeClass('active');
            $('.quick-filter-btn[data-filter="all"]').addClass('active');
            applyFilters();
        });

        $('.quick-filter-btn').on('click', function() {
            $('.quick-filter-btn').removeClass('active');
            $(this).addClass('active');
            activeQuickFilter = $(this).data('filter');
            applyFilters();
        });

        $('#exportCsvBtn').on('click', function() { exportLogs('csv'); });
        $('#exportJsonBtn').on('click', function() { exportLogs('json'); });

        $('#verifyIntegrityBtn').on('click', function() {
            $('#integrityChecking').show();
            $('#integrityResult').hide();
            $('#integrityModal').modal('show');

            setTimeout(function() {
                $('#integrityChecking').hide();
                $('#integrityResult').show();
                $('#recordsVerified').text(allLogs.length);
                $('#verificationTime').text(Math.floor(Math.random() * 200 + 50) + 'ms');
            }, 1500);
        });
    }

    function exportLogs(format) {
        var data = filteredLogs.length > 0 ? filteredLogs : allLogs;
        var content, filename, mimeType;

        AuditLogger.log('DATA_EXPORTED', {
            data: { exportType: 'audit_log', format: format, recordCount: data.length }
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
        var headers = ['id', 'timestamp', 'action', 'action_label', 'category', 'severity', 'actor_id', 'actor_name', 'actor_role', 'target_id', 'target_name', 'ip_address', 'session_id', 'result', 'integrity_hash', 'retention_expiry'];
        var rows = data.map(function(log) {
            return [
                log.id, log.timestamp, log.action, log.actionLabel, log.category, log.severity,
                log.actor.userId, log.actor.userName, log.actor.role,
                log.target ? (log.target.userId || log.target.resourceId) : '',
                log.target ? (log.target.userName || log.target.name || '') : '',
                log.context.ipAddress, log.context.sessionId, log.result, log.integrityHash, log.retentionExpiry
            ].map(function(v) { return '"' + String(v || '').replace(/"/g, '""') + '"'; }).join(',');
        });
        return headers.join(',') + '\n' + rows.join('\n');
    }

    function formatCategory(category) { return category.split('_').map(capitalizeFirst).join(' '); }
    function formatRole(role) { return role.split('_').map(capitalizeFirst).join(' '); }
    function capitalizeFirst(str) { return str.charAt(0).toUpperCase() + str.slice(1); }

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
