# CLAUDE.md — QuickSMS Platform Guardrails

## Project Identity

QuickSMS is a **multi-tenant UK business messaging SaaS** (SMS, RCS, WhatsApp Business). Laravel 10, PostgreSQL 15+, Blade frontend with the Fillow design system. Targets ISO27001, Cyber Essentials Plus, and NHS DSP Toolkit compliance.

Two consoles exist:
- **Customer Portal** (GREEN zone) — self-service messaging, contacts, reporting, API connections
- **Admin Console** (RED zone) — internal management of accounts, routing, rates, approvals, HR

---

## HARD RULES — Never Break These

### 1. Database Migrations Are Sacred

- **NEVER** rename, delete, or add `.bak` / `.disabled` extensions to migration files
- **NEVER** use `migrate:fresh` in production — it drops all tables
- All new migrations must be **additive** — wrap `Schema::create` in `Schema::hasTable()` checks for idempotency
- Test that `php artisan migrate --force` runs cleanly from a fresh database AND from an existing database
- The `country_controls`, `country_control_overrides`, and `mcc_mnc_master` tables are **critical infrastructure** — they power the entire country permissions system

### 2. Row Level Security (RLS) Is Mandatory

- Every tenant-scoped table **must** have RLS policies active
- `tenant_id` is **always** derived server-side from the authenticated session — never from user input
- `SetTenantContext` middleware in `Kernel.php` must never be bypassed, reordered, or removed
- New tenant-scoped tables must have RLS policies created in their migration

### 3. RED / GREEN Trust Boundary

- **GREEN (Customer Portal):** Uses `portal_rw` database role, RLS enforced, `toPortalArray()` for all API responses, Sanctum or session auth
- **RED (Admin Console):** Uses `svc_red` database role, bypasses RLS, full model data access
- **NEVER** expose RED-zone data through GREEN-zone routes
- **NEVER** use `svc_red` role from customer-facing code paths
- Portal API routes use session-based `customer.auth` middleware in `routes/web.php`

### 4. Authentication & Stored Procedures

- Account creation and login **exclusively** use stored procedures (`sp_create_account()`, `sp_authenticate_user()`)
- Passwords are hashed **once** in the controller — never double-hash
- **NEVER** create raw SQL INSERT/UPDATE for user credentials outside stored procedures

### 5. Database Roles — Four Roles, No Exceptions

| Role | Purpose | RLS |
|------|---------|-----|
| `portal_ro` | Read-only customer queries | Enforced |
| `portal_rw` | Customer read/write operations | Enforced |
| `svc_red` | Admin / internal operations | Bypassed |
| `ops_admin` | Ops and provisioning | Bypassed |

Do not create new database roles. Do not grant additional privileges to existing roles without explicit approval.

### 6. Audit Logging Is Immutable

- Five audit log tables use the `ImmutableAuditLog` trait — DB triggers prevent UPDATE/DELETE
- Audit calls are wrapped in `try/catch(\Throwable)` — audit failures must **never** block business logic
- **NEVER** disable audit logging, remove the immutability trait, or drop audit triggers

---

## ANTI-DRIFT RULES — Stay On Track

### Do Not Invent

- **No new layouts** — use `layouts/admin.blade.php` (messaging) or `layouts/admin-hr.blade.php` (HR) for admin, `layouts/quicksms.blade.php` for customer portal
- **No new CSS colour palettes** — use the Fillow design system tokens already in the codebase
- **No new auth mechanisms** — no JWT, no Passport, no Firebase Auth. Sanctum + stored procedures only
- **No new frontend frameworks** — no React, no Vue, no Alpine.js. Blade + vanilla JS only
- **No new database drivers** — PostgreSQL only, `pgsql` connection, no SQLite/MySQL fallbacks

### Do Not Remove

- **Never remove `country_prefix`** from `mcc_mnc_master` or `country_controls` — it powers international dialling codes, prefix-based permission lookups, and the customer country dropdown
- **Never remove seeders** — `CountryControlSeeder` populates country_controls from mcc_mnc_master and is essential for fresh deployments
- **Never remove the `overrides` relationship** from `CountryControl` model — it's used by `withCount('overrides')` across admin views
- **Never remove `Schema::hasTable()` guards** from the country_controls migration — they ensure idempotent execution across fresh and existing VMs

### Do Not Modify Without Understanding

- `app/Services/CountryPermissionCacheService.php` — 3-tier cache (L1 in-process, L2 Redis, L3 PostgreSQL). Changes here affect every permission check across the entire platform
- `app/Services/CountryPermissionCheckService.php` — prefix matching logic for phone numbers. Uses longest-prefix-first resolution
- `app/Providers/AppServiceProvider.php` — registers cache and permission services as singletons. Changing registration affects the entire request lifecycle
- `setup.sh` — boot script for Replit. Runs migrations and seeds on every fresh import. Must complete without errors

