/**
 * Inbox v2 — Main application controller
 * Wires together ConversationList, ChatThread, Composer,
 * and contact sidebar (tags, notes, lists).
 */
var InboxApp = (function () {
    'use strict';

    var config = {};
    var activeConversation = null;
    var _selectRequestId = 0;

    var contactData = {};
    var availableTags = [];
    var availableLists = [];

    /* ── Boot ──────────────────────────────────────────── */
    function init() {
        config = window.__inbox || {};

        ConversationList.init(config.conversations || [], onConversationSelect);
        Composer.init(onSendReply);

        bindGlobalEvents();
        bindContactSidebar();
        fetchAvailableTags();
        fetchAvailableLists();
    }

    /* ── Called when a conversation is selected ─────────── */
    function onConversationSelect(conv) {
        activeConversation = conv;
        Composer.show();
        Composer.setChannelFromConversation(conv);
        updateContactPanel(conv);

        // Auto-mark as read
        if (conv.unread) {
            ConversationList.markRead(conv.id);
            apiPost(config.routes.messages + '/' + conv.id + '/read');
        }

        var requestId = ++_selectRequestId;
        fetch(config.routes.messages + '/' + conv.id + '/messages', {
            headers: { 'Accept': 'application/json' }
        })
        .then(function (res) { return res.json(); })
        .then(function (json) {
            if (requestId !== _selectRequestId) return;
            if (json.success && json.data) {
                conv.messages = json.data;
                if (json.contact) {
                    conv.name = json.contact.name || conv.name;
                    conv.initials = json.contact.initials || conv.initials;
                }
            } else {
                conv.messages = [];
            }
            ChatThread.load(conv);
        })
        .catch(function () {
            if (requestId !== _selectRequestId) return;
            conv.messages = [];
            ChatThread.load(conv);
        });
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
    }

    /* ── Contact panel (modal) ─────────────────────────── */
    function toggleContactPanel() {
        var modal = document.getElementById('contactPanelModal');
        if (!modal || typeof bootstrap === 'undefined') return;
        var bsModal = bootstrap.Modal.getOrCreateInstance(modal);
        bsModal.toggle();
    }

    var AVATAR_COLORS = [
        '#6f42c1', '#e83e8c', '#20c997', '#fd7e14', '#0d6efd',
        '#6610f2', '#d63384', '#198754', '#dc3545', '#0dcaf0'
    ];

    function getAvatarColor(name) {
        var hash = 0;
        var str = name || '?';
        for (var i = 0; i < str.length; i++) {
            hash = str.charCodeAt(i) + ((hash << 5) - hash);
        }
        return AVATAR_COLORS[Math.abs(hash) % AVATAR_COLORS.length];
    }

    function updateContactPanel(conv) {
        setTextContent('contactName', conv.name);
        setTextContent('contactPhone', conv.phone_masked);
        setTextContent('contactChannel', conv.channel ? conv.channel.toUpperCase() : '');
        setTextContent('contactSource', conv.source || '');
        setTextContent('contactFirstDate', conv.first_contact || '');

        var avatar = document.getElementById('contactAvatar');
        if (avatar) {
            avatar.textContent = conv.initials || '?';
            var color = getAvatarColor(conv.name);
            avatar.style.backgroundColor = color + '20';
            avatar.style.color = color;
        }

        var viewBtn = document.getElementById('viewInContactsBtn');
        if (viewBtn) {
            if (conv.contact_id) {
                viewBtn.href = '/contacts/all?highlight=' + conv.contact_id;
            } else {
                viewBtn.href = '/contacts/all';
            }
        }

        renderContactTags(conv.id);
        renderContactNotes(conv.id);
        renderContactLists(conv.id);
    }

    /* ── Contact Sidebar: Tags, Notes, Lists ──────────── */
    function bindContactSidebar() {
        bindClick('showAddTagBtn', function () {
            var form = document.getElementById('addTagForm');
            var btn = document.getElementById('showAddTagBtn');
            if (form) form.classList.remove('d-none');
            if (btn) btn.classList.add('d-none');
            populateTagDropdown();
        });

        bindClick('cancelTagBtn', function () {
            resetTagForm();
        });

        bindClick('saveTagBtn', function () {
            var select = document.getElementById('existingTagSelect');
            var input = document.getElementById('newTagInput');
            var tagName = '';
            if (select && select.value) {
                tagName = select.options[select.selectedIndex].textContent;
            } else if (input && input.value.trim()) {
                tagName = input.value.trim();
            }
            if (!tagName) return;
            addTag(tagName);
            resetTagForm();
        });

        var existingTagSelect = document.getElementById('existingTagSelect');
        if (existingTagSelect) {
            existingTagSelect.addEventListener('change', function () {
                var input = document.getElementById('newTagInput');
                if (this.value && input) input.value = '';
            });
        }

        var tagInput = document.getElementById('newTagInput');
        if (tagInput) {
            tagInput.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    document.getElementById('saveTagBtn').click();
                }
            });
            tagInput.addEventListener('input', function () {
                var select = document.getElementById('existingTagSelect');
                if (this.value && select) select.value = '';
            });
        }

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

        bindClick('showAddToListBtn', function () {
            var form = document.getElementById('addToListForm');
            var btn = document.getElementById('showAddToListBtn');
            if (form) form.classList.remove('d-none');
            if (btn) btn.classList.add('d-none');
            populateListDropdown();
        });

        bindClick('cancelAddToListBtn', function () {
            resetListForm();
        });

        bindClick('confirmAddToListBtn', function () {
            var select = document.getElementById('addToListSelect');
            if (!select || !select.value) return;
            addToList(select.value, select.options[select.selectedIndex].textContent);
            resetListForm();
        });
    }

    function resetTagForm() {
        var form = document.getElementById('addTagForm');
        var btn = document.getElementById('showAddTagBtn');
        if (form) form.classList.add('d-none');
        if (btn) btn.classList.remove('d-none');
        var input = document.getElementById('newTagInput');
        if (input) input.value = '';
        var select = document.getElementById('existingTagSelect');
        if (select) select.value = '';
    }

    function resetListForm() {
        var form = document.getElementById('addToListForm');
        var btn = document.getElementById('showAddToListBtn');
        if (form) form.classList.add('d-none');
        if (btn) btn.classList.remove('d-none');
        var select = document.getElementById('addToListSelect');
        if (select) select.value = '';
    }

    function getContactData(convId) {
        if (!contactData[convId]) {
            contactData[convId] = { tags: [], notes: [], lists: [] };
        }
        return contactData[convId];
    }

    /* ── Fetch available tags & lists ─────────────────── */
    function fetchAvailableTags() {
        fetch('/api/tags', {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': config.csrfToken || '' }
        })
        .then(function (r) { return r.json(); })
        .then(function (json) {
            availableTags = (json.data || []).map(function (t) {
                return { id: t.id, name: t.name, color: t.color || '#6f42c1' };
            });
        })
        .catch(function () { availableTags = []; });
    }

    function fetchAvailableLists() {
        fetch('/api/contact-lists', {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': config.csrfToken || '' }
        })
        .then(function (r) { return r.json(); })
        .then(function (json) {
            availableLists = (json.data || []).map(function (l) {
                return { id: l.id, name: l.name };
            });
        })
        .catch(function () { availableLists = []; });
    }

    function populateTagDropdown() {
        var select = document.getElementById('existingTagSelect');
        if (!select) return;
        while (select.options.length > 1) select.remove(1);

        var data = activeConversation ? getContactData(activeConversation.id) : { tags: [] };
        var existingNames = data.tags.map(function (t) { return t.name.toLowerCase(); });

        availableTags.forEach(function (tag) {
            if (existingNames.indexOf(tag.name.toLowerCase()) === -1) {
                var opt = document.createElement('option');
                opt.value = tag.id;
                opt.textContent = tag.name;
                select.appendChild(opt);
            }
        });
    }

    function populateListDropdown() {
        var select = document.getElementById('addToListSelect');
        if (!select) return;
        while (select.options.length > 1) select.remove(1);

        var data = activeConversation ? getContactData(activeConversation.id) : { lists: [] };
        var existingIds = data.lists.map(function (l) { return l.id; });

        availableLists.forEach(function (list) {
            if (existingIds.indexOf(list.id) === -1) {
                var opt = document.createElement('option');
                opt.value = list.id;
                opt.textContent = list.name;
                select.appendChild(opt);
            }
        });
    }

    /* ── Tags ─────────────────────────────────────────── */
    var PASTEL_TAG_CLASSES = [
        'badge-pastel-primary', 'badge-pastel-success', 'badge-pastel-info',
        'badge-pastel-warning', 'badge-pastel-pink', 'badge-pastel-green',
        'badge-pastel-secondary', 'badge-pastel-danger'
    ];

    function getTagPastelClass(index) {
        return PASTEL_TAG_CLASSES[index % PASTEL_TAG_CLASSES.length];
    }

    function addTag(name) {
        if (!activeConversation) return;
        var data = getContactData(activeConversation.id);
        if (data.tags.some(function (t) { return t.name.toLowerCase() === name.toLowerCase(); })) return;
        data.tags.push({ name: name, created: new Date().toISOString() });
        renderContactTags(activeConversation.id);

        if (activeConversation.contact_id) {
            apiPost('/api/contacts/bulk/add-tags', {
                contact_ids: [activeConversation.contact_id],
                tags: [name]
            });
        }
    }

    function removeTag(convId, tagName) {
        var data = getContactData(convId);
        data.tags = data.tags.filter(function (t) { return t.name !== tagName; });
        renderContactTags(convId);

        var conv = ConversationList.getById ? ConversationList.getById(convId) : activeConversation;
        if (conv && conv.contact_id) {
            apiPost('/api/contacts/bulk/remove-tags', {
                contact_ids: [conv.contact_id],
                tags: [tagName]
            });
        }
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

        container.innerHTML = data.tags.map(function (tag, i) {
            var cls = getTagPastelClass(i);
            return '<span class="badge rounded-pill ' + cls + ' me-1 mb-1" style="cursor: pointer;" ' +
                'title="Click to remove" onclick="InboxApp.removeTag(\'' + convId + '\', \'' + escapeAttr(tag.name) + '\')">' +
                escapeHtml(tag.name) + ' <i class="fas fa-times ms-1" style="font-size: 0.5rem; opacity: 0.7;"></i></span>';
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
    function addToList(listId, listName) {
        if (!activeConversation || !activeConversation.contact_id) return;
        var data = getContactData(activeConversation.id);
        if (data.lists.some(function (l) { return l.id === listId; })) return;
        data.lists.push({ id: listId, name: listName });
        renderContactLists(activeConversation.id);

        apiPost('/api/contact-lists/' + listId + '/members', {
            contact_ids: [activeConversation.contact_id]
        });
    }

    function removeFromList(convId, listId) {
        var data = getContactData(convId);
        data.lists = data.lists.filter(function (l) { return l.id !== listId; });
        renderContactLists(convId);

        var conv = ConversationList.getById ? ConversationList.getById(convId) : activeConversation;
        if (conv && conv.contact_id) {
            fetch('/api/contact-lists/' + listId + '/members', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': config.csrfToken || '',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ contact_ids: [conv.contact_id] })
            }).catch(function (err) { console.warn('[Inbox] Remove from list error:', err); });
        }
    }

    function renderContactLists(convId) {
        var container = document.getElementById('contactLists');
        if (!container) return;

        var data = getContactData(convId);
        if (data.lists.length === 0) {
            container.innerHTML = '<span class="text-muted small">No lists</span>';
            return;
        }

        container.innerHTML = data.lists.map(function (list) {
            return '<span class="badge badge-pastel-info rounded-pill me-1 mb-1" style="cursor: pointer;" ' +
                'title="Click to remove" onclick="InboxApp.removeFromList(\'' + convId + '\', \'' + escapeAttr(list.id) + '\')">' +
                escapeHtml(list.name) + ' <i class="fas fa-times ms-1" style="font-size: 0.5rem; opacity: 0.7;"></i></span>';
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
        }).then(function (res) {
            if (!res.ok) {
                return res.json().then(function (data) {
                    var errMsg = data.message || 'Request failed (' + res.status + ')';
                    console.error('[Inbox] API error ' + res.status + ':', errMsg);
                    showToast(errMsg, 'error');
                    return data;
                }).catch(function () {
                    console.error('[Inbox] API error ' + res.status);
                    showToast('Request failed (' + res.status + ')', 'error');
                });
            }
            return res.json();
        }).catch(function (err) {
            console.warn('[Inbox] Network error:', err);
            showToast('Network error — message not sent', 'error');
        });
    }

    function showToast(message, type) {
        var container = document.getElementById('inboxToastContainer');
        if (!container) {
            container = document.createElement('div');
            container.id = 'inboxToastContainer';
            container.style.cssText = 'position:fixed;top:20px;right:20px;z-index:99999;';
            document.body.appendChild(container);
        }
        var toast = document.createElement('div');
        toast.className = 'alert alert-' + (type === 'error' ? 'danger' : 'success') + ' alert-dismissible fade show';
        toast.style.cssText = 'min-width:300px;margin-bottom:8px;box-shadow:0 4px 12px rgba(0,0,0,0.15);';
        var msgSpan = document.createElement('span');
        msgSpan.textContent = message;
        toast.appendChild(msgSpan);
        var closeBtn = document.createElement('button');
        closeBtn.type = 'button';
        closeBtn.className = 'btn-close';
        closeBtn.setAttribute('data-bs-dismiss', 'alert');
        toast.appendChild(closeBtn);
        container.appendChild(toast);
        setTimeout(function () { if (toast.parentNode) toast.remove(); }, 6000);
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
        if (!str) return '';
        return str.replace(/\\/g, '\\\\').replace(/'/g, "\\'").replace(/"/g, '&quot;');
    }

    /* ── Public API ────────────────────────────────────── */
    return {
        init: init,
        comingSoon: comingSoon,
        removeTag: removeTag,
        removeFromList: removeFromList
    };
})();

/* ── Auto-boot on DOM ready ────────────────────────────── */
document.addEventListener('DOMContentLoaded', function () {
    InboxApp.init();
});
