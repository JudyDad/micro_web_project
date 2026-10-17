# P1.4 Preservation specification

Prepared 16 October 2026. Status: reviewable design; runtime preservation is not certified. This contract applies to all 616 city/table instances and all 127 distinct names, including auxiliary tables and duplicates. Sources: `team-execution-plan.md`, `creation-and-migration-plan.md`, `backup-audit.json`, `source-schemas.json`, `p0-2-data-and-files.md`, and accepted `p0-4-scope-contract.md`.

## Evidence boundary and immutable identity

Phase 0 static gate is recorded as passed in P0.4; permission/formula disagreements still block affected implementation. ZIP hashes and parsed counts are evidence about supplied dumps only. No restored values, file bytes or live services were inspected for this specification. Restore counts/settings and row anomalies must be supplied by P1.1 before preservation acceptance.

Freeze a snapshot manifest containing each city, immutable source identifier, original database name, archive/member SHA-256, DDL SHA-256, importer/extractor version, SQL mode, server version, charset/collation and session timezone used for restore/extraction. Never overwrite a prior manifest. A new backup is a new snapshot, even for the same source database.

Record identity is `(source_id, table, typed_original_key)` for a genuinely unique non-null key. Canonical keys use versioned length-delimited bytes with ordered column names, declared types and explicit null tags; decimal strings represent integers without conversion through JavaScript numbers. Keep the full key, not merely a digest. A digest index requires full-key collision checks. Composite keys retain every component in declared order. Duplicate purported keys become blocking exceptions, not overwrite candidates.

For keyless records, use `(snapshot_id, source_id, table, INSERT_statement_ordinal, tuple_ordinal)` from the immutable SQL member, or an equally immutable frozen extraction ordinal with its extraction manifest. Identical rows receive distinct ordinals. An unordered database scan, value hash alone, or AUTO_INCREMENT assigned afresh on each retry is not stable identity. Across new snapshots, keyless rows require explicit reconciliation; do not pretend ordinals identify the same evolving record.

Source identity and original city are immutable; later operational reassignment is separate. Projects preserve original MP_ID plus city, with separate internal IDs. Children preserve original reference values and source identity in addition to mapped keys. Resolve each relationship within its originating source; validate target identity by reversing mappings. Embedded IDs in paths, messages and labels stay unchanged. No automatic merge of people, users or lookups across sources.

## Typed baseline and comparison

Preserve both immutable original SQL bytes and a typed extraction of restored values. The dump token is not automatically identical to the value stored by the historical live server; dump/restore precision loss cannot be reconstructed from these inputs. Certification must say “supplied snapshot” unless a separately verified live baseline exists. Each typed cell has source column/type, null flag, exact payload or restricted payload reference, length, digest and extraction-method version. JSON numbers are forbidden for exact numeric payloads; use strings or binary encodings. SQL defaults never replace an explicitly supplied source value. Omitted columns, NULL, empty and zero remain distinguishable in extraction evidence.

| Source class | Required retained representation | Acceptance comparison |
|---|---|---|
| Integer, signed/unsigned, flags | Exact integer value as arbitrary-precision signed decimal string plus original SQL type | Exact equality and range check; preserve -1 and other sentinels. Never cast all nonzero flags to true. |
| DECIMAL(p,s) | Sign, exact unscaled integer and declared scale; original token retained in archive | Exact rational equality plus preservation of declared precision/scale contract. No binary floating-point conversion, currency conversion or inferred rounding. |
| FLOAT / DOUBLE | Source declared definition, effective binary width, and exact restored IEEE value obtained through a validated binary-protocol extractor; retain canonical network-order bit bytes and round-trip decimal representation | Same width: bit equality for finite values, including signed zero when observable. Any wider operational representation must convert back to identical source-width bits and also equal the exact widened value; narrowing is prohibited without proven equality per cell. No epsilon, percentage tolerance, formatted-display comparison or SQL SUM-only proof. |
| DATE / DATETIME / TIME / YEAR if encountered | Exact source components and fractional precision, null flag and original literal; capture restore/session settings | Compare components exactly. DATE/DATETIME have no inferred timezone. Never shift dates, truncate fractions, map invalid/zero dates to NULL, or invent midnight/default timestamps. |
| TIMESTAMP if encountered | Source session timezone, exact instant/fraction and original extracted representation | Compare under the same explicit session timezone and precision; if historical timezone is unknown, record limitation and block an inferred conversion. |
| CHAR / VARCHAR / TEXT / ENUM / SET | Original dump bytes plus restored column bytes, declared charset/collation, decoded text only after a reversible decode | Compare byte lengths and bytes in the preserved representation; if operational transcoding is approved, exact Unicode sequence equality AND reversible conversion to original bytes. No trimming, Unicode normalization, case folding, Arabic character substitution, line-ending changes, replacement characters or collation-equality shortcut. ENUM/SET retain declared members/order and invalid/sentinel states. |
| BINARY / VARBINARY / BLOB | Exact bytes, null flag, octet length and SHA-256 | Null differs from zero bytes. Compare octet length and SHA-256 for every value; byte comparison is the final arbiter of discrepancies. No image recompression, document conversion or base64-text substitution as the business value. |

