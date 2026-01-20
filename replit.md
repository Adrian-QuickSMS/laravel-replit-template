# QuickSMS Web Application

## Overview
QuickSMS is a Laravel-based SMS messaging platform designed for efficient communication management with a modern UI. It provides tools for managing messages, contacts, reporting, purchasing, and account administration. The platform aims to offer an intuitive solution for businesses to communicate effectively with their audience.

## User Preferences
The user prefers detailed explanations when new concepts or features are introduced. The user wants the agent to prioritize developing the UI and front-end interactions before implementing backend logic. When making changes, the agent should clearly mark areas requiring backend integration with `TODO` comments. The user prefers an iterative development approach, focusing on completing one module's UI before moving to the next.

## System Architecture
QuickSMS is built using PHP 8.1+ and Laravel 10, utilizing the Fillow SaaS Admin Template (Bootstrap 5) and MetisMenu for its UI/UX. The system employs Laravel Blade templating with a consistent layout and a pastel color scheme.

**UI/UX and Design Decisions:**
- **Consistent UI:** Responsive sidebar, standardized data tables with client-side features, and consistent forms/modals.
- **Interactive Elements:** Mobile number masking, dynamic validation, real-time character counting.
- **Content Editor:** Enhanced with personalization, emoji support, and AI assistance.
- **Color Scheme:** Portal-wide standardization using `public/css/quicksms-pastel.css`.
- **Global Layout & Density System:** Implemented via `public/css/quicksms-global-layout.css` with a max-width content container and responsive density classes.
- **RCS Agent Registration Wizard:** An 8-step wizard using jQuery SmartWizard.
- **Message Templates:** Comprehensive management with a multi-step creation wizard, versioning, and audit trails.
- **Unified RCS Wizard:** Shared component for creating rich RCS messages.
- **RCS Preview System:** Schema-driven renderer with an Android-style phone UI for channel-specific previews.
- **Shared Image Editor Component:** Reusable JavaScript component for image manipulation with fixed aspect ratio enforcement.
- **Branding Asset Audit Trail:** Audit-compliant metadata storage for logo and hero image uploads.

**Technical Implementations & Feature Specifications:**
- **Modules:** Dashboard, Messages, Contact Book, Reporting, Purchase, Management, Account, and Support.
- **Account Details:** Single source of truth for customer information, structured into five collapsible accordion cards with inline validation and audit logging.
- **Email-to-SMS Module:** Tabbed interface for managing email addresses for SMS triggers, including multi-step wizards and filtering.
- **Role-Based Access Control:** JavaScript-based system for UI visibility control based on user roles (viewer, analyst, admin).
- **Account Hierarchy View:** Vertical tree/flow layout for organizational structure (Main Account, Sub-Accounts, Users) with role and status pills. Includes contextual creation actions, modals for sub-account creation and user invitation, and direct user creation with security warnings.
  - **User Statuses:** Invited, Active, Suspended, Expired.
  - **Roles as Navigation Controls:** Account Owner, Admin, Messaging Manager, Finance/Billing, Developer/API User, Read-Only/Auditor, Campaign Approver, Security Officer.
  - **Sender Capability Levels:** Advanced Sender vs. Restricted Sender, enforced across messaging modules.
  - **Granular Permission Toggles:** Override role defaults per user, with changes logged.
  - **Permission Evaluation Order:** Account Scope → Role → Sender Capability Level → Permission Toggles.
  - **Layered Reporting Access:** KPI Dashboard, Campaign Analytics, Message Logs with default masking.
  - **Sub-Account Enforcement Rules:** Daily send limits, monthly spend caps, campaign approval workflows, recipient limits, with Warn Only, Soft Stop, and Hard Stop modes.
  - **Mandatory Audit Logging:** Centralized logging for hierarchy changes, user actions, and security events, with severity levels.
  - **Cross-Cutting Security Controls:** Mandatory MFA, optional IP allowlists, immediate suspension propagation, central password policy enforcement.
  - **Hierarchy Enforcement:** Users belong to one Sub-Account, credential sharing detection, silent permission changes prohibited, hierarchical context enforced.
  - **Permission Engine:** High-performance permission evaluation with in-memory caching and audit logging.
- **Unified Approval Framework:** Single approval system handling both SenderID and RCS Agent entity types with shared:
  - Queue infrastructure (unified approval queue)
  - Lifecycle handling (10-state status workflow)
  - Audit logging (status transitions, external references, integrity checksums)
  - Customer notification logic (email templates with preview modals)
  - External validation tracking (BrandAssure for SenderID, RCS Provider for RCS Agent)
  - Return-to-customer flow with version preservation
  - Force approve with CRITICAL severity audit logging
  - High-risk detection (BANK, NHS, HMRC keywords; Financial Services, Healthcare verticals)
- **SMS SenderID Registration:** UK-compliant 5-step registration wizard with lifecycle management and audit trails.
- **Numbers Management:** Library table for managing owned numbers with status, capabilities, filtering, and bulk actions.
- **Numbers Mode Selection:** Each number operates in one mutually exclusive mode (Portal Mode or API Mode) with explicit confirmation for switching.
- **Numbers Portal/API Configuration:** Mode-specific configuration options, including sub-account assignment and capability toggles.
- **Sub-Account Detail Page:** Dedicated full-page view for managing individual sub-accounts with status management and audit logging.
- **Campaign Approval Inbox:** Role-restricted page for reviewing pending campaigns with approve/reject workflow.
- **Audit Logs Module:** Enterprise-grade audit trail with tabbed interface, role-based access, 7-year retention, and cryptographic integrity verification.
  - **Normalized Event Catalogue:** Frozen, versioned catalogue of 60+ approved event types.
  - **Event Categories:** user_management, access_control, account, enforcement, security, authentication, messaging, contacts, data_access, financial, gdpr, compliance, api, system.
  - **Event Structure:** Each event includes code, module, category, severity, description, and required fields.
  - **Data Sanitization:** PII (phone numbers, message content, credentials) is never logged in the audit trail.
  - **Export:** CSV and Excel formats with read-only protection and async processing.
  - **Filters:** Date range, module, event type, sub-account, user, actor type, severity.

