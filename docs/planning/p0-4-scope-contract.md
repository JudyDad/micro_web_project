# Phase 0 scope contract

Prepared 16 October 2026. Owner: coordinator. Status: Phase 0 scope reviewed; see `p0-1-review-scope.md` and `p0-coordinator-review.md`. This document records requirements and evidence; it does not authorize production changes. User decisions in this conversation supersede earlier pending-question language in discovery artifacts.

## Accepted boundaries

- Five sources/cities: Aleppo, Damascus, Homs, Latakia and AlWadi. Lebanon and PTS Aleppo are excluded from v1.
- Preserve visible original project number plus city; assign internal technical IDs and retain immutable source provenance on records and relationships. Matching names, numbers or credentials do not establish a shared identity.
- One operational MySQL database. Preserve all source tables and values pending explicit target dispositions; no silent cleanup or consolidation.
- Existing FTP files retain their paths and storage. Authorized server-side streaming, a separate source-qualified upload namespace, file completeness checks and an independently verified backup are required.
- Laravel, Livewire, Blade, Tailwind and Alpine; select supported compatible releases against the verified local runtime and recheck production compatibility before deployment. Arabic RTL and English LTR. HopeHR theme tokens from the accepted plan.
- Desktop feature parity is the release baseline. A static finding that a form is unused does not approve its removal. Uncertain screens and contradictory behavior remain explicit review items.
- All discovery is static and read-only against source evidence. No desktop execution, database connection, FTP mutation, notifications, migration, application scaffolding or deployment is part of this phase.

Authority: `team-execution-plan.md`, Working agreement; `creation-and-migration-plan.md`, confirmed decisions. The newer team plan takes precedence over draft recommendations.

## Release workflow contract

The detailed screen and action inventory is maintained by P0.1. The release families to reconcile with that inventory are:

| ID | Workflow family | Required acceptance behavior | Accountable acceptance role |
|---|---|---|---|
| W01 | Authentication, city selection and account administration | Source-qualified actors; explicit permitted cities; disabled-account denial; authorized actions and downloads | Staff administrator, name pending |
| W02 | Preliminary/full applications, people and families | Complete intake including historical values and duplicate-number searches | Intake lead, name pending |
| W03 | Agriculture, rehabilitation and supporting application details | Preserve form fields, linked entities and validation distinctions | Intake lead |
| W04 | Checklists, scoring, review and lifecycle | Trace calculations and allowed transitions, including rejection and incomplete records | Programme lead, name pending |
| W05 | Loans, payments, receipts, purchases and executive files | Preserve currency-specific amounts and calculations; retry/concurrency protection | Finance/legal lead, names pending |
| W06 | Attachments, document/photo access and manuals | Original source/path resolution; authorized access; missing/conflicting files visible | Records owner, name pending |
| W07 | Visits, scheduling, monitoring/evaluation and rehabilitation follow-up | Match scheduling, assessments and historical outcomes | Field lead, name pending |
| W08 | Professional meetings and special cases | Preserve participant relationships and resulting decisions | Programme lead |
| W09 | Staff tasks, internal notifications, logs and lookups | Preserve assignments/history and explicit permissions; test delivery disabled | Staff administrator |
| W10 | Search, statistics and cross-city reporting | Only authorized cities; distinguish same-number projects; reconcile row sets and currency totals | Reporting lead, name pending |
| W11 | Exports, document generation and printing | Source-template parity, selected-row completeness, Arabic and pagination checks | Reporting/records leads |
| W12 | Backup, restore, versioning and operational maintenance | Replace desktop operational mechanisms with a reviewed web runbook; no browser-exposed arbitrary restore | Operations owner, name pending |

P0.1 is the detailed report/export inventory; P0.3 is the template and asset inventory. W12 remains in scope for an explicit operational disposition, rather than implicitly copying privileged desktop tools into web navigation.

## Permission contract

Observed desktop menu visibility is evidence, not sufficient server authorization. P0.1 records the full legacy visibility matrix and form-level exceptions. Every future read, mutation, report, export request/download and file stream must enforce both city membership and capability on the server.

The coordinator independently checked `MyWorkApplication/MainForm.cs:354` onward: roles 1/8 expose administrative menus; role 2 exposes intake, payments/attachments and selected field operations; roles 3/7 hide intake and field groups; roles 4/6/9 hide intake, finance and field groups. Login excludes role 6 (`Login_Form.cs:249`). No permission is inferred for an unhandled role.

