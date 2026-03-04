@extends(isset($isAdminMode) && $isAdminMode ? 'layouts.admin' : 'layouts.quicksms')

@section('title', 'Create Template - Content')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/rcs-preview.css') }}">
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
    justify-content: flex-end;
    gap: 0.5rem;
    padding: 1.5rem 0 0 0;
    border-top: 1px solid #e9ecef;
    margin-top: 2rem;
}
.toolbar-bottom .btn-back {
    background: #a894d4 !important;
    color: #fff !important;
    border: none !important;
    font-weight: 500;
}
.toolbar-bottom .btn-back:hover {
    background: #9783c7 !important;
}
.toolbar-bottom .btn-save-draft {
    background-color: #fff !important;
    color: #D653C1 !important;
    border: 1px solid #D653C1 !important;
    font-weight: 500;
}
.toolbar-bottom .btn-save-draft:hover {
    background-color: rgba(214, 83, 193, 0.08) !important;
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

/* 2-column layout matching Send Message */
.template-content-layout {
    display: flex;
    gap: 1.5rem;
    align-items: flex-start;
}
.template-content-left {
    flex: 1 1 auto;
    min-width: 0;
}
.template-content-right {
    flex: 0 0 460px;
    width: 460px;
    max-width: 100%;
    position: sticky;
    top: 90px;
    align-self: flex-start;
}
.template-content-right .card {
    max-height: calc(100vh - 120px);
    overflow: auto;
}
@media (max-width: 1199.98px) {
    .template-content-layout {
        flex-direction: column;
    }
    .template-content-left,
    .template-content-right {
        flex: 0 0 100%;
        width: 100%;
        max-width: 100%;
        position: static;
    }
    .template-content-right .card {
        max-height: none;
        overflow: visible;
    }
}
@media (max-width: 1440px) {
    .template-content-layout {
        gap: 1rem;
    }
    .template-content-right {
        flex: 0 0 400px;
        width: 400px;
    }
    #mainPreviewContainer {
        transform: scale(0.75);
        margin-bottom: -120px;
    }
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row page-titles">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('management.templates') }}">Templates</a></li>
            <li class="breadcrumb-item active">{{ $isEditMode ? 'Edit Template' : 'Create Template' }}</li>
        </ol>
    </div>

    @if(isset($isAdminMode) && $isAdminMode)
    <div class="alert alert-warning mb-3">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <strong>Admin Mode:</strong> You are editing a template belonging to <strong>{{ $account['name'] ?? 'Unknown Account' }}</strong>. Changes will affect the customer's account.
    </div>
    @endif

    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0"><i class="fas fa-{{ $isEditMode ? 'edit' : 'file-alt' }} me-2 text-primary"></i>{{ $isEditMode ? 'Edit Message Template' : 'Create Message Template' }}</h4>
                </div>
                <div class="card-body">
                    <div class="form-wizard">
                        <ul class="nav nav-wizard">
                            <li class="nav-item"><a class="nav-link done" href="#step-1"><span><i class="fas fa-check"></i></span><small>Metadata</small></a></li>
                            <li class="nav-item"><a class="nav-link active" href="#step-2"><span>2</span><small>Content</small></a></li>
                            <li class="nav-item"><a class="nav-link" href="#step-3"><span>3</span><small>Settings</small></a></li>
                            <li class="nav-item"><a class="nav-link" href="#step-4"><span>4</span><small>Review</small></a></li>
                        </ul>
                        
                        <div class="alert alert-pastel-primary mb-4">
                            <strong>Step 2: Content</strong> - Choose channel and compose your message content.
                        </div>
                        
                        <div class="template-content-layout">
                            <div class="template-content-left">
                                @include('quicksms.partials.message-composer', ['composerMode' => 'template'])

                                <div class="toolbar-bottom">
                                    <a href="@if($isEditMode){{ isset($isAdminMode) && $isAdminMode ? route('admin.management.templates.edit.step1', ['accountId' => $accountId, 'templateId' => $templateId]) : route('management.templates.edit.step1', ['templateId' => $templateId]) }}@else{{ route('management.templates.create.step1') }}@endif" class="btn btn-back">
                                        <i class="fas fa-arrow-left me-1"></i>Back
                                    </a>
                                    <button type="button" class="btn btn-save-draft" id="saveDraftBtn">
                                        <i class="fas fa-save me-1"></i>Save Draft
                                    </button>
                                    <a href="@if($isEditMode){{ isset($isAdminMode) && $isAdminMode ? route('admin.management.templates.edit.step3', ['accountId' => $accountId, 'templateId' => $templateId]) : route('management.templates.edit.step3', ['templateId' => $templateId]) }}@else{{ route('management.templates.create.step3') }}@endif" class="btn btn-primary" id="nextBtn">
                                        Next: Settings <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                            
                            <div class="template-content-right">
                                <div class="card mb-3">
                                    <div class="card-body p-4">
                                        <h6 class="mb-3">Message Preview</h6>
                                        <div id="mainPreviewContainer" class="d-flex justify-content-center" style="transform: scale(0.85); transform-origin: top center; margin-bottom: -70px;"></div>
                                        
                                        <div class="text-center d-none" id="previewToggleContainer">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button type="button" class="btn btn-sm py-0 px-3 active" id="previewRCSBtn" onclick="showPreview('rcs')" style="font-size: 11px; background: #886CC0; color: white; border: 1px solid #886CC0;">RCS</button>
                                                <button type="button" class="btn btn-sm py-0 px-3" id="previewSMSBtn" onclick="showPreview('sms')" style="font-size: 11px; background: white; color: #886CC0; border: 1px solid #886CC0;">SMS</button>
                                            </div>
                                        </div>
                                        
                                        <div class="text-center d-none" id="basicRcsPreviewToggle">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button type="button" class="btn btn-sm py-0 px-3 active" id="basicPreviewRCSBtn" onclick="toggleBasicRcsPreview('rcs')" style="font-size: 11px; background: #886CC0; color: white; border: 1px solid #886CC0;">RCS</button>
                                                <button type="button" class="btn btn-sm py-0 px-3" id="basicPreviewSMSBtn" onclick="toggleBasicRcsPreview('sms')" style="font-size: 11px; background: white; color: #886CC0; border: 1px solid #886CC0;">SMS</button>
                                            </div>
                                        </div>
                                        
                                        <div class="mt-3 border-top pt-2">
                                            <div class="row text-center">
                                                <div class="col-6"><small class="text-muted d-block mb-1">Channel</small><strong id="previewChannel" class="small">SMS</strong></div>
                                                <div class="col-6"><small class="text-muted d-block mb-1">Encoding</small><strong id="previewEncoding" class="small">GSM-7</strong></div>
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
    </div>
</div>

@include('quicksms.partials.rcs-wizard-modal')
@include('quicksms.partials.rcs-button-config-modal')

<div class="modal fade" id="aiAssistantModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header py-3">
                <h5 class="modal-title"><i class="fas fa-magic me-2"></i>AI Content Assistant</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-4">
                    <h6 class="mb-3">Current Message</h6>
                    <div class="p-3 rounded" id="aiCurrentContent" style="background-color: #f0ebf8;">
                        <em class="text-muted">No content to improve</em>
                    </div>
                </div>
                <div class="mb-4">
                    <h6 class="mb-3">What would you like to do?</h6>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-outline-primary" onclick="aiImprove('tone')"><i class="fas fa-smile me-1"></i>Improve tone</button>
                        <button type="button" class="btn btn-outline-primary" onclick="aiImprove('shorten')"><i class="fas fa-compress-alt me-1"></i>Shorten message</button>
                        <button type="button" class="btn btn-outline-primary" onclick="aiImprove('grammar')"><i class="fas fa-spell-check me-1"></i>Correct spelling & grammar</button>
                        <button type="button" class="btn btn-outline-primary" onclick="aiImprove('clarity')"><i class="fas fa-lightbulb me-1"></i>Rephrase for clarity</button>
                    </div>
                </div>
                <div class="d-none" id="aiResultSection">
                    <h6 class="mb-3">Suggested Version</h6>
                    <div class="bg-success bg-opacity-10 border border-success p-3 rounded mb-3" id="aiSuggestedContent"></div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-success" onclick="useAiSuggestion()"><i class="fas fa-check me-1"></i>Use this</button>
                        <button type="button" class="btn btn-outline-secondary" onclick="discardAiSuggestion()">Discard</button>
                    </div>
                </div>
                <div class="d-none" id="aiLoadingSection">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary mb-3"></div>
                        <p class="text-muted">Improving your message...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/rcs-preview-renderer.js') }}?v=20260106b"></script>
<script src="{{ asset('js/rcs-wizard.js') }}"></script>
<script>
var composerMode = 'template';
var rcsWizardCallback = null;
var rcsContentData = null;
var basicRcsPreviewMode = 'rcs';
var richRcsPreviewMode = 'rcs';

window.sender_ids = @json($sender_ids);
window.rcs_agents = @json($rcs_agents);
window.opt_out_lists = @json($opt_out_lists);
window.virtual_numbers = @json($virtual_numbers);
window.optout_domains = @json($optout_domains);

document.addEventListener('DOMContentLoaded', function() {
    initChannelSelector();
    loadSavedData();
    
    setTimeout(function() {
        if (typeof RcsPreviewRenderer !== 'undefined') {
            updatePreview();
        } else {
            console.warn('[Templates] RcsPreviewRenderer not available yet, retrying...');
            setTimeout(updatePreview, 500);
        }
    }, 100);
    
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function(el) {
        new bootstrap.Tooltip(el);
    });
});

