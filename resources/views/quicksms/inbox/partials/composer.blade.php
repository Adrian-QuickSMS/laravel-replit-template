{{-- Inbox v2: Reply composer — matches v1 card-based layout --}}
<div class="card border-top mb-0 d-none" id="replyComposer" style="border-radius: 0; flex-shrink: 0;">
    <div class="card-body p-2">
        <div class="mb-2">
            <label class="form-label small fw-bold mb-1">Channel & Sender</label>
            <div class="btn-group w-100 mb-2" role="group">
                <input type="radio" class="btn-check" name="replyChannel" id="channelSms" value="sms" checked>
                <label class="btn btn-outline-primary" for="channelSms"><i class="fas fa-sms me-1"></i>SMS only</label>
                <input type="radio" class="btn-check" name="replyChannel" id="channelRcsBasic" value="rcs_basic">
                <label class="btn btn-outline-primary" for="channelRcsBasic" data-bs-toggle="tooltip" title="Text-only RCS with SMS fallback"><i class="fas fa-comment-dots me-1"></i>Basic RCS</label>
                <input type="radio" class="btn-check" name="replyChannel" id="channelRcsRich" value="rcs_rich">
                <label class="btn btn-outline-primary" for="channelRcsRich" data-bs-toggle="tooltip" title="Rich cards, images & buttons with SMS fallback"><i class="fas fa-image me-1"></i>Rich RCS</label>
            </div>
            <div class="d-flex gap-2">
                <div class="flex-fill" id="inboxSenderIdSection">
                    <select class="form-select form-select-sm" id="inboxSenderSelect">
                        <option value="">Reply From (VMN) *</option>
                    </select>
                </div>
                <div class="flex-fill d-none" id="inboxRcsAgentSection">
                    <select class="form-select form-select-sm" id="inboxRcsAgentSelect">
                        <option value="">RCS Agent *</option>
                    </select>
                </div>
                <div class="flex-fill d-none" id="inboxSmsFallbackSection">
                    <select class="form-select form-select-sm" id="inboxSmsFallbackSelect">
                        <option value="">SMS Fallback (VMN) *</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2 mb-2">
            <select class="form-select form-select-sm flex-fill" id="inboxTemplateSelector">
                <option value="">Template: None</option>
            </select>
            <button type="button" class="btn btn-outline-primary btn-sm text-nowrap" id="btnAiAssist">
                <i class="fas fa-magic me-1"></i>Improve with AI
            </button>
        </div>

        <label class="form-label small mb-1" id="replyContentLabel">SMS Content</label>
        <div class="position-relative border rounded mb-2">
            <textarea class="form-control border-0" id="replyMessage" rows="3" placeholder="Type your message here..." style="padding-bottom: 40px; resize: none;"></textarea>
            <div class="position-absolute d-flex gap-2" style="bottom: 8px; right: 12px; z-index: 10;">
                <button type="button" class="btn btn-sm btn-light border" id="btnPersonalisation" title="Insert personalisation">
                    <i class="fas fa-user-tag"></i>
                </button>
                <button type="button" class="btn btn-sm btn-light border" id="btnEmoji" title="Insert emoji">
                    <i class="fas fa-smile"></i>
                </button>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-2">
            <div>
                <span class="text-muted small me-3">Characters: <strong id="charCount">0</strong></span>
                <span class="text-muted small me-3">Encoding: <strong id="encodingType">GSM-7</strong></span>
                <span class="text-muted small" id="segmentDisplay">Segments: <strong id="smsPartCount">1</strong></span>
            </div>
            <span class="badge bg-warning text-dark d-none" id="unicodeWarning" data-bs-toggle="tooltip" title="This character causes the message to be sent using Unicode encoding.">
                <i class="fas fa-exclamation-triangle me-1"></i>Unicode
            </span>
        </div>

        <div class="d-none" id="rcsRichContentSection">
            <div class="border rounded p-3 text-center mb-2" style="background: rgba(136, 108, 192, 0.1);">
                <i class="fas fa-image fa-2x mb-2" style="color: #886CC0;"></i>
                <h6 class="mb-2">Rich RCS Card</h6>
                <p class="text-muted small mb-2">Create rich media cards with images, descriptions, and interactive buttons.</p>
                <div id="rcsConfiguredSummary" class="d-none mb-2">
                    <div class="alert alert-success py-2 px-3 small mb-2">
                        <i class="fas fa-check-circle me-1"></i>
                        <span id="rcsConfiguredText">RCS content configured</span>
                    </div>
                </div>
                <div class="d-flex gap-2 justify-content-center">
                    <button type="button" class="btn btn-sm" style="background: #886CC0; color: white;" id="btnRcsWizard">
                        <i class="fas fa-magic me-1"></i><span id="rcsWizardBtnText">Create RCS Message</span>
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-sm d-none" id="rcsClearBtn">
                        <i class="fas fa-times me-1"></i>Clear
                    </button>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end">
            <button type="button" class="btn btn-primary" id="btnSendReply">
                <i class="far fa-paper-plane me-1"></i>Send Message
            </button>
        </div>
    </div>
</div>

{{-- ═══ Emoji Picker Modal ═══ --}}
<div class="modal fade" id="inboxEmojiPickerModal" tabindex="-1" style="z-index: 1060;">
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
                        <button type="button" class="btn btn-light btn-sm emoji-btn" data-emoji="😊">😊</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" data-emoji="👍">👍</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" data-emoji="❤️">❤️</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" data-emoji="🎉">🎉</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" data-emoji="✅">✅</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" data-emoji="⭐">⭐</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" data-emoji="📱">📱</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" data-emoji="📞">📞</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" data-emoji="📧">📧</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" data-emoji="📅">📅</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" data-emoji="⏰">⏰</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" data-emoji="💊">💊</button>
                    </div>
                </div>
                <div class="mb-3">
                    <h6 class="text-muted mb-2">Healthcare</h6>
                    <div class="d-flex flex-wrap gap-1">
                        <button type="button" class="btn btn-light btn-sm emoji-btn" data-emoji="🏥">🏥</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" data-emoji="👨‍⚕️">👨‍⚕️</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" data-emoji="👩‍⚕️">👩‍⚕️</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" data-emoji="💉">💉</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" data-emoji="🩺">🩺</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" data-emoji="🩹">🩹</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" data-emoji="💪">💪</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" data-emoji="🧘">🧘</button>
                    </div>
                </div>
                <div class="mb-0">
                    <h6 class="text-muted mb-2">Business</h6>
                    <div class="d-flex flex-wrap gap-1">
                        <button type="button" class="btn btn-light btn-sm emoji-btn" data-emoji="💼">💼</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" data-emoji="📊">📊</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" data-emoji="🔔">🔔</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" data-emoji="🎯">🎯</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" data-emoji="💡">💡</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" data-emoji="🚀">🚀</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" data-emoji="🔗">🔗</button>
                        <button type="button" class="btn btn-light btn-sm emoji-btn" data-emoji="📋">📋</button>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- ═══ Personalisation Fields Modal ═══ --}}
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

{{-- ═══ AI Content Assistant Modal ═══ --}}
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

{{-- ═══ RCS Content Wizard ═══ --}}
@include('quicksms.partials.rcs-wizard-modal')
