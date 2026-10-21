# P4.3 assessment and lifecycle

Started and implemented locally on 21 October 2026. Status: implementation delivered; acceptance review open. The source trace is [the workflow contract](p4-3-workflow-contract.md). This record does not close Phase 3 or Phase 4.

## Delivered

- **State history.** `LifecycleService` reads a project's `op_microproject_state` rows with source-qualified catalog names, shows the current state and enforces the desktop's explicit exclusions (preliminary state automatic, Waiting only through the full application, Financed only through finance). Transitions clear every current flag and insert one new current row under a project row lock and an optimistic version hash. Reason and note of the current row can be edited separately.
- **Preliminary creation** now writes the `On Hold` history row in the creation transaction and fails without consuming a number when the city catalog lacks that state.
- **Preliminary → full** (`convertToFull`) is enabled. It requires a preliminary application, the full project fields already enforced by P4.2 (funding type, premises type, requested amount, five agreements) and at least one complete budget row, then sets the application state, inserts `Waiting` as current and assigns the `a` tasks (`T_ID = 1` starts complete). Already-assigned tasks are skipped, so a retry cannot duplicate assignments.
- **Accepted** needs the separate `lifecycle.accept` grant, assigns the `f` tasks once, and **Withdrew** clears the donor reference when it is set.
- **Assessment checklist.** `ChecklistService` and `/applications/{id}/checklist/{person}` create or edit the person's checklist and member rows (key people, team, committee) with the `check_data` rules, desktop clearing of switched-off sections, in-place member edits, position-type checks and stale-edit protection. Untouched legacy values (sentinels, exact whitespace) are not revalidated or rewritten.
- New per-city grants: `lifecycle.manage`, `lifecycle.accept`, `assessments.view`, `assessments.manage`, each in addition to `applications.view` (and `people.view` for checklists). New audit events: `lifecycle.transitioned`, `lifecycle.reason_updated`, `assessments.view`, `assessments.saved`.
- English and Arabic strings; the lifecycle panel sits on the application page and links to each beneficiary's checklist.
- No physical schema change.

## Deliberately not done

- **Full-application validation parity.** Household, story, work, education and course gates of the desktop full form are still not enforced on conversion; the conversion check covers project details and budget only. Treat full applications created here as incompletely validated until those sections are built.
- **Scoring.** The only writer is the unreachable legacy `Application_Form`; there is nothing to reproduce. Owner confirmation needed before any build.
- **Direct full creation** (`create` with stage `full`) still refuses.
- **Checklist deletion** (member rows and whole checklist), **rejection-message sender selection** (a historical source user cannot stand in for a web actor) and **Word checklist printing** (P4.4).
- **Notifications** on state changes and withdrawal (P5.3), **donor rules** for funded states (P5.1) and any assertion that the desktop role table maps to the new grants. No real grants are provisioned.
- When the rejection-message switch is turned off the desktop clears the sender; the web keeps the recorded sender because it cannot be re-entered.
- Existing `ApplicationService` still carries its own copy of the table helpers; the new services share `Operational\OperationalTables`. Folding them together is a pure refactor for later.

## Verification (21 October 2026)

- 107 unit/feature cases, 5,850 assertions pass (one host-dependent symlink case skipped). New: 10 lifecycle service, 6 checklist service and 4 HTTP cases, including stale versions, cross-city catalog tampering, revoked grants, identifier 0, duplicate-task retries and exact-text preservation.
- Actual approved schema on the isolated MySQL 8.4.8 target (loopback port 33072): the extended application test passes 1 case / 28 assertions, including full conversion, task assignment, Accepted transition and a checklist save, then full rollback.
- Pint and Blade compilation pass.
- Not done: rendered browser check of the new panel and checklist page, cross-process concurrency, staff acceptance, and any five-city data parity.
