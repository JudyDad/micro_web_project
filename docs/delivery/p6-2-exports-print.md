# P6.2 Exports and print

Started 26 October 2026 on the user's instruction "start P6.2". Status: first slice implemented locally (two CSV exports and the checklist print); acceptance review open. This record does not close P6.2 or Phase 6.

## Scope chosen

The desktop has about twenty export buttons (Excel) and one print (the checklist mail merge into `cl.docx`). This slice covers the three that the web already has data and screens for:

1. **Applications export** (`/{locale}/applications/export?format=xlsx|csv`; Excel is the default): every application matching the list's current search, city, stage, status and date filters, not one page. Columns: City, Project number, Name, Stage, Request date, Lifecycle status. Linked from the applications page with the filters carried over.
2. **Instalments export** (`/{locale}/payments/export`): every instalment matching the payments schedule's filters (pending/paid, year, months, group, cities). Columns: City, Project number, Beneficiary, Partner, Group, Currency, Amount, Due date, Paid, Received date. Amounts are exact decimal strings; currencies are never combined. "Received date" is filled only for paid rows (imported unpaid rows carry the due date there, which would mislead).
3. **Checklist print** (`/{locale}/applications/{id}/checklist/{person}/print`): a clean A4 page with print styles, right-to-left for Arabic, showing the saved checklist (visits, professional test and training, reviews, key people, team and committee members). It prints what is stored; the desktop saved before printing, the web does not write on print. Without a saved checklist it says so.

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

- **Coverage block:** the desktop adds a coverage sheet to its workbooks. The CSV has City on every row; which cities were included or left out is on screen only.
- **Other exports** (lookups, logs, notifications, task board, statistics, visits, details, the 22 search modes) are not built. The statistics page has no export yet.
- **Role rule for payments:** the desktop rejected payment export for the Lawful role. The web requires finance and application view grants; a separate export grant is an owner decision.
- **Large and queued exports:** the gate asks for queued exports when needed and a re-check at download; the streamed export is authorized once per request. No selection over 500 rows was tried beyond the 24-row test; the real sample has 523 rows.
- **Print parity:** the desktop merged a Word template (`cl.docx`). The web page is not a pixel copy of it, so the print comparison, Arabic printing on paper or PDF, and page breaks across several pages remain to be checked by staff. Template licensing and font packaging (D09) are unchanged.
- Not run on MySQL beyond the sample site: the sample database is MySQL 8.4, and these exports ran there through the browser, but no automated MySQL test covers them.
