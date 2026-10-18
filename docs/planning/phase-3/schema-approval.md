# P3.0 physical schema approval — 17 October 2026

The user replied “approved.” after the coordinator presented and linked the physical schema proposal and asked for confirmation to implement its migrations and importer. This approves the concrete proposal pushed through commit `41395c6`; do not ask again for that scope.

Approved machine schema SHA-256: `24c6d75ce52f3361545174dc1f605f4888feb8360cc9aad67f460d68097b7254`. The implementation freezes those exact bytes at `database/schema/operational-v1.json`; never rewrite that version after migration delivery.

The approved scope includes 118 shared business tables, four support tables, and the additional UNIQUE(source_id,table_id,snapshot_id,id) on source_records and UNIQUE(snapshot_id,id) on migration_runs. The source-column mappings cover all 616 city/table instances and 3,947 columns, with 743 declared and 25 application relationship occurrences. The nine preservation-only names, nullable historical projection strategy, FLOAT/DOUBLE width variants, continuing entities versus immutable origins and resumable import contract are part of the proposal.

Approval authorizes local implementation and isolated tests. It does not certify runtime DDL, exact data parity, source/file backup, staff permissions or production readiness. Existing source business databases, desktop code and FTP files remain read-only. No account activation follows from an import.

Concrete attachment/reference catalogs, reconciliation evidence tables and optional actor/polymorphic resolution ledgers were explicitly deferred by the proposal and are not implied by this approval. Core reconciliation can produce restricted local evidence without adding an undesigned database table. Later physical addenda must enumerate their definitions before migration code.
