# Micro Projects web

Phase 2 foundation in progress. Laravel, Livewire, Blade, Tailwind and Alpine; Arabic RTL and English LTR. This checkout is canonical: https://github.com/JudyDad/micro_web_project.

## Local setup

Requires PHP 8.2+ (mbstring, XML, PDO, OpenSSL; zip recommended for Composer), Composer 2 and Node 22.12+. Use patched runtimes for deployment. Local verification used PHP 8.2.4 and Node 22.19.0; production hosting remains deferred to project completion.

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
npm ci
npm run build
php artisan serve --host=127.0.0.1 --port=8023
```

Open http://127.0.0.1:8023/ar/foundation or /en/foundation. The preview is available only when APP_ENV is local/testing and uses invented rows; no database is needed. File sessions/cache and log-only mail avoid external services. Bind the development server to loopback. Do not run migrations: none are included pending P1.5.

On this XAMPP installation, enable ZIP for a single Composer invocation with `php -d extension=zip C:\ProgramData\ComposerSetup\bin\composer.phar install`. No global PHP settings need changing.

```powershell
composer validate --strict
php vendor/bin/pint --test
php artisan test
npm run build
php artisan view:cache
```

The Windows restricted runner can report PHP `is_readable`/`is_writable` as false even when reads work. Run verification in a normal local terminal if PHPUnit reports that it cannot open vendor/autoload.php. Do not patch dependencies to bypass that check.

## Scope and remaining work

Delivered: locked runtime, CI definition, bilingual reusable UI preview, persistent desktop collapse/mobile navigation, and independently tested desktop PBKDF2 verifier. Real authentication, account rehashing, permissions, audit events, business migrations, concurrency-safe numbering and the database-backed vertical slice await the accepted Phase 1 schema and capability decisions. A browser-only city filter is not authorization. No source records or credentials are seeded.

SomarSans/logo packaging and exact visual comparison remain pending D09; this preview uses system fonts and a temporary text mark.

For a separate, explicitly configured MySQL test instance named `micro_projects_test`, set the DB_* variables locally and run `php artisan foundation:check-database`. It only executes SELECT 1, refuses DB_URL overrides and production mode, and never migrates. CI provisions a disposable MySQL 8.4 service. A passed connection check is not schema or value-preservation acceptance.

See [delivery board](docs/delivery/task-board.md) and [Phase 2 handoff](docs/delivery/phase-2-foundation.md). Keep all dumps, private attachments and credentials outside Git. Hosting/provider/runtime and public versus office/VPN access must be confirmed at project completion before deployment.