function initChannelSelector() {
    document.querySelectorAll('input[name="channel"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            handleChannelChange(this.value);
        });
    });
}

function handleChannelChange(channel) {
    var senderIdSection = document.getElementById('senderIdSection');
    var rcsAgentSection = document.getElementById('rcsAgentSection');
    var contentLabel = document.getElementById('contentLabel');
    var rcsTextHelper = document.getElementById('rcsTextHelper');
    var rcsHelperText = document.getElementById('rcsHelperText');
    var rcsContentSection = document.getElementById('rcsContentSection');
    var basicRcsPreviewToggle = document.getElementById('basicRcsPreviewToggle');
    var previewToggleContainer = document.getElementById('previewToggleContainer');
    var previewChannel = document.getElementById('previewChannel');
    
    if (channel === 'sms') {
        senderIdSection.classList.remove('d-none');
        rcsAgentSection.classList.add('d-none');
        rcsContentSection.classList.add('d-none');
        rcsTextHelper.classList.add('d-none');
        contentLabel.textContent = 'SMS Content';
        if (basicRcsPreviewToggle) basicRcsPreviewToggle.classList.add('d-none');
        if (previewToggleContainer) previewToggleContainer.classList.add('d-none');
        if (previewChannel) previewChannel.textContent = 'SMS';
    } else if (channel === 'rcs_basic') {
        senderIdSection.classList.remove('d-none');
        rcsAgentSection.classList.remove('d-none');
        rcsContentSection.classList.add('d-none');
        rcsTextHelper.classList.remove('d-none');
        rcsHelperText.textContent = 'Messages over 160 characters will be automatically sent as a single RCS message where supported.';
        contentLabel.textContent = 'Message Content';
        if (basicRcsPreviewToggle) basicRcsPreviewToggle.classList.remove('d-none');
        if (previewToggleContainer) previewToggleContainer.classList.add('d-none');
        if (previewChannel) previewChannel.textContent = 'Basic RCS';
        autoSelectFirstAgent();
    } else if (channel === 'rcs_rich') {
        senderIdSection.classList.remove('d-none');
        rcsAgentSection.classList.remove('d-none');
        rcsContentSection.classList.remove('d-none');
        rcsTextHelper.classList.add('d-none');
        contentLabel.textContent = 'SMS Fallback Content';
        if (basicRcsPreviewToggle) basicRcsPreviewToggle.classList.add('d-none');
        if (previewToggleContainer) previewToggleContainer.classList.remove('d-none');
        if (previewChannel) previewChannel.textContent = 'Rich RCS';
        autoSelectFirstAgent();
    }
    
    sessionStorage.setItem('templateWizardChannel', channel);
    handleContentChange();
}

