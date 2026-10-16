# Independent P0.2 review by P0.1 owner

16 October 2026. Reviewed p0-2-data-and-files.md, table register and source relationship/file call sites without changing P0.2-owned files. Result: request two documentation corrections before acceptance; enumeration claims remain static, not restored.

1. **Correct cross-city filename collision assertion.** Program.cs:110–143 appends city-specific folders to both online/offline roots. Document.cs:23–37 composes the selected root with the supplied name. Thus identical project number + filename in different correctly routed cities does not inherently collide. Preserve city/root alias/name; genuine risks include same-city duplicate names, failed existence checks, misrouting, and divergent online/offline bytes. The source-qualified catalog requirement remains correct.
2. **Upgrade user_notification.Pay_ID to an observed overloaded reference.** Loans_Form.cs:1082–1110 obtains payment IDs and passes them to notification insertion/update. Classes/UserNotification.cs:122–127 instead updates executive-file notifications with Pay_ID=@ExeFileID; :259 identifies visit notifications with sentinel -21. A single enforced payment FK would be wrong. Retain raw value, body/type discriminator and source, resolve confirmed positive payment/executive-file references separately, and retain negative sentinels without key remapping. Any other uses still require profiling.

Confirmed Document.cs:29–34 writes office storage when offline, then attempts online regardless; its GetImageByName at :88 uses an OR null guard and at :113 uses the offline path in the online branch. These support the report's split-copy and retrieval defect findings. QR_Form.cs is compiled but has no observed active constructor route, so QR payload behavior is reference/uncertain functionality.

Coverage sanity: the table register is explicit and source-qualified, with uncertain tables retained; counts clearly labeled parsed. This review does not independently certify every parsed SQL tuple or FK, nor execute routine/report results. Keep P1.1 restored counts and routine reconciliation as gate prerequisites. The data engineer/coordinator must close the above findings; reviewer approval is conditional on those changes.

## Review closure

Coordinator corrections re-read on 16 October 2026: both findings above are addressed in p0-2-data-and-files.md. City-specific root scoping and the polymorphic/sentinel Pay_ID contract now match the inspected code. Static discovery review passes within the stated boundary; this is not restored-data, importer or file-byte certification. No other blocking discrepancy identified in this focused review.
