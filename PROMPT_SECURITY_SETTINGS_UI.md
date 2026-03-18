# Master Prompt: Security Settings — Backend Reference & UI Wiring Guide

## Purpose

This document describes the **fully built and tested** backend for 5 security settings features. It serves two purposes:

1. **Customer Portal UI** — Wire `resources/views/quicksms/account/security.blade.php` to the existing REST API endpoints using vanilla JS `fetch()` calls
2. **Admin Console UI** — Add per-account security controls to `resources/views/admin/accounts/settings.blade.php` with new admin API endpoints

---

## Architecture Overview

```
┌──────────────────────────────────────────────────────────────┐
│                    CUSTOMER PORTAL (GREEN zone)               │
│                                                               │
│  security.blade.php ──fetch()──> SecuritySettingsController    │
│  Layout: layouts/quicksms.blade.php    (10 REST endpoints)    │
│  Auth: session cookie (customer.auth)                         │
│  Permission: manage_security (JSONB toggle)                   │
│  DB role: portal_rw (RLS enforced)                            │
└──────────────────────────────────────────────────────────────┘

┌──────────────────────────────────────────────────────────────┐
│                    ADMIN CONSOLE (RED zone)                    │
│                                                               │
│  settings.blade.php ──fetch()──> AdminController (NEW)        │
│  Layout: layouts/admin.blade.php       (8 new endpoints)      │
│  Auth: AdminAuthenticate middleware                           │
│  DB role: svc_red (RLS bypassed)                              │
└──────────────────────────────────────────────────────────────┘

┌──────────────────────────────────────────────────────────────┐
│                    BACKEND SERVICES (exist, wired)             │
│                                                               │
│  AntiFloodService ──── duplicate detection (Redis L1 + DB L2) │
│  OutOfHoursService ─── time-window blocking + message holding │
│  DataMaskingService ── field-level masking for logs/exports    │
│  IpAllowlistService ── IP validation + CIDR + Redis cache     │
└──────────────────────────────────────────────────────────────┘
```

---

## PART 1: Customer Portal — API Endpoint Reference

### Base URL & Auth

All endpoints are under `/api/account/security/`. Auth is session-based (cookies sent automatically by `fetch()`). No Bearer token needed.

**Middleware stack:** `customer.auth` → `customer.ip_allowlist` → `permission:manage_security` → `throttle:30,1`

**Controller:** `app/Http/Controllers/SecuritySettingsController.php` (507 lines, 10 endpoints)

---

### 1.1 Load All Settings (single call populates all 5 cards)

```
GET /api/account/security/settings
```

**Response (200):**
```json
{
  "status": "success",
  "data": {
    "retention": {
      "message_retention_days": 180
    },
    "masking": {
      "config": {
        "mask_mobile": false,
        "mask_content": false,
        "mask_sent_time": false,
        "mask_delivered_time": false
      },
      "owner_bypass_masking": true
    },
    "anti_flood": {
      "enabled": false,
      "mode": "off",
      "window_hours": 2
    },
    "out_of_hours": {
      "enabled": false,
      "start": "21:00",
      "end": "08:00",
      "action": "reject",
      "timezone": "Europe/London"
    },
    "ip_allowlist": {
      "enabled": false,
      "entries": [],
      "limit": 50
    }
  }
}
```

---

### 1.2 Message Data Retention

```
PUT /api/account/security/retention
```

**Request:**
```json
{ "message_retention_days": 90 }
```

**Validation:** `message_retention_days` must be one of: `30, 60, 90, 120, 150, 180`

**Response (200):**
```json
{ "status": "success", "data": { "message_retention_days": 90 } }
```

**UI:** Dropdown select with 6 options. Default: 180 days.

---

### 1.3 Data Visibility & Masking

```
PUT /api/account/security/masking
```

**Request (all 4 mask fields required, bypass optional):**
```json
{
  "mask_mobile": true,
  "mask_content": false,
  "mask_sent_time": false,
  "mask_delivered_time": false,
  "owner_bypass_masking": true
}
```

**Response (200):**
```json
{
  "status": "success",
  "data": {
    "config": { "mask_mobile": true, "mask_content": false, "mask_sent_time": false, "mask_delivered_time": false },
    "owner_bypass_masking": true
  }
}
```

**UI:** 4 toggle switches (one per field) + 1 bypass toggle. Show preview examples:
- Mobile: `07700900123` → `077****0123`
- Content: full text → `[REDACTED]`
- Timestamps: `18/03/2026 14:30` → `18/03/2026 --:--`

