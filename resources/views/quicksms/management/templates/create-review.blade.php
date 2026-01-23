@extends('layouts.quicksms')

@section('title', 'Create Template - Review')

@push('styles')
<style>
.form-wizard {
    border: 0;
}
.form-wizard .nav-wizard {
    box-shadow: none !important;
    margin-bottom: 2rem;
    display: flex;
    justify-content: center;
    list-style: none;
    padding: 0;
}
.form-wizard .nav-wizard li {
    flex: 1;
    max-width: 150px;
}
.form-wizard .nav-wizard li .nav-link {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-decoration: none;
    color: #6c757d;
    padding: 0;
    background: transparent !important;
    border: none !important;
}
.form-wizard .nav-wizard li .nav-link span {
    border-radius: 3.125rem;
    width: 3rem;
    height: 3rem;
    border: 0.125rem solid var(--primary, #886CC0);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.125rem;
    font-weight: 500;
    background: #fff;
    color: var(--primary, #886CC0);
    position: relative;
    z-index: 1;
}
.form-wizard .nav-wizard li .nav-link:after {
    position: absolute;
    top: 1.5rem;
    left: 50%;
    height: 0.1875rem;
    background: #e9ecef;
    content: "";
    z-index: 0;
    width: 100%;
}
.form-wizard .nav-wizard li:last-child .nav-link:after {
    content: none;
}
.form-wizard .nav-wizard li .nav-link.active span,
.form-wizard .nav-wizard li .nav-link.done span {
    background: var(--primary, #886CC0);
    color: #fff;
    border-color: var(--primary, #886CC0);
}
.form-wizard .nav-wizard li .nav-link.active:after,
.form-wizard .nav-wizard li .nav-link.done:after {
    background: var(--primary, #886CC0) !important;
}
.form-wizard .nav-wizard li .nav-link small {
    display: block;
    margin-top: 0.5rem;
    font-size: 0.75rem;
}
.form-wizard .nav-wizard li .nav-link.active small {
    color: var(--primary, #886CC0);
    font-weight: 600;
}
.toolbar-bottom {
    display: flex;
    justify-content: space-between;
    gap: 0.5rem;
    padding: 1.5rem 0 0 0;
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
.alert-pastel-primary {
    background: rgba(136, 108, 192, 0.1);
    border: 1px solid rgba(136, 108, 192, 0.2);
    color: #614099;
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

    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0"><i class="fas fa-file-alt me-2 text-primary"></i>Create Message Template</h4>
                </div>
                <div class="card-body">
                    <div class="form-wizard">
                        <ul class="nav nav-wizard">
                            <li class="nav-item"><a class="nav-link done" href="#step-1"><span><i class="fas fa-check"></i></span><small>Metadata</small></a></li>
                            <li class="nav-item"><a class="nav-link done" href="#step-2"><span><i class="fas fa-check"></i></span><small>Content</small></a></li>
                            <li class="nav-item"><a class="nav-link done" href="#step-3"><span><i class="fas fa-check"></i></span><small>Settings</small></a></li>
                            <li class="nav-item"><a class="nav-link active" href="#step-4"><span>4</span><small>Review</small></a></li>
                        </ul>
                        
                        <div class="row">
                            <div class="col-lg-10 mx-auto">
                                <div class="alert alert-pastel-primary mb-4">
                                    <strong>Step 4: Review</strong> - Review your template before saving. Click "Edit" to make changes.
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
                                        <span class="review-label">Type:</span>
                                        <span class="review-value" id="reviewType">-</span>
                                    </div>
                                    <div class="review-row">
                                        <span class="review-label">Description:</span>
                                        <span class="review-value" id="reviewDescription">-</span>
                                    </div>
                                    <div class="review-row">
                                        <span class="review-label">Category:</span>
                                        <span class="review-value" id="reviewCategory">-</span>
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
                                        <div class="review-label mb-1">Message:</div>
                                        <div class="message-preview" id="reviewMessage">-</div>
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
                                    <div class="review-row">
                                        <span class="review-label">Scheduling:</span>
                                        <span class="review-value" id="reviewScheduling">-</span>
                                    </div>
                                </div>

                                <div class="toolbar-bottom">
                                    <a href="{{ route('management.templates.create.step3') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-arrow-left me-1"></i>Back
                                    </a>
                                    <div>
                                        <button type="button" class="btn btn-outline-primary me-2" id="saveDraftBtn">
                                            <i class="fas fa-save me-1"></i>Save as Draft
                                        </button>
                                        <button type="button" class="btn btn-primary" id="createTemplateBtn">
                                            <i class="fas fa-check me-1"></i>Create Template
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var step1 = sessionStorage.getItem('templateWizardStep1');
    var step2 = sessionStorage.getItem('templateWizardStep2');
    var step3 = sessionStorage.getItem('templateWizardStep3');
    
    if (step1) {
        var data1 = JSON.parse(step1);
        document.getElementById('reviewName').textContent = data1.name || '-';
        document.getElementById('reviewType').textContent = data1.type === 'api' ? 'API Template' : 'Portal Template';
        document.getElementById('reviewDescription').textContent = data1.description || 'No description';
        document.getElementById('reviewCategory').textContent = data1.category ? data1.category.charAt(0).toUpperCase() + data1.category.slice(1) : 'Not specified';
    }
    
    if (step2) {
        var data2 = JSON.parse(step2);
        var channelMap = { 'sms': 'SMS only', 'rcs_basic': 'Basic RCS + SMS Fallback', 'rcs_rich': 'Rich RCS + SMS Fallback' };
        document.getElementById('reviewChannel').textContent = channelMap[data2.channel] || data2.channel;
        document.getElementById('reviewMessage').textContent = data2.smsText || 'No content';
    }
    
    if (step3) {
        var data3 = JSON.parse(step3);
        var visibilityMap = { 'private': 'Private (Only me)', 'team': 'Team (All users)', 'sub-accounts': 'Sub-accounts included' };
        document.getElementById('reviewVisibility').textContent = visibilityMap[data3.visibility] || data3.visibility;
        document.getElementById('reviewOptOut').textContent = data3.includeOptOut ? 'Enabled' : 'Disabled';
        document.getElementById('reviewScheduling').textContent = data3.enableScheduling ? 'Enabled' : 'Disabled';
    }
    
    document.getElementById('createTemplateBtn').addEventListener('click', function() {
        sessionStorage.removeItem('templateWizardStep1');
        sessionStorage.removeItem('templateWizardStep2');
        sessionStorage.removeItem('templateWizardStep3');
        sessionStorage.removeItem('templateWizardChannel');
        
        alert('Template created successfully!');
        window.location.href = '{{ route("management.templates") }}';
    });
    
    document.getElementById('saveDraftBtn').addEventListener('click', function() {
        alert('Template saved as draft.');
        window.location.href = '{{ route("management.templates") }}';
    });
});
</script>
@endpush
