# QuickSMS — Opus Security & Performance Review

**Reviewer:** Claude Opus (Principal Security Architect + Senior PostgreSQL DBA + Performance Specialist)
**Date:** 2026-02-13
**Branch Reviewed:** `claude/review-codebase-1VxG3` (commits `a4cd5f2` through `0906957`)
**Scope:** Database schema, migrations, RLS policies, roles/grants, stored procedures, views, middleware, controllers, and models

---

## 1) EXECUTIVE VERDICT

### **PROCEED ONLY AFTER FIXES**
**Confidence: HIGH**

1. **CRITICAL: Models still use BINARY(16) UUID mutators on a native-UUID PostgreSQL schema.** Every `getIdAttribute()` / `setIdAttribute()` / `getTenantIdAttribute()` / `setTenantIdAttribute()` in `Account.php`, `User.php`, `ApiToken.php`, `AuthAuditLog.php` will corrupt UUIDs at runtime — `bin2hex()` on a 36-char string returns 72 hex chars, and `hex2bin()` on a UUID string silently fails. This is a **data-corruption ship-blocker**.

2. **CRITICAL: `SetTenantContext` middleware is NOT registered in `Kernel.php`.** Neither the `api` middleware group nor any alias includes `SetTenantContext::class`. RLS policies rely on `app.current_tenant_id` being set — without the middleware, the session variable is never set, and RLS effectively allows all rows (the `NULLIF(..., '')` pattern returns NULL, which causes the comparison to fail, but the `current_setting(..., true) IS NULL` clause in accounts_isolation opens up all data). This means **zero tenant isolation at runtime** until fixed.

3. **HIGH: The `accounts_isolation` RLS policy has a NULL-context bypass.** The `USING` clause includes `OR current_setting('app.current_tenant_id', true) IS NULL`. If the middleware fails to set the variable (which is currently the case — see point 2), or a background job or migration runs without setting context, **every account is visible**. This is an intentional design choice documented as "defense-in-depth primary at app layer" but it defeats the entire purpose of RLS.

4. **HIGH: `AuthController::signup()` bypasses the stored procedure entirely.** The controller directly uses Eloquent `Account::create()` + `User::create()` instead of calling `sp_create_account`. This means portal_rw doesn't need direct table INSERT grants (which is correct), but the app is running as a superuser/owner role that bypasses RLS and grants entirely — negating the entire RBAC model.

5. **MEDIUM: `AuthController::login()` uses `User::withoutGlobalScope('tenant')` and queries raw `users` table.** Combined with likely superuser connection, this bypasses both the Eloquent tenant scope AND RLS. The stored procedure `sp_authenticate_user` is called but the user lookup happens first outside of it, and the `CALL` syntax used is incorrect for PostgreSQL functions (should be `SELECT * FROM sp_authenticate_user(...)`).

---

## 2) SECURITY GUARDRAIL SCORECARD

