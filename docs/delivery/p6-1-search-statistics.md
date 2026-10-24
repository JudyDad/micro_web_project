# P6.1 Search and statistics

Started and implemented locally on 24 October 2026 on the user's instruction "start p6.1". Status: implemented for applications; acceptance review open. This record does not close Phase 6.

## Scope chosen

P6.1 was picked because it reads only Phase 2 and Phase 4 data (applications, lifecycle state history, city grants) and does not depend on Phase 5 finance or field data. Money and visit statistics are deliberately not included; they wait on P5.4 and on an owner decision about currency (the requested amount carries no currency, so no amount is summed).

## Delivered behaviour

- **Application list filters** (`/{locale}/applications`): application stage (preliminary/full), lifecycle status (current state from the state history, matched by English name across cities), request-date range (inclusive), alongside the existing name/number search and city filter. Filters survive pagination and the language switch.
- **Statistics page** (`/{locale}/statistics`, nav "Statistics"): record counts by lifecycle status and city, by application stage and by request year, with a share meter and plain tables (no script dependency).
- **Coverage indicators**: every city the account may view is listed as included, not included (left unchecked) or included with no matching records. Records with no recorded status or no request date are counted and called out, never dropped.
- **Authorization**: both pages use the `applications.view` city grant. An explicitly requested city that is not granted is a 403 and an audited denial; revoked cities never contribute to totals or appear in coverage. The statistics view itself is audited as `reports.statistics`.
- The list and the statistics share one filter implementation (`App\Services\Reports\ApplicationReport`), so a filtered list total and the matching statistics total cannot diverge.

## Files

`app/Services/Reports/ApplicationReport.php`, `app/Http/Controllers/ReportController.php`, `app/Http/Requests/ApplicationFilterRequest.php`, `resources/views/reports/*`, `lang/{en,ar}/reports.php`; changed: `ApplicationService::search`, `ApplicationController::index`, applications list view, layout (nav and language-switch filters), `routes/web.php`, `AccountAuditTrail`.

## Current state definition

The current lifecycle status is the history row flagged `Current_State = 1`, else the newest row by date then id (the same rule as `LifecycleService::current`). An application with no history shows as "No status recorded".

## Checks run (24 Oct 2026)

- `ApplicationStatisticsTest`: 5 tests / 63 assertions (invented data, SQLite): cross-city totals and breakdowns, filter parity between list and statistics for seven filter combinations, revoked/unselected city exclusion and 403, EN/AR rendering and language-switch links, invalid filters and empty results.
- Full unit and feature suites: 239 tests / 7,667 assertions pass (1 skipped as before). Pint passes; Vite build passes.

## Not done / not verified

- **MySQL**: the correlated "current state" subquery and year grouping were only run on SQLite. They use portable SQL, but an actual-schema MySQL run (and `EXPLAIN` at full scale) is still required.
- **Projects page** (preserved-table adapter) is unchanged: it still has name/number and city search only.
- **People search** has no new filters.
- No charts library, no exports or print (P6.2), no old-versus-new report comparison (P6.3), no finance, visit or monitoring statistics.
- Browser/visual check of the new pages and staff acceptance remain open.
