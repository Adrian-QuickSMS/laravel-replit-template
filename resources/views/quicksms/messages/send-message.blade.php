@extends('layouts.quicksms')

@section('title', 'Send Message')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/rcs-preview.css') }}">
<style>
#rcsWizardPreviewContainer .rcs-phone-frame {
    transform: scale(0.85);
    transform-origin: top center;
}
.validation-error-field {
    border-color: #dc3545 !important;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
}
.validation-error-field:focus {
    border-color: #dc3545 !important;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row page-titles">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('messages') }}">Messages</a></li>
            <li class="breadcrumb-item active">Send Message</li>
        </ol>
    </div>
    
    <div class="row align-items-start">
        <div class="col-lg-8">
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
                        <button type="button" class="btn btn-outline-primary" onclick="triggerFileUpload()">
                            <i class="fas fa-upload me-1"></i>Upload CSV
                        </button>
                        <button type="button" class="btn btn-outline-primary" onclick="openContactBookModal()">
                            <i class="fas fa-users me-1"></i>Select from Contact Book
                        </button>
                        <input type="file" class="d-none" id="recipientFile" accept=".csv,.xlsx,.xls" onchange="handleFileSelect()">
                    </div>
                    
                    <div class="d-none mb-3" id="uploadProgress">
                        <div class="progress mb-2" style="height: 6px;"><div class="progress-bar" id="uploadProgressBar" style="width: 0%;"></div></div>
                        <span id="uploadStatus" class="text-muted">Processing...</span>
                    </div>
                    <div class="d-none mb-3" id="uploadResult">
                        <span class="badge bg-light text-dark me-2"><i class="fas fa-file-csv me-1"></i>File uploaded</span>
                        <span class="text-success"><i class="fas fa-check-circle me-1"></i><span id="uploadValid">0</span> valid</span>
                        <span class="text-danger ms-2"><i class="fas fa-times-circle me-1"></i><span id="uploadInvalid">0</span> invalid</span>
                        <a href="#" class="ms-2 d-none" id="uploadInvalidLink" onclick="showInvalidNumbers('upload')">View</a>
                    </div>
                    
                    <div class="d-none mb-3" id="contactBookSelection">
                        <div id="contactBookChips"></div>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                        <span class="fw-medium">Total Recipients</span>
                        <span class="badge bg-primary" id="recipientCount">0</span>
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
                                    @foreach($templates as $template)
                                    <option value="{{ $template['id'] }}" data-content="{{ addslashes($template['content']) }}">{{ $template['name'] }}</option>
                                    @endforeach
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
                        <div class="alert alert-info py-2 mb-0">
                            <i class="fas fa-info-circle me-1"></i>
                            <span id="rcsHelperText">Messages over 160 characters will be automatically sent as a single RCS message where supported.</span>
                        </div>
                    </div>
                    
                    <div class="border-top pt-3 mb-3">
                        <div class="row">
                            <div class="col-md-4 mb-2">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="includeTrackableLink" onchange="toggleTrackableLinkModal()">
                                    <label class="form-check-label" for="includeTrackableLink">Include trackable link</label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="messageExpiry" onchange="toggleMessageExpiryModal()">
                                    <label class="form-check-label" for="messageExpiry">Message expiry</label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="scheduleRules" onchange="toggleScheduleRulesModal()">
                                    <label class="form-check-label" for="scheduleRules">Schedule & sending rules</label>
                                </div>
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
                        <div class="border rounded p-3 bg-light text-center">
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
                        <button type="button" class="btn btn-primary" onclick="continueToConfirmation()">Continue <i class="fas fa-arrow-right ms-1"></i></button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card sticky-top" style="top: 20px;">
                <div class="card-header bg-primary text-white py-3">
                    <h6 class="card-title mb-0">Preview</h6>
                </div>
                <div class="card-body p-4">
                    <div class="text-center mb-3">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-outline-primary active" id="previewSMSBtn" onclick="showPreview('sms')">SMS</button>
                            <button type="button" class="btn btn-outline-primary" id="previewRCSBtn" onclick="showPreview('rcs')">RCS</button>
                        </div>
                    </div>
                    
                    <div class="phone-mockup mx-auto" style="max-width: 220px;">
                        <div class="bg-dark rounded-top p-2 text-center">
                            <small class="text-white">Preview</small>
                        </div>
                        <div class="bg-light p-3" style="min-height: 200px; border-radius: 0 0 12px 12px;">
                            <div id="smsPreview">
                                <div class="text-center mb-2">
                                    <small class="text-muted" id="previewSenderId">From: Select Sender</small>
                                </div>
                                <div class="bg-primary text-white p-2 rounded mb-2" style="max-width: 90%; margin-left: auto;">
                                    <p class="mb-0" id="previewMessage">Your message...</p>
                                </div>
                                <div class="text-end"><small class="text-muted">Now</small></div>
                            </div>
                            
                            <div id="rcsPreview" class="d-none">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2" style="width: 24px; height: 24px;" id="previewRcsLogo"><i class="fas fa-building"></i></div>
                                    <strong id="previewRcsAgent">Agent</strong>
                                    <span class="badge bg-success ms-1">✓</span>
                                </div>
                                <div class="card shadow-sm">
                                    <div class="bg-secondary" style="height: 80px;" id="previewRcsImageArea">
                                        <div class="d-flex align-items-center justify-content-center h-100 text-white"><i class="fas fa-image"></i></div>
                                    </div>
                                    <div class="card-body p-2">
                                        <h6 class="card-title mb-1" id="previewRcsTitle">Title</h6>
                                        <p class="card-text mb-2" id="previewRcsDescription">Description</p>
                                        <button class="btn btn-outline-primary btn-sm w-100">Action</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4 border-top pt-3">
                        <div class="row text-center">
                            <div class="col-4"><small class="text-muted d-block mb-1">Channel</small><strong id="previewChannel">SMS</strong></div>
                            <div class="col-4"><small class="text-muted d-block mb-1">Recipients</small><strong id="previewRecipients">0</strong></div>
                            <div class="col-4"><small class="text-muted d-block mb-1">Cost</small><strong id="previewCost">0 cr</strong></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="contactBookModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-fullscreen-lg-down">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h5 class="modal-title"><i class="fas fa-address-book me-2"></i>Select from Contact Book</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-2">
                <ul class="nav nav-tabs mb-2" style="font-size: 12px;">
                    <li class="nav-item"><button class="nav-link active py-1 px-3" data-bs-toggle="tab" data-bs-target="#cbContacts">Contacts</button></li>
                    <li class="nav-item"><button class="nav-link py-1 px-3" data-bs-toggle="tab" data-bs-target="#cbLists">Lists</button></li>
                    <li class="nav-item"><button class="nav-link py-1 px-3" data-bs-toggle="tab" data-bs-target="#cbDynamicLists">Dynamic Lists</button></li>
                    <li class="nav-item"><button class="nav-link py-1 px-3" data-bs-toggle="tab" data-bs-target="#cbTags">Tags</button></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="cbContacts">
                        <div class="row mb-2">
                            <div class="col-md-6">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input type="text" class="form-control" id="cbContactSearch" placeholder="Search names, numbers, tags, custom fields..." oninput="filterContacts()">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex gap-2 align-items-center" style="font-size: 11px;">
                                    <select class="form-select form-select-sm" id="cbContactSort" onchange="sortContacts()">
                                        <option value="recent">Most recently contacted</option>
                                        <option value="added">Most recently added</option>
                                        <option value="name_asc">Name A-Z</option>
                                        <option value="name_desc">Name Z-A</option>
                                    </select>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="toggleContactFilters()"><i class="fas fa-filter"></i></button>
                                </div>
                            </div>
                        </div>
                        <div class="d-none mb-2 p-2 bg-light rounded" id="cbContactFilters" style="font-size: 11px;">
                            <div class="row">
                                <div class="col-md-3"><label class="form-label mb-1">Tags</label><select class="form-select form-select-sm" id="cbFilterTags"><option value="">All tags</option></select></div>
                                <div class="col-md-3"><label class="form-label mb-1">Has Mobile</label><select class="form-select form-select-sm" id="cbFilterMobile"><option value="">Any</option><option value="yes">Yes</option><option value="no">No</option></select></div>
                                <div class="col-md-3"><label class="form-label mb-1">Opt-out Status</label><select class="form-select form-select-sm" id="cbFilterOptout"><option value="exclude">Exclude opted-out</option><option value="include">Include all</option></select></div>
                                <div class="col-md-3 d-flex align-items-end"><button class="btn btn-link btn-sm" onclick="clearContactFilters()">Clear filters</button></div>
                            </div>
                        </div>
                        <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                            <table class="table table-sm table-hover mb-0" style="font-size: 11px;">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th style="width: 30px;"><input type="checkbox" class="form-check-input" id="cbSelectAllContacts" onchange="toggleAllContacts()"></th>
                                        <th>Name</th>
                                        <th>Mobile</th>
                                        <th>Tags</th>
                                    </tr>
                                </thead>
                                <tbody id="cbContactsTable">
                                    <tr><td><input type="checkbox" class="form-check-input cb-contact" value="1"></td><td>John Smith</td><td>+44 7700***123</td><td><span class="badge bg-info">VIP</span></td></tr>
                                    <tr><td><input type="checkbox" class="form-check-input cb-contact" value="2"></td><td>Jane Doe</td><td>+44 7700***456</td><td><span class="badge bg-success">Asthma</span></td></tr>
                                    <tr><td><input type="checkbox" class="form-check-input cb-contact" value="3"></td><td>Robert Brown</td><td>+44 7700***789</td><td></td></tr>
                                    <tr><td><input type="checkbox" class="form-check-input cb-contact" value="4"></td><td>Sarah Wilson</td><td>+44 7700***012</td><td><span class="badge bg-warning">Diabetes</span></td></tr>
                                    <tr><td><input type="checkbox" class="form-check-input cb-contact" value="5"></td><td>Michael Johnson</td><td>+44 7700***345</td><td><span class="badge bg-info">VIP</span></td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="cbLists">
                        <div class="input-group input-group-sm mb-2">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control" placeholder="Search lists...">
                        </div>
                        <div class="table-responsive" style="max-height: 300px;">
                            <table class="table table-sm table-hover mb-0" style="font-size: 11px;">
                                <thead class="table-light">
                                    <tr><th style="width: 30px;"></th><th>List Name</th><th>Contacts</th><th>Last Updated</th></tr>
                                </thead>
                                <tbody>
                                    <tr><td><input type="checkbox" class="form-check-input cb-list" value="1"></td><td>VIP Patients</td><td>1,234</td><td>22-Dec-2025</td></tr>
                                    <tr><td><input type="checkbox" class="form-check-input cb-list" value="2"></td><td>Newsletter Subscribers</td><td>5,678</td><td>21-Dec-2025</td></tr>
                                    <tr><td><input type="checkbox" class="form-check-input cb-list" value="3"></td><td>Flu Campaign 2025</td><td>3,456</td><td>20-Dec-2025</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="cbDynamicLists">
                        <div class="input-group input-group-sm mb-2">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control" placeholder="Search dynamic lists...">
                        </div>
                        <div class="table-responsive" style="max-height: 300px;">
                            <table class="table table-sm table-hover mb-0" style="font-size: 11px;">
                                <thead class="table-light">
                                    <tr><th style="width: 30px;"></th><th>List Name</th><th>Rules</th><th>Contacts</th><th>Last Evaluated</th></tr>
                                </thead>
                                <tbody>
                                    <tr><td><input type="checkbox" class="form-check-input cb-dynamic" value="1"></td><td>Over 65s</td><td>Age > 65</td><td>2,345</td><td>22-Dec-2025</td></tr>
                                    <tr><td><input type="checkbox" class="form-check-input cb-dynamic" value="2"></td><td>Local Postcodes</td><td>Postcode starts with SW</td><td>1,890</td><td>22-Dec-2025</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="cbTags">
                        <div class="input-group input-group-sm mb-2">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control" placeholder="Search tags...">
                        </div>
                        <div class="table-responsive" style="max-height: 300px;">
                            <table class="table table-sm table-hover mb-0" style="font-size: 11px;">
                                <thead class="table-light">
                                    <tr><th style="width: 30px;"></th><th>Tag</th><th>Contacts</th></tr>
                                </thead>
                                <tbody>
                                    <tr><td><input type="checkbox" class="form-check-input cb-tag" value="1"></td><td><span class="badge" style="background-color: #0d6efd;">VIP</span></td><td>456</td></tr>
                                    <tr><td><input type="checkbox" class="form-check-input cb-tag" value="2"></td><td><span class="badge" style="background-color: #198754;">Asthma</span></td><td>1,234</td></tr>
                                    <tr><td><input type="checkbox" class="form-check-input cb-tag" value="3"></td><td><span class="badge" style="background-color: #ffc107;">Diabetes</span></td><td>890</td></tr>
                                    <tr><td><input type="checkbox" class="form-check-input cb-tag" value="4"></td><td><span class="badge" style="background-color: #dc3545;">Hypertension</span></td><td>567</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="border-top mt-2 pt-2" style="font-size: 11px;">
                    <strong>Selected:</strong> <span id="cbSelectionSummary">0 contacts, 0 lists, 0 dynamic lists, 0 tags</span>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" onclick="confirmContactBookSelection()"><i class="fas fa-plus me-1"></i>Add to Campaign</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="columnMappingModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h5 class="modal-title"><i class="fas fa-columns me-2"></i>Map Columns</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info py-2" style="font-size: 12px;">
                    <i class="fas fa-info-circle me-1"></i>Map your file columns to the required fields. Mobile Number is required.
                </div>
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="hasHeaders" checked>
                        <label class="form-check-label" for="hasHeaders">First row contains column headings</label>
                    </div>
                </div>
                <table class="table table-sm" style="font-size: 12px;">
                    <thead><tr><th>Detected Column</th><th>Map to Field</th><th>Sample Data</th></tr></thead>
                    <tbody id="columnMappingTable">
                        <tr><td>Column A</td><td><select class="form-select form-select-sm"><option value="">-- Skip --</option><option value="mobile" selected>Mobile Number *</option><option value="firstname">First Name</option><option value="lastname">Last Name</option><option value="email">Email</option></select></td><td class="text-muted">07700900123</td></tr>
                        <tr><td>Column B</td><td><select class="form-select form-select-sm"><option value="">-- Skip --</option><option value="mobile">Mobile Number *</option><option value="firstname" selected>First Name</option><option value="lastname">Last Name</option><option value="email">Email</option></select></td><td class="text-muted">John</td></tr>
                        <tr><td>Column C</td><td><select class="form-select form-select-sm"><option value="">-- Skip --</option><option value="mobile">Mobile Number *</option><option value="firstname">First Name</option><option value="lastname" selected>Last Name</option><option value="email">Email</option></select></td><td class="text-muted">Smith</td></tr>
                    </tbody>
                </table>
                <div class="alert alert-warning py-2 d-none" id="excelZeroWarning" style="font-size: 12px;">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    <strong>Excel formatting detected:</strong> Numbers starting with '7' may have had leading zeros removed. 
                    <div class="form-check mt-1">
                        <input class="form-check-input" type="checkbox" id="fixExcelZeros" checked>
                        <label class="form-check-label" for="fixExcelZeros">Convert to UK format (+447...)</label>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" onclick="confirmColumnMapping()"><i class="fas fa-check me-1"></i>Confirm & Import</button>
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
<div class="modal fade" id="personalisationModal" tabindex="-1">
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
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="insertPlaceholder('firstName')">@{{firstName}}</button>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="insertPlaceholder('lastName')">@{{lastName}}</button>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="insertPlaceholder('fullName')">@{{fullName}}</button>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="insertPlaceholder('mobile')">@{{mobile}}</button>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="insertPlaceholder('email')">@{{email}}</button>
                    </div>
                </div>
                <div class="mb-3">
                    <h6 class="text-muted mb-2">Custom Fields</h6>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertPlaceholder('appointmentDate')">@{{appointmentDate}}</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertPlaceholder('appointmentTime')">@{{appointmentTime}}</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertPlaceholder('clinicName')">@{{clinicName}}</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertPlaceholder('customField_1')">@{{customField_1}}</button>
                    </div>
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

<div class="modal fade" id="emojiPickerModal" tabindex="-1">
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
                <div class="alert alert-info py-2 mb-0">
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
                    <div class="bg-light p-3 rounded" id="aiCurrentContent">
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

<div class="modal fade" id="rcsWizardModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-fullscreen-lg-down">
        <div class="modal-content">
            <div class="modal-header py-3" style="background: var(--primary); color: #fff;">
                <h5 class="modal-title"><i class="fas fa-magic me-2"></i>RCS Content Wizard</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="row g-0" style="min-height: 500px;">
                    <div class="col-lg-5 p-4 d-flex flex-column align-items-center justify-content-start border-end" style="overflow-y: auto; max-height: 80vh; background: rgba(136, 108, 192, 0.1);">
                        <p class="text-muted small mb-3">Live Preview</p>
                        <div id="rcsWizardPreviewContainer"></div>
                    </div>
                    <div class="col-lg-7 p-4">
                        <div class="rcs-config-panel">
                            <div id="rcsValidationErrors" class="d-none"></div>
                            
                            <div class="mb-4">
                                <h6 class="text-muted text-uppercase small mb-3"><i class="fas fa-layer-group me-2"></i>Message Type</h6>
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="rcsMessageType" id="rcsTypeSingle" value="single" checked>
                                    <label class="btn btn-outline-primary" for="rcsTypeSingle">
                                        <i class="fas fa-square me-1"></i>Single Rich Card
                                    </label>
                                    <input type="radio" class="btn-check" name="rcsMessageType" id="rcsTypeCarousel" value="carousel">
                                    <label class="btn btn-outline-primary" for="rcsTypeCarousel">
                                        <i class="fas fa-images me-1"></i>Carousel
                                    </label>
                                </div>
                            </div>
                            
                            <div class="d-none mb-4" id="rcsCarouselNav">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <h6 class="text-muted text-uppercase small mb-0"><i class="fas fa-th-list me-2"></i>Cards</h6>
                                    <span class="badge bg-secondary" id="rcsCardCount">1 / 10</span>
                                </div>
                                <div class="d-flex flex-wrap gap-2 align-items-center" id="rcsCardTabs">
                                    <button type="button" class="btn btn-primary btn-sm rcs-card-tab active" data-card="1" onclick="selectRcsCard(1)">Card 1</button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" id="rcsAddCardBtn" onclick="addRcsCard()">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                                <small class="text-muted d-block mt-2">Cards display left to right in sent message order.</small>
                            </div>
                            
                            <div id="rcsCardConfig">
                                <div class="d-none mb-2" id="rcsCurrentCardLabel">
                                    <span class="badge bg-primary"><i class="fas fa-square me-1"></i>Editing: <span id="rcsCurrentCardName">Card 1</span></span>
                                </div>
                                
                                <div class="mb-4">
                                    <h6 class="text-muted text-uppercase small mb-3"><i class="fas fa-image me-2"></i>Media</h6>
                                    <div class="border rounded p-3">
                                        <div class="btn-group btn-group-sm w-100 mb-3" role="group">
                                            <input type="radio" class="btn-check" name="rcsMediaSource" id="rcsMediaUrl" value="url" checked>
                                            <label class="btn btn-outline-secondary" for="rcsMediaUrl">
                                                <i class="fas fa-link me-1"></i>URL
                                            </label>
                                            <input type="radio" class="btn-check" name="rcsMediaSource" id="rcsMediaUpload" value="upload">
                                            <label class="btn btn-outline-secondary" for="rcsMediaUpload">
                                                <i class="fas fa-upload me-1"></i>Upload
                                            </label>
                                        </div>
                                        
                                        <div id="rcsMediaUrlSection">
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text"><i class="fas fa-globe"></i></span>
                                                <input type="url" class="form-control" id="rcsMediaUrlInput" placeholder="https://example.com/image.jpg">
                                                <button type="button" class="btn btn-outline-primary" onclick="loadRcsMediaUrl()">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </div>
                                            <small class="text-muted d-block mt-1">Enter a publicly accessible image URL (JPEG, PNG, GIF)</small>
                                        </div>
                                        
                                        <div id="rcsMediaUploadSection" class="d-none">
                                            <div class="border border-dashed rounded p-3 text-center bg-light" id="rcsMediaDropzone">
                                                <input type="file" id="rcsMediaFileInput" class="d-none" accept=".jpg,.jpeg,.png,.gif,image/jpeg,image/png,image/gif">
                                                <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                                                <p class="mb-1 small">Drag & drop or <a href="#" onclick="document.getElementById('rcsMediaFileInput').click(); return false;">browse</a></p>
                                                <small class="text-muted">JPEG, PNG, GIF only. Max 250 KB</small>
                                            </div>
                                            <div id="rcsMediaError" class="alert alert-danger py-2 px-3 mt-2 d-none small">
                                                <i class="fas fa-exclamation-circle me-1"></i><span id="rcsMediaErrorText"></span>
                                            </div>
                                        </div>
                                        
                                        <div id="rcsMediaPreview" class="d-none mt-3">
                                            <div class="position-relative">
                                                <div class="border rounded overflow-hidden d-flex align-items-center justify-content-center" style="height: 200px; background: repeating-conic-gradient(#e8e8e8 0% 25%, #f8f8f8 0% 50%) 50% / 12px 12px;">
                                                    <img id="rcsMediaPreviewImg" src="" alt="Media preview" style="object-fit: contain; max-height: 100%; max-width: 100%; transform-origin: center center;">
                                                </div>
                                                <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1" onclick="removeRcsMedia()">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                            
                                            <div class="mt-3 p-3 border rounded bg-light" id="rcsImageEditor">
                                                <h6 class="small text-muted text-uppercase mb-3"><i class="fas fa-sliders-h me-1"></i>Image Editor</h6>
                                                
                                                <div class="mb-3">
                                                    <label class="form-label small mb-1">Orientation</label>
                                                    <div class="d-flex gap-2">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="rcsOrientation" id="rcsOrientVertShort" value="vertical_short" checked>
                                                            <label class="form-check-label small" for="rcsOrientVertShort">Vertical Short</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="rcsOrientation" id="rcsOrientVertMed" value="vertical_medium">
                                                            <label class="form-check-label small" for="rcsOrientVertMed">Vertical Medium</label>
                                                        </div>
                                                        <div class="form-check" id="rcsOrientHorizWrapper">
                                                            <input class="form-check-input" type="radio" name="rcsOrientation" id="rcsOrientHoriz" value="horizontal">
                                                            <label class="form-check-label small" for="rcsOrientHoriz">Horizontal</label>
                                                        </div>
                                                    </div>
                                                    <div id="rcsCarouselOrientWarning" class="alert alert-warning py-1 px-2 mt-2 d-none small">
                                                        <i class="fas fa-info-circle me-1"></i>Horizontal orientation is not available for Carousel cards.
                                                    </div>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label class="form-label small mb-1">Zoom <span class="text-muted" id="rcsZoomValue">100%</span></label>
                                                    <input type="range" class="form-range" id="rcsZoomSlider" min="25" max="200" value="100" oninput="updateRcsZoom(this.value)">
                                                    <div class="d-flex justify-content-between small text-muted mt-1">
                                                        <span>25% (fit)</span>
                                                        <span>100%</span>
                                                        <span>200%</span>
                                                    </div>
                                                </div>
                                                
                                                <div class="mb-2">
                                                    <label class="form-label small mb-1">Crop Position</label>
                                                    <div class="d-flex gap-2">
                                                        <button type="button" class="btn btn-outline-secondary btn-sm flex-fill rcs-crop-btn active" data-position="center" onclick="setRcsCropPosition('center')">
                                                            <i class="fas fa-compress-arrows-alt"></i> Center
                                                        </button>
                                                        <button type="button" class="btn btn-outline-secondary btn-sm flex-fill rcs-crop-btn" data-position="top" onclick="setRcsCropPosition('top')">
                                                            <i class="fas fa-arrow-up"></i> Top
                                                        </button>
                                                        <button type="button" class="btn btn-outline-secondary btn-sm flex-fill rcs-crop-btn" data-position="bottom" onclick="setRcsCropPosition('bottom')">
                                                            <i class="fas fa-arrow-down"></i> Bottom
                                                        </button>
                                                    </div>
                                                </div>
                                                <small class="text-muted">Aspect ratio maintained. No distortion applied.</small>
                                                
                                                <div id="rcsImageSaveBtn" class="mt-3 d-none">
                                                    <button type="button" class="btn btn-primary btn-sm w-100" onclick="saveRcsImageEdits()">
                                                        <i class="fas fa-save me-1"></i>Save Image Changes
                                                    </button>
                                                    <small class="text-muted d-block mt-1">Changes will be saved to QuickSMS hosted URL</small>
                                                </div>
                                                
                                                <div class="mt-3 pt-3 border-top">
                                                    <div class="d-flex justify-content-between align-items-center small">
                                                        <span class="text-muted">Dimensions:</span>
                                                        <span id="rcsImageDimensions" class="badge bg-secondary">--</span>
                                                    </div>
                                                    <div class="d-flex justify-content-between align-items-center small mt-1">
                                                        <span class="text-muted">File size:</span>
                                                        <span id="rcsImageFileSize" class="badge bg-secondary">--</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <h6 class="text-muted text-uppercase small mb-3"><i class="fas fa-heading me-2"></i>Description</h6>
                                    <div class="position-relative border rounded">
                                        <input type="text" class="form-control form-control-sm fw-bold border-0" id="rcsDescription" 
                                            placeholder="Enter card description (bold text)" maxlength="150"
                                            oninput="updateRcsDescriptionCount()" style="padding-right: 70px;">
                                        <div class="position-absolute d-flex gap-1" style="top: 50%; right: 8px; transform: translateY(-50%); z-index: 10;">
                                            <button type="button" class="btn btn-sm btn-light border" onclick="openRcsPlaceholderPicker('description')" title="Insert personalisation">
                                                <i class="fas fa-user-tag"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-light border" onclick="openRcsEmojiPicker('description')" title="Insert emoji">
                                                <i class="fas fa-smile"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-end mt-1">
                                        <small class="text-muted">
                                            <span id="rcsDescriptionCount">0</span> / <span class="text-warning">120</span> chars
                                        </small>
                                    </div>
                                    <div id="rcsDescriptionWarning" class="alert alert-warning py-1 px-2 mt-2 d-none small">
                                        <i class="fas fa-exclamation-triangle me-1"></i>Description exceeds recommended 120 characters.
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <h6 class="text-muted text-uppercase small mb-3"><i class="fas fa-align-left me-2"></i>Text Body</h6>
                                    <div class="position-relative border rounded">
                                        <textarea class="form-control form-control-sm border-0" id="rcsTextBody" rows="4" 
                                            placeholder="Enter message body content..." maxlength="2100"
                                            oninput="updateRcsTextBodyCount()" style="padding-bottom: 40px;"></textarea>
                                        <div class="position-absolute d-flex gap-1" style="bottom: 8px; right: 8px; z-index: 10;">
                                            <button type="button" class="btn btn-sm btn-light border" onclick="openRcsPlaceholderPicker('textBody')" title="Insert personalisation">
                                                <i class="fas fa-user-tag"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-light border" onclick="openRcsEmojiPicker('textBody')" title="Insert emoji">
                                                <i class="fas fa-smile"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-end mt-1">
                                        <small class="text-muted">
                                            <span id="rcsTextBodyCount">0</span> / <span class="text-warning">2000</span> chars
                                        </small>
                                    </div>
                                    <div id="rcsTextBodyWarning" class="alert alert-warning py-1 px-2 mt-2 d-none small">
                                        <i class="fas fa-exclamation-triangle me-1"></i>Text body exceeds recommended 2000 characters.
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <h6 class="text-muted text-uppercase small mb-3"><i class="fas fa-mouse-pointer me-2"></i>Action Buttons</h6>
                                    <div class="border rounded p-3">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <small class="text-muted">Add up to 3 interactive buttons</small>
                                            <span class="badge bg-secondary" id="rcsButtonCount">0 / 3</span>
                                        </div>
                                        
                                        <div id="rcsButtonsList"></div>
                                        
                                        <div id="rcsAddButtonSection">
                                            <button type="button" class="btn btn-outline-primary btn-sm w-100" onclick="addRcsButton()" id="rcsAddButtonBtn">
                                                <i class="fas fa-plus me-1"></i>Add Button
                                            </button>
                                        </div>
                                        
                                        <div class="modal fade" id="rcsButtonConfigModal" tabindex="-1" data-bs-backdrop="static">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header py-2">
                                                        <h6 class="modal-title"><i class="fas fa-mouse-pointer me-2"></i>Configure Button</h6>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label class="form-label small">Button Label <span class="text-danger">*</span></label>
                                                            <div class="position-relative border rounded">
                                                                <input type="text" class="form-control form-control-sm border-0" id="rcsButtonLabel" maxlength="25" placeholder="e.g., Learn More" oninput="updateRcsButtonLabelCount()" style="padding-right: 70px;">
                                                                <div class="position-absolute d-flex gap-1" style="top: 50%; right: 8px; transform: translateY(-50%); z-index: 10;">
                                                                    <button type="button" class="btn btn-sm btn-light border" onclick="openRcsButtonFieldPlaceholder('rcsButtonLabel')" title="Insert personalisation">
                                                                        <i class="fas fa-user-tag"></i>
                                                                    </button>
                                                                    <button type="button" class="btn btn-sm btn-light border" onclick="openRcsButtonFieldEmoji('rcsButtonLabel')" title="Insert emoji">
                                                                        <i class="fas fa-smile"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                            <div class="d-flex justify-content-between mt-1">
                                                                <small id="rcsButtonLabelError" class="text-danger d-none">Label is required</small>
                                                                <small class="text-muted ms-auto"><span id="rcsButtonLabelCount">0</span>/25</small>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <label class="form-label small">Button Type <span class="text-danger">*</span></label>
                                                            <div class="btn-group w-100" role="group">
                                                                <input type="radio" class="btn-check" name="rcsButtonType" id="rcsButtonTypeUrl" value="url" checked>
                                                                <label class="btn btn-outline-secondary btn-sm" for="rcsButtonTypeUrl">
                                                                    <i class="fas fa-link me-1"></i>URL
                                                                </label>
                                                                <input type="radio" class="btn-check" name="rcsButtonType" id="rcsButtonTypePhone" value="phone">
                                                                <label class="btn btn-outline-secondary btn-sm" for="rcsButtonTypePhone">
                                                                    <i class="fas fa-phone me-1"></i>Call
                                                                </label>
                                                                <input type="radio" class="btn-check" name="rcsButtonType" id="rcsButtonTypeCalendar" value="calendar">
                                                                <label class="btn btn-outline-secondary btn-sm" for="rcsButtonTypeCalendar">
                                                                    <i class="fas fa-calendar-plus me-1"></i>Calendar
                                                                </label>
                                                            </div>
                                                        </div>
                                                        
                                                        <div id="rcsButtonUrlConfig">
                                                            <div class="mb-3">
                                                                <label class="form-label small">URL <span class="text-danger">*</span></label>
                                                                <input type="url" class="form-control form-control-sm" id="rcsButtonUrl" placeholder="https://example.com">
                                                                <small id="rcsButtonUrlError" class="text-danger d-none">Valid URL is required</small>
                                                            </div>
                                                        </div>
                                                        
                                                        <div id="rcsButtonPhoneConfig" class="d-none">
                                                            <div class="mb-3">
                                                                <label class="form-label small">Phone Number <span class="text-danger">*</span></label>
                                                                <div class="position-relative border rounded">
                                                                    <input type="tel" class="form-control form-control-sm border-0" id="rcsButtonPhone" placeholder="+44 1234 567890" oninput="validateRcsPhoneNoEmoji()" style="padding-right: 40px;">
                                                                    <div class="position-absolute d-flex gap-1" style="top: 50%; right: 8px; transform: translateY(-50%); z-index: 10;">
                                                                        <button type="button" class="btn btn-sm btn-light border" onclick="openRcsButtonFieldPlaceholder('rcsButtonPhone')" title="Insert personalisation">
                                                                            <i class="fas fa-user-tag"></i>
                                                                        </button>
                                                                        <button type="button" class="btn btn-sm btn-light border disabled" title="Emoji not allowed in phone numbers" style="opacity: 0.5; cursor: not-allowed;">
                                                                            <i class="fas fa-smile"></i>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                                <small id="rcsButtonPhoneError" class="text-danger d-none">Valid phone number required (e.g., +44...)</small>
                                                                <small id="rcsButtonPhoneEmojiError" class="text-danger d-none"><i class="fas fa-ban me-1"></i>Emoji not allowed in phone numbers</small>
                                                            </div>
                                                        </div>
                                                        
                                                        <div id="rcsButtonCalendarConfig" class="d-none">
                                                            <div class="mb-3">
                                                                <label class="form-label small">Event Title <span class="text-danger">*</span></label>
                                                                <div class="position-relative border rounded">
                                                                    <input type="text" class="form-control form-control-sm border-0" id="rcsButtonEventTitle" maxlength="100" placeholder="Meeting with QuickSMS" style="padding-right: 70px;">
                                                                    <div class="position-absolute d-flex gap-1" style="top: 50%; right: 8px; transform: translateY(-50%); z-index: 10;">
                                                                        <button type="button" class="btn btn-sm btn-light border" onclick="openRcsButtonFieldPlaceholder('rcsButtonEventTitle')" title="Insert personalisation">
                                                                            <i class="fas fa-user-tag"></i>
                                                                        </button>
                                                                        <button type="button" class="btn btn-sm btn-light border" onclick="openRcsButtonFieldEmoji('rcsButtonEventTitle')" title="Insert emoji">
                                                                            <i class="fas fa-smile"></i>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                                <small id="rcsButtonEventTitleError" class="text-danger d-none">Event title is required</small>
                                                            </div>
                                                            <div class="row g-2 mb-3">
                                                                <div class="col-6">
                                                                    <label class="form-label small">Start Date/Time <span class="text-danger">*</span></label>
                                                                    <input type="datetime-local" class="form-control form-control-sm" id="rcsButtonEventStart">
                                                                    <small id="rcsButtonEventStartError" class="text-danger d-none">Start time required</small>
                                                                </div>
                                                                <div class="col-6">
                                                                    <label class="form-label small">End Date/Time <span class="text-danger">*</span></label>
                                                                    <input type="datetime-local" class="form-control form-control-sm" id="rcsButtonEventEnd">
                                                                    <small id="rcsButtonEventEndError" class="text-danger d-none">End time required</small>
                                                                </div>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label small">Event Description</label>
                                                                <div class="position-relative border rounded">
                                                                    <textarea class="form-control form-control-sm border-0" id="rcsButtonEventDesc" rows="2" maxlength="500" placeholder="Optional description..." style="padding-bottom: 35px;"></textarea>
                                                                    <div class="position-absolute d-flex gap-1" style="bottom: 6px; right: 8px; z-index: 10;">
                                                                        <button type="button" class="btn btn-sm btn-light border" onclick="openRcsButtonFieldPlaceholder('rcsButtonEventDesc')" title="Insert personalisation">
                                                                            <i class="fas fa-user-tag"></i>
                                                                        </button>
                                                                        <button type="button" class="btn btn-sm btn-light border" onclick="openRcsButtonFieldEmoji('rcsButtonEventDesc')" title="Insert emoji">
                                                                            <i class="fas fa-smile"></i>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer py-2">
                                                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="button" class="btn btn-primary btn-sm" onclick="saveRcsButton()">
                                                            <i class="fas fa-check me-1"></i>Save Button
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
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary" onclick="handleRcsWizardClose()">Cancel</button>
                <button type="button" class="btn btn-primary" id="rcsApplyContentBtn" onclick="handleRcsApplyContent()" disabled>
                    <i class="fas fa-check me-1"></i>Apply RCS Content
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="rcsUnsavedChangesModal" tabindex="-1" data-bs-backdrop="static" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle text-warning me-2"></i>Save image changes?</h5>
            </div>
            <div class="modal-body">
                <p>You have made changes to how the image is presented. Do you want to save?</p>
                <div class="alert alert-info small mb-3">
                    <i class="fas fa-info-circle me-1"></i>
                    <strong>If you save:</strong> QuickSMS will create a unique URL on a quicksms.com domain to replace the URL you provided.
                </div>
                <div class="alert alert-secondary small mb-0">
                    <i class="fas fa-undo me-1"></i>
                    <strong>If you don't save:</strong> The image will render using the default (original URL and default presentation).
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" onclick="cancelRcsUnsavedChanges()">Cancel</button>
                <button type="button" class="btn btn-secondary" onclick="discardRcsImageEdits()">Don't Save</button>
                <button type="button" class="btn btn-primary" onclick="saveRcsImageEditsAndContinue()">
                    <i class="fas fa-save me-1"></i>Save Changes
                </button>
            </div>
        </div>
    </div>
</div>

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
        });
    });
    
    document.querySelectorAll('input[name="scheduling"]').forEach(function(radio) {
        radio.addEventListener('change', toggleScheduling);
    });
    
    checkForDuplicatePrefill();
});

