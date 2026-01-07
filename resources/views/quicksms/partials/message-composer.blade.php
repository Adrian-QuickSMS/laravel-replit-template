@php
$composerMode = $composerMode ?? 'campaign';
$isTemplateMode = $composerMode === 'template';
$composerId = $composerId ?? 'main';
$channelInputName = $isTemplateMode ? 'templateChannel' : 'channel';
$contentInputId = $composerId === 'main' ? 'smsContent' : $composerId . 'Content';
$charCountId = $composerId === 'main' ? 'charCount' : $composerId . 'CharCount';
@endphp

<div class="message-composer" data-mode="{{ $composerMode }}" data-composer-id="{{ $composerId }}">
    <div class="card mb-3">
        <div class="card-body p-4">
            <h6 class="mb-3">Channel</h6>
            <div class="btn-group w-100" role="group">
                <input type="radio" class="btn-check" name="{{ $channelInputName }}" id="{{ $composerId }}ChannelSMS" value="sms" checked>
                <label class="btn btn-outline-primary" for="{{ $composerId }}ChannelSMS"><i class="fas fa-sms me-1"></i>SMS only</label>
                <input type="radio" class="btn-check" name="{{ $channelInputName }}" id="{{ $composerId }}ChannelRCSBasic" value="rcs_basic">
                <label class="btn btn-outline-primary" for="{{ $composerId }}ChannelRCSBasic" data-bs-toggle="tooltip" title="Text-only RCS with SMS fallback"><i class="fas fa-comment-dots me-1"></i>Basic RCS</label>
                <input type="radio" class="btn-check" name="{{ $channelInputName }}" id="{{ $composerId }}ChannelRCSRich" value="rcs_rich">
                <label class="btn btn-outline-primary" for="{{ $composerId }}ChannelRCSRich" data-bs-toggle="tooltip" title="Rich cards, images & buttons with SMS fallback"><i class="fas fa-image me-1"></i>Rich RCS</label>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body p-4">
            <h6 class="mb-3">Content</h6>
            
            @if(!$isTemplateMode)
            <div class="row align-items-center mb-3">
                <div class="col-md-6 col-lg-5 mb-2 mb-md-0">
                    <div class="d-flex align-items-center gap-2">
                        <label class="form-label mb-0 text-nowrap">Template</label>
                        <select class="form-select form-select-sm" id="{{ $composerId }}TemplateSelector" onchange="applySelectedTemplate{{ $composerId === 'main' ? '' : ucfirst($composerId) }}()">
                            <option value="">-- None --</option>
                            @if(isset($templates))
                            @foreach($templates as $template)
                            <option value="{{ $template['id'] }}" data-content="{{ addslashes($template['content']) }}">{{ $template['name'] }}</option>
                            @endforeach
                            @endif
                        </select>
                    </div>
                </div>
                <div class="col-md-6 col-lg-7 text-md-end">
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="openAiAssistant{{ $composerId === 'main' ? '' : ucfirst($composerId) }}()">
                        <i class="fas fa-magic me-1"></i>Improve with AI
                    </button>
                </div>
            </div>
            @else
            <div class="text-end mb-3">
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="openTemplateAiAssistant()">
                    <i class="fas fa-magic me-1"></i>Improve with AI
                </button>
            </div>
            @endif
            
            <label class="form-label mb-2" id="{{ $composerId }}ContentLabel">SMS Content</label>
            
            <div class="position-relative border rounded mb-2" id="{{ $composerId }}TextEditorContainer">
                <textarea class="form-control border-0" id="{{ $contentInputId }}" rows="5" placeholder="Type your message here..." oninput="handleComposerContentChange('{{ $composerId }}')" style="padding-bottom: 40px;"></textarea>
                <div class="position-absolute d-flex gap-2" style="bottom: 8px; right: 12px; z-index: 10;">
                    <button type="button" class="btn btn-sm btn-light border" onclick="openComposerPersonalisation('{{ $composerId }}')" title="Insert personalisation">
                        <i class="fas fa-user-tag"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-light border" id="{{ $composerId }}EmojiPickerBtn" title="Insert emoji" onclick="openComposerEmojiPicker('{{ $composerId }}')">
                        <i class="fas fa-smile"></i>
                    </button>
                </div>
            </div>
            
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                    <span class="text-muted me-3">Characters: <strong id="{{ $charCountId }}">0</strong></span>
                    <span class="text-muted me-3">Encoding: <strong id="{{ $composerId }}EncodingType">GSM-7</strong></span>
                    <span class="text-muted" id="{{ $composerId }}SegmentDisplay">Segments: <strong id="{{ $composerId }}PartCount">1</strong></span>
                </div>
                <span class="badge bg-warning text-dark d-none" id="{{ $composerId }}UnicodeWarning" data-bs-toggle="tooltip" title="This character causes the message to be sent using Unicode encoding.">
                    <i class="fas fa-exclamation-triangle me-1"></i>Unicode
                </span>
            </div>
            
            <div class="d-none mb-2" id="{{ $composerId }}RcsTextHelper">
                <div class="alert alert-info py-2 mb-0">
                    <i class="fas fa-info-circle me-1"></i>
                    <span id="{{ $composerId }}RcsHelperText">Messages over 160 characters will be automatically sent as a single RCS message where supported.</span>
                </div>
            </div>
            
            @if(!$isTemplateMode)
            <div class="border-top pt-3 mb-3">
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="{{ $composerId }}IncludeTrackableLink" onchange="toggleTrackableLinkModal()">
                            <label class="form-check-label" for="{{ $composerId }}IncludeTrackableLink">Include trackable link</label>
                        </div>
                    </div>
                    <div class="col-md-4 mb-2">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="{{ $composerId }}MessageExpiry" onchange="toggleMessageExpiryModal()">
                            <label class="form-check-label" for="{{ $composerId }}MessageExpiry">Message expiry</label>
                        </div>
                    </div>
                    <div class="col-md-4 mb-2">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="{{ $composerId }}ScheduleRules" onchange="toggleScheduleRulesModal()">
                            <label class="form-check-label" for="{{ $composerId }}ScheduleRules">Schedule & sending rules</label>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            
            <div class="d-none mt-3" id="{{ $composerId }}RcsContentSection">
                <div class="border rounded p-3 text-center" style="background-color: rgba(136, 108, 192, 0.1); border-color: rgba(136, 108, 192, 0.2) !important;">
                    <i class="fas fa-image fa-2x text-primary mb-2"></i>
                    <h6 class="mb-2">Rich RCS Card</h6>
                    <p class="text-muted small mb-3">Create rich media cards with images, descriptions, and interactive buttons.</p>
                    <button type="button" class="btn btn-primary" onclick="openRcsWizard{{ $isTemplateMode ? 'Template' : '' }}()">
                        <i class="fas fa-magic me-1"></i>Create RCS Message
                    </button>
                    <div class="d-none mt-3" id="{{ $composerId }}RcsConfiguredSummary">
                        <div class="alert alert-primary py-2 mb-0">
                            <i class="fas fa-check-circle me-1"></i>
                            <span id="{{ $composerId }}RcsConfiguredText">RCS content configured</span>
                            <a href="#" class="ms-2" onclick="openRcsWizard{{ $isTemplateMode ? 'Template' : '' }}(); return false;">Edit</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function handleComposerContentChange(composerId) {
    var contentEl = document.getElementById(composerId === 'main' ? 'smsContent' : composerId + 'Content');
    var charCountEl = document.getElementById(composerId === 'main' ? 'charCount' : composerId + 'CharCount');
    var encodingEl = document.getElementById(composerId + 'EncodingType');
    var partCountEl = document.getElementById(composerId + 'PartCount');
    var unicodeWarningEl = document.getElementById(composerId + 'UnicodeWarning');
    
    if (!contentEl || !charCountEl) return;
    
    var content = contentEl.value;
    var charCount = content.length;
    charCountEl.textContent = charCount;
    
    var hasUnicode = /[^\x00-\x7F\u00A0-\u00FF]/.test(content);
    
    if (encodingEl) {
        encodingEl.textContent = hasUnicode ? 'Unicode' : 'GSM-7';
    }
    
    if (unicodeWarningEl) {
        unicodeWarningEl.classList.toggle('d-none', !hasUnicode);
    }
    
    if (partCountEl) {
        var maxCharsPerSegment = hasUnicode ? 70 : 160;
        var segments = Math.ceil(charCount / maxCharsPerSegment) || 1;
        partCountEl.textContent = segments;
    }
    
    if (typeof updateTemplatePreview === 'function' && composerId !== 'main') {
        updateTemplatePreview();
    }
}

function openComposerPersonalisation(composerId) {
    window.currentComposerId = composerId;
    var modal = document.getElementById('personalisationModal') || document.getElementById('templatePersonalisationModal');
    if (modal) {
        new bootstrap.Modal(modal).show();
    }
}

function openComposerEmojiPicker(composerId) {
    window.currentComposerId = composerId;
    var modal = document.getElementById('emojiModal') || document.getElementById('templateEmojiModal');
    if (modal) {
        new bootstrap.Modal(modal).show();
    }
}
</script>
