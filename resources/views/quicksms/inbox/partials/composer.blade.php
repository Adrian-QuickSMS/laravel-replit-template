{{-- Inbox: Reply composer — styled to match Send Message page --}}
<div class="inbox-composer d-none" id="replyComposer">

    {{-- Channel toggle (full-width, matching Send Message) --}}
    <div class="inbox-composer__channel-bar">
        <div class="btn-group w-100" role="group">
            <input type="radio" class="btn-check" name="replyChannel" id="channelSms" value="sms" checked>
            <label class="btn btn-outline-primary" for="channelSms"><i class="fas fa-sms me-1"></i>SMS</label>
            <input type="radio" class="btn-check" name="replyChannel" id="channelRcs" value="rcs">
            <label class="btn btn-outline-primary" for="channelRcs"><i class="fas fa-comment-dots me-1"></i>RCS</label>
        </div>
    </div>

    {{-- Sender info --}}
    <div class="inbox-composer__sender" id="composerSenderInfo"></div>

    {{-- RCS rich card summary (hidden until configured) --}}
    <div class="inbox-composer__rcs-summary d-none" id="rcsConfiguredSummary">
        <div class="d-flex align-items-center justify-content-between">
            <span class="text-muted"><i class="fas fa-image me-1"></i> Rich card configured</span>
            <button class="btn btn-sm btn-outline-danger" id="rcsClearBtn" title="Remove rich card">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>

    {{-- Template row + AI button (matching Send Message) --}}
    <div class="inbox-composer__template-row">
        <div class="d-flex align-items-center gap-2">
            <label class="form-label mb-0 text-nowrap text-muted" style="font-size: 0.8125rem;">Template</label>
            <button class="btn btn-sm btn-outline-secondary" id="btnTemplate" title="Templates">
                <i class="far fa-file-alt me-1"></i>Select
            </button>
        </div>
        <button type="button" class="btn btn-outline-primary btn-sm" id="btnAiAssist">
            <i class="fas fa-magic me-1"></i>Improve with AI
        </button>
    </div>

    {{-- Content label --}}
    <label class="form-label mb-2 inbox-composer__content-label" id="composerContentLabel">SMS Content</label>

    {{-- Bordered textarea container with floating tool buttons (matching Send Message) --}}
    <div class="position-relative border rounded inbox-composer__textarea-wrap">
        <textarea id="replyMessage"
                  class="form-control border-0 inbox-composer__textarea"
                  rows="3"
                  placeholder="Type your message here..."
                  maxlength="1600"
                  style="padding-bottom: 40px;"></textarea>
        <div class="position-absolute d-flex gap-2" style="bottom: 8px; right: 12px; z-index: 10;">
            <button type="button" class="btn btn-sm btn-light border" id="btnPersonalisation" title="Insert personalisation">
                <i class="fas fa-user-tag"></i>
            </button>
            <button type="button" class="btn btn-sm btn-light border" id="btnEmoji" title="Insert emoji">
                <i class="fas fa-smile"></i>
            </button>
            <button type="button" class="btn btn-sm btn-light border d-none" id="btnRcsWizard" title="RCS Rich Card">
                <i class="fas fa-palette"></i>
            </button>
        </div>
    </div>

    {{-- Character count meta (verbose format matching Send Message) --}}
    <div class="d-flex justify-content-between align-items-center mt-2 mb-2">
        <div class="inbox-composer__stats">
            <span class="text-muted me-3">Characters: <strong id="charCount">0</strong></span>
            <span class="text-muted me-3">Encoding: <strong id="encodingType">GSM-7</strong></span>
            <span class="text-muted" id="segmentDisplay">Segments: <strong id="smsPartCount">1</strong></span>
        </div>
        <span class="badge bg-warning text-dark d-none" id="unicodeWarning" data-bs-toggle="tooltip" title="This character causes the message to be sent using Unicode encoding.">
            <i class="fas fa-exclamation-triangle me-1"></i>Unicode
        </span>
    </div>

    {{-- Send button --}}
    <div class="d-flex justify-content-end">
        <button class="btn btn-primary inbox-composer__send" id="btnSendReply">
            <i class="fas fa-paper-plane me-1"></i>Send
        </button>
    </div>