---

### 1.4 Anti-Flood Protection

```
PUT /api/account/security/anti-flood
```

**Request:**
```json
{
  "enabled": true,
  "mode": "enforce",
  "window_hours": 4
}
```

**Validation:**
- `enabled`: required boolean
- `mode`: required, one of `enforce`, `monitor`, `off`
- `window_hours`: required integer, 2–48

**Server behaviour:** If `enabled = false`, server forces `mode = 'off'` regardless of input.

**Response (200):**
```json
{ "status": "success", "data": { "enabled": true, "mode": "enforce", "window_hours": 4 } }
```

**UI:** Master enable toggle. When enabled: mode selector (Enforce = block duplicates / Monitor = log only) + window hours input (2–48). When disabling, send `mode: "off"`.

---

### 1.5 Out-of-Hours Sending Restriction

```
PUT /api/account/security/out-of-hours
```

**Request:**
```json
{
  "enabled": true,
  "start": "21:00",
  "end": "08:00",
  "action": "hold"
}
```

**Validation:**
- `enabled`: required boolean
- `start`: optional, `HH:MM` format (validated via `date_format:H:i`)
- `end`: optional, `HH:MM` format (validated via `date_format:H:i`)
- `action`: optional, one of `reject`, `hold`
- Server rejects if `start === end` (422)

**Response (200):**
```json
{ "status": "success", "data": { "enabled": true, "start": "21:00", "end": "08:00", "action": "hold" } }
```

**UI:** Master enable toggle. When enabled: start/end time pickers + action selector (Reject = caller retries / Hold = auto-sends when window opens). Display timezone as read-only from `data.out_of_hours.timezone`.

---

### 1.6 Login IP Allowlist (6 endpoints)

#### List IPs
```
GET /api/account/security/ip-allowlist
```
**Response:**
```json
{
  "status": "success",
  "data": {
    "enabled": false,
    "entries": [
      { "id": "uuid", "ip_address": "192.168.1.0/24", "label": "Office", "status": "active", "created_at": "2026-03-18T14:30:00+00:00" }
    ],
    "limit": 50
  }
}
```

#### Add IP
```
POST /api/account/security/ip-allowlist
```
**Request:** `{ "ip_address": "10.0.0.1", "label": "VPN" }`
**Response (201):** `{ "status": "success", "data": { "id": "uuid", "ip_address": "10.0.0.1", "label": "VPN", "status": "active", "created_at": "..." } }`
**Errors:** 422 (invalid IP/CIDR), 409 (duplicate or limit reached at 50)

#### Remove IP
```
DELETE /api/account/security/ip-allowlist/{id}
```
**Response:** `{ "status": "success" }`
**Error:** 409 if removing the last active IP while allowlist is enabled: `"Cannot remove the last IP while allowlist is enabled. Disable the allowlist first."`

#### Toggle Allowlist
```
PUT /api/account/security/ip-allowlist/toggle
```
**Request:** `{ "enabled": true }`
**Self-lockout protection:** When enabling, server auto-adds caller's IP if not already listed (label: "Auto-added (enabling allowlist)").
**Response:** `{ "status": "success", "data": { "enabled": true } }`

#### Get Current IP
```
GET /api/account/security/ip-allowlist/current-ip
```
**Response:** `{ "status": "success", "data": { "ip_address": "203.0.113.42" } }`

**UI:** Enable toggle + "Your current IP" display + IP table (label, address, status, date, delete button) + add form (IP + label) + counter "X / 50 IPs used". Show confirmation dialog before delete. Show warning banner when enabling.

---

### JavaScript Pattern (all fetch calls must follow this)

```javascript
async function apiCall(url, method = 'GET', body = null) {
    const opts = {
        method,
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
    };
    if (body) {
        opts.headers['Content-Type'] = 'application/json';
        opts.body = JSON.stringify(body);
    }
    const response = await fetch(url, opts);
    if (!response.ok) {
        const err = await response.json().catch(() => ({ message: 'Request failed' }));
        throw new Error(err.message || `HTTP ${response.status}`);
    }
    return response.json();
}
```

**Rules:**
- Always include `X-CSRF-TOKEN` from `<meta name="csrf-token">` tag
- Always check `response.ok` before parsing JSON
- Always show visible error states — never silently swallow failures
- Disable save buttons during requests (prevent double-submit)
- Use toast notifications for success/failure feedback

