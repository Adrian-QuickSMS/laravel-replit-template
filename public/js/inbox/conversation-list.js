/**
 * Inbox v2 — Conversation List module
 * Handles sidebar filtering, sorting, search, and selection.
 */
var ConversationList = (function () {
    'use strict';

    var conversations = [];
    var filtered = [];
    var activeId = null;
    var onSelectCallback = null;

    /* ── Initialise ────────────────────────────────────── */
    function init(data, onSelect) {
        conversations = data || [];
        filtered = conversations.slice();
        onSelectCallback = onSelect;

        bindEvents();
        populateSourceFilter();
        render();
    }

    /* ── Bind DOM events ───────────────────────────────── */
    function bindEvents() {
        var searchEl = document.getElementById('sidebarSearch');
        var channelEl = document.getElementById('filterChannel');
        var statusEl = document.getElementById('filterStatus');
        var sourceEl = document.getElementById('filterSource');
        var sortEl = document.getElementById('sortOrder');

        if (searchEl) searchEl.addEventListener('input', applyFilters);
        if (channelEl) channelEl.addEventListener('change', applyFilters);
        if (statusEl) statusEl.addEventListener('change', applyFilters);
        if (sourceEl) sourceEl.addEventListener('change', applyFilters);
        if (sortEl) sortEl.addEventListener('change', applyFilters);
    }

    /* ── Populate source filter from data ──────────────── */
    function populateSourceFilter() {
        var sourceEl = document.getElementById('filterSource');
        if (!sourceEl) return;

        var sources = {};
        conversations.forEach(function (c) {
            sources[c.source] = true;
        });

        Object.keys(sources).sort().forEach(function (s) {
            var opt = document.createElement('option');
            opt.value = s;
            opt.textContent = s;
            sourceEl.appendChild(opt);
        });
    }

    /* ── Filtering + sorting ───────────────────────────── */
    function applyFilters() {
        var search = (document.getElementById('sidebarSearch') || {}).value || '';
        var channel = (document.getElementById('filterChannel') || {}).value || 'all';
        var status = (document.getElementById('filterStatus') || {}).value || 'all';
        var source = (document.getElementById('filterSource') || {}).value || 'all';
        var sort = (document.getElementById('sortOrder') || {}).value || 'newest';

        var term = search.toLowerCase().trim();

        filtered = conversations.filter(function (c) {
            // Channel filter
            if (channel !== 'all' && c.channel !== channel) return false;

            // Status filter
            if (status === 'unread' && !c.unread) return false;
            if (status === 'read' && c.unread) return false;
            if (status === 'awaiting' && !c.awaiting_reply_48h) return false;

            // Source filter
            if (source !== 'all' && c.source !== source) return false;

            // Search
            if (term) {
                var haystack = (c.name + ' ' + c.phone + ' ' + c.last_message).toLowerCase();
                if (haystack.indexOf(term) === -1) return false;
            }

            return true;
        });

        // Sort
        filtered.sort(function (a, b) {
            switch (sort) {
                case 'oldest': return a.timestamp - b.timestamp;
                case 'alpha': return (a.name || '').localeCompare(b.name || '');
                case 'unread_first':
                    if (a.unread !== b.unread) return a.unread ? -1 : 1;
                    return b.timestamp - a.timestamp;
                default: return b.timestamp - a.timestamp; // newest
            }
        });

        render();
    }

    /* ── Render list ───────────────────────────────────── */
    function render() {
        var container = document.getElementById('conversationList');
        if (!container) return;

        container.innerHTML = '';

        if (filtered.length === 0) {
            container.innerHTML = '<div class="p-3 text-center text-muted" style="font-size:0.8125rem">No conversations match your filters</div>';
            return;
        }

        filtered.forEach(function (conv) {
            var el = createItemElement(conv);
            container.appendChild(el);
        });

        updateUnreadBadge();
    }

    /* ── Create a single conversation DOM element ──────── */
    function createItemElement(conv) {
        var div = document.createElement('div');
        div.className = 'conv-item' +
            (conv.unread ? ' conv-item--unread' : '') +
            (conv.id === activeId ? ' conv-item--active' : '');
        div.setAttribute('data-id', conv.id);

        var snippet = conv.last_message || '';
        if (snippet.length > 45) snippet = snippet.substring(0, 45) + '…';

        div.innerHTML =
            '<div class="conv-item__avatar"><span class="conv-item__initials">' + escapeHtml(conv.initials) + '</span></div>' +
            '<div class="conv-item__body">' +
                '<div class="conv-item__top">' +
                    '<span class="conv-item__name">' + escapeHtml(conv.name) + '</span>' +
                    '<span class="conv-item__time">' + escapeHtml(conv.last_message_time) + '</span>' +
                '</div>' +
                '<div class="conv-item__bottom">' +
                    '<span class="conv-item__snippet">' + escapeHtml(snippet) + '</span>' +
                    '<div class="conv-item__badges">' +
                        '<span class="conv-item__channel conv-item__channel--' + conv.channel + '">' + conv.channel.toUpperCase() + '</span>' +
                        (conv.unread_count > 0 ? '<span class="conv-item__unread-badge">' + conv.unread_count + '</span>' : '') +
                    '</div>' +
                '</div>' +
            '</div>';

        div.addEventListener('click', function () {
            select(conv.id);
        });

        return div;
    }

    /* ── Select a conversation ─────────────────────────── */
    function select(id) {
        activeId = id;

        // Update active class
        var items = document.querySelectorAll('.conv-item');
        items.forEach(function (el) {
            el.classList.toggle('conv-item--active', el.getAttribute('data-id') === id);
        });

        // On mobile, hide sidebar
        var sidebar = document.getElementById('inboxSidebar');
        if (sidebar && window.innerWidth <= 992) {
            sidebar.classList.add('sidebar-hidden');
        }

        var conv = findById(id);
        if (conv && onSelectCallback) {
            onSelectCallback(conv);
        }
    }

    /* ── Update unread badge in sidebar footer ─────────── */
    function updateUnreadBadge() {
        var total = 0;
        conversations.forEach(function (c) {
            if (c.unread) total += c.unread_count || 1;
        });
        var el = document.getElementById('sidebarUnreadCount');
        if (el) el.textContent = total + ' unread';
    }

    /* ── Mark conversation read/unread in local state ──── */
    function markRead(id) {
        var conv = findById(id);
        if (conv) {
            conv.unread = false;
            conv.unread_count = 0;
            render();
        }
    }

    function markUnread(id) {
        var conv = findById(id);
        if (conv) {
            conv.unread = true;
            conv.unread_count = conv.unread_count || 1;
            render();
        }
    }

    /* ── Update snippet after sending ──────────────────── */
    function updateSnippet(id, text, time) {
        var conv = findById(id);
        if (conv) {
            conv.last_message = text;
            conv.last_message_time = time || 'Just now';
            conv.timestamp = Math.floor(Date.now() / 1000);
            render();
        }
    }

    /* ── Helpers ───────────────────────────────────────── */
    function findById(id) {
        for (var i = 0; i < conversations.length; i++) {
            if (conversations[i].id === id) return conversations[i];
        }
        return null;
    }

    function getActive() {
        return findById(activeId);
    }

    function showSidebar() {
        var sidebar = document.getElementById('inboxSidebar');
        if (sidebar) sidebar.classList.remove('sidebar-hidden');
    }

    function escapeHtml(str) {
        if (!str) return '';
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    function resetFilters() {
        var ids = ['sidebarSearch', 'filterChannel', 'filterStatus', 'filterSource', 'sortOrder'];
        ids.forEach(function (id) {
            var el = document.getElementById(id);
            if (el) {
                if (el.tagName === 'SELECT') el.selectedIndex = 0;
                else el.value = '';
            }
        });
        applyFilters();
    }

    /* ── Public API ────────────────────────────────────── */
    return {
        init: init,
        select: select,
        getActive: getActive,
        markRead: markRead,
        markUnread: markUnread,
        updateSnippet: updateSnippet,
        showSidebar: showSidebar,
        resetFilters: resetFilters,
        applyFilters: applyFilters
    };
})();
