# QuickSMS — Replit Agent Guide

## Overview

QuickSMS is a UK enterprise multi-tenant SaaS platform for business messaging (SMS, RCS, WhatsApp Business). It is built on **Laravel 10** (PHP 8.1+) with a **PostgreSQL** backend, and targets compliance with ISO27001, Cyber Essentials Plus, and NHS DSP Toolkit.

The platform has two primary surfaces:
1. **Customer Portal** — multi-tenant self-service for business customers to send messages, manage contacts, view reports, and configure API connections.
2. **Admin Console** — internal control plane for managing all customer accounts, routing rules, supplier rate cards, sender ID approvals, and platform-wide reporting.

Core capabilities include:
- Campaign & single-message sending (SMS, Basic RCS, Rich RCS with SMS fallback)
- Contact Book with custom fields, tags, lists, and opt-out management
- Inbox / two-way messaging
- Reporting & finance data with drill-down
- API connections (bulk, campaign, integration types)
- Supplier rate cards, gateway routing rules, and VMN/shortcode number management
- Template management (shared `SendMessageBuilder` component across campaign/inbox/template modes)

---

## User Preferences

Preferred communication style: Simple, everyday language.

---

## System Architecture

### Backend — Laravel 10 (PHP 8.1+)

- **Framework:** Laravel 10 with Eloquent ORM, Laravel Sanctum for API token auth, and artisan migrations.
- **Database:** PostgreSQL 15+ (migrated from MySQL/MariaDB). Native UUID (`gen_random_uuid()`), ENUM types, JSONB, and INET types are used throughout. Do **not** use MySQL-style `BINARY(16)` UUID patterns.
- **Multi-tenancy:** Row Level Security (RLS) enforced at the database level on all tenant-scoped tables. The `SetTenantContext` middleware sets `app.current_tenant_id` as a PostgreSQL session variable before every query. This middleware **must** remain registered in `Kernel.php` (in BOTH `web` and `api` middleware groups) and must not be removed or reordered. It supports two auth sources: `$request->user()->tenant_id` (Sanctum/API) and `session('customer_tenant_id')` (customer portal session auth).
- **Authentication flow:**
  - Account creation → always via `sp_create_account()` stored procedure, never `Account::create()` directly.
  - Login → always via `sp_authenticate_user()` stored procedure.
  - Password hashed **once** in the controller with `Hash::make()`. The `User` model must **not** re-hash.
  - UUID mutators (`getIdAttribute`, `setIdAttribute`, etc.) must **not** exist in models — PostgreSQL returns native 36-char UUID strings.
- **Database roles:** Four roles are defined — `portal_ro`, `portal_rw`, `svc_red`, `ops_admin`. The application connects as `portal_rw` for customer-facing operations; `svc_red` bypasses RLS for admin operations.
- **Stored procedures:** Five core PL/pgSQL procedures (`sp_create_account`, `sp_authenticate_user`, `sp_create_api_token`, `sp_update_user_profile`, plus password change). These use `SECURITY DEFINER` — be aware this runs as the function owner and bypasses RLS unless carefully controlled.
- **Audit trail:** All sensitive actions (login, signup, token creation, admin overrides) are logged to `auth_audit_log`. This table is insert-only via grants.

### RED / GREEN Trust Boundary

```
GREEN (Customer-Facing)         RED (Internal/Admin)
portal_rw DB role               svc_red DB role
RLS enforced                    RLS bypassed
toPortalArray() responses       Full model data allowed
auth:sanctum middleware         Admin-only middleware
```

- Portal API responses must **always** use `toPortalArray()` — never return raw model or sensitive fields (password hash, MFA secret, token hash) to customers.
- `tenant_id` is **never** derived from user input — only from the authenticated user record server-side.

### Frontend

- **Templating:** Laravel Blade views. The design system is called **Fillow** — all UI must reuse existing Fillow components (buttons, modals, pills, tables, filters, dropdowns). Do not introduce new layouts, colours, or component variants.
- **Asset pipeline:** Vite 4 with `laravel-vite-plugin`. Entry points: `resources/css/app.css`, `resources/js/app.js`, `resources/js/rcs/preview-controller.ts` (TypeScript supported).
- **Key UI rules:**
  - The `SendMessageBuilder` is a **shared component** used across campaign, inbox, and template modes via a `mode` flag. Never duplicate or fork it — only hide/show sections via the flag.
  - Filters across the platform follow a consistent pattern: search bar on the LEFT, filter button on the RIGHT. "Clear All" resets state but does **not** auto-apply — user must press "Apply Filters".
  - All table/modal/pill/dropdown styles must match existing Message Logs and API Connections pages as the reference standard.
  - Admin pages use a standardised breadcrumb + page header pattern (reference: Global Templates Library page).

### Key Modules & Data Models

