# P0.3 — theme, templates, assets and hosting discovery

Scope update: `p0-4-scope-contract.md` records the user's subsequent decisions. Hosting/access questions are deferred to project completion and block deployment, not local development. Select versions against a verified local runtime, then recheck the production host. Online operation with draft recovery and compatible desktop credentials are selected. Checklist entries describing these as P0.4 blockers are superseded by that decision; capability measurements remain unverified.

Prepared 16 October 2026. Owner: UI discovery specialist. Status: ready for independent review; hosting decisions remain blocking dependencies. This is static discovery, not a rendered UI or an operational hosting test.

Evidence roots: **MP** = `D:\MyProjects\MicroProject newTest\MyWorkApplication`; **HR** = `D:\MyProjects\hc-hr`; **PLAN** = `D:\MyProjects\MicroProjects-Web-Plan`. References below use `root/relative-file:line` with one-based lines. Desktop and HR source were read-only; only `p0-3*` planning files were created. No database, FTP, HR session, document merge or desktop application was executed. No source records or credentials were exported.

## Theme contract and strength of evidence

The accepted authority is the HopeHR appearance, not the desktop palette. PLAN/creation-and-migration-plan.md:72 records an earlier live inspection of `https://hr.hcsyria-apps.org/en`. That inspection's 14px body and 22px heading samples are **prior evidence**, not a new live measurement in this task. This task independently inspected local HR style/layout sources; local checkout equivalence to the deployed site is unverified.

| Token/pattern | Contract / evidence | Verification status |
|---|---|---|
| Font | SomarSans; fallback Lato then system sans. HR/resources/css/app.css:122,126 | Newly verified local source; 8 OTF weights declared at lines 19–81 |
| Primary | #C2151C | HR/resources/css/app.css:133; corroborates prior live sample |
| Heading / medium / light gray | #58595B / #6D6E71 / #939598 | HR/resources/css/app.css:134–136 |
| Soft neutral | #DCDDDE; neutral-50 #F5F5F6 | HR/resources/css/app.css:137,152; neutral-50 is a palette entry, not proof of actual workspace computed color |
| Body / heading size | 14px / 22px sampled | Prior live sample only, PLAN/creation-and-migration-plan.md:74; do not claim every component uses these sizes |
| Workspace | Pale gray; white cards; subtle borders and shadow | Prior visual sample; local app main uses bg-gray-100 at HR/resources/views/components/layouts/app.blade.php:38 |
| Direction | Arabic RTL / English LTR | HR/resources/views/components/layouts/app.blade.php:2 |
| Sidebar | Collapsible, persistent collapsed state, mobile overlay | HR/resources/views/components/layouts/app.blade.php:17–20; HR/resources/views/livewire/layout/sidebar.blade.php:6,16,322 |
| Header | Fixed top white header | HR/resources/views/livewire/layout/header.blade.php:12 |
| Controls | Rounded controls/cards | Prior live observation; local header rounded control classes at HR/resources/views/livewire/layout/header.blade.php:19,64,94 |
| Language switch | EN/AR required | Accepted plan + prior observed pattern; no new interactive language-switch test |

Exact corner radii, spacing, focus/disabled/error tokens, table density, contrast and mobile breakpoints still require P2.2 component design and visual verification. Do not copy HR account, employee, role or business data. Do not assume HR's package constraints are the new application's locked versions.

## Active print-template trace

**One packaged DOCX template was found:** `MP/DocPrint/cl.docx`. The scope of that conclusion is static references in this desktop source, not an assertion that staff have no additional external paper forms.