CHAR padding/retrieval and lossy client conversions must be characterized during P1.1. SQL source literals remain retained where restored retrieval cannot reproduce original bytes; never certify that the original live bytes are recovered merely because a client string matches. An invalid date or undecodable value that the operational type cannot store is retained unchanged in restricted staging/typed archive with an explicit exception and no invented substitute. Archive preservation does not by itself meet workflow parity.

Observed numerical risks in DDL: Homs and AlWadi `loan.Loan_ReturnedAmount` and `Loan_PaymentsAmount` are DOUBLE while other sources use FLOAT; nullability differs. `payment.Pay_Amount` is declared `float(24,0)`, whose effective restore semantics must be measured on the selected compatible server. `material`, `mevisit.Profit`, requested amounts and several costs/salaries are also floating-point. Do not assume all money is DECIMAL or widen all columns without type evidence. New financial arithmetic is a separate reviewed domain rule.

For each report, first compare selected source identities and per-cell inputs, then grouping, formula, ordering and rounding behavior. Exact decimal totals use arbitrary-precision arithmetic. Floating aggregates require the same verified operation order/type semantics or a separately approved revised report rule; aggregate agreement cannot conceal changed cells. Group comparisons by city, currency and report category; never sum USD and SP as one amount.

## Historical authors and relationship exceptions

Keep `log.Log_User`, `Created_By`, `Edited_By` and other original actor text byte-exact and source-qualified, even if blank, duplicated or unmatched. A source user FK resolves only to that source's user. Optional actor resolution carries evidence and status (unresolved, unique-source-match, ambiguous); it never replaces original text. Historical actors can remain inactive/non-login identities. Web principals and explicit city grants are separate; matching usernames, passwords or display names do not authorize consolidation. Rehashing credentials must not rewrite authorship. Restricted credential material is never included in public review artifacts.

Retain NULL references and sentinels explicitly. For `user_notification.Pay_ID`, preserve raw value/body/type; do not assert one payment FK because discovery found payment, executive-file and sentinel uses. Orphans remain archived and visible in exceptions until disposition; no fake parent, cross-city fallback or dropped child. Cycles need reserved mappings and a later relationship pass, with post-load validation before acceptance.

## Attachment manifest contract

This is a logical serialization contract, to be implemented by the reviewed physical schema. One attachment reference may have multiple observed storage objects (online, office, BLOB, backup). Do not conflate reference identity with object checksum. Full paths and payloads are restricted operational evidence, not checked-in public examples.

