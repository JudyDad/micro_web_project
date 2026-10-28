# Local test setup (28 October 2026)

How the site runs locally for viewing and testing while no real data is in use. Nothing here is production configuration.

## Database: `microproject_db` on XAMPP (MariaDB 10.11)

After the XAMPP update (MariaDB 10.11.16, PHP 8.5.12) the site's database lives on XAMPP:

| Item | Value |
|---|---|
| Server | XAMPP MariaDB 10.11.16 on 127.0.0.1:3306 |
| Database | `microproject_db` (utf8mb4) |
| Site user | `microproject_web`@`127.0.0.1`, all privileges on that database only; password in `MicroProjects-TestRuntime\microproject_db.secret` and in the checkout's `.env` |
| Schema | all nine migrations (819 tables including the 616 typed preservation tables), run with `php artisan migrate` |
| phpMyAdmin | http://localhost/phpmyadmin/ on XAMPP's default server, database `microproject_db` |

The migration guards of 000006 and 000007 used to refuse MariaDB by name. Since 28 October they accept MariaDB 10.2.1+ as well as MySQL 8.0.16+, because both enforce CHECK constraints, which is what the guard protects (verified on this server: a violating insert fails with error 4025). The approved schema itself is unchanged and runs unmodified on MariaDB 10.11. The importer and its target guard still require MySQL 8; a full five-city import into MariaDB is not covered by any evidence yet, and the hosting decision remains deferred to project end.

The earlier databases are untouched and only switched off in `.env`: the 100-application sample (MySQL 8.4.8, port 33074, `.env.bak-33074`) and the empty `micro_projects_local` (port 33072, `.env.bak-33072`). phpMyAdmin keeps a second server entry for the port-33074 sample.

## Serving the site

Launch configuration `micro-web` in `D:\MyProjects\micro_web\.claude\launch.json` runs `php artisan serve --host=127.0.0.1 --port=8024` from the checkout, matching `APP_URL`. XAMPP's MySQL must be running.

## Seeders (local only, refuse production)

```bash
php artisan db:seed            # both of the following
php artisan db:seed --class=RoleAccountsSeeder
php artisan db:seed --class=LocalProjectSequencesSeeder
```

- `RoleAccountsSeeder`: one account per staff role plus `super.admin`, password `password`, role assigned in all five cities. Re-running resets the password, re-enables the account and re-assigns the role, so the accounts are always available until real staff accounts replace them. Passwords are marked as changed, so no new password is demanded at first login.
- `LocalProjectSequencesSeeder`: gives each source a project-number sequence starting at 1 when it has none, so applications can be created on an empty database. It never changes an existing sequence; cutover sets the sequences from the reconciled source maximums.

| Username | Role in every city |
|---|---|
| `super.admin` | administrator |
| `role.administrator` | administrator |
| `role.data-entry` | data-entry |
| `role.financial` | financial |
| `role.legal` | legal |
| `role.media` | media |
| `role.guest` | guest |
| `role.monitoring` | monitoring |
| `role.communication` | communication |

The database starts empty apart from the catalogs the migrations register (cities, sources, source tables). Lookup catalogs (categories, parishes, streets and so on) are filled by the importer; on this empty database the intake form's selects are empty until a catalog is imported or rows are added through phpMyAdmin.

## PHP 8.5

The checkout runs on XAMPP's PHP 8.5.12. `composer check-platform-reqs` reports that `openspout/openspout` declares support only up to PHP 8.4; the application boots and the export, intake, print and search suites pass on 8.5 (32 tests / 462 assertions, no deprecation notices), so this is a declared-constraint mismatch, not an observed failure.
