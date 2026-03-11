# Replit: Pull and Merge — Unified Audit Log System

## READ THIS ENTIRE FILE BEFORE DOING ANYTHING

This prompt merges the **complete audit log system** from the `claude/quicksms-security-performance-dr8sw` branch into your working Replit branch. Two commits are consolidated here:

1. `4a6f056` — Cherry-pick audit log system onto current main
2. `4ba0156` — Fix audit log review findings: field names, RLS safety, sanitization, mock data indicator

**Every file, every route, every security constraint is listed below. Do NOT improvise, rewrite, rename, refactor, or "improve" any of it.**

---

## GUARDRAILS — READ BEFORE EVERY STEP

```
┌─────────────────────────────────────────────────────────────────┐
│  ANTI-DRIFT RULES — VIOLATIONS BREAK THE SYSTEM                │
│                                                                 │
│  1. Do NOT rename any file, class, method, table, or column    │
│  2. Do NOT convert raw PostgreSQL to MySQL or SQLite syntax     │
│  3. Do NOT remove DB::statement / DB::unprepared calls          │
│  4. Do NOT replace UNION ALL queries with Eloquent ORM          │
│  5. Do NOT add, remove, or reorder migration files              │
│  6. Do NOT modify the ImmutableAuditLog trait                   │
│  7. Do NOT remove RLS policies, triggers, or REVOKE statements  │
│  8. Do NOT add mock/seed data to audit tables                   │
│  9. Do NOT merge audit tables — 5 separate tables is correct    │
│ 10. Do NOT touch any file NOT listed in this document           │
│ 11. Do NOT create new controllers, models, or services          │
│ 12. Do NOT change middleware names or route prefixes             │
│ 13. Do NOT remove the throttle:60,1 middleware from API routes  │
│ 14. Do NOT change UUID primary key strategy to auto-increment   │
│ 15. Do NOT remove JSONB typing from metadata columns            │
└─────────────────────────────────────────────────────────────────┘
```

---

## Step 1: Pull the Branch

```bash
git fetch origin claude/quicksms-security-performance-dr8sw
git merge origin/claude/quicksms-security-performance-dr8sw --no-edit
```

If merge conflicts arise, **keep the incoming (Claude branch) version** for all files listed in this document. Your local Blade UI work should be preserved where it doesn't conflict.

After merge:

```bash
php artisan config:clear && php artisan cache:clear && php artisan route:clear && php artisan view:clear
php artisan migrate --force
```

---

## Step 2: Files Manifest — Verify These Exist

After merging, confirm every file below exists and is not empty. **Do NOT modify, rename, refactor, or "clean up" any of them.**

### 2A. Migration (1 file)

| File | Purpose |
|---|---|
| `database/migrations/2026_03_10_000001_create_audit_log_tables.php` | Creates ALL 5 audit tables, RLS policies, immutability triggers, REVOKE statements, indexes |

**Critical:** This single migration creates:
- `campaign_audit_log` (CRITICAL severity — tenant-scoped)
- `user_audit_log` (CRITICAL severity — tenant-scoped, dual-module: user_management + sub_account)
- `account_audit_log` (HIGH severity — tenant-scoped)
- `number_audit_log` (MEDIUM severity — tenant-scoped)
- `admin_audit_log` (MEDIUM severity — RED zone, admin-only, NO RLS)

Each table gets:
- `prevent_audit_mutation()` trigger (blocks UPDATE/DELETE at DB level)
- `REVOKE UPDATE, DELETE` on `portal_rw` and `portal_ro` roles
- Composite indexes for `account_id + created_at DESC`
- UUID primary keys with `gen_random_uuid()` default

GREEN zone tables (all except admin) get:
- `ENABLE ROW LEVEL SECURITY`
- SELECT policy: `account_id = current_setting('app.current_tenant_id')`
- INSERT policy: same check on write

**DO NOT** split this into multiple migrations. **DO NOT** convert to Schema::create() syntax. The raw SQL is required for PostgreSQL-specific features (RLS, triggers, REVOKE).

### 2B. Models (5 files + 1 trait)

