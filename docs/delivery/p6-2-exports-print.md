# P6.2 Exports and print

Started 26 October 2026 on the user's instruction "start P6.2". Status: first slice implemented locally (two CSV exports and the checklist print); acceptance review open. This record does not close P6.2 or Phase 6.

## Scope chosen

The desktop has about twenty export buttons (Excel) and one print (the checklist mail merge into `cl.docx`). This slice covers the three that the web already has data and screens for:

1. **Applications export** (`/{locale}/applications/export?format=xlsx|csv`; Excel is the default): every application matching the list's current search, city, stage, status and date filters, not one page. Columns: City, Project number, Name, Stage, Request date, Lifecycle status. Linked from the applications page with the filters carried over.
2. **Instalments export** (`/{locale}/payments/export`): every instalment matching the payments schedule's filters (pending/paid, year, months, group, cities). Columns: City, Project number, Beneficiary, Partner, Group, Currency, Amount, Due date, Paid, Received date. Amounts are exact decimal strings; currencies are never combined. "Received date" is filled only for paid rows (imported unpaid rows carry the due date there, which would mislead).
3. **Statistics export** (`/{locale}/statistics/export?format=xlsx|csv`): the statistics page's own numbers under the same filters and city scope, as one flat sheet (Section, Category, City, Records): total, coverage per permitted city (Included, Included with no matching records, Not included), lifecycle status by city, application stage and request year. Counts only, as on the page; the coverage rows carry the "which cities were left out" statement that the desktop put on a coverage sheet.
4. **Checklist print** (`/{locale}/applications/{id}/checklist/{person}/print`): a clean A4 page with print styles, right-to-left for Arabic, showing the saved checklist (visits, professional test and training, reviews, key people, team and committee members). It prints what is stored; the desktop saved before printing, the web does not write on print. Without a saved checklist it says so.

## Behaviour

- **Format:** a real Excel workbook (`.xlsx`, written with OpenSpout, streamed row by row so large exports do not build in memory) or, with `format=csv`, a UTF-8 CSV with a byte-order mark so Excel opens Arabic text correctly. Workbook: bold frozen header row, project numbers and instalment amounts as numbers (plain decimals of at most 15 digits, otherwise text), dates as ISO text, and the sheet is right-to-left on the Arabic pages. Text is never a formula in a workbook; in the CSV a cell starting with `=`, `+`, `-` or `@` (other than a plain number) is prefixed with an apostrophe.
- **Authorization:** both exports run through the same service methods as the on-screen lists, so city scope and capability are identical. Authorization and the audit record happen before the first byte is sent, so a refusal is a normal 403. A city the account may not view is a 403; with no viewable city the export is refused and audited as `access.denied`. Print requires the `assessments.manage` grant (the desktop denied checklist print to the Financial and Lawful roles) and is audited as `assessments.printed`.
- **Audit:** every export writes `reports.exported`.
- **Large exports:** streamed synchronously; no queue yet (see below).

## Checks run (26 Oct 2026)

- `ExportTest`: 8 tests / 102 assertions on invented data (SQLite): all 24 rows exported while the list pages at 20; filter and search parity with the list; the same project number in two cities stays distinguishable; other-city, missing-grant and bad-filter refusals; audit rows; formula defusing; paid and unpaid instalments; link generation in English and Arabic; print in both languages, grant check, audit, empty case.
- Real-data sample (100 applications, Latakia and AlWadi) in the browser: export has 100 rows (Latakia 50, Financed 84, requested in 2026: 10), equal to the on-screen statistics; instalment exports have 110 pending and 523 paid rows, equal to the schedule's counts, and their amount sums equal the schedule totals exactly (72,040,000 and 127,440,000, currency SP). Arabic print page renders right-to-left with all six sections and no horizontal overflow.
- Defect found on real data and fixed: the instalment "Paid" column showed 0 for paid rows because the stored paid flag is the text `Paid`, not a number. A test with a paid instalment now covers it.
- Full suite: 252 tests / 7,839 assertions pass (1 skipped as before); Pint and the Vite build pass.

