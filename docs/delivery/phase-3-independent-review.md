# Phase 3 independent review

Prepared 17 October 2026. Reviewer: import/file/reconciliation specialist, separate from the P3.0 schema author. Status: baseline/interface and corrected generated-core-schema static review completed. This is not schema approval, importer verification or a full-rehearsal result.

## Evidence and checks

Read repository AGENTS, Phase 3 in the team execution plan, the scope/account revisions, P1 mapping/preservation/relationship specifications and current global-account access/read services. Applied the Laravel expert skill to dependency and service-boundary review. Source/production databases, credentials, FTP and private file bytes were not accessed. No migration or application runtime behavior changed.

An independent PowerShell traversal of `database/schema/preservation-v1.json` compared each source DDL byte string with `docs/planning/source-schemas.json` and each column name/definition with `p0-2-data-inventory.json`. Result: 616 table instances, 127 distinct names, 3,947 columns, 743 declared FK contracts; zero DDL/column-definition differences in that direction. This verifies the checked-in preservation baseline against discovery artifacts; it does not certify restored values or future operational coverage.

The traversal identified 11 keyless instances: Damascus `dept`, `temp_gender`, `work_exp`; Homs `resultstable`, `visit_schedule_temp`; AlWadi `resultstable`; and `t1` in all five cities. Payload hashes cannot serve as their sole identity. Immutable statement/tuple ordinals must preserve equal duplicate rows and survive retries of the same snapshot. A separate PowerShell depth-first traversal visited all 616 source-qualified nodes and found zero declared-FK back edges, independently confirming a declared DAG. Additional application links still require cycle enumeration in P3.0.

Reviewed the P3.1–P3.3 contract in `../planning/phase-3-import-file-reconciliation-contract.md`: contiguous transactional checkpoints with fencing, exact full-key collision checks, second-pass cyclic-link resolution, source/snapshot-qualified reverse reconciliation, independent value extraction, opaque authorized file IDs, path confinement and separate file-reference/object observation identities. These are specified requirements, not implemented/tested services.

An additional independent PowerShell traversal compared every generated `phase-3/operational-schema.json` mapping with the immutable preservation manifest. All 616 source tables and 3,947 source column definitions match, composite/primary-key component order matches, and every non-archive mapping points to an existing operational column with exactly its declared target definition. The proposal has 118 shared operational tables and four core support tables. Twenty-six city/table instances across nine names remain fully preservation-only, including all 11 keyless instances and historical users. Thus no operational payload is claimed for those instances; full preservation/reconciliation remains mandatory. FLOAT/DOUBLE variants in `op_loan` retain separate source-width columns instead of silently casting all money to one decimal or double field.

Reviewed the proposed core support: `operational_entities` separates web/primary/ordinal identities, `operational_origins` maps each immutable record and prevents two occurrences in a snapshot collapsing to one entity, `migration_run_contracts` freezes hashes/versions, and `migration_checkpoints` includes snapshot/mapping qualification, full cursor plus digest, batch counts/ordinal, lease owner/expiry and fencing token. Same-source/city/kind FKs, origin snapshot FKs and historical-user table-kind checks are enumerated. Actual contiguous commits, lease fencing, exact full-byte collision checks, cross-snapshot parent resolution and complete row cardinality remain service/reconciler responsibilities requiring tests.

The first independent relationship traversal found 738 of 743 declared city contracts plus 25 application contracts in the operational register. The five omitted contracts were historical `user.UserRoleID → role.Role_ID`, one per city, whose child remains preservation-only. Reported to the schema author and coordinator; the corrected register explicitly retains them in the preservation `record_relationships` ledger. Independent rerun now finds all 743 declared city contracts, zero omissions, and 25 application contracts: 768 city occurrences in 152 grouped contracts. The five preservation-only role links are not counted as operational payload links. The corrected JSON reviewed has SHA-256 `e2bf6cd25f183b4b4e70713eef31b70d2a7dc0f9834e1aef8289758e8056ed9f`.

The core proposal explicitly defers the concrete attachment catalog, reconciliation-run evidence and optional actor/polymorphic ledgers to separately enumerated physical addenda. Approving this core cannot approve those future table definitions. Dependency-independent interface preparation is complete; P3.2/P3.3 database-dependent implementation still needs those concrete schemas and their acceptance.

Current `AccountAccess::CAPABILITIES` contains only `projects.view`. New file capabilities and owner-domain policies must be explicitly registered and tested before any attachment route; sharing a city or knowing a file ID is insufficient. The current interim project reader joins `source_publications`; importer success must not bypass that publication boundary or activate staff accounts.

## Acceptance still required

- Independent review of generated P3.0 table/column mappings, constraints, dependency graph and support catalogs; explicit physical-schema approval before dependent migrations.
- Validated extraction vectors for exact text/CHAR, invalid dates and source FLOAT/DOUBLE widths/bit encodings; no decimal casts or arbitrary tolerance as proof.
- Executable batch interruption/rerun/concurrent-writer tests, duplicate/keyless identity and cycle/sentinel/orphan cases.
- Authorized file streaming/path/error tests against isolated storage, including NULL versus empty BLOB and revoked account/city access.
- Independent whole-snapshot counts/values/relationships/files/report comparisons; explained anomalies and matching snapshot manifest.
- Isolated five-city rehearsal with timings and preserved anomaly evidence; later independent file backup/restore and deployment-host connectivity checks.

No P3.1–P3.4 acceptance is claimed. Pending legacy ambiguities include notification discriminators/sentinels, application-only links, unresolved historical actors and report formulas. They must be preserved with explicit exceptions and resolved or explicitly accepted for the affected workflow; no invented cleanup is authorized.
