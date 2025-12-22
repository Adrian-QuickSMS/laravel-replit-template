# QuickSMS Web Application

## Overview
QuickSMS is a Laravel-based SMS messaging platform with a comprehensive navigation shell. Currently implemented is the navigation structure with placeholder pages for all modules.

## Project Structure
- `app/Http/Controllers/QuickSMSController.php` - Main controller with all page methods
- `resources/views/quicksms/` - QuickSMS Blade templates
  - `layout.blade.php` - Main layout with sidebar navigation
  - `dashboard.blade.php` - Dashboard page
  - `placeholder.blade.php` - Generic placeholder for all other pages
- `routes/web.php` - All route definitions
- `database/` - Database (uses SQLite)
- `public/` - Static assets

## Navigation Structure
Top-level modules with sub-modules:
- **Dashboard** - Main overview page
- **Messages** - Send Message, Inbox, Campaign History
- **Contact Book** - All Contacts, Lists, Tags, Opt-Out Lists
- **Reporting** - Dashboard, Message Log, Finance Data, Invoices, Download Area
- **Purchase** - Credits and packages purchase
- **Management** - RCS Agent Registrations, SMS SenderID Registration, Templates, API Connections, Email-to-SMS, Numbers
- **Account** - Details, Users and Access, Sub Accounts, Audit Logs, Security Settings
- **Support** - Dashboard, Create a Ticket, Knowledge Base

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
- All routes: Implemented
- All placeholder pages: Implemented
- Business logic: Not yet implemented

## Recent Changes
- December 22, 2025: Implemented QuickSMS navigation shell
  - Created QuickSMSController with all placeholder methods
  - Created responsive layout with collapsible sidebar
  - Implemented all routes for navigation items
  - Created placeholder pages for all modules
