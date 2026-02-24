# Replit Prompt: Wire Send Message UI to Campaign Backend

## Overview

A complete campaign management backend has been built on branch `claude/quicksms-security-performance-dr8sw`. Your job is to wire the existing frontend Blade views and JavaScript to these new backend API endpoints. **Do not rebuild anything that already exists.** The backend is fully functional — you are connecting the UI layer to it.

Pull the branch first:
```bash
git fetch origin claude/quicksms-security-performance-dr8sw
git checkout claude/quicksms-security-performance-dr8sw
```

---

## What Was Built (Backend — Already Complete)

### New Database Tables (4 migrations in `database/migrations/`)
| Table | Purpose |
|---|---|
| `message_templates` | Reusable SMS/RCS message templates with encoding detection, segment counting, placeholder extraction |
| `campaigns` | Campaign lifecycle (8-state machine: draft→scheduled→queued→sending→paused→completed→cancelled→failed) |
| `campaign_recipients` | Per-recipient delivery tracking with contact data snapshots, batch processing, retry logic |
| `media_library` | RCS media uploads with MIME validation, dimensions, thumbnails |

### New Models (4 in `app/Models/`)
- `MessageTemplate` — tenant-scoped, encoding/segment/placeholder auto-calculation
- `Campaign` — state machine with validated transitions, progress/delivery helpers
- `CampaignRecipient` — status lifecycle (pending→queued→sent→delivered/failed), merge field resolution, retry scheduling
- `MediaLibraryItem` — tenant-scoped media management

### New Services (6 in `app/Services/Campaign/`)
- `CampaignService` — full orchestration: create, update, send, schedule, pause, resume, cancel, clone, delete
- `RecipientResolverService` — expands 5 source types (list, tag, individual, manual, csv), deduplication, opt-out filtering
- `BillingPreflightService` — cost estimation by country, balance checks, fund reservation before send
- `DeliveryService` — per-message send pipeline, gateway selection, DLR processing
- `PhoneNumberUtils` — E.164 normalization, country detection (80+ countries)
- `ResolverResult` — immutable DTO for recipient resolution results

### New Queue Jobs (3 in `app/Jobs/`)
- `ProcessCampaignBatch` — processes a batch of recipients for delivery (queue: `campaigns`)
- `HandleDeliveryReceipt` — processes gateway DLR callbacks (queue: `dlr`)
- `ScheduledCampaignDispatcher` — checks for due scheduled campaigns every minute (queue: `scheduler`)

### New API Controllers (2 in `app/Http/Controllers/Api/`)
- `CampaignApiController` — 17 endpoints for campaign CRUD + send operations
- `MessageTemplateApiController` — 7 endpoints for template CRUD + content analysis

### API Routes (registered in `routes/web.php` lines 231-281)

All routes are under `customer.auth` middleware with `throttle:60,1`.

---

## API Endpoint Reference

### Campaign API (`/api/campaigns`)

| Method | Endpoint | Controller Method | Purpose |
|--------|----------|-------------------|---------|
| `GET` | `/api/campaigns` | `index` | List campaigns (paginated, filterable by status/type/search) |
| `POST` | `/api/campaigns` | `store` | Create new campaign (draft) |
| `GET` | `/api/campaigns/{id}` | `show` | Get single campaign with full details |
| `PUT` | `/api/campaigns/{id}` | `update` | Update draft campaign |
| `DELETE` | `/api/campaigns/{id}` | `destroy` | Soft delete campaign |
| `POST` | `/api/campaigns/{id}/apply-template` | `applyTemplate` | Apply a message template to campaign |
| `GET` | `/api/campaigns/{id}/recipients/preview` | `previewRecipients` | Preview recipient counts (dry run) |
| `POST` | `/api/campaigns/{id}/recipients/resolve` | `resolveRecipients` | Resolve and persist recipients |
| `GET` | `/api/campaigns/{id}/recipients` | `recipients` | List resolved recipients (paginated) |
| `GET` | `/api/campaigns/{id}/estimate-cost` | `estimateCost` | Get cost estimate |
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

## Security Protocols — CRITICAL

### 1. Authentication & Tenant Isolation
- All API routes use `customer.auth` middleware (`app/Http/Middleware/CustomerAuthenticate.php`)
- This middleware checks session variables: `customer_logged_in`, `customer_user_id`, `customer_tenant_id`
- It sets the PostgreSQL tenant context: `SELECT set_config('app.current_tenant_id', ?, false)`
- All new models use **fail-closed global scopes** — if `session('customer_tenant_id')` is null, queries return `WHERE 1 = 0` (no data)
- PostgreSQL Row Level Security (RLS) policies enforce tenant isolation at the database level as a second layer
- **NEVER bypass global scopes** in frontend-facing code. The only place `withoutGlobalScope('tenant')` is used is in background jobs that need cross-tenant access

