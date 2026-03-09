/**
 * Inbox v2 — Main application controller
 * Wires together ConversationList, ChatThread, Composer,
 * and contact sidebar (tags, notes, lists).
 */
var InboxApp = (function () {
    'use strict';

    var config = {};
    var activeConversation = null;

    // Local storage for contact notes and tags (mock — replace with API)
    var contactData = {};

    /* ── Boot ──────────────────────────────────────────── */
    function init() {
        config = window.__inbox || {};

        // Initialise sub-modules
        ConversationList.init(config.conversations || [], onConversationSelect);
        Composer.init(onSendReply);

        bindGlobalEvents();
        bindContactSidebar();
    }

    /* ── Called when a conversation is selected ─────────── */
    function onConversationSelect(conv) {
        activeConversation = conv;
        ChatThread.load(conv);
        Composer.show();
        Composer.setChannelFromConversation(conv);
        updateContactPanel(conv);

        // Auto-mark as read
        if (conv.unread) {
            ConversationList.markRead(conv.id);
            apiPost(config.routes.messages + '/' + conv.id + '/read');
        }
    }

    /* ── Called when user sends a reply ─────────────────── */
    function onSendReply(payload) {
        var conv = ConversationList.getActive();
        if (!conv) return;

        var now = new Date();
        var timeStr = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

        // Optimistic UI update
        if (payload.rcs_payload) {
            ChatThread.appendRichCard(payload.rcs_payload, payload.channel);
            ConversationList.updateSnippet(conv.id, '[Rich Card]', timeStr);
        } else {
            var msg = {
                direction: 'outbound',
                content: payload.message,
                time: timeStr,
                date: now.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
            };
            ChatThread.appendMessage(msg, payload.channel);
            ConversationList.updateSnippet(conv.id, payload.message, timeStr);
        }

        var postData = {
            message: payload.message,
            channel: payload.channel,
            rcs_payload: payload.rcs_payload
        };
        if (payload.sender_id) postData.sender_id = payload.sender_id;
        if (payload.rcs_agent) postData.rcs_agent = payload.rcs_agent;
        if (payload.sms_fallback) postData.sms_fallback = payload.sms_fallback;

        apiPost(config.routes.sendReply + '/' + conv.id + '/reply', postData);
    }

    /* ── Global event bindings ─────────────────────────── */
    function bindGlobalEvents() {
        // Back button (mobile)
        bindClick('backToListBtn', function () {
            ConversationList.showSidebar();
        });

        // Chat search toggle
        bindClick('toggleChatSearch', function () {
            var bar = document.getElementById('chatSearchBar');
            if (!bar) return;
            var isHidden = bar.classList.contains('d-none');
            bar.classList.toggle('d-none', !isHidden);
            if (isHidden) {
                var input = document.getElementById('chatSearchInput');
                if (input) input.focus();
            } else {
                ChatThread.clearSearchHighlights();
            }
        });

        // Chat search input
        var chatSearchInput = document.getElementById('chatSearchInput');
        if (chatSearchInput) {
            chatSearchInput.addEventListener('input', function () {
                ChatThread.search(this.value);
            });
        }

        bindClick('chatSearchPrev', function () { ChatThread.searchPrev(); });
        bindClick('chatSearchNext', function () { ChatThread.searchNext(); });
        bindClick('chatSearchClose', function () {
            var bar = document.getElementById('chatSearchBar');
            if (bar) bar.classList.add('d-none');
            var input = document.getElementById('chatSearchInput');
            if (input) input.value = '';
            ChatThread.clearSearchHighlights();
        });

        // Mark read/unread toggle
        bindClick('toggleReadBtn', function () {
            var conv = ConversationList.getActive();
            if (!conv) return;
            if (conv.unread) {
                ConversationList.markRead(conv.id);
                apiPost(config.routes.messages + '/' + conv.id + '/read');
            } else {
                ConversationList.markUnread(conv.id);
                apiPost(config.routes.messages + '/' + conv.id + '/unread');
            }
            // Re-load to update header button text
            ChatThread.load(conv);
        });

        // Contact panel toggle
        bindClick('toggleContactPanel', toggleContactPanel);
        bindClick('closeContactPanel', toggleContactPanel);
    }

    /* ── Contact panel ─────────────────────────────────── */
    function toggleContactPanel() {
        var panel = document.getElementById('contactPanel');
        if (!panel) return;
        panel.classList.toggle('d-none');
    }

    function updateContactPanel(conv) {
        setTextContent('contactName', conv.name);
        setTextContent('contactPhone', conv.phone_masked);
        setTextContent('contactChannel', conv.channel ? conv.channel.toUpperCase() : '');
        setTextContent('contactSource', conv.source || '');
        setTextContent('contactFirstDate', conv.first_contact || '');

        var avatar = document.getElementById('contactAvatar');
        if (avatar) avatar.textContent = conv.initials || '?';

        // Load tags and notes for this contact
        renderContactTags(conv.id);
        renderContactNotes(conv.id);
        renderContactLists(conv.id);
    }

    /* ── Contact Sidebar: Tags, Notes, Lists ──────────── */
    function bindContactSidebar() {
        // Tag add form toggle
        bindClick('showAddTagBtn', function () {
            var form = document.getElementById('addTagForm');
            var btn = document.getElementById('showAddTagBtn');
            if (form) form.classList.remove('d-none');
            if (btn) btn.classList.add('d-none');
            var input = document.getElementById('newTagInput');
            if (input) input.focus();
        });

        bindClick('cancelTagBtn', function () {
            var form = document.getElementById('addTagForm');
            var btn = document.getElementById('showAddTagBtn');
            if (form) form.classList.add('d-none');
            if (btn) btn.classList.remove('d-none');
            var input = document.getElementById('newTagInput');
            if (input) input.value = '';
        });

        bindClick('saveTagBtn', function () {
            var input = document.getElementById('newTagInput');
            if (!input || !input.value.trim()) return;
            addTag(input.value.trim());
            input.value = '';
            var form = document.getElementById('addTagForm');
            var btn = document.getElementById('showAddTagBtn');
            if (form) form.classList.add('d-none');
            if (btn) btn.classList.remove('d-none');
        });

        // Enter key on tag input
        var tagInput = document.getElementById('newTagInput');
        if (tagInput) {
            tagInput.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    document.getElementById('saveTagBtn').click();
                }
            });
        }

        // Note add form toggle
        bindClick('showAddNoteBtn', function () {
            var form = document.getElementById('addNoteForm');
            var btn = document.getElementById('showAddNoteBtn');
            if (form) form.classList.remove('d-none');
            if (btn) btn.classList.add('d-none');
            var input = document.getElementById('newNoteInput');
            if (input) input.focus();
        });

        bindClick('cancelNoteBtn', function () {
            var form = document.getElementById('addNoteForm');
            var btn = document.getElementById('showAddNoteBtn');
            if (form) form.classList.add('d-none');
            if (btn) btn.classList.remove('d-none');
            var input = document.getElementById('newNoteInput');
            if (input) input.value = '';
        });

        bindClick('saveNoteBtn', function () {
            var input = document.getElementById('newNoteInput');
            if (!input || !input.value.trim()) return;
            addNote(input.value.trim());
            input.value = '';
            var form = document.getElementById('addNoteForm');
            var btn = document.getElementById('showAddNoteBtn');
            if (form) form.classList.add('d-none');
            if (btn) btn.classList.remove('d-none');
        });
    }

    function getContactData(convId) {
        if (!contactData[convId]) {
            contactData[convId] = { tags: [], notes: [], lists: [] };
        }
        return contactData[convId];
    }

    /* ── Tags ─────────────────────────────────────────── */
    var TAG_COLORS = ['#886CC0', '#34C759', '#FF9500', '#FF3B30', '#007AFF', '#5856D6', '#AF52DE', '#FF2D55'];

    function addTag(name) {
        if (!activeConversation) return;
        var data = getContactData(activeConversation.id);
        // Prevent duplicate
        if (data.tags.some(function (t) { return t.name.toLowerCase() === name.toLowerCase(); })) return;
        data.tags.push({
            name: name,
            color: TAG_COLORS[data.tags.length % TAG_COLORS.length],
            created: new Date().toISOString()
        });
        renderContactTags(activeConversation.id);

        // API call (when backend is ready)
        if (activeConversation.contact_id) {
            apiPost('/api/contacts/' + activeConversation.contact_id + '/tags', { name: name });
        }
    }

    function removeTag(convId, tagName) {
        var data = getContactData(convId);
        data.tags = data.tags.filter(function (t) { return t.name !== tagName; });
        renderContactTags(convId);
    }

    function renderContactTags(convId) {
        var container = document.getElementById('contactTags');
        var countEl = document.getElementById('contactTagCount');
        if (!container) return;

        var data = getContactData(convId);
        if (countEl) countEl.textContent = data.tags.length;

        if (data.tags.length === 0) {
            container.innerHTML = '<span class="text-muted small">No tags</span>';
            return;
        }

        container.innerHTML = data.tags.map(function (tag) {
            return '<span class="badge me-1 mb-1" style="background-color: ' + tag.color + '; cursor: pointer;" ' +
                'title="Click to remove" onclick="InboxApp.removeTag(\'' + convId + '\', \'' + escapeAttr(tag.name) + '\')">' +
                escapeHtml(tag.name) + ' <i class="fas fa-times ms-1" style="font-size: 0.5rem;"></i></span>';
        }).join('');
    }

    /* ── Notes ────────────────────────────────────────── */
    function addNote(text) {
        if (!activeConversation) return;
        var data = getContactData(activeConversation.id);
        data.notes.unshift({
            text: text,
            created: new Date().toISOString(),
            author: 'You'
        });
        renderContactNotes(activeConversation.id);

        // API call (when backend is ready)
        if (activeConversation.contact_id) {
            apiPost('/api/contacts/' + activeConversation.contact_id + '/notes', { content: text });
        }
    }

    function renderContactNotes(convId) {
        var container = document.getElementById('contactNotes');
        var countEl = document.getElementById('contactNoteCount');
        if (!container) return;

        var data = getContactData(convId);
        if (countEl) countEl.textContent = data.notes.length;

        if (data.notes.length === 0) {
            container.innerHTML = '<span class="text-muted small">No notes yet</span>';
            return;
        }

        container.innerHTML = data.notes.map(function (note) {
            var date = new Date(note.created);
            var dateStr = date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short' }) +
                ' ' + date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            return '<div class="contact-panel__note-item mb-2 p-2 rounded" style="background: var(--inbox-bg-alt); text-align: left;">' +
                '<div class="small">' + escapeHtml(note.text) + '</div>' +
                '<div class="text-muted" style="font-size: 0.625rem;">' + escapeHtml(note.author) + ' · ' + dateStr + '</div>' +
                '</div>';
        }).join('');
    }

    /* ── Lists ────────────────────────────────────────── */
    function renderContactLists(convId) {
        var container = document.getElementById('contactLists');
        if (!container) return;

        var data = getContactData(convId);
        if (data.lists.length === 0) {
            container.innerHTML = '<span class="text-muted small">No lists</span>';
            return;
        }

        container.innerHTML = data.lists.map(function (list) {
            return '<span class="badge bg-secondary me-1 mb-1">' + escapeHtml(list.name) + '</span>';
        }).join('');
    }

    /* ── Coming soon toast ─────────────────────────────── */
    function comingSoon(feature) {
        var msg = (feature || 'This feature') + ' — coming soon!';

        // Use a simple toast if available, otherwise alert
        if (typeof Toastify !== 'undefined') {
            Toastify({ text: msg, duration: 2500, gravity: 'bottom', position: 'right',
                style: { background: '#886CC0' } }).showToast();
        } else {
            // Create a lightweight toast
            var toast = document.createElement('div');
            toast.textContent = msg;
            toast.style.cssText = 'position:fixed;bottom:1.5rem;right:1.5rem;background:#886CC0;color:#fff;' +
                'padding:0.5rem 1rem;border-radius:0.375rem;font-size:0.8125rem;z-index:9999;' +
                'box-shadow:0 2px 8px rgba(0,0,0,0.15);transition:opacity 0.3s;';
            document.body.appendChild(toast);
            setTimeout(function () {
                toast.style.opacity = '0';
                setTimeout(function () { toast.remove(); }, 300);
            }, 2500);
        }
    }

    /* ── Helpers ───────────────────────────────────────── */
    function apiPost(url, body) {
        return fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': config.csrfToken || '',
                'Accept': 'application/json'
            },
            body: body ? JSON.stringify(body) : undefined
        }).catch(function (err) {
            console.warn('[Inbox] API error:', err);
        });
    }

    function bindClick(id, handler) {
        var el = document.getElementById(id);
        if (el) el.addEventListener('click', handler);
    }

    function setTextContent(id, text) {
        var el = document.getElementById(id);
        if (el) el.textContent = text || '';
    }

    function escapeHtml(str) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    function escapeAttr(str) {
        return str.replace(/'/g, "\\'").replace(/"/g, '&quot;');
    }

    /* ── Public API ────────────────────────────────────── */
    return {
        init: init,
        comingSoon: comingSoon,
        removeTag: removeTag
    };
})();

/* ── Auto-boot on DOM ready ────────────────────────────── */
document.addEventListener('DOMContentLoaded', function () {
    InboxApp.init();
});