function autoSelectFirstAgent() {
    var rcsAgentSelect = document.getElementById('rcsAgent');
    if (rcsAgentSelect && rcsAgentSelect.selectedIndex === 0 && rcsAgentSelect.options.length > 1) {
        rcsAgentSelect.selectedIndex = 1;
    }
}

function updateRcsWizardPreviewInMain() {
    if (typeof rcsPersistentPayload !== 'undefined' && rcsPersistentPayload) {
        rcsContentData = {
            messageType: rcsPersistentPayload.type || 'single',
            cardCount: rcsPersistentPayload.cardCount || 1,
            title: (rcsPersistentPayload.cards && rcsPersistentPayload.cards[0]) ? rcsPersistentPayload.cards[0].title : '',
            buttonCount: rcsPersistentPayload.cards ? rcsPersistentPayload.cards.reduce(function(sum, c) { return sum + c.buttons.length; }, 0) : 0,
            cards: rcsPersistentPayload.cards || [],
            orientation: rcsPersistentPayload.orientation || {}
        };
        updateRcsContentPreview();
    }
    updatePreview();
}

function updateRcsContentPreview() {
    var summaryEl = document.getElementById('rcsConfiguredSummary');
    var textEl = document.getElementById('rcsConfiguredText');
    
    if (rcsContentData && summaryEl) {
        summaryEl.classList.remove('d-none');
        
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
        if (textEl) textEl.textContent = summary || 'RCS content configured';
    } else if (summaryEl) {
        summaryEl.classList.add('d-none');
    }
    
    updatePreview();
}

function handleContentChange() {
    var content = document.getElementById('smsContent').value;
    var charCount = content.length;
    var hasUnicode = /[^\x00-\x7F]/.test(content);
    var partCount = hasUnicode ? Math.ceil(charCount / 70) : Math.ceil(charCount / 160);
    
    document.getElementById('charCount').textContent = charCount;
    document.getElementById('smsPartCount').textContent = partCount || 1;
    document.getElementById('encodingType').textContent = hasUnicode ? 'Unicode' : 'GSM-7';
    
    var previewEncoding = document.getElementById('previewEncoding');
    if (previewEncoding) previewEncoding.textContent = hasUnicode ? 'Unicode' : 'GSM-7';
    
    if (hasUnicode) {
        document.getElementById('unicodeWarning').classList.remove('d-none');
    } else {
        document.getElementById('unicodeWarning').classList.add('d-none');
    }
    
    updatePreview();
}