### Page Load Pattern

```javascript
document.addEventListener('DOMContentLoaded', async () => {
    try {
        const { data } = await apiCall('/api/account/security/settings');
        populateRetention(data.retention);
        populateMasking(data.masking);
        populateAntiFlood(data.anti_flood);
        populateOutOfHours(data.out_of_hours);
        populateIpAllowlist(data.ip_allowlist);
    } catch (err) {
        showToast('error', 'Failed to load security settings');
    }
});
```

---

## PART 2: Admin Console — Per-Account Security Controls

### Overview

Admins manage security settings for any customer from **Admin Console > Accounts > Settings** (`/admin/accounts/{accountId}/settings`).

### Existing Page

`resources/views/admin/accounts/settings.blade.php` already has:
- Account Status card (status change dropdown)
- Spam Filter card
- Test Credits section

Security settings go as a **new section** below existing content.

### New Admin API Endpoints (to be created)

Add to `AdminController.php`. These are RED zone — `svc_red` role, no RLS, use `withoutGlobalScopes()`.

| Method | Verb | Route | Purpose |
|--------|------|-------|---------|
| `getSecuritySettings` | GET | `/admin/api/accounts/{accountId}/security-settings` | Read all settings |
| `updateAccountRetention` | PUT | `/admin/api/accounts/{accountId}/security-settings/retention` | Update retention |
| `updateAccountMasking` | PUT | `/admin/api/accounts/{accountId}/security-settings/masking` | Update masking |
| `updateAccountAntiFlood` | PUT | `/admin/api/accounts/{accountId}/security-settings/anti-flood` | Update anti-flood |
| `updateAccountOutOfHours` | PUT | `/admin/api/accounts/{accountId}/security-settings/out-of-hours` | Update out-of-hours |
| `getAccountIpAllowlist` | GET | `/admin/api/accounts/{accountId}/security-settings/ip-allowlist` | List IPs |
| `toggleAccountIpAllowlist` | PUT | `/admin/api/accounts/{accountId}/security-settings/ip-allowlist/toggle` | Enable/disable |
| `removeAccountIpEntry` | DELETE | `/admin/api/accounts/{accountId}/security-settings/ip-allowlist/{entryId}` | Remove IP |

### Route Registration

Add inside the existing admin middleware group in `routes/web.php` (after the other `/api/accounts/{accountId}/` routes around line 649):

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

### GET Endpoint Implementation

The GET endpoint returns the same shape as the customer portal `index()`, but bypasses RLS:

```php
public function getSecuritySettings($accountId)
{
    $settings = \App\Models\AccountSettings::withoutGlobalScopes()->find($accountId);
    if (!$settings) {
        return response()->json(['status' => 'error', 'message' => 'Account settings not found'], 404);
    }

    $ipEntries = \App\Models\AccountIpAllowlist::withoutGlobalScopes()
        ->where('tenant_id', $accountId)
        ->orderBy('created_at', 'desc')
        ->get()
        ->map->toPortalArray();

    return response()->json([
        'status' => 'success',
        'data' => [
            'retention' => ['message_retention_days' => $settings->message_retention_days ?? 180],
            'masking' => [
                'config' => is_string($settings->data_masking_config)
                    ? json_decode($settings->data_masking_config, true)
                    : ($settings->data_masking_config ?? ['mask_mobile' => false, 'mask_content' => false, 'mask_sent_time' => false, 'mask_delivered_time' => false]),
                'owner_bypass_masking' => $settings->owner_bypass_masking ?? true,
            ],
            'anti_flood' => ['enabled' => $settings->anti_flood_enabled ?? false, 'mode' => $settings->anti_flood_mode ?? 'off', 'window_hours' => $settings->anti_flood_window_hours ?? 2],
            'out_of_hours' => ['enabled' => $settings->out_of_hours_enabled ?? false, 'start' => $settings->out_of_hours_start ?? '21:00', 'end' => $settings->out_of_hours_end ?? '08:00', 'action' => $settings->out_of_hours_action ?? 'reject', 'timezone' => $settings->timezone ?? 'Europe/London'],
            'ip_allowlist' => ['enabled' => $settings->ip_allowlist_enabled ?? false, 'entries' => $ipEntries, 'limit' => 50],
        ],
    ]);
}
```

### PUT Endpoints Pattern

