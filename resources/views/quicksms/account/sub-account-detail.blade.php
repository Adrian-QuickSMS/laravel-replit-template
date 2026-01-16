@extends('layouts.quicksms')

@section('title', $sub_account['name'])

@push('styles')
<style>
.page-header {
    margin-bottom: 1.5rem;
}
.page-header h1 {
    font-size: 1.5rem;
    font-weight: 600;
    color: #374151;
    margin: 0;
}

.section-card {
    background: #fff;
    border-radius: 0.5rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    margin-bottom: 1.5rem;
}
.section-header {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #f3f4f6;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.section-title {
    font-size: 1rem;
    font-weight: 600;
    color: #374151;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.section-title i {
    color: #886cc0;
}
.section-body {
    padding: 1.25rem;
}

.status-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.375rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.8rem;
    font-weight: 500;
}
.status-pill.live {
    background: #dcfce7;
    color: #166534;
}
.status-pill.suspended {
    background: #fef3c7;
    color: #92400e;
}
.status-pill.archived {
    background: #f3f4f6;
    color: #6b7280;
}
.status-pill i {
    font-size: 0.65rem;
}

.status-display {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem;
    background: #f9fafb;
    border-radius: 0.5rem;
    margin-bottom: 1rem;
}
.status-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}
.status-label {
    font-size: 0.8rem;
    color: #6b7280;
    margin-bottom: 0.25rem;
}
.status-actions {
    display: flex;
    gap: 0.5rem;
}

.btn-action {
    padding: 0.4rem 0.875rem;
    font-size: 0.8rem;
    border-radius: 0.375rem;
    font-weight: 500;
    transition: all 0.15s ease;
}
.btn-suspend {
    background: transparent;
    color: #886cc0;
    border: 1px solid #886cc0;
}
.btn-suspend:hover {
    background: #f3e8ff;
    color: #6b21a8;
}
.btn-reactivate {
    background: #886cc0;
    color: #fff;
    border: 1px solid #886cc0;
}
.btn-reactivate:hover {
    background: #7c5fb3;
    color: #fff;
}
.btn-archive {
    background: transparent;
    color: #6b7280;
    border: 1px solid #d1d5db;
}
.btn-archive:hover {
    background: #f3f4f6;
    color: #374151;
}

.status-note {
    font-size: 0.8rem;
    color: #6b7280;
    padding: 0.75rem 1rem;
    background: #f8f9fa;
    border-radius: 0.375rem;
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
}
.status-note i {
    color: #9ca3af;
    margin-top: 0.125rem;
}

.limits-form .form-label {
    font-size: 0.8rem;
    font-weight: 500;
    color: #374151;
    margin-bottom: 0.375rem;
}
.limits-form .form-control,
.limits-form .form-select {
    font-size: 0.85rem;
}
.limits-form .input-group-text {
    font-size: 0.8rem;
    background: #f9fafb;
}
.limits-form .form-text {
    font-size: 0.75rem;
}

.enforcement-option {
    padding: 0.75rem 1rem;
    border: 1px solid #e5e7eb;
    border-radius: 0.375rem;
    cursor: pointer;
    transition: all 0.15s ease;
    height: 100%;
    display: flex;
    flex-direction: column;
}
.enforcement-option:hover {
    border-color: #886cc0;
    background: #faf8ff;
}
.enforcement-option.selected {
    border-color: #886cc0;
    background: #f3e8ff;
}
.enforcement-option .option-title {
    font-size: 0.85rem;
    font-weight: 500;
    color: #374151;
}
.enforcement-option .option-desc {
    font-size: 0.75rem;
    color: #6b7280;
    margin-top: 0.25rem;
}

.hard-stop-toggle {
    padding: 0.75rem 1rem;
    background: #fef2f2;
    border: 1px solid #fecaca;
    border-radius: 0.375rem;
}
.hard-stop-toggle.enabled {
    background: #fee2e2;
    border-color: #f87171;
}

.approval-required-banner {
    padding: 0.75rem 1rem;
    background: #f3e8ff;
    border: 1px solid #ddd6fe;
    border-radius: 0.375rem;
    font-size: 0.8rem;
    color: #6b21a8;
    display: none;
}
.approval-required-banner.show {
    display: flex;
}

.current-values {
    background: #f9fafb;
    border-radius: 0.375rem;
    padding: 0.75rem 1rem;
    margin-bottom: 1rem;
}
.current-values .label {
    font-size: 0.7rem;
    text-transform: uppercase;
    color: #6b7280;
    letter-spacing: 0.025em;
}
.current-values .value {
    font-size: 0.9rem;
    font-weight: 600;
    color: #374151;
}