### Keep Consistent

- Admin tables use the action menu pattern (ellipsis button -> dropdown with sections)
- Status badges: `allowed` = green, `blocked` = red, `restricted` = amber
- All fetch() calls to internal APIs **must** check `response.ok` before parsing JSON
- Error states must be **visible to the user** — never silently swallow API failures into empty UI

---

## COUNTRY PERMISSIONS SYSTEM — Critical Path

This is the most complex subsystem. Understand it before touching any part:

```
mcc_mnc_master (source of truth for MCC/MNC/prefix data)
    |
    v  [CountryControlSeeder]
country_controls (global defaults: allowed/blocked/restricted per country)
    |
    +---> country_control_overrides (account-level overrides)
    |
    +---> sub_account_country_permissions (sub-account-level overrides)
    |
    v
CountryPermissionCacheService (L1/L2/L3 cache resolution)
    |
    v
CountryPermissionCheckService (phone number -> country -> permission check)
```

**Resolution priority:** sub-account override > account override > global default

**Cache invalidation:**
- Global default changed -> `invalidateAll()` (clears every cached permission)
- Account override changed -> `invalidateAccount($accountId)` (clears that account only)
- Always invalidate after status changes — stale cache = wrong permissions = messages sent to blocked countries

---

## FILE STRUCTURE — Where Things Live

```
app/
  Http/Controllers/
    Admin/                          # RED zone controllers
      CountryControlController.php  # Global country defaults + overrides
      SubAccountCountryPermissionController.php
      MccMncController.php          # MCC/MNC master data management
    QuickSMSController.php          # GREEN zone (customer portal)
  Models/
    CountryControl.php              # Global country defaults
    CountryControlOverride.php      # Account-level overrides
    SubAccountCountryPermission.php # Sub-account overrides
    MccMnc.php                      # MCC/MNC/prefix data
    CountryRequest.php              # Customer country access requests
  Services/
    CountryPermissionCacheService.php  # 3-tier cache
    CountryPermissionCheckService.php  # Permission resolution
  Jobs/
    WarmCountryPermissionCache.php     # Background cache warming

database/
  migrations/
    2026_02_09_210000_create_country_controls_table.php      # CRITICAL — creates country_controls + overrides
    2026_03_13_140000_add_country_prefix_to_mcc_mnc_master.php
    2026_03_13_170000_create_sub_account_country_permissions_table.php
  seeders/
    CountryControlSeeder.php         # Populates country_controls from mcc_mnc_master
    DatabaseSeeder.php

resources/views/
  admin/security/country-controls.blade.php  # Admin country management UI
  quicksms/account/security.blade.php        # Customer security settings + country dropdown
```

---

## REPLIT ENVIRONMENT

- **Boot:** `setup.sh` runs on import — installs deps, configures .env, runs migrations + seeds
- **Server:** `php artisan serve --host=0.0.0.0 --port=5000`
- **Database:** PostgreSQL on host `helium`, port `5432`, database `heliumdb`
- **Dev mode:** `ADMIN_DEV_AUTOLOGIN=true` (local only — auto-login for admin console)
- **Nix packages:** PHP 8.3, Node 20, PostgreSQL client libs

### After Any Migration Change

Always verify:
```bash
php artisan migrate --force          # Must complete without errors
php artisan db:seed --class=CountryControlSeeder --force  # Must populate countries
```

### After Any Country-Related Change

Verify on the running app:
1. Admin Console -> Country Controls -> countries list must populate (not empty)
2. Customer Portal -> Security Settings -> country dropdown must have entries
3. Check browser console for `[CountryControls] Initialized with X countries` (X should be > 0)

---

## COMMON MISTAKES TO AVOID

| Mistake | Why It Breaks Things |
|---------|---------------------|
| Renaming migration files (.bak, .disabled, .old) | Laravel ignores non-.php files — tables never get created on fresh deploys |
| Adding `country_prefix` to SELECT without checking column exists | The column was added via a later migration — breaks if migrations run out of order |
| Changing `default_status` enum values without updating frontend | Frontend has hardcoded status->label/class/icon mappings in renderCountryTable() |
| Forgetting cache invalidation after status changes | Stale permissions served for up to 5 minutes (Redis TTL) |
| Using `Schema::create` without `hasTable()` guard | Fails on VMs where the table already exists |
| Silently catching fetch() errors and showing empty UI | Users see a blank page with no indication of what went wrong |
| Adding MySQL-specific syntax (BINARY, UNSIGNED for UUIDs) | PostgreSQL uses native UUID type — MySQL patterns will fail |
| Touching `SetTenantContext` middleware ordering | Breaks RLS for every tenant-scoped query in the application |
