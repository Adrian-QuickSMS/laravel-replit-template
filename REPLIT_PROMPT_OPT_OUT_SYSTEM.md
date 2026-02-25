# Replit Prompt: Campaign Opt-Out System — Wire UI to Backend

## Overview

This commit adds a complete **campaign opt-out system** with two mechanisms: **Reply Opt-Out** (subscriber replies a keyword) and **URL Click Opt-Out** (subscriber clicks a unique link). The backend, migration, models, services, controllers, routes, and landing pages are all done. **This prompt is for wiring the frontend (Send Message UI) to the new backend API endpoints.**

### What Was Built (Backend — Already Complete)

| Component | Description |
|-----------|-------------|
| **Migration** | `2026_02_25_000002_add_campaign_opt_out_fields.php` — adds 7 opt-out columns to `campaigns`, creates `campaign_opt_out_urls` table, adds partial unique index for in-flight keyword constraint |
| **CampaignOptOutUrl model** | `app/Models/CampaignOptOutUrl.php` — unique 8-char base62 token per recipient, `https://qout.uk/{token}` (25 chars fixed), 30-day TTL |
| **OptOutService** | `app/Services/OptOutService.php` — keyword validation, URL generation, inbound processing, click processing |
| **OptOutLandingController** | `app/Http/Controllers/OptOutLandingController.php` — public unbranded landing page |
| **Blade views** | `resources/views/optout/landing.blade.php`, `confirmed.blade.php`, `invalid.blade.php` |
| **Campaign model** | Updated with opt-out fields in `$fillable`, `$casts`, relationships, `toPortalArray()` |
| **CampaignApiController** | 4 new endpoints + opt-out validation in store/update |
| **CampaignService** | `create()` now passes opt-out fields through |
| **ResolveRecipientContentJob** | Generates unique opt-out URLs per recipient + appends opt-out text during content resolution |
| **HandleInboundSms** | Opt-out keyword matching runs as Step 1 (highest priority) before auto-reply rules |

---

## New API Endpoints

All endpoints are under `api/campaigns` with `customer.auth` middleware.

### 1. `GET /api/campaigns/opt-out-numbers`

Returns VMNs, shortcodes, and keywords available to the current user for opt-out reply configuration.

**Response:**
```json
{
  "data": {
    "vmns": [
      {
        "id": "uuid",
        "number": "+447700900123",
        "friendly_name": "Main VMN",
        "country_iso": "GB",
        "type": "vmn",
        "is_dedicated": true
      }
    ],
    "shortcodes": [
      {
        "id": "uuid",
        "number": "60777",
        "friendly_name": "Shared SC",
        "country_iso": "GB",
        "type": "shared_shortcode",
        "is_dedicated": false,
        "keywords": [
          { "id": "uuid", "keyword": "PROMO" },
          { "id": "uuid", "keyword": "SALE" }
        ]
      }
    ]
  }
}
```

### 2. `POST /api/campaigns/validate-opt-out-keyword`

Validates a keyword is available for use on the selected number.

**Request:**
```json
{
  "keyword": "UNSUB",
  "number_id": "uuid",
  "campaign_id": "uuid (optional, for edit mode exclusion)"
}
```

**Success Response (200):**
```json
{ "valid": true, "keyword": "UNSUB" }
```

**Error Response (422):**
```json
{ "valid": false, "error": "Keyword 'UNSUB' is already in use by an active campaign on this number." }
```

### 3. `GET /api/campaigns/opt-out-keywords/{numberId}`

Returns available keywords for a shared shortcode (purchased by this account, not in use by in-flight campaigns). Only relevant when the selected number is a shared shortcode.

**Response:**
```json
{ "data": ["PROMO", "SALE", "DEALS"] }
```

### 4. `POST /api/campaigns/suggest-opt-out-text`

Generates the suggested opt-out text string for the selected keyword + number combination.

**Request:**
```json
{
  "keyword": "UNSUB",
  "number_id": "uuid"
}
```

**Response:**
```json
{
  "text": "Reply UNSUB to +447700900123 to opt out",
  "char_count": 40,
  "opt_out_url_char_count": 25,
  "opt_out_url_preview": "https://qout.uk/XXXXXXXX"
}
```

---

## Campaign Object — New Fields in API Response

The campaign `toPortalArray()` (returned by GET/POST/PUT) now includes:

```json
{
  "opt_out_enabled": false,
  "opt_out_method": null,
  "opt_out_number_id": null,
  "opt_out_number": null,
  "opt_out_keyword": null,
  "opt_out_text": null,
  "opt_out_list_id": null,
  "opt_out_url_enabled": false
}
```

When saving a campaign (POST or PUT), send these fields:

```json
{
  "opt_out_enabled": true,
  "opt_out_method": "reply",
  "opt_out_number_id": "uuid-of-purchased-number",
  "opt_out_keyword": "UNSUB",
  "opt_out_text": "Reply UNSUB to +447700900123 to opt out",
  "opt_out_list_id": "uuid-of-opt-out-list",
  "opt_out_url_enabled": false
}
```

