# Unified accounts and city filters — 17 October 2026

Status: business decisions confirmed by the user; physical schema below ready for review, migrations and runtime changes not yet implemented. This revision supersedes the single-source login and city landing-page requirements in the original Phase 2 addendum. The earlier Phase 2 completion remains evidence for the previous behavior only.

## Confirmed product rules

- Login takes username and password only. There is one web account per reviewed person, with one globally unique login username. The user confirms that the same person's accounts in different legacy databases use the same password.
- Administrators assign permitted cities. A user does not acquire access merely because an old account, username or password matches.
- Duplicate legacy usernames require explicit review before accounts are linked. Matching credentials are not evidence that two accounts belong to the same person. Distinct people with the same username must receive distinct reviewed web usernames; historical values remain unchanged.
- Permissions are independently assignable per account and city, even though current legacy roles normally match across cities. Roles may later serve as assignment presets; server checks use the effective city-specific capabilities. No global union of city roles.
- Login opens the main workspace directly. Remove the city-choice landing page and any global active-city mode. In the current read-only slice, projects is the first workspace page; the broader dashboard arrives with its planned modules.
- Relevant pages include a multiple-city filter beside search and other domain filters. Default is all permitted cities for that page's capability. Options contain only permitted cities. An account with no permitted cities sees an empty access state.
- An explicitly requested unauthorized city returns 403; it must not silently broaden the query to all cities. Record, file, export and action authorization remains server-side regardless of filters.
- Lists show city alongside original project number. The same project number in two cities continues to identify distinct records. Pagination and sorting cover the combined permitted results; language switching preserves filters.
- One operational MySQL database is the final destination. Current typed city/source tables inside that one database are the immutable preservation layer. Later reviewed shared domain tables use city IDs and technical IDs, with complete source-to-target provenance. Cross-city lists over preserved tables are an interim adapter, not completed data consolidation.

## Physical schema breakdown for approval

Four additive tables are proposed. Existing preservation tables, web_principals, principal_capabilities and audit_events are retained unchanged as evidence. No destructive rewrite of an applied migration or automatic account merge.

All tables: MySQL 8.4, InnoDB. Text uses utf8mb4_bin unless binary storage is specified. IDs are BIGINT UNSIGNED; an `id` primary key is AUTO_INCREMENT. All FK delete/update actions are RESTRICT. All columns are NOT NULL with no default unless explicitly marked NULL DEFAULT NULL. UTC DATETIME(6) timestamps are supplied by the application. There are no soft deletes or automatic timestamp defaults.

### web_accounts

| Column | Type / default | Purpose |
|---|---|---|
| id | BIGINT UNSIGNED AUTO_INCREMENT | Global account identity |
| username | VARBINARY(800) | Exact UTF-8 username bytes; application limit 200 characters, no silent trim/case normalization |
| password_hash | VARCHAR(255) NULL DEFAULT NULL | One modern Argon2id credential |
| credential_principal_id | BIGINT UNSIGNED NULL DEFAULT NULL | Reviewed historical account selected only for first compatible password upgrade |
| disabled_at | DATETIME(6) NULL DEFAULT NULL | Disables the entire web account |
| created_at | DATETIME(6) | Provisioning time |
| updated_at | DATETIME(6) | Last account update |

Constraints/indexes: PK(id); UNIQUE(username) for global login lookup; index(credential_principal_id), FK credential_principal_id → web_principals(id); CHECK(password_hash IS NOT NULL OR credential_principal_id IS NOT NULL). An empty or invalid UTF-8 username/hash is rejected by provisioning. A credential bridge must be an active, reviewed account_legacy_links row for this account; this cross-table invariant is validated transactionally before provisioning or password verification. Once a modern hash exists, never retry historical credentials on failure. Native session identity becomes web_accounts.id and survives source snapshot changes.

### account_legacy_links

