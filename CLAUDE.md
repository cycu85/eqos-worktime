# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Communication Language
**IMPORTANT**: Always communicate in Polish when working on this project. All responses, explanations, and discussions should be in Polish language.

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

## Development Session Summary (2025-11-24)

### Integracja z zewnętrzną bazą danych ASEK

#### 1. Drugie połączenie MySQL (mysql_external)
- **Cel**: Odczyt danych z zewnętrznej bazy ASEK (zestawy asekuracyjne)
- **Konfiguracja**: Nowe zmienne w `.env` z prefiksem `DB_EXTERNAL_*`
- **Ograniczenia**: Tylko odczyt, charset `utf8` (starsza baza)

#### 2. Nowe modele
- `AsekZestaw` - Model dla tabeli `asek_zestawy` (zestawy narzędzi)
- `AsekTicket` - Model dla tabeli `asek_ticket` (elementy zestawów)
- Relacja: AsekZestaw hasMany AsekTicket (przez pole `zestaw_id`)

#### 3. Nowe widoki i trasy
- `/asek/zestawy` - Lista zestawów z filtrowaniem i sortowaniem
- `/asek/zestawy/{id}` - Szczegóły zestawu z listą elementów

#### 4. Integracja z profilem użytkownika
- Metoda `User::getAsekZestawy()` wyszukuje zestawy po polu `who_use`
- Obsługa formatów: "Imię Nazwisko" oraz "Nazwisko Imię"
- Wyświetlanie zestawów w widoku `users/show.blade.php`

#### 5. Logika przeglądów
- Kolumna "Przegląd" sprawdza pole `type_calib`:
  - Jeśli "Wymaga" → wyświetla datę z kolorowym znacznikiem
  - W przeciwnym razie → "Nie wymaga"
- Kolorowanie dat: zielony (OK), żółty (<30 dni), czerwony (przeterminowany)

#### Pliki utworzone/zmodyfikowane:
- `config/database.php` - połączenie mysql_external
- `.env.example` - zmienne DB_EXTERNAL_*
- `app/Models/AsekZestaw.php` - model zestawu
- `app/Models/AsekTicket.php` - model elementu
- `app/Http/Controllers/AsekZestawController.php` - kontroler
- `resources/views/asek/zestawy/index.blade.php` - lista zestawów
- `resources/views/asek/zestawy/show.blade.php` - szczegóły zestawu
- `resources/views/users/show.blade.php` - sekcja zestawów ASEK
- `app/Http/Controllers/UserController.php` - pobieranie zestawów
- `app/Models/User.php` - metoda getAsekZestawy()
- `routes/web.php` - trasy /asek/*

## Development Session Summary (2026-02-06)

### Refaktoryzacja modułu Finanse - Cennik z datami obowiązywania

#### 1. Zmiana podejścia do cen
- **Poprzednie podejście**: Cena bezpośrednio w tabeli `task_types` (pole `value`)
- **Nowe podejście**: Osobna tabela `task_type_prices` z datami obowiązywania cen

#### 2. Nowa tabela task_type_prices
- **Pola**:
  - `id` - klucz główny
  - `task_type_id` - FK do task_types (ON DELETE CASCADE)
  - `price` - cena (decimal 10,2)
  - `valid_from` - data od kiedy cena obowiązuje
  - `created_at`, `updated_at` - timestampy
- **Indeks**: `(task_type_id, valid_from)` dla szybkiego wyszukiwania

#### 3. Nowy model TaskTypePrice
- Relacja `belongsTo(TaskType::class)`
- Metoda statyczna `getPriceForDate($taskTypeId, $date)` - zwraca aktualną cenę na dany dzień
- Model TaskType rozszerzony o relację `hasMany(TaskTypePrice::class)`

#### 4. Nowy kontroler PriceListController
- **Trasy**:
  - `GET /finanse/cennik` - lista cen
  - `POST /finanse/cennik` - dodanie ceny
  - `PUT /finanse/cennik/{price}` - edycja ceny
  - `DELETE /finanse/cennik/{price}` - usunięcie ceny
- **Dostęp**: role admin, kierownik, ksiegowy

#### 5. Aktualizacja FinanceController
- Zapytanie używa podzapytania do znalezienia aktualnej ceny:
  ```sql
  SELECT price FROM task_type_prices
  WHERE task_type_id = tasks.task_type_id
    AND valid_from <= task_work_logs.work_date
  ORDER BY valid_from DESC
  LIMIT 1
  ```
- Obliczenia uwzględniają cenę obowiązującą na dzień wykonania pracy (work_date)

#### 6. Submenu w nawigacji
- Menu "Finanse" przekształcone w dropdown:
  - "Lista finansowa" → `/finanse`
  - "Cennik" → `/finanse/cennik`
- Implementacja dla desktop i mobile (responsive)

#### 7. Czyszczenie kodu
- Usunięto pole `value` z tabeli `task_types` (migracja remove_value_from_task_types_table)
- Usunięto pole `value` z modelu TaskType
- Usunięto walidację `value` z TaskTypeController
- Usunięto kolumnę "Wartość za szt." z widoku task-types/index.blade.php
- Usunięto pole value z modali dodawania/edycji typu zadania

#### 8. Export do Excel
- Nowa klasa `FinanceExport` wzorowana na `TaskExport`
- Metoda `export()` w FinanceController
- Trasa `GET /finanse/export/excel`
- Przycisk "Export do Excel" na widoku listy finansowej
- Export zachowuje wszystkie aktywne filtry (daty, rodzaj zadania, zespół)

#### Pliki utworzone/zmodyfikowane:
- `database/migrations/2026_02_06_110126_create_task_type_prices_table.php` - nowa tabela
- `database/migrations/2026_02_06_110138_remove_value_from_task_types_table.php` - usunięcie value
- `app/Models/TaskTypePrice.php` - nowy model
- `app/Models/TaskType.php` - usunięcie value, dodanie relacji prices()
- `app/Http/Controllers/PriceListController.php` - nowy kontroler
- `app/Http/Controllers/FinanceController.php` - podzapytanie do cen, metoda export()
- `app/Http/Controllers/TaskTypeController.php` - usunięcie value z walidacji
- `app/Exports/FinanceExport.php` - nowa klasa exportu
- `resources/views/finanse/price-list/index.blade.php` - nowy widok cennika
- `resources/views/finanse/index.blade.php` - przycisk Export do Excel
- `resources/views/layouts/navigation.blade.php` - submenu dropdown
- `resources/views/settings/task-types/index.blade.php` - usunięcie kolumny value
- `routes/web.php` - nowe trasy dla cennika i exportu