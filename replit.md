# QuickSMS Web Application

## Overview
QuickSMS is a Laravel-based SMS messaging platform designed to provide efficient communication management for businesses. It offers comprehensive tools for message handling, contact management, reporting, purchasing, and account administration, with a focus on an intuitive user experience. The project aims to empower businesses to effectively engage with their audience through an intuitive and feature-rich platform.

## User Preferences
The user prefers detailed explanations when new concepts or features are introduced. The user wants the agent to prioritize developing the UI and front-end interactions before implementing backend logic. When making changes, the agent should clearly mark areas requiring backend integration with `TODO` comments. The user prefers an iterative development approach, focusing on completing one module's UI before moving to the next.

## System Architecture
QuickSMS is built with PHP 8.1+ and Laravel 10, utilizing the Fillow SaaS Admin Template (Bootstrap 5) and MetisMenu for its UI/UX. The system employs Laravel Blade templating, maintaining a consistent layout, a pastel color scheme, and a global density system for responsive design.

**UI/UX and Design Decisions:**
- **Consistent UI/UX:** Responsive sidebar, standardized data tables with client-side features, uniform forms/modals, consistent date formats (DD-MM-YYYY), and standardized status badges.
- **Interactive Elements:** Mobile number masking, dynamic validation, real-time character counting, enhanced content editors, multi-step wizards for RCS Agent Registration, Message Templates, and Email-to-SMS, and an RCS preview system.
- **Shared Components:** Reusable JavaScript components for image editing, RCS button click tracking, and a shared template-edit-wizard partial (`resources/views/shared/partials/template-edit-wizard.blade.php`).

**Technical Implementations & Feature Specifications:**
- **Core Modules:** Dashboard, Messages, Contact Book, Reporting, Purchase, Management, Account, and Support.
- **Account Management:** Single source of truth for customer information, robust Role-Based Access Control (RBAC) with JavaScript-based UI visibility, and a comprehensive Account Hierarchy View with audit logging.
- **Communication Features:** Email-to-SMS module, SMS SenderID registration (UK-compliant), and Numbers Management.
- **Enterprise Capabilities:** Unified Approval Framework for SenderID and RCS Agent entities, Campaign Approval Inbox, Contact Activity Timeline, and an enterprise-grade Audit Logs Module with 7-year retention.
    - **SenderID Returned/Resubmitted Workflow:** Complete round-trip workflow where admins return SenderID requests with comments (in_review → pending_info), customers receive notifications and respond via detail drawer (pending_info → info_provided), with comment threads, audit trails, and admin notification bell. Tables: `notifications`, `sender_id_comments`, `admin_notifications`. Status labels: pending_info = "Returned — Action Required" (customer) / "Returned to Customer" (admin), info_provided = "Resubmitted". Resubmitted items sort with priority in admin queue.
- **Admin Control Plane:** A separate, highly secured internal interface for QuickSMS employees with distinct authentication, RBAC, and features including:
    - **Global Management:** Admin Accounts, Email-to-SMS, Campaign History, Invoices Modules, and a Global Templates Library.
    - **Admin Account Billing Page:** Customer-scoped billing view with billing mode toggle, credit limit editing, and invoice/credit note actions.
    - **Admin Users Module:** Comprehensive admin user lifecycle management with status lifecycle, security actions (password reset, force logout, MFA reset), and Impersonation/Support Mode.
    - **Admin Audit Logging:** Immutable audit trail with 7-year retention covering various event types and sensitive data sanitization.
    - **Impersonation:** Enhanced security controls including reason requirement, session limits, read-only mode, and critical audit logging.