.usage-metric {
    margin-bottom: 1.5rem;
}
.usage-metric:last-child {
    margin-bottom: 0;
}
.usage-metric .metric-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}
.usage-metric .metric-label {
    font-size: 0.85rem;
    font-weight: 500;
    color: #374151;
}
.usage-metric .metric-value {
    font-size: 0.85rem;
    font-weight: 600;
    color: #374151;
}
.usage-metric .progress {
    height: 10px;
    border-radius: 5px;
    background: #f3f4f6;
}
.usage-metric .progress-bar {
    border-radius: 5px;
    transition: width 0.5s ease;
}
.usage-metric .progress-bar.normal { background: #886cc0; }
.usage-metric .progress-bar.warning { background: #f59e0b; }
.usage-metric .progress-bar.critical { background: #ef4444; }

.enforcement-state {
    padding: 1rem 1.25rem;
    border-radius: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}
.enforcement-state.normal {
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
}
.enforcement-state.warning {
    background: #fefce8;
    border: 1px solid #fef08a;
}
.enforcement-state.blocked {
    background: #fef2f2;
    border: 1px solid #fecaca;
}
.enforcement-state.approval-required {
    background: #f3e8ff;
    border: 1px solid #e9d5ff;
}
.enforcement-state .state-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
}
.enforcement-state.normal .state-icon { background: #dcfce7; color: #16a34a; }
.enforcement-state.warning .state-icon { background: #fef3c7; color: #d97706; }
.enforcement-state.blocked .state-icon { background: #fee2e2; color: #dc2626; }
.enforcement-state.approval-required .state-icon { background: #f3e8ff; color: #7c3aed; }
.enforcement-state .state-title {
    font-size: 0.9rem;
    font-weight: 600;
}
.enforcement-state.normal .state-title { color: #166534; }
.enforcement-state.warning .state-title { color: #92400e; }
.enforcement-state.blocked .state-title { color: #991b1b; }
.enforcement-state.approval-required .state-title { color: #6b21a8; }
.enforcement-state .state-desc {
    font-size: 0.8rem;
    color: #6b7280;
    margin-top: 0.125rem;
}

.live-indicator {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    font-size: 0.7rem;
    color: #16a34a;
    font-weight: 500;
}
.live-indicator .pulse {
    width: 8px;
    height: 8px;
    background: #16a34a;
    border-radius: 50%;
    animation: pulse 2s infinite;
}
@keyframes pulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.5; transform: scale(0.8); }
}

.assets-tabs .nav-tabs {
    border-bottom: 1px solid #e5e7eb;
}
.assets-tabs .nav-link {
    font-size: 0.8rem;
    color: #6b7280;
    border: none;
    padding: 0.625rem 1rem;
    border-bottom: 2px solid transparent;
    margin-bottom: -1px;
}
.assets-tabs .nav-link:hover {
    color: #886cc0;
    border-color: transparent;
}
.assets-tabs .nav-link.active {
    color: #886cc0;
    font-weight: 500;
    border-bottom-color: #886cc0;
    background: transparent;
}
.assets-tabs .nav-link .badge {
    font-size: 0.65rem;
    padding: 0.2rem 0.4rem;
    margin-left: 0.375rem;
    background: #f3e8ff;
    color: #886cc0;
}

.asset-table {
    font-size: 0.85rem;
    margin: 0;
}
.asset-table thead th {
    background: #f9fafb;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    color: #6b7280;
    padding: 0.625rem 1rem;
    border-bottom: 1px solid #e5e7eb;
}
.asset-table tbody td {
    padding: 0.75rem 1rem;
    vertical-align: middle;
    border-bottom: 1px solid #f3f4f6;
}
.asset-table tbody tr:hover {
    background: #faf8ff;
}
.asset-table .asset-name {
    font-weight: 500;
    color: #374151;
}
.asset-table .asset-id {
    font-size: 0.75rem;
    color: #9ca3af;
    font-family: monospace;
}
.asset-table .type-badge {
    font-size: 0.7rem;
    padding: 0.2rem 0.5rem;
    border-radius: 4px;
    background: #f3f4f6;
    color: #6b7280;
}
.asset-table .status-badge {
    font-size: 0.7rem;
    padding: 0.2rem 0.5rem;
    border-radius: 4px;
}
.asset-table .status-badge.active { background: #dcfce7; color: #166534; }
.asset-table .status-badge.pending { background: #fef3c7; color: #92400e; }
.asset-table .status-badge.inactive { background: #f3f4f6; color: #6b7280; }

.btn-manage {
    font-size: 0.75rem;
    padding: 0.25rem 0.625rem;
    color: #886cc0;
    border: 1px solid #886cc0;
    background: transparent;
    border-radius: 4px;
}
.btn-manage:hover {
    background: #f3e8ff;
    color: #7c3aed;
}

.empty-assets {
    text-align: center;
    padding: 2rem;
    color: #9ca3af;
}
.empty-assets i {
    font-size: 2rem;
    margin-bottom: 0.5rem;
    opacity: 0.5;
}
.empty-assets p {
    font-size: 0.85rem;
    margin: 0;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <div class="card mb-0">
                <div class="card-body py-2 px-3">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0" style="background: transparent; padding: 0;">
                            <li class="breadcrumb-item"><a href="{{ route('account') }}" style="color: #886cc0;">Account</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('account.sub-accounts') }}" style="color: #886cc0;">Sub-Accounts</a></li>
                            <li class="breadcrumb-item active" style="color: #6b7280;">{{ $sub_account['name'] }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    
    <div class="page-header">
        <h1>{{ $sub_account['name'] }}</h1>
    </div>
    
    <div class="section-card" id="status-section">
        <div class="section-header">
            <h2 class="section-title">
                <i class="fas fa-circle-check"></i>
                Sub-Account Status
            </h2>
        </div>
        <div class="section-body">
            <div class="status-display">
                <div class="status-info">
                    <div>
                        <div class="status-label">Current Status</div>
                        <span class="status-pill {{ $sub_account['status'] }}" id="current-status-pill">
                            @if($sub_account['status'] === 'live')
                                <i class="fas fa-circle"></i> Live
                            @elseif($sub_account['status'] === 'suspended')
                                <i class="fas fa-pause-circle"></i> Suspended
                            @else
                                <i class="fas fa-archive"></i> Archived
                            @endif
                        </span>
                    </div>
                </div>
                <div class="status-actions" id="status-actions">
                    @if($sub_account['status'] === 'live')
                        <button type="button" class="btn btn-action btn-suspend" data-bs-toggle="modal" data-bs-target="#suspendModal">
                            <i class="fas fa-pause me-1"></i>Suspend
                        </button>
                    @elseif($sub_account['status'] === 'suspended')
                        <button type="button" class="btn btn-action btn-reactivate" data-bs-toggle="modal" data-bs-target="#reactivateModal">
                            <i class="fas fa-play me-1"></i>Reactivate
                        </button>
                        <button type="button" class="btn btn-action btn-archive" data-bs-toggle="modal" data-bs-target="#archiveModal">
                            <i class="fas fa-archive me-1"></i>Archive
                        </button>
                    @else
                        <span class="text-muted" style="font-size: 0.8rem; font-style: italic;">No actions available for archived accounts</span>
                    @endif
                </div>
            </div>
            
            <div class="status-note">
                <i class="fas fa-info-circle"></i>
                <div>
                    @if($sub_account['status'] === 'live')
                        This sub-account is active. All users can send messages and access features according to their permissions.
                    @elseif($sub_account['status'] === 'suspended')
                        This sub-account is suspended. Users cannot send messages until it is reactivated. You may archive this account if it is no longer needed.
                    @else
                        This sub-account has been archived and cannot be modified. Historical data is preserved for reporting purposes.
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <div class="section-card" id="limits-section">
        <div class="section-header">
            <h2 class="section-title">
                <i class="fas fa-sliders"></i>
                Limits & Enforcement
            </h2>
        </div>
        <div class="section-body">
            <div class="current-values">
                <div class="row">
                    <div class="col-md-3">
                        <div class="label">Current Spend</div>
                        <div class="value">£{{ number_format($sub_account['monthly_spend'], 2) }}</div>
                    </div>
                    <div class="col-md-3">
                        <div class="label">Current Messages</div>
                        <div class="value">{{ number_format($sub_account['monthly_messages']) }}</div>
                    </div>
                    <div class="col-md-3">
                        <div class="label">Spend Cap</div>
                        <div class="value">£{{ number_format($sub_account['limits']['spend_cap'], 2) }}</div>
                    </div>
                    <div class="col-md-3">
                        <div class="label">Message Cap</div>
                        <div class="value">{{ number_format($sub_account['limits']['message_cap']) }}</div>
                    </div>
                </div>
            </div>
            
            <div class="approval-required-banner mb-3" id="approval-banner">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <div>
                    <strong>Approval Required:</strong> Increasing limits requires Main Account Admin approval. Your request will be submitted for review.
                </div>
            </div>
            
            <form class="limits-form" id="limits-form">
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Monthly Spend Cap</label>
                        <div class="input-group">
                            <span class="input-group-text">£</span>
                            <input type="number" class="form-control" id="spend-cap" 
                                   value="{{ $sub_account['limits']['spend_cap'] }}" 
                                   data-original="{{ $sub_account['limits']['spend_cap'] }}"
                                   min="0" step="0.01" placeholder="No limit">
                        </div>
                        <div class="form-text">Maximum monthly spend allowed for this sub-account</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Monthly Message Cap</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="message-cap" 
                                   value="{{ $sub_account['limits']['message_cap'] }}"
                                   data-original="{{ $sub_account['limits']['message_cap'] }}"
                                   min="0" placeholder="No limit">
                            <span class="input-group-text">parts</span>
                        </div>
                        <div class="form-text">Maximum message parts allowed per month</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Daily Send Limit <span class="text-muted">(optional)</span></label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="daily-limit" 
                                   value="{{ $sub_account['limits']['daily_limit'] }}"
                                   data-original="{{ $sub_account['limits']['daily_limit'] }}"
                                   min="0" placeholder="No limit">
                            <span class="input-group-text">msgs/day</span>
                        </div>
                        <div class="form-text">Leave empty for unlimited daily sends</div>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="form-label mb-2">Enforcement Type</label>
                    <div class="row g-2">
                        <div class="col-md-4">
                            <div class="enforcement-option {{ $sub_account['limits']['enforcement_type'] === 'warn' ? 'selected' : '' }}" data-value="warn">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-bell me-2" style="color: #d97706;"></i>
                                    <div class="option-title">Warn Only</div>
                                </div>
                                <div class="option-desc">Alert admins when limits approach, but allow sends to continue</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="enforcement-option {{ $sub_account['limits']['enforcement_type'] === 'block' ? 'selected' : '' }}" data-value="block">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-ban me-2" style="color: #dc2626;"></i>
                                    <div class="option-title">Block Sends</div>
                                </div>
                                <div class="option-desc">Prevent all sends when limits are reached until next period</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="enforcement-option {{ $sub_account['limits']['enforcement_type'] === 'approval' ? 'selected' : '' }}" data-value="approval">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-clipboard-check me-2" style="color: #886cc0;"></i>
                                    <div class="option-title">Require Approval</div>
                                </div>
                                <div class="option-desc">Queue sends for admin approval when limits are reached</div>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" id="enforcement-type" value="{{ $sub_account['limits']['enforcement_type'] }}" data-original="{{ $sub_account['limits']['enforcement_type'] }}">
                </div>
                
                <div class="hard-stop-toggle {{ $sub_account['limits']['hard_stop'] ? 'enabled' : '' }}" id="hard-stop-container">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="hard-stop" {{ $sub_account['limits']['hard_stop'] ? 'checked' : '' }} data-original="{{ $sub_account['limits']['hard_stop'] ? '1' : '0' }}" style="border-color: #dc2626;">
                        <label class="form-check-label" for="hard-stop">
                            <span style="font-weight: 500; color: #991b1b;">Enable Hard Stop</span>
                            <div style="font-size: 0.75rem; color: #6b7280;">When enabled, enforcement cannot be overridden by any user. Use with caution.</div>
                        </label>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                    <button type="button" class="btn btn-link text-muted" id="btn-reset-limits" style="font-size: 0.85rem;">
                        <i class="fas fa-undo me-1"></i>Reset to Original
                    </button>
                    <button type="button" class="btn" id="btn-save-limits" style="background: #886cc0; color: white;">
                        <i class="fas fa-save me-1"></i>Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    @php
        $spendPercent = $sub_account['limits']['spend_cap'] > 0 
            ? min(100, ($sub_account['monthly_spend'] / $sub_account['limits']['spend_cap']) * 100) 
            : 0;
        $msgPercent = $sub_account['limits']['message_cap'] > 0 
            ? min(100, ($sub_account['monthly_messages'] / $sub_account['limits']['message_cap']) * 100) 
            : 0;
        
        $spendClass = $spendPercent >= 90 ? 'critical' : ($spendPercent >= 75 ? 'warning' : 'normal');
        $msgClass = $msgPercent >= 90 ? 'critical' : ($msgPercent >= 75 ? 'warning' : 'normal');
        
        $enforcementState = 'normal';
        if ($spendPercent >= 100 || $msgPercent >= 100) {
            $enforcementState = $sub_account['limits']['enforcement_type'] === 'approval' ? 'approval-required' : 'blocked';
        } elseif ($spendPercent >= 75 || $msgPercent >= 75) {
            $enforcementState = 'warning';
        }
    @endphp
    
    <div class="section-card" id="usage-section">
        <div class="section-header">
            <h2 class="section-title">
                <i class="fas fa-chart-line"></i>
                Live Usage & Telemetry
            </h2>
            <span class="live-indicator">
                <span class="pulse"></span>
                Live
            </span>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="usage-metric" id="spend-metric">
                        <div class="metric-header">
                            <span class="metric-label"><i class="fas fa-pound-sign me-1"></i>Spend vs Cap</span>
                            <span class="metric-value" id="spend-value">£{{ number_format($sub_account['monthly_spend'], 2) }} / £{{ number_format($sub_account['limits']['spend_cap'], 2) }}</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar {{ $spendClass }}" id="spend-bar" role="progressbar" style="width: {{ $spendPercent }}%"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="usage-metric" id="msgs-metric">
                        <div class="metric-header">
                            <span class="metric-label"><i class="fas fa-envelope me-1"></i>Messages vs Limit</span>
                            <span class="metric-value" id="msgs-value">{{ number_format($sub_account['monthly_messages']) }} / {{ number_format($sub_account['limits']['message_cap']) }}</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar {{ $msgClass }}" id="msgs-bar" role="progressbar" style="width: {{ $msgPercent }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="enforcement-state {{ $enforcementState }}" id="enforcement-state-display">
                <div class="state-icon">
                    @if($enforcementState === 'normal')
                        <i class="fas fa-shield-check"></i>
                    @elseif($enforcementState === 'warning')
                        <i class="fas fa-exclamation-triangle"></i>
                    @elseif($enforcementState === 'blocked')
                        <i class="fas fa-ban"></i>
                    @else
                        <i class="fas fa-clock"></i>
                    @endif
                </div>
                <div>
                    <div class="state-title" id="enforcement-state-title">
                        @if($enforcementState === 'normal')
                            Normal
                        @elseif($enforcementState === 'warning')
                            Warning
                        @elseif($enforcementState === 'blocked')
                            Blocked
                        @else
                            Approval Required
                        @endif
                    </div>
                    <div class="state-desc" id="enforcement-state-desc">
                        @if($enforcementState === 'normal')
                            All systems operational. Usage is within acceptable limits.
                        @elseif($enforcementState === 'warning')
                            Approaching limit threshold. Consider reviewing usage patterns.
                        @elseif($enforcementState === 'blocked')
                            Limit exceeded. Sends are blocked until next billing period or limit increase.
                        @else
                            Limit exceeded. New campaigns require approval before sending.
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="section-card" id="assets-section">
        <div class="section-header">
            <h2 class="section-title">
                <i class="fas fa-cube"></i>
                Assigned Assets
            </h2>
        </div>
        <div class="section-body p-0">
            <div class="assets-tabs">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-senderids">
                            SMS SenderIDs <span class="badge">3</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-numbers">
                            Numbers <span class="badge">2</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-rcs">
                            RCS Agents <span class="badge">1</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-templates">
                            Templates <span class="badge">5</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-email">
                            Email-to-SMS <span class="badge">1</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-api">
                            API Connections <span class="badge">2</span>
                        </button>
                    </li>
                </ul>
                
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="tab-senderids">
                        <table class="table asset-table">
                            <thead>
                                <tr>
                                    <th>Name / ID</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div class="asset-name">QuickSMS</div>
                                        <div class="asset-id">SID-001</div>
                                    </td>
                                    <td><span class="type-badge">Alphanumeric</span></td>
                                    <td><span class="status-badge active">Active</span></td>
                                    <td><a href="{{ route('management.sms-sender-id') }}" class="btn btn-manage">Manage</a></td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="asset-name">Marketing</div>
                                        <div class="asset-id">SID-002</div>
                                    </td>
                                    <td><span class="type-badge">Alphanumeric</span></td>
                                    <td><span class="status-badge active">Active</span></td>
                                    <td><a href="{{ route('management.sms-sender-id') }}" class="btn btn-manage">Manage</a></td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="asset-name">Alerts</div>
                                        <div class="asset-id">SID-003</div>
                                    </td>
                                    <td><span class="type-badge">Alphanumeric</span></td>
                                    <td><span class="status-badge pending">Pending</span></td>
                                    <td><a href="{{ route('management.sms-sender-id') }}" class="btn btn-manage">Manage</a></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="tab-pane fade" id="tab-numbers">
                        <table class="table asset-table">
                            <thead>
                                <tr>
                                    <th>Number</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div class="asset-name">+44 7700 900123</div>
                                        <div class="asset-id">NUM-VMN-001</div>
                                    </td>
                                    <td><span class="type-badge">VMN</span></td>
                                    <td><span class="status-badge active">Active</span></td>
                                    <td><a href="{{ route('management.numbers') }}" class="btn btn-manage">Manage</a></td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="asset-name">88020</div>
                                        <div class="asset-id">NUM-SC-001</div>
                                    </td>
                                    <td><span class="type-badge">Shortcode</span></td>
                                    <td><span class="status-badge active">Active</span></td>
                                    <td><a href="{{ route('management.numbers') }}" class="btn btn-manage">Manage</a></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="tab-pane fade" id="tab-rcs">
                        <table class="table asset-table">
                            <thead>
                                <tr>
                                    <th>Agent Name</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div class="asset-name">QuickSMS Brand</div>
                                        <div class="asset-id">RCS-AGT-001</div>
                                    </td>
                                    <td><span class="type-badge">Conversational</span></td>
                                    <td><span class="status-badge active">Verified</span></td>
                                    <td><a href="{{ route('management.rcs-agent') }}" class="btn btn-manage">Manage</a></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="tab-pane fade" id="tab-templates">
                        <table class="table asset-table">
                            <thead>
                                <tr>
                                    <th>Template Name</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div class="asset-name">Sale Announcement</div>
                                        <div class="asset-id">TPL-001</div>
                                    </td>
                                    <td><span class="type-badge">SMS</span></td>
                                    <td><span class="status-badge active">Active</span></td>
                                    <td><a href="{{ route('management.templates') }}" class="btn btn-manage">Manage</a></td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="asset-name">Appointment Reminder</div>
                                        <div class="asset-id">TPL-002</div>
                                    </td>
                                    <td><span class="type-badge">SMS</span></td>
                                    <td><span class="status-badge active">Active</span></td>
                                    <td><a href="{{ route('management.templates') }}" class="btn btn-manage">Manage</a></td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="asset-name">Product Showcase</div>
                                        <div class="asset-id">TPL-003</div>
                                    </td>
                                    <td><span class="type-badge">RCS Rich</span></td>
                                    <td><span class="status-badge active">Active</span></td>
                                    <td><a href="{{ route('management.templates') }}" class="btn btn-manage">Manage</a></td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="asset-name">Order Confirmation</div>
                                        <div class="asset-id">TPL-004</div>
                                    </td>
                                    <td><span class="type-badge">SMS</span></td>
                                    <td><span class="status-badge active">Active</span></td>
                                    <td><a href="{{ route('management.templates') }}" class="btn btn-manage">Manage</a></td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="asset-name">Feedback Request</div>
                                        <div class="asset-id">TPL-005</div>
                                    </td>
                                    <td><span class="type-badge">RCS Basic</span></td>
                                    <td><span class="status-badge pending">Draft</span></td>
                                    <td><a href="{{ route('management.templates') }}" class="btn btn-manage">Manage</a></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="tab-pane fade" id="tab-email">
                        <table class="table asset-table">
                            <thead>
                                <tr>
                                    <th>Email Address</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div class="asset-name">alerts@company.quicksms.io</div>
                                        <div class="asset-id">E2S-001</div>
                                    </td>
                                    <td><span class="type-badge">Standard</span></td>
                                    <td><span class="status-badge active">Active</span></td>
                                    <td><a href="{{ route('management.email-to-sms') }}" class="btn btn-manage">Manage</a></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="tab-pane fade" id="tab-api">
                        <table class="table asset-table">
                            <thead>
                                <tr>
                                    <th>Connection Name</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div class="asset-name">Production API</div>
                                        <div class="asset-id">API-001</div>
                                    </td>
                                    <td><span class="type-badge">REST v2</span></td>
                                    <td><span class="status-badge active">Active</span></td>
                                    <td><a href="{{ route('management.api-connections') }}" class="btn btn-manage">Manage</a></td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="asset-name">Webhook Endpoint</div>
                                        <div class="asset-id">API-002</div>
                                    </td>
                                    <td><span class="type-badge">Webhook</span></td>
                                    <td><span class="status-badge active">Active</span></td>
                                    <td><a href="{{ route('management.api-connections') }}" class="btn btn-manage">Manage</a></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="suspendModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-pause-circle text-warning me-2"></i>Suspend Sub-Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning mb-3" style="font-size: 0.85rem;">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    <strong>Warning:</strong> Suspending this sub-account will immediately prevent all users from sending messages.
                </div>
                <p style="font-size: 0.9rem;">Are you sure you want to suspend <strong>{{ $sub_account['name'] }}</strong>?</p>
                <div class="mb-3">
                    <label class="form-label" style="font-size: 0.85rem;">Reason for suspension <span class="text-muted">(optional)</span></label>
                    <textarea class="form-control" id="suspend-reason" rows="2" placeholder="Enter reason for audit trail..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn" id="btn-confirm-suspend" style="background: #d97706; color: white;">
                    <i class="fas fa-pause me-1"></i>Suspend Sub-Account
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="reactivateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-play-circle me-2" style="color: #886cc0;"></i>Reactivate Sub-Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info mb-3" style="font-size: 0.85rem; background: #f3e8ff; border-color: #e9d5ff; color: #6b21a8;">
                    <i class="fas fa-info-circle me-1"></i>
                    Reactivating will restore all user access and messaging capabilities for this sub-account.
                </div>
                <p style="font-size: 0.9rem;">Are you sure you want to reactivate <strong>{{ $sub_account['name'] }}</strong>?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn" id="btn-confirm-reactivate" style="background: #886cc0; color: white;">
                    <i class="fas fa-play me-1"></i>Reactivate Sub-Account
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="archiveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-archive text-secondary me-2"></i>Archive Sub-Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger mb-3" style="font-size: 0.85rem;">
                    <i class="fas fa-exclamation-circle me-1"></i>
                    <strong>This action is permanent.</strong> Archived sub-accounts cannot be reactivated. All users will lose access permanently.
                </div>
                <p style="font-size: 0.9rem;">Are you sure you want to archive <strong>{{ $sub_account['name'] }}</strong>?</p>
                <div class="mb-3">
                    <label class="form-label" style="font-size: 0.85rem;">Type the sub-account name to confirm</label>
                    <input type="text" class="form-control" id="archive-confirm-name" placeholder="{{ $sub_account['name'] }}">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="btn-confirm-archive" disabled>
                    <i class="fas fa-archive me-1"></i>Archive Permanently
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var subAccountId = '{{ $sub_account['id'] }}';
    var subAccountName = '{{ $sub_account['name'] }}';
    var currentStatus = '{{ $sub_account['status'] }}';
    
    function updateStatusUI(newStatus) {
        var statusPill = document.getElementById('current-status-pill');
        var statusActions = document.getElementById('status-actions');
        var statusNote = document.querySelector('.status-note div');
        
        statusPill.className = 'status-pill ' + newStatus;
        
        if (newStatus === 'live') {
            statusPill.innerHTML = '<i class="fas fa-circle"></i> Live';
            statusActions.innerHTML = '<button type="button" class="btn btn-action btn-suspend" data-bs-toggle="modal" data-bs-target="#suspendModal"><i class="fas fa-pause me-1"></i>Suspend</button>';
            statusNote.textContent = 'This sub-account is active. All users can send messages and access features according to their permissions.';
        } else if (newStatus === 'suspended') {
            statusPill.innerHTML = '<i class="fas fa-pause-circle"></i> Suspended';
            statusActions.innerHTML = '<button type="button" class="btn btn-action btn-reactivate" data-bs-toggle="modal" data-bs-target="#reactivateModal"><i class="fas fa-play me-1"></i>Reactivate</button>' +
                '<button type="button" class="btn btn-action btn-archive" data-bs-toggle="modal" data-bs-target="#archiveModal"><i class="fas fa-archive me-1"></i>Archive</button>';
            statusNote.textContent = 'This sub-account is suspended. Users cannot send messages until it is reactivated. You may archive this account if it is no longer needed.';
        } else {
            statusPill.innerHTML = '<i class="fas fa-archive"></i> Archived';
            statusActions.innerHTML = '<span class="text-muted" style="font-size: 0.8rem; font-style: italic;">No actions available for archived accounts</span>';
            statusNote.textContent = 'This sub-account has been archived and cannot be modified. Historical data is preserved for reporting purposes.';
        }
        
        currentStatus = newStatus;
    }
    
    function logAuditEvent(action, details) {
        console.log('[AUDIT] Sub-account status change:', {
            action: action,
            subAccountId: subAccountId,
            subAccountName: subAccountName,
            previousStatus: currentStatus,
            ...details,
            changedBy: { userId: 'user-001', userName: 'Sarah Mitchell', role: 'admin' },
            timestamp: new Date().toISOString(),
            ipAddress: '192.168.1.100',
            sessionId: 'sess_abc123'
        });
    }
    
    document.getElementById('btn-confirm-suspend').addEventListener('click', function() {
        var reason = document.getElementById('suspend-reason').value.trim();
        
        logAuditEvent('SUB_ACCOUNT_SUSPENDED', {
            newStatus: 'suspended',
            reason: reason || 'No reason provided'
        });
        
        bootstrap.Modal.getInstance(document.getElementById('suspendModal')).hide();
        updateStatusUI('suspended');
        
        var toast = document.createElement('div');
        toast.className = 'alert alert-warning position-fixed';
        toast.style.cssText = 'top: 80px; right: 20px; z-index: 9999; min-width: 300px;';
        toast.innerHTML = '<i class="fas fa-check-circle me-2"></i>Sub-account suspended successfully.';
        document.body.appendChild(toast);
        setTimeout(function() { toast.remove(); }, 3000);
    });
    
    document.getElementById('btn-confirm-reactivate').addEventListener('click', function() {
        logAuditEvent('SUB_ACCOUNT_REACTIVATED', {
            newStatus: 'live'
        });
        
        bootstrap.Modal.getInstance(document.getElementById('reactivateModal')).hide();
        updateStatusUI('live');
        
        var toast = document.createElement('div');
        toast.className = 'alert position-fixed';
        toast.style.cssText = 'top: 80px; right: 20px; z-index: 9999; min-width: 300px; background: #dcfce7; border-color: #bbf7d0; color: #166534;';
        toast.innerHTML = '<i class="fas fa-check-circle me-2"></i>Sub-account reactivated successfully.';
        document.body.appendChild(toast);
        setTimeout(function() { toast.remove(); }, 3000);
    });
    
    document.getElementById('archive-confirm-name').addEventListener('input', function() {
        var confirmBtn = document.getElementById('btn-confirm-archive');
        confirmBtn.disabled = this.value !== subAccountName;
    });
    
    document.getElementById('btn-confirm-archive').addEventListener('click', function() {
        logAuditEvent('SUB_ACCOUNT_ARCHIVED', {
            newStatus: 'archived',
            permanentAction: true
        });
        
        bootstrap.Modal.getInstance(document.getElementById('archiveModal')).hide();
        updateStatusUI('archived');
        
        var toast = document.createElement('div');
        toast.className = 'alert alert-secondary position-fixed';
        toast.style.cssText = 'top: 80px; right: 20px; z-index: 9999; min-width: 300px;';
        toast.innerHTML = '<i class="fas fa-archive me-2"></i>Sub-account archived permanently.';
        document.body.appendChild(toast);
        setTimeout(function() { toast.remove(); }, 3000);
    });
    
    // Limits & Enforcement handlers
    var originalLimits = {
        spendCap: parseFloat(document.getElementById('spend-cap').dataset.original) || 0,
        messageCap: parseInt(document.getElementById('message-cap').dataset.original) || 0,
        dailyLimit: parseInt(document.getElementById('daily-limit').dataset.original) || 0,
        enforcementType: document.getElementById('enforcement-type').dataset.original,
        hardStop: document.getElementById('hard-stop').dataset.original === '1'
    };
    
    function checkForIncreases() {
        var spendCap = parseFloat(document.getElementById('spend-cap').value) || 0;
        var messageCap = parseInt(document.getElementById('message-cap').value) || 0;
        var dailyLimit = parseInt(document.getElementById('daily-limit').value) || 0;
        
        var hasIncrease = spendCap > originalLimits.spendCap || 
                          messageCap > originalLimits.messageCap || 
                          dailyLimit > originalLimits.dailyLimit;
        
        var banner = document.getElementById('approval-banner');
        if (hasIncrease) {
            banner.classList.add('show');
        } else {
            banner.classList.remove('show');
        }
        
        return hasIncrease;
    }
    
    document.getElementById('spend-cap').addEventListener('input', checkForIncreases);
    document.getElementById('message-cap').addEventListener('input', checkForIncreases);
    document.getElementById('daily-limit').addEventListener('input', checkForIncreases);
    
    document.querySelectorAll('.enforcement-option').forEach(function(option) {
        option.addEventListener('click', function() {
            document.querySelectorAll('.enforcement-option').forEach(function(o) {
                o.classList.remove('selected');
            });
            this.classList.add('selected');
            document.getElementById('enforcement-type').value = this.dataset.value;
        });
    });
    
    document.getElementById('hard-stop').addEventListener('change', function() {
        var container = document.getElementById('hard-stop-container');
        if (this.checked) {
            container.classList.add('enabled');
        } else {
            container.classList.remove('enabled');
        }
    });
    
    document.getElementById('btn-reset-limits').addEventListener('click', function() {
        document.getElementById('spend-cap').value = originalLimits.spendCap;
        document.getElementById('message-cap').value = originalLimits.messageCap;
        document.getElementById('daily-limit').value = originalLimits.dailyLimit;
        document.getElementById('enforcement-type').value = originalLimits.enforcementType;
        document.getElementById('hard-stop').checked = originalLimits.hardStop;
        
        document.querySelectorAll('.enforcement-option').forEach(function(o) {
            o.classList.remove('selected');
            if (o.dataset.value === originalLimits.enforcementType) {
                o.classList.add('selected');
            }
        });
        
        var container = document.getElementById('hard-stop-container');
        if (originalLimits.hardStop) {
            container.classList.add('enabled');
        } else {
            container.classList.remove('enabled');
        }
        
        checkForIncreases();
    });
    
    document.getElementById('btn-save-limits').addEventListener('click', function() {
        var newLimits = {
            spendCap: parseFloat(document.getElementById('spend-cap').value) || 0,
            messageCap: parseInt(document.getElementById('message-cap').value) || 0,
            dailyLimit: parseInt(document.getElementById('daily-limit').value) || 0,
            enforcementType: document.getElementById('enforcement-type').value,
            hardStop: document.getElementById('hard-stop').checked
        };
        
        var requiresApproval = checkForIncreases();
        
        console.log('[AUDIT] Sub-account limits changed:', {
            action: requiresApproval ? 'LIMITS_INCREASE_REQUESTED' : 'LIMITS_UPDATED',
            subAccountId: subAccountId,
            subAccountName: subAccountName,
            previousLimits: originalLimits,
            newLimits: newLimits,
            requiresApproval: requiresApproval,
            changedBy: { userId: 'user-001', userName: 'Sarah Mitchell', role: 'admin' },
            timestamp: new Date().toISOString(),
            ipAddress: '192.168.1.100',
            sessionId: 'sess_abc123'
        });
        
        if (requiresApproval) {
            var toast = document.createElement('div');
            toast.className = 'alert position-fixed';
            toast.style.cssText = 'top: 80px; right: 20px; z-index: 9999; min-width: 350px; background: #fef3c7; border-color: #fcd34d; color: #92400e;';
            toast.innerHTML = '<i class="fas fa-clock me-2"></i>Limit increase request submitted for approval.';
            document.body.appendChild(toast);
            setTimeout(function() { toast.remove(); }, 4000);
        } else {
            originalLimits = JSON.parse(JSON.stringify(newLimits));
            
            var toast = document.createElement('div');
            toast.className = 'alert position-fixed';
            toast.style.cssText = 'top: 80px; right: 20px; z-index: 9999; min-width: 300px; background: #dcfce7; border-color: #bbf7d0; color: #166534;';
            toast.innerHTML = '<i class="fas fa-check-circle me-2"></i>Limits saved successfully.';
            document.body.appendChild(toast);
            setTimeout(function() { toast.remove(); }, 3000);
        }
        
        document.getElementById('approval-banner').classList.remove('show');
    });
    
    // Live Usage & Telemetry - Real-time simulation
    var usageData = {
        spend: {{ $sub_account['monthly_spend'] }},
        messages: {{ $sub_account['monthly_messages'] }},
        spendCap: {{ $sub_account['limits']['spend_cap'] }},
        messageCap: {{ $sub_account['limits']['message_cap'] }},
        enforcementType: '{{ $sub_account['limits']['enforcement_type'] }}'
    };
    
    function updateUsageDisplay() {
        var spendPercent = usageData.spendCap > 0 ? Math.min(100, (usageData.spend / usageData.spendCap) * 100) : 0;
        var msgPercent = usageData.messageCap > 0 ? Math.min(100, (usageData.messages / usageData.messageCap) * 100) : 0;
        
        document.getElementById('spend-value').textContent = '£' + usageData.spend.toLocaleString('en-GB', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' / £' + usageData.spendCap.toLocaleString('en-GB', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('msgs-value').textContent = usageData.messages.toLocaleString() + ' / ' + usageData.messageCap.toLocaleString();
        
        var spendBar = document.getElementById('spend-bar');
        var msgsBar = document.getElementById('msgs-bar');
        
        spendBar.style.width = spendPercent + '%';
        msgsBar.style.width = msgPercent + '%';
        
        spendBar.className = 'progress-bar ' + (spendPercent >= 90 ? 'critical' : (spendPercent >= 75 ? 'warning' : 'normal'));
        msgsBar.className = 'progress-bar ' + (msgPercent >= 90 ? 'critical' : (msgPercent >= 75 ? 'warning' : 'normal'));
        
        updateEnforcementState(spendPercent, msgPercent);
    }
    
    function updateEnforcementState(spendPercent, msgPercent) {
        var stateDisplay = document.getElementById('enforcement-state-display');
        var stateTitle = document.getElementById('enforcement-state-title');
        var stateDesc = document.getElementById('enforcement-state-desc');
        var stateIcon = stateDisplay.querySelector('.state-icon i');
        
        var state = 'normal';
        if (spendPercent >= 100 || msgPercent >= 100) {
            state = usageData.enforcementType === 'approval' ? 'approval-required' : 'blocked';
        } else if (spendPercent >= 75 || msgPercent >= 75) {
            state = 'warning';
        }
        
        stateDisplay.className = 'enforcement-state ' + state;
        
        var states = {
            'normal': { title: 'Normal', desc: 'All systems operational. Usage is within acceptable limits.', icon: 'fa-shield-check' },
            'warning': { title: 'Warning', desc: 'Approaching limit threshold. Consider reviewing usage patterns.', icon: 'fa-exclamation-triangle' },
            'blocked': { title: 'Blocked', desc: 'Limit exceeded. Sends are blocked until next billing period or limit increase.', icon: 'fa-ban' },
            'approval-required': { title: 'Approval Required', desc: 'Limit exceeded. New campaigns require approval before sending.', icon: 'fa-clock' }
        };
        
        stateTitle.textContent = states[state].title;
        stateDesc.textContent = states[state].desc;
        stateIcon.className = 'fas ' + states[state].icon;
    }
    
    // Simulate real-time updates every 10 seconds
    setInterval(function() {
        if (usageData.spend < usageData.spendCap * 0.95) {
            usageData.spend += Math.random() * 5;
            usageData.messages += Math.floor(Math.random() * 50);
            updateUsageDisplay();
        }
    }, 10000);
});
</script>
@endpush
