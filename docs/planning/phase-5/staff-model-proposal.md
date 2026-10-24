# Staff model for the single database — approved

**Approved by the owner on 24 October 2026** ("Approved as written"), with these changes to the draft: Media may use the task board (Guest may not), and the Financial role gets every finance part (loans, instalment schedule, executive files, purchasing, attachments). Accounts created after cutover receive a generated one-time password that the administrator hands over; the user must change it at first login. Payment notices are stored one row per recipient account (not one shared role copy), so the table needs no extra column. Prepared from the owner's answers to the P5.3 open decisions. It replaces the interim P5.3 rule "a web account acts through the historical desktop user it is linked to".

## Owner decisions this proposal implements

1. Administrators assign, for every city, which accounts are responsible for each task. Staff flags and task responsibilities live in the web, not in the preserved desktop `user` table.
2. Roles are web roles. Notifications reach people by role automatically (the financial role gets payment notices, and so on); nothing is configured per user by hand as on the desktop.
3. Reschedule visits: a not-yet-seen visit can be moved, and the project's later unseen visits can move with it. Generating never duplicates existing visit notices.
5. **One database in the end.** After the five city databases are migrated, the old databases and the preserved `user` tables are ignored. At runtime the web must not depend on them: accounts, roles, duties, e-mail, task responsibilities and notices are web data. Historical desktop users remain only as provenance (who did what in old records).
6. A bundle for each role (table below).
7. Password reset: a "change password" button for a logged-in user (no old password asked) and "forgot password" on the login page.

## Roles

A role is a named bundle of per-city capabilities. An administrator assigns roles per account and per city; each capability stays individually grantable and audited (nothing changes in how grants are checked). Derived from the desktop menus and role checks (`MainForm`, `Application_New_Form`, `Loans_Form`, `Purchasing_Form`, `SpecialCase_Form`, `Payments_Form`, `Search_Form`).

| Role (desktop id) | Capabilities in the city | Receives automatically |
|---|---|---|
| Administrator (1, 8) | every capability, including accept/change state, delete, `imported.delete`, `tasks.manage_all`, `accounts.manage`, `notifications.send` | state changes |
| Data entry (2) | people, applications, assessments, special cases, visits, M&E visits, profession meetings (view/manage/delete), attachments, task board and notifications (view) | state changes |
| Financial (3) | projects, applications, attachments, finance, executive files and purchasing (view/manage), task board (view/manage) and notifications (view) | payment-due notices, state changes |
| Legal (7) | projects, applications, attachments, finance (view), executive files (view/manage), task board and notifications (view) | executive-file expiry notices, state changes |
| Media (9) | projects, people, applications (view), task board (view/manage) and notifications (view) | state changes |
| Guest (4) | projects, people, applications (view only) | nothing (the desktop excluded Guest from state notices) |
| Duty: Monitoring & evaluation (flag IsME) | visits, M&E visits, special cases, profession meetings (view/manage), task board | state changes; its presence in a city allows visit schedules to be generated |
| Duty: Communication (flag IsCommunication) | task board, notifications (view) | "purchases finished" and clearance trophy notices, state changes |
| Duty: Cashier (flag IsCashier) | finance (view) | payment-due notices |

A person may hold several roles/duties in a city. "Out of service" (desktop 6) is not a role: the account or its city access is disabled. The desktop's "visitor" flag has no web use (visitors are not recorded) and is not carried over.

## New and changed tables

All additions are new tables except one column pair on `web_accounts`. Names are proposals.

**`web_accounts` (alter):** add `email VARCHAR(190) NULL` (case-insensitive unique when set) and `password_changed_at DATETIME(6) NULL`.

**`account_city_roles`:** `id`, `account_id` FK, `city_id` FK, `role VARCHAR(32)`, `decision_locator VARBINARY(1024)`, `assigned_at`, `revoked_at NULL`; UNIQUE(account_id, city_id, role). Routing uses active rows. Assigning a role also grants its capabilities (audited); revoking a role revokes only the capabilities no other role of that city still needs.

