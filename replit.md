# QuickSMS Web Application

## Overview
QuickSMS is a Laravel-based SMS messaging platform designed for comprehensive communication management. It provides a user-friendly solution for messages, contacts, reporting, purchasing, and account administration. The platform aims to be a powerful and intuitive solution for effective communication, leveraging the Fillow SaaS Admin template for a modern UI.

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
- **RCS Agent Registration Wizard:** An 8-step page-based wizard using jQuery SmartWizard for registering RCS agents (accessed via `/management/rcs-agent/create`):
  1. Agent Basics (agent name, description, brand colour)
  2. Branding Assets (logo, hero banner image) - uses SharedImageEditor components
  3. Handset + Compliance (phone, email, website with visibility toggles + privacy policy URL + terms of service URL)
  4. Agent Type (billing category, use case, use case description)
  5. Messaging Behaviour (campaign frequency, opt-in/legitimate interest, opt-out method, monthly volume estimate)
  6. Company Details (company information, approver details - auto-populated from Account > Details)
  7. Test Numbers (up to 20 test numbers input)
  8. Review & Submit
  Features field validation per step, draft autosave, and audit trails. Uses Fillow SmartWizard template with stepper navigation, Next/Back buttons.
- **Message Templates:** Complete management with a multi-step creation wizard, versioning, lifecycle states (Draft/Live/Archived), and a detailed version history with rollback capabilities and an audit trail.
- **Unified RCS Wizard:** Shared component for creating rich RCS messages across different sections, supporting loading of Rich RCS templates.
- **RCS Preview System:** Schema-driven renderer with an Android-style phone UI for channel-specific previews (Rich RCS, Basic RCS, SMS).
- **Shared Image Editor Component:** Reusable JavaScript component for drag, zoom, and crop operations with fixed aspect ratio enforcement, used for RCS media assets.

**Platform Rules:**
- **Image Editor Consistency:** All RCS-related image uploads MUST use the SharedImageEditor component (`public/js/shared-image-editor.js`). No alternative image upload or crop logic should be introduced elsewhere. Supported use cases:
  - RCS Agent Logo (preset: `agent-logo`, 222×222 px, circular)
  - RCS Agent Hero/Banner (preset: `agent-hero`, 1480×448 px, 45:14 ratio)
  - RCS Rich Card Media (presets: `rich-card-short`, `rich-card-medium`, `rich-card-tall`)
  - For new image requirements, add a preset to PRESET_CONFIGS in the component.

- **Branding Asset Audit Trail:** All logo and hero image uploads persist audit-compliant metadata for reporting and dispute resolution:
  - `cdnUrl`: Final CDN URL after server upload (populated by backend)
  - `originalSrc`: Original image source (data URL or fetch URL)
  - `originalFileName`, `originalFileSize`, `originalFileType`: Original file details
  - `sourceType`: Upload method (`file_upload` or `url_fetch`)
  - `crop`: Crop coordinates `{x, y, width, height}`
  - `zoom`, `offsetX`, `offsetY`: Editor state
  - `outputWidth`, `outputHeight`, `aspectRatio`, `frameShape`: Output specifications
  - `userId`: User who performed the upload (from Auth::id())
  - `timestamp`: ISO 8601 timestamp when crop was applied
  - `clientTimezone`: User's timezone for audit context
  - `uploadedAt`: Server-side timestamp when CDN upload completed (populated by backend)
  - Database columns: `logo_crop_metadata` and `hero_crop_metadata` (JSON) in `rcs_agents` table