Each admin PUT endpoint follows the same pattern as the customer portal equivalent but:
1. Takes `$accountId` from the route parameter (not session)
2. Uses `DB::table('account_settings')->where('account_id', $accountId)->update(...)`
3. Uses same validation rules as the customer endpoint
4. Logs to admin audit log (not customer audit log)

### Admin vs Customer Portal Differences

| Feature | Customer Portal | Admin Console |
|---------|----------------|---------------|
| Auth | Session cookie (`customer.auth`) | Admin session (`AdminAuthenticate`) |
| API base | `/api/account/security/` | `/admin/api/accounts/{accountId}/security-settings/` |
| Account ID source | `session('customer_tenant_id')` | Route parameter `{accountId}` |
| RLS | Enforced (`portal_rw` role) | Bypassed (`svc_red` role) |
| IP allowlist add | Yes (customers add their own IPs) | No (admin can only view/remove) |
| IP allowlist toggle | With self-lockout protection | No lockout check needed (admin IP irrelevant) |
| Audit log | `AccountAuditLog` (customer) | Admin audit log |
| CSRF | `X-CSRF-TOKEN` meta tag | `X-CSRF-TOKEN` meta tag |

### Admin UI Section

Add to `admin/accounts/settings.blade.php` below existing Test Credits section:

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

Then 5 cards with the same structure as the customer portal but using admin design tokens:
- Headers: `var(--admin-primary)` navy blue (`#1e3a5f`)
- Cards: `.settings-card` pattern (already exists in this view)
- Each card loads data from the GET endpoint, saves via the corresponding PUT

---

## PART 3: Database Schema Reference

### account_settings table (columns added by migration)

| Column | Type | Default | Notes |
|--------|------|---------|-------|
| `message_retention_days` | integer | 180 | Valid: 30, 60, 90, 120, 150, 180 |
| `data_masking_config` | jsonb | `{"mask_mobile":false,...}` | 4 boolean fields |
| `owner_bypass_masking` | boolean | true | Owner/admin sees unmasked |
| `anti_flood_enabled` | boolean | false | Master toggle |
| `anti_flood_mode` | varchar | 'off' | enforce / monitor / off |
| `anti_flood_window_hours` | integer | 2 | Range: 2–48 |
| `out_of_hours_enabled` | boolean | false | Master toggle |
| `out_of_hours_start` | time | '21:00' | HH:MM format |
| `out_of_hours_end` | time | '08:00' | HH:MM format |
| `out_of_hours_action` | varchar | 'reject' | reject / hold |
| `ip_allowlist_enabled` | boolean | false | Master toggle |
| `timezone` | varchar | 'Europe/London' | Pre-existing column |

### account_ip_allowlist table

| Column | Type | Notes |
|--------|------|-------|
| `id` | uuid (PK) | Auto-generated |
| `tenant_id` | uuid (FK→accounts) | RLS key |
| `ip_address` | varchar(45) | IPv4, IPv6, or CIDR |
| `label` | varchar(100) | Nullable friendly name |
| `created_by` | uuid | User who added |
| `status` | varchar(20) | active / disabled |
| `created_at` / `updated_at` | timestamps | |

### held_messages table

| Column | Type | Notes |
|--------|------|-------|
| `id` | uuid (PK) | |
| `tenant_id` | uuid (FK→accounts) | RLS key |
| `recipient_number` | varchar(20) | E.164 |
| `message_content` | text | **Encrypted at rest** (Crypt::encryptString) |
| `sender_id` | varchar(15) | |
| `message_type` | varchar(20) | sms / rcs_basic / rcs_rich |
| `origin` | varchar(30) | portal / api / email_to_sms / campaign |
| `held_reason` | varchar(50) | |
| `status` | varchar(20) | held / released / expired / cancelled / failed |
| `release_after` | timestamp | When message can be released |
| `released_at` | timestamp | Nullable |

---

## PART 4: Backend Services — How Settings Are Consumed

### AntiFloodService (`app/Services/AntiFloodService.php`)
- **Called by:** Message send pipeline (Step 0b)
- **Checks:** Redis for duplicate `(content_hash + recipient_hash)` within window
- **Modes:** `enforce` = block send + return error | `monitor` = log but allow | `off` = skip
- **Fail behaviour:** Fail-open (Redis down = allow send)

