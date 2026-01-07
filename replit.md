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
- **RCS Agent Registration Wizard:** A 7-step fullscreen modal wizard for registering RCS agents:
  1. Identity (agent name, description, brand colour)
  2. Branding (logo, hero banner image)
  3. Contact (phone, email, website with visibility toggles)
  4. Compliance (privacy policy URL, terms of service URL)
  5. Messaging (billing category, use case, campaign details, opt-in/out, test numbers)
  6. Company (company information, approver details)
  7. Review & Submit
  Features field validation per step, draft autosave, and audit trails.
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
- **CSS Architecture:** Module-specific CSS overrides Fillow styles with unique prefixes.
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