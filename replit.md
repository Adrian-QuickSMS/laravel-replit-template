# Fillow Laravel Admin Dashboard

## Overview
This is a Laravel 10 admin dashboard template (Fillow) with a Bootstrap 5 frontend. It provides a comprehensive admin panel UI with multiple pages including dashboards, charts, forms, tables, and various UI components.

## Project Structure
- `app/` - Laravel application code (Controllers, Models, Middleware)
- `config/` - Configuration files including `dz.php` for theme settings
- `database/` - Database migrations and seeders (uses SQLite)
- `public/` - Static assets (CSS, JS, images, vendor libraries)
- `resources/views/` - Blade templates
- `routes/` - Route definitions

## Running the Project
The application runs on port 5000 using Laravel's built-in development server:
```bash
php artisan serve --host=0.0.0.0 --port=5000
```

## Database
- Uses SQLite database located at `database/database.sqlite`
- Default Laravel migrations have been applied (users, password resets, failed jobs, personal access tokens)

## Key Dependencies
- PHP 8.1+
- Laravel 10
- Bootstrap 5
- jQuery
- Various JavaScript plugins (ApexCharts, Chart.js, DataTables, etc.)

## Recent Changes
- December 22, 2025: Initial setup for Replit environment
  - Configured SQLite database
  - Added trusted proxies for Replit proxy environment
  - Added inline preloader timeout for reliability
