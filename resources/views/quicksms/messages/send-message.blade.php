@extends('layouts.quicksms')

@section('title', 'Send Message')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/rcs-preview.css') }}">
<style>
/* Page-specific validation styles */
.validation-error-field {
    border-color: #dc3545 !important;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
}
.validation-error-field:focus {
    border-color: #dc3545 !important;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
}
#main-wrapper,
.content-body {
    overflow: visible !important;
}

/* Send Message page-specific layout */
.send-message-layout {
    display: flex;
    gap: 1.5rem;
    align-items: flex-start;
}
.send-message-left {
    flex: 1 1 auto;
    min-width: 0;
}
.send-message-right {
    flex: 0 0 460px;
    width: 460px;
    max-width: 100%;
    position: sticky;
    top: 90px;
    align-self: flex-start;
}
.send-message-right .card {
    max-height: calc(100vh - 120px);
    overflow: auto;
}
@media (max-width: 991.98px) {
    .send-message-layout {
        flex-direction: column;
    }
    .send-message-left,
    .send-message-right {
        flex: 0 0 100%;
        width: 100%;
        max-width: 100%;
        position: static;
    }
    .send-message-right .card {
        max-height: none;
        overflow: visible;
    }
}

/* Send Message page-specific density adjustments */
@media (max-width: 1440px) {
    .send-message-layout {
        gap: 1rem;
    }
    .send-message-right {
        flex: 0 0 400px;
        width: 400px;
    }
    .send-message-right .card {
        max-height: calc(100vh - 100px);
    }
    #mainPreviewContainer {
        transform: scale(0.75);
        margin-bottom: -100px;
    }
}

