@extends('layouts.quicksms')

@section('title', 'Create Template - Review')

@push('styles')
<style>
.wizard-container {
    max-width: 900px;
    margin: 0 auto;
}
.wizard-progress {
    display: flex;
    justify-content: center;
    margin-bottom: 2rem;
    padding: 0;
    list-style: none;
}
.wizard-progress li {
    flex: 1;
    max-width: 180px;
    position: relative;
}
.wizard-progress li .step-circle {
    width: 3rem;
    height: 3rem;
    border-radius: 50%;
    border: 2px solid #e9ecef;
    background: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 0.5rem;
    font-weight: 600;
    color: #6c757d;
    position: relative;
    z-index: 2;
}
.wizard-progress li.active .step-circle,
.wizard-progress li.completed .step-circle {
    background: var(--primary, #886CC0);
    border-color: var(--primary, #886CC0);
    color: #fff;
}
.wizard-progress li .step-label {
    font-size: 0.8rem;
    color: #6c757d;
    text-align: center;
}
.wizard-progress li.active .step-label {
    color: var(--primary, #886CC0);
    font-weight: 600;
}
.wizard-progress li:not(:last-child)::after {
    content: '';
    position: absolute;
    top: 1.5rem;
    left: 50%;
    width: 100%;
    height: 2px;
    background: #e9ecef;
    z-index: 1;
}
.wizard-progress li.completed:not(:last-child)::after {
    background: var(--primary, #886CC0);
}
.wizard-card {
    background: #fff;
    border-radius: 0.75rem;
    border: 1px solid #e9ecef;
    padding: 2rem;
}
.wizard-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 1.5rem;
    border-top: 1px solid #e9ecef;
    margin-top: 2rem;
}
.form-section-title {
    font-size: 1rem;
    font-weight: 600;
    color: #343a40;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #e9ecef;
}
.review-section {
    background: #f8f9fa;
    border-radius: 0.5rem;
    padding: 1.25rem;
    margin-bottom: 1.5rem;
}
.review-section h6 {
    margin-bottom: 1rem;
    font-weight: 600;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.review-section h6 a {
    font-size: 0.8rem;
    font-weight: 400;
}
.review-row {
    display: flex;
    margin-bottom: 0.5rem;
}
.review-label {
    width: 140px;
    color: #6c757d;
    font-size: 0.85rem;
}
.review-value {
    flex: 1;
    font-size: 0.85rem;
}
.message-preview {
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 0.5rem;
    padding: 1rem;
    white-space: pre-wrap;
    font-family: inherit;
    font-size: 0.9rem;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row page-titles">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('management.templates') }}">Templates</a></li>
            <li class="breadcrumb-item active">Create Template</li>
        </ol>
    </div>

    <div class="wizard-container">
        <ul class="wizard-progress">
            <li class="completed">
                <div class="step-circle"><i class="fas fa-check"></i></div>
                <div class="step-label">Metadata</div>
            </li>
            <li class="completed">
                <div class="step-circle"><i class="fas fa-check"></i></div>
                <div class="step-label">Content</div>
            </li>
            <li class="completed">
                <div class="step-circle"><i class="fas fa-check"></i></div>
                <div class="step-label">Settings</div>
            </li>
            <li class="active">
                <div class="step-circle">4</div>
                <div class="step-label">Review</div>
            </li>
        </ul>

        <div class="wizard-card">
            <h5 class="form-section-title"><i class="fas fa-clipboard-check me-2"></i>Review Your Template</h5>
            
            <div class="alert alert-info mb-4">
                <i class="fas fa-info-circle me-2"></i>
                Review your template details before saving. Click "Edit" on any section to make changes.
            </div>

            <div class="review-section">
                <h6>
                    <span><i class="fas fa-info-circle me-2"></i>Metadata</span>
                    <a href="{{ route('management.templates.create.step1') }}"><i class="fas fa-edit me-1"></i>Edit</a>
                </h6>
                <div class="review-row">
                    <span class="review-label">Name:</span>
                    <span class="review-value" id="reviewName">-</span>
                </div>
                <div class="review-row">
                    <span class="review-label">Description:</span>
                    <span class="review-value" id="reviewDescription">-</span>
                </div>
                <div class="review-row">
                    <span class="review-label">Category:</span>
                    <span class="review-value" id="reviewCategory">-</span>
                </div>
                <div class="review-row">
                    <span class="review-label">Trigger:</span>
                    <span class="review-value" id="reviewTrigger">-</span>
                </div>
            </div>

            <div class="review-section">
                <h6>
                    <span><i class="fas fa-envelope me-2"></i>Content</span>
                    <a href="{{ route('management.templates.create.step2') }}"><i class="fas fa-edit me-1"></i>Edit</a>
                </h6>
                <div class="review-row">
                    <span class="review-label">Channel:</span>
                    <span class="review-value" id="reviewChannel">-</span>
                </div>
                <div class="mt-2">
                    <span class="review-label d-block mb-2">Message Preview:</span>
                    <div class="message-preview" id="reviewMessagePreview">-</div>
                </div>
            </div>

            <div class="review-section">
                <h6>
                    <span><i class="fas fa-cog me-2"></i>Settings</span>
                    <a href="{{ route('management.templates.create.step3') }}"><i class="fas fa-edit me-1"></i>Edit</a>
                </h6>
                <div class="review-row">
                    <span class="review-label">Visibility:</span>
                    <span class="review-value" id="reviewVisibility">-</span>
                </div>
                <div class="review-row">
                    <span class="review-label">Opt-Out:</span>
                    <span class="review-value" id="reviewOptOut">-</span>
                </div>
            </div>

            <div class="wizard-footer">
                <a href="{{ route('management.templates.create.step3') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Back
                </a>
                <div>
                    <button type="button" class="btn btn-outline-primary me-2" id="saveDraftBtn">
                        <i class="fas fa-save me-1"></i>Save as Draft
                    </button>
                    <button type="button" class="btn btn-success" id="createTemplateBtn">
                        <i class="fas fa-check me-1"></i>Create Template
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center py-5">
                <div class="mb-4">
                    <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                </div>
                <h4 class="mb-2">Template Created!</h4>
                <p class="text-muted mb-4">Your template has been successfully created and is ready to use.</p>
                <a href="{{ route('management.templates') }}" class="btn btn-primary">
                    <i class="fas fa-arrow-left me-1"></i>Back to Templates
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var step1 = JSON.parse(sessionStorage.getItem('templateWizardStep1') || '{}');
    var step2 = JSON.parse(sessionStorage.getItem('templateWizardStep2') || '{}');
    var step3 = JSON.parse(sessionStorage.getItem('templateWizardStep3') || '{}');
    var channel = sessionStorage.getItem('templateWizardChannel') || 'sms';

    document.getElementById('reviewName').textContent = step1.name || '-';
    document.getElementById('reviewDescription').textContent = step1.description || 'No description';
    document.getElementById('reviewCategory').textContent = step1.category ? step1.category.charAt(0).toUpperCase() + step1.category.slice(1) : 'Not specified';
    document.getElementById('reviewTrigger').textContent = step1.trigger === 'portal' ? 'Portal (Manual)' : step1.trigger === 'api' ? 'API Triggered' : 'Both';

    var channelLabels = {
        'sms': 'SMS',
        'basic-rcs': 'Basic RCS + SMS Fallback',
        'rich-rcs': 'Rich RCS + SMS Fallback'
    };
    document.getElementById('reviewChannel').textContent = channelLabels[channel] || 'SMS';
    document.getElementById('reviewMessagePreview').textContent = step2.smsText || 'No message content';

    var visibilityLabels = {
        'private': 'Private (Only me)',
        'team': 'Team (All users)',
        'sub-accounts': 'Include sub-accounts'
    };
    document.getElementById('reviewVisibility').textContent = visibilityLabels[step3.visibility] || 'Private';
    document.getElementById('reviewOptOut').textContent = step3.includeOptOut !== false ? 'Included' : 'Not included';

    document.getElementById('createTemplateBtn').addEventListener('click', function() {
        sessionStorage.removeItem('templateWizardStep1');
        sessionStorage.removeItem('templateWizardStep2');
        sessionStorage.removeItem('templateWizardStep3');
        sessionStorage.removeItem('templateWizardChannel');

        var modal = new bootstrap.Modal(document.getElementById('successModal'));
        modal.show();
    });
});
</script>
@endpush
