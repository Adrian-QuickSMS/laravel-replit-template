# QuickSMS — Replit Agent Guide

## Overview
QuickSMS is a multi-tenant SaaS platform for business messaging (SMS, RCS, WhatsApp Business) in the UK. Built with Laravel 10 and PostgreSQL, it aims for ISO27001, Cyber Essentials Plus, and NHS DSP Toolkit compliance. It offers a Customer Portal for self-service messaging, contact management, reporting, and API connections, and an Admin Console for internal management of accounts, routing, rates, and approvals. Key capabilities include campaign and single-message sending across various message types, comprehensive contact management, two-way messaging, detailed reporting, and robust API integration.

## User Preferences
Preferred communication style: Simple, everyday language.

## System Architecture

### Backend — Laravel 10 (PHP 8.1+)
- **Framework:** Laravel 10 with Eloquent ORM, Laravel Sanctum for API authentication, and artisan migrations.
- **Database:** PostgreSQL 15+ is used, leveraging native UUIDs, ENUMs, JSONB, and INET types. MySQL-style `BINARY(16)` UUID patterns are explicitly avoided.
- **Multi-tenancy:** Row Level Security (RLS) is strictly enforced at the database level for all tenant-scoped tables, managed by a `SetTenantContext` middleware in `Kernel.php`.
- **Authentication:** Account creation and login exclusively use stored procedures (`sp_create_account()`, `sp_authenticate_user()`). Passwords are hashed once in the controller.
- **Database Roles:** Four distinct roles (`portal_ro`, `portal_rw`, `svc_red`, `ops_admin`) manage database access and RLS bypass for different application contexts.
- **Audit Trail:** Comprehensive audit logging across 16 modules via 5 new immutable tables (`campaign_audit_log`, `user_audit_log`, `account_audit_log`, `number_audit_log`, `admin_audit_log`) plus existing tables (`auth_audit_log`, `api_connection_audit_events`, `routing_audit_log`, `rate_card_audit_log`, `message_template_audit_log`, `email_to_sms_audit_log`, `financial_audit_log`, `purchase_audit_logs`). 55+ event types. Dual-layer immutability (Eloquent `ImmutableAuditLog` trait + PostgreSQL `REVOKE`/triggers). Unified API via `AuditLogApiController` with UNION ALL across all tables. `contact_timeline_events` ENUM columns converted to VARCHAR for extensibility. Full spec in `AUDIT_LOG_SPEC.md`, anti-drift rules in `REPLIT_PROMPT_AUDIT_LOG_COMPLETE.md`.
- **RED / GREEN Trust Boundary:** Customer-facing (GREEN) operations use `portal_rw` role with RLS, `toPortalArray()` for responses, and Sanctum authentication. Internal/Admin (RED) operations use `svc_red` role, bypassing RLS, and allow full model data. `tenant_id` is always derived server-side.

### Frontend
- **Templating:** Laravel Blade views, utilizing the **Fillow** design system for consistent UI components. No new layouts, colors, or component variants are to be introduced.
- **Asset Pipeline:** Vite 4 with `laravel-vite-plugin` for CSS and JavaScript/TypeScript bundling.
- **Key UI Rules:** The `SendMessageBuilder` is a shared component adaptable via a `mode` flag. Filters follow a consistent left-search, right-filter pattern. All UI elements must match existing styling on Message Logs and API Connections pages. Admin pages use a standardized breadcrumb and page header.
- **Emoji Picker:** `QSEmojiPicker` — shared floating popover component (`public/js/emoji-picker.js`, `public/css/emoji-picker.css`, `resources/views/quicksms/partials/emoji-picker.blade.php`). Replaces old Bootstrap modal emoji pickers. Uses vanilla JS, no dependencies. Integrated on Send Message, Inbox v2, RCS Wizard, Template Editor, and shared message-builder/composer partials. Global instances: `window.smsEmojiPicker` (Send Message, Templates, Inbox via alias), `window.inboxEmojiPicker` (Inbox). Architecture documented in `EMOJI_PICKER_ARCHITECTURE.md`.