| Module | Key Tables |
|---|---|
| Auth & Accounts | `accounts`, `users`, `auth_audit_log` |
| Contact Book | `contacts`, `tags`, `contact_lists`, `opt_out_lists`, `opt_out_records`, `contact_timeline_events` |
| Messaging | Campaign, inbox, send message (shared builder) |
| API Connections | `api_connections`, `api_connection_audit_events` |
| Numbers | `vmn_pool`, `purchased_numbers`, `shortcode_keywords`, `number_assignments`, `number_auto_reply_rules` |
| Routing Rules | `routing_rules`, `routing_gateway_weights`, `routing_customer_overrides`, `routing_audit_log` |
| Supplier Rate Cards | `suppliers`, `gateways`, `mcc_mnc_master`, `rate_cards`, `fx_rates`, `rate_card_audit_log` |
| Reporting | Finance data with drill-down (Month → user-chosen dimension) |
| RCS Media Assets | `rcs_assets` — tenant-scoped via `account_id`, draft lifecycle, daily cleanup |

### RCS Rich Messaging Backend

- **`rcs_assets` table** — stores uploaded/URL-imported media with edit params (crop/zoom/orientation). Tenant-scoped via `account_id`. Draft assets are cleaned up daily by `php artisan rcs:cleanup-drafts`. Composite index on `(account_id, is_draft, created_at)`.
- **`RcsAssetService`** — full image processing pipeline: URL import with SSRF protection, file upload, crop/zoom/resize edits via Intervention Image, original image stored for lossless re-cropping. Returns `uuid` + `public_url` to the frontend.
- **`RcsContentValidator`** — validates card count (1 for single, 2–10 for carousel), button count (max 4 per card), button label length (max 25 chars), text body length (max 2000 chars), shared orientations, and asset finalization before send. Used by `CampaignService` on create/update/validateForSend.
- **`Campaign::TYPE_RCS_CAROUSEL`** constant added. `CampaignApiController` now accepts `rcs_carousel` as a valid campaign type alongside `sms`, `rcs_basic`, `rcs_single`.
- **`POST /messages/confirm-send`** — web route that creates a real Campaign record from session data and triggers send or schedule via `CampaignService`. Clears the `campaign_config` session key on success.
- **`rcs:cleanup-drafts` artisan command** — scheduled daily at 03:00 UTC. Deletes `rcs_assets` rows where `is_draft = true` and `created_at` is older than the configured threshold (default 24 hours), and removes the associated stored files.

### Security Rules (Non-Negotiable)

1. `tenant_id` always from authenticated session, never from request input.
2. Account creation always via `sp_create_account()`.
3. Login always via `sp_authenticate_user()`.
4. Password hashed once in controller only.
5. `SetTenantContext` middleware must remain in `Kernel.php`.
6. All portal API responses use `toPortalArray()`.
7. CSRF tokens included in all form submissions.
8. All admin actions logged to `auth_audit_log`.
9. `FORCE ROW LEVEL SECURITY` active on all 7+ tenant tables.
10. The `accounts_isolation` RLS policy must **not** include a NULL-context bypass (`OR current_setting(...) IS NULL`).

---

## External Dependencies

### PHP Packages (composer.json)
| Package | Purpose |
|---|---|
| `laravel/framework` ^10.10 | Core framework |
| `laravel/sanctum` ^3.2 | API token authentication |
| `laravel/tinker` ^2.8 | REPL / debugging |
| `guzzlehttp/guzzle` ^7.2 | HTTP client for outbound API calls |
| `intervention/image` ^3.0 | Image processing (RCS rich content) |
| `phpoffice/phpspreadsheet` ^5.4 | CSV/Excel file upload processing |
| `stripe/stripe-php` ^19.1 | Payment/billing integration |

### Node / Frontend (package.json)
| Package | Purpose |
|---|---|
| `vite` ^4.0.0 | Asset bundler |
| `laravel-vite-plugin` ^0.8.0 | Laravel + Vite integration |
| `axios` ^1.1.2 | HTTP client for frontend API calls |
| `typescript` ^5.9.3 | TypeScript support (RCS preview controller) |

### Database
- **PostgreSQL 15+** (Replit managed). Must use `pgsql` driver in `.env` (`DB_CONNECTION=pgsql`, `DB_PORT=5432`).
- Four database roles must be provisioned: `portal_ro`, `portal_rw`, `svc_red`, `ops_admin` — created via `package/database/setup/01_create_roles_and_grants.sql`.

### External Services
- **Stripe** — billing and payment processing (`stripe/stripe-php`).
- **SMS/RCS gateways** — routed via the routing rules module; specific gateway providers configured per supplier rate cards.
- **Webhook endpoints** — outbound DLR and inbound SMS webhooks configured per API connection and VMN number.
- **FX rate sources** — external FX rate feeds stored in `fx_rates` table for multi-currency rate card calculations.