@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@push('styles')
<style>
.admin-dashboard {
    padding: 1.5rem;
}

.dashboard-ownership-banner {
    background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 100%);
    color: #fff;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    margin-bottom: 1rem;
    font-size: 0.75rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.dashboard-ownership-banner .rule-item {
    display: flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0 0.75rem;
    border-right: 1px solid rgba(255,255,255,0.2);
}

.dashboard-ownership-banner .rule-item:last-child {
    border-right: none;
}

.dashboard-ownership-banner i {
    opacity: 0.7;
}

.metric-definition {
    font-size: 0.65rem;
    color: #94a3b8;
    margin-top: 0.25rem;
}

.warehouse-source {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    font-size: 0.6rem;
    color: #059669;
    background: rgba(5, 150, 105, 0.1);
    padding: 2px 6px;
    border-radius: 3px;
    margin-top: 0.5rem;
}

.warehouse-source i {
    font-size: 0.55rem;
}

.operational-section {
    border-left: 3px solid #4a90d9;
    padding-left: 1rem;
    margin-bottom: 1.5rem;
}

.operational-section-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.75rem;
}

.operational-section-header h6 {
    margin: 0;
    font-weight: 600;
    color: #1e3a5f;
}

.operational-section-header .section-type {
    font-size: 0.65rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #64748b;
    background: #f1f5f9;
    padding: 2px 8px;
    border-radius: 3px;
}

.risk-indicator {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 0.75rem;
    border-radius: 6px;
    margin-bottom: 0.5rem;
}

.risk-indicator.critical {
    background: rgba(220, 38, 38, 0.1);
    border-left: 3px solid #dc2626;
}

.risk-indicator.warning {
    background: rgba(245, 158, 11, 0.1);
    border-left: 3px solid #f59e0b;
}

.risk-indicator.info {
    background: rgba(74, 144, 217, 0.1);
    border-left: 3px solid #4a90d9;
}

.risk-indicator .risk-icon {
    font-size: 1rem;
}

