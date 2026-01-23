@extends('layouts.quicksms')

@section('title', 'Create Template - Settings')

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
.settings-section {
    background: #f8f9fa;
    border-radius: 0.5rem;
    padding: 1.25rem;
    margin-bottom: 1.5rem;
}
.settings-section h6 {
    margin-bottom: 1rem;
    font-weight: 600;
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
            <li class="active">
                <div class="step-circle">3</div>
                <div class="step-label">Settings</div>
            </li>
            <li>
                <div class="step-circle">4</div>
                <div class="step-label">Review</div>
            </li>
        </ul>

        <div class="wizard-card">
            <h5 class="form-section-title"><i class="fas fa-cog me-2"></i>Template Settings</h5>
            
            <div class="settings-section">
                <h6><i class="fas fa-user-shield me-2"></i>Access Control</h6>
                <div class="mb-3">
                    <label class="form-label">Visibility</label>
                    <select class="form-select" id="templateVisibility">
                        <option value="private">Private (Only me)</option>
                        <option value="team">Team (All users in account)</option>
                        <option value="sub-accounts">Sub-accounts (Include sub-accounts)</option>
                    </select>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="allowEditing">
                    <label class="form-check-label" for="allowEditing">
                        Allow team members to edit this template
                    </label>
                </div>
            </div>

            <div class="settings-section">
                <h6><i class="fas fa-ban me-2"></i>Opt-Out Settings</h6>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="includeOptOut" checked>
                    <label class="form-check-label" for="includeOptOut">
                        Include opt-out link in message
                    </label>
                </div>
                <div id="optOutOptions">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Opt-Out Link Text</label>
                            <input type="text" class="form-control" id="optOutText" value="Reply STOP to opt out" placeholder="e.g., Reply STOP to opt out">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Opt-Out List</label>
                            <select class="form-select" id="optOutList">
                                <option value="master">Master Opt-Out List</option>
                                <option value="marketing">Marketing Opt-Outs</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="settings-section">
                <h6><i class="fas fa-clock me-2"></i>Scheduling</h6>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="enableScheduling">
                    <label class="form-check-label" for="enableScheduling">
                        Allow scheduled sending for this template
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="enableQuietHours">
                    <label class="form-check-label" for="enableQuietHours">
                        Respect quiet hours (no sending between 9pm-8am)
                    </label>
                </div>
            </div>

            <div class="wizard-footer">
                <a href="{{ route('management.templates.create.step2') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Back
                </a>
                <div>
                    <button type="button" class="btn btn-outline-primary me-2" id="saveDraftBtn">
                        <i class="fas fa-save me-1"></i>Save Draft
                    </button>
                    <a href="{{ route('management.templates.create.review') }}" class="btn btn-primary" id="nextBtn">
                        Next: Review <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var savedData = sessionStorage.getItem('templateWizardStep3');
    if (savedData) {
        var data = JSON.parse(savedData);
        document.getElementById('templateVisibility').value = data.visibility || 'private';
        document.getElementById('allowEditing').checked = data.allowEditing || false;
        document.getElementById('includeOptOut').checked = data.includeOptOut !== false;
        document.getElementById('optOutText').value = data.optOutText || 'Reply STOP to opt out';
        document.getElementById('enableScheduling').checked = data.enableScheduling || false;
        document.getElementById('enableQuietHours').checked = data.enableQuietHours || false;
    }

    document.getElementById('includeOptOut').addEventListener('change', function() {
        document.getElementById('optOutOptions').style.display = this.checked ? 'block' : 'none';
    });

    document.getElementById('nextBtn').addEventListener('click', function() {
        sessionStorage.setItem('templateWizardStep3', JSON.stringify({
            visibility: document.getElementById('templateVisibility').value,
            allowEditing: document.getElementById('allowEditing').checked,
            includeOptOut: document.getElementById('includeOptOut').checked,
            optOutText: document.getElementById('optOutText').value,
            optOutList: document.getElementById('optOutList').value,
            enableScheduling: document.getElementById('enableScheduling').checked,
            enableQuietHours: document.getElementById('enableQuietHours').checked
        }));
    });
});
</script>
@endpush
