# Replit Prompt: Wire Send Message UI to Campaign Backend

## Overview

A complete campaign management backend has been built on branch `claude/quicksms-security-performance-dr8sw`. Your job is to wire the existing frontend Blade views and JavaScript to these new backend API endpoints. **Do not rebuild anything that already exists.** The backend is fully functional — you are connecting the UI layer to it.

Pull the branch first:
```bash
git fetch origin claude/quicksms-security-performance-dr8sw
git checkout claude/quicksms-security-performance-dr8sw
php artisan migrate
```

This creates 4 new tables (`message_templates`, `campaigns`, `campaign_recipients`, `media_library`) and adds preparation tracking columns + per-recipient encoding tracking.

---

## What Was Built (Backend — Already Complete)

### Database Tables (5 migrations in `database/migrations/`)
| Table / Migration | Purpose |
|---|---|
| `message_templates` | Reusable SMS/RCS message templates with encoding detection, segment counting, placeholder extraction |
| `campaigns` | Campaign lifecycle (8-state machine) with preparation tracking (`preparation_status`, `preparation_progress`, `content_resolved_at`) |
| `campaign_recipients` | Per-recipient delivery tracking with contact data snapshots, per-recipient `encoding` and `segments`, batch processing, retry logic |
| `media_library` | RCS media uploads with MIME validation, dimensions, thumbnails |
| Alter migration | Adds `content_resolved_at`, `preparation_status/progress/error` to campaigns; adds `encoding` column + composite cost estimation index to campaign_recipients |

### Models (4 in `app/Models/`)
- `MessageTemplate` — tenant-scoped, encoding/segment/placeholder auto-calculation
- `Campaign` — state machine with validated transitions, preparation tracking fields, progress/delivery helpers
- `CampaignRecipient` — status lifecycle, merge field resolution, per-recipient encoding/segment storage, retry scheduling
- `MediaLibraryItem` — tenant-scoped media management

### Services (6 in `app/Services/Campaign/`)
- `CampaignService` — full orchestration including `prepareCampaign()` (async content resolution), accurate `estimateCost()` using per-recipient segments, `getPreparationStatus()` for polling
- `RecipientResolverService` — expands 5 source types (list, tag, individual, manual, csv), deduplication, opt-out filtering
- `BillingPreflightService` — `estimateCost()` (flat), `estimateCostPerSegmentGroup()` (accurate), balance checks, fund reservation
- `DeliveryService` — per-message send pipeline, gateway selection, DLR processing
- `PhoneNumberUtils` — E.164 normalization, country detection (80+ countries)
- `ResolverResult` — immutable DTO for recipient resolution results

### Queue Jobs (4 in `app/Jobs/`)
- `ResolveRecipientContentJob` — **NEW** — async merge field resolution + per-recipient encoding detection + segment calculation with progress tracking (queue: `campaigns`)
- `ProcessCampaignBatch` — processes a batch of recipients for delivery (queue: `campaigns`)
- `HandleDeliveryReceipt` — processes gateway DLR callbacks (queue: `dlr`)
- `ScheduledCampaignDispatcher` — checks for due scheduled campaigns every minute (queue: `scheduler`)

### API Controllers (2 in `app/Http/Controllers/Api/`)
- `CampaignApiController` — 20 endpoints for campaign CRUD + preparation + send operations
- `MessageTemplateApiController` — 7 endpoints for template CRUD + content analysis

All routes under `customer.auth` middleware with `throttle:60,1`.

---

## API Endpoint Reference

### Campaign API (`/api/campaigns`)

| Method | Endpoint | Controller Method | Purpose |
|--------|----------|-------------------|---------|
| `GET` | `/api/campaigns` | `index` | List campaigns (paginated, filterable by status/type/search) |
| `POST` | `/api/campaigns` | `store` | Create new campaign (draft) |
| `POST` | `/api/campaigns/field-statistics` | `fieldStatistics` | Get contact field length stats for early segment estimates |
| `GET` | `/api/campaigns/{id}` | `show` | Get single campaign with full details |
| `PUT` | `/api/campaigns/{id}` | `update` | Update draft campaign (invalidates resolved content) |
| `DELETE` | `/api/campaigns/{id}` | `destroy` | Soft delete campaign |
| `POST` | `/api/campaigns/{id}/prepare` | `prepare` | **PRIMARY FLOW** — resolve recipients + dispatch async content resolution |
| `GET` | `/api/campaigns/{id}/preparation-status` | `preparationStatus` | Poll progress + get accurate cost estimate when ready |
| `POST` | `/api/campaigns/{id}/apply-template` | `applyTemplate` | Apply a message template to campaign |
| `GET` | `/api/campaigns/{id}/recipients/preview` | `previewRecipients` | Preview recipient counts (dry run) |
| `POST` | `/api/campaigns/{id}/recipients/resolve` | `resolveRecipients` | Resolve and persist recipients (legacy — use prepare instead) |
| `GET` | `/api/campaigns/{id}/recipients` | `recipients` | List resolved recipients (paginated) |
| `GET` | `/api/campaigns/{id}/estimate-cost` | `estimateCost` | Get cost estimate (accurate if prepared, flat otherwise) |
| `GET` | `/api/campaigns/{id}/validate` | `validate_` | Validate campaign ready-to-send (dry run) |
| `POST` | `/api/campaigns/{id}/send` | `sendNow` | Send campaign immediately |
| `POST` | `/api/campaigns/{id}/schedule` | `schedule` | Schedule for future send |
| `POST` | `/api/campaigns/{id}/pause` | `pause` | Pause a sending campaign |
| `POST` | `/api/campaigns/{id}/resume` | `resume` | Resume a paused campaign |
| `POST` | `/api/campaigns/{id}/cancel` | `cancel` | Cancel a campaign |
| `POST` | `/api/campaigns/{id}/clone` | `clone` | Clone as new draft |

