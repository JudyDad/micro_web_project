# Phase 3 start and acceptance record

Started 17 October 2026 after the user's instruction, “start phase 3.” This authorizes local Phase 3 work under the execution plan, including specialist design and independent review. It does not approve a physical schema that had not yet been presented or authorize production changes.

Current status, 18 October 2026: the subsequently presented P3.0 schema was explicitly approved and implemented. Core import/reconciliation behavior and isolated synthetic verification are recorded in [phase-3-importer.md](phase-3-importer.md). The design-stage gate descriptions below are historical. The whole-identical-table sharing revision was subsequently approved and implemented; see [sharing delivery](phase-3-sharing.md). Full Phase 3 acceptance is still open.

## Current gate

P3.0 is the shared operational business schema and complete mappings. The approved preservation schema and four unified-account tables remain unchanged. The new physical schema must be presented and explicitly confirmed before migration code, under the database architecture skill and the execution plan. Importer and reconciler implementation must use that accepted schema.

P2.R4 automated/live HTTP checks passed in the previous task. Its revised rendered EN/AR browser checks remain outstanding because local browser use was disabled. This does not block metadata-only Phase 3 design, but remains an acceptance item before declaring the revised UI verified. No attempt to bypass the browser restriction is part of this work.

## Evidence and boundaries

The checked-in preservation manifest enumerates 127 source table names, 616 city/table instances and 3,947 source columns. The earlier isolated restore summary reports 1,721,699 rows and 10,512 projects with matching table counts and no declared-FK orphan links. These are prior snapshot evidence, not a new Phase 3 import or proof about application-only relationships. Every rehearsal must derive expected counts from its own frozen manifest.

All source columns and auxiliary/configuration tables need a disposition. Shared operational tables hold workflow data; a preservation-only disposition must still retain and reconcile every source row and cannot certify workflow parity by itself. Legacy credentials are preserved in restricted source evidence, not copied into new login accounts. Duplicate accounts require explicit review.

The desktop checkout is read-only. Business rows, dump files, credentials and private attachments must stay outside Git. No live FTP connection, source database mutation, staff provisioning or production activation is authorized by this record.

## Phase acceptance sequence

| Task | Required evidence before completion |
|---|---|
| P3.0 | Every source table/column disposition, exact physical types and constraints, city/source consistency, stable operational identity versus snapshot occurrence, historical author handling, independent reviews, then explicit schema confirmation |
| P3.1 | Accepted migrations and deterministic importer; transactionally committed map/rows/checkpoints; interrupted-batch recovery, identical reruns, conflicting reruns rejected, same-number cities and keyless duplicates retained |
| P3.2 | Authorized read-only file access; source-qualified metadata; adversarial path/URL tests; exact BLOB handling; missing/conflicting files retained as exceptions; hosting reachability and independent backup clearly distinguished from local tests |
| P3.3 | Independent source/target reads; complete cell/relationship comparison after reversing technical mappings; no arbitrary numeric tolerance; per-file/BLOB length and hash verification; reports show retained exceptions |
| P3.4 | Isolated five-city rehearsal with immutable input hashes, counts, timings, interruption/rerun evidence and exception dispositions; zero unexplained mismatches |

Preservation is measured against the supplied snapshot. No claim is made that dump/restore reproduces unknown historical live-server bytes. FLOAT/DOUBLE bit preservation, CHAR retrieval, zero dates and encoding require validated extractors and representative private profiling; schema metadata checks cannot certify them.

## Handoff

This is a design-stage start, not Phase 3 completion. The canonical task board tracks each acceptance dependency. Hosting/provider/runtime and internet versus office/VPN decisions remain deferred until project completion before deployment, as requested.

## Coordinator static verification

`docs/planning/validate-phase-3-schema.py` parses the original source DDL independently and checks the proposal's source hashes, all source columns/definitions, primary-key component order, preservation and operational targets, case-insensitive identifier uniqueness, local FK referenced unique keys and all declared relationship occurrences. It reads metadata only and executes no SQL.

Result: 616 city/table mappings, 127 distinct source names, 3,947 columns and 743 declared relationship occurrences pass. The proposal has 118 shared business tables and four support tables; 26 source instances across nine names remain explicitly preservation-only. Every retained source instance still requires full import and independent reconciliation.

Regenerating the machine schema, physical breakdown and coverage JSON produced byte-identical artifacts. `git diff --check` passes. The preserved manifest SHA-256 remains `5e46874d8ee93e72d36d05986fe95154e2c6d6650e908ba1dccbb0cbb2ca8e5d`. The source-schema metadata hash uses UTF-8 text with LF newlines (`3973b3ea0a60c8e35f688907b79c98bb19931e3f43797c679648ded865f1dc91`) so a Git checkout and a Windows CRLF working copy verify identically. This metadata-only encoding is explicit in the proposal; it never normalizes business values, source dumps or the approved preservation manifest. Generator outputs also use explicit LF. Existing application behavior was not changed, so no new application/runtime test result is claimed.

Six deliberate in-memory corruptions were rejected: removed table mapping, changed original column definition, case-insensitive duplicate target column, missing relationship occurrence, changed preservation-manifest hash and changed primary-key components. These verify important checker failure paths; they are not importer, runtime DDL or data-parity tests.

The independent checker found five historical user-role relationships absent from the first register. The author added explicit preservation-ledger dispositions, and all 743 now pass. The preserved schema manifest is unchanged. Separate application and independent review records describe semantic limitations and deferred acceptance checks.

Reproduce the metadata checks from the repository root with a Python 3 runtime:

```powershell
python docs/planning/phase-3/build-operational-schema.py
python docs/planning/validate-phase-3-schema.py
git diff --check
```

The generated core schema does not include the deferred attachment catalog, reconciliation evidence catalog or optional actor/polymorphic-link addenda. Those require concrete definitions before their own database migrations. Schema confirmation for this proposal authorizes only its enumerated tables and alterations.
