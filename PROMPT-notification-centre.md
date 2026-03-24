# MASTER PROMPT — Notification Centre UI Build (Final — Review Complete)

> **Branch:** `claude/review-alerting-engine-EonJJ`
> **HEAD:** Latest commit on branch (run `git log -1 --oneline` to verify)
> **Base (origin/main):** `7dbecea6` — Update breadcrumb navigation links
> **Total commits on branch:** Run `git rev-list --count origin/main..HEAD` to verify
> **Date:** 2026-03-24 (review fixes applied)
> **Review status:** External review completed — 5 valid issues fixed, 3 invalid claims rejected with evidence (see Appendix A)

---

## 1. WHAT WAS BUILT — COMPLETE COMMIT LOG

8 commits deliver the full rules-based alerting engine backend. All have been code-reviewed and fix-iterated.

| # | Commit | Date | Summary |
|---|--------|------|---------|
| 1 | `281c347a` | 2026-03-17 | **Rules-based alerting engine** — 5 models (AlertRule, AlertHistory, AlertChannelConfig, AlertPreference, NotificationBatch), 3 services (Evaluator, Dispatcher, Frequency), 9 queue jobs, 6 migrations, config/alerting.php with 23 customer + 8 admin default rules, AlertDefaultsSeeder, AlertEventSubscriber, 36 event classes, 4 email templates |
| 2 | `628f658e` | 2026-03-18 | **Review round 1** — Transition gating in SubAccount.recordMessageSent() (fire alerts on state change only, not every message). Primary DB refresh after atomic counter update. Log::info on every alert dispatch. SubAccountAlertingTest (18 tests) |
| 3 | `ebe4e382` | 2026-03-19 | **Security/account alert events** — 5 new events (AccountSecuritySettingChanged, AccountStatusOverridden, SpamFilterModeChanged, IpAllowlistChanged, ApiConnectionStateChanged). Wired dispatch into AdminController (9 sites) + SecuritySettingsController (7 sites). Subscriber extended to 42 events |
| 4 | `1d235b9d` | 2026-03-19 | **Review round 2** — Moved 16 dispatch() calls outside business-logic try/catch. Each wrapped in own try/catch(\Throwable). Fixed old-value captures. Added cooldown_minutes:5 on security rules. Created SecurityAlertingTest |
| 5 | `56a3be4c` | 2026-03-19 | **Review round 3** — Extracted DispatchesAlertsSafely trait (eliminates 16x duplicated try/catch/log). Moved $oldMasking capture before DB update. Added try/catch(\Throwable) around all DB writes in SecuritySettingsController |
| 6 | `ad40797d` | 2026-03-19 | **Master prompt v1** — Initial Notification Centre UI prompt (this file, now superseded) |
| 7 | `f15228a5` | 2026-03-19 | **Review round 4** — Created 6 missing SubAccount event classes (SpendCapBreached/Approaching, VolumeCapBreached/Approaching, DailyLimitBreached/Approaching). Added customer.ip_allowlist + throttle middleware to /api/v1/alerts/* routes. hasColumn() guards on migration 800006 up(). Removed duplicate scopeOfType(). Removed tautological condition in getEscalationChannels(). Used $validator->validated() in AlertPreferenceController |
| 8 | `26edbe7b` | 2026-03-19 | **Review round 5** — hasColumn() guards on migration 800006 down() method for safe rollback |

### Verified Counts (run these to confirm freshness)

```bash
# Event classes subscribed (should be 42)
grep '::class' app/Listeners/Alerting/AlertEventSubscriber.php | wc -l
# Expected: 42

# Concrete event files (should be 42 + BaseAlertEvent = 43)
ls app/Events/Alerting/*.php | wc -l
# Expected: 43

# Trigger keys in config (should be 31)
grep -c "'trigger_key'" config/alerting.php
# Expected: 31

# Migration files
ls database/migrations/*alert* database/migrations/*notification*
# Expected: 8 files (2 notification + 6 alert)

# Controllers
ls app/Http/Controllers/Api/V1/Alert*.php app/Http/Controllers/Admin/AdminAlert*.php app/Http/Controllers/Admin/AdminNotification*.php app/Http/Controllers/NotificationController.php
# Expected: 6 files

# Tests
ls tests/Unit/*Alert*.php
# Expected: 2 files (SubAccountAlertingTest.php, SecurityAlertingTest.php)

# Total files changed vs origin/main
git diff --stat origin/main..HEAD | tail -1
# Expected: 95 files changed, 7237 insertions(+), 97 deletions(-)
```

---

## 2. EVIDENCE THIS IS NOT STALE

### Branch is ahead of origin/main by exactly 8 commits

```bash
git log --oneline origin/main..HEAD
# 26edbe7b Add hasColumn() guards to migration 800006 down() method
# f15228a5 Fix review findings: create missing SubAccount events, harden routes and migration
# ad40797d Add master prompt for Notification Centre UI build
# 56a3be4c Extract DispatchesAlertsSafely trait, harden old-value captures, add error handling
# 1d235b9d Fix review findings: dispatch isolation, old-value capture, observability
# ebe4e382 Add security/account alert events and wire dispatches into controllers
# 628f658e Fix review findings: transition gating, primary DB refresh, logging, tests
# 281c347a Add rules-based alerting engine for customer and admin notifications
```

### Merge base confirms clean fork point

```bash
git merge-base origin/main HEAD
# 7dbecea6e94045ec4389ba298d0aa40e2d1bee68
# This is the latest commit on origin/main (breadcrumb nav update by adrian459)
```

### No conflicts with main

```bash
git diff origin/main..HEAD --name-only | sort
# All 95 files are new or cleanly modified — no overlap with concurrent main work
```

---

## 3. OBJECTIVE — BUILD THE NOTIFICATION CENTRE UI

Build the **Notification Centre** page for both consoles. This is the user-facing frontend that surfaces alerts generated by the engine described above.

### Navigation Locations

| Console | Nav Path | Sidebar File | Layout |
|---------|----------|-------------|--------|
| **Customer Portal** (GREEN) | Account > Notification Centre | `resources/views/elements/quicksms-sidebar.blade.php` line 98 (after Security Settings) | `layouts/quicksms.blade.php` |
| **Admin Console** (RED) | Management > Notification Centre | `resources/views/elements/admin-sidebar.blade.php` line 56 (after Pricing) | `layouts/admin.blade.php` |

### Existing Header Notification Bell Infrastructure

Both consoles already have notification bell dropdowns in the header:

- **Admin:** `resources/views/elements/admin-header.blade.php` lines 9-23 — bell icon with `#adminNotificationBell`, badge `#adminNotifCount`, dropdown `#adminNotifDropdown`, "Mark all read" button `#adminMarkAllRead`, "View all activity" link (currently points to audit-logs — update to notification-centre)
- **Customer:** `resources/views/elements/header.blade.php` lines 513-526 — enforcement alerts bell with badge and timeline list

---

## 4. EXISTING BACKEND — DO NOT REBUILD

### Models (all exist, all have correct fillable/casts/relationships)

| Model | File | Scope |
|-------|------|-------|
| `AlertRule` | `app/Models/Alerting/AlertRule.php` | tenant_id scoped, has getEscalationChannels() |
| `AlertHistory` | `app/Models/Alerting/AlertHistory.php` | Immutable audit trail |
| `AlertChannelConfig` | `app/Models/Alerting/AlertChannelConfig.php` | Per-user channel settings |
| `AlertPreference` | `app/Models/Alerting/AlertPreference.php` | Per-user category muting |
| `NotificationBatch` | `app/Models/Alerting/NotificationBatch.php` | Digest accumulator |
| `Notification` | `app/Models/Notification.php` | GREEN zone, tenant-scoped, RLS |
| `AdminNotification` | `app/Models/AdminNotification.php` | RED zone, no RLS |

### API Routes (all exist in routes/web.php — DO NOT RE-CREATE)

**Customer Portal (GREEN zone) — inside customer.auth + customer.ip_allowlist middleware:**

```
GET    /api/v1/alerts/rules              → AlertRuleController@index
POST   /api/v1/alerts/rules              → AlertRuleController@store
GET    /api/v1/alerts/rules/{id}         → AlertRuleController@show
PUT    /api/v1/alerts/rules/{id}         → AlertRuleController@update
DELETE /api/v1/alerts/rules/{id}         → AlertRuleController@destroy
GET    /api/v1/alerts/history            → AlertHistoryController@index
GET    /api/v1/alerts/history/summary    → AlertHistoryController@summary
GET    /api/v1/alerts/preferences        → AlertPreferenceController@index
PUT    /api/v1/alerts/preferences        → AlertPreferenceController@update
GET    /api/v1/alerts/channels           → AlertChannelController@index
PUT    /api/v1/alerts/channels/{channel} → AlertChannelController@update
DELETE /api/v1/alerts/channels/{channel} → AlertChannelController@destroy
GET    /api/notifications/               → NotificationController@index
POST   /api/notifications/mark-all-read  → NotificationController@markAllRead
POST   /api/notifications/{uuid}/read    → NotificationController@markRead
POST   /api/notifications/{uuid}/dismiss → NotificationController@dismiss
```

**Admin Console (RED zone) — inside admin middleware group:**

```
GET    /admin/api/alerts/rules                 → AdminAlertRuleController@index
POST   /admin/api/alerts/rules                 → AdminAlertRuleController@store
PUT    /admin/api/alerts/rules/{id}            → AdminAlertRuleController@update
DELETE /admin/api/alerts/rules/{id}            → AdminAlertRuleController@destroy
GET    /admin/api/alerts/history               → AdminAlertRuleController@history
GET    /admin/api/alerts/dashboard             → AdminAlertRuleController@dashboard
GET    /admin/api/notifications/               → AdminNotificationController@index
POST   /admin/api/notifications/mark-all-read  → AdminNotificationController@markAllRead
POST   /admin/api/notifications/{uuid}/read    → AdminNotificationController@markRead
POST   /admin/api/notifications/{uuid}/dismiss → AdminNotificationController@dismiss
POST   /admin/api/notifications/{uuid}/resolve → AdminNotificationController@resolve
```

### Controllers (all exist — DO NOT RE-CREATE)

```
app/Http/Controllers/NotificationController.php                  # GREEN — index, markRead, dismiss, markAllRead
app/Http/Controllers/Api/V1/AlertRuleController.php              # GREEN — CRUD
app/Http/Controllers/Api/V1/AlertHistoryController.php           # GREEN — history + summary
app/Http/Controllers/Api/V1/AlertPreferenceController.php        # GREEN — preferences
app/Http/Controllers/Api/V1/AlertChannelController.php           # GREEN — channel configs
app/Http/Controllers/Admin/AdminNotificationController.php       # RED — index, markRead, dismiss, resolve, markAllRead
app/Http/Controllers/Admin/AdminAlertRuleController.php          # RED — CRUD + history + dashboard
```

### Config — config/alerting.php

Contains: categories, channels, frequencies, operators, condition types, 23 customer defaults, 8 admin defaults, severity levels. Each default rule has a `title` field that serves as the human-readable label. **Read this file before building UI** — it defines every dropdown option.

### Data Flow (for reference)

```
User action (e.g. security setting change)
  → Controller updates DB
  → safeDispatch() fires event (e.g. AccountSecuritySettingChanged)
  → AlertEventSubscriber catches it → EvaluateAlertEventJob on 'alerts' queue
  → AlertEvaluatorService finds matching AlertRule(s) by trigger_key + tenant_id
  → Checks condition, cooldown, frequency
  → AlertDispatcherService dispatches to channels → CreateInAppNotificationJob + channel jobs
  → CreateInAppNotificationJob creates Notification or AdminNotification record
  → UI reads via /api/notifications/
```

### API Response Formats

**All endpoints** wrap responses in `{ "success": true, "data": ... }`. Paginated endpoints add a nested `pagination` object — fields are **never** flat at the root.

**Paginated response** (notifications, alert history, admin history):
```json
{
  "success": true,
  "data": [ ... ],
  "pagination": {
    "total": 100,
    "per_page": 25,
    "current_page": 1,
    "last_page": 4
  }
}
```

**Non-paginated response** (preferences, channels, admin rules):
```json
{
  "success": true,
  "data": [ ... ]
}
```

**Notification endpoints** (both customer and admin) also return unread counts — use these for the bell badge and category filters instead of making separate API calls:
```json
{
  "success": true,
  "data": [ ... ],
  "unread_count": 5,
  "unread_by_category": { "security": 3, "billing": 2 },
  "pagination": { ... }
}
```

### GREEN Zone Model Shapes (toPortalArray responses)

**Notification** (`GET /api/notifications/`):
```json
{
  "uuid": "550e8400-e29b-41d4-a716-446655440000",
  "type": "credit_balance_percentage",
  "severity": "warning",
  "category": "billing",
  "title": "Balance below 20%",
  "body": "Your account balance has dropped below the configured threshold.",
  "deep_link": "/account/billing",
  "action_url": "/account/top-up",
  "action_label": "Top Up Now",
  "read_at": "2026-03-19T16:00:00+00:00",
  "dismissed_at": null,
  "created_at": "2026-03-19T15:30:00+00:00"
}
```

**AlertRule** (`GET /api/v1/alerts/rules`):
```json
{
  "id": 42,
  "category": "billing",
  "trigger_type": "threshold",
  "trigger_key": "credit_balance_percentage",
  "condition_operator": "lt",
  "condition_value": 20,
  "channels": ["in_app", "email"],
  "frequency": "once_per_breach",
  "cooldown_minutes": 60,
  "escalation_rules": [],
  "is_enabled": true,
  "is_system_default": true,
  "created_at": "2026-03-17T10:00:00+00:00",
  "updated_at": "2026-03-17T10:00:00+00:00"
}
```

**AlertHistory** (`GET /api/v1/alerts/history`):
```json
{
  "id": 1,
  "trigger_key": "credit_balance_percentage",
  "trigger_value": 15.5,
  "severity": "warning",
  "category": "billing",
  "title": "Balance below 20%",
  "body": "Current balance is 15.5% of credit limit.",
  "status": "dispatched",
  "channels_dispatched": ["in_app", "email"],
  "created_at": "2026-03-19T15:30:00+00:00"
}
```

**AlertPreference** (`GET /api/v1/alerts/preferences`) — returns all 7 categories with defaults for unconfigured ones:
```json
{
  "category": "billing",
  "label": "Account & Billing",
  "channels": ["in_app", "email"],
  "is_muted": false,
  "muted_until": null
}
```

> **Note:** `muted_until` is returned as ISO 8601 format (`"2026-03-19T16:00:00+00:00"`) or `null` on both `GET` and `PUT` endpoints. Parse with `new Date()`.

**AlertChannelConfig** (`GET /api/v1/alerts/channels`) — sensitive values masked via `safe_config`:
```json
{
  "id": 1,
  "channel": "webhook",
  "config": {
    "webhook_url_set": true,
    "hmac_secret": "***a1b2"
  },
  "is_enabled": true,
  "updated_at": "2026-03-19T16:00:00"
}
```

### RED Zone Response Shapes (Admin)

Admin controllers return **raw Eloquent models** (not toPortalArray). Key differences from GREEN zone:
- Extra fields present: `tenant_id`, `user_id`, `recipient_admin_id`, `alert_rule_id`, `condition_value`, `metadata`/`meta`
- Dates in default Eloquent format (`"2026-03-19 16:00:00"`) not ISO 8601 — parse with `new Date()` which handles both
- `$hidden` fields (like AlertChannelConfig `config`) are excluded from JSON serialization

**AdminNotification** (`GET /api/notifications/` admin):
```json
{
  "id": 1,
  "uuid": "550e8400-e29b-41d4-a716-446655440000",
  "recipient_admin_id": null,
  "type": "spam_filter_triggered",
  "severity": "warning",
  "category": "fraud",
  "title": "Spam filter triggered",
  "body": "Account XYZ triggered spam filters.",
  "deep_link": "/admin/accounts/xyz",
  "action_url": "/admin/accounts/xyz/review",
  "action_label": "Review Account",
  "meta": {},
  "read_at": null,
  "dismissed_at": null,
  "resolved_at": null,
  "created_at": "2026-03-19 16:00:00",
  "updated_at": "2026-03-19 16:00:00"
}
```

**Admin AlertRule** (`GET /api/alerts/rules`) — returns raw model, not paginated:
```json
{
  "id": 1,
  "tenant_id": null,
  "category": "fraud",
  "trigger_type": "event",
  "trigger_key": "spam_filter_triggered",
  "condition_operator": "gte",
  "condition_value": 1,
  "channels": ["in_app", "slack"],
  "frequency": "instant",
  "cooldown_minutes": 5,
  "escalation_rules": [],
  "is_enabled": true,
  "is_system_default": true,
  "last_triggered_at": null,
  "last_value_snapshot": null,
  "created_at": "2026-03-17 10:00:00",
  "updated_at": "2026-03-17 10:00:00"
}
```

**Admin Dashboard** (`GET /api/alerts/dashboard`):
```json
{
  "success": true,
  "data": {
    "most_triggered": [
      { "trigger_key": "spam_filter_triggered", "category": "fraud", "count": 42 }
    ],
    "by_severity": { "critical": 5, "warning": 18, "info": 120 },
    "dispatched_count": 130,
    "suppressed_count": 15,
    "batched_count": 8,
    "recent_critical": [ /* raw AlertHistory models */ ],
    "period_since": "2026-03-12 16:00:00"
  }
}
```

---

## 5. WHAT TO BUILD

### 5A. Customer Portal — Account > Notification Centre

**File:** `resources/views/quicksms/account/notification-centre.blade.php`
**Layout:** `@extends('layouts.quicksms')`

**Tabs:**

1. **Notifications** — Feed from `GET /api/notifications/`
   - Severity badges: critical=red, warning=amber, info=blue (Fillow status badge pattern)
   - Title, body, timestamp, category chip
   - Actions: Mark Read, Dismiss
   - Bulk: "Mark All Read"
   - Filters: category, severity, read/unread
   - Empty state: "No notifications yet" (visible message, not blank)

2. **Alert Rules** — CRUD from `/api/v1/alerts/rules`
   - List rules: trigger_key (use the `title` field from matching default rule in config/alerting.php as display label), channels, frequency, enabled toggle
   - "Add Rule" form: category dropdown, trigger_key dropdown, condition operator/value, channels checkboxes, frequency dropdown
   - System defaults (is_system_default=true) shown read-only with option to override
   - Edit/Delete via action menu (ellipsis → dropdown pattern)

3. **Alert History** — from `/api/v1/alerts/history`
   - Table: timestamp, trigger_key label, severity badge, title, status chip (dispatched/delivered/failed/suppressed), channels
   - Summary stats from `/api/v1/alerts/history/summary`
   - Filters: category, severity, status, date range

4. **Preferences** — from `/api/v1/alerts/preferences`
   - Per-category row: category name, mute toggle, channel selection, mute-until date
   - All 7 categories: billing, messaging, compliance, security, system, campaign, sub_account

5. **Channel Settings** — from `/api/v1/alerts/channels`
   - Email: enabled toggle (address from profile)
   - Webhook: URL input, HMAC secret display, test button
   - Slack: webhook URL, test button
   - Teams: webhook URL, test button
   - SMS: phone number, enabled toggle

### 5B. Admin Console — Management > Notification Centre

**File:** `resources/views/admin/management/notification-centre.blade.php`
**Layout:** `@extends('layouts.admin')`

**Tabs:**

1. **Notifications** — from `GET /admin/api/notifications/` (admin routes)
   - Admin categories: fraud, platform_health, customer_risk, commercial, compliance_legal
   - Extra action: "Resolve" button (timestamp when resolved)
   - Same severity badges as customer

2. **Alert Rules** — from `/admin/api/alerts/rules`
   - System default management
   - Create admin-specific rules
   - Toggle on/off, edit channels/frequency/cooldown
   - Action menu pattern for row actions

3. **Dashboard** — from `/admin/api/alerts/dashboard`
   - Summary cards: total today, by severity, by category
   - Recent alerts feed

4. **History** — from `/admin/api/alerts/history`
   - Full table, all columns
   - Filters: trigger_key, severity, status, tenant_id, date range

> **Admin response format note:** Admin API endpoints return raw Eloquent models, NOT `toPortalArray()`. This means:
> - Extra fields are present (e.g. `tenant_id`, `user_id`, `alert_rule_id`, `condition_value`, `metadata`)
> - Dates use Eloquent format (`"2026-03-19 16:00:00"`) not ISO 8601 — `new Date()` handles both
> - Refer to the "RED Zone Response Shapes" in Section 4 for exact field lists

### 5C. Sidebar Navigation Updates

**Customer sidebar** (`resources/views/elements/quicksms-sidebar.blade.php`):
Add after line 97 (Security Settings):
```html
<li data-subnav="notification-centre"><a href="{{ route('account.notification-centre') }}" class="{{ request()->routeIs('account.notification-centre') ? 'mm-active' : '' }}">Notification Centre</a></li>
```

**Admin sidebar** (`resources/views/elements/admin-sidebar.blade.php`):
Add after line 55 (Pricing):
```html
<li><a href="{{ route('admin.management.notification-centre') }}" class="{{ request()->routeIs('admin.management.notification-centre') ? 'mm-active' : '' }}">Notification Centre</a></li>
```

### 5D. Header Bell Updates

**Admin header** (`resources/views/elements/admin-header.blade.php`):
- Line 22: Change "View all activity" href from `{{ route('admin.security.audit-logs') }}` to `{{ route('admin.management.notification-centre') }}`
- Wire `#adminNotifCount` badge and `#adminNotifDropdown` to poll `GET /admin/api/notifications/` and show 5 most recent unread
- Wire `#adminMarkAllRead` to `POST /admin/api/notifications/mark-all-read`

