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
    border: 1px solid #e5e9f2;
    margin-bottom: 1.5rem;
    overflow: hidden;
}
.country-table-header {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #f8f9fc;
}
.country-table-header h6 {
    margin: 0;
    font-weight: 600;
    color: #1e3a5f;
}
.country-search-box {
    position: relative;
    width: 280px;
}
.country-search-box input {
    padding-left: 2.25rem;
    font-size: 0.85rem;
    border: 1px solid #ced4da;
    border-radius: 6px;
}
.country-search-box input:focus {
    border-color: #1e3a5f;
    box-shadow: 0 0 0 2px rgba(30, 58, 95, 0.1);
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
    border-collapse: collapse;
}
.country-table thead {
    background: #f8f9fc;
    border-bottom: 2px solid #e5e9f2;
}
.country-table th {
    padding: 0.875rem 0.75rem;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #1e3a5f;
    text-align: left;
    white-space: nowrap;
    cursor: pointer;
}
.country-table th:hover {
    background: #e9ecef;
}
.country-table th i.sort-icon {
    margin-left: 0.25rem;
    opacity: 0.5;
}
.country-table th.sorted i.sort-icon {
    opacity: 1;
    color: #1e3a5f;
}
.country-table td {
    padding: 0.75rem;
    font-size: 0.85rem;
    border-bottom: 1px solid #f0f0f0;
    vertical-align: middle;
}
.country-table tbody tr {
    transition: background 0.15s, box-shadow 0.15s;
}
.country-table tbody tr:hover {
    background: #f8f9fc;
    box-shadow: inset 3px 0 0 #1e3a5f;
}
.country-table tbody tr:last-child td {
    border-bottom: none;
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
    color: #1e3a5f;
}
.country-code {
    font-size: 0.75rem;
    color: #6c757d;
    font-family: monospace;
}
.dial-code {
    font-size: 0.85rem;
    color: #495057;
    font-family: monospace;
}
.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    font-size: 0.7rem;
    padding: 0.25rem 0.625rem;
    border-radius: 50px;
    font-weight: 600;
}
.status-badge.allowed {
    background: #d1fae5;
    color: #065f46;
}
.status-badge.blocked {
    background: #fee2e2;
    color: #991b1b;
}
.status-badge.restricted {
    background: #fef3c7;
    color: #92400e;
}
.status-badge.pending {
    background: #fef3c7;
    color: #92400e;
}
.source-of-truth-banner {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    padding: 1rem 1.25rem;
    background: linear-gradient(135deg, rgba(30, 58, 95, 0.08) 0%, rgba(30, 58, 95, 0.03) 100%);
    border: 1px solid rgba(30, 58, 95, 0.2);
    border-radius: 8px;
    margin-bottom: 1rem;
}
.source-of-truth-banner .sot-icon {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    background: #1e3a5f;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    flex-shrink: 0;
}
.source-of-truth-banner .sot-content h6 {
    margin: 0 0 0.25rem 0;
    font-weight: 600;
    color: #1e3a5f;
    font-size: 0.95rem;
}
.source-of-truth-banner .sot-content p {
    margin: 0;
    font-size: 0.85rem;
    color: #495057;
    line-height: 1.4;
}
.overrides-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.2rem 0.5rem;
    background: rgba(30, 58, 95, 0.1);
    color: #1e3a5f;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.15s;
}
.overrides-badge:hover {
    background: rgba(30, 58, 95, 0.2);
}
.overrides-badge.none {
    background: #f3f4f6;
    color: #9ca3af;
    cursor: default;
}
.overrides-badge i {
    font-size: 0.65rem;
}
.country-name-cell {
    display: flex;
    flex-direction: column;
    gap: 0.15rem;
}
.country-name-cell .country-name {
    font-weight: 600;
    color: #1e3a5f;
}
.country-name-cell .country-code {
    font-size: 0.7rem;
    color: #6c757d;
    font-family: monospace;
}
.action-menu {
    position: relative;
    display: inline-block;
}
.action-menu-btn {
    background: none;
    border: none;
    color: #6c757d;
    cursor: pointer;
    padding: 0.35rem 0.5rem;
    border-radius: 4px;
    transition: all 0.15s;
}
.action-menu-btn:hover {
    background: #f3f4f6;
    color: #1e3a5f;
}
.action-dropdown {
    position: absolute;
    right: 0;
    top: 100%;
    background: white;
    border: 1px solid #e9ecef;
    border-radius: 6px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    min-width: 160px;
    z-index: 100;
    display: none;
}
.action-dropdown.show {
    display: block;
}
.action-dropdown-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 0.75rem;
    cursor: pointer;
    font-size: 0.8rem;
    color: #374151;
    transition: background 0.15s;
}
.action-dropdown-item:hover {
    background: #f3f4f6;
}
.action-dropdown-item.approve i {
    color: #16a34a;
}
.action-dropdown-item.reject i {
    color: #dc2626;
}
.action-dropdown-item.view i {
    color: #1e3a5f;
}
.action-dropdown-section {
    padding: 0.4rem 0.75rem 0.25rem;
    font-size: 0.65rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #9ca3af;
}
.action-dropdown-divider {
    height: 1px;
    background: #e9ecef;
    margin: 0.35rem 0;
}
.action-dropdown-item:first-of-type {
    border-radius: 0;
}
.action-dropdown-item:last-child {
    border-radius: 0 0 6px 6px;
}
.overrides-modal-info {
    background: #f0f7ff;
    border-bottom: 1px solid #d1e3f6;
    padding: 0.75rem 1rem;
    font-size: 0.8rem;
    color: #1e3a5f;
}
#overridesTable thead th {
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
    font-size: 0.75rem;
    font-weight: 600;
    color: #1e3a5f;
    padding: 0.5rem 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.02em;
}
#overridesTable tbody td {
    padding: 0.6rem 0.75rem;
    font-size: 0.8rem;
    border-bottom: 1px solid #f1f3f5;
    vertical-align: middle;
}
#overridesTable tbody tr:hover {
    background: #f8f9fa;
}
.override-type-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.2rem 0.5rem;
    border-radius: 4px;
    font-size: 0.7rem;
    font-weight: 600;
}
.override-type-badge.allowed {
    background: #dcfce7;
    color: #16a34a;
}
.override-type-badge.blocked {
    background: #fee2e2;
    color: #dc2626;
}
.override-account-cell {
    display: flex;
    flex-direction: column;
    gap: 0.1rem;
}
.override-account-cell .account-name {
    font-weight: 600;
    color: #1e3a5f;
}
.override-account-cell .account-id {
    font-size: 0.7rem;
    color: #6c757d;
    font-family: monospace;
}
.override-admin {
    display: flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.75rem;
}
.override-admin i {
    color: #6c757d;
    font-size: 0.65rem;
}
.override-remove-btn {
    padding: 0.2rem 0.4rem;
    font-size: 0.7rem;
    border-radius: 4px;
}
.override-remove-btn:hover {
    background: #dc2626;
    border-color: #dc2626;
    color: white;
}
.account-typeahead-wrapper {
    position: relative;
}
.account-typeahead-wrapper input {
    padding-right: 2rem;
}
.account-typeahead-wrapper::after {
    content: '\f078';
    font-family: 'Font Awesome 5 Free';
    font-weight: 900;
    position: absolute;
    right: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    color: #6c757d;
    font-size: 0.7rem;
    pointer-events: none;
}
.typeahead-results {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #e9ecef;
    border-top: none;
    border-radius: 0 0 6px 6px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    max-height: 250px;
    overflow-y: auto;
    z-index: 1000;
    display: none;
}
.typeahead-results.show {
    display: block;
}
.typeahead-item {
    padding: 0.5rem 0.75rem;
    cursor: pointer;
    border-bottom: 1px solid #f1f3f5;
    transition: background 0.15s;
}
.typeahead-item:hover {
    background: #f8f9fa;
}
.typeahead-item:last-child {
    border-bottom: none;
}
.typeahead-item .account-name {
    font-weight: 600;
    color: #1e3a5f;
}
.typeahead-item .account-id {
    font-size: 0.75rem;
    color: #6c757d;
    font-family: monospace;
}
.typeahead-item .account-status {
    font-size: 0.65rem;
    padding: 0.1rem 0.35rem;
    border-radius: 3px;
    margin-left: 0.5rem;
}
.typeahead-item .account-status.live {
    background: #dcfce7;
    color: #16a34a;
}
.typeahead-item .account-status.test {
    background: #fef3c7;
    color: #d97706;
}
.typeahead-no-results {
    padding: 0.75rem;
    text-align: center;
    color: #6c757d;
    font-size: 0.8rem;
}
.selected-account-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: #e0f2fe;
    border: 1px solid #7dd3fc;
    padding: 0.4rem 0.6rem;
    border-radius: 6px;
    margin-top: 0.5rem;
}
.selected-account-badge .account-info {
    font-size: 0.8rem;
    color: #1e3a5f;
    font-weight: 500;
}
.selected-account-badge .clear-selection {
    background: none;
    border: none;
    color: #6c757d;
    cursor: pointer;
    padding: 0.1rem 0.25rem;
    border-radius: 3px;
    transition: all 0.15s;
}
.selected-account-badge .clear-selection:hover {
    background: #dc2626;
    color: white;
}
.override-type-radios {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}
.override-type-option {
    display: block;
    cursor: pointer;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    padding: 0.75rem;
    transition: all 0.15s;
}
.override-type-option:hover {
    border-color: #1e3a5f;
}
.override-type-option input[type="radio"] {
    display: none;
}
.override-type-option.allowed input:checked ~ .option-content {
    color: #16a34a;
}
.override-type-option.allowed input:checked ~ .option-content i {
    color: #16a34a;
}
.override-type-option.blocked input:checked ~ .option-content {
    color: #dc2626;
}
.override-type-option.blocked input:checked ~ .option-content i {
    color: #dc2626;
}
.override-type-option input:checked ~ .option-content {
    font-weight: 600;
}
.override-type-option.allowed:has(input:checked) {
    border-color: #16a34a;
    background: #f0fdf4;
}
.override-type-option.blocked:has(input:checked) {
    border-color: #dc2626;
    background: #fef2f2;
}
.option-content {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}
.option-content i {
    font-size: 1.25rem;
    color: #9ca3af;
}
.option-text {
    display: flex;
    flex-direction: column;
    gap: 0.1rem;
}
.option-title {
    font-size: 0.85rem;
}
.option-desc {
    font-size: 0.7rem;
    color: #6c757d;
    font-weight: 400;
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
.filter-toggle-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 0.75rem;
    background: transparent;
    border: 1px solid #1e3a5f;
    border-radius: 6px;
    color: #1e3a5f;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}
.filter-toggle-btn:hover {
    background: rgba(30, 58, 95, 0.08);
}
.filter-toggle-btn i {
    transition: transform 0.2s ease;
}
.filter-toggle-btn[aria-expanded="true"] i.fa-chevron-down {
    transform: rotate(180deg);
}
.filter-toggle-header {
    display: flex;
    justify-content: flex-end;
    margin-bottom: 0.75rem;
}
.filter-panel-collapsible {
    border: 1px solid #e5e9f2;
    border-radius: 8px;
    background: #f8f9fc;
    padding: 1rem 1.25rem;
    margin-bottom: 1.5rem;
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

            <div class="filter-toggle-header">
                <button class="filter-toggle-btn" type="button" data-bs-toggle="collapse" data-bs-target="#reviewFiltersCollapse" aria-expanded="false" aria-controls="reviewFiltersCollapse">
                    <i class="fas fa-filter"></i>
                    Filters
                    <i class="fas fa-chevron-down"></i>
                </button>
            </div>
            <div class="collapse" id="reviewFiltersCollapse">
                <div class="filter-panel-collapsible">
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
                                <th onclick="sortCountries('name')" class="sortable">
                                    Country Name <i class="fas fa-sort sort-icon"></i>
                                </th>
                                <th>Default Status</th>
                                <th>Customer Overrides</th>
                                <th style="width: 80px;">Actions</th>
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

<div class="modal fade" id="approvalConfirmModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: #16a34a; color: #fff;">
                <h5 class="modal-title"><i class="fas fa-check-circle me-2"></i>Approve Request</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-light border mb-3">
                    <div class="small text-muted">Approving request for:</div>
                    <div><strong id="approvalCustomerName"></strong> &rarr; <strong id="approvalCountryName"></strong></div>
                </div>
                
                <div class="alert alert-info mb-0" style="background: rgba(30, 58, 95, 0.1); border-color: #1e3a5f; color: #1e3a5f;">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>What will happen:</strong>
                    <ul class="mb-0 mt-2" style="padding-left: 1.2rem;">
                        <li>An account-level override will be created allowing this customer to send to this country</li>
                        <li>Global country policy will NOT be changed</li>
                        <li>The customer will be notified via their portal</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="confirmApproveBtn">
                    <i class="fas fa-check-circle me-1"></i>Confirm Approval
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="rejectionReasonModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: #dc2626; color: #fff;">
                <h5 class="modal-title"><i class="fas fa-times-circle me-2"></i>Reject Request</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-light border mb-3">
                    <div class="small text-muted">Rejecting request for:</div>
                    <div><strong id="rejectionCustomerName"></strong> &rarr; <strong id="rejectionCountryName"></strong></div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Rejection Reason <span class="text-danger">*</span></label>
                    <select class="form-select" id="rejectionReasonCategory" onchange="validateRejectionForm()">
                        <option value="">Select a reason...</option>
                        <option value="Country blocked by policy">Country blocked by policy</option>
                        <option value="Insufficient business justification">Insufficient business justification</option>
                        <option value="Account not eligible">Account not eligible (Test/Suspended status)</option>
                        <option value="Regulatory compliance">Regulatory compliance concerns</option>
                        <option value="High fraud risk">High fraud risk destination</option>
                        <option value="Volume exceeds limits">Requested volume exceeds account limits</option>
                        <option value="Missing documentation">Missing required documentation</option>
                        <option value="Other">Other (specify below)</option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Additional Details <span class="text-muted fw-normal">(Optional)</span></label>
                    <textarea class="form-control" id="rejectionReasonText" rows="3" placeholder="Provide additional context for the customer..."></textarea>
                    <div class="form-text">This message will be visible to the customer in their portal notification.</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmRejectBtn" disabled>
                    <i class="fas fa-times-circle me-1"></i>Confirm Rejection
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="removeOverrideConfirmModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: #dc2626; color: #fff;">
                <h5 class="modal-title"><i class="fas fa-trash-alt me-2"></i>Remove Override</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-light border mb-3">
                    <div class="small text-muted">Removing override for:</div>
                    <div><strong id="removeOverrideAccountName"></strong></div>
                    <div class="mt-1">
                        <span class="badge" id="removeOverrideTypeBadge"></span>
                        <span class="text-muted ms-2">&rarr;</span>
                        <strong class="ms-2" id="removeOverrideCountryName"></strong>
                    </div>
                </div>
                
                <div class="alert alert-warning mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>What will happen:</strong>
                    <ul class="mb-0 mt-2" style="padding-left: 1.2rem;">
                        <li>This account-level override will be permanently removed</li>
                        <li>The account will revert to the global default status: <strong id="removeOverrideGlobalStatus"></strong></li>
                        <li>This action will be logged in the audit trail</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmRemoveOverrideBtn" onclick="executeRemoveOverride()">
                    <i class="fas fa-trash-alt me-1"></i>Remove Override
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

<div class="modal fade" id="defaultStatusConfirmModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" id="defaultStatusModalHeader">
                <h5 class="modal-title" id="defaultStatusModalTitle">
                    <i class="fas fa-globe me-2"></i>Change Default Status
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label text-muted small">Country</label>
                    <div class="fw-bold" id="defaultStatusCountryName"></div>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small">New Default Status</label>
                    <div id="defaultStatusNewStatus"></div>
                </div>
                <div class="alert mb-3" id="defaultStatusDescription"></div>
                <div class="alert alert-warning mb-0" id="defaultStatusOverrideWarning" style="display: none;">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Note:</strong> <span id="overrideCountText"></span> account override(s) exist for this country. 
                    These overrides will remain active and continue to take precedence over the default status unless explicitly removed.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn" id="confirmDefaultStatusBtn" onclick="confirmDefaultStatusChange()">
                    <i class="fas fa-check me-1"></i>Confirm Change
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addOverrideModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: #1e3a5f; color: #fff;">
                <h5 class="modal-title">
                    <i class="fas fa-plus-circle me-2"></i>Add Account Override
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label text-muted small">Country</label>
                    <div class="fw-bold" id="addOverrideCountryName"></div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Search Account <span class="text-danger">*</span></label>
                    <div class="account-typeahead-wrapper">
                        <input type="text" class="form-control" id="addOverrideAccountSearch" 
                               placeholder="Type to search accounts..." autocomplete="off">
                        <div class="typeahead-results" id="accountTypeaheadResults"></div>
                        <input type="hidden" id="addOverrideAccountId">
                        <input type="hidden" id="addOverrideAccountName">
                    </div>
                    <div id="selectedAccountDisplay" class="selected-account-badge" style="display: none;">
                        <span class="account-info"></span>
                        <button type="button" class="clear-selection" onclick="clearAccountSelection()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="mb-3" id="subAccountSection" style="display: none;">
                    <label class="form-label fw-bold">Sub-Account <span class="text-muted fw-normal">(Optional)</span></label>
                    <select class="form-select" id="addOverrideSubAccount">
                        <option value="">Apply to entire account</option>
                    </select>
                    <div class="form-text text-muted small">Leave blank to apply override at the account level.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Override Type <span class="text-danger">*</span></label>
                    <div class="override-type-radios">
                        <label class="override-type-option allowed">
                            <input type="radio" name="overrideType" value="allowed" checked>
                            <div class="option-content">
                                <i class="fas fa-check-circle"></i>
                                <div class="option-text">
                                    <span class="option-title">Allow this country</span>
                                    <span class="option-desc">Account can send messages to this destination</span>
                                </div>
                            </div>
                        </label>
                        <label class="override-type-option blocked">
                            <input type="radio" name="overrideType" value="blocked">
                            <div class="option-content">
                                <i class="fas fa-ban"></i>
                                <div class="option-text">
                                    <span class="option-title">Block this country</span>
                                    <span class="option-desc">Account cannot send messages to this destination</span>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>
                <div class="alert alert-info small mb-0">
                    <i class="fas fa-info-circle me-1"></i>
                    This override will apply immediately and take precedence over the global default status.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn admin-btn-primary" id="confirmAddOverrideBtn" onclick="confirmAddOverride()" disabled>
                    <i class="fas fa-plus me-1"></i>Add Override
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="removeOverrideModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: #dc2626; color: #fff;">
                <h5 class="modal-title">
                    <i class="fas fa-minus-circle me-2"></i>Remove Account Override
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label text-muted small">Country</label>
                    <div class="fw-bold" id="removeOverrideCountryName"></div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Select Override to Remove <span class="text-danger">*</span></label>
                    <select class="form-select" id="removeOverrideSelect">
                        <option value="">Choose an override...</option>
                    </select>
                </div>
                <div class="alert alert-warning small mb-0">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    After removal, the account will follow the global default status for this country.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="confirmRemoveOverride()">
                    <i class="fas fa-trash me-1"></i>Remove Override
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="customerOverridesModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header" style="background: #1e3a5f; color: #fff;">
                <h5 class="modal-title">
                    <i class="fas fa-users me-2"></i>Customer Overrides
                    <span id="overridesModalCountryName" class="ms-2 fw-normal" style="opacity: 0.8;"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="overrides-modal-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <span>These account-level overrides take precedence over the global default status for this country.</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="overridesTable">
                        <thead>
                            <tr>
                                <th>Account Name</th>
                                <th>Sub Account</th>
                                <th>Override Type</th>
                                <th>Date Applied</th>
                                <th>Applied By</th>
                                <th style="width: 60px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="overridesTableBody">
                        </tbody>
                    </table>
                </div>
                <div id="noOverridesMessage" class="text-center py-4 text-muted" style="display: none;">
                    <i class="fas fa-inbox fa-2x mb-2 d-block" style="opacity: 0.5;"></i>
                    No account overrides exist for this country.
                </div>
            </div>
            <div class="modal-footer">
                <small class="text-muted me-auto">
                    <i class="fas fa-info-circle me-1"></i>Click the <i class="fas fa-trash-alt text-danger"></i> button to remove an override.
                </small>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    initCountryControls();
    initAccountTypeahead();
    
    // Bind confirm approval button click handler
    var confirmApproveBtn = document.getElementById('confirmApproveBtn');
    if (confirmApproveBtn) {
        confirmApproveBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            confirmApproval();
        });
    }
    
    // Bind confirm rejection button click handler
    var confirmRejectBtn = document.getElementById('confirmRejectBtn');
    if (confirmRejectBtn) {
        confirmRejectBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            confirmRejection();
        });
    }
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

