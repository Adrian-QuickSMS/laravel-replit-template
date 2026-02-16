# QuickSMS — Replit Agent Instructions

## CRITICAL: READ THIS ENTIRE FILE BEFORE MAKING ANY CHANGES

QuickSMS is a multi-tenant Laravel SMS platform on PostgreSQL. Every decision below is deliberate. Do NOT deviate from these patterns. Do NOT "improve" or "simplify" them. Do NOT introduce new patterns. Follow what exists.

---

## HARD RULES — NEVER VIOLATE THESE

1. **NEVER reintroduce mock data.** All mock/hardcoded data was removed. Controllers query the database. JS services call real API endpoints. Do NOT add `useMockData: true`, do NOT add hardcoded arrays, do NOT add sample/dummy/placeholder data.
2. **NEVER bypass tenant isolation.** Every query MUST be scoped by `account_id` via model global scopes. Every table that holds tenant data MUST have RLS. There are NO exceptions.
3. **NEVER use MySQL syntax.** The database is PostgreSQL. Use `ilike` not `LIKE` for case-insensitive. Use `gen_random_uuid()` not `UUID()`. Use `RAISE EXCEPTION` not `SIGNAL`. Use `JSONB` not `JSON`. Use `TIMESTAMPTZ` for timezone-aware timestamps.
4. **NEVER create SQLite or MySQL migrations.** All migrations use raw PostgreSQL via `DB::statement()` and `DB::unprepared()` for enums, triggers, RLS, and functions.
5. **NEVER change the frontend framework.** The UI is Blade + jQuery + Bootstrap 5 (Fillow template). Do NOT introduce React, Vue, Inertia, Livewire, Alpine.js, or Tailwind.
6. **NEVER modify existing migrations.** Create new migrations only. Migration filenames use the pattern `YYYY_MM_DD_SSSSSS_description.php`.
7. **NEVER delete or rename existing model files, controllers, or routes** unless explicitly asked.
8. **NEVER commit .env files, credentials, or secrets.**

---

## Architecture Overview

```
PHP 8.3 / Laravel 10 / PostgreSQL 16
UI: Blade templates + jQuery + Bootstrap 5 (Fillow SaaS Admin Template)
Navigation: MetisMenu
Multi-tenant: account_id FK on every tenant table + PostgreSQL RLS + Laravel global scopes
Primary keys: UUID (gen_random_uuid()) — string type, non-incrementing
Payments: Stripe SDK + HubSpot Invoices
```

---

## Tenant Isolation — THREE LAYERS (ALL MANDATORY)

### Layer 1: Laravel Global Scopes (Application)
Every tenant-scoped model has this in `boot()`:
```php
static::addGlobalScope('tenant', function (Builder $builder) {
    if (auth()->check() && auth()->user()->tenant_id) {
        $builder->where('TABLE_NAME.account_id', auth()->user()->tenant_id);
    }
});
```
**11 models have this scope:** User, Contact, SenderId, SubAccount, ContactList, Tag, OptOutList, ContactCustomFieldDefinition, ContactTimelineEvent, OptOutRecord, RcsAsset.

**Account model does NOT have a global scope** — it IS the tenant root.

### Layer 2: PostgreSQL Session Variable (Middleware → Database)
Set by `CustomerAuthenticate` middleware (`app/Http/Middleware/CustomerAuthenticate.php`):
```php
DB::select("SELECT set_config('app.current_tenant_id', ?, false)", [session('customer_tenant_id')]);
```
Also set by `SetTenantContext` middleware (`app/Http/Middleware/SetTenantContext.php`):
```php
DB::select("SELECT set_config('app.current_tenant_id', ?, false)", [$user->tenant_id]);
```

### Layer 3: PostgreSQL Row Level Security (Database — Fail-Closed)
Every tenant table has:
```sql
ALTER TABLE table_name ENABLE ROW LEVEL SECURITY;
ALTER TABLE table_name FORCE ROW LEVEL SECURITY;

CREATE POLICY table_tenant_isolation ON table_name
FOR ALL
USING (account_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid)
WITH CHECK (account_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid);

CREATE POLICY table_postgres_bypass ON table_name
FOR ALL TO postgres USING (true) WITH CHECK (true);
```

**If `app.current_tenant_id` is not set → zero rows returned.** This is intentional (fail-closed).

