# P5.2 field operations

Started 22 October 2026 on the user's instruction "Yes, start P5.2 field operations". Status: **slices S1 (special cases) and S2 (opening/closing visits and the visit schedule) implemented locally; P5.2 is not complete and no acceptance is claimed.** No physical schema change: every payload maps to the approved `op_specialcase` / `op_specialcasetype` tables.

## Scope and slicing

P5.2 covers about 11,000 lines of desktop code. It is delivered in independently testable slices:

| Slice | Desktop source | State |
|---|---|---|
| S1 Special cases | `SpecialCase_Form.cs`, `Classes/SpecialCase.cs` | implemented locally (this record) |
| S2 Opening/closing visits and visit schedule | `Visit Forms/ChooseVisitKind_Form`, `VOpening_*`, `VClosing_Form`, `Visits_Schedule_Form`, `Classes/Visit.cs`, `Classes/Office_Monitoring.cs` | implemented locally (see S2 below) |
| S3 Monitoring and evaluation (M&E) | `V_ME_Taxi_Form`, `V_ME_Other_Form`, `Classes/M_and_E.cs` | todo |
| S4 Rehabilitation follow-up | `VClosing_Rehab_Form`, `Classes/VisitRehab.cs` | todo |
| S5 Profession meetings | `ProfessionMeetings_Form.cs` (3,448 lines), `ProfessionMeetings_Schema.sql` | todo |
| S6 Visit results / statistics | `Visit_Results_Form`, `Visits_Statistics` | belongs with P6.1/P6.2 reporting |

## S1 special cases — delivered

- Page `/applications/{id}/special-cases`: lists a project's reviews and adds or edits one in place (date, review type, reason, description, action taken). Linked from the project page for accounts with the grant.
- Rules from `SpecialCase_Form.Save_button_Click:367–432`: type required; the reason must not be empty; date defaults to today. The desktop's "registrar" combo is not shown (see decision 1). Description and action are optional free text and are stored exactly as typed.
- Only changed fields are validated and written, so a legacy review with an odd value survives an unrelated edit. Edits and creation run in one transaction under the project row lock and carry a version hash of the project's reviews; a repeated or stale submission is refused with 409 and adds nothing.
- Review types are the city's own catalog (`op_specialcasetype`); a type of another city is refused. The first type by name is preselected as the desktop did.
- Two new per-city capabilities, `special_cases.view` and `special_cases.manage`, each combined with `applications.view`; audit events `special_cases.view` / `special_cases.saved`. English and Arabic (RTL) pages; view-only accounts see the text without forms.

## S2 opening/closing visits and schedule — delivered

Source: `ChooseVisitKind_Form.cs`, `VOpening_Other_Form.cs`, `VOpening_Taxi_Form.cs`, `VClosing_Form.cs`, `Classes/Visit.cs`, `Classes/Office_Monitoring.cs`, `Classes/TasksOfProjects.cs`, `Visits_Schedule_Form.cs`, `Classes/UserNotification.cs:212–272`. No schema change: `op_visit`, `op_office_monitoring`, `op_user_notification`, `op_street`, `op_task_microproject`.

