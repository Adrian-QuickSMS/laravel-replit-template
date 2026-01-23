@extends('layouts.quicksms')

@section('title', 'Create Template - Settings')

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
                            <li class="nav-item"><a class="nav-link active" href="#step-3"><span>3</span><small>Settings</small></a></li>
                            <li class="nav-item"><a class="nav-link" href="#step-4"><span>4</span><small>Review</small></a></li>
                        </ul>
                        
                        <div class="row">
                            <div class="col-lg-10 mx-auto">
                                <div class="alert alert-pastel-primary mb-4">
                                    <strong>Step 3: Settings</strong> - Configure access control, opt-out and scheduling options.
                                </div>
                                
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

                                <div class="toolbar-bottom">
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
        document.getElementById('optOutList').value = data.optOutList || 'master';
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
