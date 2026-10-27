# Export role rules: three owner decisions with proposals

## Decisions (owner, 27 October 2026)

| Question | Answer | What was built |
|---|---|---|
| 0. What is a desktop "manager" (role 5)? | An administrator. The two roles are one. | Desktop role 5 maps to the administrator role in the legacy staff adoption (with 1 and 8). |
| Payment export (decision 1) | Option A. Cashiers export payments. | New grant `finance.export`. The cashier duty is merged into the financial role (below), so administrator and financial hold it; legal, data entry, media, guest, communication and monitoring do not. |
| Attachment types (decision 2) | Option A; "Financial gets attachments" means the financial papers, as in the desktop. | Grants `attachments.personal`, `attachments.legal`, `attachments.financial`, checked with `attachments.download`: administrator all three; data entry personal and legal; financial financial; legal legal. They decide which images open and which are listed in the attachments report. |
| General export rule (decision 3) | Option B. Communication cannot export. | New grant `reports.export` on the details grids, application list, search reports, visit schedule, task board and statistics: administrator, data entry, financial and monitoring. Not legal, media, guest, communication. Statistics additionally needs `applications.manage` (administrator and data entry, the desktop's set). |
| Cashier | The same as financial: merge. | The `cashier` role is removed. The desktop's cashier flag now gives the financial role. Payment notices go to the financial role. `staff:sync-export-roles` converts existing cashier assignments. |

Not changed: lookup exports stay with `applications.manage`, the audit log with `accounts.manage`, the notification export is the account's own box. Files stored without an image type (the `file` table) are not classified and keep the download grant alone.

Prepared 26 October 2026 for Phase 6 (P6.2). **Status: decided by the owner on 27 October 2026 and implemented the same day (see "Decisions" below).** The web currently applies each page's own view grant to its export, so these three desktop restrictions are not reproduced. Each decision below has the evidence, what the web does today, options, a recommendation and what the change would cost. Answer by naming the option for each decision (A/B/C); until then the current behaviour stays and is recorded in the delivery board.

## What the desktop does

Every export button checks the user's role, but not the same way. Role numbers are the desktop's (1 Admin, 8 L0 Admin, 2 Data, 3 Financial, 4 Guest, 5 "manager" per a code comment, 6 Out of service, 7 Lawful, 9 Media).

| Export (desktop form) | Refused for | Allowed only for |
|---|---|---|
| Payments schedule (`Payments_Form.cs:230`) | 4 Guest, 6 Out of service, 7 Lawful | |
| Search reports, both buttons (`Search_Form.cs:3069`, `:3089`) | 4, 6, 7, 9 Media | |
| Details grids: project states, family members, statistics drill-down (`ShowDetails_Form.cs:220`) | 4, 6, 7, 9 | |
| Visit results (`Visit_Results_Form.cs:611`) | 4, 6, 7, 9 | |
| Visit schedule (`Visits_Schedule_Form.cs:472`) | 4, 6, 7 | |
| Lookup forms: donors, aid sources, educations, … (`AddNew*.cs`) | 4, 6, 7 | |
| Statistics (`Statistics_Form.cs:564`, comment "Admin, admin_l0, manager, data") | everyone else | 1, 8, 5, 2 |
| Log (`Log_Form.cs:309`) | everyone else | 1, 8, 5, 3 |
| Task board, notifications | no role check found in the export code | |

So there is one general rule, "no export for Guest, Out of service and Lawful (and Media for most)", plus two stricter ones (statistics, log). Out of service is not a web role (a disabled account). The web has no "manager" role (the approved role table has none; the source documents say other ids such as 5 must be denied by default until the owner confirms). Question 0 for the owner: what is a desktop "manager" in the web (administrator, data entry, or a new read-only role)?

## What the web does today

| Export | Web grant | Roles that hold it (approved role table) |
|---|---|---|
| Instalment schedule | `finance.view` + `applications.view` | administrator, financial, **legal**, cashier |
| Search reports | the mode's view grants (`finance.view`, `people.view`, `assessments.view`, …) | varies by mode |
| Details: state history, family members | `applications.view`; `people.view` | every role that can open the page, **including guest, media and legal** |
| Attachments report | `attachments.download` + `projects.view` + `applications.view` | administrator, data entry, financial, legal; **all image types** |
| Statistics | `applications.view` | every role that can read applications, **including guest, media and legal** (the desktop allows only administrator, manager and data entry) |
| Visit schedule, task board | `visits.view` / `tasks.view` + `applications.view` | |
| Lookups | `applications.manage` | administrator, data entry |
| Audit log | `accounts.manage` | administrator |

Differences from the desktop: Lawful (and cashier, by extension) can export payments; Guest, Media and Legal can export the details grids; and every attachment-capable role sees every image type in the report and can download it.

---

## Decision 1: payment export

**Desktop:** refused for Guest, Out of service, Lawful. Media is not refused but has no finance menu, so cannot reach it.

**Options**

- **A. New grant `finance.export` (recommended).** Part of the bundles: administrator, financial and cashier. Not legal, data entry, media, guest. Lawful stays read-only on finance, which is what the desktop meant. Cashier is included because the schedule is their working list; strike it if cashiers should only read.
- **B. Keep `finance.view` (today).** Simplest, but Legal (and any cashier) can take the whole schedule out; deviates from the desktop.
- **C. Administrator and financial only.** Strictest; cashiers lose the export they may use daily.

**Recommendation: A.** Cost: one capability added to the grant list and to three role bundles, a check in the payments export and in the loan report export (it shows money), tests, and a one-off grant for existing accounts holding those roles (no schema change; grants are rows). Questions: (1) cashier yes or no? (2) should the loan search report follow the same grant? (recommended yes, it exposes the same amounts).

## Decision 2: attachment types by role

**Desktop (`Search_Form.cs:1444`, `Images.cs:131`, `Attachments_Form.cs:502`):** one rule, used when listing, adding and viewing images. There are only three image categories (Personal, Legal, Financial), and each role sees:

| Role | Personal | Legal | Financial |
|---|---|---|---|
| Administrator (1, 8) | yes | yes | yes |
| Data entry (2) | yes | yes | no |
| Financial (3) | no | no | yes |
| Lawful (7) | no | yes | no |
| Guest (4), Media (9) | the Attachments form refuses them; but the search report applies no filter to them (a desktop gap) | | |

**Size of what the rule hides** (images in the restored AlWadi and Latakia sources, 9,553 in all): Personal 5,449 (IDs, profile photos, church recommendations, certificates), Legal 2,443 (contracts, trust deeds, detention papers, rent), Financial 1,661 (invoices, delivery protocols, procurement documents, receipts). Without the rule a financial or legal account sees the identity papers of every beneficiary.

**Web today:** the download path needs `attachments.download` and `projects.view` and checks the owner project, but not the image type. The new attachments report lists metadata (project, type, category; never a path or a file) for every type. The approved role table says the Financial role gets "attachments" without saying which.

**Options**

- **A. Desktop parity through three grants (recommended).** `attachments.personal`, `attachments.legal`, `attachments.financial`, checked together with `attachments.download`: administrator all three; data entry personal and legal; financial financial only; legal legal only. The same check governs the download path and the report. Each grant stays individually revocable and audited.
- **B. Keep one grant (today).** No work; every attachment role sees every type. A regression against the desktop for Financial and Legal staff.
- **C. Stricter.** Personal papers only to administrators; data entry sees legal only. Safest for privacy; breaks the daily work of the data-entry staff the desktop lets handle IDs.

**Recommendation: A**, reading "Financial gets attachments" as financial papers only (the desktop's meaning). Cost: the image type is already in the imported data (`imagetype.Category` through the image's type link), so no schema change; the download check must read the type of the stored image (the frozen attachment catalog holds the file reference and its checksums, not the image type, so the check looks the type up through the image row), the report adds a filter, a tests round, and a one-off grant for existing accounts. Questions: (1) confirm the table above, in particular that data entry sees legal papers and financial staff see no identity papers; (2) should Guest and Media stay without attachments (the web gives them no grant, so yes by default)?

## Decision 3: details grids and the general export rule

**Desktop:** the details grids (project state history, family members, statistics drill-down) refuse Guest, Out of service, Lawful and Media; the same four are refused for search reports and visit results.

**Web today:** any account that can open the page can export. State history includes reasons and notes only for lifecycle managers; family members shows names, mother, marital status, work and date of birth.

**Options**

- **A. New grant `reports.export` for the details grids only.**
- **B. The same grant for every data export (recommended):** details grids, search reports, visit schedule, task board and the statistics page. Bundles: administrator, data entry, financial, monitoring. Not legal, media, guest; communication and cashier only read. Statistics keeps the desktop's stricter set (administrator, data entry, manager) as its own check (today any role that reads applications can export it), the audit log stays administrator-only (`accounts.manage`), lookups stay data entry and administrator (`applications.manage`).
- **C. Keep the view grants (today).** No work; Guest, Media and Legal can export people's data.

**Recommendation: B.** One rule that matches what the desktop did on all its export buttons; a person can still read everything their role reads, but cannot take it away in bulk without the grant. Cost: one capability, the check in each export service (they all run through the same export layer, so it is one place per service), role bundles, tests for each export refusing an account without it, and the one-off grant. Questions: (1) is communication or cashier allowed to export? (the proposal says no; they can still see the data on screen); (2) what is a "manager" (Question 0), since the desktop lets managers export statistics and the log?

---

## What is not changed by any option

Row-level scope stays as it is: a city the account cannot read never appears in any export, and every export is audited. Out of service stays a disabled account. The exports of the notification inbox (own notices only) and of the audit log are unaffected.

## If the owner accepts the recommendations

Implementation order: add the capabilities (`finance.export`, `reports.export`, `attachments.personal|legal|financial`) to the grant list and the role bundles; check them in the exports and the download path; a command that grants the new capabilities to accounts that already hold the matching role (so no one loses access silently); tests per role for each export and each image category; update the oracle and the delivery records. Estimated at one work session plus the owner's review of the role table.
