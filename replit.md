# QuickSMS Web Application

## Overview
QuickSMS is a Laravel-based SMS messaging platform focused on efficient communication management through a modern user interface. It offers comprehensive tools for message handling, contact management, reporting, purchasing, and account administration, aiming to provide an intuitive solution for businesses to engage with their audience effectively.

## User Preferences
The user prefers detailed explanations when new concepts or features are introduced. The user wants the agent to prioritize developing the UI and front-end interactions before implementing backend logic. When making changes, the agent should clearly mark areas requiring backend integration with `TODO` comments. The user prefers an iterative development approach, focusing on completing one module's UI before moving to the next.

## System Architecture
QuickSMS is built with PHP 8.1+ and Laravel 10, leveraging the Fillow SaaS Admin Template (Bootstrap 5) and MetisMenu for its UI/UX. The system uses Laravel Blade templating with a consistent layout and a pastel color scheme.

**UI/UX and Design Decisions:**
- **Consistent UI:** Responsive sidebar, standardized data tables with client-side features, and uniform forms/modals.
- **Interactive Elements:** Mobile number masking, dynamic validation, real-time character counting.
- **Content Editor:** Enhanced with personalization, emoji support, and AI assistance.
- **Color Scheme:** Portal-wide standardization using `public/css/quicksms-pastel.css`.
- **Global Layout & Density System:** Implemented via `public/css/quicksms-global-layout.css` with a max-width content container and responsive density classes.
- **RCS Agent Registration Wizard:** An 8-step wizard using jQuery SmartWizard.
- **Message Templates:** Comprehensive management with a multi-step creation wizard, versioning, and audit trails.
- **RCS Preview System:** Schema-driven renderer with an Android-style phone UI for channel-specific previews.
- **Shared Image Editor Component:** Reusable JavaScript component for image manipulation with fixed aspect ratio enforcement.

**Technical Implementations & Feature Specifications:**
- **Core Modules:** Dashboard, Messages, Contact Book, Reporting, Purchase, Management, Account, and Support.
- **Account Details:** Single source of truth for customer information, structured into five collapsible accordion cards with inline validation and audit logging.
- **Email-to-SMS Module:** Tabbed interface for managing email addresses for SMS triggers, including multi-step wizards and filtering.
- **Role-Based Access Control:** JavaScript-based system for UI visibility control and granular permission toggles, with a defined permission evaluation order.
- **Account Hierarchy View:** Vertical tree/flow layout for organizational structure with contextual creation actions, modals for sub-account creation and user invitation. Includes various user statuses and roles.
  - **Sub-Account Enforcement Rules:** Daily send limits, monthly spend caps, campaign approval workflows, recipient limits, with Warn Only, Soft Stop, and Hard Stop modes.
  - **Mandatory Audit Logging:** Centralized logging for hierarchy changes, user actions, and security events.
  - **Cross-Cutting Security Controls:** Mandatory MFA, optional IP allowlists, immediate suspension propagation, central password policy enforcement.
- **Unified Approval Framework:** Single approval system for SenderID and RCS Agent entity types, sharing queue infrastructure, 10-state status workflow, audit logging, customer notification logic, and external validation tracking.
- **SMS SenderID Registration:** UK-compliant 5-step registration wizard with lifecycle management and audit trails.
- **Numbers Management:** Library table for managing owned numbers with status, capabilities, filtering, and bulk actions, including mode selection (Portal or API) with configuration options.
- **Sub-Account Detail Page:** Dedicated full-page view for managing individual sub-accounts with status management and audit logging.
- **Campaign Approval Inbox:** Role-restricted page for reviewing pending campaigns with approve/reject workflow.
- **Contact Activity Timeline:** Comprehensive activity history for each contact with a responsive UI, permission-gated MSISDN reveal, collapsible filters, and paginated event display.
- **Audit Logs Module:** Enterprise-grade audit trail with tabbed interface, role-based access, 7-year retention, cryptographic integrity verification, normalized event catalogue, and PII data sanitization.
- **Admin Control Plane:** A separate internal interface for QuickSMS employees with a hard security boundary, separate authentication, mandatory MFA, IP allow-listing, and distinct RBAC. Features include:
  - **Admin Accounts Module:** Overview, dedicated Account Details page with hierarchy modal, and row actions like Add Credit, Change Name, Edit Details, Suspend/Reactivate.
  - **Admin Email-to-SMS Module:** Global view of configurations across customer accounts, reusing customer logic with admin-specific additions like account column and filtering.
  - **Admin Campaign History Module:** Global view of all campaigns with account name, filters, and actions (Cancel, Suspend, Resume) requiring reasons and audit logging.
  - **Impersonation:** Enhanced security controls including reason requirement, session limit, read-only mode, no PII access, visual banner, and critical audit logging.
  - **Admin Access Security:** Separate authentication, whitelisted internal users, IP allowlist enforcement, and session timeout handling.
  - **Non-Functional Requirements:** Designed for full platform traffic, millions of records/day, multi-year historical queries, with performance considerations for heavy queries, indexed aggregations, and query limits (max 365-day date range, pagination).

**Service Layer Architecture:**
- **NumbersAdminService (`public/js/numbers-admin-service.js`):** Backend-ready abstraction layer for Admin Numbers Management, providing separation between UI and backend API, supporting mock data for development, and handling various operations for numbers, accounts, and audit history with validation and error handling.

## External Dependencies
- **PHP 8.1+ / Laravel 10:** Core backend framework.
- **Fillow SaaS Admin Template:** UI framework based on Bootstrap 5.
- **MetisMenu:** JavaScript library for navigation.
- **Stripe PHP SDK:** For Checkout Sessions, webhooks, and payment processing.
- **HubSpot Products API:** For live product pricing.
- **HubSpot Invoices API:** For fetching invoice data.
- **Stripe Checkout:** For secure payment processing.
- **Intervention Image (v3):** PHP image manipulation library for RCS asset processing.