### Message Template API (`/api/message-templates`)

| Method | Endpoint | Controller Method | Purpose |
|--------|----------|-------------------|---------|
| `GET` | `/api/message-templates` | `index` | List templates (paginated, filterable) |
| `POST` | `/api/message-templates` | `store` | Create template |
| `GET` | `/api/message-templates/{id}` | `show` | Get single template |
| `PUT` | `/api/message-templates/{id}` | `update` | Update template |
| `DELETE` | `/api/message-templates/{id}` | `destroy` | Delete template |
| `POST` | `/api/message-templates/{id}/toggle-favourite` | `toggleFavourite` | Toggle favourite status |
| `POST` | `/api/message-templates/analyse-content` | `analyseContent` | Analyse encoding/segments/placeholders |

### Existing APIs Already Wired (use these — do NOT rebuild)

| Prefix | Controller | Notes |
|--------|-----------|-------|
| `/api/contacts` | `ContactBookApiController` | Contacts, bulk operations, timeline |
| `/api/contact-lists` | `ContactBookApiController` | Contact lists CRUD + members |
| `/api/tags` | `ContactBookApiController` | Tag CRUD |
| `/api/opt-out-lists` | `ContactBookApiController` | Opt-out list CRUD + records |
| `/api/sender-ids` | `SenderIdController` | Sender ID management |
| `/api/rcs-agents` | `RcsAgentController` | RCS agent management |
| `/api/account/pricing` | `QuickSMSController` | Account pricing tiers |

---

## CRITICAL: Per-Recipient Accurate Cost Estimation

### The Problem This Solves

When a campaign message contains placeholders like `{{first_name}}`, the actual message length varies per recipient after merge field resolution. Examples:

- "Hello {{first_name}}" → "Hello Jo" (10 chars, 1 GSM-7 segment) vs "Hello Christopher" (19 chars, still 1 segment)
- But at 152 chars + `{{first_name}}`: "Jo" stays 1 segment, "Christopher" (+9 = 161 chars) jumps to **2 segments — doubling cost**
- If a contact's name contains emoji/Chinese/non-GSM characters (e.g. "李明"), the **entire message flips to UCS-2 encoding** (70/67 char limit instead of 160/153) — potentially **quadrupling segment count**

### How the Backend Handles It

The backend resolves this via a **two-stage preparation flow**:

1. **`POST /api/campaigns/{id}/prepare`** — Resolves recipients synchronously (expands lists/tags, deduplicates, filters opt-outs), then dispatches an async background job (`ResolveRecipientContentJob`) that:
   - Substitutes `{{placeholder}}` tokens with each recipient's actual contact data
   - Detects per-recipient encoding (GSM-7 vs Unicode)
   - Calculates per-recipient segment count
   - Stores `resolved_content`, `encoding`, and `segments` on each `campaign_recipient` row
   - Tracks progress (0-100%) on the campaign's `preparation_progress` field

2. **`GET /api/campaigns/{id}/preparation-status`** — Returns:
   - While preparing: `{ "preparation_status": "preparing", "preparation_progress": 45 }`
   - When ready: includes `cost_estimate` (accurate, using per-recipient segments) and `segment_stats` (min/max/avg segments, unicode count, total segments)
   - On failure: `{ "preparation_status": "failed", "error": "..." }`

### Frontend MUST Use This Flow

The confirm page MUST NOT display cost until `preparation_status === 'ready'`. The cost estimate in the `preparation-status` response is the **accurate** figure based on actual per-recipient segment counts grouped by (country, segment_count).

---

## Security Protocols — CRITICAL

### 1. Authentication & Tenant Isolation
- All API routes use `customer.auth` middleware (`app/Http/Middleware/CustomerAuthenticate.php`)
- This middleware checks session variables: `customer_logged_in`, `customer_user_id`, `customer_tenant_id`
- It sets PostgreSQL tenant context: `SELECT set_config('app.current_tenant_id', ?, false)`
- All new models use **fail-closed global scopes** — if `session('customer_tenant_id')` is null, queries return `WHERE 1 = 0` (no data)
- PostgreSQL Row Level Security (RLS) policies enforce tenant isolation at the database level as a second layer
- **NEVER bypass global scopes** in frontend-facing code. The only place `withoutGlobalScope('tenant')` is used is in background jobs

### 2. CSRF Protection
- All fetch requests MUST include the CSRF token header
- Follow the established pattern in `public/js/contacts-service.js`:
```javascript
_headers: function() {
    return {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
    };
}
```