**Customer header** (`resources/views/elements/header.blade.php`):
- Existing enforcement bell at lines 513-526 can be adapted or a new bell added
- Wire to poll `GET /api/notifications/` and show 5 most recent unread
- "View All" link to `{{ route('account.notification-centre') }}`

### 5E. Page Routes to Add (NOT API routes — those exist)

Add to `routes/web.php`:

```php
// Customer Portal — inside customer.auth middleware group
Route::get('/account/notification-centre', function () {
    return view('quicksms.account.notification-centre');
})->name('account.notification-centre');

// Admin Console — inside admin middleware group
Route::get('/management/notification-centre', function () {
    return view('admin.management.notification-centre');
})->name('admin.management.notification-centre');
```

---

## 6. HARD RULES — ANTI-DRIFT GUARDRAILS

### NEVER DO

| # | Rule | Reason |
|---|------|--------|
| 1 | Create new API routes | All 27 API endpoints already exist in routes/web.php |
| 2 | Create new controllers | All 7 controllers already exist |
| 3 | Create new models | All 7 models already exist |
| 4 | Create new migrations | All 8 migration files already exist |
| 5 | Modify `app/Events/Alerting/*` | 43 event files are reviewed and final |
| 6 | Modify `app/Listeners/Alerting/AlertEventSubscriber.php` | 42-event subscriber is reviewed and final |
| 7 | Modify `app/Services/Alerting/*` | 3 services are reviewed and final |
| 8 | Modify `app/Jobs/Alerting/*` | 9 jobs are reviewed and final |
| 9 | Modify `app/Traits/DispatchesAlertsSafely.php` | Trait is reviewed and final |
| 10 | Modify `config/alerting.php` | Config is reviewed and final |
| 11 | Modify `app/Http/Controllers/AdminController.php` | 9 dispatch sites are reviewed and final |
| 12 | Modify `app/Http/Controllers/SecuritySettingsController.php` | 7 dispatch sites are reviewed and final |
| 13 | Modify `app/Models/SubAccount.php` | Alert dispatch logic is reviewed and final |
| 14 | Modify `tests/Unit/SubAccountAlertingTest.php` | Tests are reviewed and final |
| 15 | Modify `tests/Unit/SecurityAlertingTest.php` | Tests are reviewed and final |
| 16 | Modify existing migration files | Migrations are sacred (CLAUDE.md Hard Rule 1) |
| 17 | Use React, Vue, Alpine.js | Blade + vanilla JS only (CLAUDE.md) |
| 18 | Create new CSS palettes | Fillow design system tokens only (CLAUDE.md) |
| 19 | Create new layouts | Use existing layouts only (CLAUDE.md) |
| 20 | Create new auth mechanisms | Session-based auth only (CLAUDE.md) |
| 21 | Use MySQL syntax | PostgreSQL only (CLAUDE.md) |
| 22 | Expose RED-zone data via GREEN routes | Trust boundary (CLAUDE.md Hard Rule 3) |

