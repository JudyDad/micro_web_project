# P1.3 mapping specification

Status: proposed; complete declared source-column/FK coverage, application-only resolution and operational target design remain under review. No importer implemented.

The authoritative machine-readable register is schema-design.json. It contains every city/source table, all 3,947 original column definitions and their target definitions, original PKs, retained index definitions, 743 declared relationship contracts and five parent-first dependency orders. All 127 distinct names / 616 instances retain all rows. No table is silently discarded because its name suggests temporary use.

## Keys and retries

The canonical primary key encoding is versioned, length-prefixed binary: encoding version, number of columns, then for each original PK column in declared order its column-name bytes, source type descriptor, explicit null marker and canonical exact value bytes. Integer bytes encode sign and arbitrary precision consistently; textual key bytes retain source charset/collation metadata. SQL collation identity resolution must be performed in the source-compatible lookup, never by a simplistic case-normalized hash. The encoding specification needs implementation vectors before importer acceptance.

For a keyless table use the verified SQL SHA-256, source/table and one-based tuple occurrence ordinal in the frozen INSERT stream. Two identical rows have different ordinals. Never assign ordinals from an unordered SELECT. Across a different snapshot, ordinals do not prove continuing entity identity. Original ZIPs remain the immutable source, and the importer must record the tuple extraction algorithm version.

Allocate internal record IDs once and persist them. Retry lookup uses source + table + snapshot + key digest, followed by full key-byte equality; a digest collision is a blocking exception, never a merge. Interrupted retries reuse their map and verify typed-row content before accepting a completed row. If mapping exists but row insert did not complete, transaction/checkpoint recovery must repair that incomplete batch without replacing another record. All source key columns remain unchanged in typed rows.

## Relationships

For each declared contract, read the exact original child tuple. A NULL component follows the original FK's NULL semantics and becomes status `null`, not an invented parent. Otherwise look up the parent in the same source, table and snapshot under the referenced source key's comparison semantics. Exactly one match resolves; zero is orphan; multiple is ambiguous. Keep the raw value and a blocking exception for unresolved non-null links. Stage all raw rows before the resolving pass; never enable source CASCADE behavior in the target. Declared dependency graphs are acyclic, but future application links may add cycles; reserve a second pass.

Application-only contracts established by relationship-review.md include agriculture question/answer references, education type, the two family person roles, textual historical authors and discriminated notifications. Do not create one blanket FK on user_notification.Pay_ID. Sentinels include -21, -3, -10, -100, -1199 and dynamic negative ranges in the code; positive IDs need verified payment/executive-file context. Project -1 can be an intentional message sentinel despite the declared project FK. These cases need aggregate profiling and explicit resolution rules. The legacy notification table code refers to a table absent from the supplied schemas; keep the discrepancy open.

Historical actor strings remain exactly stored even if no account resolves. A resolved source user is supplemental metadata. Equal usernames, credentials, people names or lookup numbers never authorize cross-city merging. New web login identities and their city capabilities are separate operational schema work.

## Project numbering and walkthroughs

Aleppo microproject MP_ID=1 and Homs microproject MP_ID=1 have different source-qualified record identities and different new IDs. Aleppo loan.MicroProject_ID=1 resolves only to the Aleppo record. UI numbering remains 1 plus city; paths containing 1 are not rewritten.

For microproject_ag_answer, all three PK components participate; `(project=1,question=2,answer=3)` differs from answer=4. Validate answer/question compatibility as an additional business relationship without changing mismatched historical values.

For two equal t1 rows, ordinal 10 and 11 create two records; a retry of the same frozen dump maps back to those two records. A new dump does not reuse these ordinal mappings blindly.

Initialize each project's future number sequence to max(1, restored MAX(MP_ID)+1, original table AUTO_INCREMENT counter). Original counters are visible in source-schemas.json and retained as evidence. Check integer range and overflow before allocation, then transactionally lock/update the per-source sequence. Internal record ID allocation is independent of the visible project number. Never reuse deleted gaps or derive a city from the number alone.

These walkthroughs are design checks, not executed importer tests. Full numeric/text/BLOB/date comparison follows preservation-spec.md, not SQL display formatting or arbitrary tolerance.