@media (max-width: 1366px) {
    .send-message-right {
        flex: 0 0 360px;
        width: 360px;
    }
    #mainPreviewContainer {
        transform: scale(0.65);
        margin-bottom: -120px;
    }
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="send-message-layout-wrap">
        <div class="row page-titles">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('messages') }}">Messages</a></li>
                <li class="breadcrumb-item active">Send Message</li>
            </ol>
        </div>
        
        <div class="send-message-layout">
        <div class="send-message-left">
            <div class="card mb-3">
                <div class="card-body p-4">
                    <h6 class="mb-3">Campaign Details</h6>
                    <input type="text" class="form-control" id="campaignName" placeholder="Campaign name (auto-generated if blank)" maxlength="100">
                </div>
            </div>
            
            <div class="card mb-3">
                <div class="card-body p-4">
                    <h6 class="mb-3">Channel & Sender</h6>
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
            
            <div class="card mb-3">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h6 class="mb-2">Recipients</h6>
                            <p class="text-muted mb-0">Add recipients via manual entry, CSV upload, or from your contact book</p>
                        </div>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" id="ukNumbersOnly" checked onchange="toggleUkMode()">
                            <label class="form-check-label" for="ukNumbersOnly">UK only</label>
                        </div>
                    </div>
                    
                    <label class="form-label mb-2">Enter mobile numbers</label>
                    <textarea class="form-control mb-3" id="manualNumbers" rows="4" placeholder="Paste or type numbers separated by commas, spaces, or new lines" onblur="validateManualNumbers()"></textarea>
                    
                    <div class="d-none mb-3" id="manualValidation">
                        <span class="text-success"><i class="fas fa-check-circle me-1"></i><span id="manualValid">0</span> valid</span>
                        <span class="text-danger ms-2"><i class="fas fa-times-circle me-1"></i><span id="manualInvalid">0</span> invalid</span>
                        <a href="#" class="ms-2 d-none" id="manualInvalidLink" onclick="showInvalidNumbers('manual')">View</a>
                    </div>
                    
                    <div class="d-flex gap-2 mb-4">
                        <button type="button" class="btn btn-outline-primary" id="uploadCsvBtn" onclick="triggerFileUpload()">
                            <i class="fas fa-upload me-1"></i>Upload File
                        </button>
                        <button type="button" class="btn btn-outline-primary" onclick="openContactBookModal()">
                            <i class="fas fa-users me-1"></i>Select from Contact Book
                        </button>
                        
                    </div>
                    
                    <div id="uploadedFilesContainer" class="mb-3"></div>
                    <div class="d-none mb-3" id="fileProcessingIndicator">
                        <div class="progress mb-2" style="height: 6px;"><div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 100%; background-color: #886CC0;"></div></div>
                        <span class="text-muted">Processing file...</span>
                    </div>
                    
                    <div class="d-none mb-3" id="contactBookSelection">
                        <div id="contactBookChips"></div>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                        <span class="fw-medium">Total Recipients</span>
                        <span class="badge" id="recipientCount" style="background-color: #f0ebf8; color: #6b5b95;">0</span>
                    </div>
                </div>
            </div>
            
            <div class="card mb-3">
                <div class="card-body p-4">
                    <h6 class="mb-3">Content</h6>
                    
                    <div class="row align-items-center mb-3">
                        <div class="col-md-6 col-lg-5 mb-2 mb-md-0">
                            <div class="d-flex align-items-center gap-2">
                                <label class="form-label mb-0 text-nowrap">Template</label>
                                <select class="form-select form-select-sm" id="templateSelector" onchange="applySelectedTemplate()">
                                    <option value="">-- None --</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-7 text-md-end">
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="openAiAssistant()">
                                <i class="fas fa-magic me-1"></i>Improve with AI
                            </button>
                        </div>
                    </div>
                    
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
                    
                    <div class="d-none mb-2" id="rcsTextHelper">
                        <div class="alert py-2 mb-0" style="background-color: #f0ebf8; color: #6b5b95; border: none;">
                            <i class="fas fa-info-circle me-1"></i>
                            <span id="rcsHelperText">Messages over 160 characters will be automatically sent as a single RCS message where supported.</span>
                        </div>
                    </div>
                    
                    <div class="border-top pt-3 mb-3">
                        <div class="d-flex flex-wrap gap-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="includeTrackableLink" onchange="toggleTrackableLinkModal()">
                                <label class="form-check-label" for="includeTrackableLink">Include trackable link</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="messageExpiry" onchange="toggleMessageExpiryModal()">
                                <label class="form-check-label" for="messageExpiry">Message expiry</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="scheduleRules" onchange="toggleScheduleRulesModal()">
                                <label class="form-check-label" for="scheduleRules">Schedule & sending rules</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-none mb-2" id="trackableLinkSummary">
                        <div class="alert alert-secondary py-2 mb-0">
                            <i class="fas fa-link me-2"></i>Trackable link: <strong id="trackableLinkDomain">qsms.uk</strong>
                            <a href="#" class="ms-2" onclick="openTrackableLinkModal(); return false;">Edit</a>
                        </div>
                    </div>
                    
                    <div class="d-none mb-2" id="messageExpirySummary">
                        <div class="alert alert-secondary py-2 mb-0">
                            <i class="fas fa-hourglass-half me-2"></i>Message expiry: <strong id="messageExpiryValue">24 Hours</strong>
                            <a href="#" class="ms-2" onclick="openMessageExpiryModal(); return false;">Edit</a>
                        </div>
                    </div>
                    
                    <div class="d-none mb-2" id="scheduleSummary">
                        <div class="alert alert-secondary py-2 mb-0">
                            <i class="fas fa-clock me-2"></i><span id="scheduleSummaryText">Scheduled for: --</span>
                            <a href="#" class="ms-2" onclick="openScheduleRulesModal(); return false;">Edit</a>
                        </div>
                    </div>
                    
                    <div class="d-none mt-3" id="rcsContentSection">
                        <div class="border rounded p-3 text-center" style="background-color: rgba(136, 108, 192, 0.1); border-color: rgba(136, 108, 192, 0.2) !important;">
                            <i class="fas fa-image fa-2x text-primary mb-2"></i>
                            <h6 class="mb-2">Rich RCS Card</h6>
                            <p class="text-muted small mb-3">Create rich media cards with images, descriptions, and interactive buttons.</p>
                            <button type="button" class="btn btn-primary" onclick="openRcsWizard()">
                                <i class="fas fa-magic me-1"></i>Create RCS Message
                            </button>
                            <div class="d-none mt-3" id="rcsConfiguredSummary">
                                <div class="alert alert-primary py-2 mb-0">
                                    <i class="fas fa-check-circle me-1"></i>
                                    <span id="rcsConfiguredText">RCS content configured</span>
                                    <a href="#" class="ms-2" onclick="openRcsWizard(); return false;">Edit</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mb-3">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0">Opt-out Management</h6>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" id="enableOptoutManagement" onchange="toggleOptoutManagement()">
                            <label class="form-check-label" for="enableOptoutManagement">Enable</label>
                        </div>
                    </div>
                    
                    <div class="d-none" id="optoutManagementSection">
                        <div class="mb-3">
                            <label class="form-label">Opt-out list <span class="text-muted">(optional)</span></label>
                            <select class="form-select" id="optoutListSelect">
                                <option value="" selected>No list selected</option>
                                @foreach($opt_out_lists as $list)
                                <option value="{{ $list['id'] }}">{{ $list['name'] }} ({{ number_format($list['count']) }})</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Select a list to exclude numbers. If no list is selected, you must enable an opt-out method below.</small>
                        </div>
                        
                        <div class="border-top pt-3 mb-3">
                            <h6 class="mb-3">Opt-out Options</h6>
                            
                            @if(count($virtual_numbers) > 0)
                            <div class="mb-3 p-3 border rounded">
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" id="enableReplyOptout" onchange="toggleReplyOptout()">
                                    <label class="form-check-label fw-medium" for="enableReplyOptout">Enable reply-to-opt-out</label>
                                </div>
                                <div class="d-none ps-3" id="replyOptoutConfig">
                                    <div class="mb-2">
                                        <label class="form-label">Virtual Number</label>
                                        <select class="form-select form-select-sm" id="replyVirtualNumber">
                                            <option value="">-- Select virtual number --</option>
                                            @foreach($virtual_numbers as $vn)
                                            <option value="{{ $vn['id'] }}" data-number="{{ $vn['number'] }}">{{ $vn['number'] }} ({{ $vn['label'] }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">Opt-out Text</label>
                                        <div class="input-group input-group-sm">
                                            <input type="text" class="form-control" id="replyOptoutText" value="Opt-out: Reply STOP to @{{number}}" placeholder="e.g. Reply STOP to @{{number}}">
                                            <button type="button" class="btn btn-outline-primary" onclick="addOptoutToMessage('reply')" title="Append to message">Add to message content</button>
                                        </div>
                                        <small class="text-muted">Use @{{number}} to insert the virtual number.</small>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">Store opt-outs in</label>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="replyOptoutTarget" id="replyOptoutExisting" value="existing" checked>
                                            <label class="form-check-label" for="replyOptoutExisting">Existing opt-out list</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="replyOptoutTarget" id="replyOptoutNew" value="new">
                                            <label class="form-check-label" for="replyOptoutNew">Create new opt-out list</label>
                                        </div>
                                        <div class="d-none mt-2" id="replyNewListFields">
                                            <input type="text" class="form-control form-control-sm mb-1" id="replyNewListName" placeholder="List name (required)">
                                            <input type="text" class="form-control form-control-sm" id="replyNewListDesc" placeholder="Description (optional)">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                            
                            <div class="mb-3 p-3 border rounded">
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" id="enableUrlOptout" onchange="toggleUrlOptout()">
                                    <label class="form-check-label fw-medium" for="enableUrlOptout">Enable click-to-opt-out</label>
                                </div>
                                <div class="d-none ps-3" id="urlOptoutConfig">
                                    <div class="mb-2">
                                        <label class="form-label">URL Domain</label>
                                        <select class="form-select form-select-sm" id="urlOptoutDomain">
                                            @foreach($optout_domains as $domain)
                                            <option value="{{ $domain['id'] }}" {{ $domain['is_default'] ? 'selected' : '' }}>{{ $domain['domain'] }}{{ $domain['is_default'] ? ' (default)' : '' }}</option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted">A unique URL will be generated per message.</small>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">Opt-out Text</label>
                                        <div class="input-group input-group-sm">
                                            <input type="text" class="form-control" id="urlOptoutText" value="Opt-out: Click @{{unique_url}}" placeholder="e.g. Click @{{unique_url}}">
                                            <button type="button" class="btn btn-outline-primary" onclick="addOptoutToMessage('url')" title="Append to message">Add to message content</button>
                                        </div>
                                        <small class="text-muted">Use @{{unique_url}} to insert the tracking URL.</small>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">Store opt-outs in</label>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="urlOptoutTarget" id="urlOptoutExisting" value="existing" checked>
                                            <label class="form-check-label" for="urlOptoutExisting">Existing opt-out list</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="urlOptoutTarget" id="urlOptoutNew" value="new">
                                            <label class="form-check-label" for="urlOptoutNew">Create new opt-out list</label>
                                        </div>
                                        <div class="d-none mt-2" id="urlNewListFields">
                                            <input type="text" class="form-control form-control-sm mb-1" id="urlNewListName" placeholder="List name (required)">
                                            <input type="text" class="form-control form-control-sm" id="urlNewListDesc" placeholder="Description (optional)">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-none" id="optoutValidationError">
                            <div class="alert alert-danger py-2 mb-0">
                                <i class="fas fa-exclamation-circle me-1"></i>
                                <span id="optoutValidationMessage">At least one opt-out mechanism must be configured.</span>
                            </div>
                        </div>
                    </div>
                    
                    <div id="optoutDisabledMessage">
                        <p class="text-muted mb-0"><small>No opt-out logic will be applied. Enable to configure opt-out options.</small></p>
                    </div>
                </div>
            </div>
            
            <div class="card mb-3">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <button type="button" class="btn btn-outline-secondary" onclick="saveDraft()"><i class="fas fa-save me-1"></i>Save Draft</button>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-primary" onclick="openTestMessageModal()"><i class="fas fa-mobile-alt me-1"></i>Test Message</button>
                            <button type="button" class="btn btn-primary" onclick="continueToConfirmation()">Continue <i class="fas fa-arrow-right ms-1"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="send-message-right">
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
                            <div class="col-4"><small class="text-muted d-block mb-1">Channel</small><strong id="previewChannel" class="small">SMS</strong></div>
                            <div class="col-4"><small class="text-muted d-block mb-1">Recipients</small><strong id="previewRecipients" class="small">0</strong></div>
                            <div class="col-4"><small class="text-muted d-block mb-1">Cost</small><strong id="previewCost" class="small">&pound;0.00</strong></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
</div>

<div class="modal fade" id="contactBookModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-fullscreen-lg-down">
        <div class="modal-content" style="border-radius: 0.75rem; border: none; box-shadow: 0 8px 30px rgba(0,0,0,0.12);">
            <div class="modal-header py-3 px-4" style="border-bottom: 1px solid #f0ebf8;">
                <h5 class="modal-title" style="font-weight: 600; color: #2c2c2c;"><i class="fas fa-address-book me-2" style="color: #886CC0;"></i>Select from Contact Book</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <ul class="nav nav-tabs px-4 pt-3 mb-0" style="border-bottom: none;">
                    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#cbContacts" style="font-size: 13px; font-weight: 500; padding: 0.5rem 1rem;">Contacts</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#cbLists" style="font-size: 13px; font-weight: 500; padding: 0.5rem 1rem;">Lists</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#cbDynamicLists" style="font-size: 13px; font-weight: 500; padding: 0.5rem 1rem;">Dynamic Lists</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#cbTags" style="font-size: 13px; font-weight: 500; padding: 0.5rem 1rem;">Tags</button></li>
                </ul>
                <div class="tab-content px-4 pt-3">
                    <div class="tab-pane fade show active" id="cbContacts">
                        <div class="row mb-3">
                            <div class="col-md-7">
                                <div class="input-group">
                                    <span class="input-group-text" style="background: #f8f7fc; border-color: #e6e6e6; border-radius: 0.625rem 0 0 0.625rem;"><i class="fas fa-search" style="color: #a1a1a1;"></i></span>
                                    <input type="text" class="form-control" id="cbContactSearch" placeholder="Search names, numbers, tags, custom fields..." oninput="filterContacts()" style="border-color: #e6e6e6; border-radius: 0 0.625rem 0.625rem 0; font-size: 13px;">
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="d-flex gap-2 align-items-center">
                                    <select class="form-select" id="cbContactSort" onchange="sortContacts()" style="border-color: #e6e6e6; border-radius: 0.625rem; font-size: 13px;">
                                        <option value="recent">Most recently contacted</option>
                                        <option value="added">Most recently added</option>
                                        <option value="name_asc">Name A-Z</option>
                                        <option value="name_desc">Name Z-A</option>
                                    </select>
                                    <button type="button" class="btn btn-outline-secondary" onclick="toggleContactFilters()" style="border-color: #e6e6e6; border-radius: 0.625rem; color: #886CC0;"><i class="fas fa-filter"></i></button>
                                </div>
                            </div>
                        </div>
                        <div class="d-none mb-3 p-3 rounded" id="cbContactFilters" style="font-size: 13px; background: #f8f7fc;">
                            <div class="row">
                                <div class="col-md-3"><label class="form-label mb-1" style="font-weight: 500;">Tags</label><select class="form-select form-select-sm" id="cbFilterTags" style="border-radius: 0.625rem;"><option value="">All tags</option></select></div>
                                <div class="col-md-3"><label class="form-label mb-1" style="font-weight: 500;">Has Mobile</label><select class="form-select form-select-sm" id="cbFilterMobile" style="border-radius: 0.625rem;"><option value="">Any</option><option value="yes">Yes</option><option value="no">No</option></select></div>
                                <div class="col-md-3"><label class="form-label mb-1" style="font-weight: 500;">Opt-out Status</label><select class="form-select form-select-sm" id="cbFilterOptout" style="border-radius: 0.625rem;"><option value="exclude">Exclude opted-out</option><option value="include">Include all</option></select></div>
                                <div class="col-md-3 d-flex align-items-end"><button class="btn btn-link btn-sm" onclick="clearContactFilters()" style="color: #886CC0;">Clear filters</button></div>
                            </div>
                        </div>
                        <div class="table-responsive" style="max-height: 380px; overflow-y: auto;">
                            <table class="table table-hover mb-0 cb-table" style="font-size: 13px;">
                                <thead style="position: sticky; top: 0; z-index: 2; background: #fff;">
                                    <tr style="border-bottom: 2px solid #f0ebf8;">
                                        <th style="width: 40px; padding: 12px 8px;"><input type="checkbox" class="form-check-input" id="cbSelectAllContacts" onchange="toggleAllContacts()"></th>
                                        <th style="padding: 12px 8px; color: #6c757d; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Name</th>
                                        <th style="padding: 12px 8px; color: #6c757d; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Mobile</th>
                                        <th style="padding: 12px 8px; color: #6c757d; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Tags</th>
                                    </tr>
                                </thead>
                                <tbody id="cbContactsTable">
                                    <tr><td colspan="4" class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin me-2"></i>Loading contacts...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="cbLists">
                        <div class="row mb-3">
                            <div class="col-md-7">
                                <div class="input-group">
                                    <span class="input-group-text" style="background: #f8f7fc; border-color: #e6e6e6; border-radius: 0.625rem 0 0 0.625rem;"><i class="fas fa-search" style="color: #a1a1a1;"></i></span>
                                    <input type="text" class="form-control" id="cbListSearch" placeholder="Search lists..." oninput="filterLists()" style="border-color: #e6e6e6; border-radius: 0 0.625rem 0.625rem 0; font-size: 13px;">
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="d-flex gap-2 align-items-center">
                                    <select class="form-select" id="cbListSort" onchange="sortLists()" style="border-color: #e6e6e6; border-radius: 0.625rem; font-size: 13px;">
                                        <option value="name_asc">Name A-Z</option>
                                        <option value="name_desc">Name Z-A</option>
                                        <option value="count_desc">Most contacts</option>
                                        <option value="count_asc">Fewest contacts</option>
                                        <option value="updated">Recently updated</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive" style="max-height: 380px; overflow-y: auto;">
                            <table class="table table-hover mb-0 cb-table" style="font-size: 13px;">
                                <thead style="position: sticky; top: 0; z-index: 2; background: #fff;">
                                    <tr style="border-bottom: 2px solid #f0ebf8;">
                                        <th style="width: 40px; padding: 12px 8px;"></th>
                                        <th style="padding: 12px 8px; color: #6c757d; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">List Name</th>
                                        <th style="padding: 12px 8px; color: #6c757d; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Contacts</th>
                                        <th style="padding: 12px 8px; color: #6c757d; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Last Updated</th>
                                    </tr>
                                </thead>
                                <tbody id="cbListsTable">
                                    <tr><td colspan="4" class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin me-2"></i>Loading lists...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="cbDynamicLists">
                        <div class="row mb-3">
                            <div class="col-md-7">
                                <div class="input-group">
                                    <span class="input-group-text" style="background: #f8f7fc; border-color: #e6e6e6; border-radius: 0.625rem 0 0 0.625rem;"><i class="fas fa-search" style="color: #a1a1a1;"></i></span>
                                    <input type="text" class="form-control" id="cbDynamicSearch" placeholder="Search dynamic lists..." oninput="filterDynamicLists()" style="border-color: #e6e6e6; border-radius: 0 0.625rem 0.625rem 0; font-size: 13px;">
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="d-flex gap-2 align-items-center">
                                    <select class="form-select" id="cbDynamicSort" onchange="sortDynamicLists()" style="border-color: #e6e6e6; border-radius: 0.625rem; font-size: 13px;">
                                        <option value="name_asc">Name A-Z</option>
                                        <option value="name_desc">Name Z-A</option>
                                        <option value="count_desc">Most contacts</option>
                                        <option value="count_asc">Fewest contacts</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive" style="max-height: 380px; overflow-y: auto;">
                            <table class="table table-hover mb-0 cb-table" style="font-size: 13px;">
                                <thead style="position: sticky; top: 0; z-index: 2; background: #fff;">
                                    <tr style="border-bottom: 2px solid #f0ebf8;">
                                        <th style="width: 40px; padding: 12px 8px;"></th>
                                        <th style="padding: 12px 8px; color: #6c757d; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">List Name</th>
                                        <th style="padding: 12px 8px; color: #6c757d; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Rules</th>
                                        <th style="padding: 12px 8px; color: #6c757d; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Contacts</th>
                                        <th style="padding: 12px 8px; color: #6c757d; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Last Evaluated</th>
                                    </tr>
                                </thead>
                                <tbody id="cbDynamicListsTable">
                                    <tr><td colspan="5" class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin me-2"></i>Loading dynamic lists...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="cbTags">
                        <div class="row mb-3">
                            <div class="col-md-7">
                                <div class="input-group">
                                    <span class="input-group-text" style="background: #f8f7fc; border-color: #e6e6e6; border-radius: 0.625rem 0 0 0.625rem;"><i class="fas fa-search" style="color: #a1a1a1;"></i></span>
                                    <input type="text" class="form-control" id="cbTagSearch" placeholder="Search tags..." oninput="filterTagsList()" style="border-color: #e6e6e6; border-radius: 0 0.625rem 0.625rem 0; font-size: 13px;">
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="d-flex gap-2 align-items-center">
                                    <select class="form-select" id="cbTagSort" onchange="sortTagsList()" style="border-color: #e6e6e6; border-radius: 0.625rem; font-size: 13px;">
                                        <option value="name_asc">Name A-Z</option>
                                        <option value="name_desc">Name Z-A</option>
                                        <option value="count_desc">Most contacts</option>
                                        <option value="count_asc">Fewest contacts</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive" style="max-height: 380px; overflow-y: auto;">
                            <table class="table table-hover mb-0 cb-table" style="font-size: 13px;">
                                <thead style="position: sticky; top: 0; z-index: 2; background: #fff;">
                                    <tr style="border-bottom: 2px solid #f0ebf8;">
                                        <th style="width: 40px; padding: 12px 8px;"></th>
                                        <th style="padding: 12px 8px; color: #6c757d; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Tag</th>
                                        <th style="padding: 12px 8px; color: #6c757d; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Contacts</th>
                                    </tr>
                                </thead>
                                <tbody id="cbTagsTable">
                                    <tr><td colspan="3" class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin me-2"></i>Loading tags...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="px-4 py-2 mt-2" style="font-size: 13px; border-top: 1px solid #f0ebf8; background: #faf9fd;">
                    <strong style="color: #6b5b95;">Selected:</strong> <span id="cbSelectionSummary" style="color: #555;">0 contacts, 0 lists, 0 dynamic lists, 0 tags</span>
                </div>
            </div>
            <div class="modal-footer py-3 px-4" style="border-top: 1px solid #f0ebf8;">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" style="border-radius: 0.625rem;">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="confirmContactBookSelection()" style="border-radius: 0.625rem; background-color: #886CC0; border-color: #886CC0;"><i class="fas fa-plus me-1"></i>Add to Campaign</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="csvUploadModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius: 0.75rem; border: none; box-shadow: 0 8px 30px rgba(0,0,0,0.12);">
            <div class="modal-header py-3 px-4" style="border-bottom: 1px solid #f0ebf8;">
                <h5 class="modal-title" style="font-weight: 600; color: #2c2c2c;"><i class="fas fa-file-import me-2" style="color: #886CC0;"></i>Upload Recipients</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4">
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-3">
                        <div class="text-center flex-fill">
                            <div class="rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background-color: #886CC0; color: #fff;" id="csvStepCircle1">1</div>
                            <div class="small mt-1">Upload</div>
                        </div>
                        <div class="text-center flex-fill">
                            <div class="rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background-color: #fff; color: #886CC0; border: 2px solid #886CC0;" id="csvStepCircle2">2</div>
                            <div class="small mt-1">Map Columns</div>
                        </div>
                        <div class="text-center flex-fill">
                            <div class="rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background-color: #fff; color: #886CC0; border: 2px solid #886CC0;" id="csvStepCircle3">3</div>
                            <div class="small mt-1">Review</div>
                        </div>
                    </div>
                </div>

                <div id="csvStep1">
                    <h6 class="mb-3">Step 1: Upload File</h6>
                    <div class="border rounded p-4 text-center" id="csvDropZone" style="border-style: dashed !important; background-color: #f0ebf8; border-color: #886CC0 !important; cursor: pointer;">
                        <i class="fas fa-cloud-upload-alt fa-3x mb-3" style="color: #886CC0;"></i>
                        <p class="mb-2">Drag and drop your file here, or click to browse</p>
                        <input type="file" class="d-none" id="csvFileInput" accept=".csv,.xlsx,.xls">
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="document.getElementById('csvFileInput').click()" style="border-color: #886CC0; color: #886CC0;">
                            <i class="fas fa-folder-open me-1"></i> Browse Files
                        </button>
                        <p class="text-muted small mt-2 mb-0">Accepted formats: CSV, Excel (.xlsx)</p>
                    </div>
                    <div id="csvSelectedFileInfo" class="d-none mt-3">
                        <div class="d-flex align-items-center p-3 rounded" style="background-color: #f0ebf8;">
                            <i class="fas fa-file-alt fa-2x me-3" style="color: #886CC0;"></i>
                            <div>
                                <strong id="csvSelectedFileName" style="color: #2c2c2c;">filename.csv</strong>
                                <div class="small text-muted" id="csvSelectedFileSize">123 KB</div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger ms-auto" onclick="csvClearFile()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="csvHasHeaders" checked>
                            <label class="form-check-label" for="csvHasHeaders">First row contains column headings</label>
                        </div>
                    </div>
                </div>

                <div id="csvStep2" class="d-none">
                    <h6 class="mb-3">Step 2: Map Columns</h6>
                    <div class="small p-3 rounded" style="background-color: #f0ebf8; color: #6c5ce7;">
                        <i class="fas fa-info-circle me-1"></i>
                        Map your file columns to the required fields. <strong style="color: #886CC0;">Mobile Number</strong> <span class="text-dark">is required.</span>
                    </div>
                    <div class="table-responsive mt-3">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Detected Column</th>
                                    <th>Map To Field</th>
                                    <th>Sample Data</th>
                                </tr>
                            </thead>
                            <tbody id="csvColumnMappingBody">
                            </tbody>
                        </table>
                    </div>
                    <input type="hidden" id="csvExcelCorrectionApplied" value="">
                    <div id="csvNormalisationWarning" class="d-none p-3 rounded mt-2" style="background-color: #f0ebf8;">
                        <div id="csvNormalisationContent">
                            <i class="fas fa-exclamation-triangle me-2" style="color: #886CC0;"></i>
                            <strong style="color: #886CC0;">UK Number Normalisation</strong>
                            <p class="mb-2 mt-2 text-dark" id="csvNormalisationDetail">We've detected mixed mobile number formats in your file.</p>
                            <p class="mb-2 text-dark">Should we normalise all numbers to international format (e.g. <code>447712345678</code>)?</p>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-sm text-white" style="background-color: #886CC0;" onclick="csvSetNormalisation(true)">
                                    <i class="fas fa-check me-1"></i> Yes, normalise to UK format
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="csvSetNormalisation(false)">
                                    <i class="fas fa-times me-1"></i> No, leave as-is
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="csvStep3" class="d-none">
                    <h6 class="mb-3">Step 3: Review & Validate</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <div class="card border-0" style="background-color: #f0ebf8;">
                                <div class="card-body text-center py-3">
                                    <div class="h3 mb-0 text-dark" id="csvStatTotalRows">0</div>
                                    <div class="small text-dark">Total Rows</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-0" style="background-color: #f0ebf8;">
                                <div class="card-body text-center py-3">
                                    <div class="h3 mb-0 text-dark" id="csvStatUniqueNumbers">0</div>
                                    <div class="small text-dark">Unique Numbers</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-0" style="background-color: #f0ebf8;">
                                <div class="card-body text-center py-3">
                                    <div class="h3 mb-0 text-dark" id="csvStatValidNumbers">0</div>
                                    <div class="small text-dark">Valid Numbers</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-0" style="background-color: #f0ebf8;">
                                <div class="card-body text-center py-3">
                                    <div class="h3 mb-0 text-dark" id="csvStatInvalidNumbers">0</div>
                                    <div class="small text-dark">Invalid Numbers</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="csvImportIndicators" class="mb-3"></div>
                    <div id="csvInvalidRowsSection" class="d-none">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0"><i class="fas fa-exclamation-circle text-danger me-2"></i>Invalid Rows</h6>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="csvDownloadInvalidRows()">
                                <i class="fas fa-download me-1"></i> Download
                            </button>
                        </div>
                        <div class="table-responsive" style="max-height: 200px; overflow-y: auto;">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="table-light sticky-top">
                                    <tr><th>Row</th><th>Original Value</th><th>Reason</th></tr>
                                </thead>
                                <tbody id="csvInvalidRowsBody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-3 px-4" style="border-top: 1px solid #f0ebf8;">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" id="csvCancelBtn" style="border-radius: 0.625rem;">Cancel</button>
                <button type="button" class="btn btn-outline-primary d-none" id="csvBackBtn" onclick="csvPrevStep()" style="border-radius: 0.625rem; border-color: #886CC0; color: #886CC0;">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </button>
                <button type="button" class="btn btn-primary" id="csvNextBtn" onclick="csvNextStep()" disabled style="border-radius: 0.625rem; background-color: #886CC0; border-color: #886CC0;">
                    Next <i class="fas fa-arrow-right ms-1"></i>
                </button>
                <button type="button" class="btn btn-success d-none" id="csvConfirmBtn" onclick="csvConfirmImport()" style="border-radius: 0.625rem;">
                    <i class="fas fa-check me-1"></i> Confirm & Import
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="ukModeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h5 class="modal-title"><i class="fas fa-globe me-2"></i>International Numbers</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="font-size: 13px;">
                <p>When <strong>UK only mode is OFF</strong>, numbers will be processed as follows:</p>
                <ul>
                    <li>Numbers with + prefix are used as-is (e.g., +33, +1, +49)</li>
                    <li>UK format numbers (07xxx) need special handling</li>
                </ul>
                <div class="alert alert-warning py-2">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="convert07ToUk" checked>
                        <label class="form-check-label" for="convert07ToUk"><strong>Convert all 07xxx numbers to UK format (+447xxx)</strong></label>
                    </div>
                    <small class="text-muted d-block mt-1">Warning: If international recipients (e.g., Russia, Kazakhstan) use 07xxx format, they may be incorrectly targeted as UK numbers.</small>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="cancelUkModeChange()">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" onclick="confirmUkModeChange()">Confirm</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="invalidNumbersModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h5 class="modal-title"><i class="fas fa-times-circle text-danger me-2"></i>Invalid Numbers</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive" style="max-height: 300px;">
                    <table class="table table-sm" style="font-size: 11px;">
                        <thead class="table-light"><tr><th>#</th><th>Original Value</th><th>Reason</th></tr></thead>
                        <tbody id="invalidNumbersTable"></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="downloadInvalidNumbers()"><i class="fas fa-download me-1"></i>Download CSV</button>
                <button type="button" class="btn btn-primary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- TODO: Backend Integration Required
    - Contact Book Fields: Fetch available fields from GET /api/contacts/fields
    - Custom Fields: Fetch user-defined custom fields from GET /api/custom-fields
    - CSV Columns: Populated dynamically from file upload column mapping (already implemented client-side)
--}}
<div class="modal fade" id="personalisationModal" tabindex="-1" style="z-index: 1060;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header py-3">
                <h5 class="modal-title"><i class="fas fa-user-tag me-2"></i>Insert Personalisation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-3">Click a placeholder to insert it at the cursor position in your message.</p>
                <div class="mb-3">
                    <h6 class="text-muted mb-2">Contact Book Fields</h6>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="insertPlaceholder('first_name')">@{{first_name}}</button>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="insertPlaceholder('last_name')">@{{last_name}}</button>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="insertPlaceholder('full_name')">@{{full_name}}</button>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="insertPlaceholder('mobile_number')">@{{mobile_number}}</button>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="insertPlaceholder('email')">@{{email}}</button>
                    </div>
                </div>
                <div class="mb-3">
                    <h6 class="text-muted mb-2">Custom Fields</h6>
                    <p class="text-muted small mb-0" id="noCustomFieldsHint">Upload a CSV/Excel file with extra columns to see custom field placeholders here.</p>
                </div>
                <div class="mb-3" id="csvFieldsSection" style="display: none;">
                    <h6 class="text-muted mb-2">CSV/Excel Columns</h6>
                    <div class="d-flex flex-wrap gap-2" id="csvFieldButtons"></div>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="emojiPickerModal" tabindex="-1" style="z-index: 1060;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header py-3">
                <h5 class="modal-title"><i class="fas fa-smile me-2"></i>Insert Emoji</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning py-2 mb-3">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    Emojis switch the message to Unicode encoding, reducing characters per segment.
                </div>
                <div class="mb-3">
                    <h6 class="text-muted mb-2">Commonly Used</h6>
                    <div class="d-flex flex-wrap gap-1">
                        <button type="button" class="btn btn-light btn-sm emoji-btn" onclick="insertEmoji('😊')">😊</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" onclick="insertEmoji('👍')">👍</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" onclick="insertEmoji('❤️')">❤️</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" onclick="insertEmoji('🎉')">🎉</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" onclick="insertEmoji('✅')">✅</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" onclick="insertEmoji('⭐')">⭐</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" onclick="insertEmoji('📱')">📱</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" onclick="insertEmoji('📞')">📞</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" onclick="insertEmoji('📧')">📧</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" onclick="insertEmoji('📅')">📅</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" onclick="insertEmoji('⏰')">⏰</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" onclick="insertEmoji('💊')">💊</button>
                    </div>
                </div>
                <div class="mb-3">
                    <h6 class="text-muted mb-2">Healthcare</h6>
                    <div class="d-flex flex-wrap gap-1">
                        <button type="button" class="btn btn-light btn-sm emoji-btn" onclick="insertEmoji('🏥')">🏥</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" onclick="insertEmoji('👨‍⚕️')">👨‍⚕️</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" onclick="insertEmoji('👩‍⚕️')">👩‍⚕️</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" onclick="insertEmoji('💉')">💉</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" onclick="insertEmoji('🩺')">🩺</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" onclick="insertEmoji('🩹')">🩹</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" onclick="insertEmoji('💪')">💪</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" onclick="insertEmoji('🧘')">🧘</button>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="templateModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header py-3">
                <h5 class="modal-title"><i class="fas fa-file-alt me-2"></i>Select Template</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="input-group mb-3">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control" id="templateSearch" placeholder="Search templates..." oninput="filterTemplates()">
                </div>
                <div class="list-group" id="templateList">
                    @foreach($templates as $template)
                    <a href="#" class="list-group-item list-group-item-action" onclick="selectTemplate('{{ $template['id'] }}', '{{ addslashes($template['content']) }}')">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-1">{{ $template['name'] }}</h6>
                            <span class="badge bg-secondary">SMS</span>
                        </div>
                        <p class="mb-0 text-muted small">{{ Str::limit($template['content'], 100) }}</p>
                    </a>
                    @endforeach
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="trackableLinkModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header py-3">
                <h5 class="modal-title"><i class="fas fa-link me-2"></i>Trackable Link Settings</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-3">A unique shortened URL will be generated for each recipient to track clicks.</p>
                <div class="mb-3">
                    <label class="form-label">Short URL Domain</label>
                    <select class="form-select" id="shortUrlDomain">
                        <option value="qsms.uk" selected>qsms.uk (default)</option>
                        <option value="custom1.co.uk">custom1.co.uk</option>
                        <option value="custom2.com">custom2.com</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Destination URL</label>
                    <input type="url" class="form-control" id="destinationUrl" placeholder="https://example.com/landing-page">
                </div>
                <div class="mb-3">
                    <label class="form-label">Insert Link As</label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="linkInsertMethod" id="linkAtCursor" value="cursor" checked>
                        <label class="form-check-label" for="linkAtCursor">Insert at cursor position</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="linkInsertMethod" id="linkAsPlaceholder" value="placeholder">
                        <label class="form-check-label" for="linkAsPlaceholder">Use placeholder @{{trackingUrl}}</label>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="confirmTrackableLink()">Apply</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="scheduleRulesModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header py-3">
                <h5 class="modal-title"><i class="fas fa-clock me-2"></i>Schedule & Sending Rules</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-4">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="scheduleToggle" onchange="toggleScheduleFields()">
                        <label class="form-check-label fw-medium" for="scheduleToggle">Schedule this campaign</label>
                    </div>
                    <div class="d-none ps-4" id="scheduleFields">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Send Date</label>
                                <input type="date" class="form-control" id="scheduleDate">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Send Time</label>
                                <input type="time" class="form-control" id="scheduleTime">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="border-top pt-4 mb-4">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="unsociableToggle" onchange="toggleUnsociableFields()">
                        <label class="form-check-label fw-medium" for="unsociableToggle">Define unsociable hours</label>
                    </div>
                    <div class="d-none ps-4" id="unsociableFields">
                        <p class="text-muted small mb-3">Messages will not be sent during these hours. They will be queued and sent at the next allowable time.</p>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Do not send before</label>
                                <input type="time" class="form-control" id="unsociableFrom" value="08:00">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Do not send after</label>
                                <input type="time" class="form-control" id="unsociableTo" value="20:00">
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="confirmScheduleRules()">Apply</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="messageExpiryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header py-3">
                <h5 class="modal-title"><i class="fas fa-hourglass-half me-2"></i>Message Expiry</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-3">Define how long the platform should attempt delivery before expiring a message.</p>
                <div class="mb-3">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="validityToggle" onchange="toggleValidityFields()" checked>
                        <label class="form-check-label fw-medium" for="validityToggle">Set message validity period</label>
                    </div>
                    <div class="ps-4" id="validityFields">
                        <p class="text-muted small mb-3">If a message cannot be delivered within this period, it will expire and no further attempts will be made.</p>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Validity Duration</label>
                                <input type="number" class="form-control" id="validityDuration" value="24" min="1">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Unit</label>
                                <select class="form-select" id="validityUnit">
                                    <option value="minutes">Minutes</option>
                                    <option value="hours" selected>Hours</option>
                                    <option value="days">Days</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="py-2 mb-0 rounded" style="background-color: #f0ebf8; color: #6b5b95; padding: 12px;">
                    <i class="fas fa-info-circle me-1"></i>
                    <small>When off, operator/platform defaults apply (typically 24-72 hours for SMS, configurable for RCS).</small>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="confirmMessageExpiry()">Apply</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="validationErrorsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title text-danger"><i class="fas fa-exclamation-circle me-2"></i>Required Information Missing</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-3">Please complete the following required fields before continuing:</p>
                <ul class="list-unstyled mb-0" id="validationErrorsList"></ul>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK, I'll fix these</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="csvAlertModal" tabindex="-1" style="z-index: 1070;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title" style="color: #886CC0;"><i class="fas fa-info-circle me-2"></i>Attention</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-2">
                <p class="mb-0" id="csvAlertMessage"></p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-primary btn-sm" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

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

