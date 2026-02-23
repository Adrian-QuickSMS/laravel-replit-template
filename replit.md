# QuickSMS — Replit Agent Instructions

## Overview

QuickSMS is a multi-tenant Laravel SMS platform built on PostgreSQL. Its primary purpose is to provide a robust and scalable solution for SMS communication, offering features like contact management, list segmentation, and comprehensive reporting. The platform emphasizes strict tenant isolation and uses a standardized architecture to ensure consistency and maintainability.

## User Preferences

- **CRITICAL:** Read this entire file before making any changes.
- Do NOT deviate from existing patterns.
- Do NOT "improve" or "simplify" existing patterns.
- Do NOT introduce new patterns. Follow what exists.
- NEVER reintroduce mock data.
- NEVER bypass tenant isolation.
- NEVER use MySQL syntax; the database is PostgreSQL.
- NEVER create SQLite or MySQL migrations.
- NEVER change the frontend framework (Blade + jQuery + Bootstrap 5).
- NEVER modify existing migrations; create new ones only.
- NEVER delete or rename existing model files, controllers, or routes unless explicitly asked.
- NEVER commit `.env` files, credentials, or secrets.

## System Architecture

The QuickSMS platform is built on PHP 8.3 and Laravel 10, utilizing PostgreSQL 16 as its database. The UI is constructed with Blade templates, jQuery, and Bootstrap 5 (specifically the Fillow SaaS Admin Template), with MetisMenu for navigation.

**Key Architectural Decisions:**

*   **Multi-tenancy:** Implemented with three mandatory layers:
    1.  **Laravel Global Scopes:** Every tenant-scoped model includes a global scope to filter data by `account_id` (or `tenant_id` for the `User` model) based on the authenticated user.
    2.  **PostgreSQL Session Variable:** Middleware sets `app.current_tenant_id` in the PostgreSQL session for database-level context.
    3.  **PostgreSQL Row Level Security (RLS):** Every tenant table has RLS enabled and forced, with policies ensuring that only data matching `app.current_tenant_id` is accessible. This is a fail-closed design, returning zero rows if `app.current_tenant_id` is not set.
*   **Primary Keys:** All primary keys are UUIDs (`gen_random_uuid()`), requiring models to have `$keyType = 'string'` and `$incrementing = false`.
*   **Migrations:** All migrations use raw PostgreSQL via `DB::statement()` and `DB::unprepared()` for enums, triggers, RLS, and functions. They follow a strict order: create ENUMs, create tables, add ENUM columns, add indexes, create UUID trigger functions, create validation trigger, and finally, enable RLS.
*   **Model Pattern:** New models must adhere to a strict pattern including UUID PKs, global tenant scopes, and `toPortalArray()` for data serialization to views/JSON. JSONB columns are cast to `'array'`.
*   **Controller Patterns:**
    *   **Blade Page Controllers:** Query the database using Eloquent models, pass data to views via `toPortalArray()`, and are protected by `customer.auth` middleware.
    *   **API Controllers:** Return JSON responses, use `$request->validate()` for input, set `account_id` from `auth()->user()->tenant_id`, and follow standard HTTP status codes (201 for create, 200 for update, 422 for validation errors).
*   **Route Structure:** Web routes handle customer portal, sender ID API, and admin panel, while API routes manage contact book and other API endpoints, relying on session-based authentication.
*   **JavaScript Service Pattern:** Services use `fetch()` to call real API endpoints (`useMockData: false`) and handle CSRF tokens. They include UI rendering helpers for client-side HTML generation.
*   **UI/UX Conventions:**
    *   **CSS Framework:** Bootstrap 5 via Fillow SaaS Admin Template, using a pastel color scheme.
    *   **Date Format:** DD-MM-YYYY in UI, ISO 8601 in API responses.
    *   **Tables:** Client-side DataTables with search, sort, and pagination.
    *   **Modals:** Bootstrap modals for forms.
    *   **Mobile Numbers:** Masked in UI, requiring audit-logged API call to reveal.
    *   **Status Badges:** Use `badge-pastel-*` classes.
    *   **Icons:** Font Awesome 5.

## External Dependencies

*   **PHP 8.3 / Laravel 10:** Core application framework.
*   **PostgreSQL 16:** Primary database.
*   **Fillow SaaS Admin Template:** UI framework (Bootstrap 5).
*   **MetisMenu:** Sidebar navigation component.
*   **Stripe PHP SDK:** For payment processing.
*   **HubSpot Products API:** For product pricing information.
*   **HubSpot Invoices API:** For handling invoice data.
*   **Intervention Image v3:** For PHP image manipulation.
*   **Xero API v2:** For accounting integration (invoices, payments, credit notes).

## Billing Backend (Added 2026-02-20)

The billing backend has been integrated from branch `claude/quicksms-security-performance-dr8sw`. It includes:

*   **19 database tables** across 8 migrations covering: ledger (double-entry accounting), test credits, pricing (product tiers + customer bespoke), invoices, payments, and billing operations (reservations, recurring charges, alerts, audit log).
*   **20 Eloquent models** in `app/Models/Billing/` with UUID PKs, tenant scoping, and `toPortalArray()`.
*   **9 services** in `app/Services/Billing/` covering: LedgerService, BalanceService, PricingEngine, InvoiceService, StripeCheckoutService, XeroService, HubSpotPricingSyncService, BalanceAlertService, ReconciliationService.
*   **12 controllers** in `app/Http/Controllers/Api/` for customer portal, admin, and webhook endpoints.
*   **Billing routes** in `routes/api_billing.php` (16 API routes).
*   **Config** in `config/billing.php` and service config in `config/services.php`.

**Migration fixes applied:**
1. `billing_payment_status` enum used instead of `payment_status` (conflict with existing AccountFlags enum).
2. Self-referencing FK on `customer_prices.previous_version_id` moved to post-table-creation `DB::statement()`.
3. `inet()` column replaced with `string('ip_address', 45)` for Laravel compatibility.

## Admin Account Billing UI (Added 2026-02-23)

The admin account billing page (`/admin/accounts/{id}/billing`) has been wired to real database data:

*   **3 admin API endpoints** in `AdminController`:
    *   `GET /admin/api/accounts/{id}/billing` — returns billing mode, balance, credit limit, available credit, payment terms, VAT status from `accounts` + `account_balances` tables.
    *   `PUT /admin/api/accounts/{id}/billing-mode` — updates `billing_type` (prepay/postpay) on `accounts` table. Resets credit limit to 0 when switching to prepay. Also updates `account_balances` if present.
    *   `PUT /admin/api/accounts/{id}/credit-limit` — updates `credit_limit` on `accounts` table and mirrors to `account_balances.credit_limit`. Only allowed for postpay accounts.
*   **Billing fields** added to Account model's `$fillable`: `billing_type`, `billing_method`, `product_tier`, `credit_limit`, `payment_terms_days`, `currency`, `platform_fee_monthly`, `stripe_customer_id`, `xero_contact_id`.
*   **Frontend adapter** (`AdminAccountBillingService` in billing blade) rewired to call admin API endpoints directly instead of going through `BillingServices` layer (which was designed for customer-scoped API calls).
*   **RLS handling**: Admin endpoints use `SET LOCAL app.current_tenant_id` before querying/updating tenant-scoped tables.
*   **DB value mapping**: `prepay`/`postpay` (DB enum) ↔ `prepaid`/`postpaid` (UI/API).