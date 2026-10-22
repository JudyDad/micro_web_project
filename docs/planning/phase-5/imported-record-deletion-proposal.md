# Proposal: deleting imported (old) records from the web

22 October 2026. **Proposal only: no migration or code exists yet, and the physical schema below needs the owner's explicit approval before it is implemented** (project rule: confirm the physical schema before generating migrations).

## Why imported records are protected today

An imported row in an `op_*` table is the working copy of a source row. It is tied to its preserved source row through `operational_entities` and `operational_origins`. Hard-deleting it would break that link (the audit trail from the working record back to the original), and the deterministic importer or a re-import could bring the row back. The plan forbids silently discarding legacy data. Editing imported rows is already allowed and keeps legacy values; only deletion is held back.

## Proposed design: tombstones, not physical deletion

- One additive table `operational_deletions`: `id`, `city_id`, `source_id`, `source_table_id`, `entity_id`, `deleted_by_account_id` (FK to `web_accounts`), `deleted_at` (UTC), `reason` (text, optional). Unique on `(source_table_id, entity_id)`; FK to `operational_entities` with RESTRICT. Nothing in the preservation tables, `operational_entities` or `operational_origins` changes, so the original data stays intact and traceable.
- `OperationalTables::query()`, the single read path for every `op_*` table, excludes entities that have a tombstone. Every existing page then hides the deleted record without further changes; children are tombstoned in the same transaction (for example a visit with its office row and answers).
- Deleting an imported record needs the same `*.delete` capability, is audited (`*.deleted` with the entity), and is reversible: an authorized user can restore it by removing the tombstone (also audited). Records created in the web keep the current hard delete.
- The importer and the independent reconciler treat tombstoned rows as present-but-deleted, so counts still reconcile and a re-import cannot resurrect them.

## What I need from you

Approve the `operational_deletions` table as described (or ask for changes). On approval I will add the migration, the read-path filter with a regression test over every operational table, delete and restore actions for special cases, visits, M&E visits and the rehabilitation visit, and the reconciler change.