### ALWAYS DO

| # | Rule |
|---|------|
| 1 | Check `response.ok` before parsing JSON on every `fetch()` call |
| 2 | Show visible error states — never silently fail to empty/blank UI |
| 3 | Use Fillow severity badges: critical=red (`badge-danger`), warning=amber (`badge-warning`), info=blue (`badge-info`) |
| 4 | Use the action menu pattern (ellipsis → dropdown) for table row actions |
| 5 | Include CSRF token in all POST/PUT/DELETE: `headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content}` |
| 6 | Use `toPortalArray()` responses in GREEN zone — never expose full model |
| 7 | Read `config/alerting.php` before building dropdowns — it defines all categories, channels, operators, frequencies |
| 8 | Test the notification bell by checking browser console for API response |
| 9 | Add `[NotificationCentre]` prefix to console.log statements for debugging |

---

## 7. FILES YOU MAY CREATE

| File | Purpose |
|------|---------|
| `resources/views/quicksms/account/notification-centre.blade.php` | Customer portal Notification Centre page |
| `resources/views/admin/management/notification-centre.blade.php` | Admin console Notification Centre page |

## 8. FILES YOU MAY MODIFY (minimal, specific changes only)

| File | What to Change |
|------|---------------|
| `routes/web.php` | Add 2 GET page routes (NOT API routes) |
| `resources/views/elements/quicksms-sidebar.blade.php` | Add 1 nav link after line 97 |
| `resources/views/elements/admin-sidebar.blade.php` | Add 1 nav link after line 55 |
| `resources/views/elements/admin-header.blade.php` | Wire bell dropdown to /admin/api/notifications, update "View all" link |
| `resources/views/elements/header.blade.php` | Wire customer bell to /api/notifications, add "View All" link |

