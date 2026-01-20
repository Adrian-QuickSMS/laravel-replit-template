{{-- 
    Admin-only SenderID Extra Information
    ADMIN ONLY: These sections are NOT part of the customer view
    Include AFTER the canonical review summary partial
--}}
<div class="detail-card" style="margin-top: 1rem;">
    <div class="detail-card-header">
        <i class="fas fa-broadcast-tower"></i> Enabled Channels
        <span class="badge bg-warning text-dark ms-2" style="font-size: 0.65rem;"><i class="fas fa-lock me-1"></i>Admin Only</span>
    </div>
    <div class="detail-card-body">
        <div class="channels-grid">
            <div class="channel-item {{ ($data['channels']['portal'] ?? false) ? 'enabled' : 'disabled' }}">
                <div class="channel-icon"><i class="fas fa-desktop"></i></div>
                <span>Portal</span>
                @if($data['channels']['portal'] ?? false)
                    <i class="fas fa-check-circle ms-auto" style="color: #22c55e;"></i>
                @else
                    <i class="fas fa-times-circle ms-auto" style="color: #dc2626;"></i>
                @endif
            </div>
            <div class="channel-item {{ ($data['channels']['inbox'] ?? false) ? 'enabled' : 'disabled' }}">
                <div class="channel-icon"><i class="fas fa-inbox"></i></div>
                <span>Inbox</span>
                @if($data['channels']['inbox'] ?? false)
                    <i class="fas fa-check-circle ms-auto" style="color: #22c55e;"></i>
                @else
                    <i class="fas fa-times-circle ms-auto" style="color: #dc2626;"></i>
                @endif
            </div>
            <div class="channel-item {{ ($data['channels']['emailToSms'] ?? false) ? 'enabled' : 'disabled' }}">
                <div class="channel-icon"><i class="fas fa-envelope"></i></div>
                <span>Email-to-SMS</span>
                @if($data['channels']['emailToSms'] ?? false)
                    <i class="fas fa-check-circle ms-auto" style="color: #22c55e;"></i>
                @else
                    <i class="fas fa-times-circle ms-auto" style="color: #dc2626;"></i>
                @endif
            </div>
            <div class="channel-item {{ ($data['channels']['api'] ?? false) ? 'enabled' : 'disabled' }}">
                <div class="channel-icon"><i class="fas fa-code"></i></div>
                <span>API</span>
                @if($data['channels']['api'] ?? false)
                    <i class="fas fa-check-circle ms-auto" style="color: #22c55e;"></i>
                @else
                    <i class="fas fa-times-circle ms-auto" style="color: #dc2626;"></i>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="detail-card" style="margin-top: 1rem;">
    <div class="detail-card-header">
        <i class="fas fa-file-alt"></i> Customer Explanation
        <span class="badge bg-warning text-dark ms-2" style="font-size: 0.65rem;"><i class="fas fa-lock me-1"></i>Admin Only</span>
    </div>
    <div class="detail-card-body">
        <div class="explanation-box">
            "{{ $data['explanation'] ?? 'No explanation provided.' }}"
        </div>
    </div>
</div>
