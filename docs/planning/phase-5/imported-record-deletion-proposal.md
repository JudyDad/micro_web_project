# Proposal: deleting imported (old) records from the web

22 October 2026. **Approved by the owner on 22 October 2026 ("Approved: build operational_deletions with the admin-only imported.delete rule, delete and restore") and implemented as migration `2026_10_22_000008_create_operational_deletions`** (project rule: confirm the physical schema before generating migrations).

## Why imported records are protected today

An imported row in an `op_*` table is the working copy of a source row. It is tied to its preserved source row through `operational_entities` and `operational_origins`. Hard-deleting it would break that link (the audit trail from the working record back to the original), and the deterministic importer or a re-import could bring the row back. The plan forbids silently discarding legacy data. Editing imported rows is already allowed and keeps legacy values; only deletion is held back.

## Proposed design: tombstones, not physical deletion

- One additive table `operational_deletions`: `id`, `city_id`, `source_id`, `source_table_id`, `entity_id`, `deleted_by_account_id` (FK to `web_accounts`), `deleted_at` (UTC), `reason` (text, optional). Unique on `(source_table_id, entity_id)`; FK to `operational_entities` with RESTRICT. Nothing in the preservation tables, `operational_entities` or `operational_origins` changes, so the original data stays intact and traceable.
- `OperationalTables::query()`, the single read path for every `op_*` table, excludes entities that have a tombstone. Every existing page then hides the deleted record without further changes; children are tombstoned in the same transaction (for example a visit with its office row and answers).
- **Revised 22 October 2026 at the owner's request:** deleting or restoring an imported record needs a separate, administrator-only capability `imported.delete`, granted per city, in addition to the area's own `*.delete` capability (for example `visits.delete`). The `data-entry` role does not include `imported.delete`, so data-entry staff keep deleting only records created in the web. The capability is granted and revoked like any other, and each grant is audited.
- Deleting an imported record is audited (`*.deleted` with the entity), and is reversible: an authorized user can restore it by removing the tombstone (also audited). Records created in the web keep the current hard delete.
- The importer and the independent reconciler treat tombstoned rows as present-but-deleted, so counts still reconcile and a re-import cannot resurrect them.

## What I need from you

Approve (with the administrator-only `imported.delete` rule above) the `operational_deletions` table as described (or ask for changes). On approval I will add the migration, the read-path filter with a regression test over every operational table, delete and restore actions for special cases, visits, M&E visits and the rehabilitation visit, and the reconciler change.
## Implementation record

- Migration 000008 creates `operational_deletions` exactly as proposed (unique `(source_table_id, entity_id)`, composite FK to `operational_entities`, FK to `web_accounts`, RESTRICT). Rollback is refused while any deletion record exists. It follows the other migrations' MySQL-only pattern. It was run against an isolated MySQL 8.4.8 on 22 October 2026 (migrate and rollback, unique and foreign-key behaviour, the rollback refusal, restore); the unit/feature tests use the same table in the SQLite fixtures.
- `OperationalTables::query()` excludes hidden entities (`query($table, true)` returns only hidden ones); `hide()` / `unhide()` write and remove the record. Capability `imported.delete` (administrator-only, not part of the `data-entry` role) plus the area's own delete capability are required to delete or restore an imported record. Special cases, visits (all kinds including the rehabilitation closing visit) and M&E visits support delete and restore; each administrator page lists hidden records with a Restore button. Delete and restore are audited (`*.deleted`, `*.restored`). Records created in the web are still deleted outright.
- Children of a hidden record (office rows, answers) are not hidden separately: they are only ever read through their parent. The importer and reconciler read the physical tables, which are unchanged, so counts reconcile and nothing is resurrected.
- Not yet covered: deleting imported people, applications, finance and other earlier areas (those areas have no delete capabilities at all), and a reason text (the column exists, nothing fills it yet).
