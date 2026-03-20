# MASTER PROMPT — Notification Centre UI Build

**Date:** 2026-03-19
**Branch:** `claude/review-alerting-engine-EonJJ`
**Prerequisite:** All backend code is merged and live on this branch. This prompt builds the UI only.

---

## 1. WHAT HAS BEEN BUILT (Backend — Complete)

The alerting engine was built across 8 commits (oldest to newest):

| # | Commit | Summary |
|---|--------|---------|
| 1 | `281c347` | Core engine: 77 files, +4929 lines. Created 5 Alerting models, 3 services, 9 jobs, 43 event classes, subscriber, contract, config, 7 migrations, seeder, 4 email templates, SSRF-safe webhook validator, 8 API controllers, all routes. |
| 2 | `628f658` | Review fix: transition gating on `once_per_breach`, primary DB `refresh()` after atomic `markTriggered()`, structured logging, test scaffolding. |
| 3 | `ebe4e38` | Wired alert dispatches into `SecuritySettingsController` and `AdminController` for 7 real event types (security setting, IP allowlist, MFA, password, account status, spam filter, API connection). |
| 4 | `1d235b9` | Fix: isolated event dispatch from DB transactions, captured old values before mutation, added observability logging. |
| 5 | `56a3be4` | Extracted `DispatchesAlertsSafely` trait, hardened old-value captures, wrapped all dispatches in try/catch. |
| 6 | `f15228a` | Created 6 missing SubAccount events, hardened routes with `throttle:60,1`, added `hasColumn()` guards to migration down(). |
| 7 | `2c572bc` | Fixed prompt accuracy (correct counts), removed phantom config key, fixed sub-account event categories. |
| 8 | `3124b50` | Security hardening: SSRF protection on webhook URLs, email recipient resolution from DB config only (not metadata), RLS migration for all 5 alerting tables, `toPortalArray()` on all GREEN-zone responses. |

### Evidence This Is Current (Not Stale)

**How to verify the backend matches HEAD:**

```bash
# 1. Confirm branch
git branch --show-current
# Expected: claude/review-alerting-engine-EonJJ

# 2. Confirm latest commit
git log --oneline -1
# Expected: 3124b502 Fix alerting engine review findings...

# 3. Confirm all alerting files exist
ls app/Models/Alerting/          # 5 files: AlertRule, AlertHistory, AlertChannelConfig, AlertPreference, NotificationBatch
ls app/Services/Alerting/        # 3 files: AlertDispatcherService, AlertEvaluatorService, AlertFrequencyService
ls app/Jobs/Alerting/            # 9 files: EvaluateAlertEventJob, CreateInAppNotificationJob, Send{Email,Sms,Slack,Teams,Webhook}AlertJob, DispatchBatchedAlertsJob, PlatformHealthCheckJob
ls app/Events/Alerting/          # 43 event files + BaseAlertEvent
ls database/migrations/*800*     # 7 migrations: 800001-800007

# 4. Confirm RLS is in place
grep -l "ENABLE ROW LEVEL SECURITY" database/migrations/*800007*
# Expected: 2026_03_19_800007_add_rls_to_alerting_tables.php

# 5. Confirm routes exist
grep -c "alerts" routes/web.php
# Expected: 20+ matches (customer + admin alert routes)

# 6. Confirm config
wc -l config/alerting.php
# Expected: ~550 lines
```

---

## 2. WHAT NEEDS TO BE BUILT (This Prompt)

Two Blade views — one per console — plus sidebar wiring and a header notification bell.

### 2A. Customer Portal: Notification Centre

**Location:** Account > Notification Centre
**Route name:** `account.notification-centre`
**View file:** `resources/views/quicksms/account/notification-centre.blade.php`
**Layout:** `@extends('layouts.quicksms')`
**Sidebar entry:** Add to `resources/views/elements/quicksms-sidebar.blade.php` under the `Account` section, after "Security Settings"

**What it contains (4 tabs):**

#### Tab 1: Notifications (default)
- Paginated list of in-app notifications from `GET /api/notifications`
- Each row: severity icon, title, body (truncated), category badge, timestamp, action button (deep_link)
- Severity colours: `critical` = red, `warning` = amber, `info` = blue
- Category badges using `config('alerting.categories')` labels
- "Mark all read" button → `POST /api/notifications/mark-all-read`
- Per-notification: mark read (`POST /api/notifications/{uuid}/read`), dismiss (`POST /api/notifications/{uuid}/dismiss`)
- Filter bar: category dropdown, severity dropdown, unread-only toggle
- Empty state: "No notifications yet" with icon
- Unread counter badge in sidebar nav item

