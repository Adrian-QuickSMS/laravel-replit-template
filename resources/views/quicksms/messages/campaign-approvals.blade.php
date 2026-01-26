@extends('layouts.quicksms')

@section('title', 'Campaign Approvals')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/rcs-preview.css') }}">
<style>
.card {
    border-radius: 0.75rem !important;
    border: 1px solid #e9ecef !important;
    box-shadow: none !important;
}
.container-fluid > .card {
    border: 1px solid #e9ecef !important;
    border-radius: 0.75rem !important;
}
.table-container {
    background: #fff;
    border-radius: 0.75rem;
    border: 1px solid #e9ecef;
    overflow: hidden;
}
.approval-stats {
    display: flex;
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}
.stat-card {
    background: #fff;
    border-radius: 0.75rem;
    border: 1px solid #e9ecef;
    padding: 1rem 1.5rem;
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
.stat-card .stat-icon.pending { background: #f3e8ff; color: #886cc0; }
.stat-card .stat-icon.approved { background: #dcfce7; color: #16a34a; }
.stat-card .stat-icon.rejected { background: #fee2e2; color: #dc2626; }
.stat-card .stat-value { font-size: 1.5rem; font-weight: 700; color: #374151; }
.stat-card .stat-label { font-size: 0.8rem; color: #6b7280; }

.table thead th {
    background: #f8f9fa !important;
    border-bottom: 1px solid #e9ecef !important;
    padding: 0.75rem 0.5rem !important;
    font-weight: 600 !important;
    font-size: 0.8rem !important;
    color: #495057 !important;
    text-transform: none !important;
    letter-spacing: normal !important;
}
.table tbody td {
    padding: 0.75rem 0.5rem !important;
    vertical-align: middle;
    border-bottom: 1px solid #f1f3f5 !important;
    font-size: 0.85rem;
    color: #495057;
}
.table tbody tr:last-child td {
    border-bottom: none !important;
}
.table tbody tr:hover td {
    background-color: #f8f9fa !important;
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
    background: #dcfce7;
    color: #166534;
    border: none;
}
.btn-approve:hover { background: #bbf7d0; color: #166534; }
.btn-reject {
    background: #fee2e2;
    color: #991b1b;
    border: none;
}
.btn-reject:hover { background: #fecaca; color: #991b1b; }
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

.status-badge {
    font-size: 0.7rem;
    padding: 4px 10px;
    border-radius: 9999px;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
}
.status-badge.pending {
    background: #f3e8ff;
    color: #6b21a8;
}
.status-badge.pending .dot {
    width: 6px;
    height: 6px;
    background: #886cc0;
    border-radius: 50%;
    animation: pulse 2s infinite;
}
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

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

.btn-export {
    padding: 0.35rem 0.75rem;
    font-size: 0.75rem;
    background: #886cc0;
    color: white;
    border: none;
    border-radius: 4px;
    font-weight: 500;
}
.btn-export:hover {
    background: #7c3aed;
    color: white;
}

.audit-trail {
    background: #f9fafb;
    border-radius: 8px;
    padding: 1rem;
    margin-top: 1rem;
}
.audit-trail-title {
    font-size: 0.8rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.75rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.audit-trail-title i {
    color: #886cc0;
}
.audit-entry {
    display: flex;
    gap: 0.75rem;
    padding: 0.75rem 0;
    border-bottom: 1px solid #e5e7eb;
}
.audit-entry:last-child {
    border-bottom: none;
    padding-bottom: 0;
}
.audit-entry:first-child {
    padding-top: 0;
}
.audit-icon {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    flex-shrink: 0;
}
.audit-icon.approved { background: #dcfce7; color: #16a34a; }
.audit-icon.rejected { background: #fee2e2; color: #dc2626; }
.audit-icon.created { background: #dbeafe; color: #2563eb; }
.audit-icon.submitted { background: #f3e8ff; color: #7c3aed; }
.audit-content {
    flex: 1;
}
.audit-action {
    font-size: 0.85rem;
    font-weight: 500;
    color: #374151;
}
.audit-meta {
    font-size: 0.75rem;
    color: #9ca3af;
    margin-top: 0.125rem;
}
.audit-reason {
    font-size: 0.8rem;
    color: #6b7280;
    background: white;
    padding: 0.5rem;
    border-radius: 4px;
    margin-top: 0.5rem;
    border-left: 3px solid #dc2626;
}

.campaign-detail-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
    margin-bottom: 1rem;
}
.detail-item {
    padding: 0.75rem;
    background: #f9fafb;
    border-radius: 6px;
}
.detail-label {
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
    color: #9ca3af;
    margin-bottom: 0.25rem;
}
.detail-value {
    font-size: 0.9rem;
    color: #374151;
    font-weight: 500;
}

.message-preview-section {
    margin-top: 1rem;
}
.message-preview-title {
    font-size: 0.8rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.75rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.message-preview-title i {
    color: #886cc0;
}
#campaign-preview-container {
    transform: scale(0.75);
    transform-origin: top center;
    margin-bottom: -100px;
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
    
    <div class="table-container mb-4">
        <div class="section-header px-3 pt-3">
            <h5 class="section-title"><i class="fas fa-hourglass-half me-2" style="color: #886cc0;"></i>Pending Approvals</h5>
        </div>
        
        @if(count($pending_approvals) > 0)
        <div class="table-responsive">
            <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Campaign</th>
                            <th>Sub-Account</th>
                            <th>Created By</th>
                            <th>Message Volume</th>
                            <th>Est. Cost</th>
                            <th>Scheduled</th>
                            <th>Status</th>
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
                            <td>{{ number_format($approval['message_volume']) }} messages</td>
                            <td>£{{ number_format($approval['estimated_cost'], 2) }}</td>
                            <td>{{ \Carbon\Carbon::parse($approval['scheduled_time'])->format('M j, H:i') }}</td>
                            <td>
                                <span class="status-badge pending">
                                    <span class="dot"></span>
                                    Pending
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button class="btn action-btn btn-view" data-campaign-id="{{ $approval['id'] }}" title="View Campaign"><i class="fas fa-eye"></i></button>
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
    
    <div class="table-container">
        <div class="section-header px-3 pt-3">
            <h5 class="section-title"><i class="fas fa-history me-2" style="color: #6b7280;"></i>Recent Decisions</h5>
            <button class="btn btn-export" id="btn-export-audit">
                <i class="fas fa-download me-1"></i>Export Audit Log
            </button>
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
                <div class="alert mb-3" style="font-size: 0.85rem; background: #f3e8ff; border: none; color: #6b21a8;">
                    <i class="fas fa-info-circle me-1" style="color: #886cc0;"></i>
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

<div class="modal fade" id="campaignDetailModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-bullhorn me-2" style="color: #886cc0;"></i>Campaign Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-lg-7">
                        <div class="campaign-detail-grid" id="campaign-details">
                            <div class="detail-item">
                                <div class="detail-label">Campaign Name</div>
                                <div class="detail-value" id="detail-name">-</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Sub-Account</div>
                                <div class="detail-value" id="detail-sub-account">-</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Created By</div>
                                <div class="detail-value" id="detail-created-by">-</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Message Volume</div>
                                <div class="detail-value" id="detail-volume">-</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Estimated Cost</div>
                                <div class="detail-value" id="detail-cost">-</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Scheduled Time</div>
                                <div class="detail-value" id="detail-scheduled">-</div>
                            </div>
                        </div>
                        
                        <div class="audit-trail">
                            <div class="audit-trail-title">
                                <i class="fas fa-clipboard-list"></i>
                                Approval Audit Trail
                            </div>
                            <div id="audit-entries">
                                <div class="audit-entry">
                                    <div class="audit-icon submitted"><i class="fas fa-paper-plane"></i></div>
                                    <div class="audit-content">
                                        <div class="audit-action">Submitted for Approval</div>
                                        <div class="audit-meta">by Emma Thompson • Jan 19, 2026 14:30</div>
                                    </div>
                                </div>
                                <div class="audit-entry">
                                    <div class="audit-icon created"><i class="fas fa-plus"></i></div>
                                    <div class="audit-content">
                                        <div class="audit-action">Campaign Created</div>
                                        <div class="audit-meta">by Emma Thompson • Jan 19, 2026 14:15</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="message-preview-section">
                            <div class="message-preview-title">
                                <i class="fas fa-mobile-alt"></i>
                                Message Preview
                            </div>
                            <div id="campaign-preview-container" class="d-flex justify-content-center"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/rcs-preview-renderer.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var campaignData = {
        'camp-001': {
            name: 'January Promo Blast',
            subAccount: 'Marketing Department',
            createdBy: 'Emma Thompson',
            volume: '5,200 messages',
            cost: '£156.00',
            scheduled: 'Jan 20, 09:00',
            channel: 'sms',
            senderId: 'QuickSMS',
            messageContent: "Hi! Don't miss our January Promo - 20% off all products! Use code JAN20 at checkout. Valid until Jan 31. Shop now: quicksms.co/promo",
            audit: [
                { type: 'submitted', action: 'Submitted for Approval', by: 'Emma Thompson', date: 'Jan 19, 2026 14:30' },
                { type: 'created', action: 'Campaign Created', by: 'Emma Thompson', date: 'Jan 19, 2026 14:15' }
            ]
        },
        'camp-002': {
            name: 'Product Launch RCS',
            subAccount: 'Marketing Department',
            createdBy: 'Michael Brown',
            volume: '3,800 messages',
            cost: '£228.00',
            scheduled: 'Jan 21, 10:00',
            channel: 'rich_rcs',
            agent: {
                name: 'QuickSMS Brand',
                logo: 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHJ4PSI4IiBmaWxsPSIjODg2Q0MwIi8+PHRleHQgeD0iNTAlIiB5PSI1NSUiIGRvbWluYW50LWJhc2VsaW5lPSJtaWRkbGUiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGZpbGw9IndoaXRlIiBmb250LXNpemU9IjE2IiBmb250LWZhbWlseT0ic2Fucy1zZXJpZiIgZm9udC13ZWlnaHQ9ImJvbGQiPlFTPC90ZXh0Pjwvc3ZnPg==',
                tagline: 'Official Business',
                verified: true
            },
            richCard: {
                title: 'New Product Launch!',
                description: 'Be the first to experience our revolutionary new product. Exclusive 25% launch discount for early adopters.',
                media: { height: 'medium' },
                buttons: [
                    { label: 'View Details', action: { type: 'url' } },
                    { label: 'Shop Now', action: { type: 'url' } }
                ]
            },
            audit: [
                { type: 'submitted', action: 'Submitted for Approval', by: 'Michael Brown', date: 'Jan 20, 2026 09:15' },
                { type: 'created', action: 'Campaign Created', by: 'Michael Brown', date: 'Jan 20, 2026 08:45' }
            ]
        },
        'camp-003': {
            name: 'Flash Sale Alert',
            subAccount: 'Customer Support',
            createdBy: 'Chris Martinez',
            volume: '1,500 messages',
            cost: '£45.00',
            scheduled: 'Jan 18, 12:00',
            channel: 'sms',
            senderId: 'ALERTS',
            messageContent: "FLASH SALE! 50% off everything for the next 4 hours only! Don't miss out - shop now at quicksms.co/flash",
            audit: [
                { type: 'submitted', action: 'Submitted for Approval', by: 'Chris Martinez', date: 'Jan 17, 2026 16:20' },
                { type: 'created', action: 'Campaign Created', by: 'Chris Martinez', date: 'Jan 17, 2026 15:30' }
            ]
        }
    };
    
    function renderCampaignPreview(data) {
        var container = document.getElementById('campaign-preview-container');
        var previewConfig = {
            channel: data.channel,
            senderId: data.senderId || 'Sender'
        };
        
        if (data.channel === 'sms') {
            previewConfig.message = {
                type: 'text',
                content: { body: data.messageContent }
            };
        } else if (data.channel === 'basic_rcs') {
            previewConfig.agent = data.agent;
            previewConfig.message = {
                type: 'text',
                content: { body: data.messageContent }
            };
        } else if (data.channel === 'rich_rcs' && data.richCard) {
            previewConfig.agent = data.agent;
            previewConfig.message = {
                type: 'rich_card',
                content: data.richCard
            };
        }
        
        container.innerHTML = RcsPreviewRenderer.renderPreview(previewConfig);
    }
    
    document.querySelectorAll('.btn-view').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var campaignId = this.getAttribute('data-campaign-id');
            var data = campaignData[campaignId] || campaignData['camp-001'];
            
            document.getElementById('detail-name').textContent = data.name;
            document.getElementById('detail-sub-account').textContent = data.subAccount;
            document.getElementById('detail-created-by').textContent = data.createdBy;
            document.getElementById('detail-volume').textContent = data.volume;
            document.getElementById('detail-cost').textContent = data.cost;
            document.getElementById('detail-scheduled').textContent = data.scheduled;
            
            renderCampaignPreview(data);
            
            var auditHtml = data.audit.map(function(entry) {
                return '<div class="audit-entry">' +
                    '<div class="audit-icon ' + entry.type + '"><i class="fas fa-' + getAuditIcon(entry.type) + '"></i></div>' +
                    '<div class="audit-content">' +
                        '<div class="audit-action">' + entry.action + '</div>' +
                        '<div class="audit-meta">by ' + entry.by + ' • ' + entry.date + '</div>' +
                        (entry.reason ? '<div class="audit-reason">"' + entry.reason + '"</div>' : '') +
                    '</div>' +
                '</div>';
            }).join('');
            document.getElementById('audit-entries').innerHTML = auditHtml;
            
            new bootstrap.Modal(document.getElementById('campaignDetailModal')).show();
        });
    });
    
    function getAuditIcon(type) {
        const icons = {
            'approved': 'check',
            'rejected': 'times',
            'created': 'plus',
            'submitted': 'paper-plane'
        };
        return icons[type] || 'circle';
    }
    
    document.getElementById('btn-export-audit')?.addEventListener('click', function() {
        const auditData = [
            { campaign: 'Weekend Special', subAccount: 'Marketing Department', createdBy: 'Emma Thompson', decision: 'Approved', approver: 'Sarah Mitchell', reason: '', timestamp: '2026-01-14T16:20:00Z' },
            { campaign: 'Discount Code SMS', subAccount: 'Marketing Department', createdBy: 'Michael Brown', decision: 'Rejected', approver: 'James Wilson', reason: 'Content violates brand guidelines - please revise messaging.', timestamp: '2026-01-13T10:05:00Z' },
            { campaign: 'New Year Blast', subAccount: 'Sales Team', createdBy: 'Sarah Wilson', decision: 'Approved', approver: 'Sarah Mitchell', reason: '', timestamp: '2026-01-12T14:30:00Z' }
        ];
        
        const headers = ['Campaign', 'Sub-Account', 'Created By', 'Decision', 'Approver', 'Reason', 'Timestamp'];
        const csvContent = [
            headers.join(','),
            ...auditData.map(row => [
                `"${row.campaign}"`,
                `"${row.subAccount}"`,
                `"${row.createdBy}"`,
                row.decision,
                `"${row.approver}"`,
                `"${row.reason}"`,
                row.timestamp
            ].join(','))
        ].join('\n');
        
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = `campaign-approval-audit-${new Date().toISOString().split('T')[0]}.csv`;
        link.click();
        
        console.log('[AUDIT] Audit log exported', { timestamp: new Date().toISOString(), records: auditData.length });
    });
    
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