<div class="modal fade" id="testMessageModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-3">
                <h5 class="modal-title"><i class="fas fa-mobile-alt me-2"></i>Send a test message</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-3">We'll send this message to your phone so you can preview it before continuing.</p>
                
                <div class="mb-3">
                    <label class="form-label">Mobile number <span class="text-danger">*</span></label>
                    <input type="tel" class="form-control" id="testMobileNumber" placeholder="e.g. 447700900123" maxlength="15">
                    <small class="text-muted">Enter your UK mobile number (07... or +44... or 447...)</small>
                </div>
                
                <div class="mb-3 d-none" id="testMessageChannelInfo">
                    <div class="py-2 mb-0 rounded" style="background-color: #f0ebf8; color: #6b5b95; padding: 12px;">
                        <small>
                            <i class="fas fa-info-circle me-1"></i>
                            <span id="testChannelDescription">SMS message will be sent</span>
                        </small>
                    </div>
                </div>
                
                <div class="d-none" id="testMessageSuccess">
                    <div class="alert alert-success mb-0">
                        <i class="fas fa-check-circle me-2"></i>Test message sent. Check your phone.
                    </div>
                </div>
                
                <div class="d-none" id="testMessageError">
                    <div class="alert alert-danger mb-0">
                        <i class="fas fa-exclamation-circle me-2"></i><span id="testErrorText">Failed to send test message.</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="sendTestBtn" onclick="sendTestMessage()">
                    <i class="fas fa-paper-plane me-1"></i>Send test
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="saveDraftModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-3">
                <h5 class="modal-title"><i class="fas fa-save me-2"></i>Save Draft</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Draft Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="draftName" placeholder="Enter a name for this draft">
                    <small class="text-muted">Give your draft a memorable name so you can find it later</small>
                </div>
                <div class="p-3 rounded mb-3" style="background-color: #f0ebf8;">
                    <h6 class="mb-2"><i class="fas fa-info-circle me-1 text-primary"></i>Draft Summary</h6>
                    <div class="small">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Channel:</span>
                            <span id="draftSummaryChannel">SMS</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Recipients:</span>
                            <span id="draftSummaryRecipients">0</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Message:</span>
                            <span id="draftSummaryMessage" class="text-truncate ms-2" style="max-width: 200px;">No content</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="confirmSaveDraft()"><i class="fas fa-save me-1"></i>Save Draft</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="draftSavedModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-body text-center py-4">
                <div class="mb-3">
                    <i class="fas fa-check-circle text-success" style="font-size: 48px;"></i>
                </div>
                <h5 class="mb-2">Draft Saved!</h5>
                <p class="text-muted mb-3">Your campaign has been saved and can be found in Campaign History.</p>
                <div class="d-flex gap-2 justify-content-center">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Continue Editing</button>
                    <a href="/messages/campaign-history" class="btn btn-primary">View Drafts</a>
                </div>
            </div>
        </div>
    </div>
</div>

@include('quicksms.partials.rcs-wizard-modal')

<script src="https://cdn.sheetjs.com/xlsx-0.20.3/package/dist/xlsx.full.min.js"></script>
<script src="{{ asset('js/rcs-preview-renderer.js') }}?v=20260106b"></script>
<script src="{{ asset('js/rcs-wizard.js') }}?v=20260210d"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(e) { return new bootstrap.Tooltip(e); });
    
    document.getElementById('emojiPickerBtn').addEventListener('click', function() {
        openEmojiPicker();
    });
    
    document.querySelectorAll('input[name="channel"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            selectChannel(this.value);
            populateTemplateSelector();
        });
    });
    
    document.querySelectorAll('input[name="scheduling"]').forEach(function(radio) {
        radio.addEventListener('change', toggleScheduling);
    });
    
    populateTemplateSelector();
    checkForDuplicatePrefill();
    updatePreview();
    loadPreselectedContacts();
});

function checkForDuplicatePrefill() {
    var urlParams = new URLSearchParams(window.location.search);
    var duplicateId = urlParams.get('duplicate');
    var editId = urlParams.get('edit');
    
    // Handle edit draft
    if (editId) {
        loadDraftForEditing(editId);
        return;
    }
    
    if (duplicateId) {
        var configJson = sessionStorage.getItem('campaignDuplicateConfig');
        
        if (configJson) {
            try {
                var config = JSON.parse(configJson);
                
                // TODO: Replace with actual campaign config loading from backend
                // - Call GET /api/campaigns/{duplicateId}/config
                // - Prefill: channel, sender ID, RCS agent, message content, recipients, scheduling options
                // - For now, just show notification with mock prefill
                
                var campaignNameInput = document.getElementById('campaignName');
                if (campaignNameInput && config.name) {
                    campaignNameInput.value = config.name;
                }
                
                showDuplicateNotification(config.originalName);
                
                sessionStorage.removeItem('campaignDuplicateConfig');
                
                window.history.replaceState({}, document.title, window.location.pathname);
            } catch (e) {
                console.error('Error parsing duplicate config:', e);
            }
        }
    }
}

function loadDraftForEditing(draftId) {
    var drafts = JSON.parse(localStorage.getItem('quicksms_drafts') || '[]');
    var draft = drafts.find(function(d) { return d.id === draftId; });
    
    if (!draft) {
        console.error('Draft not found:', draftId);
        return;
    }
    
    console.log('Loading draft for editing:', draft);
    
    // Set campaign name
    var campaignNameInput = document.getElementById('campaignName');
    if (campaignNameInput && draft.name) {
        campaignNameInput.value = draft.name;
    }
    
    // Set channel
    if (draft.config && draft.config.channel) {
        var channelMap = {
            'sms_only': 'sms',
            'sms': 'sms',
            'basic_rcs': 'rcs_basic',
            'rcs_basic': 'rcs_basic',
            'rich_rcs': 'rcs_rich',
            'rcs_rich': 'rcs_rich'
        };
        var channelValue = channelMap[draft.config.channel] || 'sms';
        var channelRadio = document.querySelector('input[name="channel"][value="' + channelValue + '"]');
        if (channelRadio) {
            channelRadio.checked = true;
            selectChannel(channelValue);
        }
    }
    
    // Set sender ID
    if (draft.config && draft.config.sender_id) {
        var senderSelect = document.getElementById('senderIdSelect');
        if (senderSelect) {
            for (var i = 0; i < senderSelect.options.length; i++) {
                if (senderSelect.options[i].value === draft.config.sender_id || 
                    senderSelect.options[i].text === draft.config.sender_id) {
                    senderSelect.selectedIndex = i;
                    break;
                }
            }
        }
    }
    
    // Set RCS agent
    if (draft.config && draft.config.rcs_agent) {
        var rcsAgentSelect = document.getElementById('rcsAgentSelect');
        if (rcsAgentSelect) {
            for (var i = 0; i < rcsAgentSelect.options.length; i++) {
                if (rcsAgentSelect.options[i].value === draft.config.rcs_agent || 
                    rcsAgentSelect.options[i].text === draft.config.rcs_agent) {
                    rcsAgentSelect.selectedIndex = i;
                    break;
                }
            }
        }
    }
    
    // Set message content
    if (draft.config && draft.config.message_content) {
        var smsContent = document.getElementById('smsContent');
        if (smsContent) {
            smsContent.value = draft.config.message_content;
            updateCharacterCount();
            updatePreview();
        }
    }
    
    // Set recipients
    if (draft.config && draft.config.recipients && draft.config.recipients.length > 0) {
        var manualNumbers = document.getElementById('manualNumbers');
        if (manualNumbers) {
            manualNumbers.value = draft.config.recipients.join('\n');
            validateManualNumbers();
        }
    }
    
    // Set trackable link
    if (draft.config && draft.config.trackable_link) {
        var trackableLink = document.getElementById('enableTrackableLink');
        if (trackableLink) {
            trackableLink.checked = true;
        }
    }
    
    // Set opt-out
    if (draft.config && draft.config.optout_enabled) {
        var optoutCheckbox = document.getElementById('optoutMessage');
        if (optoutCheckbox) {
            optoutCheckbox.checked = true;
            toggleOptoutOptions();
        }
    }
    
    // Show notification
    showDraftLoadedNotification(draft.name);
    
    // Clear URL parameter
    window.history.replaceState({}, document.title, window.location.pathname);
}

function showDraftLoadedNotification(draftName) {
    var alertHtml = '<div class="alert alert-dismissible fade show mb-3" role="alert" style="background-color: #f0ebf8; color: #6b5b95; border: none;">' +
        '<i class="fas fa-file-alt me-2"></i>' +
        '<strong>Draft loaded:</strong> ' + draftName +
        '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
        '</div>';
    
    var cardBody = document.querySelector('.card-body');
    if (cardBody) {
        cardBody.insertAdjacentHTML('afterbegin', alertHtml);
    }
}

function showDuplicateNotification(originalName) {
    var alertHtml = '<div class="alert alert-info alert-dismissible fade show mb-3" role="alert">' +
        '<i class="fas fa-copy me-2"></i>' +
        '<strong>Duplicating campaign:</strong> ' + originalName +
        '<br><small class="text-muted">TODO: Full configuration prefill requires backend integration.</small>' +
        '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
        '</div>';
    
    var cardBody = document.querySelector('.card-body');
    if (cardBody) {
        cardBody.insertAdjacentHTML('afterbegin', alertHtml);
    }
}

var basicRcsPreviewMode = 'rcs';

function selectChannel(channel) {
    var rcsAgentSection = document.getElementById('rcsAgentSection');
    var rcsContentSection = document.getElementById('rcsContentSection');
    var previewChannel = document.getElementById('previewChannel');
    var contentLabel = document.getElementById('contentLabel');
    var rcsTextHelper = document.getElementById('rcsTextHelper');
    var rcsHelperText = document.getElementById('rcsHelperText');
    var previewToggle = document.getElementById('previewToggleContainer');
    var basicRcsToggle = document.getElementById('basicRcsPreviewToggle');
    
    if (channel === 'sms') {
        rcsAgentSection.classList.add('d-none');
        rcsContentSection.classList.add('d-none');
        rcsTextHelper.classList.add('d-none');
        previewChannel.textContent = 'SMS';
        contentLabel.textContent = 'SMS Content';
        previewToggle.classList.add('d-none');
        basicRcsToggle.classList.add('d-none');
        updatePreview();
    } else if (channel === 'rcs_basic') {
        rcsAgentSection.classList.remove('d-none');
        rcsContentSection.classList.add('d-none');
        rcsTextHelper.classList.remove('d-none');
        rcsHelperText.textContent = 'Messages over 160 characters will be automatically sent as a single RCS message where supported.';
        previewChannel.textContent = 'Basic RCS';
        contentLabel.textContent = 'Message Content';
        previewToggle.classList.add('d-none');
        basicRcsToggle.classList.remove('d-none');
        basicRcsPreviewMode = 'rcs';
        document.getElementById('basicPreviewRCSBtn').classList.add('active');
        document.getElementById('basicPreviewSMSBtn').classList.remove('active');
        autoSelectFirstAgent();
        updatePreview();
    } else if (channel === 'rcs_rich') {
        rcsAgentSection.classList.remove('d-none');
        rcsContentSection.classList.remove('d-none');
        rcsTextHelper.classList.add('d-none');
        previewChannel.textContent = 'Rich RCS';
        contentLabel.textContent = 'SMS Fallback Content';
        previewToggle.classList.remove('d-none');
        basicRcsToggle.classList.add('d-none');
        document.getElementById('previewRCSBtn').classList.add('active');
        document.getElementById('previewSMSBtn').classList.remove('active');
        autoSelectFirstAgent();
        updatePreview();
    }
    handleContentChange();
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
        smsBtn.style.background = '#886CC0';
        smsBtn.style.color = 'white';
        rcsBtn.style.background = 'white';
        rcsBtn.style.color = '#886CC0';
    }
    
    rcsBtn.classList.toggle('active', mode === 'rcs');
    smsBtn.classList.toggle('active', mode === 'sms');
    updatePreview();
}

function toggleScheduling() {
    var options = document.getElementById('schedulingOptions');
    var isLater = document.getElementById('sendLater').checked;
    options.classList.toggle('d-none', !isLater);
}

function autoSelectFirstAgent() {
    var agentSelect = document.getElementById('rcsAgent');
    if (agentSelect && agentSelect.selectedIndex === 0 && agentSelect.options.length > 1) {
        agentSelect.selectedIndex = 1;
    }
}

function updatePreview() {
    var channel = document.querySelector('input[name="channel"]:checked')?.value || 'sms';
    var container = document.getElementById('mainPreviewContainer');
    if (!container) return;
    
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
            // Show Rich RCS preview - use configured content or placeholder
            var selectedOption = rcsAgentSelect?.selectedOptions[0];
            var agent = {
                name: selectedOption?.dataset?.name || selectedOption?.text || 'QuickSMS Brand',
                logo: selectedOption?.dataset?.logo || '{{ asset("images/rcs-agents/quicksms-brand.svg") }}',
                verified: true,
                tagline: selectedOption?.dataset?.tagline || 'Business messaging'
            };
            
            if (typeof rcsPersistentPayload !== 'undefined' && rcsPersistentPayload) {
                // Render configured Rich RCS content
                container.innerHTML = RcsPreviewRenderer.renderRichRcsPreview(rcsPersistentPayload, agent);
            } else {
                // Show placeholder for unconfigured Rich RCS
                container.innerHTML = RcsPreviewRenderer.renderRichRcsPlaceholder(agent);
            }
            return;
        }
    }
    
    container.innerHTML = RcsPreviewRenderer.renderPreview(previewConfig);
}

var GSM_CHARS = "@£$¥èéùìòÇ\nØø\rÅåΔ_ΦΓΛΩΠΨΣΘΞÆæßÉ !\"#¤%&'()*+,-./0123456789:;<=>?¡ABCDEFGHIJKLMNOPQRSTUVWXYZÄÖÑÜ§¿abcdefghijklmnopqrstuvwxyzäöñüà";
var GSM_EXTENDED = "^{}\\[~]|€";

function isGSM7(text) {
    for (var i = 0; i < text.length; i++) {
        var char = text[i];
        if (GSM_CHARS.indexOf(char) === -1 && GSM_EXTENDED.indexOf(char) === -1) {
            return false;
        }
    }
    return true;
}

function handleContentChange() {
    var content = document.getElementById('smsContent').value;
    var charCount = content.length;
    var isGsm = isGSM7(content);
    var channel = document.querySelector('input[name="channel"]:checked').value;
    
    document.getElementById('charCount').textContent = charCount;
    document.getElementById('encodingType').textContent = isGsm ? 'GSM-7' : 'Unicode';
    document.getElementById('unicodeWarning').classList.toggle('d-none', isGsm);
    
    var segmentDisplay = document.getElementById('segmentDisplay');
    if (channel === 'rcs_basic' && charCount > 160) {
        segmentDisplay.innerHTML = '<em class="text-success">Single RCS message</em>';
        document.getElementById('rcsTextHelper').classList.remove('d-none');
        document.getElementById('rcsHelperText').textContent = 'This message will be delivered as a single RCS text message.';
    } else {
        var singleLimit = isGsm ? 160 : 70;
        var concatLimit = isGsm ? 153 : 67;
        var parts = charCount <= singleLimit ? 1 : Math.ceil(charCount / concatLimit);
        segmentDisplay.innerHTML = 'Segments: <strong id="smsPartCount">' + parts + '</strong>';
        if (channel !== 'rcs_basic') {
            document.getElementById('rcsTextHelper').classList.add('d-none');
        }
    }
    
    updatePreview();
    updatePreviewCost();
}

function updateCharCount() {
    handleContentChange();
}

function applyTemplate() {
    var select = document.getElementById('templateSelect');
    var option = select.selectedOptions[0];
    if (option && option.dataset.content) {
        document.getElementById('smsContent').value = option.dataset.content;
        handleContentChange();
    }
}

function openPersonalisationModal() {
    var modal = new bootstrap.Modal(document.getElementById('personalisationModal'));
    modal.show();
}

function insertPlaceholder(field) {
    if (rcsActiveTextField) {
        insertRcsPlaceholder(field);
        return;
    }
    var textarea = document.getElementById('smsContent');
    var start = textarea.selectionStart;
    var end = textarea.selectionEnd;
    var text = textarea.value;
    var placeholder = '{' + '{' + field + '}' + '}';
    textarea.value = text.substring(0, start) + placeholder + text.substring(end);
    textarea.selectionStart = textarea.selectionEnd = start + placeholder.length;
    textarea.focus();
    handleContentChange();
    bootstrap.Modal.getInstance(document.getElementById('personalisationModal')).hide();
}

function openEmojiPicker() {
    var modal = new bootstrap.Modal(document.getElementById('emojiPickerModal'));
    modal.show();
}

function insertEmoji(emoji) {
    if (rcsActiveTextField) {
        insertRcsEmoji(emoji);
        return;
    }
    var textarea = document.getElementById('smsContent');
    var start = textarea.selectionStart;
    var end = textarea.selectionEnd;
    var text = textarea.value;
    textarea.value = text.substring(0, start) + emoji + text.substring(end);
    textarea.selectionStart = textarea.selectionEnd = start + emoji.length;
    textarea.focus();
    handleContentChange();
    bootstrap.Modal.getInstance(document.getElementById('emojiPickerModal')).hide();
}

function toggleTemplateSelection() {
    var isChecked = document.getElementById('useTemplate').checked;
    if (isChecked) {
        var modal = new bootstrap.Modal(document.getElementById('templateModal'));
        modal.show();
    }
}

function selectTemplate(id, content) {
    document.getElementById('smsContent').value = content;
    handleContentChange();
    bootstrap.Modal.getInstance(document.getElementById('templateModal')).hide();
}

function filterTemplates() {
    var search = document.getElementById('templateSearch').value.toLowerCase();
    document.querySelectorAll('#templateList .list-group-item').forEach(function(item) {
        var text = item.textContent.toLowerCase();
        item.style.display = text.indexOf(search) > -1 ? '' : 'none';
    });
}

var trackableLinkConfirmed = false;
var messageExpiryConfirmed = false;
var scheduleRulesConfirmed = false;

function toggleTrackableLinkModal() {
    var isChecked = document.getElementById('includeTrackableLink').checked;
    if (isChecked) {
        trackableLinkConfirmed = false;
        var modalEl = document.getElementById('trackableLinkModal');
        var modal = new bootstrap.Modal(modalEl);
        modalEl.addEventListener('hidden.bs.modal', onTrackableLinkModalHidden, { once: true });
        modal.show();
    } else {
        document.getElementById('trackableLinkSummary').classList.add('d-none');
    }
}

function onTrackableLinkModalHidden() {
    if (!trackableLinkConfirmed) {
        var hasUrl = document.getElementById('destinationUrl').value.trim() !== '';
        if (!hasUrl) {
            document.getElementById('includeTrackableLink').checked = false;
            document.getElementById('trackableLinkSummary').classList.add('d-none');
        }
    }
}

function openTrackableLinkModal() {
    var modal = new bootstrap.Modal(document.getElementById('trackableLinkModal'));
    modal.show();
}

function confirmTrackableLink() {
    var domain = document.getElementById('shortUrlDomain').value;
    var url = document.getElementById('destinationUrl').value.trim();
    var method = document.querySelector('input[name="linkInsertMethod"]:checked').value;
    
    if (!url) {
        alert('Please enter a destination URL');
        return;
    }
    
    trackableLinkConfirmed = true;
    document.getElementById('trackableLinkDomain').textContent = domain;
    document.getElementById('trackableLinkSummary').classList.remove('d-none');
    
    if (method === 'cursor') {
        var textarea = document.getElementById('smsContent');
        var start = textarea.selectionStart;
        var text = textarea.value;
        var shortUrl = 'https://' + domain + '/abc123';
        textarea.value = text.substring(0, start) + shortUrl + text.substring(start);
        handleContentChange();
    } else {
        insertPlaceholderDirect('trackingUrl');
    }
    
    bootstrap.Modal.getInstance(document.getElementById('trackableLinkModal')).hide();
}

function insertPlaceholderDirect(field) {
    var textarea = document.getElementById('smsContent');
    var start = textarea.selectionStart;
    var text = textarea.value;
    var placeholder = '{' + '{' + field + '}' + '}';
    textarea.value = text.substring(0, start) + placeholder + text.substring(start);
    handleContentChange();
}

function toggleScheduleRulesModal() {
    var isChecked = document.getElementById('scheduleRules').checked;
    if (isChecked) {
        scheduleRulesConfirmed = false;
        var modalEl = document.getElementById('scheduleRulesModal');
        var modal = new bootstrap.Modal(modalEl);
        modalEl.addEventListener('hidden.bs.modal', onScheduleRulesModalHidden, { once: true });
        modal.show();
    } else {
        document.getElementById('scheduleSummary').classList.add('d-none');
    }
}

function onScheduleRulesModalHidden() {
    if (!scheduleRulesConfirmed) {
        var hasSchedule = document.getElementById('scheduleToggle').checked;
        var hasUnsociable = document.getElementById('unsociableToggle').checked;
        if (!hasSchedule && !hasUnsociable) {
            document.getElementById('scheduleRules').checked = false;
            document.getElementById('scheduleSummary').classList.add('d-none');
        }
    }
}

function openScheduleRulesModal() {
    var modal = new bootstrap.Modal(document.getElementById('scheduleRulesModal'));
    modal.show();
}

function toggleMessageExpiryModal() {
    var isChecked = document.getElementById('messageExpiry').checked;
    if (isChecked) {
        messageExpiryConfirmed = false;
        var modalEl = document.getElementById('messageExpiryModal');
        var modal = new bootstrap.Modal(modalEl);
        modalEl.addEventListener('hidden.bs.modal', onMessageExpiryModalHidden, { once: true });
        modal.show();
    } else {
        document.getElementById('messageExpirySummary').classList.add('d-none');
    }
}

