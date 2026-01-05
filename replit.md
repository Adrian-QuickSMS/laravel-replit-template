# QuickSMS Web Application

## Overview
QuickSMS is a Laravel-based SMS messaging platform that provides a comprehensive, user-friendly solution for managing messages, contacts, reporting, purchasing, and account administration. It leverages the Fillow SaaS Admin template for its design system, focusing on a modern and intuitive UI to facilitate effective communication management. The project's ambition is to be a powerful and user-friendly platform for communication management.

## User Preferences
The user prefers detailed explanations when new concepts or features are introduced. The user wants the agent to prioritize developing the UI and front-end interactions before implementing backend logic. When making changes, the agent should clearly mark areas requiring backend integration with `TODO` comments. The user prefers an iterative development approach, focusing on completing one module's UI before moving to the next.

## System Architecture
QuickSMS is built on PHP 8.1+ and Laravel 10, utilizing the Fillow SaaS Admin Template (Bootstrap 5) and MetisMenu for UI/UX and navigation. The application uses Laravel's Blade templating, extending `layouts.quicksms` from `layouts.default` for consistent UI, augmented with custom components and a shared pastel color scheme.

**UI/UX and Design Decisions:**
- **Navigation:** Responsive sidebar with active route highlighting.
- **Data Tables:** Standardized patterns with client-side filtering, search, pagination, and bulk/row actions.
- **Forms & Modals:** Consistent use for creation, editing, and specific workflows.
- **Interactive Elements:** Mobile number masking, dynamic validation, real-time character counting, and visual feedback.
- **Content Editor:** Enhanced editor with personalization, emoji support, AI assistance, and channel-aware behavior.
- **Color Scheme:** Shared pastel color scheme (`public/css/quicksms-pastel.css`) for portal-wide standardization.

**Technical Implementations & Feature Specifications:**
- **Dashboard:** Operational overview, RCS promotion tools, and support/notifications using Fillow's widget-stat pattern.
- **Messages:** Features for sending messages, an Inbox with a three-panel conversation workspace (conversation list, chat pane, contact info sidebar), and Campaign History. The Inbox uses a hybrid flex/absolute positioning layout and robust JavaScript for filtering and interaction.
- **Contact Book:** Management of contacts, static/dynamic lists, tags, and opt-out lists.
- **Reporting:** Includes a Dashboard with customizable grid (using CSS Grid and SortableJS) for KPI tiles and charts (ApexCharts), a detailed Message Log with extensive filtering, Finance Data with mock API billing layer, Invoices (HubSpot API integration), and a Download Area. Reporting leverages a `MockReportingDataService` for consistent data generation across all widgets and filter-aware API endpoints.
  - **Invoices Page:** Read-only display of HubSpot invoices with persistent Account Financial Summary header (Billing Mode, Current Balance, Credit Limit, Available Credit, Account Status with Active/Credit Hold states), filterable invoice list, and detail drawer. Financial summary updates in near-real time (30s interval) with visibility-aware polling. Displays Invoice #, Billing Period, Issue/Due Date, Status, Line Items, VAT, Total, Balance Due, and PDF URL. All values are fetched directly from HubSpot (no UI recalculation). Includes expandable helper text explaining credit usage and credit hold consequences. Falls back to mock data when `HUBSPOT_ACCESS_TOKEN` is not configured.
    - **Row-Level Actions:** Each invoice row has a dropdown menu with: View Invoice (always available), Download PDF (uses HubSpot-provided PDF URL, disabled if unavailable), and Pay Now (visible only for Admin/Finance roles when status is Issued/Overdue and balance > 0).
    - **Filters:** Non-auto-applying filters for Billing Year/Month, Invoice Status, and Invoice Number (free-text). Apply Filters and Reset buttons control when filtering occurs. Active filters display as removable chips; removing a chip requires re-applying filters to update results. Year filter supports multi-year history (2015 to current).
    - **Invoice Detail Drawer:** Right-hand slide-out panel displaying full invoice details. Header shows Invoice Number, Status Badge, and Billing Period. Includes Issue/Due Date card, Invoice Summary (Subtotal, VAT, Total, Amount Paid, Balance Outstanding), and read-only Line Items from HubSpot. Actions: Download PDF, Pay Invoice (if unpaid and permitted), View Billing Breakdown (navigates to Finance Data with pre-applied filters for billing month and invoice reference for 1:1 reconciliation).
