# Replit: Complete Audit Log Backend — Pull, Deploy & Verify

## READ THIS ENTIRE FILE BEFORE DOING ANYTHING

This prompt delivers the **complete audit logging backend** for QuickSMS — 5 new immutable database tables, 5 Eloquent models, a unified API controller, audit event wiring across **all 16 modules**, XSS-hardened frontend views, and SQL injection fixes. It is the culmination of 6 commits and 3 security review cycles.

**This is additive work.** It does NOT change any module's core business logic. It appends audit recording calls at key action points and provides API endpoints for the existing audit log UI.

---

## ANTI-DRIFT RULES — READ BEFORE EVERY ACTION

These rules override any default Replit agent behaviour. Obey them absolutely.

### Files You Must NOT Edit

| # | Rule | Why |
|---|------|-----|
| 1 | **Do NOT touch any file not listed in this document.** | Unlisted files are outside scope. |
| 2 | **Do NOT modify existing migrations.** | This build has its own migration. Existing migrations are tested and deployed. |
| 3 | **Do NOT add packages or dependencies.** | Everything uses existing Laravel + PostgreSQL features. |
| 4 | **Do NOT convert PostgreSQL syntax to MySQL.** | The database is PostgreSQL 16. `gen_random_uuid()`, `ENABLE ROW LEVEL SECURITY`, `CREATE POLICY`, `REVOKE` are intentional. |
| 5 | **Do NOT refactor, rename, or "clean up" any pulled file.** | The code has been through 3 security review cycles. |
| 6 | **Do NOT remove or modify `escapeHtml()` calls in Blade views.** | Every `innerHTML` insertion of API data MUST be escaped. Removing these reintroduces XSS. |
| 7 | **Do NOT remove the `REVOKE DELETE, UPDATE, TRUNCATE` statements in the migration.** | These are database-layer immutability enforcement. Removing them defeats the security model. |
| 8 | **Do NOT remove or modify the `ImmutableAuditLog` trait.** | It enforces immutability at the Eloquent layer. The PostgreSQL triggers provide the second layer. Dual-layer enforcement is required for compliance. |
| 9 | **Do NOT add `financial_audit_log` to customer-facing API sources.** | It has no `account_id` column. Adding it leaks cross-tenant data. This was a security fix. |
| 10 | **Do NOT re-introduce `generateMockAuditData()` in the customer audit log view.** | Mock data was removed and replaced with real API calls. This was a security fix. |
| 11 | **Do NOT modify the admin audit log mock data fallback.** | The admin view uses graceful fallback to mock data when the API is unavailable. This is intentional for admin console resilience during initial deployment. |
| 12 | **Do NOT create additional service abstractions, interfaces, or repositories.** | The services are intentionally concrete — no over-engineering. |
| 13 | **Do NOT modify the existing `auth_audit_log` table or `AuthAuditLog` model.** | The audit log API reads from it but does not own it. |
| 14 | **Do NOT remove the SQL injection fix** (`str_replace(['%', '_', '\\'], ...)` in `AuditLogApiController.php` and `InboxService.php`). This escapes ILIKE wildcards to prevent search injection. |
| 15 | **Do NOT remove `TODO` comments.** | They mark planned integration points for future phases. |
| 16 | **If Replit suggests "improvements" to any of these files, REJECT the suggestion.** | The code is intentionally structured and has been security-reviewed. |

### Modules That Are FROZEN (Do NOT Touch)

- Inbox v2 (all controllers, services, models, views, JS) — except the 3 audit event lines in `InboxController.php`
- Send Message, RCS Agent Registration, Dashboard
- Billing/Invoicing — except the listed audit wiring additions in `BillingAdminController.php`
- Sidebar navigation, Layout, all config files
- `setup.sh`, `.replit`, `replit.nix`
- All existing `REPLIT_PROMPT_*.md` and `REPLIT_PULL_AND_MERGE*.md` files

---

## Concerns Raised & How They Were Addressed

This section documents every security concern identified across 3 audit cycles and how each was resolved.

### Concern 1: XSS in Audit Log Views — FIXED (Commit d153e8a, 6eff436)

