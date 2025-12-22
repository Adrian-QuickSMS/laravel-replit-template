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
            <div class="card mb-1">
                <div class="card-body p-2">
                    <h6 class="mb-1" style="font-size: 13px;"><i class="fas fa-clipboard-list text-primary me-1"></i>1. Campaign Details</h6>
                    <input type="text" class="form-control form-control-sm" id="campaignName" placeholder="Campaign name (auto-generated if blank)" maxlength="100">
                </div>
            </div>
            
            <div class="card mb-1">
                <div class="card-body p-2">
                    <h6 class="mb-1" style="font-size: 13px;"><i class="fas fa-broadcast-tower text-primary me-1"></i>2. Channel & Sender</h6>
                    <div class="row mb-1">
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
            </div>
            
            <div class="card mb-1">
                <div class="card-body p-2">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <h6 class="mb-0" style="font-size: 13px;"><i class="fas fa-users text-primary me-1"></i>3. Recipients <span class="badge bg-primary ms-1" id="recipientCount">0</span></h6>
                        <div class="form-check form-switch mb-0" style="font-size: 10px;">
                            <input class="form-check-input" type="checkbox" id="ukNumbersOnly" checked onchange="toggleUkMode()">
                            <label class="form-check-label" for="ukNumbersOnly">UK only</label>
                        </div>
                    </div>
                    <ul class="nav nav-pills nav-pills-sm mb-1" style="font-size: 11px;">
                        <li class="nav-item"><button class="nav-link active py-0 px-2" data-bs-toggle="pill" data-bs-target="#manualEntry">Manual</button></li>
                        <li class="nav-item"><button class="nav-link py-0 px-2" data-bs-toggle="pill" data-bs-target="#uploadFile">Upload</button></li>
                        <li class="nav-item"><button class="nav-link py-0 px-2" data-bs-toggle="pill" data-bs-target="#contactBook">Contact Book</button></li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="manualEntry">
                            <textarea class="form-control form-control-sm" id="manualNumbers" rows="2" placeholder="Paste or type numbers separated by commas, spaces, or new lines" onblur="validateManualNumbers()"></textarea>
                            <div class="d-none mt-1" id="manualValidation" style="font-size: 10px;">
                                <span class="text-success"><i class="fas fa-check-circle me-1"></i><span id="manualValid">0</span> valid</span>
                                <span class="text-danger ms-2"><i class="fas fa-times-circle me-1"></i><span id="manualInvalid">0</span> invalid</span>
                                <a href="#" class="ms-2 d-none" id="manualInvalidLink" onclick="showInvalidNumbers('manual')">View</a>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="uploadFile">
                            <div class="input-group input-group-sm">
                                <input type="file" class="form-control form-control-sm" id="recipientFile" accept=".csv,.xlsx,.xls" onchange="handleFileSelect()">
                                <button class="btn btn-outline-primary" type="button" id="uploadBtn" disabled onclick="processFileUpload()"><i class="fas fa-upload"></i></button>
                            </div>
                            <div class="d-none mt-1" id="uploadProgress" style="font-size: 10px;">
                                <div class="progress" style="height: 4px;"><div class="progress-bar" id="uploadProgressBar" style="width: 0%;"></div></div>
                                <span id="uploadStatus" class="text-muted">Processing...</span>
                            </div>
                            <div class="d-none mt-1" id="uploadResult" style="font-size: 10px;">
                                <span class="text-success"><i class="fas fa-check-circle me-1"></i><span id="uploadValid">0</span> valid</span>
                                <span class="text-danger ms-2"><i class="fas fa-times-circle me-1"></i><span id="uploadInvalid">0</span> invalid</span>
                                <a href="#" class="ms-2 d-none" id="uploadInvalidLink" onclick="showInvalidNumbers('upload')">View</a>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="contactBook">
                            <button type="button" class="btn btn-outline-primary btn-sm w-100" onclick="openContactBookModal()">
                                <i class="fas fa-address-book me-1"></i>Select from Contact Book
                            </button>
                            <div class="d-none mt-1" id="contactBookSelection" style="font-size: 10px;">
                                <div id="contactBookChips"></div>
                            </div>
                        </div>
                    </div>
                    <div class="border-top mt-2 pt-2" style="font-size: 11px;">
                        <div class="row text-center">
                            <div class="col-4">
                                <span class="text-primary fw-bold" id="totalRecipients">0</span>
                                <small class="text-muted d-block">Unique</small>
                            </div>
                            <div class="col-4">
                                <span class="text-success fw-bold" id="validRecipients">0</span>
                                <small class="text-muted d-block">Valid</small>
                            </div>
                            <div class="col-4">
                                <span class="text-danger fw-bold" id="invalidRecipients">0</span>
                                <small class="text-muted d-block"><a href="#" onclick="showAllInvalidNumbers()" id="invalidReviewLink" class="d-none">Review</a><span id="invalidLabel">Invalid</span></small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mb-1">
                <div class="card-body p-2">
                    <h6 class="mb-1" style="font-size: 13px;"><i class="fas fa-edit text-primary me-1"></i>4. Content</h6>
                    <div class="row mb-1">
                        <div class="col-8">
                            <select class="form-select form-select-sm" id="templateSelect" onchange="applyTemplate()">
                                <option value="">Select template...</option>
                                @foreach($templates as $template)
                                <option value="{{ $template['id'] }}" data-content="{{ $template['content'] }}">{{ $template['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-4">
                            <div class="btn-group btn-group-sm w-100">
                                <button type="button" class="btn btn-outline-secondary" onclick="insertMergeField()" title="Insert merge field"><i class="fas fa-code"></i></button>
                                <button type="button" class="btn btn-outline-secondary" onclick="insertTrackingUrl()" title="Insert tracking URL"><i class="fas fa-link"></i></button>
                            </div>
                        </div>
                    </div>
                    <textarea class="form-control form-control-sm" id="smsContent" rows="2" placeholder="Type your message..." onkeyup="updatePreview(); updateCharCount();"></textarea>
                    <div class="d-flex justify-content-between align-items-center" style="font-size: 10px;">
                        <span class="text-muted"><span id="charCount">0</span>/160 | <span id="smsPartCount">1</span> part(s)</span>
                        <div>
                            <span class="form-check form-check-inline mb-0"><input class="form-check-input" type="radio" name="scheduling" id="sendNow" value="now" checked style="margin-top: 0;"><label class="form-check-label" for="sendNow">Now</label></span>
                            <span class="form-check form-check-inline mb-0"><input class="form-check-input" type="radio" name="scheduling" id="sendLater" value="scheduled" style="margin-top: 0;"><label class="form-check-label" for="sendLater">Later</label></span>
                        </div>
                    </div>
                    <div class="d-none mt-1" id="schedulingOptions">
                        <input type="datetime-local" class="form-control form-control-sm" id="scheduledTime">
                    </div>
                    <div class="d-none mt-1" id="rcsContentSection">
                        <div class="border rounded p-1 bg-light" style="font-size: 10px;">
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
                            <button type="button" class="btn btn-link btn-sm p-0" onclick="addRcsButton()" style="font-size: 9px;">+ Button</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mb-1">
                <div class="card-body p-2">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <h6 class="mb-0" style="font-size: 13px;"><i class="fas fa-ban text-primary me-1"></i>5. Opt-outs</h6>
                        <span class="text-muted" style="font-size: 10px;"><span id="totalExcluded">2,847</span> excluded</span>
                    </div>
                    <div class="row" style="font-size: 11px;">
                        @foreach($opt_out_lists as $list)
                        <div class="col-6">
                            <div class="form-check"><input class="form-check-input" type="checkbox" value="{{ $list['id'] }}" id="optout{{ $list['id'] }}" {{ $list['id'] === 1 ? 'checked disabled' : 'checked' }}><label class="form-check-label" for="optout{{ $list['id'] }}">{{ $list['name'] }} @if($list['id'] === 1)<span class="badge bg-secondary" style="font-size: 8px;">Req</span>@endif</label></div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            
            <div class="card mb-1">
                <div class="card-body p-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="saveDraft()"><i class="fas fa-save me-1"></i>Draft</button>
                        <button type="button" class="btn btn-primary btn-sm" onclick="continueToConfirmation()">Continue <i class="fas fa-arrow-right ms-1"></i></button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card sticky-top" style="top: 20px;">
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
                    
                    <div class="phone-mockup mx-auto" style="max-width: 200px;">
                        <div class="bg-dark rounded-top p-1 text-center">
                            <small class="text-white" style="font-size: 9px;">Preview</small>
                        </div>
                        <div class="bg-light p-2" style="min-height: 180px; border-radius: 0 0 12px 12px;">
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
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-1" style="width: 20px; height: 20px; font-size: 9px;" id="previewRcsLogo"><i class="fas fa-building"></i></div>
                                    <strong style="font-size: 9px;" id="previewRcsAgent">Agent</strong>
                                    <span class="badge bg-success ms-1" style="font-size: 6px;">✓</span>
                                </div>
                                <div class="card shadow-sm">
                                    <div class="bg-secondary" style="height: 60px;" id="previewRcsImageArea">
                                        <div class="d-flex align-items-center justify-content-center h-100 text-white"><i class="fas fa-image"></i></div>
                                    </div>
                                    <div class="card-body p-1">
                                        <h6 class="card-title mb-0" style="font-size: 10px;" id="previewRcsTitle">Title</h6>
                                        <p class="card-text mb-1" style="font-size: 8px;" id="previewRcsDescription">Description</p>
                                        <button class="btn btn-outline-primary btn-sm w-100 py-0" style="font-size: 8px;">Action</button>
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

function handleFileSelect() {
    var fileInput = document.getElementById('recipientFile');
    var uploadBtn = document.getElementById('uploadBtn');
    uploadBtn.disabled = !fileInput.files.length;
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
    var totalInvalid = recipientState.manual.invalid.length + recipientState.upload.invalid.length;
    
    document.getElementById('totalRecipients').textContent = totalValid;
    document.getElementById('validRecipients').textContent = totalValid;
    document.getElementById('invalidRecipients').textContent = totalInvalid;
    document.getElementById('previewRecipients').textContent = totalValid;
    document.getElementById('recipientCount').textContent = totalValid;
    
    if (totalInvalid > 0) {
        document.getElementById('invalidReviewLink').classList.remove('d-none');
        document.getElementById('invalidLabel').classList.add('d-none');
    } else {
        document.getElementById('invalidReviewLink').classList.add('d-none');
        document.getElementById('invalidLabel').classList.remove('d-none');
    }
    
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