#### Tab 2: Alert Rules
- List of alert rules from `GET /api/v1/alerts/rules`
- System defaults shown with lock icon (not deletable, but can toggle enabled/disabled)
- Custom rules can be created, edited, deleted
- Create/edit modal with fields:
  - Category (dropdown from `config('alerting.categories')`)
  - Trigger type (dropdown from `config('alerting.trigger_types')`)
  - Trigger key (text input)
  - Condition operator (dropdown from `config('alerting.condition_operators')`)
  - Condition value (number input)
  - Channels (multi-select checkboxes from `config('alerting.channels')`)
  - Frequency (dropdown from `config('alerting.frequencies')`)
  - Cooldown minutes (number input)
- Toggle enabled/disabled with PATCH-style `PUT /api/v1/alerts/rules/{id}`
- Delete custom rule: `DELETE /api/v1/alerts/rules/{id}`

#### Tab 3: Preferences
- Per-category channel preferences from `GET /api/v1/alerts/preferences`
- One row per category showing:
  - Category name and description
  - Channel checkboxes (which channels to receive for this category)
  - Mute toggle with optional "mute until" datetime
- Save: `PUT /api/v1/alerts/preferences` (per-category upsert)

#### Tab 4: Channels
- Account-level channel configurations from `GET /api/v1/alerts/channels`
- Cards for each configurable channel:
  - **Email:** Custom alert email address
  - **Webhook:** URL (SSRF-validated server-side), shows HMAC secret (masked, copy button)
  - **SMS:** Phone number for SMS alerts
  - **Slack:** Webhook URL
  - **Teams:** Webhook URL
- Enable/disable per channel
- Save: `PUT /api/v1/alerts/channels/{channel}`
- Delete: `DELETE /api/v1/alerts/channels/{channel}`

### 2B. Admin Console: Notification Centre

**Location:** Management > Notification Centre
**Route name:** `admin.management.notification-centre`
**View file:** `resources/views/admin/management/notification-centre.blade.php`
**Layout:** `@extends('layouts.admin')`
**Sidebar entry:** Add to `resources/views/elements/admin-sidebar.blade.php` under the Management section, after "Pricing"

**What it contains (4 tabs):**

#### Tab 1: Notifications (default)
- Admin notifications from `GET /admin/api/notifications`
- Same layout as customer but with additional:
  - "Resolve" action (`POST /admin/api/notifications/{uuid}/resolve`) for actionable alerts
  - Account identifier column (which customer triggered it)
  - Severity filter defaults to showing critical + warning

#### Tab 2: Alert Rules
- System-wide alert rules from `GET /admin/api/alerts/rules`
- Uses admin + standard categories (`config('alerting.admin_categories')` merged with `config('alerting.categories')`)
- Create/edit/delete system default rules
- Block deletion of `is_system_default` rules (backend enforced, show disabled delete button)

#### Tab 3: Alert History (Analytics)
- From `GET /admin/api/alerts/history` (paginated, filterable)
- Columns: timestamp, trigger_key, tenant (account name), severity, category, status, channels dispatched
- Filter bar: category, severity, status (dispatched/suppressed/batched/failed), date range
- Summary panel from `GET /admin/api/alerts/dashboard`:
  - Most triggered rules (top 5)
  - Alerts by severity (pie/donut chart data — render as simple stat cards)
  - Dispatched vs suppressed vs batched counts
  - Recent critical alerts list

#### Tab 4: System Configuration
- Read-only display of `config('alerting.*')` values for operator reference
- Shows: categories, admin_categories, channels, frequencies, trigger_types, condition_operators, queue names, webhook config, batch schedule
- No edit — config changes require code deployment

### 2C. Header Notification Bell

**Both consoles** need a notification bell icon in the header bar:

**Customer header** (`resources/views/elements/header.blade.php`):
- Bell icon with unread count badge
- Clicking opens a dropdown with latest 5 unread notifications
- "View all" link → routes to `account.notification-centre`
- Poll for unread count every 60 seconds OR on page load only

**Admin header** (`resources/views/elements/admin-header.blade.php`):
- Same pattern but using admin notification endpoints
- "View all" link → routes to `admin.management.notification-centre`
- Show severity-coloured dot for critical unread notifications

---

## 3. EXISTING API ENDPOINTS (All Already Wired)

### Customer Portal (GREEN zone)