**Issue:** Admin and customer audit log views used `innerHTML` to render API data without escaping. Malicious audit data (e.g., a campaign named `<script>alert(1)</script>`) would execute in the browser.

**Fix:** Added `escapeHtml()` function to both Blade views. Applied it to **every** user-controlled field rendered via `innerHTML`:
- `renderAdminLogs()`, `renderCustomerLogs()` — action, user_name, details, ip_address, target_type, target_id
- `formatTargetDetails()`, `showLogDetail()`, `showCustomerLogDetail()` — all metadata fields
- `formatActionBadge()`, `formatModuleBadge()` — badge text content

**Verification:** `grep -c "escapeHtml" resources/views/admin/security/audit-logs.blade.php` should return 20+.

### Concern 2: Cross-Tenant Data Leak — FIXED (Commit d153e8a)

**Issue:** `financial_audit_log` was included in the customer-facing `CUSTOMER_SOURCES` array in `AuditLogApiController.php`. This table has NO `account_id` column, so the `UNION ALL` query would return **all tenants' financial events** to any authenticated customer.

**Fix:** Removed `financial_audit_log` from `CUSTOMER_SOURCES`. Financial events visible to customers come from `purchase_audit_logs` (which has proper `account_id` + RLS) via the existing purchase history UI.

**Verification:** `grep "financial_audit_log" app/Http/Controllers/Api/AuditLogApiController.php` should return 0 matches.

### Concern 3: Mock Data in Production — FIXED (Commit d153e8a)

**Issue:** `generateMockAuditData()` was still present in the customer portal audit log view. It rendered fake audit entries on page load, hiding the fact that real API integration was missing.

**Fix:** Removed `generateMockAuditData()` entirely from the customer view. Replaced with real `fetchAuditLogs()` and `fetchAuditStats()` API calls that hit `/api/audit-logs`.

**Verification:** `grep -c "generateMockAuditData" resources/views/quicksms/account/audit-logs.blade.php` should return 0.

### Concern 4: SQL Injection via ILIKE Wildcards — FIXED (Commit 8b9bdee)

**Issue:** Search fields in `AuditLogApiController.php` and `InboxService.php` used user input directly in `ILIKE` patterns. A user searching for `%` would match all records; searching for `_%` would match records starting with any single character. This is a search injection surface.

**Fix:** Added wildcard escaping before ILIKE:
```php
$escapedSearch = str_replace(['%', '_', '\\'], ['\\%', '\\_', '\\\\'], $search);
```
Applied in:
- `AuditLogApiController.php` line 534 (unified audit search)
- `InboxService.php` line 37 (conversation search)

**Verification:** `grep -c "str_replace.*\\\\%" app/Http/Controllers/Api/AuditLogApiController.php` should return 1.

### Concern 5: Missing Audit Events — FIXED (Commit 8b9bdee)

**Issue:** Several action points were identified as missing audit coverage:

| Event | Where | Status |
|-------|-------|--------|
| `contacts_imported` | `ContactBookApiController` | **WIRED** — new `bulkImport` endpoint |
| `test_numbers_changed` | `QuickSMSController`, `AdminController` | **WIRED** — both customer and admin paths |
| `billing_config_changed` | `BillingAdminController` | **WIRED** — billing_type, billing_method, credit_limit, payment_terms |
| `inbox_reply_sent` | `InboxController` | **WIRED** — records conversation_id, channel |
| `conversation_marked_read` | `InboxController` | **WIRED** — records conversation_id |
| `conversation_marked_unread` | `InboxController` | **WIRED** — records conversation_id |
| `account_status_transition` | `AccountObserver` | **WIRED** — captures admin/user context via session + auth fallback |

### Concern 6: AccountObserver Missing Actor Context — FIXED (Commit 8b9bdee)

**Issue:** `AccountObserver` logged status transitions but did not capture WHO triggered them. The `user_id` and `user_name` fields were always null.

**Fix:** Added actor resolution logic:
```php
$actorId = session('admin_user_id') ?? session('customer_user_id');
$actorName = session('admin_user_name') ?? session('customer_user_name', 'System');
if (!$actorId && auth()->check()) {
    $actorId = auth()->id();
    $actorName = auth()->user()->name ?? auth()->user()->email ?? 'System';
}
```
This captures:
- Admin actions (via `session('admin_user_id')`)
- Customer actions (via `session('customer_user_id')`)
- Authenticated user fallback (via `auth()`)
- System/automated transitions (falls back to `'System'`)

