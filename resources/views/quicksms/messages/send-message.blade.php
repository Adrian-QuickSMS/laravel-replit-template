@extends('layouts.quicksms')

@section('title', 'Send Message')

@section('content')
<div class="container-fluid">
    <div class="row page-titles mb-0 py-1">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('messages') }}">Messages</a></li>
            <li class="breadcrumb-item active">Send Message</li>
        </ol>
    </div>
    
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body p-3">
                    <div class="mb-3 pb-2 border-bottom">
                        <h6 class="mb-2"><i class="fas fa-clipboard-list text-primary me-2"></i>1. Campaign Details</h6>
                        <div class="row">
                            <div class="col-12">
                                <input type="text" class="form-control form-control-sm" id="campaignName" placeholder="Campaign name (auto-generated if blank)" maxlength="100">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3 pb-2 border-bottom">
                        <h6 class="mb-2"><i class="fas fa-broadcast-tower text-primary me-2"></i>2. Channel & Sender</h6>
                        <div class="row mb-2">
                            <div class="col-12">
                                <div class="btn-group btn-group-sm w-100" role="group">
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
                                <select class="form-select form-select-sm" id="senderId" onchange="updatePreview()">
                                    <option value="">SMS Sender ID *</option>
                                    @foreach($sender_ids as $sender)
                                    <option value="{{ $sender['id'] }}">{{ $sender['name'] }} ({{ $sender['type'] }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 d-none" id="rcsAgentSection">
                                <select class="form-select form-select-sm" id="rcsAgent" onchange="updatePreview()">
                                    <option value="">RCS Agent *</option>
                                    @foreach($rcs_agents as $agent)
                                    <option value="{{ $agent['id'] }}">{{ $agent['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3 pb-2 border-bottom">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0"><i class="fas fa-users text-primary me-2"></i>3. Recipients</h6>
                            <span class="badge bg-primary" id="recipientCount">0</span>
                        </div>
                        <ul class="nav nav-pills nav-pills-sm mb-2" style="font-size: 11px;">
                            <li class="nav-item">
                                <button class="nav-link active py-1 px-2" data-bs-toggle="pill" data-bs-target="#manualEntry">Manual</button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link py-1 px-2" data-bs-toggle="pill" data-bs-target="#uploadFile">Upload</button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link py-1 px-2" data-bs-toggle="pill" data-bs-target="#contactBook">Contacts</button>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="manualEntry">
                                <textarea class="form-control form-control-sm" id="manualNumbers" rows="1" placeholder="+447700900123, +447700900456..."></textarea>
                            </div>
                            <div class="tab-pane fade" id="uploadFile">
                                <input type="file" class="form-control form-control-sm" id="recipientFile" accept=".csv,.xlsx,.xls">
                            </div>
                            <div class="tab-pane fade" id="contactBook">
                                <div class="row">
                                    <div class="col-6">
                                        <div class="border rounded p-1" style="max-height: 60px; overflow-y: auto; font-size: 11px;">
                                            @foreach($lists as $list)
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="{{ $list['id'] }}" id="list{{ $list['id'] }}">
                                                <label class="form-check-label" for="list{{ $list['id'] }}">{{ $list['name'] }} ({{ $list['count'] }})</label>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="border rounded p-1" style="max-height: 60px; overflow-y: auto; font-size: 11px;">
                                            @foreach($tags as $tag)
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="{{ $tag['id'] }}" id="tag{{ $tag['id'] }}">
                                                <label class="form-check-label" for="tag{{ $tag['id'] }}"><span style="color: {{ $tag['color'] }};">●</span> {{ $tag['name'] }}</label>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3 pb-2 border-bottom">
                        <h6 class="mb-2"><i class="fas fa-edit text-primary me-2"></i>4. Content</h6>
                        <div class="row mb-2">
                            <div class="col-8">
                                <select class="form-select form-select-sm" id="templateSelect" onchange="applyTemplate()">
                                    <option value="">Select template or start fresh...</option>
                                    @foreach($templates as $template)
                                    <option value="{{ $template['id'] }}" data-content="{{ $template['content'] }}">{{ $template['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-4">
                                <div class="btn-group btn-group-sm w-100">
                                    <button type="button" class="btn btn-outline-secondary" onclick="insertMergeField()"><i class="fas fa-code"></i></button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="insertTrackingUrl()"><i class="fas fa-link"></i></button>
                                </div>
                            </div>
                        </div>
                        <textarea class="form-control form-control-sm" id="smsContent" rows="2" placeholder="Type your message..." onkeyup="updatePreview(); updateCharCount();"></textarea>
                        <div class="d-flex justify-content-between" style="font-size: 10px;">
                            <span class="text-muted"><span id="charCount">0</span>/160 | <span id="smsPartCount">1</span> part(s)</span>
                            <div>
                                <span class="form-check form-check-inline" style="font-size: 10px;">
                                    <input class="form-check-input" type="radio" name="scheduling" id="sendNow" value="now" checked style="width: 12px; height: 12px;">
                                    <label class="form-check-label" for="sendNow">Now</label>
                                </span>
                                <span class="form-check form-check-inline" style="font-size: 10px;">
                                    <input class="form-check-input" type="radio" name="scheduling" id="sendLater" value="scheduled" style="width: 12px; height: 12px;">
                                    <label class="form-check-label" for="sendLater">Later</label>
                                </span>
                            </div>
                        </div>
                        <div class="d-none mt-1" id="schedulingOptions">
                            <input type="datetime-local" class="form-control form-control-sm" id="scheduledTime">
                        </div>
                        
                        <div class="d-none mt-2" id="rcsContentSection">
                            <div class="border rounded p-2 bg-light" style="font-size: 11px;">
                                <div class="row mb-1">
                                    <div class="col-6"><input type="text" class="form-control form-control-sm" id="rcsTitle" placeholder="Card title"></div>
                                    <div class="col-6"><input type="file" class="form-control form-control-sm" id="rcsImage" accept="image/*"></div>
                                </div>
                                <textarea class="form-control form-control-sm mb-1" id="rcsDescription" rows="1" placeholder="Description"></textarea>
                                <div id="rcsButtons">
                                    <div class="input-group input-group-sm mb-1">
                                        <input type="text" class="form-control" placeholder="Button">
                                        <input type="text" class="form-control" placeholder="URL">
                                        <button class="btn btn-outline-danger btn-sm" type="button"><i class="fas fa-times"></i></button>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-link btn-sm p-0" onclick="addRcsButton()" style="font-size: 10px;">+ Button</button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <h6 class="mb-0"><i class="fas fa-ban text-primary me-2"></i>5. Opt-outs</h6>
                            <span class="text-muted" style="font-size: 10px;"><span id="totalExcluded">2,847</span> excluded</span>
                        </div>
                        <div class="row" style="font-size: 11px;">
                            @foreach($opt_out_lists as $list)
                            <div class="col-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="{{ $list['id'] }}" id="optout{{ $list['id'] }}" {{ $list['id'] === 1 ? 'checked disabled' : 'checked' }}>
                                    <label class="form-check-label" for="optout{{ $list['id'] }}">{{ $list['name'] }} @if($list['id'] === 1)<span class="badge bg-secondary" style="font-size: 8px;">Req</span>@endif</label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="saveDraft()"><i class="fas fa-save me-1"></i>Draft</button>
                        <button type="button" class="btn btn-primary" onclick="continueToConfirmation()">Continue <i class="fas fa-arrow-right ms-1"></i></button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card sticky-top" style="top: 10px;">
                <div class="card-header bg-primary text-white py-2">
                    <h6 class="card-title mb-0"><i class="fas fa-mobile-alt me-2"></i>6. Preview</h6>
                </div>
                <div class="card-body p-2">
                    <div class="text-center mb-2">
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-outline-primary active btn-sm" id="previewSMSBtn" onclick="showPreview('sms')">SMS</button>
                            <button type="button" class="btn btn-outline-primary btn-sm" id="previewRCSBtn" onclick="showPreview('rcs')">RCS</button>
                        </div>
                    </div>
                    
                    <div class="phone-mockup mx-auto" style="max-width: 220px;">
                        <div class="bg-dark rounded-top p-1 text-center">
                            <small class="text-white" style="font-size: 9px;">Preview</small>
                        </div>
                        <div class="bg-light p-2" style="min-height: 200px; border-radius: 0 0 12px 12px;">
                            <div id="smsPreview">
                                <div class="text-center mb-1">
                                    <small class="text-muted" style="font-size: 9px;" id="previewSenderId">From: Select Sender</small>
                                </div>
                                <div class="bg-primary text-white p-2 rounded mb-1" style="max-width: 90%; margin-left: auto; font-size: 10px;">
                                    <p class="mb-0" id="previewMessage">Your message...</p>
                                </div>
                                <div class="text-end"><small class="text-muted" style="font-size: 8px;">Now</small></div>
                            </div>
                            
                            <div id="rcsPreview" class="d-none">
                                <div class="d-flex align-items-center mb-1">
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-1" style="width: 24px; height: 24px; font-size: 10px;" id="previewRcsLogo"><i class="fas fa-building"></i></div>
                                    <strong style="font-size: 10px;" id="previewRcsAgent">Agent</strong>
                                    <span class="badge bg-success ms-1" style="font-size: 7px;">✓</span>
                                </div>
                                <div class="card shadow-sm">
                                    <div class="bg-secondary" style="height: 80px;" id="previewRcsImageArea">
                                        <div class="d-flex align-items-center justify-content-center h-100 text-white"><i class="fas fa-image"></i></div>
                                    </div>
                                    <div class="card-body p-2">
                                        <h6 class="card-title mb-1" style="font-size: 11px;" id="previewRcsTitle">Card Title</h6>
                                        <p class="card-text mb-1" style="font-size: 9px;" id="previewRcsDescription">Description</p>
                                        <div id="previewRcsButtons">
                                            <button class="btn btn-outline-primary btn-sm w-100 mb-1 py-0" style="font-size: 9px;">Action</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-2 border-top pt-2" style="font-size: 10px;">
                        <div class="row text-center">
                            <div class="col-4"><small class="text-muted d-block">Channel</small><strong id="previewChannel">SMS</strong></div>
                            <div class="col-4"><small class="text-muted d-block">Recipients</small><strong id="previewRecipients">0</strong></div>
                            <div class="col-4"><small class="text-muted d-block">Cost</small><strong id="previewCost">0 cr</strong></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(e) { return new bootstrap.Tooltip(e); });
    
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
    
    if (channel === 'sms') {
        rcsAgentSection.classList.add('d-none');
        rcsContentSection.classList.add('d-none');
        previewChannel.textContent = 'SMS';
    } else if (channel === 'rcs_basic') {
        rcsAgentSection.classList.remove('d-none');
        rcsContentSection.classList.add('d-none');
        previewChannel.textContent = 'Basic RCS';
    } else if (channel === 'rcs_rich') {
        rcsAgentSection.classList.remove('d-none');
        rcsContentSection.classList.remove('d-none');
        previewChannel.textContent = 'Rich RCS';
    }
    updatePreview();
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

function updateCharCount() {
    var content = document.getElementById('smsContent').value;
    var charCount = content.length;
    var parts = Math.ceil(charCount / 160) || 1;
    
    document.getElementById('charCount').textContent = charCount;
    document.getElementById('smsPartCount').textContent = parts;
}

function applyTemplate() {
    var select = document.getElementById('templateSelect');
    var option = select.selectedOptions[0];
    if (option && option.dataset.content) {
        document.getElementById('smsContent').value = option.dataset.content;
        updatePreview();
        updateCharCount();
    }
}

function insertMergeField() {
    var fields = ['@{{first_name}}', '@{{last_name}}', '@{{mobile}}', '@{{email}}'];
    var field = prompt('Enter field name or choose:\n' + fields.join(', '));
    if (field) {
        var textarea = document.getElementById('smsContent');
        textarea.value += field;
        updatePreview();
        updateCharCount();
    }
}

function insertTrackingUrl() {
    var url = prompt('Enter URL to track:');
    if (url) {
        document.getElementById('smsContent').value += ' ' + url;
        updatePreview();
        updateCharCount();
    }
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
    
    alert('Proceeding to confirmation... (TODO: Navigate to confirmation screen)');
    console.log('TODO: Navigate to /messages/send/confirm with campaign data');
}

function updateOptoutCount() {
    console.log('TODO: Calculate total excluded from selected opt-out lists');
}
</script>
@endsection
