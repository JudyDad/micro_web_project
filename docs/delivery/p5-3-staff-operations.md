# P5.3 staff operations

Started 22 October 2026 on the user's instruction "start P5.3"; revised 24 October 2026 for the owner's decisions on the single database. Status: **implemented locally (task board, notification inbox and messages, generated notices, account administration, roles, task responsibilities, password reset, visit rescheduling, legacy adoption); acceptance review open and no acceptance is claimed.**

The first delivery (22 October) carried desktop users into the web through reviewed links and used only the approved schema. The owner then decided that the final system is **one database with the old city databases ignored once their data is migrated**, so staff identity, roles, duties, e-mail, task responsibilities and notices became web data. The physical schema for that was proposed and approved on 24 October ([proposal and role table](../planning/phase-5/staff-model-proposal.md)): migration `2026_10_24_000009_create_staff_model_tables` adds `account_city_roles`, `account_city_tasks`, `web_notices`, `account_password_resets` and `email` / `password_changed_at` on `web_accounts`.

## Slices

| Slice | Desktop source | State |
|---|---|---|
| S1 Task board | `TaskBoard_Form.cs`, `Classes/TasksOfProjects.cs` | implemented locally |
| S2 Notification inbox, messages, schedule actions | `Notification_Box.cs`, `SendMessage_Box.cs`, `Classes/UserNotification.cs`, `Visits_Schedule_Form.cs` | implemented locally |
| S3 Generated notices | `Loans_Form.cs`, `ExecutiveFiles_Form.cs`, `AdminPanel_From.cs`, visit forms | implemented locally |
| S4 Account administration, staff directory | `User_Form.cs` (partly) | implemented locally |
| S5 Staff model (24 Oct): roles, task responsibilities, notices by role, passwords, rescheduling, legacy adoption | owner decisions 1, 2, 4–7 | implemented locally |

Not in scope by design: `Chat_Form.cs` (a LAN TCP chat; notices replace it) and real delivery. The web has no e-mail, push or other external channel, so a test environment cannot deliver anything.

## Roles

A role is a named bundle of per-city capabilities that an administrator assigns to an account in one city. Assigning grants the capabilities (each still audited and revocable individually) and records the assignment in `account_city_roles`; removing a role revokes only the capabilities no other role of the account in that city still includes. Nobody configures notices per user: the roles decide who receives what. The bundles follow the owner-approved table: **Administrator** (every capability), **Data entry**, **Financial** (finance, executive files, purchasing, attachments and the instalment schedule), **Legal**, **Media** (read plus task board), **Guest** (read only, no task board), and the duties **Monitoring & evaluation**, **Communication** and **Cashier**.

| Notice | Goes to (accounts holding the role in the city) |
|---|---|
| Payment due | Financial, Cashier |
| "Purchases finished", clearance trophy | Communication |
| Executive-file expiry | Legal |
| State change | every role except Guest |
| Message | the accounts chosen (everyone who may read notifications, or one) |
| Visit and clearance notices | shared copy, no recipient |

## S1 task board

- `/task-board`: one row per project and a done / not done cell per task across the permitted cities (funded board: states Accepted, Financed, Finished, Canceled, Closed with 21 tasks; application board: every current state with 8). Filters: board, city, state, funder, type, text, done / not done per task. Cells are toggled with a form button that sends the state shown, so stale or repeated submissions get 409.
- **Who may toggle:** `tasks.view`, `tasks.manage` and `applications.view` in the city **and** responsibility for the task. Responsibility is what administrators set on the **task responsibilities** page (`/admin/tasks`): for each task of a city's board, the accounts responsible. Only accounts that may manage tasks in the city can be chosen. `tasks.manage_all` lets an administrator toggle any task of its city. The board lists the responsible accounts per task.
- Completing "مشتريات" sends "انتهاء المشتريات" to the Communication accounts of the city; clearing it marks only that project's notices seen (the desktop cleared every project's).

## S2 notifications

- **Inbox** `/notifications` (bell with the unseen count in the navigation): the account's own unseen notices in the cities where it has `notifications.view`. A Financial account reads up to today, every other account up to three days ahead, and the count is the size of that list (confirmed rule). Marking seen clears every copy of the notice; a payment notice clears all notices of that payment.
- **Messages** `/notifications/send` (`notifications.send`): to one account or to everyone who reads notifications in the city; date, subject (255 bytes) and text. Administrators (`accounts.manage`) may attach a project number. The sender is the acting account.
- **Visit schedule** (`/visit-schedule`, `visits.manage`): edit a note, hide / restore a notice, and **reschedule**.

## Visit rescheduling and generation

- **Generation guard:** a project's visit schedule is created once, with its first loan, never when it already has any visit or clearance notice, and only when the city has an enabled Monitoring & evaluation account. Editing a loan never regenerates it. The finance page says when a loan project has no visit notices.
- **Reschedule** (owner decision 4): on the schedule page a not-yet-seen visit can be given a new date and a required reason; the original date is kept (and shown with the reason). With "also move this project's later unseen visits" the later unseen visits move by the same number of days (earlier or later). Seen visits never move; only visits can be moved (not the clearance notice); the action needs `visits.manage` and is audited (`notifications.rescheduled`).
- Visit dates: opening five days after the loan date, then six-monthly visits each counted from the previous one with .NET month-end clamping (standard: M1, M2, M3, MFinal; solar: M1, M2 marked "(Solar)"; agricultural: M1–M5 in April or November, seven and five months apart; grant-funded rehabilitation: a single closing visit). The letter is V for vehicle sub-categories 1 and 2, otherwise O.

## S3 other generated notices

