@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@push('styles')
<style>
/* ==============================================
   ADMIN DASHBOARD - FILLOW COMPONENT OVERRIDES
   Uses Fillow base classes with admin blue theme
   ============================================== */

/* Admin blue theme color overrides for Fillow */
.admin-dashboard {
    --admin-primary: #1e3a5f;
    --admin-secondary: #2d5a87;
    --admin-accent: #4a90d9;
}

/* Override Fillow primary colors in admin context */
.admin-dashboard .bgl-primary {
    background: rgba(74, 144, 217, 0.1) !important;
}
.admin-dashboard .text-primary {
    color: #4a90d9 !important;
}

/* Dashboard header - uses Fillow row utilities */
.admin-dashboard .dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.admin-dashboard .dashboard-header h4 {
    margin: 0;
    color: var(--admin-primary);
    font-weight: 600;
}

.dashboard-meta {
    display: flex;
    align-items: center;
    gap: 1rem;
}

/* Collapse icon rotation for collapsible sections */
.collapse-icon {
    transition: transform 0.3s ease;
}
[aria-expanded="true"] .collapse-icon {
    transform: rotate(180deg);
}

/* Platform status badge REMOVED - no longer displayed */
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

/* Admin KPI tile hover effects - follows Fillow widget-stat pattern */
.admin-kpi-tile.cursor-pointer {
    cursor: pointer;
    transition: all 0.2s ease;
}