function onMessageExpiryModalHidden() {
    if (!messageExpiryConfirmed) {
        document.getElementById('messageExpiry').checked = false;
        document.getElementById('messageExpirySummary').classList.add('d-none');
    }
}

function openMessageExpiryModal() {
    var modal = new bootstrap.Modal(document.getElementById('messageExpiryModal'));
    modal.show();
}

function confirmMessageExpiry() {
    var isEnabled = document.getElementById('validityToggle').checked;
    if (isEnabled) {
        var duration = document.getElementById('validityDuration').value;
        var unit = document.getElementById('validityUnit').value;
        var unitLabel = unit.charAt(0).toUpperCase() + unit.slice(1);
        document.getElementById('messageExpiryValue').textContent = duration + ' ' + unitLabel;
        document.getElementById('messageExpirySummary').classList.remove('d-none');
        messageExpiryConfirmed = true;
    } else {
        document.getElementById('messageExpiry').checked = false;
        document.getElementById('messageExpirySummary').classList.add('d-none');
        messageExpiryConfirmed = true;
    }
    var modal = bootstrap.Modal.getInstance(document.getElementById('messageExpiryModal'));
    if (modal) modal.hide();
}

var portalTemplates = @json($templates ?? []);

function getCompatibleTemplates(currentChannel) {
    var channelMap = {
        'sms': ['sms'],
        'rcs_basic': ['rcs_basic', 'sms'],
        'rcs_rich': ['rcs_rich', 'rcs_basic', 'sms']
    };
    var allowedChannels = channelMap[currentChannel] || ['sms'];
    
    return portalTemplates.filter(function(t) {
        if (t.trigger === 'API') return false;
        if (t.status === 'Archived') return false;
        var templateChannel = t.channel || 'sms';
        if (templateChannel === 'Basic RCS + SMS') templateChannel = 'rcs_basic';
        if (templateChannel === 'Rich RCS + SMS') templateChannel = 'rcs_rich';
        if (templateChannel === 'SMS') templateChannel = 'sms';
        return allowedChannels.indexOf(templateChannel) !== -1;
    });
}

function populateTemplateSelector() {
    var channel = document.querySelector('input[name="channel"]:checked')?.value || 'sms';
    var selector = document.getElementById('templateSelector');
    var currentValue = selector.value;
    
    selector.innerHTML = '<option value="">-- None --</option>';
    
    var compatible = getCompatibleTemplates(channel);
    compatible.forEach(function(t) {
        var opt = document.createElement('option');
        opt.value = t.id;
        opt.setAttribute('data-content', (t.content || '').replace(/'/g, "\\'"));
        opt.setAttribute('data-channel', t.channel || 'SMS');
        opt.setAttribute('data-rcs-payload', t.rcs_payload ? JSON.stringify(t.rcs_payload) : '');
        opt.textContent = t.name + ' (v' + (t.version || '1') + ')';
        selector.appendChild(opt);
    });
    
    if (currentValue) selector.value = currentValue;
}

function refreshTemplateList() {
    var btn = event.target.closest('button');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    
    setTimeout(function() {
        populateTemplateSelector();
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-sync-alt"></i>';
    }, 300);
}

function applySelectedTemplate() {
    var selector = document.getElementById('templateSelector');
    var selectedOption = selector.options[selector.selectedIndex];
    
    if (!selectedOption.value) {
        document.getElementById('smsContent').value = '';
        handleContentChange();
        return;
    }
    
    var channel = selectedOption.getAttribute('data-channel') || 'SMS';
    var content = selectedOption.getAttribute('data-content') || '';
    var rcsPayloadStr = selectedOption.getAttribute('data-rcs-payload');
    
    content = content.replace(/\\'/g, "'");
    
    if (channel === 'Rich RCS + SMS' && rcsPayloadStr) {
        try {
            var payload = JSON.parse(rcsPayloadStr);
            document.querySelector('#channelRCSRich').click();
            
            setTimeout(function() {
                if (typeof openRcsWizard === 'function') {
                    openRcsWizard();
                    setTimeout(function() {
                        if (typeof loadRcsPayloadIntoWizard === 'function') {
                            loadRcsPayloadIntoWizard(payload);
                        }
                    }, 300);
                }
            }, 200);
        } catch (e) {
            console.warn('Failed to parse RCS payload:', e);
        }
    } else if (channel === 'Basic RCS + SMS') {
        document.querySelector('#channelRCSBasic').click();
        document.getElementById('smsContent').value = content;
        handleContentChange();
    } else {
        document.getElementById('smsContent').value = content;
        handleContentChange();
    }
}

function toggleScheduleFields() {
    var isChecked = document.getElementById('scheduleToggle').checked;
    document.getElementById('scheduleFields').classList.toggle('d-none', !isChecked);
}

function toggleUnsociableFields() {
    var isChecked = document.getElementById('unsociableToggle').checked;
    document.getElementById('unsociableFields').classList.toggle('d-none', !isChecked);
}

function toggleValidityFields() {
    var isChecked = document.getElementById('validityToggle').checked;
    document.getElementById('validityFields').classList.toggle('d-none', !isChecked);
}

function confirmScheduleRules() {
    var scheduled = document.getElementById('scheduleToggle').checked;
    var unsociable = document.getElementById('unsociableToggle').checked;
    
    var summaryParts = [];
    
    if (scheduled) {
        var date = document.getElementById('scheduleDate').value;
        var time = document.getElementById('scheduleTime').value;
        if (date && time) {
            var dateObj = new Date(date + 'T' + time);
            summaryParts.push('Scheduled: ' + dateObj.toLocaleString('en-GB', {day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'}));
        }
    }
    
    if (unsociable) {
        var from = document.getElementById('unsociableFrom').value;
        var to = document.getElementById('unsociableTo').value;
        summaryParts.push('Quiet hours: ' + from + ' - ' + to);
    }
    
    scheduleRulesConfirmed = true;
    
    if (summaryParts.length > 0) {
        document.getElementById('scheduleSummaryText').textContent = summaryParts.join(' | ');
        document.getElementById('scheduleSummary').classList.remove('d-none');
        document.getElementById('scheduleRules').checked = true;
    } else {
        document.getElementById('scheduleSummary').classList.add('d-none');
        document.getElementById('scheduleRules').checked = false;
    }
    
    bootstrap.Modal.getInstance(document.getElementById('scheduleRulesModal')).hide();
}

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
    
    var modal = new bootstrap.Modal(document.getElementById('aiAssistantModal'));
    modal.show();
}

var aiSuggestedText = '';

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
            'tone': 'Hi @{{firstName}}, we hope you\'re well! Just a friendly reminder about your upcoming appointment. We look forward to seeing you soon!',
            'shorten': 'Reminder: Appt on @{{appointmentDate}} at @{{appointmentTime}}. Reply to confirm.',
            'grammar': content.charAt(0).toUpperCase() + content.slice(1).replace(/\s+/g, ' ').trim() + (content.endsWith('.') ? '' : '.'),
            'clarity': 'This is a reminder about your scheduled appointment. Please arrive 10 minutes early. Reply YES to confirm or call us to reschedule.'
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

// addRcsButton function loaded from shared rcs-wizard.js

function insertMergeField() {
    openPersonalisationModal();
}

function insertTrackingUrl() {
    document.getElementById('includeTrackableLink').checked = true;
    toggleTrackableLinkModal();
}

function getRcsPayloadForSubmission() {
    if (!rcsPersistentPayload) {
        return null;
    }
    
    // TODO: Google RCS API Integration
    // Transform rcsPersistentPayload to Google RCS format before submission
    // Include user_id from session and campaign timestamp
    
    return {
        rcsContent: rcsPersistentPayload,
        submittedAt: new Date().toISOString(),
        userId: null, // TODO: Populate from Laravel session
        campaignId: null // TODO: Generate or retrieve campaign ID
    };
}


// RCS Wizard functions loaded from shared rcs-wizard.js

var rcsCurrentCardWidth = 'medium';
var rcsCarouselHeight = 'vertical_short';
var rcsCarouselWidth = 'medium';

function processAssetServerSide(isUpdate) {
    if (rcsEditDebounceTimer) {
        clearTimeout(rcsEditDebounceTimer);
    }
    
    rcsEditDebounceTimer = setTimeout(function() {
        var editParams = getCurrentEditParams();
        
        if (editParams.zoom === 100 && editParams.cropOffsetX === 0 && editParams.cropOffsetY === 0 && !rcsMediaData.assetUuid) {
            return;
        }
        
        if (rcsMediaData.assetUuid && isUpdate) {
            fetch('/api/rcs/assets/' + rcsMediaData.assetUuid, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({ edit_params: editParams })
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.success && data.asset) {
                    rcsMediaData.hostedUrl = data.asset.public_url;
                    rcsMediaData.dimensions = { width: data.asset.width, height: data.asset.height };
                    rcsMediaData.fileSize = data.asset.file_size;
                    updateRcsImageInfo();
                }
            })
            .catch(function(err) {
                console.error('Failed to update asset:', err);
            });
        } else if (rcsMediaData.originalUrl && rcsMediaData.source === 'url') {
            showRcsProcessingIndicator();
            
            fetch('/api/rcs/assets/process-url', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({
                    url: rcsMediaData.originalUrl,
                    edit_params: editParams,
                    draft_session: rcsDraftSession
                })
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                hideRcsProcessingIndicator();
                if (data.success && data.asset) {
                    rcsMediaData.assetUuid = data.asset.uuid;
                    rcsMediaData.hostedUrl = data.asset.public_url;
                    rcsMediaData.url = data.asset.public_url;
                    rcsMediaData.dimensions = { width: data.asset.width, height: data.asset.height };
                    rcsMediaData.fileSize = data.asset.file_size;
                    showRcsMediaPreview(data.asset.public_url);
                    updateRcsImageInfo();
                } else if (data.error) {
                    showRcsMediaError(data.error);
                }
            })
            .catch(function(err) {
                hideRcsProcessingIndicator();
                showRcsMediaError('Failed to process image. Please try again.');
            });
        }
    }, 500);
}


// Crop editor functions loaded from shared rcs-wizard.js

function setRcsCropPosition(position) {
    var workspace = document.getElementById('rcsCropWorkspace');
    if (!workspace) return;
    
    var scale = rcsCropState.displayScale * (rcsCropState.zoom / 100);
    var displayHeight = rcsCropState.imageHeight * scale;
    var workspaceHeight = workspace.clientHeight;
    var frameHalfH = rcsCropState.frameHeight / 2;
    
    switch(position) {
        case 'top':
            rcsCropState.offsetY = (workspaceHeight / 2) - frameHalfH - ((displayHeight / 2) - frameHalfH);
            break;
        case 'bottom':
            rcsCropState.offsetY = -((workspaceHeight / 2) - frameHalfH - ((displayHeight / 2) - frameHalfH));
            break;
        default:
            rcsCropState.offsetY = 0;
    }
    rcsCropState.offsetX = 0;
    
    constrainRcsCropPosition();
    applyRcsCropTransform();
    markRcsImageDirty();
}

// Text count and picker functions loaded from shared rcs-wizard.js

function insertRcsPlaceholder(field) {
    var el = getRcsTextElement(rcsActiveTextField);
    if (!el) return;
    
    var start = el.selectionStart;
    var end = el.selectionEnd;
    var text = el.value;
    var placeholder = '{{' + field + '}}';
    el.value = text.substring(0, start) + placeholder + text.substring(end);
    el.selectionStart = el.selectionEnd = start + placeholder.length;
    el.focus();
    
    if (rcsActiveTextField === 'description') updateRcsDescriptionCount();
    if (rcsActiveTextField === 'textBody') updateRcsTextBodyCount();
    if (rcsActiveTextField === 'rcsButtonLabel') updateRcsButtonLabelCount();
    
    bootstrap.Modal.getInstance(document.getElementById('personalisationModal')).hide();
    rcsActiveTextField = null;
}

function insertRcsEmoji(emoji) {
    var el = getRcsTextElement(rcsActiveTextField);
    if (!el) return;
    
    var start = el.selectionStart;
    var end = el.selectionEnd;
    var text = el.value;
    el.value = text.substring(0, start) + emoji + text.substring(end);
    el.selectionStart = el.selectionEnd = start + emoji.length;
    el.focus();
    
    if (rcsActiveTextField === 'description') updateRcsDescriptionCount();
    if (rcsActiveTextField === 'textBody') updateRcsTextBodyCount();
    if (rcsActiveTextField === 'rcsButtonLabel') updateRcsButtonLabelCount();
    
    bootstrap.Modal.getInstance(document.getElementById('emojiPickerModal')).hide();
    rcsActiveTextField = null;
}

// Button management functions loaded from shared rcs-wizard.js

function escapeHtml(text) {
    var div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

var rcsMessageTypeBeforeChange = null;

function captureRcsMessageTypeState() {
    rcsMessageTypeBeforeChange = document.querySelector('input[name="rcsMessageType"]:checked')?.value;
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('label[for^="rcsType"]').forEach(function(label) {
        label.addEventListener('mousedown', captureRcsMessageTypeState);
        label.addEventListener('touchstart', captureRcsMessageTypeState);
    });
    
    document.querySelectorAll('input[name="rcsMessageType"]').forEach(function(radio) {
        radio.addEventListener('focus', captureRcsMessageTypeState);
    });
    
    document.querySelectorAll('input[name="rcsMessageType"]').forEach(function(radio) {
        radio.addEventListener('change', function(e) {
            var newValue = e.target.value;
            if (isRcsImageDirty()) {
                e.preventDefault();
                e.stopImmediatePropagation();
                if (rcsMessageTypeBeforeChange && rcsMessageTypeBeforeChange !== newValue) {
                    document.getElementById(rcsMessageTypeBeforeChange === 'single' ? 'rcsTypeSingle' : 'rcsTypeCarousel').checked = true;
                }
                showRcsUnsavedChangesModal({ type: 'changeType', targetValue: newValue });
                return;
            }
            toggleRcsMessageType();
            updateCarouselOrientationWarning();
            updateRcsWizardPreview();
        });
    });
    
    document.querySelectorAll('input[name="rcsMediaSource"]').forEach(function(radio) {
        radio.addEventListener('change', toggleRcsMediaSource);
    });
    
    document.querySelectorAll('input[name="rcsOrientation"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            markRcsImageDirty();
            updateRcsWizardPreview();
        });
    });
    
    var fileInput = document.getElementById('rcsMediaFileInput');
    fileInput.addEventListener('change', function(e) {
        if (e.target.files.length > 0) {
            handleRcsFileUpload(e.target.files[0]);
        }
    });
    
    var dropzone = document.getElementById('rcsMediaDropzone');
    dropzone.addEventListener('dragover', function(e) {
        e.preventDefault();
        dropzone.classList.add('border-primary');
    });
    dropzone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        dropzone.classList.remove('border-primary');
    });
    dropzone.addEventListener('drop', function(e) {
        e.preventDefault();
        dropzone.classList.remove('border-primary');
        if (e.dataTransfer.files.length > 0) {
            handleRcsFileUpload(e.dataTransfer.files[0]);
        }
    });
    
    document.getElementById('personalisationModal').addEventListener('hidden.bs.modal', function() {
        rcsActiveTextField = null;
    });
    document.getElementById('emojiPickerModal').addEventListener('hidden.bs.modal', function() {
        rcsActiveTextField = null;
    });
    
    document.querySelectorAll('input[name="rcsButtonType"]').forEach(function(radio) {
        radio.addEventListener('change', toggleRcsButtonType);
    });
    
    document.getElementById('rcsButtonConfigModal').addEventListener('hidden.bs.modal', function() {
        rcsEditingButtonIndex = -1;
    });
});

var richRcsPreviewMode = 'rcs';

function showPreview(type) {
    richRcsPreviewMode = type;
    var channel = document.querySelector('input[name="channel"]:checked')?.value || 'sms';
    
    var rcsBtn = document.getElementById('previewRCSBtn');
    var smsBtn = document.getElementById('previewSMSBtn');
    
    if (type === 'rcs') {
        rcsBtn.style.background = '#886CC0';
        rcsBtn.style.color = 'white';
        smsBtn.style.background = 'white';
        smsBtn.style.color = '#886CC0';
    } else {
        smsBtn.style.background = '#886CC0';
        smsBtn.style.color = 'white';
        rcsBtn.style.background = 'white';
        rcsBtn.style.color = '#886CC0';
    }
    
    rcsBtn.classList.toggle('active', type === 'rcs');
    smsBtn.classList.toggle('active', type === 'sms');
    
    if (channel === 'rcs_rich') {
        if (type === 'rcs') {
            updateRcsWizardPreviewInMain();
        } else {
            showRichRcsSmsPreview();
        }
    } else {
        updatePreview();
    }
}

function showRichRcsSmsPreview() {
    var container = document.getElementById('mainPreviewContainer');
    if (!container) return;
    
    var senderId = document.getElementById('senderId');
    var smsContent = document.getElementById('smsContent');
    
    var senderIdText = (senderId?.selectedOptions[0]?.text || 'Sender').replace(/\s*\(.*?\)\s*$/, '');
    var messageText = smsContent?.value || '';
    
    var previewConfig = {
        channel: 'sms',
        senderId: senderIdText,
        message: { body: messageText }
    };
    
    container.innerHTML = RcsPreviewRenderer.renderPreview(previewConfig);
}

function updateRcsWizardPreviewInMain() {
    var container = document.getElementById('mainPreviewContainer');
    if (!container) return;
    
    var rcsAgentSelect = document.getElementById('rcsAgent');
    var selectedOption = rcsAgentSelect?.selectedOptions[0];
    var agentName = selectedOption?.dataset?.name || selectedOption?.text || 'QuickSMS Brand';
    var agentLogo = selectedOption?.dataset?.logo || '{{ asset("images/rcs-agents/quicksms-brand.svg") }}';
    var agentTagline = selectedOption?.dataset?.tagline || 'Business messaging';
    
    var isCarousel = document.querySelector('input[name="rcsMessageType"]:checked')?.value === 'carousel';
    var messageHtml = '';
    
    if (isCarousel && rcsCardCount > 1) {
        messageHtml = renderRcsCarouselPreview();
    } else {
        messageHtml = renderRcsCardPreview(rcsCurrentCard);
    }
    
    container.innerHTML = renderRcsPhoneFrame({
        name: agentName,
        logo: agentLogo,
        verified: true,
        tagline: agentTagline
    }, messageHtml);
    
    RcsPreviewRenderer.initCarouselBehavior('#mainPreviewContainer');
}

function toggleOptoutManagement() {
    var isEnabled = document.getElementById('enableOptoutManagement').checked;
    document.getElementById('optoutManagementSection').classList.toggle('d-none', !isEnabled);
    document.getElementById('optoutDisabledMessage').classList.toggle('d-none', isEnabled);
    if (!isEnabled) {
        document.getElementById('optoutValidationError').classList.add('d-none');
    } else {
        validateOptoutConfig();
    }
}

function toggleReplyOptout() {
    var isEnabled = document.getElementById('enableReplyOptout').checked;
    document.getElementById('replyOptoutConfig').classList.toggle('d-none', !isEnabled);
    
    var urlOptoutCheckbox = document.getElementById('enableUrlOptout');
    var urlOptoutContainer = urlOptoutCheckbox ? urlOptoutCheckbox.closest('.p-3.border.rounded') : null;
    
    if (isEnabled) {
        if (urlOptoutCheckbox) {
            urlOptoutCheckbox.checked = false;
            urlOptoutCheckbox.disabled = true;
            document.getElementById('urlOptoutConfig').classList.add('d-none');
        }
        if (urlOptoutContainer) {
            urlOptoutContainer.classList.add('opacity-50');
        }
    } else {
        if (urlOptoutCheckbox) {
            urlOptoutCheckbox.disabled = false;
        }
        if (urlOptoutContainer) {
            urlOptoutContainer.classList.remove('opacity-50');
        }
    }
    validateOptoutConfig();
}

function toggleUrlOptout() {
    var isEnabled = document.getElementById('enableUrlOptout').checked;
    document.getElementById('urlOptoutConfig').classList.toggle('d-none', !isEnabled);
    
    var replyOptoutCheckbox = document.getElementById('enableReplyOptout');
    var replyOptoutContainer = replyOptoutCheckbox ? replyOptoutCheckbox.closest('.p-3.border.rounded') : null;
    
    if (isEnabled) {
        if (replyOptoutCheckbox) {
            replyOptoutCheckbox.checked = false;
            replyOptoutCheckbox.disabled = true;
            document.getElementById('replyOptoutConfig').classList.add('d-none');
        }
        if (replyOptoutContainer) {
            replyOptoutContainer.classList.add('opacity-50');
        }
    } else {
        if (replyOptoutCheckbox) {
            replyOptoutCheckbox.disabled = false;
        }
        if (replyOptoutContainer) {
            replyOptoutContainer.classList.remove('opacity-50');
        }
    }
    validateOptoutConfig();
}

var optoutAddedToMessage = { reply: false, url: false };

