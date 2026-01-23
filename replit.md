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
- **RCS Button Click Tracking:** Shared component in Button Config modal (`rcs-button-config-modal.blade.php`) with toggle, tracking ID, UTM parameters (URL buttons only), and conversion tracking. Produces consistent tracking payload structure across all entry points (Send Message, Templates, Inbox).

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
  - **Admin Invoices Module:** Global view of all customer invoices, cloned from Customer Portal invoice module with: Account Name as first data column (sortable, clickable navigation to account), Account Name filter (typeahead/searchable dropdown), global summary strip (Total Invoices, Total Value, Outstanding, Overdue, Paid This Month, Accounts), admin blue (#1e3a5f) theme applied throughout, Apply Filters pattern with active filter chips, 25 invoices per page pagination.
  - **Admin Account Billing Page:** Customer-scoped billing view at `/admin/accounts/{accountId}/billing` with summary bar (Billing Mode, Current Balance, Credit Limit, Available Credit, Account Status, Last Updated), billing settings card (Billing Type toggle with Prepaid/Postpaid, inline-editable Credit Limit with HubSpot sync, Payment Terms, Currency, VAT status), and Customer Invoices table. Features include:
    - **Billing Type Toggle:** Segmented control with confirmation modal, permission-gated (`billing.edit_mode`), billing risk warnings for outstanding invoices.
    - **Credit Limit Inline Edit:** Currency input with £ symbol, validation (0-£1M, 2 decimals), Save/Cancel controls, HubSpot sync, automatic Available Credit recalculation.
    - **Create Invoice/Credit Actions:** Reusable shared modal component (`resources/views/admin/partials/create-invoice-credit-modal.blade.php`, `public/js/invoice-credit-modal.js`) with locked customer mode (preselected, read-only with lock icon). Permission-gated (`billing.create_invoice`, `billing.create_credit`). On success: modal closes, invoices table refreshes with new row highlighted.
  - **Shared Create Invoice/Credit Modal (`public/js/invoice-credit-modal.js`):** Backend-ready JavaScript module supporting both global (Admin > Invoices) and customer-scoped (Admin > Accounts > Billing) contexts. Features customer typeahead search OR locked customer mode, VAT calculation based on customer settings (including reverse charge), line item entry with live totals, override email, and full audit logging (INVOICE_CREATE_ATTEMPT, INVOICE_CREATED/CREDIT_CREATED, INVOICE_CREATE_FAILED).
  - **Impersonation:** Enhanced security controls including reason requirement, session limit, read-only mode, no PII access, visual banner, and critical audit logging.
  - **Admin Access Security:** Separate authentication, whitelisted internal users, IP allowlist enforcement, and session timeout handling.
  - **Non-Functional Requirements:** Designed for full platform traffic, millions of records/day, multi-year historical queries, with performance considerations for heavy queries, indexed aggregations, and query limits (max 365-day date range, pagination).

**Service Layer Architecture:**
- **ContactTimelineService (`public/js/contact-timeline-service.js`):** Backend-ready abstraction layer for Contact Activity Timeline.
  - Unified Timeline Event Data Model with fields: event_id, tenant_id, contact_id, msisdn_hash, timestamp, event_type, source_module, actor_type (User/System/API), actor_id, actor_name, metadata (JSON)
  - Methods: `getContactTimeline(contactId, filters, pagination)`, `revealMsisdn(contactId, reason)`
  - Cursor-based pagination with configurable page size (default 50, max 100)
  - Mock data mode for development (configurable via `ContactTimelineService.config.useMockData`)
  - Clean separation between UI and backend API - swap endpoints only
  - No PII leakage - MSISDN stored as hash, reveal requires audit-logged API call
- **NumbersAdminService (`public/js/numbers-admin-service.js`):** Backend-ready abstraction layer for Admin Numbers Management, providing separation between UI and backend API, supporting mock data for development, and handling various operations for numbers, accounts, and audit history with validation and error handling.
- **BillingServices (`public/js/billing-services.js`):** Unified billing services layer supporting the Admin Account Billing page.
  - **HubSpotBillingService:** Source of truth for billingMode and creditLimit. Methods: `getBillingProfile(accountId)`, `updateBillingMode(accountId, mode)`, `updateCreditLimit(accountId, creditLimit)`. Returns typed BillingProfile objects with hubspotContactId, hubspotUrl, paymentTerms, currency, VAT settings.
  - **InternalBillingLedgerService:** Internal balance tracking. Methods: `getBalance(accountId)` returns LedgerBalance (currentBalance, lastUpdatedTimestamp, currency), `calculateAvailableCredit(billingMode, currentBalance, creditLimit)`.
  - **InvoicesService:** Invoice and credit note management with Xero integration. Methods: `listInvoices(filters)` with pagination, `createInvoice(request)`, `createCredit(request)`, `getInvoice(invoiceNumber)`, `syncToXero(invoiceNumber)`. Returns typed Invoice objects with xeroInvoiceId.
  - **AccountDetailsService:** Basic account information. Method: `getAccountDetails(accountId)` returns name, status.
  - **BillingFacade:** Unified data loading combining all services. Methods: `loadCompleteBillingData(accountId)` returns complete billing profile, `checkOutstandingInvoices(accountId)` returns outstanding invoice summary.
  - **Configuration:** `BillingServices.config.useMockData` controls mock/real API mode. Set to `false` to use real API endpoints defined in `apiBaseUrl`, `hubspotApiUrl`, `xeroApiUrl`.
  - **Design Principles:** Typed JSDoc objects, no hardcoded credentials, HubSpot as source of truth, UI revert on API failure via Promise rejection, backend-ready without refactor.

## External Dependencies
- **PHP 8.1+ / Laravel 10:** Core backend framework.
- **Fillow SaaS Admin Template:** UI framework based on Bootstrap 5.
- **MetisMenu:** JavaScript library for navigation.
- **Stripe PHP SDK:** For Checkout Sessions, webhooks, and payment processing.
- **HubSpot Products API:** For live product pricing.
- **HubSpot Invoices API:** For fetching invoice data.
- **Stripe Checkout:** For secure payment processing.
- **Intervention Image (v3):** PHP image manipulation library for RCS asset processing.