function updatePreview() {
    var channel = document.querySelector('input[name="channel"]:checked')?.value || 'sms';
    var container = document.getElementById('mainPreviewContainer');
    if (!container) return;
    if (typeof RcsPreviewRenderer === 'undefined') return;
    
    var senderId = document.getElementById('senderId');
    var smsContent = document.getElementById('smsContent');
    var rcsAgentSelect = document.getElementById('rcsAgent');
    
    var senderIdText = (senderId?.selectedOptions[0]?.text || 'Sender').replace(/\s*\(.*?\)\s*$/, '');
    var messageText = smsContent?.value || '';
    
    var previewConfig = {
        channel: 'sms',
        senderId: senderIdText,
        message: { body: messageText }
    };
    
    if (channel === 'sms') {
        previewConfig.channel = 'sms';
    } else if (channel === 'rcs_basic') {
        if (basicRcsPreviewMode === 'sms') {
            previewConfig.channel = 'sms';
        } else {
            previewConfig.channel = 'basic_rcs';
            var selectedOption = rcsAgentSelect?.selectedOptions[0];
            previewConfig.agent = {
                name: selectedOption?.dataset?.name || selectedOption?.text || 'QuickSMS Brand',
                logo: selectedOption?.dataset?.logo || '{{ asset("images/rcs-agents/quicksms-brand.svg") }}',
                verified: true,
                tagline: selectedOption?.dataset?.tagline || 'Business messaging'
            };
        }
    } else if (channel === 'rcs_rich') {
        if (richRcsPreviewMode === 'sms') {
            previewConfig.channel = 'sms';
        } else {
            var selectedOption = rcsAgentSelect?.selectedOptions[0];
            var agent = {
                name: selectedOption?.dataset?.name || selectedOption?.text || 'QuickSMS Brand',
                logo: selectedOption?.dataset?.logo || '{{ asset("images/rcs-agents/quicksms-brand.svg") }}',
                verified: true,
                tagline: selectedOption?.dataset?.tagline || 'Business messaging'
            };
            
            if (rcsContentData) {
                container.innerHTML = RcsPreviewRenderer.renderRichRcsPreview(rcsContentData, agent);
                RcsPreviewRenderer.initCarouselBehavior('#mainPreviewContainer');
            } else {
                container.innerHTML = RcsPreviewRenderer.renderRichRcsPlaceholder(agent);
            }
            return;
        }
    }
    
    container.innerHTML = RcsPreviewRenderer.renderPreview(previewConfig);
}

function toggleBasicRcsPreview(mode) {
    basicRcsPreviewMode = mode;
    var rcsBtn = document.getElementById('basicPreviewRCSBtn');
    var smsBtn = document.getElementById('basicPreviewSMSBtn');
    
    if (mode === 'rcs') {
        rcsBtn.style.background = '#886CC0';
        rcsBtn.style.color = 'white';
        smsBtn.style.background = 'white';
        smsBtn.style.color = '#886CC0';
    } else {
        rcsBtn.style.background = 'white';
        rcsBtn.style.color = '#886CC0';
        smsBtn.style.background = '#886CC0';
        smsBtn.style.color = 'white';
    }
    updatePreview();
}

function showPreview(mode) {
    richRcsPreviewMode = mode;
    var rcsBtn = document.getElementById('previewRCSBtn');
    var smsBtn = document.getElementById('previewSMSBtn');
    
    if (mode === 'rcs') {
        rcsBtn.style.background = '#886CC0';
        rcsBtn.style.color = 'white';
        smsBtn.style.background = 'white';
        smsBtn.style.color = '#886CC0';
    } else {
        rcsBtn.style.background = 'white';
        rcsBtn.style.color = '#886CC0';
        smsBtn.style.background = '#886CC0';
        smsBtn.style.color = 'white';
    }
    updatePreview();
}

function openPersonalisationModal() {
    alert('Personalisation modal would open here');
}

var aiSuggestedText = '';

function openAiAssistant() {
    var content = document.getElementById('smsContent').value;
    var display = document.getElementById('aiCurrentContent');

    if (content.trim()) {
        display.innerHTML = content;
    } else {
        display.innerHTML = '<em class="text-muted">No content to improve</em>';
    }

    document.getElementById('aiResultSection').classList.add('d-none');
    document.getElementById('aiLoadingSection').classList.add('d-none');

    new bootstrap.Modal(document.getElementById('aiAssistantModal')).show();
}

function aiImprove(action) {
    var content = document.getElementById('smsContent').value;
    if (!content.trim()) {
        alert('Please enter some message content first.');
        return;
    }

    document.getElementById('aiLoadingSection').classList.remove('d-none');
    document.getElementById('aiResultSection').classList.add('d-none');

    setTimeout(function() {
        var suggestions = {
            'tone': 'Hi there! We hope you\'re well! ' + content.replace(/^Hi|^Hello/i, '').trim(),
            'shorten': content.substring(0, Math.min(content.length, 100)) + (content.length > 100 ? '...' : ''),
            'grammar': content.charAt(0).toUpperCase() + content.slice(1).replace(/\s+/g, ' ').trim() + (content.endsWith('.') ? '' : '.'),
            'clarity': content.replace(/\s+/g, ' ').trim()
        };

        aiSuggestedText = suggestions[action] || content;
        document.getElementById('aiSuggestedContent').textContent = aiSuggestedText;
        document.getElementById('aiLoadingSection').classList.add('d-none');
        document.getElementById('aiResultSection').classList.remove('d-none');
    }, 1500);
}

function useAiSuggestion() {
    document.getElementById('smsContent').value = aiSuggestedText;
    handleContentChange();
    bootstrap.Modal.getInstance(document.getElementById('aiAssistantModal')).hide();
}

function discardAiSuggestion() {
    document.getElementById('aiResultSection').classList.add('d-none');
}

var _optOutNumbers = null;
var _currentNumberType = null;
var _keywordValidationTimer = null;

function toggleOptoutManagement() {
    var isEnabled = document.getElementById('enableOptoutManagement').checked;
    document.getElementById('optoutManagementSection').classList.toggle('d-none', !isEnabled);
    document.getElementById('optoutDisabledMessage').classList.toggle('d-none', isEnabled);
    if (!isEnabled) {
        document.getElementById('optoutValidationError').classList.add('d-none');
    } else {
        loadOptOutNumbers();
        validateOptoutConfig();
    }
}