function addOptoutToMessage(type) {
    var textInput = type === 'reply' ? document.getElementById('replyOptoutText') : document.getElementById('urlOptoutText');
    var messageArea = document.getElementById('smsContent');
    
    if (!textInput || !messageArea) return;
    
    if (optoutAddedToMessage[type]) {
        alert('Opt-out text has already been added to the message.');
        return;
    }
    
    var optoutText = textInput.value.trim();
    if (!optoutText) {
        alert('Please enter opt-out text first.');
        return;
    }
    
    var currentContent = messageArea.value;
    var separator = currentContent.trim() ? '\n\n' : '';
    messageArea.value = currentContent + separator + optoutText;
    
    optoutAddedToMessage[type] = true;
    
    updateCharCount();
    updatePreview();
}

document.addEventListener('DOMContentLoaded', function() {
    var replyTargetRadios = document.querySelectorAll('input[name="replyOptoutTarget"]');
    replyTargetRadios.forEach(function(radio) {
        radio.addEventListener('change', function() {
            var replyNewFields = document.getElementById('replyNewListFields');
            if (replyNewFields) {
                replyNewFields.classList.toggle('d-none', this.value !== 'new');
            }
            validateOptoutConfig();
        });
    });
    
    var urlTargetRadios = document.querySelectorAll('input[name="urlOptoutTarget"]');
    urlTargetRadios.forEach(function(radio) {
        radio.addEventListener('change', function() {
            var urlNewFields = document.getElementById('urlNewListFields');
            if (urlNewFields) {
                urlNewFields.classList.toggle('d-none', this.value !== 'new');
            }
            validateOptoutConfig();
        });
    });
    
    var replyNewListName = document.getElementById('replyNewListName');
    if (replyNewListName) {
        replyNewListName.addEventListener('input', validateOptoutConfig);
    }
    
    var urlNewListName = document.getElementById('urlNewListName');
    if (urlNewListName) {
        urlNewListName.addEventListener('input', validateOptoutConfig);
    }
    
    var replyVirtualNumber = document.getElementById('replyVirtualNumber');
    if (replyVirtualNumber) {
        replyVirtualNumber.addEventListener('change', validateOptoutConfig);
    }
    
    var replyOptoutText = document.getElementById('replyOptoutText');
    if (replyOptoutText) {
        replyOptoutText.addEventListener('input', validateOptoutConfig);
    }
    
    var urlOptoutText = document.getElementById('urlOptoutText');
    if (urlOptoutText) {
        urlOptoutText.addEventListener('input', validateOptoutConfig);
    }
    
    var optoutListSelect = document.getElementById('optoutListSelect');
    if (optoutListSelect) {
        optoutListSelect.addEventListener('change', validateOptoutConfig);
    }
});

function validateOptoutConfig() {
    var isEnabled = document.getElementById('enableOptoutManagement').checked;
    if (!isEnabled) {
        document.getElementById('optoutValidationError').classList.add('d-none');
        return true;
    }
    
    var optoutListValue = document.getElementById('optoutListSelect').value;
    var hasListSelected = optoutListValue !== '';
    var replyEnabled = document.getElementById('enableReplyOptout') ? document.getElementById('enableReplyOptout').checked : false;
    var urlEnabled = document.getElementById('enableUrlOptout').checked;
    var errorDiv = document.getElementById('optoutValidationError');
    var errorMsg = document.getElementById('optoutValidationMessage');
    
    if (!hasListSelected && !replyEnabled && !urlEnabled) {
        errorMsg.textContent = 'When no opt-out list is selected, you must enable an opt-out method (Reply-based or Click-to-opt-out).';
        errorDiv.classList.remove('d-none');
        return false;
    }
    
    if (replyEnabled) {
        var virtualNumber = document.getElementById('replyVirtualNumber').value;
        var replyText = document.getElementById('replyOptoutText').value.trim();
        
        if (!virtualNumber) {
            errorMsg.textContent = 'Please select a virtual number for reply-based opt-out.';
            errorDiv.classList.remove('d-none');
            return false;
        }
        
        if (!replyText) {
            errorMsg.textContent = 'Opt-out text cannot be empty for reply-based opt-out.';
            errorDiv.classList.remove('d-none');
            return false;
        }
        
        var replyTarget = document.querySelector('input[name="replyOptoutTarget"]:checked');
        if (replyTarget && replyTarget.value === 'new') {
            var replyNewName = document.getElementById('replyNewListName');
            if (replyNewName && !replyNewName.value.trim()) {
                errorMsg.textContent = 'Please enter a name for the new opt-out list (reply-based).';
                errorDiv.classList.remove('d-none');
                return false;
            }
        }
    }
    
    if (urlEnabled) {
        var urlText = document.getElementById('urlOptoutText').value.trim();
        
        if (!urlText.includes('{' + '{unique_url}' + '}')) {
            errorMsg.textContent = 'URL opt-out text must include the {' + '{unique_url}' + '} token.';
            errorDiv.classList.remove('d-none');
            return false;
        }
        
        var urlTarget = document.querySelector('input[name="urlOptoutTarget"]:checked');
        if (urlTarget && urlTarget.value === 'new') {
            var urlNewName = document.getElementById('urlNewListName');
            if (urlNewName && !urlNewName.value.trim()) {
                errorMsg.textContent = 'Please enter a name for the new opt-out list (URL-based).';
                errorDiv.classList.remove('d-none');
                return false;
            }
        }
    }
    
    errorDiv.classList.add('d-none');
    return true;
}

function getOptoutConfiguration() {
    var isEnabled = document.getElementById('enableOptoutManagement').checked;
    if (!isEnabled) {
        return null;
    }
    
    var config = {
        enabled: true,
        optout_list_id: document.getElementById('optoutListSelect').value,
        reply_optout: null,
        url_optout: null
    };
    
    var replyEnabled = document.getElementById('enableReplyOptout') ? document.getElementById('enableReplyOptout').checked : false;
    if (replyEnabled) {
        config.reply_optout = {
            virtual_number_id: document.getElementById('replyVirtualNumber').value,
            text: document.getElementById('replyOptoutText').value,
            target: document.querySelector('input[name="replyOptoutTarget"]:checked').value,
            new_list_name: document.getElementById('replyNewListName') ? document.getElementById('replyNewListName').value : null,
            new_list_desc: document.getElementById('replyNewListDesc') ? document.getElementById('replyNewListDesc').value : null
        };
    }
    
    var urlEnabled = document.getElementById('enableUrlOptout').checked;
    if (urlEnabled) {
        config.url_optout = {
            domain_id: document.getElementById('urlOptoutDomain').value,
            text: document.getElementById('urlOptoutText').value,
            target: document.querySelector('input[name="urlOptoutTarget"]:checked').value,
            new_list_name: document.getElementById('urlNewListName').value,
            new_list_desc: document.getElementById('urlNewListDesc').value
        };
    }
    
    return config;
}

function collectCampaignConfig() {
    var channel = document.querySelector('input[name="channel"]:checked');
    var channelValue = channel ? channel.value : 'sms';
    
    // Map frontend channel values to backend expected values
    var channelMap = {
        'sms': 'sms_only',
        'rcs_basic': 'basic_rcs',
        'rcs_rich': 'rich_rcs'
    };
    var mappedChannel = channelMap[channelValue] || 'sms_only';
    
    var senderSelect = document.getElementById('senderIdSelect');
    var senderId = senderSelect ? senderSelect.value : '';
    
    var rcsAgentSelect = document.getElementById('rcsAgentSelect');
    var rcsAgent = (rcsAgentSelect && rcsAgentSelect.value) ? rcsAgentSelect.value : null;
    
    var smsContent = document.getElementById('smsContent').value.trim();
    
    var templateSelect = document.getElementById('templateSelect');
    var templateName = (templateSelect && templateSelect.value) ? templateSelect.options[templateSelect.selectedIndex].text : null;
    
    var trackableLink = document.getElementById('enableTrackableLink');
    var trackableLinkEnabled = trackableLink ? trackableLink.checked : false;
    
    var optoutCheckbox = document.getElementById('optoutMessage');
    var optoutEnabled = optoutCheckbox ? optoutCheckbox.checked : false;
    
    var recipientsList = [];
    if (recipientState && recipientState.manual && recipientState.manual.valid) {
        recipientsList = recipientsList.concat(recipientState.manual.valid);
    }
    recipientState.files.forEach(function(f) {
        recipientsList = recipientsList.concat(f.valid);
    });
    if (recipientState && recipientState.contacts && recipientState.contacts.valid) {
        recipientsList = recipientsList.concat(recipientState.contacts.valid);
    }
    
    var manualCount = recipientState.manual.valid.length;
    var uploadCount = recipientState.files.reduce(function(acc, f) { return acc + f.valid.length; }, 0);
    var contactsCount = recipientState.contactBook.contacts.reduce(function(acc, c) { return acc + (c.count || 1); }, 0);
    var listsCount = recipientState.contactBook.lists.reduce(function(acc, l) { return acc + (l.count || 0); }, 0);
    var dynamicListsCount = recipientState.contactBook.dynamicLists.reduce(function(acc, l) { return acc + (l.count || 0); }, 0);
    var tagsCount = recipientState.contactBook.tags.reduce(function(acc, t) { return acc + (t.count || 0); }, 0);
    var totalRecipientCount = manualCount + uploadCount + contactsCount + listsCount + dynamicListsCount + tagsCount;
    
    return {
        channel: mappedChannel,
        sender_id: senderId,
        rcs_agent: rcsAgent,
        message_content: smsContent,
        template: templateName,
        trackable_link: trackableLinkEnabled,
        optout_enabled: optoutEnabled,
        recipients: recipientsList,
        recipient_count: totalRecipientCount,
        valid_count: totalRecipientCount,
        invalid_count: recipientState.manual.invalid.length + recipientState.files.reduce(function(acc, f) { return acc + f.invalid.length; }, 0),
        sources: {
            manual_input: manualCount,
            file_upload: uploadCount,
            contacts: contactsCount,
            lists: listsCount,
            dynamic_lists: dynamicListsCount,
            tags: tagsCount
        }
    };
}

var saveDraftModal = null;
var draftSavedModal = null;

function saveDraft() {
    if (!saveDraftModal) {
        saveDraftModal = new bootstrap.Modal(document.getElementById('saveDraftModal'));
    }
    
    var channel = document.querySelector('input[name="channel"]:checked');
    var channelLabel = 'SMS';
    if (channel) {
        if (channel.value === 'basic_rcs') channelLabel = 'Basic RCS';
        else if (channel.value === 'rich_rcs') channelLabel = 'Rich RCS';
        else channelLabel = 'SMS';
    }
    
    var recipientCount = document.getElementById('recipientCount');
    var recipients = recipientCount ? recipientCount.textContent : '0';
    
    var messageContent = document.getElementById('smsContent').value.trim();
    var messagePreview = messageContent ? (messageContent.substring(0, 30) + (messageContent.length > 30 ? '...' : '')) : 'No content';
    
    document.getElementById('draftSummaryChannel').textContent = channelLabel;
    document.getElementById('draftSummaryRecipients').textContent = recipients;
    document.getElementById('draftSummaryMessage').textContent = messagePreview;
    
    var existingName = document.getElementById('draftName').value;
    if (!existingName) {
        var today = new Date();
        var defaultName = 'Draft - ' + today.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
        document.getElementById('draftName').value = defaultName;
    }
    
    saveDraftModal.show();
}

function confirmSaveDraft() {
    var draftName = document.getElementById('draftName').value.trim();
    if (!draftName) {
        document.getElementById('draftName').classList.add('is-invalid');
        document.getElementById('draftName').focus();
        return;
    }
    document.getElementById('draftName').classList.remove('is-invalid');
    
    var config = collectCampaignConfig();
    
    var draftData = {
        id: 'draft_' + Date.now(),
        name: draftName,
        channel: config.channel,
        sender_id: config.sender_id || 'Not set',
        rcs_agent: config.rcs_agent || null,
        status: 'draft',
        send_date: null,
        recipients: config.recipients ? config.recipients.length : 0,
        delivered: 0,
        failed: 0,
        message_content: config.message_content,
        tags: [],
        template: config.template || null,
        has_tracking: config.trackable_link ? 'yes' : 'no',
        has_optout: config.optout_enabled ? 'yes' : 'no',
        created_at: new Date().toISOString(),
        config: config
    };
    
    var drafts = JSON.parse(localStorage.getItem('quicksms_drafts') || '[]');
    drafts.unshift(draftData);
    localStorage.setItem('quicksms_drafts', JSON.stringify(drafts));
    
    saveDraftModal.hide();
    
    if (!draftSavedModal) {
        draftSavedModal = new bootstrap.Modal(document.getElementById('draftSavedModal'));
    }
    draftSavedModal.show();
    
    console.log('Draft saved:', draftData);
}

function clearValidationErrors() {
    document.querySelectorAll('.validation-error-field').forEach(function(el) {
        el.classList.remove('validation-error-field');
    });
}

function markFieldAsError(fieldId) {
    var field = document.getElementById(fieldId);
    if (field) {
        field.classList.add('validation-error-field');
        field.addEventListener('input', function handleInput() {
            field.classList.remove('validation-error-field');
            field.removeEventListener('input', handleInput);
        }, { once: true });
        field.addEventListener('change', function handleChange() {
            field.classList.remove('validation-error-field');
            field.removeEventListener('change', handleChange);
        }, { once: true });
    }
}

function showValidationErrors(errors) {
    var list = document.getElementById('validationErrorsList');
    list.innerHTML = '';
    
    errors.forEach(function(error) {
        var li = document.createElement('li');
        li.className = 'd-flex align-items-center mb-2';
        li.innerHTML = '<i class="fas fa-times-circle text-danger me-2"></i><span>' + error.message + '</span>';
        list.appendChild(li);
        
        if (error.fieldId) {
            markFieldAsError(error.fieldId);
        }
    });
    
    var modal = new bootstrap.Modal(document.getElementById('validationErrorsModal'));
    modal.show();
}

function continueToConfirmation() {
    clearValidationErrors();
    var errors = [];
    
    var campaignName = document.getElementById('campaignName').value;
    if (!campaignName) {
        var now = new Date();
        campaignName = 'Campaign - ' + now.toISOString().slice(0, 16).replace('T', ' ');
        document.getElementById('campaignName').value = campaignName;
    }
    
    var senderId = document.getElementById('senderId').value;
    if (!senderId) {
        errors.push({ fieldId: 'senderId', message: 'Sender ID is required' });
    }
    
    var smsContent = document.getElementById('smsContent').value;
    if (!smsContent.trim()) {
        errors.push({ fieldId: 'smsContent', message: 'Message content is required' });
    }
    
    var manualNumbers = document.getElementById('manualNumbers').value.trim();
    var hasRecipients = manualNumbers.length > 0 || 
        (recipientState && recipientState.manual && recipientState.manual.valid && recipientState.manual.valid.length > 0) ||
        (recipientState && recipientState.files && recipientState.files.reduce(function(acc, f) { return acc + f.valid.length; }, 0) > 0) ||
        (recipientState && recipientState.contactBook && (
            recipientState.contactBook.contacts.length > 0 ||
            recipientState.contactBook.lists.length > 0 ||
            recipientState.contactBook.dynamicLists.length > 0 ||
            recipientState.contactBook.tags.length > 0
        ));
    
    if (!hasRecipients) {
        errors.push({ fieldId: 'manualNumbers', message: 'At least one recipient is required' });
    }
    
    var optoutEnabled = document.getElementById('enableOptoutManagement') && document.getElementById('enableOptoutManagement').checked;
    if (optoutEnabled && !validateOptoutConfig()) {
        errors.push({ fieldId: null, message: 'Opt-out configuration has errors that must be fixed' });
    }
    
    if (errors.length > 0) {
        showValidationErrors(errors);
        return;
    }
    
    var optoutConfig = getOptoutConfiguration();
    
    var channel = document.querySelector('input[name="channel"]:checked');
    var channelValue = channel ? channel.value : 'sms';
    
    var apiChannelMap = {
        'sms': 'sms',
        'rcs_basic': 'rcs_basic',
        'rcs_rich': 'rcs_single'
    };
    var apiChannelValue = apiChannelMap[channelValue] || 'sms';

    var sessionChannelMap = {
        'sms': 'sms_only',
        'rcs_basic': 'basic_rcs',
        'rcs_rich': 'rich_rcs'
    };
    var sessionChannelValue = sessionChannelMap[channelValue] || 'sms_only';
    
    var senderIdSelect = document.getElementById('senderId');
    var senderIdText = senderIdSelect && senderIdSelect.selectedIndex > 0 ? senderIdSelect.options[senderIdSelect.selectedIndex].text : senderId;
    
    var rcsAgentSelect = document.getElementById('rcsAgent');
    var rcsAgentName = rcsAgentSelect && rcsAgentSelect.selectedIndex > 0 ? rcsAgentSelect.options[rcsAgentSelect.selectedIndex].text : null;
    var rcsAgentId = rcsAgentSelect ? rcsAgentSelect.value : null;
    
    var manualCount = recipientState.manual.valid.length;
    var uploadCount = recipientState.files.reduce(function(acc, f) { return acc + f.valid.length; }, 0);
    var contactsCount = recipientState.contactBook.contacts.length;
    var listsCount = recipientState.contactBook.lists.reduce(function(acc, l) { return acc + (l.count || 0); }, 0);
    var dynamicListsCount = recipientState.contactBook.dynamicLists.reduce(function(acc, l) { return acc + (l.count || 0); }, 0);
    var tagsCount = recipientState.contactBook.tags.reduce(function(acc, t) { return acc + (t.count || 0); }, 0);
    var recipientCount = manualCount + uploadCount + contactsCount + listsCount + dynamicListsCount + tagsCount;
    var invalidCount = recipientState.manual.invalid.length + recipientState.files.reduce(function(acc, f) { return acc + f.invalid.length; }, 0);
    
    var scheduledTimeValue = 'now';
    var scheduledAt = null;
    var scheduleToggle = document.getElementById('scheduleToggle');
    if (scheduleToggle && scheduleToggle.checked) {
        var dateInput = document.getElementById('scheduleDate');
        var timeInput = document.getElementById('scheduleTime');
        if (dateInput && timeInput && dateInput.value && timeInput.value) {
            scheduledTimeValue = dateInput.value + ' ' + timeInput.value;
            scheduledAt = dateInput.value + 'T' + timeInput.value + ':00';
        }
    }

    var sendingWindowValue = null;
    var unsociableToggle = document.getElementById('unsociableToggle');
    if (unsociableToggle && unsociableToggle.checked) {
        var fromVal = document.getElementById('unsociableFrom');
        var toVal = document.getElementById('unsociableTo');
        if (fromVal && toVal && fromVal.value && toVal.value) {
            sendingWindowValue = 'Quiet hours: ' + fromVal.value + ' - ' + toVal.value;
        }
    }

    var messageExpiry = null;
    if (document.getElementById('messageExpiry') && document.getElementById('messageExpiry').checked) {
        var expiryVal = document.getElementById('messageExpiryValue');
        if (expiryVal) messageExpiry = expiryVal.textContent;
    }

    var recipientSources = [];
    if (recipientState.manual.valid.length > 0) {
        recipientSources.push({ type: 'manual', numbers: recipientState.manual.valid });
    }
    recipientState.files.forEach(function(f) {
        if (f.valid.length > 0) {
            recipientSources.push({ type: 'manual', numbers: f.valid });
        }
    });
    recipientState.contactBook.lists.forEach(function(l) {
        recipientSources.push({ type: 'list', id: l.id, name: l.name });
    });
    recipientState.contactBook.tags.forEach(function(t) {
        recipientSources.push({ type: 'tag', id: t.id, name: t.name });
    });
    if (recipientState.contactBook.contacts.length > 0) {
        recipientSources.push({ type: 'individual', contact_ids: recipientState.contactBook.contacts.map(function(c) { return c.id; }) });
    }

    var continueBtn = document.getElementById('continueBtn') || document.querySelector('[onclick*="continueToConfirmation"]');
    if (continueBtn) {
        continueBtn.disabled = true;
        continueBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Creating campaign...';
    }

    var validityPeriod = null;
    if (messageExpiry) {
        var match = messageExpiry.match(/(\d+)/);
        if (match) validityPeriod = parseInt(match[1], 10);
    }

    var sendingWindowStartVal = null;
    var sendingWindowEndVal = null;
    var unsociableToggleEl = document.getElementById('unsociableToggle');
    if (unsociableToggleEl && unsociableToggleEl.checked) {
        var fromEl = document.getElementById('unsociableFrom');
        var toEl = document.getElementById('unsociableTo');
        if (fromEl && fromEl.value) sendingWindowStartVal = fromEl.value;
        if (toEl && toEl.value) sendingWindowEndVal = toEl.value;
    }

    var campaignData = {
        name: campaignName,
        type: apiChannelValue,
        message_content: smsContent,
        sender_id_id: senderId || null,
        rcs_agent_id: rcsAgentId || null,
        recipient_sources: recipientSources,
        scheduled_at: scheduledAt,
        validity_period: validityPeriod,
        sending_window_start: sendingWindowStartVal,
        sending_window_end: sendingWindowEndVal
    };

    CampaignService.create(campaignData).then(function(result) {
        var campaignId = result && result.data ? result.data.id : null;
        if (!campaignId) {
            if (continueBtn) {
                continueBtn.disabled = false;
                continueBtn.innerHTML = '<i class="fas fa-arrow-right me-1"></i> Continue';
            }
            showValidationErrors([{ fieldId: null, message: 'Failed to create campaign. Please try again.' }]);
            return;
        }

        var sessionConfig = {
            campaign_id: campaignId,
            campaign_name: campaignName,
            channel: sessionChannelValue,
            sender_id: senderIdText,
            rcs_agent: rcsAgentName,
            message_content: smsContent,
            recipient_count: recipientCount,
            valid_count: recipientCount,
            invalid_count: invalidCount,
            opted_out_count: 0,
            sources: {
                manual_input: manualCount,
                file_upload: uploadCount,
                contacts: contactsCount,
                lists: listsCount,
                dynamic_lists: dynamicListsCount,
                tags: tagsCount
            },
            scheduled_time: scheduledTimeValue,
            message_expiry: messageExpiry,
            sending_window: sendingWindowValue,
            optout_config: optoutConfig
        };

        if (continueBtn) {
            continueBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Preparing campaign...';
        }

        return fetch('/api/campaigns/' + campaignId + '/prepare', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        }).then(function(prepResp) {
            if (!prepResp.ok) {
                console.warn('[Campaign] Prepare failed with status ' + prepResp.status + ', continuing with flat estimate');
                return null;
            }
            return prepResp.json();
        }).then(function(prepResult) {
            if (prepResult && prepResult.success && prepResult.data && prepResult.data.resolver_result) {
                var rr = prepResult.data.resolver_result;
                sessionConfig.recipient_count = rr.total_resolved || recipientCount;
                sessionConfig.valid_count = rr.total_created || (rr.total_resolved - (rr.total_opted_out || 0) - (rr.total_invalid || 0));
                sessionConfig.invalid_count = rr.total_invalid || invalidCount;
                sessionConfig.opted_out_count = rr.total_opted_out || 0;
            }

            return fetch('{{ route("messages.store-campaign-config") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(sessionConfig)
            });
        }).then(function() {
            window.location.href = '{{ route("messages.confirm") }}?campaign_id=' + campaignId;
        });
    }).catch(function(error) {
        if (continueBtn) {
            continueBtn.disabled = false;
            continueBtn.innerHTML = '<i class="fas fa-arrow-right me-1"></i> Continue';
        }
        if (error.validationErrors) {
            var errorList = [];
            Object.keys(error.validationErrors).forEach(function(field) {
                errorList.push({ fieldId: null, message: error.validationErrors[field][0] });
            });
            showValidationErrors(errorList);
        } else {
            showValidationErrors([{ fieldId: null, message: error.message || 'Failed to create campaign. Please try again.' }]);
        }
    });
}

