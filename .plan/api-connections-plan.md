# API Connections Module — Implementation Plan

## Design Decisions (from user answers)
- **Credential storage**: SHA-256 hash + last 4 chars display. Shown once at creation. Irreversible.
- **Auth middleware**: Full middleware that validates API key / basic auth, resolves tenant + sub-account, enforces IP allowlist.
- **Sub-account scope**: Strict enforcement. Connection can only access data within its assigned sub-account.
- **Integration type**: Partner-specific config (SystmOne, Rio, EMIS, Accurx) — needs dedicated JSONB column.
- **Rate limiting**: Per-connection limits, configurable by admin. Defaults by type (Bulk: 100/min, Campaign: 30/min, Integration: 50/min).
- **Webhooks**: Store URLs only for now. No outbound dispatch yet.
- **Audit logging**: Dedicated audit events table (api_connection_audit_events).
- **IP allowlist**: Enforced in middleware. 403 if caller IP not in list (when list is enabled).
- **Key rotation**: Immediate revocation. Old key stops working instantly.
- **State machine**: Draft → Active → Suspended → Archived. Archived is terminal. Test env can be promoted to Live.

---

## Phase 1: Database Migrations

### Migration 1: `create_api_connections_table`
PostgreSQL enums:
- `api_connection_type`: 'bulk', 'campaign', 'integration'
- `api_connection_auth_type`: 'api_key', 'basic_auth'
- `api_connection_status`: 'draft', 'active', 'suspended', 'archived'
- `api_connection_environment`: 'test', 'live'

Table `api_connections`:
- `id` UUID PK (gen_random_uuid)
- `account_id` UUID FK → accounts (NOT NULL)
- `sub_account_id` UUID FK → sub_accounts (NULLABLE — null = account-level)
- `name` VARCHAR(255) NOT NULL
- `description` TEXT NULLABLE
- `type` api_connection_type NOT NULL
- `auth_type` api_connection_auth_type NOT NULL
- `environment` api_connection_environment NOT NULL DEFAULT 'test'
- `status` api_connection_status NOT NULL DEFAULT 'draft'
- `api_key_hash` VARCHAR(64) NULLABLE — SHA-256 hex of full key
- `api_key_prefix` VARCHAR(12) NULLABLE — e.g., 'sk_live_' or 'sk_test_'
- `api_key_last4` VARCHAR(4) NULLABLE — last 4 chars for display
- `basic_auth_username` VARCHAR(255) NULLABLE
- `basic_auth_password_hash` VARCHAR(64) NULLABLE — SHA-256 hex
- `ip_allowlist_enabled` BOOLEAN NOT NULL DEFAULT false
- `ip_allowlist` JSONB NOT NULL DEFAULT '[]'
- `webhook_dlr_url` VARCHAR(2048) NULLABLE
- `webhook_inbound_url` VARCHAR(2048) NULLABLE
- `partner_name` VARCHAR(100) NULLABLE — for integration type: 'systmone', 'rio', 'emis', 'accurx'
- `partner_config` JSONB NOT NULL DEFAULT '{}' — partner-specific settings
- `rate_limit_per_minute` INTEGER NOT NULL DEFAULT 100
- `capabilities` JSONB NOT NULL DEFAULT '[]' — auto-set based on type
- `last_used_at` TIMESTAMPTZ NULLABLE
- `last_used_ip` VARCHAR(45) NULLABLE
- `created_by` VARCHAR(255) NULLABLE
- `suspended_at` TIMESTAMPTZ NULLABLE
- `suspended_by` VARCHAR(255) NULLABLE
- `suspended_reason` TEXT NULLABLE
- `archived_at` TIMESTAMPTZ NULLABLE
- `archived_by` VARCHAR(255) NULLABLE
- `created_at` TIMESTAMPTZ
- `updated_at` TIMESTAMPTZ

Indexes:
- UNIQUE: (account_id, name) — no duplicate names per account
- INDEX: (account_id, status) — common listing filter
- INDEX: (api_key_hash) — fast lookup during auth
- INDEX: (basic_auth_username) — fast lookup during auth
- INDEX: (account_id, sub_account_id) — sub-account scoping

RLS policy: account_id = current_setting('app.current_tenant_id')
UUID trigger: gen_random_uuid() on insert
Account validation trigger: verify account_id exists

### Migration 2: `create_api_connection_audit_events_table`
Table `api_connection_audit_events`:
- `id` UUID PK
- `account_id` UUID NOT NULL
- `api_connection_id` UUID FK → api_connections
- `event_type` VARCHAR(50) NOT NULL — 'created', 'suspended', 'reactivated', 'archived', 'key_regenerated', 'password_changed', 'converted_to_live', 'endpoints_updated', 'security_updated', 'updated'
- `actor_type` VARCHAR(20) NOT NULL — 'customer', 'admin', 'system'
- `actor_id` VARCHAR(255) NULLABLE
- `actor_name` VARCHAR(255) NULLABLE
- `metadata` JSONB NOT NULL DEFAULT '{}'
- `ip_address` VARCHAR(45) NULLABLE
- `created_at` TIMESTAMPTZ NOT NULL DEFAULT NOW()

Indexes:
- INDEX: (api_connection_id, created_at DESC)
- INDEX: (account_id, created_at DESC)

RLS policy: same as parent

---

## Phase 2: Eloquent Models

