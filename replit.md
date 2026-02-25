# QuickSMS — Replit Agent Instructions

## Overview

QuickSMS is a multi-tenant Laravel SMS platform designed for robust and scalable SMS communication. It provides features such as contact management, list segmentation, and comprehensive reporting, with a strong emphasis on tenant isolation and a standardized architecture for consistency and maintainability. The platform integrates a comprehensive billing backend, including ledger, pricing, invoicing, and payment processing, and supports RCS agent management for both customer and administrative interfaces.

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

The QuickSMS platform is built on PHP 8.3 and Laravel 10, using PostgreSQL 16. The UI uses Blade templates, jQuery, and Bootstrap 5 (Fillow SaaS Admin Template) with MetisMenu for navigation.

**Key Architectural Decisions:**

*   **Multi-tenancy:** Implemented via Laravel Global Scopes, PostgreSQL Session Variables (`app.current_tenant_id`), and PostgreSQL Row Level Security (RLS) for fail-closed data isolation.
*   **Primary Keys:** All primary keys are UUIDs (`gen_random_uuid()`), requiring specific `$keyType` and `$incrementing` model configurations.
*   **Migrations:** Utilize raw PostgreSQL `DB::statement()` and `DB::unprepared()` for enums, triggers, RLS, and functions, following a strict creation order.
*   **Model Pattern:** Models adhere to a strict pattern including UUID PKs, global tenant scopes, and `toPortalArray()` for view/JSON serialization. JSONB columns are cast to `array`.
*   **Controller Patterns:**
    *   **Blade Page Controllers:** Query data with Eloquent, pass `toPortalArray()` data to views, protected by `customer.auth` middleware.
    *   **API Controllers:** Return JSON, use `$request->validate()`, set `account_id` from `auth()->user()->tenant_id`, and follow standard HTTP status codes.
*   **Route Structure:** Web routes for customer portal, sender ID API, and admin panel; API routes for contact book and other endpoints with session-based authentication.
*   **JavaScript Service Pattern:** Services use `fetch()` for API calls (`useMockData: false`), handle CSRF, and include UI rendering helpers.
*   **UI/UX Conventions:** Uses Bootstrap 5 (Fillow template) with a pastel color scheme. Dates are DD-MM-YYYY in UI, ISO 8601 in API. DataTables are used for client-side table functionality. Bootstrap modals for forms. Mobile numbers are masked in UI. Status badges use `badge-pastel-*` classes, and icons are Font Awesome 5.
*   **Billing Backend:** Includes 19 database tables and 20 Eloquent models for ledger, test credits, pricing, invoices, payments, and billing operations. It features 9 services (e.g., LedgerService, PricingEngine, InvoiceService, XeroService) and 12 controllers for customer, admin, and webhook endpoints.
*   **Performance Patterns:**
    *   **Batch UPDATE FROM VALUES:** Content resolution and cost calculation use PostgreSQL `UPDATE FROM VALUES` pattern for ~60x faster bulk updates (500 rows per SQL statement instead of individual UPDATEs).
    *   **Streaming Chunk Pipeline:** `RecipientResolverService` uses cursor-based pagination (2K rows at a time) with inline dedup/validate/opt-out/persist per chunk. Only one chunk in memory at a time (~50MB constant regardless of campaign size).
    *   **Per-Segment Cost Estimation:** When content is resolved, `estimateCost()` groups recipients by `(country_iso, segments)` for accurate pricing with variable-length merge fields.
*   **Admin UI for Billing & RCS:**
    *   **Account Billing:** Admin endpoints for viewing/updating account billing mode, balance, credit limit.
    *   **Pricing Management:** A 4-tab interface for managing pricing grids, events, service catalogue, and history, with dedicated API endpoints.
    *   **RCS Agent Approval:** List and detail views for administrative approval of RCS agents, with AJAX data loading, status filtering, and action buttons. Full 11-status workflow: draft → submitted → in_review → sent_to_supplier → supplier_approved → approved (Live), with pending_info/info_provided loop, rejected, suspended, revoked branches. Admin endpoints for each transition at `/admin/api/rcs-agents/{uuid}/{action}`.
    *   **Edit Pricing Modal:** Allows administrators to override bespoke pricing for specific accounts, converting the account to a 'bespoke' product tier upon changes. Supports per-submitted/per-delivered billing type selection for SMS and RCS services, and expandable per-country pricing for International SMS. Uses `billing_type` column (enum: `per_submitted`, `per_delivered`) on `customer_prices` and `product_tier_prices` tables.
*   **Customer UI for Billing & RCS:**
    *   **RCS Agent Library:** Customer-facing page loads agents from the database via API, supporting dynamic tables and API-driven actions (resubmit, delete).
    *   **Account Pricing Tab:** Dynamically fetches and displays pricing based on the account's product tier (Starter, Enterprise, Bespoke).
    *   **Purchase Page:** Uses database-driven pricing from `product_tier_prices` for product selection and payment.
*   **Numbers Module:** All three views (Purchase > Numbers, Management > Numbers Library, Management > Numbers Configure) are wired to real backend APIs. No mock data.
    *   `AccountBalance::lockForAccount()` auto-initialises a zero-balance record if none exists (prevents `ModelNotFoundException` on accounts provisioned without a balance row).
    *   `purchase_audit_logs.user_id` is `varchar(36)` (UUID); `sub_account_id` is nullable (main-account users have no sub-account).
    *   Nested `DB::transaction()` inside outer transactions creates PostgreSQL savepoints — used in `autoCreateSenderId` to make sender-ID creation optional.
    *   Shared shortcode number is `60866`. Keyword pricing slugs: `shortcode_keyword_setup`, `shortcode_keyword_monthly`. Dedicated shortcode slugs: `shortcode_setup`, `shortcode_monthly`.
    *   Ledger entry types for numbers: `number_setup_prepay`, `number_setup_postpay`, `number_setup_refund`. Billable product type: `shortcode_keyword_monthly`.
    *   **VAT on number purchases:** `NumberBillingService::getVatRate()` returns 20% for GB accounts, 0% otherwise. `calculateVmnPricing()` and `calculateKeywordPricing()` return `vat_rate`, `*_vat`, and `*_inc_vat` fields. `debitSetupFee()` accepts an optional `vatAmount` parameter and debits the VAT-inclusive total from the balance, with a `VAT_OUTPUT` ledger line added when VAT > 0. The purchase UI (`purchase/numbers.blade.php`) shows ex-VAT, VAT, and inc-VAT breakdowns in both the selection summary bar and the confirmation modals; balance sufficiency checks use the inc-VAT total.
    *   **Shared shortcode in Numbers Library:** `GET /api/numbers` appends platform shortcodes (60866) that the tenant has active keywords on using a single `whereIn` query — no N+1. `GET /api/numbers/{id}` falls back to `withoutGlobalScopes()` when the tenant has keywords on a platform shortcode. Numbers Library UI shows keyword badges in the capabilities column for `shared_shortcode` rows and suppresses Configure/Suspend/Reactivate actions.

## External Dependencies

*   PHP 8.3 / Laravel 10
*   PostgreSQL 16
*   Fillow SaaS Admin Template (Bootstrap 5)
*   MetisMenu
*   Stripe PHP SDK
*   HubSpot Products API
*   HubSpot Invoices API
*   Intervention Image v3
*   Xero API v2