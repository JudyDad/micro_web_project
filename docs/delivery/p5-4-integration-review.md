# P5.4 Integration review

Started 26 October 2026 on the user's instruction "start P5.4". Status: automated journey and a real-data page sweep done; acceptance review open. This record does not close Phase 5.

## What was checked

One project followed over HTTP, as a staff member would, through every Phase 4 and 5 step, using invented data (`tests/Feature/ProjectJourneyTest.php`, SQLite):

| Step | Expected result | Result |
|---|---|---|
| Accepted project, loan without donor | Refused (`loan` error), nothing written, history unchanged | Pass |
| Assign donor, save loan | One loan; state history gains exactly one `Financed` row; one current state; loan task marked done; visit notices scheduled (a Monitoring account exists) | Pass |
| Resubmit the same loan form | 409, still one loan | Pass |
| Opening visit | One visit; opening task done, closing task not; lifecycle unchanged | Pass |
| Resubmit the visit form (stale version) | 409, still one visit | Pass |
| Full repayment instalment | One payment; history gains exactly one `Finished` row; one current state | Pass |
| Resubmit instalments | 409, still one payment | Pass |
| Final pages | Application page, statistics and applications filter show `Finished` | Pass |
| Rejected project | Donor assignment and loan are refused; no loan row | Pass |
| No `finance.manage` / `visits.manage`, or city revoked | Loan, visit, finance and visit-hub routes return 403; nothing written | Pass |
| No Monitoring account in the city | Loan still finances the project, no visit notices are created, and the finance page shows the "visit notices missing" note | Pass |

No step produced a duplicate state, loan, payment, visit or task change on a retry or stale form.

## Defect found on real data and fixed

Sweeping the 100-application real-data sample (50 Latakia, 50 AlWadi) in the browser showed that the application detail page (`/{locale}/applications/{id}`) returned a 500 for imported records. Imported projects carry references to the desktop `user` table (edited-by, paper-application and programme users), which has no operational payload in the new schema, so `ApplicationService::withReferences` threw "Source table has no approved shared operational payload". The invented-data tests never set such a reference. The editable-relations list already skipped `user` parents; the read path did not. Fix: `withReferences` now skips relationships whose parent is the `user` table. Regression test `test_an_imported_project_pointing_at_a_desktop_user_still_opens` fails without the fix and passes with it.

After the fix, all 100 sample applications open (HTTP 200) on the application, finance, visit hub and M&E pages (400 pages, no errors); 84 show a loan, matching the 84 Financed in the statistics. Only status codes were checked, not page content.

## Behaviour worth knowing

- Visit notices are created only when the city has an account holding the Monitoring role (the desktop refused with a message instead). Without one, the project is still financed, and the finance page says the schedule is missing. Check that every city has a Monitoring account before the cutover. The notices are not created later by themselves.
- Donor assignment and loans are only allowed in the states Accepted, Financed, Finished, Canceled and Closed; a Rejected project cannot get a donor or a loan.

## Full suite

244 tests / 7,737 assertions pass (1 skipped as before), Pint passes. The journey file has 5 tests / 70 assertions.

## Not done / not verified

- MySQL: the journey runs on SQLite only. The actual-schema MySQL tests for finance and field operations exist separately but were not re-run for this review.
- Money and visit statistics (P6.1 follow-up) still wait on an owner decision about currency.
- Visit notices and payment-due notices are checked by count, not by recipient routing.
- Staff acceptance and the print comparison remain open.
