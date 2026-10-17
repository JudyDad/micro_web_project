# P3.0 application relationship review

Prepared 17 October 2026. Owner: application reviewer, separate from the schema author. Status: requirements and generated draft-schema review completed within the boundary below. This is design evidence, not user physical-schema acceptance, importer execution, or Phase 3 completion.

## Evidence and boundary

Read the repository instructions, execution plan, current unified-account revision, Phase 0 workflow/data inventories, approved Phase 1 preservation contract, mapping/relationship reviews and restored aggregate summary. Rechecked selected desktop classes read-only: `Classes/Application_Queries.cs`, `Classes/UserNotification.cs`, `Classes/M_and_E.cs` and `Classes/Log.cs` under `D:\MyProjects\MicroProject newTest\MyWorkApplication`. No source writes, database connections, FTP access or personal records were used. The Phase 1 summary records 616 restored tables, 1,721,699 rows, 10,512 projects and zero declared-FK orphan links for its frozen snapshot. That does not establish resolution of application-only links or overloaded notification values.

## Required operational contracts

| Area | Application requirement and review criterion |
|---|---|
| Shared tables | One shared table per reviewed business relation, carrying technical identity and city; source-specific typed tables remain evidence. A cross-city query adapter alone does not implement the final operational schema. All 127 original names and 3,947 city-specific column occurrences need explicit dispositions, including retained auxiliary tables. |
| Identity and provenance | Source + snapshot + table + complete original key identify an imported occurrence. City alone is insufficient if multiple source/snapshot occurrences exist. Technical operational identity must remain separate from preservation occurrence identity, with explicit lineage and reviewed continuity across replacement snapshots. No numeric-ID, national-number, name, username or credential matching silently merges entities. |
| Relationship boundaries | Same-city parent links alone do not prevent same-city, different-source or different-snapshot contamination. Imported resolved links need source/snapshot proof through a complete provenance map and constraints or independently checked invariants. Raw FK values remain unchanged when null, orphaned, ambiguous or sentinel. |
| Projects and numbers | Preserve original project number and city; allocate internal IDs independently. Existing source number sequences remain per source until an explicitly reviewed city/source allocation policy handles additional sources. Do not enforce uniqueness on city + original number across retained replacement snapshots without a continuing-identity model. |
| Association rows | `person_microproject` has its own PMP_ID and report identity (`Classes/Select.cs:90`, Phase 1 review). Do not replace it with a deduplicated person/project pivot. `person_family.Person_ID` and `P_Provider_ID` are separate member/provider roles (`Classes/Application_Queries.cs:69–81,109`). |
| Agriculture | Preserve the ordered `(MicroProject_ID, Question_ID, Answer_ID)` key and multiple answers under a project/question. Resolve question and answer independently and flag incompatible answer/question combinations rather than repairing them. |
| M&E and visit scope | `mevisit.Person_ID` attaches to a beneficiary; project reports traverse `person_microproject` (`Classes/M_and_E.cs:174–176,212,254`). Do not invent a mandatory project FK or choose one project when a person has several associations. Preserve actual schema role and report join behavior. |
| Historical actors | `log.Log_User`, visit/M&E/rehab `Created_By` and `Edited_By` remain exact historical strings. Optional source-local actor resolution supplements those values; it never replaces them with global web-account identity. Equal names across cities are not actor continuity evidence. New web events can reference global accounts separately. |
| Notifications | `user_notification.Pay_ID` is not one payment FK. Keep raw value/body/project and sender/recipient source users; any typed target requires verified discriminator evidence and source/project agreement. Executive creation uses -1199; visit/clearance uses -21; messages use -100 and project -1; status/replacement values use dynamic -998 ± recipient index. Positive IDs alone prove no payment link. Null, sentinel, unresolved and resolved remain distinct. |
| Auxiliary rows | Keyless duplicate occurrences in `t1`, `dept`, `temp_gender`, `work_exp`, `resultstable` and `visit_schedule_temp` stay separate. Frozen INSERT-stream ordinal identity is not persistent business identity across new snapshots. Inferred field names do not justify mandatory relations or table removal. |
| Files | Preserve original paths and project numbers embedded in names/text. Attachment identity needs source/table/record/root alias; shared project IDs must not rewrite legacy names. BLOB bytes and external-file references have separate verification states. No FTP completeness claim follows from database row counts. |
| Accounts and permissions | Reviewed legacy links do not confer city access. Historical source users can survive without login accounts. Operational pages/files/exports require current account capability for each record city; no global active-city mode or union of permissions between cities. |

## Cross-source schema variants

Inspected the inventory's per-city definitions rather than inferring a common schema from table names.

