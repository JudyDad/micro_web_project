# Operational foundation: accepted Phase 2 physical schema

Revision notice: this remains the accepted schema for the original Phase 2 implementation. Its single-source login and separate city landing-page behavior are superseded by the approved [unified-account-city-filter-revision.md](../unified-account-city-filter-revision.md). Migration 000005 adds the approved global-account tables; original tables and evidence are retained during upgrade.

Prepared 17 October 2026. **Accepted for local Phase 2 implementation.** After this concrete proposal was presented, the user instructed: “Start Phase 2 from P2.3 till end of phase 2.” That instruction authorizes these four tables for P2.3 identity/authorization and the P2.5 synthetic read-only slice. It does not authorize real account activation or business writes.

The approved preservation design explicitly leaves “login principals/grants, audit events and unified operational tables” open in `schema-support.md`. Its typed rows represent immutable snapshot occurrences, not continuing operational identities. This separate addendum supplies the physical breakdown for the subsequently authorized Phase 2 implementation; it does not reinterpret the earlier preservation approval.

## Shared physical rules

All four tables use MySQL 8.4, InnoDB, `utf8mb4` with `utf8mb4_bin`. All FK actions are `ON DELETE RESTRICT ON UPDATE RESTRICT`; no cascades. Every column, default, key and FK is listed below. `NOT NULL` columns have **no default** unless specified; nullable columns have `DEFAULT NULL`. All timestamps are UTC `DATETIME(6)`, supplied by the application rather than implicit current-time defaults. There are no implicit Laravel timestamps or soft-delete columns.

`id` columns are `BIGINT UNSIGNED NOT NULL AUTO_INCREMENT`. Opaque locators are bytes, not browser-accessible URLs; references point to sanitized decision/evidence artifacts. Binary UUID request identifiers contain exactly 16 application-validated bytes. The frozen preservation manifest assigns `source_tables.id = 117` to the original `user` table. This ID was verified through `PreservationManifest::tableIds()` and must be verified again before migration; it must never be recomputed from a changed catalog silently.

## 1. `web_principals`

One source-qualified web principal bound to one preserved source-user occurrence. There is no global username key and no automatic cross-city identity union.

| Column | Exact type / nullability / default | Purpose |
|---|---|---|
| id | BIGINT UNSIGNED NOT NULL AUTO_INCREMENT | Web principal identifier |
| source_id | BIGINT UNSIGNED NOT NULL; no default | Source/city security boundary |
| source_table_id | BIGINT UNSIGNED NOT NULL; no default | Must equal the frozen user-table ID 117 |
| source_record_id | BIGINT UNSIGNED NOT NULL; no default | Preserved source-user registry occurrence |
| password_hash | VARCHAR(255) NULL DEFAULT NULL | Modern web hash after compatible legacy verification; never copy plaintext |
| disabled_at | DATETIME(6) NULL DEFAULT NULL | Non-NULL always denies login and subsequent access |
| created_at | DATETIME(6) NOT NULL; no default | Provisioning audit time |
| updated_at | DATETIME(6) NOT NULL; no default | Last hash/account-state change |

Keys and constraints:

- PK `(id)`.
- UNIQUE `(source_id, source_table_id, source_record_id)` prevents duplicate principals for one preserved source-user occurrence.
- UNIQUE `(source_id, id)` is the referenced key for source-consistent grants and audit actors.
- CHECK `source_table_id = 117`.
- FK `(source_id)` → `sources(id)`; its index is covered by the unique keys above.
- FK `(source_id, source_table_id, source_record_id)` → `source_records(source_id, table_id, id)`; its index is the first unique key.

The registry FK cannot prove that the corresponding typed `legacy_*_user` row exists. Provisioning and login must verify that typed row and its registry/snapshot/source consistency. The principal has no duplicated username, personal name, legacy role, or password-salt fields. Login looks up the submitted username within the selected source's published typed user rows and requires an unambiguous explicitly provisioned principal. Duplicate matches fail closed for review.

When `password_hash` is NULL, the compatible desktop PBKDF2 verifier may verify the preserved credential. A successful authenticated login stores a modern framework hash atomically and audits only the fact of rehashing. Once a modern hash exists, failed verification must **not** fall back to the historical hash. Incompatible legacy formats deny login and require an explicit recovery policy. `disabled_at` is checked before any successful login or rehash. A preserved role of 6 also always denies login, even if the web row was mistakenly left enabled. Other legacy roles do not automatically confer capabilities.

