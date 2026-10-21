# P5.1 finance workflow contract

21 October 2026. Static source contract, not runtime or five-city parity acceptance. Desktop source `D:/MyProjects/MicroProject newTest/MyWorkApplication` stays read-only. Authority: execution-plan P5.1, the P4.3 contract (`p4-3-workflow-contract.md`, which assigns the donor rules for funded states to this task) and the approved P3 operational schema. No new physical tables are required: every payload maps to an approved `op_*` table (`op_loan`, `op_payment`, `op_exefile`, `op_purchasing`, `op_purchasingcategory`, `op_donor`, `op_donorgroup`, `op_microproject`).

Source evidence: `Loans_Form.cs`, `Payments_Form.cs`, `ExecutiveFiles_Form.cs`, `Purchasing_Form.cs`, `AddNewGroup.cs`, `AdminPanel_From.cs:1180–1385`, `Classes/Select.cs:485–640`, `Classes/MicroProject.cs`, `Classes/TasksOfProjects.cs`, `Classes/Purchasing.cs`.

## Eligibility

`Loans_Form.MicroProject_ID_textBox2_Leave:1313–1325` only finds projects whose **current** state is one of catalog ids 1, 4, 5, 7, 8 (Accepted, Financed, Finished, Canceled, Closed), and `fill_loan:341–489` refuses to show an editable loan unless the project already has a donor (`MP_Donor` not null; the message wrongly says "not accepted"). The save buttons are hidden otherwise. The web enforces both gates on every write, by catalog name (`Name_en`) like the lifecycle service.

## Donor rules (assigned to P5.1 by P4.3)

`AdminPanel_From.cs:1335–1383`: a donor can be chosen only for funded states (Accepted, Financed, Finished, Closed, Canceled) and cannot be removed from them (`يجب تحديد الجهة الممولة للمشاريع الممولة`); for any other state the donor is empty and cannot be chosen (`لا يمكن اختيار جهة ممولة…`). Withdrawing already clears the donor (P4.3). The loan form also assigns `DonorGroup_ID` and sets `MP_Donor` to the group's donor (`Loans_Form.cs:945–963`). Donors are a **shared whole-table catalog**; the web reads them through the approved projection and does not write the shared table. Donor groups are per city and may be created (`AddNewGroup.cs`: name and donor required, no uniqueness rule; the web additionally refuses an identical name for the same donor in the same city so a double submit cannot create two).

## Loan

One loan per project is the desktop assumption (`Update_Mood`); legacy data may hold several. The web lists every loan and edits an explicitly chosen one; it never creates a second loan for a project that has one.

| Field | Desktop evidence | Web rule |
|---|---|---|
| Currency | `enum('USD','SP')`, combo (`Insert_Loan:221`) | required, exact enum |
| Exchange rate | blank or 0 stores 0 (`:215`); `decimal(12,2)` | blank or 0 stores 0; at most 2 decimals |
| Loan amount | blank rejected (`:911`); `decimal(12,2)` | required, at most 2 decimals, greater than 0 (the desktop divides by it) |
| Loan date | picker, default now | required, ISO date |
| Returned amount (amount to be returned), payment (instalment) amount | blank or 0 stores 0; `float` or `double` per city | at most 2 decimals; for single-precision cities the value must survive a float round trip so a stored value never differs from what was entered |
| Payments count, rate | read from labels computed by `Calculate_RemainedAmount:1176–1266` and formatted `N1`, so the **stored** values are rounded to one decimal | derived: count = returned ÷ payment, rate = returned × 100 ÷ loan amount, each rounded half away from zero to one decimal; 0 when returned or payment is 0 |
| Note, receipt number | free text; `Loan_ReceiptID` is the "receipt" | note ≤ 65,535; receipt ≤ 50 |

Storage variants: `Loan_ReturnedAmount` and `Loan_PaymentsAmount` are `float` in Aleppo, Damascus and Latakia and `double` in Homs and AlWadi; the approved schema keeps one column per variant (`…__76a7e234` float, `…__2ce06a9e` double). A web row writes the variant of its city (looked up from the approved mapping by city) and leaves the other NULL; reads take whichever is set.

Creation side effects (`AddLoan_button_Click:919–937`), in one transaction under the project lock: insert the loan; move the project to **Financed** (the desktop writes a history row even if already Financed; the web skips the write when Financed is already current so a retry or a legacy loan-less Financed project cannot duplicate the event); set the task `تسليم كامل المبلغ و تحديد الأقساط` to done on the project's existing assignment (desktop `UPDATE` touches nothing if the assignment is missing, and so does the web). Visit-schedule notifications (`generate_me_*_visits_notifications`) and the clearance notification are **not** generated (P5.2/P5.3; real delivery stays disabled).

