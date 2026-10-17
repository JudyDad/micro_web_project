# Phase 2 foundation handoff — 17 October 2026

Status: partial foundation delivered; Phase 2 acceptance gate remains open.

## Gate evidence

P0.4 passed static discovery review. The user supplied the Phase 1 chat at https://chatgpt.com/s/cx_6ad22492b134819197714a0960cad11d. The local chat `Start phase 1 execution` and `D:\MyProjects\MicroProjects-Web-Plan\phase-1\README.md` confirm provisional MariaDB restores of 616 tables / 1,721,699 rows / 10,512 projects with matching table counts. They explicitly leave the final operational schema, MySQL typed-value verification and independent schema acceptance open. That supersedes the old board's claim that no Phase 1 SQL ran. No private restore artifacts were copied into this repository.

P1.5 is not accepted. This delivery therefore contains no migrations, operational models or invented role grants. D07 capability decisions and D09 asset packaging remain open. The full Phase 2 slice is login → permitted city → search → persisted read-only detail; this preview is not that slice.

## Delivered behavior and interfaces

- Laravel 12.70.0, Livewire 4.4.7, Tailwind 4.3.3, Vite 7.3.7, Laravel Vite plugin 2.1.0 and PHPUnit 11.5.57 locked in Composer/npm lockfiles. Alpine is supplied by Livewire, with no duplicate initialization.
- Arabic default route and EN/AR preview, locale validation, production denial, no-store/noindex headers. Reusable layouts, form controls, cards, tables, alerts, native dialog, loading/empty examples and responsive navigation.
- Synthetic Aleppo/Damascus project 1042 rows with separate demo identities and a browser-only filter. No authorization claim or hidden business side effect.
- Pure `LegacyPasswordVerifier::verify(string $password, string $storedHash): bool`: exact desktop PBKDF2-SHA256 parameters, strict format checks, constant-time comparison and independent .NET synthetic vectors. Future login must enforce account status, explicit source/city/capability grants, rate limiting, audit, session rotation and successful-login rehashing. Empty legacy password compatibility does not authorize empty passwords in login policy.
- MySQL test connection command accepts only local/testing, MySQL, the dedicated database name and no URL override. SELECT 1 only. GitHub Actions installs from locks, validates, builds, formats, tests and provisions disposable MySQL 8.4 for this check.
- Source backups, business rows, credentials, fonts and logos were not copied. No source, FTP or production data changed. Default private-disk serving is disabled pending authorized file access design.

## Review and verification

Independent application specialist reviewed bootstrap/routes/config/CI/UI, found the DB_URL isolation bypass in the read-only connection check; coordinator fixed it and added a no-connection regression test. UI specialist independently reviewed verifier and vectors, no correctness findings. Coordinator reviewed theme source and rendered browser behavior.

Local build, strict Composer validation, Pint, Blade compilation, route inventory and PHP tests pass (24 tests / 51 assertions). Browser checks covered English/Arabic, city filtering, native dialog Escape, and responsive layout/navigation. Hosted CI and actual MySQL connectivity are not yet claimed by these local results. Font/logo fidelity remains unaccepted because assets are not packaged.

The installed PHP 8.2.4 runtime is old and is not a deployment recommendation. Laravel 12 was chosen for local PHP compatibility and is in security support through 24 February 2027; production must use patched PHP and revalidate framework lifecycle. References: https://laravel.com/framework/docs/12.x/releases and https://livewire.laravel.com/docs/4.x/installation.

## Next dependencies

Finish Phase 1 final operational schema and independent review, then obtain the required physical-schema confirmation before migration code. Resolve D07 capabilities, then implement authentication/rehashing/audit and server authorization on queries, URLs, actions, exports and downloads. Add accepted migrations and synthetic fixtures; verify project-number concurrency on isolated MySQL. Integrate the real read-only slice and test denied cross-city access. Confirm D09 assets and visually compare against HopeHR. Ask hosting questions only at project completion before deployment.
