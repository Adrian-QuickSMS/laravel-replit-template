# QuickSMS Web Application

## Overview
QuickSMS is a Laravel-based SMS messaging platform designed to provide a comprehensive messaging solution. It integrates a robust navigation shell and extends the Fillow SaaS Admin template as its authoritative design system. The project aims to offer a full suite of features for managing messages, contacts, reporting, purchasing, and account administration, with a strong focus on a modern and intuitive user interface. The ambition is to create a powerful, user-friendly platform for effective communication management.

## User Preferences
The user prefers detailed explanations when new concepts or features are introduced. The user wants the agent to prioritize developing the UI and front-end interactions before implementing backend logic. When making changes, the agent should clearly mark areas requiring backend integration with `TODO` comments. The user prefers an iterative development approach, focusing on completing one module's UI before moving to the next.

## System Architecture
The application is built on PHP 8.1+ and Laravel 10, utilizing the Fillow SaaS Admin Template (Bootstrap 5) for its UI/UX foundation. MetisMenu provides responsive, collapsible sidebar navigation. The system architecture leverages Laravel's Blade templating engine for inheritance: `layouts.quicksms` extends `layouts.default` (Fillow's main layout) and overrides the sidebar with QuickSMS-specific navigation. All application pages then extend `layouts.quicksms`. The UI design emphasizes a consistent look and feel derived from the Fillow template, with custom components for specific functionalities like contact management, list creation, tag organization, and opt-out suppression.

Key UI/UX decisions include:
- **Navigation:** A comprehensive, responsive sidebar with active route highlighting.
- **Data Tables:** Standardized table patterns with client-side filtering, search, pagination, and bulk/row actions.
- **Forms & Modals:** Consistent use of modals for creation, editing, and specific workflows (e.g., list creation wizard, import wizard, AI content assistant).
- **Interactive Elements:** Features like mobile number masking, dynamic field validation, real-time character counting, and visual feedback for user actions.
- **Content Editor:** An enhanced text editor with personalization, emoji picker, AI assistance, and channel-aware behavior (GSM-7/Unicode detection).

Feature specifications include:
- **Dashboard:** Main overview with three sections (Operational Overview, RCS Promotion & Tools, Support & Notifications).
  - **Operational Overview:** Uses Fillow's widget-stat pattern with proper circular icons (85px circles, 30px SVGs):
    - Row 1 (4 tiles, col-xl-3): Balance ($ icon, bgl-primary), Inbound (envelope, bgl-warning), Messages Sent Today (send, bgl-success), Delivery Rate (%, bgl-info)
    - Row 2 (2 tiles, left-justified): Make a Payment (bg-secondary), Buy Number (bg-primary)
  - All tiles follow Fillow's `.media.ai-icon` structure without inline style overrides. Loading/error states and mock API with random delays for testing
- **Messages:** Send Message, Inbox, Campaign History.
- **Contact Book:** All Contacts, Lists (Static & Dynamic), Tags, Opt-Out Lists.
- **Reporting:** Dashboard, Message Log, Finance Data, Invoices, Download Area.
- **Purchase:** Credits and packages.
- **Management:** RCS Agent Registrations, SMS SenderID Registration, Templates, API Connections, Email-to-SMS, Numbers.
- **Account:** Details, Users and Access, Sub Accounts, Audit Logs, Security Settings.
- **Support:** Dashboard, Create a Ticket, Knowledge Base.

The system uses an SQLite database for development, with a clear separation between UI implementation and planned backend API integrations.