Derived display (not stored): amount in the opposite currency is `amount ÷ rate` for SP with a non-zero rate, otherwise `amount × rate` (`:1188–1192`, so SP with rate 0 shows 0), rounded to 2 decimals half away from zero.

Deletion (`Delete_Loan`, admin roles 1 and 8 only, resets the task) is **not exposed**: the plan restricts deleting historical financial records and needs an explicit authorized design.

## Payment schedule rows

`insertPayment/updatePayment:134–184`: amount, due date, `Paid`/`Not Paid`, received date and note. When a row is not paid the received date is stored equal to the due date; when paid and no received date is given it also defaults to the due date (`SavePayments_button_Click:1072–1075`). Rows are inserted or updated in place; only changed rows are validated or written, so legacy values (sentinels, odd text) survive an unrelated edit.

`payment.Pay_Amount` is `float(24,0)`: MySQL silently rounds to whole units. The web **rejects fractional amounts and amounts outside ±16,777,216** instead of letting the column round them, because a silent financial rounding would break the exact reconciliation the plan requires. This is stricter than the desktop and is recorded as an open decision for the finance owner. Negative amounts (refunds, used by the loan summary comments `Select.cs:673`) are allowed.

Row deletion (`P_DeleteRow`) is not exposed for the same reason as loan deletion.

Reconciliation (`Calculate_RemainedAmount:1176–1266`, `BuildLoanReportQuery:572–619`), computed per loan in that loan's own currency and never summed across currencies: paid amount = sum of `Paid` rows; paid count = number of `Paid` rows; remaining = returned − paid (the screen shows it raw; the report clamps at 0, both are presented); remaining count = remaining ÷ payment (`N1`); the remaining figure is flagged when it is below one and a half instalments.

Automatic completion (`SavePayments_button_Click:1125–1145`): after saving, when the **last row** (highest id, the grid has no ORDER BY) is `Paid` and the remaining figure is flagged (needs payment and returned both above 0), the project moves to **Finished** (skipped if already Finished). The clearance notification that follows is deferred with the other notifications.

The payment-due notification each unpaid new row creates for financial users (`:1085–1089`) and its update on edit are deferred to P5.3.

## Payments schedule list (`Payments_Form`)

Rows of `payment` joined to loan, project and donor group; default view is unpaid ordered by due date, the paid view filters and orders by received date; filters: year (checkbox, years 2018 … current + 2; the desktop opened on the current year, the web opens on all years), months, donor group name (the desktop matched with `LIKE`; the web compares case-insensitively for equality), plus the web's city filter. The desktop sum label adds every amount regardless of currency; the web shows **count and sum per currency**. Excel export and printing belong to P6.2 and are not built. The desktop export permission (roles 4, 6, 7 refused) is therefore not modeled.

## Executive files

`ExecutiveFiles_Form.cs`: number (required on create), begin, current and impound dates, impound type, months (numeric box, default range 0–100, so the web accepts 0–100), project. Current date defaults to begin date on a new file (`:393`); the expiry date shown is current date + months with .NET month arithmetic (day clamped to month end, `Calculate_Set_ExpiredDate:370`). The desktop reads every date from masked boxes, so all three dates are needed to save; update mode does not re-check the number. One file per project is the UI assumption (the lookup returns the first), so the web creates a file only when the project has none and edits any listed file by id. The expiry notification for lawful users (`:317–337`) is deferred to P5.3; the expiry date is shown.

## Purchasing

`Purchasing_Form.cs`/`Classes/Purchasing.cs`: per project items (name, brand, category, quantity, price, currency `USD`/`SYP`, invoice date, number, trader), insert or update, no deletion in the save path. **The screen is unreachable in the desktop** (`MainForm.cs:789` commented, designer menu hidden), so it is built only as an opt-in, separately granted editor so that preserved purchase rows are readable and correctable; whether to keep it is an owner decision.

## Authorization

New per-city capabilities, independent of other grants and always combined with `applications.view`: `finance.view`, `finance.manage`, `executive_files.view`, `executive_files.manage`, `purchasing.view`, `purchasing.manage`. The desktop role table (loans: roles 1, 3, 7 save, 2/4/9 refused; purchasing: 3/4/9 refused) is not mapped to these grants; omission is not permission and no real grants are provisioned.

## Concurrency and retries

Each finance page carries a version hash of the project's donor/state fields, loans and payments; every write checks it under the project row lock, so a stale tab or a repeated submission is refused with 409 instead of duplicating a loan, payment, state row or task update. Routes are additionally serialized per user with `block(10, 10)`.