- **Purchase:** Functionality to purchase messages (Admin/Finance only) with HubSpot Products API integration for live pricing and VAT calculation. Includes UI for product selection, order summary, and payment flow with Stripe (webhook handling for payment status).
  - **Pay Invoice Flow:** For postpaid accounts with Issued/Overdue invoices. Admin/Finance users can click "Pay Now" to see a confirmation modal (invoice number, amount due, status, due date), then redirect to Stripe Checkout. On success, returns to invoices page with success message, reloads invoice list and account summary. Stripe handles all card logic (PCI DSS compliant). API endpoint validates invoice status and balance before creating checkout session.
  - **Top Up Balance Flow:** For prepaid accounts. Entry point is "Top Up Balance" button in Account Financial Summary which redirects to the Purchase Messages page (/purchase/messages). This reuses the fully-functional Purchase page with its tier cards (Starter/Enterprise/Bespoke), noUiSlider volume selectors, live HubSpot pricing, and Stripe Checkout integration.
  - **Security & Compliance:** Live pricing (no caching), multi-currency (GBP/EUR/USD), PCI DSS compliant (portal never handles card data), all actions auditable via Laravel Log.
  - **Audit Logging:** Comprehensive logging for finance audit, dispute resolution, and compliance. Logs user ID, timestamp, IP address for: invoice views, PDF downloads, payment attempts, successful payments, and balance top-ups. All entries prefixed with `[AUDIT]` in Laravel logs.
- **Management:** RCS Agent/SMS SenderID registrations, Templates, API Connections, Email-to-SMS, and Number management.
- **Account:** Details, User/Access management, Sub Accounts, Audit Logs, and Security settings.
- **Support:** Dashboard, Ticket creation, and a Knowledge Base.
- **RCS Preview System:** A schema-driven renderer at `/rcs/preview-demo` providing an Android-style phone UI for previewing RCS messages, built with Alpine.js. The shared renderer (`public/js/rcs-preview-renderer.js`) now supports channel-specific rendering:
  - **Rich RCS:** Full card/carousel rendering with RCS agent header (logo, verified badge), rich input bar (emoji, camera, microphone)
  - **Basic RCS:** Agent header with verified badge, RCS-style purple gradient text bubble, full RCS input affordances
  - **SMS:** SenderID-only header (no agent branding), gray SMS-style text bubble, simplified input bar (text field + send button only)
- **RCS Asset Management:** Server-side image processing pipeline for RCS media. When users provide public image URLs and apply edits (zoom, crop, orientation), the system fetches images server-side, applies transformations using Intervention Image, compresses to meet 250KB limit, and stores on QuickSMS-hosted storage. Features:
  - **API Endpoints:** `/api/rcs/assets/process-url` (URL-based), `/api/rcs/assets/process-upload` (file uploads), `PUT /api/rcs/assets/{uuid}` (update edits), `POST /api/rcs/assets/{uuid}/finalize` (mark complete)
  - **SSRF Protection:** Validates all DNS records (A and AAAA), blocks private/reserved IP ranges, pins validated IPs for requests, disables redirects
  - **Storage:** Public filesystem disk `rcs-assets` at `storage/app/public/rcs-assets/` with date-based organization
  - **Model:** `RcsAsset` tracks UUID, source type/URL, hosted URL, dimensions, file size, edit parameters, draft status
  - **Frontend Integration:** Debounced edit calls, processing indicators, asset UUID tracking per card
  - **Explicit Save Workflow for URL Images:** URL-based images with edits (zoom, crop, orientation) require explicit save action before navigation. Dirty state tracking compares current values against baseline captured when URL is loaded or card is opened. Save button appears only when unsaved changes exist. Unsaved changes modal intercepts: card switching, wizard close, Apply to All Cards, and message type changes. Options: Save (processes image server-side, stores to CDN), Don't Save (restores baseline values and original URL), Cancel (stays on current card). Baseline is re-initialized after save or discard to ensure accurate tracking.
- **MessageLog Model:** (`app/Models/MessageLog.php`) Defines message structure with security features like encrypted content and role-based access.
- **Development Environment:** Utilizes SQLite for local development, separating UI from backend API integrations.
- **CSS Architecture:** Module-specific CSS overrides Fillow styles; custom classes use unique prefixes to prevent conflicts.
- **Role-Based Access Control:** A JavaScript-based placeholder system for viewer, analyst, and admin roles, controlling UI element visibility. Purchase Messages page enforces access: Admin (full access), Finance (purchase & invoices), Standard user (access denied with redirect to Dashboard).

## External Dependencies
- **PHP 8.1+ / Laravel 10:** Core backend framework.
- **Fillow SaaS Admin Template:** UI framework.
- **MetisMenu:** JavaScript library for navigation.
- **SQLite:** Database for local development.
- **Stripe PHP SDK (stripe/stripe-php):** Official Stripe SDK for Checkout Sessions, webhooks, and payment processing. Requires `STRIPE_SECRET_KEY` and optionally `STRIPE_WEBHOOK_SECRET` secrets.
- **HubSpot Products API:** External service for live product pricing (no caching). Requires `HUBSPOT_ACCESS_TOKEN` secret.
- **HubSpot Invoices API:** External service for invoice data. Fetches invoice details, line items, and PDF URLs directly from HubSpot CRM. No UI-side recalculation of totals.
- **Stripe Checkout:** Payment processing - portal redirects to Stripe, never handles card data (PCI DSS compliant). StripeService creates Checkout Sessions for invoice payments and balance top-ups. Webhook handler processes checkout.session.completed events.
- **Intervention Image (v3):** PHP image manipulation library for RCS asset processing. Uses GD driver for crop, zoom, resize, and JPEG compression.