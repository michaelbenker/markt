# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Lokale Entwicklungsumgebung

Die App läuft unter MAMP für macOS unter der URL: https://markt:8890/

### Wichtige Hinweise für die Entwicklung:
- Verwende nur deutsche Sprache in der Kommunikation
- Die App ist für deutsche Märkte/Marktplätze entwickelt
- Alle Texte, Kommentare und Dokumentation sollten auf Deutsch sein

## Development Commands

### Core Development Workflow
- `composer dev` - Start full development environment (server + queue + logs + vite)
- `composer test` - Run PHPUnit tests (clears config first)
- `./vendor/bin/pint` - Format code with Laravel Pint
- `npm run dev` - Frontend development with Vite
- `npm run build` - Build frontend assets for production

### Database Operations
- `php artisan migrate:fresh --seed` - Reset database and run all seeders
- `php artisan db:seed` - Run database seeders only
- `php artisan make:migration name_der_migration` - Create new migration

### Custom Artisan Commands
- `php artisan anfragen:daily-summary` - Send daily request summary email
- `php artisan anfragen:daily-summary --test` - Send test email (first user only)
- `php artisan email-templates:create-defaults` - Create default email templates

### Testing
- `php artisan test` - Run all tests
- `vendor/bin/phpunit` - Alternative test runner
- Tests are located in `tests/Feature/` and `tests/Unit/`

### Deployment
- `./deploy.sh` - Deploy to production (builds assets, creates archive, uploads)
- `./remote-setup.sh` - Production server setup commands

## Architecture Overview

### Framework & Stack
- **Laravel 12** with **Filament 3.3** admin panel
- **Vite** for frontend build with **Tailwind CSS**
- **SQLite** for testing, likely MySQL/PostgreSQL for production
- **Queue system** using database driver
- **Email** system with template management

### Core Domain Models
- **Markt** (Market) - Main entity with termine (dates/events)
- **Aussteller** (Exhibitor) - Vendors/exhibitors
- **Anfrage** (Request) - Inquiries from potential exhibitors
- **Buchung** (Booking) - Confirmed bookings with protocol tracking
- **Stand** (Stand/Booth) - Physical market stands
- **Standort** (Location) - Market locations
- **Kategorie/Subkategorie** - Product categories
- **Leistung** (Service) - Additional services
- **Rechnung** (Invoice) - Billing with line items
- **EmailTemplate** - Configurable email templates

### Key Services
- **EmailTemplateService** - Template management and rendering
- **MailService** - Email sending with template support
- **RechnungService** - Invoice generation and PDF export

### Filament Admin Resources
All core models have Filament resources in `app/Filament/Resources/` with:
- List, Create, Edit, View pages
- Form components and table configurations
- Relation managers (e.g., TermineRelationManager for Markt)

### Email System
- **Mailables** in `app/Mail/` for different email types
- **Templates** stored in database via EmailTemplate model
- **Views** in `resources/views/emails/` with Blade templates
- **Notifications** for admin alerts (NeueAnfrageNotification)

### Queue & Background Jobs
- Daily summary emails sent via `SendDailyAnfragenSummary` command
- Email notifications for new requests
- Queue processing via `php artisan queue:work`

### Frontend Architecture
- **Blade templates** for public forms and emails
- **Filament** for admin interface
- **TailwindCSS** for styling
- **Custom components** in `resources/views/components/`

### Data Import/Export
- **Excel import** for Aussteller via `AusstellerImport`
- **PDF export** for invoices and bookings
- **Excel export** for Aussteller data

### File Organization
- **Models** - Core domain models in `app/Models/`
- **Controllers** - Public API in `app/Http/Controllers/`
- **Filament** - Admin interface in `app/Filament/`
- **Console** - Artisan commands in `app/Console/Commands/`
- **Views** - Blade templates in `resources/views/`
- **Routes** - Web routes in `routes/web.php`
- **Database** - Migrations and seeders in `database/`

### Testing Strategy
- **Feature tests** for HTTP endpoints and user flows
- **Unit tests** for service classes and model logic
- **Email testing** with test flags for safe development
- **Database** uses SQLite in-memory for tests

### Production Considerations
- **Cron jobs** for daily email summaries
- **PHP 8.4** requirement for production
- **Asset compilation** via Vite build process
- **Database caching** enabled for production performance