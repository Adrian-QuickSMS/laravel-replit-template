@extends('layouts.admin')

@section('title', 'Country Controls')

@push('styles')
<style>
.country-controls-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}
.country-controls-title h4 {
    margin: 0;
    font-weight: 600;
    color: #1e3a5f;
}
.country-controls-title p {
    margin: 0.25rem 0 0 0;
    font-size: 0.85rem;
    color: #6c757d;
}
.admin-tabs {
    border-bottom: 2px solid #e9ecef;
    margin-bottom: 1.5rem;
}
.admin-tabs .nav-link {
    color: #6c757d;
    border: none;
    border-bottom: 2px solid transparent;
    margin-bottom: -2px;
    padding: 0.75rem 1.25rem;
    font-weight: 500;
    font-size: 0.9rem;
    transition: all 0.15s;
}
.admin-tabs .nav-link:hover {
    color: #1e3a5f;
    border-bottom-color: rgba(30, 58, 95, 0.3);
}
.admin-tabs .nav-link.active {
    color: #1e3a5f;
    border-bottom-color: #1e3a5f;
    background: transparent;
}
.admin-tabs .nav-link .badge {
    font-size: 0.65rem;
    padding: 0.2rem 0.4rem;
    margin-left: 0.5rem;
    vertical-align: middle;
}
.admin-tabs .nav-link .badge.pending-badge {
    background: #ecc94b;
    color: #744210;
}
.admin-internal-badge {
    font-size: 0.6rem;
    padding: 0.15rem 0.4rem;
    background: rgba(30, 58, 95, 0.15);
    color: #1e3a5f;
    border-radius: 0.2rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-left: 0.5rem;
}
.enforcement-banner {
    background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%);
    border-radius: 0.5rem;
    padding: 1rem 1.25rem;
    margin-bottom: 1.5rem;
    color: #fff;
}
.enforcement-banner h6 {
    margin: 0 0 0.5rem 0;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.enforcement-banner p {
    margin: 0;
    font-size: 0.8rem;
    opacity: 0.9;
}
.enforcement-points {
    display: flex;
    gap: 2rem;
    margin-top: 0.75rem;
    flex-wrap: wrap;
}
.enforcement-point {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.8rem;
}
.enforcement-point i {
    color: #48bb78;
}
.country-stats {
    display: flex;
    gap: 1rem;
    margin-bottom: 1.5rem;
}
.country-stat-card {
    flex: 1;
    background: #fff;
    border-radius: 0.5rem;
    padding: 1rem 1.25rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    border-left: 3px solid;
}
.country-stat-card.allowed {
    border-left-color: #48bb78;
}
.country-stat-card.blocked {
    border-left-color: #e53e3e;
}
.country-stat-card.pending {
    border-left-color: #ecc94b;
}
.country-stat-card.restricted {
    border-left-color: #ed8936;
}
.country-stat-value {
    font-size: 1.75rem;
    font-weight: 700;
    color: #1e3a5f;
}
.country-stat-label {
    font-size: 0.75rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.country-table-card {
    background: #fff;
    border-radius: 0.5rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    margin-bottom: 1.5rem;
}
.country-table-header {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.country-table-header h6 {
    margin: 0;
    font-weight: 600;
    color: #374151;
}
.country-search-box {
    position: relative;
    width: 280px;
}
.country-search-box input {
    padding-left: 2.25rem;
    font-size: 0.85rem;
}
.country-search-box i {
    position: absolute;
    left: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
}
.country-table {
    width: 100%;
    margin: 0;
}
.country-table th {
    padding: 0.5rem 0.75rem;
    font-size: 0.75rem;
    font-weight: 600;
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #6c757d;
}
.country-table td {
    padding: 0.65rem 0.75rem;
    font-size: 0.85rem;
    border-bottom: 1px solid #f1f3f5;
    vertical-align: middle;
}
.country-table tr:hover {
    background: #f8fafc;
}
.country-flag {
    width: 24px;
    height: 16px;
    border-radius: 2px;
    margin-right: 0.5rem;
    object-fit: cover;
    border: 1px solid #e9ecef;
}
.country-name {
    font-weight: 500;
    color: #374151;
}
.country-code {
    font-size: 0.75rem;
    color: #9ca3af;
    margin-left: 0.5rem;
}
.status-badge {
    font-size: 0.7rem;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-weight: 600;
}
.status-badge.allowed {
    background: rgba(72, 187, 120, 0.15);
    color: #22543d;
}
.status-badge.blocked {
    background: rgba(229, 62, 62, 0.15);
    color: #c53030;
}
.status-badge.restricted {
    background: rgba(237, 137, 54, 0.15);
    color: #c05621;
}
.status-badge.pending {
    background: rgba(236, 201, 75, 0.15);
    color: #975a16;
}
.risk-indicator {
    display: flex;
    align-items: center;
    gap: 0.25rem;
}
.risk-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
}
.risk-dot.low { background: #48bb78; }
.risk-dot.medium { background: #ecc94b; }
.risk-dot.high { background: #ed8936; }
.risk-dot.critical { background: #e53e3e; }
.action-btn-group {
    display: flex;
    gap: 0.25rem;
}
.action-btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.7rem;
    border-radius: 0.25rem;
    border: none;
    cursor: pointer;
    transition: all 0.15s;
}
.action-btn.allow {
    background: rgba(72, 187, 120, 0.15);
    color: #22543d;
}
.action-btn.allow:hover {
    background: #48bb78;
    color: #fff;
}
.action-btn.block {
    background: rgba(229, 62, 62, 0.15);
    color: #c53030;
}
.action-btn.block:hover {
    background: #e53e3e;
    color: #fff;
}
.action-btn.restrict {
    background: rgba(237, 137, 54, 0.15);
    color: #c05621;
}
.action-btn.restrict:hover {
    background: #ed8936;
    color: #fff;
}
.customer-override-badge {
    font-size: 0.65rem;
    padding: 0.15rem 0.35rem;
    background: rgba(30, 58, 95, 0.1);
    color: #1e3a5f;
    border-radius: 0.2rem;
    margin-left: 0.5rem;
}
.enforcement-sync-indicator {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.75rem;
    color: #48bb78;
}
.enforcement-sync-indicator.syncing {
    color: #ecc94b;
}
.enforcement-sync-indicator i {
    animation: none;
}
.enforcement-sync-indicator.syncing i {
    animation: spin 1s linear infinite;
}
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
.bulk-actions {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}
.bulk-actions select {
    font-size: 0.8rem;
    padding: 0.35rem 0.75rem;
}
.audit-preview {
    background: #f8f9fa;
    border-radius: 0.375rem;
    padding: 0.75rem 1rem;
    font-size: 0.75rem;
    margin-top: 1rem;
    border: 1px dashed #dee2e6;
}
.audit-preview-title {
    font-weight: 600;
    color: #6c757d;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.audit-preview-content {
    font-family: monospace;
    font-size: 0.7rem;
    color: #495057;
    white-space: pre-wrap;
}
.admin-btn-primary {
    background: #1e3a5f;
    border-color: #1e3a5f;
    color: #fff;
}
.admin-btn-primary:hover {
    background: #2c5282;
    border-color: #2c5282;
    color: #fff;
}
.request-card {
    background: #fff;
    border-radius: 0.5rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    margin-bottom: 1rem;
    border-left: 3px solid #ecc94b;
}
.request-card.approved {
    border-left-color: #48bb78;
}
.request-card.rejected {
    border-left-color: #e53e3e;
}
.request-card-header {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #f1f3f5;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.request-card-body {
    padding: 1rem 1.25rem;
}
.request-customer {
    font-weight: 600;
    color: #374151;
    font-size: 0.9rem;
}
.request-customer-id {
    font-size: 0.75rem;
    color: #9ca3af;
    margin-left: 0.5rem;
}
.request-meta {
    font-size: 0.75rem;
    color: #6c757d;
}
.request-details {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}
.request-detail-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}
.request-detail-label {
    font-size: 0.7rem;
    color: #9ca3af;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.request-detail-value {
    font-size: 0.85rem;
    color: #374151;
    font-weight: 500;
}
.request-actions {
    display: flex;
    gap: 0.5rem;
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #f1f3f5;
}
.empty-state {
    text-align: center;
    padding: 3rem 2rem;
    color: #9ca3af;
}
.empty-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}
.empty-state h6 {
    color: #6c757d;
    margin-bottom: 0.5rem;
}
.review-filters {
    display: flex;
    gap: 1rem;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
}
.review-filters select {
    font-size: 0.85rem;
    min-width: 150px;
}
.queue-stats-row {
    display: flex;
    gap: 1rem;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
}
.queue-stat-card {
    background: #fff;
    border: 1px solid #e5e9f2;
    border-radius: 8px;
    padding: 1rem 1.5rem;
    min-width: 140px;
    cursor: pointer;
    transition: all 0.2s;
    text-align: center;
}
.queue-stat-card:hover {
    border-color: #1e3a5f;
    box-shadow: 0 2px 8px rgba(30, 58, 95, 0.1);
}
.queue-stat-card.active {
    border-color: #1e3a5f;
    background: rgba(30, 58, 95, 0.05);
}
.queue-stat-card .stat-count {
    font-size: 1.75rem;
    font-weight: 700;
    line-height: 1.2;
}
.queue-stat-card .stat-label {
    font-size: 0.75rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-top: 0.25rem;
}
.queue-stat-card.awaiting .stat-count { color: #f59e0b; }
.queue-stat-card.approved .stat-count { color: #059669; }
.queue-stat-card.rejected .stat-count { color: #dc2626; }
.queue-stat-card.total .stat-count { color: #1e3a5f; }
.filter-panel {
    background: #f8f9fc;
    border: 1px solid #e5e9f2;
    border-radius: 8px;
    padding: 1rem 1.25rem;
    margin-bottom: 1.5rem;
}
.filter-row {
    display: flex;
    gap: 1rem;
    align-items: flex-end;
    flex-wrap: wrap;
}
.filter-group {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    min-width: 150px;
}
.filter-group label {
    font-size: 0.75rem;
    font-weight: 600;
    color: #495057;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.filter-actions {
    display: flex;
    gap: 0.5rem;
    margin-left: auto;
}
.queue-table-container {
    background: #fff;
    border: 1px solid #e5e9f2;
    border-radius: 8px;
    overflow: hidden;
}
.queue-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.875rem;
}
.queue-table thead {
    background: #f8f9fc;
    border-bottom: 2px solid #e5e9f2;
}
.queue-table th {
    padding: 0.875rem 1rem;
    text-align: left;
    font-weight: 600;
    color: #1e3a5f;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    white-space: nowrap;
}
.queue-table td {
    padding: 0.875rem 1rem;
    border-bottom: 1px solid #f0f0f0;
    vertical-align: middle;
}
.queue-table tbody tr {
    cursor: pointer;
    transition: background 0.15s, box-shadow 0.15s;
}
.queue-table tbody tr:hover {
    background: #f8f9fc;
    box-shadow: inset 3px 0 0 #1e3a5f;
}
.queue-table tbody tr.high-risk {
    background: rgba(220, 38, 38, 0.03);
}
.queue-table tbody tr.high-risk:hover {
    background: rgba(220, 38, 38, 0.06);
    box-shadow: inset 3px 0 0 #dc2626;
}
.type-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.25rem 0.625rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 600;
}
.type-badge.country-enable {
    background: #dbeafe;
    color: #1e40af;
}
.type-badge.country-disable {
    background: #fee2e2;
    color: #991b1b;
}
.account-cell {
    display: flex;
    flex-direction: column;
    gap: 0.125rem;
}
.account-name {
    font-weight: 500;
    color: #1e3a5f;
}
.account-id {
    font-size: 0.75rem;
    color: #6c757d;
}
.status-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.25rem 0.625rem;
    border-radius: 50px;
    font-size: 0.75rem;
    font-weight: 500;
    white-space: nowrap;
}
.status-pill.pending {
    background: #fef3c7;
    color: #92400e;
}
.status-pill.approved {
    background: #d9f99d;
    color: #3f6212;
}
.status-pill.rejected {
    background: #fecaca;
    color: #7f1d1d;
}
.status-pill.account-live {
    background: #d1fae5;
    color: #065f46;
}
.status-pill.account-test {
    background: #dbeafe;
    color: #1e40af;
}
.status-pill.account-suspended {
    background: #fee2e2;
    color: #991b1b;
}
.btn-review {
    background: #1e3a5f;
    color: #fff;
    border: none;
    padding: 0.375rem 0.75rem;
    border-radius: 6px;
    font-size: 0.8rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}
.btn-review:hover {
    background: #2d5a87;
    color: #fff;
}
.account-link {
    color: #1e3a5f;
    text-decoration: none;
    font-weight: 500;
}
.account-link:hover {
    text-decoration: underline;
    color: #2d5a87;
}
.review-panel-content {
    padding: 0;
}
.review-section {
    border-bottom: 1px solid #e5e9f2;
}
.review-section:last-child {
    border-bottom: none;
}
.review-section-header {
    background: #f8f9fc;
    padding: 0.875rem 1.25rem;
    font-weight: 600;
    font-size: 0.9rem;
    color: #1e3a5f;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    border-bottom: 1px solid #e5e9f2;
}
.review-section-header i {
    color: #1e3a5f;
    font-size: 0.85rem;
}
.review-section-body {
    padding: 1.25rem;
}
.review-field {
    margin-bottom: 0;
}
.review-field label {
    display: block;
    font-size: 0.7rem;
    font-weight: 600;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.25rem;
}
.review-field-value {
    font-size: 0.9rem;
    color: #1e293b;
}
.allowed-countries-list {
    display: flex;
    flex-wrap: wrap;
    gap: 0.375rem;
}
.country-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.25rem 0.5rem;
    background: #e2e8f0;
    color: #475569;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 500;
}
.country-chip.default {
    background: #dbeafe;
    color: #1e40af;
}
.country-chip.override {
    background: #d1fae5;
    color: #065f46;
}
.activity-placeholder {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
    background: #f8fafc;
    border: 1px dashed #cbd5e1;
    border-radius: 6px;
    color: #94a3b8;
    font-size: 0.85rem;
}
.activity-placeholder i {
    font-size: 1rem;
}
.reason-box {
    padding: 0.875rem 1rem;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    font-size: 0.875rem;
    color: #1e293b;
    line-height: 1.5;
}
.admin-actions-container {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}
.admin-actions-container .btn {
    padding: 0.625rem 1.25rem;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}
.admin-actions-container .btn-approve {
    background: #059669;
    color: #fff;
    border: none;
}
.admin-actions-container .btn-approve:hover {
    background: #047857;
    color: #fff;
}
.admin-actions-container .btn-reject {
    background: #dc2626;
    color: #fff;
    border: none;
}
.admin-actions-container .btn-reject:hover {
    background: #b91c1c;
    color: #fff;
}
.reviewed-info {
    padding: 0.875rem 1rem;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    font-size: 0.85rem;
    color: #64748b;
}
.reviewed-info i {
    margin-right: 0.375rem;
}
.reviewed-info.approved {
    background: #f0fdf4;
    border-color: #86efac;
    color: #166534;
}
.reviewed-info.rejected {
    background: #fef2f2;
    border-color: #fecaca;
    color: #991b1b;
}
.risk-pill {
    padding: 0.125rem 0.5rem;
    border-radius: 50px;
    font-size: 0.65rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}
.risk-pill.high {
    background: #fecaca;
    color: #991b1b;
}
.risk-pill.medium {
    background: #fed7aa;
    color: #9a3412;
}
.risk-pill.low {
    background: #d9f99d;
    color: #3f6212;
}
.risk-pill.critical {
    background: #fce7f3;
    color: #9d174d;
}
.action-menu {
    position: relative;
}
.action-menu-btn {
    background: none;
    border: 1px solid #e5e9f2;
    border-radius: 6px;
    padding: 0.375rem 0.5rem;
    cursor: pointer;
    color: #6c757d;
    transition: all 0.2s;
}
.action-menu-btn:hover {
    background: #f8f9fc;
    border-color: #1e3a5f;
    color: #1e3a5f;
}
.action-dropdown {
    position: absolute;
    right: 0;
    top: 100%;
    background: #fff;
    border: 1px solid #e5e9f2;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    min-width: 180px;
    z-index: 100;
    display: none;
}
.action-dropdown.show {
    display: block;
}
.action-dropdown-item {
    display: flex;
    align-items: center;
    gap: 0.625rem;
    padding: 0.625rem 1rem;
    cursor: pointer;
    font-size: 0.875rem;
    color: #495057;
    transition: background 0.15s;
}
.action-dropdown-item:hover {
    background: #f8f9fc;
}
.action-dropdown-item i {
    width: 16px;
    text-align: center;
    color: #6c757d;
}
.action-dropdown-item.approve { color: #059669; }
.action-dropdown-item.approve i { color: #059669; }
.action-dropdown-item.reject { color: #dc2626; }
.action-dropdown-item.reject i { color: #dc2626; }
.action-dropdown-item.view { color: #1e3a5f; }
.action-dropdown-item.view i { color: #1e3a5f; }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/admin">Admin</a></li>
            <li class="breadcrumb-item"><a href="/admin/security">Security & Compliance</a></li>
            <li class="breadcrumb-item active">Country Controls</li>
        </ol>
    </nav>

    <div class="country-controls-header">
        <div class="country-controls-title">
            <h4><i class="fas fa-globe me-2"></i>Country Controls<span class="admin-internal-badge">Admin Only</span></h4>
            <p>Manage allowed destination countries for SMS messaging across all customer accounts</p>
        </div>
        <div class="d-flex align-items-center gap-3">
            <div class="enforcement-sync-indicator" id="syncIndicator">
                <i class="fas fa-check-circle"></i>
                <span>All systems synchronized</span>
            </div>
            <button class="btn btn-outline-secondary btn-sm" onclick="refreshCountryData()">
                <i class="fas fa-sync-alt me-1"></i>Refresh
            </button>
        </div>
    </div>

    <ul class="nav admin-tabs" id="countryControlsTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="review-tab" data-bs-toggle="tab" data-bs-target="#reviewPane" type="button" role="tab">
                <i class="fas fa-inbox me-1"></i>Review
                <span class="badge pending-badge" id="pendingRequestsBadge">3</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="countries-tab" data-bs-toggle="tab" data-bs-target="#countriesPane" type="button" role="tab">
                <i class="fas fa-globe-americas me-1"></i>Countries
            </button>
        </li>
    </ul>

    <div class="tab-content" id="countryControlsTabContent">
        <div class="tab-pane fade show active" id="reviewPane" role="tabpanel">
            <div class="queue-stats-row">
                <div class="queue-stat-card awaiting active" data-filter="pending" onclick="filterByStatus('pending')">
                    <div class="stat-count" id="reviewPendingCount">3</div>
                    <div class="stat-label">Awaiting Review</div>
                </div>
                <div class="queue-stat-card approved" data-filter="approved" onclick="filterByStatus('approved')">
                    <div class="stat-count" id="reviewApprovedCount">5</div>
                    <div class="stat-label">Approved</div>
                </div>
                <div class="queue-stat-card rejected" data-filter="rejected" onclick="filterByStatus('rejected')">
                    <div class="stat-count" id="reviewRejectedCount">1</div>
                    <div class="stat-label">Rejected</div>
                </div>
                <div class="queue-stat-card total" data-filter="" onclick="filterByStatus('')">
                    <div class="stat-count" id="reviewTotalCount">9</div>
                    <div class="stat-label">Total</div>
                </div>
            </div>

            <div class="filter-panel">
                <div class="filter-row">
                    <div class="filter-group">
                        <label>Status</label>
                        <select class="form-select form-select-sm" id="reviewStatusFilter">
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                            <option value="">All Statuses</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Customer</label>
                        <select class="form-select form-select-sm" id="reviewCustomerFilter">
                            <option value="">All Customers</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Country</label>
                        <select class="form-select form-select-sm" id="reviewCountryFilter">
                            <option value="">All Countries</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Risk Level</label>
                        <select class="form-select form-select-sm" id="reviewRiskFilter">
                            <option value="">All Risks</option>
                            <option value="critical">Critical</option>
                            <option value="high">High</option>
                            <option value="medium">Medium</option>
                            <option value="low">Low</option>
                        </select>
                    </div>
                    <div class="filter-actions">
                        <button class="btn btn-sm" style="background: #1e3a5f; color: #fff;" onclick="applyReviewFilters()">
                            <i class="fas fa-check me-1"></i>Apply
                        </button>
                        <button class="btn btn-outline-secondary btn-sm" onclick="clearReviewFilters()">
                            <i class="fas fa-undo me-1"></i>Reset
                        </button>
                    </div>
                </div>
            </div>

            <div class="queue-table-container">
                <table class="queue-table">
                    <thead>
                        <tr>
                            <th>Account Name</th>
                            <th>Sub Account</th>
                            <th>Country</th>
                            <th>Submitted</th>
                            <th>Account Status</th>
                            <th>Review Status</th>
                            <th style="width: 100px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="reviewTableBody"></tbody>
                </table>
            </div>
            <div id="emptyReviewState" class="empty-state" style="display: none;">
                <i class="fas fa-inbox"></i>
                <h6>No requests found</h6>
                <p class="small">There are no country access requests matching your filters.</p>
            </div>
        </div>

        <div class="tab-pane fade" id="countriesPane" role="tabpanel">
            <div class="enforcement-banner">
                <h6><i class="fas fa-shield-alt"></i>Shared Enforcement Configuration</h6>
                <p>Changes here immediately apply across all enforcement points. No restart required.</p>
                <div class="enforcement-points">
                    <div class="enforcement-point">
                        <i class="fas fa-check-circle"></i>
                        <span>Customer Portal Security Settings</span>
                    </div>
                    <div class="enforcement-point">
                        <i class="fas fa-check-circle"></i>
                        <span>Send Message Validation</span>
                    </div>
                    <div class="enforcement-point">
                        <i class="fas fa-check-circle"></i>
                        <span>API Submission Validation</span>
                    </div>
                    <div class="enforcement-point">
                        <i class="fas fa-check-circle"></i>
                        <span>Bulk Campaign Processing</span>
                    </div>
                </div>
            </div>

            <div class="country-stats">
                <div class="country-stat-card allowed">
                    <div class="country-stat-value" id="allowedCount">142</div>
                    <div class="country-stat-label">Allowed Countries</div>
                </div>
                <div class="country-stat-card blocked">
                    <div class="country-stat-value" id="blockedCount">23</div>
                    <div class="country-stat-label">Blocked Countries</div>
                </div>
                <div class="country-stat-card restricted">
                    <div class="country-stat-value" id="restrictedCount">12</div>
                    <div class="country-stat-label">Restricted (Approval Required)</div>
                </div>
                <div class="country-stat-card pending">
                    <div class="country-stat-value" id="pendingCount">5</div>
                    <div class="country-stat-label">Pending Review</div>
                </div>
            </div>

            <div class="country-table-card">
                <div class="country-table-header">
                    <div class="d-flex align-items-center gap-3">
                        <h6><i class="fas fa-list me-2"></i>Global Policy & Overrides</h6>
                        <div class="bulk-actions">
                            <select class="form-select form-select-sm" id="bulkStatusFilter">
                                <option value="">All Statuses</option>
                                <option value="allowed">Allowed</option>
                                <option value="blocked">Blocked</option>
                                <option value="restricted">Restricted</option>
                                <option value="pending">Pending</option>
                            </select>
                            <select class="form-select form-select-sm" id="bulkRiskFilter">
                                <option value="">All Risk Levels</option>
                                <option value="low">Low Risk</option>
                                <option value="medium">Medium Risk</option>
                                <option value="high">High Risk</option>
                                <option value="critical">Critical Risk</option>
                            </select>
                        </div>
                    </div>
                    <div class="country-search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" class="form-control form-control-sm" id="countrySearch" placeholder="Search countries...">
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="country-table" id="countryTable">
                        <thead>
                            <tr>
                                <th style="width: 30px;"><input type="checkbox" id="selectAllCountries"></th>
                                <th>Country</th>
                                <th>ISO Code</th>
                                <th>Dial Code</th>
                                <th>Status</th>
                        <th>Risk Level</th>
                        <th>Customer Overrides</th>
                        <th>Last Updated</th>
                        <th style="width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="countryTableBody">
                </tbody>
            </table>
        </div>
    </div>

    <div class="audit-preview" id="auditPreview" style="display: none;">
        <div class="audit-preview-title">
            <i class="fas fa-history"></i>Pending Audit Record (Preview)
        </div>
        <div class="audit-preview-content" id="auditPreviewContent"></div>
    </div>
        </div>
    </div>
</div>

<div class="modal fade" id="reviewDetailModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header" style="background: #1e3a5f; color: #fff;">
                <h5 class="modal-title"><i class="fas fa-clipboard-check me-2"></i>Country Access Review</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="review-panel-content">
                    <div class="review-section">
                        <div class="review-section-header">
                            <i class="fas fa-building"></i>
                            <span>Account Context</span>
                        </div>
                        <div class="review-section-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="review-field">
                                        <label>Account Name</label>
                                        <div id="modalAccountName" class="review-field-value"></div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="review-field">
                                        <label>Account Number</label>
                                        <div id="modalAccountNumber" class="review-field-value"></div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="review-field">
                                        <label>Account State</label>
                                        <div id="modalAccountState"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="review-field">
                                        <label>Sub Account</label>
                                        <div id="modalSubAccount" class="review-field-value"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="review-field">
                                        <label>Risk Level</label>
                                        <div id="modalRiskLevel"></div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="review-field">
                                        <label>Current Allowed Countries</label>
                                        <div id="modalAllowedCountries" class="allowed-countries-list"></div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="review-field">
                                        <label>Recent Messaging Activity</label>
                                        <div class="activity-placeholder">
                                            <i class="fas fa-chart-line"></i>
                                            <span>Activity summary will be available in a future release</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="review-section">
                        <div class="review-section-header">
                            <i class="fas fa-file-alt"></i>
                            <span>Request Details</span>
                        </div>
                        <div class="review-section-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="review-field">
                                        <label>Request ID</label>
                                        <div id="modalRequestId" class="review-field-value font-monospace"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="review-field">
                                        <label>Current Status</label>
                                        <div id="modalRequestStatus"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="review-field">
                                        <label>Requested Country</label>
                                        <div id="modalRequestCountry" class="review-field-value fw-medium"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="review-field">
                                        <label>Estimated Volume</label>
                                        <div id="modalRequestVolume" class="review-field-value"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="review-field">
                                        <label>Submitted By</label>
                                        <div id="modalRequestSubmittedBy" class="review-field-value">
                                            <i class="fas fa-envelope text-muted me-1"></i>
                                            <span id="modalSubmitterEmail"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="review-field">
                                        <label>Submitted Date</label>
                                        <div id="modalRequestSubmittedAt" class="review-field-value"></div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="review-field">
                                        <label>Business Justification</label>
                                        <div id="modalRequestReason" class="reason-box"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="review-section" id="adminActionsSection">
                        <div class="review-section-header">
                            <i class="fas fa-user-shield"></i>
                            <span>Admin Actions</span>
                        </div>
                        <div class="review-section-body">
                            <div id="modalRequestActions" class="admin-actions-container"></div>
                            <div id="modalReviewedInfo" class="reviewed-info" style="display: none;"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Close
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="countryActionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="countryActionModalTitle">Update Country Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Country</label>
                    <div id="modalCountryName" class="form-control-plaintext"></div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Current Status</label>
                    <div id="modalCurrentStatus"></div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">New Status</label>
                    <select class="form-select" id="modalNewStatus">
                        <option value="allowed">Allowed</option>
                        <option value="blocked">Blocked</option>
                        <option value="restricted">Restricted (Approval Required)</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Reason for Change <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="modalChangeReason" rows="3" placeholder="Enter reason for this change (required for audit)..."></textarea>
                </div>
                <div class="alert alert-info small mb-0">
                    <i class="fas fa-info-circle me-1"></i>
                    This change will immediately affect all customers and enforcement points.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn admin-btn-primary" id="confirmStatusChange">
                    <i class="fas fa-save me-1"></i>Apply Change
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    initCountryControls();
});

var CountryControlsService = (function() {
    var CONFIG_KEY = 'QUICKSMS_ALLOWED_DESTINATION_COUNTRIES';
    var VERSION_KEY = 'QUICKSMS_COUNTRY_CONFIG_VERSION';
    
    var sharedConfig = {
        version: 0,
        lastUpdated: null,
        updatedBy: null,
        countries: {}
    };

    function getSharedConfig() {
        return JSON.parse(JSON.stringify(sharedConfig));
    }

    function updateCountryStatus(countryCode, newStatus, reason, adminUser) {
        var beforeState = sharedConfig.countries[countryCode] ? 
            JSON.parse(JSON.stringify(sharedConfig.countries[countryCode])) : null;

        sharedConfig.countries[countryCode] = sharedConfig.countries[countryCode] || {};
        sharedConfig.countries[countryCode].status = newStatus;
        sharedConfig.countries[countryCode].lastUpdated = new Date().toISOString();
        sharedConfig.countries[countryCode].updatedBy = adminUser.email;

        sharedConfig.version++;
        sharedConfig.lastUpdated = new Date().toISOString();
        sharedConfig.updatedBy = adminUser.email;

        var auditEvent = createAuditEvent(countryCode, beforeState, sharedConfig.countries[countryCode], reason, adminUser);
        
        broadcastConfigUpdate();

        return {
            success: true,
            configVersion: sharedConfig.version,
            auditEvent: auditEvent
        };
    }

    function createAuditEvent(countryCode, beforeState, afterState, reason, adminUser) {
        return {
            id: 'CCNTRL-' + Date.now(),
            eventType: 'COUNTRY_CONTROL_UPDATED',
            eventLabel: 'Country Control Updated',
            timestamp: new Date().toISOString(),
            actor: {
                id: adminUser.id,
                email: adminUser.email,
                role: adminUser.role
            },
            admin_actor_id: adminUser.id,
            category: 'security',
            severity: 'high',
            result: 'success',
            isInternalOnly: true,
            isAdminEvent: true,
            target: {
                type: 'country_control',
                countryCode: countryCode
            },
            details: {
                countryCode: countryCode,
                reason: reason,
                beforeState: beforeState,
                afterState: afterState,
                configVersion: sharedConfig.version,
                enforcementPoints: [
                    'customer_portal_security_settings',
                    'send_message_validation',
                    'api_submission_validation',
                    'bulk_campaign_processing'
                ]
            },
            ip: '10.0.1.50'
        };
    }

    function broadcastConfigUpdate() {
        console.log('[CountryControls] Broadcasting config update v' + sharedConfig.version);
        
        window.dispatchEvent(new CustomEvent('countryConfigUpdated', {
            detail: {
                version: sharedConfig.version,
                timestamp: sharedConfig.lastUpdated
            }
        }));
    }

    function isCountryAllowed(countryCode, customerId) {
        var globalStatus = sharedConfig.countries[countryCode]?.status || 'allowed';
        
        if (globalStatus === 'blocked') {
            return { allowed: false, reason: 'Blocked globally by administrator' };
        }
        
        if (globalStatus === 'restricted') {
            return { allowed: false, reason: 'Requires approval - country is restricted', requiresApproval: true };
        }
        
        return { allowed: true };
    }

    function validateDestination(phoneNumber, customerId) {
        var countryCode = extractCountryCode(phoneNumber);
        return isCountryAllowed(countryCode, customerId);
    }

    function extractCountryCode(phoneNumber) {
        var dialCodeMap = {
            '1': 'US', '44': 'GB', '33': 'FR', '49': 'DE', '39': 'IT',
            '34': 'ES', '81': 'JP', '86': 'CN', '91': 'IN', '7': 'RU'
        };
        var cleaned = phoneNumber.replace(/[^0-9]/g, '');
        for (var code in dialCodeMap) {
            if (cleaned.startsWith(code)) {
                return dialCodeMap[code];
            }
        }
        return 'UNKNOWN';
    }

    return {
        getSharedConfig: getSharedConfig,
        updateCountryStatus: updateCountryStatus,
        isCountryAllowed: isCountryAllowed,
        validateDestination: validateDestination,
        CONFIG_KEY: CONFIG_KEY
    };
})();

window.CountryControlsService = CountryControlsService;

var countries = [];
var countryRequests = [];
var currentAdmin = {
    id: 'ADM001',
    email: 'admin@quicksms.co.uk',
    role: 'super_admin'
};
var selectedCountry = null;
var selectedRequest = null;

function initCountryControls() {
    countries = generateMockCountries();
    countryRequests = generateMockRequests();
    renderCountryTable();
    renderRequestsList();
    bindEvents();
    updateStats();
    updateReviewStats();
    
    console.log('[CountryControls] Initialized with shared enforcement service');
    console.log('[CountryControls] Config version:', CountryControlsService.getSharedConfig().version);
}

function generateMockRequests() {
    return [
        {
            id: 'REQ-001',
            customer: { id: 'CUST-001', name: 'TechStart Ltd', accountNumber: 'ACC-10045', accountStatus: 'live', subAccount: null },
            country: { code: 'NG', name: 'Nigeria', dialCode: '+234' },
            requestType: 'enable',
            reason: 'We have legitimate business operations in Nigeria and need to send SMS to our local customers.',
            submittedBy: 'james@techstart.co.uk',
            submittedAt: '28-01-2026 14:30',
            status: 'pending',
            risk: 'high',
            estimatedVolume: '5,000/mo',
            reviewedBy: null,
            reviewedAt: null
        },
        {
            id: 'REQ-002',
            customer: { id: 'CUST-002', name: 'HealthFirst UK', accountNumber: 'ACC-10089', accountStatus: 'live', subAccount: 'NHS Partnership' },
            country: { code: 'IN', name: 'India', dialCode: '+91' },
            requestType: 'enable',
            reason: 'Need to send appointment reminders to patients in our Indian branch.',
            submittedBy: 'dr.jones@healthfirst.nhs.uk',
            submittedAt: '28-01-2026 10:15',
            status: 'pending',
            risk: 'medium',
            estimatedVolume: '2,000/mo',
            reviewedBy: null,
            reviewedAt: null
        },
        {
            id: 'REQ-003',
            customer: { id: 'CUST-003', name: 'E-Commerce Hub', accountNumber: 'ACC-10112', accountStatus: 'test', subAccount: null },
            country: { code: 'PH', name: 'Philippines', dialCode: '+63' },
            requestType: 'enable',
            reason: 'Expanding e-commerce operations to Philippines, need order confirmation SMS.',
            submittedBy: 'ops@ecommercehub.com',
            submittedAt: '27-01-2026 16:45',
            status: 'pending',
            risk: 'critical',
            estimatedVolume: '10,000/mo',
            reviewedBy: null,
            reviewedAt: null
        },
        {
            id: 'REQ-004',
            customer: { id: 'CUST-004', name: 'RetailMax', accountNumber: 'ACC-10078', accountStatus: 'live', subAccount: 'LATAM Division' },
            country: { code: 'BR', name: 'Brazil', dialCode: '+55' },
            requestType: 'enable',
            reason: 'Opening new retail stores in Brazil.',
            submittedBy: 'admin@retailmax.com',
            submittedAt: '27-01-2026 09:00',
            status: 'approved',
            risk: 'medium',
            estimatedVolume: '8,000/mo',
            reviewedBy: 'sarah.johnson@quicksms.co.uk',
            reviewedAt: '27-01-2026 11:30'
        },
        {
            id: 'REQ-005',
            customer: { id: 'CUST-005', name: 'Unknown Corp', accountNumber: 'ACC-10099', accountStatus: 'suspended', subAccount: null },
            country: { code: 'RU', name: 'Russia', dialCode: '+7' },
            requestType: 'enable',
            reason: 'Business expansion.',
            submittedBy: 'info@unknowncorp.com',
            submittedAt: '26-01-2026 14:00',
            status: 'rejected',
            risk: 'critical',
            estimatedVolume: '50,000/mo',
            reviewedBy: 'emily.chen@quicksms.co.uk',
            reviewedAt: '26-01-2026 15:30'
        }
    ];
}

function viewAccount(customerId) {
    console.log('[CountryControls] Navigate to account:', customerId);
    window.location.href = '/admin/accounts/' + customerId;
}

function renderRequestsList() {
    var tbody = document.getElementById('reviewTableBody');
    var emptyState = document.getElementById('emptyReviewState');
    var tableContainer = document.querySelector('.queue-table-container');
    var statusFilter = document.getElementById('reviewStatusFilter').value;
    var customerFilter = document.getElementById('reviewCustomerFilter').value;
    var countryFilter = document.getElementById('reviewCountryFilter').value;
    var riskFilter = document.getElementById('reviewRiskFilter') ? document.getElementById('reviewRiskFilter').value : '';

    var filtered = countryRequests.filter(function(r) {
        var matchesStatus = !statusFilter || r.status === statusFilter;
        var matchesCustomer = !customerFilter || r.customer.id === customerFilter;
        var matchesCountry = !countryFilter || r.country.code === countryFilter;
        var matchesRisk = !riskFilter || r.risk === riskFilter;
        return matchesStatus && matchesCustomer && matchesCountry && matchesRisk;
    });

    if (filtered.length === 0) {
        tableContainer.style.display = 'none';
        emptyState.style.display = 'block';
        return;
    }

    tableContainer.style.display = 'block';
    emptyState.style.display = 'none';
    tbody.innerHTML = '';

    filtered.forEach(function(request) {
        var row = document.createElement('tr');
        row.onclick = function(e) { 
            if (!e.target.closest('.btn-review')) {
                openReviewModal(request.id);
            }
        };

        var accountStatusClass = request.customer.accountStatus === 'live' ? 'live' : 
                                 request.customer.accountStatus === 'test' ? 'test' : 'suspended';
        var accountStatusPill = '<span class="status-pill account-' + accountStatusClass + '">' + 
            capitalize(request.customer.accountStatus) + '</span>';

        var reviewStatusPill = '<span class="status-pill ' + request.status + '">' + 
            (request.status === 'pending' ? '<i class="fas fa-clock"></i>' : 
             request.status === 'approved' ? '<i class="fas fa-check"></i>' : '<i class="fas fa-times"></i>') + 
            ' ' + capitalize(request.status) + '</span>';

        var reviewBtn = '<button class="btn btn-sm btn-review" onclick="openReviewModal(\'' + request.id + '\')">' +
            '<i class="fas fa-eye me-1"></i>Review</button>';

        row.innerHTML = 
            '<td><a href="#" class="account-link" onclick="viewAccount(\'' + request.customer.id + '\'); return false;">' + 
                request.customer.name + '</a><div class="account-id">' + request.customer.accountNumber + '</div></td>' +
            '<td>' + (request.customer.subAccount ? '<span class="text-muted">' + request.customer.subAccount + '</span>' : '<span class="text-muted">—</span>') + '</td>' +
            '<td><strong>' + request.country.name + '</strong> <span class="text-muted">(' + request.country.dialCode + ')</span></td>' +
            '<td><span class="small">' + request.submittedAt + '</span></td>' +
            '<td>' + accountStatusPill + '</td>' +
            '<td>' + reviewStatusPill + '</td>' +
            '<td>' + reviewBtn + '</td>';

        tbody.appendChild(row);
    });
}

function toggleActionMenu(event, requestId) {
    event.stopPropagation();
    document.querySelectorAll('.action-dropdown.show').forEach(function(menu) {
        if (menu.id !== 'actionMenu-' + requestId) {
            menu.classList.remove('show');
        }
    });
    document.getElementById('actionMenu-' + requestId).classList.toggle('show');
}

document.addEventListener('click', function(e) {
    if (!e.target.closest('.action-menu')) {
        document.querySelectorAll('.action-dropdown.show').forEach(function(menu) {
            menu.classList.remove('show');
        });
    }
});

function filterByStatus(status) {
    document.getElementById('reviewStatusFilter').value = status;
    document.querySelectorAll('.queue-stat-card').forEach(function(card) {
        card.classList.remove('active');
        if (card.dataset.filter === status) {
            card.classList.add('active');
        }
    });
    renderRequestsList();
}

function applyReviewFilters() {
    renderRequestsList();
}

function clearReviewFilters() {
    document.getElementById('reviewStatusFilter').value = 'pending';
    document.getElementById('reviewCustomerFilter').value = '';
    document.getElementById('reviewCountryFilter').value = '';
    if (document.getElementById('reviewRiskFilter')) {
        document.getElementById('reviewRiskFilter').value = '';
    }
    filterByStatus('pending');
}

function openReviewModal(requestId) {
    var request = countryRequests.find(function(r) { return r.id === requestId; });
    if (!request) return;
    selectedRequest = request;
    
    document.getElementById('modalAccountName').textContent = request.customer.name;
    document.getElementById('modalAccountNumber').textContent = request.customer.accountNumber;
    
    var accountStatusClass = request.customer.accountStatus === 'live' ? 'account-live' : 
                             request.customer.accountStatus === 'test' ? 'account-test' : 'account-suspended';
    document.getElementById('modalAccountState').innerHTML = 
        '<span class="status-pill ' + accountStatusClass + '">' + capitalize(request.customer.accountStatus) + '</span>';
    
    document.getElementById('modalSubAccount').textContent = request.customer.subAccount || '—';
    document.getElementById('modalRiskLevel').innerHTML = 
        '<span class="risk-pill ' + request.risk + '">' + request.risk.toUpperCase() + '</span>';
    
    var allowedCountriesHtml = getAccountAllowedCountries(request.customer.id);
    document.getElementById('modalAllowedCountries').innerHTML = allowedCountriesHtml;
    
    document.getElementById('modalRequestId').textContent = request.id;
    
    var statusIcon = request.status === 'pending' ? 'fa-clock' : 
                     request.status === 'approved' ? 'fa-check' : 'fa-times';
    document.getElementById('modalRequestStatus').innerHTML = 
        '<span class="status-pill ' + request.status + '"><i class="fas ' + statusIcon + '"></i> ' + capitalize(request.status) + '</span>';
    
    document.getElementById('modalRequestCountry').innerHTML = 
        '<i class="fas fa-globe-americas text-muted me-1"></i>' + request.country.name + ' <span class="text-muted">(' + request.country.dialCode + ')</span>';
    document.getElementById('modalRequestVolume').textContent = request.estimatedVolume || '—';
    document.getElementById('modalSubmitterEmail').textContent = request.submittedBy;
    document.getElementById('modalRequestSubmittedAt').textContent = request.submittedAt;
    document.getElementById('modalRequestReason').textContent = request.reason || 'No reason provided';
    
    var actionsDiv = document.getElementById('modalRequestActions');
    var reviewedInfoDiv = document.getElementById('modalReviewedInfo');
    
    if (request.status === 'pending') {
        actionsDiv.style.display = 'flex';
        reviewedInfoDiv.style.display = 'none';
        actionsDiv.innerHTML = 
            '<button class="btn btn-approve" onclick="approveRequest(\'' + request.id + '\'); bootstrap.Modal.getInstance(document.getElementById(\'reviewDetailModal\')).hide();">' +
                '<i class="fas fa-check-circle"></i>Approve Request' +
            '</button>' +
            '<button class="btn btn-reject" onclick="rejectRequest(\'' + request.id + '\'); bootstrap.Modal.getInstance(document.getElementById(\'reviewDetailModal\')).hide();">' +
                '<i class="fas fa-times-circle"></i>Reject Request' +
            '</button>';
    } else {
        actionsDiv.style.display = 'none';
        reviewedInfoDiv.style.display = 'block';
        reviewedInfoDiv.className = 'reviewed-info ' + request.status;
        
        var icon = request.status === 'approved' ? 'fa-check-circle' : 'fa-times-circle';
        reviewedInfoDiv.innerHTML = 
            '<i class="fas ' + icon + '"></i>' +
            '<strong>' + capitalize(request.status) + '</strong> by ' + request.reviewedBy + 
            ' on ' + request.reviewedAt;
    }
    
    var modal = new bootstrap.Modal(document.getElementById('reviewDetailModal'));
    modal.show();
}

function getAccountAllowedCountries(customerId) {
    var defaultCountries = [
        { code: 'GB', name: 'United Kingdom' },
        { code: 'IE', name: 'Ireland' },
        { code: 'FR', name: 'France' },
        { code: 'DE', name: 'Germany' }
    ];
    
    var overrides = {
        'CUST-001': [{ code: 'US', name: 'United States' }],
        'CUST-002': [{ code: 'AE', name: 'UAE' }, { code: 'SG', name: 'Singapore' }],
        'CUST-004': [{ code: 'ES', name: 'Spain' }, { code: 'PT', name: 'Portugal' }]
    };
    
    var html = '';
    
    defaultCountries.forEach(function(c) {
        html += '<span class="country-chip default"><i class="fas fa-globe"></i>' + c.code + '</span>';
    });
    
    if (overrides[customerId]) {
        overrides[customerId].forEach(function(c) {
            html += '<span class="country-chip override"><i class="fas fa-plus-circle"></i>' + c.code + '</span>';
        });
    }
    
    return html;
}

function updateReviewStats() {
    var pending = countryRequests.filter(function(r) { return r.status === 'pending'; }).length;
    var approved = countryRequests.filter(function(r) { return r.status === 'approved'; }).length;
    var rejected = countryRequests.filter(function(r) { return r.status === 'rejected'; }).length;
    var total = countryRequests.length;

    document.getElementById('reviewPendingCount').textContent = pending;
    document.getElementById('reviewApprovedCount').textContent = approved;
    document.getElementById('reviewRejectedCount').textContent = rejected;
    document.getElementById('reviewTotalCount').textContent = total;
    document.getElementById('pendingRequestsBadge').textContent = pending;
    
    if (pending === 0) {
        document.getElementById('pendingRequestsBadge').style.display = 'none';
    } else {
        document.getElementById('pendingRequestsBadge').style.display = 'inline';
    }
}

function approveRequest(requestId) {
    var request = countryRequests.find(function(r) { return r.id === requestId; });
    if (!request) return;

    if (!confirm('Approve country access for ' + request.customer.name + ' to ' + request.country.name + '?')) {
        return;
    }

    request.status = 'approved';
    request.reviewedBy = currentAdmin.email;
    request.reviewedAt = new Date().toISOString().replace('T', ' ').substring(0, 16);

    renderRequestsList();
    updateReviewStats();
    showToast('Request approved. ' + request.customer.name + ' can now send to ' + request.country.name + '.', 'success');
}

function rejectRequest(requestId) {
    var request = countryRequests.find(function(r) { return r.id === requestId; });
    if (!request) return;

    var reason = prompt('Enter rejection reason:');
    if (!reason) return;

    request.status = 'rejected';
    request.reviewedBy = currentAdmin.email;
    request.reviewedAt = new Date().toISOString().replace('T', ' ').substring(0, 16);
    request.rejectionReason = reason;

    renderRequestsList();
    updateReviewStats();
    showToast('Request rejected.', 'info');
}

function viewRequestDetails(requestId) {
    var request = countryRequests.find(function(r) { return r.id === requestId; });
    if (!request) return;
    alert('Request Details:\n\nCustomer: ' + request.customer.name + '\nCountry: ' + request.country.name + '\nReason: ' + request.reason);
}

function generateMockCountries() {
    var countryData = [
        { code: 'GB', name: 'United Kingdom', dialCode: '+44', status: 'allowed', risk: 'low', overrides: 0 },
        { code: 'US', name: 'United States', dialCode: '+1', status: 'allowed', risk: 'low', overrides: 3 },
        { code: 'DE', name: 'Germany', dialCode: '+49', status: 'allowed', risk: 'low', overrides: 1 },
        { code: 'FR', name: 'France', dialCode: '+33', status: 'allowed', risk: 'low', overrides: 0 },
        { code: 'ES', name: 'Spain', dialCode: '+34', status: 'allowed', risk: 'low', overrides: 2 },
        { code: 'IT', name: 'Italy', dialCode: '+39', status: 'allowed', risk: 'medium', overrides: 0 },
        { code: 'NL', name: 'Netherlands', dialCode: '+31', status: 'allowed', risk: 'low', overrides: 0 },
        { code: 'BE', name: 'Belgium', dialCode: '+32', status: 'allowed', risk: 'low', overrides: 0 },
        { code: 'CH', name: 'Switzerland', dialCode: '+41', status: 'allowed', risk: 'low', overrides: 1 },
        { code: 'AT', name: 'Austria', dialCode: '+43', status: 'allowed', risk: 'low', overrides: 0 },
        { code: 'PL', name: 'Poland', dialCode: '+48', status: 'allowed', risk: 'medium', overrides: 0 },
        { code: 'RU', name: 'Russia', dialCode: '+7', status: 'blocked', risk: 'critical', overrides: 0 },
        { code: 'BY', name: 'Belarus', dialCode: '+375', status: 'blocked', risk: 'critical', overrides: 0 },
        { code: 'IR', name: 'Iran', dialCode: '+98', status: 'blocked', risk: 'critical', overrides: 0 },
        { code: 'KP', name: 'North Korea', dialCode: '+850', status: 'blocked', risk: 'critical', overrides: 0 },
        { code: 'SY', name: 'Syria', dialCode: '+963', status: 'blocked', risk: 'critical', overrides: 0 },
        { code: 'CU', name: 'Cuba', dialCode: '+53', status: 'blocked', risk: 'high', overrides: 0 },
        { code: 'NG', name: 'Nigeria', dialCode: '+234', status: 'restricted', risk: 'high', overrides: 5 },
        { code: 'PH', name: 'Philippines', dialCode: '+63', status: 'restricted', risk: 'high', overrides: 3 },
        { code: 'IN', name: 'India', dialCode: '+91', status: 'allowed', risk: 'medium', overrides: 8 },
        { code: 'PK', name: 'Pakistan', dialCode: '+92', status: 'restricted', risk: 'high', overrides: 2 },
        { code: 'BD', name: 'Bangladesh', dialCode: '+880', status: 'restricted', risk: 'medium', overrides: 1 },
        { code: 'VN', name: 'Vietnam', dialCode: '+84', status: 'allowed', risk: 'medium', overrides: 0 },
        { code: 'TH', name: 'Thailand', dialCode: '+66', status: 'allowed', risk: 'low', overrides: 0 },
        { code: 'MY', name: 'Malaysia', dialCode: '+60', status: 'allowed', risk: 'low', overrides: 0 },
        { code: 'SG', name: 'Singapore', dialCode: '+65', status: 'allowed', risk: 'low', overrides: 0 },
        { code: 'AU', name: 'Australia', dialCode: '+61', status: 'allowed', risk: 'low', overrides: 0 },
        { code: 'NZ', name: 'New Zealand', dialCode: '+64', status: 'allowed', risk: 'low', overrides: 0 },
        { code: 'JP', name: 'Japan', dialCode: '+81', status: 'allowed', risk: 'low', overrides: 0 },
        { code: 'KR', name: 'South Korea', dialCode: '+82', status: 'allowed', risk: 'low', overrides: 0 },
        { code: 'CN', name: 'China', dialCode: '+86', status: 'restricted', risk: 'high', overrides: 4 },
        { code: 'HK', name: 'Hong Kong', dialCode: '+852', status: 'allowed', risk: 'low', overrides: 0 },
        { code: 'TW', name: 'Taiwan', dialCode: '+886', status: 'allowed', risk: 'low', overrides: 0 },
        { code: 'BR', name: 'Brazil', dialCode: '+55', status: 'allowed', risk: 'medium', overrides: 2 },
        { code: 'MX', name: 'Mexico', dialCode: '+52', status: 'allowed', risk: 'medium', overrides: 1 },
        { code: 'ZA', name: 'South Africa', dialCode: '+27', status: 'allowed', risk: 'medium', overrides: 0 },
        { code: 'AE', name: 'United Arab Emirates', dialCode: '+971', status: 'allowed', risk: 'low', overrides: 0 },
        { code: 'SA', name: 'Saudi Arabia', dialCode: '+966', status: 'restricted', risk: 'medium', overrides: 1 },
        { code: 'IL', name: 'Israel', dialCode: '+972', status: 'allowed', risk: 'medium', overrides: 0 },
        { code: 'VE', name: 'Venezuela', dialCode: '+58', status: 'blocked', risk: 'high', overrides: 0 }
    ];

    return countryData.map(function(c, index) {
        c.id = index + 1;
        c.lastUpdated = getRandomDate();
        return c;
    });
}

function getRandomDate() {
    var dates = [
        '2026-01-28 14:30', '2026-01-27 10:15', '2026-01-26 16:45',
        '2026-01-25 09:00', '2026-01-24 11:30', '2026-01-20 08:00'
    ];
    return dates[Math.floor(Math.random() * dates.length)];
}

function renderCountryTable() {
    var tbody = document.getElementById('countryTableBody');
    var searchTerm = document.getElementById('countrySearch').value.toLowerCase();
    var statusFilter = document.getElementById('bulkStatusFilter').value;
    var riskFilter = document.getElementById('bulkRiskFilter').value;

    var filtered = countries.filter(function(c) {
        var matchesSearch = c.name.toLowerCase().includes(searchTerm) || 
                           c.code.toLowerCase().includes(searchTerm) ||
                           c.dialCode.includes(searchTerm);
        var matchesStatus = !statusFilter || c.status === statusFilter;
        var matchesRisk = !riskFilter || c.risk === riskFilter;
        return matchesSearch && matchesStatus && matchesRisk;
    });

    tbody.innerHTML = '';

    filtered.forEach(function(country) {
        var row = document.createElement('tr');
        row.innerHTML = 
            '<td><input type="checkbox" class="country-checkbox" data-code="' + country.code + '"></td>' +
            '<td>' +
                '<span class="country-name">' + country.name + '</span>' +
            '</td>' +
            '<td><code>' + country.code + '</code></td>' +
            '<td>' + country.dialCode + '</td>' +
            '<td><span class="status-badge ' + country.status + '">' + capitalize(country.status) + '</span></td>' +
            '<td>' +
                '<div class="risk-indicator">' +
                    '<span class="risk-dot ' + country.risk + '"></span>' +
                    '<span>' + capitalize(country.risk) + '</span>' +
                '</div>' +
            '</td>' +
            '<td>' + (country.overrides > 0 ? 
                '<span class="customer-override-badge">' + country.overrides + ' customers</span>' : 
                '<span class="text-muted">None</span>') + 
            '</td>' +
            '<td class="small text-muted">' + country.lastUpdated + '</td>' +
            '<td>' +
                '<div class="action-btn-group">' +
                    (country.status !== 'allowed' ? 
                        '<button class="action-btn allow" onclick="openActionModal(\'' + country.code + '\', \'allowed\')"><i class="fas fa-check"></i> Allow</button>' : '') +
                    (country.status !== 'blocked' ? 
                        '<button class="action-btn block" onclick="openActionModal(\'' + country.code + '\', \'blocked\')"><i class="fas fa-ban"></i> Block</button>' : '') +
                    (country.status !== 'restricted' ? 
                        '<button class="action-btn restrict" onclick="openActionModal(\'' + country.code + '\', \'restricted\')"><i class="fas fa-exclamation-triangle"></i></button>' : '') +
                '</div>' +
            '</td>';
        tbody.appendChild(row);
    });
}

function capitalize(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}

function updateStats() {
    document.getElementById('allowedCount').textContent = countries.filter(function(c) { return c.status === 'allowed'; }).length;
    document.getElementById('blockedCount').textContent = countries.filter(function(c) { return c.status === 'blocked'; }).length;
    document.getElementById('restrictedCount').textContent = countries.filter(function(c) { return c.status === 'restricted'; }).length;
    document.getElementById('pendingCount').textContent = countries.filter(function(c) { return c.status === 'pending'; }).length;
}

function bindEvents() {
    document.getElementById('countrySearch').addEventListener('input', renderCountryTable);
    document.getElementById('bulkStatusFilter').addEventListener('change', renderCountryTable);
    document.getElementById('bulkRiskFilter').addEventListener('change', renderCountryTable);

    document.getElementById('reviewStatusFilter').addEventListener('change', renderRequestsList);
    document.getElementById('reviewCustomerFilter').addEventListener('change', renderRequestsList);
    document.getElementById('reviewCountryFilter').addEventListener('change', renderRequestsList);

    document.getElementById('confirmStatusChange').addEventListener('click', function() {
        applyStatusChange();
    });

    document.getElementById('selectAllCountries').addEventListener('change', function() {
        var checked = this.checked;
        document.querySelectorAll('.country-checkbox').forEach(function(cb) {
            cb.checked = checked;
        });
    });

    $('button[data-bs-target="#countriesPane"]').on('shown.bs.tab', function() {
        console.log('[CountryControls] Countries tab activated');
        renderCountryTable();
    });
}

function openActionModal(countryCode, newStatus) {
    selectedCountry = countries.find(function(c) { return c.code === countryCode; });
    if (!selectedCountry) return;

    document.getElementById('modalCountryName').textContent = selectedCountry.name + ' (' + selectedCountry.code + ')';
    document.getElementById('modalCurrentStatus').innerHTML = '<span class="status-badge ' + selectedCountry.status + '">' + capitalize(selectedCountry.status) + '</span>';
    document.getElementById('modalNewStatus').value = newStatus;
    document.getElementById('modalChangeReason').value = '';

    var modal = new bootstrap.Modal(document.getElementById('countryActionModal'));
    modal.show();
}

function applyStatusChange() {
    var newStatus = document.getElementById('modalNewStatus').value;
    var reason = document.getElementById('modalChangeReason').value.trim();

    if (!reason) {
        alert('Please provide a reason for this change.');
        return;
    }

    var syncIndicator = document.getElementById('syncIndicator');
    syncIndicator.classList.add('syncing');
    syncIndicator.innerHTML = '<i class="fas fa-sync-alt"></i><span>Synchronizing...</span>';

    var result = CountryControlsService.updateCountryStatus(
        selectedCountry.code,
        newStatus,
        reason,
        currentAdmin
    );

    selectedCountry.status = newStatus;
    selectedCountry.lastUpdated = new Date().toISOString().replace('T', ' ').substring(0, 16);

    console.log('[CountryControls] Audit event created:', result.auditEvent);

    setTimeout(function() {
        syncIndicator.classList.remove('syncing');
        syncIndicator.innerHTML = '<i class="fas fa-check-circle"></i><span>All systems synchronized</span>';

        renderCountryTable();
        updateStats();

        bootstrap.Modal.getInstance(document.getElementById('countryActionModal')).hide();

        showToast('Country status updated and synchronized across all enforcement points', 'success');
    }, 1000);
}

function refreshCountryData() {
    var syncIndicator = document.getElementById('syncIndicator');
    syncIndicator.classList.add('syncing');
    syncIndicator.innerHTML = '<i class="fas fa-sync-alt"></i><span>Refreshing...</span>';

    setTimeout(function() {
        syncIndicator.classList.remove('syncing');
        syncIndicator.innerHTML = '<i class="fas fa-check-circle"></i><span>All systems synchronized</span>';
        showToast('Country data refreshed', 'info');
    }, 800);
}

function showToast(message, type) {
    var toastContainer = document.getElementById('toastContainer');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toastContainer';
        toastContainer.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999;';
        document.body.appendChild(toastContainer);
    }

    var bgColor = type === 'success' ? '#48bb78' : type === 'error' ? '#e53e3e' : type === 'warning' ? '#ecc94b' : '#1e3a5f';
    var toast = document.createElement('div');
    toast.style.cssText = 'background: ' + bgColor + '; color: #fff; padding: 0.75rem 1.25rem; border-radius: 0.375rem; margin-bottom: 0.5rem; font-size: 0.85rem; box-shadow: 0 4px 12px rgba(0,0,0,0.15);';
    toast.textContent = message;
    toastContainer.appendChild(toast);

    setTimeout(function() {
        toast.remove();
    }, 4000);
}
</script>
@endpush
