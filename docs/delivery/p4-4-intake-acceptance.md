# P4.4 intake acceptance

21 October 2026. Status: automated and browser acceptance evidence recorded; **staff acceptance and print comparison are open, so the Phase 4 gate is not claimed.** All data below is invented.

## Acceptance matrix

| Area | Evidence | Result |
|---|---|---|
| Whole journey | `IntakeAcceptanceTest`: preliminary creation over HTTP (Arabic name and multi-line note) → full-field update → conversion refused without budget → budget row → conversion → checklist with committee opinion → Accepted → Financed refused | Pass |
| Rejected, withdrawn, reopened | Rejected → Under Review → Withdrew, repeat refused; one current row throughout; exact note text kept; no tasks assigned | Pass |
| Retries and stale forms | Double-submitted conversion and transition produce one event (second request 409); a form whose own section did not change still saves | Pass |
| Historical records | Imported project with sentinel date `0000-00-00`, padded Arabic note and two history rows with no current flag: page renders, the newest row is treated as current, a transition sets exactly one flag, older rows and unrelated values are untouched | Pass |
| Authorization | Every new route is denied (403, no writes) when any one required grant is revoked, and when all grants for the city are revoked; a project of a city without grants is unreachable and cross-city catalog ids are refused | Pass |
| Concurrency (two real PHP processes, MySQL 8.4.8) | Both race one conversion and one transition on the same form version while the parent holds the project lock: exactly one 200 and one 409 each; one current state row; tasks not duplicated | Pass |
| Rendered browser (built-in browser, local synthetic data) | Application page with state panel and checklist page in English and Arabic RTL at desktop width and 375px: no horizontal overflow, direction correct, no console errors | Pass |

## Defect found and fixed

A conversion refused for a missing budget row (or a non-preliminary application) returned a bare 422 error page. It now returns a form error in the active language.

## Limits and open items

- **Print comparison not done.** The Word checklist/print templates have no web equivalent yet; there is nothing to compare. Needs a report/print task (planned P6.2) and the owner's choice of reference templates.
- **Staff workflow sign-off not done.** Needs named acceptance owners and restored-data access (Phase 3 rehearsal still open).
- **Realistic catalog scale, five-city data and full-form household/story/work/education validation** are untested or unimplemented; see the P4.3 record. The conversion check remains partial.
- The concurrency test covers the two lifecycle writers. Person/family and application-edit races are covered by their own earlier checks; no test races all writers together.
- The browser check used invented data seeded through the test fixtures. Arabic names in that seed appeared as question marks because the seed script was saved in a non-UTF-8 encoding; the stored text, not the application, was affected, and the PHPUnit suite covers Arabic round-trips.

## Verification commands (21 October 2026)

- Unit and feature suites: 113 cases, 5,941 assertions pass (one host-dependent symlink case skipped). Pint passes.
- MySQL actual-schema: the P4.2/P4.3 application test (28 assertions) and `LifecycleConcurrencyTest` (103 assertions) pass on the isolated loopback server. The concurrency test commits its fixture, so it empties the disposable database afterwards instead of using migration rollback (the rollback guards refuse retained evidence).
