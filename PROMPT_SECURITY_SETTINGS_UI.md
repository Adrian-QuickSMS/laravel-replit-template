# Master Prompt: Security Settings UI — Customer Portal + Admin Console

## Context

You are building the frontend UI for a security settings feature in a multi-tenant UK business messaging SaaS (SMS, RCS, WhatsApp). The **backend is fully built and tested**. Your job is to wire the existing Blade views to the backend API endpoints using vanilla JavaScript `fetch()` calls. No React, Vue, or Alpine.js — Blade + vanilla JS only.

Two UIs are needed:
1. **Customer Portal** (GREEN zone) — `/account/security` — customers manage their own settings
2. **Admin Console** (RED zone) — `/admin/accounts/{accountId}/settings` — admins manage settings per customer account

---

## Architecture Overview

```
┌─────────────────────────────────────────────────────────┐
│                    CUSTOMER PORTAL                       │
│                                                         │
│  security.blade.php ──fetch()──> SecuritySettingsController │
│  (GREEN zone)          │         (10 REST endpoints)       │
│                        │                                    │
│                        ▼                                    │
│              AccountSettings model                          │
│              AccountIpAllowlist model                       │
│              (RLS enforced, tenant-scoped)                  │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│                    ADMIN CONSOLE                         │
│                                                         │
│  settings.blade.php ──fetch()──> AdminController (new)     │
│  (RED zone)           │          (8 REST endpoints)        │
│                       │          svc_red role, no RLS      │
│                       ▼                                    │
│              AccountSettings (direct access)               │
│              AccountIpAllowlist (withoutGlobalScopes)      │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│                 BACKEND SERVICES                         │
│                                                         │
│  AntiFloodService ────── checks duplicate messages       │
│  OutOfHoursService ───── blocks/holds OOH messages       │
│  DataMaskingService ──── masks fields in message logs    │
│  IpAllowlistService ──── validates IP on every request   │
└─────────────────────────────────────────────────────────┘
```

---

## PART 1: Customer Portal Security Settings

### Existing File
`resources/views/quicksms/account/security.blade.php`

This file already exists with styling and the country permissions section. The security settings cards need to be added **above** the existing country permissions section, and wired to the backend API.

### Route
`GET /account/security` — renders the Blade view (controller: `QuickSMSController::securitySettings`)

### Layout
Uses `layouts/quicksms.blade.php` (the Fillow design system).

### Permission Required
The API endpoints require `permission:manage_security`. Users without this permission should see the settings as read-only (show current values but disable controls).

### API Base URL
All endpoints are under `/api/account/security/` with session-based auth (cookies). No Bearer token needed — the session cookie is sent automatically by `fetch()`.

### The 5 Feature Cards

The page should display 5 security settings cards in this order:

---

#### Card 1: Message Data Retention

**Purpose:** Controls how long message logs (MSISDN + content) are kept before being soft-purged.

**API:**
```
GET  /api/account/security/settings          → data.retention.message_retention_days
PUT  /api/account/security/retention         → { message_retention_days: 30|60|90|120|150|180 }
```

**UI Elements:**
- Dropdown select with options: 30, 60, 90, 120, 150, 180 days
- Current value loaded from GET response
- Save button triggers PUT
- Show success/error toast after save
- Default: 180 days

**Validation:**
- Value must be one of: 30, 60, 90, 120, 150, 180
- Server rejects anything else with 422

---

#### Card 2: Data Visibility & Masking

**Purpose:** Controls per-field masking on message logs and exports. When enabled, sub-users see masked data (e.g., `077****0123`, `[REDACTED]`, time hidden). Account owners/admins can optionally bypass masking.

**API:**
```
GET  /api/account/security/settings          → data.masking.config + data.masking.owner_bypass_masking
PUT  /api/account/security/masking           → { mask_mobile, mask_content, mask_sent_time, mask_delivered_time, owner_bypass_masking }
```

**Request body (all required booleans):**
```json
{
  "mask_mobile": true,
  "mask_content": false,
  "mask_sent_time": false,
  "mask_delivered_time": false,
  "owner_bypass_masking": true
}
```