var CountryPrecedenceService = (function() {
    var PRECEDENCE_RULES = {
        ACCOUNT_OVERRIDE_TAKES_PRECEDENCE: true,
        GLOBAL_DEFAULT_IS_FALLBACK: true,
        NO_SILENT_MUTATIONS: true
    };

    var MUTATION_TYPES = {
        GLOBAL_DEFAULT_CHANGE: 'GLOBAL_DEFAULT_CHANGE',
        ACCOUNT_OVERRIDE_ADD: 'ACCOUNT_OVERRIDE_ADD',
        ACCOUNT_OVERRIDE_REMOVE: 'ACCOUNT_OVERRIDE_REMOVE',
        ACCOUNT_OVERRIDE_UPDATE: 'ACCOUNT_OVERRIDE_UPDATE'
    };

    function getEffectiveStatus(countryCode, accountId, subAccountId) {
        var globalDefault = getGlobalDefault(countryCode);
        var accountOverride = getAccountOverride(countryCode, accountId, subAccountId);

        if (accountOverride !== null) {
            return {
                status: accountOverride.overrideType,
                source: 'ACCOUNT_OVERRIDE',
                precedence: 1,
                details: {
                    accountId: accountId,
                    subAccountId: subAccountId,
                    appliedBy: accountOverride.appliedBy,
                    appliedAt: accountOverride.dateApplied
                }
            };
        }

        return {
            status: globalDefault,
            source: 'GLOBAL_DEFAULT',
            precedence: 2,
            details: null
        };
    }

    function getGlobalDefault(countryCode) {
        var country = countries.find(function(c) { return c.code === countryCode; });
        return country ? country.status : 'blocked';
    }

    function getAccountOverride(countryCode, accountId, subAccountId) {
        var overrides = mockOverridesData[countryCode] || [];
        
        if (subAccountId) {
            var subOverride = overrides.find(function(o) {
                return o.accountId === accountId && o.subAccount === subAccountId;
            });
            if (subOverride) return subOverride;
        }

        var accountOverride = overrides.find(function(o) {
            return o.accountId === accountId && !o.subAccount;
        });
        
        return accountOverride || null;
    }

    function canSendToCountry(countryCode, accountId, subAccountId) {
        var effective = getEffectiveStatus(countryCode, accountId, subAccountId);
        return {
            allowed: effective.status === 'allowed',
            effectiveStatus: effective.status,
            source: effective.source,
            reason: buildReason(effective)
        };
    }

    function buildReason(effective) {
        if (effective.source === 'ACCOUNT_OVERRIDE') {
            if (effective.status === 'allowed') {
                return 'Allowed via account-level override (takes precedence over global default)';
            } else {
                return 'Blocked via account-level override (takes precedence over global default)';
            }
        } else {
            if (effective.status === 'allowed') {
                return 'Allowed by global default (no account override exists)';
            } else {
                return 'Blocked by global default (no account override exists)';
            }
        }
    }

    function validateMutation(mutationType, data, callback) {
        if (!PRECEDENCE_RULES.NO_SILENT_MUTATIONS) {
            callback(null);
            return;
        }

        var confirmationRequired = true;
        var confirmationMessage = '';
        var auditEventType = '';

        switch (mutationType) {
            case MUTATION_TYPES.GLOBAL_DEFAULT_CHANGE:
                confirmationMessage = buildGlobalChangeConfirmation(data);
                auditEventType = 'COUNTRY_DEFAULT_STATUS_CHANGED';
                break;
            case MUTATION_TYPES.ACCOUNT_OVERRIDE_ADD:
                confirmationMessage = buildAddOverrideConfirmation(data);
                auditEventType = 'COUNTRY_OVERRIDE_ADDED';
                break;
            case MUTATION_TYPES.ACCOUNT_OVERRIDE_REMOVE:
                confirmationMessage = buildRemoveOverrideConfirmation(data);
                auditEventType = 'COUNTRY_OVERRIDE_REMOVED';
                break;
            default:
                confirmationRequired = false;
        }

        if (!confirmationRequired) {
            callback(null);
            return;
        }

        return {
            requiresConfirmation: true,
            confirmationMessage: confirmationMessage,
            auditEventType: auditEventType,
            proceed: function() {
                callback({
                    auditEventType: auditEventType,
                    timestamp: new Date().toISOString(),
                    mutationType: mutationType,
                    data: data
                });
            }
        };
    }

    function buildGlobalChangeConfirmation(data) {
        var overrideCount = (mockOverridesData[data.countryCode] || []).length;
        var msg = 'Change global default for ' + data.countryName + ' to ' + 
                  (data.newStatus === 'allowed' ? 'ALLOWED' : 'BLOCKED') + '?\n\n';
        
        if (data.newStatus === 'allowed') {
            msg += 'Effect: All customers can send to this country without needing approval.';
        } else {
            msg += 'Effect: No customer can send to this country unless they have an explicit account override.';
        }

        if (overrideCount > 0) {
            msg += '\n\nNote: ' + overrideCount + ' account override(s) exist. ';
            msg += 'These overrides will CONTINUE to take precedence over this global default.';
        }

        return msg;
    }

    function buildAddOverrideConfirmation(data) {
        var country = countries.find(function(c) { return c.code === data.countryCode; });
        var globalDefault = country ? country.status : 'blocked';
        
        var msg = 'Add override for ' + data.accountName + '?\n\n';
        msg += 'Override Type: ' + (data.overrideType === 'allowed' ? 'ALLOWED' : 'BLOCKED') + '\n';
        msg += 'Country: ' + data.countryName + '\n\n';
        
        if (data.overrideType !== globalDefault) {
            msg += 'This override DIFFERS from the global default (' + globalDefault.toUpperCase() + '). ';
            msg += 'The account override will take precedence.';
        } else {
            msg += 'This override MATCHES the global default. ';
            msg += 'It provides explicit protection if the global default changes.';
        }

        return msg;
    }

    function buildRemoveOverrideConfirmation(data) {
        var country = countries.find(function(c) { return c.code === data.countryCode; });
        var globalDefault = country ? country.status : 'blocked';
        
        var msg = 'Remove override for ' + data.accountName + '?\n\n';
        msg += 'Current Override: ' + (data.currentOverrideType === 'allowed' ? 'ALLOWED' : 'BLOCKED') + '\n\n';
        msg += 'After removal, this account will follow the GLOBAL DEFAULT:\n';
        msg += '→ ' + data.countryName + ' is currently ' + globalDefault.toUpperCase() + ' by default.\n\n';
        
        if (data.currentOverrideType !== globalDefault) {
            msg += '⚠️ WARNING: This will change the effective status for this account!';
        }

        return msg;
    }

    function logPrecedenceDecision(countryCode, accountId, decision) {
        console.log('[CountryPrecedence] Decision:', {
            countryCode: countryCode,
            accountId: accountId,
            effectiveStatus: decision.effectiveStatus,
            source: decision.source,
            reason: decision.reason,
            timestamp: new Date().toISOString()
        });
    }

    function getPrecedenceExplanation() {
        return {
            rules: [
                '1. Account-level overrides ALWAYS take precedence over global defaults.',
                '2. Sub-account overrides take precedence over account-level overrides.',
                '3. If no override exists, the global default applies.',
                '4. A country can be BLOCKED globally but ALLOWED for specific accounts.',
                '5. A country can be ALLOWED globally but BLOCKED for specific accounts.',
                '6. All state changes require confirmation and generate audit events.'
            ],
            hierarchy: [
                { level: 1, name: 'Sub-Account Override', description: 'Most specific - applies to single sub-account' },
                { level: 2, name: 'Account Override', description: 'Applies to entire account and all sub-accounts (unless sub-account override exists)' },
                { level: 3, name: 'Global Default', description: 'Fallback - applies when no overrides exist' }
            ]
        };
    }

    return {
        MUTATION_TYPES: MUTATION_TYPES,
        getEffectiveStatus: getEffectiveStatus,
        canSendToCountry: canSendToCountry,
        validateMutation: validateMutation,
        getPrecedenceExplanation: getPrecedenceExplanation,
        logPrecedenceDecision: logPrecedenceDecision
    };
})();

