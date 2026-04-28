# QuickSMS — Replit Agent Guide

## Overview
QuickSMS is a multi-tenant SaaS platform delivering business messaging (SMS, RCS, WhatsApp Business) services in the UK. It is built with Laravel 10 and PostgreSQL, aiming for ISO27001, Cyber Essentials Plus, and NHS DSP Toolkit compliance. The platform provides a Customer Portal for self-service messaging, contact management, reporting, and API connectivity, alongside an Admin Console for internal account, routing, rate, and approval management. Its core capabilities include sending various message types (campaigns, single messages), comprehensive contact and number management, two-way messaging, detailed reporting, and robust API integration.

## User Preferences
Preferred communication style: Simple, everyday language.

## System Architecture

### Backend
- **Framework:** Laravel 10 (PHP 8.1+) with Eloquent ORM, Laravel Sanctum for API authentication.
- **Database:** PostgreSQL 15+ is used, enforcing Row Level Security (RLS) for multi-tenancy.
- **Authentication:** Account creation and login use stored procedures. Passwords are hashed once.
- **Database Roles:** Four distinct roles (`portal_ro`, `portal_rw`, `svc_red`, `ops_admin`) manage database access.
- **Security Hardening:** Includes fail-closed webhook endpoints, session regeneration on login, mass assignment protection, IDOR prevention, and restricted admin autologin. Portal API routes use session-based authentication.
- **RED / GREEN Trust Boundary:** Distinguishes customer-facing (GREEN, RLS-enforced) from internal/admin (RED, RLS-bypassing) operations.

### Frontend
- **Templating:** Laravel Blade views, adhering to the Fillow design system. No new layouts or component variants.
- **Asset Pipeline:** Vite 4 with `laravel-vite-plugin` for asset bundling.
- **UI/UX:** Consistent UI styling across the application, with a standardized `SendMessageBuilder` component and filter patterns. Admin pages use standardized breadcrumbs and page headers. An integrated `QSEmojiPicker` replaces older emoji selection methods.

