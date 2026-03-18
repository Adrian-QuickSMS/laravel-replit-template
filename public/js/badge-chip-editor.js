(function(window) {
    'use strict';

    var PLACEHOLDER_REGEX = /\{\{([^}]+)\}\}/g;
    var URL_REGEX = /((?:https?:\/\/)?(?:qsms\.uk|qout\.uk|custom1\.co\.uk|custom2\.com)\/[a-zA-Z0-9]{3,})/g;

    function BadgeChipEditor(container, options) {
        options = options || {};
        this.container = typeof container === 'string' ? document.querySelector(container) : container;
        if (!this.container) return;

        this.onChangeCallback = options.onChange || null;
        this.placeholder = options.placeholder || 'Type your message here...';
        this.singleLine = options.singleLine || false;

        this._build();
        this._bindEvents();

        if (options.initialValue) {
            this.setValue(options.initialValue);
        }
    }

    BadgeChipEditor.prototype._build = function() {
        this.el = document.createElement('div');
        this.el.className = 'bce-editor' + (this.singleLine ? ' bce-editor--single-line' : '');
        this.el.setAttribute('contenteditable', 'true');
        this.el.setAttribute('data-placeholder', this.placeholder);
        this.el.setAttribute('spellcheck', 'true');
        this.el.setAttribute('role', 'textbox');

        var existingTextarea = this.container.querySelector('textarea');
        var existingInput = !existingTextarea ? this.container.querySelector('input[type="text"]') : null;
        var sourceEl = existingTextarea || existingInput;
        if (sourceEl) {
            this.hiddenTextarea = sourceEl;
            this.hiddenTextarea.style.display = 'none';
            if (existingTextarea) {
                this.el.style.paddingBottom = existingTextarea.style.paddingBottom || '';
                this.el.style.minHeight = (existingTextarea.rows * 1.5) + 'em';
                if (existingTextarea.style.resize === 'none') {
                    this.el.style.resize = 'none';
                }
            }
            if (sourceEl.style.paddingRight) {
                this.el.style.paddingRight = sourceEl.style.paddingRight;
            }
            if (sourceEl.classList.contains('fw-bold')) {
                this.el.style.fontWeight = 'bold';
            }
            var wrapper = sourceEl.parentNode;
            if (wrapper && wrapper.classList && wrapper.classList.contains('border')) {
                wrapper.classList.remove('border');
                wrapper.classList.add('bce-wrapper-active');
            }
            wrapper.insertBefore(this.el, sourceEl);
        } else {
            this.hiddenTextarea = null;
            this.container.appendChild(this.el);
        }
    };

    BadgeChipEditor.prototype._bindEvents = function() {
        var self = this;
        this._savedRange = null;

        this.el.addEventListener('input', function() {
            self._autoConvertPlaceholders();
            self._syncToHidden();
            self._fireChange();
        });

        this.el.addEventListener('keydown', function(e) {
            if (self.singleLine && e.key === 'Enter') {
                e.preventDefault();
                return;
            }
            self._handleKeydown(e);
        });

        this.el.addEventListener('keyup', function() {
            self._saveSelection();
        });

        this.el.addEventListener('mouseup', function() {
            self._saveSelection();
        });

        this.el.addEventListener('blur', function() {
            self._saveSelection();
        });

        this.el.addEventListener('paste', function(e) {
            e.preventDefault();
            var text = (e.clipboardData || window.clipboardData).getData('text/plain');
            self._insertRawText(text);
        });

        this.el.addEventListener('copy', function(e) {
            e.preventDefault();
            var sel = window.getSelection();
            if (sel.rangeCount) {
                var raw = self._extractRawFromRange(sel.getRangeAt(0));
                e.clipboardData.setData('text/plain', raw);
            }
        });

        this.el.addEventListener('cut', function(e) {
            e.preventDefault();
            var sel = window.getSelection();
            if (sel.rangeCount) {
                var range = sel.getRangeAt(0);
                var raw = self._extractRawFromRange(range);
                e.clipboardData.setData('text/plain', raw);
                range.deleteContents();
                self._syncToHidden();
                self._fireChange();
            }
        });

        this.el.addEventListener('focus', function() {
            self.el.parentElement && self.el.parentElement.classList && self.el.parentElement.classList.add('bce-focused');
        });

        this.el.addEventListener('blur', function() {
            self.el.parentElement && self.el.parentElement.classList && self.el.parentElement.classList.remove('bce-focused');
        });
    };

    BadgeChipEditor.prototype._handleKeydown = function(e) {
        var sel = window.getSelection();
        if (!sel.rangeCount) return;
        var range = sel.getRangeAt(0);

        if (e.key === 'Backspace') {
            if (range.collapsed) {
                var chip = this._chipBeforeCaret(range);
                if (chip) {
                    e.preventDefault();
                    chip.parentNode.removeChild(chip);
                    this._syncToHidden();
                    this._fireChange();
                    return;
                }
            }
        }

        if (e.key === 'Delete') {
            if (range.collapsed) {
                var chip = this._chipAfterCaret(range);
                if (chip) {
                    e.preventDefault();
                    chip.parentNode.removeChild(chip);
                    this._syncToHidden();
                    this._fireChange();
                    return;
                }
            }
        }

        if (e.key === 'ArrowLeft') {
            var chip = this._chipBeforeCaret(range);
            if (chip) {
                e.preventDefault();
                var r = document.createRange();
                r.setStartBefore(chip);
                r.collapse(true);
                sel.removeAllRanges();
                sel.addRange(r);
                return;
            }
        }

        if (e.key === 'ArrowRight') {
            var chip = this._chipAfterCaret(range);
            if (chip) {
                e.preventDefault();
                var r = document.createRange();
                r.setStartAfter(chip);
                r.collapse(true);
                sel.removeAllRanges();
                sel.addRange(r);
                return;
            }
        }
    };

    BadgeChipEditor.prototype._chipBeforeCaret = function(range) {
        if (!range.collapsed) return null;
        var node = range.startContainer;
        var offset = range.startOffset;

        if (node.nodeType === Node.TEXT_NODE && offset === 0) {
            var prev = node.previousSibling;
            if (prev && prev.classList && prev.classList.contains('bce-chip')) return prev;
        }

        if (node === this.el || (node.nodeType === Node.ELEMENT_NODE && node.closest && node.closest('.bce-editor'))) {
            var children = node.childNodes;
            if (offset > 0) {
                var prev = children[offset - 1];
                if (prev && prev.classList && prev.classList.contains('bce-chip')) return prev;
            }
        }

        return null;
    };

    BadgeChipEditor.prototype._chipAfterCaret = function(range) {
        if (!range.collapsed) return null;
        var node = range.startContainer;
        var offset = range.startOffset;

        if (node.nodeType === Node.TEXT_NODE && offset === node.textContent.length) {
            var next = node.nextSibling;
            if (next && next.classList && next.classList.contains('bce-chip')) return next;
        }

        if (node === this.el || (node.nodeType === Node.ELEMENT_NODE && node.closest && node.closest('.bce-editor'))) {
            var children = node.childNodes;
            if (offset < children.length) {
                var next = children[offset];
                if (next && next.classList && next.classList.contains('bce-chip')) return next;
            }
        }

        return null;
    };

    BadgeChipEditor.BUILTIN_FIELDS = [
        'first_name', 'last_name', 'full_name', 'mobile_number', 'email',
        'unique_url', 'company', 'title', 'address', 'city', 'postcode',
        'country', 'date_of_birth', 'notes', 'opt_out_url', 'trackingUrl'
    ];

    BadgeChipEditor.prototype._classifyPlaceholder = function(rawValue) {
        var label = rawValue.replace(/^\{\{|\}\}$/g, '').trim();
        if (BadgeChipEditor.BUILTIN_FIELDS.indexOf(label) !== -1) {
            return 'placeholder';
        }
        return 'custom';
    };

    BadgeChipEditor.prototype._createChip = function(rawValue, type) {
        var chip = document.createElement('span');
        chip.setAttribute('contenteditable', 'false');
        chip.setAttribute('data-raw', rawValue);

        var chipType = type || 'placeholder';
        if (chipType === 'placeholder') {
            chipType = this._classifyPlaceholder(rawValue);
        }
        chip.className = 'bce-chip' + (chipType === 'custom' ? ' bce-chip--custom' : '');
        chip.setAttribute('data-type', chipType);

        if (type === 'link') {
            chip.innerHTML = '<span class="bce-chip-icon">\uD83D\uDD17</span><span class="bce-chip-label">' + this._escapeHtml(rawValue) + '</span>';
        } else {
            var label = rawValue.replace(/^\{\{|\}\}$/g, '').trim();
            chip.innerHTML = '<span class="bce-chip-label">' + this._escapeHtml(label) + '</span>';
        }

        var x = document.createElement('span');
        x.className = 'bce-chip-x';
        x.textContent = '\u00D7';
        x.addEventListener('mousedown', function(e) {
            e.preventDefault();
            e.stopPropagation();
        });
        var self = this;
        x.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            chip.parentNode.removeChild(chip);
            self._syncToHidden();
            self._fireChange();
        });
        chip.appendChild(x);

        return chip;
    };

    BadgeChipEditor.prototype._parseAndRender = function(rawText) {
        var frag = document.createDocumentFragment();

        var text = rawText;
        var lastIndex = 0;
        var regex = /(\{\{[^}]+\}\})|((?:https?:\/\/)?(?:qsms\.uk|qout\.uk|custom1\.co\.uk|custom2\.com)\/[a-zA-Z0-9]{3,})/g;
        var match;

        while ((match = regex.exec(text)) !== null) {
            if (match.index > lastIndex) {
                frag.appendChild(document.createTextNode(text.substring(lastIndex, match.index)));
            }
            if (match[1]) {
                frag.appendChild(this._createChip(match[1], 'placeholder'));
            } else if (match[2]) {
                frag.appendChild(this._createChip(match[2], 'link'));
            }
            lastIndex = regex.lastIndex;
        }

        if (lastIndex < text.length) {
            frag.appendChild(document.createTextNode(text.substring(lastIndex)));
        }

        return frag;
    };

    BadgeChipEditor.prototype.setValue = function(rawText) {
        this.el.innerHTML = '';
        if (rawText) {
            this.el.appendChild(this._parseAndRender(rawText));
        }
        this._syncToHidden();
    };

    BadgeChipEditor.prototype.getValue = function() {
        return this._extractRaw(this.el).replace(/\u200B/g, '');
    };

    BadgeChipEditor.prototype._extractRaw = function(node) {
        var raw = '';
        var children = node.childNodes;
        for (var i = 0; i < children.length; i++) {
            var child = children[i];
            if (child.nodeType === Node.TEXT_NODE) {
                raw += child.textContent;
            } else if (child.classList && child.classList.contains('bce-chip')) {
                raw += child.getAttribute('data-raw') || '';
            } else if (child.nodeName === 'BR') {
                raw += '\n';
            } else if (child.nodeName === 'DIV' || child.nodeName === 'P') {
                if (i > 0) raw += '\n';
                raw += this._extractRaw(child);
            } else {
                raw += this._extractRaw(child);
            }
        }
        return raw;
    };

    BadgeChipEditor.prototype._extractRawFromRange = function(range) {
        var cloned = range.cloneContents();
        var temp = document.createElement('div');
        temp.appendChild(cloned);
        return this._extractRaw(temp);
    };

    BadgeChipEditor.prototype._syncToHidden = function() {
        if (this.hiddenTextarea) {
            this.hiddenTextarea.value = this.getValue();
        }
    };

    BadgeChipEditor.prototype._fireChange = function() {
        if (this.onChangeCallback) {
            this.onChangeCallback(this.getValue());
        }
    };

    BadgeChipEditor.prototype._saveSelection = function() {
        var sel = window.getSelection();
        if (sel.rangeCount && this.el.contains(sel.anchorNode)) {
            this._savedRange = sel.getRangeAt(0).cloneRange();
        }
    };

    BadgeChipEditor.prototype._restoreSelection = function() {
        if (this._savedRange) {
            var sel = window.getSelection();
            sel.removeAllRanges();
            sel.addRange(this._savedRange);
            return true;
        }
        return false;
    };

    BadgeChipEditor.prototype._suspendModalFocusTraps = function() {
        var traps = [];
        var modals = document.querySelectorAll('.modal.show[data-bs-focus="true"], .modal.show:not([data-bs-focus="false"])');
        modals.forEach(function(m) {
            var current = m.getAttribute('data-bs-focus');
            if (current !== 'false') {
                traps.push({ el: m, original: current });
                m.setAttribute('data-bs-focus', 'false');
                var inst = bootstrap && bootstrap.Modal && bootstrap.Modal.getInstance(m);
                if (inst && inst._focustrap && inst._focustrap.deactivate) {
                    inst._focustrap.deactivate();
                }
            }
        });
        return traps;
    };

    BadgeChipEditor.prototype._restoreModalFocusTraps = function(traps) {
        traps.forEach(function(t) {
            if (t.original === null) {
                t.el.removeAttribute('data-bs-focus');
            } else {
                t.el.setAttribute('data-bs-focus', t.original || 'true');
            }
        });
    };

    BadgeChipEditor.prototype.insertAtCursor = function(text) {
        var frag = this._parseAndRender(text);
        var nodes = [];
        while (frag.firstChild) {
            nodes.push(frag.firstChild);
            frag.removeChild(frag.firstChild);
        }
        if (nodes.length === 0) return;

        var traps = this._suspendModalFocusTraps();
        var inserted = false;

        var savedRange = this._savedRange;
        if (savedRange) {
            try {
                var sel = window.getSelection();
                sel.removeAllRanges();
                sel.addRange(savedRange);
                if (sel.rangeCount && this.el.contains(sel.anchorNode)) {
                    var range = sel.getRangeAt(0);
                    range.deleteContents();
                    for (var i = 0; i < nodes.length; i++) {
                        range.insertNode(nodes[nodes.length - 1 - i]);
                    }
                    inserted = true;
                }
            } catch (e) {
                inserted = false;
            }
        }

        if (!inserted) {
            try {
                this.el.focus();
                var sel = window.getSelection();
                if (sel.rangeCount && this.el.contains(sel.anchorNode)) {
                    var range = sel.getRangeAt(0);
                    range.deleteContents();
                    for (var i = 0; i < nodes.length; i++) {
                        range.insertNode(nodes[nodes.length - 1 - i]);
                    }
                    inserted = true;
                }
            } catch (e) {
                inserted = false;
            }
        }

        if (!inserted) {
            for (var i = 0; i < nodes.length; i++) {
                this.el.appendChild(nodes[i]);
            }
            inserted = true;
        }

        var lastNode = nodes[nodes.length - 1];
        var zwsp = document.createTextNode('\u200B');
        if (lastNode && lastNode.parentNode) {
            lastNode.parentNode.insertBefore(zwsp, lastNode.nextSibling);
        }

        try {
            var sel = window.getSelection();
            var newRange = document.createRange();
            newRange.setStartAfter(zwsp);
            newRange.collapse(true);
            sel.removeAllRanges();
            sel.addRange(newRange);
        } catch (e) {}

        this._restoreModalFocusTraps(traps);

        this._savedRange = null;
        this._syncToHidden();
        this._fireChange();
    };

    BadgeChipEditor.prototype._insertRawText = function(text) {
        this.el.focus();
        var sel = window.getSelection();
        if (!sel.rangeCount) return;

        var range = sel.getRangeAt(0);
        range.deleteContents();

        var frag = this._parseAndRender(text);
        var lastNode = frag.lastChild;
        range.insertNode(frag);

        if (lastNode) {
            var newRange = document.createRange();
            newRange.setStartAfter(lastNode);
            newRange.collapse(true);
            sel.removeAllRanges();
            sel.addRange(newRange);
        }

        this._syncToHidden();
        this._fireChange();
    };

    BadgeChipEditor.prototype._autoConvertPlaceholders = function() {
        var textNodes = [];
        var walker = document.createTreeWalker(this.el, NodeFilter.SHOW_TEXT, null, false);
        var node;
        while ((node = walker.nextNode())) {
            if (node.parentNode === this.el || (node.parentNode && !node.parentNode.classList.contains('bce-chip'))) {
                textNodes.push(node);
            }
        }
        var regex = /\{\{[^}]+\}\}/g;
        var converted = false;
        for (var i = 0; i < textNodes.length; i++) {
            var tNode = textNodes[i];
            var text = tNode.textContent;
            regex.lastIndex = 0;
            if (!regex.test(text)) { continue; }
            regex.lastIndex = 0;

            var frag = this._parseAndRender(text);
            var zwsp = document.createTextNode('\u200B');
            frag.appendChild(zwsp);

            var parent = tNode.parentNode;
            parent.insertBefore(frag, tNode);
            parent.removeChild(tNode);
            converted = true;

            try {
                var sel = window.getSelection();
                var newRange = document.createRange();
                newRange.setStartAfter(zwsp);
                newRange.collapse(true);
                sel.removeAllRanges();
                sel.addRange(newRange);
            } catch (e) {}
            break;
        }
        return converted;
    };

    BadgeChipEditor.prototype.focus = function() {
        this.el.focus();
    };

    BadgeChipEditor.prototype.getElement = function() {
        return this.el;
    };

    BadgeChipEditor.prototype.onContentChange = function(fn) {
        this.onChangeCallback = fn;
    };

    BadgeChipEditor.prototype._escapeHtml = function(str) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    };

    BadgeChipEditor.initFromTextarea = function(textareaSelector, options) {
        options = options || {};
        var textarea = typeof textareaSelector === 'string' ? document.querySelector(textareaSelector) : textareaSelector;
        if (!textarea) return null;

        var wrapper = textarea.parentElement;
        var editor = new BadgeChipEditor(wrapper, {
            placeholder: textarea.getAttribute('placeholder') || options.placeholder || '',
            singleLine: options.singleLine || false,
            initialValue: textarea.value || '',
            onChange: options.onChange || null
        });

        return editor;
    };

    window.BadgeChipEditor = BadgeChipEditor;

})(window);