function checkForDuplicatePrefill() {
    var urlParams = new URLSearchParams(window.location.search);
    var duplicateId = urlParams.get('duplicate');
    
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

function selectChannel(channel) {
    var rcsAgentSection = document.getElementById('rcsAgentSection');
    var rcsContentSection = document.getElementById('rcsContentSection');
    var previewChannel = document.getElementById('previewChannel');
    var contentLabel = document.getElementById('contentLabel');
    var rcsTextHelper = document.getElementById('rcsTextHelper');
    var rcsHelperText = document.getElementById('rcsHelperText');
    
    if (channel === 'sms') {
        rcsAgentSection.classList.add('d-none');
        rcsContentSection.classList.add('d-none');
        rcsTextHelper.classList.add('d-none');
        previewChannel.textContent = 'SMS';
        contentLabel.textContent = 'SMS Content';
    } else if (channel === 'rcs_basic') {
        rcsAgentSection.classList.remove('d-none');
        rcsContentSection.classList.add('d-none');
        rcsTextHelper.classList.remove('d-none');
        rcsHelperText.textContent = 'Messages over 160 characters will be automatically sent as a single RCS message where supported.';
        previewChannel.textContent = 'Basic RCS';
        contentLabel.textContent = 'Message Content';
    } else if (channel === 'rcs_rich') {
        rcsAgentSection.classList.remove('d-none');
        rcsContentSection.classList.remove('d-none');
        rcsTextHelper.classList.add('d-none');
        previewChannel.textContent = 'Rich RCS';
        contentLabel.textContent = 'SMS Fallback Content';
    }
    updatePreview();
    handleContentChange();
}

function toggleScheduling() {
    var options = document.getElementById('schedulingOptions');
    var isLater = document.getElementById('sendLater').checked;
    options.classList.toggle('d-none', !isLater);
}

function updatePreview() {
    var senderId = document.getElementById('senderId');
    var smsContent = document.getElementById('smsContent');
    
    document.getElementById('previewSenderId').textContent = 'From: ' + (senderId.selectedOptions[0]?.text || 'Select Sender');
    document.getElementById('previewMessage').textContent = smsContent.value || 'Your message...';
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

function applySelectedTemplate() {
    var selector = document.getElementById('templateSelector');
    var selectedOption = selector.options[selector.selectedIndex];
    if (selectedOption.value) {
        var content = selectedOption.getAttribute('data-content');
        if (content) {
            content = content.replace(/\\'/g, "'");
            document.getElementById('smsContent').value = content;
            handleContentChange();
        }
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

function addRcsButton() {
    var container = document.getElementById('rcsButtons');
    var newBtn = document.createElement('div');
    newBtn.className = 'input-group mb-2';
    newBtn.innerHTML = '<input type="text" class="form-control" placeholder="Button text"><input type="text" class="form-control" placeholder="Button URL"><button class="btn btn-outline-danger" type="button" onclick="removeRcsButton(this)"><i class="fas fa-times"></i></button>';
    container.appendChild(newBtn);
}

function removeRcsButton(btn) {
    btn.closest('.input-group').remove();
}

function insertMergeField() {
    openPersonalisationModal();
}

function insertTrackingUrl() {
    document.getElementById('includeTrackableLink').checked = true;
    toggleTrackableLinkModal();
}

function openRcsWizard() {
    if (!rcsPersistentPayload && Object.keys(rcsCardsData).length === 0) {
        var hasStoredDraft = loadRcsFromStorage();
        if (!hasStoredDraft) {
            initializeRcsCard(1);
            rcsCurrentCard = 1;
            rcsCardCount = 1;
        }
    }
    
    hideRcsValidationErrors();
    
    var modal = new bootstrap.Modal(document.getElementById('rcsWizardModal'));
    modal.show();
    
    setTimeout(function() {
        updateRcsWizardPreview();
    }, 100);
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

function clearRcsContent() {
    resetRcsWizard();
    document.getElementById('rcsConfiguredSummary').classList.add('d-none');
    sessionStorage.removeItem('quicksms_rcs_draft');
}

var rcsCardCount = 1;
var rcsCurrentCard = 1;
var rcsMaxCards = 10;
var rcsMaxButtons = 3;

var rcsCardsData = {};
var rcsPersistentPayload = null;

function initializeRcsCard(cardNum) {
    if (!rcsCardsData[cardNum]) {
        rcsCardsData[cardNum] = {
            media: {
                source: null,
                url: null,
                file: null,
                fileName: null,
                fileSize: 0,
                dimensions: null,
                orientation: 'vertShort',
                zoom: 100,
                cropPosition: 'center',
                assetUuid: null,
                hostedUrl: null,
                originalUrl: null
            },
            description: '',
            textBody: '',
            buttons: []
        };
    }
    return rcsCardsData[cardNum];
}

function saveCurrentCardData() {
    var card = initializeRcsCard(rcsCurrentCard);
    
    card.media.source = rcsMediaData.source;
    card.media.url = rcsMediaData.url;
    card.media.file = rcsMediaData.file;
    card.media.fileName = rcsMediaData.file ? rcsMediaData.file.name : null;
    card.media.fileSize = rcsMediaData.fileSize;
    card.media.dimensions = rcsMediaData.dimensions;
    card.media.assetUuid = rcsMediaData.assetUuid;
    card.media.hostedUrl = rcsMediaData.hostedUrl;
    card.media.originalUrl = rcsMediaData.originalUrl;
    
    var orientChecked = document.querySelector('input[name="rcsOrientation"]:checked');
    card.media.orientation = orientChecked ? orientChecked.value : 'vertShort';
    card.media.zoom = parseInt(document.getElementById('rcsZoomSlider').value) || 100;
    var activeCrop = document.querySelector('.rcs-crop-btn.active');
    card.media.cropPosition = activeCrop ? activeCrop.dataset.position : 'center';
    
    card.description = document.getElementById('rcsDescription').value;
    card.textBody = document.getElementById('rcsTextBody').value;
    card.buttons = JSON.parse(JSON.stringify(rcsButtons));
}

function loadCardData(cardNum) {
    var card = initializeRcsCard(cardNum);
    
    rcsMediaData.source = card.media.source;
    rcsMediaData.url = card.media.url;
    rcsMediaData.file = card.media.file;
    rcsMediaData.fileSize = card.media.fileSize;
    rcsMediaData.dimensions = card.media.dimensions;
    rcsMediaData.assetUuid = card.media.assetUuid;
    rcsMediaData.hostedUrl = card.media.hostedUrl;
    rcsMediaData.originalUrl = card.media.originalUrl;
    
    if (card.media.url) {
        showRcsMediaPreview(card.media.hostedUrl || card.media.url);
        updateRcsImageInfo();
        document.getElementById('rcsZoomSlider').value = card.media.zoom;
        document.getElementById('rcsZoomValue').textContent = card.media.zoom + '%';
        document.getElementById('rcsMediaPreviewImg').style.transform = 'scale(' + (card.media.zoom / 100) + ')';
        
        document.querySelectorAll('.rcs-crop-btn').forEach(function(btn) {
            btn.classList.remove('active');
            if (btn.dataset.position === card.media.cropPosition) btn.classList.add('active');
        });
        var img = document.getElementById('rcsMediaPreviewImg');
        switch(card.media.cropPosition) {
            case 'top': img.style.objectPosition = 'center top'; break;
            case 'bottom': img.style.objectPosition = 'center bottom'; break;
            default: img.style.objectPosition = 'center center';
        }
        
        var orientRadio = document.getElementById('rcsOrient' + card.media.orientation.charAt(0).toUpperCase() + card.media.orientation.slice(1));
        if (orientRadio) orientRadio.checked = true;
        
        initRcsImageBaseline();
    } else {
        removeRcsMedia();
    }
    
    document.getElementById('rcsDescription').value = card.description;
    document.getElementById('rcsTextBody').value = card.textBody;
    updateRcsDescriptionCount();
    updateRcsTextBodyCount();
    
    rcsButtons = JSON.parse(JSON.stringify(card.buttons));
    renderRcsButtons();
    updateRcsButtonsPreview();
}

function validateRcsContent() {
    var errors = [];
    var warnings = [];
    
    for (var i = 1; i <= rcsCardCount; i++) {
        var card = rcsCardsData[i];
        if (!card) {
            errors.push('Card ' + i + ': No data configured');
            continue;
        }
        
        if (card.media.source === 'upload' && card.media.file) {
            if (card.media.fileSize > 250 * 1024) {
                errors.push('Card ' + i + ': Media file exceeds 250KB limit (' + (card.media.fileSize / 1024).toFixed(1) + 'KB)');
            }
            var allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (card.media.file.type && !allowedTypes.includes(card.media.file.type)) {
                errors.push('Card ' + i + ': Invalid media type. Only JPEG, PNG, GIF allowed.');
            }
        }
        
        if (card.description.length > 120) {
            warnings.push('Card ' + i + ': Description exceeds recommended 120 characters (' + card.description.length + ')');
        }
        if (card.textBody.length > 2000) {
            warnings.push('Card ' + i + ': Text body exceeds recommended 2000 characters (' + card.textBody.length + ')');
        }
        
        if (card.buttons.length > rcsMaxButtons) {
            errors.push('Card ' + i + ': Maximum ' + rcsMaxButtons + ' buttons allowed per card');
        }
        
        card.buttons.forEach(function(btn, btnIndex) {
            if (btn.label.length > 25) {
                errors.push('Card ' + i + ', Button ' + (btnIndex + 1) + ': Label exceeds 25 character limit');
            }
            if (btn.type === 'url' && !/^https?:\/\/.+/i.test(btn.url || '')) {
                errors.push('Card ' + i + ', Button ' + (btnIndex + 1) + ': Invalid URL format');
            }
            if (btn.type === 'phone' && !/^\+?[0-9\s\-()]{7,20}$/.test(btn.phone || '')) {
                errors.push('Card ' + i + ', Button ' + (btnIndex + 1) + ': Invalid phone number format');
            }
            if (btn.type === 'calendar') {
                if (!btn.eventTitle) errors.push('Card ' + i + ', Button ' + (btnIndex + 1) + ': Calendar event requires a title');
                if (!btn.eventStart) errors.push('Card ' + i + ', Button ' + (btnIndex + 1) + ': Calendar event requires start date/time');
                if (!btn.eventEnd) errors.push('Card ' + i + ', Button ' + (btnIndex + 1) + ': Calendar event requires end date/time');
            }
        });
    }
    
    if (rcsCardCount > rcsMaxCards) {
        errors.push('Maximum ' + rcsMaxCards + ' cards allowed in carousel');
    }
    
    return { valid: errors.length === 0, errors: errors, warnings: warnings };
}

function buildRcsPayload() {
    var isCarousel = document.getElementById('rcsTypeCarousel').checked;
    var payload = {
        type: isCarousel ? 'carousel' : 'single',
        cardCount: rcsCardCount,
        cards: [],
        placeholders: [],
        createdAt: new Date().toISOString(),
        userId: null
    };
    
    for (var i = 1; i <= rcsCardCount; i++) {
        var card = rcsCardsData[i];
        if (!card) continue;
        
        var cardPayload = {
            order: i,
            media: {
                source: card.media.source,
                url: card.media.url,
                fileName: card.media.fileName,
                fileSize: card.media.fileSize,
                dimensions: card.media.dimensions,
                orientation: card.media.orientation,
                zoom: card.media.zoom,
                cropPosition: card.media.cropPosition
            },
            description: card.description,
            textBody: card.textBody,
            buttons: card.buttons.map(function(btn, idx) {
                return {
                    order: idx + 1,
                    label: btn.label,
                    type: btn.type,
                    action: btn.type === 'url' ? { url: btn.url } :
                            btn.type === 'phone' ? { phoneNumber: btn.phone } :
                            btn.type === 'calendar' ? {
                                title: btn.eventTitle,
                                startTime: btn.eventStart,
                                endTime: btn.eventEnd,
                                description: btn.eventDesc || ''
                            } : null
                };
            })
        };
        
        var placeholderRegex = /\{\{([^}]+)\}\}/g;
        var match;
        while ((match = placeholderRegex.exec(card.description)) !== null) {
            if (!payload.placeholders.includes(match[1])) payload.placeholders.push(match[1]);
        }
        while ((match = placeholderRegex.exec(card.textBody)) !== null) {
            if (!payload.placeholders.includes(match[1])) payload.placeholders.push(match[1]);
        }
        
        payload.cards.push(cardPayload);
    }
    
    return payload;
}

function persistRcsPayload(payload) {
    rcsPersistentPayload = payload;
    
    console.log('RCS Payload persisted:', JSON.stringify(payload, null, 2));
    
    sessionStorage.setItem('quicksms_rcs_draft', JSON.stringify(payload));
    
    // TODO: Google RCS API Integration
    // 1. Upload media files to Google RCS CDN for each card
    //    - POST /v1/files:upload with media content
    //    - Store returned file URIs in payload.cards[].media.rcsFileUri
    // 2. Transform payload to Google RCS Business Messages format:
    //    - For single card: contentMessage.richCard.standaloneCard
    //    - For carousel: contentMessage.richCard.carouselCard
    // 3. Validate RCS Agent ID is properly configured
    // 4. Store payload to database with user_id and campaign reference:
    //    - INSERT INTO rcs_content (campaign_id, user_id, payload, created_at)
    // 5. When campaign is sent, call Google RCS API:
    //    - POST /v1/phones/{phoneNumber}/agentMessages
    //    - Handle delivery receipts and status callbacks
}

function applyRcsContent() {
    saveCurrentCardData();
    
    var validation = validateRcsContent();
    
    hideRcsValidationErrors();
    
    if (!validation.valid) {
        showRcsValidationErrors(validation.errors, validation.warnings);
        return;
    }
    
    if (validation.warnings.length > 0) {
        showRcsValidationWarnings(validation.warnings);
    }
    
    var payload = buildRcsPayload();
    persistRcsPayload(payload);
    
    var summaryText = payload.type === 'carousel' 
        ? 'RCS Carousel (' + payload.cardCount + ' cards) configured'
        : 'RCS Rich Card configured';
    
    var totalButtons = payload.cards.reduce(function(sum, c) { return sum + c.buttons.length; }, 0);
    if (totalButtons > 0) {
        summaryText += ' with ' + totalButtons + ' action button' + (totalButtons > 1 ? 's' : '');
    }
    
    document.getElementById('rcsConfiguredText').textContent = summaryText;
    document.getElementById('rcsConfiguredSummary').classList.remove('d-none');
    
    bootstrap.Modal.getInstance(document.getElementById('rcsWizardModal')).hide();
}

function showRcsValidationErrors(errors, warnings) {
    var container = document.getElementById('rcsValidationErrors');
    if (!container) return;
    
    var html = '<div class="alert alert-danger mb-3"><h6 class="mb-2"><i class="fas fa-exclamation-circle me-1"></i>Please fix the following errors:</h6><ul class="mb-0 ps-3">';
    errors.forEach(function(err) {
        html += '<li>' + escapeHtml(err) + '</li>';
    });
    html += '</ul></div>';
    
    if (warnings.length > 0) {
        html += '<div class="alert alert-warning mb-3"><h6 class="mb-2"><i class="fas fa-exclamation-triangle me-1"></i>Warnings:</h6><ul class="mb-0 ps-3">';
        warnings.forEach(function(warn) {
            html += '<li>' + escapeHtml(warn) + '</li>';
        });
        html += '</ul></div>';
    }
    
    container.innerHTML = html;
    container.classList.remove('d-none');
    container.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function showRcsValidationWarnings(warnings) {
    var container = document.getElementById('rcsValidationErrors');
    if (!container || warnings.length === 0) return;
    
    var html = '<div class="alert alert-warning mb-3"><h6 class="mb-2"><i class="fas fa-exclamation-triangle me-1"></i>Warnings (content saved with warnings):</h6><ul class="mb-0 ps-3">';
    warnings.forEach(function(warn) {
        html += '<li>' + escapeHtml(warn) + '</li>';
    });
    html += '</ul></div>';
    
    container.innerHTML = html;
    container.classList.remove('d-none');
    
    setTimeout(function() {
        container.classList.add('d-none');
    }, 5000);
}

function hideRcsValidationErrors() {
    var container = document.getElementById('rcsValidationErrors');
    if (container) {
        container.classList.add('d-none');
        container.innerHTML = '';
    }
}

function updateRcsWizardPreview() {
    var container = document.getElementById('rcsWizardPreviewContainer');
    if (!container) return;
    
    var agentSelect = document.getElementById('rcsAgent');
    var agentName = 'Select an Agent';
    var agentTagline = '';
    var agentLogo = '';
    var agentBrandColor = '#886CC0';
    
    if (agentSelect && agentSelect.selectedIndex > 0) {
        var selectedOption = agentSelect.options[agentSelect.selectedIndex];
        agentName = selectedOption.getAttribute('data-name') || selectedOption.text;
        agentTagline = selectedOption.getAttribute('data-tagline') || '';
        agentLogo = selectedOption.getAttribute('data-logo') || '';
        agentBrandColor = selectedOption.getAttribute('data-brand-color') || '#886CC0';
    }
    
    if (!agentLogo) {
        var initials = agentName.split(' ').map(function(w) { return w.charAt(0); }).join('').substring(0, 2).toUpperCase();
        var bgColor = agentBrandColor.replace('#', '');
        agentLogo = 'https://ui-avatars.com/api/?name=' + encodeURIComponent(initials) + '&background=' + bgColor + '&color=fff&size=80';
    }
    
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
    
    initRcsCarouselBehavior();
}

function renderRcsPhoneFrame(agent, messageContent) {
    var badge = '<svg class="rcs-verified-badge" viewBox="0 0 24 24" fill="#1a73e8"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm-2 16l-4-4 1.41-1.41L10 14.17l6.59-6.59L18 9l-8 8z"/></svg>';
    var tagline = agent.tagline ? '<span class="rcs-agent-tagline">' + escapeHtml(agent.tagline) + '</span>' : '';
    
    return '<div class="rcs-phone-frame">' +
        '<div class="rcs-status-bar"><span class="rcs-status-time">9:30</span><div class="rcs-status-icons"><span class="rcs-status-5g">5G</span><svg class="rcs-status-signal" viewBox="0 0 24 24" fill="currentColor" width="16" height="16"><path d="M2 22h20V2L2 22zm18-2H6.83L20 6.83V20z"/></svg><svg class="rcs-status-battery" viewBox="0 0 24 24" fill="currentColor" width="16" height="16"><path d="M15.67 4H14V2h-4v2H8.33C7.6 4 7 4.6 7 5.33v15.33C7 21.4 7.6 22 8.33 22h7.33c.74 0 1.34-.6 1.34-1.33V5.33C17 4.6 16.4 4 15.67 4z"/></svg></div></div>' +
        '<div class="rcs-header">' +
            '<button class="rcs-back-button"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/></svg></button>' +
            '<div class="rcs-agent-info">' +
                '<div class="rcs-agent-logo-wrapper"><img src="' + escapeHtml(agent.logo) + '" alt="' + escapeHtml(agent.name) + '" class="rcs-agent-logo"/>' + badge + '</div>' +
                '<div class="rcs-agent-details"><span class="rcs-agent-name">' + escapeHtml(agent.name) + '</span>' + tagline + '</div>' +
            '</div>' +
            '<div class="rcs-header-actions"><button class="rcs-header-btn"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg></button><button class="rcs-header-btn"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/></svg></button></div>' +
        '</div>' +
        '<div class="rcs-chat-area"><div class="rcs-timestamp">Today</div><div class="rcs-message">' + messageContent + '</div></div>' +
        '<div class="rcs-input-bar"><button class="rcs-input-action"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg></button><input type="text" class="rcs-input-field" placeholder="RCS message" readonly/><button class="rcs-input-action"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm3.5-9c.83 0 1.5-.67 1.5-1.5S16.33 8 15.5 8 14 8.67 14 9.5s.67 1.5 1.5 1.5zm-7 0c.83 0 1.5-.67 1.5-1.5S9.33 8 8.5 8 7 8.67 7 9.5 7.67 11 8.5 11zm3.5 6.5c2.33 0 4.31-1.46 5.11-3.5H6.89c.8 2.04 2.78 3.5 5.11 3.5z"/></svg></button><button class="rcs-input-action"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/></svg></button><button class="rcs-send-button"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 14c1.66 0 2.99-1.34 2.99-3L15 5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3zm5.3-3c0 3-2.54 5.1-5.3 5.1S6.7 14 6.7 11H5c0 3.41 2.72 6.23 6 6.72V21h2v-3.28c3.28-.48 6-3.3 6-6.72h-1.7z"/></svg></button></div>' +
    '</div>';
}

function renderRcsCardPreview(cardNum) {
    var card = rcsCardsData[cardNum] || initializeRcsCard(cardNum);
    var mediaHtml = '';
    var orientChecked = document.querySelector('input[name="rcsOrientation"]:checked');
    var orientation = orientChecked ? orientChecked.value : 'vertical_short';
    var heights = { 'vertical_short': 'short', 'vertical_medium': 'medium', 'horizontal': 'tall' };
    var heightClass = heights[orientation] || 'medium';
    var heightPx = { 'short': '112px', 'medium': '168px', 'tall': '264px' };
    
    if (card.media && card.media.url) {
        mediaHtml = '<div class="rcs-media rcs-media--' + heightClass + '" style="height: ' + heightPx[heightClass] + ';"><img src="' + escapeHtml(card.media.url) + '" class="rcs-media-image" loading="lazy"/></div>';
    } else if (rcsMediaData.url) {
        mediaHtml = '<div class="rcs-media rcs-media--' + heightClass + '" style="height: ' + heightPx[heightClass] + ';"><img src="' + escapeHtml(rcsMediaData.url) + '" class="rcs-media-image" loading="lazy"/></div>';
    } else {
        mediaHtml = '<div class="rcs-media rcs-media--' + heightClass + '" style="height: ' + heightPx[heightClass] + '; background: #e0e0e0; display: flex; align-items: center; justify-content: center;"><span style="color: #888; font-size: 12px;">No media</span></div>';
    }
    
    var descEl = document.getElementById('rcsDescription');
    var bodyEl = document.getElementById('rcsTextBody');
    var description = descEl ? descEl.value : (card.description || '');
    var textBody = bodyEl ? bodyEl.value : (card.textBody || '');
    
    var titleHtml = description ? '<h3 class="rcs-card-title">' + escapeHtml(description) + '</h3>' : '';
    var descHtml = textBody ? '<p class="rcs-card-description">' + escapeHtml(textBody) + '</p>' : '';
    
    var buttonsHtml = '';
    var btns = rcsButtons.length > 0 ? rcsButtons : (card.buttons || []);
    if (btns.length > 0) {
        buttonsHtml = '<div class="rcs-buttons">';
        btns.forEach(function(btn) {
            var icon = getRcsButtonIcon(btn.type);
            buttonsHtml += '<button type="button" class="rcs-button">' + (icon ? '<span class="rcs-button-icon-wrapper">' + icon + '</span>' : '') + '<span class="rcs-button-label">' + escapeHtml(btn.label || 'Button') + '</span></button>';
        });
        buttonsHtml += '</div>';
    }
    
    var mediaClass = (card.media && card.media.url) || rcsMediaData.url ? 'rcs-card--has-media' : 'rcs-card--no-media';
    return '<div class="rcs-card ' + mediaClass + '">' + mediaHtml + '<div class="rcs-card-content">' + titleHtml + descHtml + '</div>' + buttonsHtml + '</div>';
}

function renderRcsCarouselPreview() {
    var cardsHtml = '';
    for (var i = 1; i <= rcsCardCount; i++) {
        cardsHtml += '<div class="rcs-carousel-item" style="min-width: 256px; max-width: 256px;">' + renderRcsCardPreviewForCarousel(i) + '</div>';
    }
    var dots = '';
    for (var j = 1; j <= rcsCardCount; j++) {
        dots += '<button class="rcs-carousel-dot ' + (j === 1 ? 'active' : '') + '" data-index="' + (j - 1) + '"></button>';
    }
    return '<div class="rcs-carousel"><div class="rcs-carousel-track">' + cardsHtml + '</div><div class="rcs-carousel-indicators">' + dots + '</div></div>';
}

function renderRcsCardPreviewForCarousel(cardNum) {
    var card = rcsCardsData[cardNum] || {};
    var mediaHtml = '';
    var heightClass = 'medium';
    var heightPx = '168px';
    
    if (card.media && card.media.url) {
        mediaHtml = '<div class="rcs-media rcs-media--' + heightClass + '" style="height: ' + heightPx + ';"><img src="' + escapeHtml(card.media.url) + '" class="rcs-media-image" loading="lazy"/></div>';
    } else {
        mediaHtml = '<div class="rcs-media rcs-media--' + heightClass + '" style="height: ' + heightPx + '; background: #e0e0e0; display: flex; align-items: center; justify-content: center;"><span style="color: #888; font-size: 11px;">Card ' + cardNum + '</span></div>';
    }
    
    var titleHtml = card.description ? '<h3 class="rcs-card-title">' + escapeHtml(card.description) + '</h3>' : '';
    var descHtml = card.textBody ? '<p class="rcs-card-description">' + escapeHtml(card.textBody.substring(0, 80)) + (card.textBody.length > 80 ? '...' : '') + '</p>' : '';
    
    var buttonsHtml = '';
    var btns = card.buttons || [];
    if (btns.length > 0) {
        buttonsHtml = '<div class="rcs-buttons">';
        btns.forEach(function(btn) {
            var icon = getRcsButtonIcon(btn.type);
            buttonsHtml += '<button type="button" class="rcs-button">' + (icon ? '<span class="rcs-button-icon-wrapper">' + icon + '</span>' : '') + '<span class="rcs-button-label">' + escapeHtml(btn.label || 'Button') + '</span></button>';
        });
        buttonsHtml += '</div>';
    }
    
    return '<div class="rcs-card rcs-carousel-card">' + mediaHtml + '<div class="rcs-card-content">' + titleHtml + descHtml + '</div>' + buttonsHtml + '</div>';
}

function getRcsButtonIcon(type) {
    var icons = {
        url: '<svg class="rcs-button-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/></svg>',
        phone: '<svg class="rcs-button-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/></svg>',
        calendar: '<svg class="rcs-button-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11z"/></svg>',
        reply: '<svg class="rcs-button-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M10 9V5l-7 7 7 7v-4.1c5 0 8.5 1.6 11 5.1-1-5-4-10-11-11z"/></svg>'
    };
    return icons[type] || icons.url;
}

function initRcsCarouselBehavior() {
    var carousel = document.querySelector('#rcsWizardPreviewContainer .rcs-carousel-track');
    if (!carousel) return;
    var dots = document.querySelectorAll('#rcsWizardPreviewContainer .rcs-carousel-dot');
    carousel.addEventListener('scroll', function() {
        var scrollLeft = carousel.scrollLeft;
        var itemWidth = carousel.firstElementChild?.clientWidth || 256;
        var currentIndex = Math.round(scrollLeft / (itemWidth + 8));
        dots.forEach(function(dot, i) { dot.classList.toggle('active', i === currentIndex); });
    });
    dots.forEach(function(dot, i) {
        dot.addEventListener('click', function() {
            var itemWidth = carousel.firstElementChild?.clientWidth || 256;
            carousel.scrollTo({ left: i * (itemWidth + 8), behavior: 'smooth' });
        });
    });
}

function resetRcsWizard() {
    rcsCardsData = {};
    rcsCardCount = 1;
    rcsCurrentCard = 1;
    rcsButtons = [];
    rcsPersistentPayload = null;
    
    document.getElementById('rcsTypeSingle').checked = true;
    toggleRcsMessageType();
    removeRcsMedia();
    document.getElementById('rcsDescription').value = '';
    document.getElementById('rcsTextBody').value = '';
    updateRcsDescriptionCount();
    updateRcsTextBodyCount();
    renderRcsButtons();
    updateRcsButtonsPreview();
    hideRcsValidationErrors();
    
    initializeRcsCard(1);
}

function loadRcsFromStorage() {
    var stored = sessionStorage.getItem('quicksms_rcs_draft');
    if (!stored) return false;
    
    try {
        var payload = JSON.parse(stored);
        
        document.getElementById('rcsType' + (payload.type === 'carousel' ? 'Carousel' : 'Single')).checked = true;
        toggleRcsMessageType();
        
        rcsCardCount = payload.cardCount;
        payload.cards.forEach(function(cardData) {
            var cardNum = cardData.order;
            rcsCardsData[cardNum] = {
                media: {
                    source: cardData.media.source,
                    url: cardData.media.url,
                    file: null,
                    fileName: cardData.media.fileName,
                    fileSize: cardData.media.fileSize,
                    dimensions: cardData.media.dimensions,
                    orientation: cardData.media.orientation,
                    zoom: cardData.media.zoom,
                    cropPosition: cardData.media.cropPosition
                },
                description: cardData.description,
                textBody: cardData.textBody,
                buttons: cardData.buttons.map(function(btn) {
                    var buttonObj = { label: btn.label, type: btn.type };
                    if (btn.type === 'url') buttonObj.url = btn.action.url;
                    if (btn.type === 'phone') buttonObj.phone = btn.action.phoneNumber;
                    if (btn.type === 'calendar') {
                        buttonObj.eventTitle = btn.action.title;
                        buttonObj.eventStart = btn.action.startTime;
                        buttonObj.eventEnd = btn.action.endTime;
                        buttonObj.eventDesc = btn.action.description;
                    }
                    return buttonObj;
                })
            };
        });
        
        loadCardData(1);
        rcsPersistentPayload = payload;
        return true;
    } catch (e) {
        console.error('Failed to load RCS draft:', e);
        return false;
    }
}

function toggleRcsMessageType() {
    saveCurrentCardData();
    
    var isCarousel = document.getElementById('rcsTypeCarousel').checked;
    document.getElementById('rcsCarouselNav').classList.toggle('d-none', !isCarousel);
    document.getElementById('rcsCurrentCardLabel').classList.toggle('d-none', !isCarousel);
    
    if (!isCarousel) {
        for (var i = 2; i <= rcsCardCount; i++) {
            delete rcsCardsData[i];
        }
        rcsCardCount = 1;
        rcsCurrentCard = 1;
        resetRcsCardTabs();
        loadCardData(1);
    }
    updateRcsCardCount();
}

function resetRcsCardTabs() {
    var tabsContainer = document.getElementById('rcsCardTabs');
    var addBtn = document.getElementById('rcsAddCardBtn');
    tabsContainer.querySelectorAll('.rcs-card-tab').forEach(function(tab, index) {
        if (index > 0) tab.remove();
    });
    var firstTab = tabsContainer.querySelector('.rcs-card-tab');
    if (firstTab) {
        firstTab.classList.add('active');
        firstTab.classList.remove('btn-outline-primary');
        firstTab.classList.add('btn-primary');
    }
    addBtn.disabled = false;
}

function addRcsCard() {
    if (rcsCardCount >= rcsMaxCards) return;
    
    saveCurrentCardData();
    
    rcsCardCount++;
    initializeRcsCard(rcsCardCount);
    
    var tabsContainer = document.getElementById('rcsCardTabs');
    var addBtn = document.getElementById('rcsAddCardBtn');
    
    var newTab = document.createElement('button');
    newTab.type = 'button';
    newTab.className = 'btn btn-outline-primary btn-sm rcs-card-tab';
    newTab.setAttribute('data-card', rcsCardCount);
    newTab.textContent = 'Card ' + rcsCardCount;
    newTab.onclick = function() { selectRcsCard(rcsCardCount); };
    
    tabsContainer.insertBefore(newTab, addBtn);
    
    updateRcsCardCount();
    selectRcsCard(rcsCardCount);
    
    if (rcsCardCount >= rcsMaxCards) {
        addBtn.disabled = true;
    }
}

function deleteRcsCard(cardNum) {
    if (rcsCardCount <= 1) return;
    
    delete rcsCardsData[cardNum];
    
    var newCardsData = {};
    var newIndex = 1;
    for (var i = 1; i <= rcsCardCount; i++) {
        if (i !== cardNum && rcsCardsData[i]) {
            newCardsData[newIndex] = rcsCardsData[i];
            newIndex++;
        }
    }
    rcsCardsData = newCardsData;
    rcsCardCount--;
    
    rebuildCardTabs();
    
    if (rcsCurrentCard > rcsCardCount) {
        rcsCurrentCard = rcsCardCount;
    }
    selectRcsCard(rcsCurrentCard);
    updateRcsCardCount();
}

function rebuildCardTabs() {
    var tabsContainer = document.getElementById('rcsCardTabs');
    var addBtn = document.getElementById('rcsAddCardBtn');
    
    tabsContainer.querySelectorAll('.rcs-card-tab').forEach(function(tab) {
        tab.remove();
    });
    
    for (var i = 1; i <= rcsCardCount; i++) {
        var tab = document.createElement('button');
        tab.type = 'button';
        tab.className = 'btn btn-sm rcs-card-tab ' + (i === rcsCurrentCard ? 'btn-primary active' : 'btn-outline-primary');
        tab.setAttribute('data-card', i);
        tab.textContent = 'Card ' + i;
        (function(cardNum) {
            tab.onclick = function() { selectRcsCard(cardNum); };
        })(i);
        tabsContainer.insertBefore(tab, addBtn);
    }
    
    addBtn.disabled = rcsCardCount >= rcsMaxCards;
}

function selectRcsCard(cardNum) {
    if (rcsCurrentCard !== cardNum && isRcsImageDirty()) {
        showRcsUnsavedChangesModal({ type: 'selectCard', cardNum: cardNum });
        return;
    }
    
    selectRcsCardDirect(cardNum);
}

function selectRcsCardDirect(cardNum) {
    if (rcsCurrentCard !== cardNum) {
        saveCurrentCardData();
        clearRcsImageDirtyState();
    }
    
    rcsCurrentCard = cardNum;
    
    document.querySelectorAll('.rcs-card-tab').forEach(function(tab) {
        var tabCard = parseInt(tab.getAttribute('data-card'));
        if (tabCard === cardNum) {
            tab.classList.remove('btn-outline-primary');
            tab.classList.add('btn-primary', 'active');
        } else {
            tab.classList.remove('btn-primary', 'active');
            tab.classList.add('btn-outline-primary');
        }
    });
    
    document.getElementById('rcsCurrentCardName').textContent = 'Card ' + cardNum;
    loadCardData(cardNum);
}

function updateRcsCardCount() {
    document.getElementById('rcsCardCount').textContent = rcsCardCount + ' / ' + rcsMaxCards;
}

var rcsMediaData = {
    source: null,
    url: null,
    file: null,
    dimensions: null,
    fileSize: 0,
    assetUuid: null,
    hostedUrl: null,
    originalUrl: null
};

var rcsAllowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
var rcsMaxFileSize = 250 * 1024;
var rcsDraftSession = generateDraftSession();
var rcsEditDebounceTimer = null;

var rcsImageDirtyState = {
    isDirty: false,
    baselineZoom: 100,
    baselineCropPosition: 'center',
    baselineOrientation: 'vertical_short',
    pendingNavigation: null,
    hasBeenEdited: false
};

function initRcsImageBaseline() {
    var current = getCurrentEditParams();
    rcsImageDirtyState.baselineZoom = current.zoom;
    rcsImageDirtyState.baselineCropPosition = current.crop_position;
    rcsImageDirtyState.baselineOrientation = current.orientation;
    rcsImageDirtyState.isDirty = false;
    rcsImageDirtyState.hasBeenEdited = false;
    updateRcsSaveButtonVisibility();
}

function markRcsImageDirty() {
    if (rcsMediaData.source === 'url' && rcsMediaData.originalUrl && !rcsMediaData.hostedUrl) {
        var current = getCurrentEditParams();
        var hasChanges = current.zoom !== rcsImageDirtyState.baselineZoom ||
                         current.crop_position !== rcsImageDirtyState.baselineCropPosition ||
                         current.orientation !== rcsImageDirtyState.baselineOrientation;
        
        rcsImageDirtyState.isDirty = hasChanges;
        if (hasChanges) rcsImageDirtyState.hasBeenEdited = true;
        updateRcsSaveButtonVisibility();
    }
}

function clearRcsImageDirtyState() {
    rcsImageDirtyState.isDirty = false;
    rcsImageDirtyState.baselineZoom = 100;
    rcsImageDirtyState.baselineCropPosition = 'center';
    rcsImageDirtyState.baselineOrientation = 'vertical_short';
    rcsImageDirtyState.pendingNavigation = null;
    rcsImageDirtyState.hasBeenEdited = false;
    updateRcsSaveButtonVisibility();
}

function updateRcsSaveButtonVisibility() {
    var saveBtn = document.getElementById('rcsImageSaveBtn');
    if (saveBtn) {
        var shouldShow = rcsMediaData.source === 'url' && 
                         rcsMediaData.originalUrl && 
                         !rcsMediaData.hostedUrl &&
                         rcsImageDirtyState.isDirty;
        saveBtn.classList.toggle('d-none', !shouldShow);
    }
}

function isRcsImageDirty() {
    return rcsMediaData.source === 'url' && 
           rcsMediaData.originalUrl && 
           !rcsMediaData.hostedUrl &&
           rcsImageDirtyState.isDirty;
}

function showRcsUnsavedChangesModal(pendingAction) {
    rcsImageDirtyState.pendingNavigation = pendingAction;
    var modal = new bootstrap.Modal(document.getElementById('rcsUnsavedChangesModal'));
    modal.show();
}

function hideRcsUnsavedChangesModal() {
    var modalEl = document.getElementById('rcsUnsavedChangesModal');
    var modal = bootstrap.Modal.getInstance(modalEl);
    if (modal) modal.hide();
}

function saveRcsImageEdits() {
    if (!rcsMediaData.originalUrl || rcsMediaData.source !== 'url') return;
    
    showRcsProcessingIndicator();
    var editParams = getCurrentEditParams();
    
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
            clearRcsImageDirtyState();
            initRcsImageBaseline();
            updateRcsWizardPreview();
        } else if (data.error) {
            showRcsMediaError(data.error);
        }
    })
    .catch(function(err) {
        hideRcsProcessingIndicator();
        showRcsMediaError('Failed to process image. Please try again.');
    });
}

function saveRcsImageEditsAndContinue() {
    hideRcsUnsavedChangesModal();
    
    if (!rcsMediaData.originalUrl || rcsMediaData.source !== 'url') {
        executePendingNavigation();
        return;
    }
    
    showRcsProcessingIndicator();
    var editParams = getCurrentEditParams();
    
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
            clearRcsImageDirtyState();
            updateRcsWizardPreview();
            executePendingNavigation();
        } else if (data.error) {
            showRcsMediaError(data.error);
        }
    })
    .catch(function(err) {
        hideRcsProcessingIndicator();
        showRcsMediaError('Failed to process image. Please try again.');
    });
}

