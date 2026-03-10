/**
 * QuickSMS Emoji Picker — Reusable floating popover component
 *
 * Usage:
 *   var picker = new QSEmojiPicker({
 *       triggerEl: document.getElementById('emojiPickerBtn'),
 *       textareaEl: document.getElementById('smsContent'),
 *       onInsert: function(emoji) { handleContentChange(); }
 *   });
 *
 *   // Optionally open for a different textarea (e.g. RCS fields):
 *   picker.openFor(document.getElementById('rcsTitle'));
 */
(function (root) {
    'use strict';

    // ─── Emoji data: { id, icon (tab), label, emojis[] } ───
    var CATEGORIES = [
        {
            id: 'recent',
            icon: '🕑',
            label: 'Recently Used',
            emojis: [] // populated from localStorage
        },
        {
            id: 'common',
            icon: '⭐',
            label: 'Commonly Used',
            emojis: ['😊','👍','❤️','🎉','✅','⭐','📱','📞','📧','📅','⏰','💊']
        },
        {
            id: 'smileys',
            icon: '😀',
            label: 'Smileys & People',
            emojis: [
                '😀','😃','😄','😁','😅','😂','🤣','😇','🙂','😉','😍','🥰',
                '😘','😋','😎','🤔','🤗','🤩','🥳','😏','😢','😭','😤','😡',
                '🤯','😱','🥶','🥵','😴','🤮','🤧','😷','🤠','🥺','😬','🫠'
            ]
        },
        {
            id: 'gestures',
            icon: '👋',
            label: 'Gestures & Body',
            emojis: [
                '👌','✌️','👋','👏','🙏','🤝','👊','✊','🤞','🤟','🤙','👈',
                '👉','👆','👇','☝️','💪','🦾','🙌','👐','🤲','🫶','👍','👎'
            ]
        },
        {
            id: 'hearts',
            icon: '❤️',
            label: 'Hearts & Symbols',
            emojis: [
                '❤️','💙','💚','💜','💛','🧡','🖤','🤍','🤎','💔','💕','💖',
                '💗','💘','💝','✨','💯','🔥','⚡','💥','🌟','⭐','🌈','🎵'
            ]
        },
        {
            id: 'healthcare',
            icon: '🏥',
            label: 'Healthcare',
            emojis: [
                '🏥','👨‍⚕️','👩‍⚕️','💉','🩺','🩹','💊','💪','🧘','🫀','🫁',
                '🦷','👁️','🧬','🩸','🩻','♿','🚑','⚕️','🧑‍⚕️'
            ]
        },
        {
            id: 'business',
            icon: '💼',
            label: 'Business & Office',
            emojis: [
                '💼','📊','📈','📉','🔔','🎯','💡','🚀','🔗','📋','📝','📌',
                '📎','✏️','🖊️','📁','🗂️','🗓️','💳','💰','🏦','🤑','📦','🏷️'
            ]
        },
        {
            id: 'travel',
            icon: '✈️',
            label: 'Travel & Places',
            emojis: [
                '✈️','🚗','🚕','🚌','🏠','🏢','🏪','🏫','⛪','🏰','🗽','🗼',
                '🌍','🌎','🌏','🗺️','🧭','🏖️','🏔️','🌋','🏕️','🎢'
            ]
        },
        {
            id: 'food',
            icon: '🍕',
            label: 'Food & Drink',
            emojis: [
                '🍕','🍔','🍟','🌭','🍿','🧁','🍩','🍪','🎂','🍰','☕','🍵',
                '🧃','🍷','🍺','🥂','🍽️','🥗','🍱','🍜','🍣','🌮'
            ]
        },
        {
            id: 'nature',
            icon: '🌿',
            label: 'Animals & Nature',
            emojis: [
                '🐶','🐱','🐭','🐰','🦊','🐻','🐼','🐸','🐵','🦁','🐯','🐮',
                '🌸','🌺','🌻','🌹','🌷','🍀','🌿','🍃','🌴','🌲','🌊','☀️'
            ]
        },
        {
            id: 'flags',
            icon: '🏳️',
            label: 'Flags',
            emojis: [
                '🏳️','🏴','🇬🇧','🇺🇸','🇪🇺','🇫🇷','🇩🇪','🇪🇸','🇮🇹','🇯🇵',
                '🇨🇳','🇰🇷','🇧🇷','🇮🇳','🇦🇺','🇨🇦','🇿🇦','🇳🇬','🇰🇪','🇦🇪'
            ]
        }
    ];

    var RECENT_KEY = 'qs_emoji_recent';
    var MAX_RECENT = 16;

    // ─── Simple emoji keyword map for search ───
    var SEARCH_KEYWORDS = {
        '😊': 'happy smile blush',
        '👍': 'thumbs up like yes ok good approve',
        '❤️': 'heart love red',
        '🎉': 'party celebrate tada congratulations',
        '✅': 'check done complete yes tick',
        '⭐': 'star favourite',
        '📱': 'phone mobile cell',
        '📞': 'telephone call ring',
        '📧': 'email mail envelope',
        '📅': 'calendar date schedule',
        '⏰': 'alarm clock time reminder',
        '💊': 'pill medicine medication drug',
        '😀': 'happy grin',
        '😃': 'happy grin smile big',
        '😄': 'happy smile grin',
        '😁': 'grin teeth smile',
        '😅': 'sweat nervous haha',
        '😂': 'laugh cry tears joy lol',
        '🤣': 'rolling laugh rofl',
        '😇': 'angel innocent halo',
        '🙂': 'smile slight',
        '😉': 'wink',
        '😍': 'love heart eyes',
        '🥰': 'love hearts smiling face',
        '😘': 'kiss blow love',
        '😋': 'yummy delicious tongue',
        '😎': 'cool sunglasses',
        '🤔': 'thinking hmm wonder',
        '🤗': 'hug hugging',
        '🤩': 'star struck excited wow',
        '🥳': 'party celebrate birthday hat',
        '😏': 'smirk',
        '😢': 'sad cry tear',
        '😭': 'crying sob',
        '😤': 'angry huff',
        '😡': 'angry mad rage red',
        '🤯': 'mind blown exploding head shocked',
        '😱': 'scream shock horror',
        '🥶': 'cold freezing ice',
        '🥵': 'hot sweat heat',
        '😴': 'sleep zzz tired',
        '🤮': 'vomit sick',
        '🤧': 'sneeze tissue sick',
        '😷': 'mask sick face covering',
        '🤠': 'cowboy hat',
        '🥺': 'pleading puppy eyes',
        '😬': 'grimace awkward',
        '🫠': 'melting face',
        '👌': 'ok perfect',
        '✌️': 'peace victory',
        '👋': 'wave hello hi bye',
        '👏': 'clap applause bravo',
        '🙏': 'pray please hope thanks',
        '🤝': 'handshake deal agree',
        '👊': 'fist bump punch',
        '✊': 'fist power',
        '🤞': 'crossed fingers luck hope',
        '🤟': 'love you gesture rock',
        '🤙': 'call me hang loose',
        '👈': 'point left',
        '👉': 'point right',
        '👆': 'point up',
        '👇': 'point down',
        '☝️': 'point up one',
        '💪': 'muscle strong flex power',
        '🦾': 'robotic arm strong',
        '🙌': 'hands celebration raise',
        '👐': 'open hands',
        '🤲': 'palms up',
        '🫶': 'heart hands love',
        '👎': 'thumbs down dislike no bad',
        '💙': 'blue heart',
        '💚': 'green heart',
        '💜': 'purple heart',
        '💛': 'yellow heart',
        '🧡': 'orange heart',
        '🖤': 'black heart',
        '🤍': 'white heart',
        '🤎': 'brown heart',
        '💔': 'broken heart',
        '💕': 'two hearts love',
        '💖': 'sparkling heart',
        '💗': 'growing heart',
        '💘': 'cupid heart arrow',
        '💝': 'ribbon heart gift',
        '✨': 'sparkles glitter shine',
        '💯': 'hundred perfect score',
        '🔥': 'fire hot lit flame',
        '⚡': 'lightning bolt electric zap',
        '💥': 'boom explosion collision',
        '🌟': 'glowing star bright',
        '🌈': 'rainbow',
        '🎵': 'music note song',
        '🏥': 'hospital',
        '👨‍⚕️': 'male doctor man health',
        '👩‍⚕️': 'female doctor woman health',
        '💉': 'syringe injection vaccine needle',
        '🩺': 'stethoscope doctor',
        '🩹': 'bandage plaster adhesive',
        '🧘': 'yoga meditation zen',
        '🫀': 'anatomical heart organ',
        '🫁': 'lungs breathing respiratory',
        '🦷': 'tooth dental dentist',
        '👁️': 'eye vision see',
        '🧬': 'dna genetics science',
        '🩸': 'blood drop donate',
        '🩻': 'xray scan bones',
        '♿': 'wheelchair disability accessibility',
        '🚑': 'ambulance emergency',
        '⚕️': 'medical symbol caduceus',
        '🧑‍⚕️': 'health worker doctor nurse',
        '💼': 'briefcase work office business',
        '📊': 'chart bar graph statistics',
        '📈': 'chart increasing growth up',
        '📉': 'chart decreasing down',
        '🔔': 'bell notification alert ring',
        '🎯': 'target dart bullseye goal',
        '💡': 'light bulb idea',
        '🚀': 'rocket launch fast ship',
        '🔗': 'link chain url',
        '📋': 'clipboard list',
        '📝': 'memo note write',
        '📌': 'pushpin pin',
        '📎': 'paperclip attach',
        '✏️': 'pencil write edit',
        '🖊️': 'pen write',
        '📁': 'folder file directory',
        '🗂️': 'dividers files tabs',
        '🗓️': 'calendar date',
        '💳': 'credit card payment',
        '💰': 'money bag cash',
        '🏦': 'bank building',
        '🤑': 'money face rich',
        '📦': 'package box delivery',
        '🏷️': 'label tag price',
        '✈️': 'airplane plane flight travel',
        '🚗': 'car automobile',
        '🚕': 'taxi cab',
        '🚌': 'bus',
        '🏠': 'house home',
        '🏢': 'office building',
        '🏪': 'store shop convenience',
        '🏫': 'school education',
        '⛪': 'church',
        '🏰': 'castle',
        '🗽': 'statue liberty new york',
        '🗼': 'tokyo tower',
        '🌍': 'earth globe africa europe world',
        '🌎': 'earth globe americas world',
        '🌏': 'earth globe asia australia world',
        '🗺️': 'world map',
        '🧭': 'compass direction',
        '🏖️': 'beach umbrella holiday',
        '🏔️': 'mountain snow',
        '🌋': 'volcano',
        '🏕️': 'camping tent',
        '🎢': 'roller coaster theme park',
        '🍕': 'pizza food',
        '🍔': 'burger hamburger food',
        '🍟': 'fries chips food',
        '🌭': 'hot dog food',
        '🍿': 'popcorn cinema snack',
        '🧁': 'cupcake cake sweet',
        '🍩': 'donut doughnut sweet',
        '🍪': 'cookie biscuit sweet',
        '🎂': 'birthday cake celebration',
        '🍰': 'shortcake cake dessert',
        '☕': 'coffee tea hot drink',
        '🍵': 'tea cup drink',
        '🧃': 'juice box drink',
        '🍷': 'wine glass drink',
        '🍺': 'beer mug drink',
        '🥂': 'cheers champagne toast celebrate',
        '🍽️': 'plate cutlery fork knife dinner',
        '🥗': 'salad green healthy',
        '🍱': 'bento box japanese food',
        '🍜': 'noodles ramen soup',
        '🍣': 'sushi japanese food',
        '🌮': 'taco mexican food',
        '🐶': 'dog puppy',
        '🐱': 'cat kitten',
        '🐭': 'mouse',
        '🐰': 'rabbit bunny',
        '🦊': 'fox',
        '🐻': 'bear',
        '🐼': 'panda bear',
        '🐸': 'frog',
        '🐵': 'monkey',
        '🦁': 'lion king',
        '🐯': 'tiger',
        '🐮': 'cow',
        '🌸': 'cherry blossom flower pink spring',
        '🌺': 'hibiscus flower tropical',
        '🌻': 'sunflower flower yellow',
        '🌹': 'rose flower red',
        '🌷': 'tulip flower',
        '🍀': 'four leaf clover lucky',
        '🌿': 'herb plant green',
        '🍃': 'leaves wind nature',
        '🌴': 'palm tree tropical',
        '🌲': 'evergreen tree pine',
        '🌊': 'wave ocean sea water',
        '☀️': 'sun sunny weather',
        '🏳️': 'white flag',
        '🏴': 'black flag',
        '🇬🇧': 'uk united kingdom britain flag',
        '🇺🇸': 'us usa united states america flag',
        '🇪🇺': 'eu european union flag',
        '🇫🇷': 'france french flag',
        '🇩🇪': 'germany german flag',
        '🇪🇸': 'spain spanish flag',
        '🇮🇹': 'italy italian flag',
        '🇯🇵': 'japan japanese flag',
        '🇨🇳': 'china chinese flag',
        '🇰🇷': 'korea korean flag south',
        '🇧🇷': 'brazil brazilian flag',
        '🇮🇳': 'india indian flag',
        '🇦🇺': 'australia flag',
        '🇨🇦': 'canada flag',
        '🇿🇦': 'south africa flag',
        '🇳🇬': 'nigeria flag',
        '🇰🇪': 'kenya flag',
        '🇦🇪': 'uae emirates flag'
    };

    // ─── Constructor ───
    function QSEmojiPicker(opts) {
        this.triggerEl = opts.triggerEl;
        this.textareaEl = opts.textareaEl;
        this.onInsert = opts.onInsert || function () {};
        this._activeTextarea = opts.textareaEl;
        this._currentTab = 'common';
        this._searchTerm = '';
        this._el = null;
        this._built = false;

        this._onDocClick = this._handleDocClick.bind(this);

        if (this.triggerEl) {
            this.triggerEl.addEventListener('click', this.toggle.bind(this));
        }
    }

    // ─── Public API ───

    QSEmojiPicker.prototype.toggle = function (e) {
        if (e) { e.preventDefault(); e.stopPropagation(); }
        if (this.isOpen()) { this.close(); } else { this.open(); }
    };

    QSEmojiPicker.prototype.open = function () {
        if (!this._built) this._build();
        this._loadRecent();
        this._renderBody();
        this._el.classList.add('qs-emoji-picker--open');
        this._position();
        var searchInput = this._el.querySelector('.qs-emoji-picker__search input');
        if (searchInput) {
            searchInput.value = '';
            this._searchTerm = '';
        }
        document.addEventListener('click', this._onDocClick, true);
        document.addEventListener('keydown', this._onEsc = function (ev) {
            if (ev.key === 'Escape') this.close();
        }.bind(this));
    };

    QSEmojiPicker.prototype.close = function () {
        if (this._el) this._el.classList.remove('qs-emoji-picker--open');
        document.removeEventListener('click', this._onDocClick, true);
        if (this._onEsc) document.removeEventListener('keydown', this._onEsc);
        this._activeTextarea = this.textareaEl;
    };

    QSEmojiPicker.prototype.isOpen = function () {
        return this._el && this._el.classList.contains('qs-emoji-picker--open');
    };

    /** Open the picker targeting a different textarea (e.g. an RCS field). */
    QSEmojiPicker.prototype.openFor = function (textareaEl) {
        this._activeTextarea = textareaEl;
        this.open();
    };

    /** Reset target back to the default textarea. */
    QSEmojiPicker.prototype.resetTarget = function () {
        this._activeTextarea = this.textareaEl;
    };

    QSEmojiPicker.prototype.destroy = function () {
        this.close();
        if (this._el && this._el.parentNode) this._el.parentNode.removeChild(this._el);
        if (this.triggerEl) this.triggerEl.removeEventListener('click', this.toggle);
    };

    // ─── Build DOM ───

    QSEmojiPicker.prototype._build = function () {
        var el = document.createElement('div');
        el.className = 'qs-emoji-picker';

        // Warning
        el.innerHTML =
            '<div class="qs-emoji-picker__warning">' +
                '<i class="fas fa-exclamation-triangle"></i>' +
                '<span>Emojis switch to Unicode encoding, reducing characters per segment.</span>' +
            '</div>' +
            '<div class="qs-emoji-picker__search">' +
                '<input type="text" placeholder="Search emojis..." autocomplete="off">' +
            '</div>' +
            '<div class="qs-emoji-picker__tabs"></div>' +
            '<div class="qs-emoji-picker__body"></div>';

        // Build tabs
        var tabsContainer = el.querySelector('.qs-emoji-picker__tabs');
        var self = this;
        CATEGORIES.forEach(function (cat) {
            var btn = document.createElement('button');
            btn.className = 'qs-emoji-picker__tab' + (cat.id === self._currentTab ? ' qs-emoji-picker__tab--active' : '');
            btn.setAttribute('data-tab', cat.id);
            btn.setAttribute('title', cat.label);
            btn.type = 'button';
            btn.textContent = cat.icon;
            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                self._switchTab(cat.id);
            });
            tabsContainer.appendChild(btn);
        });

        // Search handler
        var searchInput = el.querySelector('.qs-emoji-picker__search input');
        searchInput.addEventListener('input', function () {
            self._searchTerm = this.value.trim().toLowerCase();
            self._renderBody();
        });
        searchInput.addEventListener('click', function (e) { e.stopPropagation(); });

        // Prevent clicks inside picker from closing it
        el.addEventListener('click', function (e) { e.stopPropagation(); });

        // Append to document
        document.body.appendChild(el);
        this._el = el;
        this._built = true;
    };

    // ─── Position near trigger button ───

    QSEmojiPicker.prototype._position = function () {
        if (!this.triggerEl || !this._el) return;
        var rect = this.triggerEl.getBoundingClientRect();
        var pickerW = 340;
        var pickerH = 420;

        // Prefer opening above the button if there's room, else below
        var top, left;
        left = rect.right - pickerW;
        if (left < 8) left = 8;
        if (left + pickerW > window.innerWidth - 8) left = window.innerWidth - pickerW - 8;

        if (rect.top > pickerH + 8) {
            // Above
            top = rect.top - pickerH - 4 + window.scrollY;
        } else {
            // Below
            top = rect.bottom + 4 + window.scrollY;
        }

        left = left + window.scrollX;

        this._el.style.top = top + 'px';
        this._el.style.left = left + 'px';
    };

    // ─── Tab switching ───

    QSEmojiPicker.prototype._switchTab = function (tabId) {
        this._currentTab = tabId;
        // Update active class
        var tabs = this._el.querySelectorAll('.qs-emoji-picker__tab');
        tabs.forEach(function (t) {
            t.classList.toggle('qs-emoji-picker__tab--active', t.getAttribute('data-tab') === tabId);
        });
        // If switching tab, clear search
        this._searchTerm = '';
        var searchInput = this._el.querySelector('.qs-emoji-picker__search input');
        if (searchInput) searchInput.value = '';
        this._renderBody();
        // Scroll to top
        var body = this._el.querySelector('.qs-emoji-picker__body');
        if (body) body.scrollTop = 0;
    };

    // ─── Render emoji body ───

    QSEmojiPicker.prototype._renderBody = function () {
        var body = this._el.querySelector('.qs-emoji-picker__body');
        body.innerHTML = '';

        if (this._searchTerm) {
            this._renderSearch(body);
        } else {
            this._renderCategory(body, this._currentTab);
        }
    };

    QSEmojiPicker.prototype._renderCategory = function (container, catId) {
        var cat = CATEGORIES.find(function (c) { return c.id === catId; });
        if (!cat) return;

        if (cat.id === 'recent' && cat.emojis.length === 0) {
            container.innerHTML = '<div class="qs-emoji-picker__empty">No recently used emojis yet.</div>';
            return;
        }

        var label = document.createElement('div');
        label.className = 'qs-emoji-picker__category-label';
        label.textContent = cat.label;
        container.appendChild(label);

        var grid = document.createElement('div');
        grid.className = 'qs-emoji-picker__grid';
        var self = this;
        cat.emojis.forEach(function (emoji) {
            grid.appendChild(self._makeEmojiBtn(emoji));
        });
        container.appendChild(grid);
    };

    QSEmojiPicker.prototype._renderSearch = function (container) {
        var term = this._searchTerm;
        var results = [];
        var seen = {};

        CATEGORIES.forEach(function (cat) {
            if (cat.id === 'recent') return;
            cat.emojis.forEach(function (emoji) {
                if (seen[emoji]) return;
                var keywords = (SEARCH_KEYWORDS[emoji] || '').toLowerCase();
                if (emoji.indexOf(term) !== -1 || keywords.indexOf(term) !== -1) {
                    results.push(emoji);
                    seen[emoji] = true;
                }
            });
        });

        if (results.length === 0) {
            container.innerHTML = '<div class="qs-emoji-picker__empty">No emojis found for "' +
                this._escHtml(term) + '"</div>';
            return;
        }

        var label = document.createElement('div');
        label.className = 'qs-emoji-picker__category-label';
        label.textContent = 'Search Results (' + results.length + ')';
        container.appendChild(label);

        var grid = document.createElement('div');
        grid.className = 'qs-emoji-picker__grid';
        var self = this;
        results.forEach(function (emoji) {
            grid.appendChild(self._makeEmojiBtn(emoji));
        });
        container.appendChild(grid);
    };

    QSEmojiPicker.prototype._makeEmojiBtn = function (emoji) {
        var btn = document.createElement('button');
        btn.className = 'qs-emoji-picker__emoji';
        btn.type = 'button';
        btn.textContent = emoji;
        btn.setAttribute('title', SEARCH_KEYWORDS[emoji] || emoji);
        var self = this;
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            self._insertEmoji(emoji);
        });
        return btn;
    };

    // ─── Insert emoji into textarea ───

    QSEmojiPicker.prototype._insertEmoji = function (emoji) {
        var ta = this._activeTextarea;
        if (ta) {
            var start = ta.selectionStart || 0;
            var end = ta.selectionEnd || 0;
            var val = ta.value;
            ta.value = val.substring(0, start) + emoji + val.substring(end);
            var newPos = start + emoji.length;
            ta.selectionStart = newPos;
            ta.selectionEnd = newPos;
            ta.focus();
            // Trigger input event so char counters update
            ta.dispatchEvent(new Event('input', { bubbles: true }));
        }
        this._saveRecent(emoji);
        this.onInsert(emoji);
        this.close();
    };

    // ─── Recently used (localStorage) ───

    QSEmojiPicker.prototype._loadRecent = function () {
        var recent = [];
        try {
            var stored = localStorage.getItem(RECENT_KEY);
            if (stored) recent = JSON.parse(stored);
        } catch (e) { /* ignore */ }
        CATEGORIES[0].emojis = Array.isArray(recent) ? recent.slice(0, MAX_RECENT) : [];
    };

    QSEmojiPicker.prototype._saveRecent = function (emoji) {
        var recent = [];
        try {
            var stored = localStorage.getItem(RECENT_KEY);
            if (stored) recent = JSON.parse(stored);
        } catch (e) { /* ignore */ }
        if (!Array.isArray(recent)) recent = [];
        // Remove if already present, then prepend
        recent = recent.filter(function (e) { return e !== emoji; });
        recent.unshift(emoji);
        if (recent.length > MAX_RECENT) recent = recent.slice(0, MAX_RECENT);
        try { localStorage.setItem(RECENT_KEY, JSON.stringify(recent)); } catch (e) { /* ignore */ }
        CATEGORIES[0].emojis = recent;
    };

    // ─── Helpers ───

    QSEmojiPicker.prototype._handleDocClick = function (e) {
        if (this._el && !this._el.contains(e.target) &&
            this.triggerEl && !this.triggerEl.contains(e.target)) {
            this.close();
        }
    };

    QSEmojiPicker.prototype._escHtml = function (str) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    };

    // ─── Export ───
    root.QSEmojiPicker = QSEmojiPicker;

})(window);
