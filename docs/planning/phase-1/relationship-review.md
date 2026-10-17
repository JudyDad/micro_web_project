# P1.5 independent application relationship review

Prepared 16 October 2026. Owner: application relationship reviewer, separate from schema author. Status: application evidence review completed; target-schema review pending draft. This report is a focused static review, not exhaustive workflow certification, restored-data profiling, or physical-schema approval.

Read the execution/creation plans, Phase 0 data inventory and independent review/acceptance records. Inspected C# files read-only and evaluated the declared-FK graph from p0-2-data-inventory.json. No source database connection, source mutation, notification delivery or FTP access occurred. References below are relative to `D:\MyProjects\MicroProject newTest\MyWorkApplication`; inventory locations are relative to the planning folder.

## Application-only relationships and actor preservation

| Source fields | Evidence and required mapping |
|---|---|
| microproject_ag_answer.Question_ID / Answer_ID | Classes/AgricultureApplication.cs:29–35 inserts three original key components; :70–75 joins agquestion.ID and aganswer.ID. Preserve `(MicroProject_ID,Question_ID,Answer_ID)` as ordered source key, resolve each parent within the same city/source, and separately check answer.Question_ID equals the supplied question. The composite key permits multiple answers per question; do not impose unique(project,question). |
| education.Type_ID | Classes/Education.cs:55 resolves educationtype.ID without declared FK. Preserve raw type and nullable resolved link; unresolved values become exceptions. |
| log.Log_User | Classes/Log.cs:18–25 accepts actor text, not a user ID. Keep exact historical text and source. Any account resolution is additional, optional evidence, never a replacement. Log_Type and Log_OnTable retain embedded descriptions/IDs. |
| mevisit.Created_By / Edited_By | Classes/M_and_E.cs:22, :54 writes actor names; :171 reads them. Do not consolidate matching strings across sources. |
| microproject_rehab.Created_By / Edited_By | Classes/RehabApplication.cs:10–14, :22–28 accepts strings. Preserve independently of login-account availability. |
| visit.Created_By / Edited_By | Classes/Visit.cs:25, :86, :113 and Classes/VisitRehab.cs:78. Preserve text; no invented mandatory user FK. |
| person_microproject associations | Classes/Application_Queries.cs:59 filters both person and project; Classes/Select.cs:90 exposes PMP_ID as report ID. Preserve association's own identity and all attributes rather than replacing with a deduplicated person/project pair. |
| person_family.Person_ID / P_Provider_ID | Classes/Application_Queries.cs:69–81 and :109 resolves member and provider independently. Two FKs to person are distinct roles, not duplicates. They do not form a declared table cycle. |
| image.image_NewPath and IDs embedded in filenames/text | Classes/Images.cs:18–25; Application_New_Form.cs:7923–7937; Attachments_Form.cs:579–590. Keep exact stored path/name and original project number, plus city/root alias. Technical-ID remapping must not rewrite file names or log prose. |

Unknown application-link candidates in the inventory remain unresolved: dept.PMP_ID, work_exp.PMP_ID, t1.Person_ID/MicroProject_ID, visit_schedule_temp.MicroProject_ID, temp_gender.id. No active C# hits for these auxiliary table names were found in the focused non-Designer search; that does not establish absence from stored routines, dynamic SQL or historical behavior. Retain them and profile privately; names alone do not authorize FKs or deletion.

## Notifications: explicit mapping exceptions

`user_notification.Pay_ID` is not a uniform payment FK. Keep raw integer, raw Body, source, project and recipient/sender links, alongside a separately typed resolved target and resolution status. Do not infer type solely from sign or from a coincidentally existing numeric target.