### 3. Rate Limiting
- All API routes have `throttle:60,1` (60 requests per minute per session)
- Handle 429 responses gracefully in the UI (show "Too many requests, please wait")
- Note: `preparation-status` polling should use reasonable intervals (2-3 seconds), not flood the API

### 4. Input Validation
- The backend validates all inputs server-side. Display validation errors returned in `422` responses
- Error format: `{ "status": "error", "message": "...", "errors": { "field": ["message"] } }`

### 5. Session Variables Available
| Key | Type | Purpose |
|-----|------|---------|
| `customer_tenant_id` | string (UUID) | Tenant ID for data isolation |
| `customer_user_id` | string | Current user ID |
| `customer_email` | string | Current user email |
| `customer_logged_in` | bool | Auth flag |

---

## Frontend Architecture — What Exists Today

### Blade Views That Need Wiring

#### 1. `resources/views/quicksms/messages/send-message.blade.php` (3,434 lines)
**Current state:** Large multi-step campaign creation form. Currently receives mock data from `QuickSMSController::sendMessage()` — mock `$templates`, `$lists`, `$tags`, `$opt_out_lists`, `$virtual_numbers`, `$optout_domains`. The "Continue" button calls `storeCampaignConfig()` which POSTs form data to session then redirects to confirm page.

**What needs to change:**
- Replace mock data in `QuickSMSController::sendMessage()` with real data from the database:
  - `$sender_ids` — already live (uses `$this->getApprovedSenderIds()`)
  - `$rcs_agents` — replace mock array with: `RcsAgent::where('status', 'approved')->get()->toArray()`
  - `$templates` — replace mock array with: `MessageTemplate::where('status', 'active')->get()->map->toPortalArray()`
  - `$lists` — fetch from `ContactList` model or `GET /api/contact-lists`
  - `$tags` — fetch from `Tag` model or `GET /api/tags`
  - `$opt_out_lists` — fetch from `OptOutList` model or `GET /api/opt-out-lists`
  - `$virtual_numbers` — leave as mock for now
  - `$optout_domains` — leave as mock for now

- **Campaign creation flow must change from session-based to API + preparation:**

  **Old flow:**
  ```
  Form → POST /messages/store-campaign-config (session) → redirect /messages/confirm
  ```

  **New flow:**
  ```
  Form → POST /api/campaigns (create draft)
       → POST /api/campaigns/{id}/prepare (resolve recipients + start async content resolution)
       → store campaign_id in session
       → redirect to /messages/confirm?campaign_id={id}
  ```

- **Real-time content analysis with placeholder-aware segment estimation:**
  Wire the message textarea to call `POST /api/message-templates/analyse-content` on input (debounced, 300ms) for basic encoding/segment display. BUT if the message contains `{{placeholders}}`, ALSO call `POST /api/campaigns/field-statistics` with the selected recipient source IDs to get average/max field lengths, then compute a segment range estimate client-side:

  ```javascript
  // field-statistics response:
  {
      "data": {
          "avg_first_name_len": 6.2,
          "max_first_name_len": 28,
          "avg_last_name_len": 7.8,
          "max_last_name_len": 35,
          "avg_email_len": 22.5,
          "max_email_len": 64,
          "total_contacts": 4800
      }
  }
  ```

  Use these to replace `{{first_name}}` with avg/max lengths and re-compute segments:
  ```javascript
  // Show: "Estimated: 1-2 segments (varies by recipient data)"
  var avgText = message.replace(/\{\{first_name\}\}/g, 'X'.repeat(avgFirstNameLen));
  var maxText = message.replace(/\{\{first_name\}\}/g, 'X'.repeat(maxFirstNameLen));
  var avgSegments = calculateSegments(avgText); // use GSM-7/Unicode thresholds
  var maxSegments = calculateSegments(maxText);
  ```

- **Template selection:** When user picks a template, call `POST /api/campaigns/{id}/apply-template` with `{ "template_id": "uuid" }` to copy template content into the campaign

- **Recipient preview:** After selecting recipient sources, call `GET /api/campaigns/{id}/recipients/preview` to show counts before resolving

#### 2. `resources/views/quicksms/messages/confirm-campaign.blade.php`
**Current state:** Reads campaign data from `session('campaign_config')`. Displays summary cards. Has "Send Now" and "Schedule" buttons that are currently stubs.

**What needs to change — THIS IS THE BIGGEST CHANGE:**
- Accept `campaign_id` from query parameter
- On page load, call `GET /api/campaigns/{id}/preparation-status`
- **Show a progress bar while `preparation_status === 'preparing'`** — poll every 2-3 seconds
- Once `preparation_status === 'ready'`, display the accurate cost from the response

