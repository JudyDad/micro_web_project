# Phase 3 core importer — implementation and operation

Updated 18 October 2026. The user approved the concrete P3.0 schema, then requested whole-identical-table sharing. The approved schema and core staged import are implemented. The subsequently approved sharing addendum is implemented separately; see [sharing delivery](phase-3-sharing.md). This record does not certify the full Phase 3 gate.

## Delivered behavior

Migration 000006 adds the 118 unified business tables, four support tables and two approved indexes. The runtime manifest is frozen at SHA-256 `24c6d75ce52f3361545174dc1f605f4888feb8360cc9aad67f460d68097b7254`; the preservation manifest remains byte-identical. Migration cleanup tracks only its own objects, and rollback refuses retained evidence before its first DDL.

The source exporter reads a separately restored `micro_projects_source_*` MySQL 8 database through a native prepared PDO connection in a read-only repeatable-read transaction. It checks the original archive/member hashes, complete city table set, ordered columns/primary keys, source types/nullability/collations, client encoding and native FLOAT/DOUBLE binary extraction vectors. It freezes every table, including empty tables, as canonical base64 string/NULL JSONL and verifies all file hashes. It never serializes business numbers as JSON numeric values. Large source reads are streamed.

Exporter values certify the restored source observed by this extractor, not an unknown historical live server. Matching archive hashes alone do not prove that a database was restored from those bytes. A complete private restore record, conversion-warning review and original-to-restored value verification are still required before a supplied-snapshot preservation claim. CHAR retrieval/padding, invalid dates, unsupported ENUM states and unknown historical timestamp settings remain explicit profiling requirements.

The reader validates the complete manifest and every table checksum before import, validates exact source-column shape and canonical base64, and rechecks each stream's count/hash as it is consumed. Stable keyless ordinals bind to that frozen extraction; unordered database scans are never used on resume. A changed stream can leave previously committed private batches but cannot publish data or certify a completed import. Restore the exact frozen artifacts before retry, or investigate a new explicit recovery run; never silently substitute a fresh extraction.

The importer writes exact preservation rows, shared-table projections, immutable origin links and contiguous checkpoints in one bounded batch transaction. It validates full source keys beyond their digests, frozen versions/hashes, source/snapshot/run/table scope, native numeric roundtrips and unused type variants. Existing committed rows are reverified before skipping them on resume. Lease ownership, expiry and monotonic fencing tokens reject stale writers. Relationship resolution runs after all row identities exist, preserves raw values and distinguishes NULL/orphan/ambiguous/sentinel states. Unresolved overloaded notifications and agriculture inconsistencies remain explicit exceptions. No source publications, accounts or capabilities are created.

The independent reconciler has its own key inspector and cell comparisons and does not call importer transformations, serializers or digest builders. It reads preservation, origin, shared rows and relationship ledgers in a separate read-only repeatable-read transaction. It checks one-to-one coverage, exact cells, source/snapshot scope, reverse mappings, supplemental references and source anomalies. Relationship SQL uses the preserved source-compatible types/collations only after the independent cell/identity pass; any corruption in that earlier pass keeps the complete report unsuccessful. Reports contain bounded technical IDs/categories/counts, not business values.

`matches` covers core value/mapping consistency. `core_ready` additionally requires zero unresolved source anomalies. `publishable` is always false: external attachments, report totals and full release evidence are not covered by this core implementation. No command automatically publishes data, allocates project numbers, merges identities or activates staff.

## Commands and private configuration

Use a migration/import account on the dedicated loopback `micro_projects_local` or empty disposable `micro_projects_test` target. Do not mix a real rehearsal with the existing synthetic demo: source legacy IDs can overlap. A fresh separate rehearsal target and reviewed grants/setup are required before the full private dataset run. The current CLI intentionally refuses arbitrary database names, remote targets, production environments and URL/socket overrides.

```powershell
php artisan migrate
php artisan phase3:freeze-source D:/private/source-configuration.json D:/private/new-frozen-city
php artisan phase3:register-snapshot D:/private/new-frozen-city/manifest.json
php artisan phase3:import D:/private/new-frozen-city/manifest.json --batch-size=250
php artisan phase3:reconcile D:/private/new-frozen-city/manifest.json --report=city-core-review.json
```