### OutOfHoursService (`app/Services/OutOfHoursService.php`)
- **Called by:** Message send pipeline (Step 0a, before anti-flood)
- **Logic:** Pure time comparison in account timezone, handles overnight windows (21:00→08:00)
- **Actions:** `reject` = return error, caller retries | `hold` = store in held_messages, auto-release
- **Fail behaviour:** Fail-open (errors = allow send)

### DataMaskingService (`app/Services/DataMaskingService.php`)
- **Status:** Built but not yet wired into controllers (follow-up task)
- **Masks:** mobile → `077****0123`, content → `[REDACTED]`, timestamps → `18/03/2026 --:--`
- **Bypass:** Account owner + `owner_bypass_masking = true` sees unmasked data

### IpAllowlistService (`app/Services/IpAllowlistService.php`)
- **Called by:** `CustomerIpAllowlist` middleware on every authenticated request
- **Cache:** Redis (60s TTL) with DB fallback
- **CIDR support:** IPv4 and IPv6 range matching
- **Lockout protection:** Toggle auto-adds caller IP; removeIp blocks if last active IP

---

## PART 5: Bug Review Justifications

A prior code review flagged several issues. Here is the disposition of each, with evidence for verification:

### VALID — Fixed in this branch

| Issue | Fix Applied |
|-------|-------------|
| **held_messages.message_content stored as plaintext** | Added `Crypt::encryptString` in `saving` boot hook + `getDecryptedContentAttribute()` accessor to `HeldMessage.php`, matching the `InboxMessage` pattern at `app/Models/InboxMessage.php:73-77` |
| **PurgeExpiredMessages references message_logs table** | Added `Schema::hasTable('message_logs')` and `Schema::hasTable('message_dedup_log')` guards. The `message_logs` migration is a `.bak` file (`2024_12_30_000001_create_message_logs_table.php.bak`) so the table won't exist on fresh deploys |
| **removeIp has no last-IP lockout protection** | Added check in `SecuritySettingsController::removeIp()` (lines 410-424): if allowlist is enabled and this is the last active IP, returns 409 |
| **session()->flush() should be invalidate()** | Changed to `$request->session()->invalidate()` + `$request->session()->regenerateToken()` in `CustomerIpAllowlist.php` to prevent session fixation |
| **SendHeldMessage job doesn't actually send** | Changed from silently logging "released for delivery" to marking as `failed` with error-level log. Gateway dispatch is a follow-up integration |

### INVALID — With Evidence

#### 1. "permission:manage_security middleware doesn't exist"

**Verdict: INVALID. The permission system works correctly.**

**Evidence:**
- Permissions are stored in a JSONB `permission_toggles` column on the `users` table (migration: `database/migrations/2026_03_09_000002_expand_user_roles_permissions.php`, line 43)
- `manage_security` is defined in `User::ROLE_DEFAULT_PERMISSIONS` constant (`app/Models/User.php`, line 86) — set to `true` for Owner role, `false` for all other roles
- `CheckPermission` middleware (`app/Http/Middleware/CheckPermission.php`, lines 20-47) calls `$user->hasPermission($permission)` which checks the JSONB field
- Account owners bypass all permission checks entirely (line 32: `if ($user->isOwner()) return $next($request)`)
- The review assumed individual database columns per permission (like `manage_security boolean`). The actual system uses a single JSONB column with role-based defaults + per-user overrides. This is a more flexible design.

**How to verify:** Log in as an account owner → security settings API calls succeed. Log in as a non-owner user → API returns 403.

#### 2. "No validation on out_of_hours_start/out_of_hours_end time format"

**Verdict: INVALID. Validation already exists.**

**Evidence:** `SecuritySettingsController::updateOutOfHours()` at line 269-270:
```php
'start' => 'sometimes|date_format:H:i',
'end' => 'sometimes|date_format:H:i',
```
Laravel's `date_format:H:i` validation rejects invalid values like `"25:99"` or `"abc"`. Additionally, line 288 validates `start !== end`.

**How to verify:** Send `PUT /api/account/security/out-of-hours` with `"start": "25:99"` → returns 422 validation error.

#### 3. "No rate limiting on IP add/remove"

**Verdict: INVALID. Rate limiting already exists.**

**Evidence:** `routes/web.php` line 118:
```php
Route::prefix('api/account/security')
    ->middleware(['permission:manage_security', 'throttle:30,1'])
```
The `throttle:30,1` middleware applies to the entire security settings route group — all 10 endpoints including `addIp`, `removeIp`, `toggleIpAllowlist`. This limits to 30 requests per 1 minute per session.