function updateOptoutCount() {
    console.log('TODO: Calculate total excluded from selected opt-out lists');
}

var recipientState = {
    manual: { valid: [], invalid: [] },
    files: [],
    contactBook: { contacts: [], lists: [], dynamicLists: [], tags: [] },
    ukMode: true,
    convert07: true
};

function loadPreselectedContacts() {
    var urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('from') !== 'contacts') return;
    
    var storedData = sessionStorage.getItem('sendMessageRecipients');
    if (!storedData) return;
    
    try {
        var data = JSON.parse(storedData);
        if (data.source !== 'contacts' || !data.contactIds || data.contactIds.length === 0) return;
        
        for (var i = 0; i < data.contactIds.length; i++) {
            recipientState.contactBook.contacts.push({
                id: data.contactIds[i],
                name: data.contactNames[i] || 'Contact',
                mobile: data.contactMobiles[i] || '',
                count: 1
            });
        }
        
        updateContactBookDisplay();
        updateRecipientSummary();
        
        sessionStorage.removeItem('sendMessageRecipients');
        
        console.log('Loaded ' + data.contactIds.length + ' contacts from contact book');
    } catch (e) {
        console.error('Error loading preselected contacts:', e);
    }
}

function toggleUkMode() {
    var ukMode = document.getElementById('ukNumbersOnly').checked;
    if (!ukMode) {
        var modal = new bootstrap.Modal(document.getElementById('ukModeModal'));
        modal.show();
    } else {
        recipientState.ukMode = true;
        recipientState.convert07 = true;
        revalidateNumbers();
    }
}

function cancelUkModeChange() {
    document.getElementById('ukNumbersOnly').checked = true;
    recipientState.ukMode = true;
    bootstrap.Modal.getInstance(document.getElementById('ukModeModal')).hide();
}

function confirmUkModeChange() {
    recipientState.ukMode = false;
    recipientState.convert07 = document.getElementById('convert07ToUk').checked;
    bootstrap.Modal.getInstance(document.getElementById('ukModeModal')).hide();
    revalidateNumbers();
    console.log('TODO: Log UK mode confirmation for audit');
}

function validateManualNumbers() {
    var input = document.getElementById('manualNumbers').value.trim();
    if (!input) {
        document.getElementById('manualValidation').classList.add('d-none');
        updateRecipientSummary();
        return;
    }
    
    var numbers = input.split(/[\s,\n]+/).filter(n => n);
    var valid = [];
    var invalid = [];
    var seen = new Set();
    
    numbers.forEach(function(num, idx) {
        var cleaned = num.replace(/[^\d+]/g, '');
        var result = normalizeNumber(cleaned);
        
        if (result.valid && !seen.has(result.number)) {
            seen.add(result.number);
            valid.push(result.number);
        } else if (!result.valid) {
            invalid.push({ row: idx + 1, original: num, reason: result.reason });
        }
    });
    
    recipientState.manual.valid = valid;
    recipientState.manual.invalid = invalid;
    
    document.getElementById('manualValidation').classList.remove('d-none');
    document.getElementById('manualValid').textContent = valid.length;
    document.getElementById('manualInvalid').textContent = invalid.length;
    document.getElementById('manualInvalidLink').classList.toggle('d-none', invalid.length === 0);
    
    updateRecipientSummary();
}

function normalizeNumber(num) {
    if (num.startsWith('+')) {
        num = num.substring(1);
    }
    
    if (recipientState.ukMode) {
        if (num.startsWith('07') && num.length === 11) {
            return { valid: true, number: '44' + num.substring(1) };
        }
        if (num.startsWith('447') && (num.length === 12 || num.length === 13)) {
            return { valid: true, number: num };
        }
        if (num.startsWith('7') && num.length === 10) {
            return { valid: true, number: '44' + num };
        }
        if (!num.startsWith('44')) {
            return { valid: false, reason: 'Non-UK number (UK mode enabled)' };
        }
        return { valid: true, number: num };
    } else {
        if (num.startsWith('07') && num.length === 11) {
            if (recipientState.convert07) {
                return { valid: true, number: '44' + num.substring(1) };
            }
            return { valid: false, reason: 'Ambiguous 07 format (enable conversion)' };
        }
        if (num.length >= 10 && num.length <= 15) {
            return { valid: true, number: num };
        }
        return { valid: false, reason: 'Invalid number format' };
    }
}

function revalidateNumbers() {
    validateManualNumbers();
    recipientState.files.forEach(function(file) {
        var allNumbers = file.valid.concat(file.invalid.map(function(inv) { return inv.original; }));
        var newValid = [];
        var newInvalid = [];
        allNumbers.forEach(function(num, idx) {
            var cleaned = String(num).replace(/[\s\-\(\)]/g, '').replace(/^\+/, '');
            if (cleaned.match(/^\d{10,15}$/)) {
                newValid.push(cleaned);
            } else {
                newInvalid.push({ row: idx + 1, original: num, reason: 'Invalid format' });
            }
        });
        file.valid = newValid;
        file.invalid = newInvalid;
    });
    renderUploadedFiles();
    updateRecipientSummary();
}

var csvCurrentStep = 1;
var csvFileData = null;
var csvValidationResults = null;

var pendingFiles = [];
var currentProcessingFileIndex = -1;

function triggerFileUpload() {
    if (recipientState.files.length >= 5) {
        showCsvAlert('Maximum <strong>5 files</strong> allowed.');
        return;
    }
    csvCurrentStep = 1;
    csvFileData = null;
    csvValidationResults = null;
    csvShowStep(1);
    document.getElementById('csvSelectedFileInfo').classList.add('d-none');
    document.getElementById('csvDropZone').classList.remove('d-none');
    document.getElementById('csvFileInput').value = '';
    document.getElementById('csvNextBtn').disabled = true;
    var modal = new bootstrap.Modal(document.getElementById('csvUploadModal'));
    modal.show();
}

document.addEventListener('DOMContentLoaded', function() {
    var csvFileInput = document.getElementById('csvFileInput');
    if (csvFileInput) {
        csvFileInput.addEventListener('change', function(e) {
            csvHandleFile(e.target.files[0]);
        });
    }

    var csvDZ = document.getElementById('csvDropZone');
    if (csvDZ) {
        csvDZ.addEventListener('click', function(e) {
            if (e.target.tagName !== 'BUTTON' && e.target.tagName !== 'INPUT') {
                document.getElementById('csvFileInput').click();
            }
        });
        csvDZ.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.style.borderColor = '#6c5ce7';
            this.style.backgroundColor = '#e8e0f5';
        });
        csvDZ.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.style.borderColor = '#886CC0';
            this.style.backgroundColor = '#f0ebf8';
        });
        csvDZ.addEventListener('drop', function(e) {
            e.preventDefault();
            this.style.borderColor = '#886CC0';
            this.style.backgroundColor = '#f0ebf8';
            if (e.dataTransfer.files.length) {
                csvHandleFile(e.dataTransfer.files[0]);
            }
        });
    }
});

function csvHandleFile(file) {
    if (!file) return;
    var ext = file.name.substring(file.name.lastIndexOf('.')).toLowerCase();
    var validExtensions = ['.csv', '.xlsx', '.xls'];
    if (!validExtensions.includes(ext)) {
        showCsvAlert('Please upload a <strong>CSV</strong> or <strong>Excel</strong> file.');
        return;
    }
    csvFileData = {
        file: file,
        name: file.name,
        size: csvFormatFileSize(file.size),
        type: ext === '.csv' ? 'csv' : 'excel',
        parsedHeaders: null,
        parsedRows: null
    };
    document.getElementById('csvSelectedFileName').textContent = file.name;
    document.getElementById('csvSelectedFileSize').textContent = csvFormatFileSize(file.size);
    document.getElementById('csvSelectedFileInfo').classList.remove('d-none');
    document.getElementById('csvDropZone').classList.add('d-none');
    document.getElementById('csvNextBtn').disabled = false;
}

function csvFormatFileSize(bytes) {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
}

function csvClearFile() {
    csvFileData = null;
    document.getElementById('csvFileInput').value = '';
    document.getElementById('csvSelectedFileInfo').classList.add('d-none');
    document.getElementById('csvDropZone').classList.remove('d-none');
    document.getElementById('csvNextBtn').disabled = true;
}

function csvShowStep(step) {
    csvCurrentStep = step;
    for (var i = 1; i <= 3; i++) {
        document.getElementById('csvStep' + i).classList.add('d-none');
        var circle = document.getElementById('csvStepCircle' + i);
        circle.style.backgroundColor = '#fff';
        circle.style.color = '#886CC0';
        circle.style.border = '2px solid #886CC0';
    }
    document.getElementById('csvStep' + step).classList.remove('d-none');
    for (var i = 1; i <= step; i++) {
        var circle = document.getElementById('csvStepCircle' + i);
        circle.style.backgroundColor = '#886CC0';
        circle.style.color = '#fff';
        circle.style.border = 'none';
    }
    document.getElementById('csvBackBtn').classList.toggle('d-none', step === 1);
    document.getElementById('csvNextBtn').classList.toggle('d-none', step >= 3);
    document.getElementById('csvConfirmBtn').classList.toggle('d-none', step !== 3);
}

function csvNextStep() {
    if (csvCurrentStep === 1) {
        csvShowStep(2);
        csvDetectColumns();
    } else if (csvCurrentStep === 2) {
        if (!csvValidateMappings()) return;
        csvShowStep(3);
        csvRunValidation();
    }
}

function csvPrevStep() {
    if (csvCurrentStep > 1) {
        csvShowStep(csvCurrentStep - 1);
    }
}

function csvParseCSVLine(line) {
    line = line.trim();
    if (line.length >= 2 && line[0] === '"' && line[line.length - 1] === '"') {
        var inner = line.substring(1, line.length - 1);
        if (inner.indexOf('"') === -1) line = inner;
    }
    var result = [];
    var current = '';
    var inQuotes = false;
    for (var i = 0; i < line.length; i++) {
        var ch = line[i];
        if (inQuotes) {
            if (ch === '"' && i + 1 < line.length && line[i + 1] === '"') {
                current += '"'; i++;
            } else if (ch === '"') {
                inQuotes = false;
            } else {
                current += ch;
            }
        } else {
            if (ch === '"') { inQuotes = true; }
            else if (ch === ',') { result.push(current.trim()); current = ''; }
            else { current += ch; }
        }
    }
    result.push(current.trim());
    return result;
}

function csvDetectColumns() {
    if (!csvFileData || !csvFileData.file) return;
    var hasHeaders = document.getElementById('csvHasHeaders').checked;

    if (csvFileData.type === 'excel') {
        if (typeof XLSX === 'undefined') {
            showCsvAlert('Excel file support is not available. Please upload a <strong>CSV</strong> file instead.');
            csvPrevStep();
            return;
        }
        var reader = new FileReader();
        reader.onload = function(e) {
            try {
                var data = new Uint8Array(e.target.result);
                var workbook = XLSX.read(data, { type: 'array' });
                var sheet = workbook.Sheets[workbook.SheetNames[0]];
                var jsonRows = XLSX.utils.sheet_to_json(sheet, { header: 1, defval: '' });
                jsonRows = jsonRows.filter(function(r) {
                    return r.some(function(cell) { return cell !== '' && cell !== null && cell !== undefined; });
                });
                if (jsonRows.length === 0) { showCsvAlert('The spreadsheet appears to be empty.'); return; }
                var headerRow = jsonRows[0].map(function(c) { return String(c); });
                var sampleRow = jsonRows.length > 1 ? jsonRows[1].map(function(c) { return String(c); }) : headerRow;
                var dataRows = jsonRows.slice(hasHeaders ? 1 : 0).map(function(r) { return r.map(function(c) { return String(c); }); });
                csvBuildMappingUI(headerRow, sampleRow, dataRows, hasHeaders);
            } catch (err) {
                console.error('[CSV Upload] Excel parse error:', err);
                showCsvAlert('Could not read the Excel file: ' + escapeContactHtml(err.message || 'Unknown error') + '. Please check the format and try again.');
            }
        };
        reader.readAsArrayBuffer(csvFileData.file);
    } else {
        var reader = new FileReader();
        reader.onload = function(e) {
            var text = e.target.result;
            var lines = text.split(/\r?\n/).filter(function(l) { return l.trim().length > 0; });
            if (lines.length === 0) return;
            var headerRow = csvParseCSVLine(lines[0]);
            var sampleRow = lines.length > 1 ? csvParseCSVLine(lines[1]) : headerRow;
            var dataRows = lines.slice(hasHeaders ? 1 : 0).map(csvParseCSVLine);
            csvBuildMappingUI(headerRow, sampleRow, dataRows, hasHeaders);
        };
        reader.readAsText(csvFileData.file);
    }
}

function csvBuildMappingUI(headerRow, sampleRow, allDataRows, hasHeaders) {
    csvFileData.parsedHeaders = headerRow;
    csvFileData.parsedRows = allDataRows;

    var columns = hasHeaders
        ? headerRow
        : headerRow.map(function(_, i) { return 'Column ' + String.fromCharCode(65 + i); });
    var samples = hasHeaders ? sampleRow : headerRow;

    var tbody = document.getElementById('csvColumnMappingBody');
    tbody.innerHTML = '';

    var mappingOptions = '<option value="">-- Do not import --</option>' +
        '<option value="mobile">Mobile Number *</option>' +
        '<option value="first_name">First Name</option>' +
        '<option value="last_name">Last Name</option>' +
        '<option value="email">Email</option>' +
        '<option value="custom">Custom Field (keep as-is)</option>';

    columns.forEach(function(col, idx) {
        var colLower = String(col).toLowerCase().trim();
        var autoMap = colLower === '' ? '' : 'custom';
        if (colLower.includes('mobile') || colLower.includes('phone') || colLower.includes('msisdn') || colLower.includes('number')) autoMap = 'mobile';
        else if (colLower.includes('first')) autoMap = 'first_name';
        else if (colLower.includes('last') || colLower.includes('surname')) autoMap = 'last_name';
        else if (colLower.includes('email')) autoMap = 'email';

        var sampleVal = (samples[idx] !== undefined && samples[idx] !== null) ? String(samples[idx]) : '';
        var row = document.createElement('tr');
        row.innerHTML = '<td><strong>' + escapeContactHtml(col) + '</strong></td>' +
            '<td><select class="form-select form-select-sm csv-column-mapping" data-column="' + idx + '">' + mappingOptions + '</select></td>' +
            '<td class="text-muted small">' + escapeContactHtml(sampleVal) + '</td>';
        tbody.appendChild(row);

        if (autoMap) {
            row.querySelector('select').value = autoMap;
        }
    });

    var mobileColIdx = -1;
    columns.forEach(function(col, idx) {
        var sel = tbody.querySelectorAll('.csv-column-mapping')[idx];
        if (sel && sel.value === 'mobile') mobileColIdx = idx;
    });

    var needsNormalisation = false;
    var issues = [];
    if (mobileColIdx >= 0) {
        var checkRows = allDataRows.slice(0, Math.min(20, allDataRows.length));
        var hasLeading7 = false, hasPlus = false, hasSpaces = false, hasLeading07 = false, hasLeading44 = false;
        checkRows.forEach(function(row) {
            var val = String(row[mobileColIdx] || '');
            if (val.indexOf(' ') !== -1) hasSpaces = true;
            var cleaned = val.replace(/[\s\-]/g, '');
            if (cleaned.match(/^\+/)) hasPlus = true;
            cleaned = cleaned.replace(/^\+/, '');
            if (cleaned.match(/^07\d{9}$/)) hasLeading07 = true;
            else if (cleaned.match(/^7\d{9,}$/)) hasLeading7 = true;
            else if (cleaned.match(/^44\d{10,}$/)) hasLeading44 = true;
        });
        if (hasLeading7) issues.push("numbers starting with '7' (missing country code)");
        if (hasLeading07) issues.push("numbers starting with '07' (local UK format)");
        if (hasPlus) issues.push("numbers with '+' prefix");
        if (hasSpaces) issues.push("numbers containing spaces");
        if (hasLeading44 && (hasLeading7 || hasLeading07)) issues.push("mixed '44...' and shorter formats");
        needsNormalisation = issues.length > 0;
    }

    if (needsNormalisation) {
        document.getElementById('csvNormalisationDetail').textContent =
            'We\'ve detected mixed mobile number formats: ' + issues.join(', ') + '.';
        document.getElementById('csvNormalisationWarning').classList.remove('d-none');
    } else {
        document.getElementById('csvNormalisationWarning').classList.add('d-none');
    }
}

function csvSetNormalisation(apply) {
    document.getElementById('csvExcelCorrectionApplied').value = apply ? 'yes' : 'no';
    var content = document.getElementById('csvNormalisationContent');
    content.innerHTML =
        '<i class="fas fa-check-circle me-2" style="color: #886CC0;"></i>' +
        '<strong style="color: #886CC0;">' + (apply ? 'UK number normalisation will be applied' : 'Numbers will be left as-is') + '</strong> ' +
        '<button type="button" class="btn btn-sm btn-link" style="color: #886CC0;" onclick="csvResetNormalisation()">Change</button>';
}

function csvResetNormalisation() {
    document.getElementById('csvExcelCorrectionApplied').value = '';
    document.getElementById('csvNormalisationContent').innerHTML =
        '<i class="fas fa-exclamation-triangle me-2" style="color: #886CC0;"></i>' +
        '<strong style="color: #886CC0;">UK Number Normalisation</strong>' +
        '<p class="mb-2 mt-2 text-dark" id="csvNormalisationDetail">We\'ve detected mixed mobile number formats in your file.</p>' +
        '<p class="mb-2 text-dark">Should we normalise all numbers to international format (e.g. <code>447712345678</code>)?</p>' +
        '<div class="d-flex gap-2">' +
            '<button type="button" class="btn btn-sm text-white" style="background-color: #886CC0;" onclick="csvSetNormalisation(true)">' +
                '<i class="fas fa-check me-1"></i> Yes, normalise to UK format</button>' +
            '<button type="button" class="btn btn-sm btn-outline-secondary" onclick="csvSetNormalisation(false)">' +
                '<i class="fas fa-times me-1"></i> No, leave as-is</button>' +
        '</div>';
}