**Preparation status response when ready:**
```json
{
    "data": {
        "preparation_status": "ready",
        "preparation_progress": 100,
        "segment_stats": {
            "min_segments": 1,
            "max_segments": 3,
            "avg_segments": 1.12,
            "unicode_count": 23,
            "total_count": 4800,
            "total_segments": 5376
        },
        "cost_estimate": {
            "total_cost": "172.0320",
            "currency": "GBP",
            "per_country_costs": {
                "GB": {
                    "country_iso": "GB",
                    "recipient_count": 3500,
                    "total_cost": "115.3600",
                    "currency": "GBP",
                    "price_source": "admin_override",
                    "unit_price": "0.032000",
                    "cost_per_message": "0.032960",
                    "segments": null,
                    "segment_breakdown": [
                        { "segments": 1, "count": 3200, "cost_per_message": "0.032000" },
                        { "segments": 2, "count": 280, "cost_per_message": "0.064000" },
                        { "segments": 3, "count": 20, "cost_per_message": "0.096000" }
                    ]
                },
                "US": {
                    "country_iso": "US",
                    "recipient_count": 1300,
                    "total_cost": "56.6720",
                    "segment_breakdown": [
                        { "segments": 1, "count": 1250, "cost_per_message": "0.042000" },
                        { "segments": 2, "count": 50, "cost_per_message": "0.084000" }
                    ]
                }
            },
            "available_balance": "500.0000",
            "has_sufficient_balance": true,
            "is_postpay": false
        }
    }
}
```

**Confirm page cost display should show:**
```
Total Cost: £172.03
Recipients: 4,800 | Total Segments: 5,376 | Avg: 1.12 segments/message

By Country:
  🇬🇧 GB — 3,500 recipients — £115.36
      3,200 × 1 segment = £102.40
        280 × 2 segments = £17.92
         20 × 3 segments = £1.92  ← (contains non-GSM characters)
  🇺🇸 US — 1,300 recipients — £56.67
      1,250 × 1 segment = £52.50
         50 × 2 segments = £4.20

⚠️ 23 recipients contain non-GSM characters — messages sent as Unicode (higher segment count)

Balance: £500.00 | After send: £327.97
```

- Wire "Send Now" button to `POST /api/campaigns/{id}/send`
- Wire "Schedule" button to `POST /api/campaigns/{id}/schedule`
- Call `GET /api/campaigns/{id}/validate` before enabling send buttons
- Handle insufficient balance errors (422 with billing error)

#### 3. `resources/views/quicksms/messages/campaign-history.blade.php`
**Current state:** Uses hardcoded mock data array (16 fake campaigns in `QuickSMSController::campaignHistory()`). Has TODO comments saying "Replace with: GET /api/campaigns?page=X&limit=Y&filters=Z".

**What needs to change:**
- Replace ALL mock data with live API calls
- Remove the mock `$campaigns` array from `QuickSMSController::campaignHistory()`
- Load campaigns via JavaScript on page load: `GET /api/campaigns?page=1&per_page=25`
- Wire search/filter/pagination to make new API calls and re-render
- Wire action buttons:
  - "Edit" → navigate to `/messages/send?campaign_id={id}`
  - "Clone" → `POST /api/campaigns/{id}/clone` then navigate to the new draft
  - "Cancel" → `POST /api/campaigns/{id}/cancel` with confirmation dialog
  - "Pause/Resume" → `POST /api/campaigns/{id}/pause` or `/resume`
  - "View Detail" → expand row or navigate to detail view with `GET /api/campaigns/{id}`
- Add polling for active campaigns: refresh every 10 seconds for campaigns in `sending` status

#### 4. Template Management Views (`resources/views/quicksms/management/templates/`)
**What needs to change:**
- Wire template list to `GET /api/message-templates`
- Wire create/edit forms to `POST` / `PUT /api/message-templates/{id}`
- Wire the content textarea to `POST /api/message-templates/analyse-content` (debounced) for live encoding/segment preview
- Wire delete to `DELETE /api/message-templates/{id}`
- Wire favourite toggle to `POST /api/message-templates/{id}/toggle-favourite`

---

## JavaScript Service Layer Pattern

Follow the established `ContactsService` pattern from `public/js/contacts-service.js`. Create two new service files:

### Create: `public/js/campaign-service.js`