window.CountryPrecedenceService = CountryPrecedenceService;
console.log('[CountryPrecedenceService] Initialized with rules:', CountryPrecedenceService.getPrecedenceExplanation().rules);

var SharedPolicyStore = (function() {
    var STORE_KEY = 'QUICKSMS_COUNTRY_POLICY_STORE';
    var VERSION_KEY = 'QUICKSMS_POLICY_VERSION';
    
    var policyStore = {
        version: 1,
        lastUpdated: new Date().toISOString(),
        globalDefaults: {},
        accountOverrides: {},
        pendingRequests: {}
    };

    function initialize() {
        countries.forEach(function(country) {
            policyStore.globalDefaults[country.code] = {
                status: country.status,
                lastUpdated: country.lastUpdated,
                updatedBy: 'system'
            };
        });

        Object.keys(mockOverridesData).forEach(function(countryCode) {
            policyStore.accountOverrides[countryCode] = mockOverridesData[countryCode].map(function(override) {
                return {
                    accountId: override.accountId,
                    accountName: override.accountName,
                    subAccount: override.subAccount,
                    overrideType: override.overrideType,
                    appliedAt: override.dateApplied,
                    appliedBy: override.appliedBy
                };
            });
        });

        mockReviewData.forEach(function(request) {
            var key = request.accountId + ':' + request.countryCode;
            policyStore.pendingRequests[key] = {
                requestId: request.id,
                accountId: request.accountId,
                accountName: request.accountName,
                subAccountId: request.subAccountId,
                subAccountName: request.subAccountName,
                countryCode: request.countryCode,
                countryName: request.countryName,
                status: request.status,
                submittedAt: request.submittedAt,
                reason: request.reason
            };
        });

        console.log('[SharedPolicyStore] Initialized with', Object.keys(policyStore.globalDefaults).length, 'countries');
    }

    function getGlobalDefault(countryCode) {
        return policyStore.globalDefaults[countryCode] || { status: 'blocked', lastUpdated: null };
    }

    function setGlobalDefault(countryCode, status, adminUser) {
        var before = policyStore.globalDefaults[countryCode];
        policyStore.globalDefaults[countryCode] = {
            status: status,
            lastUpdated: new Date().toISOString(),
            updatedBy: adminUser.email
        };
        policyStore.version++;
        policyStore.lastUpdated = new Date().toISOString();

        broadcastUpdate('GLOBAL_DEFAULT_CHANGED', { countryCode: countryCode, before: before, after: policyStore.globalDefaults[countryCode] });
        return policyStore.globalDefaults[countryCode];
    }

    function getAccountOverrides(countryCode, accountId) {
        var countryOverrides = policyStore.accountOverrides[countryCode] || [];
        if (accountId) {
            return countryOverrides.filter(function(o) { return o.accountId === accountId; });
        }
        return countryOverrides;
    }

    function addAccountOverride(countryCode, override, adminUser) {
        if (!policyStore.accountOverrides[countryCode]) {
            policyStore.accountOverrides[countryCode] = [];
        }
        
        var newOverride = {
            accountId: override.accountId,
            accountName: override.accountName,
            subAccount: override.subAccount || null,
            overrideType: override.overrideType,
            appliedAt: new Date().toISOString(),
            appliedBy: adminUser.email
        };
        
        policyStore.accountOverrides[countryCode].push(newOverride);
        policyStore.version++;
        policyStore.lastUpdated = new Date().toISOString();

        broadcastUpdate('ACCOUNT_OVERRIDE_ADDED', { countryCode: countryCode, override: newOverride });
        return newOverride;
    }

    function removeAccountOverride(countryCode, accountId, subAccount, adminUser) {
        var countryOverrides = policyStore.accountOverrides[countryCode] || [];
        var index = countryOverrides.findIndex(function(o) {
            return o.accountId === accountId && o.subAccount === subAccount;
        });
        
        if (index !== -1) {
            var removed = countryOverrides.splice(index, 1)[0];
            policyStore.version++;
            policyStore.lastUpdated = new Date().toISOString();
            broadcastUpdate('ACCOUNT_OVERRIDE_REMOVED', { countryCode: countryCode, override: removed });
            return removed;
        }
        return null;
    }

    function getPendingRequest(accountId, countryCode) {
        var key = accountId + ':' + countryCode;
        return policyStore.pendingRequests[key] || null;
    }

    function getPendingRequestsForAccount(accountId) {
        return Object.values(policyStore.pendingRequests).filter(function(req) {
            return req.accountId === accountId;
        });
    }

    function addPendingRequest(request) {
        var key = request.accountId + ':' + request.countryCode;
        policyStore.pendingRequests[key] = request;
        policyStore.version++;
        broadcastUpdate('PENDING_REQUEST_ADDED', { request: request });
    }

    function updatePendingRequest(accountId, countryCode, updates) {
        var key = accountId + ':' + countryCode;
        if (policyStore.pendingRequests[key]) {
            Object.assign(policyStore.pendingRequests[key], updates);
            policyStore.version++;
            broadcastUpdate('PENDING_REQUEST_UPDATED', { key: key, updates: updates });
        }
    }

    function removePendingRequest(accountId, countryCode) {
        var key = accountId + ':' + countryCode;
        if (policyStore.pendingRequests[key]) {
            var removed = policyStore.pendingRequests[key];
            delete policyStore.pendingRequests[key];
            policyStore.version++;
            broadcastUpdate('PENDING_REQUEST_REMOVED', { request: removed });
            return removed;
        }
        return null;
    }

    function getStoreVersion() {
        return policyStore.version;
    }

    function broadcastUpdate(eventType, data) {
        var event = new CustomEvent('policyStoreUpdate', {
            detail: {
                eventType: eventType,
                data: data,
                version: policyStore.version,
                timestamp: new Date().toISOString()
            }
        });
        window.dispatchEvent(event);
        console.log('[SharedPolicyStore] Broadcast:', eventType, 'v' + policyStore.version);
    }

    return {
        initialize: initialize,
        getGlobalDefault: getGlobalDefault,
        setGlobalDefault: setGlobalDefault,
        getAccountOverrides: getAccountOverrides,
        addAccountOverride: addAccountOverride,
        removeAccountOverride: removeAccountOverride,
        getPendingRequest: getPendingRequest,
        getPendingRequestsForAccount: getPendingRequestsForAccount,
        addPendingRequest: addPendingRequest,
        updatePendingRequest: updatePendingRequest,
        removePendingRequest: removePendingRequest,
        getStoreVersion: getStoreVersion
    };
})();

