/**
 * Inbox v2 — Composer module
 * Handles reply text area, 3-channel toggle, char counting, template insertion,
 * emoji picker, personalisation fields, AI assistant, and RCS wizard integration.
 */
var Composer = (function () {
    'use strict';

    var currentChannel = 'sms';
    var onSendCallback = null;
    var pendingRcsPayload = null;

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
        var singleLimit, multiLimit;

        if (gsm) {
            for (var i = 0; i < text.length; i++) {
                len += GSM7_EXT.indexOf(text[i]) !== -1 ? 2 : 1;
            }
            singleLimit = 160;
            multiLimit = 153;
        } else {
            len = text.length;
            singleLimit = 70;
            multiLimit = 67;
        }

        var parts = len <= singleLimit ? 1 : Math.ceil(len / multiLimit);
        return {
            chars: len,
            parts: parts,
            limit: parts <= 1 ? singleLimit : multiLimit,
            encoding: gsm ? 'GSM-7' : 'UCS-2'
        };
    }

    function init(onSend) {
        onSendCallback = onSend;
        bindChannelToggle();
        bindTextarea();
        bindSendButton();
        bindTemplatePicker();
        bindEmojiPicker();
        bindPersonalisationPicker();
        bindAiAssistant();
        bindRcsWizard();
        bindRcsClear();
        populateSenderDropdowns();
        rehydrateRcsPayload();
    }

    function rehydrateRcsPayload() {
        try {
            var stored = sessionStorage.getItem('quicksms_rcs_draft');
            if (stored) {
                var data = JSON.parse(stored);
                if (data && data.cards) {
                    pendingRcsPayload = data;
                    var summary = document.getElementById('rcsConfiguredSummary');
                    var clearBtn = document.getElementById('rcsClearBtn');
                    var wizardText = document.getElementById('rcsWizardBtnText');
                    if (summary) summary.classList.remove('d-none');
                    if (clearBtn) clearBtn.classList.remove('d-none');
                    if (wizardText) wizardText.textContent = 'Edit RCS Message';
                }
            }
        } catch (e) {}
    }

    function bindChannelToggle() {
        var radios = document.querySelectorAll('input[name="replyChannel"]');
        radios.forEach(function (r) {
            r.addEventListener('change', function () {
                setChannel(this.value);
            });
        });
    }

    function bindTextarea() {
        var textarea = document.getElementById('replyMessage');
        if (!textarea) return;
        textarea.addEventListener('input', updateCharCount);
        textarea.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                send();
            }
        });
    }

    function bindSendButton() {
        var sendBtn = document.getElementById('btnSendReply');
        if (sendBtn) {
            sendBtn.addEventListener('click', function (e) {
                e.preventDefault();
                send();
            });
        }
    }

    function bindRcsClear() {
        var clearBtn = document.getElementById('rcsClearBtn');
        if (clearBtn) {
            clearBtn.addEventListener('click', clearRcsPayload);
        }
    }

    function bindEmojiPicker() {
        var btn = document.getElementById('btnEmoji');
        var ta = document.getElementById('replyMessage');
        if (!btn || !ta) return;

        if (typeof QSEmojiPicker !== 'undefined') {
            window.inboxEmojiPicker = new QSEmojiPicker({
                triggerEl: btn,
                textareaEl: ta,
                onInsert: function () { updateCharCount(); }
            });
            window.smsEmojiPicker = window.inboxEmojiPicker;
        }
    }

    function bindPersonalisationPicker() {
        var btn = document.getElementById('btnPersonalisation');
        if (!btn) return;

        btn.addEventListener('click', function () {
            var modal = document.getElementById('inboxPersonalisationModal');
            if (modal && typeof bootstrap !== 'undefined') {
                bootstrap.Modal.getOrCreateInstance(modal).show();
            }
        });

        var modalEl = document.getElementById('inboxPersonalisationModal');
        if (modalEl) {
            modalEl.addEventListener('click', function (e) {
                var placeholderBtn = e.target.closest('.inbox-placeholder-btn');
                if (!placeholderBtn) return;
                var field = placeholderBtn.getAttribute('data-placeholder');
                if (field) {
                    insertText('{{' + field + '}}');
                    updateCharCount();
                }
            });
        }
    }

    function bindAiAssistant() {
        var btn = document.getElementById('btnAiAssist');
        if (!btn) return;

        btn.addEventListener('click', function () {
            var textarea = document.getElementById('replyMessage');
            var currentText = textarea ? textarea.value.trim() : '';

            var contentEl = document.getElementById('inboxAiCurrentContent');
            if (contentEl) {
                contentEl.innerHTML = currentText
                    ? '<span>' + escapeHtml(currentText) + '</span>'
                    : '<em class="text-muted">No content to improve — type a message first</em>';
            }

            var resultSection = document.getElementById('inboxAiResultSection');
            var loadingSection = document.getElementById('inboxAiLoadingSection');
            if (resultSection) resultSection.classList.add('d-none');
            if (loadingSection) loadingSection.classList.add('d-none');

            var modal = document.getElementById('inboxAiAssistantModal');
            if (modal && typeof bootstrap !== 'undefined') {
                bootstrap.Modal.getOrCreateInstance(modal).show();
            }
        });

        var modalEl = document.getElementById('inboxAiAssistantModal');
        if (modalEl) {
            modalEl.addEventListener('click', function (e) {
                var actionBtn = e.target.closest('.inbox-ai-action-btn');
                if (actionBtn) {
                    runAiImprove(actionBtn.getAttribute('data-action'));
                    return;
                }
                if (e.target.closest('#inboxAiUseBtn')) { useAiSuggestion(); return; }
                if (e.target.closest('#inboxAiDiscardBtn')) { discardAiSuggestion(); return; }
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

    function bindRcsWizard() {
        var btn = document.getElementById('btnRcsWizard');
        if (!btn) return;

        btn.addEventListener('click', function () {
            if (typeof openRcsWizard === 'function') {
                openRcsWizard();
            } else {
                var modal = document.getElementById('rcsWizardModal');
                if (modal && typeof bootstrap !== 'undefined') {
                    bootstrap.Modal.getOrCreateInstance(modal).show();
                }
            }
        });

        window.addEventListener('rcsContentApplied', function (e) {
            var payload = e.detail;
            if (payload && payload.cards) {
                setRcsPayload(payload);
                setChannel('rcs_rich');
            }
        });
    }

    function bindTemplatePicker() {
        var selector = document.getElementById('inboxTemplateSelector');
        if (selector) {
            selector.addEventListener('change', function () {
                applyTemplate(this.value);
            });
        }

        loadTemplates();
    }

    function loadTemplates() {
        var selector = document.getElementById('inboxTemplateSelector');
        if (!selector) return;

        var templates = (window.__inbox || {}).templates || [];

        while (selector.options.length > 1) {
            selector.remove(1);
        }

        templates.forEach(function (t) {
            var opt = document.createElement('option');
            opt.value = t.id || t.name;
            opt.textContent = t.name;
            opt.setAttribute('data-content', t.content || '');
            opt.setAttribute('data-channel', t.channel || 'SMS');
            if (t.rcs_payload) {
                opt.setAttribute('data-rcs-payload', JSON.stringify(t.rcs_payload));
            }
            if (t.sender_id) {
                opt.setAttribute('data-sender-id', t.sender_id);
            }
            if (t.rcs_agent_id) {
                opt.setAttribute('data-rcs-agent-id', t.rcs_agent_id);
            }
            selector.appendChild(opt);
        });
    }

    function applyTemplate(val) {
        if (!val) return;
        var selector = document.getElementById('inboxTemplateSelector');
        if (!selector) return;

        var selected = selector.options[selector.selectedIndex];
        if (!selected) return;

        var channel = selected.getAttribute('data-channel') || 'SMS';
        var rcsPayloadStr = selected.getAttribute('data-rcs-payload') || '';
        var content = selected.getAttribute('data-content') || '';
        var templateSenderId = selected.getAttribute('data-sender-id') || '';
        var templateRcsAgentId = selected.getAttribute('data-rcs-agent-id') || '';
        var textarea = document.getElementById('replyMessage');

        var senderSelect = document.getElementById('inboxSenderSelect');
        var fallbackSelect = document.getElementById('inboxSmsFallbackSelect');
        var rcsSelect = document.getElementById('inboxRcsAgentSelect');

        if (senderSelect && templateSenderId) {
            senderSelect.value = templateSenderId;
        }
        if (fallbackSelect && templateSenderId) {
            fallbackSelect.value = templateSenderId;
        }
        if (rcsSelect && templateRcsAgentId) {
            rcsSelect.value = templateRcsAgentId;
        }

        if (rcsPayloadStr) {
            try {
                var rcsData = JSON.parse(rcsPayloadStr);
                pendingRcsPayload = rcsData;
                setRcsPayload(rcsData);
                sessionStorage.setItem('quicksms_rcs_draft', JSON.stringify(rcsData));
                if (typeof loadRcsPayloadIntoWizard === 'function') {
                    loadRcsPayloadIntoWizard(rcsData);
                } else if (typeof resetRcsWizard === 'function') {
                    resetRcsWizard();
                    loadRcsFromStorage();
                }
            } catch (e) {
                pendingRcsPayload = null;
            }

            if (channel.indexOf('Rich') !== -1) {
                setChannel('rcs_rich');
            } else {
                setChannel('rcs_basic');
            }

            if (textarea) {
                var rcsText = '';
                if (rcsData && rcsData.text) {
                    rcsText = rcsData.text;
                } else if (rcsData && rcsData.cards && rcsData.cards[0]) {
                    rcsText = rcsData.cards[0].description || rcsData.cards[0].title || '';
                } else if (rcsData && rcsData.description) {
                    rcsText = rcsData.description;
                } else if (rcsData && rcsData.title) {
                    rcsText = rcsData.title;
                }
                textarea.value = rcsText || content;
                updateCharCount();
            }
        } else {
            if (channel.indexOf('RCS') !== -1 || channel.indexOf('rcs') !== -1) {
                setChannel('rcs_basic');
            } else {
                setChannel('sms');
            }

            if (textarea && content) {
                textarea.value = content;
                updateCharCount();
                textarea.focus();
            }
        }
    }

    function populateSenderDropdowns() {
        var data = window.__inbox || {};

        var senderSelect = document.getElementById('inboxSenderSelect');
        var senderList = data.sender_ids || data.senderIds || [];
        if (senderSelect && senderList.length) {
            senderList.forEach(function (s) {
                var opt = document.createElement('option');
                opt.value = typeof s === 'string' ? s : (s.value || s.id || s);
                var label = typeof s === 'string' ? s : (s.label || s.name || s.value || s);
                var sType = (s && s.type) ? s.type : '';
                if (sType === 'alphanumeric' || sType === 'numeric') {
                    label = label + ' (Alpha)';
                }
                opt.textContent = label;
                opt.setAttribute('data-sender-type', sType);
                senderSelect.appendChild(opt);
            });
        }

        var rcsSelect = document.getElementById('inboxRcsAgentSelect');
        var rcsList = data.rcs_agents || data.rcsAgents || [];
        if (rcsSelect && rcsList.length) {
            rcsList.forEach(function (a) {
                var opt = document.createElement('option');
                opt.value = typeof a === 'string' ? a : (a.value || a.id || a);
                var name = typeof a === 'string' ? a : (a.label || a.name || a.value || a);
                opt.textContent = name;
                if (a && typeof a === 'object') {
                    opt.setAttribute('data-name', a.name || name);
                    opt.setAttribute('data-tagline', a.tagline || '');
                    opt.setAttribute('data-logo', a.logo || '');
                }
                rcsSelect.appendChild(opt);
            });
        }

        var fallbackSelect = document.getElementById('inboxSmsFallbackSelect');
        if (fallbackSelect && senderList.length) {
            senderList.forEach(function (s) {
                var sType = (s && s.type) ? s.type : '';
                if (sType === 'alphanumeric' || sType === 'numeric') return;
                var opt = document.createElement('option');
                opt.value = typeof s === 'string' ? s : (s.value || s.id || s);
                opt.textContent = typeof s === 'string' ? s : (s.label || s.name || s.value || s);
                fallbackSelect.appendChild(opt);
            });
        }
    }

    function show() {
        var el = document.getElementById('replyComposer');
        if (el) el.classList.remove('d-none');
    }

    function hide() {
        var el = document.getElementById('replyComposer');
        if (el) el.classList.add('d-none');
    }

    function setChannel(channel) {
        currentChannel = channel;

        var radioMap = {
            'sms': 'channelSms',
            'rcs_basic': 'channelRcsBasic',
            'rcs_rich': 'channelRcsRich',
            'rcs': 'channelRcsBasic'
        };
        var radioId = radioMap[channel] || 'channelSms';
        var radio = document.getElementById(radioId);
        if (radio) radio.checked = true;

        var isRcs = channel === 'rcs_basic' || channel === 'rcs_rich' || channel === 'rcs';
        var isRichRcs = channel === 'rcs_rich';

        var senderSection = document.getElementById('inboxSenderIdSection');
        var rcsAgentSection = document.getElementById('inboxRcsAgentSection');
        var smsFallbackSection = document.getElementById('inboxSmsFallbackSection');
        var rcsRichSection = document.getElementById('rcsRichContentSection');
        var textContentSection = document.getElementById('inboxTextContentSection');

        if (senderSection) senderSection.classList.toggle('d-none', isRcs);
        if (rcsAgentSection) rcsAgentSection.classList.toggle('d-none', !isRcs);
        if (smsFallbackSection) smsFallbackSection.classList.toggle('d-none', isRichRcs || !isRcs);
        if (rcsRichSection) rcsRichSection.classList.toggle('d-none', !isRichRcs);
        if (textContentSection) textContentSection.classList.toggle('d-none', isRichRcs);

        updateCharCount();
    }

    function setChannelFromConversation(conv) {
        var ch = conv.channel || 'sms';
        if (ch === 'rcs') ch = 'rcs_basic';
        setChannel(ch);
        updateSenderFromConversation(conv);
    }

    function updateSenderFromConversation(conv) {
        if (!conv) return;

        if (conv.rcs_agent_id) {
            var rcsSelect = document.getElementById('inboxRcsAgentSelect');
            if (rcsSelect) {
                for (var i = 0; i < rcsSelect.options.length; i++) {
                    if (rcsSelect.options[i].value === conv.rcs_agent_id) {
                        rcsSelect.selectedIndex = i;
                        break;
                    }
                }
            }
        }

        if (conv.purchased_number_id) {
            var senderSelect = document.getElementById('inboxSenderSelect');
            if (senderSelect) {
                for (var i = 0; i < senderSelect.options.length; i++) {
                    if (senderSelect.options[i].value === conv.purchased_number_id) {
                        senderSelect.selectedIndex = i;
                        break;
                    }
                }
            }
            var fallbackSelect = document.getElementById('inboxSmsFallbackSelect');
            if (fallbackSelect) {
                for (var i = 0; i < fallbackSelect.options.length; i++) {
                    if (fallbackSelect.options[i].value === conv.purchased_number_id) {
                        fallbackSelect.selectedIndex = i;
                        break;
                    }
                }
            }
        }
    }

    function updateCharCount() {
        var textarea = document.getElementById('replyMessage');
        var charCountEl = document.getElementById('charCount');
        var encodingEl = document.getElementById('encodingType');
        var segmentEl = document.getElementById('smsPartCount');
        var unicodeWarning = document.getElementById('unicodeWarning');

        if (!textarea) return;

        var text = textarea.value;

        if (currentChannel === 'rcs_basic' || currentChannel === 'rcs_rich' || currentChannel === 'rcs') {
            if (charCountEl) charCountEl.textContent = text.length;
            if (encodingEl) encodingEl.textContent = 'UTF-8';
            if (segmentEl) segmentEl.textContent = '1';
            if (unicodeWarning) unicodeWarning.classList.add('d-none');
            return;
        }

        var info = countSmsSegments(text);
        if (charCountEl) charCountEl.textContent = info.chars;
        if (encodingEl) encodingEl.textContent = info.encoding;
        if (segmentEl) segmentEl.textContent = info.parts;
        if (unicodeWarning) {
            unicodeWarning.classList.toggle('d-none', info.encoding !== 'UCS-2');
        }
    }

    function apiChannel() {
        if (currentChannel === 'rcs_basic' || currentChannel === 'rcs_rich' || currentChannel === 'rcs') return 'rcs';
        return 'sms';
    }

    function send() {
        var textarea = document.getElementById('replyMessage');
        if (!textarea) return;

        var text = textarea.value.trim();
        var isRcs = apiChannel() === 'rcs';

        if (pendingRcsPayload && isRcs) {
            if (onSendCallback) {
                onSendCallback({
                    channel: apiChannel(),
                    channel_detail: currentChannel,
                    message: text,
                    rcs_payload: pendingRcsPayload,
                    sender_id: getSenderId(),
                    rcs_agent: getRcsAgent(),
                    sms_fallback: getSmsFallback()
                });
            }
            clearRcsPayload();
            textarea.value = '';
            var fallbackEl = document.getElementById('rcsSmsFallbackText');
            if (fallbackEl) fallbackEl.value = '';
            updateCharCount();
            return;
        }

        if (!text) return;

        if (onSendCallback) {
            onSendCallback({
                channel: apiChannel(),
                channel_detail: currentChannel,
                message: text,
                rcs_payload: null,
                sender_id: getSenderId(),
                rcs_agent: isRcs ? getRcsAgent() : null,
                sms_fallback: isRcs ? getSmsFallback() : null
            });
        }

        textarea.value = '';
        updateCharCount();
        textarea.focus();

        var selector = document.getElementById('inboxTemplateSelector');
        if (selector) selector.selectedIndex = 0;
    }

    function getSenderId() {
        var el = document.getElementById('inboxSenderSelect');
        return el ? el.value : '';
    }

    function getRcsAgent() {
        var el = document.getElementById('inboxRcsAgentSelect');
        return el ? el.value : '';
    }

    function getSmsFallback() {
        if (currentChannel === 'rcs_rich') {
            var fallbackText = document.getElementById('rcsSmsFallbackText');
            return fallbackText ? fallbackText.value.trim() : '';
        }
        var el = document.getElementById('inboxSmsFallbackSelect');
        return el ? el.value : '';
    }

    function setRcsPayload(payload) {
        pendingRcsPayload = payload;
        var summary = document.getElementById('rcsConfiguredSummary');
        var clearBtn = document.getElementById('rcsClearBtn');
        var wizardText = document.getElementById('rcsWizardBtnText');
        if (summary) summary.classList.remove('d-none');
        if (clearBtn) clearBtn.classList.remove('d-none');
        if (wizardText) wizardText.textContent = 'Edit RCS Message';
    }

    function clearRcsPayload() {
        pendingRcsPayload = null;
        sessionStorage.removeItem('quicksms_rcs_draft');
        var summary = document.getElementById('rcsConfiguredSummary');
        var clearBtn = document.getElementById('rcsClearBtn');
        var wizardText = document.getElementById('rcsWizardBtnText');
        if (summary) summary.classList.add('d-none');
        if (clearBtn) clearBtn.classList.add('d-none');
        if (wizardText) wizardText.textContent = 'Create RCS Message';
    }

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
        return apiChannel();
    }

    function getChannelDetail() {
        return currentChannel;
    }

    function getText() {
        var textarea = document.getElementById('replyMessage');
        return textarea ? textarea.value : '';
    }

    function escapeHtml(str) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    return {
        init: init,
        show: show,
        hide: hide,
        setChannel: setChannel,
        setChannelFromConversation: setChannelFromConversation,
        getChannel: getChannel,
        getChannelDetail: getChannelDetail,
        getText: getText,
        insertText: insertText,
        setRcsPayload: setRcsPayload,
        clearRcsPayload: clearRcsPayload,
        updateCharCount: updateCharCount
    };
})();