### 2. CSRF Protection
- All fetch requests MUST include the CSRF token header
- The CSRF token is available from: `document.querySelector('meta[name="csrf-token"]')?.content`
- Required header: `'X-CSRF-TOKEN': csrfToken`
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

### 4. Input Validation
- The backend validates all inputs server-side. Display validation errors returned in `422` responses
- Error format: `{ "status": "error", "message": "...", "errors": { "field": ["message"] } }`
- For Laravel validation errors: `{ "message": "The given data was invalid.", "errors": { "name": ["The name field is required."] } }`

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
- Replace the mock data in `QuickSMSController::sendMessage()` with real data from the database:
  - `$sender_ids` — already live (uses `$this->getApprovedSenderIds()`)
  - `$rcs_agents` — replace mock array with: `RcsAgent::where('status', 'approved')->get()->toArray()`
  - `$templates` — replace mock array with: `MessageTemplate::where('status', 'active')->get()->map->toPortalArray()`
  - `$lists` — fetch from: `GET /api/contact-lists` or query `ContactList` model
  - `$tags` — fetch from: `GET /api/tags` or query `Tag` model
  - `$opt_out_lists` — fetch from: `GET /api/opt-out-lists` or query `OptOutList` model
  - `$virtual_numbers` — leave as mock for now (not yet built)
  - `$optout_domains` — leave as mock for now (not yet built)

- **Campaign creation flow must change from session-based to API-based:**
  1. When user clicks "Continue" on send-message form, instead of just saving to session, call `POST /api/campaigns` to create a draft campaign in the database
  2. Store the returned `campaign.id` (UUID) in the session or pass it as a query parameter to the confirm page
  3. Update `storeCampaignConfig()` to:
     - Call `POST /api/campaigns` with the form data
     - Call `POST /api/campaigns/{id}/recipients/resolve` to resolve recipients
     - Call `GET /api/campaigns/{id}/estimate-cost` to get cost estimate
     - Redirect to confirm page with campaign ID

- **Real-time content analysis:** Wire the message textarea to call `POST /api/message-templates/analyse-content` on input (debounced, 300ms) to show live encoding type, character count, and segment count. The response returns:
```json
{
    "encoding": "GSM-7",
    "character_count": 142,
    "segment_count": 1,
    "placeholders": ["firstName", "company"]
}
```

- **Template selection:** When user picks a template, call `POST /api/campaigns/{id}/apply-template` with `{ "template_id": "uuid" }` to copy template content into the campaign

- **Recipient preview:** After selecting recipient sources (lists/tags/manual numbers), call `GET /api/campaigns/{id}/recipients/preview` to show counts before resolving:
```json
{
    "data": {
        "total_resolved": 5200,
        "total_unique": 4800,
        "total_opted_out": 142,
        "total_invalid": 58,
        "source_breakdown": { "list": 3000, "tag": 2200 },
        "country_breakdown": { "GB": 3500, "US": 800, "AU": 500 }
    }
}
```

#### 2. `resources/views/quicksms/messages/confirm-campaign.blade.php`
**Current state:** Reads campaign data from `session('campaign_config')`. Displays summary cards for campaign details, channel/delivery, message content, recipients, and cost estimate. Has "Send Now" and "Schedule" buttons.

**What needs to change:**
- Instead of reading from session, load the campaign from the API: `GET /api/campaigns/{id}` where ID comes from query parameter or session
- The confirm page should call `GET /api/campaigns/{id}/validate` to check readiness before enabling send buttons
- Wire "Send Now" button to call `POST /api/campaigns/{id}/send`
- Wire "Schedule" button to call `POST /api/campaigns/{id}/schedule` with `{ "scheduled_at": "2026-03-01T10:00:00Z", "timezone": "Europe/London" }`
- Display the cost estimate from `GET /api/campaigns/{id}/estimate-cost`:
```json
{
    "data": {
        "total_cost": 156.80,
        "currency": "GBP",
        "recipient_count": 4800,
        "segments_per_message": 1,
        "country_breakdown": {
            "GB": { "count": 3500, "unit_price": 0.032, "total": 112.00 },
            "US": { "count": 800, "unit_price": 0.045, "total": 36.00 }
        }
    }
}
```
- Show validation errors if campaign fails validation:
```json
{
    "valid": false,
    "errors": ["Message content is required", "No sender ID selected"]
}
```
- After successful send, show success state and redirect to campaign detail/history