**Valid values for `opt_out_method`:** `"reply"`, `"url"`, `"both"`

---

## UI Changes Required

The Send Message page (`resources/views/quicksms/messages/send-message.blade.php`) has an opt-out section that needs to be wired to the backend. Based on the user's design, there are two opt-out options — "By Reply" (red circle in the design) and "By URL/Click" (separate option).

### Step 1: Opt-Out Toggle

Add a toggle/checkbox to enable opt-out for this campaign. When enabled, show the opt-out method selector.

- **State field:** `opt_out_enabled` (boolean)
- When toggled ON, show the method selector
- When toggled OFF, hide everything and clear opt-out fields

### Step 2: Opt-Out Method Selector

Three options: **Reply**, **URL**, or **Both**.

- **State field:** `opt_out_method` (`"reply"`, `"url"`, `"both"`)

### Step 3: Reply Opt-Out Configuration (shown when method is "reply" or "both")

This section needs three sub-components:

#### 3a. Number Selector (Red Circle in design)

Fetch numbers from `GET /api/campaigns/opt-out-numbers` on component mount.

Display a dropdown/selector showing:
- VMNs (dedicated virtual mobile numbers) — grouped
- Shortcodes (dedicated and shared) — grouped, with their keywords listed

When a number is selected:
- **State field:** `opt_out_number_id` = selected number's `id`
- If number is a **shared shortcode** (`is_dedicated === false`):
  - Fetch available keywords via `GET /api/campaigns/opt-out-keywords/{numberId}`
  - Show a dropdown of available keywords (only those returned — already filtered for in-flight conflicts)
  - User must pick one from the list
- If number is a **dedicated VMN or dedicated shortcode** (`is_dedicated === true`):
  - Show a free-text input for the keyword
  - Validate: 4-10 characters, alphanumeric only, no spaces or special characters
  - Live-validate via `POST /api/campaigns/validate-opt-out-keyword` on blur/change

#### 3b. Keyword Input (Blue Circle in design)

- **State field:** `opt_out_keyword` (string, 4-10 chars, alphanumeric)
- Input should auto-uppercase the keyword
- Show validation errors inline (from the validate endpoint)
- Keyword must be unique per number across in-flight campaigns

#### 3c. Opt-Out Text Preview

