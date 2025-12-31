# QuickSMS Web Application

## Overview
QuickSMS is a Laravel-based SMS messaging platform leveraging the Fillow SaaS Admin template for its design system. It aims to provide a comprehensive, user-friendly solution for managing messages, contacts, reporting, purchasing, and account administration, with a strong focus on a modern and intuitive UI for effective communication management. The project's ambition is to be a powerful and user-friendly platform for communication management.

## User Preferences
The user prefers detailed explanations when new concepts or features are introduced. The user wants the agent to prioritize developing the UI and front-end interactions before implementing backend logic. When making changes, the agent should clearly mark areas requiring backend integration with `TODO` comments. The user prefers an iterative development approach, focusing on completing one module's UI before moving to the next.

## System Architecture
The application is built on PHP 8.1+ and Laravel 10, using the Fillow SaaS Admin Template (Bootstrap 5) as its UI/UX foundation, with MetisMenu for navigation. The architecture employs Laravel's Blade templating, where `layouts.quicksms` extends `layouts.default` (Fillow's main layout) and overrides the sidebar for QuickSMS-specific navigation. All application pages extend `layouts.quicksms`, ensuring a consistent UI derived from Fillow, augmented with custom components.

**UI/UX and Design Decisions:**
- **Navigation:** Responsive sidebar with active route highlighting.
- **Data Tables:** Standardized patterns with client-side filtering, search, pagination, and bulk/row actions.
- **Forms & Modals:** Consistent use for creation, editing, and specific workflows (e.g., list creation, import, AI assistant).
- **Interactive Elements:** Mobile number masking, dynamic validation, real-time character counting, and visual feedback.
- **Content Editor:** Enhanced editor with personalization, emoji support, AI assistance, and channel-aware behavior.

**Core Feature Specifications:**
- **Dashboard:** Operational overview, RCS promotion tools, and support/notifications, using Fillow's widget-stat pattern.
- **Messages:** Send, Inbox, Campaign History.
- **Contact Book:** Management of contacts, static/dynamic lists, tags, and opt-out lists.
- **Reporting:** Dashboard, Message Log, Finance Data, Invoices, Download Area.
- **Purchase:** Credits and packages.
- **Management:** RCS Agent/SMS SenderID registrations, Templates, API Connections, Email-to-SMS, Numbers.
- **Account:** Details, User/Access management, Sub Accounts, Audit Logs, Security.
- **Support:** Dashboard, Ticket creation, Knowledge Base.