**UI Elements:**
- 4 toggle switches (one per maskable field):
  - Mobile Number — `077****0123`
  - Message Content — `[REDACTED]`
  - Sent Time — date shown, time hidden (`18/03/2026 --:--`)
  - Delivered Time — date shown, time hidden
- 1 toggle switch: "Account owner/admin sees unmasked data"
- Save button sends all 5 values together
- Show preview example of masked vs unmasked

---

#### Card 3: Anti-Flood Protection

**Purpose:** Prevents duplicate messages (same content + same recipient) within a configurable time window. Protects against accidental re-sends.

**API:**
```
GET  /api/account/security/settings          → data.anti_flood { enabled, mode, window_hours }
PUT  /api/account/security/anti-flood        → { enabled, mode, window_hours }
```

**Request body:**
```json
{
  "enabled": true,
  "mode": "enforce",        // "enforce" | "monitor" | "off"
  "window_hours": 2         // integer, 2–48
}
```

**UI Elements:**
- Master toggle: Enable/Disable
- When enabled, show:
  - Mode selector (radio buttons or dropdown):
    - **Enforce** — blocks duplicate sends, returns error
    - **Monitor** — logs duplicates but allows the send
  - Window hours slider or input: 2–48 hours
- When disabling, mode is automatically set to `off` by the server
- Save button

**Behaviour notes:**
- If `enabled = false`, the server forces `mode = 'off'` regardless of what's sent
- Window hours must be between 2 and 48

---

#### Card 4: Out-of-Hours Sending Restriction

**Purpose:** Blocks outbound messages during anti-social hours (e.g., 21:00–08:00). Messages can be rejected (caller retries) or held (automatically sent when window opens).

**API:**
```
GET  /api/account/security/settings          → data.out_of_hours { enabled, start, end, action, timezone }
PUT  /api/account/security/out-of-hours      → { enabled, start, end, action }
```

**Request body:**
```json
{
  "enabled": true,
  "start": "21:00",          // HH:MM format
  "end": "08:00",            // HH:MM format
  "action": "hold"           // "reject" | "hold"
}
```

**UI Elements:**
- Master toggle: Enable/Disable
- When enabled, show:
  - Start time picker (HH:MM) — default `21:00`
  - End time picker (HH:MM) — default `08:00`
  - Action selector:
    - **Reject** — returns error to sender, they must retry
    - **Hold** — queues message, auto-sends when window opens
  - Display the account timezone (read-only, from `data.out_of_hours.timezone`)
- Validation: start and end cannot be the same (server returns 422)
- Save button

**Important:** The timezone shown is the account's configured timezone. It cannot be changed from this page — it's set in general account settings.

---

#### Card 5: Login IP Allowlist

**Purpose:** Restricts portal login to specific IP addresses or CIDR ranges. When enabled, requests from non-listed IPs are blocked and the session is terminated.

**API:**
```
GET  /api/account/security/settings          → data.ip_allowlist { enabled, entries[], limit }
GET  /api/account/security/ip-allowlist      → { enabled, entries[], limit }
POST /api/account/security/ip-allowlist      → { ip_address, label? }
DELETE /api/account/security/ip-allowlist/{id}
PUT  /api/account/security/ip-allowlist/toggle → { enabled }
GET  /api/account/security/ip-allowlist/current-ip → { ip_address }
```

**Entry shape (from GET):**
```json
{
  "id": "uuid",
  "ip_address": "192.168.1.0/24",
  "label": "Office network",
  "status": "active",
  "created_at": "2026-03-18T14:30:00+00:00"
}
```

**UI Elements:**
- Master toggle: Enable/Disable
  - When enabling, the server auto-adds the caller's IP if not already listed (self-lockout protection)
  - Show warning: "Enabling this will restrict access to listed IPs only"
- "Your current IP" display — call GET `/current-ip` on load
- IP entry table showing all entries (label, IP, status, created date, delete button)
- "Add IP" form:
  - IP address input (required, max 45 chars — supports IPv4, IPv6, CIDR notation)
  - Label input (optional, max 100 chars)
  - Add button
- Delete button per entry (with confirmation)
- Limit indicator: "X / 50 IPs used"

