# Phase 3 attachment access and reconciliation

Updated 18 October 2026. The local access path and private metadata inventory are implemented with a disabled-by-default catalog. This does not certify file preservation, report parity, or Phase 3 completion.

## Access boundary

The access path uses an opaque reference from a SHA-256-pinned private catalog and a reviewed decision locator. Configuration remains disabled by default. Each request checks the source record, snapshot, original table identity, publication, city-scoped `attachments.download` and `projects.view` capabilities, the matching project record and its current project access. Ownership is resolved with the same source and snapshot; person IDs, filenames and unqualified legacy IDs never grant project ownership.

The initial allowlist serves only `file.File_Content` BLOBs and `image.image_NewPath` external files. Other discovered BLOB and path fields remain inventory-only. Before response headers, the adapter stages each object with byte and time limits and verifies exact length and SHA-256 against reviewed evidence. Local path handling rejects traversal, links and path swaps. FTP transport uses read-only FTPS with peer/host verification and requires an explicitly reviewed server-jail decision. The HTTP route is private, rejects ranges, sends no-store/nosniff headers, sanitizes failures and records bounded audit outcomes. No storage root is accepted from a browser request.

No attachment database tables, storage configuration, grants, publications or live FTP actions are introduced by this delivery. The catalog adapter expects a separately generated, bounded access catalog; the broader evidence inventory below does not enable it.

## Private reference inventory

The frozen-source inventory covers all 30 BLOB/path field instances in the five-city schema, including NULL BLOB occurrences and empty BLOBs. It writes one JSONL reference per source row and attachment field. BLOB payload bytes are represented only by exact length and SHA-256 anchored to the frozen manifest. External paths and `File_Name` are base64-encoded in the private inventory because they can contain sensitive information. The inventory must stay outside Git and public storage.

```powershell
php artisan phase3:inventory-attachments `
  D:/private/new-attachment-evidence `
  D:/private/frozen/aleppo/manifest.json `
  D:/private/frozen/damascus/manifest.json `
  D:/private/frozen/homs/manifest.json `
  D:/private/frozen/latakia/manifest.json `
  D:/private/frozen/alwadi/manifest.json
```

The command requires exactly one verified frozen manifest for each approved city and creates a new ACL-restricted output directory. Its manifest records field coverage, source hashes and a digest of the JSONL inventory. It performs no database writes, attachment reads, publication or access grants.

The private 18 October rehearsal produced a complete five-city inventory of 48,629 field references across all 30 field instances. It recorded 23 NULL BLOB values and 39 empty BLOB values; the remaining values are not thereby certified against an original file restore. The inventory remains unpublished and its local manifest pin is kept with private evidence.

## Remaining acceptance evidence

The private inventory is not an original-to-restored attachment proof. Full acceptance still needs independent byte-level checks between original archives and restored MySQL BLOBs, reviewed source-path/FTP-root mapping, restore or mirror evidence for external files, unresolved/duplicate/case-collision analysis, source-qualified project owner resolution, and approval of per-reference verification states. The access catalog must be generated only from those reviewed decisions, pinned in local configuration, and left disabled until city grants and publication policy are separately approved. Report totals and rendered report parity remain separate work.