function discardRcsImageEdits() {
    hideRcsUnsavedChangesModal();
    
    var baselineZoom = rcsImageDirtyState.baselineZoom || 100;
    var baselineCrop = rcsImageDirtyState.baselineCropPosition || 'center';
    var baselineOrient = rcsImageDirtyState.baselineOrientation || 'vertical_short';
    
    document.getElementById('rcsZoomSlider').value = baselineZoom;
    document.getElementById('rcsZoomValue').textContent = baselineZoom + '%';
    document.getElementById('rcsMediaPreviewImg').style.transform = 'scale(' + (baselineZoom / 100) + ')';
    
    document.querySelectorAll('.rcs-crop-btn').forEach(function(btn) {
        btn.classList.remove('active');
        if (btn.dataset.position === baselineCrop) btn.classList.add('active');
    });
    var img = document.getElementById('rcsMediaPreviewImg');
    switch(baselineCrop) {
        case 'top': img.style.objectPosition = 'center top'; break;
        case 'bottom': img.style.objectPosition = 'center bottom'; break;
        default: img.style.objectPosition = 'center center';
    }
    
    var orientId = 'rcsOrient' + baselineOrient.split('_').map(function(s) { return s.charAt(0).toUpperCase() + s.slice(1); }).join('');
    var orientRadio = document.getElementById(orientId);
    if (orientRadio) orientRadio.checked = true;
    else document.getElementById('rcsOrientVertShort').checked = true;
    
    if (rcsMediaData.originalUrl) {
        rcsMediaData.url = rcsMediaData.originalUrl;
        rcsMediaData.hostedUrl = null;
        rcsMediaData.assetUuid = null;
        showRcsMediaPreview(rcsMediaData.originalUrl);
    }
    
    clearRcsImageDirtyState();
    initRcsImageBaseline();
    updateRcsWizardPreview();
    
    executePendingNavigation();
}

