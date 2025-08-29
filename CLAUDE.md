# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

EQOS WorkTime is a Laravel-based work time management system for tracking employee tasks, vehicles, and time logging. The application uses MySQL database and is designed with a role-based access system (Admin, Kierownik/Manager, Lider/Leader).

## Development Commands

### Backend (Laravel/PHP)
- `composer install` - Install PHP dependencies
- `composer dev` - Start all development services (Laravel serve, queue, logs, vite)
- `composer test` - Run PHPUnit tests
- `php artisan serve` - Start Laravel development server (http://localhost:8000)
- `php artisan migrate` - Run database migrations
- `php artisan migrate:fresh --seed` - Fresh migration with sample data
- `php artisan make:model ModelName -m` - Create model with migration
- `php artisan make:controller ControllerName` - Create controller
- `php artisan tinker` - Laravel REPL for testing

### Frontend (Vite/JavaScript)
- `npm install` - Install Node.js dependencies
- `npm run dev` - Start Vite development server
- `npm run build` - Build production assets

### Testing
- `php artisan test` - Run all tests
- `php artisan test --filter TestName` - Run specific test
- `vendor/bin/phpunit tests/Unit/SpecificTest.php` - Run specific test file

### Code Quality
- `vendor/bin/pint` - PHP CS Fixer (Laravel Pint)
- `vendor/bin/phpstan analyze` - Static analysis (if installed)

## Architecture

### Database Structure
Core tables:
- `users` - User management with roles (admin, kierownik, lider)
- `vehicles` - Vehicle/equipment registry
- `tasks` - Work task tracking with time logging
- `activity_log` - Audit trail for all actions

### Key Models & Relationships
- `User` model handles authentication and role-based permissions
- `Vehicle` model for equipment/vehicle management
- `Task` model with relationships to User (leader) and Vehicle
- Role-based middleware controls access to different features

### Frontend Technology
- Bootstrap 5 for responsive UI
- Blade templates with server-side rendering
- Vite for asset compilation
- TailwindCSS v4 integration

### Authentication & Authorization
- Laravel Breeze for authentication scaffolding
- Role-based access control via middleware
- Three main roles: admin, kierownik (manager), lider (leader)

## Environment Setup

### Database Configuration
- Uses MySQL 8.0+ (configured in .env)
- Database name: `eqos_worktime`
- Requires MySQL service to be running
- SQLite fallback available for development

### Required Services
- PHP 8.3+
- MySQL 8.0+
- Node.js 18+ (for asset compilation)
- Composer for PHP dependencies

## Development Workflow

### Branch Strategy
- `main` - Production ready code
- `develop` - Integration branch
- `feature/feature-name` - New features
- `hotfix/fix-name` - Production fixes

### Commit Conventions
- `feat:` - New features
- `fix:` - Bug fixes
- `docs:` - Documentation updates
- `style:` - Code formatting
- `refactor:` - Code refactoring
- `test:` - Test additions/updates

## Key Files & Locations

### Configuration
- `.env` - Environment variables (not in git)
- `.env.example` - Environment template
- `config/` - Laravel configuration files
- `composer.json` - PHP dependencies and scripts

### Application Structure
- `app/Models/` - Eloquent models
- `app/Http/Controllers/` - Application controllers
- `app/Http/Middleware/` - Custom middleware
- `database/migrations/` - Database schema migrations
- `database/seeders/` - Sample data seeders
- `resources/views/` - Blade templates
- `resources/js/` & `resources/css/` - Frontend assets
- `routes/web.php` - Web routes definition

### Testing
- `tests/Feature/` - Feature tests
- `tests/Unit/` - Unit tests
- `phpunit.xml` - PHPUnit configuration

## Deployment Notes

- Uses MySQL in production
- Vite builds assets for production
- Laravel's built-in session and cache drivers
- Designed for shared hosting compatibility
- Environment-specific configurations via .env files

## Recent Development Session Summary (2025-08-29)

### UI/UX Improvements Completed:

#### 1. Dark Mode Form Field Fixes
- **Problem**: Form fields in dark mode had poor visibility (white backgrounds with light gray text)
- **Solution**: Updated `form-kt-control` and `form-kt-select` classes in `resources/css/metronic.css`:
  - Changed `dark:bg-gray-800` to `dark:bg-gray-900` (darker background)
  - Changed `dark:text-gray-100` to `dark:text-gray-300` (better contrast)
  - Updated `dark:border-gray-600` to `dark:border-gray-700` (consistent borders)

#### 2. Light/Dark Mode Toggle Implementation
- **Added**: Toggle button in navigation topbar (both desktop and mobile versions)
- **Features**:
  - Sun/moon icons that switch based on current theme
  - Saves preference in localStorage
  - Auto-detects system preference on first visit
  - JavaScript functionality in `resources/views/layouts/app.blade.php`
- **Configuration**: Added `darkMode: 'class'` to `tailwind.config.js` (critical for functionality)

#### 3. Task Edit Form Enhancement
- **Problem**: Task edit form had simple text field for team members (inconsistent with create form)
- **Solution**: Implemented advanced team selection UI matching create form:
  - Modal with checkbox interface for selecting team members
  - Visual tags display for selected members
  - Auto-population of leader's team members
  - Updated `TaskController@edit` to load users and team data
  - Unified all form fields to use Metronic CSS classes (`form-kt-control`, `form-kt-select`)

#### 4. Vehicle List Filtering and Sorting
- **Problem**: No filtering or sorting capabilities in vehicles list
- **Solution**: Comprehensive filtering and sorting system:
  - **Search**: By name, registration, or description
  - **Status filter**: Active/inactive/all vehicles
  - **Sorting**: By all columns (name, registration, status, task count, date)
  - **Clickable headers**: With sort direction indicators (arrows)
  - **Results info banner**: Shows applied filters and result count
  - **Pagination**: Preserves all query parameters
  - **Responsive design**: Form adapts to screen size

### Technical Implementation Details:

#### Files Modified:
1. `resources/css/metronic.css` - Fixed dark mode form styling
2. `resources/views/layouts/navigation.blade.php` - Added theme toggle buttons
3. `resources/views/layouts/app.blade.php` - Added JavaScript for theme switching
4. `tailwind.config.js` - Enabled class-based dark mode
5. `app/Http/Controllers/TaskController.php` - Enhanced edit method with team data
6. `resources/views/tasks/edit.blade.php` - Complete form redesign with modal
7. `app/Http/Controllers/VehicleController.php` - Added filtering and sorting logic
8. `resources/views/vehicles/index.blade.php` - Added filter form and sortable headers

#### Deployment Commands for Server:
```bash
git pull origin master
npm run build  # Required after CSS/JS changes
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### Known Issues to Address Next Session:

1. **Timezone Problem**: Application shows incorrect time despite proper timezone configuration
   - Need to investigate Laravel timezone settings vs database timezone
   - Check `config/app.php` timezone setting
   - Verify MySQL timezone configuration
   - Review datetime handling in models and views

### Development Status:

- ✅ Dark mode visibility issues resolved
- ✅ Theme toggle fully functional
- ✅ Task edit form enhanced with team selection
- ✅ Vehicle list filtering and sorting implemented
- 🔄 Timezone issue pending resolution
- 📋 All changes committed and pushed to GitHub

### Next Session Focus:
1. Fix timezone/time display issues
2. Continue with any remaining UI/UX improvements
3. Address any deployment or server-specific issues