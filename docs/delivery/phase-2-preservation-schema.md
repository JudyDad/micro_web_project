# Phase 2 approved preservation schema — 17 October 2026

The user approved the existing Phase 1 preservation schema. It is implemented and verified locally on MySQL 8.4.8. The approval and immutable artifact digest are in `../planning/phase-1/schema-approval.md`.

## Delivered

- Three migrations create the 10 approved support tables, register five cities/sources and 127 original table names, then create all 616 typed compatibility tables. They retain all 3,947 source columns, charset/collations, nullability, defaults and original indexes, replacing original auto-increment behavior with a technical record PK. The 743 declared source relationships remain metadata for importer resolution rather than destructive raw-row FKs.
- Typed rows enforce their fixed source/table and the registry identity through CHECKs and composite RESTRICT FKs. Support tables enforce the approved source/snapshot, relationship and exception contracts. Raw orphan values remain representable. No source business records are imported or seeded.
- The version-one manifest is byte-for-byte the approved register, protected by a fixed SHA-256. Arabic source column names and spaces are retained. Static catalog IDs are deterministic and checked before typed DDL.
- `ProjectNumberAllocator` locks a per-source sequence in a transaction, retries deadlocks, fails on missing/invalid/exhausted state, and allocates the final signed-INT number once. Source baseline initialization belongs to the importer; no unverified sequence is seeded. The caller must authorize city/capability before invoking it.
- Failed CREATE sequences clean up only the tables they created. Before the first rollback DROP, the typed migration refuses any populated registry or typed table. Rollback is only for an empty preservation layer; normal imported evidence is retained.

## Independent review and verification

The application specialist reviewed manifest compilation/migrations and found Arabic identifier rejection plus the final valid number being rejected. Both are fixed. QA independently wrote the MySQL lifecycle suite and identified the populated rollback risk; the guard and regression check are implemented.

Local verification:

- `php artisan test`: 25 tests, 5,233 assertions pass.
- `php vendor/phpunit/phpunit/phpunit tests/Integration/PreservationSchemaTest.php` with the isolated test environment: 1 lifecycle test, 52,433 assertions pass in 44 seconds. The precise assertion count can vary because process-readiness polling asserts at each iteration.
- Two create/verify/rollback cycles check all 616 tables and 3,947 typed column definitions, nullability/collations, original index composition/uniqueness and catalog identity. Synthetic tests cover same-number projects in two cities, original PK uniqueness, keyless duplicates, raw orphan retention, source/table CHECKs, registry and relationship FKs, resolved/null parent rules, and refusal to drop populated evidence.
- Two independent PHP processes contend for the same source sequence. Twenty allocations are distinct and contiguous; the other city is unchanged. The signed-INT maximum allocates once, and exhausted/uninitialized sequences fail.
- Strict Composer validation, Pint and `foundation:check-database` pass. The previously pushed foundation workflow also passed on GitHub. CI now includes this new MySQL lifecycle suite; its result is separate from these local checks.

The first test exposed synthetic fixture references remaining during rollback. Tests now roll back their invented record fixtures and remove only their own sequence fixtures before schema rollback. No FK checks were disabled. A one-time private cleanup removed only that failed test's invented data in the dedicated database.

MySQL ran from an official Oracle portable archive with a valid Oracle Authenticode server signature, in a separate workspace runtime/data directory on loopback port 33072. Generated credentials and runtime files remain outside Git. No installed Windows service, source restore, desktop project, live FTP or production database was changed. Tests leave only the empty Laravel migration repository and shut down the temporary server afterward.

## Remaining scope

Approval and this implementation cover the preservation design. Original support documents explicitly omitted operational login principals, capability grants, audit events, attachment catalog and unified business identity tables. The concrete Phase 2 read-only authentication/publication addendum is in `../planning/phase-1/operational-foundation-addendum.md`; no migration for its new tables is included in this delivery.

D07 intended legacy permissions and D09 approved font/logo packaging still require their owner decisions. No role is silently expanded into a grant. Source numeric/date/text/BLOB values, application-only relationships and file completeness still require importer/reconciliation work. This schema-only test does not establish data-import parity or full Phase 2 acceptance, so the conditional Phase 3 build has not started.

The retained original UNIQUE keys permit one frozen source snapshot in each typed table. Multiple coexisting replacements would collide; publication/snapshot replacement needs the documented operational identity transition and cannot silently overwrite history. Run/snapshot consistency, ledger parent-snapshot consistency, digest-collision byte comparison and registry/typed cardinality remain mandatory importer/reconciler checks as the approved design states.