#### 3. `resources/views/quicksms/messages/campaign-history.blade.php`
**Current state:** Uses hardcoded mock data array (16 fake campaigns defined in `QuickSMSController::campaignHistory()`). Has TODO comments saying "Replace with: GET /api/campaigns?page=X&limit=Y&filters=Z". Has full UI for filtering, searching, pagination, status badges, and action buttons (view, edit, cancel, clone, export).

**What needs to change:**
- Replace ALL mock data with live API calls
- Remove the mock `$campaigns` array from `QuickSMSController::campaignHistory()`
- Load campaigns via JavaScript fetch on page load: `GET /api/campaigns?page=1&per_page=25`
- Wire the search box to: `GET /api/campaigns?search=query`
- Wire status filter tabs to: `GET /api/campaigns?status=draft` / `?status=sending` / `?status=complete` etc.
- Wire pagination controls to the API's `current_page`, `last_page`, `total` response fields
- Wire action buttons:
  - "Edit" → navigate to `/messages/send?campaign_id={id}` (load existing campaign into send form)
  - "Clone" → `POST /api/campaigns/{id}/clone` then navigate to the new draft
  - "Cancel" → `POST /api/campaigns/{id}/cancel` with confirmation dialog
  - "Pause" → `POST /api/campaigns/{id}/pause`
  - "Resume" → `POST /api/campaigns/{id}/resume`
  - "View Detail" → expand row or navigate to detail view showing `GET /api/campaigns/{id}`
- Campaign list response format:
```json
{
    "data": [
        {
            "id": "uuid",
            "name": "Spring Promo",
            "type": "sms",
            "status": "sending",
            "total_recipients": 5200,
            "delivered_count": 3100,
            "failed_count": 42,
            "progress_percentage": 60.4,
            "created_at": "2026-02-20T10:00:00Z",
            "scheduled_at": null,
            "sender_display_name": "QuickSMS"
        }
    ],
    "total": 156,
    "per_page": 25,
    "current_page": 1,
    "last_page": 7
}
```

#### 4. Template Management Views (`resources/views/quicksms/management/templates/`)
**Current routing:** Multi-step create/edit flow at `/management/templates/create/step1`, `/step2`, `/step3`, `/review` and `/management/templates/{id}/edit/step1` etc.

**What needs to change:**
- Wire the template list page to `GET /api/message-templates`
- Wire template creation flow:
  - Step 1 (name/type/category): Just collect form data locally
  - Step 2 (content): Use `POST /api/message-templates/analyse-content` for live encoding/segment preview
  - Step 3 (tags/settings): Collect remaining fields
  - Review/Save: `POST /api/message-templates` to create, or `PUT /api/message-templates/{id}` to update
- Wire template deletion to `DELETE /api/message-templates/{id}`
- Wire favourite toggle to `POST /api/message-templates/{id}/toggle-favourite`

---

## JavaScript Service Layer Pattern