function csvValidateMappings() {
    var hasMobile = false;
    document.querySelectorAll('.csv-column-mapping').forEach(function(select) {
        if (select.value === 'mobile') hasMobile = true;
    });
    if (!hasMobile) {
        showCsvAlert('Please map at least one column to <strong>Mobile Number</strong>.');
        return false;
    }
    var normWarning = document.getElementById('csvNormalisationWarning');
    if (!normWarning.classList.contains('d-none') && document.getElementById('csvExcelCorrectionApplied').value === '') {
        showCsvAlert('Please confirm the <strong>UK number normalisation</strong> option above.');
        return false;
    }
    return true;
}

function showCsvAlert(message) {
    document.getElementById('csvAlertMessage').innerHTML = message;
    var modal = new bootstrap.Modal(document.getElementById('csvAlertModal'));
    modal.show();
}

function csvNormaliseMobile(raw, applyUkNormalisation) {
    var mobile = String(raw).replace(/[\s\-\(\)]/g, '');
    if (applyUkNormalisation) {
        mobile = mobile.replace(/^\+/, '');
        if (mobile.match(/^07\d{9}$/)) {
            mobile = '44' + mobile.substring(1);
        } else if (mobile.match(/^7\d{9,}$/)) {
            mobile = '44' + mobile;
        }
    } else {
        mobile = mobile.replace(/^\+/, '');
    }
    return mobile;
}

function csvRunValidation() {
    var rows = (csvFileData && csvFileData.parsedRows) ? csvFileData.parsedRows : [];
    var headers = (csvFileData && csvFileData.parsedHeaders) ? csvFileData.parsedHeaders : [];
    var hasHeaders = document.getElementById('csvHasHeaders').checked;
    var mappings = {};
    var customFieldNames = [];
    document.querySelectorAll('.csv-column-mapping').forEach(function(sel) {
        var colIdx = parseInt(sel.dataset.column, 10);
        if (sel.value === 'custom') {
            var headerName = hasHeaders ? headers[colIdx] : ('Column ' + String.fromCharCode(65 + colIdx));
            customFieldNames.push({ index: colIdx, name: headerName.trim() });
        } else if (sel.value) {
            mappings[sel.value] = colIdx;
        }
    });

    var mobileIdx = typeof mappings.mobile === 'number' ? mappings.mobile : -1;
    var firstNameIdx = typeof mappings.first_name === 'number' ? mappings.first_name : -1;
    var lastNameIdx = typeof mappings.last_name === 'number' ? mappings.last_name : -1;
    var emailIdx = typeof mappings.email === 'number' ? mappings.email : -1;
    var applyNormalisation = document.getElementById('csvExcelCorrectionApplied').value === 'yes';
    var seenNumbers = {};
    var duplicateCount = 0;
    var invalidCount = 0;
    var invalidRows = [];
    var validNumbers = [];
    var validData = [];

    rows.forEach(function(row, rowIdx) {
        var rawMobile = (mobileIdx >= 0 && row[mobileIdx]) ? String(row[mobileIdx]) : '';
        if (!rawMobile.trim()) return;

        var mobile = csvNormaliseMobile(rawMobile, applyNormalisation);

        if (!mobile.match(/^\d{10,15}$/)) {
            invalidCount++;
            var reason = 'Invalid format';
            if (mobile.match(/[a-zA-Z]/)) reason = 'Contains letters';
            else if (mobile.length < 10) reason = 'Too short';
            invalidRows.push({ row: rowIdx + 1, value: rawMobile, reason: reason });
            return;
        }

        if (seenNumbers[mobile]) {
            duplicateCount++;
            return;
        }
        seenNumbers[mobile] = true;
        validNumbers.push(mobile);

        var rowData = { mobile_number: mobile };
        if (firstNameIdx >= 0) rowData.first_name = (row[firstNameIdx] || '').trim();
        if (lastNameIdx >= 0) rowData.last_name = (row[lastNameIdx] || '').trim();
        if (emailIdx >= 0) rowData.email = (row[emailIdx] || '').trim();
        customFieldNames.forEach(function(cf) {
            rowData[cf.name] = (row[cf.index] || '').trim();
        });
        validData.push(rowData);
    });

    document.getElementById('csvStatTotalRows').textContent = rows.length;
    document.getElementById('csvStatUniqueNumbers').textContent = validNumbers.length;
    document.getElementById('csvStatValidNumbers').textContent = validNumbers.length;
    document.getElementById('csvStatInvalidNumbers').textContent = invalidCount;

    var indicators = document.getElementById('csvImportIndicators');
    indicators.innerHTML = '';
    if (applyNormalisation) {
        indicators.innerHTML += '<span class="badge me-2" style="background-color: #f0ebf8; color: #886CC0; border: 1px solid #886CC0;"><i class="fas fa-sync-alt me-1"></i> UK normalisation applied</span>';
    }
    if (duplicateCount > 0) {
        indicators.innerHTML += '<span class="badge" style="background-color: #fff3cd; color: #856404; border: 1px solid #ffc107;"><i class="fas fa-copy me-1"></i> ' + duplicateCount + ' duplicates removed</span>';
    }

    if (invalidRows.length > 0) {
        document.getElementById('csvInvalidRowsSection').classList.remove('d-none');
        var tbody = document.getElementById('csvInvalidRowsBody');
        tbody.innerHTML = '';
        invalidRows.forEach(function(item) {
            var row = document.createElement('tr');
            row.innerHTML = '<td>' + escapeContactHtml(String(item.row)) + '</td>' +
                '<td class="text-muted">' + escapeContactHtml(item.value) + '</td>' +
                '<td><span class="badge" style="background-color: #ffe0e0; color: #dc3545;">' + escapeContactHtml(item.reason) + '</span></td>';
            tbody.appendChild(row);
        });
    } else {
        document.getElementById('csvInvalidRowsSection').classList.add('d-none');
    }

    csvValidationResults = {
        validNumbers: validNumbers,
        validData: validData,
        invalidRows: invalidRows,
        totalRows: rows.length,
        duplicateCount: duplicateCount,
        mappings: mappings,
        customFields: customFieldNames.map(function(cf) { return cf.name; }),
        applyNormalisation: applyNormalisation
    };
}

function csvDownloadInvalidRows() {
    var csvContent = 'Row,Original Value,Reason\n';
    document.querySelectorAll('#csvInvalidRowsBody tr').forEach(function(row) {
        var cells = row.querySelectorAll('td');
        csvContent += '"' + cells[0].textContent + '","' + cells[1].textContent + '","' + cells[2].textContent + '"\n';
    });
    var blob = new Blob([csvContent], { type: 'text/csv' });
    var url = window.URL.createObjectURL(blob);
    var a = document.createElement('a');
    a.href = url;
    a.download = 'invalid_rows_' + new Date().toISOString().slice(0, 10) + '.csv';
    a.click();
    window.URL.revokeObjectURL(url);
}

function csvConfirmImport() {
    if (!csvValidationResults || csvValidationResults.validNumbers.length === 0) {
        showCsvAlert('No valid numbers found to import.');
        return;
    }

    var fileEntry = {
        id: 'file_' + Date.now() + '_' + Math.random().toString(36).substr(2, 5),
        name: csvFileData ? csvFileData.name : 'uploaded_file.csv',
        size: csvFileData ? csvFileData.size : '',
        valid: csvValidationResults.validNumbers,
        invalid: csvValidationResults.invalidRows.map(function(item) {
            return { row: item.row, original: item.value, reason: item.reason };
        }),
        data: csvValidationResults.validData || [],
        columnMapping: csvValidationResults.mappings || {},
        customFields: csvValidationResults.customFields || []
    };

    recipientState.files.push(fileEntry);
    bootstrap.Modal.getInstance(document.getElementById('csvUploadModal')).hide();
    renderUploadedFiles();
    updateUploadButtonState();
    updateRecipientSummary();
    refreshCsvFieldButtons();
}

function renderUploadedFiles() {
    var container = document.getElementById('uploadedFilesContainer');
    if (recipientState.files.length === 0) {
        container.innerHTML = '';
        return;
    }
    var html = '';
    recipientState.files.forEach(function(file) {
        html += '<div class="d-flex align-items-center p-2 mb-2 rounded" style="background-color: #f0ebf8; border: 1px solid #e0d8f0;" id="fileCard_' + file.id + '">';
        html += '<i class="fas fa-file-csv me-2" style="color: #886CC0;"></i>';
        html += '<div class="flex-grow-1">';
        html += '<div class="fw-medium" style="font-size: 13px;">' + escapeContactHtml(file.name) + ' <span class="text-muted">(' + escapeContactHtml(file.size) + ')</span></div>';
        html += '<div style="font-size: 12px;">';
        html += '<span class="text-success"><i class="fas fa-check-circle me-1"></i>' + file.valid.length + ' valid</span>';
        if (file.invalid.length > 0) {
            html += '<span class="text-danger ms-2"><i class="fas fa-times-circle me-1"></i>' + file.invalid.length + ' invalid</span>';
            html += ' <a href="#" class="ms-1" style="font-size: 11px;" onclick="showFileInvalidNumbers(\'' + file.id + '\'); return false;">View</a>';
        }
        html += '</div></div>';
        html += '<button type="button" class="btn btn-sm btn-outline-danger ms-2" onclick="removeUploadedFile(\'' + file.id + '\')" title="Remove file"><i class="fas fa-times"></i></button>';
        html += '</div>';
    });
    container.innerHTML = html;
}

function removeUploadedFile(fileId) {
    recipientState.files = recipientState.files.filter(function(f) { return f.id !== fileId; });
    renderUploadedFiles();
    updateUploadButtonState();
    updateRecipientSummary();
    refreshCsvFieldButtons();
}

function showFileInvalidNumbers(fileId) {
    var file = recipientState.files.find(function(f) { return f.id === fileId; });
    if (file) {
        showInvalidNumbersTable(file.invalid);
    }
}

function updateUploadButtonState() {
    var btn = document.getElementById('uploadCsvBtn');
    if (!btn) return;
    var count = recipientState.files.length;
    if (count >= 5) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-upload me-1"></i>Upload File <span class="badge bg-secondary ms-1">5/5</span>';
    } else if (count > 0) {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-upload me-1"></i>Upload File <span class="badge ms-1" style="background-color: #886CC0;">' + count + '/5</span>';
    } else {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-upload me-1"></i>Upload File';
    }
}

function refreshCsvFieldButtons() {
    var csvSection = document.getElementById('csvFieldsSection');
    var csvBtnContainer = document.getElementById('csvFieldButtons');
    var hintEl = document.getElementById('noCustomFieldsHint');

    var filesWithCustom = recipientState.files.filter(function(f) { return f.customFields && f.customFields.length > 0; });

    if (filesWithCustom.length === 0) {
        if (csvSection) csvSection.style.display = 'none';
        if (csvBtnContainer) csvBtnContainer.innerHTML = '';
        if (hintEl) hintEl.style.display = '';
        return;
    }

    var intersection = filesWithCustom[0].customFields.slice();
    for (var i = 1; i < filesWithCustom.length; i++) {
        intersection = intersection.filter(function(field) {
            return filesWithCustom[i].customFields.indexOf(field) !== -1;
        });
    }

    if (intersection.length === 0) {
        if (csvSection) csvSection.style.display = 'none';
        if (csvBtnContainer) csvBtnContainer.innerHTML = '';
        if (hintEl) hintEl.style.display = '';
        return;
    }

    if (csvSection) csvSection.style.display = '';
    if (hintEl) hintEl.style.display = 'none';
    var html = '';
    intersection.forEach(function(fieldName) {
        html += '<button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertPlaceholder(\'' + escapeContactHtml(fieldName.replace(/'/g, "\\'")) + '\')">{{' + escapeContactHtml(fieldName) + '}}</button>';
    });
    csvBtnContainer.innerHTML = html;
}

var cbContactsData = [];
var cbListsData = [];
var cbDynamicListsData = [];
var cbTagsData = [];
var cbDataLoaded = false;
var cbSearchTimeout = null;

function openContactBookModal() {
    var modal = new bootstrap.Modal(document.getElementById('contactBookModal'));
    modal.show();
    if (!cbDataLoaded) {
        loadContactBookData();
    }
    restoreContactBookSelections();
    updateContactBookSummary();
}

function loadContactBookData() {
    var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    var headers = { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken };

    Promise.all([
        fetch('/api/contacts?per_page=100', { headers: headers }).then(function(r) { return r.json(); }),
        fetch('/api/contact-lists', { headers: headers }).then(function(r) { return r.json(); }),
        fetch('/api/tags', { headers: headers }).then(function(r) { return r.json(); })
    ]).then(function(results) {
        cbContactsData = results[0].data || [];
        var allLists = results[1].data || [];
        cbListsData = allLists.filter(function(l) { return l.type === 'static'; });
        cbDynamicListsData = allLists.filter(function(l) { return l.type === 'dynamic'; });
        cbTagsData = results[2].data || [];
        cbDataLoaded = true;

        renderCbContacts(cbContactsData);
        renderCbLists(cbListsData);
        renderCbDynamicLists(cbDynamicListsData);
        renderCbTags(cbTagsData);
        populateTagFilter(cbTagsData);
        restoreContactBookSelections();
    }).catch(function(err) {
        console.error('Failed to load contact book data:', err);
        document.getElementById('cbContactsTable').innerHTML = '<tr><td colspan="4" class="text-center text-danger py-3">Failed to load contacts</td></tr>';
    });
}

var cbAvatarColors = [
    '#6f42c1', '#e83e8c', '#20c997', '#fd7e14', '#0d6efd',
    '#6610f2', '#d63384', '#198754', '#dc3545', '#0dcaf0'
];

function cbGetAvatarColor(name) {
    var hash = 0;
    for (var i = 0; i < name.length; i++) {
        hash = name.charCodeAt(i) + ((hash << 5) - hash);
    }
    return cbAvatarColors[Math.abs(hash) % cbAvatarColors.length];
}

function cbGetInitials(firstName, lastName) {
    var f = (firstName || '').trim();
    var l = (lastName || '').trim();
    if (f && l) return (f.charAt(0) + l.charAt(0)).toUpperCase();
    if (f) return f.substring(0, 2).toUpperCase();
    return '?';
}

function escapeContactHtml(str) {
    if (!str) return '';
    var div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

function renderCbContacts(contacts) {
    var tbody = document.getElementById('cbContactsTable');
    if (!contacts.length) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-4">No contacts found</td></tr>';
        return;
    }
    var html = '';
    contacts.forEach(function(c, idx) {
        var firstName = c.first_name || '';
        var lastName = c.last_name || '';
        var name = escapeContactHtml((firstName + ' ' + lastName).trim()) || 'Unnamed';
        var mobile = escapeContactHtml(c.mobile_masked || 'No mobile');
        var initials = cbGetInitials(firstName, lastName);
        var color = cbGetAvatarColor(firstName + lastName);
        var bgColor = idx % 2 === 1 ? 'background-color: #faf9fd;' : '';
        var tagsHtml = '';
        if (c.tags && c.tags.length) {
            c.tags.forEach(function(t) {
                tagsHtml += '<span class="badge badge-pastel-secondary me-1">' + escapeContactHtml(t) + '</span>';
            });
        }
        html += '<tr style="' + bgColor + ' border-bottom: 1px solid #f5f3fa;">' +
            '<td style="padding: 10px 8px; vertical-align: middle;"><input type="checkbox" class="form-check-input cb-contact" value="' + c.id + '" data-name="' + escapeContactHtml(name) + '"></td>' +
            '<td style="padding: 10px 8px; vertical-align: middle;">' +
                '<div class="d-flex align-items-center">' +
                    '<div class="contact-avatar me-2" style="background-color: ' + color + '20; color: ' + color + '; flex-shrink: 0;">' + escapeContactHtml(initials) + '</div>' +
                    '<span style="font-weight: 500; color: #2c2c2c;">' + name + '</span>' +
                '</div>' +
            '</td>' +
            '<td style="padding: 10px 8px; vertical-align: middle; color: #6c757d;">' + mobile + '</td>' +
            '<td style="padding: 10px 8px; vertical-align: middle;">' + tagsHtml + '</td>' +
            '</tr>';
    });
    tbody.innerHTML = html;
}

function renderCbLists(lists) {
    var tbody = document.getElementById('cbListsTable');
    if (!lists.length) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-4">No lists found</td></tr>';
        return;
    }
    var html = '';
    lists.forEach(function(l, idx) {
        var count = l.contact_count || 0;
        var updated = l.updated_at || '-';
        var bgColor = idx % 2 === 1 ? 'background-color: #faf9fd;' : '';
        var initials = (l.name || '').substring(0, 2).toUpperCase();
        html += '<tr style="' + bgColor + ' border-bottom: 1px solid #f5f3fa;">' +
            '<td style="padding: 10px 8px; vertical-align: middle;"><input type="checkbox" class="form-check-input cb-list" value="' + l.id + '" data-name="' + escapeContactHtml(l.name) + '" data-count="' + count + '"></td>' +
            '<td style="padding: 10px 8px; vertical-align: middle;">' +
                '<div class="d-flex align-items-center">' +
                    '<div class="list-icon-static me-2" style="flex-shrink: 0;"><i class="fas fa-list" style="font-size: 14px;"></i></div>' +
                    '<span style="font-weight: 500; color: #2c2c2c;">' + escapeContactHtml(l.name) + '</span>' +
                '</div>' +
            '</td>' +
            '<td style="padding: 10px 8px; vertical-align: middle;"><span class="badge badge-pastel-pink">' + count.toLocaleString() + '</span></td>' +
            '<td style="padding: 10px 8px; vertical-align: middle; color: #6c757d;">' + escapeContactHtml(updated) + '</td>' +
            '</tr>';
    });
    tbody.innerHTML = html;
}

function renderCbDynamicLists(lists) {
    var tbody = document.getElementById('cbDynamicListsTable');
    if (!lists.length) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">No dynamic lists found</td></tr>';
        return;
    }
    var html = '';
    lists.forEach(function(l, idx) {
        var count = l.contact_count || 0;
        var rules = l.rules ? JSON.stringify(l.rules).substring(0, 50) : '-';
        var evaluated = l.last_evaluated || '-';
        var bgColor = idx % 2 === 1 ? 'background-color: #faf9fd;' : '';
        html += '<tr style="' + bgColor + ' border-bottom: 1px solid #f5f3fa;">' +
            '<td style="padding: 10px 8px; vertical-align: middle;"><input type="checkbox" class="form-check-input cb-dynamic" value="' + l.id + '" data-name="' + escapeContactHtml(l.name) + '" data-count="' + count + '"></td>' +
            '<td style="padding: 10px 8px; vertical-align: middle;">' +
                '<div class="d-flex align-items-center">' +
                    '<div class="list-icon-dynamic me-2" style="flex-shrink: 0;"><i class="fas fa-sync-alt" style="font-size: 14px;"></i></div>' +
                    '<span style="font-weight: 500; color: #2c2c2c;">' + escapeContactHtml(l.name) + '</span>' +
                '</div>' +
            '</td>' +
            '<td style="padding: 10px 8px; vertical-align: middle; color: #6c757d; font-size: 12px;">' + escapeContactHtml(rules) + '</td>' +
            '<td style="padding: 10px 8px; vertical-align: middle;"><span class="badge badge-pastel-pink">' + count.toLocaleString() + '</span></td>' +
            '<td style="padding: 10px 8px; vertical-align: middle; color: #6c757d;">' + escapeContactHtml(evaluated) + '</td>' +
            '</tr>';
    });
    tbody.innerHTML = html;
}

function renderCbTags(tags) {
    var tbody = document.getElementById('cbTagsTable');
    if (!tags.length) {
        tbody.innerHTML = '<tr><td colspan="3" class="text-center text-muted py-4">No tags found</td></tr>';
        return;
    }
    var html = '';
    tags.forEach(function(t, idx) {
        var count = t.contact_count || 0;
        var color = t.color || '#6f42c1';
        var bgColor = idx % 2 === 1 ? 'background-color: #faf9fd;' : '';
        html += '<tr style="' + bgColor + ' border-bottom: 1px solid #f5f3fa;">' +
            '<td style="padding: 10px 8px; vertical-align: middle;"><input type="checkbox" class="form-check-input cb-tag" value="' + t.id + '" data-name="' + escapeContactHtml(t.name) + '" data-count="' + count + '"></td>' +
            '<td style="padding: 10px 8px; vertical-align: middle;">' +
                '<div class="d-flex align-items-center">' +
                    '<div class="contact-avatar me-2" style="background-color: ' + escapeContactHtml(color) + '20; color: ' + escapeContactHtml(color) + '; flex-shrink: 0;"><i class="fas fa-tag" style="font-size: 14px;"></i></div>' +
                    '<span class="badge badge-pastel-secondary" style="font-size: 12px;">' + escapeContactHtml(t.name) + '</span>' +
                '</div>' +
            '</td>' +
            '<td style="padding: 10px 8px; vertical-align: middle;"><span class="badge badge-pastel-info">' + count.toLocaleString() + '</span></td>' +
            '</tr>';
    });
    tbody.innerHTML = html;
}

