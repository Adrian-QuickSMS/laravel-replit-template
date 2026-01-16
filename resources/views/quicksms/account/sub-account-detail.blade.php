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
    color: #d97706;
    border: 1px solid #d97706;
}
.btn-suspend:hover {
    background: #fef3c7;
    color: #92400e;
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
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0" style="background: transparent; padding: 0;">
                    <li class="breadcrumb-item"><a href="{{ route('account') }}" class="text-muted">Account</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('account.sub-accounts') }}" class="text-muted">Sub-Accounts</a></li>
                    <li class="breadcrumb-item active">{{ $sub_account['name'] }}</li>
                </ol>
            </nav>
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
});
</script>
@endpush