| # | Control | Verdict | Evidence / Notes |
|---|---------|---------|-----------------|
| 1 | Tenant isolation proven at DB layer (RLS) | **FAIL** | RLS policies exist on 8 GREEN tables. However, `accounts_isolation` has NULL-context bypass. More critically, the middleware that sets context is **not registered** in `Kernel.php`, so RLS is never activated at runtime. |
| 2 | Tenant context derived server-side only | **PASS (design)** / **FAIL (runtime)** | `SetTenantContext.php` correctly reads `$user->tenant_id` from the authenticated model, never from request input. But it is not registered in the middleware stack, so it never runs. |
| 3 | No RLS bypass paths (views/functions/grants) | **FAIL** | (a) All 5 stored procedures use `SECURITY DEFINER` — runs as the **function owner** (typically superuser/postgres), which has BYPASSRLS implicitly. Any SQL injection into procedure parameters executes with owner privileges. (b) `accounts_system_bypass` policy grants `TO PUBLIC` — any role can see the system account row. (c) Views inherit RLS from base tables, which is correct. |
| 4 | Least privilege roles & grants correct | **FAIL** | Roles are well-designed in the SQL script. But **the application never connects as `portal_rw`**. Laravel's `.env` will use a single DB connection (likely `postgres` or the DB owner). No code sets `SET ROLE portal_rw` on the connection. All grant restrictions are bypassed because the app runs as owner. |
| 5 | Red/Green boundary enforced (data + endpoints) | **PARTIAL PASS** | Views correctly exclude sensitive fields (password, mfa_secret, token_hash). Grant script correctly blocks portal roles from RED tables. But since the app runs as owner, Eloquent can query `account_flags`, `auth_audit_log`, `admin_users` directly — and `AuthController` does exactly this via `AuthAuditLog::logEvent()`. The boundary is enforced by code convention, not database enforcement. |
| 6 | Secrets not in plaintext | **FAIL** | (a) Roles script: `CREATE ROLE portal_ro LOGIN PASSWORD 'CHANGE_THIS_IN_PRODUCTION'` — placeholder passwords in committed SQL. (b) `webhook_secret` in `account_settings` is stored as `VARCHAR` with comment "Encrypted" but no encryption is applied at schema or model level. (c) `mfa_secret` is encrypted via Laravel's `encrypt()` in the User model — this is correct. (d) API token hashes use SHA-256, which is correct. |
| 7 | Audit coverage sufficient | **PASS** | `auth_audit_log` covers login success/failure, signup, password changes, token creation, account lockout, MFA events. Immutability enforced via grants (INSERT-only for non-ops_admin). Good event taxonomy with ENUM types. |
| 8 | PII handling and retention reasonable | **PARTIAL PASS** | No retention policy is implemented (no cleanup jobs, no TTL triggers, no partitioning by time). `mobile_verification_attempts` has a comment about 24-hour cleanup but no mechanism. `auth_audit_log` comments mention 2-year retention but no enforcement. No data anonymization or pseudonymization strategy. |
| 9 | Injection/priv escalation blast radius minimized | **FAIL** | (a) Stored procedures with `SECURITY DEFINER` run as owner, which typically has superuser/BYPASSRLS. If any parameter is injectable, attacker gains full DB access. (b) The `sp_create_account` function builds an email regex check (`!~`) but doesn't use parameterized queries internally — the function body itself is safe (uses PL/pgSQL variables), but `SECURITY DEFINER` elevation is the blast-radius concern. (c) `sp_authenticate_user` references `u.account_locked_until` but the users table column is actually `locked_until` — this will cause a runtime error, meaning the login flow may be broken entirely. |
| 10 | Connection pooling safety (tenant context reset) | **PARTIAL PASS** | `SetTenantContext` uses `SET LOCAL` which is transaction-scoped — correct for preventing context bleed between requests sharing a connection. The `terminate()` method calls `RESET app.current_tenant_id` defensively. However, if Laravel uses persistent connections without wrapping each request in a transaction, `SET LOCAL` has no effect outside a transaction, and the variable persists on the connection. |
| 11 | Enumeration protections in place | **PASS** | UUIDs (native `gen_random_uuid()`) used for all primary keys. Account numbers are sequential (`QS00000001`) but this is a business requirement and not a security-sensitive identifier. No sequential integer IDs exposed to the portal (except `auth_audit_log.id` and `account_credits.id` which use `bigserial`, but these are RED-side). |

---

## 3) SHIP-BLOCKERS (CRITICAL SECURITY ISSUES)

### SB-1: Models Corrupt UUIDs — Data Integrity Failure

**Location:** `package/app/Models/Account.php:140-170`, `User.php:110-155`, `ApiToken.php:75-150`, `AuthAuditLog.php:55-100`

**Description:** All models retain MySQL-era `getIdAttribute()` / `setIdAttribute()` mutators that call `bin2hex()` / `hex2bin()`. PostgreSQL native UUID columns return 36-character string UUIDs (e.g., `550e8400-e29b-41d4-a716-446655440000`). The mutator treats this as binary and calls `bin2hex()`, producing a 72-character corrupted hex string. On write, `hex2bin()` on a UUID string with dashes will produce garbage or throw.

**Exploit Scenario:** Any Eloquent read of `accounts`, `users`, `api_tokens`, or `auth_audit_log` returns corrupted IDs. Foreign key lookups fail. Tenant context comparison fails. Effectively, the application is non-functional on PostgreSQL.

**Severity:** CRITICAL (P0) — application will not function.

**Fix:**
```php
// DELETE these methods entirely from Account.php, User.php, ApiToken.php, AuthAuditLog.php:
// - getIdAttribute()
// - setIdAttribute()
// - getTenantIdAttribute()
// - setTenantIdAttribute()
// - getActorIdAttribute()
// - setActorIdAttribute()
// - getUserIdAttribute()
// - setUserIdAttribute()

// KEEP these properties:
protected $keyType = 'string';
public $incrementing = false;
protected $casts = [
    'id' => 'string',
    'tenant_id' => 'string',
];
```

---

### SB-2: Tenant Context Middleware Not Registered

**Location:** `package/app/Http/Kernel.php` — `SetTenantContext` is absent from all middleware groups and aliases.

**Description:** The `SetTenantContext` middleware exists as a file but is never loaded by the framework. The `api` middleware group contains only `ThrottleRequests` and `SubstituteBindings`. No route group applies the tenant middleware. Without it, `app.current_tenant_id` is never set, and RLS policies degrade to their NULL-handling fallback.

**Exploit Scenario:** An authenticated user makes any API request. The tenant context variable is unset. The `accounts_isolation` policy's `IS NULL` clause makes all accounts visible. For other tables (`users`, `api_tokens`, etc.), the `NULLIF(current_setting('app.current_tenant_id', true), '')::uuid` returns NULL, which fails the equality check — so those tables return zero rows. This creates a split-brain: accounts table is wide open, but user data is inaccessible. The application is both insecure (accounts leak) and non-functional (no user data returned).