function populateTagFilter(tags) {
    var select = document.getElementById('cbFilterTags');
    if (!select) return;
    select.innerHTML = '<option value="">All tags</option>';
    tags.forEach(function(t) {
        select.innerHTML += '<option value="' + escapeContactHtml(t.name) + '">' + escapeContactHtml(t.name) + '</option>';
    });
}

function restoreContactBookSelections() {
    recipientState.contactBook.contacts.forEach(function(c) {
        var cb = document.querySelector('.cb-contact[value="' + c.id + '"]');
        if (cb) cb.checked = true;
    });
    recipientState.contactBook.lists.forEach(function(l) {
        var cb = document.querySelector('.cb-list[value="' + l.id + '"]');
        if (cb) cb.checked = true;
    });
    recipientState.contactBook.dynamicLists.forEach(function(dl) {
        var cb = document.querySelector('.cb-dynamic[value="' + dl.id + '"]');
        if (cb) cb.checked = true;
    });
    recipientState.contactBook.tags.forEach(function(t) {
        var cb = document.querySelector('.cb-tag[value="' + t.id + '"]');
        if (cb) cb.checked = true;
    });
}

function toggleContactFilters() {
    document.getElementById('cbContactFilters').classList.toggle('d-none');
}

function clearContactFilters() {
    document.getElementById('cbFilterTags').value = '';
    document.getElementById('cbFilterMobile').value = '';
    document.getElementById('cbFilterOptout').value = 'exclude';
    renderCbContacts(cbContactsData);
}

function toggleAllContacts() {
    var checked = document.getElementById('cbSelectAllContacts').checked;
    document.querySelectorAll('.cb-contact').forEach(function(cb) {
        cb.checked = checked;
    });
    updateContactBookSummary();
}

function filterContacts() {
    clearTimeout(cbSearchTimeout);
    cbSearchTimeout = setTimeout(function() {
        var query = (document.getElementById('cbContactSearch').value || '').toLowerCase();
        if (!query) {
            renderCbContacts(cbContactsData);
            restoreContactBookSelections();
            return;
        }
        var filtered = cbContactsData.filter(function(c) {
            var name = ((c.first_name || '') + ' ' + (c.last_name || '')).toLowerCase();
            var mobile = (c.mobile_masked || '').toLowerCase();
            var tags = (c.tags || []).join(' ').toLowerCase();
            return name.indexOf(query) !== -1 || mobile.indexOf(query) !== -1 || tags.indexOf(query) !== -1;
        });
        renderCbContacts(filtered);
        restoreContactBookSelections();
    }, 300);
}

function sortContacts() {
    var sortBy = document.getElementById('cbContactSort').value;
    var sorted = cbContactsData.slice();
    if (sortBy === 'name_asc') {
        sorted.sort(function(a, b) { return ((a.first_name || '') + ' ' + (a.last_name || '')).localeCompare((b.first_name || '') + ' ' + (b.last_name || '')); });
    } else if (sortBy === 'name_desc') {
        sorted.sort(function(a, b) { return ((b.first_name || '') + ' ' + (b.last_name || '')).localeCompare((a.first_name || '') + ' ' + (a.last_name || '')); });
    } else if (sortBy === 'added') {
        sorted.sort(function(a, b) { return (b.created_at || '').localeCompare(a.created_at || ''); });
    }
    renderCbContacts(sorted);
    restoreContactBookSelections();
}

function filterLists() {
    clearTimeout(cbSearchTimeout);
    cbSearchTimeout = setTimeout(function() {
        var query = (document.getElementById('cbListSearch').value || '').toLowerCase();
        if (!query) { renderCbLists(cbListsData); restoreContactBookSelections(); return; }
        var filtered = cbListsData.filter(function(l) { return (l.name || '').toLowerCase().indexOf(query) !== -1; });
        renderCbLists(filtered);
        restoreContactBookSelections();
    }, 300);
}

function sortLists() {
    var sortBy = document.getElementById('cbListSort').value;
    var sorted = cbListsData.slice();
    if (sortBy === 'name_asc') sorted.sort(function(a, b) { return (a.name || '').localeCompare(b.name || ''); });
    else if (sortBy === 'name_desc') sorted.sort(function(a, b) { return (b.name || '').localeCompare(a.name || ''); });
    else if (sortBy === 'count_desc') sorted.sort(function(a, b) { return (b.contact_count || 0) - (a.contact_count || 0); });
    else if (sortBy === 'count_asc') sorted.sort(function(a, b) { return (a.contact_count || 0) - (b.contact_count || 0); });
    else if (sortBy === 'updated') sorted.sort(function(a, b) { return (b.updated_at || '').localeCompare(a.updated_at || ''); });
    renderCbLists(sorted);
    restoreContactBookSelections();
}

function filterDynamicLists() {
    clearTimeout(cbSearchTimeout);
    cbSearchTimeout = setTimeout(function() {
        var query = (document.getElementById('cbDynamicSearch').value || '').toLowerCase();
        if (!query) { renderCbDynamicLists(cbDynamicListsData); restoreContactBookSelections(); return; }
        var filtered = cbDynamicListsData.filter(function(l) { return (l.name || '').toLowerCase().indexOf(query) !== -1; });
        renderCbDynamicLists(filtered);
        restoreContactBookSelections();
    }, 300);
}

function sortDynamicLists() {
    var sortBy = document.getElementById('cbDynamicSort').value;
    var sorted = cbDynamicListsData.slice();
    if (sortBy === 'name_asc') sorted.sort(function(a, b) { return (a.name || '').localeCompare(b.name || ''); });
    else if (sortBy === 'name_desc') sorted.sort(function(a, b) { return (b.name || '').localeCompare(a.name || ''); });
    else if (sortBy === 'count_desc') sorted.sort(function(a, b) { return (b.contact_count || 0) - (a.contact_count || 0); });
    else if (sortBy === 'count_asc') sorted.sort(function(a, b) { return (a.contact_count || 0) - (b.contact_count || 0); });
    renderCbDynamicLists(sorted);
    restoreContactBookSelections();
}

function filterTagsList() {
    clearTimeout(cbSearchTimeout);
    cbSearchTimeout = setTimeout(function() {
        var query = (document.getElementById('cbTagSearch').value || '').toLowerCase();
        if (!query) { renderCbTags(cbTagsData); restoreContactBookSelections(); return; }
        var filtered = cbTagsData.filter(function(t) { return (t.name || '').toLowerCase().indexOf(query) !== -1; });
        renderCbTags(filtered);
        restoreContactBookSelections();
    }, 300);
}

function sortTagsList() {
    var sortBy = document.getElementById('cbTagSort').value;
    var sorted = cbTagsData.slice();
    if (sortBy === 'name_asc') sorted.sort(function(a, b) { return (a.name || '').localeCompare(b.name || ''); });
    else if (sortBy === 'name_desc') sorted.sort(function(a, b) { return (b.name || '').localeCompare(a.name || ''); });
    else if (sortBy === 'count_desc') sorted.sort(function(a, b) { return (b.contact_count || 0) - (a.contact_count || 0); });
    else if (sortBy === 'count_asc') sorted.sort(function(a, b) { return (a.contact_count || 0) - (b.contact_count || 0); });
    renderCbTags(sorted);
    restoreContactBookSelections();
}

function updateContactBookSummary() {
    var contacts = document.querySelectorAll('.cb-contact:checked').length;
    var lists = document.querySelectorAll('.cb-list:checked').length;
    var dynamic = document.querySelectorAll('.cb-dynamic:checked').length;
    var tags = document.querySelectorAll('.cb-tag:checked').length;
    
    document.getElementById('cbSelectionSummary').textContent = 
        contacts + ' contacts, ' + lists + ' lists, ' + dynamic + ' dynamic lists, ' + tags + ' tags';
}

document.addEventListener('change', function(e) {
    if (e.target.classList.contains('cb-contact') || 
        e.target.classList.contains('cb-list') || 
        e.target.classList.contains('cb-dynamic') || 
        e.target.classList.contains('cb-tag')) {
        updateContactBookSummary();
    }
});

function confirmContactBookSelection() {
    var contacts = Array.from(document.querySelectorAll('.cb-contact:checked')).map(function(cb) {
        return { id: cb.value, name: cb.dataset.name || 'Contact' };
    });
    var lists = Array.from(document.querySelectorAll('.cb-list:checked')).map(function(cb) {
        return { id: cb.value, name: cb.dataset.name || 'List', count: parseInt(cb.dataset.count) || 0 };
    });
    var dynamic = Array.from(document.querySelectorAll('.cb-dynamic:checked')).map(function(cb) {
        return { id: cb.value, name: cb.dataset.name || 'Dynamic List', count: parseInt(cb.dataset.count) || 0 };
    });
    var tags = Array.from(document.querySelectorAll('.cb-tag:checked')).map(function(cb) {
        return { id: cb.value, name: cb.dataset.name || 'Tag', count: parseInt(cb.dataset.count) || 0 };
    });
    
    recipientState.contactBook = { contacts: contacts, lists: lists, dynamicLists: dynamic, tags: tags };
    
    bootstrap.Modal.getInstance(document.getElementById('contactBookModal')).hide();
    renderContactBookChips();
    updateRecipientSummary();
}

function removeContactBookItem(type) {
    recipientState.contactBook[type] = [];
    var selectorMap = { contacts: '.cb-contact', lists: '.cb-list', dynamicLists: '.cb-dynamic', tags: '.cb-tag' };
    document.querySelectorAll(selectorMap[type]).forEach(function(cb) { cb.checked = false; });
    renderContactBookChips();
    updateRecipientSummary();
}

function renderContactBookChips() {
    var contacts = recipientState.contactBook.contacts.length;
    var lists = recipientState.contactBook.lists.length;
    var dynamic = recipientState.contactBook.dynamicLists.length;
    var tags = recipientState.contactBook.tags.length;
    
    var chipsHtml = '';
    if (contacts) chipsHtml += '<span class="badge me-1 mb-1" style="background-color: #f0ebf8; color: #6b5b95;">' + contacts + ' Contacts <button class="btn-close ms-1" style="font-size:8px; filter: none;" onclick="removeContactBookItem(\'contacts\')"></button></span>';
    if (lists) chipsHtml += '<span class="badge me-1 mb-1" style="background-color: #f0ebf8; color: #6b5b95;">' + lists + ' Lists <button class="btn-close ms-1" style="font-size:8px; filter: none;" onclick="removeContactBookItem(\'lists\')"></button></span>';
    if (dynamic) chipsHtml += '<span class="badge me-1 mb-1" style="background-color: #f0ebf8; color: #6b5b95;">' + dynamic + ' Dynamic Lists <button class="btn-close ms-1" style="font-size:8px; filter: none;" onclick="removeContactBookItem(\'dynamicLists\')"></button></span>';
    if (tags) chipsHtml += '<span class="badge me-1 mb-1" style="background-color: #f0ebf8; color: #6b5b95;">' + tags + ' Tags <button class="btn-close ms-1" style="font-size:8px; filter: none;" onclick="removeContactBookItem(\'tags\')"></button></span>';
    
    document.getElementById('contactBookChips').innerHTML = chipsHtml;
    document.getElementById('contactBookSelection').classList.toggle('d-none', !chipsHtml);
}

function updateRecipientSummary() {
    var manualValid = recipientState.manual.valid.length;
    var uploadValid = recipientState.files.reduce(function(acc, f) { return acc + f.valid.length; }, 0);
    var contactBookCount = recipientState.contactBook.contacts.length +
                          recipientState.contactBook.lists.reduce(function(acc, l) { return acc + (l.count || 0); }, 0) +
                          recipientState.contactBook.dynamicLists.reduce(function(acc, l) { return acc + (l.count || 0); }, 0) +
                          recipientState.contactBook.tags.reduce(function(acc, t) { return acc + (t.count || 0); }, 0);
    
    var totalValid = manualValid + uploadValid + contactBookCount;
    
    document.getElementById('recipientCount').textContent = totalValid;
    document.getElementById('previewRecipients').textContent = totalValid;
    
    updatePreviewCost();
}

function showInvalidNumbers(source) {
    if (source === 'manual') {
        showInvalidNumbersTable(recipientState.manual.invalid);
    } else {
        var allFileInvalid = [];
        recipientState.files.forEach(function(f) { allFileInvalid = allFileInvalid.concat(f.invalid); });
        showInvalidNumbersTable(allFileInvalid);
    }
}

function showAllInvalidNumbers() {
    var all = recipientState.manual.invalid.slice();
    recipientState.files.forEach(function(f) { all = all.concat(f.invalid); });
    showInvalidNumbersTable(all);
}

function showInvalidNumbersTable(invalid) {
    var html = '';
    invalid.forEach(function(item) {
        html += '<tr><td>' + item.row + '</td><td><code>' + item.original + '</code></td><td class="text-danger">' + item.reason + '</td></tr>';
    });
    document.getElementById('invalidNumbersTable').innerHTML = html || '<tr><td colspan="3" class="text-center text-muted">No invalid numbers</td></tr>';
    
    var modal = new bootstrap.Modal(document.getElementById('invalidNumbersModal'));
    modal.show();
}

function downloadInvalidNumbers() {
    var all = recipientState.manual.invalid.slice();
    recipientState.files.forEach(function(f) { all = all.concat(f.invalid); });
    var csv = 'Row,Original Value,Reason\n';
    all.forEach(function(item) {
        csv += item.row + ',"' + item.original + '","' + item.reason + '"\n';
    });
    
    var blob = new Blob([csv], { type: 'text/csv' });
    var url = URL.createObjectURL(blob);
    var a = document.createElement('a');
    a.href = url;
    a.download = 'invalid_numbers.csv';
    a.click();
    console.log('TODO: Log invalid numbers download for audit');
}

var accountPricing = {!! json_encode($account_pricing) !!};

function updatePreviewCost() {
    var recipientEl = document.getElementById('previewRecipients');
    var recipients = recipientEl ? (parseInt(recipientEl.textContent) || 0) : 0;
    var channelEl = document.querySelector('input[name="channel"]:checked');
    var channel = channelEl ? channelEl.value : 'sms';
    var costPerMsg = accountPricing[channel] || accountPricing['sms'] || 0.035;
    var partsEl = document.getElementById('smsPartCount');
    var parts = partsEl ? (parseInt(partsEl.textContent) || 1) : 1;
    var cost = recipients * parts * costPerMsg;
    var costEl = document.getElementById('previewCost');
    if (costEl) {
        var isTest = (typeof AccountLifecycle !== 'undefined' && AccountLifecycle.isTest && AccountLifecycle.isTest());
        var formatted = cost.toFixed(4);
        if (isTest) {
            costEl.textContent = formatted + ' cr';
        } else {
            var symbol = accountPricing.currency === 'GBP' ? '\u00A3' : (accountPricing.currency === 'USD' ? '$' : accountPricing.currency + ' ');
            costEl.textContent = symbol + formatted;
        }
    }
}

var testMessageModal = null;

function openTestMessageModal() {
    if (!testMessageModal) {
        testMessageModal = new bootstrap.Modal(document.getElementById('testMessageModal'));
    }
    
    document.getElementById('testMobileNumber').value = '';
    document.getElementById('testMessageSuccess').classList.add('d-none');
    document.getElementById('testMessageError').classList.add('d-none');
    document.getElementById('sendTestBtn').disabled = false;
    document.getElementById('sendTestBtn').innerHTML = '<i class="fas fa-paper-plane me-1"></i>Send test';
    
    var channel = document.querySelector('input[name="channel"]:checked').value;
    var channelInfo = document.getElementById('testMessageChannelInfo');
    var channelDesc = document.getElementById('testChannelDescription');
    
    channelInfo.classList.remove('d-none');
    if (channel === 'sms') {
        channelDesc.textContent = 'SMS message will be sent to your phone';
    } else if (channel === 'rcs_basic') {
        channelDesc.textContent = 'Basic RCS message will be sent (SMS fallback if needed)';
    } else if (channel === 'rcs_rich') {
        channelDesc.textContent = 'Rich RCS message with configured cards/media will be sent';
    }
    
    testMessageModal.show();
}

function normalizeTestNumber(input) {
    var cleaned = input.replace(/[\s\-\(\)\.]/g, '');
    
    if (cleaned.startsWith('+44')) {
        cleaned = '44' + cleaned.substring(3);
    }
    if (cleaned.startsWith('0044')) {
        cleaned = '44' + cleaned.substring(4);
    }
    if (cleaned.startsWith('07')) {
        cleaned = '44' + cleaned.substring(1);
    }
    
    return cleaned;
}

function validateTestNumber(number) {
    var normalized = normalizeTestNumber(number);
    
    if (!/^44[0-9]{10}$/.test(normalized)) {
        return { valid: false, error: 'Please enter a valid UK mobile number' };
    }
    if (!/^447[0-9]{9}$/.test(normalized)) {
        return { valid: false, error: 'Please enter a UK mobile number (starting with 07 or +447)' };
    }
    
    return { valid: true, normalized: normalized };
}

function sendTestMessage() {
    var numberInput = document.getElementById('testMobileNumber').value.trim();
    var successEl = document.getElementById('testMessageSuccess');
    var errorEl = document.getElementById('testMessageError');
    var errorText = document.getElementById('testErrorText');
    var sendBtn = document.getElementById('sendTestBtn');
    
    successEl.classList.add('d-none');
    errorEl.classList.add('d-none');
    
    if (!numberInput) {
        errorText.textContent = 'Please enter a mobile number';
        errorEl.classList.remove('d-none');
        return;
    }
    
    var validation = validateTestNumber(numberInput);
    if (!validation.valid) {
        errorText.textContent = validation.error;
        errorEl.classList.remove('d-none');
        return;
    }
    
    var channel = document.querySelector('input[name="channel"]:checked').value;
    var senderId = document.getElementById('senderId').value;
    var rcsAgent = document.getElementById('rcsAgent').value;
    var messageContent = document.getElementById('messageContent').value;
    
    if (channel === 'sms' && !senderId) {
        errorText.textContent = 'Please select a Sender ID before sending a test';
        errorEl.classList.remove('d-none');
        return;
    }
    
    if ((channel === 'rcs_basic' || channel === 'rcs_rich') && !rcsAgent) {
        errorText.textContent = 'Please select an RCS Agent before sending a test';
        errorEl.classList.remove('d-none');
        return;
    }
    
    if (channel === 'sms' || channel === 'rcs_basic') {
        if (!messageContent.trim()) {
            errorText.textContent = 'Please enter message content before sending a test';
            errorEl.classList.remove('d-none');
            return;
        }
    }
    
    if (channel === 'rcs_rich') {
        if (!rcsPersistentPayload && typeof rcsPersistentPayload !== 'undefined') {
            errorText.textContent = 'Please configure Rich RCS content before sending a test';
            errorEl.classList.remove('d-none');
            return;
        }
    }
    
    sendBtn.disabled = true;
    sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Sending...';
    
    var testPayload = {
        test: true,
        recipient: validation.normalized,
        channel: channel,
        sender_id: senderId,
        rcs_agent_id: rcsAgent,
        content: messageContent,
        rcs_payload: (channel === 'rcs_rich' && typeof rcsPersistentPayload !== 'undefined') ? rcsPersistentPayload : null
    };
    
    console.log('[Test Message] TODO: POST /api/messages/test', testPayload);
    
    setTimeout(function() {
        var success = true;
        
        if (success) {
            sendBtn.disabled = false;
            sendBtn.innerHTML = '<i class="fas fa-paper-plane me-1"></i>Send test';
            
            testMessageModal.hide();
            
            showTestSentConfirmation(validation.normalized);
        } else {
            errorText.textContent = 'Failed to send test message. Please try again.';
            errorEl.classList.remove('d-none');
            sendBtn.disabled = false;
            sendBtn.innerHTML = '<i class="fas fa-paper-plane me-1"></i>Send test';
        }
    }, 1000);
}

function showTestSentConfirmation(phoneNumber) {
    var existingModal = document.getElementById('testSentConfirmModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    var modalHtml = '<div class="modal fade" id="testSentConfirmModal" tabindex="-1">' +
        '<div class="modal-dialog modal-dialog-centered modal-sm">' +
        '<div class="modal-content">' +
        '<div class="modal-body text-center py-4">' +
        '<div class="mb-3"><i class="fas fa-check-circle text-success" style="font-size: 48px;"></i></div>' +
        '<h5 class="mb-2">Test Message Sent!</h5>' +
        '<p class="text-muted mb-0">Your test message has been sent to <strong>' + phoneNumber + '</strong>. Check your phone to preview it.</p>' +
        '</div>' +
        '<div class="modal-footer justify-content-center py-2 border-0">' +
        '<button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>' +
        '</div>' +
        '</div>' +
        '</div>' +
        '</div>';
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    var confirmModal = new bootstrap.Modal(document.getElementById('testSentConfirmModal'));
    confirmModal.show();
    
    document.getElementById('testSentConfirmModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}
</script>
@endsection
