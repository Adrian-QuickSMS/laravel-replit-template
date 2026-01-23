@extends('layouts.quicksms')

@section('title', 'Create Template - Content')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/rcs-preview.css') }}">
<style>
.wizard-container {
    max-width: 1200px;
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
.channel-selector {
    display: flex;
    gap: 1rem;
    margin-bottom: 1.5rem;
}
.channel-option {
    flex: 1;
    padding: 1rem;
    border: 2px solid #e9ecef;
    border-radius: 0.5rem;
    cursor: pointer;
    text-align: center;
    transition: all 0.2s;
}
.channel-option:hover {
    border-color: var(--primary, #886CC0);
}
.channel-option.active {
    border-color: var(--primary, #886CC0);
    background: rgba(136, 108, 192, 0.05);
}
.channel-option i {
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
    color: var(--primary, #886CC0);
}
.channel-option .channel-name {
    font-weight: 600;
    display: block;
}
.channel-option .channel-desc {
    font-size: 0.75rem;
    color: #6c757d;
}
.content-editor-area {
    min-height: 200px;
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
            <li class="active">
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
            <h5 class="form-section-title"><i class="fas fa-envelope me-2"></i>Message Channel</h5>
            
            <div class="channel-selector">
                <div class="channel-option active" data-channel="sms" onclick="selectChannel('sms')">
                    <i class="fas fa-sms"></i>
                    <span class="channel-name">SMS</span>
                    <span class="channel-desc">Standard text message</span>
                </div>
                <div class="channel-option" data-channel="basic-rcs" onclick="selectChannel('basic-rcs')">
                    <i class="fas fa-comment-dots"></i>
                    <span class="channel-name">Basic RCS</span>
                    <span class="channel-desc">Text with SMS fallback</span>
                </div>
                <div class="channel-option" data-channel="rich-rcs" onclick="selectChannel('rich-rcs')">
                    <i class="fas fa-images"></i>
                    <span class="channel-name">Rich RCS</span>
                    <span class="channel-desc">Media cards & buttons</span>
                </div>
            </div>

            <div id="smsContent">
                <h5 class="form-section-title"><i class="fas fa-edit me-2"></i>Message Content</h5>
                
                <div class="mb-3">
                    <label class="form-label">Message Text <span class="text-danger">*</span></label>
                    <div class="position-relative">
                        <textarea class="form-control" id="smsMessageText" rows="5" placeholder="Enter your message..." maxlength="1600"></textarea>
                        <div class="d-flex justify-content-between mt-1">
                            <small class="text-muted">
                                <span id="smsCharCount">0</span> characters | <span id="smsPartCount">1</span> part(s)
                            </small>
                            <div>
                                <button type="button" class="btn btn-sm btn-outline-secondary me-1" title="Insert personalization">
                                    <i class="fas fa-user-tag"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" title="Insert emoji">
                                    <i class="fas fa-smile"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="rcsContent" class="d-none">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    RCS content editor will open in a dedicated wizard. Configure your rich cards and buttons.
                </div>
                <button type="button" class="btn btn-primary" onclick="openRcsEditor()">
                    <i class="fas fa-magic me-1"></i>Open RCS Content Editor
                </button>
            </div>

            <div class="wizard-footer">
                <a href="{{ route('management.templates.create.step1') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Back
                </a>
                <div>
                    <button type="button" class="btn btn-outline-primary me-2" id="saveDraftBtn">
                        <i class="fas fa-save me-1"></i>Save Draft
                    </button>
                    <a href="{{ route('management.templates.create.step3') }}" class="btn btn-primary" id="nextBtn">
                        Next: Settings <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function selectChannel(channel) {
    document.querySelectorAll('.channel-option').forEach(function(el) {
        el.classList.remove('active');
    });
    document.querySelector('[data-channel="' + channel + '"]').classList.add('active');

    if (channel === 'sms') {
        document.getElementById('smsContent').classList.remove('d-none');
        document.getElementById('rcsContent').classList.add('d-none');
    } else {
        document.getElementById('smsContent').classList.add('d-none');
        document.getElementById('rcsContent').classList.remove('d-none');
    }

    sessionStorage.setItem('templateWizardChannel', channel);
}

function openRcsEditor() {
    alert('RCS Content Editor would open here. This will be integrated with the existing RCS wizard component.');
}

document.addEventListener('DOMContentLoaded', function() {
    var savedChannel = sessionStorage.getItem('templateWizardChannel');
    if (savedChannel) {
        selectChannel(savedChannel);
    }

    var savedContent = sessionStorage.getItem('templateWizardStep2');
    if (savedContent) {
        var data = JSON.parse(savedContent);
        document.getElementById('smsMessageText').value = data.smsText || '';
        updateCharCount();
    }

    document.getElementById('smsMessageText').addEventListener('input', updateCharCount);

    document.getElementById('nextBtn').addEventListener('click', function(e) {
        var channel = sessionStorage.getItem('templateWizardChannel') || 'sms';
        if (channel === 'sms') {
            var text = document.getElementById('smsMessageText').value.trim();
            if (!text) {
                e.preventDefault();
                document.getElementById('smsMessageText').classList.add('is-invalid');
                return;
            }
        }

        sessionStorage.setItem('templateWizardStep2', JSON.stringify({
            channel: channel,
            smsText: document.getElementById('smsMessageText').value
        }));
    });
});

function updateCharCount() {
    var text = document.getElementById('smsMessageText').value;
    var charCount = text.length;
    var partCount = Math.ceil(charCount / 160) || 1;
    document.getElementById('smsCharCount').textContent = charCount;
    document.getElementById('smsPartCount').textContent = partCount;
}
</script>
@endpush
