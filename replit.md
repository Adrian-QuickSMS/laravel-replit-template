# QuickSMS Web Application

## Overview
QuickSMS is a Laravel-based SMS messaging platform designed for comprehensive communication management. It provides a user-friendly solution for messages, contacts, reporting, purchasing, and account administration, leveraging the Fillow SaaS Admin template for a modern UI. The project aims to be a powerful and intuitive platform for effective communication.

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

**Technical Implementations & Feature Specifications:**
- **Dashboard:** Operational overview, RCS promotion, support, and notifications.
- **Messages:** Features for sending, an Inbox with a three-panel conversation workspace (conversation list, chat pane, contact info sidebar), and Campaign History. Inbox supports Rich RCS message rendering directly in the chat thread.
  - **Test Message Feature:** "Test Message" button on Send Message page (next to Continue) opens a modal to send the currently configured message to a phone number for preview before proceeding. Supports all channels (SMS, Basic RCS, Rich RCS), validates UK mobile format (07.../+44.../447...), includes inline success/error feedback, and uses the same message content/payload configured on Screen 1. API call structure documented as TODO.
- **Contact Book:** Management of contacts, lists (static/dynamic), tags, and opt-out lists.
- **Reporting:** Dashboard with customizable KPI tiles and charts (ApexCharts), detailed Message Log, Finance Data, Invoices (HubSpot API), and Download Area. Invoices page displays read-only HubSpot data, including a persistent financial summary and a detail drawer.
  - **Download Area:** Repository of previously generated exports with filters (Year, Month, Module, Sub-account multi-select, User) and a results table showing filename, module, format, size, generated/expires dates, status, and download/delete actions. Filters do NOT auto-apply; data refresh occurs only on "Apply Filters" click. Includes "Reset Filters" action. Supports bulk selection and deletion. Role-restricted to Admin, Finance, and Reporting/Analytics roles. No export logic - scaffold only with TODO markers for API integration.
- **Purchase:** Functionality for purchasing messages (Admin/Finance only) with HubSpot Products API for live pricing and Stripe for payment processing. Includes "Pay Invoice" and "Top Up Balance" flows with Stripe Checkout for PCI DSS compliance and comprehensive audit logging.
- **Management:** RCS Agent/SMS SenderID registrations, Templates, API Connections, Email-to-SMS, and Number management.
- **Account:** Details, User/Access management, Sub Accounts, Audit Logs, and Security settings.
- **Support:** Dashboard, Ticket creation, and Knowledge Base.
- **Unified RCS Wizard:** Shared fullscreen modal component (`rcs-wizard-modal.blade.php`) and JavaScript (`rcs-wizard.js`) for creating rich RCS messages, used across Send Message and Inbox pages.
- **RCS Preview System:** Schema-driven renderer (`/rcs/preview-demo`) with an Android-style phone UI for channel-specific previews (Rich RCS, Basic RCS, SMS). Supports mock RCS agents with brand icons.
- **RCS Asset Management:** Server-side image processing for RCS media, handling URL-based and uploaded images with transformations (zoom, crop, orientation) using Intervention Image. Features include SSRF protection, dedicated storage, `RcsAsset` model for tracking, and an explicit save workflow for URL images with interactive drag-to-position crop editor.
- **MessageLog Model:** Defines message structure with encrypted content and role-based access.
- **Development Environment:** Utilizes SQLite for local development.
- **CSS Architecture:** Module-specific CSS overrides Fillow styles with unique prefixes.
- **Role-Based Access Control:** JavaScript-based system for controlling UI visibility based on viewer, analyst, and admin roles, particularly enforced on the Purchase Messages page.

## External Dependencies
- **PHP 8.1+ / Laravel 10:** Core backend framework.
- **Fillow SaaS Admin Template:** UI framework.
- **MetisMenu:** JavaScript library for navigation.
- **SQLite:** Database for local development.
- **Stripe PHP SDK (stripe/stripe-php):** For Checkout Sessions, webhooks, and payment processing.
- **HubSpot Products API:** For live product pricing.
- **HubSpot Invoices API:** For fetching invoice data.
- **Stripe Checkout:** For secure payment processing.
- **Intervention Image (v3):** PHP image manipulation library for RCS asset processing.