**Key Technical Implementations:**
- **Inbox / Conversation Workspace:** A three-panel interface at `/messages/inbox` with a conversation list (searchable, filterable, sortable, unread badges), a chat pane (displaying sent/received messages with channel-specific styling), and a collapsible contact info sidebar. Features a robust JavaScript-based filter system, rich conversation item layouts, and a reply composer with channel picker, template support, and AI assistance. Includes inline rendering for RCS rich cards.
- **RCS Preview System:** A schema-driven renderer at `/rcs/preview-demo` providing an Android-style phone UI for previewing RCS messages, including rich cards and carousels, with validation against RCS constraints. Uses Alpine.js for interactivity.
- **Message Log (Reporting):** A detailed history at `/reporting/message-log` with extensive filtering (date range, sub-account, user, origin, mobile number, SenderID, status, country, message type, message ID). Features a summary bar, a results table with default and optional columns (configurable via modal), content column security based on user roles, infinite scroll, and sortable columns.
- **MessageLog Model (`app/Models/MessageLog.php`):** Defines message structure with fields for ID, mobile number, sender_id, status, timestamps, cost, type, sub_account, user, origin, country, fragments, encoding, encrypted content, and billable flag. Includes security features (encrypted content at rest, masked mobile numbers, role-based content viewing) and query scopes for filtering.
- **Development Environment:** Utilizes an SQLite database for local development, with a clear separation between UI implementation and planned backend API integrations.
- **CSS Architecture:** Module-specific CSS (`public/css/quicksms-inbox.css`) is loaded after Fillow styles to override and customize UI components like message bubble colors, maintaining proper CSS specificity. Page-specific styles use `@push('styles')` which is rendered via `@stack('styles')` in `layouts.default` after Fillow's global CSS. Custom CSS classes must use unique prefixes (e.g., `.qs-dashboard-grid`, `.qs-tile`) to avoid conflicts with Fillow's built-in class names.
- **Reporting Dashboard Grid:** Uses CSS Grid with SortableJS for drag-to-reposition capability. Grid tiles use size classes (`.tile-small`, `.tile-medium`, `.tile-large`, `.tile-xlarge`, `.tile-full`) mapped to column spans. Layout order and sizes persist to localStorage. Responsive breakpoints adjust grid columns for smaller screens.
- **Reporting Dashboard Components:** Row 1: KPI tiles (Delivery Rate %, Spend, RCS Seen Rate conditional, Opt-out Rate conditional). Row 2: Volume Over Time line chart (SMS/RCS), Channel Split horizontal stacked bar. Row 3: Delivery Status Breakdown pie chart, Top 10 Countries vertical bar, Top SenderIDs table. Row 4: Peak Sending Time insight tile, Failure Reasons table. All charts use ApexCharts with mock API data.
- **Mock Reporting Data Service (`app/Services/MockReportingDataService.php`):** Generates 15,000 consistent seed messages with all filter dimensions. Provides filter-aware data aggregation for all dashboard widgets. Filter dimensions include: senderIds (QuickSMS, PROMO, ALERTS, INFO, NOTIFY, VERIFY, UPDATES, NEWS), subAccounts (Main Account, Marketing, Operations, Sales, Support), users (john.smith@company.com, jane.doe@company.com, admin@company.com, marketing@company.com), origins (API, Web UI, Scheduled, Email-to-SMS), groupNames (VIP Customers, Newsletter, Promotions, Alerts, General), countries (GB, US, DE, FR, IE, ES, IT, NL, BE, AU), channels (SMS, RCS), and statuses (delivered, pending, undelivered, expired, rejected). All API endpoints use this service for consistent data across the reporting module.
- **Filter-Aware API Endpoints (`app/Http/Controllers/Api/ReportingDashboardApiController.php`):** Provides 8 independent endpoints at `/api/reporting/dashboard/*` that accept filter query parameters (dateFrom, dateTo, senderIds[], subAccounts[], users[], origins[], groupNames[]). Endpoints: `/kpis`, `/volume`, `/channel-split`, `/delivery-status`, `/top-countries`, `/top-sender-ids`, `/peak-time`, `/failure-reasons`. Each endpoint filters data through MockReportingDataService before returning aggregated results. Routes defined in `routes/api.php`.
- **Drill-Through Interactions:** Dashboard tiles support click-through navigation to filtered views. Volume chart points → Campaign History (by date), Delivery Status pie segments → Message Log (by status), Country bars → Message Log (by country), SenderID rows → Message Log (by sender_id), Failure Reason rows → Message Log (by status=failed + reason), Opt-out tile → Opt-out List, Peak Time button → Send Message with schedule params. Uses existing routes with URL query parameters for filter pre-population.
- **Role-Based Access Control (Placeholder):** JavaScript-based role system with three roles: Viewer (cannot see cost, cannot export), Analyst (can see cost, can export), Admin (full access, can manage layout). Uses `data-requires-cost`, `data-requires-export`, `data-requires-admin` attributes for conditional rendering. Placeholder `currentUserRole` variable to be replaced with backend session data.
- **Service Layer (`ReportingService`):** Frontend JavaScript service with stub functions for backend integration. Each function accepts standard filters (dateRange, subAccount, user, origin, groupName, senderID) and returns a Promise. Designed for isolated tile loading (no blocking). 9 endpoints defined:
  - `getSummary()` → GET /reporting/summary (KPIs)
  - `getVolume()` → GET /reporting/volume (message volume over time)
  - `getDeliveryStatus()` → GET /reporting/status (delivery breakdown)
  - `getChannelSplit()` → GET /reporting/channel (SMS vs RCS)
  - `getTopCountries()` → GET /reporting/countries (top 10 countries)
  - `getTopSenderIds()` → GET /reporting/senderids (top sender IDs)
  - `getCost()` → GET /reporting/cost (spend data)
  - `getFailureReasons()` → GET /reporting/failure-reasons (failure breakdown)
  - `getOptOuts()` → GET /reporting/opt-outs (opt-out statistics)
  - `getPeakTime()` → GET /reporting/peak-time (peak sending insight)
  All functions include TODO comments for warehouse integration.

## External Dependencies
- **PHP 8.1+ / Laravel 10:** Core backend framework.
- **Fillow SaaS Admin Template:** UI framework based on Bootstrap 5.
- **MetisMenu:** JavaScript library for collapsible sidebar navigation.
- **SQLite:** Database for local development.