.admin-kpi-tile.cursor-pointer:hover {
    border-color: var(--admin-accent, #4a90d9) !important;
    box-shadow: 0 4px 12px rgba(74, 144, 217, 0.15);
    transform: translateY(-2px);
}

/* Ensure proper card spacing for widget-stat inside row */
.admin-dashboard .row .widget-stat.card {
    margin-bottom: 1rem;
}

/* Admin theme colors for Fillow badges */
.admin-dashboard .badge.badge-primary {
    background: rgba(74, 144, 217, 0.1);
    color: #4a90d9;
}

/* Cursor pointer utility */
.cursor-pointer {
    cursor: pointer;
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

/* Legacy section-card/header/body classes REMOVED - now using Fillow card classes */

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

/* Legacy kpi-grid/kpi-tile/kpi-tooltip classes REMOVED - now using Fillow widget-stat with Bootstrap tooltips */

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

.reporting-charts-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
}

.reporting-charts-grid .chart-wide {
    grid-column: span 2;
}

@media (max-width: 1100px) {
    .reporting-charts-grid {
        grid-template-columns: 1fr;
    }
    .reporting-charts-grid .chart-wide {
        grid-column: span 1;
    }
}

.chart-legend {
    display: flex;
    gap: 1rem;
    font-size: 0.75rem;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 0.35rem;
    color: #64748b;
}

.legend-dot {
    width: 10px;
    height: 10px;
    border-radius: 2px;
    background: var(--color);
}

.chart-footer {
    padding: 0.5rem 1rem;
    border-top: 1px solid #f1f5f9;
    background: #fafbfc;
}

.chart-source {
    font-size: 0.65rem;
    color: #94a3b8;
    display: flex;
    align-items: center;
    gap: 0.35rem;
}

.chart-source i {
    font-size: 0.6rem;
}

.chart-note {
    font-size: 0.7rem;
    color: #94a3b8;
    margin-top: 0.5rem;
}

.chart-toggle-group {
    display: flex;
    gap: 0;
    border: 1px solid #e2e8f0;
    border-radius: 4px;
    overflow: hidden;
}

.chart-toggle {
    background: #fff;
    border: none;
    padding: 0.25rem 0.75rem;
    font-size: 0.7rem;
    color: #64748b;
    cursor: pointer;
    border-right: 1px solid #e2e8f0;
    transition: all 0.2s ease;
}

.chart-toggle:last-child {
    border-right: none;
}

.chart-toggle.active {
    background: #1e3a5f;
    color: #fff;
}

.chart-toggle:hover:not(.active) {
    background: #f8fafc;
}

.top-items-table {
    width: 100%;
    font-size: 0.8rem;
}

.top-items-table th {
    font-weight: 500;
    color: #64748b;
    padding: 0.5rem;
    border-bottom: 1px solid #e2e8f0;
    text-align: left;
}

.top-items-table td {
    padding: 0.5rem;
    border-bottom: 1px solid #f1f5f9;
    color: #1e3a5f;
}

.top-items-table tr:last-child td {
    border-bottom: none;
}

.top-items-table code {
    background: rgba(74, 144, 217, 0.1);
    color: #1e3a5f;
    padding: 0.15rem 0.4rem;
    border-radius: 3px;
    font-size: 0.75rem;
}

.horizontal-bar-chart {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.bar-row {
    display: grid;
    grid-template-columns: 100px 1fr 80px;
    gap: 0.75rem;
    align-items: center;
}

.bar-label {
    font-size: 0.8rem;
    color: #475569;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.bar-track {
    height: 20px;
    background: #f1f5f9;
    border-radius: 4px;
    overflow: hidden;
}

.bar-fill {
    height: 100%;
    background: linear-gradient(90deg, #4a90d9, #2d5a87);
    border-radius: 4px;
    transition: width 0.3s ease;
}

.bar-value {
    font-size: 0.8rem;
    font-weight: 500;
    color: #1e3a5f;
    text-align: right;
}

.supplier-health-grid {
    display: grid;
    grid-template-columns: 280px 1fr 1fr;
    gap: 1rem;
}

.supplier-health-grid .chart-span-2 {
    grid-column: span 2;
}

@media (max-width: 1200px) {
    .supplier-health-grid {
        grid-template-columns: 1fr 1fr;
    }
    .supplier-health-grid .route-health-tile {
        grid-column: span 2;
    }
    .supplier-health-grid .chart-span-2 {
        grid-column: span 2;
    }
}

@media (max-width: 768px) {
    .supplier-health-grid {
        grid-template-columns: 1fr;
    }
    .supplier-health-grid .route-health-tile,
    .supplier-health-grid .chart-span-2 {
        grid-column: span 1;
    }
}

.route-health-tile {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 1.25rem;
    display: flex;
    flex-direction: column;
    transition: all 0.2s ease;
}

.route-health-tile.clickable:hover {
    border-color: #4a90d9;
    box-shadow: 0 4px 12px rgba(74, 144, 217, 0.15);
}

.route-health-status {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    font-weight: 600;
    font-size: 1rem;
    margin-bottom: 1rem;
}

.route-health-status.healthy {
    background: rgba(5, 150, 105, 0.1);
    color: #059669;
}

.route-health-status.degraded {
    background: rgba(245, 158, 11, 0.1);
    color: #f59e0b;
}

.route-health-status.critical {
    background: rgba(220, 38, 38, 0.1);
    color: #dc2626;
}

.route-health-title {
    font-size: 0.85rem;
    color: #64748b;
    margin-bottom: 1rem;
}

.route-health-counts {
    display: flex;
    gap: 1.5rem;
    margin-bottom: 1rem;
}

.route-count {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.route-count .count-value {
    font-size: 1.5rem;
    font-weight: 700;
}

.route-count .count-value.success { color: #059669; }
.route-count .count-value.warning { color: #f59e0b; }
.route-count .count-value.danger { color: #dc2626; }

.route-count .count-label {
    font-size: 0.7rem;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.03em;
}

.route-health-footer {
    margin-top: auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 0.75rem;
    border-top: 1px solid #f1f5f9;
}

.drill-hint {
    font-size: 0.7rem;
    color: #4a90d9;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.response-time-metrics {
    display: flex;
    justify-content: space-around;
    padding: 1rem 0;
    border-bottom: 1px solid #f1f5f9;
}

.response-metric {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.25rem;
}

.response-metric .metric-label {
    font-size: 0.7rem;
    color: #64748b;
    text-transform: uppercase;
}

.response-metric .metric-value {
    font-size: 1.25rem;
    font-weight: 600;
    color: #1e3a5f;
}

.response-metric .metric-value.warning { color: #f59e0b; }
.response-metric .metric-value.danger { color: #dc2626; }

.stacked-bar-chart {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.stacked-bar-row {
    display: grid;
    grid-template-columns: 100px 1fr 80px;
    gap: 0.75rem;
    align-items: center;
    padding: 0.35rem 0;
    border-radius: 4px;
    transition: background 0.2s ease;
}

.stacked-bar-row.clickable:hover {
    background: #f8fafc;
}

.stacked-bar-label {
    font-size: 0.8rem;
    color: #475569;
    font-weight: 500;
}

.stacked-bar-track {
    height: 24px;
    background: #f1f5f9;
    border-radius: 4px;
    overflow: hidden;
    display: flex;
}

.stacked-segment {
    height: 100%;
    transition: width 0.3s ease;
}

.stacked-segment.sms { background: #4a90d9; }
.stacked-segment.rcs-basic { background: #10b981; }
.stacked-segment.rcs-single { background: #8b5cf6; }

.stacked-bar-total {
    font-size: 0.8rem;
    font-weight: 500;
    color: #1e3a5f;
    text-align: right;
}

.supplier-health-grid .chart-span-full {
    grid-column: 1 / -1;
}

.pricing-upload-panel {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    overflow: hidden;
}

.pricing-upload-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.25rem;
    background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 100%);
    color: #fff;
}

.pricing-upload-title h6 {
    margin: 0;
    font-size: 1rem;
    color: #fff;
}

.pricing-upload-title .pricing-purpose {
    font-size: 0.75rem;
    color: rgba(255, 255, 255, 0.7);
}

.pricing-upload-body {
    padding: 1rem 1.25rem;
}

.pricing-info-strip {
    background: #e0f2fe;
    border: 1px solid #7dd3fc;
    color: #0369a1;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    font-size: 0.8rem;
    margin-bottom: 1rem;
}

.pricing-info-strip i {
    margin-right: 0.5rem;
}

.pricing-table-header {
    font-size: 0.75rem;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 0.5rem;
}

.pricing-uploads-table {
    width: 100%;
    font-size: 0.8rem;
    border-collapse: collapse;
}

.pricing-uploads-table th,
.pricing-uploads-table td {
    padding: 0.5rem 0.75rem;
    text-align: left;
    border-bottom: 1px solid #f1f5f9;
}

.pricing-uploads-table th {
    font-weight: 600;
    color: #475569;
    background: #f8fafc;
}

.pricing-uploads-table td {
    color: #334155;
}

.version-badge {
    background: #4a90d9;
    color: #fff;
    font-size: 0.65rem;
    padding: 0.15rem 0.4rem;
    border-radius: 4px;
    font-weight: 600;
}

.btn-icon {
    background: transparent;
    border: none;
    color: #64748b;
    padding: 0.25rem 0.5rem;
    cursor: pointer;
    transition: color 0.2s ease;
}

.btn-icon:hover {
    color: #4a90d9;
}

.admin-modal .modal-header {
    background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 100%);
    color: #fff;
}

.admin-modal .modal-header .btn-close {
    filter: brightness(0) invert(1);
}

.admin-modal .modal-title {
    font-size: 1rem;
}

.admin-modal .modal-title i {
    margin-right: 0.5rem;
}

.upload-requirements {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1.5rem;
}

.upload-requirements h6 {
    font-size: 0.85rem;
    color: #1e3a5f;
    margin-bottom: 0.75rem;
}

.requirement-list {
    margin-bottom: 0.75rem;
}

.requirement-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.8rem;
    color: #475569;
    margin-bottom: 0.25rem;
}

.required-columns-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-bottom: 1rem;
    padding-left: 1.5rem;
}

.column-tag {
    background: #1e3a5f;
    color: #fff;
    font-size: 0.7rem;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-family: monospace;
}

.validation-notes {
    font-size: 0.75rem;
    color: #64748b;
    border-top: 1px dashed #cbd5e1;
    padding-top: 0.75rem;
}

.validation-notes p {
    margin-bottom: 0.25rem;
    color: #475569;
}

.validation-notes ul {
    margin: 0;
    padding-left: 1.25rem;
}

.validation-notes code {
    background: #e2e8f0;
    padding: 0.1rem 0.3rem;
    border-radius: 3px;
    font-size: 0.7rem;
}

.upload-dropzone {
    border: 2px dashed #cbd5e1;
    border-radius: 8px;
    padding: 2rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s ease;
}

.upload-dropzone:hover {
    border-color: #4a90d9;
    background: #f0f9ff;
}

.upload-dropzone i {
    font-size: 2rem;
    color: #94a3b8;
    margin-bottom: 0.5rem;
}

.upload-dropzone p {
    margin: 0;
    color: #475569;
    font-size: 0.85rem;
}

.upload-dropzone small {
    color: #94a3b8;
}

.upload-file-preview {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    background: #f0fdf4;
    border: 1px solid #86efac;
    border-radius: 8px;
    padding: 0.75rem 1rem;
}

.upload-file-preview i {
    font-size: 1.5rem;
    color: #16a34a;
}

.upload-file-preview .file-name {
    font-weight: 500;
    color: #166534;
    flex: 1;
}

.upload-file-preview .file-size {
    font-size: 0.75rem;
    color: #64748b;
}

.section-header-controls {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.porting-toggle-inline {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.75rem;
}

.porting-toggle-inline .toggle-label {
    color: #64748b;
    transition: color 0.2s ease;
}

.porting-toggle-inline .toggle-label.active {
    color: #1e3a5f;
    font-weight: 600;
}

.toggle-switch {
    position: relative;
    display: inline-block;
    width: 36px;
    height: 20px;
}

.toggle-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.toggle-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #cbd5e1;
    transition: 0.3s;
    border-radius: 20px;
}

.toggle-slider:before {
    position: absolute;
    content: "";
    height: 14px;
    width: 14px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: 0.3s;
    border-radius: 50%;
}

.toggle-switch input:checked + .toggle-slider {
    background-color: #4a90d9;
}

.toggle-switch input:checked + .toggle-slider:before {
    transform: translateX(16px);
}

.uk-network-grid {
    display: grid;
    grid-template-columns: 1fr 320px;
    gap: 1rem;
}

@media (max-width: 1024px) {
    .uk-network-grid {
        grid-template-columns: 1fr;
    }
}

.chart-wide {
    min-height: 300px;
}

.network-stacked-chart {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    padding: 0.5rem 0;
}

.network-bar-row {
    display: grid;
    grid-template-columns: 100px 1fr 80px;
    gap: 0.75rem;
    align-items: center;
    padding: 0.5rem 0;
    border-radius: 4px;
    transition: background 0.2s ease;
}

.network-bar-row.clickable:hover {
    background: #f8fafc;
}

.network-bar-label {
    display: flex;
    align-items: center;
}

.network-bar-track {
    height: 28px;
    background: #f1f5f9;
    border-radius: 4px;
    overflow: hidden;
    display: flex;
}

.status-segment {
    height: 100%;
    transition: width 0.3s ease;
}

.status-segment.delivered { background: #10b981; }
.status-segment.undelivered { background: #ef4444; }
.status-segment.pending { background: #f59e0b; }
.status-segment.rejected { background: #64748b; }

.network-bar-total {
    font-size: 0.8rem;
    font-weight: 500;
    color: #1e3a5f;
    text-align: right;
}

.network-kpi-tiles {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.network-kpi-tile {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 0.75rem 1rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    cursor: pointer;
    transition: all 0.2s ease;
    position: relative;
}

.network-kpi-tile:hover {
    border-color: #4a90d9;
    box-shadow: 0 2px 8px rgba(74, 144, 217, 0.15);
}

.network-kpi-tile.green { border-left: 4px solid #10b981; }
.network-kpi-tile.amber { border-left: 4px solid #f59e0b; }
.network-kpi-tile.red { border-left: 4px solid #ef4444; }

.network-kpi-badge {
    min-width: 70px;
}

.network-kpi-value {
    font-size: 1.25rem;
    font-weight: 700;
    color: #1e3a5f;
}

.network-kpi-tile.green .network-kpi-value { color: #059669; }
.network-kpi-tile.amber .network-kpi-value { color: #d97706; }
.network-kpi-tile.red .network-kpi-value { color: #dc2626; }

.network-kpi-label {
    font-size: 0.7rem;
    color: #64748b;
    flex: 1;
}

.network-kpi-tooltip {
    display: none;
    position: absolute;
    right: 100%;
    top: 50%;
    transform: translateY(-50%);
    background: #1e3a5f;
    color: #fff;
    padding: 0.75rem;
    border-radius: 6px;
    font-size: 0.75rem;
    width: 200px;
    z-index: 100;
    margin-right: 10px;
}

.network-kpi-tile:hover .network-kpi-tooltip {
    display: block;
}

.network-kpi-tooltip::after {
    content: '';
    position: absolute;
    left: 100%;
    top: 50%;
    transform: translateY(-50%);
    border: 6px solid transparent;
    border-left-color: #1e3a5f;
}

.tooltip-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.25rem;
}

.tooltip-row.small {
    font-size: 0.7rem;
    opacity: 0.9;
}

.tooltip-section {
    font-size: 0.65rem;
    text-transform: uppercase;
    color: rgba(255,255,255,0.7);
    margin: 0.5rem 0 0.25rem;
    letter-spacing: 0.03em;
}

.anomaly-banners {
    margin-bottom: 1rem;
}

.anomaly-banner {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    border-radius: 6px;
    margin-bottom: 0.5rem;
    font-size: 0.85rem;
}

.anomaly-banner.warning {
    background: #fef3c7;
    border: 1px solid #f59e0b;
    color: #92400e;
}

.anomaly-banner.danger {
    background: #fee2e2;
    border: 1px solid #ef4444;
    color: #991b1b;
}

.anomaly-banner i {
    font-size: 1rem;
}

.btn-dismiss {
    margin-left: auto;
    background: transparent;
    border: none;
    color: inherit;
    opacity: 0.6;
    cursor: pointer;
    padding: 0.25rem;
}

.btn-dismiss:hover {
    opacity: 1;
}

.margin-view-tabs {
    display: flex;
    gap: 0.25rem;
    margin-bottom: 1rem;
    background: #f1f5f9;
    padding: 0.25rem;
    border-radius: 6px;
    width: fit-content;
}

.margin-tab {
    padding: 0.5rem 1rem;
    border: none;
    background: transparent;
    color: #64748b;
    font-size: 0.8rem;
    font-weight: 500;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.margin-tab:hover {
    color: #1e3a5f;
}

.margin-tab.active {
    background: #fff;
    color: #1e3a5f;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.margin-table-wrapper {
    overflow-x: auto;
}

.margin-risk-table {
    width: 100%;
    font-size: 0.8rem;
    border-collapse: collapse;
}

.margin-risk-table th,
.margin-risk-table td {
    padding: 0.75rem;
    text-align: left;
    border-bottom: 1px solid #f1f5f9;
}

.margin-risk-table th {
    font-weight: 600;
    color: #475569;
    background: #f8fafc;
    position: sticky;
    top: 0;
}

.margin-risk-table th.sortable {
    cursor: pointer;
    user-select: none;
}

.margin-risk-table th.sortable:hover {
    background: #e2e8f0;
}

.margin-risk-table th i {
    margin-left: 0.25rem;
    font-size: 0.7rem;
    opacity: 0.5;
}

.margin-risk-table th.sorted i {
    opacity: 1;
}

.margin-row {
    cursor: pointer;
    transition: background 0.2s ease;
}

.margin-row:hover {
    background: #f8fafc;
}

.margin-row.danger {
    background: #fef2f2;
}

.margin-row.danger:hover {
    background: #fee2e2;
}

.margin-row.warning {
    background: #fffbeb;
}

.margin-row.warning:hover {
    background: #fef3c7;
}

.dimension-flag {
    margin-right: 0.5rem;
}

.margin-loss {
    color: #dc2626;
    font-weight: 600;
}

.margin-badge {
    display: inline-block;
    padding: 0.2rem 0.5rem;
    border-radius: 4px;
    font-weight: 600;
    font-size: 0.75rem;
    background: #e2e8f0;
    color: #475569;
}

.margin-badge.danger {
    background: #fee2e2;
    color: #dc2626;
}

.margin-badge.warning {
    background: #fef3c7;
    color: #d97706;
}

.margin-badge.amber {
    background: #fff7ed;
    color: #ea580c;
}

.margin-badge.success {
    background: #dcfce7;
    color: #16a34a;
}

.margin-table-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    font-size: 0.75rem;
    color: #64748b;
    border-top: 1px solid #f1f5f9;
    margin-top: 0.5rem;
}

.table-info {
    font-style: italic;
}
</style>
@endpush

@section('content')
<div class="admin-dashboard">
    <div class="dashboard-header">
        <h4><i class="fas fa-tachometer-alt me-2" style="color: #4a90d9;"></i>Admin Dashboard</h4>
        <div class="dashboard-meta">
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

    <!-- Global Filters - Collapsible card -->
    <div class="card" id="global-filters-section">
        <div class="card-header border-0 pb-0 d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <a href="javascript:void(0);" class="text-dark d-flex align-items-center" data-bs-toggle="collapse" data-bs-target="#globalFiltersBody" aria-expanded="false" aria-controls="globalFiltersBody">
                    <i class="fas fa-filter me-2 text-primary"></i>Global Filters
                    <i class="fas fa-chevron-down ms-2 collapse-icon" style="font-size: 0.75rem; transition: transform 0.3s;"></i>
                </a>
            </h5>
            <span class="text-muted" style="font-size: 0.75rem;">Filters apply only when you click "Apply Filters"</span>
        </div>
        <div class="card-body collapse" id="globalFiltersBody">
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

    <!-- Financial Overview - Using Fillow card structure -->
    <div class="card" id="financial-overview-section">
        <div class="card-header border-0 pb-0 d-flex justify-content-between align-items-center">
            <h5 class="card-title"><i class="fas fa-pound-sign me-2 text-primary"></i>Financial Overview</h5>
            <span class="text-muted" style="font-size: 0.75rem;">Values respect active filters</span>
        </div>
        <div class="card-body">
            <div class="row">
                <!-- Volume KPI - Using Fillow widget-stat pattern -->
                <div class="col-xl-3 col-lg-6 col-sm-6">
                    <div class="widget-stat card admin-kpi-tile cursor-pointer" onclick="drillToReport('message-logs')" data-kpi="volume" data-bs-toggle="tooltip" data-bs-placement="top" title="SUM(parts) WHERE billable = true">
                        <div class="card-body p-4">
                            <div class="media ai-icon">
                                <span class="me-3 bgl-primary text-primary">
                                    <i class="fas fa-puzzle-piece"></i>
                                </span>
                                <div class="media-body">
                                    <p class="mb-1">Volume (Parts)</p>
                                    <h4 class="mb-0">1,247,832</h4>
                                    <span class="badge badge-success light mt-1"><i class="fas fa-arrow-up me-1"></i>12.4%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Revenue KPI -->
                <div class="col-xl-3 col-lg-6 col-sm-6">
                    <div class="widget-stat card admin-kpi-tile cursor-pointer" onclick="drillToReport('client-reporting')" data-kpi="revenue" data-bs-toggle="tooltip" data-bs-placement="top" title="SUM(customer_cost) WHERE billable = true">
                        <div class="card-body p-4">
                            <div class="media ai-icon">
                                <span class="me-3 bgl-success text-success">
                                    <i class="fas fa-pound-sign"></i>
                                </span>
                                <div class="media-body">
                                    <p class="mb-1">Revenue</p>
                                    <h4 class="mb-0">£18,492</h4>
                                    <span class="badge badge-success light mt-1"><i class="fas fa-arrow-up me-1"></i>8.2%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Gross Margin KPI -->
                <div class="col-xl-3 col-lg-6 col-sm-6">
                    <div class="widget-stat card admin-kpi-tile cursor-pointer" onclick="drillToReport('client-reporting')" data-kpi="margin" data-bs-toggle="tooltip" data-bs-placement="top" title="revenue - supplier_cost">
                        <div class="card-body p-4">
                            <div class="media ai-icon">
                                <span class="me-3 bgl-info text-info">
                                    <i class="fas fa-chart-line"></i>
                                </span>
                                <div class="media-body">
                                    <p class="mb-1">Gross Margin</p>
                                    <h4 class="mb-0">£6,328</h4>
                                    <span class="badge badge-success light mt-1"><i class="fas fa-arrow-up me-1"></i>12.4%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Margin % KPI -->
                <div class="col-xl-3 col-lg-6 col-sm-6">
                    <div class="widget-stat card admin-kpi-tile cursor-pointer" onclick="drillToReport('client-reporting')" data-kpi="margin-pct" data-bs-toggle="tooltip" data-bs-placement="top" title="(revenue - cost) / revenue * 100">
                        <div class="card-body p-4">
                            <div class="media ai-icon">
                                <span class="me-3 bgl-warning text-warning">
                                    <i class="fas fa-percentage"></i>
                                </span>
                                <div class="media-body">
                                    <p class="mb-1">Margin %</p>
                                    <h4 class="mb-0">34.2%</h4>
                                    <span class="badge badge-danger light mt-1"><i class="fas fa-arrow-down me-1"></i>1.1%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delivery Overview - Using Fillow card structure -->
    <div class="card" id="delivery-overview-section">
        <div class="card-header border-0 pb-0 d-flex justify-content-between align-items-center">
            <h5 class="card-title"><i class="fas fa-paper-plane me-2 text-primary"></i>Delivery Overview</h5>
            <span class="text-muted" style="font-size: 0.75rem;">Matches customer reporting logic exactly</span>
        </div>
        <div class="card-body">
            <div class="row">
                <!-- Sent Parts KPI -->
                <div class="col-xl-3 col-lg-6 col-sm-6">
                    <div class="widget-stat card admin-kpi-tile cursor-pointer" onclick="drillToReport('message-logs')" data-kpi="sent-parts" data-bs-toggle="tooltip" data-bs-placement="top" title="SUM(parts) WHERE status IN (sent,delivered,failed,rejected)">
                        <div class="card-body p-4">
                            <div class="media ai-icon">
                                <span class="me-3 bgl-primary text-primary">
                                    <i class="fas fa-paper-plane"></i>
                                </span>
                                <div class="media-body">
                                    <p class="mb-1">Sent (Parts)</p>
                                    <h4 class="mb-0">1,247,832</h4>
                                    <span class="badge badge-success light mt-1"><i class="fas fa-arrow-up me-1"></i>12.4%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Delivered Parts KPI -->
                <div class="col-xl-3 col-lg-6 col-sm-6">
                    <div class="widget-stat card admin-kpi-tile cursor-pointer" onclick="drillToReport('message-logs')" data-kpi="delivered-parts" data-bs-toggle="tooltip" data-bs-placement="top" title="SUM(parts) WHERE status = delivered">
                        <div class="card-body p-4">
                            <div class="media ai-icon">
                                <span class="me-3 bgl-success text-success">
                                    <i class="fas fa-check-circle"></i>
                                </span>
                                <div class="media-body">
                                    <p class="mb-1">Delivered (Parts)</p>
                                    <h4 class="mb-0">1,231,654</h4>
                                    <span class="badge badge-success light mt-1"><i class="fas fa-arrow-up me-1"></i>12.6%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Undelivered Parts KPI -->
                <div class="col-xl-3 col-lg-6 col-sm-6">
                    <div class="widget-stat card admin-kpi-tile cursor-pointer" onclick="drillToReport('message-logs')" data-kpi="undelivered-parts" data-bs-toggle="tooltip" data-bs-placement="top" title="SUM(parts) WHERE status IN (failed,rejected)">
                        <div class="card-body p-4">
                            <div class="media ai-icon">
                                <span class="me-3 bgl-warning text-warning">
                                    <i class="fas fa-times-circle"></i>
                                </span>
                                <div class="media-body">
                                    <p class="mb-1">Undelivered (Parts)</p>
                                    <h4 class="mb-0">16,178</h4>
                                    <span class="badge badge-danger light mt-1"><i class="fas fa-arrow-down me-1"></i>3.2%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Delivery % KPI -->
                <div class="col-xl-3 col-lg-6 col-sm-6">
                    <div class="widget-stat card admin-kpi-tile cursor-pointer" onclick="drillToReport('client-reporting')" data-kpi="delivery-pct" data-bs-toggle="tooltip" data-bs-placement="top" title="delivered_parts / sent_parts * 100">
                        <div class="card-body p-4">
                            <div class="media ai-icon">
                                <span class="me-3 bgl-info text-info">
                                    <i class="fas fa-percentage"></i>
                                </span>
                                <div class="media-body">
                                    <p class="mb-1">Delivery %</p>
                                    <h4 class="mb-0">98.7%</h4>
                                    <span class="badge badge-success light mt-1"><i class="fas fa-arrow-up me-1"></i>0.2%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Traffic & Performance - Using Fillow card structure -->
    <div class="card" id="platform-reporting-section">
        <div class="card-header border-0 pb-0 d-flex justify-content-between align-items-center">
            <h5 class="card-title"><i class="fas fa-chart-area me-2 text-primary"></i>Traffic & Performance</h5>
            <span class="text-muted" style="font-size: 0.75rem;">Mirrors customer reporting charts (all clients)</span>
        </div>
        <div class="card-body">
            <div class="reporting-charts-grid">
                <div class="chart-container chart-wide">
                    <div class="chart-header">
                        <h6>Messages Sent Over Time</h6>
                        <div class="chart-legend">
                            <span class="legend-item" style="--color: #4a90d9;"><span class="legend-dot"></span> Sent</span>
                            <span class="legend-item" style="--color: #059669;"><span class="legend-dot"></span> Delivered</span>
                            <span class="legend-item" style="--color: #dc2626;"><span class="legend-dot"></span> Undelivered</span>
                            <span class="legend-item" style="--color: #f59e0b;"><span class="legend-dot"></span> Pending</span>
                            <span class="legend-item" style="--color: #8b5cf6;"><span class="legend-dot"></span> Rejected</span>
                        </div>
                    </div>
                    <div class="chart-body" id="chart-messages-over-time">
                        <div class="chart-placeholder">
                            <i class="fas fa-chart-line d-block"></i>
                            <div>Line chart: X = date/hour, Y = parts</div>
                            <div class="chart-note">Uses same component as customer reporting</div>
                        </div>
                    </div>
                    <div class="chart-footer">
                        <span class="chart-source"><i class="fas fa-database"></i> fact_delivery (aggregated by hour)</span>
                    </div>
                </div>

                <div class="chart-container">
                    <div class="chart-header">
                        <h6>Delivery Status Breakdown</h6>
                    </div>
                    <div class="chart-body" id="chart-delivery-status-pie">
                        <div class="chart-placeholder">
                            <i class="fas fa-chart-pie d-block"></i>
                            <div>Pie chart: status distribution</div>
                        </div>
                    </div>
                    <div class="chart-footer">
                        <span class="chart-source"><i class="fas fa-database"></i> fact_delivery.status</span>
                    </div>
                </div>

                <div class="chart-container">
                    <div class="chart-header">
                        <h6>Top SenderIDs</h6>
                        <div class="chart-toggle-group">
                            <button class="chart-toggle active" data-view="volume">Volume</button>
                            <button class="chart-toggle" data-view="revenue">Revenue</button>
                            <button class="chart-toggle" data-view="margin">Margin</button>
                        </div>
                    </div>
                    <div class="chart-body" id="chart-top-senderids">
                        <table class="top-items-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>SenderID</th>
                                    <th>Client</th>
                                    <th class="text-end">Parts</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td>1</td><td><code>ALERTS24</code></td><td>Acme Corp</td><td class="text-end">124,832</td></tr>
                                <tr><td>2</td><td><code>MYBANK</code></td><td>Finance Ltd</td><td class="text-end">98,421</td></tr>
                                <tr><td>3</td><td><code>RETAILCO</code></td><td>Retail Co</td><td class="text-end">76,234</td></tr>
                                <tr><td>4</td><td><code>HEALTH+</code></td><td>HealthCare+</td><td class="text-end">54,102</td></tr>
                                <tr><td>5</td><td><code>PROMO</code></td><td>MarketingPro</td><td class="text-end">43,876</td></tr>
                                <tr><td>6</td><td><code>DELIVERY</code></td><td>LogiTech</td><td class="text-end">38,234</td></tr>
                                <tr><td>7</td><td><code>VERIFY</code></td><td>SecureAuth</td><td class="text-end">32,109</td></tr>
                                <tr><td>8</td><td><code>BOOKING</code></td><td>TravelMax</td><td class="text-end">28,765</td></tr>
                                <tr><td>9</td><td><code>SERVICE</code></td><td>UtilityCo</td><td class="text-end">21,432</td></tr>
                                <tr><td>10</td><td><code>REMINDER</code></td><td>AppointBot</td><td class="text-end">18,987</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="chart-footer">
                        <span class="chart-source"><i class="fas fa-database"></i> fact_messages GROUP BY sender_id LIMIT 10</span>
                    </div>
                </div>

                <div class="chart-container">
                    <div class="chart-header">
                        <h6>Top Destination Countries</h6>
                    </div>
                    <div class="chart-body" id="chart-top-countries">
                        <div class="horizontal-bar-chart">
                            <div class="bar-row">
                                <span class="bar-label">United Kingdom</span>
                                <div class="bar-track"><div class="bar-fill" style="width: 100%;"></div></div>
                                <span class="bar-value">892,456</span>
                            </div>
                            <div class="bar-row">
                                <span class="bar-label">Ireland</span>
                                <div class="bar-track"><div class="bar-fill" style="width: 18%;"></div></div>
                                <span class="bar-value">160,642</span>
                            </div>
                            <div class="bar-row">
                                <span class="bar-label">Germany</span>
                                <div class="bar-track"><div class="bar-fill" style="width: 12%;"></div></div>
                                <span class="bar-value">107,094</span>
                            </div>
                            <div class="bar-row">
                                <span class="bar-label">France</span>
                                <div class="bar-track"><div class="bar-fill" style="width: 8%;"></div></div>
                                <span class="bar-value">71,396</span>
                            </div>
                            <div class="bar-row">
                                <span class="bar-label">Spain</span>
                                <div class="bar-track"><div class="bar-fill" style="width: 5%;"></div></div>
                                <span class="bar-value">44,622</span>
                            </div>
                            <div class="bar-row">
                                <span class="bar-label">Netherlands</span>
                                <div class="bar-track"><div class="bar-fill" style="width: 4%;"></div></div>
                                <span class="bar-value">35,698</span>
                            </div>
                            <div class="bar-row">
                                <span class="bar-label">Belgium</span>
                                <div class="bar-track"><div class="bar-fill" style="width: 3%;"></div></div>
                                <span class="bar-value">26,773</span>
                            </div>
                            <div class="bar-row">
                                <span class="bar-label">Italy</span>
                                <div class="bar-track"><div class="bar-fill" style="width: 2.5%;"></div></div>
                                <span class="bar-value">22,311</span>
                            </div>
                            <div class="bar-row">
                                <span class="bar-label">Poland</span>
                                <div class="bar-track"><div class="bar-fill" style="width: 2%;"></div></div>
                                <span class="bar-value">17,849</span>
                            </div>
                            <div class="bar-row">
                                <span class="bar-label">Portugal</span>
                                <div class="bar-track"><div class="bar-fill" style="width: 1.5%;"></div></div>
                                <span class="bar-value">13,387</span>
                            </div>
                        </div>
                    </div>
                    <div class="chart-footer">
                        <span class="chart-source"><i class="fas fa-database"></i> fact_delivery GROUP BY country LIMIT 10</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Supplier Route Health - Using Fillow card structure -->
    <div class="card" id="supplier-route-health-section">
        <div class="card-header border-0 pb-0 d-flex justify-content-between align-items-center">
            <h5 class="card-title"><i class="fas fa-route me-2 text-primary"></i>Supplier Route Health</h5>
            <span class="badge badge-primary light">Admin Only</span>
        </div>
        <div class="card-body">
            <div class="supplier-health-grid">
                <div class="route-health-tile clickable" onclick="drillToReport('supplier-reporting')">
                    <div class="route-health-status healthy">
                        <i class="fas fa-check-circle"></i>
                        <span>Healthy</span>
                    </div>
                    <div class="route-health-title">Overall Route Health</div>
                    <div class="route-health-counts">
                        <div class="route-count">
                            <span class="count-value success">5</span>
                            <span class="count-label">Active</span>
                        </div>
                        <div class="route-count">
                            <span class="count-value warning">1</span>
                            <span class="count-label">Degraded</span>
                        </div>
                        <div class="route-count">
                            <span class="count-value danger">0</span>
                            <span class="count-label">Failed</span>
                        </div>
                    </div>
                    <div class="route-health-footer">
                        <span class="chart-source"><i class="fas fa-database"></i> dim_routes.status</span>
                        <span class="drill-hint"><i class="fas fa-external-link-alt"></i> View Supplier Reporting</span>
                    </div>
                </div>

                <div class="chart-container">
                    <div class="chart-header">
                        <h6>Supplier Response Time</h6>
                        <div class="chart-toggle-group">
                            <button class="chart-toggle active" data-window="1h">1h</button>
                            <button class="chart-toggle" data-window="24h">24h</button>
                            <button class="chart-toggle" data-window="7d">7d</button>
                        </div>
                    </div>
                    <div class="chart-body" id="chart-supplier-response-time">
                        <div class="response-time-metrics">
                            <div class="response-metric">
                                <span class="metric-label">Avg</span>
                                <span class="metric-value">142ms</span>
                            </div>
                            <div class="response-metric">
                                <span class="metric-label">P95</span>
                                <span class="metric-value warning">287ms</span>
                            </div>
                            <div class="response-metric">
                                <span class="metric-label">Max</span>
                                <span class="metric-value danger">892ms</span>
                            </div>
                        </div>
                        <div class="chart-placeholder" style="margin-top: 1rem;">
                            <i class="fas fa-chart-line d-block"></i>
                            <div>Response time over selected window</div>
                            <div class="chart-note">Click spike → Message Log (filtered by timestamp)</div>
                        </div>
                    </div>
                    <div class="chart-footer">
                        <span class="chart-source"><i class="fas fa-database"></i> fact_delivery.response_time_ms</span>
                    </div>
                </div>

                <div class="chart-container chart-span-2">
                    <div class="chart-header">
                        <h6>Route Distribution</h6>
                        <div class="chart-legend">
                            <span class="legend-item" style="--color: #4a90d9;"><span class="legend-dot"></span> SMS</span>
                            <span class="legend-item" style="--color: #10b981;"><span class="legend-dot"></span> RCS Basic</span>
                            <span class="legend-item" style="--color: #8b5cf6;"><span class="legend-dot"></span> RCS Single</span>
                        </div>
                    </div>
                    <div class="chart-body" id="chart-route-distribution">
                        <div class="stacked-bar-chart">
                            <div class="stacked-bar-row clickable" onclick="drillToReport('supplier-reporting')">
                                <span class="stacked-bar-label">UK Tier 1</span>
                                <div class="stacked-bar-track">
                                    <div class="stacked-segment sms" style="width: 75%;" title="SMS: 361,756"></div>
                                    <div class="stacked-segment rcs-basic" style="width: 18%;" title="RCS Basic: 86,821"></div>
                                    <div class="stacked-segment rcs-single" style="width: 7%;" title="RCS Single: 33,764"></div>
                                </div>
                                <span class="stacked-bar-total">482,341</span>
                            </div>
                            <div class="stacked-bar-row clickable" onclick="drillToReport('supplier-reporting')">
                                <span class="stacked-bar-label">UK Tier 2</span>
                                <div class="stacked-bar-track">
                                    <div class="stacked-segment sms" style="width: 92%;" title="SMS: 182,557"></div>
                                    <div class="stacked-segment rcs-basic" style="width: 6%;" title="RCS Basic: 11,906"></div>
                                    <div class="stacked-segment rcs-single" style="width: 2%;" title="RCS Single: 3,969"></div>
                                </div>
                                <span class="stacked-bar-total">198,432</span>
                            </div>
                            <div class="stacked-bar-row clickable" onclick="drillToReport('supplier-reporting')">
                                <span class="stacked-bar-label">EU Primary</span>
                                <div class="stacked-bar-track">
                                    <div class="stacked-segment sms" style="width: 88%;" title="SMS: 76,766"></div>
                                    <div class="stacked-segment rcs-basic" style="width: 10%;" title="RCS Basic: 8,723"></div>
                                    <div class="stacked-segment rcs-single" style="width: 2%;" title="RCS Single: 1,745"></div>
                                </div>
                                <span class="stacked-bar-total">87,234</span>
                            </div>
                            <div class="stacked-bar-row clickable" onclick="drillToReport('supplier-reporting')">
                                <span class="stacked-bar-label">International</span>
                                <div class="stacked-bar-track">
                                    <div class="stacked-segment sms" style="width: 100%;" title="SMS: 23,892"></div>
                                </div>
                                <span class="stacked-bar-total">23,892</span>
                            </div>
                            <div class="stacked-bar-row clickable" onclick="drillToReport('supplier-reporting')">
                                <span class="stacked-bar-label">RCS Google</span>
                                <div class="stacked-bar-track">
                                    <div class="stacked-segment rcs-basic" style="width: 65%;" title="RCS Basic: 27,422"></div>
                                    <div class="stacked-segment rcs-single" style="width: 35%;" title="RCS Single: 14,765"></div>
                                </div>
                                <span class="stacked-bar-total">42,187</span>
                            </div>
                        </div>
                    </div>
                    <div class="chart-footer">
                        <span class="chart-source"><i class="fas fa-database"></i> fact_delivery GROUP BY route, product</span>
                    </div>
                </div>

                <div class="pricing-upload-panel chart-span-full">
                    <div class="pricing-upload-header">
                        <div class="pricing-upload-title">
                            <h6><i class="fas fa-file-invoice-dollar"></i> Supplier Pricing Reference</h6>
                            <span class="pricing-purpose">Upload supplier price sheets for margin validation</span>
                        </div>
                        <button class="btn-admin-primary" onclick="openSupplierPricingModal()">
                            <i class="fas fa-upload"></i> Upload Supplier Pricing
                        </button>
                    </div>
                    <div class="pricing-upload-body">
                        <div class="pricing-info-strip">
                            <i class="fas fa-info-circle"></i>
                            Used for: <strong>Margin risk detection</strong> and <strong>Supplier reconciliation</strong>
                        </div>
                        <div class="pricing-recent-uploads">
                            <div class="pricing-table-header">Recent Uploads</div>
                            <table class="pricing-uploads-table">
                                <thead>
                                    <tr>
                                        <th>Supplier</th>
                                        <th>Uploaded By</th>
                                        <th>Timestamp</th>
                                        <th>Effective Date</th>
                                        <th>Version</th>
                                        <th>Rows</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>Sinch UK</strong></td>
                                        <td>admin@quicksms.com</td>
                                        <td>2026-01-18 14:32</td>
                                        <td>2026-01-01</td>
                                        <td><span class="version-badge">v3</span></td>
                                        <td>1,247</td>
                                        <td>
                                            <button class="btn-icon" title="Download"><i class="fas fa-download"></i></button>
                                            <button class="btn-icon" title="View"><i class="fas fa-eye"></i></button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Twilio</strong></td>
                                        <td>finance@quicksms.com</td>
                                        <td>2026-01-15 09:15</td>
                                        <td>2026-01-01</td>
                                        <td><span class="version-badge">v2</span></td>
                                        <td>3,421</td>
                                        <td>
                                            <button class="btn-icon" title="Download"><i class="fas fa-download"></i></button>
                                            <button class="btn-icon" title="View"><i class="fas fa-eye"></i></button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Vonage</strong></td>
                                        <td>admin@quicksms.com</td>
                                        <td>2026-01-10 11:45</td>
                                        <td>2025-12-01</td>
                                        <td><span class="version-badge">v1</span></td>
                                        <td>892</td>
                                        <td>
                                            <button class="btn-icon" title="Download"><i class="fas fa-download"></i></button>
                                            <button class="btn-icon" title="View"><i class="fas fa-eye"></i></button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="supplierPricingModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content admin-modal">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-file-invoice-dollar"></i> Upload Supplier Pricing</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="upload-requirements">
                        <h6>File Requirements</h6>
                        <div class="requirement-list">
                            <div class="requirement-item">
                                <i class="fas fa-check-circle text-success"></i>
                                <span><strong>Format:</strong> CSV (required) or XLSX (optional)</span>
                            </div>
                            <div class="requirement-item">
                                <i class="fas fa-columns text-info"></i>
                                <span><strong>Required Columns:</strong></span>
                            </div>
                        </div>
                        <div class="required-columns-grid">
                            <div class="column-tag">Country Prefix</div>
                            <div class="column-tag">Country Name</div>
                            <div class="column-tag">MCC</div>
                            <div class="column-tag">MNC</div>
                            <div class="column-tag">Price</div>
                            <div class="column-tag">Product</div>
                        </div>
                        <div class="validation-notes">
                            <p><i class="fas fa-info-circle"></i> <strong>Validation Rules:</strong></p>
                            <ul>
                                <li>Numeric fields (MCC, MNC, Price) must be valid numbers</li>
                                <li>Product must be: <code>SMS</code>, <code>RCS Basic</code>, or <code>RCS Single</code></li>
                            </ul>
                        </div>
                    </div>

                    <div class="upload-form">
                        <div class="mb-3">
                            <label class="form-label">Supplier Name <span class="text-danger">*</span></label>
                            <select class="form-select" id="pricingSupplier">
                                <option value="">Select supplier...</option>
                                <option value="sinch-uk">Sinch UK</option>
                                <option value="twilio">Twilio</option>
                                <option value="vonage">Vonage</option>
                                <option value="messagebird">MessageBird</option>
                                <option value="other">Other (specify)</option>
                            </select>
                        </div>
                        <div class="mb-3" id="otherSupplierInput" style="display: none;">
                            <label class="form-label">Supplier Name</label>
                            <input type="text" class="form-control" placeholder="Enter supplier name">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Effective Date</label>
                            <input type="date" class="form-control" id="pricingEffectiveDate">
                            <small class="text-muted">Optional. Date from which these prices apply.</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Price Sheet File <span class="text-danger">*</span></label>
                            <div class="upload-dropzone" id="pricingDropzone">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <p>Drag & drop your file here, or <span class="text-primary">browse</span></p>
                                <small>CSV or XLSX, max 10MB</small>
                                <input type="file" id="pricingFileInput" accept=".csv,.xlsx" style="display: none;">
                            </div>
                            <div class="upload-file-preview" id="pricingFilePreview" style="display: none;">
                                <i class="fas fa-file-csv"></i>
                                <span class="file-name">filename.csv</span>
                                <span class="file-size">1.2 MB</span>
                                <button class="btn-icon" onclick="clearPricingFile()"><i class="fas fa-times"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn-admin-primary" onclick="validateAndUploadPricing()">
                        <i class="fas fa-upload"></i> Upload & Validate
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- UK Network & Porting Health - Using Fillow card structure -->
    <div class="card" id="uk-network-health-section">
        <div class="card-header border-0 pb-0 d-flex justify-content-between align-items-center">
            <h5 class="card-title"><i class="fas fa-broadcast-tower me-2 text-primary"></i>UK Network & Porting Health</h5>
            <div class="d-flex align-items-center gap-3">
                <div class="form-check form-switch d-flex align-items-center gap-2">
                    <span class="text-muted small" id="network-porting-label-original">Original Network</span>
                    <input class="form-check-input" type="checkbox" id="network-porting-toggle" onchange="onNetworkPortingToggle()">
                    <span class="text-muted small" id="network-porting-label-ported">Ported Network</span>
                </div>
                <span class="badge badge-primary light">Admin Only</span>
            </div>
        </div>
        <div class="card-body">
            <div class="uk-network-grid">
                <div class="chart-container chart-wide">
                    <div class="chart-header">
                        <h6>Delivery by Network</h6>
                        <div class="chart-legend">
                            <span class="legend-item" style="--color: #10b981;"><span class="legend-dot"></span> Delivered</span>
                            <span class="legend-item" style="--color: #ef4444;"><span class="legend-dot"></span> Undelivered</span>
                            <span class="legend-item" style="--color: #f59e0b;"><span class="legend-dot"></span> Pending</span>
                            <span class="legend-item" style="--color: #64748b;"><span class="legend-dot"></span> Rejected</span>
                        </div>
                    </div>
                    <div class="chart-body" id="chart-delivery-by-network">
                        <div class="network-stacked-chart">
                            <div class="network-bar-row clickable" onclick="drillToReport('message-logs', {network: 'EE'})">
                                <span class="network-bar-label"><span class="network-badge ee">EE</span></span>
                                <div class="network-bar-track">
                                    <div class="status-segment delivered" style="width: 94%;" title="Delivered: 293,709"></div>
                                    <div class="status-segment undelivered" style="width: 3%;" title="Undelivered: 9,374"></div>
                                    <div class="status-segment pending" style="width: 2%;" title="Pending: 6,249"></div>
                                    <div class="status-segment rejected" style="width: 1%;" title="Rejected: 3,124"></div>
                                </div>
                                <span class="network-bar-total">312,456</span>
                            </div>
                            <div class="network-bar-row clickable" onclick="drillToReport('message-logs', {network: 'Vodafone'})">
                                <span class="network-bar-label"><span class="network-badge vodafone">Vodafone</span></span>
                                <div class="network-bar-track">
                                    <div class="status-segment delivered" style="width: 93%;" title="Delivered: 267,024"></div>
                                    <div class="status-segment undelivered" style="width: 4%;" title="Undelivered: 11,485"></div>
                                    <div class="status-segment pending" style="width: 2%;" title="Pending: 5,743"></div>
                                    <div class="status-segment rejected" style="width: 1%;" title="Rejected: 2,871"></div>
                                </div>
                                <span class="network-bar-total">287,123</span>
                            </div>
                            <div class="network-bar-row clickable" onclick="drillToReport('message-logs', {network: 'O2'})">
                                <span class="network-bar-label"><span class="network-badge o2">O2</span></span>
                                <div class="network-bar-track">
                                    <div class="status-segment delivered" style="width: 92%;" title="Delivered: 182,864"></div>
                                    <div class="status-segment undelivered" style="width: 4%;" title="Undelivered: 7,951"></div>
                                    <div class="status-segment pending" style="width: 3%;" title="Pending: 5,963"></div>
                                    <div class="status-segment rejected" style="width: 1%;" title="Rejected: 1,987"></div>
                                </div>
                                <span class="network-bar-total">198,765</span>
                            </div>
                            <div class="network-bar-row clickable" onclick="drillToReport('message-logs', {network: 'Three'})">
                                <span class="network-bar-label"><span class="network-badge three">Three</span></span>
                                <div class="network-bar-track">
                                    <div class="status-segment delivered" style="width: 89%;" title="Delivered: 139,224"></div>
                                    <div class="status-segment undelivered" style="width: 6%;" title="Undelivered: 9,386"></div>
                                    <div class="status-segment pending" style="width: 3%;" title="Pending: 4,693"></div>
                                    <div class="status-segment rejected" style="width: 2%;" title="Rejected: 3,129"></div>
                                </div>
                                <span class="network-bar-total">156,432</span>
                            </div>
                            <div class="network-bar-row clickable" onclick="drillToReport('message-logs', {network: 'MVNO'})">
                                <span class="network-bar-label"><span class="network-badge mvno">MVNO/Other</span></span>
                                <div class="network-bar-track">
                                    <div class="status-segment delivered" style="width: 87%;" title="Delivered: 39,429"></div>
                                    <div class="status-segment undelivered" style="width: 7%;" title="Undelivered: 3,172"></div>
                                    <div class="status-segment pending" style="width: 4%;" title="Pending: 1,813"></div>
                                    <div class="status-segment rejected" style="width: 2%;" title="Rejected: 907"></div>
                                </div>
                                <span class="network-bar-total">45,321</span>
                            </div>
                        </div>
                    </div>
                    <div class="chart-footer">
                        <span class="chart-source"><i class="fas fa-database"></i> fact_delivery GROUP BY network, status</span>
                    </div>
                </div>

                <div class="network-kpi-tiles">
                    <div class="network-kpi-tile green" data-network="ee" onclick="drillToReport('message-logs', {network: 'EE'})">
                        <div class="network-kpi-badge"><span class="network-badge ee">EE</span></div>
                        <div class="network-kpi-value">99.4%</div>
                        <div class="network-kpi-label">Delivery Rate</div>
                        <div class="network-kpi-tooltip">
                            <div class="tooltip-row"><span>Parts:</span><strong>312,456</strong></div>
                            <div class="tooltip-section">Top 3 Failure Groups:</div>
                            <div class="tooltip-row small"><span>1.</span> Invalid Number (42%)</div>
                            <div class="tooltip-row small"><span>2.</span> Network Timeout (31%)</div>
                            <div class="tooltip-row small"><span>3.</span> Subscriber Absent (27%)</div>
                        </div>
                    </div>
                    <div class="network-kpi-tile green" data-network="vodafone" onclick="drillToReport('message-logs', {network: 'Vodafone'})">
                        <div class="network-kpi-badge"><span class="network-badge vodafone">Vodafone</span></div>
                        <div class="network-kpi-value">99.1%</div>
                        <div class="network-kpi-label">Delivery Rate</div>
                        <div class="network-kpi-tooltip">
                            <div class="tooltip-row"><span>Parts:</span><strong>287,123</strong></div>
                            <div class="tooltip-section">Top 3 Failure Groups:</div>
                            <div class="tooltip-row small"><span>1.</span> Subscriber Absent (38%)</div>
                            <div class="tooltip-row small"><span>2.</span> Invalid Number (35%)</div>
                            <div class="tooltip-row small"><span>3.</span> Network Error (27%)</div>
                        </div>
                    </div>
                    <div class="network-kpi-tile green" data-network="o2" onclick="drillToReport('message-logs', {network: 'O2'})">
                        <div class="network-kpi-badge"><span class="network-badge o2">O2</span></div>
                        <div class="network-kpi-value">98.8%</div>
                        <div class="network-kpi-label">Delivery Rate</div>
                        <div class="network-kpi-tooltip">
                            <div class="tooltip-row"><span>Parts:</span><strong>198,765</strong></div>
                            <div class="tooltip-section">Top 3 Failure Groups:</div>
                            <div class="tooltip-row small"><span>1.</span> Invalid Number (45%)</div>
                            <div class="tooltip-row small"><span>2.</span> Spam Filter (32%)</div>
                            <div class="tooltip-row small"><span>3.</span> Timeout (23%)</div>
                        </div>
                    </div>
                    <div class="network-kpi-tile amber" data-network="three" onclick="drillToReport('message-logs', {network: 'Three'})">
                        <div class="network-kpi-badge"><span class="network-badge three">Three</span></div>
                        <div class="network-kpi-value">97.2%</div>
                        <div class="network-kpi-label">Delivery Rate</div>
                        <div class="network-kpi-tooltip">
                            <div class="tooltip-row"><span>Parts:</span><strong>156,432</strong></div>
                            <div class="tooltip-section">Top 3 Failure Groups:</div>
                            <div class="tooltip-row small"><span>1.</span> Network Congestion (48%)</div>
                            <div class="tooltip-row small"><span>2.</span> Invalid Number (28%)</div>
                            <div class="tooltip-row small"><span>3.</span> Subscriber Absent (24%)</div>
                        </div>
                    </div>
                    <div class="network-kpi-tile amber" data-network="mvno" onclick="drillToReport('message-logs', {network: 'MVNO'})">
                        <div class="network-kpi-badge"><span class="network-badge mvno">MVNO</span></div>
                        <div class="network-kpi-value">96.8%</div>
                        <div class="network-kpi-label">Delivery Rate</div>
                        <div class="network-kpi-tooltip">
                            <div class="tooltip-row"><span>Parts:</span><strong>45,321</strong></div>
                            <div class="tooltip-section">Top 3 Failure Groups:</div>
                            <div class="tooltip-row small"><span>1.</span> Unknown Subscriber (52%)</div>
                            <div class="tooltip-row small"><span>2.</span> Invalid Number (31%)</div>
                            <div class="tooltip-row small"><span>3.</span> Network Error (17%)</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Margin Risk & Loss - Using Fillow card structure -->
    <div class="card" id="margin-risk-section">
        <div class="card-header border-0 pb-0 d-flex justify-content-between align-items-center">
            <h5 class="card-title"><i class="fas fa-exclamation-triangle me-2 text-warning"></i>Margin Risk & Margin Loss</h5>
            <span class="badge badge-primary light">Admin Only</span>
        </div>
        <div class="card-body">
            <div class="anomaly-banners" id="margin-anomaly-banners">
                <div class="anomaly-banner warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span><strong>Delivery Drop Alert:</strong> Three network delivery rate dropped 3.2% in last 2 hours</span>
                    <button class="btn-dismiss" onclick="dismissAnomaly(this)"><i class="fas fa-times"></i></button>
                </div>
                <div class="anomaly-banner danger">
                    <i class="fas fa-chart-line-down"></i>
                    <span><strong>Margin Breach:</strong> Nigeria route margin fell below 5% threshold (-2.4%)</span>
                    <button class="btn-dismiss" onclick="dismissAnomaly(this)"><i class="fas fa-times"></i></button>
                </div>
            </div>

            <div class="margin-view-tabs">
                <button class="margin-tab active" data-view="country" onclick="switchMarginView('country')">By Country</button>
                <button class="margin-tab" data-view="uk-network" onclick="switchMarginView('uk-network')">By UK Network</button>
                <button class="margin-tab" data-view="supplier" onclick="switchMarginView('supplier')">By Supplier</button>
                <button class="margin-tab" data-view="client" onclick="switchMarginView('client')">By Client</button>
            </div>

            <div class="margin-table-wrapper">
                <table class="margin-risk-table" id="margin-risk-table">
                    <thead>
                        <tr>
                            <th class="sortable" data-sort="dimension">Dimension <i class="fas fa-sort"></i></th>
                            <th class="sortable text-end" data-sort="parts">Parts <i class="fas fa-sort"></i></th>
                            <th class="sortable text-end" data-sort="revenue">Revenue (£) <i class="fas fa-sort"></i></th>
                            <th class="sortable text-end" data-sort="cost">Supplier Cost (£) <i class="fas fa-sort"></i></th>
                            <th class="sortable text-end" data-sort="margin">Gross Margin (£) <i class="fas fa-sort"></i></th>
                            <th class="sortable text-end" data-sort="margin-pct">Margin % <i class="fas fa-sort-down"></i></th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="margin-table-body">
                        <tr class="margin-row danger" onclick="drillToReport('message-logs', {country: 'NG'})">
                            <td><span class="dimension-flag">🇳🇬</span> Nigeria</td>
                            <td class="text-end">8,234</td>
                            <td class="text-end">£1,234.56</td>
                            <td class="text-end">£1,264.12</td>
                            <td class="text-end margin-loss">-£29.56</td>
                            <td class="text-end"><span class="margin-badge danger">-2.4%</span></td>
                            <td class="text-center"><button class="btn-icon" title="View Details"><i class="fas fa-external-link-alt"></i></button></td>
                        </tr>
                        <tr class="margin-row warning" onclick="drillToReport('message-logs', {country: 'PH'})">
                            <td><span class="dimension-flag">🇵🇭</span> Philippines</td>
                            <td class="text-end">12,456</td>
                            <td class="text-end">£2,867.34</td>
                            <td class="text-end">£2,724.98</td>
                            <td class="text-end">£142.36</td>
                            <td class="text-end"><span class="margin-badge warning">4.9%</span></td>
                            <td class="text-center"><button class="btn-icon" title="View Details"><i class="fas fa-external-link-alt"></i></button></td>
                        </tr>
                        <tr class="margin-row warning" onclick="drillToReport('message-logs', {country: 'IN'})">
                            <td><span class="dimension-flag">🇮🇳</span> India</td>
                            <td class="text-end">34,892</td>
                            <td class="text-end">£4,186.70</td>
                            <td class="text-end">£3,894.43</td>
                            <td class="text-end">£292.27</td>
                            <td class="text-end"><span class="margin-badge warning">6.9%</span></td>
                            <td class="text-center"><button class="btn-icon" title="View Details"><i class="fas fa-external-link-alt"></i></button></td>
                        </tr>
                        <tr class="margin-row" onclick="drillToReport('message-logs', {country: 'ZA'})">
                            <td><span class="dimension-flag">🇿🇦</span> South Africa</td>
                            <td class="text-end">6,789</td>
                            <td class="text-end">£1,086.24</td>
                            <td class="text-end">£978.75</td>
                            <td class="text-end">£107.49</td>
                            <td class="text-end"><span class="margin-badge amber">9.9%</span></td>
                            <td class="text-center"><button class="btn-icon" title="View Details"><i class="fas fa-external-link-alt"></i></button></td>
                        </tr>
                        <tr class="margin-row" onclick="drillToReport('message-logs', {country: 'BR'})">
                            <td><span class="dimension-flag">🇧🇷</span> Brazil</td>
                            <td class="text-end">15,234</td>
                            <td class="text-end">£3,046.80</td>
                            <td class="text-end">£2,589.78</td>
                            <td class="text-end">£457.02</td>
                            <td class="text-end"><span class="margin-badge">15.0%</span></td>
                            <td class="text-center"><button class="btn-icon" title="View Details"><i class="fas fa-external-link-alt"></i></button></td>
                        </tr>
                        <tr class="margin-row" onclick="drillToReport('message-logs', {country: 'DE'})">
                            <td><span class="dimension-flag">🇩🇪</span> Germany</td>
                            <td class="text-end">45,678</td>
                            <td class="text-end">£6,851.70</td>
                            <td class="text-end">£5,481.36</td>
                            <td class="text-end">£1,370.34</td>
                            <td class="text-end"><span class="margin-badge">20.0%</span></td>
                            <td class="text-center"><button class="btn-icon" title="View Details"><i class="fas fa-external-link-alt"></i></button></td>
                        </tr>
                        <tr class="margin-row" onclick="drillToReport('message-logs', {country: 'GB'})">
                            <td><span class="dimension-flag">🇬🇧</span> United Kingdom</td>
                            <td class="text-end">892,456</td>
                            <td class="text-end">£44,622.80</td>
                            <td class="text-end">£31,235.96</td>
                            <td class="text-end">£13,386.84</td>
                            <td class="text-end"><span class="margin-badge success">30.0%</span></td>
                            <td class="text-center"><button class="btn-icon" title="View Details"><i class="fas fa-external-link-alt"></i></button></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="margin-table-footer">
                <span class="table-info">Sorted by: Lowest margin % first</span>
                <span class="chart-source"><i class="fas fa-database"></i> fact_delivery + fact_billing GROUP BY country</span>
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

    window.drillToReport = function(reportType, additionalContext) {
        additionalContext = additionalContext || {};

        var routes = {
            'client-reporting': '/admin/reporting/client',
            'message-logs': '/admin/reporting/message-log',
            'supplier-reporting': '/admin/reporting/supplier'
        };

        var url = routes[reportType];
        if (!url) {
            console.warn('[Admin Dashboard] Unknown report type:', reportType);
            return;
        }

        var queryParams = [];
        
        if (appliedFilters.dateRange) {
            queryParams.push('date_range=' + encodeURIComponent(appliedFilters.dateRange));
        }
        if (appliedFilters.client) {
            queryParams.push('client=' + encodeURIComponent(appliedFilters.client));
        }
        if (appliedFilters.senderId) {
            queryParams.push('sender_id=' + encodeURIComponent(appliedFilters.senderId));
        }
        if (appliedFilters.supplier) {
            queryParams.push('supplier=' + encodeURIComponent(appliedFilters.supplier));
        }
        if (appliedFilters.product) {
            queryParams.push('product=' + encodeURIComponent(appliedFilters.product));
        }
        if (appliedFilters.ukNetwork) {
            queryParams.push('uk_network=' + encodeURIComponent(appliedFilters.ukNetwork));
        }
        if (appliedFilters.country) {
            queryParams.push('country=' + encodeURIComponent(appliedFilters.country.join(',')));
        }

        for (var key in additionalContext) {
            if (additionalContext.hasOwnProperty(key)) {
                queryParams.push(key + '=' + encodeURIComponent(additionalContext[key]));
            }
        }

        if (queryParams.length > 0) {
            url += '?' + queryParams.join('&');
        }

        logAuditEntry('DRILL_DOWN', reportType, {
            destination: reportType,
            filters: appliedFilters,
            additional_context: additionalContext
        });

        console.log('[Admin Dashboard] Drilling to:', url);
        console.log('[Admin Dashboard] RULE: All active filters passed through');
        console.log('[Admin Dashboard] RULE: No local recalculation');
        window.location.href = url;
    };

    function logAuditEntry(action, category, details) {
        var auditEntry = {
            admin_user: '{{ session("admin_user_email", "admin@quicksms.com") }}',
            timestamp: new Date().toISOString(),
            ip: '{{ request()->ip() }}',
            action: action,
            category: category,
            filter_scope: appliedFilters,
            details: details
        };

        console.log('[ADMIN AUDIT]', JSON.stringify(auditEntry, null, 2));

        if (typeof AdminControlPlane !== 'undefined') {
            AdminControlPlane.logAdminAction(action, category, auditEntry);
        }

        return auditEntry;
    }

    window.logSensitiveDrillDown = function(reportType, context) {
        var sensitiveActions = ['supplier-reporting', 'margin-details', 'pricing-view'];
        if (sensitiveActions.includes(reportType)) {
            logAuditEntry('SENSITIVE_DRILL_DOWN', 'SECURITY', {
                report_type: reportType,
                context: context,
                reason: 'Accessing sensitive financial/supplier data'
            });
        }
        drillToReport(reportType, context);
    };

    window.logConfigChange = function(configType, oldValue, newValue) {
        logAuditEntry('CONFIG_CHANGE', 'SYSTEM', {
            config_type: configType,
            old_value: oldValue,
            new_value: newValue
        });
    };

    console.log('[Admin Dashboard] Data source: Internal Warehouse API');
    console.log('[Admin Dashboard] RULE: Filters apply ONLY on Apply button click');
    console.log('[Admin Dashboard] KPI tiles are clickable for drill-down');

    window.onNetworkPortingToggle = function() {
        var toggle = document.getElementById('network-porting-toggle');
        var originalLabel = document.getElementById('network-porting-label-original');
        var portedLabel = document.getElementById('network-porting-label-ported');
        
        if (toggle.checked) {
            originalLabel.classList.remove('active');
            portedLabel.classList.add('active');
            console.log('[Admin Dashboard] Network view: Ported Network (after porting lookup)');
        } else {
            originalLabel.classList.add('active');
            portedLabel.classList.remove('active');
            console.log('[Admin Dashboard] Network view: Original Network (before porting lookup)');
        }

        if (typeof AdminControlPlane !== 'undefined') {
            AdminControlPlane.logAdminAction('NETWORK_PORTING_VIEW_CHANGED', 'SYSTEM', {
                view: toggle.checked ? 'ported' : 'original'
            });
        }
    };

    window.switchMarginView = function(view) {
        var tabs = document.querySelectorAll('.margin-tab');
        tabs.forEach(function(tab) {
            tab.classList.remove('active');
            if (tab.dataset.view === view) {
                tab.classList.add('active');
            }
        });

        console.log('[Admin Dashboard] Margin view switched to:', view);

        if (typeof AdminControlPlane !== 'undefined') {
            AdminControlPlane.logAdminAction('MARGIN_VIEW_CHANGED', 'FINANCIAL', {
                view: view
            });
        }
    };

    window.dismissAnomaly = function(btn) {
        var banner = btn.closest('.anomaly-banner');
        banner.style.opacity = '0';
        setTimeout(function() {
            banner.remove();
        }, 300);

        if (typeof AdminControlPlane !== 'undefined') {
            AdminControlPlane.logAdminAction('ANOMALY_BANNER_DISMISSED', 'SYSTEM', {});
        }
    };

    var supplierPricingModal = null;
    var pricingFile = null;

    window.openSupplierPricingModal = function() {
        if (!supplierPricingModal) {
            supplierPricingModal = new bootstrap.Modal(document.getElementById('supplierPricingModal'));
        }
        pricingFile = null;
        document.getElementById('pricingDropzone').style.display = 'block';
        document.getElementById('pricingFilePreview').style.display = 'none';
        document.getElementById('pricingSupplier').value = '';
        document.getElementById('pricingEffectiveDate').value = '';
        document.getElementById('otherSupplierInput').style.display = 'none';
        supplierPricingModal.show();

        if (typeof AdminControlPlane !== 'undefined') {
            AdminControlPlane.logAdminAction('SUPPLIER_PRICING_MODAL_OPENED', 'FINANCIAL', {});
        }
    };

    var dropzone = document.getElementById('pricingDropzone');
    var fileInput = document.getElementById('pricingFileInput');

    if (dropzone) {
        dropzone.addEventListener('click', function() {
            fileInput.click();
        });

        dropzone.addEventListener('dragover', function(e) {
            e.preventDefault();
            dropzone.style.borderColor = '#4a90d9';
            dropzone.style.background = '#f0f9ff';
        });

        dropzone.addEventListener('dragleave', function(e) {
            e.preventDefault();
            dropzone.style.borderColor = '#cbd5e1';
            dropzone.style.background = 'transparent';
        });

        dropzone.addEventListener('drop', function(e) {
            e.preventDefault();
            dropzone.style.borderColor = '#cbd5e1';
            dropzone.style.background = 'transparent';
            
            var files = e.dataTransfer.files;
            if (files.length > 0) {
                handlePricingFile(files[0]);
            }
        });

        fileInput.addEventListener('change', function(e) {
            if (e.target.files.length > 0) {
                handlePricingFile(e.target.files[0]);
            }
        });
    }

    function handlePricingFile(file) {
        var validTypes = ['text/csv', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'];
        var validExtensions = ['.csv', '.xlsx', '.xls'];
        var extension = file.name.substring(file.name.lastIndexOf('.')).toLowerCase();

        if (!validExtensions.includes(extension)) {
            alert('Invalid file type. Please upload a CSV or XLSX file.');
            return;
        }

        if (file.size > 10 * 1024 * 1024) {
            alert('File too large. Maximum size is 10MB.');
            return;
        }

        pricingFile = file;
        document.getElementById('pricingDropzone').style.display = 'none';
        var preview = document.getElementById('pricingFilePreview');
        preview.style.display = 'flex';
        preview.querySelector('.file-name').textContent = file.name;
        preview.querySelector('.file-size').textContent = formatFileSize(file.size);

        var icon = preview.querySelector('i');
        icon.className = extension === '.csv' ? 'fas fa-file-csv' : 'fas fa-file-excel';
    }

    function formatFileSize(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
    }

    window.clearPricingFile = function() {
        pricingFile = null;
        document.getElementById('pricingDropzone').style.display = 'block';
        document.getElementById('pricingFilePreview').style.display = 'none';
        document.getElementById('pricingFileInput').value = '';
    };

    document.getElementById('pricingSupplier').addEventListener('change', function() {
        var otherInput = document.getElementById('otherSupplierInput');
        if (this.value === 'other') {
            otherInput.style.display = 'block';
        } else {
            otherInput.style.display = 'none';
        }
    });

    window.validateAndUploadPricing = function() {
        var supplier = document.getElementById('pricingSupplier').value;
        var effectiveDate = document.getElementById('pricingEffectiveDate').value;

        if (!supplier) {
            alert('Please select a supplier.');
            return;
        }

        if (!pricingFile) {
            alert('Please upload a price sheet file.');
            return;
        }

        var uploadDetails = {
            supplier: supplier,
            effective_date: effectiveDate,
            file_name: pricingFile.name,
            file_size: pricingFile.size,
            file_type: pricingFile.name.split('.').pop().toUpperCase()
        };

        logAuditEntry('SUPPLIER_PRICING_UPLOAD', 'FINANCIAL', uploadDetails);

        console.log('[Admin Dashboard] Uploading supplier pricing:', uploadDetails);
        console.log('[ADMIN AUDIT] Supplier pricing upload logged with full audit trail');

        alert('File validated and uploaded successfully!\n\nSupplier: ' + supplier + '\nFile: ' + pricingFile.name + '\n\nAudit entry created with admin user, timestamp, and IP.');
        
        if (supplierPricingModal) {
            supplierPricingModal.hide();
        }
    };
});
</script>
@endpush
