# P3.0 approved schema implementation

The user approved the physical proposal on 17 October 2026. Migration `2026_10_17_000006_create_shared_operational_tables.php` implements its 118 shared domain tables, four support tables and two existing referenced unique indexes without changing approved column types, nullability, defaults or table dispositions.

The runtime manifest is an exact byte copy of the approved `docs/planning/phase-3/operational-schema.json`, frozen at `database/schema/operational-v1.json` with SHA-256 `24c6d75ce52f3361545174dc1f605f4888feb8360cc9aad67f460d68097b7254`. Its historic proposal status text remains unchanged to preserve the reviewed bytes; approval is recorded separately. `OperationalManifest::load()` checks that hash and the approved preservation manifest before returning metadata. It reads no runtime documentation paths.

The migration accepts MySQL with enforced CHECK support and rejects MariaDB/other drivers. It verifies the frozen source-table and city/source catalog identities before any DDL. It adds the approved existing indexes, creates all 122 tables with columns/indexes/CHECKs, then attaches all RESTRICT foreign keys. Object names are deterministic hashes below MySQL's 64-byte identifier limit. It never disables foreign-key checks.

MySQL DDL is not transactional. Failure cleanup tracks only this invocation's tables/FKs/indexes and refuses to remove any newly populated table. Normal rollback checks preservation/import metadata and every proposed new table for retained rows before the first DDL, then removes its FKs, tables and only its two added indexes. Imported or web-created evidence makes rollback refuse; backup/forward repair is required instead of destructive schema removal. Deployments must continue to serialize schema migration and prohibit writers during DDL.

Runtime metadata API (explicitly require `database/schema/OperationalManifest.php`, matching the existing Database namespace pattern):

- `load(): array`: full frozen approved schema, mappings and contracts.
- `tables(): array`: all 118 domain plus four support definitions.
- `mapping(string $city, string $sourceTable): array`: exact source projection; preservation-only mappings have a NULL shared target.
- `table(string $sourceTable): array`: shared table definition by original name; preservation-only/unknown names fail closed.
- `quote(string $identifier): string`: checked MySQL identifier quoting.

`OperationalSchemaTest` is opt-in through `RUN_MYSQL_SCHEMA_TESTS=1`, asserts an empty `micro_projects_test` database on a loopback MySQL server, and never uses `migrate:fresh`. It verifies actual installed column types/nullability/collations, index tuples/uniqueness and all foreign-key RESTRICT rules. Synthetic tests exercise same-number projects in two cities, cross-source references, state/kind CHECKs, restricted parent deletes and rollback protection for both new entities and retained snapshot metadata. It ends with a complete empty-schema rollback. Execution evidence from the coordinator's isolated MySQL run is recorded in the Phase 3 delivery report; the schema author ran PHP syntax checks, Pint and metadata-loader checks successfully. The unconfigured default test invocation correctly skips the destructive lifecycle test.

No desktop source, original snapshot, source database, FTP object, production account or preserved business value is changed by authoring these migrations. Data import/reconciliation tests and the file-access physical addendum remain separately tracked Phase 3 work.