## 2. `principal_capabilities`

An explicit allow grant for one principal, one source and one capability; absence or revocation means denial. No role inheritance or wildcard grants.

| Column | Exact type / nullability / default | Purpose |
|---|---|---|
| id | BIGINT UNSIGNED NOT NULL AUTO_INCREMENT | Grant identifier |
| source_id | BIGINT UNSIGNED NOT NULL; no default | Must match the principal's source |
| principal_id | BIGINT UNSIGNED NOT NULL; no default | Granted principal |
| capability | VARCHAR(64) NOT NULL; no default | Exact allowlisted capability code |
| decision_locator | VARBINARY(1024) NOT NULL; no default | Approved grant decision artifact |
| granted_at | DATETIME(6) NOT NULL; no default | Grant decision time |
| revoked_at | DATETIME(6) NULL DEFAULT NULL | Non-NULL denies the grant |

Keys and constraints:

- PK `(id)`.
- UNIQUE `(source_id, principal_id, capability)` provides authorization lookup and prevents conflicting duplicate grant rows.
- FK `(source_id, principal_id)` → `web_principals(source_id, id)`; covered by the unique index prefix.
- CHECK `revoked_at IS NULL OR revoked_at >= granted_at`.

For this phase the application allowlist contains only `projects.view`. Only synthetic test principals receive that grant until staff resolve D07 and approve actual capabilities. The capability string remains VARCHAR so future reviewed vocabulary changes do not require an enum rewrite. Server authorization must validate both the exact capability and current principal/source state on each request and action; a client-side city filter is never authorization.

Revoking or regranting updates the single grant row and appends an immutable audit event in the same transaction. Each regrant must supply a fresh decision locator and grant time. Prior decisions remain in audit events; do not overwrite history there. Matching usernames, passwords or names in two cities cannot create a cross-city grant or merge principals. A separate source identity requires separate authentication/provisioning; a future reviewed account-linking model is outside these four tables.

## 3. `audit_events`

Append-only security and foundation events with minimal technical identifiers. Do not store request bodies, passwords, hashes, tokens, submitted usernames, personal names, IP addresses or project contents.

| Column | Exact type / nullability / default | Purpose |
|---|---|---|
| id | BIGINT UNSIGNED NOT NULL AUTO_INCREMENT | Event identifier |
| source_id | BIGINT UNSIGNED NOT NULL; no default | Audited source boundary |
| actor_principal_id | BIGINT UNSIGNED NULL DEFAULT NULL | Authenticated actor, or NULL for unresolved/system actions |
| event_type | VARCHAR(64) NOT NULL; no default | Server allowlisted event code |
| outcome | VARCHAR(16) NOT NULL; no default | succeeded / denied / failed |
| subject_type | VARCHAR(32) NULL DEFAULT NULL | Allowlisted technical subject kind |
| subject_id | BIGINT UNSIGNED NULL DEFAULT NULL | Technical record identifier; not a person's name or business number |
| request_id | BINARY(16) NULL DEFAULT NULL | Correlation UUID; not a session/access token |
| decision_locator | VARBINARY(1024) NULL DEFAULT NULL | Sanitized decision or reconciliation evidence when applicable |
| occurred_at | DATETIME(6) NOT NULL; no default | Event time |

Keys and constraints:

- PK `(id)`.
- INDEX `(source_id, occurred_at, id)` for source chronology.
- INDEX `(source_id, actor_principal_id, occurred_at)` for actor review and FK enforcement.
- FK `(source_id)` → `sources(id)`; covered by both indexes.
- FK `(source_id, actor_principal_id)` → `web_principals(source_id, id)`; nullable actor supports denied/unresolved and controlled system events without inventing a principal.
- CHECK `outcome IN ('succeeded', 'denied', 'failed')`.
- CHECK `(subject_type IS NULL AND subject_id IS NULL) OR (subject_type IS NOT NULL AND subject_id IS NOT NULL)`.

Subject references deliberately have no polymorphic FK: they are technical audit references, validated against an event-specific allowlist. Initial subject kinds are `principal`, `capability_grant`, `publication`, and `project_record`. Events covering an unknown source are written to sanitized infrastructure logs rather than inventing a source row. Grant/revocation/rehash/publication events require decision/evidence context appropriate to that event; do not copy unrestricted prose into this table.

