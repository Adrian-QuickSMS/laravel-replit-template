# QuickSMS Web Application

## Overview
QuickSMS is a Laravel-based SMS messaging platform with a comprehensive navigation shell. The application extends the Fillow SaaS Admin template as the authoritative design system. Currently implemented is the navigation structure with placeholder pages for all modules.

## Project Structure
- `app/Http/Controllers/QuickSMSController.php` - Main controller with all page methods
- `resources/views/layouts/quicksms.blade.php` - Thin wrapper extending Fillow default layout
- `resources/views/layouts/default.blade.php` - Fillow template default layout
- `resources/views/elements/quicksms-sidebar.blade.php` - QuickSMS navigation sidebar
- `resources/views/quicksms/placeholder.blade.php` - Generic placeholder for all pages
- `resources/views/quicksms/contacts/all-contacts.blade.php` - All Contacts page (implemented)
- `resources/views/quicksms/contacts/lists.blade.php` - Lists page (implemented)
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
- **All Contacts page: Implemented (UI only, mock data)**
- **Lists page: Implemented (UI only, mock data)**
- Business logic: Not yet implemented

## All Contacts Page Features (UI Only)
- Table with contact rows: checkbox, initials avatar, name, email, mobile (masked), tags, lists, status
- Mobile number masking toggle (click to reveal/hide)
- Search bar with client-side filtering
- Collapsible filter panel (Status, Tags, Lists, Source, Date of Birth, Created Date)
- Custom fields filter placeholder with info text
- Bulk action bar with all spec actions (Add/Remove List, Add/Remove Tags, Send Message, Export, Delete)
- Row action dropdown menu (View, Edit, Send Message, Timeline, Delete)
- Pagination UI (static)
- **TODO markers placed for:**
  - Backend API integration (GET/PUT/DELETE /api/contacts endpoints)
  - Database persistence
  - Date range filter implementation
  - Custom field filters (dynamic based on defined custom fields)
  - Actual server-side filtering/sorting logic
  - Contact CRUD operations with validation
  - Send message functionality (integration with Messages module)
  - Bulk actions (list management, tagging, export, delete)
  - Activity timeline view
  - Permission checks for delete operations

## Lists Page Features (UI Only)
- Tabbed interface: Static Lists and Dynamic Lists
- **Static Lists:**
  - Table with list name, description, contact count, created/updated dates
  - Actions: View Contacts, Add Contacts, Rename, Delete
  - 3-step Create List wizard: Name & Description → Add Contacts → Confirm
  - Add contacts from Contact Book or via Filters
  - Rename modal with name and description fields
  - View contacts modal with remove functionality
- **Dynamic Lists (Rule-Based):**
  - Table with list name, rules display, contact count, last evaluated date
  - Actions: View Contacts, Edit Rules, Refresh Now, Delete
  - Create Dynamic List modal with rule builder
  - Rule fields: Status, Tag, List, Created Date, Postcode, Source
  - Rule operators: equals, not equals, contains, starts with, in last N days
  - Add/remove rules dynamically
  - Preview matching contacts
- **TODO markers placed for:**
  - Backend API integration (CRUD /api/lists endpoints)
  - Database persistence for lists and list membership
  - Dynamic list rule evaluation engine
  - Scheduled re-indexing for dynamic lists
  - Import/Campaign/API contact addition hooks

## Recent Changes
- December 22, 2025: Implemented All Contacts page UI
  - Created all-contacts.blade.php with Fillow table patterns
  - Added mock contact data in controller
  - Implemented client-side search, checkbox selection, mobile masking
  - Added bulk action bar and row action menus
  - All actions have TODO markers for future backend integration
- December 22, 2025: Refactored to extend Fillow template
  - QuickSMS layout now properly extends layouts.default
  - Created dedicated quicksms-sidebar.blade.php using MetisMenu
  - All pages use Fillow styling and asset pipeline
  - Disabled preloader for better development experience
- December 22, 2025: Initial QuickSMS navigation shell
  - Created QuickSMSController with all placeholder methods
  - Added dedicated routes for all top-level modules
  - All pages follow strict placeholder-only pattern
