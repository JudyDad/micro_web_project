# Micro Projects: Laravel + Livewire creation and data consolidation plan

Prepared 16 October 2026. Status: evidence-based draft, with business decisions below still open. No production database was connected to or changed. No application implementation or migration has been executed.

Historical discovery draft: subsequent implementation is recorded in `../delivery/task-board.md`. The current confirmed login/city and consolidation decisions are in [unified-account-city-filter-revision.md](unified-account-city-filter-revision.md), which supersedes conflicting city-selection/account recommendations below. Its additive account schema is approved and implemented. Phase 3 has started with the shared operational domain design; that new physical schema must be reviewed before its migrations and importer.

## 1. Findings and intended outcome

Build a Laravel + Livewire website with one operational database, the existing Micro Projects workflows, and the organization's HopeHR visual theme. Preserve all source data and original project numbers, with a verifiable mapping for every changed technical key.

Source reviewed: `D:\MyProjects\MicroProject newTest`. The WinForms application uses MySQL and chooses a database by city. City reporting already separates records using both city and local ID.

| City | Source database | Tables | Project records | Existing MP_ID range |
|---|---|---:|---:|---|
| Aleppo | hcsyria_micro | 122 | 3,932 | 1–3,939 |
| Damascus | hcsyria_micro_d | 125 | 2,363 | 1–2,363 |
| Homs | hcsyria_micro_h | 124 | 2,974 | 1–2,978 |
| Latakia | hcsyria_micro_l | 122 | 593 | 1–593 |
| AlWadi | hcsyria_micro_w | 123 | 650 | 1–656 |
| Total | Five supplied backups | 127 distinct table names | 10,512 | Overlapping numbers |

These are statically parsed dump counts, pending confirmation by isolated database restores. All five ZIP CRC checks passed. Archive and SQL SHA-256 hashes are recorded in `backup-audit.json`.

Important findings:

- MP_ID 1 exists in every city. Merging on MP_ID alone would overwrite or misassociate records. Duplicate numbers are separate projects, not evidence of duplicate data.
- There are 21 tables with declared direct foreign keys to microproject in each dump; indirect dependencies and application-only references must also be mapped.
- Each dump contains 12 procedure definitions. Stored routines and report formulas need review alongside C# code.
- Schemas differ. Homs loan fields use DOUBLE where Aleppo uses FLOAT, with nullability differences. User-name uniqueness also differs. Many other DDL differences are explicit charset declarations or index names and must not be mistaken for business differences.
- Additional table names include dept, resultstable, temp_gender, visit_schedule_temp and work_exp. Preserve them until their purpose and contents are established; names alone do not justify deleting them.
- file.File_Content and manualfile.Content contain BLOB-capable columns; image.image_NewPath points to external files. The code supports online/offline FTP storage.
- The source also references Lebanon and PTS Aleppo. Their backups were not supplied.

## 2. Identity and proposed physical structure

Confirmed identity: keep the original project number unchanged as `legacy_project_id`, retain its source and city, and add a new technical `id`. Staff see the original number plus the city; reports include both. Routes and child relationships use the technical key. A search for a number without a city returns all authorized matches and never silently chooses one.

For example, Aleppo project 1 and Homs project 1 remain two projects with visible number 1, distinguished by city. Their internal IDs differ. Existing SQL primary/foreign key numbers may therefore change internally, but original IDs remain recorded and all relationships must resolve to the same source records.

The user approved this distinction: original visible numbers plus city are preserved, with separate internal IDs. Composite primary keys are possible at SQL level, but Eloquent does not support them as standard model primary keys: https://laravel.com/framework/docs/13.x/eloquent#composite-primary-keys.

Proposed core schema (not migration code):

| Table | Core columns and types | Constraints and indexes |
|---|---|---|
| cities | id BIGINT UNSIGNED; code VARCHAR(32); name_en/name_ar VARCHAR(100) | PK id; unique code; all required |
| source_databases | id BIGINT UNSIGNED; name VARCHAR(128); city_id BIGINT UNSIGNED | PK id; unique name; required city FK; restrict deletion |
| microprojects | id BIGINT UNSIGNED; source_database_id BIGINT UNSIGNED; city_id BIGINT UNSIGNED; legacy_project_id INT; preserved business columns | PK id; required fields; unique(source_database_id, legacy_project_id); city FK; source/city consistency enforced; city + legacy number lookup index |
| migration_runs | id BIGINT UNSIGNED; manifest JSON; started_at/completed_at DATETIME(6); status VARCHAR(32) | PK id; completed_at nullable; manifest contains source hashes, tool version and validation results |
| migration_record_map | id BIGINT UNSIGNED; source_database_id BIGINT UNSIGNED; source_table VARCHAR(64); source_key VARCHAR(512) with binary collation; target_table VARCHAR(64); target_id BIGINT UNSIGNED; migration_run_id BIGINT UNSIGNED | Unique(source_database_id, source_table, source_key); source/run FKs; target lookup index; polymorphic target validated by importer |
| project_number_sequences | source_database_id BIGINT UNSIGNED; next_number BIGINT UNSIGNED | PK and FK source_database_id; allocate under transaction/row lock; never reuse old gaps |