**Severity:** CRITICAL (P0) — tenant isolation non-existent at runtime.

**Fix:**
```php
// In Kernel.php, add to $middlewareAliases:
'tenant' => \App\Http\Middleware\SetTenantContext::class,

// Add to api middleware group (after auth):
'api' => [
    \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
    \Illuminate\Routing\Middleware\ThrottleRequests::class.':api',
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
    \App\Http\Middleware\SetTenantContext::class, // ADD THIS
],
```

---

### SB-3: RLS `accounts_isolation` Policy Has NULL-Context Bypass

**Location:** `package/database/migrations/2026_02_10_000001_create_accounts_table_postgres.php` lines ~105-113

**Description:** The policy includes:
```sql
OR current_setting('app.current_tenant_id', true) IS NULL
```
This means: if no tenant context is set (e.g., background job, migration, cron, queue worker, or middleware failure), ALL accounts are visible to ANY role. This defeats RLS entirely for the accounts table.

**Exploit Scenario:** A background queue worker processes a job. No middleware runs (queue workers don't go through HTTP middleware). The tenant context is unset. The worker can read/write ALL accounts.

**Severity:** CRITICAL (P0) — design flaw that creates a permanent bypass.

**Fix:**
```sql
-- Remove the NULL bypass. If no context is set, return NO rows.
DROP POLICY accounts_isolation ON accounts;

CREATE POLICY accounts_isolation ON accounts
FOR ALL
USING (
    id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid
)
WITH CHECK (
    id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid
);

-- Create separate policies for privileged roles:
CREATE POLICY accounts_service_access ON accounts
FOR ALL
TO svc_red, ops_admin
USING (true)
WITH CHECK (true);
```

---

### SB-4: Application Runs as DB Owner, Negating All RBAC

**Location:** `package/.env.example`, `Kernel.php`, all controllers

**Description:** No code anywhere sets `SET ROLE portal_rw` or connects as the `portal_rw` role. Laravel connects as whatever user is in `.env` — typically the database owner or `postgres` superuser. The entire grants script (`01_create_roles_and_grants.sql`) is security theater if the application bypasses it.

**Exploit Scenario:** A developer adds `AccountFlags::where('fraud_risk_level', 'high')->get()` to a portal controller. Because the app runs as owner, this query succeeds — returning RED-side fraud data to GREEN-side portal users. No database-level control prevents this. The grants only work if the connection uses `portal_rw`.

**Severity:** CRITICAL (P0) — RBAC model is unenforced.

**Fix:**
```php
// Option A: SET ROLE after connection (recommended for Replit single-DB setup)
// In a service provider or middleware:
DB::statement("SET ROLE portal_rw");

// Option B: Configure Laravel to connect as portal_rw in .env:
// DB_USERNAME=portal_rw
// DB_PASSWORD=<secure-password>

// Option C: Use separate connections for portal vs internal services
// In config/database.php:
'connections' => [
    'portal' => [
        'driver' => 'pgsql',
        'username' => 'portal_rw',
        // ...
    ],
    'internal' => [
        'driver' => 'pgsql',
        'username' => 'svc_red',
        // ...
    ],
],
```

---

### SB-5: `sp_authenticate_user` References Non-Existent Column

**Location:** `package/database/migrations/2026_02_10_200002_create_sp_authenticate_user_procedure_postgres.php`

**Description:** The stored procedure selects `u.account_locked_until` from the `users` table. However, the `users` table migration defines the column as `locked_until` (no `account_` prefix). This will cause a runtime `column does not exist` error on every login attempt.

**Severity:** CRITICAL (P0) — login flow is broken.

**Fix:**
```sql
-- In sp_authenticate_user, change:
u.account_locked_until
-- To:
u.locked_until
```

---

### SB-6: `AuthController::signup()` Bypasses Stored Procedure and RBAC

**Location:** `package/app/Http/Controllers/Auth/AuthController.php:signup()` method

**Description:** The signup method directly calls `Account::create()`, `User::create()`, `EmailVerificationToken::createForUser()`, and `AuthAuditLog::logEvent()` via Eloquent. It does NOT call `sp_create_account`. This means:
1. The stored procedure's validation logic is bypassed
2. The app needs direct INSERT on `accounts`, `users`, `account_settings`, `account_flags`, `auth_audit_log` — which `portal_rw` does NOT have
3. If the app connects as `portal_rw` (as intended), signup will fail with `permission denied`
4. The `account_flags` and `account_settings` records are NOT created (unlike the SP which creates them)

**Severity:** CRITICAL (P0) — signup either fails (if RBAC enforced) or bypasses security controls (if running as owner).

**Fix:**
```php
// Replace direct Eloquent calls with stored procedure call:
$result = DB::select("SELECT * FROM sp_create_account(?, ?, ?, ?, ?, ?, ?, ?::inet)", [
    $request->company_name,
    $request->email,
    Hash::make($request->password),
    $request->first_name,
    $request->last_name,
    $request->phone,
    $request->country,
    $request->ip(),
]);
```

---

### SB-7: Login Uses Wrong PostgreSQL Syntax

**Location:** `package/app/Http/Controllers/Auth/AuthController.php:login()` — line `DB::select('CALL sp_authenticate_user(?, ?, ?)', ...)`

**Description:** PostgreSQL functions created with `CREATE FUNCTION ... RETURNS TABLE` are called with `SELECT * FROM function_name(...)`, not `CALL`. The `CALL` syntax is only for `CREATE PROCEDURE`. All 5 database objects are created as `FUNCTION`, not `PROCEDURE`. This will throw a syntax error.

**Severity:** CRITICAL (P0) — login is non-functional.

**Fix:**
```php
// Change from:
$result = DB::select('CALL sp_authenticate_user(?, ?, ?)', [...]);
// To:
$result = DB::select('SELECT * FROM sp_authenticate_user(?, ?::inet, ?)', [
    $request->email,
    $request->ip(),
    $passwordVerified,
]);
```

---

### SB-8: `User::saving()` Double-Hashes Passwords

**Location:** `package/app/Models/User.php` boot method

**Description:** The `saving` event calls `Hash::make($user->password)` whenever the password attribute is dirty. But `AuthController::signup()` already calls `Hash::make($request->password)` before setting it. The stored procedure `sp_create_account` also expects a pre-hashed password (`p_password TEXT`). This means passwords get double-hashed: `Hash::make(Hash::make(plaintext))`. Login will always fail because `Hash::check(plaintext, double_hashed)` returns false.

**Severity:** CRITICAL (P0) — user authentication is broken.

**Fix:**
```php
// Remove the automatic hashing from boot(), OR remove Hash::make() from the controller.
// Recommended: Remove from boot() since the controller and SP handle hashing:
protected static function boot()
{
    parent::boot();

    static::addGlobalScope('tenant', function (Builder $builder) {
        if (auth()->check() && auth()->user()->tenant_id) {
            $builder->where('users.tenant_id', auth()->user()->tenant_id);
        }
    });

    // REMOVE the saving() callback that double-hashes
}
```

---

## 4) COMPROMISE RESISTANCE REVIEW

### If portal credentials are leaked, what can an attacker access?

**Current state (app running as DB owner):** EVERYTHING. The attacker authenticates, gets a Sanctum token, and because the app runs as DB owner with no RLS enforcement (middleware not registered), they can access:
- All accounts (not just their own)
- All users across all tenants
- RED-side data (account_flags, auth_audit_log, admin_users) if any endpoint exposes it
- All API tokens, including hashes

**After fixes applied:** The attacker can access only their own tenant's data (accounts, users, API tokens, settings, credits) via the portal views and stored procedures. They CANNOT access RED-side tables (fraud scores, audit logs, admin users). RLS prevents cross-tenant queries even if a developer introduces a bug.

### If an endpoint is SQL-injected, what is the blast radius?

**Current state:** Complete database compromise. The app runs as owner/superuser, so injected SQL can:
- `SELECT * FROM admin_users` — steal admin credentials
- `SELECT * FROM account_flags` — read fraud data for all tenants
- `UPDATE users SET role = 'owner'` — privilege escalation
- `DROP TABLE accounts` — destructive attack
- `COPY accounts TO '/tmp/dump.csv'` — data exfiltration

**After fixes (app connects as portal_rw + RLS active):** Blast radius is limited to:
- Reading portal views (account_safe_view, user_profile_view, api_tokens_view) for the current tenant only
- Executing the 5 stored procedures
- Cannot read base tables, RED tables, or other tenants' data
- Cannot DROP, TRUNCATE, or ALTER anything

### Can an attacker bypass RLS with current grants/views/functions?

**YES, multiple paths:**

1. **`SECURITY DEFINER` functions:** All 5 stored procedures run as the function owner (typically postgres/superuser). If parameters are injectable (they aren't directly, but secondary injection via crafted email addresses or company names is theoretically possible through the `RAISE EXCEPTION 'Account creation failed: %', SQLERRM` pattern which concatenates user-controlled data), the injected SQL runs with full privileges.

2. **`accounts_system_bypass` policy:** Grants `TO PUBLIC` — any role can read the system account (UUID `00000000-0000-0000-0000-000000000001`). Low risk but unnecessary exposure.

3. **`accounts_isolation` NULL bypass:** If tenant context is unset, all accounts are visible.

4. **Table owner implicitly bypasses RLS:** PostgreSQL RLS does not apply to the table owner unless `ALTER TABLE ... FORCE ROW LEVEL SECURITY` is used. The app connects as the table owner. **This is the biggest gap.** Even if the middleware is registered and sets the variable, the table owner is exempt from RLS.

### Are there any "foot-guns"?

1. **SECURITY DEFINER without FORCE RLS:** All 5 functions use `SECURITY DEFINER` but do not set `ALTER TABLE ... FORCE ROW LEVEL SECURITY`. The function owner (superuser) implicitly bypasses RLS.

2. **`search_path = public`:** The procedures set `search_path = public` which is correct but does not include `pg_temp` — a sophisticated attacker could create a temp table shadowing a real table. Should be `SET search_path = public, pg_temp` to prevent temp-table hijacking (some procedures have this, others don't — inconsistent).

3. **`PUBLIC` schema permissions:** No explicit `REVOKE ALL ON SCHEMA public FROM PUBLIC` — the default PostgreSQL setup grants USAGE on the public schema to all roles, which may allow unexpected access paths.

4. **Eloquent `withoutGlobalScope('tenant')`:** Used in `AuthController::login()`, `resendVerification()`, `forgotPassword()`, `resetPassword()`. This bypasses the application-level tenant scope. While necessary for cross-tenant email lookup during login, it creates a pattern that developers may copy carelessly.

---

## 5) PERFORMANCE IMPROVEMENT RECOMMENDATIONS

### P0 — Must Do Before Pilot

#### P0-1: Add `FORCE ROW LEVEL SECURITY` to All Tables

Without this, the table owner (which is the app's connection user) bypasses RLS entirely.

```sql
ALTER TABLE accounts FORCE ROW LEVEL SECURITY;
ALTER TABLE users FORCE ROW LEVEL SECURITY;
ALTER TABLE api_tokens FORCE ROW LEVEL SECURITY;
ALTER TABLE user_sessions FORCE ROW LEVEL SECURITY;
ALTER TABLE email_verification_tokens FORCE ROW LEVEL SECURITY;
ALTER TABLE account_settings FORCE ROW LEVEL SECURITY;
ALTER TABLE account_credits FORCE ROW LEVEL SECURITY;
```

#### P0-2: Add Tenant-First Composite Indexes for Hot Queries

The current indexes are single-column. For RLS-filtered queries, PostgreSQL needs composite indexes with `tenant_id` as the leading column.

```sql
-- Users: login by email within tenant
CREATE INDEX idx_users_email ON users (email);  -- Global email lookup for login
CREATE INDEX idx_users_tenant_email ON users (tenant_id, email);  -- Already exists as UNIQUE
CREATE INDEX idx_users_tenant_status_role ON users (tenant_id, status, role);

-- API Tokens: lookup by hash (global) and by tenant (portal listing)
CREATE INDEX idx_api_tokens_hash ON api_tokens (token_hash);  -- Already exists as UNIQUE
CREATE INDEX idx_api_tokens_tenant_active ON api_tokens (tenant_id) WHERE revoked_at IS NULL AND (expires_at IS NULL OR expires_at > NOW());

-- Auth Audit Log: query by tenant + time range (most common query)
-- Already has idx_auth_audit_tenant_time — good.
-- Add partial index for failures (security monitoring):
CREATE INDEX idx_auth_audit_failures_recent ON auth_audit_log (created_at, ip_address)
    WHERE result = 'failure';

-- Account Credits: balance queries
-- Already has idx_account_credits_balance — good.
```

#### P0-3: Fix Account Number Generation — Concurrent Race Condition

The `generate_account_number()` trigger does:
```sql
SELECT COALESCE(MAX(CAST(SUBSTRING(account_number FROM 3) AS INTEGER)), 0) + 1
INTO next_number FROM accounts WHERE account_number ~ '^QS[0-9]+$';
```

This is a race condition under concurrent inserts — two transactions can read the same MAX and generate the same number, violating the UNIQUE constraint.

**Fix:** Use the existing sequence:
```sql
CREATE OR REPLACE FUNCTION generate_account_number()
RETURNS TRIGGER AS $$
BEGIN
    IF NEW.account_number IS NULL THEN
        NEW.account_number := 'QS' || LPAD(nextval('accounts_number_seq')::TEXT, 8, '0');
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;
```

### P1 — Before Production

#### P1-1: RLS Policy Performance — Avoid Subqueries

The `user_sessions` and `email_verification_tokens` RLS policies use subqueries:
```sql
USING (
    user_id IN (
        SELECT id FROM users
        WHERE tenant_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid
    )
)
```

This executes a subquery for **every row** evaluated. At scale (millions of sessions), this is extremely expensive.

**Fix:** Add `tenant_id` directly to these tables:
```sql
ALTER TABLE user_sessions ADD COLUMN tenant_id UUID REFERENCES accounts(id);
ALTER TABLE email_verification_tokens ADD COLUMN tenant_id UUID REFERENCES accounts(id);

-- Populate from users table:
UPDATE user_sessions s SET tenant_id = (SELECT tenant_id FROM users u WHERE u.id = s.user_id);
UPDATE email_verification_tokens e SET tenant_id = (SELECT tenant_id FROM users u WHERE u.id = e.user_id);

-- Then simplify RLS:
CREATE POLICY user_sessions_tenant_isolation ON user_sessions
FOR ALL
USING (tenant_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid);
```

#### P1-2: Partition `auth_audit_log` by Time

This table will grow unboundedly (every login attempt, every auth event). At scale:

```sql
-- Convert to range-partitioned by month:
CREATE TABLE auth_audit_log (
    id BIGSERIAL,
    created_at TIMESTAMPTZ NOT NULL,
    -- ... other columns
) PARTITION BY RANGE (created_at);

-- Create partitions:
CREATE TABLE auth_audit_log_2026_01 PARTITION OF auth_audit_log
    FOR VALUES FROM ('2026-01-01') TO ('2026-02-01');
CREATE TABLE auth_audit_log_2026_02 PARTITION OF auth_audit_log
    FOR VALUES FROM ('2026-02-01') TO ('2026-03-01');
-- ... automated via pg_partman or cron
```

#### P1-3: Add Connection Wrapping to Ensure Transaction Scope for `SET LOCAL`

`SET LOCAL` only works inside a transaction. If Laravel's default connection doesn't wrap requests in a transaction, the variable set by the middleware has no effect.

```php
// In SetTenantContext middleware, wrap in explicit transaction check:
if (DB::transactionLevel() === 0) {
    // SET (not SET LOCAL) if no transaction is active
    DB::statement("SET app.current_tenant_id = ?", [$user->tenant_id]);
} else {
    DB::statement("SET LOCAL app.current_tenant_id = ?", [$user->tenant_id]);
}
```

Or better: always use `SET` (session-scoped) and `RESET` in terminate():
```php
DB::statement("SET app.current_tenant_id = ?", [$user->tenant_id]);
// ...
// In terminate():
DB::statement("RESET app.current_tenant_id");
```

### P2 — Optimisations

#### P2-1: Use Materialized Views for Reporting

Portal dashboard queries (credit balance, message counts, etc.) can be expensive on live tables. Create materialized views refreshed on a schedule:

```sql
CREATE MATERIALIZED VIEW mv_account_credit_summary AS
SELECT
    account_id,
    SUM(credits_awarded) as total_awarded,
    SUM(credits_used) as total_used,
    SUM(credits_remaining) as total_remaining,
    COUNT(*) as credit_entries
FROM account_credits
GROUP BY account_id;

CREATE UNIQUE INDEX idx_mv_credit_summary_account ON mv_account_credit_summary (account_id);

-- Refresh periodically:
REFRESH MATERIALIZED VIEW CONCURRENTLY mv_account_credit_summary;
```

#### P2-2: Add `VACUUM` / `ANALYZE` Schedule

For tables with heavy write patterns (`auth_audit_log`, `mobile_verification_attempts`, `user_sessions`):

```sql
-- Aggressive autovacuum for high-write tables:
ALTER TABLE auth_audit_log SET (
    autovacuum_vacuum_scale_factor = 0.01,
    autovacuum_analyze_scale_factor = 0.005
);

ALTER TABLE mobile_verification_attempts SET (
    autovacuum_vacuum_scale_factor = 0.01,
    autovacuum_analyze_scale_factor = 0.005
);
```

#### P2-3: Import Strategy (Future — Message Tables)

When message tables are added, use staging tables + COPY for bulk imports:

```sql
CREATE UNLOGGED TABLE staging_messages (LIKE messages INCLUDING ALL);
-- COPY bulk data into staging
-- INSERT INTO messages SELECT * FROM staging_messages ON CONFLICT DO NOTHING;
-- TRUNCATE staging_messages;
```

---

## 6) MIGRATION & OPERATIONS REVIEW

### Migration Locking Concerns

| Migration | Risk | Notes |
|-----------|------|-------|
| `CREATE TYPE ... AS ENUM` | LOW | DDL, acquires brief lock |
| `CREATE TABLE ...` | NONE | New tables, no existing data |
| `ALTER TABLE ... ADD COLUMN` | LOW-MEDIUM | On existing tables, acquires `ACCESS EXCLUSIVE` lock. Safe for new tables, dangerous for tables with data. |
| `ALTER TABLE ... ENABLE ROW LEVEL SECURITY` | LOW | Brief metadata lock |
| `CREATE POLICY ...` | LOW | Metadata change only |
| `CREATE INDEX ...` | **MEDIUM** | Without `CONCURRENTLY`, acquires `SHARE` lock blocking writes. For production, always use `CREATE INDEX CONCURRENTLY`. |
| `CREATE OR REPLACE FUNCTION ...` | LOW | Metadata DDL |

**Recommendation:** All index creation on existing tables should use `CONCURRENTLY`:
```sql
CREATE INDEX CONCURRENTLY idx_name ON table_name (...);
```
Note: `CONCURRENTLY` cannot run inside a transaction, so these must be in separate migrations that don't use Laravel's transaction wrapper.

### Rollback Safety

All migrations have `down()` methods that properly:
- Drop triggers before functions
- Drop functions
- Drop tables with `CASCADE` for ENUM types
- Drop ENUM types

**Gap:** No data backup strategy. For production, add `pg_dump` before migration runs.

### Monitoring Recommendations

```sql
-- 1. Slow query monitoring (set in postgresql.conf):
-- log_min_duration_statement = 500  -- Log queries > 500ms

-- 2. RLS violation monitoring (custom):
CREATE OR REPLACE FUNCTION log_rls_violation()
RETURNS event_trigger AS $$
BEGIN
    -- Log any attempt to bypass RLS
    RAISE WARNING 'RLS-related DDL detected: %', tg_tag;
END;
$$ LANGUAGE plpgsql;

-- 3. Failed auth monitoring:
-- Alert if > 10 failed logins per IP in 5 minutes
-- Alert if > 50 failed logins per tenant in 1 hour
-- Alert if any account_locked events

-- 4. Connection pool monitoring:
-- Track SET/RESET of app.current_tenant_id
-- Alert if tenant context not set for authenticated requests
```

---

## 7) RECOMMENDED NEXT STEPS

### Phase 1: Fix Ship-Blockers (Estimated: 4-6 hours)

| Step | Action | Acceptance Criteria |
|------|--------|-------------------|
| 1.1 | Remove all `bin2hex`/`hex2bin` UUID mutators from all models | Eloquent reads/writes correct 36-char UUID strings on PostgreSQL |
| 1.2 | Register `SetTenantContext` in `Kernel.php` API middleware group | `app.current_tenant_id` is set for every authenticated API request |
| 1.3 | Remove NULL-bypass from `accounts_isolation` RLS policy | Unset tenant context returns zero rows, not all rows |
| 1.4 | Add `FORCE ROW LEVEL SECURITY` to all tenant-scoped tables | RLS applies even when connected as table owner |
| 1.5 | Fix `sp_authenticate_user` column reference (`account_locked_until` → `locked_until`) | Login flow executes without SQL error |
| 1.6 | Fix `AuthController::login()` to use `SELECT * FROM sp_authenticate_user(...)` instead of `CALL` | Login flow works on PostgreSQL |
| 1.7 | Fix double-hashing: remove `Hash::make()` from either `User::saving()` or `AuthController::signup()` | Passwords verify correctly after creation |
| 1.8 | Refactor `AuthController::signup()` to use `sp_create_account` stored procedure | Signup works with `portal_rw` role permissions |

### Phase 2: Enforce RBAC at Runtime (Estimated: 2-3 hours)

| Step | Action | Acceptance Criteria |
|------|--------|-------------------|
| 2.1 | Configure Laravel to connect as `portal_rw` for the default connection | `SELECT current_user` returns `portal_rw` |
| 2.2 | Add separate `internal` database connection for queue workers/admin using `svc_red` | Background jobs can access RED tables; portal cannot |
| 2.3 | Add `SET ROLE` management for admin endpoints (if needed) | Admin API uses appropriate elevated role |
| 2.4 | Remove placeholder passwords from committed SQL; use environment variables | No passwords in version control |
| 2.5 | Add `REVOKE ALL ON SCHEMA public FROM PUBLIC` to grants script | No unintended schema-level permissions |

### Phase 3: Complete Missing Tables (Estimated: 4-6 hours)

| Step | Action | Acceptance Criteria |
|------|--------|-------------------|
| 3.1 | Convert remaining 10 platform tables (suppliers, gateways, rate_cards, routing, etc.) | All tables exist in PostgreSQL with correct types |
| 3.2 | Add RLS to `routing_customer_overrides` (has tenant_id) | Customer overrides are tenant-isolated |
| 3.3 | Add grants for platform tables to appropriate roles | portal_ro can read shared data; portal_rw cannot write |

### Phase 4: Performance & Operations (Estimated: 2-3 hours)

| Step | Action | Acceptance Criteria |
|------|--------|-------------------|
| 4.1 | Fix account number generation race condition | Concurrent signups produce unique account numbers |
| 4.2 | Add `tenant_id` to `user_sessions` and `email_verification_tokens` | RLS policies use direct comparison, not subquery |
| 4.3 | Add retention policies (cleanup jobs for expired tokens, old audit entries) | Automated data lifecycle management |
| 4.4 | Configure autovacuum for high-write tables | `auth_audit_log` and `mobile_verification_attempts` have aggressive vacuum |

### Phase 5: Security Hardening & Testing (Estimated: 3-4 hours)

| Step | Action | Acceptance Criteria |
|------|--------|-------------------|
| 5.1 | Write automated integration tests for RLS (tenant A cannot see tenant B) | CI/CD verifies tenant isolation on every deploy |
| 5.2 | Write tests for RBAC (portal_rw cannot SELECT base tables) | CI/CD verifies grant restrictions |
| 5.3 | Add `search_path = public, pg_temp` consistently to all SECURITY DEFINER functions | No temp-table hijacking possible |
| 5.4 | Add webhook_secret encryption at model level (use Laravel's `encrypt()`) | No plaintext secrets in database |
| 5.5 | Penetration test: attempt cross-tenant access via API | Zero cross-tenant data exposure |

---

## Appendix A: Files Reviewed

| File | Type | Verdict |
|------|------|---------|
| `2026_02_10_000001_create_accounts_table_postgres.php` | Migration | RLS bypass issue (SB-3) |
| `2026_02_10_000002_create_users_table_postgres.php` | Migration | Good RLS, good indexes |
| `2026_02_10_000003_create_user_sessions_table_postgres.php` | Migration | Subquery RLS (P1-1) |
| `2026_02_10_000004_create_api_tokens_table_postgres.php` | Migration | Good RLS, good grants |
| `2026_02_10_000005_create_password_reset_tokens_table_postgres.php` | Migration | No RLS (acceptable) |
| `2026_02_10_000006_create_email_verification_tokens_table_postgres.php` | Migration | Subquery RLS (P1-1) |
| `2026_02_10_000007_create_account_settings_table_postgres.php` | Migration | Good RLS |
| `2026_02_10_000008_create_account_credits_table_postgres.php` | Migration | Good RLS |
| `2026_02_10_000009_create_mobile_verification_attempts_table_postgres.php` | Migration | RED side, no RLS (correct) |
| `2026_02_10_100001_create_admin_users_table_postgres.php` | Migration | RED side, MFA trigger (good) |
| `2026_02_10_100002_create_auth_audit_log_table_postgres.php` | Migration | Good indexes, immutable |
| `2026_02_10_100003_create_account_flags_table_postgres.php` | Migration | RED side, no RLS (correct) |
| `2026_02_10_100004_create_password_history_table_postgres.php` | Migration | RED side, no RLS (correct) |
| `2026_02_10_200001_create_sp_create_account_procedure_postgres.php` | Stored Proc | SECURITY DEFINER (risk noted) |
| `2026_02_10_200002_create_sp_authenticate_user_procedure_postgres.php` | Stored Proc | Column name bug (SB-5) |
| `2026_02_10_200003_create_sp_update_user_profile_procedure_postgres.php` | Stored Proc | SECURITY DEFINER (risk noted) |
| `2026_02_10_200004_create_sp_create_api_token_procedure_postgres.php` | Stored Proc | SECURITY DEFINER (risk noted) |
| `2026_02_10_200005_create_sp_update_account_settings_procedure_postgres.php` | Stored Proc | SECURITY DEFINER (risk noted) |
| `2026_02_10_300001_create_account_safe_view_postgres.php` | View | Good, excludes sensitive fields |
| `2026_02_10_300002_create_user_profile_view_postgres.php` | View | Good, excludes password/MFA |
| `2026_02_10_300003_create_api_tokens_view_postgres.php` | View | Good, excludes token_hash |
| `01_create_roles_and_grants.sql` | Grants | Good design, not enforced at runtime (SB-4) |
| `SetTenantContext.php` | Middleware | Good design, not registered (SB-2) |
| `AuthController.php` | Controller | Multiple bugs (SB-6, SB-7, SB-8) |
| `Account.php` | Model | UUID mutator corruption (SB-1) |
| `User.php` | Model | UUID mutator corruption + double hash (SB-1, SB-8) |
| `ApiToken.php` | Model | UUID mutator corruption (SB-1) |
| `AuthAuditLog.php` | Model | UUID mutator corruption (SB-1) |
| `Kernel.php` | Middleware Registration | Missing SetTenantContext (SB-2) |

---

## Appendix B: Positive Findings

Despite the ship-blockers, the **architectural design** is strong:

1. **RED/GREEN trust boundary** is well-conceived with clear data classification comments throughout
2. **View-based data exposure** correctly hides sensitive fields (password, mfa_secret, token_hash)
3. **ENUM types** provide strong domain constraints at the database level
4. **INET type** for IP addresses with proper validation
5. **JSONB** usage for flexible fields (scopes, ip_whitelist, webhook_urls) is appropriate
6. **Audit log taxonomy** is comprehensive with proper event types
7. **Immutability enforcement** via INSERT-only grants on audit tables
8. **Password history** (last 12) with proper hash comparison
9. **Account lockout** (5 attempts, 30 minutes) with audit logging
10. **Mobile verification** with rate limiting and SHA-256 hashed codes
11. **MFA enforcement trigger** on admin_users prevents MFA disablement
12. **UUID primary keys** prevent enumeration attacks
13. **Token prefix** approach (show first 8 chars) is industry best practice

The design is **enterprise-grade in intent**. The issues are all in the **integration layer** — connecting the well-designed database to the application correctly. Once the ship-blockers are fixed, this will be a genuinely strong security architecture.

---

*Review complete. Total files analyzed: 28. Ship-blockers identified: 8. Performance recommendations: 8.*
