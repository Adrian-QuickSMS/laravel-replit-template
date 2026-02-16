# QuickSMS — Replit Agent Instructions

## Overview
QuickSMS is a multi-tenant Laravel SMS platform built on PostgreSQL. Its core purpose is to provide a robust and scalable solution for SMS communication, catering to multiple customer accounts with strict data isolation. The platform leverages modern web technologies (PHP 8.3, Laravel 10) and a PostgreSQL database for high performance and reliability. Key capabilities include contact management, SMS sending, billing integration, and comprehensive reporting. The project aims to offer a feature-rich, secure, and user-friendly experience for businesses managing their SMS campaigns.

## User Preferences
CRITICAL: READ THIS ENTIRE FILE BEFORE MAKING ANY CHANGES. QuickSMS is a multi-tenant Laravel SMS platform on PostgreSQL. Every decision below is deliberate. Do NOT deviate from these patterns. Do NOT "improve" or "simplify" them. Do NOT introduce new patterns. Follow what exists.

### HARD RULES — NEVER VIOLATE THESE
1. NEVER reintroduce mock data. All mock/hardcoded data was removed. Controllers query the database. JS services call real API endpoints. Do NOT add `useMockData: true`, do NOT add hardcoded arrays, do NOT add sample/dummy/placeholder data.
2. NEVER bypass tenant isolation. Every query MUST be scoped by `account_id` via model global scopes. Every table that holds tenant data MUST have RLS. There are NO exceptions.
3. NEVER use MySQL syntax. The database is PostgreSQL. Use `ilike` not `LIKE` for case-insensitive. Use `gen_random_uuid()` not `UUID()`. Use `RAISE EXCEPTION` not `SIGNAL`. Use `JSONB` not `JSON`. Use `TIMESTAMPTZ` for timezone-aware timestamps.
4. NEVER create SQLite or MySQL migrations. All migrations use raw PostgreSQL via `DB::statement()` and `DB::unprepared()` for enums, triggers, RLS, and functions.
5. NEVER change the frontend framework. The UI is Blade + jQuery + Bootstrap 5 (Fillow template). Do NOT introduce React, Vue, Inertia, Livewire, Alpine.js, or Tailwind.
6. NEVER modify existing migrations. Create new migrations only. Migration filenames use the pattern `YYYY_MM_DD_SSSSSS_description.php`.
7. NEVER delete or rename existing model files, controllers, or routes unless explicitly asked.
8. NEVER commit .env files, credentials, or secrets.

### What NOT to Do — Common Drift Patterns
- Adding `useMockData: true` to JS services
- Hardcoding contact/tag/list arrays in controllers
- Using `$table->enum()` in migrations
- Adding `$table->id()` or auto-increment PKs
- Skipping RLS on new tenant tables
- Using `->where('account_id', ...)` without global scope
- Creating React/Vue/Livewire components
- Using `config/database.php` sqlite connection
- Removing `toPortalArray()` from models
- Adding middleware to individual API routes
- Using `DB::table()` queries instead of Eloquent
- Creating new service providers for tenant context

## System Architecture

### Core Technologies
- **Backend:** PHP 8.3 / Laravel 10
- **Database:** PostgreSQL 16
- **Frontend:** Blade templates, jQuery, Bootstrap 5 (Fillow SaaS Admin Template)
- **Navigation:** MetisMenu

### Multi-Tenancy
QuickSMS implements a stringent three-layer multi-tenancy model to ensure data isolation:
1.  **Laravel Global Scopes:** All tenant-scoped models include a global scope that filters data by `account_id` (or `tenant_id` for the `User` model) based on the authenticated user.
2.  **PostgreSQL Session Variable:** Middleware (`CustomerAuthenticate`, `SetTenantContext`) sets `app.current_tenant_id` in the PostgreSQL session, linking database operations to the active tenant.
3.  **PostgreSQL Row Level Security (RLS):** Every tenant table has RLS policies enabled and enforced (`FORCE ROW LEVEL SECURITY`), ensuring that queries only return data belonging to the `app.current_tenant_id`. If `app.current_tenant_id` is not set, no rows are returned (fail-closed approach).

### Data Modeling & Database Design
-   **Primary Keys:** All primary keys are UUIDs (`gen_random_uuid()`), stored as strings and non-incrementing.
-   **Tenant Foreign Keys:** `account_id` (UUID string) is present on all tenant-scoped tables, referencing the `accounts` table. The `users` table uses `tenant_id` for historical reasons.
-   **Data Types:** Extensive use of PostgreSQL-native features:
    -   `JSONB` for flexible data storage (e.g., `custom_data` in `contacts`, `rules` in `contact_lists`).
    -   Custom PostgreSQL `ENUM` types for predefined values (e.g., `status`, `source`). These are managed via raw SQL in migrations and accessed with `$this->getRawOriginal('column_name')`.
    -   `TIMESTAMPTZ` for timezone-aware timestamps.
-   **Model Pattern:** New models must adhere to a strict pattern including `SoftDeletes` (if applicable), `protected $table`, `protected $keyType = 'string'`, `public $incrementing = false`, `protected $fillable`, `protected $casts` (for UUIDs to string, JSONB to array, booleans, dates), a `boot()` method with the global tenant scope, and a `toPortalArray()` method for data serialization to views/JSON.
-   **Migration Pattern:** New migrations follow a structured approach using raw `DB::statement()` and `DB::unprepared()` for ENUMs, RLS, indexes, and UUID/validation triggers. They drop elements in reverse order (`policies → triggers → functions → table → types`).

### API and Routing
-   **Web Routes (`routes/web.php`):** Handle customer portal views (`QuickSMSController`) and SenderID API via `customer.auth` middleware. Admin panel routes are protected by `AdminIpAllowlist` and `AdminAuthenticate` middleware.
-   **API Routes (`routes/api.php`):** Dedicated API controllers (`app/Http/Controllers/Api/`) handle various functionalities (Contact Book, Billing, Purchase, Invoices, Reporting, RCS Assets, Webhooks). API endpoints return JSON responses, use `$request->validate()` for input, and derive `account_id` from `auth()->user()->tenant_id`.
-   **JavaScript Services:** Client-side JavaScript (`contacts-service.js`, `contact-timeline-service.js`) uses standard `fetch()` to interact with API endpoints, never mock data. They retrieve CSRF tokens from meta tags.

### UI/UX
-   **Framework:** Bootstrap 5, customized with the Fillow SaaS Admin Template's pastel color scheme.
-   **Layout:** Consistent layout defined in `resources/views/layouts/quicksms.blade.php`.
-   **Data Display:** Client-side DataTables for search, sort, and pagination in tables. Bootstrap modals for forms.
-   **Sensitive Data:** Mobile numbers are masked (`+44 77** ***123`) in the UI, with an audit-logged API call required for full disclosure.
-   **Visual Elements:** Uses Font Awesome 5 for icons and `badge-pastel-*` classes for status indicators.
-   **Date Format:** DD-MM-YYYY for UI display, ISO 8601 for API responses.

## External Dependencies
-   **PHP 8.3 / Laravel 10:** Core application framework.
-   **PostgreSQL 16:** Primary database system (Replit built-in, Neon-backed).
-   **Fillow SaaS Admin Template:** UI framework (Bootstrap 5 based).
-   **MetisMenu:** JavaScript library for responsive sidebar navigation.
-   **Stripe PHP SDK:** For payment processing functionalities.
-   **HubSpot Products API:** To retrieve product pricing information.
-   **HubSpot Invoices API:** For managing and fetching invoice data.
-   **Intervention Image v3:** PHP library for image manipulation.