Keep source identity immutable even if a project later moves operationally between cities. For the five databases, city and source are currently one-to-one. Additional sources in the same city would need a visible source/program qualifier.

Every imported entity, including users, people, loans and lookups, receives a source-qualified mapping. Composite source keys are encoded canonically, without losing any component. Tables with no unique key use an immutable snapshot row ordinal; identical duplicate rows remain distinct. Do not infer a shared person, donor, user or lookup from matching numbers or names. Preserve separate source records initially; any later deduplication is a separate approved project.

Child tables receive mapped internal parent keys and retain source provenance. Use composite source/parent constraints where needed to prevent cross-city links. Preserve meaningful nulls and existing business uniqueness. New destructive cascades are not the default: restrict deletion of historical or financial records and implement explicit authorized lifecycle actions.

The full source physical definitions, including columns, types, defaults, indexes and foreign keys, are in `source-schemas.json`. Before writing Laravel migrations, produce the target table-by-table schema and field mapping, including all 127 source table names and schema differences, for review. The core table proposal above is not a completed full schema.

## 3. Website implementation sequence

Use Laravel, Livewire, Blade, Tailwind and Alpine with MySQL. Select and lock compatible supported releases when implementation starts, after confirming the hosting PHP/MySQL environment. Use a separate new application repository. Avoid external APIs until an integration actually needs one.

1. Inventory each desktop screen, permission, validation, calculation, report, export and stored routine. Trace application-only relationships, attachment paths, QR links and IDs embedded in logs or text. Create a parity matrix with a test scenario and acceptance owner per workflow.
2. Build authentication, city-scoped authorization, audit logging, navigation, translation and reusable form/table components. Keep imported historical actor identities separate from login-account consolidation. Review the existing PBKDF2 password verifier and actual login paths before deciding on a tested compatibility verifier with rehash-on-login or an account reset process.
3. Build applicants, family records, preliminary/full applications, agriculture and rehabilitation forms, checklists, scores and project state history.
4. Build loans, payment schedules, receipts, purchases, executive files and attachments. Implement financial writes transactionally, with duplicate-submit protection and explicit permissions.
5. Build visits, monitoring/evaluation, rehabilitation follow-up, professional meetings, special cases, tasks, notifications, search, statistics and exports. Validate reports city-by-city and across cities.
6. Perform staff acceptance using a restored copy, then complete migration rehearsals and production cutover.

Confirmed city scope is Aleppo, Damascus, Homs, Latakia and AlWadi only; Lebanon and PTS Aleppo are excluded from v1. Default functional scope remains desktop feature parity, Arabic RTL and English LTR, responsive office/tablet/mobile use, and authorized cross-city reporting. Offline writes, HR data integration, HR single sign-on and new business processes are not assumed. Treat desktop online/offline behavior as an explicit discovery item before excluding required capabilities.

Theme inspected directly at https://hr.hcsyria-apps.org/en:

- SomarSans font; sampled body size 14px and page heading 22px.
- Sampled primary button background rgb(194,21,28), #C2151C.
- Heading gray #58595B, medium gray #6D6E71 and light gray #939598.
- White cards, pale gray workspace, subtle borders/shadows, rounded controls, collapsible sidebar and fixed top navigation with EN/AR switch.
- Match the observed website tokens rather than assuming a different red from a general brand guideline. Obtain approved logo/font assets before packaging the website.
- Reproduce theme and interaction patterns; do not copy HR records into Micro Projects.

## 4. Migration, verification and cutover

