# P5.2 field operations

Started 22 October 2026 on the user's instruction "Yes, start P5.2 field operations". Status: **first slice only (special cases) implemented locally; P5.2 is not complete and no acceptance is claimed.** No physical schema change: every payload maps to the approved `op_specialcase` / `op_specialcasetype` tables.

## Scope and slicing

P5.2 covers about 11,000 lines of desktop code. It is delivered in independently testable slices:

| Slice | Desktop source | State |
|---|---|---|
| S1 Special cases | `SpecialCase_Form.cs`, `Classes/SpecialCase.cs` | implemented locally (this record) |
| S2 Visit schedule and visit entry | `Visit Forms/ChooseVisitKind_Form`, `VOpening_*`, `VClosing_*`, `Visits_Schedule_Form`, `Classes/Visit.cs` | todo |
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

## Open decisions for the field owner

1. **Registrar.** `specialcase.User_ID` is a historical source user, and a source user cannot stand in for a web account (Phase 3 actor rule). New reviews therefore leave the reference unset and the audit trail records the acting account; legacy values are preserved untouched but are not displayed. Say if a visible "recorded by" is wanted and how it should be modelled.
2. **Deletion.** The desktop lets the admin and data roles delete a review. The web does not (consistent with the other historical records). 
3. **Role mapping.** Desktop roles 1/8 manage, 2 (data) saves, 3/4/7/9 are refused. No real grants are provisioned; the capabilities are assigned per city by an administrator.
4. **Not built from the form:** the "add notes" e-mail action (P5.3 notifications) and the link that opens the application (already available from the project page). A cross-project special-case list belongs with search (P6.1).

## Verification (22 October 2026)

- `SpecialCaseTest`: 5 cases / 48 assertions (create and edit with exact text, desktop validation, other-city type refused, stale and repeated submissions, legacy values preserved, grants and both languages over HTTP). SQLite only; the actual-schema MySQL test for this slice is not written yet.
- Not done: MySQL workflow/race tests, rendered browser check, five-city data parity, staff acceptance, slices S2–S5.
