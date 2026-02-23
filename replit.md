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
*   **Admin UI for Billing & RCS:**
    *   **Account Billing:** Admin endpoints for viewing/updating account billing mode, balance, credit limit.
    *   **Pricing Management:** A 4-tab interface for managing pricing grids, events, service catalogue, and history, with dedicated API endpoints.
    *   **RCS Agent Approval:** List and detail views for administrative approval of RCS agents, with AJAX data loading, status filtering, and action buttons.
    *   **Edit Pricing Modal:** Allows administrators to override bespoke pricing for specific accounts, converting the account to a 'bespoke' product tier upon changes.
*   **Customer UI for Billing & RCS:**
    *   **RCS Agent Library:** Customer-facing page loads agents from the database via API, supporting dynamic tables and API-driven actions (resubmit, delete).
    *   **Account Pricing Tab:** Dynamically fetches and displays pricing based on the account's product tier (Starter, Enterprise, Bespoke).
    *   **Purchase Page:** Uses database-driven pricing from `product_tier_prices` for product selection and payment.

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