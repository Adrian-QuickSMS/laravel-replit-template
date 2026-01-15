# QuickSMS Web Application

## Overview
QuickSMS is a Laravel-based SMS messaging platform designed for comprehensive communication management. It provides a user-friendly solution for managing messages, contacts, reporting, purchasing, and account administration. The platform aims to be a powerful and intuitive solution for effective communication, leveraging the Fillow SaaS Admin template for a modern UI.

## User Preferences
The user prefers detailed explanations when new concepts or features are introduced. The user wants the agent to prioritize developing the UI and front-end interactions before implementing backend logic. When making changes, the agent should clearly mark areas requiring backend integration with `TODO` comments. The user prefers an iterative development approach, focusing on completing one module's UI before moving to the next.

## System Architecture
QuickSMS is built with PHP 8.1+ and Laravel 10, utilizing the Fillow SaaS Admin Template (Bootstrap 5) and MetisMenu for UI/UX. It uses Laravel Blade templating with a consistent `layouts.quicksms` structure and a shared pastel color scheme.

**UI/UX and Design Decisions:**
- **Navigation:** Responsive sidebar with active route highlighting.
- **Data Tables:** Standardized patterns with client-side filtering, search, pagination, and actions.
- **Forms & Modals:** Consistent use for various workflows.
- **Interactive Elements:** Mobile number masking, dynamic validation, real-time character counting.
- **Content Editor:** Enhanced with personalization, emoji support, AI assistance, and channel-aware behavior.
- **Color Scheme:** Portal-wide standardization using `public/css/quicksms-pastel.css`.
- **Global Layout & Density System:** Implemented in `public/css/quicksms-global-layout.css` with automatic application via `layouts/default.blade.php`. Features:
  - **Content Container:** Max-width 1280px wrapper (`.qsms-content-wrap`) for centered, constrained content
  - **Density Classes:** `.qsms-density-compact` applied globally with responsive breakpoints:
    - Default/Compact: @media (max-width: 1440px) - standard laptops
    - Extra Compact: @media (max-width: 1366px) - smaller laptops  
    - Mobile: @media (max-width: 991.98px)
  - **Full-Bleed Opt-Out:** Pages can add `@section('body_class', 'qsms-fullbleed')` to bypass container constraints (used by Inbox)
  - **CSS Variables:** Density tokens for consistent spacing (`--qsms-card-pad`, `--qsms-gap`, `--qsms-control-pad-y/x`, `--qsms-font-*`, `--qsms-btn-pad-*`)
  - **Modal Sizing:** Responsive modal max-widths (sm: 480px, default: 680px, lg: 900px, xl: 1100px)
- **RCS Agent Registration Wizard:** An 8-step page-based wizard using jQuery SmartWizard for registering RCS agents, including branding assets, handset/compliance details, agent type, messaging behavior, company details, and test numbers. Features field validation, draft autosave, and audit trails.
- **Message Templates:** Complete management with a multi-step creation wizard, versioning, lifecycle states (Draft/Live/Archived), and a detailed version history with rollback capabilities and an audit trail.
- **Unified RCS Wizard:** Shared component for creating rich RCS messages across different sections, supporting loading of Rich RCS templates.
- **RCS Preview System:** Schema-driven renderer with an Android-style phone UI for channel-specific previews (Rich RCS, Basic RCS, SMS).
- **Shared Image Editor Component:** Reusable JavaScript component for drag, zoom, and crop operations with fixed aspect ratio enforcement, used for RCS media assets. All RCS-related image uploads must use this component and its defined presets, enforcing specific dimensions and aspect ratios.
- **Branding Asset Audit Trail:** All logo and hero image uploads persist audit-compliant metadata (CDN URL, original source, file details, crop coordinates, editor state, user info, timestamps) in the `rcs_agents` table.

**Technical Implementations & Feature Specifications:**
- **Dashboard:** Provides an operational overview, RCS promotion, support access, and notifications.
- **Messages:** Includes features for sending messages, an Inbox with a three-panel conversation workspace (conversation list, chat pane, contact info sidebar), Campaign History, and a "Test Message" feature.
- **Contact Book:** Enables management of contacts, lists (static/dynamic), tags, and opt-out lists.
- **Reporting:** Features a dashboard with customizable KPI tiles, a detailed Message Log, Finance Data, Invoices, and a Download Area for generated exports.
- **Purchase:** Functionality for purchasing messages and numbers (Admin/Finance/Messaging Managers only), leveraging HubSpot for pricing and Stripe for payments. Includes "Pay Invoice" and "Top Up Balance" flows.
- **Management:** Covers RCS Agent/SMS SenderID registrations, Templates, API Connections, Email-to-SMS, and Number management.
- **Account:** Manages account details, user/access, sub-accounts, audit logs, and security settings.
- **Account Details (Source of Truth):** The `Account > Details` page serves as the authoritative single source of truth for all customer account information. Data entered here is automatically shared with RCS Agent Registration, SMS SenderID Registration, Billing & Invoicing, VAT handling, Support tickets, and Compliance records.
  
  **Page Structure (5 Collapsible Accordion Cards):**
  1. **Sign Up Details** - First Name, Last Name, Job Title, Business Name (legal name), Business Email Address (unique platform-wide), Mobile Number (E.164 preferred). All fields mandatory with inline validation. Editable by Account Owner/Admin only.
  2. **Company Information** (Required to go live) - Legal name, Trading name, Company registration number, Industry, Website, Phone, Full registered address
  3. **Support & Operations** (Optional) - Primary Contact (name, title, email, phone), Technical Contact details
  4. **Contract Signatory** (Required to go live) - Authorized signatory name, title, email, phone
  5. **VAT & Tax Information** (Required to go live) - VAT Registered (Yes/No), and if Yes: VAT Number (country-validated), VAT Country, Reverse Charges (Yes/No with tooltip). VAT fields hidden when not registered. Settings feed billing and invoice logic.
  
  **UX Rules:**
  - Mandatory fields marked with red asterisk (*), optional fields labelled "(Optional)"
  - Section status indicators: "Complete" (green), "Required to go live" (red), "Optional" (grey)
  - Inline validation only (no modals), auto-save for Sign Up and Support sections, explicit Save button for other sections
  - Usage chips show which downstream modules consume each field (RCS Registration, SMS SenderID, Invoices, etc.)
  - Sensitive changes (VAT, Signatory) are audit-logged with timestamps
  
  Downstream modules must read from this data and must NOT duplicate fields.
