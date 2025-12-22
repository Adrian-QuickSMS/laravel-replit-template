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
    
    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4" id="section-campaign">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-clipboard-list me-2"></i>1. Campaign Details</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Campaign Name</label>
                        <input type="text" class="form-control" id="campaignName" placeholder="e.g. Flu Clinic Reminder – October" maxlength="100" oninput="updateCampaignNameCount()">
                        <div class="d-flex justify-content-between mt-1">
                            <small class="text-muted">Used for reporting, audit, campaign history, and message exports</small>
                            <small class="text-muted"><span id="campaignNameCount">0</span>/100</small>
                        </div>
                        <small class="text-muted d-block mt-1"><i class="fas fa-info-circle me-1"></i>If left blank, a name will be auto-generated (e.g. Campaign - 2025-12-10 14:05)</small>
                    </div>
                </div>
            </div>
            
            <div class="card mb-4" id="section-channel">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-broadcast-tower me-2"></i>2. Channel & Sender</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Channel <span class="text-danger">*</span></label>
                        <div class="row">
                            <div class="col-md-4 mb-2">
                                <div class="form-check card p-3 border channel-option" onclick="selectChannel('sms')">
                                    <input class="form-check-input" type="radio" name="channel" id="channelSMS" value="sms" checked>
                                    <label class="form-check-label w-100" for="channelSMS">
                                        <strong><i class="fas fa-sms text-primary me-2"></i>SMS</strong>
                                        <small class="d-block text-muted">Standard text message</small>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <div class="form-check card p-3 border channel-option" onclick="selectChannel('rcs_basic')">
                                    <input class="form-check-input" type="radio" name="channel" id="channelRCSBasic" value="rcs_basic">
                                    <label class="form-check-label w-100" for="channelRCSBasic">
                                        <strong><i class="fas fa-comment-dots text-info me-2"></i>Basic RCS</strong>
                                        <small class="d-block text-muted">With SMS fallback</small>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <div class="form-check card p-3 border channel-option" onclick="selectChannel('rcs_rich')">
                                    <input class="form-check-input" type="radio" name="channel" id="channelRCSRich" value="rcs_rich">
                                    <label class="form-check-label w-100" for="channelRCSRich">
                                        <strong><i class="fas fa-image text-success me-2"></i>Rich RCS</strong>
                                        <small class="d-block text-muted">Cards, images, buttons + SMS fallback</small>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3" id="senderIdSection">
                        <label class="form-label fw-bold">SMS Sender ID <span class="text-danger">*</span></label>
                        <select class="form-select" id="senderId" onchange="updatePreview()">
                            <option value="">Select Sender ID</option>
                            @foreach($sender_ids as $sender)
                            <option value="{{ $sender['id'] }}">{{ $sender['name'] }} ({{ $sender['type'] }})</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" id="senderIdError"></div>
                    </div>
                    
                    <div class="mb-3 d-none" id="rcsAgentSection">
                        <label class="form-label fw-bold">RCS Agent <span class="text-danger">*</span></label>
                        <select class="form-select" id="rcsAgent" onchange="updatePreview()">
                            <option value="">Select RCS Agent</option>
                            @foreach($rcs_agents as $agent)
                            <option value="{{ $agent['id'] }}" data-logo="{{ $agent['logo'] }}">{{ $agent['name'] }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" id="rcsAgentError"></div>
                    </div>
                </div>
            </div>
            
            <div class="card mb-4" id="section-recipients">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><i class="fas fa-users me-2"></i>3. Recipients</h5>
                    <span class="badge bg-primary" id="recipientCount">0 recipients</span>
                </div>
                <div class="card-body">
                    <ul class="nav nav-pills mb-3" id="recipientMethodTab">
                        <li class="nav-item">
                            <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#manualEntry">Manual Entry</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#uploadFile">Upload File</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#contactBook">Contact Book</button>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="manualEntry">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Enter Mobile Numbers</label>
                                <textarea class="form-control" id="manualNumbers" rows="4" placeholder="Enter mobile numbers (one per line or comma-separated)&#10;e.g., +447700900123, +447700900456" onchange="validateRecipients()"></textarea>
                                <small class="text-muted">International format recommended (e.g., +447700900123)</small>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="uploadFile">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Upload CSV or Excel File</label>
                                <input type="file" class="form-control" id="recipientFile" accept=".csv,.xlsx,.xls" onchange="handleFileUpload()">
                                <small class="text-muted">Supported formats: CSV, Excel (.xlsx, .xls)</small>
                            </div>
                            <div class="alert alert-info d-none" id="fileUploadInfo">
                                <i class="fas fa-info-circle me-2"></i>
                                <span id="fileUploadStatus"></span>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="contactBook">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Select Lists</label>
                                    <div class="border rounded p-2" style="max-height: 150px; overflow-y: auto;">
                                        @foreach($lists as $list)
                                        <div class="form-check">
                                            <input class="form-check-input list-checkbox" type="checkbox" value="{{ $list['id'] }}" id="list{{ $list['id'] }}" onchange="updateRecipientCount()">
                                            <label class="form-check-label" for="list{{ $list['id'] }}">
                                                {{ $list['name'] }} <span class="badge bg-light text-dark">{{ number_format($list['count']) }}</span>
                                            </label>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Select Tags</label>
                                    <div class="border rounded p-2" style="max-height: 150px; overflow-y: auto;">
                                        @foreach($tags as $tag)
                                        <div class="form-check">
                                            <input class="form-check-input tag-checkbox" type="checkbox" value="{{ $tag['id'] }}" id="tag{{ $tag['id'] }}" onchange="updateRecipientCount()">
                                            <label class="form-check-label" for="tag{{ $tag['id'] }}">
                                                <span class="d-inline-block rounded-circle me-1" style="width: 10px; height: 10px; background-color: {{ $tag['color'] }};"></span>
                                                {{ $tag['name'] }} <span class="badge bg-light text-dark">{{ number_format($tag['count']) }}</span>
                                            </label>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-light border mt-3" id="recipientSummary">
                        <div class="row text-center">
                            <div class="col-3">
                                <h5 class="mb-0 text-primary" id="summaryTotal">0</h5>
                                <small class="text-muted">Total</small>
                            </div>
                            <div class="col-3">
                                <h5 class="mb-0 text-success" id="summaryValid">0</h5>
                                <small class="text-muted">Valid</small>
                            </div>
                            <div class="col-3">
                                <h5 class="mb-0 text-warning" id="summaryDuplicates">0</h5>
                                <small class="text-muted">Duplicates</small>
                            </div>
                            <div class="col-3">
                                <h5 class="mb-0 text-danger" id="summaryInvalid">0</h5>
                                <small class="text-muted">Invalid</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mb-4" id="section-content">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-edit me-2"></i>4. Content</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Template (Optional)</label>
                        <select class="form-select" id="templateSelect" onchange="applyTemplate()">
                            <option value="">Start from scratch...</option>
                            @foreach($templates as $template)
                            <option value="{{ $template['id'] }}" data-content="{{ $template['content'] }}">{{ $template['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">SMS Message <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="smsContent" rows="4" placeholder="Type your message here..." onkeyup="updatePreview(); updateCharCount();"></textarea>
                        <div class="d-flex justify-content-between mt-1">
                            <small class="text-muted">
                                <span id="charCount">0</span> / 160 characters | <span id="smsPartCount">1</span> SMS part(s)
                            </small>
                            <div>
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertMergeField()">
                                    <i class="fas fa-code me-1"></i> Insert Field
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertTrackingUrl()">
                                    <i class="fas fa-link me-1"></i> Tracking URL
                                </button>
                            </div>
                        </div>
                        <div class="invalid-feedback" id="smsContentError"></div>
                    </div>
                    
                    <div class="mb-3 d-none" id="rcsContentSection">
                        <label class="form-label fw-bold">RCS Rich Content</label>
                        <div class="border rounded p-3 bg-light">
                            <div class="mb-3">
                                <label class="form-label">Card Image (Optional)</label>
                                <input type="file" class="form-control form-control-sm" id="rcsImage" accept="image/*">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Card Title</label>
                                <input type="text" class="form-control form-control-sm" id="rcsTitle" placeholder="Enter card title" onkeyup="updatePreview()">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Card Description</label>
                                <textarea class="form-control form-control-sm" id="rcsDescription" rows="2" placeholder="Enter card description" onkeyup="updatePreview()"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Buttons</label>
                                <div id="rcsButtons">
                                    <div class="input-group mb-2">
                                        <input type="text" class="form-control form-control-sm" placeholder="Button text">
                                        <input type="text" class="form-control form-control-sm" placeholder="URL or action">
                                        <button class="btn btn-outline-danger btn-sm" type="button"><i class="fas fa-times"></i></button>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="addRcsButton()">
                                    <i class="fas fa-plus me-1"></i> Add Button
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Scheduling</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="scheduling" id="sendNow" value="now" checked onchange="toggleScheduling()">
                                <label class="form-check-label" for="sendNow">Send Immediately</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="scheduling" id="sendLater" value="scheduled" onchange="toggleScheduling()">
                                <label class="form-check-label" for="sendLater">Schedule for Later</label>
                            </div>
                            <div class="mt-2 d-none" id="schedulingOptions">
                                <input type="datetime-local" class="form-control form-control-sm" id="scheduledTime">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Sending Window</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="respectHours" checked>
                                <label class="form-check-label" for="respectHours">Respect unsociable hours (9PM - 8AM)</label>
                            </div>
                            <small class="text-muted">Messages will be queued if outside allowed hours</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mb-4" id="section-optout">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-ban me-2"></i>5. Opt-out Management</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning mb-3">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Compliance Notice:</strong> Contacts on selected opt-out lists will be automatically excluded from this campaign.
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Apply Opt-Out Lists</label>
                        @foreach($opt_out_lists as $list)
                        <div class="form-check">
                            <input class="form-check-input optout-checkbox" type="checkbox" value="{{ $list['id'] }}" id="optout{{ $list['id'] }}" {{ $list['id'] === 1 ? 'checked disabled' : '' }} onchange="updateOptoutCount()">
                            <label class="form-check-label" for="optout{{ $list['id'] }}">
                                {{ $list['name'] }} 
                                <span class="badge bg-danger">{{ number_format($list['count']) }} excluded</span>
                                @if($list['id'] === 1)
                                <span class="badge bg-secondary">Required</span>
                                @endif
                            </label>
                        </div>
                        @endforeach
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong id="totalExcluded">2,847</strong> contacts will be excluded based on selected opt-out lists.
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <button type="button" class="btn btn-outline-secondary" onclick="saveDraft()">
                            <i class="fas fa-save me-1"></i> Save Draft
                        </button>
                        <button type="button" class="btn btn-primary btn-lg" onclick="continueToConfirmation()">
                            Continue <i class="fas fa-arrow-right ms-1"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card sticky-top" style="top: 20px;" id="section-preview">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0"><i class="fas fa-mobile-alt me-2"></i>6. Message Preview</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-outline-primary active" id="previewSMSBtn" onclick="showPreview('sms')">SMS</button>
                            <button type="button" class="btn btn-outline-primary" id="previewRCSBtn" onclick="showPreview('rcs')">RCS</button>
                        </div>
                    </div>
                    
                    <div class="phone-mockup mx-auto" style="max-width: 280px;">
                        <div class="bg-dark rounded-top p-2 text-center">
                            <small class="text-white">Message Preview</small>
                        </div>
                        <div class="bg-light p-3" style="min-height: 350px; border-radius: 0 0 20px 20px;">
                            <div id="smsPreview">
                                <div class="text-center mb-2">
                                    <small class="text-muted" id="previewSenderId">Sender: Not selected</small>
                                </div>
                                <div class="bg-primary text-white p-3 rounded mb-2" style="max-width: 85%; margin-left: auto;">
                                    <p class="mb-0 small" id="previewMessage">Your message will appear here...</p>
                                </div>
                                <div class="text-end">
                                    <small class="text-muted">Now</small>
                                </div>
                            </div>
                            
                            <div id="rcsPreview" class="d-none">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;" id="previewRcsLogo">
                                        <i class="fas fa-building"></i>
                                    </div>
                                    <strong class="small" id="previewRcsAgent">RCS Agent</strong>
                                    <span class="badge bg-success ms-2" style="font-size: 8px;">Verified</span>
                                </div>
                                <div class="card shadow-sm">
                                    <div class="bg-secondary" style="height: 120px;" id="previewRcsImage">
                                        <div class="h-100 d-flex align-items-center justify-content-center text-white">
                                            <i class="fas fa-image fa-2x"></i>
                                        </div>
                                    </div>
                                    <div class="card-body p-2">
                                        <h6 class="card-title mb-1" id="previewRcsTitle">Card Title</h6>
                                        <p class="card-text small text-muted mb-2" id="previewRcsDescription">Card description will appear here...</p>
                                        <div id="previewRcsButtons">
                                            <button class="btn btn-outline-primary btn-sm w-100 mb-1">Button 1</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="small">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Channel:</span>
                            <strong id="previewChannel">SMS</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Recipients:</span>
                            <strong id="previewRecipients">0</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Excluded:</span>
                            <strong id="previewExcluded" class="text-danger">0</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Delivery:</span>
                            <strong id="previewDelivery">Immediate</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Est. Cost:</span>
                            <strong id="previewCost">0 credits</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// TODO: Replace with API data - local in-memory state for demonstration
var campaignState = {
    name: '',
    channel: 'sms',
    senderId: null,
    rcsAgent: null,
    recipients: [],
    smsContent: '',
    rcsContent: {},
    scheduling: 'now',
    scheduledTime: null,
    optOutLists: [1]
};

var templates = @json($templates);

document.addEventListener('DOMContentLoaded', function() {
    updatePreview();
    updateCharCount();
    updateCampaignNameCount();
});

function updateCampaignNameCount() {
    var input = document.getElementById('campaignName');
    var count = input.value.length;
    document.getElementById('campaignNameCount').textContent = count;
    
    if (count > 100) {
        document.getElementById('campaignNameCount').classList.add('text-danger');
    } else {
        document.getElementById('campaignNameCount').classList.remove('text-danger');
    }
}

function generateCampaignName() {
    var now = new Date();
    var year = now.getFullYear();
    var month = String(now.getMonth() + 1).padStart(2, '0');
    var day = String(now.getDate()).padStart(2, '0');
    var hours = String(now.getHours()).padStart(2, '0');
    var minutes = String(now.getMinutes()).padStart(2, '0');
    return 'Campaign - ' + year + '-' + month + '-' + day + ' ' + hours + ':' + minutes;
}

function selectChannel(channel) {
    campaignState.channel = channel;
    document.getElementById('channel' + (channel === 'sms' ? 'SMS' : channel === 'rcs_basic' ? 'RCSBasic' : 'RCSRich')).checked = true;
    
    // Show/hide RCS sections
    if (channel === 'rcs_basic' || channel === 'rcs_rich') {
        document.getElementById('rcsAgentSection').classList.remove('d-none');
    } else {
        document.getElementById('rcsAgentSection').classList.add('d-none');
    }
    
    if (channel === 'rcs_rich') {
        document.getElementById('rcsContentSection').classList.remove('d-none');
    } else {
        document.getElementById('rcsContentSection').classList.add('d-none');
    }
    
    updatePreview();
}

function applyTemplate() {
    // TODO: Connect to API - GET /api/templates/{id}
    var select = document.getElementById('templateSelect');
    var content = select.options[select.selectedIndex].dataset.content;
    if (content) {
        document.getElementById('smsContent').value = content;
        updatePreview();
        updateCharCount();
    }
}

function updateCharCount() {
    var content = document.getElementById('smsContent').value;
    var charCount = content.length;
    var parts = Math.ceil(charCount / 160) || 1;
    
    document.getElementById('charCount').textContent = charCount;
    document.getElementById('smsPartCount').textContent = parts;
    
    if (charCount > 160) {
        document.getElementById('charCount').classList.add('text-warning');
    } else {
        document.getElementById('charCount').classList.remove('text-warning');
    }
}

function updatePreview() {
    // Update sender
    var senderSelect = document.getElementById('senderId');
    var senderName = senderSelect.options[senderSelect.selectedIndex]?.text || 'Not selected';
    document.getElementById('previewSenderId').textContent = 'From: ' + senderName.split(' (')[0];
    
    // Update message content
    var content = document.getElementById('smsContent').value || 'Your message will appear here...';
    document.getElementById('previewMessage').textContent = content;
    
    // Update channel
    var channelNames = { sms: 'SMS', rcs_basic: 'Basic RCS', rcs_rich: 'Rich RCS' };
    document.getElementById('previewChannel').textContent = channelNames[campaignState.channel];
    
    // Update RCS preview
    if (campaignState.channel === 'rcs_rich') {
        var title = document.getElementById('rcsTitle').value || 'Card Title';
        var desc = document.getElementById('rcsDescription').value || 'Card description will appear here...';
        document.getElementById('previewRcsTitle').textContent = title;
        document.getElementById('previewRcsDescription').textContent = desc;
    }
    
    // Update RCS agent
    var agentSelect = document.getElementById('rcsAgent');
    if (agentSelect.value) {
        document.getElementById('previewRcsAgent').textContent = agentSelect.options[agentSelect.selectedIndex].text;
    }
    
    // Update recipients count
    updateRecipientCount();
}

function showPreview(type) {
    if (type === 'sms') {
        document.getElementById('smsPreview').classList.remove('d-none');
        document.getElementById('rcsPreview').classList.add('d-none');
        document.getElementById('previewSMSBtn').classList.add('active');
        document.getElementById('previewRCSBtn').classList.remove('active');
    } else {
        document.getElementById('smsPreview').classList.add('d-none');
        document.getElementById('rcsPreview').classList.remove('d-none');
        document.getElementById('previewSMSBtn').classList.remove('active');
        document.getElementById('previewRCSBtn').classList.add('active');
    }
}

function validateRecipients() {
    // TODO: Connect to API - POST /api/recipients/validate
    var input = document.getElementById('manualNumbers').value;
    var numbers = input.split(/[\n,]+/).map(n => n.trim()).filter(n => n);
    
    var valid = 0, invalid = 0, duplicates = 0;
    var seen = new Set();
    
    numbers.forEach(num => {
        if (seen.has(num)) {
            duplicates++;
        } else {
            seen.add(num);
            if (/^\+?[1-9]\d{6,14}$/.test(num.replace(/\s/g, ''))) {
                valid++;
            } else {
                invalid++;
            }
        }
    });
    
    document.getElementById('summaryTotal').textContent = numbers.length;
    document.getElementById('summaryValid').textContent = valid;
    document.getElementById('summaryDuplicates').textContent = duplicates;
    document.getElementById('summaryInvalid').textContent = invalid;
    document.getElementById('recipientCount').textContent = valid + ' recipients';
    document.getElementById('previewRecipients').textContent = valid;
    
    updateCostEstimate(valid);
}

function updateRecipientCount() {
    // TODO: Connect to API - POST /api/recipients/count
    var total = 0;
    
    document.querySelectorAll('.list-checkbox:checked').forEach(cb => {
        var label = cb.nextElementSibling;
        var count = parseInt(label.querySelector('.badge').textContent.replace(/,/g, ''));
        total += count;
    });
    
    document.querySelectorAll('.tag-checkbox:checked').forEach(cb => {
        var label = cb.nextElementSibling;
        var count = parseInt(label.querySelector('.badge').textContent.replace(/,/g, ''));
        total += count;
    });
    
    document.getElementById('summaryTotal').textContent = total;
    document.getElementById('summaryValid').textContent = total;
    document.getElementById('recipientCount').textContent = total + ' recipients';
    document.getElementById('previewRecipients').textContent = total;
    
    updateCostEstimate(total);
}

function handleFileUpload() {
    // TODO: Connect to API - POST /api/recipients/upload
    var file = document.getElementById('recipientFile').files[0];
    if (file) {
        document.getElementById('fileUploadInfo').classList.remove('d-none');
        document.getElementById('fileUploadStatus').textContent = 'Processing ' + file.name + '...';
        
        setTimeout(function() {
            var mockCount = Math.floor(Math.random() * 500) + 100;
            document.getElementById('fileUploadStatus').textContent = 'Found ' + mockCount + ' valid numbers in ' + file.name;
            document.getElementById('summaryTotal').textContent = mockCount + 10;
            document.getElementById('summaryValid').textContent = mockCount;
            document.getElementById('summaryDuplicates').textContent = '5';
            document.getElementById('summaryInvalid').textContent = '5';
            document.getElementById('recipientCount').textContent = mockCount + ' recipients';
            document.getElementById('previewRecipients').textContent = mockCount;
            updateCostEstimate(mockCount);
        }, 1000);
    }
}

function updateOptoutCount() {
    // TODO: Connect to API - POST /api/optouts/count
    var total = 0;
    document.querySelectorAll('.optout-checkbox:checked').forEach(cb => {
        var label = cb.nextElementSibling;
        var badge = label.querySelector('.badge.bg-danger');
        var count = parseInt(badge.textContent.replace(/[^0-9]/g, ''));
        total += count;
    });
    
    document.getElementById('totalExcluded').textContent = total.toLocaleString();
    document.getElementById('previewExcluded').textContent = total.toLocaleString();
}

function updateCostEstimate(recipients) {
    // TODO: Connect to API - POST /api/campaigns/estimate
    var creditsPerSms = campaignState.channel === 'sms' ? 1 : 2;
    var cost = recipients * creditsPerSms;
    document.getElementById('previewCost').textContent = cost.toLocaleString() + ' credits';
}

function toggleScheduling() {
    var scheduled = document.getElementById('sendLater').checked;
    document.getElementById('schedulingOptions').classList.toggle('d-none', !scheduled);
    document.getElementById('previewDelivery').textContent = scheduled ? 'Scheduled' : 'Immediate';
}

function insertMergeField() {
    // TODO: Show merge field picker modal
    var field = prompt('Enter merge field name (e.g., name, date):');
    if (field) {
        var textarea = document.getElementById('smsContent');
        var pos = textarea.selectionStart;
        var text = textarea.value;
        textarea.value = text.slice(0, pos) + '{' + field + '}' + text.slice(pos);
        updatePreview();
        updateCharCount();
    }
}

function insertTrackingUrl() {
    // TODO: Connect to API - POST /api/tracking-urls/create
    var textarea = document.getElementById('smsContent');
    var pos = textarea.selectionStart;
    var text = textarea.value;
    textarea.value = text.slice(0, pos) + '{tracking_url}' + text.slice(pos);
    updatePreview();
    updateCharCount();
}

function addRcsButton() {
    // TODO: Limit to max 4 buttons per RCS spec
    var container = document.getElementById('rcsButtons');
    var div = document.createElement('div');
    div.className = 'input-group mb-2';
    div.innerHTML = '<input type="text" class="form-control form-control-sm" placeholder="Button text"><input type="text" class="form-control form-control-sm" placeholder="URL or action"><button class="btn btn-outline-danger btn-sm" type="button" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>';
    container.appendChild(div);
}

function saveDraft() {
    // TODO: Connect to API - POST /api/campaigns/draft
    alert('Campaign draft would be saved. TODO: Connect to backend API.');
}

function continueToConfirmation() {
    // TODO: Validate all fields and navigate to confirmation screen
    var valid = true;
    
    // Handle campaign name - auto-generate if blank
    var campaignNameInput = document.getElementById('campaignName');
    var campaignName = campaignNameInput.value.trim();
    if (!campaignName) {
        campaignName = generateCampaignName();
        campaignNameInput.value = campaignName;
        updateCampaignNameCount();
    }
    campaignState.name = campaignName;
    
    // Validate sender ID
    if (!document.getElementById('senderId').value) {
        document.getElementById('senderId').classList.add('is-invalid');
        document.getElementById('senderIdError').textContent = 'Please select a Sender ID';
        valid = false;
    } else {
        document.getElementById('senderId').classList.remove('is-invalid');
    }
    
    // Validate RCS agent if RCS channel
    if (campaignState.channel !== 'sms' && !document.getElementById('rcsAgent').value) {
        document.getElementById('rcsAgent').classList.add('is-invalid');
        document.getElementById('rcsAgentError').textContent = 'Please select an RCS Agent';
        valid = false;
    }
    
    // Validate message content
    if (!document.getElementById('smsContent').value.trim()) {
        document.getElementById('smsContent').classList.add('is-invalid');
        document.getElementById('smsContentError').textContent = 'Message content is required';
        valid = false;
    } else {
        document.getElementById('smsContent').classList.remove('is-invalid');
    }
    
    if (valid) {
        alert('All validations passed! Would navigate to confirmation screen. TODO: Implement Screen 2.');
    } else {
        // Scroll to first error
        document.querySelector('.is-invalid').scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}

// Clear validation on input
document.getElementById('senderId').addEventListener('change', function() { this.classList.remove('is-invalid'); });
document.getElementById('smsContent').addEventListener('input', function() { this.classList.remove('is-invalid'); });
</script>

<style>
.channel-option {
    cursor: pointer;
    transition: all 0.2s;
}
.channel-option:hover {
    border-color: var(--primary) !important;
}
.channel-option:has(input:checked) {
    border-color: var(--primary) !important;
    background-color: rgba(108, 93, 211, 0.05);
}
.phone-mockup {
    border: 8px solid #333;
    border-radius: 25px;
    overflow: hidden;
}
</style>
@endsection