| Method | Endpoint | Controller | Purpose |
|--------|----------|------------|---------|
| GET | `/api/notifications` | `NotificationController@index` | Paginated notifications |
| POST | `/api/notifications/mark-all-read` | `NotificationController@markAllRead` | Mark all read |
| POST | `/api/notifications/{uuid}/read` | `NotificationController@markRead` | Mark one read |
| POST | `/api/notifications/{uuid}/dismiss` | `NotificationController@dismiss` | Dismiss one |
| GET | `/api/v1/alerts/rules` | `AlertRuleController@index` | List rules |
| POST | `/api/v1/alerts/rules` | `AlertRuleController@store` | Create rule |
| GET | `/api/v1/alerts/rules/{id}` | `AlertRuleController@show` | Get rule |
| PUT | `/api/v1/alerts/rules/{id}` | `AlertRuleController@update` | Update rule |
| DELETE | `/api/v1/alerts/rules/{id}` | `AlertRuleController@destroy` | Delete rule |
| GET | `/api/v1/alerts/history` | `AlertHistoryController@index` | Alert history |
| GET | `/api/v1/alerts/history/summary` | `AlertHistoryController@summary` | Alert summary stats |
| GET | `/api/v1/alerts/preferences` | `AlertPreferenceController@index` | List preferences |
| PUT | `/api/v1/alerts/preferences` | `AlertPreferenceController@update` | Update preference |
| GET | `/api/v1/alerts/channels` | `AlertChannelController@index` | List channel configs |
| PUT | `/api/v1/alerts/channels/{channel}` | `AlertChannelController@update` | Update channel |
| DELETE | `/api/v1/alerts/channels/{channel}` | `AlertChannelController@destroy` | Delete channel |

### Admin Console (RED zone)

| Method | Endpoint | Controller | Purpose |
|--------|----------|------------|---------|
| GET | `/admin/api/notifications` | `AdminNotificationController@index` | Paginated admin notifications |
| POST | `/admin/api/notifications/mark-all-read` | `AdminNotificationController@markAllRead` | Mark all read |
| POST | `/admin/api/notifications/{uuid}/read` | `AdminNotificationController@markRead` | Mark one read |
| POST | `/admin/api/notifications/{uuid}/dismiss` | `AdminNotificationController@dismiss` | Dismiss one |
| POST | `/admin/api/notifications/{uuid}/resolve` | `AdminNotificationController@resolve` | Resolve actionable alert |
| GET | `/admin/api/alerts/rules` | `AdminAlertRuleController@index` | List system rules |
| POST | `/admin/api/alerts/rules` | `AdminAlertRuleController@store` | Create system rule |
| PUT | `/admin/api/alerts/rules/{id}` | `AdminAlertRuleController@update` | Update system rule |
| DELETE | `/admin/api/alerts/rules/{id}` | `AdminAlertRuleController@destroy` | Delete system rule |
| GET | `/admin/api/alerts/history` | `AdminAlertRuleController@history` | Cross-tenant history |
| GET | `/admin/api/alerts/dashboard` | `AdminAlertRuleController@dashboard` | Analytics dashboard |

---

## 4. EXISTING DATA STRUCTURES

### config/alerting.php Categories (use these exact keys)

**Customer categories:**
```
billing        → "Account & Billing"
messaging      → "Messaging Performance"
compliance     → "Compliance & Registration"
security       → "Security & Access"
system         → "System & Integration"
campaign       → "Campaign & Flows"
sub_account    → "Sub-Account Caps & Limits"
```

**Admin categories:**
```
fraud              → "Spam & Fraud"
platform_health    → "Platform Health"
customer_risk      → "Customer Risk"
commercial         → "Commercial & Billing Risk"
compliance_legal   → "Compliance & Legal"
```

### Channels
```
email, in_app, webhook, sms, slack, teams
```

### Frequencies
```
instant         → "Instant"
batched_15m     → "Every 15 minutes"
batched_1h      → "Every hour"
daily_digest    → "Daily summary"
once_per_breach → "Once per breach"
```

### Severity Levels & Colours
```
critical → Red    (#dc3545 / .badge-danger)
warning  → Amber  (#ffc107 / .badge-warning)
info     → Blue   (#17a2b8 / .badge-info)
```

### Notification Model Fields (returned by API)
```json
{
    "uuid": "string",
    "type": "string",
    "severity": "critical|warning|info",
    "category": "string",
    "title": "string",
    "body": "string",
    "deep_link": "string|null",
    "action_url": "string|null",
    "action_label": "string|null",
    "read_at": "iso8601|null",
    "dismissed_at": "iso8601|null",
    "created_at": "iso8601"
}
```

