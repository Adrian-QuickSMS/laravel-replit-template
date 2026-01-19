@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@push('styles')
<style>
.admin-dashboard {
    padding: 1.5rem;
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
</style>
@endpush

@section('content')
<div class="admin-dashboard">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1" style="color: #1e3a5f; font-weight: 600;">Platform Overview</h4>
            <p class="text-muted mb-0">Real-time operational metrics and alerts</p>
        </div>
        <div class="d-flex align-items-center gap-3">
            <div class="platform-health-indicator healthy">
                Platform Healthy
            </div>
            <span class="text-muted" style="font-size: 0.8rem;">
                Last updated: <span id="last-update-time">Just now</span>
            </span>
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
                </div>
                <div class="stat-icon primary">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="admin-kpi-grid" style="grid-template-columns: repeat(5, 1fr);">
        <div class="admin-stat-tile">
            <div class="text-center">
                <div class="stat-value" style="font-size: 1.5rem;">847</div>
                <div class="stat-label">Active Accounts</div>
            </div>
        </div>
        <div class="admin-stat-tile">
            <div class="text-center">
                <div class="stat-value" style="font-size: 1.5rem;">12</div>
                <div class="stat-label">Test Mode</div>
            </div>
        </div>
        <div class="admin-stat-tile">
            <div class="text-center">
                <div class="stat-value" style="font-size: 1.5rem; color: #dc2626;">3</div>
                <div class="stat-label">Suspended</div>
            </div>
        </div>
        <div class="admin-stat-tile">
            <div class="text-center">
                <div class="stat-value" style="font-size: 1.5rem; color: #f59e0b;">7</div>
                <div class="stat-label">Pending Approvals</div>
            </div>
        </div>
        <div class="admin-stat-tile">
            <div class="text-center">
                <div class="stat-value" style="font-size: 1.5rem;">2</div>
                <div class="stat-label">Fraud Alerts</div>
            </div>
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
    function updateTime() {
        var now = new Date();
        var timeStr = now.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' });
        document.getElementById('last-update-time').textContent = timeStr;
    }
    
    setInterval(updateTime, 60000);
});
</script>
@endpush
