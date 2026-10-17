# Phase 1 schema approval — 17 October 2026

The user explicitly approved the Phase 1 schema in this chat: “Phase 1 schema approve.” This satisfies the physical-schema confirmation requirement for the existing preservation design. No second confirmation is required to implement those tables.

Approved artifacts:

- `database/schema/preservation-v1.json`, copied byte-for-byte from the Phase 1 schema register. SHA-256: `5e46874d8ee93e72d36d05986fe95154e2c6d6650e908ba1dccbb0cbb2ca8e5d`.
- `schema-physical.md`: 127 source table names, 616 city/table instances, 3,947 retained source columns, original indexes and 743 declared relationship contracts.
- `schema-support.md`: 10 supporting tables for cities, sources, source tables, snapshots, migration runs, source records, relationship contracts, resolved relationships, exceptions and project numbering.

The typed table/column/index definitions and supporting schema now have Laravel migrations. Deterministic static catalog IDs establish the source/table CHECK constants. Original source FK actions are retained as metadata for future resolution, not executed against historical raw values. No source data, account credentials, backup archive or private attachment is included.

The approval accepts the documented preservation-layer design. It does not create definitions for tables the artifacts explicitly leave undesigned: web login principals/grants/audit, attachment catalog or unified operational business identities. Those remain design work rather than an unanswered approval of this preservation schema. Independent runtime checks and source-value verification remain evidence requirements; user approval does not imply that tests ran.

The copied design artifacts retain their original draft labels as provenance; this dated approval record supersedes those labels for the approved preservation scope. Never edit the locked version-one manifest after migration delivery; changes require a later versioned migration.

Implementation details selected within the approved design: InnoDB with the source table/column charset and collation; utf8mb3's implicit default is made explicit as utf8mb3_general_ci. Source AUTO_INCREMENT is removed and the original PK is retained as UNIQUE. Technical record identities and RESTRICT provenance FKs are added exactly as specified. Original record values are never normalized. Arabic identifiers are quoted and preserved.

Rollback is for empty preservation tables only. Before dropping any typed table, the migration refuses a nonempty record registry or typed table. Schema removal after imports requires a separately authorized recovery plan. Creation failure cleans up only tables created by that invocation, leaving pre-existing tables alone.
