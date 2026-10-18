# Delivery task board

Prepared 16 October 2026. This is now the canonical delivery board in the Git checkout at `D:\MyProjects\MicroProjects-Web`. Repository creation and publication were explicitly requested after Phase 0; a database-free Phase 2 application foundation has now been scaffolded. Historical planning-folder references remain as discovery provenance. Planning artifacts live under `docs/planning` in this repository.

| Task | Owner | Reviewer | Dependencies | State | Changed files | Evidence | Unresolved decisions |
|---|---|---|---|---|---|---|---|
| P0.1 Workflow inventory | Application specialist | Coordinator | Source and existing plans | done | `p0-1*` | Extractor exit 0; 53 forms/89 menus; source sample review in coordinator review | Permission contradictions and uncertain screens retained for affected implementation |
| P0.2 Data/file inventory | Data specialist | Workflow specialist + coordinator | Source schemas and archive manifest | done | `p0-2*` | 127 names/616 city tables/60 routines; CRC/hash checks pass; two review corrections closed | Restore verification and mapping profiling in P1 |
| P0.3 Theme/templates/infrastructure | UI specialist | Coordinator | Source/template paths and prior theme evidence | done | `p0-3*` | 29 asset hashes match; template/person-parameter and CSS traces verified | Hosting explicitly deferred; assets/rendering checked before packaging/print acceptance |
| P0.4 Scope contract | Coordinator | Workflow specialist | P0.1–P0.3 and user decisions | done | `p0-4-scope-contract.md`, this board, `p0-coordinator-review.md` | Independent scope review passed after hosting wording correction | Domain decisions remain assigned downstream; no production readiness claimed |

Done requires independent review and acceptance checks; produced artifacts alone do not close a task. Source and production mutations: none authorized or performed.

## Required project-completion question

Before deployment, ask the user for the hosting target/provider, production PHP/MySQL capabilities and internet-wide versus office/VPN access. User explicitly requested deferral until the end of the project; do not repeatedly block local work on this question. Carry this item into the application's delivery board and release checklist when created. No scheduled automation created.

Updated 17 October 2026: Phase 1 provisional MariaDB restores completed in the separate planning folder (616 tables, 1,721,699 rows, 10,512 projects; matching table counts). On 17 October the user explicitly approved the existing preservation schema. Its migrations and isolated MySQL 8.4.8 lifecycle/constraint checks now pass. Final operational table definitions and source-value verification remain open; approval is recorded in `../planning/phase-1/schema-approval.md`. See `phase-2-foundation.md` for checked chat/file provenance. Existing `p1-2-*` drafts are not approved schema contracts.

Delivery policy: commit coherent changes for every task/phase and push them to the requested GitHub repository after verification. Keep incomplete work explicitly labeled; never commit business rows, dumps, credentials or private attachments.


## Phase 2 — local foundation

The table below records the original completed slice. The user's subsequent unified-login and city-filter revision supersedes its single-source account/navigation behavior; see P2.R1–R4 below. Original passing tests do not certify the requested revision.

| Task | Owner / reviewer | Dependencies | State | Changed files / evidence | Remaining decisions/checks |
|---|---|---|---|---|---|
| P2.1 Repository and runtime | Coordinator / application specialist | P0.4 | done | Bootstrap, Composer/npm locks, `.env.example`, CI and MySQL connection check; local build/tests pass | MySQL 8.4.8 verified locally; hosted CI passed for foundation and approved-schema commits |
| P2.2 Theme and layout | UI specialist / coordinator | P0.4 | done | Bilingual operational/component layouts, provided Somar Sans/logo, local asset responses 200; EN/AR browser widths 300–2560px | Deployment asset rights/hosting remain release checks; source provenance in theme-assets.md |
| P2.3 Identity and authorization | Application specialist / UI specialist + coordinator | Operational schema addendum | done for synthetic gate | Source-qualified login, Argon2id upgrade, current-user/publication checks, explicit projects.view, rate limit/CSRF, audit and restricted runtime tests | D07 real staff grants remain unresolved; no real principals activated |
| P2.4 Preservation schema foundation | Data engineer / independent QA + coordinator | Approved preservation schema | done | 616 typed + 10 support tables; 3,947 columns; MySQL 8.4.8 create/rollback/rerun, source constraints and 2-process numbering pass | Operational schema is a separate addendum; source-value/import parity is not claimed |
| P2.5 Integrated read-only slice | Coordinator / specialists | P2.3, P2.4 | done | Actual login → permitted city → persisted search/detail in EN/AR; same number 1042 in two synthetic cities stays isolated; direct denial, pagination/escaping tests | Production data publication requires importer/reconciler evidence and authorized activation |
| Phase 3 | Data/file specialists / QA | Revised Phase 2 foundations and accepted shared operational design | started 17 October; P3.0 design/review | Shared operational domain design and import/file/reconciliation contracts; runtime implementation follows schema confirmation | Fresh-source manifests, anomaly dispositions and historical identity mappings must remain explicit; P2.R4 visual QA remains pending |