function cancelRcsUnsavedChanges() {
    hideRcsUnsavedChangesModal();
    rcsImageDirtyState.pendingNavigation = null;
}

function executePendingNavigation() {
    var pendingAction = rcsImageDirtyState.pendingNavigation;
    rcsImageDirtyState.pendingNavigation = null;
    
    if (!pendingAction) return;
    
    if (pendingAction.type === 'selectCard') {
        selectRcsCardDirect(pendingAction.cardNum);
    } else if (pendingAction.type === 'closeWizard') {
        bootstrap.Modal.getInstance(document.getElementById('rcsWizardModal')).hide();
    } else if (pendingAction.type === 'applyContent') {
        applyRcsContent();
    } else if (pendingAction.type === 'changeType') {
        if (pendingAction.targetValue) {
            document.getElementById(pendingAction.targetValue === 'single' ? 'rcsTypeSingle' : 'rcsTypeCarousel').checked = true;
        }
        toggleRcsMessageType();
        updateCarouselOrientationWarning();
        updateRcsWizardPreview();
    }
}

function handleRcsWizardClose() {
    if (isRcsImageDirty()) {
        showRcsUnsavedChangesModal({ type: 'closeWizard' });
    } else {
        bootstrap.Modal.getInstance(document.getElementById('rcsWizardModal')).hide();
    }
}

