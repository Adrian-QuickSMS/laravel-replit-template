/**
 * Inbox v2 — Chat Thread module
 * Renders message bubbles, RCS rich cards, date separators, and in-chat search.
 */
var ChatThread = (function () {
    'use strict';

    var currentConv = null;
    var searchMatches = [];
    var searchIndex = -1;

    /* ── Load a conversation into the chat pane ────────── */
    function load(conv) {
        currentConv = conv;

        // Show header + messages, hide empty state
        toggle('chatEmpty', false);
        toggle('chatHeader', true);
        toggle('chatArea', true);

        updateHeader(conv);
        renderMessages(conv.messages, conv.channel);
        scrollToBottom();
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

    /* ── Update chat header ────────────────────────────── */
    function updateHeader(conv) {
        var avatar = document.getElementById('chatHeaderAvatar');
        var name = document.getElementById('chatHeaderName');
        var meta = document.getElementById('chatHeaderMeta');

        if (avatar) {
            avatar.textContent = conv.initials || '?';
            var color = getAvatarColor(conv.name);
            avatar.style.backgroundColor = color + '20';
            avatar.style.color = color;
        }
        if (name) name.textContent = conv.name || conv.phone_masked || '';

        var metaText = conv.phone_masked || '';
        if (conv.reply_to_label) {
            metaText += ' · ' + conv.reply_to_label;
        } else if (conv.channel) {
            metaText += ' · ' + conv.channel.toUpperCase();
        }
        if (meta) meta.textContent = metaText;

        // Update read/unread button text
        var readBtn = document.getElementById('toggleReadBtn');
        if (readBtn) {
            readBtn.innerHTML = conv.unread
                ? '<i class="far fa-envelope-open me-2"></i>Mark as read'
                : '<i class="far fa-envelope me-2"></i>Mark as unread';
        }
    }

    /* ── Render all messages ───────────────────────────── */
    function renderMessages(messages, channel) {
        var area = document.getElementById('chatArea');
        if (!area) return;
        area.innerHTML = '';

        var lastDate = '';
        messages.forEach(function (msg) {
            // Date separator
            var dateStr = msg.date || '';
            if (dateStr && dateStr !== lastDate) {
                area.appendChild(createDateSeparator(dateStr));
                lastDate = dateStr;
            }

            // Rich card or text bubble
            if (msg.type === 'rich_card' && msg.rich_card) {
                area.appendChild(createRichCardBubble(msg, channel));
            } else {
                area.appendChild(createBubble(msg, channel));
            }
        });
    }

    /* ── Create a text message bubble ──────────────────── */
    function createBubble(msg, channel) {
        var isOut = msg.direction === 'outbound';
        var ch = isOut ? channel : '';

        var wrapper = document.createElement('div');
        wrapper.className = 'msg msg--' + (isOut ? 'out' : 'in') +
            (isOut ? ' msg--' + channel : '');

        var inner = document.createElement('div');

        var bubble = document.createElement('div');
        bubble.className = 'msg__bubble';
        bubble.textContent = msg.content || '';

        var timeLine = document.createElement('div');
        timeLine.className = 'msg__time';
        timeLine.innerHTML = escapeHtml(msg.time || '');
        if (isOut) {
            timeLine.innerHTML += ' <i class="fas fa-check msg__delivery-icon text-muted"></i>';
            timeLine.innerHTML += ' <span class="msg__channel-pill msg__channel-pill--' +
                channel + '">' + channel.toUpperCase() + '</span>';
        }

        inner.appendChild(bubble);
        inner.appendChild(timeLine);
        wrapper.appendChild(inner);
        return wrapper;
    }

    /* ── Create a rich card bubble ─────────────────────── */
    function createRichCardBubble(msg, channel) {
        var wrapper = document.createElement('div');
        wrapper.className = 'msg msg--out msg--rcs';
        wrapper.style.justifyContent = 'flex-end';

        var inner = document.createElement('div');

        var card = document.createElement('div');
        card.className = 'msg__rich-card';

        var rc = msg.rich_card;

        var imageUrl = rc.image || (rc.media && (rc.media.savedDataUrl || rc.media.hostedUrl || rc.media.url)) || null;
        var cardTitle = rc.title || rc.description || '';
        var cardDesc = rc.description ? (rc.textBody || '') : (rc.textBody || rc.description || '');
        if (rc.title && rc.description) cardDesc = rc.description;
        if (!rc.title && rc.description && rc.textBody) {
            cardTitle = rc.description;
            cardDesc = rc.textBody;
        }
        var cardButtons = rc.buttons || (rc.button ? [{ label: rc.button }] : []);

        if (imageUrl && !/^\s*javascript:/i.test(imageUrl)) {
            var img = document.createElement('img');
            img.src = imageUrl;
            img.alt = cardTitle || '';
            img.style.width = '100%';
            img.style.maxHeight = '200px';
            img.style.objectFit = 'cover';
            img.style.borderRadius = '8px 8px 0 0';
            img.onerror = function () { this.style.display = 'none'; };
            card.appendChild(img);
        }

        var body = document.createElement('div');
        body.className = 'msg__rich-card-body';
        if (cardTitle) {
            var title = document.createElement('div');
            title.className = 'msg__rich-card-title';
            title.textContent = cardTitle;
            body.appendChild(title);
        }
        if (cardDesc) {
            var desc = document.createElement('div');
            desc.className = 'msg__rich-card-desc';
            desc.textContent = cardDesc;
            body.appendChild(desc);
        }
        card.appendChild(body);

        cardButtons.forEach(function (b) {
            var btn = document.createElement('a');
            btn.href = 'javascript:void(0)';
            btn.className = 'msg__rich-card-btn';
            btn.textContent = b.label || b.text || 'Action';
            card.appendChild(btn);
        });

        // Caption below card
        var timeLine = document.createElement('div');
        timeLine.className = 'msg__time';
        timeLine.innerHTML = escapeHtml(msg.time || '') +
            ' <i class="fas fa-check msg__delivery-icon text-muted"></i>' +
            ' <span class="msg__channel-pill msg__channel-pill--rcs">RCS</span>';

        inner.appendChild(card);
        if (msg.caption) {
            var cap = document.createElement('div');
            cap.className = 'msg__bubble';
            cap.style.marginTop = '0.375rem';
            cap.textContent = msg.caption;
            inner.appendChild(cap);
        }
        inner.appendChild(timeLine);
        wrapper.appendChild(inner);
        return wrapper;
    }

    /* ── Create date separator ─────────────────────────── */
    function createDateSeparator(dateStr) {
        var div = document.createElement('div');
        div.className = 'msg-date-sep';
        div.innerHTML = '<span>' + escapeHtml(dateStr) + '</span>';
        return div;
    }

    /* ── Append a new outbound message (after sending) ─── */
    function appendMessage(msg, channel) {
        var area = document.getElementById('chatArea');
        if (!area) return;

        area.appendChild(createBubble(msg, channel));
        scrollToBottom();

        // Also push into local conversation data
        if (currentConv) {
            currentConv.messages.push(msg);
        }
    }

    /* ── Append a rich card message ────────────────────── */
    function appendRichCard(payload, channel) {
        var area = document.getElementById('chatArea');
        if (!area) return;

        var msg = {
            direction: 'outbound',
            type: 'rich_card',
            time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
            rich_card: payload.cards ? payload.cards[0] : payload,
            caption: payload.caption || ''
        };

        area.appendChild(createRichCardBubble(msg, channel));
        scrollToBottom();
    }

    /* ── In-chat search ────────────────────────────────── */
    function search(term) {
        clearSearchHighlights();
        searchMatches = [];
        searchIndex = -1;

        if (!term || !term.trim()) {
            updateSearchCount();
            return;
        }

        var area = document.getElementById('chatArea');
        if (!area) return;

        var bubbles = area.querySelectorAll('.msg__bubble');
        var lowerTerm = term.toLowerCase();

        bubbles.forEach(function (bubble) {
            var text = bubble.textContent;
            var lower = text.toLowerCase();
            var idx = lower.indexOf(lowerTerm);

            if (idx !== -1) {
                // Wrap matches in highlight spans
                var before = text.substring(0, idx);
                var match = text.substring(idx, idx + term.length);
                var after = text.substring(idx + term.length);

                bubble.innerHTML = escapeHtml(before) +
                    '<span class="msg__highlight">' + escapeHtml(match) + '</span>' +
                    escapeHtml(after);

                searchMatches.push(bubble.querySelector('.msg__highlight'));
            }
        });

        if (searchMatches.length > 0) {
            searchIndex = 0;
            highlightCurrent();
        }

        updateSearchCount();
    }

    function searchNext() {
        if (searchMatches.length === 0) return;
        searchIndex = (searchIndex + 1) % searchMatches.length;
        highlightCurrent();
    }

    function searchPrev() {
        if (searchMatches.length === 0) return;
        searchIndex = (searchIndex - 1 + searchMatches.length) % searchMatches.length;
        highlightCurrent();
    }

    function clearSearchHighlights() {
        var area = document.getElementById('chatArea');
        if (!area) return;
        var highlights = area.querySelectorAll('.msg__highlight');
        highlights.forEach(function (hl) {
            var parent = hl.parentNode;
            parent.replaceChild(document.createTextNode(hl.textContent), hl);
            parent.normalize();
        });
        searchMatches = [];
        searchIndex = -1;
        updateSearchCount();
    }

    function highlightCurrent() {
        searchMatches.forEach(function (el, i) {
            el.classList.toggle('msg__highlight--active', i === searchIndex);
        });
        if (searchMatches[searchIndex]) {
            searchMatches[searchIndex].scrollIntoView({ block: 'center', behavior: 'smooth' });
        }
        updateSearchCount();
    }

    function updateSearchCount() {
        var el = document.getElementById('chatSearchCount');
        if (!el) return;
        if (searchMatches.length === 0) {
            el.textContent = '';
        } else {
            el.textContent = (searchIndex + 1) + ' of ' + searchMatches.length;
        }
    }

    /* ── Helpers ───────────────────────────────────────── */
    function scrollToBottom() {
        var area = document.getElementById('chatArea');
        if (area) {
            requestAnimationFrame(function () {
                area.scrollTop = area.scrollHeight;
            });
        }
    }

    function toggle(id, show) {
        var el = document.getElementById(id);
        if (!el) return;
        el.classList.toggle('d-none', !show);
    }

    function getCurrent() {
        return currentConv;
    }

    function escapeHtml(str) {
        if (!str) return '';
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    /* ── Public API ────────────────────────────────────── */
    return {
        load: load,
        appendMessage: appendMessage,
        appendRichCard: appendRichCard,
        search: search,
        searchNext: searchNext,
        searchPrev: searchPrev,
        clearSearchHighlights: clearSearchHighlights,
        getCurrent: getCurrent,
        scrollToBottom: scrollToBottom
    };
})();
