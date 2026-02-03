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
    margin-bottom: 0.75rem;
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

/* URL Controls Filter Pill Button (matches Quarantine Review) */
.url-filter-pill-btn {
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
.url-filter-pill-btn:hover {
    background: rgba(30, 58, 95, 0.08);
    border-color: #1e3a5f;
    color: #1e3a5f;
}
.url-filter-pill-btn.active {
    background: rgba(30, 58, 95, 0.12);
    border-color: #1e3a5f;
    color: #1e3a5f;
}
.url-filter-pill-btn i {
    font-size: 0.8rem;
    color: #1e3a5f;
}

/* URL Controls Filter Panel (matches Quarantine Review) */
.url-controls-filter-panel {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    margin-bottom: 1rem;
    overflow: hidden;
}
.url-controls-filter-panel .filter-body {
    padding: 1rem;
}
.url-controls-filter-panel .filter-row {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1rem;
}
.url-controls-filter-panel .filter-group {
    flex: 1;
    min-width: 150px;
}
.url-controls-filter-panel .filter-group label {
    display: block;
    font-size: 0.75rem;
    font-weight: 600;
    color: #495057;
    margin-bottom: 0.35rem;
}
.url-controls-filter-panel .filter-group select {
    width: 100%;
    padding: 0.375rem 0.5rem;
    font-size: 0.85rem;
    border: 1px solid #ced4da;
    border-radius: 4px;
}
.url-controls-filter-panel .filter-actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
    padding-top: 0.5rem;
    border-top: 1px solid #e9ecef;
}
.url-controls-filter-panel .btn-reset {
    background: transparent;
    border: 1px solid #6c757d;
    color: #6c757d;
    font-size: 0.8rem;
    padding: 0.35rem 0.75rem;
    border-radius: 4px;
}
.url-controls-filter-panel .btn-reset:hover {
    background: #f1f3f5;
}
.url-controls-filter-panel .btn-apply {
    background: #1e3a5f;
    border: 1px solid #1e3a5f;
    color: white;
    font-size: 0.8rem;
    padding: 0.35rem 0.75rem;
    border-radius: 4px;
}
.url-controls-filter-panel .btn-apply:hover {
    background: #152a47;
}

/* Generic transparent dark blue accent button */
.sec-pill-btn {
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
.sec-pill-btn:hover {
    background: rgba(30, 58, 95, 0.08);
    border-color: #1e3a5f;
    color: #1e3a5f;
}
.sec-pill-btn i {
    font-size: 0.8rem;
    color: #1e3a5f;
}

/* Primary CTA dark blue button */
.sec-primary-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: #1e3a5f;
    border: 1px solid #1e3a5f;
    color: #fff;
    font-weight: 500;
    font-size: 0.875rem;
    padding: 0.375rem 1rem;
    border-radius: 6px;
    transition: all 0.2s;
    cursor: pointer;
}
.sec-primary-btn:hover {
    background: #162d4a;
    border-color: #162d4a;
    color: #fff;
}
.sec-primary-btn i {
    font-size: 0.8rem;
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

/* SenderID Filter Panel - matching Quarantine pattern */
.senderid-filter-panel {
    background: #fff;
    border-radius: 0.5rem;
    margin-bottom: 1rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    display: none;
}
.senderid-filter-panel .filter-body {
    padding: 1rem 1.25rem;
    background: #f8fafc;
    border-radius: 0.5rem;
}
.senderid-filter-panel .filter-row {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1rem;
}
.senderid-filter-panel .filter-group {
    display: flex;
    flex-direction: column;
    min-width: 160px;
    flex: 1;
    max-width: 200px;
}
.senderid-filter-panel .filter-group label {
    font-size: 0.75rem;
    font-weight: 600;
    color: #6c757d;
    margin-bottom: 0.375rem;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}
.senderid-filter-panel .filter-group select {
    font-size: 0.85rem;
    padding: 0.375rem 0.75rem;
    border: 1px solid #ced4da;
    border-radius: 4px;
}
.senderid-filter-panel .filter-actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
    padding-top: 0.75rem;
    border-top: 1px solid #e9ecef;
}

/* Exemptions Filter Panel - Transparent dark blue accent */
.exemptions-filter-panel {
    background: transparent;
    border-bottom: 1px solid rgba(30, 58, 95, 0.12);
    display: none;
}
.exemptions-filter-panel .filter-body {
    padding: 0.75rem 1rem;
    background: rgba(30, 58, 95, 0.03);
}
.exemptions-filter-panel .filter-row {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-bottom: 0.75rem;
}
.exemptions-filter-panel .filter-group {
    display: flex;
    flex-direction: column;
    min-width: 140px;
    flex: 1;
    max-width: 180px;
}
.exemptions-filter-panel .filter-group label {
    font-size: 0.7rem;
    font-weight: 600;
    color: #1e3a5f;
    margin-bottom: 0.25rem;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}
.exemptions-filter-panel .filter-group select {
    font-size: 0.8rem;
    padding: 0.35rem 0.5rem;
    border: 1px solid rgba(30, 58, 95, 0.2);
    border-radius: 4px;
    background: #fff;
}
.exemptions-filter-panel .filter-group select:focus {
    border-color: #1e3a5f;
    outline: none;
    box-shadow: 0 0 0 2px rgba(30, 58, 95, 0.1);
}
.exemptions-filter-panel .filter-actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
    padding-top: 0.5rem;
}

/* Exemptions Compact Table */
.exemptions-compact-table thead th {
    padding: 0.4rem 0.35rem;
    font-size: 0.7rem;
    font-weight: 600;
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
    white-space: nowrap;
}
.exemptions-compact-table tbody td {
    padding: 0.4rem 0.35rem;
    font-size: 0.75rem;
    border-bottom: 1px solid #f1f3f5;
    vertical-align: middle;
}

/* Content Filter Panel - matches exemptions filter panel */
.content-filter-panel,
.content-exemptions-filter-panel {
    background: transparent;
    border-bottom: 1px solid rgba(30, 58, 95, 0.12);
    display: none;
}
.content-filter-panel .filter-body,
.content-exemptions-filter-panel .filter-body {
    padding: 0.75rem 1rem;
    background: rgba(30, 58, 95, 0.03);
}
.content-filter-panel .filter-row,
.content-exemptions-filter-panel .filter-row {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-bottom: 0.75rem;
}
.content-filter-panel .filter-group,
.content-exemptions-filter-panel .filter-group {
    display: flex;
    flex-direction: column;
    min-width: 140px;
    flex: 1;
    max-width: 180px;
}
.content-filter-panel .filter-group label,
.content-exemptions-filter-panel .filter-group label {
    font-size: 0.7rem;
    font-weight: 600;
    color: #1e3a5f;
    margin-bottom: 0.25rem;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}
.content-filter-panel .filter-group select,
.content-exemptions-filter-panel .filter-group select {
    font-size: 0.8rem;
    padding: 0.35rem 0.5rem;
    border: 1px solid rgba(30, 58, 95, 0.2);
    border-radius: 4px;
    background: #fff;
}
.content-filter-panel .filter-group select:focus,
.content-exemptions-filter-panel .filter-group select:focus {
    border-color: #1e3a5f;
    outline: none;
    box-shadow: 0 0 0 2px rgba(30, 58, 95, 0.1);
}
.content-filter-panel .filter-actions,
.content-exemptions-filter-panel .filter-actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
    padding-top: 0.5rem;
}

/* SenderID Sub-tabs styling */
#senderidSubTabs .nav-link,
#contentSubTabs .nav-link,
#urlSubTabs .nav-link {
    border-radius: 0;
    transition: all 0.2s;
}
#senderidSubTabs .nav-link:hover,
#contentSubTabs .nav-link:hover,
#urlSubTabs .nav-link:hover {
    color: #1e3a5f;
    background: rgba(30, 58, 95, 0.05);
}
#senderidSubTabs .nav-link.active,
#contentSubTabs .nav-link.active,
#urlSubTabs .nav-link.active {
    color: #1e3a5f;
    border-bottom: 3px solid #1e3a5f !important;
    background: transparent;
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
    padding: 0.35rem 0.3rem;
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.4px;
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
    padding: 0.3rem 0.3rem;
    font-size: 0.75rem;
    border-bottom: 1px solid #f1f3f5;
    vertical-align: middle;
    line-height: 1.3;
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
/* Collapse icon rotation for URL Rule test section */
#url-rule-test-collapse.show ~ .card-header #url-rule-test-collapse-icon,
[data-bs-target="#url-rule-test-collapse"][aria-expanded="true"] #url-rule-test-collapse-icon {
    transform: rotate(180deg);
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

.norm-char-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    gap: 12px;
}
.norm-char-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 16px 12px;
    background: #fff;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.2s ease;
}
.norm-char-card:hover {
    border-color: #1e3a5f;
    box-shadow: 0 4px 12px rgba(30, 58, 95, 0.15);
    transform: translateY(-2px);
}
.norm-char-card.disabled {
    opacity: 0.5;
    background: #f8fafc;
}
.norm-char-card .char-symbol {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 48px;
    background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%);
    color: white;
    font-size: 1.5rem;
    font-weight: 700;
    font-family: 'Courier New', monospace;
    border-radius: 8px;
    margin-bottom: 10px;
}
.norm-char-card.disabled .char-symbol {
    background: linear-gradient(135deg, #6b7280 0%, #9ca3af 100%);
}
.norm-char-card .char-info {
    text-align: center;
    width: 100%;
}
.norm-char-card .equiv-count {
    font-size: 0.8rem;
    color: #64748b;
    margin-bottom: 6px;
}
.norm-char-card .equiv-preview {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 3px;
    max-height: 50px;
    overflow: hidden;
}
.norm-char-card .equiv-preview .equiv-chip {
    min-width: 20px;
    height: 20px;
    font-size: 0.75rem;
    padding: 0 3px;
}
.norm-char-card .risk-indicator {
    display: inline-flex;
    align-items: center;
    padding: 2px 8px;
    border-radius: 10px;
    font-size: 0.65rem;
    font-weight: 600;
    margin-top: 8px;
}
.norm-char-card .risk-indicator.high {
    background: #fee2e2;
    color: #991b1b;
}
.norm-char-card .risk-indicator.medium {
    background: #fef3c7;
    color: #92400e;
}
.norm-char-card .risk-indicator.low {
    background: #dbeafe;
    color: #1e40af;
}
.norm-char-card .risk-indicator.none {
    background: #f3f4f6;
    color: #6b7280;
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
.action-menu-container {
    position: relative;
    display: inline-block;
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
.action-menu-dropdown {
    display: none !important;
    visibility: hidden;
    opacity: 0;
    position: absolute;
    right: 0;
    top: 100%;
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 6px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    min-width: 160px;
    z-index: 1050;
    padding: 0.35rem 0;
    margin-top: 2px;
}
.action-menu-dropdown.show {
    display: block !important;
    visibility: visible;
    opacity: 1;
}
.action-menu-dropdown a {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 0.85rem;
    font-size: 0.8rem;
    color: #495057;
    text-decoration: none;
    transition: all 0.15s;
    white-space: nowrap;
}
.action-menu-dropdown a:hover {
    background: rgba(30, 58, 95, 0.05);
    color: #1e3a5f;
}
.action-menu-dropdown a.text-danger:hover {
    background: rgba(220, 53, 69, 0.08);
    color: #dc3545;
}
.action-menu-dropdown .dropdown-divider {
    height: 0;
    margin: 0.25rem 0;
    border-top: 1px solid #e9ecef;
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
                <!-- Sub-tabs for SenderID Controls -->
                <ul class="nav nav-tabs mb-3" id="senderidSubTabs" role="tablist" style="border-bottom: 2px solid #e9ecef;">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="blocking-rules-tab" data-bs-toggle="tab" data-bs-target="#blocking-rules" type="button" role="tab" style="font-size: 0.85rem; font-weight: 600; color: #1e3a5f; border: none; border-bottom: 3px solid transparent; padding: 0.5rem 1rem;">
                            <i class="fas fa-ban me-1"></i> Blocking Rules
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="exemptions-tab" data-bs-toggle="tab" data-bs-target="#exemptions" type="button" role="tab" style="font-size: 0.85rem; font-weight: 600; color: #6c757d; border: none; border-bottom: 3px solid transparent; padding: 0.5rem 1rem;">
                            <i class="fas fa-shield-alt me-1"></i> Exemptions
                        </button>
                    </li>
                </ul>
                
                <div class="tab-content" id="senderidSubTabContent">
                    <!-- Blocking Rules Tab -->
                    <div class="tab-pane fade show active" id="blocking-rules" role="tabpanel">
                        <div class="sec-table-card">
                            <div class="sec-toolbar">
                                <div class="sec-search-box-left">
                                    <i class="fas fa-search"></i>
                                    <input type="text" class="form-control" placeholder="Search rules..." id="senderid-search">
                                </div>
                                <div class="sec-toolbar-actions">
                                    <button class="sec-pill-btn" type="button" id="senderid-filter-btn" onclick="toggleSenderIdFilterPanel()">
                                        <i class="fas fa-filter"></i>
                                        <span>Filter</span>
                                        <span class="badge bg-primary" id="senderid-filter-count" style="display: none; font-size: 0.7rem; padding: 0.2rem 0.4rem;">0</span>
                                    </button>
                                    <div class="btn-group">
                                        <button class="sec-primary-btn" type="button" onclick="showAddSenderIdRuleModal()">
                                            <i class="fas fa-plus"></i>
                                            <span>Add Rule</span>
                                        </button>
                                        <button type="button" class="sec-primary-btn dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false" style="padding-left: 0.5rem; padding-right: 0.5rem; border-left: 1px solid rgba(255,255,255,0.3);">
                                            <span class="visually-hidden">Toggle Dropdown</span>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" href="#" onclick="showImportRulesModal(); return false;"><i class="fas fa-file-import me-2"></i>Import Rules (CSV/XLSX)</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="senderid-filter-panel" id="senderid-filter-panel" style="display: none;">
                                <div class="filter-body">
                                    <div class="filter-row">
                                        <div class="filter-group">
                                            <label>Status</label>
                                            <select id="senderid-filter-status">
                                                <option value="">All Statuses</option>
                                                <option value="active">Active</option>
                                                <option value="disabled">Disabled</option>
                                            </select>
                                        </div>
                                        <div class="filter-group">
                                            <label>Rule Type</label>
                                            <select id="senderid-filter-type">
                                                <option value="">All Types</option>
                                                <option value="block">Block</option>
                                                <option value="flag">Flag (Quarantine)</option>
                                            </select>
                                        </div>
                                        <div class="filter-group">
                                            <label>Category</label>
                                            <select id="senderid-filter-category">
                                                <option value="">All Categories</option>
                                                <option value="government_healthcare">Government and Healthcare</option>
                                                <option value="banking_finance">Banking and Finance</option>
                                                <option value="delivery_logistics">Delivery and logistics</option>
                                                <option value="miscellaneous">Miscellaneous</option>
                                                <option value="generic">Generic</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="filter-actions">
                                        <button class="btn btn-sm btn-link text-secondary" onclick="resetSenderIdFilters()">Reset</button>
                                        <button class="btn btn-sm" style="background: #1e3a5f; color: #fff;" onclick="applySenderIdFilters()">Apply Filters</button>
                                    </div>
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
                    
                    <!-- Exemptions Tab -->
                    <div class="tab-pane fade" id="exemptions" role="tabpanel">
                        <div class="sec-table-card">
                            <div class="sec-toolbar" style="display: flex; align-items: center; gap: 1rem; padding: 0.75rem 1rem; border-bottom: 1px solid #e9ecef;">
                                <div class="sec-search-box-left" style="flex: 0 0 300px;">
                                    <i class="fas fa-search"></i>
                                    <input type="text" class="form-control" placeholder="Search SenderIDs..." id="exemptions-search" oninput="filterExemptionsTable()">
                                </div>
                                <div class="sec-toolbar-actions" style="display: flex; gap: 0.5rem; margin-left: auto;">
                                    <button class="sec-pill-btn" type="button" id="exemptions-filter-btn" onclick="toggleExemptionsFilterPanel()">
                                        <i class="fas fa-filter"></i>
                                        <span>Filter</span>
                                        <span class="badge" id="exemptions-filter-count" style="display: none; background: #1e3a5f; font-size: 0.65rem; padding: 0.15rem 0.35rem; margin-left: 0.25rem;">0</span>
                                    </button>
                                    <button class="sec-primary-btn" type="button" onclick="showAddSenderIdExemptionModal()">
                                        <i class="fas fa-plus"></i>
                                        <span>Add Exemption</span>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="exemptions-filter-panel" id="exemptions-filter-panel" style="display: none;">
                                <div class="filter-body">
                                    <div class="filter-row">
                                        <div class="filter-group">
                                            <label>Type</label>
                                            <select id="exemptions-filter-type">
                                                <option value="">All Types</option>
                                                <option value="alphanumeric">Alphanumeric</option>
                                                <option value="numeric">Numeric</option>
                                                <option value="shortcode">Shortcode</option>
                                            </select>
                                        </div>
                                        <div class="filter-group">
                                            <label>Category</label>
                                            <select id="exemptions-filter-category">
                                                <option value="">All Categories</option>
                                                <option value="government_healthcare">Government and Healthcare</option>
                                                <option value="banking_finance">Banking and Finance</option>
                                                <option value="delivery_logistics">Delivery and logistics</option>
                                                <option value="miscellaneous">Miscellaneous</option>
                                                <option value="generic">Generic</option>
                                            </select>
                                        </div>
                                        <div class="filter-group">
                                            <label>Scope</label>
                                            <select id="exemptions-filter-scope">
                                                <option value="">All Scopes</option>
                                                <option value="global">Global</option>
                                                <option value="account">Account</option>
                                                <option value="subaccount">Sub-account</option>
                                            </select>
                                        </div>
                                        <div class="filter-group">
                                            <label>Source</label>
                                            <select id="exemptions-filter-source">
                                                <option value="">All Sources</option>
                                                <option value="approval">Approved via Approvals</option>
                                                <option value="manual">Added manually</option>
                                            </select>
                                        </div>
                                        <div class="filter-group">
                                            <label>Status</label>
                                            <select id="exemptions-filter-status">
                                                <option value="">All</option>
                                                <option value="active">Active</option>
                                                <option value="disabled">Disabled</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="filter-actions">
                                        <button class="btn btn-sm btn-link text-secondary" onclick="resetExemptionsFilters()">Reset</button>
                                        <button class="btn btn-sm sec-primary-btn" onclick="applyExemptionsFilters()">Apply Filters</button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="filter-chips-row" id="exemptions-chips-row" style="display: none; padding: 0.5rem 1rem; background: #f8f9fc; border-bottom: 1px solid #e9ecef;">
                                <div class="d-flex align-items-center gap-2 flex-wrap" id="exemptions-chips-container"></div>
                            </div>
                            
                            <div class="table-responsive">
                                <table class="sec-table exemptions-compact-table" id="exemptions-table">
                                    <thead>
                                        <tr>
                                            <th>SenderID <i class="fas fa-sort"></i></th>
                                            <th>Type <i class="fas fa-sort"></i></th>
                                            <th>Category <i class="fas fa-sort"></i></th>
                                            <th>Scope <i class="fas fa-sort"></i></th>
                                            <th>Account <i class="fas fa-sort"></i></th>
                                            <th>Sub-account(s) <i class="fas fa-sort"></i></th>
                                            <th>Source <i class="fas fa-sort"></i></th>
                                            <th>Status <i class="fas fa-sort"></i></th>
                                            <th>Updated <i class="fas fa-sort"></i></th>
                                            <th style="width: 50px;"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="exemptions-body">
                                    </tbody>
                                </table>
                            </div>
                            <div class="sec-empty-state" id="exemptions-empty-state" style="display: none;">
                                <i class="fas fa-shield-alt"></i>
                                <h6>No Exemptions</h6>
                                <p>Approved SenderIDs and manual exemptions will appear here.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="message-content" role="tabpanel">
                <!-- Sub-tabs for Message Content Controls -->
                <ul class="nav nav-tabs mb-3" id="contentSubTabs" role="tablist" style="border-bottom: 2px solid #e9ecef;">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="content-rules-tab" data-bs-toggle="tab" data-bs-target="#content-rules" type="button" role="tab" style="font-size: 0.85rem; font-weight: 600; color: #1e3a5f; border: none; border-bottom: 3px solid transparent; padding: 0.5rem 1rem;">
                            <i class="fas fa-list-alt me-1"></i> Rules
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="content-exemptions-tab" data-bs-toggle="tab" data-bs-target="#content-exemptions" type="button" role="tab" style="font-size: 0.85rem; font-weight: 600; color: #6c757d; border: none; border-bottom: 3px solid transparent; padding: 0.5rem 1rem;">
                            <i class="fas fa-shield-alt me-1"></i> Exemptions
                        </button>
                    </li>
                </ul>
                
                <div class="tab-content" id="contentSubTabContent">
                    <!-- Rules Tab -->
                    <div class="tab-pane fade show active" id="content-rules" role="tabpanel">
                        <!-- Collapsible Anti-Spam Controls Card (Global Default) -->
                        <div class="card mb-3" style="border: 1px solid #e9ecef; border-left: 3px solid #1e3a5f;">
                            <div class="card-header py-2 d-flex justify-content-between align-items-center" style="background: #f8f9fa; cursor: pointer;" data-bs-toggle="collapse" data-bs-target="#antispam-collapse" aria-expanded="false">
                                <h6 class="mb-0" style="font-size: 0.85rem; font-weight: 600;">
                                    <i class="fas fa-shield-virus me-2" style="color: #1e3a5f;"></i>Anti-Spam Protection
                                    <span class="badge bg-light text-dark ms-2" style="font-size: 0.6rem; font-weight: 500;">GLOBAL DEFAULT</span>
                                    <i class="fas fa-chevron-down ms-2" id="antispam-collapse-icon" style="font-size: 0.65rem; transition: transform 0.2s;"></i>
                                </h6>
                                <div id="antispam-status-badge">
                                    <span class="badge bg-secondary" style="font-size: 0.65rem;">
                                        <i class="fas fa-toggle-off me-1"></i> Off
                                    </span>
                                </div>
                            </div>
                            <div class="collapse" id="antispam-collapse">
                                <div class="card-body py-2 px-3">
                                    <div class="d-flex align-items-center gap-3 flex-wrap">
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" id="antispam-repeat-toggle" onchange="toggleAntiSpamRepeat()" style="width: 2.25rem; height: 1.125rem;">
                                            <label class="form-check-label" for="antispam-repeat-toggle" style="font-size: 0.8rem; font-weight: 600;">
                                                Enable Anti-Spam Protection
                                            </label>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <label for="antispam-window" class="form-label mb-0" style="font-size: 0.75rem; white-space: nowrap;">Window:</label>
                                            <select class="form-select form-select-sm" id="antispam-window" onchange="updateAntiSpamWindow()" disabled style="width: auto; font-size: 0.8rem; padding: 0.25rem 1.75rem 0.25rem 0.5rem;">
                                                <option value="15">15 min</option>
                                                <option value="30">30 min</option>
                                                <option value="60">60 min</option>
                                                <option value="120" selected>120 min</option>
                                            </select>
                                        </div>
                                    </div>
                                    <p class="text-muted mb-0 mt-2" style="font-size: 0.7rem; line-height: 1.4;">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Blocks identical message content sent to the same MSISDN within the configured window. 
                                        <strong>Per-account overrides</strong> can be configured in the Exemptions tab.
                                    </p>
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
                                    <button class="sec-pill-btn" type="button" id="content-filter-btn" onclick="toggleContentFilterPanel()">
                                        <i class="fas fa-filter"></i>
                                        <span>Filter</span>
                                        <span class="badge" id="content-filter-count" style="display: none; background: #1e3a5f; font-size: 0.65rem; padding: 0.15rem 0.35rem; margin-left: 0.25rem;">0</span>
                                    </button>
                                    <button class="sec-primary-btn" onclick="showAddContentRuleModal()">
                                        <i class="fas fa-plus"></i>
                                        <span>Add Rule</span>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="content-filter-panel" id="content-filter-panel" style="display: none;">
                                <div class="filter-body">
                                    <div class="filter-row">
                                        <div class="filter-group">
                                            <label>Status</label>
                                            <select id="content-filter-status">
                                                <option value="">All Statuses</option>
                                                <option value="active">Active</option>
                                                <option value="disabled">Disabled</option>
                                            </select>
                                        </div>
                                        <div class="filter-group">
                                            <label>Match Type</label>
                                            <select id="content-filter-matchtype">
                                                <option value="">All Types</option>
                                                <option value="keyword">Keyword</option>
                                                <option value="regex">Regex</option>
                                            </select>
                                        </div>
                                        <div class="filter-group">
                                            <label>Rule Type</label>
                                            <select id="content-filter-ruletype">
                                                <option value="">All Types</option>
                                                <option value="block">Block</option>
                                                <option value="flag">Flag (Quarantine)</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="filter-actions">
                                    <button class="btn btn-sm btn-link text-secondary" onclick="resetContentFilters()">Reset</button>
                                    <button class="btn btn-sm" style="background: #1e3a5f; color: #fff;" onclick="applyContentFilters()">Apply Filters</button>
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
                    
                    <!-- Exemptions Tab -->
                    <div class="tab-pane fade" id="content-exemptions" role="tabpanel">
                        <div class="sec-table-card">
                            <div class="sec-toolbar" style="display: flex; align-items: center; gap: 1rem; padding: 0.75rem 1rem; border-bottom: 1px solid #e9ecef;">
                                <div class="sec-search-box-left" style="flex: 0 0 300px;">
                                    <i class="fas fa-search"></i>
                                    <input type="text" class="form-control" placeholder="Search exemptions..." id="content-exemptions-search" oninput="filterContentExemptionsTable()">
                                </div>
                                <div class="sec-toolbar-actions" style="display: flex; gap: 0.5rem; margin-left: auto;">
                                    <button class="sec-pill-btn" type="button" id="content-exemptions-filter-btn" onclick="toggleContentExemptionsFilterPanel()">
                                        <i class="fas fa-filter"></i>
                                        <span>Filter</span>
                                        <span class="badge" id="content-exemptions-filter-count" style="display: none; background: #1e3a5f; font-size: 0.65rem; padding: 0.15rem 0.35rem; margin-left: 0.25rem;">0</span>
                                    </button>
                                    <button class="sec-primary-btn" type="button" onclick="showAddContentExemptionModal()">
                                        <i class="fas fa-plus"></i>
                                        <span>Add Exemption</span>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="content-exemptions-filter-panel" id="content-exemptions-filter-panel" style="display: none;">
                                <div class="filter-body">
                                    <div class="filter-row">
                                        <div class="filter-group">
                                            <label>Scope</label>
                                            <select id="content-exemptions-filter-scope">
                                                <option value="">All Scopes</option>
                                                <option value="account">Account</option>
                                                <option value="subaccount">Sub-account</option>
                                            </select>
                                        </div>
                                        <div class="filter-group">
                                            <label>Type</label>
                                            <select id="content-exemptions-filter-type">
                                                <option value="">All Types</option>
                                                <option value="rule">Rule Exemption</option>
                                                <option value="antispam">Anti-Spam Override</option>
                                            </select>
                                        </div>
                                        <div class="filter-group">
                                            <label>Status</label>
                                            <select id="content-exemptions-filter-status">
                                                <option value="">All Statuses</option>
                                                <option value="active">Active</option>
                                                <option value="disabled">Disabled</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="filter-actions">
                                    <button class="btn btn-sm btn-link text-secondary" onclick="resetContentExemptionsFilters()">Reset</button>
                                    <button class="btn btn-sm" style="background: #1e3a5f; color: #fff;" onclick="applyContentExemptionsFilters()">Apply Filters</button>
                                </div>
                            </div>
                            
                            <div class="filter-chips-row" id="content-exemptions-chips-row" style="display: none; padding: 0.5rem 1rem; background: #f8f9fc; border-bottom: 1px solid #e9ecef;">
                                <div class="d-flex align-items-center gap-2 flex-wrap" id="content-exemptions-chips-container"></div>
                            </div>
                            
                            <div class="table-responsive">
                                <table class="sec-table exemptions-compact-table" id="content-exemptions-table">
                                    <thead>
                                        <tr>
                                            <th class="sortable" data-sort="account">Account <i class="fas fa-sort"></i></th>
                                            <th class="sortable" data-sort="subaccounts">Sub-account(s) <i class="fas fa-sort"></i></th>
                                            <th class="sortable" data-sort="type">Exemption Type <i class="fas fa-sort"></i></th>
                                            <th class="sortable" data-sort="rules">Rules Exempted <i class="fas fa-sort"></i></th>
                                            <th class="sortable" data-sort="antispam">Anti-Spam Override <i class="fas fa-sort"></i></th>
                                            <th class="sortable" data-sort="appliedBy">Applied By <i class="fas fa-sort"></i></th>
                                            <th class="sortable" data-sort="appliedDate">Applied Date <i class="fas fa-sort"></i></th>
                                            <th class="sortable" data-sort="status">Status <i class="fas fa-sort"></i></th>
                                            <th style="width: 50px;"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="content-exemptions-body">
                                    </tbody>
                                </table>
                            </div>
                            <div class="sec-empty-state" id="content-exemptions-empty-state" style="display: none;">
                                <i class="fas fa-shield-alt" style="color: #1e3a5f;"></i>
                                <h6>No Exemptions</h6>
                                <p>Add exemptions to allow specific accounts to bypass content rules or anti-spam controls.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="url-controls" role="tabpanel">
                <!-- URL Controls Toolbar (matches Quarantine Review styling) -->
                <div class="sec-toolbar">
                    <div class="sec-search-box-left">
                        <i class="fas fa-search"></i>
                        <input type="text" class="form-control" placeholder="Search domains, patterns, accounts..." id="url-controls-search">
                    </div>
                    <div class="sec-toolbar-actions">
                        <button class="url-filter-pill-btn" type="button" id="url-controls-filter-btn" onclick="toggleUrlControlsFilterPanel()">
                            <i class="fas fa-filter"></i>
                            <span>Filter</span>
                            <span class="badge bg-primary" id="url-controls-filter-count" style="display: none; font-size: 0.7rem; padding: 0.2rem 0.4rem;">0</span>
                        </button>
                        <button class="url-filter-pill-btn" type="button" onclick="showAddUrlExemptionGlobalModal()">
                            <i class="fas fa-shield-alt"></i>
                            <span>Exemption</span>
                        </button>
                        <button class="btn btn-sm text-white" id="url-add-rule-btn" style="background: #1e3a5f; display: none;" onclick="showAddUrlRuleModal()">
                            <i class="fas fa-plus me-1"></i> Add Rule
                        </button>
                    </div>
                </div>
                
                <!-- URL Controls Filter Panel (hidden by default) -->
                <div class="url-controls-filter-panel" id="url-controls-filter-panel" style="display: none;">
                    <div class="filter-body">
                        <div class="filter-row">
                            <div class="filter-group">
                                <label>Status</label>
                                <select id="url-controls-filter-status">
                                    <option value="">All Statuses</option>
                                    <option value="active">Active</option>
                                    <option value="disabled">Disabled</option>
                                </select>
                            </div>
                            <div class="filter-group">
                                <label>Type</label>
                                <select id="url-controls-filter-type">
                                    <option value="">All Types</option>
                                    <option value="exact">Exact Domain</option>
                                    <option value="wildcard">Wildcard</option>
                                    <option value="regex">Regex</option>
                                </select>
                            </div>
                            <div class="filter-group">
                                <label>Action</label>
                                <select id="url-controls-filter-action">
                                    <option value="">All Actions</option>
                                    <option value="block">Block</option>
                                    <option value="flag">Flag</option>
                                </select>
                            </div>
                            <div class="filter-group">
                                <label>Account</label>
                                <select id="url-controls-filter-account">
                                    <option value="">All Accounts</option>
                                </select>
                            </div>
                        </div>
                        <div class="filter-actions">
                            <button class="btn btn-reset" onclick="resetUrlControlsFilters()"><i class="fas fa-undo me-1"></i> Reset</button>
                            <button class="btn btn-apply" onclick="applyUrlControlsFilters()"><i class="fas fa-check me-1"></i> Apply Filters</button>
                        </div>
                    </div>
                </div>
                
                <!-- Sub-tabs for URL Controls -->
                <ul class="nav nav-tabs mb-3" id="urlSubTabs" role="tablist" style="border-bottom: 2px solid #e9ecef;">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="url-domain-age-tab" data-bs-toggle="tab" data-bs-target="#url-domain-age" type="button" role="tab" style="font-size: 0.85rem; font-weight: 600; color: #1e3a5f; border: none; border-bottom: 3px solid transparent; padding: 0.5rem 1rem;">
                            <i class="fas fa-clock me-1"></i> Domain Age
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="url-rules-tab" data-bs-toggle="tab" data-bs-target="#url-rules-pane" type="button" role="tab" style="font-size: 0.85rem; font-weight: 600; color: #6c757d; border: none; border-bottom: 3px solid transparent; padding: 0.5rem 1rem;">
                            <i class="fas fa-link me-1"></i> URL Rule Library
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="url-exemptions-tab" data-bs-toggle="tab" data-bs-target="#url-exemptions" type="button" role="tab" style="font-size: 0.85rem; font-weight: 600; color: #6c757d; border: none; border-bottom: 3px solid transparent; padding: 0.5rem 1rem;">
                            <i class="fas fa-shield-alt me-1"></i> Exemptions
                        </button>
                    </li>
                </ul>
                
                <div class="tab-content" id="urlSubTabContent">
                    <!-- Domain Age Tab -->
                    <div class="tab-pane fade show active" id="url-domain-age" role="tabpanel">
                        <div class="card mb-3" style="border: 1px solid #e9ecef; border-radius: 6px; box-shadow: none;">
                            <div class="card-header py-2" style="background: #f8f9fa; border-bottom: 1px solid #e9ecef;">
                                <div class="d-flex justify-content-between align-items-center" style="cursor: pointer;" data-bs-toggle="collapse" data-bs-target="#domain-age-collapse" aria-expanded="false">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="fas fa-clock" style="color: #1e3a5f; font-size: 0.85rem;"></i>
                                        <span style="font-size: 0.85rem; font-weight: 600; color: #1e3a5f;">Domain Age Settings</span>
                                        <span class="badge" id="domain-age-status-badge" style="font-size: 0.65rem; background: #dc3545; color: white;">Disabled</span>
                                    </div>
                                    <i class="fas fa-chevron-down" id="domain-age-collapse-icon" style="font-size: 0.65rem; color: #6c757d; transition: transform 0.2s;"></i>
                                </div>
                                <div class="d-flex gap-3 mt-1" id="domain-age-meta" style="font-size: 0.7rem; color: #6c757d;">
                                    <span><i class="fas fa-calendar-alt me-1"></i> Last updated: <span id="domain-age-updated-at">-</span></span>
                                    <span><i class="fas fa-user me-1"></i> By: <span id="domain-age-updated-by">-</span></span>
                                </div>
                            </div>
                            <div class="collapse" id="domain-age-collapse">
                                <div class="card-body" style="padding: 0.75rem;">
                                    <div class="row g-2 align-items-end">
                                        <div class="col-md-4">
                                            <div class="form-check form-switch mb-0">
                                                <input class="form-check-input" type="checkbox" id="domain-age-enabled" style="width: 2.25rem; height: 1.1rem;" onchange="toggleDomainAgeFields()">
                                                <label class="form-check-label" for="domain-age-enabled" style="font-weight: 600; font-size: 0.8rem; margin-left: 0.25rem;">
                                                    Enable Enforcement
                                                </label>
                                            </div>
                                            <small class="text-muted d-block" style="font-size: 0.7rem; margin-top: 0.25rem;">Block or flag URLs with newly registered domains</small>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label mb-1" style="font-size: 0.75rem; font-weight: 600;">Threshold (hours)</label>
                                            <input type="number" class="form-control form-control-sm" id="domain-age-hours" value="72" min="1" max="8760" style="font-size: 0.85rem;">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label mb-1" style="font-size: 0.75rem; font-weight: 600;">Action on Trigger</label>
                                            <select class="form-select form-select-sm" id="domain-age-action" style="font-size: 0.85rem;">
                                                <option value="block">Block</option>
                                                <option value="flag">Flag to Quarantine</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2 text-end">
                                            <button class="btn btn-sm btn-outline-secondary me-1" onclick="cancelDomainAgeSettings()" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">
                                                Cancel
                                            </button>
                                            <button class="btn btn-sm text-white" style="background: #1e3a5f; font-size: 0.75rem; padding: 0.25rem 0.5rem;" onclick="confirmSaveDomainAgeSettings()">
                                                <i class="fas fa-save me-1"></i> Save
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <hr style="margin: 0.5rem 0; border-color: #e9ecef;">
                                    
                                    <!-- Exemptions Section -->
                                    <div class="row g-2">
                                        <!-- A) Domain Allowlist -->
                                        <div class="col-md-6">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <span style="font-size: 0.75rem; font-weight: 600; color: #1e3a5f;">
                                                    <i class="fas fa-globe me-1"></i> Domain Allowlist
                                                </span>
                                                <button class="btn btn-sm btn-outline-primary" style="font-size: 0.7rem; padding: 0.15rem 0.4rem;" onclick="showAddDomainAllowlistModal()">
                                                    <i class="fas fa-plus me-1"></i> Add Domain
                                                </button>
                                            </div>
                                            <div class="table-responsive" style="max-height: 120px; overflow-y: auto;">
                                                <table class="table table-sm mb-0" style="font-size: 0.7rem;">
                                                    <thead>
                                                        <tr style="background: #f8f9fa;">
                                                            <th style="padding: 0.2rem 0.25rem; font-weight: 600;">Domain</th>
                                                            <th style="padding: 0.2rem 0.25rem; font-weight: 600;">Scope</th>
                                                            <th style="padding: 0.2rem 0.25rem; font-weight: 600;">Updated</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="domain-allowlist-preview-body">
                                                        <tr><td colspan="3" class="text-center text-muted" style="padding: 0.4rem;">No domain exemptions</td></tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="text-end mt-1">
                                                <a href="javascript:void(0)" onclick="viewAllDomainAgeExemptions('domain')" style="font-size: 0.7rem; color: #1e3a5f;">View all <i class="fas fa-arrow-right ms-1"></i></a>
                                            </div>
                                        </div>
                                        
                                        <!-- B) Account/Sub-account Threshold Override -->
                                        <div class="col-md-6">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <span style="font-size: 0.75rem; font-weight: 600; color: #1e3a5f;">
                                                    <i class="fas fa-building me-1"></i> Threshold Overrides
                                                </span>
                                                <button class="btn btn-sm btn-outline-primary" style="font-size: 0.7rem; padding: 0.15rem 0.4rem;" onclick="showAddThresholdOverrideModal()">
                                                    <i class="fas fa-plus me-1"></i> Add Override
                                                </button>
                                            </div>
                                            <div class="table-responsive" style="max-height: 120px; overflow-y: auto;">
                                                <table class="table table-sm mb-0" style="font-size: 0.7rem;">
                                                    <thead>
                                                        <tr style="background: #f8f9fa;">
                                                            <th style="padding: 0.2rem 0.25rem; font-weight: 600;">Account</th>
                                                            <th style="padding: 0.2rem 0.25rem; font-weight: 600;">Threshold</th>
                                                            <th style="padding: 0.2rem 0.25rem; font-weight: 600;">Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="threshold-override-preview-body">
                                                        <tr><td colspan="3" class="text-center text-muted" style="padding: 0.4rem;">No threshold overrides</td></tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="text-end mt-1">
                                                <a href="javascript:void(0)" onclick="viewAllDomainAgeExemptions('threshold')" style="font-size: 0.7rem; color: #1e3a5f;">View all <i class="fas fa-arrow-right ms-1"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p class="text-muted mb-0" style="font-size: 0.75rem;"><i class="fas fa-info-circle me-1"></i>Domain age checking helps prevent phishing by blocking URLs from recently registered domains.</p>
                    </div>

                    <!-- URL Rule Library Tab -->
                    <div class="tab-pane fade" id="url-rules-pane" role="tabpanel">
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
                                                <button class="btn btn-sm text-white flex-fill" style="background: #1e3a5f;" onclick="applyUrlFilters()">
                                                    Apply
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <button class="btn btn-sm text-white" style="background: #1e3a5f;" onclick="showAddUrlRuleModal()">
                                        <i class="fas fa-plus me-1"></i> Add Rule
                                    </button>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="sec-table" id="url-rules-table">
                                    <thead>
                                        <tr>
                                            <th>Rule Name <i class="fas fa-sort"></i></th>
                                            <th>Pattern <i class="fas fa-sort"></i></th>
                                            <th>Match Type <i class="fas fa-sort"></i></th>
                                            <th>Rule Type <i class="fas fa-sort"></i></th>
                                            <th>Status <i class="fas fa-sort"></i></th>
                                            <th>Updated <i class="fas fa-sort"></i></th>
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
                    </div>

                    <!-- Exemptions Tab -->
                    <div class="tab-pane fade" id="url-exemptions" role="tabpanel">
                        <div class="sec-table-card">
                            <div class="sec-toolbar">
                                <div class="sec-search-box-left">
                                    <i class="fas fa-search"></i>
                                    <input type="text" class="form-control" placeholder="Search accounts..." id="url-exemptions-search">
                                </div>
                                <div class="sec-toolbar-actions">
                                    <div class="dropdown">
                                        <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" id="url-exemptions-filter-btn">
                                            <i class="fas fa-filter me-1"></i> Filter
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-end p-3" style="min-width: 320px;" onclick="event.stopPropagation()">
                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold">Account</label>
                                                <select class="form-select form-select-sm" id="url-exemptions-filter-account">
                                                    <option value="">All Accounts</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold">Sub-account</label>
                                                <select class="form-select form-select-sm" id="url-exemptions-filter-subaccount">
                                                    <option value="">All Sub-accounts</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold">Exemption Type</label>
                                                <select class="form-select form-select-sm" id="url-exemptions-filter-type">
                                                    <option value="">All Types</option>
                                                    <option value="domain_age">Domain Age Override</option>
                                                    <option value="domains">Allowlisted Domains</option>
                                                    <option value="url_rule">Rule Exemption</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold">Date Range</label>
                                                <div class="row g-2">
                                                    <div class="col-6">
                                                        <input type="date" class="form-control form-control-sm" id="url-exemptions-filter-date-from" placeholder="From">
                                                    </div>
                                                    <div class="col-6">
                                                        <input type="date" class="form-control form-control-sm" id="url-exemptions-filter-date-to" placeholder="To">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold">Status</label>
                                                <select class="form-select form-select-sm" id="url-exemptions-filter-status">
                                                    <option value="">All Statuses</option>
                                                    <option value="active">Active</option>
                                                    <option value="disabled">Disabled</option>
                                                </select>
                                            </div>
                                            <div class="d-flex gap-2">
                                                <button class="btn btn-sm btn-outline-secondary flex-fill" onclick="resetUrlExemptionsFilters()">
                                                    <i class="fas fa-undo me-1"></i> Reset
                                                </button>
                                                <button class="btn btn-sm text-white flex-fill" style="background: #1e3a5f;" onclick="applyUrlExemptionsFilters()">
                                                    Apply
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <button class="btn btn-sm text-white" style="background: #1e3a5f;" onclick="showAddUrlExemptionModal()">
                                        <i class="fas fa-plus me-1"></i> Add Exemption
                                    </button>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="sec-table" id="url-exemptions-table">
                                    <thead>
                                        <tr>
                                            <th onclick="sortUrlExemptionsTable('accountName')" style="cursor: pointer;">Account <i class="fas fa-sort"></i></th>
                                            <th>Sub-account(s)</th>
                                            <th onclick="sortUrlExemptionsTable('type')" style="cursor: pointer;">Exemption Type <i class="fas fa-sort"></i></th>
                                            <th style="max-width: 220px;">Details</th>
                                            <th onclick="sortUrlExemptionsTable('appliedBy')" style="cursor: pointer;">Applied By <i class="fas fa-sort"></i></th>
                                            <th onclick="sortUrlExemptionsTable('appliedAt')" style="cursor: pointer;">Applied Date <i class="fas fa-sort"></i></th>
                                            <th onclick="sortUrlExemptionsTable('status')" style="cursor: pointer;">Status <i class="fas fa-sort"></i></th>
                                            <th style="width: 80px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="url-exemptions-body">
                                    </tbody>
                                </table>
                            </div>
                            <div class="sec-empty-state" id="url-exemptions-empty-state" style="display: none;">
                                <i class="fas fa-shield-alt"></i>
                                <h6>No Exemptions</h6>
                                <p>All accounts are subject to URL controls. Add exemptions to override specific rules for accounts.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="normalisation-rules" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex align-items-center">
                        <span class="text-muted" style="font-size: 0.85rem;">Click any character to edit its equivalents</span>
                    </div>
                    <button class="btn btn-sm" onclick="testNormalisationRule()" style="background: transparent; color: #1e3a5f; border: 1px solid #1e3a5f;">
                        <i class="fas fa-flask me-1"></i>Test Normalisation
                    </button>
                </div>

                <ul class="nav nav-tabs mb-3" id="normCharTabs" role="tablist" style="border-bottom: 2px solid #e9ecef;">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="norm-letters-tab" data-bs-toggle="tab" data-bs-target="#norm-letters" type="button" role="tab" style="font-weight: 600; color: #1e3a5f;">
                            <i class="fas fa-font me-1"></i>Letters A–Z <span class="badge bg-secondary ms-1">26</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="norm-digits-tab" data-bs-toggle="tab" data-bs-target="#norm-digits" type="button" role="tab" style="font-weight: 600; color: #1e3a5f;">
                            <i class="fas fa-hashtag me-1"></i>Digits 0–9 <span class="badge bg-secondary ms-1">10</span>
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="normCharTabsContent">
                    <div class="tab-pane fade show active" id="norm-letters" role="tabpanel">
                        <div class="sec-table-card p-4">
                            <div class="norm-char-grid" id="norm-letters-grid">
                                <!-- Letters A-Z grid will be rendered here -->
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="norm-digits" role="tabpanel">
                        <div class="sec-table-card p-4">
                            <div class="norm-char-grid" id="norm-digits-grid">
                                <!-- Digits 0-9 grid will be rendered here -->
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

                    <div class="quarantine-filter-panel" id="quarantine-filter-panel" style="display: none;">
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
                
                <!-- Validation Alert -->
                <div class="alert alert-danger d-none" id="senderid-validation-alert" style="font-size: 0.85rem;">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <span id="senderid-validation-message">Please complete all required fields before saving.</span>
                </div>
                
                <div class="mb-3">
                    <label class="form-label" style="font-weight: 500; color: #1e3a5f;">Rule Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="senderid-rule-name" placeholder="e.g., Block HSBC Impersonation" required>
                    <div class="invalid-feedback" id="senderid-rule-name-error">Rule name is required</div>
                    <small class="text-muted">A descriptive name for this rule</small>
                </div>

                <div class="mb-3">
                    <label class="form-label" style="font-weight: 500; color: #1e3a5f;">Base SenderID <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="senderid-base-value" placeholder="e.g., HSBC" style="text-transform: uppercase;" required>
                    <div class="invalid-feedback" id="senderid-base-value-error">Base SenderID is required</div>
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
                        <option value="government_healthcare">Government and Healthcare</option>
                        <option value="banking_finance">Banking and Finance</option>
                        <option value="delivery_logistics">Delivery and logistics</option>
                        <option value="miscellaneous">Miscellaneous</option>
                        <option value="generic">Generic</option>
                    </select>
                    <div class="invalid-feedback" id="senderid-category-error">Please select a category</div>
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

<!-- SenderID Rule Confirmation Modal -->
<div class="modal fade" id="senderIdConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: #f8f9fc; border-bottom: 1px solid #e9ecef;">
                <h5 class="modal-title" style="color: #1e3a5f; font-weight: 600;">
                    <i class="fas fa-check-circle me-2"></i>Confirm Rule Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p style="color: #6c757d; font-size: 0.9rem; margin-bottom: 1rem;">Please review the rule details before saving:</p>
                
                <table class="table table-sm" style="font-size: 0.85rem;">
                    <tbody>
                        <tr>
                            <td style="font-weight: 600; color: #1e3a5f; width: 40%;">Rule Name</td>
                            <td id="confirm-rule-name"></td>
                        </tr>
                        <tr>
                            <td style="font-weight: 600; color: #1e3a5f;">Base SenderID</td>
                            <td><code id="confirm-base-senderid" style="background: #e9ecef; padding: 0.15rem 0.4rem; border-radius: 3px;"></code></td>
                        </tr>
                        <tr>
                            <td style="font-weight: 600; color: #1e3a5f;">Rule Type</td>
                            <td id="confirm-rule-type"></td>
                        </tr>
                        <tr>
                            <td style="font-weight: 600; color: #1e3a5f;">Category</td>
                            <td id="confirm-category"></td>
                        </tr>
                        <tr>
                            <td style="font-weight: 600; color: #1e3a5f;">Normalisation</td>
                            <td id="confirm-normalisation"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer" style="background: #f8f9fc; border-top: 1px solid #e9ecef;">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn" style="background: #1e3a5f; color: #fff;" onclick="confirmSaveSenderIdRule()">
                    <i class="fas fa-check me-1"></i> Confirm & Save
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Import Rules Modal -->
<div class="modal fade" id="importRulesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: #f8f9fc; border-bottom: 1px solid #e9ecef;">
                <h5 class="modal-title" style="color: #1e3a5f; font-weight: 600;">
                    <i class="fas fa-file-import me-2"></i>Import SenderID Rules
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Step 1: Upload -->
                <div id="import-step-upload">
                    <div class="alert alert-info" style="font-size: 0.85rem;">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Supported formats:</strong> CSV, XLSX
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label" style="font-weight: 500; color: #1e3a5f;">Required Columns</label>
                        <div class="p-3 rounded" style="background: #f8f9fc; border: 1px solid #e9ecef; font-size: 0.8rem;">
                            <table class="table table-sm mb-0" style="font-size: 0.8rem;">
                                <thead>
                                    <tr style="background: #e9ecef;">
                                        <th style="padding: 0.4rem;">Column</th>
                                        <th style="padding: 0.4rem;">Values</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td><code>rule_name</code></td><td>Text (required)</td></tr>
                                    <tr><td><code>base_senderid</code></td><td>Text (required)</td></tr>
                                    <tr><td><code>rule_type</code></td><td><code>block</code> | <code>flag</code></td></tr>
                                    <tr><td><code>category</code></td><td><code>government_healthcare</code> | <code>banking_finance</code> | <code>delivery_logistics</code> | <code>miscellaneous</code> | <code>generic</code></td></tr>
                                    <tr><td><code>normalisation_applied</code></td><td><code>true</code> | <code>false</code></td></tr>
                                    <tr><td><code>status</code></td><td><code>enabled</code> | <code>disabled</code></td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label" style="font-weight: 500; color: #1e3a5f;">Upload File</label>
                        <input type="file" class="form-control" id="import-file-input" accept=".csv,.xlsx">
                        <div class="invalid-feedback" id="import-file-error">Please select a valid CSV or XLSX file</div>
                    </div>
                    
                    <div class="text-center mt-4">
                        <button type="button" class="btn" style="background: #1e3a5f; color: #fff;" onclick="parseImportFile()">
                            <i class="fas fa-upload me-1"></i> Upload & Validate
                        </button>
                    </div>
                </div>
                
                <!-- Step 2: Preview -->
                <div id="import-step-preview" style="display: none;">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <span class="badge bg-success me-2" id="import-valid-count">0 Valid</span>
                            <span class="badge bg-danger" id="import-invalid-count">0 Invalid</span>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="resetImportModal()">
                            <i class="fas fa-arrow-left me-1"></i> Back
                        </button>
                    </div>
                    
                    <!-- Valid Rows -->
                    <div class="mb-3" id="import-valid-section">
                        <h6 style="font-size: 0.85rem; font-weight: 600; color: #1e3a5f;">
                            <i class="fas fa-check-circle text-success me-1"></i> Valid Rules
                        </h6>
                        <div class="table-responsive" style="max-height: 200px; overflow-y: auto;">
                            <table class="table table-sm table-striped" style="font-size: 0.75rem;">
                                <thead style="position: sticky; top: 0; background: #f8f9fa;">
                                    <tr>
                                        <th style="padding: 0.3rem;">Rule Name</th>
                                        <th style="padding: 0.3rem;">SenderID</th>
                                        <th style="padding: 0.3rem;">Type</th>
                                        <th style="padding: 0.3rem;">Category</th>
                                        <th style="padding: 0.3rem;">Norm.</th>
                                        <th style="padding: 0.3rem;">Status</th>
                                    </tr>
                                </thead>
                                <tbody id="import-valid-tbody"></tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Invalid Rows -->
                    <div class="mb-3" id="import-invalid-section" style="display: none;">
                        <h6 style="font-size: 0.85rem; font-weight: 600; color: #991b1b;">
                            <i class="fas fa-times-circle text-danger me-1"></i> Invalid Rows (will be skipped)
                        </h6>
                        <div class="table-responsive" style="max-height: 150px; overflow-y: auto;">
                            <table class="table table-sm" style="font-size: 0.75rem; background: #fef2f2;">
                                <thead style="position: sticky; top: 0; background: #fee2e2;">
                                    <tr>
                                        <th style="padding: 0.3rem;">Row</th>
                                        <th style="padding: 0.3rem;">Data</th>
                                        <th style="padding: 0.3rem;">Error</th>
                                    </tr>
                                </thead>
                                <tbody id="import-invalid-tbody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="background: #f8f9fc; border-top: 1px solid #e9ecef;">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn" style="background: #1e3a5f; color: #fff; display: none;" id="import-confirm-btn" onclick="confirmImportRules()">
                    <i class="fas fa-check me-1"></i> <span id="import-confirm-text">Import 0 Rules</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Generic Action Confirmation Modal -->
<div class="modal fade" id="actionConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" id="action-confirm-header" style="background: #f8f9fc; border-bottom: 1px solid #e9ecef;">
                <h5 class="modal-title" id="action-confirm-title" style="color: #1e3a5f; font-weight: 600;">
                    <i class="fas fa-question-circle me-2" id="action-confirm-icon"></i><span id="action-confirm-title-text">Confirm Action</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="action-confirm-message">Are you sure you want to perform this action?</p>
                <div id="action-confirm-details" class="mb-3"></div>
                <div id="action-confirm-reason-container" style="display: none;">
                    <label class="form-label" style="font-weight: 500; color: #1e3a5f;">Reason (optional)</label>
                    <textarea class="form-control" id="action-confirm-reason" rows="2" placeholder="Enter reason for this action..."></textarea>
                </div>
                <input type="hidden" id="action-confirm-id" value="">
                <input type="hidden" id="action-confirm-type" value="">
                <input type="hidden" id="action-confirm-action" value="">
            </div>
            <div class="modal-footer" style="background: #f8f9fc; border-top: 1px solid #e9ecef;">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-sm" id="action-confirm-btn" onclick="executeConfirmedAction()">
                    <i class="fas fa-check me-1"></i> <span id="action-confirm-btn-text">Confirm</span>
                </button>
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
                <!-- Validation Alert -->
                <div id="content-rule-validation-alert" class="alert alert-danger d-flex align-items-center mb-3" role="alert" style="display: none !important;">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <div>Please complete all required fields before saving.</div>
                </div>
                
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
                    
                    <!-- Test Rule Section - Collapsible Accordion -->
                    <div class="accordion mb-3" id="testRuleAccordion">
                        <div class="accordion-item" style="border: 1px solid #e9ecef; border-radius: 6px;">
                            <h2 class="accordion-header" id="testRuleHeading">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#testRuleCollapse" aria-expanded="false" aria-controls="testRuleCollapse" style="padding: 0.75rem 1rem; font-size: 0.85rem; font-weight: 600; color: #1e3a5f; background: #f8f9fa;">
                                    <i class="fas fa-flask me-2"></i> Test this rule
                                </button>
                            </h2>
                            <div id="testRuleCollapse" class="accordion-collapse collapse" aria-labelledby="testRuleHeading" data-bs-parent="#testRuleAccordion">
                                <div class="accordion-body" style="padding: 1rem; background: #fafbfc;">
                                    <div class="mb-3">
                                        <label class="form-label" style="font-size: 0.8rem; font-weight: 600;">Test message content</label>
                                        <textarea class="form-control" id="content-rule-test-input" rows="3" placeholder="Enter sample message text to test against the rule..." style="font-size: 0.85rem;"></textarea>
                                    </div>
                                    <div class="d-flex justify-content-end mb-3">
                                        <button type="button" class="btn btn-sm" style="background: #1e3a5f; color: #fff;" onclick="testContentRule()">
                                            <i class="fas fa-play me-1"></i> Run Test
                                        </button>
                                    </div>
                                    <div id="content-rule-test-result" style="display: none;"></div>
                                </div>
                            </div>
                        </div>
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

<!-- Content Rule Success Modal -->
<div class="modal fade" id="contentRuleSuccessModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #48bb78 0%, #38a169 100%); border-bottom: none;">
                <h5 class="modal-title text-white">
                    <i class="fas fa-check-circle me-2"></i>Rule <span id="success-rule-action">Created</span> Successfully
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <div style="font-size: 4rem; color: #48bb78; margin-bottom: 1rem;">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h5 style="color: #1e3a5f; font-weight: 600; margin-bottom: 1rem;">Content Rule Saved</h5>
                <div class="p-3 mb-3" style="background: #f8f9fa; border-radius: 8px; text-align: left;">
                    <div class="mb-2">
                        <small class="text-muted">Rule Name</small>
                        <div style="font-weight: 600;" id="success-rule-name">-</div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <small class="text-muted">Match Type</small>
                            <div style="font-weight: 600;" id="success-rule-matchtype">-</div>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Action</small>
                            <div style="font-weight: 600;" id="success-rule-type">-</div>
                        </div>
                    </div>
                </div>
                <p class="text-muted mb-0" style="font-size: 0.85rem;">The rule is now active and will be applied to incoming messages.</p>
            </div>
            <div class="modal-footer justify-content-center" style="border-top: 1px solid #e9ecef;">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-sm text-white" style="background: #1e3a5f;" data-bs-dismiss="modal" onclick="showAddContentRuleModal()">
                    <i class="fas fa-plus me-1"></i> Add Another Rule
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Content Rule Confirmation Modal -->
<div class="modal fade" id="contentRuleConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: #1e3a5f; border-bottom: none;">
                <h5 class="modal-title text-white">
                    <i class="fas fa-check-circle me-2"></i>Confirm Rule Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding: 1.5rem;">
                <p class="text-muted mb-3" style="font-size: 0.9rem;">Please review the rule details before saving:</p>
                <div class="p-3" style="background: #f8f9fa; border-radius: 8px; border: 1px solid #e9ecef;">
                    <div class="row mb-2">
                        <div class="col-4 text-muted" style="font-size: 0.8rem;">Rule Name</div>
                        <div class="col-8" style="font-weight: 600;" id="confirm-rule-name">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4 text-muted" style="font-size: 0.8rem;">Match Type</div>
                        <div class="col-8" id="confirm-rule-matchtype">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4 text-muted" style="font-size: 0.8rem;">Rule Type</div>
                        <div class="col-8" id="confirm-rule-type">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4 text-muted" style="font-size: 0.8rem;">Normalisation</div>
                        <div class="col-8" id="confirm-rule-norm">-</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4 text-muted" style="font-size: 0.8rem;">Status</div>
                        <div class="col-8" id="confirm-rule-status">-</div>
                    </div>
                    <div class="row">
                        <div class="col-4 text-muted" style="font-size: 0.8rem;">Match Value</div>
                        <div class="col-8">
                            <code id="confirm-rule-value" style="font-size: 0.8rem; background: #e9ecef; padding: 0.25rem 0.5rem; border-radius: 4px; word-break: break-all;">-</code>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="border-top: 1px solid #e9ecef; padding: 1rem 1.5rem;">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="fas fa-arrow-left me-1"></i> Back to Edit
                </button>
                <button type="button" class="btn btn-sm text-white" style="background: #1e3a5f;" onclick="confirmSaveContentRule()">
                    <i class="fas fa-save me-1"></i> Confirm & Save
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Content Exemption Modal -->
<div class="modal fade" id="contentExemptionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: #1e3a5f; border-bottom: none;">
                <h5 class="modal-title text-white" id="content-exemption-modal-title">
                    <i class="fas fa-shield-alt me-2"></i>Add Content Exemption
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding: 1.5rem;">
                <form id="content-exemption-form">
                    <input type="hidden" id="content-exemption-id" value="">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="content-exemption-account-search" class="form-label" style="font-weight: 600; font-size: 0.85rem;">Account <span class="text-danger">*</span></label>
                            <div class="position-relative">
                                <input type="text" class="form-control" id="content-exemption-account-search" placeholder="Search accounts..." autocomplete="off" oninput="filterContentExemptionAccounts()" onfocus="showContentExemptionAccountDropdown()">
                                <input type="hidden" id="content-exemption-account" value="">
                                <div class="dropdown-menu w-100" id="content-exemption-account-dropdown" style="max-height: 200px; overflow-y: auto;">
                                </div>
                            </div>
                            <small class="text-muted">Type to search accounts</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Sub-Account(s)</label>
                            <div class="border rounded p-2" style="max-height: 120px; overflow-y: auto; background: #fff;" id="content-exemption-subaccounts-container">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="content-exemption-all-subaccounts" checked onchange="toggleAllSubaccounts()">
                                    <label class="form-check-label fw-bold" for="content-exemption-all-subaccounts">All Sub-accounts</label>
                                </div>
                                <div id="content-exemption-subaccounts-list" class="mt-1">
                                    <small class="text-muted">Select an account first</small>
                                </div>
                            </div>
                            <small class="text-muted">Select specific sub-accounts or apply to all</small>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Exemption Type <span class="text-danger">*</span></label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="content-exemption-type" id="content-exemption-type-rule" value="rule" checked onchange="toggleContentExemptionType()">
                                <label class="form-check-label" for="content-exemption-type-rule">Rule Exemption</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="content-exemption-type" id="content-exemption-type-antispam" value="antispam" onchange="toggleContentExemptionType()">
                                <label class="form-check-label" for="content-exemption-type-antispam">Anti-Spam Override</label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Rule Exemption Section -->
                    <div id="content-exemption-rules-section">
                        <div class="mb-3">
                            <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Exempt From Rules <span class="text-danger">*</span></label>
                            <div class="border rounded p-2" style="max-height: 200px; overflow-y: auto;" id="content-rules-checklist">
                                <!-- Rules will be populated here -->
                            </div>
                            <small class="text-muted">Select which content rules this account should be exempt from</small>
                        </div>
                    </div>
                    
                    <!-- Anti-Spam Override Section -->
                    <div id="content-exemption-antispam-section" style="display: none;">
                        <div class="p-3 mb-3" style="background: #f8f9fa; border-radius: 6px; border: 1px solid #e9ecef;">
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Anti-Spam Status</label>
                                    <div class="d-flex gap-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="antispam-toggle" id="antispam-toggle-on" value="on" checked onchange="updateAntispamModeOptions()">
                                            <label class="form-check-label" for="antispam-toggle-on">ON</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="antispam-toggle" id="antispam-toggle-off" value="off" onchange="updateAntispamModeOptions()">
                                            <label class="form-check-label" for="antispam-toggle-off">OFF</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-2" id="antispam-mode-group">
                                    <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Mode</label>
                                    <select class="form-select form-select-sm" id="content-exemption-antispam-mode" onchange="updateAntispamOverrideWindow()">
                                        <option value="default">Use Global Default</option>
                                        <option value="stricter">Stricter (shorter window)</option>
                                        <option value="relaxed">Less Strict (longer window)</option>
                                        <option value="custom">Custom Window</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mt-2" id="antispam-window-override-group" style="display: none;">
                                <label class="form-label mb-1" style="font-weight: 600; font-size: 0.8rem;">Window Duration</label>
                                <select class="form-select form-select-sm" id="content-exemption-antispam-window" style="width: auto;">
                                    <option value="15">15 minutes</option>
                                    <option value="30">30 minutes</option>
                                    <option value="60">60 minutes</option>
                                    <option value="120">120 minutes</option>
                                </select>
                            </div>
                            <small class="text-muted d-block mt-2"><i class="fas fa-info-circle me-1"></i>Override the global anti-spam protection for this account/sub-accounts</small>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="content-exemption-reason" class="form-label" style="font-weight: 600; font-size: 0.85rem;">
                            Reason <span class="text-muted" style="font-weight: 400; font-size: 0.75rem;">(optional)</span>
                        </label>
                        <textarea class="form-control" id="content-exemption-reason" rows="2" placeholder="Enter reason for this exemption or override..." style="font-size: 0.85rem;"></textarea>
                        <small class="text-muted">This will be recorded in the audit log for compliance purposes.</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer" style="border-top: 1px solid #e9ecef; padding: 1rem 1.5rem;">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-sm text-white" style="background: #1e3a5f;" onclick="saveContentExemption()">
                    <i class="fas fa-save me-1"></i> <span id="content-exemption-save-btn-text">Save Exemption</span>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="urlRuleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
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
                        <label for="url-rule-name" class="form-label" style="font-weight: 600; font-size: 0.85rem;">Rule Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="url-rule-name" placeholder="e.g., Block bit.ly shortlinks" required>
                        <small class="text-muted">A descriptive name to identify this rule.</small>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="url-match-type" class="form-label" style="font-weight: 600; font-size: 0.85rem;">Match Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="url-match-type" onchange="updateUrlPatternLabel()">
                                <option value="exact">Exact Domain</option>
                                <option value="wildcard">Wildcard Domain</option>
                                <option value="regex">Regex</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="url-rule-type" class="form-label" style="font-weight: 600; font-size: 0.85rem;">Rule Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="url-rule-type">
                                <option value="block">Block</option>
                                <option value="flag">Flag-to-Quarantine</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="url-pattern" class="form-label" style="font-weight: 600; font-size: 0.85rem;" id="url-pattern-label">Pattern <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="url-pattern" placeholder="example.com" required>
                        <small class="text-muted" id="url-pattern-help">Enter the exact domain to match (e.g., example.com)</small>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Status</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="url-rule-enabled" checked style="width: 2.25rem; height: 1.1rem;">
                                <label class="form-check-label" for="url-rule-enabled" style="font-size: 0.85rem; margin-left: 0.25rem;">Enabled</label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Domain Age Check</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="url-apply-domain-age" checked style="width: 2.25rem; height: 1.1rem;">
                                <label class="form-check-label" for="url-apply-domain-age" style="font-size: 0.85rem; margin-left: 0.25rem;">Apply domain age check</label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Test this rule section (collapsed by default) -->
                    <div class="card mt-3" style="border: 1px solid #e9ecef; border-radius: 6px;">
                        <div class="card-header py-2 d-flex justify-content-between align-items-center" style="background: #f8f9fa; cursor: pointer; border-bottom: 1px solid #e9ecef;" data-bs-toggle="collapse" data-bs-target="#url-rule-test-collapse" aria-expanded="false">
                            <div class="d-flex align-items-center gap-2">
                                <i class="fas fa-flask" style="color: #1e3a5f; font-size: 0.8rem;"></i>
                                <span style="font-size: 0.85rem; font-weight: 600; color: #1e3a5f;">Test this rule</span>
                            </div>
                            <i class="fas fa-chevron-down" id="url-rule-test-collapse-icon" style="font-size: 0.65rem; color: #6c757d; transition: transform 0.2s;"></i>
                        </div>
                        <div class="collapse" id="url-rule-test-collapse">
                            <div class="card-body" style="padding: 1rem;">
                                <div class="row g-2 align-items-end">
                                    <div class="col-md-9">
                                        <label class="form-label mb-1" style="font-size: 0.75rem; font-weight: 600;">Input URL</label>
                                        <input type="text" class="form-control form-control-sm" id="url-rule-test-input" placeholder="https://example.com/path">
                                    </div>
                                    <div class="col-md-3">
                                        <button type="button" class="btn btn-sm w-100" style="background: #1e3a5f; color: white;" onclick="runUrlRuleTest()">
                                            <i class="fas fa-play me-1"></i> Run Test
                                        </button>
                                    </div>
                                </div>
                                <div id="url-rule-test-result" class="mt-3" style="display: none;">
                                    <div class="p-3 rounded" id="url-rule-test-result-box" style="background: #f8f9fa; border: 1px solid #e9ecef;">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <span class="badge" id="url-rule-test-result-badge">MATCHED</span>
                                            <span id="url-rule-test-result-action" style="font-size: 0.8rem; font-weight: 600;"></span>
                                        </div>
                                        <div style="font-size: 0.8rem;">
                                            <div><strong>Extracted hostname:</strong> <code id="url-rule-test-hostname">-</code></div>
                                            <div><strong>Matched rule:</strong> <span id="url-rule-test-matched-rule">-</span></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer" style="border-top: 1px solid #e9ecef; padding: 1rem 1.5rem;">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-sm text-white" style="background: #1e3a5f;" onclick="confirmSaveUrlRule()">
                    <i class="fas fa-save me-1"></i> <span id="url-rule-save-btn-text">Save Rule</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- URL Rule Confirmation Modal -->
<div class="modal fade" id="urlRuleConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2" style="background: #1e3a5f; border-bottom: none;">
                <h6 class="modal-title text-white mb-0">
                    <i class="fas fa-check-circle me-2"></i>Confirm Rule Settings
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding: 1.25rem;">
                <p style="font-size: 0.9rem; color: #495057; margin-bottom: 1rem;">Please review the rule settings before saving:</p>
                <div class="p-3 rounded" style="background: #f8f9fa; border: 1px solid #e9ecef;">
                    <table style="width: 100%; font-size: 0.85rem;">
                        <tbody id="url-rule-confirm-summary">
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer py-2" style="border-top: 1px solid #e9ecef;">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Go Back</button>
                <button type="button" class="btn btn-sm text-white" style="background: #1e3a5f;" onclick="executeSaveUrlRule()">
                    <i class="fas fa-check me-1"></i> Confirm Save
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

<div class="modal fade" id="urlExemptionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: #1e3a5f; border-bottom: none;">
                <h5 class="modal-title text-white" id="url-exemption-modal-title">
                    <i class="fas fa-shield-alt me-2"></i>Add URL Exemption
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding: 1.5rem;">
                <form id="url-exemption-form">
                    <input type="hidden" id="url-exemption-id" value="">
                    
                    <div class="mb-3">
                        <label for="url-exemption-account-search" class="form-label" style="font-weight: 600; font-size: 0.85rem;">Account <span class="text-danger">*</span></label>
                        <div class="position-relative">
                            <input type="text" class="form-control" id="url-exemption-account-search" placeholder="Search by account name or ID..." autocomplete="off">
                            <input type="hidden" id="url-exemption-account" value="">
                            <div class="dropdown-menu w-100" id="url-exemption-account-dropdown" style="max-height: 200px; overflow-y: auto;"></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Exemption Type <span class="text-danger">*</span></label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="url-exemption-type" id="url-exemption-type-domain-age" value="domain_age" checked onchange="toggleUrlExemptionType()">
                                <label class="form-check-label" for="url-exemption-type-domain-age" style="font-size: 0.85rem;">
                                    <i class="fas fa-clock me-1" style="color: #1e40af;"></i> Domain Age
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="url-exemption-type" id="url-exemption-type-url-rule" value="url_rule" onchange="toggleUrlExemptionType()">
                                <label class="form-check-label" for="url-exemption-type-url-rule" style="font-size: 0.85rem;">
                                    <i class="fas fa-link me-1" style="color: #6b21a8;"></i> URL Rules
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3" id="url-exemption-rules-group" style="display: none;">
                        <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Exempt from Rules <span class="text-danger">*</span></label>
                        <div id="url-exemption-rules-list" class="border rounded p-2" style="max-height: 150px; overflow-y: auto; background: #f8f9fa;">
                            <small class="text-muted">Loading rules...</small>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="url-exemption-reason" class="form-label" style="font-weight: 600; font-size: 0.85rem;">
                            Reason <span class="text-muted" style="font-weight: 400; font-size: 0.75rem;">(optional)</span>
                        </label>
                        <textarea class="form-control" id="url-exemption-reason" rows="2" placeholder="Enter reason for this exemption..." style="font-size: 0.85rem;"></textarea>
                        <small class="text-muted">This will be recorded in the audit log.</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer" style="border-top: 1px solid #e9ecef; padding: 1rem 1.5rem;">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-sm text-white" style="background: #1e3a5f;" onclick="saveUrlExemption()">
                    <i class="fas fa-save me-1"></i> <span id="url-exemption-save-btn-text">Save Exemption</span>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="domainAgeConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header py-2" style="background: #1e3a5f; border-bottom: none;">
                <h6 class="modal-title text-white mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>Confirm Changes
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding: 1rem;">
                <p style="font-size: 0.85rem; margin-bottom: 0.75rem;">You are about to update the domain age settings:</p>
                <table class="table table-sm mb-0" style="font-size: 0.8rem;">
                    <thead>
                        <tr style="background: #f8f9fa;">
                            <th style="width: 40%; padding: 0.35rem;">Setting</th>
                            <th style="width: 30%; padding: 0.35rem;">Before</th>
                            <th style="width: 30%; padding: 0.35rem;">After</th>
                        </tr>
                    </thead>
                    <tbody id="domain-age-confirm-diff">
                    </tbody>
                </table>
            </div>
            <div class="modal-footer py-2" style="border-top: 1px solid #e9ecef;">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-sm text-white" style="background: #1e3a5f;" onclick="executeSaveDomainAgeSettings()">
                    <i class="fas fa-check me-1"></i> Confirm Save
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addUrlExemptionGlobalModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header py-2" style="background: #1e3a5f; border-bottom: none;">
                <h6 class="modal-title text-white mb-0">
                    <i class="fas fa-shield-alt me-2"></i>Add URL Exemption
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding: 1.25rem;">
                <form id="add-url-exemption-global-form">
                    <!-- Section 1: Scope -->
                    <div class="mb-3 p-3 rounded" style="background: #f8f9fa; border: 1px solid #e9ecef;">
                        <h6 style="font-size: 0.85rem; font-weight: 600; color: #1e3a5f; margin-bottom: 0.75rem;">
                            <i class="fas fa-building me-2"></i>Scope
                        </h6>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label mb-1" style="font-size: 0.75rem; font-weight: 600;">Account <span class="text-danger">*</span></label>
                                <div class="position-relative">
                                    <div class="input-group input-group-sm">
                                        <input type="text" class="form-control form-control-sm" id="global-exemption-account-search" placeholder="Search account..." autocomplete="off" style="border-right: none;">
                                        <button class="btn btn-outline-secondary" type="button" id="global-exemption-account-toggle" onclick="toggleGlobalExemptionAccountDropdown()" style="border-left: none; background: #fff;">
                                            <i class="fas fa-chevron-down" style="font-size: 0.65rem;"></i>
                                        </button>
                                    </div>
                                    <input type="hidden" id="global-exemption-account-id" value="">
                                    <div class="dropdown-menu w-100" id="global-exemption-account-dropdown" style="max-height: 200px; overflow-y: auto;"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label mb-1" style="font-size: 0.75rem; font-weight: 600;">Sub-accounts</label>
                                <div class="form-check mb-1">
                                    <input class="form-check-input" type="checkbox" id="global-exemption-all-subaccounts" checked onchange="toggleGlobalExemptionSubaccounts()">
                                    <label class="form-check-label" for="global-exemption-all-subaccounts" style="font-size: 0.8rem;">All sub-accounts</label>
                                </div>
                                <select class="form-select form-select-sm" id="global-exemption-subaccounts" multiple disabled style="height: 60px; font-size: 0.8rem;">
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Section 2: Exemption Type -->
                    <div class="mb-3">
                        <h6 style="font-size: 0.85rem; font-weight: 600; color: #1e3a5f; margin-bottom: 0.75rem;">
                            <i class="fas fa-cog me-2"></i>Exemption Type
                        </h6>
                        <div class="d-flex gap-3 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="global-exemption-type" id="global-exemption-type-domain-age" value="domain_age" checked onchange="toggleGlobalExemptionType()">
                                <label class="form-check-label" for="global-exemption-type-domain-age" style="font-size: 0.85rem;">
                                    <i class="fas fa-clock me-1" style="color: #1e40af;"></i> Domain Age Override
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="global-exemption-type" id="global-exemption-type-domains" value="domains" onchange="toggleGlobalExemptionType()">
                                <label class="form-check-label" for="global-exemption-type-domains" style="font-size: 0.85rem;">
                                    <i class="fas fa-globe me-1" style="color: #059669;"></i> Allowlisted Domains
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="global-exemption-type" id="global-exemption-type-rules" value="rules" onchange="toggleGlobalExemptionType()">
                                <label class="form-check-label" for="global-exemption-type-rules" style="font-size: 0.85rem;">
                                    <i class="fas fa-link me-1" style="color: #7c3aed;"></i> Rule Exemptions
                                </label>
                            </div>
                        </div>
                        
                        <!-- A) Domain Age Override -->
                        <div id="global-exemption-domain-age-section" class="p-3 rounded" style="background: #eff6ff; border: 1px solid #bfdbfe;">
                            <div class="row g-2 align-items-end">
                                <div class="col-md-5">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="global-exemption-disable-domain-age" onchange="toggleGlobalExemptionDomainAgeMode()">
                                        <label class="form-check-label" for="global-exemption-disable-domain-age" style="font-size: 0.8rem; font-weight: 600;">Disable domain-age enforcement</label>
                                    </div>
                                </div>
                                <div class="col-md-3" id="global-exemption-threshold-group">
                                    <label class="form-label mb-1" style="font-size: 0.75rem; font-weight: 600;">Custom Threshold (hours)</label>
                                    <input type="number" class="form-control form-control-sm" id="global-exemption-threshold-hours" value="24" min="0" max="8760">
                                </div>
                                <div class="col-md-4" id="global-exemption-action-group">
                                    <label class="form-label mb-1" style="font-size: 0.75rem; font-weight: 600;">Action Override</label>
                                    <select class="form-select form-select-sm" id="global-exemption-action-override">
                                        <option value="">Use default</option>
                                        <option value="block">Block</option>
                                        <option value="flag">Flag to Quarantine</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <!-- B) Allowlisted Domains -->
                        <div id="global-exemption-domains-section" class="p-3 rounded" style="background: #ecfdf5; border: 1px solid #a7f3d0; display: none;">
                            <label class="form-label mb-1" style="font-size: 0.75rem; font-weight: 600;">Domains <span class="text-danger">*</span></label>
                            <div class="form-control" id="global-exemption-domains-container" style="min-height: 60px; display: flex; flex-wrap: wrap; gap: 4px; padding: 0.5rem; cursor: text;" onclick="document.getElementById('global-exemption-domains-input').focus()">
                                <input type="text" id="global-exemption-domains-input" class="border-0" style="flex: 1; min-width: 120px; outline: none; font-size: 0.85rem;" placeholder="Type or paste domains..." onkeydown="handleDomainChipInput(event)" onpaste="handleDomainPaste(event)">
                            </div>
                            <small class="text-muted" style="font-size: 0.7rem;">Press Enter or comma to add. Paste multiple domains separated by commas, spaces, or newlines.</small>
                        </div>
                        
                        <!-- C) Rule Exemptions -->
                        <div id="global-exemption-rules-section" class="p-3 rounded" style="background: #f5f3ff; border: 1px solid #ddd6fe; display: none;">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label mb-0" style="font-size: 0.75rem; font-weight: 600;">URL Rules <span class="text-danger">*</span></label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="global-exemption-select-all-rules" onchange="toggleSelectAllUrlRules()">
                                    <label class="form-check-label" for="global-exemption-select-all-rules" style="font-size: 0.75rem;">Select all</label>
                                </div>
                            </div>
                            <div id="global-exemption-rules-list" class="border rounded p-2" style="max-height: 150px; overflow-y: auto; background: white;">
                                <small class="text-muted">Loading rules...</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Reason -->
                    <div class="mb-0">
                        <label class="form-label mb-1" style="font-size: 0.75rem; font-weight: 600;">
                            Reason <span class="text-muted" style="font-weight: 400;">(optional)</span>
                        </label>
                        <textarea class="form-control form-control-sm" id="global-exemption-reason" rows="2" placeholder="Enter reason for this exemption..." style="font-size: 0.85rem;"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer py-2" style="border-top: 1px solid #e9ecef;">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-sm text-white" style="background: #1e3a5f;" onclick="saveGlobalUrlExemption()">
                    <i class="fas fa-check me-1"></i> Apply Exemption
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
                                <span><strong>From:</strong> <span id="qrn-view-senderid" style="font-weight: 600; color: #1e3a5f; font-size: 0.75rem;"></span></span>
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
</script>

<div class="modal fade" id="addSenderIdExemptionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: #f8f9fa; border-bottom: 1px solid #e9ecef;">
                <h5 class="modal-title" style="font-weight: 600; color: #1e3a5f;">
                    <i class="fas fa-shield-alt me-2"></i>Add Manual Exemption
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding: 0;">
                <div class="exemption-wizard">
                    <div class="wizard-steps" style="display: flex; justify-content: center; padding: 1rem; background: #f8fafc; border-bottom: 1px solid #e9ecef;">
                        <div class="wizard-step active" data-step="1" style="display: flex; align-items: center; gap: 0.5rem;">
                            <span class="step-number" style="width: 28px; height: 28px; border-radius: 50%; background: #1e3a5f; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; font-weight: 600;">1</span>
                            <span class="step-label" style="font-size: 0.85rem; font-weight: 500; color: #1e3a5f;">SenderID Definition</span>
                        </div>
                        <div class="step-connector" style="width: 60px; height: 2px; background: #e9ecef; margin: 0 1rem; align-self: center;"></div>
                        <div class="wizard-step" data-step="2" style="display: flex; align-items: center; gap: 0.5rem;">
                            <span class="step-number" style="width: 28px; height: 28px; border-radius: 50%; background: #e9ecef; color: #6c757d; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; font-weight: 600;">2</span>
                            <span class="step-label" style="font-size: 0.85rem; font-weight: 500; color: #6c757d;">Assignment Scope</span>
                        </div>
                    </div>
                    
                    <form id="addExemptionForm" style="padding: 1.25rem;">
                        <div id="exemption-step-1">
                            <div class="alert" style="background: rgba(30, 58, 95, 0.06); border: 1px solid rgba(30, 58, 95, 0.12); color: #1e3a5f; font-size: 0.85rem; margin-bottom: 1.25rem;">
                                <strong>Step 1: SenderID Type & Value</strong> – Choose the type and enter the SenderID value.
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">SenderID Type <span class="text-danger">*</span></label>
                                <div class="exemption-type-selector" style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                                    <div class="exemption-type-card selected" data-type="alphanumeric" style="flex: 1; min-width: 120px; max-width: 160px; padding: 1rem 0.75rem; border: 2px solid #1e3a5f; border-radius: 0.5rem; text-align: center; cursor: pointer; background: rgba(30, 58, 95, 0.05); transition: all 0.2s;">
                                        <div style="width: 40px; height: 40px; border-radius: 50%; background: #1e3a5f; display: flex; align-items: center; justify-content: center; margin: 0 auto 0.5rem;">
                                            <i class="fas fa-font" style="color: #fff;"></i>
                                        </div>
                                        <div style="font-weight: 600; font-size: 0.85rem; color: #343a40;">Alphanumeric</div>
                                        <div style="font-size: 0.7rem; color: #6c757d;">e.g. MYBRAND</div>
                                    </div>
                                    <div class="exemption-type-card" data-type="numeric" style="flex: 1; min-width: 120px; max-width: 160px; padding: 1rem 0.75rem; border: 2px solid #e9ecef; border-radius: 0.5rem; text-align: center; cursor: pointer; background: #fff; transition: all 0.2s;">
                                        <div style="width: 40px; height: 40px; border-radius: 50%; background: rgba(30, 58, 95, 0.1); display: flex; align-items: center; justify-content: center; margin: 0 auto 0.5rem;">
                                            <i class="fas fa-phone" style="color: #1e3a5f;"></i>
                                        </div>
                                        <div style="font-weight: 600; font-size: 0.85rem; color: #343a40;">Numeric</div>
                                        <div style="font-size: 0.7rem; color: #6c757d;">e.g. 447700...</div>
                                    </div>
                                    <div class="exemption-type-card" data-type="shortcode" style="flex: 1; min-width: 120px; max-width: 160px; padding: 1rem 0.75rem; border: 2px solid #e9ecef; border-radius: 0.5rem; text-align: center; cursor: pointer; background: #fff; transition: all 0.2s;">
                                        <div style="width: 40px; height: 40px; border-radius: 50%; background: rgba(30, 58, 95, 0.1); display: flex; align-items: center; justify-content: center; margin: 0 auto 0.5rem;">
                                            <i class="fas fa-hashtag" style="color: #1e3a5f;"></i>
                                        </div>
                                        <div style="font-weight: 600; font-size: 0.85rem; color: #343a40;">Shortcode</div>
                                        <div style="font-size: 0.7rem; color: #6c757d;">e.g. 60123</div>
                                    </div>
                                </div>
                                <input type="hidden" id="exemption-type" value="alphanumeric">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">SenderID Value <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="exemption-senderid" placeholder="e.g. MYCOMPANY" maxlength="11" style="font-size: 1.1rem; font-weight: 600; letter-spacing: 1px; text-transform: uppercase; font-family: monospace;">
                                <div class="d-flex justify-content-between mt-1">
                                    <small class="text-muted" id="exemption-senderid-hint">3-11 characters: A-Z a-z 0-9</small>
                                    <small class="text-muted"><span id="exemption-senderid-charcount">0</span>/11</small>
                                </div>
                                <div class="invalid-feedback" id="exemption-senderid-error"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Category <span class="text-danger">*</span></label>
                                <select class="form-select" id="exemption-category">
                                    <option value="">Select category...</option>
                                    <option value="government_healthcare">Government and Healthcare</option>
                                    <option value="banking_finance">Banking and Finance</option>
                                    <option value="delivery_logistics">Delivery and logistics</option>
                                    <option value="miscellaneous">Miscellaneous</option>
                                    <option value="generic">Generic</option>
                                </select>
                            </div>
                            
                            <div class="mb-0">
                                <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Notes (Optional)</label>
                                <textarea class="form-control" id="exemption-notes" rows="2" placeholder="Reason for exemption..." style="font-size: 0.85rem;"></textarea>
                            </div>
                        </div>
                        
                        <div id="exemption-step-2" style="display: none;">
                            <div class="alert" style="background: rgba(30, 58, 95, 0.06); border: 1px solid rgba(30, 58, 95, 0.12); color: #1e3a5f; font-size: 0.85rem; margin-bottom: 1.25rem;">
                                <strong>Step 2: Assignment Scope</strong> – Define where this exemption applies.
                            </div>
                            
                            <div class="exemption-summary-card" style="background: #f8f9fa; border-radius: 0.5rem; padding: 1rem; margin-bottom: 1.25rem; border: 1px solid #e9ecef;">
                                <div style="font-size: 0.75rem; color: #6c757d; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem;">SenderID to Exempt</div>
                                <div style="display: flex; align-items: center; gap: 0.75rem;">
                                    <code id="exemption-summary-senderid" style="font-size: 1.1rem; padding: 0.25rem 0.75rem; background: #e9ecef; border-radius: 4px;">-</code>
                                    <span class="badge" id="exemption-summary-type" style="background: #1e3a5f; font-size: 0.7rem;">Alphanumeric</span>
                                    <span class="badge" id="exemption-summary-category" style="background: #6c757d; font-size: 0.7rem;">-</span>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Assign To <span class="text-danger">*</span></label>
                                <div class="exemption-scope-options" style="display: flex; flex-direction: column; gap: 0.5rem;">
                                    <label class="scope-option selected" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; border: 2px solid #1e3a5f; border-radius: 0.5rem; cursor: pointer; background: rgba(30, 58, 95, 0.03);">
                                        <input type="radio" name="exemption-scope" value="global" checked style="width: 18px; height: 18px; accent-color: #1e3a5f;">
                                        <div>
                                            <div style="font-weight: 600; font-size: 0.85rem; color: #343a40;"><i class="fas fa-globe me-1 text-info"></i>Global (All Accounts)</div>
                                            <div style="font-size: 0.75rem; color: #6c757d;">Exemption applies platform-wide</div>
                                        </div>
                                    </label>
                                    <label class="scope-option" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; border: 2px solid #e9ecef; border-radius: 0.5rem; cursor: pointer; background: #fff;">
                                        <input type="radio" name="exemption-scope" value="account" style="width: 18px; height: 18px; accent-color: #1e3a5f;">
                                        <div>
                                            <div style="font-weight: 600; font-size: 0.85rem; color: #343a40;"><i class="fas fa-building me-1 text-secondary"></i>Specific Account</div>
                                            <div style="font-size: 0.75rem; color: #6c757d;">Exemption applies to one account only</div>
                                        </div>
                                    </label>
                                    <label class="scope-option" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; border: 2px solid #e9ecef; border-radius: 0.5rem; cursor: pointer; background: #fff;">
                                        <input type="radio" name="exemption-scope" value="subaccount" style="width: 18px; height: 18px; accent-color: #1e3a5f;">
                                        <div>
                                            <div style="font-weight: 600; font-size: 0.85rem; color: #343a40;"><i class="fas fa-sitemap me-1" style="color: #6f42c1;"></i>Specific Sub-account(s)</div>
                                            <div style="font-size: 0.75rem; color: #6c757d;">Exemption applies to selected sub-accounts</div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="mb-3" id="exemption-account-group" style="display: none;">
                                <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Select Account <span class="text-danger">*</span></label>
                                <select class="form-select" id="exemption-account" style="font-size: 0.9rem;">
                                    <option value="">Type to search accounts...</option>
                                    <option value="ACC-1234">Acme Corporation (ACC-1234)</option>
                                    <option value="ACC-5678">Finance Ltd (ACC-5678)</option>
                                    <option value="ACC-4001">RetailMax Group (ACC-4001)</option>
                                    <option value="ACC-4005">HealthPlus Care (ACC-4005)</option>
                                    <option value="ACC-4008">TechStartup Inc (ACC-4008)</option>
                                    <option value="ACC-4009">FoodDelivery Pro (ACC-4009)</option>
                                    <option value="ACC-10045">TechStart Ltd (ACC-10045)</option>
                                    <option value="ACC-10089">HealthFirst UK (ACC-10089)</option>
                                    <option value="ACC-10112">E-Commerce Hub (ACC-10112)</option>
                                </select>
                            </div>
                            
                            <div class="mb-3" id="exemption-subaccount-group" style="display: none;">
                                <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Select Sub-account(s) <span class="text-danger">*</span></label>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="exemption-all-subaccounts">
                                    <label class="form-check-label" for="exemption-all-subaccounts" style="font-size: 0.85rem;">
                                        <strong>All sub-accounts</strong> (current and future)
                                    </label>
                                </div>
                                <div id="exemption-subaccount-list" style="max-height: 150px; overflow-y: auto; border: 1px solid #e9ecef; border-radius: 0.375rem; padding: 0.5rem;">
                                    <div class="text-muted text-center py-2" style="font-size: 0.8rem;">
                                        <i class="fas fa-info-circle me-1"></i>Select an account first
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-0">
                                <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Expiry Date (Optional)</label>
                                <input type="date" class="form-control" id="exemption-expiry" style="font-size: 0.9rem;">
                                <small class="text-muted">Leave empty for no expiry</small>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="modal-footer" style="border-top: 1px solid #e9ecef; display: flex; justify-content: space-between;">
                <button type="button" class="btn btn-outline-secondary btn-sm" id="exemption-back-btn" style="display: none;" onclick="exemptionWizardBack()">
                    <i class="fas fa-arrow-left me-1"></i>Back
                </button>
                <div style="margin-left: auto; display: flex; gap: 0.5rem;">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="sec-primary-btn" id="exemption-next-btn" onclick="exemptionWizardNext()">
                        Next <i class="fas fa-arrow-right ms-1"></i>
                    </button>
                    <button type="button" class="sec-primary-btn" id="exemption-confirm-btn" style="display: none;" onclick="saveNewExemption()">
                        <i class="fas fa-check me-1"></i>Confirm & Add
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

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
<!-- SheetJS for XLSX parsing -->
<script src="https://cdn.sheetjs.com/xlsx-0.20.1/package/dist/xlsx.full.min.js"></script>
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
        contentExemptions: [],
        urlRules: [],
        normalisationRules: [],
        quarantinedMessages: [],
        senderIdApprovals: [],
        manualExemptions: [],
        senderIdExemptions: [],
        enforcementOverrides: {},
        accounts: [
            { id: 'ACC-10045', name: 'TechStart Ltd', subAccounts: [{ id: 'SUB-001', name: 'Marketing Dept' }, { id: 'SUB-002', name: 'Sales Team' }] },
            { id: 'ACC-10089', name: 'HealthFirst UK', subAccounts: [{ id: 'SUB-003', name: 'Patient Comms' }] },
            { id: 'ACC-10112', name: 'E-Commerce Hub', subAccounts: [{ id: 'SUB-005', name: 'Promotions' }, { id: 'SUB-006', name: 'Customer Service' }] },
            { id: 'ACC-10150', name: 'HMRC', subAccounts: [{ id: 'SUB-007', name: 'Tax Alerts' }, { id: 'SUB-008', name: 'Self Assessment' }] },
            { id: 'ACC-10200', name: 'Royal Bank', subAccounts: [{ id: 'SUB-009', name: 'Fraud Alerts' }, { id: 'SUB-010', name: 'Transaction Notices' }] }
        ]
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
        console.log('[SecurityComplianceControls] mockData loaded with ' + mockData.quarantinedMessages.length + ' quarantine messages');
        renderAllTabs();
        setupEventListeners();
        initDomainAgeSettings();
        console.log('[SecurityComplianceControls] Initialized successfully');
    }
    
    function buildExemptionsList() {
        var exemptions = [];
        
        var approvedSenderIds = (mockData.senderIdApprovals || []).filter(function(appr) {
            return appr.approvalStatus === 'approved';
        });
        
        approvedSenderIds.forEach(function(appr) {
            var override = mockData.enforcementOverrides[appr.id];
            var enforcementStatus = override ? override.status : 'active';
            
            exemptions.push({
                id: 'EXM-' + appr.id,
                sourceId: appr.id,
                senderId: appr.senderId,
                normalisedValue: appr.normalisedValue || appr.senderId.toUpperCase(),
                type: appr.type || 'alphanumeric',
                scope: appr.scope || 'account',
                subAccounts: appr.subAccounts || [],
                accountId: appr.accountId,
                accountName: appr.accountName,
                source: 'approval',
                category: appr.category,
                approvedBy: appr.approvedBy,
                approvedAt: appr.approvedAt,
                updatedAt: appr.updatedAt || appr.approvedAt,
                expiry: null,
                approvalStatus: appr.approvalStatus,
                enforcementStatus: enforcementStatus,
                notes: appr.notes
            });
        });
        
        (mockData.manualExemptions || []).forEach(function(man) {
            var override = mockData.enforcementOverrides[man.id];
            var enforcementStatus = override ? override.status : 'active';
            
            exemptions.push({
                id: 'EXM-' + man.id,
                sourceId: man.id,
                senderId: man.senderId,
                normalisedValue: man.normalisedValue || man.senderId.toUpperCase(),
                type: man.type || 'alphanumeric',
                scope: man.scope || 'global',
                subAccounts: man.subAccounts || [],
                accountId: man.accountId,
                accountName: man.accountName,
                source: 'manual',
                category: man.category,
                approvedBy: man.addedBy,
                approvedAt: man.addedAt,
                updatedAt: man.updatedAt || man.addedAt,
                expiry: man.expiry,
                approvalStatus: 'n/a',
                enforcementStatus: enforcementStatus,
                notes: man.notes
            });
        });
        
        return exemptions;
    }
    
    function rebuildExemptions() {
        mockData.senderIdExemptions = buildExemptionsList();
        
        // Sync exemptions with MessageEnforcementService for real-time enforcement
        if (typeof window.MessageEnforcementService !== 'undefined' && 
            typeof window.MessageEnforcementService.loadSenderIdExemptions === 'function') {
            window.MessageEnforcementService.loadSenderIdExemptions(mockData.senderIdExemptions);
            console.log('[SecurityComplianceControls] Synced exemptions with MessageEnforcementService:', 
                window.MessageEnforcementService.getExemptionStats());
        }
        
        // Emit audit event for exemption sync
        logAuditEvent('EXEMPTIONS_SYNC_COMPLETED', {
            totalExemptions: mockData.senderIdExemptions.length,
            globalCount: mockData.senderIdExemptions.filter(function(e) { return e.scope === 'global'; }).length,
            accountCount: mockData.senderIdExemptions.filter(function(e) { return e.scope === 'account'; }).length,
            subAccountCount: mockData.senderIdExemptions.filter(function(e) { return e.scope === 'subaccount'; }).length
        });
    }
    
    function normaliseSenderId(senderId) {
        if (!senderId) return '';
        var normalised = senderId.toUpperCase().trim();
        
        if (typeof window.MessageEnforcementService !== 'undefined' && 
            typeof window.MessageEnforcementService.evaluateAgainstRules === 'function') {
            return normalised;
        }
        
        return normalised;
    }
    
    function validateSenderIdFormat(senderId) {
        if (!senderId) return { valid: false, error: 'SenderID is required' };
        
        senderId = senderId.trim();
        
        if (senderId.length < 3) {
            return { valid: false, error: 'SenderID must be at least 3 characters' };
        }
        if (senderId.length > 11) {
            return { valid: false, error: 'SenderID must be 11 characters or less' };
        }
        
        if (!/^[A-Za-z0-9]+$/.test(senderId)) {
            return { valid: false, error: 'SenderID must contain only alphanumeric characters' };
        }
        
        if (/^\d+$/.test(senderId)) {
            return { valid: false, error: 'SenderID cannot be all numeric' };
        }
        
        return { valid: true, normalised: senderId.toUpperCase() };
    }
    
    function setEnforcementOverride(sourceId, status, reason) {
        mockData.enforcementOverrides[sourceId] = {
            status: status,
            reason: reason,
            setBy: currentAdmin.email,
            setAt: formatDateTime(new Date())
        };
        
        rebuildExemptions();
        
        logAuditEvent('EXEMPTION_ENFORCEMENT_OVERRIDE', {
            sourceId: sourceId,
            newStatus: status,
            reason: reason
        });
        
        console.log('[SecurityComplianceControls] Enforcement override set:', sourceId, status);
    }

    function loadMockData() {
        // TODO: Backend integration - fetch data from API endpoints
        
        // SenderID Rules mock data
        mockData.senderIdRules = [
            { id: 'SID-001', name: 'HSBC Banking Brand', baseSenderId: 'HSBC', ruleType: 'block', category: 'banking_finance', applyNormalisation: true, status: 'active', createdBy: 'admin@quicksms.co.uk', updatedAt: '15-01-2026 09:30' },
            { id: 'SID-002', name: 'Barclays Banking Brand', baseSenderId: 'Barclays', ruleType: 'block', category: 'banking_finance', applyNormalisation: true, status: 'active', createdBy: 'admin@quicksms.co.uk', updatedAt: '15-01-2026 09:32' },
            { id: 'SID-003', name: 'HMRC Government', baseSenderId: 'HMRC', ruleType: 'block', category: 'government_healthcare', applyNormalisation: true, status: 'active', createdBy: 'admin@quicksms.co.uk', updatedAt: '16-01-2026 10:15' },
            { id: 'SID-004', name: 'NHS Healthcare', baseSenderId: 'NHS', ruleType: 'flag', category: 'government_healthcare', applyNormalisation: false, status: 'active', createdBy: 'compliance@quicksms.co.uk', updatedAt: '17-01-2026 14:20' }
        ];

        // Content Rules mock data
        mockData.contentRules = [
            { id: 'CR-001', name: 'Bitcoin Investment Scam', matchType: 'keyword', matchValue: 'bitcoin investment', ruleType: 'block', applyNormalisation: true, status: 'active', updatedAt: '10-01-2026 11:00' },
            { id: 'CR-002', name: 'Guaranteed Returns Fraud', matchType: 'keyword', matchValue: 'guaranteed returns', ruleType: 'block', applyNormalisation: true, status: 'active', updatedAt: '10-01-2026 11:05' },
            { id: 'CR-003', name: 'Click to Claim Phishing', matchType: 'keyword', matchValue: 'click here to claim', ruleType: 'flag', applyNormalisation: false, status: 'active', updatedAt: '12-01-2026 09:45' },
            { id: 'CR-004', name: 'Urgency Scam Indicator', matchType: 'regex', matchValue: 'urgent.*action.*required', ruleType: 'flag', applyNormalisation: true, status: 'active', updatedAt: '14-01-2026 16:30' },
            { id: 'CR-005', name: 'Free Prize Scam', matchType: 'keyword', matchValue: 'free iphone', ruleType: 'block', applyNormalisation: false, status: 'inactive', updatedAt: '18-01-2026 10:00' }
        ];

        mockData.senderIdApprovals = [];
        mockData.manualExemptions = [];
        mockData.enforcementOverrides = {};
        mockData.senderIdExemptions = buildExemptionsList();
        
        // Content Exemptions mock data
        mockData.contentExemptions = [
            { id: 'CEX-001', accountId: 'ACC-10045', accountName: 'TechStart Ltd', subAccounts: ['all'], exemptionType: 'content_rules', rulesExempted: ['CR-003'], reason: 'Legitimate marketing campaigns', status: 'active', createdAt: '20-01-2026 14:00', createdBy: 'admin@quicksms.co.uk' }
        ];
        
        // URL Rules mock data
        mockData.urlRules = [
            { id: 'URL-001', name: 'Bit.ly Shortener', pattern: 'bit.ly', matchType: 'wildcard', ruleType: 'flag', status: 'active', updatedAt: '08-01-2026 10:00' },
            { id: 'URL-002', name: 'TinyURL Shortener', pattern: 'tinyurl.com', matchType: 'wildcard', ruleType: 'flag', status: 'active', updatedAt: '08-01-2026 10:05' },
            { id: 'URL-003', name: 'High-Risk TLD .xyz', pattern: '*.xyz', matchType: 'wildcard', ruleType: 'block', status: 'active', updatedAt: '09-01-2026 11:30' },
            { id: 'URL-004', name: 'High-Risk TLD .tk', pattern: '*.tk', matchType: 'wildcard', ruleType: 'block', status: 'active', updatedAt: '09-01-2026 11:35' },
            { id: 'URL-005', name: 'Verify Account Phishing', pattern: 'verify-account', matchType: 'exact', ruleType: 'block', status: 'active', updatedAt: '11-01-2026 09:00' },
            { id: 'URL-006', name: 'Login Secure Phishing', pattern: 'login-secure', matchType: 'regex', ruleType: 'block', status: 'active', updatedAt: '11-01-2026 09:05' }
        ];
        
        mockData.domainAgeSettings = {
            enabled: true,
            minAgeHours: 72,
            action: 'quarantine',
            updatedAt: '25-01-2026 16:45',
            updatedBy: 'admin@quicksms.co.uk'
        };
        
        // Domain Allowlist mock data
        mockData.domainAllowlist = [
            { id: 'DA-001', domain: 'google.com', scope: 'global', type: 'trusted', addedAt: '01-01-2026 10:00', addedBy: 'admin@quicksms.co.uk' },
            { id: 'DA-002', domain: 'microsoft.com', scope: 'global', type: 'trusted', addedAt: '01-01-2026 10:05', addedBy: 'admin@quicksms.co.uk' },
            { id: 'DA-003', domain: 'apple.com', scope: 'global', type: 'trusted', addedAt: '01-01-2026 10:10', addedBy: 'admin@quicksms.co.uk' },
            { id: 'DA-004', domain: 'amazon.co.uk', scope: 'global', type: 'trusted', addedAt: '02-01-2026 09:00', addedBy: 'admin@quicksms.co.uk' },
            { id: 'DA-005', domain: 'gov.uk', scope: 'global', type: 'trusted', addedAt: '02-01-2026 09:15', addedBy: 'compliance@quicksms.co.uk' }
        ];
        
        // Threshold Overrides mock data
        mockData.thresholdOverrides = [
            { id: 'TO-001', accountId: 'ACC-10089', accountName: 'HealthFirst UK', subAccounts: ['all'], thresholdHours: 24, action: 'quarantine', reason: 'Trusted healthcare provider', createdAt: '22-01-2026 11:00', createdBy: 'admin@quicksms.co.uk' }
        ];
        
        mockData.domainAgeExceptions = [];
        
        // URL Exemptions mock data
        mockData.urlExemptions = [
            { id: 'UEX-001', accountId: 'ACC-10045', accountName: 'TechStart Ltd', subAccounts: ['all'], exemptionType: 'url_rules', rulesExempted: ['URL-001', 'URL-002'], reason: 'Uses branded short URLs', status: 'active', createdAt: '23-01-2026 10:30', createdBy: 'admin@quicksms.co.uk' }
        ];

        mockData.baseCharacterLibrary = (function() {
            var library = [];
            
            var letterEquivalents = {
                'A': { equivalents: ['a', '4', '@', 'À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'à', 'á', 'â', 'ã', 'ä', 'å', 'Α', 'α', 'А', 'а', 'Ά'], notes: 'Lowercase a, digit 4, @, accented variants, Greek Alpha, Cyrillic A' },
                'B': { equivalents: ['b', '8', 'Β', 'β', 'В', 'в', 'ϐ', 'ь'], notes: 'Lowercase b, digit 8, Greek Beta, Cyrillic Ve' },
                'C': { equivalents: ['c', '(', 'С', 'с', 'Ϲ', 'ϲ'], notes: 'Lowercase c, parenthesis, Cyrillic Es' },
                'D': { equivalents: ['d'], notes: 'Lowercase d' },
                'E': { equivalents: ['e', '3', 'È', 'É', 'Ê', 'Ë', 'è', 'é', 'ê', 'ë', 'Ε', 'ε', 'Е', 'е', 'ё', 'Έ'], notes: 'Lowercase e, digit 3, accented variants, Greek Epsilon, Cyrillic Ie' },
                'F': { equivalents: ['f'], notes: 'Lowercase f' },
                'G': { equivalents: ['g', '9', 'ɡ'], notes: 'Lowercase g, digit 9' },
                'H': { equivalents: ['h', 'Η', 'Н', 'Ή', 'һ'], notes: 'Lowercase h, Greek Eta, Cyrillic En, Cyrillic Shha' },
                'I': { equivalents: ['i', '1', 'l', 'L', '|', 'Ì', 'Í', 'Î', 'Ï', 'ì', 'í', 'î', 'ï', 'Ι', 'ι', 'І', 'і', 'Ί', 'ί', 'ӏ'], notes: 'Lowercase i/l, digit 1, pipe, accented variants, Greek Iota, Cyrillic I' },
                'J': { equivalents: ['j', 'Ј', 'ј'], notes: 'Lowercase j, Cyrillic Je' },
                'K': { equivalents: ['k', 'Κ', 'κ', 'К', 'к'], notes: 'Lowercase k, Greek Kappa, Cyrillic Ka' },
                'L': { equivalents: ['l', '1', 'I', 'i', '|', 'ӏ', 'Ł', 'Ĺ', 'Ľ'], notes: 'Lowercase l, digit 1, I variants, Cyrillic Palochka, Polish L' },
                'M': { equivalents: ['m', 'Μ', 'М', 'м'], notes: 'Lowercase m, Greek Mu, Cyrillic Em' },
                'N': { equivalents: ['n', 'Ν', 'ν', 'Ň', 'п'], notes: 'Lowercase n, Greek Nu, Cyrillic Pe' },
                'O': { equivalents: ['o', '0', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'ò', 'ó', 'ô', 'õ', 'ö', 'Ο', 'ο', 'О', 'о', 'Ό', 'ό'], notes: 'Lowercase o, digit 0, accented variants, Greek Omicron, Cyrillic O' },
                'P': { equivalents: ['p', 'Ρ', 'ρ', 'Р', 'р'], notes: 'Lowercase p, Greek Rho, Cyrillic Er' },
                'Q': { equivalents: ['q'], notes: 'Lowercase q' },
                'R': { equivalents: ['r', 'г'], notes: 'Lowercase r, Cyrillic Ghe' },
                'S': { equivalents: ['s', '5', '$', 'Ѕ', 'ѕ'], notes: 'Lowercase s, digit 5, dollar sign, Cyrillic Dze' },
                'T': { equivalents: ['t', '7', 'Τ', 'τ', 'Т'], notes: 'Lowercase t, digit 7, Greek Tau, Cyrillic Te' },
                'U': { equivalents: ['u', 'υ', 'ս'], notes: 'Lowercase u, Greek Upsilon, Armenian U' },
                'V': { equivalents: ['v', 'ν'], notes: 'Lowercase v, Greek Nu' },
                'W': { equivalents: ['w'], notes: 'Lowercase w' },
                'X': { equivalents: ['x', 'Χ', 'χ', 'Х', 'х'], notes: 'Lowercase x, Greek Chi, Cyrillic Ha' },
                'Y': { equivalents: ['y', 'Υ', 'У', 'у', 'γ', 'Ύ'], notes: 'Lowercase y, Greek Upsilon, Cyrillic U, Greek Gamma' },
                'Z': { equivalents: ['z', '2', 'Ζ', 'ζ'], notes: 'Lowercase z, digit 2, Greek Zeta' }
            };
            
            var digitEquivalents = {
                '0': { equivalents: ['O', 'o', 'Ο', 'ο', 'О', 'о'], notes: 'Latin O, Greek Omicron, Cyrillic O' },
                '1': { equivalents: ['I', 'i', 'l', 'L', '|', 'Ι', 'ι'], notes: 'Latin I/l, Greek Iota, pipe' },
                '2': { equivalents: ['Z', 'z'], notes: 'Visual similarity to Z' },
                '3': { equivalents: ['E', 'e', 'Ε', 'ε'], notes: 'Reversed E appearance' },
                '4': { equivalents: ['A', 'a'], notes: 'Common leet substitution' },
                '5': { equivalents: ['S', 's', 'Ѕ', 'ѕ'], notes: 'Cyrillic Dze' },
                '6': { equivalents: ['b', 'G', 'g'], notes: 'Visual similarity' },
                '7': { equivalents: ['T', 't', 'Τ', 'τ'], notes: 'Common leet substitution' },
                '8': { equivalents: ['B', 'Β', 'β'], notes: 'Greek Beta' },
                '9': { equivalents: ['g', 'q'], notes: 'Visual similarity' }
            };
            
            function dedupeEquivalents(arr) {
                var seen = {};
                return arr.filter(function(item) {
                    if (seen[item]) return false;
                    seen[item] = true;
                    return true;
                });
            }
            
            function computeRiskInternal(equivalents) {
                if (equivalents.length === 0) return 'none';
                var hasDigits = equivalents.some(function(eq) { return /[0-9]/.test(eq); });
                var hasPunctuation = equivalents.some(function(eq) { return /[!@#$%^&*(),.?":{}|<>]/.test(eq); });
                var digitCount = equivalents.filter(function(eq) { return /[0-9]/.test(eq); }).length;
                
                if (digitCount >= 2 && hasPunctuation) return 'high';
                if (equivalents.length > 8) return 'high';
                if (hasDigits || equivalents.length > 5) return 'medium';
                return 'low';
            }
            
            'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.split('').forEach(function(char) {
                var data = letterEquivalents[char] || { equivalents: [], notes: '' };
                var dedupedEquivs = dedupeEquivalents(data.equivalents);
                library.push({
                    base: char,
                    type: 'letter',
                    equivalents: dedupedEquivs,
                    notes: data.notes,
                    enabled: dedupedEquivs.length > 0,
                    risk: computeRiskInternal(dedupedEquivs),
                    updatedAt: '28-01-2026',
                    updatedBy: 'admin@quicksms.co.uk'
                });
            });
            
            '0123456789'.split('').forEach(function(char) {
                var data = digitEquivalents[char] || { equivalents: [], notes: '' };
                var dedupedEquivs = dedupeEquivalents(data.equivalents);
                library.push({
                    base: char,
                    type: 'digit',
                    equivalents: dedupedEquivs,
                    notes: data.notes,
                    enabled: dedupedEquivs.length > 0,
                    risk: computeRiskInternal(dedupedEquivs),
                    updatedAt: '28-01-2026',
                    updatedBy: 'admin@quicksms.co.uk'
                });
            });
            
            return library;
        })();

        // Quarantine Queue mock data - using ruleTriggered as type for badge lookup
        mockData.quarantinedMessages = [
            { id: 'QM-001', accountId: 'ACC-10045', accountName: 'TechStart Ltd', subAccount: null, senderId: 'TechStart', recipient: '+447700900123', messageSnippet: 'Click here to claim your prize...', url: 'bit.ly/abc123', hasUrl: true, ruleTriggered: 'url', ruleId: 'URL-001', ruleName: 'URL shortener - bit.ly', status: 'pending', quarantinedAt: '03-02-2026 08:15', decisionAt: null, reviewedBy: null },
            { id: 'QM-002', accountId: 'ACC-10089', accountName: 'HealthFirst UK', subAccount: 'NHS Partnership', senderId: 'HealthFirst', recipient: '+447700900456', messageSnippet: 'Urgent action required for your account...', url: null, hasUrl: false, ruleTriggered: 'content', ruleId: 'CR-004', ruleName: 'Social engineering indicator', status: 'pending', quarantinedAt: '03-02-2026 09:30', decisionAt: null, reviewedBy: null },
            { id: 'QM-003', accountId: 'ACC-10112', accountName: 'E-Commerce Hub', subAccount: null, senderId: 'EComHub', recipient: '+447700900789', messageSnippet: 'Your package is ready at tinyurl.com/xyz...', url: 'tinyurl.com/xyz789', hasUrl: true, ruleTriggered: 'url', ruleId: 'URL-002', ruleName: 'URL shortener - tinyurl', status: 'pending', quarantinedAt: '03-02-2026 10:45', decisionAt: null, reviewedBy: null },
            { id: 'QM-004', accountId: 'ACC-10045', accountName: 'TechStart Ltd', subAccount: null, senderId: 'NHS-Partner', recipient: '+447700900234', messageSnippet: 'Your NHS appointment reminder...', url: null, hasUrl: false, ruleTriggered: 'senderid', ruleId: 'SID-004', ruleName: 'NHS - requires verification', status: 'pending', quarantinedAt: '03-02-2026 11:00', decisionAt: null, reviewedBy: null },
            { id: 'QM-005', accountId: 'ACC-10089', accountName: 'HealthFirst UK', subAccount: null, senderId: 'HealthUK', recipient: '+447700900567', messageSnippet: 'New domain detected: newsite.xyz/promo...', url: 'newsite.xyz/promo', hasUrl: true, ruleTriggered: 'url', ruleId: 'URL-003', ruleName: 'High-risk TLD .xyz', status: 'pending', quarantinedAt: '03-02-2026 11:30', decisionAt: null, reviewedBy: null },
            { id: 'QM-006', accountId: 'ACC-10112', accountName: 'E-Commerce Hub', subAccount: null, senderId: 'ShopNow', recipient: '+447700900890', messageSnippet: 'Verify your account at verify-account.com...', url: 'verify-account.com/login', hasUrl: true, ruleTriggered: 'url', ruleId: 'URL-005', ruleName: 'Phishing indicator', status: 'pending', quarantinedAt: '02-02-2026 16:20', decisionAt: null, reviewedBy: null },
            { id: 'QM-007', accountId: 'ACC-10045', accountName: 'TechStart Ltd', subAccount: null, senderId: 'Promo', recipient: '+447700900111', messageSnippet: 'Bitcoin investment opportunity...', url: null, hasUrl: false, ruleTriggered: 'content', ruleId: 'CR-001', ruleName: 'Crypto scam indicator', status: 'pending', quarantinedAt: '02-02-2026 14:45', decisionAt: null, reviewedBy: null },
            { id: 'QM-008', accountId: 'ACC-10089', accountName: 'HealthFirst UK', subAccount: 'NHS Partnership', senderId: 'Health', recipient: '+447700900222', messageSnippet: 'Guaranteed returns on your health plan...', url: null, hasUrl: false, ruleTriggered: 'content', ruleId: 'CR-002', ruleName: 'Financial fraud indicator', status: 'pending', quarantinedAt: '02-02-2026 12:30', decisionAt: null, reviewedBy: null },
            { id: 'QM-009', accountId: 'ACC-10112', accountName: 'E-Commerce Hub', subAccount: null, senderId: 'Shop', recipient: '+447700900333', messageSnippet: 'Login at login-secure.shop/auth...', url: 'login-secure.shop/auth', hasUrl: true, ruleTriggered: 'url', ruleId: 'URL-006', ruleName: 'Phishing indicator - login', status: 'pending', quarantinedAt: '02-02-2026 10:15', decisionAt: null, reviewedBy: null },
            { id: 'QM-010', accountId: 'ACC-10045', accountName: 'TechStart Ltd', subAccount: null, senderId: 'Alert', recipient: '+447700900444', messageSnippet: 'Appointment confirmed via bit.ly/appt...', url: 'bit.ly/appt456', hasUrl: true, ruleTriggered: 'url', ruleId: 'URL-001', ruleName: 'URL shortener - bit.ly', status: 'pending', quarantinedAt: '01-02-2026 17:00', decisionAt: null, reviewedBy: null },
            { id: 'QM-011', accountId: 'ACC-10089', accountName: 'HealthFirst UK', subAccount: null, senderId: 'HealthAlert', recipient: '+447700900555', messageSnippet: 'Domain age check: freshsite.tk/offer...', url: 'freshsite.tk/offer', hasUrl: true, ruleTriggered: 'domain_age', ruleId: 'DOMAIN-AGE', ruleName: 'Domain less than 72 hours old', status: 'pending', quarantinedAt: '01-02-2026 15:30', decisionAt: null, reviewedBy: null },
            { id: 'QM-012', accountId: 'ACC-10112', accountName: 'E-Commerce Hub', subAccount: null, senderId: 'Deals', recipient: '+447700900666', messageSnippet: 'Special offer at newdomain.xyz...', url: 'newdomain.xyz/special', hasUrl: true, ruleTriggered: 'url', ruleId: 'URL-003', ruleName: 'High-risk TLD .xyz', status: 'released', quarantinedAt: '01-02-2026 11:00', decisionAt: '03-02-2026 14:30', reviewedBy: 'admin@quicksms.co.uk' }
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
        console.log('[renderAllTabs] Starting...');
        try { renderSenderIdTab(); console.log('[renderAllTabs] SenderID done'); } catch(e) { console.error('[renderAllTabs] SenderID error:', e); }
        try { renderExemptionsTab(); console.log('[renderAllTabs] Exemptions done'); } catch(e) { console.error('[renderAllTabs] Exemptions error:', e); }
        try { renderContentTab(); console.log('[renderAllTabs] Content done'); } catch(e) { console.error('[renderAllTabs] Content error:', e); }
        try { renderContentExemptionsTab(); console.log('[renderAllTabs] ContentExemptions done'); } catch(e) { console.error('[renderAllTabs] ContentExemptions error:', e); }
        try { renderUrlTab(); console.log('[renderAllTabs] Url done'); } catch(e) { console.error('[renderAllTabs] Url error:', e); }
        try { renderUrlExemptionsTab(); console.log('[renderAllTabs] UrlExemptions done'); } catch(e) { console.error('[renderAllTabs] UrlExemptions error:', e); }
        try { renderNormTab(); console.log('[renderAllTabs] Norm done'); } catch(e) { console.error('[renderAllTabs] Norm error:', e); }
        try { renderQuarantineTab(); console.log('[renderAllTabs] Quarantine done'); } catch(e) { console.error('[renderAllTabs] Quarantine error:', e); }
        console.log('[renderAllTabs] Complete');
    }

    function renderSenderIdTab() {
        var tbody = document.getElementById('senderid-rules-body');
        var emptyState = document.getElementById('senderid-empty-state');
        var rules = mockData.senderIdRules;

        var categoryLabels = {
            'government_healthcare': 'Government and Healthcare',
            'banking_finance': 'Banking and Finance',
            'delivery_logistics': 'Delivery and logistics',
            'miscellaneous': 'Miscellaneous',
            'generic': 'Generic'
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
                '<td><div style="font-weight: 600; font-size: 0.85rem; color: #1e3a5f;">' + rule.baseSenderId + '</div><small class="text-muted" style="font-size: 0.7rem;">' + rule.id + '</small></td>' +
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

    function renderExemptionsTab() {
        var tbody = document.getElementById('exemptions-body');
        var emptyState = document.getElementById('exemptions-empty-state');
        var exemptions = mockData.senderIdExemptions || [];

        var categoryLabels = {
            'government_healthcare': 'Government and Healthcare',
            'banking_finance': 'Banking and Finance',
            'delivery_logistics': 'Delivery and logistics',
            'miscellaneous': 'Miscellaneous',
            'generic': 'Generic'
        };
        
        var typeLabels = {
            'alphanumeric': 'Alphanumeric',
            'numeric': 'Numeric',
            'shortcode': 'Shortcode'
        };
        
        var scopeLabels = {
            'global': 'Global',
            'account': 'Account',
            'subaccount': 'Sub-account'
        };

        var typeFilter = document.getElementById('exemptions-filter-type') ? document.getElementById('exemptions-filter-type').value : '';
        var categoryFilter = document.getElementById('exemptions-filter-category') ? document.getElementById('exemptions-filter-category').value : '';
        var scopeFilter = document.getElementById('exemptions-filter-scope') ? document.getElementById('exemptions-filter-scope').value : '';
        var sourceFilter = document.getElementById('exemptions-filter-source') ? document.getElementById('exemptions-filter-source').value : '';
        var statusFilter = document.getElementById('exemptions-filter-status') ? document.getElementById('exemptions-filter-status').value : '';
        var searchTerm = document.getElementById('exemptions-search') ? document.getElementById('exemptions-search').value.toLowerCase() : '';

        var filteredExemptions = exemptions.filter(function(ex) {
            if (typeFilter && ex.type !== typeFilter) return false;
            if (categoryFilter && ex.category !== categoryFilter) return false;
            if (scopeFilter && ex.scope !== scopeFilter) return false;
            if (sourceFilter && ex.source !== sourceFilter) return false;
            if (statusFilter && ex.enforcementStatus !== statusFilter) return false;
            if (searchTerm && ex.senderId.toLowerCase().indexOf(searchTerm) === -1 && 
                ex.accountName.toLowerCase().indexOf(searchTerm) === -1) return false;
            return true;
        });

        if (filteredExemptions.length === 0) {
            tbody.innerHTML = '';
            emptyState.style.display = 'block';
            document.getElementById('exemptions-table').style.display = 'none';
            return;
        }

        emptyState.style.display = 'none';
        document.getElementById('exemptions-table').style.display = 'table';
        
        tbody.innerHTML = filteredExemptions.map(function(ex) {
            var statusBadge = ex.enforcementStatus === 'active'
                ? '<span class="badge" style="background: #198754; font-size: 0.65rem;">Active</span>'
                : '<span class="badge" style="background: #dc3545; font-size: 0.65rem;">Disabled</span>';
            
            var sourceBadge = ex.source === 'approval'
                ? '<span class="badge" style="background: #198754; font-size: 0.65rem;">Approved</span>'
                : '<span class="badge" style="background: #1e3a5f; font-size: 0.65rem;">Manual</span>';
            
            var scopeBadge;
            if (ex.scope === 'global') {
                scopeBadge = '<span class="badge" style="background: #17a2b8; font-size: 0.65rem;">Global</span>';
            } else if (ex.scope === 'subaccount') {
                scopeBadge = '<span class="badge" style="background: #6f42c1; font-size: 0.65rem;">Sub-account</span>';
            } else {
                scopeBadge = '<span class="badge" style="background: #6c757d; font-size: 0.65rem;">Account</span>';
            }
            
            var accountDisplay = ex.scope === 'global' ? '<span class="text-muted">-</span>' : ex.accountName;
            
            var subAccountDisplay = '<span class="text-muted">-</span>';
            if (ex.subAccounts && ex.subAccounts.length > 0) {
                if (ex.subAccounts.length === 1) {
                    subAccountDisplay = ex.subAccounts[0];
                } else {
                    subAccountDisplay = ex.subAccounts[0] + ' <span class="badge bg-secondary" style="font-size: 0.6rem;">+' + (ex.subAccounts.length - 1) + '</span>';
                }
            }
            
            var updatedDisplay = ex.updatedAt ? ex.updatedAt.split(' ')[0] : '-';
            
            var isSuperAdmin = currentAdmin.role === 'super_admin';
            
            return '<tr data-exemption-id="' + ex.id + '" data-source-id="' + ex.sourceId + '">' +
                '<td><div style="font-weight: 600; font-size: 0.85rem; color: #1e3a5f;">' + ex.senderId + '</div><small class="text-muted" style="font-size: 0.7rem;">' + ex.id + '</small></td>' +
                '<td style="font-size: 0.75rem;">' + (typeLabels[ex.type] || ex.type) + '</td>' +
                '<td style="font-size: 0.75rem;">' + (categoryLabels[ex.category] || ex.category) + '</td>' +
                '<td>' + scopeBadge + '</td>' +
                '<td style="font-size: 0.75rem;">' + accountDisplay + '</td>' +
                '<td style="font-size: 0.75rem;">' + subAccountDisplay + '</td>' +
                '<td>' + sourceBadge + '</td>' +
                '<td>' + statusBadge + '</td>' +
                '<td style="font-size: 0.75rem;">' + updatedDisplay + '</td>' +
                '<td>' +
                    '<div class="dropdown">' +
                        '<button class="action-menu-btn" data-bs-toggle="dropdown" aria-expanded="false"><i class="fas fa-ellipsis-v"></i></button>' +
                        '<ul class="dropdown-menu dropdown-menu-end">' +
                            '<li><a class="dropdown-item" href="javascript:void(0)" onclick="viewExemption(\'' + ex.id + '\')"><i class="fas fa-eye me-2 text-muted"></i>View Details</a></li>' +
                            (ex.source === 'approval' ? '<li><a class="dropdown-item" href="/admin/assets/sender-ids?id=' + ex.sourceId + '"><i class="fas fa-external-link-alt me-2 text-muted"></i>View Approval</a></li>' : '') +
                            (ex.source === 'manual' ? '<li><a class="dropdown-item" href="javascript:void(0)" onclick="editExemption(\'' + ex.id + '\')"><i class="fas fa-edit me-2 text-muted"></i>Edit</a></li>' : '') +
                            '<li><hr class="dropdown-divider"></li>' +
                            (ex.enforcementStatus === 'active' 
                                ? '<li><a class="dropdown-item text-warning" href="javascript:void(0)" onclick="disableExemptionEnforcement(\'' + ex.sourceId + '\')"><i class="fas fa-pause-circle me-2"></i>Disable Enforcement</a></li>'
                                : '<li><a class="dropdown-item text-success" href="javascript:void(0)" onclick="enableExemptionEnforcement(\'' + ex.sourceId + '\')"><i class="fas fa-play-circle me-2"></i>Enable Enforcement</a></li>') +
                            (isSuperAdmin && ex.source === 'manual' ? '<li><hr class="dropdown-divider"></li><li><a class="dropdown-item text-danger" href="javascript:void(0)" onclick="deleteExemption(\'' + ex.id + '\')"><i class="fas fa-trash me-2"></i>Delete</a></li>' : '') +
                        '</ul>' +
                    '</div>' +
                '</td>' +
                '</tr>';
        }).join('');
        
        updateExemptionsFilterChips();
    }

    function toggleExemptionsFilterPanel() {
        var panel = document.getElementById('exemptions-filter-panel');
        var btn = document.getElementById('exemptions-filter-btn');
        
        if (!panel || !btn) return;
        
        if (panel.style.display === 'none' || panel.style.display === '') {
            panel.style.display = 'block';
            btn.classList.add('active');
        } else {
            panel.style.display = 'none';
            btn.classList.remove('active');
        }
    }

    function applyExemptionsFilters() {
        var filterCount = 0;
        var type = document.getElementById('exemptions-filter-type').value;
        var category = document.getElementById('exemptions-filter-category').value;
        var scope = document.getElementById('exemptions-filter-scope').value;
        var source = document.getElementById('exemptions-filter-source').value;
        var status = document.getElementById('exemptions-filter-status').value;
        
        if (type) filterCount++;
        if (category) filterCount++;
        if (scope) filterCount++;
        if (source) filterCount++;
        if (status) filterCount++;
        
        var badge = document.getElementById('exemptions-filter-count');
        if (filterCount > 0) {
            badge.textContent = filterCount;
            badge.style.display = 'inline-block';
        } else {
            badge.style.display = 'none';
        }
        
        renderExemptionsTab();
        toggleExemptionsFilterPanel();
    }

    function resetExemptionsFilters() {
        document.getElementById('exemptions-filter-type').value = '';
        document.getElementById('exemptions-filter-category').value = '';
        document.getElementById('exemptions-filter-scope').value = '';
        document.getElementById('exemptions-filter-source').value = '';
        document.getElementById('exemptions-filter-status').value = '';
        document.getElementById('exemptions-search').value = '';
        document.getElementById('exemptions-filter-count').style.display = 'none';
        renderExemptionsTab();
    }

    function filterExemptionsTable() {
        renderExemptionsTab();
    }
    
    function updateExemptionsFilterChips() {
        var chipsRow = document.getElementById('exemptions-chips-row');
        var chipsContainer = document.getElementById('exemptions-chips-container');
        if (!chipsRow || !chipsContainer) return;
        
        var chips = [];
        var searchTerm = document.getElementById('exemptions-search').value.trim();
        var typeFilter = document.getElementById('exemptions-filter-type').value;
        var categoryFilter = document.getElementById('exemptions-filter-category').value;
        var scopeFilter = document.getElementById('exemptions-filter-scope').value;
        var sourceFilter = document.getElementById('exemptions-filter-source').value;
        var statusFilter = document.getElementById('exemptions-filter-status').value;
        
        var typeLabels = { 'alphanumeric': 'Alphanumeric', 'numeric': 'Numeric', 'shortcode': 'Shortcode' };
        var categoryLabels = { 'government_healthcare': 'Govt & Healthcare', 'banking_finance': 'Banking & Finance', 'delivery_logistics': 'Delivery & Logistics', 'miscellaneous': 'Miscellaneous', 'generic': 'Generic' };
        var scopeLabels = { 'global': 'Global', 'account': 'Account', 'subaccount': 'Sub-account' };
        var sourceLabels = { 'approval': 'Approved via Approvals', 'manual': 'Added manually' };
        var statusLabels = { 'active': 'Active', 'disabled': 'Disabled' };
        
        if (searchTerm) {
            chips.push({ label: 'Search', value: searchTerm.substring(0, 15) + (searchTerm.length > 15 ? '...' : ''), filterKey: 'search' });
        }
        if (typeFilter) chips.push({ label: 'Type', value: typeLabels[typeFilter] || typeFilter, filterKey: 'type' });
        if (categoryFilter) chips.push({ label: 'Category', value: categoryLabels[categoryFilter] || categoryFilter, filterKey: 'category' });
        if (scopeFilter) chips.push({ label: 'Scope', value: scopeLabels[scopeFilter] || scopeFilter, filterKey: 'scope' });
        if (sourceFilter) chips.push({ label: 'Source', value: sourceLabels[sourceFilter] || sourceFilter, filterKey: 'source' });
        if (statusFilter) chips.push({ label: 'Status', value: statusLabels[statusFilter] || statusFilter, filterKey: 'status' });
        
        if (chips.length === 0) {
            chipsRow.style.display = 'none';
            return;
        }
        
        chipsRow.style.display = 'block';
        var chipsHtml = chips.map(function(chip) {
            return '<span class="filter-chip" style="display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.25rem 0.5rem; background: rgba(30, 58, 95, 0.08); border: 1px solid rgba(30, 58, 95, 0.15); border-radius: 4px; font-size: 0.7rem;">' +
                '<span style="color: #1e3a5f; font-weight: 500;">' + chip.label + ':</span> ' +
                '<span style="color: #495057;">' + chip.value + '</span>' +
                '<span class="remove-chip" onclick="removeExemptionsFilter(\'' + chip.filterKey + '\')" style="cursor: pointer; margin-left: 0.25rem; color: #6c757d;">' +
                    '<i class="fas fa-times" style="font-size: 0.6rem;"></i>' +
                '</span>' +
            '</span>';
        }).join('');
        
        chipsHtml += '<span class="clear-all-filters" onclick="resetExemptionsFilters()" style="cursor: pointer; font-size: 0.7rem; color: #1e3a5f; margin-left: 0.5rem; text-decoration: underline;">Clear all</span>';
        
        chipsContainer.innerHTML = chipsHtml;
    }
    
    function removeExemptionsFilter(filterKey) {
        if (filterKey === 'search') {
            document.getElementById('exemptions-search').value = '';
        } else if (filterKey === 'type') {
            document.getElementById('exemptions-filter-type').value = '';
        } else if (filterKey === 'category') {
            document.getElementById('exemptions-filter-category').value = '';
        } else if (filterKey === 'scope') {
            document.getElementById('exemptions-filter-scope').value = '';
        } else if (filterKey === 'source') {
            document.getElementById('exemptions-filter-source').value = '';
        } else if (filterKey === 'status') {
            document.getElementById('exemptions-filter-status').value = '';
        }
        applyExemptionsFilters();
    }

    function viewExemption(exemptionId) {
        var exemptions = mockData.senderIdExemptions || [];
        var ex = exemptions.find(function(e) { return e.id === exemptionId; });
        if (!ex) return;
        
        var categoryLabels = {
            'government_healthcare': 'Government and Healthcare',
            'banking_finance': 'Banking and Finance',
            'delivery_logistics': 'Delivery and logistics',
            'miscellaneous': 'Miscellaneous',
            'generic': 'Generic'
        };
        
        var enforcementStatusBadge = ex.enforcementStatus === 'active'
            ? '<span class="badge bg-success"><i class="fas fa-shield-alt me-1"></i>Enforced</span>'
            : '<span class="badge bg-danger"><i class="fas fa-pause-circle me-1"></i>Disabled</span>';
        
        var sourceBadge = ex.source === 'approval'
            ? '<span class="badge bg-success">SenderID Approvals</span>'
            : '<span class="badge" style="background: #1e3a5f;">Manual Exemption</span>';
        
        var override = mockData.enforcementOverrides[ex.sourceId];
        var overrideInfo = override 
            ? '<tr><td class="text-muted">Override Reason</td><td class="text-danger">' + override.reason + '</td></tr>' +
              '<tr><td class="text-muted">Override Set By</td><td>' + override.setBy + '</td></tr>' +
              '<tr><td class="text-muted">Override Set At</td><td>' + override.setAt + '</td></tr>'
            : '';
        
        var html = '<div class="mb-3"><strong style="color: #1e3a5f;">Exemption Details</strong></div>' +
            '<table class="table table-sm">' +
            '<tr><td class="text-muted" style="width: 40%;">Source Record</td><td>' + ex.sourceId + '</td></tr>' +
            '<tr><td class="text-muted">SenderID</td><td><div style="font-weight: 600; font-size: 0.9rem; color: #1e3a5f;">' + ex.senderId + '</div><small class="text-muted">' + ex.id + '</small></td></tr>' +
            '<tr><td class="text-muted">Normalised Value</td><td><code>' + ex.normalisedValue + '</code></td></tr>' +
            '<tr><td class="text-muted">Account</td><td>' + (ex.accountId === 'global' ? '<span class="badge bg-info"><i class="fas fa-globe me-1"></i>Global (All Accounts)</span>' : ex.accountName + ' (' + ex.accountId + ')') + '</td></tr>' +
            '<tr><td class="text-muted">Source</td><td>' + sourceBadge + (ex.source === 'approval' ? ' <a href="/admin/assets/sender-ids?id=' + ex.sourceId + '" class="ms-2"><i class="fas fa-external-link-alt"></i> View Approval</a>' : '') + '</td></tr>' +
            '<tr><td class="text-muted">Category</td><td>' + (categoryLabels[ex.category] || ex.category) + '</td></tr>' +
            '<tr><td class="text-muted">Enforcement Status</td><td>' + enforcementStatusBadge + '</td></tr>' +
            overrideInfo +
            '<tr><td class="text-muted">Approved By</td><td>' + (ex.approvedBy || '-') + '</td></tr>' +
            '<tr><td class="text-muted">Approved Date</td><td>' + (ex.approvedAt || '-') + '</td></tr>' +
            '<tr><td class="text-muted">Expiry</td><td>' + (ex.expiry || 'No expiry') + '</td></tr>' +
            '<tr><td class="text-muted">Notes</td><td>' + (ex.notes || '-') + '</td></tr>' +
            '</table>' +
            '<div class="mt-3 pt-3 border-top">' +
            '<small class="text-muted"><i class="fas fa-info-circle me-1"></i>Disabling enforcement will immediately block this SenderID without changing the approval record.</small>' +
            '</div>';
        
        document.getElementById('generic-view-content').innerHTML = html;
        document.getElementById('generic-view-title').textContent = 'View Exemption: ' + ex.senderId;
        var modal = new bootstrap.Modal(document.getElementById('genericViewModal'));
        modal.show();
    }

    function editExemption(exemptionId) {
        var exemptions = mockData.senderIdExemptions || [];
        var ex = exemptions.find(function(e) { return e.id === exemptionId; });
        if (!ex || ex.source !== 'manual') {
            showToast('Only manual exemptions can be edited', 'warning');
            return;
        }
        
        var manualEx = mockData.manualExemptions.find(function(m) { return m.id === ex.sourceId; });
        if (!manualEx) {
            showToast('Exemption data not found', 'error');
            return;
        }
        
        document.getElementById('exemption-modal-title').textContent = 'Edit Exemption';
        document.getElementById('exemption-save-btn-text').textContent = 'Update Exemption';
        document.getElementById('exemption-edit-id').value = manualEx.id;
        
        document.getElementById('exemption-sender-id').value = manualEx.senderId;
        document.getElementById('exemption-type-' + manualEx.type).checked = true;
        document.getElementById('exemption-category').value = manualEx.category;
        document.getElementById('exemption-scope').value = manualEx.scope;
        document.getElementById('exemption-notes').value = manualEx.notes || '';
        
        updateExemptionAccountVisibility();
        
        if (manualEx.accountId && manualEx.accountId !== 'global') {
            document.getElementById('exemption-account').value = manualEx.accountId;
        }
        
        var modal = new bootstrap.Modal(document.getElementById('addExemptionModal'));
        modal.show();
        
        logAuditEvent('EXEMPTION_EDIT_STARTED', { exemptionId: exemptionId, sourceId: ex.sourceId });
    }

    function disableExemptionEnforcement(sourceId) {
        var exemptions = mockData.senderIdExemptions || [];
        var ex = exemptions.find(function(e) { return e.sourceId === sourceId; });
        if (!ex) return;
        
        showActionConfirmation({
            id: sourceId,
            type: 'exemption',
            action: 'disable_enforcement',
            title: 'Disable Enforcement',
            icon: 'fa-pause-circle',
            iconColor: 'text-warning',
            message: 'Disabling enforcement will immediately block this SenderID.',
            details: '<table class="table table-sm" style="font-size: 0.85rem;">' +
                '<tr><td class="text-muted" style="width: 35%;">SenderID</td><td><code>' + escapeHtml(ex.senderId) + '</code></td></tr>' +
                '<tr><td class="text-muted">Account</td><td>' + (ex.accountId === 'global' ? 'All Accounts' : escapeHtml(ex.accountName || ex.accountId)) + '</td></tr>' +
                '</table>' +
                '<div class="alert alert-warning" style="font-size: 0.8rem; padding: 0.5rem;"><i class="fas fa-exclamation-triangle me-1"></i>Messages using this SenderID will be blocked until enforcement is re-enabled.</div>',
            btnText: 'Disable Enforcement',
            btnClass: 'btn-warning',
            showReason: true
        });
    }
    
    function enableExemptionEnforcement(sourceId) {
        var exemptions = mockData.senderIdExemptions || [];
        var ex = exemptions.find(function(e) { return e.sourceId === sourceId; });
        if (!ex) return;
        
        showActionConfirmation({
            id: sourceId,
            type: 'exemption',
            action: 'enable_enforcement',
            title: 'Enable Enforcement',
            icon: 'fa-play-circle',
            iconColor: 'text-success',
            message: 'Re-enabling enforcement will allow this SenderID to bypass blocking rules.',
            details: '<table class="table table-sm" style="font-size: 0.85rem;">' +
                '<tr><td class="text-muted" style="width: 35%;">SenderID</td><td><code>' + escapeHtml(ex.senderId) + '</code></td></tr>' +
                '<tr><td class="text-muted">Account</td><td>' + (ex.accountId === 'global' ? 'All Accounts' : escapeHtml(ex.accountName || ex.accountId)) + '</td></tr>' +
                '</table>',
            btnText: 'Enable Enforcement',
            btnClass: 'btn-success',
            showReason: false
        });
    }
    
    function executeDisableExemptionEnforcement(sourceId, reason) {
        setEnforcementOverride(sourceId, 'disabled', reason || 'No reason provided');
        renderExemptionsTab();
        showSuccessToast('Enforcement disabled - SenderID will now be blocked');
    }
    
    function executeEnableExemptionEnforcement(sourceId) {
        delete mockData.enforcementOverrides[sourceId];
        rebuildExemptions();
        
        logAuditEvent('EXEMPTION_ENFORCEMENT_ENABLED', { sourceId: sourceId });
        renderExemptionsTab();
        showSuccessToast('Enforcement enabled - SenderID is now allowed');
    }
    
    function revokeExemption(exemptionId) {
        var exemptions = mockData.senderIdExemptions || [];
        var ex = exemptions.find(function(e) { return e.id === exemptionId; });
        if (!ex) return;
        
        disableExemptionEnforcement(ex.sourceId);
    }

    function deleteExemption(exemptionId) {
        var exemptions = mockData.senderIdExemptions || [];
        var ex = exemptions.find(function(e) { return e.id === exemptionId; });
        if (!ex || ex.source !== 'manual') {
            showToast('Only manual exemptions can be deleted', 'error');
            return;
        }
        
        showActionConfirmation({
            id: exemptionId,
            type: 'exemption',
            action: 'delete',
            title: 'Delete Exemption',
            icon: 'fa-trash',
            iconColor: 'text-danger',
            message: 'Are you sure you want to permanently delete this manual exemption?',
            details: '<table class="table table-sm" style="font-size: 0.85rem;">' +
                '<tr><td class="text-muted" style="width: 35%;">SenderID</td><td><code>' + escapeHtml(ex.senderId) + '</code></td></tr>' +
                '<tr><td class="text-muted">Account</td><td>' + (ex.accountId === 'global' ? 'All Accounts' : escapeHtml(ex.accountName || ex.accountId)) + '</td></tr>' +
                '</table>' +
                '<div class="alert alert-danger" style="font-size: 0.8rem; padding: 0.5rem;"><i class="fas fa-exclamation-triangle me-1"></i>This action cannot be undone.</div>',
            btnText: 'Delete Exemption',
            btnClass: 'btn-danger',
            showReason: false
        });
    }
    
    function executeDeleteExemption(exemptionId) {
        var exemptions = mockData.senderIdExemptions || [];
        var ex = exemptions.find(function(e) { return e.id === exemptionId; });
        if (!ex) return;
        
        var manIndex = mockData.manualExemptions.findIndex(function(m) { return m.id === ex.sourceId; });
        if (manIndex !== -1) {
            var deleted = mockData.manualExemptions.splice(manIndex, 1)[0];
            rebuildExemptions();
            logAuditEvent('EXEMPTION_DELETED', { exemptionId: exemptionId, sourceId: ex.sourceId, senderId: deleted.senderId });
            renderExemptionsTab();
            showSuccessToast('Manual exemption deleted successfully');
        }
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
                        '<button class="action-menu-btn" onclick="toggleContentActionMenu(this, \'' + rule.id + '\', event)"><i class="fas fa-ellipsis-v"></i></button>' +
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
    
    function toggleContentActionMenu(btn, ruleId, event) {
        if (event) {
            event.stopPropagation();
            event.preventDefault();
        }
        document.querySelectorAll('.action-menu-dropdown').forEach(function(menu) {
            if (menu.id !== 'content-menu-' + ruleId) {
                menu.classList.remove('show');
            }
        });
        var menu = document.getElementById('content-menu-' + ruleId);
        if (menu) {
            menu.classList.toggle('show');
        }
    }
    
    function showAddContentRuleModal() {
        document.getElementById('content-rule-modal-title').textContent = 'Add Content Rule';
        document.getElementById('content-rule-form').reset();
        document.getElementById('content-rule-id').value = '';
        document.getElementById('content-match-type').value = 'keyword';
        document.getElementById('content-apply-normalisation').checked = true;
        updateContentMatchInputLabel();
        clearContentRuleErrors();
        // Hide validation alert
        var alertDiv = document.getElementById('content-rule-validation-alert');
        if (alertDiv) {
            alertDiv.style.cssText = 'display: none !important;';
        }
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
        var alertDiv = document.getElementById('content-rule-validation-alert');
        
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
        
        // Show/hide validation alert
        if (!isValid && alertDiv) {
            alertDiv.style.display = 'flex';
            alertDiv.style.cssText = 'display: flex !important;';
        } else if (alertDiv) {
            alertDiv.style.display = 'none';
            alertDiv.style.cssText = 'display: none !important;';
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
        
        // Populate confirmation modal with rule details
        var ruleId = document.getElementById('content-rule-id').value;
        var name = document.getElementById('content-rule-name').value.trim();
        var matchType = document.getElementById('content-match-type').value;
        var matchValue = document.getElementById('content-match-value').value.trim();
        var ruleType = document.getElementById('content-rule-type').value;
        var applyNorm = document.getElementById('content-apply-normalisation').checked;
        
        document.getElementById('confirm-rule-name').textContent = name;
        document.getElementById('confirm-rule-matchtype').innerHTML = matchType === 'keyword' 
            ? '<span class="badge bg-primary">Keyword</span>' 
            : '<span class="badge bg-info">Regex</span>';
        document.getElementById('confirm-rule-type').innerHTML = ruleType === 'block' 
            ? '<span class="badge bg-danger"><i class="fas fa-ban me-1"></i>Block</span>' 
            : '<span class="badge bg-warning text-dark"><i class="fas fa-flag me-1"></i>Flag</span>';
        document.getElementById('confirm-rule-norm').innerHTML = applyNorm 
            ? '<span class="text-success"><i class="fas fa-check-circle me-1"></i>Enabled</span>' 
            : '<span class="text-muted"><i class="fas fa-times-circle me-1"></i>Disabled</span>';
        document.getElementById('confirm-rule-status').innerHTML = '<span class="badge bg-success"><i class="fas fa-check me-1"></i>Enabled</span>';
        
        // Truncate match value for display if too long
        var displayValue = matchValue.length > 100 ? matchValue.substring(0, 100) + '...' : matchValue;
        document.getElementById('confirm-rule-value').textContent = displayValue;
        
        // Hide the add/edit modal and show confirmation modal
        bootstrap.Modal.getInstance(document.getElementById('contentRuleModal')).hide();
        var confirmModal = new bootstrap.Modal(document.getElementById('contentRuleConfirmModal'));
        confirmModal.show();
    }
    
    function confirmSaveContentRule() {
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
        var isNewRule = !ruleId;
        
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
        
        // Hide confirmation modal
        bootstrap.Modal.getInstance(document.getElementById('contentRuleConfirmModal')).hide();
        
        // Refresh Rules table immediately
        renderContentTab();
        
        // Show success toast
        showContentRuleToast(isNewRule ? 'Rule added successfully.' : 'Rule updated successfully.');
    }
    
    function showContentRuleToast(message) {
        // Create toast container if it doesn't exist
        var toastContainer = document.getElementById('content-toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'content-toast-container';
            toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
            toastContainer.style.zIndex = '1100';
            document.body.appendChild(toastContainer);
        }
        
        // Create toast element
        var toastId = 'toast-' + Date.now();
        var toastHtml = '<div id="' + toastId + '" class="toast align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true" style="background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);">' +
            '<div class="d-flex">' +
                '<div class="toast-body">' +
                    '<i class="fas fa-check-circle me-2"></i>' + message +
                '</div>' +
                '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>' +
            '</div>' +
        '</div>';
        
        toastContainer.insertAdjacentHTML('beforeend', toastHtml);
        
        var toastEl = document.getElementById(toastId);
        var toast = new bootstrap.Toast(toastEl, { delay: 4000 });
        toast.show();
        
        // Remove toast element after it's hidden
        toastEl.addEventListener('hidden.bs.toast', function() {
            toastEl.remove();
        });
    }
    
    function testContentRule() {
        var testInput = document.getElementById('content-rule-test-input').value.trim();
        var matchType = document.getElementById('content-match-type').value;
        var matchValue = document.getElementById('content-match-value').value.trim();
        var applyNorm = document.getElementById('content-apply-normalisation').checked;
        var ruleType = document.getElementById('content-rule-type').value;
        var resultDiv = document.getElementById('content-rule-test-result');
        
        if (!testInput) {
            resultDiv.innerHTML = '<div class="alert alert-warning mb-0 py-2" style="font-size: 0.8rem;"><i class="fas fa-info-circle me-1"></i> Please enter sample text to test.</div>';
            resultDiv.style.display = 'block';
            return;
        }
        
        if (!matchValue) {
            resultDiv.innerHTML = '<div class="alert alert-warning mb-0 py-2" style="font-size: 0.8rem;"><i class="fas fa-info-circle me-1"></i> Please enter keywords or regex pattern first.</div>';
            resultDiv.style.display = 'block';
            return;
        }
        
        // Use MessageEnforcementService normalisation if available
        var normalisedContent = testInput;
        if (applyNorm && typeof window.MessageEnforcementService !== 'undefined' && 
            typeof window.MessageEnforcementService.normaliseText === 'function') {
            normalisedContent = window.MessageEnforcementService.normaliseText(testInput);
        } else if (applyNorm) {
            normalisedContent = testInput.toLowerCase();
        }
        
        var matched = false;
        var matchedKeyword = '';
        var matchStart = -1;
        var matchEnd = -1;
        
        if (matchType === 'keyword') {
            var keywords = matchValue.split(',').map(function(k) { return k.trim(); });
            var searchContent = applyNorm ? normalisedContent.toLowerCase() : testInput;
            for (var i = 0; i < keywords.length; i++) {
                var kw = applyNorm ? keywords[i].toLowerCase() : keywords[i];
                var idx = searchContent.indexOf(kw);
                if (idx !== -1) {
                    matched = true;
                    matchedKeyword = keywords[i];
                    matchStart = idx;
                    matchEnd = idx + kw.length;
                    break;
                }
            }
        } else {
            try {
                var flags = applyNorm ? 'gi' : 'g';
                var regex = new RegExp(matchValue, flags);
                var searchContent = applyNorm ? normalisedContent : testInput;
                var regexMatch = regex.exec(searchContent);
                if (regexMatch) {
                    matched = true;
                    matchedKeyword = regexMatch[0];
                    matchStart = regexMatch.index;
                    matchEnd = regexMatch.index + regexMatch[0].length;
                }
            } catch (e) {
                resultDiv.innerHTML = '<div class="alert alert-danger mb-0 py-2" style="font-size: 0.8rem;"><i class="fas fa-times-circle me-1"></i> Invalid regex pattern: ' + e.message + '</div>';
                resultDiv.style.display = 'block';
                return;
            }
        }
        
        // Build detailed result output
        var html = '';
        if (matched) {
            var actionLabel = ruleType === 'block' 
                ? '<span class="badge bg-danger"><i class="fas fa-ban me-1"></i>Would be rejected</span>'
                : '<span class="badge bg-warning text-dark"><i class="fas fa-flag me-1"></i>Would be sent to Quarantine</span>';
            
            // Highlight matched substring in original text
            var highlightedText = escapeHtml(testInput);
            if (matchStart !== -1 && matchEnd !== -1) {
                var before = escapeHtml(testInput.substring(0, matchStart));
                var matchStr = escapeHtml(testInput.substring(matchStart, matchEnd));
                var after = escapeHtml(testInput.substring(matchEnd));
                highlightedText = before + '<mark style="background: #ffc107; padding: 0 2px; border-radius: 2px;">' + matchStr + '</mark>' + after;
            }
            
            html = '<div class="p-3" style="background: #d4edda; border: 1px solid #c3e6cb; border-radius: 6px;">' +
                '<div class="d-flex align-items-center justify-content-between mb-2">' +
                    '<span style="font-weight: 600; color: #155724;"><i class="fas fa-check-circle me-2"></i>MATCHED</span>' +
                    actionLabel +
                '</div>' +
                '<div class="mb-2" style="font-size: 0.8rem;">' +
                    '<strong>Matched:</strong> "<span style="background: #fff; padding: 2px 6px; border-radius: 3px;">' + escapeHtml(matchedKeyword) + '</span>"' +
                '</div>' +
                '<div class="mb-2" style="font-size: 0.8rem;">' +
                    '<strong>Original text with match:</strong><br>' +
                    '<div style="background: #fff; padding: 0.5rem; border-radius: 4px; margin-top: 0.25rem; font-family: monospace; word-break: break-all;">' + highlightedText + '</div>' +
                '</div>';
            
            if (applyNorm && normalisedContent !== testInput) {
                html += '<div style="font-size: 0.75rem; color: #155724;">' +
                    '<i class="fas fa-info-circle me-1"></i><strong>Normalised version used:</strong> ' +
                    '<code style="background: #fff; padding: 2px 4px; border-radius: 2px;">' + escapeHtml(normalisedContent.substring(0, 100)) + (normalisedContent.length > 100 ? '...' : '') + '</code>' +
                '</div>';
            }
            html += '</div>';
        } else {
            html = '<div class="p-3" style="background: #e2e3e5; border: 1px solid #d6d8db; border-radius: 6px;">' +
                '<div class="d-flex align-items-center mb-2">' +
                    '<span style="font-weight: 600; color: #383d41;"><i class="fas fa-times-circle me-2"></i>NO MATCH</span>' +
                '</div>' +
                '<div style="font-size: 0.8rem; color: #383d41;">' +
                    'The test message does not match the configured ' + (matchType === 'keyword' ? 'keywords' : 'regex pattern') + '.' +
                '</div>';
            
            if (applyNorm && normalisedContent !== testInput) {
                html += '<div class="mt-2" style="font-size: 0.75rem; color: #6c757d;">' +
                    '<i class="fas fa-info-circle me-1"></i><strong>Normalised version tested:</strong> ' +
                    '<code style="background: #fff; padding: 2px 4px; border-radius: 2px;">' + escapeHtml(normalisedContent.substring(0, 100)) + (normalisedContent.length > 100 ? '...' : '') + '</code>' +
                '</div>';
            }
            html += '</div>';
        }
        
        resultDiv.innerHTML = html;
        resultDiv.style.display = 'block';
    }
    
    function showContentRuleSuccessModal(action, ruleName, matchType, ruleType) {
        var modal = document.getElementById('contentRuleSuccessModal');
        if (!modal) return;
        
        document.getElementById('success-rule-action').textContent = action;
        document.getElementById('success-rule-name').textContent = ruleName;
        document.getElementById('success-rule-matchtype').textContent = matchType === 'keyword' ? 'Keyword Match' : 'Regex Pattern';
        document.getElementById('success-rule-type').textContent = ruleType === 'block' ? 'Block' : 'Flag for Review';
        
        var successModal = new bootstrap.Modal(modal);
        successModal.show();
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
        hideContentFilterPanel();
        renderContentTab();
    }
    
    function toggleContentFilterPanel() {
        var panel = document.getElementById('content-filter-panel');
        var btn = document.getElementById('content-filter-btn');
        if (panel.style.display === 'none') {
            panel.style.display = 'block';
            btn.classList.add('active');
        } else {
            panel.style.display = 'none';
            btn.classList.remove('active');
        }
    }
    
    function hideContentFilterPanel() {
        var panel = document.getElementById('content-filter-panel');
        var btn = document.getElementById('content-filter-btn');
        if (panel) panel.style.display = 'none';
        if (btn) btn.classList.remove('active');
    }
    
    function applyContentFilters() {
        updateContentFilterCount();
        hideContentFilterPanel();
        renderContentTab();
    }
    
    function updateContentFilterCount() {
        var count = 0;
        if (document.getElementById('content-filter-status').value) count++;
        if (document.getElementById('content-filter-matchtype').value) count++;
        if (document.getElementById('content-filter-ruletype').value) count++;
        
        var badge = document.getElementById('content-filter-count');
        if (count > 0) {
            badge.textContent = count;
            badge.style.display = 'inline-block';
        } else {
            badge.style.display = 'none';
        }
    }
    
    // ========================
    // Content Exemptions Functions
    // ========================
    
    function renderContentExemptionsTab() {
        var tbody = document.getElementById('content-exemptions-body');
        var emptyState = document.getElementById('content-exemptions-empty-state');
        var table = document.getElementById('content-exemptions-table');
        var searchTerm = document.getElementById('content-exemptions-search').value.toLowerCase();
        
        var scopeFilter = document.getElementById('content-exemptions-filter-scope').value;
        var typeFilter = document.getElementById('content-exemptions-filter-type').value;
        var statusFilter = document.getElementById('content-exemptions-filter-status').value;
        
        var exemptions = (mockData.contentExemptions || []).filter(function(ex) {
            if (scopeFilter && ex.scope !== scopeFilter) return false;
            if (typeFilter && ex.type !== typeFilter) return false;
            if (statusFilter && ex.status !== statusFilter) return false;
            if (searchTerm) {
                var match = (ex.accountName || '').toLowerCase().indexOf(searchTerm) !== -1 ||
                            (ex.accountId || '').toLowerCase().indexOf(searchTerm) !== -1 ||
                            (ex.appliedBy || '').toLowerCase().indexOf(searchTerm) !== -1;
                if (!match) return false;
            }
            return true;
        });
        
        updateContentExemptionsFilterChips();
        
        if (exemptions.length === 0) {
            tbody.innerHTML = '';
            table.style.display = 'none';
            emptyState.style.display = 'block';
            return;
        }
        
        table.style.display = 'table';
        emptyState.style.display = 'none';
        tbody.innerHTML = exemptions.map(function(ex) {
            // Exemption Type badge
            var typeBadge = ex.type === 'rule' 
                ? '<span class="sec-status-badge" style="background: #e0e7ff; color: #3730a3;"><i class="fas fa-list me-1"></i>Rule Exemption</span>'
                : '<span class="sec-status-badge" style="background: #fef3c7; color: #92400e;"><i class="fas fa-shield-virus me-1"></i>Anti-Spam Override</span>';
            
            // Rules Exempted (chips with "+N more" truncation)
            var rulesExemptedHtml = '';
            if (ex.type === 'rule' && ex.exemptRules && ex.exemptRules.length > 0) {
                var ruleNames = ex.exemptRules.map(function(rId) {
                    var r = mockData.contentRules.find(function(rule) { return rule.id === rId; });
                    return r ? r.name : rId;
                });
                var displayRules = ruleNames.slice(0, 2);
                var remaining = ruleNames.length - 2;
                rulesExemptedHtml = displayRules.map(function(name) {
                    return '<span class="badge bg-light text-dark me-1" style="font-size: 0.7rem; font-weight: 500;">' + escapeHtml(name) + '</span>';
                }).join('');
                if (remaining > 0) {
                    rulesExemptedHtml += '<span class="badge bg-secondary" style="font-size: 0.7rem;">+' + remaining + ' more</span>';
                }
            } else {
                rulesExemptedHtml = '<span class="text-muted">—</span>';
            }
            
            // Anti-Spam Override display
            var antispamOverrideHtml = '';
            if (ex.type === 'antispam') {
                var overrideLabels = {
                    'disabled': '<span class="badge bg-secondary"><i class="fas fa-times me-1"></i>OFF</span>',
                    'enabled': '<span class="badge" style="background: #1e3a5f; color: #fff;"><i class="fas fa-check me-1"></i>ON (default)</span>',
                    'strict': '<span class="badge" style="background: #dc3545; color: #fff;"><i class="fas fa-shield-alt me-1"></i>Stricter (' + (ex.customWindow || 15) + 'min)</span>',
                    'relaxed': '<span class="badge" style="background: #ffc107; color: #212529;"><i class="fas fa-clock me-1"></i>Less Strict (' + (ex.customWindow || 120) + 'min)</span>',
                    'custom': '<span class="badge" style="background: #6f42c1; color: #fff;"><i class="fas fa-cog me-1"></i>Custom (' + (ex.customWindow || 60) + 'min)</span>'
                };
                antispamOverrideHtml = overrideLabels[ex.antispamOverride] || '<span class="text-muted">—</span>';
            } else {
                antispamOverrideHtml = '<span class="text-muted">—</span>';
            }
            
            // Status badge
            var statusBadge = ex.status === 'active'
                ? '<span class="sec-status-badge active"><i class="fas fa-check-circle me-1"></i>Active</span>'
                : '<span class="sec-status-badge disabled"><i class="fas fa-pause-circle me-1"></i>Disabled</span>';
            
            // Sub-accounts display
            var subAccountText = '';
            if (ex.allSubaccounts) {
                subAccountText = '<span class="text-muted fst-italic">All</span>';
            } else if (ex.subAccounts && ex.subAccounts.length > 0) {
                var displaySubs = ex.subAccounts.slice(0, 2);
                var remainingSubs = ex.subAccounts.length - 2;
                subAccountText = displaySubs.map(function(s) { return escapeHtml(s.name || s.id); }).join(', ');
                if (remainingSubs > 0) {
                    subAccountText += ' <span class="badge bg-secondary" style="font-size: 0.65rem;">+' + remainingSubs + '</span>';
                }
            } else {
                subAccountText = '<span class="text-muted fst-italic">All</span>';
            }
            
            // Format date as DD-MM-YYYY
            var appliedDate = ex.appliedAt || ex.addedAt || '';
            var dateFormatted = appliedDate ? formatDateDDMMYYYY(appliedDate) : '—';
            
            return '<tr data-exemption-id="' + ex.id + '">' +
                '<td><strong>' + escapeHtml(ex.accountName || '') + '</strong><br><small class="text-muted">' + escapeHtml(ex.accountId || '') + '</small></td>' +
                '<td>' + subAccountText + '</td>' +
                '<td>' + typeBadge + '</td>' +
                '<td>' + rulesExemptedHtml + '</td>' +
                '<td>' + antispamOverrideHtml + '</td>' +
                '<td>' + escapeHtml(ex.appliedBy || ex.addedBy || '') + '</td>' +
                '<td><span style="font-size: 0.8rem;">' + dateFormatted + '</span></td>' +
                '<td>' + statusBadge + '</td>' +
                '<td>' +
                    '<div class="action-menu-container">' +
                        '<button class="action-menu-btn" onclick="toggleContentExemptionActionMenu(this, \'' + ex.id + '\', event)"><i class="fas fa-ellipsis-v"></i></button>' +
                        '<div class="action-menu-dropdown" id="content-exemption-menu-' + ex.id + '">' +
                            '<a href="#" onclick="viewContentExemption(\'' + ex.id + '\'); return false;"><i class="fas fa-eye"></i> View Details</a>' +
                            '<a href="#" onclick="editContentExemption(\'' + ex.id + '\'); return false;"><i class="fas fa-edit"></i> Edit</a>' +
                            '<a href="#" onclick="toggleContentExemptionStatus(\'' + ex.id + '\'); return false;"><i class="fas fa-toggle-on"></i> ' + (ex.status === 'active' ? 'Disable' : 'Enable') + '</a>' +
                            '<div class="dropdown-divider"></div>' +
                            '<a href="#" class="text-danger" onclick="deleteContentExemption(\'' + ex.id + '\'); return false;"><i class="fas fa-trash"></i> Delete</a>' +
                        '</div>' +
                    '</div>' +
                '</td>' +
                '</tr>';
        }).join('');
    }
    
    function formatDateDDMMYYYY(dateStr) {
        if (!dateStr) return '—';
        var parts = dateStr.split(' ')[0].split('-');
        if (parts.length === 3) {
            return parts[2] + '-' + parts[1] + '-' + parts[0];
        }
        return dateStr.split(' ')[0];
    }
    
    function updateContentExemptionsFilterChips() {
        var chipsRow = document.getElementById('content-exemptions-chips-row');
        var chipsContainer = document.getElementById('content-exemptions-chips-container');
        var chips = [];
        
        var scopeFilter = document.getElementById('content-exemptions-filter-scope').value;
        var typeFilter = document.getElementById('content-exemptions-filter-type').value;
        var statusFilter = document.getElementById('content-exemptions-filter-status').value;
        var searchTerm = document.getElementById('content-exemptions-search').value;
        
        if (scopeFilter) {
            var scopeLabels = { 'account': 'Account-level', 'subaccount': 'Sub-account-level' };
            chips.push({ key: 'scope', label: 'Scope: ' + (scopeLabels[scopeFilter] || scopeFilter) });
        }
        if (typeFilter) {
            var typeLabels = { 'rule': 'Rule Exemption', 'antispam': 'Anti-Spam Override' };
            chips.push({ key: 'type', label: 'Type: ' + (typeLabels[typeFilter] || typeFilter) });
        }
        if (statusFilter) {
            chips.push({ key: 'status', label: 'Status: ' + (statusFilter.charAt(0).toUpperCase() + statusFilter.slice(1)) });
        }
        if (searchTerm) {
            chips.push({ key: 'search', label: 'Search: "' + searchTerm + '"' });
        }
        
        if (chips.length === 0) {
            chipsRow.style.display = 'none';
            return;
        }
        
        chipsRow.style.display = 'block';
        chipsContainer.innerHTML = chips.map(function(chip) {
            return '<span class="badge bg-light text-dark d-flex align-items-center gap-1" style="font-size: 0.75rem; padding: 0.35rem 0.5rem;">' +
                chip.label +
                '<button type="button" class="btn-close btn-close-sm ms-1" style="font-size: 0.5rem;" onclick="removeContentExemptionsFilter(\'' + chip.key + '\')"></button>' +
            '</span>';
        }).join('') +
        '<button class="btn btn-link btn-sm text-secondary p-0 ms-2" onclick="resetContentExemptionsFilters()">Clear All</button>';
    }
    
    function removeContentExemptionsFilter(filterKey) {
        switch(filterKey) {
            case 'scope':
                document.getElementById('content-exemptions-filter-scope').value = '';
                break;
            case 'type':
                document.getElementById('content-exemptions-filter-type').value = '';
                break;
            case 'status':
                document.getElementById('content-exemptions-filter-status').value = '';
                break;
            case 'search':
                document.getElementById('content-exemptions-search').value = '';
                break;
        }
        renderContentExemptionsTab();
    }
    
    function toggleContentExemptionActionMenu(btn, exemptionId, event) {
        if (event) {
            event.stopPropagation();
            event.preventDefault();
        }
        document.querySelectorAll('.action-menu-dropdown').forEach(function(menu) {
            if (menu.id !== 'content-exemption-menu-' + exemptionId) {
                menu.classList.remove('show');
            }
        });
        var menu = document.getElementById('content-exemption-menu-' + exemptionId);
        if (menu) {
            menu.classList.toggle('show');
        }
    }
    
    function showAddContentExemptionModal() {
        document.getElementById('content-exemption-modal-title').textContent = 'Add Content Exemption';
        document.getElementById('content-exemption-form').reset();
        document.getElementById('content-exemption-id').value = '';
        document.getElementById('content-exemption-save-btn-text').textContent = 'Save Exemption';
        document.getElementById('content-exemption-type-rule').checked = true;
        
        // Reset account search
        document.getElementById('content-exemption-account-search').value = '';
        document.getElementById('content-exemption-account').value = '';
        
        // Reset subaccounts
        document.getElementById('content-exemption-all-subaccounts').checked = true;
        document.getElementById('content-exemption-subaccounts-list').innerHTML = '<small class="text-muted">Select an account first</small>';
        
        // Reset anti-spam options
        document.getElementById('antispam-toggle-on').checked = true;
        document.getElementById('content-exemption-antispam-mode').value = 'default';
        document.getElementById('antispam-mode-group').style.display = 'block';
        document.getElementById('antispam-window-override-group').style.display = 'none';
        
        // Reset reason field
        var reasonField = document.getElementById('content-exemption-reason');
        if (reasonField) reasonField.value = '';
        
        // Populate rules checklist
        populateContentRulesChecklist();
        
        toggleContentExemptionType();
        
        var modal = new bootstrap.Modal(document.getElementById('contentExemptionModal'));
        modal.show();
    }
    
    function filterContentExemptionAccounts() {
        var searchTerm = document.getElementById('content-exemption-account-search').value.toLowerCase();
        var dropdown = document.getElementById('content-exemption-account-dropdown');
        
        var filtered = mockData.accounts.filter(function(acc) {
            return acc.name.toLowerCase().indexOf(searchTerm) !== -1 || 
                   acc.id.toLowerCase().indexOf(searchTerm) !== -1;
        });
        
        if (filtered.length === 0) {
            dropdown.innerHTML = '<div class="dropdown-item text-muted">No accounts found</div>';
        } else {
            dropdown.innerHTML = filtered.slice(0, 10).map(function(acc) {
                return '<a class="dropdown-item" href="#" onclick="selectContentExemptionAccount(\'' + acc.id + '\', \'' + escapeHtml(acc.name) + '\'); return false;">' +
                    '<strong>' + escapeHtml(acc.name) + '</strong> <small class="text-muted">(' + acc.id + ')</small></a>';
            }).join('');
        }
        
        dropdown.classList.add('show');
    }
    
    function showContentExemptionAccountDropdown() {
        filterContentExemptionAccounts();
    }
    
    function selectContentExemptionAccount(accountId, accountName) {
        document.getElementById('content-exemption-account').value = accountId;
        document.getElementById('content-exemption-account-search').value = accountName + ' (' + accountId + ')';
        document.getElementById('content-exemption-account-dropdown').classList.remove('show');
        
        loadContentExemptionSubaccounts();
    }
    
    function toggleAllSubaccounts() {
        var allChecked = document.getElementById('content-exemption-all-subaccounts').checked;
        var checkboxes = document.querySelectorAll('.subaccount-checkbox');
        checkboxes.forEach(function(cb) {
            cb.checked = false;
            cb.disabled = allChecked;
        });
    }
    
    function updateAntispamModeOptions() {
        var isOn = document.getElementById('antispam-toggle-on').checked;
        document.getElementById('antispam-mode-group').style.display = isOn ? 'block' : 'none';
        document.getElementById('antispam-window-override-group').style.display = 'none';
    }
    
    function populateContentRulesChecklist() {
        var container = document.getElementById('content-rules-checklist');
        if (mockData.contentRules.length === 0) {
            container.innerHTML = '<div class="text-muted text-center py-3"><i class="fas fa-info-circle me-1"></i>No content rules available</div>';
            return;
        }
        
        container.innerHTML = mockData.contentRules.map(function(rule) {
            var matchTypeBadge = rule.matchType === 'keyword' 
                ? '<span class="badge bg-light text-dark" style="font-size: 0.65rem;">Keyword</span>'
                : '<span class="badge bg-light text-dark" style="font-size: 0.65rem;">Regex</span>';
            
            return '<div class="form-check py-1 border-bottom">' +
                '<input class="form-check-input content-rule-checkbox" type="checkbox" value="' + rule.id + '" id="rule-check-' + rule.id + '">' +
                '<label class="form-check-label d-flex justify-content-between align-items-center w-100" for="rule-check-' + rule.id + '">' +
                    '<span>' + escapeHtml(rule.name) + '</span>' +
                    matchTypeBadge +
                '</label>' +
            '</div>';
        }).join('');
    }
    
    function loadContentExemptionSubaccounts() {
        var accountId = document.getElementById('content-exemption-account').value;
        var container = document.getElementById('content-exemption-subaccounts-list');
        var allCheckbox = document.getElementById('content-exemption-all-subaccounts');
        
        if (!accountId) {
            container.innerHTML = '<small class="text-muted">Select an account first</small>';
            allCheckbox.checked = true;
            return;
        }
        
        var account = mockData.accounts.find(function(a) { return a.id === accountId; });
        if (!account || !account.subAccounts || account.subAccounts.length === 0) {
            container.innerHTML = '<small class="text-muted">No sub-accounts available</small>';
            allCheckbox.checked = true;
            return;
        }
        
        container.innerHTML = account.subAccounts.map(function(sub) {
            return '<div class="form-check">' +
                '<input class="form-check-input subaccount-checkbox" type="checkbox" value="' + sub.id + '" id="subaccount-' + sub.id + '" disabled onchange="handleSubaccountChange()">' +
                '<label class="form-check-label" for="subaccount-' + sub.id + '">' + escapeHtml(sub.name) + '</label>' +
            '</div>';
        }).join('');
        
        allCheckbox.checked = true;
    }
    
    function handleSubaccountChange() {
        var checkedBoxes = document.querySelectorAll('.subaccount-checkbox:checked');
        if (checkedBoxes.length === 0) {
            document.getElementById('content-exemption-all-subaccounts').checked = true;
            toggleAllSubaccounts();
        }
    }
    
    function toggleContentExemptionType() {
        var type = document.querySelector('input[name="content-exemption-type"]:checked').value;
        var rulesSection = document.getElementById('content-exemption-rules-section');
        var antispamSection = document.getElementById('content-exemption-antispam-section');
        
        if (type === 'rule') {
            rulesSection.style.display = 'block';
            antispamSection.style.display = 'none';
        } else {
            rulesSection.style.display = 'none';
            antispamSection.style.display = 'block';
        }
        
        updateAntispamOverrideWindow();
    }
    
    function updateAntispamOverrideWindow() {
        var mode = document.getElementById('content-exemption-antispam-mode').value;
        document.getElementById('antispam-window-override-group').style.display = (mode === 'custom' || mode === 'stricter' || mode === 'relaxed') ? 'block' : 'none';
    }
    
    function saveContentExemption() {
        var accountId = document.getElementById('content-exemption-account').value;
        if (!accountId) {
            showToast('Please select an account', 'error');
            return;
        }
        
        var type = document.querySelector('input[name="content-exemption-type"]:checked').value;
        var account = mockData.accounts.find(function(a) { return a.id === accountId; });
        
        // Get selected subaccounts from checkboxes
        var allSubaccounts = document.getElementById('content-exemption-all-subaccounts').checked;
        var selectedSubaccounts = [];
        
        if (!allSubaccounts) {
            var checkedBoxes = document.querySelectorAll('.subaccount-checkbox:checked');
            checkedBoxes.forEach(function(cb) {
                var sub = account && account.subAccounts ? account.subAccounts.find(function(s) { return s.id === cb.value; }) : null;
                selectedSubaccounts.push(sub || { id: cb.value, name: cb.value });
            });
        }
        
        var exemptionId = document.getElementById('content-exemption-id').value;
        var reason = (document.getElementById('content-exemption-reason') || {}).value || '';
        reason = reason.trim();
        
        // Get before state for audit
        var beforeState = null;
        var existingIdx = mockData.contentExemptions.findIndex(function(e) { return e.id === exemptionId; });
        if (existingIdx !== -1) {
            beforeState = JSON.parse(JSON.stringify(mockData.contentExemptions[existingIdx]));
        }
        
        var exemptionData = {
            id: exemptionId || 'CEX-' + String(mockData.contentExemptions.length + 1).padStart(3, '0'),
            accountId: accountId,
            accountName: account ? account.name : accountId,
            subAccounts: selectedSubaccounts,
            allSubaccounts: allSubaccounts,
            scope: selectedSubaccounts.length > 0 ? 'subaccount' : 'account',
            type: type,
            status: 'active',
            reason: reason,
            metadata_json: JSON.stringify({ reason: reason, updatedBy: currentAdmin.email }),
            appliedBy: currentAdmin.email,
            appliedAt: formatDateTime(new Date()),
            addedBy: beforeState ? beforeState.addedBy : currentAdmin.email,
            addedAt: beforeState ? beforeState.addedAt : formatDateTime(new Date()),
            updatedAt: formatDateTime(new Date())
        };
        
        if (type === 'rule') {
            var selectedRules = Array.from(document.querySelectorAll('.content-rule-checkbox:checked')).map(function(cb) {
                return cb.value;
            });
            if (selectedRules.length === 0) {
                showToast('Please select at least one rule to exempt from', 'error');
                return;
            }
            exemptionData.exemptRules = selectedRules;
        } else {
            // Anti-spam override with new ON/OFF toggle and mode
            var isOn = document.getElementById('antispam-toggle-on').checked;
            var mode = document.getElementById('content-exemption-antispam-mode').value;
            var customWindow = parseInt(document.getElementById('content-exemption-antispam-window').value);
            
            if (!isOn) {
                exemptionData.antispamOverride = 'disabled';
            } else if (mode === 'default') {
                exemptionData.antispamOverride = 'enabled';
            } else if (mode === 'stricter') {
                exemptionData.antispamOverride = 'strict';
                exemptionData.customWindow = customWindow;
            } else if (mode === 'relaxed') {
                exemptionData.antispamOverride = 'relaxed';
                exemptionData.customWindow = customWindow;
            } else {
                exemptionData.antispamOverride = 'custom';
                exemptionData.customWindow = customWindow;
            }
        }
        
        // Determine audit event type
        var isNew = existingIdx === -1;
        var eventType;
        if (type === 'rule') {
            eventType = isNew ? 'CONTENT_EXEMPTION_ADDED' : 'CONTENT_EXEMPTION_UPDATED';
        } else {
            eventType = isNew ? 'ANTISPAM_OVERRIDE_ADDED' : 'ANTISPAM_OVERRIDE_UPDATED';
        }
        
        // Save exemption
        if (existingIdx !== -1) {
            mockData.contentExemptions[existingIdx] = exemptionData;
        } else {
            mockData.contentExemptions.push(exemptionData);
        }
        
        // Log audit event with full before/after snapshot
        logAuditEvent(eventType, {
            exemptionId: exemptionData.id,
            adminUser: currentAdmin.email,
            timestamp: new Date().toISOString(),
            accountId: exemptionData.accountId,
            accountName: exemptionData.accountName,
            subAccountsAffected: exemptionData.subAccounts.map(function(s) { return s.id; }),
            allSubaccounts: exemptionData.allSubaccounts,
            scope: exemptionData.scope,
            type: exemptionData.type,
            ruleIdsAffected: exemptionData.exemptRules || [],
            antispamOverride: exemptionData.antispamOverride || null,
            customWindow: exemptionData.customWindow || null,
            reason: reason,
            before: beforeState,
            after: exemptionData
        });
        
        bootstrap.Modal.getInstance(document.getElementById('contentExemptionModal')).hide();
        renderContentExemptionsTab();
        showToast('Content exemption ' + (isNew ? 'added' : 'updated') + ' successfully', 'success');
    }
    
    function viewContentExemption(exemptionId) {
        var ex = mockData.contentExemptions.find(function(e) { return e.id === exemptionId; });
        if (!ex) return;
        
        closeAllContentExemptionMenus();
        
        // Get account and subaccount names
        var account = mockData.accounts.find(function(a) { return a.id === ex.accountId; });
        var accountName = account ? account.name : ex.accountName || ex.accountId;
        
        var subAccountsHtml = '';
        if (ex.allSubaccounts) {
            subAccountsHtml = '<span class="badge bg-info" style="font-size: 0.7rem;">All Sub-accounts</span>';
        } else if (ex.subAccounts && ex.subAccounts.length > 0) {
            subAccountsHtml = ex.subAccounts.map(function(sub) {
                return '<span class="badge bg-secondary me-1 mb-1" style="font-size: 0.65rem;">' + escapeHtml(sub.name || sub.id) + '</span>';
            }).join('');
        } else {
            subAccountsHtml = '<span class="text-muted" style="font-size: 0.75rem;">Account only</span>';
        }
        
        // Build exemption type display
        var typeHtml = '';
        if (ex.type === 'rule') {
            typeHtml = '<span class="badge" style="background: #6366f1; color: white; font-size: 0.7rem;"><i class="fas fa-list me-1"></i>Rule Exemption</span>';
        } else {
            typeHtml = '<span class="badge" style="background: #f59e0b; color: white; font-size: 0.7rem;"><i class="fas fa-shield-alt me-1"></i>Anti-Spam Override</span>';
        }
        
        // Build rules exempted list
        var rulesHtml = '';
        if (ex.exemptRules && ex.exemptRules.length > 0) {
            rulesHtml = ex.exemptRules.map(function(ruleId) {
                var rule = mockData.contentRules.find(function(r) { return r.id === ruleId; });
                return '<span class="badge bg-primary me-1 mb-1" style="font-size: 0.65rem;">' + escapeHtml(rule ? rule.name : ruleId) + '</span>';
            }).join('');
        } else {
            rulesHtml = '<span class="text-muted" style="font-size: 0.75rem;">None</span>';
        }
        
        // Build anti-spam override display
        var antispamHtml = '';
        if (ex.antispamOverride) {
            var overrideLabels = {
                'enabled': '<span class="badge bg-success">ON (default)</span>',
                'disabled': '<span class="badge bg-danger">OFF</span>',
                'stricter': '<span class="badge" style="background: #dc3545;">Stricter (' + (ex.customWindow || 15) + 'min)</span>',
                'less_strict': '<span class="badge" style="background: #ffc107; color: #212529;">Less Strict (' + (ex.customWindow || 120) + 'min)</span>'
            };
            antispamHtml = overrideLabels[ex.antispamOverride] || '<span class="text-muted">-</span>';
        } else {
            antispamHtml = '<span class="text-muted" style="font-size: 0.75rem;">No override</span>';
        }
        
        // Status badge
        var statusHtml = ex.status === 'active' ? 
            '<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Active</span>' : 
            '<span class="badge bg-secondary"><i class="fas fa-pause-circle me-1"></i>Disabled</span>';
        
        var modalHtml = 
            '<div class="modal fade" id="viewContentExemptionModal" tabindex="-1">' +
                '<div class="modal-dialog modal-lg modal-dialog-centered">' +
                    '<div class="modal-content">' +
                        '<div class="modal-header py-2" style="background: #f8f9fa; border-bottom: 1px solid #e9ecef;">' +
                            '<h6 class="modal-title" style="font-size: 0.9rem; color: #1e3a5f;"><i class="fas fa-eye me-2"></i>View Exemption Details</h6>' +
                            '<button type="button" class="btn-close" data-bs-dismiss="modal" style="font-size: 0.7rem;"></button>' +
                        '</div>' +
                        '<div class="modal-body py-3">' +
                            '<div class="row g-3">' +
                                '<div class="col-md-6">' +
                                    '<table class="table table-sm mb-0" style="font-size: 0.8rem;">' +
                                        '<tr><td style="width: 35%; font-weight: 600; padding: 0.4rem; background: #f8f9fa;">Exemption ID</td><td style="padding: 0.4rem;"><code style="font-size: 0.75rem;">' + escapeHtml(ex.id) + '</code></td></tr>' +
                                        '<tr><td style="font-weight: 600; padding: 0.4rem; background: #f8f9fa;">Account</td><td style="padding: 0.4rem;">' + escapeHtml(accountName) + '</td></tr>' +
                                        '<tr><td style="font-weight: 600; padding: 0.4rem; background: #f8f9fa;">Sub-accounts</td><td style="padding: 0.4rem;">' + subAccountsHtml + '</td></tr>' +
                                        '<tr><td style="font-weight: 600; padding: 0.4rem; background: #f8f9fa;">Type</td><td style="padding: 0.4rem;">' + typeHtml + '</td></tr>' +
                                        '<tr><td style="font-weight: 600; padding: 0.4rem; background: #f8f9fa;">Status</td><td style="padding: 0.4rem;">' + statusHtml + '</td></tr>' +
                                    '</table>' +
                                '</div>' +
                                '<div class="col-md-6">' +
                                    '<table class="table table-sm mb-0" style="font-size: 0.8rem;">' +
                                        '<tr><td style="width: 40%; font-weight: 600; padding: 0.4rem; background: #f8f9fa;">Rules Exempted</td><td style="padding: 0.4rem;">' + rulesHtml + '</td></tr>' +
                                        '<tr><td style="font-weight: 600; padding: 0.4rem; background: #f8f9fa;">Anti-Spam Override</td><td style="padding: 0.4rem;">' + antispamHtml + '</td></tr>' +
                                        '<tr><td style="font-weight: 600; padding: 0.4rem; background: #f8f9fa;">Applied By</td><td style="padding: 0.4rem;">' + escapeHtml(ex.appliedBy || ex.addedBy || '-') + '</td></tr>' +
                                        '<tr><td style="font-weight: 600; padding: 0.4rem; background: #f8f9fa;">Applied Date</td><td style="padding: 0.4rem;">' + (ex.appliedAt || ex.addedAt || '-') + '</td></tr>' +
                                        '<tr><td style="font-weight: 600; padding: 0.4rem; background: #f8f9fa;">Last Updated</td><td style="padding: 0.4rem;">' + (ex.updatedAt || '-') + '</td></tr>' +
                                    '</table>' +
                                '</div>' +
                            '</div>' +
                            (ex.notes || ex.reason ? '<div class="mt-3 p-2" style="background: #f8f9fa; border-radius: 4px; font-size: 0.8rem;"><strong>Notes:</strong> ' + escapeHtml(ex.notes || ex.reason) + '</div>' : '') +
                        '</div>' +
                        '<div class="modal-footer py-2" style="border-top: 1px solid #e9ecef;">' +
                            '<button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal" style="font-size: 0.8rem;">Close</button>' +
                            '<button type="button" class="btn btn-sm text-white" style="background: #1e3a5f; font-size: 0.8rem;" onclick="bootstrap.Modal.getInstance(document.getElementById(\'viewContentExemptionModal\')).hide(); editContentExemption(\'' + escapeHtml(ex.id) + '\');"><i class="fas fa-edit me-1"></i>Edit</button>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
            '</div>';
        
        // Remove existing modal if any
        var existingModal = document.getElementById('viewContentExemptionModal');
        if (existingModal) existingModal.remove();
        
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        var modal = new bootstrap.Modal(document.getElementById('viewContentExemptionModal'));
        modal.show();
    }
    
    function editContentExemption(exemptionId) {
        var ex = mockData.contentExemptions.find(function(e) { return e.id === exemptionId; });
        if (!ex) return;
        
        closeAllContentExemptionMenus();
        
        document.getElementById('content-exemption-modal-title').textContent = 'Edit Content Exemption';
        document.getElementById('content-exemption-save-btn-text').textContent = 'Update Exemption';
        document.getElementById('content-exemption-id').value = ex.id;
        
        // Populate accounts
        var accountSelect = document.getElementById('content-exemption-account');
        accountSelect.innerHTML = '<option value="">Select Account...</option>' + 
            mockData.accounts.map(function(acc) {
                return '<option value="' + acc.id + '"' + (acc.id === ex.accountId ? ' selected' : '') + '>' + escapeHtml(acc.name) + ' (' + acc.id + ')</option>';
            }).join('');
        
        loadContentExemptionSubaccounts();
        
        // Set subaccounts
        if (ex.subAccounts && ex.subAccounts.length > 0) {
            var subSelect = document.getElementById('content-exemption-subaccount');
            ex.subAccounts.forEach(function(sub) {
                var opt = subSelect.querySelector('option[value="' + sub.id + '"]');
                if (opt) opt.selected = true;
            });
        }
        
        // Set type
        if (ex.type === 'rule') {
            document.getElementById('content-exemption-type-rule').checked = true;
        } else {
            document.getElementById('content-exemption-type-antispam').checked = true;
        }
        
        toggleContentExemptionType();
        
        // Populate rules checklist
        populateContentRulesChecklist();
        
        // Check selected rules
        if (ex.exemptRules) {
            ex.exemptRules.forEach(function(ruleId) {
                var cb = document.getElementById('rule-check-' + ruleId);
                if (cb) cb.checked = true;
            });
        }
        
        // Set antispam override
        if (ex.antispamOverride) {
            document.getElementById('content-exemption-antispam-override').value = ex.antispamOverride;
            if (ex.customWindow) {
                document.getElementById('content-exemption-antispam-window').value = ex.customWindow;
            }
        }
        
        // Set reason field
        var reasonField = document.getElementById('content-exemption-reason');
        if (reasonField) reasonField.value = ex.reason || ex.notes || '';
        
        var modal = new bootstrap.Modal(document.getElementById('contentExemptionModal'));
        modal.show();
    }
    
    function toggleContentExemptionStatus(exemptionId) {
        var ex = mockData.contentExemptions.find(function(e) { return e.id === exemptionId; });
        if (!ex) return;
        
        closeAllContentExemptionMenus();
        
        var beforeStatus = ex.status;
        ex.status = ex.status === 'active' ? 'disabled' : 'active';
        ex.updatedAt = formatDateTime(new Date());
        
        logAuditEvent('CONTENT_EXEMPTION_STATUS_CHANGED', {
            exemptionId: exemptionId,
            accountId: ex.accountId,
            beforeStatus: beforeStatus,
            afterStatus: ex.status
        });
        
        renderContentExemptionsTab();
        showSuccessToast('Exemption ' + (ex.status === 'active' ? 'enabled' : 'disabled'));
    }
    
    function deleteContentExemption(exemptionId) {
        var ex = mockData.contentExemptions.find(function(e) { return e.id === exemptionId; });
        if (!ex) return;
        
        closeAllContentExemptionMenus();
        
        showActionConfirmation({
            id: exemptionId,
            type: 'content_exemption',
            action: 'delete',
            title: 'Delete Exemption',
            icon: 'fa-trash-alt',
            iconColor: 'text-danger',
            message: 'Are you sure you want to delete this exemption?',
            details: '<table class="table table-sm" style="font-size: 0.85rem;">' +
                '<tr><td class="text-muted" style="width: 35%;">Account</td><td>' + escapeHtml(ex.accountName) + '</td></tr>' +
                '<tr><td class="text-muted">Type</td><td>' + (ex.type === 'rule' ? 'Rule Exemption' : 'Anti-Spam Override') + '</td></tr>' +
                '</table>' +
                '<div class="alert alert-danger" style="font-size: 0.8rem; padding: 0.5rem;"><i class="fas fa-exclamation-triangle me-1"></i>This action cannot be undone.</div>',
            btnText: 'Delete Exemption',
            btnClass: 'btn-danger',
            showReason: true,
            reasonPlaceholder: 'Reason for removing this exemption (optional)...'
        });
    }
    
    function executeDeleteContentExemption(exemptionId, reason) {
        var idx = mockData.contentExemptions.findIndex(function(e) { return e.id === exemptionId; });
        if (idx === -1) return;
        
        var ex = mockData.contentExemptions[idx];
        var beforeState = JSON.parse(JSON.stringify(ex));
        mockData.contentExemptions.splice(idx, 1);
        
        // Determine correct event type based on exemption type
        var eventType = ex.type === 'rule' ? 'CONTENT_EXEMPTION_REMOVED' : 'ANTISPAM_OVERRIDE_REMOVED';
        
        logAuditEvent(eventType, {
            exemptionId: exemptionId,
            adminUser: currentAdmin.email,
            timestamp: new Date().toISOString(),
            accountId: ex.accountId,
            accountName: ex.accountName,
            subAccountsAffected: (ex.subAccounts || []).map(function(s) { return s.id; }),
            ruleIdsAffected: ex.exemptRules || [],
            antispamOverride: ex.antispamOverride || null,
            reason: reason || '',
            before: beforeState,
            after: null
        });
        
        renderContentExemptionsTab();
        showSuccessToast('Exemption deleted successfully');
    }
    
    function closeAllContentExemptionMenus() {
        document.querySelectorAll('.action-menu-dropdown').forEach(function(menu) {
            menu.classList.remove('show');
        });
    }
    
    function toggleContentExemptionsFilterPanel() {
        var panel = document.getElementById('content-exemptions-filter-panel');
        var btn = document.getElementById('content-exemptions-filter-btn');
        if (panel.style.display === 'none') {
            panel.style.display = 'block';
            btn.classList.add('active');
        } else {
            panel.style.display = 'none';
            btn.classList.remove('active');
        }
    }
    
    function filterContentExemptionsTable() {
        renderContentExemptionsTab();
    }
    
    function applyContentExemptionsFilters() {
        updateContentExemptionsFilterCount();
        document.getElementById('content-exemptions-filter-panel').style.display = 'none';
        document.getElementById('content-exemptions-filter-btn').classList.remove('active');
        renderContentExemptionsTab();
    }
    
    function resetContentExemptionsFilters() {
        document.getElementById('content-exemptions-filter-scope').value = '';
        document.getElementById('content-exemptions-filter-type').value = '';
        document.getElementById('content-exemptions-filter-status').value = '';
        document.getElementById('content-exemptions-search').value = '';
        document.getElementById('content-exemptions-filter-panel').style.display = 'none';
        document.getElementById('content-exemptions-filter-btn').classList.remove('active');
        document.getElementById('content-exemptions-filter-count').style.display = 'none';
        renderContentExemptionsTab();
    }
    
    function updateContentExemptionsFilterCount() {
        var count = 0;
        if (document.getElementById('content-exemptions-filter-scope').value) count++;
        if (document.getElementById('content-exemptions-filter-type').value) count++;
        if (document.getElementById('content-exemptions-filter-status').value) count++;
        
        var badge = document.getElementById('content-exemptions-filter-count');
        if (count > 0) {
            badge.textContent = count;
            badge.style.display = 'inline-block';
        } else {
            badge.style.display = 'none';
        }
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
            if (searchTerm) {
                var nameMatch = rule.name && rule.name.toLowerCase().indexOf(searchTerm) !== -1;
                var patternMatch = rule.pattern.toLowerCase().indexOf(searchTerm) !== -1;
                if (!nameMatch && !patternMatch) return false;
            }
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
                    'exact': '<span class="sec-status-badge" style="background: #dbeafe; color: #1e40af;">Exact</span>',
                    'wildcard': '<span class="sec-status-badge" style="background: #fef3c7; color: #92400e;">Wildcard</span>',
                    'regex': '<span class="sec-status-badge" style="background: #f3e8ff; color: #6b21a8;">Regex</span>'
                };
                
                var ruleTypeBadge = rule.ruleType === 'block'
                    ? '<span class="sec-status-badge blocked">Block</span>'
                    : '<span class="sec-status-badge pending">Quarantine</span>';
                
                var statusBadge = '<span class="sec-status-badge ' + rule.status + '">' + 
                    (rule.status === 'active' ? 'Enabled' : 'Disabled') + '</span>';
                
                var dateOnly = rule.updatedAt.split(' ')[0];
                var ruleName = rule.name || rule.pattern;
                
                return '<tr data-rule-id="' + rule.id + '">' +
                    '<td><strong style="font-size: 0.85rem;">' + escapeHtml(ruleName) + '</strong><br><small class="text-muted" style="font-size: 0.7rem;">' + rule.id + '</small></td>' +
                    '<td><code style="background: #f8f9fa; padding: 0.2rem 0.4rem; border-radius: 4px; font-size: 0.8rem;">' + escapeHtml(rule.pattern) + '</code></td>' +
                    '<td>' + matchTypeBadges[rule.matchType] + '</td>' +
                    '<td>' + ruleTypeBadge + '</td>' +
                    '<td>' + statusBadge + '</td>' +
                    '<td><span style="font-size: 0.8rem;">' + dateOnly + '</span></td>' +
                    '<td>' +
                        '<div class="action-menu-container">' +
                            '<button class="action-menu-btn" onclick="toggleUrlActionMenu(this, \'' + rule.id + '\', event)"><i class="fas fa-ellipsis-v"></i></button>' +
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
    
    var urlExemptionsSortColumn = 'appliedAt';
    var urlExemptionsSortDirection = 'desc';
    
    function renderUrlExemptionsTab() {
        var tbody = document.getElementById('url-exemptions-body');
        var emptyState = document.getElementById('url-exemptions-empty-state');
        
        if (!tbody) return;
        
        // Populate account filter dropdown
        populateUrlExemptionsAccountFilter();
        
        // Get filter values
        var statusFilter = (document.getElementById('url-exemptions-filter-status') || {}).value || '';
        var typeFilter = (document.getElementById('url-exemptions-filter-type') || {}).value || '';
        var accountFilter = (document.getElementById('url-exemptions-filter-account') || {}).value || '';
        var subaccountFilter = (document.getElementById('url-exemptions-filter-subaccount') || {}).value || '';
        var dateFromFilter = (document.getElementById('url-exemptions-filter-date-from') || {}).value || '';
        var dateToFilter = (document.getElementById('url-exemptions-filter-date-to') || {}).value || '';
        var searchTerm = (document.getElementById('url-exemptions-search') || {}).value || '';
        searchTerm = searchTerm.toLowerCase();
        
        var exemptions = (mockData.urlExemptions || []).filter(function(ex) {
            if (statusFilter && ex.status !== statusFilter) return false;
            if (typeFilter && ex.type !== typeFilter) return false;
            if (accountFilter && ex.accountId !== accountFilter) return false;
            if (subaccountFilter) {
                if (ex.allSubaccounts) return true;
                var hasSubaccount = ex.subAccounts && ex.subAccounts.some(function(s) { return s.id === subaccountFilter; });
                if (!hasSubaccount) return false;
            }
            if (dateFromFilter) {
                var exDateIso = parseExemptionDate(ex.appliedAt);
                if (!exDateIso || exDateIso < dateFromFilter) return false;
            }
            if (dateToFilter) {
                var exDateIso = parseExemptionDate(ex.appliedAt);
                if (!exDateIso || exDateIso > dateToFilter) return false;
            }
            if (searchTerm && ex.accountName.toLowerCase().indexOf(searchTerm) === -1 && 
                ex.accountId.toLowerCase().indexOf(searchTerm) === -1) return false;
            return true;
        });
        
        // Sort exemptions
        exemptions.sort(function(a, b) {
            var aVal, bVal;
            if (urlExemptionsSortColumn === 'appliedAt') {
                aVal = parseExemptionDate(a.appliedAt) || '';
                bVal = parseExemptionDate(b.appliedAt) || '';
            } else {
                aVal = a[urlExemptionsSortColumn] || '';
                bVal = b[urlExemptionsSortColumn] || '';
            }
            if (urlExemptionsSortDirection === 'asc') {
                return aVal > bVal ? 1 : -1;
            } else {
                return aVal < bVal ? 1 : -1;
            }
        });
        
        if (exemptions.length === 0) {
            tbody.innerHTML = '';
            if (emptyState) emptyState.style.display = 'block';
            return;
        }
        
        if (emptyState) emptyState.style.display = 'none';
        tbody.innerHTML = exemptions.map(function(ex) {
            var subAccountsHtml = ex.allSubaccounts 
                ? '<span class="badge bg-info text-white" style="font-size: 0.65rem;">All Sub-accounts</span>'
                : (ex.subAccounts && ex.subAccounts.length > 0 
                    ? ex.subAccounts.slice(0, 3).map(function(s) { return '<span class="badge bg-light text-dark me-1" style="font-size: 0.65rem;">' + escapeHtml(s.name) + '</span>'; }).join('') + (ex.subAccounts.length > 3 ? '<span class="badge bg-secondary" style="font-size: 0.65rem;">+' + (ex.subAccounts.length - 3) + '</span>' : '')
                    : '<span class="text-muted" style="font-size: 0.75rem;">Account only</span>');
            
            var typeBadge = '';
            if (ex.type === 'domain_age') {
                typeBadge = '<span class="sec-status-badge" style="background: #dbeafe; color: #1e40af;"><i class="fas fa-clock me-1"></i>Domain Age Override</span>';
            } else if (ex.type === 'domains') {
                typeBadge = '<span class="sec-status-badge" style="background: #d1fae5; color: #065f46;"><i class="fas fa-globe me-1"></i>Allowlisted Domains</span>';
            } else {
                typeBadge = '<span class="sec-status-badge" style="background: #f3e8ff; color: #6b21a8;"><i class="fas fa-link me-1"></i>Rule Exemption</span>';
            }
            
            // Details column based on type
            var detailsHtml = '';
            if (ex.type === 'domain_age') {
                if (ex.disableEnforcement) {
                    detailsHtml = '<span class="badge bg-warning text-dark" style="font-size: 0.7rem;"><i class="fas fa-ban me-1"></i>Enforcement Disabled</span>';
                } else {
                    var thresholdText = ex.thresholdOverride ? ex.thresholdOverride + 'h' : 'Default';
                    var actionText = ex.actionOverride ? ex.actionOverride.charAt(0).toUpperCase() + ex.actionOverride.slice(1) : 'Default';
                    detailsHtml = '<span class="badge bg-light text-dark me-1" style="font-size: 0.7rem;">Threshold: ' + thresholdText + '</span>' +
                        '<span class="badge bg-light text-dark" style="font-size: 0.7rem;">Action: ' + actionText + '</span>';
                }
            } else if (ex.type === 'domains' && ex.domains && ex.domains.length > 0) {
                var displayDomains = ex.domains.slice(0, 3);
                detailsHtml = displayDomains.map(function(d) { 
                    return '<span class="badge bg-success bg-opacity-25 text-success me-1" style="font-size: 0.65rem;">' + escapeHtml(d) + '</span>'; 
                }).join('');
                if (ex.domains.length > 3) {
                    detailsHtml += '<span class="badge bg-secondary" style="font-size: 0.65rem;">+' + (ex.domains.length - 3) + ' more</span>';
                }
            } else if (ex.type === 'url_rule' && ex.exemptRules && ex.exemptRules.length > 0) {
                var displayRules = ex.exemptRules.slice(0, 2);
                detailsHtml = displayRules.map(function(ruleId) {
                    var rule = mockData.urlRules.find(function(r) { return r.id === ruleId; });
                    var label = rule ? rule.pattern : ruleId;
                    return '<span class="badge bg-purple-light text-purple me-1" style="font-size: 0.65rem; background: #f3e8ff; color: #7c3aed;">' + escapeHtml(label) + '</span>';
                }).join('');
                if (ex.exemptRules.length > 2) {
                    detailsHtml += '<span class="badge bg-secondary" style="font-size: 0.65rem;">+' + (ex.exemptRules.length - 2) + ' more</span>';
                }
            } else {
                detailsHtml = '<span class="text-muted" style="font-size: 0.75rem;">-</span>';
            }
            
            var statusBadge = '<span class="sec-status-badge ' + ex.status + '">' + 
                (ex.status === 'active' ? '<i class="fas fa-check-circle me-1"></i>' : '<i class="fas fa-pause-circle me-1"></i>') +
                ex.status.charAt(0).toUpperCase() + ex.status.slice(1) + '</span>';
            
            var dateOnly = ex.appliedAt ? ex.appliedAt.split(' ')[0] : '-';
            
            return '<tr data-exemption-id="' + ex.id + '">' +
                '<td><strong>' + escapeHtml(ex.accountName) + '</strong><br><small class="text-muted">' + ex.accountId + '</small></td>' +
                '<td>' + subAccountsHtml + '</td>' +
                '<td>' + typeBadge + '</td>' +
                '<td style="max-width: 220px;">' + detailsHtml + '</td>' +
                '<td><span style="font-size: 0.8rem;">' + escapeHtml(ex.appliedBy || '-') + '</span></td>' +
                '<td><span style="font-size: 0.8rem;">' + dateOnly + '</span></td>' +
                '<td>' + statusBadge + '</td>' +
                '<td>' +
                    '<div class="action-menu-container">' +
                        '<button class="action-menu-btn" onclick="toggleUrlExemptionActionMenu(this, \'' + ex.id + '\', event)"><i class="fas fa-ellipsis-v"></i></button>' +
                        '<div class="action-menu-dropdown" id="url-exemption-menu-' + ex.id + '">' +
                            '<a href="#" onclick="viewUrlExemptionDetails(\'' + ex.id + '\'); return false;"><i class="fas fa-eye"></i> View Details</a>' +
                            '<a href="#" onclick="editUrlExemption(\'' + ex.id + '\'); return false;"><i class="fas fa-edit"></i> Edit</a>' +
                            '<a href="#" onclick="toggleUrlExemptionStatus(\'' + ex.id + '\'); return false;"><i class="fas fa-toggle-on"></i> ' + (ex.status === 'active' ? 'Disable' : 'Enable') + '</a>' +
                            '<div class="dropdown-divider"></div>' +
                            '<a href="#" class="text-danger" onclick="deleteUrlExemption(\'' + ex.id + '\'); return false;"><i class="fas fa-trash"></i> Delete</a>' +
                        '</div>' +
                    '</div>' +
                '</td>' +
                '</tr>';
        }).join('');
    }
    
    function populateUrlExemptionsAccountFilter() {
        var accountSelect = document.getElementById('url-exemptions-filter-account');
        var subaccountSelect = document.getElementById('url-exemptions-filter-subaccount');
        
        if (!accountSelect) return;
        
        // Only populate accounts once
        if (accountSelect.options.length <= 1) {
            var uniqueAccounts = [];
            mockData.urlExemptions.forEach(function(ex) {
                if (!uniqueAccounts.find(function(a) { return a.id === ex.accountId; })) {
                    uniqueAccounts.push({ id: ex.accountId, name: ex.accountName });
                }
            });
            
            uniqueAccounts.sort(function(a, b) { return a.name.localeCompare(b.name); });
            uniqueAccounts.forEach(function(acc) {
                var opt = document.createElement('option');
                opt.value = acc.id;
                opt.textContent = acc.name;
                accountSelect.appendChild(opt);
            });
        }
        
        // Populate sub-accounts based on selected account
        if (subaccountSelect) {
            var selectedAccountId = accountSelect.value;
            subaccountSelect.innerHTML = '<option value="">All Sub-accounts</option>';
            
            if (selectedAccountId) {
                var uniqueSubaccounts = [];
                mockData.urlExemptions.forEach(function(ex) {
                    if (ex.accountId === selectedAccountId && ex.subAccounts) {
                        ex.subAccounts.forEach(function(sub) {
                            if (!uniqueSubaccounts.find(function(s) { return s.id === sub.id; })) {
                                uniqueSubaccounts.push(sub);
                            }
                        });
                    }
                });
                
                uniqueSubaccounts.sort(function(a, b) { return a.name.localeCompare(b.name); });
                uniqueSubaccounts.forEach(function(sub) {
                    var opt = document.createElement('option');
                    opt.value = sub.id;
                    opt.textContent = sub.name;
                    subaccountSelect.appendChild(opt);
                });
            }
        }
    }
    
    function parseExemptionDate(dateStr) {
        if (!dateStr) return null;
        var parts = dateStr.split(' ')[0].split('-');
        if (parts.length !== 3) return null;
        return parts[2] + '-' + parts[1] + '-' + parts[0];
    }
    
    function sortUrlExemptionsTable(column) {
        if (urlExemptionsSortColumn === column) {
            urlExemptionsSortDirection = urlExemptionsSortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            urlExemptionsSortColumn = column;
            urlExemptionsSortDirection = 'asc';
        }
        renderUrlExemptionsTab();
    }
    
    function viewUrlExemptionDetails(exemptionId) {
        var ex = mockData.urlExemptions.find(function(e) { return e.id === exemptionId; });
        if (!ex) return;
        
        var detailsHtml = '<div class="p-3">';
        detailsHtml += '<p><strong>Account:</strong> ' + escapeHtml(ex.accountName) + ' (' + ex.accountId + ')</p>';
        detailsHtml += '<p><strong>Type:</strong> ' + ex.type + '</p>';
        detailsHtml += '<p><strong>Applied By:</strong> ' + escapeHtml(ex.appliedBy || '-') + '</p>';
        detailsHtml += '<p><strong>Applied At:</strong> ' + (ex.appliedAt || '-') + '</p>';
        detailsHtml += '<p><strong>Status:</strong> ' + ex.status + '</p>';
        if (ex.reason) {
            detailsHtml += '<p><strong>Reason:</strong> ' + escapeHtml(ex.reason) + '</p>';
        }
        if (ex.type === 'domains' && ex.domains) {
            detailsHtml += '<p><strong>Domains:</strong></p><div class="d-flex flex-wrap gap-1">';
            ex.domains.forEach(function(d) {
                detailsHtml += '<span class="badge bg-success bg-opacity-25 text-success">' + escapeHtml(d) + '</span>';
            });
            detailsHtml += '</div>';
        }
        if (ex.type === 'url_rule' && ex.exemptRules) {
            detailsHtml += '<p><strong>Exempt Rules:</strong></p><div class="d-flex flex-wrap gap-1">';
            ex.exemptRules.forEach(function(ruleId) {
                var rule = mockData.urlRules.find(function(r) { return r.id === ruleId; });
                detailsHtml += '<span class="badge bg-secondary">' + escapeHtml(rule ? rule.pattern : ruleId) + '</span>';
            });
            detailsHtml += '</div>';
        }
        detailsHtml += '</div>';
        
        showToast('View details for ' + ex.id + ' - ' + ex.accountName, 'info');
    }
    
    function toggleUrlExemptionActionMenu(btn, exemptionId, event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }
        // Close all other menus first
        document.querySelectorAll('.action-menu-dropdown').forEach(function(menu) {
            if (menu.id !== 'url-exemption-menu-' + exemptionId) {
                menu.classList.remove('show');
            }
        });
        // Toggle this menu
        var menu = document.getElementById('url-exemption-menu-' + exemptionId);
        if (menu) {
            menu.classList.toggle('show');
        }
    }
    
    function showAddUrlExemptionModal() {
        document.getElementById('url-exemption-modal-title').textContent = 'Add URL Exemption';
        document.getElementById('url-exemption-form').reset();
        document.getElementById('url-exemption-id').value = '';
        document.getElementById('url-exemption-save-btn-text').textContent = 'Save Exemption';
        document.getElementById('url-exemption-type-domain-age').checked = true;
        document.getElementById('url-exemption-account-search').value = '';
        document.getElementById('url-exemption-account').value = '';
        document.getElementById('url-exemption-reason').value = '';
        
        populateUrlRulesChecklist();
        toggleUrlExemptionType();
        
        var modal = new bootstrap.Modal(document.getElementById('urlExemptionModal'));
        modal.show();
    }
    
    function editUrlExemption(exemptionId) {
        var ex = mockData.urlExemptions.find(function(e) { return e.id === exemptionId; });
        if (!ex) return;
        
        document.getElementById('url-exemption-modal-title').textContent = 'Edit URL Exemption';
        document.getElementById('url-exemption-save-btn-text').textContent = 'Update Exemption';
        document.getElementById('url-exemption-id').value = ex.id;
        document.getElementById('url-exemption-account-search').value = ex.accountName;
        document.getElementById('url-exemption-account').value = ex.accountId;
        document.getElementById('url-exemption-reason').value = ex.reason || '';
        
        if (ex.type === 'url_rule') {
            document.getElementById('url-exemption-type-url-rule').checked = true;
        } else {
            document.getElementById('url-exemption-type-domain-age').checked = true;
        }
        
        populateUrlRulesChecklist();
        toggleUrlExemptionType();
        
        // Check selected rules
        if (ex.exemptRules && ex.exemptRules.length > 0) {
            ex.exemptRules.forEach(function(ruleId) {
                var cb = document.getElementById('url-rule-check-' + ruleId);
                if (cb) cb.checked = true;
            });
        }
        
        var modal = new bootstrap.Modal(document.getElementById('urlExemptionModal'));
        modal.show();
    }
    
    function toggleUrlExemptionType() {
        var type = document.querySelector('input[name="url-exemption-type"]:checked').value;
        document.getElementById('url-exemption-rules-group').style.display = type === 'url_rule' ? 'block' : 'none';
    }
    
    function populateUrlRulesChecklist() {
        var container = document.getElementById('url-exemption-rules-list');
        if (!container) return;
        
        var rules = mockData.urlRules.filter(function(r) { return r.status === 'active'; });
        
        if (rules.length === 0) {
            container.innerHTML = '<small class="text-muted">No active URL rules available</small>';
            return;
        }
        
        container.innerHTML = rules.map(function(rule) {
            return '<div class="form-check">' +
                '<input class="form-check-input url-rule-checkbox" type="checkbox" value="' + rule.id + '" id="url-rule-check-' + rule.id + '">' +
                '<label class="form-check-label" for="url-rule-check-' + rule.id + '" style="font-size: 0.85rem;">' +
                    '<code style="background: #e9ecef; padding: 0 4px; border-radius: 2px;">' + escapeHtml(rule.pattern) + '</code>' +
                    '<span class="badge bg-light text-secondary ms-2" style="font-size: 0.65rem;">' + rule.matchType + '</span>' +
                '</label>' +
            '</div>';
        }).join('');
    }
    
    function saveUrlExemption() {
        var accountId = document.getElementById('url-exemption-account').value;
        if (!accountId) {
            showToast('Please select an account', 'error');
            return;
        }
        
        var type = document.querySelector('input[name="url-exemption-type"]:checked').value;
        var account = mockData.accounts.find(function(a) { return a.id === accountId; });
        var exemptionId = document.getElementById('url-exemption-id').value;
        var reason = document.getElementById('url-exemption-reason').value.trim();
        
        // Get before state for audit
        var beforeState = null;
        var existingIdx = mockData.urlExemptions.findIndex(function(e) { return e.id === exemptionId; });
        if (existingIdx !== -1) {
            beforeState = JSON.parse(JSON.stringify(mockData.urlExemptions[existingIdx]));
        }
        
        var exemptionData = {
            id: exemptionId || 'UEX-' + String(mockData.urlExemptions.length + 1).padStart(3, '0'),
            accountId: accountId,
            accountName: account ? account.name : accountId,
            subAccounts: [],
            allSubaccounts: true,
            type: type,
            status: 'active',
            reason: reason,
            appliedBy: currentAdmin.email,
            appliedAt: formatDateTime(new Date())
        };
        
        if (type === 'url_rule') {
            var selectedRules = Array.from(document.querySelectorAll('.url-rule-checkbox:checked')).map(function(cb) {
                return cb.value;
            });
            if (selectedRules.length === 0) {
                showToast('Please select at least one URL rule to exempt from', 'error');
                return;
            }
            exemptionData.exemptRules = selectedRules;
        } else {
            exemptionData.exemptRules = [];
        }
        
        // Determine event type
        var isNew = existingIdx === -1;
        var eventType = type === 'domain_age' 
            ? (isNew ? 'URL_DOMAIN_AGE_EXEMPTION_ADDED' : 'URL_DOMAIN_AGE_EXEMPTION_UPDATED')
            : (isNew ? 'URL_RULE_EXEMPTION_ADDED' : 'URL_RULE_EXEMPTION_UPDATED');
        
        // Save
        if (existingIdx !== -1) {
            mockData.urlExemptions[existingIdx] = exemptionData;
        } else {
            mockData.urlExemptions.push(exemptionData);
        }
        
        // Audit log
        logAuditEvent(eventType, {
            entityType: 'url_exemption',
            exemptionId: exemptionData.id,
            adminUserId: currentAdmin.id,
            adminUser: currentAdmin.email,
            timestamp: new Date().toISOString(),
            sourceIp: getClientIP(),
            accountId: exemptionData.accountId,
            accountName: exemptionData.accountName,
            type: exemptionData.type,
            ruleIdsAffected: exemptionData.exemptRules,
            reason: reason,
            before: beforeState,
            after: exemptionData,
            affectedScope: {
                type: exemptionData.allSubaccounts ? 'account_all_subaccounts' : 'account_specific_subaccounts',
                accountId: exemptionData.accountId,
                accountName: exemptionData.accountName,
                subAccountsAffected: exemptionData.subAccounts
            }
        });
        
        bootstrap.Modal.getInstance(document.getElementById('urlExemptionModal')).hide();
        renderUrlExemptionsTab();
        showToast('URL exemption ' + (isNew ? 'added' : 'updated') + ' successfully', 'success');
    }
    
    function filterUrlExemptionAccounts() {
        var search = document.getElementById('url-exemption-account-search').value.toLowerCase();
        var dropdown = document.getElementById('url-exemption-account-dropdown');
        
        if (search.length < 2) {
            dropdown.classList.remove('show');
            return;
        }
        
        var filtered = mockData.accounts.filter(function(a) {
            return a.name.toLowerCase().indexOf(search) !== -1 || a.id.toLowerCase().indexOf(search) !== -1;
        }).slice(0, 10);
        
        if (filtered.length === 0) {
            dropdown.innerHTML = '<div class="dropdown-item text-muted">No accounts found</div>';
        } else {
            dropdown.innerHTML = filtered.map(function(a) {
                return '<div class="dropdown-item" style="cursor: pointer;" onclick="selectUrlExemptionAccount(\'' + a.id + '\', \'' + escapeHtml(a.name).replace(/'/g, "\\'") + '\')">' +
                    '<div style="font-weight: 500;">' + escapeHtml(a.name) + '</div>' +
                    '<small class="text-muted">' + a.id + '</small>' +
                '</div>';
            }).join('');
        }
        
        dropdown.classList.add('show');
    }
    
    function selectUrlExemptionAccount(accountId, accountName) {
        document.getElementById('url-exemption-account').value = accountId;
        document.getElementById('url-exemption-account-search').value = accountName;
        document.getElementById('url-exemption-account-dropdown').classList.remove('show');
    }
    
    function toggleUrlExemptionStatus(exemptionId) {
        var ex = mockData.urlExemptions.find(function(e) { return e.id === exemptionId; });
        if (!ex) return;
        
        var beforeStatus = ex.status;
        ex.status = ex.status === 'active' ? 'disabled' : 'active';
        
        var exemptionEventType = ex.type === 'domain_age' ? 'URL_DOMAIN_AGE_EXEMPTION_STATUS_CHANGED' : 'URL_RULE_EXEMPTION_STATUS_CHANGED';
        logAuditEvent(exemptionEventType, {
            entityType: 'url_exemption',
            exemptionId: exemptionId,
            adminUserId: currentAdmin.id,
            adminUser: currentAdmin.email,
            timestamp: new Date().toISOString(),
            sourceIp: getClientIP(),
            before: { status: beforeStatus },
            after: { status: ex.status },
            affectedScope: {
                type: 'account',
                accountId: ex.accountId,
                accountName: ex.accountName,
                subAccountsAffected: ex.subAccounts || []
            }
        });
        
        renderUrlExemptionsTab();
        showSuccessToast('Exemption ' + (ex.status === 'active' ? 'enabled' : 'disabled'));
    }
    
    function deleteUrlExemption(exemptionId) {
        var ex = mockData.urlExemptions.find(function(e) { return e.id === exemptionId; });
        if (!ex) return;
        
        if (confirm('Are you sure you want to delete this exemption for ' + ex.accountName + '?')) {
            var idx = mockData.urlExemptions.findIndex(function(e) { return e.id === exemptionId; });
            if (idx !== -1) {
                var beforeState = JSON.parse(JSON.stringify(mockData.urlExemptions[idx]));
                mockData.urlExemptions.splice(idx, 1);
                
                var deleteEventType = ex.type === 'domain_age' ? 'URL_DOMAIN_AGE_EXEMPTION_REMOVED' : 'URL_RULE_EXEMPTION_REMOVED';
                logAuditEvent(deleteEventType, {
                    entityType: 'url_exemption',
                    exemptionId: exemptionId,
                    adminUserId: currentAdmin.id,
                    adminUser: currentAdmin.email,
                    timestamp: new Date().toISOString(),
                    sourceIp: getClientIP(),
                    before: beforeState,
                    after: null,
                    affectedScope: {
                        type: 'account',
                        accountId: ex.accountId,
                        accountName: ex.accountName,
                        subAccountsAffected: ex.subAccounts || []
                    }
                });
                
                renderUrlExemptionsTab();
                showSuccessToast('Exemption deleted successfully');
            }
        }
    }
    
    function resetUrlExemptionsFilters() {
        var statusEl = document.getElementById('url-exemptions-filter-status');
        var typeEl = document.getElementById('url-exemptions-filter-type');
        var accountEl = document.getElementById('url-exemptions-filter-account');
        var subaccountEl = document.getElementById('url-exemptions-filter-subaccount');
        var dateFromEl = document.getElementById('url-exemptions-filter-date-from');
        var dateToEl = document.getElementById('url-exemptions-filter-date-to');
        var searchEl = document.getElementById('url-exemptions-search');
        
        if (statusEl) statusEl.value = '';
        if (typeEl) typeEl.value = '';
        if (accountEl) accountEl.value = '';
        if (subaccountEl) subaccountEl.value = '';
        if (dateFromEl) dateFromEl.value = '';
        if (dateToEl) dateToEl.value = '';
        if (searchEl) searchEl.value = '';
        
        renderUrlExemptionsTab();
    }
    
    function applyUrlExemptionsFilters() {
        renderUrlExemptionsTab();
    }
    
    function toggleUrlActionMenu(btn, ruleId, event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }
        // Close all other menus first
        document.querySelectorAll('.action-menu-dropdown').forEach(function(menu) {
            if (menu.id !== 'url-menu-' + ruleId) {
                menu.classList.remove('show');
            }
        });
        // Toggle this menu
        var menu = document.getElementById('url-menu-' + ruleId);
        if (menu) {
            menu.classList.toggle('show');
        }
    }
    
    function showAddUrlRuleModal() {
        document.getElementById('url-rule-modal-title').innerHTML = '<i class="fas fa-link me-2"></i>Add URL Rule';
        document.getElementById('url-rule-form').reset();
        document.getElementById('url-rule-id').value = '';
        document.getElementById('url-rule-name').value = '';
        document.getElementById('url-match-type').value = 'exact';
        document.getElementById('url-rule-type').value = 'block';
        document.getElementById('url-rule-enabled').checked = true;
        document.getElementById('url-apply-domain-age').checked = true;
        document.getElementById('url-rule-save-btn-text').textContent = 'Save Rule';
        updateUrlPatternLabel();
        clearUrlRuleErrors();
        resetUrlRuleTestSection();
        var modal = new bootstrap.Modal(document.getElementById('urlRuleModal'));
        modal.show();
    }
    
    function editUrlRule(ruleId) {
        var rule = mockData.urlRules.find(function(r) { return r.id === ruleId; });
        if (!rule) return;
        
        document.getElementById('url-rule-modal-title').innerHTML = '<i class="fas fa-link me-2"></i>Edit URL Rule';
        document.getElementById('url-rule-id').value = rule.id;
        document.getElementById('url-rule-name').value = rule.name || '';
        document.getElementById('url-pattern').value = rule.pattern;
        document.getElementById('url-match-type').value = rule.matchType;
        document.getElementById('url-rule-type').value = rule.ruleType;
        document.getElementById('url-rule-enabled').checked = rule.status === 'active';
        document.getElementById('url-apply-domain-age').checked = rule.applyDomainAge;
        document.getElementById('url-rule-save-btn-text').textContent = 'Update Rule';
        updateUrlPatternLabel();
        clearUrlRuleErrors();
        resetUrlRuleTestSection();
        
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
            'exact': { label: 'Pattern', placeholder: 'example.com', help: 'Enter the exact domain to match (e.g., example.com)' },
            'wildcard': { label: 'Pattern', placeholder: '*.example.com', help: 'Use * for wildcard matching (e.g., *.example.com matches all subdomains)' },
            'regex': { label: 'Pattern', placeholder: 'phish\\d+\\.com', help: 'Enter a valid regular expression pattern' }
        };
        
        label.innerHTML = config[matchType].label + ' <span class="text-danger">*</span>';
        input.placeholder = config[matchType].placeholder;
        helpText.textContent = config[matchType].help;
    }
    
    function validateUrlRuleForm() {
        clearUrlRuleErrors();
        var isValid = true;
        
        var name = document.getElementById('url-rule-name').value.trim();
        if (!name) {
            showUrlFieldError('url-rule-name', 'Rule name is required');
            isValid = false;
        }
        
        var pattern = document.getElementById('url-pattern').value.trim();
        if (!pattern) {
            showUrlFieldError('url-pattern', 'Pattern is required');
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
        var existingError = field.parentNode.querySelector('.invalid-feedback');
        if (existingError) existingError.remove();
        var errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback';
        errorDiv.style.display = 'block';
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
    
    function resetUrlRuleTestSection() {
        document.getElementById('url-rule-test-input').value = '';
        document.getElementById('url-rule-test-result').style.display = 'none';
        var collapseEl = document.getElementById('url-rule-test-collapse');
        if (collapseEl.classList.contains('show')) {
            var bsCollapse = bootstrap.Collapse.getInstance(collapseEl);
            if (bsCollapse) bsCollapse.hide();
        }
    }
    
    // URL matching logic - same as enforcement engine
    function extractHostname(urlString) {
        try {
            var url = urlString.trim();
            if (!url.match(/^https?:\/\//i)) {
                url = 'https://' + url;
            }
            var parsed = new URL(url);
            return parsed.hostname.toLowerCase();
        } catch (e) {
            var match = urlString.match(/^(?:https?:\/\/)?([^\/\?#:]+)/i);
            return match ? match[1].toLowerCase() : null;
        }
    }
    
    function matchUrlPattern(hostname, pattern, matchType) {
        if (!hostname || !pattern) return false;
        hostname = hostname.toLowerCase();
        pattern = pattern.toLowerCase();
        
        switch (matchType) {
            case 'exact':
                return hostname === pattern;
            case 'wildcard':
                var regexPattern = pattern
                    .replace(/\./g, '\\.')
                    .replace(/\*/g, '.*');
                try {
                    return new RegExp('^' + regexPattern + '$').test(hostname);
                } catch (e) {
                    return false;
                }
            case 'regex':
                try {
                    return new RegExp(pattern, 'i').test(hostname);
                } catch (e) {
                    return false;
                }
            default:
                return false;
        }
    }
    
    function runUrlRuleTest() {
        var testUrl = document.getElementById('url-rule-test-input').value.trim();
        var resultBox = document.getElementById('url-rule-test-result');
        
        if (!testUrl) {
            showToast('Please enter a URL to test', 'warning');
            return;
        }
        
        var hostname = extractHostname(testUrl);
        if (!hostname) {
            resultBox.style.display = 'block';
            document.getElementById('url-rule-test-result-badge').className = 'badge bg-danger';
            document.getElementById('url-rule-test-result-badge').textContent = 'INVALID URL';
            document.getElementById('url-rule-test-result-action').textContent = '';
            document.getElementById('url-rule-test-hostname').textContent = 'Could not extract hostname';
            document.getElementById('url-rule-test-matched-rule').textContent = '-';
            return;
        }
        
        var currentPattern = document.getElementById('url-pattern').value.trim();
        var currentMatchType = document.getElementById('url-match-type').value;
        var currentRuleType = document.getElementById('url-rule-type').value;
        var currentRuleName = document.getElementById('url-rule-name').value.trim() || 'Current rule';
        
        var isMatch = matchUrlPattern(hostname, currentPattern, currentMatchType);
        
        resultBox.style.display = 'block';
        var badge = document.getElementById('url-rule-test-result-badge');
        var actionSpan = document.getElementById('url-rule-test-result-action');
        
        if (isMatch) {
            badge.className = 'badge bg-success';
            badge.textContent = 'MATCHED';
            var actionText = currentRuleType === 'block' ? 'Will be BLOCKED' : 'Will be QUARANTINED';
            actionSpan.textContent = '→ ' + actionText;
            actionSpan.style.color = currentRuleType === 'block' ? '#dc3545' : '#fd7e14';
            document.getElementById('url-rule-test-matched-rule').innerHTML = '<code>' + currentPattern + '</code> (' + currentMatchType + ')';
        } else {
            badge.className = 'badge bg-secondary';
            badge.textContent = 'NOT MATCHED';
            actionSpan.textContent = '→ No action (passes this rule)';
            actionSpan.style.color = '#6c757d';
            document.getElementById('url-rule-test-matched-rule').textContent = 'None';
        }
        
        document.getElementById('url-rule-test-hostname').textContent = hostname;
    }
    
    function confirmSaveUrlRule() {
        if (!validateUrlRuleForm()) return;
        
        var ruleId = document.getElementById('url-rule-id').value;
        var isEdit = !!ruleId;
        var name = document.getElementById('url-rule-name').value.trim();
        var pattern = document.getElementById('url-pattern').value.trim();
        var matchType = document.getElementById('url-match-type').value;
        var ruleType = document.getElementById('url-rule-type').value;
        var enabled = document.getElementById('url-rule-enabled').checked;
        var applyDomainAge = document.getElementById('url-apply-domain-age').checked;
        
        var matchTypeLabels = { 'exact': 'Exact Domain', 'wildcard': 'Wildcard Domain', 'regex': 'Regex' };
        var ruleTypeLabels = { 'block': 'Block', 'flag': 'Flag-to-Quarantine' };
        
        var summaryHtml = '<tr><td style="padding: 0.35rem 0.5rem; font-weight: 600; width: 40%;">Rule Name</td><td style="padding: 0.35rem 0.5rem;">' + escapeHtml(name) + '</td></tr>' +
            '<tr><td style="padding: 0.35rem 0.5rem; font-weight: 600;">Match Type</td><td style="padding: 0.35rem 0.5rem;">' + matchTypeLabels[matchType] + '</td></tr>' +
            '<tr><td style="padding: 0.35rem 0.5rem; font-weight: 600;">Pattern</td><td style="padding: 0.35rem 0.5rem;"><code>' + escapeHtml(pattern) + '</code></td></tr>' +
            '<tr><td style="padding: 0.35rem 0.5rem; font-weight: 600;">Rule Type</td><td style="padding: 0.35rem 0.5rem;">' + ruleTypeLabels[ruleType] + '</td></tr>' +
            '<tr><td style="padding: 0.35rem 0.5rem; font-weight: 600;">Status</td><td style="padding: 0.35rem 0.5rem;"><span class="badge bg-' + (enabled ? 'success' : 'secondary') + '">' + (enabled ? 'Enabled' : 'Disabled') + '</span></td></tr>' +
            '<tr><td style="padding: 0.35rem 0.5rem; font-weight: 600;">Domain Age Check</td><td style="padding: 0.35rem 0.5rem;">' + (applyDomainAge ? 'Applied' : 'Not applied') + '</td></tr>';
        
        document.getElementById('url-rule-confirm-summary').innerHTML = summaryHtml;
        
        var confirmModal = new bootstrap.Modal(document.getElementById('urlRuleConfirmModal'));
        confirmModal.show();
    }
    
    function executeSaveUrlRule() {
        var ruleId = document.getElementById('url-rule-id').value;
        var ruleData = {
            name: document.getElementById('url-rule-name').value.trim(),
            pattern: document.getElementById('url-pattern').value.trim(),
            matchType: document.getElementById('url-match-type').value,
            ruleType: document.getElementById('url-rule-type').value,
            applyDomainAge: document.getElementById('url-apply-domain-age').checked,
            status: document.getElementById('url-rule-enabled').checked ? 'active' : 'disabled',
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
            entityType: 'url_rule',
            ruleId: ruleId || ruleData.id,
            name: ruleData.name,
            pattern: ruleData.pattern,
            matchType: ruleData.matchType,
            ruleType: ruleData.ruleType,
            before: beforeState,
            after: ruleData,
            adminUserId: currentAdmin.id,
            adminUser: currentAdmin.email,
            timestamp: new Date().toISOString(),
            sourceIp: getClientIP(),
            affectedScope: { type: 'global', description: 'All messages containing matching URLs' }
        });
        
        bootstrap.Modal.getInstance(document.getElementById('urlRuleConfirmModal')).hide();
        bootstrap.Modal.getInstance(document.getElementById('urlRuleModal')).hide();
        renderUrlTab();
        showToast(ruleId ? 'Rule updated successfully' : 'Rule added successfully', 'success');
    }
    
    function toggleUrlRuleStatus(ruleId) {
        var rule = mockData.urlRules.find(function(r) { return r.id === ruleId; });
        if (!rule) return;
        
        var beforeStatus = rule.status;
        rule.status = rule.status === 'active' ? 'disabled' : 'active';
        rule.updatedAt = formatDateTime(new Date());
        
        var statusEventType = rule.status === 'active' ? 'URL_RULE_ENABLED' : 'URL_RULE_DISABLED';
        logAuditEvent(statusEventType, {
            entityType: 'url_rule',
            ruleId: ruleId,
            ruleName: rule.name,
            pattern: rule.pattern,
            before: { status: beforeStatus },
            after: { status: rule.status },
            adminUserId: currentAdmin.id,
            adminUser: currentAdmin.email,
            timestamp: new Date().toISOString(),
            sourceIp: getClientIP(),
            affectedScope: { type: 'global', description: 'All messages containing matching URLs' }
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
            entityType: 'url_rule',
            ruleId: ruleId,
            ruleName: deletedRule.name,
            pattern: deletedRule.pattern,
            before: deletedRule,
            after: null,
            adminUserId: currentAdmin.id,
            adminUser: currentAdmin.email,
            timestamp: new Date().toISOString(),
            sourceIp: getClientIP(),
            affectedScope: { type: 'global', description: 'All messages containing matching URLs' }
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
    
    function toggleUrlControlsFilterPanel() {
        var panel = document.getElementById('url-controls-filter-panel');
        var btn = document.getElementById('url-controls-filter-btn');
        if (panel && btn) {
            if (panel.style.display === 'none') {
                panel.style.display = 'block';
                btn.classList.add('active');
                updateUrlControlsFilterPanelContext();
            } else {
                panel.style.display = 'none';
                btn.classList.remove('active');
            }
        }
    }
    
    function updateUrlControlsFilterPanelContext() {
        // Show/hide filter groups based on active sub-tab
        var urlRulesTab = document.getElementById('url-rules-tab');
        var urlExemptionsTab = document.getElementById('url-exemptions-tab');
        
        var typeGroup = document.querySelector('#url-controls-filter-panel .filter-group:nth-child(2)');
        var actionGroup = document.querySelector('#url-controls-filter-panel .filter-group:nth-child(3)');
        var accountGroup = document.querySelector('#url-controls-filter-panel .filter-group:nth-child(4)');
        
        if (urlRulesTab && urlRulesTab.classList.contains('active')) {
            if (typeGroup) typeGroup.style.display = 'block';
            if (actionGroup) actionGroup.style.display = 'block';
            if (accountGroup) accountGroup.style.display = 'none';
        } else if (urlExemptionsTab && urlExemptionsTab.classList.contains('active')) {
            if (typeGroup) typeGroup.style.display = 'none';
            if (actionGroup) actionGroup.style.display = 'none';
            if (accountGroup) {
                accountGroup.style.display = 'block';
                populateUrlControlsAccountFilter();
            }
        } else {
            // Domain Age tab - minimal filters
            if (typeGroup) typeGroup.style.display = 'none';
            if (actionGroup) actionGroup.style.display = 'none';
            if (accountGroup) accountGroup.style.display = 'none';
        }
    }
    
    function populateUrlControlsAccountFilter() {
        var accountSelect = document.getElementById('url-controls-filter-account');
        if (!accountSelect || accountSelect.options.length > 1) return;
        
        var uniqueAccounts = [];
        mockData.urlExemptions.forEach(function(ex) {
            if (!uniqueAccounts.find(function(a) { return a.id === ex.accountId; })) {
                uniqueAccounts.push({ id: ex.accountId, name: ex.accountName });
            }
        });
        
        uniqueAccounts.sort(function(a, b) { return a.name.localeCompare(b.name); });
        uniqueAccounts.forEach(function(acc) {
            var opt = document.createElement('option');
            opt.value = acc.id;
            opt.textContent = acc.name;
            accountSelect.appendChild(opt);
        });
    }
    
    function applyUrlControlsFilters() {
        var statusFilter = document.getElementById('url-controls-filter-status').value;
        var typeFilter = document.getElementById('url-controls-filter-type').value;
        var actionFilter = document.getElementById('url-controls-filter-action').value;
        var accountFilter = document.getElementById('url-controls-filter-account').value;
        
        var urlRulesTab = document.getElementById('url-rules-tab');
        var urlExemptionsTab = document.getElementById('url-exemptions-tab');
        
        var count = 0;
        if (statusFilter) count++;
        
        if (urlRulesTab && urlRulesTab.classList.contains('active')) {
            // Apply to URL Rule Library filters
            document.getElementById('url-filter-status').value = statusFilter;
            document.getElementById('url-filter-matchtype').value = typeFilter;
            document.getElementById('url-filter-ruletype').value = actionFilter;
            if (typeFilter) count++;
            if (actionFilter) count++;
            renderUrlTab();
        } else if (urlExemptionsTab && urlExemptionsTab.classList.contains('active')) {
            // Apply to Exemptions tab filters
            var exemptionsStatusFilter = document.getElementById('url-exemptions-filter-status');
            var exemptionsAccountFilter = document.getElementById('url-exemptions-filter-account');
            if (exemptionsStatusFilter) exemptionsStatusFilter.value = statusFilter;
            if (exemptionsAccountFilter) exemptionsAccountFilter.value = accountFilter;
            if (accountFilter) count++;
            renderUrlExemptionsTab();
        }
        
        var badge = document.getElementById('url-controls-filter-count');
        if (badge) {
            if (count > 0) {
                badge.textContent = count;
                badge.style.display = 'inline';
            } else {
                badge.style.display = 'none';
            }
        }
        
        toggleUrlControlsFilterPanel();
    }
    
    function resetUrlControlsFilters() {
        document.getElementById('url-controls-filter-status').value = '';
        document.getElementById('url-controls-filter-type').value = '';
        document.getElementById('url-controls-filter-action').value = '';
        document.getElementById('url-controls-filter-account').value = '';
        document.getElementById('url-controls-search').value = '';
        
        var badge = document.getElementById('url-controls-filter-count');
        if (badge) badge.style.display = 'none';
        
        // Reset based on active tab
        var urlRulesTab = document.getElementById('url-rules-tab');
        var urlExemptionsTab = document.getElementById('url-exemptions-tab');
        
        if (urlRulesTab && urlRulesTab.classList.contains('active')) {
            resetUrlFilters();
        } else if (urlExemptionsTab && urlExemptionsTab.classList.contains('active')) {
            resetUrlExemptionsFilters();
        }
        
        toggleUrlControlsFilterPanel();
    }
    
    var domainAgeOriginalSettings = null;
    
    function initDomainAgeSettings() {
        domainAgeOriginalSettings = JSON.parse(JSON.stringify(mockData.domainAgeSettings));
        
        document.getElementById('domain-age-enabled').checked = mockData.domainAgeSettings.enabled;
        document.getElementById('domain-age-hours').value = mockData.domainAgeSettings.minAgeHours || 72;
        document.getElementById('domain-age-action').value = mockData.domainAgeSettings.action || 'block';
        toggleDomainAgeFields();
        
        updateDomainAgeStatusBadge();
        updateDomainAgeMeta();
        renderDomainAgeExemptionPreviews();
        
        // Collapse icon rotation
        var collapseEl = document.getElementById('domain-age-collapse');
        if (collapseEl) {
            collapseEl.addEventListener('show.bs.collapse', function() {
                document.getElementById('domain-age-collapse-icon').style.transform = 'rotate(180deg)';
            });
            collapseEl.addEventListener('hide.bs.collapse', function() {
                document.getElementById('domain-age-collapse-icon').style.transform = 'rotate(0deg)';
            });
        }
    }
    
    function updateDomainAgeStatusBadge() {
        var badge = document.getElementById('domain-age-status-badge');
        if (!badge) return;
        
        var enabled = document.getElementById('domain-age-enabled').checked;
        if (enabled) {
            badge.textContent = 'Enabled';
            badge.style.background = '#198754';
        } else {
            badge.textContent = 'Disabled';
            badge.style.background = '#dc3545';
        }
    }
    
    function toggleDomainAgeFields() {
        var enabled = document.getElementById('domain-age-enabled').checked;
        document.getElementById('domain-age-hours').disabled = !enabled;
        document.getElementById('domain-age-action').disabled = !enabled;
    }
    
    function cancelDomainAgeSettings() {
        if (!domainAgeOriginalSettings) return;
        
        document.getElementById('domain-age-enabled').checked = domainAgeOriginalSettings.enabled;
        document.getElementById('domain-age-hours').value = domainAgeOriginalSettings.minAgeHours || 72;
        document.getElementById('domain-age-action').value = domainAgeOriginalSettings.action || 'block';
        toggleDomainAgeFields();
        
        updateDomainAgeStatusBadge();
        
        var collapseEl = bootstrap.Collapse.getInstance(document.getElementById('domain-age-collapse'));
        if (collapseEl) collapseEl.hide();
        
        showToast('Changes cancelled', 'info');
    }
    
    function confirmSaveDomainAgeSettings() {
        var enabled = document.getElementById('domain-age-enabled').checked;
        var hours = parseInt(document.getElementById('domain-age-hours').value) || 72;
        var action = document.getElementById('domain-age-action').value;
        
        var diffHtml = '';
        
        // Enabled
        var beforeEnabled = domainAgeOriginalSettings.enabled ? 'Enabled' : 'Disabled';
        var afterEnabled = enabled ? 'Enabled' : 'Disabled';
        var enabledChanged = domainAgeOriginalSettings.enabled !== enabled;
        diffHtml += '<tr' + (enabledChanged ? ' style="background: #fff3cd;"' : '') + '>' +
            '<td style="padding: 0.35rem;">Enforcement</td>' +
            '<td style="padding: 0.35rem;">' + beforeEnabled + '</td>' +
            '<td style="padding: 0.35rem;">' + afterEnabled + '</td>' +
        '</tr>';
        
        // Hours
        var beforeHours = domainAgeOriginalSettings.minAgeHours || 72;
        var hoursChanged = beforeHours !== hours;
        diffHtml += '<tr' + (hoursChanged ? ' style="background: #fff3cd;"' : '') + '>' +
            '<td style="padding: 0.35rem;">Threshold</td>' +
            '<td style="padding: 0.35rem;">' + beforeHours + ' hrs</td>' +
            '<td style="padding: 0.35rem;">' + hours + ' hrs</td>' +
        '</tr>';
        
        // Action
        var beforeAction = domainAgeOriginalSettings.action === 'flag' ? 'Flag to Quarantine' : 'Block';
        var afterAction = action === 'flag' ? 'Flag to Quarantine' : 'Block';
        var actionChanged = domainAgeOriginalSettings.action !== action;
        diffHtml += '<tr' + (actionChanged ? ' style="background: #fff3cd;"' : '') + '>' +
            '<td style="padding: 0.35rem;">Action</td>' +
            '<td style="padding: 0.35rem;">' + beforeAction + '</td>' +
            '<td style="padding: 0.35rem;">' + afterAction + '</td>' +
        '</tr>';
        
        document.getElementById('domain-age-confirm-diff').innerHTML = diffHtml;
        
        var modal = new bootstrap.Modal(document.getElementById('domainAgeConfirmModal'));
        modal.show();
    }
    
    function executeSaveDomainAgeSettings() {
        var enabled = document.getElementById('domain-age-enabled').checked;
        var hours = parseInt(document.getElementById('domain-age-hours').value) || 72;
        var action = document.getElementById('domain-age-action').value;
        
        var beforeSettings = JSON.parse(JSON.stringify(mockData.domainAgeSettings));
        
        // Simulate API call with potential failure (for demo - always succeeds in mock mode)
        var simulateFailure = false; // Set to true to test error handling
        
        if (simulateFailure) {
            bootstrap.Modal.getInstance(document.getElementById('domainAgeConfirmModal')).hide();
            showDomainAgeSaveError('Failed to save settings. Please check your connection and try again.');
            return;
        }
        
        // Update settings with timestamp and user
        var now = new Date();
        var timestamp = formatDateTime(now);
        
        mockData.domainAgeSettings.enabled = enabled;
        mockData.domainAgeSettings.minAgeHours = hours;
        mockData.domainAgeSettings.action = action;
        mockData.domainAgeSettings.updatedAt = timestamp;
        mockData.domainAgeSettings.updatedBy = currentAdmin.email;
        
        domainAgeOriginalSettings = JSON.parse(JSON.stringify(mockData.domainAgeSettings));
        
        logAuditEvent('URL_DOMAIN_AGE_DEFAULT_UPDATED', {
            entityType: 'domain_age_settings',
            before: beforeSettings,
            after: mockData.domainAgeSettings,
            adminUserId: currentAdmin.id,
            adminUser: currentAdmin.email,
            timestamp: now.toISOString(),
            sourceIp: getClientIP(),
            affectedScope: { type: 'global', description: 'All accounts and sub-accounts' }
        });
        
        bootstrap.Modal.getInstance(document.getElementById('domainAgeConfirmModal')).hide();
        updateDomainAgeStatusBadge();
        updateDomainAgeMeta();
        hideDomainAgeSaveError();
        
        var collapseEl = bootstrap.Collapse.getInstance(document.getElementById('domain-age-collapse'));
        if (collapseEl) collapseEl.hide();
        
        showToast('Domain age settings saved successfully', 'success');
    }
    
    function updateDomainAgeMeta() {
        var updatedAtEl = document.getElementById('domain-age-updated-at');
        var updatedByEl = document.getElementById('domain-age-updated-by');
        
        if (updatedAtEl) {
            updatedAtEl.textContent = mockData.domainAgeSettings.updatedAt || '-';
        }
        if (updatedByEl) {
            updatedByEl.textContent = mockData.domainAgeSettings.updatedBy || '-';
        }
    }
    
    function showDomainAgeSaveError(message) {
        var errorContainer = document.getElementById('domain-age-error-alert');
        if (!errorContainer) {
            // Create error alert if it doesn't exist
            var cardBody = document.querySelector('#domain-age-collapse .card-body');
            if (cardBody) {
                var alertHtml = '<div id="domain-age-error-alert" class="alert alert-danger d-flex align-items-center justify-content-between mb-3" role="alert" style="font-size: 0.85rem;">' +
                    '<div><i class="fas fa-exclamation-circle me-2"></i><span id="domain-age-error-message"></span></div>' +
                    '<button class="btn btn-sm btn-outline-danger" onclick="retryDomainAgeSave()"><i class="fas fa-redo me-1"></i> Retry</button>' +
                    '</div>';
                cardBody.insertAdjacentHTML('afterbegin', alertHtml);
                errorContainer = document.getElementById('domain-age-error-alert');
            }
        }
        
        if (errorContainer) {
            errorContainer.style.display = 'flex';
            document.getElementById('domain-age-error-message').textContent = message;
        }
    }
    
    function hideDomainAgeSaveError() {
        var errorContainer = document.getElementById('domain-age-error-alert');
        if (errorContainer) {
            errorContainer.style.display = 'none';
        }
    }
    
    function retryDomainAgeSave() {
        hideDomainAgeSaveError();
        confirmSaveDomainAgeSettings();
    }
    
    function saveDomainAgeSettings() {
        confirmSaveDomainAgeSettings();
    }
    
    function renderDomainAgeExemptionPreviews() {
        renderDomainAllowlistPreview();
        renderThresholdOverridesPreview();
    }
    
    function renderDomainAllowlistPreview() {
        var tbody = document.getElementById('domain-allowlist-preview-body');
        if (!tbody) return;
        
        var items = (mockData.domainAllowlist || []).filter(function(d) { return d.status === 'active'; })
            .sort(function(a, b) { return (b.updatedAt || '').localeCompare(a.updatedAt || ''); })
            .slice(0, 5);
        
        if (items.length === 0) {
            tbody.innerHTML = '<tr><td colspan="3" class="text-center text-muted" style="padding: 0.4rem;">No domain exemptions</td></tr>';
            return;
        }
        
        tbody.innerHTML = items.map(function(item) {
            var scopeLabel = item.scope === 'all' ? 'Global' : 
                            (item.scope === 'account' ? (item.scopeDetails ? item.scopeDetails.accountName : 'Account').substring(0, 10) : 
                            (item.scopeDetails ? item.scopeDetails.subAccountName : 'N/A').substring(0, 10));
            var scopeBadgeColor = item.scope === 'all' ? '#1e3a5f' : (item.scope === 'account' ? '#0d6efd' : '#6f42c1');
            
            return '<tr>' +
                '<td style="padding: 0.2rem 0.25rem;"><code style="font-size: 0.65rem; background: #e9ecef; padding: 1px 3px; border-radius: 2px;">' + escapeHtml(item.domain.substring(0, 18)) + '</code></td>' +
                '<td style="padding: 0.2rem 0.25rem;"><span class="badge" style="font-size: 0.55rem; background: ' + scopeBadgeColor + '; color: white;">' + escapeHtml(scopeLabel) + '</span></td>' +
                '<td style="padding: 0.2rem 0.25rem; font-size: 0.6rem; color: #6c757d;">' + (item.updatedAt || '').split(' ')[0] + '</td>' +
            '</tr>';
        }).join('');
    }
    
    function renderThresholdOverridesPreview() {
        var tbody = document.getElementById('threshold-override-preview-body');
        if (!tbody) return;
        
        var items = (mockData.thresholdOverrides || []).filter(function(t) { return t.status === 'active'; })
            .sort(function(a, b) { return (b.updatedAt || '').localeCompare(a.updatedAt || ''); })
            .slice(0, 5);
        
        if (items.length === 0) {
            tbody.innerHTML = '<tr><td colspan="3" class="text-center text-muted" style="padding: 0.4rem;">No threshold overrides</td></tr>';
            return;
        }
        
        tbody.innerHTML = items.map(function(item) {
            var actionLabel = item.actionOverride === 'flag' ? 'Flag' : (item.actionOverride === 'block' ? 'Block' : 'Default');
            var actionColor = item.actionOverride === 'flag' ? '#ffc107' : (item.actionOverride === 'block' ? '#dc3545' : '#6c757d');
            var actionTextColor = item.actionOverride === 'flag' ? '#212529' : 'white';
            
            return '<tr>' +
                '<td style="padding: 0.2rem 0.25rem; font-size: 0.65rem; max-width: 90px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="' + escapeHtml(item.accountName) + '">' + escapeHtml(item.accountName.substring(0, 14)) + '</td>' +
                '<td style="padding: 0.2rem 0.25rem;"><span class="badge bg-info text-white" style="font-size: 0.55rem;">' + item.thresholdHours + 'h</span></td>' +
                '<td style="padding: 0.2rem 0.25rem;"><span class="badge" style="font-size: 0.55rem; background: ' + actionColor + '; color: ' + actionTextColor + ';">' + actionLabel + '</span></td>' +
            '</tr>';
        }).join('');
    }
    
    function showAddDomainAllowlistModal() {
        var modalHtml = 
            '<div class="modal fade" id="addDomainAllowlistModal" tabindex="-1" data-bs-backdrop="static">' +
                '<div class="modal-dialog modal-dialog-centered">' +
                    '<div class="modal-content">' +
                        '<div class="modal-header py-2" style="background: #1e3a5f; border-bottom: none;">' +
                            '<h6 class="modal-title text-white mb-0"><i class="fas fa-globe me-2"></i>Add Domain to Allowlist</h6>' +
                            '<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>' +
                        '</div>' +
                        '<div class="modal-body py-3">' +
                            '<div class="mb-3">' +
                                '<label class="form-label" style="font-size: 0.8rem; font-weight: 600;">Domain <span class="text-danger">*</span></label>' +
                                '<input type="text" class="form-control form-control-sm" id="allowlist-domain" placeholder="e.g. example.com or *.example.com" style="font-size: 0.85rem;">' +
                                '<small class="text-muted" style="font-size: 0.7rem;">Enter a domain or use wildcard (*) for subdomains</small>' +
                            '</div>' +
                            '<div class="mb-3">' +
                                '<label class="form-label" style="font-size: 0.8rem; font-weight: 600;">Scope</label>' +
                                '<select class="form-select form-select-sm" id="allowlist-scope" style="font-size: 0.85rem;" onchange="toggleAllowlistAccountSelect()">' +
                                    '<option value="all">Global (all accounts)</option>' +
                                    '<option value="account">Specific Account</option>' +
                                '</select>' +
                            '</div>' +
                            '<div class="mb-3" id="allowlist-account-group" style="display: none;">' +
                                '<label class="form-label" style="font-size: 0.8rem; font-weight: 600;">Account</label>' +
                                '<select class="form-select form-select-sm" id="allowlist-account" style="font-size: 0.85rem;">' +
                                    '<option value="">Select account...</option>' +
                                    mockData.accounts.map(function(acc) { return '<option value="' + acc.id + '">' + escapeHtml(acc.name) + '</option>'; }).join('') +
                                '</select>' +
                            '</div>' +
                        '</div>' +
                        '<div class="modal-footer py-2" style="border-top: 1px solid #e9ecef;">' +
                            '<button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>' +
                            '<button type="button" class="btn btn-sm text-white" style="background: #1e3a5f;" onclick="saveAddDomainAllowlist()"><i class="fas fa-plus me-1"></i>Add Domain</button>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
            '</div>';
        
        var existingModal = document.getElementById('addDomainAllowlistModal');
        if (existingModal) existingModal.remove();
        
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        var modal = new bootstrap.Modal(document.getElementById('addDomainAllowlistModal'));
        modal.show();
    }
    
    function toggleAllowlistAccountSelect() {
        var scope = document.getElementById('allowlist-scope').value;
        document.getElementById('allowlist-account-group').style.display = scope === 'account' ? 'block' : 'none';
    }
    
    function saveAddDomainAllowlist() {
        var domain = document.getElementById('allowlist-domain').value.trim();
        if (!domain) {
            showToast('Please enter a domain', 'error');
            return;
        }
        
        var scope = document.getElementById('allowlist-scope').value;
        var accountId = scope === 'account' ? document.getElementById('allowlist-account').value : null;
        var account = accountId ? mockData.accounts.find(function(a) { return a.id === accountId; }) : null;
        
        if (scope === 'account' && !accountId) {
            showToast('Please select an account', 'error');
            return;
        }
        
        var newEntry = {
            id: 'DAL-' + String(mockData.domainAllowlist.length + 1).padStart(3, '0'),
            domain: domain.toLowerCase(),
            scope: scope,
            scopeDetails: account ? { accountId: account.id, accountName: account.name } : null,
            overrideType: 'full',
            status: 'active',
            addedBy: currentAdmin.email,
            addedAt: formatDateTime(new Date()),
            updatedAt: formatDateTime(new Date())
        };
        
        mockData.domainAllowlist.push(newEntry);
        
        logAuditEvent('URL_DOMAIN_ALLOWLIST_ADDED', {
            entityType: 'domain_allowlist',
            domain: domain,
            scope: scope,
            accountId: accountId,
            accountName: account ? account.name : null,
            adminUser: currentAdmin.email,
            timestamp: new Date().toISOString()
        });
        
        bootstrap.Modal.getInstance(document.getElementById('addDomainAllowlistModal')).hide();
        renderDomainAgeExemptionPreviews();
        showToast('Domain "' + domain + '" added to allowlist', 'success');
    }
    
    function showAddThresholdOverrideModal() {
        var modalHtml = 
            '<div class="modal fade" id="addThresholdOverrideModal" tabindex="-1" data-bs-backdrop="static">' +
                '<div class="modal-dialog modal-dialog-centered">' +
                    '<div class="modal-content">' +
                        '<div class="modal-header py-2" style="background: #1e3a5f; border-bottom: none;">' +
                            '<h6 class="modal-title text-white mb-0"><i class="fas fa-clock me-2"></i>Add Threshold Override</h6>' +
                            '<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>' +
                        '</div>' +
                        '<div class="modal-body py-3">' +
                            '<div class="mb-3">' +
                                '<label class="form-label" style="font-size: 0.8rem; font-weight: 600;">Account <span class="text-danger">*</span></label>' +
                                '<select class="form-select form-select-sm" id="override-account" style="font-size: 0.85rem;">' +
                                    '<option value="">Select account...</option>' +
                                    mockData.accounts.map(function(acc) { return '<option value="' + acc.id + '">' + escapeHtml(acc.name) + '</option>'; }).join('') +
                                '</select>' +
                            '</div>' +
                            '<div class="mb-3">' +
                                '<label class="form-label" style="font-size: 0.8rem; font-weight: 600;">Threshold (hours)</label>' +
                                '<input type="number" class="form-control form-control-sm" id="override-threshold" value="24" min="1" max="8760" style="font-size: 0.85rem;">' +
                                '<small class="text-muted" style="font-size: 0.7rem;">Custom threshold for this account (overrides global setting)</small>' +
                            '</div>' +
                            '<div class="mb-3">' +
                                '<label class="form-label" style="font-size: 0.8rem; font-weight: 600;">Action Override</label>' +
                                '<select class="form-select form-select-sm" id="override-action" style="font-size: 0.85rem;">' +
                                    '<option value="block">Block</option>' +
                                    '<option value="flag">Flag to Quarantine</option>' +
                                '</select>' +
                            '</div>' +
                        '</div>' +
                        '<div class="modal-footer py-2" style="border-top: 1px solid #e9ecef;">' +
                            '<button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>' +
                            '<button type="button" class="btn btn-sm text-white" style="background: #1e3a5f;" onclick="saveAddThresholdOverride()"><i class="fas fa-plus me-1"></i>Add Override</button>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
            '</div>';
        
        var existingModal = document.getElementById('addThresholdOverrideModal');
        if (existingModal) existingModal.remove();
        
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        var modal = new bootstrap.Modal(document.getElementById('addThresholdOverrideModal'));
        modal.show();
    }
    
    function saveAddThresholdOverride() {
        var accountId = document.getElementById('override-account').value;
        if (!accountId) {
            showToast('Please select an account', 'error');
            return;
        }
        
        var account = mockData.accounts.find(function(a) { return a.id === accountId; });
        var threshold = parseInt(document.getElementById('override-threshold').value) || 24;
        var action = document.getElementById('override-action').value;
        
        var newEntry = {
            id: 'TOR-' + String(mockData.thresholdOverrides.length + 1).padStart(3, '0'),
            accountId: accountId,
            accountName: account ? account.name : accountId,
            allSubaccounts: true,
            subAccounts: [],
            thresholdHours: threshold,
            actionOverride: action,
            status: 'active',
            addedBy: currentAdmin.email,
            addedAt: formatDateTime(new Date()),
            updatedAt: formatDateTime(new Date())
        };
        
        mockData.thresholdOverrides.push(newEntry);
        
        logAuditEvent('URL_DOMAIN_AGE_OVERRIDE_ADDED', {
            entityType: 'threshold_override',
            accountId: accountId,
            accountName: account ? account.name : null,
            thresholdHours: threshold,
            actionOverride: action,
            adminUser: currentAdmin.email,
            timestamp: new Date().toISOString()
        });
        
        bootstrap.Modal.getInstance(document.getElementById('addThresholdOverrideModal')).hide();
        renderDomainAgeExemptionPreviews();
        showToast('Threshold override added for ' + (account ? account.name : accountId), 'success');
    }
    
    function viewAllDomainAgeExemptions(filterType) {
        // Switch to Exemptions tab and apply filter
        var exemptionsTab = document.getElementById('url-exemptions-tab');
        if (exemptionsTab) {
            var tab = new bootstrap.Tab(exemptionsTab);
            tab.show();
            
            // Set the filter to domain_age type
            setTimeout(function() {
                var typeFilter = document.getElementById('url-exemptions-filter-type');
                if (typeFilter) {
                    typeFilter.value = 'domain_age';
                    renderUrlExemptionsTab();
                }
            }, 100);
        }
        
        showToast('Showing all Domain Age exemptions', 'info');
    }
    
    var globalExemptionDomains = [];
    
    function showAddUrlExemptionGlobalModal() {
        document.getElementById('add-url-exemption-global-form').reset();
        document.getElementById('global-exemption-account-id').value = '';
        document.getElementById('global-exemption-account-search').value = '';
        document.getElementById('global-exemption-all-subaccounts').checked = true;
        document.getElementById('global-exemption-subaccounts').disabled = true;
        document.getElementById('global-exemption-type-domain-age').checked = true;
        document.getElementById('global-exemption-disable-domain-age').checked = false;
        document.getElementById('global-exemption-threshold-hours').value = 24;
        document.getElementById('global-exemption-action-override').value = '';
        document.getElementById('global-exemption-reason').value = '';
        
        globalExemptionDomains = [];
        renderDomainChips();
        toggleGlobalExemptionType();
        toggleGlobalExemptionDomainAgeMode();
        populateGlobalExemptionRulesList();
        
        var modal = new bootstrap.Modal(document.getElementById('addUrlExemptionGlobalModal'));
        modal.show();
    }
    
    function toggleGlobalExemptionType() {
        var type = document.querySelector('input[name="global-exemption-type"]:checked').value;
        document.getElementById('global-exemption-domain-age-section').style.display = type === 'domain_age' ? 'block' : 'none';
        document.getElementById('global-exemption-domains-section').style.display = type === 'domains' ? 'block' : 'none';
        document.getElementById('global-exemption-rules-section').style.display = type === 'rules' ? 'block' : 'none';
    }
    
    function toggleGlobalExemptionSubaccounts() {
        var allChecked = document.getElementById('global-exemption-all-subaccounts').checked;
        document.getElementById('global-exemption-subaccounts').disabled = allChecked;
    }
    
    function toggleGlobalExemptionDomainAgeMode() {
        var disabled = document.getElementById('global-exemption-disable-domain-age').checked;
        document.getElementById('global-exemption-threshold-group').style.opacity = disabled ? '0.5' : '1';
        document.getElementById('global-exemption-action-group').style.opacity = disabled ? '0.5' : '1';
        document.getElementById('global-exemption-threshold-hours').disabled = disabled;
        document.getElementById('global-exemption-action-override').disabled = disabled;
    }
    
    function handleDomainChipInput(event) {
        if (event.key === 'Enter' || event.key === ',') {
            event.preventDefault();
            var input = event.target;
            var value = input.value.trim().replace(/,/g, '');
            if (value) {
                addDomainChip(value);
                input.value = '';
            }
        }
    }
    
    function handleDomainPaste(event) {
        event.preventDefault();
        var paste = (event.clipboardData || window.clipboardData).getData('text');
        var domains = paste.split(/[\s,;\n]+/).filter(function(d) { return d.trim(); });
        domains.forEach(function(d) { addDomainChip(d.trim()); });
    }
    
    function addDomainChip(domain) {
        var canonical = domain.toLowerCase().replace(/^https?:\/\//, '').replace(/\/.*$/, '').trim();
        if (!canonical) return;
        
        // Validate domain format
        var domainRegex = /^(\*\.)?[a-zA-Z0-9]([a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(\.[a-zA-Z0-9]([a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/;
        if (!domainRegex.test(canonical)) {
            showToast('Invalid domain format: ' + canonical, 'error');
            return;
        }
        
        // Dedupe
        if (globalExemptionDomains.indexOf(canonical) !== -1) return;
        
        globalExemptionDomains.push(canonical);
        renderDomainChips();
    }
    
    function removeDomainChip(index) {
        globalExemptionDomains.splice(index, 1);
        renderDomainChips();
    }
    
    function renderDomainChips() {
        var container = document.getElementById('global-exemption-domains-container');
        var input = document.getElementById('global-exemption-domains-input');
        if (!container) return;
        
        container.innerHTML = '';
        globalExemptionDomains.forEach(function(domain, idx) {
            var chip = document.createElement('span');
            chip.className = 'badge bg-light text-dark me-1';
            chip.style.cssText = 'font-size: 0.75rem; padding: 0.25rem 0.5rem; display: inline-flex; align-items: center; gap: 4px;';
            chip.innerHTML = escapeHtml(domain) + '<i class="fas fa-times" style="cursor: pointer; font-size: 0.6rem;" onclick="removeDomainChip(' + idx + ')"></i>';
            container.appendChild(chip);
        });
        container.appendChild(input);
    }
    
    function populateGlobalExemptionRulesList() {
        var container = document.getElementById('global-exemption-rules-list');
        if (!container) return;
        
        var rules = mockData.urlRules.filter(function(r) { return r.status === 'active'; });
        
        if (rules.length === 0) {
            container.innerHTML = '<small class="text-muted">No active URL rules available</small>';
            return;
        }
        
        container.innerHTML = rules.map(function(rule) {
            return '<div class="form-check">' +
                '<input class="form-check-input global-url-rule-checkbox" type="checkbox" value="' + rule.id + '" id="global-rule-' + rule.id + '">' +
                '<label class="form-check-label" for="global-rule-' + rule.id + '" style="font-size: 0.8rem;">' +
                    '<code style="background: #e9ecef; padding: 1px 4px; border-radius: 2px; font-size: 0.75rem;">' + escapeHtml(rule.pattern) + '</code>' +
                    '<span class="badge bg-secondary ms-1" style="font-size: 0.6rem;">' + rule.matchType + '</span>' +
                '</label>' +
            '</div>';
        }).join('');
    }
    
    function toggleSelectAllUrlRules() {
        var selectAll = document.getElementById('global-exemption-select-all-rules').checked;
        document.querySelectorAll('.global-url-rule-checkbox').forEach(function(cb) {
            cb.checked = selectAll;
        });
    }
    
    function toggleGlobalExemptionAccountDropdown() {
        var dropdown = document.getElementById('global-exemption-account-dropdown');
        if (dropdown.classList.contains('show')) {
            dropdown.classList.remove('show');
        } else {
            showGlobalExemptionAccountDropdown();
        }
    }
    
    function showGlobalExemptionAccountDropdown() {
        var search = document.getElementById('global-exemption-account-search').value.toLowerCase().trim();
        var dropdown = document.getElementById('global-exemption-account-dropdown');
        
        var filtered;
        if (search.length === 0) {
            filtered = mockData.accounts.slice(0, 15);
        } else {
            filtered = mockData.accounts.filter(function(a) {
                return a.name.toLowerCase().indexOf(search) !== -1 || a.id.toLowerCase().indexOf(search) !== -1;
            }).slice(0, 15);
        }
        
        if (filtered.length === 0) {
            dropdown.innerHTML = '<div class="dropdown-item text-muted" style="font-size: 0.85rem;">No accounts found</div>';
        } else {
            dropdown.innerHTML = filtered.map(function(a) {
                return '<div class="dropdown-item" style="cursor: pointer; font-size: 0.85rem; padding: 0.4rem 0.75rem;" onclick="selectGlobalExemptionAccount(\'' + a.id + '\', \'' + escapeHtml(a.name).replace(/'/g, "\\'") + '\')" onmouseenter="this.style.background=\'#e9ecef\'" onmouseleave="this.style.background=\'transparent\'">' +
                    '<div style="font-weight: 500;">' + escapeHtml(a.name) + '</div>' +
                    '<small class="text-muted">' + a.id + '</small>' +
                '</div>';
            }).join('');
        }
        
        dropdown.classList.add('show');
    }
    
    function filterGlobalExemptionAccounts() {
        showGlobalExemptionAccountDropdown();
    }
    
    function selectGlobalExemptionAccount(accountId, accountName) {
        document.getElementById('global-exemption-account-id').value = accountId;
        document.getElementById('global-exemption-account-search').value = accountName;
        document.getElementById('global-exemption-account-dropdown').classList.remove('show');
        
        // Populate sub-accounts for this account
        var account = mockData.accounts.find(function(a) { return a.id === accountId; });
        var subSelect = document.getElementById('global-exemption-subaccounts');
        subSelect.innerHTML = '';
        if (account && account.subAccounts && account.subAccounts.length > 0) {
            account.subAccounts.forEach(function(sub) {
                var opt = document.createElement('option');
                opt.value = sub.id;
                opt.textContent = sub.name;
                subSelect.appendChild(opt);
            });
        } else {
            var opt = document.createElement('option');
            opt.value = '';
            opt.textContent = 'No sub-accounts';
            opt.disabled = true;
            subSelect.appendChild(opt);
        }
    }
    
    function saveGlobalUrlExemption() {
        var accountId = document.getElementById('global-exemption-account-id').value;
        if (!accountId) {
            showToast('Please select an account', 'error');
            return;
        }
        
        var type = document.querySelector('input[name="global-exemption-type"]:checked').value;
        var allSubaccounts = document.getElementById('global-exemption-all-subaccounts').checked;
        var reason = document.getElementById('global-exemption-reason').value.trim();
        var account = mockData.accounts.find(function(a) { return a.id === accountId; });
        
        var exemptionData = {
            id: 'UEX-' + String(mockData.urlExemptions.length + 1).padStart(3, '0'),
            accountId: accountId,
            accountName: account ? account.name : accountId,
            subAccounts: [],
            allSubaccounts: allSubaccounts,
            status: 'active',
            reason: reason,
            appliedBy: currentAdmin.email,
            appliedAt: formatDateTime(new Date())
        };
        
        if (!allSubaccounts) {
            var selectedSubs = Array.from(document.getElementById('global-exemption-subaccounts').selectedOptions);
            exemptionData.subAccounts = selectedSubs.map(function(opt) {
                return { id: opt.value, name: opt.textContent };
            });
        }
        
        var eventType = '';
        
        if (type === 'domain_age') {
            var disabled = document.getElementById('global-exemption-disable-domain-age').checked;
            exemptionData.type = 'domain_age';
            exemptionData.disableEnforcement = disabled;
            exemptionData.thresholdOverride = disabled ? null : parseInt(document.getElementById('global-exemption-threshold-hours').value) || 24;
            exemptionData.actionOverride = disabled ? null : (document.getElementById('global-exemption-action-override').value || null);
            exemptionData.exemptRules = [];
            eventType = 'URL_DOMAIN_AGE_EXEMPTION_ADDED';
            
            // Also add to threshold overrides if custom threshold
            if (!disabled) {
                mockData.thresholdOverrides.push({
                    id: 'THR-' + String(mockData.thresholdOverrides.length + 1).padStart(3, '0'),
                    accountId: accountId,
                    accountName: exemptionData.accountName,
                    subAccounts: exemptionData.subAccounts,
                    allSubaccounts: allSubaccounts,
                    thresholdHours: exemptionData.thresholdOverride,
                    actionOverride: exemptionData.actionOverride || 'default',
                    status: 'active',
                    addedBy: currentAdmin.email,
                    addedAt: formatDateTime(new Date()),
                    updatedAt: formatDateTime(new Date())
                });
            }
        } else if (type === 'domains') {
            if (globalExemptionDomains.length === 0) {
                showToast('Please add at least one domain', 'error');
                return;
            }
            exemptionData.type = 'domains';
            exemptionData.domains = globalExemptionDomains.slice();
            exemptionData.exemptRules = [];
            eventType = 'URL_DOMAIN_ALLOWLIST_ADDED';
            
            // Also add to domain allowlist
            globalExemptionDomains.forEach(function(domain) {
                mockData.domainAllowlist.push({
                    id: 'DAL-' + String(mockData.domainAllowlist.length + 1).padStart(3, '0'),
                    domain: domain,
                    scope: allSubaccounts ? 'account' : 'subaccount',
                    scopeDetails: { accountId: accountId, accountName: exemptionData.accountName },
                    overrideType: 'full',
                    status: 'active',
                    addedBy: currentAdmin.email,
                    addedAt: formatDateTime(new Date()),
                    updatedAt: formatDateTime(new Date())
                });
            });
        } else if (type === 'rules') {
            var selectedRules = Array.from(document.querySelectorAll('.global-url-rule-checkbox:checked')).map(function(cb) {
                return cb.value;
            });
            if (selectedRules.length === 0) {
                showToast('Please select at least one URL rule', 'error');
                return;
            }
            exemptionData.type = 'url_rule';
            exemptionData.exemptRules = selectedRules;
            eventType = 'URL_RULE_EXEMPTION_ADDED';
        }
        
        mockData.urlExemptions.push(exemptionData);
        
        logAuditEvent(eventType, {
            entityType: 'url_exemption',
            exemptionId: exemptionData.id,
            adminUserId: currentAdmin.id,
            adminUser: currentAdmin.email,
            timestamp: new Date().toISOString(),
            sourceIp: getClientIP(),
            before: null,
            after: exemptionData,
            affectedScope: {
                type: exemptionData.allSubaccounts ? 'account_all_subaccounts' : 'account_specific_subaccounts',
                accountId: exemptionData.accountId,
                accountName: exemptionData.accountName,
                subAccountsAffected: exemptionData.subAccounts.length > 0 ? exemptionData.subAccounts : 'all'
            },
            type: exemptionData.type,
            domains: exemptionData.domains || [],
            ruleIdsAffected: exemptionData.exemptRules || [],
            reason: reason
        });
        
        bootstrap.Modal.getInstance(document.getElementById('addUrlExemptionGlobalModal')).hide();
        renderUrlExemptionsTab();
        renderDomainAgeExemptionPreviews();
        showToast('Exemption added successfully', 'success');
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
        
        logAuditEvent('URL_DOMAIN_AGE_OVERRIDE_ADDED', {
            entityType: 'domain_age_override',
            exceptionId: exception.id,
            adminUserId: currentAdmin.id,
            adminUser: currentAdmin.email,
            timestamp: new Date().toISOString(),
            sourceIp: getClientIP(),
            before: null,
            after: exception,
            affectedScope: {
                type: 'account',
                accountId: accountId,
                accountName: accountName,
                subAccountsAffected: []
            },
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
        
        logAuditEvent('URL_DOMAIN_AGE_OVERRIDE_REMOVED', {
            entityType: 'domain_age_override',
            exceptionId: exceptionId,
            adminUserId: currentAdmin.id,
            adminUser: currentAdmin.email,
            timestamp: new Date().toISOString(),
            sourceIp: getClientIP(),
            before: removedExc,
            after: null,
            affectedScope: {
                type: 'account',
                accountId: removedExc.accountId,
                accountName: removedExc.accountName,
                subAccountsAffected: []
            }
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
            updateDomainAgeStatusBadge();
        });
        
        // URL Controls main search
        var urlControlsSearch = document.getElementById('url-controls-search');
        if (urlControlsSearch) {
            urlControlsSearch.addEventListener('input', function() {
                // Apply search to currently visible sub-tab
                var urlRulesTab = document.getElementById('url-rules-tab');
                var urlExemptionsTab = document.getElementById('url-exemptions-tab');
                if (urlRulesTab && urlRulesTab.classList.contains('active')) {
                    document.getElementById('url-search').value = urlControlsSearch.value;
                    renderUrlTab();
                } else if (urlExemptionsTab && urlExemptionsTab.classList.contains('active')) {
                    document.getElementById('url-exemptions-search').value = urlControlsSearch.value;
                    renderUrlExemptionsTab();
                }
            });
        }
        
        // Show/hide Add Rule button based on active sub-tab
        var urlRulesTab = document.getElementById('url-rules-tab');
        if (urlRulesTab) {
            urlRulesTab.addEventListener('shown.bs.tab', function() {
                var addRuleBtn = document.getElementById('url-add-rule-btn');
                if (addRuleBtn) addRuleBtn.style.display = 'inline-flex';
            });
        }
        
        var urlDomainAgeTab = document.getElementById('url-domain-age-tab');
        if (urlDomainAgeTab) {
            urlDomainAgeTab.addEventListener('shown.bs.tab', function() {
                var addRuleBtn = document.getElementById('url-add-rule-btn');
                if (addRuleBtn) addRuleBtn.style.display = 'none';
            });
        }
        
        var urlExemptionsTabBtn = document.getElementById('url-exemptions-tab');
        if (urlExemptionsTabBtn) {
            urlExemptionsTabBtn.addEventListener('shown.bs.tab', function() {
                var addRuleBtn = document.getElementById('url-add-rule-btn');
                if (addRuleBtn) addRuleBtn.style.display = 'none';
                renderUrlExemptionsTab();
            });
        }
        
        // URL Exemptions tab listeners
        var urlExemptionsSearch = document.getElementById('url-exemptions-search');
        if (urlExemptionsSearch) {
            urlExemptionsSearch.addEventListener('input', renderUrlExemptionsTab);
        }
        var urlExemptionsFilterStatus = document.getElementById('url-exemptions-filter-status');
        if (urlExemptionsFilterStatus) {
            urlExemptionsFilterStatus.addEventListener('change', renderUrlExemptionsTab);
        }
        var urlExemptionsFilterType = document.getElementById('url-exemptions-filter-type');
        if (urlExemptionsFilterType) {
            urlExemptionsFilterType.addEventListener('change', renderUrlExemptionsTab);
        }
        
        // URL Exemption modal account search
        var urlExemptionAccountSearch = document.getElementById('url-exemption-account-search');
        if (urlExemptionAccountSearch) {
            urlExemptionAccountSearch.addEventListener('input', filterUrlExemptionAccounts);
        }
        
        // Global URL Exemption modal account search
        var globalExemptionAccountSearch = document.getElementById('global-exemption-account-search');
        if (globalExemptionAccountSearch) {
            globalExemptionAccountSearch.addEventListener('input', filterGlobalExemptionAccounts);
            globalExemptionAccountSearch.addEventListener('focus', showGlobalExemptionAccountDropdown);
        }
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            var dropdown = document.getElementById('global-exemption-account-dropdown');
            var searchInput = document.getElementById('global-exemption-account-search');
            var toggleBtn = document.getElementById('global-exemption-account-toggle');
            if (dropdown && searchInput && toggleBtn) {
                if (!searchInput.contains(e.target) && !dropdown.contains(e.target) && !toggleBtn.contains(e.target)) {
                    dropdown.classList.remove('show');
                }
            }
        });
        
        // Account filter change updates sub-account options
        var urlExemptionsAccountFilter = document.getElementById('url-exemptions-filter-account');
        if (urlExemptionsAccountFilter) {
            urlExemptionsAccountFilter.addEventListener('change', function() {
                populateUrlExemptionsAccountFilter();
            });
        }
    }

    function renderNormTab() {
        console.log('[NormTab] renderNormTab called');
        var library = mockData.baseCharacterLibrary;
        console.log('[NormTab] baseCharacterLibrary length:', library ? library.length : 'undefined');
        
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
        
        var letters = library.filter(function(c) { return c.type === 'letter'; });
        var digits = library.filter(function(c) { return c.type === 'digit'; });
        
        var enabledCount = library.filter(function(c) { return c.enabled; }).length;
        var disabledCount = library.filter(function(c) { return !c.enabled; }).length;
        var totalEquivalents = library.reduce(function(sum, c) { return sum + c.equivalents.length; }, 0);
        
        renderBaseCharacterGrid('letters', letters, riskColors);
        renderBaseCharacterGrid('digits', digits, riskColors);
    }
    
    function renderBaseCharacterGrid(type, characters, riskColors) {
        var gridId = type === 'digits' ? 'norm-digits-grid' : 'norm-letters-grid';
        console.log('[NormTab] renderBaseCharacterGrid called for', type, 'with', characters ? characters.length : 0, 'characters');
        var grid = document.getElementById(gridId);
        console.log('[NormTab] Grid element found:', gridId, grid ? 'YES' : 'NO');
        if (!grid) return;
        
        grid.innerHTML = characters.map(function(char) {
            var equivalentsPreview = '';
            if (char.equivalents.length > 0) {
                equivalentsPreview = char.equivalents.slice(0, 6).map(function(eq) {
                    return '<span class="equiv-chip">' + eq + '</span>';
                }).join('');
                if (char.equivalents.length > 6) {
                    equivalentsPreview += '<span style="font-size: 0.65rem; color: #94a3b8;">+' + (char.equivalents.length - 6) + '</span>';
                }
            }
            
            var dataAttrs = 'data-base="' + char.base + '" ' +
                'data-equivalents="' + char.equivalents.join(',') + '" ' +
                'data-status="' + (char.enabled ? 'enabled' : 'disabled') + '" ' +
                'data-risk="' + char.risk + '"';
            
            return '<div class="norm-char-card ' + (char.enabled ? '' : 'disabled') + '" ' + dataAttrs + ' onclick="editBaseCharacter(\'' + char.base + '\')">' +
                '<div class="char-symbol">' + char.base + '</div>' +
                '<div class="char-info">' +
                    '<div class="equiv-count">' + char.equivalents.length + ' equivalent' + (char.equivalents.length !== 1 ? 's' : '') + '</div>' +
                    (char.equivalents.length > 0 ? '<div class="equiv-preview">' + equivalentsPreview + '</div>' : '<div class="text-muted" style="font-size: 0.7rem;">Click to add</div>') +
                    '<div class="risk-indicator ' + char.risk + '">' + 
                        char.risk.charAt(0).toUpperCase() + char.risk.slice(1) + ' Risk' +
                    '</div>' +
                '</div>' +
            '</div>';
        }).join('');
    }
    
    function filterBaseCharacters(type) {
        var suffix = type === 'digits' ? 'digits' : 'letters';
        var statusFilter = document.getElementById('norm-filter-status-' + suffix) ? document.getElementById('norm-filter-status-' + suffix).value : '';
        var riskFilter = document.getElementById('norm-filter-risk-' + suffix) ? document.getElementById('norm-filter-risk-' + suffix).value : '';
        var searchText = document.getElementById('norm-search-' + suffix) ? document.getElementById('norm-search-' + suffix).value.toLowerCase() : '';
        
        var gridId = type === 'digits' ? 'norm-digits-grid' : 'norm-letters-grid';
        var cards = document.querySelectorAll('#' + gridId + ' .norm-char-card');
        
        cards.forEach(function(card) {
            var base = card.getAttribute('data-base');
            var char = mockData.baseCharacterLibrary.find(function(c) { return c.base === base; });
            if (!char) return;
            
            var show = true;
            
            if (statusFilter) {
                if (statusFilter === 'enabled' && !char.enabled) show = false;
                if (statusFilter === 'disabled' && char.enabled) show = false;
            }
            
            if (riskFilter && char.risk !== riskFilter) {
                show = false;
            }
            
            if (searchText && base.toLowerCase().indexOf(searchText) === -1) {
                show = false;
            }
            
            card.style.display = show ? '' : 'none';
        });
    }

    function renderQuarantineTab() {
        console.log('[Quarantine] renderQuarantineTab called');
        console.log('[Quarantine] mockData.quarantinedMessages count:', mockData.quarantinedMessages.length);
        
        var tbody = document.getElementById('quarantine-body');
        var emptyState = document.getElementById('quarantine-empty-state');
        
        if (!tbody) {
            console.error('[Quarantine] tbody element not found!');
            return;
        }
        
        var statusFilterEl = document.getElementById('quarantine-filter-status');
        var ruleFilterEl = document.getElementById('quarantine-filter-rule');
        var urlFilterEl = document.getElementById('quarantine-filter-url');
        var accountFilterEl = document.getElementById('quarantine-filter-account');
        var searchEl = document.getElementById('quarantine-search');
        
        var statusFilter = statusFilterEl ? statusFilterEl.value : '';
        var ruleFilter = ruleFilterEl ? ruleFilterEl.value : '';
        var urlFilter = urlFilterEl ? urlFilterEl.value : '';
        var accountFilter = accountFilterEl ? accountFilterEl.value : '';
        var searchTerm = searchEl ? searchEl.value.toLowerCase() : '';
        var tileFilter = getActiveTileFilter();
        
        console.log('[Quarantine] tileFilter:', tileFilter);
        
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
                '<td><div style="font-weight: 600; font-size: 0.85rem; color: #1e3a5f;">' + msg.senderId + '</div></td>' +
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
    
    function toggleSenderIdFilterPanel() {
        var panel = document.getElementById('senderid-filter-panel');
        var btn = document.getElementById('senderid-filter-btn');
        
        if (!panel || !btn) return;
        
        if (panel.style.display === 'none' || panel.style.display === '') {
            panel.style.display = 'block';
            btn.classList.add('active');
        } else {
            panel.style.display = 'none';
            btn.classList.remove('active');
        }
    }
    
    var exemptionWizardStep = 1;
    var mockSubAccounts = {
        'ACC-1234': [{ id: 'SUB-001', name: 'Marketing Dept' }, { id: 'SUB-002', name: 'Sales Team' }],
        'ACC-5678': [{ id: 'SUB-003', name: 'Corporate' }, { id: 'SUB-004', name: 'Retail' }],
        'ACC-4005': [{ id: 'SUB-010', name: 'Clinic A' }, { id: 'SUB-011', name: 'Clinic B' }, { id: 'SUB-012', name: 'Clinic C' }],
        'ACC-4008': [{ id: 'SUB-020', name: 'Dev Team' }],
        'ACC-10045': [{ id: 'SUB-030', name: 'Engineering' }, { id: 'SUB-031', name: 'Product' }],
        'ACC-10089': [{ id: 'SUB-040', name: 'Primary Care' }, { id: 'SUB-041', name: 'Specialists' }],
        'ACC-10112': [{ id: 'SUB-050', name: 'Fashion' }, { id: 'SUB-051', name: 'Electronics' }, { id: 'SUB-052', name: 'Home' }]
    };
    
    function showAddSenderIdExemptionModal() {
        exemptionWizardStep = 1;
        document.getElementById('addExemptionForm').reset();
        document.getElementById('exemption-step-1').style.display = 'block';
        document.getElementById('exemption-step-2').style.display = 'none';
        document.getElementById('exemption-back-btn').style.display = 'none';
        document.getElementById('exemption-next-btn').style.display = 'inline-flex';
        document.getElementById('exemption-confirm-btn').style.display = 'none';
        document.getElementById('exemption-account-group').style.display = 'none';
        document.getElementById('exemption-subaccount-group').style.display = 'none';
        document.getElementById('exemption-type').value = 'alphanumeric';
        document.getElementById('exemption-senderid-charcount').textContent = '0';
        
        updateExemptionWizardSteps(1);
        
        document.querySelectorAll('.exemption-type-card').forEach(function(card) {
            card.classList.remove('selected');
            card.style.borderColor = '#e9ecef';
            card.style.background = '#fff';
            card.querySelector('div:first-child').style.background = 'rgba(30, 58, 95, 0.1)';
            card.querySelector('div:first-child i').style.color = '#1e3a5f';
        });
        var alphaCard = document.querySelector('.exemption-type-card[data-type="alphanumeric"]');
        if (alphaCard) {
            alphaCard.classList.add('selected');
            alphaCard.style.borderColor = '#1e3a5f';
            alphaCard.style.background = 'rgba(30, 58, 95, 0.05)';
            alphaCard.querySelector('div:first-child').style.background = '#1e3a5f';
            alphaCard.querySelector('div:first-child i').style.color = '#fff';
        }
        
        document.querySelectorAll('.scope-option').forEach(function(opt) {
            opt.classList.remove('selected');
            opt.style.borderColor = '#e9ecef';
            opt.style.background = '#fff';
        });
        var globalOpt = document.querySelector('.scope-option input[value="global"]');
        if (globalOpt) {
            globalOpt.checked = true;
            globalOpt.closest('.scope-option').classList.add('selected');
            globalOpt.closest('.scope-option').style.borderColor = '#1e3a5f';
            globalOpt.closest('.scope-option').style.background = 'rgba(30, 58, 95, 0.03)';
        }
        
        setupExemptionWizardListeners();
        
        var modal = new bootstrap.Modal(document.getElementById('addSenderIdExemptionModal'));
        modal.show();
        console.log('[SecurityComplianceControls] showAddSenderIdExemptionModal called');
    }
    
    function setupExemptionWizardListeners() {
        document.querySelectorAll('.exemption-type-card').forEach(function(card) {
            card.onclick = function() {
                document.querySelectorAll('.exemption-type-card').forEach(function(c) {
                    c.classList.remove('selected');
                    c.style.borderColor = '#e9ecef';
                    c.style.background = '#fff';
                    c.querySelector('div:first-child').style.background = 'rgba(30, 58, 95, 0.1)';
                    c.querySelector('div:first-child i').style.color = '#1e3a5f';
                });
                card.classList.add('selected');
                card.style.borderColor = '#1e3a5f';
                card.style.background = 'rgba(30, 58, 95, 0.05)';
                card.querySelector('div:first-child').style.background = '#1e3a5f';
                card.querySelector('div:first-child i').style.color = '#fff';
                document.getElementById('exemption-type').value = card.dataset.type;
                updateSenderIdHint(card.dataset.type);
            };
        });
        
        document.getElementById('exemption-senderid').oninput = function() {
            var len = this.value.length;
            document.getElementById('exemption-senderid-charcount').textContent = len;
        };
        
        document.querySelectorAll('input[name="exemption-scope"]').forEach(function(radio) {
            radio.onchange = function() {
                document.querySelectorAll('.scope-option').forEach(function(opt) {
                    opt.classList.remove('selected');
                    opt.style.borderColor = '#e9ecef';
                    opt.style.background = '#fff';
                });
                this.closest('.scope-option').classList.add('selected');
                this.closest('.scope-option').style.borderColor = '#1e3a5f';
                this.closest('.scope-option').style.background = 'rgba(30, 58, 95, 0.03)';
                
                var accountGroup = document.getElementById('exemption-account-group');
                var subaccountGroup = document.getElementById('exemption-subaccount-group');
                
                if (this.value === 'global') {
                    accountGroup.style.display = 'none';
                    subaccountGroup.style.display = 'none';
                } else if (this.value === 'account') {
                    accountGroup.style.display = 'block';
                    subaccountGroup.style.display = 'none';
                } else if (this.value === 'subaccount') {
                    accountGroup.style.display = 'block';
                    subaccountGroup.style.display = 'block';
                }
            };
        });
        
        document.getElementById('exemption-account').onchange = function() {
            loadSubAccountsForExemption(this.value);
        };
        
        document.getElementById('exemption-all-subaccounts').onchange = function() {
            var checkboxes = document.querySelectorAll('#exemption-subaccount-list input[type="checkbox"]');
            checkboxes.forEach(function(cb) {
                cb.checked = this.checked;
                cb.disabled = this.checked;
            }.bind(this));
        };
    }
    
    function updateSenderIdHint(type) {
        var hint = document.getElementById('exemption-senderid-hint');
        var input = document.getElementById('exemption-senderid');
        if (type === 'alphanumeric') {
            hint.textContent = '3-11 characters: A-Z a-z 0-9';
            input.maxLength = 11;
            input.placeholder = 'e.g. MYCOMPANY';
        } else if (type === 'numeric') {
            hint.textContent = 'UK mobile format: 447XXXXXXXXX';
            input.maxLength = 15;
            input.placeholder = 'e.g. 447700900123';
        } else if (type === 'shortcode') {
            hint.textContent = '5-6 digit shortcode';
            input.maxLength = 6;
            input.placeholder = 'e.g. 60123';
        }
    }
    
    function loadSubAccountsForExemption(accountId) {
        var listContainer = document.getElementById('exemption-subaccount-list');
        var subAccounts = mockSubAccounts[accountId] || [];
        
        if (subAccounts.length === 0) {
            listContainer.innerHTML = '<div class="text-muted text-center py-2" style="font-size: 0.8rem;"><i class="fas fa-info-circle me-1"></i>No sub-accounts found for this account</div>';
            return;
        }
        
        listContainer.innerHTML = subAccounts.map(function(sub) {
            return '<div class="form-check">' +
                '<input class="form-check-input exemption-subaccount-cb" type="checkbox" value="' + sub.id + '" id="sub-' + sub.id + '">' +
                '<label class="form-check-label" for="sub-' + sub.id + '" style="font-size: 0.85rem;">' + sub.name + ' <span class="text-muted">(' + sub.id + ')</span></label>' +
                '</div>';
        }).join('');
        
        document.getElementById('exemption-all-subaccounts').checked = false;
    }
    
    function updateExemptionWizardSteps(step) {
        var step1Elem = document.querySelector('.wizard-step[data-step="1"]');
        var step2Elem = document.querySelector('.wizard-step[data-step="2"]');
        var connector = document.querySelector('.step-connector');
        
        if (step === 1) {
            step1Elem.querySelector('.step-number').style.background = '#1e3a5f';
            step1Elem.querySelector('.step-number').style.color = '#fff';
            step1Elem.querySelector('.step-label').style.color = '#1e3a5f';
            step2Elem.querySelector('.step-number').style.background = '#e9ecef';
            step2Elem.querySelector('.step-number').style.color = '#6c757d';
            step2Elem.querySelector('.step-label').style.color = '#6c757d';
            connector.style.background = '#e9ecef';
        } else {
            step1Elem.querySelector('.step-number').style.background = '#1e3a5f';
            step1Elem.querySelector('.step-number').style.color = '#fff';
            step1Elem.querySelector('.step-label').style.color = '#1e3a5f';
            step2Elem.querySelector('.step-number').style.background = '#1e3a5f';
            step2Elem.querySelector('.step-number').style.color = '#fff';
            step2Elem.querySelector('.step-label').style.color = '#1e3a5f';
            connector.style.background = '#1e3a5f';
        }
    }
    
    function exemptionWizardNext() {
        var senderId = document.getElementById('exemption-senderid').value.trim().toUpperCase();
        var senderType = document.getElementById('exemption-type').value;
        var category = document.getElementById('exemption-category').value;
        
        var validation = validateSenderIdForType(senderId, senderType);
        if (!validation.valid) {
            var input = document.getElementById('exemption-senderid');
            input.classList.add('is-invalid');
            document.getElementById('exemption-senderid-error').textContent = validation.error;
            document.getElementById('exemption-senderid-error').style.display = 'block';
            return;
        }
        document.getElementById('exemption-senderid').classList.remove('is-invalid');
        document.getElementById('exemption-senderid-error').style.display = 'none';
        
        if (!category) {
            alert('Please select a category');
            return;
        }
        
        exemptionWizardStep = 2;
        document.getElementById('exemption-step-1').style.display = 'none';
        document.getElementById('exemption-step-2').style.display = 'block';
        document.getElementById('exemption-back-btn').style.display = 'inline-flex';
        document.getElementById('exemption-next-btn').style.display = 'none';
        document.getElementById('exemption-confirm-btn').style.display = 'inline-flex';
        
        updateExemptionWizardSteps(2);
        
        var categoryLabels = {
            'government_healthcare': 'Govt & Healthcare',
            'banking_finance': 'Banking & Finance',
            'delivery_logistics': 'Delivery & Logistics',
            'miscellaneous': 'Miscellaneous',
            'generic': 'Generic'
        };
        var typeLabels = { 'alphanumeric': 'Alphanumeric', 'numeric': 'Numeric', 'shortcode': 'Shortcode' };
        
        document.getElementById('exemption-summary-senderid').textContent = senderId;
        document.getElementById('exemption-summary-type').textContent = typeLabels[senderType] || senderType;
        document.getElementById('exemption-summary-category').textContent = categoryLabels[category] || category;
    }
    
    function exemptionWizardBack() {
        exemptionWizardStep = 1;
        document.getElementById('exemption-step-1').style.display = 'block';
        document.getElementById('exemption-step-2').style.display = 'none';
        document.getElementById('exemption-back-btn').style.display = 'none';
        document.getElementById('exemption-next-btn').style.display = 'inline-flex';
        document.getElementById('exemption-confirm-btn').style.display = 'none';
        
        updateExemptionWizardSteps(1);
    }
    
    function validateSenderIdForType(senderId, type) {
        if (!senderId) return { valid: false, error: 'SenderID is required' };
        senderId = senderId.trim();
        
        if (type === 'alphanumeric') {
            if (senderId.length < 3) return { valid: false, error: 'SenderID must be at least 3 characters' };
            if (senderId.length > 11) return { valid: false, error: 'SenderID must be 11 characters or less' };
            if (!/^[A-Za-z0-9]+$/.test(senderId)) return { valid: false, error: 'SenderID must contain only alphanumeric characters' };
            if (/^\d+$/.test(senderId)) return { valid: false, error: 'Alphanumeric SenderID cannot be all numeric' };
        } else if (type === 'numeric') {
            if (!/^\d+$/.test(senderId)) return { valid: false, error: 'Numeric SenderID must contain only digits' };
            if (senderId.length < 10) return { valid: false, error: 'Numeric SenderID must be at least 10 digits' };
        } else if (type === 'shortcode') {
            if (!/^\d+$/.test(senderId)) return { valid: false, error: 'Shortcode must contain only digits' };
            if (senderId.length < 5 || senderId.length > 6) return { valid: false, error: 'Shortcode must be 5-6 digits' };
        }
        
        return { valid: true, normalised: senderId.toUpperCase() };
    }
    
    function saveNewExemption() {
        var senderId = document.getElementById('exemption-senderid').value.trim().toUpperCase();
        var senderType = document.getElementById('exemption-type').value;
        var category = document.getElementById('exemption-category').value;
        var expiry = document.getElementById('exemption-expiry').value;
        var notes = document.getElementById('exemption-notes').value.trim();
        var scopeRadio = document.querySelector('input[name="exemption-scope"]:checked');
        var scope = scopeRadio ? scopeRadio.value : 'global';
        
        var validation = validateSenderIdForType(senderId, senderType);
        if (!validation.valid) {
            alert(validation.error);
            return;
        }
        senderId = validation.normalised;
        
        var accountId = 'global';
        var accountName = 'All Accounts';
        var selectedSubAccounts = [];
        
        if (scope === 'account' || scope === 'subaccount') {
            accountId = document.getElementById('exemption-account').value;
            if (!accountId) {
                alert('Please select an account');
                return;
            }
            var accountSelect = document.getElementById('exemption-account');
            accountName = accountSelect.options[accountSelect.selectedIndex].text.split(' (')[0];
            
            if (scope === 'subaccount') {
                var allSubsChecked = document.getElementById('exemption-all-subaccounts').checked;
                if (allSubsChecked) {
                    selectedSubAccounts = (mockSubAccounts[accountId] || []).map(function(s) { return s.id; });
                } else {
                    document.querySelectorAll('.exemption-subaccount-cb:checked').forEach(function(cb) {
                        selectedSubAccounts.push(cb.value);
                    });
                }
                if (selectedSubAccounts.length === 0) {
                    alert('Please select at least one sub-account or check "All sub-accounts"');
                    return;
                }
            }
        }
        
        var existingApproval = mockData.senderIdApprovals.find(function(a) {
            return a.normalisedValue === senderId && 
                   (accountId === 'global' || a.accountId === accountId);
        });
        if (existingApproval) {
            alert('A SenderID approval already exists for ' + senderId + '. Use the existing approval record instead.');
            return;
        }
        
        var existingManual = mockData.manualExemptions.find(function(m) {
            return m.normalisedValue === senderId && 
                   (m.accountId === 'global' || accountId === 'global' || m.accountId === accountId);
        });
        if (existingManual) {
            alert('A manual exemption already exists for ' + senderId);
            return;
        }
        
        var newManualExemption = {
            id: 'MAN-' + String(mockData.manualExemptions.length + 1).padStart(3, '0'),
            senderId: senderId,
            type: senderType,
            normalisedValue: normaliseSenderId(senderId),
            accountId: scope === 'global' ? 'global' : accountId,
            accountName: scope === 'global' ? 'All Accounts' : accountName,
            scope: scope,
            subAccounts: selectedSubAccounts,
            category: category,
            addedBy: currentAdmin.email,
            addedAt: formatDateTime(new Date()),
            updatedAt: formatDateTime(new Date()),
            expiry: expiry ? expiry.split('-').reverse().join('-') + ' 00:00' : null,
            notes: notes || 'Manual exemption added by admin'
        };
        
        mockData.manualExemptions.push(newManualExemption);
        rebuildExemptions();
        
        logAuditEvent('MANUAL_EXEMPTION_CREATED', { 
            exemptionId: newManualExemption.id, 
            senderId: senderId, 
            type: senderType,
            scope: scope,
            subAccounts: selectedSubAccounts,
            normalisedValue: newManualExemption.normalisedValue,
            accountId: accountId 
        });
        
        bootstrap.Modal.getInstance(document.getElementById('addSenderIdExemptionModal')).hide();
        renderExemptionsTab();
        showToast('Manual exemption added for ' + senderId, 'success');
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
    
    function updateQuarantineFilterChips() {
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
        
        // Anti-spam collapse icon toggle
        var antispamCollapse = document.getElementById('antispam-collapse');
        if (antispamCollapse) {
            antispamCollapse.addEventListener('show.bs.collapse', function() {
                document.getElementById('antispam-collapse-icon').style.transform = 'rotate(180deg)';
            });
            antispamCollapse.addEventListener('hide.bs.collapse', function() {
                document.getElementById('antispam-collapse-icon').style.transform = 'rotate(0deg)';
            });
        }
        
        // Content Sub-tabs styling
        var contentSubTabs = document.querySelectorAll('#contentSubTabs .nav-link');
        contentSubTabs.forEach(function(tab) {
            tab.addEventListener('shown.bs.tab', function(e) {
                contentSubTabs.forEach(function(t) {
                    t.style.color = '#6c757d';
                });
                e.target.style.color = '#1e3a5f';
            });
        });
        
        // URL Sub-tabs styling
        var urlSubTabs = document.querySelectorAll('#urlSubTabs .nav-link');
        urlSubTabs.forEach(function(tab) {
            tab.addEventListener('shown.bs.tab', function(e) {
                urlSubTabs.forEach(function(t) {
                    t.style.color = '#6c757d';
                });
                e.target.style.color = '#1e3a5f';
            });
        });
        
        // Normalisation tab - ensure grid renders when tab is shown
        var normTabBtn = document.getElementById('normalisation-rules-tab');
        if (normTabBtn) {
            normTabBtn.addEventListener('shown.bs.tab', function() {
                console.log('[NormTab] Tab shown - triggering render');
                renderNormTab();
            });
        }
        
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.action-menu-container')) {
                document.querySelectorAll('.action-menu-dropdown').forEach(function(menu) {
                    menu.classList.remove('show');
                });
            }
            // Close account dropdown when clicking outside
            if (!e.target.closest('#content-exemption-account-dropdown') && 
                !e.target.closest('#content-exemption-account-search')) {
                var dropdown = document.getElementById('content-exemption-account-dropdown');
                if (dropdown) dropdown.classList.remove('show');
            }
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
        var toggle = document.getElementById('antispam-repeat-toggle');
        var newEnabled = toggle.checked;
        var wasEnabled = mockData.antiSpamSettings.preventRepeatContent;
        var currentWindow = mockData.antiSpamSettings.windowHours || 60;
        
        // Revert toggle until confirmed
        toggle.checked = wasEnabled;
        
        var actionText = newEnabled ? 'Enable' : 'Disable';
        var statusBefore = wasEnabled ? '<span class="badge bg-success">Enabled</span>' : '<span class="badge bg-secondary">Disabled</span>';
        var statusAfter = newEnabled ? '<span class="badge bg-success">Enabled</span>' : '<span class="badge bg-secondary">Disabled</span>';
        
        var windowSelectHtml = newEnabled ? 
            '<select class="form-select form-select-sm" id="confirm-antispam-window" style="width: auto; font-size: 0.8rem;">' +
                '<option value="15"' + (currentWindow == 15 ? ' selected' : '') + '>15 min</option>' +
                '<option value="30"' + (currentWindow == 30 ? ' selected' : '') + '>30 min</option>' +
                '<option value="60"' + (currentWindow == 60 ? ' selected' : '') + '>60 min</option>' +
                '<option value="120"' + (currentWindow == 120 ? ' selected' : '') + '>120 min</option>' +
            '</select>' : 
            '<span class="text-muted">' + currentWindow + ' min (N/A when disabled)</span>';
        
        var modalHtml = 
            '<div class="modal fade" id="confirmAntiSpamToggleModal" tabindex="-1" data-bs-backdrop="static">' +
                '<div class="modal-dialog modal-dialog-centered">' +
                    '<div class="modal-content">' +
                        '<div class="modal-header py-2" style="background: #f8f9fa; border-bottom: 1px solid #e9ecef;">' +
                            '<h6 class="modal-title" style="font-size: 0.9rem; color: #1e3a5f;"><i class="fas fa-shield-alt me-2"></i>Confirm Anti-Spam Protection Change</h6>' +
                            '<button type="button" class="btn-close" data-bs-dismiss="modal" style="font-size: 0.7rem;"></button>' +
                        '</div>' +
                        '<div class="modal-body py-3">' +
                            '<p style="font-size: 0.85rem; margin-bottom: 1rem;">You are about to ' + actionText.toLowerCase() + ' Anti-Spam Repeat Content Protection. This change will apply globally.</p>' +
                            '<table class="table table-sm mb-0" style="font-size: 0.8rem;">' +
                                '<tr><td style="width: 40%; font-weight: 600; padding: 0.4rem;">Setting</td><td style="padding: 0.4rem;">Anti-Spam Protection</td></tr>' +
                                '<tr><td style="font-weight: 600; padding: 0.4rem;">Before</td><td style="padding: 0.4rem;">' + statusBefore + '</td></tr>' +
                                '<tr><td style="font-weight: 600; padding: 0.4rem;">After</td><td style="padding: 0.4rem;">' + statusAfter + '</td></tr>' +
                                '<tr><td style="font-weight: 600; padding: 0.4rem;">Window</td><td style="padding: 0.4rem;">' + windowSelectHtml + '</td></tr>' +
                            '</table>' +
                        '</div>' +
                        '<div class="modal-footer py-2" style="border-top: 1px solid #e9ecef;">' +
                            '<button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal" style="font-size: 0.8rem;">Cancel</button>' +
                            '<button type="button" class="btn btn-sm text-white" style="background: #1e3a5f; font-size: 0.8rem;" onclick="confirmAntiSpamToggle(' + newEnabled + ')"><i class="fas fa-check me-1"></i>Confirm ' + actionText + '</button>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
            '</div>';
        
        // Remove existing modal if any
        var existingModal = document.getElementById('confirmAntiSpamToggleModal');
        if (existingModal) existingModal.remove();
        
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        var modal = new bootstrap.Modal(document.getElementById('confirmAntiSpamToggleModal'));
        modal.show();
    }
    
    function confirmAntiSpamToggle(enabled) {
        var modal = bootstrap.Modal.getInstance(document.getElementById('confirmAntiSpamToggleModal'));
        
        // Get window value from modal if enabling
        var windowHours = mockData.antiSpamSettings.windowHours;
        if (enabled) {
            var windowSelect = document.getElementById('confirm-antispam-window');
            if (windowSelect) {
                windowHours = parseInt(windowSelect.value) || 60;
            }
        }
        
        if (modal) modal.hide();
        
        mockData.antiSpamSettings.preventRepeatContent = enabled;
        mockData.antiSpamSettings.windowHours = windowHours;
        mockData.antiSpamSettings.lastUpdated = formatDateTime(new Date());
        mockData.antiSpamSettings.updatedBy = currentAdmin.email;
        
        document.getElementById('antispam-repeat-toggle').checked = enabled;
        document.getElementById('antispam-window').value = windowHours;
        document.getElementById('antispam-window').disabled = !enabled;
        
        logAuditEvent('ANTISPAM_REPEAT_CONTENT_TOGGLED', {
            enabled: enabled,
            windowHours: windowHours,
            admin: currentAdmin.email
        });
        
        if (window.MessageEnforcementService) {
            window.MessageEnforcementService.updateAntiSpamSettings({
                preventRepeatContent: enabled,
                windowHours: windowHours
            });
        }
        
        renderAntiSpamControls();
        showToast(enabled ? 'Anti-spam repeat content protection enabled (' + windowHours + ' min window)' : 'Anti-spam repeat content protection disabled', enabled ? 'success' : 'info');
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
        renderQuarantineTab: renderQuarantineTab,
        updateQuarantineFilterChips: updateQuarantineFilterChips,
        toggleSenderIdFilterPanel: toggleSenderIdFilterPanel,
        toggleExemptionsFilterPanel: toggleExemptionsFilterPanel,
        showAddSenderIdExemptionModal: showAddSenderIdExemptionModal,
        saveNewExemption: saveNewExemption,
        applyExemptionsFilters: applyExemptionsFilters,
        resetExemptionsFilters: resetExemptionsFilters,
        filterExemptionsTable: filterExemptionsTable,
        viewExemption: viewExemption,
        editExemption: editExemption,
        revokeExemption: revokeExemption,
        showAddContentRuleModal: showAddContentRuleModal,
        editContentRule: editContentRule,
        viewContentRule: viewContentRule,
        toggleContentRuleStatus: toggleContentRuleStatus,
        deleteContentRule: deleteContentRule,
        deleteContentRuleById: deleteContentRuleById,
        saveContentRule: saveContentRule,
        confirmSaveContentRule: confirmSaveContentRule,
        showContentRuleToast: showContentRuleToast,
        updateContentMatchInputLabel: updateContentMatchInputLabel,
        resetContentFilters: resetContentFilters,
        toggleContentFilterPanel: toggleContentFilterPanel,
        applyContentFilters: applyContentFilters,
        toggleContentActionMenu: toggleContentActionMenu,
        setupContentTabListeners: setupContentTabListeners,
        toggleAntiSpamRepeat: toggleAntiSpamRepeat,
        confirmAntiSpamToggle: confirmAntiSpamToggle,
        updateAntiSpamWindow: updateAntiSpamWindow,
        renderAntiSpamControls: renderAntiSpamControls,
        // Content Exemptions
        showAddContentExemptionModal: showAddContentExemptionModal,
        viewContentExemption: viewContentExemption,
        editContentExemption: editContentExemption,
        toggleContentExemptionStatus: toggleContentExemptionStatus,
        deleteContentExemption: deleteContentExemption,
        executeDeleteContentExemption: executeDeleteContentExemption,
        saveContentExemption: saveContentExemption,
        toggleContentExemptionType: toggleContentExemptionType,
        updateAntispamOverrideWindow: updateAntispamOverrideWindow,
        filterContentExemptionAccounts: filterContentExemptionAccounts,
        showContentExemptionAccountDropdown: showContentExemptionAccountDropdown,
        selectContentExemptionAccount: selectContentExemptionAccount,
        toggleAllSubaccounts: toggleAllSubaccounts,
        handleSubaccountChange: handleSubaccountChange,
        updateAntispamModeOptions: updateAntispamModeOptions,
        loadContentExemptionSubaccounts: loadContentExemptionSubaccounts,
        toggleContentExemptionActionMenu: toggleContentExemptionActionMenu,
        toggleContentExemptionsFilterPanel: toggleContentExemptionsFilterPanel,
        filterContentExemptionsTable: filterContentExemptionsTable,
        applyContentExemptionsFilters: applyContentExemptionsFilters,
        resetContentExemptionsFilters: resetContentExemptionsFilters,
        removeContentExemptionsFilter: removeContentExemptionsFilter,
        updateContentExemptionsFilterChips: updateContentExemptionsFilterChips,
        showAddUrlRuleModal: showAddUrlRuleModal,
        editUrlRule: editUrlRule,
        viewUrlRule: viewUrlRule,
        toggleUrlRuleStatus: toggleUrlRuleStatus,
        deleteUrlRule: deleteUrlRule,
        deleteUrlRuleById: deleteUrlRuleById,
        confirmSaveUrlRule: confirmSaveUrlRule,
        executeSaveUrlRule: executeSaveUrlRule,
        runUrlRuleTest: runUrlRuleTest,
        extractHostname: extractHostname,
        matchUrlPattern: matchUrlPattern,
        updateUrlPatternLabel: updateUrlPatternLabel,
        resetUrlFilters: resetUrlFilters,
        toggleUrlControlsFilterPanel: toggleUrlControlsFilterPanel,
        updateUrlControlsFilterPanelContext: updateUrlControlsFilterPanelContext,
        populateUrlControlsAccountFilter: populateUrlControlsAccountFilter,
        applyUrlControlsFilters: applyUrlControlsFilters,
        resetUrlControlsFilters: resetUrlControlsFilters,
        toggleUrlActionMenu: toggleUrlActionMenu,
        setupUrlTabListeners: setupUrlTabListeners,
        saveDomainAgeSettings: saveDomainAgeSettings,
        initDomainAgeSettings: initDomainAgeSettings,
        updateDomainAgeStatusBadge: updateDomainAgeStatusBadge,
        toggleDomainAgeFields: toggleDomainAgeFields,
        cancelDomainAgeSettings: cancelDomainAgeSettings,
        confirmSaveDomainAgeSettings: confirmSaveDomainAgeSettings,
        executeSaveDomainAgeSettings: executeSaveDomainAgeSettings,
        updateDomainAgeMeta: updateDomainAgeMeta,
        showDomainAgeSaveError: showDomainAgeSaveError,
        hideDomainAgeSaveError: hideDomainAgeSaveError,
        retryDomainAgeSave: retryDomainAgeSave,
        renderDomainAgeExemptionPreviews: renderDomainAgeExemptionPreviews,
        renderDomainAllowlistPreview: renderDomainAllowlistPreview,
        renderThresholdOverridesPreview: renderThresholdOverridesPreview,
        showAddDomainAllowlistModal: showAddDomainAllowlistModal,
        toggleAllowlistAccountSelect: toggleAllowlistAccountSelect,
        saveAddDomainAllowlist: saveAddDomainAllowlist,
        showAddThresholdOverrideModal: showAddThresholdOverrideModal,
        saveAddThresholdOverride: saveAddThresholdOverride,
        viewAllDomainAgeExemptions: viewAllDomainAgeExemptions,
        showAddUrlExemptionGlobalModal: showAddUrlExemptionGlobalModal,
        toggleGlobalExemptionType: toggleGlobalExemptionType,
        toggleGlobalExemptionSubaccounts: toggleGlobalExemptionSubaccounts,
        toggleGlobalExemptionDomainAgeMode: toggleGlobalExemptionDomainAgeMode,
        handleDomainChipInput: handleDomainChipInput,
        handleDomainPaste: handleDomainPaste,
        addDomainChip: addDomainChip,
        removeDomainChip: removeDomainChip,
        toggleSelectAllUrlRules: toggleSelectAllUrlRules,
        filterGlobalExemptionAccounts: filterGlobalExemptionAccounts,
        selectGlobalExemptionAccount: selectGlobalExemptionAccount,
        saveGlobalUrlExemption: saveGlobalUrlExemption,
        showAddDomainAgeExceptionModal: showAddDomainAgeExceptionModal,
        saveException: saveException,
        removeDomainAgeException: removeDomainAgeException,
        // URL Exemptions
        renderUrlExemptionsTab: renderUrlExemptionsTab,
        populateUrlExemptionsAccountFilter: populateUrlExemptionsAccountFilter,
        parseExemptionDate: parseExemptionDate,
        sortUrlExemptionsTable: sortUrlExemptionsTable,
        viewUrlExemptionDetails: viewUrlExemptionDetails,
        showAddUrlExemptionModal: showAddUrlExemptionModal,
        editUrlExemption: editUrlExemption,
        toggleUrlExemptionStatus: toggleUrlExemptionStatus,
        deleteUrlExemption: deleteUrlExemption,
        toggleUrlExemptionActionMenu: toggleUrlExemptionActionMenu,
        resetUrlExemptionsFilters: resetUrlExemptionsFilters,
        applyUrlExemptionsFilters: applyUrlExemptionsFilters,
        toggleUrlExemptionType: toggleUrlExemptionType,
        populateUrlRulesChecklist: populateUrlRulesChecklist,
        saveUrlExemption: saveUrlExemption,
        filterUrlExemptionAccounts: filterUrlExemptionAccounts,
        selectUrlExemptionAccount: selectUrlExemptionAccount,
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

// Mark that initialization has occurred to prevent duplicate init
var _sccInitialized = false;

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
var MAX_EQUIVALENTS = 50;

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
    if (!base) return;
    
    var char = mockData.baseCharacterLibrary.find(function(c) { return c.base === base; });
    if (!char) return;
    
    var title = '<i class="fas fa-edit me-2"></i>Edit Normalisation: <code style="background: rgba(255,255,255,0.2); padding: 4px 8px; border-radius: 4px; font-size: 1.3rem; font-weight: 700;">' + base + '</code>';
    
    var existingEquivalents = char.equivalents || [];
    var existingNotes = char.notes || '';
    var existingEnabled = char.enabled;
    
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
                    '<input type="hidden" id="normRuleBaseHidden" value="' + base + '">' +
                    
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
    
    if (!char || char.length === 0) {
        return { valid: false, errors: ['Empty character not allowed'], silent: true };
    }
    
    if (/^\s+$/.test(char)) {
        errors.push('Whitespace characters are not allowed');
        return { valid: false, errors: errors };
    }
    
    if (/\s/.test(char)) {
        errors.push('Characters containing whitespace are not allowed');
        return { valid: false, errors: errors };
    }
    
    var codePoints = Array.from(char);
    if (codePoints.length > 1) {
        errors.push('Multi-character tokens are not allowed. Add one character at a time.');
        return { valid: false, errors: errors };
    }
    
    var baseHidden = document.getElementById('normRuleBaseHidden');
    var baseChar = baseHidden ? baseHidden.value : null;
    
    if (baseChar && (char === baseChar || char === baseChar.toLowerCase() || char === baseChar.toUpperCase())) {
        return { valid: false, errors: ['Base letter itself is implied and not needed'], silent: true };
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
        var uniqueChars = [];
        var seenChars = {};
        chars.forEach(function(char) {
            if (!/\s/.test(char) && !seenChars[char]) {
                seenChars[char] = true;
                uniqueChars.push(char);
            }
        });
        
        var added = 0;
        var lastError = null;
        uniqueChars.forEach(function(char) {
            var result = addEquivCharacter(char);
            if (result.success) {
                added++;
            } else if (result.error) {
                lastError = result.error;
            }
        });
        
        input.value = '';
        if (added > 0) {
            hideEquivError();
            if (added > 1) {
                showToast('Added ' + added + ' character(s)', 'success');
            }
        } else if (lastError) {
            showEquivError(lastError);
        }
    }
}

function handleEquivPaste(event) {
    event.preventDefault();
    var pastedText = (event.clipboardData || window.clipboardData).getData('text');
    if (!pastedText) return;
    
    var chars = Array.from(pastedText);
    var uniqueChars = [];
    var seenChars = {};
    
    chars.forEach(function(char) {
        if (!/\s/.test(char) && !seenChars[char]) {
            seenChars[char] = true;
            uniqueChars.push(char);
        }
    });
    
    var added = 0;
    var skipped = 0;
    var errors = [];
    
    uniqueChars.forEach(function(char) {
        var result = addEquivCharacter(char);
        if (result.success) {
            added++;
        } else {
            skipped++;
            if (result.error && errors.indexOf(result.error) === -1 && !result.silent) {
                errors.push(result.error);
            }
        }
    });
    
    if (added > 0) {
        var msg = 'Added ' + added + ' character' + (added !== 1 ? 's' : '');
        if (skipped > 0) {
            msg += ' (' + skipped + ' skipped)';
        }
        showToast(msg, 'success');
        hideEquivError();
    } else if (errors.length > 0) {
        showEquivError(errors[0]);
    } else if (skipped > 0) {
        showToast('All characters already exist or were invalid', 'info');
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
    var base = originalBase || document.getElementById('normRuleBaseHidden').value;
    if (!base) return;
    
    var char = mockData.baseCharacterLibrary.find(function(c) { return c.base === base; });
    if (!char) return;
    
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
        notes: notes,
        enabled: enabled
    };
    
    var newRisk = computeRisk({ equivalents: equivalents });
    var oldRisk = char.risk || 'none';
    
    if (newRisk === 'high' && oldRisk !== 'high') {
        showHighRiskConfirmModal(pendingChange);
        return;
    }
    
    executeSaveNormRule(pendingChange);
}

function showHighRiskConfirmModal(pendingChange) {
    var reasons = [];
    var digitCount = pendingChange.equivalents.filter(function(eq) { return /[0-9]/.test(eq); }).length;
    var hasPunctuation = pendingChange.equivalents.some(function(eq) { return /[!@#$%^&*(),.?":{}|<>]/.test(eq); });
    if (digitCount >= 2 && hasPunctuation) {
        reasons.push('Contains multiple digit equivalents with punctuation');
    }
    if (pendingChange.equivalents.length > 8) {
        reasons.push('Large number of equivalents (' + pendingChange.equivalents.length + ' total)');
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
        notes: char.notes,
        enabled: char.enabled,
        risk: char.risk || 'none'
    };
    
    char.equivalents = NormalisationRulesConfig.deduplicateEquivalents(changeData.equivalents);
    char.notes = changeData.notes;
    char.enabled = changeData.enabled;
    char.updated = new Date().toLocaleDateString('en-GB').replace(/\//g, '-');
    char.risk = computeRisk(char);
    
    var afterState = {
        equivalents: char.equivalents.slice(),
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
    var equivCount = equivalents.length;
    
    if (equivCount === 0) return 'none';
    
    var hasDigits = equivalents.some(function(eq) { return /[0-9]/.test(eq); });
    var hasPunctuation = equivalents.some(function(eq) { return /[!@#$%^&*(),.?":{}|<>]/.test(eq); });
    var digitCount = equivalents.filter(function(eq) { return /[0-9]/.test(eq); }).length;
    
    if (digitCount >= 2 && hasPunctuation) return 'high';
    if (equivCount > 8) return 'high';
    
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
    showTestNormalisationModal();
}

function showTestNormalisationModal() {
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
                        '<strong>Simulation Only:</strong> This tool tests normalisation and enforcement rules without sending any messages.' +
                    '</div>' +
                    
                    '<div class="mb-4">' +
                        '<label class="form-label fw-bold"><i class="fas fa-cog me-2 text-muted"></i>Test Mode</label>' +
                        '<div class="btn-group w-100" role="group">' +
                            '<input type="radio" class="btn-check" name="normTestMode" id="normTestModeNormalise" value="normalise" checked>' +
                            '<label class="btn btn-outline-primary" for="normTestModeNormalise" style="border-color: #1e3a5f; color: #1e3a5f;" onclick="switchNormTestMode(\'normalise\')">' +
                                '<i class="fas fa-text-height me-1"></i>Normalise Only' +
                            '</label>' +
                            '<input type="radio" class="btn-check" name="normTestMode" id="normTestModeEnforce" value="enforce">' +
                            '<label class="btn btn-outline-primary" for="normTestModeEnforce" style="border-color: #1e3a5f; color: #1e3a5f;" onclick="switchNormTestMode(\'enforce\')">' +
                                '<i class="fas fa-shield-alt me-1"></i>Would Block/Quarantine?' +
                            '</label>' +
                        '</div>' +
                    '</div>' +
                    
                    '<div class="mb-4">' +
                        '<label class="form-label fw-bold"><i class="fas fa-keyboard me-2 text-muted"></i>Input String</label>' +
                        '<input type="text" class="form-control form-control-lg" id="normTestInput" placeholder="Enter text to test..." value="LL0YDS B4NK" style="font-family: monospace; font-size: 1.2rem;">' +
                        '<small class="text-muted">Enter text with potential homoglyphs or character substitutions</small>' +
                    '</div>' +
                    
                    '<div id="normTestEngineSection" class="mb-4" style="display: none;">' +
                        '<label class="form-label fw-bold"><i class="fas fa-bullseye me-2 text-muted"></i>Rule Engine</label>' +
                        '<div class="d-flex gap-2">' +
                            '<button type="button" class="btn norm-engine-btn active" data-engine="senderid" onclick="selectNormTestEngine(this)">' +
                                '<i class="fas fa-id-badge me-1"></i>SenderID' +
                            '</button>' +
                            '<button type="button" class="btn norm-engine-btn" data-engine="content" onclick="selectNormTestEngine(this)">' +
                                '<i class="fas fa-comment-alt me-1"></i>Content' +
                            '</button>' +
                            '<button type="button" class="btn norm-engine-btn" data-engine="url" onclick="selectNormTestEngine(this)">' +
                                '<i class="fas fa-link me-1"></i>URL' +
                            '</button>' +
                        '</div>' +
                        '<small class="text-muted">Select which engine\'s rules to evaluate against</small>' +
                    '</div>' +
                    
                    '<button class="btn btn-lg w-100" onclick="runNormalisationTest()" style="background: #1e3a5f; border-color: #1e3a5f; color: white;">' +
                        '<i class="fas fa-play me-1"></i>Run Test' +
                    '</button>' +
                    
                    '<div id="normTestResults" style="display: none; margin-top: 1.5rem;">' +
                        '<hr>' +
                        
                        '<div id="normTestNormaliseResults">' +
                            '<h6 class="mb-3"><i class="fas fa-text-height me-2"></i>Normalisation Results</h6>' +
                            
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
                                            '<div class="text-muted small mb-1">Canonicalised Output</div>' +
                                            '<div id="normTestNormalised" style="font-family: monospace; font-size: 1.3rem; letter-spacing: 2px; color: #1e3a5f;"></div>' +
                                        '</div>' +
                                    '</div>' +
                                '</div>' +
                            '</div>' +
                            
                            '<div class="card border-0 mb-4" style="background: #fafbfc;">' +
                                '<div class="card-header bg-transparent border-bottom" style="font-weight: 600;">' +
                                    '<i class="fas fa-exchange-alt me-2 text-muted"></i>Character Mappings' +
                                '</div>' +
                                '<div class="card-body">' +
                                    '<div id="normTestSubstitutions"></div>' +
                                '</div>' +
                            '</div>' +
                        '</div>' +
                        
                        '<div id="normTestEnforceResults" style="display: none;">' +
                            '<h6 class="mb-3"><i class="fas fa-shield-alt me-2"></i>Enforcement Evaluation</h6>' +
                            
                            '<div id="normTestDecisionBanner" class="mb-4"></div>' +
                            
                            '<div class="row g-3 mb-4">' +
                                '<div class="col-md-6">' +
                                    '<div class="card border-0 h-100" style="background: #f8fafc;">' +
                                        '<div class="card-body">' +
                                            '<div class="text-muted small mb-1">Original Input</div>' +
                                            '<div id="normTestEnforceOriginal" style="font-family: monospace; font-size: 1.1rem;"></div>' +
                                        '</div>' +
                                    '</div>' +
                                '</div>' +
                                '<div class="col-md-6">' +
                                    '<div class="card border-0 h-100" style="background: #e8f4fd;">' +
                                        '<div class="card-body">' +
                                            '<div class="text-muted small mb-1">After Normalisation</div>' +
                                            '<div id="normTestEnforceNormalised" style="font-family: monospace; font-size: 1.1rem; color: #1e3a5f;"></div>' +
                                        '</div>' +
                                    '</div>' +
                                '</div>' +
                            '</div>' +
                            
                            '<div class="card border-0 mb-4" style="background: #fafbfc;">' +
                                '<div class="card-header bg-transparent border-bottom" style="font-weight: 600;">' +
                                    '<i class="fas fa-list-check me-2 text-muted"></i>Enforcement Steps' +
                                '</div>' +
                                '<div class="card-body">' +
                                    '<div id="normTestEnforceSteps"></div>' +
                                '</div>' +
                            '</div>' +
                            
                            '<div id="normTestMatchedRuleSection" class="card border-0" style="background: #fafbfc; display: none;">' +
                                '<div class="card-header bg-transparent border-bottom" style="font-weight: 600;">' +
                                    '<i class="fas fa-gavel me-2 text-muted"></i>Matched Rule Details' +
                                '</div>' +
                                '<div class="card-body">' +
                                    '<div id="normTestMatchedRuleDetails"></div>' +
                                '</div>' +
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
    
    addNormTestStyles();
    
    var modal = new bootstrap.Modal(document.getElementById('testNormalisationModal'));
    modal.show();
    
    document.getElementById('normTestInput').focus();
}

function addNormTestStyles() {
    if (document.getElementById('normTestModalStyles')) return;
    
    var styleHtml = '<style id="normTestModalStyles">' +
        '.btn-check:checked + .btn-outline-primary { background: #1e3a5f !important; border-color: #1e3a5f !important; color: white !important; }' +
        '.norm-engine-btn { background: white; border: 1px solid #dee2e6; padding: 0.5rem 1rem; transition: all 0.2s; }' +
        '.norm-engine-btn:hover { border-color: #1e3a5f; color: #1e3a5f; }' +
        '.norm-engine-btn.active { background: #1e3a5f; border-color: #1e3a5f; color: white; }' +
        '.norm-decision-allow { background: #d1fae5; border: 2px solid #10b981; border-radius: 8px; padding: 1rem; }' +
        '.norm-decision-block { background: #fee2e2; border: 2px solid #dc3545; border-radius: 8px; padding: 1rem; }' +
        '.norm-decision-quarantine { background: #fef3c7; border: 2px solid #f59e0b; border-radius: 8px; padding: 1rem; }' +
        '.norm-step { display: flex; align-items: flex-start; padding: 0.75rem; border-left: 3px solid #dee2e6; margin-bottom: 0.5rem; background: #f8fafc; border-radius: 0 4px 4px 0; }' +
        '.norm-step.step-pass { border-left-color: #10b981; }' +
        '.norm-step.step-fail { border-left-color: #dc3545; }' +
        '.norm-step.step-warn { border-left-color: #f59e0b; }' +
        '.norm-step-icon { width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 0.75rem; flex-shrink: 0; font-size: 0.75rem; }' +
        '.norm-step.step-pass .norm-step-icon { background: #d1fae5; color: #065f46; }' +
        '.norm-step.step-fail .norm-step-icon { background: #fee2e2; color: #991b1b; }' +
        '.norm-step.step-warn .norm-step-icon { background: #fef3c7; color: #92400e; }' +
        '.norm-mapping-chip { display: inline-flex; align-items: center; background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 20px; padding: 0.25rem 0.75rem; margin: 0.25rem; font-family: monospace; }' +
        '.norm-mapping-original { background: #fef3c7; padding: 0.125rem 0.375rem; border-radius: 4px; font-weight: 600; }' +
        '.norm-mapping-base { background: #d1fae5; padding: 0.125rem 0.375rem; border-radius: 4px; font-weight: 600; color: #065f46; }' +
        '.norm-mapping-codepoint { font-size: 0.7rem; color: #6c757d; margin-left: 0.5rem; }' +
    '</style>';
    
    document.head.insertAdjacentHTML('beforeend', styleHtml);
}

function switchNormTestMode(mode) {
    var engineSection = document.getElementById('normTestEngineSection');
    if (mode === 'enforce') {
        engineSection.style.display = 'block';
    } else {
        engineSection.style.display = 'none';
    }
    document.getElementById('normTestResults').style.display = 'none';
}

function selectNormTestEngine(btn) {
    document.querySelectorAll('.norm-engine-btn').forEach(function(b) {
        b.classList.remove('active');
    });
    btn.classList.add('active');
}

function runNormalisationTest() {
    var input = document.getElementById('normTestInput').value;
    var mode = document.querySelector('input[name="normTestMode"]:checked').value;
    
    if (!input) {
        showToast('Please enter text to test', 'warning');
        return;
    }
    
    if (mode === 'normalise') {
        runNormaliseOnlyTest(input);
    } else {
        var engineBtn = document.querySelector('.norm-engine-btn.active');
        var engine = engineBtn ? engineBtn.getAttribute('data-engine') : 'senderid';
        runEnforcementTest(input, engine);
    }
    
    document.getElementById('normTestResults').style.display = 'block';
}

function runNormaliseOnlyTest(input) {
    document.getElementById('normTestNormaliseResults').style.display = 'block';
    document.getElementById('normTestEnforceResults').style.display = 'none';
    document.getElementById('normTestSubstitutions').innerHTML = '<div class="text-center text-muted"><i class="fas fa-spinner fa-spin me-2"></i>Loading...</div>';
    
    fetch('/admin/enforcement/normalise', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: JSON.stringify({ input: input })
    })
    .then(function(response) {
        if (!response.ok) throw new Error('API request failed');
        return response.json();
    })
    .then(function(data) {
        renderNormaliseResults(input, data);
    })
    .catch(function(error) {
        console.warn('[NormaliseTest] API call failed, falling back to client-side:', error);
        var result = NormalisationEnforcementAPI.normalise(input);
        var mappingHits = result.substitutions ? result.substitutions.map(function(s) {
            return { from: s.original, to: s.base, index: s.position, base: s.base };
        }) : [];
        renderNormaliseResults(input, { normalised: result.normalised, mappingHits: mappingHits });
    });
}

function renderNormaliseResults(input, data) {
    var normalised = data.normalised || input;
    var mappingHits = data.mappingHits || [];
    
    var highlightedOriginal = escapeHtml(input);
    var highlightedNormalised = escapeHtml(normalised);
    
    if (mappingHits.length > 0) {
        var originalChars = Array.from(input);
        var normalisedChars = Array.from(normalised);
        var mappingsByIndex = {};
        mappingHits.forEach(function(m) { mappingsByIndex[m.index] = m; });
        
        highlightedOriginal = '';
        highlightedNormalised = '';
        
        for (var i = 0; i < originalChars.length; i++) {
            if (mappingsByIndex[i]) {
                highlightedOriginal += '<span class="norm-highlight-sub">' + escapeHtml(originalChars[i]) + '</span>';
                highlightedNormalised += '<span class="norm-highlight-base">' + escapeHtml(mappingsByIndex[i].to) + '</span>';
            } else {
                highlightedOriginal += escapeHtml(originalChars[i]);
                highlightedNormalised += escapeHtml(normalisedChars[i] || '');
            }
        }
    }
    
    document.getElementById('normTestOriginal').innerHTML = highlightedOriginal;
    document.getElementById('normTestNormalised').innerHTML = highlightedNormalised;
    
    var subsHtml = '';
    if (mappingHits.length > 0) {
        subsHtml = '<div class="d-flex flex-wrap">';
        mappingHits.forEach(function(m) {
            var codepoint = m.from.codePointAt(0).toString(16).toUpperCase().padStart(4, '0');
            var encoding = getCharEncoding(m.from);
            subsHtml += '<div class="norm-mapping-chip">' +
                '<span class="norm-mapping-original">' + escapeHtml(m.from) + '</span>' +
                '<i class="fas fa-arrow-right mx-2 text-muted" style="font-size: 0.7rem;"></i>' +
                '<span class="norm-mapping-base">' + escapeHtml(m.to) + '</span>' +
                '<span class="norm-mapping-codepoint">U+' + codepoint + ' (' + encoding + ')</span>' +
            '</div>';
        });
        subsHtml += '</div>';
    } else {
        subsHtml = '<div class="text-muted"><i class="fas fa-check-circle me-2 text-success"></i>No character substitutions needed - input is already canonical</div>';
    }
    document.getElementById('normTestSubstitutions').innerHTML = subsHtml;
    
    logAuditEvent('NORMALISATION_TEST_RUN', {
        entityType: 'normalisation_test',
        mode: 'normalise_only',
        input: input,
        output: normalised,
        substitutionsCount: mappingHits.length
    });
}

function runEnforcementTest(input, engine) {
    document.getElementById('normTestNormaliseResults').style.display = 'none';
    document.getElementById('normTestEnforceResults').style.display = 'block';
    document.getElementById('normTestDecisionBanner').innerHTML = '<div class="text-center text-muted p-4"><i class="fas fa-spinner fa-spin me-2"></i>Evaluating enforcement rules...</div>';
    document.getElementById('normTestEnforceSteps').innerHTML = '';
    document.getElementById('normTestMatchedRuleSection').style.display = 'none';
    
    fetch('/admin/enforcement/test', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: JSON.stringify({ engine: engine, input: input })
    })
    .then(function(response) {
        if (!response.ok) throw new Error('API request failed');
        return response.json();
    })
    .then(function(data) {
        renderEnforcementResults(input, engine, data);
    })
    .catch(function(error) {
        console.warn('[EnforcementTest] API call failed, falling back to client-side:', error);
        var normResult = NormalisationEnforcementAPI.normalise(input);
        var enforcementResult = evaluateEnforcementRules(normResult.normalised, engine);
        var mappingHits = normResult.substitutions ? normResult.substitutions.map(function(s) {
            return { from: s.original, to: s.base, index: s.position, base: s.base };
        }) : [];
        renderEnforcementResults(input, engine, {
            normalised: normResult.normalised,
            result: enforcementResult.decision.toLowerCase(),
            matchedRule: enforcementResult.matchedRule,
            mappingHits: mappingHits
        });
    });
}

function renderEnforcementResults(input, engine, data) {
    var normalised = data.normalised || input;
    var result = (data.result || 'allow').toUpperCase();
    var matchedRule = data.matchedRule;
    var mappingHits = data.mappingHits || [];
    
    var decisionHtml = '';
    if (result === 'ALLOW') {
        decisionHtml = '<div class="norm-decision-allow d-flex align-items-center">' +
            '<div class="me-3"><i class="fas fa-check-circle fa-2x" style="color: #065f46;"></i></div>' +
            '<div>' +
                '<div class="fw-bold fs-5" style="color: #065f46;">ALLOW</div>' +
                '<div class="text-muted">This message would be allowed through the ' + engine.toUpperCase() + ' engine</div>' +
            '</div>' +
        '</div>';
    } else if (result === 'BLOCK') {
        decisionHtml = '<div class="norm-decision-block d-flex align-items-center">' +
            '<div class="me-3"><i class="fas fa-ban fa-2x" style="color: #991b1b;"></i></div>' +
            '<div>' +
                '<div class="fw-bold fs-5" style="color: #991b1b;">BLOCK</div>' +
                '<div class="text-muted">This message would be blocked by the ' + engine.toUpperCase() + ' engine</div>' +
                (matchedRule ? '<div class="mt-1"><strong>Rule:</strong> ' + matchedRule.name + '</div>' : '') +
            '</div>' +
        '</div>';
    } else if (result === 'QUARANTINE') {
        decisionHtml = '<div class="norm-decision-quarantine d-flex align-items-center">' +
            '<div class="me-3"><i class="fas fa-exclamation-triangle fa-2x" style="color: #92400e;"></i></div>' +
            '<div>' +
                '<div class="fw-bold fs-5" style="color: #92400e;">QUARANTINE</div>' +
                '<div class="text-muted">This message would be quarantined for review</div>' +
                (matchedRule ? '<div class="mt-1"><strong>Rule:</strong> ' + matchedRule.name + '</div>' : '') +
            '</div>' +
        '</div>';
    }
    document.getElementById('normTestDecisionBanner').innerHTML = decisionHtml;
    
    var highlightedOriginal = escapeHtml(input);
    var highlightedNormalised = escapeHtml(normalised);
    
    if (mappingHits.length > 0) {
        var originalChars = Array.from(input);
        var normalisedChars = Array.from(normalised);
        var mappingsByIndex = {};
        mappingHits.forEach(function(m) { mappingsByIndex[m.index] = m; });
        
        highlightedOriginal = '';
        highlightedNormalised = '';
        
        for (var i = 0; i < originalChars.length; i++) {
            if (mappingsByIndex[i]) {
                highlightedOriginal += '<span class="norm-highlight-sub">' + escapeHtml(originalChars[i]) + '</span>';
                highlightedNormalised += '<span class="norm-highlight-base">' + escapeHtml(mappingsByIndex[i].to) + '</span>';
            } else {
                highlightedOriginal += escapeHtml(originalChars[i]);
                highlightedNormalised += escapeHtml(normalisedChars[i] || '');
            }
        }
    }
    
    document.getElementById('normTestEnforceOriginal').innerHTML = highlightedOriginal;
    document.getElementById('normTestEnforceNormalised').innerHTML = highlightedNormalised;
    
    var steps = [
        { name: 'Step 1: Normalisation', description: 'Input canonicalised using character equivalence library (' + mappingHits.length + ' substitutions)', status: 'pass' },
        { name: 'Step 2: Load ' + engine.toUpperCase() + ' Rules', description: 'Rules loaded for ' + engine + ' enforcement engine', status: 'pass' }
    ];
    
    if (matchedRule) {
        steps.push({ name: 'Step 3: Rule Evaluation', description: 'Matched rule: ' + matchedRule.name + ' (ID: ' + matchedRule.id + ')', status: 'fail' });
        steps.push({ name: 'Step 4: Decision', description: result + ' - Pattern "' + (matchedRule.pattern || '') + '" matched', status: result === 'QUARANTINE' ? 'warn' : 'fail' });
    } else {
        steps.push({ name: 'Step 3: Rule Evaluation', description: 'No rules matched the normalised input', status: 'pass' });
        steps.push({ name: 'Step 4: Decision', description: 'ALLOW - No blocking or quarantine rules triggered', status: 'pass' });
    }
    
    var stepsHtml = '';
    steps.forEach(function(step) {
        var stepClass = step.status === 'pass' ? 'step-pass' : (step.status === 'fail' ? 'step-fail' : 'step-warn');
        var stepIcon = step.status === 'pass' ? 'fa-check' : (step.status === 'fail' ? 'fa-times' : 'fa-exclamation');
        stepsHtml += '<div class="norm-step ' + stepClass + '">' +
            '<div class="norm-step-icon"><i class="fas ' + stepIcon + '"></i></div>' +
            '<div>' +
                '<div class="fw-bold">' + step.name + '</div>' +
                '<div class="text-muted small">' + step.description + '</div>' +
            '</div>' +
        '</div>';
    });
    document.getElementById('normTestEnforceSteps').innerHTML = stepsHtml;
    
    if (matchedRule) {
        document.getElementById('normTestMatchedRuleSection').style.display = 'block';
        var ruleHtml = '<div class="p-3" style="background: #f1f5f9; border-radius: 8px;">' +
            '<div class="row">' +
                '<div class="col-md-6 mb-2">' +
                    '<strong>Rule ID:</strong> ' + (matchedRule.id || 'N/A') +
                '</div>' +
                '<div class="col-md-6 mb-2">' +
                    '<strong>Rule Name:</strong> ' + (matchedRule.name || 'Unnamed Rule') +
                '</div>' +
                '<div class="col-md-6 mb-2">' +
                    '<strong>Pattern:</strong> <code>' + escapeHtml(matchedRule.pattern || '') + '</code>' +
                '</div>' +
                '<div class="col-md-6 mb-2">' +
                    '<strong>Action:</strong> <span class="badge ' + (matchedRule.action === 'block' ? 'bg-danger' : 'bg-warning text-dark') + '">' + 
                        (matchedRule.action || 'block').toUpperCase() + 
                    '</span>' +
                '</div>' +
            '</div>' +
            (matchedRule.matchType ? '<div class="mt-2 pt-2 border-top"><strong>Match Type:</strong> ' + matchedRule.matchType + '</div>' : '') +
        '</div>';
        document.getElementById('normTestMatchedRuleDetails').innerHTML = ruleHtml;
    } else {
        document.getElementById('normTestMatchedRuleSection').style.display = 'none';
    }
    
    logAuditEvent('NORMALISATION_TEST_RUN', {
        entityType: 'enforcement_test',
        mode: 'enforcement',
        engine: engine,
        input: input,
        normalised: normalised,
        decision: result,
        matchedRule: matchedRule ? matchedRule.id : null,
        substitutionsCount: mappingHits.length
    });
}

function evaluateEnforcementRules(normalisedInput, engine) {
    var steps = [];
    var decision = 'ALLOW';
    var matchedRule = null;
    var matchExplanation = '';
    
    steps.push({
        name: 'Step 1: Normalisation',
        description: 'Input canonicalised using character equivalence library',
        status: 'pass'
    });
    
    var rules = getEnforcementRulesForEngine(engine);
    
    steps.push({
        name: 'Step 2: Load ' + engine.toUpperCase() + ' Rules',
        description: 'Found ' + rules.length + ' active rules for ' + engine + ' engine',
        status: 'pass'
    });
    
    for (var i = 0; i < rules.length; i++) {
        var rule = rules[i];
        var isMatch = false;
        var matchType = '';
        
        try {
            if (rule.matchType === 'exact') {
                isMatch = normalisedInput.toUpperCase() === rule.pattern.toUpperCase();
                matchType = 'Exact match';
            } else if (rule.matchType === 'contains') {
                isMatch = normalisedInput.toUpperCase().indexOf(rule.pattern.toUpperCase()) !== -1;
                matchType = 'Contains "' + rule.pattern + '"';
            } else if (rule.matchType === 'regex') {
                var regex = new RegExp(rule.pattern, 'i');
                isMatch = regex.test(normalisedInput);
                matchType = 'Regex match';
            } else {
                isMatch = normalisedInput.toUpperCase().indexOf(rule.pattern.toUpperCase()) !== -1;
                matchType = 'Pattern match';
            }
        } catch (e) {
            console.warn('[EnforcementTest] Rule evaluation error:', e);
        }
        
        if (isMatch) {
            matchedRule = rule;
            decision = rule.action === 'quarantine' ? 'QUARANTINE' : 'BLOCK';
            matchExplanation = matchType + ' against rule "' + rule.name + '" (pattern: ' + rule.pattern + ')';
            
            steps.push({
                name: 'Step 3: Rule Evaluation',
                description: 'Matched rule: ' + rule.name + ' (ID: ' + rule.id + ')',
                status: 'fail'
            });
            
            steps.push({
                name: 'Step 4: Decision',
                description: decision + ' - ' + matchExplanation,
                status: decision === 'QUARANTINE' ? 'warn' : 'fail'
            });
            
            break;
        }
    }
    
    if (!matchedRule) {
        steps.push({
            name: 'Step 3: Rule Evaluation',
            description: 'No rules matched the normalised input',
            status: 'pass'
        });
        
        steps.push({
            name: 'Step 4: Decision',
            description: 'ALLOW - No blocking or quarantine rules triggered',
            status: 'pass'
        });
    }
    
    return {
        decision: decision,
        matchedRule: matchedRule,
        matchExplanation: matchExplanation,
        steps: steps
    };
}

function getEnforcementRulesForEngine(engine) {
    var rules = [];
    
    if (engine === 'senderid') {
        rules = [
            { id: 'SID-001', name: 'Block HMRC Impersonation', pattern: 'HMRC', matchType: 'contains', action: 'block' },
            { id: 'SID-002', name: 'Block HSBC Impersonation', pattern: 'HSBC', matchType: 'contains', action: 'block' },
            { id: 'SID-003', name: 'Block LLOYDS Impersonation', pattern: 'LLOYDS', matchType: 'contains', action: 'block' },
            { id: 'SID-004', name: 'Block BARCLAYS Impersonation', pattern: 'BARCLAYS', matchType: 'contains', action: 'block' },
            { id: 'SID-005', name: 'Quarantine GOV Pattern', pattern: 'GOV', matchType: 'contains', action: 'quarantine' },
            { id: 'SID-006', name: 'Block BANK Keyword', pattern: 'BANK', matchType: 'contains', action: 'block' },
            { id: 'SID-007', name: 'Block NHS Impersonation', pattern: 'NHS', matchType: 'exact', action: 'block' },
            { id: 'SID-008', name: 'Block DVLA Impersonation', pattern: 'DVLA', matchType: 'exact', action: 'block' }
        ];
    } else if (engine === 'content') {
        rules = [
            { id: 'CNT-001', name: 'Block Urgent Payment Request', pattern: 'urgent.*payment', matchType: 'regex', action: 'block' },
            { id: 'CNT-002', name: 'Block Account Suspended', pattern: 'account.*suspended', matchType: 'regex', action: 'block' },
            { id: 'CNT-003', name: 'Quarantine Click Link Urgency', pattern: 'click.*link.*now', matchType: 'regex', action: 'quarantine' },
            { id: 'CNT-004', name: 'Block Verify Identity', pattern: 'verify.*identity', matchType: 'regex', action: 'block' },
            { id: 'CNT-005', name: 'Block Tax Refund Scam', pattern: 'tax.*refund', matchType: 'regex', action: 'block' }
        ];
    } else if (engine === 'url') {
        rules = [
            { id: 'URL-001', name: 'Block Bit.ly Shorteners', pattern: 'bit\\.ly', matchType: 'regex', action: 'block' },
            { id: 'URL-002', name: 'Block TinyURL Shorteners', pattern: 'tinyurl\\.com', matchType: 'regex', action: 'block' },
            { id: 'URL-003', name: 'Quarantine Unknown Domains', pattern: '\\.xyz$', matchType: 'regex', action: 'quarantine' },
            { id: 'URL-004', name: 'Block IP-based URLs', pattern: 'http[s]?://\\d+\\.\\d+\\.\\d+\\.\\d+', matchType: 'regex', action: 'block' }
        ];
    }
    
    return rules;
}

function escapeHtml(text) {
    if (!text) return '';
    var div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
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
        audit_logging_enabled: true,
        test_tool_enabled: true
    },
    
    MAX_EQUIVALENTS_PER_CHAR: 50,
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
 * Internal API: GET /internal/normalisation-rules (returns unified normalisation map for all engines)
 * Cached with 60s TTL
 * 
 * Evaluation order:
 * 1) Normalise input using equivalence library
 * 2) Apply SenderID / Content / URL rules to canonical string
 */
var NormalisationEnforcementAPI = (function() {
    var cache = {
        rules: null,
        lastFetch: 0,
        equivIndex: null,
        TTL_MS: NormalisationRulesConfig.CACHE_TTL_MS
    };
    
    function isCacheValid() {
        return (Date.now() - cache.lastFetch) < cache.TTL_MS;
    }
    
    function fetchRules() {
        if (isCacheValid() && cache.rules) {
            console.log('[NormalisationEnforcementAPI] Cache hit (unified normalisation rules)');
            return cache.rules;
        }
        
        console.log('[NormalisationEnforcementAPI] Fetching unified normalisation rules');
        
        var rules = mockData.baseCharacterLibrary.filter(function(rule) {
            return rule.enabled;
        }).map(function(rule) {
            return {
                base: rule.base,
                equivalents: rule.equivalents.slice()
            };
        });
        
        cache.rules = rules;
        cache.lastFetch = Date.now();
        
        console.log('[NormalisationEnforcementAPI] Cached ' + rules.length + ' unified rules (TTL: 60s)');
        
        return rules;
    }
    
    function invalidateCache() {
        cache.rules = null;
        cache.lastFetch = 0;
        cache.equivIndex = null;
        console.log('[NormalisationEnforcementAPI] Cache invalidated');
    }
    
    function buildEquivIndex() {
        if (cache.equivIndex && isCacheValid()) {
            return cache.equivIndex;
        }
        
        var rules = fetchRules();
        var index = {};
        
        rules.forEach(function(rule) {
            rule.equivalents.forEach(function(equiv) {
                var normalized = NormalisationRulesConfig.normalizeUnicode(equiv);
                index[normalized] = rule.base;
            });
        });
        
        cache.equivIndex = index;
        console.log('[NormalisationEnforcementAPI] Built unified equiv index (' + Object.keys(index).length + ' mappings)');
        return index;
    }
    
    function normalise(input) {
        if (!NormalisationRulesConfig.isFeatureEnabled('normalisation_enabled')) {
            return {
                normalised: input,
                substitutions: [],
                highlightedOriginal: input,
                highlightedNormalised: input,
                featureDisabled: true
            };
        }
        
        var equivMap = buildEquivIndex();
        
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
            cacheHit: isCacheValid()
        };
    }
    
    function evaluate(input) {
        var normResult = normalise(input);
        
        var ruleMatches = [];
        
        if (window.MessageEnforcementService) {
            ruleMatches = MessageEnforcementService.evaluateAgainstRules(normResult.normalised);
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
            rulesCount: cache.rules ? cache.rules.length : 0,
            ttlMs: cache.TTL_MS,
            lastFetch: cache.lastFetch,
            cacheValid: isCacheValid()
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

function performNormalisation(input) {
    return NormalisationEnforcementAPI.normalise(input);
}

function findMatchingRules(normalisedText) {
    if (window.MessageEnforcementService) {
        return MessageEnforcementService.evaluateAgainstRules(normalisedText);
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
        var suffix = type === 'digits' ? 'digits' : 'letters';
        var statusEl = document.getElementById('norm-filter-status-' + suffix);
        var riskEl = document.getElementById('norm-filter-risk-' + suffix);
        var searchEl = document.getElementById('norm-search-' + suffix);
        if (statusEl) statusEl.value = '';
        if (riskEl) riskEl.value = '';
        if (searchEl) searchEl.value = '';
        filterBaseCharacters(type);
    } else {
        ['letters', 'digits'].forEach(function(suffix) {
            var statusEl = document.getElementById('norm-filter-status-' + suffix);
            var riskEl = document.getElementById('norm-filter-risk-' + suffix);
            var searchEl = document.getElementById('norm-search-' + suffix);
            if (statusEl) statusEl.value = '';
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
                        '<label class="form-label fw-bold">Apply To</label>' +
                        '<select class="form-control" id="bulkEditTarget">' +
                            '<option value="all">All Characters (36)</option>' +
                            '<option value="letters">Letters Only (26)</option>' +
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
    clearSenderIdValidationState();
    
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
            { id: 'SID-001', name: 'Block HSBC Impersonation', baseSenderId: 'HSBC', ruleType: 'block', category: 'banking_finance', applyNormalisation: true, status: 'active', createdBy: 'admin@quicksms.co.uk', createdAt: '15-01-2026 09:30', updatedAt: '15-01-2026 09:30' },
            { id: 'SID-002', name: 'Block Barclays Impersonation', baseSenderId: 'BARCLAYS', ruleType: 'block', category: 'banking_finance', applyNormalisation: true, status: 'active', createdBy: 'admin@quicksms.co.uk', createdAt: '15-01-2026 10:15', updatedAt: '20-01-2026 14:22' },
            { id: 'SID-003', name: 'Flag HMRC Messages', baseSenderId: 'HMRC', ruleType: 'flag', category: 'government_healthcare', applyNormalisation: true, status: 'active', createdBy: 'compliance@quicksms.co.uk', createdAt: '12-01-2026 11:00', updatedAt: '12-01-2026 11:00' },
            { id: 'SID-004', name: 'Block DPD Sender', baseSenderId: 'DPD', ruleType: 'block', category: 'delivery_logistics', applyNormalisation: true, status: 'active', createdBy: 'admin@quicksms.co.uk', createdAt: '10-01-2026 08:45', updatedAt: '25-01-2026 16:30' },
            { id: 'SID-005', name: 'Flag Generic Sender', baseSenderId: 'INFO', ruleType: 'flag', category: 'generic', applyNormalisation: false, status: 'disabled', createdBy: 'admin@quicksms.co.uk', createdAt: '05-01-2026 14:00', updatedAt: '28-01-2026 09:15' }
        ];
    }
    
    var rule = rules.find(r => r.id === ruleId);
    if (!rule) {
        console.error('Rule not found:', ruleId);
        return;
    }
    
    clearSenderIdValidationState();
    
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
            { id: 'SID-001', name: 'Block HSBC Impersonation', baseSenderId: 'HSBC', ruleType: 'block', category: 'banking_finance', applyNormalisation: true, status: 'active', createdBy: 'admin@quicksms.co.uk', createdAt: '15-01-2026 09:30', updatedAt: '15-01-2026 09:30' },
            { id: 'SID-002', name: 'Block Barclays Impersonation', baseSenderId: 'BARCLAYS', ruleType: 'block', category: 'banking_finance', applyNormalisation: true, status: 'active', createdBy: 'admin@quicksms.co.uk', createdAt: '15-01-2026 10:15', updatedAt: '20-01-2026 14:22' },
            { id: 'SID-003', name: 'Flag HMRC Messages', baseSenderId: 'HMRC', ruleType: 'flag', category: 'government_healthcare', applyNormalisation: true, status: 'active', createdBy: 'compliance@quicksms.co.uk', createdAt: '12-01-2026 11:00', updatedAt: '12-01-2026 11:00' },
            { id: 'SID-004', name: 'Block DPD Sender', baseSenderId: 'DPD', ruleType: 'block', category: 'delivery_logistics', applyNormalisation: true, status: 'active', createdBy: 'admin@quicksms.co.uk', createdAt: '10-01-2026 08:45', updatedAt: '25-01-2026 16:30' },
            { id: 'SID-005', name: 'Flag Generic Sender', baseSenderId: 'INFO', ruleType: 'flag', category: 'generic', applyNormalisation: false, status: 'disabled', createdBy: 'admin@quicksms.co.uk', createdAt: '05-01-2026 14:00', updatedAt: '28-01-2026 09:15' }
        ];
    }
    
    var rule = rules.find(r => r.id === ruleId);
    if (!rule) return;
    
    var categoryLabels = {
        'government_healthcare': 'Government and Healthcare',
        'banking_finance': 'Banking and Finance',
        'delivery_logistics': 'Delivery and logistics',
        'miscellaneous': 'Miscellaneous',
        'generic': 'Generic'
    };
    
    var variants = SenderIdMatchingService.generateVariants(rule.baseSenderId);
    
    var html = '<div class="mb-3"><strong style="color: #1e3a5f;">Rule Details</strong></div>' +
        '<table class="table table-sm">' +
        '<tr><td class="text-muted" style="width: 40%;">Rule ID</td><td>' + rule.id + '</td></tr>' +
        '<tr><td class="text-muted">Rule Name</td><td>' + rule.name + '</td></tr>' +
        '<tr><td class="text-muted">Base SenderID</td><td><div style="font-weight: 600; font-size: 0.9rem; color: #1e3a5f;">' + rule.baseSenderId + '</div><small class="text-muted">' + rule.id + '</small></td></tr>' +
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

var pendingSenderIdRule = null;

function clearSenderIdValidationState() {
    var alertEl = document.getElementById('senderid-validation-alert');
    if (alertEl) alertEl.classList.add('d-none');
    
    var fields = ['senderid-rule-name', 'senderid-base-value', 'senderid-category'];
    fields.forEach(function(fieldId) {
        var field = document.getElementById(fieldId);
        if (field) field.classList.remove('is-invalid');
    });
}

function validateSenderIdRule() {
    clearSenderIdValidationState();
    
    var name = document.getElementById('senderid-rule-name').value.trim();
    var baseSenderId = document.getElementById('senderid-base-value').value.trim();
    var category = document.getElementById('senderid-category').value;
    
    var errors = [];
    
    if (!name) {
        document.getElementById('senderid-rule-name').classList.add('is-invalid');
        errors.push('Rule name');
    }
    
    if (!baseSenderId) {
        document.getElementById('senderid-base-value').classList.add('is-invalid');
        errors.push('Base SenderID');
    }
    
    if (!category) {
        document.getElementById('senderid-category').classList.add('is-invalid');
        errors.push('Category');
    }
    
    if (errors.length > 0) {
        var alertEl = document.getElementById('senderid-validation-alert');
        var messageEl = document.getElementById('senderid-validation-message');
        if (alertEl && messageEl) {
            messageEl.textContent = 'Please complete all required fields before saving.';
            alertEl.classList.remove('d-none');
            alertEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
        return false;
    }
    
    return true;
}

function getCategoryDisplayName(categoryValue) {
    var categoryMap = {
        'government_healthcare': 'Government and Healthcare',
        'banking_finance': 'Banking and Finance',
        'delivery_logistics': 'Delivery and Logistics',
        'miscellaneous': 'Miscellaneous',
        'generic': 'Generic'
    };
    return categoryMap[categoryValue] || categoryValue;
}

function saveSenderIdRule() {
    if (!validateSenderIdRule()) {
        return;
    }
    
    var ruleId = document.getElementById('senderid-rule-id').value;
    var name = document.getElementById('senderid-rule-name').value.trim();
    var baseSenderId = document.getElementById('senderid-base-value').value.trim().toUpperCase();
    var ruleType = document.querySelector('input[name="senderid-rule-type"]:checked').value;
    var category = document.getElementById('senderid-category').value;
    var applyNormalisation = document.getElementById('senderid-apply-normalisation').checked;
    
    pendingSenderIdRule = {
        ruleId: ruleId,
        name: name,
        baseSenderId: baseSenderId,
        ruleType: ruleType,
        category: category,
        applyNormalisation: applyNormalisation,
        isEdit: !!ruleId
    };
    
    document.getElementById('confirm-rule-name').textContent = name;
    document.getElementById('confirm-base-senderid').textContent = baseSenderId;
    
    var ruleTypeBadge = ruleType === 'block' 
        ? '<span class="badge bg-danger">Block</span>' 
        : '<span class="badge bg-warning text-dark">Flag</span>';
    document.getElementById('confirm-rule-type').innerHTML = ruleTypeBadge;
    
    document.getElementById('confirm-category').textContent = getCategoryDisplayName(category);
    
    var normalisationText = applyNormalisation 
        ? '<span class="text-success"><i class="fas fa-check me-1"></i>Enabled</span>' 
        : '<span class="text-muted"><i class="fas fa-times me-1"></i>Disabled</span>';
    document.getElementById('confirm-normalisation').innerHTML = normalisationText;
    
    var confirmModal = new bootstrap.Modal(document.getElementById('senderIdConfirmModal'));
    confirmModal.show();
}

function confirmSaveSenderIdRule() {
    if (!pendingSenderIdRule) {
        console.error('[SenderIdControls] No pending rule to save');
        return;
    }
    
    var data = pendingSenderIdRule;
    var rules = JSON.parse(localStorage.getItem('senderIdRules') || '[]');
    if (rules.length === 0) {
        rules = [
            { id: 'SID-001', name: 'Block HSBC Impersonation', baseSenderId: 'HSBC', ruleType: 'block', category: 'banking_finance', applyNormalisation: true, status: 'active', createdBy: 'admin@quicksms.co.uk', createdAt: '15-01-2026 09:30', updatedAt: '15-01-2026 09:30' },
            { id: 'SID-002', name: 'Block Barclays Impersonation', baseSenderId: 'BARCLAYS', ruleType: 'block', category: 'banking_finance', applyNormalisation: true, status: 'active', createdBy: 'admin@quicksms.co.uk', createdAt: '15-01-2026 10:15', updatedAt: '20-01-2026 14:22' },
            { id: 'SID-003', name: 'Flag HMRC Messages', baseSenderId: 'HMRC', ruleType: 'flag', category: 'government_healthcare', applyNormalisation: true, status: 'active', createdBy: 'compliance@quicksms.co.uk', createdAt: '12-01-2026 11:00', updatedAt: '12-01-2026 11:00' },
            { id: 'SID-004', name: 'Block DPD Sender', baseSenderId: 'DPD', ruleType: 'block', category: 'delivery_logistics', applyNormalisation: true, status: 'active', createdBy: 'admin@quicksms.co.uk', createdAt: '10-01-2026 08:45', updatedAt: '25-01-2026 16:30' },
            { id: 'SID-005', name: 'Flag Generic Sender', baseSenderId: 'INFO', ruleType: 'flag', category: 'generic', applyNormalisation: false, status: 'disabled', createdBy: 'admin@quicksms.co.uk', createdAt: '05-01-2026 14:00', updatedAt: '28-01-2026 09:15' }
        ];
    }
    
    var now = new Date();
    var timestamp = now.toLocaleDateString('en-GB').replace(/\//g, '-') + ' ' + now.toTimeString().slice(0, 5);
    var beforeState = null;
    var ruleId = data.ruleId;
    
    if (data.isEdit) {
        var existingIndex = rules.findIndex(function(r) { return r.id === ruleId; });
        if (existingIndex !== -1) {
            beforeState = JSON.parse(JSON.stringify(rules[existingIndex]));
            rules[existingIndex] = Object.assign({}, rules[existingIndex], {
                name: data.name,
                baseSenderId: data.baseSenderId,
                ruleType: data.ruleType,
                category: data.category,
                applyNormalisation: data.applyNormalisation,
                updatedAt: timestamp
            });
        }
    } else {
        ruleId = 'SID-' + String(rules.length + 1).padStart(3, '0');
        rules.push({
            id: ruleId,
            name: data.name,
            baseSenderId: data.baseSenderId,
            ruleType: data.ruleType,
            category: data.category,
            applyNormalisation: data.applyNormalisation,
            status: 'active',
            createdBy: currentAdmin.email,
            createdAt: timestamp,
            updatedAt: timestamp
        });
    }
    
    localStorage.setItem('senderIdRules', JSON.stringify(rules));
    
    logAuditEvent(data.isEdit ? 'SENDERID_RULE_UPDATED' : 'SENDERID_RULE_CREATED', {
        ruleId: ruleId,
        ruleName: data.name,
        baseSenderId: data.baseSenderId,
        ruleType: data.ruleType,
        category: data.category,
        applyNormalisation: data.applyNormalisation,
        before: beforeState,
        after: { name: data.name, baseSenderId: data.baseSenderId, ruleType: data.ruleType, category: data.category, applyNormalisation: data.applyNormalisation },
        entityType: 'senderid_rule'
    });
    
    bootstrap.Modal.getInstance(document.getElementById('senderIdConfirmModal')).hide();
    bootstrap.Modal.getInstance(document.getElementById('senderIdRuleModal')).hide();
    
    SecurityComplianceControlsService.renderAllTabs();
    
    showSuccessToast(data.isEdit ? 'Rule updated successfully' : 'Rule created successfully');
    
    pendingSenderIdRule = null;
    console.log('[SenderIdControls] Rule saved:', ruleId);
}

function showSuccessToast(message) {
    var toastContainer = document.getElementById('toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toast-container';
        toastContainer.className = 'position-fixed top-0 end-0 p-3';
        toastContainer.style.zIndex = '9999';
        document.body.appendChild(toastContainer);
    }
    
    var toastId = 'toast-' + Date.now();
    var toastHtml = '<div id="' + toastId + '" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">' +
        '<div class="d-flex">' +
        '<div class="toast-body">' +
        '<i class="fas fa-check-circle me-2"></i>' + message +
        '</div>' +
        '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>' +
        '</div>' +
        '</div>';
    
    toastContainer.insertAdjacentHTML('beforeend', toastHtml);
    var toastEl = document.getElementById(toastId);
    var toast = new bootstrap.Toast(toastEl, { delay: 3000 });
    toast.show();
    
    toastEl.addEventListener('hidden.bs.toast', function() {
        toastEl.remove();
    });
}

function showToast(message, type) {
    var toastContainer = document.getElementById('toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toast-container';
        toastContainer.className = 'position-fixed top-0 end-0 p-3';
        toastContainer.style.zIndex = '9999';
        document.body.appendChild(toastContainer);
    }
    
    var bgClass = 'bg-primary';
    var icon = 'fa-info-circle';
    if (type === 'success') {
        bgClass = 'bg-success';
        icon = 'fa-check-circle';
    } else if (type === 'warning') {
        bgClass = 'bg-warning text-dark';
        icon = 'fa-exclamation-triangle';
    } else if (type === 'error') {
        bgClass = 'bg-danger';
        icon = 'fa-times-circle';
    }
    
    var toastId = 'toast-' + Date.now();
    var toastHtml = '<div id="' + toastId + '" class="toast align-items-center ' + bgClass + ' border-0" role="alert" aria-live="assertive" aria-atomic="true">' +
        '<div class="d-flex">' +
        '<div class="toast-body">' +
        '<i class="fas ' + icon + ' me-2"></i>' + message +
        '</div>' +
        '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>' +
        '</div>' +
        '</div>';
    
    toastContainer.insertAdjacentHTML('beforeend', toastHtml);
    var toastEl = document.getElementById(toastId);
    var toast = new bootstrap.Toast(toastEl, { delay: 3000 });
    toast.show();
    
    toastEl.addEventListener('hidden.bs.toast', function() {
        toastEl.remove();
    });
}

// ===== Import Rules Functions =====
var pendingImportRules = [];
var VALID_CATEGORIES = ['government_healthcare', 'banking_finance', 'delivery_logistics', 'miscellaneous', 'generic'];
var VALID_RULE_TYPES = ['block', 'flag'];

function showImportRulesModal() {
    resetImportModal();
    var modal = new bootstrap.Modal(document.getElementById('importRulesModal'));
    modal.show();
}

function resetImportModal() {
    pendingImportRules = [];
    document.getElementById('import-step-upload').style.display = 'block';
    document.getElementById('import-step-preview').style.display = 'none';
    document.getElementById('import-confirm-btn').style.display = 'none';
    document.getElementById('import-file-input').value = '';
    document.getElementById('import-file-input').classList.remove('is-invalid');
    document.getElementById('import-valid-tbody').innerHTML = '';
    document.getElementById('import-invalid-tbody').innerHTML = '';
}

function parseImportFile() {
    var fileInput = document.getElementById('import-file-input');
    var file = fileInput.files[0];
    
    if (!file) {
        fileInput.classList.add('is-invalid');
        document.getElementById('import-file-error').textContent = 'Please select a file';
        return;
    }
    
    var fileName = file.name.toLowerCase();
    if (!fileName.endsWith('.csv') && !fileName.endsWith('.xlsx')) {
        fileInput.classList.add('is-invalid');
        document.getElementById('import-file-error').textContent = 'Please select a valid CSV or XLSX file';
        return;
    }
    
    fileInput.classList.remove('is-invalid');
    
    var reader = new FileReader();
    reader.onload = function(e) {
        try {
            var data = e.target.result;
            var workbook;
            
            if (fileName.endsWith('.xlsx')) {
                workbook = XLSX.read(data, { type: 'array' });
            } else {
                var csvText = new TextDecoder('utf-8').decode(new Uint8Array(data));
                workbook = XLSX.read(csvText, { type: 'string' });
            }
            
            var firstSheet = workbook.Sheets[workbook.SheetNames[0]];
            var jsonData = XLSX.utils.sheet_to_json(firstSheet, { defval: '' });
            
            validateAndPreviewImport(jsonData);
        } catch (err) {
            console.error('[Import] Parse error:', err);
            fileInput.classList.add('is-invalid');
            document.getElementById('import-file-error').textContent = 'Error parsing file: ' + err.message;
        }
    };
    
    reader.readAsArrayBuffer(file);
}

function validateAndPreviewImport(rows) {
    var validRows = [];
    var invalidRows = [];
    
    rows.forEach(function(row, index) {
        var errors = [];
        var normalizedRow = normalizeImportRow(row);
        
        if (!normalizedRow.rule_name || normalizedRow.rule_name.trim() === '') {
            errors.push('Missing rule_name');
        }
        
        if (!normalizedRow.base_senderid || normalizedRow.base_senderid.trim() === '') {
            errors.push('Missing base_senderid');
        }
        
        if (!normalizedRow.rule_type || VALID_RULE_TYPES.indexOf(normalizedRow.rule_type.toLowerCase()) === -1) {
            errors.push('Invalid rule_type (must be block or flag)');
        }
        
        if (!normalizedRow.category || VALID_CATEGORIES.indexOf(normalizedRow.category.toLowerCase()) === -1) {
            errors.push('Invalid category');
        }
        
        if (errors.length === 0) {
            validRows.push({
                rowNum: index + 2,
                rule_name: normalizedRow.rule_name.trim(),
                base_senderid: normalizedRow.base_senderid.trim().toUpperCase(),
                rule_type: normalizedRow.rule_type.toLowerCase(),
                category: normalizedRow.category.toLowerCase(),
                normalisation_applied: parseBooleanValue(normalizedRow.normalisation_applied),
                status: normalizedRow.status && normalizedRow.status.toLowerCase() === 'disabled' ? 'disabled' : 'active'
            });
        } else {
            invalidRows.push({
                rowNum: index + 2,
                data: JSON.stringify(row).substring(0, 60) + '...',
                errors: errors.join('; ')
            });
        }
    });
    
    pendingImportRules = validRows;
    renderImportPreview(validRows, invalidRows);
}

function normalizeImportRow(row) {
    var normalized = {};
    Object.keys(row).forEach(function(key) {
        var lowerKey = key.toLowerCase().trim().replace(/\s+/g, '_');
        normalized[lowerKey] = row[key];
    });
    return normalized;
}

function parseBooleanValue(val) {
    if (val === true || val === 1) return true;
    if (val === false || val === 0) return false;
    if (typeof val === 'string') {
        var lower = val.toLowerCase().trim();
        return lower === 'true' || lower === 'yes' || lower === '1';
    }
    return true;
}

function renderImportPreview(validRows, invalidRows) {
    document.getElementById('import-step-upload').style.display = 'none';
    document.getElementById('import-step-preview').style.display = 'block';
    
    document.getElementById('import-valid-count').textContent = validRows.length + ' Valid';
    document.getElementById('import-invalid-count').textContent = invalidRows.length + ' Invalid';
    
    var validTbody = document.getElementById('import-valid-tbody');
    validTbody.innerHTML = validRows.map(function(row) {
        var typeBadge = row.rule_type === 'block' 
            ? '<span class="badge bg-danger" style="font-size: 0.65rem;">Block</span>' 
            : '<span class="badge bg-warning text-dark" style="font-size: 0.65rem;">Flag</span>';
        var statusBadge = row.status === 'active'
            ? '<span class="badge bg-success" style="font-size: 0.65rem;">Enabled</span>'
            : '<span class="badge bg-secondary" style="font-size: 0.65rem;">Disabled</span>';
        var normIcon = row.normalisation_applied 
            ? '<i class="fas fa-check text-success"></i>' 
            : '<i class="fas fa-times text-muted"></i>';
        
        return '<tr>' +
            '<td style="padding: 0.3rem;">' + escapeHtml(row.rule_name) + '</td>' +
            '<td style="padding: 0.3rem;"><code>' + escapeHtml(row.base_senderid) + '</code></td>' +
            '<td style="padding: 0.3rem;">' + typeBadge + '</td>' +
            '<td style="padding: 0.3rem;">' + getCategoryDisplayName(row.category) + '</td>' +
            '<td style="padding: 0.3rem;">' + normIcon + '</td>' +
            '<td style="padding: 0.3rem;">' + statusBadge + '</td>' +
            '</tr>';
    }).join('');
    
    if (validRows.length === 0) {
        validTbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No valid rows found</td></tr>';
    }
    
    var invalidSection = document.getElementById('import-invalid-section');
    var invalidTbody = document.getElementById('import-invalid-tbody');
    
    if (invalidRows.length > 0) {
        invalidSection.style.display = 'block';
        invalidTbody.innerHTML = invalidRows.map(function(row) {
            return '<tr>' +
                '<td style="padding: 0.3rem;">' + row.rowNum + '</td>' +
                '<td style="padding: 0.3rem; max-width: 200px; overflow: hidden; text-overflow: ellipsis;">' + escapeHtml(row.data) + '</td>' +
                '<td style="padding: 0.3rem; color: #991b1b;">' + escapeHtml(row.errors) + '</td>' +
                '</tr>';
        }).join('');
    } else {
        invalidSection.style.display = 'none';
    }
    
    var confirmBtn = document.getElementById('import-confirm-btn');
    if (validRows.length > 0) {
        confirmBtn.style.display = 'inline-block';
        document.getElementById('import-confirm-text').textContent = 'Import ' + validRows.length + ' Rule' + (validRows.length > 1 ? 's' : '');
    } else {
        confirmBtn.style.display = 'none';
    }
}

function escapeHtml(text) {
    if (!text) return '';
    var div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function confirmImportRules() {
    if (pendingImportRules.length === 0) {
        console.error('[Import] No rules to import');
        return;
    }
    
    var rules = JSON.parse(localStorage.getItem('senderIdRules') || '[]');
    if (rules.length === 0) {
        rules = [
            { id: 'SID-001', name: 'Block HSBC Impersonation', baseSenderId: 'HSBC', ruleType: 'block', category: 'banking_finance', applyNormalisation: true, status: 'active', createdBy: 'admin@quicksms.co.uk', createdAt: '15-01-2026 09:30', updatedAt: '15-01-2026 09:30' },
            { id: 'SID-002', name: 'Block Barclays Impersonation', baseSenderId: 'BARCLAYS', ruleType: 'block', category: 'banking_finance', applyNormalisation: true, status: 'active', createdBy: 'admin@quicksms.co.uk', createdAt: '15-01-2026 10:15', updatedAt: '20-01-2026 14:22' },
            { id: 'SID-003', name: 'Flag HMRC Messages', baseSenderId: 'HMRC', ruleType: 'flag', category: 'government_healthcare', applyNormalisation: true, status: 'active', createdBy: 'compliance@quicksms.co.uk', createdAt: '12-01-2026 11:00', updatedAt: '12-01-2026 11:00' },
            { id: 'SID-004', name: 'Block DPD Sender', baseSenderId: 'DPD', ruleType: 'block', category: 'delivery_logistics', applyNormalisation: true, status: 'active', createdBy: 'admin@quicksms.co.uk', createdAt: '10-01-2026 08:45', updatedAt: '25-01-2026 16:30' },
            { id: 'SID-005', name: 'Flag Generic Sender', baseSenderId: 'INFO', ruleType: 'flag', category: 'generic', applyNormalisation: false, status: 'disabled', createdBy: 'admin@quicksms.co.uk', createdAt: '05-01-2026 14:00', updatedAt: '28-01-2026 09:15' }
        ];
    }
    
    var now = new Date();
    var timestamp = now.toLocaleDateString('en-GB').replace(/\//g, '-') + ' ' + now.toTimeString().slice(0, 5);
    var createdCount = 0;
    var failedCount = 0;
    var createdIds = [];
    
    pendingImportRules.forEach(function(importRow) {
        try {
            var newId = 'SID-' + String(rules.length + 1).padStart(3, '0');
            rules.push({
                id: newId,
                name: importRow.rule_name,
                baseSenderId: importRow.base_senderid,
                ruleType: importRow.rule_type,
                category: importRow.category,
                applyNormalisation: importRow.normalisation_applied,
                status: importRow.status,
                createdBy: currentAdmin.email,
                createdAt: timestamp,
                updatedAt: timestamp
            });
            createdCount++;
            createdIds.push(newId);
        } catch (err) {
            console.error('[Import] Failed to create rule:', err);
            failedCount++;
        }
    });
    
    localStorage.setItem('senderIdRules', JSON.stringify(rules));
    
    logAuditEvent('SENDERID_RULES_BULK_IMPORT', {
        createdCount: createdCount,
        failedCount: failedCount,
        createdIds: createdIds,
        totalAttempted: pendingImportRules.length,
        entityType: 'senderid_rule'
    });
    
    bootstrap.Modal.getInstance(document.getElementById('importRulesModal')).hide();
    SecurityComplianceControlsService.renderAllTabs();
    
    var message = createdCount + ' rule' + (createdCount !== 1 ? 's' : '') + ' imported successfully';
    if (failedCount > 0) {
        message += ' (' + failedCount + ' failed)';
    }
    showSuccessToast(message);
    
    pendingImportRules = [];
    console.log('[Import] Bulk import complete:', { created: createdCount, failed: failedCount });
}

function toggleSenderIdRuleStatus(ruleId, newStatus) {
    var rules = JSON.parse(localStorage.getItem('senderIdRules') || '[]');
    if (rules.length === 0) {
        rules = [
            { id: 'SID-001', name: 'Block HSBC Impersonation', baseSenderId: 'HSBC', ruleType: 'block', category: 'banking_finance', applyNormalisation: true, status: 'active', createdBy: 'admin@quicksms.co.uk', createdAt: '15-01-2026 09:30', updatedAt: '15-01-2026 09:30' },
            { id: 'SID-002', name: 'Block Barclays Impersonation', baseSenderId: 'BARCLAYS', ruleType: 'block', category: 'banking_finance', applyNormalisation: true, status: 'active', createdBy: 'admin@quicksms.co.uk', createdAt: '15-01-2026 10:15', updatedAt: '20-01-2026 14:22' },
            { id: 'SID-003', name: 'Flag HMRC Messages', baseSenderId: 'HMRC', ruleType: 'flag', category: 'government_healthcare', applyNormalisation: true, status: 'active', createdBy: 'compliance@quicksms.co.uk', createdAt: '12-01-2026 11:00', updatedAt: '12-01-2026 11:00' },
            { id: 'SID-004', name: 'Block DPD Sender', baseSenderId: 'DPD', ruleType: 'block', category: 'delivery_logistics', applyNormalisation: true, status: 'active', createdBy: 'admin@quicksms.co.uk', createdAt: '10-01-2026 08:45', updatedAt: '25-01-2026 16:30' },
            { id: 'SID-005', name: 'Flag Generic Sender', baseSenderId: 'INFO', ruleType: 'flag', category: 'generic', applyNormalisation: false, status: 'disabled', createdBy: 'admin@quicksms.co.uk', createdAt: '05-01-2026 14:00', updatedAt: '28-01-2026 09:15' }
        ];
    }
    
    var rule = rules.find(function(r) { return r.id === ruleId; });
    if (!rule) return;
    
    var isDisabling = newStatus === 'disabled';
    showActionConfirmation({
        id: ruleId,
        type: 'senderid_rule',
        action: isDisabling ? 'disable' : 'enable',
        title: isDisabling ? 'Disable Rule' : 'Enable Rule',
        icon: isDisabling ? 'fa-ban' : 'fa-check-circle',
        iconColor: isDisabling ? 'text-warning' : 'text-success',
        message: isDisabling 
            ? 'Are you sure you want to disable this blocking rule?' 
            : 'Are you sure you want to enable this blocking rule?',
        details: '<table class="table table-sm" style="font-size: 0.85rem;">' +
            '<tr><td class="text-muted" style="width: 35%;">Rule Name</td><td><strong>' + escapeHtml(rule.name) + '</strong></td></tr>' +
            '<tr><td class="text-muted">SenderID</td><td><code>' + escapeHtml(rule.baseSenderId) + '</code></td></tr>' +
            '<tr><td class="text-muted">Current Status</td><td>' + (rule.status === 'active' ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Disabled</span>') + '</td></tr>' +
            '</table>' +
            '<small class="text-muted">' + (isDisabling ? 'Disabling this rule will allow matching SenderIDs to pass through.' : 'Enabling this rule will block or flag matching SenderIDs.') + '</small>',
        btnText: isDisabling ? 'Disable Rule' : 'Enable Rule',
        btnClass: isDisabling ? 'btn-warning' : 'btn-success',
        showReason: false
    });
}

function executeToggleSenderIdRuleStatus(ruleId, newStatus) {
    var rules = JSON.parse(localStorage.getItem('senderIdRules') || '[]');
    var ruleIndex = rules.findIndex(function(r) { return r.id === ruleId; });
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
        showSuccessToast('Rule ' + (newStatus === 'disabled' ? 'disabled' : 'enabled') + ' successfully');
    }
}

function showActionConfirmation(opts) {
    document.getElementById('action-confirm-id').value = opts.id;
    document.getElementById('action-confirm-type').value = opts.type;
    document.getElementById('action-confirm-action').value = opts.action;
    document.getElementById('action-confirm-title-text').textContent = opts.title || 'Confirm Action';
    document.getElementById('action-confirm-message').textContent = opts.message || 'Are you sure you want to perform this action?';
    document.getElementById('action-confirm-details').innerHTML = opts.details || '';
    document.getElementById('action-confirm-btn-text').textContent = opts.btnText || 'Confirm';
    document.getElementById('action-confirm-reason').value = '';
    document.getElementById('action-confirm-reason').placeholder = opts.reasonPlaceholder || 'Enter reason (optional)...';
    
    var header = document.getElementById('action-confirm-header');
    var iconEl = document.getElementById('action-confirm-icon');
    var btn = document.getElementById('action-confirm-btn');
    
    iconEl.className = 'fas ' + (opts.icon || 'fa-question-circle') + ' me-2 ' + (opts.iconColor || '');
    btn.className = 'btn btn-sm ' + (opts.btnClass || 'btn-primary');
    
    if (opts.showReason) {
        document.getElementById('action-confirm-reason-container').style.display = 'block';
    } else {
        document.getElementById('action-confirm-reason-container').style.display = 'none';
    }
    
    var modal = new bootstrap.Modal(document.getElementById('actionConfirmModal'));
    modal.show();
}

function executeConfirmedAction() {
    var id = document.getElementById('action-confirm-id').value;
    var type = document.getElementById('action-confirm-type').value;
    var action = document.getElementById('action-confirm-action').value;
    var reason = document.getElementById('action-confirm-reason').value;
    
    bootstrap.Modal.getInstance(document.getElementById('actionConfirmModal')).hide();
    
    if (type === 'senderid_rule') {
        if (action === 'disable') {
            executeToggleSenderIdRuleStatus(id, 'disabled');
        } else if (action === 'enable') {
            executeToggleSenderIdRuleStatus(id, 'active');
        }
    } else if (type === 'exemption') {
        if (action === 'disable_enforcement') {
            executeDisableExemptionEnforcement(id, reason);
        } else if (action === 'enable_enforcement') {
            executeEnableExemptionEnforcement(id);
        } else if (action === 'delete') {
            executeDeleteExemption(id);
        }
    } else if (type === 'content_exemption') {
        if (action === 'delete') {
            SecurityComplianceControlsService.executeDeleteContentExemption(id, reason);
        }
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

function confirmSaveContentRule() {
    SecurityComplianceControlsService.confirmSaveContentRule();
}

function updateContentMatchInputLabel() {
    SecurityComplianceControlsService.updateContentMatchInputLabel();
}


function toggleContentActionMenu(btn, ruleId, event) {
    SecurityComplianceControlsService.toggleContentActionMenu(btn, ruleId, event);
}

// Content Exemptions global wrappers
function showAddContentExemptionModal() {
    SecurityComplianceControlsService.showAddContentExemptionModal();
}

function viewContentExemption(exemptionId) {
    SecurityComplianceControlsService.viewContentExemption(exemptionId);
}

function editContentExemption(exemptionId) {
    SecurityComplianceControlsService.editContentExemption(exemptionId);
}

function toggleContentExemptionStatus(exemptionId) {
    SecurityComplianceControlsService.toggleContentExemptionStatus(exemptionId);
}

function deleteContentExemption(exemptionId) {
    SecurityComplianceControlsService.deleteContentExemption(exemptionId);
}

function saveContentExemption() {
    SecurityComplianceControlsService.saveContentExemption();
}

function toggleContentExemptionType() {
    SecurityComplianceControlsService.toggleContentExemptionType();
}

function updateAntispamOverrideWindow() {
    SecurityComplianceControlsService.updateAntispamOverrideWindow();
}

function filterContentExemptionAccounts() {
    SecurityComplianceControlsService.filterContentExemptionAccounts();
}

function showContentExemptionAccountDropdown() {
    SecurityComplianceControlsService.showContentExemptionAccountDropdown();
}

function selectContentExemptionAccount(accountId, accountName) {
    SecurityComplianceControlsService.selectContentExemptionAccount(accountId, accountName);
}

function toggleAllSubaccounts() {
    SecurityComplianceControlsService.toggleAllSubaccounts();
}

function handleSubaccountChange() {
    SecurityComplianceControlsService.handleSubaccountChange();
}

function updateAntispamModeOptions() {
    SecurityComplianceControlsService.updateAntispamModeOptions();
}

function loadContentExemptionSubaccounts() {
    SecurityComplianceControlsService.loadContentExemptionSubaccounts();
}

function toggleContentExemptionActionMenu(btn, exemptionId, event) {
    SecurityComplianceControlsService.toggleContentExemptionActionMenu(btn, exemptionId, event);
}

function toggleContentExemptionsFilterPanel() {
    SecurityComplianceControlsService.toggleContentExemptionsFilterPanel();
}

function filterContentExemptionsTable() {
    SecurityComplianceControlsService.filterContentExemptionsTable();
}

function applyContentExemptionsFilters() {
    SecurityComplianceControlsService.applyContentExemptionsFilters();
}

function resetContentExemptionsFilters() {
    SecurityComplianceControlsService.resetContentExemptionsFilters();
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

function confirmSaveUrlRule() {
    SecurityComplianceControlsService.confirmSaveUrlRule();
}

function executeSaveUrlRule() {
    SecurityComplianceControlsService.executeSaveUrlRule();
}

function runUrlRuleTest() {
    SecurityComplianceControlsService.runUrlRuleTest();
}

function updateUrlPatternLabel() {
    SecurityComplianceControlsService.updateUrlPatternLabel();
}

function resetUrlFilters() {
    SecurityComplianceControlsService.resetUrlFilters();
}

function toggleUrlControlsFilterPanel() {
    SecurityComplianceControlsService.toggleUrlControlsFilterPanel();
}

function applyUrlControlsFilters() {
    SecurityComplianceControlsService.applyUrlControlsFilters();
}

function resetUrlControlsFilters() {
    SecurityComplianceControlsService.resetUrlControlsFilters();
}

function toggleUrlActionMenu(btn, ruleId, event) {
    SecurityComplianceControlsService.toggleUrlActionMenu(btn, ruleId, event);
}

// URL Exemptions global wrappers
function showAddUrlExemptionModal() {
    SecurityComplianceControlsService.showAddUrlExemptionModal();
}

function editUrlExemption(exemptionId) {
    SecurityComplianceControlsService.editUrlExemption(exemptionId);
}

function toggleUrlExemptionStatus(exemptionId) {
    SecurityComplianceControlsService.toggleUrlExemptionStatus(exemptionId);
}

function deleteUrlExemption(exemptionId) {
    SecurityComplianceControlsService.deleteUrlExemption(exemptionId);
}

function toggleUrlExemptionActionMenu(btn, exemptionId, event) {
    SecurityComplianceControlsService.toggleUrlExemptionActionMenu(btn, exemptionId, event);
}

function resetUrlExemptionsFilters() {
    SecurityComplianceControlsService.resetUrlExemptionsFilters();
}

function applyUrlExemptionsFilters() {
    SecurityComplianceControlsService.applyUrlExemptionsFilters();
}

function sortUrlExemptionsTable(column) {
    SecurityComplianceControlsService.sortUrlExemptionsTable(column);
}

function viewUrlExemptionDetails(exemptionId) {
    SecurityComplianceControlsService.viewUrlExemptionDetails(exemptionId);
}

function toggleUrlExemptionType() {
    SecurityComplianceControlsService.toggleUrlExemptionType();
}

function saveUrlExemption() {
    SecurityComplianceControlsService.saveUrlExemption();
}

function filterUrlExemptionAccounts() {
    SecurityComplianceControlsService.filterUrlExemptionAccounts();
}

function selectUrlExemptionAccount(accountId, accountName) {
    SecurityComplianceControlsService.selectUrlExemptionAccount(accountId, accountName);
}

function saveDomainAgeSettings() {
    SecurityComplianceControlsService.saveDomainAgeSettings();
}

function cancelDomainAgeSettings() {
    SecurityComplianceControlsService.cancelDomainAgeSettings();
}

function toggleDomainAgeFields() {
    SecurityComplianceControlsService.toggleDomainAgeFields();
}

function confirmSaveDomainAgeSettings() {
    SecurityComplianceControlsService.confirmSaveDomainAgeSettings();
}

function executeSaveDomainAgeSettings() {
    SecurityComplianceControlsService.executeSaveDomainAgeSettings();
}

function retryDomainAgeSave() {
    SecurityComplianceControlsService.retryDomainAgeSave();
}

function showAddDomainAllowlistModal() {
    SecurityComplianceControlsService.showAddDomainAllowlistModal();
}

function toggleAllowlistAccountSelect() {
    SecurityComplianceControlsService.toggleAllowlistAccountSelect();
}

function saveAddDomainAllowlist() {
    SecurityComplianceControlsService.saveAddDomainAllowlist();
}

function showAddThresholdOverrideModal() {
    SecurityComplianceControlsService.showAddThresholdOverrideModal();
}

function saveAddThresholdOverride() {
    SecurityComplianceControlsService.saveAddThresholdOverride();
}

function viewAllDomainAgeExemptions(filterType) {
    SecurityComplianceControlsService.viewAllDomainAgeExemptions(filterType);
}

function showAddUrlExemptionGlobalModal() {
    SecurityComplianceControlsService.showAddUrlExemptionGlobalModal();
}

function toggleGlobalExemptionType() {
    SecurityComplianceControlsService.toggleGlobalExemptionType();
}

function toggleGlobalExemptionSubaccounts() {
    SecurityComplianceControlsService.toggleGlobalExemptionSubaccounts();
}

function toggleGlobalExemptionDomainAgeMode() {
    SecurityComplianceControlsService.toggleGlobalExemptionDomainAgeMode();
}

function handleDomainChipInput(event) {
    SecurityComplianceControlsService.handleDomainChipInput(event);
}

function handleDomainPaste(event) {
    SecurityComplianceControlsService.handleDomainPaste(event);
}

function removeDomainChip(index) {
    SecurityComplianceControlsService.removeDomainChip(index);
}

function toggleSelectAllUrlRules() {
    SecurityComplianceControlsService.toggleSelectAllUrlRules();
}

function selectGlobalExemptionAccount(accountId, accountName) {
    SecurityComplianceControlsService.selectGlobalExemptionAccount(accountId, accountName);
}

function saveGlobalUrlExemption() {
    SecurityComplianceControlsService.saveGlobalUrlExemption();
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


function editExemption(exemptionId) {
    SecurityComplianceControlsService.editExemption(exemptionId);
}

function revokeExemption(exemptionId) {
    SecurityComplianceControlsService.revokeExemption(exemptionId);
}

function deleteExemption(exemptionId) {
    SecurityComplianceControlsService.deleteExemption(exemptionId);
}

function disableExemptionEnforcement(sourceId) {
    SecurityComplianceControlsService.disableExemptionEnforcement(sourceId);
}

function enableExemptionEnforcement(sourceId) {
    SecurityComplianceControlsService.enableExemptionEnforcement(sourceId);
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

function toggleContentExemptionsFilterPanel() {
    SecurityComplianceControlsService.toggleContentExemptionsFilterPanel();
}

function applyContentExemptionsFilters() {
    SecurityComplianceControlsService.applyContentExemptionsFilters();
}

function resetContentExemptionsFilters() {
    SecurityComplianceControlsService.resetContentExemptionsFilters();
}

function removeContentExemptionsFilter(filterKey) {
    SecurityComplianceControlsService.removeContentExemptionsFilter(filterKey);
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

function confirmAntiSpamToggle(enabled) {
    SecurityComplianceControlsService.confirmAntiSpamToggle(enabled);
}

function updateAntiSpamWindow() {
    SecurityComplianceControlsService.updateAntiSpamWindow();
}

document.addEventListener('DOMContentLoaded', function() {
    // Skip if already initialized by immediate IIFE
    if (_sccInitialized) {
        console.log('[SecurityComplianceControls] Already initialized, skipping DOMContentLoaded init');
        return;
    }
    
    try {
        console.log('[SecurityComplianceControls] DOMContentLoaded init');
        SecurityComplianceControlsService.initialize();
        SecurityComplianceControlsService.renderQuarantineTab();
        SecurityComplianceControlsService.updateQuarantineFilterChips();
        _sccInitialized = true;
        console.log('[SecurityComplianceControls] DOMContentLoaded init complete');
    } catch(e) {
        console.error('[SecurityComplianceControls] Error during init:', e.message, e.stack);
    }
    
    // Enter key to run URL rule test
    var testInput = document.getElementById('url-rule-test-input');
    if (testInput) {
        testInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                runUrlRuleTest();
            }
        });
    }
    
    // Toggle collapse icon for URL Rule test section
    var testCollapseEl = document.getElementById('url-rule-test-collapse');
    if (testCollapseEl) {
        testCollapseEl.addEventListener('show.bs.collapse', function() {
            var icon = document.getElementById('url-rule-test-collapse-icon');
            if (icon) icon.style.transform = 'rotate(180deg)';
        });
        testCollapseEl.addEventListener('hide.bs.collapse', function() {
            var icon = document.getElementById('url-rule-test-collapse-icon');
            if (icon) icon.style.transform = 'rotate(0deg)';
        });
    }
});

// Backup initialization using window.onload as fallback
window.addEventListener('load', function() {
    // Direct fallback - render quarantine data without relying on the service
    var quarantineTableBody = document.getElementById('quarantine-body');
    
    // Check if the service worked already
    if (quarantineTableBody && quarantineTableBody.children.length > 1) {
        return;
    }
    
    // If stats still show 0, render directly
    var awaitingEl = document.getElementById('quarantine-pending-count');
    var releasedEl = document.getElementById('quarantine-released-count');
    
    if (awaitingEl && awaitingEl.textContent === '0') {
        awaitingEl.textContent = '11';
        if (releasedEl) releasedEl.textContent = '1';
    }
    
    // Direct table render
    if (quarantineTableBody) {
        var mockMessages = [
            { id: 'QM001', timestamp: '03-02-2026 09:15', account: 'TechCorp Ltd', subAccount: 'Marketing', senderId: 'TECHCORP', messageSnippet: 'Get 50% OFF now! Visit our...', hasUrl: true, ruleTriggered: 'Promotional Spam', status: 'pending_review', reviewer: 'Unassigned', decisionAt: '-' },
            { id: 'QM002', timestamp: '03-02-2026 09:12', account: 'Finance Pro', subAccount: 'Alerts', senderId: 'FINPRO', messageSnippet: 'URGENT: Your account needs...', hasUrl: false, ruleTriggered: 'Urgency Spam', status: 'pending_review', reviewer: 'Unassigned', decisionAt: '-' },
            { id: 'QM003', timestamp: '03-02-2026 09:08', account: 'RetailMax', subAccount: 'Sales', senderId: 'RETAIL', messageSnippet: 'Congratulations! You won a...', hasUrl: true, ruleTriggered: 'Prize Scam', status: 'pending_review', reviewer: 'Unassigned', decisionAt: '-' },
            { id: 'QM004', timestamp: '03-02-2026 08:55', account: 'HealthPlus', subAccount: 'Main', senderId: 'HEALTH', messageSnippet: 'Limited offer: Weight loss...', hasUrl: true, ruleTriggered: 'Health Scam', status: 'released', reviewer: 'admin@quicksms.co.uk', decisionAt: '03-02-2026 09:30' },
            { id: 'QM005', timestamp: '03-02-2026 08:45', account: 'CryptoEx', subAccount: 'Trading', senderId: 'CRYPTO', messageSnippet: 'Double your Bitcoin! Send...', hasUrl: true, ruleTriggered: 'Crypto Scam', status: 'blocked', reviewer: 'admin@quicksms.co.uk', decisionAt: '03-02-2026 09:25' }
        ];
        
        var html = '';
        mockMessages.forEach(function(msg) {
            var statusBadge = msg.status === 'pending_review' ? '<span class="badge bg-warning text-dark">Pending</span>' :
                              msg.status === 'released' ? '<span class="badge bg-success">Released</span>' :
                              '<span class="badge bg-danger">Blocked</span>';
            var urlBadge = msg.hasUrl ? '<span class="badge bg-info">Yes</span>' : '<span class="badge bg-secondary">No</span>';
            
            html += '<tr>' +
                '<td><input type="checkbox" class="form-check-input quarantine-checkbox" data-id="' + msg.id + '"></td>' +
                '<td>' + msg.timestamp + '</td>' +
                '<td>' + msg.account + '</td>' +
                '<td>' + msg.subAccount + '</td>' +
                '<td><code>' + msg.senderId + '</code></td>' +
                '<td class="text-truncate" style="max-width: 200px;">' + msg.messageSnippet + '</td>' +
                '<td>' + urlBadge + '</td>' +
                '<td><span class="badge bg-danger">' + msg.ruleTriggered + '</span></td>' +
                '<td>' + statusBadge + '</td>' +
                '<td>' + msg.reviewer + '</td>' +
                '<td>' + msg.decisionAt + '</td>' +
                '<td>' +
                    '<button class="btn btn-sm btn-outline-primary me-1" onclick="viewQuarantinedMessage(\'' + msg.id + '\')"><i class="fas fa-eye"></i></button>' +
                    '<button class="btn btn-sm btn-outline-success me-1" onclick="releaseQuarantinedMessage(\'' + msg.id + '\')"><i class="fas fa-check"></i></button>' +
                    '<button class="btn btn-sm btn-outline-danger" onclick="blockQuarantinedMessage(\'' + msg.id + '\')"><i class="fas fa-ban"></i></button>' +
                '</td>' +
                '</tr>';
        });
        
        quarantineTableBody.innerHTML = html;
    }
});
</script>
@endpush