Follow the established `ContactsService` pattern from `public/js/contacts-service.js`. Create a new service file:

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

        resolveRecipients: function(campaignId) {
            return fetch(this.config.baseUrl + '/' + campaignId + '/recipients/resolve', {
                method: 'POST',
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

Include these in Blade layouts:
```html
<script src="{{ asset('js/campaign-service.js') }}"></script>
<script src="{{ asset('js/template-service.js') }}"></script>
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
// Frontend → Backend mapping
var channelMap = {
    'sms_only': 'sms',
    'basic_rcs': 'rcs_basic',
    'rich_rcs': 'rcs_single'
};

// Backend → Frontend mapping (for display)
var channelDisplayMap = {
    'sms': 'SMS Only',
    'rcs_basic': 'Basic RCS with SMS Fallback',
    'rcs_single': 'Rich RCS with SMS Fallback'
};
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
The `recipient_sources` field is an array. Each source has a `type` and relevant identifiers:
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
1. Create `public/js/campaign-service.js` (full code provided above)
2. Create `public/js/template-service.js` (full code provided above)
3. Include both scripts in `resources/views/layouts/quicksms.blade.php` before `</body>`

### Phase 2: Replace Mock Data in Controllers
Update `QuickSMSController::sendMessage()` (line 354):
- Replace mock `$rcs_agents` with: `\App\Models\RcsAgent::where('status', 'approved')->select('id', 'name', 'logo_url', 'tagline', 'brand_color', 'status')->get()->toArray()`
- Replace mock `$templates` with: `\App\Models\MessageTemplate::where('status', 'active')->get()->map->toPortalArray()->toArray()`
- Replace mock `$lists` with a query to `ContactList` or use the existing contacts API
- Replace mock `$tags` with a query to `Tag` model
- Replace mock `$opt_out_lists` with a query to `OptOutList` model
- Leave `$virtual_numbers` and `$optout_domains` as mock for now

Update `QuickSMSController::campaignHistory()` (line 1330):
- Remove the entire mock `$campaigns` array
- Pass an empty array or nothing — the page should load campaigns via JavaScript `CampaignService.list()`

Update `QuickSMSController::templates()` (line 1688):
- Replace mock data with real queries (same pattern as sendMessage)

### Phase 3: Wire Send Message Flow (send-message.blade.php)
The critical flow change is the "Continue" button handler:

**Currently:**
```
User fills form → JS posts to /messages/store-campaign-config (saves to session) → redirects to /messages/confirm
```

**New flow:**
```
User fills form → JS calls POST /api/campaigns (creates draft) → JS calls POST /api/campaigns/{id}/recipients/resolve → JS calls GET /api/campaigns/{id}/estimate-cost → stores campaign_id in session → redirects to /messages/confirm?campaign_id={id}
```

Implementation for the "Continue" button:
```javascript
async function handleContinue() {
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

        var result = await CampaignService.create(campaignData);
        var campaignId = result.data.id;

        // 2. Resolve recipients
        await CampaignService.resolveRecipients(campaignId);

        // 3. Store campaign ID for confirm page
        await fetch('/messages/store-campaign-config', {
            method: 'POST',
            headers: CampaignService._headers(),
            body: JSON.stringify({ campaign_id: campaignId })
        });

        // 4. Redirect to confirm page
        window.location.href = '/messages/confirm?campaign_id=' + campaignId;

    } catch (error) {
        if (error.validationErrors) {
            displayValidationErrors(error.validationErrors);
        } else {
            showAlert('error', error.message);
        }
    }
}
```

### Phase 4: Wire Confirm Page (confirm-campaign.blade.php)
- Update `QuickSMSController::confirmCampaign()` to accept a `campaign_id` query parameter
- Load the full campaign from the database instead of session
- Pass campaign data, cost estimate, and validation result to the view
- Wire send/schedule buttons to the API

### Phase 5: Wire Campaign History (campaign-history.blade.php)
- Replace the server-side mock data rendering with client-side JavaScript
- On page load, call `CampaignService.list({ page: 1, per_page: 25 })` and render the table
- Wire search/filter/pagination to make new API calls and re-render
- Wire action buttons (clone, cancel, pause, resume) to their respective API endpoints
- Add polling for active campaigns: refresh every 10 seconds for campaigns in `sending` status to update progress bars

### Phase 6: Wire Template Management
- Wire template list to `GET /api/message-templates`
- Wire create/edit forms to `POST` / `PUT /api/message-templates/{id}`
- Wire the content textarea to `POST /api/message-templates/analyse-content` (debounced)
- Wire delete to `DELETE /api/message-templates/{id}`
- Wire favourite toggle to `POST /api/message-templates/{id}/toggle-favourite`

---

## Key Integration Points with Existing Modules

### Contact Book Integration
The recipient source selector in send-message.blade.php should use the existing Contact Book APIs:
- Load contact lists: `GET /api/contact-lists` (already wired)
- Load tags: `GET /api/tags` (already wired)
- Search individual contacts: `GET /api/contacts?search=query` (already wired)
- The `RecipientResolverService` on the backend already queries the `contacts`, `contact_lists`, and `tags` tables directly — the frontend just needs to send the source configuration

### Sender ID Integration
- Load approved sender IDs: `GET /api/sender-ids/approved` (already wired via `SenderIdController::approved()`)
- The sender ID dropdown in send-message.blade.php already works — just make sure the selected `sender_id.id` maps to `sender_id_id` in the campaign create payload

### RCS Agent Integration
- Load approved RCS agents: `GET /api/rcs-agents/approved` (already wired via `RcsAgentController::approved()`)
- Map selected `rcs_agent.id` to `rcs_agent_id` in the campaign payload

### Billing / Balance Integration
- Account pricing: `GET /api/account/pricing` (already wired)
- Campaign cost estimate: `GET /api/campaigns/{id}/estimate-cost` (new)
- Display balance and estimated cost on the confirm page
- The backend automatically handles fund reservation on send — the frontend just needs to show the estimate and handle insufficient balance errors (422 with message "Insufficient balance")

---

## Error Handling Patterns

### Standard Error Response
```json
{ "status": "error", "message": "Human-readable error message" }
```

### Validation Error (422)
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "name": ["The name field is required."],
        "type": ["The selected type is invalid."]
    }
}
```

### State Transition Error (422)
```json
{ "status": "error", "message": "Campaign cannot be edited in 'sending' status." }
```

### Billing Error (422)
```json
{ "status": "error", "message": "Insufficient balance. Required: £156.80, Available: £45.20" }
```

### Not Found (404)
```json
{ "status": "error", "message": "Campaign not found" }
```

### Recommended UI Error Handling
```javascript
try {
    var result = await CampaignService.sendNow(campaignId);
    showAlert('success', result.message);
} catch (error) {
    if (error.validationErrors) {
        // Show per-field errors inline
        Object.keys(error.validationErrors).forEach(function(field) {
            showFieldError(field, error.validationErrors[field][0]);
        });
    } else {
        // Show general error banner
        showAlert('error', error.message);
    }
}
```

---

## Campaign State Machine — What Actions Are Valid When

| Current Status | Valid Actions |
|----------------|-------------|
| `draft` | edit, delete, send, schedule |
| `scheduled` | cancel, send (override schedule) |
| `queued` | pause, cancel |
| `sending` | pause, cancel |
| `paused` | resume, cancel |
| `completed` | clone, delete |
| `cancelled` | clone, delete |
| `failed` | clone, delete |

Use this to conditionally show/hide action buttons in the UI.

---

## Database Migration

Before testing, run migrations:
```bash
php artisan migrate
```

This will create the 4 new tables: `message_templates`, `campaigns`, `campaign_recipients`, `media_library`.

---

## Files to Modify (Summary)

| File | Change |
|------|--------|
| `app/Http/Controllers/QuickSMSController.php` | Replace mock data in `sendMessage()`, `campaignHistory()`, `templates()`, update `confirmCampaign()` to load from DB, update `storeCampaignConfig()` |
| `resources/views/quicksms/messages/send-message.blade.php` | Wire form submission to Campaign API, add real-time content analysis, template selection, recipient preview |
| `resources/views/quicksms/messages/confirm-campaign.blade.php` | Load from API instead of session, wire send/schedule buttons, show cost estimate |
| `resources/views/quicksms/messages/campaign-history.blade.php` | Replace mock data with live API calls, wire filters/search/pagination/actions |
| `resources/views/quicksms/messages/campaign-approvals.blade.php` | Wire to campaign list filtered by approval status (if applicable) |
| `resources/views/layouts/quicksms.blade.php` | Add `<script>` tags for campaign-service.js and template-service.js |
| **New:** `public/js/campaign-service.js` | Campaign API service layer |
| **New:** `public/js/template-service.js` | Template API service layer |

### Files NOT to Modify
- All files in `app/Models/` — backend models are complete
- All files in `app/Services/Campaign/` — service layer is complete
- All files in `app/Jobs/` — queue jobs are complete
- `app/Http/Controllers/Api/CampaignApiController.php` — API controller is complete
- `app/Http/Controllers/Api/MessageTemplateApiController.php` — API controller is complete
- `app/Contracts/SmsGateway.php` — gateway interface is complete
- `routes/web.php` — API routes are registered
- `database/migrations/` — migrations are complete

---

## Testing Checklist

After wiring, verify these flows work end-to-end:

1. **Create Campaign:** Fill send-message form → Continue → Draft campaign created in DB
2. **Recipient Resolution:** Selected lists/tags → Preview shows correct counts → Resolve persists recipients
3. **Cost Estimation:** Confirm page shows cost breakdown by country
4. **Validation:** Confirm page shows validation errors if campaign is incomplete
5. **Send Now:** Click send → Campaign transitions to queued → Batch jobs dispatched
6. **Schedule:** Set future date → Campaign transitions to scheduled
7. **Campaign History:** Page loads campaigns from API → Filters work → Pagination works
8. **Campaign Actions:** Pause/Resume/Cancel buttons work from history page
9. **Clone Campaign:** Creates new draft with same content
10. **Templates:** Create/edit/delete templates → Analyse content shows encoding/segments
11. **Template Application:** Select template in send form → Content populated from template
12. **Error Handling:** Validation errors display inline, billing errors show balance info
