# Techstacks Logify

An HR and payroll management system built with **Laravel 12** and **React 19** (Inertia v3), featuring role-based access for Administrators, HR Personnel, and Employees.

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP ^8.2, Laravel 12, Inertia Laravel ^3.3 |
| Frontend | React 19, @inertiajs/react, Vite 7 |
| Styling | Tailwind CSS v4, FlyonUI 2.4 |
| Icons | Iconify (`@iconify-json/ph`, `@iconify-json/tabler`) |
| PDF | barryvdh/laravel-dompdf |
| Storage | Local disk / AWS S3 (`league/flysystem-aws-s3-v3`) |
| Auth tokens | firebase/php-jwt |
| Mail | Mailtrap (`railsware/mailtrap-php`) |

## Features

- **Role-based access** — Admin, HR, and Employee roles with granular permissions
- **Employee management** — profiles, employment status, archiving
- **Attendance** — manual payroll attendance encoding and employee attendance tracking with calendar views (FullCalendar)
- **Payroll** — payroll periods, payslip generation (PDF)
- **Government contributions** — SSS, PhilHealth, Pag-IBIG tracking
- **Work requests** — submission, approval workflow, pending/archived states
- **Financial requests** — cash-related requests with approval flow
- **Audit logs** — action history per user
- **Global search** — command-palette modal (`Ctrl+/` or click the navbar search) searching pages and records
- **Notifications** — in-app notification dropdown with mark-as-read
- **Theme switching** — multiple UI themes persisted per user

## Requirements

- PHP >= 8.2
- Composer
- Node.js >= 20
- MySQL (or any database supported by Laravel)

## Installation

```bash
# Install PHP dependencies
composer install

# Install Node dependencies
npm install

# Configure environment
cp .env.example .env
php artisan key:generate

# Set your database credentials in .env, then:
php artisan migrate --seed

# Build frontend assets (or run the dev server)
npm run build
npm run dev

# Serve the application
php artisan serve
```

> **Note:** If your local PHP version differs from `composer.json` requirements, use:
> `composer install --prefer-dist --ignore-platform-req=php`

## Development Notes

### Iconify icons

Icons are rendered as literal class strings like `icon-[tabler--home]`. Only icon names present in the manifest at `resources/js/Config/iconify.js` are compiled into CSS by Tailwind. When adding a new icon anywhere in JSX, add it to the manifest first, otherwise it will silently not render.

### Themes

Themes are applied client-side via `data-theme` on `<html>` and stored in `localStorage`. The selected theme is persisted server-side through a plain `fetch` PATCH to `/settings/theme` (not an Inertia request).

### Case-sensitive imports

The project deploys to Linux — import paths must match on-disk casing exactly (e.g., `../../components/AppLayout`, not `../../Components/AppLayout`). Windows will not catch these mistakes; only CI/Linux builds will.

## License

This project is proprietary software.
