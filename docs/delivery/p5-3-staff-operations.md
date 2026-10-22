# P5.3 staff operations

Started 22 October 2026 on the user's instruction "start P5.3". Status: **slice S1 (task board) implemented locally; P5.3 is not complete and no acceptance is claimed.** No physical schema change so far: every payload maps to the approved `op_task`, `op_task_microproject`, `op_task_user` and `op_user_notification` tables.

## Scope and slicing

| Slice | Desktop source | State |
|---|---|---|
| S1 Task board | `TaskBoard_Form.cs`, `Classes/TasksOfProjects.cs` | implemented locally (this record) |
| S2 Notification inbox and messages | `Notification_Box.cs`, `SendMessage_Box.cs`, `Classes/UserNotification.cs`, `MainForm.Check_Notifications` | planned |
| S3 Notification generation (deferred from P4.3/P5.1/P5.2) | `Loans_Form.cs`, `ExecutiveFiles_Form.cs`, `AdminPanel_From.cs`, visit forms | planned |
| S4 Account administration | unified-account design, `User_Form.cs` | planned |

## S1 task board — delivered

Source: `TaskBoard_Form.bind_f_tasks` / `bind_a_tasks`, `MP_dataGridView_CellDoubleClick`, `TasksOfProjects.Update_Task_MicroProject`. No schema change.

- **Page** `/task-board` (navigation item "Task board" for accounts with the grant): one row per project and a done / not done cell per task, across the permitted cities, 50 rows per page, newest project number first. Two boards as on the desktop: *funded projects* (current state Accepted, Financed, Finished, Canceled or Closed; 21 task columns) and *applications* (every current state; 8 task columns). The columns are the desktop's legacy task ids (`TaskBoardColumns`); the header is the task's name in the city's catalog, with the desktop's header as fallback.
- **Row data:** project number (link to the project), city, linked people ("first last ابن/ة father", distinct), the beneficiary's phone, project name, current state, funder, type and (funded board) the first loan date. A cell is empty ("–") when the project has no assignment of that task; the web never creates assignments here.
- **Filters:** board, city, state, funder, type, free text (project number, project name, person name or phone), and done / not done per task. Combined with the city permissions; a city without the grant never appears.
- **Toggling a task:** a form button per cell. It writes `State` 1/0 and today's date on every assignment row of that project and task (as the desktop's `UPDATE … WHERE project AND task`). The page sends the state it showed, so a stale or repeated submission is refused with 409 and changes nothing. Done is any non-zero stored state, matching the desktop's image test.
- **Who may toggle** (desktop: only users listed in `task_user` for the task): the account needs `tasks.view`, `tasks.manage` and `applications.view` for the project's city **and** responsibility for the task. Responsibility is carried by the reviewed legacy link: a `task_user` row names a historical source user, and an account that has a non-revoked link to that user's principal (same source, principal not disabled) is responsible. A source user number of another city is a different identity. `tasks.manage_all` (per city, administrator-granted) lets a web-only account manage every task of that city, since it has no source user to be assigned. The board lists the historical staff responsible for each task, per city.
- **Side effect kept:** completing "مشتريات" (legacy task 16) adds the notice "انتهاء المشتريات" (`Pay_ID` -3, beneficiary names, today's date) for every communication user of the city's source (`user.IsCommunication`). The sender is not recorded (the desktop's sender is a source user); the audit trail records the account. **Deliberate difference:** clearing "مشتريات" marks only that project's -3 notices seen; the desktop's `Update_NotificationWithPaymentID(-3, …)` had no project condition and cleared every project's notices.
- **Safety:** the project row is locked, the permission and board checks run before the transaction (so a refusal is audited as `access.denied`) and again under the lock; a project outside the board of the task's kind (for example a waiting application on the funded board) gives 404.
- **Audit and permissions:** events `tasks.board` and `tasks.toggled`; capabilities `tasks.view`, `tasks.manage`, `tasks.manage_all` (also `notifications.view`, `notifications.send`, `accounts.manage` registered for the next slices).
- Tests: `TaskBoardTest`, 8 cases / 94 assertions on SQLite (board and filters, responsibility through links including revocation and a different city's same user number, `manage_all`, off-board and missing assignments, purchases notices and project-scoped clearing, HTTP in both languages). Full unit/feature suite: 191 tests / 6,942 assertions (1 skipped) pass.

### S1 not built / open decisions

1. **Column visibility, exact-search toggle and Excel export** of the desktop grid: export belongs to P6.2; the web has filters instead of column hiding.
2. **Task assignment management** (who is responsible for a task) is the historical `task_user` data. The web cannot add rows to it for web-only accounts (its user reference points at preserved source users); `tasks.manage_all` is the interim path. Say if responsibility should be managed in the web and how (it needs a new table).
3. **Listing order:** the desktop ordered the funded board by state and the application board by project number descending; the web orders both by project number descending.
4. **Not run:** actual-schema MySQL test, rendered browser check, five-city data parity, staff acceptance.