- `loan.Loan_ReturnedAmount` and `Loan_PaymentsAmount` use FLOAT in Aleppo/Damascus/Latakia and DOUBLE in Homs/AlWadi; payments amount also differs in nullability/default. No decimal conversion, rounding or arbitrary reconciliation tolerance is implied. Any widening needs an exact comparison contract and tests.
- `person.P_Goal`, `P_GoalHow`, `P_OtherNotes` and `P_WorkYears` are absent in some city schemas. Mapping must distinguish source-column absence from present SQL NULL and empty text. A nullable superset alone cannot express all three unless source-schema presence remains available through lineage.
- `person_checklistmembers.Member_ID` and `t1.Person_ID`/`MicroProject_ID` differ in nullability. Target constraints must admit preserved historic rows without manufacturing values.
- `payment.Pay_Amount` uses legacy FLOAT(24,0), with differing default clauses. An insert default must not replace an imported explicit NULL/zero or change stored precision.
- Profession-meeting fields include utf8mb3_unicode_ci; many other fields use utf8mb4_bin. Source comparison and text fidelity cannot be inferred from a blanket target collation. Lookup labels and IDs remain source-qualified even when equal.
- `user` uniqueness varies by source. Shared historical source-user storage must not acquire the global web-account username uniqueness rule.

The version-one preservation manifest remains frozen. Operational projection transformations must be explicit per column and independently reconcilable to those typed values.

## Design walkthroughs required

1. Equal project/person/lookup IDs in two cities produce distinct operational IDs; same-city different snapshots cannot resolve against each other accidentally.
2. Two project associations for one person retain both association identities; a beneficiary visit does not acquire an arbitrary project.
3. Equal historical actor strings from different sources remain separate evidence and do not activate/link a login.
4. Two equal keyless rows remain two rows on rerun; a replacement snapshot requires reviewed identity reconciliation.
5. Two agriculture answers per question survive; a mismatched answer/question yields an exception without rewriting raw values.
6. Notification -1199, -21, -100, project -1, dynamic sentinel and coincident positive payment/executive IDs remain distinguishable.
7. Absent person column, present NULL, empty string and zero are distinguishable through provenance; FLOAT and DOUBLE representative values retain the specified comparison representation.
8. Missing file, divergent file copies, SQL BLOB NULL and zero-length BLOB have separate outcomes.

These are review cases, not executed importer tests. Domain permissions, financial formulas and state transitions still require their planned owner/parity reviews; schema design must not invent them.

## Draft-schema review record

Inspected `../planning/phase-3/build-operational-schema.py`, `operational-schema.json`, `schema-physical.md` and `schema-coverage.json`. Independently queried the generated relationship/index lists for person/project associations, agriculture answers, M&E, loans, people, notifications and logs. Results:

- `op_person_microproject` retains association payload/identity without a new unique person/project pair; `op_microproject_ag_answer` retains raw components and separate question/answer links without a new unique project/question pair.
- `op_mevisit` resolves Person_ID only and does not invent a project FK. Raw actor fields remain payload, independent of login tables.
- Loan FLOAT and DOUBLE variants receive separate exact typed payload columns. Person column presence is recorded per city; text definitions retain explicit original charset/collation. Nullable projection columns require source-presence/nullability checks by the importer; database nullability alone is not source validation.
- Notifications have no blanket Pay_ID FK. The unresolved discriminator is explicit; existing project/user links retain nullable resolution states and raw values. No silent sentinel repair is implied.
- The proposed origin ledger ties source records to exact source/table/snapshot and operational entity. Supplemental resolved parent IDs constrain source/city; same-snapshot origin correspondence remains a mandatory importer/reconciler invariant, not a guarantee of those FKs alone.
- The generated proposal maps 590 city/table instances to 118 shared tables and retains 26 instances under nine preservation-only names. The latter include source users/credentials, system metadata and unresolved auxiliary tables. This is an explicit proposal disposition, not approval to delete their data or omit later user/lookup workflows. Source-user references point to preserved source records, preserving historical identity without credential duplication.

The draft is suitable for presenting as a physical-schema proposal, subject to its explicit limitations. Do not represent this review as database execution, complete semantic workflow certification or approval of deferred support schemas. File catalogs, typed notification targets and optional historical-actor resolution require enumerated physical addenda before their own migrations. The shared tables presently carry exact legacy-shaped payloads with technical links; future write services still need field/state/financial validation and permission reviews.

Replacement snapshots are a specific boundary: `operational_entities` has a source/table/first-key uniqueness constraint without snapshot. Its lookup cannot authorize continuity by numeric key alone. No later snapshot may add origins or overwrite a shared row until an explicit continuity/replacement policy handles key reuse, altered payload and web edits. The first-import/rerun scope is reviewable; general replacement-snapshot importing is not delivered.

P3.1 migrations/importer depend on the newly proposed physical schema gate; prior preservation/account approvals cover their existing designs only. Independent full-coverage/static physical checks belong to the schema validation/reconciliation reviewer; the application review above samples the identified high-impact contracts.

Changed file: this report only. Interfaces: documentation constraints and review cases. Migration/data effects: none. Checks: targeted read-only source searches and per-city metadata comparisons. No commit by this specialist; coordinator integrates and records final review evidence.
