# QuickSMS Web Application

## Overview
QuickSMS is a comprehensive Laravel-based SMS messaging platform designed for efficient communication management. It offers a user-friendly interface for managing messages, contacts, reporting, purchasing, and account administration. The platform's vision is to provide an intuitive and powerful solution for businesses to effectively communicate with their audience, leveraging a modern UI based on the Fillow SaaS Admin template. Key capabilities include a robust messaging system, detailed reporting, and streamlined account management.

## User Preferences
The user prefers detailed explanations when new concepts or features are introduced. The user wants the agent to prioritize developing the UI and front-end interactions before implementing backend logic. When making changes, the agent should clearly mark areas requiring backend integration with `TODO` comments. The user prefers an iterative development approach, focusing on completing one module's UI before moving to the next.

## System Architecture
QuickSMS is built using PHP 8.1+ and Laravel 10, incorporating the Fillow SaaS Admin Template (Bootstrap 5) and MetisMenu for UI/UX. The system uses Laravel Blade templating with a consistent `layouts.quicksms` structure and a shared pastel color scheme.

**UI/UX and Design Decisions:**
- **Consistent UI:** Responsive sidebar navigation, standardized data tables with client-side features (filtering, search, pagination), and consistent forms/modals.
- **Interactive Elements:** Mobile number masking, dynamic validation, real-time character counting.
- **Content Editor:** Enhanced with personalization, emoji support, and AI assistance.
- **Color Scheme:** Portal-wide standardization using `public/css/quicksms-pastel.css`.
- **Global Layout & Density System:** Implemented via `public/css/quicksms-global-layout.css` with a max-width content container, responsive density classes, and full-bleed opt-out options.
- **RCS Agent Registration Wizard:** An 8-step wizard using jQuery SmartWizard for agent registration, including branding, handset/compliance, agent type, company details, and test numbers, featuring validation and autosave.
- **Message Templates:** Comprehensive management with a multi-step creation wizard, versioning, lifecycle states, and audit trails.
- **Unified RCS Wizard:** A shared component for creating rich RCS messages.
- **RCS Preview System:** Schema-driven renderer with an Android-style phone UI for channel-specific previews.
- **Shared Image Editor Component:** Reusable JavaScript component for image manipulation with fixed aspect ratio enforcement for RCS media assets.
- **Branding Asset Audit Trail:** Audit-compliant metadata storage for all logo and hero image uploads.

