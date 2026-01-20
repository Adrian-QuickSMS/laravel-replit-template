{{-- 
    Changes Required Banner Component
    Displayed on customer review page when submission has been returned by admin
    
    Usage:
    @include('partials.customer.changes-required-banner', [
        'returnedData' => [
            'reasonCode' => 'INCOMPLETE_INFO',
            'reasonLabel' => 'Incomplete Information',
            'message' => 'Please provide additional documentation...',
            'returnedAt' => '2026-01-20T14:30:00Z',
            'version' => 2
        ]
    ])
--}}

@php
    $returnedData = $returnedData ?? [];
    $reasonCode = $returnedData['reasonCode'] ?? 'OTHER';
    $reasonLabel = $returnedData['reasonLabel'] ?? 'Changes Required';
    $message = $returnedData['message'] ?? '';
    $returnedAt = $returnedData['returnedAt'] ?? now()->toISOString();
    $version = $returnedData['version'] ?? 1;
@endphp

<style>
.changes-required-banner {
    background: linear-gradient(135deg, #fef3c7 0%, #fef9c3 100%);
    border: 1px solid #f59e0b;
    border-left: 4px solid #f59e0b;
    border-radius: 8px;
    padding: 1.25rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 2px 8px rgba(245, 158, 11, 0.15);
}

.changes-required-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1rem;
}

.changes-required-icon {
    width: 40px;
    height: 40px;
    background: #f59e0b;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 1.1rem;
    flex-shrink: 0;
}

.changes-required-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #92400e;
    margin: 0;
}

.changes-required-subtitle {
    font-size: 0.8rem;
    color: #b45309;
    margin: 0;
}

.changes-required-reason {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: rgba(245, 158, 11, 0.2);
    padding: 0.5rem 1rem;
    border-radius: 6px;
    margin-bottom: 1rem;
}

.changes-required-reason .reason-label {
    font-weight: 600;
    color: #92400e;
    font-size: 0.85rem;
}

.changes-required-reason .reason-code {
    font-size: 0.7rem;
    color: #b45309;
    background: rgba(245, 158, 11, 0.3);
    padding: 0.15rem 0.4rem;
    border-radius: 4px;
}

.changes-required-message {
    background: #fff;
    border: 1px solid #fcd34d;
    border-radius: 6px;
    padding: 1rem;
    margin-bottom: 1rem;
}

.changes-required-message-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
    font-size: 0.8rem;
    color: #92400e;
    font-weight: 600;
}

.changes-required-message-content {
    font-size: 0.9rem;
    color: #1e293b;
    line-height: 1.6;
    white-space: pre-wrap;
}

.changes-required-meta {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    font-size: 0.75rem;
    color: #b45309;
}

.changes-required-meta .meta-item {
    display: flex;
    align-items: center;
    gap: 0.35rem;
}

.changes-required-actions {
    display: flex;
    gap: 0.75rem;
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px dashed #fcd34d;
}

.changes-required-actions .btn-edit {
    background: #f59e0b;
    color: #fff;
    border: none;
    padding: 0.6rem 1.25rem;
    border-radius: 6px;
    font-weight: 600;
    font-size: 0.85rem;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
    transition: all 0.2s;
}

.changes-required-actions .btn-edit:hover {
    background: #d97706;
}

.changes-required-actions .btn-view-original {
    background: transparent;
    color: #92400e;
    border: 1px solid #fcd34d;
    padding: 0.6rem 1.25rem;
    border-radius: 6px;
    font-weight: 500;
    font-size: 0.85rem;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
    transition: all 0.2s;
}

.changes-required-actions .btn-view-original:hover {
    background: rgba(245, 158, 11, 0.1);
}

.highlighted-field {
    background: rgba(245, 158, 11, 0.15) !important;
    border-color: #f59e0b !important;
    box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.2) !important;
}
</style>

