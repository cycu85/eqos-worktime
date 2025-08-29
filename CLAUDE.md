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