# P0.2 data and file discovery

Prepared 16 October 2026. Static discovery is implemented; independent review is required before marking the task done. No database restore, live connection, FTP access, SQL execution, application scaffold or desktop-source modification occurred.

## Coverage and reproducibility

`p0-2-table-register.md` explicitly classifies all **127 distinct names**. `p0-2-data-inventory.json` carries each of the **616 city/table instances**, full column definitions, PKs, unique keys, declared FKs and actions, parsed INSERT tuple counts, dump file/line evidence, differences by column, and undeclared-link candidates. Full original DDL including other indexes remains in `source-schemas.json`. Classification is an inventory category, not approval to discard or merge anything. Names do not establish equivalence across sources.

Run from this folder:

```powershell
& 'C:\Users\HC-DUO\.cache\codex-runtimes\codex-primary-runtime\dependencies\python\python.exe' .\p0-2-inventory.py
```

Observed result: 127 names; 60 procedure definitions (12 per city); all five ZIP CRCs and ZIP/SQL SHA-256 hashes match `backup-audit.json`. Source SQL routines are also inventoried separately. The parser reads ZIP contents in memory and emits metadata only. It asserts distinct table coverage, per-city microproject counts and procedure counts. Parsing INSERT tuples is format-dependent: supplied single-line INSERT statements, SQL quoted strings removed before counting tuple openings. These counts are not integrity certification and do not replace restored `COUNT(*)`, engine/charset verification or orphan profiling in P1.1.

| City | Tables | Parsed project tuples | Parsed all-table tuples | Restored counts |
|---|---:|---:|---:|---|
| Aleppo | 122 | 3,932 | 459,444 | Not run |
| Damascus | 125 | 2,363 | 359,368 | Not run |
| Homs | 124 | 2,974 | 673,943 | Not run |
| Latakia | 122 | 593 | 119,161 | Not run |
| AlWadi | 123 | 650 | 109,783 | Not run |

The project total is 10,512 for these hashes only. Every record and relationship requires source qualification, including lookup rows and historical actors. Preserve all composite-key components. Keyless tables require snapshot ordinal identity; Aleppo `t1` is a concrete keyless example. Do not collapse duplicate rows.

## Schema differences and uncertain tables

The register reports DDL variant count after ignoring only the AUTO_INCREMENT sequence counter. The JSON enumerates exact city-specific differing column definitions (including charset/nullability), missing columns, primary/unique keys and FKs; this deliberately does not silently classify collation differences as harmless. Full DDL stays authoritative for nonunique indexes. Compare key arrays as well as columns before P1 design.

`loan` FLOAT/DOUBLE and nullability variants, and `user` uniqueness variants are material. Do not round financial fields or choose a common numeric type before restored profiling. Missing tables are represented by absence of the city entry, never by a zero count. `dept`, `resultstable`, `temp_gender`, `visit_schedule_temp`, `work_exp`, and `t1` remain explicitly uncertain/auxiliary and retained. A temporary-sounding name does not justify exclusion; stored routines create/use result intermediates.

## Stored SQL and source disagreements

All 60 dump procedures are individually listed with archive member/line, definition hash, referenced table names and statement verbs. The dump set includes `beneficiaries_me_answers`, `beneficiaries_visit_answers`, `calculate_last_me_results`, `calculate_profit_rates`, `count_mevisit_results`, `count_visit_results`, `empty_database`, `print_checklist`, `select_fullapplication_details`, `select_loan_details`, `visit_results`, and `visit_user_statistics`. Definitions are source-local, not assumed equal by name. No function/trigger/event CREATE matched the supplied dump scan; isolated restore inspection must confirm executable-comment/delimiter behavior before execution.

The source file `MyWorkApplication/Procedures/all_procedures.sql` defines only eight procedures at lines 11, 53, 112, 148, 195, 229, 331 and 370. It includes `mevisit_results`, `select_application_details`, `select_checklist_details`, which differ in name from current dump contracts. It also executes `SET GLOBAL group_concat_max_len` at line 14. `empty_database` is destructive (line 112 onward), despite routine metadata such as `NO SQL` elsewhere. Never execute this file wholesale against any live connection. The routine inventory is evidence for workflow/report parity, not a deployment script. P1/P6 must resolve actual code CALL contracts and result columns against each city's definitions.

## Relationships not protected by declared foreign keys

All candidates are listed per city in `undeclared_link_candidates`; this is a heuristic, not proof that every text-encoded relationship is discoverable from column naming. Confirmed application evidence below is relative to `D:\MyProjects\MicroProject newTest\MyWorkApplication`.