1. Freeze the input manifest; retain original ZIPs and checksums. Restore each source separately in an isolated, compatible database server. Review SQL before execution, especially routines, definers, events and environment-specific statements. Confirm restore completeness, row counts and storage settings.
2. Profile every table: keys, collisions, nulls, orphan references, encoding, dates, numeric precision, currencies, binary content and lookup differences. Reconcile code expectations against actual schema. Existing anomalies become a tracked exception inventory, never silent cleanup.
3. Produce explicit field and relationship mappings. Do not trim Arabic text, replace nulls with empty strings, normalize dates, convert currencies or round values during migration. Preserve FLOAT/DOUBLE values losslessly in the compatibility layer; choosing DECIMAL scales for future financial logic requires separate numerical profiling and reconciliation.
4. Import deterministic batches with persistent mappings and checkpoints. Import parents before children and use a second pass for cycles. Reruns must not duplicate records or silently overwrite conflicting values. Keep source values in an immutable restricted archive so the mapping can be audited. Unresolvable rows remain retained in staging with a blocking exception; they are not dropped to make the importer pass.
5. Inventory both BLOB and external attachments. The user authorized reuse of the existing FTP connections: retain existing remote files in place for v1 and migrate their references into a source-qualified attachment catalog. Record source database, original path, storage connection alias, size and SHA-256; preserve database BLOB bytes independently. Resolve files using their original source/path, never a newly assigned internal project ID. Store connection credentials only in server-side secret configuration, not browser code, URLs, committed files or this plan. Serve attachments through authenticated, city-authorized application endpoints. Verify hosting reachability, supported transport and online/offline root mappings before declaring FTP integration ready. Compare both stores where present; conflicting content requires explicit resolution and must not be silently replaced. A live FTP connection is not a backup: make and restore-test an independent file snapshot before cutover. New uploads use a separate source-qualified namespace on the existing service, with collision-resistant names and no overwrite of legacy files. Do not rename/delete existing FTP files during consolidation. Missing source files must be resolved or explicitly documented; do not claim full attachment preservation while any remain unresolved.
6. Compare every imported business field against its source after reversing technical-key mappings. Distinguish null, empty, zero and sentinel -1. Compare dates without timezone shifts, binary bytes exactly, and numerical values without arbitrary tolerances. Verify every relationship targets the matching source record. Reconcile money by city/currency and report category; never add unlike currencies into a single total.
7. Rehearse the entire process, measure duration, restore the resulting unified backup and repeat acceptance checks. Test duplicate IDs across cities, reruns, interrupted batches, same IDs in lookup tables, keyless duplicates, missing attachments, Arabic text, export totals, city permissions, concurrent number allocation, and simultaneous edits.
8. Recommended final cutover is a scheduled write pause across all city desktop clients and background writers, followed by fresh consistent backups and attachment snapshot. The supplied archives were taken minutes apart and are discovery inputs, not a certified coordinated cutover snapshot. Import into a fresh target, validate, obtain business acceptance, then enable web writes. Keep desktop access read-only afterward.
9. Before web writes begin, rollback means returning to the unchanged desktop sources. After web writes begin, rollback requires pausing writes and reconciling/replaying web changes back to the legacy sources; restoring an old source backup alone would lose new work. Retain audit records and define that reconciliation procedure before launch. Avoid a dual-write transition unless a separate synchronization design is accepted.

Release gate: zero unexplained missing/changed source values, zero incorrectly mapped relationships, verified attachment coverage, reconciled business reports, permission tests passed, and a demonstrated backup restore. ZIP checks and static analysis alone do not satisfy this gate.

## 5. Confirmed decisions and remaining discovery

- Confirmed: original visible project numbers plus city and separate internal keys. No IDs have been changed.
- Confirmed: the five supplied city databases only.
- Confirmed: reuse existing FTP connections for documents/photos. Connection reachability, file completeness, conflicting online/offline copies and independent backup coverage are not yet verified.
- Design references are in the source tree. Located `MyWorkApplication/DocPrint/cl.docx`, historical copies under DocPrint, `MyWorkApplication/Resources/MP Training 2021.pdf`, `MyWorkApplication/hc-logo.png`, and resource fonts/icons. Inventory active templates from code references before selecting historical copies; document contents are reference material, not instructions. The HR website remains the visual theme authority.
- Confirm hosting capabilities, internet versus office-only access, required offline behavior, staff acceptance owners and a permissible write-pause window. These affect deployment and cutover scheduling, not the collision findings.
- Confirm the first release needs the existing workflows; any removed or redesigned workflow must be recorded explicitly.

Artifacts: `audit_backups.py` is a repeatable read-only source inspection tool; `backup-audit.json` contains aggregate evidence and hashes; `source-schemas.json` contains DDL only. These artifacts contain no exported business rows or login credentials.