## Excel workbooks (added 26 Oct 2026 on the user's decision)

- Added `openspout/openspout` 4.28.5 (the only new package) and enabled `extension=zip` in the local XAMPP `php.ini` (backup `php.ini.bak-before-zip` beside it). A server started before that change must be restarted: the first live download returned a 500 (`ZipArchive` not found) until the sample site was restarted. Hosting must provide the zip extension; CI already does.
- `ExportTest` now has 10 tests / 132 assertions: the workbook is read back and checked (header, numbers, plain-text formula lookalikes, `Not paid` rows, equal to the CSV), right-to-left only on Arabic pages, bad `format` refused.
- Real-data sample, downloaded from the running site: applications 100 rows; instalments 110 pending (amount sum 72,040,000) and 523 paid (127,440,000), amounts numeric, identical to the schedule totals; the Arabic file is right-to-left and the English one is not.
- Full suite after the change: 254 tests / 7,869 assertions (1 skipped as before); Vite build and `composer validate --strict` pass. `composer audit` reports one existing medium advisory on Livewire 4.4.7 (fixed in 4.4.8; the app does not use the affected `#[Authorize]` attribute); not changed here.
- Not checked: opening the files in desktop Excel itself (the files were read back with the library and the browser pane cannot open Excel).

## Not done / not verified

- **Coverage in the other two exports:** the application and instalment exports carry City on every row but no statement of which cities were included or left out (the statistics export does).
- **Other exports** (lookups, logs, notifications, details, the 22 search modes) are not built.
- **Role rule for payments:** the desktop rejected payment export for the Lawful role. The web requires finance and application view grants; a separate export grant is an owner decision.
- **Large and queued exports:** the gate asks for queued exports when needed and a re-check at download; the streamed export is authorized once per request. No selection over 500 rows was tried beyond the 24-row test; the real sample has 523 rows.
- **Print parity:** the desktop merged a Word template (`cl.docx`). The web page is not a pixel copy of it, so the print comparison, Arabic printing on paper or PDF, and page breaks across several pages remain to be checked by staff. Template licensing and font packaging (D09) are unchanged.
- Not run on MySQL beyond the sample site: the sample database is MySQL 8.4, and these exports ran there through the browser, but no automated MySQL test covers them.

## Statistics export and dependency update (26 Oct 2026)

- Livewire updated 4.4.7 → 4.4.8 (`composer update livewire/livewire --with-dependencies`; only `composer.lock` changed) to clear advisory PKSA-xhv3-nr94-h8db; `composer audit` now reports no advisories. Full suite, build and Pint pass after the update.
- Statistics export: `ExportTest` 12 tests / 166 assertions (page numbers, filters, coverage rows, numeric counts in the workbook, 403 for an ungranted city or no viewable city, audit, bad format). Real sample: the workbook equals the page (Financed 44 + 40, Accepted 3, Under Review 5, Rejected 3, Withdrew 2 + 2, Delayed 1, years 72/18/10, stage Full 100); with `cities[]=4&date_from=2026-01-01` the file has Latakia 10 and AlWadi "Not included".
- Full suite: 256 tests / 7,903 assertions (1 skipped as before).

## Visit schedule and task board exports (26 Oct 2026, not yet committed)