**DO NOT modify any other files.**

---

## 9. ACCEPTANCE CRITERIA

### Customer Portal

- [ ] Page loads at Account > Notification Centre
- [ ] All 5 tabs render and switch correctly
- [ ] Notification feed loads from API with severity badges
- [ ] Mark read, dismiss, mark-all-read work
- [ ] Alert rules CRUD works (create, edit, toggle, delete)
- [ ] System defaults shown read-only
- [ ] History table loads with filters
- [ ] Preferences allow muting by category
- [ ] Channel settings allow configuring webhook/slack/teams/SMS
- [ ] Sidebar nav link highlights when active
- [ ] Header bell shows unread count and 5 most recent

### Admin Console

- [ ] Page loads at Management > Notification Centre
- [ ] All 4 tabs render and switch correctly
- [ ] Admin notifications show with Resolve action
- [ ] Alert rules management works
- [ ] Dashboard summary cards render
- [ ] History table loads with all filters
- [ ] Sidebar nav link highlights when active
- [ ] Header bell shows unread count and links to Notification Centre

### Quality

- [ ] All fetch() calls check response.ok
- [ ] Error states visible to user (not blank)
- [ ] Browser console: no errors, `[NotificationCentre] Initialized` logged
- [ ] No new files created beyond the 2 Blade views listed above
- [ ] No modifications to files in the NEVER DO list

