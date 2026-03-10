# Manual Apply: Reusable Emoji Picker Component

## Why Manual Apply (Not Cherry-Pick)

The emoji picker was built on branch `origin/claude/quicksms-security-performance-dr8sw` in commit `36da3616`. However, the following shared files have diverged significantly on `main` since that commit was created:

- `public/js/inbox/composer.js` — carousel template loading rewritten
- `public/js/rcs-wizard.js` — multiple carousel/wizard fixes applied
- `resources/views/quicksms/inbox/index.blade.php` — restructured for v2 inbox
- `resources/views/quicksms/inbox/partials/composer.blade.php` — rewritten for v2
- `resources/views/quicksms/messages/send-message.blade.php` — updated

A cherry-pick will produce merge conflicts in all of these. Manual apply is cleaner.

---

## Anti-Drift Rules (Scoped to This Task)

1. **Only touch files listed in this document.** Do not open, edit, or rename anything else.
2. **No backend PHP changes.** No controllers, models, services, migrations, or routes.
3. **No new packages, npm dependencies, or CDN links.** The component is pure vanilla JS.
4. **Do not refactor or "clean up" any file you modify.** Only add the specified integration code.
5. **Do not convert the popover to a Bootstrap modal.** The old modals are being replaced.
6. **Do not add a third-party emoji picker library** (emoji-mart, picmo, etc.).
7. **Do not modify frozen modules:** Contact Book, Billing, Numbers, RCS Registration, Sub-Accounts, Dashboard, User Management, Sidebar, Layout, config files, `setup.sh`, `.replit`, `replit.nix`. (Note: the Send Message page is part of the messaging module, not the campaigns module — editing it is expected.)

---

## Step 1: Pull the 3 New Files from the Branch

These files do not exist on `main` yet. Pull them from the emoji picker commit:

```bash
git checkout origin/claude/quicksms-security-performance-dr8sw -- \
  public/css/emoji-picker.css \
  public/js/emoji-picker.js \
  resources/views/quicksms/partials/emoji-picker.blade.php
```

Verify they arrived:

```bash
ls -la public/css/emoji-picker.css public/js/emoji-picker.js resources/views/quicksms/partials/emoji-picker.blade.php
```

| File | Purpose | Lines |
|------|---------|-------|
| `public/css/emoji-picker.css` | Popover styling, grid, search bar, category tabs, Unicode warning | ~179 |
| `public/js/emoji-picker.js` | `QSEmojiPicker` class — search, 12 categories, recently-used, cursor-position insertion | ~665 |
| `resources/views/quicksms/partials/emoji-picker.blade.php` | Blade partial using `@once` + `@push` for one-per-page asset loading | ~23 |

Do not modify these files during this integration.

---

## Step 2: Wire Up Send Message Page

**File:** `resources/views/quicksms/messages/send-message.blade.php`

**2a.** Delete the old emoji modal (currently at ~line 981). Remove the entire `<div class="modal fade" id="emojiPickerModal">` block through its closing `</div>`. Replace with:

```blade
{{-- Emoji picker handled by shared QSEmojiPicker popover --}}
```

**2b.** Find the `@include('quicksms.partials.rcs-wizard-modal')` line. Add immediately after it:

```blade
@include('quicksms.partials.emoji-picker')
```

**2c.** Find the existing emoji button init (look for `emojiPickerBtn` in the `<script>` block). Replace:

```javascript
document.getElementById('emojiPickerBtn').addEventListener('click', function() {
    openEmojiPicker();
});
```

With:

```javascript
window.smsEmojiPicker = new QSEmojiPicker({
    triggerEl: document.getElementById('emojiPickerBtn'),
    textareaEl: document.getElementById('smsContent'),
    onInsert: function() { handleContentChange(); }
});
```

---

## Step 3: Wire Up Inbox v2

**File:** `resources/views/quicksms/inbox/index.blade.php`

**3a.** In the `@push('styles')` block, add:

```blade
<link rel="stylesheet" href="{{ asset('css/emoji-picker.css') }}?v=20260310">
```

**3b.** In the `@push('scripts')` block, add **before** the other inbox JS files:

```blade
<script src="{{ asset('js/emoji-picker.js') }}?v=20260310"></script>
```

---

## Step 4: Wire Up Inbox Composer

**File:** `resources/views/quicksms/inbox/partials/composer.blade.php`

**4a.** Delete the entire old emoji modal block (currently at ~line 102, `<div class="modal fade" id="inboxEmojiPickerModal">`). Remove through its closing `</div>`.

**File:** `public/js/inbox/composer.js`

**4b.** Find the `bindEmojiPicker()` function (~line 126). Replace its entire body with:

```javascript
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
    }
}
```

---

## Step 5: Wire Up RCS Wizard

**File:** `public/js/rcs-wizard.js`

**5a.** Find `openRcsEmojiPicker` (~line 2390). Replace with:

```javascript
function openRcsEmojiPicker(field) {
    rcsActiveTextField = field;
    var el = getRcsTextElement(field);
    if (window.smsEmojiPicker && el) {
        window.smsEmojiPicker.openFor(el);
    }
}
```

**5b.** Find `openRcsButtonFieldEmoji` (~line 2412). Replace with:

```javascript
function openRcsButtonFieldEmoji(fieldId) {
    rcsActiveTextField = fieldId;
    var el = getRcsTextElement(fieldId);
    if (window.smsEmojiPicker && el) {
        window.smsEmojiPicker.openFor(el);
    }
}
```

---

## Step 6: Wire Up Shared Partials

**File:** `resources/views/quicksms/partials/message-builder.blade.php`

Add at the very end of the file:

```blade
@include('quicksms.partials.emoji-picker')
```

**File:** `resources/views/quicksms/partials/message-composer.blade.php`

Add at the very end of the file:

```blade
@include('quicksms.partials.emoji-picker')
```

---

## Step 7: Wire Up Template Editor

**File:** `resources/views/quicksms/management/templates/create-step2.blade.php`

This page already includes `message-composer.blade.php` (line ~213), which loads the emoji picker assets via Step 6. No additional `<script>` or `<link>` tags are needed — the `@once` directive in the partial handles deduplication.

**7a.** Inside the `DOMContentLoaded` handler, add before `initChannelSelector()`:

```javascript
window.smsEmojiPicker = new QSEmojiPicker({
    triggerEl: document.getElementById('emojiPickerBtn'),
    textareaEl: document.getElementById('smsContent'),
    onInsert: function() { handleContentChange(); }
});
```

---

## Step 8: Verify

Test each page after wiring:

1. **Send Message** (`/messages/send`) — click emoji button, popover appears, emoji inserts at cursor, char count updates
2. **Inbox** (`/messages/inbox-v2`) — click emoji button in composer, popover appears, emoji inserts, char count updates
3. **RCS Wizard** (any page with RCS) — click emoji button inside RCS text fields, `smsEmojiPicker.openFor()` targets the correct field
4. **Template Editor** (`/management/templates/create`) — emoji button works on step 2
5. **Search** — type a keyword (e.g. "happy"), matching emojis filter correctly
6. **Recently used** — insert an emoji, close picker, reopen — it appears in "Recently Used"
7. **Multiple textareas** — on Send Message page, emoji inserts into the correct field (SMS vs RCS)
8. **Legacy stubs** — confirm `insertEmoji()`, `toggleEmojiPicker()` etc. still exist as thin wrappers in the codebase (do not remove them)