1. MP/MainForm.cs:613 opens CheckList_Form.
2. MP/CheckList_Form.Designer.cs:373 wires Print_button.Click to button1_Click.
3. MP/CheckList_Form.cs:1677 checks both Person_ID and MicroProject_ID; :1680 invokes Save_button_Click before requesting print data. A browser replacement must explicitly define/save before printing; printing is not currently a purely read-only action.
4. MP/CheckList_Form.cs:1682 requests Print_Checklist_Procedure with **Person_ID**, not project ID. MP/Classes/CheckList.cs:403–413 builds the active connection and calls stored procedure `print_checklist` with `@inPersonID`. Preserve city/source context; the person-based routine output is the print oracle pending restore-based verification.
5. MP/CheckList_Form.cs:1712–1714 selects executable-directory `DocPrint/cl.docx`. MP/MyWorkApplication.csproj:852–853 includes it as Content with CopyToOutputDirectory Always.
6. MP/CheckList_Form.cs:1718–1734 copies it to per-user AppData `Micro Projects/DocPrint/cl.docx`. :1746–1754 writes query output as Sheet1 to cl.xlsx and **renames** it cl.xls. Renaming does not convert the workbook format. The merge connection string at :1770 still refers to cl.xlsx while OpenDataSource at :1759 names cl.xls: retain this discrepancy as an interoperability issue, not intended web behavior.
7. MP/CheckList_Form.cs:1756–1778 opens Word through Interop, performs mail merge to a new document, suppresses blank lines and closes the template without saving. No direct printer/PDF call is present in this path. Word/ACE COM dependencies are desktop implementation details; select a server-side print renderer after hosting review.

The handler also attempts to kill Word processes (:1703–1708, :1785–1790). This is not a required browser behavior. No execution was attempted. Error handling and current save behavior need isolated functional validation before parity acceptance.

| Asset | Classification / evidence |
|---|---|
| DocPrint/cl.docx | Active source template, packaged and referenced as above |
| DocPrint/cl.xls | Existing workbook in source; generated AppData workbook is used at runtime. Do not treat its existing cells as a clean fixture or import them |
| DocPrint/old/cl.docx and cl.xls | Historical candidates; no active code reference found, not selected automatically |
| DocPrint/old 09-09-2025/cl.docx and cl.xls | Historical candidates; no active code reference found, not selected automatically |
| Resources/MP Training 2021-compressed.pdf | Active help resource, not a print merge template; MP/Help_Form.cs:98–105 writes resource bytes locally; MainForm.cs:1213 opens Help_Form |
| Resources/MP Training 2021.pdf | Larger reference copy; old access path in Help_Form.cs:22–25 is commented, both PDFs listed in project at :890–891 |

The `FROM DISC PROGRAM` print block at MP/CheckList_Form.cs:1796 onward is commented out. Search of all MP C# and project files for DocPrint, .docx, .dotx, PrintDocument, PrintPreview, PrintOut and ExportAsFixedFormat found no additional active document-template path. Spreadsheet exports are inventoried under P0.1 and must not be mistaken for missing DOCX templates. Template field/layout extraction, rendering, actual merge output and print parity have **not** been tested; those are P6 prerequisites using isolated/minimized data.

## Asset provenance and packaging readiness

`p0-3-assets.json` records absolute local paths, sizes and SHA-256 hashes for the six DocPrint files, both PDFs, selected desktop logos, HR logo/icon and all 16 SomarSans files. Hashes establish identity of inspected bytes, not licensing, design approval or correctness.

- HR/resources/views/components/app-logo.blade.php:1 uses `public/assets/img/001_HC.png`; sidebar.blade.php:29 uses that component, :30 references `hope-center-icon.svg`; HR/resources/views/partials/head.blade.php:6 uses the icon.
- HR/resources/css/app.css:19–81 declares OTF font URLs with exact mixed case. All eight OTF paths exist and are non-empty. HR/resources/views/partials/head.blade.php:11–12 preloads Regular and Medium OTF.
- Two **unreferenced TTF alternatives are zero bytes**: SOMARSANS-EXTRABOLD.ttf and SomarSans-Thin.ttf. Do not package them as valid fonts. This does not invalidate the corresponding non-empty OTF assets used by CSS.
- Desktop `hc-logo.png` and NewDesignIcons light/dark logos are reference candidates, not automatically the accepted web logo. Desktop Resources/Fonts contains Cairo, Traditional Arabic, Avenir, Janna, Lato, Rubik and other families; these are not a substitute for the HopeHR font contract.
- Confirm approved logo variants and redistribution/embedding rights before packaging. Copy only selected approved visual assets; do not copy the HR public/storage tree or repository wholesale. No fonts, logos or documents were copied in Phase 0.

## Hosting capability checklist

All intended-host capabilities below are **unknown/unverified**. Local HR composer.json declares PHP ^8.2, Laravel ^12.0 and Livewire ^3.6 (lines 9,14,17); those are local project constraints, not server version measurements or requirements selected for Micro Projects. HR/AGENTS.md contains historical local Laragon web/CLI version notes, also not hosting proof. The desktop project targets .NET Framework 4.8 at MP/MyWorkApplication.csproj:13; that establishes no PHP capability.

