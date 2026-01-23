{{--
    Shared Message Composer Component
    
    Used by:
    - Messages > Send Message (mode: 'send')
    - Management > Templates > Create/Edit (mode: 'template')
    
    Required variables:
    - $composerMode: 'send' or 'template' (default: 'send')
    - $sender_ids: array of SMS sender IDs
    - $rcs_agents: array of RCS agents
    - $opt_out_lists: array of opt-out lists
    - $virtual_numbers: array of virtual numbers
    - $optout_domains: array of opt-out URL domains
--}}
@php
    $composerMode = $composerMode ?? 'send';
    $isTemplateMode = $composerMode === 'template';
@endphp

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

@if(!$isTemplateMode)
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
            <span class="badge" id="recipientCount" style="background-color: #f0ebf8; color: #6b5b95;">0</span>
        </div>
    </div>
</div>
@endif

<div class="card mb-3">
    <div class="card-body p-4">
        <h6 class="mb-3">Content</h6>
        
        <div class="row align-items-center mb-3">
            @if(!$isTemplateMode)
            <div class="col-md-6 col-lg-5 mb-2 mb-md-0">
                <div class="d-flex align-items-center gap-2">
                    <label class="form-label mb-0 text-nowrap">Template</label>
                    <select class="form-select form-select-sm" id="templateSelector" onchange="applySelectedTemplate()">
                        <option value="">-- None --</option>
                    </select>
                </div>
            </div>
            <div class="col-md-6 col-lg-7 text-md-end">
            @else
            <div class="col-12 text-end">
            @endif
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
                @if(!$isTemplateMode)
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="scheduleRules" onchange="toggleScheduleRulesModal()">
                    <label class="form-check-label" for="scheduleRules">Schedule & sending rules</label>
                </div>
                @endif
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
        
        @if(!$isTemplateMode)
        <div class="d-none mb-2" id="scheduleSummary">
            <div class="alert alert-secondary py-2 mb-0">
                <i class="fas fa-clock me-2"></i><span id="scheduleSummaryText">Scheduled for: --</span>
                <a href="#" class="ms-2" onclick="openScheduleRulesModal(); return false;">Edit</a>
            </div>
        </div>
        @endif
        
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
                                <input type="text" class="form-control" id="replyOptoutText" value="Opt-out: Reply STOP to @{{ '{' }}{{ '{' }}number}}" placeholder="e.g. Reply STOP to @{{ '{' }}{{ '{' }}number}}">
                                <button type="button" class="btn btn-outline-primary" onclick="addOptoutToMessage('reply')" title="Append to message">Add to message content</button>
                            </div>
                            <small class="text-muted">Use @{{ '{' }}{{ '{' }}number}} to insert the virtual number.</small>
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
                                <input type="text" class="form-control" id="urlOptoutText" value="Opt-out: Click @{{ '{' }}{{ '{' }}unique_url}}" placeholder="e.g. Click @{{ '{' }}{{ '{' }}unique_url}}">
                                <button type="button" class="btn btn-outline-primary" onclick="addOptoutToMessage('url')" title="Append to message">Add to message content</button>
                            </div>
                            <small class="text-muted">Use @{{ '{' }}{{ '{' }}unique_url}} to insert the tracking URL.</small>
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