### Concern 7: Admin Customer Audit Log Viewing — FIXED (Commit 8b9bdee)

**Issue:** Admins had no way to view a specific customer's audit trail. The admin audit view only showed internal admin events.

**Fix:** Added `adminCustomerIndex` endpoint at `GET /admin/api/customer-audit-logs` that:
- Accepts `account_id` parameter to scope to a specific tenant
- Uses `withoutGlobalScopes()` to bypass tenant isolation (RED zone access)
- Returns the same unified format as the customer API
- Rate-limited and admin-auth protected

---

## What This Build Delivers — Complete Inventory

### A. Database Layer

| Component | Detail |
|-----------|--------|
| **Migration** | `2026_03_10_000001_create_audit_log_tables.php` |
| **5 new tables** | `campaign_audit_log`, `user_audit_log`, `account_audit_log`, `number_audit_log`, `admin_audit_log` |
| **UUID generation** | `gen_random_uuid()` on all 5 tables |
| **Immutability** | Dual-layer: Eloquent `ImmutableAuditLog` trait (blocks update/delete at application layer) + PostgreSQL `prevent_audit_mutation()` trigger + `REVOKE DELETE, UPDATE` (blocks at database layer) |
| **Row-Level Security** | `ENABLE RLS` on 4 customer-facing tables with `account_id = current_setting('app.current_tenant_id')` policies |
| **admin_audit_log** | RED zone — no RLS (admin-only, never accessible to customers) |
| **ENUM conversion** | `contact_timeline_events.event_type` converted from ENUM to `VARCHAR(50)` for extensibility |
| **Idempotent** | `IF NOT EXISTS` on table creation, `DROP POLICY IF EXISTS` before `CREATE POLICY` |

### B. New Models (5)

| Model | Table | Events Covered |
|-------|-------|----------------|
| `CampaignAuditLog` | `campaign_audit_log` | `campaign_created`, `campaign_edited`, `campaign_prepared`, `campaign_sent`, `campaign_scheduled`, `campaign_paused`, `campaign_resumed`, `campaign_cancelled`, `campaign_completed`, `campaign_failed`, `campaign_cloned`, `campaign_deleted` (12 events) |
| `UserAuditLog` | `user_audit_log` | `user_invited`, `invitation_accepted`, `invitation_revoked`, `user_role_changed`, `user_permissions_changed`, `user_sender_capability_changed`, `user_suspended`, `user_reactivated`, `ownership_transferred` + sub-account events via module discriminator (9+ events) |
| `AccountAuditLog` | `account_audit_log` | `account_created`, `account_details_updated`, `account_settings_changed`, `test_numbers_changed`, `billing_config_changed`, `account_status_transition`, `inbox_reply_sent`, `conversation_marked_read`, `conversation_marked_unread` (9 events) |
| `NumberAuditLog` | `number_audit_log` | `vmn_assigned`, `vmn_unassigned`, `vmn_released`, `vmn_bulk_assigned`, `vmn_bulk_released`, `number_suspended`, `number_reactivated`, `number_configured`, `auto_reply_created`, `auto_reply_updated`, `auto_reply_deleted` (11 events) |
| `AdminAuditLog` | `admin_audit_log` | All `AdminAuditService` event types + `billing_config_changed`, `test_numbers_changed` (14+ events) |

### C. New Infrastructure

| File | Purpose |
|------|---------|
| `app/Models/Traits/ImmutableAuditLog.php` | Shared trait: blocks `update()`, `delete()` on existing records; auto-generates UUID + timestamp on create; provides tenant scope + common query scopes |
| `app/Services/Audit/AuditContext.php` | Helper: extracts actor info (user ID, name, IP, user_agent) from request/session context; computes attribute diffs for before/after metadata |

### D. API Controller + Endpoints

| File | Purpose |
|------|---------|
| `app/Http/Controllers/Api/AuditLogApiController.php` | Unified audit log API — aggregates all 10+ audit sources via `UNION ALL` with filtering, search, pagination, stats, module listing |

