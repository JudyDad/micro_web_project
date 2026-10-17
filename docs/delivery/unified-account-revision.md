# Unified login and city filters — implementation verification

The user approved the physical schema with “yes approved” after confirming the four account/city decisions. The local synthetic application now accepts username/password only, redirects directly to projects, combines all permitted cities by default and includes city checkboxes beside search. Empty city selection means all permitted cities. Unauthorized filter values and direct record access fail closed. Old city landing URLs redirect; old city-specific record URLs authorize and verify source before redirecting.

## Changes and interfaces

- Migration 000005 adds exactly the four approved tables: web_accounts, account_legacy_links, account_city_capabilities and account_audit_events. Old migrations, typed preservation rows, principals and audit evidence remain intact; nonempty account tables prevent rollback. Frozen preservation manifest SHA-256 remains 5e46874d8ee93e72d36d05986fe95154e2c6d6650e908ba1dccbb0cbb2ca8e5d.
- WebAccount is the native identity under the new account session guard. Old web-principal sessions are ignored, preventing a matching numeric ID from becoming a global account login. Disabled accounts are rechecked on every protected request.
- AuthenticateAccount::attempt(username,password) resolves one exact binary username. Before first upgrade, the selected historical principal must have an explicit unrevoked reviewed link and valid original published identity. Its modern credential takes precedence over legacy PBKDF2. Successful verification writes one Argon2id account credential; subsequent modern failures never fall back. Modern login remains independent of source publication availability. Missing/denied accounts perform dummy KDF workloads; no exact constant-time claim.
- AccountProvisioner::provision(username,credentialPrincipalId,reviewedPrincipalIds,cityCapabilities,decision) performs explicit reviewed linking and city-capability assignment transactionally. Existing linked principals cannot be reassigned automatically. grant/revoke/setLinkRevoked provide audited controlled administrative changes. No administrator HTTP write endpoints or real staff grants are activated; the web interface is still P5.3.
- AccountAccess::cities/check/active enforce explicit current capabilities. ProjectReadService::search(account,selectedCities,search) uses a stable cross-source UNION ALL adapter and rechecks active account, grant and publication in each branch. find(account,record,expectedSource?) resolves the city server-side, validates access and returns whitelisted fields only. Original number plus city remains visible.
- Login IP/username limits, CSRF, session rotation, no password flash, escaped output, literal LIKE escaping and sanitized audit metadata remain enforced. Query/link/language filters use validated values.
- `phase2:demo` creates a fresh reviewed synthetic global account; `phase2:upgrade-demo` explicitly accepts only the known two-city synthetic fixture and is idempotent without resetting passwords, account state or revoked grants. It refuses production/remote/unknown data. Local upgrade was applied to micro_projects_local; the runtime credential remains private outside Git.

The one operational database currently contains preserved source tables and the new account foundation. Shared business-domain tables and the source-to-shared importer remain P3.0/P3.1. Aggregating the preserved tables is an interim read adapter, not completed consolidation.

## Review and verification

Application specialist implemented the schema/identity/provisioner; UI specialist implemented bilingual filters and independently reviewed project-query authorization. Coordinator integrated controllers/routes/read adapter, reviewed identity code, added synthetic upgrade and privilege regressions, and completed validation after specialist usage limits interrupted their final runs. QA specialist supplied revised integration scenarios; coordinator ran and corrected the stale publication-response assertion.

| Check | Result |
|---|---|
| Unit/feature tests | 37 tests, 5,317 assertions passed |
| Operational workflow plus preservation lifecycle | 2 tests, 52,731 assertions passed (287 workflow; 52,444 preservation) |
| Restricted runtime | 1 test, 22 assertions passed |
| Total automated verification | 40 tests, 58,070 assertions passed |
| Pint, strict Composer validation, Blade compilation | Passed |
| Vite build | Passed; existing public font URL warnings remain, fonts served from public assets |
| Live HTTP under restricted runtime | City-free login, both city results, Arabic city+1042 filter, detail, CSRF logout passed |
| Rendered browser QA | Pending: CUA reported “Local browser use is disabled in Work settings” |

MySQL tests use separate empty micro_projects_test on loopback, return it to an empty migrations repository, and never target the synthetic application DB. Runtime privilege tests use the separate micro_projects_local account and roll back their audit/hash changes. Tests cover old session rejection, combined same-number results, literal wildcard/injection search, city-filter tampering, per-city capability isolation/revocation, global disable, publication withdrawal and production synthetic exclusion, cross-city pagination, escaped project content, Unicode suffix significance, duplicate username/link constraints, reviewed-link failure rollback, minimal audit, protected schema rollback and synthetic-upgrade idempotence/unknown-data denial.

Live HTTP testing exposed first-login bridge SELECT FOR UPDATE on read-only legacy/link tables. Those reads now use shared locks; the account retains an exclusive lock for its allowed password update. A regression explicitly forces first-upgrade bridging inside a rolled-back transaction under runtime privileges. INSERT audit and UPDATE account hash succeed; old audit writes, legacy credential writes, account disabling, business writes, grant/link/publication changes and audit UPDATE/DELETE are denied. See runtime-permissions.md for exact grants.

No rendered responsive/keyboard/console claim is made for this revision because browser access is disabled. Earlier Phase 2 screenshots certify the previous layout only. Current CSS keeps the prior mobile navigation, adds wrapping city filter controls and uses existing responsive table containers; actual visual checks remain outstanding. No browser policy was bypassed and no screenshot was fabricated.

## Handoff

Use /ar/login or /en/login, demo.user / Synthetic-Desktop-2026!, then the project city filters. Local schema and known synthetic account have been upgraded. Real duplicate-account inventory, staff city assignments and capability decisions require explicit reviewed evidence during migration/administration; matching passwords never establish identity.

Finish rendered EN/AR desktop/mobile filter checks when local browser use is available. Next development task is P3.0 shared operational business schema/mappings, followed by P3.1 deterministic importer. Hosting/access-boundary questions remain deferred until project completion before deployment. Hosted CI results are reported separately through the commit's GitHub Actions run.