| Required fact | Evidence needed / responsible decision | Dependency |
|---|---|---|
| Host identity, OS, access model | Hosting owner identifies actual target and internet-facing vs office-only/VPN access | P0.4/P2.1 blocker |
| Required offline behavior | Staff confirm whether office-local writes must continue during internet outage, who synchronizes and conflict ownership | P0.4 architecture blocker |
| PHP web + CLI versions and extensions | Intended-host version/extension reports, timezone, memory/execution/upload limits; verify against selected supported Laravel/Livewire releases | P2.1 version lock blocker |
| MySQL version and configuration | Intended-host version, engines, charset/collations, SQL modes, packet size, timezones, privileges and restore support | P1 restore environment and P2.4 deployment compatibility |
| Deployment access | SSH/CI or control-panel restrictions, Composer execution or built artifact upload, public document root, HTTPS, writable private storage/cache, case sensitivity and symlink support | P2.1 blocker |
| Build environment | Node/package-manager versions and CI build/artifact strategy; host need not run Node if prebuilt assets deployed | P2.1 |
| Workers and scheduler | Persistent process supervision or alternative supported queue model, scheduler/cron availability, timeouts/retries and capacity | P3/P6 long-running work |
| FTP protocol and routing | Supported FTP/FTPS/SFTP transport, passive/active behavior, firewall/VPN, DNS, TLS trust and read-only reachability **from intended host** to each root | P3.2 blocker |
| File roots, identity and collisions | Confirm source-qualified mapping for online/offline roots without exposing credentials; preserve original paths | P3.2 and cutover |
| File integrity and backup | Independent snapshot location/capacity, hashes, file completeness, restore procedure and ownership; live FTP is not a backup | P3/P7/P8 gate |
| PDF/print renderer | Runtime support, Arabic shaping/RTL, font embedding rights, memory, pagination; no assumption Word/ACE can run on host | P6.2 |
| Capacity and operations | CPU/RAM/disk, representative concurrency, retention, monitoring, protected backup destinations and restore objectives | P7 performance/recovery gate |
| Identity and notifications | Account/reset/SSO decision belongs to P0.4; server-side secret provisioning and isolated test mail/notification sink | P2.3/P5.3 |

Static network evidence: MP/MyWorkApplication.csproj:416 compiles FtpConnector.cs, not FtpConnector_old.cs. Active connector uses WebClient upload/download (:8–27), FtpWebRequest deletion with passive mode (:29–38), and existence check with **active mode** (:57–62). It sets no explicit EnableSsl. These facts do not prove the service lacks TLS/SFTP or is reachable. MP/Classes/Document.cs:27–35 uses separate offline/online roots; when offline is selected it writes offline **and then attempts online**, so the label alone does not prove disconnected operation. Login_Form.cs:155–158 sets the Online preference. Inspecting credentials, decrypting Connections.txt.aes or probing servers was unnecessary and was not done.

## Checks and handoff

- Read accepted plan, creation plan and aggregate JSON structure; no parsed-count evidence promoted to restored counts.
- `rg --files` with template/font/logo/project filters; `rg -n` across C# / csproj for document/print references and selected FTP methods: passed, references traced above.
- Targeted `Get-Content`/`rg -n` for local HR CSS/layout/asset references: passed. One initial Windows wildcard-path query failed and was rerun using `rg -g 'app-logo*.blade.php'` successfully; no missing-file conclusion relies on that error.
- `Get-ChildItem`, `Get-Item`, `Get-FileHash -Algorithm SHA256`: asset inventory generated. No renderer, live browser sampling, login, DB restore/query, network connectivity or application runtime check was run.
- Delivered behavior: reviewable theme tokens, active-versus-historical template trace, asset provenance/defect inventory and explicit hosting checklist.
- Changed files: `p0-3-theme-template-infrastructure.md`, `p0-3-assets.json` only at initial handoff. No application interfaces or schema changes; no migration/data effects.
- Remaining risks: hosting/access/offline and identity decisions block foundation design; asset approval and renderer validation remain; template save/merge discrepancies require oracle checks. Independent reviewer must approve discovery coverage before task is marked done.
- Next tasks: P0.4 resolves infrastructure decisions; P2.1 selects exact versions after host evidence; P2.2 implements theme with approved assets; P3.2 verifies file access; P6.2 proves actual document parity.
