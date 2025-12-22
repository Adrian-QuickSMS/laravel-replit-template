@extends('layouts.quicksms')

@section('title', 'Send Message')

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
                                <label class="btn btn-outline-info" for="channelRCSBasic" data-bs-toggle="tooltip" title="Text-only RCS with SMS fallback"><i class="fas fa-comment-dots me-1"></i>Basic RCS</label>
                                <input type="radio" class="btn-check" name="channel" id="channelRCSRich" value="rcs_rich">
                                <label class="btn btn-outline-success" for="channelRCSRich" data-bs-toggle="tooltip" title="Rich cards, images & buttons with SMS fallback"><i class="fas fa-image me-1"></i>Rich RCS</label>
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
                                <option value="{{ $agent['id'] }}">{{ $agent['name'] }}</option>
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
                        <button type="button" class="btn btn-light" onclick="triggerFileUpload()">
                            <i class="fas fa-upload me-1"></i>Upload CSV
                        </button>
                        <button type="button" class="btn btn-light" onclick="openContactBookModal()">
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
                        <div class="border rounded p-3 bg-light">
                            <h6 class="mb-3">Rich RCS Card Content</h6>
                            <div class="row mb-3">
                                <div class="col-6"><input type="text" class="form-control" id="rcsTitle" placeholder="Card title"></div>
                                <div class="col-6"><input type="file" class="form-control" id="rcsImage" accept="image/*"></div>
                            </div>
                            <textarea class="form-control mb-3" id="rcsDescription" rows="2" placeholder="Description"></textarea>
                            <div id="rcsButtons">
                                <div class="input-group mb-2">
                                    <input type="text" class="form-control" placeholder="Button text">
                                    <input type="text" class="form-control" placeholder="Button URL">
                                    <button class="btn btn-outline-danger" type="button" onclick="removeRcsButton(this)"><i class="fas fa-times"></i></button>
                                </div>
                            </div>
                            <button type="button" class="btn btn-link p-0" onclick="addRcsButton()">+ Add Button</button>
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
                            <small class="text-muted">Optionally exclude numbers from a list. Opt-out events are still captured even without a list selected.</small>
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
});

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

function addRcsButton() {
    var container = document.getElementById('rcsButtons');
    var row = document.createElement('div');
    row.className = 'input-group input-group-sm mb-1';
    row.innerHTML = '<input type="text" class="form-control" placeholder="Button"><input type="text" class="form-control" placeholder="URL"><button class="btn btn-outline-danger btn-sm" type="button" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>';
    container.appendChild(row);
}

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
});

function validateOptoutConfig() {
    var isEnabled = document.getElementById('enableOptoutManagement').checked;
    if (!isEnabled) {
        document.getElementById('optoutValidationError').classList.add('d-none');
        return true;
    }
    
    var replyEnabled = document.getElementById('enableReplyOptout') ? document.getElementById('enableReplyOptout').checked : false;
    var urlEnabled = document.getElementById('enableUrlOptout').checked;
    var errorDiv = document.getElementById('optoutValidationError');
    var errorMsg = document.getElementById('optoutValidationMessage');
    
    if (!replyEnabled && !urlEnabled) {
        errorMsg.textContent = 'Please select an opt-out method: Reply-based or Click-to-opt-out.';
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

function continueToConfirmation() {
    var campaignName = document.getElementById('campaignName').value;
    if (!campaignName) {
        var now = new Date();
        campaignName = 'Campaign - ' + now.toISOString().slice(0, 16).replace('T', ' ');
        document.getElementById('campaignName').value = campaignName;
    }
    
    var senderId = document.getElementById('senderId').value;
    var smsContent = document.getElementById('smsContent').value;
    
    if (!senderId) {
        alert('Please select a Sender ID');
        return;
    }
    if (!smsContent.trim()) {
        alert('Please enter a message');
        return;
    }
    
    if (!validateOptoutConfig()) {
        alert('Please fix opt-out configuration errors before continuing.');
        return;
    }
    
    var optoutConfig = getOptoutConfiguration();
    console.log('Opt-out configuration:', optoutConfig);
    
    alert('Proceeding to confirmation... (TODO: Navigate to confirmation screen)');
    console.log('TODO: Navigate to /messages/send/confirm with campaign data');
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