| Field | Required type / rule |
|---|---|
| manifest_version, snapshot_id, source_id, original_city | Required version/string identifiers; reference immutable manifests and sources |
| reference_id, source_table, source_column, source_key_encoding | Required immutable identity; use snapshot ordinal for keyless rows; source column prevents collisions between attachments on the same record |
| raw_reference_is_null, raw_reference_bytes, reference_charset | Explicit null flag and exact encoded path/name or restricted immutable reference; empty path is not null |
| kind | `external` or `blob`; separate references when a row contains both |
| owner_source_keys, mapped_owner_ids | Original source-qualified project/person/user relations and optional internal IDs, each with resolution status |
| observation_id, storage_alias, root_mapping_version | Required per observed object; alias only, no endpoint passwords/tokens; online and office are distinct observations |
| original_path_bytes, resolved_relative_path | Original retained verbatim; nullable validated resolver output separately recorded; never rewrite original to make validation pass |
| source_octet_length, source_sha256 | Nullable until successfully read; nonnegative exact integer string and 64-character lowercase hex; zero bytes has SHA-256, missing/unread objects do not |
| read_status, observed_at, verifier_version | `not_checked`, `verified`, `missing`, `unreadable`, `unsafe_path`, `conflict`, `changed_during_read`, or `null_blob`; timestamp in explicit UTC for audit observations only |
| blob_null, source_blob_type | Explicit BLOB null flag and source definition; NULL is different from empty BLOB |
| backup_object_id, backup_manifest_id, backup_sha256, restore_verification_id | Nullable until independent backup and restore checks run; existing FTP object is not its own backup |
| exception_ids, resolution_id | Links to review decisions; no silent preference for online/office |

Required embedded coverage from DDL: `backgrounds.Image` (LONGBLOB), `file.File_Content` (LONGBLOB), `manualfile.Content` (LONGBLOB), and `user.ProfilePicture` (MEDIUMBLOB), in all five cities. External-reference discovery includes `image.image_NewPath` and `manualfile.Path`; continue coverage from every mapped path column and workflow, including mixed BLOB/path rows.

Resolve original names under a versioned source/city/storage root mapping; never derive an existing path from a new internal project ID. Treat path traversal, absolute paths outside an approved root, NUL bytes and unresolvable encodings as restricted exceptions without mutating the original reference. Keep credentials only in server-side secret configuration. Authorized download endpoints check current user, city and owner capabilities. Streaming and validation read existing storage only. New uploads use a separate source-qualified namespace and collision-resistant names; never overwrite or rename legacy objects.

Hash each online and office object independently. Same original path with unequal length/hash is a conflict; same bytes can share a physical immutable backup object while all source references remain distinct. Missing and unavailable are different outcomes. Capture stability evidence around reads or use a coordinated immutable snapshot; concurrent changes invalidate a hash claim. Independently back up all required objects, restore the backup into isolated storage and compare length/hash. Hosting connectivity and transport remain later deployment checks.

## Exceptions, reconciliation and acceptance

Exception records require stable ID, snapshot/source/table/key/column, category, original restricted evidence locator, expected rule, observed result, affected count, severity, owner, state, decision reason, reviewer, decision time, and linked rerun result. States: open, investigated, resolution-proposed, approved-disposition, verified-resolved. No default approval or conversion of a blocker to a warning. Never put personal values, paths or credentials in public exception summaries.

A proposed cleanup keeps original evidence and requires a separate explicit business decision; it is not an importer convenience. An accepted missing-file disposition must identify affected coverage and approver, and the release statement must disclose that gap. It cannot be called complete attachment preservation. Any operationally inaccessible archive-only data remains a scope/parity blocker unless explicitly accepted.

Reconciliation is independent of importer transformation code. For every source table compare restored counts, mapped identities with multiplicity, every column and every relationship; verify imported-plus-explicitly-quarantined count equals source count without double counting. Missing source tables are absent, not zero-row tables. Verify staged/resolved counts and exception transitions independently. Preserve original dump and typed evidence on retries; reject conflicting retries rather than overwrite.

Minimum future fixtures: same MP_ID in five sources; duplicate composite/keyless rows; NULL/empty/zero/-1; boundary integers; DECIMAL exact cents; adjacent representable FLOAT/DOUBLE values and signed zero; float(24,0) restored values; Arabic combining sequences and trailing whitespace; zero/invalid dates; orphan/cyclic links; duplicate actor text; empty/NULL BLOB; divergent/missing office/online objects; interruption/retry. These tests are specified, not run in this design task.

P1.4 document completion is distinct from runtime acceptance. P1.5 requires the concrete schema to satisfy these contracts, independent review and physical-schema confirmation before Laravel migrations. P3 acceptance requires actual independent per-cell/relationship/file comparisons, zero unexplained mismatches and truthful approved-gap reporting. Discovery totals (10,512 projects) apply only to the recorded hashes, never as constants for future snapshots.
