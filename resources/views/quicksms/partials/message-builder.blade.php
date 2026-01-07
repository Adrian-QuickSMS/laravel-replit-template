{{--
    Shared Message Builder Partial
    
    Used by:
    - Send Message page (mode=campaign)
    - Template Creation wizard Step 2 (mode=template)
    - Inbox compose (mode=inbox)
    
    Required variables:
    - $mode: 'campaign', 'template', or 'inbox'
    - $sender_ids: array of sender IDs
    - $rcs_agents: array of RCS agents
    - $opt_out_lists: array of opt-out lists
    - $virtual_numbers: array of virtual numbers
    - $optout_domains: array of optout domains
    
    Optional variables:
    - $id_prefix: prefix for element IDs (default: empty, use 'tpl' for templates)
--}}

@php
    $mode = $mode ?? 'campaign';
    $prefix = $id_prefix ?? '';
    $isCampaign = $mode === 'campaign';
    $isTemplate = $mode === 'template';
    $isInbox = $mode === 'inbox';
@endphp

<div class="message-builder-layout">
    <div class="message-builder-left">
        @if($isCampaign)
        <div class="card mb-3">
            <div class="card-body p-4">
                <h6 class="mb-3">Campaign Details</h6>
                <input type="text" class="form-control" id="campaignName" placeholder="Campaign name (auto-generated if blank)" maxlength="100">
            </div>
        </div>
        @endif
        
        <div class="card mb-3">
            <div class="card-body p-4">
                <h6 class="mb-3">Channel & Sender</h6>
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="{{ $prefix }}channel" id="{{ $prefix }}channelSMS" value="sms" checked>
                            <label class="btn btn-outline-primary" for="{{ $prefix }}channelSMS"><i class="fas fa-sms me-1"></i>SMS only</label>
                            <input type="radio" class="btn-check" name="{{ $prefix }}channel" id="{{ $prefix }}channelRCSBasic" value="rcs_basic">
                            <label class="btn btn-outline-primary" for="{{ $prefix }}channelRCSBasic" data-bs-toggle="tooltip" title="Text-only RCS with SMS fallback"><i class="fas fa-comment-dots me-1"></i>Basic RCS</label>
                            <input type="radio" class="btn-check" name="{{ $prefix }}channel" id="{{ $prefix }}channelRCSRich" value="rcs_rich">
                            <label class="btn btn-outline-primary" for="{{ $prefix }}channelRCSRich" data-bs-toggle="tooltip" title="Rich cards, images & buttons with SMS fallback"><i class="fas fa-image me-1"></i>Rich RCS</label>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6" id="{{ $prefix }}senderIdSection">
                        <select class="form-select" id="{{ $prefix }}senderId" onchange="{{ $prefix }}updatePreview()">
                            <option value="">SMS Sender ID *</option>
                            @foreach($sender_ids as $sender)
                            <option value="{{ $sender['id'] }}">{{ $sender['name'] }} ({{ $sender['type'] }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 d-none" id="{{ $prefix }}rcsAgentSection">
                        <select class="form-select" id="{{ $prefix }}rcsAgent" onchange="{{ $prefix }}updatePreview()">
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
        
        @if($isCampaign)
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
        @endif
        
        <div class="card mb-3">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0">Content</h6>
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="{{ $prefix }}openAiAssistant()">
                        <i class="fas fa-magic me-1"></i>Improve with AI
                    </button>
                </div>
                
                <label class="form-label mb-2" id="{{ $prefix }}contentLabel">SMS Content</label>
                
                <div class="position-relative border rounded mb-2">
                    <textarea class="form-control border-0" id="{{ $prefix }}smsContent" rows="5" placeholder="Type your message here..." oninput="{{ $prefix }}handleContentChange()" style="padding-bottom: 40px;"></textarea>
                    <div class="position-absolute d-flex gap-2" style="bottom: 8px; right: 12px; z-index: 10;">
                        <button type="button" class="btn btn-sm btn-light border" onclick="{{ $prefix }}openPersonalisationModal()" title="Insert personalisation">
                            <i class="fas fa-user-tag"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-light border" id="{{ $prefix }}emojiPickerBtn" title="Insert emoji">
                            <i class="fas fa-smile"></i>
                        </button>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <span class="text-muted me-3">Characters: <strong id="{{ $prefix }}charCount">0</strong></span>
                        <span class="text-muted me-3">Encoding: <strong id="{{ $prefix }}encodingType">GSM-7</strong></span>
                        <span class="text-muted" id="{{ $prefix }}segmentDisplay">Segments: <strong id="{{ $prefix }}smsPartCount">1</strong></span>
                    </div>
                    <span class="badge bg-warning text-dark d-none" id="{{ $prefix }}unicodeWarning" data-bs-toggle="tooltip" title="This character causes the message to be sent using Unicode encoding.">
                        <i class="fas fa-exclamation-triangle me-1"></i>Unicode
                    </span>
                </div>
                
                <div class="d-none mb-2" id="{{ $prefix }}rcsTextHelper">
                    <div class="alert alert-info py-2 mb-0">
                        <i class="fas fa-info-circle me-1"></i>
                        <span id="{{ $prefix }}rcsHelperText">Messages over 160 characters will be automatically sent as a single RCS message where supported.</span>
                    </div>
                </div>
                
                <div class="border-top pt-3 mb-3">
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="{{ $prefix }}includeTrackableLink" onchange="{{ $prefix }}toggleTrackableLink()">
                                <label class="form-check-label" for="{{ $prefix }}includeTrackableLink">Include trackable link</label>
                            </div>
                        </div>
                        <div class="col-md-4 mb-2">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="{{ $prefix }}messageExpiry" onchange="{{ $prefix }}toggleMessageExpiry()">
                                <label class="form-check-label" for="{{ $prefix }}messageExpiry">Message expiry</label>
                            </div>
                        </div>
                        @if($isCampaign)
                        <div class="col-md-4 mb-2">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="scheduleRules" onchange="toggleScheduleRulesModal()">
                                <label class="form-check-label" for="scheduleRules">Schedule & sending rules</label>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                
                <div class="d-none mb-2" id="{{ $prefix }}trackableLinkSummary">
                    <div class="alert alert-secondary py-2 mb-0">
                        <i class="fas fa-link me-2"></i>Trackable link: <strong id="{{ $prefix }}trackableLinkDomain">qsms.uk</strong>
                        <a href="#" class="ms-2" onclick="{{ $prefix }}openTrackableLinkModal(); return false;">Edit</a>
                    </div>
                </div>
                
                <div class="d-none mb-2" id="{{ $prefix }}messageExpirySummary">
                    <div class="alert alert-secondary py-2 mb-0">
                        <i class="fas fa-hourglass-half me-2"></i>Message expiry: <strong id="{{ $prefix }}messageExpiryValue">24 Hours</strong>
                        <a href="#" class="ms-2" onclick="{{ $prefix }}openMessageExpiryModal(); return false;">Edit</a>
                    </div>
                </div>
                
                @if($isCampaign)
                <div class="d-none mb-2" id="scheduleSummary">
                    <div class="alert alert-secondary py-2 mb-0">
                        <i class="fas fa-clock me-2"></i><span id="scheduleSummaryText">Scheduled for: --</span>
                        <a href="#" class="ms-2" onclick="openScheduleRulesModal(); return false;">Edit</a>
                    </div>
                </div>
                @endif
                
                <div class="d-none mt-3" id="{{ $prefix }}rcsContentSection">
                    <div class="border rounded p-3 text-center" style="background-color: rgba(136, 108, 192, 0.1); border-color: rgba(136, 108, 192, 0.2) !important;">
                        <i class="fas fa-image fa-2x text-primary mb-2"></i>
                        <h6 class="mb-2">Rich RCS Card</h6>
                        <p class="text-muted small mb-3">Create rich media cards with images, descriptions, and interactive buttons.</p>
                        <button type="button" class="btn btn-primary" onclick="{{ $prefix }}openRcsWizard()">
                            <i class="fas fa-magic me-1"></i>Create RCS Message
                        </button>
                        <div class="d-none mt-3" id="{{ $prefix }}rcsConfiguredSummary">
                            <div class="alert alert-primary py-2 mb-0">
                                <i class="fas fa-check-circle me-1"></i>
                                <span id="{{ $prefix }}rcsConfiguredText">RCS content configured</span>
                                <a href="#" class="ms-2" onclick="{{ $prefix }}openRcsWizard(); return false;">Edit</a>
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
                        <input class="form-check-input" type="checkbox" id="{{ $prefix }}enableOptoutManagement" onchange="{{ $prefix }}toggleOptoutManagement()">
                        <label class="form-check-label" for="{{ $prefix }}enableOptoutManagement">Enable</label>
                    </div>
                </div>
                
                <div class="d-none" id="{{ $prefix }}optoutManagementSection">
                    <div class="mb-3">
                        <label class="form-label">Opt-out list <span class="text-muted">(optional)</span></label>
                        <select class="form-select" id="{{ $prefix }}optoutListSelect">
                            <option value="" selected>No list selected</option>
                            @foreach($opt_out_lists as $list)
                            <option value="{{ $list['id'] }}">{{ $list['name'] }} ({{ number_format($list['count']) }})</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Select a list to exclude numbers{{ $isCampaign ? '. If no list is selected, you must enable an opt-out method below.' : ' when this template is used.' }}</small>
                    </div>
                    
                    <div class="border-top pt-3 mb-3">
                        <h6 class="mb-3">Opt-out Options</h6>
                        
                        @if(count($virtual_numbers) > 0)
                        <div class="mb-3 p-3 border rounded">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" id="{{ $prefix }}enableReplyOptout" onchange="{{ $prefix }}toggleReplyOptout()">
                                <label class="form-check-label fw-medium" for="{{ $prefix }}enableReplyOptout">Enable reply-to-opt-out</label>
                            </div>
                            <div class="d-none ps-3" id="{{ $prefix }}replyOptoutConfig">
                                <div class="mb-2">
                                    <label class="form-label">Virtual Number</label>
                                    <select class="form-select form-select-sm" id="{{ $prefix }}replyVirtualNumber">
                                        <option value="">-- Select virtual number --</option>
                                        @foreach($virtual_numbers as $vn)
                                        <option value="{{ $vn['id'] }}" data-number="{{ $vn['number'] }}">{{ $vn['number'] }} ({{ $vn['label'] }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Opt-out Text</label>
                                    @if($isCampaign)
                                    <div class="input-group input-group-sm">
                                        <input type="text" class="form-control" id="{{ $prefix }}replyOptoutText" value="Opt-out: Reply STOP to @{{number}}" placeholder="e.g. Reply STOP to @{{number}}">
                                        <button type="button" class="btn btn-outline-primary" onclick="addOptoutToMessage('reply')" title="Append to message">Add to message content</button>
                                    </div>
                                    @else
                                    <input type="text" class="form-control form-control-sm" id="{{ $prefix }}replyOptoutText" value="Reply STOP to @{{number}}" placeholder="e.g. Reply STOP to @{{number}}">
                                    @endif
                                    <small class="text-muted">Use @{{number}} to insert the virtual number.</small>
                                </div>
                                @if($isCampaign)
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
                                @endif
                            </div>
                        </div>
                        @endif
                        
                        <div class="mb-3 p-3 border rounded">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" id="{{ $prefix }}enableUrlOptout" onchange="{{ $prefix }}toggleUrlOptout()">
                                <label class="form-check-label fw-medium" for="{{ $prefix }}enableUrlOptout">Enable click-to-opt-out</label>
                            </div>
                            <div class="d-none ps-3" id="{{ $prefix }}urlOptoutConfig">
                                <div class="mb-2">
                                    <label class="form-label">URL Domain</label>
                                    <select class="form-select form-select-sm" id="{{ $prefix }}urlOptoutDomain">
                                        @foreach($optout_domains as $domain)
                                        <option value="{{ $domain['id'] }}" {{ $domain['is_default'] ? 'selected' : '' }}>{{ $domain['domain'] }}{{ $domain['is_default'] ? ' (default)' : '' }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">A unique URL will be generated per message.</small>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Opt-out Text</label>
                                    @if($isCampaign)
                                    <div class="input-group input-group-sm">
                                        <input type="text" class="form-control" id="{{ $prefix }}urlOptoutText" value="Opt-out: Click @{{unique_url}}" placeholder="e.g. Click @{{unique_url}}">
                                        <button type="button" class="btn btn-outline-primary" onclick="addOptoutToMessage('url')" title="Append to message">Add to message content</button>
                                    </div>
                                    @else
                                    <input type="text" class="form-control form-control-sm" id="{{ $prefix }}urlOptoutText" value="Opt-out: Click @{{unique_url}}" placeholder="e.g. Click @{{unique_url}}">
                                    @endif
                                    <small class="text-muted">Use @{{unique_url}} to insert the tracking URL.</small>
                                </div>
                                @if($isCampaign)
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
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    @if($isCampaign)
                    <div class="d-none" id="optoutValidationError">
                        <div class="alert alert-danger py-2 mb-0">
                            <i class="fas fa-exclamation-circle me-1"></i>
                            <span id="optoutValidationMessage">At least one opt-out mechanism must be configured.</span>
                        </div>
                    </div>
                    @endif
                </div>
                
                <div id="{{ $prefix }}optoutDisabledMessage">
                    <p class="text-muted mb-0"><small>No opt-out logic will be applied. Enable to configure opt-out options.</small></p>
                </div>
            </div>
        </div>
        
        @if($isCampaign)
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
        @endif
    </div>
    
    <div class="message-builder-right">
        <div class="card mb-3">
            <div class="card-body p-4">
                <h6 class="mb-3">Message Preview</h6>
                <div id="{{ $prefix }}mainPreviewContainer" class="d-flex justify-content-center" style="transform: scale(0.85); transform-origin: top center; margin-bottom: -70px;"></div>
                
                <div class="text-center d-none" id="{{ $prefix }}previewToggleContainer">
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-sm py-0 px-3 active" id="{{ $prefix }}previewRCSBtn" onclick="{{ $prefix }}showPreview('rcs')" style="font-size: 11px; background: #886CC0; color: white; border: 1px solid #886CC0;">RCS</button>
                        <button type="button" class="btn btn-sm py-0 px-3" id="{{ $prefix }}previewSMSBtn" onclick="{{ $prefix }}showPreview('sms')" style="font-size: 11px; background: white; color: #886CC0; border: 1px solid #886CC0;">SMS</button>
                    </div>
                </div>
                
                <div class="text-center d-none" id="{{ $prefix }}basicRcsPreviewToggle">
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-sm py-0 px-3 active" id="{{ $prefix }}basicPreviewRCSBtn" onclick="{{ $prefix }}toggleBasicRcsPreview('rcs')" style="font-size: 11px; background: #886CC0; color: white; border: 1px solid #886CC0;">RCS</button>
                        <button type="button" class="btn btn-sm py-0 px-3" id="{{ $prefix }}basicPreviewSMSBtn" onclick="{{ $prefix }}toggleBasicRcsPreview('sms')" style="font-size: 11px; background: white; color: #886CC0; border: 1px solid #886CC0;">SMS</button>
                    </div>
                </div>
                
                <div class="mt-3 border-top pt-2">
                    <div class="row text-center">
                        <div class="{{ $isCampaign ? 'col-4' : 'col-6' }}"><small class="text-muted d-block mb-1">Channel</small><strong id="{{ $prefix }}previewChannel" class="small">SMS</strong></div>
                        @if($isCampaign)
                        <div class="col-4"><small class="text-muted d-block mb-1">Recipients</small><strong id="previewRecipients" class="small">0</strong></div>
                        <div class="col-4"><small class="text-muted d-block mb-1">Cost</small><strong id="previewCost" class="small">0 cr</strong></div>
                        @else
                        <div class="col-6"><small class="text-muted d-block mb-1">Segments</small><strong id="{{ $prefix }}previewSegments" class="small">1</strong></div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