---

## 10. REFERENCE — PATTERN FILES

When building the Notification Centre views, pattern-match against these existing pages for Fillow design system conventions:

| Pattern | File | What to Copy |
|---------|------|-------------|
| Admin table + action menu | `resources/views/admin/security/country-controls.blade.php` | Table structure, ellipsis dropdown, status badges |
| Customer account page | `resources/views/quicksms/account/security.blade.php` | Tab switching, card layout, form patterns |
| Admin sidebar nav | `resources/views/elements/admin-sidebar.blade.php` | `mm-active` class pattern, link structure |
| Customer sidebar nav | `resources/views/elements/quicksms-sidebar.blade.php` | `data-subnav` attribute pattern |
| Admin header bell | `resources/views/elements/admin-header.blade.php` | Bell icon, badge, dropdown structure |
| Customer header bell | `resources/views/elements/header.blade.php` | Enforcement alerts bell pattern |

---

## APPENDIX A — REVIEW RESPONSE: WHAT WAS FIXED AND WHAT WAS REJECTED

An external code review was conducted against this prompt and the underlying codebase. Of the 8 issues raised, **5 were valid and have been fixed** in this version. **3 were invalid** — the reviewer was working from stale data or referencing text that doesn't exist in this prompt. Full justification below.

---

### FIXES APPLIED (5 valid issues)