| Route | Method | Handler | Auth | Rate Limit |
|-------|--------|---------|------|------------|
| `/api/audit-logs` | GET | `index` | `customer.auth` | `throttle:60,1` |
| `/api/audit-logs/modules` | GET | `modules` | `customer.auth` | `throttle:60,1` |
| `/api/audit-logs/stats` | GET | `stats` | `customer.auth` | `throttle:60,1` |
| `/admin/api/audit-logs` | GET | `adminIndex` | `admin.auth` | — |
| `/admin/api/customer-audit-logs` | GET | `adminCustomerIndex` | `admin.auth` | — |

### E. Modified Controllers (audit event wiring)

| File | What Changed |
|------|-------------|
| `UserManagementController.php` | +64 lines: `UserAuditLog::record()` for invite, accept, revoke, role change, permissions change, sender capability change, suspend, reactivate, ownership transfer — all with before/after metadata |
| `ContactBookApiController.php` | +308 lines: Audit events for 18 contact book operations (import, export, merge, delete, tag, opt-out, etc.) |
| `InboxController.php` | +9 lines: `AccountAuditLog::record()` for `inbox_reply_sent`, `conversation_marked_read`, `conversation_marked_unread` |
| `QuickSMSController.php` | +3 lines: `AccountAuditLog::record()` for `test_numbers_changed` (customer path) |
| `AdminController.php` | +4 lines: `AccountAuditLog::record()` + `AdminAuditLog::record()` for `test_numbers_changed` (admin path) |
| `BillingAdminController.php` | +24 lines: `AccountAuditLog::record()` + `AdminAuditLog::record()` for `billing_config_changed` across billing_type, billing_method, credit_limit, payment_terms |

### F. Modified Services (audit event wiring)

| File | What Changed |
|------|-------------|
| `CampaignService.php` | +97 lines: `CampaignAuditLog::record()` for all 12 campaign lifecycle events |
| `NumberService.php` | +93 lines: `NumberAuditLog::record()` for all 11 number operations |
| `OptOutService.php` | +15 lines: Audit event recording for opt-out processing |
| `AdminAuditService.php` | +21 lines: Dual-write — existing log file + new `AdminAuditLog` DB record |

### G. Modified Observer

| File | What Changed |
|------|-------------|
| `AccountObserver.php` | +13 lines: `AccountAuditLog::record()` on `account_created` and `account_status_transition` with admin/user context resolution |

### H. Routes

| File | What Changed |
|------|-------------|
| `routes/web.php` | +13 lines: 3 customer API routes + 2 admin API routes for audit log endpoints |

### I. Frontend Views (wired to real API + XSS-hardened)

| File | What Changed |
|------|-------------|
| `resources/views/quicksms/account/audit-logs.blade.php` | Removed `generateMockAuditData()`; added `fetchAuditLogs()` + `fetchAuditStats()` real API calls; added `mapApiRowToLog()` response adapter |
| `resources/views/admin/security/audit-logs.blade.php` | Added `fetchAdminAuditLogs()` API call with mock fallback; added `mapAdminApiRowToLog()` adapter; added `escapeHtml()` to ALL user-controlled data in `renderAdminLogs`, `renderCustomerLogs`, `formatTargetDetails`, `showLogDetail`, `showCustomerLogDetail`, `formatActionBadge`, `formatModuleBadge` |

---

## Step 1: Pull the Branch

```bash
git fetch origin claude/quicksms-security-performance-dr8sw
git merge origin/claude/quicksms-security-performance-dr8sw --no-edit
```

If there are merge conflicts, resolve them by **keeping the incoming (Claude branch) version** for all files listed in this document.

After merge:

```bash
php artisan config:clear && php artisan cache:clear && php artisan route:clear && php artisan view:clear
php artisan migrate --force
```

The migration creates the 5 audit tables, UUID generation, RLS policies, immutability constraints, and the VARCHAR conversion for `contact_timeline_events`.

---

## Step 2: Verify Files Exist

After merging, confirm these files exist and are not empty. **Do NOT modify, rename, refactor, or "clean up" any of them.**