When both number and keyword are set, auto-generate the opt-out text:
- Call `POST /api/campaigns/suggest-opt-out-text` with `{ keyword, number_id }`
- Display the generated text (e.g., "Reply UNSUB to +447700900123 to opt out")
- **State field:** `opt_out_text` (string, max 500 chars)
- Allow the user to edit this text (it's a suggestion, they can customise)
- Show character count impact: "This adds {char_count} characters to each message"
- **Important:** This text is appended to the end of every SMS during content resolution. The segment count on the confirm page already accounts for it.

### Step 4: URL Opt-Out Configuration (shown when method is "url" or "both")

Simple toggle section:

- **State field:** `opt_out_url_enabled` (boolean) — set to `true`
- Display informational text: "Each recipient will receive a unique unsubscribe link (25 characters) appended to their message."
- Show the URL format preview: `https://qout.uk/XXXXXXXX`
- Show character count impact: "This adds 26 characters to each message" (newline + 25 char URL)
- No other configuration needed — URLs are auto-generated during content resolution

### Step 5: Opt-Out List Selector

When opt-out is enabled (any method), show an opt-out list selector:

- Fetch opt-out lists from the existing `GET /api/opt-out-lists` endpoint
- Let the user select which opt-out list to store unsubscribes in
- **State field:** `opt_out_list_id` (UUID)
- Show a "Create new list" option that creates a list name inline
- **Important:** Opt-outs are recorded in BOTH the selected list AND the account's master opt-out list. You can mention this as a note: "Opt-outs will also be added to your master opt-out list automatically."

### Step 6: Save Opt-Out Fields with Campaign

When saving the campaign (store or update), include all opt-out fields in the payload:

```javascript
const payload = {
  // ... existing campaign fields ...
  opt_out_enabled: optOutEnabled,
  opt_out_method: optOutMethod,         // "reply", "url", or "both"
  opt_out_number_id: selectedNumberId,  // UUID or null
  opt_out_keyword: keyword,             // "UNSUB" etc. or null
  opt_out_text: optOutText,             // Generated/edited text or null
  opt_out_list_id: selectedListId,      // UUID or null
  opt_out_url_enabled: urlOptOutEnabled, // true/false
};
```

### Step 7: Message Preview — Show Opt-Out Impact

In the message preview/character counter section:

1. If **reply opt-out** is configured, show the opt-out text appended to the preview message
2. If **URL opt-out** is enabled, show `https://qout.uk/XXXXXXXX` appended to the preview
3. Update the segment count calculation to include these additions
4. The `char_count` and `opt_out_url_char_count` from the suggest endpoint help with this

Example preview:
```
Hello {{first_name}}, your appointment is tomorrow at 10am.
Reply UNSUB to +447700900123 to opt out
https://qout.uk/XXXXXXXX
```

### Step 8: Confirm Page — Accurate Cost Includes Opt-Out

No changes needed on the confirm page. The existing preparation flow (`POST /api/campaigns/{id}/prepare` → poll `GET /api/campaigns/{id}/preparation-status`) already resolves content with opt-out text and URLs injected, so segment counts and cost estimates on the confirm page are already accurate.

---

## Validation Rules Summary

| Field | Rules |
|-------|-------|
| `opt_out_enabled` | boolean |
| `opt_out_method` | `"reply"`, `"url"`, or `"both"` (required when enabled) |
| `opt_out_number_id` | UUID, required when method is `"reply"` or `"both"` |
| `opt_out_keyword` | 4-10 chars, alphanumeric only, required when method is `"reply"` or `"both"` |
| `opt_out_text` | string, max 500 chars, auto-generated but editable |
| `opt_out_list_id` | UUID, required when opt-out is enabled |
| `opt_out_url_enabled` | boolean, should be `true` when method is `"url"` or `"both"` |

### Frontend Validation

Before calling the backend validate endpoint:
- Keyword: regex `/^[A-Za-z0-9]{4,10}$/`
- Number must be selected before keyword validation
- List must be selected when opt-out is enabled

### Conditional Visibility

| Method | Show Number Selector | Show Keyword Input | Show Opt-Out Text | Show URL Toggle |
|--------|---------------------|--------------------|-------------------|-----------------|
| `reply` | Yes | Yes | Yes | No |
| `url` | No | No | No | Yes (auto-enabled) |
| `both` | Yes | Yes | Yes | Yes (auto-enabled) |

---

## Shared Shortcode vs Dedicated Number — Keyword Behaviour

This is a critical UX distinction:

### Dedicated VMN or Dedicated Shortcode (`is_dedicated === true`)
- User types any keyword they want (free-text input)
- Must be 4-10 chars, alphanumeric
- Validated against in-flight campaigns on the same number
- Example: User types "UNSUB" → validated → accepted

### Shared Shortcode (`is_dedicated === false`)
- User can ONLY pick from their purchased keywords on that shortcode
- Displayed as a dropdown, NOT a free-text input
- Keywords already filtered for in-flight conflicts
- The `keywords` array in the number object shows what they own
- Fetch available (non-in-flight) keywords from `GET /api/campaigns/opt-out-keywords/{numberId}`

---

## How It All Works End-to-End

1. **User creates campaign** → configures opt-out in Send Message form → saves campaign with opt-out fields
2. **User clicks "Continue"** → frontend calls `POST /api/campaigns/{id}/prepare`
3. **Backend resolves content** → `ResolveRecipientContentJob` appends opt-out reply text AND/OR generates unique opt-out URLs per recipient → stores in `resolved_content` with accurate segment counts
4. **Confirm page loads** → polls `GET /api/campaigns/{id}/preparation-status` → shows accurate cost (already includes opt-out text/URLs in segments)
5. **User sends** → messages go out with opt-out text/URLs baked into each message
6. **Subscriber replies keyword** → `HandleInboundSms` → `OptOutService::processInboundOptOut()` → creates `OptOutRecord` in designated list + master list
7. **Subscriber clicks URL** → `GET /o/{token}` → landing page → clicks "Unsubscribe" → `POST /o/{token}/confirm` → `OptOutService::processUrlOptOut()` → creates `OptOutRecord`

---

## File Reference

| File | Role |
|------|------|
| `resources/views/quicksms/messages/send-message.blade.php` | **Primary file to modify** — add opt-out UI section |
| `app/Http/Controllers/Api/CampaignApiController.php` | API endpoints (read-only reference) |
| `app/Services/OptOutService.php` | Business logic (read-only reference) |
| `app/Models/CampaignOptOutUrl.php` | Token/URL model (read-only reference) |
| `routes/web.php` | Route definitions (read-only reference) |

---

## Testing Checklist

- [ ] Toggle opt-out on → method selector appears
- [ ] Select "Reply" → number selector + keyword input + opt-out text appear
- [ ] Select a dedicated VMN → free-text keyword input appears
- [ ] Select a shared shortcode → keyword dropdown appears with purchased keywords
- [ ] Type invalid keyword (too short, special chars) → inline validation error
- [ ] Type valid keyword → validate endpoint confirms → green checkmark
- [ ] Opt-out text auto-generates when keyword + number set
- [ ] Opt-out text is editable
- [ ] Select "URL" → URL info section appears, number/keyword sections hidden
- [ ] Select "Both" → all sections appear
- [ ] Select opt-out list from dropdown
- [ ] Save campaign → verify opt-out fields in API response
- [ ] Edit campaign → opt-out fields pre-populated correctly
- [ ] Message preview shows appended opt-out text and/or URL placeholder
- [ ] Character/segment counter updates to include opt-out additions
- [ ] Prepare campaign → confirm page shows accurate cost including opt-out text/URLs
