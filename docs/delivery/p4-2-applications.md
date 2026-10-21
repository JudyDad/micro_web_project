# P4.2 application intake and private drafts

Started 20 October 2026; continued 21 October 2026. Status: implementation and acceptance checks in progress. This record does not close Phase 3 or Phase 4. The source trace is [the workflow contract](p4-2-workflow-contract.md).

## Delivered foundation

- Source-qualified preliminary applications, project-number allocation, beneficiary/partner associations and city filters in Arabic and English.
- Approved operational payloads for project details, materials, risks, power sources, agriculture answers and explicitly selected rehabilitation headers/answers. Answer choices are checked against their source-qualified question; rehabilitation limits use original question IDs.
- Per-city `applications.view` and `applications.manage` capabilities, publication-aware imported reads, transactional writes, metadata-only audit events and aggregate stale-edit checks. No real staff grants are provisioned.
- Private encrypted file drafts, atomic replacement, account/city/form ownership, bounded payloads, stale draft revisions and original application edit versions. Restore returns editable content for review; it does not submit business writes.
- Session-bound root-creation receipts prevent same-session retry duplicates. Child edits use the original aggregate version; a repeated successful child insertion is rejected as stale. These are not a durable cross-session idempotency ledger.
- Application and draft responses use private/no-store caching and no-referrer headers. Exact whitespace, empty text, null selections and unchanged historical sentinel values survive unrelated edits.

## Draft operation

Drafts use the private local filesystem disk under `storage/app/private/application-drafts`, encrypted with the application key. Keep the private root outside public document roots and back up its key/configuration separately. There is a maximum of 100 files per account/city and a 128 KiB JSON payload limit per draft. Application and source ownership are rechecked on access and commit.

Drafts expire after seven days. Cleanup is lazy on owner/city listing or save; loading an expired draft removes that file. Dormant directories can retain encrypted expired files until accessed. Losing the application key makes the drafts unrecoverable. Shared deployment requires a private shared disk and a cache lock store shared by application workers; the local default is not evidence for a multi-host deployment.

## Verification and remaining acceptance

The 20 October foundation verification passed 84 unit/feature cases with 5,668 assertions; one host-dependent symlink case was skipped. An isolated MySQL 8.4.8 actual-schema application test passed one case / 19 assertions, applying and rolling back all approved migrations and synthetic fixtures. These results predate the 21 October continuation and do not certify later changes.

21 October continuation verification: 87 unit/feature cases with 5,681 assertions pass (one host-dependent symlink case skipped). The isolated MySQL 8.4.8 actual-schema application test (loopback port 33072) passes one case / 18 assertions. Pint passes after formatting fixes. Not re-run: rendered browser checks of the continued screens. The isolated MySQL server was stopped afterwards.

Rendered foundation checks used invented SQLite data at loopback port 18042: login, directory, preliminary creation with Arabic text, application detail and private draft save. The continued full-intake work and final verification are recorded below when complete.

Phase 3 still owns private five-city reconciliation. P4.3 owns lifecycle state history, initial task assignments, review decisions and scoring. No production publication, FTP change, real notification, schema migration definition or staff permission assignment is part of this delivery. Staff workflow signoff, P4.4 print/parity acceptance and realistic cross-process concurrency remain separate evidence requirements.