var CustomerPortalCountryService = (function() {
    var VISIBILITY_RULES = {
        canViewGlobalCatalogue: false,
        canApproveOwnRequests: false,
        canSeeOtherCustomers: false,
        canViewOwnOverrides: true,
        canSubmitAccessRequests: true,
        canViewPendingRequests: true
    };

    function getAvailableCountriesForAccount(accountId, subAccountId) {
        var result = [];
        
        countries.forEach(function(country) {
            var globalDefault = SharedPolicyStore.getGlobalDefault(country.code);
            var accountOverrides = SharedPolicyStore.getAccountOverrides(country.code, accountId);
            var pendingRequest = SharedPolicyStore.getPendingRequest(accountId, country.code);

            var effectiveOverride = null;
            if (subAccountId) {
                effectiveOverride = accountOverrides.find(function(o) {
                    return o.subAccount === subAccountId;
                });
            }
            if (!effectiveOverride) {
                effectiveOverride = accountOverrides.find(function(o) {
                    return !o.subAccount;
                });
            }

            var effectiveStatus = effectiveOverride ? effectiveOverride.overrideType : globalDefault.status;
            var statusSource = effectiveOverride ? 'ACCOUNT_OVERRIDE' : 'GLOBAL_DEFAULT';

            var requestStatus = null;
            if (pendingRequest) {
                requestStatus = pendingRequest.status;
            }

            result.push({
                countryCode: country.code,
                countryName: country.name,
                dialCode: country.dialCode,
                effectiveStatus: effectiveStatus,
                statusSource: statusSource,
                hasOverride: !!effectiveOverride,
                overrideDetails: effectiveOverride ? {
                    type: effectiveOverride.overrideType,
                    appliedAt: effectiveOverride.appliedAt,
                    scope: effectiveOverride.subAccount ? 'sub-account' : 'account'
                } : null,
                pendingRequest: requestStatus ? {
                    status: requestStatus,
                    submittedAt: pendingRequest.submittedAt
                } : null,
                canSend: effectiveStatus === 'allowed',
                canRequestAccess: effectiveStatus === 'blocked' && !pendingRequest
            });
        });

        return result;
    }

    function getCountryStatusForCustomer(countryCode, accountId, subAccountId) {
        var globalDefault = SharedPolicyStore.getGlobalDefault(countryCode);
        var accountOverrides = SharedPolicyStore.getAccountOverrides(countryCode, accountId);
        var pendingRequest = SharedPolicyStore.getPendingRequest(accountId, countryCode);

        var effectiveOverride = null;
        if (subAccountId) {
            effectiveOverride = accountOverrides.find(function(o) {
                return o.subAccount === subAccountId;
            });
        }
        if (!effectiveOverride) {
            effectiveOverride = accountOverrides.find(function(o) {
                return !o.subAccount;
            });
        }

        var effectiveStatus = effectiveOverride ? effectiveOverride.overrideType : globalDefault.status;

        return {
            countryCode: countryCode,
            effectiveStatus: effectiveStatus,
            canSend: effectiveStatus === 'allowed',
            statusExplanation: buildCustomerExplanation(effectiveStatus, !!effectiveOverride, pendingRequest),
            pendingRequest: pendingRequest ? {
                status: pendingRequest.status,
                submittedAt: pendingRequest.submittedAt
            } : null
        };
    }

    function buildCustomerExplanation(status, hasOverride, pendingRequest) {
        if (pendingRequest && pendingRequest.status === 'pending') {
            return 'Your access request is pending admin review.';
        }
        if (pendingRequest && pendingRequest.status === 'rejected') {
            return 'Your access request was not approved. Contact support for more information.';
        }
        if (status === 'allowed') {
            if (hasOverride) {
                return 'You have been granted access to send to this country.';
            }
            return 'This country is available for messaging.';
        }
        return 'This country requires approval. Submit an access request to enable messaging.';
    }

    function submitAccessRequest(accountId, accountName, subAccountId, subAccountName, countryCode, reason) {
        if (!VISIBILITY_RULES.canSubmitAccessRequests) {
            return { success: false, error: 'Access requests are not enabled for your account.' };
        }

        var existingRequest = SharedPolicyStore.getPendingRequest(accountId, countryCode);
        if (existingRequest && existingRequest.status === 'pending') {
            return { success: false, error: 'A pending request already exists for this country.' };
        }

        var country = countries.find(function(c) { return c.code === countryCode; });
        if (!country) {
            return { success: false, error: 'Invalid country code.' };
        }

        var request = {
            requestId: 'REQ-' + Date.now(),
            accountId: accountId,
            accountName: accountName,
            subAccountId: subAccountId,
            subAccountName: subAccountName,
            countryCode: countryCode,
            countryName: country.name,
            status: 'pending',
            submittedAt: new Date().toISOString(),
            reason: reason
        };

        SharedPolicyStore.addPendingRequest(request);

        CountryReviewAuditService.emit('COUNTRY_REQUEST_SUBMITTED', {
            accountId: accountId,
            countryIso: countryCode,
            countryName: country.name,
            reason: reason,
            result: 'request_submitted'
        }, { emitToCustomerAudit: true });

        return { success: true, requestId: request.requestId };
    }

    function getVisibilityRules() {
        return JSON.parse(JSON.stringify(VISIBILITY_RULES));
    }

    function validateCustomerAccess(accountId, requestedAccountId) {
        if (accountId !== requestedAccountId) {
            console.warn('[CustomerPortalCountryService] Access denied: Account', accountId, 'attempted to access', requestedAccountId);
            return false;
        }
        return true;
    }

    return {
        getAvailableCountriesForAccount: getAvailableCountriesForAccount,
        getCountryStatusForCustomer: getCountryStatusForCustomer,
        submitAccessRequest: submitAccessRequest,
        getVisibilityRules: getVisibilityRules,
        validateCustomerAccess: validateCustomerAccess,
        VISIBILITY_RULES: VISIBILITY_RULES
    };
})();

SharedPolicyStore.initialize();
window.SharedPolicyStore = SharedPolicyStore;
window.CustomerPortalCountryService = CustomerPortalCountryService;

console.log('[CustomerPortalCountryService] Initialized with visibility rules:', CustomerPortalCountryService.getVisibilityRules());
console.log('[SharedPolicyStore] Policy store version:', SharedPolicyStore.getStoreVersion());

var CountryControlsPermissions = (function() {
    var ADMIN_ROLES = {
        SUPER_ADMIN: 'super_admin',
        SECURITY_ADMIN: 'security_admin',
        COMPLIANCE_OFFICER: 'compliance_officer',
        SUPPORT_AGENT: 'support_agent',
        READ_ONLY: 'read_only'
    };

    var PERMISSION_DEFINITIONS = {
        'country_controls.view': {
            description: 'View Country Controls module',
            allowedRoles: [ADMIN_ROLES.SUPER_ADMIN, ADMIN_ROLES.SECURITY_ADMIN, ADMIN_ROLES.COMPLIANCE_OFFICER, ADMIN_ROLES.SUPPORT_AGENT, ADMIN_ROLES.READ_ONLY]
        },
        'country_controls.review_requests': {
            description: 'Review and action country access requests',
            allowedRoles: [ADMIN_ROLES.SUPER_ADMIN, ADMIN_ROLES.SECURITY_ADMIN, ADMIN_ROLES.COMPLIANCE_OFFICER]
        },
        'country_controls.approve_requests': {
            description: 'Approve country access requests',
            allowedRoles: [ADMIN_ROLES.SUPER_ADMIN, ADMIN_ROLES.SECURITY_ADMIN]
        },
        'country_controls.reject_requests': {
            description: 'Reject country access requests',
            allowedRoles: [ADMIN_ROLES.SUPER_ADMIN, ADMIN_ROLES.SECURITY_ADMIN, ADMIN_ROLES.COMPLIANCE_OFFICER]
        },
        'country_controls.change_global_default': {
            description: 'Change global default status for countries',
            allowedRoles: [ADMIN_ROLES.SUPER_ADMIN]
        },
        'country_controls.add_override': {
            description: 'Add account-level country overrides',
            allowedRoles: [ADMIN_ROLES.SUPER_ADMIN, ADMIN_ROLES.SECURITY_ADMIN]
        },
        'country_controls.remove_override': {
            description: 'Remove account-level country overrides',
            allowedRoles: [ADMIN_ROLES.SUPER_ADMIN, ADMIN_ROLES.SECURITY_ADMIN]
        },
        'country_controls.view_audit_log': {
            description: 'View country controls audit log',
            allowedRoles: [ADMIN_ROLES.SUPER_ADMIN, ADMIN_ROLES.SECURITY_ADMIN, ADMIN_ROLES.COMPLIANCE_OFFICER]
        }
    };

    var currentUser = {
        id: 'admin-001',
        email: 'admin@quicksms.co.uk',
        role: ADMIN_ROLES.SUPER_ADMIN,
        permissions: []
    };

    function initialize(user) {
        if (user) {
            currentUser = user;
        }
        currentUser.permissions = derivePermissions(currentUser.role);
        console.log('[CountryControlsPermissions] Initialized for:', currentUser.email, 'Role:', currentUser.role);
        console.log('[CountryControlsPermissions] Permissions:', currentUser.permissions);
    }

    function derivePermissions(role) {
        var permissions = [];
        Object.keys(PERMISSION_DEFINITIONS).forEach(function(perm) {
            if (PERMISSION_DEFINITIONS[perm].allowedRoles.includes(role)) {
                permissions.push(perm);
            }
        });
        return permissions;
    }

    function hasPermission(permission) {
        if (currentUser.role === ADMIN_ROLES.SUPER_ADMIN) {
            return true;
        }
        return currentUser.permissions.includes(permission);
    }

    function canView() {
        return hasPermission('country_controls.view');
    }

    function canReviewRequests() {
        return hasPermission('country_controls.review_requests');
    }

    function canApproveRequests() {
        return hasPermission('country_controls.approve_requests');
    }

    function canRejectRequests() {
        return hasPermission('country_controls.reject_requests');
    }

    function canChangeGlobalDefault() {
        return hasPermission('country_controls.change_global_default');
    }

    function canAddOverride() {
        return hasPermission('country_controls.add_override');
    }

    function canRemoveOverride() {
        return hasPermission('country_controls.remove_override');
    }

    function canViewAuditLog() {
        return hasPermission('country_controls.view_audit_log');
    }

    function getCurrentUser() {
        return {
            id: currentUser.id,
            email: currentUser.email,
            role: currentUser.role
        };
    }

    function getPermissionDefinitions() {
        return JSON.parse(JSON.stringify(PERMISSION_DEFINITIONS));
    }

    function enforcePermission(permission, actionDescription) {
        if (!hasPermission(permission)) {
            console.error('[CountryControlsPermissions] Access denied:', actionDescription, 'requires', permission);
            showAdminToast('Access Denied', 'You do not have permission to ' + actionDescription + '.', 'error');
            return false;
        }
        return true;
    }

    function applyUIRestrictions() {
        if (!canChangeGlobalDefault()) {
            document.querySelectorAll('[data-permission="change_global_default"]').forEach(function(el) {
                el.style.display = 'none';
            });
        }
        if (!canAddOverride()) {
            document.querySelectorAll('[data-permission="add_override"]').forEach(function(el) {
                el.style.display = 'none';
            });
        }
        if (!canRemoveOverride()) {
            document.querySelectorAll('[data-permission="remove_override"]').forEach(function(el) {
                el.style.display = 'none';
            });
        }
        if (!canApproveRequests()) {
            document.querySelectorAll('[data-permission="approve_requests"]').forEach(function(el) {
                el.style.display = 'none';
            });
        }
        if (!canRejectRequests()) {
            document.querySelectorAll('[data-permission="reject_requests"]').forEach(function(el) {
                el.style.display = 'none';
            });
        }
    }

    return {
        ADMIN_ROLES: ADMIN_ROLES,
        initialize: initialize,
        hasPermission: hasPermission,
        canView: canView,
        canReviewRequests: canReviewRequests,
        canApproveRequests: canApproveRequests,
        canRejectRequests: canRejectRequests,
        canChangeGlobalDefault: canChangeGlobalDefault,
        canAddOverride: canAddOverride,
        canRemoveOverride: canRemoveOverride,
        canViewAuditLog: canViewAuditLog,
        getCurrentUser: getCurrentUser,
        getPermissionDefinitions: getPermissionDefinitions,
        enforcePermission: enforcePermission,
        applyUIRestrictions: applyUIRestrictions
    };
})();

CountryControlsPermissions.initialize();
window.CountryControlsPermissions = CountryControlsPermissions;

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

var pendingApprovalRequest = null;
var pendingRejectionRequest = null;
var pendingOverrideRemoval = null;

function approveRequest(requestId) {
    var request = countryRequests.find(function(r) { return r.id === requestId; });
    if (!request) return;

    pendingApprovalRequest = request;
    document.getElementById('approvalCustomerName').textContent = request.customer.name;
    document.getElementById('approvalCountryName').textContent = request.country.name;
    
    var modal = new bootstrap.Modal(document.getElementById('approvalConfirmModal'));
    modal.show();
}

function confirmApproval() {
    console.log('[CountryControls] confirmApproval called, pendingApprovalRequest:', pendingApprovalRequest);
    var request = pendingApprovalRequest;
    if (!request) {
        console.error('[CountryControls] confirmApproval: No pending request found');
        return;
    }

    var now = new Date();
    var formattedDate = formatDateDDMMYYYY(now) + ' ' + padZero(now.getHours()) + ':' + padZero(now.getMinutes());

    request.status = 'approved';
    request.reviewedBy = currentAdmin.email;
    request.reviewedAt = formattedDate;

    addAccountOverride(request.customer.id, request.country.code, 'allow');

    logAuditEvent('COUNTRY_REQUEST_APPROVED', {
        requestId: request.id,
        customerId: request.customer.id,
        customerName: request.customer.name,
        countryCode: request.country.code,
        countryName: request.country.name,
        adminEmail: currentAdmin.email,
        overrideType: 'account-level',
        globalPolicyChanged: false
    });

    sendCustomerNotification(request.customer.id, 'country_request_approved', {
        countryName: request.country.name,
        countryCode: request.country.code
    });

    bootstrap.Modal.getInstance(document.getElementById('approvalConfirmModal')).hide();
    pendingApprovalRequest = null;

    renderRequestsList();
    updateReviewStats();
    showAdminToast('Country access approved', request.customer.name + ' can now send SMS to ' + request.country.name + '. Account-level override has been added.', 'success');
}