- **Visit hub** `/applications/{id}/visits`: lists the recorded opening/closing visits and offers the kind the project needs. Vehicle projects (sub-category code 1 or 2, as the chooser did) use the taxi opening (`V-op`) and `V-cl`; all others use `O-op` and `O-cl`. A grant-funded rehabilitation project (type contains "ترميم" and fund type "منحة") gets neither (the chooser refused them); its closing visit is S4.
- **Questionnaires** `/applications/{id}/visits/{kind}`: the four forms are modelled as data (`VisitKinds`): wording, answer domains and stored values taken from the Designer/`get_radioButtons_values` code. Opening: yes/no = 1/0 and the loan-use share 25/50/75/100 stored as 1–4; closing stores the radio digit (so "yes" is 0 on its first question; the other choices are listed in `VisitKinds`). Unanswered is -1; slots a form does not use keep `''` / -1 as the desktop saves them. Each form also stores the beneficiary's office-monitoring rating (0 bad / 1 acceptable / 2 excellent, six items for opening and four for closing, with notes and "other comments") and indicators.
- **Rules kept:** one visit per phase (a project with any `op` or `cl` visit cannot get a second); the vehicle number is required for the taxi opening; an opening in the general form updates the project's street and address after funding; saving creates the office-monitoring row, and a new visit marks the project's assignment of legacy task 25 (opening) or 31 (closing) done when it exists; every closing save moves the project to Finished (written once).
- **Safety:** project row lock and version hash of the project's visits, office rows and place; stale or repeated submissions are refused with 409. Only changed fields are validated and written, so legacy values (including answer values outside today's choices) survive an unrelated edit, and unused slots of a legacy row are never touched. Creator is the acting account's username (up to 50 characters) in `Created_By`; `Edited_By` on edits.
- **Visit schedule** `/visit-schedule`: read-only, cross-city list of stored visit notices (`Pay_ID` = -21, no recipient) with year, month, kind (vehicle/other), visit name (Opening, M1–M5, Closing), seen and city filters. The status column follows the desktop: Done when the matching visit exists (opening/closing by kind, M1–M5 for the project's people), "N/A" for other notice texts, empty otherwise.
- Grants `visits.view` / `visits.manage` per city (plus `applications.view`); audit events `visits.view`, `visits.saved`, `visits.schedule`; navigation item for accounts with a visit grant; EN/AR pages. The English wording of the questionnaires is an unreviewed translation of the Arabic desktop text.

### S2 not built / open decisions

1. **Visitors** (`visit_user`, up to four per visit). They are historical source users and the `user` table is preservation-only (no operational table), so the web cannot offer them; the creator username is stored instead. Say if a visitor list is needed and how source users should be exposed.
2. **Deletion** of visits (and resetting tasks 25/31) is not offered.
3. **Notifications:** the desktop marks the opening notice seen on save, and generates M1–M5 notices on the first loan (P5.3). Neither is done here, so the schedule shows only already stored notices. The desktop's project-state/donor/phone/residence columns, street and type filters and Excel export (P6.2) are not built; the desktop's "current state" join condition is not reproduced.
4. **Role mapping** (desktop roles 1/8/2 may save; 3/4/7/9 refused) is not provisioned; capabilities are assigned per city.
5. The "visit status" is blank when the matching visit is not found (as the desktop's SQL yields NULL); say if "Not yet" is preferred.
## Owner decisions of 22 October 2026 and what they changed

- **Visitors:** creator username only (kept).
- **Permissions and deletion:** "make permissions for everything: read, edit, delete; a data-entry role with read/write/delete on applications, visits and special cases; record everything in activity logs."
  - New capabilities `special_cases.delete` and `visits.delete` (each also needs the view capability and `applications.view`). Deleting removes a visit with its office-monitoring row and resets the phase task (25/31) as the desktop did; deleting a review removes the review. Both write `special_cases.deleted` / `visits.deleted` audit events, and every grant change is already audited (`capability.granted` / `capability.revoked`).
  - **Imported (legacy) records are never deleted from the web.** Deleting an operational row of imported origin would break its link to the preserved source row, and the plan forbids discarding legacy data. Only records created in the web can be deleted; the page explains this. Say if imported records should also become deletable (it needs a soft-delete design).
  - `AccessRoles` defines the `data-entry` role as a bundle of per-city capabilities (people, applications, special cases, visits: view/manage and, for special cases and visits, delete). An administrator assigns it per city; each capability is still granted and audited individually. There is no administration screen yet (P5.3).
  - **Application deletion is not built.** The role does not include an applications delete capability because deleting a whole application/project cascades into people links, state history, finance and visits; it needs its own design and approval. Earlier slices (finance, people, assessments) also still have no delete capabilities.
- **Special cases "recorded by":** audit only (kept).
- **Schedule status:** "Not yet" is shown when the matching visit is not found (differs from the desktop's blank).
## Open decisions for the field owner

1. **Registrar.** `specialcase.User_ID` is a historical source user, and a source user cannot stand in for a web account (Phase 3 actor rule). New reviews therefore leave the reference unset and the audit trail records the acting account; legacy values are preserved untouched but are not displayed. Say if a visible "recorded by" is wanted and how it should be modelled.
2. **Deletion.** The desktop lets the admin and data roles delete a review. The web does not (consistent with the other historical records). 
3. **Role mapping.** Desktop roles 1/8 manage, 2 (data) saves, 3/4/7/9 are refused. No real grants are provisioned; the capabilities are assigned per city by an administrator.
4. **Not built from the form:** the "add notes" e-mail action (P5.3 notifications) and the link that opens the application (already available from the project page). A cross-project special-case list belongs with search (P6.1).

## Verification (22 October 2026)

- `SpecialCaseTest`: 5 cases / 48 assertions (create and edit with exact text, desktop validation, other-city type refused, stale and repeated submissions, legacy values preserved, grants and both languages over HTTP).
- `VisitWorkflowTest`: 8 cases / 109 assertions (exact opening values and side effects once, validation and kind rules, taxi questionnaire and plate number, closing choices and Finished once, legacy values preserved, rehabilitation exclusion, pages in both languages with grants, schedule filters/status/city permissions).
- SQLite only. Not done: actual-schema MySQL tests for S1/S2, rendered browser check, five-city data parity, staff acceptance, slices S3–S5.