## Inbox / Conversation Workspace
The Messages Inbox provides a three-panel conversation workspace at `/messages/inbox`:
- **Conversation List (Left):** Uses Fillow's `.chat-bx` class pattern with purple left border (3px) for active state, search, filter (All/Unread/SMS/RCS), source filter (VMN/Short Code/RCS Agent), sort (Newest/Oldest/A-Z/Unread First), unread badges, and channel pills (`.channel-pill-sms` green #34C759, `.channel-pill-rcs` blue #007AFF)
- **Filter System Architecture:** Complete JavaScript filter implementation with:
  - Normalized conversation data structure: `{ id, contactName, phoneNumber, phoneMasked, initials, contactId, type (SMS/RCS), source, sourceId, unread, unreadCount, lastMessageText, lastMessageTime, lastMessageDate, messages }`
  - Global filter state object: `inboxFilters = { status, source, search, sort }`
  - Core functions: `applyFilters()`, `matchesStatus()`, `matchesSource()`, `matchesSearch()`, `sortConversations()`, `renderConversationList()`, `createConversationHTML()`, `updateUnreadBadge()`, `resetFilters()`
  - Event listeners with debounced search (150ms) for all filter controls
  - Combined filtering: status + source + search (AND logic)
  - Search matches both contact names AND phone numbers (digit-only matching for flexible phone search)
  - Dynamic re-rendering of conversation list when filters change
  - Preserves selected conversation state after filtering (auto-selects first result if current selection is filtered out)
  - Empty state with "Clear Filters" button when no results match