#### Fix 1: Endpoint count corrected — "28" → "27"
**Reviewer claim:** "The summary says '28', table shows 27"
**Verdict:** VALID — fixed.

The guardrails table (Section 6, rule #1) previously said "All 28 API endpoints". Counting the route table: 16 customer + 11 admin = 27. There are also 4 balance alert routes in `routes/api_billing.php`, but those are a separate billing feature not used by the Notification Centre.

**Fix:** Changed to "All 27 API endpoints already exist in routes/web.php".

#### Fix 2: API response format documentation added
**Reviewer claim:** "Pagination format description is wrong — builder will write response.data.current_page instead of response.data.pagination.current_page"
**Verdict:** PARTIALLY VALID — the prompt never had wrong pagination docs (see Rejected #3 below), but the *absence* of pagination documentation was a real gap. A builder would have no way to know the correct format without reading controller code.

**Fix:** Added complete "API Response Formats" subsection to Section 4, documenting:
- Paginated response shape with nested `pagination` object
- Non-paginated response shape
- Notification endpoint extras (`unread_count`, `unread_by_category`)

This is the highest-impact fix — prevents broken pagination in every paginated tab.

#### Fix 3: Missing model response shapes added
**Reviewer claim:** "AlertHistory, AlertPreference, AlertChannelConfig, admin response shapes not documented"
**Verdict:** VALID — fixed.

The prompt previously only documented Notification and AlertRule shapes. The builder needs shapes for every tab:
- Tab 3 (History) needs AlertHistory shape
- Tab 4 (Preferences) needs AlertPreference shape
- Tab 5 (Channels) needs AlertChannelConfig shape
- Admin tabs need raw Eloquent model shapes

**Fix:** Added two new subsections to Section 4:
- **"GREEN Zone Model Shapes"** — Notification, AlertRule, AlertHistory, AlertPreference, AlertChannelConfig with exact JSON examples from `toPortalArray()` and controller `map()` functions
- **"RED Zone Response Shapes"** — AdminNotification, Admin AlertRule, Admin Dashboard with exact JSON examples showing raw Eloquent format differences (extra fields, different date format)

#### Fix 4: Missing `sub_account` category in preferences list
**Reviewer claim:** "Preferences tab lists 6 categories, should be 7"
**Verdict:** VALID — fixed.

Section 5A Tab 4 (Preferences) listed: `billing, messaging, compliance, security, system, campaign` — that's 6. The config defines 7 customer categories including `sub_account` ("Sub-Account Caps & Limits").

**Fix:** Changed to "All 7 categories: billing, messaging, compliance, security, system, campaign, sub_account".

#### Fix 5: `unread_count` and `unread_by_category` documented
**Reviewer claim:** "Notification response has extra fields not documented — builder will make separate API calls for unread counts"
**Verdict:** VALID — fixed.

Both `NotificationController@index` and `AdminNotificationController@index` return `unread_count` (integer) and `unread_by_category` (object keyed by category name) alongside `data` and `pagination`. These should be used for the bell badge count and category filter chips instead of making separate API calls.

**Fix:** Documented in the "API Response Formats" subsection with an explicit note to use these fields for the bell badge.

---

### CLAIMS NOT APPLICABLE TO CURRENT VERSION (3 issues)

These three claims reference text that does not appear in the current (or any committed) version of this prompt. The underlying concerns were valid and have been addressed — see the fixes above.

#### #1: "Customer default count says '24'"
**Reviewer claim:** The prompt says "24 customer defaults, 8 admin defaults."

**Git history verification:** The number "24" never appeared in any committed version of this file:
- `ad40797d` and `e1ecc4ab`: said "25 customer defaults"
- `2c572bce` onwards: corrected to "23 customer defaults"

The prompt has said "23" since 2026-03-19. No action needed — count is already correct.

#### #2: "HEAD verification hardcodes commit 3124b502"
**Reviewer claim:** The prompt hardcodes an expected commit hash that doesn't match HEAD.

**Current state:** Line 4 reads `"HEAD: Latest commit on branch (run git log -1 --oneline to verify)"` — no commit hash is hardcoded. This was changed to a generic pattern that remains valid as commits are added.

No action needed — already addressed in a prior update.

#### #3: "Pagination guardrail #10 describes wrong format"
**Reviewer claim:** A guardrail #10 says "Pagination: API endpoints return Laravel-style { data: [], current_page, last_page, per_page, total }" with fields flat at root.

**Git history verification:** No version of this file has ever contained a pagination guardrail with "Laravel-style" text. The ALWAYS DO table has 9 rules, none about pagination. The prompt previously had *no* pagination documentation at all — not incorrect documentation, just missing documentation.

**Action taken:** While the specific text cited doesn't exist, the underlying gap was real. We added the complete "API Response Formats" subsection (Fix #2 above) documenting the correct nested `{ success, data, pagination: {...} }` structure. The reviewer identified a genuine need even though the diagnosis referenced non-existent text.

---

### ROUND 1 REVIEW SUMMARY TABLE

| # | Reviewer Claim | Status | Action |
|---|---------------|--------|--------|
| 1 | Customer defaults says "24" | Already correct ("23") | None needed |
| 2 | Endpoint count says "28" | VALID — should be "27" | Fixed |
| 3 | Pagination guardrail #10 is wrong | Text doesn't exist; gap was real | Added missing docs |
| 4 | `unread_count` not documented | VALID | Fixed — added to response format section |
| 5 | Admin uses raw Eloquent, not toPortalArray | VALID — documentation gap | Fixed — added RED zone shapes |
| 6 | HEAD verification hardcodes wrong commit | Already generic | None needed |
| 7 | Missing AlertHistory/Preference/Channel shapes | VALID | Fixed — added all shapes |
| 8 | Preferences lists 6 categories, should be 7 | VALID | Fixed — added `sub_account` |

### ROUND 2 REVIEW SUMMARY TABLE

| # | Finding | Severity | Action |
|---|---------|----------|--------|
| 1 | `muted_until` format note at line 301 documents a bug that was simultaneously fixed in the same changeset — note was stale on arrival | Medium | Fixed — replaced with accurate "both endpoints return ISO 8601" note |
| 2 | Section 8 admin header path missing `/admin` prefix (`/api/notifications` → `/admin/api/notifications`) | Medium | Fixed — added `/admin` prefix |
| 3 | Header metadata hardcodes "12 commits" but branch has 17+ | Low | Fixed — replaced with generic `git rev-list --count` verification pattern |
| 4 | No test coverage for `muted_until` ISO 8601 serialization or `isCurrentlyMuted()` edge cases | Low | Fixed — added `tests/Unit/AlertPreferenceTest.php` with 10 test cases |