---

## Model Pattern — FOLLOW EXACTLY FOR NEW MODELS

```php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // only if table has deleted_at

class ExampleModel extends Model
{
    // only include SoftDeletes if table has deleted_at column
    use SoftDeletes;

    protected $table = 'exact_table_name';
    protected $keyType = 'string';      // ALWAYS string for UUID
    public $incrementing = false;        // ALWAYS false for UUID

    protected $fillable = [
        'account_id',
        // ... other columns
    ];

    protected $casts = [
        'id' => 'string',
        'account_id' => 'string',
        // cast UUIDs to string, JSONB to array, booleans to boolean, dates to datetime
    ];

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (auth()->check() && auth()->user()->tenant_id) {
                $builder->where('exact_table_name.account_id', auth()->user()->tenant_id);
            }
        });
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    // Always include toPortalArray() for data passed to Blade views or JSON responses
    public function toPortalArray(): array { /* ... */ }
}
```

**Key rules:**
- `$keyType = 'string'` and `$incrementing = false` on EVERY model (UUID PKs)
- Global tenant scope on EVERY tenant-scoped model — scope column is `account_id`
- The Users table uses `tenant_id` as its FK to accounts (legacy naming). All other tables use `account_id`.
- PostgreSQL enums are NOT in `$fillable` or `$casts` — read them with `$this->getRawOriginal('column_name')`
- JSONB columns cast to `'array'`

---

## Migration Pattern — FOLLOW EXACTLY FOR NEW MIGRATIONS

```php
public function up(): void
{
    // 1. Create ENUM types FIRST
    DB::statement("CREATE TYPE my_enum AS ENUM ('value1', 'value2')");

    // 2. Create table with Schema::create
    Schema::create('table_name', function (Blueprint $table) {
        $table->uuid('id')->primary();
        $table->uuid('account_id');
        $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
        // ... other columns ...
        $table->timestamps();
    });

    // 3. Add ENUM columns via raw SQL (Blueprint doesn't support PG enums)
    DB::statement("ALTER TABLE table_name ADD COLUMN status my_enum DEFAULT 'value1'");

    // 4. Add indexes via raw SQL
    DB::statement("CREATE INDEX idx_name ON table_name (columns)");
    DB::statement("CREATE INDEX idx_jsonb ON table_name USING GIN (jsonb_column)");

    // 5. UUID trigger function
    DB::unprepared("CREATE OR REPLACE FUNCTION generate_uuid_table_name() ...");
    DB::unprepared("CREATE TRIGGER before_insert_table_uuid ...");

    // 6. Validation trigger (fail-closed)
    DB::unprepared("CREATE OR REPLACE FUNCTION validate_table_account_id() ...");
    DB::unprepared("CREATE TRIGGER before_insert_table_account_validation ...");

    // 7. RLS (ALWAYS — on every tenant table)
    DB::unprepared("ALTER TABLE table_name ENABLE ROW LEVEL SECURITY");
    DB::unprepared("ALTER TABLE table_name FORCE ROW LEVEL SECURITY");
    DB::unprepared("CREATE POLICY table_tenant_isolation ON table_name FOR ALL USING (...) WITH CHECK (...)");
    DB::unprepared("CREATE POLICY table_postgres_bypass ON table_name FOR ALL TO postgres USING (true) WITH CHECK (true)");
}

public function down(): void
{
    // Drop in REVERSE order: policies → triggers → functions → table → types
    DB::unprepared("DROP POLICY IF EXISTS ...");
    DB::unprepared("DROP TRIGGER IF EXISTS ...");
    DB::unprepared("DROP FUNCTION IF EXISTS ...");
    Schema::dropIfExists('table_name');
    DB::statement("DROP TYPE IF EXISTS my_enum CASCADE");
}
```

---

## Controller Patterns

### Blade Page Controllers (`QuickSMSController.php`)
- Query the database using Eloquent models
- Pass data to views via `toPortalArray()` — NEVER hardcode data arrays
- Protected by `customer.auth` middleware (set in `routes/web.php`)

```php
public function allContacts()
{
    $contacts = Contact::with(['tags', 'lists'])->orderByDesc('created_at')->get()
        ->map(fn($c) => $c->toPortalArray())->toArray();
    // ... query tags, lists from DB ...
    return view('quicksms.contacts.all-contacts', [...]);
}
```