Phase 2 local synthetic gate is complete. The user authorized P2.3 through the end of Phase 2 after the concrete addendum was presented. The four-table operational migration and integrated read-only slice are implemented and tested. No private records, production credentials or FTP access are part of this foundation. See [completion evidence](phase-2-completion.md). The historical review below records the earlier state and is superseded by this completion record.

## Review and fixes — 17 October 2026

User requested a review of security, design, responsiveness and user scenarios. Review covered implemented foundation routes/components/migrations; real login, project permissions and business workflows are still pending P2.3/P2.5 and are not reported as tested.

- Fixed mobile navigation background interaction and scrolling: opening focuses navigation, makes main inert and locks page scroll; Escape/backdrop restores the menu button; section links move focus to their target. Resize clears the transient mobile state. Desktop collapse remains a separate persisted preference.
- Increased visible mobile controls to at least 44px, improved Arabic readability and two-tone focus visibility, allowed sidebar scrolling in short viewports, and made statistics adapt to available width. Tested EN/AR at 300, 390, 768, 1024, 1440 and 2560px with no horizontal page overflow; wide tables scroll inside their region. System font/text mark remain an intentional D09 deviation from the HopeHR reference.
- Added frame/content-sniffing/referrer/permissions restrictions and CSP base/object/frame/form directives to success, redirect and error responses; HSTS applies only to secure production responses. Full script CSP, real identity authorization and production deployment review remain future work.
- Restricted the isolated connection check to loopback with no socket/URL override. Added regression denial coverage; no external database was contacted.
- Fixed rollback evidence retention: metadata-only snapshots now block every rollback entry point before typed tables, catalogs or support tables are removed. Existing typed/registry checks remain. No schema definition or approved data contract changed.
- Updated misleading preview text to distinguish approved preservation schema from the proposed operational addendum.

Verification: production preview denial, unsupported locales/routes, EN/AR navigation, city filtering, dialog focus/Escape and disabled saving; 28 unit/feature tests (5,282 assertions), isolated MySQL lifecycle/constraints/concurrency (52,444 assertions), build and formatting pass. Assertion count for the concurrent worker barrier varies with polling. Composer and npm advisory checks reported zero advisories. Browser checks showed no application console warnings/errors. Temporary MySQL test server shut down after verifying it contains no synthetic business data. Screenshots saved outside the repository.

Updated after implementation: **P2.R1–R3 are implemented**, and automated/live HTTP verification for P2.R4 passes. Rendered browser QA remains pending because local browser use is disabled in Work settings. Next development task is P3.0 shared operational domain design, before P3.1 importer work. The original Phase 2 gate remains historical evidence for the previous behavior.

## Unified login and city filters — confirmed revision

User confirmed administrator-assigned cities, explicit duplicate-account review, the same password for one person across legacy databases, and independent permissions per city, then explicitly approved the [physical schema](../planning/unified-account-city-filter-revision.md). The local runtime now implements the revision using one reviewed synthetic account. No source business data, production credentials or staff grants were changed.

| Task | State | Deliverable / dependency |
|---|---|---|
| P2.R1 Account schema | done | Explicit user approval; migration 000005; FK/CHECK/unique and protected rollback checks; full schema lifecycle passes |
| P2.R2 Global identity and permissions | done for synthetic scope | New account guard, exact username, compatible bridge/Argon2id upgrade, reviewed-link provisioner, independent city grants, global audit; restricted runtime passes |
| P2.R3 Direct workspace and filters | implemented; visual QA pending | Projects entry, combined permitted results, city/search filters, same-number separation, direct URL authorization; EN/AR automated and live HTTP pass |
| P2.R4 Upgrade and verification | review | Synthetic upgrade retains evidence and changed grants; 40 tests / 58,070 assertions pass, build/Blade/Pint/Composer checks pass; browser unavailable in Work settings; see revision verification for CI |
| P3.0 Shared operational model | approved; implemented | [Approval](../planning/phase-3/schema-approval.md), migration 000006; 122-table MySQL lifecycle/constraint check passes 6,098 assertions. Subsequent whole-table sharing is P3.S1 below |