**Technical Implementations & Feature Specifications:**
- **Modules:** Dashboard, Messages (Inbox, Campaign History, Test Message), Contact Book (contacts, lists, tags, opt-out), Reporting (KPIs, Message Log, Finance, Invoices), Purchase (messages, numbers), Management (RCS Agent/SMS SenderID, Templates, API, Email-to-SMS, Numbers), Account (details, users, audit, security), and Support.
- **Account Details:** The `Account > Details` page is the single source of truth for customer information, structured into five collapsible accordion cards: Sign Up Details, Company Information, Support & Operations, Contract Signatory, and VAT & Tax Information. It features inline validation, status indicators, usage chips for downstream modules, and robust audit logging. Permissions are restricted to Account Owner/Admin.
- **Email-to-SMS Module:** Tabbed interface for managing email addresses for SMS triggers, with sections for addresses, setups, contact list mappings, reporting groups, and configuration. Includes multi-step wizards, filtering, and backend integration hooks.
- **Role-Based Access Control:** JavaScript-based system for UI visibility control based on user roles (viewer, analyst, admin).
- **Account Hierarchy View:** Vertical tree/flow layout for organizational structure (Main Account, Sub-Accounts, Users) with role and status pills. Features:
  - Contextual creation actions (hover-only): "+ Add Sub-Account" on Main Account, "+ Add User" on Sub-Accounts
  - Create Sub-Account modal with enforcement rules scaffold (Daily Send Limit, Monthly Spend Cap, Campaign Approval, Limit Enforcement)
  - Invite User flow: Email, Role, Sender Capability Level (Advanced/Restricted, hidden for non-messaging roles)
  - Direct User Creation: Main Account Admins only, elevated-risk warning, temporary password (min 12 chars), mandatory password reset + MFA on first login, reason required, logged as high-risk audit event
  - User statuses: Invited, Active, Suspended, Expired (7-day invite expiry)
  - Roles as Navigation Controls: Account Owner (unique), Admin, Messaging Manager, Finance/Billing, Developer/API User, Read-Only/Auditor, Campaign Approver (optional), Security Officer (optional). Roles define navigation access, not granular toggles. Role changes are auditable.
  - Sender Capability Levels (separate from roles): Advanced Sender (free-form SMS/RCS, full Contact Book, CSV uploads, ad-hoc numbers, rich media, template creation) vs Restricted Sender (templates only, predefined lists only, no free-text editing). Applies only to messaging roles. Enforced across Send Message, Campaigns, Inbox, and Email-to-SMS.
  - Granular Permission Toggles: Override role defaults per user. Categories: Messaging & Content, Recipients & Contacts, Campaign Controls, Configuration, Financial Access, Security & Governance. UI shows inherited vs overridden status. Each toggle change is logged.
  - Permission Evaluation Order: Account Scope → Role → Sender Capability Level → Permission Toggles. Earlier layers cannot be overridden by later layers. Decision path is auditable.
  - Layered Reporting Access: KPI Dashboard (aggregated, non-sensitive), Campaign Analytics (medium sensitivity), Message Logs (high sensitivity). Phone numbers and message content masked by default. Finance users see cost data only, never message content.
  - Sub-Account Enforcement Rules: Daily send limits, monthly spend caps, campaign approval workflows, recipient limits. Supports Warn Only, Soft Stop (overridable), and Hard Stop (no override) modes. Alerts sent to Sub-Account and Main Account admins. All triggers logged.
  - Mandatory Audit Logging: Centralized logging for user creation/invites, role changes, permission toggles, enforcement overrides, MFA changes, and login failures. Each entry includes actor (userId, role, senderCapability), target, action, timestamp, IP address, and session context. Supports severity levels (low/medium/high/critical) and security alerts.
  - Cross-Cutting Security Controls: Mandatory MFA (configurable per Sub-Account with grace periods), optional IP allowlists (account and sub-account level with CIDR support), immediate suspension propagation (session invalidation, token revocation), central password policy enforcement (Standard/Strong/Enterprise tiers with lockout).
  - Hierarchy Enforcement: Users belong to exactly ONE Sub-Account (cross-sub-account prevented), credential sharing detection (blocks concurrent different-user logins), silent permission changes prohibited (reason required, notifications sent), flat user lists forbidden (hierarchical context always enforced).
  - Permission Engine: High-performance permission evaluation with in-memory caching (10K+ entries), indexed user lookups by sub-account, batch permission checks, sync/async APIs, automatic cache pruning, and mock API endpoints for backend integration. Scales to 1,000+ users without performance degradation.
  - Full audit logging for all hierarchy changes
- **SMS SenderID Registration:** UK-compliant 5-step registration wizard for various SenderID types, including lifecycle management and audit trails.
- **Numbers Management:** Library table for managing owned numbers (VMN, Dedicated Shortcode, Shortcode Keyword) with status, capability indicators, filtering, sorting, and configuration drawers. Supports bulk actions.
- **Numbers Mode Selection:** Each number operates in exactly one mutually exclusive mode (Portal Mode or API Mode), with explicit confirmation for mode switching and audit logging.
- **Numbers Portal/API Configuration:** Mode-specific configuration options, including sub-account assignment, capability toggles, default number settings for Portal Mode, and single sub-account attribution with inbound forwarding for API Mode.
- **Sub-Account Detail Page:** Dedicated full-page view for managing individual sub-accounts accessed via `/account/sub-accounts/{id}`. Features:
  - Breadcrumb navigation: Account > Sub-Accounts > {Sub-Account Name}
  - Status section with Live/Suspended/Archived status pills
  - Contextual actions: Live→Suspend, Suspended→Reactivate/Archive
  - Archive requires suspended status first (safety rule)
  - Confirmation modals with Fillow styling for all status changes
  - Immediate status updates with visual feedback (toast notifications)
  - Full audit logging for all status transitions
- **Campaign Approval Inbox:** Role-restricted page (campaign-approver, admin, owner) for reviewing pending campaigns with approve/reject workflow, rejection reason capture, and recent decisions history.

## External Dependencies
- **PHP 8.1+ / Laravel 10:** Core backend framework.
- **Fillow SaaS Admin Template:** UI framework.
- **MetisMenu:** JavaScript library for navigation.
- **Stripe PHP SDK:** For Checkout Sessions, webhooks, and payment processing.
- **HubSpot Products API:** For live product pricing.
- **HubSpot Invoices API:** For fetching invoice data.
- **Stripe Checkout:** For secure payment processing.
- **Intervention Image (v3):** PHP image manipulation library for RCS asset processing.