- **Instalments:** a new unpaid instalment notifies each Financial and Cashier account (due date, amount); changing an instalment updates its notices and marks them seen exactly when it is paid; unchanged instalments leave notices alone (the desktop reset them on every save). Imported instalments keep their desktop payment number; web instalments use their own identifier.
- **Clearance:** when the last instalment is paid near the end of the loan, a shared "براءة الذمة (Clearance)" notice for the next day and a trophy notice per Communication account; saving again adds nothing.
- **Executive file:** expiry notice per Legal account; an edit moves the date (the desktop's edit never changed anything).
- **State changes:** Withdrew, Canceled, Finished and Closed mark every notice of the project seen; any move except Rejected and Waiting is announced to the staff roles.
- **Visits:** a new opening visit marks its opening notice seen; a new M&E visit marks that person's notice (visit 4 also clears "MFinal"); a closing visit, including the rehabilitation one, marks every notice of the project seen.

## S4 / S5 account administration

- **Administrator scope:** `accounts.manage`, per city. It sees accounts with grants or reviewed links in its cities and accounts with neither; assigns and removes roles and capabilities it holds itself; never edits its own grants; disables an account only when it administers every city the account is active in; sets an account's e-mail only when it holds every capability that account has (it cannot take over a stronger account through "forgot password").
- **Pages:** `/admin/accounts` (list, filters), `/admin/accounts/{id}` (roles and capabilities per city, e-mail, linked desktop identities, disable), `/admin/accounts/new` (new account from reviewed duplicate desktop identities, or **without a desktop identity**), `/admin/tasks` (responsibility matrix per city), `/admin/staff` (accounts of each city with roles, task count and e-mail).
- **Accounts without a desktop identity** (after the old databases are retired): the administrator gives a user name, an e-mail and a role; a generated one-time password is shown once; the account must choose its own password before anything else (`credential_principal_id` NULL and `password_changed_at` NULL mark it).
- **Accounts from desktop identities** inherit that identity's desktop password (verified and rehashed at first login) and its e-mail when valid and unused.

## Passwords

- **Change password** (menu "Change password"): a logged-in user sets a new password twice, **without giving the old one** (owner decision). Consider the trade-off: anyone who finds an unlocked session can change the password.
- **Forgot password** (link on the login page): the user enters the user name and the e-mail stored for the account; if both match an enabled account the reset page opens at once with a single-use link (valid 15 minutes, stored only as a SHA-256 hash; a newer request cancels earlier ones). Nothing is e-mailed. A mismatch shows a generic error; attempts are throttled and audited (`password.reset_requested|reset_denied|reset|changed`). An account without an e-mail cannot use this route.
- Policy: at least 10 characters, not equal to the user name, Argon2id.

## Legacy adoption (cutover)

`php artisan staff:adopt-legacy [--city=ID] [--dry-run] [--decision=REF]` copies, for every account with a reviewed link to a desktop user: roles and duties (desktop role 1/8 → Administrator, 2 Data entry, 3 Financial, 7 Legal, 9 Media, 4 Guest, 6 nothing; flags IsME / IsCommunication / IsCashier → the three duties), the e-mail (valid and unused only), `task_user` responsibilities and the user's notices (personal ones to the account, shared visit and clearance notices once). It is repeatable (nothing duplicated), audited like any grant, and an operator tool: it is not reachable from the web, and mapping role 1/8 to Administrator grants all capabilities, so review the dry run first. After it the web no longer reads the old databases or the preserved `user` tables for staff, roles, notices or task responsibility; the legacy bridge (linking an identity, first-login password upgrade) stays only as a migration aid.

## Decisions taken (owner, 24 October) and what remains

1. Approved: responsibilities and staff duties live in the web; administrators set them per city.
2. Approved: roles with automatic notice routing.
3. Kept: finance/legal inbox rule.
4. Approved: generation guard and rescheduling.
5. Approved: single database; old databases ignored after migration.
6. Approved with changes (Media gets the task board, Financial gets every finance part); more bundles can be added.
7. Approved: change and forgot-password flows, verified with the user name and e-mail.
8. Left out: the "content updated" notice, the "add notes" e-mail action, any external delivery.
9. Confirmed deferred: Excel exports, column hiding on the task grid, notes of non-visit notices, application deletion.

Still open: the production database user must be allowed to write the account and staff tables (no separate administrator connection is used); real staff grants are still not provisioned; **payment notices are one row per recipient, so an account given the Financial or Cashier role later sees only notices created afterwards** (it does not backfill unpaid instalments).

## Verification (24 October 2026)

- SQLite: `TaskBoardTest` 8 cases, `NotificationInboxTest` 9 (including rescheduling), `ProjectNoticesTest` 9, `AccountAdministrationTest` 14, `AccountPasswordTest` 4, `LegacyStaffAdoptionTest` 2, `AccessRolesTest` 3, plus the visit tests. The full suite passes except two HTTP cases that load `/en/applications`, which a concurrent reports change in the working tree (`$filters` undefined in `applications/index`) breaks; they are re-run when that lands.
- Actual approved schema on a fresh isolated MySQL 8.4.8 (own data directory outside Git, loopback port 33092): `StaffOperationalWorkflowTest` passes 1 case / 50 assertions (all migrations including 000009; plain accounts and one-time passwords; roles; task responsibility; purchases notice; inbox, messages, reschedule with the `MONTH()` filter; first-loan schedule needing a monitoring account; instalment, clearance and executive-file notices; the real `BINARY(32)` reset token; account creation from duplicate identities with a desktop-password login; adoption; and the database's own rejections: shared notices only for visit and clearance, kind and seen checks, project foreign key, unique task and role assignments, unique e-mail, reset expiry).
- Not done: rendered browser check, five-city data parity, concurrent-writer tests, staff acceptance.