The condition at `MainForm.cs:1285`, `role != 3 || role != 7`, is always true. It selects the notification-count date horizon (four days ahead versus today), not a general action grant. Resolve the intended notification rule for finance/legal roles before reproducing it. Main-menu visibility cannot certify permissions reached through search, child dialogs or direct methods.

## Identity evidence and decision

`Login_Form.cs:161` onward checks each source separately and builds the accessible-source list from successful credential verification; `MainForm.cs:1422` loads the selected source's role. Historical source users therefore remain distinct, even if a person has access to multiple cities.

`Classes/PasswordHelper.cs:19` and `:50` implement PBKDF2-SHA256 with 100,000 iterations, a 16-byte salt and 32-byte derived key encoded together as Base64. This establishes the format expected by code, not the format/completeness of every stored credential. A compatibility verifier requires synthetic vectors and isolated profiling; no production credential has been tested.

User decision: reuse desktop credentials where technically compatible. Implement a tested compatibility verifier and rehash on successful login when needed; determine handling of incompatible/invalid credentials after isolated profiling, without silently granting access or discarding historical authors. Preserve historical authors and explicitly map web principals to source identities and city capabilities. Never consolidate accounts automatically by username. SSO is not selected.

## Deployment and offline assumptions

User decision: build locally now; ask for hosting details at the end of the project. Hosting target, access boundary and production runtimes remain unverified deployment prerequisites, explicitly deferred rather than blockers to local development. P0.3 owns the capability checklist. Verify the local runtime and select compatible supported versions with lockfiles for local implementation; recheck production compatibility before deployment. Local desktop dependencies and dump metadata are not hosting capabilities.

User decision: build an online system with save-as-draft and, where useful, local recovery when connectivity returns. Scope interpretation: local drafts are unsubmitted form content, not committed offline business transactions or a second operational database. On reconnection, require authentication/city authorization, revalidate current server state and let the user explicitly resume/save; never silently overwrite newer server changes or auto-submit money-changing actions. Draft storage must be user/city/form-qualified, have an explicit retention/clear policy and exclude credentials and attachment bytes by default. Final sensitive-field storage and conflict UX are implementation review items. No full offline synchronization architecture is required by this decision.

Internet-wide versus office/VPN access will be asked with hosting at project completion, before deployment. Track that question in the delivery board and release checklist; no recurring reminder automation is requested. No host, DNS or network boundary is chosen by silence.

## Decisions and disagreements log

| ID | State | Decision / evidence needed | Blocks |
|---|---|---|---|
| D01 | Accepted in source plan | Five-city scope; original project numbers plus city and internal keys | None |
| D02 | Accepted in source plan | One operational DB; all original records and source-qualified relationships retained | None |
| D03 | Accepted in source plan | Reuse FTP paths; new namespace; independent file backup before cutover | Cutover until verified |
| D04 | User deferred to project completion | Build locally; ask hosting target, access boundary, PHP/MySQL and operations before deployment; verify local runtime first | Production deployment, not local discovery/foundation |
| D05 | Decided | Online system with draft save/recovery; no automatic offline business writes | Draft validation/conflict/privacy checks in implementation |
| D06 | Decided | Reuse compatible desktop credentials; preserve separate source actors and explicit city grants | P2.3 verifier vectors, isolated format profiling, incompatible-account handling |
| D07 | Pending staff owner | Resolve contradictory role checks; approve intended capabilities, including indirect entry paths | Authorization implementation |
| D08 | Pending inventory review | Resolve differences among active source, routines and documentation; do not choose a financial formula silently | Affected domain/schema implementation |
| D09 | Pending records owner | Active-template availability and approved logo/font packaging | Print/theme acceptance |
| D10 | Pending staff | Named workflow acceptance owners and explicit disposition of uncertain screens | Staff acceptance; no omission authorized |
| D11 | Later release planning | Write-pause window, backup/rollback owners and retention policy | P7/P8; not static discovery |

## Gate and next prerequisites

Phase 0 static discovery gate passed after independent inventory and scope reviews. D04–D06 have explicit user dispositions. Remaining source disagreements block their affected implementation or parity checks, not local discovery; see `p0-coordinator-review.md`. Unanswered business decisions are not accepted assumptions.

Phase 1 requires accepted discovery coverage, isolated restore infrastructure, inspected SQL and safe routine/event handling. Full target schema must map every source table/column and retain anomalies. P1.5 physical-schema confirmation is required before Laravel migrations. Phase 2 cannot start before P0.4; database-dependent implementation additionally waits for P1.5. This turn authorizes Phase 0 only.