**Admin Control Plane:**
A separate internal interface for QuickSMS employees with a hard security boundary.
- **Security Architecture:** Separate authentication, mandatory MFA, IP allow-listing, configurable session timeout, and separate admin audit logging.
- **RBAC Structure (Feature-Flagged):** Roles defined in code (Super Admin, Support, Finance, Compliance, Sales) with specific permission sets.
- **Admin Roles:** Super Admin, Support, Finance, Compliance, Sales, each with defined access.
- **Admin Accounts Module:**
  - **Account Overview:** 8 KPI filter tiles (collapsible on scroll), 20-row paginated accounts table with compact density.
  - **Navigation Pattern:** Client names in table are links that navigate to dedicated Account Details page (no View Structure button in table rows).
  - **Account Details Page:** Reuses customer portal content with admin blue styling (#1e3a5f). Includes "View Account Structure" button to open hierarchy modal, breadcrumb navigation, and "Back to Accounts" link.
  - **Account Structure Modal:** Two-panel layout with hierarchy tree (left) and contextual details panel (right). Available via button on Account Details page.
  - **Row Actions:** Add Credit, Change Account Name, Edit Details, Edit Pricing, Suspend/Reactivate - all with confirmation modals and audit logging.
- **Impersonation:** Enhanced security controls:
  - Requires reason (min 10 characters)
  - 5-minute session limit with countdown timer
  - Read-only mode enforced (no data mutations)
  - No PII access during impersonation
  - Visual banner with Read-Only and No PII Access badges
  - All actions logged with CRITICAL severity to ADMIN audit
  - Session expiry auto-terminates impersonation
- **Admin Access Security:**
  - Separate admin_auth session (not shared with customer portal)
  - Whitelisted internal users enforcement via config/admin.php
  - Customer access attempt detection with CRITICAL severity logging
  - Automatic redirect to customer portal for unauthorized access
  - IP allowlist enforcement when enabled
  - Session timeout handling with redirect to admin login
- **Admin Responsibility Model:** Observe (READ), Control (WRITE), Investigate (SUPPORT), Govern (COMPLIANCE).
- **Global Admin Module Rules:** Single source of truth for data, filtering applies only on explicit action, maximum drill depth of 1, comprehensive audit logging for state mutations, and PII protection (masking by default, explicit reveal with logging).
- **Admin vs Customer Enforcement Matrix:**
  - Admin sees all clients; customers see only themselves
  - Admin approves; customers request
  - Admin overrides; customers configure within limits
  - **FORBIDDEN:** Admin cannot redefine delivery statuses, billing logic, or message parts
  - Shared definitions (delivery statuses, message parts, billing units) are immutable
- **Non-Functional Requirements:**
  - Scale: Full platform traffic, millions of records/day, multi-year historical queries
  - Performance: Heavy queries server-side, aggregations indexed, caching read-only only
  - Query Limits: Max 365-day date range, required pagination (50 default, 100 max), date range required
  - Client-side: Max 1000 records, server-side filtering required
- **Final Guardrails:** Any admin sub-module that invents new definitions, duplicates customer logic, redesigns instead of extends, bypasses audit logging, or exposes PII by default is INVALID and must be reworked. Admin Control Plane is a governed superset, not a redesign.

**Service Layer Architecture:**
- **NumbersAdminService (`public/js/numbers-admin-service.js`):** Backend-ready abstraction layer for Admin Numbers Management.
  - Provides clean separation between UI and backend API
  - All methods return Promises for async operation
  - Mock data mode for development (configurable via `NumbersAdminService.config.useMockData`)
  - Easy swap to real endpoints by changing config and service layer only
  - Methods: `listNumbers`, `getNumber`, `suspendNumber`, `reactivateNumber`, `reassignNumber`, `changeMode`, `updateCapabilities`, `updateApiWebhook`, `disableKeyword`, `updateOptoutRouting`, `getAuditHistory`, `getAccounts`, `getSubAccounts`
  - Bulk operations: `bulkSuspend`, `bulkReactivate`, `bulkReassign`, `bulkChangeMode`, `bulkUpdateCapabilities`
  - Export: `exportNumbers`
  - Mock database includes: numbers, accounts, sub-accounts, audit history
  - Realistic delays (200-600ms) for mock operations
  - Validation and error handling built-in
  - No credentials hardcoded in UI layer

## External Dependencies
- **PHP 8.1+ / Laravel 10:** Core backend framework.
- **Fillow SaaS Admin Template:** UI framework.
- **MetisMenu:** JavaScript library for navigation.
- **Stripe PHP SDK:** For Checkout Sessions, webhooks, and payment processing.
- **HubSpot Products API:** For live product pricing.
- **HubSpot Invoices API:** For fetching invoice data.
- **Stripe Checkout:** For secure payment processing.
- **Intervention Image (v3):** PHP image manipulation library for RCS asset processing.