function onScreeningListChange() {
    var checkboxes = document.querySelectorAll('input[name="optOutScreeningLists[]"]:checked');
    var pillsContainer = document.getElementById('screeningPills');
    var pills = Array.from(checkboxes).map(function(cb) {
        var label = document.querySelector('label[for="' + cb.id + '"]');
        var name = label ? label.childNodes[0].textContent.trim() : cb.value;
        return '<span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle d-inline-flex align-items-center gap-1 px-2 py-1">'
            + name
            + '<button type="button" class="btn-close btn-close" style="font-size:0.5rem;" onclick="deselectScreening(\'' + cb.id + '\')"></button>'
            + '</span>';
    }).join('');
    pillsContainer.innerHTML = pills;
    validateOptoutConfig();
}

function deselectScreening(cbId) {
    var cb = document.getElementById(cbId);
    if (cb) { cb.checked = false; onScreeningListChange(); }
}

function getScreeningListIds() {
    return Array.from(document.querySelectorAll('input[name="optOutScreeningLists[]"]:checked')).map(function(cb) { return cb.value; });
}

function toggleReplyOptout() {
    var isEnabled = document.getElementById('enableReplyOptout').checked;
    document.getElementById('replyOptoutConfig').classList.toggle('d-none', !isEnabled);
    if (isEnabled) {
        var urlCb = document.getElementById('enableUrlOptout');
        if (urlCb && urlCb.checked) {
            urlCb.checked = false;
            document.getElementById('urlOptoutConfig').classList.add('d-none');
        }
        loadOptOutNumbers();
    }
    validateOptoutConfig();
}

function toggleUrlOptout() {
    var isEnabled = document.getElementById('enableUrlOptout').checked;
    document.getElementById('urlOptoutConfig').classList.toggle('d-none', !isEnabled);
    if (isEnabled) {
        var replyCb = document.getElementById('enableReplyOptout');
        if (replyCb && replyCb.checked) {
            replyCb.checked = false;
            document.getElementById('replyOptoutConfig').classList.add('d-none');
        }
    }
    validateOptoutConfig();
}

function toggleReplyStorageList() {
    var target = document.querySelector('input[name="replyListTarget"]:checked');
    var isNew = target && target.value === 'new';
    var newFields = document.getElementById('replyNewListFields');
    var listSelect = document.getElementById('replyOptOutListId');
    if (newFields) newFields.classList.toggle('d-none', !isNew);
    if (listSelect) listSelect.disabled = isNew;
    validateOptoutConfig();
}

function toggleUrlStorageList() {
    var target = document.querySelector('input[name="urlListTarget"]:checked');
    var isNew = target && target.value === 'new';
    var newFields = document.getElementById('urlNewListFields');
    var listSelect = document.getElementById('urlOptOutListId');
    if (newFields) newFields.classList.toggle('d-none', !isNew);
    if (listSelect) listSelect.disabled = isNew;
    validateOptoutConfig();
}

function loadOptOutNumbers(onComplete) {
    var select = document.getElementById('optOutNumberId');
    if (!select) return;
    select.innerHTML = '<option value="">-- Loading... --</option>';

    fetch('/api/campaigns/opt-out-numbers', {
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        _optOutNumbers = res.data || { vmns: [], shortcodes: [] };
        select.innerHTML = '<option value="">-- Select number --</option>';

        var vmns = _optOutNumbers.vmns || [];
        var shortcodes = _optOutNumbers.shortcodes || [];

        if (vmns.length > 0) {
            var grp = document.createElement('optgroup');
            grp.label = 'Virtual Mobile Numbers';
            vmns.forEach(function(n) {
                var opt = document.createElement('option');
                opt.value = n.id;
                opt.text = n.number + (n.friendly_name ? ' (' + n.friendly_name + ')' : '');
                opt.dataset.type = 'vmn';
                opt.dataset.number = n.number;
                grp.appendChild(opt);
            });
            select.appendChild(grp);
        }

        if (shortcodes.length > 0) {
            var grp2 = document.createElement('optgroup');
            grp2.label = 'Shortcodes';
            shortcodes.forEach(function(n) {
                var keywords = (n.keywords || []).map(function(k) { return k.keyword; });
                if (n.type === 'shared_shortcode' && keywords.length > 0) {
                    keywords.forEach(function(kw) {
                        var opt = document.createElement('option');
                        opt.value = n.id;
                        opt.text = n.number + ' (' + kw + ')';
                        opt.dataset.type = n.type;
                        opt.dataset.number = n.number;
                        opt.dataset.keyword = kw;
                        grp2.appendChild(opt);
                    });
                } else {
                    var opt = document.createElement('option');
                    opt.value = n.id;
                    opt.text = n.number + (n.friendly_name ? ' (' + n.friendly_name + ')' : '');
                    opt.dataset.type = n.type;
                    opt.dataset.number = n.number;
                    opt.dataset.keyword = keywords.join(', ');
                    grp2.appendChild(opt);
                }
            });
            select.appendChild(grp2);
        }

        if (vmns.length === 0 && shortcodes.length === 0) {
            select.innerHTML = '<option value="">No numbers available</option>';
        }
        if (typeof onComplete === 'function') onComplete();
    })
    .catch(function() {
        select.innerHTML = '<option value="">Failed to load numbers</option>';
    });
}