| Observed writer/reader | Meaning / required handling |
|---|---|
| Loans_Form.cs:1087–1089 | Actual payment ID passed into Pay_ID. Resolve a payment only with payment-type evidence and matching source/project. |
| Classes/UserNotification.cs:122–127 | Executive-file update filters Body for the executive-file label and compares Pay_ID to ExeFileID. This is an expected reference contract, not proof every stored row uses it. |
| ExecutiveFiles_Form.cs:336–337 | Executive-file creation instead writes sentinel **-1199**. This contradicts the positive-ID update assumption. Preserve and profile; do not manufacture an executive-file link. |
| Loans_Form.cs:714–728, :808–819, :853–858, :891–892; Classes/UserNotification.cs:259 | **-21** is used for visit and clearance notifications. It is a category sentinel, not a visit-row ID. |
| Loans_Form.cs:896–897 | **-10** for trophy visit notification. |
| TaskBoard_Form.cs:1146–1148 | **-3** for purchasing-completion notification. |
| SendMessage_Box.cs:86–94 | **-100** for free-form messages; also passes **MicroProject_ID=-1**, despite a declared project FK in the supplied schemas. Profile whether -1 is an actual row, an orphan, or an unreachable/failing write; preserve raw value regardless. |
| AdminPanel_From.cs:1236–1243 | Project status notification uses **-998 - i**, with recipient-loop index i. Preserve individual values; a closed sentinel enum would lose information. |
| Application_Form.cs:4717–4724 | Project replacement notification uses **-998 + i**. The theoretical sign depends on recipient count; positive values alone cannot establish a payment link. |
| Classes/UserNotification.cs:52–53, :83–84, :188–189 | **-1** is an API filter-omission control in query builders. This does not prove that every stored -1 has that meaning. |

Additional legacy code `Classes/Notification.cs:21`, :55, :69 references a table named notification and a **-5** N_PaymentID sentinel with N_ExeMP_ID. That table is absent from the supplied 127-name inventory. Keep this as a code/schema discrepancy, not a new invented source table or mapped row set. Its runtime relevance must be resolved before porting the affected behavior.

A failed typed resolution preserves the original row and emits an exception. A dangling numeric reference, null, category sentinel and unresolved text actor remain distinct states. Never substitute zero, null or a dummy account/project silently.

## Composite, keyless and cyclic dependencies

An independent Python DFS over every city's declared FK list found **no declared table cycles** in the supplied inventory. This is a graph result only, not proof about undeclared/application-level relationships or future schema design. Parent-first import can follow the declared DAG; reserve second-pass resolution for independently discovered links, and report any added cycles explicitly.

The only composite primary-key table found is microproject_ag_answer, with all three components listed above. Preserve types and component ordering in the key encoding. Give its imported row an internal ID without changing business cardinality.

| City | Tables with no primary key |
|---|---|
| Aleppo | t1 |
| Damascus | dept, t1, temp_gender, work_exp |
| Homs | resultstable, t1, visit_schedule_temp |
| Latakia | t1 |
| AlWadi | resultstable, t1 |

Keyless record identity must bind source, immutable snapshot/hash, table and row ordinal determined from the frozen dump stream. Database restore retrieval order is not stable. Identical rows remain separate occurrences; hashing contents alone is not a sufficient unique key. Reruns must reuse the same recorded ordinal assignment. Changes between snapshots require an explicit reconciliation policy, never ordinal-based assumptions of entity continuity.

## Synthetic review cases required before acceptance

1. Aleppo project 1 and Homs project 1 map to distinct internal IDs; a child from either source cannot resolve to the other.
2. Two agriculture answers under one project/question retain separate rows; an answer pointing to another question is preserved and flagged rather than rewritten.
3. Two identical keyless rows get different ordinal identities; rerunning the same manifest reproduces both identities.
4. Pay_ID=-1199 survives with unresolved executive-file association; Pay_ID=-21 stays a category sentinel; a payment and executive file sharing positive ID 7 remain disambiguated by verified type/source/project.
5. Notification project -1 remains distinguishable from null; mapping never invents a project merely to satisfy a FK.
6. An actor string with no surviving account remains readable and exact; equal actor names across cities do not merge identities.
7. Member/provider links resolve to their own person roles even when both happen to reference the same person.

These are design walkthroughs/test requirements, not executed importer tests. Source anomalies require restored aggregate profiling and retained exception evidence before the gate can pass.

## Handoff and review boundary

Changed file: phase-1/relationship-review.md only. Interfaces: documentation-only contracts for typed notification references, source-qualified association identity, raw actor fields and deterministic keyless occurrence identity. Data effects: none.

Checks executed: read-only rg searches and targeted C# reads; all five FK graphs inspected through Python using the Phase 0 JSON; no declared cycles; keyless tables enumerated per city. A search for nonexistent Classes/Family.cs and Classes/Person.cs failed; review continued using actual Classes/Application_Queries.cs and source schema evidence. No missing-file search was counted as proof of absence of business functionality.

Open gate items: schema-author draft review, P1.1 restored/profiled sentinel and orphan populations, application-only auxiliary-link resolution, historical actor ambiguity, and the notification table/code mismatch. Phase 0's coordinator acceptance exists, but this reviewer does not independently recertify its exhaustive menu coverage. Physical schema confirmation and Laravel migrations remain outside this review's approval authority.
