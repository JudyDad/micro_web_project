# Phase 0 coordinator acceptance review

16 October 2026. Static discovery and scope-contract gate passed. This is not approval of a target schema, runtime parity or production readiness.

## Independent checks

P0.1 was authored by the workflow specialist and reviewed by the coordinator. Re-ran `p0-1-inventory.py` with the bundled Python runtime: exit 0; 179 compiled files, 53 forms, 89 menu instances, 68 bound menu actions, 1,621 events, 144 construction sites, 303 action candidates and 98 role references. Reviewed generated menu/screen dispositions and narrative; independently inspected Login_Form credential/city handling, PasswordHelper, MainForm role menus and the notification horizon condition. Verified MicroApp.Net10 contains build-output directories rather than an authored second project. The lexical enumeration's limitations are explicit. Six no-root compiled forms, detached/unbound paths and three noncompiled forms remain visible; none is silently excluded from release scope.

P0.2 was authored by the data specialist. Independently compared inventory keys against source-schemas.json: all 127 distinct names and all 616 city/table instances present; 60 routine definitions recorded. Re-ran `p0-2-inventory.py`: exit 0; all five archive CRC and ZIP/SQL hash assertions passed; project counts total 10,512. Inspected parser assumptions: quoted-string masking and single-line INSERT tuple counting are snapshot-format-dependent and not restored counts. The workflow specialist independently checked application-only links and file routing, requested two corrections, then verified closure in `p0-1-review-data.md`. Coordinator also inspected notification executive-file and -21 sentinel call sites. P1 must profile all candidate relationships and reconcile actual routine behavior.

P0.3 was authored by the UI specialist and reviewed by the coordinator. Recomputed all 29 recorded asset SHA-256 hashes: all matched. Independently checked local HR CSS font declarations and four main color tokens; traced CheckList_Form save-before-print, document path and CheckList.Print_Checklist_Procedure person parameter. Inventory separates active template, historical copies, prior live samples, local-source evidence and unverified hosting. No document render or live-site measurement is claimed. The user subsequently deferred hosting; the supersession note and scope contract explicitly remove that blocker from local development.

P0.4 was authored by the coordinator and independently reviewed by the workflow specialist in `p0-1-review-scope.md`. The reviewer requested consistency between local version selection and deferred hosting; corrected and verified closed. User decisions are recorded verbatim in intent: local build now with hosting question at project completion, online operation with draft recovery, and compatible desktop credential reuse.

Two specialists reached a usage limit after saving their narratives. Their missing cross-review work was completed by the coordinator, who did not author their inventories; the workflow specialist independently reviewed the coordinator's scope contract. No task relies on an unperformed review.

## Reconciliation and remaining dependencies

| Issue | Phase 0 disposition | Required downstream proof |
|---|---|---|
| Code SQL file has 8 procedures; dumps have 12 per city, differing names/contracts | Both sets enumerated; neither silently chosen as deployment source | P1 isolated restore/actual CALL output and P6 report oracle |
| Unbound or old agriculture, rehab, purchasing, executive-file and other screens | Preserve data and scope candidates; no deletion/deferment authorized | Workflow owner resolves actual required behavior before affected P4/P5 implementation |
| Role menu/action inconsistencies and hard-coded bypass | Record observed matrices; do not reproduce bypass or treat hidden menus as server authorization | P2.3 explicit policy and denial tests; owner resolves ambiguous grants |
| Notification payment/executive-file/sentinel references | Preserve discriminator, raw value and source; no blanket payment FK | P1 typed relationship mapping and anomaly profiling |
| FTP city folders and divergent online/offline stores | Preserve city/root/path; no inherent cross-city collision claim | P3 file hashes, conflict/missing inventory and host reachability |
| Checklist save-before-print and XLSX/XLS merge mismatch | Trace current behavior without claiming intended format semantics | P6 isolated template render and report comparison |
| Hosting and access boundary | User explicitly defers; local development allowed | Ask at project completion before deployment; verify production compatibility |
| Credential format | PBKDF2 code verified, stored formats unprofiled | Synthetic verifier vectors, source-qualified authentication and incompatible-account handling |
| Draft recovery | Unsubmitted form recovery only; no automatic committed offline writes | User/city isolation, expiry/clear behavior, authorization and stale-state revalidation |

## Next phase prerequisites

Phase 1 can use this reviewed inventory to prepare isolated restores and schema/mapping design. Before executing SQL, inspect scripts, definers, global statements, routines/events and connection isolation; no production credentials or endpoints in restore configuration. Confirm restored row counts/charset/engine settings and profile precision, keys, orphans and overloaded links. Define every source table/column disposition and source-qualified mapping, retain anomalies and verify numeric/BLOB preservation. Present the complete physical schema for P1.5 confirmation before Laravel migration code.

Phase 2 local foundation may follow its dependencies in the team plan, using a verified local runtime and locked supported versions. Database-dependent implementation still waits for accepted P1.5. No dependent phase was started in this Phase 0 work.

Source mutations, production changes, restores, application scaffolding and live FTP access: none. Changes are confined to Phase 0 planning/evidence artifacts. Named staff signoff, runtime parity, complete file coverage and deployment readiness remain future checks.