- **Filters & Sorting:** Fully functional with 35 mock conversations. Filters work in combination (channel + source + search). Sort uses numeric timestamps for newest/oldest ordering. Console logging for debugging.
- **Conversation Item Layout:** Flex layout with 6px gaps, contact name truncates at 120px, time/unread badge right-aligned. SMS/RCS pills use `badge rounded-pill channel-pill-*` classes (10px font, 3px×8px padding, 50rem radius).
- **Chat Pane (Center):** Uses `.message-received` (gray, left-aligned) and channel-specific `.message-sent` bubbles (green gradient for SMS, blue gradient for RCS) with timestamps and delivery indicators. Both bubble types use max-width: 65% for consistent width. Source display in header ("From: [VMN/Short Code/RCS Agent]")
- **Contact Info (Right):** Collapsible sidebar toggled via three-dot menu, shows contact details (tags, lists, notes) with "+ Add" links, "View Contact" button for modal preview, or "Add to Contacts" for unknown numbers. Notes section with add/save functionality and user attribution timestamps
- **Search in Conversation:** Header search bar with previous/next navigation and highlight matching (`.search-highlight`)
- **Reply Composer:** Card matching Send Message UI with channel picker (SMS only, Basic RCS, Rich RCS), conditional sender controls (SenderID for SMS, RCS Agent for RCS), Template dropdown + "Improve with AI" button, content editor with placeholder modal, AI assistant modal, emoji picker modal, character/encoding/segment counters with GSM-7/Unicode detection
- **RCS Rich Cards:** Inline rendering of rich card messages (`.rcs-rich-card-inbox`) with image, title, description, action button
- **Mock Data:** 35 sample conversations with varied channels, sources, timestamps, unread states for filter/sort testing
- **GDPR Compliance:** Phone numbers are masked (+44 77** ***111), message previews use placeholders only (@{{firstName}}, @{{orderNumber}})
- **Navigation Badge Sync:** Header inbox icon (envelope) navigates directly to `/messages/inbox`. The red badge (#navInboxBadge) shows unread count synchronized via `updateUnreadCount()` JS function when conversations are marked read/unread. All unread badges use consistent `bg-danger text-white` styling.

## RCS Preview System
The application includes a schema-driven RCS message preview renderer at `/rcs/preview-demo`:
- **Phone Frame:** Android-style phone UI with status bar, agent header (logo, verified badge, name, tagline), chat area, and input bar
- **Rich Cards:** Support for short, medium, and tall media heights with title, description, and up to 4 action buttons
- **Carousels:** Horizontal scrolling card galleries with medium/small card widths and dot indicators
- **Validation:** Enforces RCS constraints (max 10 carousel cards, max 4 buttons, character limits)
- **Design Tokens:** CSS variables derived from Google RCS specifications for consistent styling
- **Alpine.js Controller:** Interactive example selector with live JSON payload display
- **Sample Payloads:** 5 example message types demonstrating rich card and carousel variations

## Message Log (Reporting)
The Message Log page at `/reporting/message-log` provides detailed message history with comprehensive filtering:
- **Filters (in order):**
  1. Date Range Picker with presets (Today, Yesterday, Last 7 Days, Last 30 Days, This Month, Last Month)
  2. Sub Account (multi-select dropdown)
  3. User (multi-select dropdown)
  4. Origin (multi-select: Portal / API / Email-to-SMS / Integration)
  5. Mobile Number (free text, multi-value with Enter key)
  6. SenderID (free text with predictive suggestions)
  7. Message Status (multi-select: Delivered / Pending / Undeliverable / Rejected)
  8. Country (multi-select dropdown)
  9. Message Type (multi-select: SMS / RCS Basic / RCS Rich)
  10. Message ID (free text, multi-value with Enter key)
- **Filter Behavior:** Filters do NOT auto-apply; changes only apply when clicking "Apply Filters". Reset Filters only resets state without applying.
- **Filter State:** Uses `pendingFilters` for UI state and `filterState` for applied filters.
- **Summary Bar:** Dashboard-style metric tiles (Total Messages, Total Parts/Fragments) - hidden until filters applied
- **Results Table:**
  - **Default Columns:** Mobile Number (masked), SenderID, Message Status, Sent Time, Delivery Time, Completed Time, Cost
  - **Optional Columns:** Message Type (SMS/RCS), Sub Account, User, Origin, Country, Fragments/Parts, Encoding (GSM-7/Unicode), Message ID, Content (security controlled)
  - **Column Settings Modal:** Full modal panel with Default Columns and Optional Columns sections, checkboxes to show/hide, security badge for Content column, saved to localStorage, Reset to Default button
  - **Content Column Security:**
    - Hidden by default (not in defaultColumns.visible)
    - Super Admin role: Shows plaintext content (truncated to 50 chars with full content in tooltip)
    - Non-authorized users: Shows lock icon + "••••••••" masked placeholder
    - Uses `canViewMessageContent()` function with `currentUserRole` variable
    - TODO: Backend integration required - replace mock role with `Auth::user()->role` or API permission check
  - **Table Features:** Sticky header, hover row highlight, scrollable container (500px max-height), 10,000 row hard cap
  - **Infinite Scroll:** Loads 50 rows per batch on scroll, shows loading spinner, respects MAX_ROWS limit
  - **Sortable Columns:** Status and Sent Time have sort dropdown menus
- **Row Actions:** 3-dot kebab menu on each row with:
  - View Details (opens placeholder modal with all message fields)
  - Copy Message ID (copies to clipboard with toast notification)
  - Copy Mobile Number (copies unmasked number to clipboard)
- **Toast Notifications:** Success/error feedback for copy actions using Bootstrap toast component
- **Mock API Layer:** Client-side data generation for development/testing:
  - `MockAPI` object with weighted random data generators
  - `fetchMessages(filters, page, limit)` returns ~50 rows per page with simulated 200-500ms delay
  - Weighted distributions: 70% Delivered, 60% SMS, 80% GSM-7 encoding
  - Mock data includes: statuses, senders, origins, message types, sub accounts, users, encodings, countries
  - Total mock count: 1,247 messages
  - Apply Filters triggers fresh mock request with `loadMessages(true)`
  - Reset Filters clears UI state (pendingFilters) but does NOT trigger data reload
  - Infinite scroll loads next page on scroll-to-bottom (100px threshold)
  - Console logging for debugging: `[Mock API] Fetched page X, Y rows (filters: {...})`
- **Export Section:**
  - Three format options: CSV (primary), Excel (success), TXT (secondary)
  - Progress indicator with spinner during export
  - Exports respect applied filters and selected columns
  - Info text with link to Download Area for large async exports
  - TODO: Backend async export with Download Centre handoff (POST /api/messages/export)

## CSS Architecture Notes
- **Inbox Module:** Uses `public/css/quicksms-inbox.css` loaded after Fillow styles to override message bubble colors with channel-specific gradients (SMS green, RCS blue)
- **CSS Specificity:** Inbox overrides use `.chat-box-area .message-sent p` and `.chat-box-area .media .message-sent p` selectors to match Fillow's specificity

## External Dependencies
- **PHP 8.1+ / Laravel 10:** Core backend framework.
- **Fillow SaaS Admin Template:** UI framework based on Bootstrap 5.
- **MetisMenu:** JavaScript library for collapsible sidebar navigation.
- **SQLite:** Database for local development.