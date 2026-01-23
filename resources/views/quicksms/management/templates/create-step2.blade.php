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
            <div class="card mb-3 border-0 p-0">
                <div class="card-body p-0">
                    <h6 class="form-section-title"><i class="fas fa-broadcast-tower me-2"></i>Channel & Sender</h6>
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="channel" id="channelSMS" value="sms" checked>
                                <label class="btn btn-outline-primary" for="channelSMS"><i class="fas fa-sms me-1"></i>SMS only</label>
                                <input type="radio" class="btn-check" name="channel" id="channelRCSBasic" value="rcs_basic">
                                <label class="btn btn-outline-primary" for="channelRCSBasic" data-bs-toggle="tooltip" title="Text-only RCS with SMS fallback"><i class="fas fa-comment-dots me-1"></i>Basic RCS</label>
                                <input type="radio" class="btn-check" name="channel" id="channelRCSRich" value="rcs_rich">
                                <label class="btn btn-outline-primary" for="channelRCSRich" data-bs-toggle="tooltip" title="Rich cards, images & buttons with SMS fallback"><i class="fas fa-image me-1"></i>Rich RCS</label>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6" id="senderIdSection">
                            <select class="form-select" id="senderId" onchange="updatePreview()">
                                <option value="">SMS Sender ID *</option>
                                @foreach($sender_ids as $sender)
                                <option value="{{ $sender['id'] }}">{{ $sender['name'] }} ({{ $sender['type'] }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 d-none" id="rcsAgentSection">
                            <select class="form-select" id="rcsAgent" onchange="updatePreview()">
                                <option value="">RCS Agent *</option>
                                @foreach($rcs_agents as $agent)
                                <option value="{{ $agent['id'] }}" 
                                    data-name="{{ $agent['name'] }}"
                                    data-logo="{{ $agent['logo'] ?? '' }}"
                                    data-tagline="{{ $agent['tagline'] ?? '' }}"
                                    data-brand-color="{{ $agent['brand_color'] ?? '#886CC0' }}">{{ $agent['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mb-3 border-0 p-0" id="smsContentCard">
                <div class="card-body p-0">
                    <h6 class="form-section-title"><i class="fas fa-edit me-2"></i>Message Content</h6>
                    
                    <label class="form-label mb-2" id="contentLabel">SMS Content</label>
                    
                    <div class="position-relative border rounded mb-2">
                        <textarea class="form-control border-0" id="smsContent" rows="5" placeholder="Type your message here..." oninput="handleContentChange()" style="padding-bottom: 40px;"></textarea>
                        <div class="position-absolute d-flex gap-2" style="bottom: 8px; right: 12px; z-index: 10;">
                            <button type="button" class="btn btn-sm btn-light border" onclick="openPersonalisationModal()" title="Insert personalisation">
                                <i class="fas fa-user-tag"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-light border" id="emojiPickerBtn" title="Insert emoji">
                                <i class="fas fa-smile"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <span class="text-muted me-3">Characters: <strong id="charCount">0</strong></span>
                            <span class="text-muted me-3">Encoding: <strong id="encodingType">GSM-7</strong></span>
                            <span class="text-muted" id="segmentDisplay">Segments: <strong id="smsPartCount">1</strong></span>
                        </div>
                        <span class="badge bg-warning text-dark d-none" id="unicodeWarning" data-bs-toggle="tooltip" title="This character causes the message to be sent using Unicode encoding.">
                            <i class="fas fa-exclamation-triangle me-1"></i>Unicode
                        </span>
                    </div>
                    
                    <div class="d-flex justify-content-end">
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="openAiAssistant()">
                            <i class="fas fa-magic me-1"></i>Improve with AI
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="card mb-3 border-0 p-0 d-none" id="rcsContentCard">
                <div class="card-body p-0">
                    <h6 class="form-section-title"><i class="fas fa-images me-2"></i>Rich RCS Content</h6>
                    
                    <div class="alert alert-info mb-3">
                        <i class="fas fa-info-circle me-2"></i>
                        Create rich cards with images, titles, descriptions and interactive buttons.
                    </div>
                    
                    <div id="rcsContentPreviewArea" class="p-3 border rounded mb-3 d-none" style="background: #f8f9fa;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-success"><i class="fas fa-check me-1"></i>RCS Content Configured</span>
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="openRcsWizard()">
                                <i class="fas fa-edit me-1"></i>Edit
                            </button>
                        </div>
                        <div id="rcsContentSummary" class="small text-muted"></div>
                    </div>
                    
                    <button type="button" class="btn btn-primary" id="openRcsWizardBtn" onclick="openRcsWizard()">
                        <i class="fas fa-magic me-1"></i>Open RCS Content Wizard
                    </button>
                    
                    <div class="mt-3">
                        <label class="form-label small">SMS Fallback Message <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="smsFallback" rows="3" placeholder="Fallback text for non-RCS devices..."></textarea>
                        <small class="text-muted">Displayed when the recipient's device doesn't support RCS</small>
                    </div>
                </div>
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

@include('quicksms.partials.rcs-wizard-modal')
@include('quicksms.partials.rcs-button-config-modal')
@endsection

@push('scripts')
<script src="{{ asset('js/rcs-preview.js') }}"></script>
<script src="{{ asset('js/rcs-wizard.js') }}"></script>
<script>
var composerMode = 'template';
var rcsWizardCallback = null;
var rcsContentData = null;

window.sender_ids = @json($sender_ids);
window.rcs_agents = @json($rcs_agents);
window.opt_out_lists = @json($opt_out_lists);
window.virtual_numbers = @json($virtual_numbers);
window.optout_domains = @json($optout_domains);

document.addEventListener('DOMContentLoaded', function() {
    initChannelSelector();
    loadSavedData();
    
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function(el) {
        new bootstrap.Tooltip(el);
    });
});

function initChannelSelector() {
    document.querySelectorAll('input[name="channel"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            var channel = this.value;
            
            if (channel === 'sms') {
                document.getElementById('senderIdSection').classList.remove('d-none');
                document.getElementById('rcsAgentSection').classList.add('d-none');
                document.getElementById('smsContentCard').classList.remove('d-none');
                document.getElementById('rcsContentCard').classList.add('d-none');
                document.getElementById('contentLabel').textContent = 'SMS Content';
            } else if (channel === 'rcs_basic') {
                document.getElementById('senderIdSection').classList.add('d-none');
                document.getElementById('rcsAgentSection').classList.remove('d-none');
                document.getElementById('smsContentCard').classList.remove('d-none');
                document.getElementById('rcsContentCard').classList.add('d-none');
                document.getElementById('contentLabel').textContent = 'Basic RCS Content';
            } else if (channel === 'rcs_rich') {
                document.getElementById('senderIdSection').classList.add('d-none');
                document.getElementById('rcsAgentSection').classList.remove('d-none');
                document.getElementById('smsContentCard').classList.add('d-none');
                document.getElementById('rcsContentCard').classList.remove('d-none');
            }
            
            sessionStorage.setItem('templateWizardChannel', channel);
        });
    });
}

function openRcsWizard() {
    if (typeof initRcsWizard === 'function') {
        var selectedAgent = document.getElementById('rcsAgent');
        var agentData = null;
        
        if (selectedAgent && selectedAgent.selectedIndex > 0) {
            var option = selectedAgent.options[selectedAgent.selectedIndex];
            agentData = {
                name: option.dataset.name || 'QuickSMS Brand',
                logo: option.dataset.logo || '',
                tagline: option.dataset.tagline || '',
                brandColor: option.dataset.brandColor || '#886CC0'
            };
        }
        
        rcsWizardCallback = function(data) {
            rcsContentData = data;
            updateRcsContentPreview();
        };
        
        initRcsWizard(agentData, rcsContentData);
        var modal = new bootstrap.Modal(document.getElementById('rcsWizardModal'));
        modal.show();
    } else {
        alert('RCS Wizard is loading. Please wait...');
    }
}

function updateRcsContentPreview() {
    if (rcsContentData) {
        document.getElementById('rcsContentPreviewArea').classList.remove('d-none');
        document.getElementById('openRcsWizardBtn').classList.add('d-none');
        
        var summary = '';
        if (rcsContentData.messageType === 'carousel') {
            summary = 'Carousel with ' + (rcsContentData.cardCount || 1) + ' cards';
        } else {
            summary = 'Single Rich Card';
            if (rcsContentData.title) summary += ': ' + rcsContentData.title;
        }
        if (rcsContentData.buttonCount) {
            summary += ' (' + rcsContentData.buttonCount + ' buttons)';
        }
        document.getElementById('rcsContentSummary').textContent = summary;
    }
}

function handleContentChange() {
    var content = document.getElementById('smsContent').value;
    var charCount = content.length;
    var hasUnicode = /[^\x00-\x7F]/.test(content);
    var partCount = hasUnicode ? Math.ceil(charCount / 70) : Math.ceil(charCount / 160);
    
    document.getElementById('charCount').textContent = charCount;
    document.getElementById('smsPartCount').textContent = partCount || 1;
    document.getElementById('encodingType').textContent = hasUnicode ? 'Unicode' : 'GSM-7';
    
    if (hasUnicode) {
        document.getElementById('unicodeWarning').classList.remove('d-none');
    } else {
        document.getElementById('unicodeWarning').classList.add('d-none');
    }
}

function updatePreview() {
}

function openPersonalisationModal() {
    alert('Personalisation modal would open here');
}

function openAiAssistant() {
    alert('AI Assistant would open here');
}

function loadSavedData() {
    var savedChannel = sessionStorage.getItem('templateWizardChannel');
    if (savedChannel) {
        var radio = document.getElementById('channel' + savedChannel.charAt(0).toUpperCase() + savedChannel.slice(1).replace('_', ''));
        if (radio) {
            radio.checked = true;
            radio.dispatchEvent(new Event('change'));
        }
    }
    
    var savedContent = sessionStorage.getItem('templateWizardStep2');
    if (savedContent) {
        var data = JSON.parse(savedContent);
        if (data.smsText) {
            document.getElementById('smsContent').value = data.smsText;
            handleContentChange();
        }
        if (data.smsFallback) {
            document.getElementById('smsFallback').value = data.smsFallback;
        }
        if (data.senderId) {
            document.getElementById('senderId').value = data.senderId;
        }
        if (data.rcsAgent) {
            document.getElementById('rcsAgent').value = data.rcsAgent;
        }
        if (data.rcsContentData) {
            rcsContentData = data.rcsContentData;
            updateRcsContentPreview();
        }
    }
}

document.getElementById('nextBtn').addEventListener('click', function(e) {
    var channel = document.querySelector('input[name="channel"]:checked').value;
    
    if (channel === 'sms' || channel === 'rcs_basic') {
        var text = document.getElementById('smsContent').value.trim();
        if (!text) {
            e.preventDefault();
            document.getElementById('smsContent').classList.add('is-invalid');
            return;
        }
    } else if (channel === 'rcs_rich') {
        if (!rcsContentData) {
            e.preventDefault();
            alert('Please configure your RCS content using the wizard.');
            return;
        }
        var fallback = document.getElementById('smsFallback').value.trim();
        if (!fallback) {
            e.preventDefault();
            document.getElementById('smsFallback').classList.add('is-invalid');
            return;
        }
    }
    
    sessionStorage.setItem('templateWizardStep2', JSON.stringify({
        channel: channel,
        smsText: document.getElementById('smsContent').value,
        smsFallback: document.getElementById('smsFallback').value,
        senderId: document.getElementById('senderId').value,
        rcsAgent: document.getElementById('rcsAgent').value,
        rcsContentData: rcsContentData
    }));
});

document.getElementById('smsContent').addEventListener('input', function() {
    this.classList.remove('is-invalid');
});
document.getElementById('smsFallback').addEventListener('input', function() {
    this.classList.remove('is-invalid');
});
</script>
@endpush
