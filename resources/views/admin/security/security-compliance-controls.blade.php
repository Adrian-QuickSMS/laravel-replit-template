@extends('layouts.admin')

@section('title', 'Spam Filter')

@section('body_class', 'qsms-fullbleed')

@push('styles')
<style>
/* Full-width layout for Spam Filter */
.qsms-fullbleed .qsms-content-wrap {
    max-width: none !important;
    padding-left: 1.5rem !important;
    padding-right: 1.5rem !important;
}
.spam-filter-page {
    width: 100%;
}
.spam-filter-page .page-titles {
    padding: 0;
    margin-bottom: 1rem;
}
.sec-controls-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}
.sec-controls-title h4 {
    margin: 0;
    font-weight: 600;
    color: #1e3a5f;
}
.sec-controls-title p {
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
.sec-enforcement-banner {
    background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%);
    border-radius: 0.5rem;
    padding: 1rem 1.25rem;
    margin-bottom: 1.5rem;
    color: #fff;
}
.sec-enforcement-banner h6 {
    margin: 0 0 0.5rem 0;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.sec-enforcement-banner p {
    margin: 0;
    font-size: 0.8rem;
    opacity: 0.9;
}
.sec-enforcement-points {
    display: flex;
    gap: 2rem;
    margin-top: 0.75rem;
    flex-wrap: wrap;
}
.sec-enforcement-point {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.8rem;
}
.sec-enforcement-point i {
    color: #48bb78;
}
.sec-stats {
    display: flex;
    gap: 1rem;
    margin-bottom: 1.5rem;
}
.sec-stat-card {
    flex: 1;
    max-width: 220px;
    background: #fff;
    border-radius: 0.5rem;
    padding: 1rem 1.25rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    border-left: 3px solid;
    cursor: pointer;
    transition: all 0.2s ease;
}
.sec-stat-card:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.12);
    transform: translateY(-1px);
}
.sec-stat-card.selected {
    box-shadow: 0 0 0 2px #1e3a5f, 0 2px 8px rgba(30, 58, 95, 0.25);
    background: #f0f4f8;
}
.sec-stat-card.active {
    border-left-color: #48bb78;
}
.sec-stat-card.pending {
    border-left-color: #ecc94b;
}
.sec-stat-value {
    font-size: 1.75rem;
    font-weight: 700;
    color: #1e3a5f;
}
.sec-stat-label {
    font-size: 0.75rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.sec-table-card {
    background: #fff;
    border-radius: 0.5rem;
    border: 1px solid #e5e9f2;
    margin-bottom: 1.5rem;
    overflow: hidden;
}
.sec-table-header {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #f8f9fc;
}
.filter-chip {
    display: inline-flex;
    align-items: center;
    padding: 0.2rem 0.6rem;
    background-color: rgba(30, 58, 95, 0.12);
    color: #1e3a5f;
    border-radius: 1rem;
    font-size: 0.7rem;
    font-weight: 500;
    margin-right: 0.35rem;
    margin-bottom: 0.25rem;
}
.filter-chip .chip-label {
    margin-right: 0.2rem;
    color: #6c757d;
}
.filter-chip .remove-chip {
    margin-left: 0.35rem;
    cursor: pointer;
    opacity: 0.7;
    font-size: 0.6rem;
}
.filter-chip .remove-chip:hover {
    opacity: 1;
    color: #dc3545;
}
.active-filters-row {
    padding: 0.5rem 1rem;
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
    display: none;
}
.active-filters-row.has-filters {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.25rem;
}
.sec-table-header h6 {
    margin: 0;
    font-weight: 600;
    color: #1e3a5f;
}
.sec-search-box {
    position: relative;
    width: 280px;
}
.sec-search-box input {
    padding-left: 2.25rem;
    font-size: 0.85rem;
    border: 1px solid #ced4da;
    border-radius: 6px;
}
.sec-search-box input:focus {
    border-color: #1e3a5f;
    box-shadow: 0 0 0 2px rgba(30, 58, 95, 0.1);
}
.sec-search-box i {
    position: absolute;
    left: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
}
.sec-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    border-bottom: 1px solid #e9ecef;
    gap: 1rem;
}
.sec-search-box-left {
    position: relative;
    width: 300px;
    flex-shrink: 0;
}
.sec-search-box-left input {
    padding-left: 2.25rem;
    font-size: 0.85rem;
    border: 1px solid #ced4da;
    border-radius: 6px;
    height: 32px;
}
.sec-search-box-left input:focus {
    border-color: #1e3a5f;
    box-shadow: 0 0 0 2px rgba(30, 58, 95, 0.1);
}
.sec-search-box-left i {
    position: absolute;
    left: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
    font-size: 0.85rem;
}
.sec-toolbar-actions {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

/* Quarantine Filter Panel - matching Numbers page pattern */
.quarantine-filter-pill-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: transparent;
    border: 1px solid #1e3a5f;
    color: #1e3a5f;
    font-weight: 500;
    font-size: 0.875rem;
    padding: 0.375rem 1rem;
    border-radius: 6px;
    transition: all 0.2s;
    cursor: pointer;
}
.quarantine-filter-pill-btn:hover {
    background: rgba(30, 58, 95, 0.08);
    border-color: #1e3a5f;
    color: #1e3a5f;
}
.quarantine-filter-pill-btn.active {
    background: rgba(30, 58, 95, 0.12);
    border-color: #1e3a5f;
    color: #1e3a5f;
}
.quarantine-filter-pill-btn i {
    font-size: 0.8rem;
    color: #1e3a5f;
}
.quarantine-filter-panel {
    background: #fff;
    border-radius: 0.5rem;
    margin-bottom: 1rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    display: none;
}
.quarantine-filter-panel .filter-body {
    padding: 1rem 1.25rem;
    background: #f8fafc;
    border-radius: 0.5rem;
}
.quarantine-filter-panel .filter-row {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1rem;
}
.quarantine-filter-panel .filter-group {
    display: flex;
    flex-direction: column;
    min-width: 160px;
    flex: 1;
    max-width: 200px;
}
.quarantine-filter-panel .filter-group label {
    font-size: 0.75rem;
    font-weight: 600;
    color: #6c757d;
    margin-bottom: 0.375rem;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}
.quarantine-filter-panel .filter-group select {
    font-size: 0.85rem;
    padding: 0.375rem 0.75rem;
    border: 1px solid #ced4da;
    border-radius: 4px;
}
.quarantine-filter-panel .filter-actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
    padding-top: 0.75rem;
    border-top: 1px solid #e9ecef;
}
.quarantine-filter-panel .btn-reset {
    background: transparent;
    border: 1px solid #6c757d;
    color: #6c757d;
    font-size: 0.85rem;
    padding: 0.375rem 1rem;
}
.quarantine-filter-panel .btn-reset:hover {
    background: #f8f9fa;
}
.quarantine-filter-panel .btn-apply {
    background: #1e3a5f;
    border: 1px solid #1e3a5f;
    color: #fff;
    font-size: 0.85rem;
    padding: 0.375rem 1rem;
}
.quarantine-filter-panel .btn-apply:hover {
    background: #152d4a;
}
.sec-table {
    width: 100%;
    margin: 0;
    border-collapse: collapse;
}
.sec-table thead {
    background: #f8f9fc;
    border-bottom: 2px solid #e5e9f2;
}
.sec-table th {
    padding: 0.5rem 0.35rem;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #1e3a5f;
    text-align: left;
    white-space: nowrap;
    cursor: pointer;
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
}
.sec-table th:hover {
    background: #e9ecef;
}
.sec-table th i.fa-sort {
    margin-left: 0.35rem;
    opacity: 0.4;
    font-size: 0.65rem;
}
.sec-table th:hover i.fa-sort {
    opacity: 0.7;
}
.sec-table td {
    padding: 0.5rem 0.35rem;
    font-size: 0.8rem;
    border-bottom: 1px solid #f1f3f5;
    vertical-align: middle;
}
.sec-table tbody tr:hover {
    background: #f8f9fc;
}
.sec-status-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.6rem;
    border-radius: 50px;
    font-size: 0.7rem;
    font-weight: 600;
}
.sec-status-badge.active {
    background: #c6f6d5;
    color: #276749;
}
.sec-status-badge.blocked {
    background: #fed7d7;
    color: #9b2c2c;
}
.sec-status-badge.pending {
    background: #fefcbf;
    color: #744210;
}
.sec-status-badge.draft {
    background: #e2e8f0;
    color: #4a5568;
}
.sec-status-badge.disabled {
    background: #f3f4f6;
    color: #6b7280;
}
.mapping-chip {
    display: inline-block;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    border-radius: 4px;
    padding: 2px 6px;
    margin: 1px 2px;
    font-size: 0.75rem;
}
.mapping-chip code {
    background: #e8f4fd;
    padding: 1px 3px;
    border-radius: 2px;
    font-family: 'Courier New', monospace;
    color: #1e3a5f;
}
.base-char-display {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%);
    color: white;
    font-size: 1.2rem;
    font-weight: 700;
    font-family: 'Courier New', monospace;
    border-radius: 6px;
}
.equiv-chip {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 24px;
    height: 24px;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    border-radius: 4px;
    padding: 0 4px;
    margin: 1px 2px;
    font-size: 0.85rem;
    font-family: 'Courier New', monospace;
}
.scope-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 26px;
    height: 26px;
    border-radius: 4px;
    margin-right: 3px;
    font-size: 0.7rem;
}
.risk-badge {
    display: inline-flex;
    align-items: center;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 500;
}
.norm-admin-table {
    margin-bottom: 0;
}
.norm-admin-table thead th {
    padding: 0.5rem 0.35rem;
    font-size: 0.75rem;
    font-weight: 600;
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
    color: #495057;
    white-space: nowrap;
    vertical-align: middle;
}
.norm-admin-table thead th.sortable {
    cursor: pointer;
    user-select: none;
}
.norm-admin-table thead th.sortable:hover {
    background: #e9ecef;
}
.norm-admin-table thead th .sort-icon {
    color: #adb5bd;
    margin-left: 4px;
    font-size: 0.65rem;
}
.norm-admin-table thead th.sort-asc .sort-icon,
.norm-admin-table thead th.sort-desc .sort-icon {
    color: #1e3a5f;
}
.norm-admin-table tbody td {
    padding: 0.5rem 0.35rem;
    font-size: 0.8rem;
    border-bottom: 1px solid #f1f3f5;
    vertical-align: middle;
}
.norm-admin-table tbody tr:hover {
    background: #f8fafc;
}
.norm-status-pill {
    display: inline-flex;
    align-items: center;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
}
.norm-status-pill.enabled {
    background: #d1fae5;
    color: #065f46;
}
.norm-status-pill.disabled {
    background: #f3f4f6;
    color: #6b7280;
}
.norm-risk-pill {
    display: inline-flex;
    align-items: center;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 0.7rem;
    font-weight: 600;
}
.norm-risk-pill.high {
    background: #fee2e2;
    color: #991b1b;
}
.norm-risk-pill.medium {
    background: #fef3c7;
    color: #92400e;
}
.norm-risk-pill.low {
    background: #dbeafe;
    color: #1e40af;
}
.norm-risk-pill.none {
    background: #f3f4f6;
    color: #6b7280;
}
.norm-scope-pill {
    display: inline-flex;
    align-items: center;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 0.65rem;
    font-weight: 500;
    margin-right: 3px;
}
.norm-scope-pill.senderid {
    background: #d9770620;
    color: #d97706;
    border: 1px solid #d9770640;
}
.norm-scope-pill.content {
    background: #2563eb20;
    color: #2563eb;
    border: 1px solid #2563eb40;
}
.norm-scope-pill.url {
    background: #7c3aed20;
    color: #7c3aed;
    border: 1px solid #7c3aed40;
}
.norm-updated-text {
    font-size: 0.75rem;
    color: #6b7280;
}
.norm-admin-table tbody tr.expandable {
    cursor: pointer;
}
.norm-admin-table tbody tr.expandable:hover {
    background: #f0f7ff;
}
.norm-admin-table tbody tr.expanded {
    background: #f0f7ff;
    border-left: 3px solid #1e3a5f;
}
.norm-admin-table tbody tr.expansion-row {
    background: #f8fafc;
}
.norm-admin-table tbody tr.expansion-row:hover {
    background: #f8fafc;
}
.norm-expansion-content {
    padding: 1rem 1.5rem;
    border-left: 3px solid #1e3a5f;
    background: linear-gradient(to right, #f0f7ff 0%, #f8fafc 100%);
}
.norm-expansion-section {
    margin-bottom: 1rem;
}
.norm-expansion-section:last-child {
    margin-bottom: 0;
}
.norm-expansion-label {
    font-size: 0.7rem;
    font-weight: 600;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.35rem;
}
.norm-expansion-value {
    font-size: 0.85rem;
    color: #1f2937;
}
.equiv-full-list {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}
.equiv-full-chip {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 32px;
    height: 32px;
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 0 8px;
    font-size: 1rem;
    font-family: 'Courier New', monospace;
    transition: all 0.15s ease;
}
.equiv-full-chip:hover {
    background: #1e3a5f;
    color: white;
    border-color: #1e3a5f;
}
.equiv-codepoint {
    font-size: 0.65rem;
    color: #9ca3af;
    margin-left: 4px;
}
.norm-codepoints-toggle {
    font-size: 0.75rem;
    color: #1e3a5f;
    cursor: pointer;
    text-decoration: underline;
}
.norm-codepoints-toggle:hover {
    color: #2c5282;
}
.norm-notes-text {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 0.5rem 0.75rem;
    font-size: 0.85rem;
    color: #374151;
    font-style: italic;
}
.norm-history-item {
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
    padding: 0.5rem 0;
    border-bottom: 1px solid #e5e7eb;
}
.norm-history-item:last-child {
    border-bottom: none;
}
.norm-history-icon {
    width: 24px;
    height: 24px;
    background: #e0e7ff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.65rem;
    color: #1e3a5f;
    flex-shrink: 0;
}
.norm-history-text {
    font-size: 0.8rem;
    color: #4b5563;
}
.norm-history-time {
    font-size: 0.7rem;
    color: #9ca3af;
}
.norm-scope-toggle {
    padding: 0.5rem 1rem;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    background: white;
    color: #6b7280;
    font-weight: 500;
    transition: all 0.15s ease;
}
.norm-scope-toggle:hover {
    border-color: #1e3a5f;
    color: #1e3a5f;
}
.norm-scope-toggle.active {
    background: #1e3a5f;
    border-color: #1e3a5f;
    color: white;
}
.norm-equiv-container {
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    padding: 0.75rem;
    min-height: 80px;
    background: #fafbfc;
    transition: border-color 0.15s ease;
}
.norm-equiv-container:focus-within {
    border-color: #1e3a5f;
    background: white;
}
.norm-equiv-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 8px;
}
.norm-equiv-tag {
    display: inline-flex;
    align-items: center;
    background: white;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    padding: 6px 10px;
    font-family: 'Courier New', monospace;
    font-size: 1.1rem;
    font-weight: 600;
    color: #1e3a5f;
    transition: all 0.15s ease;
}
.norm-equiv-tag:hover {
    background: #f0f7ff;
    border-color: #1e3a5f;
}
.norm-equiv-code {
    font-size: 0.65rem;
    color: #9ca3af;
    margin-left: 6px;
    font-weight: 400;
}
.norm-equiv-remove {
    margin-left: 8px;
    color: #9ca3af;
    cursor: pointer;
    font-size: 0.75rem;
    transition: color 0.15s ease;
}
.norm-equiv-remove:hover {
    color: #dc2626;
}
.norm-equiv-input-wrap {
    margin-top: 4px;
}
.norm-equiv-input {
    border: none;
    background: transparent;
    font-family: 'Courier New', monospace;
    font-size: 1.1rem;
    padding: 4px 8px;
    width: 100%;
    outline: none;
}
.norm-equiv-input::placeholder {
    color: #9ca3af;
    font-size: 0.85rem;
    font-family: inherit;
}
.norm-equiv-char {
    font-size: 1.1rem;
    font-weight: 600;
}
.norm-equiv-encoding {
    font-size: 0.6rem;
    padding: 2px 4px;
    border-radius: 3px;
    margin-left: 6px;
    font-weight: 500;
    text-transform: uppercase;
}
.norm-equiv-gsm {
    background: #d1fae5;
    color: #065f46;
}
.norm-equiv-unicode {
    background: #dbeafe;
    color: #1e40af;
}
.norm-char-picker {
    margin-top: 12px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    background: #f8fafc;
    overflow: hidden;
}
.norm-char-picker-header {
    background: #1e3a5f;
    color: white;
    padding: 8px 12px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.norm-char-picker-title {
    font-size: 0.85rem;
    font-weight: 500;
}
.norm-char-picker-section {
    padding: 10px 12px;
    border-bottom: 1px solid #e2e8f0;
}
.norm-char-picker-section:last-child {
    border-bottom: none;
}
.norm-char-picker-label {
    font-size: 0.75rem;
    color: #64748b;
    margin-bottom: 8px;
    font-weight: 500;
}
.norm-char-picker-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
}
.norm-char-btn {
    width: 32px;
    height: 32px;
    border: 1px solid #e2e8f0;
    background: white;
    border-radius: 4px;
    font-family: 'Courier New', monospace;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.15s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}
.norm-char-btn:hover {
    background: #1e3a5f;
    color: white;
    border-color: #1e3a5f;
    transform: scale(1.1);
}
.norm-test-mode-btn {
    padding: 0.5rem 1.25rem;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    background: white;
    color: #6b7280;
    font-weight: 500;
    transition: all 0.15s ease;
}
.norm-test-mode-btn:hover {
    border-color: #1e3a5f;
    color: #1e3a5f;
}
.norm-test-mode-btn.active {
    background: #1e3a5f;
    border-color: #1e3a5f;
    color: white;
}
.norm-highlight-sub {
    background: #fee2e2;
    color: #991b1b;
    padding: 2px 4px;
    border-radius: 3px;
    font-weight: 600;
}
.norm-highlight-base {
    background: #d1fae5;
    color: #065f46;
    padding: 2px 4px;
    border-radius: 3px;
    font-weight: 600;
}
.norm-sub-chip {
    display: inline-flex;
    align-items: center;
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 8px 12px;
    font-family: 'Courier New', monospace;
}
.norm-sub-original {
    font-size: 1.2rem;
    font-weight: 600;
    color: #991b1b;
    background: #fee2e2;
    padding: 2px 6px;
    border-radius: 4px;
}
.norm-sub-base {
    font-size: 1.2rem;
    font-weight: 600;
    color: #065f46;
    background: #d1fae5;
    padding: 2px 6px;
    border-radius: 4px;
}
.norm-sub-info {
    font-size: 0.7rem;
    color: #9ca3af;
    margin-left: 8px;
}
.norm-matched-rule {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 8px 12px;
}
.norm-matched-rule-name {
    font-size: 0.75rem;
    color: #6b7280;
    text-transform: uppercase;
    font-weight: 600;
}
.norm-matched-rule-pattern {
    font-family: 'Courier New', monospace;
    font-weight: 600;
    color: #1e3a5f;
}
.norm-export-format-btn,
.norm-import-mode-btn {
    padding: 0.75rem 1.25rem;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    background: white;
    color: #6b7280;
    font-weight: 500;
    transition: all 0.15s ease;
    text-align: left;
}
.norm-export-format-btn:hover,
.norm-import-mode-btn:hover {
    border-color: #1e3a5f;
    color: #1e3a5f;
}
.norm-export-format-btn.active,
.norm-import-mode-btn.active {
    background: #1e3a5f;
    border-color: #1e3a5f;
    color: white;
}
.norm-import-mode-btn.active small {
    color: rgba(255,255,255,0.7) !important;
}
.norm-version-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.norm-version-item {
    background: #fafbfc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 12px 16px;
}
.norm-version-current {
    background: #e8f4fd;
    border-color: #1e3a5f;
}
.norm-version-snapshot {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 8px 12px;
    font-size: 0.85rem;
}
.sec-filter-row {
    display: flex;
    gap: 1rem;
    padding: 1rem 1.25rem;
    background: #fff;
    border-bottom: 1px solid #e9ecef;
    flex-wrap: wrap;
    align-items: flex-end;
}
.sec-filter-group {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}
.sec-filter-group label {
    font-size: 0.7rem;
    font-weight: 600;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.sec-filter-group select,
.sec-filter-group input {
    font-size: 0.85rem;
    padding: 0.4rem 0.75rem;
    border: 1px solid #ced4da;
    border-radius: 6px;
    min-width: 150px;
}
.sec-filter-group select:focus,
.sec-filter-group input:focus {
    border-color: #1e3a5f;
    box-shadow: 0 0 0 2px rgba(30, 58, 95, 0.1);
    outline: none;
}
.sec-filter-actions {
    display: flex;
    gap: 0.5rem;
    margin-left: auto;
}
.sec-btn-primary {
    background: #1e3a5f;
    color: #fff;
    border: none;
    padding: 0.5rem 1rem;
    font-size: 0.85rem;
    font-weight: 500;
    border-radius: 6px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    transition: all 0.15s;
}
.sec-btn-primary:hover {
    background: #2c5282;
}
.sec-btn-outline {
    background: transparent;
    color: #6c757d;
    border: 1px solid #ced4da;
    padding: 0.5rem 1rem;
    font-size: 0.85rem;
    font-weight: 500;
    border-radius: 6px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    transition: all 0.15s;
}
.sec-btn-outline:hover {
    background: #f8f9fa;
    border-color: #1e3a5f;
    color: #1e3a5f;
}
.action-menu-btn {
    background: transparent;
    border: none;
    color: #6c757d;
    padding: 0.25rem 0.5rem;
    cursor: pointer;
    border-radius: 4px;
    transition: all 0.15s;
}
.action-menu-btn:hover {
    color: #1e3a5f;
    background: rgba(30, 58, 95, 0.08);
}
.sec-empty-state {
    text-align: center;
    padding: 3rem 2rem;
    color: #6c757d;
}
.sec-empty-state i {
    font-size: 3rem;
    opacity: 0.3;
    margin-bottom: 1rem;
}
.sec-empty-state h6 {
    color: #1e3a5f;
    font-weight: 600;
    margin-bottom: 0.5rem;
}
.sec-empty-state p {
    font-size: 0.85rem;
    margin: 0;
}
.sec-sync-status {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.8rem;
    color: #48bb78;
}
.sec-sync-status i {
    font-size: 0.7rem;
}
.sec-refresh-btn {
    background: transparent;
    border: 1px solid #ced4da;
    color: #6c757d;
    padding: 0.4rem 0.75rem;
    font-size: 0.8rem;
    border-radius: 6px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    transition: all 0.15s;
}
.sec-refresh-btn:hover {
    border-color: #1e3a5f;
    color: #1e3a5f;
}
.tab-description {
    background: #f8f9fc;
    border: 1px solid #e9ecef;
    border-radius: 0.5rem;
    padding: 1rem 1.25rem;
    margin-bottom: 1.5rem;
}
.tab-description h6 {
    margin: 0 0 0.5rem 0;
    font-weight: 600;
    color: #1e3a5f;
}
.tab-description p {
    margin: 0;
    font-size: 0.85rem;
    color: #6c757d;
}
</style>
@endpush

@section('content')
<div class="spam-filter-page">
        <div class="page-titles">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
                <li class="breadcrumb-item"><a href="javascript:void(0)">Security & Compliance</a></li>
                <li class="breadcrumb-item active">Spam Filter</li>
            </ol>
        </div>

        <div class="sec-controls-header">
            <div class="sec-controls-title">
                <h4><i class="fas fa-shield-alt me-2" style="color: #1e3a5f;"></i> Spam Filter <span class="admin-internal-badge">ADMIN ONLY</span></h4>
                <p>Manage spam filtering rules, content policies, and security controls across the platform</p>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="sec-sync-status">
                    <i class="fas fa-check-circle"></i>
                    All systems synchronized
                </span>
                <button class="sec-btn sec-btn-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#featureFlagsModal" title="Manage Feature Flags">
                    <i class="fas fa-toggle-on me-1"></i> Feature Flags
                </button>
                <button class="sec-refresh-btn" onclick="refreshAllControls()">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
            </div>
        </div>

        <ul class="nav admin-tabs" id="securityControlsTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="quarantine-review-tab" data-bs-toggle="tab" data-bs-target="#quarantine-review" type="button" role="tab">
                    <i class="fas fa-exclamation-triangle me-1"></i> Quarantine Review
                    <span class="badge pending-badge">12</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="senderid-controls-tab" data-bs-toggle="tab" data-bs-target="#senderid-controls" type="button" role="tab">
                    <i class="fas fa-id-badge me-1"></i> SenderID Controls
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="message-content-tab" data-bs-toggle="tab" data-bs-target="#message-content" type="button" role="tab">
                    <i class="fas fa-comment-alt me-1"></i> Message Content Controls
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="url-controls-tab" data-bs-toggle="tab" data-bs-target="#url-controls" type="button" role="tab">
                    <i class="fas fa-link me-1"></i> URL Controls
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="normalisation-rules-tab" data-bs-toggle="tab" data-bs-target="#normalisation-rules" type="button" role="tab">
                    <i class="fas fa-globe me-1"></i> Normalisation Rules (Global)
                </button>
            </li>
        </ul>

        <div class="tab-content" id="securityControlsTabContent">
            <div class="tab-pane fade" id="senderid-controls" role="tabpanel">
                <div class="sec-table-card">
                    <div class="sec-toolbar">
                        <div class="sec-search-box-left">
                            <i class="fas fa-search"></i>
                            <input type="text" class="form-control" placeholder="Search rules..." id="senderid-search">
                        </div>
                        <div class="sec-toolbar-actions">
                            <div class="dropdown">
                                <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" id="senderid-filter-btn">
                                    <i class="fas fa-filter me-1"></i> Filter
                                </button>
                                <div class="dropdown-menu dropdown-menu-end p-3" style="min-width: 280px;" onclick="event.stopPropagation()">
                                    <div class="mb-3">
                                        <label class="form-label small fw-semibold">Status</label>
                                        <select class="form-select form-select-sm" id="senderid-filter-status">
                                            <option value="">All Statuses</option>
                                            <option value="active">Active</option>
                                            <option value="disabled">Disabled</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-semibold">Rule Type</label>
                                        <select class="form-select form-select-sm" id="senderid-filter-type">
                                            <option value="">All Types</option>
                                            <option value="block">Block</option>
                                            <option value="flag">Flag (Quarantine)</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-semibold">Category</label>
                                        <select class="form-select form-select-sm" id="senderid-filter-category">
                                            <option value="">All Categories</option>
                                            <option value="bank_impersonation">Bank Impersonation</option>
                                            <option value="government">Government</option>
                                            <option value="lottery_prize">Lottery/Prize</option>
                                            <option value="brand_abuse">Brand Abuse</option>
                                            <option value="premium_rate">Premium Rate</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-sm btn-outline-secondary flex-fill" onclick="resetSenderIdFilters()">
                                            <i class="fas fa-undo me-1"></i> Reset
                                        </button>
                                        <button class="btn btn-sm btn-primary flex-fill" onclick="applySenderIdFilters()">
                                            Apply
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <button class="btn btn-primary btn-sm" onclick="showAddSenderIdRuleModal()">
                                <i class="fas fa-plus me-1"></i> Add Rule
                            </button>
                        </div>
                    </div>
                    <div class="sec-table-header">
                        <h6>SenderID Rule Library</h6>
                    </div>
                    <div class="table-responsive">
                        <table class="sec-table" id="senderid-rules-table">
                            <thead>
                                <tr>
                                    <th>Rule Name <i class="fas fa-sort"></i></th>
                                    <th>Base SenderID <i class="fas fa-sort"></i></th>
                                    <th>Rule Type <i class="fas fa-sort"></i></th>
                                    <th>Category <i class="fas fa-sort"></i></th>
                                    <th>Normalisation <i class="fas fa-sort"></i></th>
                                    <th>Status <i class="fas fa-sort"></i></th>
                                    <th>Created By <i class="fas fa-sort"></i></th>
                                    <th>Last Updated <i class="fas fa-sort"></i></th>
                                    <th style="width: 80px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="senderid-rules-body">
                            </tbody>
                        </table>
                    </div>
                    <div class="sec-empty-state" id="senderid-empty-state" style="display: none;">
                        <i class="fas fa-id-badge"></i>
                        <h6>No SenderID Rules</h6>
                        <p>Create rules to control which SenderIDs can be used on the platform.</p>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="message-content" role="tabpanel">
                <div class="tab-description">
                    <h6><i class="fas fa-comment-alt me-2"></i>Message Content Controls</h6>
                    <p>Configure content filtering rules, banned keywords, and message scanning policies to ensure compliance.</p>
                </div>

                
                <div class="card mb-3" style="border: 1px solid #e9ecef; border-left: 3px solid #1e3a5f;">
                    <div class="card-header py-2 d-flex justify-content-between align-items-center" style="background: #f8f9fa;">
                        <h6 class="mb-0" style="font-size: 0.9rem; font-weight: 600;">
                            <i class="fas fa-shield-virus me-2" style="color: #1e3a5f;"></i>Anti-Spam Controls
                        </h6>
                        <span class="badge text-white" style="background: #1e3a5f; font-size: 0.65rem;">SUPPLEMENTARY</span>
                    </div>
                    <div class="card-body py-3">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="antispam-repeat-toggle" onchange="toggleAntiSpamRepeat()">
                                    <label class="form-check-label" for="antispam-repeat-toggle" style="font-size: 0.85rem;">
                                        <strong>Prevent identical content to same MSISDN within window</strong>
                                    </label>
                                </div>
                                <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">
                                    When enabled, blocks duplicate messages sent to the same recipient within the configured time window.
                                </small>
                            </div>
                            <div class="col-md-3">
                                <label for="antispam-window" class="form-label mb-1" style="font-size: 0.8rem; font-weight: 600;">Time Window</label>
                                <select class="form-select form-select-sm" id="antispam-window" onchange="updateAntiSpamWindow()" disabled>
                                    <option value="2">2 hours</option>
                                    <option value="4">4 hours</option>
                                    <option value="12">12 hours</option>
                                    <option value="24" selected>24 hours</option>
                                    <option value="48">48 hours</option>
                                </select>
                            </div>
                            <div class="col-md-3 text-end">
                                <div id="antispam-status" class="d-inline-block">
                                    <span class="badge bg-secondary" style="font-size: 0.75rem;">
                                        <i class="fas fa-toggle-off me-1"></i> Disabled
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 p-2 bg-light rounded" style="font-size: 0.75rem; border: 1px solid #e9ecef;" id="antispam-info">
                            <i class="fas fa-info-circle me-1 text-muted"></i>
                            <span class="text-muted">Enforcement is handled globally via the shared Message Enforcement Service. Blocked events will include reason: "Repeated content within window".</span>
                        </div>
                    </div>
                </div>

                <div class="sec-table-card">
                    <div class="sec-toolbar">
                        <div class="sec-search-box-left">
                            <i class="fas fa-search"></i>
                            <input type="text" class="form-control" placeholder="Search rules..." id="content-search">
                        </div>
                        <div class="sec-toolbar-actions">
                            <div class="dropdown">
                                <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" id="content-filter-btn">
                                    <i class="fas fa-filter me-1"></i> Filter
                                </button>
                                <div class="dropdown-menu dropdown-menu-end p-3" style="min-width: 280px;" onclick="event.stopPropagation()">
                                    <div class="mb-3">
                                        <label class="form-label small fw-semibold">Status</label>
                                        <select class="form-select form-select-sm" id="content-filter-status">
                                            <option value="">All Statuses</option>
                                            <option value="active">Active</option>
                                            <option value="disabled">Disabled</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-semibold">Match Type</label>
                                        <select class="form-select form-select-sm" id="content-filter-matchtype">
                                            <option value="">All Types</option>
                                            <option value="keyword">Keyword</option>
                                            <option value="regex">Regex</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-semibold">Rule Type</label>
                                        <select class="form-select form-select-sm" id="content-filter-ruletype">
                                            <option value="">All Types</option>
                                            <option value="block">Block</option>
                                            <option value="flag">Flag (Quarantine)</option>
                                        </select>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-sm btn-outline-secondary flex-fill" onclick="resetContentFilters()">
                                            <i class="fas fa-undo me-1"></i> Reset
                                        </button>
                                        <button class="btn btn-sm btn-primary flex-fill" onclick="applyContentFilters()">
                                            Apply
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <button class="btn btn-primary btn-sm" onclick="showAddContentRuleModal()">
                                <i class="fas fa-plus me-1"></i> Add Rule
                            </button>
                        </div>
                    </div>
                    <div class="sec-table-header">
                        <h6>Content Rule Library</h6>
                    </div>
                    <div class="table-responsive">
                        <table class="sec-table" id="content-rules-table">
                            <thead>
                                <tr>
                                    <th>Rule Name <i class="fas fa-sort"></i></th>
                                    <th>Match Type <i class="fas fa-sort"></i></th>
                                    <th>Rule Type <i class="fas fa-sort"></i></th>
                                    <th>Normalisation <i class="fas fa-sort"></i></th>
                                    <th>Status <i class="fas fa-sort"></i></th>
                                    <th>Last Updated <i class="fas fa-sort"></i></th>
                                    <th style="width: 80px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="content-rules-body">
                            </tbody>
                        </table>
                    </div>
                    <div class="sec-empty-state" id="content-empty-state" style="display: none;">
                        <i class="fas fa-comment-alt"></i>
                        <h6>No Content Rules</h6>
                        <p>Create content filtering rules to manage message compliance.</p>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="url-controls" role="tabpanel">
                <div class="tab-description">
                    <h6><i class="fas fa-link me-2"></i>URL Controls</h6>
                    <p>Manage URL domain/pattern rules, domain age controls, and per-account exceptions for link enforcement.</p>
                </div>

                
                <div class="sec-table-card" style="margin-bottom: 1.5rem;">
                    <div class="sec-table-header" style="border-bottom: 1px solid #e9ecef; padding-bottom: 0.75rem; margin-bottom: 1rem;">
                        <h6 style="margin: 0;"><i class="fas fa-clock me-2" style="color: #1e3a5f;"></i>Domain Age Control</h6>
                    </div>
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center gap-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="domain-age-enabled" style="width: 2.5rem; height: 1.25rem;">
                                    <label class="form-check-label" for="domain-age-enabled" style="font-weight: 600; margin-left: 0.5rem;">
                                        Enable Domain Age Check
                                    </label>
                                </div>
                            </div>
                            <small class="text-muted d-block mt-1">When enabled, newly registered domains will be blocked or flagged based on their age.</small>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" style="font-size: 0.8rem; font-weight: 600;">Block domains younger than</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="domain-age-hours" value="72" min="1" max="8760" disabled>
                                <span class="input-group-text">hours</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" style="font-size: 0.8rem; font-weight: 600;">Action</label>
                            <select class="form-select" id="domain-age-action" disabled>
                                <option value="block">Block</option>
                                <option value="flag">Flag (Quarantine)</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-3 text-end">
                        <button class="btn btn-sm text-white" style="background: #1e3a5f;" onclick="saveDomainAgeSettings()">
                            <i class="fas fa-save me-1"></i> Save Settings
                        </button>
                    </div>
                </div>

                <div class="sec-table-card">
                    <div class="sec-toolbar">
                        <div class="sec-search-box-left">
                            <i class="fas fa-search"></i>
                            <input type="text" class="form-control" placeholder="Search domains/patterns..." id="url-search">
                        </div>
                        <div class="sec-toolbar-actions">
                            <div class="dropdown">
                                <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" id="url-filter-btn">
                                    <i class="fas fa-filter me-1"></i> Filter
                                </button>
                                <div class="dropdown-menu dropdown-menu-end p-3" style="min-width: 280px;" onclick="event.stopPropagation()">
                                    <div class="mb-3">
                                        <label class="form-label small fw-semibold">Status</label>
                                        <select class="form-select form-select-sm" id="url-filter-status">
                                            <option value="">All Statuses</option>
                                            <option value="active">Active</option>
                                            <option value="disabled">Disabled</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-semibold">Match Type</label>
                                        <select class="form-select form-select-sm" id="url-filter-matchtype">
                                            <option value="">All Types</option>
                                            <option value="exact">Exact Domain</option>
                                            <option value="wildcard">Wildcard</option>
                                            <option value="regex">Regex</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-semibold">Rule Type</label>
                                        <select class="form-select form-select-sm" id="url-filter-ruletype">
                                            <option value="">All Types</option>
                                            <option value="block">Block</option>
                                            <option value="flag">Flag</option>
                                        </select>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-sm btn-outline-secondary flex-fill" onclick="resetUrlFilters()">
                                            <i class="fas fa-undo me-1"></i> Reset
                                        </button>
                                        <button class="btn btn-sm btn-primary flex-fill" onclick="applyUrlFilters()">
                                            Apply
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <button class="btn btn-primary btn-sm" onclick="showAddUrlRuleModal()">
                                <i class="fas fa-plus me-1"></i> Add Rule
                            </button>
                        </div>
                    </div>
                    <div class="sec-table-header">
                        <h6>URL Rule Library</h6>
                    </div>
                    <div class="table-responsive">
                        <table class="sec-table" id="url-rules-table">
                            <thead>
                                <tr>
                                    <th>Domain/Pattern <i class="fas fa-sort"></i></th>
                                    <th>Match Type <i class="fas fa-sort"></i></th>
                                    <th>Rule Type <i class="fas fa-sort"></i></th>
                                    <th>Domain Age <i class="fas fa-sort"></i></th>
                                    <th>Status <i class="fas fa-sort"></i></th>
                                    <th>Last Updated <i class="fas fa-sort"></i></th>
                                    <th style="width: 80px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="url-rules-body">
                            </tbody>
                        </table>
                    </div>
                    <div class="sec-empty-state" id="url-empty-state" style="display: none;">
                        <i class="fas fa-link"></i>
                        <h6>No URL Rules</h6>
                        <p>Add domain/pattern rules to control URL usage in messages.</p>
                    </div>
                </div>

                <div class="sec-table-card" style="margin-top: 1.5rem;">
                    <div class="sec-table-header" style="border-bottom: 1px solid #e9ecef; padding-bottom: 0.75rem; margin-bottom: 1rem;">
                        <h6 style="margin: 0;"><i class="fas fa-user-shield me-2" style="color: #1e3a5f;"></i>Per-Account Domain Age Exceptions</h6>
                        <button class="sec-btn-primary" onclick="showAddDomainAgeExceptionModal()">
                            <i class="fas fa-plus"></i> Add Exception
                        </button>
                    </div>
                    <p class="text-muted" style="font-size: 0.85rem; margin-bottom: 1rem;">
                        Accounts listed below are exempt from domain age checks. All exceptions are logged in the audit trail.
                    </p>
                    <div class="table-responsive">
                        <table class="sec-table" id="domain-age-exceptions-table">
                            <thead>
                                <tr>
                                    <th>Account ID <i class="fas fa-sort"></i></th>
                                    <th>Account Name <i class="fas fa-sort"></i></th>
                                    <th>Reason <i class="fas fa-sort"></i></th>
                                    <th>Added By <i class="fas fa-sort"></i></th>
                                    <th>Added On <i class="fas fa-sort"></i></th>
                                    <th style="width: 80px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="domain-age-exceptions-body">
                            </tbody>
                        </table>
                    </div>
                    <div class="sec-empty-state" id="domain-exceptions-empty-state" style="display: none;">
                        <i class="fas fa-user-check"></i>
                        <h6>No Exceptions</h6>
                        <p>All accounts are subject to domain age checks when enabled.</p>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="normalisation-rules" role="tabpanel">
                <div class="norm-page-header mb-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h5 class="mb-1" style="color: #1e3a5f; font-weight: 600;">
                                <i class="fas fa-globe me-2"></i>Normalisation Rules
                            </h5>
                            <p class="text-muted mb-0" style="font-size: 0.9rem; max-width: 600px;">
                                Define character equivalence rules used to detect sender/content bastardisation (e.g. O=0, I=1). Changes affect matching across the platform.
                            </p>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-outline-secondary" onclick="showImportNormLibraryModal()">
                                <i class="fas fa-upload me-1"></i>Import
                            </button>
                            <button class="btn btn-sm btn-outline-secondary" onclick="exportBaseCharacterLibrary()">
                                <i class="fas fa-download me-1"></i>Export
                            </button>
                            <button class="btn btn-sm btn-outline-secondary" onclick="testNormalisationRule()">
                                <i class="fas fa-flask me-1"></i>Test
                            </button>
                            <button class="btn btn-sm" style="background: #1e3a5f; color: white;" onclick="showNormRuleModal()">
                                <i class="fas fa-plus me-1"></i>Add Rule
                            </button>
                        </div>
                    </div>
                    
                    <div class="norm-controls-bar p-3" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted mb-1">Search</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                                    <input type="text" class="form-control" id="norm-global-search" placeholder="Search base or equivalent character..." onkeyup="globalNormSearch()">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted mb-1">Applies To</label>
                                <select class="form-select" id="norm-global-scope" onchange="globalNormFilter()">
                                    <option value="">All Scopes</option>
                                    <option value="senderid">SenderID</option>
                                    <option value="content">Content</option>
                                    <option value="url">URL</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted mb-1">Status</label>
                                <select class="form-select" id="norm-global-status" onchange="globalNormFilter()">
                                    <option value="">All Statuses</option>
                                    <option value="enabled">Enabled</option>
                                    <option value="disabled">Disabled</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted mb-1">Risk Level</label>
                                <select class="form-select" id="norm-global-risk" onchange="globalNormFilter()">
                                    <option value="">All Levels</option>
                                    <option value="high">High</option>
                                    <option value="medium">Medium</option>
                                    <option value="low">Low</option>
                                    <option value="none">None</option>
                                </select>
                            </div>
                            <div class="col-md-2 text-end">
                                <button class="btn btn-outline-secondary btn-sm" onclick="resetGlobalNormFilters()">
                                    <i class="fas fa-undo me-1"></i>Reset
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info d-flex align-items-start mb-3" style="background: #e8f4fd; border: 1px solid #1e3a5f; border-radius: 6px;">
                    <i class="fas fa-info-circle me-2 mt-1" style="color: #1e3a5f;"></i>
                    <div>
                        <strong>Consuming Engines:</strong> These normalisation rules are consumed by the enforcement engines below. Changes apply globally.
                        <div class="d-flex gap-2 mt-2">
                            <span class="badge" style="background: #d97706; color: white;"><i class="fas fa-id-badge me-1"></i>SenderID Matching</span>
                            <span class="badge" style="background: #2563eb; color: white;"><i class="fas fa-comment-alt me-1"></i>Content Matching</span>
                            <span class="badge" style="background: #7c3aed; color: white;" id="url-engine-badge"><i class="fas fa-link me-1"></i>URL Matching <small>(guarded)</small></span>
                        </div>
                    </div>
                </div>

                <div class="sec-stats">
                    <div class="sec-stat-card active">
                        <div class="sec-stat-value" id="norm-enabled-count">0</div>
                        <div class="sec-stat-label">Enabled</div>
                    </div>
                    <div class="sec-stat-card blocked">
                        <div class="sec-stat-value" id="norm-disabled-count">0</div>
                        <div class="sec-stat-label">Disabled</div>
                    </div>
                    <div class="sec-stat-card pending">
                        <div class="sec-stat-value" id="norm-equivalents-count">0</div>
                        <div class="sec-stat-label">Total Equivalents</div>
                    </div>
                    <div class="sec-stat-card total">
                        <div class="sec-stat-value" id="norm-base-count">62</div>
                        <div class="sec-stat-label">Base Characters</div>
                    </div>
                </div>

                <ul class="nav nav-tabs mb-3" id="normCharTabs" role="tablist" style="border-bottom: 2px solid #e9ecef;">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="norm-uppercase-tab" data-bs-toggle="tab" data-bs-target="#norm-uppercase" type="button" role="tab" style="font-weight: 600; color: #1e3a5f;">
                            <i class="fas fa-font me-1"></i>A–Z <span class="badge bg-secondary ms-1">26</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="norm-lowercase-tab" data-bs-toggle="tab" data-bs-target="#norm-lowercase" type="button" role="tab" style="font-weight: 600; color: #1e3a5f;">
                            <i class="fas fa-text-height me-1"></i>a–z <span class="badge bg-secondary ms-1">26</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="norm-digits-tab" data-bs-toggle="tab" data-bs-target="#norm-digits" type="button" role="tab" style="font-weight: 600; color: #1e3a5f;">
                            <i class="fas fa-hashtag me-1"></i>0–9 <span class="badge bg-secondary ms-1">10</span>
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="normCharTabsContent">
                    <div class="tab-pane fade show active" id="norm-uppercase" role="tabpanel">
                        <div class="sec-table-card">
                            <div class="table-responsive">
                                <table class="table table-hover norm-admin-table" id="norm-uppercase-table">
                                    <thead>
                                        <tr>
                                            <th class="sortable" data-sort="base" style="width: 100px;">Base Char <i class="fas fa-sort sort-icon"></i></th>
                                            <th style="min-width: 200px;">Equivalents</th>
                                            <th class="sortable" data-sort="scope" style="width: 160px;">Applies To <i class="fas fa-sort sort-icon"></i></th>
                                            <th class="sortable" data-sort="status" style="width: 100px;">Status <i class="fas fa-sort sort-icon"></i></th>
                                            <th class="sortable" data-sort="risk" style="width: 100px;">Risk <i class="fas fa-sort sort-icon"></i></th>
                                            <th class="sortable" data-sort="updated" style="width: 130px;">Updated <i class="fas fa-sort sort-icon"></i></th>
                                            <th style="width: 70px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="norm-uppercase-body"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="norm-lowercase" role="tabpanel">
                        <div class="sec-table-card">
                            <div class="table-responsive">
                                <table class="table table-hover norm-admin-table" id="norm-lowercase-table">
                                    <thead>
                                        <tr>
                                            <th class="sortable" data-sort="base" style="width: 100px;">Base Char <i class="fas fa-sort sort-icon"></i></th>
                                            <th style="min-width: 200px;">Equivalents</th>
                                            <th class="sortable" data-sort="scope" style="width: 160px;">Applies To <i class="fas fa-sort sort-icon"></i></th>
                                            <th class="sortable" data-sort="status" style="width: 100px;">Status <i class="fas fa-sort sort-icon"></i></th>
                                            <th class="sortable" data-sort="risk" style="width: 100px;">Risk <i class="fas fa-sort sort-icon"></i></th>
                                            <th class="sortable" data-sort="updated" style="width: 130px;">Updated <i class="fas fa-sort sort-icon"></i></th>
                                            <th style="width: 70px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="norm-lowercase-body"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="norm-digits" role="tabpanel">
                        <div class="sec-table-card">
                            <div class="table-responsive">
                                <table class="table table-hover norm-admin-table" id="norm-digits-table">
                                    <thead>
                                        <tr>
                                            <th class="sortable" data-sort="base" style="width: 100px;">Base Char <i class="fas fa-sort sort-icon"></i></th>
                                            <th style="min-width: 200px;">Equivalents</th>
                                            <th class="sortable" data-sort="scope" style="width: 160px;">Applies To <i class="fas fa-sort sort-icon"></i></th>
                                            <th class="sortable" data-sort="status" style="width: 100px;">Status <i class="fas fa-sort sort-icon"></i></th>
                                            <th class="sortable" data-sort="risk" style="width: 100px;">Risk <i class="fas fa-sort sort-icon"></i></th>
                                            <th class="sortable" data-sort="updated" style="width: 130px;">Updated <i class="fas fa-sort sort-icon"></i></th>
                                            <th style="width: 70px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="norm-digits-body"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade show active" id="quarantine-review" role="tabpanel">
                <div class="sec-stats">
                    <div class="sec-stat-card pending" id="tile-awaiting-review" onclick="toggleQuarantineTileFilter('pending')">
                        <div class="sec-stat-value" id="quarantine-pending-count">0</div>
                        <div class="sec-stat-label">Awaiting Review</div>
                    </div>
                    <div class="sec-stat-card active" id="tile-released-today" onclick="toggleQuarantineTileFilter('released')">
                        <div class="sec-stat-value" id="quarantine-released-count">0</div>
                        <div class="sec-stat-label">Released Today</div>
                    </div>
                </div>

                <div class="sec-table-card">
                    <div class="sec-toolbar">
                        <div class="sec-search-box-left">
                            <i class="fas fa-search"></i>
                            <input type="text" class="form-control" placeholder="Search messages, accounts, SenderIDs..." id="quarantine-search">
                        </div>
                        <div class="sec-toolbar-actions">
                            <button class="quarantine-filter-pill-btn" type="button" id="quarantine-filter-btn" onclick="toggleQuarantineFilterPanel()">
                                <i class="fas fa-filter"></i>
                                <span>Filter</span>
                                <span class="badge bg-primary" id="quarantine-filter-count" style="display: none; font-size: 0.7rem; padding: 0.2rem 0.4rem;">0</span>
                            </button>
                            <button class="btn btn-outline-danger btn-sm" id="btn-bulk-block" onclick="showBulkBlockConfirmation()" disabled style="opacity: 0.65; color: #dc3545; border-color: #dc3545;">
                                <i class="fas fa-ban me-1"></i> Block Selected
                            </button>
                            <button class="btn btn-primary btn-sm" id="btn-bulk-release" onclick="showBulkReleaseConfirmation()" disabled style="background-color: #1e3a5f; border-color: #1e3a5f; opacity: 0.65;">
                                <i class="fas fa-check me-1"></i> Release Selected
                            </button>
                        </div>
                    </div>

                    <div class="quarantine-filter-panel" id="quarantine-filter-panel">
                        <div class="filter-body">
                            <div class="filter-row">
                                <div class="filter-group">
                                    <label>Status</label>
                                    <select id="quarantine-filter-status">
                                        <option value="">All Statuses</option>
                                        <option value="pending">Pending</option>
                                        <option value="released">Released</option>
                                        <option value="blocked">Permanently Blocked</option>
                                    </select>
                                </div>
                                <div class="filter-group">
                                    <label>Rule Triggered</label>
                                    <select id="quarantine-filter-rule">
                                        <option value="">All Rules</option>
                                        <option value="senderid">SenderID Rule</option>
                                        <option value="content">Content Rule</option>
                                        <option value="url">URL Rule</option>
                                        <option value="domain_age">Domain Age</option>
                                    </select>
                                </div>
                                <div class="filter-group">
                                    <label>URL Present</label>
                                    <select id="quarantine-filter-url">
                                        <option value="">All</option>
                                        <option value="yes">Yes</option>
                                        <option value="no">No</option>
                                    </select>
                                </div>
                                <div class="filter-group">
                                    <label>Account</label>
                                    <select id="quarantine-filter-account">
                                        <option value="">All Accounts</option>
                                    </select>
                                </div>
                            </div>
                            <div class="filter-actions">
                                <button class="btn btn-reset" onclick="resetQuarantineFilters()"><i class="fas fa-undo me-1"></i> Reset</button>
                                <button class="btn btn-apply" onclick="applyQuarantineFilters()"><i class="fas fa-check me-1"></i> Apply Filters</button>
                            </div>
                        </div>
                    </div>

                    <div class="active-filters-row" id="quarantine-active-filters">
                        <span class="text-muted me-2" style="font-size: 0.7rem;">Active filters:</span>
                        <div id="quarantine-filter-chips" class="d-flex flex-wrap"></div>
                        <button class="btn btn-link btn-sm p-0 ms-2" style="font-size: 0.65rem;" onclick="clearAllQuarantineFilters()">Clear all</button>
                    </div>
                    <div class="sec-table-header">
                        <h6>Quarantine Inbox</h6>
                    </div>
                    <div class="table-responsive">
                        <table class="sec-table" id="quarantine-table">
                            <thead>
                                <tr>
                                    <th style="width: 40px;"><input type="checkbox" id="quarantine-select-all"></th>
                                    <th>Timestamp <i class="fas fa-sort"></i></th>
                                    <th>Account <i class="fas fa-sort"></i></th>
                                    <th>Sub-Account <i class="fas fa-sort"></i></th>
                                    <th>SenderID <i class="fas fa-sort"></i></th>
                                    <th>Message Snippet <i class="fas fa-sort"></i></th>
                                    <th>URL <i class="fas fa-sort"></i></th>
                                    <th>Rule Triggered <i class="fas fa-sort"></i></th>
                                    <th>Status <i class="fas fa-sort"></i></th>
                                    <th>Reviewer <i class="fas fa-sort"></i></th>
                                    <th>Decision At <i class="fas fa-sort"></i></th>
                                    <th style="width: 100px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="quarantine-body">
                            </tbody>
                        </table>
                    </div>
                    <div class="sec-empty-state" id="quarantine-empty-state" style="display: none;">
                        <i class="fas fa-check-circle" style="color: #1e3a5f;"></i>
                        <h6>No Messages in Quarantine</h6>
                        <p>All messages have been reviewed. Check back later for new items.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

<div class="modal fade" id="senderIdRuleModal" tabindex="-1" aria-labelledby="senderIdRuleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: #f8f9fc; border-bottom: 1px solid #e9ecef;">
                <h5 class="modal-title" id="senderIdRuleModalLabel" style="color: #1e3a5f; font-weight: 600;">
                    <i class="fas fa-id-badge me-2"></i><span id="senderid-modal-title">Add SenderID Rule</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="senderid-rule-id" value="">
                
                <div class="mb-3">
                    <label class="form-label" style="font-weight: 500; color: #1e3a5f;">Rule Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="senderid-rule-name" placeholder="e.g., Block HSBC Impersonation" required>
                    <small class="text-muted">A descriptive name for this rule</small>
                </div>

                <div class="mb-3">
                    <label class="form-label" style="font-weight: 500; color: #1e3a5f;">Base SenderID <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="senderid-base-value" placeholder="e.g., HSBC" style="text-transform: uppercase;" required>
                    <small class="text-muted">The canonical SenderID to match (case-insensitive, variants auto-detected)</small>
                </div>

                <div class="mb-3">
                    <label class="form-label" style="font-weight: 500; color: #1e3a5f;">Rule Type <span class="text-danger">*</span></label>
                    <div class="d-flex gap-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="senderid-rule-type" id="senderid-type-block" value="block" checked>
                            <label class="form-check-label" for="senderid-type-block">
                                <span class="badge bg-danger">Block</span>
                                <small class="d-block text-muted">Reject message outright</small>
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="senderid-rule-type" id="senderid-type-flag" value="flag">
                            <label class="form-check-label" for="senderid-type-flag">
                                <span class="badge bg-warning text-dark">Flag</span>
                                <small class="d-block text-muted">Send to quarantine for review</small>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label" style="font-weight: 500; color: #1e3a5f;">Category <span class="text-danger">*</span></label>
                    <select class="form-select" id="senderid-category" required>
                        <option value="">Select a category...</option>
                        <option value="bank_impersonation">Bank Impersonation</option>
                        <option value="government">Government Impersonation</option>
                        <option value="lottery_prize">Lottery/Prize Scam</option>
                        <option value="brand_abuse">Brand Abuse</option>
                        <option value="premium_rate">Premium Rate Services</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="senderid-apply-normalisation" checked>
                        <label class="form-check-label" for="senderid-apply-normalisation" style="font-weight: 500; color: #1e3a5f;">
                            Apply Normalisation Rules
                        </label>
                    </div>
                    <small class="text-muted">When enabled, global normalisation rules will be applied before matching</small>
                </div>

                <div class="p-3 rounded" style="background: #f8f9fc; border: 1px solid #e9ecef;">
                    <h6 style="font-size: 0.8rem; font-weight: 600; color: #1e3a5f; margin-bottom: 0.5rem;">
                        <i class="fas fa-info-circle me-1"></i> Matching Behaviour
                    </h6>
                    <ul style="font-size: 0.75rem; color: #6c757d; margin: 0; padding-left: 1.25rem;">
                        <li>Case-insensitive matching (HSBC = hsbc = HsBc)</li>
                        <li>Character substitution variants detected (0→O, 1→I/L, 5→S)</li>
                        <li>Whitespace and special characters normalized</li>
                        <li id="normalisation-note">Global normalisation rules applied first</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer" style="background: #f8f9fc; border-top: 1px solid #e9ecef;">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn" style="background: #1e3a5f; color: #fff;" onclick="saveSenderIdRule()">
                    <i class="fas fa-save me-1"></i> <span id="senderid-save-btn-text">Save Rule</span>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="senderIdViewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: #f8f9fc; border-bottom: 1px solid #e9ecef;">
                <h5 class="modal-title" style="color: #1e3a5f; font-weight: 600;">
                    <i class="fas fa-eye me-2"></i>View SenderID Rule
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="senderid-view-content">
            </div>
            <div class="modal-footer" style="background: #f8f9fc; border-top: 1px solid #e9ecef;">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header" style="background: #fee2e2; border-bottom: 1px solid #fecaca;">
                <h5 class="modal-title" style="color: #991b1b; font-weight: 600;">
                    <i class="fas fa-exclamation-triangle me-2"></i>Confirm Delete
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="confirm-delete-message">Are you sure you want to delete this rule?</p>
                <p class="text-muted" style="font-size: 0.8rem;">This action cannot be undone.</p>
                <input type="hidden" id="delete-rule-id" value="">
                <input type="hidden" id="delete-rule-type" value="">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger btn-sm" onclick="confirmDeleteRule()">
                    <i class="fas fa-trash me-1"></i> Delete
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="contentRuleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: #1e3a5f; border-bottom: none;">
                <h5 class="modal-title text-white" id="content-rule-modal-title">
                    <i class="fas fa-comment-alt me-2"></i>Add Content Rule
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding: 1.5rem;">
                <form id="content-rule-form">
                    <input type="hidden" id="content-rule-id" value="">
                    
                    <div class="mb-3">
                        <label for="content-rule-name" class="form-label" style="font-weight: 600; font-size: 0.85rem;">Rule Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="content-rule-name" placeholder="e.g., Phishing Keywords" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="content-match-type" class="form-label" style="font-weight: 600; font-size: 0.85rem;">Match Type <span class="text-danger">*</span></label>
                        <select class="form-select" id="content-match-type" onchange="updateContentMatchInputLabel()">
                            <option value="keyword">Keyword(s)</option>
                            <option value="regex">Regex Pattern</option>
                        </select>
                        <small class="text-muted">Keyword matching is case-insensitive. Regex allows advanced pattern matching.</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="content-match-value" class="form-label" style="font-weight: 600; font-size: 0.85rem;" id="content-match-value-label">Keywords (comma-separated) <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="content-match-value" rows="3" placeholder="verify your account, click here, suspended" required></textarea>
                        <small class="text-muted" id="content-match-value-help">Enter keywords separated by commas. Matching is case-insensitive.</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="content-rule-type" class="form-label" style="font-weight: 600; font-size: 0.85rem;">Rule Type <span class="text-danger">*</span></label>
                        <select class="form-select" id="content-rule-type">
                            <option value="block">Block (Immediate Rejection)</option>
                            <option value="flag">Flag (Quarantine for Review)</option>
                        </select>
                        <small class="text-muted">Block immediately rejects the message. Flag sends it to the quarantine queue for manual review.</small>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="content-apply-normalisation" checked style="width: 2.5rem; height: 1.25rem;">
                            <label class="form-check-label" for="content-apply-normalisation" style="font-weight: 600; font-size: 0.85rem; margin-left: 0.5rem;">
                                Apply Normalisation
                            </label>
                        </div>
                        <small class="text-muted d-block mt-1">When enabled, message content is normalised (character substitution, case conversion) before matching.</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer" style="border-top: 1px solid #e9ecef; padding: 1rem 1.5rem;">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-sm text-white" style="background: #1e3a5f;" onclick="saveContentRule()">
                    <i class="fas fa-save me-1"></i> Save Rule
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="urlRuleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: #1e3a5f; border-bottom: none;">
                <h5 class="modal-title text-white" id="url-rule-modal-title">
                    <i class="fas fa-link me-2"></i>Add URL Rule
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding: 1.5rem;">
                <form id="url-rule-form">
                    <input type="hidden" id="url-rule-id" value="">
                    
                    <div class="mb-3">
                        <label for="url-match-type" class="form-label" style="font-weight: 600; font-size: 0.85rem;">Match Type <span class="text-danger">*</span></label>
                        <select class="form-select" id="url-match-type" onchange="updateUrlPatternLabel()">
                            <option value="exact">Exact Domain</option>
                            <option value="wildcard">Wildcard Pattern</option>
                            <option value="regex">Regex Pattern</option>
                        </select>
                        <small class="text-muted">Choose how the domain/URL pattern should be matched.</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="url-pattern" class="form-label" style="font-weight: 600; font-size: 0.85rem;" id="url-pattern-label">Domain <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="url-pattern" placeholder="example.com" required>
                        <small class="text-muted" id="url-pattern-help">Enter the exact domain to match (e.g., example.com)</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="url-rule-type" class="form-label" style="font-weight: 600; font-size: 0.85rem;">Rule Type <span class="text-danger">*</span></label>
                        <select class="form-select" id="url-rule-type">
                            <option value="block">Block (Immediate Rejection)</option>
                            <option value="flag">Flag (Quarantine for Review)</option>
                        </select>
                        <small class="text-muted">Block immediately rejects messages containing this URL. Flag sends them to quarantine.</small>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="url-apply-domain-age" checked style="width: 2.5rem; height: 1.25rem;">
                            <label class="form-check-label" for="url-apply-domain-age" style="font-weight: 600; font-size: 0.85rem; margin-left: 0.5rem;">
                                Apply Domain Age Check
                            </label>
                        </div>
                        <small class="text-muted d-block mt-1">When enabled, the global domain age rule will also apply to URLs matching this pattern.</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer" style="border-top: 1px solid #e9ecef; padding: 1rem 1.5rem;">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-sm text-white" style="background: #1e3a5f;" onclick="saveUrlRule()">
                    <i class="fas fa-save me-1"></i> Save Rule
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="domainAgeExceptionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: #1e3a5f; border-bottom: none;">
                <h5 class="modal-title text-white">
                    <i class="fas fa-user-shield me-2"></i>Add Domain Age Exception
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding: 1.5rem;">
                <form id="exception-form">
                    <div class="mb-3">
                        <label for="exception-account-id" class="form-label" style="font-weight: 600; font-size: 0.85rem;">Account ID <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="exception-account-id" placeholder="ACC-XXXXX" required>
                        <small class="text-muted">Enter the account ID to exempt from domain age checks.</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="exception-account-name" class="form-label" style="font-weight: 600; font-size: 0.85rem;">Account Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="exception-account-name" placeholder="Company Name" required>
                        <small class="text-muted">Enter the account/company name for reference.</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="exception-reason" class="form-label" style="font-weight: 600; font-size: 0.85rem;">Reason for Exception <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="exception-reason" rows="3" placeholder="Explain why this account needs an exception..." required></textarea>
                        <small class="text-muted">This will be recorded in the audit trail.</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer" style="border-top: 1px solid #e9ecef; padding: 1rem 1.5rem;">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-sm text-white" style="background: #1e3a5f;" onclick="saveException()">
                    <i class="fas fa-save me-1"></i> Add Exception
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="quarantineViewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 1100px;">
        <div class="modal-content" style="display: flex; flex-direction: column; max-height: 85vh;">
            <div class="modal-header py-2" style="background: #1e3a5f; border-bottom: none; flex-shrink: 0;">
                <h6 class="modal-title text-white mb-0">
                    <i class="fas fa-shield-alt me-2"></i>Quarantine Review: <span id="qrn-view-id-header"></span>
                </h6>
                <div class="d-flex align-items-center gap-2">
                    <span id="qrn-view-status-header"></span>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body" style="padding: 0.75rem; overflow-y: auto; flex: 1;">
                <div class="row g-2">
                    <div class="col-lg-6">
                        <div class="mb-2 p-2 rounded" style="background: #f8f9fa; border: 1px solid #e9ecef;">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span style="font-size: 0.7rem; font-weight: 600; color: #1e3a5f;"><i class="fas fa-envelope me-1"></i>MESSAGE</span>
                                <div class="d-flex align-items-center gap-1">
                                    <span class="badge bg-warning text-dark" style="font-size: 0.5rem;">PII GATED</span>
                                    <button type="button" class="btn btn-link p-0" style="font-size: 0.65rem; color: #6c757d;" onclick="copyQuarantineMessage()" title="Copy"><i class="fas fa-copy"></i></button>
                                </div>
                            </div>
                            <div class="d-flex gap-2 mb-1" style="font-size: 0.7rem;">
                                <span><strong>From:</strong> <code id="qrn-view-senderid" style="background: #e9ecef; padding: 0 0.2rem; font-size: 0.65rem;"></code></span>
                                <span><strong>To:</strong> <span id="qrn-view-recipient" style="font-family: monospace; font-size: 0.65rem;"></span></span>
                                <span id="qrn-view-hasurl"></span>
                            </div>
                            <div class="p-1 rounded" style="font-size: 0.75rem; border: 1px solid #e0e0e0; background: #f5f5f5; color: #212529; white-space: pre-wrap; word-wrap: break-word; max-height: 55px; overflow-y: auto;">
                                <span id="qrn-view-message"></span>
                            </div>
                            <div id="qrn-message-expand" style="display: none;">
                                <button class="btn btn-link btn-sm p-0" style="font-size: 0.65rem;" onclick="toggleMessageExpand()">Show more</button>
                            </div>
                        </div>
                        
                        <div class="mb-2 p-2 rounded" style="background: #fff5f5; border: 1px solid #f5c6cb;">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span style="font-size: 0.7rem; font-weight: 600; color: #dc3545;"><i class="fas fa-exclamation-triangle me-1"></i>TRIGGERED RULES</span>
                                <span id="qrn-rules-count" class="badge bg-danger" style="font-size: 0.55rem;">0</span>
                            </div>
                            <div id="qrn-view-triggered-rules" style="max-height: 150px; overflow-y: auto;"></div>
                        </div>
                        
                        <div class="accordion accordion-flush" id="qrn-left-accordions" style="font-size: 0.7rem;">
                            <div class="accordion-item" style="border: 1px solid #e9ecef; border-radius: 3px;">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed py-1 px-2" type="button" data-bs-toggle="collapse" data-bs-target="#qrn-normalisation-collapse" style="font-size: 0.7rem; font-weight: 600; background: #f8f9fa;">
                                        <i class="fas fa-magic me-1" style="color: #6b21a8;"></i> Normalisation Debug
                                    </button>
                                </h2>
                                <div id="qrn-normalisation-collapse" class="accordion-collapse collapse">
                                    <div class="accordion-body py-1 px-2" id="qrn-view-normalised" style="font-size: 0.7rem;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-6">
                        <div class="mb-2 p-2 rounded" style="background: #f8f9fa; border: 1px solid #e9ecef;">
                            <span style="font-size: 0.7rem; font-weight: 600; color: #1e3a5f;"><i class="fas fa-info-circle me-1"></i>DETAILS</span>
                            <table class="table table-sm table-borderless mb-0 mt-1" style="font-size: 0.7rem;">
                                <tr>
                                    <td style="width: 80px; padding: 0.1rem 0; color: #6c757d;">ID:</td>
                                    <td style="padding: 0.1rem 0;"><code id="qrn-view-id" style="font-size: 0.65rem;"></code></td>
                                    <td style="width: 80px; padding: 0.1rem 0; color: #6c757d;">Timestamp:</td>
                                    <td style="padding: 0.1rem 0;" id="qrn-view-timestamp"></td>
                                </tr>
                                <tr>
                                    <td style="padding: 0.1rem 0; color: #6c757d;">Account:</td>
                                    <td style="padding: 0.1rem 0;" id="qrn-view-account"></td>
                                    <td style="padding: 0.1rem 0; color: #6c757d;">Sub-Acct:</td>
                                    <td style="padding: 0.1rem 0;" id="qrn-view-subaccount"></td>
                                </tr>
                            </table>
                        </div>
                        
                        <div class="mb-2 p-2 rounded" style="background: #f8f9fa; border: 1px solid #e9ecef;">
                            <span style="font-size: 0.7rem; font-weight: 600; color: #1e3a5f;"><i class="fas fa-clipboard-check me-1"></i>STATUS</span>
                            <div class="d-flex align-items-center gap-3 mt-1" style="font-size: 0.7rem;">
                                <div><span class="text-muted">Status:</span> <span id="qrn-view-status"></span></div>
                                <div><span class="text-muted">Reviewer:</span> <span id="qrn-view-reviewer"></span></div>
                                <div><span class="text-muted">At:</span> <span id="qrn-view-decisionat"></span></div>
                            </div>
                        </div>
                        
                        <div class="accordion accordion-flush" id="qrn-right-accordions" style="font-size: 0.7rem;">
                            <div class="accordion-item mb-1" style="border: 1px solid #e9ecef; border-radius: 3px;">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed py-1 px-2" type="button" data-bs-toggle="collapse" data-bs-target="#qrn-notes-collapse" style="font-size: 0.7rem; font-weight: 600; background: #f8f9fa;">
                                        <i class="fas fa-sticky-note me-1" style="color: #1e3a5f;"></i> Internal Notes <span class="badge text-white ms-1" style="font-size: 0.5rem; background: #1e3a5f;">ADMIN</span>
                                    </button>
                                </h2>
                                <div id="qrn-notes-collapse" class="accordion-collapse collapse">
                                    <div class="accordion-body py-1 px-2">
                                        <div id="qrn-view-notes-list" style="max-height: 50px; overflow-y: auto; font-size: 0.7rem;"></div>
                                        <div class="mt-1" id="qrn-add-note-section">
                                            <div class="input-group input-group-sm">
                                                <input type="text" class="form-control form-control-sm" id="qrn-new-note" placeholder="Add note..." style="font-size: 0.7rem; padding: 0.15rem 0.4rem;">
                                                <button class="btn btn-outline-secondary btn-sm" type="button" onclick="addQuarantineNote()" style="font-size: 0.65rem; padding: 0.15rem 0.4rem;"><i class="fas fa-plus"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item mb-1" style="border: 1px solid #e9ecef; border-radius: 3px;">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed py-1 px-2" type="button" data-bs-toggle="collapse" data-bs-target="#qrn-raw-metadata-collapse" style="font-size: 0.7rem; font-weight: 600; background: #f8f9fa;">
                                        <i class="fas fa-code me-1" style="color: #6c757d;"></i> Raw JSON
                                    </button>
                                </h2>
                                <div id="qrn-raw-metadata-collapse" class="accordion-collapse collapse">
                                    <div class="accordion-body py-1 px-2">
                                        <pre id="qrn-view-raw-json" style="font-size: 0.6rem; background: #f8f9fa; padding: 0.25rem; border-radius: 3px; max-height: 80px; overflow: auto; margin: 0;"></pre>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item" style="border: 1px solid #e9ecef; border-radius: 3px;">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed py-1 px-2" type="button" data-bs-toggle="collapse" data-bs-target="#qrn-routing-collapse" style="font-size: 0.7rem; font-weight: 600; background: #f8f9fa;">
                                        <i class="fas fa-route me-1" style="color: #6c757d;"></i> Routing Info
                                    </button>
                                </h2>
                                <div id="qrn-routing-collapse" class="accordion-collapse collapse">
                                    <div class="accordion-body py-1 px-2" id="qrn-view-routing" style="font-size: 0.7rem;">
                                        <span class="text-muted">No routing info (blocked before routing)</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2 px-3" style="border-top: 1px solid #e9ecef; background: #f8f9fa; flex-shrink: 0;">
                <div id="qrn-view-actions" class="d-flex gap-2 align-items-center">
                </div>
                <div class="ms-auto d-flex gap-2 align-items-center">
                    <div class="form-check form-switch mb-0" id="qrn-notify-customer-section" style="display: none;">
                        <input class="form-check-input" type="checkbox" id="qrn-notify-customer">
                        <label class="form-check-label" for="qrn-notify-customer" style="font-size: 0.75rem;">Notify Customer</label>
                    </div>
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal" style="font-size: 0.8rem;">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Feature Flags Modal -->
<div class="modal fade" id="featureFlagsModal" tabindex="-1" aria-labelledby="featureFlagsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 100%); color: white;">
                <h5 class="modal-title" id="featureFlagsModalLabel">
                    <i class="fas fa-toggle-on me-2"></i> Feature Flags Management
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info mb-3" style="font-size: 0.85rem;">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Admin Only:</strong> Feature flags control which enforcement engines are active. Changes take effect immediately and are audited.
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header bg-light" style="font-size: 0.85rem; font-weight: 600;">
                                <i class="fas fa-cogs me-2"></i> Cache Statistics
                            </div>
                            <div class="card-body p-3" id="cacheStatsPanel">
                                <div class="row g-2" style="font-size: 0.8rem;">
                                    <div class="col-6"><strong>Version:</strong> <span id="cache-version">-</span></div>
                                    <div class="col-6"><strong>Rules:</strong> <span id="cache-total-rules">-</span></div>
                                    <div class="col-6"><strong>Tenants:</strong> <span id="cache-tenant-count">-</span></div>
                                    <div class="col-6"><strong>Last Load:</strong> <span id="cache-last-loaded">-</span></div>
                                </div>
                                <button class="btn btn-outline-primary btn-sm mt-2 w-100" onclick="hotReloadRules()">
                                    <i class="fas fa-sync-alt me-1"></i> Hot Reload Rules
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header bg-light" style="font-size: 0.85rem; font-weight: 600;">
                                <i class="fas fa-shield-alt me-2"></i> Tenant Isolation
                            </div>
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center justify-content-between mb-2" style="font-size: 0.8rem;">
                                    <span><i class="fas fa-lock text-success me-1"></i> Isolation Enforced</span>
                                    <span class="badge bg-success">Active</span>
                                </div>
                                <p class="text-muted mb-0" style="font-size: 0.75rem;">
                                    Rules are tenant-scoped. Cross-tenant reads are blocked. Global rules apply to all tenants.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <h6 class="mb-3" style="font-size: 0.9rem; font-weight: 600; color: #1e3a5f;">
                    <i class="fas fa-flag me-2"></i> Engine Feature Flags
                </h6>
                
                <div class="table-responsive">
                    <table class="table table-sm" style="font-size: 0.85rem;">
                        <thead style="background: #f8f9fa;">
                            <tr>
                                <th style="padding: 0.5rem;">Flag</th>
                                <th style="padding: 0.5rem;">Description</th>
                                <th style="padding: 0.5rem; text-align: center;">Status</th>
                                <th style="padding: 0.5rem; text-align: center;">Action</th>
                            </tr>
                        </thead>
                        <tbody id="featureFlagsTableBody">
                            <!-- Populated by JavaScript -->
                        </tbody>
                    </table>
                </div>
                
                <div class="alert alert-warning mt-3 mb-0" style="font-size: 0.8rem;">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Caution:</strong> Disabling engines will skip their checks. Messages that would normally be blocked may be allowed through.
                </div>
            </div>
            <div class="modal-footer">
                <span class="text-muted me-auto" style="font-size: 0.75rem;">
                    <i class="fas fa-history me-1"></i> All changes are audit logged
                </span>
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Admin Access Enforcement Script -->
<script>
(function() {
    var AdminAccessControl = {
        currentAdmin: {
            id: 'admin-001',
            email: 'admin@quicksms.co.uk',
            role: 'super_admin',
            isAdmin: true
        },
        
        accessRules: {
            internalRoutingOnly: { enforced: true, description: 'Admin routes are internal only' },
            noSharedRoutes: { enforced: true, description: 'No shared routes with customer portal' },
            noDeepLinksFromCustomer: { enforced: true, description: 'Customer portal cannot deep-link to admin' },
            mandatoryMfa: { enforced: true, description: 'MFA required for admin access' },
            adminUsersOnly: { enforced: true, description: 'Only admin users can access' }
        },
        
        validateAccess: function() {
            if (!this.currentAdmin || !this.currentAdmin.isAdmin) {
                console.error('[AdminAccessControl] Access denied - not an admin user');
                window.location.href = '/login';
                return false;
            }
            
            var referrer = document.referrer;
            if (referrer && referrer.includes('/customer/')) {
                console.error('[AdminAccessControl] Access denied - referrer from customer portal');
                this.logAccessViolation('CUSTOMER_PORTAL_REFERRER', referrer);
                return false;
            }
            
            console.log('[AdminControlPlane] Initialized for:', this.currentAdmin.email);
            console.log('[AdminControlPlane] Access Rules:', this.accessRules);
            console.log('[AdminControlPlane] Global Rules:', {
                singleSourceOfTruth: { enforced: true },
                filtering: { enforced: true },
                audit: { enforced: true },
                piiProtection: { enforced: true }
            });
            
            this.logSessionStart();
            return true;
        },
        
        logSessionStart: function() {
            var logEntry = {
                timestamp: new Date().toISOString(),
                eventType: 'ADMIN_SESSION_START',
                adminEmail: this.currentAdmin.email,
                adminRole: this.currentAdmin.role,
                path: window.location.pathname,
                referrer: document.referrer || 'direct',
                userAgent: navigator.userAgent,
                accessRulesEnforced: Object.keys(this.accessRules)
            };
            console.log('[AdminControlPlane][ACCESS]', JSON.stringify(logEntry));
        },
        
        logAccessViolation: function(type, details) {
            console.error('[AdminControlPlane][VIOLATION]', {
                timestamp: new Date().toISOString(),
                type: type,
                details: details,
                path: window.location.pathname
            });
        }
    };
    
    AdminAccessControl.validateAccess();
    window.AdminAccessControl = AdminAccessControl;
})();

function loadFeatureFlagsModal() {
    if (typeof MessageEnforcementService === 'undefined') return;
    
    var flags = MessageEnforcementService.getFeatureFlags();
    var cacheStats = MessageEnforcementService.getCacheStats();
    
    document.getElementById('cache-version').textContent = cacheStats.version || '-';
    document.getElementById('cache-total-rules').textContent = cacheStats.totalRules || '-';
    document.getElementById('cache-tenant-count').textContent = cacheStats.tenantCount || '-';
    document.getElementById('cache-last-loaded').textContent = cacheStats.lastLoaded ? 
        new Date(cacheStats.lastLoaded).toLocaleTimeString() : '-';
    
    var flagDescriptions = {
        normalisation_enabled: { name: 'Normalisation Engine', desc: 'Apply text normalisation before matching' },
        senderid_controls_enabled: { name: 'SenderID Controls', desc: 'Block/flag based on sender ID patterns' },
        content_controls_enabled: { name: 'Content Controls', desc: 'Block/flag based on message content' },
        url_controls_enabled: { name: 'URL Controls', desc: 'Block/flag based on URLs in messages' },
        quarantine_enabled: { name: 'Quarantine', desc: 'Send flagged messages to quarantine queue' },
        anti_spam_enabled: { name: 'Anti-Spam', desc: 'Prevent duplicate messages to same recipient' },
        domain_age_check_enabled: { name: 'Domain Age Check', desc: 'Flag URLs with newly registered domains' }
    };
    
    var tbody = document.getElementById('featureFlagsTableBody');
    tbody.innerHTML = '';
    
    Object.keys(flags).forEach(function(flagKey) {
        var flagInfo = flagDescriptions[flagKey] || { name: flagKey, desc: '' };
        var isEnabled = flags[flagKey];
        
        var row = document.createElement('tr');
        row.innerHTML = 
            '<td><code style="font-size: 0.75rem; background: #f1f3f5; padding: 0.2rem 0.4rem; border-radius: 3px;">' + flagKey + '</code><br><small class="text-muted">' + flagInfo.name + '</small></td>' +
            '<td style="color: #666;">' + flagInfo.desc + '</td>' +
            '<td style="text-align: center;">' +
                '<span class="badge ' + (isEnabled ? 'bg-success' : 'bg-secondary') + '">' + (isEnabled ? 'Enabled' : 'Disabled') + '</span>' +
            '</td>' +
            '<td style="text-align: center;">' +
                '<div class="form-check form-switch d-inline-block">' +
                    '<input class="form-check-input feature-flag-toggle" type="checkbox" data-flag="' + flagKey + '" ' + (isEnabled ? 'checked' : '') + ' onchange="toggleFeatureFlag(\'' + flagKey + '\', this.checked)">' +
                '</div>' +
            '</td>';
        tbody.appendChild(row);
    });
}

function toggleFeatureFlag(flagKey, enabled) {
    if (typeof MessageEnforcementService === 'undefined') return;
    
    var result = MessageEnforcementService.setFeatureFlag(flagKey, enabled, {
        isAdmin: true,
        adminId: 'admin-001',
        adminEmail: 'admin@quicksms.co.uk'
    });
    
    if (result.success) {
        logAuditEvent('FEATURE_FLAG_TOGGLED', {
            flag: flagKey,
            newValue: enabled,
            previousValue: !enabled
        });
        
        loadFeatureFlagsModal();
        
        var toast = document.createElement('div');
        toast.className = 'alert alert-success alert-dismissible fade show position-fixed';
        toast.style.cssText = 'top: 80px; right: 20px; z-index: 9999; max-width: 350px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);';
        toast.innerHTML = '<i class="fas fa-check-circle me-2"></i><strong>' + flagKey + '</strong> ' + (enabled ? 'enabled' : 'disabled') + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        document.body.appendChild(toast);
        setTimeout(function() { toast.remove(); }, 3000);
    } else {
        console.error('[FeatureFlags] Toggle failed:', result.error);
    }
}

function hotReloadRules() {
    if (typeof MessageEnforcementService === 'undefined') return;
    
    var result = MessageEnforcementService.hotReloadRules();
    
    if (result.success) {
        logAuditEvent('RULES_HOT_RELOADED', { newVersion: result.version });
        loadFeatureFlagsModal();
        
        var toast = document.createElement('div');
        toast.className = 'alert alert-info alert-dismissible fade show position-fixed';
        toast.style.cssText = 'top: 80px; right: 20px; z-index: 9999; max-width: 350px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);';
        toast.innerHTML = '<i class="fas fa-sync-alt me-2"></i>Rules reloaded (v' + result.version + ')<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        document.body.appendChild(toast);
        setTimeout(function() { toast.remove(); }, 3000);
    }
}

document.getElementById('featureFlagsModal').addEventListener('show.bs.modal', function() {
    loadFeatureFlagsModal();
});

console.log('[SecurityComplianceControls] Initialized');
</script>

<div class="modal fade" id="bulkActionConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" id="bulk-confirm-header" style="border-bottom: 1px solid #e9ecef;">
                <h5 class="modal-title" id="bulk-confirm-title" style="font-weight: 600;">
                    <i id="bulk-confirm-icon" class="me-2"></i><span id="bulk-confirm-title-text">Confirm Action</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="bulk-confirm-message" style="font-size: 0.95rem; margin-bottom: 1rem;">Are you sure you want to perform this action?</p>
                <div class="alert alert-warning py-2" style="font-size: 0.85rem;">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    <strong id="bulk-confirm-count">0</strong> message(s) will be affected. This action will be logged.
                </div>
            </div>
            <div class="modal-footer" style="border-top: 1px solid #e9ecef;">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-sm" id="bulk-confirm-btn" onclick="executeBulkAction()">Confirm</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@include('shared.services.message-enforcement-service')
<script>
var currentAdmin = {
    id: 'admin-001',
    email: 'admin@quicksms.co.uk',
    role: 'super_admin'
};

function showEnforcementErrorBanner(result, isAdmin) {
    if (!result || result.decision === 'ALLOW') return;
    
    var container = document.getElementById('enforcement-error-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'enforcement-error-container';
        container.style.cssText = 'position: fixed; top: 80px; right: 20px; z-index: 9999; max-width: 400px;';
        document.body.appendChild(container);
    }
    
    var alertClass = result.decision === 'BLOCK' ? 'alert-danger' : 'alert-warning';
    var iconClass = result.decision === 'BLOCK' ? 'fa-ban' : 'fa-exclamation-triangle';
    
    var explanation = result.explainability || {};
    var adminDetail = explanation.adminDetail || {};
    var customerSummary = explanation.customerSummary || {};
    
    var bannerHtml = '';
    if (isAdmin) {
        bannerHtml = '<div class="alert ' + alertClass + ' alert-dismissible fade show" role="alert" style="border-left: 4px solid; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">' +
            '<div class="d-flex align-items-start">' +
                '<i class="fas ' + iconClass + ' me-3 mt-1" style="font-size: 1.2rem;"></i>' +
                '<div class="flex-grow-1">' +
                    '<h6 class="alert-heading mb-1" style="font-size: 0.95rem;">' + (adminDetail.summary || 'Message ' + result.decision.toLowerCase()) + '</h6>' +
                    '<p class="mb-2" style="font-size: 0.85rem;">' + (adminDetail.fullReason || result.reason) + '</p>' +
                    '<div class="d-flex flex-wrap gap-2" style="font-size: 0.75rem;">' +
                        '<span class="badge bg-light text-dark"><i class="fas fa-cog me-1"></i>' + (adminDetail.engine || 'Policy') + '</span>' +
                        (adminDetail.ruleId ? '<span class="badge bg-light text-dark"><i class="fas fa-hashtag me-1"></i>' + adminDetail.ruleId + '</span>' : '') +
                        (adminDetail.ruleName ? '<span class="badge bg-light text-dark"><i class="fas fa-tag me-1"></i>' + adminDetail.ruleName + '</span>' : '') +
                        (adminDetail.matchedToken ? '<span class="badge bg-light text-dark" title="Matched: ' + adminDetail.matchedToken + '"><i class="fas fa-search me-1"></i>Token matched</span>' : '') +
                    '</div>' +
                '</div>' +
            '</div>' +
            '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
        '</div>';
    } else {
        bannerHtml = '<div class="alert ' + alertClass + ' alert-dismissible fade show" role="alert" style="border-left: 4px solid; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">' +
            '<div class="d-flex align-items-start">' +
                '<i class="fas ' + iconClass + ' me-3 mt-1" style="font-size: 1.2rem;"></i>' +
                '<div class="flex-grow-1">' +
                    '<h6 class="alert-heading mb-1" style="font-size: 0.95rem;">' + (customerSummary.headline || 'Message could not be sent') + '</h6>' +
                    '<p class="mb-2" style="font-size: 0.85rem;">' + (customerSummary.reason || 'Your message was flagged by our security policy.') + '</p>' +
                    '<p class="mb-0" style="font-size: 0.8rem; opacity: 0.9;">' + (customerSummary.actionRequired || '') + '</p>' +
                    (customerSummary.supportCode ? '<small class="text-muted d-block mt-2">Reference: ' + customerSummary.supportCode + '</small>' : '') +
                '</div>' +
            '</div>' +
            '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
        '</div>';
    }
    
    container.innerHTML = bannerHtml;
    
    setTimeout(function() {
        var alert = container.querySelector('.alert');
        if (alert) {
            alert.classList.remove('show');
            setTimeout(function() { container.innerHTML = ''; }, 150);
        }
    }, 10000);
}

window.showEnforcementErrorBanner = showEnforcementErrorBanner;

var auditConfig = {
    enableCustomerAudit: true,
    customerAuditEvents: [
        'MESSAGE_BLOCKED_BY_POLICY',
        'MESSAGE_QUARANTINED_BY_POLICY',
        'MESSAGE_RELEASED_FROM_QUARANTINE'
    ],
    internalOnlyEvents: [
        'SENDERID_RULE_CREATED', 'SENDERID_RULE_UPDATED', 'SENDERID_RULE_DELETED', 'SENDERID_RULE_STATUS_CHANGED',
        'CONTENT_RULE_CREATED', 'CONTENT_RULE_UPDATED', 'CONTENT_RULE_DELETED', 'CONTENT_RULE_STATUS_CHANGED',
        'URL_RULE_CREATED', 'URL_RULE_UPDATED', 'URL_RULE_DELETED', 'URL_RULE_STATUS_CHANGED',
        'NORMALISATION_RULE_CHANGED',
        'DOMAIN_AGE_SETTINGS_UPDATED', 'DOMAIN_AGE_EXCEPTION_ADDED', 'DOMAIN_AGE_EXCEPTION_REMOVED',
        'QUARANTINE_NOTE_ADDED', 'QUARANTINE_EXCEPTION_STARTED', 'QUARANTINE_RULE_CREATE_STARTED',
        'ANTISPAM_REPEAT_CONTENT_TOGGLED', 'ANTISPAM_WINDOW_UPDATED',
        'OVERRIDE_APPLIED'
    ],
    retentionYears: 7
};

function getClientIP() {
    return '192.168.1.' + Math.floor(Math.random() * 255);
}

function hashForAudit(value) {
    if (!value) return null;
    var hash = 0;
    for (var i = 0; i < value.length; i++) {
        var char = value.charCodeAt(i);
        hash = ((hash << 5) - hash) + char;
        hash = hash & hash;
    }
    return 'H' + Math.abs(hash).toString(16).toUpperCase().padStart(8, '0');
}

function logAuditEvent(eventType, details) {
    var sanitizedDetails = sanitizeAuditDetails(details);
    if (typeof NormalisationRulesConfig !== 'undefined') {
        sanitizedDetails = NormalisationRulesConfig.sanitizePII(sanitizedDetails);
    }
    
    var auditEntry = {
        eventId: 'AUD-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9),
        eventType: eventType,
        timestamp: new Date().toISOString(),
        timestampUtc: new Date().toUTCString(),
        actor: {
            adminId: currentAdmin.id,
            adminEmail: '[REDACTED]',
            adminRole: currentAdmin.role
        },
        sourceIp: '[REDACTED]',
        sourceIpHash: hashForAudit(getClientIP()),
        affectedEntities: details.affectedEntities || [],
        entityId: details.ruleId || details.messageId || details.accountId || null,
        entityType: details.entityType || inferEntityType(eventType),
        action: eventType,
        before: details.before || details.beforeStatus || null,
        after: details.after || details.afterStatus || null,
        details: sanitizedDetails,
        module: 'security_compliance_controls',
        retentionExpiry: new Date(Date.now() + (auditConfig.retentionYears * 365 * 24 * 60 * 60 * 1000)).toISOString()
    };
    
    console.log('[SecurityComplianceAudit][INTERNAL]', JSON.stringify(auditEntry));
    
    if (auditConfig.enableCustomerAudit && shouldLogToCustomerAudit(eventType, details)) {
        var customerAuditEntry = buildCustomerAuditEntry(eventType, details, auditEntry);
        console.log('[SecurityComplianceAudit][CUSTOMER]', JSON.stringify(customerAuditEntry));
    }
    
    return auditEntry;
}

function inferEntityType(eventType) {
    if (eventType.indexOf('SENDERID') !== -1) return 'senderid_rule';
    if (eventType.indexOf('CONTENT') !== -1) return 'content_rule';
    if (eventType.indexOf('URL') !== -1) return 'url_rule';
    if (eventType.indexOf('DOMAIN_AGE') !== -1) return 'domain_age_setting';
    if (eventType.indexOf('QUARANTINE') !== -1) return 'quarantine_message';
    if (eventType.indexOf('ANTISPAM') !== -1) return 'antispam_setting';
    if (eventType.indexOf('NORMALISATION') !== -1) return 'normalisation_rule';
    return 'unknown';
}

function sanitizeAuditDetails(details) {
    var sanitized = JSON.parse(JSON.stringify(details));
    var sensitiveFields = ['password', 'token', 'secret', 'apiKey', 'recipientFull'];
    sensitiveFields.forEach(function(field) {
        if (sanitized[field]) {
            sanitized[field] = '[REDACTED]';
        }
    });
    return sanitized;
}

function shouldLogToCustomerAudit(eventType, details) {
    if (auditConfig.customerAuditEvents.indexOf(eventType) !== -1) {
        return true;
    }
    if (eventType === 'QUARANTINE_MESSAGE_RELEASED' || eventType === 'QUARANTINE_MESSAGE_BLOCKED') {
        return true;
    }
    return false;
}

function buildCustomerAuditEntry(eventType, details, internalEntry) {
    var customerEvent = {
        eventId: internalEntry.eventId,
        timestamp: internalEntry.timestamp,
        accountId: details.accountId || null,
        accountName: details.accountName || null,
        eventType: mapToCustomerEventType(eventType),
        summary: buildCustomerSummary(eventType, details),
        affectedMessageId: details.messageId || null
    };
    return customerEvent;
}

function mapToCustomerEventType(internalEventType) {
    var mapping = {
        'QUARANTINE_MESSAGE_RELEASED': 'MESSAGE_RELEASED_FROM_QUARANTINE',
        'QUARANTINE_MESSAGE_BLOCKED': 'MESSAGE_BLOCKED_BY_POLICY',
        'MESSAGE_QUARANTINED': 'MESSAGE_QUARANTINED_BY_POLICY'
    };
    return mapping[internalEventType] || 'POLICY_ACTION';
}

function buildCustomerSummary(eventType, details) {
    if (eventType === 'QUARANTINE_MESSAGE_RELEASED') {
        return 'A message to ' + (details.recipient || 'recipient') + ' was reviewed and released for delivery.';
    }
    if (eventType === 'QUARANTINE_MESSAGE_BLOCKED') {
        return 'A message to ' + (details.recipient || 'recipient') + ' was reviewed and blocked due to policy violation.';
    }
    return 'A policy action was applied to your message.';
}

var SecurityComplianceControlsService = (function() {
    var mockData = {
        senderIdRules: [],
        contentRules: [],
        urlRules: [],
        normalisationRules: [],
        quarantinedMessages: []
    };

    function formatDateTime(date) {
        var d = date instanceof Date ? date : new Date(date);
        var day = String(d.getDate()).padStart(2, '0');
        var month = String(d.getMonth() + 1).padStart(2, '0');
        var year = d.getFullYear();
        var hours = String(d.getHours()).padStart(2, '0');
        var minutes = String(d.getMinutes()).padStart(2, '0');
        return day + '-' + month + '-' + year + ' ' + hours + ':' + minutes;
    }
    
    function initialize() {
        loadMockData();
        renderAllTabs();
        setupEventListeners();
        console.log('[SecurityComplianceControls] Initialized');
    }

    function loadMockData() {
        mockData.senderIdRules = [
            { id: 'SID-001', name: 'Block HSBC Impersonation', baseSenderId: 'HSBC', ruleType: 'block', category: 'bank_impersonation', applyNormalisation: true, status: 'active', createdBy: 'admin@quicksms.co.uk', createdAt: '15-01-2026 09:30', updatedAt: '15-01-2026 09:30' },
            { id: 'SID-002', name: 'Block Barclays Impersonation', baseSenderId: 'BARCLAYS', ruleType: 'block', category: 'bank_impersonation', applyNormalisation: true, status: 'active', createdBy: 'admin@quicksms.co.uk', createdAt: '15-01-2026 10:15', updatedAt: '20-01-2026 14:22' },
            { id: 'SID-003', name: 'Flag HMRC Messages', baseSenderId: 'HMRC', ruleType: 'flag', category: 'government', applyNormalisation: true, status: 'active', createdBy: 'compliance@quicksms.co.uk', createdAt: '12-01-2026 11:00', updatedAt: '12-01-2026 11:00' },
            { id: 'SID-004', name: 'Block Lottery Sender', baseSenderId: 'LOTTERY', ruleType: 'block', category: 'lottery_prize', applyNormalisation: true, status: 'active', createdBy: 'admin@quicksms.co.uk', createdAt: '10-01-2026 08:45', updatedAt: '25-01-2026 16:30' },
            { id: 'SID-005', name: 'Flag Premium Rate', baseSenderId: 'PREMIUM', ruleType: 'flag', category: 'premium_rate', applyNormalisation: false, status: 'disabled', createdBy: 'admin@quicksms.co.uk', createdAt: '05-01-2026 14:00', updatedAt: '28-01-2026 09:15' }
        ];

        mockData.contentRules = [
            { id: 'CNT-001', name: 'Phishing Keywords', matchType: 'keyword', matchValue: 'verify your account, click here immediately, suspended account, urgent action', ruleType: 'block', applyNormalisation: true, status: 'active', createdBy: 'admin@quicksms.co.uk', createdAt: '15-01-2026 09:30', updatedAt: '15-01-2026 09:30' },
            { id: 'CNT-002', name: 'Adult Content Filter', matchType: 'regex', matchValue: '(18\\+|xxx|adult\\s?content)', ruleType: 'flag', applyNormalisation: true, status: 'active', createdBy: 'admin@quicksms.co.uk', createdAt: '12-01-2026 14:00', updatedAt: '20-01-2026 11:45' },
            { id: 'CNT-003', name: 'Gambling Promotion', matchType: 'keyword', matchValue: 'bet now, free spins, casino bonus, jackpot winner', ruleType: 'flag', applyNormalisation: true, status: 'active', createdBy: 'compliance@quicksms.co.uk', createdAt: '10-01-2026 08:45', updatedAt: '25-01-2026 16:30' },
            { id: 'CNT-004', name: 'Cryptocurrency Scam', matchType: 'regex', matchValue: '(bitcoin|crypto|eth)\\s*(giveaway|airdrop|double)', ruleType: 'block', applyNormalisation: true, status: 'active', createdBy: 'admin@quicksms.co.uk', createdAt: '08-01-2026 10:20', updatedAt: '08-01-2026 10:20' },
            { id: 'CNT-005', name: 'Premium Rate Numbers', matchType: 'regex', matchValue: '(call|text|dial)\\s*(09\\d{8,}|118\\d+)', ruleType: 'flag', applyNormalisation: false, status: 'disabled', createdBy: 'admin@quicksms.co.uk', createdAt: '05-01-2026 14:00', updatedAt: '28-01-2026 09:15' }
        ];

        mockData.urlRules = [
            { id: 'URL-001', pattern: 'bit.ly', matchType: 'exact', ruleType: 'flag', applyDomainAge: true, status: 'active', createdBy: 'admin@quicksms.co.uk', createdAt: '15-01-2026 09:30', updatedAt: '15-01-2026 09:30' },
            { id: 'URL-002', pattern: 'malicious-site.com', matchType: 'exact', ruleType: 'block', applyDomainAge: true, status: 'active', createdBy: 'admin@quicksms.co.uk', createdAt: '10-01-2026 14:00', updatedAt: '20-01-2026 11:45' },
            { id: 'URL-003', pattern: '*.tinyurl.com', matchType: 'wildcard', ruleType: 'flag', applyDomainAge: false, status: 'active', createdBy: 'compliance@quicksms.co.uk', createdAt: '08-01-2026 10:20', updatedAt: '25-01-2026 16:30' },
            { id: 'URL-004', pattern: 'phish\\d+\\.com', matchType: 'regex', ruleType: 'block', applyDomainAge: true, status: 'active', createdBy: 'admin@quicksms.co.uk', createdAt: '05-01-2026 08:45', updatedAt: '05-01-2026 08:45' },
            { id: 'URL-005', pattern: 'suspicious-domain.net', matchType: 'exact', ruleType: 'block', applyDomainAge: true, status: 'disabled', createdBy: 'admin@quicksms.co.uk', createdAt: '01-01-2026 14:00', updatedAt: '28-01-2026 09:15' }
        ];
        
        mockData.domainAgeSettings = {
            enabled: false,
            minAgeHours: 72,
            action: 'block'
        };
        
        mockData.domainAgeExceptions = [
            { id: 'EXC-001', accountId: 'ACC-10045', accountName: 'TechStart Ltd', reason: 'Approved marketing partner - verified shortlinks', addedBy: 'admin@quicksms.co.uk', addedAt: '15-01-2026 10:30' },
            { id: 'EXC-002', accountId: 'ACC-10089', accountName: 'HealthFirst UK', reason: 'Enterprise customer - internal domain rotation', addedBy: 'compliance@quicksms.co.uk', addedAt: '20-01-2026 14:15' }
        ];

        mockData.baseCharacterLibrary = (function() {
            var library = [];
            
            var uppercaseEquivalents = {
                'A': { equivalents: ['a', '4', 'À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Α', 'А', 'Ά', '@'], scope: ['senderid', 'content'], notes: 'Safe defaults: accented variants, Greek Alpha, Cyrillic A' },
                'B': { equivalents: ['Β', 'В', 'ϐ', '8'], scope: ['senderid', 'content'], notes: 'Greek Beta, Cyrillic Ve' },
                'C': { equivalents: ['С', 'Ϲ', '('], scope: ['senderid', 'content'], notes: 'Cyrillic Es' },
                'D': { equivalents: [], scope: ['senderid'], notes: '' },
                'E': { equivalents: ['e', '3', 'È', 'É', 'Ê', 'Ë', 'Ε', 'Е', 'Έ'], scope: ['senderid', 'content'], notes: 'Safe defaults: accented variants, Greek Epsilon, Cyrillic Ie' },
                'F': { equivalents: [], scope: ['senderid'], notes: '' },
                'G': { equivalents: ['g', '9'], scope: ['senderid', 'content'], notes: 'Safe defaults: lowercase, digit substitution' },
                'H': { equivalents: ['Η', 'Н', 'Ή'], scope: ['senderid', 'content'], notes: 'Greek Eta, Cyrillic En' },
                'I': { equivalents: ['i', '1', 'l', 'Ì', 'Í', 'Î', 'Ï', 'Ι', 'І', 'Ί', '|'], scope: ['senderid', 'content'], notes: 'Safe defaults: accented variants, Greek Iota, Cyrillic I' },
                'J': { equivalents: ['Ј'], scope: ['senderid'], notes: 'Cyrillic Je' },
                'K': { equivalents: ['Κ', 'К'], scope: ['senderid', 'content'], notes: 'Greek Kappa, Cyrillic Ka' },
                'L': { equivalents: ['1', '|'], scope: ['senderid'], notes: 'Common substitution' },
                'M': { equivalents: ['Μ', 'М'], scope: ['senderid', 'content'], notes: 'Greek Mu, Cyrillic Em' },
                'N': { equivalents: ['Ν', 'Ň'], scope: ['senderid', 'content'], notes: 'Greek Nu' },
                'O': { equivalents: ['o', '0', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ο', 'О', 'Ό'], scope: ['senderid', 'content'], notes: 'Safe defaults: accented variants, Greek Omicron, Cyrillic O, zero' },
                'P': { equivalents: ['Ρ', 'Р'], scope: ['senderid', 'content'], notes: 'Greek Rho, Cyrillic Er' },
                'Q': { equivalents: [], scope: ['senderid'], notes: '' },
                'R': { equivalents: [], scope: ['senderid'], notes: '' },
                'S': { equivalents: ['s', '5', '$', 'Ѕ'], scope: ['senderid', 'content'], notes: 'Safe defaults: lowercase, digit, dollar sign' },
                'T': { equivalents: ['t', '7', 'Τ', 'Т'], scope: ['senderid', 'content'], notes: 'Safe defaults: lowercase, digit, Greek Tau, Cyrillic Te' },
                'U': { equivalents: [], scope: ['senderid'], notes: '' },
                'V': { equivalents: [], scope: ['senderid'], notes: '' },
                'W': { equivalents: [], scope: ['senderid'], notes: '' },
                'X': { equivalents: ['Χ', 'Х'], scope: ['senderid', 'content'], notes: 'Greek Chi, Cyrillic Ha' },
                'Y': { equivalents: ['Υ', 'У', 'Ύ'], scope: ['senderid', 'content'], notes: 'Greek Upsilon, Cyrillic U' },
                'Z': { equivalents: ['Ζ', '2'], scope: ['senderid', 'content'], notes: 'Greek Zeta' }
            };
            
            var lowercaseEquivalents = {
                'a': { equivalents: ['A', '4', 'à', 'á', 'â', 'ã', 'ä', 'å', 'α', 'а', '@'], scope: ['senderid', 'content'], notes: 'Safe defaults: accented variants, Greek alpha, Cyrillic a' },
                'b': { equivalents: ['β', 'ь', 'в'], scope: ['senderid', 'content'], notes: 'Greek beta, Cyrillic soft sign' },
                'c': { equivalents: ['с', 'ϲ'], scope: ['senderid', 'content'], notes: 'Cyrillic es' },
                'd': { equivalents: [], scope: ['senderid'], notes: '' },
                'e': { equivalents: ['E', '3', 'è', 'é', 'ê', 'ë', 'ε', 'е', 'ё'], scope: ['senderid', 'content'], notes: 'Safe defaults: accented variants, Greek epsilon, Cyrillic ie' },
                'f': { equivalents: [], scope: ['senderid'], notes: '' },
                'g': { equivalents: ['G', '9', 'ɡ'], scope: ['senderid', 'content'], notes: 'Safe defaults: uppercase, digit substitution' },
                'h': { equivalents: ['һ'], scope: ['senderid'], notes: 'Cyrillic shha' },
                'i': { equivalents: ['I', '1', 'l', 'ì', 'í', 'î', 'ï', 'ι', 'і', 'ί', '|'], scope: ['senderid', 'content'], notes: 'Safe defaults: accented variants, Greek iota, Cyrillic i' },
                'j': { equivalents: ['ј'], scope: ['senderid'], notes: 'Cyrillic je' },
                'k': { equivalents: ['κ', 'к'], scope: ['senderid', 'content'], notes: 'Greek kappa, Cyrillic ka' },
                'l': { equivalents: ['1', 'I', '|', 'ӏ'], scope: ['senderid', 'content'], notes: 'Common substitution, Cyrillic palochka' },
                'm': { equivalents: ['м'], scope: ['senderid'], notes: 'Cyrillic em' },
                'n': { equivalents: ['ν', 'п'], scope: ['senderid', 'content'], notes: 'Greek nu' },
                'o': { equivalents: ['O', '0', 'ò', 'ó', 'ô', 'õ', 'ö', 'ο', 'о', 'ό'], scope: ['senderid', 'content'], notes: 'Safe defaults: accented variants, Greek omicron, Cyrillic o, zero' },
                'p': { equivalents: ['ρ', 'р'], scope: ['senderid', 'content'], notes: 'Greek rho, Cyrillic er' },
                'q': { equivalents: [], scope: ['senderid'], notes: '' },
                'r': { equivalents: ['г'], scope: ['senderid'], notes: 'Cyrillic ghe (visual similarity in some fonts)' },
                's': { equivalents: ['S', '5', '$', 'ѕ'], scope: ['senderid', 'content'], notes: 'Safe defaults: uppercase, digit, dollar sign' },
                't': { equivalents: ['T', '7', 'τ'], scope: ['senderid', 'content'], notes: 'Safe defaults: uppercase, digit, Greek tau' },
                'u': { equivalents: ['υ', 'ս'], scope: ['senderid'], notes: 'Greek upsilon, Armenian u' },
                'v': { equivalents: ['ν'], scope: ['senderid'], notes: 'Greek nu (visual similarity)' },
                'w': { equivalents: [], scope: ['senderid'], notes: '' },
                'x': { equivalents: ['χ', 'х'], scope: ['senderid', 'content'], notes: 'Greek chi, Cyrillic ha' },
                'y': { equivalents: ['у', 'γ'], scope: ['senderid', 'content'], notes: 'Cyrillic u, Greek gamma' },
                'z': { equivalents: ['ζ'], scope: ['senderid', 'content'], notes: 'Greek zeta' }
            };
            
            var digitEquivalents = {
                '0': { equivalents: ['O', 'o', 'Ο', 'ο', 'О', 'о'], scope: ['senderid', 'content', 'url'], notes: 'Latin O, Greek Omicron, Cyrillic O' },
                '1': { equivalents: ['I', 'i', 'l', 'L', '|', 'Ι', 'ι'], scope: ['senderid', 'content', 'url'], notes: 'Latin I/l, Greek Iota, pipe' },
                '2': { equivalents: ['Z'], scope: ['url'], notes: 'Visual similarity in some fonts' },
                '3': { equivalents: ['E', 'e', 'Ε', 'ε'], scope: ['senderid', 'content', 'url'], notes: 'Reversed E appearance' },
                '4': { equivalents: ['A', 'a'], scope: ['senderid', 'url'], notes: 'Common leet substitution' },
                '5': { equivalents: ['S', 's', 'Ѕ', 'ѕ'], scope: ['senderid', 'content', 'url'], notes: 'Cyrillic Dze' },
                '6': { equivalents: ['b', 'G'], scope: ['url'], notes: 'Visual similarity' },
                '7': { equivalents: ['T', 't', 'Τ', 'τ'], scope: ['senderid', 'url'], notes: 'Common leet substitution' },
                '8': { equivalents: ['B', 'Β', 'β'], scope: ['senderid', 'content'], notes: 'Greek Beta' },
                '9': { equivalents: ['g', 'q'], scope: ['url'], notes: 'Visual similarity' }
            };
            
            function computeRiskInternal(equivalents, scope) {
                if (equivalents.length === 0) return 'none';
                var hasUrlScope = scope.indexOf('url') !== -1;
                var hasDigits = equivalents.some(function(eq) { return /[0-9]/.test(eq); });
                var hasPunctuation = equivalents.some(function(eq) { return /[!@#$%^&*(),.?":{}|<>]/.test(eq); });
                var digitCount = equivalents.filter(function(eq) { return /[0-9]/.test(eq); }).length;
                
                if (hasUrlScope) return 'high';
                if (digitCount >= 2 && hasPunctuation) return 'high';
                if (hasDigits || equivalents.length > 5) return 'medium';
                return 'low';
            }
            
            'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.split('').forEach(function(char) {
                var data = uppercaseEquivalents[char] || { equivalents: [], scope: ['senderid'], notes: '' };
                library.push({
                    base: char,
                    type: 'uppercase',
                    equivalents: data.equivalents,
                    scope: data.scope,
                    notes: data.notes,
                    enabled: data.equivalents.length > 0,
                    risk: computeRiskInternal(data.equivalents, data.scope),
                    updatedAt: '28-01-2026',
                    updatedBy: 'admin@quicksms.co.uk'
                });
            });
            
            'abcdefghijklmnopqrstuvwxyz'.split('').forEach(function(char) {
                var data = lowercaseEquivalents[char] || { equivalents: [], scope: ['senderid'], notes: '' };
                library.push({
                    base: char,
                    type: 'lowercase',
                    equivalents: data.equivalents,
                    scope: data.scope,
                    notes: data.notes,
                    enabled: data.equivalents.length > 0,
                    risk: computeRiskInternal(data.equivalents, data.scope),
                    updatedAt: '28-01-2026',
                    updatedBy: 'admin@quicksms.co.uk'
                });
            });
            
            '0123456789'.split('').forEach(function(char) {
                var data = digitEquivalents[char] || { equivalents: [], scope: ['senderid'], notes: '' };
                library.push({
                    base: char,
                    type: 'digit',
                    equivalents: data.equivalents,
                    scope: data.scope,
                    notes: data.notes,
                    enabled: data.equivalents.length > 0,
                    risk: computeRiskInternal(data.equivalents, data.scope),
                    updatedAt: '28-01-2026',
                    updatedBy: 'admin@quicksms.co.uk'
                });
            });
            
            return library;
        })();

        mockData.quarantinedMessages = [
            { 
                id: 'QRN-001', timestamp: '29-01-2026 10:15:32', accountId: 'ACC-10045', accountName: 'TechStart Ltd', 
                subAccountId: 'SUB-001', subAccountName: 'Marketing Dept', senderId: 'TECHPROMO', 
                recipient: '+44****7890', recipientFull: '+447700900890',
                messageSnippet: 'Congratulations! You have won a prize...', 
                fullMessage: 'Congratulations! You have won a prize of £1000! Click here to claim: http://win-prize-now.xyz/claim?ref=123',
                hasUrl: true, extractedUrls: ['http://win-prize-now.xyz/claim?ref=123'],
                ruleTriggered: 'content', ruleName: 'Lottery Keywords', ruleId: 'CONT-002',
                triggeredRules: [
                    { engine: 'MessageContentEngine', ruleId: 'CONT-002', ruleName: 'Lottery Keywords', matchType: 'Keyword', matchedValue: 'won a prize' }
                ],
                normalisedValues: { senderId: 'TECHPROMO', senderIdNormalised: 'techpromo', messageNormalised: 'congratulations! you have won a prize of £1000! click here to claim: http://win-prize-now.xyz/claim?ref=123' },
                status: 'pending', reviewer: null, decisionAt: null,
                notes: [], idempotencyKey: 'idem-001-abc', releaseAttempts: 0
            },
            { 
                id: 'QRN-002', timestamp: '29-01-2026 09:45:18', accountId: 'ACC-10089', accountName: 'HealthFirst UK', 
                subAccountId: null, subAccountName: null, senderId: 'HEALTH', 
                recipient: '+44****1234', recipientFull: '+447700901234',
                messageSnippet: 'Click here to verify your account immediately...', 
                fullMessage: 'URGENT: Click here to verify your account immediately or it will be suspended: http://verify-now.tk/urgent',
                hasUrl: true, extractedUrls: ['http://verify-now.tk/urgent'],
                ruleTriggered: 'url', ruleName: 'Suspicious URL Pattern', ruleId: 'URL-003',
                triggeredRules: [
                    { engine: 'UrlEnforcementEngine', ruleId: 'URL-003', ruleName: 'Suspicious URL Pattern', matchType: 'Wildcard', matchedValue: '*.tk/*' }
                ],
                normalisedValues: { senderId: 'HEALTH', senderIdNormalised: 'health', messageNormalised: 'urgent: click here to verify your account immediately or it will be suspended: http://verify-now.tk/urgent' },
                status: 'pending', reviewer: null, decisionAt: null,
                notes: [{ author: 'admin@quicksms.co.uk', timestamp: '29-01-2026 10:00:00', text: 'Appears to be phishing attempt - investigate account' }],
                idempotencyKey: 'idem-002-def', releaseAttempts: 0
            },
            { 
                id: 'QRN-003', timestamp: '29-01-2026 08:30:45', accountId: 'ACC-10112', accountName: 'E-Commerce Hub', 
                subAccountId: 'SUB-005', subAccountName: 'Promotions', senderId: 'ECOMDEALS', 
                recipient: '+44****5678', recipientFull: '+447700905678',
                messageSnippet: 'Limited time offer! Free casino bonus...', 
                fullMessage: 'Limited time offer! Free casino bonus when you sign up. Visit our site for more gaming deals!',
                hasUrl: false, extractedUrls: [],
                ruleTriggered: 'content', ruleName: 'Gambling Keywords', ruleId: 'CONT-003',
                triggeredRules: [
                    { engine: 'MessageContentEngine', ruleId: 'CONT-003', ruleName: 'Gambling Keywords', matchType: 'Keyword', matchedValue: 'casino bonus' }
                ],
                normalisedValues: { senderId: 'ECOMDEALS', senderIdNormalised: 'ecomdeals', messageNormalised: 'limited time offer! free casino bonus when you sign up. visit our site for more gaming deals!' },
                status: 'pending', reviewer: null, decisionAt: null,
                notes: [], idempotencyKey: 'idem-003-ghi', releaseAttempts: 0
            },
            { 
                id: 'QRN-004', timestamp: '29-01-2026 07:22:11', accountId: 'ACC-10045', accountName: 'TechStart Ltd', 
                subAccountId: null, subAccountName: null, senderId: 'HMRC', 
                recipient: '+44****9999', recipientFull: '+447700909999',
                messageSnippet: 'Your tax refund is ready. Click to claim...', 
                fullMessage: 'HMRC: Your tax refund of £450.32 is ready. Click to claim within 24 hours: http://hmrc-refund.net/claim',
                hasUrl: true, extractedUrls: ['http://hmrc-refund.net/claim'],
                ruleTriggered: 'senderid', ruleName: 'Block HMRC Impersonation', ruleId: 'SID-001',
                triggeredRules: [
                    { engine: 'SenderIdEnforcementEngine', ruleId: 'SID-001', ruleName: 'Block HMRC Impersonation', matchType: 'Exact', matchedValue: 'HMRC' },
                    { engine: 'UrlEnforcementEngine', ruleId: 'URL-005', ruleName: 'Suspicious Domain', matchType: 'Exact', matchedValue: 'hmrc-refund.net' }
                ],
                normalisedValues: { senderId: 'HMRC', senderIdNormalised: 'hmrc', messageNormalised: 'hmrc: your tax refund of £450.32 is ready. click to claim within 24 hours: http://hmrc-refund.net/claim' },
                status: 'pending', reviewer: null, decisionAt: null,
                notes: [], idempotencyKey: 'idem-004-jkl', releaseAttempts: 0
            },
            { 
                id: 'QRN-005', timestamp: '28-01-2026 16:45:00', accountId: 'ACC-10200', accountName: 'FastLoans Ltd', 
                subAccountId: null, subAccountName: null, senderId: 'LOANS', 
                recipient: '+44****4321', recipientFull: '+447700904321',
                messageSnippet: 'Instant approval! Get cash now at bit.ly/xxx', 
                fullMessage: 'Instant approval! Get cash now. No credit check needed. Apply at bit.ly/fastcash-now',
                hasUrl: true, extractedUrls: ['bit.ly/fastcash-now'],
                ruleTriggered: 'domain_age', ruleName: 'Domain Age Check', ruleId: 'DAGE-001',
                triggeredRules: [
                    { engine: 'DomainAgeEngine', ruleId: 'DAGE-001', ruleName: 'Domain Age Check', matchType: 'Age', matchedValue: 'Domain registered 2 hours ago (threshold: 72 hours)' }
                ],
                normalisedValues: { senderId: 'LOANS', senderIdNormalised: 'loans', messageNormalised: 'instant approval! get cash now. no credit check needed. apply at bit.ly/fastcash-now' },
                status: 'pending', reviewer: null, decisionAt: null,
                notes: [], idempotencyKey: 'idem-005-mno', releaseAttempts: 0
            },
            { 
                id: 'QRN-006', timestamp: '28-01-2026 14:30:22', accountId: 'ACC-10089', accountName: 'HealthFirst UK', 
                subAccountId: 'SUB-003', subAccountName: 'Patient Comms', senderId: 'NHSALERT', 
                recipient: '+44****8765', recipientFull: '+447700908765',
                messageSnippet: 'Important health notice regarding...', 
                fullMessage: 'Important health notice regarding your upcoming appointment on 30th January. Please confirm attendance.',
                hasUrl: false, extractedUrls: [],
                ruleTriggered: 'senderid', ruleName: 'Block NHS Impersonation', ruleId: 'SID-002',
                triggeredRules: [
                    { engine: 'SenderIdEnforcementEngine', ruleId: 'SID-002', ruleName: 'Block NHS Impersonation', matchType: 'Fuzzy', matchedValue: 'NHSALERT (variant of NHS)' }
                ],
                normalisedValues: { senderId: 'NHSALERT', senderIdNormalised: 'nhsalert', messageNormalised: 'important health notice regarding your upcoming appointment on 30th january. please confirm attendance.' },
                status: 'released', reviewer: 'admin@quicksms.co.uk', decisionAt: '28-01-2026 15:10:00',
                notes: [{ author: 'admin@quicksms.co.uk', timestamp: '28-01-2026 15:08:00', text: 'Verified with HealthFirst - legitimate NHS partnership communication' }],
                idempotencyKey: 'idem-006-pqr', releaseAttempts: 1
            },
            { 
                id: 'QRN-007', timestamp: '28-01-2026 11:15:33', accountId: 'ACC-10150', accountName: 'CryptoTraders', 
                subAccountId: null, subAccountName: null, senderId: 'CRYPTO', 
                recipient: '+44****2222', recipientFull: '+447700902222',
                messageSnippet: 'Bitcoin giveaway! Double your crypto...', 
                fullMessage: 'Bitcoin giveaway! Double your crypto instantly. Send 0.1 BTC to wallet xyz and receive 0.2 BTC back!',
                hasUrl: true, extractedUrls: [],
                ruleTriggered: 'content', ruleName: 'Cryptocurrency Scam', ruleId: 'CONT-005',
                triggeredRules: [
                    { engine: 'MessageContentEngine', ruleId: 'CONT-005', ruleName: 'Cryptocurrency Scam', matchType: 'Regex', matchedValue: 'double.*crypto|send.*btc.*receive' }
                ],
                normalisedValues: { senderId: 'CRYPTO', senderIdNormalised: 'crypto', messageNormalised: 'bitcoin giveaway! double your crypto instantly. send 0.1 btc to wallet xyz and receive 0.2 btc back!' },
                status: 'blocked', reviewer: 'compliance@quicksms.co.uk', decisionAt: '28-01-2026 12:00:00',
                notes: [
                    { author: 'compliance@quicksms.co.uk', timestamp: '28-01-2026 11:45:00', text: 'Classic crypto doubling scam pattern' },
                    { author: 'compliance@quicksms.co.uk', timestamp: '28-01-2026 12:00:00', text: 'Blocked permanently - account flagged for review' }
                ],
                idempotencyKey: 'idem-007-stu', releaseAttempts: 0
            },
            { 
                id: 'QRN-008', timestamp: '27-01-2026 09:00:15', accountId: 'ACC-10112', accountName: 'E-Commerce Hub', 
                subAccountId: 'SUB-005', subAccountName: 'Promotions', senderId: 'SHOP', 
                recipient: '+44****3333', recipientFull: '+447700903333',
                messageSnippet: 'Flash sale! 50% off everything...', 
                fullMessage: 'Flash sale! 50% off everything this weekend only. Shop now at bit.ly/shop-sale',
                hasUrl: true, extractedUrls: ['bit.ly/shop-sale'],
                ruleTriggered: 'url', ruleName: 'URL Shortener Flag', ruleId: 'URL-002',
                triggeredRules: [
                    { engine: 'UrlEnforcementEngine', ruleId: 'URL-002', ruleName: 'URL Shortener Flag', matchType: 'Wildcard', matchedValue: 'bit.ly/*' }
                ],
                normalisedValues: { senderId: 'SHOP', senderIdNormalised: 'shop', messageNormalised: 'flash sale! 50% off everything this weekend only. shop now at bit.ly/shop-sale' },
                status: 'released', reviewer: 'admin@quicksms.co.uk', decisionAt: '27-01-2026 09:30:00',
                notes: [{ author: 'admin@quicksms.co.uk', timestamp: '27-01-2026 09:28:00', text: 'Verified shortened URL points to legitimate e-commerce site' }],
                idempotencyKey: 'idem-008-vwx', releaseAttempts: 1
            }
        ];
        
        mockData.quarantineFeatureFlags = {
            notifyCustomerAdminOnRelease: true,
            requireNoteOnBlock: false,
            allowAddExceptionFromQuarantine: true,
            allowCreateRuleFromQuarantine: true
        };
        
        mockData.antiSpamSettings = {
            preventRepeatContent: false,
            windowHours: 24,
            lastUpdated: null,
            updatedBy: null
        };
    }

    function renderAllTabs() {
        renderSenderIdTab();
        renderContentTab();
        renderUrlTab();
        renderNormTab();
        renderQuarantineTab();
    }

    function renderSenderIdTab() {
        var tbody = document.getElementById('senderid-rules-body');
        var emptyState = document.getElementById('senderid-empty-state');
        var rules = mockData.senderIdRules;

        var categoryLabels = {
            'bank_impersonation': 'Bank Impersonation',
            'government': 'Government',
            'lottery_prize': 'Lottery/Prize',
            'brand_abuse': 'Brand Abuse',
            'premium_rate': 'Premium Rate',
            'other': 'Other'
        };


        if (rules.length === 0) {
            tbody.innerHTML = '';
            emptyState.style.display = 'block';
            return;
        }

        emptyState.style.display = 'none';
        tbody.innerHTML = rules.map(function(rule) {
            var ruleTypeBadge = rule.ruleType === 'block' 
                ? '<span class="sec-status-badge blocked">Block</span>'
                : '<span class="sec-status-badge pending">Flag</span>';
            var statusBadge = rule.status === 'active'
                ? '<span class="sec-status-badge active">Active</span>'
                : '<span class="sec-status-badge draft">Disabled</span>';
            var normBadge = rule.applyNormalisation
                ? '<span class="badge bg-success" style="font-size: 0.65rem;">Y</span>'
                : '<span class="badge bg-secondary" style="font-size: 0.65rem;">N</span>';
            var isSuperAdmin = currentAdmin.role === 'super_admin';
            
            return '<tr data-rule-id="' + rule.id + '">' +
                '<td><strong>' + rule.name + '</strong><br><small class="text-muted">' + rule.id + '</small></td>' +
                '<td><code style="background: #e9ecef; padding: 0.15rem 0.4rem; border-radius: 3px;">' + rule.baseSenderId + '</code></td>' +
                '<td>' + ruleTypeBadge + '</td>' +
                '<td>' + (categoryLabels[rule.category] || rule.category) + '</td>' +
                '<td class="text-center">' + normBadge + '</td>' +
                '<td>' + statusBadge + '</td>' +
                '<td><small>' + rule.createdBy.split('@')[0] + '</small></td>' +
                '<td><small>' + rule.updatedAt + '</small></td>' +
                '<td>' +
                    '<div class="dropdown">' +
                        '<button class="action-menu-btn" data-bs-toggle="dropdown" aria-expanded="false"><i class="fas fa-ellipsis-v"></i></button>' +
                        '<ul class="dropdown-menu dropdown-menu-end">' +
                            '<li><a class="dropdown-item" href="javascript:void(0)" onclick="viewSenderIdRule(\'' + rule.id + '\')"><i class="fas fa-eye me-2 text-muted"></i>View</a></li>' +
                            '<li><a class="dropdown-item" href="javascript:void(0)" onclick="editSenderIdRule(\'' + rule.id + '\')"><i class="fas fa-edit me-2 text-muted"></i>Edit</a></li>' +
                            '<li><hr class="dropdown-divider"></li>' +
                            (rule.status === 'active' 
                                ? '<li><a class="dropdown-item" href="javascript:void(0)" onclick="toggleSenderIdRuleStatus(\'' + rule.id + '\', \'disabled\')"><i class="fas fa-ban me-2 text-warning"></i>Disable</a></li>'
                                : '<li><a class="dropdown-item" href="javascript:void(0)" onclick="toggleSenderIdRuleStatus(\'' + rule.id + '\', \'active\')"><i class="fas fa-check me-2 text-success"></i>Enable</a></li>') +
                            (isSuperAdmin ? '<li><hr class="dropdown-divider"></li><li><a class="dropdown-item text-danger" href="javascript:void(0)" onclick="showDeleteConfirmation(\'' + rule.id + '\', \'senderid\')"><i class="fas fa-trash me-2"></i>Delete</a></li>' : '') +
                        '</ul>' +
                    '</div>' +
                '</td>' +
                '</tr>';
        }).join('');
    }

    function renderContentTab() {
        var tbody = document.getElementById('content-rules-body');
        var emptyState = document.getElementById('content-empty-state');
        
        var statusFilter = document.getElementById('content-filter-status').value;
        var matchTypeFilter = document.getElementById('content-filter-matchtype').value;
        var ruleTypeFilter = document.getElementById('content-filter-ruletype').value;
        var searchTerm = document.getElementById('content-search').value.toLowerCase();
        
        var rules = mockData.contentRules.filter(function(rule) {
            if (statusFilter && rule.status !== statusFilter) return false;
            if (matchTypeFilter && rule.matchType !== matchTypeFilter) return false;
            if (ruleTypeFilter && rule.ruleType !== ruleTypeFilter) return false;
            if (searchTerm && rule.name.toLowerCase().indexOf(searchTerm) === -1 && 
                rule.matchValue.toLowerCase().indexOf(searchTerm) === -1) return false;
            return true;
        });


        if (rules.length === 0) {
            tbody.innerHTML = '';
            emptyState.style.display = 'block';
            return;
        }

        emptyState.style.display = 'none';
        tbody.innerHTML = rules.map(function(rule) {
            var matchTypeBadge = rule.matchType === 'keyword' 
                ? '<span class="sec-status-badge" style="background: #e0e7ff; color: #3730a3;"><i class="fas fa-key me-1"></i>Keyword</span>'
                : '<span class="sec-status-badge" style="background: #fef3c7; color: #92400e;"><i class="fas fa-code me-1"></i>Regex</span>';
            
            var ruleTypeBadge = rule.ruleType === 'block'
                ? '<span class="sec-status-badge blocked"><i class="fas fa-ban me-1"></i>Block</span>'
                : '<span class="sec-status-badge pending"><i class="fas fa-flag me-1"></i>Flag</span>';
            
            var normBadge = rule.applyNormalisation
                ? '<span class="sec-status-badge active"><i class="fas fa-check me-1"></i>Yes</span>'
                : '<span class="sec-status-badge disabled"><i class="fas fa-times me-1"></i>No</span>';
            
            var statusBadge = '<span class="sec-status-badge ' + rule.status + '">' + 
                (rule.status === 'active' ? '<i class="fas fa-check-circle me-1"></i>' : '<i class="fas fa-pause-circle me-1"></i>') +
                rule.status.charAt(0).toUpperCase() + rule.status.slice(1) + '</span>';
            
            var dateOnly = rule.updatedAt.split(' ')[0];
            
            return '<tr data-rule-id="' + rule.id + '">' +
                '<td><strong>' + rule.name + '</strong><br><small class="text-muted" style="font-size: 0.7rem;">' + rule.id + '</small></td>' +
                '<td>' + matchTypeBadge + '</td>' +
                '<td>' + ruleTypeBadge + '</td>' +
                '<td>' + normBadge + '</td>' +
                '<td>' + statusBadge + '</td>' +
                '<td><span style="font-size: 0.8rem;">' + dateOnly + '</span></td>' +
                '<td>' +
                    '<div class="action-menu-container">' +
                        '<button class="action-menu-btn" onclick="toggleContentActionMenu(this, \'' + rule.id + '\')"><i class="fas fa-ellipsis-v"></i></button>' +
                        '<div class="action-menu-dropdown" id="content-menu-' + rule.id + '">' +
                            '<a href="#" onclick="viewContentRule(\'' + rule.id + '\'); return false;"><i class="fas fa-eye"></i> View Details</a>' +
                            '<a href="#" onclick="editContentRule(\'' + rule.id + '\'); return false;"><i class="fas fa-edit"></i> Edit Rule</a>' +
                            '<a href="#" onclick="toggleContentRuleStatus(\'' + rule.id + '\'); return false;"><i class="fas fa-toggle-on"></i> ' + (rule.status === 'active' ? 'Disable' : 'Enable') + '</a>' +
                            '<div class="dropdown-divider"></div>' +
                            '<a href="#" class="text-danger" onclick="deleteContentRule(\'' + rule.id + '\'); return false;"><i class="fas fa-trash"></i> Delete</a>' +
                        '</div>' +
                    '</div>' +
                '</td>' +
                '</tr>';
        }).join('');
    }
    
    function toggleContentActionMenu(btn, ruleId) {
        document.querySelectorAll('.action-menu-dropdown').forEach(function(menu) {
            if (menu.id !== 'content-menu-' + ruleId) {
                menu.classList.remove('show');
            }
        });
        var menu = document.getElementById('content-menu-' + ruleId);
        menu.classList.toggle('show');
    }
    
    function showAddContentRuleModal() {
        document.getElementById('content-rule-modal-title').textContent = 'Add Content Rule';
        document.getElementById('content-rule-form').reset();
        document.getElementById('content-rule-id').value = '';
        document.getElementById('content-match-type').value = 'keyword';
        document.getElementById('content-apply-normalisation').checked = true;
        updateContentMatchInputLabel();
        clearContentRuleErrors();
        var modal = new bootstrap.Modal(document.getElementById('contentRuleModal'));
        modal.show();
    }
    
    function editContentRule(ruleId) {
        var rule = mockData.contentRules.find(function(r) { return r.id === ruleId; });
        if (!rule) return;
        
        document.getElementById('content-rule-modal-title').textContent = 'Edit Content Rule';
        document.getElementById('content-rule-id').value = rule.id;
        document.getElementById('content-rule-name').value = rule.name;
        document.getElementById('content-match-type').value = rule.matchType;
        document.getElementById('content-match-value').value = rule.matchValue;
        document.getElementById('content-rule-type').value = rule.ruleType;
        document.getElementById('content-apply-normalisation').checked = rule.applyNormalisation;
        updateContentMatchInputLabel();
        clearContentRuleErrors();
        
        closeAllContentMenus();
        var modal = new bootstrap.Modal(document.getElementById('contentRuleModal'));
        modal.show();
    }
    
    function viewContentRule(ruleId) {
        editContentRule(ruleId);
    }
    
    function updateContentMatchInputLabel() {
        var matchType = document.getElementById('content-match-type').value;
        var label = document.getElementById('content-match-value-label');
        var input = document.getElementById('content-match-value');
        var helpText = document.getElementById('content-match-value-help');
        
        if (matchType === 'keyword') {
            label.textContent = 'Keywords (comma-separated)';
            input.placeholder = 'verify your account, click here, suspended';
            helpText.textContent = 'Enter keywords separated by commas. Matching is case-insensitive.';
        } else {
            label.textContent = 'Regex Pattern';
            input.placeholder = '(verify|confirm)\\s+your\\s+(account|details)';
            helpText.textContent = 'Enter a valid regular expression. Will be validated before saving.';
        }
    }
    
    function validateContentRuleForm() {
        clearContentRuleErrors();
        var isValid = true;
        
        var name = document.getElementById('content-rule-name').value.trim();
        if (!name) {
            showContentFieldError('content-rule-name', 'Rule name is required');
            isValid = false;
        }
        
        var matchValue = document.getElementById('content-match-value').value.trim();
        if (!matchValue) {
            showContentFieldError('content-match-value', 'Match value is required');
            isValid = false;
        }
        
        var matchType = document.getElementById('content-match-type').value;
        if (matchType === 'regex' && matchValue) {
            var regexError = validateRegexPattern(matchValue);
            if (regexError) {
                showContentFieldError('content-match-value', regexError);
                isValid = false;
            }
        }
        
        return isValid;
    }
    
    function validateRegexPattern(pattern) {
        try {
            new RegExp(pattern);
            return null;
        } catch (e) {
            return 'Invalid regex: ' + e.message.replace('Invalid regular expression: ', '');
        }
    }
    
    function showContentFieldError(fieldId, message) {
        var field = document.getElementById(fieldId);
        field.classList.add('is-invalid');
        var errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback';
        errorDiv.textContent = message;
        field.parentNode.appendChild(errorDiv);
    }
    
    function clearContentRuleErrors() {
        document.querySelectorAll('#content-rule-form .is-invalid').forEach(function(el) {
            el.classList.remove('is-invalid');
        });
        document.querySelectorAll('#content-rule-form .invalid-feedback').forEach(function(el) {
            el.remove();
        });
    }
    
    function saveContentRule() {
        if (!validateContentRuleForm()) return;
        
        var ruleId = document.getElementById('content-rule-id').value;
        var ruleData = {
            name: document.getElementById('content-rule-name').value.trim(),
            matchType: document.getElementById('content-match-type').value,
            matchValue: document.getElementById('content-match-value').value.trim(),
            ruleType: document.getElementById('content-rule-type').value,
            applyNormalisation: document.getElementById('content-apply-normalisation').checked,
            status: 'active',
            updatedAt: formatDateTime(new Date())
        };
        
        var eventType, beforeState = null;
        
        if (ruleId) {
            var existingRule = mockData.contentRules.find(function(r) { return r.id === ruleId; });
            if (existingRule) {
                beforeState = JSON.parse(JSON.stringify(existingRule));
                Object.assign(existingRule, ruleData);
                eventType = 'CONTENT_RULE_UPDATED';
            }
        } else {
            ruleData.id = 'CNT-' + String(mockData.contentRules.length + 1).padStart(3, '0');
            ruleData.createdBy = currentAdmin.email;
            ruleData.createdAt = ruleData.updatedAt;
            mockData.contentRules.push(ruleData);
            eventType = 'CONTENT_RULE_CREATED';
        }
        
        logAuditEvent(eventType, {
            ruleId: ruleId || ruleData.id,
            ruleName: ruleData.name,
            matchType: ruleData.matchType,
            ruleType: ruleData.ruleType,
            before: beforeState,
            after: ruleData
        });
        
        bootstrap.Modal.getInstance(document.getElementById('contentRuleModal')).hide();
        renderContentTab();
        showToast(ruleId ? 'Content rule updated successfully' : 'Content rule created successfully', 'success');
    }
    
    function toggleContentRuleStatus(ruleId) {
        var rule = mockData.contentRules.find(function(r) { return r.id === ruleId; });
        if (!rule) return;
        
        var beforeStatus = rule.status;
        rule.status = rule.status === 'active' ? 'disabled' : 'active';
        rule.updatedAt = formatDateTime(new Date());
        
        logAuditEvent('CONTENT_RULE_STATUS_CHANGED', {
            ruleId: ruleId,
            ruleName: rule.name,
            beforeStatus: beforeStatus,
            afterStatus: rule.status
        });
        
        closeAllContentMenus();
        renderContentTab();
        showToast('Content rule ' + (rule.status === 'active' ? 'enabled' : 'disabled'), 'success');
    }
    
    function deleteContentRule(ruleId) {
        var rule = mockData.contentRules.find(function(r) { return r.id === ruleId; });
        if (!rule) return;
        
        document.getElementById('confirm-delete-message').textContent = 'Are you sure you want to delete the content rule "' + rule.name + '"?';
        document.getElementById('delete-rule-id').value = ruleId;
        document.getElementById('delete-rule-type').value = 'content';
        
        closeAllContentMenus();
        var modal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
        modal.show();
    }
    
    function closeAllContentMenus() {
        document.querySelectorAll('.action-menu-dropdown').forEach(function(menu) {
            menu.classList.remove('show');
        });
    }
    
    function resetContentFilters() {
        document.getElementById('content-filter-status').value = '';
        document.getElementById('content-filter-matchtype').value = '';
        document.getElementById('content-filter-ruletype').value = '';
        document.getElementById('content-search').value = '';
        renderContentTab();
    }

    function renderUrlTab() {
        var tbody = document.getElementById('url-rules-body');
        var emptyState = document.getElementById('url-empty-state');
        
        var statusFilter = document.getElementById('url-filter-status').value;
        var matchTypeFilter = document.getElementById('url-filter-matchtype').value;
        var ruleTypeFilter = document.getElementById('url-filter-ruletype').value;
        var searchTerm = document.getElementById('url-search').value.toLowerCase();
        
        var rules = mockData.urlRules.filter(function(rule) {
            if (statusFilter && rule.status !== statusFilter) return false;
            if (matchTypeFilter && rule.matchType !== matchTypeFilter) return false;
            if (ruleTypeFilter && rule.ruleType !== ruleTypeFilter) return false;
            if (searchTerm && rule.pattern.toLowerCase().indexOf(searchTerm) === -1) return false;
            return true;
        });

        
        document.getElementById('domain-age-enabled').checked = mockData.domainAgeSettings.enabled;
        document.getElementById('domain-age-hours').value = mockData.domainAgeSettings.minAgeHours;
        document.getElementById('domain-age-hours').disabled = !mockData.domainAgeSettings.enabled;
        document.getElementById('domain-age-action').value = mockData.domainAgeSettings.action;
        document.getElementById('domain-age-action').disabled = !mockData.domainAgeSettings.enabled;

        if (rules.length === 0) {
            tbody.innerHTML = '';
            emptyState.style.display = 'block';
        } else {
            emptyState.style.display = 'none';
            tbody.innerHTML = rules.map(function(rule) {
                var matchTypeBadges = {
                    'exact': '<span class="sec-status-badge" style="background: #dbeafe; color: #1e40af;"><i class="fas fa-bullseye me-1"></i>Exact</span>',
                    'wildcard': '<span class="sec-status-badge" style="background: #fef3c7; color: #92400e;"><i class="fas fa-asterisk me-1"></i>Wildcard</span>',
                    'regex': '<span class="sec-status-badge" style="background: #f3e8ff; color: #6b21a8;"><i class="fas fa-code me-1"></i>Regex</span>'
                };
                
                var ruleTypeBadge = rule.ruleType === 'block'
                    ? '<span class="sec-status-badge blocked"><i class="fas fa-ban me-1"></i>Block</span>'
                    : '<span class="sec-status-badge pending"><i class="fas fa-flag me-1"></i>Flag</span>';
                
                var domainAgeBadge = rule.applyDomainAge
                    ? '<span class="sec-status-badge active"><i class="fas fa-check me-1"></i>Yes</span>'
                    : '<span class="sec-status-badge disabled"><i class="fas fa-times me-1"></i>No</span>';
                
                var statusBadge = '<span class="sec-status-badge ' + rule.status + '">' + 
                    (rule.status === 'active' ? '<i class="fas fa-check-circle me-1"></i>' : '<i class="fas fa-pause-circle me-1"></i>') +
                    rule.status.charAt(0).toUpperCase() + rule.status.slice(1) + '</span>';
                
                var dateOnly = rule.updatedAt.split(' ')[0];
                
                return '<tr data-rule-id="' + rule.id + '">' +
                    '<td><code style="background: #f8f9fa; padding: 0.25rem 0.5rem; border-radius: 4px;">' + rule.pattern + '</code><br><small class="text-muted" style="font-size: 0.7rem;">' + rule.id + '</small></td>' +
                    '<td>' + matchTypeBadges[rule.matchType] + '</td>' +
                    '<td>' + ruleTypeBadge + '</td>' +
                    '<td>' + domainAgeBadge + '</td>' +
                    '<td>' + statusBadge + '</td>' +
                    '<td><span style="font-size: 0.8rem;">' + dateOnly + '</span></td>' +
                    '<td>' +
                        '<div class="action-menu-container">' +
                            '<button class="action-menu-btn" onclick="toggleUrlActionMenu(this, \'' + rule.id + '\')"><i class="fas fa-ellipsis-v"></i></button>' +
                            '<div class="action-menu-dropdown" id="url-menu-' + rule.id + '">' +
                                '<a href="#" onclick="viewUrlRule(\'' + rule.id + '\'); return false;"><i class="fas fa-eye"></i> View Details</a>' +
                                '<a href="#" onclick="editUrlRule(\'' + rule.id + '\'); return false;"><i class="fas fa-edit"></i> Edit Rule</a>' +
                                '<a href="#" onclick="toggleUrlRuleStatus(\'' + rule.id + '\'); return false;"><i class="fas fa-toggle-on"></i> ' + (rule.status === 'active' ? 'Disable' : 'Enable') + '</a>' +
                                '<div class="dropdown-divider"></div>' +
                                '<a href="#" class="text-danger" onclick="deleteUrlRule(\'' + rule.id + '\'); return false;"><i class="fas fa-trash"></i> Delete</a>' +
                            '</div>' +
                        '</div>' +
                    '</td>' +
                    '</tr>';
            }).join('');
        }
        
        renderDomainAgeExceptions();
    }
    
    function renderDomainAgeExceptions() {
        var tbody = document.getElementById('domain-age-exceptions-body');
        var emptyState = document.getElementById('domain-exceptions-empty-state');
        var exceptions = mockData.domainAgeExceptions;
        
        if (exceptions.length === 0) {
            tbody.innerHTML = '';
            emptyState.style.display = 'block';
            return;
        }
        
        emptyState.style.display = 'none';
        tbody.innerHTML = exceptions.map(function(exc) {
            var dateOnly = exc.addedAt.split(' ')[0];
            return '<tr data-exception-id="' + exc.id + '">' +
                '<td><code>' + exc.accountId + '</code></td>' +
                '<td><strong>' + exc.accountName + '</strong></td>' +
                '<td><span style="font-size: 0.85rem;">' + exc.reason + '</span></td>' +
                '<td><span style="font-size: 0.8rem;">' + exc.addedBy + '</span></td>' +
                '<td><span style="font-size: 0.8rem;">' + dateOnly + '</span></td>' +
                '<td>' +
                    '<button class="action-menu-btn text-danger" onclick="removeDomainAgeException(\'' + exc.id + '\')" title="Remove Exception">' +
                        '<i class="fas fa-trash"></i>' +
                    '</button>' +
                '</td>' +
                '</tr>';
        }).join('');
    }
    
    function toggleUrlActionMenu(btn, ruleId) {
        document.querySelectorAll('.action-menu-dropdown').forEach(function(menu) {
            if (menu.id !== 'url-menu-' + ruleId) {
                menu.classList.remove('show');
            }
        });
        var menu = document.getElementById('url-menu-' + ruleId);
        menu.classList.toggle('show');
    }
    
    function showAddUrlRuleModal() {
        document.getElementById('url-rule-modal-title').textContent = 'Add URL Rule';
        document.getElementById('url-rule-form').reset();
        document.getElementById('url-rule-id').value = '';
        document.getElementById('url-match-type').value = 'exact';
        document.getElementById('url-apply-domain-age').checked = true;
        updateUrlPatternLabel();
        clearUrlRuleErrors();
        var modal = new bootstrap.Modal(document.getElementById('urlRuleModal'));
        modal.show();
    }
    
    function editUrlRule(ruleId) {
        var rule = mockData.urlRules.find(function(r) { return r.id === ruleId; });
        if (!rule) return;
        
        document.getElementById('url-rule-modal-title').textContent = 'Edit URL Rule';
        document.getElementById('url-rule-id').value = rule.id;
        document.getElementById('url-pattern').value = rule.pattern;
        document.getElementById('url-match-type').value = rule.matchType;
        document.getElementById('url-rule-type').value = rule.ruleType;
        document.getElementById('url-apply-domain-age').checked = rule.applyDomainAge;
        updateUrlPatternLabel();
        clearUrlRuleErrors();
        
        closeAllUrlMenus();
        var modal = new bootstrap.Modal(document.getElementById('urlRuleModal'));
        modal.show();
    }
    
    function viewUrlRule(ruleId) {
        editUrlRule(ruleId);
    }
    
    function updateUrlPatternLabel() {
        var matchType = document.getElementById('url-match-type').value;
        var label = document.getElementById('url-pattern-label');
        var input = document.getElementById('url-pattern');
        var helpText = document.getElementById('url-pattern-help');
        
        var config = {
            'exact': { label: 'Domain', placeholder: 'example.com', help: 'Enter the exact domain to match (e.g., example.com)' },
            'wildcard': { label: 'Wildcard Pattern', placeholder: '*.example.com', help: 'Use * for wildcard matching (e.g., *.example.com matches all subdomains)' },
            'regex': { label: 'Regex Pattern', placeholder: 'phish\\d+\\.com', help: 'Enter a valid regular expression pattern' }
        };
        
        label.textContent = config[matchType].label;
        input.placeholder = config[matchType].placeholder;
        helpText.textContent = config[matchType].help;
    }
    
    function validateUrlRuleForm() {
        clearUrlRuleErrors();
        var isValid = true;
        
        var pattern = document.getElementById('url-pattern').value.trim();
        if (!pattern) {
            showUrlFieldError('url-pattern', 'Domain/pattern is required');
            isValid = false;
        }
        
        var matchType = document.getElementById('url-match-type').value;
        if (matchType === 'regex' && pattern) {
            try {
                new RegExp(pattern);
            } catch (e) {
                showUrlFieldError('url-pattern', 'Invalid regex: ' + e.message.replace('Invalid regular expression: ', ''));
                isValid = false;
            }
        }
        
        return isValid;
    }
    
    function showUrlFieldError(fieldId, message) {
        var field = document.getElementById(fieldId);
        field.classList.add('is-invalid');
        var errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback';
        errorDiv.textContent = message;
        field.parentNode.appendChild(errorDiv);
    }
    
    function clearUrlRuleErrors() {
        document.querySelectorAll('#url-rule-form .is-invalid').forEach(function(el) {
            el.classList.remove('is-invalid');
        });
        document.querySelectorAll('#url-rule-form .invalid-feedback').forEach(function(el) {
            el.remove();
        });
    }
    
    function saveUrlRule() {
        if (!validateUrlRuleForm()) return;
        
        var ruleId = document.getElementById('url-rule-id').value;
        var ruleData = {
            pattern: document.getElementById('url-pattern').value.trim(),
            matchType: document.getElementById('url-match-type').value,
            ruleType: document.getElementById('url-rule-type').value,
            applyDomainAge: document.getElementById('url-apply-domain-age').checked,
            status: 'active',
            updatedAt: formatDateTime(new Date())
        };
        
        var eventType, beforeState = null;
        
        if (ruleId) {
            var existingRule = mockData.urlRules.find(function(r) { return r.id === ruleId; });
            if (existingRule) {
                beforeState = JSON.parse(JSON.stringify(existingRule));
                Object.assign(existingRule, ruleData);
                eventType = 'URL_RULE_UPDATED';
            }
        } else {
            ruleData.id = 'URL-' + String(mockData.urlRules.length + 1).padStart(3, '0');
            ruleData.createdBy = currentAdmin.email;
            ruleData.createdAt = ruleData.updatedAt;
            mockData.urlRules.push(ruleData);
            eventType = 'URL_RULE_CREATED';
        }
        
        logAuditEvent(eventType, {
            ruleId: ruleId || ruleData.id,
            pattern: ruleData.pattern,
            matchType: ruleData.matchType,
            ruleType: ruleData.ruleType,
            before: beforeState,
            after: ruleData
        });
        
        bootstrap.Modal.getInstance(document.getElementById('urlRuleModal')).hide();
        renderUrlTab();
        showToast(ruleId ? 'URL rule updated successfully' : 'URL rule created successfully', 'success');
    }
    
    function toggleUrlRuleStatus(ruleId) {
        var rule = mockData.urlRules.find(function(r) { return r.id === ruleId; });
        if (!rule) return;
        
        var beforeStatus = rule.status;
        rule.status = rule.status === 'active' ? 'disabled' : 'active';
        rule.updatedAt = formatDateTime(new Date());
        
        logAuditEvent('URL_RULE_STATUS_CHANGED', {
            ruleId: ruleId,
            pattern: rule.pattern,
            beforeStatus: beforeStatus,
            afterStatus: rule.status
        });
        
        closeAllUrlMenus();
        renderUrlTab();
        showToast('URL rule ' + (rule.status === 'active' ? 'enabled' : 'disabled'), 'success');
    }
    
    function deleteUrlRule(ruleId) {
        var rule = mockData.urlRules.find(function(r) { return r.id === ruleId; });
        if (!rule) return;
        
        document.getElementById('confirm-delete-message').textContent = 'Are you sure you want to delete the URL rule "' + rule.pattern + '"?';
        document.getElementById('delete-rule-id').value = ruleId;
        document.getElementById('delete-rule-type').value = 'url';
        
        closeAllUrlMenus();
        var modal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
        modal.show();
    }
    
    function deleteUrlRuleById(ruleId) {
        var ruleIndex = mockData.urlRules.findIndex(function(r) { return r.id === ruleId; });
        if (ruleIndex === -1) return;
        
        var deletedRule = mockData.urlRules[ruleIndex];
        mockData.urlRules.splice(ruleIndex, 1);
        
        logAuditEvent('URL_RULE_DELETED', {
            ruleId: ruleId,
            pattern: deletedRule.pattern,
            deletedRule: deletedRule
        });
        
        showToast('URL rule deleted successfully', 'success');
    }
    
    function closeAllUrlMenus() {
        document.querySelectorAll('.action-menu-dropdown').forEach(function(menu) {
            menu.classList.remove('show');
        });
    }
    
    function resetUrlFilters() {
        document.getElementById('url-filter-status').value = '';
        document.getElementById('url-filter-matchtype').value = '';
        document.getElementById('url-filter-ruletype').value = '';
        document.getElementById('url-search').value = '';
        renderUrlTab();
    }
    
    function saveDomainAgeSettings() {
        var enabled = document.getElementById('domain-age-enabled').checked;
        var hours = parseInt(document.getElementById('domain-age-hours').value) || 72;
        var action = document.getElementById('domain-age-action').value;
        
        var beforeSettings = JSON.parse(JSON.stringify(mockData.domainAgeSettings));
        mockData.domainAgeSettings.enabled = enabled;
        mockData.domainAgeSettings.minAgeHours = hours;
        mockData.domainAgeSettings.action = action;
        
        logAuditEvent('DOMAIN_AGE_SETTINGS_UPDATED', {
            before: beforeSettings,
            after: mockData.domainAgeSettings
        });
        
        showToast('Domain age settings saved successfully', 'success');
    }
    
    function showAddDomainAgeExceptionModal() {
        document.getElementById('exception-form').reset();
        clearExceptionErrors();
        var modal = new bootstrap.Modal(document.getElementById('domainAgeExceptionModal'));
        modal.show();
    }
    
    function saveException() {
        clearExceptionErrors();
        var accountId = document.getElementById('exception-account-id').value.trim();
        var accountName = document.getElementById('exception-account-name').value.trim();
        var reason = document.getElementById('exception-reason').value.trim();
        
        var isValid = true;
        if (!accountId) {
            document.getElementById('exception-account-id').classList.add('is-invalid');
            isValid = false;
        }
        if (!accountName) {
            document.getElementById('exception-account-name').classList.add('is-invalid');
            isValid = false;
        }
        if (!reason) {
            document.getElementById('exception-reason').classList.add('is-invalid');
            isValid = false;
        }
        
        if (!isValid) return;
        
        var exception = {
            id: 'EXC-' + String(mockData.domainAgeExceptions.length + 1).padStart(3, '0'),
            accountId: accountId,
            accountName: accountName,
            reason: reason,
            addedBy: currentAdmin.email,
            addedAt: formatDateTime(new Date())
        };
        
        mockData.domainAgeExceptions.push(exception);
        
        logAuditEvent('DOMAIN_AGE_EXCEPTION_ADDED', {
            exceptionId: exception.id,
            accountId: accountId,
            accountName: accountName,
            reason: reason
        });
        
        bootstrap.Modal.getInstance(document.getElementById('domainAgeExceptionModal')).hide();
        renderDomainAgeExceptions();
        showToast('Domain age exception added successfully', 'success');
    }
    
    function removeDomainAgeException(exceptionId) {
        var excIndex = mockData.domainAgeExceptions.findIndex(function(e) { return e.id === exceptionId; });
        if (excIndex === -1) return;
        
        var removedExc = mockData.domainAgeExceptions[excIndex];
        mockData.domainAgeExceptions.splice(excIndex, 1);
        
        logAuditEvent('DOMAIN_AGE_EXCEPTION_REMOVED', {
            exceptionId: exceptionId,
            accountId: removedExc.accountId,
            accountName: removedExc.accountName
        });
        
        renderDomainAgeExceptions();
        showToast('Exception removed successfully', 'success');
    }
    
    function clearExceptionErrors() {
        document.querySelectorAll('#exception-form .is-invalid').forEach(function(el) {
            el.classList.remove('is-invalid');
        });
    }
    
    function setupUrlTabListeners() {
        document.getElementById('url-filter-status').addEventListener('change', renderUrlTab);
        document.getElementById('url-filter-matchtype').addEventListener('change', renderUrlTab);
        document.getElementById('url-filter-ruletype').addEventListener('change', renderUrlTab);
        document.getElementById('url-search').addEventListener('input', renderUrlTab);
        
        document.getElementById('domain-age-enabled').addEventListener('change', function() {
            document.getElementById('domain-age-hours').disabled = !this.checked;
            document.getElementById('domain-age-action').disabled = !this.checked;
        });
    }

    function renderNormTab() {
        var library = mockData.baseCharacterLibrary;
        
        var riskColors = {
            'high': { bg: '#fee2e2', color: '#991b1b', icon: 'fa-exclamation-triangle' },
            'medium': { bg: '#fef3c7', color: '#92400e', icon: 'fa-exclamation-circle' },
            'low': { bg: '#dbeafe', color: '#1e40af', icon: 'fa-info-circle' },
            'none': { bg: '#f3f4f6', color: '#6b7280', icon: 'fa-minus-circle' }
        };
        
        var scopeIcons = {
            'senderid': { icon: 'fa-id-badge', color: '#d97706', label: 'SenderID' },
            'content': { icon: 'fa-comment-alt', color: '#2563eb', label: 'Content' },
            'url': { icon: 'fa-link', color: '#7c3aed', label: 'URL' }
        };
        
        var uppercase = library.filter(function(c) { return c.type === 'uppercase'; });
        var lowercase = library.filter(function(c) { return c.type === 'lowercase'; });
        var digits = library.filter(function(c) { return c.type === 'digit'; });
        
        var enabledCount = library.filter(function(c) { return c.enabled; }).length;
        var disabledCount = library.filter(function(c) { return !c.enabled; }).length;
        var totalEquivalents = library.reduce(function(sum, c) { return sum + c.equivalents.length; }, 0);
        
        document.getElementById('norm-enabled-count').textContent = enabledCount;
        document.getElementById('norm-disabled-count').textContent = disabledCount;
        document.getElementById('norm-equivalents-count').textContent = totalEquivalents;
        document.getElementById('norm-base-count').textContent = library.length;
        
        renderBaseCharacterTable('uppercase', uppercase, riskColors, scopeIcons);
        renderBaseCharacterTable('lowercase', lowercase, riskColors, scopeIcons);
        renderBaseCharacterTable('digits', digits, riskColors, scopeIcons);
    }
    
    function renderBaseCharacterTable(type, characters, riskColors, scopeIcons) {
        var bodyId = type === 'digits' ? 'norm-digits-body' : 
                     type === 'lowercase' ? 'norm-lowercase-body' : 'norm-uppercase-body';
        var tbody = document.getElementById(bodyId);
        if (!tbody) return;
        
        tbody.innerHTML = characters.map(function(char) {
            var equivalentsHtml = '';
            if (char.equivalents.length > 0) {
                equivalentsHtml = char.equivalents.slice(0, 5).map(function(eq) {
                    return '<span class="equiv-chip">' + eq + '</span>';
                }).join('');
                if (char.equivalents.length > 5) {
                    equivalentsHtml += '<span class="text-muted ms-1" style="font-size: 0.7rem; cursor: pointer;" onclick="editBaseCharacter(\'' + char.base + '\')">+' + (char.equivalents.length - 5) + ' more</span>';
                }
            } else {
                equivalentsHtml = '<span class="text-muted" style="font-size: 0.75rem;">No equivalents</span>';
            }
            
            var scopePillsHtml = char.scope.map(function(s) {
                var labels = { senderid: 'SenderID', content: 'Content', url: 'URL' };
                return '<span class="norm-scope-pill ' + s + '">' + (labels[s] || s) + '</span>';
            }).join('');
            if (char.scope.length === 0) {
                scopePillsHtml = '<span class="text-muted" style="font-size: 0.7rem;">None</span>';
            }
            
            var updatedDate = char.updated || '28-01-2026';
            
            var dataAttrs = 'data-base="' + char.base + '" ' +
                'data-equivalents="' + char.equivalents.join(',') + '" ' +
                'data-scope="' + char.scope.join(',') + '" ' +
                'data-status="' + (char.enabled ? 'enabled' : 'disabled') + '" ' +
                'data-risk="' + char.risk + '" ' +
                'data-updated="' + updatedDate + '" ' +
                'data-notes="' + (char.notes || '').replace(/"/g, '&quot;') + '"';
            
            return '<tr class="expandable" ' + dataAttrs + ' onclick="toggleNormRowExpansion(this, event)">' +
                '<td>' +
                    '<span class="base-char-display">' + char.base + '</span>' +
                    (char.notes ? '<i class="fas fa-sticky-note ms-2 text-muted" style="font-size: 0.7rem;" title="' + char.notes + '"></i>' : '') +
                '</td>' +
                '<td>' + equivalentsHtml + '</td>' +
                '<td>' + scopePillsHtml + '</td>' +
                '<td>' +
                    '<span class="norm-status-pill ' + (char.enabled ? 'enabled' : 'disabled') + '">' +
                        (char.enabled ? 'Enabled' : 'Disabled') +
                    '</span>' +
                '</td>' +
                '<td>' +
                    '<span class="norm-risk-pill ' + char.risk + '">' + 
                        char.risk.charAt(0).toUpperCase() + char.risk.slice(1) +
                    '</span>' +
                '</td>' +
                '<td><span class="norm-updated-text">' + updatedDate + '</span></td>' +
                '<td>' +
                    '<div class="dropdown">' +
                        '<button class="action-menu-btn" data-bs-toggle="dropdown" aria-expanded="false"><i class="fas fa-ellipsis-v"></i></button>' +
                        '<ul class="dropdown-menu dropdown-menu-end">' +
                            '<li><a class="dropdown-item" href="javascript:void(0)" onclick="editBaseCharacter(\'' + char.base + '\')"><i class="fas fa-edit me-2 text-muted"></i>Edit Equivalents</a></li>' +
                            '<li><a class="dropdown-item" href="javascript:void(0)" onclick="testBaseCharacter(\'' + char.base + '\')"><i class="fas fa-flask me-2 text-muted"></i>Test</a></li>' +
                            '<li><a class="dropdown-item" href="javascript:void(0)" onclick="showNormRuleVersionHistory(\'' + char.base + '\')"><i class="fas fa-history me-2 text-muted"></i>Version History</a></li>' +
                            '<li><hr class="dropdown-divider"></li>' +
                            (char.enabled 
                                ? '<li><a class="dropdown-item" href="javascript:void(0)" onclick="toggleBaseCharacterStatus(\'' + char.base + '\', false)"><i class="fas fa-ban me-2 text-warning"></i>Disable</a></li>'
                                : '<li><a class="dropdown-item" href="javascript:void(0)" onclick="toggleBaseCharacterStatus(\'' + char.base + '\', true)"><i class="fas fa-check me-2 text-success"></i>Enable</a></li>') +
                        '</ul>' +
                    '</div>' +
                '</td>' +
            '</tr>';
        }).join('');
    }
    
    function filterBaseCharacters(type) {
        var statusFilter = document.getElementById('norm-filter-status-' + (type === 'uppercase' ? 'upper' : type === 'lowercase' ? 'lower' : 'digits')).value;
        var scopeFilter = document.getElementById('norm-filter-scope-' + (type === 'uppercase' ? 'upper' : type === 'lowercase' ? 'lower' : 'digits')).value;
        var riskFilter = document.getElementById('norm-filter-risk-' + (type === 'uppercase' ? 'upper' : type === 'lowercase' ? 'lower' : 'digits')).value;
        var searchText = document.getElementById('norm-search-' + (type === 'uppercase' ? 'upper' : type === 'lowercase' ? 'lower' : 'digits')).value.toLowerCase();
        
        var tableId = type === 'digits' ? 'norm-digits-table' : 
                      type === 'lowercase' ? 'norm-lowercase-table' : 'norm-uppercase-table';
        var rows = document.querySelectorAll('#' + tableId + ' tbody tr');
        
        rows.forEach(function(row) {
            var base = row.getAttribute('data-base');
            var char = mockData.baseCharacterLibrary.find(function(c) { return c.base === base; });
            if (!char) return;
            
            var show = true;
            
            if (statusFilter) {
                if (statusFilter === 'enabled' && !char.enabled) show = false;
                if (statusFilter === 'disabled' && char.enabled) show = false;
            }
            
            if (scopeFilter && char.scope.indexOf(scopeFilter) === -1) {
                show = false;
            }
            
            if (riskFilter && char.risk !== riskFilter) {
                show = false;
            }
            
            if (searchText && base.toLowerCase().indexOf(searchText) === -1) {
                show = false;
            }
            
            row.style.display = show ? '' : 'none';
        });
    }

    function renderQuarantineTab() {
        var tbody = document.getElementById('quarantine-body');
        var emptyState = document.getElementById('quarantine-empty-state');
        
        var statusFilter = document.getElementById('quarantine-filter-status').value;
        var ruleFilter = document.getElementById('quarantine-filter-rule').value;
        var urlFilter = document.getElementById('quarantine-filter-url').value;
        var accountFilter = document.getElementById('quarantine-filter-account').value;
        var searchTerm = document.getElementById('quarantine-search').value.toLowerCase();
        var tileFilter = getActiveTileFilter();
        
        var today = new Date();
        today.setHours(0, 0, 0, 0);
        
        var messages = mockData.quarantinedMessages.filter(function(msg) {
            if (tileFilter === 'pending' && msg.status !== 'pending') return false;
            if (tileFilter === 'released') {
                if (msg.status !== 'released') return false;
                if (msg.decisionAt) {
                    var decisionDate = new Date(msg.decisionAt.split(' ')[0].split('-').reverse().join('-'));
                    decisionDate.setHours(0, 0, 0, 0);
                    if (decisionDate.getTime() !== today.getTime()) return false;
                }
            }
            
            if (statusFilter && msg.status !== statusFilter) return false;
            if (ruleFilter && msg.ruleTriggered !== ruleFilter) return false;
            if (urlFilter === 'yes' && !msg.hasUrl) return false;
            if (urlFilter === 'no' && msg.hasUrl) return false;
            if (accountFilter && msg.accountId !== accountFilter) return false;
            if (searchTerm) {
                var searchFields = [msg.accountName, msg.senderId, msg.messageSnippet, msg.ruleName].join(' ').toLowerCase();
                if (searchFields.indexOf(searchTerm) === -1) return false;
            }
            return true;
        });

        var pendingCount = mockData.quarantinedMessages.filter(m => m.status === 'pending').length;
        var releasedTodayCount = mockData.quarantinedMessages.filter(function(m) {
            if (m.status !== 'released') return false;
            if (!m.decisionAt) return false;
            var decisionDate = new Date(m.decisionAt.split(' ')[0].split('-').reverse().join('-'));
            decisionDate.setHours(0, 0, 0, 0);
            return decisionDate.getTime() === today.getTime();
        }).length;
        
        document.getElementById('quarantine-pending-count').textContent = pendingCount;
        document.getElementById('quarantine-released-count').textContent = releasedTodayCount;
        
        populateAccountFilter();

        if (messages.length === 0) {
            tbody.innerHTML = '';
            emptyState.style.display = 'block';
            return;
        }

        emptyState.style.display = 'none';
        tbody.innerHTML = messages.map(function(msg) {
            var ruleTypeBadges = {
                'senderid': '<span class="sec-status-badge" style="background: #fef3c7; color: #92400e;"><i class="fas fa-id-badge me-1"></i>SenderID</span>',
                'content': '<span class="sec-status-badge" style="background: #dbeafe; color: #1e40af;"><i class="fas fa-comment me-1"></i>Content</span>',
                'url': '<span class="sec-status-badge" style="background: #f3e8ff; color: #6b21a8;"><i class="fas fa-link me-1"></i>URL</span>',
                'domain_age': '<span class="sec-status-badge" style="background: #fee2e2; color: #991b1b;"><i class="fas fa-clock me-1"></i>Domain Age</span>',
                'antispam': '<span class="sec-status-badge" style="background: #fce7f3; color: #9d174d;"><i class="fas fa-shield-virus me-1"></i>Anti-Spam</span>'
            };
            
            var ruleTriggeredHtml = (ruleTypeBadges[msg.ruleTriggered] || '<span class="sec-status-badge disabled">Unknown</span>') +
                '<br><small class="text-muted" title="Rule: ' + msg.ruleId + '">' + msg.ruleName + '</small>';
            
            if (msg.triggeredRules && msg.triggeredRules.length > 1) {
                ruleTriggeredHtml += '<br><small class="text-primary" style="cursor: pointer;" onclick="viewQuarantinedMessage(\'' + msg.id + '\')">+' + (msg.triggeredRules.length - 1) + ' more</small>';
            }
            
            var statusBadges = {
                'pending': '<span class="sec-status-badge pending"><i class="fas fa-clock me-1"></i>Pending</span>',
                'released': '<span class="sec-status-badge active"><i class="fas fa-check-circle me-1"></i>Released</span>',
                'blocked': '<span class="sec-status-badge blocked"><i class="fas fa-ban me-1"></i>Blocked</span>'
            };
            
            var urlBadge = msg.hasUrl 
                ? '<span class="sec-status-badge" style="background: #dcfce7; color: #166534;"><i class="fas fa-check me-1"></i>Yes</span>'
                : '<span class="sec-status-badge disabled"><i class="fas fa-times me-1"></i>No</span>';
            
            var subAccountDisplay = msg.subAccountName 
                ? '<span style="font-size: 0.75rem;">' + msg.subAccountName + '</span><br><small class="text-muted">' + msg.subAccountId + '</small>'
                : '<span class="text-muted">—</span>';
            
            var reviewerDisplay = msg.reviewer 
                ? '<span style="font-size: 0.75rem;">' + msg.reviewer.split('@')[0] + '</span>'
                : '<span class="text-muted">—</span>';
            
            var decisionDisplay = msg.decisionAt 
                ? '<span style="font-size: 0.75rem;">' + msg.decisionAt.split(' ')[0] + '</span>'
                : '<span class="text-muted">—</span>';
            
            var actionButtons = '';
            if (msg.status === 'pending') {
                actionButtons = '<div class="dropdown">' +
                    '<button class="action-menu-btn" data-bs-toggle="dropdown" aria-expanded="false"><i class="fas fa-ellipsis-v"></i></button>' +
                    '<ul class="dropdown-menu dropdown-menu-end">' +
                        '<li><a class="dropdown-item" href="javascript:void(0)" onclick="viewQuarantinedMessage(\'' + msg.id + '\')"><i class="fas fa-eye me-2 text-muted"></i>View Details</a></li>' +
                        '<li><hr class="dropdown-divider"></li>' +
                        '<li><a class="dropdown-item text-success" href="javascript:void(0)" onclick="releaseQuarantinedMessage(\'' + msg.id + '\')"><i class="fas fa-check-circle me-2"></i>Release Message</a></li>' +
                        '<li><a class="dropdown-item text-danger" href="javascript:void(0)" onclick="blockQuarantinedMessage(\'' + msg.id + '\')"><i class="fas fa-ban me-2"></i>Block Message</a></li>' +
                    '</ul>' +
                '</div>';
            } else {
                actionButtons = '<div class="dropdown">' +
                    '<button class="action-menu-btn" data-bs-toggle="dropdown" aria-expanded="false"><i class="fas fa-ellipsis-v"></i></button>' +
                    '<ul class="dropdown-menu dropdown-menu-end">' +
                        '<li><a class="dropdown-item" href="javascript:void(0)" onclick="viewQuarantinedMessage(\'' + msg.id + '\')"><i class="fas fa-eye me-2 text-muted"></i>View Details</a></li>' +
                    '</ul>' +
                '</div>';
            }
            
            return '<tr data-msg-id="' + msg.id + '">' +
                '<td><input type="checkbox" class="quarantine-checkbox" data-id="' + msg.id + '"' + (msg.status !== 'pending' ? ' disabled' : '') + '></td>' +
                '<td><span style="font-size: 0.75rem;">' + msg.timestamp + '</span></td>' +
                '<td><strong style="font-size: 0.8rem;">' + msg.accountName + '</strong><br><small class="text-muted">' + msg.accountId + '</small></td>' +
                '<td>' + subAccountDisplay + '</td>' +
                '<td><code style="font-size: 0.8rem; background: #f8f9fa; padding: 0.15rem 0.35rem; border-radius: 3px;">' + msg.senderId + '</code></td>' +
                '<td><span style="font-size: 0.8rem; max-width: 200px; display: inline-block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="' + msg.messageSnippet + '">' + msg.messageSnippet + '</span></td>' +
                '<td>' + urlBadge + '</td>' +
                '<td>' + ruleTriggeredHtml + '</td>' +
                '<td>' + statusBadges[msg.status] + '</td>' +
                '<td>' + reviewerDisplay + '</td>' +
                '<td>' + decisionDisplay + '</td>' +
                '<td>' + actionButtons + '</td>' +
                '</tr>';
        }).join('');
    }
    
    function populateAccountFilter() {
        var select = document.getElementById('quarantine-filter-account');
        var currentValue = select.value;
        var accounts = [];
        mockData.quarantinedMessages.forEach(function(msg) {
            if (accounts.indexOf(msg.accountId) === -1) {
                accounts.push(msg.accountId);
            }
        });
        
        var options = '<option value="">All Accounts</option>';
        accounts.forEach(function(accId) {
            var msg = mockData.quarantinedMessages.find(function(m) { return m.accountId === accId; });
            options += '<option value="' + accId + '">' + msg.accountName + '</option>';
        });
        select.innerHTML = options;
        select.value = currentValue;
    }
    
    var currentQuarantineMessageId = null;
    
    function viewQuarantinedMessage(msgId) {
        var msg = mockData.quarantinedMessages.find(function(m) { return m.id === msgId; });
        if (!msg) return;
        
        currentQuarantineMessageId = msgId;
        
        document.getElementById('qrn-view-id-header').textContent = msg.id;
        document.getElementById('qrn-view-id').textContent = msg.id;
        document.getElementById('qrn-view-timestamp').textContent = msg.timestamp;
        document.getElementById('qrn-view-account').textContent = msg.accountName + ' (' + msg.accountId + ')';
        document.getElementById('qrn-view-subaccount').textContent = msg.subAccountName ? msg.subAccountName + ' (' + msg.subAccountId + ')' : '—';
        document.getElementById('qrn-view-senderid').textContent = msg.senderId;
        document.getElementById('qrn-view-recipient').textContent = msg.recipient || '—';
        document.getElementById('qrn-view-hasurl').innerHTML = msg.hasUrl 
            ? '<span class="badge bg-info text-white" style="font-size: 0.6rem;"><i class="fas fa-link me-1"></i>URL</span>' 
            : '';
        
        document.getElementById('qrn-view-message').innerHTML = escapeHtml(msg.fullMessage || msg.messageSnippet);
        
        var statusBadge = msg.status === 'pending' 
            ? '<span class="badge bg-warning text-dark" style="font-size: 0.7rem;">Pending</span>'
            : msg.status === 'released'
                ? '<span class="badge bg-success" style="font-size: 0.7rem;">Released</span>'
                : '<span class="badge bg-danger" style="font-size: 0.7rem;">Blocked</span>';
        document.getElementById('qrn-view-status').innerHTML = statusBadge;
        document.getElementById('qrn-view-status-header').innerHTML = statusBadge;
        document.getElementById('qrn-view-reviewer').textContent = msg.reviewer || '—';
        document.getElementById('qrn-view-decisionat').textContent = msg.decisionAt || '—';
        
        var triggeredRulesHtml = '';
        var rulesCount = msg.triggeredRules ? msg.triggeredRules.length : 0;
        document.getElementById('qrn-rules-count').textContent = rulesCount;
        
        if (msg.triggeredRules && msg.triggeredRules.length > 0) {
            triggeredRulesHtml = '<div class="d-flex flex-column gap-2">';
            msg.triggeredRules.forEach(function(rule, idx) {
                var engineColor = rule.engine === 'SenderIdEnforcementEngine' ? '#6b21a8' 
                    : rule.engine === 'MessageContentEngine' ? '#1e3a5f'
                    : rule.engine === 'UrlEnforcementEngine' ? '#0d6efd'
                    : '#dc3545';
                var engineLabel = rule.engine === 'SenderIdEnforcementEngine' ? 'SenderID' 
                    : rule.engine === 'MessageContentEngine' ? 'Content'
                    : rule.engine === 'UrlEnforcementEngine' ? 'URL'
                    : 'Other';
                
                // Determine what to display as the blocked value
                var blockedValue = rule.matchedValue || '';
                if (rule.engine === 'UrlEnforcementEngine' && msg.extractedUrls && msg.extractedUrls.length > 0) {
                    blockedValue = msg.extractedUrls[0];
                } else if (rule.engine === 'SenderIdEnforcementEngine') {
                    blockedValue = msg.senderId || rule.matchedValue;
                }
                
                triggeredRulesHtml += '<div class="triggered-rule-item" style="background: #f8f9fa; border-radius: 6px; padding: 0.5rem; border-left: 3px solid ' + engineColor + ';">';
                triggeredRulesHtml += '<div class="d-flex align-items-center gap-2 mb-1">';
                triggeredRulesHtml += '<span class="badge" style="background: ' + engineColor + '; font-size: 0.65rem;">' + engineLabel + '</span>';
                triggeredRulesHtml += '<span style="font-size: 0.75rem; font-weight: 600; color: #333;">' + escapeHtml(rule.ruleName) + '</span>';
                triggeredRulesHtml += '</div>';
                triggeredRulesHtml += '<div style="font-size: 0.7rem; color: #666; margin-bottom: 0.25rem;">Matched Pattern: <code style="background: #fff3cd; color: #856404; padding: 0.1rem 0.3rem; border-radius: 3px; font-size: 0.65rem;">' + escapeHtml(rule.matchedValue) + '</code></div>';
                triggeredRulesHtml += '<div style="font-size: 0.7rem; color: #dc3545; font-weight: 500;"><i class="fas fa-ban me-1"></i>Blocked: <span style="font-family: monospace; background: #f8d7da; padding: 0.1rem 0.3rem; border-radius: 3px;">' + escapeHtml(blockedValue) + '</span></div>';
                triggeredRulesHtml += '</div>';
            });
            triggeredRulesHtml += '</div>';
        } else {
            triggeredRulesHtml = '<span class="text-muted" style="font-size: 0.65rem;">No rules triggered</span>';
        }
        document.getElementById('qrn-view-triggered-rules').innerHTML = triggeredRulesHtml;
        
        var rawMetadata = {
            id: msg.id,
            idempotencyKey: msg.idempotencyKey,
            timestamp: msg.timestamp,
            accountId: msg.accountId,
            subAccountId: msg.subAccountId || null,
            senderId: msg.senderId,
            recipient: msg.recipient,
            hasUrl: msg.hasUrl,
            ruleTriggered: msg.ruleTriggered,
            triggeredRules: msg.triggeredRules ? msg.triggeredRules.map(function(r) { return r.ruleId; }) : [],
            status: msg.status,
            reviewer: msg.reviewer || null,
            decisionAt: msg.decisionAt || null
        };
        document.getElementById('qrn-view-raw-json').textContent = JSON.stringify(rawMetadata, null, 2);
        
        var routingHtml = msg.routingInfo 
            ? '<table class="table table-sm table-borderless mb-0" style="font-size: 0.7rem;">' +
              '<tr><td><strong>Supplier:</strong></td><td>' + (msg.routingInfo.supplier || '—') + '</td></tr>' +
              '<tr><td><strong>Route:</strong></td><td>' + (msg.routingInfo.route || '—') + '</td></tr>' +
              '<tr><td><strong>Cost:</strong></td><td>' + (msg.routingInfo.cost || '—') + '</td></tr>' +
              '</table>'
            : '<span class="text-muted" style="font-size: 0.7rem;">No routing info (message blocked before routing)</span>';
        document.getElementById('qrn-view-routing').innerHTML = routingHtml;
        
        var normHtml = '';
        if (msg.normalisedValues) {
            normHtml = '<table class="table table-sm table-borderless mb-0" style="font-size: 0.75rem;">';
            if (msg.normalisedValues.senderId !== msg.normalisedValues.senderIdNormalised) {
                normHtml += '<tr><td style="width: 90px;"><strong>SenderID:</strong></td><td><code>' + msg.normalisedValues.senderId + '</code> → <code>' + msg.normalisedValues.senderIdNormalised + '</code></td></tr>';
            } else {
                normHtml += '<tr><td style="width: 90px;"><strong>SenderID:</strong></td><td><code>' + msg.normalisedValues.senderIdNormalised + '</code> <span class="text-muted">(unchanged)</span></td></tr>';
            }
            normHtml += '<tr><td colspan="2"><strong>Message (normalised):</strong></td></tr>';
            normHtml += '<tr><td colspan="2" style="background: #f8f9fa; padding: 0.5rem; border-radius: 4px; word-break: break-word;">' + escapeHtml(msg.normalisedValues.messageNormalised) + '</td></tr>';
            normHtml += '</table>';
        } else {
            normHtml = '<span class="text-muted" style="font-size: 0.8rem;">Normalisation not applied</span>';
        }
        document.getElementById('qrn-view-normalised').innerHTML = normHtml;
        
        var notesHtml = '';
        if (msg.notes && msg.notes.length > 0) {
            msg.notes.forEach(function(note) {
                notesHtml += '<div class="mb-1 p-1 bg-light rounded" style="border-left: 2px solid #1e3a5f; font-size: 0.65rem;">' +
                    '<span class="text-muted">' + note.timestamp.split(' ')[0] + ' ' + note.author.split('@')[0] + ':</span> ' +
                    escapeHtml(note.text) + '</div>';
            });
        } else {
            notesHtml = '<span class="text-muted" style="font-size: 0.65rem;">No notes</span>';
        }
        document.getElementById('qrn-view-notes-list').innerHTML = notesHtml;
        document.getElementById('qrn-new-note').value = '';
        
        var addNoteSection = document.getElementById('qrn-add-note-section');
        addNoteSection.style.display = 'block';
        
        var notifySection = document.getElementById('qrn-notify-customer-section');
        var notifyCheckbox = document.getElementById('qrn-notify-customer');
        if (msg.status === 'pending' && mockData.quarantineFeatureFlags.notifyCustomerAdminOnRelease) {
            notifySection.style.display = 'block';
            notifyCheckbox.checked = false;
        } else {
            notifySection.style.display = 'none';
        }
        
        var actionsDiv = document.getElementById('qrn-view-actions');
        if (msg.status === 'pending') {
            var actionsHtml = '<button class="btn btn-success btn-sm" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;" onclick="releaseQuarantinedMessageFromModal()">' +
                '<i class="fas fa-paper-plane me-1"></i>Release</button> ' +
                '<button class="btn btn-danger btn-sm" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;" onclick="blockQuarantinedMessageFromModal()">' +
                '<i class="fas fa-ban me-1"></i>Block</button> ';
            
            if (mockData.quarantineFeatureFlags.allowAddExceptionFromQuarantine) {
                actionsHtml += '<button class="btn btn-outline-primary btn-sm" style="font-size: 0.7rem; padding: 0.2rem 0.4rem;" onclick="addExceptionFromQuarantine()">' +
                    '<i class="fas fa-shield-alt me-1"></i>Exception</button> ';
            }
            if (mockData.quarantineFeatureFlags.allowCreateRuleFromQuarantine) {
                actionsHtml += '<button class="btn btn-outline-secondary btn-sm" style="font-size: 0.7rem; padding: 0.2rem 0.4rem;" onclick="createRuleFromQuarantine()">' +
                    '<i class="fas fa-plus me-1"></i>Rule</button>';
            }
            actionsDiv.innerHTML = actionsHtml;
        } else {
            actionsDiv.innerHTML = '<span class="badge bg-secondary" style="font-size: 0.65rem;"><i class="fas fa-check-circle me-1"></i>Reviewed</span> ' +
                '<span class="text-muted" style="font-size: 0.7rem;">' + msg.decisionAt + ' by ' + (msg.reviewer ? msg.reviewer.split('@')[0] : 'System') + '</span>';
        }
        
        var modal = new bootstrap.Modal(document.getElementById('quarantineViewModal'));
        modal.show();
    }
    
    function escapeHtml(text) {
        if (!text) return '';
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    function releaseQuarantinedMessageFromModal() {
        if (!currentQuarantineMessageId) return;
        var notifyCustomer = document.getElementById('qrn-notify-customer').checked;
        releaseQuarantinedMessage(currentQuarantineMessageId, notifyCustomer);
        bootstrap.Modal.getInstance(document.getElementById('quarantineViewModal')).hide();
    }
    
    function blockQuarantinedMessageFromModal() {
        if (!currentQuarantineMessageId) return;
        blockQuarantinedMessage(currentQuarantineMessageId);
        bootstrap.Modal.getInstance(document.getElementById('quarantineViewModal')).hide();
    }
    
    function copyQuarantineMessage() {
        var messageEl = document.getElementById('qrn-view-message');
        if (!messageEl) return;
        
        var messageText = messageEl.textContent || messageEl.innerText;
        if (!messageText) {
            showToast('No message content to copy', 'warning');
            return;
        }
        
        navigator.clipboard.writeText(messageText).then(function() {
            showToast('Message copied to clipboard', 'success');
        }).catch(function() {
            var textArea = document.createElement('textarea');
            textArea.value = messageText;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            showToast('Message copied to clipboard', 'success');
        });
    }
    
    var messageExpanded = false;
    function toggleMessageExpand() {
        var msgBox = document.querySelector('#qrn-view-message').parentElement;
        var btn = document.querySelector('#qrn-message-expand button');
        if (!msgBox || !btn) return;
        
        messageExpanded = !messageExpanded;
        if (messageExpanded) {
            msgBox.style.maxHeight = '150px';
            btn.textContent = 'Show less';
        } else {
            msgBox.style.maxHeight = '55px';
            btn.textContent = 'Show more';
        }
    }
    
    function addQuarantineNote() {
        if (!currentQuarantineMessageId) return;
        var noteText = document.getElementById('qrn-new-note').value.trim();
        if (!noteText) {
            showToast('Please enter a note', 'warning');
            return;
        }
        
        var msg = mockData.quarantinedMessages.find(function(m) { return m.id === currentQuarantineMessageId; });
        if (!msg) return;
        
        if (!msg.notes) msg.notes = [];
        msg.notes.push({
            author: currentAdmin.email,
            timestamp: formatDateTime(new Date()),
            text: noteText
        });
        
        logAuditEvent('QUARANTINE_NOTE_ADDED', {
            messageId: currentQuarantineMessageId,
            accountId: msg.accountId,
            note: noteText,
            admin: currentAdmin.email
        });
        
        viewQuarantinedMessage(currentQuarantineMessageId);
        showToast('Note added', 'success');
    }
    
    function addExceptionFromQuarantine() {
        if (!currentQuarantineMessageId) return;
        var msg = mockData.quarantinedMessages.find(function(m) { return m.id === currentQuarantineMessageId; });
        if (!msg) return;
        
        bootstrap.Modal.getInstance(document.getElementById('quarantineViewModal')).hide();
        
        var exceptionType = msg.ruleTriggered;
        if (exceptionType === 'senderid') {
            showToast('TODO: Deep-link to SenderID exceptions with account=' + msg.accountId + ', senderId=' + msg.senderId, 'info');
        } else if (exceptionType === 'content') {
            showToast('TODO: Deep-link to Content exceptions with account=' + msg.accountId, 'info');
        } else if (exceptionType === 'url' || exceptionType === 'domain_age') {
            document.getElementById('exception-account').value = msg.accountId;
            var modal = new bootstrap.Modal(document.getElementById('domainAgeExceptionModal'));
            modal.show();
        }
        
        logAuditEvent('QUARANTINE_EXCEPTION_STARTED', {
            messageId: currentQuarantineMessageId,
            accountId: msg.accountId,
            ruleType: exceptionType,
            admin: currentAdmin.email
        });
    }
    
    function createRuleFromQuarantine() {
        if (!currentQuarantineMessageId) return;
        var msg = mockData.quarantinedMessages.find(function(m) { return m.id === currentQuarantineMessageId; });
        if (!msg) return;
        
        bootstrap.Modal.getInstance(document.getElementById('quarantineViewModal')).hide();
        
        if (msg.ruleTriggered === 'content' || msg.extractedUrls.length === 0) {
            document.querySelector('#contentRuleModal [data-content-type="keyword"]').click();
            document.getElementById('content-rule-value').value = msg.fullMessage ? msg.fullMessage.substring(0, 50) : '';
            var modal = new bootstrap.Modal(document.getElementById('contentRuleModal'));
            modal.show();
        } else {
            document.getElementById('url-pattern').value = msg.extractedUrls[0] || '';
            var modal = new bootstrap.Modal(document.getElementById('urlRuleModal'));
            modal.show();
        }
        
        logAuditEvent('QUARANTINE_RULE_CREATE_STARTED', {
            messageId: currentQuarantineMessageId,
            accountId: msg.accountId,
            prefilledFrom: msg.ruleTriggered,
            admin: currentAdmin.email
        });
        
        showToast('Rule form prefilled from quarantine data', 'info');
    }
    
    function releaseQuarantinedMessage(msgId, notifyCustomer) {
        var msg = mockData.quarantinedMessages.find(function(m) { return m.id === msgId; });
        if (!msg) return;
        
        if (msg.status !== 'pending') {
            console.log('[Quarantine] Idempotency check: Message ' + msgId + ' already processed (status: ' + msg.status + ')');
            showToast('This message has already been reviewed', 'warning');
            return;
        }
        
        if (msg.releaseAttempts > 0) {
            console.log('[Quarantine] Idempotency warning: Release already attempted for ' + msgId + ' (attempts: ' + msg.releaseAttempts + ')');
        }
        msg.releaseAttempts = (msg.releaseAttempts || 0) + 1;
        
        msg.status = 'released';
        msg.reviewer = currentAdmin.email;
        msg.decisionAt = formatDateTime(new Date());
        
        logAuditEvent('QUARANTINE_MESSAGE_RELEASED', {
            messageId: msgId,
            idempotencyKey: msg.idempotencyKey,
            accountId: msg.accountId,
            accountName: msg.accountName,
            senderId: msg.senderId,
            recipient: msg.recipient,
            ruleTriggered: msg.ruleTriggered,
            triggeredRules: msg.triggeredRules.map(function(r) { return r.ruleId; }),
            reviewer: currentAdmin.email,
            notifyCustomer: notifyCustomer || false,
            releaseAttempts: msg.releaseAttempts
        });
        
        if (notifyCustomer && mockData.quarantineFeatureFlags.notifyCustomerAdminOnRelease) {
            console.log('[Quarantine] TODO: Send notification to customer admin for account ' + msg.accountId);
            logAuditEvent('QUARANTINE_CUSTOMER_NOTIFIED', {
                messageId: msgId,
                accountId: msg.accountId,
                notificationType: 'message_released'
            });
        }
        
        console.log('[Quarantine] Message ' + msgId + ' released - resuming delivery pipeline');
        
        renderQuarantineTab();
        showToast('Message released for delivery' + (notifyCustomer ? ' (customer notified)' : ''), 'success');
    }
    
    function blockQuarantinedMessage(msgId) {
        var msg = mockData.quarantinedMessages.find(function(m) { return m.id === msgId; });
        if (!msg) return;
        
        if (msg.status !== 'pending') {
            console.log('[Quarantine] Idempotency check: Message ' + msgId + ' already processed (status: ' + msg.status + ')');
            showToast('This message has already been reviewed', 'warning');
            return;
        }
        
        msg.status = 'blocked';
        msg.reviewer = currentAdmin.email;
        msg.decisionAt = formatDateTime(new Date());
        
        logAuditEvent('QUARANTINE_MESSAGE_BLOCKED', {
            messageId: msgId,
            idempotencyKey: msg.idempotencyKey,
            accountId: msg.accountId,
            accountName: msg.accountName,
            senderId: msg.senderId,
            recipient: msg.recipient,
            ruleTriggered: msg.ruleTriggered,
            triggeredRules: msg.triggeredRules ? msg.triggeredRules.map(function(r) { return r.ruleId; }) : [],
            reviewer: currentAdmin.email,
            permanent: true
        });
        
        console.log('[Quarantine] Message ' + msgId + ' permanently blocked');
        
        renderQuarantineTab();
        showToast('Message permanently blocked', 'success');
    }
    
    var pendingBulkAction = null;
    var pendingBulkIds = [];
    
    function updateBulkActionButtons() {
        var selectedCount = getSelectedQuarantineIds().length;
        var blockBtn = document.getElementById('btn-bulk-block');
        var releaseBtn = document.getElementById('btn-bulk-release');
        
        if (blockBtn) {
            blockBtn.disabled = selectedCount === 0;
            blockBtn.style.opacity = selectedCount === 0 ? '0.65' : '1';
        }
        if (releaseBtn) {
            releaseBtn.disabled = selectedCount === 0;
            releaseBtn.style.opacity = selectedCount === 0 ? '0.65' : '1';
        }
    }
    
    function showBulkReleaseConfirmation() {
        var selectedIds = getSelectedQuarantineIds();
        if (selectedIds.length === 0) return;
        
        pendingBulkAction = 'release';
        pendingBulkIds = selectedIds;
        
        document.getElementById('bulk-confirm-header').style.background = '#f0f4f8';
        document.getElementById('bulk-confirm-icon').className = 'fas fa-check-circle me-2';
        document.getElementById('bulk-confirm-icon').style.color = '#1e3a5f';
        document.getElementById('bulk-confirm-title-text').textContent = 'Release Messages';
        document.getElementById('bulk-confirm-message').textContent = 'Are you sure you want to release the selected messages? They will be allowed through for delivery.';
        document.getElementById('bulk-confirm-count').textContent = selectedIds.length;
        document.getElementById('bulk-confirm-btn').className = 'btn btn-sm btn-primary';
        document.getElementById('bulk-confirm-btn').style.background = '#1e3a5f';
        document.getElementById('bulk-confirm-btn').style.borderColor = '#1e3a5f';
        document.getElementById('bulk-confirm-btn').innerHTML = '<i class="fas fa-check me-1"></i> Release';
        
        var modal = new bootstrap.Modal(document.getElementById('bulkActionConfirmModal'));
        modal.show();
    }
    
    function showBulkBlockConfirmation() {
        var selectedIds = getSelectedQuarantineIds();
        if (selectedIds.length === 0) return;
        
        pendingBulkAction = 'block';
        pendingBulkIds = selectedIds;
        
        document.getElementById('bulk-confirm-header').style.background = '#fef2f2';
        document.getElementById('bulk-confirm-icon').className = 'fas fa-ban me-2';
        document.getElementById('bulk-confirm-icon').style.color = '#dc3545';
        document.getElementById('bulk-confirm-title-text').textContent = 'Block Messages';
        document.getElementById('bulk-confirm-message').textContent = 'Are you sure you want to permanently block the selected messages? They will be rejected and not delivered.';
        document.getElementById('bulk-confirm-count').textContent = selectedIds.length;
        document.getElementById('bulk-confirm-btn').className = 'btn btn-sm btn-danger';
        document.getElementById('bulk-confirm-btn').style.background = '';
        document.getElementById('bulk-confirm-btn').style.borderColor = '';
        document.getElementById('bulk-confirm-btn').innerHTML = '<i class="fas fa-ban me-1"></i> Block';
        
        var modal = new bootstrap.Modal(document.getElementById('bulkActionConfirmModal'));
        modal.show();
    }
    
    function executeBulkAction() {
        if (!pendingBulkAction || pendingBulkIds.length === 0) return;
        
        var successCount = 0;
        var failedIds = [];
        
        pendingBulkIds.forEach(function(msgId) {
            try {
                if (pendingBulkAction === 'release') {
                    releaseQuarantinedMessage(msgId, false);
                    logAdminAuditEvent('QUARANTINE_BULK_RELEASE', { messageId: msgId });
                } else if (pendingBulkAction === 'block') {
                    blockQuarantinedMessage(msgId);
                    logAdminAuditEvent('QUARANTINE_BULK_BLOCK', { messageId: msgId });
                }
                successCount++;
            } catch (e) {
                failedIds.push(msgId);
                console.error('[Quarantine] Failed to process message:', msgId, e);
            }
        });
        
        bootstrap.Modal.getInstance(document.getElementById('bulkActionConfirmModal')).hide();
        
        if (failedIds.length > 0) {
            showToast(successCount + ' processed, ' + failedIds.length + ' failed. Selection kept.', 'warning');
        } else {
            var actionText = pendingBulkAction === 'release' ? 'released' : 'blocked';
            showToast(successCount + ' message(s) ' + actionText + ' successfully', 'success');
            
            document.getElementById('quarantine-select-all').checked = false;
            updateBulkActionButtons();
        }
        
        logAdminAuditEvent('QUARANTINE_BULK_ACTION_COMPLETE', { 
            action: pendingBulkAction.toUpperCase(),
            totalSelected: pendingBulkIds.length,
            successCount: successCount,
            failedCount: failedIds.length
        });
        
        pendingBulkAction = null;
        pendingBulkIds = [];
        
        renderQuarantineTab();
    }
    
    function logAdminAuditEvent(eventType, details) {
        var auditEntry = {
            timestamp: new Date().toISOString(),
            eventType: eventType,
            adminId: currentAdmin ? currentAdmin.id : 'unknown',
            adminEmail: currentAdmin ? currentAdmin.email : 'unknown',
            details: details,
            sourceIP: 'internal',
            userAgent: navigator.userAgent
        };
        console.log('[AdminAudit]', JSON.stringify(auditEntry));
    }
    
    function bulkReleaseQuarantine() {
        showBulkReleaseConfirmation();
    }
    
    function bulkBlockQuarantine() {
        showBulkBlockConfirmation();
    }
    
    function getSelectedQuarantineIds() {
        var checkboxes = document.querySelectorAll('.quarantine-checkbox:checked');
        var ids = [];
        checkboxes.forEach(function(cb) {
            ids.push(cb.dataset.id);
        });
        return ids;
    }
    
    function setupQuarantineTabListeners() {
        document.getElementById('quarantine-search').addEventListener('input', function() {
            renderQuarantineTab();
            renderActiveFilterChips();
        });
    }
    
    function toggleQuarantineFilterPanel() {
        var panel = document.getElementById('quarantine-filter-panel');
        var btn = document.getElementById('quarantine-filter-btn');
        
        if (!panel || !btn) return;
        
        if (panel.style.display === 'none' || panel.style.display === '') {
            panel.style.display = 'block';
            btn.classList.add('active');
        } else {
            panel.style.display = 'none';
            btn.classList.remove('active');
        }
    }
    
    function applyQuarantineFilters() {
        var filterCount = 0;
        var status = document.getElementById('quarantine-filter-status').value;
        var rule = document.getElementById('quarantine-filter-rule').value;
        var url = document.getElementById('quarantine-filter-url').value;
        var account = document.getElementById('quarantine-filter-account').value;
        
        if (status) filterCount++;
        if (rule) filterCount++;
        if (url) filterCount++;
        if (account) filterCount++;
        
        var badge = document.getElementById('quarantine-filter-count');
        if (filterCount > 0) {
            badge.textContent = filterCount;
            badge.style.display = 'inline-flex';
        } else {
            badge.style.display = 'none';
        }
        
        renderQuarantineTab();
        renderActiveFilterChips();
        
        toggleQuarantineFilterPanel();
    }
    
    function resetQuarantineFilters() {
        document.getElementById('quarantine-filter-status').value = '';
        document.getElementById('quarantine-filter-rule').value = '';
        document.getElementById('quarantine-filter-url').value = '';
        document.getElementById('quarantine-filter-account').value = '';
        
        var badge = document.getElementById('quarantine-filter-count');
        badge.style.display = 'none';
        
        renderQuarantineTab();
        renderActiveFilterChips();
    }
    
    function clearAllQuarantineFilters() {
        document.getElementById('quarantine-filter-status').value = '';
        document.getElementById('quarantine-filter-rule').value = '';
        document.getElementById('quarantine-filter-url').value = '';
        document.getElementById('quarantine-filter-account').value = '';
        document.getElementById('quarantine-search').value = '';
        
        activeTileFilter = null;
        document.getElementById('tile-awaiting-review').classList.remove('selected');
        document.getElementById('tile-released-today').classList.remove('selected');
        
        var badge = document.getElementById('quarantine-filter-count');
        badge.style.display = 'none';
        
        renderQuarantineTab();
        renderActiveFilterChips();
    }
    
    function removeQuarantineFilter(filterType, value) {
        if (filterType === 'search') {
            document.getElementById('quarantine-search').value = '';
        } else if (filterType === 'status') {
            document.getElementById('quarantine-filter-status').value = '';
        } else if (filterType === 'rule') {
            document.getElementById('quarantine-filter-rule').value = '';
        } else if (filterType === 'url') {
            document.getElementById('quarantine-filter-url').value = '';
        } else if (filterType === 'account') {
            document.getElementById('quarantine-filter-account').value = '';
        } else if (filterType === 'tile') {
            activeTileFilter = null;
            document.getElementById('tile-awaiting-review').classList.remove('selected');
            document.getElementById('tile-released-today').classList.remove('selected');
        }
        
        var filterCount = 0;
        if (document.getElementById('quarantine-filter-status').value) filterCount++;
        if (document.getElementById('quarantine-filter-rule').value) filterCount++;
        if (document.getElementById('quarantine-filter-url').value) filterCount++;
        if (document.getElementById('quarantine-filter-account').value) filterCount++;
        
        var badge = document.getElementById('quarantine-filter-count');
        if (filterCount > 0) {
            badge.textContent = filterCount;
            badge.style.display = 'inline-flex';
        } else {
            badge.style.display = 'none';
        }
        
        renderQuarantineTab();
        renderActiveFilterChips();
    }
    
    function renderActiveFilterChips() {
        var container = document.getElementById('quarantine-active-filters');
        var chipsContainer = document.getElementById('quarantine-filter-chips');
        chipsContainer.innerHTML = '';
        
        var hasFilters = false;
        var searchTerm = document.getElementById('quarantine-search').value.trim();
        var status = document.getElementById('quarantine-filter-status').value;
        var rule = document.getElementById('quarantine-filter-rule').value;
        var url = document.getElementById('quarantine-filter-url').value;
        var account = document.getElementById('quarantine-filter-account').value;
        
        if (activeTileFilter) {
            hasFilters = true;
            var tileLabel = activeTileFilter === 'pending' ? 'Awaiting Review' : 'Released Today';
            chipsContainer.innerHTML += '<span class="filter-chip" style="background: rgba(30, 58, 95, 0.2);"><span class="chip-label">View:</span>' + tileLabel + '<span class="remove-chip" onclick="removeQuarantineFilter(\'tile\')"><i class="fas fa-times"></i></span></span>';
        }
        
        if (searchTerm) {
            hasFilters = true;
            chipsContainer.innerHTML += '<span class="filter-chip"><span class="chip-label">Search:</span>' + escapeHtml(searchTerm.substring(0, 20)) + (searchTerm.length > 20 ? '...' : '') + '<span class="remove-chip" onclick="removeQuarantineFilter(\'search\')"><i class="fas fa-times"></i></span></span>';
        }
        
        if (status) {
            hasFilters = true;
            var statusLabels = { 'pending': 'Pending', 'released': 'Released', 'blocked': 'Blocked' };
            chipsContainer.innerHTML += '<span class="filter-chip"><span class="chip-label">Status:</span>' + (statusLabels[status] || status) + '<span class="remove-chip" onclick="removeQuarantineFilter(\'status\')"><i class="fas fa-times"></i></span></span>';
        }
        
        if (rule) {
            hasFilters = true;
            var ruleLabels = { 'senderid': 'SenderID', 'content': 'Content', 'url': 'URL', 'domain_age': 'Domain Age', 'antispam': 'Anti-Spam' };
            chipsContainer.innerHTML += '<span class="filter-chip"><span class="chip-label">Rule:</span>' + (ruleLabels[rule] || rule) + '<span class="remove-chip" onclick="removeQuarantineFilter(\'rule\')"><i class="fas fa-times"></i></span></span>';
        }
        
        if (url) {
            hasFilters = true;
            var urlLabels = { 'yes': 'Has URL', 'no': 'No URL' };
            chipsContainer.innerHTML += '<span class="filter-chip"><span class="chip-label">URL:</span>' + (urlLabels[url] || url) + '<span class="remove-chip" onclick="removeQuarantineFilter(\'url\')"><i class="fas fa-times"></i></span></span>';
        }
        
        if (account) {
            hasFilters = true;
            var msg = mockData.quarantinedMessages.find(function(m) { return m.accountId === account; });
            var accountLabel = msg ? msg.accountName : account;
            chipsContainer.innerHTML += '<span class="filter-chip"><span class="chip-label">Account:</span>' + accountLabel.substring(0, 15) + (accountLabel.length > 15 ? '...' : '') + '<span class="remove-chip" onclick="removeQuarantineFilter(\'account\')"><i class="fas fa-times"></i></span></span>';
        }
        
        if (hasFilters) {
            container.classList.add('has-filters');
        } else {
            container.classList.remove('has-filters');
        }
    }
    
    var activeTileFilter = null;
    
    function toggleQuarantineTileFilter(filterType) {
        var pendingTile = document.getElementById('tile-awaiting-review');
        var releasedTile = document.getElementById('tile-released-today');
        
        if (activeTileFilter === filterType) {
            activeTileFilter = null;
            pendingTile.classList.remove('selected');
            releasedTile.classList.remove('selected');
        } else {
            activeTileFilter = filterType;
            pendingTile.classList.remove('selected');
            releasedTile.classList.remove('selected');
            
            if (filterType === 'pending') {
                pendingTile.classList.add('selected');
            } else if (filterType === 'released') {
                releasedTile.classList.add('selected');
            }
        }
        
        renderQuarantineTab();
        renderActiveFilterChips();
    }
    
    function getActiveTileFilter() {
        return activeTileFilter;
    }

    function setupEventListeners() {
        document.getElementById('quarantine-select-all').addEventListener('change', function() {
            var checkboxes = document.querySelectorAll('.quarantine-checkbox');
            checkboxes.forEach(function(cb) {
                cb.checked = this.checked;
            }.bind(this));
            updateBulkActionButtons();
        });
        
        document.getElementById('quarantine-body').addEventListener('change', function(e) {
            if (e.target.classList.contains('quarantine-checkbox')) {
                updateBulkActionButtons();
                
                var allCheckboxes = document.querySelectorAll('.quarantine-checkbox');
                var checkedCheckboxes = document.querySelectorAll('.quarantine-checkbox:checked');
                document.getElementById('quarantine-select-all').checked = 
                    allCheckboxes.length > 0 && allCheckboxes.length === checkedCheckboxes.length;
            }
        });
        
        setupContentTabListeners();
        setupUrlTabListeners();
        setupQuarantineTabListeners();
        setupNormTableSorting();
        
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.action-menu-container')) {
                document.querySelectorAll('.action-menu-dropdown').forEach(function(menu) {
                    menu.classList.remove('show');
                });
            }
        });
    }
    
    function setupNormTableSorting() {
        var tables = ['norm-uppercase-table', 'norm-lowercase-table', 'norm-digits-table'];
        tables.forEach(function(tableId) {
            var table = document.getElementById(tableId);
            if (!table) return;
            
            var headers = table.querySelectorAll('thead th.sortable');
            headers.forEach(function(th) {
                th.addEventListener('click', function() {
                    var sortKey = th.getAttribute('data-sort');
                    var currentDir = th.classList.contains('sort-asc') ? 'asc' : 
                                     th.classList.contains('sort-desc') ? 'desc' : null;
                    
                    table.querySelectorAll('thead th').forEach(function(h) {
                        h.classList.remove('sort-asc', 'sort-desc');
                        var icon = h.querySelector('.sort-icon');
                        if (icon) {
                            icon.classList.remove('fa-sort-up', 'fa-sort-down');
                            icon.classList.add('fa-sort');
                        }
                    });
                    
                    var newDir = currentDir === 'asc' ? 'desc' : 'asc';
                    th.classList.add('sort-' + newDir);
                    var icon = th.querySelector('.sort-icon');
                    if (icon) {
                        icon.classList.remove('fa-sort');
                        icon.classList.add(newDir === 'asc' ? 'fa-sort-up' : 'fa-sort-down');
                    }
                    
                    sortNormTable(tableId, sortKey, newDir);
                });
            });
        });
    }
    
    function sortNormTable(tableId, sortKey, direction) {
        var table = document.getElementById(tableId);
        if (!table) return;
        
        var tbody = table.querySelector('tbody');
        var rows = Array.from(tbody.querySelectorAll('tr'));
        
        rows.sort(function(a, b) {
            var aVal, bVal;
            
            switch (sortKey) {
                case 'base':
                    aVal = a.getAttribute('data-base') || '';
                    bVal = b.getAttribute('data-base') || '';
                    break;
                case 'scope':
                    aVal = (a.getAttribute('data-scope') || '').split(',').length;
                    bVal = (b.getAttribute('data-scope') || '').split(',').length;
                    return direction === 'asc' ? aVal - bVal : bVal - aVal;
                case 'status':
                    aVal = a.getAttribute('data-status') || '';
                    bVal = b.getAttribute('data-status') || '';
                    break;
                case 'risk':
                    var riskOrder = { 'high': 3, 'medium': 2, 'low': 1, 'none': 0 };
                    aVal = riskOrder[a.getAttribute('data-risk')] || 0;
                    bVal = riskOrder[b.getAttribute('data-risk')] || 0;
                    return direction === 'asc' ? aVal - bVal : bVal - aVal;
                case 'updated':
                    aVal = a.getAttribute('data-updated') || '';
                    bVal = b.getAttribute('data-updated') || '';
                    break;
                default:
                    aVal = '';
                    bVal = '';
            }
            
            if (typeof aVal === 'string') {
                return direction === 'asc' ? aVal.localeCompare(bVal) : bVal.localeCompare(aVal);
            }
            return 0;
        });
        
        rows.forEach(function(row) {
            tbody.appendChild(row);
        });
    }

    function deleteContentRuleById(ruleId) {
        var ruleIndex = mockData.contentRules.findIndex(function(r) { return r.id === ruleId; });
        if (ruleIndex === -1) return;
        
        var deletedRule = mockData.contentRules[ruleIndex];
        mockData.contentRules.splice(ruleIndex, 1);
        
        logAuditEvent('CONTENT_RULE_DELETED', {
            ruleId: ruleId,
            ruleName: deletedRule.name,
            deletedRule: deletedRule
        });
        
        showToast('Content rule deleted successfully', 'success');
    }
    
    function setupContentTabListeners() {
        document.getElementById('content-filter-status').addEventListener('change', renderContentTab);
        document.getElementById('content-filter-matchtype').addEventListener('change', renderContentTab);
        document.getElementById('content-filter-ruletype').addEventListener('change', renderContentTab);
        document.getElementById('content-search').addEventListener('input', renderContentTab);
        
        renderAntiSpamControls();
    }
    
    function renderAntiSpamControls() {
        var settings = mockData.antiSpamSettings;
        document.getElementById('antispam-repeat-toggle').checked = settings.preventRepeatContent;
        document.getElementById('antispam-window').value = settings.windowHours;
        document.getElementById('antispam-window').disabled = !settings.preventRepeatContent;
        
        var statusEl = document.getElementById('antispam-status');
        if (settings.preventRepeatContent) {
            statusEl.innerHTML = '<span class="badge bg-success" style="font-size: 0.75rem;"><i class="fas fa-toggle-on me-1"></i> Enabled (' + settings.windowHours + 'h window)</span>';
        } else {
            statusEl.innerHTML = '<span class="badge bg-secondary" style="font-size: 0.75rem;"><i class="fas fa-toggle-off me-1"></i> Disabled</span>';
        }
    }
    
    function toggleAntiSpamRepeat() {
        var enabled = document.getElementById('antispam-repeat-toggle').checked;
        mockData.antiSpamSettings.preventRepeatContent = enabled;
        mockData.antiSpamSettings.lastUpdated = formatDateTime(new Date());
        mockData.antiSpamSettings.updatedBy = currentAdmin.email;
        
        document.getElementById('antispam-window').disabled = !enabled;
        
        logAuditEvent('ANTISPAM_REPEAT_CONTENT_TOGGLED', {
            enabled: enabled,
            windowHours: mockData.antiSpamSettings.windowHours,
            admin: currentAdmin.email
        });
        
        if (window.MessageEnforcementService) {
            window.MessageEnforcementService.updateAntiSpamSettings({
                preventRepeatContent: enabled,
                windowHours: mockData.antiSpamSettings.windowHours
            });
        }
        
        renderAntiSpamControls();
        showToast(enabled ? 'Anti-spam repeat content protection enabled' : 'Anti-spam repeat content protection disabled', enabled ? 'success' : 'info');
    }
    
    function updateAntiSpamWindow() {
        var windowHours = parseInt(document.getElementById('antispam-window').value, 10);
        mockData.antiSpamSettings.windowHours = windowHours;
        mockData.antiSpamSettings.lastUpdated = formatDateTime(new Date());
        mockData.antiSpamSettings.updatedBy = currentAdmin.email;
        
        logAuditEvent('ANTISPAM_WINDOW_UPDATED', {
            windowHours: windowHours,
            admin: currentAdmin.email
        });
        
        if (window.MessageEnforcementService) {
            window.MessageEnforcementService.updateAntiSpamSettings({
                preventRepeatContent: mockData.antiSpamSettings.preventRepeatContent,
                windowHours: windowHours
            });
        }
        
        renderAntiSpamControls();
        showToast('Anti-spam window updated to ' + windowHours + ' hours', 'success');
    }

    return {
        initialize: initialize,
        renderAllTabs: renderAllTabs,
        showAddContentRuleModal: showAddContentRuleModal,
        editContentRule: editContentRule,
        viewContentRule: viewContentRule,
        toggleContentRuleStatus: toggleContentRuleStatus,
        deleteContentRule: deleteContentRule,
        deleteContentRuleById: deleteContentRuleById,
        saveContentRule: saveContentRule,
        updateContentMatchInputLabel: updateContentMatchInputLabel,
        resetContentFilters: resetContentFilters,
        toggleContentActionMenu: toggleContentActionMenu,
        setupContentTabListeners: setupContentTabListeners,
        toggleAntiSpamRepeat: toggleAntiSpamRepeat,
        updateAntiSpamWindow: updateAntiSpamWindow,
        renderAntiSpamControls: renderAntiSpamControls,
        showAddUrlRuleModal: showAddUrlRuleModal,
        editUrlRule: editUrlRule,
        viewUrlRule: viewUrlRule,
        toggleUrlRuleStatus: toggleUrlRuleStatus,
        deleteUrlRule: deleteUrlRule,
        deleteUrlRuleById: deleteUrlRuleById,
        saveUrlRule: saveUrlRule,
        updateUrlPatternLabel: updateUrlPatternLabel,
        resetUrlFilters: resetUrlFilters,
        toggleUrlActionMenu: toggleUrlActionMenu,
        setupUrlTabListeners: setupUrlTabListeners,
        saveDomainAgeSettings: saveDomainAgeSettings,
        showAddDomainAgeExceptionModal: showAddDomainAgeExceptionModal,
        saveException: saveException,
        removeDomainAgeException: removeDomainAgeException,
        viewQuarantinedMessage: viewQuarantinedMessage,
        releaseQuarantinedMessage: releaseQuarantinedMessage,
        blockQuarantinedMessage: blockQuarantinedMessage,
        bulkReleaseQuarantine: bulkReleaseQuarantine,
        bulkBlockQuarantine: bulkBlockQuarantine,
        setupQuarantineTabListeners: setupQuarantineTabListeners,
        addQuarantineNote: addQuarantineNote,
        copyQuarantineMessage: copyQuarantineMessage,
        toggleMessageExpand: toggleMessageExpand,
        addExceptionFromQuarantine: addExceptionFromQuarantine,
        createRuleFromQuarantine: createRuleFromQuarantine,
        releaseQuarantinedMessageFromModal: releaseQuarantinedMessageFromModal,
        blockQuarantinedMessageFromModal: blockQuarantinedMessageFromModal,
        toggleQuarantineFilterPanel: toggleQuarantineFilterPanel,
        applyQuarantineFilters: applyQuarantineFilters,
        resetQuarantineFilters: resetQuarantineFilters,
        clearAllQuarantineFilters: clearAllQuarantineFilters,
        removeQuarantineFilter: removeQuarantineFilter,
        renderActiveFilterChips: renderActiveFilterChips,
        toggleQuarantineTileFilter: toggleQuarantineTileFilter,
        getActiveTileFilter: getActiveTileFilter,
        showBulkReleaseConfirmation: showBulkReleaseConfirmation,
        showBulkBlockConfirmation: showBulkBlockConfirmation,
        executeBulkAction: executeBulkAction,
        updateBulkActionButtons: updateBulkActionButtons
    };
})();

function refreshAllControls() {
    console.log('[SecurityComplianceControls] Refreshing all controls...');
    SecurityComplianceControlsService.renderAllTabs();
}

function editBaseCharacter(base) {
    showNormRuleModal(base);
}

var GSM7_CHARS = '@£$¥èéùìòÇ\nØø\rÅåΔ_ΦΓΛΩΠΨΣΘΞ !"#¤%&\'()*+,-./0123456789:;<=>?¡ABCDEFGHIJKLMNOPQRSTUVWXYZ' +
    'ÄÖÑÜabcdefghijklmnopqrstuvwxyzäöñüà';
var GSM7_EXTENDED = '^{}\\[~]|€';
var MAX_EQUIVALENTS = 25;

function isGSM7Char(char) {
    return GSM7_CHARS.indexOf(char) !== -1 || GSM7_EXTENDED.indexOf(char) !== -1;
}

function getCharEncoding(char) {
    if (isGSM7Char(char)) return 'GSM-7';
    return 'Unicode';
}

function buildEquivChipHtml(char) {
    var encoding = getCharEncoding(char);
    var codepoint = char.codePointAt(0).toString(16).toUpperCase().padStart(4, '0');
    var encodingClass = encoding === 'GSM-7' ? 'norm-equiv-gsm' : 'norm-equiv-unicode';
    var escapedChar = char.replace(/'/g, "\\'").replace(/"/g, '&quot;');
    
    return '<span class="norm-equiv-tag" data-char="' + escapedChar + '">' +
        '<span class="norm-equiv-char">' + char + '</span>' +
        '<span class="norm-equiv-encoding ' + encodingClass + '">' + encoding + '</span>' +
        '<i class="fas fa-times norm-equiv-remove" onclick="removeEquivFromModal(\'' + escapedChar + '\')"></i>' +
    '</span>';
}

function showNormRuleModal(base) {
    var isEdit = !!base;
    var char = isEdit ? mockData.baseCharacterLibrary.find(function(c) { return c.base === base; }) : null;
    
    var title = isEdit 
        ? '<i class="fas fa-edit me-2"></i>Edit Normalisation Rule: <code style="background: rgba(255,255,255,0.2); padding: 4px 8px; border-radius: 4px; font-size: 1.1rem;">' + base + '</code>'
        : '<i class="fas fa-plus me-2"></i>Add Normalisation Rule';
    
    var charOptions = mockData.baseCharacterLibrary.map(function(c) {
        var selected = (isEdit && c.base === base) ? ' selected' : '';
        return '<option value="' + c.base + '"' + selected + '>' + c.base + ' (' + c.type + ')</option>';
    }).join('');
    
    var existingEquivalents = char ? char.equivalents : [];
    var existingScope = char ? char.scope : ['senderid', 'content'];
    var existingNotes = char ? (char.notes || '') : '';
    var existingEnabled = char ? char.enabled : true;
    var hasUrlScope = existingScope.indexOf('url') !== -1;
    
    var equivalentsChipsHtml = existingEquivalents.map(function(eq) {
        return buildEquivChipHtml(eq);
    }).join('');
    
    var charPickerHtml = buildCharPickerHtml();
    
    var modalHtml = '<div class="modal fade" id="normRuleModal" tabindex="-1">' +
        '<div class="modal-dialog modal-lg">' +
            '<div class="modal-content">' +
                '<div class="modal-header" style="background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%); color: white;">' +
                    '<h5 class="modal-title">' + title + '</h5>' +
                    '<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>' +
                '</div>' +
                '<div class="modal-body">' +
                    (isEdit ? '<div class="alert alert-info mb-3" style="background: #e8f4fd; border: 1px solid #1e3a5f; border-radius: 8px;">' +
                        '<i class="fas fa-lock me-2" style="color: #1e3a5f;"></i><strong>Fixed Base Character:</strong> Base characters cannot be changed or deleted.' +
                    '</div>' : '') +
                    
                    '<div class="mb-4">' +
                        '<label class="form-label fw-bold"><i class="fas fa-font me-2 text-muted"></i>Base Character</label>' +
                        '<select class="form-select" id="normRuleBase" ' + (isEdit ? 'disabled' : '') + ' onchange="updateNormRuleBaseContext()" style="' + (isEdit ? 'background: #f8f9fa; font-size: 1.1rem; font-family: monospace;' : 'font-size: 1.1rem; font-family: monospace;') + '">' +
                            charOptions +
                        '</select>' +
                        (isEdit ? '<input type="hidden" id="normRuleBaseHidden" value="' + base + '">' : '') +
                        '<small class="text-muted">The base character that equivalents will map to</small>' +
                    '</div>' +
                    
                    '<div class="mb-4">' +
                        '<label class="form-label fw-bold"><i class="fas fa-bullseye me-2 text-muted"></i>Applies To</label>' +
                        '<div class="d-flex gap-2 flex-wrap">' +
                            '<button type="button" class="btn norm-scope-toggle ' + (existingScope.indexOf('senderid') !== -1 ? 'active' : '') + '" data-scope="senderid" onclick="toggleNormScope(this)">' +
                                '<i class="fas fa-id-badge me-1"></i>SenderID' +
                            '</button>' +
                            '<button type="button" class="btn norm-scope-toggle ' + (existingScope.indexOf('content') !== -1 ? 'active' : '') + '" data-scope="content" onclick="toggleNormScope(this)">' +
                                '<i class="fas fa-comment-alt me-1"></i>Content' +
                            '</button>' +
                            '<button type="button" class="btn norm-scope-toggle ' + (existingScope.indexOf('url') !== -1 ? 'active' : '') + '" data-scope="url" onclick="toggleNormScope(this)">' +
                                '<i class="fas fa-link me-1"></i>URL' +
                            '</button>' +
                        '</div>' +
                        '<div id="normUrlWarning" class="alert alert-warning mt-2 mb-0" style="display: ' + (hasUrlScope ? 'block' : 'none') + '; background: #fff3cd; border: 1px solid #ffc107; border-radius: 8px;">' +
                            '<i class="fas fa-exclamation-triangle me-2" style="color: #856404;"></i>' +
                            '<strong>Warning:</strong> URL normalisation can cause broad matches and false positives.' +
                        '</div>' +
                        '<small class="text-muted mt-2 d-block">Select which engines will use this normalisation rule</small>' +
                    '</div>' +
                    
                    '<div class="mb-4">' +
                        '<label class="form-label fw-bold"><i class="fas fa-equals me-2 text-muted"></i>Equivalents</label>' +
                        '<div class="norm-equiv-container" id="normEquivContainer">' +
                            '<div class="norm-equiv-tags" id="normEquivTags">' + equivalentsChipsHtml + '</div>' +
                            '<div class="norm-equiv-input-wrap">' +
                                '<input type="text" class="norm-equiv-input" id="normEquivInput" placeholder="Type or paste characters..." onkeydown="handleEquivInput(event)" onpaste="handleEquivPaste(event)">' +
                            '</div>' +
                        '</div>' +
                        '<div class="d-flex justify-content-between align-items-center mt-2">' +
                            '<div>' +
                                '<button type="button" class="btn btn-sm btn-outline-secondary me-2" onclick="toggleCharPicker()">' +
                                    '<i class="fas fa-keyboard me-1"></i>Character Picker' +
                                '</button>' +
                                '<small class="text-muted">Press Enter to add. Paste to add multiple.</small>' +
                            '</div>' +
                            '<span class="badge" id="normEquivCount" style="background: ' + (existingEquivalents.length >= MAX_EQUIVALENTS ? '#dc3545' : '#6c757d') + ';">' + 
                                existingEquivalents.length + '/' + MAX_EQUIVALENTS + '</span>' +
                        '</div>' +
                        '<div id="normCharPicker" class="norm-char-picker" style="display: none;">' +
                            charPickerHtml +
                        '</div>' +
                        '<div id="normEquivError" class="text-danger mt-2" style="display: none; font-size: 0.85rem;"></div>' +
                    '</div>' +
                    
                    '<div class="mb-4">' +
                        '<label class="form-label fw-bold"><i class="fas fa-sticky-note me-2 text-muted"></i>Notes <span class="text-muted fw-normal">(optional)</span></label>' +
                        '<textarea class="form-control" id="normRuleNotes" rows="2" placeholder="Add notes about this mapping, e.g., &quot;Greek capital Alpha - commonly used in phishing&quot;">' + existingNotes.replace(/"/g, '&quot;') + '</textarea>' +
                    '</div>' +
                    
                    '<div class="mb-3">' +
                        '<label class="form-label fw-bold"><i class="fas fa-toggle-on me-2 text-muted"></i>Status</label>' +
                        '<div class="form-check form-switch">' +
                            '<input class="form-check-input" type="checkbox" id="normRuleEnabled" ' + (existingEnabled ? 'checked' : '') + ' style="width: 3rem; height: 1.5rem;">' +
                            '<label class="form-check-label ms-2" for="normRuleEnabled" id="normRuleStatusLabel">' + (existingEnabled ? 'Enabled' : 'Disabled') + '</label>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
                '<div class="modal-footer">' +
                    '<small class="text-muted me-auto"><i class="fas fa-shield-alt me-1"></i>Changes logged to audit trail</small>' +
                    '<button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>' +
                    '<button type="button" class="btn" onclick="saveNormRule(\'' + (base || '') + '\')" style="background: #1e3a5f; border-color: #1e3a5f; color: white;">' +
                        '<i class="fas fa-save me-1"></i>Save' +
                    '</button>' +
                '</div>' +
            '</div>' +
        '</div>' +
    '</div>';
    
    var existingModal = document.getElementById('normRuleModal');
    if (existingModal) existingModal.remove();
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    document.getElementById('normRuleEnabled').addEventListener('change', function() {
        document.getElementById('normRuleStatusLabel').textContent = this.checked ? 'Enabled' : 'Disabled';
    });
    
    var modal = new bootstrap.Modal(document.getElementById('normRuleModal'));
    modal.show();
}

function toggleNormScope(btn) {
    btn.classList.toggle('active');
    var urlBtn = document.querySelector('.norm-scope-toggle[data-scope="url"]');
    var urlWarning = document.getElementById('normUrlWarning');
    if (urlWarning && urlBtn) {
        urlWarning.style.display = urlBtn.classList.contains('active') ? 'block' : 'none';
    }
}

function updateNormRuleBaseContext() {
    var baseSelect = document.getElementById('normRuleBase');
    if (baseSelect) {
        window.currentNormRuleBase = baseSelect.value;
    }
}

function buildCharPickerHtml() {
    var gsm7Special = ['@', '£', '$', '¥', 'è', 'é', 'ù', 'ì', 'ò', 'Ç', 'Ø', 'ø', 'Å', 'å', 'Δ', 'Φ', 'Γ', 'Λ', 'Ω', 'Π', 'Ψ', 'Σ', 'Θ', 'Ξ', '¡', '¤', 'Ä', 'Ö', 'Ñ', 'Ü', 'ä', 'ö', 'ñ', 'ü', 'à', '§'];
    var gsm7Extended = ['^', '{', '}', '\\', '[', '~', ']', '|', '€'];
    var accented = ['À', 'Á', 'Â', 'Ã', 'Ā', 'Ă', 'Ą', 'Ć', 'Ĉ', 'Ċ', 'Č', 'Ď', 'Đ', 'È', 'É', 'Ê', 'Ë', 'Ē', 'Ĕ', 'Ė', 'Ę', 'Ě', 'Ĝ', 'Ğ', 'Ġ', 'Ģ', 'Ĥ', 'Ħ', 'Ì', 'Í', 'Î', 'Ï', 'Ĩ', 'Ī', 'Ĭ', 'Į', 'İ', 'Ĵ', 'Ķ', 'Ĺ', 'Ļ', 'Ľ', 'Ŀ', 'Ł', 'Ń', 'Ņ', 'Ň', 'Ŋ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ō', 'Ŏ', 'Ő', 'Œ', 'Ŕ', 'Ŗ', 'Ř', 'Ś', 'Ŝ', 'Ş', 'Š', 'Ţ', 'Ť', 'Ŧ', 'Ù', 'Ú', 'Û', 'Ũ', 'Ū', 'Ŭ', 'Ů', 'Ű', 'Ų', 'Ŵ', 'Ŷ', 'Ÿ', 'Ź', 'Ż', 'Ž'];
    var lookalikes = ['Α', 'Β', 'Ε', 'Ζ', 'Η', 'Ι', 'Κ', 'Μ', 'Ν', 'Ο', 'Ρ', 'Τ', 'Υ', 'Χ', 'А', 'В', 'С', 'Е', 'Н', 'К', 'М', 'О', 'Р', 'Т', 'Х', 'Ａ', 'Ｂ', 'Ｃ', 'Ｄ', 'Ｅ', 'Ｆ', 'Ｇ', 'Ｈ', 'Ｉ', 'Ｊ', 'Ｋ', 'Ｌ', 'Ｍ', 'Ｎ', 'Ｏ', 'Ｐ', 'Ｑ', 'Ｒ', 'Ｓ', 'Ｔ', 'Ｕ', 'Ｖ', 'Ｗ', 'Ｘ', 'Ｙ', 'Ｚ'];
    
    var html = '<div class="norm-char-picker-header">' +
        '<span class="norm-char-picker-title"><i class="fas fa-keyboard me-1"></i>Character Picker</span>' +
        '<button type="button" class="btn-close btn-close-white" onclick="toggleCharPicker()" style="font-size: 0.6rem;"></button>' +
    '</div>';
    
    html += '<div class="norm-char-picker-section">' +
        '<div class="norm-char-picker-label"><span class="badge bg-success me-1">GSM-7</span>Special Characters</div>' +
        '<div class="norm-char-picker-grid">';
    gsm7Special.forEach(function(c) {
        html += '<button type="button" class="norm-char-btn" onclick="addCharFromPicker(\'' + c.replace(/'/g, "\\'") + '\')" title="U+' + c.charCodeAt(0).toString(16).toUpperCase().padStart(4, '0') + '">' + c + '</button>';
    });
    html += '</div></div>';
    
    html += '<div class="norm-char-picker-section">' +
        '<div class="norm-char-picker-label"><span class="badge bg-success me-1">GSM-7</span>Extended</div>' +
        '<div class="norm-char-picker-grid">';
    gsm7Extended.forEach(function(c) {
        html += '<button type="button" class="norm-char-btn" onclick="addCharFromPicker(\'' + c.replace(/'/g, "\\'").replace(/\\/g, '\\\\') + '\')" title="U+' + c.charCodeAt(0).toString(16).toUpperCase().padStart(4, '0') + '">' + c + '</button>';
    });
    html += '</div></div>';
    
    html += '<div class="norm-char-picker-section">' +
        '<div class="norm-char-picker-label"><span class="badge bg-primary me-1">Unicode</span>Accented Characters</div>' +
        '<div class="norm-char-picker-grid">';
    accented.forEach(function(c) {
        html += '<button type="button" class="norm-char-btn" onclick="addCharFromPicker(\'' + c + '\')" title="U+' + c.charCodeAt(0).toString(16).toUpperCase().padStart(4, '0') + '">' + c + '</button>';
    });
    html += '</div></div>';
    
    html += '<div class="norm-char-picker-section">' +
        '<div class="norm-char-picker-label"><span class="badge bg-warning text-dark me-1">Lookalike</span>Greek / Cyrillic / Fullwidth</div>' +
        '<div class="norm-char-picker-grid">';
    lookalikes.forEach(function(c) {
        html += '<button type="button" class="norm-char-btn" onclick="addCharFromPicker(\'' + c + '\')" title="U+' + c.charCodeAt(0).toString(16).toUpperCase().padStart(4, '0') + '">' + c + '</button>';
    });
    html += '</div></div>';
    
    return html;
}

function toggleCharPicker() {
    var picker = document.getElementById('normCharPicker');
    if (picker) {
        picker.style.display = picker.style.display === 'none' ? 'block' : 'none';
    }
}

function addCharFromPicker(char) {
    var result = addEquivCharacter(char);
    if (result.success) {
        document.getElementById('normEquivInput').focus();
    }
}

function validateEquivChar(char) {
    var errors = [];
    
    if (!char || char.trim().length === 0) {
        errors.push('Whitespace-only characters are not allowed');
        return { valid: false, errors: errors };
    }
    
    if (/[\*\?\+\[\]\(\)\{\}\^\$\.\|\\]/.test(char) && char.length > 1) {
        errors.push('Regex/wildcard patterns are not allowed');
        return { valid: false, errors: errors };
    }
    
    var baseSelect = document.getElementById('normRuleBase');
    var baseHidden = document.getElementById('normRuleBaseHidden');
    var baseChar = baseHidden ? baseHidden.value : (baseSelect ? baseSelect.value : null);
    
    if (baseChar && char === baseChar) {
        errors.push('Base character cannot be added as its own equivalent');
        return { valid: false, errors: errors };
    }
    
    var currentCount = document.querySelectorAll('#normEquivTags .norm-equiv-tag').length;
    if (currentCount >= MAX_EQUIVALENTS) {
        errors.push('Maximum ' + MAX_EQUIVALENTS + ' equivalents per base character');
        return { valid: false, errors: errors };
    }
    
    var existingTags = document.querySelectorAll('#normEquivTags .norm-equiv-tag');
    var exists = false;
    existingTags.forEach(function(tag) {
        if (tag.getAttribute('data-char') === char) exists = true;
    });
    if (exists) {
        return { valid: false, errors: ['Character already added'], silent: true };
    }
    
    return { valid: true, errors: [] };
}

function addEquivCharacter(char) {
    var validation = validateEquivChar(char);
    if (!validation.valid) {
        if (!validation.silent) {
            showEquivError(validation.errors[0]);
        }
        return { success: false, error: validation.errors[0] };
    }
    
    hideEquivError();
    var tagsContainer = document.getElementById('normEquivTags');
    var tagHtml = buildEquivChipHtml(char);
    tagsContainer.insertAdjacentHTML('beforeend', tagHtml);
    updateEquivCount();
    return { success: true };
}

function handleEquivInput(event) {
    if (event.key === 'Enter') {
        event.preventDefault();
        var input = document.getElementById('normEquivInput');
        var value = input.value;
        
        if (value.length === 0) return;
        
        var chars = Array.from(value);
        var added = 0;
        chars.forEach(function(char) {
            if (char.trim().length > 0) {
                var result = addEquivCharacter(char);
                if (result.success) added++;
            }
        });
        
        input.value = '';
        if (added > 0) {
            hideEquivError();
        }
    }
}

function handleEquivPaste(event) {
    event.preventDefault();
    var pastedText = (event.clipboardData || window.clipboardData).getData('text');
    if (!pastedText) return;
    
    var chars = Array.from(pastedText);
    var uniqueChars = [];
    chars.forEach(function(char) {
        if (char.trim().length > 0 && uniqueChars.indexOf(char) === -1) {
            uniqueChars.push(char);
        }
    });
    
    var added = 0;
    var errors = [];
    uniqueChars.forEach(function(char) {
        var result = addEquivCharacter(char);
        if (result.success) {
            added++;
        } else if (result.error && errors.indexOf(result.error) === -1) {
            errors.push(result.error);
        }
    });
    
    if (added > 0) {
        showToast('Added ' + added + ' character(s)', 'success');
    }
    if (errors.length > 0 && added === 0) {
        showEquivError(errors[0]);
    }
}

function removeEquivFromModal(char) {
    var tagsContainer = document.getElementById('normEquivTags');
    var tags = tagsContainer.querySelectorAll('.norm-equiv-tag');
    tags.forEach(function(tag) {
        if (tag.getAttribute('data-char') === char) {
            tag.remove();
        }
    });
    updateEquivCount();
    hideEquivError();
}

function updateEquivCount() {
    var count = document.querySelectorAll('#normEquivTags .norm-equiv-tag').length;
    var countBadge = document.getElementById('normEquivCount');
    if (countBadge) {
        countBadge.textContent = count + '/' + MAX_EQUIVALENTS;
        countBadge.style.background = count >= MAX_EQUIVALENTS ? '#dc3545' : '#6c757d';
    }
}

function showEquivError(msg) {
    var errorDiv = document.getElementById('normEquivError');
    if (errorDiv) {
        errorDiv.innerHTML = '<i class="fas fa-exclamation-circle me-1"></i>' + msg;
        errorDiv.style.display = 'block';
    }
}

function hideEquivError() {
    var errorDiv = document.getElementById('normEquivError');
    if (errorDiv) {
        errorDiv.style.display = 'none';
    }
}

function saveNormRule(originalBase) {
    var isEdit = !!originalBase;
    var base = isEdit ? originalBase : document.getElementById('normRuleBase').value;
    
    var char = mockData.baseCharacterLibrary.find(function(c) { return c.base === base; });
    if (!char) return;
    
    var scopeButtons = document.querySelectorAll('.norm-scope-toggle.active');
    var scope = [];
    scopeButtons.forEach(function(btn) {
        scope.push(btn.getAttribute('data-scope'));
    });
    if (scope.length === 0) scope = ['senderid'];
    
    var equivTags = document.querySelectorAll('#normEquivTags .norm-equiv-tag');
    var equivalents = [];
    equivTags.forEach(function(tag) {
        equivalents.push(tag.getAttribute('data-char'));
    });
    
    var notes = document.getElementById('normRuleNotes').value.trim();
    var enabled = document.getElementById('normRuleEnabled').checked;
    
    var pendingChange = {
        base: base,
        equivalents: equivalents,
        scope: scope,
        notes: notes,
        enabled: enabled,
        isEdit: isEdit
    };
    
    var newRisk = computeRisk({ equivalents: equivalents, scope: scope });
    var oldRisk = char.risk || 'none';
    
    if (newRisk === 'high' && oldRisk !== 'high') {
        showHighRiskConfirmModal(pendingChange);
        return;
    }
    
    executeSaveNormRule(pendingChange);
}

function showHighRiskConfirmModal(pendingChange) {
    var reasons = [];
    if (pendingChange.scope.indexOf('url') !== -1) {
        reasons.push('URL normalisation is enabled (can cause broad matches and false positives)');
    }
    var digitCount = pendingChange.equivalents.filter(function(eq) { return /[0-9]/.test(eq); }).length;
    var hasPunctuation = pendingChange.equivalents.some(function(eq) { return /[!@#$%^&*(),.?":{}|<>]/.test(eq); });
    if (digitCount >= 2 && hasPunctuation) {
        reasons.push('Contains multiple digit equivalents with punctuation');
    }
    
    var reasonsHtml = reasons.map(function(r) {
        return '<li class="mb-1"><i class="fas fa-exclamation-circle text-danger me-2"></i>' + r + '</li>';
    }).join('');
    
    var modalHtml = '<div class="modal fade" id="highRiskConfirmModal" tabindex="-1" data-bs-backdrop="static">' +
        '<div class="modal-dialog">' +
            '<div class="modal-content">' +
                '<div class="modal-header" style="background: #dc2626; color: white;">' +
                    '<h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>High Risk Change</h5>' +
                '</div>' +
                '<div class="modal-body">' +
                    '<div class="alert alert-danger" style="background: #fee2e2; border-color: #fecaca;">' +
                        '<strong><i class="fas fa-shield-alt me-2"></i>This change is classified as HIGH RISK</strong>' +
                    '</div>' +
                    '<p class="mb-2"><strong>Base Character:</strong> <code style="background: #f3f4f6; padding: 2px 6px; border-radius: 4px;">' + pendingChange.base + '</code></p>' +
                    '<p class="mb-2"><strong>Risk Factors:</strong></p>' +
                    '<ul class="mb-3" style="padding-left: 1.5rem;">' + reasonsHtml + '</ul>' +
                    '<div class="alert alert-warning mb-3" style="background: #fef3c7; border-color: #fcd34d;">' +
                        '<i class="fas fa-info-circle me-2"></i>High-risk rules may cause unintended message blocks or broad matching. Please review carefully before confirming.' +
                    '</div>' +
                    '<div class="mb-3">' +
                        '<label class="form-label fw-bold">Type <code style="background: #fee2e2; color: #991b1b; padding: 2px 6px; border-radius: 4px;">CONFIRM</code> to proceed:</label>' +
                        '<input type="text" class="form-control" id="highRiskConfirmInput" placeholder="Type CONFIRM here..." autocomplete="off">' +
                    '</div>' +
                '</div>' +
                '<div class="modal-footer">' +
                    '<button type="button" class="btn btn-light" data-bs-dismiss="modal" onclick="cancelHighRiskChange()">Cancel</button>' +
                    '<button type="button" class="btn btn-danger" id="highRiskConfirmBtn" onclick="confirmHighRiskChange()" disabled>' +
                        '<i class="fas fa-check me-1"></i>Confirm High Risk Change' +
                    '</button>' +
                '</div>' +
            '</div>' +
        '</div>' +
    '</div>';
    
    var existingModal = document.getElementById('highRiskConfirmModal');
    if (existingModal) existingModal.remove();
    
    window.pendingHighRiskChange = pendingChange;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    var confirmInput = document.getElementById('highRiskConfirmInput');
    var confirmBtn = document.getElementById('highRiskConfirmBtn');
    
    confirmInput.addEventListener('input', function() {
        confirmBtn.disabled = this.value.trim().toUpperCase() !== 'CONFIRM';
    });
    
    var normRuleModal = bootstrap.Modal.getInstance(document.getElementById('normRuleModal'));
    if (normRuleModal) normRuleModal.hide();
    
    var modal = new bootstrap.Modal(document.getElementById('highRiskConfirmModal'));
    modal.show();
    
    confirmInput.focus();
}

function confirmHighRiskChange() {
    var input = document.getElementById('highRiskConfirmInput');
    if (input.value.trim().toUpperCase() !== 'CONFIRM') {
        return;
    }
    
    var modal = bootstrap.Modal.getInstance(document.getElementById('highRiskConfirmModal'));
    modal.hide();
    
    if (window.pendingHighRiskChange) {
        executeSaveNormRule(window.pendingHighRiskChange);
        window.pendingHighRiskChange = null;
    }
}

function cancelHighRiskChange() {
    window.pendingHighRiskChange = null;
}

window.normRuleVersionHistory = window.normRuleVersionHistory || {};

function createNormRuleVersion(base, beforeState, afterState, action) {
    if (!window.normRuleVersionHistory[base]) {
        window.normRuleVersionHistory[base] = [];
    }
    
    var version = {
        id: 'v' + Date.now(),
        version: window.normRuleVersionHistory[base].length + 1,
        timestamp: new Date().toISOString(),
        action: action || 'update',
        actor: 'admin@quicksms.co.uk',
        before: JSON.parse(JSON.stringify(beforeState)),
        after: JSON.parse(JSON.stringify(afterState))
    };
    
    window.normRuleVersionHistory[base].push(version);
    
    return version;
}

function getNormRuleVersions(base) {
    return window.normRuleVersionHistory[base] || [];
}

function getLatestNormRuleVersion(base) {
    var versions = getNormRuleVersions(base);
    return versions.length > 0 ? versions[versions.length - 1] : null;
}

function executeSaveNormRule(changeData) {
    var char = mockData.baseCharacterLibrary.find(function(c) { return c.base === changeData.base; });
    if (!char) return;
    
    var beforeState = {
        equivalents: char.equivalents.slice(),
        scope: char.scope.slice(),
        notes: char.notes,
        enabled: char.enabled,
        risk: char.risk || 'none'
    };
    
    char.equivalents = NormalisationRulesConfig.deduplicateEquivalents(changeData.equivalents);
    char.scope = changeData.scope;
    char.notes = changeData.notes;
    char.enabled = changeData.enabled;
    char.updated = new Date().toLocaleDateString('en-GB').replace(/\//g, '-');
    char.risk = computeRisk(char);
    
    var afterState = {
        equivalents: char.equivalents.slice(),
        scope: char.scope.slice(),
        notes: char.notes,
        enabled: char.enabled,
        risk: char.risk
    };
    
    var version = createNormRuleVersion(changeData.base, beforeState, afterState, 'update');
    char.currentVersion = version.version;
    
    logAuditEvent('NORMALISATION_RULE_UPDATED', {
        entityType: 'normalisation_rule',
        ruleId: 'NORM-CHAR-' + changeData.base,
        base: changeData.base,
        version: version.version,
        before: beforeState,
        after: afterState,
        appliesToScope: afterState.scope,
        riskLevel: afterState.risk,
        changeType: beforeState.equivalents.length === 0 ? 'created' : 'updated'
    });
    
    var modal = bootstrap.Modal.getInstance(document.getElementById('normRuleModal'));
    if (modal) modal.hide();
    
    MessageEnforcementService.hotReloadRules();
    
    if (window.NormalisationEnforcementAPI) {
        NormalisationEnforcementAPI.invalidateCache();
    }
    
    SecurityComplianceControlsService.renderAllTabs();
    showToast('Updated normalisation rule for "' + changeData.base + '" (v' + version.version + ')', 'success');
}

function showNormRuleVersionHistory(base) {
    var char = mockData.baseCharacterLibrary.find(function(c) { return c.base === base; });
    if (!char) return;
    
    var versions = getNormRuleVersions(base);
    
    var versionsHtml = '';
    if (versions.length === 0) {
        versionsHtml = '<div class="text-center text-muted py-4"><i class="fas fa-history me-2"></i>No version history available</div>';
    } else {
        versions.slice().reverse().forEach(function(v, idx) {
            var isLatest = idx === 0;
            var actionBadge = v.action === 'rollback' 
                ? '<span class="badge bg-warning text-dark">Rollback</span>'
                : '<span class="badge bg-primary">Update</span>';
            
            var beforeEquivs = (v.before.equivalents || []).join(', ') || '—';
            var afterEquivs = (v.after.equivalents || []).join(', ') || '—';
            
            versionsHtml += '<div class="norm-version-item' + (isLatest ? ' norm-version-current' : '') + '">' +
                '<div class="d-flex justify-content-between align-items-start mb-2">' +
                    '<div>' +
                        '<strong>Version ' + v.version + '</strong> ' + actionBadge +
                        (isLatest ? ' <span class="badge bg-success">Current</span>' : '') +
                    '</div>' +
                    '<small class="text-muted">' + new Date(v.timestamp).toLocaleString() + '</small>' +
                '</div>' +
                '<div class="row g-2 mb-2">' +
                    '<div class="col-md-6">' +
                        '<div class="small text-muted">Before:</div>' +
                        '<div class="norm-version-snapshot">' +
                            '<div><strong>Equivalents:</strong> ' + beforeEquivs + '</div>' +
                            '<div><strong>Scope:</strong> ' + (v.before.scope || []).join(', ') + '</div>' +
                            '<div><strong>Status:</strong> ' + (v.before.enabled ? 'Enabled' : 'Disabled') + '</div>' +
                        '</div>' +
                    '</div>' +
                    '<div class="col-md-6">' +
                        '<div class="small text-muted">After:</div>' +
                        '<div class="norm-version-snapshot">' +
                            '<div><strong>Equivalents:</strong> ' + afterEquivs + '</div>' +
                            '<div><strong>Scope:</strong> ' + (v.after.scope || []).join(', ') + '</div>' +
                            '<div><strong>Status:</strong> ' + (v.after.enabled ? 'Enabled' : 'Disabled') + '</div>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
                '<div class="small text-muted">By: ' + v.actor + '</div>' +
                (!isLatest ? '<button class="btn btn-sm btn-outline-warning mt-2" onclick="showRollbackConfirmModal(\'' + base + '\', ' + v.version + ')">' +
                    '<i class="fas fa-undo me-1"></i>Rollback to this version</button>' : '') +
            '</div>';
        });
    }
    
    var modalHtml = '<div class="modal fade" id="normVersionHistoryModal" tabindex="-1">' +
        '<div class="modal-dialog modal-lg">' +
            '<div class="modal-content">' +
                '<div class="modal-header" style="background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%); color: white;">' +
                    '<h5 class="modal-title"><i class="fas fa-history me-2"></i>Version History: <code style="background: rgba(255,255,255,0.2); padding: 4px 8px; border-radius: 4px;">' + base + '</code></h5>' +
                    '<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>' +
                '</div>' +
                '<div class="modal-body" style="max-height: 500px; overflow-y: auto;">' +
                    '<div class="alert alert-info mb-3" style="background: #e8f4fd; border: 1px solid #1e3a5f;">' +
                        '<i class="fas fa-info-circle me-2" style="color: #1e3a5f;"></i>' +
                        'Each save creates an immutable version. You can rollback to any previous version.' +
                    '</div>' +
                    '<div class="norm-version-list">' + versionsHtml + '</div>' +
                '</div>' +
                '<div class="modal-footer">' +
                    '<small class="text-muted me-auto"><i class="fas fa-database me-1"></i>' + versions.length + ' version(s) stored</small>' +
                    '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>' +
                '</div>' +
            '</div>' +
        '</div>' +
    '</div>';
    
    var existingModal = document.getElementById('normVersionHistoryModal');
    if (existingModal) existingModal.remove();
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    var modal = new bootstrap.Modal(document.getElementById('normVersionHistoryModal'));
    modal.show();
}

function showRollbackConfirmModal(base, targetVersion) {
    var versions = getNormRuleVersions(base);
    var targetVersionData = versions.find(function(v) { return v.version === targetVersion; });
    
    if (!targetVersionData) {
        showToast('Version not found', 'error');
        return;
    }
    
    var afterEquivs = (targetVersionData.after.equivalents || []).join(', ') || '—';
    
    var modalHtml = '<div class="modal fade" id="rollbackConfirmModal" tabindex="-1" data-bs-backdrop="static">' +
        '<div class="modal-dialog">' +
            '<div class="modal-content">' +
                '<div class="modal-header" style="background: #f59e0b; color: white;">' +
                    '<h5 class="modal-title"><i class="fas fa-undo me-2"></i>Confirm Rollback</h5>' +
                '</div>' +
                '<div class="modal-body">' +
                    '<div class="alert alert-warning" style="background: #fef3c7; border-color: #f59e0b;">' +
                        '<i class="fas fa-exclamation-triangle me-2" style="color: #92400e;"></i>' +
                        '<strong>Warning:</strong> This will revert the rule to a previous state. A new version will be created.' +
                    '</div>' +
                    '<p><strong>Base Character:</strong> <code style="font-size: 1.2rem;">' + base + '</code></p>' +
                    '<p><strong>Rolling back to:</strong> Version ' + targetVersion + '</p>' +
                    '<div class="card border-0 mb-3" style="background: #fafbfc;">' +
                        '<div class="card-body">' +
                            '<div class="small text-muted mb-1">State after rollback:</div>' +
                            '<div><strong>Equivalents:</strong> ' + afterEquivs + '</div>' +
                            '<div><strong>Scope:</strong> ' + (targetVersionData.after.scope || []).join(', ') + '</div>' +
                            '<div><strong>Status:</strong> ' + (targetVersionData.after.enabled ? 'Enabled' : 'Disabled') + '</div>' +
                        '</div>' +
                    '</div>' +
                    '<div class="mb-3">' +
                        '<label class="form-label">Type <code style="background: #fef3c7; color: #92400e; padding: 2px 6px; border-radius: 4px;">ROLLBACK</code> to confirm:</label>' +
                        '<input type="text" class="form-control" id="rollbackConfirmInput" placeholder="Type ROLLBACK here..." autocomplete="off">' +
                    '</div>' +
                '</div>' +
                '<div class="modal-footer">' +
                    '<button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>' +
                    '<button type="button" class="btn btn-warning" id="rollbackConfirmBtn" onclick="executeRollback(\'' + base + '\', ' + targetVersion + ')" disabled>' +
                        '<i class="fas fa-undo me-1"></i>Rollback' +
                    '</button>' +
                '</div>' +
            '</div>' +
        '</div>' +
    '</div>';
    
    var existingModal = document.getElementById('rollbackConfirmModal');
    if (existingModal) existingModal.remove();
    
    var historyModal = bootstrap.Modal.getInstance(document.getElementById('normVersionHistoryModal'));
    if (historyModal) historyModal.hide();
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    var confirmInput = document.getElementById('rollbackConfirmInput');
    var confirmBtn = document.getElementById('rollbackConfirmBtn');
    
    confirmInput.addEventListener('input', function() {
        confirmBtn.disabled = this.value.trim().toUpperCase() !== 'ROLLBACK';
    });
    
    var modal = new bootstrap.Modal(document.getElementById('rollbackConfirmModal'));
    modal.show();
    
    confirmInput.focus();
}

function executeRollback(base, targetVersion) {
    var confirmInput = document.getElementById('rollbackConfirmInput');
    if (confirmInput.value.trim().toUpperCase() !== 'ROLLBACK') {
        return;
    }
    
    var versions = getNormRuleVersions(base);
    var targetVersionData = versions.find(function(v) { return v.version === targetVersion; });
    
    if (!targetVersionData) {
        showToast('Version not found', 'error');
        return;
    }
    
    var char = mockData.baseCharacterLibrary.find(function(c) { return c.base === base; });
    if (!char) return;
    
    var beforeState = {
        equivalents: char.equivalents.slice(),
        scope: char.scope.slice(),
        notes: char.notes,
        enabled: char.enabled,
        risk: char.risk || 'none'
    };
    
    char.equivalents = targetVersionData.after.equivalents.slice();
    char.scope = targetVersionData.after.scope.slice();
    char.notes = targetVersionData.after.notes || '';
    char.enabled = targetVersionData.after.enabled;
    char.updated = new Date().toLocaleDateString('en-GB').replace(/\//g, '-');
    char.risk = computeRisk(char);
    
    var afterState = {
        equivalents: char.equivalents.slice(),
        scope: char.scope.slice(),
        notes: char.notes,
        enabled: char.enabled,
        risk: char.risk
    };
    
    var version = createNormRuleVersion(base, beforeState, afterState, 'rollback');
    char.currentVersion = version.version;
    
    logAuditEvent('NORMALISATION_RULE_ROLLBACK', {
        entityType: 'normalisation_rule',
        ruleId: 'NORM-CHAR-' + base,
        base: base,
        fromVersion: versions.length - 1,
        toVersion: targetVersion,
        newVersion: version.version,
        before: beforeState,
        after: afterState,
        appliesToScope: afterState.scope,
        riskLevel: afterState.risk
    });
    
    var modal = bootstrap.Modal.getInstance(document.getElementById('rollbackConfirmModal'));
    modal.hide();
    
    MessageEnforcementService.hotReloadRules();
    
    if (window.NormalisationEnforcementAPI) {
        NormalisationEnforcementAPI.invalidateCache();
    }
    
    SecurityComplianceControlsService.renderAllTabs();
    showToast('Rolled back "' + base + '" to version ' + targetVersion + ' (now v' + version.version + ')', 'success');
}

function computeRisk(char) {
    var equivalents = char.equivalents || [];
    var scope = char.scope || [];
    var equivCount = equivalents.length;
    
    if (equivCount === 0) return 'none';
    
    var hasUrlScope = scope.indexOf('url') !== -1;
    var hasDigits = equivalents.some(function(eq) { return /[0-9]/.test(eq); });
    var hasPunctuation = equivalents.some(function(eq) { return /[!@#$%^&*(),.?":{}|<>]/.test(eq); });
    var digitCount = equivalents.filter(function(eq) { return /[0-9]/.test(eq); }).length;
    
    if (hasUrlScope) return 'high';
    if (digitCount >= 2 && hasPunctuation) return 'high';
    
    if (hasDigits || equivCount > 5) return 'medium';
    
    return 'low';
}

function getRiskBadgeHtml(risk) {
    var riskColors = {
        'high': { bg: '#fee2e2', text: '#991b1b', icon: 'fa-exclamation-triangle' },
        'medium': { bg: '#fef3c7', text: '#92400e', icon: 'fa-exclamation-circle' },
        'low': { bg: '#d1fae5', text: '#065f46', icon: 'fa-check-circle' },
        'none': { bg: '#f3f4f6', text: '#6b7280', icon: 'fa-minus-circle' }
    };
    var style = riskColors[risk] || riskColors['none'];
    return '<span class="badge" style="background: ' + style.bg + '; color: ' + style.text + '; font-weight: 500; padding: 4px 8px;">' +
        '<i class="fas ' + style.icon + ' me-1"></i>' + (risk.charAt(0).toUpperCase() + risk.slice(1)) + '</span>';
}

function testBaseCharacter(base) {
    var char = mockData.baseCharacterLibrary.find(function(c) { return c.base === base; });
    if (char) {
        showTestNormalisationModal({ base: base, equivalents: char.equivalents });
    }
}

function toggleBaseCharacterStatus(base, enabled) {
    var char = mockData.baseCharacterLibrary.find(function(c) { return c.base === base; });
    if (!char) return;
    
    var beforeState = {
        enabled: char.enabled,
        equivalents: char.equivalents.slice(),
        scope: char.scope.slice(),
        risk: char.risk || 'none'
    };
    
    char.enabled = enabled;
    char.updated = new Date().toLocaleDateString('en-GB').replace(/\//g, '-');
    char.updatedBy = 'admin@quicksms.co.uk';
    
    var afterState = {
        enabled: char.enabled,
        equivalents: char.equivalents.slice(),
        scope: char.scope.slice(),
        risk: char.risk || 'none'
    };
    
    logAuditEvent('NORMALISATION_RULE_STATUS_CHANGED', {
        entityType: 'normalisation_rule',
        ruleId: 'NORM-CHAR-' + base,
        base: base,
        before: beforeState,
        after: afterState,
        appliesToScope: char.scope,
        riskLevel: char.risk || 'none',
        statusChange: enabled ? 'enabled' : 'disabled'
    });
    
    MessageEnforcementService.hotReloadRules();
    
    if (window.NormalisationEnforcementAPI) {
        NormalisationEnforcementAPI.invalidateCache();
    }
    
    SecurityComplianceControlsService.renderAllTabs();
    showToast('Rule for "' + base + '" ' + (enabled ? 'enabled' : 'disabled'), 'success');
}

function exportBaseCharacterLibrary() {
    showExportNormModal();
}

function showExportNormModal() {
    var modalHtml = '<div class="modal fade" id="exportNormModal" tabindex="-1">' +
        '<div class="modal-dialog">' +
            '<div class="modal-content">' +
                '<div class="modal-header" style="background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%); color: white;">' +
                    '<h5 class="modal-title"><i class="fas fa-download me-2"></i>Export Normalisation Rules</h5>' +
                    '<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>' +
                '</div>' +
                '<div class="modal-body">' +
                    '<div class="mb-4">' +
                        '<label class="form-label fw-bold"><i class="fas fa-file me-2 text-muted"></i>Export Format</label>' +
                        '<div class="d-flex gap-2">' +
                            '<button type="button" class="btn norm-export-format-btn active" data-format="json" onclick="selectExportFormat(this)">' +
                                '<i class="fas fa-code me-1"></i>JSON' +
                            '</button>' +
                            '<button type="button" class="btn norm-export-format-btn" data-format="csv" onclick="selectExportFormat(this)">' +
                                '<i class="fas fa-file-csv me-1"></i>CSV' +
                            '</button>' +
                        '</div>' +
                    '</div>' +
                    '<div class="mb-4">' +
                        '<label class="form-label fw-bold"><i class="fas fa-filter me-2 text-muted"></i>Include</label>' +
                        '<div class="form-check">' +
                            '<input class="form-check-input" type="checkbox" id="exportIncludeEnabled" checked>' +
                            '<label class="form-check-label" for="exportIncludeEnabled">Enabled rules</label>' +
                        '</div>' +
                        '<div class="form-check">' +
                            '<input class="form-check-input" type="checkbox" id="exportIncludeDisabled" checked>' +
                            '<label class="form-check-label" for="exportIncludeDisabled">Disabled rules</label>' +
                        '</div>' +
                        '<div class="form-check">' +
                            '<input class="form-check-input" type="checkbox" id="exportIncludeEmpty">' +
                            '<label class="form-check-label" for="exportIncludeEmpty">Rules with no equivalents</label>' +
                        '</div>' +
                    '</div>' +
                    '<div class="alert alert-info" style="background: #e8f4fd; border: 1px solid #1e3a5f;">' +
                        '<i class="fas fa-info-circle me-2" style="color: #1e3a5f;"></i>' +
                        '<strong>Exported fields:</strong> base_char, equivalents, applies_to, status, risk, updated_at' +
                    '</div>' +
                '</div>' +
                '<div class="modal-footer">' +
                    '<small class="text-muted me-auto"><i class="fas fa-shield-alt me-1"></i>Export action will be logged</small>' +
                    '<button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>' +
                    '<button type="button" class="btn" onclick="executeNormExport()" style="background: #1e3a5f; border-color: #1e3a5f; color: white;">' +
                        '<i class="fas fa-download me-1"></i>Download' +
                    '</button>' +
                '</div>' +
            '</div>' +
        '</div>' +
    '</div>';
    
    var existingModal = document.getElementById('exportNormModal');
    if (existingModal) existingModal.remove();
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    var modal = new bootstrap.Modal(document.getElementById('exportNormModal'));
    modal.show();
}

function selectExportFormat(btn) {
    document.querySelectorAll('.norm-export-format-btn').forEach(function(b) {
        b.classList.remove('active');
    });
    btn.classList.add('active');
}

function executeNormExport() {
    var formatBtn = document.querySelector('.norm-export-format-btn.active');
    var format = formatBtn ? formatBtn.getAttribute('data-format') : 'json';
    var includeEnabled = document.getElementById('exportIncludeEnabled').checked;
    var includeDisabled = document.getElementById('exportIncludeDisabled').checked;
    var includeEmpty = document.getElementById('exportIncludeEmpty').checked;
    
    var library = mockData.baseCharacterLibrary.filter(function(char) {
        if (!includeEnabled && char.enabled) return false;
        if (!includeDisabled && !char.enabled) return false;
        if (!includeEmpty && char.equivalents.length === 0) return false;
        return true;
    });
    
    var timestamp = new Date().toISOString().split('T')[0];
    var filename, blob, content;
    
    if (format === 'json') {
        var exportData = {
            exportedAt: new Date().toISOString(),
            exportedBy: 'admin@quicksms.co.uk',
            version: '1.0',
            totalRules: library.length,
            rules: library.map(function(char) {
                return {
                    base_char: char.base,
                    equivalents: char.equivalents.join(','),
                    applies_to: char.scope.join(','),
                    status: char.enabled ? 'enabled' : 'disabled',
                    risk: char.risk || 'none',
                    updated_at: char.updatedAt || char.updated || ''
                };
            })
        };
        content = JSON.stringify(exportData, null, 2);
        blob = new Blob([content], { type: 'application/json' });
        filename = 'normalisation-rules-' + timestamp + '.json';
    } else {
        var csvRows = ['base_char,equivalents,applies_to,status,risk,updated_at'];
        library.forEach(function(char) {
            var row = [
                char.base,
                '"' + char.equivalents.join(',') + '"',
                '"' + char.scope.join(',') + '"',
                char.enabled ? 'enabled' : 'disabled',
                char.risk || 'none',
                char.updatedAt || char.updated || ''
            ];
            csvRows.push(row.join(','));
        });
        content = csvRows.join('\n');
        blob = new Blob([content], { type: 'text/csv' });
        filename = 'normalisation-rules-' + timestamp + '.csv';
    }
    
    var url = URL.createObjectURL(blob);
    var a = document.createElement('a');
    a.href = url;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
    
    logAuditEvent('NORMALISATION_RULES_EXPORTED', {
        entityType: 'normalisation_library',
        format: format,
        rulesExported: library.length,
        filters: { includeEnabled: includeEnabled, includeDisabled: includeDisabled, includeEmpty: includeEmpty },
        exportedScopes: library.reduce(function(acc, r) {
            (r.scope || []).forEach(function(s) { if (acc.indexOf(s) === -1) acc.push(s); });
            return acc;
        }, []),
        before: null,
        after: {
            exportedAt: new Date().toISOString(),
            rulesCount: library.length,
            format: format
        }
    });
    
    var modal = bootstrap.Modal.getInstance(document.getElementById('exportNormModal'));
    modal.hide();
    
    showToast('Exported ' + library.length + ' rules as ' + format.toUpperCase(), 'success');
    URL.revokeObjectURL(url);
    
    logAuditEvent('NORMALISATION_RULES_EXPORTED', { ruleCount: activeRules.length });
}

function toggleNormRowExpansion(row, event) {
    if (event.target.closest('.dropdown') || event.target.closest('.action-menu-btn') || event.target.closest('.dropdown-menu')) {
        return;
    }
    
    var base = row.getAttribute('data-base');
    var existingExpansion = row.nextElementSibling;
    
    if (existingExpansion && existingExpansion.classList.contains('expansion-row')) {
        row.classList.remove('expanded');
        existingExpansion.remove();
        return;
    }
    
    var tbody = row.closest('tbody');
    tbody.querySelectorAll('tr.expansion-row').forEach(function(er) { er.remove(); });
    tbody.querySelectorAll('tr.expanded').forEach(function(er) { er.classList.remove('expanded'); });
    
    row.classList.add('expanded');
    
    var char = mockData.baseCharacterLibrary.find(function(c) { return c.base === base; });
    if (!char) return;
    
    var expansionHtml = createNormExpansionContent(char);
    
    var expansionRow = document.createElement('tr');
    expansionRow.className = 'expansion-row';
    expansionRow.innerHTML = '<td colspan="7">' + expansionHtml + '</td>';
    
    row.insertAdjacentElement('afterend', expansionRow);
}

function createNormExpansionContent(char) {
    var equivalentsHtml = '';
    if (char.equivalents.length > 0) {
        equivalentsHtml = '<div class="equiv-full-list" id="equiv-list-' + char.base + '">';
        char.equivalents.forEach(function(eq) {
            var codepoint = eq.codePointAt(0).toString(16).toUpperCase().padStart(4, '0');
            equivalentsHtml += '<span class="equiv-full-chip" title="U+' + codepoint + '">' + eq + 
                '<span class="equiv-codepoint codepoint-hidden" style="display: none;">U+' + codepoint + '</span></span>';
        });
        equivalentsHtml += '</div>';
        equivalentsHtml += '<div class="mt-2"><span class="norm-codepoints-toggle" onclick="toggleCodepoints(\'' + char.base + '\')"><i class="fas fa-code me-1"></i>Show Unicode codepoints</span></div>';
    } else {
        equivalentsHtml = '<span class="text-muted">No equivalents configured for this character.</span>';
    }
    
    var notesHtml = char.notes 
        ? '<div class="norm-notes-text"><i class="fas fa-quote-left me-2 text-muted" style="font-size: 0.7rem;"></i>' + char.notes + '</div>'
        : '<span class="text-muted fst-italic">No notes added.</span>';
    
    var historyHtml = generateCharHistory(char);
    
    return '<div class="norm-expansion-content">' +
        '<div class="row">' +
            '<div class="col-md-6">' +
                '<div class="norm-expansion-section">' +
                    '<div class="norm-expansion-label">Full Equivalents List (' + char.equivalents.length + ')</div>' +
                    '<div class="norm-expansion-value">' + equivalentsHtml + '</div>' +
                '</div>' +
            '</div>' +
            '<div class="col-md-3">' +
                '<div class="norm-expansion-section">' +
                    '<div class="norm-expansion-label">Notes</div>' +
                    '<div class="norm-expansion-value">' + notesHtml + '</div>' +
                '</div>' +
            '</div>' +
            '<div class="col-md-3">' +
                '<div class="norm-expansion-section">' +
                    '<div class="norm-expansion-label">Recent Change History</div>' +
                    '<div class="norm-expansion-value">' + historyHtml + '</div>' +
                '</div>' +
            '</div>' +
        '</div>' +
        '<div class="d-flex gap-2 mt-3 pt-3 border-top">' +
            '<button class="btn btn-sm btn-outline-primary" onclick="editBaseCharacter(\'' + char.base + '\'); event.stopPropagation();" style="border-color: #1e3a5f; color: #1e3a5f;">' +
                '<i class="fas fa-edit me-1"></i>Edit Equivalents' +
            '</button>' +
            '<button class="btn btn-sm btn-outline-secondary" onclick="testBaseCharacter(\'' + char.base + '\'); event.stopPropagation();">' +
                '<i class="fas fa-flask me-1"></i>Test Character' +
            '</button>' +
        '</div>' +
    '</div>';
}

function generateCharHistory(char) {
    var history = char.history || [
        { action: 'updated', description: 'Equivalents modified', time: char.updated || '28-01-2026', actor: 'admin@quicksms.co.uk' },
        { action: 'created', description: 'Character initialized', time: '01-01-2026', actor: 'system' }
    ];
    
    if (history.length === 0) {
        return '<span class="text-muted fst-italic">No change history available.</span>';
    }
    
    var icons = {
        'updated': 'fa-pen',
        'created': 'fa-plus',
        'enabled': 'fa-check',
        'disabled': 'fa-ban',
        'scope_changed': 'fa-exchange-alt'
    };
    
    return history.slice(0, 3).map(function(h) {
        var icon = icons[h.action] || 'fa-circle';
        return '<div class="norm-history-item">' +
            '<div class="norm-history-icon"><i class="fas ' + icon + '"></i></div>' +
            '<div>' +
                '<div class="norm-history-text">' + h.description + '</div>' +
                '<div class="norm-history-time">' + h.time + ' by ' + (h.actor || 'Unknown').split('@')[0] + '</div>' +
            '</div>' +
        '</div>';
    }).join('');
}

function toggleCodepoints(base) {
    var container = document.getElementById('equiv-list-' + base);
    if (!container) return;
    
    var codepoints = container.querySelectorAll('.equiv-codepoint');
    var isVisible = codepoints.length > 0 && codepoints[0].style.display !== 'none';
    
    codepoints.forEach(function(cp) {
        cp.style.display = isVisible ? 'none' : 'inline';
    });
    
    var toggle = container.nextElementSibling.querySelector('.norm-codepoints-toggle');
    if (toggle) {
        toggle.innerHTML = isVisible 
            ? '<i class="fas fa-code me-1"></i>Show Unicode codepoints'
            : '<i class="fas fa-code me-1"></i>Hide Unicode codepoints';
    }
}

function testNormalisationRule() {
    showTestNormalisationModal(null);
}

function showTestNormalisationModal(rule) {
    var modalHtml = '<div class="modal fade" id="testNormalisationModal" tabindex="-1">' +
        '<div class="modal-dialog modal-lg">' +
            '<div class="modal-content">' +
                '<div class="modal-header" style="background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%); color: white;">' +
                    '<h5 class="modal-title"><i class="fas fa-flask me-2"></i>Test Normalisation</h5>' +
                    '<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>' +
                '</div>' +
                '<div class="modal-body">' +
                    '<div class="alert alert-info mb-3" style="background: #e8f4fd; border: 1px solid #1e3a5f; border-radius: 8px;">' +
                        '<i class="fas fa-info-circle me-2" style="color: #1e3a5f;"></i>' +
                        '<strong>Simulation Only:</strong> This tool tests normalisation rules without sending any messages.' +
                    '</div>' +
                    
                    '<div class="mb-4">' +
                        '<label class="form-label fw-bold"><i class="fas fa-keyboard me-2 text-muted"></i>Test String</label>' +
                        '<input type="text" class="form-control form-control-lg" id="normTestInput" placeholder="Enter text to test..." value="' + (rule && rule.base ? rule.base : 'LL0YDS') + '" style="font-family: monospace; font-size: 1.2rem;">' +
                        '<small class="text-muted">Enter text with potential homoglyphs or character substitutions (e.g., "LL0YDS" with zero instead of O)</small>' +
                    '</div>' +
                    
                    '<div class="mb-4">' +
                        '<label class="form-label fw-bold"><i class="fas fa-bullseye me-2 text-muted"></i>Mode</label>' +
                        '<div class="d-flex gap-2">' +
                            '<button type="button" class="btn norm-test-mode-btn active" data-mode="senderid" onclick="selectNormTestMode(this)">' +
                                '<i class="fas fa-id-badge me-1"></i>SenderID' +
                            '</button>' +
                            '<button type="button" class="btn norm-test-mode-btn" data-mode="content" onclick="selectNormTestMode(this)">' +
                                '<i class="fas fa-comment-alt me-1"></i>Content' +
                            '</button>' +
                            '<button type="button" class="btn norm-test-mode-btn" data-mode="url" onclick="selectNormTestMode(this)">' +
                                '<i class="fas fa-link me-1"></i>URL' +
                            '</button>' +
                        '</div>' +
                    '</div>' +
                    
                    '<div class="mb-4">' +
                        '<label class="form-label fw-bold"><i class="fas fa-crosshairs me-2 text-muted"></i>Compare Against <span class="text-muted fw-normal">(optional)</span></label>' +
                        '<input type="text" class="form-control" id="normTestTarget" placeholder="Enter target to match (e.g., LLOYDS)" value="LLOYDS" style="font-family: monospace;">' +
                        '<small class="text-muted">If provided, we\'ll check if the normalised input matches this target</small>' +
                    '</div>' +
                    
                    '<button class="btn btn-lg" onclick="runNormalisationTest()" style="background: #1e3a5f; border-color: #1e3a5f; color: white;">' +
                        '<i class="fas fa-play me-1"></i>Run Test' +
                    '</button>' +
                    
                    '<div id="normTestResults" style="display: none; margin-top: 1.5rem;">' +
                        '<hr>' +
                        '<h6 class="mb-3"><i class="fas fa-chart-bar me-2"></i>Results</h6>' +
                        
                        '<div class="row g-3 mb-4">' +
                            '<div class="col-md-6">' +
                                '<div class="card border-0 h-100" style="background: #f8fafc;">' +
                                    '<div class="card-body">' +
                                        '<div class="text-muted small mb-1">Original Input</div>' +
                                        '<div id="normTestOriginal" style="font-family: monospace; font-size: 1.3rem; letter-spacing: 2px;"></div>' +
                                    '</div>' +
                                '</div>' +
                            '</div>' +
                            '<div class="col-md-6">' +
                                '<div class="card border-0 h-100" style="background: #e8f4fd;">' +
                                    '<div class="card-body">' +
                                        '<div class="text-muted small mb-1">Normalised (Canonical)</div>' +
                                        '<div id="normTestNormalised" style="font-family: monospace; font-size: 1.3rem; letter-spacing: 2px; color: #1e3a5f;"></div>' +
                                    '</div>' +
                                '</div>' +
                            '</div>' +
                        '</div>' +
                        
                        '<div id="normTestMatchResult" class="mb-4" style="display: none;"></div>' +
                        
                        '<div class="card border-0 mb-4" style="background: #fafbfc;">' +
                            '<div class="card-header bg-transparent border-bottom" style="font-weight: 600;">' +
                                '<i class="fas fa-exchange-alt me-2 text-muted"></i>Character Substitutions' +
                            '</div>' +
                            '<div class="card-body">' +
                                '<div id="normTestSubstitutions"></div>' +
                            '</div>' +
                        '</div>' +
                        
                        '<div class="card border-0" style="background: #fafbfc;">' +
                            '<div class="card-header bg-transparent border-bottom" style="font-weight: 600;">' +
                                '<i class="fas fa-shield-alt me-2 text-muted"></i>Would Match Rules For' +
                            '</div>' +
                            '<div class="card-body">' +
                                '<div id="normTestMatchedRules"></div>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
                '<div class="modal-footer">' +
                    '<small class="text-muted me-auto"><i class="fas fa-lock me-1"></i>No messages are sent during testing</small>' +
                    '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>' +
                '</div>' +
            '</div>' +
        '</div>' +
    '</div>';
    
    var existingModal = document.getElementById('testNormalisationModal');
    if (existingModal) existingModal.remove();
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    var modal = new bootstrap.Modal(document.getElementById('testNormalisationModal'));
    modal.show();
    
    document.getElementById('normTestInput').focus();
}

function selectNormTestMode(btn) {
    document.querySelectorAll('.norm-test-mode-btn').forEach(function(b) {
        b.classList.remove('active');
    });
    btn.classList.add('active');
}

function runNormalisationTest() {
    var input = document.getElementById('normTestInput').value;
    var target = document.getElementById('normTestTarget').value.trim();
    var modeBtn = document.querySelector('.norm-test-mode-btn.active');
    var scope = modeBtn ? modeBtn.getAttribute('data-mode') : 'senderid';
    
    if (!input) {
        showToast('Please enter text to test', 'warning');
        return;
    }
    
    var result = performNormalisation(input, scope);
    
    document.getElementById('normTestOriginal').innerHTML = result.highlightedOriginal;
    document.getElementById('normTestNormalised').innerHTML = result.highlightedNormalised;
    
    if (target) {
        var targetResult = performNormalisation(target, scope);
        var isMatch = result.normalised.toUpperCase() === targetResult.normalised.toUpperCase();
        
        var matchHtml = isMatch 
            ? '<div class="alert alert-success" style="background: #d1fae5; border-color: #10b981;">' +
                '<i class="fas fa-check-circle me-2" style="color: #065f46;"></i>' +
                '<strong style="color: #065f46;">MATCH</strong> - ' +
                '<span style="font-family: monospace;">' + input + '</span> normalises to match ' +
                '<span style="font-family: monospace;">' + target + '</span>' +
                (result.substitutions.length > 0 ? ' via ' + result.substitutions.map(function(s) { return s.base + '≈' + s.original; }).join(', ') : '') +
              '</div>'
            : '<div class="alert alert-warning" style="background: #fef3c7; border-color: #f59e0b;">' +
                '<i class="fas fa-times-circle me-2" style="color: #92400e;"></i>' +
                '<strong style="color: #92400e;">NO MATCH</strong> - ' +
                '<span style="font-family: monospace;">' + result.normalised + '</span> does not match ' +
                '<span style="font-family: monospace;">' + targetResult.normalised + '</span>' +
              '</div>';
        
        document.getElementById('normTestMatchResult').innerHTML = matchHtml;
        document.getElementById('normTestMatchResult').style.display = 'block';
    } else {
        document.getElementById('normTestMatchResult').style.display = 'none';
    }
    
    var subsHtml = '';
    if (result.substitutions.length > 0) {
        subsHtml = '<div class="d-flex flex-wrap gap-2">';
        result.substitutions.forEach(function(s) {
            var encoding = getCharEncoding(s.original);
            var codepoint = s.original.codePointAt(0).toString(16).toUpperCase().padStart(4, '0');
            subsHtml += '<div class="norm-sub-chip">' +
                '<span class="norm-sub-original">' + s.original + '</span>' +
                '<i class="fas fa-arrow-right mx-2 text-muted"></i>' +
                '<span class="norm-sub-base">' + s.base + '</span>' +
                '<span class="norm-sub-info">U+' + codepoint + ' · ' + encoding + '</span>' +
            '</div>';
        });
        subsHtml += '</div>';
    } else {
        subsHtml = '<div class="text-muted"><i class="fas fa-check-circle me-1"></i>No character substitutions - input already canonical</div>';
    }
    document.getElementById('normTestSubstitutions').innerHTML = subsHtml;
    
    var rulesHtml = '';
    var matchedRules = findMatchingRules(result.normalised, scope);
    if (matchedRules.length > 0) {
        rulesHtml = '<div class="d-flex flex-wrap gap-2">';
        matchedRules.forEach(function(r) {
            var riskBadge = getRiskBadgeHtml(r.risk);
            rulesHtml += '<div class="norm-matched-rule">' +
                '<span class="norm-matched-rule-name">' + r.type + '</span>' +
                '<span class="norm-matched-rule-pattern">' + r.pattern + '</span>' +
                riskBadge +
            '</div>';
        });
        rulesHtml += '</div>';
    } else {
        rulesHtml = '<div class="text-muted"><i class="fas fa-shield-alt me-1"></i>No specific blocking rules would match this normalised content</div>';
    }
    document.getElementById('normTestMatchedRules').innerHTML = rulesHtml;
    
    document.getElementById('normTestResults').style.display = 'block';
    
    logAuditEvent('NORMALISATION_TEST_TOOL_USED', { 
        entityType: 'normalisation_test',
        input: input, 
        output: result.normalised, 
        appliesToScope: scope,
        target: target || null,
        matched: target ? (result.normalised.toUpperCase() === performNormalisation(target, scope).normalised.toUpperCase()) : null,
        substitutionsCount: result.substitutions.length,
        matchedRulesCount: matchedRules.length,
        riskLevel: matchedRules.length > 0 ? (matchedRules.some(function(r) { return r.risk === 'high'; }) ? 'high' : 'medium') : 'none'
    });
}

/**
 * NormalisationRulesConfig - Configuration and safety utilities
 * Requirements:
 * - UTF-8 safe (full Unicode)
 * - Deterministic deduplication
 * - No PII stored
 * - Fast rendering for 62+ base rows
 * - Feature-flag ready
 * - Admin-only access enforced at API + UI
 */
var NormalisationRulesConfig = {
    FEATURE_FLAGS: {
        normalisation_enabled: true,
        senderid_scope_enabled: true,
        content_scope_enabled: true,
        url_scope_enabled: true,
        audit_logging_enabled: true,
        test_tool_enabled: true
    },
    
    MAX_EQUIVALENTS_PER_CHAR: 25,
    MAX_BASE_CHARACTERS: 62,
    CACHE_TTL_MS: 60000,
    
    isFeatureEnabled: function(flag) {
        return this.FEATURE_FLAGS[flag] !== false;
    },
    
    setFeatureFlag: function(flag, value, adminContext) {
        if (!adminContext || !adminContext.isAdmin) {
            console.error('[NormalisationRulesConfig] Feature flag update requires admin context');
            return false;
        }
        if (this.FEATURE_FLAGS.hasOwnProperty(flag)) {
            this.FEATURE_FLAGS[flag] = !!value;
            console.log('[NormalisationRulesConfig] Feature flag updated:', flag, '=', value);
            return true;
        }
        return false;
    },
    
    isUTF8Safe: function(char) {
        if (!char || typeof char !== 'string') return false;
        try {
            var codePoint = char.codePointAt(0);
            return codePoint !== undefined && codePoint >= 0 && codePoint <= 0x10FFFF;
        } catch (e) {
            return false;
        }
    },
    
    normalizeUnicode: function(str) {
        if (typeof str !== 'string') return '';
        try {
            return str.normalize('NFC');
        } catch (e) {
            return str;
        }
    },
    
    deduplicateEquivalents: function(equivalents) {
        if (!Array.isArray(equivalents)) return [];
        var seen = {};
        var result = [];
        equivalents.forEach(function(eq) {
            if (!eq || typeof eq !== 'string') return;
            var normalized = NormalisationRulesConfig.normalizeUnicode(eq);
            var key = normalized.codePointAt(0);
            if (key && !seen[key]) {
                seen[key] = true;
                result.push(normalized);
            }
        });
        result.sort(function(a, b) {
            return a.codePointAt(0) - b.codePointAt(0);
        });
        return result;
    },
    
    sanitizePII: function(data) {
        if (!data) return data;
        var piiFields = ['email', 'phone', 'name', 'address', 'ip', 'password', 'token', 'secret'];
        var sanitized = JSON.parse(JSON.stringify(data));
        
        function redactRecursive(obj) {
            if (typeof obj !== 'object' || obj === null) return;
            Object.keys(obj).forEach(function(key) {
                var lowerKey = key.toLowerCase();
                if (piiFields.some(function(f) { return lowerKey.indexOf(f) !== -1; })) {
                    if (typeof obj[key] === 'string') {
                        obj[key] = '[REDACTED]';
                    }
                }
                if (typeof obj[key] === 'object') {
                    redactRecursive(obj[key]);
                }
            });
        }
        
        redactRecursive(sanitized);
        return sanitized;
    },
    
    validateAdminAccess: function() {
        if (typeof AdminAccessControl === 'undefined') return false;
        return AdminAccessControl.currentAdmin && AdminAccessControl.currentAdmin.isAdmin === true;
    },
    
    enforceAdminOnly: function(operation) {
        if (!this.validateAdminAccess()) {
            console.error('[NormalisationRulesConfig] Admin-only operation blocked:', operation);
            throw new Error('ADMIN_ACCESS_REQUIRED');
        }
        return true;
    }
};

window.NormalisationRulesConfig = NormalisationRulesConfig;

/**
 * NormalisationEnforcementAPI - Internal API for normalisation rules
 * All modules MUST use this service for normalisation.
 * 
 * Internal API: GET /internal/normalisation-rules?scope=senderid|content|url
 * Cached with 60s TTL
 * 
 * Evaluation order:
 * 1) Normalise input using equivalence library
 * 2) Apply SenderID / Content / URL rules to canonical string
 */
var NormalisationEnforcementAPI = (function() {
    var cache = {
        rules: {},
        lastFetch: {},
        equivIndex: {},
        TTL_MS: NormalisationRulesConfig.CACHE_TTL_MS
    };
    
    function isCacheValid(scope) {
        var lastFetch = cache.lastFetch[scope] || 0;
        return (Date.now() - lastFetch) < cache.TTL_MS;
    }
    
    function fetchRules(scope) {
        if (isCacheValid(scope) && cache.rules[scope]) {
            console.log('[NormalisationEnforcementAPI] Cache hit for scope: ' + scope);
            return cache.rules[scope];
        }
        
        console.log('[NormalisationEnforcementAPI] Fetching rules for scope: ' + scope);
        
        var rules = mockData.baseCharacterLibrary.filter(function(rule) {
            if (!rule.enabled) return false;
            if (scope === 'all') return true;
            return rule.scope.indexOf(scope) !== -1;
        }).map(function(rule) {
            return {
                base: rule.base,
                equivalents: rule.equivalents.slice(),
                scope: rule.scope.slice()
            };
        });
        
        cache.rules[scope] = rules;
        cache.lastFetch[scope] = Date.now();
        
        console.log('[NormalisationEnforcementAPI] Cached ' + rules.length + ' rules for scope: ' + scope + ' (TTL: 60s)');
        
        return rules;
    }
    
    function invalidateCache(scope) {
        if (scope) {
            delete cache.rules[scope];
            delete cache.lastFetch[scope];
            delete cache.equivIndex[scope];
        } else {
            cache.rules = {};
            cache.lastFetch = {};
            cache.equivIndex = {};
        }
        console.log('[NormalisationEnforcementAPI] Cache invalidated' + (scope ? ' for scope: ' + scope : ' (all)'));
    }
    
    function buildEquivIndex(scope) {
        if (cache.equivIndex[scope] && isCacheValid(scope)) {
            return cache.equivIndex[scope];
        }
        
        var rules = fetchRules(scope);
        var index = {};
        
        rules.forEach(function(rule) {
            rule.equivalents.forEach(function(equiv) {
                var normalized = NormalisationRulesConfig.normalizeUnicode(equiv);
                index[normalized] = rule.base;
            });
        });
        
        cache.equivIndex[scope] = index;
        console.log('[NormalisationEnforcementAPI] Built equiv index for scope: ' + scope + ' (' + Object.keys(index).length + ' mappings)');
        return index;
    }
    
    function normalise(input, scope) {
        if (!NormalisationRulesConfig.isFeatureEnabled('normalisation_enabled')) {
            return {
                normalised: input,
                substitutions: [],
                highlightedOriginal: input,
                highlightedNormalised: input,
                featureDisabled: true
            };
        }
        
        var equivMap = buildEquivIndex(scope);
        
        var normalised = '';
        var substitutions = [];
        var highlightedOriginal = '';
        var highlightedNormalised = '';
        
        var chars = Array.from(input);
        
        chars.forEach(function(char, idx) {
            var baseChar = equivMap[char] || char;
            var found = baseChar !== char;
            
            if (found) {
                substitutions.push({
                    position: idx,
                    original: char,
                    base: baseChar
                });
                highlightedOriginal += '<span class="norm-highlight-sub">' + char + '</span>';
                highlightedNormalised += '<span class="norm-highlight-base">' + baseChar + '</span>';
            } else {
                highlightedOriginal += char;
                highlightedNormalised += char;
            }
            
            normalised += baseChar;
        });
        
        return {
            normalised: normalised,
            substitutions: substitutions,
            highlightedOriginal: highlightedOriginal,
            highlightedNormalised: highlightedNormalised,
            cacheHit: isCacheValid(scope)
        };
    }
    
    function evaluate(input, scope) {
        var normResult = normalise(input, scope);
        
        var ruleMatches = [];
        
        if (window.MessageEnforcementService) {
            ruleMatches = MessageEnforcementService.evaluateAgainstRules(normResult.normalised, scope);
        }
        
        return {
            original: input,
            normalised: normResult.normalised,
            substitutions: normResult.substitutions,
            highlightedOriginal: normResult.highlightedOriginal,
            highlightedNormalised: normResult.highlightedNormalised,
            ruleMatches: ruleMatches
        };
    }
    
    function getCacheStats() {
        return {
            cachedScopes: Object.keys(cache.rules),
            ttlMs: cache.TTL_MS,
            lastFetch: cache.lastFetch
        };
    }
    
    return {
        fetchRules: fetchRules,
        normalise: normalise,
        evaluate: evaluate,
        invalidateCache: invalidateCache,
        getCacheStats: getCacheStats
    };
})();

window.NormalisationEnforcementAPI = NormalisationEnforcementAPI;

function performNormalisation(input, scope) {
    return NormalisationEnforcementAPI.normalise(input, scope);
}

function findMatchingRules(normalisedText, scope) {
    if (window.MessageEnforcementService) {
        return MessageEnforcementService.evaluateAgainstRules(normalisedText, scope);
    }
    return [];
}

function showAddNormRuleModal() {
    var modalHtml = '<div class="modal fade" id="addNormRuleModal" tabindex="-1">' +
        '<div class="modal-dialog modal-lg">' +
            '<div class="modal-content">' +
                '<div class="modal-header" style="background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%); color: white;">' +
                    '<h5 class="modal-title"><i class="fas fa-plus me-2"></i>Add Normalisation Rule</h5>' +
                    '<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>' +
                '</div>' +
                '<div class="modal-body">' +
                    '<div class="row">' +
                        '<div class="col-md-6 mb-3">' +
                            '<label class="form-label fw-bold">Rule Name <span class="text-danger">*</span></label>' +
                            '<input type="text" class="form-control" id="newNormRuleName" placeholder="e.g., Custom Homoglyphs">' +
                        '</div>' +
                        '<div class="col-md-6 mb-3">' +
                            '<label class="form-label fw-bold">Category <span class="text-danger">*</span></label>' +
                            '<select class="form-control" id="newNormRuleCategory">' +
                                '<option value="">Select category...</option>' +
                                '<option value="substitution">Character Substitution</option>' +
                                '<option value="homoglyph">Homoglyph Detection</option>' +
                                '<option value="unicode">Unicode Normalisation</option>' +
                                '<option value="case">Case Folding</option>' +
                            '</select>' +
                        '</div>' +
                    '</div>' +
                    '<div class="mb-3">' +
                        '<label class="form-label fw-bold">Description</label>' +
                        '<textarea class="form-control" id="newNormRuleDescription" rows="2" placeholder="Describe what this rule does..."></textarea>' +
                    '</div>' +
                    '<div class="row">' +
                        '<div class="col-md-6 mb-3">' +
                            '<label class="form-label fw-bold">Scope <span class="text-danger">*</span></label>' +
                            '<select class="form-control" id="newNormRuleScope">' +
                                '<option value="all">All Engines</option>' +
                                '<option value="senderid">SenderID Only</option>' +
                                '<option value="content">Content Only</option>' +
                                '<option value="url">URL Only (Guarded)</option>' +
                            '</select>' +
                        '</div>' +
                        '<div class="col-md-6 mb-3">' +
                            '<label class="form-label fw-bold">Priority</label>' +
                            '<input type="number" class="form-control" id="newNormRulePriority" value="10" min="0" max="100">' +
                            '<small class="text-muted">Lower = higher priority (0-100)</small>' +
                        '</div>' +
                    '</div>' +
                    '<div class="mb-3">' +
                        '<label class="form-label fw-bold">Character Mappings <span class="text-danger">*</span></label>' +
                        '<div id="normMappingsContainer">' +
                            '<div class="mapping-row d-flex gap-2 mb-2">' +
                                '<input type="text" class="form-control" placeholder="Base char" style="width: 100px;">' +
                                '<span class="align-self-center">→</span>' +
                                '<input type="text" class="form-control" placeholder="Equivalents (comma-separated)">' +
                                '<button class="btn btn-outline-danger btn-sm" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>' +
                            '</div>' +
                        '</div>' +
                        '<button class="btn btn-outline-secondary btn-sm mt-1" onclick="addMappingRow()">' +
                            '<i class="fas fa-plus me-1"></i>Add Mapping' +
                        '</button>' +
                    '</div>' +
                '</div>' +
                '<div class="modal-footer">' +
                    '<small class="text-muted me-auto"><i class="fas fa-shield-alt me-1"></i>Changes require admin approval</small>' +
                    '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>' +
                    '<button type="button" class="btn btn-primary" onclick="saveNewNormRule()" style="background: #1e3a5f; border-color: #1e3a5f;">' +
                        '<i class="fas fa-save me-1"></i>Save Rule' +
                    '</button>' +
                '</div>' +
            '</div>' +
        '</div>' +
    '</div>';
    
    var existingModal = document.getElementById('addNormRuleModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    var modal = new bootstrap.Modal(document.getElementById('addNormRuleModal'));
    modal.show();
}

function addMappingRow() {
    var container = document.getElementById('normMappingsContainer');
    var rowHtml = '<div class="mapping-row d-flex gap-2 mb-2">' +
        '<input type="text" class="form-control" placeholder="Base char" style="width: 100px;">' +
        '<span class="align-self-center">→</span>' +
        '<input type="text" class="form-control" placeholder="Equivalents (comma-separated)">' +
        '<button class="btn btn-outline-danger btn-sm" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>' +
    '</div>';
    container.insertAdjacentHTML('beforeend', rowHtml);
}

function saveNewNormRule() {
    var name = document.getElementById('newNormRuleName').value.trim();
    var category = document.getElementById('newNormRuleCategory').value;
    var description = document.getElementById('newNormRuleDescription').value.trim();
    var scope = document.getElementById('newNormRuleScope').value;
    var priority = parseInt(document.getElementById('newNormRulePriority').value) || 10;
    
    if (!name || !category) {
        alert('Please fill in required fields');
        return;
    }
    
    var mappings = [];
    document.querySelectorAll('#normMappingsContainer .mapping-row').forEach(function(row) {
        var inputs = row.querySelectorAll('input');
        var base = inputs[0].value.trim();
        var equivalentsStr = inputs[1].value.trim();
        if (base && equivalentsStr) {
            mappings.push({
                base: base,
                equivalents: equivalentsStr.split(',').map(function(e) { return e.trim(); }).filter(function(e) { return e; })
            });
        }
    });
    
    if (mappings.length === 0) {
        alert('Please add at least one character mapping');
        return;
    }
    
    var scopeLabels = {
        'all': 'All Engines',
        'senderid': 'SenderID Only',
        'content': 'Content Only',
        'url': 'URL Only'
    };
    
    var newRule = {
        id: mockData.normalisationRules.length + 1,
        name: name,
        category: category,
        description: description,
        mappings: mappings,
        scope: scope,
        scopeLabel: scopeLabels[scope] || scope,
        priority: priority,
        status: 'active',
        createdAt: new Date().toLocaleDateString('en-GB').replace(/\//g, '-'),
        updatedAt: new Date().toLocaleDateString('en-GB').replace(/\//g, '-'),
        createdBy: 'admin@quicksms.co.uk'
    };
    
    mockData.normalisationRules.push(newRule);
    MessageEnforcementService.hotReloadRules();
    
    logAuditEvent('NORMALISATION_RULE_CREATED', { ruleId: newRule.id, ruleName: newRule.name });
    
    var modal = bootstrap.Modal.getInstance(document.getElementById('addNormRuleModal'));
    modal.hide();
    
    SecurityComplianceControlsService.renderAllTabs();
}

function resetNormFilters(type) {
    if (type) {
        var suffix = type === 'uppercase' ? 'upper' : type === 'lowercase' ? 'lower' : 'digits';
        document.getElementById('norm-filter-status-' + suffix).value = '';
        document.getElementById('norm-filter-scope-' + suffix).value = '';
        document.getElementById('norm-filter-risk-' + suffix).value = '';
        document.getElementById('norm-search-' + suffix).value = '';
        filterBaseCharacters(type);
    } else {
        ['upper', 'lower', 'digits'].forEach(function(suffix) {
            var statusEl = document.getElementById('norm-filter-status-' + suffix);
            var scopeEl = document.getElementById('norm-filter-scope-' + suffix);
            var riskEl = document.getElementById('norm-filter-risk-' + suffix);
            var searchEl = document.getElementById('norm-search-' + suffix);
            if (statusEl) statusEl.value = '';
            if (scopeEl) scopeEl.value = '';
            if (riskEl) riskEl.value = '';
            if (searchEl) searchEl.value = '';
        });
        SecurityComplianceControlsService.renderAllTabs();
    }
}

function showBulkEditModal() {
    var modalHtml = '<div class="modal fade" id="bulkEditModal" tabindex="-1">' +
        '<div class="modal-dialog">' +
            '<div class="modal-content">' +
                '<div class="modal-header" style="background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%); color: white;">' +
                    '<h5 class="modal-title"><i class="fas fa-edit me-2"></i>Bulk Edit Scope</h5>' +
                    '<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>' +
                '</div>' +
                '<div class="modal-body">' +
                    '<div class="mb-3">' +
                        '<label class="form-label fw-bold">Apply Scope To</label>' +
                        '<select class="form-control" id="bulkEditTarget">' +
                            '<option value="all">All Characters (62)</option>' +
                            '<option value="uppercase">Uppercase Only (26)</option>' +
                            '<option value="lowercase">Lowercase Only (26)</option>' +
                            '<option value="digits">Digits Only (10)</option>' +
                            '<option value="with_equivalents">Characters With Equivalents</option>' +
                        '</select>' +
                    '</div>' +
                    '<div class="mb-3">' +
                        '<label class="form-label fw-bold">Set Scope</label>' +
                        '<div class="d-flex gap-3">' +
                            '<div class="form-check">' +
                                '<input class="form-check-input" type="checkbox" id="bulkScopeSenderid" checked>' +
                                '<label class="form-check-label" for="bulkScopeSenderid"><i class="fas fa-id-badge me-1" style="color: #d97706;"></i>SenderID</label>' +
                            '</div>' +
                            '<div class="form-check">' +
                                '<input class="form-check-input" type="checkbox" id="bulkScopeContent">' +
                                '<label class="form-check-label" for="bulkScopeContent"><i class="fas fa-comment-alt me-1" style="color: #2563eb;"></i>Content</label>' +
                            '</div>' +
                            '<div class="form-check">' +
                                '<input class="form-check-input" type="checkbox" id="bulkScopeUrl">' +
                                '<label class="form-check-label" for="bulkScopeUrl"><i class="fas fa-link me-1" style="color: #7c3aed;"></i>URL</label>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
                '<div class="modal-footer">' +
                    '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>' +
                    '<button type="button" class="btn btn-primary" onclick="applyBulkScope()" style="background: #1e3a5f; border-color: #1e3a5f;">' +
                        '<i class="fas fa-check me-1"></i>Apply' +
                    '</button>' +
                '</div>' +
            '</div>' +
        '</div>' +
    '</div>';
    
    var existingModal = document.getElementById('bulkEditModal');
    if (existingModal) existingModal.remove();
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    var modal = new bootstrap.Modal(document.getElementById('bulkEditModal'));
    modal.show();
}

function applyBulkScope() {
    var target = document.getElementById('bulkEditTarget').value;
    var scope = [];
    if (document.getElementById('bulkScopeSenderid').checked) scope.push('senderid');
    if (document.getElementById('bulkScopeContent').checked) scope.push('content');
    if (document.getElementById('bulkScopeUrl').checked) scope.push('url');
    
    if (scope.length === 0) scope = ['senderid'];
    
    var count = 0;
    mockData.baseCharacterLibrary.forEach(function(char) {
        var shouldUpdate = false;
        if (target === 'all') shouldUpdate = true;
        else if (target === 'uppercase' && char.type === 'uppercase') shouldUpdate = true;
        else if (target === 'lowercase' && char.type === 'lowercase') shouldUpdate = true;
        else if (target === 'digits' && char.type === 'digit') shouldUpdate = true;
        else if (target === 'with_equivalents' && char.equivalents.length > 0) shouldUpdate = true;
        
        if (shouldUpdate) {
            char.scope = scope.slice();
            count++;
        }
    });
    
    logAuditEvent('BULK_SCOPE_UPDATED', { target: target, scope: scope, count: count });
    
    var modal = bootstrap.Modal.getInstance(document.getElementById('bulkEditModal'));
    modal.hide();
    
    MessageEnforcementService.hotReloadRules();
    SecurityComplianceControlsService.renderAllTabs();
}

function globalNormSearch() {
    var searchTerm = document.getElementById('norm-global-search').value.toLowerCase().trim();
    applyGlobalNormFilters();
}

function globalNormFilter() {
    applyGlobalNormFilters();
}

function resetGlobalNormFilters() {
    document.getElementById('norm-global-search').value = '';
    document.getElementById('norm-global-scope').value = '';
    document.getElementById('norm-global-status').value = '';
    document.getElementById('norm-global-risk').value = '';
    applyGlobalNormFilters();
}

function applyGlobalNormFilters() {
    var searchTerm = document.getElementById('norm-global-search').value.toLowerCase().trim();
    var scopeFilter = document.getElementById('norm-global-scope').value;
    var statusFilter = document.getElementById('norm-global-status').value;
    var riskFilter = document.getElementById('norm-global-risk').value;
    
    var tables = ['norm-uppercase-body', 'norm-lowercase-body', 'norm-digits-body'];
    
    tables.forEach(function(tableId) {
        var tbody = document.getElementById(tableId);
        if (!tbody) return;
        
        var rows = tbody.querySelectorAll('tr');
        rows.forEach(function(row) {
            var baseChar = row.getAttribute('data-base') || '';
            var equivalents = row.getAttribute('data-equivalents') || '';
            var scope = row.getAttribute('data-scope') || '';
            var status = row.getAttribute('data-status') || '';
            var risk = row.getAttribute('data-risk') || '';
            
            var show = true;
            
            if (searchTerm) {
                var matchBase = baseChar.toLowerCase().indexOf(searchTerm) !== -1;
                var matchEquiv = equivalents.toLowerCase().indexOf(searchTerm) !== -1;
                if (!matchBase && !matchEquiv) show = false;
            }
            
            if (scopeFilter && show) {
                if (scope.indexOf(scopeFilter) === -1) show = false;
            }
            
            if (statusFilter && show) {
                if (status !== statusFilter) show = false;
            }
            
            if (riskFilter && show) {
                if (risk !== riskFilter) show = false;
            }
            
            row.style.display = show ? '' : 'none';
        });
    });
}

function showImportNormLibraryModal() {
    var modalHtml = '<div class="modal fade" id="importNormModal" tabindex="-1" data-bs-backdrop="static">' +
        '<div class="modal-dialog modal-xl">' +
            '<div class="modal-content">' +
                '<div class="modal-header" style="background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%); color: white;">' +
                    '<h5 class="modal-title"><i class="fas fa-upload me-2"></i>Import Normalisation Rules</h5>' +
                    '<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>' +
                '</div>' +
                '<div class="modal-body">' +
                    '<div id="importStep1">' +
                        '<div class="alert alert-warning" style="background: #fef3c7; border: 1px solid #f59e0b;">' +
                            '<i class="fas fa-exclamation-triangle me-2" style="color: #92400e;"></i>' +
                            '<strong>Warning:</strong> Importing will modify existing rules. All changes are logged to the audit trail.' +
                        '</div>' +
                        
                        '<div class="mb-4">' +
                            '<label class="form-label fw-bold"><i class="fas fa-file me-2 text-muted"></i>Upload File (CSV or JSON)</label>' +
                            '<input type="file" class="form-control form-control-lg" id="importFile" accept=".json,.csv" onchange="parseImportFile()">' +
                            '<small class="text-muted">Accepts .json or .csv files exported from this system</small>' +
                        '</div>' +
                        
                        '<div class="text-center my-4">' +
                            '<span class="text-muted">— or paste content directly —</span>' +
                        '</div>' +
                        
                        '<div class="mb-4">' +
                            '<label class="form-label fw-bold"><i class="fas fa-paste me-2 text-muted"></i>Paste Content</label>' +
                            '<textarea class="form-control" id="importPasteContent" rows="6" placeholder="Paste JSON or CSV content here..." onchange="parseImportPaste()"></textarea>' +
                        '</div>' +
                        
                        '<div class="mb-4">' +
                            '<label class="form-label fw-bold"><i class="fas fa-cog me-2 text-muted"></i>Import Mode</label>' +
                            '<div class="d-flex gap-2">' +
                                '<button type="button" class="btn norm-import-mode-btn active" data-mode="merge" onclick="selectImportMode(this)">' +
                                    '<i class="fas fa-code-merge me-1"></i>Merge' +
                                    '<small class="d-block text-muted" style="font-size: 0.7rem;">Add new equivalents, keep existing</small>' +
                                '</button>' +
                                '<button type="button" class="btn norm-import-mode-btn" data-mode="replace" onclick="selectImportMode(this)">' +
                                    '<i class="fas fa-sync me-1"></i>Replace' +
                                    '<small class="d-block text-muted" style="font-size: 0.7rem;">Overwrite all equivalents</small>' +
                                '</button>' +
                            '</div>' +
                        '</div>' +
                        
                        '<div class="text-end">' +
                            '<button type="button" class="btn btn-lg" id="importPreviewBtn" onclick="showImportPreview()" style="background: #1e3a5f; border-color: #1e3a5f; color: white;" disabled>' +
                                '<i class="fas fa-eye me-1"></i>Preview Changes' +
                            '</button>' +
                        '</div>' +
                    '</div>' +
                    
                    '<div id="importStep2" style="display: none;">' +
                        '<div class="d-flex justify-content-between align-items-center mb-3">' +
                            '<button type="button" class="btn btn-outline-secondary" onclick="backToImportStep1()">' +
                                '<i class="fas fa-arrow-left me-1"></i>Back' +
                            '</button>' +
                            '<h6 class="mb-0"><i class="fas fa-clipboard-list me-2"></i>Import Preview</h6>' +
                            '<span></span>' +
                        '</div>' +
                        
                        '<div class="row g-3 mb-4">' +
                            '<div class="col-md-4">' +
                                '<div class="card border-0 h-100" style="background: #d1fae5;">' +
                                    '<div class="card-body text-center">' +
                                        '<div class="fs-2 fw-bold" style="color: #065f46;" id="importNewCount">0</div>' +
                                        '<div class="text-muted small">New Rules</div>' +
                                    '</div>' +
                                '</div>' +
                            '</div>' +
                            '<div class="col-md-4">' +
                                '<div class="card border-0 h-100" style="background: #fef3c7;">' +
                                    '<div class="card-body text-center">' +
                                        '<div class="fs-2 fw-bold" style="color: #92400e;" id="importUpdatedCount">0</div>' +
                                        '<div class="text-muted small">Updated Rules</div>' +
                                    '</div>' +
                                '</div>' +
                            '</div>' +
                            '<div class="col-md-4">' +
                                '<div class="card border-0 h-100" style="background: #fee2e2;">' +
                                    '<div class="card-body text-center">' +
                                        '<div class="fs-2 fw-bold" style="color: #991b1b;" id="importConflictCount">0</div>' +
                                        '<div class="text-muted small">Conflicts</div>' +
                                    '</div>' +
                                '</div>' +
                            '</div>' +
                        '</div>' +
                        
                        '<div class="card border-0 mb-4" style="background: #fafbfc; max-height: 300px; overflow-y: auto;">' +
                            '<div class="card-header bg-transparent border-bottom fw-bold">' +
                                '<i class="fas fa-list me-2 text-muted"></i>Changes to Apply' +
                            '</div>' +
                            '<div class="card-body p-0">' +
                                '<table class="table table-sm mb-0">' +
                                    '<thead style="position: sticky; top: 0; background: #f8f9fa;">' +
                                        '<tr>' +
                                            '<th style="width: 60px;">Base</th>' +
                                            '<th>Change Type</th>' +
                                            '<th>Current</th>' +
                                            '<th>New</th>' +
                                        '</tr>' +
                                    '</thead>' +
                                    '<tbody id="importChangesTable"></tbody>' +
                                '</table>' +
                            '</div>' +
                        '</div>' +
                        
                        '<div id="importConflictWarning" class="alert alert-danger mb-4" style="display: none; background: #fee2e2; border-color: #fecaca;">' +
                            '<i class="fas fa-exclamation-triangle me-2" style="color: #991b1b;"></i>' +
                            '<strong>Conflicts detected:</strong> Some rules have different values. Review carefully before importing.' +
                        '</div>' +
                        
                        '<div class="alert alert-info" style="background: #e8f4fd; border: 1px solid #1e3a5f;">' +
                            '<i class="fas fa-shield-alt me-2" style="color: #1e3a5f;"></i>' +
                            'To confirm import, type <code style="background: #1e3a5f; color: white; padding: 2px 6px; border-radius: 4px;">IMPORT</code> below:' +
                        '</div>' +
                        '<div class="mb-3">' +
                            '<input type="text" class="form-control" id="importConfirmInput" placeholder="Type IMPORT to confirm..." autocomplete="off">' +
                        '</div>' +
                    '</div>' +
                '</div>' +
                '<div class="modal-footer">' +
                    '<small class="text-muted me-auto"><i class="fas fa-shield-alt me-1"></i>All import actions are logged</small>' +
                    '<button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>' +
                    '<button type="button" class="btn" id="importApplyBtn" onclick="executeNormImport()" style="background: #1e3a5f; border-color: #1e3a5f; color: white; display: none;" disabled>' +
                        '<i class="fas fa-check me-1"></i>Apply Import' +
                    '</button>' +
                '</div>' +
            '</div>' +
        '</div>' +
    '</div>';
    
    var existingModal = document.getElementById('importNormModal');
    if (existingModal) existingModal.remove();
    
    window.pendingImportData = null;
    window.parsedImportRules = null;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    var modal = new bootstrap.Modal(document.getElementById('importNormModal'));
    modal.show();
}

function selectImportMode(btn) {
    document.querySelectorAll('.norm-import-mode-btn').forEach(function(b) {
        b.classList.remove('active');
    });
    btn.classList.add('active');
}

function parseImportFile() {
    var fileInput = document.getElementById('importFile');
    if (!fileInput.files || fileInput.files.length === 0) return;
    
    var file = fileInput.files[0];
    var reader = new FileReader();
    
    reader.onload = function(e) {
        var content = e.target.result;
        var isCSV = file.name.endsWith('.csv');
        
        try {
            if (isCSV) {
                window.parsedImportRules = parseCSVImport(content);
            } else {
                window.parsedImportRules = parseJSONImport(content);
            }
            document.getElementById('importPreviewBtn').disabled = false;
            showToast('File parsed: ' + window.parsedImportRules.length + ' rules found', 'success');
        } catch (err) {
            showToast('Error parsing file: ' + err.message, 'error');
            document.getElementById('importPreviewBtn').disabled = true;
        }
    };
    
    reader.readAsText(file);
}

function parseImportPaste() {
    var content = document.getElementById('importPasteContent').value.trim();
    if (!content) {
        document.getElementById('importPreviewBtn').disabled = true;
        return;
    }
    
    try {
        if (content.startsWith('{') || content.startsWith('[')) {
            window.parsedImportRules = parseJSONImport(content);
        } else {
            window.parsedImportRules = parseCSVImport(content);
        }
        document.getElementById('importPreviewBtn').disabled = false;
        showToast('Content parsed: ' + window.parsedImportRules.length + ' rules found', 'success');
    } catch (err) {
        showToast('Error parsing content: ' + err.message, 'error');
        document.getElementById('importPreviewBtn').disabled = true;
    }
}

function parseJSONImport(content) {
    var data = JSON.parse(content);
    var rules = data.rules || data.baseCharacters || [];
    
    return rules.map(function(r) {
        return {
            base_char: r.base_char || r.base,
            equivalents: typeof r.equivalents === 'string' ? r.equivalents.split(',').filter(function(e) { return e.trim(); }) : (r.equivalents || []),
            applies_to: typeof r.applies_to === 'string' ? r.applies_to.split(',').filter(function(s) { return s.trim(); }) : (r.applies_to || r.scope || ['senderid']),
            status: r.status || (r.enabled ? 'enabled' : 'disabled'),
            risk: r.risk || 'none',
            notes: r.notes || ''
        };
    });
}

function parseCSVImport(content) {
    var lines = content.trim().split('\n');
    if (lines.length < 2) throw new Error('CSV must have header and at least one data row');
    
    var headers = lines[0].split(',').map(function(h) { return h.trim().toLowerCase(); });
    var baseIdx = headers.indexOf('base_char');
    var equivIdx = headers.indexOf('equivalents');
    var scopeIdx = headers.indexOf('applies_to');
    var statusIdx = headers.indexOf('status');
    var riskIdx = headers.indexOf('risk');
    
    if (baseIdx === -1) throw new Error('Missing required column: base_char');
    
    var rules = [];
    for (var i = 1; i < lines.length; i++) {
        var values = parseCSVLine(lines[i]);
        if (values.length === 0 || !values[baseIdx]) continue;
        
        rules.push({
            base_char: values[baseIdx],
            equivalents: equivIdx !== -1 ? values[equivIdx].split(',').filter(function(e) { return e.trim(); }) : [],
            applies_to: scopeIdx !== -1 ? values[scopeIdx].split(',').filter(function(s) { return s.trim(); }) : ['senderid'],
            status: statusIdx !== -1 ? values[statusIdx] : 'enabled',
            risk: riskIdx !== -1 ? values[riskIdx] : 'none'
        });
    }
    
    return rules;
}

function parseCSVLine(line) {
    var values = [];
    var current = '';
    var inQuotes = false;
    
    for (var i = 0; i < line.length; i++) {
        var char = line[i];
        if (char === '"') {
            inQuotes = !inQuotes;
        } else if (char === ',' && !inQuotes) {
            values.push(current.trim());
            current = '';
        } else {
            current += char;
        }
    }
    values.push(current.trim());
    
    return values;
}

function showImportPreview() {
    if (!window.parsedImportRules || window.parsedImportRules.length === 0) {
        showToast('No rules to import', 'error');
        return;
    }
    
    var modeBtn = document.querySelector('.norm-import-mode-btn.active');
    var mode = modeBtn ? modeBtn.getAttribute('data-mode') : 'merge';
    
    var newRules = [];
    var updatedRules = [];
    var conflicts = [];
    
    window.parsedImportRules.forEach(function(importRule) {
        var existingChar = mockData.baseCharacterLibrary.find(function(c) { return c.base === importRule.base_char; });
        
        if (!existingChar) {
            return;
        }
        
        var currentEquivs = existingChar.equivalents.slice().sort().join(',');
        var newEquivs = (importRule.equivalents || []).slice().sort().join(',');
        
        if (mode === 'merge') {
            var mergedEquivs = existingChar.equivalents.slice();
            (importRule.equivalents || []).forEach(function(eq) {
                if (mergedEquivs.indexOf(eq) === -1) {
                    mergedEquivs.push(eq);
                }
            });
            newEquivs = mergedEquivs.slice().sort().join(',');
        }
        
        if (currentEquivs === newEquivs && 
            existingChar.scope.slice().sort().join(',') === (importRule.applies_to || []).slice().sort().join(',') &&
            (existingChar.enabled ? 'enabled' : 'disabled') === importRule.status) {
            return;
        }
        
        var changeType = existingChar.equivalents.length === 0 ? 'new' : 'update';
        
        if (changeType === 'new') {
            newRules.push({ rule: importRule, existing: existingChar });
        } else {
            var hasConflict = mode === 'replace' && currentEquivs !== '' && newEquivs !== currentEquivs;
            if (hasConflict) {
                conflicts.push({ rule: importRule, existing: existingChar });
            } else {
                updatedRules.push({ rule: importRule, existing: existingChar });
            }
        }
    });
    
    document.getElementById('importNewCount').textContent = newRules.length;
    document.getElementById('importUpdatedCount').textContent = updatedRules.length;
    document.getElementById('importConflictCount').textContent = conflicts.length;
    
    var tableHtml = '';
    
    newRules.forEach(function(item) {
        tableHtml += '<tr>' +
            '<td><code class="fs-5">' + item.rule.base_char + '</code></td>' +
            '<td><span class="badge bg-success">New</span></td>' +
            '<td class="text-muted">—</td>' +
            '<td>' + (item.rule.equivalents || []).join(', ') + '</td>' +
        '</tr>';
    });
    
    updatedRules.forEach(function(item) {
        tableHtml += '<tr>' +
            '<td><code class="fs-5">' + item.rule.base_char + '</code></td>' +
            '<td><span class="badge bg-warning text-dark">Update</span></td>' +
            '<td>' + item.existing.equivalents.join(', ') + '</td>' +
            '<td>' + (item.rule.equivalents || []).join(', ') + '</td>' +
        '</tr>';
    });
    
    conflicts.forEach(function(item) {
        tableHtml += '<tr style="background: #fee2e2;">' +
            '<td><code class="fs-5">' + item.rule.base_char + '</code></td>' +
            '<td><span class="badge bg-danger">Conflict</span></td>' +
            '<td>' + item.existing.equivalents.join(', ') + '</td>' +
            '<td>' + (item.rule.equivalents || []).join(', ') + '</td>' +
        '</tr>';
    });
    
    if (tableHtml === '') {
        tableHtml = '<tr><td colspan="4" class="text-center text-muted py-4">No changes to apply</td></tr>';
    }
    
    document.getElementById('importChangesTable').innerHTML = tableHtml;
    document.getElementById('importConflictWarning').style.display = conflicts.length > 0 ? 'block' : 'none';
    
    window.pendingImportData = { newRules: newRules, updatedRules: updatedRules, conflicts: conflicts, mode: mode };
    
    document.getElementById('importStep1').style.display = 'none';
    document.getElementById('importStep2').style.display = 'block';
    document.getElementById('importApplyBtn').style.display = 'inline-block';
    
    var confirmInput = document.getElementById('importConfirmInput');
    var applyBtn = document.getElementById('importApplyBtn');
    
    confirmInput.value = '';
    applyBtn.disabled = true;
    
    confirmInput.addEventListener('input', function() {
        applyBtn.disabled = this.value.trim().toUpperCase() !== 'IMPORT';
    });
}

function backToImportStep1() {
    document.getElementById('importStep1').style.display = 'block';
    document.getElementById('importStep2').style.display = 'none';
    document.getElementById('importApplyBtn').style.display = 'none';
}

function executeNormImport() {
    var confirmInput = document.getElementById('importConfirmInput');
    if (confirmInput.value.trim().toUpperCase() !== 'IMPORT') {
        showToast('Please type IMPORT to confirm', 'warning');
        return;
    }
    
    if (!window.pendingImportData) {
        showToast('No import data available', 'error');
        return;
    }
    
    var data = window.pendingImportData;
    var appliedCount = 0;
    
    var allChanges = data.newRules.concat(data.updatedRules).concat(data.conflicts);
    
    allChanges.forEach(function(item) {
        var char = mockData.baseCharacterLibrary.find(function(c) { return c.base === item.rule.base_char; });
        if (!char) return;
        
        var beforeState = {
            equivalents: char.equivalents.slice(),
            scope: char.scope.slice(),
            enabled: char.enabled
        };
        
        if (data.mode === 'merge') {
            (item.rule.equivalents || []).forEach(function(eq) {
                if (char.equivalents.indexOf(eq) === -1) {
                    char.equivalents.push(eq);
                }
            });
        } else {
            char.equivalents = item.rule.equivalents || [];
        }
        
        char.scope = item.rule.applies_to || char.scope;
        char.enabled = item.rule.status === 'enabled';
        char.risk = computeRisk(char);
        char.updated = new Date().toLocaleDateString('en-GB').replace(/\//g, '-');
        
        appliedCount++;
    });
    
    logAuditEvent('NORMALISATION_RULES_IMPORTED', {
        entityType: 'normalisation_library',
        mode: data.mode,
        newRulesCount: data.newRules.length,
        updatedRulesCount: data.updatedRules.length,
        conflictsCount: data.conflicts.length,
        totalApplied: appliedCount,
        affectedCharacters: allChanges.map(function(c) { return c.rule.base_char; }),
        before: {
            totalRules: mockData.baseCharacterLibrary.length,
            timestamp: new Date().toISOString()
        },
        after: {
            totalRules: mockData.baseCharacterLibrary.length,
            appliedChanges: appliedCount,
            timestamp: new Date().toISOString()
        }
    });
    
    var modal = bootstrap.Modal.getInstance(document.getElementById('importNormModal'));
    modal.hide();
    
    window.pendingImportData = null;
    window.parsedImportRules = null;
    
    MessageEnforcementService.hotReloadRules();
    
    if (window.NormalisationEnforcementAPI) {
        NormalisationEnforcementAPI.invalidateCache();
    }
    
    SecurityComplianceControlsService.renderAllTabs();
    
    showToast('Successfully imported ' + appliedCount + ' rules', 'success');
}

var NormalisationLibrary = (function() {
    function normalise(input, options) {
        options = options || {};
        var scope = options.scope || 'all';
        var targetBase = options.base || null;
        
        var chars = mockData.baseCharacterLibrary.filter(function(c) {
            if (!c.enabled) return false;
            if (targetBase !== null) return c.base === targetBase;
            if (scope === 'all') return true;
            return c.scope.indexOf(scope) !== -1;
        });
        
        var result = input;
        var transformations = [];
        
        chars.forEach(function(char) {
            if (char.equivalents && char.equivalents.length > 0) {
                char.equivalents.forEach(function(eq) {
                    if (result.indexOf(eq) !== -1) {
                        var regex = new RegExp(escapeRegex(eq), 'g');
                        var oldResult = result;
                        result = result.replace(regex, char.base);
                        if (result !== oldResult) {
                            transformations.push({
                                original: eq,
                                replacement: char.base,
                                ruleName: 'Base: ' + char.base,
                                base: char.base
                            });
                        }
                    }
                });
            }
        });
        
        return {
            original: input,
            normalised: result,
            transformations: transformations,
            charsApplied: chars.map(function(c) { return c.base; })
        };
    }
    
    function escapeRegex(string) {
        return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }
    
    function getEnabledCharacters(scope) {
        return mockData.baseCharacterLibrary.filter(function(c) {
            if (!c.enabled) return false;
            if (!scope || scope === 'all') return true;
            return c.scope.indexOf(scope) !== -1;
        });
    }
    
    function getTotalEquivalents() {
        return mockData.baseCharacterLibrary.reduce(function(sum, char) {
            return sum + char.equivalents.length;
        }, 0);
    }
    
    function getCharacter(base) {
        return mockData.baseCharacterLibrary.find(function(c) { return c.base === base; });
    }
    
    return {
        normalise: normalise,
        getEnabledCharacters: getEnabledCharacters,
        getTotalEquivalents: getTotalEquivalents,
        getCharacter: getCharacter
    };
})();

var SenderIdMatchingService = (function() {
    var SUBSTITUTION_MAP = {
        '0': ['O', 'o'],
        'O': ['0'],
        'o': ['0'],
        '1': ['I', 'i', 'L', 'l', '|'],
        'I': ['1', 'l', '|'],
        'i': ['1', 'L', 'l', '|'],
        'L': ['1', 'I', 'i', '|'],
        'l': ['1', 'I', 'i', '|'],
        '5': ['S', 's'],
        'S': ['5'],
        's': ['5'],
        '3': ['E', 'e'],
        'E': ['3'],
        'e': ['3'],
        '4': ['A', 'a'],
        'A': ['4'],
        'a': ['4'],
        '8': ['B', 'b'],
        'B': ['8'],
        'b': ['8'],
        '6': ['G', 'g'],
        'G': ['6'],
        'g': ['6'],
        '7': ['T', 't'],
        'T': ['7'],
        't': ['7']
    };

    function normalise(senderId) {
        if (!senderId) return '';
        return senderId.toUpperCase().replace(/[\s\-_\.]/g, '');
    }

    function generateVariants(baseSenderId) {
        var normalised = normalise(baseSenderId);
        var variants = [normalised];
        
        for (var i = 0; i < normalised.length; i++) {
            var char = normalised[i];
            if (SUBSTITUTION_MAP[char]) {
                var subs = SUBSTITUTION_MAP[char];
                var newVariants = [];
                variants.forEach(function(v) {
                    subs.forEach(function(sub) {
                        var newVariant = v.substring(0, i) + sub.toUpperCase() + v.substring(i + 1);
                        if (newVariants.indexOf(newVariant) === -1) {
                            newVariants.push(newVariant);
                        }
                    });
                });
                variants = variants.concat(newVariants);
            }
        }
        
        return [...new Set(variants)];
    }

    function matches(inputSenderId, baseSenderId, applyNormalisation) {
        var normalisedInput = normalise(inputSenderId);
        var normalisedBase = normalise(baseSenderId);
        
        if (normalisedInput === normalisedBase) {
            return { matched: true, reason: 'exact_match', variant: normalisedBase };
        }
        
        if (applyNormalisation) {
            var variants = generateVariants(normalisedBase);
            for (var i = 0; i < variants.length; i++) {
                if (normalisedInput === variants[i]) {
                    return { matched: true, reason: 'variant_match', variant: variants[i] };
                }
            }
        }
        
        return { matched: false, reason: null, variant: null };
    }

    function buildRegexPattern(baseSenderId) {
        var normalised = normalise(baseSenderId);
        var pattern = '';
        
        for (var i = 0; i < normalised.length; i++) {
            var char = normalised[i];
            if (SUBSTITUTION_MAP[char]) {
                var allChars = [char].concat(SUBSTITUTION_MAP[char]);
                pattern += '[' + allChars.join('') + ']';
            } else {
                pattern += char;
            }
        }
        
        return new RegExp('^' + pattern + '$', 'i');
    }

    return {
        normalise: normalise,
        generateVariants: generateVariants,
        matches: matches,
        buildRegexPattern: buildRegexPattern
    };
})();

window.SenderIdMatchingService = SenderIdMatchingService;

var senderIdRulesStore = [];

function showAddSenderIdRuleModal() {
    document.getElementById('senderid-modal-title').textContent = 'Add SenderID Rule';
    document.getElementById('senderid-save-btn-text').textContent = 'Save Rule';
    document.getElementById('senderid-rule-id').value = '';
    document.getElementById('senderid-rule-name').value = '';
    document.getElementById('senderid-base-value').value = '';
    document.getElementById('senderid-type-block').checked = true;
    document.getElementById('senderid-category').value = '';
    document.getElementById('senderid-apply-normalisation').checked = true;
    
    var modal = new bootstrap.Modal(document.getElementById('senderIdRuleModal'));
    modal.show();
}

function editSenderIdRule(ruleId) {
    var rules = JSON.parse(localStorage.getItem('senderIdRules') || '[]');
    if (rules.length === 0) {
        rules = [
            { id: 'SID-001', name: 'Block HSBC Impersonation', baseSenderId: 'HSBC', ruleType: 'block', category: 'bank_impersonation', applyNormalisation: true, status: 'active', createdBy: 'admin@quicksms.co.uk', createdAt: '15-01-2026 09:30', updatedAt: '15-01-2026 09:30' },
            { id: 'SID-002', name: 'Block Barclays Impersonation', baseSenderId: 'BARCLAYS', ruleType: 'block', category: 'bank_impersonation', applyNormalisation: true, status: 'active', createdBy: 'admin@quicksms.co.uk', createdAt: '15-01-2026 10:15', updatedAt: '20-01-2026 14:22' },
            { id: 'SID-003', name: 'Flag HMRC Messages', baseSenderId: 'HMRC', ruleType: 'flag', category: 'government', applyNormalisation: true, status: 'active', createdBy: 'compliance@quicksms.co.uk', createdAt: '12-01-2026 11:00', updatedAt: '12-01-2026 11:00' },
            { id: 'SID-004', name: 'Block Lottery Sender', baseSenderId: 'LOTTERY', ruleType: 'block', category: 'lottery_prize', applyNormalisation: true, status: 'active', createdBy: 'admin@quicksms.co.uk', createdAt: '10-01-2026 08:45', updatedAt: '25-01-2026 16:30' },
            { id: 'SID-005', name: 'Flag Premium Rate', baseSenderId: 'PREMIUM', ruleType: 'flag', category: 'premium_rate', applyNormalisation: false, status: 'disabled', createdBy: 'admin@quicksms.co.uk', createdAt: '05-01-2026 14:00', updatedAt: '28-01-2026 09:15' }
        ];
    }
    
    var rule = rules.find(r => r.id === ruleId);
    if (!rule) {
        console.error('Rule not found:', ruleId);
        return;
    }
    
    document.getElementById('senderid-modal-title').textContent = 'Edit SenderID Rule';
    document.getElementById('senderid-save-btn-text').textContent = 'Update Rule';
    document.getElementById('senderid-rule-id').value = rule.id;
    document.getElementById('senderid-rule-name').value = rule.name;
    document.getElementById('senderid-base-value').value = rule.baseSenderId;
    document.getElementById('senderid-type-' + rule.ruleType).checked = true;
    document.getElementById('senderid-category').value = rule.category;
    document.getElementById('senderid-apply-normalisation').checked = rule.applyNormalisation;
    
    var modal = new bootstrap.Modal(document.getElementById('senderIdRuleModal'));
    modal.show();
}

function viewSenderIdRule(ruleId) {
    var rules = JSON.parse(localStorage.getItem('senderIdRules') || '[]');
    if (rules.length === 0) {
        rules = [
            { id: 'SID-001', name: 'Block HSBC Impersonation', baseSenderId: 'HSBC', ruleType: 'block', category: 'bank_impersonation', applyNormalisation: true, status: 'active', createdBy: 'admin@quicksms.co.uk', createdAt: '15-01-2026 09:30', updatedAt: '15-01-2026 09:30' },
            { id: 'SID-002', name: 'Block Barclays Impersonation', baseSenderId: 'BARCLAYS', ruleType: 'block', category: 'bank_impersonation', applyNormalisation: true, status: 'active', createdBy: 'admin@quicksms.co.uk', createdAt: '15-01-2026 10:15', updatedAt: '20-01-2026 14:22' },
            { id: 'SID-003', name: 'Flag HMRC Messages', baseSenderId: 'HMRC', ruleType: 'flag', category: 'government', applyNormalisation: true, status: 'active', createdBy: 'compliance@quicksms.co.uk', createdAt: '12-01-2026 11:00', updatedAt: '12-01-2026 11:00' },
            { id: 'SID-004', name: 'Block Lottery Sender', baseSenderId: 'LOTTERY', ruleType: 'block', category: 'lottery_prize', applyNormalisation: true, status: 'active', createdBy: 'admin@quicksms.co.uk', createdAt: '10-01-2026 08:45', updatedAt: '25-01-2026 16:30' },
            { id: 'SID-005', name: 'Flag Premium Rate', baseSenderId: 'PREMIUM', ruleType: 'flag', category: 'premium_rate', applyNormalisation: false, status: 'disabled', createdBy: 'admin@quicksms.co.uk', createdAt: '05-01-2026 14:00', updatedAt: '28-01-2026 09:15' }
        ];
    }
    
    var rule = rules.find(r => r.id === ruleId);
    if (!rule) return;
    
    var categoryLabels = {
        'bank_impersonation': 'Bank Impersonation',
        'government': 'Government',
        'lottery_prize': 'Lottery/Prize',
        'brand_abuse': 'Brand Abuse',
        'premium_rate': 'Premium Rate',
        'other': 'Other'
    };
    
    var variants = SenderIdMatchingService.generateVariants(rule.baseSenderId);
    
    var html = '<div class="mb-3"><strong style="color: #1e3a5f;">Rule Details</strong></div>' +
        '<table class="table table-sm">' +
        '<tr><td class="text-muted" style="width: 40%;">Rule ID</td><td>' + rule.id + '</td></tr>' +
        '<tr><td class="text-muted">Rule Name</td><td>' + rule.name + '</td></tr>' +
        '<tr><td class="text-muted">Base SenderID</td><td><code>' + rule.baseSenderId + '</code></td></tr>' +
        '<tr><td class="text-muted">Rule Type</td><td>' + (rule.ruleType === 'block' ? '<span class="badge bg-danger">Block</span>' : '<span class="badge bg-warning text-dark">Flag</span>') + '</td></tr>' +
        '<tr><td class="text-muted">Category</td><td>' + (categoryLabels[rule.category] || rule.category) + '</td></tr>' +
        '<tr><td class="text-muted">Normalisation</td><td>' + (rule.applyNormalisation ? '<span class="badge bg-success">Enabled</span>' : '<span class="badge bg-secondary">Disabled</span>') + '</td></tr>' +
        '<tr><td class="text-muted">Status</td><td>' + (rule.status === 'active' ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Disabled</span>') + '</td></tr>' +
        '<tr><td class="text-muted">Created By</td><td>' + rule.createdBy + '</td></tr>' +
        '<tr><td class="text-muted">Created At</td><td>' + rule.createdAt + '</td></tr>' +
        '<tr><td class="text-muted">Last Updated</td><td>' + rule.updatedAt + '</td></tr>' +
        '</table>' +
        '<div class="mt-3 p-2 rounded" style="background: #f8f9fc; border: 1px solid #e9ecef;">' +
        '<small class="text-muted d-block mb-1"><strong>Detected Variants (' + variants.length + ')</strong></small>' +
        '<div style="font-size: 0.75rem; max-height: 80px; overflow-y: auto;">' +
        variants.slice(0, 20).map(function(v) { return '<code class="me-1 mb-1 d-inline-block" style="background: #e9ecef; padding: 0.1rem 0.3rem; border-radius: 3px;">' + v + '</code>'; }).join('') +
        (variants.length > 20 ? '<span class="text-muted">... and ' + (variants.length - 20) + ' more</span>' : '') +
        '</div></div>';
    
    document.getElementById('senderid-view-content').innerHTML = html;
    var modal = new bootstrap.Modal(document.getElementById('senderIdViewModal'));
    modal.show();
}

function saveSenderIdRule() {
    var ruleId = document.getElementById('senderid-rule-id').value;
    var name = document.getElementById('senderid-rule-name').value.trim();
    var baseSenderId = document.getElementById('senderid-base-value').value.trim().toUpperCase();
    var ruleType = document.querySelector('input[name="senderid-rule-type"]:checked').value;
    var category = document.getElementById('senderid-category').value;
    var applyNormalisation = document.getElementById('senderid-apply-normalisation').checked;
    
    if (!name || !baseSenderId || !category) {
        alert('Please fill in all required fields.');
        return;
    }
    
    var rules = JSON.parse(localStorage.getItem('senderIdRules') || '[]');
    if (rules.length === 0) {
        rules = [
            { id: 'SID-001', name: 'Block HSBC Impersonation', baseSenderId: 'HSBC', ruleType: 'block', category: 'bank_impersonation', applyNormalisation: true, status: 'active', createdBy: 'admin@quicksms.co.uk', createdAt: '15-01-2026 09:30', updatedAt: '15-01-2026 09:30' },
            { id: 'SID-002', name: 'Block Barclays Impersonation', baseSenderId: 'BARCLAYS', ruleType: 'block', category: 'bank_impersonation', applyNormalisation: true, status: 'active', createdBy: 'admin@quicksms.co.uk', createdAt: '15-01-2026 10:15', updatedAt: '20-01-2026 14:22' },
            { id: 'SID-003', name: 'Flag HMRC Messages', baseSenderId: 'HMRC', ruleType: 'flag', category: 'government', applyNormalisation: true, status: 'active', createdBy: 'compliance@quicksms.co.uk', createdAt: '12-01-2026 11:00', updatedAt: '12-01-2026 11:00' },
            { id: 'SID-004', name: 'Block Lottery Sender', baseSenderId: 'LOTTERY', ruleType: 'block', category: 'lottery_prize', applyNormalisation: true, status: 'active', createdBy: 'admin@quicksms.co.uk', createdAt: '10-01-2026 08:45', updatedAt: '25-01-2026 16:30' },
            { id: 'SID-005', name: 'Flag Premium Rate', baseSenderId: 'PREMIUM', ruleType: 'flag', category: 'premium_rate', applyNormalisation: false, status: 'disabled', createdBy: 'admin@quicksms.co.uk', createdAt: '05-01-2026 14:00', updatedAt: '28-01-2026 09:15' }
        ];
    }
    
    var now = new Date();
    var timestamp = now.toLocaleDateString('en-GB').replace(/\//g, '-') + ' ' + now.toTimeString().slice(0, 5);
    var beforeState = null;
    var isEdit = !!ruleId;
    
    if (isEdit) {
        var existingIndex = rules.findIndex(r => r.id === ruleId);
        if (existingIndex !== -1) {
            beforeState = JSON.parse(JSON.stringify(rules[existingIndex]));
            rules[existingIndex] = {
                ...rules[existingIndex],
                name: name,
                baseSenderId: baseSenderId,
                ruleType: ruleType,
                category: category,
                applyNormalisation: applyNormalisation,
                updatedAt: timestamp
            };
        }
    } else {
        var newId = 'SID-' + String(rules.length + 1).padStart(3, '0');
        rules.push({
            id: newId,
            name: name,
            baseSenderId: baseSenderId,
            ruleType: ruleType,
            category: category,
            applyNormalisation: applyNormalisation,
            status: 'active',
            createdBy: currentAdmin.email,
            createdAt: timestamp,
            updatedAt: timestamp
        });
        ruleId = newId;
    }
    
    localStorage.setItem('senderIdRules', JSON.stringify(rules));
    
    logAuditEvent(isEdit ? 'SENDERID_RULE_UPDATED' : 'SENDERID_RULE_CREATED', {
        ruleId: ruleId,
        ruleName: name,
        baseSenderId: baseSenderId,
        ruleType: ruleType,
        category: category,
        applyNormalisation: applyNormalisation,
        before: beforeState,
        after: { name: name, baseSenderId: baseSenderId, ruleType: ruleType, category: category, applyNormalisation: applyNormalisation },
        entityType: 'senderid_rule'
    });
    
    bootstrap.Modal.getInstance(document.getElementById('senderIdRuleModal')).hide();
    SecurityComplianceControlsService.renderAllTabs();
    
    console.log('[SenderIdControls] Rule saved:', ruleId);
}

function toggleSenderIdRuleStatus(ruleId, newStatus) {
    var rules = JSON.parse(localStorage.getItem('senderIdRules') || '[]');
    if (rules.length === 0) {
        rules = [
            { id: 'SID-001', name: 'Block HSBC Impersonation', baseSenderId: 'HSBC', ruleType: 'block', category: 'bank_impersonation', applyNormalisation: true, status: 'active', createdBy: 'admin@quicksms.co.uk', createdAt: '15-01-2026 09:30', updatedAt: '15-01-2026 09:30' },
            { id: 'SID-002', name: 'Block Barclays Impersonation', baseSenderId: 'BARCLAYS', ruleType: 'block', category: 'bank_impersonation', applyNormalisation: true, status: 'active', createdBy: 'admin@quicksms.co.uk', createdAt: '15-01-2026 10:15', updatedAt: '20-01-2026 14:22' },
            { id: 'SID-003', name: 'Flag HMRC Messages', baseSenderId: 'HMRC', ruleType: 'flag', category: 'government', applyNormalisation: true, status: 'active', createdBy: 'compliance@quicksms.co.uk', createdAt: '12-01-2026 11:00', updatedAt: '12-01-2026 11:00' },
            { id: 'SID-004', name: 'Block Lottery Sender', baseSenderId: 'LOTTERY', ruleType: 'block', category: 'lottery_prize', applyNormalisation: true, status: 'active', createdBy: 'admin@quicksms.co.uk', createdAt: '10-01-2026 08:45', updatedAt: '25-01-2026 16:30' },
            { id: 'SID-005', name: 'Flag Premium Rate', baseSenderId: 'PREMIUM', ruleType: 'flag', category: 'premium_rate', applyNormalisation: false, status: 'disabled', createdBy: 'admin@quicksms.co.uk', createdAt: '05-01-2026 14:00', updatedAt: '28-01-2026 09:15' }
        ];
    }
    
    var ruleIndex = rules.findIndex(r => r.id === ruleId);
    if (ruleIndex !== -1) {
        var beforeStatus = rules[ruleIndex].status;
        rules[ruleIndex].status = newStatus;
        rules[ruleIndex].updatedAt = new Date().toLocaleDateString('en-GB').replace(/\//g, '-') + ' ' + new Date().toTimeString().slice(0, 5);
        localStorage.setItem('senderIdRules', JSON.stringify(rules));
        
        logAuditEvent('SENDERID_RULE_STATUS_CHANGED', {
            ruleId: ruleId,
            ruleName: rules[ruleIndex].name,
            beforeStatus: beforeStatus,
            afterStatus: newStatus,
            entityType: 'senderid_rule'
        });
        
        SecurityComplianceControlsService.renderAllTabs();
    }
}

function showDeleteConfirmation(ruleId, ruleType) {
    document.getElementById('delete-rule-id').value = ruleId;
    document.getElementById('delete-rule-type').value = ruleType;
    document.getElementById('confirm-delete-message').textContent = 'Are you sure you want to delete rule ' + ruleId + '?';
    var modal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
    modal.show();
}

function confirmDeleteRule() {
    var ruleId = document.getElementById('delete-rule-id').value;
    var ruleType = document.getElementById('delete-rule-type').value;
    
    if (currentAdmin.role !== 'super_admin') {
        alert('Only Super Admins can delete rules.');
        return;
    }
    
    if (ruleType === 'senderid') {
        var rules = JSON.parse(localStorage.getItem('senderIdRules') || '[]');
        var deletedRule = rules.find(r => r.id === ruleId);
        rules = rules.filter(r => r.id !== ruleId);
        localStorage.setItem('senderIdRules', JSON.stringify(rules));
        
        logAuditEvent('SENDERID_RULE_DELETED', {
            ruleId: ruleId,
            ruleName: deletedRule ? deletedRule.name : null,
            before: deletedRule,
            after: null,
            entityType: 'senderid_rule'
        });
    } else if (ruleType === 'content') {
        SecurityComplianceControlsService.deleteContentRuleById(ruleId);
    } else if (ruleType === 'url') {
        SecurityComplianceControlsService.deleteUrlRuleById(ruleId);
    }
    
    bootstrap.Modal.getInstance(document.getElementById('confirmDeleteModal')).hide();
    SecurityComplianceControlsService.renderAllTabs();
}

function showAddContentRuleModal() {
    SecurityComplianceControlsService.showAddContentRuleModal();
}

function editContentRule(ruleId) {
    SecurityComplianceControlsService.editContentRule(ruleId);
}

function viewContentRule(ruleId) {
    SecurityComplianceControlsService.viewContentRule(ruleId);
}

function toggleContentRuleStatus(ruleId) {
    SecurityComplianceControlsService.toggleContentRuleStatus(ruleId);
}

function deleteContentRule(ruleId) {
    SecurityComplianceControlsService.deleteContentRule(ruleId);
}

function saveContentRule() {
    SecurityComplianceControlsService.saveContentRule();
}

function updateContentMatchInputLabel() {
    SecurityComplianceControlsService.updateContentMatchInputLabel();
}

function resetContentFilters() {
    SecurityComplianceControlsService.resetContentFilters();
}

function toggleContentActionMenu(btn, ruleId) {
    SecurityComplianceControlsService.toggleContentActionMenu(btn, ruleId);
}

function showAddUrlRuleModal() {
    SecurityComplianceControlsService.showAddUrlRuleModal();
}

function editUrlRule(ruleId) {
    SecurityComplianceControlsService.editUrlRule(ruleId);
}

function viewUrlRule(ruleId) {
    SecurityComplianceControlsService.viewUrlRule(ruleId);
}

function toggleUrlRuleStatus(ruleId) {
    SecurityComplianceControlsService.toggleUrlRuleStatus(ruleId);
}

function deleteUrlRule(ruleId) {
    SecurityComplianceControlsService.deleteUrlRule(ruleId);
}

function saveUrlRule() {
    SecurityComplianceControlsService.saveUrlRule();
}

function updateUrlPatternLabel() {
    SecurityComplianceControlsService.updateUrlPatternLabel();
}

function resetUrlFilters() {
    SecurityComplianceControlsService.resetUrlFilters();
}

function toggleUrlActionMenu(btn, ruleId) {
    SecurityComplianceControlsService.toggleUrlActionMenu(btn, ruleId);
}

function saveDomainAgeSettings() {
    SecurityComplianceControlsService.saveDomainAgeSettings();
}

function showAddDomainAgeExceptionModal() {
    SecurityComplianceControlsService.showAddDomainAgeExceptionModal();
}

function saveException() {
    SecurityComplianceControlsService.saveException();
}

function removeDomainAgeException(exceptionId) {
    SecurityComplianceControlsService.removeDomainAgeException(exceptionId);
}

function showAddNormRuleModal() {
    console.log('[SecurityComplianceControls] TODO: Implement Add Normalisation Rule modal');
    alert('Add Normalisation Rule - Coming Soon');
}

function resetSenderIdFilters() {
    document.getElementById('senderid-filter-status').value = '';
    document.getElementById('senderid-filter-type').value = '';
    document.getElementById('senderid-filter-category').value = '';
    document.getElementById('senderid-search').value = '';
}

function resetContentFilters() {
    document.getElementById('content-filter-status').value = '';
    document.getElementById('content-filter-category').value = '';
    document.getElementById('content-search').value = '';
}

function resetUrlFilters() {
    document.getElementById('url-filter-type').value = '';
    document.getElementById('url-filter-category').value = '';
    document.getElementById('url-search').value = '';
}

function toggleQuarantineFilterPanel() {
    SecurityComplianceControlsService.toggleQuarantineFilterPanel();
}

function applyQuarantineFilters() {
    SecurityComplianceControlsService.applyQuarantineFilters();
}

function resetQuarantineFilters() {
    SecurityComplianceControlsService.resetQuarantineFilters();
}

function clearAllQuarantineFilters() {
    SecurityComplianceControlsService.clearAllQuarantineFilters();
}

function removeQuarantineFilter(filterType, value) {
    SecurityComplianceControlsService.removeQuarantineFilter(filterType, value);
}

function toggleQuarantineTileFilter(filterType) {
    SecurityComplianceControlsService.toggleQuarantineTileFilter(filterType);
}

function showBulkReleaseConfirmation() {
    SecurityComplianceControlsService.showBulkReleaseConfirmation();
}

function showBulkBlockConfirmation() {
    SecurityComplianceControlsService.showBulkBlockConfirmation();
}

function executeBulkAction() {
    SecurityComplianceControlsService.executeBulkAction();
}

function resetNormFilters() {
    document.getElementById('norm-filter-status').value = '';
    document.getElementById('norm-filter-type').value = '';
    document.getElementById('norm-search').value = '';
}

function bulkReleaseQuarantine() {
    var selected = document.querySelectorAll('.quarantine-checkbox:checked');
    if (selected.length === 0) {
        alert('Please select messages to release.');
        return;
    }
    console.log('[SecurityComplianceControls] TODO: Implement bulk release');
    alert('Bulk release ' + selected.length + ' messages - Coming Soon');
}

function viewQuarantinedMessage(msgId) {
    SecurityComplianceControlsService.viewQuarantinedMessage(msgId);
}

function releaseQuarantinedMessage(msgId) {
    SecurityComplianceControlsService.releaseQuarantinedMessage(msgId);
}

function blockQuarantinedMessage(msgId) {
    SecurityComplianceControlsService.blockQuarantinedMessage(msgId);
}

function bulkReleaseQuarantine() {
    SecurityComplianceControlsService.bulkReleaseQuarantine();
}

function bulkBlockQuarantine() {
    SecurityComplianceControlsService.bulkBlockQuarantine();
}

function bulkRejectQuarantine() {
    SecurityComplianceControlsService.bulkBlockQuarantine();
}

function addQuarantineNote() {
    SecurityComplianceControlsService.addQuarantineNote();
}

function copyQuarantineMessage() {
    SecurityComplianceControlsService.copyQuarantineMessage();
}

function toggleMessageExpand() {
    SecurityComplianceControlsService.toggleMessageExpand();
}

function addExceptionFromQuarantine() {
    SecurityComplianceControlsService.addExceptionFromQuarantine();
}

function createRuleFromQuarantine() {
    SecurityComplianceControlsService.createRuleFromQuarantine();
}

function releaseQuarantinedMessageFromModal() {
    SecurityComplianceControlsService.releaseQuarantinedMessageFromModal();
}

function blockQuarantinedMessageFromModal() {
    SecurityComplianceControlsService.blockQuarantinedMessageFromModal();
}

function toggleAntiSpamRepeat() {
    SecurityComplianceControlsService.toggleAntiSpamRepeat();
}

function updateAntiSpamWindow() {
    SecurityComplianceControlsService.updateAntiSpamWindow();
}

document.addEventListener('DOMContentLoaded', function() {
    SecurityComplianceControlsService.initialize();
});
</script>
@endpush