function rejectRequest(requestId) {
    var request = countryRequests.find(function(r) { return r.id === requestId; });
    if (!request) return;

    pendingRejectionRequest = request;
    document.getElementById('rejectionCustomerName').textContent = request.customer.name;
    document.getElementById('rejectionCountryName').textContent = request.country.name;
    document.getElementById('rejectionReasonCategory').value = '';
    document.getElementById('rejectionReasonText').value = '';
    document.getElementById('confirmRejectBtn').disabled = true;
    
    var modal = new bootstrap.Modal(document.getElementById('rejectionReasonModal'));
    modal.show();
}

function confirmRejection() {
    var request = pendingRejectionRequest;
    if (!request) return;

    var category = document.getElementById('rejectionReasonCategory').value;
    var additionalText = document.getElementById('rejectionReasonText').value.trim();
    
    if (!category) {
        alert('Please select a rejection reason.');
        return;
    }

    var now = new Date();
    var formattedDate = formatDateDDMMYYYY(now) + ' ' + padZero(now.getHours()) + ':' + padZero(now.getMinutes());

    request.status = 'rejected';
    request.reviewedBy = currentAdmin.email;
    request.reviewedAt = formattedDate;
    request.rejectionReason = category + (additionalText ? ': ' + additionalText : '');
    request.rejectionCategory = category;

    logAuditEvent('COUNTRY_REQUEST_REJECTED', {
        requestId: request.id,
        customerId: request.customer.id,
        customerName: request.customer.name,
        countryCode: request.country.code,
        countryName: request.country.name,
        adminEmail: currentAdmin.email,
        rejectionCategory: category,
        rejectionReason: request.rejectionReason
    });

    sendCustomerNotification(request.customer.id, 'country_request_rejected', {
        countryName: request.country.name,
        countryCode: request.country.code,
        reason: request.rejectionReason
    });

    bootstrap.Modal.getInstance(document.getElementById('rejectionReasonModal')).hide();
    pendingRejectionRequest = null;

    renderRequestsList();
    updateReviewStats();
    showAdminToast('Request rejected', 'The customer has been notified of the decision.', 'info');
}

function validateRejectionForm() {
    var category = document.getElementById('rejectionReasonCategory').value;
    document.getElementById('confirmRejectBtn').disabled = !category;
}

function addAccountOverride(customerId, countryCode, action) {
    console.log('[CountryControls] Adding account override:', {
        customerId: customerId,
        countryCode: countryCode,
        action: action,
        timestamp: new Date().toISOString()
    });
}

function sendCustomerNotification(customerId, notificationType, data) {
    console.log('[CustomerNotification] Sending notification:', {
        customerId: customerId,
        type: notificationType,
        data: data,
        channel: 'portal_notification',
        timestamp: new Date().toISOString()
    });
}

var CountryReviewAuditService = {
    ALLOWED_EVENTS: [
        'COUNTRY_REQUEST_SUBMITTED',
        'COUNTRY_REQUEST_APPROVED',
        'COUNTRY_REQUEST_REJECTED',
        'COUNTRY_REQUEST_ADMIN_NOTE_ADDED',
        'COUNTRY_REQUEST_VIEWED',
        'COUNTRY_REQUEST_ESCALATED',
        'COUNTRY_DEFAULT_STATUS_CHANGED',
        'COUNTRY_ACCOUNT_OVERRIDE_ADDED',
        'COUNTRY_ACCOUNT_OVERRIDE_REMOVED',
        'COUNTRY_REQUEST_REVIEWED',
        'COUNTRY_OVERRIDE_ADDED',
        'COUNTRY_OVERRIDE_REMOVED'
    ],
    
    PII_SAFE_FIELDS: [
        'adminId', 'adminEmail', 'timestamp', 'accountId', 'subAccountId',
        'countryIso', 'countryName', 'result', 'reason', 'rejectionCategory',
        'requestId', 'eventType', 'source', 'ipAddress', 'userAgent',
        'beforeStatus', 'afterStatus', 'overrideType', 'accountName', 'subAccountName'
    ],

    RETENTION_YEARS: 7,

    sanitizePayload: function(payload) {
        var sanitized = {};
        var self = this;
        Object.keys(payload).forEach(function(key) {
            if (self.PII_SAFE_FIELDS.indexOf(key) !== -1) {
                sanitized[key] = payload[key];
            } else if (key === 'submitterEmail') {
                sanitized[key] = self.maskEmail(payload[key]);
            } else if (key === 'customerName') {
                sanitized[key] = payload[key];
            }
        });
        return sanitized;
    },

    maskEmail: function(email) {
        if (!email) return null;
        var parts = email.split('@');
        if (parts.length !== 2) return '***@***.***';
        var local = parts[0];
        var domain = parts[1];
        var maskedLocal = local.length > 2 ? local.substring(0, 2) + '***' : '***';
        return maskedLocal + '@' + domain;
    },

    buildAuditRecord: function(eventType, payload, timestamp) {
        var record = {
            id: 'CCAUDIT-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9),
            eventType: eventType,
            eventLabel: this.getEventLabel(eventType),
            timestamp: timestamp,
            timestampUTC: timestamp,
            adminActor: {
                id: currentAdmin.id,
                email: currentAdmin.email,
                role: currentAdmin.role || 'super_admin'
            },
            source: 'country_controls',
            sourceIP: '10.0.0.1',
            userAgent: navigator.userAgent,
            countryIso: payload.countryIso || null,
            countryName: payload.countryName || null,
            impactedAccount: payload.accountId ? {
                id: payload.accountId,
                name: payload.accountName || null,
                subAccountId: payload.subAccountId || null,
                subAccountName: payload.subAccountName || null
            } : null,
            beforeState: payload.beforeStatus || payload.oldStatus || null,
            afterState: payload.afterStatus || payload.newStatus || null,
            reason: payload.reason || null,
            requestId: payload.requestId || null,
            result: payload.result || null,
            retentionExpiry: this.calculateRetentionExpiry(),
            payload: this.sanitizePayload(payload)
        };
        return record;
    },

    getEventLabel: function(eventType) {
        var labels = {
            'COUNTRY_DEFAULT_STATUS_CHANGED': 'Country Default Status Changed',
            'COUNTRY_ACCOUNT_OVERRIDE_ADDED': 'Account Override Added',
            'COUNTRY_ACCOUNT_OVERRIDE_REMOVED': 'Account Override Removed',
            'COUNTRY_REQUEST_REVIEWED': 'Country Request Reviewed',
            'COUNTRY_REQUEST_APPROVED': 'Country Request Approved',
            'COUNTRY_REQUEST_REJECTED': 'Country Request Rejected',
            'COUNTRY_REQUEST_SUBMITTED': 'Country Request Submitted',
            'COUNTRY_OVERRIDE_ADDED': 'Country Override Added',
            'COUNTRY_OVERRIDE_REMOVED': 'Country Override Removed'
        };
        return labels[eventType] || eventType;
    },

    calculateRetentionExpiry: function() {
        var expiry = new Date();
        expiry.setFullYear(expiry.getFullYear() + this.RETENTION_YEARS);
        return expiry.toISOString();
    },

    emit: function(eventType, payload, options) {
        options = options || {};
        
        if (this.ALLOWED_EVENTS.indexOf(eventType) === -1) {
            console.error('[CountryReviewAudit] Invalid event type:', eventType);
            return;
        }

        var timestamp = new Date().toISOString();
        var auditRecord = this.buildAuditRecord(eventType, payload, timestamp);

        this.emitToInternalAdminAudit(auditRecord);

        if (options.emitToCustomerAudit && payload.accountId) {
            this.emitToCustomerAudit(eventType, payload, timestamp, auditRecord.id);
        }

        return auditRecord;
    },

    emitToInternalAdminAudit: function(auditRecord) {
        console.log('[InternalAdminAudit][' + auditRecord.eventType + ']', JSON.stringify({
            id: auditRecord.id,
            eventType: auditRecord.eventType,
            eventLabel: auditRecord.eventLabel,
            timestamp: auditRecord.timestamp,
            adminActor: auditRecord.adminActor,
            countryIso: auditRecord.countryIso,
            countryName: auditRecord.countryName,
            impactedAccount: auditRecord.impactedAccount,
            beforeState: auditRecord.beforeState,
            afterState: auditRecord.afterState,
            reason: auditRecord.reason,
            requestId: auditRecord.requestId,
            result: auditRecord.result,
            retentionExpiry: auditRecord.retentionExpiry
        }));

        if (window.AdminAuditLogger) {
            window.AdminAuditLogger.log(auditRecord);
        }
    },

    emitToCustomerAudit: function(eventType, payload, timestamp, parentAuditId) {
        var customerEventMap = {
            'COUNTRY_REQUEST_APPROVED': 'COUNTRY_ACCESS_GRANTED',
            'COUNTRY_REQUEST_REJECTED': 'COUNTRY_ACCESS_DENIED',
            'COUNTRY_REQUEST_SUBMITTED': 'COUNTRY_ACCESS_REQUESTED',
            'COUNTRY_ACCOUNT_OVERRIDE_ADDED': 'COUNTRY_ACCESS_UPDATED',
            'COUNTRY_ACCOUNT_OVERRIDE_REMOVED': 'COUNTRY_ACCESS_UPDATED',
            'COUNTRY_OVERRIDE_ADDED': 'COUNTRY_ACCESS_UPDATED',
            'COUNTRY_OVERRIDE_REMOVED': 'COUNTRY_ACCESS_UPDATED'
        };

        var customerEventType = customerEventMap[eventType];
        if (!customerEventType) return;

        var customerRecord = {
            id: 'CUSTAUDIT-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9),
            parentAuditId: parentAuditId,
            eventType: customerEventType,
            eventLabel: this.getCustomerEventLabel(customerEventType),
            timestamp: timestamp,
            accountId: payload.accountId,
            subAccountId: payload.subAccountId || null,
            countryIso: payload.countryIso,
            countryName: payload.countryName,
            status: this.getCustomerFacingStatus(eventType, payload),
            message: this.getCustomerFacingMessage(eventType, payload),
            requestId: payload.requestId || null,
            visibleToCustomer: true
        };

        console.log('[CustomerAudit][' + payload.accountId + ']', JSON.stringify(customerRecord));

        if (window.CustomerAuditLogger) {
            window.CustomerAuditLogger.log(payload.accountId, customerRecord);
        }
    },

    getCustomerEventLabel: function(eventType) {
        var labels = {
            'COUNTRY_ACCESS_GRANTED': 'Country Access Granted',
            'COUNTRY_ACCESS_DENIED': 'Country Access Request Declined',
            'COUNTRY_ACCESS_REQUESTED': 'Country Access Requested',
            'COUNTRY_ACCESS_UPDATED': 'Country Access Updated'
        };
        return labels[eventType] || eventType;
    },

    getCustomerFacingStatus: function(eventType, payload) {
        if (eventType === 'COUNTRY_REQUEST_APPROVED' || 
            (eventType.includes('OVERRIDE') && payload.overrideType === 'allowed')) {
            return 'allowed';
        }
        if (eventType === 'COUNTRY_REQUEST_REJECTED' ||
            (eventType.includes('OVERRIDE') && payload.overrideType === 'blocked')) {
            return 'blocked';
        }
        if (eventType === 'COUNTRY_REQUEST_SUBMITTED') {
            return 'pending';
        }
        return 'updated';
    },

    getCustomerFacingMessage: function(eventType, payload) {
        var messages = {
            'COUNTRY_REQUEST_APPROVED': 'Your request to send messages to ' + payload.countryName + ' has been approved.',
            'COUNTRY_REQUEST_REJECTED': 'Your request to send messages to ' + payload.countryName + ' was not approved.',
            'COUNTRY_REQUEST_SUBMITTED': 'Your request to access ' + payload.countryName + ' has been submitted for review.',
            'COUNTRY_ACCOUNT_OVERRIDE_ADDED': 'Your access to ' + payload.countryName + ' has been updated.',
            'COUNTRY_ACCOUNT_OVERRIDE_REMOVED': 'Your access settings for ' + payload.countryName + ' have been updated.',
            'COUNTRY_OVERRIDE_ADDED': 'Your access to ' + payload.countryName + ' has been updated.',
            'COUNTRY_OVERRIDE_REMOVED': 'Your access settings for ' + payload.countryName + ' have been updated.'
        };
        return messages[eventType] || 'Country access settings updated.';
    },

    emitDefaultStatusChanged: function(countryCode, countryName, beforeStatus, afterStatus, reason) {
        return this.emit('COUNTRY_DEFAULT_STATUS_CHANGED', {
            countryIso: countryCode,
            countryName: countryName,
            beforeStatus: beforeStatus,
            afterStatus: afterStatus,
            oldStatus: beforeStatus,
            newStatus: afterStatus,
            reason: reason || null,
            result: 'status_changed'
        }, { emitToCustomerAudit: false });
    },

    emitOverrideAdded: function(countryCode, countryName, accountId, accountName, subAccountId, subAccountName, overrideType) {
        return this.emit('COUNTRY_ACCOUNT_OVERRIDE_ADDED', {
            countryIso: countryCode,
            countryName: countryName,
            accountId: accountId,
            accountName: accountName,
            subAccountId: subAccountId,
            subAccountName: subAccountName,
            overrideType: overrideType,
            afterStatus: overrideType,
            result: 'override_added'
        }, { emitToCustomerAudit: true });
    },

    emitOverrideRemoved: function(countryCode, countryName, accountId, accountName, subAccountId, subAccountName, previousOverrideType) {
        return this.emit('COUNTRY_ACCOUNT_OVERRIDE_REMOVED', {
            countryIso: countryCode,
            countryName: countryName,
            accountId: accountId,
            accountName: accountName,
            subAccountId: subAccountId,
            subAccountName: subAccountName,
            beforeStatus: previousOverrideType,
            overrideType: previousOverrideType,
            result: 'override_removed'
        }, { emitToCustomerAudit: true });
    },

    emitRequestReviewed: function(requestId, countryCode, countryName, accountId, accountName, decision, reason) {
        return this.emit('COUNTRY_REQUEST_REVIEWED', {
            requestId: requestId,
            countryIso: countryCode,
            countryName: countryName,
            accountId: accountId,
            accountName: accountName,
            afterStatus: decision,
            reason: reason || null,
            result: decision === 'approved' ? 'request_approved' : 'request_rejected'
        }, { emitToCustomerAudit: true });
    },

    emitCustomerAudit: function(eventType, payload, timestamp) {
        var customerEventMap = {
            'COUNTRY_REQUEST_APPROVED': 'COUNTRY_ACCESS_GRANTED',
            'COUNTRY_REQUEST_REJECTED': 'COUNTRY_ACCESS_DENIED',
            'COUNTRY_REQUEST_SUBMITTED': 'COUNTRY_ACCESS_REQUESTED'
        };

        var customerEventType = customerEventMap[eventType];
        if (!customerEventType) return;

        var customerAuditRecord = {
            eventType: customerEventType,
            timestamp: timestamp,
            accountId: payload.accountId,
            subAccountId: payload.subAccountId || null,
            countryIso: payload.countryIso,
            countryName: payload.countryName,
            result: payload.result || (eventType.indexOf('APPROVED') !== -1 ? 'approved' : 'rejected'),
            reason: payload.reason || null,
            source: 'admin_review'
        };

        console.log('[CustomerAudit]', JSON.stringify(customerAuditRecord));

        return customerAuditRecord;
    },

    logRequestSubmitted: function(request) {
        return this.emit('COUNTRY_REQUEST_SUBMITTED', {
            requestId: request.id,
            accountId: request.customer.id,
            accountName: request.customer.name,
            subAccountId: request.customer.subAccount ? request.customer.subAccountId : null,
            countryIso: request.country.code,
            countryName: request.country.name,
            submitterEmail: request.submittedBy,
            estimatedVolume: request.estimatedVolume,
            result: 'submitted'
        }, { emitToCustomerAudit: true });
    },

    logRequestApproved: function(request, adminNotes) {
        return this.emit('COUNTRY_REQUEST_APPROVED', {
            requestId: request.id,
            accountId: request.customer.id,
            accountName: request.customer.name,
            subAccountId: request.customer.subAccount ? request.customer.subAccountId : null,
            countryIso: request.country.code,
            countryName: request.country.name,
            result: 'approved',
            overrideType: 'account-level',
            globalPolicyChanged: false,
            adminNotes: adminNotes || null
        }, { emitToCustomerAudit: true });
    },

    logRequestRejected: function(request, rejectionCategory, rejectionReason) {
        return this.emit('COUNTRY_REQUEST_REJECTED', {
            requestId: request.id,
            accountId: request.customer.id,
            accountName: request.customer.name,
            subAccountId: request.customer.subAccount ? request.customer.subAccountId : null,
            countryIso: request.country.code,
            countryName: request.country.name,
            result: 'rejected',
            rejectionCategory: rejectionCategory,
            reason: rejectionReason
        }, { emitToCustomerAudit: true });
    },

    logAdminNoteAdded: function(request, noteContent) {
        return this.emit('COUNTRY_REQUEST_ADMIN_NOTE_ADDED', {
            requestId: request.id,
            accountId: request.customer.id,
            countryIso: request.country.code,
            noteLength: noteContent ? noteContent.length : 0,
            result: 'note_added'
        }, { emitToCustomerAudit: false });
    }
};