`freeze-source` requires a new output directory; it never overwrites an existing extract. Its private configuration contains a connection object (`host`, integer `port`, `database`, `username`, `password`) and an identity object (`city`, integer `source_id`, integer `snapshot_id`, original `archive_path`/`archive_sha256`, extracted `sql_path`/`sql_sha256`). Keep credentials and all original/decoded rows outside Git. The source connection must point to a separate restored loopback `micro_projects_source_*` database. Database and endpoint configuration never comes from a browser request.

`register-snapshot` is idempotent only for the exact same source/archive/SQL identity; it refuses rebinding an existing snapshot ID. `import` accepts batches from 1 to 1000 and retains durable checkpoints on failure. `reconcile` writes a new report filename under ignored `storage/app/private`, refuses to overwrite a report and returns unsuccessful when core mismatches or unresolved anomalies remain. Driver errors are sanitized at CLI boundaries because raw SQL exceptions can contain private data.

After accepted independent reconciliation, source project-number floors still need initialization and verification against MAX(MP_ID)+1 and the frozen source AUTO_INCREMENT counter, without reducing an existing counter. That post-reconciliation action is not automatic in this staged importer and remains required before enabling business writes. Replacement snapshots and existing web edits require an explicit continuity policy; matching keys cannot authorize overwrite.

## Verification and limitations

- `OperationalSchemaTest`: isolated MySQL 8.4.8, 1 test / 6,098 assertions passed. All 122 tables, types, nullability, collations, indexes, RESTRICT FKs and identity/state constraints verified; evidence prevents rollback; empty lifecycle cleanup passed.
- `PhaseThreeImportTest`: isolated MySQL 8.4.8, 1 test / 49 assertions passed. Real committed batches interrupted before commit and after lost acknowledgement, repeated reruns, relationship-stage interruption, stable IDs, two-city project number 1042, composite agriculture keys, equal keyless duplicates, values/BLOB/reference/entity/source-run corruption and stale lease rejection verified. Test-created data is removed and the dedicated target returns to its empty migration repository.
- Unit/feature suite: 48 tests / 5,366 assertions passed, including exact keys, independent numeric/key comparison and five frozen-reader integrity/path/byte cases.
- Native exporter validation: a newly created isolated source schema with all 122 Aleppo table definitions and three invented manual-file rows passed binary/Arabic/empty/NULL comparisons, native FLOAT/DOUBLE binary vectors and changed-archive-hash denial. Only that newly created validation database was removed; invented extraction files remain private outside Git. This is not a five-city business-data rehearsal.
- Full serial MySQL integration run: four tests passed with 58,876 assertions; the restricted-runtime test was skipped there because it has a separate opt-in, then explicitly run and passed with 26 assertions. Together with the 48 unit/feature tests, all 53 distinct tests pass with 64,268 assertions across the commands. The disposable integration database contains only its empty migration repository afterward.
- Migration 000006 was then applied additively to the existing local synthetic demo. The separately restricted runtime still authenticates/appends allowed account audit and is denied writes to new operational entities, project payloads, origins and checkpoints. Existing data and grants were retained. No source or real business dataset was imported into the demo.
- Formatting, PHP syntax, frozen-manifest SHA and independent 616/3,947/743 metadata coverage checks pass. No browser/UI change was introduced; the earlier P2.R4 rendered-browser limitation remains recorded separately.

The coordinator corrected a diagnostic-protocol defect found by the live import test: mysqlnd tried to prepare `SHOW WARNINGS`, generating warning 1295 itself. Diagnostics now use a scoped text-protocol call and restore the original PDO mode; genuine storage warnings still roll back the batch. No warning category is silently ignored.

The source archive equality audit separately verified all five ZIP/SQL hashes, 616 table counts and 1,721,699 tuple occurrences. That static audit is not source-to-target import/reconciliation evidence. Whole-table sharing found 55 operational names / 58 groups; the new physical proposal describes one canonical typed payload and source-scoped aliases. No sharing migration/conversion ran. FTP/BLOB catalog access, file backup/restore, file/report parity, complete fresh-source rehearsal, final sequence initialization and production activation remain open acceptance tasks.
