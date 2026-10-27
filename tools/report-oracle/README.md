# Report oracle (P6.3)

Compares the web's reports and exports with the desktop's own SQL on the restored source databases, city by city. It is read-only on the sources and writes only counts and the differing values.

```powershell
$env:ORACLE_WEB_PASSWORD = '<web password>'   # never on the command line
php tools/report-oracle/oracle.php --web=http://127.0.0.1:8024 --user=sample.tester `
    --source-config=D:\MyProjects\MicroProjects-Phase3-Private\connection.json --sample --out=oracle-report.json
```

- `--source-config`: JSON with `host`, `port`, `source_username`, `source_password`; the databases are `micro_projects_source_<city>` (lower-case city name as the web shows it).
- `--sample`: for the 100-application sample only (its catalog rows are copied through resolved links, so a catalog name can be blank in the web but present in the desktop; the tool lists that as a known difference). **Do not use it on the full import**: there such a blank is a failure.
- Exit code 0 when there are no unexplained differences, 1 otherwise, 2 for bad arguments.

## What it checks

| Check | Compares |
|---|---|
| applications | every web row against the desktop (name, request date, current status); a filter matrix (status, year, date ranges including an inclusive boundary on a day that has records and the day before it, year end/start, status and date, empty result, each city) against the desktop rows satisfying the same predicate |
| statistics | status-by-city and request-year counts against the desktop aggregates over the same projects |
| loans | the 15 figures of the Loans and Payments report, per loan, against `BuildLoanReportQuery` |
| instalments | count and amount by city, project, currency and paid/unpaid, plus totals by city, currency and state (both directions) |
| family | per person: family numbers, project links and the person's attributes against `FamilyMember_bind` |
| details | the twelve detail search modes: every row on both sides, compared as a multiset |
| lookups | 21 lookup tables exported whole against the desktop's tables (with `--sample`: every web row must exist in the desktop's table) |
| state history | each project's state-history export against the desktop's `microproject_state` rows (state, date, current); fails when no project is found |
| loan filters | the Loans and Payments report's fund type, type, sub type, category, sub category, career, partnership and funded-amount (USD) filters: the number of loans under each filter (names taken from the real data, ranges from the real values) against the desktop's own conditions; on a difference it lists the loans that differ |
| other modes | applications, attachments, M&E visits, monitoring visits, check lists, special cases, educations (every row, both ways) and profession meetings (every web meeting must exist in the desktop's meetings of the city) |

## Known, documented differences (listed, not failures)

- "Total instalments" may differ by one cent: the desktop rounds a floating-point column, the web rounds the exact decimal.
- "Remaining instalments" is rounded to two decimals; the desktop value is unrounded.
- "Paid amount" is blank in the desktop and 0 in the web until a loan has a paid instalment.
- CSV text that starts with `=`, `+`, `-`, `@`, a tab or a carriage return gets an apostrophe prefix (formula guard); workbook exports are unaffected.
- Check lists: the desktop SQL reads the home-visit second visitor as the project-visit one; that column is not compared.
- With `--sample`: catalog names missing from the sample.