### `ApiConnection` model
- Global tenant scope (session-based, same pattern as Contact)
- $fillable: name, description, type, auth_type, environment, status, sub_account_id, ip_allowlist_enabled, ip_allowlist, webhook_dlr_url, webhook_inbound_url, partner_name, partner_config, rate_limit_per_minute, capabilities
- NOT fillable: api_key_hash, api_key_prefix, api_key_last4, basic_auth_username, basic_auth_password_hash (set via dedicated methods)
- Relationships: account(), subAccount(), auditEvents()
- Methods:
  - generateApiKey(): string — creates key, stores hash+prefix+last4, returns raw key
  - generateBasicAuth(): array — creates username+password, stores hash, returns raw credentials
  - regenerateApiKey(): string — same as above but for existing connection
  - regeneratePassword(): string — same for basic auth
  - verifyApiKey(string $key): bool — hash and compare
  - verifyPassword(string $password): bool — hash and compare
  - suspend(string $reason, string $actorId): void
  - reactivate(string $actorId): void
  - archive(string $actorId): void
  - convertToLive(string $actorId): void
  - isActive(): bool
  - isSuspended(): bool
  - isArchived(): bool
  - isDraft(): bool
  - isTestEnvironment(): bool
  - toPortalArray(): array — for customer view (masked credentials)
  - toAdminArray(): array — for admin view (includes account info)
- Scopes: active(), suspended(), archived(), draft(), forEnvironment(), ofType()

### `ApiConnectionAuditEvent` model
- Global tenant scope
- Append-only (no update/delete)
- toPortalArray(): array

---

## Phase 3: API Auth Middleware

### `ApiKeyAuthenticate` middleware
Registered as 'api.connection' middleware.
Flow:
1. Check for `Authorization: Bearer sk_live_xxx` or `X-API-Key: sk_live_xxx` header
2. OR check for `Authorization: Basic base64(username:password)` header
3. Hash the key/password → lookup in api_connections table
4. Verify connection is active (not draft/suspended/archived)
5. Verify environment matches (live connection for production endpoints)
6. Check IP allowlist (if enabled) → 403 if not allowed
7. Check rate limit → 429 if exceeded
8. Set tenant context: DB::select("SELECT set_config('app.current_tenant_id', ?, false)", [$connection->account_id])
9. Set sub-account context on request: $request->merge(['_api_connection' => $connection])
10. Update last_used_at and last_used_ip
11. Proceed to controller

Rate limit implementation: Use Laravel's `RateLimiter` with key `api_connection:{connection_id}`, limit from `rate_limit_per_minute`.

---

## Phase 4: Controllers

### `ApiConnectionController` (Customer Portal)
Route prefix: `api/api-connections` under `customer.auth` + `throttle:60,1`

Methods:
- `index(Request)` — list connections (with search, filters, sort)
- `store(Request)` — create connection (returns credentials in response)
- `show(string $id)` — single connection details
- `suspend(string $id)` — suspend active connection
- `reactivate(string $id)` — reactivate suspended connection
- `archive(string $id)` — archive suspended connection
- `convertToLive(string $id)` — promote test to live
- `regenerateKey(string $id)` — regenerate API key
- `changePassword(string $id)` — regenerate basic auth password

### `AdminApiConnectionController` (Admin Portal)
Route prefix: `admin/api/api-connections` under admin middleware

Same methods as customer + extras:
- `updateEndpoints(Request, string $id)` — edit URLs
- `updateSecurity(Request, string $id)` — edit IP allowlist
- No tenant scoping — uses `withoutGlobalScopes()` with explicit account_id filtering
- All actions include account_id context

---

## Phase 5: Routes

### Customer routes (web.php):
```
Route::middleware(['customer.auth', 'throttle:60,1'])->prefix('api/api-connections')->group(function () {
    Route::get('/', ...)->name('api.api-connections.index');
    Route::post('/', ...)->name('api.api-connections.store');
    Route::get('/{id}', ...)->name('api.api-connections.show');
    Route::put('/{id}/suspend', ...)->name('api.api-connections.suspend');
    Route::put('/{id}/reactivate', ...)->name('api.api-connections.reactivate');
    Route::put('/{id}/archive', ...)->name('api.api-connections.archive');
    Route::put('/{id}/convert-to-live', ...)->name('api.api-connections.convert-to-live');
    Route::post('/{id}/regenerate-key', ...)->name('api.api-connections.regenerate-key');
    Route::post('/{id}/change-password', ...)->name('api.api-connections.change-password');
});
```

### Admin routes (web.php inside admin group):
```
Route::prefix('api/api-connections')->controller(AdminApiConnectionController::class)->group(function () {
    Route::get('/', 'index');
    Route::post('/', 'store');
    Route::get('/{id}', 'show');
    Route::put('/{id}/suspend', 'suspend');
    Route::put('/{id}/reactivate', 'reactivate');
    Route::put('/{id}/archive', 'archive');
    Route::put('/{id}/convert-to-live', 'convertToLive');
    Route::post('/{id}/regenerate-key', 'regenerateKey');
    Route::post('/{id}/change-password', 'changePassword');
    Route::put('/{id}/endpoints', 'updateEndpoints');
    Route::put('/{id}/security', 'updateSecurity');
});
```

---

## File Summary

| # | File | Type |
|---|------|------|
| 1 | `database/migrations/2026_02_17_400001_create_api_connections_table.php` | Migration |
| 2 | `database/migrations/2026_02_17_400002_create_api_connection_audit_events_table.php` | Migration |
| 3 | `app/Models/ApiConnection.php` | Model |
| 4 | `app/Models/ApiConnectionAuditEvent.php` | Model |
| 5 | `app/Http/Middleware/ApiKeyAuthenticate.php` | Middleware |
| 6 | `app/Http/Controllers/Api/ApiConnectionController.php` | Controller (customer) |
| 7 | `app/Http/Controllers/Admin/AdminApiConnectionController.php` | Controller (admin) |
| 8 | `routes/web.php` | Route additions |
| 9 | `app/Http/Kernel.php` | Register middleware |
