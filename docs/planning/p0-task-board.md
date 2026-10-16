# Phase 0 delivery task board

Prepared 16 October 2026. The application directory did not exist at discovery start. This planning-folder board is temporary; move its accepted contents to `docs/delivery/task-board.md` when the new application repository is created in Phase 2. No repository or business application has been scaffolded.

| Task | Owner | Reviewer | Dependencies | State | Changed files | Evidence | Unresolved decisions |
|---|---|---|---|---|---|---|---|
| P0.1 Workflow inventory | Application specialist | Coordinator | Source and existing plans | done | `p0-1*` | Extractor exit 0; 53 forms/89 menus; source sample review in coordinator review | Permission contradictions and uncertain screens retained for affected implementation |
| P0.2 Data/file inventory | Data specialist | Workflow specialist + coordinator | Source schemas and archive manifest | done | `p0-2*` | 127 names/616 city tables/60 routines; CRC/hash checks pass; two review corrections closed | Restore verification and mapping profiling in P1 |
| P0.3 Theme/templates/infrastructure | UI specialist | Coordinator | Source/template paths and prior theme evidence | done | `p0-3*` | 29 asset hashes match; template/person-parameter and CSS traces verified | Hosting explicitly deferred; assets/rendering checked before packaging/print acceptance |
| P0.4 Scope contract | Coordinator | Workflow specialist | P0.1–P0.3 and user decisions | done | `p0-4-scope-contract.md`, this board, `p0-coordinator-review.md` | Independent scope review passed after hosting wording correction | Domain decisions remain assigned downstream; no production readiness claimed |

Done requires independent review and acceptance checks; produced artifacts alone do not close a task. Source and production mutations: none authorized or performed.

## Required project-completion question

Before deployment, ask the user for the hosting target/provider, production PHP/MySQL capabilities and internet-wide versus office/VPN access. User explicitly requested deferral until the end of the project; do not repeatedly block local work on this question. Carry this item into the application's delivery board and release checklist when created. No scheduled automation created.

Next: Phase 1 isolated restores and complete schema/mapping specification. See `p0-coordinator-review.md` for exact safety/acceptance prerequisites and outstanding domain checks. No Phase 1 SQL or application scaffold has run.