### Key Modules & Data Models
- **Auth & Accounts:** Manages user accounts, authentication, and audit logging.
- **Sub-Accounts & User Management:** Provides granular control over user roles, permissions, and sub-account limits.
- **Messaging:** Includes campaign management, an Inbox for two-way conversations (using PostgreSQL-backed data models), inbound message routing, and at-rest encryption of message content. The Inbox composer supports VMNs and approved RCS agents.
- **API Connections:** Manages customer API integrations, with distinct suspension mechanisms for admin and customer actions.
- **Numbers:** Handles virtual mobile numbers (VMNs), shortcodes, and associated rules.
- **Routing Rules:** Configures message routing logic to various gateways.
- **Supplier Rate Cards:** Manages suppliers, gateways, MCC/MNC data, and dynamic rate cards.
- **Reporting:** Provides financial and operational reporting with drill-down capabilities.
- **RCS Media Assets:** Manages tenant-scoped RCS media assets with draft lifecycle and validation.
- **Message Templates:** Supports versioned message templates with status lifecycle and audit logging.
- **Email-to-SMS:** Comprehensive configuration and integration for email-to-SMS functionality.
- **Flow Builder:** A visual drag-and-drop tool for automated messaging journeys, including triggers, actions, logic, and endpoints, with a secure credential vault.
- **Admin Console:** Dedicated landing page and separate layouts for Messaging and HR management.
- **HR Leave Management (Admin Console):** Internal module for staff leave, entitlements, requests, and auditing, with specific HR roles and robust calculation services.
- **Security Settings:** Customer portal configuration for message data retention, data masking, anti-flood protection, out-of-hours sending restrictions, and IP allowlisting. Admin console also provides per-account security settings management via `/admin/accounts/{accountId}/settings` with dedicated API endpoints (`/admin/api/accounts/{accountId}/security/*`) for viewing and modifying each customer's security configuration without logging in as the customer.
- **Auto Top-Up:** Prepay accounts can automatically replenish balance via Stripe when it falls below a threshold. Features: Stripe Checkout setup-mode for payment method capture, off-session PaymentIntent creation, webhook-only balance crediting, per-account VAT calculation, retry with configurable delay, admin lock/unlock, daily limits and cooldown timers, and a scheduled command (`billing:expire-stale-auto-topups`) to expire stale events. Customer portal at `/payments/auto-topup`, admin console at `/admin/billing/auto-topup`, API at `/api/v1/topup/auto-topup/*`. Core service: `AutoTopUpService` (884 lines). Key tables: `auto_topup_configs`, `auto_topup_events`. Full handoff guide: `docs/AUTO-TOPUP-MODULE.md`.
- **Billing Snapshots:** Immutable records for campaign pricing estimates.
- **Help Centre (Customer Dashboard):** Replaces the legacy "Support & Notifications" section on the customer dashboard with three Fillow-styled cards (Open Support Tickets via HubSpot Service Hub, Knowledge Base search via HubSpot KB, Platform Updates summary) plus a full-width "Platform Updates & Alerts" feed panel with category tabs (All / Updates / Maintenance / Features) and "Mark all as read". Backend: `app/Services/HubSpotHelpCentreService.php` (read-only, reuses existing `HUBSPOT_ACCESS_TOKEN`, 60s ticket cache, 300s KB cache, deterministic mock fallback when token absent), `app/Http/Controllers/HelpCentreController.php` (4 endpoints), `app/Models/PlatformUpdate.php`, `database/migrations/2026_04_28_000001_create_platform_updates_table.php` (global `platform_updates` + per-user `platform_update_reads` pivot, portal_ro/portal_rw/ops_admin grants), `database/seeders/PlatformUpdateSeeder.php` (idempotent). Routes (`routes/web.php`): `GET/POST /portal/api/help-centre/{tickets|kb/search|platform-updates|platform-updates/mark-read}` under `customer.auth + customer.ip_allowlist + throttle:60,1`. Frontend lives inline in `resources/views/quicksms/dashboard.blade.php` (`#helpCentre` and `#platformUpdatesFeed` sections + scoped CSS + IIFE script using session-auth `fetch` with CSRF). Distinct from the bug-report `HubSpotTicketService`.
- **Bug Report Widget:** A floating bug report button on both Customer Portal and Admin Console, gated by `BUG_REPORT_WIDGET_ENABLED` env var (default: false). Submissions create HubSpot tickets and optionally trigger a Claude Code auto-fix pipeline via GitHub Issues/Actions. Core files: `BugReportController`, `BugFixWebhookController`, `HubSpotTicketService`, `GitHubIssueService`, `CleanBugReportTempFiles` job. Routes: `POST /api/bug-report` (portal), `POST /admin/api/bug-report` (admin), `POST /api/webhooks/bug-fix-status` (webhook). Frontend: `bug-report-widget.blade.php`, `bug-report-widget.js`, `bug-report-annotation.js`, `bug-report-widget.css`. GitHub Actions: `bug-auto-fix.yml`, `bug-fix-merged.yml`.
- **Audit Logging:** Five domain-specific, immutable audit log tables with a unified API for retrieval.
- **Supplier Monitoring:** Admin-only alert category with 14 pre-configured rules across two tiers (Critical Supplier Health and Carrier Behaviour), covering delivery rates, DLR latency, queue depth, submit success, API availability, network degradation, sender ID rejection, and missing DLR rates. Backend evaluation services are documented in `docs/supplier-monitoring-backend-todo.md` for future integration with reporting databases.
- **Customer Monitoring Alerts:** 21 additional customer-facing alert defaults across Messaging (delivery deviations, pending rates, missing DLR, submission/sender ID rejection, RCS fallback), System (platform processing time, queue depth/growth, oldest queued message, DLR queue, customer API errors/latency, webhook failure rate, DLR callback latency), and Campaign (traffic spike/drop detection). Backend evaluation services documented in `docs/customer-monitoring-backend-todo.md`.

### Account Lifecycle & Test Mode
A 7-status account model (`pending_verification`, `test_standard`, `test_dynamic`, `active_standard`, `active_dynamic`, `suspended`, `closed`) is implemented. `TestModeEnforcementService` applies restrictions for test accounts. `FraudScreeningService` handles activation. Admin controls allow status overrides, test credit top-ups, and per-account spam filter mode adjustments, all audit-logged.

## External Dependencies

### PHP Packages
- `laravel/framework`: Core framework.
- `laravel/sanctum`: API authentication.
- `guzzlehttp/guzzle`: HTTP client.
- `intervention/image`: Image processing.
- `phpoffice/phpspreadsheet`: Spreadsheet processing.
- `stripe/stripe-php`: Payment processing.

### Node / Frontend Packages
- `vite`: Asset bundler.
- `laravel-vite-plugin`: Laravel + Vite integration.
- `axios`: Frontend HTTP client.
- `typescript`: For JavaScript development.

### Database
- **PostgreSQL 15+**: Primary relational database.

### External Services
- **Stripe**: Billing and payment processing.
- **SMS/RCS gateways**: For message delivery.
- **Webhook endpoints**: For delivery receipts and inbound messages.
- **FX rate sources**: For currency conversion in rate cards.