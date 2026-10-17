# P1.5 preservation review status

The independent preservation specialist authored preservation-spec.md, and a separate application reviewer authored relationship-review.md. Their schema review could not finish because the specialist agents reached the account usage limit. The coordinator completed schema-design.py and the generated schema proposal afterward; therefore these artifacts do not have an independent final-schema approval.

Coordinator cross-check: all 616 source table instances and all 3,947 columns are explicitly retained; original composite PKs and keyless duplicate occurrences remain distinguishable; raw foreign values are retained when a resolved ledger link cannot be made. All four BLOB-bearing columns identified by the specialist remain typed columns. No source column is coerced across city variants. Original cascades are not copied into target deletion behavior. Historical username text remains independent of web login accounts. Counts and preserved ZIP hashes are explicitly not presented as typed-value certification.

Outstanding review findings:

1. The 616-table compatibility design is a concrete preservation option, not a confirmed final operational schema. Consolidation/query routing and operational principals/grants/attachments still need physical design and acceptance.
2. Ledger snapshot consistency and import-run/snapshot consistency are currently importer/reconciler checks. Decide and review stronger composite DB constraints before acceptance.
3. Every declared FK is enumerated; application-only links are documented but their complete per-source machine-readable contracts and ambiguous discriminator rules remain unfinished.
4. MariaDB is a provisional restore engine. Full typed comparisons on the chosen MySQL runtime, warnings, source anomalies, and binary FLOAT/DOUBLE extraction are unverified.
5. The schema generator's coverage assertions and dependency graph checks pass, but target DDL/migrations have not been executed or independently reviewed.
6. Attachment manifest is logical only. No FTP file existence, hash, conflict resolution or independent backup has been verified.

P1.5 remains open. No final approval, migration authorization or no-loss certification is implied by these documents.