```javascript
/**
 * CampaignService
 * Backend-connected abstraction layer for Campaign Management
 * Wired to /api/campaigns/* endpoints (CampaignApiController)
 */
(function(window) {
    'use strict';

    var CampaignService = {
        config: {
            baseUrl: '/api/campaigns'
        },

        _headers: function() {
            return {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            };
        },

        _handleResponse: function(response) {
            if (response.status === 422) {
                return response.json().then(function(err) {
                    var error = new Error(err.message || 'Validation failed');
                    error.validationErrors = err.errors || {};
                    throw error;
                });
            }
            if (!response.ok) {
                return response.json().then(function(err) {
                    throw new Error(err.message || 'Request failed: ' + response.status);
                });
            }
            return response.json();
        },

        // CRUD
        list: function(params) {
            var qs = new URLSearchParams(params || {}).toString();
            return fetch(this.config.baseUrl + (qs ? '?' + qs : ''), {
                headers: this._headers()
            }).then(this._handleResponse);
        },

        create: function(data) {
            return fetch(this.config.baseUrl, {
                method: 'POST',
                headers: this._headers(),
                body: JSON.stringify(data)
            }).then(this._handleResponse);
        },

        get: function(id) {
            return fetch(this.config.baseUrl + '/' + id, {
                headers: this._headers()
            }).then(this._handleResponse);
        },

        update: function(id, data) {
            return fetch(this.config.baseUrl + '/' + id, {
                method: 'PUT',
                headers: this._headers(),
                body: JSON.stringify(data)
            }).then(this._handleResponse);
        },

        delete: function(id) {
            return fetch(this.config.baseUrl + '/' + id, {
                method: 'DELETE',
                headers: this._headers()
            }).then(this._handleResponse);
        },

        // ==========================================
        // PREPARATION (the primary "Continue" flow)
        // ==========================================

        /**
         * Prepare a campaign: resolve recipients + start async content resolution.
         * Returns immediately. Poll preparationStatus() until ready.
         */
        prepare: function(campaignId) {
            return fetch(this.config.baseUrl + '/' + campaignId + '/prepare', {
                method: 'POST',
                headers: this._headers()
            }).then(this._handleResponse);
        },

        /**
         * Poll preparation progress. Returns preparation_status, progress,
         * and when ready: cost_estimate + segment_stats.
         */
        preparationStatus: function(campaignId) {
            return fetch(this.config.baseUrl + '/' + campaignId + '/preparation-status', {
                headers: this._headers()
            }).then(this._handleResponse);
        },

        /**
         * Get field length statistics for early segment estimation.
         * Call with selected source IDs to scope to those contacts.
         */
        fieldStatistics: function(data) {
            return fetch(this.config.baseUrl + '/field-statistics', {
                method: 'POST',
                headers: this._headers(),
                body: JSON.stringify(data || {})
            }).then(this._handleResponse);
        },

        /**
         * Poll preparation status until ready, resolving with the final result.
         * Shows progress via optional callback.
         */
        waitForPreparation: function(campaignId, onProgress) {
            var self = this;
            return new Promise(function(resolve, reject) {
                function poll() {
                    self.preparationStatus(campaignId).then(function(result) {
                        var data = result.data;
                        if (onProgress) onProgress(data.preparation_progress, data.preparation_status);

                        if (data.preparation_status === 'ready') {
                            resolve(data);
                        } else if (data.preparation_status === 'failed') {
                            reject(new Error(data.error || 'Preparation failed'));
                        } else {
                            setTimeout(poll, 2500); // Poll every 2.5 seconds
                        }
                    }).catch(reject);
                }
                poll();
            });
        },

        // Template application
        applyTemplate: function(campaignId, templateId) {
            return fetch(this.config.baseUrl + '/' + campaignId + '/apply-template', {
                method: 'POST',
                headers: this._headers(),
                body: JSON.stringify({ template_id: templateId })
            }).then(this._handleResponse);
        },

        // Recipients
        previewRecipients: function(campaignId) {
            return fetch(this.config.baseUrl + '/' + campaignId + '/recipients/preview', {
                headers: this._headers()
            }).then(this._handleResponse);
        },

        listRecipients: function(campaignId, params) {
            var qs = new URLSearchParams(params || {}).toString();
            return fetch(this.config.baseUrl + '/' + campaignId + '/recipients' + (qs ? '?' + qs : ''), {
                headers: this._headers()
            }).then(this._handleResponse);
        },

        // Cost
        estimateCost: function(campaignId) {
            return fetch(this.config.baseUrl + '/' + campaignId + '/estimate-cost', {
                headers: this._headers()
            }).then(this._handleResponse);
        },

        // Validation
        validate: function(campaignId) {
            return fetch(this.config.baseUrl + '/' + campaignId + '/validate', {
                headers: this._headers()
            }).then(this._handleResponse);
        },

        // Send operations
        sendNow: function(campaignId) {
            return fetch(this.config.baseUrl + '/' + campaignId + '/send', {
                method: 'POST',
                headers: this._headers()
            }).then(this._handleResponse);
        },

        schedule: function(campaignId, scheduledAt, timezone) {
            return fetch(this.config.baseUrl + '/' + campaignId + '/schedule', {
                method: 'POST',
                headers: this._headers(),
                body: JSON.stringify({ scheduled_at: scheduledAt, timezone: timezone })
            }).then(this._handleResponse);
        },

        pause: function(campaignId) {
            return fetch(this.config.baseUrl + '/' + campaignId + '/pause', {
                method: 'POST',
                headers: this._headers()
            }).then(this._handleResponse);
        },

        resume: function(campaignId) {
            return fetch(this.config.baseUrl + '/' + campaignId + '/resume', {
                method: 'POST',
                headers: this._headers()
            }).then(this._handleResponse);
        },

        cancel: function(campaignId) {
            return fetch(this.config.baseUrl + '/' + campaignId + '/cancel', {
                method: 'POST',
                headers: this._headers()
            }).then(this._handleResponse);
        },

        clone: function(campaignId, newName) {
            return fetch(this.config.baseUrl + '/' + campaignId + '/clone', {
                method: 'POST',
                headers: this._headers(),
                body: JSON.stringify({ name: newName })
            }).then(this._handleResponse);
        }
    };

    window.CampaignService = CampaignService;
})(window);
```

### Create: `public/js/template-service.js`

