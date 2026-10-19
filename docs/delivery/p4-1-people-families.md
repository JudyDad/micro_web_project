# P4.1 people and families — initial implementation

19 October 2026. Status: implemented local workflow; acceptance review remains open. This starts Phase 4 alongside the unfinished Phase 3 rehearsal. No physical schema, private business records or real staff grants changed.

## Delivered behavior

- English/Arabic people directory, city filters, literal name/mobile/national-number search and exact family-number search. National-number matching includes one added/removed leading zero. Existing people remain separate; creates and changed national numbers require explicit confirmation when accessible duplicates exist.
- Person creation and editing against the approved operational schema. Web-created entities have `identity_kind=web`, no fake legacy keys and no invented origins. Payload validation uses explicit allowlists and approved field widths/types. Minimal new-person requirements are first/last name and sex, distinct from later application submission validation.
- Family creation with duplicate review; explicit household member, family and optional provider selection; membership editing; education, work/experience and course creation/editing. Canonical references require matching city, source and table kind. Raw imported identifiers remain evidence and are never rewritten to web IDs.
- Separate `people.view` and `people.manage` capabilities, checked on every read/write. Existing project access does not grant these capabilities. No role mapping or real assignment was inferred. Imported operational rows require a matching published source occurrence; synthetic publications are accepted only locally/in tests.
- Transactional entity/payload/audit writes; parent-row locking and aggregate HMAC comparison reject stale person/detail/membership edits. Form recovery retains the submitted version, fields, null selections and relationship choices only for the submitted form. Missing/current lookup choices survive ordinary saves. Null, empty text, unchanged sentinel dates and historical work-status spellings remain distinct.
- Session-issued creation tokens and session locks prevent ordinary same-session double submissions from creating another person/family. At most 32 receipts remain in a session. Local session locks use the file cache store (`SESSION_BLOCK_STORE` may select a shared supported store at deployment). Receipts are not a crash-safe durable idempotency ledger.
- Metadata-only audit events identify the actor, city and operational person/family. They do not constitute full before/after change history. People responses use private/no-store caching and no-referrer headers.

## Verification

The ten P4.1 tests use a newly constructed SQLite in-memory schema and invented values. They cover city/permission isolation, disabled/revoked accounts, duplicate review on create/edit, leading-zero candidates, literal wildcard search, source publication, canonical cross-city rejection, read-only reference labels, family membership changes, stale aggregate edits, repeated HTTP root submission, Arabic/English HTTP forms, exact whitespace/sentinel retention and scoped validation recovery. They do not certify MySQL DDL, row locks or cross-process behavior.

Final verification: the unit/feature suite discovered 64 cases, with 63 passing / 5,492 assertions and one host-dependent symlink case skipped. The isolated MySQL 8.4.8 server was subsequently restarted on loopback port 33072. `PeopleOperationalWorkflowTest` applied all approved migrations to an empty `micro_projects_test`, exercised person/family/membership/education/work/course writes, checked web identities and audit rows, rolled back the fixtures, and rolled back the migrations. It passed one test / 18 assertions and verified the migration repository is empty afterwards. This validates actual-schema writes, not concurrent worker behavior. Pint, Composer validation, Blade compilation, Vite build and whitespace checks pass. Vite retains the previously known runtime font-URL notices and empty JavaScript chunk notice; the browser showed no relevant errors/warnings.

An independent source/implementation review found unavailable-reference clearing, cross-form old-input contamination, lost null selection, duplicate-edit bypass, invisible education/family links, repeated root submission and stale-version recovery. Regression coverage and fixes are included. The accepted workflow/source trace is [p4-1-workflow-contract.md](p4-1-workflow-contract.md).

Rendered browser checks used a separate synthetic SQLite fixture at loopback `127.0.0.1:18041`: login → directory → person → edit mobile → successful save, followed by Arabic RTL and a 390×844 mobile view. The mobile document did not overflow horizontally; browser warnings/errors were empty. Screenshots and fixture scripts remain outside this repository under `MicroProjects-TestRuntime`. This browser fixture uses simplified test tables and grants only an invented account.

Run checks from the repository root:

```powershell
php vendor/phpunit/phpunit/phpunit tests/Feature/PeopleWorkflowTest.php
php vendor/phpunit/phpunit/phpunit --testsuite=Unit,Feature
# With RUN_MYSQL_SCHEMA_TESTS=1 and the isolated MYSQL_TEST_* connection configured:
php vendor/phpunit/phpunit/phpunit tests/Integration/PeopleOperationalWorkflowTest.php
php vendor/bin/pint --dirty
php artisan view:cache
composer validate --no-check-publish
npm run build
git diff --check
```

## Remaining acceptance and integration

- Real concurrent requests remain to be tested on an isolated disposable target. Actual-schema MySQL writes passed as recorded above. Cross-session duplicate races and crash-between-database-commit/session-save behavior are not certified. No uniqueness rule or silent merge was added to compensate.
- Full five-city import/value/file/report reconciliation remains Phase 3 work. No private snapshot was published or modified for P4.1. Operational edits to validated imports must be coordinated with importer/reconciliation and future cutover policy.
- Staff must review intended capabilities and intake behavior; real runtime write privileges and grants need separate controlled provisioning. Existing restricted runtime grants were not widened.
- Lookup/provider choices are currently bounded to the first 500 authorized rows; existing selections outside the list are retained. Searchable larger catalogs and measured query/index performance remain review items before acceptance on realistic data.
- Person-project roles and project-specific work context integrate in P4.2. Durable drafts/recovery belong to P4.2; family-number editing, removal/archive semantics and a full edit-history ledger have not been introduced.
- Phase 4 intake/print acceptance remains P4.4. Hosting and access-boundary questions remain deferred until project completion, as requested.