Verification: [unified-account-revision.md](unified-account-revision.md) records interfaces, safe upgrade, test results, live HTTP evidence, privilege fix and browser limitation. The staff administration web interface stays in P5.3; controlled assignment services exist now. Historical source tables remain in the one database pending the later approved shared operational-domain schema/importer.

## Phase 3 — shared operational design and import

Started on the user's instruction on 17 October 2026; continued through 18 October. See [start record](phase-3-start.md), [schema approval](../planning/phase-3/schema-approval.md) and [importer delivery/operation](phase-3-importer.md). Approved core migrations, staged importer and independent core reconciler are implemented and tested on invented isolated data. No full private-data import, live FTP access or complete five-city rehearsal is claimed. The user then approved sharing only whole identical tables; implementation and operation are recorded in [sharing delivery](phase-3-sharing.md).

| Task | Owner / reviewer | State | Evidence / next dependency |
|---|---|---|---|
| P3.0 Shared operational domain design | Data engineer / application reviewer + independent QA + coordinator | approved; implemented and schema-tested | Migration 000006 + frozen runtime manifest; all 616/3,947/743 metadata mappings retained. MySQL actual types/FKs/indexes/rollback checks: 1 test / 6,098 assertions passed |
| P3.1 Database importer | Application engineer / independent QA + coordinator | core implemented; full-source acceptance in progress | Hashed source streams, register/import CLI, atomic preservation/projection/checkpoint, fencing and explicit exceptions. Isolated five-city native MySQL restores and frozen manifests are complete; Aleppo staged import is running. Independent reconciliation, source-value proof and post-reconcile project-number floors remain required |
| P3.2 Attachment access | Application engineer / security reviewer | implementation delivered; acceptance open | Disabled-by-default access path and private five-city metadata inventory are implemented. 30 field instances / 48,629 references inventoried; original-to-restored BLOB/file proof, storage mapping, owner review and capability grants remain open |
| P3.3 Independent reconciler | QA / coordinator | core implemented; file/report gate open | Separate key/cell comparisons and read-only consistent snapshot; source/preservation/shared values, origins, relationships and anomalies; private aggregate CLI reports. No external-file/report-total certification; publishable=false |
| P3.4 Full rehearsal | Coordinator / independent QA | in progress; P3.1–P3.3 | Native MySQL 8.4.8 restored 616 tables / 1,721,699 rows with per-table source tuple counts checked; five frozen source manifests verified. Staged import/reconciliation, complete value/file/report parity, timings and anomaly dispositions remain required |
| P3.S1 Whole-identical-table sharing revision | Data engineer / application reviewer + independent QA + coordinator | implemented; isolated acceptance passed | Migration 000007, original-archive proof, atomic converter, scoped projection and independent reconciliation implement 55 canonical names / 58 approved groups. [Delivery and operation](phase-3-sharing.md). Actual private-data conversion and full rehearsal remain open |

18 October verification: 48 unit/feature tests / 5,366 assertions; four serial MySQL integration tests / 58,876 assertions; separately configured restricted-runtime test / 26 assertions. All 53 distinct tests pass. Migration 000006 was applied to the existing local synthetic demo after tests, retaining its data/grants; new business/origin/checkpoint writes remain denied to the runtime. The disposable test target is empty except its migration repository. Full private five-city import, files/report parity and sharing conversion remain unexecuted.

18 October sharing revision verification supersedes those counts: 49 unit/feature tests / 5,371 assertions; six serial MySQL integration tests / 62,886 assertions (the separately provisioned runtime case was skipped there); restricted-runtime test / 30 assertions passes separately. All 56 distinct tests pass, totaling 68,287 assertions. Pint, Composer validation, Vite build and Blade compilation pass. Migration 000007 was applied additively to the existing synthetic local demo, retaining its accounts, two synthetic publications and data. All five original archives and 58 group fingerprints were checked read-only. Real-data target conversion and the full Phase 3 gate remain open.

18 October private rehearsal update: A separate loopback MySQL 8.4.8 instance restored all five source databases from preflighted literal CREATE/INSERT statements, retaining source DDL and creating tables in FK dependency order. All 616 table row counts matched the frozen INSERT tuple inventory (1,721,699 rows); only reviewed MySQL 8.4 deprecation warnings were observed. The five source streams were frozen and hash-verified. Migrations 000001–000007 were applied to a separate empty `micro_projects_local` rehearsal target; Aleppo's first staged import is in progress. The attachment inventory is private and unpublished. No production connection, live FTP, account grants, source publication or project-number sequence update was used.