```javascript
/**
 * TemplateService
 * Backend-connected abstraction layer for Message Template Management
 * Wired to /api/message-templates/* endpoints (MessageTemplateApiController)
 */
(function(window) {
    'use strict';

    var TemplateService = {
        config: {
            baseUrl: '/api/message-templates'
        },

        _headers: function() {
            return {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            };
        },

        _handleResponse: function(response) {
            if (!response.ok) {
                return response.json().then(function(err) {
                    throw new Error(err.message || 'Request failed: ' + response.status);
                });
            }
            return response.json();
        },

        list: function(params) {
            var qs = new URLSearchParams(params || {}).toString();
            return fetch(this.config.baseUrl + (qs ? '?' + qs : ''), {
                headers: this._headers()
            }).then(this._handleResponse);
        },

        create: function(data) {
            return fetch(this.config.baseUrl, {
                method: 'POST',
                headers: this._headers(),
                body: JSON.stringify(data)
            }).then(this._handleResponse);
        },

        get: function(id) {
            return fetch(this.config.baseUrl + '/' + id, {
                headers: this._headers()
            }).then(this._handleResponse);
        },

        update: function(id, data) {
            return fetch(this.config.baseUrl + '/' + id, {
                method: 'PUT',
                headers: this._headers(),
                body: JSON.stringify(data)
            }).then(this._handleResponse);
        },

        delete: function(id) {
            return fetch(this.config.baseUrl + '/' + id, {
                method: 'DELETE',
                headers: this._headers()
            }).then(this._handleResponse);
        },

        toggleFavourite: function(id) {
            return fetch(this.config.baseUrl + '/' + id + '/toggle-favourite', {
                method: 'POST',
                headers: this._headers()
            }).then(this._handleResponse);
        },

        analyseContent: function(content) {
            return fetch(this.config.baseUrl + '/analyse-content', {
                method: 'POST',
                headers: this._headers(),
                body: JSON.stringify({ content: content })
            }).then(this._handleResponse);
        }
    };

    window.TemplateService = TemplateService;
})(window);
```

Include these in `resources/views/layouts/quicksms.blade.php` before `</body>`:
```html
<script src="{{ asset('js/campaign-service.js') }}"></script>
<script src="{{ asset('js/template-service.js') }}"></script>
```

---

## The Primary "Continue" Button Flow (send-message.blade.php)

This is the most important wiring. The "Continue" button handler must be rewritten:

```javascript
async function handleContinue() {
    var continueBtn = document.getElementById('continueBtn');
    continueBtn.disabled = true;
    continueBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Creating campaign...';

    try {
        // 1. Create the campaign draft
        var campaignData = {
            name: document.getElementById('campaignName').value,
            type: channelMap[selectedChannel],
            message_content: document.getElementById('messageContent').value,
            sender_id_id: selectedSenderId,
            rcs_agent_id: selectedRcsAgent || null,
            rcs_content: rcsContentData || null,
            message_template_id: selectedTemplateId || null,
            recipient_sources: buildRecipientSources(),
            scheduled_at: scheduledTime !== 'now' ? scheduledTime : null,
            timezone: selectedTimezone || null
        };

        var createResult = await CampaignService.create(campaignData);
        var campaignId = createResult.data.id;

        // 2. Prepare campaign (resolve recipients + start content resolution)
        continueBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Resolving recipients...';
        await CampaignService.prepare(campaignId);

        // 3. Store campaign ID in session for confirm page
        await fetch('/messages/store-campaign-config', {
            method: 'POST',
            headers: CampaignService._headers(),
            body: JSON.stringify({ campaign_id: campaignId })
        });

        // 4. Redirect to confirm page — it will poll preparation status
        window.location.href = '/messages/confirm?campaign_id=' + campaignId;

    } catch (error) {
        continueBtn.disabled = false;
        continueBtn.innerHTML = 'Continue';
        if (error.validationErrors) {
            displayValidationErrors(error.validationErrors);
        } else {
            showAlert('error', error.message);
        }
    }
}
```

---

## The Confirm Page Preparation Polling (confirm-campaign.blade.php)

The confirm page MUST poll for preparation completion before showing cost:

```javascript
document.addEventListener('DOMContentLoaded', async function() {
    var campaignId = new URLSearchParams(window.location.search).get('campaign_id');
    if (!campaignId) {
        showAlert('error', 'No campaign ID provided');
        return;
    }

    // Show loading state
    showPreparationProgress(0, 'Preparing campaign...');

    try {
        // Wait for preparation to complete (polls every 2.5s)
        var result = await CampaignService.waitForPreparation(campaignId, function(progress, status) {
            showPreparationProgress(progress, status === 'preparing' ? 'Calculating costs...' : 'Ready');
        });

        // Preparation complete — display accurate cost
        hidePreparationProgress();
        displayCostEstimate(result.cost_estimate);
        displaySegmentStats(result.segment_stats);

        // Validate campaign readiness
        var validation = await CampaignService.validate(campaignId);
        if (validation.valid) {
            enableSendButtons();
        } else {
            showValidationErrors(validation.errors);
        }

    } catch (error) {
        hidePreparationProgress();
        showAlert('error', 'Campaign preparation failed: ' + error.message);
    }
});

function showPreparationProgress(percent, label) {
    document.getElementById('preparationOverlay').style.display = 'flex';
    document.getElementById('preparationBar').style.width = percent + '%';
    document.getElementById('preparationLabel').textContent = label + ' (' + percent + '%)';
}

function displaySegmentStats(stats) {
    if (stats.unicode_count > 0) {
        // Show warning about Unicode recipients
        document.getElementById('unicodeWarning').style.display = 'block';
        document.getElementById('unicodeCount').textContent = stats.unicode_count;
    }
    document.getElementById('totalSegments').textContent = stats.total_segments.toLocaleString();
    document.getElementById('avgSegments').textContent = stats.avg_segments.toFixed(2);
    if (stats.min_segments !== stats.max_segments) {
        document.getElementById('segmentRange').textContent =
            stats.min_segments + '–' + stats.max_segments + ' segments per message';
    }
}
```