function onOptOutNumberChange() {
    var select = document.getElementById('optOutNumberId');
    var selectedOpt = select.options[select.selectedIndex];
    var numberType = selectedOpt ? selectedOpt.dataset.type : null;
    _currentNumberType = numberType;

    var keywordInput = document.getElementById('optOutKeywordInput');
    var keywordSelect = document.getElementById('optOutKeywordSelect');

    if (numberType === 'shared_shortcode') {
        var presetKeyword = selectedOpt ? selectedOpt.dataset.keyword : '';
        keywordInput.classList.remove('d-none');
        keywordInput.value = presetKeyword;
        keywordInput.readOnly = true;
        keywordInput.style.backgroundColor = '#f8f9fa';
        keywordSelect.classList.add('d-none');
        refreshOptOutText();
    } else {
        keywordInput.classList.remove('d-none');
        keywordInput.value = '';
        keywordInput.readOnly = false;
        keywordInput.style.backgroundColor = '';
        keywordSelect.classList.add('d-none');
    }

    clearKeywordValidation();
    if (numberType !== 'shared_shortcode') {
        document.getElementById('replyOptoutText').value = '';
    }
    validateOptoutConfig();
}

function onKeywordSelectChange() {
    clearKeywordValidation();
    refreshOptOutText();
    validateOptoutConfig();
}

function scheduleKeywordValidation() {
    if (_keywordValidationTimer) clearTimeout(_keywordValidationTimer);
    _keywordValidationTimer = setTimeout(function() {
        validateOptOutKeyword();
    }, 600);
}

function clearKeywordValidation() {
    document.getElementById('keywordValidationIcon').innerHTML = '';
    var errDiv = document.getElementById('keywordError');
    errDiv.textContent = '';
    errDiv.classList.add('d-none');
}

function validateOptOutKeyword() {
    var numberId = document.getElementById('optOutNumberId').value;
    var keyword = document.getElementById('optOutKeywordInput').value.trim().toUpperCase();

    if (!numberId || !keyword || keyword.length < 4) {
        clearKeywordValidation();
        return;
    }

    if (_currentNumberType === 'shared_shortcode') {
        document.getElementById('keywordValidationIcon').innerHTML = '<i class="fas fa-check-circle text-success"></i>';
        return;
    }

    var icon = document.getElementById('keywordValidationIcon');
    icon.innerHTML = '<i class="fas fa-spinner fa-spin text-muted"></i>';

    fetch('/api/campaigns/validate-opt-out-keyword', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ keyword: keyword, number_id: numberId })
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        var errDiv = document.getElementById('keywordError');
        if (res.valid) {
            icon.innerHTML = '<i class="fas fa-check-circle text-success"></i>';
            errDiv.textContent = '';
            errDiv.classList.add('d-none');
            refreshOptOutText();
        } else {
            icon.innerHTML = '<i class="fas fa-times-circle text-danger"></i>';
            errDiv.textContent = res.message || 'Invalid keyword.';
            errDiv.classList.remove('d-none');
        }
        validateOptoutConfig();
    })
    .catch(function() {
        clearKeywordValidation();
    });
}

function refreshOptOutText() {
    var numberId = document.getElementById('optOutNumberId').value;
    var numberOpt = document.getElementById('optOutNumberId').options[document.getElementById('optOutNumberId').selectedIndex];
    var numberVal = numberOpt ? numberOpt.dataset.number : '';
    var keyword = document.getElementById('optOutKeywordInput').value.trim().toUpperCase();

    var textField = document.getElementById('replyOptoutText');

    if (!numberId || !keyword || !numberVal) {
        textField.value = '';
        return;
    }

    fetch('/api/campaigns/suggest-opt-out-text', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ keyword: keyword, number_id: numberId })
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (res.text) textField.value = res.text;
    })
    .catch(function() {});
}

function insertOptOutTextToMessage(fieldId) {
    var textField = document.getElementById(fieldId);
    var messageArea = document.getElementById('smsContent');
    if (!textField || !messageArea) return;

    var text = textField.value.trim();
    if (!text) {
        alert('Opt-out text is empty. Please configure opt-out settings first.');
        return;
    }

    var current = messageArea.value;
    var separator = current.trim() ? '\n\n' : '';
    messageArea.value = current + separator + text;
    handleContentChange();
}

function validateOptoutConfig() {
    var isEnabled = document.getElementById('enableOptoutManagement') && document.getElementById('enableOptoutManagement').checked;
    if (!isEnabled) {
        document.getElementById('optoutValidationError').classList.add('d-none');
        return true;
    }

    var screeningIds = getScreeningListIds();
    var replyEnabled = document.getElementById('enableReplyOptout').checked;
    var urlEnabled = document.getElementById('enableUrlOptout').checked;
    var errorDiv = document.getElementById('optoutValidationError');
    var errorMsg = document.getElementById('optoutValidationMessage');

    if (screeningIds.length === 0 && !replyEnabled && !urlEnabled) {
        errorMsg.textContent = 'Select an opt-out method or choose a screening list.';
        errorDiv.classList.remove('d-none');
        return false;
    }

    errorDiv.classList.add('d-none');
    return true;
}

function toggleTrackableLinkModal() {
    var enabled = document.getElementById('includeTrackableLink').checked;
    var summary = document.getElementById('trackableLinkSummary');
    if (summary) summary.classList.toggle('d-none', !enabled);
}