- **Service Layer Architecture:** Modular service layers including `ContactTimelineService`, `NumbersAdminService`, `BillingServices`, `AdminAuditService`, `AdminLoginPolicyService`, and `ImpersonationService`.
    - **BillingServices:** Unifies `HubSpotBillingService`, `InternalBillingLedgerService`, `InvoicesService` (with Xero integration), and `AccountDetailsService`.
    - **MessageEnforcementService:** Unified message security enforcement with indexed rule storage, deterministic ordering, hot reload capabilities, tenant isolation, and feature flags.
    - **Spam Filter Module (Admin > Security & Compliance > Spam Filter):** Provides centralized message security enforcement with content exemptions, a test rule accordion, and URL controls (Domain Age, URL Rule Library, Exemptions).
    - **NormalisationLibrary:** Fixed base character library (36 immutable characters) for unified character equivalence with a grid-based UI, unified equivalence sets, deterministic deduplication, and scope-agnostic design. All changes are logged via audit events.
    - **UK Prefixes Module (Admin > Supplier Management > MCC/MNC Reference > UK Prefixes tab):** Ofcom number range management with network auto-matching, import wizard, data normalization, and bulk operations.
    - **Routing Rules Module (Admin > Routing > Routing Rules):** Manual routing control with tabbed views for UK Routes, International Routes, and Customer Overrides.

## Database Architecture (PostgreSQL - Deployed Feb 2026)
- **Database:** PostgreSQL (Neon-backed, Replit built-in)
- **UUID Strategy:** Native PostgreSQL `uuid` type with `gen_random_uuid()` defaults (converted from MySQL BINARY(16))
- **Multi-Tenant Isolation:** `tenant_id` foreign keys with CASCADE DELETE, RED/GREEN data separation
- **Schema Summary:** 60 base tables, 3 views, 9 PL/pgSQL functions, 4 triggers
- **Key Tables:** accounts, users, admin_users, api_tokens, account_settings, account_flags, auth_audit_log, contacts, contact_groups, messages, campaigns, invoices, suppliers, gateways, rate_cards, routing_rules, spam_filter_rules, normalisation_library, sub_accounts, sender_ids, sender_id_assignments, sender_id_status_history, notifications, sender_id_comments, admin_notifications
- **Spam Filter Tables (Feb 2026):** senderid_rules, content_rules, url_rules, normalisation_characters, enforcement_exemptions, quarantine_messages, quarantine_recipients, domain_age_cache, message_dedup_log, system_settings
- **Spam Filter API:** 31 routes under `/admin/api/enforcement/` with `EnforcementController` providing full CRUD for all rule types, exemptions, quarantine review, system settings, and domain age cache. Field mapping: UI baseSenderId = DB pattern, UI ruleType 'flag' = DB action 'quarantine', UI status = DB is_active. Security hardened: ReDoS regex validation, CHECK constraints on action/match_type/scope/status fields, QueryException try-catch with generic error messages, LogicException handling on quarantine review, server-side @json data injection for initial rule loading.
- **CHECK Constraints (Feb 2026):** 14 constraints across senderid_rules, content_rules, url_rules, enforcement_exemptions, quarantine_messages, normalisation_characters, domain_age_cache validating action, match_type, scope, status, engine, source, and character_type values.
- **Quarantine Messages Extended Columns:** sub_account_id, user_id, source, campaign_id, message_body_normalised, urls_detected, triggered_rules, recipient_count, scheduled_send_at
- **Stored Procedures:** sp_create_account, sp_authenticate_user, sp_update_user_profile, sp_create_api_token, sp_update_account_settings
- **Views:** account_safe_view, user_profile_view, api_tokens_view
- **System Account:** ID `00000000-0000-0000-0000-000000000001`, Account Number `SYS-000001`
- **Account Activation:** 5-section flow (signup details, company info, support/ops, contract signatory, billing/VAT)
- **Conversion Notes:** All MySQL triggers converted to PostgreSQL trigger functions, SIGNAL → RAISE EXCEPTION, HEX/UNHEX → native UUID ::TEXT cast, ->after() calls removed from activation migration

## External Dependencies
- **PHP 8.1+ / Laravel 10:** Core backend framework.
- **PostgreSQL:** Primary database (Replit built-in, Neon-backed).
- **Fillow SaaS Admin Template:** UI framework (Bootstrap 5).
- **MetisMenu:** JavaScript library for navigation.
- **Stripe PHP SDK / Stripe Checkout:** For payment processing.
- **HubSpot Products API / HubSpot Invoices API:** For product pricing and invoice data.
- **Intervention Image (v3):** PHP image manipulation library.