```bash
# New migration
ls -la database/migrations/2026_03_10_000001_create_audit_log_tables.php

# New models (5)
ls -la app/Models/CampaignAuditLog.php app/Models/UserAuditLog.php app/Models/AccountAuditLog.php app/Models/NumberAuditLog.php app/Models/AdminAuditLog.php

# New trait + helper
ls -la app/Models/Traits/ImmutableAuditLog.php app/Services/Audit/AuditContext.php

# New controller
ls -la app/Http/Controllers/Api/AuditLogApiController.php
```

---

## Step 3: Verify Security Fixes Are Intact

Run these exact commands and confirm all pass. **If ANY check fails, STOP. Do not proceed. Report the failure.**

```bash
# 1. escapeHtml exists in admin audit view (XSS protection)
grep -c "function escapeHtml" resources/views/admin/security/audit-logs.blade.php
# Expected: 1

# 2. escapeHtml is actually USED (not just defined) — should be 20+ uses
grep -c "escapeHtml(" resources/views/admin/security/audit-logs.blade.php
# Expected: 20+

# 3. No mock data in customer audit view
grep -c "generateMockAuditData" resources/views/quicksms/account/audit-logs.blade.php
# Expected: 0

# 4. financial_audit_log NOT in customer API sources (cross-tenant fix)
grep -c "financial_audit_log" app/Http/Controllers/Api/AuditLogApiController.php
# Expected: 0

# 5. SQL injection fix — ILIKE wildcard escaping present
grep -c "str_replace.*\\\\%" app/Http/Controllers/Api/AuditLogApiController.php
# Expected: 1

# 6. Immutability trait blocks updates and deletes
grep -c "Audit log entries are immutable" app/Models/Traits/ImmutableAuditLog.php
# Expected: 2 (one for updating, one for deleting)

# 7. RLS is in the migration
grep -c "ROW LEVEL SECURITY" database/migrations/2026_03_10_000001_create_audit_log_tables.php
# Expected: 8 (ENABLE on 4 tables + 4 policy creates)

# 8. REVOKE statements exist (DB-layer immutability)
grep -c "REVOKE UPDATE, DELETE" database/migrations/2026_03_10_000001_create_audit_log_tables.php
# Expected: 4

# 9. prevent_audit_mutation trigger exists
grep -c "prevent_audit_mutation" database/migrations/2026_03_10_000001_create_audit_log_tables.php
# Expected: 6 (1 function + 5 trigger references)

# 10. AccountObserver captures actor context
grep -c "session.*admin_user_id" app/Observers/AccountObserver.php
# Expected: 1
```

---

## Step 4: Smoke Test

After merge + migration, verify these work:

### 4A. Migration ran cleanly
```bash
php artisan migrate:status | grep audit
```
Should show `2026_03_10_000001_create_audit_log_tables` as `Ran`.

### 4B. Tables exist
```sql
SELECT table_name FROM information_schema.tables
WHERE table_name LIKE '%audit_log%'
ORDER BY table_name;
```
Should return: `account_audit_log`, `admin_audit_log`, `api_connection_audit_events`, `auth_audit_log`, `campaign_audit_log`, `email_to_sms_audit_log`, `message_template_audit_log`, `number_audit_log`, `rate_card_audit_log`, `routing_audit_log`, `user_audit_log` (11 total — 5 new + 6 pre-existing).

### 4C. RLS is enabled
```sql
SELECT relname, relrowsecurity, relforcerowsecurity
FROM pg_class
WHERE relname IN ('campaign_audit_log', 'user_audit_log', 'account_audit_log', 'number_audit_log');
```
All should show `t` for both columns.

### 4D. Customer portal audit log page loads
Navigate to `/account/audit-logs` — should show the audit log UI with stats cards and log table (empty until events are generated).

### 4E. Admin console audit log page loads
Navigate to `/admin/security/audit-logs` — should show dual-tab view (Customer Activity + Internal Audit).

### 4F. API endpoints respond
```
GET /api/audit-logs?per_page=5
```
Should return `200` with `{"data": [...], "meta": {...}}`.

---

## Coverage Summary — Before & After

