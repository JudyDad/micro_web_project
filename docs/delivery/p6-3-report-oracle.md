# P6.3 Report oracle

Started 26 October 2026 ("check review phase 6 and continue what remains"). Status: tool built and run on the 100-application sample with 0 unexplained differences, covering all 22 search modes; the full-scale run belongs to Phase 9. This record does not close P6.3.

## What was built

`tools/report-oracle/oracle.php` ([README](../../tools/report-oracle/README.md)): a repeatable, read-only comparison of the web's reports and exports with the desktop's own SQL on the restored source databases, city by city. It logs in to a running web instance, downloads the CSV exports, runs the desktop queries (`BuildLoanReportQuery`, `FamilyMember_bind`, `bind_person_tables`, `bind_project_tables`, the application state join and the payment tables) on `micro_projects_source_<city>` and compares. The password comes from `ORACLE_WEB_PASSWORD`; the source credentials from the private connection file read at run time. Nothing private is written to its report.

## Result on the sample (26 Oct 2026, Latakia and AlWadi, 100 applications)

| Check | Result |
|---|---|
| applications | 100 rows match (name, request date, current status); 10 filter cases match the desktop counts, including an inclusive boundary on a day with 8 records, the day before it, year end/start, status and date, an empty result and each city |
| statistics | 11 cells (status by city, request year) match |
| loans | 84 loans, 15 figures each, match; known differences below |
| instalments | per city, project, currency and state, and totals by city, currency and state, match in both directions: AlWadi SP 200 paid (61,295,000) and 66 pending (61,330,000); Latakia SP 323 paid (66,145,000) and 44 pending (10,710,000); 523 paid instalments cover the over-500 case |
| family | 443 persons: family numbers, project links and fields match |
| details | the 12 detail modes, 1,109 rows, match row for row (aid sources 6, assets 58, courses 12, goals 105, relief aids 22, illness 46, losses 26, home power 86, current works 167, work experiences 99, materials 443, project power 39) |
| lookups | 21 lookup tables exported whole: every web row exists in the desktop's table (ID and name); the sample holds fewer rows than the desktop because it copies catalog rows only through resolved links |
| state history | all 100 projects' state-history exports against the desktop's `microproject_state`: 409 state rows (state, date, current) equal |
| loan filters | 27 cases against the desktop's own conditions, number of loans per case, all equal: each of the six lists (the two most common names alone, together, and a name that does not exist), individual and partnership, funded-amount ranges from the real values (q25 to q75, an exact one-loan boundary, open at either end, all, empty above the maximum) and a combined case |
| other modes | the last 8 modes, 2,323 rows, match row for row: applications 101, attachments 1,573, M&E visits 100, monitoring visits 4, check lists 100 (48 columns), special cases 1, educations 443, profession meetings 1 (compared one way: every web meeting exists in the desktop's meetings of the city, which has more than the sample) |

Known differences, listed and not failures: total instalments one cent apart on 2 loans (desktop rounds a floating-point column); remaining instalments rounded to two decimals on 53 loans (the desktop value is unrounded); paid amount blank in the desktop and 0 in the web on 15 loans with no paid instalment; 2 CSV cells with the formula-guard apostrophe; with `--sample`, catalog names absent from the sample (Latakia aid source "SALT", and the evaluation text of 4 monitoring visits because the sample has no result-range rows; the sample copies catalog rows only through resolved links; the evaluation logic itself is unit-tested); CSV text that begins with a tab or carriage return also gets the formula-guard apostrophe (2 more cells); the check list's project-visit second visitor is not compared (the desktop SQL reads the wrong column).

## Defects the oracle found and the fixes

1. **CSV placeholder altered.** A lone `-` (a placeholder in the data) was exported as `'-`. A lone `-` or `+` is now left alone.
2. **Unresolved detail links (data-model gap).** Latakia's `person_aidsource` has no declared foreign key to `person` or `aidsource`, so the import marked those links `not_applicable` and the report listed 3 of 6 rows and blanked the funding body. The desktop joins on the legacy numbers. The detail search now falls back to the legacy person and catalog number within the same city when a link is unresolved. The importer gap itself is not fixed: Phase 3/9 should decide whether undeclared source links are resolved by legacy key at import. Other cities and tables were not inspected for the same gap.
3. **Export memory.** Exports read through a buffered cursor, which on MySQL holds the whole result in PHP memory. They now read in ordered chunks of 200 to 500 rows, so production-sized exports do not hit the memory limit (all export tests and the oracle pass unchanged).

## Gate: what is covered, what is not

Gate: "required report totals and selected-record sets reconcile; large exports are queued when necessary, authorized both when requested and downloaded, and never leak another city's records".

- Reconciled on the sample: counts, sets and amounts by city and currency as above; null and date boundaries (inclusive ends); empty results; more than 500 selected rows (523); duplicate project numbers across cities are tested in `ExportTest` and handled by the city key in the oracle (the sample has none).
- Not covered: the full five-city data (Phase 9); group/donor-group filters beyond the loan report's own tests; Aleppo, Damascus and Homs (not in the sample); queued exports (exports are streamed in one request, authorized once per request); the desktop's workbook and its coverage sheet are not compared cell by cell (only the data).

## Addendum: loan filters (26 Oct 2026)

The oracle's new "loan filters" check found a defect no earlier test could: with only a "funded to" amount the form rejected the request (`gte:funded_from` against an absent field), so the web export was a login/validation page instead of a list. The oracle now names the loans that differ when a count differs, which is how this was traced. Fixed and covered by an HTTP test for every range open at either end.
## Addendum: lookups and state history (26 Oct 2026)

The oracle gained two checks (lookup tables, project state history). While adding them it showed a defect in the tool itself: the loan-filters section had reused the variable `$web` for a count, silently overwriting the base URL, so the next check requested `66/en/...` and compared nothing yet passed. The variables are renamed, and a check that finds no projects to compare now fails instead of passing. Not compared with the desktop: the notifications, audit-log and family-member exports (the sources hold no notifications or web audit events; family members would need person ids the exports do not carry).