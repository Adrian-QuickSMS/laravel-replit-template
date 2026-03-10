/**
 * Inbox v2 — Composer module
 * Handles reply text area, channel toggle, char counting, template insertion,
 * emoji picker, personalisation fields, AI assistant, and RCS wizard integration.
 */
var Composer = (function () {
    'use strict';

    var currentChannel = 'sms';
    var onSendCallback = null;
    var pendingRcsPayload = null;

    /* ── GSM-7 character set for accurate SMS counting ─── */
    var GSM7_BASIC = '@£$¥èéùìòÇ\nØø\rÅåΔ_ΦΓΛΩΠΨΣΘΞ ÆæßÉ' +
        ' !"#¤%&\'()*+,-./0123456789:;<=>?¡ABCDEFGHIJKLMNOPQRSTUVWXYZ' +
        'ÄÖÑÜabcdefghijklmnopqrstuvwxyz§äöñüà';
    var GSM7_EXT = '^{}\\[~]|€';

    function isGSM7(text) {
        for (var i = 0; i < text.length; i++) {
            if (GSM7_BASIC.indexOf(text[i]) === -1 && GSM7_EXT.indexOf(text[i]) === -1) {
                return false;
            }
        }
        return true;
    }

    function countSmsSegments(text) {
        if (!text) return { chars: 0, parts: 0, limit: 160, encoding: 'GSM-7' };

        var gsm = isGSM7(text);
        var len = 0;

        if (gsm) {
            for (var i = 0; i < text.length; i++) {
                len += GSM7_EXT.indexOf(text[i]) !== -1 ? 2 : 1;
            }
            var singleLimit = 160;
            var multiLimit = 153;
        } else {
            len = text.length;
            var singleLimit = 70;
            var multiLimit = 67;
        }

        var parts = len <= singleLimit ? 1 : Math.ceil(len / multiLimit);
        return {
            chars: len,
            parts: parts,
            limit: parts <= 1 ? singleLimit : multiLimit,
            encoding: gsm ? 'GSM-7' : 'UCS-2'
        };
    }

    /* ── Initialise ────────────────────────────────────── */
    function init(onSend) {
        onSendCallback = onSend;
        bindEvents();
        bindEmojiPicker();
        bindPersonalisationPicker();
        bindAiAssistant();
        bindRcsWizard();
    }

    function bindEvents() {
        // Channel toggle
        var radios = document.querySelectorAll('input[name="replyChannel"]');
        radios.forEach(function (r) {
            r.addEventListener('change', function () {
                setChannel(this.value);
            });
        });

        // Text input
        var textarea = document.getElementById('replyMessage');
        if (textarea) {
            textarea.addEventListener('input', updateCharCount);
            textarea.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    send();
                }
            });
        }

        // Send button
        var sendBtn = document.getElementById('btnSendReply');
        if (sendBtn) {
            sendBtn.addEventListener('click', function (e) {
                e.preventDefault();
                send();
            });
        }

        // Template button
        bindTool('btnTemplate', showTemplateSelector);

        // RCS clear
        var clearBtn = document.getElementById('rcsClearBtn');
        if (clearBtn) {
            clearBtn.addEventListener('click', clearRcsPayload);
        }
    }

    /* ── Emoji Picker ──────────────────────────────────── */
    function bindEmojiPicker() {
        var btn = document.getElementById('btnEmoji');
        var ta = document.getElementById('replyMessage');
        if (!btn || !ta) return;

        // Use the shared QSEmojiPicker popover component
        if (typeof QSEmojiPicker !== 'undefined') {
            window.inboxEmojiPicker = new QSEmojiPicker({
                triggerEl: btn,
                textareaEl: ta,
                onInsert: function () { updateCharCount(); }
            });
        }
    }

    /* ── Personalisation Fields ─────────────────────────── */
    function bindPersonalisationPicker() {
        var btn = document.getElementById('btnPersonalisation');
        if (!btn) return;

        btn.addEventListener('click', function () {
            var modal = document.getElementById('inboxPersonalisationModal');
            if (modal && typeof bootstrap !== 'undefined') {
                var bsModal = bootstrap.Modal.getOrCreateInstance(modal);
                bsModal.show();
            }
        });

        // Delegate placeholder button clicks
        var modalEl = document.getElementById('inboxPersonalisationModal');
        if (modalEl) {
            modalEl.addEventListener('click', function (e) {
                var btn = e.target.closest('.inbox-placeholder-btn');
                if (!btn) return;
                var field = btn.getAttribute('data-placeholder');
                if (field) {
                    insertText('{{' + field + '}}');
                    updateCharCount();
                }
            });
        }
    }

    /* ── AI Content Assistant ──────────────────────────── */
    function bindAiAssistant() {
        var btn = document.getElementById('btnAiAssist');
        if (!btn) return;

        btn.addEventListener('click', function () {
            var textarea = document.getElementById('replyMessage');
            var currentText = textarea ? textarea.value.trim() : '';

            // Populate current content display
            var contentEl = document.getElementById('inboxAiCurrentContent');
            if (contentEl) {
                contentEl.innerHTML = currentText
                    ? '<span>' + escapeHtml(currentText) + '</span>'
                    : '<em class="text-muted">No content to improve — type a message first</em>';
            }

            // Reset state
            var resultSection = document.getElementById('inboxAiResultSection');
            var loadingSection = document.getElementById('inboxAiLoadingSection');
            if (resultSection) resultSection.classList.add('d-none');
            if (loadingSection) loadingSection.classList.add('d-none');

            var modal = document.getElementById('inboxAiAssistantModal');
            if (modal && typeof bootstrap !== 'undefined') {
                var bsModal = bootstrap.Modal.getOrCreateInstance(modal);
                bsModal.show();
            }
        });

        // AI action buttons
        var modalEl = document.getElementById('inboxAiAssistantModal');
        if (modalEl) {
            modalEl.addEventListener('click', function (e) {
                var actionBtn = e.target.closest('.inbox-ai-action-btn');
                if (actionBtn) {
                    var action = actionBtn.getAttribute('data-action');
                    runAiImprove(action);
                    return;
                }

                if (e.target.closest('#inboxAiUseBtn')) {
                    useAiSuggestion();
                    return;
                }

                if (e.target.closest('#inboxAiDiscardBtn')) {
                    discardAiSuggestion();
                    return;
                }
            });
        }
    }

    function runAiImprove(action) {
        var textarea = document.getElementById('replyMessage');
        var currentText = textarea ? textarea.value.trim() : '';
        if (!currentText) {
            InboxApp.comingSoon('Type a message first');
            return;
        }

        var loadingSection = document.getElementById('inboxAiLoadingSection');
        var resultSection = document.getElementById('inboxAiResultSection');
        if (loadingSection) loadingSection.classList.remove('d-none');
        if (resultSection) resultSection.classList.add('d-none');

        // Simulate AI processing (replace with real API call when backend is ready)
        setTimeout(function () {
            var improved = simulateAiImprove(currentText, action);
            var suggestedEl = document.getElementById('inboxAiSuggestedContent');
            if (suggestedEl) suggestedEl.textContent = improved;
            if (loadingSection) loadingSection.classList.add('d-none');
            if (resultSection) resultSection.classList.remove('d-none');
        }, 1200);
    }

    function simulateAiImprove(text, action) {
        switch (action) {
            case 'tone':
                return 'Hi there! ' + text.charAt(0).toLowerCase() + text.slice(1) + ' We appreciate your time! 😊';
            case 'shorten':
                var words = text.split(' ');
                return words.length > 6 ? words.slice(0, Math.ceil(words.length * 0.7)).join(' ') + '.' : text;
            case 'grammar':
                return text.charAt(0).toUpperCase() + text.slice(1).replace(/\s+/g, ' ').trim() +
                    (text.endsWith('.') || text.endsWith('!') || text.endsWith('?') ? '' : '.');
            case 'clarity':
                return text.replace(/\b(ASAP)\b/gi, 'as soon as possible')
                    .replace(/\b(FYI)\b/gi, 'for your information')
                    .replace(/\b(pls|plz)\b/gi, 'please');
            default:
                return text;
        }
    }

    function useAiSuggestion() {
        var suggestedEl = document.getElementById('inboxAiSuggestedContent');
        if (!suggestedEl) return;

        var textarea = document.getElementById('replyMessage');
        if (textarea) {
            textarea.value = suggestedEl.textContent;
            updateCharCount();
        }

        var modal = document.getElementById('inboxAiAssistantModal');
        if (modal && typeof bootstrap !== 'undefined') {
            bootstrap.Modal.getInstance(modal).hide();
        }
    }

    function discardAiSuggestion() {
        var resultSection = document.getElementById('inboxAiResultSection');
        if (resultSection) resultSection.classList.add('d-none');
    }

    /* ── RCS Content Wizard ────────────────────────────── */
    function bindRcsWizard() {
        var btn = document.getElementById('btnRcsWizard');
        if (!btn) return;

        btn.addEventListener('click', function () {
            // Use the v1 RCS wizard if available
            if (typeof openRcsWizard === 'function') {
                openRcsWizard();
            } else {
                // Fall back to opening the modal directly
                var modal = document.getElementById('rcsWizardModal');
                if (modal && typeof bootstrap !== 'undefined') {
                    var bsModal = bootstrap.Modal.getOrCreateInstance(modal);
                    bsModal.show();
                }
            }
        });

        // Listen for RCS wizard "Apply" to capture the payload
        var applyBtn = document.getElementById('rcsApplyContentBtn');
        if (applyBtn) {
            // Wrap the existing handler to also set payload in inbox composer
            var origClick = applyBtn.onclick;
            applyBtn.onclick = null;
            applyBtn.addEventListener('click', function () {
                // Call original handler if it exists
                if (typeof handleRcsApplyContent === 'function') {
                    handleRcsApplyContent();
                }

                // Capture payload from the wizard
                if (typeof getRcsPayloadForSubmission === 'function') {
                    var payload = getRcsPayloadForSubmission();
                    if (payload) {
                        setRcsPayload(payload);
                        // Auto-switch to RCS channel
                        setChannel('rcs');
                    }
                }

                // Close the wizard modal
                var modal = document.getElementById('rcsWizardModal');
                if (modal && typeof bootstrap !== 'undefined') {
                    var instance = bootstrap.Modal.getInstance(modal);
                    if (instance) instance.hide();
                }
            });
        }
    }

    function bindTool(id, handler) {
        var el = document.getElementById(id);
        if (el) el.addEventListener('click', handler);
    }

    /* ── Show / hide composer ──────────────────────────── */
    function show() {
        var el = document.getElementById('replyComposer');
        if (el) el.classList.remove('d-none');
    }

    function hide() {
        var el = document.getElementById('replyComposer');
        if (el) el.classList.add('d-none');
    }

    /* ── Channel switching ─────────────────────────────── */
    function setChannel(channel) {
        currentChannel = channel;

        // Update radio state
        var radio = document.getElementById(channel === 'rcs' ? 'channelRcs' : 'channelSms');
        if (radio) radio.checked = true;

        // Toggle RCS-specific UI
        var rcsWizardBtn = document.getElementById('btnRcsWizard');
        if (rcsWizardBtn) rcsWizardBtn.classList.toggle('d-none', channel !== 'rcs');

        updateCharCount();
    }

    function setChannelFromConversation(conv) {
        setChannel(conv.channel || 'sms');
        updateSenderInfo(conv);
    }

    function updateSenderInfo(conv) {
        var el = document.getElementById('composerSenderInfo');
        if (!el) return;

        var label = '';
        if (conv.source_type === 'shortcode') label = 'via ' + conv.source;
        else if (conv.source_type === 'vmn') label = 'via ' + conv.source;
        else if (conv.source_type === 'rcs_agent') label = 'via ' + conv.source;
        el.textContent = label;
    }

    /* ── Character counting (verbose format matching Send Message) ── */
    function updateCharCount() {
        var textarea = document.getElementById('replyMessage');
        var charCountEl = document.getElementById('charCount');
        var encodingEl = document.getElementById('encodingType');
        var segmentDisplay = document.getElementById('segmentDisplay');
        var unicodeWarning = document.getElementById('unicodeWarning');
        var contentLabel = document.getElementById('composerContentLabel');
        if (!textarea || !charCountEl) return;

        var text = textarea.value;

        if (currentChannel === 'rcs') {
            charCountEl.textContent = text.length;
            if (encodingEl) encodingEl.textContent = 'RCS';
            if (segmentDisplay) segmentDisplay.innerHTML = 'Limit: <strong>1,600</strong>';
            if (unicodeWarning) unicodeWarning.classList.add('d-none');
            if (contentLabel) contentLabel.textContent = 'RCS Content';
            return;
        }

        if (contentLabel) contentLabel.textContent = 'SMS Content';
        var info = countSmsSegments(text);
        charCountEl.textContent = info.chars;
        if (encodingEl) encodingEl.textContent = info.encoding === 'UCS-2' ? 'Unicode' : 'GSM-7';
        if (segmentDisplay) segmentDisplay.innerHTML = 'Segments: <strong id="smsPartCount">' + info.parts + '</strong>';
        if (unicodeWarning) unicodeWarning.classList.toggle('d-none', info.encoding !== 'UCS-2');
    }

    /* ── Send message ──────────────────────────────────── */
    function send() {
        var textarea = document.getElementById('replyMessage');
        if (!textarea) return;

        var text = textarea.value.trim();

        // Check for RCS rich card payload
        if (pendingRcsPayload && currentChannel === 'rcs') {
            if (onSendCallback) {
                onSendCallback({
                    channel: 'rcs',
                    message: text,
                    rcs_payload: pendingRcsPayload
                });
            }
            clearRcsPayload();
            textarea.value = '';
            updateCharCount();
            return;
        }

        if (!text) return;

        if (onSendCallback) {
            onSendCallback({
                channel: currentChannel,
                message: text,
                rcs_payload: null
            });
        }

        textarea.value = '';
        updateCharCount();
        textarea.focus();
    }

    /* ── RCS payload management ────────────────────────── */
    function setRcsPayload(payload) {
        pendingRcsPayload = payload;
        var summary = document.getElementById('rcsConfiguredSummary');
        if (summary) summary.classList.remove('d-none');
    }

    function clearRcsPayload() {
        pendingRcsPayload = null;
        var summary = document.getElementById('rcsConfiguredSummary');
        if (summary) summary.classList.add('d-none');
    }

    /* ── Template selector ─────────────────────────────── */
    function showTemplateSelector() {
        var templates = (window.__inbox || {}).templates || [];
        if (templates.length === 0) {
            InboxApp.comingSoon('No templates available');
            return;
        }

        // Filter templates compatible with current channel
        var compatible = templates.filter(function (t) {
            if (currentChannel === 'sms') return t.channel === 'SMS';
            return true; // RCS can use any template
        });

        if (compatible.length === 0) {
            InboxApp.comingSoon('No templates for ' + currentChannel.toUpperCase());
            return;
        }

        // Simple dropdown — in production, replace with a proper modal
        var names = compatible.map(function (t, i) { return (i + 1) + '. ' + t.name; });
        var choice = prompt('Select template:\n' + names.join('\n') + '\n\nEnter number:');
        if (!choice) return;

        var idx = parseInt(choice, 10) - 1;
        if (idx >= 0 && idx < compatible.length) {
            insertText(compatible[idx].content || '');
        }
    }

    /* ── Insert text at cursor ─────────────────────────── */
    function insertText(text) {
        var textarea = document.getElementById('replyMessage');
        if (!textarea) return;

        var start = textarea.selectionStart;
        var end = textarea.selectionEnd;
        var before = textarea.value.substring(0, start);
        var after = textarea.value.substring(end);

        textarea.value = before + text + after;
        textarea.selectionStart = textarea.selectionEnd = start + text.length;
        textarea.focus();
        updateCharCount();
    }

    function getChannel() {
        return currentChannel;
    }

    function getText() {
        var textarea = document.getElementById('replyMessage');
        return textarea ? textarea.value : '';
    }

    /* ── Helpers ────────────────────────────────────────── */
    function escapeHtml(str) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    /* ── Public API ────────────────────────────────────── */
    return {
        init: init,
        show: show,
        hide: hide,
        setChannel: setChannel,
        setChannelFromConversation: setChannelFromConversation,
        getChannel: getChannel,
        getText: getText,
        insertText: insertText,
        setRcsPayload: setRcsPayload,
        clearRcsPayload: clearRcsPayload,
        updateCharCount: updateCharCount
    };
})();
