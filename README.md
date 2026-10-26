# Micro Projects web

Phase 2 foundation with unified username/password login, reviewed historical account links, explicit per-city access and audited project search/details in Arabic and English. Phase 3 adds the approved unified business schema, deterministic staged importer and independent core reconciler. Whole-identical-table sharing is a separate proposed addendum. Laravel, Livewire, Blade, Tailwind and Alpine. Canonical repository: https://github.com/JudyDad/micro_web_project.

## Local setup

Requires PHP 8.2+ with Argon2id, mbstring, XML, PDO MySQL and OpenSSL (also ZIP, which Excel exports and archive sharing proof need; enable `extension=zip` in php.ini and restart the server), Composer 2, Node 22.12+ and MySQL 8.4. Use patched runtimes for deployment. Local verification used PHP 8.2.4, Node 22.19.0 and MySQL 8.4.8; hosting is deferred until project completion.

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

These commands create 616 typed preservation tables, ten support tables, four historical foundation tables, four unified-account tables, 122 operational/import tables and 57 sharing tables. They seed two entirely invented projects, their historical principals and one explicitly reviewed global demo account. The fixture refuses production, remote databases, URL/socket overrides and databases containing snapshots or principals. Rollback refuses retained evidence. Never run these commands against source or business databases.

To upgrade the existing two-city synthetic Phase 2 demo, use the migration account to run `php artisan migrate` and `php artisan phase2:upgrade-demo`. The latter accepts only the known synthetic snapshot identities and preserves history, password and grant changes on repeat runs. Configure the revised runtime grants below afterward. Authentication now uses the distinct `account` session guard, so old principal sessions cannot become global account sessions merely by matching numeric IDs.

Switch DB_* to a separate restricted runtime account using [runtime permissions](docs/operations/runtime-permissions.md), then start:

```powershell
php artisan serve --host=127.0.0.1 --port=8023
```

Open http://127.0.0.1:8023/ar/login or /en/login. Username `demo.user`, password `Synthetic-Desktop-2026!`. These are public synthetic test values. Login opens projects from both assigned cities; use the city checkboxes alongside search to filter results. Leaving all cities unchecked shows all permitted cities. Both example projects have original number 1042 and retain their city identities. Local component demonstrations remain at /ar/foundation and /en/foundation.

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

The user approved the [unified-account schema and plan](docs/planning/unified-account-city-filter-revision.md). The local demo implements username/password login, administrator-assigned cities, independent per-city capabilities and page-level city filters. Controlled provisioning/grant services are implemented; the administrator web interface remains in the planned staff-administration module. Only synthetic accounts are provisioned here.

See the [delivery board](docs/delivery/task-board.md) and [revision verification](docs/delivery/unified-account-revision.md). Real staff grants (D07), complete source/file/report reconciliation, attachment access and production activation remain subsequent work. One physical database is already used; the current combined web read adapter operates over preserved source tables while Phase 3 staged consolidation is verified. Confirm hosting/provider/runtime and public versus office/VPN access at project completion before deployment.

The approved P3.0 schema now has migration 000006. See [Phase 3 importer operation and evidence](docs/delivery/phase-3-importer.md) for freezing a restored source, registering its immutable snapshot, staged import and independent reconciliation. Those CLI operations require isolated loopback MySQL and never publish data or activate accounts. The five-city complete import, file coverage and report parity gates remain open. The [whole-table audit](docs/planning/phase-3/shared-data-candidate-audit.md) and [sharing addendum](docs/planning/phase-3/shared-catalog-schema-proposal.md) distinguish common task definitions from city-specific task assignments; the approved converter is implemented and verified on invented isolated data. See [sharing operation and evidence](docs/delivery/phase-3-sharing.md). Actual private-data conversion remains part of the full rehearsal.
