@extends('layouts.quicksms')

@section('title', 'Create Template - Metadata')

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
            <li class="active">
                <div class="step-circle">1</div>
                <div class="step-label">Metadata</div>
            </li>
            <li>
                <div class="step-circle">2</div>
                <div class="step-label">Content</div>
            </li>
            <li>
                <div class="step-circle">3</div>
                <div class="step-label">Settings</div>
            </li>
            <li>
                <div class="step-circle">4</div>
                <div class="step-label">Review</div>
            </li>
        </ul>

        <div class="wizard-card">
            <h5 class="form-section-title"><i class="fas fa-info-circle me-2"></i>Template Metadata</h5>
            
            <div class="mb-3">
                <label class="form-label">Template Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="templateName" placeholder="e.g., Welcome Message" maxlength="100">
                <small class="text-muted">A descriptive name to identify this template</small>
            </div>

            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea class="form-control" id="templateDescription" rows="2" placeholder="Optional description..." maxlength="255"></textarea>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Category</label>
                    <select class="form-select" id="templateCategory">
                        <option value="">Select category...</option>
                        <option value="marketing">Marketing</option>
                        <option value="transactional">Transactional</option>
                        <option value="alerts">Alerts & Notifications</option>
                        <option value="reminders">Reminders</option>
                        <option value="promotions">Promotions</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Trigger Type <span class="text-danger">*</span></label>
                    <select class="form-select" id="templateTrigger">
                        <option value="portal">Portal (Manual)</option>
                        <option value="api">API Triggered</option>
                        <option value="both">Both</option>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Tags</label>
                <input type="text" class="form-control" id="templateTags" placeholder="e.g., welcome, onboarding, new-user">
                <small class="text-muted">Comma-separated tags for organization</small>
            </div>

            <div class="wizard-footer">
                <a href="{{ route('management.templates') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i>Cancel
                </a>
                <a href="{{ route('management.templates.create.step2') }}" class="btn btn-primary" id="nextBtn">
                    Next: Content <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var savedData = sessionStorage.getItem('templateWizardStep1');
    if (savedData) {
        var data = JSON.parse(savedData);
        document.getElementById('templateName').value = data.name || '';
        document.getElementById('templateDescription').value = data.description || '';
        document.getElementById('templateCategory').value = data.category || '';
        document.getElementById('templateTrigger').value = data.trigger || 'portal';
        document.getElementById('templateTags').value = data.tags || '';
    }

    document.getElementById('nextBtn').addEventListener('click', function(e) {
        var name = document.getElementById('templateName').value.trim();
        if (!name) {
            e.preventDefault();
            document.getElementById('templateName').classList.add('is-invalid');
            return;
        }

        sessionStorage.setItem('templateWizardStep1', JSON.stringify({
            name: name,
            description: document.getElementById('templateDescription').value.trim(),
            category: document.getElementById('templateCategory').value,
            trigger: document.getElementById('templateTrigger').value,
            tags: document.getElementById('templateTags').value.trim()
        }));
    });

    document.getElementById('templateName').addEventListener('input', function() {
        this.classList.remove('is-invalid');
    });
});
</script>
@endpush
