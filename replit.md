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
    *   `PUT /admin/api/accounts/{id}/billing-mode` — updates `billing_type` (prepay/postpay) on `accounts` table. Preserves credit limit across mode changes. Also updates `account_balances` if present.
    *   `PUT /admin/api/accounts/{id}/credit-limit` — updates `credit_limit` on `accounts` table and mirrors to `account_balances.credit_limit`. Allowed for both prepay and postpay accounts.
*   **Billing fields** added to Account model's `$fillable`: `billing_type`, `billing_method`, `product_tier`, `credit_limit`, `payment_terms_days`, `currency`, `platform_fee_monthly`, `stripe_customer_id`, `xero_contact_id`.
*   **Frontend adapter** (`AdminAccountBillingService` in billing blade) rewired to call admin API endpoints directly instead of going through `BillingServices` layer (which was designed for customer-scoped API calls).
*   **RLS handling**: Admin endpoints use `set_config('app.current_tenant_id', ?, false)` before querying/updating tenant-scoped tables.
*   **DB value mapping**: `prepay`/`postpay` (DB enum) ↔ `prepaid`/`postpaid` (UI/API).
*   **Available credit calculation**: For both prepaid and postpaid accounts, credit limit is included in available credit. Prepaid: `max(0, balance - reserved) + creditLimit`. Postpaid: `creditLimit - totalOutstanding + balance - reserved`.
*   **Auto-created balance records**: The `Account` model's `booted()` method hooks into the `created` event to automatically insert an `account_balances` row with zero balance and the account's currency/credit limit whenever a new account is created.

## Admin Pricing Management Frontend (Added 2026-02-23)

Full admin pricing management page at `/admin/management/pricing` with 4-tab interface:

*   **Tab 1 - Pricing Grid**: Services × tiers (Starter/Enterprise) table. Prices formatted via `display_format` (pence: ×100 + "p", pounds: "£X.XX"). Inline edit modal. Point-in-time preview with date picker. Bespoke-only badge. Scheduled future price calendar icon.
*   **Tab 2 - Pricing Events**: Grouped pricing changes with status badges (draft/scheduled/applied/cancelled). Create/edit/schedule/cancel workflows. Event detail with items table (old→new price) and affected accounts.
*   **Tab 3 - Service Catalogue**: CRUD for services (display name, slug, unit label, format, tier availability, bespoke-only flag, sort order).
*   **Tab 4 - History & Export**: Change log with filters (service, tier, source, date range). CSV export. Upcoming scheduled changes.
*   **API endpoints**: 15 routes under `/admin/api/pricing/` (services CRUD, tier-prices, events CRUD+schedule+cancel, history, export, upcoming, preview).
*   **View file**: `resources/views/admin/management/pricing.blade.php`
*   **Controller**: `app/Http/Controllers/Admin/PricingManagementController.php`

## RCS Agent Customer Portal — DB-Driven (Added 2026-02-23)

The customer-facing RCS Agent Library page (`/management/rcs-agent`) now loads from the database via API:

*   **API endpoint**: `GET /api/rcs-agents` in `RcsAgentController::list()`. Returns all agents for the current account from the `rcs_agents` table.
*   **`toPortalArray()`**: Added to `RcsAgent` model — serializes DB columns to camelCase JS format (id→uuid, status with underscore-to-hyphen, billing_category→billing, etc.).
*   **Dynamic table**: JavaScript calls the API on page load and renders the agent table. Search, sort, pagination, and filters all operate on API-sourced data.
*   **API-driven actions**: Resubmit (`POST /api/rcs-agents/{uuid}/resubmit`), Delete (`DELETE /api/rcs-agents/{uuid}`) now call real API endpoints and reload the table from the server on success.
*   **Wizard save**: After wizard submission, the table reloads from the API instead of locally inserting into the array.
*   **Empty state**: When no agents exist in the database, the empty state with "Create RCS Agent" button is shown.

## RCS Agent Admin Frontend (Added 2026-02-23)

Admin RCS agent approval views wired to real backend API:

*   **List view** (`resources/views/admin/assets/rcs-agents.blade.php`): AJAX data loading from `GET /admin/api/rcs-agents` with pagination, status/billing_category/use_case/account filters. Stat cards for submitted/in_review/approved/rejected/total counts. 9-state status badges. Bulk approve/reject. Quick search.
*   **Detail view** (`resources/views/admin/assets/rcs-agent-detail.blade.php`): Full agent detail with 6 sections (identity, contact, classification, campaign, company, approver). Status history timeline. Comments section. Admin action buttons (review/approve/reject/request-info/suspend/reactivate/revoke) with modals. SLA timer sidebar.
*   **Controller**: `app/Http/Controllers/Admin/RcsAgentApprovalController.php`
*   **API endpoints**: 9 admin routes under `/admin/api/rcs-agents/`.

## Account Pricing Tab — DB-Driven (Added 2026-02-23)

The account details pricing tab (`/account/details` → Pricing tab) now pulls all pricing from the database:

*   **API endpoint**: `GET /api/account/pricing` in `QuickSMSController::accountPricingApi()`. Returns all active services from `service_catalogue` with tier prices from `product_tier_prices`. For bespoke accounts, also returns customer-specific prices from `customer_prices`.
*   **Dynamic rendering**: Frontend uses AJAX to fetch pricing data and renders tier cards dynamically via JavaScript. No hardcoded prices.
*   **Tier display logic**: Shows only Starter + Enterprise columns (2-column layout). If the account's `product_tier` is `bespoke`, a third Bespoke column is added (3-column layout) showing customer-specific prices or "Custom" where not yet set.
*   **Service grouping**: Services are auto-grouped by type — SMS Rates (slugs containing `sms`), RCS Rates (slugs containing `rcs`), Other Services (non-per-message). Prices formatted via `ServiceCatalogue::formatPrice()` using `display_format` (pence/pounds).
*   **"Your Plan" badge**: Highlights the column matching the account's `product_tier`.

## Purchase Page — DB-Driven Pricing (Added 2026-02-23)

The purchase messages page (`/purchase/messages`) now pulls tier prices from the database instead of HubSpot mock data:

*   **`PurchaseApiController::getProducts()`** queries `product_tier_prices` for both starter and enterprise tiers, maps DB `product_type` slugs to frontend keys (e.g., `virtual_number_monthly` → `vmn`, `ai_query` → `ai`).
*   **Response structure** preserved: each product has `price` (starter), `price_enterprise`, `name`, `sku`, `description`, `billing_period`, `currency`, `pricing` (VAT calculation).
*   **Tier selection on payment**: When Stripe `checkout.session.completed` webhook fires for `balance_topup` type, the `WebhookController::updateAccountTier()` method sets `product_tier` on the `accounts` table to the selected tier.
*   **Account context**: `isVatApplicable()` and `getAccountCurrency()` now read from the account record via `session('customer_tenant_id')`.
*   **Dashboard calculator**: SMS/RCS Basic/RCS Single prices in the RCS vs SMS Savings Calculator are now read-only and populated from the customer's tier prices via the controller.
*   **Frontend account_id**: Purchase page uses `{{ $account_id }}` from Blade instead of hardcoded `ACC-001`.