**Technical Implementations & Feature Specifications:**
- **Dashboard:** Provides an operational overview, RCS promotion, support access, and notifications.
- **Messages:** Includes features for sending messages, an Inbox with a three-panel conversation workspace (conversation list, chat pane, contact info sidebar), and Campaign History. Supports Rich RCS message rendering directly in the chat thread and a "Test Message" feature.
- **Contact Book:** Enables management of contacts, lists (static/dynamic), tags, and opt-out lists.
- **Reporting:** Features a dashboard with customizable KPI tiles (ApexCharts), a detailed Message Log, Finance Data, Invoices (from HubSpot), and a Download Area for generated exports. The Download Area includes filtering, sorting, pagination, and bulk actions.
- **Purchase:** Functionality for purchasing messages and numbers (Admin/Finance/Messaging Managers only) leveraging HubSpot Products API for pricing and Stripe for payment processing. Includes "Pay Invoice" and "Top Up Balance" flows. The Purchase Numbers section offers acquiring UK Virtual Mobile Numbers, Shared Short Codes, and Dedicated Short Codes with comprehensive UI controls for selection, validation, and cost summaries.
- **Management:** Covers RCS Agent/SMS SenderID registrations, Templates, API Connections, Email-to-SMS, and Number management.
- **Account:** Manages account details, user/access, sub-accounts, audit logs, and security settings.
- **Support:** Provides a dashboard, ticket creation, and knowledge base.
- **Template Integration:** Templates are dynamically filtered by trigger type and channel, with version numbers displayed and a refresh option.
- **RCS Asset Management:** Server-side image processing for RCS media using Intervention Image, including SSRF protection, dedicated storage, and an interactive crop editor.
- **MessageLog Model:** Defines message structure with encrypted content and role-based access.
- **Development Environment:** Utilizes PostgreSQL database with Neon backend for persistence.
- **CSS Architecture:** Module-specific CSS uses inline `@push('styles')` for page-specific layouts; shared badge classes (.badge-bulk, .badge-campaign, .badge-test, .badge-live-status, .badge-suspended, etc.) are centralized in `public/css/quicksms-pastel.css`.
- **Email-to-SMS Module:** Tabbed interface for managing email addresses that trigger SMS to Contact Lists. Core behavior: when an email is sent to a generated QuickSMS email address, SMS is sent to the mapped Contact Book List. SenderID is taken from email subject, message content from email body. Features:
  - Email-to-SMS Addresses tab: Table with filter bar (debounced search, multi-select dropdowns, date presets, Apply/Reset Filters, filter chips), Create Address modal, View Details drawer, Suspend/Delete modals
  - Contact Lists tab (Mapping Library): Manages mappings between Email-to-SMS Addresses and existing Contact Book Lists. Table columns: Email-to-SMS Address (copyable), Linked Contact List, Recipients Count, Allowed Sender Emails, Last Used, Created, Actions (View/Edit/Archive). Features filter bar with date range, Contact List dropdown, and search; 300ms debounced search; filter chips. Create Mapping wizard (4-step full-screen Fillow wizard):
    1. Select Contact Book List (searchable dropdown with name + recipient count)
    2. Allowed Sender Emails (optional whitelist)
    3. Email Address Generation (auto-generates unique address: listname-XXXX@sms.quicksms.com)
    4. Confirmation (read-only summary showing SenderID from EMAIL SUBJECT, SMS content from EMAIL BODY)
  - Reporting Groups tab: Table-based library for organizing email addresses into groups (reporting/billing attribution only). Features filter bar with date range, status, and search; columns for Group Name, Description, Linked Addresses, Messages Sent, Last Activity, Created, and Actions (Edit/Archive)
  - Configuration tab: Global settings panel with:
    - Email Settings: Originating Email Addresses (multi-line), Email-to-SMS via Mail Client toggle, Email-to-SMS from Attachments toggle (future/disabled)
    - Message Settings: Multipart SMS toggle, Fixed SenderID toggle with SenderID dropdown (conditional visibility), Subject as SenderID toggle
    - Delivery Receipts: Toggle with conditional Alternate Receipts Email input
    - Content Processing: Signature Removal patterns (regex support)
    - Conflict warning banner when Fixed SenderID=ON and Subject as SenderID=ON
    - SenderID validation (3-11 alphanumeric characters)
    - Resolution Preview: Interactive table showing SenderID resolution outcomes for mock subjects
    - Email Parsing Test: Interactive tool to test email-to-SMS parsing with subject/body input, shows extracted SenderID, validation status, SMS content, character count, and delivery status (accepted/rejected with reason)
    - SenderID Resolution Priority Rules:
      1. Fixed SenderID=ON → Always use selected SenderID, ignore subject
      2. Fixed SenderID=OFF + Subject as SenderID=ON → Extract alphanumeric from subject
      3. Extraction fails (empty/short/invalid) → Reject email and notify sender
    - Email Parsing Rules:
      - SenderID extracted from email subject (trimmed, alphanumeric only, 3-11 chars)
      - SMS content extracted from email body (plain text, HTML fallback)
      - Signature removal patterns applied from configuration
      - Invalid SenderID or empty body → Email rejected with sender notification (mocked)
    - Audit log placeholder hooks for backend integration
  - Integrates with Contact Lists, Templates, Opt-Out Lists, Sender IDs, and Reporting
- **Role-Based Access Control:** JavaScript-based system for controlling UI visibility based on viewer, analyst, and admin roles, enforced for features like Purchase, RCS Agent Registration, and delete actions.

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