| Relationship | Code evidence | Preservation contract |
|---|---|---|
| `microproject_ag_answer.Question_ID` → `agquestion.ID`, `Answer_ID` → `aganswer.ID` | `Classes/AgricultureApplication.cs:70`, joins at 74–75 | Composite PK does not imply FKs. Map both with the same source; validate answer belongs to question. |
| `education.Type_ID` → `educationtype.ID` | `Classes/Education.cs:55` | Application subquery resolves type without a declared FK; retain orphan anomalies. |
| `log.Log_User` → historical username/text | `Classes/Log.cs:21`; `Classes/Images.cs:24` | Preserve actor text, table discriminator and message verbatim. Do not replace by a merged account ID. |
| `Created_By` / `Edited_By` on visits, M&E and rehabilitation | `Classes/M_and_E.cs:22`, `Classes/RehabApplication.cs:13`, `Classes/Visit.cs:25` | Preserve original text and optional source-local resolved actor independently. |
| `user_notification.Pay_ID` | `Loans_Form.cs:1082–1110`; `Classes/UserNotification.cs:122–127`, `:259` | Observed overloaded reference: payment IDs, executive-file IDs (with body discriminator), and visit sentinel -21. Preserve raw value, body/type and source; map confirmed positive references by type, retain sentinels unchanged. Never impose one blanket payment FK. Profile other uses in P1. |
| `t1.Person_ID`, `MicroProject_ID` | Per-city DDL candidates | Keyless auxiliary records remain retained with source/snapshot ordinal; purpose unresolved. |
| Project IDs embedded in file names and log text | `Application_New_Form.cs:7923`, `Attachments_Form.cs:581`, `Classes/Images.cs:25` | Preserve embedded values and original paths; never replace embedded number by new internal ID. |

`QR_Form.cs:34` generates a QR from arbitrary textbox text, with fixed local output paths at 39/41. Discovery does not establish a canonical project URL encoded in historical QR assets. Any legacy QR migration needs payload sampling in an isolated/private environment before redirects are specified.

## External files and BLOB rules

| Evidence | Observed behavior / consequence |
|---|---|
| `Program.cs:107–108` | Separate online and office FTP roots. Hostnames/credentials must remain server-side; no reachability tested. |
| `Classes/Document.cs:23–37` | Builds each path as configured root + slash + supplied name. Offline mode writes office storage and then still attempts online storage. A failed second write can leave split copies; mode is not proof of synchronization. |
| `Classes/Document.cs:43`, `:55`, `:67` | Existence checks and copy helpers operate on original names/paths. Do not normalize case, separators or prefixes during discovery/import. |
| `Application_New_Form.cs:7923–7937` | Profile filename is original project number + `_profile_` + original basename. Collision loop repeatedly prefixes `(1)` while either store reports existence; database stores actual resulting filename. |
| `Attachments_Form.cs:579–590` | Other attachment filename is original project number + `_img` + current `yyyyMMdd` + `_` + basename, with collision checks in both stores. This is not a globally source-qualified naming scheme. |
| `Classes/Images.cs:18`, `:43` | `image_NewPath` persisted with source-local project/person/type links. Use stored value rather than regenerating a guessed filename. |
| `Classes/Document.cs:86–113` | `GetImageByName` prefers office existence; online branch downloads the office path. Its null guard uses OR and can dereference null. Reported existence is not evidence of correct bytes returned. |
| DDL `file.File_Content`, `manualfile.Content`, `manualfile.Path`, `image.image_NewPath` | Inventory both embedded bytes and external references. Preserve BLOB bytes, source key, original path and storage alias separately. |

Proposed catalog interface for later phases: source database/city, source table, canonical original key (or snapshot ordinal), original path, storage alias, BLOB/external kind, size, SHA-256, verification status, missing/conflict exception. These are specification fields only; no schema or adapter was created. Never key a file only by project number. New uploads need collision-resistant source-qualified paths; legacy files remain in place. `Program.cs:110–143` appends city-specific folders to both roots, so equal numbers in correctly routed different cities do not inherently collide. Risks include same-city duplicate names, failed existence checks, missing/incorrect routing and divergent online/offline bytes. Preserve city/root alias/name together.

No file bytes were downloaded, hashed, backed up or compared between stores. File completeness, conflicts, access transport, hosting reachability and independent restored backup remain release blockers. Raw personal rows, file names from business rows and secrets are excluded from the artifacts.

## Handoff

- Delivered: complete table classification/key inventory, per-city schema differences/counts/evidence, dump and source routine inventory, known application-only links/candidates and file path contract.
- Changed files: `p0-2-inventory.py`, `p0-2-data-inventory.json`, `p0-2-table-register.md`, `p0-2-data-and-files.md`.
- Interfaces: documentation-only proposed catalog fields; no application interfaces changed.
- Verification: static script passed counts/hash/CRC assertions; no restored or live checks claimed. Independent reviewer must validate coverage and sample parser outputs.
- Data effects: none; metadata outputs only.
- Risks: restore/profile coverage, routine/source disagreements, unresolved auxiliary tables and candidate links, online/offline conflict and attachment completeness remain explicitly open.
- Next: coordinator P0.4 uses these constraints; P1.1 isolated restores precede target schema/mapping approval. No physical migrations authorized by this inventory.
