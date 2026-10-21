# P4.3 assessment and lifecycle workflow contract

21 October 2026. Static source contract, not runtime or five-city parity acceptance. Desktop source `D:/MyProjects/MicroProject newTest/MyWorkApplication` stays read-only. Authority: execution-plan P4.3, the P4.2 contract (`p4-2-workflow-contract.md`) and the approved P3 operational schema. No new physical tables are required: every payload below maps to an approved `op_*` table.

## Project state history

| Desktop evidence | Behavior | Web disposition |
|---|---|---|
| `Application_New_Form.cs:4219` | Preliminary creation inserts a `microproject_state` row for catalog state `On Hold` (Arabic "تسجيل أولي") with `Current_State = 1` | Implemented in the same transaction as preliminary creation. Creation fails if the city/source state catalog has no `On Hold` row; the desktop silently wrote NULL |
| `Application_New_Form.cs:4287–4302` | Preliminary → full (or direct full) sets `ApplicationState = project`, clears all current flags, inserts `Waiting` (current), and assigns every `task` with `T_Type = 'a'` to the project (`State = 1` for `T_ID = 1`, else 0) | Implemented as the lifecycle handoff of `convertToFull`. Already-assigned tasks are skipped so a retry cannot duplicate a business event (desktop would duplicate) |
| `AdminPanel_From.cs:1275` | Any administrative state change clears all current flags then inserts a new state row dated now | Implemented with optimistic version check and a project row lock |
| `AdminPanel_From.cs:1259,1267` | "On Hold" can never be chosen manually; "Waiting" cannot be chosen while the project is "On Hold" (the full application does that); "Financed" cannot be chosen from "Accepted" (finance enters the loan, `Loans_Form.cs:905`) | Enforced by name, using the source-qualified catalog rows `Name_en` (`On Hold`, `Waiting`, `Accepted`, `Financed`) |
| `AdminPanel_From.cs:1187–1198` | Moving to "Accepted" is limited to desktop roles 1 and 8, and assigns every `T_Type = 'f'` task (`T_ID = 1` state 1, else 0) | Requires the separate `lifecycle.accept` capability; role mapping to real staff stays a separate decision. Task assignment idempotent as above |
| `AdminPanel_From.cs:1199–1217` | Moving to "Withdrew" clears the donor and marks the project's notifications seen | The donor reference is cleared only when set. Notification side effects are deferred to P5.3 (real delivery stays disabled) |
| `AdminPanel_From.cs:1320` | Reason and note update the latest state row of the displayed state | Implemented as an explicit update of the current row's reason/note |

State catalog (identical in the inspected source): 0 Rejected, 1 Accepted, 2 Delayed, 3 Waiting, 4 Financed, 5 Finished, 6 Withdrew, 7 Canceled, 8 Closed, 9 Under Review, 10 On Hold. State 0 is a valid identifier; never test it for truthiness.

No closed transition graph is invented. Beyond the three explicit exclusions above, any catalog state may be chosen, except choosing the state that is already current (which would only add a duplicate history row). The donor rules (donor required for funded states, donor not selectable for earlier ones) belong to the donor/finance workflow (P5.1) and are not enforced here.

Dates: `microproject_state.Date` is a DATE column; the web writes the application-time date. `task_microproject.Date` is a DATETIME. Web rows carry null raw legacy key columns (`MicroProject_ID`, `ID`, `TMP_ID`, `Task_ID`); canonical relationships carry the links.

## Assessment checklist

`CheckList_Form.cs:1152–1560` and `Classes/CheckList.cs:179–248`. The checklist is keyed by **person** (`checklist.Person_ID`), selected through a project and beneficiary. One checklist per person is the desktop assumption (`Update_Mode`); legacy data may hold several, so the web never imposes a uniqueness rule: with more than one header an explicit header is required.

Header (`op_checklist`): home-visit and project-visit flags with date, time, notes, up to two visitors each; visits report; team and committee dates; notes; professional test need/note and result (`1` needs, `0` good, `-1` not applicable) with date, note, examiner; professional and management training with trainer, place, dates, note, result; rejection message flag, date and sender. Tri-state values use `1`, `0` and `-1`; `-1` is preserved as a value.

Validation (`check_data`, `:1355–1418`): a project and beneficiary are chosen; a home or project visit requires its first visitor; a professional test "yes" requires note and examiner; professional training "yes" requires trainer, place and note; management training "yes" requires trainer and place; a rejection message requires a sender. When a section is switched off the desktop empties its details; the web clears them the same way.

Member rows (`op_person_checklistmembers`): location `k` (key person: name notes, role notes, opinion fixed at `-1`), `t` (team) and `c` (committee) with opinion `1` for, `0` against, `-1` not applicable, and notes. Each row selects a catalog member whose position type must equal its location. Desktop updates stamp `created_at` again; the web stamps it only on insert so history is not rewritten. Rows are inserted or updated in place; the desktop delete icons and "delete the whole checklist" are not exposed because the plan restricts deleting historical records (deferred; needs an explicit authorized deletion design).

Open points kept visible: the checklist form does not record an author; `RejectMsgSender_ID` resolves to a historical source user occurrence and cannot be chosen for a web actor, so the sender is preserved when present but not editable. Printing the Word checklist is P4.4.

## Scoring

`score` / `microproject_score` are written only by the legacy `Application_Form`, which no live menu or form constructs (the only construction site is inside itself). The newer `Application_New_Form` has no scoring step. There is no reachable desktop scoring workflow to reproduce, so scoring is **not implemented**; preserved score rows remain available through the preservation layer. The owner should confirm whether scoring is still wanted before any build.

## Authorization

New per-city capabilities, independent of applications grants: `lifecycle.manage` (state transitions and reason/note), `lifecycle.accept` (the Accepted transition), `assessments.view` and `assessments.manage`. Every action also requires `applications.view`. Omission in the desktop role table is not permission; real staff grants are a separate decision and none are provisioned.
