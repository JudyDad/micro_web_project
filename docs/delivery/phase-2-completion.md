# Phase 2 completion — 17 October 2026

The local synthetic gate in team-execution-plan.md passes: actual login → permitted city → project search → persisted read-only detail in English and Arabic. P2.3–P2.5 are implemented; P2.2 now packages the supplied brand assets. This is a local foundation acceptance record, not production activation or source-value reconciliation.

## Implementation and review

The user instructed “Start Phase 2 from P2.3 till end of phase 2” after the four-table addendum was proposed. Migration 000004 creates the documented principals, explicit grants, audit and publication tables with source-consistent RESTRICT FKs and CHECK constraints. Preservation manifest bytes remain unchanged.

Source-qualified exact usernames resolve only against an explicit publication and provisioned principal. Ambiguous names, missing publications, disabled accounts and legacy role 6 fail closed. Compatible desktop PBKDF2 credentials upgrade atomically to Argon2id; a modern hash never falls back to the legacy credential. Long Unicode passwords retain significance beyond 72 bytes. Missing/denied accounts perform both supported KDF workloads; no strict constant-time claim is made. Native sessions rotate on login/logout, CSRF protects login/logout, and IP/identity login limits apply.

Every protected request revalidates the current published user. Every project query checks source and the exact active projects.view grant, joins the immutable registry and publication, and selects only whitelisted fields. Original project numbers remain city qualified. Search uses bound parameters and literal wildcard escaping, with stable server pagination. Display escapes stored content; amounts remain source values without invented currency or precision rules. No business-write, download, export or publication endpoint exists in this slice.

The application specialist implemented identity/schema services; the UI specialist reviewed their access boundary and implemented bilingual views/assets; the QA specialist independently exercised MySQL workflows and synthetic fixture guards. Coordinator review added pagination/XSS/oversized-ID/long-password checks, balanced missing-account KDF work, verified runtime database privileges and tested the browser flows. No unresolved blocking defect was found within this scope.

Audit contains allowlisted technical event metadata, never credentials, usernames, search strings or project payloads. The separately provisioned local runtime account has SELECT, audit INSERT and password-hash/timestamp UPDATE only. Integration checks prove audit UPDATE/DELETE, business writes and grant/publication/account-state changes fail. Privileged administrators still require protected operating procedures/backups; this is not a tamper-proof claim.

## Verification

| Check | Result |
|---|---|
| Unit/feature suite | 37 tests, 5,317 assertions passed |
| Operational MySQL workflow | 1 test, 310 assertions passed |
| Preservation MySQL lifecycle/constraints/concurrency | 1 test, 52,444 assertions passed; empty dedicated test DB afterward |
| Restricted runtime permissions | 1 test, 11 assertions passed; audit fixture transaction rolled back |
| Formatting, Composer validation, Blade compilation | Passed |
| Frontend build | Passed; public font URLs intentionally remain runtime URLs with all three fonts returning HTTP 200 |
| Dependency advisories | Composer and npm production audit: none reported |
| Asset responses | Three fonts and unchanged Hope Center logo return HTTP 200 with matching file sizes |

Runtime: Windows, PHP 8.2.4 with Argon2id, Node 22.19.0, MySQL 8.4.8 on loopback. Synthetic application DB micro_projects_local is separate from disposable micro_projects_test. No source business database or FTP was contacted or modified. CI repeats these checks against an ephemeral MySQL 8.4 service, including a separately restricted runtime account. Hosted CI status is recorded by the GitHub run, separately from these local results.

## Browser QA

Target flow: /en/login → source 1 sign-in → Aleppo projects → search 1042 → record 2 detail → Arabic switch → mobile navigation/Escape → source 2 URL denied → logout. A separate Arabic source 2 sign-in resolves project 1042 to Damascus record 4 and logs out successfully. All actions use invented fixture records and the restricted runtime account.

Browser plugin skill is not installed; the available CUA in-app browser with its documented Playwright locators was used. No new browser dependency was installed. EN/AR project lists were measured at widths 300, 390, 768, 1440 and 2560px with no horizontal page overflow; tables scroll in their own region. Screenshots verified English desktop and Arabic mobile detail, readable controls, RTL flow and a localized 403. The mobile menu focuses navigation, locks background scroll and restores focus on Escape. Browser console warning/error capture was empty.

| Rendered check | Result |
|---|---|
| Page identity and nonblank content | Correct localized titles/routes and persisted synthetic record contents |
| Framework overlay | None |
| Console health | No captured application warnings/errors |
| Interaction proof | Login, city link, search, detail, language, menu/Escape, cross-city denial and logout passed |
| Screenshot evidence | Stored outside Git under the task visualization directory: phase2-en-desktop.png and phase2-ar-mobile.png |
| Reference comparison | Somar Sans, supplied unchanged logo, HopeHR primary #C2151C, headings #58595B, text #6D6E71, neutral workspace/cards match documented reference tokens |

Intentional differences: project-specific navigation replaces HR modules; synthetic alerts and read-only labels explain this slice; imported names remain unchanged when UI language changes. This does not claim pixel-identical reconstruction of the entire HR application. Other browser engines, assistive-technology sessions and production datasets remain untested.

## Handoff

Next is P3.1 deterministic importer with checkpoint/resume and rerun safety; agree metadata/file interfaces with P3.2 and the independent P3.3 reconciler. No routine snapshot replacement may silently rebind principals: current identities bind to published snapshot occurrences. Real staff permissions (D07), validated publication, attachment authorization and business writes need their own evidence/decisions. Ask hosting/provider/runtime and internet versus office/VPN questions at project completion before deployment.