**How to verify:** Send 31 rapid requests to any security endpoint → 31st returns 429 Too Many Requests.

#### 4. "Anti-flood window validation allows 0"

**Verdict: INVALID.**

**Evidence:** `SecuritySettingsController::updateAntiFlood()` at line 213:
```php
'window_hours' => 'required|integer|min:2|max:48',
```
`min:2` means 0 and 1 are rejected. The review text says "min:1" but the actual code says `min:2`.

**How to verify:** Send `PUT /api/account/security/anti-flood` with `"window_hours": 0` → returns 422.

#### 5. "toPortalArray() missing created_by"

**Verdict: INTENTIONAL DESIGN DECISION, not a bug.**

**Evidence:** `AccountIpAllowlist::toPortalArray()` (`app/Models/AccountIpAllowlist.php`, lines 65-74) deliberately excludes `created_by` (internal user UUID). This follows the GREEN zone trust boundary rule — internal UUIDs should not be exposed to the customer portal. The frontend should show "Added on {date}" using `created_at` instead.

#### 6. "DataMaskingService has no integration points"

**Verdict: TRUE but intentionally deferred.** The service is built and registered as a scoped singleton in `AppServiceProvider.php`. Integration into reporting controllers and export endpoints is a separate follow-up task — it requires changes across multiple controllers and was scoped out of this branch.

---

## PART 6: Design System Rules

### Customer Portal (GREEN zone)
- **Layout:** `layouts/quicksms.blade.php`
- **Primary colour:** `#886cc0` (purple)
- **Card pattern:** `.security-card` → `.security-card-header` + `.security-card-body`
- **Toggle style:** Bootstrap `.form-switch` with purple checked state
- **Icons:** Font Awesome 6
- **Toast notifications:** Use existing toast pattern or create simple one

### Admin Console (RED zone)
- **Layout:** `layouts/admin.blade.php`
- **Primary colour:** `--admin-primary: #1e3a5f` (navy blue)
- **Card pattern:** `.settings-card` → `.settings-card-header` + `.settings-card-body`
- **Existing tabs:** Details | Pricing | Billing | **Settings** (active)
- **Action pattern:** Ellipsis button → dropdown with action sections

### Hard Rules
- **No new layouts** — use existing ones
- **No new CSS colour palettes** — use Fillow design tokens
- **No new frontend frameworks** — Blade + vanilla JS only
- **All fetch() calls must check `response.ok`** before parsing JSON
- **Error states must be visible** — never silently swallow API failures
- **CSRF token required** on all POST/PUT/DELETE
- **PostgreSQL only** — no MySQL syntax

---

## PART 7: Implementation Checklist

### Customer Portal (wire existing view to existing API)
- [ ] On page load, call `GET /api/account/security/settings` to populate all 5 cards
- [ ] Card 1: Message Retention — dropdown (30/60/90/120/150/180) + save button
- [ ] Card 2: Data Masking — 4 field toggles + 1 bypass toggle + save button
- [ ] Card 3: Anti-Flood — enable toggle + mode selector (enforce/monitor) + window hours (2–48) + save
- [ ] Card 4: Out-of-Hours — enable toggle + start/end time pickers + action selector (reject/hold) + timezone display + save
- [ ] Card 5: IP Allowlist — enable toggle + current IP display + IP table + add form + delete buttons + limit counter
- [ ] Loading states (spinner/skeleton while fetching)
- [ ] Error handling on every fetch call (toast notifications)
- [ ] Disable controls for users without `manage_security` permission
- [ ] Disable save buttons during pending requests

### Admin Console — Backend (create new endpoints)
- [ ] Add 8 admin API endpoints to `AdminController.php`
- [ ] Add route group to `routes/web.php` inside admin middleware group
- [ ] Each PUT endpoint: validate, update via `DB::table()`, audit log
- [ ] GET endpoint: return same shape as customer portal

### Admin Console — Frontend (extend existing view)
- [ ] Add "Security Settings" section to `admin/accounts/settings.blade.php`
- [ ] Load on page init via `GET /admin/api/accounts/{accountId}/security-settings`
- [ ] 5 settings cards matching customer portal
- [ ] Admin can modify all settings for any account
- [ ] Admin can view/remove IPs but not add (customers add their own)
- [ ] Toast notifications for all save actions