**Error handling:**
- 409 Conflict: "IP address already in allowlist" or "Limit reached (50/50)"
- 422: "Invalid IP address or CIDR range"

**Self-lockout protection:** When toggling ON, the server ensures the caller's IP is in the list. If it's not, the server adds it automatically with label "Auto-added (enabling allowlist)".

---

### JavaScript Pattern

All fetch calls in the customer portal must follow this pattern:

```javascript
async function saveRetention(value) {
    try {
        const response = await fetch('/api/account/security/retention', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ message_retention_days: value }),
        });

        if (!response.ok) {
            const err = await response.json();
            showToast('error', err.message || 'Failed to save');
            return;
        }

        const data = await response.json();
        showToast('success', 'Retention policy updated');
    } catch (error) {
        showToast('error', 'Network error — please try again');
    }
}
```

**Rules:**
- Always include `X-CSRF-TOKEN` header from the `<meta>` tag
- Always check `response.ok` before parsing JSON
- Always show visible error states to the user — never silently swallow errors
- Use toast notifications for save success/failure
- Disable save buttons during requests (prevent double-submit)

---

### Loading State

On page load, call `GET /api/account/security/settings` once to populate all 5 cards:

```javascript
document.addEventListener('DOMContentLoaded', async () => {
    try {
        const response = await fetch('/api/account/security/settings', {
            headers: { 'Accept': 'application/json' },
        });
        if (!response.ok) throw new Error('Failed to load settings');
        const { data } = await response.json();

        // Populate retention card
        document.getElementById('retentionDays').value = data.retention.message_retention_days;

        // Populate masking card
        document.getElementById('maskMobile').checked = data.masking.config.mask_mobile;
        document.getElementById('maskContent').checked = data.masking.config.mask_content;
        // ... etc

        // Populate anti-flood card
        document.getElementById('antiFloodEnabled').checked = data.anti_flood.enabled;
        // ... etc

        // Populate out-of-hours card
        document.getElementById('oohEnabled').checked = data.out_of_hours.enabled;
        // ... etc

        // Populate IP allowlist card
        renderIpTable(data.ip_allowlist.entries);
        document.getElementById('ipAllowlistEnabled').checked = data.ip_allowlist.enabled;
        document.getElementById('ipCount').textContent = `${data.ip_allowlist.entries.length} / ${data.ip_allowlist.limit}`;

    } catch (error) {
        showToast('error', 'Failed to load security settings');
    }
});
```

---

## PART 2: Admin Console — Account Security Settings

### Overview

Admins need to view and manage security settings for any customer account from the Admin Console. This goes in the existing **Accounts > Settings** page at `/admin/accounts/{accountId}/settings`.

### Existing File
`resources/views/admin/accounts/settings.blade.php`

This file already exists with an "Account Status" card, "Spam Filter" card, and "Test Credits" section. Security settings should be added as a **new section** below the existing content, with its own heading.

### Layout
Uses `layouts/admin.blade.php` (the admin design system with navy blue `--admin-primary: #1e3a5f`).

### New Admin API Endpoints Needed

These endpoints do NOT exist yet and **must be created** in `AdminController.php`. They operate as RED zone — no RLS, using `svc_red` role, full access.

```
GET  /admin/api/accounts/{accountId}/security-settings
PUT  /admin/api/accounts/{accountId}/security-settings/retention
PUT  /admin/api/accounts/{accountId}/security-settings/masking
PUT  /admin/api/accounts/{accountId}/security-settings/anti-flood
PUT  /admin/api/accounts/{accountId}/security-settings/out-of-hours
GET  /admin/api/accounts/{accountId}/security-settings/ip-allowlist
PUT  /admin/api/accounts/{accountId}/security-settings/ip-allowlist/toggle
DELETE /admin/api/accounts/{accountId}/security-settings/ip-allowlist/{entryId}
```

### Admin API Controller Methods

Add these methods to `AdminController.php`:

#### GET /admin/api/accounts/{accountId}/security-settings

Returns the same shape as the customer portal `index()` endpoint, but for any account (no RLS).