Append-only behavior is an explicit application and database-privilege requirement: the runtime audit writer receives INSERT and necessary SELECT, never UPDATE or DELETE on this table. Migration/administration credentials are separate. FKs alone do not make a table immutable; no claim of tamper-proof history is made without those privileges and protected backup/operational evidence. Corrections append a new event and reference the original event through an allowlisted future extension if needed; they never edit old rows.

## 4. `source_publications`

One explicit published snapshot per source, used to choose the visible read-only dataset. This table is required for the proposed slice; absence of a row denies access to source projects rather than selecting the latest snapshot implicitly.

| Column | Exact type / nullability / default | Purpose |
|---|---|---|
| source_id | BIGINT UNSIGNED NOT NULL; no default | One publication pointer per source |
| snapshot_id | BIGINT UNSIGNED NOT NULL; no default | Published preservation snapshot |
| publication_kind | VARCHAR(16) NOT NULL; no default | synthetic / validated |
| evidence_locator | VARBINARY(1024) NOT NULL; no default | Synthetic fixture manifest or passed reconciliation/activation decision |
| published_at | DATETIME(6) NOT NULL; no default | Publication time |

Keys and constraints:

- PK `(source_id)`; no AUTO_INCREMENT.
- UNIQUE `(source_id, snapshot_id)` supports exact publication identity and covers the composite FK.
- FK `(source_id)` → `sources(id)`; covered by PK.
- FK `(source_id, snapshot_id)` → `snapshots(source_id, id)`; covered by the unique key.
- CHECK `publication_kind IN ('synthetic', 'validated')`.

Only synthetic publications may be created during Phase 2 local/testing execution. A real publication requires successful importer/reconciler evidence and explicit authorized activation after reconciliation; a string value of `validated` is not evidence by itself. Publication changes require a controlled command/service and append an audit event in the same transaction. There is no automatic publication after import and no public route for activation. Synthetic publications must be rejected outside local/testing environments.

## Read-only slice and snapshot boundaries

Resolve the authenticated principal's source, deny disabled/role-6 principals, require an active `projects.view` grant, then resolve that source's publication. Query the existing source-specific typed `legacy_*_microproject` table through `source_records`, enforcing the same `source_id`, the frozen microproject table ID and the published `snapshot_id`. Route-supplied source/project IDs must pass the same checks; do not trust a city selector. A source-qualified registry record ID is the read route identity. The original `MP_ID` remains visible beside the city; equal project numbers in two cities remain separate records. No new money, project or attachment models/tables are proposed here.

Source records, original typed values and snapshot identities remain immutable. A principal binds to a **snapshot occurrence**, not a proven continuing human identity. Publishing a newer snapshot must not silently rebind existing principals by username or source numeric ID. Login/access must fail closed when the bound user occurrence is outside the active publication until an explicitly reviewed reconciliation/provisioning decision resolves the transition. This initial proposal supports the controlled single-publication read slice; it does not solve long-lived identity continuity across replacement snapshots. A future schema/process decision must address that before routine snapshot replacement or operational writes.

The read service must also verify the principal's typed user row remains valid for the published snapshot. Audit events retain their historical principal references even after disabling a principal or withdrawing publication. FKs prevent deleting referenced evidence; they do not prove snapshot reconciliation or authorize access.

## Approval and acceptance boundary

Approval requested is limited to these four physical tables, their listed constraints/indexes, and the single-source principal behavior. It does not resolve D07, approve real grants, replace importer consistency checks, or authorize production activation. After approval, implementation acceptance requires source-mismatch FK tests, role-6/disabled denial, deny-by-default grants, same-number project isolation, direct URL/action checks, legacy-to-modern hash behavior, publication denial and audit privilege/immutability checks. Database CHECK and FK behavior must run on isolated MySQL 8.4 with synthetic data.

Attachments remain Phase 3 design work. Financial writes, unified business identities, cross-source account linking, production permission matrices and replacement-snapshot identity continuity need separate reviewed designs. Migration `2026_10_17_000004_create_operational_foundation_tables.php` implements this contract. Acceptance evidence is recorded in `../../delivery/phase-2-completion.md`.