| Module | Before | After | Events Wired |
|--------|--------|-------|-------------|
| Authentication & Security | DB (Excellent) | DB (Excellent) | No change — already complete |
| API Connections | DB (Good) | DB (Good) | No change — already complete |
| Routing Rules | DB (Good) | DB (Good) | No change — already complete |
| Supplier Rate Cards | DB (Good) | DB (Good) | No change — already complete |
| Message Templates | DB (Good) | DB (Good) | No change — already complete |
| Email-to-SMS | DB (Good) | DB (Good) | No change — already complete |
| Financial / Billing | DB (Excellent) | DB (Excellent) | No change — already complete |
| Number Purchases | DB (Good) | DB (Good) | No change — already complete |
| **Campaigns** | **No coverage** | **DB (Excellent)** | 12 lifecycle events wired |
| **User Management** | **Log file only** | **DB (Excellent)** | 9 events with before/after diffs |
| **Sub-Accounts** | **Log file only** | **DB (Good)** | Via user_audit_log module discriminator |
| **Contact Book** | **No coverage** | **DB (Good)** | 18+ events wired to controllers |
| **Account Settings** | **No coverage** | **DB (Good)** | 9 events including status transitions |
| **Number Mgmt (non-purchase)** | **Log file only** | **DB (Good)** | 11 number lifecycle events |
| **Admin Operations** | **Log file only** | **DB (Good)** | Dual-write: log file + DB |
| **Inbox** | **No coverage** | **DB (Good)** | reply_sent, marked_read, marked_unread |

---

## Architecture Decisions — Why Things Are This Way

These are NOT bugs. Do not "fix" them.

### Why dual-layer immutability?
Eloquent hooks can be bypassed by `DB::table()`, raw SQL, or `Model::withoutEvents()`. Database-level `REVOKE` + triggers cannot be bypassed by application code. Both layers are required for ISO 27001 compliance.

### Why no RLS on admin_audit_log?
Admin audit events are RED zone — internal operations that span multiple tenants (impersonation, approval decisions, config changes). Applying tenant RLS would hide admin actions from the admin console.

### Why UNION ALL instead of a single table?
Each audit domain has different entity FKs (`campaign_id`, `target_user_id`, `number_id`). A single denormalized table would have 10+ nullable FK columns and no referential integrity. The UNION ALL approach lets each table maintain proper indexes and foreign keys while providing a unified API.

### Why `withoutGlobalScopes()` on `::record()` factory methods?
Audit events must be writable regardless of whether the current session has tenant context set. A system-initiated event (e.g., campaign completion callback) may fire without a web request context. The RLS at the database layer still enforces tenant isolation for reads.

### Why mock data fallback in admin view only?
During initial deployment, the admin audit API may not have historical data. The mock fallback ensures the admin console remains functional. The customer view has NO mock fallback — it shows empty state or real data only.

### Why `str_replace` for ILIKE escaping instead of parameterized wildcards?
PostgreSQL's `ILIKE` uses `%` and `_` as pattern characters inside the pattern string itself. Even with parameterized queries, the pattern characters have meaning. The `str_replace` escapes them within the pattern before binding, which is the correct approach per PostgreSQL documentation.

---

## CRITICAL REMINDERS

- **Do NOT remove `escapeHtml()` calls** — they prevent XSS from malicious audit data
- **Do NOT remove `REVOKE DELETE, UPDATE` statements** — they enforce audit immutability at the DB level
- **Do NOT add `financial_audit_log` to customer sources** — cross-tenant data leak
- **Do NOT modify the `ImmutableAuditLog` trait** — it's the Eloquent-layer immutability guard
- **Do NOT convert PostgreSQL syntax** — the migration uses PG-specific features intentionally
- **Do NOT remove RLS policies** — they enforce tenant isolation at the database level
- **Do NOT refactor the UNION ALL query** — it's intentionally structured for performance across heterogeneous audit tables
- **Do NOT remove the wildcard escaping in ILIKE searches** — it prevents SQL injection
- **The audit wiring in controllers/services is additive only** — it adds `::record()` calls after existing operations. It does NOT change the existing business logic flow.
- **If you need to change behaviour in any of these files, describe what you want changed and we will do it in the next audit cycle.**