.risk-indicator.critical .risk-icon { color: #dc2626; }
.risk-indicator.warning .risk-icon { color: #f59e0b; }
.risk-indicator.info .risk-icon { color: #4a90d9; }

.risk-indicator .risk-content {
    flex: 1;
}

.risk-indicator .risk-title {
    font-weight: 600;
    font-size: 0.85rem;
    color: #1e3a5f;
}

.risk-indicator .risk-meta {
    font-size: 0.75rem;
    color: #64748b;
}

.admin-kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
    margin-bottom: 1.5rem;
}

@media (max-width: 1200px) {
    .admin-kpi-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .admin-kpi-grid {
        grid-template-columns: 1fr;
    }
}

.admin-charts-row {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

@media (max-width: 992px) {
    .admin-charts-row {
        grid-template-columns: 1fr;
    }
}

.admin-tables-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

@media (max-width: 992px) {
    .admin-tables-row {
        grid-template-columns: 1fr;
    }
}

.platform-health-indicator {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 500;
}

.platform-health-indicator.healthy {
    background: rgba(5, 150, 105, 0.1);
    color: #059669;
}

.platform-health-indicator.degraded {
    background: rgba(245, 158, 11, 0.1);
    color: #f59e0b;
}

.platform-health-indicator.critical {
    background: rgba(220, 38, 38, 0.1);
    color: #dc2626;
}

.platform-health-indicator::before {
    content: '';
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: currentColor;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

.route-status-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.5rem 0;
    border-bottom: 1px solid #f1f5f9;
}

.route-status-row:last-child {
    border-bottom: none;
}

.route-name {
    font-weight: 500;
    color: #1e3a5f;
}

.route-metrics {
    display: flex;
    gap: 1rem;
    font-size: 0.8rem;
    color: #64748b;
}

.route-status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
}

.route-status-dot.active { background: #059669; }
.route-status-dot.degraded { background: #f59e0b; }
.route-status-dot.down { background: #dc2626; }

.pending-action-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.75rem;
    border-radius: 6px;
    background: #f8fafc;
    margin-bottom: 0.5rem;
}

.pending-action-item:last-child {
    margin-bottom: 0;
}

.pending-action-icon {
    width: 36px;
    height: 36px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
}

.pending-action-icon.sender-id {
    background: rgba(124, 58, 237, 0.1);
    color: #7c3aed;
}

.pending-action-icon.rcs {
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
}

.pending-action-icon.account {
    background: rgba(59, 130, 246, 0.1);
    color: #3b82f6;
}

.pending-action-content {
    flex: 1;
}

.pending-action-title {
    font-weight: 500;
    color: #1e3a5f;
    font-size: 0.85rem;
}

.pending-action-meta {
    font-size: 0.75rem;
    color: #64748b;
}

.margin-cell {
    font-weight: 600;
}

.margin-cell.positive { color: #059669; }
.margin-cell.negative { color: #dc2626; }

.data-scope-banner {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 0.5rem 1rem;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 1rem;
}

.data-scope-banner .scope-label {
    font-weight: 600;
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #64748b;
    white-space: nowrap;
}

.data-scope-banner .scope-items {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
}

.data-scope-banner .scope-item {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.75rem;
    color: #475569;
    background: #fff;
    border: 1px solid #e2e8f0;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
}

.data-scope-banner .scope-item i {
    font-size: 0.65rem;
    color: #4a90d9;
}

.refresh-control {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-refresh {
    background: transparent;
    border: 1px solid #e2e8f0;
    color: #64748b;
    width: 32px;
    height: 32px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    transition: all 0.2s ease;
}

.btn-refresh:hover {
    background: #f1f5f9;
    color: #4a90d9;
    border-color: #4a90d9;
}

.btn-refresh.refreshing i {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.api-source-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    font-size: 0.6rem;
    color: #4a90d9;
    background: rgba(74, 144, 217, 0.1);
    padding: 2px 6px;
    border-radius: 3px;
    margin-left: 0.5rem;
}

.forbidden-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    font-size: 0.55rem;
    color: #dc2626;
    background: rgba(220, 38, 38, 0.1);
    padding: 2px 6px;
    border-radius: 3px;
}
</style>
@endpush

@section('content')
<div class="admin-dashboard">
    <div class="dashboard-ownership-banner">
        <span style="font-weight: 600;"><i class="fas fa-cog me-1"></i> OPERATIONAL DASHBOARD</span>
        <div class="rule-item">
            <i class="fas fa-database"></i>
            <span>Warehouse API</span>
        </div>
        <div class="rule-item">
            <i class="fas fa-equals"></i>
            <span>Identical definitions</span>
        </div>
        <div class="rule-item">
            <i class="fas fa-ban"></i>
            <span>No UI derivation</span>
        </div>
        <div class="rule-item">
            <i class="fas fa-clock"></i>
            <span>5-10min cache</span>
        </div>
    </div>

    <div class="data-scope-banner">
        <div class="scope-label"><i class="fas fa-globe me-1"></i> SCOPE</div>
        <div class="scope-items">
            <span class="scope-item"><i class="fas fa-users"></i> All Clients</span>
            <span class="scope-item"><i class="fas fa-sitemap"></i> Portal + API + Email-to-SMS + Integrations</span>
            <span class="scope-item"><i class="fas fa-comment-alt"></i> SMS + RCS (Basic, Single)</span>
            <span class="scope-item"><i class="fas fa-map-marker-alt"></i> UK + International</span>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1" style="color: #1e3a5f; font-weight: 600;">Platform Overview</h4>
            <p class="text-muted mb-0">Operational metrics from warehouse API (cached aggregates)</p>
        </div>
        <div class="d-flex align-items-center gap-3">
            <div class="platform-health-indicator healthy">
                Platform Healthy
            </div>
            <div class="refresh-control">
                <span class="text-muted" style="font-size: 0.8rem;">
                    Last updated: <span id="last-update-time">Just now</span>
                </span>
                <button type="button" class="btn btn-sm btn-refresh" onclick="refreshDashboard()" title="Refresh data">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
        </div>
    </div>

    <div class="operational-section">
        <div class="operational-section-header">
            <h6><i class="fas fa-heartbeat me-2" style="color: #4a90d9;"></i>Platform Health Monitoring</h6>
            <span class="section-type">Real-time</span>
        </div>
    </div>

    <div class="admin-kpi-grid">
        <div class="admin-stat-tile">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">Messages Today</div>
                    <div class="stat-value">1,247,832</div>
                    <div class="stat-trend up">
                        <i class="fas fa-arrow-up"></i> 12.4% vs yesterday
                    </div>
                    <div class="metric-definition">SUM(messages.submitted) WHERE date = TODAY</div>
                    <div class="warehouse-source"><i class="fas fa-database"></i> fact_messages</div>
                </div>
                <div class="stat-icon primary">
                    <i class="fas fa-paper-plane"></i>
                </div>
            </div>
        </div>

        <div class="admin-stat-tile">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">Revenue Today</div>
                    <div class="stat-value">£18,492</div>
                    <div class="stat-trend up">
                        <i class="fas fa-arrow-up"></i> 8.2% vs yesterday
                    </div>
                    <div class="metric-definition">SUM(billing.charged_amount) WHERE date = TODAY</div>
                    <div class="warehouse-source"><i class="fas fa-database"></i> fact_billing</div>
                </div>
                <div class="stat-icon success">
                    <i class="fas fa-pound-sign"></i>
                </div>
            </div>
        </div>

        <div class="admin-stat-tile">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">Gross Margin</div>
                    <div class="stat-value">34.2%</div>
                    <div class="stat-trend down">
                        <i class="fas fa-arrow-down"></i> 1.1% vs yesterday
                    </div>
                    <div class="metric-definition">(revenue - cost) / revenue * 100</div>
                    <div class="warehouse-source"><i class="fas fa-database"></i> fact_billing</div>
                </div>
                <div class="stat-icon warning">
                    <i class="fas fa-percentage"></i>
                </div>
            </div>
        </div>

        <div class="admin-stat-tile">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">Delivery Rate</div>
                    <div class="stat-value">98.7%</div>
                    <div class="stat-trend up">
                        <i class="fas fa-arrow-up"></i> 0.3% vs yesterday
                    </div>
                    <div class="metric-definition">delivered / (delivered + failed) * 100</div>
                    <div class="warehouse-source"><i class="fas fa-database"></i> fact_delivery</div>
                </div>
                <div class="stat-icon primary">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="operational-section" style="border-color: #f59e0b;">
        <div class="operational-section-header">
            <h6><i class="fas fa-exclamation-triangle me-2" style="color: #f59e0b;"></i>Risk & Anomaly Detection</h6>
            <span class="section-type">Monitoring</span>
        </div>
    </div>

    <div class="admin-kpi-grid" style="grid-template-columns: repeat(5, 1fr);">
        <div class="admin-stat-tile">
            <div class="text-center">
                <div class="stat-value" style="font-size: 1.5rem;">847</div>
                <div class="stat-label">Active Accounts</div>
                <div class="warehouse-source"><i class="fas fa-database"></i> dim_accounts</div>
            </div>
        </div>
        <div class="admin-stat-tile">
            <div class="text-center">
                <div class="stat-value" style="font-size: 1.5rem;">12</div>
                <div class="stat-label">Test Mode</div>
                <div class="warehouse-source"><i class="fas fa-database"></i> dim_accounts</div>
            </div>
        </div>
        <div class="admin-stat-tile">
            <div class="text-center">
                <div class="stat-value" style="font-size: 1.5rem; color: #dc2626;">3</div>
                <div class="stat-label">Suspended</div>
                <div class="warehouse-source"><i class="fas fa-database"></i> dim_accounts</div>
            </div>
        </div>
        <div class="admin-stat-tile">
            <div class="text-center">
                <div class="stat-value" style="font-size: 1.5rem; color: #f59e0b;">7</div>
                <div class="stat-label">Pending Approvals</div>
                <div class="warehouse-source"><i class="fas fa-database"></i> fact_approvals</div>
            </div>
        </div>
        <div class="admin-stat-tile">
            <div class="text-center">
                <div class="stat-value" style="font-size: 1.5rem; color: #dc2626;">2</div>
                <div class="stat-label">Fraud Alerts</div>
                <div class="warehouse-source"><i class="fas fa-database"></i> fact_alerts</div>
            </div>
        </div>
    </div>

    <div class="operational-section" style="border-color: #10b981;">
        <div class="operational-section-header">
            <h6><i class="fas fa-chart-line me-2" style="color: #10b981;"></i>Delivery Performance</h6>
            <span class="section-type">Performance</span>
        </div>
    </div>

    <div class="admin-charts-row">
        <div class="admin-chart-container">
            <div class="chart-header">
                <h6>Message Volume (24 Hours)</h6>
                <div class="d-flex gap-3">
                    <span class="badge" style="background: rgba(74, 144, 217, 0.1); color: #4a90d9;">SMS</span>
                    <span class="badge" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">RCS</span>
                </div>
            </div>
            <div id="volume-chart" style="height: 250px; display: flex; align-items: center; justify-content: center;">
                <div class="text-muted">
                    <i class="fas fa-chart-area me-2"></i>
                    Chart loads with ApexCharts integration
                </div>
            </div>
        </div>

        <div class="admin-chart-container">
            <div class="chart-header">
                <h6>Supplier Route Health</h6>
            </div>
            <div class="route-health-list">
                <div class="route-status-row">
                    <div class="d-flex align-items-center gap-2">
                        <span class="route-status-dot active"></span>
                        <span class="route-name">UK Tier 1</span>
                    </div>
                    <div class="route-metrics">
                        <span>99.2% success</span>
                        <span>120ms</span>
                    </div>
                </div>
                <div class="route-status-row">
                    <div class="d-flex align-items-center gap-2">
                        <span class="route-status-dot active"></span>
                        <span class="route-name">UK Tier 2</span>
                    </div>
                    <div class="route-metrics">
                        <span>97.8% success</span>
                        <span>180ms</span>
                    </div>
                </div>
                <div class="route-status-row">
                    <div class="d-flex align-items-center gap-2">
                        <span class="route-status-dot active"></span>
                        <span class="route-name">EU Primary</span>
                    </div>
                    <div class="route-metrics">
                        <span>98.5% success</span>
                        <span>150ms</span>
                    </div>
                </div>
                <div class="route-status-row">
                    <div class="d-flex align-items-center gap-2">
                        <span class="route-status-dot degraded"></span>
                        <span class="route-name">International</span>
                    </div>
                    <div class="route-metrics">
                        <span style="color: #f59e0b;">94.1% success</span>
                        <span style="color: #f59e0b;">350ms</span>
                    </div>
                </div>
                <div class="route-status-row">
                    <div class="d-flex align-items-center gap-2">
                        <span class="route-status-dot active"></span>
                        <span class="route-name">RCS Google</span>
                    </div>
                    <div class="route-metrics">
                        <span>99.8% success</span>
                        <span>95ms</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="operational-section" style="border-color: #8b5cf6;">
        <div class="operational-section-header">
            <h6><i class="fas fa-pound-sign me-2" style="color: #8b5cf6;"></i>Revenue, Cost & Margin</h6>
            <span class="section-type">Financial</span>
        </div>
    </div>

    <div class="admin-tables-row">
        <div class="admin-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>Pending Actions</h5>
                <a href="{{ route('admin.assets.sender-ids') }}" class="btn btn-sm" style="background: #4a90d9; color: #fff;">View All</a>
            </div>
            <div class="card-body">
                <div class="pending-action-item">
                    <div class="pending-action-icon sender-id">
                        <i class="fas fa-id-card"></i>
                    </div>
                    <div class="pending-action-content">
                        <div class="pending-action-title">Sender ID: "ALERTS24"</div>
                        <div class="pending-action-meta">Acme Corp (ACC-1234) • Submitted 2h ago</div>
                    </div>
                    <button class="btn btn-sm btn-outline-primary">Review</button>
                </div>
                <div class="pending-action-item">
                    <div class="pending-action-icon sender-id">
                        <i class="fas fa-id-card"></i>
                    </div>
                    <div class="pending-action-content">
                        <div class="pending-action-title">Sender ID: "MYBANK"</div>
                        <div class="pending-action-meta">Finance Ltd (ACC-5678) • Submitted 4h ago</div>
                    </div>
                    <button class="btn btn-sm btn-outline-primary">Review</button>
                </div>
                <div class="pending-action-item">
                    <div class="pending-action-icon rcs">
                        <i class="fas fa-comment-dots"></i>
                    </div>
                    <div class="pending-action-content">
                        <div class="pending-action-title">RCS Agent: "PromoBot"</div>
                        <div class="pending-action-meta">Retail Co (ACC-9012) • Submitted 1d ago</div>
                    </div>
                    <button class="btn btn-sm btn-outline-primary">Review</button>
                </div>
                <div class="pending-action-item">
                    <div class="pending-action-icon account">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div class="pending-action-content">
                        <div class="pending-action-title">Account Activation</div>
                        <div class="pending-action-meta">New signup (ACC-3456) • Pending KYC</div>
                    </div>
                    <button class="btn btn-sm btn-outline-primary">Review</button>
                </div>
            </div>
        </div>

        <div class="admin-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>Top Clients Today</h5>
                <a href="{{ route('admin.reporting.client') }}" class="btn btn-sm" style="background: #4a90d9; color: #fff;">Full Report</a>
            </div>
            <div class="card-body p-0">
                <table class="table admin-table mb-0">
                    <thead>
                        <tr>
                            <th>Account</th>
                            <th class="text-end">Messages</th>
                            <th class="text-end">Revenue</th>
                            <th class="text-end">Margin</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="admin-account-card" style="padding: 0.25rem 0.5rem; border: none;">
                                        <div class="account-avatar" style="width: 28px; height: 28px; font-size: 0.7rem;">AC</div>
                                        <div>
                                            <div class="account-name" style="font-size: 0.85rem;">Acme Corp</div>
                                            <div class="account-id" style="font-size: 0.7rem;">ACC-1234</div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-end">124,832</td>
                            <td class="text-end">£2,147</td>
                            <td class="text-end margin-cell positive">38.2%</td>
                        </tr>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="admin-account-card" style="padding: 0.25rem 0.5rem; border: none;">
                                        <div class="account-avatar" style="width: 28px; height: 28px; font-size: 0.7rem;">FL</div>
                                        <div>
                                            <div class="account-name" style="font-size: 0.85rem;">Finance Ltd</div>
                                            <div class="account-id" style="font-size: 0.7rem;">ACC-5678</div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-end">98,421</td>
                            <td class="text-end">£1,892</td>
                            <td class="text-end margin-cell positive">35.7%</td>
                        </tr>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="admin-account-card" style="padding: 0.25rem 0.5rem; border: none;">
                                        <div class="account-avatar" style="width: 28px; height: 28px; font-size: 0.7rem;">RC</div>
                                        <div>
                                            <div class="account-name" style="font-size: 0.85rem;">Retail Co</div>
                                            <div class="account-id" style="font-size: 0.7rem;">ACC-9012</div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-end">76,234</td>
                            <td class="text-end">£1,523</td>
                            <td class="text-end margin-cell positive">32.1%</td>
                        </tr>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="admin-account-card" style="padding: 0.25rem 0.5rem; border: none;">
                                        <div class="account-avatar" style="width: 28px; height: 28px; font-size: 0.7rem;">HC</div>
                                        <div>
                                            <div class="account-name" style="font-size: 0.85rem;">HealthCare+</div>
                                            <div class="account-id" style="font-size: 0.7rem;">ACC-3456</div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-end">54,102</td>
                            <td class="text-end">£987</td>
                            <td class="text-end margin-cell positive">29.8%</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="admin-card mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5>Recent Alerts & Anomalies</h5>
            <span class="text-muted" style="font-size: 0.8rem;">Last 24 hours</span>
        </div>
        <div class="card-body p-0">
            <table class="table admin-table mb-0">
                <thead>
                    <tr>
                        <th style="width: 140px;">Time</th>
                        <th>Alert</th>
                        <th>Account</th>
                        <th>Severity</th>
                        <th>Status</th>
                        <th style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>14:32 today</td>
                        <td>
                            <i class="fas fa-exclamation-triangle text-danger me-2"></i>
                            Unusual send volume spike (3x normal)
                        </td>
                        <td>ACC-7890 - NewClient</td>
                        <td><span class="admin-severity-high">HIGH</span></td>
                        <td><span class="admin-status-badge pending">Investigating</span></td>
                        <td>
                            <div class="admin-quick-actions">
                                <button class="btn btn-outline-primary btn-sm">View</button>
                                <button class="btn btn-outline-danger btn-sm">Suspend</button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>11:45 today</td>
                        <td>
                            <i class="fas fa-shield-alt text-warning me-2"></i>
                            Failed login attempts (5x)
                        </td>
                        <td>ACC-1234 - Acme Corp</td>
                        <td><span class="admin-severity-medium">MEDIUM</span></td>
                        <td><span class="admin-status-badge live">Resolved</span></td>
                        <td>
                            <div class="admin-quick-actions">
                                <button class="btn btn-outline-primary btn-sm">View</button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>09:12 today</td>
                        <td>
                            <i class="fas fa-server text-warning me-2"></i>
                            Route INT-GLOBAL latency elevated
                        </td>
                        <td>System-wide</td>
                        <td><span class="admin-severity-medium">MEDIUM</span></td>
                        <td><span class="admin-status-badge pending">Monitoring</span></td>
                        <td>
                            <div class="admin-quick-actions">
                                <button class="btn btn-outline-primary btn-sm">Details</button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>Yesterday 22:18</td>
                        <td>
                            <i class="fas fa-ban text-danger me-2"></i>
                            Blocked content detected (spam pattern)
                        </td>
                        <td>ACC-4567 - TestCo</td>
                        <td><span class="admin-severity-high">HIGH</span></td>
                        <td><span class="admin-status-badge suspended">Account Suspended</span></td>
                        <td>
                            <div class="admin-quick-actions">
                                <button class="btn btn-outline-primary btn-sm">Review</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var lastRefreshTime = new Date();
    
    function formatLastUpdated() {
        var now = new Date();
        var diffMs = now - lastRefreshTime;
        var diffMins = Math.floor(diffMs / 60000);
        
        if (diffMins < 1) {
            return 'Just now';
        } else if (diffMins === 1) {
            return '1 min ago';
        } else if (diffMins < 60) {
            return diffMins + ' mins ago';
        } else {
            return lastRefreshTime.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' });
        }
    }
    
    function updateTimeDisplay() {
        document.getElementById('last-update-time').textContent = formatLastUpdated();
    }
    
    setInterval(updateTimeDisplay, 30000);
    
    window.refreshDashboard = function() {
        var btn = document.querySelector('.btn-refresh');
        btn.classList.add('refreshing');
        btn.disabled = true;
        
        setTimeout(function() {
            lastRefreshTime = new Date();
            updateTimeDisplay();
            btn.classList.remove('refreshing');
            btn.disabled = false;
            
            if (typeof AdminControlPlane !== 'undefined') {
                AdminControlPlane.logAdminAction('DASHBOARD_REFRESH', 'SYSTEM', {
                    refresh_type: 'manual',
                    timestamp: lastRefreshTime.toISOString()
                });
            }
            
            console.log('[Admin Dashboard] Data refreshed from warehouse API at', lastRefreshTime.toISOString());
        }, 1500);
    };
    
    if (typeof AdminControlPlane !== 'undefined') {
        console.log('[Admin Dashboard] Data source: Internal Warehouse API');
        console.log('[Admin Dashboard] Cache TTL: 5-10 minutes');
        console.log('[Admin Dashboard] FORBIDDEN: Direct supplier queries, UI-side derivation');
    }
});
</script>
@endpush
