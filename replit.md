# QuickSMS Web Application

## Overview
QuickSMS is a Laravel-based SMS messaging platform with a comprehensive navigation shell. Currently implemented is the navigation structure with placeholder pages for all modules.

## Project Structure
- `app/Http/Controllers/QuickSMSController.php` - Main controller with all page methods
- `resources/views/quicksms/` - QuickSMS Blade templates
  - `layout.blade.php` - Main layout with sidebar navigation
  - `placeholder.blade.php` - Generic placeholder for all pages
- `routes/web.php` - All route definitions
- `database/` - Database (uses SQLite)
- `public/` - Static assets

## Navigation Structure
Top-level modules with sub-modules (all with dedicated routes):
- **Dashboard** (/) - Main overview page
- **Messages** (/messages) - Send Message, Inbox, Campaign History
- **Contact Book** (/contacts) - All Contacts, Lists, Tags, Opt-Out Lists
- **Reporting** (/reporting) - Dashboard, Message Log, Finance Data, Invoices, Download Area
- **Purchase** (/purchase) - Credits and packages purchase
- **Management** (/management) - RCS Agent Registrations, SMS SenderID Registration, Templates, API Connections, Email-to-SMS, Numbers
- **Account** (/account) - Details, Users and Access, Sub Accounts, Audit Logs, Security Settings
- **Support** (/support) - Dashboard, Create a Ticket, Knowledge Base

## Placeholder Page Structure
Each page displays only:
- Page title
- Purpose description
- Sub-modules bullet list (for top-level modules with children)
- "Coming Soon" panel

## Running the Project
```bash
php artisan serve --host=0.0.0.0 --port=5000
```

## Key Technologies
- PHP 8.1+ / Laravel 10
- Bootstrap 5 (CDN)
- Bootstrap Icons (CDN)
- SQLite database

## Current Status
- Navigation shell: Complete
- All routes (8 top-level + 26 sub-modules): Implemented
- All placeholder pages: Implemented
- Responsive sidebar with expand/collapse: Implemented
- Active route highlighting: Implemented
- Business logic: Not yet implemented

## Recent Changes
- December 22, 2025: Completed QuickSMS navigation shell
  - Created QuickSMSController with all placeholder methods
  - Added dedicated routes for all top-level modules
  - Created responsive layout with collapsible sidebar
  - Top-level pages display sub-module bullet lists
  - All pages follow strict placeholder-only pattern