| Column | Type / default | Purpose |
|---|---|---|
| id | BIGINT UNSIGNED AUTO_INCREMENT | Reviewed identity link |
| account_id | BIGINT UNSIGNED | Global web account |
| source_id | BIGINT UNSIGNED | Historical source |
| principal_id | BIGINT UNSIGNED | Preserved source principal |
| decision_locator | VARBINARY(1024) | Sanitized review evidence reference |
| reviewed_at | DATETIME(6) | Review time |
| revoked_at | DATETIME(6) NULL DEFAULT NULL | Withdraws the link without deleting evidence |

Constraints/indexes: PK(id); UNIQUE(principal_id) prevents one preserved principal being assigned to different web accounts; UNIQUE(account_id,source_id,principal_id) supports account/source review queries; index(source_id,principal_id) for composite FK. FK account_id → web_accounts(id); FK(source_id,principal_id) → web_principals(source_id,id). CHECK(revoked_at IS NULL OR revoked_at >= reviewed_at). Multiple snapshot occurrences may map to one account only through separate explicit reviews; no username-based rebinding. Revoking/reinstating a link appends an audit event; reassignment to a different person requires a separately reviewed correction process, not an ordinary update.

### account_city_capabilities

| Column | Type / default | Purpose |
|---|---|---|
| id | BIGINT UNSIGNED AUTO_INCREMENT | Explicit city capability assignment |
| account_id | BIGINT UNSIGNED | Global account |
| city_id | BIGINT UNSIGNED | Permitted city |
| capability | VARCHAR(64) | Application-allowlisted action, initially projects.view |
| decision_locator | VARBINARY(1024) | Administrator assignment evidence |
| granted_at | DATETIME(6) | Assignment time |
| revoked_at | DATETIME(6) NULL DEFAULT NULL | Immediate revocation |

Constraints/indexes: PK(id); UNIQUE(account_id,city_id,capability) supports per-account authorization and prohibits competing grant rows; index(city_id) for FK. FK account_id → web_accounts(id); FK city_id → cities(id); CHECK(revoked_at IS NULL OR revoked_at >= granted_at). No wildcard, implicit role inheritance or automatic grants from account linking. Each capability independently defines the cities available on its page. A regrant updates this row with a fresh decision/time and appends audit in the same transaction.

### account_audit_events

| Column | Type / default | Purpose |
|---|---|---|
| id | BIGINT UNSIGNED AUTO_INCREMENT | Append-only event identity |
| actor_account_id | BIGINT UNSIGNED NULL DEFAULT NULL | Authenticated actor; NULL for denied unknown login/system provisioning |
| subject_account_id | BIGINT UNSIGNED NULL DEFAULT NULL | Affected account for administrative changes |
| city_id | BIGINT UNSIGNED NULL DEFAULT NULL | City context when applicable; login has no city |
| event_type | VARCHAR(64) | Application-allowlisted event |
| outcome | VARCHAR(16) | succeeded / denied / failed |
| subject_type | VARCHAR(32) NULL DEFAULT NULL | Allowlisted technical object kind |
| subject_id | BIGINT UNSIGNED NULL DEFAULT NULL | Technical object ID |
| decision_locator | VARBINARY(1024) NULL DEFAULT NULL | Required for provisioning/link/grant changes |
| occurred_at | DATETIME(6) | Event time |

Constraints/indexes: PK(id); indexes(actor_account_id,occurred_at,id), (subject_account_id,occurred_at,id), (city_id,occurred_at,id). FKs actor_account_id and subject_account_id → web_accounts(id); city_id → cities(id). CHECK(outcome IN ('succeeded','denied','failed')); CHECK both subject_type/subject_id are NULL or both are non-NULL. Application validates event-specific subjects and city membership; no polymorphic FK is claimed. No submitted usernames, passwords, request payloads, search text or private project fields are stored.

The runtime receives SELECT, INSERT on account_audit_events, and UPDATE(password_hash,updated_at) on web_accounts only. Administration/provisioning uses separate controlled credentials. Audit UPDATE/DELETE and business writes remain denied. A future web administration interface must use a separately reviewed capability and write path; the current read-only runtime must not receive broad database writes to enable that future feature.

## Upgrade and account review

