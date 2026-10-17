# Theme assets

Packaged 17 October 2026 for the user-authorized Phase 2 theme implementation. Source files were read only. Selected bytes match the Phase 0 inventory in `docs/planning/p0-3-assets.json`; destination files are unchanged copies. This records provenance and the implementation authorization, not a claim about independent licensing or redistribution rights.

| Destination in public/ | Source | SHA-256 |
|---|---|---|
| assets/fonts/SomarSans-Regular.otf | D:/MyProjects/hc-hr/public/fonts/SomarSans/SomarSans-Regular.otf | ac355205b9cce0c34fea32bd30871647a5bd7f1241ace774458628f95521142e |
| assets/fonts/SOMARSANS-MEDIUM.OTF | D:/MyProjects/hc-hr/public/fonts/SomarSans/SOMARSANS-MEDIUM.OTF | 5a189f9f0bfa7ad9cd30b208b2471b6f59606b29d1428e42f7bdbd2f72d98717 |
| assets/fonts/SOMARSANS-BOLD.OTF | D:/MyProjects/hc-hr/public/fonts/SomarSans/SOMARSANS-BOLD.OTF | 7a35ead2497f13a31c6320f3815ac225c1219988111938f10f524678362b2be6 |
| assets/brand/hope-center.png | D:/MyProjects/hc-hr/public/assets/img/001_HC.png | 218a050be57e64dba1bcaf7bd240c43c281712b0d7f1f4a401b342aa21582dfd |

The appearance follows the accepted HopeHR contract: primary `#C2151C`, heading `#58595B`, text `#6D6E71`, neutral white cards and pale gray workspace. Fonts load locally with swap and system fallbacks. Only regular, medium and bold weights are packaged. The logo is displayed with `object-fit: contain`; original pixels are not modified. No HR records, other images, templates or source application files are copied.

Operational views use bilingual native forms, CSRF fields, escaped project values, city-qualified links, server pagination and read-only details. The foundation component demonstration remains separate from operational flows and does not itself certify phase acceptance.
