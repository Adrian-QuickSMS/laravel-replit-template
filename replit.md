# QuickSMS Web Application

## Overview
QuickSMS is a Laravel-based SMS messaging platform designed to provide efficient communication management for businesses. It offers comprehensive tools for message handling, contact management, reporting, purchasing, and account administration, with a focus on an intuitive user experience. The project aims to empower businesses to effectively engage with their audience.

## User Preferences
The user prefers detailed explanations when new concepts or features are introduced. The user wants the agent to prioritize developing the UI and front-end interactions before implementing backend logic. When making changes, the agent should clearly mark areas requiring backend integration with `TODO` comments. The user prefers an iterative development approach, focusing on completing one module's UI before moving to the next.

## System Architecture
QuickSMS is built with PHP 8.1+ and Laravel 10, utilizing the Fillow SaaS Admin Template (Bootstrap 5) and MetisMenu for its UI/UX. The system employs Laravel Blade templating, maintaining a consistent layout, a pastel color scheme, and a global density system for responsive design.

**UI/UX and Design Decisions:**
- **Consistent UI/UX:** Responsive sidebar, standardized data tables with client-side features, and uniform forms/modals.
- **Table Standardization (Management Section):** All Management section tables use consistent styling:
  - Headers: padding 0.5rem 0.35rem, font-size 0.75rem, font-weight 600, background #f8f9fa, border-bottom 1px solid #e9ecef
  - Body cells: padding 0.5rem 0.35rem, font-size 0.8rem, border-bottom 1px solid #f1f3f5
  - Date format: DD-MM-YYYY across all tables
  - Status badges: Consistent color scheme (green for active/approved, yellow for pending, gray for draft, red for rejected, blue for conversational)
  - Sort icons: fa-sort icons with click-to-sort functionality on all table headers
  - Action buttons: .action-menu-btn class with gray (#6c757d) color, purple on hover
- **Interactive Elements:** Mobile number masking, dynamic validation, real-time character counting, and enhanced content editors with personalization and emoji support.
- **Wizards and Previews:** Multi-step wizards for RCS Agent Registration, Message Templates, and Email-to-SMS, alongside an RCS preview system with an Android-style phone UI.
- **Shared Components:** Reusable JavaScript components for image editing (with fixed aspect ratio), RCS button click tracking with UTM parameters and conversion tracking, and a shared template-edit-wizard partial (`resources/views/shared/partials/template-edit-wizard.blade.php`) that supports both admin and customer contexts via `$wizardMode` parameter, theme customization, and Rich RCS availability controls.

**Technical Implementations & Feature Specifications:**
- **Core Modules:** Dashboard, Messages, Contact Book, Reporting, Purchase, Management, Account, and Support, with a focus on granular features within each.
- **Account Management:** Single source of truth for customer information with inline validation and audit logging, robust Role-Based Access Control (RBAC) with JavaScript-based UI visibility, and a comprehensive Account Hierarchy View with sub-account enforcement rules and mandatory audit logging.
- **Communication Features:** Email-to-SMS module with tabbed interface and multi-step wizards, SMS SenderID registration (UK-compliant), and Numbers Management for owned numbers.
- **Enterprise Capabilities:** Unified Approval Framework for SenderID and RCS Agent entities, Campaign Approval Inbox, Contact Activity Timeline, and an enterprise-grade Audit Logs Module with 7-year retention and cryptographic integrity.
- **Admin Control Plane:** A separate, highly secured internal interface for QuickSMS employees with distinct authentication, RBAC, and features including:
    - **Admin Accounts, Email-to-SMS, Campaign History, and Invoices Modules:** Global views and management capabilities across customer accounts.
    - **Admin Global Templates Library:** Cross-tenant template management with an impersonation-safe editing wizard, comprehensive template lifecycle actions (Suspend, Reactivate, Archive), and dedicated admin audit logging.
    - **Admin Account Billing Page:** Customer-scoped billing view with billing mode toggle, inline credit limit editing, and actions to create invoices/credit notes via a shared modal.
    - **Admin Users Module:** Comprehensive admin user lifecycle management with:
      - Status lifecycle (Invited → Active → Suspended ↔ Reactivated → Archived)
      - Security actions: Password Reset, Force Logout, MFA Reset/Update, Email Update
      - Impersonation/Support Mode with time-limited sessions and PII masking
      - Server-side role enforcement via `AdminLoginPolicy` and `ImpersonationGuard` middleware
    - **Admin Audit Logging:** Immutable audit trail via `AdminAuditService` with 7-year retention, covering:
      - Event types: ADMIN_USER_INVITED, INVITE_RESENT, ACTIVATED, SUSPENDED, REACTIVATED, ARCHIVED, PASSWORD_RESET, MFA_RESET, MFA_UPDATED, EMAIL_UPDATED, SESSIONS_REVOKED, IMPERSONATION_STARTED, IMPERSONATION_ENDED, LOGIN_BLOCKED_BY_IP
      - Required fields: actor admin, target admin, timestamp UTC, source IP, action type, before/after values, optional reason
      - Sensitive data sanitization (passwords, tokens, secrets auto-redacted)
    - **Impersonation:** Enhanced security controls including reason requirement, session limits, read-only mode, and critical audit logging.

**Service Layer Architecture:**
- **Modular Service Layers:** `ContactTimelineService`, `NumbersAdminService`, `BillingServices`, `AdminAuditService`, `AdminLoginPolicyService`, and `ImpersonationService` provide backend-ready abstraction layers.
- **BillingServices (Unified):** Encompasses `HubSpotBillingService` (source of truth for billing), `InternalBillingLedgerService` (internal balance tracking), `InvoicesService` (invoice/credit note management with Xero integration), and `AccountDetailsService`. Includes a `BillingFacade` for unified data loading and defensive error handling.
- **MessageEnforcementService:** Unified message security enforcement with:
  - **Indexed Rule Storage:** O(1) lookups via `ruleIndex.byId`, engine-based grouping via `ruleIndex.byEngine`, tenant isolation via `ruleIndex.byTenant`
  - **Deterministic Ordering:** Rules sorted by priority with secondary sort by ID for stability
  - **Hot Reload:** `hotReloadRules()` enables rule updates without service restart; `cacheMetadata` tracks version and last load time
  - **Tenant Isolation:** `getRulesForTenant(tenantId, engine)` guarantees no cross-tenant reads; global rules apply to all tenants
  - **Feature Flags:** Per-engine toggles (`senderid_controls_enabled`, `content_controls_enabled`, `url_controls_enabled`, `quarantine_enabled`, `normalisation_enabled`, `anti_spam_enabled`, `domain_age_check_enabled`)
  - **Admin Access Enforcement:** `AdminAccessControl` validates admin context at UI layer; `setFeatureFlag()` requires admin context
- **NormalisationLibrary:** Single source of truth for character equivalence ("bastardisation") rules:
  - **Rule Categories:** Character Substitution, Homoglyph Detection (Greek/Cyrillic), Unicode Normalisation (NFKC), Case Folding
  - **Scoped Application:** Rules can target specific engines (SenderID, Content, URL) or apply globally to all engines
  - **Character Mappings:** Define base characters and their visual equivalents (e.g., '0' → ['O', 'o', 'Ο', 'ο'])
  - **Consuming Engines:** SenderID Matching, Content Matching, URL Matching (guarded via feature flag)
  - **Test & Export:** Interactive rule testing with transformation breakdown; JSON export of active substitution map
  - **Audit Integration:** All rule changes logged via `logAuditEvent()` with NORMALISATION_RULE_CREATED, NORMALISATION_RULE_STATUS_CHANGED events
- **Design Principles:** Typed JSDoc objects, mock data modes for development, clean separation of UI and API, and robust error handling.

## External Dependencies
- **PHP 8.1+ / Laravel 10:** Core backend framework.
- **Fillow SaaS Admin Template:** UI framework (Bootstrap 5).
- **MetisMenu:** JavaScript library for navigation.
- **Stripe PHP SDK / Stripe Checkout:** For payment processing.
- **HubSpot Products API / HubSpot Invoices API:** For product pricing and invoice data.
- **Intervention Image (v3):** PHP image manipulation library.