```php
public function getSecuritySettings($accountId)
{
    $settings = AccountSettings::withoutGlobalScopes()->find($accountId);
    if (!$settings) {
        return response()->json(['status' => 'error', 'message' => 'Account settings not found'], 404);
    }

    $ipEntries = AccountIpAllowlist::withoutGlobalScopes()
        ->where('tenant_id', $accountId)
        ->orderBy('created_at', 'desc')
        ->get()
        ->map->toPortalArray();

    return response()->json([
        'status' => 'success',
        'data' => [
            'retention' => [
                'message_retention_days' => $settings->message_retention_days ?? 180,
            ],
            'masking' => [
                'config' => is_string($settings->data_masking_config)
                    ? json_decode($settings->data_masking_config, true)
                    : ($settings->data_masking_config ?? [
                        'mask_mobile' => false,
                        'mask_content' => false,
                        'mask_sent_time' => false,
                        'mask_delivered_time' => false,
                    ]),
                'owner_bypass_masking' => $settings->owner_bypass_masking ?? true,
            ],
            'anti_flood' => [
                'enabled' => $settings->anti_flood_enabled ?? false,
                'mode' => $settings->anti_flood_mode ?? 'off',
                'window_hours' => $settings->anti_flood_window_hours ?? 2,
            ],
            'out_of_hours' => [
                'enabled' => $settings->out_of_hours_enabled ?? false,
                'start' => $settings->out_of_hours_start ?? '21:00',
                'end' => $settings->out_of_hours_end ?? '08:00',
                'action' => $settings->out_of_hours_action ?? 'reject',
                'timezone' => $settings->timezone ?? 'Europe/London',
            ],
            'ip_allowlist' => [
                'enabled' => $settings->ip_allowlist_enabled ?? false,
                'entries' => $ipEntries,
                'limit' => 50,
            ],
        ],
    ]);
}
```

#### PUT endpoints

Each update endpoint should:
1. Accept the same request body as the customer portal equivalent
2. Use `DB::table('account_settings')->where('account_id', $accountId)->update(...)` (same pattern as SecuritySettingsController)
3. Log the change to the admin audit log (use the admin's session, not the customer's)
4. Return the updated values

#### Admin Routes (add to `routes/web.php` inside the admin middleware group)

```php
// Security Settings — Admin per-account management
Route::prefix('api/accounts/{accountId}/security-settings')->group(function () {
    Route::get('/', 'getSecuritySettings')->name('admin.api.accounts.security-settings');
    Route::put('/retention', 'updateAccountRetention')->name('admin.api.accounts.security-settings.retention');
    Route::put('/masking', 'updateAccountMasking')->name('admin.api.accounts.security-settings.masking');
    Route::put('/anti-flood', 'updateAccountAntiFlood')->name('admin.api.accounts.security-settings.anti-flood');
    Route::put('/out-of-hours', 'updateAccountOutOfHours')->name('admin.api.accounts.security-settings.out-of-hours');
    Route::get('/ip-allowlist', 'getAccountIpAllowlist')->name('admin.api.accounts.security-settings.ip-allowlist');
    Route::put('/ip-allowlist/toggle', 'toggleAccountIpAllowlist')->name('admin.api.accounts.security-settings.ip-allowlist.toggle');
    Route::delete('/ip-allowlist/{entryId}', 'removeAccountIpEntry')->name('admin.api.accounts.security-settings.ip-allowlist.remove');
});
```

### Admin UI Section

Add to `admin/accounts/settings.blade.php` below the existing Test Credits section:

```html
<!-- Security Settings Section -->
<div class="row mt-4">
    <div class="col-12">
        <h5 class="mb-3" style="color: var(--admin-primary); font-weight: 600;">
            <i class="fas fa-shield-alt me-2"></i>Security Settings
        </h5>
    </div>
</div>
```

Then 5 cards matching the customer portal but with admin styling:
- Navy blue headers (`var(--admin-primary)`)
- Admin action menu pattern (ellipsis button → dropdown)
- Admin-only features: ability to force-disable customer settings

### Key Difference: Admin vs Customer Portal