function toggleMessageExpiryModal() {
    var enabled = document.getElementById('messageExpiry').checked;
    var summary = document.getElementById('messageExpirySummary');
    if (summary) summary.classList.toggle('d-none', !enabled);
}

function toggleSocialHoursFields() {
    var isChecked = document.getElementById('socialHoursToggle').checked;
    var fields = document.getElementById('socialHoursFields');
    var summary = document.getElementById('socialHoursSummary');
    if (fields) fields.classList.toggle('d-none', !isChecked);
    if (summary && !isChecked) summary.classList.add('d-none');
    if (summary && isChecked) updateSocialHoursSummary();
}

function updateSocialHoursSummary() {
    var from = document.getElementById('socialHoursFrom').value || '08:00';
    var to = document.getElementById('socialHoursTo').value || '20:00';
    var valueEl = document.getElementById('socialHoursValue');
    if (valueEl) valueEl.textContent = from + ' - ' + to;
}

function loadSavedData() {
    var isEditMode = {{ $isEditMode ? 'true' : 'false' }};
    
    if (isEditMode) {
        // In Edit mode, load from template data
        @if($isEditMode && $template)
        var templateChannel = '{{ $template['channel'] ?? 'sms' }}';
        var channelMap = { 'sms': 'channelSMS', 'basic_rcs': 'channelRCSBasic', 'rich_rcs': 'channelRCSRich' };
        var radioId = channelMap[templateChannel];
        if (radioId && document.getElementById(radioId)) {
            document.getElementById(radioId).checked = true;
            handleChannelChange(templateChannel === 'basic_rcs' ? 'rcs_basic' : (templateChannel === 'rich_rcs' ? 'rcs_rich' : templateChannel));
        }
        
        document.getElementById('smsContent').value = '{{ addslashes($template['content'] ?? '') }}';
        
        var templateSenderId = '{{ $template['senderId'] ?? '' }}';
        if (templateSenderId && document.getElementById('senderId')) {
            document.getElementById('senderId').value = templateSenderId;
        }
        
        var templateRcsAgent = '{{ $template['rcsAgent'] ?? '' }}';
        if (templateRcsAgent && document.getElementById('rcsAgent')) {
            document.getElementById('rcsAgent').value = templateRcsAgent;
        }
        @endif
    } else {
        // In Create mode, restore from sessionStorage
        var savedContent = sessionStorage.getItem('templateWizardStep2');
        if (savedContent) {
            var data = JSON.parse(savedContent);
            if (data.smsText) {
                document.getElementById('smsContent').value = data.smsText;
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
            if (data.channel) {
                var channelMap = { 'sms': 'channelSMS', 'rcs_basic': 'channelRCSBasic', 'rcs_rich': 'channelRCSRich' };
                var radioId = channelMap[data.channel];
                if (radioId) {
                    document.getElementById(radioId).checked = true;
                    handleChannelChange(data.channel);
                }
            }
            if (data.optOut) {
                setTimeout(function() { restoreOptOutData(data.optOut); }, 200);
            }
            if (data.socialHours) {
                restoreSocialHoursData(data.socialHours);
            }
        }
        
        var savedChannel = sessionStorage.getItem('templateWizardChannel');
        if (savedChannel && !savedContent) {
            var channelMap = { 'sms': 'channelSMS', 'rcs_basic': 'channelRCSBasic', 'rcs_rich': 'channelRCSRich' };
            var radioId = channelMap[savedChannel];
            if (radioId) {
                document.getElementById(radioId).checked = true;
                handleChannelChange(savedChannel);
            }
        }
    }
    
    handleContentChange();
}

function collectOptOutData() {
    var optOutEnabled = document.getElementById('enableOptoutManagement') && document.getElementById('enableOptoutManagement').checked;
    if (!optOutEnabled) return { enabled: false };

    var data = { enabled: true };
    data.screeningListIds = getScreeningListIds();

    var replyEnabled = document.getElementById('enableReplyOptout') && document.getElementById('enableReplyOptout').checked;
    data.replyEnabled = replyEnabled;
    if (replyEnabled) {
        var numSelect = document.getElementById('optOutNumberId');
        var selectedOpt = numSelect ? numSelect.options[numSelect.selectedIndex] : null;
        data.replyNumberId = numSelect ? numSelect.value : '';
        data.replyNumberType = selectedOpt ? (selectedOpt.dataset.type || '') : '';
        data.replyKeyword = (document.getElementById('optOutKeywordInput') || {}).value || '';
        data.replyOptoutText = (document.getElementById('replyOptoutText') || {}).value || '';
        var replyTarget = document.querySelector('input[name="replyListTarget"]:checked');
        data.replyListTarget = replyTarget ? replyTarget.value : 'existing';
        data.replyOptOutListId = (document.getElementById('replyOptOutListId') || {}).value || '';
        data.replyNewListName = (document.getElementById('replyNewListName') || {}).value || '';
    }

    var urlEnabled = document.getElementById('enableUrlOptout') && document.getElementById('enableUrlOptout').checked;
    data.urlEnabled = urlEnabled;
    if (urlEnabled) {
        data.urlOptoutText = (document.getElementById('urlOptoutText') || {}).value || '';
        var urlTarget = document.querySelector('input[name="urlListTarget"]:checked');
        data.urlListTarget = urlTarget ? urlTarget.value : 'existing';
        data.urlOptOutListId = (document.getElementById('urlOptOutListId') || {}).value || '';
        data.urlNewListName = (document.getElementById('urlNewListName') || {}).value || '';
    }

    return data;
}

function collectSocialHoursData() {
    var toggle = document.getElementById('socialHoursToggle');
    if (!toggle || !toggle.checked) return { enabled: false };
    return {
        enabled: true,
        from: (document.getElementById('socialHoursFrom') || {}).value || '08:00',
        to: (document.getElementById('socialHoursTo') || {}).value || '20:00'
    };
}

function restoreOptOutData(data) {
    if (!data || !data.enabled) return;

    var toggle = document.getElementById('enableOptoutManagement');
    if (toggle) {
        toggle.checked = true;
        toggleOptoutManagement();
    }

    if (data.screeningListIds && data.screeningListIds.length > 0) {
        data.screeningListIds.forEach(function(id) {
            var cb = document.getElementById('screening_' + id);
            if (cb) cb.checked = true;
        });
        onScreeningListChange();
    }

    if (data.replyEnabled) {
        var replyCb = document.getElementById('enableReplyOptout');
        if (replyCb) {
            replyCb.checked = true;
            document.getElementById('replyOptoutConfig').classList.remove('d-none');
        }
        loadOptOutNumbers(function() {
            if (data.replyNumberId) {
                var numSelect = document.getElementById('optOutNumberId');
                if (numSelect) {
                    numSelect.value = data.replyNumberId;
                    onOptOutNumberChange();
                }
            }
            if (data.replyKeyword) {
                var kwInput = document.getElementById('optOutKeywordInput');
                if (kwInput) kwInput.value = data.replyKeyword;
            }
            if (data.replyOptoutText) {
                var textField = document.getElementById('replyOptoutText');
                if (textField) textField.value = data.replyOptoutText;
            }
            if (data.replyListTarget === 'new') {
                var newRadio = document.getElementById('replyListNew');
                if (newRadio) { newRadio.checked = true; toggleReplyStorageList(); }
                var nameField = document.getElementById('replyNewListName');
                if (nameField) nameField.value = data.replyNewListName || '';
            } else if (data.replyOptOutListId) {
                var listSelect = document.getElementById('replyOptOutListId');
                if (listSelect) listSelect.value = data.replyOptOutListId;
            }
        });
    } else if (data.urlEnabled) {
        var urlCb = document.getElementById('enableUrlOptout');
        if (urlCb) {
            urlCb.checked = true;
            document.getElementById('urlOptoutConfig').classList.remove('d-none');
        }
        if (data.urlOptoutText) {
            var tf = document.getElementById('urlOptoutText');
            if (tf) tf.value = data.urlOptoutText;
        }
        if (data.urlListTarget === 'new') {
            var newRadio = document.getElementById('urlListNew');
            if (newRadio) { newRadio.checked = true; toggleUrlStorageList(); }
            var nameField = document.getElementById('urlNewListName');
            if (nameField) nameField.value = data.urlNewListName || '';
        } else if (data.urlOptOutListId) {
            var listSelect = document.getElementById('urlOptOutListId');
            if (listSelect) listSelect.value = data.urlOptOutListId;
        }
    }
}

function restoreSocialHoursData(data) {
    if (!data || !data.enabled) return;
    var toggle = document.getElementById('socialHoursToggle');
    if (toggle) {
        toggle.checked = true;
        toggleSocialHoursFields();
    }
    if (data.from) document.getElementById('socialHoursFrom').value = data.from;
    if (data.to) document.getElementById('socialHoursTo').value = data.to;
    updateSocialHoursSummary();
}

document.getElementById('nextBtn').addEventListener('click', function(e) {
    var channel = document.querySelector('input[name="channel"]:checked').value;
    var smsContent = document.getElementById('smsContent');
    
    if (channel === 'sms' || channel === 'rcs_basic') {
        var text = smsContent.value.trim();
        if (!text) {
            e.preventDefault();
            smsContent.classList.add('is-invalid');
            smsContent.focus();
            return;
        }
    } else if (channel === 'rcs_rich') {
        if (!rcsContentData) {
            e.preventDefault();
            alert('Please configure your RCS content using the wizard.');
            return;
        }
        var fallbackText = smsContent.value.trim();
        if (!fallbackText) {
            e.preventDefault();
            smsContent.classList.add('is-invalid');
            smsContent.focus();
            return;
        }
    }

    var optOutEnabled = document.getElementById('enableOptoutManagement') && document.getElementById('enableOptoutManagement').checked;
    if (optOutEnabled && !validateOptoutConfig()) {
        e.preventDefault();
        return;
    }
    
    sessionStorage.setItem('templateWizardStep2', JSON.stringify({
        channel: channel,
        smsText: smsContent.value,
        senderId: document.getElementById('senderId').value,
        rcsAgent: document.getElementById('rcsAgent').value,
        rcsContentData: rcsContentData,
        optOut: collectOptOutData(),
        socialHours: collectSocialHoursData()
    }));
});

document.getElementById('smsContent').addEventListener('input', function() {
    this.classList.remove('is-invalid');
});
</script>
@endpush