1. Apply additive schema with a migration identity. Each new table has an empty-table rollback guard. Preserve existing audit and legacy principal IDs.
2. Produce a private duplicate-account review inventory from isolated restored sources. Never commit usernames, hashes or personal rows. Do not automatically create global accounts from matching names/passwords.
3. Provision each approved account, reviewed legacy links and explicit city capabilities in one transaction with decision evidence/audit. The selected credential principal supplies the compatible existing credential for the first login: verify its modern hash if present, otherwise its compatible legacy hash; never fall back from a failed modern verification. Store the successful credential as the global account's modern hash. No password reset is required by this design. Unexpected incompatible credentials or disagreements go into an exception report instead of choosing silently.
4. Disabled/role-6 legacy accounts are not silently activated or granted access. Provisioning preserves denial pending a staff decision. After transition, global disabled_at and explicit per-city grants are authoritative; historical source-role changes do not silently alter web permissions. Publication validity still gates access to displayed source data.
5. For synthetic local fixtures only, explicitly link the two invented demo principals to one global demo.user with projects.view in Aleppo and Damascus. Keep their original source records and separate historical identities. Add single-city, different-city-capability, revoked and duplicate-review fixtures for tests.
6. Authenticate only the global unique username; perform compatible upgrade once, rotate sessions and enforce current global status on every protected request. A changed publication cannot silently relink historical identities. Modern login remains independent of a publication's availability; affected data access fails closed until publication/reconciliation is valid.

## Routes and UI implementation contract

- GET/POST /{locale}/login: username and password only; no city field, query or session requirement.
- GET /{locale}/projects: combined permitted results, search and cities[] filters, deterministic pagination. Route redirects after login point here.
- GET /{locale}/projects/{record}: registry ID identifies a record globally; resolve city/source server-side and authorize it. Do not expose data through direct IDs outside grants.
- Old /{locale}/cities becomes a compatibility redirect to /{locale}/projects. Old city-qualified project links may redirect only after authorizing the resolved record/city, without establishing a global selected city.
- Sidebar has Projects and future business modules, with no Choose city entry. City is a local page filter and a record attribute. Create/edit pages later require a permitted record city as part of the record form, not a prerequisite navigation screen.
- Empty permission and empty search states are distinct. No accessible-city count, filter option, result count or total reveals unpermitted cities.

## Revised delivery sequence and tests

| Task | Deliverable | Acceptance |
|---|---|---|
| P2.R1 | Account schema review and additive migration | Physical schema approved; FK/CHECK/index and protected rollback tests |
| P2.R2 | Global identity, reviewed linking and city capability services | Duplicate links fail closed; exact username login; legacy upgrade; global disable; independent city grants/revocation; minimal append-only audit |
| P2.R3 | Direct workspace and combined filtered project pages | No login/city chooser; all permitted cities by default; multi-city filter alongside search; direct record and query tampering denied; stable cross-city pagination; EN/AR responsive browser checks |
| P2.R4 | Synthetic upgrade, independent security review, docs/CI/push | Existing evidence retained; restricted database privileges verified; old sessions invalidated during auth-provider transition; all revised acceptance tests pass |
| P3.0 | Shared operational domain schema and complete mappings | Review city_id, global technical IDs, original numbers, historical actors and every source-column disposition before operational migration code |
| P3.1 | Deterministic importer into approved shared tables with preservation links | Five source databases consolidate into one operational DB; restart/rerun safe; no merge by unqualified legacy IDs |
| P3.2–P3.4 | Authorized files, independent reconciliation and full rehearsal | City-capability checks cover files/exports; values, relationships and source-to-operational mappings independently reconcile |

One physical database was already chosen; this revision explicitly requires shared operational business tables as well. Preserve the 616 typed tables as evidence until a later authorized retention decision. Do not report UI aggregation over these tables as the finished importer or final operational schema.

The four user answers settle product behavior. The remaining approval is this exact physical schema, required before migration code by the database architecture workflow. No further password-policy, city-access or role-model question is pending for this revision. D07 still tracks actual staff assignments and domain capability definitions; hosting remains deferred until project completion.
