@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@push('styles')
<style>
.admin-dashboard {
    padding: 1.5rem;
}

.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.dashboard-header h4 {
    margin: 0;
    color: #1e3a5f;
    font-weight: 600;
}

.dashboard-meta {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.platform-status {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.35rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 500;
}

.platform-status.healthy {
    background: rgba(5, 150, 105, 0.1);
    color: #059669;
}

.platform-status::before {
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

.refresh-control {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.8rem;
    color: #64748b;
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

.info-strip {
    background: linear-gradient(135deg, #e8f4fc 0%, #f1f5f9 100%);
    border: 1px solid #d1e3f0;
    border-radius: 6px;
    padding: 0.5rem 1rem;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 0.75rem;
}

.info-strip .rules {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.info-strip .rule-item {
    display: flex;
    align-items: center;
    gap: 0.35rem;
    color: #475569;
}

.info-strip .rule-item i {
    color: #4a90d9;
    font-size: 0.7rem;
}

.info-strip .scope-items {
    display: flex;
    gap: 0.5rem;
}

.info-strip .scope-item {
    background: #fff;
    border: 1px solid #d1e3f0;
    padding: 0.2rem 0.5rem;
    border-radius: 4px;
    font-size: 0.7rem;
    color: #475569;
}

.section-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    margin-bottom: 1.5rem;
}

.section-header {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.section-header h6 {
    margin: 0;
    font-weight: 600;
    color: #1e3a5f;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.section-header h6 i {
    color: #4a90d9;
}

.section-header .badge-admin-only {
    background: rgba(30, 58, 95, 0.1);
    color: #1e3a5f;
    font-size: 0.65rem;
    padding: 0.2rem 0.5rem;
    border-radius: 3px;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.03em;
}

.section-body {
    padding: 1.25rem;
}

.global-filters-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
}

@media (max-width: 1400px) {
    .global-filters-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 1100px) {
    .global-filters-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .global-filters-grid {
        grid-template-columns: 1fr;
    }
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
}

.filter-group label {
    font-size: 0.75rem;
    font-weight: 500;
    color: #475569;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.filter-group label .required {
    color: #dc2626;
}

.filter-group .form-control,
.filter-group .form-select {
    font-size: 0.85rem;
    padding: 0.5rem 0.75rem;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
}

.filter-group .form-control:focus,
.filter-group .form-select:focus {
    border-color: #4a90d9;
    box-shadow: 0 0 0 3px rgba(74, 144, 217, 0.15);
}

.filter-actions {
    display: flex;
    align-items: flex-end;
    gap: 0.75rem;
    padding-top: 0.5rem;
}

.btn-apply-filters {
    background: linear-gradient(135deg, #1e3a5f, #2d5a87);
    color: #fff;
    border: none;
    padding: 0.5rem 1.5rem;
    border-radius: 6px;
    font-weight: 500;
    font-size: 0.85rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-apply-filters:hover {
    background: linear-gradient(135deg, #2d5a87, #4a90d9);
    color: #fff;
}

.btn-reset-filters {
    background: transparent;
    color: #64748b;
    border: 1px solid #e2e8f0;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    font-size: 0.85rem;
}

.btn-reset-filters:hover {
    background: #f8fafc;
    color: #475569;
}

.filter-summary-bar {
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
    padding: 0.75rem 1.25rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.filter-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.filter-chip {
    background: #e8f4fc;
    border: 1px solid #bfdbf7;
    color: #1e3a5f;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    display: flex;
    align-items: center;
    gap: 0.35rem;
}

.filter-chip .chip-label {
    color: #64748b;
}

.filter-chip .chip-remove {
    cursor: pointer;
    color: #94a3b8;
    margin-left: 0.25rem;
}

.filter-chip .chip-remove:hover {
    color: #dc2626;
}

.filter-summary-text {
    font-size: 0.8rem;
    color: #64748b;
}

.filter-summary-text strong {
    color: #1e3a5f;
}

.filter-pending-notice {
    display: none;
    background: rgba(245, 158, 11, 0.1);
    border: 1px solid rgba(245, 158, 11, 0.3);
    color: #b45309;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    font-size: 0.8rem;
    margin-top: 1rem;
}

.filter-pending-notice.visible {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.porting-toggle {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.porting-toggle .form-check-input {
    width: 2.5rem;
    height: 1.25rem;
}

.porting-toggle .toggle-labels {
    display: flex;
    flex-direction: column;
    font-size: 0.7rem;
    color: #64748b;
}

.porting-toggle .toggle-labels .active-label {
    color: #1e3a5f;
    font-weight: 500;
}

.kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
}

@media (max-width: 1200px) {
    .kpi-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .kpi-grid {
        grid-template-columns: 1fr;
    }
}

.kpi-tile {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 1.25rem;
    position: relative;
}

.kpi-tile .kpi-icon {
    position: absolute;
    top: 1rem;
    right: 1rem;
    width: 40px;
    height: 40px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
}

.kpi-tile .kpi-icon.blue {
    background: rgba(74, 144, 217, 0.1);
    color: #4a90d9;
}

.kpi-tile .kpi-icon.green {
    background: rgba(5, 150, 105, 0.1);
    color: #059669;
}

.kpi-tile .kpi-icon.amber {
    background: rgba(245, 158, 11, 0.1);
    color: #f59e0b;
}

.kpi-tile .kpi-icon.purple {
    background: rgba(139, 92, 246, 0.1);
    color: #8b5cf6;
}

.kpi-tile .kpi-label {
    font-size: 0.8rem;
    color: #64748b;
    margin-bottom: 0.25rem;
}

.kpi-tile .kpi-value {
    font-size: 1.75rem;
    font-weight: 700;
    color: #1e3a5f;
    line-height: 1.2;
}

.kpi-tile .kpi-trend {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    font-size: 0.75rem;
    margin-top: 0.35rem;
}

.kpi-tile .kpi-trend.up {
    color: #059669;
}

.kpi-tile .kpi-trend.down {
    color: #dc2626;
}

.kpi-tile .kpi-source {
    font-size: 0.6rem;
    color: #94a3b8;
    margin-top: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.kpi-tile .kpi-source i {
    font-size: 0.55rem;
}

.charts-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 1rem;
}

@media (max-width: 1100px) {
    .charts-grid {
        grid-template-columns: 1fr;
    }
}

.chart-container {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    overflow: hidden;
}

.chart-header {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.chart-header h6 {
    margin: 0;
    font-weight: 600;
    color: #1e3a5f;
    font-size: 0.9rem;
}

.chart-body {
    padding: 1.25rem;
    min-height: 280px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.chart-placeholder {
    color: #94a3b8;
    text-align: center;
}

.chart-placeholder i {
    font-size: 2rem;
    margin-bottom: 0.5rem;
}

.health-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
}

@media (max-width: 992px) {
    .health-grid {
        grid-template-columns: 1fr;
    }
}

.health-table {
    width: 100%;
    font-size: 0.85rem;
}

.health-table th {
    background: #f8fafc;
    padding: 0.75rem 1rem;
    text-align: left;
    font-weight: 500;
    color: #475569;
    border-bottom: 1px solid #e2e8f0;
}

.health-table td {
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #f1f5f9;
    color: #1e3a5f;
}

.health-table tr:last-child td {
    border-bottom: none;
}

.status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    display: inline-block;
    margin-right: 0.5rem;
}

.status-dot.active { background: #059669; }
.status-dot.degraded { background: #f59e0b; }
.status-dot.down { background: #dc2626; }

.network-badge {
    padding: 0.2rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 500;
}

.network-badge.ee { background: rgba(0, 150, 136, 0.1); color: #009688; }
.network-badge.vodafone { background: rgba(230, 0, 0, 0.1); color: #e60000; }
.network-badge.o2 { background: rgba(0, 51, 153, 0.1); color: #003399; }
.network-badge.three { background: rgba(255, 0, 102, 0.1); color: #ff0066; }
.network-badge.mvno { background: rgba(100, 116, 139, 0.1); color: #64748b; }

.margin-risk-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
}

@media (max-width: 1100px) {
    .margin-risk-grid {
        grid-template-columns: 1fr;
    }
}

.risk-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    overflow: hidden;
}

.risk-card .risk-header {
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.risk-card .risk-header h6 {
    margin: 0;
    font-size: 0.85rem;
    font-weight: 600;
    color: #1e3a5f;
}

.risk-card .risk-body {
    padding: 1rem;
}

.risk-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0;
    border-bottom: 1px solid #f1f5f9;
}

.risk-item:last-child {
    border-bottom: none;
}

.risk-item .risk-label {
    font-size: 0.8rem;
    color: #475569;
}

.risk-item .risk-value {
    font-weight: 600;
    font-size: 0.85rem;
}

.risk-item .risk-value.danger { color: #dc2626; }
.risk-item .risk-value.warning { color: #f59e0b; }
.risk-item .risk-value.success { color: #059669; }

.severity-high {
    background: rgba(220, 38, 38, 0.1);
    color: #dc2626;
    padding: 0.2rem 0.5rem;
    border-radius: 4px;
    font-size: 0.7rem;
    font-weight: 600;
}

.severity-medium {
    background: rgba(245, 158, 11, 0.1);
    color: #f59e0b;
    padding: 0.2rem 0.5rem;
    border-radius: 4px;
    font-size: 0.7rem;
    font-weight: 600;
}

.severity-low {
    background: rgba(5, 150, 105, 0.1);
    color: #059669;
    padding: 0.2rem 0.5rem;
    border-radius: 4px;
    font-size: 0.7rem;
    font-weight: 600;
}
</style>
@endpush

@section('content')
<div class="admin-dashboard">
    <div class="dashboard-header">
        <h4><i class="fas fa-tachometer-alt me-2" style="color: #4a90d9;"></i>Admin Dashboard</h4>
        <div class="dashboard-meta">
            <div class="platform-status healthy">Platform Healthy</div>
            <div class="refresh-control">
                <span>Last updated: <span id="last-update-time">Just now</span></span>
                <button type="button" class="btn btn-refresh" onclick="refreshDashboard()" title="Refresh data">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
        </div>
    </div>

    <div class="info-strip">
        <div class="rules">
            <div class="rule-item"><i class="fas fa-database"></i> Warehouse API</div>
            <div class="rule-item"><i class="fas fa-equals"></i> Same definitions</div>
            <div class="rule-item"><i class="fas fa-ban"></i> No UI derivation</div>
            <div class="rule-item"><i class="fas fa-clock"></i> 5-10min cache</div>
        </div>
        <div class="scope-items">
            <span class="scope-item">All Clients</span>
            <span class="scope-item">Portal + API + E2S + Int.</span>
            <span class="scope-item">SMS + RCS</span>
            <span class="scope-item">UK + Intl</span>
        </div>
    </div>

    <div class="section-card" id="global-filters-section">
        <div class="section-header">
            <h6><i class="fas fa-filter"></i> Global Filters</h6>
            <span class="text-muted" style="font-size: 0.75rem;">Filters apply only when you click "Apply Filters"</span>
        </div>
        <div class="section-body">
            <div class="global-filters-grid">
                <div class="filter-group">
                    <label>Date Range <span class="required">*</span></label>
                    <select class="form-select" id="filter-date-range" onchange="onFilterChange()">
                        <option value="today" selected>Today</option>
                        <option value="yesterday">Yesterday</option>
                        <option value="last7">Last 7 days</option>
                        <option value="last30">Last 30 days</option>
                        <option value="mtd">Month to Date</option>
                        <option value="custom">Custom Range...</option>
                    </select>
                </div>

                <div class="filter-group" id="custom-date-range" style="display: none;">
                    <label>Custom Dates</label>
                    <div class="d-flex gap-2">
                        <input type="date" class="form-control" id="filter-date-start" onchange="onFilterChange()">
                        <input type="date" class="form-control" id="filter-date-end" onchange="onFilterChange()">
                    </div>
                </div>

                <div class="filter-group">
                    <label>Client</label>
                    <input type="text" class="form-control" id="filter-client" placeholder="Search clients..." onkeyup="onFilterChange()">
                </div>

                <div class="filter-group">
                    <label>Sending Origin</label>
                    <select class="form-select" id="filter-origin" multiple size="1" onchange="onFilterChange()">
                        <option value="portal">Portal</option>
                        <option value="api">API</option>
                        <option value="email-to-sms">Email-to-SMS</option>
                        <option value="integration">Integration</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label>Product / Channel</label>
                    <select class="form-select" id="filter-channel" multiple size="1" onchange="onFilterChange()">
                        <option value="sms">SMS</option>
                        <option value="rcs-basic">RCS Basic</option>
                        <option value="rcs-single">RCS Single</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label>SenderID</label>
                    <input type="text" class="form-control" id="filter-sender-id" placeholder="Search sender IDs..." onkeyup="onFilterChange()">
                </div>

                <div class="filter-group">
                    <label>Supplier</label>
                    <select class="form-select" id="filter-supplier" multiple size="1" onchange="onFilterChange()">
                        <option value="uk-tier1">UK Tier 1</option>
                        <option value="uk-tier2">UK Tier 2</option>
                        <option value="eu-primary">EU Primary</option>
                        <option value="intl">International</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label>UK Mobile Network</label>
                    <select class="form-select" id="filter-network" multiple size="1" onchange="onFilterChange()">
                        <option value="ee">EE</option>
                        <option value="vodafone">Vodafone</option>
                        <option value="o2">O2</option>
                        <option value="three">Three</option>
                        <option value="mvno">MVNO/Other</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label>Porting View</label>
                    <div class="porting-toggle">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="filter-porting-view" onchange="onFilterChange()">
                        </div>
                        <div class="toggle-labels">
                            <span id="porting-label-original" class="active-label">Original Network</span>
                            <span id="porting-label-ported">Ported-to Network</span>
                        </div>
                    </div>
                </div>

                <div class="filter-group">
                    <label>Country</label>
                    <select class="form-select" id="filter-country" multiple size="1" onchange="onFilterChange()">
                        <option value="uk" selected>United Kingdom</option>
                        <option value="ie">Ireland</option>
                        <option value="de">Germany</option>
                        <option value="fr">France</option>
                        <option value="es">Spain</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div class="filter-actions">
                    <button type="button" class="btn btn-apply-filters" onclick="applyFilters()">
                        <i class="fas fa-check"></i> Apply Filters
                    </button>
                    <button type="button" class="btn btn-reset-filters" onclick="resetFilters()">
                        Reset
                    </button>
                </div>
            </div>

            <div class="filter-pending-notice" id="filter-pending-notice">
                <i class="fas fa-exclamation-circle"></i>
                <span>You have pending filter changes. Click "Apply Filters" to update the dashboard.</span>
            </div>
        </div>

        <div class="filter-summary-bar" id="filter-summary-bar">
            <div class="filter-chips" id="filter-chips">
                <span class="filter-chip">
                    <span class="chip-label">Date:</span> Today
                </span>
                <span class="filter-chip">
                    <span class="chip-label">Country:</span> UK
                </span>
            </div>
            <div class="filter-summary-text">
                <strong>1,247,832</strong> parts | <strong>892,145</strong> messages | <strong>847</strong> clients
            </div>
        </div>
    </div>

    <div class="section-card">
        <div class="section-header">
            <h6><i class="fas fa-pound-sign"></i> Financial KPIs</h6>
        </div>
        <div class="section-body">
            <div class="kpi-grid">
                <div class="kpi-tile">
                    <div class="kpi-icon green"><i class="fas fa-pound-sign"></i></div>
                    <div class="kpi-label">Revenue</div>
                    <div class="kpi-value">£18,492</div>
                    <div class="kpi-trend up"><i class="fas fa-arrow-up"></i> 8.2% vs yesterday</div>
                    <div class="kpi-source"><i class="fas fa-database"></i> fact_billing.charged_amount</div>
                </div>
                <div class="kpi-tile">
                    <div class="kpi-icon amber"><i class="fas fa-coins"></i></div>
                    <div class="kpi-label">Supplier Cost</div>
                    <div class="kpi-value">£12,164</div>
                    <div class="kpi-trend up"><i class="fas fa-arrow-up"></i> 6.1% vs yesterday</div>
                    <div class="kpi-source"><i class="fas fa-database"></i> fact_billing.supplier_cost</div>
                </div>
                <div class="kpi-tile">
                    <div class="kpi-icon blue"><i class="fas fa-chart-line"></i></div>
                    <div class="kpi-label">Gross Margin</div>
                    <div class="kpi-value">£6,328</div>
                    <div class="kpi-trend up"><i class="fas fa-arrow-up"></i> 12.4% vs yesterday</div>
                    <div class="kpi-source"><i class="fas fa-database"></i> revenue - cost</div>
                </div>
                <div class="kpi-tile">
                    <div class="kpi-icon purple"><i class="fas fa-percentage"></i></div>
                    <div class="kpi-label">Margin %</div>
                    <div class="kpi-value">34.2%</div>
                    <div class="kpi-trend down"><i class="fas fa-arrow-down"></i> 1.1% vs yesterday</div>
                    <div class="kpi-source"><i class="fas fa-database"></i> (revenue - cost) / revenue</div>
                </div>
            </div>
        </div>
    </div>

    <div class="section-card">
        <div class="section-header">
            <h6><i class="fas fa-paper-plane"></i> Delivery KPIs</h6>
        </div>
        <div class="section-body">
            <div class="kpi-grid">
                <div class="kpi-tile">
                    <div class="kpi-icon blue"><i class="fas fa-envelope"></i></div>
                    <div class="kpi-label">Messages Submitted</div>
                    <div class="kpi-value">892,145</div>
                    <div class="kpi-trend up"><i class="fas fa-arrow-up"></i> 10.3% vs yesterday</div>
                    <div class="kpi-source"><i class="fas fa-database"></i> fact_messages.submitted</div>
                </div>
                <div class="kpi-tile">
                    <div class="kpi-icon blue"><i class="fas fa-puzzle-piece"></i></div>
                    <div class="kpi-label">Parts Submitted</div>
                    <div class="kpi-value">1,247,832</div>
                    <div class="kpi-trend up"><i class="fas fa-arrow-up"></i> 12.4% vs yesterday</div>
                    <div class="kpi-source"><i class="fas fa-database"></i> fact_messages.parts</div>
                </div>
                <div class="kpi-tile">
                    <div class="kpi-icon green"><i class="fas fa-check-circle"></i></div>
                    <div class="kpi-label">Delivery Rate</div>
                    <div class="kpi-value">98.7%</div>
                    <div class="kpi-trend up"><i class="fas fa-arrow-up"></i> 0.3% vs yesterday</div>
                    <div class="kpi-source"><i class="fas fa-database"></i> delivered / (delivered + failed)</div>
                </div>
                <div class="kpi-tile">
                    <div class="kpi-icon amber"><i class="fas fa-clock"></i></div>
                    <div class="kpi-label">Avg Latency</div>
                    <div class="kpi-value">142ms</div>
                    <div class="kpi-trend down"><i class="fas fa-arrow-down"></i> 8ms vs yesterday</div>
                    <div class="kpi-source"><i class="fas fa-database"></i> fact_delivery.avg_latency</div>
                </div>
            </div>
        </div>
    </div>

    <div class="section-card">
        <div class="section-header">
            <h6><i class="fas fa-chart-area"></i> Platform Reporting</h6>
        </div>
        <div class="section-body">
            <div class="charts-grid">
                <div class="chart-container">
                    <div class="chart-header">
                        <h6>Message Volume (24 Hours)</h6>
                        <div class="d-flex gap-2">
                            <span class="badge" style="background: rgba(74, 144, 217, 0.1); color: #4a90d9;">SMS</span>
                            <span class="badge" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">RCS</span>
                        </div>
                    </div>
                    <div class="chart-body">
                        <div class="chart-placeholder">
                            <i class="fas fa-chart-area d-block"></i>
                            <span>Hourly volume chart</span>
                        </div>
                    </div>
                </div>
                <div class="chart-container">
                    <div class="chart-header">
                        <h6>Channel Split</h6>
                    </div>
                    <div class="chart-body">
                        <div class="chart-placeholder">
                            <i class="fas fa-chart-pie d-block"></i>
                            <span>SMS vs RCS breakdown</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="section-card">
        <div class="section-header">
            <h6><i class="fas fa-route"></i> Supplier & Route Health</h6>
            <span class="badge-admin-only">Admin Only</span>
        </div>
        <div class="section-body">
            <div class="health-grid">
                <div class="chart-container">
                    <div class="chart-header">
                        <h6>Route Performance</h6>
                    </div>
                    <table class="health-table">
                        <thead>
                            <tr>
                                <th>Route</th>
                                <th>Status</th>
                                <th>Success Rate</th>
                                <th>Latency</th>
                                <th>Volume</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span class="status-dot active"></span>UK Tier 1</td>
                                <td><span class="severity-low">Healthy</span></td>
                                <td>99.2%</td>
                                <td>120ms</td>
                                <td>482,341</td>
                            </tr>
                            <tr>
                                <td><span class="status-dot active"></span>UK Tier 2</td>
                                <td><span class="severity-low">Healthy</span></td>
                                <td>97.8%</td>
                                <td>180ms</td>
                                <td>198,432</td>
                            </tr>
                            <tr>
                                <td><span class="status-dot active"></span>EU Primary</td>
                                <td><span class="severity-low">Healthy</span></td>
                                <td>98.5%</td>
                                <td>150ms</td>
                                <td>87,234</td>
                            </tr>
                            <tr>
                                <td><span class="status-dot degraded"></span>International</td>
                                <td><span class="severity-medium">Degraded</span></td>
                                <td>94.1%</td>
                                <td>350ms</td>
                                <td>23,892</td>
                            </tr>
                            <tr>
                                <td><span class="status-dot active"></span>RCS Google</td>
                                <td><span class="severity-low">Healthy</span></td>
                                <td>99.8%</td>
                                <td>95ms</td>
                                <td>42,187</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="chart-container">
                    <div class="chart-header">
                        <h6>Supplier Cost Trends</h6>
                    </div>
                    <div class="chart-body">
                        <div class="chart-placeholder">
                            <i class="fas fa-chart-bar d-block"></i>
                            <span>Cost per route over time</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="section-card">
        <div class="section-header">
            <h6><i class="fas fa-broadcast-tower"></i> UK Network & Porting Health</h6>
            <span class="badge-admin-only">Admin Only</span>
        </div>
        <div class="section-body">
            <div class="health-grid">
                <div class="chart-container">
                    <div class="chart-header">
                        <h6>Network Delivery Performance</h6>
                    </div>
                    <table class="health-table">
                        <thead>
                            <tr>
                                <th>Network</th>
                                <th>Volume</th>
                                <th>Delivery %</th>
                                <th>Avg Latency</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span class="network-badge ee">EE</span></td>
                                <td>312,456</td>
                                <td>99.4%</td>
                                <td>108ms</td>
                                <td><span class="severity-low">Normal</span></td>
                            </tr>
                            <tr>
                                <td><span class="network-badge vodafone">Vodafone</span></td>
                                <td>287,123</td>
                                <td>99.1%</td>
                                <td>125ms</td>
                                <td><span class="severity-low">Normal</span></td>
                            </tr>
                            <tr>
                                <td><span class="network-badge o2">O2</span></td>
                                <td>198,765</td>
                                <td>98.8%</td>
                                <td>142ms</td>
                                <td><span class="severity-low">Normal</span></td>
                            </tr>
                            <tr>
                                <td><span class="network-badge three">Three</span></td>
                                <td>156,432</td>
                                <td>97.2%</td>
                                <td>178ms</td>
                                <td><span class="severity-medium">Elevated</span></td>
                            </tr>
                            <tr>
                                <td><span class="network-badge mvno">MVNO/Other</span></td>
                                <td>45,321</td>
                                <td>96.8%</td>
                                <td>195ms</td>
                                <td><span class="severity-low">Normal</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="chart-container">
                    <div class="chart-header">
                        <h6>Porting Impact Analysis</h6>
                    </div>
                    <table class="health-table">
                        <thead>
                            <tr>
                                <th>Original → Ported</th>
                                <th>Volume</th>
                                <th>Delivery %</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>EE → Vodafone</td>
                                <td>12,456</td>
                                <td>98.9%</td>
                            </tr>
                            <tr>
                                <td>Vodafone → EE</td>
                                <td>9,823</td>
                                <td>99.1%</td>
                            </tr>
                            <tr>
                                <td>O2 → Three</td>
                                <td>7,654</td>
                                <td>97.4%</td>
                            </tr>
                            <tr>
                                <td>Three → O2</td>
                                <td>5,432</td>
                                <td>98.2%</td>
                            </tr>
                            <tr>
                                <td>MVNO → Major</td>
                                <td>3,210</td>
                                <td>96.5%</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="section-card">
        <div class="section-header">
            <h6><i class="fas fa-exclamation-triangle"></i> Margin Risk & Margin Loss Insights</h6>
            <span class="badge-admin-only">Admin Only</span>
        </div>
        <div class="section-body">
            <div class="margin-risk-grid">
                <div class="risk-card">
                    <div class="risk-header">
                        <h6>Margin Erosion Alerts</h6>
                        <span class="severity-high">3 Active</span>
                    </div>
                    <div class="risk-body">
                        <div class="risk-item">
                            <span class="risk-label">Route INT-GLOBAL cost spike</span>
                            <span class="risk-value danger">+18% cost</span>
                        </div>
                        <div class="risk-item">
                            <span class="risk-label">Client ACC-7890 negative margin</span>
                            <span class="risk-value danger">-4.2%</span>
                        </div>
                        <div class="risk-item">
                            <span class="risk-label">Three network retry cost</span>
                            <span class="risk-value warning">+8% overhead</span>
                        </div>
                    </div>
                </div>
                <div class="risk-card">
                    <div class="risk-header">
                        <h6>Low Margin Clients</h6>
                        <span class="text-muted" style="font-size: 0.75rem;">Below 15% threshold</span>
                    </div>
                    <div class="risk-body">
                        <div class="risk-item">
                            <span class="risk-label">ACC-7890 - NewClient</span>
                            <span class="risk-value danger">8.2%</span>
                        </div>
                        <div class="risk-item">
                            <span class="risk-label">ACC-2345 - BulkSender</span>
                            <span class="risk-value warning">12.4%</span>
                        </div>
                        <div class="risk-item">
                            <span class="risk-label">ACC-6789 - ValueCo</span>
                            <span class="risk-value warning">14.1%</span>
                        </div>
                    </div>
                </div>
                <div class="risk-card">
                    <div class="risk-header">
                        <h6>Margin Recovery Actions</h6>
                        <span class="text-muted" style="font-size: 0.75rem;">Pending</span>
                    </div>
                    <div class="risk-body">
                        <div class="risk-item">
                            <span class="risk-label">Route optimization review</span>
                            <span class="risk-value success">Scheduled</span>
                        </div>
                        <div class="risk-item">
                            <span class="risk-label">Pricing review: ACC-7890</span>
                            <span class="risk-value warning">Pending</span>
                        </div>
                        <div class="risk-item">
                            <span class="risk-label">Supplier renegotiation</span>
                            <span class="risk-value success">In Progress</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var lastRefreshTime = new Date();
    var pendingChanges = false;
    var appliedFilters = {
        dateRange: 'today',
        country: ['uk']
    };

    function formatLastUpdated() {
        var now = new Date();
        var diffMs = now - lastRefreshTime;
        var diffMins = Math.floor(diffMs / 60000);
        
        if (diffMins < 1) return 'Just now';
        if (diffMins === 1) return '1 min ago';
        if (diffMins < 60) return diffMins + ' mins ago';
        return lastRefreshTime.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' });
    }

    function updateTimeDisplay() {
        document.getElementById('last-update-time').textContent = formatLastUpdated();
    }

    setInterval(updateTimeDisplay, 30000);

    window.onFilterChange = function() {
        pendingChanges = true;
        document.getElementById('filter-pending-notice').classList.add('visible');
        
        var dateRange = document.getElementById('filter-date-range');
        var customDateDiv = document.getElementById('custom-date-range');
        if (dateRange.value === 'custom') {
            customDateDiv.style.display = 'block';
        } else {
            customDateDiv.style.display = 'none';
        }

        var portingView = document.getElementById('filter-porting-view');
        var originalLabel = document.getElementById('porting-label-original');
        var portedLabel = document.getElementById('porting-label-ported');
        if (portingView.checked) {
            originalLabel.classList.remove('active-label');
            portedLabel.classList.add('active-label');
        } else {
            originalLabel.classList.add('active-label');
            portedLabel.classList.remove('active-label');
        }

        if (typeof AdminControlPlane !== 'undefined') {
            AdminControlPlane.setPendingFilter('dateRange', dateRange.value);
        }
    };

    window.applyFilters = function() {
        pendingChanges = false;
        document.getElementById('filter-pending-notice').classList.remove('visible');
        
        var filters = {
            dateRange: document.getElementById('filter-date-range').value,
            client: document.getElementById('filter-client').value,
            senderId: document.getElementById('filter-sender-id').value,
            portingView: document.getElementById('filter-porting-view').checked ? 'ported' : 'original'
        };

        appliedFilters = filters;
        updateFilterChips();
        lastRefreshTime = new Date();
        updateTimeDisplay();

        if (typeof AdminControlPlane !== 'undefined') {
            AdminControlPlane.applyFilters();
            AdminControlPlane.logAdminAction('DASHBOARD_FILTERS_APPLIED', 'SYSTEM', filters);
        }

        console.log('[Admin Dashboard] Filters applied:', filters);
    };

    window.resetFilters = function() {
        document.getElementById('filter-date-range').value = 'today';
        document.getElementById('filter-client').value = '';
        document.getElementById('filter-sender-id').value = '';
        document.getElementById('filter-porting-view').checked = false;
        document.getElementById('custom-date-range').style.display = 'none';
        
        pendingChanges = false;
        document.getElementById('filter-pending-notice').classList.remove('visible');
        
        appliedFilters = { dateRange: 'today', country: ['uk'] };
        updateFilterChips();

        if (typeof AdminControlPlane !== 'undefined') {
            AdminControlPlane.clearFilters();
            AdminControlPlane.logAdminAction('DASHBOARD_FILTERS_RESET', 'SYSTEM', {});
        }
    };

    function updateFilterChips() {
        var chipsContainer = document.getElementById('filter-chips');
        var chips = [];
        
        if (appliedFilters.dateRange) {
            var dateLabels = {
                'today': 'Today',
                'yesterday': 'Yesterday',
                'last7': 'Last 7 days',
                'last30': 'Last 30 days',
                'mtd': 'Month to Date',
                'custom': 'Custom Range'
            };
            chips.push('<span class="filter-chip"><span class="chip-label">Date:</span> ' + (dateLabels[appliedFilters.dateRange] || appliedFilters.dateRange) + '</span>');
        }

        if (appliedFilters.client) {
            chips.push('<span class="filter-chip"><span class="chip-label">Client:</span> ' + appliedFilters.client + ' <i class="fas fa-times chip-remove" onclick="removeFilterChip(\'client\')"></i></span>');
        }

        if (appliedFilters.country && appliedFilters.country.length > 0) {
            chips.push('<span class="filter-chip"><span class="chip-label">Country:</span> UK</span>');
        }

        chipsContainer.innerHTML = chips.join('');
    }

    window.removeFilterChip = function(filterKey) {
        if (filterKey === 'client') {
            document.getElementById('filter-client').value = '';
        }
        window.applyFilters();
    };

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
        }, 1500);
    };

    console.log('[Admin Dashboard] Data source: Internal Warehouse API');
    console.log('[Admin Dashboard] RULE: Filters apply ONLY on Apply button click');
});
</script>
@endpush