function logAuditEvent(eventType, details) {
    var payload = {
        requestId: details.requestId,
        accountId: details.customerId,
        accountName: details.customerName,
        countryIso: details.countryCode,
        countryName: details.countryName,
        result: eventType.indexOf('APPROVED') !== -1 ? 'approved' : 
                eventType.indexOf('REJECTED') !== -1 ? 'rejected' : 'action',
        reason: details.rejectionReason || null,
        rejectionCategory: details.rejectionCategory || null,
        overrideType: details.overrideType || null,
        globalPolicyChanged: details.globalPolicyChanged || false
    };

    CountryReviewAuditService.emit(eventType, payload, { emitToCustomerAudit: true });
}

function showAdminToast(title, message, type) {
    var toastContainer = document.getElementById('toastContainer');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toastContainer';
        toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        toastContainer.style.zIndex = '1100';
        document.body.appendChild(toastContainer);
    }

    var iconClass = type === 'success' ? 'fa-check-circle text-success' : 
                    type === 'error' ? 'fa-exclamation-circle text-danger' : 'fa-info-circle text-primary';
    
    var toastId = 'toast-' + Date.now();
    var toastHtml = 
        '<div id="' + toastId + '" class="toast align-items-center border-0" role="alert">' +
            '<div class="toast-header">' +
                '<i class="fas ' + iconClass + ' me-2"></i>' +
                '<strong class="me-auto">' + title + '</strong>' +
                '<small class="text-muted">Just now</small>' +
                '<button type="button" class="btn-close" data-bs-dismiss="toast"></button>' +
            '</div>' +
            '<div class="toast-body">' + message + '</div>' +
        '</div>';

    toastContainer.insertAdjacentHTML('beforeend', toastHtml);
    var toastElement = document.getElementById(toastId);
    var toast = new bootstrap.Toast(toastElement, { delay: 5000 });
    toast.show();

    toastElement.addEventListener('hidden.bs.toast', function() {
        toastElement.remove();
    });
}

function formatDateDDMMYYYY(date) {
    return padZero(date.getDate()) + '-' + padZero(date.getMonth() + 1) + '-' + date.getFullYear();
}