- `/{locale}/visit-schedule/export?format=xlsx|csv`: every visit notice matching the schedule's filters (year, months, vehicle/other, visit number, seen), not one page of 50. Columns: City, Project number, Project name, Beneficiary, Visit, Date, Seen, Visit status (Done / N/A / blank, the desktop's rule), Note.
- `/{locale}/task-board/export?format=xlsx|csv[&kind=a]`: every project matching the board's filters (all pages), one row per project and one column per task (Done / Not done / Not assigned), plus loan date on the funded board. The task column headings are the city catalog's task names.
- Both reuse the page's own query (the services were split into a private filter-and-query method used by the page and the export), so the cities, capabilities and filters are identical; authorization and the `reports.exported` audit record happen before the first byte is sent. Links with the current filters sit above both tables.
- Tests: `FieldExportTest` 5 tests / 79 assertions (59 visits exported while a page holds 50, filter parity, status and seen, other-city and missing-grant refusals, task cells including not-assigned, both formats, links in both languages); the existing task-board, notification, visit and monitoring tests (34) still pass. Full suite 261 tests / 7,982 assertions (1 skipped as before), including another session's uncommitted work.
- Real sample: funded task board 87 projects (84 Financed + 3 Accepted, equal to the statistics), applications board 100; the Arabic file shows Arabic status names. The sample has no visit notices (its loans were imported, not created in the web), so the visit export there is header-only, which matches the empty page.
- Not verified: a visit export with real visit notices on MySQL; headings are English in both languages (as in the other exports) while task and status names follow the catalog and locale.
## Search reports: Loans and Payments, Family Members (26 Oct 2026, not yet committed)

The desktop has 22 search modes (`Search_Form`). The web now has a search-reports page, `/{locale}/search-reports?mode=…`, with a registry of modes (`App\Services\Reports\Search`): each mode is one class with its capabilities, filters, row query and lines. The page lists the matching rows (50 per page, with the total) and the same rows, every page, go to `/search-reports/export?format=xlsx|csv`. Cities, grants, filters and audit (`reports.search`, `reports.exported`) are common to all modes; a new mode is one new class and one line in the registry. Linked from the statistics page. Two modes are built:

- **Loans and Payments** (`Search_Form.Loan_bind`, `Select.BuildLoanReportQuery`): one row per loan, 30 columns: city, project number, every linked person, mobile, career, category, sub category, project status, loan date, currency, exchange rate, loan in SP and in USD, return rate, amount to return, instalment, paid and remaining amount, total, paid and remaining instalments, first instalment, last paid and due dates, months late, unpaid instalments, note, donor, group and receipt. Needs `finance.view` and `applications.view` in the city. Filters: search (name or number), project status, donor, donor group, request date range, loan date range. The formulas are the desktop report's, on exact decimals (`LoanMath`, `Money`); nothing is summed across currencies. The remaining-instalments figure uses the stored loan rate, as the desktop report does.
- **Family Members** (`Search_Form.FamilyMember_bind`): every person with family book, relation, residence state, work and project link; as in the desktop's left joins a person with several family links or projects appears once per combination and one with none appears once with blanks. Needs `people.view`. Filter: search (name, national number, family book number).

### Checks

- `SearchReportsTest`: 9 tests / 142 assertions (USD and SP loans, zero rate, fully repaid, finished project, months late with a fixed clock, every filter, 54 loans exported while a page holds 50, family fields and blanks, workbook equals the CSV with numbers, grants and city isolation, audit, both languages, lone dash placeholder). Full suite: 270 tests / 8,124 assertions (1 skipped as before), Pint and the build pass.
- **Oracle on the real-data sample (read-only):** the desktop's own loan SQL was run on the restored Latakia and AlWadi source databases and compared with the export for all 84 sample loans. Loan (SP), Loan (USD), return rate, amount to return, instalment, paid amount, paid and unpaid counts, first, last-paid and due dates and months late: 0 mismatches. The first run differed on 15 loans with no paid instalment, where the desktop leaves remaining amount and remaining instalments blank (its SQL sums the paid instalments, NULL for none); on the owner's decision (26 Oct) the web now matches the desktop, and the rerun shows 0 mismatches on those. The only remaining difference is 2 loans one cent apart in total instalments (118.76 vs 118.77, 91.12 vs 91.13): the desktop rounds a floating-point column, the web rounds the exact decimal. For Family Members, all 443 sample persons: identical family-number sets, every web project link exists in the desktop data, every field equal except trailing spaces the web trims. The comparison scripts live in the session scratchpad, not in the repository (they read the private connection file at run time).
- Defect found by the oracle and fixed: a lone `-` (a placeholder in the data) was exported as `'-` by the CSV formula guard; a lone `-` or `+` is now left alone (other formula lookalikes are still defused).

### Not done / not verified