**`account_city_tasks`:** `id`, `account_id` FK, `city_id` FK, `task_id` → `op_task(city_id, id)` (the city's task catalog), `assigned_at`, `revoked_at NULL`; UNIQUE(account_id, task_id). This is the "who is responsible for each task" matrix that administrators fill per city.

**`web_notices`:** `id`, `city_id` FK, `account_id` FK NULL (NULL = the shared copy of a visit notice), `sender_account_id` FK NULL, `project_id` NULL → `op_microproject(city_id, source_id, id)` with `source_id`, `project_number INT NULL`, `kind VARCHAR(32)` (visit, payment, clearance, trophy, executive_file, state, purchases, message), `pay_key BIGINT NULL` (instalment identifier for payment notices), `notice_date DATE`, `original_date DATE NULL`, `title VARCHAR(255)`, `body MEDIUMTEXT`, `note TEXT NULL`, `change_reason TEXT NULL`, `seen TINYINT NOT NULL DEFAULT 0`, `created_at`. Indexes by (account_id, seen, notice_date), (city_id, project_id, kind), (pay_key). At migration the old `op_user_notification` rows are copied in: rows addressed to a user that an account is linked to go to that account, shared visit notices stay shared, others stay readable only in the preserved table.

**`account_password_resets`:** `id`, `account_id` FK, `token_sha256 BINARY(32)`, `requested_at`, `expires_at`, `used_at NULL`; one-time, 15 minutes, stored hashed only.

No change to `op_*` business tables, the preserved tables or the existing account tables beyond the two columns.

## Behaviour

- **Task responsibility:** an account may tick a task if it holds `tasks.manage` in the city and is assigned that task in `account_city_tasks`, or holds `tasks.manage_all`. Administrators fill the matrix on a "Task responsibilities" page per city (task rows × staff, with a copy-from-role shortcut). Until cutover, assignments inherited from the desktop `task_user` through reviewed links still count; the console command `staff:adopt-legacy` copies them (and roles, duties and e-mail from each linked desktop user) into the new tables, after which the legacy sources are ignored.
- **Notices by role:** recipients are found from active `account_city_roles` of the city: payment-due → Financial and Cashier; purchases finished and clearance trophy → Communication; executive-file expiry → Legal; state changes → every role except Guest/Media; messages → chosen accounts. Senders are the acting account. The inbox reads `web_notices` for the account; payment notices are one row per recipient account, and marking one seen clears every row of that payment. An account given the Financial or Cashier role later sees only notices created afterwards.
- **Visits:** a project's visit schedule is generated once, never when any visit notice already exists, and only if the city has an account with the Monitoring & evaluation duty. Administrators and M&E staff can set a new date and reason on a not-yet-seen visit notice (original date kept) and choose to shift all later unseen visits of the project by the same number of days; seen visits cannot be moved. Each change is audited.
- **Password reset (no e-mail is sent; delivery stays disabled):**
  - *Change password* (logged in): a button on the account menu; new password twice, no old password asked (as requested). Existing sessions are not otherwise affected. Consider the trade-off: anyone who finds an unlocked session can change the password.
  - *Forgot password* (login page): the user enters user name and the e-mail stored for the account; if both match, a single-use reset link is shown on the next page (valid 15 minutes) and the user sets a new password. Answers are identical for unknown names and wrong e-mails; attempts are throttled and audited. The e-mail comes from the migrated desktop user record; administrators can set it on the account page. An account without an e-mail cannot use this route and an administrator must change it.
  - Password policy: at least 10 characters, not equal to the user name; stored with Argon2id.
- **Accounts without a legacy password** (created after cutover) are created by an administrator with a one-time password they hand over, and the user changes it at first login.

## Effect on what was just built

S1–S3 (task board, inbox, generators) are re-pointed from "desktop user through a link" to the tables above; S4 (account administration) gains role assignment, e-mail and the task matrix. The legacy bridge (first-login password upgrade, linking a desktop identity) stays as a migration aid and is no longer used at runtime for notices, senders, recipients or task responsibility. Existing tests are updated, not discarded.

## Status

Approved and being implemented (migration `2026_10_24_000009_create_staff_model_tables`).
