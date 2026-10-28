# Local test setup (28 October 2026)

How the site runs locally for viewing and testing while no real data is in use. Nothing here is production configuration.

## Database: MySQL 8, not XAMPP's MariaDB

The approved schema refuses MariaDB: migrations 000006 and 000007 require MySQL 8.0.16+ for enforced CHECK constraints, and the import target guard requires MySQL 8 as well. XAMPP ships MariaDB 10.4, so the site's database cannot live there. The local database is the **100-application sample** (`micro_projects_local`, cities Latakia and AlWadi, publication kind `synthetic`) on a separate MySQL 8.4.8 instance:

| Item | Value |
|---|---|
| Server | `MicroProjects-TestRuntime\mysql-8.4.8-winx64\bin\mysqld.exe`, data directory `MicroProjects-Phase3-Private\sample-data` |
| Port | 33074 on 127.0.0.1 only |
| Database | `micro_projects_local` |
| Site user | `sample_web` (password in `MicroProjects-Phase3-Private\sample-web-db.secret`, copied into the checkout's `.env`) |
| Start | launch configuration `micro-mysql-sample` in `D:\MyProjects\micro_web\.claude\launch.json`, or the same `mysqld` arguments by hand |

The previous `.env` (port 33072, the older empty `micro_projects_local` under `MicroProjects-TestRuntime\data`) is kept as `.env.bak-33072`.

## Editing the data with XAMPP's phpMyAdmin

`C:\xampp\phpMyAdmin\config.inc.php` has a second server entry, "Micro Projects (MySQL 8.4, port 33074)", root without password, limited to `micro_projects_local`. Open http://localhost/phpmyadmin/ and pick that server from the server list at the top of the login panel (or `index.php?server=2`). XAMPP's own MariaDB stays server 1 and is untouched; the `hcsyria_micro*` databases on it are the desktop sources and must stay read-only.

## Serving the site

Launch configuration `micro-web` runs `php artisan serve --host=127.0.0.1 --port=8024` from the checkout, matching `APP_URL`. The MySQL instance must be up first.

## Test accounts: one per role, password `password`

`database/seeders/RoleAccountsSeeder.php` creates or restores them; it refuses to run in production:

```bash
php artisan db:seed --class=RoleAccountsSeeder
```

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

Re-running the seeder resets each password to `password`, re-enables the account and re-assigns the role in all five cities, so the accounts are always available until real staff accounts replace them. The passwords are marked as changed, so the site does not demand a new password at first login (an account whose password was never changed is treated as holding a one-time password). `sample.tester` (administrator in the two sample cities) and `role.cashier` (the cashier role was merged into financial on 27 October, so it no longer grants anything new) predate the seeder and are left as they are.