### AlertRule Model Fields (returned by API)
```json
{
    "id": "uuid",
    "category": "string",
    "trigger_type": "threshold|percentage_change|absolute_change|event",
    "trigger_key": "string",
    "condition_operator": "lt|gt|lte|gte|eq|drops_by|increases_by",
    "condition_value": "decimal|null",
    "channels": ["in_app", "email"],
    "frequency": "instant|batched_15m|batched_1h|daily_digest|once_per_breach",
    "cooldown_minutes": 60,
    "escalation_rules": [{"condition_value": 95, "channels": ["sms"]}],
    "is_enabled": true,
    "is_system_default": false,
    "created_at": "iso8601",
    "updated_at": "iso8601"
}
```

---

## 5. HARD GUARDRAILS — Do Not Violate

### Files You MUST NOT Modify

These files are complete and tested. Do not touch them:

| File | Reason |
|------|--------|
| `config/alerting.php` | Fully configured — 550 lines, 24 customer defaults, 8 admin defaults |
| `app/Models/Alerting/*.php` | All 5 models complete with scopes, casts, `toPortalArray()` |
| `app/Services/Alerting/*.php` | All 3 services complete — evaluator, dispatcher, frequency |
| `app/Jobs/Alerting/*.php` | All 9 jobs complete and wired to queues |
| `app/Events/Alerting/*.php` | All 43 events + BaseAlertEvent complete |
| `app/Listeners/Alerting/AlertEventSubscriber.php` | Subscriber complete, wired in EventServiceProvider |
| `app/Contracts/AlertableEvent.php` | Interface complete — 8 methods |
| `app/Traits/DispatchesAlertsSafely.php` | Trait complete — used by SecuritySettingsController and AdminController |
| `app/Validation/WebhookUrlValidator.php` | SSRF protection — do not weaken |
| `app/Http/Controllers/Api/V1/Alert*.php` | All 4 customer API controllers complete |
| `app/Http/Controllers/Api/V1/BalanceAlertController.php` | Complete |
| `app/Http/Controllers/Admin/AdminAlertRuleController.php` | Complete |
| `app/Http/Controllers/Admin/AdminNotificationController.php` | Complete |
| `app/Http/Controllers/NotificationController.php` | Complete |
| `database/migrations/2026_03_17_800001_*` through `800007_*` | All 7 migrations — NEVER rename, delete, or modify |
| `database/seeders/AlertDefaultsSeeder.php` | Complete |
| `resources/views/emails/alerts/*.blade.php` | All 4 email templates complete |
| `routes/web.php` (alerting routes sections) | All notification and alert routes already registered |

### CLAUDE.md Rules That Apply

1. **No new layouts** — use `layouts.quicksms` for customer portal, `layouts.admin` for admin console
2. **No new CSS colour palettes** — use Fillow design system tokens already in the codebase
3. **No new frontend frameworks** — Blade + vanilla JS only. No React, Vue, Alpine.js
4. **All `fetch()` calls must check `response.ok`** before parsing JSON
5. **Error states must be visible** — never silently swallow API failures into empty UI
6. **Admin tables use the action menu pattern** (ellipsis button → dropdown)
7. **Status badges:** use Bootstrap badge classes (`.badge-danger`, `.badge-warning`, `.badge-info`)
8. **CSRF token:** include `X-CSRF-TOKEN` header on all `fetch()` calls:
   ```javascript
   headers: {
       'Content-Type': 'application/json',
       'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
   }
   ```
9. **GREEN zone responses** already use `toPortalArray()` — do not add `tenant_id` or internal fields to the UI
10. **Pagination:** API endpoints return Laravel-style `{ data: [], current_page, last_page, per_page, total }` — use this for page controls

### Files You MUST Modify

| File | Change |
|------|--------|
| `resources/views/elements/quicksms-sidebar.blade.php` | Add "Notification Centre" under Account section, after Security Settings (line ~98) |
| `resources/views/elements/admin-sidebar.blade.php` | Add "Notification Centre" under Management section, after Pricing (line ~56) |
| `resources/views/elements/header.blade.php` | Add notification bell dropdown to customer header |
| `resources/views/elements/admin-header.blade.php` | Add notification bell dropdown to admin header |
| `routes/web.php` | Add 2 route entries for the Notification Centre pages (view routes only — API routes already exist) |

### Files You MUST Create

