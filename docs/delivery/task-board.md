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

| Task | Owner / reviewer | Dependencies | State | Changed files / evidence | Remaining decisions/checks |
|---|---|---|---|---|---|
| P2.1 Repository and runtime | Coordinator / application specialist | P0.4 | done | Bootstrap, Composer/npm locks, `.env.example`, CI and MySQL connection check; local build/tests pass | MySQL 8.4.8 verified locally; hosted CI passed for foundation and approved-schema commits |
| P2.2 Theme and layout | UI specialist / coordinator | P0.4 | in-progress | `resources`, `lang`; EN/AR desktop/mobile browser checks; components, collapse, filters, dialog | D09 font/logo approval and exact reference comparison remain open |
| P2.3 Identity and authorization | Application specialist / UI specialist + coordinator | Operational schema addendum, D07 | in-progress | `LegacyPasswordVerifier`, independent .NET vectors and unit tests pass | Real login, rehash, source identity mapping, capability enforcement and audit need the newly documented operational schema/capability contract |
| P2.4 Preservation schema foundation | Data engineer / independent QA + coordinator | Approved preservation schema | done | 616 typed + 10 support tables; 3,947 columns; MySQL 8.4.8 create/rollback/rerun, source constraints and 2-process numbering pass | Operational schema is a separate addendum; source-value/import parity is not claimed |
| P2.5 Integrated read-only slice | Coordinator / specialists | P2.3, P2.4 | blocked | Only clearly labeled synthetic presentation exists | Real login → authorized city → search → persisted detail and denied cross-city access tests |
| Phase 3 | Data/file specialists / QA | Accepted schema and Phase 2 foundations | blocked | Not started | Phase 2 gate is not passed; user authorized Phase 3 only after Phase 2 is done |

Phase 2 is not complete. Approved preservation migrations are now implemented and tested with synthetic rows. No private records, production credentials or FTP access are part of this foundation. Next: review the concrete operational foundation addendum, then implement login, server permissions, audit and the integrated read-only slice. The approved preservation schema does not need approval again. Do not substitute preview interactions for authorization or migration acceptance.

## Review and fixes — 17 October 2026

User requested a review of security, design, responsiveness and user scenarios. Review covered implemented foundation routes/components/migrations; real login, project permissions and business workflows are still pending P2.3/P2.5 and are not reported as tested.

- Fixed mobile navigation background interaction and scrolling: opening focuses navigation, makes main inert and locks page scroll; Escape/backdrop restores the menu button; section links move focus to their target. Resize clears the transient mobile state. Desktop collapse remains a separate persisted preference.
- Increased visible mobile controls to at least 44px, improved Arabic readability and two-tone focus visibility, allowed sidebar scrolling in short viewports, and made statistics adapt to available width. Tested EN/AR at 300, 390, 768, 1024, 1440 and 2560px with no horizontal page overflow; wide tables scroll inside their region. System font/text mark remain an intentional D09 deviation from the HopeHR reference.
- Added frame/content-sniffing/referrer/permissions restrictions and CSP base/object/frame/form directives to success, redirect and error responses; HSTS applies only to secure production responses. Full script CSP, real identity authorization and production deployment review remain future work.
- Restricted the isolated connection check to loopback with no socket/URL override. Added regression denial coverage; no external database was contacted.
- Fixed rollback evidence retention: metadata-only snapshots now block every rollback entry point before typed tables, catalogs or support tables are removed. Existing typed/registry checks remain. No schema definition or approved data contract changed.
- Updated misleading preview text to distinguish approved preservation schema from the proposed operational addendum.

Verification: production preview denial, unsupported locales/routes, EN/AR navigation, city filtering, dialog focus/Escape and disabled saving; 28 unit/feature tests (5,282 assertions), isolated MySQL lifecycle/constraints/concurrency (52,444 assertions), build and formatting pass. Assertion count for the concurrent worker barrier varies with polling. Composer and npm advisory checks reported zero advisories. Browser checks showed no application console warnings/errors. Temporary MySQL test server shut down after verifying it contains no synthetic business data. Screenshots saved outside the repository.

Next task: **Phase 2 / P2.3 Identity and authorization**. Begin with the concrete operational foundation addendum (new principal, capability, audit and publication tables), then implement source-qualified login, legacy-hash upgrade, disabled/role-6 denial, deny-by-default grants and cross-city access tests. D07 real grant decisions still need staff disposition. Follow with P2.5 login → permitted city → search → read-only detail. Phase 3 starts after that slice passes; preservation-table approval is already recorded and does not need repeating.
