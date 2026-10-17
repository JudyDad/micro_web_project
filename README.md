# Micro Projects web

Phase 2 local foundation complete: source-qualified sign-in, explicit city access, audited project search and read-only details in Arabic and English. Laravel, Livewire, Blade, Tailwind and Alpine. Canonical repository: https://github.com/JudyDad/micro_web_project.

## Local setup

Requires PHP 8.2+ with Argon2id, mbstring, XML, PDO MySQL and OpenSSL, Composer 2, Node 22.12+ and MySQL 8.4. Use patched runtimes for deployment. Local verification used PHP 8.2.4, Node 22.19.0 and MySQL 8.4.8; hosting is deferred until project completion.

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
npm ci
npm run build
```

Create a dedicated empty loopback database named `micro_projects_local`. Configure DB_* privately with a migration account, then run:

```powershell
php artisan migrate
php artisan phase2:demo
```

These commands create 616 typed preservation tables, ten support tables and four operational tables, then seed two entirely invented projects and source-qualified principals. The fixture refuses production, remote databases, URL/socket overrides and databases containing snapshots or principals. Rollback refuses retained evidence. Never run these commands against source or business databases.

Switch DB_* to a separate restricted runtime account using [runtime permissions](docs/operations/runtime-permissions.md), then start:

```powershell
php artisan serve --host=127.0.0.1 --port=8023
```

Open http://127.0.0.1:8023/ar/login or /en/login. Select Aleppo or Damascus; username `demo.user`, password `Synthetic-Desktop-2026!`. These credentials are public synthetic test values. Each account sees only its own city; both example projects have original number 1042. Local component demonstrations remain at /ar/foundation and /en/foundation.

File sessions/cache and log-only mail need no external services. No private records, production credentials, dumps or attachments are seeded. Provided Somar Sans and Hope Center assets have [documented provenance](docs/delivery/theme-assets.md).

## Verification

```powershell
composer validate --strict
php vendor/bin/pint --test
php artisan test
npm run build
php artisan view:cache
```

The full integration suite is opt-in. It requires a disposable **empty** MySQL database named exactly `micro_projects_test` on loopback, refuses pre-existing application tables and MariaDB, and returns the database to an empty migration repository. Never point it at the demo or business database.

```powershell
$env:RUN_MYSQL_SCHEMA_TESTS='1'
$env:MYSQL_TEST_HOST='127.0.0.1'
$env:MYSQL_TEST_PORT='33072'
$env:MYSQL_TEST_USERNAME='micro_projects_test'
# Set MYSQL_TEST_PASSWORD privately.
php vendor/phpunit/phpunit/phpunit tests/Integration
```

The separate `RuntimeAuditPrivilegesTest` requires `RUN_RUNTIME_AUDIT_TESTS=1`, MYSQL_RUNTIME_HOST/PORT/DATABASE/USERNAME/PASSWORD, a provisioned synthetic local/test database and a restricted account. It inserts an audit event in a rolled-back transaction and proves audit edits, business writes and permission/publication changes are denied. CI provisions an ephemeral MySQL 8.4 service and runs both suites.

Windows restricted execution can falsely report PHP files unreadable or block npm process spawning; run checks from a normal local terminal in that case. Do not patch dependencies to bypass these checks.

## Delivery boundary

See the [delivery board](docs/delivery/task-board.md) and [Phase 2 completion evidence](docs/delivery/phase-2-completion.md). The preservation schema and four-table foundation addendum are implemented; the local synthetic read-only gate passes. Real staff grants (D07), importer/reconciliation, attachment access, snapshot identity continuity and production activation remain subsequent work. Phase 3 starts with P3.1, the deterministic importer. Confirm hosting/provider/runtime and public versus office/VPN access at project completion before deployment.