| File | Content |
|------|---------|
| `resources/views/quicksms/account/notification-centre.blade.php` | Customer portal Notification Centre (4 tabs) |
| `resources/views/admin/management/notification-centre.blade.php` | Admin console Notification Centre (4 tabs) |

### Route Additions Required

Add to `routes/web.php`:

**Customer portal** (inside the existing customer auth middleware group):
```php
Route::get('/account/notification-centre', function () {
    return view('quicksms.account.notification-centre');
})->name('account.notification-centre');
```

**Admin console** (inside the existing admin auth middleware group):
```php
Route::get('/management/notification-centre', function () {
    return view('admin.management.notification-centre');
})->name('admin.management.notification-centre');
```

---

## 6. ANTI-DRIFT RULES — Stay On Track

### Do NOT

- Do not create new API endpoints — all endpoints exist and are tested
- Do not create new controllers — all controllers exist
- Do not create new models, services, jobs, or events
- Do not modify any migration file
- Do not add new npm packages or JS libraries
- Do not create new CSS files — use inline styles or existing Fillow classes
- Do not add WebSocket/Pusher — use simple polling or page-load-only fetching
- Do not create separate Blade partials for each tab — keep all tabs in a single view per console
- Do not add new config keys to `config/alerting.php`
- Do not change the alerting categories, frequencies, or channel lists
- Do not add `tenant_id` to any customer-facing API call — RLS handles scoping automatically
- Do not bypass the existing middleware stack (`customer.auth`, `customer.ip_allowlist`)

### Do

- Use the existing `public/css/quicksms-pastel.css` for any QuickSMS-specific styling needs
- Use Bootstrap 5 classes from the Fillow design system
- Use `fetch()` with proper error handling for all API calls
- Show loading spinners during API calls
- Show toast/alert messages for success and error states
- Use `data-nav` and `data-subnav` attributes on sidebar items for the active-state JS
- Follow the tab pattern used in `resources/views/quicksms/account/security.blade.php` (Bootstrap nav-tabs)
- Match the table styling used in `resources/views/admin/security/country-controls.blade.php`
- Use `formatDistanceToNow`-style relative timestamps (e.g. "5 minutes ago") — implement in vanilla JS

---

## 7. VERIFICATION CHECKLIST

After building, verify:

1. **Customer Portal:**
   - [ ] Navigate to Account > Notification Centre — page loads without errors
   - [ ] Notifications tab: fetches and displays notifications, mark read/dismiss works
   - [ ] Alert Rules tab: lists system defaults + custom rules, create/edit/delete works
   - [ ] Preferences tab: shows all 7 categories, channel toggles save correctly
   - [ ] Channels tab: can configure webhook/email/sms/slack/teams
   - [ ] Header bell: shows unread count, dropdown shows latest 5
   - [ ] Browser console: no JS errors, no failed fetch() calls

2. **Admin Console:**
   - [ ] Navigate to Management > Notification Centre — page loads without errors
   - [ ] Notifications tab: fetches admin notifications, resolve action works
   - [ ] Alert Rules tab: lists system rules, can create admin category rules
   - [ ] History tab: paginated history with filters, summary stats display
   - [ ] Config tab: shows read-only config values
   - [ ] Header bell: shows unread count with severity colouring

3. **Sidebar:**
   - [ ] Customer sidebar: "Notification Centre" appears under Account
   - [ ] Admin sidebar: "Notification Centre" appears under Management
   - [ ] Active state highlights correctly on both

4. **Security:**
   - [ ] All fetch() calls include CSRF token
   - [ ] All fetch() calls check `response.ok`
   - [ ] No `tenant_id` exposed in customer-facing UI
   - [ ] No RED-zone data leaking through GREEN-zone views

---

## 8. REFERENCE FILES (Read Before Building)

Read these files to match existing patterns:

| File | Pattern to Match |
|------|-----------------|
| `resources/views/quicksms/account/security.blade.php` | Tab layout, fetch() pattern, error handling |
| `resources/views/admin/security/country-controls.blade.php` | Admin table pattern, action menus, status badges |
| `resources/views/elements/quicksms-sidebar.blade.php` | Sidebar item structure with `data-nav`/`data-subnav` |
| `resources/views/elements/admin-sidebar.blade.php` | Admin sidebar structure with badge counts |
| `resources/views/elements/header.blade.php` | Header bar structure for bell placement |
| `resources/views/elements/admin-header.blade.php` | Admin header bar structure |
| `public/css/quicksms-pastel.css` | Available QuickSMS-specific CSS classes |