| File | Table | Zone |
|---|---|---|
| `app/Models/Traits/ImmutableAuditLog.php` | — | Shared trait |
| `app/Models/AccountAuditLog.php` | `account_audit_log` | GREEN |
| `app/Models/CampaignAuditLog.php` | `campaign_audit_log` | GREEN |
| `app/Models/UserAuditLog.php` | `user_audit_log` | GREEN |
| `app/Models/NumberAuditLog.php` | `number_audit_log` | GREEN |
| `app/Models/AdminAuditLog.php` | `admin_audit_log` | RED |

**ImmutableAuditLog trait** provides to all GREEN models:
- UUID auto-generation on create
- `updating()` / `deleting()` hooks that throw `RuntimeException`
- Global tenant scope (`account_id` filtering)
- Common scopes: `forAccount()`, `ofAction()`, `dateRange()`, `recent()`, `byActor()`
- `toPortalArray()` serialization
- Metadata cast as JSON array

**AdminAuditLog** does NOT use the trait — it implements its own immutability hooks because it has no tenant scope (RED zone is inherently isolated).

**DO NOT** merge models. **DO NOT** add the ImmutableAuditLog trait to AdminAuditLog. **DO NOT** add a global scope to AdminAuditLog.

### 2C. Controller (1 file)

| File | Purpose |
|---|---|
| `app/Http/Controllers/Api/AuditLogApiController.php` | Unified API for both customer portal and admin console |

This controller has 5 methods:

| Method | Route | Auth | Purpose |
|---|---|---|---|
| `index()` | `GET /api/audit-logs` | `customer.auth` | Customer portal — aggregates all GREEN tables via UNION ALL |
| `modules()` | `GET /api/audit-logs/modules` | `customer.auth` | Returns available module list |
| `stats()` | `GET /api/audit-logs/stats` | `customer.auth` | Returns event counts and unique actors |
| `adminIndex()` | `GET /admin/api/audit-logs` | `admin.auth` | Admin console — RED zone only |
| `adminCustomerIndex()` | `GET /admin/api/customer-audit-logs` | `admin.auth` | Admin cross-tenant view (requires `account_id` param, sets RLS context) |

**UNION ALL strategy:** The customer `index()` queries multiple tables with `UNION ALL`, normalizing all rows to a unified schema:
```
id, module, category, action, user_id, user_name, details, metadata, ip_address, created_at, entity_id
```

**CUSTOMER_SOURCES constant** maps modules to tables. The `financial_audit_log` is explicitly EXCLUDED (has no `account_id`, would leak cross-tenant data).

**DO NOT** replace UNION ALL with Eloquent relationships. **DO NOT** add `financial_audit_log` to CUSTOMER_SOURCES. **DO NOT** remove the `throttle:60,1` middleware.

### 2D. Services (2 files)

| File | Purpose |
|---|---|
| `app/Services/Audit/AuditContext.php` | Actor resolution, diff computation, sensitive field sanitization |
| `app/Services/Admin/AdminAuditService.php` | Admin event catalogue with categories and severity levels |

**AuditContext::sanitize()** redacts these fields before storage:
```
password, password_hash, token, secret, api_key, credit_card,
credential, private_key, auth_token, session_token, mfa_secret,
otp_secret, recovery_codes
```

Redacted values become `[REDACTED]`. This prevents accidental credential logging.

**DO NOT** remove fields from the sanitize list. **DO NOT** change `[REDACTED]` to any other string.

### 2E. Observer (1 file — MODIFIED, not new)

| File | Purpose |
|---|---|
| `app/Observers/AccountObserver.php` | Hooks into Account model lifecycle to record audit events |

The observer records:
- `account_status_transition` — on status changes (captures old/new in metadata)
- `account_created` — on account creation
- Account deletion and restoration events

**DO NOT** remove the audit logging calls from the observer. The observer also handles existing business logic (credit expiry, SenderID management) — do not remove those either.

### 2F. Existing Controllers (MODIFIED — 3 files)

| File | Change |
|---|---|
| `app/Http/Controllers/AccountController.php` | Added `auditLogs()` method (renders Blade view), fixed field name references |
| `app/Http/Controllers/AdminController.php` | Added `securityAuditLogs()` method (renders admin Blade view) |
| `app/Http/Controllers/QuickSMSController.php` | Minor adjustments for audit integration |

**DO NOT** remove the new methods. **DO NOT** rename them.