- 20 of the 22 modes: applications (the list has most of it), attachments, M&E and monitoring visits, checklists, special cases, project materials and power sources, home power sources, relief aids, aid sources, assets, illness, losses, current works, work experiences, courses, goals, educations, profession meetings.
- Loans filters not yet built: fund type, project type and sub type, partnership, category, sub category, career, funded-amount ranges. Family has no project filters (the desktop hides them for this mode too). Instalments of the "paid" kind are not a separate report (they are the payments export).
- The desktop collapses a project's loans into one row (group by project); the web has one row per loan. The sample has one loan per project.
- The desktop's own on-screen financial totals panel (export/import/returned SP, USD) is not reproduced; it is per currency and belongs to P6.3's oracle.
- Remaining amount and remaining instalments are blank until a loan has at least one paid instalment (owner decision, matches the desktop). Paid amount shows 0 where the desktop leaves it blank.
- Headings are English in both languages, as in the other exports; catalog names (status, relation, donor) follow the data and locale.
- Not run on a MySQL database through an automated test; the sample (MySQL 8.4) was checked through the browser and the oracle only.
## Detail search modes and export reading (26 Oct 2026, not yet committed)

- Twelve more search reports, defined as data in `DetailSearch::DEFINITIONS` (the desktop's `bind_person_tables` and `bind_project_tables`): aid sources, assets, courses, goals, relief aids, illness and medical needs, losses, home power sources, current works, work experiences (person modes: the person's name, relation to the beneficiary and the project of the family's provider) and project required materials, project power sources (project modes: the project's people). Only projects with a current status appear, as in the desktop. Catalog values show by name. A detail row whose person or catalog link the import left unresolved (a city whose source declares no foreign key, e.g. Latakia's `person_aidsource`) is matched by its legacy number within the same city.
- The project filters (status, donor, donor group, request dates, search) moved into a shared trait used by loans and the detail modes.
- Exports now read in ordered chunks (`lazy`) instead of a buffered cursor.
- Tests: `DetailSearchTest` 8 tests / 95 assertions. Full suite 278 tests / 8,220 assertions (1 skipped as before).
- Each of the twelve modes was compared row for row with the desktop's own SQL on the sample (1,109 rows, 0 unexplained differences): see [P6.3](p6-3-report-oracle.md).
- The other eight modes are described in the next section.
## The remaining eight search modes (26 Oct 2026, not yet committed)

All 22 desktop search reports now exist in the web (`App\Services\Reports\Search`). The last eight, each a class with its own grants, filters and row query, following the desktop's SQL:

| Mode (key) | Lists | Needs | Not shown, and why |
|---|---|---|---|
| Applications (`applications`) | One line per project and linked person (and per loan): 48 columns for the project, status and status date, loan, donor, and the person's details | `applications.view`, `people.view` | Spouse and the two child counts (desktop subqueries over family rows) |
| Attachments (`attachments`) | The images recorded per project, by type and category | `attachments.download`, `projects.view`, `applications.view` | File paths and files (never in a report; they open only through the audited download path). The desktop narrows image types by the user's role; the web has no such rule, so every account that may download attachments sees all types here: owner decision |
| M&E visits (`visit-dates`) | One line per project: opening visit, M1 to M5 and closing visit dates | `visits.view`, `me_visits.view`, `applications.view` | The desktop's totals panel; the desktop reads M&E visits for an arbitrary linked person, the web for the beneficiary |
| Monitoring visits 1 to 5 (`me-visits`) | Every M&E visit with project, net profit, result, evaluation range, first and last visitor marks | `me_visits.view`, `applications.view` | Visitor names (desktop `user` table) |
| Check lists (`checklists`) | One line per checklist and project: visits, professional test and training, team and committee dates and opinions, key people | `assessments.view`, `applications.view` | Examiner and rejection-message sender names (desktop `user`) and total marks (the desktop's `need_evaluation` view) |
| Special cases (`special-cases`) | Every review with project and people | `special_cases.view`, `applications.view` | Reviewer name (desktop `user`) |
| Educations (`educations`) | Every family member's education under the provider's project | `people.view`, `applications.view` | None |
| Profession meetings (`meetings`) | Meetings, newest first, filtered by meeting date (new filter) | `profession_meetings.view` | Registrar names (desktop `user`) |

- **Check list quirk:** the desktop's SQL reads the home-visit second visitor for the project visit's second visitor; the web shows the project visit's own. The oracle does not compare that column and counts the rows where the two differ.
- Tests: `OtherSearchModesTest` 10 tests / 169 assertions (all 22 modes registered, applications with loan and a second person, educations with the family-row and provider fallback, special cases, meetings and the date filter, evaluation ranges and visitor marks, visit dates, a saved check list, attachments without paths and the grant, per-mode grants, pages in both languages). Full suite 288 tests / 8,389 assertions (1 skipped as before).
- The oracle ran each new mode against the desktop's SQL on the sample: see [P6.3](p6-3-report-oracle.md).
- Still open in P6.2: queueing, print parity, staff acceptance (the export role rules were decided and built; see the last section).
## Loan report filters (26 Oct 2026, not yet committed)

The Loans and Payments report now has the desktop's remaining filters (`Check_Conditions`):

- **Fund type, project type, project sub type, category, sub category, career:** multi-select lists by name (the form lists the names found in the permitted cities). A project matches when it has any of the chosen names; different lists combine with "and".
- **Partnership:** individual (one person) or partnership (more than one), by the project's partnership value.
- **Funded amount (USD):** from and to, inclusive. The amount is the loan converted to USD (an SP loan is divided by its exchange rate) and rounded to two decimals, as in the desktop; a loan with no exchange rate has no USD value and is excluded once a range is given. A blank end is open. The desktop turns a blank end into 0, so a range with only a "from" matches nothing there; the web does not copy that.
- The classification and partnership filters live in the shared project-filter trait (`applyClassificationFilters`), so any project-listing mode can enable them by listing `classify` in its filters. Only the loan report does for now; the desktop shows them for most project-based modes. The desktop's requested-amount range is not built.
- Exports and links carry the chosen filters, including the lists.
- Defect found by the oracle and fixed: a "funded to" amount with no "from" was rejected by the form's validation (`gte` against an absent field), which an HTTP-less service test could not see. Range ends are now compared with their start only when the start is given; a new test opens every range at either end over HTTP. Another cause found on the way: SQLite compares a bound parameter as text, so the amount comparison is made numeric (`? + 0`).
- Tests: `LoanFiltersTest` 5 tests / 115 assertions (each list by name, several names, blank, combinations, partnership, USD range inclusive and open, no-rate loans, page and export with the choices, validation, open ends over HTTP). Full suite 293 tests / 8,516 assertions (1 skipped as before).
- Oracle: 27 filter cases against the desktop's own conditions on the real sample, all equal (see [P6.3](p6-3-report-oracle.md)).
## Notifications, audit log, lookup tables and details (26 Oct 2026, not yet committed)

The desktop's remaining export buttons, each with the authorization and the audit record (`reports.exported`) of the page it comes from, all matching rows, Excel or CSV:

| Export | Where | Needs | Content |
|---|---|---|---|
| Notifications (`Notification_Box`) | `/{locale}/notifications/export`, link on the inbox | `notifications.view` in the city | The account's own box exactly as the inbox shows it (unseen, up to three days ahead), newest first: city, date, type, project number, title, message, sender. Other accounts' notices and the shared copies are never included |
| Audit log (`Log_Form`) | `/{locale}/admin/audit` (page, 50 per page) and `/admin/audit/export`, links on the accounts page | `accounts.manage` in the city | Event number, time (UTC), event, outcome, account, city, subject type and number, subject account. Filters: dates, event, outcome, account name, cities. Scope: events recorded for the administrator's cities, plus events with no city (sign-ins, password changes) whose account or subject has a capability or role in those cities. Viewing and exporting the log are themselves logged. Newest first |
| Lookup tables (the administration forms' Excel buttons) | `/{locale}/lookups` (list with row counts) and `/lookups/{table}/export` | `applications.manage` in the city (the data-entry and administrator roles, who reach the desktop's lookup forms) | 36 reference tables (streets, aid sources, donors and groups, educations and their types and levels, works, state reasons, careers and categories, and the others used by the forms), by city; a linked value (a group's donor, a sub category's category) shows by name |
| Project state history (`ShowDetails_Form`, "حالات المشروع") | `/{locale}/applications/{id}/states/export`, link on the application page | The application page's own (`applications.view`; the reason and note only with `lifecycle.manage`) | Every state, newest first: city, project number, state, date, current, reason, note |
| Family members (`ShowDetails_Form`, "أفراد العائلة") | `/{locale}/people/{id}/family/export`, link on the person page | `people.view` | Every member whose family provider is the beneficiary: person and family numbers, family book, name, mother, marital status, relation, work, date of birth |

- Tests: `OtherExportsTest` 8 tests / 177 assertions (own box only, horizon and seen rules, city scope and filters of the audit log, 120 events paged at 50 and exported whole in order, lookup values by name and by city, every listed lookup exports, state history with the note hidden from readers, family members of the provider only, links on the four pages, grants and audit for each). Full suite 310 tests / 8,919 assertions after the export role rules (1 skipped as before).
- Real sample (MySQL): all five exports return data; the oracle compares the lookups and the state history with the desktop (see [P6.3](p6-3-report-oracle.md)).
- Not built: the desktop's third details grid, the beneficiaries behind a statistics bar (the application export with the same status, city and date filters lists them); the desktop's data-change log (its `log` table is empty in the restored sources and the web has its own audit trail instead; "seen" flags are not reproduced); an editor for the lookup tables.
- The desktop refuses the details export to the Guest, Out-of-service, Lawful and Media roles. The web applies the page's own view grants only; mapping that rule onto roles is an owner decision, together with the payment-export and attachment-type rules.
- Still open in P6.2: those role rules, queued exports, print parity, staff acceptance.
## Export role rules (decided and built 27 Oct 2026, not yet committed)

The owner accepted the proposals in [export-role-rules-proposal.md](../planning/phase-6/export-role-rules-proposal.md) (a desktop manager is an administrator; cashier and financial are one role; financial gets the financial papers; cashiers export payments; communication cannot export).

- **New grants** (`AccountAccess::CAPABILITIES`): `finance.export`, `reports.export`, `attachments.personal`, `attachments.legal`, `attachments.financial`. Role bundles: administrator all; data entry `reports.export`, personal and legal; financial `finance.export`, `reports.export`, financial; legal the legal category only; monitoring `reports.export`; communication, media and guest none. The `cashier` role is gone (its grants are the financial role's; payment notices go to financial accounts).
- **Enforcement**, per city: instalment schedule export `finance.export`; search-report exports `reports.export` (the loan report also `finance.export`); application list, visit schedule, task board and the two details exports `reports.export`; statistics export `reports.export` and `applications.manage`. An export that needs a grant the account holds in only some cities is limited to those cities; naming another city, or holding it nowhere, is a 403 and an audited refusal. Pages are unchanged: a role that may read may still read.
- **Attachments:** an image downloads only with its category's grant (the check reads the image type through the image row); an unknown or missing type needs all three; the attachments report lists only the categories the account holds, and an account with the download grant but no category grant is refused. Files without an image type keep the download grant alone.
- **Existing accounts:** `php artisan staff:sync-export-roles [--dry-run]` ends each cashier assignment and gives the account the financial role in full, then adds only the new grants to accounts that hold a role carrying them. It never re-adds a grant an administrator revoked by hand and is safe to repeat. Accounts adopted from desktop users with role 5 need `staff:adopt-legacy` run again.
- **Tests:** `ExportRolesTest` 9 tests / 219 assertions (role bundles; the nine exports for each of the eight roles; pages stay open where only the export is refused; money export needs `finance.export`; city-limited exports; the attachments report per role; the download check per role and category with audit; the sync command including a dry run, a hand-revoked grant and repetition; the legacy mappings). The shared test fixtures now carry the export grants.