<div class="changes-required-banner" id="changesRequiredBanner">
    <div class="changes-required-header">
        <div class="changes-required-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div>
            <h4 class="changes-required-title">Changes Required</h4>
            <p class="changes-required-subtitle">Your submission has been reviewed and requires updates before it can be approved</p>
        </div>
    </div>
    
    <div class="changes-required-reason">
        <i class="fas fa-tag"></i>
        <span class="reason-label">{{ $reasonLabel }}</span>
        <span class="reason-code">{{ $reasonCode }}</span>
    </div>
    
    @if($message)
    <div class="changes-required-message">
        <div class="changes-required-message-header">
            <i class="fas fa-comment-alt"></i>
            Message from QuickSMS Review Team
        </div>
        <div class="changes-required-message-content">{{ $message }}</div>
    </div>
    @endif
    
    <div class="changes-required-meta">
        <div class="meta-item">
            <i class="fas fa-clock"></i>
            <span>Returned on {{ \Carbon\Carbon::parse($returnedAt)->format('d M Y, H:i') }}</span>
        </div>
        <div class="meta-item">
            <i class="fas fa-code-branch"></i>
            <span>Version {{ $version }} review</span>
        </div>
    </div>
    
    <div class="changes-required-actions">
        <button type="button" class="btn-edit" onclick="startEditing()">
            <i class="fas fa-edit"></i> Edit & Resubmit
        </button>
        <button type="button" class="btn-view-original" onclick="viewOriginalSubmission()">
            <i class="fas fa-history"></i> View Original
        </button>
    </div>
</div>

<script>
function startEditing() {
    var banner = document.getElementById('changesRequiredBanner');
    if (banner) {
        banner.style.display = 'none';
    }
    
    document.querySelectorAll('.review-section, .detail-card').forEach(function(section) {
        section.classList.add('editable-mode');
    });
    
    document.querySelectorAll('input, textarea, select').forEach(function(field) {
        field.disabled = false;
        field.readOnly = false;
    });
    
    showResubmitControls();
}

function viewOriginalSubmission() {
    var currentUrl = new URL(window.location.href);
    var version = '{{ $version }}';
    currentUrl.searchParams.set('version', 'v' + version);
    currentUrl.searchParams.set('readOnly', 'true');
    window.open(currentUrl.toString(), '_blank');
}

function showResubmitControls() {
    if (document.getElementById('resubmitControls')) return;
    
    var controls = document.createElement('div');
    controls.id = 'resubmitControls';
    controls.className = 'resubmit-controls';
    controls.innerHTML = '\
        <div style="background: #f0fdf4; border: 1px solid #22c55e; border-radius: 8px; padding: 1rem; margin-top: 1.5rem; display: flex; align-items: center; justify-content: space-between;">\
            <div style="display: flex; align-items: center; gap: 0.75rem;">\
                <i class="fas fa-info-circle" style="color: #22c55e;"></i>\
                <span style="color: #166534; font-size: 0.9rem;">Make your changes and click <strong>Resubmit</strong> when ready</span>\
            </div>\
            <div style="display: flex; gap: 0.75rem;">\
                <button type="button" class="btn btn-outline-secondary" onclick="cancelEditing()">Cancel</button>\
                <button type="button" class="btn btn-success" onclick="resubmit()">\
                    <i class="fas fa-paper-plane me-1"></i> Resubmit for Review\
                </button>\
            </div>\
        </div>';
    
    var mainContent = document.querySelector('.main-content, .detail-grid, .container');
    if (mainContent) {
        mainContent.appendChild(controls);
    }
}

function cancelEditing() {
    location.reload();
}

function resubmit() {
    if (confirm('Submit your updated application for review?')) {
        if (typeof UNIFIED_APPROVAL !== 'undefined' && UNIFIED_APPROVAL.handleResubmission) {
            UNIFIED_APPROVAL.handleResubmission(collectFormData());
            alert('Your application has been resubmitted for review. You will receive an email notification when the review is complete.');
            location.reload();
        } else {
            alert('Submission resubmitted successfully.');
            location.reload();
        }
    }
}

function collectFormData() {
    var data = {};
    document.querySelectorAll('input, textarea, select').forEach(function(field) {
        if (field.name || field.id) {
            data[field.name || field.id] = field.value;
        }
    });
    return data;
}
</script>
