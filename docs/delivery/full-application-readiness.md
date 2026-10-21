# Full-application readiness check

21 October 2026. Status: validator delivered first as new files only (another session, P5.1, was editing shared files), then wired into `convertToFull` after P5.1 landed; see the hook-up section.

## What it is

`App\Services\Applications\FullApplicationReadiness::check($application)` is a read-only check. It returns a list of issues `{section, code, field, row}` for the application's beneficiary and project. It writes nothing, authorizes nothing (the caller does), and reports rows by identifier. Codes: `required` (blank value or missing selection), `choice` (yes/no switch not 0 or 1), `required_rows` (a "yes" answer without any entries).

Source: `Application_New_Form.Save_Application` (`:4030–6770`). The desktop validates only touched sections for existing records. The web has no per-section dirty tracking, so conversion is gated as if the beneficiary and project were new and the state were `project` (every `validate*` flag true). This is an interpretation, recorded here rather than silently assumed.

| Section | Rules implemented |
|---|---|
| Beneficiary | first, last, father, mother names; sex; national number; birth year; intermediary; address; marital status, parish, street, home type selected; at least one priest; at least one contact number; for a male discharged/serving: period and in-war answer |
| Story | loss story, goal, how |
| Work | work yes/no chosen and years; "yes" needs a current row; current row needs work, type, start, premises ownership, place, salary; prior row needs work, start, end and, unless the work is "لا يعمل", type, ownership, place, cause |
| Education | education yes/no and, for yes, a row with level and specialization; courses yes/no and, for yes, rows with type, name, date |
| Project | premises type; five yes/no agreements; at least one budget row with name, price, quantity; power-source rows need a source |
| Family | family number; living-with-family answer; each member's names, birth year, sex, national number, relation, residence state, education level and, unless "غير متعلّم", specialization |
| Support | yes/no for losses, illness, assets, aid, debts, help; "yes" needs rows; loss type and year; illness type (and aid source when medical aid); asset item, type, benefit (and year when the type is "ملك"); aid source and year; help source, kind, period; at least one home power-source row with source, explanation and responsible party |

## Not covered

Family members' own work rows (`FP_Exp`), the goal check boxes, duplicate national-number and family-number prompts, the paper-application user, and partners (only the beneficiary is checked). Catalog names such as "ملك" are matched by exact text as the desktop does.

## Hook-up (done 21 October 2026)

`ApplicationService::complete` now collects the project-identity errors (`requireIntake`, with readable field names) and the readiness issues into one `readiness` error list, thrown as a `ValidationException`; the application page shows it through the existing message box. The separate budget check was removed because readiness covers it. Lines read "Section — field needs …" with an `(entry #id)` suffix for a specific row, in English or Arabic. A refused conversion changes nothing.

Tests: `IntakeAcceptanceTest` (refused conversion lists sections in English and Arabic and writes nothing; a broken budget row is named and a complete application converts), the shared `tests/Support/FullApplicationFixture` used by the lifecycle, acceptance and MySQL tests. Verified 21 October: 148 unit/feature cases, plus the actual-schema application test (28 assertions) and the two-process concurrency test (89 assertions) on isolated MySQL. MySQL's real `date` column caught a fixture using a bare year for a birth date; fixtures now use full dates.

## Verification

`FullApplicationReadinessTest`: 5 cases / 45 assertions. A complete application has no issues; 29 single-rule breakages each report the expected section, code and field; "not working" prior rows and non-educated members skip their dependent fields; issues carry row identifiers and the check writes nothing; an application without a beneficiary is not ready. Invented data, SQLite behavior tests only. Not run against MySQL or restored data, so catalog spellings in real data are unverified.
