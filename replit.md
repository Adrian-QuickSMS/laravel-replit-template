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
- **Dashboard:** Main overview.
- **Messages:** Send Message, Inbox, Campaign History.
- **Contact Book:** All Contacts, Lists (Static & Dynamic), Tags, Opt-Out Lists.
- **Reporting:** Dashboard, Message Log, Finance Data, Invoices, Download Area.
- **Purchase:** Credits and packages.
- **Management:** RCS Agent Registrations, SMS SenderID Registration, Templates, API Connections, Email-to-SMS, Numbers.
- **Account:** Details, Users and Access, Sub Accounts, Audit Logs, Security Settings.
- **Support:** Dashboard, Create a Ticket, Knowledge Base.

The system uses an SQLite database for development, with a clear separation between UI implementation and planned backend API integrations.

## External Dependencies
- **PHP 8.1+ / Laravel 10:** Core backend framework.
- **Fillow SaaS Admin Template:** UI framework based on Bootstrap 5.
- **MetisMenu:** JavaScript library for collapsible sidebar navigation.
- **SQLite:** Database for local development.