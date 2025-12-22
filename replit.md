# QuickSMS Web Application

## Overview
QuickSMS is a Laravel-based SMS messaging platform with a comprehensive navigation shell. The application extends the Fillow SaaS Admin template as the authoritative design system. Currently implemented is the navigation structure with placeholder pages for all modules.

## Project Structure
- `app/Http/Controllers/QuickSMSController.php` - Main controller with all page methods
- `resources/views/layouts/quicksms.blade.php` - Thin wrapper extending Fillow default layout
- `resources/views/layouts/default.blade.php` - Fillow template default layout
- `resources/views/elements/quicksms-sidebar.blade.php` - QuickSMS navigation sidebar
- `resources/views/quicksms/placeholder.blade.php` - Generic placeholder for all pages
- `routes/web.php` - All route definitions
- `database/` - Database (uses SQLite)
- `public/` - Static assets (Fillow template CSS/JS)

## Architecture
QuickSMS extends the Fillow template using Laravel's Blade template inheritance:
1. `layouts.quicksms` extends `layouts.default` (Fillow's main layout)
2. `layouts.quicksms` overrides the `sidebar` section with QuickSMS navigation
3. Placeholder views extend `layouts.quicksms` and define `content` section
4. All Fillow assets (CSS, JS, MetisMenu) are reused from the parent layout

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
- Fillow SaaS Admin Template (Bootstrap 5)
- MetisMenu (collapsible sidebar navigation)
- SQLite database

## Current Status
- Navigation shell: Complete
- All routes (8 top-level + 26 sub-modules): Implemented
- All placeholder pages: Implemented
- Responsive sidebar with expand/collapse: Implemented (MetisMenu)
- Active route highlighting: Implemented
- Fillow template integration: Complete
- Business logic: Not yet implemented

## Recent Changes
- December 22, 2025: Refactored to extend Fillow template
  - QuickSMS layout now properly extends layouts.default
  - Created dedicated quicksms-sidebar.blade.php using MetisMenu
  - All pages use Fillow styling and asset pipeline
  - Disabled preloader for better development experience
- December 22, 2025: Initial QuickSMS navigation shell
  - Created QuickSMSController with all placeholder methods
  - Added dedicated routes for all top-level modules
  - All pages follow strict placeholder-only pattern
