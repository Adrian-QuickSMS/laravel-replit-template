@extends('layouts.quicksms')

@section('title', 'RCS Agent Library')

@push('styles')
<style>
.rcs-agents-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}
.rcs-agents-header h2 {
    margin: 0;
    font-weight: 600;
}
.rcs-agents-header p {
    margin: 0;
    color: #6c757d;
}
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    background: #fff;
    border-radius: 0.75rem;
    border: 1px solid #e9ecef;
}
.empty-state-icon {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: rgba(136, 108, 192, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
}
.empty-state-icon i {
    font-size: 2rem;
    color: var(--primary);
}
.empty-state h4 {
    margin-bottom: 0.5rem;
    color: #343a40;
}
.empty-state p {
    color: #6c757d;
    margin-bottom: 1.5rem;
    max-width: 400px;
    margin-left: auto;
    margin-right: auto;
}
.agents-table-container {
    background: #fff;
    border-radius: 0.75rem;
    border: 1px solid #e9ecef;
    overflow-x: auto;
}
.agents-table {
    width: 100%;
    margin: 0;
    min-width: 900px;
    table-layout: fixed;
}
.agents-table thead th {
    background: #f8f9fa;
    padding: 0.5rem 0.35rem;
    font-weight: 600;
    font-size: 0.75rem;
    color: #495057;
    border-bottom: 1px solid #e9ecef;
    cursor: pointer;
    white-space: nowrap;
    user-select: none;
}
.agents-table thead th:first-child { width: 22%; }
.agents-table thead th:nth-child(2) { width: 12%; }
.agents-table thead th:nth-child(3) { width: 16%; }
.agents-table thead th:nth-child(4) { width: 14%; }
.agents-table thead th:nth-child(5) { width: 12%; }
.agents-table thead th:nth-child(6) { width: 12%; }
.agents-table thead th:last-child { 
    width: 7%; 
    position: sticky;
    right: 0;
    background: #f8f9fa;
    z-index: 2;
    cursor: default;
}
.agents-table thead th:hover {
    background: #e9ecef;
}
.agents-table thead th:last-child:hover {
    background: #f8f9fa;
}
.agents-table thead th .sort-icon {
    margin-left: 0.25rem;
    opacity: 0.4;
}
.agents-table thead th.sorted .sort-icon {
    opacity: 1;
    color: var(--primary);
}
.agents-table tbody td {
    padding: 0.5rem 0.35rem;
    vertical-align: middle;
    border-bottom: 1px solid #f1f3f5;
    font-size: 0.8rem;
}
.agents-table tbody td:last-child {
    position: sticky;
    right: 0;
    background: #fff;
    z-index: 1;
    box-shadow: -2px 0 4px rgba(0,0,0,0.05);
}
.agents-table tbody tr:last-child td {
    border-bottom: none;
}
.agents-table tbody tr:hover td {
    background: #f8f9fa;
}
.agents-table tbody tr:hover td:last-child {
    background: #f8f9fa;
}
.agent-name {
    font-weight: 500;
    color: #343a40;
}
.badge-draft {
    background: rgba(108, 117, 125, 0.15);
    color: #6c757d;
}
.badge-submitted {
    background: rgba(48, 101, 208, 0.15);
    color: #3065D0;
}
.badge-in-review {
    background: rgba(255, 191, 0, 0.15);
    color: #cc9900;
}
.badge-approved {
    background: rgba(28, 187, 140, 0.15);
    color: #1cbb8c;
}
.badge-rejected {
    background: rgba(220, 53, 69, 0.15);
    color: #dc3545;
}
.badge-pending-info {
    background: rgba(255, 152, 0, 0.15);
    color: #e65100;
}
.badge-info-provided {
    background: rgba(48, 101, 208, 0.15);
    color: #3065D0;
}
.badge-sent-to-supplier {
    background: rgba(67, 56, 202, 0.15);
    color: #4338ca;
}
.badge-supplier-approved {
    background: rgba(15, 118, 110, 0.15);
    color: #0f766e;
}
.badge-suspended {
    background: rgba(108, 117, 125, 0.15);
    color: #6c757d;
}
.badge-revoked {
    background: rgba(0, 0, 0, 0.15);
    color: #333;
}
.badge-conversational {
    background: rgba(48, 101, 208, 0.15);
    color: #3065D0;
}
.badge-non-conversational {
    background: rgba(108, 117, 125, 0.15);
    color: #6c757d;
}
.action-menu-btn {
    background: transparent;
    border: none;
    padding: 0.25rem 0.5rem;
    cursor: pointer;
    color: #6c757d;
}
.action-menu-btn:hover {
    color: var(--primary);
}
.rcs-review-section {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1rem;
}
.rcs-section-header {
    font-weight: 600;
    color: var(--pastel-purple, #886cc0);
    font-size: 0.875rem;
    margin-bottom: 0.75rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #f1f5f9;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.rcs-section-header .section-letter {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 20px;
    height: 20px;
    background: #886cc0;
    color: #fff;
    border-radius: 4px;
    font-size: 0.7rem;
    font-weight: 700;
}
.rcs-review-row {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    border-bottom: 1px solid #f8fafc;
    align-items: flex-start;
}
.rcs-review-row:last-child {
    border-bottom: none;
}
.rcs-review-label {
    font-size: 0.8rem;
    color: #64748b;
    font-weight: 500;
    flex-shrink: 0;
}
.rcs-review-value {
    font-size: 0.8rem;
    color: #1e293b;
    font-weight: 500;
    text-align: right;
    max-width: 60%;
}
.rcs-review-value.mono {
    font-family: 'SF Mono', 'Monaco', monospace;
    background: #f1f5f9;
    padding: 0.2rem 0.4rem;
    border-radius: 4px;
    font-size: 0.75rem;
}
.rcs-color-swatch {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}
.rcs-color-swatch .swatch {
    width: 20px;
    height: 20px;
    border-radius: 4px;
    border: 2px solid #e2e8f0;
}
.rcs-toggle-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.15rem 0.4rem;
    border-radius: 4px;
    font-size: 0.7rem;
    font-weight: 600;
}
.rcs-toggle-badge.shown { background: rgba(144, 238, 144, 0.35); color: #2e7d32; }
.rcs-toggle-badge.hidden { background: #f1f5f9; color: #64748b; }
.rcs-logo-preview {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #e2e8f0;
}
.rcs-hero-preview {
    width: 120px;
    height: auto;
    max-height: 80px;
    object-fit: cover;
    border-radius: 6px;
    border: 1px solid #e2e8f0;
}
.rcs-test-numbers-list {
    display: flex;
    flex-wrap: wrap;
    gap: 0.25rem;
    justify-content: flex-end;
}
.rcs-test-number-pill {
    background: #f1f5f9;
    padding: 0.15rem 0.4rem;
    border-radius: 4px;
    font-size: 0.7rem;
    font-family: 'SF Mono', monospace;
}
.rcs-yes-no {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.15rem 0.4rem;
    border-radius: 4px;
    font-size: 0.7rem;
    font-weight: 600;
}
.rcs-yes-no.yes { background: rgba(255, 182, 193, 0.4); color: #d63384; }
.rcs-yes-no.no { background: #fecaca; color: #991b1b; }
.search-filter-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    border-bottom: 1px solid #e9ecef;
    gap: 1rem;
    flex-wrap: wrap;
}
.search-box {
    flex: 1;
    max-width: 300px;
    min-width: 200px;
}
.filters-group {
    display: flex;
    gap: 0.5rem;
    align-items: center;
    flex-wrap: wrap;
}
.action-menu .dropdown-menu {
    z-index: 9999 !important;
}
.table-dropdown-clone {
    position: fixed !important;
    z-index: 99999 !important;
    min-width: 160px;
    background: #fff;
    border: 1px solid rgba(0,0,0,0.15);
    border-radius: 0.375rem;
    box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.175);
}
.action-menu .dropdown-item {
    font-size: 0.85rem;
    padding: 0.5rem 1rem;
}
.action-menu .dropdown-item i {
    width: 18px;
    margin-right: 0.5rem;
}
.action-menu .dropdown-item.disabled {
    color: #adb5bd;
    pointer-events: none;
}
.date-text {
    font-size: 0.85rem;
    color: #495057;
}
.use-case-text {
    font-size: 0.85rem;
    color: #495057;
}
.pagination-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    border-top: 1px solid #e9ecef;
    flex-wrap: wrap;
    gap: 1rem;
}
.pagination-info {
    font-size: 0.85rem;
    color: #6c757d;
}
.pagination-controls {
    display: flex;
    gap: 0.25rem;
}
.pagination-controls .btn {
    padding: 0.375rem 0.75rem;
    font-size: 0.85rem;
}
.wizard-steps {
    display: flex;
    gap: 1rem;
}
.wizard-step {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: rgba(255, 255, 255, 0.6);
}
.wizard-step.active {
    color: #fff;
}
.wizard-step.completed {
    color: #fff;
}
.wizard-step .step-number {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background-color: rgba(255, 255, 255, 0.3);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    font-weight: 600;
}
.wizard-step.active .step-number {
    background-color: #fff;
    color: var(--primary);
}
.wizard-step.completed .step-number {
    background-color: #fff;
    color: var(--primary);
}
.wizard-step .step-label {
    font-size: 0.85rem;
    font-weight: 500;
}
.wizard-step-inner {
    background: #fff;
    border-radius: 0.75rem;
    padding: 2rem;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}
.billing-option, .usecase-option {
    border: 2px solid #e9ecef;
    border-radius: 0.5rem;
    padding: 1rem;
    cursor: pointer;
    transition: all 0.2s ease;
}
.billing-option:hover, .usecase-option:hover {
    border-color: rgba(136, 108, 192, 0.5);
    background-color: #fdfcfe;
}
.billing-option.selected, .usecase-option.selected {
    border-color: var(--primary);
    background-color: #f0ebf8;
}
.billing-option .form-check, .usecase-option .form-check {
    margin: 0;
    padding: 0;
}
.billing-option .form-check-input, .usecase-option .form-check-input {
    position: absolute;
    opacity: 0;
}
.billing-option .form-check-label, .usecase-option .form-check-label {
    width: 100%;
    cursor: pointer;
}
.option-icon {
    width: 40px;
    height: 40px;
    border-radius: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
    flex-shrink: 0;
}
.option-icon.bg-conversational {
    background: rgba(136, 108, 192, 0.15);
    color: #886CC0;
}
.option-icon.bg-non-conversational {
    background: rgba(108, 117, 125, 0.15);
    color: #6c757d;
}
.option-icon.bg-otp {
    background: rgba(220, 53, 69, 0.15);
    color: #dc3545;
}
.option-icon.bg-transactional {
    background: rgba(48, 101, 208, 0.15);
    color: #3065D0;
}
.option-icon.bg-promotional {
    background: rgba(255, 191, 0, 0.15);
    color: #cc9900;
}
.option-icon.bg-multiuse {
    background: rgba(28, 187, 140, 0.15);
    color: #1cbb8c;
}

.selectable-tile {
    border: 2px solid #e9ecef;
    border-radius: 0.75rem;
    padding: 1.25rem;
    cursor: pointer;
    transition: all 0.2s ease;
    background: #fff;
    height: 100%;
    display: flex;
    flex-direction: column;
}
.selectable-tile:hover {
    border-color: rgba(136, 108, 192, 0.5);
    box-shadow: 0 4px 12px rgba(136, 108, 192, 0.1);
}
.selectable-tile.selected {
    border-color: var(--primary);
    background: linear-gradient(135deg, #f8f5fd 0%, #f0ebf8 100%);
    box-shadow: 0 4px 12px rgba(136, 108, 192, 0.15);
}
.selectable-tile .tile-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 0.75rem;
}
.selectable-tile .tile-icon {
    width: 48px;
    height: 48px;
    border-radius: 0.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
}
.selectable-tile .tile-icon.bg-pastel-primary {
    background: rgba(136, 108, 192, 0.15);
    color: #886CC0;
}
.selectable-tile .tile-icon.bg-pastel-secondary {
    background: rgba(108, 117, 125, 0.15);
    color: #6c757d;
}
.selectable-tile .tile-icon.bg-pastel-warning {
    background: rgba(255, 191, 0, 0.15);
    color: #cc9900;
}
.selectable-tile .tile-icon.bg-pastel-info {
    background: rgba(48, 101, 208, 0.15);
    color: #3065D0;
}
.selectable-tile .tile-icon.bg-pastel-danger {
    background: rgba(220, 53, 69, 0.15);
    color: #dc3545;
}
.selectable-tile .tile-icon.bg-pastel-success {
    background: rgba(28, 187, 140, 0.15);
    color: #1cbb8c;
}
.selectable-tile .tile-check {
    color: #e9ecef;
    font-size: 1.25rem;
    transition: all 0.2s ease;
}
.selectable-tile.selected .tile-check {
    color: var(--primary);
}
.selectable-tile .tile-body {
    flex: 1;
}
.selectable-tile .tile-title {
    font-weight: 600;
    margin-bottom: 0.25rem;
    color: #2c2c2c;
}
.selectable-tile .tile-desc {
    font-size: 0.85rem;
    color: #6c757d;
    margin-bottom: 0;
}
.selectable-tile .tile-footer {
    margin-top: 0.75rem;
    padding-top: 0.75rem;
    border-top: 1px solid #f0f0f0;
}
.selectable-tile .tile-info {
    font-size: 0.8rem;
    color: #6c757d;
    cursor: help;
}
.selectable-tile .tile-info i {
    color: var(--primary);
}
.selectable-tile .learn-more-btn {
    font-size: 0.8rem;
    color: var(--primary);
    text-decoration: none;
}
.selectable-tile .learn-more-btn:hover {
    text-decoration: underline;
}

.test-numbers-container {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}
.test-number-tag {
    display: inline-flex;
    align-items: center;
    background: #f0ebf8;
    border: 1px solid rgba(136, 108, 192, 0.3);
    border-radius: 2rem;
    padding: 0.35rem 0.75rem;
    font-size: 0.85rem;
    color: #2c2c2c;
}
.test-number-tag .remove-number {
    margin-left: 0.5rem;
    color: #886CC0;
    cursor: pointer;
    transition: color 0.2s;
}
.test-number-tag .remove-number:hover {
    color: #dc3545;
}

.logo-upload-zone {
    border: 2px dashed #dee2e6;
    border-radius: 0.5rem;
    padding: 2rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s ease;
    background: #fafafa;
}
.logo-upload-zone:hover {
    border-color: var(--primary);
    background: #f8f5fd;
}
.logo-upload-zone.has-logo {
    border-style: solid;
    border-color: var(--primary);
}
.logo-upload-zone.circular-crop {
    padding: 1.5rem;
}
.circular-preview-wrapper {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    overflow: hidden;
    margin: 0 auto;
    border: 3px solid var(--primary);
    box-shadow: 0 2px 8px rgba(136, 108, 192, 0.2);
}
.logo-preview {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #fff;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
.logo-preview.circular {
    width: 100%;
    height: 100%;
    border-radius: 0;
    border: none;
    box-shadow: none;
}
.hero-upload-zone {
    border: 2px dashed #dee2e6;
    border-radius: 0.5rem;
    padding: 1.5rem;
    cursor: pointer;
    transition: all 0.2s ease;
    background: #fafafa;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 100px;
}
.hero-upload-zone:hover {
    border-color: var(--primary);
    background: #f8f5fd;
}
.hero-upload-zone.has-hero {
    border-style: solid;
    border-color: var(--primary);
    padding: 0.5rem;
}
.hero-preview {
    width: 100%;
    max-height: 120px;
    object-fit: cover;
    border-radius: 0.375rem;
}
.color-preview {
    width: 40px;
    height: 40px;
    border-radius: 0.375rem;
    border: 2px solid #dee2e6;
}
.autosave-indicator {
    font-size: 0.8rem;
    color: #6c757d;
}
.autosave-indicator.saving {
    color: #886CC0;
}
.autosave-indicator.saved {
    color: #1cbb8c;
}
.review-section {
    background: #f8f9fa;
    border-radius: 0.5rem;
    padding: 1.25rem;
    margin-bottom: 1rem;
}
.review-section h6 {
    color: #495057;
    margin-bottom: 1rem;
    font-weight: 600;
}
.review-row {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    border-bottom: 1px solid #e9ecef;
}
.review-row:last-child {
    border-bottom: none;
}
.review-label {
    color: #6c757d;
    font-size: 0.85rem;
}
.review-value {
    font-weight: 500;
    color: #343a40;
    font-size: 0.85rem;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row page-titles">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="#">Management</a></li>
            <li class="breadcrumb-item active"><a href="javascript:void(0)">RCS Agent Registrations</a></li>
        </ol>
    </div>
</div>
<div class="container-fluid">
    @php
        // TODO: Replace with Auth::user()->role or session-based role
        $currentUserRole = 'admin'; // Options: 'admin', 'message_manager', 'viewer', 'analyst'
        
        // RBAC Configuration for RCS Agent Registration
        // Admin: full access (create, edit, submit, delete, view)
        // Message Manager: create, edit, submit (no delete)
        // Others (viewer, analyst, etc.): view-only
        $canCreate = in_array($currentUserRole, ['admin', 'message_manager']);
        $canEdit = in_array($currentUserRole, ['admin', 'message_manager']);
        $canSubmit = in_array($currentUserRole, ['admin', 'message_manager']);
        $canDelete = $currentUserRole === 'admin';
        $canView = true; // All authenticated users can view
        
        // Roles that have any access to this page
        $allowedRoles = ['admin', 'message_manager', 'viewer', 'analyst', 'finance'];
        $hasPageAccess = in_array($currentUserRole, $allowedRoles);
    @endphp
    
    @if(!$hasPageAccess)
    <div id="accessDeniedView">
        <div class="card" style="max-width: 500px; margin: 3rem auto; text-align: center;">
            <div class="card-body py-5">
                <div style="width: 80px; height: 80px; border-radius: 50%; background: rgba(136, 108, 192, 0.1); display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                    <i class="fas fa-lock" style="font-size: 2rem; color: var(--primary);"></i>
                </div>
                <h4 class="mb-3">Access Restricted</h4>
                <p class="text-muted mb-4">You don't have permission to access the RCS Agent Library. Please contact your administrator if you need access.</p>
                <a href="{{ route('dashboard') }}" class="btn btn-primary">
                    <i class="fas fa-arrow-left me-2"></i>Return to Dashboard
                </a>
            </div>
        </div>
    </div>
    @else
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
            <h5 class="card-title mb-0">RCS Agent Library</h5>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#filtersPanel">
                    <i class="fas fa-filter me-1"></i> Filters
                </button>
                @if($canCreate)
                <button class="btn btn-primary btn-sm" id="createAgentBtn">
                    <i class="fas fa-plus me-1"></i>Create RCS Agent
                </button>
                @endif
            </div>
        </div>
        <div class="card-body" id="agentsTableContainer">
            <div class="row mb-3">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" class="form-control border-start-0" id="searchInput" placeholder="Search agents...">
                    </div>
                </div>
            </div>
            
            <div class="collapse mb-3" id="filtersPanel">
                <div class="card card-body border-0 rounded-3" style="background-color: #f0ebf8;">
                    <div class="row g-3 align-items-end">
                        <div class="col-6 col-md-4 col-lg-2">
                            <label class="form-label small fw-bold">Status</label>
                            <select class="form-select form-select-sm" id="statusFilter">
                                <option value="">All Statuses</option>
                                <option value="draft">Draft</option>
                                <option value="submitted">Submitted</option>
                                <option value="in-review">In Review</option>
                                <option value="pending-info">Returned</option>
                                <option value="info-provided">Info Provided</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-4 col-lg-2">
                            <label class="form-label small fw-bold">Billing Type</label>
                            <select class="form-select form-select-sm" id="billingFilter">
                                <option value="">All Billing Types</option>
                                <option value="conversational">Conversational</option>
                                <option value="non-conversational">Non-conversational</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-4 col-lg-2">
                            <label class="form-label small fw-bold">Use Case</label>
                            <select class="form-select form-select-sm" id="useCaseFilter">
                                <option value="">All Use Cases</option>
                                <option value="otp">OTP</option>
                                <option value="transactional">Transactional</option>
                                <option value="promotional">Promotional</option>
                                <option value="multi-use">Multi-use</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="agents-table-container">
                <table class="agents-table" id="agentsTable">
                    <thead>
                        <tr>
                            <th data-sort="name" onclick="sortTable('name')">Agent Name <i class="fas fa-sort sort-icon"></i></th>
                            <th data-sort="status" onclick="sortTable('status')">Status <i class="fas fa-sort sort-icon"></i></th>
                            <th data-sort="billing" onclick="sortTable('billing')">Billing Category <i class="fas fa-sort sort-icon"></i></th>
                            <th data-sort="useCase" onclick="sortTable('useCase')">Use Case <i class="fas fa-sort sort-icon"></i></th>
                            <th data-sort="created" onclick="sortTable('created')">Created <i class="fas fa-sort sort-icon"></i></th>
                            <th data-sort="updated" onclick="sortTable('updated')">Last Updated <i class="fas fa-sort sort-icon"></i></th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="agentsTableBody">
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="pagination-info small text-muted">
                    Showing <span id="showingStart">1</span>-<span id="showingEnd">10</span> of <span id="totalCount">0</span> agents
                </div>
                <div class="pagination-controls">
                    <button class="btn btn-outline-secondary btn-sm" id="prevPageBtn" disabled>
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button class="btn btn-outline-secondary btn-sm" id="nextPageBtn">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="empty-state d-none" id="emptyState">
        <div class="empty-state-icon">
            <i class="fas fa-robot"></i>
        </div>
        <h4>No RCS Agents Yet</h4>
        @if($canCreate)
        <p>Create your first RCS Agent to enable rich messaging experiences for your customers.</p>
        <button class="btn btn-primary" id="createAgentEmptyBtn">
            <i class="fas fa-plus me-2"></i>Create RCS Agent
        </button>
        @else
        <p>No RCS Agents have been created yet. Contact an administrator or message manager to create one.</p>
        @endif
    </div>
    @endif
</div>

<div class="modal fade" id="viewAgentModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-robot me-2"></i>View RCS Agent</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="background: #f8f9fa;">
                <div class="mb-3" id="viewRejectionReasonContainer" style="display: none;">
                    <div class="alert alert-danger mb-3">
                        <div class="d-flex align-items-start">
                            <i class="fas fa-exclamation-circle me-2 mt-1"></i>
                            <div>
                                <strong>Rejection Reason</strong>
                                <p class="mb-0 mt-1" id="viewAgentRejectionReason"></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-3" id="viewReturnedCommentsContainer" style="display: none;">
                    <div class="alert alert-warning mb-3">
                        <div class="d-flex align-items-start">
                            <i class="fas fa-reply me-2 mt-1"></i>
                            <div style="width: 100%;">
                                <strong>Returned for Review</strong>
                                <p class="text-muted small mb-2">Our review team has returned this agent with the following comments. Please address them and resubmit.</p>
                                <div id="viewReturnedCommentsList"></div>
                                <div class="mt-2">
                                    <button class="btn btn-sm btn-outline-primary" onclick="var modal = bootstrap.Modal.getInstance(document.getElementById('viewAgentModal')); if(modal) modal.hide(); var agentId = document.getElementById('viewReturnedCommentsContainer').dataset.agentId; editAgent(agentId);">
                                        <i class="fas fa-edit me-1"></i> Edit & Resubmit
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-lg-6">
                        <div class="rcs-review-section">
                            <div class="rcs-section-header">
                                <span class="section-letter">A</span>
                                <i class="fas fa-palette"></i> Agent Identity & Branding
                            </div>
                            <div class="rcs-review-row">
                                <span class="rcs-review-label">Agent Name</span>
                                <span class="rcs-review-value" id="viewAgentName">-</span>
                            </div>
                            <div class="rcs-review-row">
                                <span class="rcs-review-label">Status</span>
                                <span class="rcs-review-value" id="viewAgentStatus">-</span>
                            </div>
                            <div class="rcs-review-row">
                                <span class="rcs-review-label">Agent Description</span>
                                <span class="rcs-review-value" id="viewAgentDescription">-</span>
                            </div>
                            <div class="rcs-review-row">
                                <span class="rcs-review-label">Brand Colour</span>
                                <span class="rcs-review-value rcs-color-swatch">
                                    <span class="swatch" id="viewColorSwatch" style="background: #886CC0;"></span>
                                    <span class="mono" id="viewColorHex">#886CC0</span>
                                </span>
                            </div>
                            <div class="rcs-review-row">
                                <span class="rcs-review-label">Logo</span>
                                <span class="rcs-review-value" id="viewAgentLogo">
                                    <span class="text-muted">Not uploaded</span>
                                </span>
                            </div>
                            <div class="rcs-review-row">
                                <span class="rcs-review-label">Hero/Banner Image</span>
                                <span class="rcs-review-value" id="viewAgentHero">
                                    <span class="text-muted">Not uploaded</span>
                                </span>
                            </div>
                        </div>

                        <div class="rcs-review-section">
                            <div class="rcs-section-header">
                                <span class="section-letter">B</span>
                                <i class="fas fa-mobile-alt"></i> Handset Contact Details
                            </div>
                            <div class="rcs-review-row">
                                <span class="rcs-review-label">Call</span>
                                <span class="rcs-review-value" id="viewAgentPhone">-</span>
                            </div>
                            <div class="rcs-review-row">
                                <span class="rcs-review-label">Website</span>
                                <span class="rcs-review-value" id="viewAgentWebsite">-</span>
                            </div>
                            <div class="rcs-review-row">
                                <span class="rcs-review-label">Email</span>
                                <span class="rcs-review-value" id="viewAgentEmail">-</span>
                            </div>
                        </div>

                        <div class="rcs-review-section">
                            <div class="rcs-section-header">
                                <span class="section-letter">C</span>
                                <i class="fas fa-shield-alt"></i> Compliance URLs
                            </div>
                            <div class="rcs-review-row">
                                <span class="rcs-review-label">Privacy Policy URL</span>
                                <span class="rcs-review-value" id="viewAgentPrivacy">-</span>
                            </div>
                            <div class="rcs-review-row">
                                <span class="rcs-review-label">Terms of Service URL</span>
                                <span class="rcs-review-value" id="viewAgentTerms">-</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="rcs-review-section">
                            <div class="rcs-section-header">
                                <span class="section-letter">D</span>
                                <i class="fas fa-tags"></i> Agent Classification
                            </div>
                            <div class="rcs-review-row">
                                <span class="rcs-review-label">Billing Category</span>
                                <span class="rcs-review-value" id="viewAgentBilling">-</span>
                            </div>
                            <div class="rcs-review-row">
                                <span class="rcs-review-label">Use Case</span>
                                <span class="rcs-review-value" id="viewAgentUseCase">-</span>
                            </div>
                            <div class="rcs-review-row">
                                <span class="rcs-review-label">Created Date</span>
                                <span class="rcs-review-value" id="viewAgentCreated">-</span>
                            </div>
                            <div class="rcs-review-row">
                                <span class="rcs-review-label">Last Updated</span>
                                <span class="rcs-review-value" id="viewAgentUpdated">-</span>
                            </div>
                        </div>

                        <div class="rcs-review-section">
                            <div class="rcs-section-header">
                                <span class="section-letter">E</span>
                                <i class="fas fa-comments"></i> Messaging Behaviour
                            </div>
                            <div class="rcs-review-row">
                                <span class="rcs-review-label">Use Case Description</span>
                                <span class="rcs-review-value" id="viewAgentUseCaseOverview">-</span>
                            </div>
                            <div class="rcs-review-row">
                                <span class="rcs-review-label">Opt-in Consent</span>
                                <span class="rcs-review-value" id="viewAgentOptIn">-</span>
                            </div>
                            <div class="rcs-review-row">
                                <span class="rcs-review-label">Opt-out Supported</span>
                                <span class="rcs-review-value" id="viewAgentOptOut">-</span>
                            </div>
                            <div class="rcs-review-row">
                                <span class="rcs-review-label">Monthly Volume Estimate</span>
                                <span class="rcs-review-value" id="viewAgentVolume">-</span>
                            </div>
                        </div>

                        <div class="rcs-review-section">
                            <div class="rcs-section-header">
                                <span class="section-letter">F</span>
                                <i class="fas fa-phone"></i> Test Numbers
                                <span class="badge ms-auto" style="font-size: 0.65rem; background: rgba(255, 182, 193, 0.4); color: #d63384;" id="viewAgentTestNumbersCount">0 / 20</span>
                            </div>
                            <div class="rcs-review-row">
                                <span class="rcs-review-label">Numbers</span>
                                <span class="rcs-review-value rcs-test-numbers-list" id="viewAgentTestNumbers">
                                    <span class="text-muted">No test numbers added</span>
                                </span>
                            </div>
                        </div>

                        <div class="rcs-review-section">
                            <div class="rcs-section-header">
                                <span class="section-letter">G</span>
                                <i class="fas fa-building"></i> Company & Approver Details
                            </div>
                            <div style="margin-bottom: 0.75rem;">
                                <div style="font-size: 0.7rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem;">Company Information</div>
                                <div class="rcs-review-row">
                                    <span class="rcs-review-label">Company Name</span>
                                    <span class="rcs-review-value" id="viewAgentCompanyName">-</span>
                                </div>
                                <div class="rcs-review-row">
                                    <span class="rcs-review-label">Company Number</span>
                                    <span class="rcs-review-value mono" id="viewAgentCompanyNumber">-</span>
                                </div>
                            </div>
                            <div>
                                <div style="font-size: 0.7rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem;">Approver Details</div>
                                <div class="rcs-review-row">
                                    <span class="rcs-review-label">Approver Name</span>
                                    <span class="rcs-review-value" id="viewAgentApproverName">-</span>
                                </div>
                                <div class="rcs-review-row">
                                    <span class="rcs-review-label">Job Title</span>
                                    <span class="rcs-review-value" id="viewAgentApproverJobTitle">-</span>
                                </div>
                                <div class="rcs-review-row">
                                    <span class="rcs-review-label">Email</span>
                                    <span class="rcs-review-value" id="viewAgentApproverEmail">-</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-info" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="returnCommentsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: #fff; border-bottom: 1px solid #e9ecef;">
                <h5 class="modal-title"><i class="fas fa-comments me-2 text-warning"></i>Review Comments — <span id="returnCommentsAgentName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="returnCommentsBody">
                <div class="text-center py-3"><i class="fas fa-spinner fa-spin me-2"></i>Loading comments...</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="returnCommentsEditBtn"><i class="fas fa-edit me-1"></i> Edit & Resubmit</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="resubmitAgentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Resubmit RCS Agent</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-pastel-primary mb-3">
                    You are about to resubmit <strong id="resubmitAgentName"></strong> for review.
                </div>
                
                <div id="resubmitRejectionInfo" class="mb-3" style="display: none;">
                    <label class="form-label small text-muted">Previous Rejection Reason</label>
                    <div class="border rounded p-3 bg-white">
                        <p class="mb-0 text-danger" id="resubmitRejectionReason"></p>
                    </div>
                    <small class="text-muted">Please ensure you have addressed this feedback before resubmitting.</small>
                </div>
                
                <p class="text-muted">The agent will be placed back in the review queue. You will be notified once a decision has been made.</p>
                
                <div class="alert alert-warning small mb-0 mt-3">
                    <i class="fas fa-info-circle me-1"></i>
                    <strong>Note:</strong> Status updates are synced from Google, MNOs, and third-party validators. Review typically takes 2-5 business days.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-info" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmResubmitBtn">Resubmit for Review</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteAgentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete RCS Agent</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger mb-3">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Warning:</strong> This action cannot be undone.
                </div>
                <p>Are you sure you want to permanently delete <strong id="deleteAgentName"></strong>?</p>
                <p class="text-muted small mb-0">This will remove all agent data including branding assets, messaging profile, and configuration. Only draft agents can be deleted.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-info" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete Agent</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="agentWizardModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content" style="height: 100vh; display: flex; flex-direction: column;">
            <div class="modal-header py-3 flex-shrink-0" style="background: linear-gradient(135deg, #886CC0 0%, #a78bda 100%); color: #fff;">
                <div class="d-flex align-items-center">
                    <h5 class="modal-title mb-0" id="agentWizardTitle"><i class="fas fa-robot me-2"></i>Create RCS Agent</h5>
                    <div class="wizard-steps ms-4">
                        <span class="wizard-step active" data-step="1">
                            <span class="step-number">1</span>
                            <span class="step-label">Agent Basics</span>
                        </span>
                        <span class="wizard-step" data-step="2">
                            <span class="step-number">2</span>
                            <span class="step-label">Branding</span>
                        </span>
                        <span class="wizard-step" data-step="3">
                            <span class="step-number">3</span>
                            <span class="step-label">Handset</span>
                        </span>
                        <span class="wizard-step" data-step="4">
                            <span class="step-number">4</span>
                            <span class="step-label">Agent Type</span>
                        </span>
                        <span class="wizard-step" data-step="5">
                            <span class="step-number">5</span>
                            <span class="step-label">Messaging</span>
                        </span>
                        <span class="wizard-step" data-step="6">
                            <span class="step-number">6</span>
                            <span class="step-label">Company</span>
                        </span>
                        <span class="wizard-step" data-step="7">
                            <span class="step-number">7</span>
                            <span class="step-label">Test Numbers</span>
                        </span>
                        <span class="wizard-step" data-step="8">
                            <span class="step-number">8</span>
                            <span class="step-label">Review</span>
                        </span>
                    </div>
                    <span class="autosave-indicator ms-auto me-3" id="autosaveIndicator">
                        <i class="fas fa-cloud me-1"></i><span id="autosaveText">Draft saved</span>
                    </span>
                </div>
                <button type="button" class="btn-close btn-close-white" id="wizardCloseBtn"></button>
            </div>
            
            <div class="modal-body flex-grow-1 p-0" style="overflow-y: auto; background: #f8f9fa;">
                <!-- Step 1: Agent Basics -->
                <div id="agentWizardStep1" class="wizard-content p-4">
                    <div class="wizard-step-inner mx-auto" style="max-width: 700px;">
                        <div class="alert alert-pastel-primary mb-4">
                            <strong>Step 1: Agent Basics</strong> - Define your RCS Agent's name, description, and brand colour.
                        </div>
                        
                        <div class="card border mb-4">
                            <div class="card-body">
                                <h6 class="fw-semibold mb-3"><i class="fas fa-id-card me-2 text-primary"></i>Basic Information</h6>
                                
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">RCS Agent Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="agentName" placeholder="e.g., Your Brand Name" maxlength="25">
                                    <small class="text-muted">Max 25 characters. Displayed as sender name on devices.</small>
                                    <div class="invalid-feedback">Please enter an agent name (max 25 characters)</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">RCS Agent Description <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="agentDescription" rows="3" placeholder="Brief description of your business..." maxlength="100"></textarea>
                                    <small class="text-muted"><span id="descCharCount">0</span>/100 characters</small>
                                    <div class="invalid-feedback">Please enter a description (max 100 characters)</div>
                                </div>
                                
                                <div class="mb-0">
                                    <label class="form-label fw-semibold">Brand Colour <span class="text-danger">*</span></label>
                                    <div class="d-flex align-items-center gap-3">
                                        <input type="color" class="form-control form-control-color color-preview" id="brandColor" value="#886CC0" style="width: 50px; height: 38px;">
                                        <input type="text" class="form-control" id="brandColorHex" value="#886CC0" maxlength="7" style="max-width: 120px;">
                                    </div>
                                    <small class="text-muted">Used for buttons and accent elements in RCS messages</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Step 2: Branding Assets -->
                <div id="agentWizardStep2" class="wizard-content p-4 d-none">
                    <div class="wizard-step-inner mx-auto" style="max-width: 900px;">
                        <div class="alert alert-pastel-primary mb-4">
                            <strong>Step 2: Branding Assets</strong> - Upload your agent logo and hero/banner image.
                        </div>
                        
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="mb-4">
                                    <div class="alert alert-light border py-2 px-3 mb-3" style="font-size: 0.8rem;">
                                        <i class="fas fa-mobile-alt text-primary me-2"></i>
                                        <strong>Device display:</strong> Logos are displayed as circles on handset devices. Ensure important elements are centred.
                                    </div>
                                    
                                    @include('quicksms.partials.shared-image-editor', [
                                        'editorId' => 'agentLogo',
                                        'preset' => 'agent-logo',
                                        'label' => 'Agent Logo',
                                        'accept' => 'image/png,image/jpeg',
                                        'maxSize' => 2 * 1024 * 1024,
                                        'required' => true,
                                        'helpText' => 'Upload a square logo. Final output: 222×222 px with circular display.'
                                    ])
                                </div>
                            </div>
                            
                            <div class="col-lg-6">
                                <div class="mb-4">
                                    <div class="alert alert-light border py-2 px-3 mb-3" style="font-size: 0.8rem;">
                                        <i class="fas fa-mobile-alt text-primary me-2"></i>
                                        <strong>Device display:</strong> Hero images partially overlap the logo on handset devices. Avoid placing key content near the centre.
                                    </div>
                                    
                                    @include('quicksms.partials.shared-image-editor', [
                                        'editorId' => 'agentHero',
                                        'preset' => 'agent-hero',
                                        'label' => 'Hero / Banner Image',
                                        'accept' => 'image/png,image/jpeg',
                                        'maxSize' => 5 * 1024 * 1024,
                                        'required' => true,
                                        'helpText' => 'Wide banner image. Final output: 1480×448 px (45:14 aspect ratio).'
                                    ])
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Step 3: Handset + Compliance -->
                <div id="agentWizardStep3" class="wizard-content p-4 d-none">
                    <div class="wizard-step-inner mx-auto" style="max-width: 900px;">
                        <div class="alert alert-pastel-primary mb-4">
                            <strong>Step 3: Handset + Compliance</strong> - Configure handset contact details and compliance URLs.
                        </div>
                        
                        <div class="card border mb-4">
                            <div class="card-body">
                                <h6 class="fw-semibold mb-3"><i class="fas fa-mobile-alt me-2 text-primary"></i>Handset Contact Details</h6>
                                <p class="text-muted small mb-3">These details will be shown to message recipients on their device. Toggle which fields to display.</p>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <label class="form-label fw-semibold mb-0">Phone Number <span class="text-danger">*</span></label>
                                            <div class="form-check form-switch mb-0">
                                                <input class="form-check-input" type="checkbox" id="showPhoneToggle" checked>
                                                <label class="form-check-label small text-muted" for="showPhoneToggle">Display on handset</label>
                                            </div>
                                        </div>
                                        <div class="input-group">
                                            <span class="input-group-text">+44</span>
                                            <input type="tel" class="form-control" id="supportPhone" placeholder="20 1234 5678">
                                        </div>
                                        <small class="text-muted">UK numbers only. Leading 0 will be stripped automatically.</small>
                                        <div class="invalid-feedback" id="phoneError">Please enter a valid UK phone number</div>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <label class="form-label fw-semibold mb-0">Website URL <span class="text-danger">*</span></label>
                                            <div class="form-check form-switch mb-0">
                                                <input class="form-check-input" type="checkbox" id="showWebsiteToggle" checked>
                                                <label class="form-check-label small text-muted" for="showWebsiteToggle">Display on handset</label>
                                            </div>
                                        </div>
                                        <input type="url" class="form-control" id="businessWebsite" placeholder="https://www.example.com">
                                        <small class="text-muted">Must start with https://</small>
                                        <div class="invalid-feedback">Please enter a valid HTTPS URL</div>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <label class="form-label fw-semibold mb-0">Email Address <span class="text-danger">*</span></label>
                                            <div class="form-check form-switch mb-0">
                                                <input class="form-check-input" type="checkbox" id="showEmailToggle" checked>
                                                <label class="form-check-label small text-muted" for="showEmailToggle">Display on handset</label>
                                            </div>
                                        </div>
                                        <input type="email" class="form-control" id="supportEmail" placeholder="support@example.com">
                                        <small class="text-muted">Contact email for customer inquiries</small>
                                        <div class="invalid-feedback">Please enter a valid email address</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card border mb-4">
                            <div class="card-body">
                                <h6 class="fw-semibold mb-3"><i class="fas fa-shield-alt me-2 text-primary"></i>Compliance URLs</h6>
                                <p class="text-muted small mb-3">Required for RCS agent registration. Both must use HTTPS.</p>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">Privacy Policy URL <span class="text-danger">*</span></label>
                                        <input type="url" class="form-control" id="privacyUrl" placeholder="https://www.example.com/privacy">
                                        <small class="text-muted">Link to your privacy policy page</small>
                                        <div class="invalid-feedback">Please enter a valid HTTPS URL for your privacy policy</div>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">Terms of Service URL <span class="text-danger">*</span></label>
                                        <input type="url" class="form-control" id="termsUrl" placeholder="https://www.example.com/terms">
                                        <small class="text-muted">Link to your terms of service page</small>
                                        <div class="invalid-feedback">Please enter a valid HTTPS URL for your terms of service</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Step 4: Agent Type -->
                <div id="agentWizardStep4" class="wizard-content p-4 d-none">
                    <div class="wizard-step-inner mx-auto" style="max-width: 900px;">
                        <div class="alert alert-pastel-primary mb-4">
                            <strong>Step 4: Agent Type</strong> - Select billing category, use case, and provide a use case description.
                        </div>
                        
                        <div class="card border mb-4">
                            <div class="card-body">
                                <h6 class="fw-semibold mb-3"><i class="fas fa-credit-card me-2 text-primary"></i>Billing Category <span class="text-danger">*</span></h6>
                                <p class="text-muted small mb-3">Select the billing model for this agent. This determines how message costs are calculated.</p>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="selectable-tile billing-tile" data-billing="conversational" id="tileConversational">
                                            <div class="tile-header">
                                                <div class="tile-icon bg-pastel-primary">
                                                    <i class="fas fa-comments"></i>
                                                </div>
                                                <div class="tile-check">
                                                    <i class="fas fa-check-circle"></i>
                                                </div>
                                            </div>
                                            <div class="tile-body">
                                                <h6 class="tile-title">Conversational</h6>
                                                <p class="tile-desc">Two-way messaging with customer interactions</p>
                                            </div>
                                            <div class="tile-footer">
                                                <span class="tile-info" data-bs-toggle="tooltip" data-bs-placement="top" title="Billed per conversation session. A session starts when you send a message and lasts 24 hours. Unlimited messages within a session.">
                                                    <i class="fas fa-info-circle"></i> Session-based billing
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="selectable-tile billing-tile" data-billing="non-conversational" id="tileNonConversational">
                                            <div class="tile-header">
                                                <div class="tile-icon bg-pastel-secondary">
                                                    <i class="fas fa-paper-plane"></i>
                                                </div>
                                                <div class="tile-check">
                                                    <i class="fas fa-check-circle"></i>
                                                </div>
                                            </div>
                                            <div class="tile-body">
                                                <h6 class="tile-title">Non-conversational</h6>
                                                <p class="tile-desc">One-way notifications and alerts only</p>
                                            </div>
                                            <div class="tile-footer">
                                                <span class="tile-info" data-bs-toggle="tooltip" data-bs-placement="top" title="Billed per message sent. Best for notifications, alerts, and one-way broadcasts where replies are not expected.">
                                                    <i class="fas fa-info-circle"></i> Per-message billing
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="invalid-feedback" id="billingError" style="display: none;">Please select a billing category</div>
                            </div>
                        </div>
                        
                        <div class="card border mb-4">
                            <div class="card-body">
                                <h6 class="fw-semibold mb-3"><i class="fas fa-bullseye me-2 text-primary"></i>Use Case <span class="text-danger">*</span></h6>
                                <p class="text-muted small mb-3">Select the primary use case for this agent. This helps carriers understand your messaging intent.</p>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="selectable-tile usecase-tile" data-usecase="otp" id="tileOtp">
                                            <div class="tile-header">
                                                <div class="tile-icon bg-pastel-warning">
                                                    <i class="fas fa-key"></i>
                                                </div>
                                                <div class="tile-check">
                                                    <i class="fas fa-check-circle"></i>
                                                </div>
                                            </div>
                                            <div class="tile-body">
                                                <h6 class="tile-title">OTP</h6>
                                                <p class="tile-desc">One-time passwords and verification codes</p>
                                            </div>
                                            <div class="tile-footer">
                                                <button type="button" class="btn btn-link btn-sm p-0 learn-more-btn" data-usecase="otp">
                                                    <i class="fas fa-external-link-alt me-1"></i> Learn More
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="selectable-tile usecase-tile" data-usecase="transactional" id="tileTrans">
                                            <div class="tile-header">
                                                <div class="tile-icon bg-pastel-info">
                                                    <i class="fas fa-receipt"></i>
                                                </div>
                                                <div class="tile-check">
                                                    <i class="fas fa-check-circle"></i>
                                                </div>
                                            </div>
                                            <div class="tile-body">
                                                <h6 class="tile-title">Transactional</h6>
                                                <p class="tile-desc">Order updates, confirmations, alerts</p>
                                            </div>
                                            <div class="tile-footer">
                                                <button type="button" class="btn btn-link btn-sm p-0 learn-more-btn" data-usecase="transactional">
                                                    <i class="fas fa-external-link-alt me-1"></i> Learn More
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="selectable-tile usecase-tile" data-usecase="promotional" id="tilePromo">
                                            <div class="tile-header">
                                                <div class="tile-icon bg-pastel-danger">
                                                    <i class="fas fa-bullhorn"></i>
                                                </div>
                                                <div class="tile-check">
                                                    <i class="fas fa-check-circle"></i>
                                                </div>
                                            </div>
                                            <div class="tile-body">
                                                <h6 class="tile-title">Promotional</h6>
                                                <p class="tile-desc">Marketing, offers, and campaigns</p>
                                            </div>
                                            <div class="tile-footer">
                                                <button type="button" class="btn btn-link btn-sm p-0 learn-more-btn" data-usecase="promotional">
                                                    <i class="fas fa-external-link-alt me-1"></i> Learn More
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="selectable-tile usecase-tile" data-usecase="multi-use" id="tileMulti">
                                            <div class="tile-header">
                                                <div class="tile-icon bg-pastel-success">
                                                    <i class="fas fa-layer-group"></i>
                                                </div>
                                                <div class="tile-check">
                                                    <i class="fas fa-check-circle"></i>
                                                </div>
                                            </div>
                                            <div class="tile-body">
                                                <h6 class="tile-title">Multi-use</h6>
                                                <p class="tile-desc">Combination of multiple use cases</p>
                                            </div>
                                            <div class="tile-footer">
                                                <button type="button" class="btn btn-link btn-sm p-0 learn-more-btn" data-usecase="multi-use">
                                                    <i class="fas fa-external-link-alt me-1"></i> Learn More
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="invalid-feedback" id="useCaseError" style="display: none;">Please select a use case</div>
                            </div>
                        </div>
                        
                        <div class="card border mb-4">
                            <div class="card-body">
                                <h6 class="fw-semibold mb-3"><i class="fas fa-file-alt me-2 text-primary"></i>Use Case Description <span class="text-danger">*</span></h6>
                                <p class="text-muted small mb-3">Provide a detailed description of how you will use this RCS Agent.</p>
                                
                                <div class="mb-0">
                                    <label class="form-label fw-semibold">Use Case Overview</label>
                                    <textarea class="form-control" id="useCaseOverview" rows="4" maxlength="1000" placeholder="Include example message types, target audience, and business purpose..."></textarea>
                                    <div class="d-flex justify-content-between">
                                        <small class="text-muted">Detailed explanation of your messaging use case</small>
                                        <small class="text-muted"><span id="useCaseCharCount">0</span>/1000</small>
                                    </div>
                                    <div class="invalid-feedback">Please provide a use case overview</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Step 5: Messaging Behaviour -->
                <div id="agentWizardStep5" class="wizard-content p-4 d-none">
                    <div class="wizard-step-inner mx-auto" style="max-width: 800px;">
                        <div class="alert alert-pastel-primary mb-4">
                            <strong>Step 5: Messaging Behaviour</strong> - Define your messaging patterns and compliance measures.
                        </div>
                        
                        <div class="card border mb-4">
                            <div class="card-body">
                                <h6 class="fw-semibold mb-3"><i class="fas fa-cog me-2 text-primary"></i>Messaging Patterns</h6>
                                <p class="text-muted small mb-3">Provide details about your messaging frequency and volume.</p>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">Campaign Frequency <span class="text-danger">*</span></label>
                                        <select class="form-select" id="campaignFrequency">
                                            <option value="">Select frequency...</option>
                                            <option value="daily">Daily</option>
                                            <option value="weekly">Weekly</option>
                                            <option value="monthly">Monthly</option>
                                            <option value="on-demand">On-demand / Event-triggered</option>
                                            <option value="continuous">Continuous (24/7)</option>
                                        </select>
                                        <div class="invalid-feedback">Please select campaign frequency</div>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">Estimated Monthly Volume <span class="text-danger">*</span></label>
                                        <select class="form-select" id="monthlyVolume">
                                            <option value="">Select volume...</option>
                                            <option value="0-1000">Up to 1,000 messages</option>
                                            <option value="1000-10000">1,000 - 10,000 messages</option>
                                            <option value="10000-100000">10,000 - 100,000 messages</option>
                                            <option value="100000-500000">100,000 - 500,000 messages</option>
                                            <option value="500000-1000000">500,000 - 1,000,000 messages</option>
                                            <option value="1000000-5000000">1M - 5M messages</option>
                                            <option value="5000000-10000000">5M - 10M messages</option>
                                        </select>
                                        <div class="invalid-feedback">Please select estimated monthly volume</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card border mb-4">
                            <div class="card-body">
                                <h6 class="fw-semibold mb-3"><i class="fas fa-check-circle me-2 text-primary"></i>Consent & Opt-out</h6>
                                <p class="text-muted small mb-3">Describe how you obtain consent and handle opt-out requests.</p>
                                
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Opt-in / Legitimate Interest Description <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="optInDescription" rows="3" maxlength="500" placeholder="Describe how users opt-in to receive messages from this agent (e.g., website sign-up form, in-store registration, existing customer relationship)..."></textarea>
                                    <div class="d-flex justify-content-between">
                                        <small class="text-muted">Explain your legal basis for sending messages</small>
                                        <small class="text-muted"><span id="optInCharCount">0</span>/500</small>
                                    </div>
                                    <div class="invalid-feedback">Please describe your opt-in or legitimate interest basis</div>
                                </div>
                                
                                <div class="mb-0">
                                    <label class="form-label fw-semibold">Opt-out Mechanism Description <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="optOutDescription" rows="3" maxlength="500" placeholder="Describe how users can opt-out of receiving messages (e.g., reply STOP, unsubscribe link, customer service request)..."></textarea>
                                    <div class="d-flex justify-content-between">
                                        <small class="text-muted">Explain how recipients can stop receiving messages</small>
                                        <small class="text-muted"><span id="optOutCharCount">0</span>/500</small>
                                    </div>
                                    <div class="invalid-feedback">Please describe your opt-out mechanism</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="modal fade" id="useCaseLearnMoreModal" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header border-0 pb-0">
                                <h5 class="modal-title" id="useCaseModalTitle">Use Case Details</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body pt-2">
                                <div id="useCaseModalContent"></div>
                            </div>
                            <div class="modal-footer border-0 pt-0">
                                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Got it</button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Step 6: Company & Approver Details -->
                <div id="agentWizardStep6" class="wizard-content p-4 d-none">
                    <div class="wizard-step-inner mx-auto" style="max-width: 900px;">
                        <div class="alert alert-pastel-primary mb-4">
                            <strong>Step 6: Company & Approver Details</strong> - Provide your company registration and approver information.
                        </div>
                        
                        <div class="alert alert-pastel-primary mb-4" style="background: rgba(136, 108, 192, 0.1); border-left: 4px solid #886CC0;">
                            <i class="fas fa-info-circle me-2 text-primary"></i>
                            <strong>Important:</strong> Incorrect or inconsistent information may delay approval. Please ensure all details match your official company records.
                        </div>
                        
                        <div class="card border mb-4">
                            <div class="card-body">
                                <h6 class="fw-semibold mb-3"><i class="fas fa-building me-2 text-primary"></i>Company Information</h6>
                                <p class="text-muted small mb-3">These details are pre-populated from your account settings. You can edit them if needed for this registration.</p>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">Company Number <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="companyNumber" placeholder="e.g., 12345678">
                                        <small class="text-muted">Your registered company number</small>
                                        <div class="invalid-feedback">Please enter your company number</div>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">Company Website <span class="text-danger">*</span></label>
                                        <input type="url" class="form-control" id="companyWebsite" placeholder="https://www.yourcompany.com">
                                        <small class="text-muted">Your main company website</small>
                                        <div class="invalid-feedback">Please enter a valid company website URL</div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Registered Address <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="registeredAddress" rows="3" placeholder="Enter your full registered business address..."></textarea>
                                    <small class="text-muted">Your official registered business address</small>
                                    <div class="invalid-feedback">Please enter your registered address</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card border mb-4">
                            <div class="card-body">
                                <h6 class="fw-semibold mb-3"><i class="fas fa-user-tie me-2 text-primary"></i>Approver Details</h6>
                                <p class="text-muted small mb-3">The approver is the person authorizing this RCS Agent registration on behalf of your company.</p>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">Approver Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="approverName" placeholder="e.g., John Smith">
                                        <small class="text-muted">Full name of the authorizing person</small>
                                        <div class="invalid-feedback">Please enter the approver's name</div>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">Approver Job Title <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="approverJobTitle" placeholder="e.g., Marketing Director">
                                        <small class="text-muted">Their role within your organization</small>
                                        <div class="invalid-feedback">Please enter the approver's job title</div>
                                    </div>
                                </div>
                                
                                <div class="mb-0">
                                    <label class="form-label fw-semibold">Approver Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="approverEmail" placeholder="e.g., john.smith@yourcompany.com">
                                    <small class="text-muted">Email address for verification and approval communications</small>
                                    <div class="invalid-feedback">Please enter a valid email address</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Step 7: Test Numbers -->
                <div id="agentWizardStep7" class="wizard-content p-4 d-none">
                    <div class="wizard-step-inner mx-auto" style="max-width: 700px;">
                        <div class="alert alert-pastel-primary mb-4">
                            <strong>Step 7: Test Numbers</strong> - Add phone numbers for testing your RCS Agent before going live.
                        </div>
                        
                        <div class="card border mb-4">
                            <div class="card-body">
                                <h6 class="fw-semibold mb-3"><i class="fas fa-mobile-alt me-2 text-primary"></i>Test Numbers</h6>
                                <p class="text-muted small mb-3">Add up to 20 phone numbers for testing your RCS Agent before going live. Numbers must be in international format (e.g., +447700900123).</p>
                                
                                <div class="mb-3">
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="testNumberInput" placeholder="+447700900123">
                                        <button class="btn btn-primary" type="button" id="addTestNumberBtn">
                                            <i class="fas fa-plus me-1"></i> Add
                                        </button>
                                    </div>
                                    <div class="invalid-feedback" id="testNumberError" style="display: none;">Invalid format. Use international format (e.g., +447700900123)</div>
                                    <small class="text-muted">Enter phone number in international format starting with +</small>
                                </div>
                                
                                <div id="testNumbersList" class="test-numbers-container">
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <small class="text-muted"><span id="testNumberCount">0</span>/20 numbers added</small>
                                    <button type="button" class="btn btn-link btn-sm text-danger p-0" id="clearAllTestNumbers" style="display: none;">
                                        <i class="fas fa-trash-alt me-1"></i> Clear All
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-light border py-2 px-3" style="font-size: 0.85rem;">
                            <i class="fas fa-info-circle text-primary me-2"></i>
                            <strong>Note:</strong> Test numbers are optional but recommended before submitting for approval.
                        </div>
                    </div>
                </div>
                
                <!-- Step 8: Review & Submit -->
                <div id="agentWizardStep8" class="wizard-content p-4 d-none">
                    <div class="wizard-step-inner mx-auto" style="max-width: 800px;">
                        <div class="alert alert-pastel-primary mb-4">
                            <strong>Step 8: Review & Submit</strong> - Please review all information before submitting for approval.
                        </div>
                        
                        <div class="review-section">
                            <h6>Branding & Identity</h6>
                            <div class="review-row">
                                <span class="review-label">Agent Name</span>
                                <span class="review-value" id="reviewAgentName">-</span>
                            </div>
                            <div class="review-row">
                                <span class="review-label">Description</span>
                                <span class="review-value" id="reviewDescription" style="max-width: 60%; text-align: right;">-</span>
                            </div>
                            <div class="review-row">
                                <span class="review-label">Logo</span>
                                <span class="review-value" id="reviewLogo">-</span>
                            </div>
                            <div class="review-row">
                                <span class="review-label">Hero Image</span>
                                <span class="review-value" id="reviewHero">-</span>
                            </div>
                            <div class="review-row">
                                <span class="review-label">Brand Colour</span>
                                <span class="review-value d-flex align-items-center justify-content-end gap-2">
                                    <span class="color-preview" id="reviewColorPreview" style="width: 24px; height: 24px;"></span>
                                    <span id="reviewColor">-</span>
                                </span>
                            </div>
                        </div>
                        
                        <div class="review-section">
                            <h6>Handset Contact Details</h6>
                            <div class="review-row">
                                <span class="review-label">Phone Number</span>
                                <span class="review-value">
                                    <span id="reviewPhone">-</span>
                                    <span class="badge badge-pastel-primary ms-2" id="reviewPhoneDisplay">Visible</span>
                                </span>
                            </div>
                            <div class="review-row">
                                <span class="review-label">Website URL</span>
                                <span class="review-value">
                                    <span id="reviewWebsite">-</span>
                                    <span class="badge badge-pastel-primary ms-2" id="reviewWebsiteDisplay">Visible</span>
                                </span>
                            </div>
                            <div class="review-row">
                                <span class="review-label">Email Address</span>
                                <span class="review-value">
                                    <span id="reviewEmail">-</span>
                                    <span class="badge badge-pastel-primary ms-2" id="reviewEmailDisplay">Visible</span>
                                </span>
                            </div>
                        </div>
                        
                        <div class="review-section">
                            <h6>Compliance URLs</h6>
                            <div class="review-row">
                                <span class="review-label">Privacy Policy</span>
                                <span class="review-value" id="reviewPrivacy">-</span>
                            </div>
                            <div class="review-row">
                                <span class="review-label">Terms of Service</span>
                                <span class="review-value" id="reviewTerms">-</span>
                            </div>
                        </div>
                        
                        <div class="review-section">
                            <h6>Billing & Use Case</h6>
                            <div class="review-row">
                                <span class="review-label">Billing Category</span>
                                <span class="review-value" id="reviewBilling">-</span>
                            </div>
                            <div class="review-row">
                                <span class="review-label">Use Case</span>
                                <span class="review-value" id="reviewUseCase">-</span>
                            </div>
                        </div>
                        
                        <div class="review-section">
                            <h6>Messaging Behaviour</h6>
                            <div class="review-row">
                                <span class="review-label">Campaign Frequency</span>
                                <span class="review-value" id="reviewFrequency">-</span>
                            </div>
                            <div class="review-row">
                                <span class="review-label">Monthly Volume</span>
                                <span class="review-value" id="reviewVolume">-</span>
                            </div>
                            <div class="review-row">
                                <span class="review-label">Opt-in Description</span>
                                <span class="review-value" id="reviewOptIn" style="max-width: 60%; text-align: right;">-</span>
                            </div>
                            <div class="review-row">
                                <span class="review-label">Opt-out Mechanism</span>
                                <span class="review-value" id="reviewOptOut" style="max-width: 60%; text-align: right;">-</span>
                            </div>
                            <div class="review-row">
                                <span class="review-label">Use Case Overview</span>
                                <span class="review-value" id="reviewUseCaseOverview" style="max-width: 60%; text-align: right;">-</span>
                            </div>
                            <div class="review-row">
                                <span class="review-label">Test Numbers</span>
                                <span class="review-value" id="reviewTestNumbers" style="max-width: 60%; text-align: right;">-</span>
                            </div>
                        </div>
                        
                        <div class="review-section">
                            <h6>Company Information</h6>
                            <div class="review-row">
                                <span class="review-label">Company Number</span>
                                <span class="review-value" id="reviewCompanyNumber">-</span>
                            </div>
                            <div class="review-row">
                                <span class="review-label">Company Website</span>
                                <span class="review-value" id="reviewCompanyWebsite">-</span>
                            </div>
                            <div class="review-row">
                                <span class="review-label">Registered Address</span>
                                <span class="review-value" id="reviewRegisteredAddress" style="max-width: 60%; text-align: right; white-space: pre-line;">-</span>
                            </div>
                        </div>
                        
                        <div class="review-section">
                            <h6>Approver Details</h6>
                            <div class="review-row">
                                <span class="review-label">Approver Name</span>
                                <span class="review-value" id="reviewApproverName">-</span>
                            </div>
                            <div class="review-row">
                                <span class="review-label">Approver Job Title</span>
                                <span class="review-value" id="reviewApproverJobTitle">-</span>
                            </div>
                            <div class="review-row">
                                <span class="review-label">Approver Email</span>
                                <span class="review-value" id="reviewApproverEmail">-</span>
                            </div>
                        </div>
                        
                        <div class="alert alert-warning mt-4">
                            <i class="fas fa-info-circle me-2"></i>
                            Once submitted, your agent will be reviewed by our team. This typically takes 2-5 business days. You will be notified of the outcome via email.
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer flex-shrink-0 bg-white border-top">
                <button type="button" class="btn btn-outline-info" id="wizardCancelBtn">Cancel</button>
                <button type="button" class="btn btn-outline-secondary" id="wizardPrevBtn" style="display: none;">
                    <i class="fas fa-arrow-left me-1"></i>Previous
                </button>
                <button type="button" class="btn btn-primary" id="wizardNextBtn">
                    Next<i class="fas fa-arrow-right ms-1"></i>
                </button>
                <button type="button" class="btn btn-primary" id="wizardSubmitBtn" style="display: none;">
                    <i class="fas fa-paper-plane me-1"></i>Submit for Review
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="exitWizardModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Save as Draft?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Your progress has been automatically saved as a draft. You can resume editing this agent at any time from the RCS Agent Library.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-danger" id="discardDraftBtn">Discard Draft</button>
                <button type="button" class="btn btn-primary" id="keepDraftBtn">Keep Draft & Exit</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('js/shared-image-editor.js') }}"></script>
<script src="{{ asset('js/table-dropdown-fix.js') }}"></script>
<script>
// RBAC Configuration - passed from PHP
var currentUserRole = @json($currentUserRole);
var userPermissions = {
    canCreate: @json($canCreate),
    canEdit: @json($canEdit),
    canSubmit: @json($canSubmit),
    canDelete: @json($canDelete),
    canView: @json($canView)
};

// Current user ID for audit trail - passed from PHP
// TODO: Replace with actual Auth::id() when backend is integrated
var currentUserId = @json($currentUserId ?? 1);

/**
 * Get current user ID for audit metadata
 * Used in logo/hero crop metadata for compliance and dispute resolution
 */
function getCurrentUserId() {
    return currentUserId;
}


var allAgents = [];
var filteredAgents = [];
var currentSort = { field: 'updated', direction: 'desc' };
var currentPage = 1;
var pageSize = 10;

function loadAgentsFromApi() {
    fetch('/api/rcs-agents', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(function(response) { return response.json(); })
    .then(function(result) {
        if (result.success && result.data) {
            allAgents = result.data;
        } else {
            allAgents = [];
        }
        filteredAgents = allAgents.slice();
        sortAgents();
        renderTable();
    })
    .catch(function(err) {
        console.error('[RcsAgent] Failed to load agents:', err);
        allAgents = [];
        filteredAgents = [];
        renderTable();
    });
}

document.addEventListener('DOMContentLoaded', function() {
    loadAgentsFromApi();
    
    document.getElementById('searchInput').addEventListener('input', debounce(applyFilters, 300));
    document.getElementById('statusFilter').addEventListener('change', applyFilters);
    document.getElementById('billingFilter').addEventListener('change', applyFilters);
    document.getElementById('useCaseFilter').addEventListener('change', applyFilters);
    
    // Create buttons now navigate to the new wizard page instead of opening a modal
    var createAgentBtn = document.getElementById('createAgentBtn');
    if (createAgentBtn) {
        createAgentBtn.addEventListener('click', function() {
            window.location.href = '{{ route("management.rcs-agent.create") }}';
        });
    }
    
    var createAgentEmptyBtn = document.getElementById('createAgentEmptyBtn');
    if (createAgentEmptyBtn) {
        createAgentEmptyBtn.addEventListener('click', function() {
            window.location.href = '{{ route("management.rcs-agent.create") }}';
        });
    }
    
    // Legacy modal wizard initialization removed - now using page-based wizard at /management/rcs-agent/create
    // initializeWizard();
    
    document.getElementById('confirmResubmitBtn').addEventListener('click', function() {
        if (!pendingResubmitAgentId) return;
        
        var agent = allAgents.find(function(a) { return a.id === pendingResubmitAgentId; });
        if (!agent) return;
        
        fetch('/api/rcs-agents/' + pendingResubmitAgentId + '/resubmit', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(function(response) { return response.json(); })
        .then(function(result) {
            if (result.success) {
                showNotification('success', 'Agent Resubmitted', agent.name + ' has been resubmitted for review.');
                loadAgentsFromApi();
            } else {
                showNotification('error', 'Error', result.error || 'Failed to resubmit agent.');
            }
        })
        .catch(function() {
            showNotification('error', 'Error', 'Failed to resubmit agent. Please try again.');
        });
        
        pendingResubmitAgentId = null;
        bootstrap.Modal.getInstance(document.getElementById('resubmitAgentModal')).hide();
    });
    
    // Delete confirmation button handler (Admin only)
    document.getElementById('confirmDeleteBtn').addEventListener('click', deleteAgent);
    
    document.getElementById('prevPageBtn').addEventListener('click', function() {
        if (currentPage > 1) {
            currentPage--;
            renderTable();
        }
    });
    
    document.getElementById('nextPageBtn').addEventListener('click', function() {
        var maxPages = Math.ceil(filteredAgents.length / pageSize);
        if (currentPage < maxPages) {
            currentPage++;
            renderTable();
        }
    });
});

function debounce(func, wait) {
    var timeout;
    return function() {
        var context = this, args = arguments;
        clearTimeout(timeout);
        timeout = setTimeout(function() {
            func.apply(context, args);
        }, wait);
    };
}

function applyFilters() {
    var search = document.getElementById('searchInput').value.toLowerCase();
    var status = document.getElementById('statusFilter').value;
    var billing = document.getElementById('billingFilter').value;
    var useCase = document.getElementById('useCaseFilter').value;
    
    filteredAgents = allAgents.filter(function(agent) {
        var matchesSearch = !search || agent.name.toLowerCase().includes(search);
        var matchesStatus = !status || agent.status === status;
        var matchesBilling = !billing || agent.billing === billing;
        var matchesUseCase = !useCase || agent.useCase === useCase;
        
        return matchesSearch && matchesStatus && matchesBilling && matchesUseCase;
    });
    
    currentPage = 1;
    sortAgents();
    renderTable();
}

function sortTable(field) {
    var headers = document.querySelectorAll('.agents-table thead th');
    headers.forEach(function(th) { th.classList.remove('sorted'); });
    
    if (currentSort.field === field) {
        currentSort.direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
    } else {
        currentSort.field = field;
        currentSort.direction = 'asc';
    }
    
    var sortedHeader = document.querySelector('[data-sort="' + field + '"]');
    if (sortedHeader) {
        sortedHeader.classList.add('sorted');
        var icon = sortedHeader.querySelector('.sort-icon');
        icon.className = 'fas fa-sort-' + (currentSort.direction === 'asc' ? 'up' : 'down') + ' sort-icon';
    }
    
    sortAgents();
    renderTable();
}

function sortAgents() {
    filteredAgents.sort(function(a, b) {
        var aVal = a[currentSort.field];
        var bVal = b[currentSort.field];
        
        if (typeof aVal === 'string') {
            aVal = aVal.toLowerCase();
            bVal = bVal.toLowerCase();
        }
        
        if (aVal < bVal) return currentSort.direction === 'asc' ? -1 : 1;
        if (aVal > bVal) return currentSort.direction === 'asc' ? 1 : -1;
        return 0;
    });
}

function renderTable() {
    var tbody = document.getElementById('agentsTableBody');
    var tableContainer = document.getElementById('agentsTableContainer');
    var emptyState = document.getElementById('emptyState');
    
    if (filteredAgents.length === 0) {
        tableContainer.classList.add('d-none');
        emptyState.classList.remove('d-none');
        return;
    }
    
    tableContainer.classList.remove('d-none');
    emptyState.classList.add('d-none');
    
    var start = (currentPage - 1) * pageSize;
    var end = Math.min(start + pageSize, filteredAgents.length);
    var pageAgents = filteredAgents.slice(start, end);
    
    tbody.innerHTML = pageAgents.map(function(agent) {
        return '<tr>' +
            '<td><span class="agent-name">' + escapeHtml(agent.name) + '</span></td>' +
            '<td>' + getStatusBadge(agent.status) + '</td>' +
            '<td>' + getBillingBadge(agent.billing) + '</td>' +
            '<td><span class="use-case-text">' + formatUseCase(agent.useCase) + '</span></td>' +
            '<td><span class="date-text">' + formatDate(agent.created) + '</span></td>' +
            '<td><span class="date-text">' + formatDate(agent.updated) + '</span></td>' +
            '<td>' + getActionsMenu(agent) + '</td>' +
        '</tr>';
    }).join('');
    
    document.getElementById('showingStart').textContent = start + 1;
    document.getElementById('showingEnd').textContent = end;
    document.getElementById('totalCount').textContent = filteredAgents.length;
    
    document.getElementById('prevPageBtn').disabled = currentPage === 1;
    document.getElementById('nextPageBtn').disabled = end >= filteredAgents.length;
}

function getStatusBadge(status) {
    var labels = {
        'draft': 'Draft',
        'submitted': 'Submitted',
        'in-review': 'In Review',
        'approved': 'Live',
        'rejected': 'Rejected',
        'pending-info': 'Returned',
        'info-provided': 'Info Provided',
        'sent-to-supplier': 'Sent to Mobile Networks',
        'supplier-approved': 'Supplier Approved',
        'suspended': 'Suspended',
        'revoked': 'Revoked'
    };
    var classes = {
        'draft': 'badge-draft',
        'submitted': 'badge-submitted',
        'in-review': 'badge-in-review',
        'approved': 'badge-approved',
        'rejected': 'badge-rejected',
        'pending-info': 'badge-pending-info',
        'info-provided': 'badge-info-provided',
        'sent-to-supplier': 'badge-sent-to-supplier',
        'supplier-approved': 'badge-supplier-approved',
        'suspended': 'badge-suspended',
        'revoked': 'badge-revoked'
    };
    return '<span class="badge ' + classes[status] + '">' + labels[status] + '</span>';
}

function getBillingBadge(billing) {
    var label = billing === 'conversational' ? 'Conversational' : 'Non-conversational';
    var cls = billing === 'conversational' ? 'badge-conversational' : 'badge-non-conversational';
    return '<span class="badge ' + cls + '">' + label + '</span>';
}

function formatUseCase(useCase) {
    var labels = {
        'otp': 'OTP',
        'transactional': 'Transactional',
        'promotional': 'Promotional',
        'multi-use': 'Multi-use'
    };
    return labels[useCase] || useCase;
}

function formatDate(dateStr) {
    var date = new Date(dateStr);
    var year = date.getFullYear();
    var month = String(date.getMonth() + 1).padStart(2, '0');
    var day = String(date.getDate()).padStart(2, '0');
    return day + '-' + month + '-' + year;
}

function getActionsMenu(agent) {
    // Status-based permissions (agent must be editable)
    var agentIsEditable = agent.status === 'draft' || agent.status === 'rejected' || agent.status === 'pending-info';
    var agentCanResubmit = agent.status === 'rejected';
    var agentCanDelete = agent.status === 'draft'; // Only draft agents can be deleted
    
    // Role-based permissions combined with status-based
    var showEdit = userPermissions.canEdit && agentIsEditable;
    var showResubmit = userPermissions.canSubmit && agentCanResubmit;
    var showDelete = userPermissions.canDelete && agentCanDelete;
    
    var menuItems = '';
    
    // View - always available if user has view permission
    if (userPermissions.canView) {
        menuItems += '<li><a class="dropdown-item" href="javascript:void(0)" onclick="viewAgent(\'' + agent.id + '\')">' +
            '<i class="fas fa-eye"></i>View</a></li>';
    }
    
    // Edit - requires canEdit permission AND agent is draft/rejected
    if (userPermissions.canEdit) {
        menuItems += '<li><a class="dropdown-item' + (showEdit ? '' : ' disabled') + '" href="javascript:void(0)"' + 
            (showEdit ? ' onclick="editAgent(\'' + agent.id + '\')"' : '') + '>' +
            '<i class="fas fa-edit"></i>Edit</a></li>';
    }
    
    // View Comments & Edit - for returned agents
    if (agent.status === 'pending-info') {
        menuItems += '<li><a class="dropdown-item" href="javascript:void(0)" onclick="viewReturnComments(\'' + agent.id + '\')">' +
            '<i class="fas fa-comments"></i>View Comments</a></li>';
        if (showEdit) {
            menuItems += '<li><a class="dropdown-item" href="javascript:void(0)" onclick="editAgent(\'' + agent.id + '\')">' +
                '<i class="fas fa-edit"></i>Edit & Resubmit</a></li>';
        }
    }

    // Resubmit - requires canSubmit permission AND agent is rejected
    if (showResubmit) {
        menuItems += '<li><a class="dropdown-item" href="javascript:void(0)" onclick="resubmitAgent(\'' + agent.id + '\')">' +
            '<i class="fas fa-redo"></i>Resubmit</a></li>';
    }
    
    // Delete - Admin only, draft agents only
    if (showDelete) {
        menuItems += '<li><hr class="dropdown-divider"></li>' +
            '<li><a class="dropdown-item text-danger" href="javascript:void(0)" onclick="confirmDeleteAgent(\'' + agent.id + '\')">' +
            '<i class="fas fa-trash-alt"></i>Delete</a></li>';
    }
    
    return '<div class="dropdown action-menu table-action-dropdown">' +
        '<button class="action-menu-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">' +
            '<i class="fas fa-ellipsis-v"></i>' +
        '</button>' +
        '<ul class="dropdown-menu dropdown-menu-end">' + menuItems + '</ul>' +
    '</div>';
}

function viewReturnComments(agentId) {
    var agent = allAgents.find(function(a) { return a.id === agentId; });
    if (!agent) return;

    var modalBody = document.getElementById('returnCommentsBody');
    modalBody.innerHTML = '<div class="text-center py-3"><i class="fas fa-spinner fa-spin me-2"></i>Loading comments...</div>';
    document.getElementById('returnCommentsAgentName').textContent = agent.name;
    document.getElementById('returnCommentsEditBtn').onclick = function() {
        var modal = bootstrap.Modal.getInstance(document.getElementById('returnCommentsModal'));
        if (modal) modal.hide();
        editAgent(agentId);
    };
    new bootstrap.Modal(document.getElementById('returnCommentsModal')).show();

    $.ajax({
        url: '/api/rcs-agents/' + agentId,
        method: 'GET',
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
        success: function(response) {
            if (response.success) {
                var comments = response.comments || [];
                var returnInfo = response.return_info;
                var html = '';

                if (returnInfo && returnInfo.returned_at) {
                    var returnDate = new Date(returnInfo.returned_at).toLocaleString('en-GB', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
                    html += '<div class="text-muted small mb-3"><i class="fas fa-clock me-1"></i>Returned on ' + returnDate + '</div>';
                }

                if (comments.length > 0) {
                    comments.forEach(function(c) {
                        var date = c.created_at ? new Date(c.created_at).toLocaleString('en-GB', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '';
                        html += '<div class="p-3 mb-2 rounded border" style="background: #fff9ed;">';
                        html += '<div class="d-flex justify-content-between align-items-center mb-1">';
                        html += '<strong class="small"><i class="fas fa-user-shield me-1 text-muted"></i>' + escapeHtml(c.created_by_actor_type) + '</strong>';
                        html += '<span class="text-muted small">' + date + '</span>';
                        html += '</div>';
                        html += '<p class="mb-0">' + escapeHtml(c.comment_text) + '</p>';
                        html += '</div>';
                    });
                } else if (returnInfo && returnInfo.reason) {
                    html += '<div class="p-3 mb-2 rounded border" style="background: #fff9ed;">';
                    html += '<p class="mb-0">' + escapeHtml(returnInfo.reason) + '</p>';
                    html += '</div>';
                } else {
                    html += '<div class="text-muted text-center py-3">No comments available.</div>';
                }

                modalBody.innerHTML = html;
            } else {
                modalBody.innerHTML = '<div class="text-danger text-center py-3">Failed to load comments.</div>';
            }
        },
        error: function() {
            modalBody.innerHTML = '<div class="text-danger text-center py-3">Failed to load comments. Please try again.</div>';
        }
    });
}

function viewAgent(agentId) {
    var agent = allAgents.find(function(a) { return a.id === agentId; });
    if (!agent) return;
    
    // Section A: Agent Identity & Branding
    document.getElementById('viewAgentName').textContent = agent.name || '-';
    document.getElementById('viewAgentStatus').innerHTML = getStatusBadge(agent.status);
    document.getElementById('viewAgentDescription').textContent = agent.description || '-';
    
    // Brand color
    var brandColor = agent.brandColor || '#886CC0';
    document.getElementById('viewColorSwatch').style.background = brandColor;
    document.getElementById('viewColorHex').textContent = brandColor;
    
    // Logo
    var logoContainer = document.getElementById('viewAgentLogo');
    if (agent.logoUrl) {
        logoContainer.innerHTML = '<img src="' + agent.logoUrl + '" class="rcs-logo-preview" alt="Logo" onerror="this.parentNode.innerHTML=\'<span class=text-muted>Not uploaded</span>\'">';
    } else {
        logoContainer.innerHTML = '<span class="text-muted">Not uploaded</span>';
    }
    
    // Hero
    var heroContainer = document.getElementById('viewAgentHero');
    if (agent.heroUrl) {
        heroContainer.innerHTML = '<img src="' + agent.heroUrl + '" class="rcs-hero-preview" alt="Hero" onerror="this.parentNode.innerHTML=\'<span class=text-muted>Not uploaded</span>\'">';
    } else {
        heroContainer.innerHTML = '<span class="text-muted">Not uploaded</span>';
    }
    
    // Section B: Handset Contact Details
    var phoneHtml = agent.supportPhone || '-';
    if (agent.supportPhone) {
        phoneHtml += agent.showPhone ? 
            ' <span class="rcs-toggle-badge shown ms-2"><i class="fas fa-eye"></i> Displayed</span>' :
            ' <span class="rcs-toggle-badge hidden ms-2"><i class="fas fa-eye-slash"></i> Hidden</span>';
    }
    document.getElementById('viewAgentPhone').innerHTML = phoneHtml;
    document.getElementById('viewAgentWebsite').textContent = agent.website || '-';
    
    var emailHtml = agent.supportEmail || '-';
    if (agent.supportEmail) {
        emailHtml += agent.showEmail ? 
            ' <span class="rcs-toggle-badge shown ms-2"><i class="fas fa-eye"></i> Displayed</span>' :
            ' <span class="rcs-toggle-badge hidden ms-2"><i class="fas fa-eye-slash"></i> Hidden</span>';
    }
    document.getElementById('viewAgentEmail').innerHTML = emailHtml;
    
    // Section C: Compliance URLs
    document.getElementById('viewAgentPrivacy').textContent = agent.privacyUrl || '-';
    document.getElementById('viewAgentTerms').textContent = agent.termsUrl || '-';
    
    // Section D: Agent Classification
    document.getElementById('viewAgentBilling').innerHTML = getBillingBadge(agent.billing);
    document.getElementById('viewAgentUseCase').textContent = formatUseCase(agent.useCase);
    document.getElementById('viewAgentCreated').textContent = formatDate(agent.created);
    document.getElementById('viewAgentUpdated').textContent = formatDate(agent.updated);
    
    // Section E: Messaging Behaviour
    document.getElementById('viewAgentUseCaseOverview').textContent = agent.useCaseOverview || '-';
    
    var optInHtml = agent.userConsent ? 
        '<span class="rcs-yes-no yes"><i class="fas fa-check"></i> Yes</span>' :
        '<span class="rcs-yes-no no"><i class="fas fa-times"></i> No</span>';
    document.getElementById('viewAgentOptIn').innerHTML = optInHtml;
    
    var optOutHtml = agent.optOutAvailable ? 
        '<span class="rcs-yes-no yes"><i class="fas fa-check"></i> Yes</span>' :
        '<span class="rcs-yes-no no"><i class="fas fa-times"></i> No</span>';
    document.getElementById('viewAgentOptOut').innerHTML = optOutHtml;
    
    document.getElementById('viewAgentVolume').textContent = agent.monthlyVolume || '-';
    
    // Section F: Test Numbers
    var testNumbers = agent.testNumbers || [];
    document.getElementById('viewAgentTestNumbersCount').textContent = testNumbers.length + ' / 20';
    
    var testNumbersContainer = document.getElementById('viewAgentTestNumbers');
    if (testNumbers.length > 0) {
        testNumbersContainer.innerHTML = testNumbers.map(function(num) {
            return '<span class="rcs-test-number-pill">' + num + '</span>';
        }).join('');
    } else {
        testNumbersContainer.innerHTML = '<span class="text-muted">No test numbers added</span>';
    }
    
    // Section G: Company & Approver Details
    document.getElementById('viewAgentCompanyName').textContent = agent.companyName || '-';
    document.getElementById('viewAgentCompanyNumber').textContent = agent.companyNumber || '-';
    document.getElementById('viewAgentApproverName').textContent = agent.approverName || '-';
    document.getElementById('viewAgentApproverJobTitle').textContent = agent.approverJobTitle || '-';
    document.getElementById('viewAgentApproverEmail').textContent = agent.approverEmail || '-';
    
    // Rejection reason
    var rejectionContainer = document.getElementById('viewRejectionReasonContainer');
    if (agent.rejectionReason) {
        rejectionContainer.style.display = 'block';
        document.getElementById('viewAgentRejectionReason').textContent = agent.rejectionReason;
    } else {
        rejectionContainer.style.display = 'none';
    }

    // Returned with comments
    var returnedContainer = document.getElementById('viewReturnedCommentsContainer');
    returnedContainer.style.display = 'none';
    if (agent.status === 'pending-info') {
        returnedContainer.dataset.agentId = agent.id;
        $.ajax({
            url: '/api/rcs-agents/' + agent.id,
            method: 'GET',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
            success: function(response) {
                if (response.success) {
                    var comments = response.comments || [];
                    var returnInfo = response.return_info;
                    if (comments.length > 0 || returnInfo) {
                        returnedContainer.style.display = 'block';
                        var commentsHtml = '';
                        comments.forEach(function(c) {
                            var date = c.created_at ? new Date(c.created_at).toLocaleString('en-GB', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '';
                            commentsHtml += '<div class="p-2 mb-2 bg-white rounded border">';
                            commentsHtml += '<div class="d-flex justify-content-between"><strong class="small">' + escapeHtml(c.created_by_actor_type) + '</strong><span class="text-muted small">' + date + '</span></div>';
                            commentsHtml += '<p class="mb-0 mt-1 small">' + escapeHtml(c.comment_text) + '</p>';
                            commentsHtml += '</div>';
                        });
                        if (commentsHtml === '' && returnInfo) {
                            commentsHtml = '<div class="p-2 mb-2 bg-white rounded border"><p class="mb-0 small">' + escapeHtml(returnInfo.reason || 'Please review and resubmit.') + '</p></div>';
                        }
                        document.getElementById('viewReturnedCommentsList').innerHTML = commentsHtml;
                    }
                }
            }
        });
    }
    
    new bootstrap.Modal(document.getElementById('viewAgentModal')).show();
}

function editAgent(agentId) {
    window.location.href = '/management/rcs-agent/' + agentId + '/edit';
}

var pendingResubmitAgentId = null;

function resubmitAgent(agentId) {
    var agent = allAgents.find(function(a) { return a.id === agentId; });
    if (!agent) return;
    
    pendingResubmitAgentId = agentId;
    document.getElementById('resubmitAgentName').textContent = agent.name;
    
    var rejectionInfo = document.getElementById('resubmitRejectionInfo');
    if (rejectionInfo && agent.rejectionReason) {
        rejectionInfo.style.display = 'block';
        document.getElementById('resubmitRejectionReason').textContent = agent.rejectionReason;
    } else if (rejectionInfo) {
        rejectionInfo.style.display = 'none';
    }
    
    new bootstrap.Modal(document.getElementById('resubmitAgentModal')).show();
}

var pendingDeleteAgentId = null;

function confirmDeleteAgent(agentId) {
    // RBAC check: Only admin can delete
    if (!userPermissions.canDelete) {
        showNotification('error', 'Access Denied', 'You do not have permission to delete RCS agents.');
        return;
    }
    
    var agent = allAgents.find(function(a) { return a.id === agentId; });
    if (!agent) return;
    
    // Only draft agents can be deleted
    if (agent.status !== 'draft') {
        showNotification('error', 'Cannot Delete', 'Only draft agents can be deleted. Submitted, in-review, or approved agents cannot be deleted.');
        return;
    }
    
    pendingDeleteAgentId = agentId;
    document.getElementById('deleteAgentName').textContent = agent.name;
    new bootstrap.Modal(document.getElementById('deleteAgentModal')).show();
}

function deleteAgent() {
    if (!pendingDeleteAgentId) return;
    
    if (!userPermissions.canDelete) {
        showNotification('error', 'Access Denied', 'You do not have permission to delete RCS agents.');
        return;
    }
    
    var agent = allAgents.find(function(a) { return a.id === pendingDeleteAgentId; });
    if (!agent) return;
    
    var agentName = agent.name;
    
    fetch('/api/rcs-agents/' + pendingDeleteAgentId, {
        method: 'DELETE',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(function(response) { return response.json(); })
    .then(function(result) {
        if (result.success) {
            showNotification('success', 'Agent Deleted', 'The RCS agent "' + agentName + '" has been permanently deleted.');
            loadAgentsFromApi();
        } else {
            showNotification('error', 'Error', result.error || 'Failed to delete agent.');
        }
    })
    .catch(function() {
        showNotification('error', 'Error', 'Failed to delete agent. Please try again.');
    });
    
    var modal = bootstrap.Modal.getInstance(document.getElementById('deleteAgentModal'));
    if (modal) modal.hide();
    
    pendingDeleteAgentId = null;
}

function showNotification(type, title, message) {
    var toastContainer = document.getElementById('notificationToastContainer');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'notificationToastContainer';
        toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        toastContainer.style.zIndex = '10000';
        document.body.appendChild(toastContainer);
    }
    
    var icons = {
        'success': 'fa-check-circle text-success',
        'error': 'fa-exclamation-circle text-danger',
        'warning': 'fa-exclamation-triangle text-warning',
        'info': 'fa-info-circle text-primary'
    };
    
    var toastId = 'toast-' + Date.now();
    var toastHtml = '<div id="' + toastId + '" class="toast" role="alert">' +
        '<div class="toast-header">' +
            '<i class="fas ' + (icons[type] || icons['info']) + ' me-2"></i>' +
            '<strong class="me-auto">' + title + '</strong>' +
            '<small>Just now</small>' +
            '<button type="button" class="btn-close" data-bs-dismiss="toast"></button>' +
        '</div>' +
        '<div class="toast-body">' + message + '</div>' +
    '</div>';
    
    toastContainer.insertAdjacentHTML('beforeend', toastHtml);
    
    var toastEl = document.getElementById(toastId);
    var toast = new bootstrap.Toast(toastEl, { delay: 6000 });
    toast.show();
    
    toastEl.addEventListener('hidden.bs.toast', function() {
        toastEl.remove();
    });
}

function escapeHtml(text) {
    var div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

var wizardData = {
    id: null,
    name: '',
    description: '',
    billing: '',
    useCase: '',
    logoFile: null,
    logoDataUrl: null,
    logoCropMetadata: null,
    heroFile: null,
    heroDataUrl: null,
    brandColor: '#886CC0',
    website: '',
    privacyUrl: '',
    termsUrl: '',
    supportEmail: '',
    supportPhone: '',
    showPhone: true,
    showWebsite: true,
    showEmail: true,
    campaignFrequency: '',
    monthlyVolume: '',
    optInDescription: '',
    optOutDescription: '',
    useCaseOverview: '',
    testNumbers: [],
    companyNumber: '',
    companyWebsite: '',
    registeredAddress: '',
    approverName: '',
    approverJobTitle: '',
    approverEmail: '',
    currentStep: 1,
    isEditing: false,
    isDirty: false,
    logoValid: false,
    heroValid: false,
    isSubmitted: false
};

var mockAccountDetails = {
    companyNumber: '12345678',
    companyWebsite: 'https://www.quicksms.example.com',
    registeredAddress: '123 Business Park\nLondon\nEC1A 1BB\nUnited Kingdom',
    approverName: 'Sarah Johnson',
    approverJobTitle: 'Head of Marketing',
    approverEmail: 'sarah.johnson@quicksms.example.com'
};

var useCaseDetails = {
    'otp': {
        title: 'OTP (One-Time Passwords)',
        content: '<p><strong>Best for:</strong> Account verification, two-factor authentication, password resets, and secure login codes.</p>' +
            '<p><strong>Typical use cases:</strong></p>' +
            '<ul><li>User registration verification</li><li>Transaction confirmations</li><li>Password reset codes</li><li>Login authentication</li></ul>' +
            '<p><strong>Requirements:</strong></p>' +
            '<ul><li>OTPs must expire within a reasonable timeframe</li><li>Messages should clearly identify the sender</li><li>Include security warnings about not sharing codes</li></ul>'
    },
    'transactional': {
        title: 'Transactional Messages',
        content: '<p><strong>Best for:</strong> Order confirmations, shipping updates, appointment reminders, and account notifications.</p>' +
            '<p><strong>Typical use cases:</strong></p>' +
            '<ul><li>Purchase receipts and confirmations</li><li>Delivery status updates</li><li>Booking confirmations</li><li>Account balance notifications</li></ul>' +
            '<p><strong>Requirements:</strong></p>' +
            '<ul><li>Messages must relate to an existing customer relationship or transaction</li><li>No promotional content allowed in transactional messages</li></ul>'
    },
    'promotional': {
        title: 'Promotional Messages',
        content: '<p><strong>Best for:</strong> Marketing campaigns, special offers, product announcements, and sales events.</p>' +
            '<p><strong>Typical use cases:</strong></p>' +
            '<ul><li>Flash sales and discounts</li><li>New product launches</li><li>Loyalty program updates</li><li>Seasonal promotions</li></ul>' +
            '<p><strong>Requirements:</strong></p>' +
            '<ul><li>Explicit opt-in consent required</li><li>Clear opt-out mechanism must be provided</li><li>Frequency caps may apply</li><li>Subject to carrier filtering policies</li></ul>'
    },
    'multi-use': {
        title: 'Multi-use Agent',
        content: '<p><strong>Best for:</strong> Businesses that send different types of messages from a single agent identity.</p>' +
            '<p><strong>Typical use cases:</strong></p>' +
            '<ul><li>Combined transactional and promotional messaging</li><li>Customer service with marketing updates</li><li>Full lifecycle customer communications</li></ul>' +
            '<p><strong>Requirements:</strong></p>' +
            '<ul><li>Must comply with requirements for each message type</li><li>Clear separation of message purposes in compliance documentation</li><li>May require additional review during registration</li></ul>'
    }
};

var wizardModal = null;
var exitModal = null;
var autosaveTimeout = null;

function initializeWizard() {
    wizardModal = new bootstrap.Modal(document.getElementById('agentWizardModal'), { backdrop: 'static' });
    exitModal = new bootstrap.Modal(document.getElementById('exitWizardModal'));
    
    document.getElementById('wizardNextBtn').addEventListener('click', nextStep);
    document.getElementById('wizardPrevBtn').addEventListener('click', prevStep);
    document.getElementById('wizardCancelBtn').addEventListener('click', handleWizardCancel);
    document.getElementById('wizardCloseBtn').addEventListener('click', handleWizardCancel);
    document.getElementById('wizardSubmitBtn').addEventListener('click', submitAgent);
    document.getElementById('keepDraftBtn').addEventListener('click', keepDraftAndExit);
    document.getElementById('discardDraftBtn').addEventListener('click', discardDraftAndExit);
    
    document.querySelectorAll('.billing-tile').forEach(function(tile) {
        tile.addEventListener('click', function(e) {
            if (e.target.closest('.tile-info')) return;
            document.querySelectorAll('.billing-tile').forEach(function(t) { t.classList.remove('selected'); });
            tile.classList.add('selected');
            wizardData.billing = tile.dataset.billing;
            document.getElementById('billingError').style.display = 'none';
            triggerAutosave();
        });
    });
    
    document.querySelectorAll('.usecase-tile').forEach(function(tile) {
        tile.addEventListener('click', function(e) {
            if (e.target.closest('.learn-more-btn')) return;
            document.querySelectorAll('.usecase-tile').forEach(function(t) { t.classList.remove('selected'); });
            tile.classList.add('selected');
            wizardData.useCase = tile.dataset.usecase;
            document.getElementById('useCaseError').style.display = 'none';
            triggerAutosave();
        });
    });
    
    document.querySelectorAll('.learn-more-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            var useCase = btn.dataset.usecase;
            var details = useCaseDetails[useCase];
            if (details) {
                document.getElementById('useCaseModalTitle').textContent = details.title;
                document.getElementById('useCaseModalContent').innerHTML = details.content;
                new bootstrap.Modal(document.getElementById('useCaseLearnMoreModal')).show();
            }
        });
    });
    
    document.getElementById('campaignFrequency').addEventListener('change', function() {
        wizardData.campaignFrequency = this.value;
        triggerAutosave();
    });
    
    document.getElementById('monthlyVolume').addEventListener('change', function() {
        wizardData.monthlyVolume = this.value;
        triggerAutosave();
    });
    
    document.getElementById('optInDescription').addEventListener('input', function() {
        wizardData.optInDescription = this.value;
        document.getElementById('optInCharCount').textContent = this.value.length;
        triggerAutosave();
    });
    
    document.getElementById('optOutDescription').addEventListener('input', function() {
        wizardData.optOutDescription = this.value;
        document.getElementById('optOutCharCount').textContent = this.value.length;
        triggerAutosave();
    });
    
    document.getElementById('useCaseOverview').addEventListener('input', function() {
        wizardData.useCaseOverview = this.value;
        document.getElementById('useCaseCharCount').textContent = this.value.length;
        triggerAutosave();
    });
    
    document.getElementById('addTestNumberBtn').addEventListener('click', addTestNumber);
    document.getElementById('testNumberInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            addTestNumber();
        }
    });
    document.getElementById('clearAllTestNumbers').addEventListener('click', clearAllTestNumbers);
    
    ['companyNumber', 'companyWebsite', 'registeredAddress', 'approverName', 'approverJobTitle', 'approverEmail'].forEach(function(id) {
        document.getElementById(id).addEventListener('input', function() {
            wizardData[id] = this.value;
            triggerAutosave();
        });
    });
    
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(el) {
        return new bootstrap.Tooltip(el);
    });
    
    document.getElementById('agentName').addEventListener('input', function() {
        wizardData.name = this.value;
        triggerAutosave();
    });
    
    document.getElementById('agentDescription').addEventListener('input', function() {
        wizardData.description = this.value;
        document.getElementById('descCharCount').textContent = this.value.length;
        triggerAutosave();
    });
    
    // Shared Image Editor Callbacks - Logo
    // These callbacks wire the shared component to wizardData
    window.onagentLogoChange = function(data) {
        // Generate cropped image for wizardData
        if (typeof agentLogoGenerateCroppedImage === 'function') {
            agentLogoGenerateCroppedImage(function(err, dataUrl) {
                if (!err && dataUrl) {
                    var cropData = agentLogoGetCropData();
                    wizardData.logoDataUrl = dataUrl;
                    wizardData.logoValid = true;
                    // Audit-compliant metadata structure
                    wizardData.logoCropMetadata = {
                        originalSrc: data.originalSrc || null,
                        originalFileName: data.fileName || null,
                        originalFileSize: data.fileSize || null,
                        originalFileType: data.fileType || null,
                        sourceType: 'file_upload',
                        crop: cropData ? cropData.crop : null,
                        zoom: cropData ? cropData.zoom : 1,
                        offsetX: cropData ? cropData.offsetX : 0,
                        offsetY: cropData ? cropData.offsetY : 0,
                        outputWidth: 222,
                        outputHeight: 222,
                        aspectRatio: '1:1',
                        frameShape: 'circle',
                        userId: getCurrentUserId(),
                        timestamp: new Date().toISOString(),
                        clientTimezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
                        cdnUrl: null,
                        uploadedAt: null
                    };
                    var logoErrorEl = document.getElementById('agentLogoError');
                    if (logoErrorEl) logoErrorEl.classList.add('d-none');
                    triggerAutosave();
                }
            });
        }
    };
    
    window.onagentLogoRemove = function() {
        wizardData.logoDataUrl = null;
        wizardData.logoValid = false;
        wizardData.logoCropMetadata = null;
        triggerAutosave();
    };
    
    // Shared Image Editor Callbacks - Hero
    window.onagentHeroChange = function(data) {
        // Generate cropped image for wizardData
        if (typeof agentHeroGenerateCroppedImage === 'function') {
            agentHeroGenerateCroppedImage(function(err, dataUrl) {
                if (!err && dataUrl) {
                    var cropData = agentHeroGetCropData();
                    wizardData.heroDataUrl = dataUrl;
                    wizardData.heroValid = true;
                    // Audit-compliant metadata structure
                    wizardData.heroCropMetadata = {
                        originalSrc: data.originalSrc || null,
                        originalFileName: data.fileName || null,
                        originalFileSize: data.fileSize || null,
                        originalFileType: data.fileType || null,
                        sourceType: 'file_upload',
                        crop: cropData ? cropData.crop : null,
                        zoom: cropData ? cropData.zoom : 1,
                        offsetX: cropData ? cropData.offsetX : 0,
                        offsetY: cropData ? cropData.offsetY : 0,
                        outputWidth: 1480,
                        outputHeight: 448,
                        aspectRatio: '45:14',
                        frameShape: 'rectangle',
                        userId: getCurrentUserId(),
                        timestamp: new Date().toISOString(),
                        clientTimezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
                        cdnUrl: null,
                        uploadedAt: null
                    };
                    var heroErrorEl = document.getElementById('agentHeroError');
                    if (heroErrorEl) heroErrorEl.classList.add('d-none');
                    triggerAutosave();
                }
            });
        }
    };
    
    window.onagentHeroRemove = function() {
        wizardData.heroDataUrl = null;
        wizardData.heroValid = false;
        wizardData.heroCropMetadata = null;
        triggerAutosave();
    };
    
    document.getElementById('brandColor').addEventListener('input', function() {
        wizardData.brandColor = this.value;
        document.getElementById('brandColorHex').value = this.value;
        triggerAutosave();
    });
    
    document.getElementById('brandColorHex').addEventListener('input', function() {
        if (/^#[0-9A-Fa-f]{6}$/.test(this.value)) {
            wizardData.brandColor = this.value;
            document.getElementById('brandColor').value = this.value;
            triggerAutosave();
        }
    });
    
    ['businessWebsite', 'privacyUrl', 'termsUrl', 'supportEmail', 'supportPhone'].forEach(function(id) {
        document.getElementById(id).addEventListener('input', function() {
            var key = id === 'businessWebsite' ? 'website' : id;
            wizardData[key] = this.value;
            triggerAutosave();
        });
    });
    
    document.getElementById('showPhoneToggle').addEventListener('change', function() {
        wizardData.showPhone = this.checked;
        triggerAutosave();
    });
    
    document.getElementById('showWebsiteToggle').addEventListener('change', function() {
        wizardData.showWebsite = this.checked;
        triggerAutosave();
    });
    
    document.getElementById('showEmailToggle').addEventListener('change', function() {
        wizardData.showEmail = this.checked;
        triggerAutosave();
    });
    
    document.getElementById('supportPhone').addEventListener('blur', function() {
        var value = this.value.replace(/[\s\-\(\)]/g, '');
        if (value.startsWith('0')) {
            value = value.substring(1);
        }
        if (value.startsWith('44')) {
            value = value.substring(2);
        }
        if (value.startsWith('+44')) {
            value = value.substring(3);
        }
        this.value = value;
        wizardData.supportPhone = value;
    });
}

function openAgentWizard(existingAgent) {
    resetWizardData();
    
    var isLocked = existingAgent && ['submitted', 'in_review', 'approved'].includes(existingAgent.status);
    var agentId = existingAgent ? existingAgent.id : null;
    
    // Check for saved draft in localStorage (non-functional requirement: draft recovery)
    var savedDraft = loadDraftFromStorage(agentId);
    var draftRestored = false;
    
    if (savedDraft && !isLocked) {
        // Prompt user to recover draft or start fresh
        var draftDate = new Date(savedDraft.savedAt);
        var draftAgeMinutes = Math.round((Date.now() - draftDate.getTime()) / 60000);
        var draftAgeText = draftAgeMinutes < 60 ? 
            draftAgeMinutes + ' minute' + (draftAgeMinutes !== 1 ? 's' : '') + ' ago' :
            Math.round(draftAgeMinutes / 60) + ' hour' + (Math.round(draftAgeMinutes / 60) !== 1 ? 's' : '') + ' ago';
        
        if (confirm('A draft was found from ' + draftAgeText + '. Would you like to restore it?\n\nClick OK to restore, or Cancel to start fresh.')) {
            restoreWizardFromDraft(savedDraft);
            draftRestored = true;
            showNotification('info', 'Draft Restored', 'Your previous work has been restored from the saved draft.');
        } else {
            // User chose to start fresh - clear the old draft
            clearDraftFromStorage(agentId);
        }
    }
    
    if (existingAgent && !draftRestored) {
        wizardData.id = existingAgent.id;
        wizardData.name = existingAgent.name || '';
        wizardData.billing = existingAgent.billing || '';
        wizardData.useCase = existingAgent.useCase || '';
        wizardData.isEditing = true;
        wizardData.isSubmitted = isLocked;
        document.getElementById('agentWizardTitle').innerHTML = '<i class="fas fa-robot me-2"></i>' + (isLocked ? 'View RCS Agent' : 'Edit RCS Agent');
        
        document.getElementById('agentName').value = wizardData.name;
        if (wizardData.billing) {
            var billingTile = document.querySelector('.billing-tile[data-billing="' + wizardData.billing + '"]');
            if (billingTile) {
                billingTile.classList.add('selected');
            }
        }
        if (wizardData.useCase) {
            var useCaseTile = document.querySelector('.usecase-tile[data-usecase="' + wizardData.useCase + '"]');
            if (useCaseTile) {
                useCaseTile.classList.add('selected');
            }
        }
        
        if (isLocked) {
            lockWizardFields();
        }
    } else if (!existingAgent && !draftRestored) {
        wizardData.id = 'agent-' + Date.now();
        document.getElementById('agentWizardTitle').innerHTML = '<i class="fas fa-robot me-2"></i>Create RCS Agent';
    }
    
    // If draft was restored, populate the UI
    if (draftRestored) {
        populateWizardUIFromData();
        document.getElementById('agentWizardTitle').innerHTML = '<i class="fas fa-robot me-2"></i>' + 
            (wizardData.isEditing ? 'Edit RCS Agent' : 'Create RCS Agent');
    }
    
    goToStep(draftRestored ? wizardData.currentStep : 1);
    wizardModal.show();
}

function lockWizardFields() {
    var inputs = document.querySelectorAll('#agentWizardModal input:not([type="file"]), #agentWizardModal textarea, #agentWizardModal select');
    inputs.forEach(function(input) {
        input.disabled = true;
    });
    
    document.querySelectorAll('.billing-tile, .usecase-tile').forEach(function(tile) {
        tile.style.pointerEvents = 'none';
        tile.style.opacity = '0.7';
    });
    
    var logoZone = document.getElementById('agentLogoUploadZone');
    var heroZone = document.getElementById('agentHeroUploadZone');
    if (logoZone) logoZone.style.pointerEvents = 'none';
    if (heroZone) heroZone.style.pointerEvents = 'none';
    document.getElementById('wizardSubmitBtn').style.display = 'none';
    document.getElementById('wizardNextBtn').textContent = 'Next';
    
    var lockedBanner = document.createElement('div');
    lockedBanner.id = 'lockedBanner';
    lockedBanner.className = 'alert alert-info mb-0';
    lockedBanner.style.cssText = 'position: absolute; top: 60px; left: 50%; transform: translateX(-50%); z-index: 10; max-width: 600px;';
    lockedBanner.innerHTML = '<i class="fas fa-lock me-2"></i>This agent has been submitted and cannot be edited.';
    
    var existingBanner = document.getElementById('lockedBanner');
    if (!existingBanner) {
        document.querySelector('#agentWizardModal .modal-body').prepend(lockedBanner);
    }
}

function resetWizardData() {
    wizardData = {
        id: null,
        name: '',
        description: '',
        billing: '',
        useCase: '',
        logoFile: null,
        logoDataUrl: null,
        logoCropMetadata: null,
        heroFile: null,
        heroDataUrl: null,
        heroCropMetadata: null,
        brandColor: '#886CC0',
        website: '',
        privacyUrl: '',
        termsUrl: '',
        supportEmail: '',
        supportPhone: '',
        showPhone: true,
        showWebsite: true,
        showEmail: true,
        campaignFrequency: '',
        monthlyVolume: '',
        optInDescription: '',
        optOutDescription: '',
        useCaseOverview: '',
        testNumbers: [],
        companyNumber: '',
        companyWebsite: '',
        registeredAddress: '',
        approverName: '',
        approverJobTitle: '',
        approverEmail: '',
        currentStep: 1,
        isEditing: false,
        isDirty: false,
        logoValid: false,
        heroValid: false,
        isSubmitted: false
    };
    
    document.getElementById('agentName').value = '';
    document.getElementById('agentDescription').value = '';
    document.getElementById('descCharCount').textContent = '0';
    document.querySelectorAll('.billing-tile, .usecase-tile').forEach(function(t) {
        t.classList.remove('selected');
    });
    // Reset shared image editors using their API
    if (typeof agentLogoReset === 'function') agentLogoReset();
    if (typeof agentHeroReset === 'function') agentHeroReset();
    document.getElementById('brandColor').value = '#886CC0';
    document.getElementById('brandColorHex').value = '#886CC0';
    document.getElementById('businessWebsite').value = '';
    document.getElementById('privacyUrl').value = '';
    document.getElementById('termsUrl').value = '';
    document.getElementById('supportEmail').value = '';
    document.getElementById('supportPhone').value = '';
    document.getElementById('showPhoneToggle').checked = true;
    document.getElementById('showWebsiteToggle').checked = true;
    document.getElementById('showEmailToggle').checked = true;
    
    // Hide shared component error alerts (they use d-none class)
    var logoErr = document.getElementById('agentLogoError');
    var heroErr = document.getElementById('agentHeroError');
    if (logoErr) logoErr.classList.add('d-none');
    if (heroErr) heroErr.classList.add('d-none');
    
    document.getElementById('campaignFrequency').value = '';
    document.getElementById('monthlyVolume').value = '';
    document.getElementById('optInDescription').value = '';
    document.getElementById('optOutDescription').value = '';
    document.getElementById('useCaseOverview').value = '';
    document.getElementById('optInCharCount').textContent = '0';
    document.getElementById('optOutCharCount').textContent = '0';
    document.getElementById('useCaseCharCount').textContent = '0';
    document.getElementById('testNumberInput').value = '';
    document.getElementById('testNumbersList').innerHTML = '';
    document.getElementById('testNumberCount').textContent = '0';
    document.getElementById('clearAllTestNumbers').style.display = 'none';
    document.getElementById('billingError').style.display = 'none';
    document.getElementById('useCaseError').style.display = 'none';
    document.getElementById('testNumberError').style.display = 'none';
    
    document.getElementById('companyNumber').value = '';
    document.getElementById('companyWebsite').value = '';
    document.getElementById('registeredAddress').value = '';
    document.getElementById('approverName').value = '';
    document.getElementById('approverJobTitle').value = '';
    document.getElementById('approverEmail').value = '';
    
    document.querySelectorAll('.form-control.is-invalid, .form-select.is-invalid').forEach(function(el) {
        el.classList.remove('is-invalid');
    });
    
    unlockWizardFields();
    
    var lockedBanner = document.getElementById('lockedBanner');
    if (lockedBanner) {
        lockedBanner.remove();
    }
    
    updateAutosaveIndicator('saved');
}

function unlockWizardFields() {
    var inputs = document.querySelectorAll('#agentWizardModal input:not([type="file"]), #agentWizardModal textarea, #agentWizardModal select');
    inputs.forEach(function(input) {
        input.disabled = false;
    });
    
    document.querySelectorAll('.billing-tile, .usecase-tile').forEach(function(tile) {
        tile.style.pointerEvents = '';
        tile.style.opacity = '';
    });
    
    var logoZone = document.getElementById('agentLogoUploadZone');
    var heroZone = document.getElementById('agentHeroUploadZone');
    if (logoZone) logoZone.style.pointerEvents = '';
    if (heroZone) heroZone.style.pointerEvents = '';
}

function prefillCompanyDetails() {
    if (!wizardData.companyNumber) {
        wizardData.companyNumber = mockAccountDetails.companyNumber;
        document.getElementById('companyNumber').value = mockAccountDetails.companyNumber;
    }
    if (!wizardData.companyWebsite) {
        wizardData.companyWebsite = mockAccountDetails.companyWebsite;
        document.getElementById('companyWebsite').value = mockAccountDetails.companyWebsite;
    }
    if (!wizardData.registeredAddress) {
        wizardData.registeredAddress = mockAccountDetails.registeredAddress;
        document.getElementById('registeredAddress').value = mockAccountDetails.registeredAddress;
    }
    if (!wizardData.approverName) {
        wizardData.approverName = mockAccountDetails.approverName;
        document.getElementById('approverName').value = mockAccountDetails.approverName;
    }
    if (!wizardData.approverJobTitle) {
        wizardData.approverJobTitle = mockAccountDetails.approverJobTitle;
        document.getElementById('approverJobTitle').value = mockAccountDetails.approverJobTitle;
    }
    if (!wizardData.approverEmail) {
        wizardData.approverEmail = mockAccountDetails.approverEmail;
        document.getElementById('approverEmail').value = mockAccountDetails.approverEmail;
    }
}

function addTestNumber() {
    var input = document.getElementById('testNumberInput');
    var number = input.value.trim();
    var errorEl = document.getElementById('testNumberError');
    
    if (!number) return;
    
    if (!isValidInternationalNumber(number)) {
        errorEl.style.display = 'block';
        input.classList.add('is-invalid');
        return;
    }
    
    if (wizardData.testNumbers.length >= 20) {
        errorEl.textContent = 'Maximum 20 test numbers allowed';
        errorEl.style.display = 'block';
        return;
    }
    
    if (wizardData.testNumbers.includes(number)) {
        errorEl.textContent = 'This number has already been added';
        errorEl.style.display = 'block';
        return;
    }
    
    errorEl.style.display = 'none';
    input.classList.remove('is-invalid');
    
    wizardData.testNumbers.push(number);
    input.value = '';
    renderTestNumbers();
    triggerAutosave();
}

function removeTestNumber(number) {
    var index = wizardData.testNumbers.indexOf(number);
    if (index > -1) {
        wizardData.testNumbers.splice(index, 1);
        renderTestNumbers();
        triggerAutosave();
    }
}

function clearAllTestNumbers() {
    wizardData.testNumbers = [];
    renderTestNumbers();
    triggerAutosave();
}

function renderTestNumbers() {
    var container = document.getElementById('testNumbersList');
    container.innerHTML = '';
    
    wizardData.testNumbers.forEach(function(number) {
        var tag = document.createElement('span');
        tag.className = 'test-number-tag';
        tag.innerHTML = '<i class="fas fa-mobile-alt me-2 text-muted"></i>' + escapeHtml(number) +
            '<span class="remove-number" onclick="removeTestNumber(\'' + escapeHtml(number) + '\')">' +
            '<i class="fas fa-times"></i></span>';
        container.appendChild(tag);
    });
    
    document.getElementById('testNumberCount').textContent = wizardData.testNumbers.length;
    document.getElementById('clearAllTestNumbers').style.display = wizardData.testNumbers.length > 0 ? '' : 'none';
}

function isValidInternationalNumber(number) {
    var cleaned = number.replace(/[\s\-\(\)]/g, '');
    return /^\+[1-9]\d{7,14}$/.test(cleaned);
}

function goToStep(step) {
    wizardData.currentStep = step;
    
    for (var i = 1; i <= 8; i++) {
        var stepEl = document.getElementById('agentWizardStep' + i);
        if (stepEl) {
            stepEl.classList.toggle('d-none', i !== step);
        }
    }
    
    document.querySelectorAll('.wizard-step').forEach(function(el) {
        var stepNum = parseInt(el.dataset.step);
        el.classList.remove('active', 'completed');
        if (stepNum < step) {
            el.classList.add('completed');
        } else if (stepNum === step) {
            el.classList.add('active');
        }
    });
    
    document.getElementById('wizardPrevBtn').style.display = step > 1 ? '' : 'none';
    document.getElementById('wizardNextBtn').style.display = step < 8 ? '' : 'none';
    document.getElementById('wizardSubmitBtn').style.display = step === 8 ? '' : 'none';
    
    if (step === 6) {
        prefillCompanyDetails();
    }
    
    if (step === 8) {
        populateReviewStep();
    }
}

function nextStep() {
    if (!validateCurrentStep()) return;
    
    if (wizardData.currentStep < 8) {
        goToStep(wizardData.currentStep + 1);
    }
}

function prevStep() {
    if (wizardData.currentStep > 1) {
        goToStep(wizardData.currentStep - 1);
    }
}

function validateCurrentStep() {
    var isValid = true;
    
    document.querySelectorAll('.form-control.is-invalid').forEach(function(el) {
        el.classList.remove('is-invalid');
    });
    var logoErr = document.getElementById('agentLogoError');
    var heroErr = document.getElementById('agentHeroError');
    if (logoErr) logoErr.classList.add('d-none');
    if (heroErr) heroErr.classList.add('d-none');
    
    if (wizardData.currentStep === 1) {
        // Step 1: Agent Basics (name, description, brand colour)
        if (!wizardData.name.trim() || wizardData.name.length > 25) {
            document.getElementById('agentName').classList.add('is-invalid');
            isValid = false;
        }
        if (!wizardData.description.trim() || wizardData.description.length > 100) {
            document.getElementById('agentDescription').classList.add('is-invalid');
            isValid = false;
        }
    } else if (wizardData.currentStep === 2) {
        // Step 2: Branding Assets (logo, hero - both required)
        if (!wizardData.logoDataUrl || !wizardData.logoValid) {
            var logoErr = document.getElementById('agentLogoError');
            if (logoErr) {
                // Only set text if element is empty (avoid overwriting component errors)
                if (!logoErr.textContent.trim()) {
                    logoErr.textContent = 'Please upload a logo image';
                }
                logoErr.classList.remove('d-none');
            }
            isValid = false;
        }
        if (!wizardData.heroDataUrl || !wizardData.heroValid) {
            var heroErr = document.getElementById('agentHeroError');
            if (heroErr) {
                // Only set text if element is empty (avoid overwriting component errors)
                if (!heroErr.textContent.trim()) {
                    heroErr.textContent = 'Please upload a hero/banner image';
                }
                heroErr.classList.remove('d-none');
            }
            isValid = false;
        }
    } else if (wizardData.currentStep === 3) {
        // Step 3: Handset + Compliance (contact details + privacy/terms URLs)
        if (!wizardData.supportPhone.trim() || !isValidUKPhone(wizardData.supportPhone)) {
            document.getElementById('supportPhone').classList.add('is-invalid');
            isValid = false;
        }
        if (!wizardData.website.trim() || !isValidHttpsUrl(wizardData.website)) {
            document.getElementById('businessWebsite').classList.add('is-invalid');
            isValid = false;
        }
        if (!wizardData.supportEmail.trim() || !isValidEmail(wizardData.supportEmail)) {
            document.getElementById('supportEmail').classList.add('is-invalid');
            isValid = false;
        }
        if (!wizardData.privacyUrl.trim() || !isValidHttpsUrl(wizardData.privacyUrl)) {
            document.getElementById('privacyUrl').classList.add('is-invalid');
            isValid = false;
        }
        if (!wizardData.termsUrl.trim() || !isValidHttpsUrl(wizardData.termsUrl)) {
            document.getElementById('termsUrl').classList.add('is-invalid');
            isValid = false;
        }
    } else if (wizardData.currentStep === 4) {
        // Step 4: Agent Type (billing, use case, use case overview)
        if (!wizardData.billing) {
            document.getElementById('billingError').style.display = 'block';
            isValid = false;
        }
        if (!wizardData.useCase) {
            document.getElementById('useCaseError').style.display = 'block';
            isValid = false;
        }
        if (!wizardData.useCaseOverview.trim()) {
            document.getElementById('useCaseOverview').classList.add('is-invalid');
            isValid = false;
        }
    } else if (wizardData.currentStep === 5) {
        // Step 5: Messaging Behaviour (campaign frequency, monthly volume, opt-in, opt-out)
        if (!wizardData.campaignFrequency) {
            document.getElementById('campaignFrequency').classList.add('is-invalid');
            isValid = false;
        }
        if (!wizardData.monthlyVolume) {
            document.getElementById('monthlyVolume').classList.add('is-invalid');
            isValid = false;
        }
        if (!wizardData.optInDescription.trim()) {
            document.getElementById('optInDescription').classList.add('is-invalid');
            isValid = false;
        }
        if (!wizardData.optOutDescription.trim()) {
            document.getElementById('optOutDescription').classList.add('is-invalid');
            isValid = false;
        }
    } else if (wizardData.currentStep === 6) {
        // Step 6: Company Details (company + approver)
        if (!wizardData.companyNumber.trim()) {
            document.getElementById('companyNumber').classList.add('is-invalid');
            isValid = false;
        }
        if (!wizardData.companyWebsite.trim() || !isValidUrl(wizardData.companyWebsite)) {
            document.getElementById('companyWebsite').classList.add('is-invalid');
            isValid = false;
        }
        if (!wizardData.registeredAddress.trim()) {
            document.getElementById('registeredAddress').classList.add('is-invalid');
            isValid = false;
        }
        if (!wizardData.approverName.trim()) {
            document.getElementById('approverName').classList.add('is-invalid');
            isValid = false;
        }
        if (!wizardData.approverJobTitle.trim()) {
            document.getElementById('approverJobTitle').classList.add('is-invalid');
            isValid = false;
        }
        if (!wizardData.approverEmail.trim() || !isValidEmail(wizardData.approverEmail)) {
            document.getElementById('approverEmail').classList.add('is-invalid');
            isValid = false;
        }
    }
    // Step 7 (Test Numbers) and Step 8 (Review) - no validation needed
    
    return isValid;
}

function isValidUKPhone(phone) {
    var cleaned = phone.replace(/[\s\-\(\)]/g, '');
    return /^\d{9,11}$/.test(cleaned);
}

function isValidHttpsUrl(str) {
    try {
        var url = new URL(str);
        return url.protocol === 'https:';
    } catch (e) {
        return false;
    }
}

function isValidUrl(str) {
    try {
        new URL(str);
        return true;
    } catch (e) {
        return false;
    }
}

function isValidEmail(str) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(str);
}

function populateReviewStep() {
    document.getElementById('reviewAgentName').textContent = wizardData.name || '-';
    document.getElementById('reviewDescription').textContent = wizardData.description || '-';
    document.getElementById('reviewLogo').textContent = wizardData.logoDataUrl ? 'Uploaded' : 'Not uploaded';
    document.getElementById('reviewHero').textContent = wizardData.heroDataUrl ? 'Uploaded' : 'Not uploaded';
    document.getElementById('reviewColorPreview').style.backgroundColor = wizardData.brandColor;
    document.getElementById('reviewColor').textContent = wizardData.brandColor;
    
    document.getElementById('reviewPhone').textContent = wizardData.supportPhone ? '+44 ' + wizardData.supportPhone : '-';
    document.getElementById('reviewPhoneDisplay').textContent = wizardData.showPhone ? 'Visible' : 'Hidden';
    document.getElementById('reviewPhoneDisplay').className = 'badge ms-2 ' + (wizardData.showPhone ? 'badge-pastel-primary' : 'badge-pastel-secondary');
    
    document.getElementById('reviewWebsite').textContent = wizardData.website || '-';
    document.getElementById('reviewWebsiteDisplay').textContent = wizardData.showWebsite ? 'Visible' : 'Hidden';
    document.getElementById('reviewWebsiteDisplay').className = 'badge ms-2 ' + (wizardData.showWebsite ? 'badge-pastel-primary' : 'badge-pastel-secondary');
    
    document.getElementById('reviewEmail').textContent = wizardData.supportEmail || '-';
    document.getElementById('reviewEmailDisplay').textContent = wizardData.showEmail ? 'Visible' : 'Hidden';
    document.getElementById('reviewEmailDisplay').className = 'badge ms-2 ' + (wizardData.showEmail ? 'badge-pastel-primary' : 'badge-pastel-secondary');
    
    document.getElementById('reviewPrivacy').textContent = wizardData.privacyUrl || '-';
    document.getElementById('reviewTerms').textContent = wizardData.termsUrl || '-';
    
    document.getElementById('reviewBilling').textContent = wizardData.billing === 'conversational' ? 'Conversational' : 'Non-conversational';
    document.getElementById('reviewUseCase').textContent = formatUseCase(wizardData.useCase);
    
    document.getElementById('reviewFrequency').textContent = formatFrequency(wizardData.campaignFrequency);
    document.getElementById('reviewVolume').textContent = formatVolume(wizardData.monthlyVolume);
    document.getElementById('reviewOptIn').textContent = wizardData.optInDescription || '-';
    document.getElementById('reviewOptOut').textContent = wizardData.optOutDescription || '-';
    document.getElementById('reviewUseCaseOverview').textContent = wizardData.useCaseOverview || '-';
    document.getElementById('reviewTestNumbers').textContent = wizardData.testNumbers.length > 0 ? wizardData.testNumbers.join(', ') : 'None added';
    
    document.getElementById('reviewCompanyNumber').textContent = wizardData.companyNumber || '-';
    document.getElementById('reviewCompanyWebsite').textContent = wizardData.companyWebsite || '-';
    document.getElementById('reviewRegisteredAddress').textContent = wizardData.registeredAddress || '-';
    document.getElementById('reviewApproverName').textContent = wizardData.approverName || '-';
    document.getElementById('reviewApproverJobTitle').textContent = wizardData.approverJobTitle || '-';
    document.getElementById('reviewApproverEmail').textContent = wizardData.approverEmail || '-';
}

function formatFrequency(value) {
    var labels = {
        'daily': 'Daily',
        'weekly': 'Weekly',
        'monthly': 'Monthly',
        'on-demand': 'On-demand / Event-triggered',
        'continuous': 'Continuous (24/7)'
    };
    return labels[value] || '-';
}

function formatVolume(value) {
    var labels = {
        '0-1000': 'Up to 1,000 messages',
        '1000-10000': '1,000 - 10,000 messages',
        '10000-100000': '10,000 - 100,000 messages',
        '100000-500000': '100,000 - 500,000 messages',
        '500000-1000000': '500,000 - 1,000,000 messages',
        '1000000-5000000': '1M - 5M messages',
        '5000000-10000000': '5M - 10M messages'
    };
    return labels[value] || '-';
}

function handleWizardCancel() {
    if (wizardData.isDirty) {
        exitModal.show();
    } else {
        wizardModal.hide();
    }
}

function keepDraftAndExit() {
    autosaveDraft();
    exitModal.hide();
    wizardModal.hide();
}

function discardDraftAndExit() {
    // Clear the draft from localStorage when user explicitly discards
    clearDraftFromStorage(wizardData.id);
    exitModal.hide();
    wizardModal.hide();
}

function triggerAutosave() {
    wizardData.isDirty = true;
    updateAutosaveIndicator('saving');
    
    if (autosaveTimeout) {
        clearTimeout(autosaveTimeout);
    }
    
    autosaveTimeout = setTimeout(function() {
        autosaveDraft();
    }, 1500);
}

function autosaveDraft() {
    // Persist draft to localStorage for recovery on refresh/navigation
    var draftKey = getDraftStorageKey();
    
    try {
        var draftData = {
            id: wizardData.id,
            name: wizardData.name,
            description: wizardData.description,
            billing: wizardData.billing,
            useCase: wizardData.useCase,
            logoDataUrl: wizardData.logoDataUrl,
            logoCropMetadata: wizardData.logoCropMetadata,
            heroDataUrl: wizardData.heroDataUrl,
            brandColor: wizardData.brandColor,
            website: wizardData.website,
            privacyUrl: wizardData.privacyUrl,
            termsUrl: wizardData.termsUrl,
            supportEmail: wizardData.supportEmail,
            supportPhone: wizardData.supportPhone,
            showPhone: wizardData.showPhone,
            showWebsite: wizardData.showWebsite,
            showEmail: wizardData.showEmail,
            campaignFrequency: wizardData.campaignFrequency,
            monthlyVolume: wizardData.monthlyVolume,
            optInDescription: wizardData.optInDescription,
            optOutDescription: wizardData.optOutDescription,
            useCaseOverview: wizardData.useCaseOverview,
            testNumbers: wizardData.testNumbers,
            companyNumber: wizardData.companyNumber,
            companyWebsite: wizardData.companyWebsite,
            registeredAddress: wizardData.registeredAddress,
            approverName: wizardData.approverName,
            approverJobTitle: wizardData.approverJobTitle,
            approverEmail: wizardData.approverEmail,
            currentStep: wizardData.currentStep,
            isEditing: wizardData.isEditing,
            tempDraftId: wizardData.tempDraftId,
            savedAt: new Date().toISOString()
        };
        
        localStorage.setItem(draftKey, JSON.stringify(draftData));
        
        // Also maintain a draft index for listing all drafts (for all draft types, not just new)
        updateDraftIndex(draftKey, wizardData.name || 'Untitled Agent');
        
        console.log('[Autosave] Draft saved to localStorage:', draftKey);
    } catch (e) {
        console.error('[Autosave] Failed to save draft:', e);
        // Notify user of storage failure to maintain "no data loss" guarantee
        showNotification('warning', 'Draft Save Issue', 'Unable to save draft to browser storage. Your work may not be preserved if you refresh the page.');
    }
    
    updateAutosaveIndicator('saved');
    wizardData.isDirty = false;
}

function getDraftStorageKey() {
    // Use agent ID for existing agents, or generate temp ID for new ones
    if (wizardData.id) {
        return 'rcs_agent_draft_' + wizardData.id;
    }
    // For new agents, use a session-based temp key
    if (!wizardData.tempDraftId) {
        wizardData.tempDraftId = 'new_' + Date.now();
    }
    return 'rcs_agent_draft_' + wizardData.tempDraftId;
}

function updateDraftIndex(draftKey, agentName) {
    // Maintain index of all drafts for recovery UI
    try {
        var indexStr = localStorage.getItem('rcs_agent_draft_index') || '{}';
        var index = JSON.parse(indexStr);
        index[draftKey] = {
            name: agentName,
            savedAt: new Date().toISOString()
        };
        localStorage.setItem('rcs_agent_draft_index', JSON.stringify(index));
    } catch (e) {
        console.error('[Autosave] Failed to update draft index:', e);
    }
}

function loadDraftFromStorage(agentId) {
    var draftKey = agentId ? 'rcs_agent_draft_' + agentId : null;
    
    // For new agents, check for any unsaved new drafts
    if (!draftKey) {
        draftKey = findLatestNewDraft();
    }
    
    if (!draftKey) return null;
    
    try {
        var draftStr = localStorage.getItem(draftKey);
        if (draftStr) {
            var draft = JSON.parse(draftStr);
            console.log('[Autosave] Loaded draft from localStorage:', draftKey);
            return draft;
        }
    } catch (e) {
        console.error('[Autosave] Failed to load draft:', e);
    }
    return null;
}

function findLatestNewDraft() {
    // Find the most recent unsaved new agent draft
    try {
        var indexStr = localStorage.getItem('rcs_agent_draft_index') || '{}';
        var index = JSON.parse(indexStr);
        var latestKey = null;
        var latestTime = 0;
        
        for (var key in index) {
            if (key.startsWith('rcs_agent_draft_new_')) {
                var savedAt = new Date(index[key].savedAt).getTime();
                if (savedAt > latestTime) {
                    latestTime = savedAt;
                    latestKey = key;
                }
            }
        }
        return latestKey;
    } catch (e) {
        return null;
    }
}

function clearDraftFromStorage(agentId) {
    // Build the key - handle both real IDs and temp IDs
    var draftKey;
    if (agentId) {
        draftKey = 'rcs_agent_draft_' + agentId;
    } else if (wizardData.tempDraftId) {
        draftKey = 'rcs_agent_draft_' + wizardData.tempDraftId;
    } else {
        // No ID to clear
        return;
    }
    
    try {
        localStorage.removeItem(draftKey);
        
        // Remove from index - handles all draft types
        var indexStr = localStorage.getItem('rcs_agent_draft_index') || '{}';
        var index = JSON.parse(indexStr);
        delete index[draftKey];
        localStorage.setItem('rcs_agent_draft_index', JSON.stringify(index));
        
        console.log('[Autosave] Cleared draft from localStorage:', draftKey);
    } catch (e) {
        console.error('[Autosave] Failed to clear draft:', e);
    }
}

function restoreWizardFromDraft(draft) {
    if (!draft) return false;
    
    // Restore all fields from draft
    wizardData.id = draft.id || null;
    wizardData.name = draft.name || '';
    wizardData.description = draft.description || '';
    wizardData.billing = draft.billing || '';
    wizardData.useCase = draft.useCase || '';
    wizardData.logoDataUrl = draft.logoDataUrl || null;
    wizardData.logoCropMetadata = draft.logoCropMetadata || null;
    wizardData.heroDataUrl = draft.heroDataUrl || null;
    wizardData.brandColor = draft.brandColor || '#886CC0';
    wizardData.website = draft.website || '';
    wizardData.privacyUrl = draft.privacyUrl || '';
    wizardData.termsUrl = draft.termsUrl || '';
    wizardData.supportEmail = draft.supportEmail || '';
    wizardData.supportPhone = draft.supportPhone || '';
    wizardData.showPhone = draft.showPhone !== false;
    wizardData.showWebsite = draft.showWebsite !== false;
    wizardData.showEmail = draft.showEmail !== false;
    wizardData.campaignFrequency = draft.campaignFrequency || '';
    wizardData.monthlyVolume = draft.monthlyVolume || '';
    wizardData.optInDescription = draft.optInDescription || '';
    wizardData.optOutDescription = draft.optOutDescription || '';
    wizardData.useCaseOverview = draft.useCaseOverview || '';
    wizardData.testNumbers = draft.testNumbers || [];
    wizardData.companyNumber = draft.companyNumber || '';
    wizardData.companyWebsite = draft.companyWebsite || '';
    wizardData.registeredAddress = draft.registeredAddress || '';
    wizardData.approverName = draft.approverName || '';
    wizardData.approverJobTitle = draft.approverJobTitle || '';
    wizardData.approverEmail = draft.approverEmail || '';
    wizardData.currentStep = draft.currentStep || 1;
    wizardData.isEditing = draft.isEditing || false;
    // Preserve tempDraftId for new agent drafts (important for clearDraftFromStorage)
    wizardData.tempDraftId = draft.tempDraftId || null;
    
    // Mark images as valid if we have data URLs
    wizardData.logoValid = !!draft.logoDataUrl;
    wizardData.heroValid = !!draft.heroDataUrl;
    
    return true;
}

function populateWizardUIFromData() {
    // Populate form fields from wizardData
    var nameInput = document.getElementById('agentName');
    if (nameInput) nameInput.value = wizardData.name;
    
    var descInput = document.getElementById('agentDescription');
    if (descInput) descInput.value = wizardData.description;
    
    // Billing category tiles
    if (wizardData.billing) {
        document.querySelectorAll('.billing-category-tile').forEach(function(tile) {
            tile.classList.toggle('selected', tile.dataset.billing === wizardData.billing);
        });
    }
    
    // Use case tiles
    if (wizardData.useCase) {
        document.querySelectorAll('.use-case-tile').forEach(function(tile) {
            tile.classList.toggle('selected', tile.dataset.usecase === wizardData.useCase);
        });
    }
    
    // Restore logo using shared component API
    if (wizardData.logoDataUrl && typeof agentLogoLoadImage === 'function') {
        agentLogoLoadImage(wizardData.logoDataUrl, function(err) {
            if (err) console.warn('Failed to restore logo from draft');
        });
    }
    
    // Restore hero using shared component API
    if (wizardData.heroDataUrl && typeof agentHeroLoadImage === 'function') {
        agentHeroLoadImage(wizardData.heroDataUrl, function(err) {
            if (err) console.warn('Failed to restore hero from draft');
        });
    }
    
    // Brand color
    var colorPicker = document.getElementById('brandColorPicker');
    var colorInput = document.getElementById('brandColorInput');
    if (colorPicker) colorPicker.value = wizardData.brandColor;
    if (colorInput) colorInput.value = wizardData.brandColor;
    
    // Contact fields
    var fields = ['website', 'privacyUrl', 'termsUrl', 'supportEmail', 'supportPhone'];
    fields.forEach(function(field) {
        var el = document.getElementById(field);
        if (el) el.value = wizardData[field] || '';
    });
    
    // Visibility toggles
    var showPhone = document.getElementById('showPhoneToggle');
    var showWebsite = document.getElementById('showWebsiteToggle');
    var showEmail = document.getElementById('showEmailToggle');
    if (showPhone) showPhone.checked = wizardData.showPhone;
    if (showWebsite) showWebsite.checked = wizardData.showWebsite;
    if (showEmail) showEmail.checked = wizardData.showEmail;
    
    // Step 2 fields
    var step2Fields = ['campaignFrequency', 'monthlyVolume', 'optInDescription', 'optOutDescription', 'useCaseOverview'];
    step2Fields.forEach(function(field) {
        var el = document.getElementById(field);
        if (el) el.value = wizardData[field] || '';
    });
    
    // Step 3 fields
    var step3Fields = ['companyNumber', 'companyWebsite', 'registeredAddress', 'approverName', 'approverJobTitle', 'approverEmail'];
    step3Fields.forEach(function(field) {
        var el = document.getElementById(field);
        if (el) el.value = wizardData[field] || '';
    });
    
    // Restore test numbers
    if (wizardData.testNumbers && wizardData.testNumbers.length > 0) {
        renderTestNumbers();
    }
}

function updateAutosaveIndicator(status) {
    var indicator = document.getElementById('autosaveIndicator');
    var text = document.getElementById('autosaveText');
    
    indicator.classList.remove('saving', 'saved');
    
    if (status === 'saving') {
        indicator.classList.add('saving');
        text.textContent = 'Saving...';
    } else {
        indicator.classList.add('saved');
        text.textContent = 'Draft saved';
    }
}

function validateAllSteps() {
    var errors = [];
    
    if (!wizardData.name.trim() || wizardData.name.length > 25) {
        errors.push('Agent name is required (max 25 characters)');
    }
    if (!wizardData.description.trim() || wizardData.description.length > 100) {
        errors.push('Agent description is required (max 100 characters)');
    }
    if (!wizardData.logoDataUrl) {
        errors.push('Logo image is required');
    }
    if (!wizardData.supportPhone.trim() || !isValidUKPhone(wizardData.supportPhone)) {
        errors.push('Valid UK phone number is required');
    }
    if (!wizardData.website.trim() || !isValidHttpsUrl(wizardData.website)) {
        errors.push('Website URL must be valid HTTPS');
    }
    if (!wizardData.supportEmail.trim() || !isValidEmail(wizardData.supportEmail)) {
        errors.push('Valid email address is required');
    }
    if (!wizardData.privacyUrl.trim() || !isValidHttpsUrl(wizardData.privacyUrl)) {
        errors.push('Privacy Policy URL must be valid HTTPS');
    }
    if (!wizardData.termsUrl.trim() || !isValidHttpsUrl(wizardData.termsUrl)) {
        errors.push('Terms of Service URL must be valid HTTPS');
    }
    
    if (!wizardData.billing) {
        errors.push('Billing category is required');
    }
    if (!wizardData.useCase) {
        errors.push('Use case is required');
    }
    if (!wizardData.campaignFrequency) {
        errors.push('Campaign frequency is required');
    }
    if (!wizardData.monthlyVolume) {
        errors.push('Monthly volume is required');
    }
    if (!wizardData.optInDescription.trim()) {
        errors.push('Opt-in description is required');
    }
    if (!wizardData.optOutDescription.trim()) {
        errors.push('Opt-out description is required');
    }
    if (!wizardData.useCaseOverview.trim()) {
        errors.push('Use case overview is required');
    }
    
    if (!wizardData.companyNumber.trim()) {
        errors.push('Company number is required');
    }
    if (!wizardData.companyWebsite.trim() || !isValidUrl(wizardData.companyWebsite)) {
        errors.push('Company website must be a valid URL');
    }
    if (!wizardData.registeredAddress.trim()) {
        errors.push('Registered address is required');
    }
    if (!wizardData.approverName.trim()) {
        errors.push('Approver name is required');
    }
    if (!wizardData.approverJobTitle.trim()) {
        errors.push('Approver job title is required');
    }
    if (!wizardData.approverEmail.trim() || !isValidEmail(wizardData.approverEmail)) {
        errors.push('Approver email must be valid');
    }
    
    return errors;
}

function showValidationErrors(errors) {
    var errorHtml = '<div class="alert alert-danger mb-0">' +
        '<h6 class="alert-heading mb-2"><i class="fas fa-exclamation-triangle me-2"></i>Please correct the following errors:</h6>' +
        '<ul class="mb-0 ps-3">';
    errors.forEach(function(err) {
        errorHtml += '<li>' + err + '</li>';
    });
    errorHtml += '</ul></div>';
    
    var container = document.getElementById('submissionErrors');
    if (!container) {
        container = document.createElement('div');
        container.id = 'submissionErrors';
        container.style.cssText = 'position: fixed; bottom: 80px; left: 50%; transform: translateX(-50%); z-index: 10001; max-width: 600px; width: 90%;';
        document.body.appendChild(container);
    }
    container.innerHTML = errorHtml;
    
    setTimeout(function() {
        container.innerHTML = '';
    }, 8000);
}

function submitAgent() {
    var errors = validateAllSteps();
    
    if (errors.length > 0) {
        showValidationErrors(errors);
        return;
    }
    
    updateAutosaveIndicator('saving');
    
    setTimeout(function() {
        updateAutosaveIndicator('saved');
        
        wizardData.isSubmitted = true;
        
        var newAgent = {
            id: wizardData.id,
            name: wizardData.name,
            description: wizardData.description,
            status: 'submitted',
            billing: wizardData.billing,
            useCase: wizardData.useCase,
            logoDataUrl: wizardData.logoDataUrl,
            heroDataUrl: wizardData.heroDataUrl,
            brandColor: wizardData.brandColor,
            website: wizardData.website,
            privacyUrl: wizardData.privacyUrl,
            termsUrl: wizardData.termsUrl,
            supportEmail: wizardData.supportEmail,
            supportPhone: wizardData.supportPhone,
            showPhone: wizardData.showPhone,
            showWebsite: wizardData.showWebsite,
            showEmail: wizardData.showEmail,
            campaignFrequency: wizardData.campaignFrequency,
            monthlyVolume: wizardData.monthlyVolume,
            optInDescription: wizardData.optInDescription,
            optOutDescription: wizardData.optOutDescription,
            useCaseOverview: wizardData.useCaseOverview,
            testNumbers: wizardData.testNumbers,
            companyNumber: wizardData.companyNumber,
            companyWebsite: wizardData.companyWebsite,
            registeredAddress: wizardData.registeredAddress,
            approverName: wizardData.approverName,
            approverJobTitle: wizardData.approverJobTitle,
            approverEmail: wizardData.approverEmail,
            created: wizardData.isEditing ? (allAgents.find(function(a) { return a.id === wizardData.id; }) || {}).created || new Date().toISOString().split('T')[0] : new Date().toISOString().split('T')[0],
            updated: new Date().toISOString().split('T')[0],
            rejectionReason: null
        };
        
        clearDraftFromStorage(wizardData.id);
        
        loadAgentsFromApi();
        wizardModal.hide();
        
        showNotification('success', 'Agent Submitted', 'RCS Agent submitted for review successfully! You will be notified once a decision has been made.');
    }, 500);
}
</script>
@endpush