---

## Data Mapping: Frontend Fields → API Fields

### Send Message Form → `POST /api/campaigns`

| Frontend Field (session key) | API Field | Notes |
|------------------------------|-----------|-------|
| `campaign_name` | `name` | Required, string, max 255 |
| `channel` → `'sms_only'` | `type` → `'sms'` | Map frontend channel values |
| `channel` → `'basic_rcs'` | `type` → `'rcs_basic'` | |
| `channel` → `'rich_rcs'` | `type` → `'rcs_single'` | |
| `sender_id` | `sender_id_id` | Integer FK to sender_ids table |
| `rcs_agent` | `rcs_agent_id` | Integer FK to rcs_agents table |
| `message_content` | `message_content` | SMS text content |
| `rcs_content` | `rcs_content` | JSON object for RCS payload |
| Selected template ID | `message_template_id` | UUID, nullable |
| `scheduled_time` (if not 'now') | `scheduled_at` | ISO 8601 datetime string |
| — | `timezone` | e.g. `'Europe/London'` |
| `sources` (lists/tags/manual) | `recipient_sources` | Array of `{ type, id/value }` objects |
| — | `send_rate` | Integer, 0-500 msg/sec (0 = unlimited) |
| — | `batch_size` | Integer, 100-10000 (default 1000) |

### Campaign Channel Type Mapping
```javascript
var channelMap = { 'sms_only': 'sms', 'basic_rcs': 'rcs_basic', 'rich_rcs': 'rcs_single' };
var channelDisplayMap = { 'sms': 'SMS Only', 'rcs_basic': 'Basic RCS + SMS Fallback', 'rcs_single': 'Rich RCS + SMS Fallback' };
```

### Campaign Status Badge Mapping
```javascript
var statusBadgeMap = {
    'draft':      { label: 'Draft',      class: 'badge-secondary' },
    'scheduled':  { label: 'Scheduled',  class: 'badge-info' },
    'queued':     { label: 'Queued',     class: 'badge-primary' },
    'sending':    { label: 'Sending',    class: 'badge-warning' },
    'paused':     { label: 'Paused',     class: 'badge-dark' },
    'completed':  { label: 'Completed',  class: 'badge-success' },
    'cancelled':  { label: 'Cancelled',  class: 'badge-danger' },
    'failed':     { label: 'Failed',     class: 'badge-danger' }
};
```

### Recipient Sources Format
```json
[
    { "type": "list", "id": 3, "name": "Marketing" },
    { "type": "tag", "id": 7, "name": "VIP" },
    { "type": "individual", "contact_ids": ["uuid1", "uuid2"] },
    { "type": "manual", "numbers": ["+447700900100", "+447700900200"] },
    { "type": "csv", "file_path": "uploads/recipients.csv" }
]
```

---

## Step-by-Step Implementation Order

### Phase 1: Service Layer Files
1. Create `public/js/campaign-service.js` (full code provided above — includes `prepare`, `preparationStatus`, `fieldStatistics`, `waitForPreparation`)
2. Create `public/js/template-service.js` (full code provided above)
3. Include both scripts in `resources/views/layouts/quicksms.blade.php` before `</body>`

### Phase 2: Replace Mock Data in Controllers
Update `QuickSMSController::sendMessage()`:
- Replace mock `$rcs_agents`, `$templates`, `$lists`, `$tags`, `$opt_out_lists` with real database queries

Update `QuickSMSController::campaignHistory()`:
- Remove mock `$campaigns` array — page loads via JavaScript `CampaignService.list()`

Update `QuickSMSController::templates()`:
- Replace mock data with real queries

### Phase 3: Wire Send Message Form (send-message.blade.php)
- Rewrite "Continue" button to: create draft → prepare → redirect to confirm page
- Add real-time content analysis on message textarea (debounced `analyseContent` calls)
- Add placeholder-aware segment range estimation using `fieldStatistics` endpoint
- Wire template selection to `applyTemplate` endpoint

### Phase 4: Wire Confirm Page (confirm-campaign.blade.php) — **BIGGEST CHANGE**
- Accept `campaign_id` from query parameter
- Add preparation progress overlay with progress bar
- Poll `preparationStatus` until ready
- Display accurate cost with per-country segment breakdown
- Show Unicode recipient warning if `segment_stats.unicode_count > 0`
- Wire send/schedule buttons to API
- Validate before enabling send buttons

### Phase 5: Wire Campaign History (campaign-history.blade.php)
- Replace mock data with `CampaignService.list()` calls
- Wire search/filter/pagination/action buttons
- Add polling for campaigns in `sending` status