</div>

{{-- Emoji picker is now handled by the shared QSEmojiPicker popover component --}}
@include('quicksms.partials.emoji-picker')

{{-- ═══ Personalisation Fields Modal (from v1 Send Message) ═══ --}}
<div class="modal fade" id="inboxPersonalisationModal" tabindex="-1" style="z-index: 1060;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header py-3">
                <h5 class="modal-title"><i class="fas fa-user-tag me-2"></i>Insert Personalisation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-3">Click a placeholder to insert it at the cursor position in your message.</p>
                <div class="mb-3">
                    <h6 class="text-muted mb-2">Contact Fields</h6>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-outline-primary btn-sm inbox-placeholder-btn" data-placeholder="first_name">@{{first_name}}</button>
                        <button type="button" class="btn btn-outline-primary btn-sm inbox-placeholder-btn" data-placeholder="last_name">@{{last_name}}</button>
                        <button type="button" class="btn btn-outline-primary btn-sm inbox-placeholder-btn" data-placeholder="full_name">@{{full_name}}</button>
                        <button type="button" class="btn btn-outline-primary btn-sm inbox-placeholder-btn" data-placeholder="mobile_number">@{{mobile_number}}</button>
                        <button type="button" class="btn btn-outline-primary btn-sm inbox-placeholder-btn" data-placeholder="email">@{{email}}</button>
                    </div>
                </div>
                <div class="mb-0">
                    <h6 class="text-muted mb-2">Custom Fields</h6>
                    <p class="text-muted small mb-0">Custom field placeholders from the contact's profile will appear here when available.</p>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- ═══ AI Content Assistant Modal (from v1 Send Message) ═══ --}}
<div class="modal fade" id="inboxAiAssistantModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header py-3">
                <h5 class="modal-title"><i class="fas fa-magic me-2"></i>AI Content Assistant</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-4">
                    <h6 class="mb-3">Current Message</h6>
                    <div class="p-3 rounded" id="inboxAiCurrentContent" style="background-color: #f0ebf8;">
                        <em class="text-muted">No content to improve</em>
                    </div>
                </div>
                <div class="mb-4">
                    <h6 class="mb-3">What would you like to do?</h6>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-outline-primary inbox-ai-action-btn" data-action="tone"><i class="fas fa-smile me-1"></i>Improve tone</button>
                        <button type="button" class="btn btn-outline-primary inbox-ai-action-btn" data-action="shorten"><i class="fas fa-compress-alt me-1"></i>Shorten message</button>
                        <button type="button" class="btn btn-outline-primary inbox-ai-action-btn" data-action="grammar"><i class="fas fa-spell-check me-1"></i>Correct spelling & grammar</button>
                        <button type="button" class="btn btn-outline-primary inbox-ai-action-btn" data-action="clarity"><i class="fas fa-lightbulb me-1"></i>Rephrase for clarity</button>
                    </div>
                </div>
                <div class="d-none" id="inboxAiResultSection">
                    <h6 class="mb-3">Suggested Version</h6>
                    <div class="bg-success bg-opacity-10 border border-success p-3 rounded mb-3" id="inboxAiSuggestedContent"></div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-success" id="inboxAiUseBtn"><i class="fas fa-check me-1"></i>Use this</button>
                        <button type="button" class="btn btn-outline-secondary" id="inboxAiDiscardBtn">Discard</button>
                    </div>
                </div>
                <div class="d-none" id="inboxAiLoadingSection">
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

{{-- ═══ RCS Content Wizard (from v1) ═══ --}}
@include('quicksms.partials.rcs-wizard-modal')