### API Controllers (`app/Http/Controllers/Api/`)
- Return JSON responses: `response()->json([...])`
- Use `$request->validate([...])` for input validation
- Set `account_id` from `auth()->user()->tenant_id`
- Return 201 for creates, 200 for updates, 422 for validation errors

### Existing API Controllers (DO NOT RENAME OR RESTRUCTURE):
| Controller | Prefix | Purpose |
|---|---|---|
| `ContactBookApiController` | `/api/contacts`, `/api/tags`, `/api/contact-lists`, `/api/opt-out-lists` | Contact Book CRUD + bulk ops |
| `BillingApiController` | `/api/billing` | Finance data |
| `PurchaseApiController` | `/api/purchase` | HubSpot products |
| `InvoiceApiController` | `/api/invoices` | HubSpot invoices |
| `TopUpApiController` | `/api/topup` | Stripe checkout |
| `ReportingDashboardApiController` | `/api/reporting/dashboard` | Dashboard KPIs |
| `RcsAssetController` | `/api/rcs/assets` | RCS media processing |
| `WebhookController` | `/api/webhooks`, `/api/account` | Stripe/HubSpot webhooks |

---

## Route Structure

### Web Routes (`routes/web.php`)
- Customer portal: `middleware('customer.auth')` → `QuickSMSController`
- SenderID API: `middleware('customer.auth')` → `SenderIdController`
- Admin panel: `middleware(AdminIpAllowlist, AdminAuthenticate)` → admin controllers

### API Routes (`routes/api.php`)
- Contact Book: `/api/contacts/*`, `/api/tags/*`, `/api/contact-lists/*`, `/api/opt-out-lists/*`
- All other prefixes listed in table above
- API routes rely on session-based auth (called from authenticated Blade pages)

---

## JavaScript Service Pattern

### contacts-service.js
- `useMockData: false` — calls `/api/contacts/bulk/*` endpoints via `fetch()`
- Methods: `bulkAddToList`, `bulkRemoveFromList`, `bulkAddTags`, `bulkRemoveTags`, `bulkDelete`, `bulkExport`
- Uses `_headers()` helper with CSRF token from `<meta name="csrf-token">`

### contact-timeline-service.js
- `useMockData: false` — calls `/api/contacts/{id}/timeline` and `/api/contacts/{id}/reveal-msisdn`
- Contains UI rendering helpers (buildOutboundDetails, buildTagChangeDetails, etc.) — these are used for rendering timeline HTML client-side. DO NOT remove them.
- `AuditLogger` module emits audit events for sensitive operations

**When adding new JS service files, follow the same pattern:** IIFE module, `config.useMockData: false`, real `fetch()` calls, CSRF token from meta tag.

---

## Database Facts

| Item | Value |
|---|---|
| Database | PostgreSQL 16 |
| Connection name | `pgsql` |
| Host | `helium` (Replit built-in) |
| Database name | `heliumdb` |
| User | `postgres` |
| Stored procedures | `sp_create_account`, `sp_authenticate_user`, `sp_update_user_profile`, `sp_create_api_token`, `sp_update_account_settings` |
| System account UUID | `00000000-0000-0000-0000-000000000001` |

### Contact Book Tables (9 tables — Feb 2026)
| Table | Key Features |
|---|---|
| `contacts` | UUID PK, account_id + sub_account_id, E.164 mobile_number (unique per account), custom_data JSONB with GIN index, status/source PG enums, RLS |
| `contact_custom_field_definitions` | EAV schema: field_name, field_type PG enum, enum_options JSONB, per-account unique field_name |
| `tags` | Colour-coded labels, denormalized contact_count, source PG enum, per-account unique name |
| `contact_tag` | Junction: composite PK (contact_id, tag_id), cascade deletes |
| `contact_lists` | Static + dynamic lists, type PG enum, rules JSONB for dynamic filter conditions, last_evaluated timestamp |
| `contact_list_member` | Junction: composite PK (contact_id, list_id), cascade deletes |
| `opt_out_lists` | Suppression lists, is_master boolean, partial unique index (one master per account) |
| `opt_out_records` | Keyed by mobile_number (NOT contact_id), persists after contact deletion, source PG enum |
| `contact_timeline_events` | Partitioned by month (RANGE on created_at), composite PK (event_id, created_at), msisdn_hash SHA-256, append-only |