| Feature | Customer Portal | Admin Console |
|---------|----------------|---------------|
| Auth | Session cookie (`customer.auth`) | Admin session (`AdminAuthenticate`) |
| API base | `/api/account/security/` | `/admin/api/accounts/{accountId}/security-settings/` |
| RLS | Enforced (portal_rw role) | Bypassed (svc_red role) |
| IP allowlist add | Yes (customers add their own) | No (admins can only view and remove) |
| IP allowlist toggle | Yes (with self-lockout protection) | Yes (no self-lockout check needed — admins access via different IP) |
| Audit log | Customer audit log (AccountAuditLog) | Admin audit log (AdminAuditLog) |
| CSRF | Via `X-CSRF-TOKEN` meta tag | Via `X-CSRF-TOKEN` meta tag |

---

## PART 3: Database Schema Reference

### account_settings table (existing — columns added by migration)

| Column | Type | Default | Description |
|--------|------|---------|-------------|
| `message_retention_days` | integer | 180 | Message log retention: 30–180 days |
| `data_masking_config` | jsonb | `{"mask_mobile":false,...}` | Per-field masking toggles |
| `owner_bypass_masking` | boolean | true | Owner/admin sees unmasked data |
| `anti_flood_enabled` | boolean | false | Enable duplicate protection |
| `anti_flood_mode` | enum | 'off' | enforce/monitor/off |
| `anti_flood_window_hours` | integer | 2 | Dedup window (2–48 hours) |
| `out_of_hours_enabled` | boolean | false | Enable OOH restriction |
| `out_of_hours_start` | time | '21:00' | Start of restricted window |
| `out_of_hours_end` | time | '08:00' | End of restricted window |
| `out_of_hours_action` | enum | 'reject' | reject/hold |
| `ip_allowlist_enabled` | boolean | false | Enable IP restriction |
| `timezone` | string | 'Europe/London' | Account timezone (pre-existing) |

### account_ip_allowlist table (new)

| Column | Type | Description |
|--------|------|-------------|
| `id` | uuid (PK) | Auto-generated |
| `tenant_id` | uuid (FK→accounts) | RLS key |
| `ip_address` | varchar(45) | IPv4/IPv6 or CIDR (e.g. `192.168.1.0/24`) |
| `label` | varchar(100) | Friendly name (nullable) |
| `created_by` | uuid | User who added |
| `status` | varchar(20) | `active` or `disabled` |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

### held_messages table (new — for out-of-hours hold feature)

| Column | Type | Description |
|--------|------|-------------|
| `id` | uuid (PK) | |
| `tenant_id` | uuid (FK→accounts) | RLS key |
| `recipient_number` | varchar(20) | E.164 |
| `message_content` | text | Message body |
| `sender_id` | varchar(15) | |
| `message_type` | varchar(20) | sms/rcs_basic/rcs_rich |
| `origin` | varchar(30) | portal/api/email_to_sms/campaign |
| `campaign_id` | uuid (nullable) | FK→campaigns |
| `campaign_recipient_id` | uuid (nullable) | FK→campaign_recipients |
| `status` | varchar(20) | held/released/expired/cancelled |
| `release_after` | timestamp | When message can be released |
| `released_at` | timestamp (nullable) | When it was actually released |

---

## PART 4: Backend Service Integration Points

These are the backend services that consume the settings. Understanding them helps inform UI copy and tooltips.

### AntiFloodService
- **File:** `app/Services/AntiFloodService.php`
- **Called by:** `DeliveryService::sendRecipient()` (Step 0b in the send pipeline)
- **Behavior:** Checks Redis for duplicate `(content_hash, recipient_hash)` within the window. If found and mode=enforce, blocks the send. If mode=monitor, logs but allows.
- **UI implication:** Explain to users that "enforce" blocks sends and "monitor" only logs

### OutOfHoursService
- **File:** `app/Services/OutOfHoursService.php`
- **Called by:** `DeliveryService::sendRecipient()` (Step 0a — runs before anti-flood)
- **Behavior:** Pure time comparison. If current time is within the restricted window:
  - `reject` → returns error, message not sent
  - `hold` → stores in `held_messages`, automatically released when window opens
- **UI implication:** Explain the difference between reject (caller must retry) and hold (automatic delivery)

