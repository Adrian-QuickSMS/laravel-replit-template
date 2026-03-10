# QSEmojiPicker — Component Architecture

Living reference for the shared emoji picker component. This document covers the design rationale, constructor API, permanent guardrails, and future-ready rules.

---

## Overview

`QSEmojiPicker` is a floating popover component designed to replace the separate Bootstrap modal emoji pickers across the QuickSMS platform. Once integrated (see `REPLIT_CHERRY_PICK_EMOJI_PICKER.md`), it will be used on the Send Message page, Inbox composer, RCS wizard (all pages), and Template Editor.

**Current state:** The component source files exist on branch `origin/claude/quicksms-security-performance-dr8sw` (commit `36da3616`). The pages currently use inline Bootstrap modal emoji pickers. Integration has not yet been applied to `main`.

### File Manifest

| File | Role |
|------|------|
| `public/js/emoji-picker.js` | `QSEmojiPicker` class — all logic |
| `public/css/emoji-picker.css` | Popover layout, grid, search, categories |
| `resources/views/quicksms/partials/emoji-picker.blade.php` | Blade partial with `@once` + `@push` |

---

## Constructor API

```javascript
var picker = new QSEmojiPicker({
    triggerEl: HTMLElement,   // The button that toggles the picker
    textareaEl: HTMLElement,  // The textarea to insert emojis into
    onInsert: Function        // Called after each emoji insertion (optional)
});
```

### Instance Methods

| Method | Purpose |
|--------|---------|
| `openFor(textareaEl)` | Opens the picker and redirects insertion to a different textarea. Used by the RCS wizard to target whichever RCS text field the user clicked from. |

### Global Instances

| Variable | Page | Purpose |
|----------|------|---------|
| `window.smsEmojiPicker` | Send Message, Template Editor | Primary picker bound to the SMS content textarea. Also used by `openRcsEmojiPicker()` and `openRcsButtonFieldEmoji()` in `rcs-wizard.js`. |
| `window.inboxEmojiPicker` | Inbox v2 | Picker bound to the inbox reply textarea. |

---

## Design Rationale

### Why Popover (Not Modal)

The old emoji pickers were Bootstrap modals. This caused z-index conflicts when the emoji picker was triggered from inside another modal (e.g., the RCS wizard modal). A popover positioned via `getBoundingClientRect()` avoids the stacking context entirely and works at any nesting depth.

### Why Vanilla JS (Not a Library)

The project uses vanilla JS loaded via `<script>` tags. Adding a library like emoji-mart would require a build step or a large CDN dependency. The custom component is ~665 lines, has zero dependencies, and includes SMS-specific features (Unicode encoding warning, recently-used tracking, keyword search with business/healthcare categories) that no off-the-shelf library provides.

### Why `@once` + `@push`

The emoji picker partial can be `@include`d from multiple Blade partials on the same page (e.g., `message-builder.blade.php` and `message-composer.blade.php` both include it). The `@once` directive ensures the CSS and JS assets are only injected once into the page, regardless of how many times the partial is included.

### Why Cursor-Position Insertion

Most quick emoji implementations append to the end of the textarea. `QSEmojiPicker` reads `selectionStart` / `selectionEnd` and inserts at the cursor position, preserving any existing selection behavior. This is a deliberate UX decision.

---

## Key Implementation Details

### Emoji Categories (12)

Recently Used, Commonly Used, Smileys, Gestures, Hearts/Symbols, Healthcare, Business, Travel, Food, Nature, Numbers, Flags.

### Keyword Search

342 keyword mappings allow natural-language search (e.g., "happy" finds multiple smiley emojis, "hospital" finds healthcare emojis). Keywords are curated for UK business SMS use cases.

### Recently Used Storage

- **localStorage key:** `qs_emoji_recent`
- **Max items:** 16
- All pages share this storage, so recently-used emojis persist across Send Message, Inbox, and Templates.

### Z-Index

The picker uses `z-index: 1070`, positioned above Bootstrap modals (`1050`) and below tooltips. This is deliberate — do not change it without testing every modal context.

### Legacy Function Stubs

The following global functions exist as thin wrappers for backwards compatibility:

- `insertEmoji(emoji)`
- `insertEmojiFromModal(emoji)`
- `toggleEmojiPicker()`
- `openEmojiPicker()`

These exist because other branches or inline `onclick` handlers may reference them. Do not remove them.

---

## Permanent Guardrails

These rules apply at all times, not just during integration:

1. **Do not convert the popover to a Bootstrap modal.** The modals were removed because they broke inside modal contexts.
2. **Do not add a third-party emoji picker library.** The custom component is intentional.
3. **Do not change the z-index (1070).** It is positioned relative to Bootstrap's modal stack.
4. **Do not merge the 3 files into one.** CSS, JS, and Blade partial are separate by design.
5. **Do not change the `@once` directive to `@if` or remove it.** It prevents duplicate asset loading.
6. **Do not change the `QSEmojiPicker` constructor API** (`triggerEl`, `textareaEl`, `onInsert`). All integration points depend on this interface.
7. **Do not remove `window.smsEmojiPicker` or `window.inboxEmojiPicker` global assignments.** The RCS wizard and legacy stubs reference them.
8. **Do not move or rename `emoji-picker.js` / `emoji-picker.css`.** Other pages reference them by exact path.
9. **Do not change the localStorage key (`qs_emoji_recent`) or max-recent limit (16).** All pages share this storage.
10. **Do not add animations or transitions to the popover open/close.** It uses `display:none` / `display:flex` for instant response.
11. **Do not change the popover positioning logic.** It uses `getBoundingClientRect()` and accounts for viewport edges.
12. **Do not introduce a JS test framework as part of emoji picker work.** The project does not currently use one. If a test framework is added project-wide in the future, emoji picker tests are welcome.

---

## Future-Ready Rules

These items are intentionally excluded from v1. They can be revisited in future versions:

### Keyboard Navigation

The picker is currently mouse/touch only. Adding keyboard navigation (arrow keys, Enter to select, Escape to close) is a valid accessibility improvement but is out of scope for the initial integration. When adding it, ensure it does not break the existing `openFor()` flow or interfere with the textarea's own keyboard handling.

### Emoji List Updates

The emoji list, categories, and keyword mappings are curated for business SMS. They should not be modified as part of an integration or cherry-pick task. However, they can be updated in a dedicated task — for example, adding new Unicode 16 emojis or expanding keyword coverage. When doing so, test that the search index still performs well with the expanded dataset.

### Legacy Stub Removal

The legacy function stubs (`insertEmoji`, `toggleEmojiPicker`, etc.) can be removed once all branches have been merged and no inline `onclick` handlers reference them. This requires an audit of all Blade templates and JS files across all active branches.

### Cache-Busting Versions

Asset URLs include a `?v=20260310` cache-busting parameter. When modifying the CSS or JS files, update the version string in all references (the Blade partial and any direct `<link>`/`<script>` tags in views). Do not remove the version parameter entirely — it ensures browsers load updated assets.
