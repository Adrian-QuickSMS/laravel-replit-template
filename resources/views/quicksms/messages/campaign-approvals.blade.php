@extends('layouts.quicksms')

@section('title', 'Campaign Approvals')

@push('styles')
<style>
.approval-stats {
    display: flex;
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}
.stat-card {
    background: #fff;
    border-radius: 0.5rem;
    padding: 1rem 1.5rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    flex: 1;
    display: flex;
    align-items: center;
    gap: 1rem;
}
.stat-card .stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
}
.stat-card .stat-icon.pending { background: #fef3c7; color: #d97706; }
.stat-card .stat-icon.approved { background: #dcfce7; color: #16a34a; }
.stat-card .stat-icon.rejected { background: #fee2e2; color: #dc2626; }
.stat-card .stat-value { font-size: 1.5rem; font-weight: 700; color: #374151; }
.stat-card .stat-label { font-size: 0.8rem; color: #6b7280; }

.approval-table {
    background: #fff;
    border-radius: 0.5rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    overflow: hidden;
}
.approval-table th {
    background: #f8f9fa;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    color: #6b7280;
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #e5e7eb;
}
.approval-table td {
    padding: 1rem;
    border-bottom: 1px solid #f3f4f6;
    vertical-align: middle;
}
.approval-table tbody tr:hover {
    background: #faf8ff;
}
.campaign-name {
    font-weight: 600;
    color: #374151;
}
.campaign-meta {
    font-size: 0.75rem;
    color: #9ca3af;
}
.channel-badge {
    font-size: 0.7rem;
    padding: 3px 8px;
    border-radius: 4px;
    font-weight: 500;
}
.channel-badge.sms { background: #dbeafe; color: #1d4ed8; }
.channel-badge.rcs { background: #f3e8ff; color: #7c3aed; }

.action-btn {
    padding: 0.35rem 0.75rem;
    font-size: 0.75rem;
    border-radius: 4px;
    font-weight: 500;
    transition: all 0.15s ease;
}
.btn-approve {
    background: #16a34a;
    color: #fff;
    border: none;
}
.btn-approve:hover { background: #15803d; color: #fff; }
.btn-reject {
    background: transparent;
    color: #dc2626;
    border: 1px solid #dc2626;
}
.btn-reject:hover { background: #dc2626; color: #fff; }
.btn-view {
    background: transparent;
    color: #886cc0;
    border: 1px solid #886cc0;
}
.btn-view:hover { background: #f3e8ff; }

.decision-badge {
    font-size: 0.7rem;
    padding: 3px 8px;
    border-radius: 4px;
    font-weight: 500;
}
.decision-badge.approved { background: #dcfce7; color: #166534; }
.decision-badge.rejected { background: #fee2e2; color: #991b1b; }

.section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1rem;
}
.section-title {
    font-size: 1rem;
    font-weight: 600;
    color: #374151;
    margin: 0;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0" style="background: transparent; padding: 0;">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-muted">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('messages') }}" class="text-muted">Messages</a></li>
                    <li class="breadcrumb-item active">Campaign Approvals</li>
                </ol>
            </nav>
        </div>
    </div>
    
    <div class="approval-stats">
        <div class="stat-card">
            <div class="stat-icon pending"><i class="fas fa-clock"></i></div>
            <div>
                <div class="stat-value">{{ count($pending_approvals) }}</div>
                <div class="stat-label">Pending Approval</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon approved"><i class="fas fa-check"></i></div>
            <div>
                <div class="stat-value">12</div>
                <div class="stat-label">Approved This Week</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon rejected"><i class="fas fa-times"></i></div>
            <div>
                <div class="stat-value">2</div>
                <div class="stat-label">Rejected This Week</div>
            </div>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-body p-0">
            <div class="section-header px-3 pt-3">
                <h5 class="section-title"><i class="fas fa-hourglass-half me-2" style="color: #d97706;"></i>Pending Approvals</h5>
            </div>
            
            @if(count($pending_approvals) > 0)
            <div class="table-responsive">
                <table class="table approval-table mb-0">
                    <thead>
                        <tr>
                            <th>Campaign</th>
                            <th>Sub-Account</th>
                            <th>Created By</th>
                            <th>Channel</th>
                            <th>Recipients</th>
                            <th>Est. Cost</th>
                            <th>Scheduled</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pending_approvals as $approval)
                        <tr>
                            <td>
                                <div class="campaign-name">{{ $approval['name'] }}</div>
                                <div class="campaign-meta">Created {{ \Carbon\Carbon::parse($approval['created_at'])->diffForHumans() }}</div>
                            </td>
                            <td>{{ $approval['sub_account'] }}</td>
                            <td>{{ $approval['created_by'] }}</td>
                            <td><span class="channel-badge {{ strtolower($approval['channel']) }}">{{ $approval['channel'] }}</span></td>
                            <td>{{ number_format($approval['message_volume']) }}</td>
                            <td>£{{ number_format($approval['estimated_cost'], 2) }}</td>
                            <td>{{ \Carbon\Carbon::parse($approval['scheduled_time'])->format('M j, H:i') }}</td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button class="btn action-btn btn-view" data-campaign-id="{{ $approval['id'] }}"><i class="fas fa-eye"></i></button>
                                    <button class="btn action-btn btn-approve" data-campaign-id="{{ $approval['id'] }}"><i class="fas fa-check me-1"></i>Approve</button>
                                    <button class="btn action-btn btn-reject" data-campaign-id="{{ $approval['id'] }}" data-bs-toggle="modal" data-bs-target="#rejectModal"><i class="fas fa-times me-1"></i>Reject</button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-5">
                <i class="fas fa-clipboard-check fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No Pending Approvals</h5>
                <p class="text-muted" style="font-size: 0.85rem;">All campaigns have been reviewed. Check back later.</p>
            </div>
            @endif
        </div>
    </div>
    
    <div class="card">
        <div class="card-body p-0">
            <div class="section-header px-3 pt-3">
                <h5 class="section-title"><i class="fas fa-history me-2" style="color: #6b7280;"></i>Recent Decisions</h5>
            </div>
            
            <div class="table-responsive">
                <table class="table approval-table mb-0">
                    <thead>
                        <tr>
                            <th>Campaign</th>
                            <th>Sub-Account</th>
                            <th>Created By</th>
                            <th>Decision</th>
                            <th>Approved/Rejected By</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recent_decisions as $decision)
                        <tr>
                            <td>
                                <div class="campaign-name">{{ $decision['name'] }}</div>
                            </td>
                            <td>{{ $decision['sub_account'] }}</td>
                            <td>{{ $decision['created_by'] }}</td>
                            <td>
                                <span class="decision-badge {{ $decision['decision'] }}">
                                    <i class="fas fa-{{ $decision['decision'] === 'approved' ? 'check' : 'times' }} me-1"></i>
                                    {{ ucfirst($decision['decision']) }}
                                </span>
                                @if(isset($decision['rejection_reason']))
                                <div class="campaign-meta mt-1">{{ $decision['rejection_reason'] }}</div>
                                @endif
                            </td>
                            <td>{{ $decision['approver'] }}</td>
                            <td>{{ \Carbon\Carbon::parse($decision['decided_at'])->format('M j, H:i') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reject Campaign</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning mb-3" style="font-size: 0.85rem;">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    This campaign will be returned to the creator for revision.
                </div>
                <div class="mb-3">
                    <label class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="rejection-reason" rows="3" placeholder="Please explain why this campaign is being rejected..." required></textarea>
                    <div class="form-text">This will be visible to the campaign creator and logged for audit purposes.</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="btn-confirm-reject">
                    <i class="fas fa-times me-1"></i>Confirm Rejection
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.btn-approve').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var campaignId = this.getAttribute('data-campaign-id');
            if (confirm('Are you sure you want to approve this campaign? It will be scheduled for sending.')) {
                console.log('[AUDIT] Campaign approved:', {
                    action: 'CAMPAIGN_APPROVED',
                    campaignId: campaignId,
                    approver: { userId: 'user-001', userName: 'Sarah Mitchell', role: 'admin' },
                    timestamp: new Date().toISOString()
                });
                alert('Campaign approved successfully!');
                location.reload();
            }
        });
    });
    
    var selectedCampaignId = null;
    document.querySelectorAll('.btn-reject').forEach(function(btn) {
        btn.addEventListener('click', function() {
            selectedCampaignId = this.getAttribute('data-campaign-id');
        });
    });
    
    document.getElementById('btn-confirm-reject').addEventListener('click', function() {
        var reason = document.getElementById('rejection-reason').value.trim();
        if (!reason) {
            alert('Please provide a rejection reason');
            return;
        }
        
        console.log('[AUDIT] Campaign rejected:', {
            action: 'CAMPAIGN_REJECTED',
            campaignId: selectedCampaignId,
            reason: reason,
            rejectedBy: { userId: 'user-001', userName: 'Sarah Mitchell', role: 'admin' },
            timestamp: new Date().toISOString()
        });
        
        bootstrap.Modal.getInstance(document.getElementById('rejectModal')).hide();
        alert('Campaign rejected. The creator has been notified.');
        location.reload();
    });
});
</script>
@endpush
