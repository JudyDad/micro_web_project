# Phase 3 whole-table sharing delivery

The user approved the concrete sharing addendum on 18 October 2026. Migration 000007 and its converter implement the approved 55 canonical tables, two support tables, 55 source-alias alterations and origin index. The frozen schema SHA-256 is `6a7222a837f9a5fb882155227e8e30c0fa660a77442c0786e05ba2ed16b5bc51`; its historical proposal status is retained byte-for-byte, with acceptance recorded separately in [the approval](../planning/phase-3/shared-catalog-approval.md).

Only complete approved equality groups may share payloads. Each source occurrence keeps its operational entity ID, immutable preservation record, city, origin and local relationship references. Canonical values are immutable definitions; aliases carry a group/row/snapshot triplet and clear their duplicate payload. A scoped read projection validates membership, publication, snapshot, canonical bytes and preserved values before resolving an alias. It does not grant access by a global canonical row ID. Existing account grants and source publications do not change.

All 58 groups must match their original ZIP and SQL member hashes, original SQL schema and complete tuple fingerprints, frozen reader contents, imported preservation and operational values. The production command always constructs the original-archive verifier. SQL is parsed as data and never executed. A second independent reconciler verifies cells, identities, origins and relationships before and after conversion. Parent definitions are verified first. Each group commits atomically under source locks shared with the importer; failures retain previously committed complete groups, and reruns verify their existing IDs. Rollback refuses retained sharing evidence before DDL.

## Private local operation

Use the migration/import account on an isolated loopback `micro_projects_local` or `micro_projects_test` target. Apply migrations first, then finish the frozen source import and independent reconciliation described in [the importer instructions](phase-3-importer.md). PHP ZIP support is required in addition to the importer extensions.

Create a private JSON configuration outside Git with `manifests` mapping each of the five exact city names to its frozen manifest path, `archives` mapping those cities to the original ZIP paths, and bounded `decision_locator` and `evidence_locator` strings identifying the approval and private evidence. Use absolute paths. Keep source rows, credentials, paths and reports private.

```powershell
php artisan phase3:share-identical 'D:\private\sharing-configuration.json'
# If ZIP is available but disabled in the local CLI:
php -d extension=zip artisan phase3:share-identical 'D:\private\sharing-configuration.json'
```

The command accepts the full approved group set, reports aggregate counts and sanitizes failures. Recovery uses the same frozen inputs and evidence; do not substitute a new extraction or alter published definitions. The HTTP runtime retains SELECT-only business access. Future catalog writers must use the same source-lock protocol and need a separately designed editing workflow.

## Verification and remaining gate

Isolated MySQL tests cover physical types, constraints, lifecycle and rollback evidence protection; complete synthetic age/task conversion; unchanged entity/preservation/task-assignment rows; scoped projection denial; source and target mismatch rejection; transaction rollback; a competing connection; acknowledgement loss and idempotent reruns; corruption detection and importer resume. Unit tests include integer references beyond JavaScript's exact numeric range. Restricted-runtime tests deny sharing writes.

A separate private read-only check parsed all five actual original archives, verified their full ZIP/SQL hashes and reproduced all 58 audited group fingerprints. It also rejected an altered source cell. That validates the archive verifier against the actual backups, not a full native restored-source import or a real-data target conversion. No business rows or backups were committed.

Archive parsing supports the pinned snapshots' INSERT grammar and fails closed on unsupported syntax. A new backup format or changed snapshot requires reviewed evidence rather than automatic reuse of this approval.

Final local verification: 49 unit/feature tests (5,371 assertions), six serial isolated MySQL tests (62,886 assertions), and the separately provisioned restricted-runtime test (30 assertions) pass: 56 distinct tests and 68,287 assertions. The runtime case is deliberately skipped in the empty-target integration run and executed separately against the migrated synthetic demo. MySQL suite duration was 11:18. Pint, Composer validation, Vite build and Blade compilation pass. Windows process-spawn restrictions required running the existing build outside the restricted shell; no dependency changes were needed. Migration 000007 also passed as an additive upgrade on the existing local demo. Existing synthetic accounts, two publications and business data were retained.

Full five-city restoration/value proof, the 1,721,699-row rehearsal, attachment access, file/report parity and production activation remain open. Sharing implementation verification does not close Phase 3.