### Key Modules & Data Models
- **Auth & Accounts:** `accounts`, `users`, `auth_audit_log`.
- **Sub-Accounts & User Management:** `sub_accounts` (with limits/enforcement: spending caps, message caps, daily limits, enforcement types), `users` (7 roles: owner, admin, messaging_manager, finance, developer, user, readonly; 28 permission toggles; sender capability levels: advanced/restricted/none), `user_invitations` (SHA-256 hashed tokens, 72h expiry, RLS-protected). Managed by `SubAccountController`, `UserManagementController`, and `CheckPermission` middleware. `CustomerAuthenticate` middleware binds the user to Laravel's auth guard via `Auth::setUser()`.
- **Main Account Overview:** `/account/overview` route displays account status, aggregated limits (summed across all sub-accounts), live usage telemetry, and assigned assets. View: `account-overview.blade.php`. Clickable from the hierarchy tree main account node.
- **Contact Book:** `contacts`, `tags`, `contact_lists`, `opt_out_lists`, `opt_out_records`, `contact_timeline_events`.
- **Messaging:** Campaigns, inbox at `/messages/inbox`, and a shared send message builder. Inbox uses `InboxController` + `InboxService` + `InboxDeliveryService` with real PostgreSQL-backed data (3 tables: `inbox_conversations`, `inbox_messages`, `inbox_read_receipts`, all RLS-protected). Inbound message routing via `InboundRoutingService` + `InboundWebhookController` at `POST /webhook/inbound/{gateway}`. Message content encrypted at rest via `Crypt::encryptString`. Inbox composer uses VMNs (purchased numbers) and approved RCS agents as "from" options — only two-way capable identifiers. `InboxDeliveryService` resolves `purchased_number_id` to actual MSISDN before gateway send. Gateway delivery and billing integration are placeholders pending those modules. Old `InboxDataService` mock retained for reference.
- **API Connections:** `api_connections`, `api_connection_audit_events`.
- **Numbers:** `vmn_pool`, `purchased_numbers`, `shortcode_keywords`, `number_assignments`, `number_auto_reply_rules`.
- **Routing Rules:** `routing_rules`, `routing_gateway_weights`, `routing_customer_overrides`, `routing_audit_log`.
- **Supplier Rate Cards:** `suppliers`, `gateways`, `mcc_mnc_master`, `rate_cards`, `fx_rates`, `rate_card_audit_log`.
- **Reporting:** Financial data with drill-down capabilities.
- **RCS Media Assets:** `rcs_assets` (tenant-scoped, draft lifecycle, daily cleanup), managed by `RcsAssetService` for image processing and `RcsContentValidator` for content integrity.
- **Message Templates:** `message_templates` with version tracking (`message_template_versions`), a status lifecycle (draft, active, suspended, archived), and `message_template_audit_log` for changes.
- **Email-to-SMS:** Seven normalized tables for configuration (`email_to_sms_setups`, `email_to_sms_addresses`, `email_to_sms_allowed_senders`, `email_to_sms_recipients`, `email_to_sms_opt_out_config`, `email_to_sms_reporting_groups`, `email_to_sms_audit_log`), with full API and UI integration.
- **Billing Snapshots:** `campaign_estimate_snapshots` for immutable pricing records.

### Account Lifecycle & Test Mode
A 7-status model for `accounts.status` (`pending_verification`, `test_standard`, `test_dynamic`, `active_standard`, `active_dynamic`, `suspended`, `closed`) is implemented. `TestModeEnforcementService` applies restrictions for test accounts (e.g., approved numbers, disclaimer). `FraudScreeningService` handles activation, and `Account::transitionTo()` validates status changes. Account settings include `approved_test_numbers` for `test_standard` accounts.

### Security Rules
Strict security protocols are enforced: `tenant_id` from authenticated session only, account creation/login via stored procedures, single password hashing, `SetTenantContext` middleware integrity, `toPortalArray()` for API responses, CSRF protection, audit logging for admin actions, active RLS on tenant tables, and no RLS bypass for `accounts_isolation` policy.

## External Dependencies

### PHP Packages
- `laravel/framework`: Core framework.
- `laravel/sanctum`: API token authentication.
- `guzzlehttp/guzzle`: HTTP client.
- `intervention/image`: Image processing for RCS.
- `phpoffice/phpspreadsheet`: CSV/Excel processing.
- `stripe/stripe-php`: Payment/billing integration.

### Node / Frontend Packages
- `vite`: Asset bundler.
- `laravel-vite-plugin`: Laravel + Vite integration.
- `axios`: Frontend HTTP client.
- `typescript`: TypeScript support.

### Database
- **PostgreSQL 15+**: Primary database, configured via `pgsql` driver.
- **Database Roles**: `portal_ro`, `portal_rw`, `svc_red`, `ops_admin` are provisioned.

### External Services
- **Stripe**: Billing and payment processing.
- **SMS/RCS gateways**: Integrated via routing rules module.
- **Webhook endpoints**: For DLRs and inbound SMS.
- **FX rate sources**: For multi-currency rate card calculations.