function handleRcsApplyContent() {
    if (isRcsImageDirty()) {
        showRcsUnsavedChangesModal({ type: 'applyContent' });
    } else {
        applyRcsContent();
    }
}

function generateDraftSession() {
    return 'draft_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
}

function getCurrentEditParams() {
    var zoomSlider = document.getElementById('rcsZoomSlider');
    var zoom = zoomSlider ? parseInt(zoomSlider.value) : 100;
    
    var cropPosition = 'center';
    document.querySelectorAll('.rcs-crop-btn.active').forEach(function(btn) {
        cropPosition = btn.dataset.position || 'center';
    });
    
    var orientation = 'vertical_short';
    if (document.getElementById('rcsOrientVertTall') && document.getElementById('rcsOrientVertTall').checked) {
        orientation = 'vertical_tall';
    } else if (document.getElementById('rcsOrientHoriz') && document.getElementById('rcsOrientHoriz').checked) {
        orientation = 'horizontal';
    }
    
    return {
        zoom: zoom,
        crop_position: cropPosition,
        orientation: orientation
    };
}

function processAssetServerSide(isUpdate) {
    if (rcsEditDebounceTimer) {
        clearTimeout(rcsEditDebounceTimer);
    }
    
    rcsEditDebounceTimer = setTimeout(function() {
        var editParams = getCurrentEditParams();
        
        if (editParams.zoom === 100 && editParams.crop_position === 'center' && !rcsMediaData.assetUuid) {
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

function showRcsProcessingIndicator() {
    var preview = document.getElementById('rcsMediaPreview');
    if (preview && !preview.querySelector('.rcs-processing-overlay')) {
        var overlay = document.createElement('div');
        overlay.className = 'rcs-processing-overlay';
        overlay.innerHTML = '<div class="spinner-border spinner-border-sm text-primary" role="status"><span class="visually-hidden">Processing...</span></div>';
        overlay.style.cssText = 'position:absolute;top:0;left:0;right:0;bottom:0;background:rgba(255,255,255,0.8);display:flex;align-items:center;justify-content:center;z-index:10;';
        preview.style.position = 'relative';
        preview.appendChild(overlay);
    }
}

function hideRcsProcessingIndicator() {
    var overlay = document.querySelector('.rcs-processing-overlay');
    if (overlay) {
        overlay.remove();
    }
}

function toggleRcsMediaSource() {
    var isUpload = document.getElementById('rcsMediaUpload').checked;
    document.getElementById('rcsMediaUrlSection').classList.toggle('d-none', isUpload);
    document.getElementById('rcsMediaUploadSection').classList.toggle('d-none', !isUpload);
    hideRcsMediaError();
}

function loadRcsMediaUrl() {
    var url = document.getElementById('rcsMediaUrlInput').value.trim();
    if (!url) return;
    
    hideRcsMediaError();
    
    var urlLower = url.toLowerCase();
    var validExtensions = ['.jpg', '.jpeg', '.png', '.gif'];
    var hasValidExtension = validExtensions.some(function(ext) {
        return urlLower.includes(ext);
    });
    
    if (!hasValidExtension) {
        var allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];
    }
    
    var loadBtn = document.querySelector('#rcsMediaUrlSection button');
    if (loadBtn) {
        loadBtn.disabled = true;
        loadBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    }
    
    fetch(url, { method: 'HEAD', mode: 'cors' })
        .then(function(response) {
            if (!response.ok) {
                throw new Error('URL not accessible');
            }
            
            var contentType = response.headers.get('Content-Type') || '';
            var contentLength = response.headers.get('Content-Length');
            var fileSize = contentLength ? parseInt(contentLength, 10) : 0;
            
            var validTypes = ['image/jpeg', 'image/png', 'image/gif'];
            var isValidType = validTypes.some(function(t) {
                return contentType.toLowerCase().includes(t);
            }) || hasValidExtension;
            
            if (!isValidType) {
                throw new Error('Unsupported file type. Only JPEG, PNG, and GIF images are allowed.');
            }
            
            if (fileSize > 0 && fileSize > rcsMaxFileSize) {
                throw new Error('File size (' + (fileSize / 1024).toFixed(1) + ' KB) exceeds 250 KB limit.');
            }
            
            return { fileSize: fileSize, contentType: contentType };
        })
        .catch(function(err) {
            return { fileSize: 0, contentType: '', corsBlocked: err.message === 'Failed to fetch' };
        })
        .then(function(metadata) {
            var img = new Image();
            img.crossOrigin = 'anonymous';
            img.onload = function() {
                rcsMediaData.source = 'url';
                rcsMediaData.url = url;
                rcsMediaData.originalUrl = url;
                rcsMediaData.dimensions = { width: img.width, height: img.height };
                rcsMediaData.fileSize = metadata.fileSize || 0;
                rcsMediaData.assetUuid = null;
                rcsMediaData.hostedUrl = null;
                showRcsMediaPreview(url);
                updateRcsImageInfo();
                initRcsImageBaseline();
                
                if (metadata.corsBlocked) {
                    showRcsMediaWarning('File size could not be verified. Ensure image is under 250 KB.');
                }
                
                if (loadBtn) {
                    loadBtn.disabled = false;
                    loadBtn.innerHTML = '<i class="fas fa-check"></i>';
                }
            };
            img.onerror = function() {
                showRcsMediaError('Unable to load image from URL. Please check the URL is publicly accessible.');
                if (loadBtn) {
                    loadBtn.disabled = false;
                    loadBtn.innerHTML = '<i class="fas fa-check"></i>';
                }
            };
            img.src = url;
        })
        .catch(function(err) {
            showRcsMediaError(err.message || 'Unable to load image from URL.');
            if (loadBtn) {
                loadBtn.disabled = false;
                loadBtn.innerHTML = '<i class="fas fa-check"></i>';
            }
        });
}

function showRcsMediaWarning(message) {
    var errorEl = document.getElementById('rcsMediaError');
    var errorTextEl = document.getElementById('rcsMediaErrorText');
    errorTextEl.textContent = message;
    errorEl.classList.remove('d-none');
    errorEl.classList.remove('alert-danger');
    errorEl.classList.add('alert-warning');
}

function handleRcsFileUpload(file) {
    hideRcsMediaError();
    
    if (!file) return;
    
    if (!rcsAllowedTypes.includes(file.type)) {
        showRcsMediaError('Unsupported file type. Only JPEG, PNG, and GIF images are allowed.');
        return;
    }
    
    if (file.size > rcsMaxFileSize) {
        showRcsMediaError('File size exceeds 250 KB limit. Please choose a smaller file.');
        return;
    }
    
    var reader = new FileReader();
    reader.onload = function(e) {
        var img = new Image();
        img.onload = function() {
            rcsMediaData.source = 'upload';
            rcsMediaData.file = file;
            rcsMediaData.url = e.target.result;
            rcsMediaData.dimensions = { width: img.width, height: img.height };
            rcsMediaData.fileSize = file.size;
            showRcsMediaPreview(e.target.result);
            updateRcsImageInfo();
        };
        img.src = e.target.result;
    };
    reader.readAsDataURL(file);
}

function showRcsMediaPreview(src) {
    document.getElementById('rcsMediaPreviewImg').src = src;
    document.getElementById('rcsMediaPreview').classList.remove('d-none');
    updateCarouselOrientationWarning();
    updateRcsWizardPreview();
}

function removeRcsMedia() {
    rcsMediaData = { source: null, url: null, file: null, dimensions: null, fileSize: 0, assetUuid: null, hostedUrl: null, originalUrl: null };
    document.getElementById('rcsMediaPreview').classList.add('d-none');
    document.getElementById('rcsMediaPreviewImg').src = '';
    document.getElementById('rcsMediaPreviewImg').style.transform = '';
    document.getElementById('rcsMediaPreviewImg').style.objectPosition = '';
    document.getElementById('rcsMediaUrlInput').value = '';
    document.getElementById('rcsMediaFileInput').value = '';
    document.getElementById('rcsZoomSlider').value = 100;
    document.getElementById('rcsZoomValue').textContent = '100%';
    updateRcsWizardPreview();
    document.getElementById('rcsImageDimensions').textContent = '--';
    document.getElementById('rcsImageFileSize').textContent = '--';
    document.getElementById('rcsOrientVertShort').checked = true;
    document.querySelectorAll('.rcs-crop-btn').forEach(function(btn) {
        btn.classList.remove('active');
        if (btn.dataset.position === 'center') btn.classList.add('active');
    });
    hideRcsMediaError();
}

function showRcsMediaError(message) {
    var errorEl = document.getElementById('rcsMediaError');
    document.getElementById('rcsMediaErrorText').textContent = message;
    errorEl.classList.remove('d-none');
    errorEl.classList.remove('alert-warning');
    errorEl.classList.add('alert-danger');
}

function hideRcsMediaError() {
    var errorEl = document.getElementById('rcsMediaError');
    errorEl.classList.add('d-none');
    errorEl.classList.remove('alert-warning');
    errorEl.classList.add('alert-danger');
}

function updateRcsImageInfo() {
    if (rcsMediaData.dimensions) {
        document.getElementById('rcsImageDimensions').textContent = 
            rcsMediaData.dimensions.width + ' x ' + rcsMediaData.dimensions.height + ' px';
    }
    if (rcsMediaData.fileSize > 0) {
        var sizeText = (rcsMediaData.fileSize / 1024).toFixed(1) + ' KB';
        if (rcsMediaData.hostedUrl) {
            sizeText += ' (QuickSMS hosted)';
        } else if (rcsMediaData.source === 'url') {
            sizeText += ' (from URL)';
        }
        document.getElementById('rcsImageFileSize').textContent = sizeText;
    } else if (rcsMediaData.source === 'url') {
        document.getElementById('rcsImageFileSize').textContent = 'External URL (size unknown)';
    } else {
        document.getElementById('rcsImageFileSize').textContent = '--';
    }
}

function updateRcsZoom(value) {
    document.getElementById('rcsZoomValue').textContent = value + '%';
    var img = document.getElementById('rcsMediaPreviewImg');
    img.style.transform = 'scale(' + (value / 100) + ')';
    
    markRcsImageDirty();
    
    if (rcsMediaData.source === 'url' && rcsMediaData.originalUrl && rcsMediaData.hostedUrl) {
        processAssetServerSide(!!rcsMediaData.assetUuid);
    }
}

function setRcsCropPosition(position) {
    document.querySelectorAll('.rcs-crop-btn').forEach(function(btn) {
        btn.classList.remove('active');
        if (btn.dataset.position === position) btn.classList.add('active');
    });
    var img = document.getElementById('rcsMediaPreviewImg');
    switch(position) {
        case 'top': img.style.objectPosition = 'center top'; break;
        case 'bottom': img.style.objectPosition = 'center bottom'; break;
        default: img.style.objectPosition = 'center center';
    }
    
    markRcsImageDirty();
    
    if (rcsMediaData.source === 'url' && rcsMediaData.originalUrl && rcsMediaData.hostedUrl) {
        processAssetServerSide(!!rcsMediaData.assetUuid);
    }
}

function updateCarouselOrientationWarning() {
    var isCarousel = document.getElementById('rcsTypeCarousel').checked;
    var horizWrapper = document.getElementById('rcsOrientHorizWrapper');
    var horizInput = document.getElementById('rcsOrientHoriz');
    var warning = document.getElementById('rcsCarouselOrientWarning');
    
    if (isCarousel) {
        horizWrapper.classList.add('opacity-50');
        horizInput.disabled = true;
        warning.classList.remove('d-none');
        if (horizInput.checked) {
            document.getElementById('rcsOrientVertShort').checked = true;
        }
    } else {
        horizWrapper.classList.remove('opacity-50');
        horizInput.disabled = false;
        warning.classList.add('d-none');
    }
}

var rcsActiveTextField = null;

function updateRcsDescriptionCount() {
    var input = document.getElementById('rcsDescription');
    var count = input.value.length;
    document.getElementById('rcsDescriptionCount').textContent = count;
    var warning = document.getElementById('rcsDescriptionWarning');
    warning.classList.toggle('d-none', count <= 120);
    updateRcsWizardPreview();
}

function updateRcsTextBodyCount() {
    var textarea = document.getElementById('rcsTextBody');
    var count = textarea.value.length;
    document.getElementById('rcsTextBodyCount').textContent = count;
    var warning = document.getElementById('rcsTextBodyWarning');
    warning.classList.toggle('d-none', count <= 2000);
    updateRcsWizardPreview();
}

function openRcsPlaceholderPicker(field) {
    rcsActiveTextField = field;
    var modal = new bootstrap.Modal(document.getElementById('personalisationModal'));
    modal.show();
}

function openRcsEmojiPicker(field) {
    rcsActiveTextField = field;
    var modal = new bootstrap.Modal(document.getElementById('emojiPickerModal'));
    modal.show();
}

function getRcsTextElement(field) {
    if (field === 'description') return document.getElementById('rcsDescription');
    if (field === 'textBody') return document.getElementById('rcsTextBody');
    if (field === 'rcsButtonLabel') return document.getElementById('rcsButtonLabel');
    if (field === 'rcsButtonPhone') return document.getElementById('rcsButtonPhone');
    if (field === 'rcsButtonEventTitle') return document.getElementById('rcsButtonEventTitle');
    if (field === 'rcsButtonEventDesc') return document.getElementById('rcsButtonEventDesc');
    return null;
}

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

function openRcsButtonFieldPlaceholder(fieldId) {
    rcsActiveTextField = fieldId;
    var modal = new bootstrap.Modal(document.getElementById('personalisationModal'));
    modal.show();
}

function openRcsButtonFieldEmoji(fieldId) {
    rcsActiveTextField = fieldId;
    var modal = new bootstrap.Modal(document.getElementById('emojiPickerModal'));
    modal.show();
}

function validateRcsPhoneNoEmoji() {
    var input = document.getElementById('rcsButtonPhone');
    var errorEl = document.getElementById('rcsButtonPhoneEmojiError');
    var emojiRegex = /[\u{1F600}-\\u{1F64F}]|[\\u{1F300}-\\u{1F5FF}]|[\u{1F680}-\u{1F6FF}]|[\u{1F1E0}-\u{1F1FF}]|[\u{2600}-\u{26FF}]|[\u{2700}-\u{27BF}]|[\\u{1F900}-\u{1F9FF}]|[\u{1FA00}-\\u{1FA6F}]|[\u{1FA70}-\u{1FAFF}]/gu;
    
    if (emojiRegex.test(input.value)) {
        input.value = input.value.replace(emojiRegex, '');
        errorEl.classList.remove('d-none');
        setTimeout(function() {
            errorEl.classList.add('d-none');
        }, 3000);
    }
}

var rcsButtons = [];
var rcsEditingButtonIndex = -1;

function addRcsButton() {
    if (rcsButtons.length >= rcsMaxButtons) return;
    rcsEditingButtonIndex = -1;
    resetRcsButtonForm();
    var modal = new bootstrap.Modal(document.getElementById('rcsButtonConfigModal'));
    modal.show();
}

function editRcsButton(index) {
    rcsEditingButtonIndex = index;
    var btn = rcsButtons[index];
    document.getElementById('rcsButtonLabel').value = btn.label;
    updateRcsButtonLabelCount();
    
    document.getElementById('rcsButtonType' + btn.type.charAt(0).toUpperCase() + btn.type.slice(1)).checked = true;
    toggleRcsButtonType();
    
    if (btn.type === 'url') {
        document.getElementById('rcsButtonUrl').value = btn.url || '';
    } else if (btn.type === 'phone') {
        document.getElementById('rcsButtonPhone').value = btn.phone || '';
    } else if (btn.type === 'calendar') {
        document.getElementById('rcsButtonEventTitle').value = btn.eventTitle || '';
        document.getElementById('rcsButtonEventStart').value = btn.eventStart || '';
        document.getElementById('rcsButtonEventEnd').value = btn.eventEnd || '';
        document.getElementById('rcsButtonEventDesc').value = btn.eventDesc || '';
    }
    
    var modal = new bootstrap.Modal(document.getElementById('rcsButtonConfigModal'));
    modal.show();
}

function deleteRcsButton(index) {
    rcsButtons.splice(index, 1);
    renderRcsButtons();
    updateRcsButtonsPreview();
}

function resetRcsButtonForm() {
    document.getElementById('rcsButtonLabel').value = '';
    document.getElementById('rcsButtonLabelCount').textContent = '0';
    document.getElementById('rcsButtonTypeUrl').checked = true;
    toggleRcsButtonType();
    document.getElementById('rcsButtonUrl').value = '';
    document.getElementById('rcsButtonPhone').value = '';
    document.getElementById('rcsButtonEventTitle').value = '';
    document.getElementById('rcsButtonEventStart').value = '';
    document.getElementById('rcsButtonEventEnd').value = '';
    document.getElementById('rcsButtonEventDesc').value = '';
    hideAllRcsButtonErrors();
}

function hideAllRcsButtonErrors() {
    ['rcsButtonLabelError', 'rcsButtonUrlError', 'rcsButtonPhoneError', 
     'rcsButtonEventTitleError', 'rcsButtonEventStartError', 'rcsButtonEventEndError'].forEach(function(id) {
        document.getElementById(id).classList.add('d-none');
    });
}

function toggleRcsButtonType() {
    var type = document.querySelector('input[name="rcsButtonType"]:checked').value;
    document.getElementById('rcsButtonUrlConfig').classList.toggle('d-none', type !== 'url');
    document.getElementById('rcsButtonPhoneConfig').classList.toggle('d-none', type !== 'phone');
    document.getElementById('rcsButtonCalendarConfig').classList.toggle('d-none', type !== 'calendar');
    hideAllRcsButtonErrors();
    
    if (type !== 'url') document.getElementById('rcsButtonUrl').value = '';
    if (type !== 'phone') document.getElementById('rcsButtonPhone').value = '';
    if (type !== 'calendar') {
        document.getElementById('rcsButtonEventTitle').value = '';
        document.getElementById('rcsButtonEventStart').value = '';
        document.getElementById('rcsButtonEventEnd').value = '';
        document.getElementById('rcsButtonEventDesc').value = '';
    }
}

function updateRcsButtonLabelCount() {
    var count = document.getElementById('rcsButtonLabel').value.length;
    document.getElementById('rcsButtonLabelCount').textContent = count;
}

function validateRcsButton() {
    hideAllRcsButtonErrors();
    var valid = true;
    var label = document.getElementById('rcsButtonLabel').value.trim();
    var type = document.querySelector('input[name="rcsButtonType"]:checked').value;
    
    if (!label) {
        document.getElementById('rcsButtonLabelError').classList.remove('d-none');
        valid = false;
    }
    
    if (type === 'url') {
        var url = document.getElementById('rcsButtonUrl').value.trim();
        var urlPattern = /^https?:\/\/.+/i;
        if (!url || !urlPattern.test(url)) {
            document.getElementById('rcsButtonUrlError').classList.remove('d-none');
            valid = false;
        }
    } else if (type === 'phone') {
        var phone = document.getElementById('rcsButtonPhone').value.trim();
        var phonePattern = /^\+?[0-9\s\-()]{7,20}$/;
        if (!phone || !phonePattern.test(phone)) {
            document.getElementById('rcsButtonPhoneError').classList.remove('d-none');
            valid = false;
        }
    } else if (type === 'calendar') {
        var eventTitle = document.getElementById('rcsButtonEventTitle').value.trim();
        var eventStart = document.getElementById('rcsButtonEventStart').value;
        var eventEnd = document.getElementById('rcsButtonEventEnd').value;
        
        if (!eventTitle) {
            document.getElementById('rcsButtonEventTitleError').classList.remove('d-none');
            valid = false;
        }
        if (!eventStart) {
            document.getElementById('rcsButtonEventStartError').classList.remove('d-none');
            valid = false;
        }
        if (!eventEnd) {
            document.getElementById('rcsButtonEventEndError').classList.remove('d-none');
            valid = false;
        }
    }
    
    return valid;
}

function saveRcsButton() {
    if (!validateRcsButton()) return;
    
    var label = document.getElementById('rcsButtonLabel').value.trim();
    var type = document.querySelector('input[name="rcsButtonType"]:checked').value;
    
    var buttonData = { label: label, type: type };
    
    if (type === 'url') {
        buttonData.url = document.getElementById('rcsButtonUrl').value.trim();
    } else if (type === 'phone') {
        buttonData.phone = document.getElementById('rcsButtonPhone').value.trim();
    } else if (type === 'calendar') {
        buttonData.eventTitle = document.getElementById('rcsButtonEventTitle').value.trim();
        buttonData.eventStart = document.getElementById('rcsButtonEventStart').value;
        buttonData.eventEnd = document.getElementById('rcsButtonEventEnd').value;
        buttonData.eventDesc = document.getElementById('rcsButtonEventDesc').value.trim();
    }
    
    if (rcsEditingButtonIndex >= 0) {
        rcsButtons[rcsEditingButtonIndex] = buttonData;
    } else {
        rcsButtons.push(buttonData);
    }
    
    bootstrap.Modal.getInstance(document.getElementById('rcsButtonConfigModal')).hide();
    renderRcsButtons();
    updateRcsButtonsPreview();
}

function renderRcsButtons() {
    var container = document.getElementById('rcsButtonsList');
    container.innerHTML = '';
    
    rcsButtons.forEach(function(btn, index) {
        var typeIcon = btn.type === 'url' ? 'fa-link' : btn.type === 'phone' ? 'fa-phone' : 'fa-calendar-plus';
        var typeLabel = btn.type === 'url' ? 'URL' : btn.type === 'phone' ? 'Call' : 'Calendar';
        
        var html = '<div class="d-flex align-items-center justify-content-between p-2 border rounded mb-2 bg-light">';
        html += '<div class="d-flex align-items-center">';
        html += '<span class="badge bg-secondary me-2"><i class="fas ' + typeIcon + '"></i></span>';
        html += '<span class="small fw-medium">' + escapeHtml(btn.label) + '</span>';
        html += '<span class="badge bg-light text-muted ms-2 small">' + typeLabel + '</span>';
        html += '</div>';
        html += '<div class="btn-group btn-group-sm">';
        html += '<button type="button" class="btn btn-outline-secondary" onclick="editRcsButton(' + index + ')"><i class="fas fa-edit"></i></button>';
        html += '<button type="button" class="btn btn-outline-danger" onclick="deleteRcsButton(' + index + ')"><i class="fas fa-trash"></i></button>';
        html += '</div>';
        html += '</div>';
        
        container.innerHTML += html;
    });
    
    document.getElementById('rcsButtonCount').textContent = rcsButtons.length + ' / ' + rcsMaxButtons;
    document.getElementById('rcsAddButtonBtn').disabled = rcsButtons.length >= rcsMaxButtons;
    updateRcsWizardPreview();
}

function escapeHtml(text) {
    var div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function updateRcsButtonsPreview() {
    var previewContainer = document.querySelector('.rcs-preview-buttons .d-grid');
    if (!previewContainer) return;
    
    previewContainer.innerHTML = '';
    
    if (rcsButtons.length === 0) {
        previewContainer.innerHTML = '<button class="btn btn-outline-primary btn-sm" disabled>Action Button 1</button>';
        previewContainer.innerHTML += '<button class="btn btn-outline-secondary btn-sm" disabled>Action Button 2</button>';
        return;
    }
    
    rcsButtons.forEach(function(btn, index) {
        var btnClass = index === 0 ? 'btn-outline-primary' : 'btn-outline-secondary';
        var icon = btn.type === 'url' ? 'fa-external-link-alt' : btn.type === 'phone' ? 'fa-phone' : 'fa-calendar-plus';
        previewContainer.innerHTML += '<button class="btn ' + btnClass + ' btn-sm" disabled><i class="fas ' + icon + ' me-1"></i>' + escapeHtml(btn.label) + '</button>';
    });
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

function showPreview(type) {
    document.getElementById('smsPreview').classList.toggle('d-none', type !== 'sms');
    document.getElementById('rcsPreview').classList.toggle('d-none', type !== 'rcs');
    document.getElementById('previewSMSBtn').classList.toggle('active', type === 'sms');
    document.getElementById('previewRCSBtn').classList.toggle('active', type === 'rcs');
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

function saveDraft() {
    alert('Draft saved! (TODO: API integration)');
    console.log('TODO: Save draft via POST /api/campaigns/draft');
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
        (recipientState && recipientState.upload && recipientState.upload.valid && recipientState.upload.valid.length > 0) ||
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
    console.log('Opt-out configuration:', optoutConfig);
    
    window.location.href = '{{ route("messages.confirm") }}';
}

function updateOptoutCount() {
    console.log('TODO: Calculate total excluded from selected opt-out lists');
}

var recipientState = {
    manual: { valid: [], invalid: [] },
    upload: { valid: [], invalid: [] },
    contactBook: { contacts: [], lists: [], dynamicLists: [], tags: [] },
    ukMode: true,
    convert07: true
};

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
    if (recipientState.upload.valid.length > 0 || recipientState.upload.invalid.length > 0) {
        console.log('TODO: Revalidate uploaded numbers');
    }
}

function triggerFileUpload() {
    document.getElementById('recipientFile').click();
}

function handleFileSelect() {
    var fileInput = document.getElementById('recipientFile');
    if (fileInput.files.length) {
        processFileUpload();
    }
}

function processFileUpload() {
    var fileInput = document.getElementById('recipientFile');
    if (!fileInput.files.length) return;
    
    var file = fileInput.files[0];
    var isExcel = file.name.endsWith('.xlsx') || file.name.endsWith('.xls');
    
    document.getElementById('uploadProgress').classList.remove('d-none');
    document.getElementById('uploadResult').classList.add('d-none');
    
    setTimeout(function() {
        document.getElementById('uploadProgressBar').style.width = '50%';
        document.getElementById('uploadStatus').textContent = 'Detecting columns...';
        
        if (isExcel) {
            document.getElementById('excelZeroWarning').classList.remove('d-none');
        }
        
        setTimeout(function() {
            document.getElementById('uploadProgressBar').style.width = '100%';
            var modal = new bootstrap.Modal(document.getElementById('columnMappingModal'));
            modal.show();
        }, 500);
    }, 500);
}

function confirmColumnMapping() {
    bootstrap.Modal.getInstance(document.getElementById('columnMappingModal')).hide();
    
    recipientState.upload.valid = ['+447700900111', '+447700900222', '+447700900333', '+447700900444', '+447700900555'];
    recipientState.upload.invalid = [
        { row: 6, original: 'invalid', reason: 'Not a valid number' },
        { row: 12, original: '123', reason: 'Too short' }
    ];
    
    document.getElementById('uploadProgress').classList.add('d-none');
    document.getElementById('uploadResult').classList.remove('d-none');
    document.getElementById('uploadValid').textContent = recipientState.upload.valid.length;
    document.getElementById('uploadInvalid').textContent = recipientState.upload.invalid.length;
    document.getElementById('uploadInvalidLink').classList.toggle('d-none', recipientState.upload.invalid.length === 0);
    
    updateRecipientSummary();
    console.log('TODO: API - Process file upload with column mapping');
}

function openContactBookModal() {
    var modal = new bootstrap.Modal(document.getElementById('contactBookModal'));
    modal.show();
    updateContactBookSummary();
}

function toggleContactFilters() {
    document.getElementById('cbContactFilters').classList.toggle('d-none');
}

function clearContactFilters() {
    document.getElementById('cbFilterTags').value = '';
    document.getElementById('cbFilterMobile').value = '';
    document.getElementById('cbFilterOptout').value = 'exclude';
}

function toggleAllContacts() {
    var checked = document.getElementById('cbSelectAllContacts').checked;
    document.querySelectorAll('.cb-contact').forEach(function(cb) {
        cb.checked = checked;
    });
    updateContactBookSummary();
}

function filterContacts() {
    console.log('TODO: Filter contacts based on search');
}

function sortContacts() {
    console.log('TODO: Sort contacts based on selection');
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
    var contacts = Array.from(document.querySelectorAll('.cb-contact:checked')).map(cb => cb.value);
    var lists = Array.from(document.querySelectorAll('.cb-list:checked')).map(cb => cb.value);
    var dynamic = Array.from(document.querySelectorAll('.cb-dynamic:checked')).map(cb => cb.value);
    var tags = Array.from(document.querySelectorAll('.cb-tag:checked')).map(cb => cb.value);
    
    recipientState.contactBook = { contacts: contacts, lists: lists, dynamicLists: dynamic, tags: tags };
    
    bootstrap.Modal.getInstance(document.getElementById('contactBookModal')).hide();
    renderContactBookChips();
    updateRecipientSummary();
    console.log('TODO: API - Resolve contact book selections to actual numbers');
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
    if (contacts) chipsHtml += '<span class="badge bg-primary me-1 mb-1">' + contacts + ' Contacts <button class="btn-close btn-close-white ms-1" style="font-size:8px;" onclick="removeContactBookItem(\'contacts\')"></button></span>';
    if (lists) chipsHtml += '<span class="badge bg-success me-1 mb-1">' + lists + ' Lists <button class="btn-close btn-close-white ms-1" style="font-size:8px;" onclick="removeContactBookItem(\'lists\')"></button></span>';
    if (dynamic) chipsHtml += '<span class="badge bg-info me-1 mb-1">' + dynamic + ' Dynamic Lists <button class="btn-close btn-close-white ms-1" style="font-size:8px;" onclick="removeContactBookItem(\'dynamicLists\')"></button></span>';
    if (tags) chipsHtml += '<span class="badge bg-warning me-1 mb-1">' + tags + ' Tags <button class="btn-close btn-close-white ms-1" style="font-size:8px;" onclick="removeContactBookItem(\'tags\')"></button></span>';
    
    document.getElementById('contactBookChips').innerHTML = chipsHtml;
    document.getElementById('contactBookSelection').classList.toggle('d-none', !chipsHtml);
}

function updateRecipientSummary() {
    var manualValid = recipientState.manual.valid.length;
    var uploadValid = recipientState.upload.valid.length;
    var contactBookCount = (recipientState.contactBook.contacts.length * 1) + 
                          (recipientState.contactBook.lists.length * 1234) + 
                          (recipientState.contactBook.dynamicLists.length * 2000) + 
                          (recipientState.contactBook.tags.length * 500);
    
    var totalValid = manualValid + uploadValid + contactBookCount;
    
    document.getElementById('recipientCount').textContent = totalValid;
    document.getElementById('previewRecipients').textContent = totalValid;
    
    updatePreviewCost();
}

function showInvalidNumbers(source) {
    var invalid = source === 'manual' ? recipientState.manual.invalid : recipientState.upload.invalid;
    showInvalidNumbersTable(invalid);
}

function showAllInvalidNumbers() {
    var all = recipientState.manual.invalid.concat(recipientState.upload.invalid);
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
    var all = recipientState.manual.invalid.concat(recipientState.upload.invalid);
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

function updatePreviewCost() {
    var recipients = parseInt(document.getElementById('validRecipients').textContent) || 0;
    var channel = document.querySelector('input[name="channel"]:checked').value;
    var costPerMsg = channel === 'sms' ? 0.035 : (channel === 'rcs_basic' ? 0.05 : 0.08);
    var parts = parseInt(document.getElementById('smsPartCount').textContent) || 1;
    var cost = recipients * parts * costPerMsg;
    document.getElementById('previewCost').textContent = cost.toFixed(2) + ' cr';
}
</script>
@endsection