function padZero(n) {
    return n < 10 ? '0' + n : n;
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
        { code: 'RU', name: 'Russia', dialCode: '+7', status: 'blocked', risk: 'critical', overrides: 1 },
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

var countrySortOrder = 'asc';

function sortCountries(field) {
    if (field === 'name') {
        countrySortOrder = countrySortOrder === 'asc' ? 'desc' : 'asc';
        countries.sort(function(a, b) {
            if (countrySortOrder === 'asc') {
                return a.name.localeCompare(b.name);
            } else {
                return b.name.localeCompare(a.name);
            }
        });
        renderCountryTable();
    }
}

function renderCountryTable() {
    var tbody = document.getElementById('countryTableBody');
    var searchTerm = document.getElementById('countrySearch') ? document.getElementById('countrySearch').value.toLowerCase() : '';
    var statusFilter = document.getElementById('bulkStatusFilter') ? document.getElementById('bulkStatusFilter').value : '';

    var filtered = countries.filter(function(c) {
        var matchesSearch = c.name.toLowerCase().includes(searchTerm) || 
                           c.code.toLowerCase().includes(searchTerm) ||
                           c.dialCode.includes(searchTerm);
        var matchesStatus = !statusFilter || c.status === statusFilter;
        return matchesSearch && matchesStatus;
    });

    tbody.innerHTML = '';

    filtered.forEach(function(country) {
        var row = document.createElement('tr');
        
        var statusLabel = country.status === 'allowed' ? 'Allowed' : 'Blocked';
        var statusClass = country.status === 'allowed' ? 'allowed' : 'blocked';
        var statusIcon = country.status === 'allowed' ? 'fa-check-circle' : 'fa-ban';
        var statusTooltip = country.status === 'allowed' ? 
            'Any customer can send without approval' : 
            'No customer can send unless explicit account override exists';

        var overridesHtml = country.overrides > 0 ? 
            '<span class="overrides-badge" onclick="viewOverrides(\'' + country.code + '\')" title="Click to view account overrides">' +
                '<i class="fas fa-users"></i>' + country.overrides + 
            '</span>' : 
            '<span class="overrides-badge none">0</span>';

        var actionMenu = 
            '<div class="action-menu">' +
                '<button class="action-menu-btn" onclick="toggleCountryActionMenu(event, \'' + country.code + '\')">' +
                    '<i class="fas fa-ellipsis-v"></i>' +
                '</button>' +
                '<div class="action-dropdown" id="countryActionMenu-' + country.code + '">' +
                    '<div class="action-dropdown-section">Account Overrides</div>' +
                    '<div class="action-dropdown-item" onclick="openAddOverrideModal(\'' + country.code + '\')">' +
                        '<i class="fas fa-plus-circle"></i>Add Account Override' +
                    '</div>' +
                    '<div class="action-dropdown-item" onclick="openRemoveOverrideModal(\'' + country.code + '\')">' +
                        '<i class="fas fa-minus-circle"></i>Remove Account Override' +
                    '</div>' +
                    '<div class="action-dropdown-item view" onclick="viewOverrides(\'' + country.code + '\')">' +
                        '<i class="fas fa-users"></i>View Overrides (' + country.overrides + ')' +
                    '</div>' +
                    '<div class="action-dropdown-divider"></div>' +
                    '<div class="action-dropdown-section">Default Status</div>' +
                    (country.status !== 'allowed' ? 
                        '<div class="action-dropdown-item approve" onclick="openDefaultStatusModal(\'' + country.code + '\', \'allowed\')">' +
                            '<i class="fas fa-check-circle"></i>Allow Country (Default)' +
                        '</div>' : '') +
                    (country.status !== 'blocked' ? 
                        '<div class="action-dropdown-item reject" onclick="openDefaultStatusModal(\'' + country.code + '\', \'blocked\')">' +
                            '<i class="fas fa-ban"></i>Block Country (Default)' +
                        '</div>' : '') +
                '</div>' +
            '</div>';

        row.innerHTML = 
            '<td><input type="checkbox" class="country-checkbox" data-code="' + country.code + '"></td>' +
            '<td>' +
                '<div class="country-name-cell">' +
                    '<span class="country-name">' + country.name + '</span>' +
                    '<span class="country-code">' + country.code + '</span>' +
                '</div>' +
            '</td>' +
            '<td>' +
                '<span class="status-badge ' + statusClass + '" title="' + statusTooltip + '">' +
                    '<i class="fas ' + statusIcon + '"></i>' + statusLabel +
                '</span>' +
            '</td>' +
            '<td>' + overridesHtml + '</td>' +
            '<td>' + actionMenu + '</td>';

        tbody.appendChild(row);
    });
}

function toggleCountryActionMenu(event, countryCode) {
    event.stopPropagation();
    document.querySelectorAll('.action-dropdown.show').forEach(function(menu) {
        if (menu.id !== 'countryActionMenu-' + countryCode) {
            menu.classList.remove('show');
        }
    });
    document.getElementById('countryActionMenu-' + countryCode).classList.toggle('show');
}

var pendingDefaultStatusChange = { countryCode: null, newStatus: null };
var pendingAddOverride = { countryCode: null };
var pendingRemoveOverride = { countryCode: null };

function openDefaultStatusModal(countryCode, newStatus) {
    var country = countries.find(function(c) { return c.code === countryCode; });
    if (!country) return;

    document.querySelectorAll('.action-dropdown.show').forEach(function(menu) {
        menu.classList.remove('show');
    });

    pendingDefaultStatusChange = { countryCode: countryCode, newStatus: newStatus };

    var statusLabel = newStatus === 'allowed' ? 'Allowed' : 'Blocked';
    var statusIcon = newStatus === 'allowed' ? 'fa-check-circle' : 'fa-ban';
    var statusClass = newStatus === 'allowed' ? 'allowed' : 'blocked';
    var headerColor = newStatus === 'allowed' ? '#16a34a' : '#dc2626';
    var description = newStatus === 'allowed' ? 
        'Any customer will be able to send to this country without needing approval.' : 
        'No customer can send to this country unless they have an explicit account override.';
    var alertClass = newStatus === 'allowed' ? 'alert-success' : 'alert-danger';

    document.getElementById('defaultStatusModalHeader').style.background = headerColor;
    document.getElementById('defaultStatusModalHeader').style.color = '#fff';
    document.getElementById('defaultStatusCountryName').textContent = country.name + ' (' + country.code + ')';
    document.getElementById('defaultStatusNewStatus').innerHTML = 
        '<span class="status-badge ' + statusClass + '">' +
            '<i class="fas ' + statusIcon + '"></i>' + statusLabel +
        '</span>';
    document.getElementById('defaultStatusDescription').className = 'alert mb-3 ' + alertClass;
    document.getElementById('defaultStatusDescription').innerHTML = 
        '<i class="fas fa-info-circle me-1"></i>' + description;
    
    var confirmBtn = document.getElementById('confirmDefaultStatusBtn');
    confirmBtn.className = newStatus === 'allowed' ? 'btn btn-success' : 'btn btn-danger';

    var overrideWarning = document.getElementById('defaultStatusOverrideWarning');
    if (country.overrides > 0) {
        overrideWarning.style.display = 'block';
        document.getElementById('overrideCountText').textContent = country.overrides;
    } else {
        overrideWarning.style.display = 'none';
    }

    var modal = new bootstrap.Modal(document.getElementById('defaultStatusConfirmModal'));
    modal.show();
}

function confirmDefaultStatusChange() {
    try {
        console.log('[CountryControls] confirmDefaultStatusChange called');
        var countryCode = pendingDefaultStatusChange.countryCode;
        var newStatus = pendingDefaultStatusChange.newStatus;
        console.log('[CountryControls] Pending change:', { countryCode: countryCode, newStatus: newStatus });
        
        var country = countries.find(function(c) { return c.code === countryCode; });
        if (!country) {
            console.error('[CountryControls] Country not found:', countryCode);
            return;
        }

        var oldStatus = country.status;
        country.status = newStatus;
        country.lastUpdated = formatDateDDMMYYYY(new Date());

        CountryReviewAuditService.emit('COUNTRY_DEFAULT_STATUS_CHANGED', {
            countryIso: country.code,
            countryName: country.name,
            oldStatus: oldStatus,
            newStatus: newStatus,
            result: 'status_changed'
        }, { emitToCustomerAudit: false });

        var modalEl = document.getElementById('defaultStatusConfirmModal');
        var modalInstance = bootstrap.Modal.getInstance(modalEl);
        if (modalInstance) {
            modalInstance.hide();
        } else {
            console.warn('[CountryControls] No modal instance found, using alternative hide');
            modalEl.classList.remove('show');
            document.body.classList.remove('modal-open');
            var backdrop = document.querySelector('.modal-backdrop');
            if (backdrop) backdrop.remove();
        }
        
        renderCountryTable();
        
        var statusLabel = newStatus === 'allowed' ? 'Allowed' : 'Blocked';
        showAdminToast('Default status updated', country.name + ' is now ' + statusLabel + ' by default.', 'success');
        console.log('[CountryControls] Status change completed successfully');
    } catch (error) {
        console.error('[CountryControls] Error in confirmDefaultStatusChange:', error);
    }
}

var mockAccountsData = [
    { id: 'ACC-10045', name: 'TechStart Ltd', status: 'live', subAccounts: [] },
    { id: 'ACC-10089', name: 'HealthFirst UK', status: 'live', subAccounts: ['NHS Partnership', 'Private Clinics'] },
    { id: 'ACC-10112', name: 'E-Commerce Hub', status: 'test', subAccounts: [] },
    { id: 'ACC-10156', name: 'MediCare Global', status: 'live', subAccounts: ['UK Division', 'EU Division'] },
    { id: 'ACC-10034', name: 'RetailMax Corp', status: 'live', subAccounts: ['APAC Operations', 'EMEA Operations'] },
    { id: 'ACC-10078', name: 'Global Comms Inc', status: 'live', subAccounts: ['Marketing Division', 'Sales Division'] },
    { id: 'ACC-10098', name: 'TravelWise Ltd', status: 'live', subAccounts: [] },
    { id: 'ACC-10067', name: 'Logistics Pro', status: 'test', subAccounts: [] },
    { id: 'ACC-10102', name: 'FinServe Solutions', status: 'live', subAccounts: ['Investments', 'Insurance'] },
    { id: 'ACC-10199', name: 'Digital Media Co', status: 'live', subAccounts: [] }
];

function initAccountTypeahead() {
    var searchInput = document.getElementById('addOverrideAccountSearch');
    var resultsContainer = document.getElementById('accountTypeaheadResults');
    
    if (!searchInput) return;

    function renderAccountList(accounts) {
        if (accounts.length === 0) {
            resultsContainer.innerHTML = '<div class="typeahead-no-results">No accounts found</div>';
        } else {
            resultsContainer.innerHTML = accounts.map(function(account) {
                return '<div class="typeahead-item" onclick="selectAccount(\'' + account.id + '\')">' +
                    '<div><span class="account-name">' + account.name + '</span>' +
                    '<span class="account-status ' + account.status + '">' + account.status.toUpperCase() + '</span></div>' +
                    '<div class="account-id">' + account.id + '</div>' +
                '</div>';
            }).join('');
        }
        resultsContainer.classList.add('show');
    }

    searchInput.addEventListener('input', function() {
        var query = this.value.toLowerCase().trim();
        
        if (query.length === 0) {
            renderAccountList(mockAccountsData);
            return;
        }

        var filtered = mockAccountsData.filter(function(account) {
            return account.name.toLowerCase().includes(query) || 
                   account.id.toLowerCase().includes(query);
        });

        renderAccountList(filtered);
    });

    searchInput.addEventListener('blur', function() {
        setTimeout(function() {
            resultsContainer.classList.remove('show');
        }, 200);
    });

    searchInput.addEventListener('focus', function() {
        var query = this.value.toLowerCase().trim();
        if (query.length === 0) {
            renderAccountList(mockAccountsData);
        } else {
            var filtered = mockAccountsData.filter(function(account) {
                return account.name.toLowerCase().includes(query) || 
                       account.id.toLowerCase().includes(query);
            });
            renderAccountList(filtered);
        }
    });
}

function selectAccount(accountId) {
    var account = mockAccountsData.find(function(a) { return a.id === accountId; });
    if (!account) return;

    document.getElementById('addOverrideAccountId').value = account.id;
    document.getElementById('addOverrideAccountName').value = account.name;
    document.getElementById('addOverrideAccountSearch').value = '';
    document.getElementById('accountTypeaheadResults').classList.remove('show');

    var displayBadge = document.getElementById('selectedAccountDisplay');
    displayBadge.querySelector('.account-info').textContent = account.name + ' (' + account.id + ')';
    displayBadge.style.display = 'inline-flex';
    
    document.querySelector('.account-typeahead-wrapper').style.display = 'none';

    var subAccountSection = document.getElementById('subAccountSection');
    var subAccountSelect = document.getElementById('addOverrideSubAccount');
    
    if (account.subAccounts && account.subAccounts.length > 0) {
        subAccountSelect.innerHTML = '<option value="">Apply to entire account</option>';
        account.subAccounts.forEach(function(sub) {
            var option = document.createElement('option');
            option.value = sub;
            option.textContent = sub;
            subAccountSelect.appendChild(option);
        });
        subAccountSection.style.display = 'block';
    } else {
        subAccountSection.style.display = 'none';
    }

    document.getElementById('confirmAddOverrideBtn').disabled = false;
}

function clearAccountSelection() {
    document.getElementById('addOverrideAccountId').value = '';
    document.getElementById('addOverrideAccountName').value = '';
    document.getElementById('selectedAccountDisplay').style.display = 'none';
    document.querySelector('.account-typeahead-wrapper').style.display = 'block';
    document.getElementById('addOverrideAccountSearch').value = '';
    document.getElementById('subAccountSection').style.display = 'none';
    document.getElementById('confirmAddOverrideBtn').disabled = true;
}

function openAddOverrideModal(countryCode) {
    var country = countries.find(function(c) { return c.code === countryCode; });
    if (!country) return;

    document.querySelectorAll('.action-dropdown.show').forEach(function(menu) {
        menu.classList.remove('show');
    });

    pendingAddOverride = { countryCode: countryCode };
    
    document.getElementById('addOverrideCountryName').textContent = country.name + ' (' + country.code + ')';
    
    clearAccountSelection();
    document.querySelector('input[name="overrideType"][value="allowed"]').checked = true;

    var modal = new bootstrap.Modal(document.getElementById('addOverrideModal'));
    modal.show();
}

function confirmAddOverride() {
    var countryCode = pendingAddOverride.countryCode;
    var accountId = document.getElementById('addOverrideAccountId').value;
    var accountName = document.getElementById('addOverrideAccountName').value;
    var subAccount = document.getElementById('addOverrideSubAccount').value || null;
    var overrideType = document.querySelector('input[name="overrideType"]:checked').value;

    if (!accountId) {
        alert('Please select an account.');
        return;
    }

    var country = countries.find(function(c) { return c.code === countryCode; });
    if (!country) return;

    if (!mockOverridesData[countryCode]) {
        mockOverridesData[countryCode] = [];
    }
    
    var existingOverride = mockOverridesData[countryCode].find(function(o) {
        return o.accountId === accountId && o.subAccount === subAccount;
    });
    
    if (existingOverride) {
        alert('An override already exists for this account' + (subAccount ? ' and sub-account' : '') + '.');
        return;
    }

    mockOverridesData[countryCode].push({
        accountName: accountName,
        accountId: accountId,
        subAccount: subAccount,
        overrideType: overrideType,
        dateApplied: formatDateDDMMYYYY(new Date()),
        appliedBy: 'admin@quicksms.co.uk'
    });
    country.overrides = mockOverridesData[countryCode].length;

    CountryReviewAuditService.emit('COUNTRY_OVERRIDE_ADDED', {
        countryIso: country.code,
        countryName: country.name,
        accountId: accountId,
        accountName: accountName,
        subAccount: subAccount,
        overrideType: overrideType,
        result: 'override_added'
    }, { emitToCustomerAudit: true });

    bootstrap.Modal.getInstance(document.getElementById('addOverrideModal')).hide();
    renderCountryTable();
    
    var typeLabel = overrideType === 'allowed' ? 'Allowed' : 'Blocked';
    var scopeText = subAccount ? accountName + ' / ' + subAccount : accountName;
    showAdminToast('Override added', scopeText + ' is now ' + typeLabel + ' for ' + country.name + '.', 'success');
}

function openRemoveOverrideModal(countryCode) {
    var country = countries.find(function(c) { return c.code === countryCode; });
    if (!country) return;

    document.querySelectorAll('.action-dropdown.show').forEach(function(menu) {
        menu.classList.remove('show');
    });

    pendingRemoveOverride = { countryCode: countryCode };
    
    document.getElementById('removeOverrideCountryName').textContent = country.name + ' (' + country.code + ')';
    
    var select = document.getElementById('removeOverrideSelect');
    select.innerHTML = '<option value="">Choose an override...</option>';
    
    var overrides = mockOverridesData[countryCode] || [];
    if (overrides.length === 0) {
        select.innerHTML = '<option value="">No overrides exist for this country</option>';
        select.disabled = true;
    } else {
        select.disabled = false;
        overrides.forEach(function(override, index) {
            var typeLabel = override.overrideType === 'allowed' ? 'Allowed' : 'Blocked';
            var option = document.createElement('option');
            option.value = index;
            option.textContent = override.accountName + ' (' + override.accountId + ') - ' + typeLabel;
            select.appendChild(option);
        });
    }

    var modal = new bootstrap.Modal(document.getElementById('removeOverrideModal'));
    modal.show();
}

function confirmRemoveOverride() {
    var countryCode = pendingRemoveOverride.countryCode;
    var overrideIndex = document.getElementById('removeOverrideSelect').value;

    if (overrideIndex === '') {
        alert('Please select an override to remove.');
        return;
    }

    var country = countries.find(function(c) { return c.code === countryCode; });
    if (!country) return;

    var overrides = mockOverridesData[countryCode] || [];
    var removedOverride = overrides[parseInt(overrideIndex)];

    CountryReviewAuditService.emit('COUNTRY_OVERRIDE_REMOVED', {
        countryIso: country.code,
        countryName: country.name,
        accountId: removedOverride.accountId,
        accountName: removedOverride.accountName,
        previousOverrideType: removedOverride.overrideType,
        result: 'override_removed'
    }, { emitToCustomerAudit: true });

    overrides.splice(parseInt(overrideIndex), 1);
    mockOverridesData[countryCode] = overrides;
    country.overrides = overrides.length;

    bootstrap.Modal.getInstance(document.getElementById('removeOverrideModal')).hide();
    renderCountryTable();
    
    showAdminToast('Override removed', removedOverride.accountName + ' override for ' + country.name + ' has been removed.', 'success');
}

var mockOverridesData = {
    'US': [
        { accountName: 'Global Comms Inc', accountId: 'ACC-10078', subAccount: null, overrideType: 'blocked', dateApplied: '25-01-2026', appliedBy: 'admin@quicksms.co.uk' },
        { accountName: 'RetailMax Corp', accountId: 'ACC-10034', subAccount: null, overrideType: 'blocked', dateApplied: '20-01-2026', appliedBy: 'sarah.jones@quicksms.co.uk' },
        { accountName: 'MediCare Global', accountId: 'ACC-10156', subAccount: 'West Region', overrideType: 'allowed', dateApplied: '18-01-2026', appliedBy: 'admin@quicksms.co.uk' }
    ],
    'DE': [
        { accountName: 'TravelWise Ltd', accountId: 'ACC-10098', subAccount: null, overrideType: 'blocked', dateApplied: '22-01-2026', appliedBy: 'admin@quicksms.co.uk' }
    ],
    'ES': [
        { accountName: 'Digital Media Co', accountId: 'ACC-10199', subAccount: null, overrideType: 'blocked', dateApplied: '19-01-2026', appliedBy: 'sarah.jones@quicksms.co.uk' },
        { accountName: 'Logistics Pro', accountId: 'ACC-10067', subAccount: null, overrideType: 'allowed', dateApplied: '15-01-2026', appliedBy: 'admin@quicksms.co.uk' }
    ],
    'CH': [
        { accountName: 'FinServe Solutions', accountId: 'ACC-10102', subAccount: 'Investments', overrideType: 'blocked', dateApplied: '12-01-2026', appliedBy: 'admin@quicksms.co.uk' }
    ],
    'NG': [
        { accountName: 'TechStart Ltd', accountId: 'ACC-10045', subAccount: null, overrideType: 'allowed', dateApplied: '28-01-2026', appliedBy: 'admin@quicksms.co.uk' },
        { accountName: 'Global Comms Inc', accountId: 'ACC-10078', subAccount: 'Marketing Division', overrideType: 'allowed', dateApplied: '25-01-2026', appliedBy: 'sarah.jones@quicksms.co.uk' },
        { accountName: 'FinServe Solutions', accountId: 'ACC-10102', subAccount: null, overrideType: 'allowed', dateApplied: '20-01-2026', appliedBy: 'admin@quicksms.co.uk' },
        { accountName: 'MediCare Global', accountId: 'ACC-10156', subAccount: null, overrideType: 'allowed', dateApplied: '18-01-2026', appliedBy: 'james.smith@quicksms.co.uk' },
        { accountName: 'E-Commerce Hub', accountId: 'ACC-10112', subAccount: null, overrideType: 'allowed', dateApplied: '15-01-2026', appliedBy: 'admin@quicksms.co.uk' }
    ],
    'IN': [
        { accountName: 'HealthFirst UK', accountId: 'ACC-10089', subAccount: 'NHS Partnership', overrideType: 'allowed', dateApplied: '28-01-2026', appliedBy: 'admin@quicksms.co.uk' },
        { accountName: 'MediCare Global', accountId: 'ACC-10156', subAccount: null, overrideType: 'allowed', dateApplied: '22-01-2026', appliedBy: 'james.smith@quicksms.co.uk' },
        { accountName: 'TechStart Ltd', accountId: 'ACC-10045', subAccount: null, overrideType: 'allowed', dateApplied: '20-01-2026', appliedBy: 'admin@quicksms.co.uk' },
        { accountName: 'Digital Media Co', accountId: 'ACC-10199', subAccount: null, overrideType: 'allowed', dateApplied: '18-01-2026', appliedBy: 'sarah.jones@quicksms.co.uk' },
        { accountName: 'Global Comms Inc', accountId: 'ACC-10078', subAccount: null, overrideType: 'allowed', dateApplied: '16-01-2026', appliedBy: 'admin@quicksms.co.uk' },
        { accountName: 'Logistics Pro', accountId: 'ACC-10067', subAccount: null, overrideType: 'allowed', dateApplied: '14-01-2026', appliedBy: 'james.smith@quicksms.co.uk' },
        { accountName: 'RetailMax Corp', accountId: 'ACC-10034', subAccount: null, overrideType: 'allowed', dateApplied: '12-01-2026', appliedBy: 'admin@quicksms.co.uk' },
        { accountName: 'TravelWise Ltd', accountId: 'ACC-10098', subAccount: null, overrideType: 'allowed', dateApplied: '10-01-2026', appliedBy: 'sarah.jones@quicksms.co.uk' }
    ],
    'PH': [
        { accountName: 'E-Commerce Hub', accountId: 'ACC-10112', subAccount: null, overrideType: 'allowed', dateApplied: '27-01-2026', appliedBy: 'admin@quicksms.co.uk' },
        { accountName: 'TechStart Ltd', accountId: 'ACC-10045', subAccount: null, overrideType: 'allowed', dateApplied: '20-01-2026', appliedBy: 'sarah.jones@quicksms.co.uk' },
        { accountName: 'Digital Media Co', accountId: 'ACC-10199', subAccount: null, overrideType: 'allowed', dateApplied: '15-01-2026', appliedBy: 'admin@quicksms.co.uk' }
    ],
    'PK': [
        { accountName: 'RetailMax Corp', accountId: 'ACC-10034', subAccount: 'APAC Operations', overrideType: 'allowed', dateApplied: '15-01-2026', appliedBy: 'admin@quicksms.co.uk' },
        { accountName: 'TravelWise Ltd', accountId: 'ACC-10098', subAccount: null, overrideType: 'allowed', dateApplied: '10-01-2026', appliedBy: 'sarah.jones@quicksms.co.uk' }
    ],
    'BD': [
        { accountName: 'HealthFirst UK', accountId: 'ACC-10089', subAccount: null, overrideType: 'allowed', dateApplied: '08-01-2026', appliedBy: 'admin@quicksms.co.uk' }
    ],
    'RU': [
        { accountName: 'Logistics Pro', accountId: 'ACC-10067', subAccount: null, overrideType: 'blocked', dateApplied: '05-01-2026', appliedBy: 'admin@quicksms.co.uk' }
    ]
};

var currentViewingCountryCode = null;

function viewOverrides(countryCode) {
    var country = countries.find(function(c) { return c.code === countryCode; });
    if (!country) return;

    currentViewingCountryCode = countryCode;

    document.querySelectorAll('.action-dropdown.show').forEach(function(menu) {
        menu.classList.remove('show');
    });

    document.getElementById('overridesModalCountryName').textContent = '— ' + country.name;
    
    renderOverridesTable(countryCode);
    
    var modal = new bootstrap.Modal(document.getElementById('customerOverridesModal'));
    modal.show();
}

function renderOverridesTable(countryCode) {
    var overrides = mockOverridesData[countryCode] || [];
    var tbody = document.getElementById('overridesTableBody');
    var noOverridesMsg = document.getElementById('noOverridesMessage');
    var tableContainer = document.querySelector('#overridesTable').closest('.table-responsive');
    
    tbody.innerHTML = '';
    
    if (overrides.length === 0) {
        tableContainer.style.display = 'none';
        noOverridesMsg.style.display = 'block';
    } else {
        tableContainer.style.display = 'block';
        noOverridesMsg.style.display = 'none';
        
        overrides.forEach(function(override, index) {
            var row = document.createElement('tr');
            
            var typeIcon = override.overrideType === 'allowed' ? 'fa-check-circle' : 'fa-ban';
            var typeLabel = override.overrideType === 'allowed' ? 'Allowed' : 'Blocked';
            
            row.innerHTML = 
                '<td>' +
                    '<div class="override-account-cell">' +
                        '<span class="account-name">' + override.accountName + '</span>' +
                        '<span class="account-id">' + override.accountId + '</span>' +
                    '</div>' +
                '</td>' +
                '<td>' + (override.subAccount ? '<span class="text-muted">' + override.subAccount + '</span>' : '<span class="text-muted">—</span>') + '</td>' +
                '<td>' +
                    '<span class="override-type-badge ' + override.overrideType + '">' +
                        '<i class="fas ' + typeIcon + '"></i>' + typeLabel +
                    '</span>' +
                '</td>' +
                '<td class="text-muted">' + override.dateApplied + '</td>' +
                '<td>' +
                    '<div class="override-admin">' +
                        '<i class="fas fa-user-shield"></i>' +
                        '<span>' + override.appliedBy + '</span>' +
                    '</div>' +
                '</td>' +
                '<td>' +
                    '<button class="btn btn-sm btn-outline-danger override-remove-btn" ' +
                        'onclick="confirmRemoveOverrideFromModal(\'' + countryCode + '\', ' + index + ')" ' +
                        'title="Remove this override">' +
                        '<i class="fas fa-trash-alt"></i>' +
                    '</button>' +
                '</td>';
            
            tbody.appendChild(row);
        });
    }
}

function confirmRemoveOverrideFromModal(countryCode, overrideIndex) {
    var country = countries.find(function(c) { return c.code === countryCode; });
    var overrides = mockOverridesData[countryCode] || [];
    var override = overrides[overrideIndex];
    
    if (!country || !override) return;

    var scopeText = override.subAccount ? 
        override.accountName + ' / ' + override.subAccount : 
        override.accountName;
    var typeLabel = override.overrideType === 'allowed' ? 'Allowed' : 'Blocked';
    var typeBadgeClass = override.overrideType === 'allowed' ? 'bg-success' : 'bg-danger';
    var globalStatus = country.status === 'allowed' ? 'Allowed' : 'Blocked';

    pendingOverrideRemoval = {
        countryCode: countryCode,
        overrideIndex: overrideIndex,
        country: country,
        override: override,
        scopeText: scopeText
    };

    document.getElementById('removeOverrideAccountName').textContent = scopeText;
    document.getElementById('removeOverrideCountryName').textContent = country.name;
    document.getElementById('removeOverrideGlobalStatus').textContent = globalStatus;
    
    var typeBadge = document.getElementById('removeOverrideTypeBadge');
    typeBadge.textContent = typeLabel;
    typeBadge.className = 'badge ' + typeBadgeClass;

    var modal = new bootstrap.Modal(document.getElementById('removeOverrideConfirmModal'));
    modal.show();
}

function executeRemoveOverride() {
    if (!pendingOverrideRemoval) return;

    var countryCode = pendingOverrideRemoval.countryCode;
    var overrideIndex = pendingOverrideRemoval.overrideIndex;
    var country = pendingOverrideRemoval.country;
    var override = pendingOverrideRemoval.override;
    var scopeText = pendingOverrideRemoval.scopeText;

    var overrides = mockOverridesData[countryCode] || [];

    CountryReviewAuditService.emit('COUNTRY_OVERRIDE_REMOVED', {
        countryIso: country.code,
        countryName: country.name,
        accountId: override.accountId,
        accountName: override.accountName,
        subAccount: override.subAccount,
        previousOverrideType: override.overrideType,
        result: 'override_removed'
    }, { emitToCustomerAudit: true });

    overrides.splice(overrideIndex, 1);
    mockOverridesData[countryCode] = overrides;
    country.overrides = overrides.length;

    bootstrap.Modal.getInstance(document.getElementById('removeOverrideConfirmModal')).hide();
    pendingOverrideRemoval = null;

    renderOverridesTable(countryCode);
    renderCountryTable();
    
    showAdminToast('Override removed', scopeText + ' override for ' + country.name + ' has been removed.', 'success');
}

function capitalize(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
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
