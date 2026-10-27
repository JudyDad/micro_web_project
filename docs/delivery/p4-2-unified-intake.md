# P4.2 revision: one-form application intake

27 October 2026. On the user's instruction: "rebuild the application creation screen as one form holding the beneficiary, the family and the project with one save, as in the desktop program, and keep the people directory for search and editing only." Status: implemented locally with automated evidence on SQLite; MySQL re-run, rendered browser check and staff acceptance remain open, as for the rest of Phase 4.

## Why

The original P4.1/P4.2 split put person creation on `/people/create` and let `/applications/create` only link an existing person from a 500-row list. That was an artefact of the task decomposition in the execution plan (two tasks, two owners, disjoint files), not a desktop behavior: `Application_New_Form.cs` has no standalone person form. Its `Save_Application` treats a person with `Person_ID <= 0` as new inside the same form (`:4031`), validates the applicant's personal information before any save (`validateBenificiry`, `:4345–4470`), requires the family book number for a new applicant (`:5566`), and writes `person`, `family`, `person_family`, `microproject` and `person_microproject` in one SQL batch using `LAST_INSERT_ID()` (`:4618`, `:6103–6110`). No scope decision ever recorded the split as intentional.

## What changed

- **One screen, three sections.** `/applications/create` now has "Applicant personal information", "Family book" and "Project" sections and one Save. The applicant section offers *New applicant* (the people directory's own fields, posted as `person[fields]`, `person[null_fields]`, `person[references]`) or *Existing person* (the previous select). Accounts without `people.manage` in that city see only the existing-person choice.
- **One transaction.** `ApplicationService::intake` validates every section first and reports all problems at once, then creates the person (through `PeopleService::create`), the family (`PeopleService::createFamily`), the membership row and finally the project through the unchanged `create()`. A failure anywhere writes nothing (tested: a bad request date rolls back the new person).
- **Desktop applicant checks, preliminary included.** A new applicant must have first, last, father's and mother's names, sex, national number, birth date, referral source (intermediary), detailed address, at least one of mobile / second mobile / home phone, and a selected marital status, parish, street and housing type. The two priests are required only at full-application time, as the desktop (`ApplicationState == "project"`). The 11-digit national-number length stays a desktop warning, not a rejection, and is not enforced.
- **Family book, as the desktop writes it.** A new applicant must give a family number. The membership row is `Family_ID`, `Person_ID` = the applicant, `P_Provider_ID` = the applicant, `Relation_ID` = the city's relation named exactly `مستفيد` (null, as the desktop's subquery would be, when the source has no such row). An existing person may give a family number too; if they already belong to a family the form refuses and points to the person's page.
- **Both desktop prompts as explicit choices.** A matching national number lists the matching people with links and requires the *separate record* confirmation (or choosing the existing person). A matching family number lists the matching families and offers *reuse that family* or *separate family with the same number* plus confirmation. Nothing is merged or chosen silently; the people directory's own duplicate rules are unchanged.
- **Drafts.** The private draft of a new application now carries the applicant and family sections (`beneficiary_mode`, `person`, `family`) and restores them; the draft store validates those keys against the people metadata like the project keys.
- **People directory.** Unchanged for search and editing. The "Add a person" entry stays for family members and other non-applicants and now says so; applicants are created from the application form.

Files: `app/Services/Applications/ApplicationService.php` (intake, applicant checks, family plan, beneficiary relation), `app/Services/People/PeopleService.php` (`validateFields` made public), `app/Services/Drafts/ApplicationDraftStore.php`, `app/Http/Requests/ApplicationWriteRequest.php`, `app/Http/Controllers/ApplicationController.php`, `resources/views/applications/create.blade.php`, `resources/views/applications/applicant-fields.blade.php`, `resources/views/people/index.blade.php`, `resources/css/app.css`, `lang/{en,ar}/applications.php`, `lang/{en,ar}/people.php`.

## Interfaces

- `ApplicationService::intake(account, city, fields, references, person, family, stage)`; `person = {id|null, fields, references, confirm_duplicate}`, `family = {id|null, number|null, confirm_duplicate}`. `create()` keeps its signature; existing callers and tests are unchanged.
- `ApplicationService::applicantRequirements()` exposes the required field and reference keys the view marks with `*`.
- Request: `beneficiary_mode` (`new`|`existing`; absent with a `beneficiary_id` means existing), `person[...]`, `family[id|number|confirm_duplicate]`.
- Error keys: `person.fields.<column>`, `person.references.<key>`, `person.duplicate`, `family.number`, `family.duplicate`, `family.existing`, project keys as before.

## Verification (27 October 2026)

`tests/Feature/UnifiedIntakeTest.php`, 8 cases: one save writes person, family, membership (provider and relation as the desktop), project and link with the four audit events and the application page opens; every section is checked before anything is written (Arabic father-name message); a project error rolls back the new applicant; a duplicate national number needs confirmation and creates a separate record, never merging; a matching family number is reused only when chosen, otherwise confirmed as separate; the existing-person path still links without touching the directory and an empty choice is refused; an existing person joins a family once; a private draft restores all three sections without writing. Together with `ApplicationHttpTest` and `IntakeAcceptanceTest`: 22 tests / 286 assertions pass. Full unit/feature suite: 318 tests / 9,020 assertions pass, one host-dependent symlink case skipped. Pint passes on the changed files; the Vite build and compiled Blade syntax checks pass. SQLite only; the MySQL actual-schema test, rendered browser check and staff acceptance remain open.

## Not done / open

- Full-intake sections (story, work, education, support rows, family members) are still entered on the application page after creation, as before; the one form covers what the desktop requires to create a preliminary application.
- Sex is a free text field as in P4.1; the desktop uses a two-value list.
- The relation named `مستفيد` is matched by exact text as the desktop does; a source spelling it differently gets a null relation, visible on the person's page.