### 2G. Blade Views (3 files)

| File | Purpose |
|---|---|
| `resources/views/quicksms/account/audit-logs.blade.php` | Customer portal audit log page (purple theme #886CC0) |
| `resources/views/admin/security/audit-logs.blade.php` | Admin console audit log page (blue theme #1e3a5f) |
| `resources/views/shared/partials/audit-log-component.blade.php` | Shared component (configurable theme, stats cards, table, filters) |

**Customer view features:**
- Category badges (user_management, security, authentication, messaging, etc.)
- Severity badges
- Retention indicator
- Integrity hash display (monospace)
- Quick filter buttons
- Infinite scroll
- Stats cards (total, today, unique actors)

**Admin view features:**
- Severity badges: low (gray), medium (blue), high (red), critical (dark red)
- Admin-specific category badges
- Compliance stats card

**DO NOT** change the theme colors. **DO NOT** remove the mock data indicator (added in fix commit). **DO NOT** remove the sanitization display logic.

### 2H. Routes (added to `routes/web.php`)

**Customer portal API routes** (inside `customer.auth` middleware):
```php
Route::middleware(['customer.auth', 'throttle:60,1'])->prefix('api/audit-logs')
    ->controller(AuditLogApiController::class)->group(function () {
    Route::get('/', 'index')->name('api.audit-logs.index');
    Route::get('/modules', 'modules')->name('api.audit-logs.modules');
    Route::get('/stats', 'stats')->name('api.audit-logs.stats');
});
```

**Admin console API routes** (inside `admin.auth` middleware):
```php
Route::get('/api/audit-logs', [AuditLogApiController::class, 'adminIndex'])
    ->name('admin.api.audit-logs.index');
Route::get('/api/customer-audit-logs', [AuditLogApiController::class, 'adminCustomerIndex'])
    ->name('admin.api.customer-audit-logs.index');
```

**DO NOT** change route names. **DO NOT** remove throttle. **DO NOT** move admin routes outside admin middleware group.

---

## Step 3: Security Architecture — Understand Before Touching

### Triple-Layer Immutability

```
Layer 1: PostgreSQL Trigger
  → prevent_audit_mutation() blocks UPDATE/DELETE at SQL level
  → Even raw DB::update() or pg_dump + edit + pg_restore cannot mutate

Layer 2: Eloquent Model Hooks
  → ImmutableAuditLog trait throws RuntimeException on update/delete
  → Prevents application-layer mutations via ORM

Layer 3: SQL REVOKE
  → REVOKE UPDATE, DELETE ON table FROM portal_rw, portal_ro
  → Database role cannot even attempt the operation
```

All three layers are required for compliance. **DO NOT** remove any layer.

### RED/GREEN Zone Separation

```
GREEN Zone (Customer-Visible):
  ├── campaign_audit_log    → RLS by account_id
  ├── user_audit_log        → RLS by account_id
  ├── account_audit_log     → RLS by account_id
  └── number_audit_log      → RLS by account_id

RED Zone (Admin-Only):
  └── admin_audit_log       → No RLS (isolated by middleware + controller logic)
```

**Customers NEVER see RED zone data.** The `adminIndex()` method is only reachable through `admin.auth` middleware. The UNION ALL in `index()` only queries GREEN tables.

### Tenant Isolation via RLS

```php
// Set before every customer query:
DB::statement("SELECT set_config('app.current_tenant_id', ?, true)", [$accountId]);
```

The `true` parameter makes it session-local (transaction-scoped). PostgreSQL RLS policies enforce `account_id = current_setting('app.current_tenant_id')` on every SELECT and INSERT.

**DO NOT** remove the `set_config` calls. **DO NOT** change `true` to `false` (that makes it persist across connections in pooled environments).

---

## Step 4: Field Naming — Exact Schema

### Standard Fields (All 5 Tables)

| Column | Type | Constraint | Notes |
|---|---|---|---|
| `id` | UUID | PK, DEFAULT gen_random_uuid() | Immutable |
| `account_id` | UUID | NOT NULL | Tenant key (not on admin_audit_log) |
| `action` | VARCHAR(50) | NOT NULL | Event type string |
| `user_id` | UUID | NULLABLE | Who performed the action |
| `user_name` | VARCHAR(255) | NULLABLE | Display name at time of action |
| `details` | TEXT | NULLABLE | Human-readable description |
| `metadata` | JSONB | DEFAULT '{}' | Structured before/after data |
| `ip_address` | INET | NULLABLE | Request origin |
| `user_agent` | TEXT | NULLABLE | HTTP User-Agent |
| `created_at` | TIMESTAMPTZ | NOT NULL, DEFAULT NOW() | Immutable event timestamp |

### Entity-Specific Fields

| Table | Extra Column | Type | Purpose |
|---|---|---|---|
| `campaign_audit_log` | `campaign_id` | UUID | Links to campaign |
| `user_audit_log` | `target_user_id` | UUID | Subject of action |
| `user_audit_log` | `module` | VARCHAR(30) | `'user_management'` or `'sub_account'` |
| `number_audit_log` | `number_id` | UUID | Links to PurchasedNumber |
| `admin_audit_log` | `admin_user_id` | UUID | Admin actor |
| `admin_audit_log` | `admin_user_name` | VARCHAR(255) | Admin display name |
| `admin_audit_log` | `category` | VARCHAR(50) | Event category |
| `admin_audit_log` | `severity` | VARCHAR(20) | low/medium/high/critical |
| `admin_audit_log` | `target_type` | VARCHAR(50) | What was targeted |
| `admin_audit_log` | `target_id` | UUID | Target resource ID |
| `admin_audit_log` | `target_account_id` | UUID | Target customer account |

**DO NOT** rename `user_name` to `actor_name`. **DO NOT** rename `target_user_id` to `subject_id`. **DO NOT** add `updated_at` — audit logs have no updates. The fix commit (`4ba0156`) already corrected field names — use these exact names.

---

## Step 5: Verify After Merge

Run these checks:

```bash
# 1. Migration runs cleanly
php artisan migrate --force

# 2. Routes are registered
php artisan route:list --name=audit
# Expect: api.audit-logs.index, api.audit-logs.modules, api.audit-logs.stats,
#         admin.api.audit-logs.index, admin.api.customer-audit-logs.index

# 3. Models load without error
php artisan tinker --execute="new App\Models\AccountAuditLog; new App\Models\CampaignAuditLog; new App\Models\UserAuditLog; new App\Models\NumberAuditLog; new App\Models\AdminAuditLog; echo 'OK';"

# 4. Views compile
php artisan view:cache
```

If migration fails with "table already exists", the tables were already created from a previous merge. Run:
```bash
php artisan migrate:status
```
If the migration shows as "Ran", you're fine — skip it.

---

## Step 6: What NOT to Touch

These files were NOT changed by the audit log commits. **Do not modify them as part of this merge:**

- Any migration file other than `2026_03_10_000001_create_audit_log_tables.php`
- Any model not listed in Section 2B
- Any controller not listed in Sections 2C/2F
- `app/Providers/*` — no service provider changes needed
- `config/*` — no config changes needed
- `composer.json` — no new dependencies
- `.env` — no new env variables

---

## Troubleshooting

| Problem | Fix |
|---|---|
| Migration fails: "function prevent_audit_mutation already exists" | Wrap in `CREATE OR REPLACE FUNCTION` or skip — the function is idempotent |
| Migration fails: "role portal_rw does not exist" | Create the role first: `CREATE ROLE portal_rw;` or remove REVOKE lines for dev-only environments |
| Route conflict on `api/audit-logs` | Ensure the route group is placed before catch-all routes in `web.php` |
| "Class AuditLogApiController not found" | Run `composer dump-autoload` |
| Blade view errors about undefined variables | Clear view cache: `php artisan view:clear` |

---

## Summary of What This Delivers

- **5 immutable, append-only audit tables** with PostgreSQL triggers, Eloquent guards, and SQL REVOKE
- **Row-Level Security** on all customer-facing tables — tenants cannot see each other's data
- **Unified API** with UNION ALL aggregation across all GREEN tables
- **Admin console** with separate RED zone audit logs and cross-tenant customer audit view
- **Sensitive field redaction** — passwords, tokens, API keys automatically replaced with `[REDACTED]`
- **SQL injection protection** — parameterized queries throughout, LIKE patterns escaped
- **No new dependencies** — pure Laravel + PostgreSQL