---

## Existing Models — COMPLETE LIST

### Tenant-Scoped (have global scope filtering by account_id/tenant_id):
`User`, `Contact`, `SenderId`, `SubAccount`, `ContactList`, `Tag`, `OptOutList`, `ContactCustomFieldDefinition`, `ContactTimelineEvent`, `OptOutRecord`, `RcsAsset`

### Tenant Root (NO global scope):
`Account`

### System/Admin (no tenant scope — RED SIDE):
`AdminUser`, `AdminNotification`, `AccountFlags`, `AccountSettings`, `AccountCredit`, `AuthAuditLog`, `PasswordHistory`, `UserSession`, `EmailVerificationToken`, `ApiToken`, `PurchaseAuditLog`, `MessageLog`, `MessageDedupLog`, `RateCard`, `RateCardAuditLog`, `Gateway`, `Supplier`, `FxRate`, `MccMnc`, `UkNetworkControl`, `UkNetworkOverride`, `UkPrefix`, `CountryControl`, `CountryControlOverride`, `RcsAgent`, `RcsAgentStatusHistory`, `RoutingRule`, `RoutingCustomerOverride`, `RoutingGatewayWeight`, `RoutingAuditLog`, `SenderIdAssignment`, `SenderIdStatusHistory`, `SenderIdComment`, `Notification`, `SystemSetting`, `ContentRule`, `UrlRule`, `SenderidRule`, `NormalisationCharacter`, `EnforcementExemption`, `QuarantineMessage`, `QuarantineRecipient`, `DomainAgeCache`

---

## UI/UX Conventions

- **CSS Framework:** Bootstrap 5 via Fillow SaaS Admin Template — pastel color scheme, badge-pastel-* classes
- **Layout:** `resources/views/layouts/quicksms.blade.php`
- **Date Format:** DD-MM-YYYY in UI display, ISO 8601 in API responses
- **Tables:** Client-side DataTables with search, sort, pagination
- **Modals:** Bootstrap modals for create/edit forms
- **Mobile Numbers:** Always masked in UI (`+44 77** ***123`), reveal requires audit-logged API call
- **Status Badges:** `badge-pastel-success`, `badge-pastel-danger`, `badge-pastel-warning`, etc.
- **Icons:** Font Awesome 5 (`fas fa-*`)

---

## What NOT to Do — Common Drift Patterns

| Drift | Why It's Wrong |
|---|---|
| Adding `useMockData: true` to JS services | Mock data was deliberately removed. All data comes from DB. |
| Hardcoding contact/tag/list arrays in controllers | Controllers query the database. Empty state is correct for empty DB. |
| Using `$table->enum()` in migrations | Laravel's enum() generates VARCHAR CHECK — use `CREATE TYPE` for native PG enums. |
| Adding `$table->id()` or auto-increment PKs | All PKs are UUID via `$table->uuid('id')->primary()`. |
| Skipping RLS on new tenant tables | Every tenant table MUST have ENABLE + FORCE ROW LEVEL SECURITY + policies. |
| Using `->where('account_id', ...)` without global scope | Add the global scope to the model. Individual queries should not manually filter. |
| Creating React/Vue/Livewire components | Frontend is Blade + jQuery. No SPA framework. |
| Using `config/database.php` sqlite connection | Database is PostgreSQL only. |
| Removing `toPortalArray()` from models | This is the standard data serialization method for views and API responses. |
| Adding middleware to individual API routes | API routes use session-based auth via `customer.auth` middleware group on web routes. |
| Using `DB::table()` queries instead of Eloquent | Use Eloquent models with global scopes. Raw queries bypass tenant isolation. |
| Creating new service providers for tenant context | Tenant context is set by existing middleware. Do not add a second mechanism. |

---

## External Dependencies

| Dependency | Purpose |
|---|---|
| PHP 8.3 / Laravel 10 | Core framework |
| PostgreSQL 16 | Primary database (Replit built-in, Neon-backed) |
| Fillow SaaS Admin Template | UI framework (Bootstrap 5) |
| MetisMenu | Sidebar navigation |
| Stripe PHP SDK | Payment processing |
| HubSpot Products API | Product pricing |
| HubSpot Invoices API | Invoice data |
| Intervention Image v3 | PHP image manipulation |