- **Support:** Provides a dashboard, ticket creation, and knowledge base.
- **Template Integration:** Templates are dynamically filtered by trigger type and channel, with version numbers and a refresh option.
- **RCS Asset Management:** Server-side image processing for RCS media using Intervention Image, including SSRF protection, dedicated storage, and an interactive crop editor.
- **MessageLog Model:** Defines message structure with encrypted content and role-based access.
- **Development Environment:** Utilizes PostgreSQL database with Neon backend for persistence.
- **CSS Architecture:** Module-specific CSS uses inline `@push('styles')`; shared badge classes are centralized in `public/css/quicksms-pastel.css`.
- **Email-to-SMS Module:** Tabbed interface for managing email addresses that trigger SMS to Contact Lists. Includes sections for Email-to-SMS Addresses, Standard setups, Contact List Mappings, Reporting Groups, and a Configuration panel. Features multi-step wizards for creation, filter bars, detailed views, and global settings for email processing, message settings, delivery receipts, and content processing. Provides tools for SenderID resolution preview and email parsing tests. Includes hooks for backend integration with a dedicated service layer.
- **Role-Based Access Control:** JavaScript-based system for controlling UI visibility based on viewer, analyst, and admin roles, enforced for features like Purchase, RCS Agent Registration, and delete actions.
- **SMS SenderID Registration Module:** UK-compliant SenderID registration system including Alphanumeric, Numeric, and Shortcode types. Features a 5-step registration wizard, lifecycle management (Draft, Pending, Approved, Rejected, Suspended, Archived), usage scopes, approval tracking, and a comprehensive audit trail for all status transitions.
- **Numbers Management Module:** Library table for managing owned numbers (VMN, Dedicated Shortcode, Shortcode Keyword). Features status pills (Active/Suspended/Pending), capability indicators (API, Portal SenderID, Inbox, Opt-out), filtering by country/type/status/mode/capability/sub-account, sortable columns, and a configuration drawer for viewing number details. Includes actions for suspend/reactivate/release with confirmation modals. Supports bulk actions (Suspend, Reactivate, Assign Sub-Accounts) with row multi-select.
- **Numbers Mode Selection (Hard Rule):** Each number operates in exactly ONE mutually exclusive mode - Portal Mode (Campaigns, Inbox, Opt-out) or API Mode (REST API only). Mode switching requires explicit confirmation modal showing disabled/enabled features. Changes are logged for audit and propagate immediately across all modules. No silent behavior changes or auto-migration between modes.
- **Numbers Portal Configuration:** Visible only when Mode = Portal. Includes sub-account assignment (controls visibility, defaults, reporting), capability toggles (Allow as SenderID, Enable Inbox Replies, Enable Opt-out Handling), and defaults per sub-account (Default Sender Number, Default Inbox Number, Default Opt-out Number). Enforces one default per capability per sub-account rule, and only active numbers can be set as defaults.
- **Numbers API Configuration:** Visible only when Mode = API. Includes single sub-account attribution (API numbers can belong to only one sub-account), inbound forwarding toggle, and inbound webhook URL (HTTPS only). API numbers cannot be used as SenderID, Inbox number, or in Portal features. If URL is empty, inbound messages are not forwarded. API routing works independently of Portal logic.

## External Dependencies
- **PHP 8.1+ / Laravel 10:** Core backend framework.
- **Fillow SaaS Admin Template:** UI framework.
- **MetisMenu:** JavaScript library for navigation.
- **SQLite:** Database for local development.
- **Stripe PHP SDK (stripe/stripe-php):** For Checkout Sessions, webhooks, and payment processing.
- **HubSpot Products API:** For live product pricing (messages and numbers).
- **HubSpot Invoices API:** For fetching invoice data.
- **Stripe Checkout:** For secure payment processing.
- **Intervention Image (v3):** PHP image manipulation library for RCS asset processing.