### DataMaskingService
- **File:** `app/Services/DataMaskingService.php`
- **Called by:** Controllers/exports when rendering message log data
- **Behavior:** Replaces visible fields based on config:
  - `mask_mobile`: `07700900123` → `077****0123`
  - `mask_content`: full text → `[REDACTED]`
  - `mask_sent_time`: `18/03/2026 14:30` → `18/03/2026 --:--`
  - `mask_delivered_time`: same pattern
- **UI implication:** Show examples of what masked data looks like

### IpAllowlistService
- **File:** `app/Services/IpAllowlistService.php`
- **Called by:** `CustomerIpAllowlist` middleware on every authenticated request
- **Behavior:** If enabled, checks every request IP against the allowlist. Non-matching IPs get their session flushed and are redirected to login with an error message.
- **UI implication:** Strong warning when enabling — user could lock themselves out if their IP changes

---

## PART 5: Design System Rules

### Customer Portal (GREEN zone)
- **Layout:** `layouts/quicksms.blade.php`
- **Primary color:** `#886cc0` (purple)
- **Card pattern:** `.security-card` with `.security-card-header` and `.security-card-body`
- **Toggle style:** Bootstrap `.form-switch` with purple checked state
- **Icons:** Font Awesome 6
- **Toast notifications:** Use existing toast system (look for `showToast()` or create a simple one)
- **NO new CSS frameworks** — use existing Fillow design tokens

### Admin Console (RED zone)
- **Layout:** `layouts/admin.blade.php`
- **Primary color:** `--admin-primary: #1e3a5f` (navy blue)
- **Card pattern:** `.settings-card` with `.settings-card-header` and `.settings-card-body`
- **Existing tabs:** Details | Pricing | Billing | **Settings** (active)
- **Status badges:** `allowed` = green, `blocked` = red, `restricted` = amber
- **Action pattern:** Ellipsis button → dropdown with action sections
- **NO new layouts** — add to existing `admin/accounts/settings.blade.php`

---

## PART 6: Implementation Checklist

### Customer Portal
- [ ] Load all settings on page load via `GET /api/account/security/settings`
- [ ] Card 1: Message Retention — dropdown + save
- [ ] Card 2: Data Masking — 5 toggles + save
- [ ] Card 3: Anti-Flood — enable toggle + mode selector + window slider + save
- [ ] Card 4: Out-of-Hours — enable toggle + time pickers + action selector + save
- [ ] Card 5: IP Allowlist — enable toggle + IP table + add form + delete + current IP display
- [ ] Error handling: show visible errors on all fetch failures
- [ ] Loading states: show spinner/skeleton while fetching
- [ ] Permission check: disable controls for users without `manage_security`
- [ ] Toast notifications for save success/failure

### Admin Console — Backend
- [ ] Add 8 admin API endpoints to `AdminController.php`
- [ ] Add routes to `routes/web.php` inside admin middleware group
- [ ] Each endpoint: validate input, update via `DB::table()`, audit log
- [ ] Pass security settings data to the `accountsSettings()` view

### Admin Console — Frontend
- [ ] Add "Security Settings" section to `admin/accounts/settings.blade.php`
- [ ] Load settings via `GET /admin/api/accounts/{accountId}/security-settings`
- [ ] 5 cards matching customer portal functionality
- [ ] Admin can view and modify all settings for any account
- [ ] Admin can disable IP entries but not add new ones (customers add their own)
- [ ] Admin audit logging for all changes
- [ ] Toast notifications for save success/failure

---

## PART 7: Hard Rules (from CLAUDE.md)

1. **No new layouts** — use existing `layouts/quicksms.blade.php` and `layouts/admin.blade.php`
2. **No new CSS colour palettes** — use Fillow (portal) and admin design tokens
3. **No new frontend frameworks** — Blade + vanilla JS only
4. **All fetch() calls must check `response.ok`** before parsing JSON
5. **Error states must be visible** — never silently swallow API failures
6. **CSRF token required** on all POST/PUT/DELETE requests
7. **Admin endpoints use `withoutGlobalScopes()`** — they bypass RLS
8. **Customer endpoints inherit RLS** — tenant isolation is automatic via session
9. **Audit logging wrapped in try/catch** — must never block the save operation
10. **PostgreSQL only** — no MySQL syntax (e.g., use `JSONB` not `JSON`, no `UNSIGNED`)
