# P5.1 finance

Started and implemented locally on 21 October 2026 on the user's instruction "start executing phase 5 task p5.1". Status: implementation delivered; acceptance review open. The source trace is [the workflow contract](p5-1-workflow-contract.md). This record does not close Phase 5.

## Delivered

- **Donor rules** (assigned to P5.1 by P4.3). A project's donor is mandatory in the funded states (Accepted, Financed, Finished, Canceled, Closed) and cannot be set in any other. Donors are the shared whole-table catalog and are only read. A donor group of the city can be created (name and donor required; an identical name for the same donor is refused so a double submit adds one).
- **Loans.** One page per project (`/applications/{id}/finance`) lists every loan with exact figures and edits the chosen one. Creation requires a funded project that already has a donor, writes the approved `op_loan` row in the city's float or double amount column, stores the payments count and repayment rate as the desktop does (rounded to one decimal), moves the project to **Financed** once, and completes the loan task on the project's existing assignment. A second loan for a project that has one is refused. Editing writes only the fields that changed, so preserved legacy values survive an unrelated edit.
- **Instalments.** Rows are inserted or edited in place; unpaid rows (and paid rows without a received date) record the due date as the received date as in the desktop. Whole currency units only (see open decision 1). Paid amount, paid count, remaining (raw and clamped), instalment counts and the "less than one and a half instalments" flag are computed per loan in its own currency. A paid last row with the flag moves the project to **Finished** once.
- **Instalments schedule** (`/payments`). Cross-city list of unpaid (by due date) or paid (by received date) instalments with year, month, donor group and city filters; count and sum **per currency**; beneficiary and partner names; links back to the project's finance page.
- **Executive files.** One file per project (`/applications/{id}/executive-files`): number, begin/current/impound dates, impound type, months; expiry date with .NET month arithmetic; edit in place.
- **Purchases.** Opt-in editor over the preserved `op_purchasing` rows of a project (the desktop screen is unreachable): add and edit rows with category, quantity, price, currency, invoice details.
- **Authorization and audit.** Six new per-city capabilities (`finance.view/manage`, `executive_files.view/manage`, `purchasing.view/manage`), each combined with `applications.view`; the schedule list drops cities without the grant. New audit events: `finance.list/view/donor_assigned/group_created/loan_saved/payments_saved`, `executive_files.view/saved`, `purchasing.view/saved`.
- **Duplicate-submit and retry protection.** Every write runs in one transaction under the project row lock and checks a version hash of the donor, state history, loans and instalments; routes are also serialized per user. A repeated or stale submission is refused with 409 and cannot add a loan, instalment, state row or task update. `LifecycleService::enterNamed` writes a state only when it is not already current.
- English and Arabic strings, application-page links, an "Instalments" navigation item for users with a finance grant, and responsive/RTL tables. No physical schema change.

## Deliberately not done

- **Deleting** loans and instalments, executive files and purchases (desktop admin roles can; the plan restricts deleting historical financial records and needs an explicit authorized design).
- **Notifications** (visit schedules generated on the first loan, payment-due notices for financial users, the clearance notice, the executive-file expiry notice for lawful users). These belong to P5.2/P5.3 and real delivery stays disabled. The expiry date is shown instead.
- **Donor catalog editing** (`AddNewDonor`): donors are a shared whole-table catalog and writing them is a cross-city decision. Group editing/deletion is also not offered.
- **Excel export and printing** of the schedule (P6.2). The desktop role gate on export is therefore not modeled.
- **Mapping the desktop role table** (loans: roles 1/3/7 save, 2/4/9 refused; purchasing 3/4/9 refused) to the new grants. No real grants are provisioned.
- Report-side loan summaries (`BuildLoanSummaryQuery`, USD/SP exports) are P6.

## Open decisions for the finance owner

1. **Whole-unit instalments.** `payment.Pay_Amount` is `FLOAT(24,0)`, so MySQL silently rounds fractions. The web refuses fractional amounts and amounts beyond ±16,777,216 instead. If fractional instalments are real, the physical column needs a reviewed change.
2. **Loan amounts in single precision.** Aleppo, Damascus and Latakia store returned/instalment amounts as `FLOAT`; the web refuses values a float cannot hold at the entered precision (for example 1,234,567.89). Homs and AlWadi (`DOUBLE`) accept them.
3. **Derived count and rate are stored rounded to one decimal** because the desktop saved its `N1` label text. Confirm that this is wanted for new loans; the web never recomputes them for untouched legacy loans.
4. **Loan deletion and instalment deletion**, the purchasing screen's status, and the default year filter of the schedule (the desktop opened on the current year; the web opens on all years).
5. The donor-change rule when a loan's group belongs to another donor: the desktop overwrote the project donor with the group's donor, and so does the web (audited as a loan save).

## Verification (21 October 2026)

- 141 unit/feature cases, 6,250 assertions pass (one host-dependent symlink case skipped). New: 9 money/calculation unit cases, 14 finance workflow cases (donor rules, exact loan values, float and double cities, derived values, legacy preservation, instalment rules and rejection of inexact amounts, automatic Finished once, per-currency schedule totals, grants, groups, executive files, purchases) and 5 HTTP cases (EN/AR render, stale submissions, forbidden routes, view-only accounts).
- Actual approved schema on the isolated MySQL 8.4.8 target (loopback port 33072): `FinanceOperationalWorkflowTest` passes 1 case / 37 assertions (real enum/float/decimal columns, FKs, MySQL month filter) and `FinanceConcurrencyTest` passes 1 case / 97 assertions: two real PHP processes submitting one form version produce exactly one loan, one Financed row and one instalment (the other receives 409). Existing P4 MySQL tests (application 27 assertions, lifecycle concurrency 107, people 17) still pass. The disposable database was verified empty and the server shut down afterwards.
- Pint and Vite build pass; Blade compilation passes. Rendered check in the built-in browser (synthetic data, EN and AR, desktop and 375px): no page-level overflow; the wide instalment table scrolls inside its region.
- Not done: staff acceptance, five-city or real-data parity (the reconciled desktop figures were not compared), concurrency across more than two writers, and the notification/report dependents above.

## Review pass (22 October 2026)

- Re-ran the unit/feature suite: 147 passed (6,324 assertions), one host-dependent symlink case skipped; Pint passes.
- Reviewed the finance, schedule, purchasing and executive-file services and the finance controller and found no defect. An earlier edit that widened the schedule year filter to 1990 was reverted: the desktop offers years 2018 to current + 2 (see the workflow contract), so 2018 is the intended lower bound. `FinanceHttpTest` now covers a year inside and a year below that range.
- Not re-run: the MySQL workflow and two-process race tests. No isolated test MySQL was available; the only listener (port 3306) is not a disposable test instance and was deliberately left untouched.