### Phase 6: Wire Template Management
- Wire CRUD operations to template API endpoints
- Wire content textarea to `analyseContent` for live preview

---

## Key Integration Points with Existing Modules

### Contact Book Integration
- Load contact lists: `GET /api/contact-lists`
- Load tags: `GET /api/tags`
- Search individual contacts: `GET /api/contacts?search=query`
- `RecipientResolverService` queries contacts/lists/tags directly — frontend sends source config

### Sender ID Integration
- Load approved sender IDs: `GET /api/sender-ids/approved`
- Map `sender_id.id` to `sender_id_id` in campaign payload

### RCS Agent Integration
- Load approved RCS agents: `GET /api/rcs-agents/approved`
- Map `rcs_agent.id` to `rcs_agent_id` in campaign payload

### Billing / Balance Integration
- Account pricing: `GET /api/account/pricing`
- Campaign cost estimate: from `preparationStatus` response (accurate) or `GET /api/campaigns/{id}/estimate-cost`
- Fund reservation happens automatically on send — frontend shows estimate and handles insufficient balance errors (422)

---

## Error Handling Patterns

```javascript
try {
    var result = await CampaignService.sendNow(campaignId);
    showAlert('success', result.message);
} catch (error) {
    if (error.validationErrors) {
        Object.keys(error.validationErrors).forEach(function(field) {
            showFieldError(field, error.validationErrors[field][0]);
        });
    } else {
        showAlert('error', error.message);
    }
}
```

Error types: validation (422 with field errors), state transition (422), billing insufficient balance (422), not found (404), rate limit (429).

---

## Campaign State Machine — What Actions Are Valid When

| Current Status | Valid Actions |
|----------------|-------------|
| `draft` | edit, delete, send, schedule, prepare |
| `scheduled` | cancel, send (override schedule) |
| `queued` | pause, cancel |
| `sending` | pause, cancel |
| `paused` | resume, cancel |
| `completed` | clone, delete |
| `cancelled` | clone, delete |
| `failed` | clone, delete |

---

## Files to Modify (Summary)

| File | Change |
|------|--------|
| `app/Http/Controllers/QuickSMSController.php` | Replace mock data in `sendMessage()`, `campaignHistory()`, `templates()`, update `confirmCampaign()` to load campaign by ID, update `storeCampaignConfig()` |
| `resources/views/quicksms/messages/send-message.blade.php` | Rewrite Continue button to create+prepare flow, add content analysis, field statistics, template selection |
| `resources/views/quicksms/messages/confirm-campaign.blade.php` | **Major rewrite**: add preparation progress bar, poll for status, display accurate per-recipient cost with segment breakdown, wire send/schedule to API |
| `resources/views/quicksms/messages/campaign-history.blade.php` | Replace mock data with live API calls, wire filters/search/pagination/actions |
| `resources/views/layouts/quicksms.blade.php` | Add `<script>` tags for campaign-service.js and template-service.js |
| **New:** `public/js/campaign-service.js` | Campaign API service layer (includes prepare, polling, field statistics) |
| **New:** `public/js/template-service.js` | Template API service layer |

### Files NOT to Modify
- All files in `app/Models/` — backend models are complete
- All files in `app/Services/Campaign/` — service layer is complete
- All files in `app/Jobs/` — queue jobs are complete (including `ResolveRecipientContentJob`)
- `app/Http/Controllers/Api/CampaignApiController.php` — API controller is complete
- `app/Http/Controllers/Api/MessageTemplateApiController.php` — API controller is complete
- `app/Contracts/SmsGateway.php` — gateway interface is complete
- `routes/web.php` — all API routes are registered (including prepare, preparation-status, field-statistics)
- `database/migrations/` — all migrations are complete

---

## Testing Checklist

After wiring, verify these flows end-to-end:

1. **Create Campaign:** Fill send-message form → Continue → Draft created in DB
2. **Preparation Flow:** Continue → `prepare` called → confirm page shows progress bar → polls until ready → accurate cost displayed
3. **Segment Variation:** Create campaign with `{{first_name}}` placeholder and contacts of varying name lengths → confirm page shows segment breakdown (e.g. "3,200 × 1 segment, 280 × 2 segments")
4. **Unicode Detection:** If contacts have non-GSM names → confirm page shows Unicode warning with count
5. **Field Statistics:** On send form, select recipients with placeholders in message → segment range estimate shows (e.g. "1-2 segments")
6. **Cost Accuracy:** Confirm page cost matches sum of per-country segment breakdowns
7. **Send Now:** Click send → Campaign transitions to queued → Batch jobs dispatched
8. **Schedule:** Set future date → Campaign transitions to scheduled
9. **Message Edit After Prepare:** Edit message content → preparation invalidated → re-prepare shows updated cost
10. **Campaign History:** Page loads campaigns from API → Filters/pagination work
11. **Campaign Actions:** Pause/Resume/Cancel buttons work from history page
12. **Clone Campaign:** Creates new draft with same content
13. **Templates:** Create/edit/delete templates → Analyse content shows encoding/segments
14. **Insufficient Balance:** If balance too low → send returns 422 with clear error message
15. **Error Handling:** Validation errors display inline, billing errors show balance info
